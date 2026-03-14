<?php
/**
 * Army System - Soldier Training
 *
 * Train/untrain soldiers between types (attack, defense, spy, sentry)
 * and upgrade unit production level.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Training page and POST handler.
 *
 * Displays the player's current soldier distribution across all types
 * (attack, defense, spy, sentry, untrained) and allows training or
 * untraining soldiers between categories. Also provides unit production
 * level upgrades which increase the rate of automatic soldier generation.
 *
 * Training costs gold per unit and moves soldiers from the untrained
 * pool into a specialized role. Untraining moves soldiers back to the
 * untrained pool at a lower cost. Training mercenaries is handled
 * separately in ArmySystem-Mercenaries.php.
 *
 * POST parameters:
 *   train_action - One of: train_attack, train_defense, train_spy,
 *                  train_sentry, untrain_attack, untrain_defense,
 *                  untrain_spy, untrain_sentry, upgrade_production
 *   train_count  - Number of soldiers to train/untrain (ignored for
 *                  upgrade_production)
 *
 * @return void
 */
function ArmyTraining()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_play');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	// Load member data
	$member = army_load_member($user_info['id']);

	// Must have a race to train soldiers
	if ($member === false || empty($member['race_id']))
	{
		redirectexit('action=army;sa=race');
		return;
	}

	// Cannot train while on vacation
	if (army_check_vacation($member))
		fatal_lang_error('army_on_vacation', false);

	// Load training price items from the items table
	$ta_items = army_load_items('ta');
	$td_items = army_load_items('td');
	$ts_items = army_load_items('ts');
	$tw_items = army_load_items('tw');
	$tu_items = army_load_items('tu');

	// Extract prices (each type has a single entry at number 1)
	$ta_price = isset($ta_items[1]) ? (int) $ta_items[1]['price'] : 2500;
	$td_price = isset($td_items[1]) ? (int) $td_items[1]['price'] : 2500;
	$ts_price = isset($ts_items[1]) ? (int) $ts_items[1]['price'] : 3500;
	$tw_price = isset($tw_items[1]) ? (int) $tw_items[1]['price'] : 3500;
	$tu_price = isset($tu_items[1]) ? (int) $tu_items[1]['price'] : 500;

	// Handle POST: process training actions
	if (isset($_POST['train_action']))
	{
		checkSession();

		$action = $_POST['train_action'];
		$count = max(0, (int) ($_POST['train_count'] ?? 0));

		// Reload member for up-to-date data
		$member = army_load_member($user_info['id']);
		$gold = (int) $member['army_points'];

		// Unit production upgrade does not need a count
		if ($action === 'upgrade_production')
		{
			// Load unit production items
			$prod_items = army_load_items('up');

			$current_level = (int) $member['unit_prod_level'];
			$next_level = $current_level + 1;

			// Validate that next level exists
			if (!isset($prod_items[$next_level]))
				fatal_lang_error('army_prod_max_level', false);

			$upgrade_cost = (int) $prod_items[$next_level]['price'];

			// Check gold
			if ($gold < $upgrade_cost)
				fatal_lang_error('army_not_enough_gold', false);

			// Deduct gold and increment unit_prod_level
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}army_members
				SET army_points = army_points - {int:cost},
					unit_prod_level = {int:next_level},
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
			redirectexit('action=army;sa=training;upgraded=1');
			return;
		}

		// All other actions require a positive count
		if ($count <= 0)
			fatal_lang_error('army_train_invalid_count', false);

		// Define the training action map: action => array(source_field, dest_field, price, direction)
		// direction: 'train' means untrained -> specialized, 'untrain' means specialized -> untrained
		$action_map = array(
			'train_attack' => array(
				'source' => 'soldiers_untrained',
				'dest' => 'soldiers_attack',
				'price' => $ta_price,
			),
			'train_defense' => array(
				'source' => 'soldiers_untrained',
				'dest' => 'soldiers_defense',
				'price' => $td_price,
			),
			'train_spy' => array(
				'source' => 'soldiers_untrained',
				'dest' => 'soldiers_spy',
				'price' => $ts_price,
			),
			'train_sentry' => array(
				'source' => 'soldiers_untrained',
				'dest' => 'soldiers_sentry',
				'price' => $tw_price,
			),
			'untrain_attack' => array(
				'source' => 'soldiers_attack',
				'dest' => 'soldiers_untrained',
				'price' => $tu_price,
			),
			'untrain_defense' => array(
				'source' => 'soldiers_defense',
				'dest' => 'soldiers_untrained',
				'price' => $tu_price,
			),
			'untrain_spy' => array(
				'source' => 'soldiers_spy',
				'dest' => 'soldiers_untrained',
				'price' => $tu_price,
			),
			'untrain_sentry' => array(
				'source' => 'soldiers_sentry',
				'dest' => 'soldiers_untrained',
				'price' => $tu_price,
			),
		);

		// Validate the action
		if (!isset($action_map[$action]))
			fatal_lang_error('army_train_invalid_action', false);

		$train = $action_map[$action];

		// Check source pool has enough soldiers
		$available = (int) $member[$train['source']];

		if ($count > $available)
			fatal_lang_error('army_train_not_enough_soldiers', false);

		// Calculate cost
		$total_cost = $count * $train['price'];

		// Check gold
		if ($total_cost > $gold)
			fatal_lang_error('army_not_enough_gold', false);

		// Execute: deduct gold, move soldiers from source to destination
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET army_points = army_points - {int:cost},
				' . $train['source'] . ' = ' . $train['source'] . ' - {int:count},
				' . $train['dest'] . ' = ' . $train['dest'] . ' + {int:count},
				last_active = {int:now}
			WHERE id_member = {int:id_member}',
			array(
				'cost' => $total_cost,
				'count' => $count,
				'now' => time(),
				'id_member' => $user_info['id'],
			)
		);

		// Redirect with success
		redirectexit('action=army;sa=training;trained=1');
		return;
	}

	// Display: reload member data (in case of fresh page load after redirect)
	$member = army_load_member($user_info['id']);

	// Build member context for the template
	$context['army_member'] = array(
		'id' => (int) $member['id_member'],
		'army_points' => army_format_number($member['army_points']),
		'army_points_raw' => (int) $member['army_points'],
		'army_size' => army_format_number($member['army_size']),
		'army_size_raw' => (int) $member['army_size'],
		'soldiers_attack' => (int) $member['soldiers_attack'],
		'soldiers_attack_formatted' => army_format_number($member['soldiers_attack']),
		'soldiers_defense' => (int) $member['soldiers_defense'],
		'soldiers_defense_formatted' => army_format_number($member['soldiers_defense']),
		'soldiers_spy' => (int) $member['soldiers_spy'],
		'soldiers_spy_formatted' => army_format_number($member['soldiers_spy']),
		'soldiers_sentry' => (int) $member['soldiers_sentry'],
		'soldiers_sentry_formatted' => army_format_number($member['soldiers_sentry']),
		'soldiers_untrained' => (int) $member['soldiers_untrained'],
		'soldiers_untrained_formatted' => army_format_number($member['soldiers_untrained']),
		'unit_prod_level' => (int) $member['unit_prod_level'],
		'race_name' => $member['race_name'] ?? '',
		'train_atk_icon' => $member['train_atk_icon'] ?? '',
		'train_def_icon' => $member['train_def_icon'] ?? '',
		'spy_icon' => $member['spy_icon'] ?? '',
		'sentry_icon' => $member['sentry_icon'] ?? '',
	);

	// Training prices for the template
	$context['army_train_prices'] = array(
		'train_attack' => array(
			'price' => $ta_price,
			'price_formatted' => army_format_number($ta_price),
			'label' => $txt['army_train_attack'] ?? 'Train Attack',
		),
		'train_defense' => array(
			'price' => $td_price,
			'price_formatted' => army_format_number($td_price),
			'label' => $txt['army_train_defense'] ?? 'Train Defense',
		),
		'train_spy' => array(
			'price' => $ts_price,
			'price_formatted' => army_format_number($ts_price),
			'label' => $txt['army_train_spy'] ?? 'Train Spy',
		),
		'train_sentry' => array(
			'price' => $tw_price,
			'price_formatted' => army_format_number($tw_price),
			'label' => $txt['army_train_sentry'] ?? 'Train Sentry',
		),
		'untrain' => array(
			'price' => $tu_price,
			'price_formatted' => army_format_number($tu_price),
			'label' => $txt['army_untrain'] ?? 'Untrain',
		),
	);

	// Unit production upgrade information
	$prod_items = army_load_items('up');
	$current_prod_level = (int) $member['unit_prod_level'];
	$next_prod_level = $current_prod_level + 1;

	$context['army_prod_current'] = null;

	if (isset($prod_items[$current_prod_level]))
	{
		$context['army_prod_current'] = array(
			'level' => $current_prod_level,
			'name' => $prod_items[$current_prod_level]['name'],
		);
	}
	elseif ($current_prod_level === 0)
	{
		// Level 0 means no production upgrades yet
		$context['army_prod_current'] = array(
			'level' => 0,
			'name' => $txt['army_prod_none'] ?? 'None',
		);
	}

	$context['army_prod_next'] = null;

	if (isset($prod_items[$next_prod_level]))
	{
		$context['army_prod_next'] = array(
			'level' => $next_prod_level,
			'name' => $prod_items[$next_prod_level]['name'],
			'price' => (int) $prod_items[$next_prod_level]['price'],
			'price_formatted' => army_format_number($prod_items[$next_prod_level]['price']),
		);
	}

	// Current gold for display
	$context['army_gold'] = army_format_number($member['army_points']);
	$context['army_gold_raw'] = (int) $member['army_points'];

	// Success flags from redirects
	$context['army_train_success'] = isset($_REQUEST['trained']) && $_REQUEST['trained'] == 1;
	$context['army_upgrade_success'] = isset($_REQUEST['upgraded']) && $_REQUEST['upgraded'] == 1;

	// Session tokens for the forms
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	// Load the template
	loadTemplate('ArmySystem-Training');
	$context['sub_template'] = 'army_training';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_training_title'] ?? 'Training');
}
