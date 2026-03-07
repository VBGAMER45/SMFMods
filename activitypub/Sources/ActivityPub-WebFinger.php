<?php
/**
 * ActivityPub Federation - WebFinger Endpoint
 *
 * Handles WebFinger lookups for board and user actors.
 * ?action=activitypub;sa=webfinger&resource=acct:slug@domain
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Handle WebFinger requests.
 * Returns JRD+JSON for board and user actors.
 */
function ActivityPubWebFinger()
{
	global $modSettings;

	// Handle CORS preflight.
	if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
	{
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, OPTIONS');
		header('Access-Control-Allow-Headers: Accept');
		header('HTTP/1.1 204 No Content');
		exit;
	}

	if ($_SERVER['REQUEST_METHOD'] !== 'GET')
	{
		header('HTTP/1.1 405 Method Not Allowed');
		exit;
	}

	$resource = isset($_GET['resource']) ? $_GET['resource'] : '';
	if (empty($resource))
	{
		header('HTTP/1.1 400 Bad Request');
		header('Content-Type: application/json');
		echo json_encode(array('error' => 'Missing resource parameter'));
		exit;
	}

	// Parse acct: URI.
	$actor_data = activitypub_parse_webfinger_resource($resource);
	if (empty($actor_data))
	{
		header('HTTP/1.1 404 Not Found');
		header('Content-Type: application/json');
		echo json_encode(array('error' => 'Resource not found'));
		exit;
	}

	// Build JRD response.
	$response = array(
		'subject' => 'acct:' . $actor_data['username'] . '@' . activitypub_domain(),
		'aliases' => array(
			$actor_data['ap_id'],
			$actor_data['url'],
		),
		'links' => array(
			array(
				'rel' => 'self',
				'type' => 'application/activity+json',
				'href' => $actor_data['ap_id'],
			),
			array(
				'rel' => 'http://webfinger.net/rel/profile-page',
				'type' => 'text/html',
				'href' => $actor_data['url'],
			),
		),
	);

	header('Content-Type: application/jrd+json; charset=utf-8');
	header('Access-Control-Allow-Origin: *');
	echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	obExit(false);
}

/**
 * Parse a WebFinger resource and find the matching actor.
 *
 * @param string $resource The resource string (acct:slug@domain or URL).
 * @return array|false Actor data or false.
 */
function activitypub_parse_webfinger_resource($resource)
{
	global $modSettings, $sourcedir;

	$domain = activitypub_domain();
	$base = activitypub_base_url();

	// Handle acct: URIs.
	if (strpos($resource, 'acct:') === 0)
	{
		$resource = substr($resource, 5);
		$parts = explode('@', $resource, 2);

		if (count($parts) !== 2 || strtolower($parts[1]) !== strtolower($domain))
			return false;

		$username = strtolower($parts[0]);

		// Try to find a board with this slug.
		$board = activitypub_find_board_by_slug($username);
		if (!empty($board) && activitypub_board_is_federated($board['id_board']))
		{
			return array(
				'username' => $username,
				'ap_id' => $base . '?action=activitypub;sa=actor;type=board;id=' . $board['id_board'],
				'url' => $base . '?board=' . $board['id_board'] . '.0',
			);
		}

		// Try to find a user (if user actors are enabled).
		if (!empty($modSettings['activitypub_user_actors_enabled']))
		{
			require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

			global $smcFunc;
			$request = $smcFunc['db_query']('', '
				SELECT id_member, member_name
				FROM {db_prefix}members
				WHERE LOWER(member_name) = {string:name}',
				array('name' => $username)
			);

			if ($smcFunc['db_num_rows']($request) > 0)
			{
				$member = $smcFunc['db_fetch_assoc']($request);
				$smcFunc['db_free_result']($request);

				// Check if user has opted in.
				$actor = activitypub_get_local_user_actor($member['id_member']);
				if (!empty($actor) && !empty($actor['enabled']))
				{
					return array(
						'username' => $username,
						'ap_id' => $base . '?action=activitypub;sa=actor;type=user;id=' . $member['id_member'],
						'url' => $base . '?action=profile;u=' . $member['id_member'],
					);
				}
			}
			else
				$smcFunc['db_free_result']($request);
		}

		return false;
	}

	// Handle URL-based lookups.
	if (strpos($resource, $base) === 0 && strpos($resource, 'action=activitypub') !== false)
	{
		// It's one of our AP URLs, just return it.
		// Parse out the type and id.
		parse_str(parse_url($resource, PHP_URL_QUERY), $params);
		$type = isset($params['type']) ? $params['type'] : '';
		$id = isset($params['id']) ? (int) $params['id'] : 0;

		if ($type === 'board' && $id > 0)
		{
			$board = activitypub_get_board_info($id);
			if (!empty($board) && activitypub_board_is_federated($id))
			{
				return array(
					'username' => activitypub_board_slug($board['name']),
					'ap_id' => $resource,
					'url' => $base . '?board=' . $id . '.0',
				);
			}
		}

		return false;
	}

	return false;
}
