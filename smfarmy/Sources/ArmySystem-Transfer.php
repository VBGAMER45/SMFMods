<?php
/**
 * Army System - Transfer Services
 *
 * Transfer money and weapons between members. Validates that both
 * sender and recipient are active participants with a chosen race,
 * and logs all transfers for auditing.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Transfer page and POST handler.
 *
 * Routes between money and weapons transfers based on the 'do' parameter.
 * Both sub-actions require the sender to be an active participant who is
 * not on vacation.
 *
 * GET: Displays transfer forms (money or weapons) with a target selector
 * listing all active members and the sender's current inventory.
 *
 * POST (money transfer):
 *   - Validates target exists, has race, is active
 *   - Validates amount > 0 and <= sender's gold
 *   - Deducts from sender, adds to target
 *   - Logs in army_transfer_log and army_events (type 6)
 *
 * POST (weapons transfer):
 *   - Validates target exists, has race, is active
 *   - Loops through 'transfer' array: key = "section_number", value = qty
 *   - Validates each item exists in sender's inventory with sufficient qty
 *   - Reduces sender inventory, adds to target inventory (insert or update)
 *   - Logs in army_transfer_log and army_events (type 4)
 *
 * @return void
 */
function ArmyTransfer()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_play');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	$settings = $modSettings['army'];

	// Load member data
	$member = army_load_member($user_info['id']);

	// Must have a race to use transfers
	if ($member === false || empty($member['race_id']))
	{
		redirectexit('action=army;sa=race');
		return;
	}

	// Cannot transfer while on vacation
	if (army_check_vacation($member))
		fatal_lang_error('army_on_vacation', false);

	// Load the template
	loadTemplate('ArmySystem-Transfer');

	// Determine sub-action: money (default) or weapons
	$do = isset($_REQUEST['do']) ? $_REQUEST['do'] : 'money';

	if (!in_array($do, array('money', 'weapons')))
		$do = 'money';

	// Build the transfer sub-navigation tabs for the template
	$context['army_transfer_tabs'] = array(
		'money' => array(
			'label' => $txt['army_transfer_money'] ?? 'Money Transfer',
			'url' => $scripturl . '?action=army;sa=transfer;do=money',
			'active' => ($do === 'money'),
		),
		'weapons' => array(
			'label' => $txt['army_transfer_weapons'] ?? 'Weapons Transfer',
			'url' => $scripturl . '?action=army;sa=transfer;do=weapons',
			'active' => ($do === 'weapons'),
		),
	);

	$currency_name = $settings['currency_name'] ?? ($txt['army_currency'] ?? 'Gold');

	// -------------------------------------------------------
	// POST handler: money transfer
	// -------------------------------------------------------
	if ($do === 'money' && isset($_POST['transfer_money']))
	{
		checkSession();

		// Reload member for up-to-date gold
		$member = army_load_member($user_info['id']);

		if ($member === false || empty($member['race_id']))
			fatal_lang_error('army_member_not_found', false);

		if (army_check_vacation($member))
			fatal_lang_error('army_on_vacation', false);

		$target_id = (int) ($_POST['target'] ?? 0);
		$amount = max(0, (int) ($_POST['amount'] ?? 0));

		// Validate target
		if ($target_id <= 0 || $target_id === $user_info['id'])
			fatal_lang_error('army_transfer_invalid_target', false);

		$target = army_load_member($target_id);

		if ($target === false || empty($target['race_id']))
			fatal_lang_error('army_transfer_invalid_target', false);

		if (!army_is_active($target))
			fatal_lang_error('army_transfer_target_inactive', false);

		// Validate amount
		if ($amount <= 0)
			fatal_lang_error('army_transfer_invalid_amount', false);

		$gold = (int) $member['army_points'];

		if ($amount > $gold)
			fatal_lang_error('army_not_enough_gold', false);

		// Deduct from sender
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET army_points = army_points - {int:amount},
				last_active = {int:now}
			WHERE id_member = {int:id_member}',
			array(
				'amount' => $amount,
				'now' => time(),
				'id_member' => $user_info['id'],
			)
		);

		// Add to target
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET army_points = army_points + {int:amount}
			WHERE id_member = {int:target}',
			array(
				'amount' => $amount,
				'target' => $target_id,
			)
		);

		// Log the transfer
		$smcFunc['db_insert']('insert',
			'{db_prefix}army_transfer_log',
			array(
				'field_from' => 'string',
				'field_to' => 'string',
				'member_from' => 'int',
				'member_to' => 'int',
				'amount' => 'int',
				'l_time' => 'int',
			),
			array(
				'army_points',
				'army_points',
				$user_info['id'],
				$target_id,
				$amount,
				time(),
			),
			array('l_id')
		);

		// Log event (type 6: money transfer)
		army_log_event(
			$user_info['id'],
			$target_id,
			6,
			'<% FROM %> transferred ' . army_format_number($amount) . ' <% MONEYNAME %> to <% TO %>'
		);

		// Redirect with success
		redirectexit('action=army;sa=transfer;do=money;sent=1');
		return;
	}

	// -------------------------------------------------------
	// POST handler: weapons transfer
	// -------------------------------------------------------
	if ($do === 'weapons' && isset($_POST['transfer_weapons']))
	{
		checkSession();

		// Reload member for up-to-date data
		$member = army_load_member($user_info['id']);

		if ($member === false || empty($member['race_id']))
			fatal_lang_error('army_member_not_found', false);

		if (army_check_vacation($member))
			fatal_lang_error('army_on_vacation', false);

		$target_id = (int) ($_POST['target'] ?? 0);

		// Validate target
		if ($target_id <= 0 || $target_id === $user_info['id'])
			fatal_lang_error('army_transfer_invalid_target', false);

		$target = army_load_member($target_id);

		if ($target === false || empty($target['race_id']))
			fatal_lang_error('army_transfer_invalid_target', false);

		if (!army_is_active($target))
			fatal_lang_error('army_transfer_target_inactive', false);

		// Process transfer array
		$transfers = isset($_POST['transfer']) && is_array($_POST['transfer']) ? $_POST['transfer'] : array();

		if (empty($transfers))
		{
			redirectexit('action=army;sa=transfer;do=weapons');
			return;
		}

		// Load sender's inventory
		$inventory = army_load_inventory($user_info['id']);

		// Load all item definitions for validation
		$all_items = array(
			'a' => army_load_items('a'),
			'd' => army_load_items('d'),
			'q' => army_load_items('q'),
			'e' => army_load_items('e'),
		);

		// Load target's inventory for insert-or-update logic
		$target_inventory = army_load_inventory($target_id);

		$valid_transfers = array();
		$total_items_transferred = 0;

		// First pass: validate all transfers
		foreach ($transfers as $key => $value)
		{
			$qty = max(0, (int) $value);

			if ($qty <= 0)
				continue;

			// Parse key: "section_number" e.g. "a_1"
			$parts = explode('_', $key);

			if (count($parts) !== 2)
				continue;

			$section = $parts[0];
			$number = (int) $parts[1];

			// Validate section is a transferable type
			if (!isset($all_items[$section]) || !isset($all_items[$section][$number]))
				continue;

			// Validate sender owns enough
			$inv_key = $section . '.' . $number;

			if (!isset($inventory[$inv_key]))
				continue;

			$owned = (int) $inventory[$inv_key]['i_quantity'];

			if ($qty > $owned)
				$qty = $owned;

			if ($qty <= 0)
				continue;

			$valid_transfers[] = array(
				'section' => $section,
				'number' => $number,
				'qty' => $qty,
				'strength' => (float) $inventory[$inv_key]['i_strength'],
				'item_value' => (int) $all_items[$section][$number]['value'],
				'item_name' => $all_items[$section][$number]['name'],
				'owned' => $owned,
			);

			$total_items_transferred += $qty;
		}

		if (empty($valid_transfers))
		{
			redirectexit('action=army;sa=transfer;do=weapons');
			return;
		}

		// Second pass: execute the transfers
		foreach ($valid_transfers as $xfer)
		{
			// Reduce or delete sender's inventory
			if ($xfer['qty'] >= $xfer['owned'])
			{
				// Transferring all: delete the sender's row
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}army_inventory
					WHERE i_member = {int:id_member}
						AND i_section = {string:section}
						AND i_number = {int:number}',
					array(
						'id_member' => $user_info['id'],
						'section' => $xfer['section'],
						'number' => $xfer['number'],
					)
				);
			}
			else
			{
				// Transferring partial: reduce quantity
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_inventory
					SET i_quantity = i_quantity - {int:qty}
					WHERE i_member = {int:id_member}
						AND i_section = {string:section}
						AND i_number = {int:number}',
					array(
						'qty' => $xfer['qty'],
						'id_member' => $user_info['id'],
						'section' => $xfer['section'],
						'number' => $xfer['number'],
					)
				);
			}

			// Add to target's inventory (insert or update)
			$target_inv_key = $xfer['section'] . '.' . $xfer['number'];

			if (isset($target_inventory[$target_inv_key]))
			{
				// Target already has this item: add quantity
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_inventory
					SET i_quantity = i_quantity + {int:qty}
					WHERE i_member = {int:target}
						AND i_section = {string:section}
						AND i_number = {int:number}',
					array(
						'qty' => $xfer['qty'],
						'target' => $target_id,
						'section' => $xfer['section'],
						'number' => $xfer['number'],
					)
				);
			}
			else
			{
				// Target does not have this item: insert new row
				// Transferred items keep the sender's current strength
				$smcFunc['db_insert']('insert',
					'{db_prefix}army_inventory',
					array(
						'i_section' => 'string',
						'i_number' => 'int',
						'i_member' => 'int',
						'i_quantity' => 'int',
						'i_strength' => 'float',
						'i_spy_sabbed' => 'int',
					),
					array(
						$xfer['section'],
						$xfer['number'],
						$target_id,
						$xfer['qty'],
						$xfer['strength'],
						0,
					),
					array('i_id')
				);

				// Track it so subsequent transfers of the same item update instead of insert
				$target_inventory[$target_inv_key] = true;
			}
		}

		// Update sender's last_active
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET last_active = {int:now}
			WHERE id_member = {int:id_member}',
			array(
				'now' => time(),
				'id_member' => $user_info['id'],
			)
		);

		// Log the transfer (one aggregate log entry)
		$smcFunc['db_insert']('insert',
			'{db_prefix}army_transfer_log',
			array(
				'field_from' => 'string',
				'field_to' => 'string',
				'member_from' => 'int',
				'member_to' => 'int',
				'amount' => 'int',
				'l_time' => 'int',
			),
			array(
				'{army2_weapons}',
				'{army2_weapons}',
				$user_info['id'],
				$target_id,
				$total_items_transferred,
				time(),
			),
			array('l_id')
		);

		// Log event (type 4: item transfer)
		army_log_event(
			$user_info['id'],
			$target_id,
			4,
			'<% FROM %> transferred ' . army_format_number($total_items_transferred) . ' item(s) to <% TO %>'
		);

		// Redirect with success
		redirectexit('action=army;sa=transfer;do=weapons;sent=1');
		return;
	}

	// -------------------------------------------------------
	// GET: Display transfer forms
	// -------------------------------------------------------

	// Refresh member data
	$member = army_load_member($user_info['id']);

	// Build list of possible targets (active members, not self)
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

	// Member info for the template
	$context['army_member'] = array(
		'id' => (int) $member['id_member'],
		'army_points' => army_format_number($member['army_points']),
		'army_points_raw' => (int) $member['army_points'],
		'race_name' => $member['race_name'] ?? '',
	);

	// Load owned items for weapons transfer tab
	$context['army_owned_items'] = array();

	if ($do === 'weapons')
	{
		$inventory = army_load_inventory($user_info['id']);

		// Load all item definitions for names/icons
		$all_items = array(
			'a' => army_load_items('a'),
			'd' => army_load_items('d'),
			'q' => army_load_items('q'),
			'e' => army_load_items('e'),
		);

		$section_labels = array(
			'a' => $txt['army_section_weapons'] ?? 'Weapons',
			'd' => $txt['army_section_armor'] ?? 'Armor',
			'q' => $txt['army_section_spy_tools'] ?? 'Spy Tools',
			'e' => $txt['army_section_sentry_tools'] ?? 'Sentry Tools',
		);

		foreach ($inventory as $inv_key => $inv)
		{
			$section = $inv['i_section'];
			$number = (int) $inv['i_number'];
			$qty = (int) $inv['i_quantity'];

			// Only show transferable equipment types
			if (!isset($all_items[$section]) || !isset($all_items[$section][$number]))
				continue;

			if ($qty <= 0)
				continue;

			$item = $all_items[$section][$number];

			$context['army_owned_items'][] = array(
				'section' => $section,
				'number' => $number,
				'name' => $item['name'],
				'icon' => $item['icon'],
				'quantity' => $qty,
				'quantity_formatted' => army_format_number($qty),
				'strength' => (float) $inv['i_strength'],
				'max_strength' => (int) $item['value'],
				'section_label' => $section_labels[$section] ?? $section,
				'key' => $section . '_' . $number,
			);
		}
	}

	// Currency name for display
	$context['army_currency'] = $currency_name;

	// Current gold for display
	$context['army_gold'] = army_format_number($member['army_points']);
	$context['army_gold_raw'] = (int) $member['army_points'];

	// Current sub-action
	$context['army_transfer_do'] = $do;

	// Success flag from redirect
	$context['army_transfer_success'] = isset($_REQUEST['sent']) && $_REQUEST['sent'] == 1;

	// Pre-fill target if provided via URL
	$context['army_prefill_target'] = 0;

	if (isset($_REQUEST['target']))
	{
		$prefill_target = (int) $_REQUEST['target'];

		if ($prefill_target > 0 && $prefill_target !== $user_info['id'])
			$context['army_prefill_target'] = $prefill_target;
	}

	// Session tokens for the forms
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_transfer';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_transfer_title'] ?? 'Transfer');
}
