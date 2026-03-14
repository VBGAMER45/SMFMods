<?php
/**
 * Army System - Race Selection & Reset Templates
 *
 * Provides template functions for the race picker (displaying all
 * available races with their bonus comparisons) and the reset/leave
 * confirmation page.
 *
 * @package ArmySystem
 * @version 1.0
 */

/**
 * Race selection template - displays all available races for the player to choose.
 *
 * Shows a comparison table of all races with their stat bonuses color-coded:
 * positive bonuses in green, negative in red, zero in neutral. Players select
 * a race via radio button and submit the form.
 *
 * If the player arrived here after a reset ($context['army_reset_success']),
 * a success message is shown.
 *
 * Context variables used:
 *   $context['army_races']          - array of races keyed by race_id
 *   $context['army_member']         - array|null, current member data
 *   $context['army_reset_success']  - bool, show reset success message
 *   $context['army_session_var']    - string, session variable name
 *   $context['army_session_id']     - string, session token value
 */
function template_army_race()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_race_title'] ?? 'Choose Your Race', '</h3>
			</div>';

	// Reset success message
	if (!empty($context['army_reset_success']))
	{
		echo '
			<div class="infobox">
				', $txt['army_reset_success_msg'] ?? 'Your army has been reset successfully. Choose a new race to begin again.', '
			</div>';
	}

	// Already has a race - show error
	if (!empty($context['army_member']) && !empty($context['army_member']['has_race']))
	{
		echo '
			<div class="errorbox">
				', $txt['army_race_already_chosen'] ?? 'You have already chosen a race. You must reset your army before you can pick a new race.', '
				<br><br>
				<a class="button" href="', $scripturl, '?action=army;sa=reset">', $txt['army_go_to_reset'] ?? 'Go to Reset Page', '</a>
			</div>
		</div>
	</div>';

		return;
	}

	// Introduction text
	echo '
			<div class="windowbg">
				<p>', $txt['army_race_intro'] ?? 'Each race provides unique bonuses to different aspects of your army. Choose wisely -- your race determines your strategic strengths and weaknesses.', '</p>
			</div>';

	// Race comparison table inside a form
	echo '
			<form action="', $scripturl, '?action=army;sa=race" method="post" accept-charset="UTF-8">
				<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
				<table class="table_grid army_table army_race_table" width="100%">
					<thead>
						<tr class="title_bar">
							<th>', $txt['army_col_race_name'] ?? 'Race', '</th>
							<th class="centercol">', $txt['army_bonus_income'] ?? 'Income', '</th>
							<th class="centercol">', $txt['army_bonus_discount'] ?? 'Discount', '</th>
							<th class="centercol">', $txt['army_bonus_casualties'] ?? 'Casualties', '</th>
							<th class="centercol">', $txt['army_bonus_attack'] ?? 'Attack', '</th>
							<th class="centercol">', $txt['army_bonus_defence'] ?? 'Defence', '</th>
							<th class="centercol">', $txt['army_bonus_spy'] ?? 'Spy', '</th>
							<th class="centercol">', $txt['army_bonus_sentry'] ?? 'Sentry', '</th>
							<th class="centercol">', $txt['army_col_select'] ?? 'Select', '</th>
						</tr>
					</thead>
					<tbody>';

	$first = true;
	foreach ($context['army_races'] as $race_id => $race)
	{
		echo '
						<tr class="windowbg">
							<td>';

		if (!empty($race['icon']))
			echo '
								<img src="', $context['army_images_url'], '/', htmlspecialchars($race['icon']), '" alt="" class="army_race_icon"> ';

		echo '
								<strong>', htmlspecialchars($race['name']), '</strong>
							</td>
							<td class="centercol">', _army_format_bonus($race['bonus_income']), '</td>
							<td class="centercol">', _army_format_bonus($race['bonus_discount']), '</td>
							<td class="centercol">', _army_format_bonus($race['bonus_casualties']), '</td>
							<td class="centercol">', _army_format_bonus($race['bonus_attack']), '</td>
							<td class="centercol">', _army_format_bonus($race['bonus_defence']), '</td>
							<td class="centercol">', _army_format_bonus($race['bonus_spy']), '</td>
							<td class="centercol">', _army_format_bonus($race['bonus_sentry']), '</td>
							<td class="centercol">
								<input type="radio" name="race_id" value="', $race['id'], '"', ($first ? ' checked' : ''), '>
							</td>
						</tr>';

		$first = false;
	}

	echo '
					</tbody>
				</table>
				<div class="windowbg">
					<div class="righttext">
						<input type="submit" value="', $txt['army_race_choose'] ?? 'Choose Race', '" class="button">
					</div>
				</div>
			</form>
		</div>
	</div>';
}

/**
 * Reset / Leave template - confirmation page for resetting or leaving the army.
 *
 * Shows the player's current stats as a summary of what they will lose,
 * then offers two buttons: Reset Race (wipes progress, allows re-pick) and
 * Leave Army System (wipes progress and deactivates).
 *
 * Context variables used:
 *   $context['army_member']        - array, current player stats
 *   $context['army_currency']      - string, currency name
 *   $context['army_session_var']   - string, session variable name
 *   $context['army_session_id']    - string, session token value
 */
function template_army_reset()
{
	global $context, $txt, $scripturl;

	$member = $context['army_member'];

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_reset_title'] ?? 'Reset / Leave Army System', '</h3>
			</div>';

	// Warning message
	echo '
			<div class="errorbox">
				<strong>', $txt['army_reset_warning_title'] ?? 'Warning!', '</strong>
				<br>', $txt['army_reset_warning'] ?? 'Resetting or leaving the Army System will permanently destroy all of your progress, including soldiers, inventory, upgrades, and clan membership. This action cannot be undone.', '
			</div>';

	// Current stats summary
	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_reset_current_stats'] ?? 'Your Current Stats', '</h3>
			</div>
			<div class="windowbg">
				<table class="table_grid army_table" width="100%">
					<tbody>
						<tr class="windowbg">
							<td width="30%"><strong>', $txt['army_race'] ?? 'Race', '</strong></td>
							<td>', htmlspecialchars($member['race_name']), '</td>
						</tr>
						<tr class="windowbg">
							<td><strong>', $txt['army_army_size'] ?? 'Army Size', '</strong></td>
							<td>', $member['army_size'], '</td>
						</tr>
						<tr class="windowbg">
							<td><strong>', htmlspecialchars($context['army_currency'] ?? 'Gold'), '</strong></td>
							<td>', $member['army_points'], '</td>
						</tr>
						<tr class="windowbg">
							<td><strong>', $txt['army_total_attacks'] ?? 'Total Attacks', '</strong></td>
							<td>', $member['total_attacks'], '</td>
						</tr>
						<tr class="windowbg">
							<td><strong>', $txt['army_total_defends'] ?? 'Total Defends', '</strong></td>
							<td>', $member['total_defends'], '</td>
						</tr>
					</tbody>
				</table>
			</div>';

	// Reset and Leave buttons
	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_reset_actions'] ?? 'Choose an Action', '</h3>
			</div>
			<div class="windowbg">
				<form action="', $scripturl, '?action=army;sa=reset" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">

					<div class="army_reset_options">
						<div class="army_reset_option">
							<h4>', $txt['army_reset_race_title'] ?? 'Reset Race', '</h4>
							<p>', $txt['army_reset_race_desc'] ?? 'Wipe all progress (soldiers, inventory, gold, upgrades, clan) and choose a new race. Your lifetime attack/defense record will be preserved. You will receive starting resources when you pick a new race.', '</p>
							<input type="submit" name="reset_action" value="reset" class="button" onclick="return confirm(\'', ($txt['army_reset_confirm'] ?? 'Are you sure you want to reset your army? All progress will be lost!'), '\');">
						</div>

						<div class="army_reset_option">
							<h4>', $txt['army_leave_title'] ?? 'Leave Army System', '</h4>
							<p>', $txt['army_leave_desc'] ?? 'Completely leave the Army System. All progress will be wiped and your account will be deactivated. You can rejoin later by visiting the Army System and picking a new race.', '</p>
							<input type="submit" name="reset_action" value="leave" class="button" onclick="return confirm(\'', ($txt['army_leave_confirm'] ?? 'Are you sure you want to leave the Army System entirely? All progress will be lost!'), '\');">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>';
}

/**
 * Helper function to format a race bonus value with color coding.
 *
 * Positive values are shown in green with a + prefix.
 * Negative values are shown in red.
 * Zero values are shown in neutral gray.
 *
 * @param int $value The bonus value (percentage)
 * @return string HTML-formatted bonus string
 */
function _army_format_bonus($value)
{
	$value = (int) $value;

	if ($value > 0)
		return '<span class="army_bonus_positive">+' . $value . '%</span>';
	elseif ($value < 0)
		return '<span class="army_bonus_negative">' . $value . '%</span>';
	else
		return '<span class="army_bonus_neutral">0%</span>';
}
