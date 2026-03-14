<?php
/**
 * Army System - Armor & Equipment Shop Templates
 *
 * Provides template functions for the equipment shop: buying weapons/armor/
 * tools, selling owned equipment, repairing damaged items, and upgrading
 * fortification and siege technology levels.
 *
 * Each template displays the shared shop sub-navigation tabs for switching
 * between the five shop sections.
 *
 * @package ArmySystem
 * @version 1.0
 */

/**
 * Shared shop sub-navigation tabs.
 *
 * Renders a horizontal tab bar for the five shop sections (Buy, Sell, Repair,
 * Fort, Siege). The active tab is highlighted with a CSS class.
 *
 * Context variables used:
 *   $context['army_shop_tabs'] - array of tab definitions with label, url, active
 */
function template_army_shop_tabs()
{
	global $context;

	$tabs = $context['army_shop_tabs'] ?? array();

	if (empty($tabs))
		return;

	echo '
			<div class="buttonlist army_shop_tabs">
				<ul>';

	foreach ($tabs as $key => $tab)
	{
		$active = !empty($tab['active']) ? ' active' : '';

		echo '
					<li>
						<a class="button', $active, '" href="', $tab['url'], '">', htmlspecialchars($tab['label']), '</a>
					</li>';
	}

	echo '
				</ul>
			</div>';
}

/**
 * Buy equipment template.
 *
 * Displays four collapsible item categories (Attack Weapons, Defense Armor,
 * Spy Tools, Sentry Tools) in table format. Each item shows its name, power
 * value, purchase price (with race discount note when applicable), current
 * owned quantity, and a quantity input field for purchasing.
 *
 * Context variables used:
 *   $context['army_shop_tabs']    - array, shop sub-navigation tabs
 *   $context['army_buy_success']  - bool, show purchase success message
 *   $context['army_gold']         - string, formatted current gold
 *   $context['army_gold_raw']     - int, raw current gold
 *   $context['army_discount']     - int, race discount percentage (0-100)
 *   $context['army_weapons']      - array, attack weapon items
 *   $context['army_armor']        - array, defense armor items
 *   $context['army_spy_tools']    - array, spy tool items
 *   $context['army_sentry_tools'] - array, sentry tool items
 *   $context['army_currency']     - string, currency name
 *   $context['army_session_var']  - string, session variable name
 *   $context['army_session_id']   - string, session token value
 */
function template_army_armor_buy()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_armor_buy'] ?? 'Buy Equipment', '</h3>
			</div>';

	// Shop sub-navigation tabs
	template_army_shop_tabs();

	// Success message
	if (!empty($context['army_buy_success']))
	{
		echo '
			<div class="infobox">
				', $txt['army_buy_success_msg'] ?? 'Equipment purchased successfully!', '
			</div>';
	}

	// Current gold display
	echo '
			<div class="windowbg">
				<strong>', $txt['army_your_gold'] ?? 'Your Gold', ':</strong> ',
				$context['army_gold'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold');

	// Discount note if applicable
	if (!empty($context['army_discount']))
	{
		echo ' &mdash; <span class="army_bonus_positive">',
			sprintf($txt['army_discount_note'] ?? 'Your race gives you a %d%% discount on purchases!', $context['army_discount']),
			'</span>';
	}

	echo '
			</div>';

	// Purchase form wrapping all four categories
	echo '
			<form action="', $scripturl, '?action=army;sa=armor;do=buy" method="post" accept-charset="UTF-8">
				<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">';

	// Define the five categories to display
	$categories = array(
		array(
			'key' => 'army_weapons',
			'title' => $txt['army_section_weapons'] ?? 'Attack Weapons',
		),
		array(
			'key' => 'army_armor',
			'title' => $txt['army_section_armor'] ?? 'Defense Armor',
		),
		array(
			'key' => 'army_spy_tools',
			'title' => $txt['army_section_spy_tools'] ?? 'Spy Tools',
		),
		array(
			'key' => 'army_sentry_tools',
			'title' => $txt['army_section_sentry_tools'] ?? 'Sentry Tools',
		),
		array(
			'key' => 'army_ships',
			'title' => $txt['army_ships'] ?? 'Ships',
		),
	);

	foreach ($categories as $category)
	{
		$items = $context[$category['key']] ?? array();

		if (empty($items))
			continue;

		echo '
				<div class="title_bar">
					<h4 class="titlebg">', $category['title'], '</h4>
				</div>
				<table class="table_grid army_table" width="100%">
					<thead>
						<tr class="title_bar">
							<th>', $txt['army_col_name'] ?? 'Name', '</th>
							<th class="centercol">', $txt['army_col_power'] ?? 'Power', '</th>
							<th class="centercol">', $txt['army_col_price'] ?? 'Price', '</th>
							<th class="centercol">', $txt['army_col_owned'] ?? 'Owned', '</th>
							<th class="centercol" width="15%">', $txt['army_col_buy_qty'] ?? 'Buy Qty', '</th>
						</tr>
					</thead>
					<tbody>';

		foreach ($items as $item)
		{
			echo '
						<tr class="windowbg">
							<td>';

			if (!empty($item['icon']))
				echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($item['icon']), '" alt="" class="army_item_icon"> ';

			echo htmlspecialchars($item['name']), '
							</td>
							<td class="centercol">', $item['value'], '</td>
							<td class="centercol">', $item['price_formatted'], '</td>
							<td class="centercol">', $item['owned_formatted'], '</td>
							<td class="centercol">
								<input type="number" name="buy[', $item['key'], ']" value="0" min="0" size="6" class="input_text army_qty_input">
							</td>
						</tr>';
		}

		echo '
					</tbody>
				</table>';
	}

	// Submit button
	echo '
				<div class="windowbg">
					<div class="righttext">
						<input type="submit" value="', $txt['army_btn_purchase'] ?? 'Purchase', '" class="button">
					</div>
				</div>
			</form>
		</div>
	</div>';
}

/**
 * Sell equipment template.
 *
 * Displays all owned equipment items with their calculated resell values.
 * Weapons and tools sell at the tool_resell rate; armor at the armor_resell
 * rate. Players enter a quantity to sell for each item.
 *
 * Context variables used:
 *   $context['army_shop_tabs']       - array, shop sub-navigation tabs
 *   $context['army_sell_success']    - bool, show sale success message
 *   $context['army_gold']            - string, formatted current gold
 *   $context['army_currency']        - string, currency name
 *   $context['army_owned_items']     - array, owned items with sell prices
 *   $context['army_tool_resell']     - int, tool resell percentage
 *   $context['army_armor_resell']    - int, armor resell percentage
 *   $context['army_session_var']     - string, session variable name
 *   $context['army_session_id']      - string, session token value
 */
function template_army_armor_sell()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_armor_sell'] ?? 'Sell Equipment', '</h3>
			</div>';

	// Shop sub-navigation tabs
	template_army_shop_tabs();

	// Success message
	if (!empty($context['army_sell_success']))
	{
		echo '
			<div class="infobox">
				', $txt['army_sell_success_msg'] ?? 'Equipment sold successfully!', '
			</div>';
	}

	// Current gold display
	echo '
			<div class="windowbg">
				<strong>', $txt['army_your_gold'] ?? 'Your Gold', ':</strong> ',
				$context['army_gold'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold'), '
				<br><span class="smalltext">',
				sprintf(
					$txt['army_resell_rates'] ?? 'Resell rates: Armor at %d%%, Ships at %d%%, Weapons/Tools at %d%%',
					$context['army_armor_resell'] ?? 75,
					$context['army_ship_resell'] ?? 60,
					$context['army_tool_resell'] ?? 45
				), '</span>
			</div>';

	if (empty($context['army_owned_items']))
	{
		echo '
			<div class="windowbg">
				<p class="centertext">', $txt['army_no_items_to_sell'] ?? 'You do not own any equipment to sell.', '</p>
			</div>
		</div>
	</div>';

		return;
	}

	// Sell form
	echo '
			<form action="', $scripturl, '?action=army;sa=armor;do=sell" method="post" accept-charset="UTF-8">
				<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
				<table class="table_grid army_table" width="100%">
					<thead>
						<tr class="title_bar">
							<th>', $txt['army_col_name'] ?? 'Name', '</th>
							<th class="centercol">', $txt['army_col_type'] ?? 'Type', '</th>
							<th class="centercol">', $txt['army_col_quantity'] ?? 'Quantity', '</th>
							<th class="centercol">', $txt['army_col_strength'] ?? 'Strength', '</th>
							<th class="centercol">', $txt['army_col_sell_price'] ?? 'Sell Price Each', '</th>
							<th class="centercol" width="15%">', $txt['army_col_sell_qty'] ?? 'Sell Qty', '</th>
						</tr>
					</thead>
					<tbody>';

	foreach ($context['army_owned_items'] as $item)
	{
		echo '
						<tr class="windowbg">
							<td>';

		if (!empty($item['icon']))
			echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($item['icon']), '" alt="" class="army_item_icon"> ';

		echo htmlspecialchars($item['name']), '
							</td>
							<td class="centercol">', htmlspecialchars($item['section_label']), '</td>
							<td class="centercol">', $item['quantity_formatted'], '</td>
							<td class="centercol">', number_format($item['strength'], 2), ' / ', $item['max_strength'], '</td>
							<td class="centercol">', $item['sell_price_per_unit_formatted'], '
								<span class="smalltext">(', $item['resell_rate'], '%)</span>
							</td>
							<td class="centercol">
								<input type="number" name="sell[', $item['key'], ']" value="0" min="0" max="', $item['quantity'], '" size="6" class="input_text army_qty_input">
							</td>
						</tr>';
	}

	echo '
					</tbody>
				</table>
				<div class="windowbg">
					<div class="righttext">
						<input type="submit" value="', $txt['army_btn_sell'] ?? 'Sell Items', '" class="button">
					</div>
				</div>
			</form>
		</div>
	</div>';
}

/**
 * Repair equipment template.
 *
 * Lists all inventory items whose strength is below maximum, indicating
 * battle damage or sabotage. Shows damage percentages and repair costs.
 * Players select which items to repair via checkboxes.
 *
 * Context variables used:
 *   $context['army_shop_tabs']       - array, shop sub-navigation tabs
 *   $context['army_repair_success']  - bool, show repair success message
 *   $context['army_gold']            - string, formatted current gold
 *   $context['army_currency']        - string, currency name
 *   $context['army_damaged_items']   - array, damaged items with repair costs
 *   $context['army_session_var']     - string, session variable name
 *   $context['army_session_id']      - string, session token value
 */
function template_army_armor_repair()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_armor_repair'] ?? 'Repair Equipment', '</h3>
			</div>';

	// Shop sub-navigation tabs
	template_army_shop_tabs();

	// Success message
	if (!empty($context['army_repair_success']))
	{
		echo '
			<div class="infobox">
				', $txt['army_repair_success_msg'] ?? 'Equipment repaired successfully!', '
			</div>';
	}

	// Current gold display
	echo '
			<div class="windowbg">
				<strong>', $txt['army_your_gold'] ?? 'Your Gold', ':</strong> ',
				$context['army_gold'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold'), '
			</div>';

	// No damaged items
	if (empty($context['army_damaged_items']))
	{
		echo '
			<div class="windowbg">
				<p class="centertext">', $txt['army_no_damaged_items'] ?? 'All your equipment is in good condition. Nothing to repair!', '</p>
			</div>
		</div>
	</div>';

		return;
	}

	// Repair form
	echo '
			<form action="', $scripturl, '?action=army;sa=armor;do=repair" method="post" accept-charset="UTF-8">
				<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
				<table class="table_grid army_table" width="100%">
					<thead>
						<tr class="title_bar">
							<th>', $txt['army_col_name'] ?? 'Name', '</th>
							<th class="centercol">', $txt['army_col_type'] ?? 'Type', '</th>
							<th class="centercol">', $txt['army_col_quantity'] ?? 'Qty', '</th>
							<th class="centercol">', $txt['army_col_current_strength'] ?? 'Current', '</th>
							<th class="centercol">', $txt['army_col_max_strength'] ?? 'Max', '</th>
							<th class="centercol">', $txt['army_col_damage'] ?? 'Damage', '</th>
							<th class="centercol">', $txt['army_col_repair_cost'] ?? 'Repair Cost', '</th>
							<th class="centercol">', $txt['army_col_repair'] ?? 'Repair', '</th>
						</tr>
					</thead>
					<tbody>';

	foreach ($context['army_damaged_items'] as $item)
	{
		echo '
						<tr class="windowbg">
							<td>';

		if (!empty($item['icon']))
			echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($item['icon']), '" alt="" class="army_item_icon"> ';

		echo htmlspecialchars($item['name']), '
							</td>
							<td class="centercol">', htmlspecialchars($item['section_label']), '</td>
							<td class="centercol">', $item['quantity_formatted'], '</td>
							<td class="centercol">', number_format($item['current_strength'], 2), '</td>
							<td class="centercol">', number_format($item['max_strength'], 2), '</td>
							<td class="centercol">
								<span class="army_bonus_negative">', $item['damage_percent'], '%</span>
							</td>
							<td class="centercol">', $item['repair_cost_formatted'], '</td>
							<td class="centercol">
								<input type="checkbox" name="repair[', $item['i_id'], ']" value="1">
							</td>
						</tr>';
	}

	echo '
					</tbody>
				</table>
				<div class="windowbg">
					<div class="righttext">
						<input type="submit" value="', $txt['army_btn_repair'] ?? 'Repair Selected', '" class="button">
					</div>
				</div>
			</form>
		</div>
	</div>';
}

/**
 * Fortification upgrade template.
 *
 * Shows the player's current fort level, the defense bonus provided by
 * forts, a progression list of all fort levels (completed, current, upcoming),
 * and an upgrade button for the next available level.
 *
 * Context variables used:
 *   $context['army_shop_tabs']     - array, shop sub-navigation tabs
 *   $context['army_fort_success']  - bool, show upgrade success message
 *   $context['army_gold']          - string, formatted current gold
 *   $context['army_currency']      - string, currency name
 *   $context['army_fort_current']  - array|null, current fort (level, name, icon)
 *   $context['army_fort_next']     - array|null, next fort (level, name, price, icon)
 *   $context['army_fort_items']    - array, all fort levels for progression display
 *   $context['army_fort_percent']  - int, defense bonus percentage per level
 *   $context['army_session_var']   - string, session variable name
 *   $context['army_session_id']    - string, session token value
 */
function template_army_armor_fort()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_armor_fort'] ?? 'Fortification', '</h3>
			</div>';

	// Shop sub-navigation tabs
	template_army_shop_tabs();

	// Success message
	if (!empty($context['army_fort_success']))
	{
		echo '
			<div class="infobox">
				', $txt['army_fort_success_msg'] ?? 'Fortification upgraded successfully!', '
			</div>';
	}

	// Current gold display
	echo '
			<div class="windowbg">
				<strong>', $txt['army_your_gold'] ?? 'Your Gold', ':</strong> ',
				$context['army_gold'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold'), '
			</div>';

	// Current fort info
	echo '
			<div class="windowbg">';

	if ($context['army_fort_current'] !== null)
	{
		echo '
				<dl class="army_stats_list">
					<dt>', $txt['army_current_fort'] ?? 'Current Fortification', '</dt>
					<dd>';

		if (!empty($context['army_fort_current']['icon']))
			echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($context['army_fort_current']['icon']), '" alt="" class="army_item_icon"> ';

		echo '<strong>', htmlspecialchars($context['army_fort_current']['name']), '</strong>
						(', $txt['army_level'] ?? 'Level', ' ', $context['army_fort_current']['level'], ')
					</dd>
					<dt>', $txt['army_fort_bonus'] ?? 'Defense Bonus', '</dt>
					<dd>',
						sprintf(
							$txt['army_fort_bonus_desc'] ?? '%d%% defense bonus per fortification level',
							$context['army_fort_percent']
						), '
					</dd>
				</dl>';
	}
	else
	{
		echo '
				<p>', $txt['army_no_fort'] ?? 'You have not built any fortifications yet.', '</p>';
	}

	echo '
			</div>';

	// Progression list
	if (!empty($context['army_fort_items']))
	{
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_fort_progression'] ?? 'Fortification Levels', '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th class="centercol" width="10%">', $txt['army_col_level'] ?? 'Level', '</th>
						<th>', $txt['army_col_name'] ?? 'Name', '</th>
						<th class="centercol">', $txt['army_col_price'] ?? 'Price', '</th>
						<th class="centercol" width="15%">', $txt['army_col_status'] ?? 'Status', '</th>
					</tr>
				</thead>
				<tbody>';

		foreach ($context['army_fort_items'] as $fort)
		{
			// Determine the row status
			if (!empty($fort['is_completed']))
				$status_html = '<span class="army_bonus_positive">' . ($txt['army_status_completed'] ?? 'Completed') . '</span>';
			elseif (!empty($fort['is_current']))
				$status_html = '<strong>' . ($txt['army_status_current'] ?? 'Current') . '</strong>';
			elseif (!empty($fort['is_next']))
				$status_html = '<span class="army_bonus_neutral">' . ($txt['army_status_next'] ?? 'Next') . '</span>';
			else
				$status_html = '<span class="smalltext">' . ($txt['army_status_locked'] ?? 'Locked') . '</span>';

			// Determine row class for highlighting
			$row_class = 'windowbg';
			if (!empty($fort['is_current']))
				$row_class = 'windowbg army_row_current';

			echo '
					<tr class="', $row_class, '">
						<td class="centercol">', $fort['level'], '</td>
						<td>';

			if (!empty($fort['icon']))
				echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($fort['icon']), '" alt="" class="army_item_icon"> ';

			echo htmlspecialchars($fort['name']), '
						</td>
						<td class="centercol">';

			if ($fort['price'] > 0)
				echo $fort['price_formatted'];
			else
				echo '<span class="smalltext">', $txt['army_free'] ?? 'Free', '</span>';

			echo '</td>
						<td class="centercol">', $status_html, '</td>
					</tr>';
		}

		echo '
				</tbody>
			</table>';
	}

	// Upgrade button
	if ($context['army_fort_next'] !== null)
	{
		echo '
			<div class="windowbg">
				<form action="', $scripturl, '?action=army;sa=armor;do=fort" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<div class="centertext">
						<p>',
							sprintf(
								$txt['army_fort_upgrade_prompt'] ?? 'Upgrade to <strong>%s</strong> for <strong>%s</strong> Gold',
								htmlspecialchars($context['army_fort_next']['name']),
								$context['army_fort_next']['price_formatted']
							), '
						</p>
						<input type="submit" name="upgrade_fort" value="', $txt['army_btn_upgrade_fort'] ?? 'Upgrade Fortification', '" class="button">
					</div>
				</form>
			</div>';
	}
	else
	{
		echo '
			<div class="windowbg">
				<p class="centertext"><strong>', $txt['army_fort_maxed'] ?? 'Your fortification is at the maximum level!', '</strong></p>
			</div>';
	}

	echo '
		</div>
	</div>';
}

/**
 * Siege technology upgrade template.
 *
 * Same structure as the fortification template but for siege upgrades.
 * Siege technology provides an attack bonus against fortified defenders.
 *
 * Context variables used:
 *   $context['army_shop_tabs']      - array, shop sub-navigation tabs
 *   $context['army_siege_success']  - bool, show upgrade success message
 *   $context['army_gold']           - string, formatted current gold
 *   $context['army_currency']       - string, currency name
 *   $context['army_siege_current']  - array|null, current siege (level, name, icon)
 *   $context['army_siege_next']     - array|null, next siege (level, name, price, icon)
 *   $context['army_siege_items']    - array, all siege levels for progression display
 *   $context['army_siege_percent']  - int, offense bonus percentage per level
 *   $context['army_session_var']    - string, session variable name
 *   $context['army_session_id']     - string, session token value
 */
function template_army_armor_siege()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_armor_siege'] ?? 'Siege Technology', '</h3>
			</div>';

	// Shop sub-navigation tabs
	template_army_shop_tabs();

	// Success message
	if (!empty($context['army_siege_success']))
	{
		echo '
			<div class="infobox">
				', $txt['army_siege_success_msg'] ?? 'Siege technology upgraded successfully!', '
			</div>';
	}

	// Current gold display
	echo '
			<div class="windowbg">
				<strong>', $txt['army_your_gold'] ?? 'Your Gold', ':</strong> ',
				$context['army_gold'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold'), '
			</div>';

	// Current siege info
	echo '
			<div class="windowbg">';

	if ($context['army_siege_current'] !== null)
	{
		echo '
				<dl class="army_stats_list">
					<dt>', $txt['army_current_siege'] ?? 'Current Siege Technology', '</dt>
					<dd>';

		if (!empty($context['army_siege_current']['icon']))
			echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($context['army_siege_current']['icon']), '" alt="" class="army_item_icon"> ';

		echo '<strong>', htmlspecialchars($context['army_siege_current']['name']), '</strong>
						(', $txt['army_level'] ?? 'Level', ' ', $context['army_siege_current']['level'], ')
					</dd>
					<dt>', $txt['army_siege_bonus'] ?? 'Offense Bonus', '</dt>
					<dd>',
						sprintf(
							$txt['army_siege_bonus_desc'] ?? '%d%% offense bonus vs fortified defenders per siege level',
							$context['army_siege_percent']
						), '
					</dd>
				</dl>';
	}
	else
	{
		echo '
				<p>', $txt['army_no_siege'] ?? 'You have not researched any siege technology yet.', '</p>';
	}

	echo '
			</div>';

	// Progression list
	if (!empty($context['army_siege_items']))
	{
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_siege_progression'] ?? 'Siege Technology Levels', '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th class="centercol" width="10%">', $txt['army_col_level'] ?? 'Level', '</th>
						<th>', $txt['army_col_name'] ?? 'Name', '</th>
						<th class="centercol">', $txt['army_col_price'] ?? 'Price', '</th>
						<th class="centercol" width="15%">', $txt['army_col_status'] ?? 'Status', '</th>
					</tr>
				</thead>
				<tbody>';

		foreach ($context['army_siege_items'] as $siege)
		{
			// Determine the row status
			if (!empty($siege['is_completed']))
				$status_html = '<span class="army_bonus_positive">' . ($txt['army_status_completed'] ?? 'Completed') . '</span>';
			elseif (!empty($siege['is_current']))
				$status_html = '<strong>' . ($txt['army_status_current'] ?? 'Current') . '</strong>';
			elseif (!empty($siege['is_next']))
				$status_html = '<span class="army_bonus_neutral">' . ($txt['army_status_next'] ?? 'Next') . '</span>';
			else
				$status_html = '<span class="smalltext">' . ($txt['army_status_locked'] ?? 'Locked') . '</span>';

			// Determine row class for highlighting
			$row_class = 'windowbg';
			if (!empty($siege['is_current']))
				$row_class = 'windowbg army_row_current';

			echo '
					<tr class="', $row_class, '">
						<td class="centercol">', $siege['level'], '</td>
						<td>';

			if (!empty($siege['icon']))
				echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($siege['icon']), '" alt="" class="army_item_icon"> ';

			echo htmlspecialchars($siege['name']), '
						</td>
						<td class="centercol">';

			if ($siege['price'] > 0)
				echo $siege['price_formatted'];
			else
				echo '<span class="smalltext">', $txt['army_free'] ?? 'Free', '</span>';

			echo '</td>
						<td class="centercol">', $status_html, '</td>
					</tr>';
		}

		echo '
				</tbody>
			</table>';
	}

	// Upgrade button
	if ($context['army_siege_next'] !== null)
	{
		echo '
			<div class="windowbg">
				<form action="', $scripturl, '?action=army;sa=armor;do=siege" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<div class="centertext">
						<p>',
							sprintf(
								$txt['army_siege_upgrade_prompt'] ?? 'Upgrade to <strong>%s</strong> for <strong>%s</strong> Gold',
								htmlspecialchars($context['army_siege_next']['name']),
								$context['army_siege_next']['price_formatted']
							), '
						</p>
						<input type="submit" name="upgrade_siege" value="', $txt['army_btn_upgrade_siege'] ?? 'Upgrade Siege Technology', '" class="button">
					</div>
				</form>
			</div>';
	}
	else
	{
		echo '
			<div class="windowbg">
				<p class="centertext"><strong>', $txt['army_siege_maxed'] ?? 'Your siege technology is at the maximum level!', '</strong></p>
			</div>';
	}

	echo '
		</div>
	</div>';
}

/**
 * Ships buy template.
 *
 * Displays all available ships with purchase options. Ships contribute
 * to both attack and defense power. Uses larger icons for the ship art.
 */
function template_army_armor_ships()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_ships'] ?? 'Ships', '</h3>
			</div>';

	// Shop sub-navigation tabs
	template_army_shop_tabs();

	// Success message
	if (!empty($context['army_buy_success']))
	{
		echo '
			<div class="infobox">
				', $txt['army_ship_buy_success'] ?? 'Ships purchased successfully!', '
			</div>';
	}

	// Current gold display
	echo '
			<div class="windowbg">
				<strong>', $txt['army_your_gold'] ?? 'Your Gold', ':</strong> ',
				$context['army_gold'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold');

	if (!empty($context['army_discount']))
	{
		echo ' &mdash; <span class="army_bonus_positive">',
			sprintf($txt['army_discount_note'] ?? 'Your race gives you a %d%% discount on purchases!', $context['army_discount']),
			'</span>';
	}

	echo '
				<br><span class="smalltext">', $txt['army_ships_desc'] ?? 'Ships contribute to both attack and defense power.', '</span>
			</div>';

	if (empty($context['army_ships']))
	{
		echo '
			<div class="windowbg">
				<p class="centertext">', $txt['army_no_ships'] ?? 'No ships available.', '</p>
			</div>
		</div>
	</div>';

		return;
	}

	// Purchase form
	echo '
			<form action="', $scripturl, '?action=army;sa=armor;do=ships" method="post" accept-charset="UTF-8">
				<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
				<table class="table_grid army_table" width="100%">
					<thead>
						<tr class="title_bar">
							<th>', $txt['army_col_name'] ?? 'Name', '</th>
							<th class="centercol">', $txt['army_col_power'] ?? 'Power', '</th>
							<th class="centercol">', $txt['army_col_price'] ?? 'Price', '</th>
							<th class="centercol">', $txt['army_col_owned'] ?? 'Owned', '</th>
							<th class="centercol" width="15%">', $txt['army_col_buy_qty'] ?? 'Buy Qty', '</th>
						</tr>
					</thead>
					<tbody>';

	foreach ($context['army_ships'] as $item)
	{
		echo '
						<tr class="windowbg">
							<td>';

		if (!empty($item['icon']))
			echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($item['icon']), '" alt="" class="army_ship_icon"> ';

		echo htmlspecialchars($item['name']), '
							</td>
							<td class="centercol">', $item['value'], '</td>
							<td class="centercol">', $item['price_formatted'], '</td>
							<td class="centercol">', $item['owned_formatted'], '</td>
							<td class="centercol">
								<input type="number" name="buy[', $item['key'], ']" value="0" min="0" size="6" class="input_text army_qty_input">
							</td>
						</tr>';
	}

	echo '
					</tbody>
				</table>
				<div class="windowbg">
					<div class="righttext">
						<input type="submit" value="', $txt['army_btn_purchase'] ?? 'Purchase', '" class="button">
					</div>
				</div>
			</form>
		</div>
	</div>';
}
