<?php
/**
 * ActivityPub Federation - Outbox Endpoint
 *
 * Serves the outbox OrderedCollection for board/user actors.
 * ?action=activitypub;sa=outbox;type=board;id=X
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Serve the outbox collection.
 */
function ActivityPubOutbox()
{
	global $sourcedir;

	if ($_SERVER['REQUEST_METHOD'] !== 'GET')
	{
		activitypub_json_response(array('error' => 'Method not allowed'), 405);
		return;
	}

	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');
	require_once($sourcedir . '/Subs-ActivityPub-Object.php');

	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'board';
	$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

	if ($type !== 'board' || empty($id))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	if (!activitypub_board_is_federated($id))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	$actor = activitypub_get_local_board_actor($id);
	if (empty($actor))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	$page = isset($_REQUEST['page']) ? max(0, (int) $_REQUEST['page']) : -1;
	$per_page = 20;
	$base = activitypub_base_url();
	$outbox_url = $base . '?action=activitypub;sa=outbox;type=board;id=' . $id;

	// If no page specified, return the collection summary.
	if ($page < 0)
	{
		$total = activitypub_get_outbox_count($id);

		$response = array(
			'@context' => 'https://www.w3.org/ns/activitystreams',
			'id' => $outbox_url,
			'type' => 'OrderedCollection',
			'totalItems' => $total,
			'first' => $outbox_url . ';page=0',
			'last' => $outbox_url . ';page=' . max(0, (int) ceil($total / $per_page) - 1),
		);

		activitypub_json_response($response);
		return;
	}

	// Return a page of activities.
	$items = activitypub_get_outbox_page($id, $page, $per_page);
	$total = activitypub_get_outbox_count($id);

	$response = array(
		'@context' => 'https://www.w3.org/ns/activitystreams',
		'id' => $outbox_url . ';page=' . $page,
		'type' => 'OrderedCollectionPage',
		'partOf' => $outbox_url,
		'totalItems' => $total,
		'orderedItems' => $items,
	);

	if (($page + 1) * $per_page < $total)
		$response['next'] = $outbox_url . ';page=' . ($page + 1);
	if ($page > 0)
		$response['prev'] = $outbox_url . ';page=' . ($page - 1);

	activitypub_json_response($response);
}

/**
 * Get the total number of outbox items for a board.
 *
 * @param int $board_id The board ID.
 * @return int Count.
 */
function activitypub_get_outbox_count($board_id)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}ap_objects
		WHERE local_board_id = {int:board}
			AND is_local = {int:local}
			AND is_deleted = {int:not_deleted}',
		array(
			'board' => (int) $board_id,
			'local' => 1,
			'not_deleted' => 0,
		)
	);
	list($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return (int) $count;
}

/**
 * Get a page of outbox items for a board.
 *
 * @param int $board_id The board ID.
 * @param int $page Page number (0-indexed).
 * @param int $per_page Items per page.
 * @return array Array of activity objects.
 */
function activitypub_get_outbox_page($board_id, $page, $per_page)
{
	global $smcFunc, $sourcedir;

	require_once($sourcedir . '/Subs-ActivityPub-Activity.php');

	$request = $smcFunc['db_query']('', '
		SELECT o.*
		FROM {db_prefix}ap_objects AS o
		WHERE o.local_board_id = {int:board}
			AND o.is_local = {int:local}
			AND o.is_deleted = {int:not_deleted}
		ORDER BY o.published DESC
		LIMIT {int:start}, {int:limit}',
		array(
			'board' => (int) $board_id,
			'local' => 1,
			'not_deleted' => 0,
			'start' => $page * $per_page,
			'limit' => $per_page,
		)
	);

	$items = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$object = activitypub_deserialize_object($row);
		if (!empty($object))
		{
			$items[] = array(
				'type' => 'Create',
				'actor' => $row['audience_url'],
				'published' => gmdate('Y-m-d\TH:i:s\Z', $row['published']),
				'object' => $object,
			);
		}
	}
	$smcFunc['db_free_result']($request);

	return $items;
}
