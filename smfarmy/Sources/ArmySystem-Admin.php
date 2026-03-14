<?php
/**
 * Army System - Admin Control Panel
 *
 * Settings, race/item CRUD, member management, logs.
 * Provides the full administrative interface for managing the Army System
 * from within the SMF admin panel.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Main admin dispatcher.
 *
 * Called from the SMF admin panel via the integrate_admin_areas hook.
 * Loads common dependencies, validates admin permission, and routes
 * to the appropriate sub-action handler.
 *
 * Sub-actions:
 *   settings - System configuration (default)
 *   races    - Race CRUD management
 *   items    - Item/equipment CRUD management
 *   members  - Search and edit member army data
 *   logs     - View staff and attack logs
 *
 * @return void
 */
function ArmyAdmin()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	// Permission check - allow both admin_forum and army_admin
	isAllowedTo('admin_forum');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	// Load language and template
	loadTemplate('ArmySystem-Admin');
	loadLanguage('ArmySystem');

	// Sub-action routing
	$subActions = array(
		'settings' => 'ArmyAdminSettings',
		'races'    => 'ArmyAdminRaces',
		'items'    => 'ArmyAdminItems',
		'members'  => 'ArmyAdminMembers',
		'logs'     => 'ArmyAdminLogs',
	);

	$sa = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : 'settings';

	if (!isset($subActions[$sa]))
		$sa = 'settings';

	// Build admin tab navigation for the template
	$context['army_admin_tabs'] = array();

	foreach ($subActions as $key => $func)
	{
		$tab_labels = array(
			'settings' => $txt['army_admin_settings'] ?? 'Settings',
			'races'    => $txt['army_admin_races'] ?? 'Races',
			'items'    => $txt['army_admin_items'] ?? 'Items',
			'members'  => $txt['army_admin_members'] ?? 'Members',
			'logs'     => $txt['army_admin_logs'] ?? 'Logs',
		);

		$context['army_admin_tabs'][$key] = array(
			'label' => $tab_labels[$key],
			'url' => $scripturl . '?action=admin;area=armysystem;sa=' . $key,
			'active' => ($sa === $key),
		);
	}

	// Set page title and linktree
	$context['page_title'] = ($txt['army_admin_title'] ?? 'Army System') . ' - ' . ($txt['army_admin'] ?? 'Admin');

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=admin;area=armysystem',
		'name' => $txt['army_admin_title'] ?? 'Army System',
	);

	if ($sa !== 'settings')
	{
		$tab_labels = array(
			'races'   => $txt['army_admin_races'] ?? 'Races',
			'items'   => $txt['army_admin_items'] ?? 'Items',
			'members' => $txt['army_admin_members'] ?? 'Members',
			'logs'    => $txt['army_admin_logs'] ?? 'Logs',
		);

		if (isset($tab_labels[$sa]))
		{
			$context['linktree'][] = array(
				'url' => $scripturl . '?action=admin;area=armysystem;sa=' . $sa,
				'name' => $tab_labels[$sa],
			);
		}
	}

	// Set tab_data for GenericMenu template (prevents "array offset on null" crash)
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['army_admin_title'] ?? 'Army System',
		'description' => $txt['army_admin_desc'] ?? 'Manage Army System settings, races, items, members, and logs.',
	);

	// Dispatch to the sub-action handler
	$subActions[$sa]();
}

/**
 * Manage Army System settings.
 *
 * Displays a form with all configurable settings grouped into categories:
 * General, Economy, Combat, Production, Reset Defaults, Timing, and Vacation.
 *
 * POST handler updates each setting in the army_settings table and
 * invalidates the settings cache.
 *
 * @return void
 */
function ArmyAdminSettings()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	isAllowedTo('admin_forum');

	// Define all known settings with their types and group assignments
	// Types: 'bool' (checkbox), 'int' (integer), 'text' (string)
	$setting_defs = array(
		// General
		'army_enabled' => array('type' => 'bool', 'group' => 'general', 'label' => $txt['army_setting_enabled'] ?? 'Enable Army System'),
		'allow_guest_view' => array('type' => 'bool', 'group' => 'general', 'label' => $txt['army_setting_guest_view'] ?? 'Allow Guest View'),
		'currency_name' => array('type' => 'text', 'group' => 'general', 'label' => $txt['army_setting_currency_name'] ?? 'Currency Name'),

		// Economy
		'tool_resell' => array('type' => 'int', 'group' => 'economy', 'label' => $txt['army_setting_tool_resell'] ?? 'Tool Resell Rate (%)'),
		'armor_resell' => array('type' => 'int', 'group' => 'economy', 'label' => $txt['army_setting_armor_resell'] ?? 'Armor Resell Rate (%)'),
		'money_amount' => array('type' => 'int', 'group' => 'economy', 'label' => $txt['army_setting_money_amount'] ?? 'Gold Per Income Tick'),
		'money_mercanery' => array('type' => 'int', 'group' => 'economy', 'label' => $txt['army_setting_money_mercanery'] ?? 'Mercenary Upkeep Cost'),
		'post_point_reply' => array('type' => 'int', 'group' => 'economy', 'label' => $txt['army_setting_post_point_reply'] ?? 'Gold Per Reply'),
		'post_point_topic' => array('type' => 'int', 'group' => 'economy', 'label' => $txt['army_setting_post_point_topic'] ?? 'Gold Per New Topic'),
		'guy_per_post' => array('type' => 'int', 'group' => 'economy', 'label' => $txt['army_setting_guy_per_post'] ?? 'Soldiers Gained Per Threshold'),
		'post_per_guy' => array('type' => 'int', 'group' => 'economy', 'label' => $txt['army_setting_post_per_guy'] ?? 'Posts Per Soldier Threshold'),

		// Combat
		'max_attack' => array('type' => 'int', 'group' => 'combat', 'label' => $txt['army_setting_max_attack'] ?? 'Max Attacks Per Period'),
		'turns_max' => array('type' => 'int', 'group' => 'combat', 'label' => $txt['army_setting_turns_max'] ?? 'Max Attack Turns'),
		'turn_gain' => array('type' => 'int', 'group' => 'combat', 'label' => $txt['army_setting_turn_gain'] ?? 'Turns Gained Per Tick'),
		'attack_money' => array('type' => 'int', 'group' => 'combat', 'label' => $txt['army_setting_attack_money'] ?? 'Gold Stolen Per Attack'),
		'fort_percent' => array('type' => 'int', 'group' => 'combat', 'label' => $txt['army_setting_fort_percent'] ?? 'Fort Defense Bonus (%)'),
		'siege_percent' => array('type' => 'int', 'group' => 'combat', 'label' => $txt['army_setting_siege_percent'] ?? 'Siege Attack Bonus (%)'),
		'max_spy' => array('type' => 'int', 'group' => 'combat', 'label' => $txt['army_setting_max_spy'] ?? 'Max Spy Missions Per Period'),
		'view_money' => array('type' => 'int', 'group' => 'combat', 'label' => $txt['army_setting_view_money'] ?? 'Gold for Viewing Stats'),

		// Production
		'auto_gain_prod' => array('type' => 'bool', 'group' => 'production', 'label' => $txt['army_setting_auto_gain_prod'] ?? 'Enable Auto Production'),
		'auto_gain_money' => array('type' => 'bool', 'group' => 'production', 'label' => $txt['army_setting_auto_gain_money'] ?? 'Enable Auto Income'),
		'production_base' => array('type' => 'int', 'group' => 'production', 'label' => $txt['army_setting_production_base'] ?? 'Production Base Amount'),
		'production_constant' => array('type' => 'int', 'group' => 'production', 'label' => $txt['army_setting_production_constant'] ?? 'Production Constant'),
		'production_type' => array('type' => 'int', 'group' => 'production', 'label' => $txt['army_setting_production_type'] ?? 'Production Type (0=off, 1=on)'),

		// Reset Defaults
		'reset_army' => array('type' => 'int', 'group' => 'reset', 'label' => $txt['army_setting_reset_army'] ?? 'Starting Army Size'),
		'reset_turn' => array('type' => 'int', 'group' => 'reset', 'label' => $txt['army_setting_reset_turn'] ?? 'Starting Attack Turns'),
		'reset_money' => array('type' => 'int', 'group' => 'reset', 'label' => $txt['army_setting_reset_money'] ?? 'Starting Gold'),

		// Timing
		'log_time' => array('type' => 'int', 'group' => 'timing', 'label' => $txt['army_setting_log_time'] ?? 'Log Retention (days)'),
		'inactive_time' => array('type' => 'int', 'group' => 'timing', 'label' => $txt['army_setting_inactive_time'] ?? 'Inactive Threshold (days, 0=off)'),
		'security_check' => array('type' => 'int', 'group' => 'timing', 'label' => $txt['army_setting_security_check'] ?? 'Security Check Level (0=off)'),

		// Vacation
		'vacation_allowed' => array('type' => 'bool', 'group' => 'vacation', 'label' => $txt['army_setting_vacation_allowed'] ?? 'Allow Vacation Mode'),
		'vacation_min_time' => array('type' => 'int', 'group' => 'vacation', 'label' => $txt['army_setting_vacation_min_time'] ?? 'Min Vacation Days'),
		'vacation_max_time' => array('type' => 'int', 'group' => 'vacation', 'label' => $txt['army_setting_vacation_max_time'] ?? 'Max Vacation Days'),
		'vacation_back' => array('type' => 'bool', 'group' => 'vacation', 'label' => $txt['army_setting_vacation_back'] ?? 'Allow Early Return'),
	);

	// Handle POST: save settings
	if (isset($_POST['save_settings']))
	{
		checkSession();

		$changes = array();

		foreach ($setting_defs as $key => $def)
		{
			if ($def['type'] === 'bool')
				$value = !empty($_POST['army_' . $key]) ? '1' : '0';
			elseif ($def['type'] === 'int')
				$value = isset($_POST['army_' . $key]) ? (string) (int) $_POST['army_' . $key] : '0';
			else
				$value = isset($_POST['army_' . $key]) ? $smcFunc['htmlspecialchars'](trim($_POST['army_' . $key])) : '';

			// Only update if value actually changed
			$old_value = isset($modSettings['army'][$key]) ? $modSettings['army'][$key] : '';

			if ((string) $value !== (string) $old_value)
				$changes[$key] = $value;

			// Update in the database regardless (ensures row exists)
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}army_settings
				SET current_value = {string:val}
				WHERE core = {string:core}
					AND setting = {string:key}',
				array(
					'val' => $value,
					'core' => 'armysystem',
					'key' => $key,
				)
			);
		}

		// Invalidate settings cache
		army_invalidate_cache('army_settings');

		// Log the admin action
		army_log_staff_action('edit_settings', $user_info['id'], 0, 'Updated system settings', implode(', ', array_keys($changes)));

		// Redirect with success
		redirectexit('action=admin;area=armysystem;sa=settings;saved=1');
		return;
	}

	// Load current settings for display
	$settings = $modSettings['army'];

	// Build the config vars grouped for the template
	$context['army_setting_groups'] = array(
		'general' => array(
			'label' => $txt['army_admin_group_general'] ?? 'General Settings',
			'settings' => array(),
		),
		'economy' => array(
			'label' => $txt['army_admin_group_economy'] ?? 'Economy',
			'settings' => array(),
		),
		'combat' => array(
			'label' => $txt['army_admin_group_combat'] ?? 'Combat',
			'settings' => array(),
		),
		'production' => array(
			'label' => $txt['army_admin_group_production'] ?? 'Production & Auto-Gain',
			'settings' => array(),
		),
		'reset' => array(
			'label' => $txt['army_admin_group_reset'] ?? 'Reset Defaults (New Players)',
			'settings' => array(),
		),
		'timing' => array(
			'label' => $txt['army_admin_group_timing'] ?? 'Timing & Maintenance',
			'settings' => array(),
		),
		'vacation' => array(
			'label' => $txt['army_admin_group_vacation'] ?? 'Vacation Mode',
			'settings' => array(),
		),
	);

	foreach ($setting_defs as $key => $def)
	{
		$current_value = isset($settings[$key]) ? $settings[$key] : '';

		$context['army_setting_groups'][$def['group']]['settings'][$key] = array(
			'key' => $key,
			'field_name' => 'army_' . $key,
			'label' => $def['label'],
			'type' => $def['type'],
			'value' => $current_value,
		);
	}

	// Success flag from redirect
	$context['army_admin_saved'] = isset($_REQUEST['saved']) && $_REQUEST['saved'] == 1;

	// Session tokens
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_admin_settings';
	$context['page_title'] = ($txt['army_admin_title'] ?? 'Army System') . ' - ' . ($txt['army_admin_settings'] ?? 'Settings');
}

/**
 * CRUD for races.
 *
 * Displays all existing races with inline edit forms, and provides
 * a form to add new races. Handles add, edit, and delete POST actions.
 *
 * When deleting a race, checks that no members are currently using it.
 * All changes invalidate the army_races cache.
 *
 * @return void
 */
function ArmyAdminRaces()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $settings;

	isAllowedTo('admin_forum');

	// Images URL for icon thumbnails
	$context['army_images_url'] = $settings['default_images_url'] . '/army';

	// Bonus field list — used for both add and edit
	$bonus_fields = array(
		'bonus_income', 'bonus_discount', 'bonus_casualties',
		'bonus_attack', 'bonus_defence', 'bonus_spy', 'bonus_sentry',
	);

	// Icon field list
	$icon_fields = array(
		'default_icon', 'train_atk_icon', 'train_def_icon',
		'merc_icon', 'merc_atk_icon', 'merc_def_icon',
		'spy_icon', 'sentry_icon',
	);

	// Handle POST: add race
	if (isset($_POST['add_race']))
	{
		checkSession();

		$name = isset($_POST['race_name']) ? trim($smcFunc['htmlspecialchars']($_POST['race_name'])) : '';

		if (empty($name))
			fatal_lang_error('army_admin_race_name_required', false);

		// Build the insert columns and values
		$columns = array(
			'name' => 'string',
		);
		$values = array($name);

		foreach ($bonus_fields as $field)
		{
			$columns[$field] = 'int';
			$values[] = isset($_POST[$field]) ? (int) $_POST[$field] : 0;
		}

		foreach ($icon_fields as $field)
		{
			$columns[$field] = 'string';
			$values[] = isset($_POST[$field]) ? trim($smcFunc['htmlspecialchars']($_POST[$field])) : '';
		}

		$smcFunc['db_insert']('insert',
			'{db_prefix}army_races',
			$columns,
			$values,
			array('race_id')
		);

		// Invalidate cache
		army_invalidate_cache('army_races');

		// Log the action
		army_log_staff_action('add_race', $user_info['id'], 0, 'Added race: ' . $name, '');

		redirectexit('action=admin;area=armysystem;sa=races;added=1');
		return;
	}

	// Handle POST: edit race
	if (isset($_POST['edit_race']))
	{
		checkSession();

		$race_id = (int) ($_POST['race_id'] ?? 0);

		if ($race_id <= 0)
			fatal_lang_error('army_admin_race_invalid', false);

		$name = isset($_POST['race_name']) ? trim($smcFunc['htmlspecialchars']($_POST['race_name'])) : '';

		if (empty($name))
			fatal_lang_error('army_admin_race_name_required', false);

		// Build SET clause dynamically
		$set_parts = array('name = {string:name}');
		$params = array(
			'name' => $name,
			'race_id' => $race_id,
		);

		foreach ($bonus_fields as $field)
		{
			$set_parts[] = $field . ' = {int:' . $field . '}';
			$params[$field] = isset($_POST[$field]) ? (int) $_POST[$field] : 0;
		}

		foreach ($icon_fields as $field)
		{
			$set_parts[] = $field . ' = {string:' . $field . '}';
			$params[$field] = isset($_POST[$field]) ? trim($smcFunc['htmlspecialchars']($_POST[$field])) : '';
		}

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_races
			SET ' . implode(', ', $set_parts) . '
			WHERE race_id = {int:race_id}',
			$params
		);

		// Invalidate cache
		army_invalidate_cache('army_races');

		// Log the action
		army_log_staff_action('edit_race', $user_info['id'], 0, 'Edited race #' . $race_id . ': ' . $name, '');

		redirectexit('action=admin;area=armysystem;sa=races;updated=1');
		return;
	}

	// Handle POST: delete race
	if (isset($_POST['delete_race']))
	{
		checkSession();

		$race_id = (int) ($_POST['race_id'] ?? 0);

		if ($race_id <= 0)
			fatal_lang_error('army_admin_race_invalid', false);

		// Check if any members are currently using this race
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}army_members
			WHERE race_id = {int:race_id}',
			array(
				'race_id' => $race_id,
			)
		);

		list($member_count) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if ((int) $member_count > 0)
			fatal_lang_error('army_admin_race_in_use', false);

		// Get the race name for logging before deletion
		$races = army_load_races();
		$race_name = isset($races[$race_id]) ? $races[$race_id]['name'] : 'Unknown';

		// Delete the race
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_races
			WHERE race_id = {int:race_id}',
			array(
				'race_id' => $race_id,
			)
		);

		// Invalidate cache
		army_invalidate_cache('army_races');

		// Log the action
		army_log_staff_action('delete_race', $user_info['id'], 0, 'Deleted race #' . $race_id . ': ' . $race_name, '');

		redirectexit('action=admin;area=armysystem;sa=races;deleted=1');
		return;
	}

	// Display: load all races
	$context['army_races'] = army_load_races();

	// For each race, check how many members are using it
	$context['army_race_member_counts'] = array();

	if (!empty($context['army_races']))
	{
		$race_ids = array_keys($context['army_races']);

		$request = $smcFunc['db_query']('', '
			SELECT race_id, COUNT(*) AS member_count
			FROM {db_prefix}army_members
			WHERE race_id IN ({array_int:race_ids})
			GROUP BY race_id',
			array(
				'race_ids' => $race_ids,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['army_race_member_counts'][(int) $row['race_id']] = (int) $row['member_count'];

		$smcFunc['db_free_result']($request);
	}

	// Bonus field labels for the template
	$context['army_bonus_fields'] = array(
		'bonus_income' => $txt['army_bonus_income'] ?? 'Income',
		'bonus_discount' => $txt['army_bonus_discount'] ?? 'Discount',
		'bonus_casualties' => $txt['army_bonus_casualties'] ?? 'Casualties',
		'bonus_attack' => $txt['army_bonus_attack'] ?? 'Attack',
		'bonus_defence' => $txt['army_bonus_defence'] ?? 'Defence',
		'bonus_spy' => $txt['army_bonus_spy'] ?? 'Spy',
		'bonus_sentry' => $txt['army_bonus_sentry'] ?? 'Sentry',
	);

	// Icon field labels for the template
	$context['army_icon_fields'] = array(
		'default_icon' => $txt['army_icon_default'] ?? 'Default Icon',
		'train_atk_icon' => $txt['army_icon_train_atk'] ?? 'Train Attack Icon',
		'train_def_icon' => $txt['army_icon_train_def'] ?? 'Train Defense Icon',
		'merc_icon' => $txt['army_icon_merc'] ?? 'Mercenary Icon',
		'merc_atk_icon' => $txt['army_icon_merc_atk'] ?? 'Merc Attack Icon',
		'merc_def_icon' => $txt['army_icon_merc_def'] ?? 'Merc Defense Icon',
		'spy_icon' => $txt['army_icon_spy'] ?? 'Spy Icon',
		'sentry_icon' => $txt['army_icon_sentry'] ?? 'Sentry Icon',
	);

	// Success flags
	$context['army_admin_saved'] = isset($_REQUEST['added']) && $_REQUEST['added'] == 1;
	$context['army_admin_updated'] = isset($_REQUEST['updated']) && $_REQUEST['updated'] == 1;
	$context['army_admin_deleted'] = isset($_REQUEST['deleted']) && $_REQUEST['deleted'] == 1;

	// Session tokens
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_admin_races';
	$context['page_title'] = ($txt['army_admin_title'] ?? 'Army System') . ' - ' . ($txt['army_admin_races'] ?? 'Races');
}

/**
 * CRUD for items (weapons, armor, spy tools, sentry tools, fort, siege).
 *
 * Provides a tab-based interface filtered by item type. Each type shows
 * a table of existing items with inline editing, plus an add form.
 *
 * Valid types: 'a' (attack weapons), 'd' (defense armor), 'q' (spy tools),
 * 'e' (sentry tools), 'f' (fort levels), 's' (siege levels).
 *
 * When adding items, the number field is auto-assigned as max(number) + 1
 * for the given type. When deleting items, matching inventory rows are
 * also removed.
 *
 * @return void
 */
function ArmyAdminItems()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $settings;

	isAllowedTo('admin_forum');

	// Images URL for icon thumbnails
	$context['army_images_url'] = $settings['default_images_url'] . '/army';

	// Valid item types and their labels
	$valid_types = array(
		'a' => $txt['army_item_type_a'] ?? 'Attack Weapons',
		'd' => $txt['army_item_type_d'] ?? 'Defense Armor',
		'q' => $txt['army_item_type_q'] ?? 'Spy Tools',
		'e' => $txt['army_item_type_e'] ?? 'Sentry Tools',
		'f' => $txt['army_item_type_f'] ?? 'Fortification',
		's' => $txt['army_item_type_s'] ?? 'Siege Technology',
		'b' => $txt['army_item_type_b'] ?? 'Ships',
	);

	// Determine current type tab
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'a';

	if (!isset($valid_types[$type]))
		$type = 'a';

	// Handle POST: add item
	if (isset($_POST['add_item']))
	{
		checkSession();

		$item_type = isset($_POST['item_type']) ? $_POST['item_type'] : '';

		if (!isset($valid_types[$item_type]))
			fatal_lang_error('army_admin_item_type_invalid', false);

		$name = isset($_POST['item_name']) ? trim($smcFunc['htmlspecialchars']($_POST['item_name'])) : '';

		if (empty($name))
			fatal_lang_error('army_admin_item_name_required', false);

		$value = isset($_POST['item_value']) ? (int) $_POST['item_value'] : 0;
		$price = isset($_POST['item_price']) ? (int) $_POST['item_price'] : 0;
		$letter = isset($_POST['item_letter']) ? trim($smcFunc['htmlspecialchars']($_POST['item_letter'])) : '';
		$icon = isset($_POST['item_icon']) ? trim($smcFunc['htmlspecialchars']($_POST['item_icon'])) : '';
		$repair = isset($_POST['item_repair']) ? (int) $_POST['item_repair'] : 0;

		// Auto-assign number: max existing number for this type + 1
		$request = $smcFunc['db_query']('', '
			SELECT COALESCE(MAX(number), 0) AS max_number
			FROM {db_prefix}army_items
			WHERE type = {string:type}',
			array(
				'type' => $item_type,
			)
		);

		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		$next_number = ((int) $row['max_number']) + 1;

		$smcFunc['db_insert']('insert',
			'{db_prefix}army_items',
			array(
				'type' => 'string',
				'number' => 'int',
				'name' => 'string',
				'value' => 'int',
				'price' => 'int',
				'letter' => 'string',
				'icon' => 'string',
				'repair' => 'int',
			),
			array(
				$item_type,
				$next_number,
				$name,
				$value,
				$price,
				$letter,
				$icon,
				$repair,
			),
			array('id')
		);

		// Invalidate cache for this item type
		army_invalidate_cache('army_items_' . $item_type);

		// Log the action
		army_log_staff_action('add_item', $user_info['id'], 0, 'Added item [' . $item_type . ']: ' . $name, '');

		redirectexit('action=admin;area=armysystem;sa=items;type=' . $item_type . ';added=1');
		return;
	}

	// Handle POST: edit item
	if (isset($_POST['edit_item']))
	{
		checkSession();

		$item_id = (int) ($_POST['item_id'] ?? 0);

		if ($item_id <= 0)
			fatal_lang_error('army_admin_item_invalid', false);

		$name = isset($_POST['item_name']) ? trim($smcFunc['htmlspecialchars']($_POST['item_name'])) : '';

		if (empty($name))
			fatal_lang_error('army_admin_item_name_required', false);

		$value = isset($_POST['item_value']) ? (int) $_POST['item_value'] : 0;
		$price = isset($_POST['item_price']) ? (int) $_POST['item_price'] : 0;
		$letter = isset($_POST['item_letter']) ? trim($smcFunc['htmlspecialchars']($_POST['item_letter'])) : '';
		$icon = isset($_POST['item_icon']) ? trim($smcFunc['htmlspecialchars']($_POST['item_icon'])) : '';
		$repair = isset($_POST['item_repair']) ? (int) $_POST['item_repair'] : 0;

		// Get the item type for cache invalidation
		$request = $smcFunc['db_query']('', '
			SELECT type
			FROM {db_prefix}army_items
			WHERE id = {int:id}
			LIMIT 1',
			array(
				'id' => $item_id,
			)
		);

		$item_row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if ($item_row === null)
			fatal_lang_error('army_admin_item_invalid', false);

		$item_type = $item_row['type'];

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_items
			SET name = {string:name},
				value = {int:value},
				price = {int:price},
				letter = {string:letter},
				icon = {string:icon},
				repair = {int:repair}
			WHERE id = {int:id}',
			array(
				'name' => $name,
				'value' => $value,
				'price' => $price,
				'letter' => $letter,
				'icon' => $icon,
				'repair' => $repair,
				'id' => $item_id,
			)
		);

		// Invalidate cache for this item type
		army_invalidate_cache('army_items_' . $item_type);

		// Log the action
		army_log_staff_action('edit_item', $user_info['id'], 0, 'Edited item #' . $item_id . ': ' . $name, '');

		redirectexit('action=admin;area=armysystem;sa=items;type=' . $item_type . ';updated=1');
		return;
	}

	// Handle POST: delete item
	if (isset($_POST['delete_item']))
	{
		checkSession();

		$item_id = (int) ($_POST['item_id'] ?? 0);

		if ($item_id <= 0)
			fatal_lang_error('army_admin_item_invalid', false);

		// Get the item details for cache invalidation and inventory cleanup
		$request = $smcFunc['db_query']('', '
			SELECT type, number, name
			FROM {db_prefix}army_items
			WHERE id = {int:id}
			LIMIT 1',
			array(
				'id' => $item_id,
			)
		);

		$item_row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if ($item_row === null)
			fatal_lang_error('army_admin_item_invalid', false);

		$item_type = $item_row['type'];
		$item_number = (int) $item_row['number'];
		$item_name = $item_row['name'];

		// Delete the item
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_items
			WHERE id = {int:id}',
			array(
				'id' => $item_id,
			)
		);

		// Also delete matching inventory rows for buyable item types (a, d, q, e)
		if (in_array($item_type, array('a', 'd', 'q', 'e')))
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}army_inventory
				WHERE i_section = {string:section}
					AND i_number = {int:number}',
				array(
					'section' => $item_type,
					'number' => $item_number,
				)
			);
		}

		// Invalidate cache
		army_invalidate_cache('army_items_' . $item_type);

		// Log the action
		army_log_staff_action('delete_item', $user_info['id'], 0, 'Deleted item #' . $item_id . ' [' . $item_type . ']: ' . $item_name, '');

		redirectexit('action=admin;area=armysystem;sa=items;type=' . $item_type . ';deleted=1');
		return;
	}

	// Display: build tab navigation for item types
	$context['army_item_tabs'] = array();

	foreach ($valid_types as $type_code => $type_label)
	{
		$context['army_item_tabs'][$type_code] = array(
			'label' => $type_label,
			'url' => $scripturl . '?action=admin;area=armysystem;sa=items;type=' . $type_code,
			'active' => ($type === $type_code),
		);
	}

	// Load items for the current type
	$context['army_items'] = army_load_items($type);
	$context['army_item_type'] = $type;
	$context['army_item_type_label'] = $valid_types[$type];

	// Success flags
	$context['army_admin_saved'] = isset($_REQUEST['added']) && $_REQUEST['added'] == 1;
	$context['army_admin_updated'] = isset($_REQUEST['updated']) && $_REQUEST['updated'] == 1;
	$context['army_admin_deleted'] = isset($_REQUEST['deleted']) && $_REQUEST['deleted'] == 1;

	// Session tokens
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_admin_items';
	$context['page_title'] = ($txt['army_admin_title'] ?? 'Army System') . ' - ' . ($txt['army_admin_items'] ?? 'Items') . ': ' . $valid_types[$type];
}

/**
 * Search and edit member army data.
 *
 * Provides a search form (by member name with LIKE, or direct member ID),
 * and when a member is found, displays a full edit form for all army
 * fields. Also supports resetting or fully deleting a member's army data.
 *
 * POST actions:
 *   edit_member   - Update the target member's army fields
 *   reset_member  - Reset member to defaults (like ArmyReset), clear inventory
 *   delete_member - Remove all army data for the member
 *
 * All admin actions are logged to army_staff_logs.
 *
 * @return void
 */
function ArmyAdminMembers()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	isAllowedTo('admin_forum');

	$settings = $modSettings['army'];

	// Handle POST: edit member
	if (isset($_POST['edit_member']))
	{
		checkSession();

		$id = (int) ($_POST['member_id'] ?? 0);

		if ($id <= 0)
			fatal_lang_error('army_admin_member_invalid', false);

		// Load current data for comparison (for log notes)
		$old_data = army_load_member($id);

		if ($old_data === false)
			fatal_lang_error('army_admin_member_not_found', false);

		// Gather all editable fields from POST
		$fields = array(
			'race_id' => (int) ($_POST['race_id'] ?? $old_data['race_id']),
			'army_points' => (int) ($_POST['army_points'] ?? $old_data['army_points']),
			'army_size' => (int) ($_POST['army_size'] ?? $old_data['army_size']),
			'soldiers_attack' => (int) ($_POST['soldiers_attack'] ?? $old_data['soldiers_attack']),
			'soldiers_defense' => (int) ($_POST['soldiers_defense'] ?? $old_data['soldiers_defense']),
			'soldiers_spy' => (int) ($_POST['soldiers_spy'] ?? $old_data['soldiers_spy']),
			'soldiers_sentry' => (int) ($_POST['soldiers_sentry'] ?? $old_data['soldiers_sentry']),
			'soldiers_untrained' => (int) ($_POST['soldiers_untrained'] ?? $old_data['soldiers_untrained']),
			'mercs_attack' => (int) ($_POST['mercs_attack'] ?? $old_data['mercs_attack']),
			'mercs_defense' => (int) ($_POST['mercs_defense'] ?? $old_data['mercs_defense']),
			'mercs_untrained' => (int) ($_POST['mercs_untrained'] ?? $old_data['mercs_untrained']),
			'fort_level' => (int) ($_POST['fort_level'] ?? $old_data['fort_level']),
			'siege_level' => (int) ($_POST['siege_level'] ?? $old_data['siege_level']),
			'unit_prod_level' => (int) ($_POST['unit_prod_level'] ?? $old_data['unit_prod_level']),
			'spy_skill_level' => (int) ($_POST['spy_skill_level'] ?? $old_data['spy_skill_level']),
			'attack_turns' => (int) ($_POST['attack_turns'] ?? $old_data['attack_turns']),
			'is_active' => !empty($_POST['is_active']) ? 1 : 0,
		);

		// Build change notes for the log
		$changes = array();

		foreach ($fields as $field => $new_value)
		{
			$old_value = isset($old_data[$field]) ? (int) $old_data[$field] : 0;

			if ($new_value !== $old_value)
				$changes[] = $field . ': ' . $old_value . ' -> ' . $new_value;
		}

		// Update the member record
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET race_id = {int:race_id},
				army_points = {int:army_points},
				army_size = {int:army_size},
				soldiers_attack = {int:soldiers_attack},
				soldiers_defense = {int:soldiers_defense},
				soldiers_spy = {int:soldiers_spy},
				soldiers_sentry = {int:soldiers_sentry},
				soldiers_untrained = {int:soldiers_untrained},
				mercs_attack = {int:mercs_attack},
				mercs_defense = {int:mercs_defense},
				mercs_untrained = {int:mercs_untrained},
				fort_level = {int:fort_level},
				siege_level = {int:siege_level},
				unit_prod_level = {int:unit_prod_level},
				spy_skill_level = {int:spy_skill_level},
				attack_turns = {int:attack_turns},
				is_active = {int:is_active}
			WHERE id_member = {int:id_member}',
			array_merge($fields, array('id_member' => $id))
		);

		// Log the action
		$note = !empty($changes) ? implode('; ', $changes) : 'No changes detected';
		army_log_staff_action('edit_member', $user_info['id'], $id, $note, '');

		redirectexit('action=admin;area=armysystem;sa=members;u=' . $id . ';updated=1');
		return;
	}

	// Handle POST: reset member
	if (isset($_POST['reset_member']))
	{
		checkSession();

		$id = (int) ($_POST['member_id'] ?? 0);

		if ($id <= 0)
			fatal_lang_error('army_admin_member_invalid', false);

		// Verify the member exists
		$old_data = army_load_member($id);

		if ($old_data === false)
			fatal_lang_error('army_admin_member_not_found', false);

		// Delete all inventory for this member
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_inventory
			WHERE i_member = {int:id_member}',
			array(
				'id_member' => $id,
			)
		);

		// Remove from clan membership
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_clan_members
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => $id,
			)
		);

		// Remove any pending clan invitations/applications
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_clan_pending
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => $id,
			)
		);

		// Reset the army_members row to defaults, preserve lifetime stats
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET race_id = {int:zero},
				army_points = {int:zero},
				army_size = {int:zero},
				soldiers_attack = {int:zero},
				soldiers_defense = {int:zero},
				soldiers_spy = {int:zero},
				soldiers_sentry = {int:zero},
				soldiers_untrained = {int:zero},
				mercs_attack = {int:zero},
				mercs_defense = {int:zero},
				mercs_untrained = {int:zero},
				fort_level = {int:one},
				siege_level = {int:one},
				unit_prod_level = {int:zero},
				spy_skill_level = {int:zero},
				attack_turns = {int:zero},
				rank_level = {int:zero},
				vacation_start = {int:zero},
				vacation_end = {int:zero},
				is_active = {int:one},
				last_active = {int:now}
			WHERE id_member = {int:id_member}',
			array(
				'zero' => 0,
				'one' => 1,
				'now' => time(),
				'id_member' => $id,
			)
		);

		// Log the action
		army_log_staff_action('reset_member', $user_info['id'], $id, 'Admin reset member to defaults', '');

		redirectexit('action=admin;area=armysystem;sa=members;u=' . $id . ';reset=1');
		return;
	}

	// Handle POST: delete member
	if (isset($_POST['delete_member']))
	{
		checkSession();

		$id = (int) ($_POST['member_id'] ?? 0);

		if ($id <= 0)
			fatal_lang_error('army_admin_member_invalid', false);

		// Verify the member exists
		$old_data = army_load_member($id);

		if ($old_data === false)
			fatal_lang_error('army_admin_member_not_found', false);

		// Delete inventory
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_inventory
			WHERE i_member = {int:id_member}',
			array(
				'id_member' => $id,
			)
		);

		// Delete clan membership
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_clan_members
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => $id,
			)
		);

		// Delete pending clan entries
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_clan_pending
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => $id,
			)
		);

		// Delete the army_members row
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_members
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => $id,
			)
		);

		// Log the action
		army_log_staff_action('delete_member', $user_info['id'], $id, 'Deleted all army data for member', '');

		redirectexit('action=admin;area=armysystem;sa=members;deleted=1');
		return;
	}

	// Display: handle search or direct member load
	$context['army_admin_member'] = null;
	$context['army_admin_search_results'] = array();
	$context['army_admin_search_query'] = '';

	// Direct member ID load
	if (isset($_REQUEST['u']))
	{
		$id = (int) $_REQUEST['u'];

		if ($id > 0)
		{
			$member = army_load_member($id);

			if ($member !== false)
			{
				// Load SMF member name
				$request = $smcFunc['db_query']('', '
					SELECT real_name
					FROM {db_prefix}members
					WHERE id_member = {int:id_member}
					LIMIT 1',
					array(
						'id_member' => $id,
					)
				);

				$name_row = $smcFunc['db_fetch_assoc']($request);
				$smcFunc['db_free_result']($request);

				$member['real_name'] = ($name_row !== null) ? $name_row['real_name'] : ($txt['army_unknown_player'] ?? 'Unknown');

				// Load inventory summary
				$inventory = army_load_inventory($id);

				// Calculate power ratings
				$attack_power = army_calculate_attack_power($member, $inventory);
				$defense_power = army_calculate_defense_power($member, $inventory);
				$spy_power = army_calculate_spy_power($member, $inventory);
				$sentry_power = army_calculate_sentry_power($member, $inventory);

				$context['army_admin_member'] = array(
					'id_member' => (int) $member['id_member'],
					'real_name' => $member['real_name'],
					'race_id' => (int) $member['race_id'],
					'race_name' => $member['race_name'] ?? ($txt['army_no_race'] ?? 'None'),
					'army_points' => (int) $member['army_points'],
					'army_size' => (int) $member['army_size'],
					'soldiers_attack' => (int) $member['soldiers_attack'],
					'soldiers_defense' => (int) $member['soldiers_defense'],
					'soldiers_spy' => (int) $member['soldiers_spy'],
					'soldiers_sentry' => (int) $member['soldiers_sentry'],
					'soldiers_untrained' => (int) $member['soldiers_untrained'],
					'mercs_attack' => (int) $member['mercs_attack'],
					'mercs_defense' => (int) $member['mercs_defense'],
					'mercs_untrained' => (int) $member['mercs_untrained'],
					'fort_level' => (int) $member['fort_level'],
					'siege_level' => (int) $member['siege_level'],
					'unit_prod_level' => (int) $member['unit_prod_level'],
					'spy_skill_level' => (int) $member['spy_skill_level'],
					'attack_turns' => (int) $member['attack_turns'],
					'total_attacks' => (int) $member['total_attacks'],
					'total_defends' => (int) $member['total_defends'],
					'rank_level' => (int) $member['rank_level'],
					'last_active' => (int) $member['last_active'],
					'last_active_formatted' => !empty($member['last_active']) ? timeformat((int) $member['last_active']) : ($txt['army_never'] ?? 'Never'),
					'vacation_start' => (int) $member['vacation_start'],
					'vacation_end' => (int) $member['vacation_end'],
					'is_active' => (int) $member['is_active'],
					'attack_power' => army_format_number((int) $attack_power),
					'defense_power' => army_format_number((int) $defense_power),
					'spy_power' => army_format_number((int) $spy_power),
					'sentry_power' => army_format_number((int) $sentry_power),
					'inventory_count' => count($inventory),
				);
			}
		}
	}

	// Search by member name
	if (isset($_REQUEST['search']) && !empty(trim($_REQUEST['search'])))
	{
		$search = trim($_REQUEST['search']);
		$context['army_admin_search_query'] = $smcFunc['htmlspecialchars']($search);

		// Search SMF members table joined with army_members
		$request = $smcFunc['db_query']('', '
			SELECT am.id_member, am.race_id, am.army_size, am.is_active,
				ar.name AS race_name,
				mem.real_name
			FROM {db_prefix}army_members AS am
				LEFT JOIN {db_prefix}army_races AS ar ON (ar.race_id = am.race_id)
				INNER JOIN {db_prefix}members AS mem ON (mem.id_member = am.id_member)
			WHERE LOWER(mem.real_name) LIKE {string:search}
			ORDER BY mem.real_name ASC
			LIMIT 25',
			array(
				'search' => '%' . strtolower($smcFunc['db_escape_wildcard_string']($search)) . '%',
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['army_admin_search_results'][] = array(
				'id_member' => (int) $row['id_member'],
				'real_name' => $row['real_name'],
				'race_name' => $row['race_name'] ?? ($txt['army_no_race'] ?? 'None'),
				'army_size' => army_format_number($row['army_size']),
				'is_active' => (int) $row['is_active'],
				'edit_url' => $scripturl . '?action=admin;area=armysystem;sa=members;u=' . (int) $row['id_member'],
			);
		}

		$smcFunc['db_free_result']($request);
	}

	// Load available races for the edit form dropdown
	$context['army_races'] = army_load_races();

	// Success/action flags
	$context['army_admin_updated'] = isset($_REQUEST['updated']) && $_REQUEST['updated'] == 1;
	$context['army_admin_reset'] = isset($_REQUEST['reset']) && $_REQUEST['reset'] == 1;
	$context['army_admin_deleted'] = isset($_REQUEST['deleted']) && $_REQUEST['deleted'] == 1;

	// Session tokens
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_admin_members';
	$context['page_title'] = ($txt['army_admin_title'] ?? 'Army System') . ' - ' . ($txt['army_admin_members'] ?? 'Members');
}

/**
 * View staff logs and optionally attack logs.
 *
 * Provides a paginated view of admin action logs (staff logs) or
 * attack battle logs, with member name JOINs for readable display.
 *
 * Log types:
 *   staff  - Admin actions (default): edits, resets, deletions, etc.
 *   attack - Battle logs: attacks between members
 *
 * @return void
 */
function ArmyAdminLogs()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	isAllowedTo('admin_forum');

	// Determine log type
	$log_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'staff';

	if (!in_array($log_type, array('staff', 'attack')))
		$log_type = 'staff';

	// Handle POST: clear logs
	if (isset($_POST['clear_logs']))
	{
		checkSession();

		if ($log_type === 'staff')
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}army_staff_logs',
				array()
			);

			army_log_staff_action('clear_staff_logs', $user_info['id'], 0, 'Cleared all staff logs', '');
		}
		elseif ($log_type === 'attack')
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}army_attack_logs',
				array()
			);

			// Also clear attack log inventory snapshots
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}army_attack_logs_inv',
				array()
			);

			army_log_staff_action('clear_attack_logs', $user_info['id'], 0, 'Cleared all attack logs', '');
		}

		redirectexit('action=admin;area=armysystem;sa=logs;type=' . $log_type . ';cleared=1');
		return;
	}

	// Build log type tab navigation
	$context['army_log_tabs'] = array(
		'staff' => array(
			'label' => $txt['army_admin_logs_staff'] ?? 'Staff Logs',
			'url' => $scripturl . '?action=admin;area=armysystem;sa=logs;type=staff',
			'active' => ($log_type === 'staff'),
		),
		'attack' => array(
			'label' => $txt['army_admin_logs_attack'] ?? 'Attack Logs',
			'url' => $scripturl . '?action=admin;area=armysystem;sa=logs;type=attack',
			'active' => ($log_type === 'attack'),
		),
	);

	$context['army_log_type'] = $log_type;
	$context['army_logs'] = array();

	// Pagination
	$per_page = 30;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	if ($start < 0)
		$start = 0;

	if ($log_type === 'staff')
	{
		// Count total staff logs
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}army_staff_logs',
			array()
		);

		list($total_logs) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$total_logs = (int) $total_logs;

		$context['page_index'] = constructPageIndex(
			$scripturl . '?action=admin;area=armysystem;sa=logs;type=staff',
			$start,
			$total_logs,
			$per_page
		);
		$context['start'] = $start;

		// Query staff logs with member name JOINs
		$request = $smcFunc['db_query']('', '
			SELECT sl.sl_id, sl.action, sl.id_member, sl.target_member,
				sl.log_time, sl.note, sl.reason,
				m1.real_name AS member_name,
				m2.real_name AS target_name
			FROM {db_prefix}army_staff_logs AS sl
				LEFT JOIN {db_prefix}members AS m1 ON (m1.id_member = sl.id_member)
				LEFT JOIN {db_prefix}members AS m2 ON (m2.id_member = sl.target_member)
			ORDER BY sl.log_time DESC
			LIMIT {int:start}, {int:per_page}',
			array(
				'start' => $start,
				'per_page' => $per_page,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['army_logs'][] = array(
				'id' => (int) $row['sl_id'],
				'action' => $row['action'],
				'member_id' => (int) $row['id_member'],
				'member_name' => $row['member_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
				'target_id' => (int) $row['target_member'],
				'target_name' => $row['target_name'] ?? '',
				'time' => timeformat((int) $row['log_time']),
				'time_raw' => (int) $row['log_time'],
				'note' => $row['note'],
				'reason' => $row['reason'],
			);
		}

		$smcFunc['db_free_result']($request);
	}
	else
	{
		// Count total attack logs
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}army_attack_logs',
			array()
		);

		list($total_logs) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);
		$total_logs = (int) $total_logs;

		$context['page_index'] = constructPageIndex(
			$scripturl . '?action=admin;area=armysystem;sa=logs;type=attack',
			$start,
			$total_logs,
			$per_page
		);
		$context['start'] = $start;

		// Query attack logs with member name JOINs
		$request = $smcFunc['db_query']('', '
			SELECT al.id, al.attack_time, al.money_stolen, al.turns_used,
				al.attacker, al.defender,
				al.atk_damage, al.def_damage,
				al.atk_kill, al.def_kill,
				m1.real_name AS attacker_name,
				m2.real_name AS defender_name
			FROM {db_prefix}army_attack_logs AS al
				LEFT JOIN {db_prefix}members AS m1 ON (m1.id_member = al.attacker)
				LEFT JOIN {db_prefix}members AS m2 ON (m2.id_member = al.defender)
			ORDER BY al.attack_time DESC
			LIMIT {int:start}, {int:per_page}',
			array(
				'start' => $start,
				'per_page' => $per_page,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['army_logs'][] = array(
				'id' => (int) $row['id'],
				'attacker_id' => (int) $row['attacker'],
				'attacker_name' => $row['attacker_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
				'defender_id' => (int) $row['defender'],
				'defender_name' => $row['defender_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
				'time' => timeformat((int) $row['attack_time']),
				'time_raw' => (int) $row['attack_time'],
				'money_stolen' => army_format_number($row['money_stolen']),
				'turns_used' => (int) $row['turns_used'],
				'atk_damage' => army_format_number($row['atk_damage']),
				'def_damage' => army_format_number($row['def_damage']),
				'atk_kill' => army_format_number($row['atk_kill']),
				'def_kill' => army_format_number($row['def_kill']),
			);
		}

		$smcFunc['db_free_result']($request);
	}

	$context['army_total_logs'] = $total_logs;

	// Success flag
	$context['army_admin_cleared'] = isset($_REQUEST['cleared']) && $_REQUEST['cleared'] == 1;

	// Session tokens
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_admin_logs';
	$context['page_title'] = ($txt['army_admin_title'] ?? 'Army System') . ' - ' . ($txt['army_admin_logs'] ?? 'Logs');
}

/**
 * Helper: Log an admin/staff action to the army_staff_logs table.
 *
 * Records what action was taken, who performed it, who was targeted,
 * and an optional note and reason. Used throughout the admin panel
 * to maintain an audit trail of all administrative changes.
 *
 * @param string $action       Short action identifier (e.g., 'edit_member', 'delete_race')
 * @param int    $id_member    The admin member ID who performed the action
 * @param int    $target_member The target member ID (0 if not member-specific)
 * @param string $note         Descriptive note about what was changed
 * @param string $reason       Optional reason provided by the admin
 * @return void
 */
function army_log_staff_action($action, $id_member, $target_member, $note, $reason)
{
	global $smcFunc;

	$smcFunc['db_insert']('insert',
		'{db_prefix}army_staff_logs',
		array(
			'action' => 'string',
			'id_member' => 'int',
			'target_member' => 'int',
			'log_time' => 'int',
			'note' => 'string',
			'reason' => 'string',
		),
		array(
			$action,
			(int) $id_member,
			(int) $target_member,
			time(),
			$note,
			$reason,
		),
		array('sl_id')
	);
}
