<?php
/**
 * ActivityPub Federation - Collection Endpoints
 *
 * Serves Followers and Following OrderedCollections.
 * ?action=activitypub;sa=followers;type=board;id=X
 * ?action=activitypub;sa=following;type=board;id=X
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Serve the followers collection for an actor.
 */
function ActivityPubFollowers()
{
	global $sourcedir;

	if ($_SERVER['REQUEST_METHOD'] !== 'GET')
	{
		activitypub_json_response(array('error' => 'Method not allowed'), 405);
		return;
	}

	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'board';
	$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

	if (empty($id))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	// Get the actor.
	$actor = null;
	if ($type === 'board')
	{
		if (!activitypub_board_is_federated($id))
		{
			activitypub_json_response(array('error' => 'Not found'), 404);
			return;
		}
		$actor = activitypub_get_local_board_actor($id);
	}
	elseif ($type === 'user')
	{
		$actor = activitypub_get_local_user_actor($id);
	}

	if (empty($actor))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	$page = isset($_REQUEST['page']) ? max(0, (int) $_REQUEST['page']) : -1;
	$per_page = 20;
	$base = activitypub_base_url();
	$collection_url = $base . '?action=activitypub;sa=followers;type=' . $type . ';id=' . $id;

	$total = activitypub_get_follower_count($actor['id_actor']);

	// Collection summary (no page specified).
	if ($page < 0)
	{
		$response = array(
			'@context' => 'https://www.w3.org/ns/activitystreams',
			'id' => $collection_url,
			'type' => 'OrderedCollection',
			'totalItems' => $total,
		);

		if ($total > 0)
		{
			$response['first'] = $collection_url . ';page=0';
			$response['last'] = $collection_url . ';page=' . max(0, (int) ceil($total / $per_page) - 1);
		}

		activitypub_json_response($response);
		return;
	}

	// Paginated response.
	$followers = activitypub_get_followers($actor['id_actor'], $page, $per_page);

	$response = array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => $collection_url . ';page=' . $page,
		'type' => 'OrderedCollectionPage',
		'partOf' => $collection_url,
		'totalItems' => $total,
		'orderedItems' => $followers,
	);

	if (($page + 1) * $per_page < $total)
		$response['next'] = $collection_url . ';page=' . ($page + 1);
	if ($page > 0)
		$response['prev'] = $collection_url . ';page=' . ($page - 1);

	activitypub_json_response($response);
}

/**
 * Serve the following collection for an actor.
 * Boards don't follow anyone, so this is always empty.
 */
function ActivityPubFollowing()
{
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'board';
	$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

	$base = activitypub_base_url();
	$collection_url = $base . '?action=activitypub;sa=following;type=' . $type . ';id=' . $id;

	$response = array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => $collection_url,
		'type' => 'OrderedCollection',
		'totalItems' => 0,
		'orderedItems' => array(),
	);

	activitypub_json_response($response);
}
