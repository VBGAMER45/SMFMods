<?php
/**
 * Army System - Mercenary Hiring
 *
 * Hire, fire, and retrain mercenaries. Mercenaries provide combat
 * strength without consuming soldiers from the army pool, but cost
 * gold to acquire and periodic upkeep (handled in scheduled tasks).
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Mercenary page and POST handler.
 *
 * Displays the player's current mercenary counts (attack, defense,
 * untrained) and allows hiring new mercenaries, firing existing ones,
 * or retraining untrained mercenaries into attack or defense roles.
 *
 * Mercenary types and their defaults from the items table:
 * - 'ma' (Attack merc):    10 strength, 3,500 gold per hire
 * - 'md' (Defense merc):   10 strength, 3,500 gold per hire
 * - 'mu' (Untrained merc): 25 strength, 3,000 gold per hire
 *
 * Retraining an untrained mercenary to attack or defense costs the
 * respective specialization price (ma or md), not the untrained price.
 *
 * Firing mercenaries returns no gold (no refund).
 *
 * POST parameters:
 *   merc_action - One of: hire_attack, hire_defense, hire_untrained,
 *                 fire_attack, fire_defense, fire_untrained,
 *                 train_merc_attack, train_merc_defense
 *   merc_count  - Number of mercenaries to hire/fire/retrain
 *
 * @return void
 */
function ArmyMercenaries()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_play');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	// Load member data
	$member = army_load_member($user_info['id']);

	// Must have a race to use mercenaries
	if ($member === false || empty($member['race_id']))
	{
		redirectexit('action=army;sa=race');
		return;
	}

	// Cannot manage mercenaries while on vacation
	if (army_check_vacation($member))
		fatal_lang_error('army_on_vacation', false);

	// Load mercenary item definitions from the items table
	$ma_items = army_load_items('ma');
	$md_items = army_load_items('md');
	$mu_items = army_load_items('mu');

	// Extract prices and strength values (each type has a single entry at number 1)
	$ma_price = isset($ma_items[1]) ? (int) $ma_items[1]['price'] : 3500;
	$md_price = isset($md_items[1]) ? (int) $md_items[1]['price'] : 3500;
	$mu_price = isset($mu_items[1]) ? (int) $mu_items[1]['price'] : 3000;

	$ma_strength = isset($ma_items[1]) ? (int) $ma_items[1]['value'] : 10;
	$md_strength = isset($md_items[1]) ? (int) $md_items[1]['value'] : 10;
	$mu_strength = isset($mu_items[1]) ? (int) $mu_items[1]['value'] : 25;

	// Handle POST: process mercenary actions
	if (isset($_POST['merc_action']))
	{
		checkSession();

		$action = $_POST['merc_action'];
		$count = max(0, (int) ($_POST['merc_count'] ?? 0));

		if ($count <= 0)
			fatal_lang_error('army_merc_invalid_count', false);

		// Reload member for up-to-date data
		$member = army_load_member($user_info['id']);
		$gold = (int) $member['army_points'];

		switch ($action)
		{
			// Hire new mercenaries (costs gold, adds to count)
			case 'hire_attack':
				$total_cost = $count * $ma_price;

				if ($total_cost > $gold)
					fatal_lang_error('army_not_enough_gold', false);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_members
					SET army_points = army_points - {int:cost},
						mercs_attack = mercs_attack + {int:count},
						last_active = {int:now}
					WHERE id_member = {int:id_member}',
					array(
						'cost' => $total_cost,
						'count' => $count,
						'now' => time(),
						'id_member' => $user_info['id'],
					)
				);
				break;

			case 'hire_defense':
				$total_cost = $count * $md_price;

				if ($total_cost > $gold)
					fatal_lang_error('army_not_enough_gold', false);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_members
					SET army_points = army_points - {int:cost},
						mercs_defense = mercs_defense + {int:count},
						last_active = {int:now}
					WHERE id_member = {int:id_member}',
					array(
						'cost' => $total_cost,
						'count' => $count,
						'now' => time(),
						'id_member' => $user_info['id'],
					)
				);
				break;

			case 'hire_untrained':
				$total_cost = $count * $mu_price;

				if ($total_cost > $gold)
					fatal_lang_error('army_not_enough_gold', false);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_members
					SET army_points = army_points - {int:cost},
						mercs_untrained = mercs_untrained + {int:count},
						last_active = {int:now}
					WHERE id_member = {int:id_member}',
					array(
						'cost' => $total_cost,
						'count' => $count,
						'now' => time(),
						'id_member' => $user_info['id'],
					)
				);
				break;

			// Fire mercenaries (no refund, just reduce count)
			case 'fire_attack':
				$available = (int) $member['mercs_attack'];

				if ($count > $available)
					fatal_lang_error('army_merc_not_enough', false);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_members
					SET mercs_attack = mercs_attack - {int:count},
						last_active = {int:now}
					WHERE id_member = {int:id_member}',
					array(
						'count' => $count,
						'now' => time(),
						'id_member' => $user_info['id'],
					)
				);
				break;

			case 'fire_defense':
				$available = (int) $member['mercs_defense'];

				if ($count > $available)
					fatal_lang_error('army_merc_not_enough', false);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_members
					SET mercs_defense = mercs_defense - {int:count},
						last_active = {int:now}
					WHERE id_member = {int:id_member}',
					array(
						'count' => $count,
						'now' => time(),
						'id_member' => $user_info['id'],
					)
				);
				break;

			case 'fire_untrained':
				$available = (int) $member['mercs_untrained'];

				if ($count > $available)
					fatal_lang_error('army_merc_not_enough', false);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_members
					SET mercs_untrained = mercs_untrained - {int:count},
						last_active = {int:now}
					WHERE id_member = {int:id_member}',
					array(
						'count' => $count,
						'now' => time(),
						'id_member' => $user_info['id'],
					)
				);
				break;

			// Retrain untrained mercs into attack or defense roles
			case 'train_merc_attack':
				$available = (int) $member['mercs_untrained'];

				if ($count > $available)
					fatal_lang_error('army_merc_not_enough', false);

				$total_cost = $count * $ma_price;

				if ($total_cost > $gold)
					fatal_lang_error('army_not_enough_gold', false);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_members
					SET army_points = army_points - {int:cost},
						mercs_untrained = mercs_untrained - {int:count},
						mercs_attack = mercs_attack + {int:count},
						last_active = {int:now}
					WHERE id_member = {int:id_member}',
					array(
						'cost' => $total_cost,
						'count' => $count,
						'now' => time(),
						'id_member' => $user_info['id'],
					)
				);
				break;

			case 'train_merc_defense':
				$available = (int) $member['mercs_untrained'];

				if ($count > $available)
					fatal_lang_error('army_merc_not_enough', false);

				$total_cost = $count * $md_price;

				if ($total_cost > $gold)
					fatal_lang_error('army_not_enough_gold', false);

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_members
					SET army_points = army_points - {int:cost},
						mercs_untrained = mercs_untrained - {int:count},
						mercs_defense = mercs_defense + {int:count},
						last_active = {int:now}
					WHERE id_member = {int:id_member}',
					array(
						'cost' => $total_cost,
						'count' => $count,
						'now' => time(),
						'id_member' => $user_info['id'],
					)
				);
				break;

			default:
				fatal_lang_error('army_merc_invalid_action', false);
		}

		// Redirect with success
		redirectexit('action=army;sa=mercs;done=1');
		return;
	}

	// Display: reload member data for fresh view
	$member = army_load_member($user_info['id']);

	// Build member context for the template
	$context['army_member'] = array(
		'id' => (int) $member['id_member'],
		'army_points' => army_format_number($member['army_points']),
		'army_points_raw' => (int) $member['army_points'],
		'mercs_attack' => (int) $member['mercs_attack'],
		'mercs_attack_formatted' => army_format_number($member['mercs_attack']),
		'mercs_defense' => (int) $member['mercs_defense'],
		'mercs_defense_formatted' => army_format_number($member['mercs_defense']),
		'mercs_untrained' => (int) $member['mercs_untrained'],
		'mercs_untrained_formatted' => army_format_number($member['mercs_untrained']),
		'race_name' => $member['race_name'] ?? '',
		'merc_icon' => $member['merc_icon'] ?? '',
		'merc_atk_icon' => $member['merc_atk_icon'] ?? '',
		'merc_def_icon' => $member['merc_def_icon'] ?? '',
	);

	// Mercenary prices for the template
	$context['army_merc_prices'] = array(
		'hire_attack' => array(
			'price' => $ma_price,
			'price_formatted' => army_format_number($ma_price),
			'label' => $txt['army_merc_hire_attack'] ?? 'Hire Attack Merc',
		),
		'hire_defense' => array(
			'price' => $md_price,
			'price_formatted' => army_format_number($md_price),
			'label' => $txt['army_merc_hire_defense'] ?? 'Hire Defense Merc',
		),
		'hire_untrained' => array(
			'price' => $mu_price,
			'price_formatted' => army_format_number($mu_price),
			'label' => $txt['army_merc_hire_untrained'] ?? 'Hire Untrained Merc',
		),
		'train_merc_attack' => array(
			'price' => $ma_price,
			'price_formatted' => army_format_number($ma_price),
			'label' => $txt['army_merc_train_attack'] ?? 'Train Merc to Attack',
		),
		'train_merc_defense' => array(
			'price' => $md_price,
			'price_formatted' => army_format_number($md_price),
			'label' => $txt['army_merc_train_defense'] ?? 'Train Merc to Defense',
		),
	);

	// Mercenary strength values for the template
	$context['army_merc_strength'] = array(
		'attack' => $ma_strength,
		'defense' => $md_strength,
		'untrained' => $mu_strength,
	);

	// Current gold for display
	$context['army_gold'] = army_format_number($member['army_points']);
	$context['army_gold_raw'] = (int) $member['army_points'];

	// Success flag from redirect
	$context['army_merc_success'] = isset($_REQUEST['done']) && $_REQUEST['done'] == 1;

	// Session tokens for the forms
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	// Load the template
	loadTemplate('ArmySystem-Mercenaries');
	$context['sub_template'] = 'army_mercenaries';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_mercs_title'] ?? 'Mercenaries');
}
