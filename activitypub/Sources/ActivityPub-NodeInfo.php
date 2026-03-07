<?php
/**
 * ActivityPub Federation - NodeInfo Endpoint
 *
 * Serves NodeInfo 2.1 protocol data for instance discovery.
 * ?action=activitypub;sa=nodeinfo
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Serve NodeInfo 2.1 data.
 */
function ActivityPubNodeInfo()
{
	global $smcFunc, $forum_version, $modSettings;

	if ($_SERVER['REQUEST_METHOD'] !== 'GET')
	{
		activitypub_json_response(array('error' => 'Method not allowed'), 405);
		return;
	}

	// Determine which format was requested.
	$version = isset($_REQUEST['version']) ? $_REQUEST['version'] : '2.1';

	// If this is the well-known nodeinfo request, serve the discovery document.
	if (isset($_REQUEST['discovery']))
	{
		$base = activitypub_base_url();
		$response = array(
			'links' => array(
				array(
					'rel' => 'http://nodeinfo.diaspora.software/ns/schema/2.1',
					'href' => $base . '?action=activitypub;sa=nodeinfo;version=2.1',
				),
			),
		);

		header('Content-Type: application/json; charset=utf-8');
		header('Access-Control-Allow-Origin: *');
		echo json_encode($response, JSON_UNESCAPED_SLASHES);
		obExit(false);
		return;
	}

	// Get usage stats.
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) FROM {db_prefix}members',
		array()
	);
	list($total_users) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Active users in last month.
	$month_ago = time() - (30 * 86400);
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}members
		WHERE last_login > {int:cutoff}',
		array('cutoff' => $month_ago)
	);
	list($active_month) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Active users in last 6 months.
	$halfyear_ago = time() - (180 * 86400);
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}members
		WHERE last_login > {int:cutoff}',
		array('cutoff' => $halfyear_ago)
	);
	list($active_halfyear) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Post count.
	$total_posts = isset($modSettings['totalMessages']) ? (int) $modSettings['totalMessages'] : 0;

	// SMF version.
	$smf_version = isset($forum_version) ? preg_replace('/^SMF\s*/', '', $forum_version) : '2.1';

	$response = array(
		'version' => '2.1',
		'software' => array(
			'name' => 'smf',
			'version' => $smf_version,
			'repository' => 'https://github.com/SimpleMachines/SMF',
			'homepage' => 'https://www.simplemachines.org',
		),
		'protocols' => array('activitypub'),
		'usage' => array(
			'users' => array(
				'total' => (int) $total_users,
				'activeMonth' => (int) $active_month,
				'activeHalfyear' => (int) $active_halfyear,
			),
			'localPosts' => $total_posts,
		),
		'openRegistrations' => !empty($modSettings['registration_method']) && $modSettings['registration_method'] != 3,
		'metadata' => array(
			'nodeName' => !empty($modSettings['forum_name']) ? $modSettings['forum_name'] : 'SMF Forum',
			'nodeDescription' => '',
			'activitypubVersion' => ACTIVITYPUB_VERSION,
		),
	);

	header('Content-Type: application/json; charset=utf-8');
	header('Access-Control-Allow-Origin: *');
	echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	obExit(false);
}
