<?php
/**
 * Army System - Soldier Training Template
 *
 * Provides the template function for the training page where players can
 * train/untrain soldiers between specializations (attack, defense, spy,
 * sentry) and upgrade their unit production level.
 *
 * @package ArmySystem
 * @version 1.0
 */

/**
 * Training page template.
 *
 * Displays the player's soldier distribution, training/untraining forms
 * for each soldier type, and the unit production upgrade section.
 *
 * Each train/untrain action is a separate form to allow independent
 * submissions. The unit production upgrade is also a separate form.
 *
 * Context variables used:
 *   $context['army_member']          - array, player's army data with soldier counts
 *   $context['army_train_prices']    - array, training prices per type
 *   $context['army_prod_current']    - array|null, current production level
 *   $context['army_prod_next']       - array|null, next production level
 *   $context['army_gold']            - string, formatted current gold
 *   $context['army_gold_raw']        - int, raw current gold
 *   $context['army_currency']        - string, currency name
 *   $context['army_train_success']   - bool, show training success message
 *   $context['army_upgrade_success'] - bool, show upgrade success message
 *   $context['army_session_var']     - string, session variable name
 *   $context['army_session_id']      - string, session token value
 */
function template_army_training()
{
	global $context, $txt, $scripturl;

	$member = $context['army_member'];
	$prices = $context['army_train_prices'];

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_training_title'] ?? 'Soldier Training', '</h3>
			</div>';

	// Success messages
	if (!empty($context['army_train_success']))
	{
		echo '
			<div class="infobox">
				', $txt['army_train_success_msg'] ?? 'Soldiers trained successfully!', '
			</div>';
	}

	if (!empty($context['army_upgrade_success']))
	{
		echo '
			<div class="infobox">
				', $txt['army_upgrade_success_msg'] ?? 'Unit production upgraded successfully!', '
			</div>';
	}

	// Current gold display
	echo '
			<div class="windowbg">
				<strong>', $txt['army_your_gold'] ?? 'Your Gold', ':</strong> ',
				$context['army_gold'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold'), '
			</div>';

	// --- Soldier Overview ---
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_soldier_overview'] ?? 'Soldier Overview', '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th>', $txt['army_col_type'] ?? 'Type', '</th>
						<th class="centercol">', $txt['army_col_count'] ?? 'Count', '</th>
					</tr>
				</thead>
				<tbody>
					<tr class="windowbg">
						<td>';

	if (!empty($member['train_atk_icon']))
		echo '<img src="', htmlspecialchars($member['train_atk_icon']), '" alt="" class="army_item_icon"> ';

	echo $txt['army_soldiers_attack'] ?? 'Attack Soldiers', '
						</td>
						<td class="centercol">', $member['soldiers_attack_formatted'], '</td>
					</tr>
					<tr class="windowbg">
						<td>';

	if (!empty($member['train_def_icon']))
		echo '<img src="', htmlspecialchars($member['train_def_icon']), '" alt="" class="army_item_icon"> ';

	echo $txt['army_soldiers_defense'] ?? 'Defense Soldiers', '
						</td>
						<td class="centercol">', $member['soldiers_defense_formatted'], '</td>
					</tr>
					<tr class="windowbg">
						<td>';

	if (!empty($member['spy_icon']))
		echo '<img src="', htmlspecialchars($member['spy_icon']), '" alt="" class="army_item_icon"> ';

	echo $txt['army_soldiers_spy'] ?? 'Spy Soldiers', '
						</td>
						<td class="centercol">', $member['soldiers_spy_formatted'], '</td>
					</tr>
					<tr class="windowbg">
						<td>';

	if (!empty($member['sentry_icon']))
		echo '<img src="', htmlspecialchars($member['sentry_icon']), '" alt="" class="army_item_icon"> ';

	echo $txt['army_soldiers_sentry'] ?? 'Sentry Soldiers', '
						</td>
						<td class="centercol">', $member['soldiers_sentry_formatted'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_soldiers_untrained'] ?? 'Untrained Soldiers', '</strong></td>
						<td class="centercol"><strong>', $member['soldiers_untrained_formatted'], '</strong></td>
					</tr>
				</tbody>
			</table>';

	// --- Train Soldiers ---
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_train_soldiers'] ?? 'Train Soldiers', '</h4>
			</div>
			<div class="windowbg">
				<p class="smalltext">', $txt['army_train_desc'] ?? 'Training converts untrained soldiers into specialized roles. You must have enough untrained soldiers and gold to cover the training cost.', '</p>
			</div>';

	// Train Attack
	$train_types = array(
		'train_attack' => array(
			'action' => 'train_attack',
			'label' => $prices['train_attack']['label'],
			'price_formatted' => $prices['train_attack']['price_formatted'],
			'available' => $member['soldiers_untrained'],
			'icon_key' => 'train_atk_icon',
		),
		'train_defense' => array(
			'action' => 'train_defense',
			'label' => $prices['train_defense']['label'],
			'price_formatted' => $prices['train_defense']['price_formatted'],
			'available' => $member['soldiers_untrained'],
			'icon_key' => 'train_def_icon',
		),
		'train_spy' => array(
			'action' => 'train_spy',
			'label' => $prices['train_spy']['label'],
			'price_formatted' => $prices['train_spy']['price_formatted'],
			'available' => $member['soldiers_untrained'],
			'icon_key' => 'spy_icon',
		),
		'train_sentry' => array(
			'action' => 'train_sentry',
			'label' => $prices['train_sentry']['label'],
			'price_formatted' => $prices['train_sentry']['price_formatted'],
			'available' => $member['soldiers_untrained'],
			'icon_key' => 'sentry_icon',
		),
	);

	foreach ($train_types as $key => $train)
	{
		echo '
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=training" method="post" accept-charset="UTF-8" class="army_inline_form">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="train_action" value="', $train['action'], '">';

		if (!empty($member[$train['icon_key']]))
			echo '
					<img src="', htmlspecialchars($member[$train['icon_key']]), '" alt="" class="army_item_icon">';

		echo '
					<strong>', htmlspecialchars($train['label']), ':</strong>
					<input type="number" name="train_count" value="0" min="0" max="', $train['available'], '" size="8" class="input_text army_qty_input">
					', $txt['army_soldiers_label'] ?? 'soldiers', '
					&mdash; ', $txt['army_cost'] ?? 'Cost', ': ', $train['price_formatted'], ' ', $txt['army_per_unit'] ?? 'per unit', '
					<span class="smalltext">(', sprintf($txt['army_available_count'] ?? '%s available', $member['soldiers_untrained_formatted']), ')</span>
					<input type="submit" value="', $txt['army_btn_train'] ?? 'Train', '" class="button">
				</form>
			</div>';
	}

	// --- Untrain Soldiers ---
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_untrain_soldiers'] ?? 'Untrain Soldiers', '</h4>
			</div>
			<div class="windowbg">
				<p class="smalltext">', $txt['army_untrain_desc'] ?? 'Untraining returns specialized soldiers to the untrained pool. Untraining costs less than training.', '</p>
			</div>';

	$untrain_types = array(
		'untrain_attack' => array(
			'action' => 'untrain_attack',
			'label' => $txt['army_untrain_attack'] ?? 'Untrain Attack',
			'available' => $member['soldiers_attack'],
			'available_formatted' => $member['soldiers_attack_formatted'],
		),
		'untrain_defense' => array(
			'action' => 'untrain_defense',
			'label' => $txt['army_untrain_defense'] ?? 'Untrain Defense',
			'available' => $member['soldiers_defense'],
			'available_formatted' => $member['soldiers_defense_formatted'],
		),
		'untrain_spy' => array(
			'action' => 'untrain_spy',
			'label' => $txt['army_untrain_spy'] ?? 'Untrain Spy',
			'available' => $member['soldiers_spy'],
			'available_formatted' => $member['soldiers_spy_formatted'],
		),
		'untrain_sentry' => array(
			'action' => 'untrain_sentry',
			'label' => $txt['army_untrain_sentry'] ?? 'Untrain Sentry',
			'available' => $member['soldiers_sentry'],
			'available_formatted' => $member['soldiers_sentry_formatted'],
		),
	);

	foreach ($untrain_types as $key => $untrain)
	{
		echo '
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=training" method="post" accept-charset="UTF-8" class="army_inline_form">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="train_action" value="', $untrain['action'], '">
					<strong>', htmlspecialchars($untrain['label']), ':</strong>
					<input type="number" name="train_count" value="0" min="0" max="', $untrain['available'], '" size="8" class="input_text army_qty_input">
					', $txt['army_soldiers_label'] ?? 'soldiers', '
					&mdash; ', $txt['army_cost'] ?? 'Cost', ': ', $prices['untrain']['price_formatted'], ' ', $txt['army_per_unit'] ?? 'per unit', '
					<span class="smalltext">(', sprintf($txt['army_available_count'] ?? '%s available', $untrain['available_formatted']), ')</span>
					<input type="submit" value="', $txt['army_btn_untrain'] ?? 'Untrain', '" class="button">
				</form>
			</div>';
	}

	// --- Unit Production Upgrade ---
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_unit_production'] ?? 'Unit Production Upgrade', '</h4>
			</div>
			<div class="windowbg">';

	// Current level info
	if ($context['army_prod_current'] !== null)
	{
		echo '
				<dl class="army_stats_list">
					<dt>', $txt['army_prod_current_level'] ?? 'Current Production Level', '</dt>
					<dd><strong>', htmlspecialchars($context['army_prod_current']['name']), '</strong>
						(', $txt['army_level'] ?? 'Level', ' ', $context['army_prod_current']['level'], ')
					</dd>
				</dl>';
	}

	// Next level upgrade button
	if ($context['army_prod_next'] !== null)
	{
		echo '
				<form action="', $scripturl, '?action=army;sa=training" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="train_action" value="upgrade_production">
					<p>',
						sprintf(
							$txt['army_prod_upgrade_prompt'] ?? 'Upgrade to <strong>%s</strong> (Level %d) for <strong>%s</strong> Gold',
							htmlspecialchars($context['army_prod_next']['name']),
							$context['army_prod_next']['level'],
							$context['army_prod_next']['price_formatted']
						), '
					</p>
					<input type="submit" value="', $txt['army_btn_upgrade_production'] ?? 'Upgrade Production', '" class="button">
				</form>';
	}
	else
	{
		echo '
				<p><strong>', $txt['army_prod_maxed'] ?? 'Unit production is at the maximum level!', '</strong></p>';
	}

	echo '
			</div>
		</div>
	</div>';
}
