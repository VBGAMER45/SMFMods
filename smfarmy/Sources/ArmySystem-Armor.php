<?php
/**
 * Army System - Armor & Equipment Shop
 *
 * Buy/sell/repair weapons, armor, spy tools, and sentry tools.
 * Upgrade fortification and siege technology levels.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Main dispatcher for the equipment shop.
 *
 * Routes to the appropriate sub-handler based on the 'do' parameter:
 * buy (default), sell, repair, fort, or siege.
 *
 * Validates that the player has chosen a race and is not on vacation
 * before allowing access to any shop functionality.
 *
 * @return void
 */
function ArmyArmor()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_play');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	// Load member data
	$member = army_load_member($user_info['id']);

	// Must have a race to use the shop
	if ($member === false || empty($member['race_id']))
	{
		redirectexit('action=army;sa=race');
		return;
	}

	// Cannot shop while on vacation
	if (army_check_vacation($member))
		fatal_lang_error('army_on_vacation', false);

	// Sub-action routing for the shop
	$do = isset($_REQUEST['do']) ? $_REQUEST['do'] : 'buy';

	$shopActions = array(
		'buy'    => 'ArmyArmorBuy',
		'sell'   => 'ArmyArmorSell',
		'repair' => 'ArmyArmorRepair',
		'fort'   => 'ArmyArmorFort',
		'siege'  => 'ArmyArmorSiege',
		'ships'  => 'ArmyArmorShips',
	);

	if (!isset($shopActions[$do]))
		$do = 'buy';

	// Build the shop sub-navigation tabs for the template
	$context['army_shop_tabs'] = array(
		'buy' => array(
			'label' => $txt['army_armor_buy'] ?? 'Buy Equipment',
			'url' => $scripturl . '?action=army;sa=armor;do=buy',
			'active' => ($do === 'buy'),
		),
		'ships' => array(
			'label' => $txt['army_ships'] ?? 'Ships',
			'url' => $scripturl . '?action=army;sa=armor;do=ships',
			'active' => ($do === 'ships'),
		),
		'sell' => array(
			'label' => $txt['army_armor_sell'] ?? 'Sell Equipment',
			'url' => $scripturl . '?action=army;sa=armor;do=sell',
			'active' => ($do === 'sell'),
		),
		'repair' => array(
			'label' => $txt['army_armor_repair'] ?? 'Repair Equipment',
			'url' => $scripturl . '?action=army;sa=armor;do=repair',
			'active' => ($do === 'repair'),
		),
		'fort' => array(
			'label' => $txt['army_armor_fort'] ?? 'Fortification',
			'url' => $scripturl . '?action=army;sa=armor;do=fort',
			'active' => ($do === 'fort'),
		),
		'siege' => array(
			'label' => $txt['army_armor_siege'] ?? 'Siege Technology',
			'url' => $scripturl . '?action=army;sa=armor;do=siege',
			'active' => ($do === 'siege'),
		),
	);

	// Load the template
	loadTemplate('ArmySystem-Armor');

	// Dispatch to the appropriate handler
	$shopActions[$do]();
}

/**
 * Buy equipment page and POST handler.
 *
 * Displays four categories of purchasable equipment: attack weapons,
 * defense armor, spy tools, and sentry tools. Shows current inventory
 * quantities alongside each item.
 *
 * POST handler processes a bulk purchase: the 'buy' array maps item
 * keys ("section_number", e.g. "a_1") to quantities. The race discount
 * bonus reduces prices. Gold is deducted and inventory rows are
 * inserted or updated.
 *
 * @return void
 */
function ArmyArmorBuy()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	$settings = $modSettings['army'];

	// Load member data (fresh, in case of POST redirect)
	$member = army_load_member($user_info['id']);

	// Load all item categories
	$weapons = army_load_items('a');
	$armor = army_load_items('d');
	$spy_tools = army_load_items('q');
	$sentry_tools = army_load_items('e');
	$ships = army_load_items('b');

	// Load the member's current inventory
	$inventory = army_load_inventory($user_info['id']);

	// Handle POST: process purchases
	if (isset($_POST['buy']) && is_array($_POST['buy']))
	{
		checkSession();

		// Reload member for up-to-date gold
		$member = army_load_member($user_info['id']);
		$gold = (int) $member['army_points'];

		// Race discount bonus: reduces purchase price by bonus_discount %
		$discount = isset($member['bonus_discount']) ? (int) $member['bonus_discount'] : 0;

		// Combine all item definitions for easy lookup
		$all_items = array(
			'a' => $weapons,
			'd' => $armor,
			'q' => $spy_tools,
			'e' => $sentry_tools,
			'b' => $ships,
		);

		$total_cost = 0;
		$purchases = array();

		// First pass: validate all purchases and calculate total cost
		foreach ($_POST['buy'] as $key => $value)
		{
			$qty = max(0, (int) $value);

			if ($qty <= 0)
				continue;

			// Parse the key: "section_number" e.g. "a_1"
			$parts = explode('_', $key);

			if (count($parts) !== 2)
				continue;

			$section = $parts[0];
			$number = (int) $parts[1];

			// Validate section is a buyable type
			if (!isset($all_items[$section]) || !isset($all_items[$section][$number]))
				continue;

			$item = $all_items[$section][$number];
			$item_price = (int) $item['price'];

			// Apply race discount: price * (1 - discount/100), minimum 0
			$effective_price = (int) max(0, floor($item_price * (1 - $discount / 100)));
			$cost = $effective_price * $qty;

			$purchases[] = array(
				'section' => $section,
				'number' => $number,
				'qty' => $qty,
				'cost' => $cost,
				'item_value' => (int) $item['value'],
			);

			$total_cost += $cost;
		}

		// Check if player can afford the total
		if ($total_cost > $gold || empty($purchases))
		{
			if (!empty($purchases))
				fatal_lang_error('army_not_enough_gold', false);

			// Nothing to buy, just redirect
			redirectexit('action=army;sa=armor;do=buy');
			return;
		}

		// Deduct gold
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET army_points = army_points - {int:cost},
				last_active = {int:now}
			WHERE id_member = {int:id_member}',
			array(
				'cost' => $total_cost,
				'now' => time(),
				'id_member' => $user_info['id'],
			)
		);

		// Reload inventory for up-to-date quantities
		$inventory = army_load_inventory($user_info['id']);

		// Process each purchase
		foreach ($purchases as $purchase)
		{
			$inv_key = $purchase['section'] . '.' . $purchase['number'];

			if (isset($inventory[$inv_key]))
			{
				// Update existing inventory row: add quantity
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_inventory
					SET i_quantity = i_quantity + {int:qty}
					WHERE i_member = {int:id_member}
						AND i_section = {string:section}
						AND i_number = {int:number}',
					array(
						'qty' => $purchase['qty'],
						'id_member' => $user_info['id'],
						'section' => $purchase['section'],
						'number' => $purchase['number'],
					)
				);
			}
			else
			{
				// Insert new inventory row: new items start at full strength
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
						$purchase['section'],
						$purchase['number'],
						$user_info['id'],
						$purchase['qty'],
						(float) $purchase['item_value'],
						0,
					),
					array('i_id')
				);
			}
		}

		// Redirect back to buy page with success
		redirectexit('action=army;sa=armor;do=buy;bought=1');
		return;
	}

	// Build context for the template: each category with owned quantities
	$context['army_weapons'] = array();

	foreach ($weapons as $number => $item)
	{
		$inv_key = 'a.' . $number;
		$owned = isset($inventory[$inv_key]) ? (int) $inventory[$inv_key]['i_quantity'] : 0;

		$context['army_weapons'][$number] = array(
			'id' => (int) $item['id'],
			'number' => (int) $number,
			'name' => $item['name'],
			'value' => (int) $item['value'],
			'price' => (int) $item['price'],
			'price_formatted' => army_format_number($item['price']),
			'icon' => $item['icon'],
			'owned' => $owned,
			'owned_formatted' => army_format_number($owned),
			'key' => 'a_' . $number,
		);
	}

	$context['army_armor'] = array();

	foreach ($armor as $number => $item)
	{
		$inv_key = 'd.' . $number;
		$owned = isset($inventory[$inv_key]) ? (int) $inventory[$inv_key]['i_quantity'] : 0;

		$context['army_armor'][$number] = array(
			'id' => (int) $item['id'],
			'number' => (int) $number,
			'name' => $item['name'],
			'value' => (int) $item['value'],
			'price' => (int) $item['price'],
			'price_formatted' => army_format_number($item['price']),
			'icon' => $item['icon'],
			'owned' => $owned,
			'owned_formatted' => army_format_number($owned),
			'key' => 'd_' . $number,
		);
	}

	$context['army_spy_tools'] = array();

	foreach ($spy_tools as $number => $item)
	{
		$inv_key = 'q.' . $number;
		$owned = isset($inventory[$inv_key]) ? (int) $inventory[$inv_key]['i_quantity'] : 0;

		$context['army_spy_tools'][$number] = array(
			'id' => (int) $item['id'],
			'number' => (int) $number,
			'name' => $item['name'],
			'value' => (int) $item['value'],
			'price' => (int) $item['price'],
			'price_formatted' => army_format_number($item['price']),
			'icon' => $item['icon'],
			'owned' => $owned,
			'owned_formatted' => army_format_number($owned),
			'key' => 'q_' . $number,
		);
	}

	$context['army_sentry_tools'] = array();

	foreach ($sentry_tools as $number => $item)
	{
		$inv_key = 'e.' . $number;
		$owned = isset($inventory[$inv_key]) ? (int) $inventory[$inv_key]['i_quantity'] : 0;

		$context['army_sentry_tools'][$number] = array(
			'id' => (int) $item['id'],
			'number' => (int) $number,
			'name' => $item['name'],
			'value' => (int) $item['value'],
			'price' => (int) $item['price'],
			'price_formatted' => army_format_number($item['price']),
			'icon' => $item['icon'],
			'owned' => $owned,
			'owned_formatted' => army_format_number($owned),
			'key' => 'e_' . $number,
		);
	}

	$context['army_ships'] = array();

	foreach ($ships as $number => $item)
	{
		$inv_key = 'b.' . $number;
		$owned = isset($inventory[$inv_key]) ? (int) $inventory[$inv_key]['i_quantity'] : 0;

		$context['army_ships'][$number] = array(
			'id' => (int) $item['id'],
			'number' => (int) $number,
			'name' => $item['name'],
			'value' => (int) $item['value'],
			'price' => (int) $item['price'],
			'price_formatted' => army_format_number($item['price']),
			'icon' => $item['icon'],
			'owned' => $owned,
			'owned_formatted' => army_format_number($owned),
			'key' => 'b_' . $number,
		);
	}

	// Race discount info for the template
	$context['army_discount'] = isset($member['bonus_discount']) ? (int) $member['bonus_discount'] : 0;

	// Current gold
	$context['army_gold'] = army_format_number($member['army_points']);
	$context['army_gold_raw'] = (int) $member['army_points'];

	// Success flag from redirect
	$context['army_buy_success'] = isset($_REQUEST['bought']) && $_REQUEST['bought'] == 1;

	// Session tokens for the form
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_armor_buy';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_armor_buy'] ?? 'Buy Equipment');
}

/**
 * Sell equipment page and POST handler.
 *
 * Displays all owned equipment with calculated resell values. Weapons
 * (section 'a') and spy/sentry tools ('q','e') sell at the tool_resell
 * rate (default 45%). Armor (section 'd') sells at the armor_resell
 * rate (default 75%).
 *
 * POST handler processes a bulk sale: the 'sell' array maps item keys
 * ("section_number") to quantities. Gold is credited and inventory
 * quantities are reduced or rows deleted when emptied.
 *
 * @return void
 */
function ArmyArmorSell()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	$settings = $modSettings['army'];

	// Resell rates from settings (percentages)
	$tool_resell = isset($settings['tool_resell']) ? (int) $settings['tool_resell'] : 45;
	$armor_resell = isset($settings['armor_resell']) ? (int) $settings['armor_resell'] : 75;

	// Load member data
	$member = army_load_member($user_info['id']);

	// Load all item definitions for price lookup
	$weapons = army_load_items('a');
	$armor_items = army_load_items('d');
	$spy_tools = army_load_items('q');
	$sentry_tools = army_load_items('e');
	$ship_items = army_load_items('b');

	// Ship resell rate
	$ship_resell = isset($settings['ship_resell']) ? (int) $settings['ship_resell'] : 60;

	$all_items = array(
		'a' => $weapons,
		'd' => $armor_items,
		'q' => $spy_tools,
		'e' => $sentry_tools,
		'b' => $ship_items,
	);

	// Load inventory
	$inventory = army_load_inventory($user_info['id']);

	// Handle POST: process sales
	if (isset($_POST['sell']) && is_array($_POST['sell']))
	{
		checkSession();

		$total_income = 0;
		$sales = array();

		foreach ($_POST['sell'] as $key => $value)
		{
			$qty = max(0, (int) $value);

			if ($qty <= 0)
				continue;

			// Parse key
			$parts = explode('_', $key);

			if (count($parts) !== 2)
				continue;

			$section = $parts[0];
			$number = (int) $parts[1];

			// Validate item type and existence
			if (!isset($all_items[$section]) || !isset($all_items[$section][$number]))
				continue;

			// Validate player owns enough
			$inv_key = $section . '.' . $number;

			if (!isset($inventory[$inv_key]))
				continue;

			$owned = (int) $inventory[$inv_key]['i_quantity'];

			if ($qty > $owned)
				$qty = $owned;

			if ($qty <= 0)
				continue;

			$item = $all_items[$section][$number];
			$item_price = (int) $item['price'];

			// Determine the resell rate based on section
			// Armor (d) uses armor_resell; ships (b) use ship_resell; everything else uses tool_resell
			$resell_rate = ($section === 'd') ? $armor_resell : (($section === 'b') ? $ship_resell : $tool_resell);

			$sell_value = (int) floor($item_price * $qty * ($resell_rate / 100));

			$sales[] = array(
				'section' => $section,
				'number' => $number,
				'qty' => $qty,
				'sell_value' => $sell_value,
				'owned' => $owned,
			);

			$total_income += $sell_value;
		}

		if (!empty($sales))
		{
			// Credit gold
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}army_members
				SET army_points = army_points + {int:income},
					last_active = {int:now}
				WHERE id_member = {int:id_member}',
				array(
					'income' => $total_income,
					'now' => time(),
					'id_member' => $user_info['id'],
				)
			);

			// Reduce or delete inventory rows
			foreach ($sales as $sale)
			{
				if ($sale['qty'] >= $sale['owned'])
				{
					// Selling all: delete the row
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}army_inventory
						WHERE i_member = {int:id_member}
							AND i_section = {string:section}
							AND i_number = {int:number}',
						array(
							'id_member' => $user_info['id'],
							'section' => $sale['section'],
							'number' => $sale['number'],
						)
					);
				}
				else
				{
					// Selling partial: reduce quantity
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}army_inventory
						SET i_quantity = i_quantity - {int:qty}
						WHERE i_member = {int:id_member}
							AND i_section = {string:section}
							AND i_number = {int:number}',
						array(
							'qty' => $sale['qty'],
							'id_member' => $user_info['id'],
							'section' => $sale['section'],
							'number' => $sale['number'],
						)
					);
				}
			}
		}

		// Redirect back with success
		redirectexit('action=army;sa=armor;do=sell;sold=1');
		return;
	}

	// Build context: all owned items with sell prices
	$context['army_owned_items'] = array();

	foreach ($inventory as $inv_key => $inv)
	{
		$section = $inv['i_section'];
		$number = (int) $inv['i_number'];
		$qty = (int) $inv['i_quantity'];

		// Only show sellable equipment types
		if (!isset($all_items[$section]) || !isset($all_items[$section][$number]))
			continue;

		if ($qty <= 0)
			continue;

		$item = $all_items[$section][$number];
		$item_price = (int) $item['price'];

		// Determine resell rate
		$resell_rate = ($section === 'd') ? $armor_resell : (($section === 'b') ? $ship_resell : $tool_resell);

		$sell_price_per_unit = (int) floor($item_price * ($resell_rate / 100));

		// Section labels for the template
		$section_labels = array(
			'a' => $txt['army_section_weapons'] ?? 'Weapons',
			'd' => $txt['army_section_armor'] ?? 'Armor',
			'q' => $txt['army_section_spy_tools'] ?? 'Spy Tools',
			'e' => $txt['army_section_sentry_tools'] ?? 'Sentry Tools',
			'b' => $txt['army_ships'] ?? 'Ships',
		);

		$context['army_owned_items'][] = array(
			'section' => $section,
			'number' => $number,
			'name' => $item['name'],
			'icon' => $item['icon'],
			'quantity' => $qty,
			'quantity_formatted' => army_format_number($qty),
			'strength' => (float) $inv['i_strength'],
			'max_strength' => (int) $item['value'],
			'sell_price_per_unit' => $sell_price_per_unit,
			'sell_price_per_unit_formatted' => army_format_number($sell_price_per_unit),
			'resell_rate' => $resell_rate,
			'section_label' => $section_labels[$section] ?? $section,
			'key' => $section . '_' . $number,
		);
	}

	// Current gold
	$context['army_gold'] = army_format_number($member['army_points']);
	$context['army_gold_raw'] = (int) $member['army_points'];

	// Resell rates for display
	$context['army_tool_resell'] = $tool_resell;
	$context['army_armor_resell'] = $armor_resell;
	$context['army_ship_resell'] = $ship_resell;

	// Success flag
	$context['army_sell_success'] = isset($_REQUEST['sold']) && $_REQUEST['sold'] == 1;

	// Session tokens
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_armor_sell';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_armor_sell'] ?? 'Sell Equipment');
}

/**
 * Repair equipment page and POST handler.
 *
 * Lists all inventory items whose current strength (i_strength) is below
 * the item's maximum value, indicating battle damage or sabotage. The
 * player can select items to repair to full strength.
 *
 * Repair cost per unit is calculated proportionally: the fraction of
 * strength lost times the item's base price. If the item has a specific
 * repair cost defined in the items table, that is used instead.
 *
 * POST handler processes selected repairs: the 'repair' array maps
 * inventory i_id values to 1 (checked). Gold is deducted and i_strength
 * is restored to the item's full value.
 *
 * @return void
 */
function ArmyArmorRepair()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	$settings = $modSettings['army'];

	// Load member data
	$member = army_load_member($user_info['id']);

	// Load all item definitions for value/repair lookups
	$weapons = army_load_items('a');
	$armor_items = army_load_items('d');
	$spy_tools = army_load_items('q');
	$sentry_tools = army_load_items('e');
	$ship_items = army_load_items('b');

	$all_items = array(
		'a' => $weapons,
		'd' => $armor_items,
		'q' => $spy_tools,
		'e' => $sentry_tools,
		'b' => $ship_items,
	);

	// Load inventory
	$inventory = army_load_inventory($user_info['id']);

	// Handle POST: process repairs
	if (isset($_POST['repair']) && is_array($_POST['repair']))
	{
		checkSession();

		// Reload member for up-to-date gold
		$member = army_load_member($user_info['id']);
		$gold = (int) $member['army_points'];

		$total_cost = 0;
		$repairs = array();

		foreach ($_POST['repair'] as $i_id => $checked)
		{
			$i_id = (int) $i_id;

			if ($i_id <= 0 || empty($checked))
				continue;

			// Find this inventory row in the loaded inventory, verify ownership
			$found = false;

			foreach ($inventory as $inv_key => $inv)
			{
				if ((int) $inv['i_id'] === $i_id)
				{
					$found = $inv;
					break;
				}
			}

			if ($found === false)
				continue;

			$section = $found['i_section'];
			$number = (int) $found['i_number'];
			$qty = (int) $found['i_quantity'];
			$current_strength = (float) $found['i_strength'];

			// Validate item definition exists
			if (!isset($all_items[$section]) || !isset($all_items[$section][$number]))
				continue;

			$item = $all_items[$section][$number];
			$max_strength = (float) $item['value'];
			$damage = $max_strength - $current_strength;

			// Skip if no damage
			if ($damage <= 0)
				continue;

			// Calculate repair cost
			// If the item has a specific repair cost, use it; otherwise calculate proportionally
			$item_repair = (int) $item['repair'];
			$item_price = (int) $item['price'];

			if ($item_repair > 0)
			{
				// Specific repair cost per unit, scaled by damage fraction
				$cost_per_unit = (int) ceil($item_repair * ($damage / $max_strength));
			}
			else
			{
				// Fallback: proportional cost based on item price
				$cost_per_unit = (int) ceil($item_price * ($damage / $max_strength));
			}

			$repair_cost = $cost_per_unit * $qty;

			$repairs[] = array(
				'i_id' => $i_id,
				'max_strength' => $max_strength,
				'cost' => $repair_cost,
			);

			$total_cost += $repair_cost;
		}

		// Check affordability
		if ($total_cost > $gold || empty($repairs))
		{
			if (!empty($repairs))
				fatal_lang_error('army_not_enough_gold', false);

			redirectexit('action=army;sa=armor;do=repair');
			return;
		}

		// Deduct gold
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET army_points = army_points - {int:cost},
				last_active = {int:now}
			WHERE id_member = {int:id_member}',
			array(
				'cost' => $total_cost,
				'now' => time(),
				'id_member' => $user_info['id'],
			)
		);

		// Restore strength to full for each repaired item
		foreach ($repairs as $repair)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}army_inventory
				SET i_strength = {float:max_strength}
				WHERE i_id = {int:i_id}
					AND i_member = {int:id_member}',
				array(
					'max_strength' => $repair['max_strength'],
					'i_id' => $repair['i_id'],
					'id_member' => $user_info['id'],
				)
			);
		}

		// Redirect with success
		redirectexit('action=army;sa=armor;do=repair;repaired=1');
		return;
	}

	// Build context: items that have damage (strength < max value)
	$context['army_damaged_items'] = array();

	// Section labels
	$section_labels = array(
		'a' => $txt['army_section_weapons'] ?? 'Weapons',
		'd' => $txt['army_section_armor'] ?? 'Armor',
		'q' => $txt['army_section_spy_tools'] ?? 'Spy Tools',
		'e' => $txt['army_section_sentry_tools'] ?? 'Sentry Tools',
		'b' => $txt['army_ships'] ?? 'Ships',
	);

	foreach ($inventory as $inv_key => $inv)
	{
		$section = $inv['i_section'];
		$number = (int) $inv['i_number'];
		$qty = (int) $inv['i_quantity'];
		$current_strength = (float) $inv['i_strength'];

		if (!isset($all_items[$section]) || !isset($all_items[$section][$number]))
			continue;

		if ($qty <= 0)
			continue;

		$item = $all_items[$section][$number];
		$max_strength = (float) $item['value'];
		$damage = $max_strength - $current_strength;

		// Only show items with actual damage
		if ($damage <= 0)
			continue;

		// Calculate repair cost
		$item_repair = (int) $item['repair'];
		$item_price = (int) $item['price'];

		if ($item_repair > 0)
			$cost_per_unit = (int) ceil($item_repair * ($damage / $max_strength));
		else
			$cost_per_unit = (int) ceil($item_price * ($damage / $max_strength));

		$repair_cost = $cost_per_unit * $qty;

		// Damage percentage for display
		$damage_percent = ($max_strength > 0) ? round(($damage / $max_strength) * 100, 1) : 0;

		$context['army_damaged_items'][] = array(
			'i_id' => (int) $inv['i_id'],
			'section' => $section,
			'number' => $number,
			'name' => $item['name'],
			'icon' => $item['icon'],
			'quantity' => $qty,
			'quantity_formatted' => army_format_number($qty),
			'current_strength' => $current_strength,
			'max_strength' => $max_strength,
			'damage' => $damage,
			'damage_percent' => $damage_percent,
			'repair_cost' => $repair_cost,
			'repair_cost_formatted' => army_format_number($repair_cost),
			'section_label' => $section_labels[$section] ?? $section,
		);
	}

	// Current gold
	$context['army_gold'] = army_format_number($member['army_points']);
	$context['army_gold_raw'] = (int) $member['army_points'];

	// Success flag
	$context['army_repair_success'] = isset($_REQUEST['repaired']) && $_REQUEST['repaired'] == 1;

	// Session tokens
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_armor_repair';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_armor_repair'] ?? 'Repair Equipment');
}

/**
 * Fortification upgrade page and POST handler.
 *
 * Displays the player's current fort level and the next available
 * upgrade (if not already at maximum). Fort levels provide a percentage-
 * based defense bonus during attacks (configured by fort_percent setting).
 *
 * Fort items are stored in the items table with type='f', ordered by
 * number. The player's fort_level in army_members corresponds to the
 * item number.
 *
 * POST handler validates the upgrade is available, checks gold, deducts
 * the price, and increments fort_level.
 *
 * @return void
 */
function ArmyArmorFort()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	$settings = $modSettings['army'];

	// Load member data
	$member = army_load_member($user_info['id']);

	// Load fort items
	$fort_items = army_load_items('f');

	$current_level = (int) $member['fort_level'];
	$next_level = $current_level + 1;

	// Find current and next fort items
	$current_fort = isset($fort_items[$current_level]) ? $fort_items[$current_level] : null;
	$next_fort = isset($fort_items[$next_level]) ? $fort_items[$next_level] : null;

	// Handle POST: process fort upgrade
	if (isset($_POST['upgrade_fort']))
	{
		checkSession();

		// Reload member for up-to-date data
		$member = army_load_member($user_info['id']);
		$current_level = (int) $member['fort_level'];
		$next_level = $current_level + 1;
		$next_fort = isset($fort_items[$next_level]) ? $fort_items[$next_level] : null;

		// Validate that next level exists (not already maxed)
		if ($next_fort === null)
			fatal_lang_error('army_fort_max_level', false);

		$upgrade_cost = (int) $next_fort['price'];
		$gold = (int) $member['army_points'];

		// Check gold
		if ($gold < $upgrade_cost)
			fatal_lang_error('army_not_enough_gold', false);

		// Deduct gold and increment fort level
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET army_points = army_points - {int:cost},
				fort_level = {int:next_level},
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
		redirectexit('action=army;sa=armor;do=fort;upgraded=1');
		return;
	}

	// Build context for the template
	$context['army_fort_current'] = null;

	if ($current_fort !== null)
	{
		$context['army_fort_current'] = array(
			'level' => $current_level,
			'name' => $current_fort['name'],
			'icon' => $current_fort['icon'],
		);
	}

	$context['army_fort_next'] = null;

	if ($next_fort !== null)
	{
		$context['army_fort_next'] = array(
			'level' => $next_level,
			'name' => $next_fort['name'],
			'price' => (int) $next_fort['price'],
			'price_formatted' => army_format_number($next_fort['price']),
			'icon' => $next_fort['icon'],
		);
	}

	// All fort levels for display (progression list)
	$context['army_fort_items'] = array();

	foreach ($fort_items as $number => $item)
	{
		$context['army_fort_items'][$number] = array(
			'level' => (int) $number,
			'name' => $item['name'],
			'price' => (int) $item['price'],
			'price_formatted' => army_format_number($item['price']),
			'icon' => $item['icon'],
			'is_current' => ((int) $number === $current_level),
			'is_completed' => ((int) $number < $current_level),
			'is_next' => ((int) $number === $next_level),
		);
	}

	// Fort defense bonus percentage from settings
	$context['army_fort_percent'] = isset($settings['fort_percent']) ? (int) $settings['fort_percent'] : 25;

	// Current gold
	$context['army_gold'] = army_format_number($member['army_points']);
	$context['army_gold_raw'] = (int) $member['army_points'];

	// Success flag
	$context['army_fort_success'] = isset($_REQUEST['upgraded']) && $_REQUEST['upgraded'] == 1;

	// Session tokens
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_armor_fort';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_armor_fort'] ?? 'Fortification');
}

/**
 * Siege technology upgrade page and POST handler.
 *
 * Same structure as ArmyArmorFort() but for siege levels (type='s',
 * siege_level field). Siege technology provides a percentage-based
 * attack bonus against fortified defenders (configured by siege_percent).
 *
 * Siege items are stored in the items table with type='s', ordered by
 * number. The player's siege_level in army_members corresponds to the
 * item number.
 *
 * POST handler validates the upgrade is available, checks gold, deducts
 * the price, and increments siege_level.
 *
 * @return void
 */
function ArmyArmorSiege()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	$settings = $modSettings['army'];

	// Load member data
	$member = army_load_member($user_info['id']);

	// Load siege items
	$siege_items = army_load_items('s');

	$current_level = (int) $member['siege_level'];
	$next_level = $current_level + 1;

	// Find current and next siege items
	$current_siege = isset($siege_items[$current_level]) ? $siege_items[$current_level] : null;
	$next_siege = isset($siege_items[$next_level]) ? $siege_items[$next_level] : null;

	// Handle POST: process siege upgrade
	if (isset($_POST['upgrade_siege']))
	{
		checkSession();

		// Reload member for up-to-date data
		$member = army_load_member($user_info['id']);
		$current_level = (int) $member['siege_level'];
		$next_level = $current_level + 1;
		$next_siege = isset($siege_items[$next_level]) ? $siege_items[$next_level] : null;

		// Validate that next level exists (not already maxed)
		if ($next_siege === null)
			fatal_lang_error('army_siege_max_level', false);

		$upgrade_cost = (int) $next_siege['price'];
		$gold = (int) $member['army_points'];

		// Check gold
		if ($gold < $upgrade_cost)
			fatal_lang_error('army_not_enough_gold', false);

		// Deduct gold and increment siege level
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET army_points = army_points - {int:cost},
				siege_level = {int:next_level},
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
		redirectexit('action=army;sa=armor;do=siege;upgraded=1');
		return;
	}

	// Build context for the template
	$context['army_siege_current'] = null;

	if ($current_siege !== null)
	{
		$context['army_siege_current'] = array(
			'level' => $current_level,
			'name' => $current_siege['name'],
			'icon' => $current_siege['icon'],
		);
	}

	$context['army_siege_next'] = null;

	if ($next_siege !== null)
	{
		$context['army_siege_next'] = array(
			'level' => $next_level,
			'name' => $next_siege['name'],
			'price' => (int) $next_siege['price'],
			'price_formatted' => army_format_number($next_siege['price']),
			'icon' => $next_siege['icon'],
		);
	}

	// All siege levels for display (progression list)
	$context['army_siege_items'] = array();

	foreach ($siege_items as $number => $item)
	{
		$context['army_siege_items'][$number] = array(
			'level' => (int) $number,
			'name' => $item['name'],
			'price' => (int) $item['price'],
			'price_formatted' => army_format_number($item['price']),
			'icon' => $item['icon'],
			'is_current' => ((int) $number === $current_level),
			'is_completed' => ((int) $number < $current_level),
			'is_next' => ((int) $number === $next_level),
		);
	}

	// Siege attack bonus percentage from settings
	$context['army_siege_percent'] = isset($settings['siege_percent']) ? (int) $settings['siege_percent'] : 25;

	// Current gold
	$context['army_gold'] = army_format_number($member['army_points']);
	$context['army_gold_raw'] = (int) $member['army_points'];

	// Success flag
	$context['army_siege_success'] = isset($_REQUEST['upgraded']) && $_REQUEST['upgraded'] == 1;

	// Session tokens
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_armor_siege';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_armor_siege'] ?? 'Siege Technology');
}

/**
 * Ships buy page and POST handler.
 *
 * Displays all available ships with current owned quantities. Ships
 * contribute to both attack and defense power. Uses the same purchase
 * mechanic as weapons/armor.
 *
 * @return void
 */
function ArmyArmorShips()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	$settings = $modSettings['army'];

	// Load member data
	$member = army_load_member($user_info['id']);

	// Load ship items
	$ships = army_load_items('b');

	// Load inventory
	$inventory = army_load_inventory($user_info['id']);

	// Handle POST: process ship purchases
	if (isset($_POST['buy']) && is_array($_POST['buy']))
	{
		checkSession();

		// Reload member for up-to-date gold
		$member = army_load_member($user_info['id']);
		$gold = (int) $member['army_points'];

		// Race discount bonus
		$discount = isset($member['bonus_discount']) ? (int) $member['bonus_discount'] : 0;

		$total_cost = 0;
		$purchases = array();

		foreach ($_POST['buy'] as $key => $value)
		{
			$qty = max(0, (int) $value);

			if ($qty <= 0)
				continue;

			// Parse key: "b_number"
			$parts = explode('_', $key);

			if (count($parts) !== 2 || $parts[0] !== 'b')
				continue;

			$number = (int) $parts[1];

			if (!isset($ships[$number]))
				continue;

			$item = $ships[$number];
			$item_price = (int) $item['price'];

			$effective_price = (int) max(0, floor($item_price * (1 - $discount / 100)));
			$cost = $effective_price * $qty;

			$purchases[] = array(
				'section' => 'b',
				'number' => $number,
				'qty' => $qty,
				'cost' => $cost,
				'item_value' => (int) $item['value'],
			);

			$total_cost += $cost;
		}

		if ($total_cost > $gold || empty($purchases))
		{
			if (!empty($purchases))
				fatal_lang_error('army_not_enough_gold', false);

			redirectexit('action=army;sa=armor;do=ships');
			return;
		}

		// Deduct gold
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_members
			SET army_points = army_points - {int:cost},
				last_active = {int:now}
			WHERE id_member = {int:id_member}',
			array(
				'cost' => $total_cost,
				'now' => time(),
				'id_member' => $user_info['id'],
			)
		);

		// Reload inventory
		$inventory = army_load_inventory($user_info['id']);

		foreach ($purchases as $purchase)
		{
			$inv_key = $purchase['section'] . '.' . $purchase['number'];

			if (isset($inventory[$inv_key]))
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}army_inventory
					SET i_quantity = i_quantity + {int:qty}
					WHERE i_member = {int:id_member}
						AND i_section = {string:section}
						AND i_number = {int:number}',
					array(
						'qty' => $purchase['qty'],
						'id_member' => $user_info['id'],
						'section' => $purchase['section'],
						'number' => $purchase['number'],
					)
				);
			}
			else
			{
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
						$purchase['section'],
						$purchase['number'],
						$user_info['id'],
						$purchase['qty'],
						(float) $purchase['item_value'],
						0,
					),
					array('i_id')
				);
			}
		}

		redirectexit('action=army;sa=armor;do=ships;bought=1');
		return;
	}

	// Build context for the template
	$context['army_ships'] = array();

	foreach ($ships as $number => $item)
	{
		$inv_key = 'b.' . $number;
		$owned = isset($inventory[$inv_key]) ? (int) $inventory[$inv_key]['i_quantity'] : 0;

		$context['army_ships'][$number] = array(
			'id' => (int) $item['id'],
			'number' => (int) $number,
			'name' => $item['name'],
			'value' => (int) $item['value'],
			'price' => (int) $item['price'],
			'price_formatted' => army_format_number($item['price']),
			'icon' => $item['icon'],
			'owned' => $owned,
			'owned_formatted' => army_format_number($owned),
			'key' => 'b_' . $number,
		);
	}

	// Race discount info
	$context['army_discount'] = isset($member['bonus_discount']) ? (int) $member['bonus_discount'] : 0;

	// Current gold
	$context['army_gold'] = army_format_number($member['army_points']);
	$context['army_gold_raw'] = (int) $member['army_points'];

	// Success flag
	$context['army_buy_success'] = isset($_REQUEST['bought']) && $_REQUEST['bought'] == 1;

	// Session tokens
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_armor_ships';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_ships'] ?? 'Ships');
}
