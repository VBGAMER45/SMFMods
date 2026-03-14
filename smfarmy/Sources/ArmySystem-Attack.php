<?php
/**
 * Army System - Attack & Combat System
 *
 * Battle calculator, attack execution, attack log viewer.
 * Handles the core combat mechanic: players spend attack turns to assault
 * other players, with outcomes determined by offensive/defensive power,
 * equipment, fortifications, siege technology, and race bonuses.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Main attack page - select a target and execute an attack.
 *
 * GET: Displays the attack form with the attacker's current power
 * preview and a member search field for target selection.
 *
 * POST: Executes the full battle calculation between attacker and
 * defender, applying all bonuses, determining casualties and gold
 * theft, degrading equipment, logging the battle, and firing an
 * event. Redirects to the battle result view.
 *
 * Battle calculation overview:
 *   1. Calculate attacker offense (weapons + soldiers + mercs + race + siege) * turns
 *   2. Calculate defender defense (armor + soldiers + mercs + race + fort)
 *   3. Apply +/-20% randomization to both sides
 *   4. Higher value wins; calculate gold stolen, casualties, equipment degradation
 *   5. Log everything and insert an event
 *
 * @return void
 */
function ArmyAttack()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_attack');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	$settings = $modSettings['army'];

	// Load attacker member data
	$member = army_load_member($user_info['id']);

	// Must have a race to attack
	if ($member === false || empty($member['race_id']))
	{
		redirectexit('action=army;sa=race');
		return;
	}

	// Cannot attack while on vacation
	if (army_check_vacation($member))
		fatal_lang_error('army_on_vacation', false);

	// Must have attack turns available
	if ((int) $member['attack_turns'] <= 0)
		fatal_lang_error('army_no_turns', false);

	// Setting limits
	$turns_max = isset($settings['turns_max']) ? (int) $settings['turns_max'] : 10;
	$max_attack = isset($settings['max_attack']) ? (int) $settings['max_attack'] : 5;
	$attack_money = isset($settings['attack_money']) ? (int) $settings['attack_money'] : 1;
	$log_time = isset($settings['log_time']) ? (int) $settings['log_time'] : 14;
	$log_time_unit = isset($settings['log_time_unit']) ? (int) $settings['log_time_unit'] : 86400;
	$fort_percent = isset($settings['fort_percent']) ? (int) $settings['fort_percent'] : 25;
	$siege_percent = isset($settings['siege_percent']) ? (int) $settings['siege_percent'] : 25;

	// -------------------------------------------------------
	// POST handler: execute the attack
	// -------------------------------------------------------
	if (isset($_POST['attack_target']))
	{
		checkSession();

		// Reload attacker data for up-to-date state
		$member = army_load_member($user_info['id']);

		if ($member === false || empty($member['race_id']))
			fatal_lang_error('army_member_not_found', false);

		if (army_check_vacation($member))
			fatal_lang_error('army_on_vacation', false);

		if ((int) $member['attack_turns'] <= 0)
			fatal_lang_error('army_no_turns', false);

		// Sanitize inputs
		$target_id = (int) ($_POST['target'] ?? 0);
		$turns = max(1, (int) ($_POST['turns'] ?? 1));

		// Cap turns at the member's available turns and the system max
		$turns = min($turns, (int) $member['attack_turns'], $turns_max);

		// --- Validation ---

		// Cannot attack yourself
		if ($target_id === $user_info['id'])
			fatal_lang_error('army_attack_self', false);

		// Target must be a valid member
		if ($target_id <= 0)
			fatal_lang_error('army_attack_invalid_target', false);

		// Load target data
		$target = army_load_member($target_id);

		if ($target === false || empty($target['race_id']))
			fatal_lang_error('army_attack_invalid_target', false);

		// Target must be active and not on vacation
		if (!army_is_active($target))
			fatal_lang_error('army_attack_target_inactive', false);

		// Flood protection: count recent attacks against this target
		$flood_window = $log_time * $log_time_unit;
		$flood_since = time() - $flood_window;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}army_attack_logs
			WHERE attacker = {int:attacker}
				AND defender = {int:defender}
				AND attack_time > {int:since}',
			array(
				'attacker' => $user_info['id'],
				'defender' => $target_id,
				'since' => $flood_since,
			)
		);

		list($recent_attacks) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if ((int) $recent_attacks >= $max_attack)
			fatal_lang_error('army_attack_flood', false);

		// IP clone protection: check if target shares IP with attacker
		$clone_ids = army_check_ip_clone($user_info['id']);

		if (in_array($target_id, $clone_ids))
			fatal_lang_error('army_attack_ip_clone', false);

		// --- Battle Calculation ---

		// Load inventories for both sides
		$atk_inventory = army_load_inventory($user_info['id']);
		$def_inventory = army_load_inventory($target_id);

		// Step 1: Calculate Attacker Offense
		$weapon_power = 0.0;
		$atk_ship_power = 0.0;

		foreach ($atk_inventory as $key => $item)
		{
			if ($item['i_section'] === 'a' && (int) $item['i_quantity'] > 0)
				$weapon_power += (float) $item['i_quantity'] * (float) $item['i_strength'];
			elseif ($item['i_section'] === 'b' && (int) $item['i_quantity'] > 0)
				$atk_ship_power += (float) $item['i_quantity'] * (float) $item['i_strength'];
		}

		$atk_soldier_power = (float) (int) $member['soldiers_attack'];
		$atk_merc_power = (float) (int) $member['mercs_attack'] * 10;
		// Ships contribute 50% of their power to offense
		$base_offense = $weapon_power + $atk_soldier_power + $atk_merc_power + ($atk_ship_power / 2);

		// Apply race attack bonus
		$atk_bonus = isset($member['bonus_attack']) ? (int) $member['bonus_attack'] : 0;
		$offense = army_apply_race_bonus($base_offense, $atk_bonus);

		// Apply siege bonus: if attacker siege_level > 1
		$atk_siege_level = (int) $member['siege_level'];

		if ($atk_siege_level > 1)
			$offense *= (1 + ($atk_siege_level - 1) * $siege_percent / 100);

		// Multiply by turns used
		$offense *= $turns;

		// Step 2: Calculate Defender Defense
		$armor_power = 0.0;
		$def_ship_power = 0.0;

		foreach ($def_inventory as $key => $item)
		{
			if ($item['i_section'] === 'd' && (int) $item['i_quantity'] > 0)
				$armor_power += (float) $item['i_quantity'] * (float) $item['i_strength'];
			elseif ($item['i_section'] === 'b' && (int) $item['i_quantity'] > 0)
				$def_ship_power += (float) $item['i_quantity'] * (float) $item['i_strength'];
		}

		$def_soldier_power = (float) (int) $target['soldiers_defense'];
		$def_merc_power = (float) (int) $target['mercs_defense'] * 10;
		// Ships contribute 50% of their power to defense
		$base_defense = $armor_power + $def_soldier_power + $def_merc_power + ($def_ship_power / 2);

		// Apply race defense bonus
		$def_bonus = isset($target['bonus_defence']) ? (int) $target['bonus_defence'] : 0;
		$defense = army_apply_race_bonus($base_defense, $def_bonus);

		// Apply fort bonus: if defender fort_level > 1
		$def_fort_level = (int) $target['fort_level'];

		if ($def_fort_level > 1)
			$defense *= (1 + ($def_fort_level - 1) * $fort_percent / 100);

		// Defense is NOT multiplied by turns (defender doesn't choose turns)

		// Step 3: Randomization (+/- 20% variance)
		$offense *= mt_rand(80, 120) / 100;
		$defense *= mt_rand(80, 120) / 100;

		// Ensure non-negative
		$offense = max(0.0, $offense);
		$defense = max(0.0, $defense);

		// Step 4: Determine winner
		$attacker_wins = ($offense > $defense);

		// Step 5: Calculate consequences

		// -- Gold stolen --
		$gold_stolen = 0;

		if ($attacker_wins)
		{
			// stolen = defender's gold * (turns * attack_money / 100)
			$steal_fraction = ($turns * $attack_money) / 100;
			$gold_stolen = (int) floor((float) $target['army_points'] * $steal_fraction);

			// Cap at defender's total gold
			$gold_stolen = min($gold_stolen, (int) $target['army_points']);
			$gold_stolen = max(0, $gold_stolen);
		}

		// -- Casualties --
		$max_power = max($offense, $defense, 1.0);
		$power_ratio = abs($offense - $defense) / $max_power;

		// Loser takes heavier casualties
		if ($attacker_wins)
		{
			$loser_casualties_base = floor((float) $target['army_size'] * $power_ratio * 0.05 * $turns);
			$winner_casualties_base = floor((float) $member['army_size'] * $power_ratio * 0.01 * $turns);

			// Apply race casualties bonus (reduces own casualties)
			$atk_casualty_bonus = isset($member['bonus_casualties']) ? (int) $member['bonus_casualties'] : 0;
			$def_casualty_bonus = isset($target['bonus_casualties']) ? (int) $target['bonus_casualties'] : 0;

			$atk_casualties = (int) max(0, floor($winner_casualties_base * (1 - $atk_casualty_bonus / 100)));
			$def_casualties = (int) max(0, floor($loser_casualties_base * (1 - $def_casualty_bonus / 100)));
		}
		else
		{
			$loser_casualties_base = floor((float) $member['army_size'] * $power_ratio * 0.05 * $turns);
			$winner_casualties_base = floor((float) $target['army_size'] * $power_ratio * 0.01 * $turns);

			$atk_casualty_bonus = isset($member['bonus_casualties']) ? (int) $member['bonus_casualties'] : 0;
			$def_casualty_bonus = isset($target['bonus_casualties']) ? (int) $target['bonus_casualties'] : 0;

			$atk_casualties = (int) max(0, floor($loser_casualties_base * (1 - $atk_casualty_bonus / 100)));
			$def_casualties = (int) max(0, floor($winner_casualties_base * (1 - $def_casualty_bonus / 100)));
		}

		// Ensure casualties don't exceed army size
		$atk_casualties = min($atk_casualties, (int) $member['army_size']);
		$def_casualties = min($def_casualties, (int) $target['army_size']);

		// Remove casualties from untrained first, then from trained soldiers proportionally
		$atk_casualty_breakdown = army_distribute_casualties($member, $atk_casualties);
		$def_casualty_breakdown = army_distribute_casualties($target, $def_casualties);

		// -- Equipment degradation --
		// 0.5% per turn used, applied to all equipped items on both sides
		$degradation_rate = 0.005 * $turns;

		// Degrade attacker weapons, armor, and ships
		if (!empty($atk_inventory))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}army_inventory
				SET i_strength = GREATEST(0, i_strength - i_strength * {float:rate})
				WHERE i_member = {int:id_member}
					AND i_section IN ({string:sec_a}, {string:sec_d}, {string:sec_b})',
				array(
					'rate' => $degradation_rate,
					'id_member' => $user_info['id'],
					'sec_a' => 'a',
					'sec_d' => 'd',
					'sec_b' => 'b',
				)
			);
		}

		// Degrade defender weapons, armor, and ships
		if (!empty($def_inventory))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}army_inventory
				SET i_strength = GREATEST(0, i_strength - i_strength * {float:rate})
				WHERE i_member = {int:target}
					AND i_section IN ({string:sec_a}, {string:sec_d}, {string:sec_b})',
				array(
					'rate' => $degradation_rate,
					'target' => $target_id,
					'sec_a' => 'a',
					'sec_d' => 'd',
					'sec_b' => 'b',
				)
			);
		}

		// -- Spy/Sentry casualties (small chance in battle) --
		// 5% chance per side to lose 1-3 spy or sentry soldiers
		$atk_spy_killed = 0;
		$atk_sen_killed = 0;
		$def_spy_killed = 0;
		$def_sen_killed = 0;

		if (mt_rand(1, 100) <= 5)
		{
			$atk_spy_killed = min(mt_rand(1, 3), (int) $member['soldiers_spy']);
		}

		if (mt_rand(1, 100) <= 5)
		{
			$atk_sen_killed = min(mt_rand(1, 3), (int) $member['soldiers_sentry']);
		}

		if (mt_rand(1, 100) <= 5)
		{
			$def_spy_killed = min(mt_rand(1, 3), (int) $target['soldiers_spy']);
		}

		if (mt_rand(1, 100) <= 5)
		{
			$def_sen_killed = min(mt_rand(1, 3), (int) $target['soldiers_sentry']);
		}

		// Total attacker losses (casualties + spy/sentry killed)
		$atk_total_lost = $atk_casualties + $atk_spy_killed + $atk_sen_killed;
		$def_total_lost = $def_casualties + $def_spy_killed + $def_sen_killed;

		// Step 6: Update attacker
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET army_points = army_points + {int:gold_gain},
				army_size = GREATEST(0, army_size - {int:atk_total_lost}),
				soldiers_untrained = GREATEST(0, soldiers_untrained - {int:untrained_loss}),
				soldiers_attack = GREATEST(0, soldiers_attack - {int:attack_loss}),
				soldiers_defense = GREATEST(0, soldiers_defense - {int:defense_loss}),
				soldiers_spy = GREATEST(0, soldiers_spy - {int:spy_loss}),
				soldiers_sentry = GREATEST(0, soldiers_sentry - {int:sentry_loss}),
				attack_turns = GREATEST(0, attack_turns - {int:turns_used}),
				total_attacks = total_attacks + 1,
				last_active = {int:now}
			WHERE id_member = {int:id_member}',
			array(
				'gold_gain' => $gold_stolen,
				'atk_total_lost' => $atk_total_lost,
				'untrained_loss' => (int) $atk_casualty_breakdown['untrained'],
				'attack_loss' => (int) $atk_casualty_breakdown['attack'],
				'defense_loss' => (int) $atk_casualty_breakdown['defense'],
				'spy_loss' => (int) $atk_casualty_breakdown['spy'] + $atk_spy_killed,
				'sentry_loss' => (int) $atk_casualty_breakdown['sentry'] + $atk_sen_killed,
				'turns_used' => $turns,
				'now' => time(),
				'id_member' => $user_info['id'],
			)
		);

		// Step 7: Update defender
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET army_points = GREATEST(0, army_points - {int:gold_lost}),
				army_size = GREATEST(0, army_size - {int:def_total_lost}),
				soldiers_untrained = GREATEST(0, soldiers_untrained - {int:untrained_loss}),
				soldiers_attack = GREATEST(0, soldiers_attack - {int:attack_loss}),
				soldiers_defense = GREATEST(0, soldiers_defense - {int:defense_loss}),
				soldiers_spy = GREATEST(0, soldiers_spy - {int:spy_loss}),
				soldiers_sentry = GREATEST(0, soldiers_sentry - {int:sentry_loss}),
				total_defends = total_defends + 1
			WHERE id_member = {int:target}',
			array(
				'gold_lost' => $gold_stolen,
				'def_total_lost' => $def_total_lost,
				'untrained_loss' => (int) $def_casualty_breakdown['untrained'],
				'attack_loss' => (int) $def_casualty_breakdown['attack'],
				'defense_loss' => (int) $def_casualty_breakdown['defense'],
				'spy_loss' => (int) $def_casualty_breakdown['spy'] + $def_spy_killed,
				'sentry_loss' => (int) $def_casualty_breakdown['sentry'] + $def_sen_killed,
				'target' => $target_id,
			)
		);

		// Step 8: Insert attack log
		$smcFunc['db_insert']('insert',
			'{db_prefix}army_attack_logs',
			array(
				'attack_time' => 'int',
				'money_stolen' => 'int',
				'turns_used' => 'int',
				'attacker' => 'int',
				'defender' => 'int',
				'atk_damage' => 'int',
				'def_damage' => 'int',
				'atk_kill' => 'int',
				'def_kill' => 'int',
				'atk_army_gen' => 'int',
				'atk_army_atk' => 'int',
				'atk_army_mgen' => 'int',
				'atk_army_matk' => 'int',
				'def_army_gen' => 'int',
				'def_army_def' => 'int',
				'def_army_mgen' => 'int',
				'def_army_mdef' => 'int',
				'atk_spy_killed' => 'int',
				'atk_sen_killed' => 'int',
				'def_spy_killed' => 'int',
				'def_sen_killed' => 'int',
			),
			array(
				time(),
				$gold_stolen,
				$turns,
				$user_info['id'],
				$target_id,
				(int) round($offense),
				(int) round($defense),
				$atk_casualties,
				$def_casualties,
				(int) $member['army_size'],
				(int) $member['soldiers_attack'],
				(int) $member['mercs_untrained'],
				(int) $member['mercs_attack'],
				(int) $target['army_size'],
				(int) $target['soldiers_defense'],
				(int) $target['mercs_untrained'],
				(int) $target['mercs_defense'],
				$atk_spy_killed,
				$atk_sen_killed,
				$def_spy_killed,
				$def_sen_killed,
			),
			array('id')
		);

		// Get the inserted log ID
		$log_id = $smcFunc['db_insert_id']('{db_prefix}army_attack_logs');

		// Step 9: Insert attack log inventory snapshots for both sides
		army_log_battle_inventory($log_id, 'a', $user_info['id'], $atk_inventory);
		army_log_battle_inventory($log_id, 'b', $user_info['id'], $atk_inventory);
		army_log_battle_inventory($log_id, 'd', $target_id, $def_inventory);
		army_log_battle_inventory($log_id, 'b', $target_id, $def_inventory);

		// Step 10: Insert event
		if ($attacker_wins)
			$event_text = '<% FROM %> attacked <% TO %> and was successful';
		else
			$event_text = '<% FROM %> attacked <% TO %> and was defeated';

		army_log_event($user_info['id'], $target_id, 1, $event_text);

		// Step 11: Load member names for the result display
		$atk_name = '';
		$def_name = '';

		$request = $smcFunc['db_query']('', '
			SELECT id_member, real_name
			FROM {db_prefix}members
			WHERE id_member IN ({int:atk}, {int:def})',
			array(
				'atk' => $user_info['id'],
				'def' => $target_id,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ((int) $row['id_member'] === $user_info['id'])
				$atk_name = $row['real_name'];
			elseif ((int) $row['id_member'] === $target_id)
				$def_name = $row['real_name'];
		}

		$smcFunc['db_free_result']($request);

		// Step 12: Set battle result context for the template
		$context['army_battle_result'] = array(
			'log_id' => $log_id,
			'attacker_wins' => $attacker_wins,
			'attacker_id' => $user_info['id'],
			'attacker_name' => $atk_name,
			'attacker_race' => $member['race_name'] ?? '',
			'defender_id' => $target_id,
			'defender_name' => $def_name,
			'defender_race' => $target['race_name'] ?? '',
			'turns_used' => $turns,
			'offense' => army_format_number((int) round($offense)),
			'offense_raw' => (int) round($offense),
			'defense' => army_format_number((int) round($defense)),
			'defense_raw' => (int) round($defense),
			'gold_stolen' => army_format_number($gold_stolen),
			'gold_stolen_raw' => $gold_stolen,
			'atk_casualties' => army_format_number($atk_casualties),
			'atk_casualties_raw' => $atk_casualties,
			'def_casualties' => army_format_number($def_casualties),
			'def_casualties_raw' => $def_casualties,
			'atk_spy_killed' => $atk_spy_killed,
			'atk_sen_killed' => $atk_sen_killed,
			'def_spy_killed' => $def_spy_killed,
			'def_sen_killed' => $def_sen_killed,
			'atk_army_before' => army_format_number($member['army_size']),
			'def_army_before' => army_format_number($target['army_size']),
			'atk_siege_level' => $atk_siege_level,
			'def_fort_level' => $def_fort_level,
		);

		// Load the template and display the result
		loadTemplate('ArmySystem-Attack');
		$context['sub_template'] = 'army_battle_result';
		$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_battle_result'] ?? 'Battle Result');

		return;
	}

	// -------------------------------------------------------
	// GET: Display the attack form
	// -------------------------------------------------------

	// Load attacker's inventory for power preview
	$inventory = army_load_inventory($user_info['id']);
	$attack_power = army_calculate_attack_power($member, $inventory);
	$defense_power = army_calculate_defense_power($member, $inventory);

	// Pre-fill target if provided via URL
	$prefill_target = 0;
	$prefill_target_name = '';

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
				$prefill_target_name = $row['real_name'];
			else
				$prefill_target = 0;
		}
	}

	// Build the list of attackable members for the search/select
	// We load a compact list of active players with race chosen (excluding self)
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

	// Build context for the template
	$context['army_member'] = array(
		'id' => (int) $member['id_member'],
		'race_name' => $member['race_name'] ?? '',
		'attack_turns' => (int) $member['attack_turns'],
		'army_size' => army_format_number($member['army_size']),
		'army_size_raw' => (int) $member['army_size'],
		'army_points' => army_format_number($member['army_points']),
		'army_points_raw' => (int) $member['army_points'],
		'soldiers_attack' => army_format_number($member['soldiers_attack']),
		'soldiers_defense' => army_format_number($member['soldiers_defense']),
		'mercs_attack' => army_format_number($member['mercs_attack']),
		'mercs_defense' => army_format_number($member['mercs_defense']),
		'fort_level' => (int) $member['fort_level'],
		'siege_level' => (int) $member['siege_level'],
	);

	$context['army_attack_power'] = army_format_number((int) $attack_power);
	$context['army_attack_power_raw'] = (int) $attack_power;
	$context['army_defense_power'] = army_format_number((int) $defense_power);
	$context['army_defense_power_raw'] = (int) $defense_power;

	// Turns selector: 1 to min(attack_turns, turns_max)
	$context['army_turns_max'] = min((int) $member['attack_turns'], $turns_max);
	$context['army_turns_setting_max'] = $turns_max;

	// Pre-fill target
	$context['army_prefill_target'] = $prefill_target;
	$context['army_prefill_target_name'] = $prefill_target_name;

	// Session tokens for the form
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	// Load the template
	loadTemplate('ArmySystem-Attack');
	$context['sub_template'] = 'army_attack';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_attack_title'] ?? 'Attack');
}

/**
 * Attack log viewer - browse and inspect attack history.
 *
 * Supports two modes:
 *   - List view: Paginated listing of all attacks involving the current
 *     player, filterable by 'sent', 'received', or 'all'.
 *   - Detail view: Full battle breakdown for a single attack log entry,
 *     including per-item inventory snapshots.
 *
 * Access is restricted to the involved parties or admin users.
 *
 * @return void
 */
function ArmyAttackLog()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_view');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	// Load the template
	loadTemplate('ArmySystem-Attack');

	$log_id = (int) ($_REQUEST['log'] ?? 0);

	// -------------------------------------------------------
	// Single log detail view
	// -------------------------------------------------------
	if ($log_id > 0)
	{
		// Load the attack log entry
		$request = $smcFunc['db_query']('', '
			SELECT al.*,
				ma.real_name AS attacker_name,
				md.real_name AS defender_name,
				ra.name AS attacker_race,
				rd.name AS defender_race
			FROM {db_prefix}army_attack_logs AS al
				LEFT JOIN {db_prefix}members AS ma ON (ma.id_member = al.attacker)
				LEFT JOIN {db_prefix}members AS md ON (md.id_member = al.defender)
				LEFT JOIN {db_prefix}army_members AS ama ON (ama.id_member = al.attacker)
				LEFT JOIN {db_prefix}army_members AS amd ON (amd.id_member = al.defender)
				LEFT JOIN {db_prefix}army_races AS ra ON (ra.race_id = ama.race_id)
				LEFT JOIN {db_prefix}army_races AS rd ON (rd.race_id = amd.race_id)
			WHERE al.id = {int:log_id}
			LIMIT 1',
			array(
				'log_id' => $log_id,
			)
		);

		$log = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (empty($log))
			fatal_lang_error('army_log_not_found', false);

		// Verify the current user is the attacker or defender, or has admin permission
		if ((int) $log['attacker'] !== $user_info['id'] && (int) $log['defender'] !== $user_info['id'] && !allowedTo('army_admin'))
			fatal_lang_error('army_log_no_access', false);

		// Load inventory snapshots for this log
		$log_inv_attacker = array();
		$log_inv_defender = array();

		$request = $smcFunc['db_query']('', '
			SELECT ali.*, ai.name AS item_name, ai.icon AS item_icon
			FROM {db_prefix}army_attack_logs_inv AS ali
				LEFT JOIN {db_prefix}army_items AS ai ON (
					ai.type = CASE ali.a_section WHEN {string:sec_a} THEN {string:type_a} WHEN {string:sec_d} THEN {string:type_d} ELSE {string:type_n} END
					AND ai.number = ali.a_number
				)
			WHERE ali.a_logid = {int:log_id}
			ORDER BY ali.a_section ASC, ali.a_number ASC',
			array(
				'log_id' => $log_id,
				'sec_a' => 'a',
				'sec_d' => 'd',
				'type_a' => 'a',
				'type_d' => 'd',
				'type_n' => 'n',
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$entry = array(
				'section' => $row['a_section'],
				'number' => (int) $row['a_number'],
				'member_id' => (int) $row['a_memberid'],
				'quantity' => army_format_number($row['a_quantity']),
				'quantity_raw' => (int) $row['a_quantity'],
				'used' => army_format_number($row['a_used']),
				'used_raw' => (int) $row['a_used'],
				'sab_total' => (int) $row['a_sab_total'],
				'original_strength' => (float) $row['a_original_strength'],
				'after_strength' => (float) $row['a_after_strength'],
				'spy_sabbed' => (int) $row['a_spy_sabbed'],
				'item_name' => $row['item_name'] ?? ($txt['army_unknown'] ?? 'Unknown'),
				'item_icon' => $row['item_icon'] ?? '',
			);

			if ((int) $row['a_memberid'] === (int) $log['attacker'])
				$log_inv_attacker[] = $entry;
			else
				$log_inv_defender[] = $entry;
		}

		$smcFunc['db_free_result']($request);

		// Determine winner: attacker wins if atk_damage > def_damage
		$attacker_wins = ((int) $log['atk_damage'] > (int) $log['def_damage']);

		// Build the detail context
		$context['army_log_detail'] = array(
			'id' => (int) $log['id'],
			'attack_time' => timeformat((int) $log['attack_time']),
			'attack_time_raw' => (int) $log['attack_time'],
			'attacker_wins' => $attacker_wins,
			'attacker_id' => (int) $log['attacker'],
			'attacker_name' => $log['attacker_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
			'attacker_race' => $log['attacker_race'] ?? '',
			'defender_id' => (int) $log['defender'],
			'defender_name' => $log['defender_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
			'defender_race' => $log['defender_race'] ?? '',
			'money_stolen' => army_format_number($log['money_stolen']),
			'money_stolen_raw' => (int) $log['money_stolen'],
			'turns_used' => (int) $log['turns_used'],
			'atk_damage' => army_format_number($log['atk_damage']),
			'atk_damage_raw' => (int) $log['atk_damage'],
			'def_damage' => army_format_number($log['def_damage']),
			'def_damage_raw' => (int) $log['def_damage'],
			'atk_kill' => army_format_number($log['atk_kill']),
			'atk_kill_raw' => (int) $log['atk_kill'],
			'def_kill' => army_format_number($log['def_kill']),
			'def_kill_raw' => (int) $log['def_kill'],
			'atk_army_gen' => army_format_number($log['atk_army_gen']),
			'atk_army_atk' => army_format_number($log['atk_army_atk']),
			'atk_army_mgen' => army_format_number($log['atk_army_mgen']),
			'atk_army_matk' => army_format_number($log['atk_army_matk']),
			'def_army_gen' => army_format_number($log['def_army_gen']),
			'def_army_def' => army_format_number($log['def_army_def']),
			'def_army_mgen' => army_format_number($log['def_army_mgen']),
			'def_army_mdef' => army_format_number($log['def_army_mdef']),
			'atk_spy_killed' => (int) $log['atk_spy_killed'],
			'atk_sen_killed' => (int) $log['atk_sen_killed'],
			'def_spy_killed' => (int) $log['def_spy_killed'],
			'def_sen_killed' => (int) $log['def_sen_killed'],
			'attacker_inventory' => $log_inv_attacker,
			'defender_inventory' => $log_inv_defender,
			'attacker_profile_url' => $scripturl . '?action=army;sa=profile;u=' . (int) $log['attacker'],
			'defender_profile_url' => $scripturl . '?action=army;sa=profile;u=' . (int) $log['defender'],
		);

		// Add to linktree
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=army;sa=attacklog',
			'name' => $txt['army_attack_log'] ?? 'Attack Log',
		);

		$context['linktree'][] = array(
			'url' => $scripturl . '?action=army;sa=attacklog;log=' . $log_id,
			'name' => ($txt['army_battle_detail'] ?? 'Battle') . ' #' . $log_id,
		);

		$context['sub_template'] = 'army_attack_detail';
		$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_battle_detail'] ?? 'Battle Detail') . ' #' . $log_id;

		return;
	}

	// -------------------------------------------------------
	// List view: paginated attack history
	// -------------------------------------------------------

	$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'all';

	if (!in_array($view, array('all', 'sent', 'received')))
		$view = 'all';

	// Build the WHERE clause based on the view filter
	switch ($view)
	{
		case 'sent':
			$where = 'al.attacker = {int:id_member}';
			break;

		case 'received':
			$where = 'al.defender = {int:id_member}';
			break;

		default: // 'all'
			$where = '(al.attacker = {int:id_member} OR al.defender = {int:id_member})';
			break;
	}

	$query_params = array(
		'id_member' => $user_info['id'],
	);

	// Count total matching logs
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}army_attack_logs AS al
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
		$scripturl . '?action=army;sa=attacklog;view=' . $view,
		$start,
		$total_logs,
		$per_page
	);
	$context['start'] = $start;

	// Query logs with member name joins
	$context['army_attack_logs'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT al.id, al.attack_time, al.money_stolen, al.turns_used,
			al.attacker, al.defender, al.atk_damage, al.def_damage,
			al.atk_kill, al.def_kill,
			ma.real_name AS attacker_name,
			md.real_name AS defender_name
		FROM {db_prefix}army_attack_logs AS al
			LEFT JOIN {db_prefix}members AS ma ON (ma.id_member = al.attacker)
			LEFT JOIN {db_prefix}members AS md ON (md.id_member = al.defender)
		WHERE ' . $where . '
		ORDER BY al.attack_time DESC
		LIMIT {int:start}, {int:per_page}',
		array_merge($query_params, array(
			'start' => $start,
			'per_page' => $per_page,
		))
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$atk_won = ((int) $row['atk_damage'] > (int) $row['def_damage']);

		$context['army_attack_logs'][] = array(
			'id' => (int) $row['id'],
			'attack_time' => timeformat((int) $row['attack_time']),
			'attack_time_raw' => (int) $row['attack_time'],
			'attacker_id' => (int) $row['attacker'],
			'attacker_name' => $row['attacker_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
			'defender_id' => (int) $row['defender'],
			'defender_name' => $row['defender_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
			'attacker_wins' => $atk_won,
			'money_stolen' => army_format_number($row['money_stolen']),
			'money_stolen_raw' => (int) $row['money_stolen'],
			'turns_used' => (int) $row['turns_used'],
			'atk_casualties' => army_format_number($row['atk_kill']),
			'def_casualties' => army_format_number($row['def_kill']),
			'is_attacker' => ((int) $row['attacker'] === $user_info['id']),
			'detail_url' => $scripturl . '?action=army;sa=attacklog;log=' . (int) $row['id'],
			'attacker_profile_url' => $scripturl . '?action=army;sa=profile;u=' . (int) $row['attacker'],
			'defender_profile_url' => $scripturl . '?action=army;sa=profile;u=' . (int) $row['defender'],
		);
	}

	$smcFunc['db_free_result']($request);

	// View filter tabs for the template
	$context['army_log_views'] = array(
		'all' => array(
			'label' => $txt['army_log_all'] ?? 'All Battles',
			'url' => $scripturl . '?action=army;sa=attacklog;view=all',
			'active' => ($view === 'all'),
		),
		'sent' => array(
			'label' => $txt['army_log_sent'] ?? 'Attacks Sent',
			'url' => $scripturl . '?action=army;sa=attacklog;view=sent',
			'active' => ($view === 'sent'),
		),
		'received' => array(
			'label' => $txt['army_log_received'] ?? 'Attacks Received',
			'url' => $scripturl . '?action=army;sa=attacklog;view=received',
			'active' => ($view === 'received'),
		),
	);

	$context['army_log_view'] = $view;
	$context['army_log_total'] = $total_logs;

	// Add to linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=army;sa=attacklog',
		'name' => $txt['army_attack_log'] ?? 'Attack Log',
	);

	$context['sub_template'] = 'army_attacklog';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_attack_log'] ?? 'Attack Log');
}

/**
 * Distribute battle casualties across soldier types.
 *
 * Removes casualties from untrained soldiers first. If more casualties
 * remain than untrained soldiers available, the excess is distributed
 * proportionally among the trained types (attack, defense, spy, sentry).
 *
 * @param array $member     Member data row with soldier counts
 * @param int   $casualties Total number of casualties to distribute
 * @return array Breakdown of casualties per type:
 *               'untrained', 'attack', 'defense', 'spy', 'sentry'
 */
function army_distribute_casualties($member, $casualties)
{
	$result = array(
		'untrained' => 0,
		'attack' => 0,
		'defense' => 0,
		'spy' => 0,
		'sentry' => 0,
	);

	$casualties = (int) $casualties;

	if ($casualties <= 0)
		return $result;

	$untrained = (int) $member['soldiers_untrained'];

	// Remove from untrained first
	if ($untrained >= $casualties)
	{
		$result['untrained'] = $casualties;
		return $result;
	}

	// All untrained soldiers are lost
	$result['untrained'] = $untrained;
	$remaining = $casualties - $untrained;

	// Distribute remaining casualties proportionally among trained types
	$trained = array(
		'attack' => (int) $member['soldiers_attack'],
		'defense' => (int) $member['soldiers_defense'],
		'spy' => (int) $member['soldiers_spy'],
		'sentry' => (int) $member['soldiers_sentry'],
	);

	$total_trained = array_sum($trained);

	if ($total_trained <= 0)
		return $result;

	// Cap remaining at total trained
	$remaining = min($remaining, $total_trained);

	// Distribute proportionally with rounding
	$distributed = 0;

	foreach ($trained as $type => $count)
	{
		if ($count <= 0)
			continue;

		$share = (int) floor($remaining * $count / $total_trained);
		$share = min($share, $count);
		$result[$type] = $share;
		$distributed += $share;
	}

	// Distribute any rounding remainder to the largest group
	$leftover = $remaining - $distributed;

	if ($leftover > 0)
	{
		// Sort by count descending to give leftovers to the largest pool
		arsort($trained);

		foreach ($trained as $type => $count)
		{
			if ($leftover <= 0)
				break;

			$can_lose = $count - $result[$type];

			if ($can_lose > 0)
			{
				$take = min($leftover, $can_lose);
				$result[$type] += $take;
				$leftover -= $take;
			}
		}
	}

	return $result;
}

/**
 * Log inventory snapshots for a battle to the attack_logs_inv table.
 *
 * Records the quantity and strength of each relevant equipment item
 * involved in the battle for later review in the attack log detail view.
 *
 * @param int    $log_id     The attack log ID to associate with
 * @param string $section    'a' for attacker items, 'd' for defender items
 * @param int    $member_id  The member ID whose inventory is being logged
 * @param array  $inventory  The member's inventory (from army_load_inventory)
 * @return void
 */
function army_log_battle_inventory($log_id, $section, $member_id, $inventory)
{
	global $smcFunc;

	$log_id = (int) $log_id;
	$member_id = (int) $member_id;

	if ($log_id <= 0 || $member_id <= 0 || empty($inventory))
		return;

	// Log relevant items: for 'a' section (attacker), log attack weapons
	// For 'd' section (defender), log defense armor
	foreach ($inventory as $key => $item)
	{
		$item_section = $item['i_section'];
		$item_qty = (int) $item['i_quantity'];

		// Only log items that match the relevant combat section
		// Attackers: log their weapons ('a')
		// Defenders: log their armor ('d')
		if ($item_section !== $section)
			continue;

		if ($item_qty <= 0)
			continue;

		$smcFunc['db_insert']('insert',
			'{db_prefix}army_attack_logs_inv',
			array(
				'a_section' => 'string',
				'a_number' => 'int',
				'a_memberid' => 'int',
				'a_logid' => 'int',
				'a_quantity' => 'int',
				'a_used' => 'int',
				'a_sab_total' => 'int',
				'a_original_strength' => 'float',
				'a_after_strength' => 'float',
				'a_spy_sabbed' => 'int',
			),
			array(
				$item_section,
				(int) $item['i_number'],
				$member_id,
				$log_id,
				$item_qty,
				$item_qty,  // a_used: all equipped items participate in battle
				(int) $item['i_spy_sabbed'],
				(float) $item['i_strength'],
				(float) max(0, $item['i_strength'] * (1 - 0.005)),  // post-degradation estimate
				(int) $item['i_spy_sabbed'],
			),
			array()
		);
	}
}
