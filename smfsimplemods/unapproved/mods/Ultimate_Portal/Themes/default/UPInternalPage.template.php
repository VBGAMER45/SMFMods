<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

//Show the Ultimate Portal - Module Internal Page
function template_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";

	// Create the button set...
	$normal_buttons = array(
		'add_html' => array('condition' => ($user_info['is_admin'] || !empty($user_info['up-modules-permissions']['ipage_add'])), 'text' => 'up_ipage_add_html', 'lang' => true, 'url' => $scripturl .'?action=internal-page;sa=add;type=html;sesc=' . $context['session_id'].''),
		'add_bbc' => array('condition' => ($user_info['is_admin'] || !empty($user_info['up-modules-permissions']['ipage_add'])), 'text' => 'up_ipage_add_bbc', 'lang' => true, 'url' => $scripturl .'?action=internal-page;sa=add;type=bbc;sesc=' . $context['session_id'].''),		
	);

	$content .= '
<table width="100%" cellpadding="5" cellspacing="1">
	<tr>
		<td  align="left" width="100%">
			<table class="tborder" style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td align="left" width="100%">
						<strong>'. $context['news-linktree'] .'</strong>';
						$content .='	
						<div style="float:right" class="UPpagesection">
							'. up_template_button_strip($normal_buttons, 'right') .'
						</div>';
						
	$content .='					
					</td>
				</tr>
			</table>	
		</td>
	</tr>		
	<tr>
		<td width="100%">';
		//Disabled Any Page?
		if(!empty($context['disabled_page']) && $user_info['is_admin'])
		{
		$content .='
		<div style="border: 1px solid;" id="downloadsite">
			<div id="warning">
				<img alt="" width="30" height="30" title="'. $txt['up_module_ipage_title'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/download/stop.png"/>
					'. str_replace("IPAGE_URL", $scripturl . '?action=internal-page;sa=inactive' ,$txt['up_ipage_disabled_any_ipage']) .'
			</div>
		</div>';
		}
		//End
	if (!empty($context['view_ipage']))	
	{
			//Page Index
			$content .= '
			<br />
				<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] .'
			<br /><br />
			<table cellpadding="1" cellspacing="5" width="100%">
				<tr>
					<td width="100%">
						<dl id="DownBrowsing">';
			//End Page Index						

		foreach ($context['ipage'] as $ipage)
		{
			if ($ipage['can_view'] === true)
			{
				$content .= '
						<dt>
							'. $ipage['title'] .''. (!empty($ipage['sticky']) ? '&nbsp;&nbsp;<img alt="" style="vertical-align:middle" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/internal-page/sticky.gif" />' : '') .'
							'. (($user_info['is_admin'] || !empty($user_info['up-modules-permissions']['ipage_moderate'])) ? '&nbsp;'. $ipage['edit'] . ' '. $ipage['delete'] : '') .'															
						</dt>
						<dd '. (!empty($ipage['sticky']) ? 'class="stickybg"' : '') .'>
							<p>
								'. $ipage['day_created'] .'/'. $ipage['month_created'] .'/'. $ipage['year_created'] .'  
								'. $txt['up_ipage_member'] .' '. $ipage['profile'] .'
							</p>
							<ul>
								<li>'. $ipage['read_more'] .'</li>
								'. ($ipage['is_updated'] ? '
								<li><strong>'. $txt['up_ipage_date_updated'] .'</strong> '. $ipage['date_updated'] .'</li>
								<li><strong>'. $txt['up_ipage_member_updated'] .'</strong> '. $ipage['profile_updated'] .'</li>' : '') .'							
							</ul>
						</dd>';
			}	
		}//End for
		//Page Index
			$content .= '
						</dl>
					</td>
				</tr>
			</table>
			<br /><br />
				<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] .'';
			//End Page Index						
	}	
	$content .='	
		</td>
	</tr>
</table>';

	//The Internal Page Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_ipage_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	//Active Columns LEFT | RIGHT?
	$left = 0;
	$right = 0;
	if (!empty($ultimateportalSettings['ipage_active_columns']))
	{
		$left = 1;
		$right = 1;
	}
		
	up_print_page($left, $right, $content, $copyright, 'internal-page', $txt['up_module_title'] . ' - ' . $txt['up_module_ipage_title']);
	
}

//Show the Ultimate Portal - Module Internal Page - View
function template_view()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $memberContext;
	global $user_info;

	$content = "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";

	$content .= '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td  align="left" width="100%">
			<table class="tborder" style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td  align="left" width="100%">
						<strong>'. $context['news-linktree'] .'</strong>
					</td>
				</tr>
			</table>	
		</td>
	</tr>		
	<tr>
		<td width="100%">';
	if (!empty($context['view_ipage']))	
	{
			//Page Index
			$content .= '
			<table cellpadding="5" cellspacing="1" width="100%">';
			//End Page Index						
		foreach ($context['ipage'] as $ipage)
		{
			$content .= '
				<tr>
					<td valign="top" align="left" width="100%">
						<h2 class="titleipage">
							'. $ipage['title'] .''. (!empty($ipage['sticky']) ? '&nbsp;&nbsp;<img alt="" style="vertical-align:middle" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/internal-page/sticky.gif" />' : '') .'
						</h2>
						<br/>
						<div class="post-date">
							<p class="day">
								'. $ipage['day_created'] .'/'. $ipage['month_created'] .'/'. $ipage['year_created'] .'  
							</p>
						</div>
						<div class="post-info">
							<p class="author alignleft">
								'. $txt['up_ipage_member'] .' '. $ipage['profile'] .'
							</p>
							<p class="moderate alignright">
								'. (($user_info['is_admin'] || $user_info['up-modules-permissions']['ipage_moderate']) ? $ipage['edit'] .' | '. $ipage['delete'] : '') .'													
							</p>
						</div>						
						<br/>
						'. $ipage['parse_content'] .'												
						<br/><br/>
						'. ($ipage['is_updated'] ? '
						<br/>
						<strong>'. $txt['up_ipage_date_updated'] .'</strong> '. $ipage['date_updated'] .'
						<br/>
						<strong>'. $txt['up_ipage_member_updated'] .'</strong> '. $ipage['profile_updated'] : '') .'					
					</td>
				</tr>';		
		}//End for
			$content .= '
			</table>
			<br />
			'. (!empty($ultimateportalSettings['ipage_social_bookmarks']) ? $context['social_bookmarks'] : '') .'';
	}	
	$content .='	
		</td>
	</tr>
</table>';

	//The News Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_ipage_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page($context['column_left'], $context['column_right'], $content, $copyright, 'internal-page', $txt['up_module_ipage_title'] .' - '. $context['title']);
	
}

//Form for Edit Internal Page
function template_add()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings, $user_info;

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">
	<tr>
		<td  align="left" width="100%">
			<table class="tborder" style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td  align="left" width="100%">
						<strong>'. $context['news-linktree'] .'</strong>
					</td>
				</tr>
			</table>	
		</td>
	</tr>		
	<tr>
		<td width="100%">				
		<br />';
		
			$content .=	'
			<form name="ipageform" method="post" action="'. $scripturl .'?action=internal-page;sa=add" accept-charset="'. $context['character_set'] .'">												
				<table width="100%" align="center" class="tborder" cellspacing="1" cellpadding="5" border="0">
					<tr>
						<td colspan="2" align="left" width="100%" class="titlebg">									
							'. $txt['up_ipage_add_title'] .'
						</td>			
					</tr>			
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['up_ipage_title'] .'
						</td>			
						<td width="50%" align="center" class="windowbg2">									
							<input type="text" name="title" size="50" maxlength="100"/>
						</td>			
					</tr>
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['ipage_column_left'] .'
						</td>
						<td width="50%" align="center" class="windowbg2">
							<input type="checkbox" value="1" name="column_left"/>
						</td>
					</tr>												
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['ipage_column_right'] .'
						</td>
						<td width="50%" align="center" class="windowbg2">
							<input type="checkbox" value="1" name="column_right"/>
						</td>
					</tr>												
					<tr>
						<td colspan="2" width="100%" align="left" class="titlebg">';
				//Internal Page HTML
				if ($context['type_ipage'] == 'html')									
				{
					$content .='
							'. $txt['up_ipage_content'] .'
						</td>
					</tr>
					<tr>
						<td colspan="2" class="windowbg2" align="left" width="100%">							
							<textarea id="elm1" name="elm1" rows="15" cols="80" style="width: 100%"></textarea>';
				}	
				//Internal Page BBC						
				if ($context['type_ipage'] == 'bbc')									
				{
					$content .='
							'. $txt['up_ipage_content'] .'
						</td>
					</tr>
					<tr>
						<td colspan="2" class="windowbg2" align="left" width="100%">
							<div id="'. $context['bbcBox_container'] .'"></div>			
							<div id="'. $context['smileyBox_container'] .'"></div>											
							'. up_template_control_richedit($context['post_box_name'],$context['smileyBox_container'],$context['bbcBox_container']) .'';
				}	
	$content .='						
						</td>			
					</tr>
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['up_ipage_perms'] .'
						</td>
						<td valign="top" width="50%" align="left" class="windowbg2">
							<div id="allowedAutoUnhideGroupsList">';
								// List all the membergroups so the user can choose who may access this board.
							foreach ($context['groups'] as $group)
								$content .='
								<input type="checkbox" name="perms[]" value="'. $group['id_group'] .'" id="groups_'. $group['id_group'] .'"/>'. $group['group_name'] .'<br />';
								$content .='
								<input type="checkbox" onclick="invertAll(this, this.form, \'perms[]\');" /> <i>'. $txt['ultport_button_select_all'] .'</i><br />
								<br />
							</div>
						</td>
					</tr>																	
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['up_ipage_active'] .'
						</td>
						<td width="50%" align="center" class="windowbg2">
							<input type="checkbox" value="on" name="active"/>
						</td>
					</tr>																	
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['up_ipage_sticky'] .'
						</td>
						<td width="50%" align="center" class="windowbg2">
							<input type="checkbox" value="1" name="sticky"/>
						</td>
					</tr>																	
				</table>
				<table width="100%" align="center" cellspacing="1" cellpadding="5" border="0">
					<tr>
						<td colspan="2" align="left">	
							<input type="hidden" name="save" value="ok" />
							<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
							<input type="hidden" name="type_ipage" value="'. $context['type_ipage'] .'" />
							<input type="submit" name="'.$txt['ultport_button_add'].'" value="'.$txt['ultport_button_add'].'" />
						</td>
					</tr>
				</table>
			</form>';

	$content .= '		
		</td>
	</tr>
</table>';
		

	//The News Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_ipage_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	//Active Columns LEFT | RIGHT?
	$left = 0;
	$right = 0;
	if (!empty($ultimateportalSettings['ipage_active_columns']))
	{
		$left = 1;
		$right = 1;
	}	
	//Now Print the PAGE
	up_print_page($left, $right, $content, $copyright, 'internal-page', $txt['up_ipage_add_title']);

}

//Form for Edit Internal Page
function template_edit()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings, $user_info;

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td  align="left" width="100%">
			<table class="tborder" style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td  align="left" width="100%">
						<strong>'. $context['news-linktree'] .'</strong>
					</td>
				</tr>
			</table>	
		</td>
	</tr>		
	<tr>
		<td width="100%">
		<br />';
		
			$content .=	'
			<form name="ipageform" method="post" action="'. $scripturl .'?action=internal-page;sa=edit" accept-charset="'. $context['character_set'] .'">												
				<table width="100%" align="center" class="tborder" cellspacing="1" cellpadding="5" border="0">
					<tr>
						<td colspan="2" align="left" width="100%" class="titlebg">									
							'. $txt['up_ipage_edit_title'] .'
						</td>			
					</tr>			
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['up_ipage_title'] .'
						</td>			
						<td width="50%" align="center" class="windowbg2">									
							<input type="text" name="title" value="'. $context['title'] .'" size="50" maxlength="100"/>
						</td>			
					</tr>
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['ipage_column_left'] .'
						</td>
						<td width="50%" align="center" class="windowbg2">
							<input type="checkbox" '. (!empty($context['column_left']) ? 'checked="checked"' : '') .' value="1" name="column_left"/>
						</td>
					</tr>												
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['ipage_column_right'] .'
						</td>
						<td width="50%" align="center" class="windowbg2">
							<input type="checkbox" '. (!empty($context['column_right']) ? 'checked="checked"' : '') .' value="1" name="column_right"/>
						</td>
					</tr>												
					<tr>
						<td colspan="2" width="100%" align="left" class="titlebg">';
				//Internal Page HTML
				if ($context['type_ipage'] == 'html')									
				{
					$content .='
							'. $txt['up_ipage_content'] .'
						</td>
					</tr>
					<tr>
						<td colspan="2" class="windowbg2" align="left" width="100%">							
							<textarea id="elm1" name="elm1" rows="15" cols="80" style="width: 100%">'. $context['content'] .'</textarea>';
				}	
				//Internal Page BBC						
				if ($context['type_ipage'] == 'bbc')									
				{
					$content .='
							'. $txt['up_ipage_content'] .'
						</td>
					</tr>
					<tr>
						<td colspan="2" class="windowbg2" align="left" width="100%">
							<div id="'. $context['bbcBox_container'] .'"></div>			
							<div id="'. $context['smileyBox_container'] .'"></div>											
							'. up_template_control_richedit($context['post_box_name'],$context['smileyBox_container'],$context['bbcBox_container']) .'';
				}	
	$content .='						
						</td>			
					</tr>
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['up_ipage_perms'] .'
						</td>
						<td valign="top" width="50%" align="left" class="windowbg2">
							<div id="allowedAutoUnhideGroupsList">';
							$permissionsGroups = explode(',',$context['perms']);
								// List all the membergroups so the user can choose who may access this board.
							foreach ($context['groups'] as $group)
								$content .='
								<input type="checkbox" name="perms[]" value="'. $group['id_group'] .'" id="groups_'. $group['id_group'] .'"'. ((in_array($group['id_group'],$permissionsGroups) == true) ? ' checked="checked" ' : '') .'/>'. $group['group_name'] .'<br />';
								$content .='
								<input type="checkbox" onclick="invertAll(this, this.form, \'perms[]\');" /> <i>'. $txt['ultport_button_select_all'] .'</i><br />
								<br />
							</div>
						</td>
					</tr>																	
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['up_ipage_active'] .'
						</td>
						<td width="50%" align="center" class="windowbg2">
							<input type="checkbox" '. (($context['active'] == 'on') ? 'checked="checked"' : '') .' value="on" name="active"/>
						</td>
					</tr>																	
					<tr>
						<td width="50%" align="left" class="windowbg">									
							'. $txt['up_ipage_sticky'] .'
						</td>
						<td width="50%" align="center" class="windowbg2">
							<input type="checkbox" '. (!empty($context['sticky']) ? 'checked="checked"' : '') .' value="1" name="sticky"/>
						</td>
					</tr>																	
				</table>
				<table width="100%" align="center" cellspacing="1" cellpadding="5" border="0">
					<tr>
						<td colspan="2" align="left">	
							<input type="hidden" name="save" value="ok" />						
							<input type="hidden" name="type_ipage" value="'. $context['type_ipage'] .'" />
							<input type="hidden" name="id_ipage" value="'. $context['id'] .'" />	
							<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
							<input type="submit" name="'.$txt['ultport_button_edit'].'" value="'.$txt['ultport_button_edit'].'" />
						</td>
					</tr>
				</table>
			</form>';

	$content .= '		
		</td>
	</tr>
</table>';
		

	//The News Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_ipage_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	//Active Columns LEFT | RIGHT?
	$left = 0;
	$right = 0;
	if (!empty($ultimateportalSettings['ipage_active_columns']))
	{
		$left = 1;
		$right = 1;
	}	
	//Now Print the PAGE
	up_print_page($left, $right, $content, $copyright, 'internal-page', $txt['up_ipage_edit_title']);

}

?>