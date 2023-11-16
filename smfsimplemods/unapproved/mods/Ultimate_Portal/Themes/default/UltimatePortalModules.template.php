<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

// Turn on and off certain key features.
function template_enableModules_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo '
	<script type="text/javascript"><!-- // --><![CDATA[
		function toggleItem(itemID)
		{
			// Toggle the hidden item.
			var itemValueHandle = document.getElementById(itemID);
			itemValueHandle.value = itemValueHandle.value == 1 ? 0 : 1;

			// Change the image, alternative text and the title.
			document.getElementById("switch_" + itemID).src = \'', $settings['images_url'], '/admin/switch_\' + (itemValueHandle.value == 1 ? \'on\' : \'off\') + \'.png\';
			document.getElementById("switch_" + itemID).alt = itemValueHandle.value == 1 ? \'', $txt['core_settings_switch_off'], '\' : \'', $txt['core_settings_switch_on'], '\';
			document.getElementById("switch_" + itemID).title = itemValueHandle.value == 1 ? \'', $txt['core_settings_switch_off'], '\' : \'', $txt['core_settings_switch_on'], '\';

			// Don\'t reload.
			return false;
		}
	// ]]></script>';

	echo '
	<div id="admincenter">
		<form method="post" action="', $scripturl, '?action=admin;area=upmodulesenable" accept-charset="', $context['character_set'], '">
			<div class="title_bar">
				<h3 class="titlebg">
					', $txt['ultport_enablemodules_title'] ,'
				</h3>
			</div>';

	$alternate = true;
	foreach ($context['modules'] as $id => $modules)
	{
		echo '
			<div class="windowbg', $alternate ? '2' : '', '">
				<span class="topslice"><span></span></span>
				<div class="content features">
					<img class="features_image png_fix" src="', $settings['default_images_url'], '/ultimate-portal/admin-main/', $modules['images'], '" alt="" />
					<div class="features_switch" id="js_feature_', $id, '" style="display: none;">
						<a href="', $scripturl, '?action=admin;area=upmodulesenable;', $context['session_var'], '=', $context['session_id'], ';toggle=', $id, ';state=', $modules['enabled'] ? 0 : 1, '" onclick="return toggleItem(\'', $id, '\');">
							<input type="hidden" name="', $id, '" id="', $id, '" value="', $modules['enabled'] ? 'on' : '', '" /><img src="', $settings['images_url'], '/admin/switch_', $modules['enabled'] ? 'on' : 'off', '.png" id="switch_', $id, '" style="margin-top: 1.3em;" alt="', $txt['core_settings_switch_' . ($modules['enabled'] ? 'off' : 'on')], '" title="', $txt['core_settings_switch_' . ($modules['enabled'] ? 'off' : 'on')], '" />
						</a>
					</div>
					<h4>', ($modules['enabled'] && $modules['url'] ? '<a href="' . $modules['url'] . '">' . $modules['title'] . '</a>' : $modules['title']), '</h4>
					<p>', $modules['desc'], '</p>
					<div id="plain_feature_', $id, '">
						<label for="plain_feature_', $id, '_radio_on"><input type="radio" name="feature_plain_', $id, '" id="plain_feature_', $id, '_radio_on" value="1"', $modules['enabled'] ? ' checked="checked"' : '', ' class="input_radio" />', $txt['core_settings_enabled'], '</label>
						<label for="plain_feature_', $id, '_radio_off"><input type="radio" name="feature_plain_', $id, '" id="plain_feature_', $id, '_radio_off" value="0"', !$modules['enabled'] ? ' checked="checked"' : '', ' class="input_radio" />', $txt['core_settings_disabled'], '</label>
					</div>
				</div>
				<span class="botslice clear_right"><span></span></span>
			</div>';

		$alternate = !$alternate;
	}

	echo '
			<div class="righttext">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="hidden" value="0" name="js_worked" id="js_worked" />
				<input type="submit" value="', $txt['save'], '" name="save" class="button_submit" />
			</div>
		</form>
	</div>
	<br class="clear" />';

	// Turn on the pretty javascript if we can!
	echo '
	<script type="text/javascript"><!-- // --><![CDATA[
		document.getElementById(\'js_worked\').value = "1";';
		foreach ($context['modules'] as $id => $feature)
			echo '
		document.getElementById(\'js_feature_', $id, '\').style.display = "";
		document.getElementById(\'plain_feature_', $id, '\').style.display = "none";';
	echo '
	// ]]></script>';

}

//Show the Ultimate Portal - Area: User Posts / Section: Gral Settings
function template_user_posts_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=user-posts;sa=up-main" accept-charset="', $context['character_set'], '">												
		<table width="80%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_user_posts_main'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['ultport_admin_user_posts_main'], '
				</td>
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_up_limit'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="user_posts_limit" size="3" maxlength="3" ',!empty($ultimateportalSettings['user_posts_limit']) ? 'value="'.$ultimateportalSettings['user_posts_limit'].'"' : 'value="10"','/>
				</td>
			</tr>	
			<tr>
				<td width="50%" valign="top" class="windowbg">									
					', $txt['ultport_admin_up_fields'], '
				</td>
				<td width="50%" valign="top" align="left" class="windowbg2">
					<input type="checkbox" value="on" name="user_posts_field_title" checked="checked" disabled="disabled"/>&nbsp;', $txt['ultport_admin_up_field_title'] ,'
					<br />
					<input type="checkbox" value="on" name="user_posts_field_cover" ',!empty($ultimateportalSettings['user_posts_field_cover']) ? 'checked="checked"' : '',' />&nbsp;', $txt['ultport_admin_up_field_cover'] ,'
					<br />
					<input type="checkbox" value="on" name="user_posts_field_description" ',!empty($ultimateportalSettings['user_posts_field_description']) ? 'checked="checked"' : '',' />&nbsp;', $txt['ultport_admin_up_field_description'] ,'
					<br />
					<input type="checkbox" value="on" name="user_posts_field_link_topic" checked="checked" disabled="disabled"/>&nbsp;', $txt['ultport_admin_up_field_link_topic'] ,'
					<br />
					<input type="checkbox" value="on" name="user_posts_field_topic_author" ',!empty($ultimateportalSettings['user_posts_field_topic_author']) ? 'checked="checked"' : '',' />&nbsp;', $txt['ultport_admin_up_field_topic_author'] ,'
					<br />
					<input type="checkbox" value="on" name="user_posts_field_member_use_module" ',!empty($ultimateportalSettings['user_posts_field_member_use_module']) ? 'checked="checked"' : '',' />&nbsp;', $txt['ultport_admin_up_field_member_use_module'] ,'
					<br />
					<input type="checkbox" value="on" name="user_posts_field_member_updated_module" ',!empty($ultimateportalSettings['user_posts_field_member_updated_module']) ? 'checked="checked"' : '',' />&nbsp;', $txt['ultport_admin_up_field_member_updated_module'] ,'
					<br />
					<input type="checkbox" value="on" name="user_posts_field_type_posts" ',!empty($ultimateportalSettings['user_posts_field_type_posts']) ? 'checked="checked"' : '',' />&nbsp;', $txt['ultport_admin_up_field_type_posts'] ,'
					<br />
					<input type="checkbox" value="on" name="user_posts_field_add_language" ',!empty($ultimateportalSettings['user_posts_field_add_language']) ? 'checked="checked"' : '',' />&nbsp;', $txt['ultport_admin_up_field_add_language'] ,'
				</td>
			</tr>';
		
		//ONLY IF THE "COVER" FIELD, IS ENABLED, THIS PART APPEARS
		//SOLO SI EL CAMPO COVER ESTA ACTIVADO, APARECERA ESTA PARTE
		if (!empty($ultimateportalSettings['user_posts_field_cover']))
		{
		echo '			
			<tr>
				<td width="50%" valign="top" class="windowbg">									
					', $txt['ultport_admin_up_cover_save_host'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="user_posts_cover_save_host" ',!empty($ultimateportalSettings['user_posts_cover_save_host']) ? 'checked="checked"' : '',' />
				</td>
			</tr>';
		}

		//ONLY IF THE "COVER" AND "DESCRIPTION" FIELDS, IS ENABLED, THIS PART APPEARS
		//SOLO SI LOS CAMPOS COVER Y DESCRIPTION ESTAN ACTIVADOS, APARECERA ESTA PARTE
		if (!empty($ultimateportalSettings['user_posts_field_cover']) && !empty($ultimateportalSettings['user_posts_field_description']))
		{
		echo '			
			<tr>
				<td width="50%" valign="top" class="windowbg">									
					', $txt['ultport_admin_up_internal_page_presentation'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="user_posts_internal_page" ',!empty($ultimateportalSettings['user_posts_internal_page']) ? 'checked="checked"' : '',' />
				</td>
			</tr>';
		}
		
		echo '	
			<tr>
				<td width="50%" valign="top" class="windowbg">									
					', $txt['ultport_admin_up_presentation'], '
				</td>
				<td width="50%" valign="top" align="left" class="windowbg2">
					<table width="100%" align="center" cellspacing="1" cellpadding="5" border="0">
						<tr>
							<td width="50%" align="center" valign="top" class="windowbg2">									
								<input ', (($ultimateportalSettings['user_posts_cover_view'] == 'normal') ? 'checked="checked"' : '') ,' type="radio" name="user_posts_cover_view" value="normal" >&nbsp;', $txt['ultport_admin_up_normal'] ,'
								<br /><br />
								<a href="http://www.smfsimple.com/img/ultimateportal/user-posts/up-normal.jpg" target="_blank"><img width="150" height="150" alt="',$txt['ultport_admin_up_normal'],'" border="0" src="http://www.smfsimple.com/img/ultimateportal/user-posts/up-normal.jpg"/></a>
							</td>
							<td width="50%" align="center" valign="top" class="windowbg2">									
								<input ', (($ultimateportalSettings['user_posts_cover_view'] == 'advanced') ? 'checked="checked"' : '') ,' type="radio" name="user_posts_cover_view" value="advanced" >&nbsp;', $txt['ultport_admin_up_advanced'] ,'
								<br /><br />
								<a href="http://www.smfsimple.com/img/ultimateportal/user-posts/up-advanced.jpg" target="_blank"><img width="150" height="150" alt="',$txt['ultport_admin_up_advanced'],'" border="0" src="http://www.smfsimple.com/img/ultimateportal/user-posts/up-advanced.jpg"/></a>
							</td>
						</tr>
					</table>			
				</td>
			</tr>';

		//ONLY IF THE "ADVANCED" VIEW COVER FIELD, IS SELECTED, THIS PART APPEARS
		//SOLO SI EL CAMPO VISTA AVANZADA DE LA CARATULA ESTA SELECCIONADO, ESTA PARTE APARECE
		if ($ultimateportalSettings['user_posts_cover_view'] == 'advanced')
		{
		echo '			
			<tr>
				<td width="50%" valign="top" class="windowbg">									
					', $txt['ultport_admin_up_cover_watermark'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="ultimate_portal_cover_watermark" size="50" maxlength="100" ',!empty($ultimateportalSettings['ultimate_portal_cover_watermark']) ? 'value="'.$ultimateportalSettings['ultimate_portal_cover_watermark'].'"' : '','/>
				</td>
			</tr>';
		}
		//ONLY IF THE "COVER" FIELD, IS ENABLED, THIS PART APPEARS
		//SOLO SI EL CAMPO COVER  ESTA ACTIVADO, APARECERA ESTA PARTE
		if (!empty($ultimateportalSettings['user_posts_field_cover']))
		{
		echo '
			<tr>
				<td width="50%" valign="top" class="windowbg">									
					', $txt['ultport_admin_up_header_show'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="user_posts_header_show" ',!empty($ultimateportalSettings['user_posts_header_show']) ? 'checked="checked"' : '',' />
				</td>
			</tr>';	
		}
		echo '	
			<tr>
				<td width="50%" valign="top" class="windowbg">									
					', $txt['ultport_admin_up_social_bookmarks'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="user_posts_social_bookmarks" ',!empty($ultimateportalSettings['user_posts_social_bookmarks']) ? 'checked="checked"' : '',' />
				</td>
			</tr>		
		</table>
		<table width="80%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: User Post / Section: Add Extra Field
function template_up_extra_field()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";

	if (!empty($ultimateportalSettings['user_posts_field_type_posts']) || !empty($ultimateportalSettings['user_posts_field_add_language']))
	{
		if (!empty($context['view']))
		{
			echo'
			<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
				<tr>
					<td width="1%" align="center" class="catbg">									
						', $txt['ultport_admin_extra_field_id'], '
					</td>
					<td width="9%" align="center" class="catbg">									
						', $txt['ultport_admin_extra_field_icon'], '
					</td>
					<td width="40%" align="center" class="catbg">									
						', $txt['ultport_admin_extra_field_title'], '
					</td>
					<td width="50%" align="left" colspan="2" class="catbg">									
						', $txt['ultport_admin_extra_field_action'], '
					</td>
				</tr>';
			foreach ($context['up-extfield'] as $extfield)	
			{
				echo '					
				<tr>
					<td width="1%" align="center" class="windowbg">									
						', $extfield['id'] ,'
					</td>
					<td width="9%" align="center" class="windowbg">									
						', $extfield['icon'] ,'
					</td>
					<td width="40%" align="left" class="windowbg">									
						', $extfield['title'] ,' 
						<br /><em>', $extfield['field'] ,'</em>
					</td>
					<td width="25%" align="center" class="windowbg">									
						<strong>', $extfield['edit'] ,'</strong>
					</td>
					<td width="25%" align="center" class="windowbg">									
						<strong>', $extfield['delete'] ,'</strong>
					</td>
				</tr>';
			}
			echo '
			</table>';
		}else{
			echo '	
			<table width="70%" class="tborder" align="center" cellspacing="1" cellpadding="5" border="0">
				<tr>
					<td class="catbg" align="left" colspan="6">	
						<strong>', $txt['ultport_no_rows_title'] ,'</strong>
					</td>
				</tr>
				<tr>
					<td class="windowbg" align="center" colspan="6">	
						<strong>', $txt['ultport_no_rows'] ,'</strong>
					</td>
				</tr>
			</table>';
		}
	}else{
		echo '	
		<table width="70%" class="tborder" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td class="catbg" align="left" colspan="6">	
					<strong>', $txt['ultport_no_rows_title'] ,'</strong>
				</td>
			</tr>
			<tr>
				<td class="windowbg" align="center" colspan="6">	
					<strong>', $txt['ultport_no_activate_extra_field'] ,'</strong>
				</td>
			</tr>
		</table>';	
	}	
	
	echo '	
	<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
		<tr>
			<td align="left" colspan="6">	
				<img style="float:left" alt="',$txt['ultport_button_add'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png"/>&nbsp;
				<a href="', $scripturl ,'?action=admin;area=user-posts;sa=add-extra-field;sesc=' . $context['session_id'].'"><strong>', $txt['ultport_button_add'] ,'</strong></a>
			</td>
		</tr>
	</table>';

}

//Show the Ultimate Portal - Area: Extra Field / Section: Add Extra Field
function template_add_extra_field()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;


	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=user-posts;sa=add-extra-field" accept-charset="', $context['character_set'], '">												
		<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_up_extra_field_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png"/>&nbsp;', $txt['ultport_admin_up_extra_field_title'], '
				</td>
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_extra_field_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="title" size="50" maxlength="100" />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_extra_field_icon'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="icon" size="50" maxlength="100" />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_extra_field_selectfield'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<select name="field" size="1">';
					if (!empty($ultimateportalSettings['user_posts_field_type_posts']))
					echo '
						<option value="type" >', $txt['ultport_admin_extra_field_type'] ,'</option>';
						
					if (!empty($ultimateportalSettings['user_posts_field_add_language']))						
					echo '	
						<option value="lang" >', $txt['ultport_admin_extra_field_lang'] ,'</option>';
						
					echo '	
					</select>
				</td>
			</tr>				
		</table>
		<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_add'],'" value="',$txt['ultport_button_add'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: Extra Field / Section: Add Extra Field
function template_edit_extra_field()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;


	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=user-posts;sa=edit-extra-field" accept-charset="', $context['character_set'], '">												
		<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_up_extra_field_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png"/>&nbsp;', $txt['ultport_admin_up_extra_field_title'], '
				</td>
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_extra_field_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="title" size="50" maxlength="100" value="', $context['title'] ,'"/>
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_extra_field_icon'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="icon" size="50" maxlength="100" value="', $context['icon'] ,'"/>
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_extra_field_selectfield'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<select name="field" size="1">';
					if (!empty($ultimateportalSettings['user_posts_field_type_posts']))
					echo '
						<option value="type" ', ($context['field'] == 'type') ? 'selected="selected"' : '','>', $txt['ultport_admin_extra_field_type'] ,'</option>';
						
					if (!empty($ultimateportalSettings['user_posts_field_add_language']))						
					echo '	
						<option value="lang" ', ($context['field'] == 'lang') ? 'selected="selected"' : '','>', $txt['ultport_admin_extra_field_lang'] ,'</option>';
						
					echo '	
					</select>				
				</td>
			</tr>				
		</table>
		<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="id" value="', $context['id'] ,'" />						
					<input type="hidden" name="save" value="ok" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_edit'],'" value="',$txt['ultport_button_edit'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: News / Section: Gral Settings
function template_news_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;


	echo'
	<form name="newsform" method="post" action="', $scripturl, '?action=admin;area=up-news;sa=ns-main" accept-charset="', $context['character_set'], '">												
		<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_news_main_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['ultport_admin_news_main_title'], '
				</td>
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_news_limit'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="up_news_limit" size="3" maxlength="3" ',!empty($ultimateportalSettings['up_news_limit']) ? 'value="'.$ultimateportalSettings['up_news_limit'].'"' : '','/>
				</td>
			</tr>	
		</table>
		<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: News / Section: Announcement
function template_announcement()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;


	echo'
	<form name="newsform" method="post" action="', $scripturl, '?action=admin;area=up-news;sa=announcements" accept-charset="', $context['character_set'], '">												
		<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" valign="top" align="left" class="catbg">									
					<img alt="" style="vertical-align: middle;" width="16" height="16" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/information.png"/>
					', $txt['ultport_admin_announcements_title'], '
				</td>
			</tr>	
			<tr>
				<td width="100%" valign="top" align="left" class="windowbg">									
					', $txt['ultport_global_annoucements'], '
				</td>
			</tr>	
			<tr>
				<td width="100%" align="left" class="windowbg2">
					<div id="bbcBox_message"></div>
					<div id="smileyBox_message"></div>
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'),'
				</td>
			</tr>	
		</table>
		<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: News / Section: News Section
function template_news_section()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_section_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";

	echo'
	<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
		<tr>
			<td width="1%" align="center" class="catbg">									
				', $txt['ultport_admin_news_sect_id'], '
			</td>
			<td width="8%" align="center" class="catbg">									
				', $txt['ultport_admin_news_sect_icon'], '
			</td>
			<td width="40%" align="center" class="catbg">									
				', $txt['ultport_admin_news_sect_title'], '
			</td>
			<td width="1%" align="center" class="catbg">									
				', $txt['ultport_admin_news_sect_position'], '
			</td>
			<td width="50%" align="left" colspan="2" class="catbg">									
				', $txt['ultport_admin_news_sect_action'], '
			</td>
		</tr>';
	if(!empty($context['news_rows']))	
	{
		foreach ($context['news-section'] as $section)	
		{
			echo '					
			<tr>
				<td width="1%" align="center" class="windowbg">									
					', $section['id'] ,'
				</td>
				<td width="8%" align="center" class="windowbg">									
					', $section['icon'] ,'
				</td>
				<td width="40%" align="left" class="windowbg">									
					', $section['title'] ,'
				</td>
				<td width="1%" align="center" class="windowbg">									
					', $section['position'] ,'
				</td>
				<td width="25%" align="center" class="windowbg">									
					<strong>', $section['edit'] ,'</strong>
				</td>
				<td width="25%" align="center" class="windowbg">									
					<strong>', $section['delete'] ,'</strong>
				</td>
			</tr>';
		}
	}
	echo '	
	</table>
	<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
		<tr>
			<td align="left" colspan="6">	
				<img style="float:left" alt="',$txt['ultport_button_add'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png"/>&nbsp;
				<a href="', $scripturl ,'?action=admin;area=up-news;sa=add-section;sesc=' . $context['session_id'].'"><strong>', $txt['ultport_button_add'] ,'</strong></a>
			</td>
		</tr>
	</table>';

}

//Show the Ultimate Portal - Area: News / Section: Add Section
function template_add_news_section()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;


	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=up-news;sa=add-section" accept-charset="', $context['character_set'], '">												
		<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_add_sect_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png"/>&nbsp;', $txt['ultport_admin_add_sect_title'], '
				</td>
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_news_add_sect_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="title" size="50" maxlength="100" />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_news_add_sect_icon'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="icon" size="50" maxlength="100" />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_news_add_sect_position'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="position" size="3" value="', $context['last_position'] ,'" maxlength="3" />
				</td>
			</tr>				
		</table>
		<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_add'],'" value="',$txt['ultport_button_add'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: News / Section: Edit Section
function template_edit_news_section()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;


	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=up-news;sa=edit-section" accept-charset="', $context['character_set'], '">												
		<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_edit_sect_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png"/>&nbsp;', $txt['ultport_admin_edit_sect_title'], '
				</td>
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_news_add_sect_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="title" size="50" value="', $context['title'] ,'" maxlength="100" />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_news_add_sect_icon'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="icon" size="50" value="', $context['icon'] ,'"  maxlength="100" />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_news_add_sect_position'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="position" size="3" value="', $context['position'] ,'" maxlength="3" />
				</td>
			</tr>				
		</table>
		<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />						
					<input type="hidden" name="id" value="', $context['id'] ,'" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_edit'],'" value="',$txt['ultport_button_edit'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: News / Section: Admin News
function template_admin_news()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_news_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";

	echo'
	<table width="90%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
		<tr>
			<td class="titlebg" align="left" colspan="4">	
				<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] .'
			</td>
			<td class="titlebg" align="left" colspan="1">	
				<img style="float:left" alt="',$txt['ultport_button_add'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png"/>&nbsp;
				<a href="', $scripturl ,'?action=admin;area=up-news;sa=add-news;sesc=' . $context['session_id'].'"><strong>', $txt['ultport_button_add'] ,'</strong></a>
			</td>
		</tr>
		<tr>
			<td width="1%" align="center" class="catbg">									
				', $txt['ultport_admin_news_sect_id'], '
			</td>
			<td width="39%" align="center" class="catbg">									
				', $txt['ultport_admin_add_news_title'], '
			</td>
			<td width="30%" align="center" class="catbg">									
				', $txt['ultport_admin_add_news_sect_title'], '
			</td>
			<td width="30%" align="left" colspan="2" class="catbg">									
				', $txt['ultport_admin_news_sect_action'], '
			</td>
		</tr>';
	if(!empty($context['load_news_admin']))	
	{
		foreach ($context['news-admin'] as $news)	
		{
			echo '					
			<tr>
				<td width="1%" align="center" class="windowbg">									
					', $news['id'] ,'
				</td>
				<td width="39%" align="left" class="windowbg">									
					<strong>', $news['title-edit'] ,'</strong>
				</td>
				<td width="30%" align="left" class="windowbg">									
					<strong>', $news['title-section'] ,'</strong>
				</td>
				<td width="15%" align="center" class="windowbg">									
					<strong>', $news['edit'] ,'</strong>
				</td>
				<td width="15%" align="center" class="windowbg">									
					<strong>', $news['delete'] ,'</strong>
				</td>
			</tr>';
		}
	}
	echo '
		<tr>
			<td class="titlebg" align="left" colspan="4">	
				<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] .'
			</td>
			<td class="titlebg" align="left" colspan="1">	
				<img style="float:left" alt="',$txt['ultport_button_add'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png"/>&nbsp;
				<a href="', $scripturl ,'?action=admin;area=up-news;sa=add-news;sesc=' . $context['session_id'].'"><strong>', $txt['ultport_button_add'] ,'</strong></a>
			</td>
		</tr>
	</table>';

}

function template_add_news()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings, $user_info;
	
	echo	'
	<form method="post" action="', $scripturl, '?action=admin;area=up-news;sa=add-news" accept-charset="', $context['character_set'], '">												
		<table width="80%" align="center" class="tborder" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td colspan="2" width="100%" class="titlebg">									
					', $txt['ultport_admin_add_news_title2'], '
				</td>			
			</tr>			
			<tr>
				<td width="50%" class="windowbg2">									
					', $txt['ultport_admin_add_news_title'], '
				</td>			
				<td width="50%" class="windowbg2">									
					<input type="text" name="title" size="85" />
				</td>			
			</tr>			
			<tr>
				<td width="50%" class="windowbg2">									
					', $txt['ultport_admin_add_news_section'], '
				</td>			
				<td width="50%" class="windowbg2">									
					<select size="1" name="id_cat">
						', $context['section'] ,'
					</select>	
				</td>			
			</tr>			
			<tr>
				<td colspan="2" width="100%" align="center" class="windowbg2">									
					<textarea id="elm1" name="elm1" rows="15" cols="80" style="width: 100%"></textarea>
				</td>			
			</tr>
		</table>
		<table width="80%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td colspan="2" align="center">	
					<input type="hidden" name="save" value="ok" />						
					<input type="hidden" name="id_member" value="', $user_info['id'] ,'" />						
					<input type="hidden" name="username" value="', $user_info['username'] ,'" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_add'],'" value="',$txt['ultport_button_add'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

function template_edit_news()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings, $user_info;
	
	echo	'
	<form method="post" action="', $scripturl, '?action=admin;area=up-news;sa=edit-news" accept-charset="', $context['character_set'], '">												
		<table width="80%" align="center" class="tborder" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td colspan="2" width="100%" class="titlebg">									
					', $txt['ultport_admin_edit_news_title'], '
				</td>			
			</tr>			
			<tr>
				<td width="50%" class="windowbg2">									
					', $txt['ultport_admin_add_news_title'], '
				</td>			
				<td width="50%" class="windowbg2">									
					<input type="text" value="', $context['title'] ,'" name="title" size="85" />
				</td>			
			</tr>			
			<tr>
				<td width="50%" class="windowbg2">									
					', $txt['ultport_admin_add_news_section'], '
				</td>			
				<td width="50%" class="windowbg2">									
					<select size="1" name="id_cat">
						', $context['section-edit'] ,'
					</select>	
				</td>			
			</tr>			
			<tr>
				<td colspan="2" width="100%" align="center" class="windowbg2">									
					<textarea id="elm1" name="elm1" rows="15" cols="80" style="width: 100%">', $context['body'] ,'</textarea>
				</td>			
			</tr>
		</table>
		<table width="80%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td colspan="2" align="center">	
					<input type="hidden" name="save" value="ok" />						
					<input type="hidden" name="id" value="', $context['id'] ,'" />											
					<input type="hidden" name="id_member_updated" value="', $user_info['id'] ,'" />						
					<input type="hidden" name="username_updated" value="', $user_info['username'] ,'" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_edit'],'" value="',$txt['ultport_button_edit'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: Board News / Section: Gral Settings
function template_board_news_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=board-news;sa=bn-main" accept-charset="', $context['character_set'], '">												
		<table width="95%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_bn_main_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['ultport_admin_bn_main_title'], '
				</td>
			</tr>
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_bn_limit'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="board_news_limit" size="3" maxlength="3" ',!empty($ultimateportalSettings['board_news_limit']) ? 'value="'.$ultimateportalSettings['board_news_limit'].'"' : '','/>
				</td>
			</tr>	
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_bn_lenght'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="board_news_lenght" size="4" maxlength="5" ',!empty($ultimateportalSettings['board_news_lenght']) ? 'value="'.$ultimateportalSettings['board_news_lenght'].'"' : '','/>
				</td>
			</tr>
			<tr>
				<td width="50%" class="windowbg" valign="top">
					',$txt['ultport_admin_bn_view'],'
				</td>
				<td width="50%" align="center" class="windowbg2">						
					<select name="boards[]" size="10" multiple="multiple" style="width: 88%;">';
							$id_boards = explode(',',$ultimateportalSettings['board_news_view']);			
								echo'
									<option value="0" ' ,isset($id_boards) ? (in_array(0, $id_boards) ? 'selected="selected"' : '') : '', '>' .$txt['ultport_admin_bn_select_all']. '</option>';
							foreach ($context['jump_to'] as $category)
							{
								echo '
									<option disabled="disabled">----------------------------------------------------</option>
									<option disabled="disabled">', $category['name'], '</option>
									<option disabled="disabled">----------------------------------------------------</option>';
								foreach ($category['boards'] as $board)
									echo '
									<option value="' ,$board['id'], '" ' ,isset($id_boards) ? (in_array($board['id'], $id_boards) ? 'selected="selected"' : '') : '', '> ' . str_repeat('&nbsp;&nbsp;&nbsp; ', $board['child_level']) . '|--- ' . $board['name'] . '</option>';
							}
	echo '
					</select>
				</td>
			</tr>				
			<tr>
				<td class="windowbg" align="center" colspan="2">	
					<input type="hidden" name="bn-save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: Download / Section: Gral Settings
function template_download_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo'
	<form method="post" name="downform" action="', $scripturl, '?action=admin;area=download;sa=main" accept-charset="', $context['character_set'], '">												
		<table width="90%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" colspan="2" class="catbg">						
					<img alt="',$txt['up_down_settings_tab'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['up_down_settings_tab'], '
				</td>
			</tr>
			<tr>
				<td valign="top" width="50%" class="windowbg">									
					', $txt['up_down_file_limit_page'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="download_file_limit_page" size="3" maxlength="3" ',!empty($ultimateportalSettings['download_file_limit_page']) ? 'value="'.$ultimateportalSettings['download_file_limit_page'].'"' : 'value="10"','/>
				</td>
			</tr>	
			<tr>
				<td valign="top" width="50%" class="windowbg">									
					', $txt['up_down_file_max_size'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="download_file_max_size" size="20" maxlength="50" ',!empty($ultimateportalSettings['download_file_max_size']) ? 'value="'.$ultimateportalSettings['download_file_max_size'].'"' : 'value="2048"','/>
				</td>
			</tr>				
			<tr>
				<td valign="top" width="50%" class="windowbg">									
					', $txt['up_down_extension_file'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="download_extension_file" size="50" maxlength="50" ',!empty($ultimateportalSettings['download_extension_file']) ? 'value="'.$ultimateportalSettings['download_extension_file'].'"' : 'value="zip, tar.gz"','/>
				</td>
			</tr>				
			<tr>
				<td valign="top" width="50%" class="windowbg" valign="top">
					',$txt['up_down_enable_approved_file'],'
				</td>
				<td width="50%" align="center" class="windowbg2">						
					<select name="download_enable_approved_file" size="1">';				
					echo'
						<option value="" ', (empty($ultimateportalSettings['download_enable_approved_file'])) ? 'selected="selected"' : '' ,'>'. $txt['up_down_no_approved_file'] .'</option>
						<option value="on" ', (!empty($ultimateportalSettings['download_enable_approved_file'])) ? 'selected="selected"' : '' ,'>'. $txt['up_down_yes_approved_file'] .'</option>';							
	echo '
					</select>
				</td>
			</tr>';

	//If required, approval for the Admin, then, this part appears		
	if (!empty($ultimateportalSettings['download_enable_approved_file']))			
	{
		echo '		
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg">									
					', $txt['up_down_enable_send_pm_approved'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="down_enable_send_pm_approved" ',!empty($ultimateportalSettings['down_enable_send_pm_approved']) ? 'checked="checked"' : '',' />
				</td>
			</tr>';

		if (!empty($ultimateportalSettings['down_enable_send_pm_approved']))			
		{		
			echo '
			<tr>
				<td valign="top" width="50%" class="windowbg">									
					', $txt['up_down_pm_id_member'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="download_pm_id_member" size="3" maxlength="10" ',!empty($ultimateportalSettings['download_pm_id_member']) ? 'value="'.$ultimateportalSettings['download_pm_id_member'].'"' : '','/>
				</td>
			</tr>
			<tr>
				<td valign="top" width="50%" class="windowbg">									
					', $txt['up_down_pm_subject'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="download_pm_subject" size="70" maxlength="150" ',!empty($ultimateportalSettings['download_pm_subject']) ? 'value="'.$ultimateportalSettings['download_pm_subject'].'"' : '','/>
				</td>
			</tr>
			<tr>
				<td colspan="2" valign="top" align="left width="100%" class="windowbg">									
					', $txt['up_down_pm_body'], '
				</td>
			</tr>
			<tr>	
				<td colspan="2" width="100%" align="left" class="windowbg2">									
					<div id="'. $context['bbcBox_container'] .'"></div>			
					<div id="'. $context['smileyBox_container'] .'"></div>											
					'. up_template_control_richedit($context['post_box_name'],$context['smileyBox_container'],$context['bbcBox_container']) .'
				</td>
			</tr>';
		}		
	}		
	
	echo '		
		</table>
		<table width="90%" align="center" cellspacing="1" cellpadding="5" border="0">			
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: Download / Section: Download Section
function template_download_section()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_download_section_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";

	echo'												
		<table width="90%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" colspan="5" class="catbg">						
					<img alt="',$txt['up_down_section_tab'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/block-position.png"/>&nbsp;', $txt['up_down_section_tab'], '
				</td>
			</tr>';
			
	echo '		
			<tr>
				<td valign="top" width="3%" align="center" class="titlebg">									
					&nbsp;
				</td>
				<td valign="top" width="7%" align="center" class="titlebg">									
					', $txt['up_down_sect_icon'] ,'
				</td>
				<td valign="top" width="40%" align="left" class="titlebg">									
					', $txt['up_down_sect_title'] ,'
				</td>
				<td valign="top" width="25%" align="center" class="titlebg">									
					', $txt['up_down_sect_perms'] ,'
				</td>				
				<td valign="top" width="20%" align="center" class="titlebg">									
					', $txt['up_down_sect_board'] ,'
				</td>				
			</tr>';

	if ($context['view'])
	{
		foreach($context['dowsect'] as $dowsect)
		{
			echo '		
			<tr>
				<td valign="top" width="3%" align="center" class="windowbg">									
					', $dowsect['id'] ,'
				</td>
				<td valign="top" width="7%" align="center" class="windowbg">									
					', $dowsect['icon-img'] ,'
				</td>
				<td valign="top" width="40%" align="left" class="windowbg">									
					<strong>', $dowsect['title'] ,'</strong>
					<div style="float:right">	
						<strong>', $dowsect['edit'] ,'&nbsp;', $dowsect['delete'] ,'</strong>
					</div>	
					<br />
					', $dowsect['description'] ,'
				</td>
				<td valign="top" width="25%" align="center" class="windowbg">									
					', $dowsect['id_groups'] ,'
				</td>
				<td valign="top" width="20%" align="center" class="windowbg">';
				if(!empty($dowsect['id_board']))
				{
					foreach ($context['jump_to'] as $category)
					{
						foreach ($category['boards'] as $board)
						{
							if ($dowsect['id_board'] == $board['id'])
							{
								echo '', $board['name'] ,'';
							}
						}
					}
				}else{
					echo '', $txt['up_down_sect_no_board'] ,'';							
				}					
			echo '		
				</td>
			</tr>';
		}	
	}
	
	echo '		
		</table>
		<table width="90%" align="center" cellspacing="1" cellpadding="5" border="0">			
			<tr>
				<td align="left" colspan="4">	
					<img style="float:left" alt="',$txt['ultport_button_add'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png"/>&nbsp;
					<a href="', $scripturl ,'?action=admin;area=download;sa=add;sesc=' . $context['session_id'].'"><strong>', $txt['ultport_button_add'] ,'</strong></a>
				</td>
			</tr>
		</table>';

}

//Show the Ultimate Portal - Area: Download / Section: Add Section
function template_download_add_section()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo'
	<form method="post" name="sectform" action="', $scripturl, '?action=admin;area=download;sa=add" accept-charset="', $context['character_set'], '">												
		<table width="90%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="catbg">						
					<img alt="',$txt['up_down_settings_tab'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png"/>&nbsp;', $txt['up_down_section_tab'], '
				</td>
			</tr>
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg">									
					', $txt['up_down_manage_sect_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="title" size="70" maxlength="100"/>
				</td>
			</tr>	
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg">									
					', $txt['up_down_manage_sect_icon'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="icon" size="70" maxlength="150"/>
				</td>
			</tr>	
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg">									
					', $txt['up_down_manage_sect_perms'], '
				</td>
				<td width="50%" align="left" class="windowbg2">									
					<div id="allowedAutoUnhideGroupsList">';
					// List all the membergroups so the user can choose who may access this board.
					foreach ($context['groups'] as $group)
					echo '
						<input type="checkbox" name="perms[]" value="', $group['id_group'], '" id="groups_', $group['id_group'], '"/>', $group['group_name'], '<br />';
					echo '
						<input type="checkbox" onclick="invertAll(this, this.form, \'perms[]\');" /> <i>', $txt['ultport_button_select_all'], '</i><br />
						<br />
					</div>
				</td>			
			</tr>
			<tr>
				<td valign="top" width="50%" class="windowbg" valign="top">
					',$txt['up_down_board_post_file'],'
				</td>
				<td width="50%" align="center" class="windowbg2">						
					<select name="id_board_posts" size="1">';
						echo'
							<option value="0">' .$txt['up_down_board_post_file_disabled']. '</option>
							<option disabled="disabled">----------------------------------------------------</option>';
							
					foreach ($context['jump_to'] as $category)
					{
						foreach ($category['boards'] as $board)
							echo '
							<option value="' ,$board['id'], '">'. $board['name'] .'</option>';
					}
	echo '
					</select>
				</td>
			</tr>			
			<tr>
				<td colspan="2" valign="top" width="100%" align="left" class="windowbg">									
					', $txt['up_down_manage_sect_description'], '
				</td>
			</tr>
			<tr>	
				<td colspan="2" class="windowbg2" align="left" width="100%">
					<div id="'. $context['bbcBox_container'] .'"></div>			
					<div id="'. $context['smileyBox_container'] .'"></div>											
					'. up_template_control_richedit($context['post_box_name'],$context['smileyBox_container'],$context['bbcBox_container']) .'
				</td>
			</tr>';
	echo '		
		</table>
		<table width="90%" align="center" cellspacing="1" cellpadding="5" border="0">			
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: Download / Section: Edit Section
function template_download_edit_section()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo'
	<form method="post" name="sectform" action="', $scripturl, '?action=admin;area=download;sa=edit" accept-charset="', $context['character_set'], '">												
		<table width="90%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="catbg">						
					<img alt="',$txt['up_down_settings_tab'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png"/>&nbsp;', $txt['up_down_section_tab'], '
				</td>
			</tr>';
	foreach($context['dowsect'] as $dowsect)		
	{
	echo '		
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg">									
					', $txt['up_down_manage_sect_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">	
					<input type="hidden" name="id" value="', $dowsect['id'] ,'"/>									
					<input type="text" name="title" size="70" maxlength="100" value="', $dowsect['title'] ,'"/>
				</td>
			</tr>	
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg">									
					', $txt['up_down_manage_sect_icon'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="icon" size="70" maxlength="150" value="', $dowsect['icon'] ,'"/>
				</td>
			</tr>	
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg">									
					', $txt['up_down_manage_sect_perms'], '
				</td>
				<td width="50%" align="left" class="windowbg2">									
					<div id="allowedAutoUnhideGroupsList">';
					$permissionsGroups = explode(',',$dowsect['id_groups']);					
					// List all the membergroups so the user can choose who may access this board.
					foreach ($context['groups'] as $group)
					echo '
						<input ', ((in_array($group['id_group'],$permissionsGroups) == true) ? ' checked="checked" ' : '') ,' type="checkbox" name="perms[]" value="', $group['id_group'], '" id="groups_', $group['id_group'], '"/>', $group['group_name'], '<br />';
					echo '
						<input type="checkbox" onclick="invertAll(this, this.form, \'perms[]\');" /> <i>', $txt['ultport_button_select_all'], '</i><br />
						<br />
					</div>
				</td>			
			</tr>	
			<tr>
				<td valign="top" width="50%" class="windowbg" valign="top">
					',$txt['up_down_board_post_file'],'
				</td>
				<td width="50%" align="center" class="windowbg2">						
					<select name="id_board_posts" size="1">';
						echo'
							<option value="0" ', empty($dowsect['id_board']) ? 'selected="selected"' : '','>' .$txt['up_down_board_post_file_disabled']. '</option>
							<option disabled="disabled">----------------------------------------------------</option>';
							
					foreach ($context['jump_to'] as $category)
					{
						foreach ($category['boards'] as $board)
							echo '
							<option value="' ,$board['id'], '" ', ($dowsect['id_board'] == $board['id']) ? 'selected="selected"' : '','>'. $board['name'] .'</option>';
					}
	echo '
					</select>
				</td>
			</tr>						
			<tr>
				<td colspan="2" valign="top" width="100%" align="left" class="windowbg">									
					', $txt['up_down_manage_sect_description'], '
				</td>
			</tr>
			<tr>	
				<td colspan="2" class="windowbg2" align="left" width="100%">
					<div id="'. $context['bbcBox_container'] .'"></div>			
					<div id="'. $context['smileyBox_container'] .'"></div>											
					'. up_template_control_richedit($context['post_box_name'],$context['smileyBox_container'],$context['bbcBox_container']) .'
				</td>
			</tr>';
	}		
	echo '		
		</table>
		<table width="90%" align="center" cellspacing="1" cellpadding="5" border="0">			
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_edit'],'" value="',$txt['ultport_button_edit'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: Internal Page / Section: Gral Settings
function template_ipage_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=internal-page;sa=main" accept-charset="', $context['character_set'], '">												
		<table width="80%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="catbg">						
					<img alt="',$txt['ipage_settings_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['ipage_settings_title'], '
				</td>
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ipage_active_columns'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="ipage_active_columns" ',!empty($ultimateportalSettings['ipage_active_columns']) ? 'checked="checked"' : '',' />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ipage_limit'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="ipage_limit" size="5" maxlength="4" ',!empty($ultimateportalSettings['ipage_limit']) ? 'value="'.$ultimateportalSettings['ipage_limit'].'"' : 'value="10"','/>
				</td>
			</tr>	
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ipage_social_bookmarks'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="ipage_social_bookmarks" ',!empty($ultimateportalSettings['ipage_social_bookmarks']) ? 'checked="checked"' : '',' />
				</td>
			</tr>							
		</table>
		<table width="80%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

		
//Show the Ultimate Portal - Area: Affiliates / Section: Gral Settings
function template_affiliates_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	
echo'
	<form method="post" action="', $scripturl, '?action=admin;area=up-affiliates;sa=aff-main" accept-charset="', $context['character_set'], '">												
		<table width="95%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_aff_main_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['ultport_admin_aff_main_title'], '
				</td>
			</tr>
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_aff_limit'], '
				</td>';
				
				// Banner Limits
				echo'
					<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="aff_limit" size="3" maxlength="3" ',!empty($ultimateportalSettings['aff_limit']) ? 'value="'.$ultimateportalSettings['aff_limit'].'"' : '','/>
				</td>
				
				</tr>
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_aff_scrollDelay'], '
				</td>';
				
				//Scroll Delay
				echo'
					<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="aff_scrolldelay" size="3" maxlength="3" ',!empty($ultimateportalSettings['aff_scrolldelay']) ? 'value="'.$ultimateportalSettings['aff_scrolldelay'].'"' : '','/>
				</td>
				
			</tr>
			
			<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_aff_direction'], '
				</td>';
		
				//Marquee Direction
				echo'
					<td width="50%" align="center" class="windowbg2">									
					<input type="radio" name="aff_direction" value="1" ', ($ultimateportalSettings['aff_direction'] == 1) ? 'checked="checked"' : '' ,'>&nbsp; ', $txt['ultport_admin_aff_direction_up'], ' &nbsp;
					<input type="radio" name="aff_direction" value="2" ', ($ultimateportalSettings['aff_direction'] == 2) ? 'checked="checked"' : '' ,'>&nbsp; ', $txt['ultport_admin_aff_direction_down'], ' &nbsp;
					<input type="radio" name="aff_direction" value="3" ', ($ultimateportalSettings['aff_direction'] == 3) ? 'checked="checked"' : '' ,'>&nbsp; ', $txt['ultport_admin_aff_direction_noMove'], ' &nbsp;					
				</td>
				
			</tr>	
			
						<tr>
				<td width="50%" class="windowbg">									
					', $txt['ultport_admin_aff_target'], '
				</td>';
				
				//Target
				echo'
					<td width="50%" align="center" class="windowbg2">									
					<input type="radio" name="aff_target" value="1" ', ($ultimateportalSettings['aff_target'] == 1) ? 'checked="checked"' : '' ,'>&nbsp; ', $txt['ultport_admin_aff_target_self'], ' &nbsp;
					<input type="radio" name="aff_target" value="2" ', ($ultimateportalSettings['aff_target'] == 2) ? 'checked="checked"' : '' ,'>&nbsp; ', $txt['ultport_admin_aff_target_blank'], ' &nbsp;
				</td>
				
			</tr>	
			
				</table>
		<table width="80%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />		
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
			
		</table>
	</form>';

}

function template_aff_affiliates()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	echo "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";

	
		if (!empty($context['view']))
		{
			echo'
			<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
				<tr>
					<td width="1%" align="center" class="catbg">									
						', $txt['ultport_admin_add_aff_cant'], '
					</td>
					<td width="40%" align="center" class="catbg">									
						', $txt['ultport_admin_add_aff_title'], '
					</td>
					<td width="9%" align="center" class="catbg">									
						', $txt['ultport_admin_add_aff_minibanner'], '
					</td>

					<td width="50%" align="center" colspan="2" class="catbg">									
						', $txt['ultport_admin_add_aff_actions'], '
					</td>
				</tr>';
				
						$cantidad = 0;
			foreach ($context['up-aff'] as $aff)	
			{
				

				echo '					
				<tr>
					<td width="1%" align="center" class="windowbg">									
						', ++$cantidad ,'
					</td>
					<td width="9%" align="center" class="windowbg">									
						', $aff['title'] ,'
					</td>
					<td width="9%" align="center" class="windowbg">									
						', $aff['imageurl'] ,'
					</td>
					<td width="25%" align="center" class="windowbg">									
						<strong>', $aff['edit'] ,'</strong>
					</td>
					<td width="25%" align="center" class="windowbg">									
						<strong>', $aff['delete'] ,'</strong>
					</td>
				</tr>';
			}
			echo '
			</table>';
		}else{
			echo '	
			<table width="70%" class="tborder" align="center" cellspacing="1" cellpadding="5" border="0">
				<tr>
					<td class="catbg" align="left" colspan="6">	
						<strong>', $txt['ultport_no_rows_title'] ,'</strong>
					</td>
				</tr>
				<tr>
					<td class="windowbg" align="center" colspan="6">	
						<strong>', $txt['ultport_no_rows'] ,'</strong>
					</td>
				</tr>
			</table>';
		}
		$can_add_more = true;
		if(!empty($cantidad))
			$can_add_more = ($ultimateportalSettings['aff_limit'] > $cantidad) ? true : false;
	
		if ($can_add_more === true)
		{
			echo '	
		<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="left" colspan="6">	
					<img style="float:left" alt="',$txt['ultport_button_add'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png"/>&nbsp;
					<a href="', $scripturl ,'?action=admin;area=up-affiliates;sa=add_aff;sesc=' . $context['session_id'].'"><strong>', $txt['ultport_button_add'] ,'</strong></a>
				</td>
			</tr>
		</table>';
		}
		else 
		{ 
			echo' 
				<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="left" colspan="6">	
			'.$txt['ultport_admin_aff_limit_error'].'</td>
			</tr>
		</table>';
		}
}

function template_add_aff()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;


	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=up-affiliates;sa=add_aff" accept-charset="', $context['character_set'], '">												
		<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_aff_admin_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png"/>&nbsp;', $txt['ultport_admin_aff_admin_title'], '
				</td>
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_aff_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="title" size="50" maxlength="100" />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_aff_url'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input value="http://" type="text" name="url" size="50" maxlength="100" />
				</td>
			</tr>	
			
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_aff_urlbanner'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input value="http://" type="text" name="imageurl" size="50" maxlength="100" />
				</td>
			</tr>
			
						<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_aff_alt'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="alt" size="50" maxlength="100" />
				</td>
			</tr>
			
		</table>
		<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_add'],'" value="',$txt['ultport_button_add'],'" />
				</td>
			</tr>
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: Extra Field / Section: Add Extra Field
function template_edit_aff()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;


	echo'
	<form method="post" action="', $scripturl, '?action=admin;area=up-affiliates;sa=edit_aff" accept-charset="', $context['character_set'], '">												
		<table width="70%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="catbg">						
					<img alt="',$txt['ultport_admin_aff_admin_title'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png"/>&nbsp;', $txt['ultport_admin_aff_admin_title'], '
				</td>
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_aff_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="title" size="50" maxlength="100" value="', $context['title'] ,'"/>
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_aff_url'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="url" size="50" maxlength="100" value="', $context['url'] ,'"/>
				</td>
			</tr>	
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_aff_urlbanner'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="imageurl" size="50" maxlength="100" value="', $context['imageurl'] ,'"/>
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['ultport_admin_add_aff_alt'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="alt" size="50" maxlength="100" value="', $context['alt'] ,'"/>
				</td>
			</tr>				
		</table>
		<table width="70%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="id" value="', $context['id'] ,'" />						
					<input type="hidden" name="save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_edit'],'" value="',$txt['ultport_button_edit'],'" />
				</td>
			</tr>
		</table>
	</form>';
}

//Show the Ultimate Portal - Area: About Us / Section: Gral Settings
function template_aboutus_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	
echo'
	<form method="post" name="aboutform" action="', $scripturl, '?action=admin;area=up-aboutus;sa=main" accept-charset="', $context['character_set'], '">												
		<table width="95%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="left" width="100%" colspan="2" class="catbg">						
					<img alt="',$txt['up_about_settings_tab'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['up_about_settings_tab'], '
				</td>
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['up_about_show_nick'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="about_us_show_nick" checked="checked" disabled="disabled" />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['up_about_show_group'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="about_us_show_rank" checked="checked" disabled="disabled" />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['up_about_show_date_registered'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="about_us_registered" ',!empty($ultimateportalSettings['about_us_registered']) ? 'checked="checked"' : '',' />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['up_about_show_mail'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="about_us_view_mail" ',!empty($ultimateportalSettings['about_us_view_mail']) ? 'checked="checked"' : '',' />
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['up_about_show_pm'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="checkbox" value="on" name="about_us_view_pm" ',!empty($ultimateportalSettings['about_us_view_pm']) ? 'checked="checked"' : '',' />
				</td>
			</tr>			
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['up_about_extrainfo_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="about_us_extrainfo_title" size="50" maxlength="150" "', !empty($ultimateportalSettings['about_us_extrainfo_title']) ? 'value="'. $ultimateportalSettings['about_us_extrainfo_title'] .'"' : '' ,'"/>
				</td>
			</tr>				
			<tr>
				<td colspan="2" width="100%" valign="top" align="left" class="windowbg">									
					', $txt['up_about_extra_info'], '
				</td>
			</tr>	
			<tr>
				<td colspan="2" width="100%" align="left" class="windowbg2">
					<div id="bbcBox_message"></div>
					<div id="smileyBox_message"></div>
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'),'
				</td>
			</tr>
			<tr>
				<td width="50%" valign="top" align="left" class="windowbg">									
					', $txt['up_about_group_view'], '
				</td>
				<td width="50%" align="left" class="windowbg2">
					<div id="allowedAutoUnhideGroupsList">';
					$GroupsView = explode(',',$ultimateportalSettings['about_us_group_view']);
						// List all the membergroups so the user can choose who may access this board.
					foreach ($context['groups'] as $group)
	echo '
						<input type="checkbox" name="about_us_group_view[]" value="', $group['id_group'], '" id="groups_', $group['id_group'], '"', ((in_array($group['id_group'],$GroupsView) == true) ? ' checked="checked" ' : ''), '/>', $group['group_name'], '<br />';
echo '
						<input type="checkbox" onclick="invertAll(this, this.form, \'about_us_group_view[]\');" /> <i>', $txt['ultport_button_select_all'], '</i><br />
						<br />
					</div>
				</td>
			</tr>			
		</table>
		<table width="80%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
			
		</table>
	</form>';

}

//Show the Ultimate Portal - Area: FAQ / Section: Gral Settings
function template_faq_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	
echo'
	<form method="post" name="aboutform" action="', $scripturl, '?action=admin;area=up-faq;sa=main" accept-charset="', $context['character_set'], '">												
		<table width="95%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="left" width="100%" colspan="2" class="catbg">						
					<img alt="',$txt['up_faq_config'],'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/gral-settings.png"/>&nbsp;', $txt['up_faq_config'], '
				</td>
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['up_faq_title_page'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="faq_title" size="50" maxlength="100" "', !empty($ultimateportalSettings['faq_title']) ? 'value="'. $ultimateportalSettings['faq_title'] .'"' : '' ,'"/>
				</td>
			</tr>				
			<tr>
				<td width="50%" align="left" class="windowbg">									
					', $txt['up_faq_small_description'], '
				</td>
				<td width="50%" align="center" class="windowbg2">
					<input type="text" name="faq_small_description" size="50" maxlength="150" "', !empty($ultimateportalSettings['faq_small_description']) ? 'value="'. $ultimateportalSettings['faq_small_description'] .'"' : '' ,'"/>
				</td>
			</tr>				
			<tr>
				<td colspan="2" width="50%" align="center" class="windowbg">
					<a href="'. $scripturl .'?action=admin;area=preferences;sa=permissions-settings"><strong>'. $txt['up_faq_perms'] .'</strong></a>
				</td>
			</tr>				
		</table>
		<table width="80%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['ultport_button_save'],'" value="',$txt['ultport_button_save'],'" />
				</td>
			</tr>
			
		</table>
	</form>';

}

?>