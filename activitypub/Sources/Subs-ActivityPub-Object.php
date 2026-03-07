<?php
/**
 * ActivityPub Federation - Object Serialization
 *
 * Creates and serializes AP objects (Note/Article) from SMF messages.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Create an AP object record from an SMF message.
 *
 * @param int $msg_id The message ID.
 * @return array|false The AP object data or false.
 */
function activitypub_create_object_from_message($msg_id)
{
	global $smcFunc, $sourcedir, $modSettings;

	require_once($sourcedir . '/Subs-ActivityPub-Content.php');
	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

	// Fetch the message.
	$request = $smcFunc['db_query']('', '
		SELECT m.id_msg, m.id_topic, m.id_board, m.id_member, m.subject,
			m.body, m.poster_name, m.poster_time, m.modified_time,
			t.id_first_msg
		FROM {db_prefix}messages AS m
			INNER JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic)
		WHERE m.id_msg = {int:msg}',
		array('msg' => (int) $msg_id)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return false;
	}

	$msg = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$base = activitypub_base_url();
	$is_first_post = ($msg['id_msg'] == $msg['id_first_msg']);

	// Determine object type.
	$content_mode = !empty($modSettings['activitypub_content_mode']) ? $modSettings['activitypub_content_mode'] : 'note';
	$type = ($is_first_post && $content_mode === 'article') ? 'Article' : 'Note';

	// Build AP ID.
	$ap_id = $base . '?action=activitypub;sa=object;id=' . $msg['id_msg'];

	// Board actor URL.
	$board_actor_url = $base . '?action=activitypub;sa=actor;type=board;id=' . $msg['id_board'];

	// Context URL (topic).
	$context_url = $base . '?topic=' . $msg['id_topic'] . '.0';

	// In reply to.
	$in_reply_to = '';
	if (!$is_first_post)
	{
		// Find the first post's AP object.
		$request = $smcFunc['db_query']('', '
			SELECT ap_id
			FROM {db_prefix}ap_objects
			WHERE local_msg_id = {int:msg}',
			array('msg' => (int) $msg['id_first_msg'])
		);
		if ($smcFunc['db_num_rows']($request) > 0)
		{
			$row = $smcFunc['db_fetch_assoc']($request);
			$in_reply_to = $row['ap_id'];
		}
		$smcFunc['db_free_result']($request);

		// Fallback: construct the AP ID for the first post.
		if (empty($in_reply_to))
			$in_reply_to = $base . '?action=activitypub;sa=object;id=' . $msg['id_first_msg'];
	}

	// Convert content.
	$html_content = activitypub_bbcode_to_html($msg['body']);

	// Build the human-readable URL.
	$msg_url = $base . '?topic=' . $msg['id_topic'] . '.msg' . $msg['id_msg'] . '#msg' . $msg['id_msg'];

	// Store in DB.
	$smcFunc['db_insert']('replace',
		'{db_prefix}ap_objects',
		array(
			'ap_id' => 'string',
			'type' => 'string',
			'actor_id' => 'int',
			'in_reply_to' => 'string',
			'context_url' => 'string',
			'audience_url' => 'string',
			'content' => 'string',
			'summary' => 'string',
			'url' => 'string',
			'published' => 'int',
			'updated_at' => 'int',
			'is_local' => 'int',
			'local_msg_id' => 'int',
			'local_topic_id' => 'int',
			'local_board_id' => 'int',
			'is_deleted' => 'int',
		),
		array(
			$ap_id,
			$type,
			0,
			$in_reply_to,
			$context_url,
			$board_actor_url,
			$html_content,
			$is_first_post ? $msg['subject'] : '',
			$msg_url,
			$msg['poster_time'],
			$msg['modified_time'] > 0 ? $msg['modified_time'] : $msg['poster_time'],
			1,
			$msg['id_msg'],
			$msg['id_topic'],
			$msg['id_board'],
			0,
		),
		array('ap_id')
	);

	// Return the serialized object.
	return activitypub_serialize_note($ap_id, $type, $board_actor_url, $html_content, array(
		'summary' => $is_first_post ? $msg['subject'] : '',
		'in_reply_to' => $in_reply_to,
		'context' => $context_url,
		'url' => $msg_url,
		'published' => gmdate('Y-m-d\TH:i:s\Z', $msg['poster_time']),
		'updated' => $msg['modified_time'] > 0 ? gmdate('Y-m-d\TH:i:s\Z', $msg['modified_time']) : null,
		'attributedTo' => $board_actor_url,
		'poster_name' => $msg['poster_name'],
	));
}

/**
 * Update an existing AP object from an edited SMF message.
 *
 * @param int $msg_id The message ID.
 * @return array|false The updated AP object data or false.
 */
function activitypub_update_object_from_message($msg_id)
{
	global $smcFunc, $sourcedir;

	require_once($sourcedir . '/Subs-ActivityPub-Content.php');

	// Fetch updated message.
	$request = $smcFunc['db_query']('', '
		SELECT m.id_msg, m.id_topic, m.id_board, m.subject, m.body,
			m.poster_time, m.modified_time,
			t.id_first_msg
		FROM {db_prefix}messages AS m
			INNER JOIN {db_prefix}topics AS t ON (t.id_topic = m.id_topic)
		WHERE m.id_msg = {int:msg}',
		array('msg' => (int) $msg_id)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return false;
	}

	$msg = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$html_content = activitypub_bbcode_to_html($msg['body']);
	$base = activitypub_base_url();
	$ap_id = $base . '?action=activitypub;sa=object;id=' . $msg['id_msg'];

	// Update the stored object.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ap_objects
		SET content = {string:content},
			summary = {string:summary},
			updated_at = {int:updated}
		WHERE local_msg_id = {int:msg}',
		array(
			'content' => $html_content,
			'summary' => ($msg['id_msg'] == $msg['id_first_msg']) ? $msg['subject'] : '',
			'updated' => $msg['modified_time'] > 0 ? $msg['modified_time'] : time(),
			'msg' => (int) $msg_id,
		)
	);

	// Fetch the full record.
	$request = $smcFunc['db_query']('', '
		SELECT * FROM {db_prefix}ap_objects WHERE local_msg_id = {int:msg}',
		array('msg' => (int) $msg_id)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return false;
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	return activitypub_deserialize_object($row);
}

/**
 * Serialize a Note/Article object for AP.
 *
 * @param string $ap_id The object's AP ID.
 * @param string $type 'Note' or 'Article'.
 * @param string $actor_url The attributedTo actor URL.
 * @param string $content The HTML content.
 * @param array $extra Additional properties.
 * @return array The AP object.
 */
function activitypub_serialize_note($ap_id, $type, $actor_url, $content, $extra = array())
{
	$object = array(
		'id' => $ap_id,
		'type' => $type,
		'attributedTo' => isset($extra['attributedTo']) ? $extra['attributedTo'] : $actor_url,
		'content' => $content,
		'to' => array('https://www.w3.org/ns/activitystreams#Public'),
		'cc' => array(),
	);

	if (!empty($extra['summary']))
	{
		if ($type === 'Article')
			$object['name'] = $extra['summary'];
		else
			$object['summary'] = $extra['summary'];
	}

	if (!empty($extra['in_reply_to']))
		$object['inReplyTo'] = $extra['in_reply_to'];

	if (!empty($extra['context']))
		$object['context'] = $extra['context'];

	if (!empty($extra['url']))
		$object['url'] = $extra['url'];

	if (!empty($extra['published']))
		$object['published'] = $extra['published'];

	if (!empty($extra['updated']))
		$object['updated'] = $extra['updated'];

	// Add audience (the board/group actor).
	if (!empty($actor_url))
		$object['audience'] = $actor_url;

	// CC the board's followers.
	$object['cc'][] = $actor_url . '/followers';

	// Add source for the original BBCode if available.
	if (!empty($extra['poster_name']))
	{
		// Tag the original poster.
		$object['tag'] = array();
	}

	return $object;
}

/**
 * Deserialize an AP object from a database row.
 *
 * @param array $row The database row.
 * @return array The AP object.
 */
function activitypub_deserialize_object($row)
{
	// If we have raw JSON data, use that.
	if (!empty($row['raw_data']))
	{
		$data = json_decode($row['raw_data'], true);
		if (!empty($data))
			return $data;
	}

	// Build from DB fields.
	$object = array(
		'id' => $row['ap_id'],
		'type' => $row['type'],
		'content' => $row['content'],
		'to' => array('https://www.w3.org/ns/activitystreams#Public'),
		'cc' => array(),
	);

	if (!empty($row['audience_url']))
	{
		$object['attributedTo'] = $row['audience_url'];
		$object['audience'] = $row['audience_url'];
	}

	if (!empty($row['summary']))
	{
		if ($row['type'] === 'Article')
			$object['name'] = $row['summary'];
		else
			$object['summary'] = $row['summary'];
	}

	if (!empty($row['in_reply_to']))
		$object['inReplyTo'] = $row['in_reply_to'];

	if (!empty($row['context_url']))
		$object['context'] = $row['context_url'];

	if (!empty($row['url']))
		$object['url'] = $row['url'];

	if (!empty($row['published']))
		$object['published'] = gmdate('Y-m-d\TH:i:s\Z', $row['published']);

	if (!empty($row['updated_at']) && $row['updated_at'] != $row['published'])
		$object['updated'] = gmdate('Y-m-d\TH:i:s\Z', $row['updated_at']);

	return $object;
}

/**
 * Store a remote AP object in the database.
 *
 * @param array $data The AP object data.
 * @param int $actor_id The actor row ID.
 * @return int The object row ID.
 */
function activitypub_store_remote_object($data, $actor_id = 0)
{
	global $smcFunc;

	$ap_id = isset($data['id']) ? $data['id'] : '';
	if (empty($ap_id))
		return 0;

	// Check for existing.
	$request = $smcFunc['db_query']('', '
		SELECT id_object FROM {db_prefix}ap_objects WHERE ap_id = {string:ap_id}',
		array('ap_id' => $ap_id)
	);
	if ($smcFunc['db_num_rows']($request) > 0)
	{
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
		return $row['id_object'];
	}
	$smcFunc['db_free_result']($request);

	$content = isset($data['content']) ? $data['content'] : '';
	$summary = '';
	if (isset($data['name']))
		$summary = $data['name'];
	elseif (isset($data['summary']))
		$summary = $data['summary'];

	$published = 0;
	if (isset($data['published']))
		$published = strtotime($data['published']);

	$smcFunc['db_insert']('insert',
		'{db_prefix}ap_objects',
		array(
			'ap_id' => 'string',
			'type' => 'string',
			'actor_id' => 'int',
			'in_reply_to' => 'string',
			'context_url' => 'string',
			'audience_url' => 'string',
			'content' => 'string',
			'summary' => 'string',
			'url' => 'string',
			'published' => 'int',
			'updated_at' => 'int',
			'is_local' => 'int',
			'local_msg_id' => 'int',
			'local_topic_id' => 'int',
			'local_board_id' => 'int',
			'is_deleted' => 'int',
			'raw_data' => 'string',
		),
		array(
			$ap_id,
			isset($data['type']) ? $data['type'] : 'Note',
			$actor_id,
			isset($data['inReplyTo']) ? (is_string($data['inReplyTo']) ? $data['inReplyTo'] : '') : '',
			isset($data['context']) ? $data['context'] : '',
			isset($data['audience']) ? (is_string($data['audience']) ? $data['audience'] : '') : '',
			$content,
			$summary,
			isset($data['url']) ? (is_string($data['url']) ? $data['url'] : $ap_id) : $ap_id,
			$published,
			$published,
			0,
			0,
			0,
			0,
			0,
			json_encode($data, JSON_UNESCAPED_SLASHES),
		),
		array('id_object')
	);

	return $smcFunc['db_insert_id']('{db_prefix}ap_objects');
}
