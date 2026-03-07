<?php
/**
 * ActivityPub Federation - Actor Endpoint
 *
 * Serves actor profiles (Board=Group, User=Person) in ActivityPub format.
 * ?action=activitypub;sa=actor;type=board;id=X
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Serve an actor profile.
 */
function ActivityPubActor()
{
	global $sourcedir, $modSettings;

	// Handle CORS preflight.
	if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
	{
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, OPTIONS');
		header('Access-Control-Allow-Headers: Accept, Signature');
		header('HTTP/1.1 204 No Content');
		exit;
	}

	if ($_SERVER['REQUEST_METHOD'] !== 'GET')
	{
		activitypub_json_response(array('error' => 'Method not allowed'), 405);
		return;
	}

	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
	$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

	if (empty($id) || !in_array($type, array('board', 'user')))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	if ($type === 'board')
	{
		activitypub_serve_board_actor($id);
	}
	elseif ($type === 'user')
	{
		activitypub_serve_user_actor($id);
	}
}

/**
 * Serve a board (Group) actor.
 */
function activitypub_serve_board_actor($board_id)
{
	if (!activitypub_board_is_federated($board_id))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	$actor = activitypub_get_local_board_actor($board_id);
	if (empty($actor))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	$data = activitypub_serialize_actor($actor);

	// Add Group-specific properties.
	$data['manuallyApprovesFollowers'] = false;

	// Add attachment with board description.
	if (!empty($actor['summary']))
	{
		$data['attachment'] = array(
			array(
				'type' => 'PropertyValue',
				'name' => 'Forum Board',
				'value' => '<a href="' . htmlspecialchars($actor['url']) . '">' . htmlspecialchars($actor['name']) . '</a>',
			),
		);
	}

	activitypub_json_response($data);
}

/**
 * Serve a user (Person) actor.
 */
function activitypub_serve_user_actor($member_id)
{
	global $modSettings;

	if (empty($modSettings['activitypub_user_actors_enabled']))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	$actor = activitypub_get_local_user_actor($member_id);
	if (empty($actor) || empty($actor['enabled']))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	$data = activitypub_serialize_actor($actor);
	$data['manuallyApprovesFollowers'] = false;

	activitypub_json_response($data);
}
