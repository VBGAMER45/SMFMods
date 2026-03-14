<?php
/**
 * Army System - Main Templates
 *
 * Provides template functions for the dashboard, member rankings, player
 * profiles, profile integration, vacation mode, and the shared sidebar
 * navigation used across all Army System pages.
 *
 * @package ArmySystem
 * @version 1.0
 */

/**
 * Shared sidebar navigation displayed on all Army System pages.
 *
 * Reads $context['army_nav'] (built by ArmySystemMain in ArmySystem.php)
 * and outputs a vertical navigation menu. The active page is highlighted.
 * Links with 'guest' => false are hidden from guests.
 */
function template_army_sidebar()
{
	global $context, $txt, $user_info;

	$nav = $context['army_nav'] ?? array();

	echo '
		<div class="army_sidebar">
			<div class="cat_bar">
				<h3 class="catbg">', htmlspecialchars($context['army_name'] ?? ($txt['army_system'] ?? 'Army System')), '</h3>
			</div>
			<div class="windowbg">
				<ul class="army_nav_list">';

	foreach ($nav as $key => $item)
	{
		// Hide items marked as non-guest from guests
		if ($user_info['is_guest'] && isset($item['guest']) && $item['guest'] === false)
			continue;

		$active_class = !empty($item['active']) ? ' army_nav_active' : '';

		echo '
					<li class="army_nav_item', $active_class, '">
						<a href="', $item['url'], '">', htmlspecialchars($item['label']), '</a>
					</li>';
	}

	echo '
				</ul>
			</div>
		</div>';
}

/**
 * Dashboard template - the main Army System landing page.
 *
 * For logged-in players: shows full army statistics, power ratings,
 * soldier/merc breakdowns, fort/siege info, quick action links, and
 * recent events.
 *
 * For guests: shows a top-10 player ranking table.
 *
 * Context variables used:
 *   $context['army_guest_view']     - bool, true if guest
 *   $context['army_top_players']    - array, top 10 players (guest view)
 *   $context['army_member']         - array, current player's army data
 *   $context['army_attack_power']   - string, formatted attack power
 *   $context['army_defense_power']  - string, formatted defense power
 *   $context['army_spy_power']      - string, formatted spy power
 *   $context['army_sentry_power']   - string, formatted sentry power
 *   $context['army_events']         - array, recent events
 *   $context['army_on_vacation']    - bool, vacation status
 *   $context['army_currency']       - string, currency name
 */
function template_army_dashboard()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">';

	// --- Guest view: top players list ---
	if (!empty($context['army_guest_view']))
	{
		echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_dashboard_title'] ?? 'Army System', '</h3>
			</div>
			<div class="windowbg">
				<p>', $txt['army_guest_welcome'] ?? 'Welcome to the Army System! Register and join to build your army, battle other players, and climb the ranks.', '</p>
			</div>';

		// Top players table
		if (!empty($context['army_top_players']))
		{
			echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_top_players'] ?? 'Top Players', '</h3>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th class="centercol" width="5%">#</th>
						<th>', $txt['army_col_player'] ?? 'Player', '</th>
						<th>', $txt['army_col_race'] ?? 'Race', '</th>
						<th class="centercol">', $txt['army_col_army_size'] ?? 'Army Size', '</th>
						<th class="centercol">', $txt['army_col_rank'] ?? 'Rank', '</th>
					</tr>
				</thead>
				<tbody>';

			$position = 0;
			foreach ($context['army_top_players'] as $player)
			{
				$position++;
				$row_class = ($position % 2 === 0) ? 'windowbg' : 'windowbg';

				echo '
					<tr class="', $row_class, '">
						<td class="centercol">', $position, '</td>
						<td>
							<a href="', $scripturl, '?action=army;sa=profile;u=', $player['id'], '">', htmlspecialchars($player['name']), '</a>
						</td>
						<td>', htmlspecialchars($player['race_name']), '</td>
						<td class="centercol">', $player['army_size'], '</td>
						<td class="centercol">', $player['rank_level'], '</td>
					</tr>';
			}

			echo '
				</tbody>
			</table>';
		}

		echo '
		</div>
	</div>';

		return;
	}

	// --- Logged-in player view ---
	$member = $context['army_member'];

	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_dashboard_title'] ?? 'Dashboard', '</h3>
			</div>';

	// Vacation warning banner
	if (!empty($context['army_on_vacation']))
	{
		echo '
			<div class="noticebox">
				', $txt['army_vacation_active'] ?? 'You are currently on vacation. Your army is protected from attacks, but you cannot perform most actions.', '
				<br><strong>', $txt['army_vacation_ends'] ?? 'Vacation ends:', '</strong> ', timeformat($context['army_vacation_end']), '
			</div>';
	}

	// Main stats panel
	echo '
			<div class="windowbg">
				<div class="army_stats_grid">
					<div class="army_stats_section">
						<h4>', $txt['army_overview'] ?? 'Overview', '</h4>
						<dl class="army_stats_list">
							<dt>', $txt['army_race'] ?? 'Race', '</dt>
							<dd>';

	if (!empty($member['race_icon']))
		echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($member['race_icon']), '" alt="" class="army_race_icon"> ';

	echo htmlspecialchars($member['race_name']), '</dd>
							<dt>', $txt['army_army_size'] ?? 'Army Size', '</dt>
							<dd>', $member['army_size'], '</dd>
							<dt>', htmlspecialchars($context['army_currency'] ?? 'Gold'), '</dt>
							<dd>', $member['army_points'], '</dd>
							<dt>', $txt['army_rank'] ?? 'Rank', '</dt>
							<dd>', $member['rank_level'], '</dd>
							<dt>', $txt['army_attack_turns'] ?? 'Attack Turns', '</dt>
							<dd>', $member['attack_turns'], '</dd>
						</dl>
					</div>
					<div class="army_stats_section">
						<h4>', $txt['army_soldiers'] ?? 'Soldiers', '</h4>
						<dl class="army_stats_list">
							<dt>', $txt['army_soldiers_attack'] ?? 'Attack', '</dt>
							<dd>', $member['soldiers_attack'], '</dd>
							<dt>', $txt['army_soldiers_defense'] ?? 'Defense', '</dt>
							<dd>', $member['soldiers_defense'], '</dd>
							<dt>', $txt['army_soldiers_spy'] ?? 'Spy', '</dt>
							<dd>', $member['soldiers_spy'], '</dd>
							<dt>', $txt['army_soldiers_sentry'] ?? 'Sentry', '</dt>
							<dd>', $member['soldiers_sentry'], '</dd>
							<dt>', $txt['army_soldiers_untrained'] ?? 'Untrained', '</dt>
							<dd>', $member['soldiers_untrained'], '</dd>
						</dl>
					</div>
					<div class="army_stats_section">
						<h4>', $txt['army_mercenaries'] ?? 'Mercenaries', '</h4>
						<dl class="army_stats_list">
							<dt>', $txt['army_mercs_attack'] ?? 'Attack Mercs', '</dt>
							<dd>', $member['mercs_attack'], '</dd>
							<dt>', $txt['army_mercs_defense'] ?? 'Defense Mercs', '</dt>
							<dd>', $member['mercs_defense'], '</dd>
							<dt>', $txt['army_mercs_untrained'] ?? 'Untrained Mercs', '</dt>
							<dd>', $member['mercs_untrained'], '</dd>
						</dl>
					</div>
					<div class="army_stats_section">
						<h4>', $txt['army_fortifications'] ?? 'Fortifications', '</h4>
						<dl class="army_stats_list">
							<dt>', $txt['army_fort_level'] ?? 'Fort', '</dt>
							<dd>', htmlspecialchars($member['fort_name']), ' (', $txt['army_level'] ?? 'Level', ' ', $member['fort_level'], ')</dd>
							<dt>', $txt['army_siege_level'] ?? 'Siege', '</dt>
							<dd>', htmlspecialchars($member['siege_name']), ' (', $txt['army_level'] ?? 'Level', ' ', $member['siege_level'], ')</dd>
						</dl>
					</div>
					<div class="army_stats_section">
						<h4>', $txt['army_power_ratings'] ?? 'Power Ratings', '</h4>
						<dl class="army_stats_list">
							<dt>', $txt['army_attack_power'] ?? 'Attack Power', '</dt>
							<dd>', $context['army_attack_power'], '</dd>
							<dt>', $txt['army_defense_power'] ?? 'Defense Power', '</dt>
							<dd>', $context['army_defense_power'], '</dd>
							<dt>', $txt['army_spy_power'] ?? 'Spy Power', '</dt>
							<dd>', $context['army_spy_power'], '</dd>
							<dt>', $txt['army_sentry_power'] ?? 'Sentry Power', '</dt>
							<dd>', $context['army_sentry_power'], '</dd>
							<dt>', $txt['army_naval_power'] ?? 'Naval Power', '</dt>
							<dd>', $context['army_naval_power'] ?? '0', '</dd>
						</dl>
					</div>
					<div class="army_stats_section">
						<h4>', $txt['army_record'] ?? 'Combat Record', '</h4>
						<dl class="army_stats_list">
							<dt>', $txt['army_total_attacks'] ?? 'Total Attacks', '</dt>
							<dd>', $member['total_attacks'], '</dd>
							<dt>', $txt['army_total_defends'] ?? 'Total Defends', '</dt>
							<dd>', $member['total_defends'], '</dd>
						</dl>
					</div>
				</div>
			</div>';

	// Quick action links
	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_quick_actions'] ?? 'Quick Actions', '</h3>
			</div>
			<div class="windowbg">
				<div class="buttonlist">
					<a class="button" href="', $scripturl, '?action=army;sa=attack">', $txt['army_btn_attack'] ?? 'Attack', '</a>
					<a class="button" href="', $scripturl, '?action=army;sa=training">', $txt['army_btn_train'] ?? 'Train', '</a>
					<a class="button" href="', $scripturl, '?action=army;sa=armor">', $txt['army_btn_shop'] ?? 'Armory', '</a>
					<a class="button" href="', $scripturl, '?action=army;sa=spy">', $txt['army_btn_spy'] ?? 'Spy', '</a>
					<a class="button" href="', $scripturl, '?action=army;sa=mercs">', $txt['army_btn_mercs'] ?? 'Mercenaries', '</a>
					<a class="button" href="', $scripturl, '?action=army;sa=members">', $txt['army_btn_rankings'] ?? 'Rankings', '</a>
				</div>
			</div>';

	// Recent events
	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_recent_events'] ?? 'Recent Events', '</h3>
			</div>';

	if (!empty($context['army_events']))
	{
		echo '
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th width="25%">', $txt['army_col_time'] ?? 'Time', '</th>
						<th>', $txt['army_col_event'] ?? 'Event', '</th>
					</tr>
				</thead>
				<tbody>';

		foreach ($context['army_events'] as $event)
		{
			echo '
					<tr class="windowbg">
						<td>', $event['time'], '</td>
						<td>', $event['text'], '</td>
					</tr>';
		}

		echo '
				</tbody>
			</table>';
	}
	else
	{
		echo '
			<div class="windowbg">
				<p class="centertext">', $txt['army_no_events'] ?? 'No recent events.', '</p>
			</div>';
	}

	echo '
		</div>
	</div>';
}

/**
 * Member rankings template - paginated list of all active army players.
 *
 * Context variables used:
 *   $context['army_members']     - array, members for current page
 *   $context['page_index']       - string, SMF pagination HTML
 *   $context['total_members']    - int, total member count
 */
function template_army_members()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_members_title'] ?? 'Army Rankings', '</h3>
			</div>';

	// Pagination - top
	echo '
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>';

	// Members table
	echo '
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th class="centercol" width="5%">#</th>
						<th>', $txt['army_col_player'] ?? 'Player', '</th>
						<th>', $txt['army_col_race'] ?? 'Race', '</th>
						<th class="centercol">', $txt['army_col_army_size'] ?? 'Army Size', '</th>
						<th class="centercol">', $txt['army_col_rank'] ?? 'Rank', '</th>
						<th class="centercol">', $txt['army_col_attacks'] ?? 'Attacks', '</th>
						<th class="centercol">', $txt['army_col_defends'] ?? 'Defends', '</th>
					</tr>
				</thead>
				<tbody>';

	if (!empty($context['army_members']))
	{
		$alt = false;
		foreach ($context['army_members'] as $member)
		{
			$row_class = $alt ? 'windowbg' : 'windowbg';
			$alt = !$alt;

			echo '
					<tr class="', $row_class, '">
						<td class="centercol">', $member['position'], '</td>
						<td>
							<a href="', $member['profile_url'], '">', htmlspecialchars($member['name']), '</a>
						</td>
						<td>', htmlspecialchars($member['race_name']), '</td>
						<td class="centercol">', $member['army_size'], '</td>
						<td class="centercol">', $member['rank_level'], '</td>
						<td class="centercol">', $member['total_attacks'], '</td>
						<td class="centercol">', $member['total_defends'], '</td>
					</tr>';
		}
	}
	else
	{
		echo '
					<tr class="windowbg">
						<td colspan="7" class="centercol">', $txt['army_no_members'] ?? 'No active army members found.', '</td>
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
 * Player profile template - shows a specific player's army profile.
 *
 * Displays limited info to other players; full details for own profile.
 * Includes action buttons to attack/spy on other players.
 *
 * Context variables used:
 *   $context['army_profile'] - array, target player's army data
 *   $context['army_currency'] - string, currency name
 */
function template_army_profile()
{
	global $context, $txt, $scripturl, $user_info;

	$profile = $context['army_profile'];

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', htmlspecialchars($profile['name']), ' - ', $txt['army_profile_title'] ?? 'Army Profile', '</h3>
			</div>';

	// Status banners
	if (!empty($profile['on_vacation']))
	{
		echo '
			<div class="noticebox">
				', $txt['army_profile_on_vacation'] ?? 'This player is currently on vacation and cannot be attacked.', '
			</div>';
	}

	if (empty($profile['is_active']))
	{
		echo '
			<div class="errorbox">
				', $txt['army_profile_inactive'] ?? 'This player is no longer active in the Army System.', '
			</div>';
	}

	// Stats panel
	echo '
			<div class="windowbg">
				<div class="army_stats_grid">
					<div class="army_stats_section">
						<h4>', $txt['army_overview'] ?? 'Overview', '</h4>
						<dl class="army_stats_list">
							<dt>', $txt['army_race'] ?? 'Race', '</dt>
							<dd>';

	if (!empty($profile['race_icon']))
		echo '<img src="', $context['army_images_url'], '/', htmlspecialchars($profile['race_icon']), '" alt="" class="army_race_icon"> ';

	echo htmlspecialchars($profile['race_name']), '</dd>
							<dt>', $txt['army_army_size'] ?? 'Army Size', '</dt>
							<dd>', $profile['army_size'], '</dd>
							<dt>', $txt['army_rank'] ?? 'Rank', '</dt>
							<dd>', $profile['rank_level'], '</dd>
							<dt>', $txt['army_total_attacks'] ?? 'Total Attacks', '</dt>
							<dd>', $profile['total_attacks'], '</dd>
							<dt>', $txt['army_total_defends'] ?? 'Total Defends', '</dt>
							<dd>', $profile['total_defends'], '</dd>
							<dt>', $txt['army_fort_level'] ?? 'Fort', '</dt>
							<dd>', htmlspecialchars($profile['fort_name']), ' (', $txt['army_level'] ?? 'Level', ' ', $profile['fort_level'], ')</dd>
							<dt>', $txt['army_siege_level'] ?? 'Siege', '</dt>
							<dd>', htmlspecialchars($profile['siege_name']), ' (', $txt['army_level'] ?? 'Level', ' ', $profile['siege_level'], ')</dd>
						</dl>
					</div>';

	// Extra details visible only to the profile owner
	if (!empty($profile['is_own']))
	{
		echo '
					<div class="army_stats_section">
						<h4>', $txt['army_your_details'] ?? 'Your Details', '</h4>
						<dl class="army_stats_list">
							<dt>', htmlspecialchars($context['army_currency'] ?? 'Gold'), '</dt>
							<dd>', $profile['army_points'] ?? '0', '</dd>
							<dt>', $txt['army_soldiers_attack'] ?? 'Attack Soldiers', '</dt>
							<dd>', $profile['soldiers_attack'] ?? '0', '</dd>
							<dt>', $txt['army_soldiers_defense'] ?? 'Defense Soldiers', '</dt>
							<dd>', $profile['soldiers_defense'] ?? '0', '</dd>
							<dt>', $txt['army_soldiers_spy'] ?? 'Spy Soldiers', '</dt>
							<dd>', $profile['soldiers_spy'] ?? '0', '</dd>
							<dt>', $txt['army_soldiers_sentry'] ?? 'Sentry Soldiers', '</dt>
							<dd>', $profile['soldiers_sentry'] ?? '0', '</dd>
							<dt>', $txt['army_soldiers_untrained'] ?? 'Untrained Soldiers', '</dt>
							<dd>', $profile['soldiers_untrained'] ?? '0', '</dd>
							<dt>', $txt['army_attack_turns'] ?? 'Attack Turns', '</dt>
							<dd>', $profile['attack_turns'] ?? 0, '</dd>
						</dl>
					</div>';
	}

	echo '
				</div>
			</div>';

	// Action buttons (for other players only)
	if (empty($profile['is_own']) && !$user_info['is_guest'] && !empty($profile['is_active']) && empty($profile['on_vacation']))
	{
		echo '
			<div class="windowbg">
				<div class="buttonlist">';

		if (allowedTo('army_attack'))
		{
			echo '
					<a class="button" href="', $scripturl, '?action=army;sa=attack;target=', $profile['id'], '">', $txt['army_btn_attack_player'] ?? 'Attack this Player', '</a>';
		}

		if (allowedTo('army_spy'))
		{
			echo '
					<a class="button" href="', $scripturl, '?action=army;sa=spy;target=', $profile['id'], '">', $txt['army_btn_spy_player'] ?? 'Spy on this Player', '</a>';
		}

		echo '
				</div>
			</div>';
	}

	// Link to SMF profile
	echo '
			<div class="windowbg">
				<a href="', $profile['smf_profile_url'], '">', $txt['army_view_forum_profile'] ?? 'View Forum Profile', '</a>
			</div>
		</div>
	</div>';
}

/**
 * Simplified profile stats for SMF profile integration.
 *
 * Displayed within the SMF profile page (no sidebar). Shows basic army
 * information in a compact table format.
 *
 * Context variables used:
 *   $context['army_profile_stats'] - array|false, player stats or false if not participating
 */
function template_army_profile_stats()
{
	global $context, $txt, $scripturl;

	$stats = $context['army_profile_stats'] ?? false;

	// Not participating
	if ($stats === false)
	{
		echo '
		<div class="windowbg">
			<p class="centertext">', $txt['army_not_participating'] ?? 'This member is not participating in the Army System.', '</p>
		</div>';

		return;
	}

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['army_profile_stats_title'] ?? 'Army Stats', '</h3>
		</div>
		<div class="windowbg">';

	// Status banners
	if (!empty($stats['on_vacation']))
	{
		echo '
			<div class="noticebox">
				', $txt['army_profile_on_vacation'] ?? 'This player is currently on vacation.', '
			</div>';
	}

	echo '
			<table class="table_grid army_table" width="100%">
				<tbody>
					<tr class="windowbg">
						<td width="30%"><strong>', $txt['army_race'] ?? 'Race', '</strong></td>
						<td>', htmlspecialchars($stats['race_name']), '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_army_size'] ?? 'Army Size', '</strong></td>
						<td>', $stats['army_size'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_rank'] ?? 'Rank', '</strong></td>
						<td>', $stats['rank_level'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_total_attacks'] ?? 'Total Attacks', '</strong></td>
						<td>', $stats['total_attacks'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_total_defends'] ?? 'Total Defends', '</strong></td>
						<td>', $stats['total_defends'], '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_fort_level'] ?? 'Fort', '</strong></td>
						<td>', htmlspecialchars($stats['fort_name']), ' (', $txt['army_level'] ?? 'Level', ' ', $stats['fort_level'], ')</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_siege_level'] ?? 'Siege', '</strong></td>
						<td>', htmlspecialchars($stats['siege_name']), ' (', $txt['army_level'] ?? 'Level', ' ', $stats['siege_level'], ')</td>
					</tr>';

	// Extra details visible only to the profile owner
	if (!empty($stats['is_own']))
	{
		echo '
					<tr class="windowbg">
						<td><strong>', htmlspecialchars($context['army_currency'] ?? 'Gold'), '</strong></td>
						<td>', $stats['army_points'] ?? '0', '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_soldiers_attack'] ?? 'Attack Soldiers', '</strong></td>
						<td>', $stats['soldiers_attack'] ?? '0', '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_soldiers_defense'] ?? 'Defense Soldiers', '</strong></td>
						<td>', $stats['soldiers_defense'] ?? '0', '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_soldiers_spy'] ?? 'Spy Soldiers', '</strong></td>
						<td>', $stats['soldiers_spy'] ?? '0', '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_soldiers_sentry'] ?? 'Sentry Soldiers', '</strong></td>
						<td>', $stats['soldiers_sentry'] ?? '0', '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_soldiers_untrained'] ?? 'Untrained Soldiers', '</strong></td>
						<td>', $stats['soldiers_untrained'] ?? '0', '</td>
					</tr>
					<tr class="windowbg">
						<td><strong>', $txt['army_attack_turns'] ?? 'Attack Turns', '</strong></td>
						<td>', $stats['attack_turns'] ?? 0, '</td>
					</tr>';
	}

	echo '
				</tbody>
			</table>';

	// Link to full army profile
	echo '
			<br>
			<a class="button" href="', $stats['army_url'], '">', $txt['army_view_full_profile'] ?? 'View Full Army Profile', '</a>
		</div>';
}

/**
 * Vacation mode template.
 *
 * Shows vacation status and controls for entering or leaving vacation.
 *
 * Context variables used:
 *   $context['army_vacation']     - array with vacation state/settings
 *   $context['army_session_var']  - string, session variable name
 *   $context['army_session_id']   - string, session token value
 */
function template_army_vacation()
{
	global $context, $txt, $scripturl;

	$vacation = $context['army_vacation'];

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_vacation_title'] ?? 'Vacation Mode', '</h3>
			</div>';

	if (!empty($vacation['on_vacation']))
	{
		// Currently on vacation
		echo '
			<div class="windowbg">
				<div class="noticebox">
					', $txt['army_vacation_active'] ?? 'You are currently on vacation. Your army is protected from attacks.', '
				</div>
				<dl class="army_stats_list">
					<dt>', $txt['army_vacation_started'] ?? 'Vacation Started', '</dt>
					<dd>', $vacation['vacation_start_formatted'], '</dd>
					<dt>', $txt['army_vacation_ends'] ?? 'Vacation Ends', '</dt>
					<dd>', $vacation['vacation_end_formatted'], '</dd>
					<dt>', $txt['army_vacation_days_left'] ?? 'Days Remaining', '</dt>
					<dd>', $vacation['vacation_remaining_days'], '</dd>
				</dl>';

		// End vacation early button (if allowed)
		if (!empty($vacation['vacation_back']))
		{
			echo '
				<hr>
				<form action="', $scripturl, '?action=army;sa=vacation" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<p>', $txt['army_vacation_end_early_desc'] ?? 'You may end your vacation early and return to active duty.', '</p>
					<input type="submit" name="end_vacation" value="', $txt['army_vacation_end_early'] ?? 'End Vacation Early', '" class="button" onclick="return confirm(\'', ($txt['army_vacation_end_confirm'] ?? 'Are you sure you want to end your vacation early?'), '\');">
				</form>';
		}

		echo '
			</div>';
	}
	else
	{
		// Not on vacation - show the start vacation form
		echo '
			<div class="windowbg">
				<p>', $txt['army_vacation_desc'] ?? 'Vacation mode protects your army from attacks while you are away. You will not be able to perform any actions during vacation.', '</p>
				<form action="', $scripturl, '?action=army;sa=vacation" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<dl class="settings">
						<dt>
							<label for="vacation_days">', $txt['army_vacation_days'] ?? 'Vacation Duration (days)', '</label>
							<br>
							<span class="smalltext">',
								sprintf(
									$txt['army_vacation_range'] ?? 'Minimum: %d days, Maximum: %d days',
									$vacation['vacation_min'],
									$vacation['vacation_max']
								), '
							</span>
						</dt>
						<dd>
							<select name="vacation_days" id="vacation_days">';

		for ($d = $vacation['vacation_min']; $d <= $vacation['vacation_max']; $d++)
		{
			echo '
								<option value="', $d, '">', $d, ' ', ($d === 1 ? ($txt['army_day'] ?? 'day') : ($txt['army_days'] ?? 'days')), '</option>';
		}

		echo '
							</select>
						</dd>
					</dl>
					<input type="submit" name="start_vacation" value="', $txt['army_vacation_start'] ?? 'Start Vacation', '" class="button" onclick="return confirm(\'', ($txt['army_vacation_start_confirm'] ?? 'Are you sure you want to start vacation? You will not be able to perform any actions during this time.'), '\');">
				</form>
			</div>';
	}

	echo '
		</div>
	</div>';
}
