<?php
/**
 * Army System - Main Controller
 *
 * Hook callbacks, dispatcher, dashboard, members list, profile view,
 * vacation mode. This is the primary entry point for the Army System mod.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Hook: integrate_actions
 *
 * Registers the 'army' action so that ?action=army is routed to this file.
 *
 * @param array &$actionArray SMF's master action routing array
 * @return void
 */
function army_integrate_actions(&$actionArray)
{
	$actionArray['army'] = array('ArmySystem.php', 'ArmySystemMain');
}

/**
 * Hook: integrate_admin_areas
 *
 * Adds the "Army System" section to the SMF admin panel under the
 * 'config' area, with subsections for settings, races, items, members,
 * and logs.
 *
 * @param array &$admin_areas SMF's admin area configuration array
 * @return void
 */
function army_integrate_admin_areas(&$admin_areas)
{
	global $txt, $scripturl;

	loadLanguage('ArmySystem');

	// Insert Army System as its own top-level admin section before 'layout',
	// matching the pattern used by RSS Feed Poster and SMF Trader.
	$insert = array(
		'armysystem' => array(
			'title' => $txt['army_admin_title'] ?? 'Army System',
			'permission' => array('admin_forum'),
			'areas' => array(
				'armysystem' => array(
					'label' => $txt['army_admin_title'] ?? 'Army System',
					'file' => 'ArmySystem-Admin.php',
					'function' => 'ArmyAdmin',
					'custom_url' => $scripturl . '?action=admin;area=armysystem',
					'icon' => 'army',
					'subsections' => array(
						'settings' => array($txt['army_admin_settings'] ?? 'Settings'),
						'races' => array($txt['army_admin_races'] ?? 'Races'),
						'items' => array($txt['army_admin_items'] ?? 'Items'),
						'members' => array($txt['army_admin_members'] ?? 'Members'),
						'logs' => array($txt['army_admin_logs'] ?? 'Logs'),
					),
				),
			),
		),
	);

	// Insert before 'layout' section, or append if not found
	$position = array_search('layout', array_keys($admin_areas));

	if ($position === false)
		$admin_areas = array_merge($admin_areas, $insert);
	elseif ($position === 0)
		$admin_areas = array_merge($insert, $admin_areas);
	else
		$admin_areas = array_merge(
			array_slice($admin_areas, 0, $position, true),
			$insert,
			array_slice($admin_areas, $position, null, true)
		);
}

/**
 * Hook: integrate_menu_buttons
 *
 * Adds an "Army" button to the main forum navigation menu, placed
 * immediately after the 'home' button.
 *
 * @param array &$buttons SMF's menu button configuration array
 * @return void
 */
function army_integrate_menu_buttons(&$buttons)
{
	global $txt, $scripturl, $settings;

	loadLanguage('ArmySystem');

	$buttons = array_merge(
		array_slice($buttons, 0, 1, true),
		array('army' => array(
			'title' => $txt['army_menu'] ?? 'Army',
			'href' => $scripturl . '?action=army',
			'show' => allowedTo('army_view'),
			'icon' => 'army',
		)),
		array_slice($buttons, 1, null, true)
	);
}

/**
 * Hook: integrate_after_create_post
 *
 * Awards gold and soldiers to active players when they create posts.
 *
 * - Replies earn post_point_reply gold
 * - New topics earn post_point_topic gold
 * - Every post_per_guy posts, the player gains guy_per_post untrained soldiers
 *
 * @param array $msgOptions    Message options from createPost()
 * @param array $topicOptions  Topic options from createPost()
 * @param array $posterOptions Poster options from createPost()
 * @param array $message_columns  Column definitions (unused)
 * @param array $message_parameters Parameter values (unused)
 * @return void
 */
function army_integrate_post_gain($msgOptions, $topicOptions, $posterOptions, $message_columns, $message_parameters)
{
	global $smcFunc, $modSettings;

	// Only process for logged-in members
	$id_member = (int) ($posterOptions['id'] ?? 0);

	if ($id_member <= 0)
		return;

	// Load Army System settings
	require_once(__DIR__ . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	$settings = $modSettings['army'];

	// Check if the system is enabled
	if (empty($settings['army_enabled']))
		return;

	// Check if the player has chosen a race (i.e., is participating)
	$member = army_load_member($id_member);

	if ($member === false || empty($member['race_id']))
		return;

	// Skip if player is inactive or on vacation
	if (!army_is_active($member))
		return;

	// Determine gold gain based on whether this is a new topic or a reply
	$is_new_topic = empty($topicOptions['id']) || !empty($topicOptions['is_new']);
	$gold_gain = $is_new_topic
		? (int) ($settings['post_point_topic'] ?? 0)
		: (int) ($settings['post_point_reply'] ?? 0);

	// Apply race income bonus to gold gain
	if ($gold_gain > 0 && !empty($member['bonus_income']))
	{
		$gold_gain = (int) army_apply_race_bonus((float) $gold_gain, (int) $member['bonus_income']);
	}

	// Calculate soldier gain from posts
	$guy_per_post = (int) ($settings['guy_per_post'] ?? 0);
	$post_per_guy = (int) ($settings['post_per_guy'] ?? 0);
	$soldier_gain = 0;

	if ($guy_per_post > 0 && $post_per_guy > 0)
	{
		// Count this member's total posts in the forum to determine if they hit the threshold.
		// We use a simple modulo approach: every post_per_guy posts, they gain soldiers.
		// Load current post count from SMF members table.
		$request = $smcFunc['db_query']('', '
			SELECT posts
			FROM {db_prefix}members
			WHERE id_member = {int:id_member}
			LIMIT 1',
			array(
				'id_member' => $id_member,
			)
		);

		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if ($row !== null)
		{
			// The post count includes the current post (SMF increments before hooks fire)
			$post_count = (int) $row['posts'];

			if ($post_count > 0 && ($post_count % $post_per_guy) === 0)
				$soldier_gain = $guy_per_post;
		}
	}

	// Apply updates if there is anything to give
	if ($gold_gain <= 0 && $soldier_gain <= 0)
		return;

	// Build the SET clause dynamically
	$set_parts = array();
	$params = array('id_member' => $id_member);

	if ($gold_gain > 0)
	{
		$set_parts[] = 'army_points = army_points + {int:gold_gain}';
		$params['gold_gain'] = $gold_gain;
	}

	if ($soldier_gain > 0)
	{
		$set_parts[] = 'soldiers_untrained = soldiers_untrained + {int:soldier_gain}';
		$set_parts[] = 'army_size = army_size + {int:soldier_gain}';
		$params['soldier_gain'] = $soldier_gain;
	}

	// Update last_active timestamp as well
	$set_parts[] = 'last_active = {int:now}';
	$params['now'] = time();

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}army_members
		SET ' . implode(', ', $set_parts) . '
		WHERE id_member = {int:id_member}',
		$params
	);
}

/**
 * Hook: integrate_load_permissions
 *
 * Registers Army System permissions with SMF's permission system:
 * - army_view:   Can view the Army System pages
 * - army_play:   Can participate (pick race, buy, train, etc.)
 * - army_attack: Can attack other players
 * - army_spy:    Can perform spy missions
 * - army_admin:  Can manage the Army System via the admin panel
 *
 * @param array &$permissionGroups Permission group definitions
 * @param array &$permissionList   Permission list definitions
 * @return void
 */
function army_integrate_permissions(&$permissionGroups, &$permissionList)
{
	$permissionGroups['membergroup'][] = 'army';

	$permissionList['membergroup']['army_view'] = array(false, 'army', 'army');
	$permissionList['membergroup']['army_play'] = array(false, 'army', 'army');
	$permissionList['membergroup']['army_attack'] = array(false, 'army', 'army');
	$permissionList['membergroup']['army_spy'] = array(false, 'army', 'army');
	$permissionList['membergroup']['army_admin'] = array(false, 'army', 'army');
}

/**
 * Hook: integrate_pre_profile_areas
 *
 * Adds an "Army Stats" tab to user profile pages, allowing anyone
 * with army_view permission to see a member's army information.
 *
 * @param array &$profile_areas SMF's profile area configuration array
 * @return void
 */
function army_integrate_profile(&$profile_areas)
{
	global $txt;

	loadLanguage('ArmySystem');

	$profile_areas['info']['areas']['army_stats'] = array(
		'label' => $txt['army_profile_stats'] ?? 'Army Stats',
		'file' => 'ArmySystem.php',
		'function' => 'ArmyProfileStats',
		'permission' => array(
			'own' => 'army_view',
			'any' => 'army_view',
		),
	);
}

/**
 * Hook: integrate_load_theme
 *
 * Loads the Army System CSS file on every page load so that styles
 * are available when army elements appear (menu buttons, profile tabs, etc.).
 *
 * @return void
 */
function army_integrate_theme()
{
	loadCSSFile('army_system.css', array('minimize' => true));
}

/**
 * Main entry point and dispatcher for the Army System.
 *
 * Loads language files, templates, and settings, then routes the request
 * to the appropriate sub-action handler based on the 'sa' parameter.
 * Also builds the navigation context used by all Army System templates.
 *
 * @return void
 */
function ArmySystemMain()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir, $settings;

	// Load language and template
	loadLanguage('ArmySystem');
	loadTemplate('ArmySystem');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');
	army_load_settings();

	// Check if the Army System is enabled
	if (empty($modSettings['army']['army_enabled']))
		fatal_lang_error('army_not_enabled', false);

	// Sub-action routing map
	// String values are functions in this file.
	// Array values are array(filename, function) to require_once and call.
	$subActions = array(
		''          => 'ArmyDashboard',
		'members'   => 'ArmyMembers',
		'profile'   => 'ArmyProfile',
		'race'      => array('ArmySystem-Race.php', 'ArmyRace'),
		'armor'     => array('ArmySystem-Armor.php', 'ArmyArmor'),
		'training'  => array('ArmySystem-Training.php', 'ArmyTraining'),
		'mercs'     => array('ArmySystem-Mercenaries.php', 'ArmyMercenaries'),
		'attack'    => array('ArmySystem-Attack.php', 'ArmyAttack'),
		'attacklog' => array('ArmySystem-Attack.php', 'ArmyAttackLog'),
		'spy'       => array('ArmySystem-Spy.php', 'ArmySpy'),
		'spylog'    => array('ArmySystem-Spy.php', 'ArmySpyLog'),
		'clans'     => array('ArmySystem-Clans.php', 'ArmyClans'),
		'clan'      => array('ArmySystem-Clans.php', 'ArmyClanView'),
		'transfer'  => array('ArmySystem-Transfer.php', 'ArmyTransfer'),
		'events'    => array('ArmySystem-Events.php', 'ArmyEvents'),
		'reset'     => array('ArmySystem-Race.php', 'ArmyReset'),
		'vacation'  => 'ArmyVacation',
		'admin'     => array('ArmySystem-Admin.php', 'ArmyAdmin'),
	);

	// Determine which sub-action to run
	$sa = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : '';

	if (!isset($subActions[$sa]))
		$sa = '';

	// Build the navigation links for the sidebar/menu
	$army_name = $modSettings['army']['name'] ?? ($txt['army_system'] ?? 'Army System');
	$currency_name = $modSettings['army']['currency_name'] ?? ($txt['army_currency'] ?? 'Gold');

	$context['army_name'] = $army_name;
	$context['army_currency'] = $currency_name;
	$context['army_images_url'] = $settings['default_images_url'] . '/army';

	$context['army_nav'] = array(
		'' => array(
			'label' => $txt['army_nav_dashboard'] ?? 'Dashboard',
			'url' => $scripturl . '?action=army',
			'active' => ($sa === ''),
		),
		'members' => array(
			'label' => $txt['army_nav_members'] ?? 'Rankings',
			'url' => $scripturl . '?action=army;sa=members',
			'active' => ($sa === 'members'),
		),
		'race' => array(
			'label' => $txt['army_nav_race'] ?? 'Race',
			'url' => $scripturl . '?action=army;sa=race',
			'active' => ($sa === 'race'),
			'guest' => false,
		),
		'armor' => array(
			'label' => $txt['army_nav_armor'] ?? 'Armory',
			'url' => $scripturl . '?action=army;sa=armor',
			'active' => ($sa === 'armor'),
			'guest' => false,
		),
		'training' => array(
			'label' => $txt['army_nav_training'] ?? 'Training',
			'url' => $scripturl . '?action=army;sa=training',
			'active' => ($sa === 'training'),
			'guest' => false,
		),
		'mercs' => array(
			'label' => $txt['army_nav_mercs'] ?? 'Mercenaries',
			'url' => $scripturl . '?action=army;sa=mercs',
			'active' => ($sa === 'mercs'),
			'guest' => false,
		),
		'attack' => array(
			'label' => $txt['army_nav_attack'] ?? 'Attack',
			'url' => $scripturl . '?action=army;sa=attack',
			'active' => ($sa === 'attack' || $sa === 'attacklog'),
			'guest' => false,
		),
		'spy' => array(
			'label' => $txt['army_nav_spy'] ?? 'Espionage',
			'url' => $scripturl . '?action=army;sa=spy',
			'active' => ($sa === 'spy' || $sa === 'spylog'),
			'guest' => false,
		),
		'clans' => array(
			'label' => $txt['army_nav_clans'] ?? 'Clans',
			'url' => $scripturl . '?action=army;sa=clans',
			'active' => ($sa === 'clans' || $sa === 'clan'),
		),
		'transfer' => array(
			'label' => $txt['army_nav_transfer'] ?? 'Transfer',
			'url' => $scripturl . '?action=army;sa=transfer',
			'active' => ($sa === 'transfer'),
			'guest' => false,
		),
		'events' => array(
			'label' => $txt['army_nav_events'] ?? 'Events',
			'url' => $scripturl . '?action=army;sa=events',
			'active' => ($sa === 'events'),
		),
	);

	// Set page title and linktree
	$context['page_title'] = $army_name;

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=army',
		'name' => $army_name,
	);

	// If we are on a sub-action, add it to the linktree
	if ($sa !== '' && isset($context['army_nav'][$sa]))
	{
		$context['linktree'][] = array(
			'url' => $context['army_nav'][$sa]['url'],
			'name' => $context['army_nav'][$sa]['label'],
		);
	}

	// Dispatch to the appropriate handler
	$action = $subActions[$sa];

	if (is_array($action))
	{
		require_once($sourcedir . '/' . $action[0]);
		$action[1]();
	}
	else
	{
		$action();
	}
}

/**
 * Dashboard view - the main Army System landing page.
 *
 * Shows the current player's army stats, calculated power ratings,
 * fort/siege information, and recent events. Guests see a limited
 * public view if allow_guest_view is enabled.
 *
 * Players who have not yet chosen a race are redirected to the race picker.
 *
 * @return void
 */
function ArmyDashboard()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	isAllowedTo('army_view');

	$settings = $modSettings['army'];

	// Guest handling
	if ($user_info['is_guest'])
	{
		if (empty($settings['allow_guest_view']))
			fatal_lang_error('army_no_guest_access', false);

		// Guests see a limited public view with top players
		$context['army_guest_view'] = true;

		// Load the top 10 players for the guest landing page
		$context['army_top_players'] = array();

		$request = $smcFunc['db_query']('', '
			SELECT am.id_member, am.army_size, am.rank_level,
				ar.name AS race_name, ar.default_icon,
				mem.real_name
			FROM {db_prefix}army_members AS am
				LEFT JOIN {db_prefix}army_races AS ar ON (ar.race_id = am.race_id)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = am.id_member)
			WHERE am.is_active = {int:active}
				AND am.race_id > {int:zero}
			ORDER BY am.army_size DESC
			LIMIT 10',
			array(
				'active' => 1,
				'zero' => 0,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['army_top_players'][] = array(
				'id' => (int) $row['id_member'],
				'name' => $row['real_name'],
				'army_size' => army_format_number($row['army_size']),
				'army_size_raw' => (int) $row['army_size'],
				'rank_level' => (int) $row['rank_level'],
				'race_name' => $row['race_name'],
				'race_icon' => $row['default_icon'],
			);
		}

		$smcFunc['db_free_result']($request);

		$context['sub_template'] = 'army_dashboard';
		$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_dashboard'] ?? 'Dashboard');

		return;
	}

	// Logged-in user: load their army data
	$member = army_load_member($user_info['id']);

	// If no army record or no race chosen, redirect to race picker
	if ($member === false || empty($member['race_id']))
	{
		redirectexit('action=army;sa=race');
		return;
	}

	// Check if on vacation
	$context['army_on_vacation'] = army_check_vacation($member);

	if ($context['army_on_vacation'])
	{
		$context['army_vacation_end'] = (int) $member['vacation_end'];
		$context['army_vacation_remaining'] = max(0, (int) $member['vacation_end'] - time());
	}

	// Load inventory and calculate power ratings
	$inventory = army_load_inventory($user_info['id']);

	$attack_power = army_calculate_attack_power($member, $inventory);
	$defense_power = army_calculate_defense_power($member, $inventory);
	$spy_power = army_calculate_spy_power($member, $inventory);
	$sentry_power = army_calculate_sentry_power($member, $inventory);
	$naval_power = army_calculate_naval_power($member, $inventory);

	// Load fort and siege level names
	$fort_items = army_load_items('f');
	$siege_items = army_load_items('s');

	$fort_name = isset($fort_items[$member['fort_level']]) ? $fort_items[$member['fort_level']]['name'] : ($txt['army_unknown'] ?? 'Unknown');
	$siege_name = isset($siege_items[$member['siege_level']]) ? $siege_items[$member['siege_level']]['name'] : ($txt['army_unknown'] ?? 'Unknown');

	// Calculate current rank
	$current_rank = army_get_rank($member);

	// Set member data in context
	$context['army_member'] = array(
		'id' => (int) $member['id_member'],
		'race_id' => (int) $member['race_id'],
		'race_name' => $member['race_name'],
		'race_icon' => $member['default_icon'],
		'army_points' => army_format_number($member['army_points']),
		'army_points_raw' => (int) $member['army_points'],
		'army_size' => army_format_number($member['army_size']),
		'army_size_raw' => (int) $member['army_size'],
		'soldiers_attack' => army_format_number($member['soldiers_attack']),
		'soldiers_defense' => army_format_number($member['soldiers_defense']),
		'soldiers_spy' => army_format_number($member['soldiers_spy']),
		'soldiers_sentry' => army_format_number($member['soldiers_sentry']),
		'soldiers_untrained' => army_format_number($member['soldiers_untrained']),
		'mercs_attack' => army_format_number($member['mercs_attack']),
		'mercs_defense' => army_format_number($member['mercs_defense']),
		'mercs_untrained' => army_format_number($member['mercs_untrained']),
		'fort_level' => (int) $member['fort_level'],
		'fort_name' => $fort_name,
		'siege_level' => (int) $member['siege_level'],
		'siege_name' => $siege_name,
		'unit_prod_level' => (int) $member['unit_prod_level'],
		'spy_skill_level' => (int) $member['spy_skill_level'],
		'attack_turns' => (int) $member['attack_turns'],
		'total_attacks' => army_format_number($member['total_attacks']),
		'total_defends' => army_format_number($member['total_defends']),
		'rank_level' => $current_rank,
		'is_active' => (int) $member['is_active'],
		'bonus_income' => (int) ($member['bonus_income'] ?? 0),
		'bonus_discount' => (int) ($member['bonus_discount'] ?? 0),
		'bonus_casualties' => (int) ($member['bonus_casualties'] ?? 0),
		'bonus_attack' => (int) ($member['bonus_attack'] ?? 0),
		'bonus_defence' => (int) ($member['bonus_defence'] ?? 0),
		'bonus_spy' => (int) ($member['bonus_spy'] ?? 0),
		'bonus_sentry' => (int) ($member['bonus_sentry'] ?? 0),
	);

	// Power ratings
	$context['army_attack_power'] = army_format_number((int) $attack_power);
	$context['army_defense_power'] = army_format_number((int) $defense_power);
	$context['army_spy_power'] = army_format_number((int) $spy_power);
	$context['army_sentry_power'] = army_format_number((int) $sentry_power);
	$context['army_naval_power'] = army_format_number((int) $naval_power);

	// Load recent events for this player (last 10)
	$context['army_events'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT ae.id, ae.event_time, ae.event_from, ae.event_to,
			ae.event_type, ae.event_text,
			mf.real_name AS from_name, mt.real_name AS to_name
		FROM {db_prefix}army_events AS ae
			LEFT JOIN {db_prefix}members AS mf ON (mf.id_member = ae.event_from)
			LEFT JOIN {db_prefix}members AS mt ON (mt.id_member = ae.event_to)
		WHERE ae.event_from = {int:id_member}
			OR ae.event_to = {int:id_member}
		ORDER BY ae.event_time DESC
		LIMIT 10',
		array(
			'id_member' => $user_info['id'],
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Resolve placeholders in event text
		$event_text = $row['event_text'];
		$event_text = str_replace('<% FROM %>', $row['from_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'), $event_text);
		$event_text = str_replace('<% TO %>', $row['to_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'), $event_text);
		$event_text = str_replace('<% MONEYNAME %>', $settings['currency_name'] ?? 'Gold', $event_text);

		$context['army_events'][] = array(
			'id' => (int) $row['id'],
			'time' => timeformat((int) $row['event_time']),
			'time_raw' => (int) $row['event_time'],
			'type' => (int) $row['event_type'],
			'text' => $event_text,
			'from_id' => (int) $row['event_from'],
			'from_name' => $row['from_name'] ?? '',
			'to_id' => (int) $row['event_to'],
			'to_name' => $row['to_name'] ?? '',
		);
	}

	$smcFunc['db_free_result']($request);

	// Update last_active timestamp
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}army_members
		SET last_active = {int:now}
		WHERE id_member = {int:id_member}',
		array(
			'now' => time(),
			'id_member' => $user_info['id'],
		)
	);

	$context['sub_template'] = 'army_dashboard';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_dashboard'] ?? 'Dashboard');
}

/**
 * Members list - shows a paginated ranking of all active army players.
 *
 * Players are ranked by army size (descending). Each entry shows the
 * player's name, race, army size, and rank level.
 *
 * @return void
 */
function ArmyMembers()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	isAllowedTo('army_view');

	$settings = $modSettings['army'];

	// Count total active members who have chosen a race
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}army_members
		WHERE is_active = {int:active}
			AND race_id > {int:zero}',
		array(
			'active' => 1,
			'zero' => 0,
		)
	);

	list($total_members) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
	$total_members = (int) $total_members;

	// Pagination
	$per_page = 25;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	if ($start < 0)
		$start = 0;

	$context['page_index'] = constructPageIndex(
		$scripturl . '?action=army;sa=members',
		$start,
		$total_members,
		$per_page
	);
	$context['start'] = $start;

	// Query active members ordered by army size
	$context['army_members'] = array();
	$rank_position = $start;

	$request = $smcFunc['db_query']('', '
		SELECT am.id_member, am.army_size, am.rank_level, am.total_attacks,
			am.total_defends, am.fort_level, am.siege_level,
			ar.name AS race_name, ar.default_icon,
			mem.real_name
		FROM {db_prefix}army_members AS am
			LEFT JOIN {db_prefix}army_races AS ar ON (ar.race_id = am.race_id)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = am.id_member)
		WHERE am.is_active = {int:active}
			AND am.race_id > {int:zero}
		ORDER BY am.army_size DESC
		LIMIT {int:start}, {int:per_page}',
		array(
			'active' => 1,
			'zero' => 0,
			'start' => $start,
			'per_page' => $per_page,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$rank_position++;

		$context['army_members'][] = array(
			'position' => $rank_position,
			'id' => (int) $row['id_member'],
			'name' => $row['real_name'],
			'race_name' => $row['race_name'],
			'race_icon' => $row['default_icon'],
			'army_size' => army_format_number($row['army_size']),
			'army_size_raw' => (int) $row['army_size'],
			'rank_level' => (int) $row['rank_level'],
			'total_attacks' => army_format_number($row['total_attacks']),
			'total_defends' => army_format_number($row['total_defends']),
			'profile_url' => $scripturl . '?action=army;sa=profile;u=' . (int) $row['id_member'],
		);
	}

	$smcFunc['db_free_result']($request);

	$context['total_members'] = $total_members;
	$context['sub_template'] = 'army_members';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_members'] ?? 'Rankings');
}

/**
 * Profile view - shows a specific player's army profile.
 *
 * Displays limited information to other players: race, army size,
 * rank, total attacks/defends, and fort/siege levels. Detailed
 * inventory and gold amounts are hidden.
 *
 * @return void
 */
function ArmyProfile()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	isAllowedTo('army_view');

	// Get the target member ID
	if (!isset($_REQUEST['u']))
	{
		redirectexit('action=army;sa=members');
		return;
	}

	$id_member = (int) $_REQUEST['u'];

	if ($id_member <= 0)
	{
		redirectexit('action=army;sa=members');
		return;
	}

	// Load the target member's army data
	$member = army_load_member($id_member);

	if ($member === false || empty($member['race_id']))
		fatal_lang_error('army_member_not_found', false);

	// Load the SMF member name
	$request = $smcFunc['db_query']('', '
		SELECT real_name
		FROM {db_prefix}members
		WHERE id_member = {int:id_member}
		LIMIT 1',
		array(
			'id_member' => $id_member,
		)
	);

	$name_row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$real_name = ($name_row !== null) ? $name_row['real_name'] : ($txt['army_unknown_player'] ?? 'Unknown');

	// Load fort and siege names
	$fort_items = army_load_items('f');
	$siege_items = army_load_items('s');

	$fort_name = isset($fort_items[$member['fort_level']]) ? $fort_items[$member['fort_level']]['name'] : ($txt['army_unknown'] ?? 'Unknown');
	$siege_name = isset($siege_items[$member['siege_level']]) ? $siege_items[$member['siege_level']]['name'] : ($txt['army_unknown'] ?? 'Unknown');

	// Calculate current rank
	$current_rank = army_get_rank($member);

	// Check if viewing own profile
	$is_own = ($user_info['id'] == $id_member);

	// Build profile data (limited view for other players)
	$context['army_profile'] = array(
		'id' => (int) $member['id_member'],
		'name' => $real_name,
		'race_id' => (int) $member['race_id'],
		'race_name' => $member['race_name'],
		'race_icon' => $member['default_icon'],
		'army_size' => army_format_number($member['army_size']),
		'army_size_raw' => (int) $member['army_size'],
		'rank_level' => $current_rank,
		'total_attacks' => army_format_number($member['total_attacks']),
		'total_defends' => army_format_number($member['total_defends']),
		'fort_level' => (int) $member['fort_level'],
		'fort_name' => $fort_name,
		'siege_level' => (int) $member['siege_level'],
		'siege_name' => $siege_name,
		'is_active' => (int) $member['is_active'],
		'on_vacation' => army_check_vacation($member),
		'is_own' => $is_own,
		'smf_profile_url' => $scripturl . '?action=profile;u=' . (int) $member['id_member'],
	);

	// If viewing own profile, include extra details
	if ($is_own)
	{
		$context['army_profile']['army_points'] = army_format_number($member['army_points']);
		$context['army_profile']['soldiers_attack'] = army_format_number($member['soldiers_attack']);
		$context['army_profile']['soldiers_defense'] = army_format_number($member['soldiers_defense']);
		$context['army_profile']['soldiers_spy'] = army_format_number($member['soldiers_spy']);
		$context['army_profile']['soldiers_sentry'] = army_format_number($member['soldiers_sentry']);
		$context['army_profile']['soldiers_untrained'] = army_format_number($member['soldiers_untrained']);
		$context['army_profile']['attack_turns'] = (int) $member['attack_turns'];
	}

	// Add to linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=army;sa=profile;u=' . $id_member,
		'name' => $real_name,
	);

	$context['sub_template'] = 'army_profile';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . $real_name;
}

/**
 * Profile integration - shows army stats on SMF profile pages.
 *
 * This is called via the integrate_pre_profile_areas hook and uses
 * $context['member']['id'] from SMF's profile context rather than
 * a URL parameter.
 *
 * @return void
 */
function ArmyProfileStats()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	// Load dependencies if not already loaded
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	loadLanguage('ArmySystem');
	loadTemplate('ArmySystem');

	// Get the profile member ID from SMF's profile context
	$id_member = (int) ($context['member']['id'] ?? 0);

	if ($id_member <= 0)
		return;

	// Load the member's army data
	$member = army_load_member($id_member);

	if ($member === false || empty($member['race_id']))
	{
		// Member is not participating in the Army System
		$context['army_profile_stats'] = false;
		$context['sub_template'] = 'army_profile_stats';
		return;
	}

	// Load fort and siege names
	$fort_items = army_load_items('f');
	$siege_items = army_load_items('s');

	$fort_name = isset($fort_items[$member['fort_level']]) ? $fort_items[$member['fort_level']]['name'] : ($txt['army_unknown'] ?? 'Unknown');
	$siege_name = isset($siege_items[$member['siege_level']]) ? $siege_items[$member['siege_level']]['name'] : ($txt['army_unknown'] ?? 'Unknown');

	// Calculate current rank
	$current_rank = army_get_rank($member);

	// Check if viewing own profile
	$is_own = ($user_info['id'] == $id_member);

	// Build profile stats data
	$context['army_profile_stats'] = array(
		'id' => (int) $member['id_member'],
		'race_id' => (int) $member['race_id'],
		'race_name' => $member['race_name'],
		'race_icon' => $member['default_icon'],
		'army_size' => army_format_number($member['army_size']),
		'army_size_raw' => (int) $member['army_size'],
		'rank_level' => $current_rank,
		'total_attacks' => army_format_number($member['total_attacks']),
		'total_defends' => army_format_number($member['total_defends']),
		'fort_level' => (int) $member['fort_level'],
		'fort_name' => $fort_name,
		'siege_level' => (int) $member['siege_level'],
		'siege_name' => $siege_name,
		'is_active' => (int) $member['is_active'],
		'on_vacation' => army_check_vacation($member),
		'is_own' => $is_own,
		'army_url' => $scripturl . '?action=army;sa=profile;u=' . (int) $member['id_member'],
	);

	// If viewing own profile, include extra details
	if ($is_own)
	{
		$context['army_profile_stats']['army_points'] = army_format_number($member['army_points']);
		$context['army_profile_stats']['soldiers_attack'] = army_format_number($member['soldiers_attack']);
		$context['army_profile_stats']['soldiers_defense'] = army_format_number($member['soldiers_defense']);
		$context['army_profile_stats']['soldiers_spy'] = army_format_number($member['soldiers_spy']);
		$context['army_profile_stats']['soldiers_sentry'] = army_format_number($member['soldiers_sentry']);
		$context['army_profile_stats']['soldiers_untrained'] = army_format_number($member['soldiers_untrained']);
		$context['army_profile_stats']['attack_turns'] = (int) $member['attack_turns'];
	}

	$context['sub_template'] = 'army_profile_stats';
}

/**
 * Vacation mode - allows a player to enter or leave vacation mode.
 *
 * While on vacation, the player cannot be attacked and cannot perform
 * most game actions. Vacation duration must be between the configured
 * minimum and maximum days.
 *
 * POST actions:
 * - start_vacation: Enter vacation for a specified number of days
 * - end_vacation:   Leave vacation early (only if vacation_back is enabled)
 *
 * @return void
 */
function ArmyVacation()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	isAllowedTo('army_play');

	$settings = $modSettings['army'];

	// Load member data
	$member = army_load_member($user_info['id']);

	if ($member === false || empty($member['race_id']))
	{
		redirectexit('action=army;sa=race');
		return;
	}

	// Check if vacation mode is allowed
	if (empty($settings['vacation_allowed']))
		fatal_lang_error('army_vacation_not_allowed', false);

	$vacation_min = (int) ($settings['vacation_min_time'] ?? 4);
	$vacation_max = (int) ($settings['vacation_max_time'] ?? 28);
	$vacation_back = !empty($settings['vacation_back']);

	$on_vacation = army_check_vacation($member);

	// Handle POST actions
	if (isset($_POST['start_vacation']) && !$on_vacation)
	{
		checkSession();

		$days = isset($_POST['vacation_days']) ? (int) $_POST['vacation_days'] : 0;

		// Validate duration
		if ($days < $vacation_min || $days > $vacation_max)
			fatal_lang_error('army_vacation_invalid_days', false);

		$vacation_start = time();
		$vacation_end = $vacation_start + ($days * 86400);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET vacation_start = {int:start},
				vacation_end = {int:end}
			WHERE id_member = {int:id_member}',
			array(
				'start' => $vacation_start,
				'end' => $vacation_end,
				'id_member' => $user_info['id'],
			)
		);

		redirectexit('action=army;sa=vacation');
		return;
	}
	elseif (isset($_POST['end_vacation']) && $on_vacation)
	{
		checkSession();

		if (!$vacation_back)
			fatal_lang_error('army_vacation_no_early_return', false);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET vacation_start = {int:zero},
				vacation_end = {int:zero}
			WHERE id_member = {int:id_member}',
			array(
				'zero' => 0,
				'id_member' => $user_info['id'],
			)
		);

		redirectexit('action=army;sa=vacation');
		return;
	}

	// Refresh member data after potential changes
	$member = army_load_member($user_info['id']);
	$on_vacation = army_check_vacation($member);

	// Set context for the template
	$context['army_vacation'] = array(
		'on_vacation' => $on_vacation,
		'vacation_start' => (int) $member['vacation_start'],
		'vacation_end' => (int) $member['vacation_end'],
		'vacation_remaining' => $on_vacation ? max(0, (int) $member['vacation_end'] - time()) : 0,
		'vacation_remaining_days' => $on_vacation ? max(0, (int) ceil(((int) $member['vacation_end'] - time()) / 86400)) : 0,
		'vacation_min' => $vacation_min,
		'vacation_max' => $vacation_max,
		'vacation_back' => $vacation_back,
		'vacation_start_formatted' => !empty($member['vacation_start']) ? timeformat((int) $member['vacation_start']) : '',
		'vacation_end_formatted' => !empty($member['vacation_end']) ? timeformat((int) $member['vacation_end']) : '',
	);

	// Session token for the form
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_vacation';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_vacation'] ?? 'Vacation Mode');
}

/**
 * Helper: Check if the Army System is enabled.
 *
 * Reads from the cached settings and returns true if the army_enabled
 * setting is set and truthy.
 *
 * @return bool True if the Army System is enabled
 */
function army_enabled()
{
	global $modSettings;

	return !empty($modSettings['army']['army_enabled']);
}
