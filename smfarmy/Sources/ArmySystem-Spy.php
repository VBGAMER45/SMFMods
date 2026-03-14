<?php
/**
 * Army System - Espionage System
 *
 * Recon & sabotage missions, spy skill upgrades, spy log.
 * Players send trained spy soldiers on intelligence-gathering (recon) or
 * equipment-damaging (sabotage) missions against other players. Success
 * depends on the attacker's spy power vs the target's sentry power.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Main spy page - mission selector, execution, and spy skill upgrade.
 *
 * GET: Displays the mission form with target selector, mission type
 * (recon/sabotage), spy count slider, current spy power preview, spy
 * skill upgrade section, and list of valid targets.
 *
 * POST (spy mission): Executes a recon or sabotage mission against
 * the selected target. Spy power is pitted against the target's sentry
 * power to determine success. Failed missions result in spies being
 * caught and killed. Successful recon reveals target stats with an
 * accuracy margin. Successful sabotage damages random target equipment.
 *
 * POST (upgrade_spy): Upgrades the player's spy skill level, which
 * provides a percentage bonus to all spy power calculations.
 *
 * @return void
 */
function ArmySpy()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_spy');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	$settings = $modSettings['army'];

	// Load member data
	$member = army_load_member($user_info['id']);

	// Must have a race to perform espionage
	if ($member === false || empty($member['race_id']))
	{
		redirectexit('action=army;sa=race');
		return;
	}

	// Cannot spy while on vacation
	if (army_check_vacation($member))
		fatal_lang_error('army_on_vacation', false);

	// System setting: max spies per mission
	$max_spy = isset($settings['max_spy']) ? (int) $settings['max_spy'] : 10;

	// -------------------------------------------------------
	// POST handler: spy skill upgrade
	// -------------------------------------------------------
	if (isset($_POST['upgrade_spy']))
	{
		checkSession();

		// Reload member for up-to-date data
		$member = army_load_member($user_info['id']);

		if ($member === false || empty($member['race_id']))
			fatal_lang_error('army_member_not_found', false);

		// Load spy skill level items
		$spy_levels = army_load_items('sl');

		$current_level = (int) $member['spy_skill_level'];
		$next_level = $current_level + 1;

		// Validate next level exists
		if (!isset($spy_levels[$next_level]))
			fatal_lang_error('army_spy_max_level', false);

		$upgrade_cost = (int) $spy_levels[$next_level]['price'];
		$gold = (int) $member['army_points'];

		// Check gold
		if ($gold < $upgrade_cost)
			fatal_lang_error('army_not_enough_gold', false);

		// Deduct gold and increment spy_skill_level
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET army_points = army_points - {int:cost},
				spy_skill_level = {int:next_level},
				last_active = {int:now}
			WHERE id_member = {int:id_member}',
			array(
				'cost' => $upgrade_cost,
				'next_level' => $next_level,
				'now' => time(),
				'id_member' => $user_info['id'],
			)
		);

		// Redirect with success
		redirectexit('action=army;sa=spy;upgraded=1');
		return;
	}

	// -------------------------------------------------------
	// POST handler: execute spy mission
	// -------------------------------------------------------
	if (isset($_POST['spy_mission']))
	{
		checkSession();

		// Reload member for up-to-date data
		$member = army_load_member($user_info['id']);

		if ($member === false || empty($member['race_id']))
			fatal_lang_error('army_member_not_found', false);

		if (army_check_vacation($member))
			fatal_lang_error('army_on_vacation', false);

		// Validate mission type
		$mission = isset($_POST['mission']) ? $_POST['mission'] : '';

		if (!in_array($mission, array('recon', 'sab')))
			fatal_lang_error('army_spy_invalid_mission', false);

		// Sanitize inputs
		$target_id = (int) ($_POST['target'] ?? 0);
		$spies_used = max(1, (int) ($_POST['spies'] ?? 1));

		// Cap spies at member's available spy soldiers and system max
		$spies_used = min($spies_used, (int) $member['soldiers_spy'], $max_spy);

		// Must have at least 1 spy soldier
		if ((int) $member['soldiers_spy'] <= 0 || $spies_used <= 0)
			fatal_lang_error('army_spy_no_spies', false);

		// --- Target Validation ---

		// Cannot spy on yourself
		if ($target_id === $user_info['id'])
			fatal_lang_error('army_spy_target_self', false);

		if ($target_id <= 0)
			fatal_lang_error('army_spy_invalid_target', false);

		// Load target data
		$target = army_load_member($target_id);

		if ($target === false || empty($target['race_id']))
			fatal_lang_error('army_spy_invalid_target', false);

		// Target must be active and not on vacation
		if (!army_is_active($target))
			fatal_lang_error('army_spy_target_inactive', false);

		// IP clone protection
		$clone_ids = army_check_ip_clone($user_info['id']);

		if (in_array($target_id, $clone_ids))
			fatal_lang_error('army_spy_ip_clone', false);

		// --- Spy Power Calculation ---

		// Base spy power = spies_used + sum of spy tool contributions
		$atk_inventory = army_load_inventory($user_info['id']);

		$spy_tool_power = 0.0;

		foreach ($atk_inventory as $key => $item)
		{
			if ($item['i_section'] === 'q' && (int) $item['i_quantity'] > 0)
				$spy_tool_power += (float) $item['i_quantity'] * (float) $item['i_strength'];
		}

		$base_spy_power = (float) $spies_used + $spy_tool_power;

		// Apply race spy bonus
		$spy_bonus = isset($member['bonus_spy']) ? (int) $member['bonus_spy'] : 0;
		$spy_power = army_apply_race_bonus($base_spy_power, $spy_bonus);

		// Apply spy skill level bonus: +10% per level
		$spy_skill_level = (int) $member['spy_skill_level'];

		if ($spy_skill_level > 0)
			$spy_power *= (1 + $spy_skill_level * 0.1);

		// --- Sentry Power Calculation ---

		$def_inventory = army_load_inventory($target_id);

		$sentry_tool_power = 0.0;

		foreach ($def_inventory as $key => $item)
		{
			if ($item['i_section'] === 'e' && (int) $item['i_quantity'] > 0)
				$sentry_tool_power += (float) $item['i_quantity'] * (float) $item['i_strength'];
		}

		$base_sentry_power = (float) (int) $target['soldiers_sentry'] + $sentry_tool_power;

		// Apply race sentry bonus
		$sentry_bonus = isset($target['bonus_sentry']) ? (int) $target['bonus_sentry'] : 0;
		$sentry_power = army_apply_race_bonus($base_sentry_power, $sentry_bonus);

		// --- Success Check ---

		$total_power = max($spy_power + $sentry_power, 1.0);
		$success_chance = ($spy_power / $total_power) * 100;
		$roll = mt_rand(1, 100);
		$success = ($roll <= (int) $success_chance);

		// --- Process Mission Outcome ---

		if (!$success)
		{
			// Failed: some spies are caught and killed
			$caught = mt_rand(1, $spies_used);

			// Reduce attacker's spy soldiers and army size
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}army_members
				SET soldiers_spy = GREATEST(0, soldiers_spy - {int:caught}),
					army_size = GREATEST(0, army_size - {int:caught}),
					last_active = {int:now}
				WHERE id_member = {int:id_member}',
				array(
					'caught' => $caught,
					'now' => time(),
					'id_member' => $user_info['id'],
				)
			);

			// Log the failed mission
			$smcFunc['db_insert']('insert',
				'{db_prefix}army_spy_logs',
				array(
					'spy_member' => 'int',
					'target_member' => 'int',
					'spies_used' => 'int',
					'spy_time' => 'int',
					'result' => 'string',
					'mission' => 'string',
					'caught' => 'int',
					'link_id' => 'int',
				),
				array(
					$user_info['id'],
					$target_id,
					$spies_used,
					time(),
					'failed',
					$mission,
					$caught,
					0,
				),
				array('s_id')
			);

			// Load target name for the result display
			$target_name = army_get_member_name($target_id);

			// Set failure context for the template
			$context['army_spy_result'] = array(
				'success' => false,
				'mission' => $mission,
				'target_id' => $target_id,
				'target_name' => $target_name,
				'spies_used' => $spies_used,
				'caught' => $caught,
				'spy_power' => army_format_number((int) $spy_power),
				'spy_power_raw' => (int) $spy_power,
				'sentry_power' => army_format_number((int) $sentry_power),
				'sentry_power_raw' => (int) $sentry_power,
				'success_chance' => (int) $success_chance,
			);

			// Load the template and show result
			loadTemplate('ArmySystem-Spy');
			$context['sub_template'] = 'army_spy_result';
			$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_spy_result'] ?? 'Mission Report');

			return;
		}

		// --- Successful Mission ---

		// Update attacker's last_active (spies survive on success)
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET last_active = {int:now}
			WHERE id_member = {int:id_member}',
			array(
				'now' => time(),
				'id_member' => $user_info['id'],
			)
		);

		// Load target name
		$target_name = army_get_member_name($target_id);

		if ($mission === 'recon')
		{
			// --- Recon Mission: Reveal target information ---

			// Calculate accuracy based on spy/sentry power ratio
			$accuracy = min(100, ($spy_power / max($sentry_power, 1)) * 50);

			// Error margin: (100 - accuracy) / 100
			$error = (100 - $accuracy) / 100;

			// Compute target's actual power ratings
			$target_attack_power = army_calculate_attack_power($target, $def_inventory);
			$target_defense_power = army_calculate_defense_power($target, $def_inventory);

			// Actual values
			$actual_army_size = (int) $target['army_size'];
			$actual_gold = (int) $target['army_points'];
			$actual_offense = (int) $target_attack_power;
			$actual_defense = (int) $target_defense_power;

			// Generate approximate ranges
			$recon_data = array(
				'army_size_low' => max(0, (int) floor($actual_army_size * (1 - $error))),
				'army_size_high' => (int) ceil($actual_army_size * (1 + $error)),
				'gold_low' => max(0, (int) floor($actual_gold * (1 - $error))),
				'gold_high' => (int) ceil($actual_gold * (1 + $error)),
				'offense_low' => max(0, (int) floor($actual_offense * (1 - $error))),
				'offense_high' => (int) ceil($actual_offense * (1 + $error)),
				'defense_low' => max(0, (int) floor($actual_defense * (1 - $error))),
				'defense_high' => (int) ceil($actual_defense * (1 + $error)),
				'accuracy' => (int) $accuracy,
				'fort_level' => (int) $target['fort_level'],
				'siege_level' => (int) $target['siege_level'],
				'race_name' => $target['race_name'] ?? ($txt['army_unknown'] ?? 'Unknown'),
			);

			// Load fort/siege names for display
			$fort_items = army_load_items('f');
			$siege_items = army_load_items('s');

			$recon_data['fort_name'] = isset($fort_items[$target['fort_level']]) ? $fort_items[$target['fort_level']]['name'] : ($txt['army_unknown'] ?? 'Unknown');
			$recon_data['siege_name'] = isset($siege_items[$target['siege_level']]) ? $siege_items[$target['siege_level']]['name'] : ($txt['army_unknown'] ?? 'Unknown');

			// Format numbers for display
			$recon_data['army_size_low_fmt'] = army_format_number($recon_data['army_size_low']);
			$recon_data['army_size_high_fmt'] = army_format_number($recon_data['army_size_high']);
			$recon_data['gold_low_fmt'] = army_format_number($recon_data['gold_low']);
			$recon_data['gold_high_fmt'] = army_format_number($recon_data['gold_high']);
			$recon_data['offense_low_fmt'] = army_format_number($recon_data['offense_low']);
			$recon_data['offense_high_fmt'] = army_format_number($recon_data['offense_high']);
			$recon_data['defense_low_fmt'] = army_format_number($recon_data['defense_low']);
			$recon_data['defense_high_fmt'] = army_format_number($recon_data['defense_high']);

			// Log the successful recon
			$smcFunc['db_insert']('insert',
				'{db_prefix}army_spy_logs',
				array(
					'spy_member' => 'int',
					'target_member' => 'int',
					'spies_used' => 'int',
					'spy_time' => 'int',
					'result' => 'string',
					'mission' => 'string',
					'caught' => 'int',
					'link_id' => 'int',
				),
				array(
					$user_info['id'],
					$target_id,
					$spies_used,
					time(),
					'success',
					'recon',
					0,
					0,
				),
				array('s_id')
			);

			// Set result context
			$context['army_spy_result'] = array(
				'success' => true,
				'mission' => 'recon',
				'target_id' => $target_id,
				'target_name' => $target_name,
				'spies_used' => $spies_used,
				'caught' => 0,
				'spy_power' => army_format_number((int) $spy_power),
				'spy_power_raw' => (int) $spy_power,
				'sentry_power' => army_format_number((int) $sentry_power),
				'sentry_power_raw' => (int) $sentry_power,
				'success_chance' => (int) $success_chance,
			);

			$context['army_recon_result'] = $recon_data;
		}
		else
		{
			// --- Sabotage Mission: Damage random target equipment ---

			// Collect all target equipment items (weapons and armor) with quantity > 0
			$target_equipment = array();

			foreach ($def_inventory as $key => $item)
			{
				if (in_array($item['i_section'], array('a', 'd')) && (int) $item['i_quantity'] > 0 && (float) $item['i_strength'] > 0)
					$target_equipment[] = $item;
			}

			$sabotage_item = null;
			$sabotage_damage_pct = 0;
			$sabotage_strength_before = 0.0;
			$sabotage_strength_after = 0.0;
			$link_id = 0;

			if (!empty($target_equipment))
			{
				// Pick a random item to sabotage
				$rand_index = mt_rand(0, count($target_equipment) - 1);
				$sabotage_item = $target_equipment[$rand_index];

				// Damage: reduce i_strength by 5-20% of current strength
				$sabotage_damage_pct = mt_rand(5, 20);
				$sabotage_strength_before = (float) $sabotage_item['i_strength'];
				$damage_amount = $sabotage_strength_before * ($sabotage_damage_pct / 100);
				$sabotage_strength_after = max(0.0, $sabotage_strength_before - $damage_amount);

				// Update the item's strength and increment sabotage count
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_inventory
					SET i_strength = {float:new_strength},
						i_spy_sabbed = i_spy_sabbed + 1
					WHERE i_id = {int:item_id}',
					array(
						'new_strength' => $sabotage_strength_after,
						'item_id' => (int) $sabotage_item['i_id'],
					)
				);

				$link_id = (int) $sabotage_item['i_id'];
			}

			// Log the successful sabotage
			$smcFunc['db_insert']('insert',
				'{db_prefix}army_spy_logs',
				array(
					'spy_member' => 'int',
					'target_member' => 'int',
					'spies_used' => 'int',
					'spy_time' => 'int',
					'result' => 'string',
					'mission' => 'string',
					'caught' => 'int',
					'link_id' => 'int',
				),
				array(
					$user_info['id'],
					$target_id,
					$spies_used,
					time(),
					'success',
					'sab',
					0,
					$link_id,
				),
				array('s_id')
			);

			// Load the item name for display if sabotage occurred
			$sabotage_item_name = '';
			$sabotage_item_section = '';

			if ($sabotage_item !== null)
			{
				// Look up the item's display name from the items table
				$item_type = $sabotage_item['i_section']; // 'a' or 'd'
				$item_number = (int) $sabotage_item['i_number'];

				$items_of_type = army_load_items($item_type);

				if (isset($items_of_type[$item_number]))
					$sabotage_item_name = $items_of_type[$item_number]['name'];
				else
					$sabotage_item_name = $txt['army_unknown'] ?? 'Unknown';

				$sabotage_item_section = ($item_type === 'a')
					? ($txt['army_weapon'] ?? 'Weapon')
					: ($txt['army_armor'] ?? 'Armor');
			}

			// Set result context
			$context['army_spy_result'] = array(
				'success' => true,
				'mission' => 'sab',
				'target_id' => $target_id,
				'target_name' => $target_name,
				'spies_used' => $spies_used,
				'caught' => 0,
				'spy_power' => army_format_number((int) $spy_power),
				'spy_power_raw' => (int) $spy_power,
				'sentry_power' => army_format_number((int) $sentry_power),
				'sentry_power_raw' => (int) $sentry_power,
				'success_chance' => (int) $success_chance,
			);

			$context['army_sabotage_result'] = array(
				'has_target_equipment' => !empty($target_equipment),
				'item_name' => $sabotage_item_name,
				'item_section' => $sabotage_item_section,
				'damage_pct' => $sabotage_damage_pct,
				'strength_before' => number_format($sabotage_strength_before, 2),
				'strength_after' => number_format($sabotage_strength_after, 2),
			);
		}

		// Load the template and show result
		loadTemplate('ArmySystem-Spy');
		$context['sub_template'] = 'army_spy_result';
		$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_spy_result'] ?? 'Mission Report');

		return;
	}

	// -------------------------------------------------------
	// GET: Display the spy mission form
	// -------------------------------------------------------

	// Refresh member data (in case of redirect)
	$member = army_load_member($user_info['id']);

	// Load attacker's inventory for spy power preview
	$inventory = army_load_inventory($user_info['id']);
	$spy_power = army_calculate_spy_power($member, $inventory);

	// Apply spy skill level bonus for the preview
	$spy_skill_level = (int) $member['spy_skill_level'];

	if ($spy_skill_level > 0)
		$spy_power *= (1 + $spy_skill_level * 0.1);

	$sentry_power = army_calculate_sentry_power($member, $inventory);

	// Build member context
	$context['army_member'] = array(
		'id' => (int) $member['id_member'],
		'race_name' => $member['race_name'] ?? '',
		'army_points' => army_format_number($member['army_points']),
		'army_points_raw' => (int) $member['army_points'],
		'army_size' => army_format_number($member['army_size']),
		'army_size_raw' => (int) $member['army_size'],
		'soldiers_spy' => (int) $member['soldiers_spy'],
		'soldiers_spy_formatted' => army_format_number($member['soldiers_spy']),
		'soldiers_sentry' => (int) $member['soldiers_sentry'],
		'soldiers_sentry_formatted' => army_format_number($member['soldiers_sentry']),
		'spy_skill_level' => $spy_skill_level,
		'spy_icon' => $member['spy_icon'] ?? '',
		'sentry_icon' => $member['sentry_icon'] ?? '',
	);

	// Power preview
	$context['army_spy_power'] = army_format_number((int) $spy_power);
	$context['army_spy_power_raw'] = (int) $spy_power;
	$context['army_sentry_power'] = army_format_number((int) $sentry_power);
	$context['army_sentry_power_raw'] = (int) $sentry_power;

	// Maximum spies for the slider: min of soldiers_spy and max_spy setting
	$context['army_max_spies'] = min((int) $member['soldiers_spy'], $max_spy);
	$context['army_max_spy_setting'] = $max_spy;

	// --- Spy Skill Upgrade Section ---

	$spy_levels = army_load_items('sl');

	$context['army_spy_current_level'] = null;

	if ($spy_skill_level > 0 && isset($spy_levels[$spy_skill_level]))
	{
		$context['army_spy_current_level'] = array(
			'level' => $spy_skill_level,
			'name' => $spy_levels[$spy_skill_level]['name'],
		);
	}
	elseif ($spy_skill_level === 0)
	{
		$context['army_spy_current_level'] = array(
			'level' => 0,
			'name' => $txt['army_spy_level_none'] ?? 'None',
		);
	}

	$next_spy_level = $spy_skill_level + 1;
	$context['army_spy_next_level'] = null;

	if (isset($spy_levels[$next_spy_level]))
	{
		$context['army_spy_next_level'] = array(
			'level' => $next_spy_level,
			'name' => $spy_levels[$next_spy_level]['name'],
			'price' => (int) $spy_levels[$next_spy_level]['price'],
			'price_formatted' => army_format_number($spy_levels[$next_spy_level]['price']),
		);
	}

	// --- Build Target List ---

	$context['army_targets'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT am.id_member, am.army_size, am.race_id,
			ar.name AS race_name,
			mem.real_name
		FROM {db_prefix}army_members AS am
			LEFT JOIN {db_prefix}army_races AS ar ON (ar.race_id = am.race_id)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = am.id_member)
		WHERE am.is_active = {int:active}
			AND am.race_id > {int:zero}
			AND am.id_member != {int:self}
			AND (am.vacation_end = {int:no_vac} OR am.vacation_end < {int:now})
		ORDER BY mem.real_name ASC
		LIMIT 500',
		array(
			'active' => 1,
			'zero' => 0,
			'self' => $user_info['id'],
			'no_vac' => 0,
			'now' => time(),
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['army_targets'][] = array(
			'id' => (int) $row['id_member'],
			'name' => $row['real_name'],
			'race_name' => $row['race_name'] ?? '',
			'army_size' => army_format_number($row['army_size']),
			'army_size_raw' => (int) $row['army_size'],
		);
	}

	$smcFunc['db_free_result']($request);

	// Pre-fill target if provided via URL
	$context['army_prefill_target'] = 0;
	$context['army_prefill_target_name'] = '';

	if (isset($_REQUEST['target']))
	{
		$prefill_target = (int) $_REQUEST['target'];

		if ($prefill_target > 0)
		{
			$request = $smcFunc['db_query']('', '
				SELECT real_name
				FROM {db_prefix}members
				WHERE id_member = {int:target}
				LIMIT 1',
				array(
					'target' => $prefill_target,
				)
			);

			$row = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);

			if ($row !== null)
			{
				$context['army_prefill_target'] = $prefill_target;
				$context['army_prefill_target_name'] = $row['real_name'];
			}
		}
	}

	// Current gold for display
	$context['army_gold'] = army_format_number($member['army_points']);
	$context['army_gold_raw'] = (int) $member['army_points'];

	// Success flags from redirects
	$context['army_spy_upgrade_success'] = isset($_REQUEST['upgraded']) && $_REQUEST['upgraded'] == 1;

	// Session tokens for the forms
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	// Load the template
	loadTemplate('ArmySystem-Spy');
	$context['sub_template'] = 'army_spy';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_spy_title'] ?? 'Espionage');
}

/**
 * Spy log viewer - browse spy mission history.
 *
 * Shows a paginated listing of all spy missions involving the current
 * player (as spy or target), with details on mission type, result,
 * spies used, spies caught, and timestamp.
 *
 * Access is restricted to viewing one's own spy logs or having admin
 * permission.
 *
 * @return void
 */
function ArmySpyLog()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_view');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	// Load the template
	loadTemplate('ArmySystem-Spy');

	// --- Filter by view mode ---
	$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'all';

	if (!in_array($view, array('all', 'sent', 'received')))
		$view = 'all';

	// Build WHERE clause based on filter
	switch ($view)
	{
		case 'sent':
			$where = 'sl.spy_member = {int:id_member}';
			break;

		case 'received':
			$where = 'sl.target_member = {int:id_member}';
			break;

		default: // 'all'
			$where = '(sl.spy_member = {int:id_member} OR sl.target_member = {int:id_member})';
			break;
	}

	$query_params = array(
		'id_member' => $user_info['id'],
	);

	// Count total matching spy logs
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}army_spy_logs AS sl
		WHERE ' . $where,
		$query_params
	);

	list($total_logs) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
	$total_logs = (int) $total_logs;

	// Pagination
	$per_page = 20;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	if ($start < 0)
		$start = 0;

	$context['page_index'] = constructPageIndex(
		$scripturl . '?action=army;sa=spylog;view=' . $view,
		$start,
		$total_logs,
		$per_page
	);
	$context['start'] = $start;

	// Query spy logs with member name joins
	$context['army_spy_logs'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT sl.s_id, sl.spy_member, sl.target_member, sl.spies_used,
			sl.spy_time, sl.result, sl.mission, sl.caught, sl.link_id,
			ms.real_name AS spy_name,
			mt.real_name AS target_name
		FROM {db_prefix}army_spy_logs AS sl
			LEFT JOIN {db_prefix}members AS ms ON (ms.id_member = sl.spy_member)
			LEFT JOIN {db_prefix}members AS mt ON (mt.id_member = sl.target_member)
		WHERE ' . $where . '
		ORDER BY sl.spy_time DESC
		LIMIT {int:start}, {int:per_page}',
		array_merge($query_params, array(
			'start' => $start,
			'per_page' => $per_page,
		))
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['army_spy_logs'][] = array(
			'id' => (int) $row['s_id'],
			'spy_id' => (int) $row['spy_member'],
			'spy_name' => $row['spy_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
			'target_id' => (int) $row['target_member'],
			'target_name' => $row['target_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
			'spies_used' => (int) $row['spies_used'],
			'spy_time' => timeformat((int) $row['spy_time']),
			'spy_time_raw' => (int) $row['spy_time'],
			'result' => $row['result'],
			'result_label' => ($row['result'] === 'success')
				? ($txt['army_spy_success'] ?? 'Success')
				: ($txt['army_spy_failed'] ?? 'Failed'),
			'mission' => $row['mission'],
			'mission_label' => ($row['mission'] === 'recon')
				? ($txt['army_spy_recon'] ?? 'Recon')
				: ($txt['army_spy_sabotage'] ?? 'Sabotage'),
			'caught' => (int) $row['caught'],
			'link_id' => (int) $row['link_id'],
			'is_spy' => ((int) $row['spy_member'] === $user_info['id']),
			'spy_profile_url' => $scripturl . '?action=army;sa=profile;u=' . (int) $row['spy_member'],
			'target_profile_url' => $scripturl . '?action=army;sa=profile;u=' . (int) $row['target_member'],
		);
	}

	$smcFunc['db_free_result']($request);

	// View filter tabs for the template
	$context['army_log_views'] = array(
		'all' => array(
			'label' => $txt['army_spy_log_all'] ?? 'All Missions',
			'url' => $scripturl . '?action=army;sa=spylog;view=all',
			'active' => ($view === 'all'),
		),
		'sent' => array(
			'label' => $txt['army_spy_log_sent'] ?? 'Missions Sent',
			'url' => $scripturl . '?action=army;sa=spylog;view=sent',
			'active' => ($view === 'sent'),
		),
		'received' => array(
			'label' => $txt['army_spy_log_received'] ?? 'Missions Against Me',
			'url' => $scripturl . '?action=army;sa=spylog;view=received',
			'active' => ($view === 'received'),
		),
	);

	$context['army_log_view'] = $view;
	$context['army_log_total'] = $total_logs;

	// Add to linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=army;sa=spylog',
		'name' => $txt['army_spy_log'] ?? 'Spy Log',
	);

	$context['sub_template'] = 'army_spylog';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_spy_log'] ?? 'Spy Log');
}

/**
 * Helper: Get a member's display name from the SMF members table.
 *
 * @param int $id_member The member ID to look up
 * @return string The member's real_name, or a fallback string if not found
 */
function army_get_member_name($id_member)
{
	global $smcFunc, $txt;

	$id_member = (int) $id_member;

	if ($id_member <= 0)
		return $txt['army_unknown_player'] ?? 'Unknown';

	$request = $smcFunc['db_query']('', '
		SELECT real_name
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
		return $row['real_name'];

	return $txt['army_unknown_player'] ?? 'Unknown';
}
