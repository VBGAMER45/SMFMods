<?php
/**
 * Army System - Espionage Templates
 *
 * Provides template functions for spy missions (recon and sabotage),
 * mission results, spy skill upgrades, and the paginated spy log.
 *
 * @package ArmySystem
 * @version 1.0
 */

/**
 * Spy missions template.
 *
 * Displays the spy mission form with target selection, mission type
 * (recon/sabotage) radio buttons, spies count selector, and a preview
 * of the player's current spy and sentry power. Also includes the
 * spy skill upgrade section.
 *
 * Context variables used:
 *   $context['army_targets']         - array, eligible spy targets
 *   $context['army_member']          - array, current player's army data
 *   $context['army_spy_power']       - string, formatted spy power
 *   $context['army_sentry_power']    - string, formatted sentry power
 *   $context['army_max_spies']       - int, max spies per mission
 *   $context['army_prefill_target']   - int|null, pre-selected target id
 *   $context['army_spy_error']       - string|null, error message
 *   $context['army_spy_current_level'] - array|null, current spy skill
 *     ['level']   - int, current spy skill level
 *     ['name']    - string, current level name
 *   $context['army_spy_next_level']  - array|null, next spy skill level (null if maxed)
 *     ['level']   - int, next level number
 *     ['name']    - string, next level name
 *     ['price']   - int, upgrade cost
 *     ['price_formatted'] - string, formatted upgrade price
 *   $context['army_gold']            - string, formatted current gold
 *   $context['army_currency']        - string, currency name
 *   $context['army_session_var']     - string, session variable name
 *   $context['army_session_id']      - string, session token value
 */
function template_army_spy()
{
	global $context, $txt, $scripturl;

	$member = $context['army_member'];
	$max_spies = $context['army_max_spies'] ?? 10;
	$preselect = $context['army_prefill_target'] ?? 0;
	$current_level = $context['army_spy_current_level'];
	$next_level = $context['army_spy_next_level'];

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_spy_title'] ?? 'Espionage', '</h3>
			</div>';

	// Error message
	if (!empty($context['army_spy_error']))
	{
		echo '
			<div class="errorbox">
				', $context['army_spy_error'], '
			</div>';
	}

	// Success messages
	if (!empty($context['army_spy_upgrade_success']))
	{
		echo '
			<div class="infobox">
				', $txt['army_spy_upgrade_success_msg'] ?? 'Spy skill upgraded successfully!', '
			</div>';
	}

	// Current gold display
	echo '
			<div class="windowbg">
				<strong>', $txt['army_your_gold'] ?? 'Your Gold', ':</strong> ',
				$context['army_gold'], ' ', htmlspecialchars($context['army_currency'] ?? 'Gold'), '
			</div>';

	// Spy power preview
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_spy_overview'] ?? 'Espionage Overview', '</h4>
			</div>
			<div class="windowbg">
				<div class="army_stats_grid">
					<div class="army_stats_section">
						<dl class="army_stats_list">
							<dt>', $txt['army_spy_power'] ?? 'Spy Power', '</dt>
							<dd><strong>', $context['army_spy_power'], '</strong></dd>
							<dt>', $txt['army_sentry_power'] ?? 'Sentry Power', '</dt>
							<dd><strong>', $context['army_sentry_power'], '</strong></dd>
						</dl>
					</div>
					<div class="army_stats_section">
						<dl class="army_stats_list">
							<dt>', $txt['army_soldiers_spy'] ?? 'Spy Soldiers', '</dt>
							<dd>', $member['soldiers_spy'], '</dd>
							<dt>', $txt['army_soldiers_sentry'] ?? 'Sentry Soldiers', '</dt>
							<dd>', $member['soldiers_sentry'], '</dd>
							<dt>', $txt['army_spy_skill_level'] ?? 'Spy Skill Level', '</dt>
							<dd>', htmlspecialchars($current_level !== null ? $current_level['name'] : 'None'), ' (', $txt['army_level'] ?? 'Level', ' ', ($current_level !== null ? $current_level['level'] : 0), ')</dd>
						</dl>
					</div>
				</div>
			</div>';

	// --- Spy Skill Upgrade Section ---
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_spy_skill_upgrade'] ?? 'Spy Skill Upgrade', '</h4>
			</div>
			<div class="windowbg">';

	if ($next_level === null)
	{
		echo '
				<p><strong>', $txt['army_spy_skill_maxed'] ?? 'Your spy skill is at the maximum level!', '</strong></p>';
	}
	else
	{
		echo '
				<form action="', $scripturl, '?action=army;sa=spy" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="upgrade_spy" value="1">
					<p>',
						sprintf(
							$txt['army_spy_skill_upgrade_prompt'] ?? 'Upgrade to <strong>%s</strong> (Level %d) for <strong>%s</strong> %s',
							htmlspecialchars($next_level['name']),
							$next_level['level'],
							$next_level['price_formatted'],
							htmlspecialchars($context['army_currency'] ?? 'Gold')
						), '
					</p>
					<input type="submit" value="', $txt['army_btn_upgrade_spy'] ?? 'Upgrade Spy Skill', '" class="button">
				</form>';
	}

	echo '
			</div>';

	// --- Mission Form ---
	if (empty($context['army_targets']))
	{
		echo '
			<div class="windowbg">
				<p class="centertext">', $txt['army_no_spy_targets'] ?? 'There are no valid targets available for spy missions at this time.', '</p>
			</div>
		</div>
	</div>';

		return;
	}

	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_spy_mission'] ?? 'Launch Spy Mission', '</h4>
			</div>
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=spy" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<dl class="settings">
						<dt>
							<label for="spy_target">', $txt['army_select_target'] ?? 'Select Target', '</label>
						</dt>
						<dd>
							<select name="target" id="spy_target">';

	foreach ($context['army_targets'] as $target)
	{
		$selected = ((int) $target['id'] === (int) $preselect) ? ' selected' : '';

		echo '
								<option value="', $target['id'], '"', $selected, '>', htmlspecialchars($target['name']), '</option>';
	}

	echo '
							</select>
						</dd>
						<dt>
							<label>', $txt['army_mission_type'] ?? 'Mission Type', '</label>
						</dt>
						<dd>
							<label>
								<input type="radio" name="mission" value="recon" checked>
								<strong>', $txt['army_mission_recon'] ?? 'Recon Mission', '</strong>
								<br><span class="smalltext">', $txt['army_mission_recon_desc'] ?? 'Gather intelligence on the target\'s army. Reveals army size, fort level, race, and other details based on accuracy.', '</span>
							</label>
							<br><br>
							<label>
								<input type="radio" name="mission" value="sab">
								<strong>', $txt['army_mission_sabotage'] ?? 'Sabotage Mission', '</strong>
								<br><span class="smalltext">', $txt['army_mission_sab_desc'] ?? 'Attempt to damage the target\'s equipment, reducing its effectiveness in battle.', '</span>
							</label>
						</dd>
						<dt>
							<label for="spy_count">', $txt['army_spies_to_send'] ?? 'Spies to Send', '</label>
							<br>
							<span class="smalltext">', sprintf($txt['army_spies_range'] ?? 'You may send 1 to %d spies per mission.', $max_spies), '</span>
						</dt>
						<dd>
							<select name="spies" id="spy_count">';

	$available_spies = min($max_spies, $member['soldiers_spy']);
	for ($s = 1; $s <= $available_spies; $s++)
	{
		echo '
								<option value="', $s, '">', $s, '</option>';
	}

	echo '
							</select>
						</dd>
					</dl>
					<div class="righttext">
						<input type="submit" name="do_spy" value="', $txt['army_btn_spy'] ?? 'Launch Mission', '" class="button">
					</div>
				</form>
			</div>
		</div>
	</div>';
}

/**
 * Recon mission result template.
 *
 * Displays the intelligence gathered from a successful recon mission.
 * Values may be shown as ranges if accuracy is below 100%.
 *
 * Context variables used:
 *   $context['army_recon_result']  - array, recon outcome data
 *     ['success']           - bool, true if mission succeeded
 *     ['target_name']       - string, target display name
 *     ['target_id']         - int, target member id
 *     ['accuracy']          - int, accuracy percentage (0-100)
 *     ['spies_sent']        - int
 *     ['spies_caught']      - int
 *     ['race_name']         - string|null
 *     ['army_size']         - string, exact or range
 *     ['fort_level']        - string|null
 *     ['fort_name']         - string|null
 *     ['siege_level']       - string|null
 *     ['siege_name']        - string|null
 *     ['soldiers_attack']   - string|null, range or exact
 *     ['soldiers_defense']  - string|null, range or exact
 *     ['gold']              - string|null, range or exact
 *     ['log_id']            - int, spy log id
 */
function template_army_recon_result()
{
	global $context, $txt, $scripturl;

	$result = $context['army_recon_result'];

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_recon_result_title'] ?? 'Recon Report', '</h3>
			</div>';

	// Success or failure banner
	if (!empty($result['success']))
	{
		echo '
			<div class="infobox">
				<strong>', sprintf($txt['army_recon_success_msg'] ?? 'Recon mission on %s was successful!', htmlspecialchars($result['target_name'])), '</strong>
			</div>';
	}
	else
	{
		echo '
			<div class="errorbox">
				<strong>', sprintf($txt['army_recon_failed_msg'] ?? 'Recon mission on %s has failed! Your spies were detected.', htmlspecialchars($result['target_name'])), '</strong>
			</div>';

		// Spy loss summary for failed missions
		echo '
			<div class="windowbg">
				<dl class="army_stats_list">
					<dt>', $txt['army_spies_sent'] ?? 'Spies Sent', '</dt>
					<dd>', $result['spies_sent'], '</dd>
					<dt>', $txt['army_spies_caught'] ?? 'Spies Caught', '</dt>
					<dd><span class="army_bonus_negative">', $result['spies_caught'], '</span></dd>
				</dl>
			</div>';

		// Navigation for failed result
		echo '
			<div class="windowbg">
				<div class="buttonlist">
					<a class="button" href="', $scripturl, '?action=army;sa=spy">', $txt['army_try_again'] ?? 'Try Again', '</a>
					<a class="button" href="', $scripturl, '?action=army;sa=spylog">', $txt['army_view_spy_log'] ?? 'View Spy Log', '</a>
				</div>
			</div>
		</div>
	</div>';

		return;
	}

	// Accuracy indicator
	echo '
			<div class="windowbg">
				<dl class="army_stats_list">
					<dt>', $txt['army_recon_accuracy'] ?? 'Intelligence Accuracy', '</dt>
					<dd>';

	if ($result['accuracy'] >= 100)
		echo '<span class="army_bonus_positive">', $txt['army_accuracy_perfect'] ?? 'Perfect (100%)', '</span>';
	elseif ($result['accuracy'] >= 75)
		echo '<span class="army_bonus_positive">', $result['accuracy'], '%</span>';
	elseif ($result['accuracy'] >= 50)
		echo '<span class="army_bonus_neutral">', $result['accuracy'], '%</span>';
	else
		echo '<span class="army_bonus_negative">', $result['accuracy'], '%</span>';

	echo '</dd>
					<dt>', $txt['army_spies_sent'] ?? 'Spies Sent', '</dt>
					<dd>', $result['spies_sent'], '</dd>
					<dt>', $txt['army_spies_caught'] ?? 'Spies Caught', '</dt>
					<dd>';

	if ($result['spies_caught'] > 0)
		echo '<span class="army_bonus_negative">', $result['spies_caught'], '</span>';
	else
		echo '0';

	echo '</dd>
				</dl>
			</div>';

	// Intelligence gathered
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', sprintf($txt['army_intel_on'] ?? 'Intelligence on %s', htmlspecialchars($result['target_name'])), '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th width="30%">', $txt['army_col_stat'] ?? 'Stat', '</th>
						<th>', $txt['army_col_value'] ?? 'Value', '</th>
					</tr>
				</thead>
				<tbody>';

	// Race (always shown if mission succeeds)
	if ($result['race_name'] !== null)
	{
		echo '
					<tr class="windowbg">
						<td><strong>', $txt['army_race'] ?? 'Race', '</strong></td>
						<td>', htmlspecialchars($result['race_name']), '</td>
					</tr>';
	}

	// Army size
	echo '
					<tr class="windowbg">
						<td><strong>', $txt['army_army_size'] ?? 'Army Size', '</strong></td>
						<td>', $result['army_size'], '</td>
					</tr>';

	// Fort level
	if ($result['fort_name'] !== null)
	{
		echo '
					<tr class="windowbg">
						<td><strong>', $txt['army_fort_level'] ?? 'Fort', '</strong></td>
						<td>', htmlspecialchars($result['fort_name']), ' (', $txt['army_level'] ?? 'Level', ' ', $result['fort_level'], ')</td>
					</tr>';
	}

	// Siege level
	if ($result['siege_name'] !== null)
	{
		echo '
					<tr class="windowbg">
						<td><strong>', $txt['army_siege_level'] ?? 'Siege', '</strong></td>
						<td>', htmlspecialchars($result['siege_name']), ' (', $txt['army_level'] ?? 'Level', ' ', $result['siege_level'], ')</td>
					</tr>';
	}

	// Soldiers attack
	if ($result['soldiers_attack'] !== null)
	{
		echo '
					<tr class="windowbg">
						<td><strong>', $txt['army_soldiers_attack'] ?? 'Attack Soldiers', '</strong></td>
						<td>', $result['soldiers_attack'], '</td>
					</tr>';
	}

	// Soldiers defense
	if ($result['soldiers_defense'] !== null)
	{
		echo '
					<tr class="windowbg">
						<td><strong>', $txt['army_soldiers_defense'] ?? 'Defense Soldiers', '</strong></td>
						<td>', $result['soldiers_defense'], '</td>
					</tr>';
	}

	// Gold
	if ($result['gold'] !== null)
	{
		echo '
					<tr class="windowbg">
						<td><strong>', htmlspecialchars($context['army_currency'] ?? 'Gold'), '</strong></td>
						<td>', $result['gold'], '</td>
					</tr>';
	}

	echo '
				</tbody>
			</table>';

	// Accuracy note for inexact values
	if ($result['accuracy'] < 100)
	{
		echo '
			<div class="windowbg">
				<p class="smalltext">', $txt['army_recon_accuracy_note'] ?? 'Values shown as ranges indicate imprecise intelligence. Higher spy skill and more spies improve accuracy.', '</p>
			</div>';
	}

	// Navigation
	echo '
			<div class="windowbg">
				<div class="buttonlist">
					<a class="button" href="', $scripturl, '?action=army;sa=spy">', $txt['army_new_mission'] ?? 'New Mission', '</a>
					<a class="button" href="', $scripturl, '?action=army;sa=spylog">', $txt['army_view_spy_log'] ?? 'View Spy Log', '</a>
					<a class="button" href="', $scripturl, '?action=army;sa=profile;u=', $result['target_id'], '">', $txt['army_view_target_profile'] ?? 'View Target Profile', '</a>
				</div>
			</div>
		</div>
	</div>';
}

/**
 * Sabotage mission result template.
 *
 * Displays the outcome of a sabotage mission including what was
 * damaged, the damage percentage, and the item affected.
 *
 * Context variables used:
 *   $context['army_sabotage_result']  - array, sabotage outcome data
 *     ['success']              - bool, true if mission succeeded
 *     ['target_name']          - string
 *     ['target_id']            - int
 *     ['spies_sent']           - int
 *     ['spies_caught']         - int
 *     ['item_name']            - string|null, name of item damaged
 *     ['damage_percent']       - string|null, damage inflicted percentage
 *     ['total_sabbed']         - string|null, total units affected
 *     ['log_id']               - int, spy log id
 */
function template_army_sabotage_result()
{
	global $context, $txt, $scripturl;

	$result = $context['army_sabotage_result'];

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_sabotage_result_title'] ?? 'Sabotage Report', '</h3>
			</div>';

	// Success or failure banner
	if (!empty($result['success']))
	{
		echo '
			<div class="infobox">
				<strong>', sprintf($txt['army_sabotage_success_msg'] ?? 'Sabotage mission against %s was successful!', htmlspecialchars($result['target_name'])), '</strong>
			</div>';
	}
	else
	{
		echo '
			<div class="errorbox">
				<strong>', sprintf($txt['army_sabotage_failed_msg'] ?? 'Sabotage mission against %s has failed! Your spies were detected.', htmlspecialchars($result['target_name'])), '</strong>
			</div>';
	}

	// Mission summary
	echo '
			<div class="windowbg">
				<dl class="army_stats_list">
					<dt>', $txt['army_target'] ?? 'Target', '</dt>
					<dd>
						<a href="', $scripturl, '?action=army;sa=profile;u=', $result['target_id'], '">', htmlspecialchars($result['target_name']), '</a>
					</dd>
					<dt>', $txt['army_spies_sent'] ?? 'Spies Sent', '</dt>
					<dd>', $result['spies_sent'], '</dd>
					<dt>', $txt['army_spies_caught'] ?? 'Spies Caught', '</dt>
					<dd>';

	if ($result['spies_caught'] > 0)
		echo '<span class="army_bonus_negative">', $result['spies_caught'], '</span>';
	else
		echo '0';

	echo '</dd>
				</dl>
			</div>';

	// Damage details (only on success)
	if (!empty($result['success']) && $result['item_name'] !== null)
	{
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_sabotage_damage'] ?? 'Damage Inflicted', '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<tbody>
					<tr class="windowbg">
						<td width="30%"><strong>', $txt['army_item_damaged'] ?? 'Item Damaged', '</strong></td>
						<td>', htmlspecialchars($result['item_name']), '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_damage_percent'] ?? 'Damage Inflicted', '</strong></td>
						<td><span class="army_bonus_negative">', $result['damage_percent'], '%</span></td>
					</tr>';

		if ($result['total_sabbed'] !== null)
		{
			echo '
					<tr class="windowbg">
						<td><strong>', $txt['army_units_affected'] ?? 'Units Affected', '</strong></td>
						<td>', $result['total_sabbed'], '</td>
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
					<a class="button" href="', $scripturl, '?action=army;sa=spy">', $txt['army_new_mission'] ?? 'New Mission', '</a>
					<a class="button" href="', $scripturl, '?action=army;sa=spylog">', $txt['army_view_spy_log'] ?? 'View Spy Log', '</a>
				</div>
			</div>
		</div>
	</div>';
}

/**
 * Spy log template - paginated history of spy missions.
 *
 * Displays a table of all spy missions with type, target, result,
 * spies used, and spies caught.
 *
 * Context variables used:
 *   $context['army_spy_logs']   - array, spy log entries for current page
 *   $context['page_index']      - string, SMF pagination HTML
 */
function template_army_spylog()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_spylog_title'] ?? 'Spy Mission Log', '</h3>
			</div>';

	// Pagination - top
	echo '
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>';

	// Spy logs table
	echo '
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th>', $txt['army_col_date'] ?? 'Date', '</th>
						<th class="centercol">', $txt['army_col_mission_type'] ?? 'Type', '</th>
						<th>', $txt['army_col_target'] ?? 'Target', '</th>
						<th class="centercol">', $txt['army_col_result'] ?? 'Result', '</th>
						<th class="centercol">', $txt['army_col_spies_used'] ?? 'Spies Used', '</th>
						<th class="centercol">', $txt['army_col_spies_caught'] ?? 'Caught', '</th>
					</tr>
				</thead>
				<tbody>';

	if (!empty($context['army_spy_logs']))
	{
		foreach ($context['army_spy_logs'] as $log)
		{
			// Mission type label
			if ($log['mission'] === 'recon')
				$type_html = '<span class="army_spy_recon">' . ($txt['army_mission_recon_short'] ?? 'Recon') . '</span>';
			else
				$type_html = '<span class="army_spy_sabotage">' . ($txt['army_mission_sab_short'] ?? 'Sabotage') . '</span>';

			// Result styling
			if ($log['result'] === 'success')
				$result_html = '<span class="army_bonus_positive">' . ($txt['army_result_success'] ?? 'Success') . '</span>';
			else
				$result_html = '<span class="army_bonus_negative">' . ($txt['army_result_failed'] ?? 'Failed') . '</span>';

			// Determine if this player was the spy or the target
			if (!empty($log['is_spy']))
			{
				$target_name = $log['target_name'];
				$target_id = $log['target_id'];
			}
			else
			{
				$target_name = $log['spy_name'];
				$target_id = $log['spy_id'];
			}

			echo '
					<tr class="windowbg">
						<td>', $log['time_formatted'], '</td>
						<td class="centercol">', $type_html, '</td>
						<td>
							<a href="', $scripturl, '?action=army;sa=profile;u=', $target_id, '">', htmlspecialchars($target_name), '</a>';

			// Show role indicator
			if (!empty($log['is_spy']))
				echo ' <span class="smalltext">(', $txt['army_role_you_spied'] ?? 'you spied', ')</span>';
			else
				echo ' <span class="smalltext">(', $txt['army_role_spied_you'] ?? 'spied on you', ')</span>';

			echo '
						</td>
						<td class="centercol">', $result_html, '</td>
						<td class="centercol">', $log['spies_used'], '</td>
						<td class="centercol">';

			if ($log['caught'] > 0)
				echo '<span class="army_bonus_negative">', $log['caught'], '</span>';
			else
				echo '0';

			echo '</td>
					</tr>';
		}
	}
	else
	{
		echo '
					<tr class="windowbg">
						<td colspan="6" class="centercol">', $txt['army_no_spy_logs'] ?? 'No spy mission records found.', '</td>
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
