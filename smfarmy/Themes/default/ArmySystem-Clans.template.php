<?php
/**
 * Army System - Clan Templates
 *
 * Provides template functions for the clan listing page (with clan
 * creation form) and the detailed clan view page (with management
 * controls for leaders, join/leave buttons for members).
 *
 * @package ArmySystem
 * @version 1.0
 */

/**
 * Clan listing template.
 *
 * Displays a table of all active clans with their leader, member count,
 * and type (Open/Invite Only). Includes a clan creation form at the
 * bottom for eligible players.
 *
 * Context variables used:
 *   $context['army_clans']          - array, all clans with summary data
 *     ['id']           - int
 *     ['name']         - string
 *     ['leader_name']  - string
 *     ['leader_id']    - int
 *     ['member_count'] - int
 *     ['by_invite']    - bool
 *     ['by_join']      - bool
 *   $context['army_can_create']     - bool, true if player can create a clan
 *   $context['army_clan_error']     - string|null, error message
 *   $context['army_clan_success']   - string|null, success message
 *   $context['army_session_var']    - string, session variable name
 *   $context['army_session_id']     - string, session token value
 */
function template_army_clans()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_clans_title'] ?? 'Clans', '</h3>
			</div>';

	// Error message
	if (!empty($context['army_clan_error']))
	{
		echo '
			<div class="errorbox">
				', $context['army_clan_error'], '
			</div>';
	}

	// Success message
	if (!empty($context['army_clan_success']))
	{
		echo '
			<div class="infobox">
				', $context['army_clan_success'], '
			</div>';
	}

	// Clans table
	echo '
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th>', $txt['army_col_clan_name'] ?? 'Clan Name', '</th>
						<th>', $txt['army_col_leader'] ?? 'Leader', '</th>
						<th class="centercol">', $txt['army_col_members'] ?? 'Members', '</th>
						<th class="centercol">', $txt['army_col_type'] ?? 'Type', '</th>
					</tr>
				</thead>
				<tbody>';

	if (!empty($context['army_clans']))
	{
		foreach ($context['army_clans'] as $clan)
		{
			// Clan type label
			if (!empty($clan['by_join']))
				$type_html = '<span class="army_bonus_positive">' . ($txt['army_clan_open'] ?? 'Open') . '</span>';
			else
				$type_html = '<span class="army_bonus_neutral">' . ($txt['army_clan_invite_only'] ?? 'Invite Only') . '</span>';

			echo '
					<tr class="windowbg">
						<td>
							<a href="', $scripturl, '?action=army;sa=clan;id=', $clan['id'], '"><strong>', htmlspecialchars($clan['name']), '</strong></a>
						</td>
						<td>
							<a href="', $scripturl, '?action=army;sa=profile;u=', $clan['leader_id'], '">', htmlspecialchars($clan['leader_name']), '</a>
						</td>
						<td class="centercol">', $clan['member_count'], '</td>
						<td class="centercol">', $type_html, '</td>
					</tr>';
		}
	}
	else
	{
		echo '
					<tr class="windowbg">
						<td colspan="4" class="centercol">', $txt['army_no_clans'] ?? 'No clans have been created yet.', '</td>
					</tr>';
	}

	echo '
				</tbody>
			</table>';

	// Create clan form
	if (!empty($context['army_can_create']))
	{
		echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_create_clan'] ?? 'Create a Clan', '</h3>
			</div>
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=clans" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<dl class="settings">
						<dt>
							<label for="clan_name">', $txt['army_clan_name_label'] ?? 'Clan Name', '</label>
						</dt>
						<dd>
							<input type="text" name="clan_name" id="clan_name" value="" maxlength="255" size="40" class="input_text" required>
						</dd>
						<dt>
							<label for="clan_desc">', $txt['army_clan_desc_label'] ?? 'Description', '</label>
						</dt>
						<dd>
							<textarea name="clan_desc" id="clan_desc" rows="3" cols="50" class="input_text"></textarea>
						</dd>
						<dt>
							<label>', $txt['army_clan_join_settings'] ?? 'Join Settings', '</label>
						</dt>
						<dd>
							<label>
								<input type="checkbox" name="by_invite" value="1">
								', $txt['army_clan_allow_invite'] ?? 'Allow joining by invitation', '
							</label>
							<br>
							<label>
								<input type="checkbox" name="by_join" value="1" checked>
								', $txt['army_clan_allow_open'] ?? 'Allow open joining (anyone can join)', '
							</label>
						</dd>
					</dl>
					<div class="righttext">
						<input type="submit" name="create_clan" value="', $txt['army_btn_create_clan'] ?? 'Create Clan', '" class="button">
					</div>
				</form>
			</div>';
	}

	echo '
		</div>
	</div>';
}

/**
 * Clan detail view template.
 *
 * Displays clan header info, member list, and context-sensitive controls:
 * - Leaders see management tools (invite, pending requests, edit, disband)
 * - Non-members see join/request buttons
 * - Members see a leave button
 * - Clan notes are shown to all and editable by the leader
 *
 * Context variables used:
 *   $context['army_clan']           - array, full clan data
 *     ['id']              - int
 *     ['name']            - string
 *     ['description']     - string
 *     ['leader_name']     - string
 *     ['leader_id']       - int
 *     ['by_invite']       - bool
 *     ['by_join']         - bool
 *     ['started']         - string, formatted date
 *     ['notes']           - string, clan notes (HTML safe)
 *   $context['army_clan_members']   - array, member list
 *     ['id']              - int
 *     ['name']            - string
 *     ['race_name']       - string
 *     ['army_size']       - int/string
 *     ['time_joined']     - string, formatted date
 *   $context['army_clan_pending']   - array|null, pending requests (leader only)
 *     ['id']              - int, member id
 *     ['name']            - string
 *     ['time_pending']    - string, formatted date
 *   $context['army_is_leader']      - bool, true if current user is leader
 *   $context['army_is_member']      - bool, true if current user is clan member
 *   $context['army_can_join']       - bool, true if user can join this clan
 *   $context['army_can_request']    - bool, true if user can request to join
 *   $context['army_clan_error']     - string|null
 *   $context['army_clan_success']   - string|null
 *   $context['army_session_var']    - string, session variable name
 *   $context['army_session_id']     - string, session token value
 */
function template_army_clan_view()
{
	global $context, $txt, $scripturl;

	$clan = $context['army_clan'];

	echo '
	<div class="army_wrapper">';

	template_army_sidebar();

	echo '
		<div class="army_content">
			<div class="cat_bar">
				<h3 class="catbg">', htmlspecialchars($clan['name']), '</h3>
			</div>';

	// Error message
	if (!empty($context['army_clan_error']))
	{
		echo '
			<div class="errorbox">
				', $context['army_clan_error'], '
			</div>';
	}

	// Success message
	if (!empty($context['army_clan_success']))
	{
		echo '
			<div class="infobox">
				', $context['army_clan_success'], '
			</div>';
	}

	// Clan header info
	echo '
			<div class="windowbg">
				<dl class="army_stats_list">
					<dt>', $txt['army_clan_description'] ?? 'Description', '</dt>
					<dd>', htmlspecialchars($clan['description']), '</dd>
					<dt>', $txt['army_clan_leader'] ?? 'Leader', '</dt>
					<dd>
						<a href="', $scripturl, '?action=army;sa=profile;u=', $clan['leader_id'], '">', htmlspecialchars($clan['leader_name']), '</a>
					</dd>
					<dt>', $txt['army_clan_type'] ?? 'Type', '</dt>
					<dd>';

	if (!empty($clan['by_join']))
		echo '<span class="army_bonus_positive">', $txt['army_clan_open'] ?? 'Open', '</span>';
	elseif (!empty($clan['by_invite']))
		echo '<span class="army_bonus_neutral">', $txt['army_clan_invite_only'] ?? 'Invite Only', '</span>';
	else
		echo '<span class="army_bonus_negative">', $txt['army_clan_closed'] ?? 'Closed', '</span>';

	echo '</dd>
					<dt>', $txt['army_clan_founded'] ?? 'Founded', '</dt>
					<dd>', $clan['started'], '</dd>
					<dt>', $txt['army_col_members'] ?? 'Members', '</dt>
					<dd>', count($context['army_clan_members']), '</dd>
				</dl>
			</div>';

	// --- Members table ---
	echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_clan_members_title'] ?? 'Clan Members', '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th>', $txt['army_col_player'] ?? 'Player', '</th>
						<th>', $txt['army_col_race'] ?? 'Race', '</th>
						<th class="centercol">', $txt['army_col_army_size'] ?? 'Army Size', '</th>
						<th class="centercol">', $txt['army_col_joined'] ?? 'Joined', '</th>';

	// Leader gets a kick column
	if (!empty($context['army_is_leader']))
	{
		echo '
						<th class="centercol">', $txt['army_col_action'] ?? 'Action', '</th>';
	}

	echo '
					</tr>
				</thead>
				<tbody>';

	if (!empty($context['army_clan_members']))
	{
		foreach ($context['army_clan_members'] as $member)
		{
			$is_leader_row = ((int) $member['id'] === (int) $clan['leader_id']);

			echo '
					<tr class="windowbg">
						<td>
							<a href="', $scripturl, '?action=army;sa=profile;u=', $member['id'], '">', htmlspecialchars($member['name']), '</a>';

			if ($is_leader_row)
				echo ' <span class="smalltext army_bonus_positive">(', $txt['army_clan_leader_tag'] ?? 'Leader', ')</span>';

			echo '
						</td>
						<td>', htmlspecialchars($member['race_name']), '</td>
						<td class="centercol">', $member['army_size'], '</td>
						<td class="centercol">', $member['time_joined'], '</td>';

			// Leader can kick (but not themselves)
			if (!empty($context['army_is_leader']))
			{
				echo '
						<td class="centercol">';

				if (!$is_leader_row)
				{
					echo '
							<form action="', $scripturl, '?action=army;sa=clan;id=', $clan['id'], '" method="post" accept-charset="UTF-8" style="display:inline;">
								<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
								<input type="hidden" name="kick_member" value="', $member['id'], '">
								<input type="submit" value="', $txt['army_btn_kick'] ?? 'Kick', '" class="button" onclick="return confirm(\'', ($txt['army_clan_kick_confirm'] ?? 'Are you sure you want to remove this member from the clan?'), '\');">
							</form>';
				}
				else
				{
					echo '&mdash;';
				}

				echo '
						</td>';
			}

			echo '
					</tr>';
		}
	}
	else
	{
		$colspan = !empty($context['army_is_leader']) ? 5 : 4;

		echo '
					<tr class="windowbg">
						<td colspan="', $colspan, '" class="centercol">', $txt['army_clan_no_members'] ?? 'No members found.', '</td>
					</tr>';
	}

	echo '
				</tbody>
			</table>';

	// --- Join / Request / Leave buttons ---
	if (!empty($context['army_can_join']))
	{
		echo '
			<div class="windowbg">
				<form action="', $scripturl, '?action=army;sa=clan;id=', $clan['id'], '" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<div class="centertext">
						<p>', $txt['army_clan_join_desc'] ?? 'This clan is open for new members. Click below to join.', '</p>
						<input type="submit" name="join_clan" value="', $txt['army_btn_join_clan'] ?? 'Join Clan', '" class="button">
					</div>
				</form>
			</div>';
	}
	elseif (!empty($context['army_can_request']))
	{
		echo '
			<div class="windowbg">
				<form action="', $scripturl, '?action=army;sa=clan;id=', $clan['id'], '" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<div class="centertext">
						<p>', $txt['army_clan_request_desc'] ?? 'This clan is invite-only. You may request to join and the leader will review your request.', '</p>
						<input type="submit" name="request_join" value="', $txt['army_btn_request_join'] ?? 'Request to Join', '" class="button">
					</div>
				</form>
			</div>';
	}
	elseif (!empty($context['army_is_member']) && empty($context['army_is_leader']))
	{
		echo '
			<div class="windowbg">
				<form action="', $scripturl, '?action=army;sa=clan;id=', $clan['id'], '" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<div class="centertext">
						<input type="submit" name="leave_clan" value="', $txt['army_btn_leave_clan'] ?? 'Leave Clan', '" class="button" onclick="return confirm(\'', ($txt['army_clan_leave_confirm'] ?? 'Are you sure you want to leave this clan?'), '\');">
					</div>
				</form>
			</div>';
	}

	// --- Leader Management Section ---
	if (!empty($context['army_is_leader']))
	{
		echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['army_clan_management'] ?? 'Clan Management', '</h3>
			</div>';

		// --- Pending Requests ---
		if (!empty($context['army_clan_pending']))
		{
			echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_clan_pending'] ?? 'Pending Join Requests', '</h4>
			</div>
			<table class="table_grid army_table" width="100%">
				<thead>
					<tr class="title_bar">
						<th>', $txt['army_col_player'] ?? 'Player', '</th>
						<th class="centercol">', $txt['army_col_requested'] ?? 'Requested', '</th>
						<th class="centercol">', $txt['army_col_action'] ?? 'Action', '</th>
					</tr>
				</thead>
				<tbody>';

			foreach ($context['army_clan_pending'] as $pending)
			{
				echo '
					<tr class="windowbg">
						<td>
							<a href="', $scripturl, '?action=army;sa=profile;u=', $pending['id'], '">', htmlspecialchars($pending['name']), '</a>
						</td>
						<td class="centercol">', $pending['time_pending'], '</td>
						<td class="centercol">
							<form action="', $scripturl, '?action=army;sa=clan;id=', $clan['id'], '" method="post" accept-charset="UTF-8" style="display:inline;">
								<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
								<input type="hidden" name="pending_member" value="', $pending['id'], '">
								<input type="submit" name="approve_request" value="', $txt['army_btn_approve'] ?? 'Approve', '" class="button">
								<input type="submit" name="deny_request" value="', $txt['army_btn_deny'] ?? 'Deny', '" class="button">
							</form>
						</td>
					</tr>';
			}

			echo '
				</tbody>
			</table>';
		}

		// --- Invite Member ---
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_clan_invite'] ?? 'Invite a Player', '</h4>
			</div>
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=clan;id=', $clan['id'], '" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<dl class="settings">
						<dt>
							<label for="invite_name">', $txt['army_clan_invite_label'] ?? 'Player Name', '</label>
						</dt>
						<dd>
							<input type="text" name="invite_name" id="invite_name" value="" maxlength="80" size="30" class="input_text">
						</dd>
					</dl>
					<div class="righttext">
						<input type="submit" name="invite_member" value="', $txt['army_btn_invite'] ?? 'Send Invitation', '" class="button">
					</div>
				</form>
			</div>';

		// --- Edit Clan Settings ---
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_clan_edit'] ?? 'Edit Clan', '</h4>
			</div>
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=clan;id=', $clan['id'], '" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<dl class="settings">
						<dt>
							<label for="edit_clan_name">', $txt['army_clan_name_label'] ?? 'Clan Name', '</label>
						</dt>
						<dd>
							<input type="text" name="clan_name" id="edit_clan_name" value="', htmlspecialchars($clan['name']), '" maxlength="255" size="40" class="input_text">
						</dd>
						<dt>
							<label for="edit_clan_desc">', $txt['army_clan_desc_label'] ?? 'Description', '</label>
						</dt>
						<dd>
							<textarea name="clan_desc" id="edit_clan_desc" rows="3" cols="50" class="input_text">', htmlspecialchars($clan['description']), '</textarea>
						</dd>
						<dt>
							<label>', $txt['army_clan_join_settings'] ?? 'Join Settings', '</label>
						</dt>
						<dd>
							<label>
								<input type="checkbox" name="by_invite" value="1"', (!empty($clan['by_invite']) ? ' checked' : ''), '>
								', $txt['army_clan_allow_invite'] ?? 'Allow joining by invitation', '
							</label>
							<br>
							<label>
								<input type="checkbox" name="by_join" value="1"', (!empty($clan['by_join']) ? ' checked' : ''), '>
								', $txt['army_clan_allow_open'] ?? 'Allow open joining (anyone can join)', '
							</label>
						</dd>
					</dl>
					<div class="righttext">
						<input type="submit" name="edit_clan" value="', $txt['army_btn_save_changes'] ?? 'Save Changes', '" class="button">
					</div>
				</form>
			</div>';

		// --- Clan Notes ---
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_clan_notes'] ?? 'Clan Notes', '</h4>
			</div>
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=clan;id=', $clan['id'], '" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<input type="hidden" name="clan_action" value="edit">
					<textarea name="clan_notes" rows="6" cols="60" class="input_text" style="width: 100%;">', htmlspecialchars($clan['notes']), '</textarea>
					<br>
					<div class="righttext">
						<input type="submit" name="save_notes" value="', $txt['army_btn_save_notes'] ?? 'Save Notes', '" class="button">
					</div>
				</form>
			</div>';

		// --- Transfer Leadership ---
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_clan_transfer_leader'] ?? 'Transfer Leadership', '</h4>
			</div>
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=clan;id=', $clan['id'], '" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<dl class="settings">
						<dt>
							<label for="transfer_leader">', $txt['army_clan_new_leader'] ?? 'New Leader', '</label>
						</dt>
						<dd>
							<select name="new_leader" id="transfer_leader">';

		if (!empty($context['army_clan_members']))
		{
			foreach ($context['army_clan_members'] as $member)
			{
				// Skip the current leader
				if ((int) $member['id'] === (int) $clan['leader_id'])
					continue;

				echo '
								<option value="', $member['id'], '">', htmlspecialchars($member['name']), '</option>';
			}
		}

		echo '
							</select>
						</dd>
					</dl>
					<div class="righttext">
						<input type="submit" name="transfer_leadership" value="', $txt['army_btn_transfer_leader'] ?? 'Transfer Leadership', '" class="button" onclick="return confirm(\'', ($txt['army_clan_transfer_confirm'] ?? 'Are you sure you want to transfer clan leadership? You will become a regular member.'), '\');">
					</div>
				</form>
			</div>';

		// --- Disband Clan ---
		echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_clan_disband'] ?? 'Disband Clan', '</h4>
			</div>
			<div class="roundframe">
				<form action="', $scripturl, '?action=army;sa=clan;id=', $clan['id'], '" method="post" accept-charset="UTF-8">
					<input type="hidden" name="', $context['army_session_var'], '" value="', $context['army_session_id'], '">
					<p class="army_bonus_negative">', $txt['army_clan_disband_desc'] ?? 'Disbanding the clan will permanently remove it and all members will be released. This action cannot be undone.', '</p>
					<div class="righttext">
						<input type="submit" name="disband_clan" value="', $txt['army_btn_disband'] ?? 'Disband Clan', '" class="button" onclick="return confirm(\'', ($txt['army_clan_disband_confirm'] ?? 'Are you sure you want to disband this clan? This cannot be undone!'), '\');">
					</div>
				</form>
			</div>';
	}
	else
	{
		// Non-leader clan notes (read-only)
		if (!empty($clan['notes']))
		{
			echo '
			<div class="title_bar">
				<h4 class="titlebg">', $txt['army_clan_notes'] ?? 'Clan Notes', '</h4>
			</div>
			<div class="windowbg">
				', nl2br(htmlspecialchars($clan['notes'])), '
			</div>';
		}
	}

	// Back to clans link
	echo '
			<div class="windowbg">
				<a class="button" href="', $scripturl, '?action=army;sa=clans">', $txt['army_back_to_clans'] ?? 'Back to Clans', '</a>
			</div>
		</div>
	</div>';
}
