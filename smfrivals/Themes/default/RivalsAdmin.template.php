<?php
/**
 * SMF Rivals - Admin Panel Templates
 *
 * @package SMFRivals
 * @version 1.0.0
 */

/**
 * Admin settings page.
 */
function template_rivals_admin_settings()
{
	global $context, $txt, $scripturl;

	$s = $context['rivals_settings'];

	echo '
	<div id="admincenter">
		<form action="', $scripturl, '?action=admin;area=rivals;sa=settings" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['rivals_admin_settings'], '</h3>
			</div>
			<div class="windowbg">
				<dl class="settings">
					<dt><label for="rivals_enabled">', $txt['rivals_setting_enabled'], '</label></dt>
					<dd><input type="checkbox" name="rivals_enabled" id="rivals_enabled" value="1"', $s['enabled'] ? ' checked' : '', ' /></dd>

					<dt><label for="rivals_min_posts">', $txt['rivals_setting_min_posts'], '</label></dt>
					<dd><input type="number" name="rivals_min_posts" id="rivals_min_posts" value="', $s['min_posts'], '" min="0" /></dd>

					<dt><label for="rivals_banned_group">', $txt['rivals_setting_banned_group'], '</label></dt>
					<dd><input type="number" name="rivals_banned_group" id="rivals_banned_group" value="', $s['banned_group'], '" min="0" /></dd>

					<dt><label for="rivals_frost_cost">', $txt['rivals_setting_frost_cost'], '</label></dt>
					<dd><input type="number" name="rivals_frost_cost" id="rivals_frost_cost" value="', $s['frost_cost'], '" min="0" max="100" /></dd>

					<dt><label for="rivals_inactivity_penalty">', $txt['rivals_setting_inactivity_penalty'], '</label></dt>
					<dd><input type="checkbox" name="rivals_inactivity_penalty" id="rivals_inactivity_penalty" value="1"', $s['inactivity_penalty'] ? ' checked' : '', ' /></dd>

					<dt><label for="rivals_max_report_hours">', $txt['rivals_setting_max_report_hours'], '</label></dt>
					<dd><input type="number" name="rivals_max_report_hours" id="rivals_max_report_hours" value="', $s['max_report_hours'], '" min="1" /></dd>

					<dt><label for="rivals_kickout_days">', $txt['rivals_setting_kickout_days'], '</label></dt>
					<dd><input type="number" name="rivals_kickout_days" id="rivals_kickout_days" value="', $s['kickout_days'], '" min="0" /></dd>

					<dt><label for="rivals_mod_override">', $txt['rivals_setting_mod_override'], '</label></dt>
					<dd><input type="checkbox" name="rivals_mod_override" id="rivals_mod_override" value="1"', $s['mod_override'] ? ' checked' : '', ' /></dd>

					<dt><label for="rivals_logo_max_size">', $txt['rivals_setting_logo_max_size'], '</label></dt>
					<dd><input type="number" name="rivals_logo_max_size" id="rivals_logo_max_size" value="', $s['logo_max_size'], '" min="0" /></dd>

					<dt><label for="rivals_logo_max_width">', $txt['rivals_setting_logo_max_width'], '</label></dt>
					<dd><input type="number" name="rivals_logo_max_width" id="rivals_logo_max_width" value="', $s['logo_max_width'], '" min="0" /></dd>

					<dt><label for="rivals_logo_max_height">', $txt['rivals_setting_logo_max_height'], '</label></dt>
					<dd><input type="number" name="rivals_logo_max_height" id="rivals_logo_max_height" value="', $s['logo_max_height'], '" min="0" /></dd>
				</dl>
				<input type="submit" name="save" value="', $txt['rivals_save'], '" class="button" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</div>
		</form>
	</div>';
}

/**
 * Shared: error list display.
 */
function _template_rivals_admin_errors()
{
	global $context;

	if (!empty($context['rivals_errors']))
	{
		echo '
		<div class="errorbox"><ul>';
		foreach ($context['rivals_errors'] as $err)
			echo '<li>', $err, '</li>';
		echo '</ul></div>';
	}
}

// ============================================================
// Platforms Admin
// ============================================================
function template_rivals_admin_platforms()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">';

	_template_rivals_admin_errors();

	// Add/Edit form
	$editing = $context['rivals_editing'];

	echo '
		<form action="', $scripturl, '?action=admin;area=rivals;sa=platforms', !empty($editing) ? ';edit=' . $editing['id_platform'] : '', '" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data">
			<div class="cat_bar">
				<h3 class="catbg">', !empty($editing) ? $txt['rivals_edit'] . ': ' . $editing['name'] : $txt['rivals_admin_add_platform'], '</h3>
			</div>
			<div class="windowbg">
				<dl class="settings">
					<dt><label>', $txt['rivals_name'], '</label></dt>
					<dd><input type="text" name="platform_name" value="', !empty($editing) ? $editing['name'] : '', '" size="40" required /></dd>
					<dt><label>', $txt['rivals_logo'], '</label></dt>
					<dd><input type="file" name="platform_logo" accept=".jpg,.jpeg,.png,.gif" /></dd>
				</dl>';

	if (!empty($editing))
		echo '
				<input type="hidden" name="id_platform" value="', $editing['id_platform'], '" />';

	echo '
				<input type="submit" name="save_platform" value="', $txt['rivals_save'], '" class="button" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</div>
		</form>';

	// Platform list
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['rivals_admin_platforms'], '</h3>
		</div>';

	if (empty($context['rivals_platforms']))
	{
		echo '<div class="information">', $txt['rivals_no_results'], '</div>';
	}
	else
	{
		echo '
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>', $txt['rivals_name'], '</th>
					<th>', $txt['rivals_logo'], '</th>
					<th>', $txt['rivals_actions'], '</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['rivals_platforms'] as $p)
		{
			echo '
				<tr class="windowbg">
					<td>', $p['name'], '</td>
					<td>', !empty($p['logo']) ? $p['logo'] : '-', '</td>
					<td>
						<a href="', $scripturl, '?action=admin;area=rivals;sa=platforms;edit=', $p['id_platform'], '">', $txt['rivals_edit'], '</a> |
						<a href="', $scripturl, '?action=admin;area=rivals;sa=platforms;delete=', $p['id_platform'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_delete'], '</a>
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>';
}

// ============================================================
// Ladders Admin
// ============================================================
function template_rivals_admin_ladders()
{
	global $context, $txt, $scripturl;

	$ranking_labels = array(0 => $txt['rivals_ranking_elo'], 1 => $txt['rivals_ranking_swap'], 2 => $txt['rivals_ranking_rth']);
	$style_labels = array(0 => $txt['rivals_style_standard'], 1 => $txt['rivals_style_decerto'], 2 => $txt['rivals_style_cpc'], 3 => $txt['rivals_style_football']);

	echo '
	<div id="admincenter">';

	_template_rivals_admin_errors();

	$editing = $context['rivals_editing'];

	echo '
		<form action="', $scripturl, '?action=admin;area=rivals;sa=ladders', !empty($editing) ? ';edit=' . $editing['id_ladder'] : '', '" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data">
			<div class="cat_bar">
				<h3 class="catbg">', !empty($editing) ? $txt['rivals_edit'] . ': ' . $editing['name'] : $txt['rivals_admin_add_ladder'], '</h3>
			</div>
			<div class="windowbg">
				<dl class="settings">
					<dt><label>', $txt['rivals_name'], '</label></dt>
					<dd><input type="text" name="ladder_name" value="', !empty($editing) ? $editing['name'] : '', '" size="40" required /></dd>

					<dt><label>', $txt['rivals_admin_short_name'], '</label></dt>
					<dd><input type="text" name="short_name" value="', !empty($editing) ? $editing['short_name'] : '', '" size="20" /></dd>

					<dt><label>', $txt['rivals_description'], '</label></dt>
					<dd><textarea name="description" rows="3" cols="50">', !empty($editing) ? $editing['description'] : '', '</textarea></dd>

					<dt><label>', $txt['rivals_platforms'], '</label></dt>
					<dd><select name="id_platform">
						<option value="0">-</option>';

	foreach ($context['rivals_platforms'] as $pid => $pname)
		echo '<option value="', $pid, '"', (!empty($editing) && $editing['id_platform'] == $pid ? ' selected' : ''), '>', $pname, '</option>';

	echo '</select></dd>

					<dt><label>', $txt['rivals_admin_parent_ladder'], '</label></dt>
					<dd><select name="id_parent">
						<option value="0">', $txt['rivals_admin_top_level'], '</option>';

	foreach ($context['rivals_ladders'] as $l)
	{
		if ($l['id_parent'] == 0)
			echo '<option value="', $l['id_ladder'], '"', (!empty($editing) && $editing['id_parent'] == $l['id_ladder'] ? ' selected' : ''), '>', $l['name'], '</option>';
	}

	echo '</select></dd>

					<dt><label>', $txt['rivals_admin_ranking_system'], '</label></dt>
					<dd><select name="ranking_system">';
	foreach ($ranking_labels as $k => $v)
		echo '<option value="', $k, '"', (!empty($editing) && $editing['ranking_system'] == $k ? ' selected' : ''), '>', $v, '</option>';
	echo '</select></dd>

					<dt><label>', $txt['rivals_admin_ladder_style'], '</label></dt>
					<dd><select name="ladder_style">';
	foreach ($style_labels as $k => $v)
		echo '<option value="', $k, '"', (!empty($editing) && $editing['ladder_style'] == $k ? ' selected' : ''), '>', $v, '</option>';
	echo '</select></dd>

					<dt><label>', $txt['rivals_admin_win_system'], '</label></dt>
					<dd><select name="win_system">
						<option value="0"', (!empty($editing) && $editing['win_system'] == 0 ? ' selected' : ''), '>', $txt['rivals_winsystem_score'], '</option>
						<option value="1"', (!empty($editing) && $editing['win_system'] == 1 ? ' selected' : ''), '>', $txt['rivals_winsystem_wins'], '</option>
					</select></dd>

					<dt><label>', $txt['rivals_ladder_1v1'], '</label></dt>
					<dd><input type="checkbox" name="is_1v1" value="1"', (!empty($editing) && $editing['is_1v1'] ? ' checked' : ''), ' /></dd>

					<dt><label>', $txt['rivals_ladder_locked'], '</label></dt>
					<dd><input type="checkbox" name="is_locked" value="1"', (!empty($editing) && $editing['is_locked'] ? ' checked' : ''), ' /></dd>

					<dt><label>', $txt['rivals_admin_enable_mvp'], '</label></dt>
					<dd><input type="checkbox" name="enable_mvp" value="1"', (!empty($editing) && $editing['enable_mvp'] ? ' checked' : ''), ' /></dd>

					<dt><label>', $txt['rivals_admin_enable_advstats'], '</label></dt>
					<dd><input type="checkbox" name="enable_advstats" value="1"', (!empty($editing) && $editing['enable_advstats'] ? ' checked' : ''), ' /></dd>

					<dt><label>', $txt['rivals_admin_limit_level'], '</label></dt>
					<dd><input type="number" name="limit_level" value="', !empty($editing) ? $editing['limit_level'] : 0, '" min="0" /></dd>

					<dt><label>', $txt['rivals_admin_moderator'], '</label></dt>
					<dd><input type="number" name="id_moderator" value="', !empty($editing) ? $editing['id_moderator'] : 0, '" min="0" /> <span class="smalltext">', $txt['rivals_admin_moderator_desc'], '</span></dd>

					<dt><label>', $txt['rivals_logo'], '</label></dt>
					<dd><input type="file" name="ladder_logo" accept=".jpg,.jpeg,.png,.gif" /></dd>
				</dl>';

	if (!empty($editing))
		echo '<input type="hidden" name="id_ladder" value="', $editing['id_ladder'], '" />';

	echo '
				<input type="submit" name="save_ladder" value="', $txt['rivals_save'], '" class="button" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</div>
		</form>';

	// Ladder list
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['rivals_admin_ladders'], '</h3>
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>', $txt['rivals_name'], '</th>
					<th>', $txt['rivals_platforms'], '</th>
					<th>', $txt['rivals_admin_ranking_system'], '</th>
					<th>', $txt['rivals_admin_ladder_style'], '</th>
					<th>', $txt['rivals_status'], '</th>
					<th>', $txt['rivals_actions'], '</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($context['rivals_ladders']))
	{
		echo '<tr class="windowbg"><td colspan="6">', $txt['rivals_no_results'], '</td></tr>';
	}
	else
	{
		foreach ($context['rivals_ladders'] as $l)
		{
			$indent = $l['id_parent'] > 0 ? '&nbsp;&nbsp;&nbsp;&#8627; ' : '';

			echo '
				<tr class="windowbg">
					<td>', $indent, '<strong>', $l['name'], '</strong>', !empty($l['short_name']) ? ' (' . $l['short_name'] . ')' : '', '</td>
					<td>', !empty($l['platform_name']) ? $l['platform_name'] : '-', '</td>
					<td>', isset($ranking_labels[$l['ranking_system']]) ? $ranking_labels[$l['ranking_system']] : '?', '</td>
					<td>', isset($style_labels[$l['ladder_style']]) ? $style_labels[$l['ladder_style']] : '?', '</td>
					<td>',
						$l['is_locked'] ? '<span class="rivals_badge">' . $txt['rivals_ladder_locked'] . '</span> ' : '',
						$l['is_1v1'] ? '<span class="rivals_badge rivals_badge_1v1">' . $txt['rivals_ladder_1v1'] . '</span>' : '',
					'</td>
					<td>
						<a href="', $scripturl, '?action=admin;area=rivals;sa=ladders;edit=', $l['id_ladder'], '">', $txt['rivals_edit'], '</a> |
						<a href="', $scripturl, '?action=admin;area=rivals;sa=ladders;move=up;lid=', $l['id_ladder'], ';', $context['session_var'], '=', $context['session_id'], '">&#9650;</a>
						<a href="', $scripturl, '?action=admin;area=rivals;sa=ladders;move=down;lid=', $l['id_ladder'], ';', $context['session_var'], '=', $context['session_id'], '">&#9660;</a> |
						<a href="', $scripturl, '?action=admin;area=rivals;sa=ladders;delete=', $l['id_ladder'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_delete'], '</a>
					</td>
				</tr>';
		}
	}

	echo '
			</tbody>
		</table>
	</div>';
}

// ============================================================
// Clans Admin
// ============================================================
function template_rivals_admin_clans()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['rivals_admin_clans'], '</h3>
		</div>';

	if (empty($context['rivals_clans']))
	{
		echo '<div class="information">', $txt['rivals_no_clans'], '</div>';
	}
	else
	{
		echo '
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>', $txt['rivals_clan_name'], '</th>
					<th>', $txt['rivals_clan_members'], '</th>
					<th>', $txt['rivals_wld'], '</th>
					<th>', $txt['rivals_status'], '</th>
					<th>', $txt['rivals_actions'], '</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['rivals_clans'] as $c)
		{
			echo '
				<tr class="windowbg">
					<td><a href="', $scripturl, '?action=rivals;sa=clan;id=', $c['id_clan'], '">', $c['name'], '</a></td>
					<td>', $c['member_count'], '</td>
					<td>', $c['total_wins'], '-', $c['total_losses'], '-', $c['total_draws'], '</td>
					<td>', $c['is_closed'] ? '<span class="rivals_badge">' . $txt['rivals_clan_closed'] . '</span>' : $txt['rivals_admin_open'], '</td>
					<td>
						<a href="', $scripturl, '?action=admin;area=rivals;sa=clans;toggle_close=', $c['id_clan'], ';', $context['session_var'], '=', $context['session_id'], '">', $c['is_closed'] ? $txt['rivals_admin_reopen'] : $txt['rivals_admin_close'], '</a> |
						<a href="', $scripturl, '?action=admin;area=rivals;sa=clans;delete=', $c['id_clan'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_delete'], '</a>
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}

	if (!empty($context['page_index']))
		echo '
		<div class="pagesection"><div class="pagelinks">', $context['page_index'], '</div></div>';

	echo '
	</div>';
}

// ============================================================
// Tournaments Admin
// ============================================================
function template_rivals_admin_tournaments()
{
	global $context, $txt, $scripturl;

	$status_labels = array(
		0 => $txt['rivals_tournament_draft'],
		1 => $txt['rivals_tournament_signups'],
		2 => $txt['rivals_tournament_active'],
		3 => $txt['rivals_tournament_archived'],
	);

	$type_labels = array(
		0 => $txt['rivals_single_elimination'],
		1 => $txt['rivals_double_elimination'],
		2 => $txt['rivals_round_robin'],
		3 => $txt['rivals_league'],
	);

	echo '
	<div id="admincenter">';

	_template_rivals_admin_errors();

	$editing = $context['rivals_editing'];

	// Add/Edit form
	echo '
		<form action="', $scripturl, '?action=admin;area=rivals;sa=tournaments', !empty($editing) ? ';edit=' . $editing['id_tournament'] : '', '" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data">
			<div class="cat_bar">
				<h3 class="catbg">', !empty($editing) ? $txt['rivals_edit'] . ': ' . $editing['name'] : $txt['rivals_admin_add_tournament'], '</h3>
			</div>
			<div class="windowbg">
				<dl class="settings">
					<dt><label>', $txt['rivals_name'], '</label></dt>
					<dd><input type="text" name="tournament_name" value="', !empty($editing) ? $editing['name'] : '', '" size="40" required /></dd>

					<dt><label>', $txt['rivals_admin_short_name'], '</label></dt>
					<dd><input type="text" name="short_name" value="', !empty($editing) ? $editing['short_name'] : '', '" size="20" /></dd>

					<dt><label>', $txt['rivals_details'], '</label></dt>
					<dd><textarea name="info" rows="4" cols="50">', !empty($editing) ? $editing['info'] : '', '</textarea></dd>

					<dt><label>', $txt['rivals_tournament_bracket_size'], '</label></dt>
					<dd><select name="bracket_size">';

	foreach (array(4, 8, 16, 32, 64, 128) as $bs)
		echo '<option value="', $bs, '"', (!empty($editing) && $editing['bracket_size'] == $bs ? ' selected' : ''), '>', $bs, '</option>';

	echo '</select></dd>

					<dt><label>', $txt['rivals_admin_tournament_type'], '</label></dt>
					<dd><select name="tournament_type">';
	foreach ($type_labels as $k => $v)
		echo '<option value="', $k, '"', (!empty($editing) && $editing['tournament_type'] == $k ? ' selected' : ''), '>', $v, '</option>';
	echo '</select></dd>

					<dt><label>', $txt['rivals_admin_signup_type'], '</label></dt>
					<dd><select name="signup_type">
						<option value="0"', (!empty($editing) && $editing['signup_type'] == 0 ? ' selected' : ''), '>', $txt['rivals_admin_signup_open'], '</option>
						<option value="1"', (!empty($editing) && $editing['signup_type'] == 1 ? ' selected' : ''), '>', $txt['rivals_admin_signup_invite'], '</option>
					</select></dd>

					<dt><label>', $txt['rivals_status'], '</label></dt>
					<dd><select name="status">';
	foreach ($status_labels as $k => $v)
		echo '<option value="', $k, '"', (!empty($editing) && $editing['status'] == $k ? ' selected' : ''), '>', $v, '</option>';
	echo '</select></dd>

					<dt><label>', $txt['rivals_ladder_1v1'], '</label></dt>
					<dd><input type="checkbox" name="is_user_based" value="1"', (!empty($editing) && $editing['is_user_based'] ? ' checked' : ''), ' /></dd>

					<dt><label>', $txt['rivals_admin_enable_advstats'], '</label></dt>
					<dd><input type="checkbox" name="enable_advstats" value="1"', (!empty($editing) && $editing['enable_advstats'] ? ' checked' : ''), ' /></dd>

					<dt><label>', $txt['rivals_style_decerto'], '</label></dt>
					<dd><input type="checkbox" name="enable_decerto" value="1"', (!empty($editing) && $editing['enable_decerto'] ? ' checked' : ''), ' /></dd>

					<dt><label>', $txt['rivals_admin_restricted'], '</label></dt>
					<dd><input type="checkbox" name="is_restricted" value="1"', (!empty($editing) && $editing['is_restricted'] ? ' checked' : ''), ' /></dd>

					<dt><label>', $txt['rivals_admin_min_members'], '</label></dt>
					<dd><input type="number" name="min_members" value="', !empty($editing) ? $editing['min_members'] : 0, '" min="0" /></dd>

					<dt><label>', $txt['rivals_admin_max_members'], '</label></dt>
					<dd><input type="number" name="max_members" value="', !empty($editing) ? $editing['max_members'] : 0, '" min="0" /></dd>

					<dt><label>', $txt['rivals_logo'], '</label></dt>
					<dd><input type="file" name="tournament_logo" accept=".jpg,.jpeg,.png,.gif" /></dd>
				</dl>';

	if (!empty($editing))
		echo '<input type="hidden" name="id_tournament" value="', $editing['id_tournament'], '" />';

	echo '
				<input type="submit" name="save_tournament" value="', $txt['rivals_save'], '" class="button" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</div>
		</form>';

	// Tournament list
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['rivals_admin_tournaments'], '</h3>
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>', $txt['rivals_name'], '</th>
					<th>', $txt['rivals_admin_tournament_type'], '</th>
					<th>', $txt['rivals_tournament_bracket_size'], '</th>
					<th>', $txt['rivals_tournament_entries'], '</th>
					<th>', $txt['rivals_status'], '</th>
					<th>', $txt['rivals_actions'], '</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($context['rivals_tournaments']))
	{
		echo '<tr class="windowbg"><td colspan="6">', $txt['rivals_no_tournaments'], '</td></tr>';
	}
	else
	{
		foreach ($context['rivals_tournaments'] as $t)
		{
			echo '
				<tr class="windowbg">
					<td><strong>', $t['name'], '</strong></td>
					<td>', isset($type_labels[$t['tournament_type']]) ? $type_labels[$t['tournament_type']] : '?', '</td>
					<td>', $t['bracket_size'], '</td>
					<td>', $t['entry_count'], '</td>
					<td><span class="rivals_badge rivals_status_', $t['status'], '">', isset($status_labels[$t['status']]) ? $status_labels[$t['status']] : '?', '</span></td>
					<td>
						<a href="', $scripturl, '?action=admin;area=rivals;sa=tournaments;edit=', $t['id_tournament'], '">', $txt['rivals_edit'], '</a> |
						<a href="', $scripturl, '?action=rivals;sa=brackets;tournament=', $t['id_tournament'], '">', $txt['rivals_tournament_brackets'], '</a> |
						<a href="', $scripturl, '?action=admin;area=rivals;sa=tournaments;delete=', $t['id_tournament'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_delete'], '</a>
					</td>
				</tr>';
		}
	}

	echo '
			</tbody>
		</table>
	</div>';
}

// ============================================================
// Seasons Admin
// ============================================================
function template_rivals_admin_seasons()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">';

	_template_rivals_admin_errors();

	if (empty($context['rivals_ladders_seasons']))
	{
		echo '<div class="information">', $txt['rivals_no_results'], '</div>';
		echo '</div>';
		return;
	}

	foreach ($context['rivals_ladders_seasons'] as $ladder)
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $ladder['name'], '</h3>
		</div>
		<div class="windowbg">';

		if (empty($ladder['seasons']))
			echo '<p>', $txt['rivals_no_seasons'], '</p>';
		else
		{
			echo '
			<table class="table_grid" style="margin-bottom:8px;">
				<thead><tr class="title_bar">
					<th>', $txt['rivals_season'], '</th>
					<th>', $txt['rivals_status'], '</th>
					<th>', $txt['rivals_actions'], '</th>
				</tr></thead><tbody>';

			foreach ($ladder['seasons'] as $season)
			{
				echo '
					<tr class="windowbg">
						<td>', $season['name'], '</td>
						<td>', $season['status'] == 1 ? '<span class="rivals_badge rivals_status_2">' . $txt['rivals_season_active'] . '</span>' : $txt['rivals_season_ended'], '</td>
						<td>';

				if ($season['status'] == 1)
					echo '<a href="', $scripturl, '?action=admin;area=rivals;sa=seasons;end_season=', $season['id'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_admin_end_season'], '</a>';
				else
					echo '<a href="', $scripturl, '?action=rivals;sa=league;ladder=', $ladder['id'], ';season=', $season['id'], '">', $txt['rivals_view'], '</a>';

				echo '</td>
					</tr>';
			}

			echo '</tbody></table>';
		}

		// Start new season form (only if no active season)
		if (empty($ladder['active_season']))
		{
			echo '
			<form action="', $scripturl, '?action=admin;area=rivals;sa=seasons" method="post" accept-charset="', $context['character_set'], '" style="display:inline-flex;gap:8px;align-items:center;">
				<input type="text" name="season_name" placeholder="', $txt['rivals_admin_new_season_name'], '" size="20" required />
				<input type="hidden" name="id_ladder" value="', $ladder['id'], '" />
				<input type="submit" name="start_season" value="', $txt['rivals_admin_start_season'], '" class="button" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</form>';
		}

		echo '
		</div>';
	}

	echo '
	</div>';
}

// ============================================================
// MVP Definitions Admin
// ============================================================
function template_rivals_admin_mvp()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">';

	_template_rivals_admin_errors();

	$editing = $context['rivals_editing'];

	echo '
		<form action="', $scripturl, '?action=admin;area=rivals;sa=mvp', !empty($editing) ? ';edit=' . $editing['id_mvp'] : '', '" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">', !empty($editing) ? $txt['rivals_edit'] : $txt['rivals_admin_add_mvp'], '</h3>
			</div>
			<div class="windowbg">
				<dl class="settings">
					<dt><label>', $txt['rivals_name'], '</label></dt>
					<dd><input type="text" name="mvp_name" value="', !empty($editing) ? $editing['name'] : '', '" size="40" required /></dd>

					<dt><label>', $txt['rivals_description'], '</label></dt>
					<dd><textarea name="mvp_description" rows="3" cols="50">', !empty($editing) ? $editing['description'] : '', '</textarea></dd>

					<dt><label>', $txt['rivals_platforms'], '</label></dt>
					<dd><select name="id_platform">
						<option value="0">-</option>';
	foreach ($context['rivals_platforms'] as $pid => $pname)
		echo '<option value="', $pid, '"', (!empty($editing) && $editing['id_platform'] == $pid ? ' selected' : ''), '>', $pname, '</option>';
	echo '</select></dd>

					<dt><label>', $txt['rivals_ladder'], '</label></dt>
					<dd><select name="id_ladder">
						<option value="0">', $txt['rivals_all'], '</option>';
	foreach ($context['rivals_ladders_list'] as $lid => $lname)
		echo '<option value="', $lid, '"', (!empty($editing) && $editing['id_ladder'] == $lid ? ' selected' : ''), '>', $lname, '</option>';
	echo '</select></dd>
				</dl>';

	if (!empty($editing))
		echo '<input type="hidden" name="id_mvp" value="', $editing['id_mvp'], '" />';

	echo '
				<input type="submit" name="save_mvp" value="', $txt['rivals_save'], '" class="button" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</div>
		</form>';

	// List
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['rivals_admin_mvp'], '</h3>
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>', $txt['rivals_name'], '</th>
					<th>', $txt['rivals_description'], '</th>
					<th>', $txt['rivals_platforms'], '</th>
					<th>', $txt['rivals_ladder'], '</th>
					<th>', $txt['rivals_actions'], '</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($context['rivals_mvp_list']))
		echo '<tr class="windowbg"><td colspan="5">', $txt['rivals_no_results'], '</td></tr>';
	else
	{
		foreach ($context['rivals_mvp_list'] as $m)
		{
			echo '
				<tr class="windowbg">
					<td>', $m['name'], '</td>
					<td>', !empty($m['description']) ? $m['description'] : '-', '</td>
					<td>', !empty($m['platform_name']) ? $m['platform_name'] : '-', '</td>
					<td>', !empty($m['ladder_name']) ? $m['ladder_name'] : $txt['rivals_all'], '</td>
					<td>
						<a href="', $scripturl, '?action=admin;area=rivals;sa=mvp;edit=', $m['id_mvp'], '">', $txt['rivals_edit'], '</a> |
						<a href="', $scripturl, '?action=admin;area=rivals;sa=mvp;delete=', $m['id_mvp'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_delete'], '</a>
					</td>
				</tr>';
		}
	}

	echo '
			</tbody>
		</table>
	</div>';
}

// ============================================================
// Game Modes Admin
// ============================================================
function template_rivals_admin_gamemodes()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">';

	_template_rivals_admin_errors();

	$editing = $context['rivals_editing'];

	echo '
		<form action="', $scripturl, '?action=admin;area=rivals;sa=gamemodes', !empty($editing) ? ';edit=' . $editing['id_mode'] : '', '" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">', !empty($editing) ? $txt['rivals_edit'] : $txt['rivals_admin_add_gamemode'], '</h3>
			</div>
			<div class="windowbg">
				<dl class="settings">
					<dt><label>', $txt['rivals_name'], '</label></dt>
					<dd><input type="text" name="game_name" value="', !empty($editing) ? $editing['game_name'] : '', '" size="40" required /></dd>

					<dt><label>', $txt['rivals_admin_short_name'], '</label></dt>
					<dd><input type="text" name="short_name" value="', !empty($editing) ? $editing['short_name'] : '', '" size="20" /></dd>

					<dt><label>', $txt['rivals_admin_mode_type'], '</label></dt>
					<dd><select name="mode_type">
						<option value="0"', (!empty($editing) && $editing['mode_type'] == 0 ? ' selected' : ''), '>', $txt['rivals_admin_type_gamemode'], '</option>
						<option value="1"', (!empty($editing) && $editing['mode_type'] == 1 ? ' selected' : ''), '>', $txt['rivals_admin_type_map'], '</option>
					</select></dd>

					<dt><label>', $txt['rivals_admin_parent_mode'], '</label></dt>
					<dd><select name="parent_id">
						<option value="0">-</option>';
	foreach ($context['rivals_parent_modes'] as $pid => $pname)
		echo '<option value="', $pid, '"', (!empty($editing) && $editing['parent_id'] == $pid ? ' selected' : ''), '>', $pname, '</option>';
	echo '</select></dd>

					<dt><label>CPC</label></dt>
					<dd><input type="checkbox" name="is_cpc" value="1"', (!empty($editing) && $editing['is_cpc'] ? ' checked' : ''), ' /></dd>
				</dl>';

	if (!empty($editing))
		echo '<input type="hidden" name="id_mode" value="', $editing['id_mode'], '" />';

	echo '
				<input type="submit" name="save_gamemode" value="', $txt['rivals_save'], '" class="button" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</div>
		</form>';

	// List
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['rivals_admin_gamemodes'], '</h3>
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>', $txt['rivals_name'], '</th>
					<th>', $txt['rivals_admin_short_name'], '</th>
					<th>', $txt['rivals_admin_mode_type'], '</th>
					<th>', $txt['rivals_admin_parent_mode'], '</th>
					<th>', $txt['rivals_status'], '</th>
					<th>', $txt['rivals_actions'], '</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($context['rivals_game_modes']))
		echo '<tr class="windowbg"><td colspan="6">', $txt['rivals_no_results'], '</td></tr>';
	else
	{
		foreach ($context['rivals_game_modes'] as $gm)
		{
			echo '
				<tr class="windowbg">
					<td>', $gm['game_name'], '</td>
					<td>', $gm['short_name'], '</td>
					<td>', $gm['mode_type'] == 0 ? $txt['rivals_admin_type_gamemode'] : $txt['rivals_admin_type_map'], '</td>
					<td>', !empty($gm['parent_name']) ? $gm['parent_name'] : '-', '</td>
					<td>', $gm['is_active'] ? '<span style="color:green;">&#10003;</span>' : '<span style="color:red;">&#10007;</span>', '</td>
					<td>
						<a href="', $scripturl, '?action=admin;area=rivals;sa=gamemodes;edit=', $gm['id_mode'], '">', $txt['rivals_edit'], '</a> |
						<a href="', $scripturl, '?action=admin;area=rivals;sa=gamemodes;toggle=', $gm['id_mode'], ';', $context['session_var'], '=', $context['session_id'], '">', $gm['is_active'] ? $txt['rivals_admin_deactivate'] : $txt['rivals_admin_activate'], '</a> |
						<a href="', $scripturl, '?action=admin;area=rivals;sa=gamemodes;delete=', $gm['id_mode'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_delete'], '</a>
					</td>
				</tr>';
		}
	}

	echo '
			</tbody>
		</table>
	</div>';
}

// ============================================================
// Random Map Admin
// ============================================================
function template_rivals_admin_random()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">';

	_template_rivals_admin_errors();

	$editing = $context['rivals_editing'];

	echo '
		<form action="', $scripturl, '?action=admin;area=rivals;sa=random', !empty($editing) ? ';edit=' . $editing['id_random'] : '', '" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data">
			<div class="cat_bar">
				<h3 class="catbg">', !empty($editing) ? $txt['rivals_edit'] : $txt['rivals_admin_add_random'], '</h3>
			</div>
			<div class="windowbg">
				<dl class="settings">
					<dt><label>', $txt['rivals_name'], '</label></dt>
					<dd><input type="text" name="game_name" value="', !empty($editing) ? $editing['game_name'] : '', '" size="40" required /></dd>

					<dt><label>', $txt['rivals_admin_short_name'], '</label></dt>
					<dd><input type="text" name="short_name" value="', !empty($editing) ? $editing['short_name'] : '', '" size="20" /></dd>

					<dt><label>', $txt['rivals_admin_image'], '</label></dt>
					<dd><input type="file" name="random_image" accept=".jpg,.jpeg,.png,.gif" /></dd>
				</dl>';

	if (!empty($editing))
		echo '<input type="hidden" name="id_random" value="', $editing['id_random'], '" />';

	echo '
				<input type="submit" name="save_random" value="', $txt['rivals_save'], '" class="button" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			</div>
		</form>';

	// List
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['rivals_admin_random'], '</h3>
		</div>
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>', $txt['rivals_name'], '</th>
					<th>', $txt['rivals_admin_short_name'], '</th>
					<th>', $txt['rivals_admin_image'], '</th>
					<th>', $txt['rivals_actions'], '</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($context['rivals_random_maps']))
		echo '<tr class="windowbg"><td colspan="4">', $txt['rivals_no_results'], '</td></tr>';
	else
	{
		foreach ($context['rivals_random_maps'] as $rm)
		{
			echo '
				<tr class="windowbg">
					<td>', $rm['game_name'], '</td>
					<td>', $rm['short_name'], '</td>
					<td>', !empty($rm['image']) ? $rm['image'] : '-', '</td>
					<td>
						<a href="', $scripturl, '?action=admin;area=rivals;sa=random;edit=', $rm['id_random'], '">', $txt['rivals_edit'], '</a> |
						<a href="', $scripturl, '?action=admin;area=rivals;sa=random;delete=', $rm['id_random'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_delete'], '</a>
					</td>
				</tr>';
		}
	}

	echo '
			</tbody>
		</table>
	</div>';
}

// ============================================================
// Matches Admin (Moderation)
// ============================================================
function template_rivals_admin_matches()
{
	global $context, $txt, $scripturl;

	$status_labels = array(
		0 => $txt['rivals_match_pending'],
		1 => $txt['rivals_match_completed'],
		2 => $txt['rivals_match_disputed_status'],
	);

	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['rivals_admin_matches'], '</h3>
		</div>';

	// Filter
	echo '
		<div class="rivals_filter_bar">
			<a href="', $scripturl, '?action=admin;area=rivals;sa=matches" class="button', $context['rivals_match_filter'] == -1 ? ' active' : '', '">', $txt['rivals_all'], '</a>
			<a href="', $scripturl, '?action=admin;area=rivals;sa=matches;status=0" class="button', $context['rivals_match_filter'] == 0 ? ' active' : '', '">', $txt['rivals_match_pending'], '</a>
			<a href="', $scripturl, '?action=admin;area=rivals;sa=matches;status=1" class="button', $context['rivals_match_filter'] == 1 ? ' active' : '', '">', $txt['rivals_match_completed'], '</a>
			<a href="', $scripturl, '?action=admin;area=rivals;sa=matches;status=2" class="button', $context['rivals_match_filter'] == 2 ? ' active' : '', '"><strong>', $txt['rivals_match_disputed_status'], '</strong></a>
		</div>';

	if (empty($context['rivals_matches']))
	{
		echo '<div class="information">', $txt['rivals_no_matches'], '</div>';
	}
	else
	{
		echo '
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>ID</th>
					<th>', $txt['rivals_ladder'], '</th>
					<th>', $txt['rivals_challenger'], '</th>
					<th>', $txt['rivals_challengee'], '</th>
					<th>', $txt['rivals_match_score'], '</th>
					<th>', $txt['rivals_status'], '</th>
					<th>', $txt['rivals_date'], '</th>
					<th>', $txt['rivals_actions'], '</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($context['rivals_matches'] as $m)
		{
			echo '
				<tr class="windowbg">
					<td>', $m['id_match'], '</td>
					<td>', !empty($m['ladder_name']) ? $m['ladder_name'] : '-', '</td>
					<td>', $m['challenger_name'], '</td>
					<td>', $m['challengee_name'], '</td>
					<td>', $m['challenger_score'], ' - ', $m['challengee_score'], '</td>
					<td><span class="rivals_badge rivals_status_', $m['status'] == 2 ? '0' : ($m['status'] + 1), '">', isset($status_labels[$m['status']]) ? $status_labels[$m['status']] : '?', '</span></td>
					<td>', !empty($m['created_at']) ? timeformat($m['created_at']) : '-', '</td>
					<td>';

			// Dispute resolution
			if ($m['status'] == 2)
			{
				echo '
						<a href="', $scripturl, '?action=admin;area=rivals;sa=matches;resolve=', $m['id_match'], ';winner=', $m['challenger_id'], ';', $context['session_var'], '=', $context['session_id'], '" class="button" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_admin_resolve_challenger'], '</a>
						<a href="', $scripturl, '?action=admin;area=rivals;sa=matches;resolve=', $m['id_match'], ';winner=', $m['challengee_id'], ';', $context['session_var'], '=', $context['session_id'], '" class="button" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_admin_resolve_challengee'], '</a>';
			}

			echo '
						<a href="', $scripturl, '?action=admin;area=rivals;sa=matches;delete=', $m['id_match'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['rivals_confirm'], '\');">', $txt['rivals_delete'], '</a>
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}

	if (!empty($context['page_index']))
		echo '
		<div class="pagesection"><div class="pagelinks">', $context['page_index'], '</div></div>';

	echo '
	</div>';
}
?>