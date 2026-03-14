<?php
/**
 * Army System - Race Selection & Reset
 *
 * Handles race picking for new and returning players, race reset
 * (clearing all progress and inventory), and leaving the Army System
 * entirely.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Race selection page and processing.
 *
 * Displays all available races with their bonuses and allows the player
 * to choose one. New players (no army_members row) are automatically
 * enrolled via army_ensure_member(). Returning players (race_id=0 after
 * a reset) have their starting resources restored from settings.
 *
 * Players who already have a race are shown an error and must reset first.
 *
 * POST parameters:
 *   race_id - The race_id from army_races to select
 *
 * @return void
 */
function ArmyRace()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_play');

	// Load shared helpers and ensure settings are available
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	$settings = $modSettings['army'];

	// Load all available races
	$races = army_load_races();

	// Load current member data (may be false if brand new player)
	$member = army_load_member($user_info['id']);

	// Handle POST: player is picking a race
	if (isset($_POST['race_id']))
	{
		checkSession();

		$race_id = (int) $_POST['race_id'];

		// Validate that the chosen race exists
		if (!isset($races[$race_id]))
			fatal_lang_error('army_race_invalid', false);

		// If the member already has a race, they must reset first
		if ($member !== false && !empty($member['race_id']))
			fatal_lang_error('army_race_already_chosen', false);

		// Default starting values from settings
		$reset_army = isset($settings['reset_army']) ? (int) $settings['reset_army'] : 10;
		$reset_turn = isset($settings['reset_turn']) ? (int) $settings['reset_turn'] : 25;
		$reset_money = isset($settings['reset_money']) ? (int) $settings['reset_money'] : 50000;

		if ($member === false)
		{
			// Brand new player: create the army_members row with defaults
			army_ensure_member($user_info['id']);

			// Now update the race_id on the freshly-created row
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}army_members
				SET race_id = {int:race_id},
					last_active = {int:now}
				WHERE id_member = {int:id_member}',
				array(
					'race_id' => $race_id,
					'now' => time(),
					'id_member' => $user_info['id'],
				)
			);
		}
		else
		{
			// Existing player with race_id=0 (after a reset): restore defaults and set race
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}army_members
				SET race_id = {int:race_id},
					soldiers_untrained = {int:reset_army},
					army_size = {int:reset_army},
					attack_turns = {int:reset_turn},
					army_points = {int:reset_money},
					is_active = {int:active},
					last_active = {int:now}
				WHERE id_member = {int:id_member}',
				array(
					'race_id' => $race_id,
					'reset_army' => $reset_army,
					'reset_turn' => $reset_turn,
					'reset_money' => $reset_money,
					'active' => 1,
					'now' => time(),
					'id_member' => $user_info['id'],
				)
			);
		}

		// Log an event: player picked a race
		$race_name = $races[$race_id]['name'];
		army_log_event(
			$user_info['id'],
			0,
			2,
			'<% FROM %> joined the ' . $race_name . ' race'
		);

		// Redirect to the main dashboard
		redirectexit('action=army');
		return;
	}

	// Build the race list for the template, including bonus formatting
	$context['army_races'] = array();

	foreach ($races as $race_id => $race)
	{
		$bonuses = array();

		if (!empty($race['bonus_income']))
			$bonuses[] = array('stat' => $txt['army_bonus_income'] ?? 'Income', 'value' => (int) $race['bonus_income']);
		if (!empty($race['bonus_discount']))
			$bonuses[] = array('stat' => $txt['army_bonus_discount'] ?? 'Discount', 'value' => (int) $race['bonus_discount']);
		if (!empty($race['bonus_casualties']))
			$bonuses[] = array('stat' => $txt['army_bonus_casualties'] ?? 'Casualties', 'value' => (int) $race['bonus_casualties']);
		if (!empty($race['bonus_attack']))
			$bonuses[] = array('stat' => $txt['army_bonus_attack'] ?? 'Attack', 'value' => (int) $race['bonus_attack']);
		if (!empty($race['bonus_defence']))
			$bonuses[] = array('stat' => $txt['army_bonus_defence'] ?? 'Defence', 'value' => (int) $race['bonus_defence']);
		if (!empty($race['bonus_spy']))
			$bonuses[] = array('stat' => $txt['army_bonus_spy'] ?? 'Spy', 'value' => (int) $race['bonus_spy']);
		if (!empty($race['bonus_sentry']))
			$bonuses[] = array('stat' => $txt['army_bonus_sentry'] ?? 'Sentry', 'value' => (int) $race['bonus_sentry']);

		$context['army_races'][$race_id] = array(
			'id' => (int) $race['race_id'],
			'name' => $race['name'],
			'icon' => $race['default_icon'],
			'bonuses' => $bonuses,
			'bonus_income' => (int) $race['bonus_income'],
			'bonus_discount' => (int) $race['bonus_discount'],
			'bonus_casualties' => (int) $race['bonus_casualties'],
			'bonus_attack' => (int) $race['bonus_attack'],
			'bonus_defence' => (int) $race['bonus_defence'],
			'bonus_spy' => (int) $race['bonus_spy'],
			'bonus_sentry' => (int) $race['bonus_sentry'],
		);
	}

	// Member context (null if new player)
	$context['army_member'] = ($member !== false) ? array(
		'id' => (int) $member['id_member'],
		'race_id' => (int) $member['race_id'],
		'race_name' => $member['race_name'] ?? '',
		'has_race' => !empty($member['race_id']),
	) : null;

	// Session tokens for the form
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	// Load the template and set sub-template
	loadTemplate('ArmySystem-Race');
	$context['sub_template'] = 'army_race';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_race_title'] ?? 'Choose Your Race');
}

/**
 * Reset race or leave the Army System entirely.
 *
 * Provides two options:
 * - 'reset': Wipes all progress (inventory, soldiers, mercs, upgrades, clan
 *   membership) and sets race_id to 0, allowing the player to pick a new race
 *   and start over. Preserves the member row and lifetime stats (total_attacks,
 *   total_defends).
 * - 'leave': Same as reset but also sets is_active=0, effectively removing
 *   the player from the Army System. They can rejoin later by picking a race.
 *
 * POST parameters:
 *   reset_action - Either 'reset' or 'leave'
 *
 * @return void
 */
function ArmyReset()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_play');

	// Load shared helpers and ensure settings are available
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	// Load member data; must have a race to reset
	$member = army_load_member($user_info['id']);

	if ($member === false || empty($member['race_id']))
	{
		redirectexit('action=army;sa=race');
		return;
	}

	// Handle POST: process the reset or leave action
	if (isset($_POST['reset_action']))
	{
		checkSession();

		$action = $_POST['reset_action'];

		// Validate the action is one of the allowed values
		if ($action !== 'reset' && $action !== 'leave')
			fatal_lang_error('army_reset_invalid_action', false);

		// Delete all inventory for this member
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_inventory
			WHERE i_member = {int:id_member}',
			array(
				'id_member' => $user_info['id'],
			)
		);

		// Remove from clan membership
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_clan_members
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => $user_info['id'],
			)
		);

		// Remove any pending clan invitations/applications
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}army_clan_pending
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => $user_info['id'],
			)
		);

		// Determine is_active based on action type
		$is_active = ($action === 'leave') ? 0 : 1;

		// Reset the army_members row: clear all progress, keep lifetime stats
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
				is_active = {int:is_active},
				last_active = {int:now}
			WHERE id_member = {int:id_member}',
			array(
				'zero' => 0,
				'one' => 1,
				'is_active' => $is_active,
				'now' => time(),
				'id_member' => $user_info['id'],
			)
		);

		// Redirect based on action type
		if ($action === 'leave')
		{
			redirectexit();
			return;
		}

		// Reset: send back to race picker with a success indicator
		redirectexit('action=army;sa=race;reset=1');
		return;
	}

	// Display: build context for the confirmation form
	$context['army_member'] = array(
		'id' => (int) $member['id_member'],
		'race_id' => (int) $member['race_id'],
		'race_name' => $member['race_name'] ?? '',
		'army_size' => army_format_number($member['army_size']),
		'army_size_raw' => (int) $member['army_size'],
		'army_points' => army_format_number($member['army_points']),
		'army_points_raw' => (int) $member['army_points'],
		'total_attacks' => army_format_number($member['total_attacks']),
		'total_defends' => army_format_number($member['total_defends']),
	);

	// Flag for showing "reset successful" message on the race page
	$context['army_reset_success'] = isset($_REQUEST['reset']) && $_REQUEST['reset'] == 1;

	// Session tokens for the form
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	// Load the template and set sub-template
	loadTemplate('ArmySystem-Race');
	$context['sub_template'] = 'army_reset';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_reset_title'] ?? 'Reset / Leave');
}
