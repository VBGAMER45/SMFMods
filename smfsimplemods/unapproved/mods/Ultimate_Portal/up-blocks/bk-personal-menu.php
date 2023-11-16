<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');
	
global $sourcedir, $context, $ultimateportalSettings, $boardurl, $boarddir;
global $scripturl, $txt, $user_info, $settings, $sc, $message;

if (!$user_info['is_guest'])
{
		
	echo 
			'<table style="border-spacing:5px;table-layout:fixed;width:100%;" border="0" cellspacing="1" cellpadding="3">
				<tr>
					<td align="center">
						<strong>'. $txt['hello_member_ndt']. ' <a href="'.$scripturl.'?action=profile;u='.$user_info['id'].'">' . $user_info['username']. '</a></strong>
					</td>
				</tr>
				<tr>
					<td align="center">';
			if (!empty($context['user']['avatar']))
			echo '
				<span class="avatar">', $context['user']['avatar']['image'], '</span>';
			
					echo'</td>
				</tr>';
	//Admin Panel 
	if($user_info['is_admin'])
	{			
		echo '				
				<tr>
					<td align="left">
						<img alt="" style="float:left" width="16" height="16" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/admin.png" />&nbsp;<a href="'.$scripturl.'?action=admin">'.$txt['ultport_admin_panel'].'</a>
					</td>
				</tr>';
	}
	//End Access the Admin Panel
	//Profile Access
	echo '				
				<tr>
					<td align="left">
						<img alt="" style="float:left" width="16" height="16" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/user.png" />&nbsp;<a href="'.$scripturl.'?action=profile">'.$txt['ultport_profile_panel'].'</a>
					</td>
				</tr>
				<tr>
					<td align="left">
						<img alt="" style="float:left" width="16" height="16" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/mp.png" />&nbsp;<a href="'.$scripturl.'?action=pm">'.$txt['ultport_profile_mps'].'&nbsp;( '. $user_info['unread_messages'] .' / '. $user_info['messages'] .'</a> )
					</td>
				</tr>				
				<tr>
					<td align="left">
						<img alt="" style="float:left" width="16" height="16" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/logout.png" />&nbsp;<a href="'. $scripturl. '?action=logout;sesc='. $sc .'">'.$txt['ultport_profile_logout'].'</a>
					</td>
				</tr>								
				';
	//End Access Profile
				
	echo '								
			</table>';

}else{
		echo '
			<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
				<table align="center">
					<tr>
						<td><label>', $txt['username'], ':</label>&nbsp;</td>
					
						<td><input type="text" id="sp_user" name="user" size="9" value="', !empty($user_info['username']) ? $user_info['username'] : '', '" /></td>
					</tr><tr>
						<td><label>', $txt['password'], ':</label>&nbsp;</td>
					
						<td><input type="password" name="passwrd" id="sp_passwrd" size="9" /></td>
					</tr><tr>
						<td>
							<select name="cookielength">
								<option value="60">', $txt['one_hour'], '</option>
								<option value="1440">', $txt['one_day'], '</option>
								<option value="10080">', $txt['one_week'], '</option>
								<option value="43200">', $txt['one_month'], '</option>
								<option value="-1" selected="selected">', $txt['forever'], '</option>
							</select>
						</td>
						<td><input type="submit" value="', $txt['login'], '" /></td>
					</tr>
				</table>
			</form><div align="center">', sprintf($txt['welcome_guest'], $txt['guest_title']),'</div>';	
}
?>