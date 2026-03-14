<?php

/**
 * Global Announcements - Hook callbacks
 *
 * All hook callback functions for the Global Announcements modification.
 *
 * @package GlobalAnnouncements
 * @license MIT
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Register the globalannouncements action.
 *
 * Hook: integrate_actions
 */
function globalannouncements_actions(&$actionArray)
{
	$actionArray['globalannouncements'] = array('GlobalAnnouncements.php', 'GlobalAnnouncementsMain');
}

/**
 * Add admin areas for Global Announcements.
 *
 * Hook: integrate_admin_areas
 */
function globalannouncements_admin_areas(&$admin_areas)
{
	global $txt;

	loadLanguage('GlobalAnnouncements');

	$admin_areas['config']['areas']['globalannouncements'] = array(
		'label' => $txt['globalannouncements_title'],
		'function' => 'GlobalAnnouncementsAdmin',
		'file' => 'GlobalAnnouncements.php',
		'icon' => 'posts',
		'subsections' => array(
			'list' => array($txt['globalannouncements_manage']),
			'add' => array($txt['globalannouncements_add_new']),
			'settings' => array($txt['globalannouncements_settings']),
		),
	);
}

/**
 * Register permissions for global announcements.
 *
 * Hook: integrate_load_permissions
 */
function globalannouncements_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$permissionGroups['membergroup'][] = 'globalannouncements';

	$permissionList['membergroup']['globalannouncements_manage'] = array(false, 'globalannouncements');
	$permissionList['membergroup']['globalannouncements_comment'] = array(false, 'globalannouncements');
}

/**
 * Load CSS and language on relevant pages, handle force-read redirect.
 *
 * Hook: integrate_load_theme
 */
function globalannouncements_load_theme()
{
	global $modSettings, $user_info, $smcFunc, $scripturl;

	// Must be enabled.
	if (empty($modSettings['globalannouncements_enabled']))
		return;

	// Load on board pages or when viewing an announcement.
	$is_board_page = isset($_REQUEST['board']);
	$is_announcement_page = isset($_REQUEST['action']) && $_REQUEST['action'] === 'globalannouncements';

	if ($is_board_page || $is_announcement_page)
	{
		loadLanguage('GlobalAnnouncements');
		loadTemplate('GlobalAnnouncements');
		loadCSSFile('globalannouncements.css', array('default_theme' => true, 'minimize' => true));
	}

	// Force-read check: redirect logged-in users to a specific announcement if they haven't viewed it.
	if (!empty($modSettings['globalannouncements_force_id']) && !$user_info['is_guest'])
	{
		$force_id = (int) $modSettings['globalannouncements_force_id'];

		// Don't redirect if already viewing the forced announcement.
		if ($is_announcement_page && isset($_REQUEST['aid']) && (int) $_REQUEST['aid'] == $force_id)
			return;

		// Don't redirect on admin pages.
		if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'admin')
			return;

		// Check if user has viewed this announcement.
		$request = $smcFunc['db_query']('', '
			SELECT id_announcement
			FROM {db_prefix}global_announcement_log
			WHERE id_announcement = {int:aid}
				AND id_member = {int:member}
			LIMIT 1',
			array(
				'aid' => $force_id,
				'member' => $user_info['id'],
			)
		);

		$has_viewed = $smcFunc['db_num_rows']($request) > 0;
		$smcFunc['db_free_result']($request);

		if (!$has_viewed)
		{
			// Verify the announcement exists and is enabled.
			$request = $smcFunc['db_query']('', '
				SELECT id_announcement
				FROM {db_prefix}global_announcements
				WHERE id_announcement = {int:aid}
					AND enabled = {int:enabled}
				LIMIT 1',
				array(
					'aid' => $force_id,
					'enabled' => 1,
				)
			);

			$exists = $smcFunc['db_num_rows']($request) > 0;
			$smcFunc['db_free_result']($request);

			if ($exists)
				redirectexit('action=globalannouncements;sa=view;aid=' . $force_id);
		}
	}
}

/**
 * Load announcements for the message index (board view).
 *
 * Hook: integrate_message_index
 */
function globalannouncements_message_index(&$message_index_selects, &$message_index_tables, &$message_index_parameters, &$message_index_wheres, &$topic_ids, &$message_index_topic_wheres)
{
	global $modSettings, $context, $smcFunc, $user_info, $scripturl;

	if (empty($modSettings['globalannouncements_enabled']))
		return;

	// Query enabled announcements.
	$request = $smcFunc['db_query']('', '
		SELECT id_announcement, id_member, member_name, title, body, boards, groups, views, num_comments, created_at, sort_order
		FROM {db_prefix}global_announcements
		WHERE enabled = {int:enabled}
		ORDER BY sort_order ASC, created_at DESC',
		array(
			'enabled' => 1,
		)
	);

	$announcements = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Filter by board.
		if (!empty($row['boards']))
		{
			$allowed_boards = explode(',', $row['boards']);
			if (!in_array((string) $context['current_board'], $allowed_boards))
				continue;
		}

		// Filter by membergroup.
		if (!empty($row['groups']))
		{
			$allowed_groups = explode(',', $row['groups']);
			$user_groups = array_map('strval', $user_info['groups']);

			if (empty(array_intersect($allowed_groups, $user_groups)))
				continue;
		}

		$announcements[] = array(
			'id' => $row['id_announcement'],
			'title' => $row['title'],
			'body' => $row['body'],
			'author' => array(
				'id' => $row['id_member'],
				'name' => $row['member_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			),
			'views' => $row['views'],
			'comments' => $row['num_comments'],
			'created_at' => $row['created_at'],
			'href' => $scripturl . '?action=globalannouncements;sa=view;aid=' . $row['id_announcement'],
		);
	}
	$smcFunc['db_free_result']($request);

	if (!empty($announcements))
	{
		$context['global_announcements'] = $announcements;
		$context['global_announcements_active'] = true;
	}
}

/**
 * Show what users are doing in Who's Online.
 *
 * Hook: integrate_whos_online
 */
function globalannouncements_whos_online($actions)
{
	global $txt, $smcFunc, $scripturl;

	if (!isset($actions['action']) || $actions['action'] !== 'globalannouncements')
		return false;

	loadLanguage('GlobalAnnouncements');

	if (!empty($actions['aid']))
	{
		$aid = (int) $actions['aid'];

		$request = $smcFunc['db_query']('', '
			SELECT title
			FROM {db_prefix}global_announcements
			WHERE id_announcement = {int:aid}
			LIMIT 1',
			array(
				'aid' => $aid,
			)
		);

		if ($smcFunc['db_num_rows']($request) > 0)
		{
			$row = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);

			return sprintf($txt['globalannouncements_who_viewing'], $scripturl . '?action=globalannouncements;sa=view;aid=' . $aid, $row['title']);
		}
		$smcFunc['db_free_result']($request);
	}

	return $txt['globalannouncements_who_viewing_list'];
}

/**
 * Add "Convert to Announcement" button to topic mod buttons.
 *
 * Hook: integrate_mod_buttons
 */
function globalannouncements_mod_buttons(&$mod_buttons)
{
	global $context, $scripturl, $txt;

	if (!allowedTo('globalannouncements_manage') || empty($context['current_topic']))
		return;

	loadLanguage('GlobalAnnouncements');

	$mod_buttons['globalannouncements_from_topic'] = array(
		'text' => 'globalannouncements_from_topic',
		'url' => $scripturl . '?action=globalannouncements;sa=fromtopic;topic=' . $context['current_topic'],
	);
}