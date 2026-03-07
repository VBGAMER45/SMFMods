<?php
/*
SMF Staff Page
Version 2.0
by:vbgamer45
https://www.smfhacks.com
*/
function template_main()
{
	global $scripturl, $context, $txt, $settings, $modSettings, $memberContext;

	// Check if the user is an Admin
	$manage_staff = allowedTo('admin_forum');

	$totalcols = 1;
	if ($modSettings['smfstaff_showavatar'])
		$totalcols++;

	if ($modSettings['smfstaff_showlastactive'])
		$totalcols++;

	if ($modSettings['smfstaff_showdateregistered'])
		$totalcols++;

	if ($modSettings['smfstaff_showcontactinfo'])
		$totalcols++;

	echo '
	<div class="main_section" id="staff_page">';

	if (!empty($context['smfstaff_groups']))
		foreach ($context['smfstaff_groups'] as $id => $data)
		{
			if (empty($context['smfstaff_users'][$data['id']]))
				continue;

			echo '
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th scope="col" class="lefttext">', $data['name'], '</th>';

			if ($modSettings['smfstaff_showavatar'])
				echo '
					<th scope="col" class="centertext">', $txt['smfstaff_avatar'], '</th>';

			if ($modSettings['smfstaff_showlastactive'])
				echo '
					<th scope="col" class="centertext">', $txt['smfstaff_lastactive'], '</th>';

			if ($modSettings['smfstaff_showdateregistered'])
				echo '
					<th scope="col" class="centertext">', $txt['smfstaff_dateregistered'], '</th>';

			if ($modSettings['smfstaff_showcontactinfo'])
				echo '
					<th scope="col" class="centertext">', $txt['smfstaff_contact'], '</th>';

			echo '
				</tr>
			</thead>
			<tbody>';

			if (!empty($context['smfstaff_users'][$data['id']]))
				foreach ($context['smfstaff_users'][$data['id']] as $id => $row2)
				{
					echo '
				<tr class="windowbg">
					<td><a href="', $scripturl, '?action=profile;u=', $row2['ID_MEMBER'], '"', (!empty($data['color']) ? ' style="color: ' . $data['color'] . ';"' : ''), '>', $row2['realName'], '</a></td>';

					if ($modSettings['smfstaff_showavatar'])
					{
						echo '
					<td>';
						// Display the users avatar
						$memCommID = $row2['ID_MEMBER'];
						loadMemberData($memCommID);
						loadMemberContext($memCommID);
						echo $memberContext[$memCommID]['avatar']['image'];
						echo '</td>';
					}

					if ($modSettings['smfstaff_showlastactive'])
						echo '
					<td>', timeformat($row2['lastLogin']), '</td>';

					if ($modSettings['smfstaff_showdateregistered'])
						echo '
					<td>', timeformat($row2['dateRegistered']), '</td>';

					if ($modSettings['smfstaff_showcontactinfo'])
					{
						echo '
					<td>';
						// Send PM row
						echo '<a href="', $scripturl, '?action=pm;sa=send;u=', $row2['ID_MEMBER'], '">', $txt['smfstaff_sendpm'], '</a>';
						echo '</td>';
					}

					echo '
				</tr>';
				}

			// If they are allowed to manage the staff page give them the option
			if ($manage_staff)
				echo '
				<tr class="windowbg">
					<td class="centertext" colspan="', $totalcols, '">
						<a href="', $scripturl, '?action=staff;sa=catdown&id=', $data['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smfstaff_down'], '</a> | <a href="', $scripturl, '?action=staff;sa=catup&id=', $data['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smfstaff_up'], '</a> | <a href="', $scripturl, '?action=staff;sa=delete&id=', $data['id'], ';ret;', $context['session_var'], '=', $context['session_id'], '" class="you_sure" data-confirm="', $txt['smfstaff_delgroup_confirm'], '">', $txt['smfstaff_delgroup'], '</a>
					</td>
				</tr>';

			echo '
			</tbody>
		</table>
		<br />';
		}

	if ($modSettings['smfstaff_showlocalmods'])
	{
		if (!empty($context['smfstaff_localmods']))
		{
			echo '
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th scope="col" class="lefttext">', $txt['smfstaff_local'], '</th>';

			if ($modSettings['smfstaff_showavatar'])
				echo '
					<th scope="col" class="centertext">', $txt['smfstaff_avatar'], '</th>';

			if ($modSettings['smfstaff_showlastactive'])
				echo '
					<th scope="col" class="centertext">', $txt['smfstaff_lastactive'], '</th>';

			if ($modSettings['smfstaff_showdateregistered'])
				echo '
					<th scope="col" class="centertext">', $txt['smfstaff_dateregistered'], '</th>';

			echo '
					<th scope="col" class="centertext">', $txt['smfstaff_forums'], '</th>';

			if ($modSettings['smfstaff_showcontactinfo'])
				echo '
					<th scope="col" class="centertext">', $txt['smfstaff_contact'], '</th>';

			echo '
				</tr>
			</thead>
			<tbody>';

			foreach ($context['smfstaff_localmods'] as $id => $data)
			{
				echo '
				<tr class="windowbg">
					<td><a href="', $scripturl, '?action=profile;u=', $data['id'], '">', $data['realName'], '</a></td>';

				if ($modSettings['smfstaff_showavatar'])
				{
					echo '
					<td>';
					// Display the users avatar
					$memCommID = $data['id'];
					loadMemberData($memCommID);
					loadMemberContext($memCommID);
					echo $memberContext[$memCommID]['avatar']['image'];
					echo '</td>';
				}

				if ($modSettings['smfstaff_showlastactive'])
					echo '
					<td>', timeformat($data['lastLogin']), '</td>';

				if ($modSettings['smfstaff_showdateregistered'])
					echo '
					<td>', timeformat($data['dateRegistered']), '</td>';

				echo '
					<td>', $data['forums'], '</td>';

				if ($modSettings['smfstaff_showcontactinfo'])
				{
					echo '
					<td class="centertext">';
					// Send PM row
					echo '<a href="', $scripturl, '?action=pm;sa=send;u=', $data['id'], '">', $txt['smfstaff_sendpm'], '</a>';
					echo '</td>';
				}

				echo '
				</tr>';
			}

			echo '
			</tbody>
		</table>';
		}
	}

	// If they can manage the staff page show them the link
	if ($manage_staff)
		echo '
		<div class="centertext"><a href="', $scripturl, '?action=admin;area=staff;sa=admin">', $txt['smfstaff_admin'], '</a></div><br />';

	// The Copyright is required to remain or contact me to purchase link removal.
	echo '
		<div class="centertext smalltext">Powered by: <a href="https://www.smfhacks.com" target="_blank" rel="noopener">SMF Staff</a></div>
	</div><!-- #staff_page -->';
}

function template_adminset()
{
	global $scripturl, $modSettings, $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">', $txt['smfstaff_staffsetting'], '</h3>
	</div>
	<div class="windowbg">
		<form method="post" action="', $scripturl, '?action=staff;sa=admin2" accept-charset="', $context['character_set'], '">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<dl class="settings">
				<dt><label for="smfstaff_showavatar">', $txt['smfstaff_showavatar'], '</label></dt>
				<dd><input type="checkbox" name="smfstaff_showavatar" id="smfstaff_showavatar"', ($modSettings['smfstaff_showavatar'] ? ' checked' : ''), '></dd>
				<dt><label for="smfstaff_showlastactive">', $txt['smfstaff_showlastactive'], '</label></dt>
				<dd><input type="checkbox" name="smfstaff_showlastactive" id="smfstaff_showlastactive"', ($modSettings['smfstaff_showlastactive'] ? ' checked' : ''), '></dd>
				<dt><label for="smfstaff_showdateregistered">', $txt['smfstaff_showdateregistered'], '</label></dt>
				<dd><input type="checkbox" name="smfstaff_showdateregistered" id="smfstaff_showdateregistered"', ($modSettings['smfstaff_showdateregistered'] ? ' checked' : ''), '></dd>
				<dt><label for="smfstaff_showcontactinfo">', $txt['smfstaff_showcontactinfo'], '</label></dt>
				<dd><input type="checkbox" name="smfstaff_showcontactinfo" id="smfstaff_showcontactinfo"', ($modSettings['smfstaff_showcontactinfo'] ? ' checked' : ''), '></dd>
				<dt><label for="smfstaff_showlocalmods">', $txt['smfstaff_showlocalmods'], '</label></dt>
				<dd><input type="checkbox" name="smfstaff_showlocalmods" id="smfstaff_showlocalmods"', ($modSettings['smfstaff_showlocalmods'] ? ' checked' : ''), '></dd>
			</dl>
			<input type="submit" name="savesettings" value="', $txt['smfstaff_savesettings'], '" class="button">
		</form>
		<br />
		<strong>', $txt['smfstaff_groupstoadd'], '</strong>
		<ul class="nolist">';

	foreach ($context['smfstaff_groups'] as $id => $data)
		echo '
			<li>', $data['name'], ' - <a href="', $scripturl, '?action=staff;sa=add&id=', $data['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smfstaff_addgroup'], '</a></li>';

	echo '
		</ul>
		<br />
		<strong>', $txt['smfstaff_groupstoshow'], '</strong>
		<ul class="nolist">';

	foreach ($context['smfstaff_showgroups'] as $id => $data)
		echo '
			<li>', $data['name'], ' - <a href="', $scripturl, '?action=staff;sa=delete&id=', $data['id'], ';', $context['session_var'], '=', $context['session_id'], '" class="you_sure" data-confirm="', $txt['smfstaff_delgroup_confirm'], '">', $txt['smfstaff_delgroup'], '</a></li>';

	echo '
		</ul>
	</div>';
}
?>