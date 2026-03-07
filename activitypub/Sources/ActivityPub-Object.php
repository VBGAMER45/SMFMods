<?php
/**
 * ActivityPub Federation - Object & Activity Endpoints
 *
 * Serves individual objects (posts) and activities in AP format.
 * ?action=activitypub;sa=object;id=X
 * ?action=activitypub;sa=activity;id=X
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Serve an individual AP object (post/note).
 */
function ActivityPubObject()
{
	global $sourcedir, $smcFunc;

	if ($_SERVER['REQUEST_METHOD'] !== 'GET')
	{
		activitypub_json_response(array('error' => 'Method not allowed'), 405);
		return;
	}

	require_once($sourcedir . '/Subs-ActivityPub-Object.php');

	$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
	if (empty($id))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	// Look up by local message ID.
	$request = $smcFunc['db_query']('', '
		SELECT o.*
		FROM {db_prefix}ap_objects AS o
		WHERE o.local_msg_id = {int:msg}
			AND o.is_deleted = {int:not_deleted}',
		array(
			'msg' => $id,
			'not_deleted' => 0,
		)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Verify the board is still federated.
	if (!empty($row['local_board_id']) && !activitypub_board_is_federated($row['local_board_id']))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	$object = activitypub_deserialize_object($row);
	if (empty($object))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	// Add context to top-level object.
	$object['@context'] = 'https://www.w3.org/ns/activitystreams';

	activitypub_json_response($object);
}

/**
 * Serve an individual activity.
 */
function ActivityPubActivity()
{
	global $smcFunc;

	if ($_SERVER['REQUEST_METHOD'] !== 'GET')
	{
		activitypub_json_response(array('error' => 'Method not allowed'), 405);
		return;
	}

	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	if (empty($id))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	// Look up by AP ID suffix or full AP ID.
	$base = activitypub_base_url();
	$ap_id = $base . '?action=activitypub;sa=activity;id=' . $id;

	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}ap_activities
		WHERE ap_id = {string:ap_id}
			AND direction = {string:direction}',
		array(
			'ap_id' => $ap_id,
			'direction' => 'outbound',
		)
	);

	if ($smcFunc['db_num_rows']($request) === 0)
	{
		$smcFunc['db_free_result']($request);
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$activity = json_decode($row['raw_data'], true);
	if (empty($activity))
	{
		activitypub_json_response(array('error' => 'Not found'), 404);
		return;
	}

	// Ensure @context is present.
	if (!isset($activity['@context']))
		$activity['@context'] = 'https://www.w3.org/ns/activitystreams';

	activitypub_json_response($activity);
}
