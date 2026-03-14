<?php
/**
 * Army System - Mercenaries Template
 *
 * Provides the template function for the mercenary management page where
 * players can hire, fire, and retrain mercenaries. Mercenaries add combat
 * strength without consuming the army's soldier pool.
 *
 * @package ArmySystem
 * @version 1.0
 */

/**
 * Mercenary management page template.
 *
 * Displays the player's current mercenary counts, hiring forms for each
 * mercenary type, firing forms, and retraining options for converting
 * untrained mercenaries into specialized roles.
 *
 * Each action (hire, fire, retrain) is a separate form to allow
 * independent submissions.
 *
 * Context variables used:
 *   $context['army_member']         - array, player's army data with merc counts
 *   $context['army_merc_prices']    - array, merc prices per type
 *   $context['army_merc_strength']  - array, merc strength values per type
 *   $context['army_gold']           - string, formatted current gold
 *   $context['army_gold_raw']       - int, raw current gold
 *   $context['army_currency']       - string, currency name
 *   $context['army_merc_success']   - bool, show action success message
 *   $context['army_session_var']    - string, session variable name
 *   $context['army_session_id']     - string, session token value
 */
function template_army_mercenaries()
{
	global $context, $txt, $scripturl;

	$member = $context['army_member'];
	$prices = $context['army_merc_prices'];
	$strength = $context['army_merc_strength'];

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_mercs_title'] ?? 'Mercenaries', '</h3>
			</div>';

	// Success message
	if (!empty($context['army_merc_success']))
	{
		echo '
			<div class="infobox">
				', $txt['army_merc_success_msg'] ?? 'Mercenary action completed successfully!', '
			</div>';
	}

	// Current gold display
	echo '
			<div class="windowbg">
				<strong>', $txt['army_your_gold'] ?? 'Your Gold', ':</strong> ',
				$context['army_gold'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold'), '
			</div>';

	// --- Mercenary Overview ---
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_merc_overview'] ?? 'Mercenary Overview', '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th>', $txt['army_col_type'] ?? 'Type', '</th>
						<th class="centercol">', $txt['army_col_count'] ?? 'Count', '</th>
						<th class="centercol">', $txt['army_col_strength_each'] ?? 'Strength Each', '</th>
					</tr>
				</thead>
				<tbody>
					<tr class="windowbg">
						<td>';

	if (!empty($member['merc_atk_icon']))
		echo '<img src="', htmlspecialchars($member['merc_atk_icon']), '" alt="" class="army_item_icon"> ';

	echo $txt['army_mercs_attack'] ?? 'Attack Mercenaries', '
						</td>
						<td class="centercol">', $member['mercs_attack_formatted'], '</td>
						<td class="centercol">', $strength['attack'], '</td>
					</tr>
					<tr class="windowbg">
						<td>';

	if (!empty($member['merc_def_icon']))
		echo '<img src="', htmlspecialchars($member['merc_def_icon']), '" alt="" class="army_item_icon"> ';

	echo $txt['army_mercs_defense'] ?? 'Defense Mercenaries', '
						</td>
						<td class="centercol">', $member['mercs_defense_formatted'], '</td>
						<td class="centercol">', $strength['defense'], '</td>
					</tr>
					<tr class="windowbg">
						<td>';

	if (!empty($member['merc_icon']))
		echo '<img src="', htmlspecialchars($member['merc_icon']), '" alt="" class="army_item_icon"> ';

	echo $txt['army_mercs_untrained'] ?? 'Untrained Mercenaries', '
						</td>
						<td class="centercol">', $member['mercs_untrained_formatted'], '</td>
						<td class="centercol">', $strength['untrained'], '</td>
					</tr>
				</tbody>
			</table>';

	// --- Hire Mercenaries ---
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_merc_hire'] ?? 'Hire Mercenaries', '</h4>
			</div>
			<div class="windowbg">
				<p class="smalltext">', $txt['army_merc_hire_desc'] ?? 'Hire new mercenaries to bolster your forces. Each mercenary costs gold to recruit.', '</p>
			</div>';

	// Hire Attack Mercs
	echo '
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=mercs" method="post" accept-charset="UTF-8" class="army_inline_form">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="merc_action" value="hire_attack">';

	if (!empty($member['merc_atk_icon']))
		echo '
					<img src="', htmlspecialchars($member['merc_atk_icon']), '" alt="" class="army_item_icon">';

	echo '
					<strong>', htmlspecialchars($prices['hire_attack']['label']), ':</strong>
					<input type="number" name="merc_count" value="0" min="0" size="8" class="input_text army_qty_input">
					&mdash; ', $txt['army_cost'] ?? 'Cost', ': ', $prices['hire_attack']['price_formatted'], ' ', $txt['army_each'] ?? 'each', ',
					', $txt['army_strength'] ?? 'Strength', ': ', $strength['attack'], '
					<input type="submit" value="', $txt['army_btn_hire'] ?? 'Hire', '" class="button">
				</form>
			</div>';

	// Hire Defense Mercs
	echo '
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=mercs" method="post" accept-charset="UTF-8" class="army_inline_form">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="merc_action" value="hire_defense">';

	if (!empty($member['merc_def_icon']))
		echo '
					<img src="', htmlspecialchars($member['merc_def_icon']), '" alt="" class="army_item_icon">';

	echo '
					<strong>', htmlspecialchars($prices['hire_defense']['label']), ':</strong>
					<input type="number" name="merc_count" value="0" min="0" size="8" class="input_text army_qty_input">
					&mdash; ', $txt['army_cost'] ?? 'Cost', ': ', $prices['hire_defense']['price_formatted'], ' ', $txt['army_each'] ?? 'each', ',
					', $txt['army_strength'] ?? 'Strength', ': ', $strength['defense'], '
					<input type="submit" value="', $txt['army_btn_hire'] ?? 'Hire', '" class="button">
				</form>
			</div>';

	// Hire Untrained Mercs
	echo '
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=mercs" method="post" accept-charset="UTF-8" class="army_inline_form">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="merc_action" value="hire_untrained">';

	if (!empty($member['merc_icon']))
		echo '
					<img src="', htmlspecialchars($member['merc_icon']), '" alt="" class="army_item_icon">';

	echo '
					<strong>', htmlspecialchars($prices['hire_untrained']['label']), ':</strong>
					<input type="number" name="merc_count" value="0" min="0" size="8" class="input_text army_qty_input">
					&mdash; ', $txt['army_cost'] ?? 'Cost', ': ', $prices['hire_untrained']['price_formatted'], ' ', $txt['army_each'] ?? 'each', ',
					', $txt['army_strength'] ?? 'Strength', ': ', $strength['untrained'], '
					<input type="submit" value="', $txt['army_btn_hire'] ?? 'Hire', '" class="button">
				</form>
			</div>';

	// --- Fire Mercenaries ---
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_merc_fire'] ?? 'Fire Mercenaries', '</h4>
			</div>
			<div class="windowbg">
				<p class="smalltext">', $txt['army_merc_fire_desc'] ?? 'Dismiss mercenaries from your army. Fired mercenaries provide no refund.', '</p>
			</div>';

	// Fire Attack Mercs
	echo '
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=mercs" method="post" accept-charset="UTF-8" class="army_inline_form">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="merc_action" value="fire_attack">
					<strong>', $txt['army_merc_fire_attack'] ?? 'Fire Attack Mercs', ':</strong>
					<input type="number" name="merc_count" value="0" min="0" max="', $member['mercs_attack'], '" size="8" class="input_text army_qty_input">
					<span class="smalltext">(', sprintf($txt['army_available_count'] ?? '%s available', $member['mercs_attack_formatted']), ')</span>
					<span class="army_bonus_negative">', $txt['army_no_refund'] ?? 'No refund', '</span>
					<input type="submit" value="', $txt['army_btn_fire'] ?? 'Fire', '" class="button" onclick="return confirm(\'', ($txt['army_merc_fire_confirm'] ?? 'Are you sure? Fired mercenaries provide no gold refund.'), '\');">
				</form>
			</div>';

	// Fire Defense Mercs
	echo '
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=mercs" method="post" accept-charset="UTF-8" class="army_inline_form">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="merc_action" value="fire_defense">
					<strong>', $txt['army_merc_fire_defense'] ?? 'Fire Defense Mercs', ':</strong>
					<input type="number" name="merc_count" value="0" min="0" max="', $member['mercs_defense'], '" size="8" class="input_text army_qty_input">
					<span class="smalltext">(', sprintf($txt['army_available_count'] ?? '%s available', $member['mercs_defense_formatted']), ')</span>
					<span class="army_bonus_negative">', $txt['army_no_refund'] ?? 'No refund', '</span>
					<input type="submit" value="', $txt['army_btn_fire'] ?? 'Fire', '" class="button" onclick="return confirm(\'', ($txt['army_merc_fire_confirm'] ?? 'Are you sure? Fired mercenaries provide no gold refund.'), '\');">
				</form>
			</div>';

	// Fire Untrained Mercs
	echo '
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=mercs" method="post" accept-charset="UTF-8" class="army_inline_form">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="merc_action" value="fire_untrained">
					<strong>', $txt['army_merc_fire_untrained'] ?? 'Fire Untrained Mercs', ':</strong>
					<input type="number" name="merc_count" value="0" min="0" max="', $member['mercs_untrained'], '" size="8" class="input_text army_qty_input">
					<span class="smalltext">(', sprintf($txt['army_available_count'] ?? '%s available', $member['mercs_untrained_formatted']), ')</span>
					<span class="army_bonus_negative">', $txt['army_no_refund'] ?? 'No refund', '</span>
					<input type="submit" value="', $txt['army_btn_fire'] ?? 'Fire', '" class="button" onclick="return confirm(\'', ($txt['army_merc_fire_confirm'] ?? 'Are you sure? Fired mercenaries provide no gold refund.'), '\');">
				</form>
			</div>';

	// --- Retrain Mercenaries ---
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_merc_retrain'] ?? 'Retrain Mercenaries', '</h4>
			</div>
			<div class="windowbg">
				<p class="smalltext">', $txt['army_merc_retrain_desc'] ?? 'Convert untrained mercenaries into specialized attack or defense roles. Retraining costs the same as hiring a specialized mercenary.', '</p>
			</div>';

	// Train Untrained Mercs to Attack
	echo '
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=mercs" method="post" accept-charset="UTF-8" class="army_inline_form">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="merc_action" value="train_merc_attack">
					<strong>', htmlspecialchars($prices['train_merc_attack']['label']), ':</strong>
					<input type="number" name="merc_count" value="0" min="0" max="', $member['mercs_untrained'], '" size="8" class="input_text army_qty_input">
					&mdash; ', $txt['army_cost'] ?? 'Cost', ': ', $prices['train_merc_attack']['price_formatted'], ' ', $txt['army_per_unit'] ?? 'per unit', '
					<span class="smalltext">(', sprintf($txt['army_untrained_available'] ?? '%s untrained mercs available', $member['mercs_untrained_formatted']), ')</span>
					<input type="submit" value="', $txt['army_btn_train'] ?? 'Train', '" class="button">
				</form>
			</div>';

	// Train Untrained Mercs to Defense
	echo '
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=mercs" method="post" accept-charset="UTF-8" class="army_inline_form">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="merc_action" value="train_merc_defense">
					<strong>', htmlspecialchars($prices['train_merc_defense']['label']), ':</strong>
					<input type="number" name="merc_count" value="0" min="0" max="', $member['mercs_untrained'], '" size="8" class="input_text army_qty_input">
					&mdash; ', $txt['army_cost'] ?? 'Cost', ': ', $prices['train_merc_defense']['price_formatted'], ' ', $txt['army_per_unit'] ?? 'per unit', '
					<span class="smalltext">(', sprintf($txt['army_untrained_available'] ?? '%s untrained mercs available', $member['mercs_untrained_formatted']), ')</span>
					<input type="submit" value="', $txt['army_btn_train'] ?? 'Train', '" class="button">
				</form>
			</div>
		</div>
	</div>';
}
