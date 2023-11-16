<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

function template_preferences_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	//Header Main
	echo '
	<table width="100%" align="center" cellspacing="1" cellpadding="5" border="0">							
		<tr>
			<td width="100%" class="catbg" align="left">	
				', $txt['ultport_admin_preferences_title'] . ' - ' . $txt['ultport_preferences_title'] ,'
			</td>
		</tr>
		<tr>
			<td class="windowbg description" width="100%" align="left">	
				', $txt['main_description'] ,'
			</td>
		</tr>
	</table><br/>';
	//Rapid Links
	echo '
	<div style="border:1px solid" class="windowbg2">
			<div class="content">
				<ul class="flow_hidden" id="quick_tasks">
					<li>
						<a href="', $scripturl ,'?action=admin;area=preferences;sa=gral-settings;' . $context['session_var'].'=' . $context['session_id'].'"><img class="home_image png_fix" alt="" src="', $settings['default_theme_url'] ,'/images/ultimate-portal/admin-main/preferences.png" /></a>
						<h5><a href="', $scripturl ,'?action=admin;area=preferences;sa=gral-settings;' . $context['session_var'].'=' . $context['session_id'].'">', $txt['ultport_preferences_title'] ,'</a></h5>
						<span class="task">', $txt['ultport_admin_gral_settings_description'] ,'</span>
					</li>
					<li>
						<a href="', $scripturl ,'?action=admin;area=ultimate_portal_blocks;' . $context['session_var'].'=' . $context['session_id'].'"><img class="home_image png_fix" alt="" src="', $settings['default_theme_url'] ,'/images/ultimate-portal/admin-main/blocks.png" /></a>
						<h5><a href="', $scripturl ,'?action=admin;area=ultimate_portal_blocks;' . $context['session_var'].'=' . $context['session_id'].'">', $txt['main_blocks_title'] ,'</a></h5>
						<span class="task">', $txt['main_blocks_description'] ,'</span>
					</li>';
					
				if(!empty($ultimateportalSettings['user_posts_enable']))
				{
					echo '
					<li>
						<a href="', $scripturl ,'?action=admin;area=user-posts;' . $context['session_var'].'=' . $context['session_id'].'"><img class="home_image png_fix" alt="" src="', $settings['default_theme_url'] ,'/images/ultimate-portal/admin-main/user-posts.png" /></a>
						<h5><a href="', $scripturl ,'?action=admin;area=user-posts;' . $context['session_var'].'=' . $context['session_id'].'">', $txt['main_user_posts_title'] ,'</a></h5>
						<span class="task">', $txt['main_user_posts_description'] ,'</span>
					</li>';
				}
				if(!empty($ultimateportalSettings['up_news_enable']))
				{
					echo '
					<li>
						<a href="', $scripturl ,'?action=admin;area=up-news;' . $context['session_var'].'=' . $context['session_id'].'"><img class="home_image png_fix" alt="" src="', $settings['default_theme_url'] ,'/images/ultimate-portal/admin-main/news.png" /></a>
						<h5><a href="', $scripturl ,'?action=admin;area=up-news;' . $context['session_var'].'=' . $context['session_id'].'">', $txt['main_news_title'] ,'</a></h5>
						<span class="task">', $txt['main_news_description'] ,'</span>
					</li>';
				}
					echo '
					<li>
						<a href="', $scripturl ,'?action=admin;area=board-news;' . $context['session_var'].'=' . $context['session_id'].'"><img class="home_image png_fix" alt="" src="', $settings['default_theme_url'] ,'/images/ultimate-portal/admin-main/board-news.png" /></a>
						<h5><a href="', $scripturl ,'?action=admin;area=board-news;' . $context['session_var'].'=' . $context['session_id'].'">', $txt['main_bnews_title'] ,'</a></h5>
						<span class="task">', $txt['main_bnews_description'] ,'</span>
					</li>';
				
				if(!empty($ultimateportalSettings['download_enable']))
				{
					echo '				
					<li>
						<a href="', $scripturl ,'?action=admin;area=download;' . $context['session_var'].'=' . $context['session_id'].'"><img class="home_image png_fix" alt="" src="', $settings['default_theme_url'] ,'/images/ultimate-portal/admin-main/download.png" /></a>
						<h5><a href="', $scripturl ,'?action=admin;area=download;' . $context['session_var'].'=' . $context['session_id'].'">', $txt['main_download_title'] ,'</a></h5>
						<span class="task">', $txt['main_download_description'] ,'</span>
					</li>';
				}
				
				if(!empty($ultimateportalSettings['ipage_enable']))
				{				
					echo '
					<li>
						<a href="', $scripturl ,'?action=admin;area=internal-page;' . $context['session_var'].'=' . $context['session_id'].'"><img class="home_image png_fix" alt="" src="', $settings['default_theme_url'] ,'/images/ultimate-portal/admin-main/internal-page.png" /></a>
						<h5><a href="', $scripturl ,'?action=admin;area=internal-page;' . $context['session_var'].'=' . $context['session_id'].'">', $txt['main_ipage_title'] ,'</a></h5>
						<span class="task">', $txt['main_ipage_description'] ,'</span>
					</li>';
				}
				
					echo '
					<li>
						<a href="', $scripturl ,'?action=admin;area=up-affiliates;' . $context['session_var'].'=' . $context['session_id'].'"><img class="home_image png_fix" alt="" src="', $settings['default_theme_url'] ,'/images/ultimate-portal/admin-main/up-affiliates.png" /></a>
						<h5><a href="', $scripturl ,'?action=admin;area=up-affiliates;' . $context['session_var'].'=' . $context['session_id'].'">', $txt['main_affiliates_title'] ,'</a></h5>
						<span class="task">', $txt['main_affiliates_description'] ,'</span>
					</li>';
				
				if(!empty($ultimateportalSettings['about_us_enable']))
				{
					echo '				
					<li>
						<a href="', $scripturl ,'?action=admin;area=up-aboutus;' . $context['session_var'].'=' . $context['session_id'].'"><img class="home_image png_fix" alt="" src="', $settings['default_theme_url'] ,'/images/ultimate-portal/admin-main/up-aboutus.png" /></a>
						<h5><a href="', $scripturl ,'?action=admin;area=up-aboutus;' . $context['session_var'].'=' . $context['session_id'].'">', $txt['main_about_title'] ,'</a></h5>
						<span class="task">', $txt['main_about_description'] ,'</span>
					</li>';
				}
				
				if(!empty($ultimateportalSettings['faq_enable']))
				{
					echo '
					<li>
						<a href="', $scripturl ,'?action=admin;area=up-faq;' . $context['session_var'].'=' . $context['session_id'].'"><img class="home_image png_fix" alt="" src="', $settings['default_theme_url'] ,'/images/ultimate-portal/admin-main/up-faq.png" /></a>
						<h5><a href="', $scripturl ,'?action=admin;area=up-faq;' . $context['session_var'].'=' . $context['session_id'].'">', $txt['main_faq_title'] ,'</a></h5>
						<span class="task">', $txt['main_faq_description'] ,'</span>
					</li>';
				}
				
			echo '
				</ul>
			</div>
		</div><br />';
		
		//Credits
	echo '
	<table width="100%" align="center" cellspacing="1" cellpadding="5" border="0">							
		<tr>
			<td width="100%" class="catbg" align="left">	
				<img width="20" height="20" style="vertical-align:middle" alt="" src="', $settings['default_theme_url'] ,'/images/ultimate-portal/admin-main/credits.png" />&nbsp;', $txt['main_credits_title'] ,'
			</td>
		</tr>
		<tr>
			<td class="windowbg description" width="100%" align="left">	
				', $txt['main_credits_description'] ,'
			</td>
		</tr>
	</table><br/>';
		
}

//Show the Ultimate Portal - Area: Preferences / Section: Gral Settings
function template_preferences_gral_settings()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	
	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=preferences;sa=gral-settings" accept-charset="', $context['character_set'], '">												
		<table width="95%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td class="catbg" width="100%" colspan="2">						
					<img alt="',$txt['ultport_admin_gral_settings_sect_principal'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['ultport_admin_gral_settings_sect_principal'], '
				</td>
			</tr>
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_gral_settings_portal_enable'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="ultimate_portal_enable" ',!empty($ultimateportalSettings['ultimate_portal_enable']) ? 'checked="checked"' : '',' />
				</td>
			</tr>	
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_gral_settings_portal_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="ultimate_portal_home_title" size="50" maxlength="100" ',!empty($ultimateportalSettings['ultimate_portal_home_title']) ? 'value="'.$ultimateportalSettings['ultimate_portal_home_title'].'"' : '','/>
				</td>
			</tr>	
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_gral_settings_favicons'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="checkbox" value="on" name="favicons" ',!empty($ultimateportalSettings['favicons']) ? 'checked="checked"' : '','/>
				</td>
			</tr>	
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_gral_settings_use_curve'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="checkbox" value="on" name="up_use_curve_variation" ',!empty($ultimateportalSettings['up_use_curve_variation']) ? 'checked="checked"' : '','/>
				</td>
			</tr>				
			<tr>
				<td class="catbg" width="100%" colspan="2">
					<img alt="',$txt['ultport_admin_gral_settings_sect_view_portal'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/view-portal.png"/>&nbsp;', $txt['ultport_admin_gral_settings_sect_view_portal'], '
				</td>
			</tr>
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_gral_settings_height_col_left'] , '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="ultimate_portal_width_col_left" size="3" maxlength="4" ',!empty($ultimateportalSettings['ultimate_portal_width_col_left']) ? 'value="'.$ultimateportalSettings['ultimate_portal_width_col_left'].'"' : '','/>
				</td>
			</tr>	
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_gral_settings_height_col_center'] , '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="ultimate_portal_width_col_center" size="3" maxlength="4" ',!empty($ultimateportalSettings['ultimate_portal_width_col_center']) ? 'value="'.$ultimateportalSettings['ultimate_portal_width_col_center'].'"' : '','/>
				</td>
			</tr>	
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_gral_settings_height_col_right'] , '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="ultimate_portal_width_col_right" size="3" maxlength="4" ',!empty($ultimateportalSettings['ultimate_portal_width_col_right']) ? 'value="'.$ultimateportalSettings['ultimate_portal_width_col_right'].'"' : '','/>
				</td>
			</tr>
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_gral_settings_enable_portal_col_left'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="checkbox" value="on" name="ultimate_portal_enable_col_left" ',!empty($ultimateportalSettings['ultimate_portal_enable_col_left']) ? 'checked="checked"' : '','/>
				</td>
			</tr>					
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_gral_settings_enable_portal_col_right'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="checkbox" value="on" name="ultimate_portal_enable_col_right" ',!empty($ultimateportalSettings['ultimate_portal_enable_col_right']) ? 'checked="checked"' : '','/>
				</td>
			</tr>					
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_gral_settings_enable_icons'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="checkbox" value="on" name="ultimate_portal_enable_icons" ',!empty($ultimateportalSettings['ultimate_portal_enable_icons']) ? 'checked="checked"' : '','/>
				</td>
			</tr>					
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_gral_settings_icons_extention'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<select name="ultimate_portal_icons_extention" size="1">
						<option value=".png" ' ,($ultimateportalSettings['ultimate_portal_icons_extention'] == '.png') ? 'selected="selected"' : '','>.png</option>
						<option value=".jpg" ' ,($ultimateportalSettings['ultimate_portal_icons_extention'] == '.jpg') ? 'selected="selected"' : '','>.jpg</option>
						<option value=".gif" ' ,($ultimateportalSettings['ultimate_portal_icons_extention'] == '.gif') ? 'selected="selected"' : '','>.gif</option>
						<option value=".bmp" ' ,($ultimateportalSettings['ultimate_portal_icons_extention'] == '.bmp') ? 'selected="selected"' : '','>.bmp</option>																		
					</select>
				</td>
			</tr>								
			<tr>
				<td class="catbg" width="100%" colspan="2">		
					<img alt="',$txt['ultport_admin_gral_settings_sect_view_forum'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/view-forum.png"/>&nbsp;', $txt['ultport_admin_gral_settings_sect_view_forum'], '
				</td>
			</tr>
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_view_forum_enable_col_left'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="checkbox" value="on" name="up_forum_enable_col_left" ',!empty($ultimateportalSettings['up_forum_enable_col_left']) ? 'checked="checked"' : '','/>
				</td>
			</tr>					
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_view_forum_enable_col_right'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="checkbox" value="on" name="up_forum_enable_col_right" ',!empty($ultimateportalSettings['up_forum_enable_col_right']) ? 'checked="checked"' : '','/>
				</td>
			</tr>	
			<tr>
				<td class="catbg" width="100%" colspan="2">		
					<img alt="',$txt['ultport_exconfig_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/config.png"/>&nbsp;', $txt['ultport_exconfig_title'], '
				</td>
			</tr>	
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_rso_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="checkbox" value="on" name="up_reduce_site_overload" ',!empty($ultimateportalSettings['up_reduce_site_overload']) ? 'checked="checked"' : '','/>
				</td>
			</tr>				
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_collapse_left_right'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="checkbox" value="on" name="up_left_right_collapse" ',!empty($ultimateportalSettings['up_left_right_collapse']) ? 'checked="checked"' : '','/>
				</td>
			</tr>				
		</table>
		<table width="95%" align="center">					
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="save" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: Preferences / Section: Lang Maintenance
function template_preferences_lang_maintenance()
{
	global $context, $scripturl, $txt, $settings;

	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=preferences;sa=lang-edit" accept-charset="', $context['character_set'], '">												
		<table width="95%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_lang_maintenance_admin'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/lang-edit.png"/>&nbsp;', $txt['ultport_admin_lang_maintenance_admin'], '
				</td>
			</tr>
			<tr>
				<td style="background-color:#FFF6BF;" colspan="2" width="100%">									
					<div id="warning">
						<img alt="" style="float:left" width="30" height="30"  border="0" src="'.$settings['default_images_url'].'/ultimate-portal/download/stop.png"/>&nbsp;&nbsp;'. $txt['ultport_admin_lang_maintenance_warning'] .'
					</div>
				</td>
			</tr>	
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_lang_maintenance_admin_edit_language'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					', $context['ult_port_langs'], '
				</td>
			</tr>
		</table>
		<table width="95%" align="center" cellspacing="1" cellpadding="5" border="0">				
			<tr>
				<td align="center" colspan="2">
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="editing" value="',$txt['ultport_button_edit'],'" />
				</td>
			</tr>
		</table>
	</form><br/>';

	//Duplicate Files?
	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=preferences;sa=lang-edit" accept-charset="', $context['character_set'], '">												
		<table width="95%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" colspan="2" class="catbg">						
					<img alt="',$txt['lang_maintenance_duplicate_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/copy.png"/>&nbsp;', $txt['lang_maintenance_duplicate_title'], '
				</td>
			</tr>
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_select_lang_duplicate'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					', $context['ult_port_langs'], '
				</td>
			</tr>
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_lang_duplicate_new'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="new_file" size="40" value="" />
				</td>
			</tr>			
		</table>
		<table width="95%" align="center" cellspacing="1" cellpadding="5" border="0">				
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="duplicate" value="',$txt['ultport_button_add'],'" />
				</td>
			</tr>
		</table>
	</form>';
		
}

//Show the Ultimate Portal - Area: Preferences / Section: Lang Maintenance
function template_preferences_lang_edit()
{
	global $context, $scripturl, $txt, $settings;

	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=preferences;sa=lang-edit" accept-charset="', $context['character_set'], '">												
		<table width="95%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_lang_maintenance_edit_info'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/info.png"/>&nbsp;', $txt['ultport_admin_lang_maintenance_edit_info'], '
				</td>
			</tr>
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_edit_language_file'] ,'
				</td>
				<td width="50%" align="center" class="windowbg2">
					<span style="color:#00883C" ><strong>', $context['file'] ,'</strong></span>
				</td>
			</tr>	
			<tr>
				<td width="100%" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_lang_maintenance_edit'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/lang-edit.png"/>&nbsp;', $txt['ultport_admin_lang_maintenance_edit'], '
				</td>
			</tr>
			<tr>
				<td colspan="2"  width="100%" align="center" class="windowbg2">
					<textarea id="content" name="content" rows="20" cols="80" style="width: 99.2%">', $context['content_htmlspecialchars'] ,'</textarea>
				</td>
			</tr>
			<tr>
				<td class="windowbg" align="center" colspan="2">	
					<input type="hidden" name="file" value="', $context['file'] ,'" />				
					<input type="submit" name="save" value="',$txt['ultport_button_edit'],'" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<a href="', $scripturl, '?action=admin;area=preferences;sa=lang-maintenance"><input type="button" name="',$txt['ultport_button_go_back'],'" value="',$txt['ultport_button_go_back'],'" /></a>
				</td>
			</tr>
		</table>
	</form>';
	
}

//Show the Ultimate Portal - Area: Preferences / Section: Permissions Settings
function template_preferences_permissions_settings()
{
	global $context, $scripturl, $txt, $settings;

	echo	'
	<form method="post" action="', $scripturl, '?action=admin;area=preferences;sa=permissions-settings" accept-charset="', $context['character_set'], '">												
		<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td class="catbg" align="left" colspan="2">	
					<img alt="',$txt['ultport_admin_permissions_settings_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/perm.png"/>&nbsp;', $txt['ultport_admin_permissions_settings_title'], '
				</td>
			</tr>		
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_perms_groups'], '
				</td>			
				<td width="50%" class="windowbg">									
					<select size="1" name="group">';
					foreach ($context['groups'] as $group)
					{	
						echo '
						<option '. (!empty($group['selected']) ? $group['selected'] : '') .' value="'. $group['id_group'] .'">'. $group['group_name'] .'</option>';
					}	
				echo '					
					</select>	
				</td>
			</tr>
		</table>
		<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">							
			<tr>
				<td align="left" colspan="2">	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />											
					<input type="submit" name="view-perms" value="',$txt['ultport_button_edit'],'" />
				</td>
			</tr>
		</table>
	</form><br />';
	
	if (!empty($context['view-perms']))	
	{
		echo	'
		<form method="post" action="', $scripturl, '?action=admin;area=preferences;sa=permissions-settings" accept-charset="', $context['character_set'], '">												
			<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
				<tr>
					<td class="catbg" align="left" colspan="2">
						', $txt['ultport_admin_permissions_settings_subtitle'], '
					</td>
				</tr>';
			
			foreach ($context['permissions'] as $permissions)
			{	
					echo '
				<tr>
					<td align="left" width="50%" class="windowbg">									
						'. $permissions['text-name'] .'
					</td>
					<td align="center" width="50%" class="windowbg">									
						<input type="checkbox" value="on" name="', $permissions['name'] ,'" ', (!empty($context[$permissions['name']]['value']) ? 'checked="checked"' : '') ,'/>
					</td>
				</tr>';
			}	

		echo '					
			</table>
			<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">							
				<tr>
					<td align="left" colspan="2">	
						<input type="hidden" name="group_selected" value="', $context['group-selected'] ,'" />					
						<input type="hidden" name="sc" value="', $context['session_id'], '" />						
						<input type="submit" name="save" value="',$txt['ultport_button_save'],'" />
					</td>
				</tr>
			</table>
		</form><br />';

	}	
	
}

//Show the Ultimate Portal - Area: Preferences / Section: Portal Menu Settings
function template_preferences_main_links()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings;

	echo "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_admin_portal_menu_delet_confirm']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";
	
	echo	'
	<form method="post" action="', $scripturl, '?action=admin;area=preferences;sa=save-portal-menu" accept-charset="', $context['character_set'], '">												
		<table width="95%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td class="catbg" align="left" colspan="8">	
					<img alt="',$txt['ultport_admin_portal_menu_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/link.png"/>&nbsp;', $txt['ultport_admin_portal_menu_title'], '
				</td>
			</tr>		
			<tr>
				<td align="center" width="1%" style="font-size:12px" class="titlebg">									
					', $txt['ultport_admin_mainlinks_icon'], '
				</td>			
				<td align="center" width="15%" style="font-size:12px" class="titlebg">									
					', $txt['ultport_admin_mainlinks_title'], '
				</td>			
				<td align="center" width="60%" style="font-size:12px" class="titlebg">									
					', $txt['ultport_admin_mainlinks_url'], '
				</td>			
				<td align="center" width="1%" style="font-size:12px" class="titlebg">									
					', $txt['ultport_admin_mainlinks_position'], '
				</td>			
				<td align="center" width="1%" style="font-size:12px" class="titlebg">									
					', $txt['ultport_admin_mainlinks_edit'], '
				</td>			
				<td align="center" width="1%" style="font-size:12px" class="titlebg">									
					', $txt['ultport_admin_mainlinks_delete'], '
				</td>			
				<td align="center" width="1%" style="font-size:12px" class="titlebg">									
					', $txt['ultport_admin_mainlinks_active'], '
				</td>			
				<td align="center" width="17%" style="font-size:12px" class="titlebg">									
					', $txt['ultport_admin_mainlinks_top_menu'], '
				</td>			
			</tr>';	
			
		foreach($context['main-links'] as $main_link)
		{	
			echo '
			<tr>
				<td class="',$main_link['activestyle'],'" width="1%" align="center">
					', $main_link['icon'] ,'
				</td>
				<td class="',$main_link['activestyle'],'" width="15%" align="left">
					', $main_link['title'] ,'
				</td>
				<td class="',$main_link['activestyle'],'" width="60%" align="left">
					', $main_link['url'] ,'
				</td>
				<td class="',$main_link['activestyle'],'" width="1%" align="center">
					<input type="text" name="', $main_link['position_form'] ,'" size="4" value="', !empty($main_link['position']) ? $main_link['position'] : '' , '" />
				</td>
				<td class="',$main_link['activestyle'],'" width="1%" align="center">
					<strong><a style="color:blue" href="', $scripturl, '?action=admin;area=preferences;sa=edit-portal-menu;id=', $main_link['id'] ,';' . $context['session_var'].'=', $context['session_id'], '">', $txt['ultport_button_edit'] ,'</a></strong>
				</td>
				<td class="',$main_link['activestyle'],'" width="1%" align="center">
					<strong><a onclick="return makesurelink()" style="color:red" href="', $scripturl, '?action=admin;area=preferences;sa=delete-portal-menu;id=', $main_link['id'] ,';' . $context['session_var'].'=', $context['session_id'], '">', $txt['ultport_button_delete'] ,'</a></strong>
				</td>
				<td class="',$main_link['activestyle'],'" width="1%" align="center">
					<input type="checkbox" name="',  $main_link['active_form'] ,'" value="1" ', $main_link['active'] ,' />
				</td>
				<td class="',$main_link['activestyle'],'" width="17%" align="center">
					<input type="checkbox" name="',  $main_link['top_menu_form'] ,'" value="1" ', $main_link['top_menu'] ,' />
				</td>
			</tr>';				
		}
			
				
	echo '
			<tr>
				<td class="windowbg" align="right" colspan="8">
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="save-menu" value="ok" />						
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>
	</form><br />';

	//Add Main Link
	echo	'
	<form method="post" action="', $scripturl, '?action=admin;area=preferences;sa=add-portal-menu" accept-charset="', $context['character_set'], '">												
		<table style="margin-left:30px" width="50%" align="left" class="tborder" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td class="catbg" align="left" colspan="2">	
					<img alt="',$txt['ultport_admin_portal_menu_add_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/link_add.png"/>&nbsp;', $txt['ultport_admin_portal_menu_add_title'], '
				</td>
			</tr>		
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_mainlinks_icon'], '
				</td>			
				<td width="50%" class="windowbg">									
					<input type="text" name="icon" size="52" />
				</td>			
			</tr>
			<tr>
				<td width="50%" class="windowbg2">									
					', $txt['ultport_admin_mainlinks_title'], '
				</td>			
				<td width="50%" class="windowbg2">									
					<input type="text" name="title" size="52" />
				</td>			
			</tr>			
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_mainlinks_url'], '
				</td>			
				<td width="50%" class="windowbg">									
					<input value="http://" type="text" name="url" size="52" />
				</td>			
			</tr>			
			<tr>
				<td width="50%" class="windowbg2">									
					', $txt['ultport_admin_mainlinks_position'], '
				</td>			
				<td width="50%" class="windowbg2">									
					<input type="text" name="position" value="', $context['last_position'] ,'" size="2" />
				</td>			
			</tr>			
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_mainlinks_active'], '
				</td>			
				<td width="50%" class="windowbg">									
					<input type="checkbox" name="active" value="1" />
				</td>			
			</tr>
			<tr>
				<td class="windowbg2" align="center" colspan="2">	
					<input type="hidden" name="add-menu" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_add'],'" value="',$txt['ultport_button_add'],'" />
				</td>
			</tr>
		</table>
	</form>';
	
	echo '	
	<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
		<tr>
			<td align="left" colspan="6">	
				&nbsp;
			</td>
		</tr>
	</table>';
	
	
}

//Show the Ultimate Portal - Area: Preferences / Section: Edit Portal Menu Settings
function template_preferences_edit_main_links()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings;
	
	//Add Main Link
	echo	'
	<form method="post" action="', $scripturl, '?action=admin;area=preferences;sa=edit-portal-menu" accept-charset="', $context['character_set'], '">												
		<table width="50%" align="center" class="tborder" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td class="catbg" align="left" colspan="2">	
					<img alt="',$txt['ultport_admin_portal_menu_edit_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/link_edit.png"/>&nbsp;', $txt['ultport_admin_portal_menu_edit_title'], '
				</td>
			</tr>';
	
	foreach($context['edit-main-links'] as $edit_main_link)
	{		
	echo	'				
			<tr>
				<td width="30%" class="windowbg">									
					', $txt['ultport_admin_mainlinks_icon'], '
				</td>			
				<td width="70%" class="windowbg">									
					<input type="hidden" name="id" value="', $edit_main_link['id'] ,'" />						
					<input type="text" name="icon" value="', $edit_main_link['icon'] ,'" size="65" />
				</td>			
			</tr>
			<tr>
				<td width="30%" class="windowbg2">									
					', $txt['ultport_admin_mainlinks_title'], '
				</td>			
				<td width="70%" class="windowbg2">									
					<input type="text" name="title" value="', $edit_main_link['title'] ,'" size="65" />
				</td>			
			</tr>			
			<tr>
				<td width="30%" class="windowbg">									
					', $txt['ultport_admin_mainlinks_url'], '
				</td>			
				<td width="70%" class="windowbg">									
					<input type="text" name="url" value="', $edit_main_link['url'] ,'" size="65" />
				</td>			
			</tr>			
			<tr>
				<td width="30%" class="windowbg2">									
					', $txt['ultport_admin_mainlinks_position'], '
				</td>			
				<td width="70%" class="windowbg2">									
					<input type="text" name="position" value="', $edit_main_link['position'] ,'" size="2" />
				</td>			
			</tr>			
			<tr>
				<td width="30%" class="windowbg">									
					', $txt['ultport_admin_mainlinks_active'], '
				</td>			
				<td width="70%" class="windowbg">									
					<input type="checkbox" value="on" name="active" value="1" ', (!empty($edit_main_link['active']) ? 'checked="checked"' : '') ,' />
				</td>			
			</tr>';
	}		
			
	echo 	'		
			<tr>
				<td class="windowbg2" align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_edit'],'" value="',$txt['ultport_button_edit'],'" />
				</td>
			</tr>
		</table>
	</form>';
	
}

//Show the Ultimate Portal - Area: Preferences / Section: SEO
function template_preferences_seo()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings;
	
	//Add Main Link
	echo	'
	<form method="post" action="', $scripturl, '?action=admin;area=preferences;sa=seo" accept-charset="', $context['character_set'], '">												
		<table width="', !empty($context['robots_txt']) ? '82%' : '60%', '" align="center" class="tborder" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td class="catbg" align="left" colspan="2">	
					<img alt="',$txt['ultport_seo_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/config.png"/>&nbsp;', $txt['seo_robots_title'], '
				</td>
			</tr>			
			<tr>
				<td valign="top" width="30%" class="windowbg">									
					', $txt['seo_robots_txt'], '
				</td>
				<td width="70%" class="windowbg">
					<strong>', $txt['seo_robots_added'] ,'</strong><br/>
					<textarea id="robots_add" name="robots_add" rows="20" cols="80">', !empty($context['robots_txt']) ? $context['robots_txt'] : '' ,'</textarea>
				</td>			
			</tr>	
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="save_robot" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table><br />
		<table width="', !empty($context['robots_txt']) ? '82%' : '60%', '" align="center" class="tborder" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td class="catbg" align="left" colspan="2">	
					<img alt="',$txt['ultport_seo_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/config.png"/>&nbsp;', $txt['seo_config'], '
				</td>
			</tr>			
			<tr>
				<td valign="top" align="left" width="50%" class="windowbg">									
					', $txt['seo_title_key_word'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="seo_title_keyword" size="50" maxlength="200" ',!empty($ultimateportalSettings['seo_title_keyword']) ? 'value="'.$ultimateportalSettings['seo_title_keyword'].'"' : '','/>				
				</td>			
			</tr>	
			<tr>
				<td valign="top" align="left" width="50%" class="windowbg">									
					', $txt['seo_google_analytics'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="seo_google_analytics" size="20" maxlength="50" ',!empty($ultimateportalSettings['seo_google_analytics']) ? 'value="'.$ultimateportalSettings['seo_google_analytics'].'"' : '','/>
				</td>			
			</tr>				
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="save_seo_config" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>
		<br />
		<table width="', !empty($context['robots_txt']) ? '82%' : '60%', '" align="center" class="tborder" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td class="catbg" align="left" colspan="2">	
					<img alt="',$txt['seo_google_verification_code_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/config.png"/>&nbsp;', $txt['seo_google_verification_code_title'], '
				</td>
			</tr>			
			<tr>
				<td valign="top" align="left" width="50%" class="windowbg">									
					', $txt['seo_google_verification_code'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="seo_google_verification_code" size="50" maxlength="200" />';
				if(!empty($ultimateportalSettings['seo_google_verification_code']))	
				{
					$verifications_codes = explode(',', $ultimateportalSettings['seo_google_verification_code']);
					$count = count($verifications_codes);
					echo '	
					<div align="left">
						<ul style="list-style-image:none;list-style-position:outside;list-style-type:none;">';
					for($i = 0; $i <= $count; $i++)
					{
						if(!empty($verifications_codes[$i]))
						{
							echo '	
							<li style="background:transparent url(', $settings['default_theme_url'] ,'/images/ultimate-portal/download/menu.png) no-repeat scroll 2px 50%;padding-left:16px;">', $verifications_codes[$i] ,'.html&nbsp;<a href="', $scripturl ,'?action=admin;area=preferences;sa=seo;file=', $verifications_codes[$i] ,';' . $context['session_var'].'=', $context['session_id'] ,'"><img src="', $settings['default_theme_url'] ,'/images/pm_recipient_delete.gif" alt="', $txt['ultport_button_delete'] ,'" title="', $txt['ultport_button_delete'] ,'" /></a></li>';	
						}
					}
					echo '
						</ul>
					</div>';
				}
	echo '				
				</td>			
			</tr>	
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="save_seo_google_verification_code" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>		
	</form>';	
}

function template_mb_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo "
	<script type=\"text/javascript\">
		function makesurelink() {
			if (confirm('".$txt['ultport_mb_delete']."')) {
				return true;
			} else {
				return false;
			}
		}
	</script>";

	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['ultport_mb_title'] ,' - ', $txt['ultport_mb_main'] ,'
			</h3>
		</div>
		<table align="left" width="100%">
			<tr>';
					
			if($context['mb_view'])
			{
				$alternate = true;
				$i = 1;
				$column = 2;
				foreach($context['multiblocks'] as $mb)
				{
					echo '
					<td valign="top" width="33.33%">
						<div class="windowbg', $alternate ? '' : '2' ,'">
							<span class="topslice"><span></span></span>
							<div class="content">
								<span style="font-size:15px;font-weight:bold">', $mb['title'] ,'</span>
								<div class="righttext" style="float:right">
									', $mb['edit'] ,'&nbsp;', $mb['delete'] ,'
								</div>	
							</div>
							<span class="botslice"><span></span></span>
						</div>
					</td>';					
					$alternate = !$alternate;
					if($i == $column+1)
					{
						echo '</tr><tr>';
						$i = 1;
					}
				}
			}else{
				echo '<td></td>';
			}
			echo '
			</tr>
		</table>	
	</div><!-- div admincenter -->
	<br class="clear" />';
}

function template_mb_add()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo '
	<div id="admincenter">
		<form name="mbadd" method="post" action="', $scripturl, '?action=admin;area=multiblock;sa=add" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['ultport_mb_title'] ,' - ', $txt['ultport_mb_add'] ,' - ', $txt['ultport_mb_step'] ,' 1
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							', $txt['ultport_mb_enable'] ,'
						</dt>
						<dd>
							<input type="checkbox" name="enable" value="1" class="input_check" /> 
						</dd>						
						<dt>
							', $txt['ultport_mb_title2'] ,'
						</dt>
						<dd>
							<input type="text" name="title" value="" class="input_text" size="70"/>
						</dd>';
						
						$position = explode('|', $txt['ultport_mb_position']);
						
						echo '
						<dt>
							', $position[0] ,'
						</dt>
						<dd>
							<select id="position" name="position">
								<option value="header">', trim($position[1]) ,'</option>
								<option value="footer">', trim($position[2]) ,'</option>
							</select>
						</dd>
						<dt>
							', $txt['ultport_mb_blocks'] ,'
						</dt>
						<dd>';
					
					foreach($context['blocks'] as $blocks)
					{
						echo '	
							<input type="checkbox" name="block[]" value="', $blocks['id'] ,'" class="input_check" /> ', $blocks['title'],'<br />';
					}
					
					echo '
						</dd>';
					
					$design = explode('|', $txt['ultport_mb_design']);
					echo '	
						<dt>
							', $design[0] ,'
						</dt>
						<dd>
							<div style="width:30%;float:left;text-align:left">
								<input type="radio" value="1-2" name="design"> ', $design[1] ,'
								<br />
								<img alt="" id="design_image" src="', $settings['default_images_url'], '/ultimate-portal/1-row-2-columns.png" width="100" height="100" align="top" />
							</div>
							<div style="width:30%;float:left;text-align:left">
								<input type="radio" value="2-1" name="design"> ', $design[2] ,'
								<br />
								<img alt="" id="design_image" src="', $settings['default_images_url'], '/ultimate-portal/2-rows-1-column.png" width="100" height="100" align="top" />
							</div>
							<div style="width:30%;float:left;text-align:left">
								<input type="radio" value="3-1" name="design"> ', $design[3] ,'
								<br />
								<img alt="" id="design_image" src="', $settings['default_images_url'], '/ultimate-portal/3-rows-1-column.png" width="100" height="100" align="top" />
							</div>
						</dd>';
						
					echo '
					<dt>
						', $txt['ultport_mbk_collapse'] ,'
					</dt>
					<dd>
						<input type="checkbox" name="mbk_collapse" value="1" class="input_check" /> 
					</dd>											
					<dt>
						', $txt['ultport_mbk_style'] ,'
					</dt>
					<dd>
						<input type="checkbox" name="mbk_style" value="1" class="input_check" /> 
					</dd>											
					<dt>
						', $txt['ultport_mbk_title'] ,'
					</dt>
					<dd>
						<input type="checkbox" name="mbk_title" value="1" class="input_check" /> 
					</dd>											
					</dl>
					<div class="righttext">
						<input type="hidden" name="step" value="1" />
						<input type="hidden" name="sc" value="', $context['session_id'] ,'" />
						<input type="submit" name="next" value="', $txt['ultport_mb_next'], '" class="button_submit" />
					</div>
				</div><!-- div content -->
				<span class="botslice"><span></span></span>
			</div><!-- div windowbg2 -->
		</form>	
	</div><!-- div admincenter -->
	<br class="clear">';
}

function template_mb_add_1()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo '
	<div id="admincenter">
		<form name="mbadd" method="post" action="', $scripturl, '?action=admin;area=multiblock;sa=add" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['ultport_mb_title'] ,' - ', $txt['ultport_mb_add'] ,' - ', $txt['ultport_mb_step'] ,' 2				
				</h3>
			</div>
			<div>
				<span class="topslice"><span></span></span>
				<div class="content">
					', $txt['ultport_mb_organization'] ,'
				</div><!-- div content -->
				<span class="botslice"><span></span></span>
			</div><!-- div windowbg2 -->
			<table align="center" width="80%">
				<tr>';
					$bk_selected = explode(',', $context['id_blocks']);
					$alternate = true;
					$i = 1; //flag
					$column = 2;
					foreach($context['blocks'] as $blocks)
					{
						if (in_array($blocks['id'], $bk_selected))
						{
							echo '
							<td width="50%" align="top">	
								<div class="windowbg', $alternate ? '' : '2' ,'">
									<span class="topslice"><span></span></span>
									<div class="content">
										<span style="font-weight:bold;font-size:15px;">', $blocks['title'],'</span>
										<br />
										',$txt['ultport_mbk_position'],'&nbsp;&nbsp;';
										//1 Row 2 Columns
										if($context['design'] == '1-2')
										{
											echo '
											<input type="radio" value="c1" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_column'] ,' 1
											&nbsp;&nbsp;
											<input type="radio" value="c2" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_column'] ,' 2';									
										}
										
										//2 Rows 1 Column
										if ($context['design'] == '2-1')
										{
											echo '
											<input type="radio" value="r1" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_row'] ,' 1
											&nbsp;&nbsp;
											<input type="radio" value="r2" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_row'] ,' 2';
										}
										
										//3 Rows 1 Column
										if ($context['design'] == '3-1')
										{
											echo '
											<input type="radio" value="r1" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_row'] ,' 1
											&nbsp;&nbsp;
											<input type="radio" value="r2" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_row'] ,' 2
											&nbsp;&nbsp;
											<input type="radio" value="r3" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_row'] ,' 3';
										}										
									echo '
									</div>
									<span class="botslice"><span></span></span>
								</div>
							</td>';
							$alternate = !$alternate;
							$i++;
							if ($i==$column+1)
							{
								echo '</tr><tr>';
								$i=1;
							} 							
						}
					}
					echo '
				</tr>
			</table>
			<div class="righttext">
				<input type="hidden" name="title" value="', $context['title'] ,'" />
				<input type="hidden" name="position" value="', $context['position'] ,'" />
				<input type="hidden" name="mbk_title" value="', $context['mbk_title'] ,'" />
				<input type="hidden" name="mbk_collapse" value="', $context['mbk_collapse'] ,'" />
				<input type="hidden" name="mbk_style" value="', $context['mbk_style'] ,'" />
				<input type="hidden" name="enable" value="', $context['enable'] ,'" />
				<input type="hidden" name="blocks" value="',  $context['id_blocks'] ,'" />
				<input type="hidden" name="design" value="',  $context['design'] ,'" />
				<input type="hidden" name="sc" value="', $context['session_id'] ,'" />
				<input type="submit" name="save" value="', $txt['ultport_button_save'], '" class="button_submit" />
			</div>
		</form>	
	</div><!-- div admincenter -->
	<br class="clear">';
}

function template_mb_edit()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo '
	<div id="admincenter">
		<form name="mbedit" method="post" action="', $scripturl, '?action=admin;area=multiblock;sa=edit;id=', $context['idmbk'] ,'" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['ultport_mb_title'] ,' - ', $txt['ultport_mb_edit'] ,' - ', $context['multiblocks'][$context['idmbk']]['title'] ,' - ', $txt['ultport_mb_step'] ,' 1
				</h3>
			</div>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							', $txt['ultport_mb_enable'] ,'
						</dt>
						<dd>
							<input type="checkbox" name="enable" value="1" class="input_check" ', !empty($context['multiblocks'][$context['idmbk']]['enable']) ? 'checked="checked"' : '' ,'/> 
						</dd>						
						<dt>
							', $txt['ultport_mb_title2'] ,'
						</dt>
						<dd>
							<input type="text" name="title" value="', $context['multiblocks'][$context['idmbk']]['title'] ,'" class="input_text" size="70"/>
						</dd>';
						
						$position = explode('|', $txt['ultport_mb_position']);
						
						echo '
						<dt>
							', $position[0] ,'
						</dt>
						<dd>
							<select id="position" name="position">
								<option ', $context['multiblocks'][$context['idmbk']]['position']=='header' ? 'selected="selected"' : '' ,' value="header">', trim($position[1]) ,'</option>
								<option ', $context['multiblocks'][$context['idmbk']]['position']=='footer' ? 'selected="selected"' : '' ,' value="footer">', trim($position[2]) ,'</option>
							</select>
						</dd>
						<dt>
							', $txt['ultport_mb_blocks'] ,'
						</dt>
						<dd>';
					
					$id_blocks = explode(',',$context['multiblocks'][$context['idmbk']]['blocks']);
					foreach($context['blocks'] as $blocks)
					{
						echo '	
							<input ', in_array($blocks['id'], $id_blocks) ? 'checked="checked"' : '' ,' type="checkbox" name="block[]" value="', $blocks['id'] ,'" class="input_check" /> ', $blocks['title'],'<br />';
					}
					
					echo '
						</dd>';
					
					$design = explode('|', $txt['ultport_mb_design']);
					echo '	
						<dt>
							', $design[0] ,'
						</dt>
						<dd>
							<div style="width:30%;float:left;text-align:left">
								<input ', trim($context['multiblocks'][$context['idmbk']]['design'])=='1-2' ? 'checked="checked"' : '' ,' type="radio" value="1-2" name="design"> ', $design[1] ,'
								<br />
								<img alt="" id="design_image" src="', $settings['default_images_url'], '/ultimate-portal/1-row-2-columns.png" width="100" height="100" align="top" />
							</div>
							<div style="width:30%;float:left;text-align:left">
								<input ', trim($context['multiblocks'][$context['idmbk']]['design'])=='2-1' ? 'checked="checked"' : '' ,' type="radio" value="2-1" name="design"> ', $design[2] ,'
								<br />
								<img alt="" id="design_image" src="', $settings['default_images_url'], '/ultimate-portal/2-rows-1-column.png" width="100" height="100" align="top" />
							</div>
							<div style="width:30%;float:left;text-align:left">
								<input ', trim($context['multiblocks'][$context['idmbk']]['design'])=='3-1' ? 'checked="checked"' : '' ,' type="radio" value="3-1" name="design"> ', $design[3] ,'
								<br />
								<img alt="" id="design_image" src="', $settings['default_images_url'], '/ultimate-portal/3-rows-1-column.png" width="100" height="100" align="top" />
							</div>
						</dd>';
						
					echo '
					<dt>
						', $txt['ultport_mbk_collapse'] ,'
					</dt>
					<dd>
						<input ', !empty($context['multiblocks'][$context['idmbk']]['mbk_collapse']) ? 'checked="checked"' : '' ,' type="checkbox" name="mbk_collapse" value="1" class="input_check" /> 
					</dd>											
					<dt>
						', $txt['ultport_mbk_style'] ,'
					</dt>
					<dd>
						<input ', !empty($context['multiblocks'][$context['idmbk']]['mbk_style']) ? 'checked="checked"' : '' ,' type="checkbox" name="mbk_style" value="1" class="input_check" /> 
					</dd>											
					<dt>
						', $txt['ultport_mbk_title'] ,'
					</dt>
					<dd>
						<input ', !empty($context['multiblocks'][$context['idmbk']]['mbk_title']) ? 'checked="checked"' : '' ,' type="checkbox" name="mbk_title" value="1" class="input_check" /> 
					</dd>											
					</dl>
					<div class="righttext">
						<input type="hidden" name="step" value="1" />
						<input type="hidden" name="sc" value="', $context['session_id'] ,'" />
						<input type="submit" name="next" value="', $txt['ultport_mb_next'], '" class="button_submit" />
					</div>
				</div><!-- div content -->
				<span class="botslice"><span></span></span>
			</div><!-- div windowbg2 -->
		</form>	
	</div><!-- div admincenter -->
	<br class="clear">';
}

function template_mb_edit_1()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo '
	<div id="admincenter">
		<form name="mbadd" method="post" action="', $scripturl, '?action=admin;area=multiblock;sa=edit;id=', $context['idmbk'] ,'" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['ultport_mb_title'] ,' - ', $txt['ultport_mb_add'] ,' - ', $txt['ultport_mb_step'] ,' 2				
				</h3>
			</div>
			<div>
				<span class="topslice"><span></span></span>
				<div class="content">
					', $txt['ultport_mb_organization'] ,'
				</div><!-- div content -->
				<span class="botslice"><span></span></span>
			</div><!-- div windowbg2 -->
			<table align="center" width="80%">
				<tr>';
					$bk_selected = explode(',', $context['id_blocks']);
					$alternate = true;
					$i = 1; //flag
					$column = 2;
					foreach($context['blocks'] as $blocks)
					{
						if (in_array($blocks['id'], $bk_selected))
						{
							echo '
							<td width="50%" align="top">	
								<div ', !empty($context['oblocks'][$blocks['id']]['mbk_view']) ? ('class="windowbg'. ($alternate ? '' : '2')) : 'class="information' ,'">
									<span class="topslice"><span></span></span>
									<div class="content">
										<span style="font-weight:bold;font-size:15px;">', $blocks['title'],'</span>
										<br />
										',$txt['ultport_mbk_position'],'&nbsp;&nbsp;';
										//1 Row 2 Columns
										if($context['design'] == '1-2')
										{
											echo '
											<input ', !empty($context['oblocks'][$blocks['id']]['mbk_view']) ? ($context['oblocks'][$blocks['id']]['mbk_view']=='c1' ? 'checked="checked"' : '') : '' ,' type="radio" value="c1" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_column'] ,' 1
											&nbsp;&nbsp;
											<input ', !empty($context['oblocks'][$blocks['id']]['mbk_view']) ? ($context['oblocks'][$blocks['id']]['mbk_view']=='c2' ? 'checked="checked"' : '') : '' ,' type="radio" value="c2" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_column'] ,' 2';									
										}
										
										//2 Rows 1 Column
										if ($context['design'] == '2-1')
										{
											echo '
											<input ', !empty($context['oblocks'][$blocks['id']]['mbk_view']) ? ($context['oblocks'][$blocks['id']]['mbk_view']=='r1' ? 'checked="checked"' : '') : '' ,' type="radio" value="r1" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_row'] ,' 1
											&nbsp;&nbsp;
											<input ', !empty($context['oblocks'][$blocks['id']]['mbk_view']) ? ($context['oblocks'][$blocks['id']]['mbk_view']=='r2' ? 'checked="checked"' : '') : '' ,' type="radio" value="r2" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_row'] ,' 2';
										}
										
										//3 Rows 1 Column
										if ($context['design'] == '3-1')
										{
											echo '
											<input ', !empty($context['oblocks'][$blocks['id']]['mbk_view']) ? ($context['oblocks'][$blocks['id']]['mbk_view']=='r1' ? 'checked="checked"' : '') : '' ,' type="radio" value="r1" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_row'] ,' 1
											&nbsp;&nbsp;
											<input ', !empty($context['oblocks'][$blocks['id']]['mbk_view']) ? ($context['oblocks'][$blocks['id']]['mbk_view']=='r2' ? 'checked="checked"' : '') : '' ,' type="radio" value="r2" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_row'] ,' 2
											&nbsp;&nbsp;
											<input ', !empty($context['oblocks'][$blocks['id']]['mbk_view']) ? ($context['oblocks'][$blocks['id']]['mbk_view']=='r3' ? 'checked="checked"' : '') : '' ,' type="radio" value="r3" name="mbk_view_', $blocks['id'] ,'"> ', $txt['ultport_mb_row'] ,' 3';
										}										
									echo '
									</div>
									<span class="botslice"><span></span></span>
								</div>
							</td>';
							$alternate = !$alternate;
							$i++;
							if ($i==$column+1)
							{
								echo '</tr><tr>';
								$i=1;
							} 							
						}
					}
					echo '
				</tr>
			</table>
			<div class="righttext">
				<input type="hidden" name="title" value="', $context['title'] ,'" />
				<input type="hidden" name="position" value="', $context['position'] ,'" />
				<input type="hidden" name="mbk_title" value="', $context['mbk_title'] ,'" />
				<input type="hidden" name="mbk_collapse" value="', $context['mbk_collapse'] ,'" />
				<input type="hidden" name="mbk_style" value="', $context['mbk_style'] ,'" />
				<input type="hidden" name="enable" value="', $context['enable'] ,'" />
				<input type="hidden" name="blocks" value="',  $context['id_blocks'] ,'" />
				<input type="hidden" name="design" value="',  $context['design'] ,'" />
				<input type="hidden" name="sc" value="', $context['session_id'] ,'" />
				<input type="submit" name="back" value="', $txt['ultport_button_go_back'], '" class="button_submit" />&nbsp;
				<input type="submit" name="save" value="', $txt['ultport_button_save'], '" class="button_submit" />
			</div>
		</form>	
	</div><!-- div admincenter -->
	<br class="clear">';
}

?>