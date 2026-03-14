<?php
/**
 * Army System - Attack & Battle Templates
 *
 * Provides template functions for the attack page (target selection, turns),
 * battle result display, the paginated attack log, and detailed attack log
 * view showing full battle breakdowns including per-item equipment data.
 *
 * @package ArmySystem
 * @version 1.0
 */

/**
 * Attack form template.
 *
 * Displays a target selector dropdown, turns input, and a preview of the
 * player's current offensive and defensive power. Submits to the attack
 * action for battle calculation.
 *
 * Context variables used:
 *   $context['army_targets']        - array, eligible targets with id/name
 *   $context['army_member']         - array, current player's army data
 *   $context['army_attack_power']   - string, formatted attack power
 *   $context['army_defense_power']  - string, formatted defense power
 *   $context['army_turns_max']      - int, maximum turns per attack
 *   $context['army_prefill_target']      - int|null, pre-selected target id
 *   $context['army_currency']       - string, currency name
 *   $context['army_session_var']    - string, session variable name
 *   $context['army_session_id']     - string, session token value
 */
function template_army_attack()
{
	global $context, $txt, $scripturl;

	$member = $context['army_member'];
	$max_turns = $context['army_turns_max'] ?? 10;
	$preselect = $context['army_prefill_target'] ?? 0;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_attack_title'] ?? 'Attack', '</h3>
			</div>';

	// Error message
	if (!empty($context['army_attack_error']))
	{
		echo '
			<div class="errorbox">
				', $context['army_attack_error'], '
			</div>';
	}

	// No targets available
	if (empty($context['army_targets']))
	{
		echo '
			<div class="windowbg">
				<p class="centertext">', $txt['army_no_targets'] ?? 'There are no valid targets available to attack at this time.', '</p>
			</div>
		</div>
	</div>';

		return;
	}

	// Power preview
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_your_power'] ?? 'Your Power', '</h4>
			</div>
			<div class="windowbg">
				<div class="army_stats_grid">
					<div class="army_stats_section">
						<dl class="army_stats_list">
							<dt>', $txt['army_attack_power'] ?? 'Attack Power', '</dt>
							<dd><strong>', $context['army_attack_power'], '</strong></dd>
							<dt>', $txt['army_defense_power'] ?? 'Defense Power', '</dt>
							<dd><strong>', $context['army_defense_power'], '</strong></dd>
						</dl>
					</div>
					<div class="army_stats_section">
						<dl class="army_stats_list">
							<dt>', $txt['army_attack_turns'] ?? 'Attack Turns', '</dt>
							<dd><strong>', $member['attack_turns'], '</strong></dd>
							<dt>', $txt['army_army_size'] ?? 'Army Size', '</dt>
							<dd>', $member['army_size'], '</dd>
						</dl>
					</div>
				</div>
			</div>';

	// Attack form
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_launch_attack'] ?? 'Launch Attack', '</h4>
			</div>
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=attack" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<dl class="settings">
						<dt>
							<label for="army_target">', $txt['army_select_target'] ?? 'Select Target', '</label>
						</dt>
						<dd>
							<select name="target" id="army_target">';

	foreach ($context['army_targets'] as $target)
	{
		$selected = ((int) $target['id'] === (int) $preselect) ? ' selected' : '';

		echo '
								<option value="', $target['id'], '"', $selected, '>', htmlspecialchars($target['name']), ' (', $txt['army_army_size'] ?? 'Army Size', ': ', $target['army_size'], ')</option>';
	}

	echo '
							</select>
						</dd>
						<dt>
							<label for="army_turns">', $txt['army_turns_to_use'] ?? 'Turns to Use', '</label>
							<br>
							<span class="smalltext">', sprintf($txt['army_turns_range'] ?? 'You may use 1 to %d turns per attack.', $max_turns), '</span>
						</dt>
						<dd>
							<select name="turns" id="army_turns">';

	$available_turns = min($max_turns, $member['attack_turns']);
	for ($t = 1; $t <= $available_turns; $t++)
	{
		echo '
								<option value="', $t, '">', $t, '</option>';
	}

	echo '
							</select>
						</dd>
					</dl>
					<div class="righttext">
						<input type="submit" name="do_attack" value="', $txt['army_btn_attack'] ?? 'Attack', '" class="button" onclick="return confirm(\'', ($txt['army_attack_confirm'] ?? 'Are you sure you want to launch this attack?'), '\');">
					</div>
				</form>
			</div>
		</div>
	</div>';
}

/**
 * Battle result template.
 *
 * Displays the outcome of an attack including a winner/loser banner,
 * offensive vs defensive power comparison, gold stolen/lost, casualty
 * breakdowns for both sides, and equipment damage summaries.
 *
 * Context variables used:
 *   $context['army_battle_result']  - array, full battle outcome data
 *     ['won']            - bool, true if attacker won
 *     ['attacker_name']  - string, attacker display name
 *     ['defender_name']  - string, defender display name
 *     ['atk_power']      - string, formatted attacker offensive power
 *     ['def_power']      - string, formatted defender defensive power
 *     ['atk_damage']     - string, formatted damage dealt by attacker
 *     ['def_damage']     - string, formatted damage dealt by defender
 *     ['money_stolen']   - string, formatted gold stolen/lost
 *     ['atk_kill']       - string, formatted attacker casualties
 *     ['def_kill']       - string, formatted defender casualties
 *     ['atk_spy_killed'] - string, attacker spies killed
 *     ['atk_sen_killed'] - string, attacker sentries killed
 *     ['def_spy_killed'] - string, defender spies killed
 *     ['def_sen_killed'] - string, defender sentries killed
 *     ['equipment_summary'] - array|null, equipment damage summary
 *     ['log_id']         - int, attack log id for detail link
 *   $context['army_currency']       - string, currency name
 */
function template_army_battle_result()
{
	global $context, $txt, $scripturl;

	$result = $context['army_battle_result'];

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_battle_result_title'] ?? 'Battle Report', '</h3>
			</div>';

	// Winner/loser banner
	if (!empty($result['won']))
	{
		echo '
			<div class="infobox army_result_victory">
				<strong>', sprintf($txt['army_battle_victory'] ?? 'Victory! %s has defeated %s!', htmlspecialchars($result['attacker_name']), htmlspecialchars($result['defender_name'])), '</strong>
			</div>';
	}
	else
	{
		echo '
			<div class="errorbox army_result_defeat">
				<strong>', sprintf($txt['army_battle_defeat'] ?? 'Defeat! %s failed to overcome %s\'s defenses!', htmlspecialchars($result['attacker_name']), htmlspecialchars($result['defender_name'])), '</strong>
			</div>';
	}

	// Power comparison
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_battle_summary'] ?? 'Battle Summary', '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th width="30%">&nbsp;</th>
						<th class="centercol">', htmlspecialchars($result['attacker_name']), '<br><span class="smalltext">(', $txt['army_attacker'] ?? 'Attacker', ')</span></th>
						<th class="centercol">', htmlspecialchars($result['defender_name']), '<br><span class="smalltext">(', $txt['army_defender'] ?? 'Defender', ')</span></th>
					</tr>
				</thead>
				<tbody>
					<tr class="windowbg">
						<td><strong>', $txt['army_offensive_power'] ?? 'Offensive Power', '</strong></td>
						<td class="centercol">', $result['atk_power'], '</td>
						<td class="centercol">&mdash;</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_defensive_power'] ?? 'Defensive Power', '</strong></td>
						<td class="centercol">&mdash;</td>
						<td class="centercol">', $result['def_power'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_damage_dealt'] ?? 'Damage Dealt', '</strong></td>
						<td class="centercol">', $result['atk_damage'], '</td>
						<td class="centercol">', $result['def_damage'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_casualties'] ?? 'Casualties', '</strong></td>
						<td class="centercol"><span class="army_bonus_negative">', $result['atk_kill'], '</span></td>
						<td class="centercol"><span class="army_bonus_negative">', $result['def_kill'], '</span></td>
					</tr>
				</tbody>
			</table>';

	// Gold stolen/lost
	echo '
			<div class="windowbg">
				<dl class="army_stats_list">
					<dt><strong>', $txt['army_gold_change'] ?? 'Gold Stolen', '</strong></dt>
					<dd>';

	if (!empty($result['won']))
		echo '<span class="army_bonus_positive">+', $result['money_stolen'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold'), '</span>';
	else
		echo '<span class="army_bonus_negative">-', $result['money_stolen'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold'), '</span>';

	echo '</dd>
					<dt><strong>', $txt['army_turns_used'] ?? 'Turns Used', '</strong></dt>
					<dd>', $result['turns_used'] ?? '1', '</dd>
				</dl>
			</div>';

	// Spy/sentry casualties
	if (!empty($result['atk_spy_killed']) || !empty($result['atk_sen_killed']) || !empty($result['def_spy_killed']) || !empty($result['def_sen_killed']))
	{
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_intel_casualties'] ?? 'Intelligence Casualties', '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th width="30%">&nbsp;</th>
						<th class="centercol">', htmlspecialchars($result['attacker_name']), '</th>
						<th class="centercol">', htmlspecialchars($result['defender_name']), '</th>
					</tr>
				</thead>
				<tbody>
					<tr class="windowbg">
						<td><strong>', $txt['army_spies_killed'] ?? 'Spies Killed', '</strong></td>
						<td class="centercol">', $result['atk_spy_killed'], '</td>
						<td class="centercol">', $result['def_spy_killed'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_sentries_killed'] ?? 'Sentries Killed', '</strong></td>
						<td class="centercol">', $result['atk_sen_killed'], '</td>
						<td class="centercol">', $result['def_sen_killed'], '</td>
					</tr>
				</tbody>
			</table>';
	}

	// Equipment damage summary
	if (!empty($result['equipment_summary']))
	{
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_equipment_damage'] ?? 'Equipment Damage', '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th>', $txt['army_col_item'] ?? 'Item', '</th>
						<th class="centercol">', $txt['army_col_owner'] ?? 'Owner', '</th>
						<th class="centercol">', $txt['army_col_used'] ?? 'Used', '</th>
						<th class="centercol">', $txt['army_col_strength_before'] ?? 'Before', '</th>
						<th class="centercol">', $txt['army_col_strength_after'] ?? 'After', '</th>
					</tr>
				</thead>
				<tbody>';

		foreach ($result['equipment_summary'] as $equip)
		{
			echo '
					<tr class="windowbg">
						<td>', htmlspecialchars($equip['name']), '</td>
						<td class="centercol">', htmlspecialchars($equip['owner_name']), '</td>
						<td class="centercol">', $equip['used'], '</td>
						<td class="centercol">', number_format($equip['original_strength'], 2), '</td>
						<td class="centercol">', number_format($equip['after_strength'], 2), '</td>
					</tr>';
		}

		echo '
				</tbody>
			</table>';
	}

	// Navigation links
	echo '
			<div class="windowbg">
				<div class="buttonlist">
					<a class="button" href="', $scripturl, '?action=army;sa=attacklog;detail=', $result['log_id'], '">', $txt['army_view_detail'] ?? 'View Full Detail', '</a>
					<a class="button" href="', $scripturl, '?action=army;sa=attack">', $txt['army_attack_again'] ?? 'Attack Again', '</a>
					<a class="button" href="', $scripturl, '?action=army">', $txt['army_back_dashboard'] ?? 'Back to Dashboard', '</a>
				</div>
			</div>
		</div>
	</div>';
}

/**
 * Attack log template - paginated history of attacks sent and received.
 *
 * Displays filter tabs (All, Sent, Received) and a table of attack
 * records with links to the detailed view.
 *
 * Context variables used:
 *   $context['army_attack_logs']     - array, log entries for current page
 *   $context['army_attacklog_filter']- string, current filter ('all','sent','received')
 *   $context['page_index']           - string, SMF pagination HTML
 *   $context['army_currency']        - string, currency name
 */
function template_army_attacklog()
{
	global $context, $txt, $scripturl;

	$filter = $context['army_attacklog_filter'] ?? 'all';

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_attacklog_title'] ?? 'Attack Log', '</h3>
			</div>';

	// Filter tabs
	echo '
			<div class="buttonlist army_filter_tabs">
				<ul>
					<li>
						<a class="button', ($filter === 'all' ? ' active' : ''), '" href="', $scripturl, '?action=army;sa=attacklog;filter=all">', $txt['army_filter_all'] ?? 'All', '</a>
					</li>
					<li>
						<a class="button', ($filter === 'sent' ? ' active' : ''), '" href="', $scripturl, '?action=army;sa=attacklog;filter=sent">', $txt['army_filter_sent'] ?? 'Sent', '</a>
					</li>
					<li>
						<a class="button', ($filter === 'received' ? ' active' : ''), '" href="', $scripturl, '?action=army;sa=attacklog;filter=received">', $txt['army_filter_received'] ?? 'Received', '</a>
					</li>
				</ul>
			</div>';

	// Pagination - top
	echo '
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>';

	// Attack logs table
	echo '
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th>', $txt['army_col_date'] ?? 'Date', '</th>
						<th>', $txt['army_col_opponent'] ?? 'Opponent', '</th>
						<th class="centercol">', $txt['army_col_role'] ?? 'Role', '</th>
						<th class="centercol">', $txt['army_col_result'] ?? 'Result', '</th>
						<th class="centercol">', $txt['army_col_gold_change'] ?? 'Gold Change', '</th>
						<th class="centercol">', $txt['army_col_casualties_short'] ?? 'Casualties', '</th>
						<th class="centercol">', $txt['army_col_detail'] ?? 'Detail', '</th>
					</tr>
				</thead>
				<tbody>';

	if (!empty($context['army_attack_logs']))
	{
		foreach ($context['army_attack_logs'] as $log)
		{
			// Result styling
			if (!empty($log['won']))
				$result_html = '<span class="army_bonus_positive">' . ($txt['army_result_won'] ?? 'Won') . '</span>';
			else
				$result_html = '<span class="army_bonus_negative">' . ($txt['army_result_lost'] ?? 'Lost') . '</span>';

			// Gold change styling
			if ($log['gold_change'] > 0)
				$gold_html = '<span class="army_bonus_positive">+' . $log['gold_formatted'] . '</span>';
			elseif ($log['gold_change'] < 0)
				$gold_html = '<span class="army_bonus_negative">' . $log['gold_formatted'] . '</span>';
			else
				$gold_html = '0';

			// Role
			$role_html = !empty($log['is_attacker'])
				? ($txt['army_role_attacker'] ?? 'Attacker')
				: ($txt['army_role_defender'] ?? 'Defender');

			echo '
					<tr class="windowbg">
						<td>', $log['time_formatted'], '</td>
						<td>
							<a href="', $scripturl, '?action=army;sa=profile;u=', $log['opponent_id'], '">', htmlspecialchars($log['opponent_name']), '</a>
						</td>
						<td class="centercol">', $role_html, '</td>
						<td class="centercol">', $result_html, '</td>
						<td class="centercol">', $gold_html, '</td>
						<td class="centercol">', $log['casualties'], '</td>
						<td class="centercol">
							<a class="button" href="', $scripturl, '?action=army;sa=attacklog;detail=', $log['id'], '">', $txt['army_btn_detail'] ?? 'View', '</a>
						</td>
					</tr>';
		}
	}
	else
	{
		echo '
					<tr class="windowbg">
						<td colspan="7" class="centercol">', $txt['army_no_attack_logs'] ?? 'No attack records found.', '</td>
					</tr>';
	}

	echo '
				</tbody>
			</table>';

	// Pagination - bottom
	echo '
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>
		</div>
	</div>';
}

/**
 * Attack detail template - full breakdown of a single battle.
 *
 * Shows comprehensive attacker/defender stats at the time of the battle,
 * the per-item equipment table from the inventory log, gold changes,
 * casualty details, and spy/sentry losses.
 *
 * Context variables used:
 *   $context['army_log_detail']      - array, full battle data
 *     ['id']               - int, log id
 *     ['time_formatted']   - string, formatted attack time
 *     ['attacker_name']    - string
 *     ['attacker_id']      - int
 *     ['defender_name']    - string
 *     ['defender_id']      - int
 *     ['won']              - bool
 *     ['money_stolen']     - string, formatted gold
 *     ['turns_used']       - int
 *     ['atk_damage']       - string, formatted
 *     ['def_damage']       - string, formatted
 *     ['atk_kill']         - string, formatted
 *     ['def_kill']         - string, formatted
 *     ['atk_army_gen']     - string, attacker total army at time
 *     ['atk_army_atk']     - string, attacker attack soldiers
 *     ['atk_army_mgen']    - string, attacker total mercs
 *     ['atk_army_matk']    - string, attacker attack mercs
 *     ['def_army_gen']     - string, defender total army at time
 *     ['def_army_def']     - string, defender defense soldiers
 *     ['def_army_mgen']    - string, defender total mercs
 *     ['def_army_mdef']    - string, defender defense mercs
 *     ['atk_spy_killed']   - string
 *     ['atk_sen_killed']   - string
 *     ['def_spy_killed']   - string
 *     ['def_sen_killed']   - string
 *     ['inventory']        - array, per-item equipment log entries
 *   $context['army_currency']        - string, currency name
 */
function template_army_attack_detail()
{
	global $context, $txt, $scripturl;

	$detail = $context['army_log_detail'];

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', sprintf($txt['army_attack_detail_title'] ?? 'Battle Report #%d', $detail['id']), '</h3>
			</div>';

	// Time and result banner
	echo '
			<div class="windowbg">
				<dl class="army_stats_list">
					<dt>', $txt['army_col_date'] ?? 'Date', '</dt>
					<dd>', $detail['time_formatted'], '</dd>
					<dt>', $txt['army_col_result'] ?? 'Result', '</dt>
					<dd>';

	if (!empty($detail['won']))
		echo '<span class="army_bonus_positive"><strong>', sprintf($txt['army_detail_victory'] ?? '%s won the battle', htmlspecialchars($detail['attacker_name'])), '</strong></span>';
	else
		echo '<span class="army_bonus_negative"><strong>', sprintf($txt['army_detail_defeat'] ?? '%s lost the battle', htmlspecialchars($detail['attacker_name'])), '</strong></span>';

	echo '</dd>
					<dt>', $txt['army_turns_used'] ?? 'Turns Used', '</dt>
					<dd>', $detail['turns_used'], '</dd>
					<dt>', $txt['army_gold_change'] ?? 'Gold Stolen', '</dt>
					<dd>', $detail['money_stolen'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold'), '</dd>
				</dl>
			</div>';

	// Attacker stats at time of battle
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_attacker_stats'] ?? 'Attacker Stats', ' &mdash;
					<a href="', $scripturl, '?action=army;sa=profile;u=', $detail['attacker_id'], '">', htmlspecialchars($detail['attacker_name']), '</a>
				</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<tbody>
					<tr class="windowbg">
						<td width="30%"><strong>', $txt['army_army_size'] ?? 'Army Size', '</strong></td>
						<td>', $detail['atk_army_gen'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_soldiers_attack'] ?? 'Attack Soldiers', '</strong></td>
						<td>', $detail['atk_army_atk'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_mercs_total'] ?? 'Total Mercenaries', '</strong></td>
						<td>', $detail['atk_army_mgen'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_mercs_attack'] ?? 'Attack Mercenaries', '</strong></td>
						<td>', $detail['atk_army_matk'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_damage_dealt'] ?? 'Damage Dealt', '</strong></td>
						<td>', $detail['atk_damage'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_casualties'] ?? 'Casualties', '</strong></td>
						<td><span class="army_bonus_negative">', $detail['atk_kill'], '</span></td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_spies_killed'] ?? 'Spies Killed', '</strong></td>
						<td>', $detail['atk_spy_killed'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_sentries_killed'] ?? 'Sentries Killed', '</strong></td>
						<td>', $detail['atk_sen_killed'], '</td>
					</tr>
				</tbody>
			</table>';

	// Defender stats at time of battle
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_defender_stats'] ?? 'Defender Stats', ' &mdash;
					<a href="', $scripturl, '?action=army;sa=profile;u=', $detail['defender_id'], '">', htmlspecialchars($detail['defender_name']), '</a>
				</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<tbody>
					<tr class="windowbg">
						<td width="30%"><strong>', $txt['army_army_size'] ?? 'Army Size', '</strong></td>
						<td>', $detail['def_army_gen'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_soldiers_defense'] ?? 'Defense Soldiers', '</strong></td>
						<td>', $detail['def_army_def'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_mercs_total'] ?? 'Total Mercenaries', '</strong></td>
						<td>', $detail['def_army_mgen'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_mercs_defense'] ?? 'Defense Mercenaries', '</strong></td>
						<td>', $detail['def_army_mdef'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_damage_dealt'] ?? 'Damage Dealt', '</strong></td>
						<td>', $detail['def_damage'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_casualties'] ?? 'Casualties', '</strong></td>
						<td><span class="army_bonus_negative">', $detail['def_kill'], '</span></td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_spies_killed'] ?? 'Spies Killed', '</strong></td>
						<td>', $detail['def_spy_killed'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_sentries_killed'] ?? 'Sentries Killed', '</strong></td>
						<td>', $detail['def_sen_killed'], '</td>
					</tr>
				</tbody>
			</table>';

	// Per-item equipment log
	if (!empty($detail['inventory']))
	{
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_equipment_detail'] ?? 'Equipment Detail', '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th>', $txt['army_col_item'] ?? 'Item', '</th>
						<th class="centercol">', $txt['army_col_side'] ?? 'Side', '</th>
						<th class="centercol">', $txt['army_col_quantity'] ?? 'Qty', '</th>
						<th class="centercol">', $txt['army_col_used'] ?? 'Used', '</th>
						<th class="centercol">', $txt['army_col_strength_before'] ?? 'Str Before', '</th>
						<th class="centercol">', $txt['army_col_strength_after'] ?? 'Str After', '</th>
						<th class="centercol">', $txt['army_col_sabotaged'] ?? 'Sabotaged', '</th>
					</tr>
				</thead>
				<tbody>';

		foreach ($detail['inventory'] as $inv)
		{
			// Side label
			if ($inv['section'] === 'a')
				$side_label = $txt['army_side_attack'] ?? 'Attack';
			elseif ($inv['section'] === 'd')
				$side_label = $txt['army_side_defense'] ?? 'Defense';
			else
				$side_label = $txt['army_side_other'] ?? 'Other';

			echo '
					<tr class="windowbg">
						<td>', htmlspecialchars($inv['name']), '</td>
						<td class="centercol">', $side_label, '</td>
						<td class="centercol">', $inv['quantity'], '</td>
						<td class="centercol">', $inv['used'], '</td>
						<td class="centercol">', number_format($inv['original_strength'], 2), '</td>
						<td class="centercol">', number_format($inv['after_strength'], 2), '</td>
						<td class="centercol">';

			if ($inv['spy_sabbed'] > 0)
				echo '<span class="army_bonus_negative">', $inv['spy_sabbed'], '</span>';
			else
				echo '0';

			echo '</td>
					</tr>';
		}

		echo '
				</tbody>
			</table>';
	}

	// Navigation
	echo '
			<div class="windowbg">
				<div class="buttonlist">
					<a class="button" href="', $scripturl, '?action=army;sa=attacklog">', $txt['army_back_to_log'] ?? 'Back to Attack Log', '</a>
					<a class="button" href="', $scripturl, '?action=army">', $txt['army_back_dashboard'] ?? 'Back to Dashboard', '</a>
				</div>
			</div>
		</div>
	</div>';
}
