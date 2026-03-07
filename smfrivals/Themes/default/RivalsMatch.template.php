<?php
/**
 * SMF Rivals - Match/Challenge Templates
 *
 * @package SMFRivals
 * @version 1.0.0
 */

/**
 * Recent match history with entity names and winner highlighting.
 */
function template_rivals_match_history()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_matches'], '</h3>
	</div>';

	if (empty($context['rivals_matches']))
	{
		echo '
	<div class="information">', $txt['rivals_no_matches'], '</div>';
		return;
	}

	echo '
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_ladder'], '</th>
				<th>', $txt['rivals_challenger'], '</th>
				<th>', $txt['rivals_match_score'], '</th>
				<th>', $txt['rivals_challengee'], '</th>
				<th>', $txt['rivals_status'], '</th>
				<th>', $txt['rivals_date'], '</th>
			</tr>
		</thead>
		<tbody>';

	foreach ($context['rivals_matches'] as $match)
	{
		$challenger_class = ($match['winner_id'] == $match['challenger_id']) ? ' rivals_match_winner' : '';
		$challengee_class = ($match['winner_id'] == $match['challengee_id']) ? ' rivals_match_winner' : '';

		echo '
			<tr class="windowbg">
				<td>', $match['ladder_name'], '</td>
				<td class="', $challenger_class, '">', $match['challenger_id'], '</td>
				<td><strong>', $match['challenger_score'], ' - ', $match['challengee_score'], '</strong></td>
				<td class="', $challengee_class, '">', $match['challengee_id'], '</td>
				<td>';

		if ($match['status'] == 1)
			echo '<span class="rivals_badge" style="background:#27ae60;">', $txt['rivals_match_completed'], '</span>';
		elseif ($match['status'] == 2)
			echo '<span class="rivals_badge">', $txt['rivals_match_disputed_status'], '</span>';
		else
			echo '<span class="rivals_badge" style="background:#f39c12;">', $txt['rivals_match_pending'], '</span>';

		echo '</td>
				<td>', !empty($match['completed_at']) ? timeformat($match['completed_at']) : '-', '</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>';

	if (!empty($context['page_index']))
		echo '
	<div class="pagesection">
		<div class="pagelinks">', $context['page_index'], '</div>
	</div>';
}

/**
 * Create challenge form.
 */
function template_rivals_create_challenge()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_create_challenge'], '</h3>
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

	if (empty($context['rivals_available_ladders']))
	{
		echo '
	<div class="information">', $txt['rivals_error_no_ladders'], '</div>';
		return;
	}

	echo '
	<div class="windowbg">
		<form action="', $scripturl, '?action=rivals;sa=challenge" method="post" accept-charset="', $context['character_set'], '">
			<dl class="settings">
				<dt><strong>', $txt['rivals_select_ladder'], '</strong></dt>
				<dd>
					<select name="id_ladder" id="rivals_challenge_ladder">';

	foreach ($context['rivals_available_ladders'] as $ladder)
	{
		echo '
						<option value="', $ladder['id_ladder'], '"',
							$ladder['id_ladder'] == $context['rivals_selected_ladder'] ? ' selected' : '', '>',
							$ladder['name'], '</option>';
	}

	echo '
					</select>
				</dd>

				<dt><strong>', $txt['rivals_select_opponent'], '</strong></dt>
				<dd>
					<div class="rivals_search_wrapper">
						<input type="text" id="rivals_opponent_search" autocomplete="off" class="input_text" />
						<input type="hidden" name="opponent_id" id="rivals_opponent_search_id" value="0" />
						<div id="rivals_opponent_results" class="rivals_search_results"></div>
					</div>
				</dd>

				<dt><strong>', $txt['rivals_ranked'], ' / ', $txt['rivals_unranked'], '</strong></dt>
				<dd>
					<label>
						<input type="checkbox" name="is_unranked" value="1" /> ', $txt['rivals_unranked'], '
					</label>
				</dd>

				<dt><strong>', $txt['rivals_challenge_details'], '</strong></dt>
				<dd>
					<textarea name="details" rows="4" cols="50" class="input_text"></textarea>
				</dd>
			</dl>

			<div class="righttext">
				<input type="submit" name="submit_challenge" value="', $txt['rivals_submit'], '" class="button" />
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>

	<script>
		// Initialize search - detect ladder type to search clans or users
		document.getElementById("rivals_challenge_ladder").addEventListener("change", function() {
			document.getElementById("rivals_opponent_search").value = "";
			document.getElementById("rivals_opponent_search_id").value = "0";
		});
		// Default search type based on first ladder
		RivalsSearch.init("rivals_opponent_search", "rivals_opponent_results", "clan");
	</script>';
}

/**
 * Challenge listing - incoming and outgoing.
 */
function template_rivals_challenge_list()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_challenges'], '</h3>
	</div>';

	if (!empty($context['rivals_sent_success']))
		echo '
	<div class="infobox">', $txt['rivals_challenge_sent'], '</div>';

	if (isset($_GET['accepted']))
		echo '
	<div class="infobox">', $txt['rivals_challenge_accepted'], '</div>';

	if (isset($_GET['declined']))
		echo '
	<div class="infobox">', $txt['rivals_challenge_declined'], '</div>';

	// Incoming Challenges
	echo '
	<div class="title_bar">
		<h4 class="titlebg">', $txt['rivals_incoming_challenges'], '</h4>
	</div>';

	if (empty($context['rivals_incoming']))
	{
		echo '
	<div class="information">', $txt['rivals_no_challenges'], '</div>';
	}
	else
	{
		echo '
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_challenger'], '</th>
				<th>', $txt['rivals_ladder'], '</th>
				<th>', $txt['rivals_ranked'], '?</th>
				<th>', $txt['rivals_date'], '</th>
				<th>', $txt['rivals_actions'], '</th>
			</tr>
		</thead>
		<tbody>';

		foreach ($context['rivals_incoming'] as $challenge)
		{
			echo '
			<tr class="windowbg">
				<td><a href="', $challenge['challenger_href'], '">', $challenge['challenger_name'], '</a></td>
				<td>', $challenge['ladder_name'], '</td>
				<td>', $challenge['is_unranked'] ? $txt['rivals_unranked'] : $txt['rivals_ranked'], '</td>
				<td>', $challenge['time'], '</td>
				<td>
					<a href="', $challenge['accept_href'], '" class="button">', $txt['rivals_accept_challenge'], '</a>
					<a href="', $challenge['decline_href'], '" class="button" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_decline_challenge'], '</a>
				</td>
			</tr>';

			if (!empty($challenge['details']))
				echo '
			<tr class="windowbg">
				<td colspan="5" class="rivals_challenge_details"><em>', $challenge['details'], '</em></td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}

	// Outgoing Challenges
	echo '
	<div class="title_bar">
		<h4 class="titlebg">', $txt['rivals_outgoing_challenges'], '</h4>
	</div>';

	if (empty($context['rivals_outgoing']))
	{
		echo '
	<div class="information">', $txt['rivals_no_challenges'], '</div>';
	}
	else
	{
		echo '
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_challengee'], '</th>
				<th>', $txt['rivals_ladder'], '</th>
				<th>', $txt['rivals_ranked'], '?</th>
				<th>', $txt['rivals_date'], '</th>
				<th>', $txt['rivals_status'], '</th>
			</tr>
		</thead>
		<tbody>';

		foreach ($context['rivals_outgoing'] as $challenge)
		{
			echo '
			<tr class="windowbg">
				<td>', $challenge['challengee_name'], '</td>
				<td>', $challenge['ladder_name'], '</td>
				<td>', $challenge['is_unranked'] ? $txt['rivals_unranked'] : $txt['rivals_ranked'], '</td>
				<td>', $challenge['time'], '</td>
				<td><span class="rivals_badge" style="background:#f39c12;">', $txt['rivals_match_pending'], '</span></td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}

	// Link to create new challenge
	echo '
	<div class="rivals_standings_actions">
		<a href="', $scripturl, '?action=rivals;sa=challenge" class="button">', $txt['rivals_create_challenge'], '</a>
	</div>';
}

/**
 * Match view + report form.
 */
function template_rivals_my_match()
{
	global $context, $txt, $scripturl;

	$match = $context['rivals_match'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_match'], ' #', $match['id_match'], ' - ', $match['ladder_name'], '</h3>
	</div>';

	if (isset($_GET['reported']))
		echo '
	<div class="infobox">', $txt['rivals_match_reported'], '</div>';

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

	// Match info display
	echo '
	<div class="windowbg">
		<div class="rivals_match_detail">
			<div class="rivals_match_teams">
				<div class="rivals_match_team rivals_match_team_left">
					<strong>', $context['rivals_challenger_name'], '</strong>
					<span class="rivals_match_label">', $txt['rivals_challenger'], '</span>
				</div>
				<div class="rivals_match_vs">VS</div>
				<div class="rivals_match_team rivals_match_team_right">
					<strong>', $context['rivals_challengee_name'], '</strong>
					<span class="rivals_match_label">', $txt['rivals_challengee'], '</span>
				</div>
			</div>';

	// Show round maps/modes if Decerto/CPC
	if ($match['ladder_style'] == 1 || $match['ladder_style'] == 2)
	{
		echo '
			<div class="rivals_match_rounds_info">';

		for ($r = 1; $r <= 3; $r++)
		{
			$map = $match['round' . $r . '_map'];
			$mode = $match['round' . $r . '_mode'];

			if (!empty($map))
			{
				echo '
				<div class="rivals_round_info">
					<strong>', $txt['rivals_round'], ' ', $r, ':</strong> ', $map;
				if (!empty($mode) && $mode !== '-')
					echo ' (', $mode, ')';
				echo '</div>';
			}
		}

		echo '
			</div>';
	}

	// Show reported scores if already reported
	if ($match['id_reporter'] > 0)
	{
		echo '
			<div class="rivals_match_scores">
				<h4>', $txt['rivals_match_score'], '</h4>
				<div class="rivals_score_display">
					<span class="rivals_score_num">', $match['challenger_score'], '</span>
					<span class="rivals_score_sep"> - </span>
					<span class="rivals_score_num">', $match['challengee_score'], '</span>
				</div>';

		// Per-round scores
		if ($match['ladder_style'] == 1 || $match['ladder_style'] == 2)
		{
			for ($r = 1; $r <= 3; $r++)
			{
				$sc = $match['round' . $r . '_score_challenger'];
				$se = $match['round' . $r . '_score_challengee'];
				if ($sc > 0 || $se > 0)
					echo '
				<div class="rivals_round_score">', $txt['rivals_round'], ' ', $r, ': ', $sc, ' - ', $se, '</div>';
			}
		}

		// Winner
		if ($match['winner_id'] == 9999999)
			echo '<div class="rivals_match_result"><strong>', $txt['rivals_draw'], '</strong></div>';
		elseif ($match['winner_id'] == $match['challenger_id'])
			echo '<div class="rivals_match_result"><strong>', $txt['rivals_winner'], ': ', $context['rivals_challenger_name'], '</strong></div>';
		elseif ($match['winner_id'] == $match['challengee_id'])
			echo '<div class="rivals_match_result"><strong>', $txt['rivals_winner'], ': ', $context['rivals_challengee_name'], '</strong></div>';

		echo '
			</div>';

		// Status badges
		echo '
			<div class="rivals_match_status">';

		if ($match['status'] == 0 && $match['id_reporter'] > 0)
			echo '<span class="rivals_badge" style="background:#f39c12;">', $txt['rivals_awaiting_confirmation'], '</span>';
		elseif ($match['status'] == 1)
			echo '<span class="rivals_badge" style="background:#27ae60;">', $txt['rivals_match_completed'], '</span>';
		elseif ($match['status'] == 2)
			echo '<span class="rivals_badge">', $txt['rivals_match_disputed_status'], '</span>';

		echo '
			</div>';
	}

	echo '
		</div>
	</div>';

	// Report form (only if match is pending and not yet reported)
	if ($context['rivals_can_report'])
	{
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_report_match'], '</h3>
	</div>
	<div class="windowbg">
		<form action="', $scripturl, '?action=rivals;sa=mymatch;match=', $match['id_match'], '" method="post" accept-charset="', $context['character_set'], '">
			<dl class="settings">';

		// Winner selection
		echo '
				<dt><strong>', $txt['rivals_winner'], '</strong></dt>
				<dd>
					<select name="winner_id">
						<option value="', $match['challenger_id'], '">', $context['rivals_challenger_name'], '</option>
						<option value="', $match['challengee_id'], '">', $context['rivals_challengee_name'], '</option>';

		// Draw option for football
		if ($match['ladder_style'] == 3)
			echo '
						<option value="9999999">', $txt['rivals_draw'], '</option>';

		echo '
					</select>
				</dd>';

		// Overall scores
		echo '
				<dt><strong>', $context['rivals_challenger_name'], ' ', $txt['rivals_match_score'], '</strong></dt>
				<dd><input type="number" name="challenger_score" value="0" min="0" class="input_text" style="width:80px;" /></dd>

				<dt><strong>', $context['rivals_challengee_name'], ' ', $txt['rivals_match_score'], '</strong></dt>
				<dd><input type="number" name="challengee_score" value="0" min="0" class="input_text" style="width:80px;" /></dd>';

		// Per-round scores for Decerto/CPC
		if ($match['ladder_style'] == 1 || $match['ladder_style'] == 2)
		{
			for ($r = 1; $r <= 3; $r++)
			{
				echo '
				<dt><strong>', $txt['rivals_round'], ' ', $r, '</strong></dt>
				<dd>
					<input type="number" name="r', $r, '_challenger" value="0" min="0" class="input_text" style="width:80px;" /> -
					<input type="number" name="r', $r, '_challengee" value="0" min="0" class="input_text" style="width:80px;" />
				</dd>';
			}
		}

		// Team names for football
		if ($match['ladder_style'] == 3)
		{
			echo '
				<dt><strong>', $context['rivals_challenger_name'], ' ', $txt['rivals_team_used'], '</strong></dt>
				<dd><input type="text" name="challenger_team" value="" class="input_text" /></dd>

				<dt><strong>', $context['rivals_challengee_name'], ' ', $txt['rivals_team_used'], '</strong></dt>
				<dd><input type="text" name="challengee_team" value="" class="input_text" /></dd>';
		}

		// MVP selection
		if (!empty($match['enable_mvp']) && !empty($context['rivals_team_members']))
		{
			for ($m = 1; $m <= 3; $m++)
			{
				echo '
				<dt><strong>', $txt['rivals_mvp' . $m], '</strong></dt>
				<dd>
					<select name="mvp', $m, '">
						<option value="0">-</option>';

				foreach ($context['rivals_team_members'] as $mid => $mname)
					echo '
						<option value="', $mid, '">', $mname, '</option>';

				echo '
					</select>
				</dd>';
			}
		}

		echo '
			</dl>';

		// Advanced stats
		if (!empty($match['enable_advstats']) && !empty($context['rivals_team_members']))
		{
			echo '
			<h4>', $txt['rivals_advanced_stats'], '</h4>
			<table class="table_grid rivals_table">
				<thead>
					<tr class="title_bar">
						<th>', $txt['rivals_name'], '</th>
						<th>', $txt['rivals_played'], '</th>
						<th>', $txt['rivals_kills'], '</th>
						<th>', $txt['rivals_deaths'], '</th>
						<th>', $txt['rivals_assists'], '</th>';

			if ($match['ladder_style'] == 3)
				echo '
						<th>', $txt['rivals_goals_for'], '</th>
						<th>', $txt['rivals_goals_against'], '</th>';

			echo '
					</tr>
				</thead>
				<tbody>';

			foreach ($context['rivals_team_members'] as $mid => $mname)
			{
				echo '
				<tr class="windowbg">
					<td>', $mname, '</td>
					<td><input type="checkbox" name="stats[', $mid, '][played]" value="1" /></td>
					<td><input type="number" name="stats[', $mid, '][kills]" value="0" min="0" style="width:60px;" /></td>
					<td><input type="number" name="stats[', $mid, '][deaths]" value="0" min="0" style="width:60px;" /></td>
					<td><input type="number" name="stats[', $mid, '][assists]" value="0" min="0" style="width:60px;" /></td>';

				if ($match['ladder_style'] == 3)
					echo '
					<td><input type="number" name="stats[', $mid, '][goals_for]" value="0" min="0" style="width:60px;" /></td>
					<td><input type="number" name="stats[', $mid, '][goals_against]" value="0" min="0" style="width:60px;" /></td>';

				echo '
				</tr>';
			}

			echo '
				</tbody>
			</table>';
		}

		echo '
			<div class="righttext" style="margin-top:12px;">
				<input type="submit" name="submit_report" value="', $txt['rivals_report_match'], '" class="button" />
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
	}

	// Link to match chat
	echo '
	<div class="rivals_standings_actions">
		<a href="', $scripturl, '?action=rivals;sa=matchchat;match=', $match['id_match'], '" class="button">', $txt['rivals_match_chat'], '</a>';

	// Link to confirm/dispute if applicable
	if ($match['status'] == 0 && $match['id_reporter'] > 0)
		echo '
		<a href="', $scripturl, '?action=rivals;sa=confirmmatch;match=', $match['id_match'], '" class="button">', $txt['rivals_confirm_match'], '</a>';

	echo '
	</div>';
}

/**
 * Confirm or dispute match.
 */
function template_rivals_confirm_match()
{
	global $context, $txt, $scripturl;

	$match = $context['rivals_match'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_confirm_match'], ' #', $match['id_match'], '</h3>
	</div>
	<div class="windowbg">
		<div class="rivals_match_detail">
			<div class="rivals_match_teams">
				<div class="rivals_match_team rivals_match_team_left">
					<strong>', $context['rivals_challenger_name'], '</strong>
				</div>
				<div class="rivals_match_vs">
					<strong>', $match['challenger_score'], ' - ', $match['challengee_score'], '</strong>
				</div>
				<div class="rivals_match_team rivals_match_team_right">
					<strong>', $context['rivals_challengee_name'], '</strong>
				</div>
			</div>
			<div class="rivals_match_result">
				<strong>', $txt['rivals_winner'], ':</strong> ', $context['rivals_winner_name'], '
			</div>';

	// Per-round scores
	if ($match['ladder_style'] == 1 || $match['ladder_style'] == 2)
	{
		for ($r = 1; $r <= 3; $r++)
		{
			$sc = $match['round' . $r . '_score_challenger'];
			$se = $match['round' . $r . '_score_challengee'];
			if ($sc > 0 || $se > 0)
				echo '
			<div class="rivals_round_score">', $txt['rivals_round'], ' ', $r, ': ', $sc, ' - ', $se, '</div>';
		}
	}

	echo '
		</div>

		<hr />

		<form action="', $scripturl, '?action=rivals;sa=confirmmatch;match=', $match['id_match'], '" method="post" accept-charset="', $context['character_set'], '">
			<dl class="settings">
				<dt><strong>', $txt['rivals_feedback'], '</strong></dt>
				<dd>
					<select name="feedback">
						<option value="5">5 - ', $txt['rivals_feedback_excellent'], '</option>
						<option value="4">4 - ', $txt['rivals_feedback_good'], '</option>
						<option value="3">3 - ', $txt['rivals_feedback_average'], '</option>
						<option value="2">2 - ', $txt['rivals_feedback_poor'], '</option>
						<option value="1">1 - ', $txt['rivals_feedback_bad'], '</option>
					</select>
				</dd>
			</dl>

			<div class="rivals_confirm_buttons">
				<input type="submit" name="do_confirm" value="', $txt['rivals_confirm_match'], '" class="button" />
				<input type="submit" name="do_dispute" value="', $txt['rivals_dispute_match'], '" class="button" onclick="return confirm(\'', $txt['rivals_confirm_dispute'], '\');" />
			</div>

			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

/**
 * MVP selection form.
 */
function template_rivals_match_mvp()
{
	global $context, $txt, $scripturl;

	$match = $context['rivals_match'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_select_mvp'], ' - ', $match['ladder_name'], '</h3>
	</div>
	<div class="windowbg">
		<form action="', $scripturl, '?action=rivals;sa=matchmvp;match=', $match['id_match'], '" method="post" accept-charset="', $context['character_set'], '">
			<dl class="settings">';

	for ($m = 1; $m <= 3; $m++)
	{
		echo '
				<dt><strong>', $txt['rivals_mvp' . $m], '</strong></dt>
				<dd>
					<select name="mvp', $m, '">
						<option value="0">-</option>';

		foreach ($context['rivals_team_members'] as $mid => $mname)
			echo '
						<option value="', $mid, '"', ($match['mvp' . $m] == $mid ? ' selected' : ''), '>', $mname, '</option>';

		echo '
					</select>
				</dd>';
	}

	echo '
			</dl>
			<div class="righttext">
				<input type="submit" name="save_mvp" value="', $txt['rivals_save'], '" class="button" />
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

/**
 * Match finder queue.
 */
function template_rivals_match_finder()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_match_finder'], '</h3>
	</div>';

	// Join queue form
	if (!empty($context['rivals_available_ladders']))
	{
		echo '
	<div class="windowbg">
		<form action="', $scripturl, '?action=rivals;sa=matchfinder" method="post" accept-charset="', $context['character_set'], '">
			<dl class="settings">
				<dt><strong>', $txt['rivals_select_ladder'], '</strong></dt>
				<dd>
					<select name="id_ladder">';

		foreach ($context['rivals_available_ladders'] as $ladder)
			echo '
						<option value="', $ladder['id_ladder'], '">', $ladder['name'], '</option>';

		echo '
					</select>
				</dd>

				<dt><strong>', $txt['rivals_matchfinder_wait'], '</strong></dt>
				<dd>
					<select name="wait_time">
						<option value="30">30 ', $txt['rivals_minutes'], '</option>
						<option value="60" selected>1 ', $txt['rivals_hour'], '</option>
						<option value="120">2 ', $txt['rivals_hours'], '</option>
						<option value="240">4 ', $txt['rivals_hours'], '</option>
						<option value="480">8 ', $txt['rivals_hours'], '</option>
						<option value="1440">24 ', $txt['rivals_hours'], '</option>
					</select>
				</dd>

				<dt><strong>', $txt['rivals_ranked'], '</strong></dt>
				<dd>
					<label><input type="checkbox" name="is_unranked" value="1" /> ', $txt['rivals_unranked'], '</label>
				</dd>
			</dl>

			<div class="righttext">
				<input type="submit" name="join_queue" value="', $txt['rivals_matchfinder_join'], '" class="button" />
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
	}

	// Queue listing
	echo '
	<div class="title_bar">
		<h4 class="titlebg">', $txt['rivals_matchfinder_queue'], '</h4>
	</div>';

	if (empty($context['rivals_queue']))
	{
		echo '
	<div class="information">', $txt['rivals_matchfinder_empty'], '</div>';
	}
	else
	{
		echo '
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_name'], '</th>
				<th>', $txt['rivals_ladder'], '</th>
				<th>', $txt['rivals_ranked'], '?</th>
				<th>', $txt['rivals_matchfinder_expires'], '</th>
				<th>', $txt['rivals_actions'], '</th>
			</tr>
		</thead>
		<tbody>';

		foreach ($context['rivals_queue'] as $entry)
		{
			echo '
			<tr class="windowbg">
				<td>', $entry['entity_name'], '</td>
				<td>', $entry['ladder_name'], '</td>
				<td>', $entry['is_unranked'] ? $txt['rivals_unranked'] : $txt['rivals_ranked'], '</td>
				<td>', timeformat($entry['expire_time']), '</td>
				<td>';

			if ($entry['is_mine'])
				echo '<a href="', $scripturl, '?action=rivals;sa=matchfinder;leave;entry=', $entry['id_entry'], ';', $context['session_var'], '=', $context['session_id'], '" class="button">', $txt['rivals_matchfinder_leave'], '</a>';
			else
				echo '<a href="', $scripturl, '?action=rivals;sa=challenge;ladder=', $entry['id_ladder'], '" class="button">', $txt['rivals_challenge'], '</a>';

			echo '</td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}
}

/**
 * Match chat with AJAX.
 */
function template_rivals_match_chat()
{
	global $context, $txt, $user_info;

	$match = $context['rivals_match'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_match_chat'], ' - ', $match['ladder_name'], ' #', $match['id_match'], '</h3>
	</div>
	<div class="windowbg">
		<div class="rivals_chat_container">
			<div class="rivals_chat_messages" id="rivals_chat_messages"></div>';

	if (!$user_info['is_guest'])
	{
		echo '
			<form class="rivals_chat_input" id="rivals_chat_form">
				<input type="text" id="rivals_chat_input" placeholder="', $txt['rivals_match_chat_placeholder'], '" maxlength="500" />
				<button type="submit">', $txt['rivals_submit'], '</button>
			</form>';
	}

	echo '
		</div>
	</div>

	<script>
		RivalsChat.init(', $match['id_match'], ', "', $context['session_var'], '", "', $context['session_id'], '");
	</script>';
}
?>