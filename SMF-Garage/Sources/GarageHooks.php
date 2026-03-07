<?php
/**********************************************************************************
 * GarageHooks.php                                                                 *
 ***********************************************************************************
 * SMF Garage: Simple Machines Forum Garage (MOD)                                  *
 * =============================================================================== *
 * Software Version:           SMF Garage 3.0.0                                    *
 * Install for:                2.1.0-2.1.99                                        *
 * Copyright 2026 by:          vbgamer45 (https://www.smfhacks.com)               *
 ***********************************************************************************
 * SMF integration hook handler functions for the Garage mod.                      *
 * Loaded via integrate_pre_include hook registered in dohooks.php.                *
 **********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Hook: integrate_actions
 * Registers garage action handlers with SMF's action router.
 * Replaces: index.php file edit
 */
function garage_hook_actions(&$actionArray)
{
	$actionArray += array(
		'garage' => array('Garage.php', 'Garage'),
		'garagemanagement' => array('GarageManagement.php', 'GarageManagement'),
		'garagesettings' => array('GarageSettings.php', 'GarageSettings'),
	);
}

/**
 * Hook: integrate_admin_areas
 * Adds garage admin panel areas.
 * Replaces: Admin.php file edits (loadLanguage + admin area definition)
 */
function garage_hook_admin_areas(&$admin_areas)
{
	global $txt;

	// Load the garage language
	loadLanguage('Garage');

	// Insert garage admin area after 'maintenance'
	garage_hook_array_insert($admin_areas, 'maintenance', array(
		'garage' => array(
			'title' => $txt['smfg_garage'],
			'permission' => array('manage_garage_settings', 'manage_garage'),
			'areas' => array(
				'garagesettings' => array(
					'label' => $txt['smfg_settings'],
					'file' => 'GarageSettings.php',
					'function' => 'GarageSettings',
					'icon' => 'server',
					'permission' => array('manage_garage_settings'),
					'subsections' => array(
						'general' => array($txt['smfg_general'], 'manage_garage_settings'),
						'menusettings' => array($txt['smfg_menu'], 'manage_garage_settings'),
						'indexsettings' => array($txt['smfg_index'], 'manage_garage_settings'),
						'imagesettings' => array($txt['smfg_images'], 'manage_garage_settings'),
						'videosettings' => array($txt['smfg_videos'], 'manage_garage_settings'),
						'modulesettings' => array($txt['smfg_modules'], 'manage_garage_settings'),
					),
				),
				'garagemanagement' => array(
					'label' => $txt['smfg_management'],
					'file' => 'GarageManagement.php',
					'function' => 'GarageManagement',
					'icon' => 'corefeatures',
					'permission' => array('manage_garage'),
					'subsections' => array(
						'business' => array($txt['smfg_businesses'], 'manage_garage'),
						'categories' => array($txt['smfg_categories'], 'manage_garage'),
						'makesmodels' => array($txt['smfg_mm'], 'manage_garage'),
						'products' => array($txt['smfg_products'], 'manage_garage'),
						'tracks' => array($txt['smfg_tracks'], 'manage_garage'),
						'other' => array($txt['smfg_other'], 'manage_garage'),
						'tools' => array($txt['smfg_tools'], 'manage_garage'),
						'pending' => array($txt['smfg_pending'], 'manage_garage'),
					),
				),
			),
		),
	), 'after');
}

/**
 * Hook: integrate_menu_buttons
 * Adds garage button to the main menu and shows admin button for garage admins.
 * Replaces: Subs.php menu button edit + admin permission edit + Admin.php isAllowedTo edit
 */
function garage_hook_menu_buttons(&$buttons)
{
	global $txt, $scripturl;

	// Load the garage language for the menu button title
	loadLanguage('Garage');

	// Add garage menu button after 'home'
	garage_hook_array_insert($buttons, 'home', array(
		'garage' => array(
			'title' => $txt['smfg_garage'],
			'icon' => 'car.png',
			'href' => $scripturl . '?action=garage',
			'show' => true,
			'sub_buttons' => array(),
		),
	), 'after');

	// Show admin button if user has garage admin permissions
	if (allowedTo(array('manage_garage_settings', 'manage_garage')))
		$buttons['admin']['show'] = true;
}

/**
 * Hook: integrate_load_permissions
 * Adds garage permission groups and permissions.
 * Replaces: 4 of 7 ManagePermissions.php file edits
 * (permissionGroups, permissionList, leftPermissionGroups)
 */
function garage_hook_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	// Add garage permission groups to membergroup
	$permissionGroups['membergroup'][] = 'garage';
	$permissionGroups['membergroup'][] = 'garage_acp';

	// Add garage permission groups to board
	$permissionGroups['board'][] = 'garage';
	$permissionGroups['board'][] = 'garage_acp';

	// Add all garage permissions to the permission list
	$permissionList['membergroup'] += array(
		'view_garage' => array(false, 'garage', 'garage'),
		'browse_vehicles' => array(false, 'garage', 'garage'),
		'view_vehicles' => array(false, 'garage', 'garage'),
		'own_vehicle' => array(false, 'garage', 'garage'),
		'search_vehicles' => array(false, 'garage', 'garage'),
		'browse_insurance' => array(false, 'garage', 'garage'),
		'view_insurance' => array(false, 'garage', 'garage'),
		'browse_shops' => array(false, 'garage', 'garage'),
		'view_shops' => array(false, 'garage', 'garage'),
		'browse_garages' => array(false, 'garage', 'garage'),
		'view_garages' => array(false, 'garage', 'garage'),
		'browse_qms' => array(false, 'garage', 'garage'),
		'view_qms' => array(false, 'garage', 'garage'),
		'browse_dynos' => array(false, 'garage', 'garage'),
		'view_dynos' => array(false, 'garage', 'garage'),
		'browse_laps' => array(false, 'garage', 'garage'),
		'view_laps' => array(false, 'garage', 'garage'),
		'post_comments' => array(false, 'garage', 'garage'),
		'manage_garage_settings' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_general' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_menu' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_index' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_images' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_videos' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_modules' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_businesses' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_categories' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_makes_models' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_products' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_tools' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_tracks' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_other' => array(false, 'garage_acp', 'garage_acp'),
		'manage_garage_pending' => array(false, 'garage_acp', 'garage_acp'),
		'edit_all_vehicles' => array(false, 'garage_acp', 'garage_acp'),
		'edit_all_comments' => array(false, 'garage_acp', 'garage_acp'),
		'limit_exemption' => array(false, 'garage_acp', 'garage_acp'),
	);

	// Add garage to left permission groups
	$leftPermissionGroups[] = 'garage';
}

/**
 * Hook: integrate_heavy_permissions_session
 * Registers garage management permissions as "heavy" permissions.
 * Replaces: Security.php file edit
 */
function garage_hook_heavy_permissions(&$heavy_permissions)
{
	$heavy_permissions[] = 'manage_garage';
	$heavy_permissions[] = 'manage_garage_settings';
}

/**
 * Hook: integrate_whos_online
 * Handles Who's Online display for garage actions (simple cases).
 * Return-value hook - returns display text string for the action.
 * Replaces: Part of Who.php file edits (inline action matching)
 */
function garage_hook_whos_online($actions)
{
	global $txt, $scripturl;

	// Not a garage-related action? Nothing to do.
	if (empty($actions['action']))
		return '';

	// Load language strings for Who's Online
	loadLanguage('Who');

	// Handle garagemanagement and garagesettings actions
	if ($actions['action'] == 'garagemanagement')
		return isset($txt['whoall_garagemanagement']) ? $txt['whoall_garagemanagement'] : '';
	if ($actions['action'] == 'garagesettings')
		return isset($txt['whoall_garagesettings']) ? $txt['whoall_garagesettings'] : '';

	// Not a garage action
	if ($actions['action'] != 'garage')
		return '';

	// Garage action without subaction
	if (empty($actions['sa']))
		return isset($txt['whoall_garage']) ? $txt['whoall_garage'] : '';

	// Simple subactions without IDs (no DB lookup needed)
	$simple_subactions = array(
		'browse', 'search', 'search_results', 'insurance', 'shops',
		'garages', 'quartermiles', 'dynoruns', 'laptimes', 'user_garage',
	);
	if (in_array($actions['sa'], $simple_subactions) && empty($actions['VID']) && empty($actions['BID']))
	{
		$key = 'whoall_garage_' . $actions['sa'];
		return isset($txt[$key]) ? $txt[$key] : '';
	}

	// Complex cases with IDs - return hidden text as placeholder.
	// whos_online_after will replace with proper text after batch DB lookups.
	if (!empty($actions['sa']))
		return isset($txt['who_hidden']) ? $txt['who_hidden'] : '';

	return '';
}

/**
 * Hook: whos_online_after
 * Handles batch database lookups for Who's Online garage entries with IDs.
 * Replaces: Part of Who.php file edits (batch query section)
 */
function garage_hook_whos_online_after(&$urls, &$data)
{
	global $smcFunc, $txt, $scripturl;

	// Load language strings
	loadLanguage('Who');

	$garage_ids = array();

	// Parse all URLs and collect garage IDs needing lookups
	foreach ($urls as $k => $url)
	{
		// Parse URL query string into key-value pairs
		$actions = array();
		$parts = explode(';', $url);
		foreach ($parts as $part)
		{
			$kv = explode('=', $part, 2);
			if (count($kv) == 2)
				$actions[urldecode($kv[0])] = urldecode($kv[1]);
		}

		if (!isset($actions['action']) || $actions['action'] != 'garage' || empty($actions['sa']))
			continue;

		$txt_key = 'whoall_garage_' . $actions['sa'];
		if (!isset($txt[$txt_key]))
			continue;

		// Collect IDs for batch processing based on available parameters
		if (!empty($actions['MID']) && !empty($actions['VID']))
			$garage_ids['MID'][(int) $actions['MID']][$k] = $txt[$txt_key];
		elseif (!empty($actions['DID']) && !empty($actions['VID']))
			$garage_ids['DID'][(int) $actions['DID']][$k] = $txt[$txt_key];
		elseif (!empty($actions['LID']) && !empty($actions['VID']))
			$garage_ids['LID'][(int) $actions['LID']][$k] = $txt[$txt_key];
		elseif (!empty($actions['QID']) && !empty($actions['VID']))
			$garage_ids['QID'][(int) $actions['QID']][$k] = $txt[$txt_key];
		elseif (!empty($actions['BID']) && !empty($actions['VID']))
			$garage_ids['VID'][(int) $actions['VID']][$k] = $txt[$txt_key];
		elseif (!empty($actions['INS_ID']) && !empty($actions['VID']))
			$garage_ids['VID'][(int) $actions['VID']][$k] = $txt[$txt_key];
		elseif (!empty($actions['SID']) && !empty($actions['VID']))
			$garage_ids['VID'][(int) $actions['VID']][$k] = $txt[$txt_key];
		elseif (!empty($actions['CID']) && !empty($actions['VID']))
			$garage_ids['VID'][(int) $actions['VID']][$k] = $txt[$txt_key];
		elseif (!empty($actions['BID']) && empty($actions['VID']))
			$garage_ids['BID'][(int) $actions['BID']][$k] = $txt[$txt_key];
		elseif (!empty($actions['VID']))
			$garage_ids['VID'][(int) $actions['VID']][$k] = $txt[$txt_key];
		else
			$data[$k] = isset($txt['whoall_garage_unknown']) ? $txt['whoall_garage_unknown'] : '';
	}

	// Batch query for each ID type
	if (empty($garage_ids))
		return;

	foreach ($garage_ids as $type => $ids)
	{
		if (empty($ids))
			continue;

		$ints = 1;
		switch ($type)
		{
			// Vehicles
			case 'VID':
				$query = "SELECT v.id AS id, CONCAT_WS( ' ', v.made_year, mk.make, md.model) AS description
					FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
					WHERE v.id IN (" . implode(', ', array_keys($ids)) . ")
						AND v.make_id = mk.id AND v.model_id = md.id
					LIMIT " . count($ids);
				break;
			// Modifications
			case 'MID':
				$query = "SELECT m.vehicle_id AS vid, m.id AS id, p.title AS description
					FROM {db_prefix}garage_products AS p, {db_prefix}garage_modifications AS m
					WHERE m.id IN (" . implode(', ', array_keys($ids)) . ")
						AND m.product_id = p.id
					LIMIT " . count($ids);
				$ints = 2;
				break;
			// Laptimes
			case 'LID':
				$query = "SELECT l.vehicle_id AS vid, l.id AS id, t.title AS description
					FROM {db_prefix}garage_laps AS l, {db_prefix}garage_tracks AS t
					WHERE l.id IN (" . implode(', ', array_keys($ids)) . ")
						AND t.id = l.track_id
					LIMIT " . count($ids);
				$ints = 2;
				break;
			// Dynoruns
			case 'DID':
				$query = "SELECT d.vehicle_id AS vid, d.id AS id, CONCAT_WS( ' ', d.bhp, d.bhp_unit, '/', d.torque, d.torque_unit, '/', d.nitrous) AS description
					FROM {db_prefix}garage_dynoruns AS d
					WHERE d.id IN (" . implode(', ', array_keys($ids)) . ")
					LIMIT " . count($ids);
				$ints = 2;
				break;
			// Quartermiles
			case 'QID':
				$query = "SELECT q.vehicle_id AS vid, q.id AS id, CONCAT_WS( ' ', q.quart, q.quartmph) AS description
					FROM {db_prefix}garage_quartermiles AS q
					WHERE q.id IN (" . implode(', ', array_keys($ids)) . ")
					LIMIT " . count($ids);
				$ints = 2;
				break;
			// Businesses
			case 'BID':
				$query = "SELECT b.id AS id, b.title AS description
					FROM {db_prefix}garage_business AS b
					WHERE b.id IN (" . implode(', ', array_keys($ids)) . ")
					LIMIT " . count($ids);
				break;
			default:
				continue 2;
		}

		$result = $smcFunc['db_query']('', $query);
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			foreach ($ids[$row['id']] as $k => $session_text)
				if ($ints == 2)
					$data[$k] = sprintf($session_text, $row['vid'], $row['id'], $row['description']);
				else
					$data[$k] = sprintf($session_text, $row['id'], $row['description']);
		}
		$smcFunc['db_free_result']($result);
	}
}

/**
 * Helper: Insert array elements before/after a specified key.
 * Same pattern as EzPortal's ezphook_array_insert().
 */
function garage_hook_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
{
	$position = array_search($key, array_keys($input), $strict);

	// Key not found -> insert as last
	if ($position === false)
	{
		$input = array_merge($input, $insert);
		return;
	}

	if ($where === 'after')
		$position += 1;

	// Insert at position
	if ($position === 0)
		$input = array_merge($insert, $input);
	else
		$input = array_merge(
			array_slice($input, 0, $position, true),
			$insert,
			array_slice($input, $position, null, true)
		);
}

?>
