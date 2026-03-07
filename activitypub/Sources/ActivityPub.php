<?php
/**
 * ActivityPub Federation - Main Action Dispatcher
 *
 * Routes ?action=activitypub;sa=X to the appropriate handler.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

define('ACTIVITYPUB_VERSION', '1.0.0');

/**
 * Register the activitypub action.
 * Hook: integrate_actions
 */
function activitypub_actions(&$actionArray)
{
	$actionArray['activitypub'] = array('ActivityPub.php', 'ActivityPubMain');
}

/**
 * Main dispatcher for ?action=activitypub.
 * Routes to sub-action handlers based on ;sa= parameter.
 */
function ActivityPubMain()
{
	global $sourcedir, $modSettings;

	// Check if ActivityPub is enabled (allow admin access even when disabled).
	$sa = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : '';

	if (empty($modSettings['activitypub_enabled']) && !in_array($sa, array('admin', '')))
	{
		// Return 404 for AP endpoints when disabled.
		header('HTTP/1.1 404 Not Found');
		exit;
	}

	$subActions = array(
		'webfinger' => array('ActivityPub-WebFinger.php', 'ActivityPubWebFinger'),
		'actor' => array('ActivityPub-Actor.php', 'ActivityPubActor'),
		'inbox' => array('ActivityPub-Inbox.php', 'ActivityPubInbox'),
		'outbox' => array('ActivityPub-Outbox.php', 'ActivityPubOutbox'),
		'object' => array('ActivityPub-Object.php', 'ActivityPubObject'),
		'activity' => array('ActivityPub-Object.php', 'ActivityPubActivity'),
		'followers' => array('ActivityPub-Collection.php', 'ActivityPubFollowers'),
		'following' => array('ActivityPub-Collection.php', 'ActivityPubFollowing'),
		'nodeinfo' => array('ActivityPub-NodeInfo.php', 'ActivityPubNodeInfo'),
	);

	if (!isset($subActions[$sa]))
	{
		header('HTTP/1.1 404 Not Found');
		exit;
	}

	// Load the handler file and call the function.
	require_once($sourcedir . '/' . $subActions[$sa][0]);

	// Load core utilities.
	require_once($sourcedir . '/Subs-ActivityPub.php');

	$subActions[$sa][1]();
}
