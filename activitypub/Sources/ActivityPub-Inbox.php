<?php
/**
 * ActivityPub Federation - Inbox Endpoint
 *
 * Handles incoming POST requests to board/shared inboxes.
 * Validates HTTP Signatures, verifies Digest, deduplicates, and queues for processing.
 *
 * ?action=activitypub;sa=inbox;type=board;id=X  (board inbox)
 * ?action=activitypub;sa=inbox                   (shared inbox)
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Handle inbox POST requests.
 */
function ActivityPubInbox()
{
	global $sourcedir, $smcFunc;

	// Handle CORS preflight.
	if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
	{
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST, OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type, Signature, Digest, Date');
		header('HTTP/1.1 204 No Content');
		exit;
	}

	if ($_SERVER['REQUEST_METHOD'] !== 'POST')
	{
		activitypub_json_response(array('error' => 'Method not allowed'), 405);
		return;
	}

	require_once($sourcedir . '/Subs-ActivityPub-HttpSig.php');
	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

	// Read the request body.
	$body = file_get_contents('php://input');
	if (empty($body))
	{
		activitypub_json_response(array('error' => 'Empty body'), 400);
		return;
	}

	// Verify Digest header.
	if (!activitypub_verify_digest($body))
	{
		activitypub_json_response(array('error' => 'Digest mismatch'), 401);
		return;
	}

	// Parse JSON.
	$activity = json_decode($body, true);
	if (empty($activity) || !isset($activity['type']))
	{
		activitypub_json_response(array('error' => 'Invalid JSON'), 400);
		return;
	}

	// Check required fields.
	if (empty($activity['actor']))
	{
		activitypub_json_response(array('error' => 'Missing actor'), 400);
		return;
	}

	$actor_url = is_string($activity['actor']) ? $activity['actor'] : (isset($activity['actor']['id']) ? $activity['actor']['id'] : '');

	// Check domain block.
	$domain = activitypub_extract_domain($actor_url);
	$block = activitypub_check_domain_block($domain);
	if ($block === 'block')
	{
		activitypub_json_response(array('error' => 'Domain blocked'), 403);
		return;
	}

	// Rate limiting.
	if (!activitypub_check_rate_limit($domain))
	{
		activitypub_json_response(array('error' => 'Rate limited'), 429);
		return;
	}

	// Verify HTTP Signature.
	$verified_actor = activitypub_verify_http_signature();
	if (empty($verified_actor))
	{
		activitypub_json_response(array('error' => 'Invalid signature'), 401);
		return;
	}

	// Verify that the signature's actor matches the activity's actor.
	$sig_actor_id = $verified_actor['id'];
	if ($sig_actor_id !== $actor_url)
	{
		activitypub_log_error('Actor mismatch: signature=' . $sig_actor_id . ' activity=' . $actor_url);
		activitypub_json_response(array('error' => 'Actor mismatch'), 403);
		return;
	}

	// Deduplicate: check if we've already processed this activity.
	$activity_id = isset($activity['id']) ? $activity['id'] : '';
	if (!empty($activity_id))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_activity
			FROM {db_prefix}ap_activities
			WHERE ap_id = {string:ap_id}',
			array('ap_id' => $activity_id)
		);
		if ($smcFunc['db_num_rows']($request) > 0)
		{
			$smcFunc['db_free_result']($request);
			// Already processed - return 202.
			activitypub_json_response(array('status' => 'already processed'), 202);
			return;
		}
		$smcFunc['db_free_result']($request);
	}

	// Determine target board (if board inbox).
	$target_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
	$target_id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

	// Store the remote actor.
	$remote_actor_id = activitypub_store_remote_actor($verified_actor);

	// Log the inbound activity.
	$db_activity_id = activitypub_log_activity($activity, 'inbound');

	// Process the activity based on type.
	$type = strtolower($activity['type']);

	switch ($type)
	{
		case 'follow':
			activitypub_process_follow($activity, $verified_actor, $remote_actor_id, $target_type, $target_id);
			break;

		case 'undo':
			activitypub_process_undo($activity, $verified_actor, $remote_actor_id);
			break;

		case 'create':
			activitypub_process_create($activity, $verified_actor, $remote_actor_id);
			break;

		case 'update':
			activitypub_process_update($activity, $verified_actor, $remote_actor_id);
			break;

		case 'delete':
			activitypub_process_delete($activity, $verified_actor, $remote_actor_id);
			break;

		case 'like':
			activitypub_process_like($activity, $verified_actor, $remote_actor_id);
			break;

		case 'announce':
			// We don't process remote Announce activities currently.
			break;

		case 'accept':
		case 'reject':
			// Handle responses to our Follow requests (future use).
			break;

		default:
			activitypub_log_error('Unknown activity type: ' . $activity['type']);
			break;
	}

	// Mark activity as completed.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ap_activities
		SET status = {string:status}, processed_at = {int:now}
		WHERE id_activity = {int:id}',
		array(
			'status' => 'completed',
			'now' => time(),
			'id' => $db_activity_id,
		)
	);

	activitypub_json_response(array('status' => 'accepted'), 202);
}

/**
 * Process a Follow activity.
 */
function activitypub_process_follow($activity, $actor_data, $remote_actor_id, $target_type, $target_id)
{
	global $smcFunc, $sourcedir, $modSettings;

	require_once($sourcedir . '/Subs-ActivityPub-Activity.php');
	require_once($sourcedir . '/Subs-ActivityPub-Delivery.php');

	$object = is_string($activity['object']) ? $activity['object'] : (isset($activity['object']['id']) ? $activity['object']['id'] : '');

	// Find the local actor being followed.
	$local_actor = activitypub_get_actor_by_ap_id($object);
	if (empty($local_actor) || empty($local_actor['is_local']))
	{
		activitypub_log_error('Follow target not found: ' . $object);
		return;
	}

	// Check if already following.
	$request = $smcFunc['db_query']('', '
		SELECT id_follow, status
		FROM {db_prefix}ap_followers
		WHERE actor_id = {int:actor}
			AND follower_id = {int:follower}',
		array(
			'actor' => $local_actor['id_actor'],
			'follower' => $remote_actor_id,
		)
	);

	$existing = null;
	if ($smcFunc['db_num_rows']($request) > 0)
		$existing = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$follow_ap_id = isset($activity['id']) ? $activity['id'] : '';

	if (!empty($existing))
	{
		// Update existing follow.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}ap_followers
			SET follow_activity_id = {string:follow_id},
				status = {string:status},
				accepted_at = {int:now}
			WHERE id_follow = {int:id}',
			array(
				'follow_id' => $follow_ap_id,
				'status' => 'accepted',
				'now' => time(),
				'id' => $existing['id_follow'],
			)
		);
	}
	else
	{
		// Create new follow.
		$auto_accept = !empty($modSettings['activitypub_auto_accept_follows']);
		$status = $auto_accept ? 'accepted' : 'pending';

		$smcFunc['db_insert']('insert',
			'{db_prefix}ap_followers',
			array(
				'actor_id' => 'int',
				'follower_id' => 'int',
				'follow_activity_id' => 'string',
				'status' => 'string',
				'created_at' => 'int',
				'accepted_at' => 'int',
			),
			array(
				$local_actor['id_actor'],
				$remote_actor_id,
				$follow_ap_id,
				$status,
				time(),
				$auto_accept ? time() : 0,
			),
			array('id_follow')
		);
	}

	// Send Accept (or Reject) response.
	if (!empty($modSettings['activitypub_auto_accept_follows']))
	{
		// Decrypt local actor's private key for signing.
		if (!empty($local_actor['private_key_pem']))
			$local_actor['private_key_pem_decrypted'] = activitypub_decrypt_private_key($local_actor['private_key_pem']);

		$accept = activitypub_build_accept_activity($local_actor, $activity);
		$accept_id = activitypub_log_activity($accept, 'outbound');

		// Deliver Accept directly to the follower's inbox.
		$inbox = $actor_data['inbox'];
		if (!empty($inbox))
		{
			activitypub_queue_single_delivery($accept, $local_actor['id_actor'], $inbox, $accept_id);
		}
	}
}

/**
 * Process an Undo activity (typically Undo Follow).
 */
function activitypub_process_undo($activity, $actor_data, $remote_actor_id)
{
	global $smcFunc;

	$object = isset($activity['object']) ? $activity['object'] : null;
	if (empty($object))
		return;

	$object_type = '';
	if (is_array($object) && isset($object['type']))
		$object_type = strtolower($object['type']);

	// Undo Follow.
	if ($object_type === 'follow')
	{
		$follow_object = is_string($object['object']) ? $object['object'] : (isset($object['object']['id']) ? $object['object']['id'] : '');

		$local_actor = activitypub_get_actor_by_ap_id($follow_object);
		if (empty($local_actor))
			return;

		// Remove the follow relationship.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ap_followers
			WHERE actor_id = {int:actor}
				AND follower_id = {int:follower}',
			array(
				'actor' => $local_actor['id_actor'],
				'follower' => $remote_actor_id,
			)
		);
	}
	elseif ($object_type === 'like')
	{
		// Undo Like - we don't track remote likes currently.
	}
}

/**
 * Process a Create activity (new post/reply).
 */
function activitypub_process_create($activity, $actor_data, $remote_actor_id)
{
	global $smcFunc, $sourcedir;

	$object = isset($activity['object']) ? $activity['object'] : null;
	if (empty($object) || !is_array($object))
		return;

	$object_type = isset($object['type']) ? strtolower($object['type']) : '';
	if (!in_array($object_type, array('note', 'article', 'page')))
		return;

	require_once($sourcedir . '/Subs-ActivityPub-Object.php');
	require_once($sourcedir . '/Subs-ActivityPub-Content.php');

	// Store the remote object.
	$object_id = activitypub_store_remote_object($object, $remote_actor_id);

	// Try to match to a local topic via inReplyTo or context.
	$in_reply_to = isset($object['inReplyTo']) ? $object['inReplyTo'] : '';
	$context = isset($object['context']) ? $object['context'] : '';
	$audience = isset($object['audience']) ? (is_string($object['audience']) ? $object['audience'] : '') : '';

	$local_topic_id = 0;
	$local_board_id = 0;

	// Check inReplyTo for a local object.
	if (!empty($in_reply_to))
	{
		$request = $smcFunc['db_query']('', '
			SELECT local_topic_id, local_board_id
			FROM {db_prefix}ap_objects
			WHERE ap_id = {string:ap_id}
				AND local_topic_id > 0',
			array('ap_id' => $in_reply_to)
		);
		if ($smcFunc['db_num_rows']($request) > 0)
		{
			$row = $smcFunc['db_fetch_assoc']($request);
			$local_topic_id = $row['local_topic_id'];
			$local_board_id = $row['local_board_id'];
		}
		$smcFunc['db_free_result']($request);
	}

	// Try context URL matching.
	if (empty($local_topic_id) && !empty($context))
	{
		$request = $smcFunc['db_query']('', '
			SELECT local_topic_id, local_board_id
			FROM {db_prefix}ap_objects
			WHERE context_url = {string:ctx}
				AND local_topic_id > 0
			LIMIT 1',
			array('ctx' => $context)
		);
		if ($smcFunc['db_num_rows']($request) > 0)
		{
			$row = $smcFunc['db_fetch_assoc']($request);
			$local_topic_id = $row['local_topic_id'];
			$local_board_id = $row['local_board_id'];
		}
		$smcFunc['db_free_result']($request);
	}

	// Try audience (board actor) matching.
	if (empty($local_board_id) && !empty($audience))
	{
		$board_actor = activitypub_get_actor_by_ap_id($audience);
		if (!empty($board_actor) && $board_actor['local_type'] === 'board')
		{
			$local_board_id = $board_actor['local_id'];
		}
	}

	// If we found a matching local topic, create a reply post.
	if (!empty($local_topic_id) && !empty($local_board_id))
	{
		activitypub_create_local_reply($object, $actor_data, $local_topic_id, $local_board_id, $object_id);
	}
}

/**
 * Create a local reply from a remote ActivityPub object.
 */
function activitypub_create_local_reply($object, $actor_data, $topic_id, $board_id, $ap_object_id)
{
	global $sourcedir, $smcFunc;

	require_once($sourcedir . '/Subs-Post.php');
	require_once($sourcedir . '/Subs-ActivityPub-Activity.php');
	require_once($sourcedir . '/Subs-ActivityPub-Delivery.php');

	// Convert HTML content to BBCode.
	$content = isset($object['content']) ? $object['content'] : '';
	$bbcode = activitypub_html_to_bbcode($content);

	if (empty($bbcode))
		return;

	// Determine poster name.
	$poster_name = '';
	if (!empty($actor_data['name']))
		$poster_name = $actor_data['name'];
	elseif (!empty($actor_data['preferredUsername']))
		$poster_name = $actor_data['preferredUsername'];

	$domain = activitypub_extract_domain($actor_data['id']);
	$poster_name = substr($poster_name, 0, 60) . '@' . $domain;

	// Build subject from topic.
	$request = $smcFunc['db_query']('', '
		SELECT m.subject
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
		WHERE t.id_topic = {int:topic}',
		array('topic' => $topic_id)
	);
	$subject = 'Re: ';
	if ($smcFunc['db_num_rows']($request) > 0)
	{
		$row = $smcFunc['db_fetch_assoc']($request);
		$subject = 'Re: ' . $row['subject'];
	}
	$smcFunc['db_free_result']($request);

	// Prepend attribution.
	$attribution = '[i]' . $poster_name . ' via ActivityPub:[/i]' . "\n\n";
	$bbcode = $attribution . $bbcode;

	// Create the post as a guest post.
	$msgOptions = array(
		'subject' => $subject,
		'body' => $bbcode,
		'smileys_enabled' => true,
		'attachments' => array(),
	);

	$topicOptions = array(
		'id' => $topic_id,
		'board' => $board_id,
		'lock_mode' => null,
		'sticky_mode' => null,
		'mark_as_read' => false,
		'is_approved' => true,
	);

	$posterOptions = array(
		'id' => 0,
		'name' => $poster_name,
		'email' => 'activitypub@' . $domain,
		'ip' => '127.0.0.1',
		'update_post_count' => false,
	);

	createPost($msgOptions, $topicOptions, $posterOptions);

	// Update the AP object with the local message ID.
	if (!empty($msgOptions['id']))
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}ap_objects
			SET local_msg_id = {int:msg},
				local_topic_id = {int:topic},
				local_board_id = {int:board}
			WHERE id_object = {int:obj_id}',
			array(
				'msg' => $msgOptions['id'],
				'topic' => $topic_id,
				'board' => $board_id,
				'obj_id' => $ap_object_id,
			)
		);

		// Board actor relays the content via Announce (FEP-1b12).
		$board_actor = activitypub_get_local_board_actor($board_id);
		if (!empty($board_actor))
		{
			if (!empty($board_actor['private_key_pem']))
				$board_actor['private_key_pem_decrypted'] = activitypub_decrypt_private_key($board_actor['private_key_pem']);

			$create_activity = array(
				'@context' => 'https://www.w3.org/ns/activitystreams',
				'id' => isset($object['id']) ? $object['id'] . '#create' : activitypub_generate_activity_id(),
				'type' => 'Create',
				'actor' => $actor_data['id'],
				'object' => $object,
			);

			$announce = activitypub_build_announce_activity($board_actor, $create_activity);
			$announce_id = activitypub_log_activity($announce, 'outbound');
			activitypub_queue_delivery_to_followers($board_actor, $announce, $announce_id);
		}
	}
}

/**
 * Process an Update activity.
 */
function activitypub_process_update($activity, $actor_data, $remote_actor_id)
{
	global $smcFunc, $sourcedir;

	$object = isset($activity['object']) ? $activity['object'] : null;
	if (empty($object) || !is_array($object))
		return;

	$ap_id = isset($object['id']) ? $object['id'] : '';
	if (empty($ap_id))
		return;

	require_once($sourcedir . '/Subs-ActivityPub-Content.php');

	// Find the existing object.
	$request = $smcFunc['db_query']('', '
		SELECT id_object, local_msg_id, local_board_id
		FROM {db_prefix}ap_objects
		WHERE ap_id = {string:ap_id}',
		array('ap_id' => $ap_id)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return;
	}

	$existing = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Update the stored object.
	$content = isset($object['content']) ? $object['content'] : '';
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ap_objects
		SET content = {string:content},
			updated_at = {int:now},
			raw_data = {string:raw}
		WHERE id_object = {int:id}',
		array(
			'content' => $content,
			'now' => time(),
			'raw' => json_encode($object, JSON_UNESCAPED_SLASHES),
			'id' => $existing['id_object'],
		)
	);

	// If this was a local reply, update the SMF message too.
	if (!empty($existing['local_msg_id']))
	{
		$bbcode = activitypub_html_to_bbcode($content);
		if (!empty($bbcode))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}messages
				SET body = {string:body}, modified_time = {int:now}
				WHERE id_msg = {int:msg}',
				array(
					'body' => $bbcode,
					'now' => time(),
					'msg' => $existing['local_msg_id'],
				)
			);
		}
	}
}

/**
 * Process a Delete activity.
 */
function activitypub_process_delete($activity, $actor_data, $remote_actor_id)
{
	global $smcFunc, $sourcedir;

	$object = $activity['object'];
	$object_id = is_string($object) ? $object : (isset($object['id']) ? $object['id'] : '');

	if (empty($object_id))
		return;

	// If it's a Tombstone or actor deletion.
	if (is_array($object) && isset($object['type']) && $object['type'] === 'Tombstone')
		$object_id = isset($object['id']) ? $object['id'] : '';

	// Find the object.
	$request = $smcFunc['db_query']('', '
		SELECT id_object, local_msg_id, local_board_id
		FROM {db_prefix}ap_objects
		WHERE ap_id = {string:ap_id}',
		array('ap_id' => $object_id)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return;
	}

	$existing = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Mark as deleted.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ap_objects
		SET is_deleted = 1
		WHERE id_object = {int:id}',
		array('id' => $existing['id_object'])
	);

	// If it created a local message, remove it.
	if (!empty($existing['local_msg_id']))
	{
		require_once($sourcedir . '/RemoveTopic.php');
		removeMessage($existing['local_msg_id']);
	}
}

/**
 * Process a Like activity.
 */
function activitypub_process_like($activity, $actor_data, $remote_actor_id)
{
	global $smcFunc;

	$object_id = is_string($activity['object']) ? $activity['object'] : (isset($activity['object']['id']) ? $activity['object']['id'] : '');
	if (empty($object_id))
		return;

	// Find the local object.
	$request = $smcFunc['db_query']('', '
		SELECT local_msg_id
		FROM {db_prefix}ap_objects
		WHERE ap_id = {string:ap_id}
			AND local_msg_id > 0',
		array('ap_id' => $object_id)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return;
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// We note the like but don't create SMF likes for remote users
	// since SMF likes require a member ID. This is logged as an activity.
}
