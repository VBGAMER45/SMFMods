<?php
/**
 * SMF Rivals - Clan Templates
 *
 * @package SMFRivals
 * @version 1.0.0
 */

/**
 * Clan directory listing with alpha filter and pagination.
 */
function template_rivals_clan_list()
{
	global $context, $txt, $scripturl, $settings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_clans'], '</h3>
	</div>';

	// Alpha filter
	echo '
	<div class="windowbg rivals_filters">
		<div class="rivals_alpha_filter">';

	$letters = range('A', 'Z');
	echo '<a href="', $scripturl, '?action=rivals;sa=clans" class="button', empty($context['rivals_alpha_filter']) ? ' active' : '', '">All</a> ';
	foreach ($letters as $letter)
	{
		echo '<a href="', $scripturl, '?action=rivals;sa=clans;alpha=', $letter, '" class="button', $context['rivals_alpha_filter'] === $letter ? ' active' : '', '">', $letter, '</a> ';
	}

	echo '
		</div>';

	// Ladder filter
	if (!empty($context['rivals_ladder_options']))
	{
		echo '
		<form action="', $scripturl, '?action=rivals;sa=clans" method="get" class="rivals_ladder_filter">
			<input type="hidden" name="action" value="rivals" />
			<input type="hidden" name="sa" value="clans" />
			<select name="ladder" onchange="this.form.submit();">
				<option value="0">', $txt['rivals_select_ladder'], '</option>';

		foreach ($context['rivals_ladder_options'] as $id => $name)
			echo '
				<option value="', $id, '"', $context['rivals_ladder_filter'] == $id ? ' selected' : '', '>', $name, '</option>';

		echo '
			</select>
		</form>';
	}

	echo '
	</div>';

	// Create clan button
	echo '
	<div class="rivals_actions_bar">
		<a href="', $scripturl, '?action=rivals;sa=createclan" class="button">', $txt['rivals_create_clan'], '</a>
	</div>';

	if (empty($context['rivals_clans']))
	{
		echo '
	<div class="information">', $txt['rivals_no_clans'], '</div>';
		return;
	}

	echo '
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th></th>
				<th>', $txt['rivals_name'], '</th>
				<th>', $txt['rivals_clan_members'], '</th>
				<th>', $txt['rivals_wld'], '</th>
				<th>', $txt['rivals_clan_level'], '</th>
				<th>', $txt['rivals_clan_created'], '</th>
			</tr>
		</thead>
		<tbody>';

	foreach ($context['rivals_clans'] as $clan)
	{
		echo '
			<tr class="windowbg">
				<td class="rivals_clan_logo_cell">';

		if (!empty($clan['logo']))
			echo '<img src="', $settings['default_images_url'], '/rivals/clanlogo/', $clan['logo'], '" alt="" class="rivals_clan_logo_thumb" />';

		echo '</td>
				<td><a href="', $clan['href'], '"><strong>', $clan['name'], '</strong></a></td>
				<td>', $clan['member_count'], '</td>
				<td>', $clan['wins'], '-', $clan['losses'], '-', $clan['draws'], '</td>
				<td>', $clan['level'], '</td>
				<td>', timeformat($clan['created_at']), '</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>
	<div class="pagesection">', $context['page_index'], '</div>';
}

/**
 * Clan profile page with tabs.
 */
function template_rivals_clan_profile()
{
	global $context, $txt, $scripturl, $settings;

	$clan = $context['rivals_clan'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $clan['name'], '</h3>
	</div>';

	if (!empty($clan['is_closed']))
		echo '<div class="noticebox">', $txt['rivals_clan_closed'], '</div>';

	// Clan header
	echo '
	<div class="windowbg rivals_clan_header">';

	if (!empty($clan['logo']))
	{
		echo '
		<div class="rivals_clan_logo">
			<img src="', $settings['default_images_url'], '/rivals/clanlogo/', $clan['logo'], '" alt="', $clan['name'], '"',
			$clan['logo_width'] > 0 ? ' width="' . $clan['logo_width'] . '"' : '', ' />
		</div>';
	}

	echo '
		<div class="rivals_clan_info">
			<h2 class="rivals_clan_name">', $clan['name'], '</h2>
			<div class="rivals_clan_meta">';

	if (!empty($context['rivals_clan_leader']))
		echo $txt['rivals_role_leader'], ': <a href="', $scripturl, '?action=profile;u=', $context['rivals_clan_leader']['id_member'], '">', $context['rivals_clan_leader']['real_name'], '</a> | ';

	echo count($context['rivals_clan_members']), ' ', $txt['rivals_clan_members'], ' | ',
		$txt['rivals_clan_created'], ': ', timeformat($clan['created_at']);

	echo '
			</div>';

	if (!empty($clan['description']))
		echo '
			<p>', parse_bbc($clan['description']), '</p>';

	if (!empty($clan['website']))
		echo '
			<p><strong>', $txt['rivals_clan_website'], ':</strong> <a href="', $clan['website'], '" target="_blank" rel="noopener">', $clan['website'], '</a></p>';

	echo '
		</div>
	</div>';

	// Action buttons
	echo '
	<div class="rivals_actions_bar">';

	if ($context['rivals_is_leader'])
	{
		echo '
		<a href="', $scripturl, '?action=rivals;sa=manageclan" class="button">', $txt['rivals_manage_clan'], '</a>
		<a href="', $scripturl, '?action=rivals;sa=editclan" class="button">', $txt['rivals_edit_clan'], '</a>';
	}

	if ($context['rivals_can_challenge'])
		echo '
		<a href="', $scripturl, '?action=rivals;sa=challenge;clan=', $clan['id_clan'], '" class="button">', $txt['rivals_challenge'], '</a>';

	if (!$context['rivals_is_member'] && !$context['rivals_is_pending'] && empty($clan['is_closed']))
		echo '
		<a href="', $scripturl, '?action=rivals;sa=joinclan;id=', $clan['id_clan'], ';', $context['session_var'], '=', $context['session_id'], '" class="button">', $txt['rivals_join_clan'], '</a>';

	if ($context['rivals_is_pending'])
		echo '
		<span class="rivals_badge">', $txt['rivals_clan_join_pending'], '</span>';

	echo '
	</div>';

	// Tabs
	echo '
	<div class="rivals_tabs">
		<div class="rivals_tab active" data-tab="tab_info">', $txt['rivals_clan_info'], '</div>
		<div class="rivals_tab" data-tab="tab_members">', $txt['rivals_clan_members'], '</div>
		<div class="rivals_tab" data-tab="tab_stats">', $txt['rivals_clan_stats'], '</div>
		<div class="rivals_tab" data-tab="tab_history">', $txt['rivals_clan_history'], '</div>
	</div>';

	// Tab: Info
	echo '
	<div id="tab_info" class="rivals_tab_content active">
		<div class="windowbg">
			<dl class="settings">';

	if (!empty($clan['guid']))
		echo '<dt>', $txt['rivals_guid'], ':</dt><dd>', $clan['guid'], '</dd>';
	if (!empty($clan['uac']))
		echo '<dt>', $txt['rivals_uac'], ':</dt><dd>', $clan['uac'], '</dd>';
	if (!empty($clan['favorite_maps']))
		echo '<dt>', $txt['rivals_favorite_maps'], ':</dt><dd>', $clan['favorite_maps'], '</dd>';
	if (!empty($clan['favorite_teams']))
		echo '<dt>', $txt['rivals_favorite_teams'], ':</dt><dd>', $clan['favorite_teams'], '</dd>';

	echo '
				<dt>', $txt['rivals_wins'], ' / ', $txt['rivals_losses'], ' / ', $txt['rivals_draws'], ':</dt>
				<dd>', $clan['total_wins'], ' / ', $clan['total_losses'], ' / ', $clan['total_draws'], '</dd>';

	if ($clan['achievement_10streak'])
		echo '<dt>', $txt['rivals_achievement_10streak'], ':</dt><dd>', $txt['rivals_yes'], '</dd>';
	if ($clan['achievement_ladderwin'])
		echo '<dt>', $txt['rivals_achievement_ladderwin'], ':</dt><dd>', $txt['rivals_yes'], '</dd>';

	echo '
			</dl>
		</div>
	</div>';

	// Tab: Members
	echo '
	<div id="tab_members" class="rivals_tab_content">
		<table class="table_grid rivals_table">
			<thead>
				<tr class="title_bar">
					<th>', $txt['rivals_name'], '</th>
					<th>', $txt['rivals_gamer_name'], '</th>
					<th>', $txt['rivals_role_member'], '</th>
					<th>', $txt['rivals_mvp'], '</th>
					<th>', $txt['rivals_kills'], '</th>
					<th>', $txt['rivals_deaths'], '</th>
					<th>', $txt['rivals_assists'], '</th>
				</tr>
			</thead>
			<tbody>';

	foreach ($context['rivals_clan_members'] as $member)
	{
		echo '
				<tr class="windowbg">
					<td><a href="', $member['href'], '">', $member['name'], '</a></td>
					<td>', $member['gamer_name'], '</td>
					<td>', $member['role_text'], '</td>
					<td>', $member['mvp_count'], '</td>
					<td>', $member['kills'], '</td>
					<td>', $member['deaths'], '</td>
					<td>', $member['assists'], '</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>
	</div>';

	// Tab: Stats (ladder standings)
	echo '
	<div id="tab_stats" class="rivals_tab_content">';

	if (empty($context['rivals_clan_standings']))
	{
		echo '<div class="information">', $txt['rivals_no_results'], '</div>';
	}
	else
	{
		echo '
		<table class="table_grid rivals_table">
			<thead>
				<tr class="title_bar">
					<th>', $txt['rivals_ladder'], '</th>
					<th>', $txt['rivals_rank'], '</th>
					<th>', $txt['rivals_score'], '</th>
					<th>', $txt['rivals_wld'], '</th>
					<th>', $txt['rivals_streak'], '</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['rivals_clan_standings'] as $s)
		{
			echo '
				<tr class="windowbg">
					<td><a href="', $scripturl, '?action=rivals;sa=standings;ladder=', $s['id_ladder'], '">', $s['ladder_name'], '</a></td>
					<td>', $s['current_rank'] > 0 ? $s['current_rank'] : '-', '</td>
					<td>', $s['score'], '</td>
					<td>', $s['wins'], '-', $s['losses'], '-', $s['draws'], '</td>
					<td>', $s['streak'], '</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>';

	// Tab: History (recent matches)
	echo '
	<div id="tab_history" class="rivals_tab_content">';

	if (empty($context['rivals_clan_matches']))
	{
		echo '<div class="information">', $txt['rivals_no_matches'], '</div>';
	}
	else
	{
		echo '
		<table class="table_grid rivals_table">
			<thead>
				<tr class="title_bar">
					<th>', $txt['rivals_ladder'], '</th>
					<th>', $txt['rivals_challenger'], '</th>
					<th>', $txt['rivals_match_score'], '</th>
					<th>', $txt['rivals_challengee'], '</th>
					<th>', $txt['rivals_date'], '</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['rivals_clan_matches'] as $m)
		{
			$is_winner = $m['winner_id'] == $clan['id_clan'];
			echo '
				<tr class="windowbg', $is_winner ? ' rivals_match_won' : '', '">
					<td>', $m['ladder_name'], '</td>
					<td><a href="', $scripturl, '?action=rivals;sa=clan;id=', $m['challenger_id'], '">', $m['challenger_name'], '</a></td>
					<td><strong>', $m['challenger_score'], ' - ', $m['challengee_score'], '</strong></td>
					<td><a href="', $scripturl, '?action=rivals;sa=clan;id=', $m['challengee_id'], '">', $m['challengee_name'], '</a></td>
					<td>', timeformat($m['completed_at']), '</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>';
}

/**
 * Create clan form.
 */
function template_rivals_create_clan()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_create_clan'], '</h3>
	</div>';

	if (!empty($context['rivals_errors']))
	{
		echo '<div class="errorbox"><ul>';
		foreach ($context['rivals_errors'] as $error)
			echo '<li>', $error, '</li>';
		echo '</ul></div>';
	}

	echo '
	<form action="', $scripturl, '?action=rivals;sa=createclan" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data">
		<div class="windowbg">
			<dl class="settings">
				<dt><label for="clan_name">', $txt['rivals_clan_name'], ' <span class="error">*</span></label></dt>
				<dd><input type="text" name="clan_name" id="clan_name" maxlength="255" required class="input_text" /></dd>

				<dt><label for="clan_description">', $txt['rivals_clan_description'], '</label></dt>
				<dd><textarea name="clan_description" id="clan_description" rows="4" class="input_text" style="width:90%;"></textarea></dd>

				<dt><label for="clan_website">', $txt['rivals_clan_website'], '</label></dt>
				<dd><input type="url" name="clan_website" id="clan_website" maxlength="255" class="input_text" /></dd>

				<dt><label for="clan_logo">', $txt['rivals_clan_logo'], '</label></dt>
				<dd>
					<input type="file" name="clan_logo" id="clan_logo" accept="image/jpeg,image/png,image/gif" />
					<div id="rivals_logo_preview" class="rivals_logo_preview"></div>
				</dd>
			</dl>
			<input type="submit" name="create_clan" value="', $txt['rivals_create_clan'], '" class="button" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</div>
	</form>';
}

/**
 * Clan management dashboard.
 */
function template_rivals_manage_clan()
{
	global $context, $txt, $scripturl;

	$clan = $context['rivals_clan'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_manage_clan'], ' - ', $clan['name'], '</h3>
	</div>
	<div class="windowbg">
		<div class="rivals_dashboard_grid">
			<a href="', $scripturl, '?action=rivals;sa=editclan" class="rivals_dashboard_card">
				<strong>', $txt['rivals_edit_clan'], '</strong>
				<span>', $txt['rivals_description'], ', ', $txt['rivals_logo'], ', etc.</span>
			</a>
			<a href="', $scripturl, '?action=rivals;sa=members" class="rivals_dashboard_card">
				<strong>', $txt['rivals_clan_members'], '</strong>
				<span>', $context['rivals_member_count'], ' ', $txt['rivals_clan_members'], '</span>
			</a>
			<a href="', $scripturl, '?action=rivals;sa=pending" class="rivals_dashboard_card">
				<strong>', $txt['rivals_pending_members'], '</strong>
				<span>', $context['rivals_pending_count'], ' pending</span>
			</a>
			<a href="', $scripturl, '?action=rivals;sa=invite" class="rivals_dashboard_card">
				<strong>', $txt['rivals_invite_members'], '</strong>
			</a>
			<a href="', $scripturl, '?action=rivals;sa=roster" class="rivals_dashboard_card">
				<strong>', $txt['rivals_rosters'], '</strong>
			</a>
			<a href="', $scripturl, '?action=rivals;sa=challenges" class="rivals_dashboard_card">
				<strong>', $txt['rivals_challenges'], '</strong>
				<span>', $context['rivals_challenge_count'], ' incoming</span>
			</a>
			<a href="', $scripturl, '?action=rivals;sa=clanchat" class="rivals_dashboard_card">
				<strong>', $txt['rivals_clan_chat'], '</strong>
			</a>
			<a href="', $scripturl, '?action=rivals;sa=clan;id=', $clan['id_clan'], '" class="rivals_dashboard_card">
				<strong>', $txt['rivals_view'], ' ', $txt['rivals_clan'], '</strong>
			</a>
		</div>
	</div>';
}

/**
 * Edit clan form.
 */
function template_rivals_edit_clan()
{
	global $context, $txt, $scripturl, $settings;

	$clan = $context['rivals_clan'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_edit_clan'], '</h3>
	</div>';

	if (!empty($context['rivals_errors']))
	{
		echo '<div class="errorbox"><ul>';
		foreach ($context['rivals_errors'] as $error)
			echo '<li>', $error, '</li>';
		echo '</ul></div>';
	}

	if (!empty($context['rivals_saved']))
		echo '<div class="infobox">', $txt['rivals_clan_saved'], '</div>';

	echo '
	<form action="', $scripturl, '?action=rivals;sa=editclan" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data">
		<div class="windowbg">
			<dl class="settings">
				<dt><label for="clan_name">', $txt['rivals_clan_name'], '</label></dt>
				<dd><input type="text" name="clan_name" id="clan_name" value="', $clan['name'], '" maxlength="255" required class="input_text" /></dd>

				<dt><label for="clan_description">', $txt['rivals_clan_description'], '</label></dt>
				<dd><textarea name="clan_description" id="clan_description" rows="4" class="input_text" style="width:90%;">', $clan['description'], '</textarea></dd>

				<dt><label for="clan_website">', $txt['rivals_clan_website'], '</label></dt>
				<dd><input type="url" name="clan_website" id="clan_website" value="', $clan['website'], '" maxlength="255" class="input_text" /></dd>

				<dt><label for="clan_logo">', $txt['rivals_clan_logo'], '</label></dt>
				<dd>';

	if (!empty($clan['logo']))
		echo '<img src="', $settings['default_images_url'], '/rivals/clanlogo/', $clan['logo'], '" alt="" class="rivals_clan_logo_thumb" /><br />';

	echo '
					<input type="file" name="clan_logo" id="clan_logo" accept="image/jpeg,image/png,image/gif" />
				</dd>

				<dt><label for="favorite_maps">', $txt['rivals_favorite_maps'], '</label></dt>
				<dd><input type="text" name="favorite_maps" id="favorite_maps" value="', $clan['favorite_maps'], '" maxlength="255" class="input_text" /></dd>

				<dt><label for="favorite_teams">', $txt['rivals_favorite_teams'], '</label></dt>
				<dd><input type="text" name="favorite_teams" id="favorite_teams" value="', $clan['favorite_teams'], '" maxlength="255" class="input_text" /></dd>

				<dt><label for="guid">', $txt['rivals_guid'], '</label></dt>
				<dd><input type="text" name="guid" id="guid" value="', $clan['guid'], '" maxlength="8" class="input_text" style="width:100px;" /></dd>

				<dt><label for="uac">', $txt['rivals_uac'], '</label></dt>
				<dd><input type="text" name="uac" id="uac" value="', $clan['uac'], '" maxlength="6" class="input_text" style="width:80px;" /></dd>

				<dt><label for="is_closed">', $txt['rivals_clan_closed'], '</label></dt>
				<dd><input type="checkbox" name="is_closed" id="is_closed" value="1"', !empty($clan['is_closed']) ? ' checked' : '', ' /></dd>
			</dl>
			<input type="submit" name="save_clan" value="', $txt['rivals_save'], '" class="button" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</div>
	</form>';
}

/**
 * Member management table.
 */
function template_rivals_clan_members()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_clan_members'], '</h3>
	</div>
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_name'], '</th>
				<th>', $txt['rivals_gamer_name'], '</th>
				<th>', $txt['rivals_role_member'], '</th>
				<th>', $txt['rivals_mvp'], '</th>
				<th>', $txt['rivals_kills'], ' / ', $txt['rivals_deaths'], ' / ', $txt['rivals_assists'], '</th>
				<th>', $txt['rivals_actions'], '</th>
			</tr>
		</thead>
		<tbody>';

	foreach ($context['rivals_clan_members'] as $member)
	{
		echo '
			<tr class="windowbg">
				<td><a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], '</a></td>
				<td>', $member['gamer_name'], '</td>
				<td>', $member['role_text'], '</td>
				<td>', $member['mvp_count'], '</td>
				<td>', $member['kills'], ' / ', $member['deaths'], ' / ', $member['assists'], '</td>
				<td>';

		if ($member['role'] < 1)
		{
			echo '
					<form action="', $scripturl, '?action=rivals;sa=members" method="post" style="display:inline;">
						<input type="hidden" name="target_member" value="', $member['id'], '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<button type="submit" name="member_action" value="promote" class="button" title="', $txt['rivals_promote'], '">&uarr;</button>
						<button type="submit" name="member_action" value="kick" class="button" title="', $txt['rivals_kick'], '">&times;</button>
					</form>';
		}
		elseif ($member['role'] == 2)
		{
			echo '
					<form action="', $scripturl, '?action=rivals;sa=members" method="post" style="display:inline;">
						<input type="hidden" name="target_member" value="', $member['id'], '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<button type="submit" name="member_action" value="demote" class="button" title="', $txt['rivals_demote'], '">&darr;</button>
						<button type="submit" name="member_action" value="kick" class="button" title="', $txt['rivals_kick'], '">&times;</button>
					</form>';
		}

		echo '
				</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>';
}

/**
 * Pending member approvals.
 */
function template_rivals_pending_members()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_pending_members'], '</h3>
	</div>';

	if (empty($context['rivals_pending']))
	{
		echo '<div class="information">', $txt['rivals_no_results'], '</div>';
		return;
	}

	echo '
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_name'], '</th>
				<th>', $txt['rivals_gamer_name'], '</th>
				<th>Posts</th>
				<th>Registered</th>
				<th>', $txt['rivals_actions'], '</th>
			</tr>
		</thead>
		<tbody>';

	foreach ($context['rivals_pending'] as $pending)
	{
		echo '
			<tr class="windowbg">
				<td><a href="', $pending['href'], '">', $pending['name'], '</a></td>
				<td>', $pending['gamer_name'], '</td>
				<td>', $pending['posts'], '</td>
				<td>', $pending['registered'], '</td>
				<td>
					<form action="', $scripturl, '?action=rivals;sa=pending" method="post" style="display:inline;">
						<input type="hidden" name="target_member" value="', $pending['id'], '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<button type="submit" name="pending_action" value="approve" class="button">', $txt['rivals_approve'], '</button>
						<button type="submit" name="pending_action" value="deny" class="button">', $txt['rivals_deny'], '</button>
					</form>
				</td>
			</tr>';
	}

	echo '
		</tbody>
	</table>';
}

/**
 * Invite members form.
 */
function template_rivals_invite_members()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_invite_members'], '</h3>
	</div>';

	if (!empty($context['rivals_invite_sent']))
		echo '<div class="infobox">Invitation sent!</div>';

	echo '
	<div class="windowbg">
		<form action="', $scripturl, '?action=rivals;sa=invite" method="post" accept-charset="', $context['character_set'], '">
			<dl class="settings">
				<dt><label>Search Member</label></dt>
				<dd>
					<div style="position:relative;">
						<input type="text" id="invite_search" autocomplete="off" class="input_text" placeholder="Type a username..." />
						<input type="hidden" name="member_id" id="invite_search_id" value="0" />
						<div id="invite_search_results" class="rivals_search_dropdown"></div>
					</div>
				</dd>
			</dl>
			<input type="submit" name="invite_member" value="', $txt['rivals_invite'], '" class="button" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>
	<script>
		RivalsSearch.init("invite_search", "invite_search_results", "user");
	</script>';
}

/**
 * Roster management.
 */
function template_rivals_manage_roster()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_manage_roster'], '</h3>
	</div>';

	// Existing rosters
	if (!empty($context['rivals_rosters']))
	{
		echo '
	<table class="table_grid rivals_table">
		<thead>
			<tr class="title_bar">
				<th>', $txt['rivals_name'], '</th>
				<th>', $txt['rivals_clan_members'], '</th>
				<th>', $txt['rivals_actions'], '</th>
			</tr>
		</thead>
		<tbody>';

		foreach ($context['rivals_rosters'] as $roster)
		{
			echo '
			<tr class="windowbg">
				<td>', $roster['name'], '</td>
				<td>', $roster['member_count'], '</td>
				<td>
					<form action="', $scripturl, '?action=rivals;sa=roster" method="post" style="display:inline;">
						<input type="hidden" name="roster_id" value="', $roster['id'], '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<button type="submit" name="delete_roster" class="button">', $txt['rivals_delete'], '</button>
					</form>
				</td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}

	// Create roster form
	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_create_roster'], '</h3>
	</div>
	<div class="windowbg">
		<form action="', $scripturl, '?action=rivals;sa=roster" method="post" accept-charset="', $context['character_set'], '">
			<dl class="settings">
				<dt><label for="roster_name">', $txt['rivals_name'], '</label></dt>
				<dd><input type="text" name="roster_name" id="roster_name" maxlength="255" required class="input_text" /></dd>

				<dt><label>', $txt['rivals_clan_members'], '</label></dt>
				<dd>';

	foreach ($context['rivals_available_members'] as $id => $name)
		echo '<label><input type="checkbox" name="roster_members[]" value="', $id, '" /> ', $name, '</label><br />';

	echo '
				</dd>
			</dl>
			<input type="submit" name="create_roster" value="', $txt['rivals_create_roster'], '" class="button" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

/**
 * Clan internal chat.
 */
function template_rivals_clan_chat()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['rivals_clan_chat'], '</h3>
	</div>';

	// Message form
	echo '
	<div class="windowbg">
		<form action="', $scripturl, '?action=rivals;sa=clanchat" method="post" accept-charset="', $context['character_set'], '">
			<textarea name="message_body" rows="3" class="input_text" style="width:90%;" placeholder="Write a message..."></textarea>
			<br />
			<input type="submit" name="send_message" value="', $txt['rivals_submit'], '" class="button" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';

	// Messages
	if (empty($context['rivals_messages']))
	{
		echo '<div class="information">', $txt['rivals_no_results'], '</div>';
		return;
	}

	foreach ($context['rivals_messages'] as $msg)
	{
		echo '
	<div class="windowbg">
		<div class="rivals_chat_message">
			<strong><a href="', $scripturl, '?action=profile;u=', $msg['id_member'], '">', $msg['poster_name'], '</a></strong>
			<span class="rivals_chat_time">', $msg['time'], '</span>
			<p>', parse_bbc($msg['body']), '</p>
		</div>
	</div>';
	}
}
?>