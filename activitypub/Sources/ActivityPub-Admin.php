<?php
/**
 * ActivityPub Federation - Admin Panel Controller
 *
 * Manages admin settings, per-board configuration, domain blocks,
 * and the federation status dashboard.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Register the admin area.
 * Hook: integrate_admin_areas
 */
function activitypub_admin_areas(&$admin_areas)
{
	global $txt;

	loadLanguage('ActivityPub');

	$admin_areas['config']['areas']['activitypub'] = array(
		'label' => isset($txt['activitypub_admin']) ? $txt['activitypub_admin'] : 'ActivityPub Federation',
		'function' => 'ActivityPubAdminMain',
		'file' => 'ActivityPub-Admin.php',
		'icon' => 'posts',
		'subsections' => array(
			'settings' => array(isset($txt['activitypub_settings']) ? $txt['activitypub_settings'] : 'Settings'),
			'boards' => array(isset($txt['activitypub_boards']) ? $txt['activitypub_boards'] : 'Board Federation'),
			'blocks' => array(isset($txt['activitypub_blocks']) ? $txt['activitypub_blocks'] : 'Domain Blocks'),
			'status' => array(isset($txt['activitypub_status']) ? $txt['activitypub_status'] : 'Status Dashboard'),
		),
	);
}

/**
 * Main admin controller - routes to sub-sections.
 */
function ActivityPubAdminMain()
{
	global $context, $txt, $sourcedir;

	isAllowedTo('admin_forum');

	loadLanguage('ActivityPub');
	loadTemplate('ActivityPub');

	require_once($sourcedir . '/Subs-ActivityPub.php');

	$context['page_title'] = isset($txt['activitypub_admin']) ? $txt['activitypub_admin'] : 'ActivityPub Federation';

	$sa = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : 'settings';

	$subActions = array(
		'settings' => 'ActivityPubAdminSettings',
		'boards' => 'ActivityPubAdminBoards',
		'blocks' => 'ActivityPubAdminBlocks',
		'status' => 'ActivityPubAdminStatus',
	);

	if (!isset($subActions[$sa]))
		$sa = 'settings';

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $context['page_title'],
		'description' => isset($txt['activitypub_admin_desc']) ? $txt['activitypub_admin_desc'] : '',
	);

	$subActions[$sa]();
}

/**
 * Global settings page.
 */
function ActivityPubAdminSettings()
{
	global $context, $txt, $scripturl, $modSettings;

	$context['sub_template'] = 'activitypub_admin_settings';
	$context['page_title'] .= ' - ' . $txt['activitypub_settings'];

	// Handle form save.
	if (isset($_POST['save']))
	{
		checkSession();

		$settings = array(
			'activitypub_enabled' => !empty($_POST['activitypub_enabled']) ? 1 : 0,
			'activitypub_auto_accept_follows' => !empty($_POST['activitypub_auto_accept_follows']) ? 1 : 0,
			'activitypub_user_actors_enabled' => !empty($_POST['activitypub_user_actors_enabled']) ? 1 : 0,
			'activitypub_user_opt_in' => !empty($_POST['activitypub_user_opt_in']) ? 1 : 0,
			'activitypub_content_mode' => isset($_POST['activitypub_content_mode']) && $_POST['activitypub_content_mode'] === 'article' ? 'article' : 'note',
			'activitypub_max_delivery_attempts' => max(1, min(20, (int) ($_POST['activitypub_max_delivery_attempts'] ?? 8))),
			'activitypub_delivery_batch_size' => max(10, min(200, (int) ($_POST['activitypub_delivery_batch_size'] ?? 50))),
			'activitypub_rate_limit_inbox' => max(10, min(10000, (int) ($_POST['activitypub_rate_limit_inbox'] ?? 100))),
		);

		updateSettings($settings);
		redirectexit('action=admin;area=activitypub;sa=settings');
	}

	// Prepare template data.
	$context['ap_settings'] = array(
		'enabled' => !empty($modSettings['activitypub_enabled']),
		'auto_accept_follows' => !empty($modSettings['activitypub_auto_accept_follows']),
		'user_actors_enabled' => !empty($modSettings['activitypub_user_actors_enabled']),
		'user_opt_in' => !empty($modSettings['activitypub_user_opt_in']),
		'content_mode' => !empty($modSettings['activitypub_content_mode']) ? $modSettings['activitypub_content_mode'] : 'note',
		'max_delivery_attempts' => !empty($modSettings['activitypub_max_delivery_attempts']) ? (int) $modSettings['activitypub_max_delivery_attempts'] : 8,
		'delivery_batch_size' => !empty($modSettings['activitypub_delivery_batch_size']) ? (int) $modSettings['activitypub_delivery_batch_size'] : 50,
		'rate_limit_inbox' => !empty($modSettings['activitypub_rate_limit_inbox']) ? (int) $modSettings['activitypub_rate_limit_inbox'] : 100,
	);
}

/**
 * Per-board federation settings.
 */
function ActivityPubAdminBoards()
{
	global $context, $txt, $smcFunc, $modSettings, $sourcedir;

	require_once($sourcedir . '/Subs-ActivityPub.php');
	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

	$context['sub_template'] = 'activitypub_admin_boards';
	$context['page_title'] .= ' - ' . $txt['activitypub_boards'];

	// Handle form save.
	if (isset($_POST['save']))
	{
		checkSession();

		$settings = array();
		if (isset($_POST['board_enabled']) && is_array($_POST['board_enabled']))
		{
			foreach ($_POST['board_enabled'] as $board_id => $value)
			{
				$board_id = (int) $board_id;
				$settings['activitypub_board_' . $board_id . '_enabled'] = !empty($value) ? '1' : '0';
			}
		}

		if (!empty($settings))
			updateSettings($settings);

		redirectexit('action=admin;area=activitypub;sa=boards');
	}

	// Load all boards.
	$request = $smcFunc['db_query']('', '
		SELECT b.id_board, b.name, b.description, b.member_groups, b.redirect,
			b.id_cat, c.name AS cat_name
		FROM {db_prefix}boards AS b
			LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		WHERE b.redirect = {string:empty}
		ORDER BY c.cat_order, b.board_order',
		array('empty' => '')
	);

	$context['ap_boards'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$is_public = activitypub_board_is_public($row['id_board']);
		$key = 'activitypub_board_' . $row['id_board'] . '_enabled';
		$enabled = isset($modSettings[$key]) ? !empty($modSettings[$key]) : $is_public;

		$follower_count = 0;
		$actor = activitypub_get_actor_by_ap_id(activitypub_base_url() . '?action=activitypub;sa=actor;type=board;id=' . $row['id_board']);
		if (!empty($actor))
			$follower_count = activitypub_get_follower_count($actor['id_actor']);

		$context['ap_boards'][] = array(
			'id' => $row['id_board'],
			'name' => $row['name'],
			'category' => $row['cat_name'],
			'is_public' => $is_public,
			'enabled' => $enabled,
			'handle' => activitypub_board_slug($row['name']) . '@' . activitypub_domain(),
			'followers' => $follower_count,
		);
	}
	$smcFunc['db_free_result']($request);
}

/**
 * Domain block management.
 */
function ActivityPubAdminBlocks()
{
	global $context, $txt, $smcFunc, $user_info;

	$context['sub_template'] = 'activitypub_admin_blocks';
	$context['page_title'] .= ' - ' . $txt['activitypub_blocks'];

	// Handle add block.
	if (isset($_POST['add_block']))
	{
		checkSession();

		$domain = strtolower(trim($_POST['block_domain'] ?? ''));
		$type = ($_POST['block_type'] ?? 'block') === 'silence' ? 'silence' : 'block';
		$reason = substr(trim($_POST['block_reason'] ?? ''), 0, 512);

		if (!empty($domain))
		{
			$smcFunc['db_insert']('replace',
				'{db_prefix}ap_blocks',
				array(
					'domain' => 'string',
					'block_type' => 'string',
					'reason' => 'string',
					'created_by' => 'int',
					'created_at' => 'int',
				),
				array($domain, $type, $reason, $user_info['id'], time()),
				array('domain')
			);
		}

		redirectexit('action=admin;area=activitypub;sa=blocks');
	}

	// Handle remove block.
	if (isset($_POST['remove_block']))
	{
		checkSession();

		$id = (int) ($_POST['remove_id'] ?? 0);
		if ($id > 0)
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}ap_blocks WHERE id_block = {int:id}',
				array('id' => $id)
			);
		}

		redirectexit('action=admin;area=activitypub;sa=blocks');
	}

	// Load current blocks.
	$request = $smcFunc['db_query']('', '
		SELECT b.*, m.real_name AS created_by_name
		FROM {db_prefix}ap_blocks AS b
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = b.created_by)
		ORDER BY b.domain',
		array()
	);

	$context['ap_blocks'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['ap_blocks'][] = $row;
	$smcFunc['db_free_result']($request);
}

/**
 * Federation status dashboard.
 */
function ActivityPubAdminStatus()
{
	global $context, $txt, $smcFunc, $modSettings;

	$context['sub_template'] = 'activitypub_admin_status';
	$context['page_title'] .= ' - ' . $txt['activitypub_status'];

	$context['ap_status'] = array(
		'enabled' => !empty($modSettings['activitypub_enabled']),
		'domain' => activitypub_domain(),
	);

	// Federated board count.
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}ap_actors
		WHERE is_local = 1 AND local_type = {string:board} AND enabled = 1',
		array('board' => 'board')
	);
	list($context['ap_status']['federated_boards']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Total followers.
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}ap_followers
		WHERE status = {string:accepted}',
		array('accepted' => 'accepted')
	);
	list($context['ap_status']['total_followers']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Delivery queue stats.
	$queue_stats = array('queued' => 0, 'delivered' => 0, 'failed' => 0, 'abandoned' => 0);
	$request = $smcFunc['db_query']('', '
		SELECT status, COUNT(*) AS cnt
		FROM {db_prefix}ap_delivery_queue
		GROUP BY status',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$queue_stats[$row['status']] = (int) $row['cnt'];
	$smcFunc['db_free_result']($request);
	$context['ap_status']['queue'] = $queue_stats;

	// Recent activities.
	$request = $smcFunc['db_query']('', '
		SELECT a.*, act.preferred_username, act.name AS actor_name
		FROM {db_prefix}ap_activities AS a
			LEFT JOIN {db_prefix}ap_actors AS act ON (act.id_actor = a.actor_id)
		ORDER BY a.created_at DESC
		LIMIT 20',
		array()
	);

	$context['ap_status']['recent_activities'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['ap_status']['recent_activities'][] = array(
			'type' => $row['type'],
			'direction' => $row['direction'],
			'status' => $row['status'],
			'actor_name' => $row['actor_name'] ?? $row['preferred_username'] ?? 'Unknown',
			'object_type' => $row['object_type'],
			'created_at' => $row['created_at'],
			'error' => $row['error_message'],
		);
	}
	$smcFunc['db_free_result']($request);
}
