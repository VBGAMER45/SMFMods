<?php
/**
 * ActivityPub Federation - Core Utilities & Hook Handlers
 *
 * Contains helper functions and all integration hook callbacks.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

// =========================================================================
// Core Helper Functions
// =========================================================================

/**
 * Send a JSON-LD ActivityPub response and exit.
 *
 * @param array $data The data to encode as JSON.
 * @param int $status HTTP status code.
 */
function activitypub_json_response($data, $status = 200)
{
	if ($status !== 200)
	{
		$statusMessages = array(
			201 => 'Created', 202 => 'Accepted', 400 => 'Bad Request',
			401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found',
			405 => 'Method Not Allowed', 409 => 'Conflict', 410 => 'Gone',
			429 => 'Too Many Requests', 500 => 'Internal Server Error',
		);
		$msg = isset($statusMessages[$status]) ? $statusMessages[$status] : 'Unknown';
		header('HTTP/1.1 ' . $status . ' ' . $msg);
	}

	header('Content-Type: application/activity+json; charset=utf-8');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: Accept, Content-Type, Signature, Digest');
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

	echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	obExit(false);
}

/**
 * Check if ActivityPub federation is globally enabled.
 *
 * @return bool
 */
function activitypub_is_enabled()
{
	global $modSettings;
	return !empty($modSettings['activitypub_enabled']);
}

/**
 * Check if a specific board is enabled for federation.
 *
 * @param int $board_id The board ID.
 * @return bool
 */
function activitypub_board_is_federated($board_id)
{
	global $modSettings;

	if (!activitypub_is_enabled())
		return false;

	// Check per-board setting (defaults to enabled if global is on).
	$key = 'activitypub_board_' . (int) $board_id . '_enabled';
	if (isset($modSettings[$key]))
		return !empty($modSettings[$key]);

	// Default: enabled for public boards.
	return activitypub_board_is_public($board_id);
}

/**
 * Check if a board is publicly accessible (guests can view).
 * Private boards must never be federated.
 *
 * @param int $board_id The board ID.
 * @return bool
 */
function activitypub_board_is_public($board_id)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT member_groups
		FROM {db_prefix}boards
		WHERE id_board = {int:board}',
		array(
			'board' => (int) $board_id,
		)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return false;
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Group -1 = guests. Board is public if guests are in member_groups.
	$groups = explode(',', $row['member_groups']);
	return in_array('-1', $groups) || in_array('0', $groups);
}

/**
 * Get the forum's base URL for AP identifiers.
 *
 * @return string Base URL without trailing slash.
 */
function activitypub_base_url()
{
	global $boardurl;
	return rtrim($boardurl, '/');
}

/**
 * Get the forum's domain name.
 *
 * @return string Domain name (e.g., "forum.example.com").
 */
function activitypub_domain()
{
	$base = activitypub_base_url();
	$parsed = parse_url($base);
	return $parsed['host'];
}

/**
 * Generate a URL-safe slug from a board name.
 *
 * @param string $name The board name.
 * @return string Lowercase slug.
 */
function activitypub_board_slug($name)
{
	$slug = strtolower(trim($name));
	$slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
	$slug = preg_replace('/-+/', '-', $slug);
	$slug = trim($slug, '-');
	return $slug;
}

/**
 * Get a board's info for AP purposes.
 *
 * @param int $board_id The board ID.
 * @return array|false Board info array or false if not found.
 */
function activitypub_get_board_info($board_id)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_board, name, description, member_groups, redirect
		FROM {db_prefix}boards
		WHERE id_board = {int:board}',
		array(
			'board' => (int) $board_id,
		)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return false;
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	return $row;
}

/**
 * Look up a board by its slug (for WebFinger).
 *
 * @param string $slug The board slug.
 * @return array|false Board info or false.
 */
function activitypub_find_board_by_slug($slug)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_board, name, description, member_groups
		FROM {db_prefix}boards
		WHERE redirect = {string:empty}',
		array(
			'empty' => '',
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (activitypub_board_slug($row['name']) === $slug)
		{
			$smcFunc['db_free_result']($request);
			return $row;
		}
	}

	$smcFunc['db_free_result']($request);
	return false;
}

/**
 * Check if a domain is blocked.
 *
 * @param string $domain The domain to check.
 * @return string|false Returns block type ('block'/'silence') or false.
 */
function activitypub_check_domain_block($domain)
{
	global $smcFunc;

	$domain = strtolower(trim($domain));

	$request = $smcFunc['db_query']('', '
		SELECT block_type
		FROM {db_prefix}ap_blocks
		WHERE domain = {string:domain}',
		array(
			'domain' => $domain,
		)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return false;
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	return $row['block_type'];
}

/**
 * Extract domain from an AP ID or URL.
 *
 * @param string $url The URL to extract domain from.
 * @return string The domain.
 */
function activitypub_extract_domain($url)
{
	$parsed = parse_url($url);
	return isset($parsed['host']) ? strtolower($parsed['host']) : '';
}

/**
 * Generate a unique AP ID for an activity.
 *
 * @return string Full AP ID URL.
 */
function activitypub_generate_activity_id()
{
	$base = activitypub_base_url();
	$uuid = sprintf(
		'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand(0, 0xffff), mt_rand(0, 0xffff),
		mt_rand(0, 0xffff),
		mt_rand(0, 0x0fff) | 0x4000,
		mt_rand(0, 0x3fff) | 0x8000,
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
	);
	return $base . '?action=activitypub;sa=activity;id=' . $uuid;
}

/**
 * Encrypt a private key for database storage.
 *
 * @param string $private_key_pem The PEM-encoded private key.
 * @return string Encrypted key (base64).
 */
function activitypub_encrypt_private_key($private_key_pem)
{
	global $modSettings;

	$key = hex2bin($modSettings['activitypub_encryption_key']);
	$iv = openssl_random_pseudo_bytes(16);
	$encrypted = openssl_encrypt($private_key_pem, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

	return base64_encode($iv . $encrypted);
}

/**
 * Decrypt a private key from database storage.
 *
 * @param string $encrypted_data The encrypted key (base64).
 * @return string|false PEM-encoded private key or false on failure.
 */
function activitypub_decrypt_private_key($encrypted_data)
{
	global $modSettings;

	$key = hex2bin($modSettings['activitypub_encryption_key']);
	$data = base64_decode($encrypted_data);

	if ($data === false || strlen($data) < 17)
		return false;

	$iv = substr($data, 0, 16);
	$ciphertext = substr($data, 16);

	return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
}

/**
 * Simple rate limiter for inbox requests. Checks per-domain request count.
 *
 * @param string $domain The requesting domain.
 * @return bool True if within limit, false if rate limited.
 */
function activitypub_check_rate_limit($domain)
{
	global $modSettings;

	$limit = !empty($modSettings['activitypub_rate_limit_inbox']) ? (int) $modSettings['activitypub_rate_limit_inbox'] : 100;
	$cache_key = 'ap_rate_' . md5($domain);
	$current = cache_get_data($cache_key, 3600);

	if ($current === null)
		$current = 0;

	if ($current >= $limit)
		return false;

	cache_put_data($cache_key, $current + 1, 3600);
	return true;
}

/**
 * Log an ActivityPub error for debugging.
 *
 * @param string $message Error message.
 * @param string $type Error type for categorization.
 */
function activitypub_log_error($message, $type = 'activitypub')
{
	log_error('[ActivityPub] ' . $message, $type);
}

// =========================================================================
// Integration Hook Handlers
// =========================================================================

/**
 * Hook: integrate_after_create_post
 * Triggered after a new post is created. Queue outbound federation.
 */
function activitypub_after_create_post($msgOptions, $topicOptions, $posterOptions, $message_columns, $message_parameters)
{
	global $sourcedir;

	if (!activitypub_is_enabled())
		return;

	$board_id = $topicOptions['board'];

	if (!activitypub_board_is_federated($board_id))
		return;

	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');
	require_once($sourcedir . '/Subs-ActivityPub-Object.php');
	require_once($sourcedir . '/Subs-ActivityPub-Activity.php');
	require_once($sourcedir . '/Subs-ActivityPub-Delivery.php');

	// Get or create the board actor.
	$board_actor = activitypub_get_local_board_actor($board_id);
	if (empty($board_actor))
		return;

	// Create the AP object for this post.
	$object = activitypub_create_object_from_message($msgOptions['id']);
	if (empty($object))
		return;

	// Build Create activity (from poster or board).
	$create_activity = activitypub_build_create_activity($board_actor, $object);

	// Log the activity.
	$activity_id = activitypub_log_activity($create_activity, 'outbound');

	// Board wraps in Announce (FEP-1b12 Group relay).
	$announce_activity = activitypub_build_announce_activity($board_actor, $create_activity);
	$announce_activity_id = activitypub_log_activity($announce_activity, 'outbound');

	// Queue delivery of the Announce to all followers.
	activitypub_queue_delivery_to_followers($board_actor, $announce_activity, $announce_activity_id);
}

/**
 * Hook: integrate_modify_post
 * Triggered after a post is edited. Send Update activity.
 */
function activitypub_after_modify_post($messages_columns, $update_parameters, $msgOptions, $topicOptions, $posterOptions)
{
	global $sourcedir, $smcFunc;

	if (!activitypub_is_enabled())
		return;

	// Get the board ID from the message.
	$msg_id = isset($msgOptions['id']) ? $msgOptions['id'] : 0;
	if (empty($msg_id))
		return;

	$request = $smcFunc['db_query']('', '
		SELECT m.id_board
		FROM {db_prefix}messages AS m
		WHERE m.id_msg = {int:msg}',
		array('msg' => $msg_id)
	);
	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return;
	}
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!activitypub_board_is_federated($row['id_board']))
		return;

	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');
	require_once($sourcedir . '/Subs-ActivityPub-Object.php');
	require_once($sourcedir . '/Subs-ActivityPub-Activity.php');
	require_once($sourcedir . '/Subs-ActivityPub-Delivery.php');

	$board_actor = activitypub_get_local_board_actor($row['id_board']);
	if (empty($board_actor))
		return;

	// Update the AP object.
	$object = activitypub_update_object_from_message($msg_id);
	if (empty($object))
		return;

	// Build Update activity.
	$update_activity = activitypub_build_update_activity($board_actor, $object);
	$activity_id = activitypub_log_activity($update_activity, 'outbound');

	// Announce the Update.
	$announce = activitypub_build_announce_activity($board_actor, $update_activity);
	$announce_id = activitypub_log_activity($announce, 'outbound');

	activitypub_queue_delivery_to_followers($board_actor, $announce, $announce_id);
}

/**
 * Hook: integrate_remove_message
 * Triggered when a message is deleted. Send Delete activity.
 */
function activitypub_after_remove_message($message, $row, $recycle)
{
	global $sourcedir, $smcFunc;

	if (!activitypub_is_enabled())
		return;

	// Don't send Delete if just recycling.
	if (!empty($recycle))
		return;

	// Look up the AP object for this message.
	$request = $smcFunc['db_query']('', '
		SELECT o.ap_id, o.local_board_id
		FROM {db_prefix}ap_objects AS o
		WHERE o.local_msg_id = {int:msg}
			AND o.is_local = {int:local}',
		array('msg' => (int) $message, 'local' => 1)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return;
	}

	$obj = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!activitypub_board_is_federated($obj['local_board_id']))
		return;

	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');
	require_once($sourcedir . '/Subs-ActivityPub-Activity.php');
	require_once($sourcedir . '/Subs-ActivityPub-Delivery.php');

	$board_actor = activitypub_get_local_board_actor($obj['local_board_id']);
	if (empty($board_actor))
		return;

	// Mark object as deleted.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ap_objects
		SET is_deleted = 1
		WHERE local_msg_id = {int:msg}',
		array('msg' => (int) $message)
	);

	$delete_activity = activitypub_build_delete_activity($board_actor, $obj['ap_id']);
	$activity_id = activitypub_log_activity($delete_activity, 'outbound');

	activitypub_queue_delivery_to_followers($board_actor, $delete_activity, $activity_id);
}

/**
 * Hook: integrate_remove_topics
 * Triggered when topics are removed.
 */
function activitypub_after_remove_topics($topics, $decreasePostCount, $ignoreRecycling)
{
	global $sourcedir, $smcFunc;

	if (!activitypub_is_enabled())
		return;

	if (!is_array($topics))
		$topics = array($topics);

	// Find all AP objects for messages in these topics.
	$request = $smcFunc['db_query']('', '
		SELECT o.ap_id, o.local_board_id, o.local_msg_id
		FROM {db_prefix}ap_objects AS o
		WHERE o.local_topic_id IN ({array_int:topics})
			AND o.is_local = {int:local}
			AND o.is_deleted = {int:not_deleted}',
		array(
			'topics' => $topics,
			'local' => 1,
			'not_deleted' => 0,
		)
	);

	$objects = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$objects[] = $row;
	$smcFunc['db_free_result']($request);

	if (empty($objects))
		return;

	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');
	require_once($sourcedir . '/Subs-ActivityPub-Activity.php');
	require_once($sourcedir . '/Subs-ActivityPub-Delivery.php');

	// Group by board.
	$by_board = array();
	foreach ($objects as $obj)
		$by_board[$obj['local_board_id']][] = $obj;

	foreach ($by_board as $board_id => $board_objects)
	{
		if (!activitypub_board_is_federated($board_id))
			continue;

		$board_actor = activitypub_get_local_board_actor($board_id);
		if (empty($board_actor))
			continue;

		foreach ($board_objects as $obj)
		{
			$delete_activity = activitypub_build_delete_activity($board_actor, $obj['ap_id']);
			$activity_id = activitypub_log_activity($delete_activity, 'outbound');
			activitypub_queue_delivery_to_followers($board_actor, $delete_activity, $activity_id);
		}
	}

	// Mark all as deleted.
	$msg_ids = array_column($objects, 'local_msg_id');
	if (!empty($msg_ids))
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}ap_objects
			SET is_deleted = 1
			WHERE local_msg_id IN ({array_int:msgs})',
			array('msgs' => $msg_ids)
		);
	}
}

/**
 * Hook: integrate_issue_like
 * Triggered when a user likes a post.
 */
function activitypub_after_issue_like($obj)
{
	global $sourcedir, $smcFunc;

	if (!activitypub_is_enabled())
		return;

	// $obj is the Likes class instance.
	$content_id = $obj->_content;
	$content_type = $obj->_type;

	// Only handle message likes.
	if ($content_type !== 'msg')
		return;

	// Get the board.
	$request = $smcFunc['db_query']('', '
		SELECT m.id_board
		FROM {db_prefix}messages AS m
		WHERE m.id_msg = {int:msg}',
		array('msg' => (int) $content_id)
	);
	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return;
	}
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!activitypub_board_is_federated($row['id_board']))
		return;

	// Check if the AP object exists for this message.
	$request = $smcFunc['db_query']('', '
		SELECT ap_id
		FROM {db_prefix}ap_objects
		WHERE local_msg_id = {int:msg}',
		array('msg' => (int) $content_id)
	);
	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return;
	}
	$ap_obj = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');
	require_once($sourcedir . '/Subs-ActivityPub-Activity.php');
	require_once($sourcedir . '/Subs-ActivityPub-Delivery.php');

	$board_actor = activitypub_get_local_board_actor($row['id_board']);
	if (empty($board_actor))
		return;

	$like_activity = activitypub_build_like_activity($board_actor, $ap_obj['ap_id']);
	$activity_id = activitypub_log_activity($like_activity, 'outbound');

	// Announce the Like.
	$announce = activitypub_build_announce_activity($board_actor, $like_activity);
	$announce_id = activitypub_log_activity($announce, 'outbound');

	activitypub_queue_delivery_to_followers($board_actor, $announce, $announce_id);
}

/**
 * Hook: integrate_board_info
 * Add AP-related info to board data.
 */
function activitypub_board_info(&$board_info, $row)
{
	if (!activitypub_is_enabled())
		return;

	$board_info['ap_federated'] = activitypub_board_is_federated($board_info['id']);
}

/**
 * Hook: integrate_profile_areas
 * Add ActivityPub section to user profile.
 */
function activitypub_profile_areas(&$profile_areas)
{
	global $modSettings, $txt;

	if (empty($modSettings['activitypub_enabled']) || empty($modSettings['activitypub_user_actors_enabled']))
		return;

	loadLanguage('ActivityPub');

	$profile_areas['info']['areas']['activitypub'] = array(
		'label' => isset($txt['activitypub_profile']) ? $txt['activitypub_profile'] : 'ActivityPub',
		'function' => 'activitypub_profile_main',
		'file' => 'Subs-ActivityPub.php',
		'permission' => array(
			'own' => 'profile_identity_any',
			'any' => 'profile_identity_any',
		),
	);
}

/**
 * Profile page for ActivityPub settings.
 */
function activitypub_profile_main($memID)
{
	global $context, $txt, $modSettings, $smcFunc, $sourcedir;

	loadLanguage('ActivityPub');
	loadTemplate('ActivityPub');

	$context['page_title'] = isset($txt['activitypub_profile']) ? $txt['activitypub_profile'] : 'ActivityPub';
	$context['sub_template'] = 'activitypub_profile';

	// Check if user has opted in.
	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

	$actor = activitypub_get_local_user_actor($memID);
	$context['ap_user_enabled'] = !empty($actor) && !empty($actor['enabled']);
	$context['ap_user_handle'] = '';

	if (!empty($actor))
	{
		$context['ap_user_handle'] = $actor['preferred_username'] . '@' . activitypub_domain();
	}

	// Handle form submission.
	if (isset($_POST['ap_save']) && isset($_SESSION['session_value']))
	{
		checkSession();

		$enabled = !empty($_POST['ap_user_enabled']) ? 1 : 0;

		if ($enabled && empty($actor))
		{
			activitypub_create_local_user_actor($memID);
		}
		elseif (!empty($actor))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}ap_actors
				SET enabled = {int:enabled}
				WHERE id_actor = {int:id}',
				array(
					'enabled' => $enabled,
					'id' => $actor['id_actor'],
				)
			);
		}

		redirectexit('action=profile;area=activitypub;u=' . $memID);
	}
}

/**
 * Hook: integrate_buffer
 * Inject <link rel="alternate"> tags for AP discovery.
 */
function activitypub_buffer_replace($buffer)
{
	global $context, $board, $topic, $modSettings;

	if (!activitypub_is_enabled())
		return $buffer;

	// Only inject on board or topic pages.
	if (empty($board) && empty($topic))
		return $buffer;

	$board_id = !empty($board) ? $board : 0;

	// If we're on a topic page, get the board.
	if (!empty($topic) && empty($board_id))
	{
		global $smcFunc;
		$request = $smcFunc['db_query']('', '
			SELECT id_board FROM {db_prefix}topics WHERE id_topic = {int:topic}',
			array('topic' => $topic)
		);
		if ($smcFunc['db_num_rows']($request) > 0)
		{
			$row = $smcFunc['db_fetch_assoc']($request);
			$board_id = $row['id_board'];
		}
		$smcFunc['db_free_result']($request);
	}

	if (empty($board_id) || !activitypub_board_is_federated($board_id))
		return $buffer;

	$base = activitypub_base_url();
	$actor_url = $base . '?action=activitypub;sa=actor;type=board;id=' . $board_id;

	$link_tag = "\n" . '<link rel="alternate" type="application/activity+json" href="' . htmlspecialchars($actor_url) . '" />';

	// Inject before </head>.
	$buffer = str_replace('</head>', $link_tag . "\n</head>", $buffer);

	return $buffer;
}

/**
 * Hook: integrate_load_permissions
 * Register AP-specific permissions.
 */
function activitypub_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$permissionList['membergroup']['activitypub_admin'] = array(false, 'activitypub', 'activitypub');
}

/**
 * Log an activity to the ap_activities table.
 *
 * @param array $activity The activity data.
 * @param string $direction 'inbound' or 'outbound'.
 * @return int The activity row ID.
 */
function activitypub_log_activity($activity, $direction = 'outbound')
{
	global $smcFunc;

	$ap_id = isset($activity['id']) ? $activity['id'] : activitypub_generate_activity_id();
	$type = isset($activity['type']) ? $activity['type'] : '';
	$object_ap_id = '';
	$object_type = '';

	if (isset($activity['object']))
	{
		if (is_string($activity['object']))
			$object_ap_id = $activity['object'];
		elseif (is_array($activity['object']))
		{
			$object_ap_id = isset($activity['object']['id']) ? $activity['object']['id'] : '';
			$object_type = isset($activity['object']['type']) ? $activity['object']['type'] : '';
		}
	}

	$smcFunc['db_insert']('insert',
		'{db_prefix}ap_activities',
		array(
			'ap_id' => 'string',
			'type' => 'string',
			'actor_id' => 'int',
			'object_ap_id' => 'string',
			'object_type' => 'string',
			'target_ap_id' => 'string',
			'direction' => 'string',
			'status' => 'string',
			'raw_data' => 'string',
			'created_at' => 'int',
		),
		array(
			$ap_id,
			$type,
			0,
			$object_ap_id,
			$object_type,
			isset($activity['target']) ? (is_string($activity['target']) ? $activity['target'] : '') : '',
			$direction,
			'pending',
			json_encode($activity, JSON_UNESCAPED_SLASHES),
			time(),
		),
		array('id_activity')
	);

	return $smcFunc['db_insert_id']('{db_prefix}ap_activities');
}
