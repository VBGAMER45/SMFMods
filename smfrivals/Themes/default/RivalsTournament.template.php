<?php
/**
 * SMF Rivals - Tournament Templates
 * Tournament list, brackets, signup, and management.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

/**
 * Tournament listing page.
 */
function template_rivals_tournament_list()
{
	global $context, $txt, $scripturl, $settings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_tournaments'], '</h3>
	</div>';

	// Status filter tabs
	echo '
	<div class="rivals_filter_bar">
		<a href="', $scripturl, '?action=rivals;sa=tournaments" class="button', $context['rivals_status_filter'] == -1 ? ' active' : '', '">', $txt['rivals_all'], '</a>
		<a href="', $scripturl, '?action=rivals;sa=tournaments;status=1" class="button', $context['rivals_status_filter'] == 1 ? ' active' : '', '">', $txt['rivals_tournament_signups'], '</a>
		<a href="', $scripturl, '?action=rivals;sa=tournaments;status=2" class="button', $context['rivals_status_filter'] == 2 ? ' active' : '', '">', $txt['rivals_tournament_active'], '</a>
		<a href="', $scripturl, '?action=rivals;sa=tournaments;status=3" class="button', $context['rivals_status_filter'] == 3 ? ' active' : '', '">', $txt['rivals_tournament_archived'], '</a>
	</div>';

	if (empty($context['rivals_tournaments']))
	{
		echo '
	<div class="information">', $txt['rivals_no_tournaments'], '</div>';
		return;
	}

	echo '
	<div class="rivals_tournament_grid">';

	foreach ($context['rivals_tournaments'] as $tournament)
	{
		$status_class = 'rivals_status_' . $tournament['status'];

		echo '
		<div class="rivals_tournament_card">
			<div class="rivals_tournament_card_header">';

		if (!empty($tournament['logo']))
			echo '
				<img src="', $settings['default_images_url'], '/rivals/tournamentlogo/', $tournament['logo'], '" alt="" class="rivals_tournament_logo" />';

		echo '
				<div class="rivals_tournament_card_title">
					<h4>', $tournament['name'], '</h4>';

		if (!empty($tournament['short_name']))
			echo '
					<span class="rivals_short_name">(', $tournament['short_name'], ')</span>';

		echo '
				</div>
			</div>
			<div class="rivals_tournament_card_body">
				<div class="rivals_tournament_meta">
					<span class="rivals_badge ', $status_class, '">', $tournament['status_label'], '</span>
					<span class="rivals_badge">', $tournament['type_label'], '</span>';

		if ($tournament['is_user_based'])
			echo '
					<span class="rivals_badge rivals_badge_1v1">', $txt['rivals_ladder_1v1'], '</span>';

		echo '
				</div>
				<dl class="rivals_tournament_stats">
					<dt>', $txt['rivals_tournament_bracket_size'], ':</dt>
					<dd>', $tournament['bracket_size'], '</dd>
					<dt>', $txt['rivals_tournament_entries'], ':</dt>
					<dd>', $tournament['entry_count'], ' / ', $tournament['bracket_size'], '</dd>
					<dt>', $txt['rivals_tournament_start'], ':</dt>
					<dd>', $tournament['start_date'], '</dd>
				</dl>
			</div>
			<div class="rivals_tournament_card_actions">
				<a href="', $tournament['href_brackets'], '" class="button">', $txt['rivals_tournament_brackets'], '</a>';

		if ($tournament['status'] == 1)
			echo '
				<a href="', $tournament['href_signup'], '" class="button">', $txt['rivals_tournament_signup'], '</a>';

		echo '
			</div>
		</div>';
	}

	echo '
	</div>';

	// Pagination
	if (!empty($context['page_index']))
		echo '
	<div class="pagesection">
		<div class="pagelinks">', $context['page_index'], '</div>
	</div>';
}

/**
 * Tournament bracket visualization.
 * Renders winners bracket (and optionally losers bracket) using CSS flexbox.
 */
function template_rivals_brackets()
{
	global $context, $txt, $scripturl;

	$tournament = $context['rivals_tournament'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $tournament['name'], ' - ', $txt['rivals_tournament_brackets'], '</h3>
	</div>';

	// Tournament info bar
	echo '
	<div class="windowbg rivals_tournament_info">
		<span><strong>', $txt['rivals_status'], ':</strong> ';

	$status_labels = array(
		0 => $txt['rivals_tournament_draft'],
		1 => $txt['rivals_tournament_signups'],
		2 => $txt['rivals_tournament_active'],
		3 => $txt['rivals_tournament_archived'],
	);
	echo isset($status_labels[$tournament['status']]) ? $status_labels[$tournament['status']] : '?';

	echo '</span>';

	if (!empty($tournament['info']))
		echo '
		<div class="rivals_tournament_description">', $tournament['info'], '</div>';

	echo '
	</div>';

	// Success message
	if (isset($_GET['signedup']))
		echo '
	<div class="infobox">', $txt['rivals_tournament_signed_up'], '</div>';

	if (empty($context['rivals_bracket_rounds']))
	{
		echo '
	<div class="information">', $txt['rivals_no_results'], '</div>';
		return;
	}

	// Winners bracket
	echo '
	<div class="rivals_bracket_label">', $txt['rivals_winners_bracket'], '</div>
	<div class="rivals_bracket_container">
		<div class="rivals_bracket">';

	foreach ($context['rivals_bracket_rounds'] as $round_num => $round)
	{
		echo '
			<div class="rivals_bracket_round">
				<div class="rivals_bracket_round_label">', $round['label'], '</div>';

		foreach ($round['matches'] as $match)
		{
			$t1_class = ($match['winner_id'] > 0 && $match['winner_id'] == $match['team1_id']) ? ' rivals_bracket_winner' : '';
			$t2_class = ($match['winner_id'] > 0 && $match['winner_id'] == $match['team2_id']) ? ' rivals_bracket_winner' : '';
			$t1_loser = ($match['winner_id'] > 0 && $match['winner_id'] != $match['team1_id'] && $match['team1_id'] > 0) ? ' rivals_bracket_loser' : '';
			$t2_loser = ($match['winner_id'] > 0 && $match['winner_id'] != $match['team2_id'] && $match['team2_id'] > 0) ? ' rivals_bracket_loser' : '';
			$bye_class = $match['is_bye'] ? ' rivals_bracket_bye' : '';

			echo '
				<div class="rivals_bracket_match', $bye_class, '">
					<div class="rivals_bracket_team', $t1_class, $t1_loser, '">
						<span class="rivals_bracket_team_name">', $match['team1_name'], '</span>
						<span class="rivals_bracket_team_score">', $match['team1_score'] !== '' ? $match['team1_score'] : '-', '</span>
					</div>
					<div class="rivals_bracket_team', $t2_class, $t2_loser, '">
						<span class="rivals_bracket_team_name">', $match['team2_name'], '</span>
						<span class="rivals_bracket_team_score">', $match['team2_score'] !== '' ? $match['team2_score'] : '-', '</span>
					</div>
				</div>';
		}

		echo '
			</div>';
	}

	echo '
		</div>
	</div>';

	// Losers bracket (double elimination)
	if (!empty($context['rivals_losers_bracket']))
	{
		echo '
	<div class="rivals_bracket_label">', $txt['rivals_losers_bracket'], '</div>
	<div class="rivals_bracket_container">
		<div class="rivals_bracket">';

		foreach ($context['rivals_losers_bracket'] as $round_num => $round)
		{
			echo '
			<div class="rivals_bracket_round">
				<div class="rivals_bracket_round_label">', $round['label'], '</div>';

			foreach ($round['matches'] as $match)
			{
				$t1_class = ($match['winner_id'] > 0 && $match['winner_id'] == $match['team1_id']) ? ' rivals_bracket_winner' : '';
				$t2_class = ($match['winner_id'] > 0 && $match['winner_id'] == $match['team2_id']) ? ' rivals_bracket_winner' : '';

				echo '
				<div class="rivals_bracket_match">
					<div class="rivals_bracket_team', $t1_class, '">
						<span class="rivals_bracket_team_name">', $match['team1_name'], '</span>
						<span class="rivals_bracket_team_score">', $match['team1_score'] !== '' ? $match['team1_score'] : '-', '</span>
					</div>
					<div class="rivals_bracket_team', $t2_class, '">
						<span class="rivals_bracket_team_name">', $match['team2_name'], '</span>
						<span class="rivals_bracket_team_score">', $match['team2_score'] !== '' ? $match['team2_score'] : '-', '</span>
					</div>
				</div>';
			}

			echo '
			</div>';
		}

		echo '
		</div>
	</div>';
	}

	// Actions
	if ($tournament['status'] == 1)
		echo '
	<div class="rivals_standings_actions">
		<a href="', $scripturl, '?action=rivals;sa=signup;tournament=', $tournament['id_tournament'], '" class="button">', $txt['rivals_tournament_signup'], '</a>
	</div>';
}

/**
 * Tournament signup form.
 */
function template_rivals_tournament_signup()
{
	global $context, $txt, $scripturl;

	$tournament = $context['rivals_tournament'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $tournament['name'], ' - ', $txt['rivals_tournament_signup'], '</h3>
	</div>';

	// Errors
	if (!empty($context['rivals_errors']))
	{
		echo '
	<div class="errorbox">
		<ul>';
		foreach ($context['rivals_errors'] as $error)
			echo '
			<li>', $error, '</li>';
		echo '
		</ul>
	</div>';
	}

	// Tournament info
	echo '
	<div class="windowbg">
		<dl class="settings">
			<dt><strong>', $txt['rivals_tournament'], ':</strong></dt>
			<dd>', $tournament['name'], '</dd>
			<dt><strong>', $txt['rivals_tournament_bracket_size'], ':</strong></dt>
			<dd>', $tournament['bracket_size'], '</dd>
			<dt><strong>', $txt['rivals_tournament_entries'], ':</strong></dt>
			<dd>', $context['rivals_entry_count'], ' / ', $tournament['bracket_size'], '</dd>';

	if (!empty($tournament['start_date']))
		echo '
			<dt><strong>', $txt['rivals_tournament_start'], ':</strong></dt>
			<dd>', timeformat($tournament['start_date']), '</dd>';

	if (!empty($tournament['info']))
		echo '
			<dt><strong>', $txt['rivals_details'], ':</strong></dt>
			<dd>', $tournament['info'], '</dd>';

	echo '
		</dl>
	</div>';

	// Already signed up
	if ($context['rivals_already_signed_up'])
	{
		echo '
	<div class="infobox">', $txt['rivals_error_already_signed_up'], '</div>
	<div class="rivals_standings_actions">
		<a href="', $scripturl, '?action=rivals;sa=signup;tournament=', $tournament['id_tournament'], ';withdraw;', $context['session_var'], '=', $context['session_id'], '"
			class="button" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_tournament_withdraw'], '</a>
		<a href="', $scripturl, '?action=rivals;sa=brackets;tournament=', $tournament['id_tournament'], '" class="button">', $txt['rivals_tournament_brackets'], '</a>
	</div>';
		return;
	}

	// Full
	if ($context['rivals_is_full'])
	{
		echo '
	<div class="errorbox">', $txt['rivals_error_tournament_full'], '</div>';
		return;
	}

	// Signup form
	echo '
	<form action="', $scripturl, '?action=rivals;sa=signup;tournament=', $tournament['id_tournament'], '" method="post" accept-charset="', $context['character_set'], '">
		<div class="windowbg">';

	// Roster selection for restricted tournaments
	if (!empty($context['rivals_rosters']))
	{
		echo '
			<dl class="settings">
				<dt><strong>', $txt['rivals_roster'], ':</strong></dt>
				<dd>
					<select name="id_roster">';

		foreach ($context['rivals_rosters'] as $roster)
		{
			echo '
						<option value="', $roster['id'], '"', !$roster['valid'] ? ' disabled' : '', '>',
							$roster['name'], ' (', $roster['member_count'], ' ', $txt['rivals_clan_members'], ')',
							!$roster['valid'] ? ' - ' . $txt['rivals_error_roster_invalid'] : '',
						'</option>';
		}

		echo '
					</select>
				</dd>
			</dl>';
	}

	echo '
			<div class="rivals_standings_actions">
				<button type="submit" name="do_signup" class="button">', $txt['rivals_tournament_signup'], '</button>
			</div>
		</div>
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';
}

/**
 * User's tournament entries.
 */
function template_rivals_my_tournaments()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_my_tournaments'], '</h3>
	</div>';

	if (empty($context['rivals_my_entries']))
	{
		echo '
	<div class="information">', $txt['rivals_no_tournaments'], '</div>';
		return;
	}

	echo '
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_tournament'], '</th>
				<th>', $txt['rivals_status'], '</th>
				<th>', $txt['rivals_round'], '</th>
				<th>', $txt['rivals_actions'], '</th>
			</tr>
		</thead>
		<tbody>';

	foreach ($context['rivals_my_entries'] as $entry)
	{
		echo '
			<tr class="windowbg">
				<td><a href="', $entry['href_brackets'], '">', $entry['tournament_name'], '</a></td>
				<td><span class="rivals_badge rivals_status_', $entry['tournament_status'], '">', $entry['status_label'], '</span></td>
				<td>';

		if ($entry['bracket_round'] > 0)
			echo sprintf($txt['rivals_bracket_round'], $entry['bracket_round']);
		else
			echo '-';

		echo '</td>
				<td>
					<a href="', $entry['href_brackets'], '" class="button">', $txt['rivals_tournament_brackets'], '</a>
				</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>';
}
?>