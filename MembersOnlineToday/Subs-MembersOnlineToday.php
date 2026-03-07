<?php

/**
 * Members Online Today
 *
 * @package MembersOnlineToday
 * @author vbgamer45
 * @copyright 2024 vbgamer45
 * @license BSD 3-Clause
 * @version 1.0
 * @website https://www.smfhacks.com
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Adds mod settings to Admin > Configuration > Modification Settings.
 * Hooked to integrate_general_mod_settings.
 *
 * @param array &$config_vars The array of configuration variables
 */
function MembersOnlineToday_Settings(&$config_vars)
{
	global $txt;

	loadLanguage('MembersOnlineToday');

	$config_vars[] = '';
	$config_vars[] = $txt['mot_settings_heading'];

	$config_vars[] = array(
		'select',
		'mot_sort_field',
		array(
			'last_login' => $txt['mot_opt_last_login'],
			'member_name' => $txt['mot_opt_member_name'],
		),
	);

	$config_vars[] = array(
		'select',
		'mot_sort_direction',
		array(
			'desc' => $txt['mot_opt_desc'],
			'asc' => $txt['mot_opt_asc'],
		),
	);

	$config_vars[] = array(
		'select',
		'mot_time_range',
		array(
			'today' => $txt['mot_opt_today'],
			'24hours' => $txt['mot_opt_24hours'],
			'7days' => $txt['mot_opt_7days'],
		),
	);

	$config_vars[] = array(
		'select',
		'mot_visibility',
		array(
			'members' => $txt['mot_opt_vis_members'],
			'all' => $txt['mot_opt_vis_all'],
			'staff' => $txt['mot_opt_vis_staff'],
		),
	);
}

/**
 * Loads member data and registers the Info Center block.
 * Hooked to integrate_mark_read_button.
 */
function MembersOnlineToday_InfoCenter()
{
	global $context;

	loadTemplate('MembersOnlineToday');
	loadLanguage('MembersOnlineToday');

	$data = getMembersOnlineTodayData();

	$context['mot_members'] = $data['mot_members'];
	$context['mot_member_links'] = $data['mot_member_links'];
	$context['mot_groups'] = $data['mot_groups'];
	$context['mot_total_count'] = $data['mot_total_count'];
	$context['mot_hidden_count'] = $data['mot_hidden_count'];
	$context['mot_buddy_count'] = $data['mot_buddy_count'];
	$context['mot_can_view'] = $data['mot_can_view'];

	$context['info_center'][] = array(
		'tpl' => 'MembersOnlineToday',
		'txt' => 'mot_block_title',
	);
}

/**
 * Retrieves the list of members who have been online within the configured time range.
 *
 * @return array Member data, links, groups, counts, and access flag
 */
function getMembersOnlineTodayData()
{
	global $smcFunc, $modSettings, $user_info, $scripturl, $txt;

	$time_range = isset($modSettings['mot_time_range']) ? $modSettings['mot_time_range'] : 'today';
	$sort_field = isset($modSettings['mot_sort_field']) ? $modSettings['mot_sort_field'] : 'last_login';
	$sort_dir = isset($modSettings['mot_sort_direction']) ? $modSettings['mot_sort_direction'] : 'desc';
	$visibility = isset($modSettings['mot_visibility']) ? $modSettings['mot_visibility'] : 'members';

	// Determine access.
	$can_moderate = allowedTo('moderate_forum');

	if ($visibility === 'staff')
		$mot_can_view = $can_moderate;
	elseif ($visibility === 'members')
		$mot_can_view = !$user_info['is_guest'];
	else
		$mot_can_view = true;

	// Calculate time threshold.
	if ($time_range === 'today')
	{
		$tz = timezone_open(getUserTimezone());
		$dt = new DateTime('today', $tz);
		$time_threshold = $dt->getTimestamp();
	}
	elseif ($time_range === '7days')
		$time_threshold = time() - 604800;
	else
		$time_threshold = time() - 86400;

	$members = array();
	$member_links = array();
	$groups = array();
	$total_count = 0;
	$hidden_count = 0;
	$buddy_count = 0;

	if (!$mot_can_view)
	{
		return array(
			'mot_members' => $members,
			'mot_member_links' => $member_links,
			'mot_groups' => $groups,
			'mot_total_count' => $total_count,
			'mot_hidden_count' => $hidden_count,
			'mot_buddy_count' => $buddy_count,
			'mot_can_view' => $mot_can_view,
		);
	}

	$request = $smcFunc['db_query']('', '
		SELECT mem.id_member, mem.real_name, mem.member_name, mem.last_login,
			mem.show_online, mg.online_color, mg.id_group, mg.group_name
		FROM {db_prefix}members AS mem
			LEFT JOIN {db_prefix}membergroups AS mg
				ON (mg.id_group = CASE WHEN mem.id_group = {int:regular_group}
					THEN mem.id_post_group ELSE mem.id_group END)
		WHERE mem.last_login >= {int:time_threshold}',
		array(
			'regular_group' => 0,
			'time_threshold' => $time_threshold,
		)
	);

	$sorted = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$is_hidden = empty($row['show_online']);

		// Skip hidden users for non-moderators.
		if ($is_hidden && !$can_moderate)
		{
			$total_count++;
			$hidden_count++;
			continue;
		}

		$total_count++;

		if ($is_hidden)
			$hidden_count++;

		$is_buddy = in_array((int) $row['id_member'], $user_info['buddies']);
		if ($is_buddy)
			$buddy_count++;

		// Build the color style.
		$color_start = '';
		$color_end = '';
		if (!empty($row['online_color']))
		{
			$color_start = '<span style="color: ' . $row['online_color'] . ';">';
			$color_end = '</span>';
		}

		$href = $scripturl . '?action=profile;u=' . $row['id_member'];
		$tooltip = $smcFunc['htmlspecialchars']($row['real_name']) . ' - ' . $txt['mot_last_login'] . ': ' . timeformat($row['last_login']);

		$link = '<a href="' . $href . '" title="' . $tooltip . '">' . $color_start . $smcFunc['htmlspecialchars']($row['real_name']) . $color_end . '</a>';

		if ($is_hidden)
			$link = '<em>' . $link . '</em>';

		$member_data = array(
			'id' => (int) $row['id_member'],
			'name' => $row['real_name'],
			'member_name' => $row['member_name'],
			'href' => $href,
			'link' => $link,
			'is_buddy' => $is_buddy,
			'hidden' => $is_hidden,
			'last_login' => (int) $row['last_login'],
		);

		// Track distinct groups.
		if (!empty($row['id_group']) && !isset($groups[$row['id_group']]))
		{
			$groups[$row['id_group']] = array(
				'id' => (int) $row['id_group'],
				'name' => $row['group_name'],
				'color' => $row['online_color'],
			);
		}

		// Build sort key.
		if ($sort_field === 'member_name')
			$sort_key = $smcFunc['strtolower']($row['member_name']);
		else
			$sort_key = $row['last_login'] . '-' . $smcFunc['strtolower']($row['member_name']);

		$sorted[$sort_key] = array(
			'member' => $member_data,
			'link' => $link,
		);
	}
	$smcFunc['db_free_result']($request);

	// Sort the results.
	if ($sort_dir === 'asc')
		ksort($sorted);
	else
		krsort($sorted);

	foreach ($sorted as $entry)
	{
		$members[] = $entry['member'];
		$member_links[] = $entry['link'];
	}

	return array(
		'mot_members' => $members,
		'mot_member_links' => $member_links,
		'mot_groups' => $groups,
		'mot_total_count' => $total_count,
		'mot_hidden_count' => $hidden_count,
		'mot_buddy_count' => $buddy_count,
		'mot_can_view' => $mot_can_view,
	);
}
