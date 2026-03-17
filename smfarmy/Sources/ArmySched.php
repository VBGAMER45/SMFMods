<?php
/**
 * Army System - Scheduled Tasks
 *
 * Auto-gain production, money, and turns; mercenary upkeep costs;
 * inactive player detection. These functions are called by SMF's
 * built-in task scheduler at configured intervals.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Scheduled task: auto-gain production, money, and turns.
 *
 * Called periodically by SMF's task scheduler. Handles three independent
 * auto-gain systems that were part of the original Army System:
 *
 * 1. **Production gain** (auto_gain_prod): Awards untrained soldiers to
 *    all active members based on their unit production level. The formula
 *    depends on the production_type setting:
 *    - Type 0 (linear): base * (1 + unit_prod_level) * constant
 *    - Type 1 (exponential): base * pow(2, unit_prod_level)
 *
 * 2. **Money gain** (auto_gain_money): Awards gold to all active members
 *    based on their army size and race income bonus. Formula:
 *    army_size * money_amount * (100 + bonus_income) / 100
 *
 * 3. **Turn gain**: Awards attack turns to all active members, capped at
 *    the configured turns_max value.
 *
 * All three systems skip members who are inactive, have not chosen a race,
 * or are currently on vacation.
 *
 * @return bool True on success (SMF expects this)
 */
function scheduled_army_auto_gain()
{
	global $smcFunc, $sourcedir;

	require_once($sourcedir . '/ArmySystem-Subs.php');

	$settings = army_load_settings();

	// Check if the Army System is enabled
	if (empty($settings['army_enabled']))
		return true;

	$now = time();

	// ----------------------------------------------------------------
	// Production gain: award untrained soldiers based on unit_prod_level
	// ----------------------------------------------------------------
	if (!empty($settings['auto_gain_prod']))
	{
		$production_base = (int) ($settings['production_base'] ?? 2);
		$production_constant = (int) ($settings['production_constant'] ?? 1);
		$production_type = (int) ($settings['production_type'] ?? 1);

		if ($production_base > 0)
		{
			if ($production_type == 1)
			{
				// Exponential mode: base * pow(2, unit_prod_level)
				// SQL has no native pow() in all MySQL versions that returns int safely,
				// so we use: base * (1 << unit_prod_level) which is bit-shift for pow(2, n).
				// However, bit-shift in MySQL is limited to BIGINT (64-bit), which is fine
				// for unit_prod_level up to ~30 (the max defined levels).
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_members
					SET soldiers_untrained = soldiers_untrained + ({int:base} * (1 << unit_prod_level)),
						army_size = army_size + ({int:base} * (1 << unit_prod_level))
					WHERE is_active = {int:active}
						AND race_id > {int:zero}
						AND (vacation_end = {int:zero} OR vacation_end < {int:time})',
					array(
						'base' => $production_base,
						'active' => 1,
						'zero' => 0,
						'time' => $now,
					)
				);
			}
			else
			{
				// Linear mode: base * (1 + unit_prod_level) * constant
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_members
					SET soldiers_untrained = soldiers_untrained + ({int:base} * (1 + unit_prod_level) * {int:constant}),
						army_size = army_size + ({int:base} * (1 + unit_prod_level) * {int:constant})
					WHERE is_active = {int:active}
						AND race_id > {int:zero}
						AND (vacation_end = {int:zero} OR vacation_end < {int:time})',
					array(
						'base' => $production_base,
						'constant' => $production_constant,
						'active' => 1,
						'zero' => 0,
						'time' => $now,
					)
				);
			}
		}
	}

	// ----------------------------------------------------------------
	// Money gain: award gold based on army_size and race income bonus
	// ----------------------------------------------------------------
	if (!empty($settings['auto_gain_money']))
	{
		$money_amount = (int) ($settings['money_amount'] ?? 5);

		if ($money_amount > 0)
		{
			// JOIN with army_races to apply the race income bonus.
			// Formula: army_size * money_amount * (100 + bonus_income) / 100
			// GREATEST(0, ...) prevents negative gold gain from large negative bonuses.
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}army_members AS am
					INNER JOIN {db_prefix}army_races AS ar ON (am.race_id = ar.race_id)
				SET am.army_points = am.army_points + GREATEST(0, am.army_size * {int:money_amount} * (100 + ar.bonus_income) / 100)
				WHERE am.is_active = {int:active}
					AND am.race_id > {int:zero}
					AND (am.vacation_end = {int:zero} OR am.vacation_end < {int:time})',
				array(
					'money_amount' => $money_amount,
					'active' => 1,
					'zero' => 0,
					'time' => $now,
				)
			);
		}
	}

	// ----------------------------------------------------------------
	// Turn gain: add attack turns, capped at turns_max
	// ----------------------------------------------------------------
	$turn_gain = (int) ($settings['turn_gain'] ?? 1);
	$turns_max = (int) ($settings['turns_max'] ?? 15);

	if ($turn_gain > 0 && $turns_max > 0)
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET attack_turns = LEAST(attack_turns + {int:turn_gain}, {int:turns_max})
			WHERE is_active = {int:active}
				AND race_id > {int:zero}
				AND (vacation_end = {int:zero} OR vacation_end < {int:time})',
			array(
				'turn_gain' => $turn_gain,
				'turns_max' => $turns_max,
				'active' => 1,
				'zero' => 0,
				'time' => $now,
			)
		);
	}

	return true;
}

/**
 * Scheduled task: mercenary upkeep costs.
 *
 * Deducts gold from active members to pay for their mercenaries.
 * Each mercenary (attack, defense, or untrained) costs a flat rate
 * per upkeep cycle, configured by the 'money_mercanery' setting.
 *
 * The upkeep cost for a member is:
 *   (mercs_attack + mercs_defense + mercs_untrained) * upkeep_rate
 *
 * Processing is done in two passes:
 *
 * 1. **Affordable pass**: Members who can afford the full upkeep have
 *    the cost deducted from their gold in a single bulk UPDATE.
 *
 * 2. **Broke pass**: Members who cannot afford the full upkeep lose
 *    all their mercenaries and their gold is set to zero. This is the
 *    simplest fair penalty -- rather than partial dismissal, which would
 *    require per-member iteration to decide which mercs to fire.
 *
 * Members on vacation or who have no mercenaries are not charged.
 *
 * @return bool True on success (SMF expects this)
 */
function scheduled_army_merc_upkeep()
{
	global $smcFunc, $sourcedir;

	require_once($sourcedir . '/ArmySystem-Subs.php');

	$settings = army_load_settings();

	// Check if the Army System is enabled
	if (empty($settings['army_enabled']))
		return true;

	$upkeep_rate = (int) ($settings['money_mercanery'] ?? 5);

	// If upkeep is zero or negative, no cost to maintain mercs
	if ($upkeep_rate <= 0)
		return true;

	$now = time();

	// Common WHERE conditions for active, non-vacation members with mercs
	// Pass 1: Deduct gold from members who can afford the upkeep
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}army_members
		SET army_points = army_points - ((mercs_attack + mercs_defense + mercs_untrained) * {int:rate})
		WHERE is_active = {int:active}
			AND race_id > {int:zero}
			AND (mercs_attack + mercs_defense + mercs_untrained) > {int:zero}
			AND army_points >= ((mercs_attack + mercs_defense + mercs_untrained) * {int:rate})
			AND (vacation_end = {int:zero} OR vacation_end < {int:time})',
		array(
			'rate' => $upkeep_rate,
			'active' => 1,
			'zero' => 0,
			'time' => $now,
		)
	);

	// Pass 2: Members who cannot afford upkeep lose all mercs and remaining gold
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}army_members
		SET mercs_attack = {int:zero},
			mercs_defense = {int:zero},
			mercs_untrained = {int:zero},
			army_points = {int:zero}
		WHERE is_active = {int:active}
			AND race_id > {int:zero}
			AND (mercs_attack + mercs_defense + mercs_untrained) > {int:zero}
			AND army_points < ((mercs_attack + mercs_defense + mercs_untrained) * {int:rate})
			AND (vacation_end = {int:zero} OR vacation_end < {int:time})',
		array(
			'rate' => $upkeep_rate,
			'active' => 1,
			'zero' => 0,
			'time' => $now,
		)
	);

	return true;
}

/**
 * Scheduled task: mark inactive players.
 *
 * Sets is_active = 0 for members whose last_active timestamp is older
 * than the configured inactivity threshold. Inactive members are excluded
 * from auto-gain rewards, rankings, and cannot be attacked.
 *
 * The inactivity threshold is calculated as:
 *   inactive_time * inactive_time_unit (in seconds)
 *
 * For example, with inactive_time=14 and inactive_time_unit=86400 (one day),
 * members who have not accessed the Army System in 14 days are marked inactive.
 *
 * If inactive_time is set to 0, this feature is disabled and no members
 * will be deactivated.
 *
 * @return bool True on success (SMF expects this)
 */
function scheduled_army_inactive_check()
{
	global $smcFunc, $sourcedir;

	require_once($sourcedir . '/ArmySystem-Subs.php');

	$settings = army_load_settings();

	// Check if the Army System is enabled
	if (empty($settings['army_enabled']))
		return true;

	$inactive_time = (int) ($settings['inactive_time'] ?? 14);
	$inactive_time_unit = (int) ($settings['inactive_time_unit'] ?? 86400);

	// Feature disabled if inactive_time is zero or negative
	if ($inactive_time <= 0)
		return true;

	// Calculate the cutoff timestamp
	$cutoff = time() - ($inactive_time * $inactive_time_unit);

	// Mark members as inactive if they have not accessed the Army System
	// since before the cutoff. Only process members who are currently active,
	// have chosen a race, and have a recorded last_active timestamp.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}army_members
		SET is_active = {int:inactive}
		WHERE is_active = {int:active}
			AND race_id > {int:zero}
			AND last_active > {int:zero}
			AND last_active < {int:cutoff}',
		array(
			'inactive' => 0,
			'active' => 1,
			'zero' => 0,
			'cutoff' => $cutoff,
		)
	);

	return true;
}
