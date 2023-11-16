<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

//Show the Ultimate Portal - Module User Posts - View 
function template_user_posts_view()
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
		'add' => array('condition' => (!empty($user_info['up-modules-permissions']['user_posts_add']) || $user_info['is_admin']), 'text' => 'ultport_button_add', 'lang' => true, 'url' => $scripturl .'?action=user-posts;sa=add;sesc=' . $context['session_id'].''),
		'search' => array('condition' => ($user_info['is_guest'] || !$user_info['is_guest']), 'text' => 'ultport_button_search', 'lang' => true, 'url' => $scripturl .'?action=user-posts;sa=search'),
	);

	$content .= '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td align="left" width="100%">
			<table class="tborder" style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td align="left" width="70%">
						<strong>'. $context['news-linktree'] .'</strong>
					</td>';
					$content .= '		
					<td style="font-size:12px" align="left" width="30%">
						<div class="UPpagesection">
							'. up_template_button_strip($normal_buttons, 'right') .'
						</div>
					</td>';
			$content .= '		
				</tr>
			</table>	
		</td>
	</tr>		
	<tr>
		<td width="100%">
		<br />
			<table class="tborder" cellpadding="5" cellspacing="1" width="100%">
				';
		if (!empty($context['view-userpost']))
		{
			foreach ($context['userpost'] as $userpost)
			{		
				$content .= '		
				<tr>
					<td class="description" style="border-bottom:1px dashed;" width="100%" align="left">
						<div style="width:100%">
							<div style="width:15%;float:right">';

								if (!empty($ultimateportalSettings['user_posts_internal_page']))
								{							
									$content .='	
									<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/view.png" alt="'.  $txt['ultport_button_view'] .'" />&nbsp;
									<a href="'.$scripturl .'?action=user-posts;sa=view-single;id='. $userpost['id'] .'">'.  $txt['ultport_button_view'] .'</a><br />';
								}else{
									$content .='	
									<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/view.png" />&nbsp;
									<a href="'. $userpost['link_topic'] .'">'.  $txt['ultport_button_view'] .'</a><br />';							
								}									
					
								if (!empty($user_info['up-modules-permissions']['user_posts_moderate']) || $user_info['is_admin'])
								{										
									$content .= '
									<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png" />&nbsp;
									<a href="'.$scripturl .'?action=user-posts;sa=edit;id='. $userpost['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a><br />
									<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png" />&nbsp;
									<a onclick="return makesurelink()" href="'.$scripturl .'?action=user-posts;sa=delete;id='. $userpost['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
								}
							$content .= '						
							</div>							
							&nbsp;					
							<strong>'. $userpost['title'] . '</strong>
							<br/>&nbsp;&nbsp;'. $txt['user_posts_by'] . '&nbsp;'. $userpost['link-author'] .'
							<br/>
							&nbsp; '.  (!empty($ultimateportalSettings['user_posts_field_type_posts']) ? $userpost['type'] : '') .'
							&nbsp;'. (!empty($ultimateportalSettings['user_posts_field_add_language']) ? $userpost['lang'] : '') .'
							<br /><br />
							<div>';
								if (!empty($ultimateportalSettings['user_posts_field_member_use_module']))
									$content .=	'&nbsp;&nbsp;<em>'. $userpost['added-for'] .'</em>';
								
								if (!empty($ultimateportalSettings['user_posts_field_member_updated_module']) && !empty($userpost['id_member_updated']))				
									$content .=	'<br />&nbsp;&nbsp;<em>'. $userpost['updated-for'] .'</em>';
								
						$content .= '		
							</div>							
						</div>	
					</td>
				</tr>';
			}
		}else{
				$content .= '		
				<tr>
					<td class="windowbg" width="100%" align="center">
						'. $txt['ultport_error_no_user_posts_rows'] .'
					</td>
				</tr>';		
		}
				
		$content .= '		
			</table>
		</td>
	</tr>
	<tr>
		<td width="100%">
			<div style="text-align:center">
				<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] . '
			</div>
		</td>
	</tr>
</table>';

	//The User Posts Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_user_posts_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'user-posts', $txt['up_module_title'] . ' - ' . $txt['up_module_user_posts_title']);
	
}

//Show the Ultimate Portal - Module User Posts - Search
function template_user_posts_search()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = "";

	$content .= '
	<table width="100%" cellpadding="5" cellspacing="1">	
		<tr>
			<td align="left" width="100%">
				<table class="tborder" style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
					<tr>
						<td align="left" width="100%">
							<strong>'. $context['news-linktree'] .'</strong>
						</td>		
					</tr>
				</table>	
			</td>
		</tr>		
		<tr>
			<td width="100%">
				<form method="post" action="'. $scripturl .'?action=user-posts;sa=search" accept-charset="'. $context['character_set'] .'">
					<table class="UPdescription" cellpadding="5" cellspacing="1" width="100%">
						<tr>
							<td colspan="2" class="catbg" align="left" width="100%">
								<strong>'. $txt['user_posts_search_title'] .'</strong>
							</td>		
						</tr>				
						<tr>
							<td align="right" width="50%">
								<strong>'. $txt['user_posts_search_parameter'] .'</strong>
							</td>		
							<td align="left" width="50%">
								<input type="text" name="title" size="50" maxlength="150" '. (!empty($context['title']) ? 'value="'.$context['title'].'"' : '') .'/>
							</td>								
						</tr>';
			if (!empty($context['view_extra_field']))
			{
				$content .= '
						<tr>
							<td align="right" width="50%">
								<strong>'. $txt['user_posts_search_filter'] .'</strong>
							</td>		
							<td align="left" width="50%">
								<select name="extra_field" size="1">
									<option value="all" '. ((!empty($context['extra_field_filter']) && $context['extra_field_filter'] == 'all') ? 'selected="selected"' : '') .'>'. $txt['ultport_search_all'] .'</option>';
								foreach ($context['extra_field'] as $extra_field)
									$content .= '
									<option '. ((!empty($context['extra_field_filter']) && $context['extra_field_filter'] == $extra_field['id']) ? 'selected="selected"' : '') .' value="'. $extra_field['id'] .'">'. $extra_field['title'] .'</option>';
						$content .= '
								</select>		
							</td>								
						</tr>';
			}
			$content .= '			
						<tr>
							<td colspan="2" align="left">	
								<input type="hidden" name="search" value="ok" />						
								<input type="submit" name="'.$txt['ultport_button_search'].'" value="'.$txt['ultport_button_search'].'" />
							</td>
						</tr>
					</table>					
				</form>	
			</td>
		</tr>';
	//Search Result
	if(!empty($context['search_result']))
	{
		$content .= '
		<tr>
			<td width="100%">
				<table class="UPdescription" cellpadding="5" cellspacing="1" width="100%">
					<tr>
						<td colspan="2" class="catbg" align="left" width="100%">
							<strong>'. $txt['user_posts_search_result'] .'</strong>
						</td>		
					</tr>				
					<tr>
						<td colspan="2" align="left" width="100%">
							<dl id="DownBrowsing">';
						if (!empty($context['search_result_specific']))
						{						
							foreach ($context['result'] as $result)
							{
								$content .= 
								'<dt>
									' . $result['title'] . (!empty($ultimateportalSettings['user_posts_internal_page']) ? '<div style="float:right"><a href="'.$scripturl .'?action=user-posts;sa=view-single;id='. $result['id'] .'"><img style="vertical-align:middle" border="0" alt="'. $txt['ultport_button_view'] .'" title="'. $txt['ultport_button_view'] .'" src="'.$settings['default_images_url'].'/ultimate-portal/view.png" /></a></div>' : '<div style="float:right"><a href="'. $result['link_topic'] .'"><img style="vertical-align:middle" border="0" alt="'. $txt['ultport_button_view'] .'" title="'. $txt['ultport_button_view'] .'" src="'.$settings['default_images_url'].'/ultimate-portal/view.png" /></a></div>') .'
								</dt>
								<dd>
									<p>'. $txt['user_posts_by'] . '&nbsp;' . $result['author'] .'</p>
									<ul>
										<li>
											'. (!empty($ultimateportalSettings['user_posts_field_member_use_module']) ? $txt['user_posts_added_search'] . '&nbsp;'. $result['username_add_link'] . ',&nbsp;'. $result['date_add'] : '' ) .'
										</li>
									</ul>
								</dd>';
							}
						}else{
								$content .= 
								'<strong>'. $txt['user_posts_search_no_result'] . '</strong>';							
						}
				$content .= '
							</dl>
						</td>		
					</tr>									
				</table>					
			</td>
		</tr>';	
	}
	//End Search Result 	
	$content .= '	
	</table>';

	//The User Posts Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_user_posts_title'] .'</a> &copy; 2021 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'user-posts', $txt['up_module_title'] . ' - ' . $txt['up_module_user_posts_title']);
	
}

//User Posts Preview Template
function template_view_user_posts_preview()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings, $user_info;

	$return = '';
	
	if (empty($ultimateportalSettings['user_posts_internal_page']))
	{							
		$return .='	
		<table class="tborder" cellpadding="5" cellspacing="1" width="100%">					
			<tbody>
				<tr>
					<td class="catbg" width="100%" align="left">			
						'. $txt['ultport_button_preview'] .'
					</td>
				</tr>	
				<tr>
					<td class="windowbg" style="border-bottom:1px dashed;" width="100%" align="left">			
						<div style="width:100%">
							<div class="windowbg2" style="border:1px solid;float:left">
								'. $context['avatar-author'] .'
							</div>	
							&nbsp;					
							<strong>'. $context['title'] . '</strong>
							<br/>&nbsp;&nbsp;'. $txt['user_posts_by'] . '&nbsp;'. $context['link-author'] .'
							<br/>
							&nbsp; '.  (!empty($ultimateportalSettings['user_posts_field_type_posts']) ? $context['img_type_post'] : '') .'
							&nbsp;'. (!empty($ultimateportalSettings['user_posts_field_add_language']) ? $context['img_lang_post'] : '') .'
							<br /><br />
							<div>';
								if (!empty($ultimateportalSettings['user_posts_field_member_use_module']))
									$return .=	'&nbsp;&nbsp;<em>'. $context['added-for'] .'</em>';				
					$return .= '		
							</div>							
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<br />';
	}else{
		$return .= '
		<table class="tborder" cellpadding="5" cellspacing="1" width="100%">					
			<tbody>	
				<tr>
					<td colspan="2" class="catbg" width="100%" align="left">			
						'. $txt['ultport_button_preview'] .'
					</td>
				</tr>				
				<tr>
					<td colspan="2" class="catbg" width="100%" align="left">
						<div style="float:left;text-align:left">
							<h2><strong>'. $context['title'] .' </strong></h2>
						</div>	
						<div style="float:right;text-align:center">							
							'.  (!empty($ultimateportalSettings['user_posts_field_type_posts']) ? $context['img_type_post'] : '') .'&nbsp;
							'. (!empty($ultimateportalSettings['user_posts_field_add_language']) ? $context['img_lang_post'] : '') .'
						</div>
					</td>
				</tr>
				<tr>
					<td class="titlebg" width="55%" align="left">
						<img alt="" title="" style="float:left" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/information.png" width="20" height="20"/>&nbsp;&nbsp;'. $txt['up_information_title'] .'
					</td>
					<td class="titlebg" width="45%" align="center">
						'. $txt['up_cover_title'] .'
					</td>						
				</tr>					
				<tr>
					<td valign="top" class="windowbg" width="55%" align="left">
						<strong>'. $txt['up_post_title'] .':</strong>&nbsp;'. $context['title'] .'
						'. (!empty($ultimateportalSettings['user_posts_field_topic_author']) ? '<br /><strong>'. $txt['up_author_title'] .':</strong>&nbsp;'. $context['link-author'] : '') .'
						'. (!empty($ultimateportalSettings['user_posts_field_type_posts']) ? '<br /><strong>'. $txt['up_type_post'] .':</strong>&nbsp;'. $context['type-title'] : '') .'											
						'. (!empty($ultimateportalSettings['user_posts_field_add_language']) ? '<br /><strong>'. $txt['up_lang_post'] .':</strong>&nbsp;'. $context['lang-title'] : '') .'
						<div>';
						if (!empty($ultimateportalSettings['user_posts_field_member_use_module']))
							$return .=	$context['added-for'] .'<br />';						
				$return .= '		
						</div>
						<br />
						<strong>'. $txt['up_description_post'] .':</strong>&nbsp;<br />
							'. $context['description'] .'
					</td>
					<td valign="top" class="windowbg" width="45%" align="center">
						<a href="'. $userpost['link_topic'] .'">'. $context['cover_img'] .'</a>
					</td>						
				</tr>					
			</tbody>
		</table>
		<br/>';		
	}
	
	return $return;
}
//Form for Add User Posts
function template_user_posts_add()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings, $user_info;

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td align="left" width="100%">
			<table class="tborder" style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td align="left" width="100%">
						<strong>'. $context['news-linktree'] .'</strong>
					</td>
				</tr>
			</table>	
		</td>
	</tr>		
	<tr>
		<td width="100%">
		<br />';
		
		if(!empty($context['preview']))
		{
			$content .=	template_view_user_posts_preview();		
		}
			$content .=	'<br />
			<form method="post" action="'. $scripturl .'?action=user-posts;sa=add" accept-charset="'. $context['character_set'] .'">												
				<table width="100%" align="center" class="tborder" cellspacing="1" cellpadding="5" border="0">
					<tr>
						<td colspan="2" align="left" width="100%" class="titlebg">									
							'. $txt['user_posts_module_add_title'] .'
						</td>			
					</tr>';
			//TITLE
			$content .= '					
					<tr>
						<td width="50%" align="left" class="windowbg2">									
							'. $txt['user_posts_module_title'] .'
						</td>			
						<td width="50%" align="left" class="windowbg2">									
							<input type="text" name="title" size="85" maxlength="150" '. (!empty($context['title']) ? 'value="'. $context['title'] .'"' : '') .' />
						</td>			
					</tr>';
			
			//COVER
			if (!empty($ultimateportalSettings['user_posts_field_cover']))
			{
			$content .= '					
					<tr>
						<td width="50%" align="left" class="windowbg2">									
							'. $txt['user_posts_module_cover'] .'
						</td>			
						<td width="50%" align="left" class="windowbg2">									
							<input type="text" name="cover" size="85" '. (!empty($context['cover_file']) ? 'value="'. $context['cover_file'] .'"' : '') .' />
						</td>			
					</tr>';
			}				

			//LINK TOPIC
			$content .= '					
					<tr>
						<td width="50%" align="left" class="windowbg2">									
							'. $txt['user_posts_module_link_topic'] .'
						</td>			
						<td width="50%" align="left" class="windowbg2">									
							<input type="text" name="link_topic" size="85" '. (!empty($context['link_topic']) ? 'value="'. $context['link_topic'] .'"' : '') .' />
						</td>			
					</tr>';

			//AUTHOR TOPIC
			if (!empty($ultimateportalSettings['user_posts_field_topic_author']))
			{
			$content .= '					
					<tr>
						<td width="50%" align="left" class="windowbg2">									
							'. $txt['user_posts_module_topic_author'] .'
						</td>			
						<td width="50%" align="left" class="windowbg2">									
							<input type="text" name="topic_author" id="topic_author" size="10" '. (!empty($context['topic_author']) ? 'value="'. $context['topic_author'] .'"' : '') .' />&nbsp;
							<a href="'. $scripturl. '?action=findmember;input=topic_author;sesc='. $context['session_id']. '" onclick="return reqWin(this.href, 350, 400);"><img src="'. $settings['images_url']. '/icons/assist.gif" alt="'. $txt['user_posts_search_member']. '" /></a> <a href="'. $scripturl. '?action=findmember;input=topic_author;sesc='. $context['session_id']. '" onclick="return reqWin(this.href, 350, 400);">'. $txt['user_posts_search_member']. '</a>
						</td>			
					</tr>';
			}				

			//TYPE POST
			if (!empty($ultimateportalSettings['user_posts_field_type_posts']))
			{
			$content .= '					
					<tr>
						<td width="50%" valign="top" align="left" class="windowbg2">									
							'. $txt['user_posts_module_type'] .'
						</td>			
						<td width="50%" align="left" class="windowbg2">';
					if (!empty($context['view-type']))
					{
						foreach($context['type'] as $type)
						{	
							$content .= '
							<input type="radio" name="type" '. (($type['id'] == $context['id_type']) ? 'checked="checked"' : '') .' value="'. $type['icon'] .'#'. $type['title'] .'#'. $type['id'] .'">&nbsp;'. $type['title'] .'&nbsp;&raquo;&nbsp;'. $type['icon-img'] .'<br />';
						}	
					}else{
						$content .= '<strong>'. $txt['ultport_error_no_rows'] .'</strong>';
					}				
			$content .= '
						</td>			
					</tr>';
			}				

			//EXTRA FIELD LANGUAGE
			if (!empty($ultimateportalSettings['user_posts_field_add_language']))
			{
			$content .= '					
					<tr>
						<td width="50%" valign="top" align="left" class="windowbg2">
							'. $txt['user_posts_module_lang'] .'
						</td>			
						<td width="50%" align="left" class="windowbg2">';
					if (!empty($context['view-lang']))
					{
						foreach($context['lang'] as $lang)
						{	
							$content .= '
							<input type="radio" name="lang" '. (($lang['id'] == $context['id_lang']) ? 'checked="checked"' : '') .' value="'. $lang['icon'] .'#'. $lang['title'] .'#'. $lang['id'] .'" >&nbsp;'. $lang['title'] .'&nbsp;&raquo;&nbsp;'. $lang['icon-img'] .'<br />';
						}	
					}else{
						$content .= '<strong>'. $txt['ultport_error_no_rows'] .'</strong>';
					}				
			$content .= '
						</td>			
					</tr>';
			}				

			//THE POST DESCRIPTION
			if (!empty($ultimateportalSettings['user_posts_field_description']))
			{
				$content .= '					
					<tr>
						<td colspan="2" width="100%" align="left" class="windowbg2">									
							'. $txt['user_posts_module_description'] .'
						</td>			
					</tr>					
					<tr>
						<td colspan="2" width="100%" align="center" class="windowbg2">									
							<textarea id="elm1" name="elm1" rows="15" cols="80" style="width: 100%">'. (!empty($context['description']) ? $context['description'] : '') .'</textarea>
						</td>			
					</tr>';
			}
							
			$content .= '		
				</table>
				<table width="100%" align="center" cellspacing="1" cellpadding="5" border="0">
					<tr>
						<td colspan="2" align="left">	
							<input type="hidden" name="id_member_add" value="'. $user_info['id'] .'" />						
							<input type="hidden" name="username_add" value="'. $user_info['username'] .'" />																
							<input type="submit" name="save" value="'.$txt['ultport_button_add'].'" />&nbsp;
							<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
							<input type="submit" name="preview" value="'.$txt['ultport_button_preview'].'" />
						</td>
					</tr>
				</table>
			</form>';

	$content .= '		
		</td>
	</tr>
</table>';
		

	//The User Posts Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_user_posts_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'up-add', $txt['user_posts_module_add_title']);

}

//Form for Edit User Posts
function template_user_posts_edit()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings, $user_info;

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td align="left" width="100%">
			<table class="tborder" style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td align="left" width="100%">
						<strong>'. $context['news-linktree'] .'</strong>
					</td>
				</tr>
			</table>	
		</td>
	</tr>		
	<tr>
		<td width="100%">
		<br />';
			//Preview
			if(!empty($context['preview']))
			{
				$content .=	template_view_user_posts_preview();		
			}
			$content .=	'
			<form method="post" action="'. $scripturl .'?action=user-posts;sa=edit;id='. $context['id'] .'" accept-charset="'. $context['character_set'] .'">												
				<table width="100%" align="center" class="tborder" cellspacing="1" cellpadding="5" border="0">
					<tr>
						<td colspan="2" align="left" width="100%" class="titlebg">									
							'. $txt['user_posts_module_add_title'] .'
						</td>			
					</tr>';
			foreach ($context['userpost'] as $userpost)
			{
				//TITLE
				$content .= '					
						<tr>
							<td width="50%" align="left" class="windowbg2">									
								'. $txt['user_posts_module_title'] .'
							</td>			
							<td width="50%" align="left" class="windowbg2">									
								<input type="text" name="title" size="85" maxlength="150" value="'. (empty($context['preview']) ? $userpost['title'] : $context['title']) .'"/>
							</td>			
						</tr>';
				
				//COVER
				if (!empty($ultimateportalSettings['user_posts_field_cover']))
				{
				$content .= '					
						<tr>
							<td width="50%" align="left" class="windowbg2">									
								'. $txt['user_posts_module_cover'] .'
							</td>			
							<td width="50%" align="left" class="windowbg2">									
								<input type="text" name="cover" size="85" value="'. (empty($context['preview']) ? $userpost['cover'] : $context['cover_url']) .'"/>
							</td>			
						</tr>';
				}				
	
				//LINK TOPIC
				$content .= '					
						<tr>
							<td width="50%" align="left" class="windowbg2">									
								'. $txt['user_posts_module_link_topic'] .'
							</td>			
							<td width="50%" align="left" class="windowbg2">									
								<input type="text" name="link_topic" size="85" value="'. (empty($context['preview']) ? $userpost['link_topic'] : $context['link_topic']) .'"/>
							</td>			
						</tr>';
	
				//AUTHOR TOPIC
				if (!empty($ultimateportalSettings['user_posts_field_topic_author']))
				{
				$content .= '					
						<tr>
							<td width="50%" align="left" class="windowbg2">									
								'. $txt['user_posts_module_topic_author'] .'
							</td>			
							<td width="50%" align="left" class="windowbg2">									
								<input type="text" name="topic_author" id="topic_author" size="10" value="'. (empty($context['preview']) ? $userpost['author'] : $context['topic_author']) .'"/>&nbsp;
								<a href="'. $scripturl. '?action=findmember;input=topic_author;sesc='. $context['session_id']. '" onclick="return reqWin(this.href, 350, 400);"><img src="'. $settings['images_url']. '/icons/assist.gif" alt="'. $txt['user_posts_search_member']. '" /></a> <a href="'. $scripturl. '?action=findmember;input=topic_author;sesc='. $context['session_id']. '" onclick="return reqWin(this.href, 350, 400);">'. $txt['user_posts_search_member']. '</a>
							</td>			
						</tr>';
				}				
	
				//TYPE POST
				if (!empty($ultimateportalSettings['user_posts_field_type_posts']))
				{
				$content .= '					
						<tr>
							<td width="50%" valign="top" align="left" class="windowbg2">									
								'. $txt['user_posts_module_type'] .'
							</td>			
							<td width="50%" align="left" class="windowbg2">';
						if (!empty($context['view-type']))
						{
							foreach($context['type'] as $type)
							{	
								$content .= '
								<input type="radio" name="type" value="'. $type['icon'] .'#'. $type['title'] .'#'. $type['id'] .'" '. (empty($context['preview']) ? (($type['id'] == $userpost['id_type_post']) ? 'checked="checked"' : '') : (($type['id'] == $context['id_type']) ? 'checked="checked"' : '')) .'>&nbsp;'. $type['title'] .'&nbsp;&raquo;&nbsp;'. $type['icon-img'] .'<br />';
							}	
						}else{
							$content .= '<strong>'. $txt['ultport_error_no_rows'] .'</strong>';
						}				
				$content .= '
							</td>			
						</tr>';
				}				
	
				//EXTRA FIELD LANGUAGE
				if (!empty($ultimateportalSettings['user_posts_field_add_language']))
				{
				$content .= '					
						<tr>
							<td width="50%" valign="top" align="left" class="windowbg2">
								'. $txt['user_posts_module_lang'] .'
							</td>			
							<td width="50%" align="left" class="windowbg2">';
						if (!empty($context['view-lang']))
						{
							foreach($context['lang'] as $lang)
							{	
								$content .= '
								<input type="radio" name="lang" value="'. $lang['icon'] .'#'. $lang['title'] .'#'. $lang['id'] .'" '. (empty($context['preview']) ? (($userpost['id_lang_post'] == $lang['id']) ? 'checked="checked"' : '') : (($context['id_lang'] == $lang['id']) ? 'checked="checked"' : '')) .'>&nbsp;'. $lang['title'] .'&nbsp;&raquo;&nbsp;'. $lang['icon-img'] .'<br />';
							}	
						}else{
							$content .= '<strong>'. $txt['ultport_error_no_rows'] .'</strong>';
						}				
				$content .= '
							</td>			
						</tr>';
				}				
	
				//THE POST DESCRIPTION
				if (!empty($ultimateportalSettings['user_posts_field_description']))
				{
					$content .= '					
						<tr>
							<td colspan="2" width="100%" align="left" class="windowbg2">									
								'. $txt['user_posts_module_description'] .'
							</td>			
						</tr>					
						<tr>
							<td colspan="2" width="100%" align="center" class="windowbg2">									
								<textarea id="elm1" name="elm1" rows="15" cols="80" style="width: 100%">'. (empty($context['preview']) ? $userpost['description'] : $context['description']) .'</textarea>
							</td>			
						</tr>';
				}
				$content .= '
				<input type="hidden" name="id_member_add" value="'. $userpost['id_member_add'] .'" />						
				<input type="hidden" name="username_add" value="'. $userpost['username_add'] .'" />';
			}//End ForEach princpal				
			$content .= '		
				</table>
				<table width="100%" align="center" cellspacing="1" cellpadding="5" border="0">
					<tr>
						<td colspan="2" align="left">	
							<input type="hidden" name="id_member_updated" value="'. $user_info['id'] .'" />						
							<input type="hidden" name="username_updated" value="'. $user_info['username'] .'" />																
							<input type="submit" name="save" value="'.$txt['ultport_button_edit'].'" />&nbsp;
							<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
							<input type="submit" name="preview" value="'.$txt['ultport_button_preview'].'" />							
						</td>
					</tr>
				</table>
			</form>';

	$content .= '		
		</td>
	</tr>
</table>';
		

	//The User Posts Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_user_posts_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'up-add', $txt['user_posts_module_add_title']);

}

//Show the Ultimate Portal - Module User Posts - View Single 
function template_user_posts_view_single()
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

	$content .= '<br /><div style="padding:3px;border:1px dashed;"><strong>'. $context['news-linktree'] .'</strong></div>
						<br />';
			
		foreach ($context['userpost'] as $userpost)
		{		
			$content .= '<div>
		<div class="description"><table><tr><td><img alt="" title="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/information.png" width="15" /></td><td style="font-size: 14px;font-weight:bold;"> '. $txt['up_information_title'] .'</td></tr></table>
		<hr />
						<div style="float:right;text-align:center">							
							'.  (!empty($ultimateportalSettings['user_posts_field_type_posts']) ? $userpost['type'] : '') .'
							'. (!empty($ultimateportalSettings['user_posts_field_add_language']) ? $userpost['lang'] : '') .'
						</div>
					
						<strong>'. $txt['up_post_title'] .': </strong>'. $userpost['title'] .'
						'. (!empty($ultimateportalSettings['user_posts_field_topic_author']) ? '<br /><strong>'. $txt['up_author_title'] .': </strong>'. $userpost['link-author'] : '') .'
						'. (!empty($ultimateportalSettings['user_posts_field_type_posts']) ? ' | <strong>'. $txt['up_type_post'] .': </strong>'. $userpost['type-title'] : '') .'											
						'. (!empty($ultimateportalSettings['user_posts_field_add_language']) ? ' | <strong>'. $txt['up_lang_post'] .': </strong>'. $userpost['lang-title'] : '') .'
						<div>';
						if (!empty($ultimateportalSettings['user_posts_field_member_use_module']))
							$content .= 	$userpost['added-for'] .'<br />';
						
						if (!empty($ultimateportalSettings['user_posts_field_member_updated_module']) && !empty($userpost['id_member_updated']))				
							$content .= 	$userpost['updated-for'] .'<br />';											
					$content .= '		
							</div></div>
						<span class="clear upperframe"><span></span></span>
				 <div class="roundframe"><table style="font-family:fantasy;"><tr><td valign="top"><a href="'. $userpost['link_topic'] .'">'. $userpost['cover-img'] .'</a></td><td valign="top" style="border-radius:8px;">
						<strong style="font-size:14px;text-decoration:underline;">'. $txt['up_description_post'] .':</strong><br /><br />
							'. $userpost['description'] .'
						</td></tr></table><br /></div><span class="lowerframe"><span></span></span>
						<div>';
						
					$content .= '<div style="padding:3px;font-family:arial;" align="center"><img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/view.png" alt="" title="" />
						<a href="'. $userpost['link_topic'] .'">'. $txt['up_view_post'] .'</a> - ';
					if (!empty($user_info['up-modules-permissions']['user_posts_moderate']) || $user_info['is_admin'])
					{										
						$content .= '
						<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png" alt="" title="" />
						<a href="'.$scripturl .'?action=user-posts;sa=edit;id='. $userpost['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a> -
						<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png" alt="" title="" />
						<a onclick="return makesurelink()" href="'.$scripturl .'?action=user-posts;sa=delete;id='. $userpost['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
					}
					//Social Bookmarks
	$content .= '
	'. (!empty($ultimateportalSettings['user_posts_social_bookmarks']) ? $context['social_bookmarks'] : '') .'</div></div></div>';

		}


	

	//The User Posts Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_user_posts_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'user-posts', $txt['up_module_title'] . ' - ' . $txt['up_module_user_posts_title']);
	
}

?>