<?php
/**
 * ActivityPub Federation - Actor CRUD & Keypair Management
 *
 * Handles creation, retrieval, and serialization of local and remote actors.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Find the openssl.cnf config file path.
 *
 * On Linux this is auto-discovered, but Windows typically needs an explicit
 * path passed to openssl_pkey_new(). This checks common locations.
 *
 * @return string|null Path to openssl.cnf or null if not needed/found.
 */
function activitypub_find_openssl_config()
{
	// Linux/Mac: OpenSSL finds its config automatically.
	if (DIRECTORY_SEPARATOR === '/')
		return null;

	// Check OPENSSL_CONF environment variable first.
	$env = getenv('OPENSSL_CONF');
	if (!empty($env) && file_exists($env))
		return $env;

	// Use openssl_get_cert_locations() if available (PHP 7.4+).
	if (function_exists('openssl_get_cert_locations'))
	{
		$locations = openssl_get_cert_locations();
		if (!empty($locations['ini']) && file_exists($locations['ini']))
			return $locations['ini'];
	}

	// Common Windows paths relative to PHP installation.
	$candidates = array();

	// Relative to PHP binary.
	$php_dir = dirname(PHP_BINARY);
	$candidates[] = $php_dir . '\\extras\\ssl\\openssl.cnf';
	$candidates[] = $php_dir . '\\ssl\\openssl.cnf';
	$candidates[] = $php_dir . '\\openssl.cnf';
	$candidates[] = dirname($php_dir) . '\\apache\\conf\\openssl.cnf';

	// Relative to PHP extension dir.
	$ext_dir = ini_get('extension_dir');
	if (!empty($ext_dir))
	{
		$candidates[] = dirname($ext_dir) . '\\extras\\ssl\\openssl.cnf';
		$candidates[] = dirname($ext_dir) . '\\ssl\\openssl.cnf';
	}

	// System-wide.
	$candidates[] = 'C:\\Program Files\\Common Files\\SSL\\openssl.cnf';
	$candidates[] = 'C:\\Program Files\\OpenSSL\\openssl.cnf';
	$candidates[] = 'C:\\OpenSSL-Win64\\openssl.cnf';
	$candidates[] = 'C:\\OpenSSL-Win32\\openssl.cnf';

	foreach ($candidates as $path)
	{
		if (file_exists($path))
			return $path;
	}

	return null;
}

/**
 * Generate an RSA keypair for an actor.
 *
 * @return array|false Array with 'public' and 'private' PEM-encoded keys, or false on failure.
 */
function activitypub_generate_keypair()
{
	$config = array(
		'private_key_bits' => 2048,
		'private_key_type' => OPENSSL_KEYTYPE_RSA,
	);

	// On Windows, openssl_pkey_new() needs an explicit path to openssl.cnf.
	$openssl_cnf = activitypub_find_openssl_config();
	if ($openssl_cnf !== null)
		$config['config'] = $openssl_cnf;

	$resource = openssl_pkey_new($config);
	if ($resource === false)
	{
		activitypub_log_error('Failed to generate RSA keypair: ' . openssl_error_string());
		return false;
	}

	// Export also needs the config path on Windows.
	$export_config = !empty($openssl_cnf) ? array('config' => $openssl_cnf) : array();
	openssl_pkey_export($resource, $private_key_pem, null, $export_config);
	$details = openssl_pkey_get_details($resource);
	$public_key_pem = $details['key'];

	return array(
		'public' => $public_key_pem,
		'private' => $private_key_pem,
	);
}

/**
 * Get or create a local board actor.
 *
 * @param int $board_id The board ID.
 * @return array|false The actor record or false.
 */
function activitypub_get_local_board_actor($board_id)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}ap_actors
		WHERE is_local = {int:local}
			AND local_type = {string:type}
			AND local_id = {int:id}',
		array(
			'local' => 1,
			'type' => 'board',
			'id' => (int) $board_id,
		)
	);

	if ($smcFunc['db_num_rows']($request) > 0)
	{
		$actor = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		// Decrypt private key.
		if (!empty($actor['private_key_pem']))
			$actor['private_key_pem_decrypted'] = activitypub_decrypt_private_key($actor['private_key_pem']);

		return $actor;
	}
	$smcFunc['db_free_result']($request);

	// Create the actor.
	return activitypub_create_local_board_actor($board_id);
}

/**
 * Create a local board actor with keypair.
 *
 * @param int $board_id The board ID.
 * @return array|false The created actor record or false.
 */
function activitypub_create_local_board_actor($board_id)
{
	global $smcFunc;

	$board = activitypub_get_board_info($board_id);
	if (empty($board))
		return false;

	$keypair = activitypub_generate_keypair();
	if (empty($keypair))
		return false;

	$base = activitypub_base_url();
	$slug = activitypub_board_slug($board['name']);
	$ap_id = $base . '?action=activitypub;sa=actor;type=board;id=' . $board_id;

	$encrypted_private = activitypub_encrypt_private_key($keypair['private']);

	$smcFunc['db_insert']('insert',
		'{db_prefix}ap_actors',
		array(
			'ap_id' => 'string',
			'type' => 'string',
			'preferred_username' => 'string',
			'name' => 'string',
			'summary' => 'string',
			'inbox_url' => 'string',
			'outbox_url' => 'string',
			'shared_inbox_url' => 'string',
			'followers_url' => 'string',
			'following_url' => 'string',
			'url' => 'string',
			'icon_url' => 'string',
			'public_key_pem' => 'string',
			'private_key_pem' => 'string',
			'is_local' => 'int',
			'local_type' => 'string',
			'local_id' => 'int',
			'enabled' => 'int',
			'created_at' => 'int',
			'updated_at' => 'int',
		),
		array(
			$ap_id,
			'Group',
			$slug,
			$board['name'],
			strip_tags($board['description']),
			$base . '?action=activitypub;sa=inbox;type=board;id=' . $board_id,
			$base . '?action=activitypub;sa=outbox;type=board;id=' . $board_id,
			$base . '?action=activitypub;sa=inbox',
			$base . '?action=activitypub;sa=followers;type=board;id=' . $board_id,
			$base . '?action=activitypub;sa=following;type=board;id=' . $board_id,
			$base . '?board=' . $board_id . '.0',
			'',
			$keypair['public'],
			$encrypted_private,
			1,
			'board',
			$board_id,
			1,
			time(),
			time(),
		),
		array('id_actor')
	);

	return activitypub_get_local_board_actor($board_id);
}

/**
 * Get or create a local user actor.
 *
 * @param int $member_id The member ID.
 * @return array|false The actor record or false.
 */
function activitypub_get_local_user_actor($member_id)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}ap_actors
		WHERE is_local = {int:local}
			AND local_type = {string:type}
			AND local_id = {int:id}',
		array(
			'local' => 1,
			'type' => 'user',
			'id' => (int) $member_id,
		)
	);

	if ($smcFunc['db_num_rows']($request) > 0)
	{
		$actor = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($actor['private_key_pem']))
			$actor['private_key_pem_decrypted'] = activitypub_decrypt_private_key($actor['private_key_pem']);

		return $actor;
	}
	$smcFunc['db_free_result']($request);

	return false;
}

/**
 * Create a local user actor.
 *
 * @param int $member_id The member ID.
 * @return array|false The created actor record or false.
 */
function activitypub_create_local_user_actor($member_id)
{
	global $smcFunc;

	// Look up the member.
	$request = $smcFunc['db_query']('', '
		SELECT id_member, member_name, real_name
		FROM {db_prefix}members
		WHERE id_member = {int:id}',
		array('id' => (int) $member_id)
	);
	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return false;
	}
	$member = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$keypair = activitypub_generate_keypair();
	if (empty($keypair))
		return false;

	$base = activitypub_base_url();
	$username = strtolower($member['member_name']);
	$ap_id = $base . '?action=activitypub;sa=actor;type=user;id=' . $member_id;

	$encrypted_private = activitypub_encrypt_private_key($keypair['private']);

	$smcFunc['db_insert']('insert',
		'{db_prefix}ap_actors',
		array(
			'ap_id' => 'string',
			'type' => 'string',
			'preferred_username' => 'string',
			'name' => 'string',
			'summary' => 'string',
			'inbox_url' => 'string',
			'outbox_url' => 'string',
			'shared_inbox_url' => 'string',
			'followers_url' => 'string',
			'following_url' => 'string',
			'url' => 'string',
			'icon_url' => 'string',
			'public_key_pem' => 'string',
			'private_key_pem' => 'string',
			'is_local' => 'int',
			'local_type' => 'string',
			'local_id' => 'int',
			'enabled' => 'int',
			'created_at' => 'int',
			'updated_at' => 'int',
		),
		array(
			$ap_id,
			'Person',
			$username,
			$member['real_name'],
			'',
			$base . '?action=activitypub;sa=inbox;type=user;id=' . $member_id,
			$base . '?action=activitypub;sa=outbox;type=user;id=' . $member_id,
			$base . '?action=activitypub;sa=inbox',
			$base . '?action=activitypub;sa=followers;type=user;id=' . $member_id,
			$base . '?action=activitypub;sa=following;type=user;id=' . $member_id,
			$base . '?action=profile;u=' . $member_id,
			'',
			$keypair['public'],
			$encrypted_private,
			1,
			'user',
			$member_id,
			1,
			time(),
			time(),
		),
		array('id_actor')
	);

	return activitypub_get_local_user_actor($member_id);
}

/**
 * Get an actor by its AP ID.
 *
 * @param string $ap_id The ActivityPub ID.
 * @return array|false The actor record or false.
 */
function activitypub_get_actor_by_ap_id($ap_id)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}ap_actors
		WHERE ap_id = {string:ap_id}',
		array('ap_id' => $ap_id)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return false;
	}

	$actor = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	return $actor;
}

/**
 * Get an actor by its DB row ID.
 *
 * @param int $id_actor The row ID.
 * @return array|false The actor record or false.
 */
function activitypub_get_actor_by_id($id_actor)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}ap_actors
		WHERE id_actor = {int:id}',
		array('id' => (int) $id_actor)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		return false;
	}

	$actor = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	return $actor;
}

/**
 * Store or update a remote actor from fetched data.
 *
 * @param array $data The actor JSON data.
 * @return int The actor row ID.
 */
function activitypub_store_remote_actor($data)
{
	global $smcFunc;

	$ap_id = $data['id'];

	// Check if we already have it.
	$existing = activitypub_get_actor_by_ap_id($ap_id);

	$values = array(
		'type' => isset($data['type']) ? $data['type'] : 'Person',
		'preferred_username' => isset($data['preferredUsername']) ? $data['preferredUsername'] : '',
		'name' => isset($data['name']) ? $data['name'] : '',
		'summary' => isset($data['summary']) ? strip_tags($data['summary']) : '',
		'inbox_url' => isset($data['inbox']) ? $data['inbox'] : '',
		'outbox_url' => isset($data['outbox']) ? $data['outbox'] : '',
		'shared_inbox_url' => '',
		'followers_url' => isset($data['followers']) ? $data['followers'] : '',
		'following_url' => isset($data['following']) ? $data['following'] : '',
		'url' => isset($data['url']) ? $data['url'] : $ap_id,
		'icon_url' => '',
		'public_key_pem' => '',
		'last_fetched' => time(),
		'updated_at' => time(),
		'raw_data' => json_encode($data, JSON_UNESCAPED_SLASHES),
	);

	// Extract shared inbox.
	if (isset($data['endpoints']['sharedInbox']))
		$values['shared_inbox_url'] = $data['endpoints']['sharedInbox'];

	// Extract icon.
	if (isset($data['icon']['url']))
		$values['icon_url'] = $data['icon']['url'];
	elseif (isset($data['icon']) && is_string($data['icon']))
		$values['icon_url'] = $data['icon'];

	// Extract public key.
	if (isset($data['publicKey']['publicKeyPem']))
		$values['public_key_pem'] = $data['publicKey']['publicKeyPem'];

	if (!empty($existing))
	{
		// Update existing.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}ap_actors
			SET type = {string:type},
				preferred_username = {string:preferred_username},
				name = {string:name},
				summary = {string:summary},
				inbox_url = {string:inbox_url},
				outbox_url = {string:outbox_url},
				shared_inbox_url = {string:shared_inbox_url},
				followers_url = {string:followers_url},
				following_url = {string:following_url},
				url = {string:url},
				icon_url = {string:icon_url},
				public_key_pem = {string:public_key_pem},
				last_fetched = {int:last_fetched},
				updated_at = {int:updated_at},
				raw_data = {string:raw_data}
			WHERE id_actor = {int:id}',
			array_merge($values, array('id' => $existing['id_actor']))
		);

		return $existing['id_actor'];
	}

	// Insert new.
	$smcFunc['db_insert']('insert',
		'{db_prefix}ap_actors',
		array(
			'ap_id' => 'string',
			'type' => 'string',
			'preferred_username' => 'string',
			'name' => 'string',
			'summary' => 'string',
			'inbox_url' => 'string',
			'outbox_url' => 'string',
			'shared_inbox_url' => 'string',
			'followers_url' => 'string',
			'following_url' => 'string',
			'url' => 'string',
			'icon_url' => 'string',
			'public_key_pem' => 'string',
			'private_key_pem' => 'string',
			'is_local' => 'int',
			'local_type' => 'string',
			'local_id' => 'int',
			'enabled' => 'int',
			'last_fetched' => 'int',
			'created_at' => 'int',
			'updated_at' => 'int',
			'raw_data' => 'string',
		),
		array(
			$ap_id,
			$values['type'],
			$values['preferred_username'],
			$values['name'],
			$values['summary'],
			$values['inbox_url'],
			$values['outbox_url'],
			$values['shared_inbox_url'],
			$values['followers_url'],
			$values['following_url'],
			$values['url'],
			$values['icon_url'],
			$values['public_key_pem'],
			'',
			0,
			'',
			0,
			1,
			time(),
			time(),
			time(),
			$values['raw_data'],
		),
		array('id_actor')
	);

	return $smcFunc['db_insert_id']('{db_prefix}ap_actors');
}

/**
 * Serialize a local actor to ActivityPub JSON-LD format.
 *
 * @param array $actor The actor DB record.
 * @return array The AP actor object.
 */
function activitypub_serialize_actor($actor)
{
	$data = array(
		'@context' => array(
			'https://www.w3.org/ns/activitystreams',
			'https://w3id.org/security/v1',
		),
		'id' => $actor['ap_id'],
		'type' => $actor['type'],
		'preferredUsername' => $actor['preferred_username'],
		'name' => $actor['name'],
		'summary' => $actor['summary'],
		'inbox' => $actor['inbox_url'],
		'outbox' => $actor['outbox_url'],
		'followers' => $actor['followers_url'],
		'following' => $actor['following_url'],
		'url' => $actor['url'],
		'published' => gmdate('Y-m-d\TH:i:s\Z', $actor['created_at']),
		'publicKey' => array(
			'id' => $actor['ap_id'] . '#main-key',
			'owner' => $actor['ap_id'],
			'publicKeyPem' => $actor['public_key_pem'],
		),
		'endpoints' => array(
			'sharedInbox' => $actor['shared_inbox_url'],
		),
	);

	if (!empty($actor['icon_url']))
	{
		$data['icon'] = array(
			'type' => 'Image',
			'mediaType' => 'image/png',
			'url' => $actor['icon_url'],
		);
	}

	return $data;
}

/**
 * Get the follower count for an actor.
 *
 * @param int $actor_id The actor row ID.
 * @return int Follower count.
 */
function activitypub_get_follower_count($actor_id)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}ap_followers
		WHERE actor_id = {int:actor}
			AND status = {string:status}',
		array(
			'actor' => (int) $actor_id,
			'status' => 'accepted',
		)
	);
	list($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return (int) $count;
}

/**
 * Get followers for an actor (paginated).
 *
 * @param int $actor_id The actor row ID.
 * @param int $page Page number (0-indexed).
 * @param int $per_page Items per page.
 * @return array Array of follower AP IDs.
 */
function activitypub_get_followers($actor_id, $page = 0, $per_page = 20)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT a.ap_id
		FROM {db_prefix}ap_followers AS f
			INNER JOIN {db_prefix}ap_actors AS a ON (a.id_actor = f.follower_id)
		WHERE f.actor_id = {int:actor}
			AND f.status = {string:status}
		ORDER BY f.created_at DESC
		LIMIT {int:start}, {int:limit}',
		array(
			'actor' => (int) $actor_id,
			'status' => 'accepted',
			'start' => $page * $per_page,
			'limit' => $per_page,
		)
	);

	$followers = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$followers[] = $row['ap_id'];
	$smcFunc['db_free_result']($request);

	return $followers;
}

/**
 * Get all follower actor records for delivery purposes.
 *
 * @param int $actor_id The local actor row ID.
 * @return array Array of follower actor records.
 */
function activitypub_get_follower_actors($actor_id)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT a.*
		FROM {db_prefix}ap_followers AS f
			INNER JOIN {db_prefix}ap_actors AS a ON (a.id_actor = f.follower_id)
		WHERE f.actor_id = {int:actor}
			AND f.status = {string:status}',
		array(
			'actor' => (int) $actor_id,
			'status' => 'accepted',
		)
	);

	$followers = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$followers[] = $row;
	$smcFunc['db_free_result']($request);

	return $followers;
}
