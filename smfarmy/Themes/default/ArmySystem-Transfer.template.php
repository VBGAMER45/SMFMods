<?php
/**
 * Army System - Transfer Services Template
 *
 * Provides the template function for the transfer page where players can
 * send gold and weapons to other army members. Displays tabbed sections
 * for money transfers and weapon transfers.
 *
 * @package ArmySystem
 * @version 1.0
 */

/**
 * Transfer page template.
 *
 * Displays transfer tabs (Money, Weapons, etc.) based on available
 * transfer services. The money tab shows a simple amount input; the
 * weapons tab shows a table of owned items with quantity inputs.
 *
 * Context variables used:
 *   $context['army_transfer_tabs']      - array, available transfer types
 *     ['key']       - string, tab identifier
 *     ['label']     - string, tab display label
 *     ['url']       - string, tab URL
 *     ['active']    - bool
 *   $context['army_transfer_active']    - string, active tab key
 *   $context['army_transfer_success']   - string|null, success message
 *   $context['army_transfer_error']     - string|null, error message
 *   $context['army_targets']            - array, eligible transfer targets
 *   $context['army_gold']               - string, formatted current gold
 *   $context['army_gold_raw']           - int, raw current gold
 *   $context['army_currency']           - string, currency name
 *   $context['army_owned_weapons']      - array|null, owned items for weapon transfer
 *     ['key']              - string, item identifier
 *     ['name']             - string, item name
 *     ['icon']             - string, item icon URL
 *     ['quantity']         - int, owned quantity
 *     ['quantity_formatted'] - string, formatted quantity
 *     ['section_label']    - string, section type label
 *   $context['army_session_var']        - string, session variable name
 *   $context['army_session_id']         - string, session token value
 */
function template_army_transfer()
{
	global $context, $txt, $scripturl;

	$active_tab = $context['army_transfer_active'] ?? 'money';

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_transfer_title'] ?? 'Transfer Services', '</h3>
			</div>';

	// Transfer tabs
	if (!empty($context['army_transfer_tabs']))
	{
		echo '
			<div class="buttonlist army_transfer_tabs">
				<ul>';

		foreach ($context['army_transfer_tabs'] as $tab)
		{
			$active_class = !empty($tab['active']) ? ' active' : '';

			echo '
					<li>
						<a class="button', $active_class, '" href="', $tab['url'], '">', htmlspecialchars($tab['label']), '</a>
					</li>';
		}

		echo '
				</ul>
			</div>';
	}

	// Error message
	if (!empty($context['army_transfer_error']))
	{
		echo '
			<div class="errorbox">
				', $context['army_transfer_error'], '
			</div>';
	}

	// Success message
	if (!empty($context['army_transfer_success']))
	{
		echo '
			<div class="infobox">
				', $context['army_transfer_success'], '
			</div>';
	}

	// Current gold display
	echo '
			<div class="windowbg">
				<strong>', $txt['army_your_gold'] ?? 'Your Gold', ':</strong> ',
				$context['army_gold'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold'), '
			</div>';

	// No targets available
	if (empty($context['army_targets']))
	{
		echo '
			<div class="windowbg">
				<p class="centertext">', $txt['army_no_transfer_targets'] ?? 'There are no eligible players to transfer to at this time.', '</p>
			</div>
		</div>
	</div>';

		return;
	}

	// --- Money Transfer Tab ---
	if ($active_tab === 'money')
	{
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_transfer_money'] ?? 'Money Transfer', '</h4>
			</div>
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=transfer" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="transfer_type" value="money">
					<dl class="settings">
						<dt>
							<label for="transfer_target">', $txt['army_transfer_recipient'] ?? 'Recipient', '</label>
						</dt>
						<dd>
							<select name="target" id="transfer_target">';

		foreach ($context['army_targets'] as $target)
		{
			echo '
								<option value="', $target['id'], '">', htmlspecialchars($target['name']), '</option>';
		}

		echo '
							</select>
						</dd>
						<dt>
							<label for="transfer_amount">', $txt['army_transfer_amount'] ?? 'Amount', '</label>
							<br>
							<span class="smalltext">', sprintf($txt['army_transfer_available'] ?? 'You have %s %s available.', $context['army_gold'], htmlspecialchars($context['army_currency'] ?? 'Gold')), '</span>
						</dt>
						<dd>
							<input type="number" name="amount" id="transfer_amount" value="0" min="1" max="', $context['army_gold_raw'], '" size="12" class="input_text">
							', htmlspecialchars($context['army_currency'] ?? 'Gold'), '
						</dd>
					</dl>
					<div class="righttext">
						<input type="submit" name="do_transfer" value="', $txt['army_btn_send_gold'] ?? 'Send Gold', '" class="button" onclick="return confirm(\'', ($txt['army_transfer_confirm'] ?? 'Are you sure you want to send this gold? This cannot be undone.'), '\');">
					</div>
				</form>
			</div>';
	}

	// --- Weapons Transfer Tab ---
	if ($active_tab === 'weapons')
	{
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_transfer_weapons'] ?? 'Weapons Transfer', '</h4>
			</div>';

		if (empty($context['army_owned_weapons']))
		{
			echo '
			<div class="windowbg">
				<p class="centertext">', $txt['army_no_weapons_to_transfer'] ?? 'You do not own any weapons or equipment to transfer.', '</p>
			</div>';
		}
		else
		{
			echo '
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=transfer" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="transfer_type" value="weapons">
					<dl class="settings">
						<dt>
							<label for="weapon_transfer_target">', $txt['army_transfer_recipient'] ?? 'Recipient', '</label>
						</dt>
						<dd>
							<select name="target" id="weapon_transfer_target">';

			foreach ($context['army_targets'] as $target)
			{
				echo '
								<option value="', $target['id'], '">', htmlspecialchars($target['name']), '</option>';
			}

			echo '
							</select>
						</dd>
					</dl>
					<table class="table_grid army_table" width="100%">
						<thead>
							<tr class="title_bar">
								<th>', $txt['army_col_item'] ?? 'Item', '</th>
								<th class="centercol">', $txt['army_col_type'] ?? 'Type', '</th>
								<th class="centercol">', $txt['army_col_owned'] ?? 'Owned', '</th>
								<th class="centercol" width="15%">', $txt['army_col_transfer_qty'] ?? 'Transfer Qty', '</th>
							</tr>
						</thead>
						<tbody>';

			foreach ($context['army_owned_weapons'] as $item)
			{
				echo '
							<tr class="windowbg">
								<td>';

				if (!empty($item['icon']))
					echo '<img src="', htmlspecialchars($item['icon']), '" alt="" class="army_item_icon"> ';

				echo htmlspecialchars($item['name']), '
								</td>
								<td class="centercol">', htmlspecialchars($item['section_label']), '</td>
								<td class="centercol">', $item['quantity_formatted'], '</td>
								<td class="centercol">
									<input type="number" name="transfer[', $item['key'], ']" value="0" min="0" max="', $item['quantity'], '" size="6" class="input_text army_qty_input">
								</td>
							</tr>';
			}

			echo '
						</tbody>
					</table>
					<div class="windowbg">
						<div class="righttext">
							<input type="submit" name="do_transfer" value="', $txt['army_btn_send_weapons'] ?? 'Send Weapons', '" class="button" onclick="return confirm(\'', ($txt['army_transfer_weapon_confirm'] ?? 'Are you sure you want to transfer these weapons? This cannot be undone.'), '\');">
						</div>
					</div>
				</form>
			</div>';
		}
	}

	echo '
		</div>
	</div>';
}
