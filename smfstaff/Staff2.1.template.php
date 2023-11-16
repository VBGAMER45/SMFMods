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

		$totalcols =1;
		if ($modSettings['smfstaff_showavatar'])
			$totalcols++;
					
		if ($modSettings['smfstaff_showlastactive'])
			$totalcols++;;
					
		if ($modSettings['smfstaff_showdateregistered'])
			$totalcols++;
				
		if ($modSettings['smfstaff_showcontactinfo'])	
			$totalcols++;
				

		if (!empty($context['smfstaff_groups']))
		foreach ($context['smfstaff_groups']  as $id => $data)
		{
			if (empty($context['smfstaff_users'][$data['id']]))
				continue;

				echo '<table border="0" cellspacing="0" cellpadding="2" width="100%"  class="table_grid">';
				echo '<tr class="title_bar">';
				echo '<th class="lefttext first_th">', $data['name'], '</th>';
			
				if ($modSettings['smfstaff_showavatar'])
					echo '<th class="centertext" align="center">', $txt['smfstaff_avatar'], '</th>';
					
				if ($modSettings['smfstaff_showlastactive'])
					echo '<th class="centertext" align="center">', $txt['smfstaff_lastactive'], '</th>';
					
				if ($modSettings['smfstaff_showdateregistered'])
					echo '<th class="centertext" align="center">', $txt['smfstaff_dateregistered'], '</th>';
				
				if ($modSettings['smfstaff_showcontactinfo'])	
					echo '<th class="centertext" align="center">', $txt['smfstaff_contact'], '</th>';
				
				echo '</tr>';
				
				
				if (!empty($context['smfstaff_users'][$data['id']]))
				foreach ($context['smfstaff_users'][$data['id']]  as $id => $row2)
				{
	
						
					echo '<tr>';
					echo '<td class="windowbg"><a href="' . $scripturl . '?action=profile;u=' . $row2['ID_MEMBER'] . '"><font color="' . $data['color'] . '">' . $row2['realName'] . '</font></a></td>';
					
					if ($modSettings['smfstaff_showavatar'])
					{
						echo '<td class="windowbg">';
						// Display the users avatar
			            $memCommID = $row2['ID_MEMBER'];
			            loadMemberData($memCommID);
			            loadMemberContext($memCommID);
			            echo $memberContext[$memCommID]['avatar']['image'];

						echo '</td>';
					}
					
					
					if ($modSettings['smfstaff_showlastactive'])
						echo '<td class="windowbg">' . timeformat($row2['lastLogin']) . '</td>';
					
					if ($modSettings['smfstaff_showdateregistered'])
						echo '<td class="windowbg">' .  timeformat($row2['dateRegistered']) . '</td>';
					
						
					if ($modSettings['smfstaff_showcontactinfo'])
					{
						echo '<td class="windowbg">';

						// Send PM row
						echo '<a href="' . $scripturl . '?action=pm;sa=send;u=' . $row2['ID_MEMBER'] . '">' . $txt['smfstaff_sendpm'] . '</a>';
	
						echo '</td>';
					} // End Contact Information
					
			
					echo '</tr>';
				}
				// If they are allowed to manage the staff page give them the option
				if ($manage_staff)
					echo '<tr>
					<td align="center" colspan="',$totalcols,'" class="windowbg">
					<a href="' . $scripturl . '?action=staff;sa=catdown&id=' . $data['id'] . '">' . $txt['smfstaff_down'] . '</a> | <a href="' . $scripturl . '?action=staff;sa=catup&id=' . $data['id'] . '">' . $txt['smfstaff_up'] . '</a> | <a href="' . $scripturl . '?action=staff;sa=delete&id=' . $data['id'] . ';ret">' . $txt['smfstaff_delgroup'] . '</a></td></tr>';

				
				echo '</table>';
			
			// Separate the groups from the local mods.
			echo '<br />';
		} // End of Main staff listing

			if ($modSettings['smfstaff_showlocalmods'])
			{
				
				
				
				if (!empty($context['smfstaff_localmods']))
				{
					echo '<table border="0" cellspacing="0" cellpadding="2" width="100%"  class="table_grid">';
					echo '<tr class="title_bar">';
					echo '<th class="lefttext first_th">', $txt['smfstaff_local'], '</th>';
					
					if($modSettings['smfstaff_showavatar'])
						echo '<th class="centertext" align="center">', $txt['smfstaff_avatar'], '</th>';
					
				
					if($modSettings['smfstaff_showlastactive'])
						echo '<th class="centertext" align="center">', $txt['smfstaff_lastactive'], '</th>';
					
					if($modSettings['smfstaff_showdateregistered'])
						echo '<th class="centertext" align="center">', $txt['smfstaff_dateregistered'], '</th>';
					
					
					echo '<th class="centertext" align="center">', $txt['smfstaff_forums'], '</th>';
					
					if($modSettings['smfstaff_showcontactinfo'])
						echo '<th class="centertext" align="center">', $txt['smfstaff_contact'], '</th>';
					
					echo '</tr>';
	
	
					foreach ($context['smfstaff_localmods']  as $id => $data)
					{
							echo '<tr>';
							echo '<td class="windowbg"><a href="', $scripturl, '?action=profile;u=', $data['id'], '">', $data['realName'], '</a></td>';
							
							
							if ($modSettings['smfstaff_showavatar'])
							{
								echo '<td class="windowbg">';
								//Display the users avatar
					            $memCommID = $data['id'];
					            loadMemberData($memCommID);
					            
					            			           
			            		loadMemberContext($memCommID);
			           			echo $memberContext[$memCommID]['avatar']['image'];
								echo '</td>';
							}
							
							if($modSettings['smfstaff_showlastactive'])
								echo '<td class="windowbg">', timeformat($data['lastLogin']), '</td>';
							
							if ($modSettings['smfstaff_showdateregistered'])
								echo '<td class="windowbg">',  timeformat($data['dateRegistered']), '</td>';
														
							
							echo '<td class="windowbg">', $data['forums'], '</td>';
							
							if ($modSettings['smfstaff_showcontactinfo'])
							{
								echo '<td class="windowbg" align="center">';
		

								//Send PM row
								echo '<a href="', $scripturl, '?action=pm;sa=send;u=', $data['id'], '">', $txt['smfstaff_sendpm'], '</a>';
		
								echo '</td>';
							} // End smfstaff_showcontactinfo
							
							
							echo '</tr>';
					}
	
					echo '</table>';
				} // End of local mods count
				
			} // End of modSettings local mods check
	
			
			
	// If they can manage the staff page show them the link
	if ($manage_staff)
		echo '<div align="center"><a href="', $scripturl, '?action=admin;area=staff;sa=admin">', $txt['smfstaff_admin'], '</a></div><br />';

	
			
	// The Copyright is required to remain or contact me to purchase link removal.
	echo '<div align="center" class="smalltext">Powered by: <a href="https://www.smfhacks.com" target="blank">SMF Staff</a></div>';

	
}

function template_adminset()
{
		global $scripturl, $modSettings, $txt, $context;

echo '
<div class="cat_bar">
		<h3 class="catbg centertext">
		' . $txt['smfstaff_staffsetting'] . '
		</h3>
  </div>
<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg2">
			<td>
			<b>', $txt['smfstaff_staffsetting'], '</b><br />
			<form method="post" action="', $scripturl, '?action=staff;sa=admin2">
				
				<input type="checkbox" name="smfstaff_showavatar" ', ($modSettings['smfstaff_showavatar'] ? ' checked="checked" ' : '') . ' />', $txt['smfstaff_showavatar'], '<br />
				<input type="checkbox" name="smfstaff_showlastactive" ', ($modSettings['smfstaff_showlastactive'] ? ' checked="checked" ' : ''), ' />' . $txt['smfstaff_showlastactive'], '<br />
				<input type="checkbox" name="smfstaff_showdateregistered" ', ($modSettings['smfstaff_showdateregistered'] ? ' checked="checked" ' : ''), ' />' . $txt['smfstaff_showdateregistered'], '<br />
				<input type="checkbox" name="smfstaff_showcontactinfo" ', ($modSettings['smfstaff_showcontactinfo'] ? ' checked="checked" ' : ''), ' />' . $txt['smfstaff_showcontactinfo'], '<br />
				<input type="checkbox" name="smfstaff_showlocalmods" ', ($modSettings['smfstaff_showlocalmods'] ? ' checked="checked" ' : ''), ' />' . $txt['smfstaff_showlocalmods'], '<br />
				<input type="submit" name="savesettings" value="' . $txt['smfstaff_savesettings'] . '" />
			</form>
			<br /><br />
			
			<b>',$txt['smfstaff_groupstoadd'], '</b><br />';

		foreach ($context['smfstaff_groups'] as $id => $data)
			echo $data['name'], '&nbsp; -<a href="', $scripturl, '?action=staff;sa=add&id=', $data['id'], '">' . $txt['smfstaff_addgroup'] . '</a><br />';

		
		echo '<br /><br /><b>',$txt['smfstaff_groupstoshow'], '</b><br />';
		
		foreach ($context['smfstaff_showgroups'] as $id => $data)
			echo $data['name'], '&nbsp;- <a href="', $scripturl, '?action=staff;sa=delete&id=', $data['id'], '">' . $txt['smfstaff_delgroup'] . '</a><br />';
	
echo '
			
			<br /><br />
			<b>Has SMF Staff Page helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="SMF Staff Page">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
			</td>
		</tr>
</table>';

}
?>