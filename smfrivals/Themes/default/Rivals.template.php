<?php
/**
 * SMF Rivals - Public Page Templates
 *
 * @package SMFRivals
 * @version 1.0.0
 */

/**
 * Platform listing grid.
 */
function template_rivals_platforms()
{
	global $context, $txt, $scripturl, $settings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_platforms'], '</h3>
	</div>';

	if (empty($context['rivals_platforms']))
	{
		echo '
	<div class="information">', $txt['rivals_no_results'], '</div>';
		return;
	}

	echo '
	<div class="rivals_platforms_grid">';

	foreach ($context['rivals_platforms'] as $platform)
	{
		echo '
		<div class="rivals_platform_card">
			<a href="', $platform['href'], '">';

		if (!empty($platform['logo']))
			echo '
				<img src="', $settings['default_images_url'], '/rivals/ladderlogo/', $platform['logo'], '" alt="', $platform['name'], '"
					', ($platform['logo_width'] > 0 ? ' width="' . $platform['logo_width'] . '"' : ''), ' />';

		echo '
				<h4>', $platform['name'], '</h4>
				<span class="rivals_count">', $platform['ladder_count'], ' ', $txt['rivals_ladders_count'], '</span>
			</a>
		</div>';
	}

	echo '
	</div>';
}

/**
 * Ladder listing for a platform with hierarchy.
 */
function template_rivals_ladders()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['rivals_platform']['name'], ' - ', $txt['rivals_ladders'], '</h3>
	</div>';

	if (empty($context['rivals_ladders']))
	{
		echo '
	<div class="information">', $txt['rivals_no_results'], '</div>';
		return;
	}

	echo '
	<div class="rivals_ladders_list">';

	foreach ($context['rivals_ladders'] as $ladder)
	{
		echo '
		<div class="rivals_ladder_item', !empty($ladder['is_locked']) ? ' rivals_locked' : '', '">
			<div class="rivals_ladder_header">
				<a href="', $ladder['href'], '">
					<strong>', $ladder['name'], '</strong>
					', !empty($ladder['short_name']) ? '<span class="rivals_short_name">(' . $ladder['short_name'] . ')</span>' : '', '
				</a>
				', !empty($ladder['is_locked']) ? '<span class="rivals_badge">' . $txt['rivals_ladder_locked'] . '</span>' : '', '
				', !empty($ladder['is_1v1']) ? '<span class="rivals_badge rivals_badge_1v1">' . $txt['rivals_ladder_1v1'] . '</span>' : '', '
			</div>';

		if (!empty($ladder['description']))
			echo '
			<p class="rivals_ladder_desc">', $ladder['description'], '</p>';

		// Sub-ladders
		if (!empty($ladder['children']))
		{
			echo '
			<div class="rivals_sub_ladders">
				<h5>', $txt['rivals_sub_ladders'], '</h5>
				<ul>';

			foreach ($ladder['children'] as $child)
			{
				echo '
				<li>
					<a href="', $child['href'], '">', $child['name'], '</a>
					', !empty($child['is_locked']) ? '<span class="rivals_badge_small">' . $txt['rivals_ladder_locked'] . '</span>' : '', '
				</li>';
			}

			echo '
				</ul>
			</div>';
		}

		echo '
		</div>';
	}

	echo '
	</div>';
}

/**
 * Standings table for a ladder.
 * Shows ranked teams/users with scores, status icons, rank changes.
 */
function template_rivals_standings()
{
	global $context, $txt, $scripturl, $settings;

	$ladder = $context['rivals_ladder'];
	$is_1v1 = !empty($ladder['is_1v1']);
	$is_football = ($ladder['ladder_style'] == 3);
	$is_rth = ($ladder['ranking_system'] == 2);
	$is_swap = ($ladder['ranking_system'] == 1);

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $ladder['name'], ' - ', $txt['rivals_standings'], '</h3>
	</div>';

	// RTH winner announcement
	if (!empty($context['rivals_rth_winner']))
	{
		$winner = $context['rivals_rth_winner'];
		echo '
	<div class="rivals_rth_winner_banner">
		<strong>', sprintf($txt['rivals_rth_winner_msg'], $winner['entity_name']), '</strong>
	</div>';
	}

	// Action buttons: join / leave / freeze / rules
	echo '
	<div class="rivals_standings_actions">';

	if (!empty($context['rivals_can_join']))
		echo '
		<a href="', $scripturl, '?action=rivals;sa=standings;ladder=', $ladder['id_ladder'], ';do=join;', $context['session_var'], '=', $context['session_id'], '" class="button">', $txt['rivals_join_ladder'], '</a>';

	if (!empty($context['rivals_can_leave']))
		echo '
		<a href="', $scripturl, '?action=rivals;sa=standings;ladder=', $ladder['id_ladder'], ';do=leave;', $context['session_var'], '=', $context['session_id'], '" class="button" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_leave_ladder'], '</a>';

	if (!empty($context['rivals_in_ladder']) && empty($ladder['is_locked']))
		echo '
		<a href="', $scripturl, '?action=rivals;sa=standings;ladder=', $ladder['id_ladder'], ';do=freeze;', $context['session_var'], '=', $context['session_id'], '" class="button">', $txt['rivals_freeze_toggle'], '</a>';

	echo '
		<a href="', $scripturl, '?action=rivals;sa=rules;ladder=', $ladder['id_ladder'], '" class="button">', $txt['rivals_rules'], '</a>
	</div>';

	// Tabs: Standings + Advanced Stats (if enabled)
	if (!empty($ladder['enable_advstats']) && !empty($context['rivals_advstats']))
	{
		echo '
	<div class="rivals_tabs_container">
		<div class="rivals_tabs">
			<div class="rivals_tab active" data-tab="rivals_tab_standings">', $txt['rivals_standings'], '</div>
			<div class="rivals_tab" data-tab="rivals_tab_advstats">', $txt['rivals_advanced_stats'], '</div>
		</div>';
	}

	// Standings tab content
	if (!empty($ladder['enable_advstats']) && !empty($context['rivals_advstats']))
		echo '
		<div id="rivals_tab_standings" class="rivals_tab_content active">';

	if (empty($context['rivals_standings_data']))
	{
		echo '
	<div class="information">', $txt['rivals_no_standings'], '</div>';
	}
	else
	{
		echo '
	<table class="table_grid rivals_table rivals_standings_table">
		<thead>
			<tr class="title_bar">
				<th class="rivals_col_rank">#</th>
				<th class="rivals_col_status"></th>
				<th class="rivals_col_name">', $is_1v1 ? $txt['rivals_name'] : $txt['rivals_clan'], '</th>
				<th>', $txt['rivals_wins'], '</th>
				<th>', $txt['rivals_losses'], '</th>
				<th>', $txt['rivals_draws'], '</th>';

		if ($is_football)
			echo '
				<th>', $txt['rivals_goals_for'], '</th>
				<th>', $txt['rivals_goals_against'], '</th>
				<th>', $txt['rivals_goal_difference'], '</th>';

		if (!$is_swap)
			echo '
				<th>', $txt['rivals_score'], '</th>';

		echo '
				<th>', $txt['rivals_ratio'], '</th>
				<th>', $txt['rivals_streak'], '</th>
				<th class="rivals_col_icons"></th>
			</tr>
		</thead>
		<tbody>';

		foreach ($context['rivals_standings_data'] as $entry)
		{
			$row_class = 'windowbg';
			if ($entry['is_frozen'])
				$row_class .= ' rivals_standings_row_frozen';

			echo '
			<tr class="', $row_class, '">
				<td class="rivals_col_rank">';

			// Rank position with movement arrow
			echo '<span class="rivals_rank_pos">', $entry['position'], '</span>';

			if ($entry['rank_change'] === 'up')
				echo ' <span class="rivals_rank_up" title="', $txt['rivals_rank_up'], '">&#9650;</span>';
			elseif ($entry['rank_change'] === 'down')
				echo ' <span class="rivals_rank_down" title="', $txt['rivals_rank_down'], '">&#9660;</span>';
			else
				echo ' <span class="rivals_rank_same">&#8226;</span>';

			echo '</td>';

			// Activity status icon
			echo '
				<td class="rivals_col_status"><span class="rivals_status_icon rivals_status_', $entry['activity'], '" title="', $txt['rivals_status_' . $entry['activity']], '"></span></td>';

			// Name with optional logo
			echo '
				<td class="rivals_col_name">';

			if (!$is_1v1 && !empty($entry['logo']))
				echo '<img src="', $settings['default_images_url'], '/rivals/clanlogo/', $entry['logo'], '" class="rivals_standings_logo" alt="" /> ';

			echo '<a href="', $entry['href'], '">', $entry['entity_name'], '</a>';

			if ($is_1v1 && !empty($entry['gamer_name']))
				echo ' <span class="rivals_gamer_tag">(', $entry['gamer_name'], ')</span>';

			// Frozen badge
			if ($entry['is_frozen'])
				echo ' <span class="rivals_badge_small" style="background:#3498db;">', $txt['rivals_frozen'], '</span>';

			echo '</td>';

			// W-L-D
			echo '
				<td>', $entry['wins'], '</td>
				<td>', $entry['losses'], '</td>
				<td>', $entry['draws'], '</td>';

			// Football: GF, GA, GD
			if ($is_football)
			{
				$gd = $entry['goals_for'] - $entry['goals_against'];
				echo '
				<td>', $entry['goals_for'], '</td>
				<td>', $entry['goals_against'], '</td>
				<td>', ($gd > 0 ? '+' : ''), $gd, '</td>';
			}

			// Score (ELO/RTH mode)
			if (!$is_swap)
				echo '
				<td>', $entry['score'], '</td>';

			// Ratio
			echo '
				<td>', $entry['ratio'], '</td>';

			// Streak
			echo '
				<td>';
			if ($entry['streak_display'] > 0)
				echo '<span class="rivals_streak_hot">W', $entry['streak_display'], '</span>';
			elseif ($entry['streak_display'] < 0)
				echo '<span class="rivals_streak_cold">L', abs($entry['streak_display']), '</span>';
			else
				echo '-';
			echo '</td>';

			// Icons column: hot/cold, crown, tomb, football, chicken
			echo '
				<td class="rivals_col_icons">';

			if ($entry['hot_cold'] === 'hot')
				echo '<span class="rivals_icon_hot" title="', $txt['rivals_icon_hot_streak'], '">&#128293;</span>';
			elseif ($entry['hot_cold'] === 'cold')
				echo '<span class="rivals_icon_cold" title="', $txt['rivals_icon_cold_streak'], '">&#10052;</span>';

			if ($entry['is_crown'])
				echo '<span class="rivals_icon_crown" title="', $txt['rivals_icon_crown'], '">&#128081;</span>';

			if ($entry['is_tomb'])
				echo '<span class="rivals_icon_tomb" title="', $txt['rivals_icon_tomb'], '">&#9760;</span>';

			if ($entry['is_golden_shoe'])
				echo '<span class="rivals_icon_golden_shoe" title="', $txt['rivals_icon_golden_shoe'], '">&#9917;</span>';

			if ($entry['is_soap'])
				echo '<span class="rivals_icon_soap" title="', $txt['rivals_icon_soap'], '">&#128167;</span>';

			if ($entry['rth_chicken'])
				echo '<span class="rivals_icon_chicken" title="', $txt['rivals_icon_chicken'], '">&#128020;</span>';

			echo '</td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';

		// Pagination
		if (!empty($context['page_index']))
			echo '
	<div class="pagesection">
		<div class="pagelinks">', $context['page_index'], '</div>
	</div>';
	}

	// Close standings tab
	if (!empty($ladder['enable_advstats']) && !empty($context['rivals_advstats']))
		echo '
		</div>';

	// Advanced Stats tab
	if (!empty($ladder['enable_advstats']) && !empty($context['rivals_advstats']))
	{
		echo '
		<div id="rivals_tab_advstats" class="rivals_tab_content">
		<table class="table_grid rivals_table">
			<thead>
				<tr class="title_bar">
					<th>#</th>
					<th>', $txt['rivals_name'], '</th>
					<th>', $txt['rivals_kills'], '</th>
					<th>', $txt['rivals_deaths'], '</th>
					<th>', $txt['rivals_assists'], '</th>
					<th>', $txt['rivals_kd_ratio'], '</th>';

		if ($is_football)
			echo '
					<th>', $txt['rivals_goals_for'], '</th>
					<th>', $txt['rivals_goals_against'], '</th>';

		echo '
					<th>', $txt['rivals_mvp'], '</th>
					<th>', $txt['rivals_matches_played'], '</th>
				</tr>
			</thead>
			<tbody>';

		$pos = 1;
		foreach ($context['rivals_advstats'] as $stat)
		{
			echo '
				<tr class="windowbg">
					<td>', $pos++, '</td>
					<td><a href="', $stat['href'], '">', $stat['name'], '</a>';

			if (!empty($stat['gamer_name']))
				echo ' <span class="rivals_gamer_tag">(', $stat['gamer_name'], ')</span>';

			echo '</td>
					<td>', $stat['kills'], '</td>
					<td>', $stat['deaths'], '</td>
					<td>', $stat['assists'], '</td>
					<td>', $stat['kd_ratio'], '</td>';

			if ($is_football)
				echo '
					<td>', $stat['goals_for'], '</td>
					<td>', $stat['goals_against'], '</td>';

			echo '
					<td>', $stat['mvp_count'], '</td>
					<td>', $stat['matches_played'], '</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>
		</div>';

		// Close tabs container
		echo '
	</div>';
	}
}

/**
 * Ladder rules display with sections.
 */
function template_rivals_rules()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $context['rivals_ladder_name'], ' - ', $txt['rivals_rules'], '</h3>
	</div>';

	if (empty($context['rivals_rules']))
	{
		echo '
	<div class="information">', $txt['rivals_no_rules'], '</div>';
		return;
	}

	$rules = $context['rivals_rules'];

	// Requirements
	if (!empty($rules['requirements']))
	{
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_rules_requirements'], '</h3>
	</div>
	<div class="windowbg rivals_rules_section">', $rules['requirements'], '</div>';
	}

	// General Rules
	if (!empty($rules['general_rules']))
	{
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_rules_general'], '</h3>
	</div>
	<div class="windowbg rivals_rules_section">', $rules['general_rules'], '</div>';
	}

	// Configuration
	if (!empty($rules['configuration']))
	{
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_rules_configuration'], '</h3>
	</div>
	<div class="windowbg rivals_rules_section">', $rules['configuration'], '</div>';
	}

	// Prohibitions
	if (!empty($rules['prohibitions']))
	{
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_rules_prohibitions'], '</h3>
	</div>
	<div class="windowbg rivals_rules_section">', $rules['prohibitions'], '</div>';
	}
}

/**
 * MVP leaderboard.
 */
function template_rivals_mvp()
{
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_mvp'], '</h3>
	</div>';

	if (empty($context['rivals_mvp_leaders']))
	{
		echo '
	<div class="information">', $txt['rivals_no_results'], '</div>';
		return;
	}

	echo '
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_rank'], '</th>
				<th>', $txt['rivals_name'], '</th>
				<th>', $txt['rivals_mvp'], '</th>
			</tr>
		</thead>
		<tbody>';

	$rank = 1;
	foreach ($context['rivals_mvp_leaders'] as $leader)
	{
		echo '
			<tr class="windowbg">
				<td>', $rank++, '</td>
				<td><a href="', $leader['href'], '">', $leader['name'], '</a></td>
				<td>', $leader['mvp_count'], '</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>';
}

/**
 * MVP chart placeholder.
 */
function template_rivals_mvp_chart()
{
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_mvp_chart'], '</h3>
	</div>
	<div class="information">', $txt['rivals_no_results'], '</div>';
}

/**
 * Random map of the day.
 */
function template_rivals_random()
{
	global $context, $txt, $settings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_random'], '</h3>
	</div>';

	if (empty($context['rivals_random_map']))
	{
		echo '
	<div class="information">', $txt['rivals_no_results'], '</div>';
		return;
	}

	$map = $context['rivals_random_map'];

	echo '
	<div class="windowbg rivals_random_map">
		<h4>', $map['game_name'], '</h4>';

	if (!empty($map['image']))
		echo '
		<img src="', $settings['default_images_url'], '/rivals/icons/', $map['image'], '" alt="', $map['game_name'], '" />';

	echo '
		<p>', $map['short_name'], '</p>
	</div>';
}

/**
 * User leaderboard.
 */
function template_rivals_leaderboard()
{
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_leaderboard'], '</h3>
	</div>';

	if (empty($context['rivals_leaderboard']))
	{
		echo '
	<div class="information">', $txt['rivals_no_results'], '</div>';
		return;
	}

	echo '
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_rank'], '</th>
				<th>', $txt['rivals_name'], '</th>
				<th>', $txt['rivals_experience'], '</th>
				<th>', $txt['rivals_mvp'], '</th>
				<th>', $txt['rivals_pwner'], '</th>
				<th>', $txt['rivals_chicken'], '</th>
			</tr>
		</thead>
		<tbody>';

	foreach ($context['rivals_leaderboard'] as $entry)
	{
		echo '
			<tr class="windowbg">
				<td>', $entry['rank'], '</td>
				<td><a href="', $entry['href'], '">', $entry['name'], '</a></td>
				<td>', $entry['exp'], '</td>
				<td>', $entry['mvp_count'], '</td>
				<td>', $entry['pwner_count'], '</td>
				<td>', $entry['chicken_count'], '</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>';
}

/**
 * League standings table (football/league style with points).
 */
function template_rivals_league_standings()
{
	global $context, $txt, $scripturl, $settings;

	$ladder = $context['rivals_ladder'];
	$is_1v1 = !empty($context['rivals_is_1v1']);
	$is_football = !empty($context['rivals_is_football']);

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $ladder['name'], ' - ', $txt['rivals_league_standings'], '</h3>
	</div>';

	// Season selector
	if (!empty($context['rivals_seasons']))
	{
		echo '
	<div class="rivals_filter_bar">';

		// Current season link
		echo '
		<a href="', $scripturl, '?action=rivals;sa=league;ladder=', $ladder['id_ladder'], '" class="button', empty($context['rivals_viewing_season']) ? ' active' : '', '">', $txt['rivals_season_current'], '</a>';

		foreach ($context['rivals_seasons'] as $season)
		{
			if ($season['status'] == 0)
				echo '
		<a href="', $scripturl, '?action=rivals;sa=league;ladder=', $ladder['id_ladder'], ';season=', $season['id_season'], '" class="button', ($context['rivals_viewing_season'] == $season['id_season']) ? ' active' : '', '">', $season['name'], '</a>';
		}

		echo '
	</div>';
	}

	// Active season name
	if (!empty($context['rivals_active_season']))
		echo '
	<div class="information">', $txt['rivals_season_active'], ': <strong>', $context['rivals_active_season']['name'], '</strong></div>';

	if (empty($context['rivals_standings_data']))
	{
		echo '
	<div class="information">', $txt['rivals_no_standings'], '</div>';
		return;
	}

	echo '
	<table class="table_grid rivals_table rivals_league_table">
		<thead>
			<tr class="title_bar">
				<th class="rivals_col_rank">#</th>
				<th class="rivals_col_name">', $is_1v1 ? $txt['rivals_name'] : $txt['rivals_clan'], '</th>
				<th>', $txt['rivals_played'], '</th>
				<th>', $txt['rivals_wins'], '</th>
				<th>', $txt['rivals_draws'], '</th>
				<th>', $txt['rivals_losses'], '</th>';

	if ($is_football)
		echo '
				<th>', $txt['rivals_goals_for'], '</th>
				<th>', $txt['rivals_goals_against'], '</th>
				<th>', $txt['rivals_goal_difference'], '</th>';

	echo '
				<th class="rivals_col_points"><strong>', $txt['rivals_points'], '</strong></th>
			</tr>
		</thead>
		<tbody>';

	foreach ($context['rivals_standings_data'] as $entry)
	{
		$row_class = 'windowbg';
		if ($entry['position'] <= 1)
			$row_class .= ' rivals_league_champion';
		if (!empty($entry['is_frozen']))
			$row_class .= ' rivals_standings_row_frozen';

		echo '
			<tr class="', $row_class, '">
				<td class="rivals_col_rank">', $entry['position'], '</td>
				<td class="rivals_col_name">';

		if (!$is_1v1 && !empty($entry['logo']))
			echo '<img src="', $settings['default_images_url'], '/rivals/clanlogo/', $entry['logo'], '" class="rivals_standings_logo" alt="" /> ';

		echo '<a href="', $entry['href'], '">', $entry['entity_name'], '</a>';

		if ($is_1v1 && !empty($entry['gamer_name']))
			echo ' <span class="rivals_gamer_tag">(', $entry['gamer_name'], ')</span>';

		echo '</td>
				<td>', $entry['played'], '</td>
				<td>', $entry['wins'], '</td>
				<td>', $entry['draws'], '</td>
				<td>', $entry['losses'], '</td>';

		if ($is_football)
		{
			$gd = $entry['goal_difference'];
			echo '
				<td>', $entry['goals_for'], '</td>
				<td>', $entry['goals_against'], '</td>
				<td>', ($gd > 0 ? '+' : ''), $gd, '</td>';
		}

		echo '
				<td class="rivals_col_points"><strong>', $entry['points'], '</strong></td>
			</tr>';
	}

	echo '
		</tbody>
	</table>';

	// Link to season history
	if (isset($ladder['id_ladder']))
		echo '
	<div class="rivals_standings_actions">
		<a href="', $scripturl, '?action=rivals;sa=seasons;ladder=', $ladder['id_ladder'], '" class="button">', $txt['rivals_season_history'], '</a>
	</div>';
}

/**
 * Season history - list of seasons with optional detail view.
 */
function template_rivals_season_history()
{
	global $context, $txt, $scripturl;

	$ladder = $context['rivals_ladder'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $ladder['name'], ' - ', $txt['rivals_season_history'], '</h3>
	</div>';

	if (empty($context['rivals_seasons_list']))
	{
		echo '
	<div class="information">', $txt['rivals_no_seasons'], '</div>';
		return;
	}

	// Season list
	echo '
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_season'], '</th>
				<th>', $txt['rivals_status'], '</th>
				<th>', $txt['rivals_season_teams'], '</th>
				<th>', $txt['rivals_actions'], '</th>
			</tr>
		</thead>
		<tbody>';

	foreach ($context['rivals_seasons_list'] as $season)
	{
		$active = !empty($context['rivals_viewing_season']) && $context['rivals_viewing_season'] == $season['id'];

		echo '
			<tr class="windowbg', $active ? ' rivals_season_active_row' : '', '">
				<td><strong>', $season['name'], '</strong></td>
				<td><span class="rivals_badge ', ($season['status'] == 1 ? 'rivals_status_2' : 'rivals_status_3'), '">', $season['status_label'], '</span></td>
				<td>', $season['team_count'], '</td>
				<td>
					<a href="', $season['href'], '" class="button">', $txt['rivals_view'], '</a>
					<a href="', $scripturl, '?action=rivals;sa=league;ladder=', $ladder['id_ladder'], ';season=', $season['id'], '" class="button">', $txt['rivals_league_standings'], '</a>
				</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>';

	// Season detail view
	if (!empty($context['rivals_season_detail']))
	{
		$season_name = isset($context['rivals_seasons_list'][$context['rivals_viewing_season']])
			? $context['rivals_seasons_list'][$context['rivals_viewing_season']]['name'] : '?';

		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $season_name, ' - ', $txt['rivals_standings'], '</h3>
	</div>
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>#</th>
				<th>', $txt['rivals_name'], '</th>
				<th>', $txt['rivals_wins'], '</th>
				<th>', $txt['rivals_draws'], '</th>
				<th>', $txt['rivals_losses'], '</th>
				<th>', $txt['rivals_goals_for'], '</th>
				<th>', $txt['rivals_goals_against'], '</th>
				<th>', $txt['rivals_goal_difference'], '</th>
				<th>', $txt['rivals_points'], '</th>
				<th>', $txt['rivals_score'], '</th>
				<th>', $txt['rivals_season_best_rank'], '</th>
				<th>', $txt['rivals_season_worst_rank'], '</th>
			</tr>
		</thead>
		<tbody>';

		foreach ($context['rivals_season_detail'] as $entry)
		{
			$gd = $entry['goal_difference'];

			echo '
			<tr class="windowbg">
				<td>', $entry['position'], '</td>
				<td><a href="', $entry['href'], '">', $entry['entity_name'], '</a></td>
				<td>', $entry['wins'], '</td>
				<td>', $entry['draws'], '</td>
				<td>', $entry['losses'], '</td>
				<td>', $entry['goals_for'], '</td>
				<td>', $entry['goals_against'], '</td>
				<td>', ($gd > 0 ? '+' : ''), $gd, '</td>
				<td><strong>', $entry['points'], '</strong></td>
				<td>', $entry['score'], '</td>
				<td>', $entry['best_rank'] > 0 ? $entry['best_rank'] : '-', '</td>
				<td>', $entry['worst_rank'] > 0 ? $entry['worst_rank'] : '-', '</td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}
}

/**
 * Profile section for Rivals.
 */
function template_rivals_profile()
{
	global $context, $txt, $scripturl;

	$member = $context['rivals_member'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_profile'], '</h3>
	</div>';

	// Editable gamer name form
	if (!empty($context['rivals_can_edit']))
	{
		echo '
	<form action="', $scripturl, '?action=profile;u=', $context['id_member'], ';area=rivals" method="post" accept-charset="', $context['character_set'], '">
		<div class="windowbg">
			<dl class="settings">
				<dt><strong>', $txt['rivals_gamer_name'], ':</strong></dt>
				<dd><input type="text" name="rivals_gamer_name" value="', !empty($member['rivals_gamer_name']) ? $member['rivals_gamer_name'] : '', '" size="30" /></dd>
			</dl>
			<input type="submit" name="save_gamer_name" value="', $txt['rivals_save'], '" class="button" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</div>
	</form>';
	}

	echo '
	<div class="windowbg">
		<dl class="settings">';

	if (empty($context['rivals_can_edit']))
		echo '
			<dt><strong>', $txt['rivals_gamer_name'], ':</strong></dt>
			<dd>', !empty($member['rivals_gamer_name']) ? $member['rivals_gamer_name'] : '<em>-</em>', '</dd>';

	echo '
			<dt><strong>', $txt['rivals_profile_clan'], ':</strong></dt>
			<dd>';

	if (!empty($context['rivals_clan']))
		echo '<a href="', $scripturl, '?action=rivals;sa=clan;id=', $context['rivals_clan']['id_clan'], '">', $context['rivals_clan']['name'], '</a>';
	else
		echo '<em>', $txt['rivals_profile_no_clan'], '</em>';

	echo '</dd>';

	echo '
			<dt><strong>', $txt['rivals_experience'], ':</strong></dt>
			<dd>', $member['rivals_ladder_value'], '</dd>';

	echo '
			<dt><strong>', $txt['rivals_mvp'], ':</strong></dt>
			<dd>', $member['rivals_mvp_count'], '</dd>';

	echo '
			<dt><strong>', $txt['rivals_pwner'], ':</strong></dt>
			<dd>', $member['rivals_pwner_count'], '</dd>';

	echo '
			<dt><strong>', $txt['rivals_chicken'], ':</strong></dt>
			<dd>', $member['rivals_chicken_count'], '</dd>';

	echo '
		</dl>
	</div>';

	// Ladder standings
	if (!empty($context['rivals_standings']))
	{
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_standings'], '</h3>
	</div>
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_ladder'], '</th>
				<th>', $txt['rivals_rank'], '</th>
				<th>', $txt['rivals_score'], '</th>
				<th>', $txt['rivals_wld'], '</th>
			</tr>
		</thead>
		<tbody>';

		foreach ($context['rivals_standings'] as $standing)
		{
			echo '
			<tr class="windowbg">
				<td><a href="', $scripturl, '?action=rivals;sa=standings;ladder=', $standing['id_ladder'], '">', $standing['ladder_name'], '</a></td>
				<td>', $standing['current_rank'] > 0 ? $standing['current_rank'] : '-', '</td>
				<td>', $standing['score'], '</td>
				<td>', $standing['wins'], '-', $standing['losses'], '-', $standing['draws'], '</td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}

	// Tournament history
	if (!empty($context['rivals_tournament_history']))
	{
		echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_profile_tournaments'], '</h3>
	</div>
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_tournament'], '</th>
				<th>', $txt['rivals_status'], '</th>
			</tr>
		</thead>
		<tbody>';

		$status_labels = array(0 => $txt['rivals_tournament_draft'], 1 => $txt['rivals_tournament_signups'], 2 => $txt['rivals_tournament_active'], 3 => $txt['rivals_tournament_archived']);

		foreach ($context['rivals_tournament_history'] as $t)
		{
			echo '
			<tr class="windowbg">
				<td><a href="', $scripturl, '?action=rivals;sa=brackets;tournament=', $t['id_tournament'], '">', $t['name'], '</a></td>
				<td>', isset($status_labels[$t['status']]) ? $status_labels[$t['status']] : '?', '</td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}
}
?>