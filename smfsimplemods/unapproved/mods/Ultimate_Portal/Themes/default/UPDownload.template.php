<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

//Show the Ultimate Portal - Module Download - Main
function template_down_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	// Create the button set...
	$normal_buttons = array(
		'add_file' => array('condition' => (!empty($user_info['up-modules-permissions']['download_add']) || $user_info['is_admin']), 'text' => 'down_module_new_file', 'lang' => true, 'url' => $scripturl .'?action=downloads;sa=new;sesc=' . $context['session_id'], 'active' => true),
		'unapproved_files' => array('condition' => (!empty($user_info['up-modules-permissions']['download_moderate']) || $user_info['is_admin']), 'text' => 'down_module_unapproved_title', 'lang' => true, 'url' => $scripturl .'?action=downloads;sa=unapproved', 'active' => true),		
		'view_stats' => array('condition' => ($user_info['is_guest'] || !$user_info['is_guest']), 'text' => 'down_module_stats_title', 'lang' => true, 'url' => $scripturl .'?action=downloads;sa=stats'),		
	);

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td  align="left" width="100%">
			<table  style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td  align="left" width="90%">
						<strong>'. $context['down-linktree'] .'</strong>
						<div style="float:right" class="UPpagesection">
							'. up_template_button_strip($normal_buttons, 'right') .'
						</div>						
					</td>
				</tr>
			</table>
		</td>
	</tr>';

	$content .= '
	<tr>
		<td  align="left" width="100%">
			<br />
			<table  cellpadding="5" cellspacing="1" width="100%">		
				<tr>
					<td align="left" width="100%">
						<h3 class="DownloadH3">'. $txt['down_module_section_title'] .'</h3><br />';
							$div_float = true;							
							if ($context['view'])
							{
								$content .= '			
								<table  cellpadding="5" cellspacing="2" width="100%">
									<tr>
										<td valign="top" align="left" width="50%">';							
									$i = 1;	
									foreach($context['dowsect'] as $dowsect)
									{
										$content .= '		
											<div style="width:49.5%;float:'. ($div_float ? 'left' : 'right') .';">
												'. $dowsect['icon-img'] .'
												<strong><a href="'. $scripturl .'?action=downloads;sa=search;type='. $dowsect['id'] .'">'. $dowsect['title'] .'</a></strong> ('. (!empty($dowsect['total_files']) ? $dowsect['total_files'] : '0') .') &nbsp; <span style="cursor:pointer" onclick="openAjaxConfirm_'. $dowsect['id'] .'()"><img style="vertical-align:middle" src="'. $settings['default_theme_url'] .'/images/ultimate-portal/load.png" alt="" title="'. $txt['down_module_more_popular_title'] . ' | ' . $txt['down_module_last_files_title'] .'" width="20" height="20" /></span>
												<script type="text/javascript">
													function openAjaxConfirm_'. $dowsect['id'] .'() 
													{
														Dialog.alert({url: "'. $scripturl .'?action=downloads;sa=view-more-last;section='. $dowsect['id'] .'", options: {method: \'get\'}}, {className: "alphacube", width:540, okLabel: "'. $txt['ultport_close'] .'"});													
													}
												</script>
												<br />
												'. $dowsect['description'] .'
												<br />
											</div>';
											$div_float = !$div_float;
											$i++;
											if($i == 3)
											{
												$content .='<br class="clear"/>';
												$i = 1;
											}
									}
								$content .= '
										</td>
									</tr>
								</table>';				
							}	
					//Close DIV Downloadbrowse		
					$content .= '<br /><br />
					<form method="post" action="'. $scripturl .'?action=downloads;sa=search" id="DownloadSearch">
						<input type="text" size="50" maxlength="100" value="" id="search" name="basic_search"/>
						<input type="submit" value="'. $txt['down_module_search'] .'"/>
					</form>
					</td>
				</tr>		
			</table><br /><br />
		</td>
	</tr>
</table>';		
				
	//The Download Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'up-download', $txt['down_module_title'] . ' - ' . $txt['down_module_title2']);
	
}

//Show the Ultimate Portal - Module Download - Main
function template_down_new()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td  align="left" width="100%">
			<table  style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td colspan="2"  align="left" width="100%">
						<strong>'. $context['down-linktree'] .'</strong>
					</td>
				</tr>
			</table>
		</td>
	</tr>';

	$content .= '
	<tr>
		<td align="left" width="100%">		
		<br />
			<form id="newform" enctype="multipart/form-data" name="newform" method="post" action="'. $scripturl .'?action=downloads;sa=new" accept-charset="'. $context['character_set'] .'">														
				<table cellpadding="5" cellspacing="1" width="100%">
					<tr>
						<td class="catbg" colspan="2"  align="left" width="100%">
							<strong>'. $txt['down_module_new_file_title'] .'</strong>
						</td>
					</tr>';

			//Needs approval the archive?
			if (!empty($ultimateportalSettings['download_enable_approved_file']) && (!$user_info['is_admin'] && empty($user_info['up-modules-permissions']['download_moderate'])))
			{
			$content .= '
					<tr>
						<td style="color:#F00;background:#E8EAEE" colspan="2"  align="left" width="100%">
							'. $txt['down_module_warning'] .'
						</td>
					</tr>';
			}
								
			$content .= '					
					<tr>
						<td  align="right" width="20%">
							'. $txt['down_module_file_name'] .'
						</td>
						<td align="left" width="80%">
							<input type="text" style="width:96%;" value="" name="name" size="100" maxlength="100" />
						</td>
					</tr>
					<tr>
						<td  align="right" width="20%">
							'. $txt['down_module_file_description'] .'
						</td>
						<td  align="left" width="80%">
							&nbsp;
						</td>
					</tr>
					<tr>
						<td colspan="2"  align="left" width="100%">
							<div id="'. $context['bbcBox_container'] .'"></div>			
							<div id="'. $context['smileyBox_container'] .'"></div>											
							'. up_template_control_richedit($context['post_box_name'],$context['smileyBox_container'],$context['bbcBox_container']) .'
						</td>
					</tr>
					<tr>
						<td valign="top"  align="right" width="20%">
							'. $txt['down_module_file_small_description'] .'
						</td>
						<td valign="top"  align="left" width="80%">
							<input type="text" size="100" maxlength="100" style="width:86%;" value="" name="small_description"/>
						</td>
					</tr>
					<tr>
						<td valign="top"  align="right" width="20%">
							'. $txt['down_module_file_section'] .'
						</td>
						<td valign="top"  align="left" width="80%">
							<select name="section" size="1">';
							foreach ($context['dowsect'] as $dowsect)
								$content .= '
								<option value="'. $dowsect['id'] .'">'. $dowsect['title'] .'</option>';
					$content .= '
							</select>		
						</td>
					</tr>
					<tr>
						<td class="catbg" colspan="2"  align="left" width="100%">
							<strong>'. $txt['down_module_upload_file_title'] .'</strong>
						</td>
					</tr>
					<tr>
						<td valign="top"  align="right" width="20%">
							'. $txt['down_module_file_upload'] .'
						</td>
						<td valign="top"  align="left" width="80%">
							<input type="file" size="48" name="attachment[]" />
							<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
								var allowed_attachments = 9999;
								function addAttachment()
								{
									setOuterHTML(document.getElementById("moreAttachments"), \'<br /><input type="file" size="48" name="attachment[]" /><span id="moreAttachments"></span>\');
									allowed_attachments = allowed_attachments - 1;

									return true;
								}
							// ]]></script>
							<span id="moreAttachments"></span>
							<br />
							 <a href="javascript:addAttachment(); void(0);">['. $txt['down_module_file_upload_other'] .']</a><br />
							<noscript><input type="file" size="48" name="attachment[]" /><br /></noscript>
							<br/>
							'. str_replace('[SIZE]', $ultimateportalSettings['download_file_max_size'], $txt['down_module_file_upload_max_size']) .'
						</td>
					</tr>
					<tr>
						<td class="catbg" align="center" colspan="2" width="100%">	
							<input type="hidden" name="save" value="ok" />						
							<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
							<input type="submit" name="'. $txt['ultport_button_add'] .'" value="'. $txt['ultport_button_add'] .'" />
						</td>
					</tr>					
				</table>
			</form>	
		</td>
	</tr>
</table>';
		
	//The Download Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'up-download', $txt['down_module_title'] . ' - ' . $txt['down_module_title2']);
	
}

//Show the Ultimate Portal - Module Download - Search
function template_down_search()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td  align="left" width="100%">
			<table  style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td  align="left" width="100%">
						<strong>'. $context['down-linktree'] .'</strong>
					</td>
				</tr>
			</table>
		</td>
	</tr>';

	$content .= '
	<tr>
		<td  align="left" width="100%"><br/>	
			<table cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td align="left" width="100%">					
						<h3 class="DownloadH3">'. $txt['down_module_search_page_title'] .'</h3><br/>	
						'. (!empty($context['whatsearch']) ? $txt['down_search'] . ': '. $context['whatsearch'] . '<br/>' : '' ) .'
						'. (!empty($context['view-downsearch']) ? '<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] : ''). '';	
					if(!empty($context['view-downsearch']))
					{	
						$content .='
						<dl id="DownBrowsing">';
						foreach($context['downsearch'] as $downsearch)
						{
							if ($downsearch['can_view'] && !empty($downsearch['title']))
							{
								$content .= '
								<dt '. (empty($downsearch['approved']) ? 'class="not_approved"' : '') .'>
									'. $context['icon-img'] .'&nbsp;<a href="'. $scripturl .'?action=downloads;sa=view;download='. $downsearch['id_files'] .'">'. $downsearch['title'] .'</a>
								</dt>
								<dd '. (empty($downsearch['approved']) ? 'class="not_approved"' : '') .'>
									<p>'. $downsearch['small_description'] .'</p>
									<ul>
										<li>'. $txt['down_author'] .': <a href="'. $scripturl .'?action=downloads;sa=profile;u='. $downsearch['id_member'] .'">'. $downsearch['membername'] .'</a></li>
										<li>'. $txt['down_date_created'] .': '. $downsearch['date_created'] .'</li>
										<li>'. $txt['down_date_updated'] .': '. $downsearch['date_updated'] .'</li>
										<li>'. $txt['down_total_downloads'] .': '. $downsearch['total_downloads'] .'</li>
									</ul>
								</dd>';							
							}
						}
						$content .='
						</dl>';
					}
						
	$content .= '		
					</td>
				</tr>		
				<tr>
					<td  align="left" width="100%">					
					'. (!empty($context['view-downsearch']) ? '<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] : ''). ' 
					</td>
				</tr>	
			</table>			
		</td>
	</tr>
</table>';
		
	//The Download Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'up-download', $txt['down_module_title'] . ' - ' . $txt['down_module_title2']);
	
}

//Show the Ultimate Portal - Module Download - Search
function template_down_unapproved_files()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td  align="left" width="100%">
			<table  style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td  align="left" width="100%">
						<strong>'. $context['down-linktree'] .'</strong>
					</td>
				</tr>
			</table>
		</td>
	</tr>';

	$content .= '
	<tr>
		<td  align="left" width="100%"><br/>	
			<table cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td align="left" width="100%">					
						<h3 class="DownloadH3">'. $txt['down_module_unapproved_title'] .'</h3>';	
					if(!empty($context['view-downsearch']))
					{	
						$content .='
						<dl id="DownBrowsing">';
						foreach($context['downsearch'] as $downsearch)
						{
							if ($downsearch['can_view'] && !empty($downsearch['title']))
							{
								$content .= '
								<dt '. (empty($downsearch['approved']) ? 'class="not_approved"' : '') .'>
									'. $context['icon-img'] .'&nbsp;<a href="'. $scripturl .'?action=downloads;sa=view;download='. $downsearch['id_files'] .'">'. $downsearch['title'] .'</a>
								</dt>
								<dd '. (empty($downsearch['approved']) ? 'class="not_approved"' : '') .'>
									<p>'. $downsearch['small_description'] .'</p>
									<ul>
										<li>'. $txt['down_author'] .': <a href="'. $scripturl .'?action=downloads;sa=profile;u='. $downsearch['id_member'] .'">'. $downsearch['membername'] .'</a></li>
										<li>'. $txt['down_date_created'] .': '. $downsearch['date_created'] .'</li>
										<li>'. $txt['down_date_updated'] .': '. $downsearch['date_updated'] .'</li>
										<li>'. $txt['down_total_downloads'] .': '. $downsearch['total_downloads'] .'</li>
									</ul>
								</dd>';							
							}
						}
						$content .='
						</dl>';
					}
						
	$content .= '		
					</td>
				</tr>		
				<tr>
					<td  align="left" width="100%">					
					'. (!empty($context['view-downsearch']) ? '<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] : ''). ' 
					</td>
				</tr>	
			</table>			
		</td>
	</tr>
</table>';
		
	//The Download Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'up-download', $txt['down_module_title'] . ' - ' . $txt['down_module_title2']);
	
}

//Show the Ultimate Portal - Module Download - Search
function template_view_file()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;
	global $boardurl;

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
			<table  style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td  align="left" width="100%">
						<strong>'. $context['down-linktree'] .'</strong>
					</td>
				</tr>
			</table>
		</td>
	</tr>';

	$content .= '
	<tr>
		<td align="left" width="100%"><br/>	
			<table cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td align="left" width="100%">
						<h3 style="color:#EB9112" class="DownloadH3">'. $context['filetitle'] .'&nbsp;&nbsp;';

					//Is admin or is the member Edt this file?
					if (($user_info['is_admin']) || ($context['id_member'] == $user_info['id']) || !empty($user_info['up-modules-permissions']['download_moderate']))						
					{
						$content .='
							<a href="'. $scripturl .'?action=downloads;sa=edit;id='. $context['id_files'] .';sesc=' . $context['session_id'].'">
								<img alt="" title="'. $txt['ultport_button_edit'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png"/>
							</a>';
					}		
					if ($user_info['is_admin']  || !empty($user_info['up-modules-permissions']['download_moderate']))						
					{							
						$content .='							
							<a onclick="return makesurelink()" href="'. $scripturl .'?action=downloads;sa=delete;id='. $context['id_files'] .';sesc=' . $context['session_id'].'">	
								<img alt="" title="'. $txt['ultport_button_delete'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png"/>
							</a>';
					}							

					//Is admin? then can Approved this file
					if (($user_info['is_admin'] || !empty($user_info['up-modules-permissions']['download_moderate'])) && empty($context['approved']))						
					{
						$content .='
							<a href="'. $scripturl .'?action=downloads;sa=approved;id='. $context['id_files'] .'">
								<img alt="" title="'. $txt['down_file_approved'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/download/approved.png"/>
							</a>';
					}							
							
					$content .= '			
						</h3> 
						<br/>	
						<div id="DownloadSecondarybody">
							<div id="downloadsite">';

								if(empty($context['approved']))
								{
								$content .='
									<div id="warning">
										<img alt="" width="30" height="30" title="'. $txt['down_file_warning_no_approved'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/download/stop.png"/>
										
											'. $txt['down_file_warning_no_approved'] .'
										
									</div>';
								}
								
							$content .='								
								<div id="download">
									<h3>'. $txt['down_file_title_downloads'] .'</h3>
									<ul class="item windowbg">';
									if(!empty($context['view-attachments']))
									{
										foreach($context['attachment'] as $attachment)
										{
											$content .= '
											<li><a href="'. $scripturl .'?action=downloads;sa=download;id='. $attachment['ID_ATTACH'] .'"">'. $attachment['filename'] .'</a> ('. $attachment['size'] .'KB) ['. $attachment['downloads'] .']</li>';
										}
									}else{
										$content .= '
										<li>'. $txt['down_file_no_attachment'] .'</li>';
									}								
							$content .= '
									</ul>
								</div>
								<div id="displaydownload">
									<dl id="details">
										<dt>'. $txt['down_module_file_name'] .':</dt>
										<dd>'. $context['filetitle'] .'</dd><br/>
										<dt>'. $txt['down_author'] .':</dt>
										<dd><a href="'. $scripturl .'?action=profile;u='. $context['id_member'] .'">'. $context['membername'] .'</a>&nbsp;<a href="'. $scripturl .'?action=downloads;sa=profile;u='. $context['id_member'] .'"><img alt="" title="'. $txt['down_file_uploaded_user'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/download/filter.gif" /></a></dd><br/>			
										<dt>'. $txt['down_file_title_section'] .':</dt>
										<dd>'. $context['title'] .'</dd><br/>									
										<dt>'. $txt['down_date_created'] .':</dt>
										<dd>'. $context['date_created'] .'</dd><br/>									
										<dt>'. $txt['down_date_updated'] .':</dt>
										<dd>'. $context['date_updated'] .'</dd><br/><br/>																		
										<dt>'. $txt['down_total_downloads'] .':</dt>
										<dd>'. $context['filetotal_downloads'] .'</dd><br/>
									</dl>
								</div>';
					if (!empty($context['id_board']) && !empty($context['id_topic']))
					{
						// Create the button set...
						$normal_buttons = array(
							'topic' => array('condition' => !empty($context['id_topic']), 'text' => 'down_file_topic_link', 'lang' => true, 'url' => $scripturl .'?topic='. $context['id_topic'] .'.0', 'active' => true),
						);
						$content .= '
						<div style="font-size:14px" class="UPpagesection">
							'. up_template_button_strip($normal_buttons, 'top') .'
						</div>';						
					}			
					$content .='			
							</div>
							<div id="descript">';
					//Attach Image?
					if(!empty($context['view_attach_image']))
					{
						$content .='			
						<h3>'. $txt['down_module_image_description'] .'</h3>
						<br />';						
						$content .= '
						<marquee width="100%" scrolldelay="100" direction="left" loop="infinite" onmouseover="this.stop()" onmouseout="this.start()">';
						foreach ($context['full_image'] as $imageattachment)
						{
							foreach ($imageattachment['thumbnail'] as $thumbimage)
							{
								$content .= '
								<a href="'. $boardurl .'/up-attachments/downloads/photos/'. $imageattachment['filename'] .'" target="_blank"><img src="'. $boardurl .'/up-attachments/downloads/thumbnails/'. $thumbimage['filename'] .'" alt="'. $context['filetitle'] .'" /></a>';
							}							
						}
						$content .= '
						</marquee>
						<br />';						
					}
					
					$content .='
								<h3>'. $txt['down_module_file_description'] .'</h3>
								<br/><br/>
								'. $context['filedescription'] .'
							</div>								
						</div><br /><br />						
					</td>
				</tr>		
			</table>			
		</td>
	</tr>
</table>';
		
	//The Download Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'up-download', $txt['down_module_title'] . ' - ' . $txt['down_module_title2']);
	
}

//Show the Ultimate Portal - Module Download - Edit
function template_down_edit()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td  align="left" width="100%">
			<table  style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td colspan="2"  align="left" width="100%">
						<strong>'. $context['down-linktree'] .'</strong>
					</td>
				</tr>
			</table>
		</td>
	</tr>';

	$content .= '
	<tr>
		<td align="left" width="100%">		
		<br />
			<form id="newform" enctype="multipart/form-data" name="newform" method="post" action="'. $scripturl .'?action=downloads;sa=edit" accept-charset="'. $context['character_set'] .'">														
				<table cellpadding="5" cellspacing="1" width="100%">
					<tr>
						<td class="catbg" colspan="2"  align="left" width="100%">
							<strong>'. $txt['down_module_new_file_title'] .'</strong>
						</td>
					</tr>';

			//Needs approval the archive?
			if (!empty($ultimateportalSettings['download_enable_approved_file']) && empty($context['approved']))
			{
			$content .= '
					<tr>
						<td style="color:#F00;background:#E8EAEE" colspan="2"  align="left" width="100%">
							'. $txt['down_module_warning'] .'
						</td>
					</tr>';
			}
								
			$content .= '					
					<tr>
						<td  align="right" width="20%">
							'. $txt['down_module_file_name'] .'
						</td>
						<td  align="left" width="80%">
							<input type="text" style="width:96%;" value="'. $context['filetitle'] .'" name="name"/>
						</td>
					</tr>
					<tr>
						<td  align="right" width="20%">
							'. $txt['down_module_file_description'] .'
						</td>
						<td  align="left" width="80%">
							&nbsp;
						</td>
					</tr>
					<tr>
						<td colspan="2"  align="left" width="100%">
							<div id="'. $context['bbcBox_container'] .'"></div>			
							<div id="'. $context['smileyBox_container'] .'"></div>											
							'. up_template_control_richedit($context['post_box_name'],$context['smileyBox_container'],$context['bbcBox_container']) .'
						</td>
					</tr>
					<tr>
						<td valign="top"  align="right" width="20%">
							'. $txt['down_module_file_small_description'] .'
						</td>
						<td valign="top"  align="left" width="80%">
							<input type="text" size="100" maxlength="100" style="width:86%;" value="'. $context['small_description'] .'" name="small_description"/>
						</td>
					</tr>
					<tr>
						<td valign="top"  align="right" width="20%">
							'. $txt['down_module_file_section'] .'
						</td>
						<td valign="top"  align="left" width="80%">
							<select name="section" size="1">';
							foreach ($context['dowsect'] as $dowsect)
								$content .= '
								<option '. (($dowsect['id'] == $context['id_section']) ? 'selected="selected"' : '') .' value="'. $dowsect['id'] .'">'. $dowsect['title'] .'</option>';
					$content .= '
							</select>		
						</td>
					</tr>
					<tr>
						<td class="catbg" colspan="2"  align="left" width="100%">
							<strong>'. $txt['down_module_upload_file_title'] .'</strong>
						</td>
					</tr>';
				
				$content .='
					<tr id="postAttachment">
						<td align="right" valign="top">
							<b>'. $txt['down_attach'] .':</b>
						</td>
						<td >
							'. $txt['smf130'] .':<br />';
						foreach ($context['attachment'] as $attachment)
							$content .= '
							<input type="checkbox" name="attach_del[]" value="'. $attachment['ID_ATTACH'] .'" class="check" /> '. $attachment['filename'] .'<br />';
					//Attach Image?
					if(!empty($context['view_attach_image']))
					{
						foreach ($context['full_image'] as $imageattachment)
						{
							$content .= '
							<input type="checkbox" name="attach_del[]" value="'. $imageattachment['ID_ATTACH'] .'" class="check" /> '. $imageattachment['filename'] .'<br />';							
							foreach ($imageattachment['thumbnail'] as $thumbimage)
							{
								$content .= '
								<input type="checkbox" name="attach_del[]" value="'. $thumbimage['ID_ATTACH'] .'" class="check" /> '. $thumbimage['filename'] .'<br />';							
							}							
						}						
					}
						$content .='
							<br />
						</td>
					</tr>';

				$content .= '					
					<tr>
						<td valign="top"  align="right" width="20%">
							'. $txt['down_module_file_upload'] .'
						</td>
						<td valign="top"  align="left" width="80%">
							<input type="file" size="48" name="attachment[]" />
							<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
								var allowed_attachments = 9999;
								function addAttachment()
								{
									setOuterHTML(document.getElementById("moreAttachments"), \'<br /><input type="file" size="48" name="attachment[]" /><span id="moreAttachments"></span>\');
									allowed_attachments = allowed_attachments - 1;

									return true;
								}
							// ]]></script>
							<span id="moreAttachments"></span>
							<br />
							 <a href="javascript:addAttachment(); void(0);">['. $txt['down_module_file_upload_other'] .']</a><br />
							<noscript><input type="file" size="48" name="attachment[]" /><br /></noscript>
							<br/>
							'. str_replace('[SIZE]', $ultimateportalSettings['download_file_max_size'], $txt['down_module_file_upload_max_size']) .'
						</td>
					</tr>
					<tr>
						<td class="catbg" align="center" colspan="2" width="100%">
							<input type="hidden" name="ID_FILE" value="'. $context['id_files'] .'" />							
							<input type="hidden" name="save" value="ok" />						
							<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
							<input type="submit" name="'. $txt['ultport_button_edit'] .'" value="'. $txt['ultport_button_edit'] .'"/>
						</td>
					</tr>					
				</table>
			</form>	
		</td>
	</tr>
</table>';
		
	//The Download Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'up-download', $txt['down_module_title'] . ' - ' . $txt['down_module_title2']);
	
}

//Show the Ultimate Portal - Module Download - Stats
function template_down_stats()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td  align="left" width="100%">
			<table  style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td  align="left" width="90%">
						<strong>'. $context['down-linktree'] .'</strong>
					</td>
				</tr>
			</table>
		</td>
	</tr>';

	$content .= '
	<tr>
		<td align="left" width="100%">
			<div id="statistics" class="main_section">
				<h4 class="titlebg"><span class="left"></span>
					'. $txt['down_module_stats_title'] .'
				</h4>
				<div class="flow_hidden">
					<div id="stats_left">
						<h3 class="catbg"><span class="left"></span>
							<img src="'. $settings['images_url'] .'/stats_posters.gif" class="icon" alt="" /> '. $txt['down_top_uploader'] .'
						</h3>						
						<div class="windowbg">
							<span class="topslice"><span></span></span>
							<div class="content">';
						if (!empty($context['view-top-uploader']))
						{
							$content .='
								<dl class="stats">';
							foreach ($context['down_top_user'] as $TopUploadMember)
							{
								$content .='
									<dt>
										'. $TopUploadMember['profile'] .'&nbsp;<a href="'. $scripturl .'?action=downloads;sa=profile;u='. $TopUploadMember['id_member'] .'"><img alt="" style="vertical-align: middle;" title="'. $txt['down_file_uploaded_user'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/download/filter.gif" /></a>
									</dt>
									<dd class="statsbar">
										<span class="left"></span>
										<div style="width: '. $TopUploadMember['upload_percent'] .'px;" class="stats_bar"></div>
										<span class="right"></span>
										<span class="righttext">'. $TopUploadMember['Total_Upload'] .'</span>
									</dd>';
							}	
							$content .='
								</dl>';
						}		
	$content .= '								
								<div class="clear"></div>
							</div>
							<span class="botslice"><span></span></span>
						</div>
					</div>
					<div id="stats_right">
						<h3 class="catbg"><span class="left"></span>
							<img src="'. $settings['images_url'] .'/stats_views.gif" class="icon" alt="" /> '. $txt['down_top_sections'] .'
						</h3>											
						<div class="windowbg2">
							<span class="topslice"><span></span></span>
							<div class="content">';
						if (!empty($context['view']))
						{
							$content .='
								<dl class="stats">';							
							foreach ($context['dowsect'] as $dowsect)
							{
								$content .='
									<dt>
										<a href="'. $scripturl .'?action=downloads;sa=search;type='. $dowsect['id'] .'">'. $dowsect['title'] .'</a>
									</dt>
									<dd class="statsbar">';
									
									if (!empty($dowsect['total_files']))
									{
										$content .='
										<span class="left"></span>
										<div style="width: '. $dowsect['total_percent'] .'px;" class="stats_bar"></div>
										<span class="right"></span>';
									}	
									
								$content .='										
										<span class="righttext">'. $dowsect['total_files'] .'</span>
									</dd>';
							}		
							$content .='
								</dl>';							
						}		
	$content .= '											
								<div class="clear"></div>
							</div>
						<span class="botslice"><span></span></span>
						</div>
					</div>
				</div>
				<br class="clear" />				
				<div class="flow_hidden">
					<h3 class="catbg"><span class="left"></span>
						<img src="'. $settings['images_url'] .'/stats_views.gif" class="icon" alt="" /> '. $txt['down_module_more_popular_title'] .'
					</h3>						
					<div class="windowbg">
						<span class="topslice"><span></span></span>
						<div class="content">';
					if(!empty($context['view-file-rows-popular']))
					{
						$content .='
							<dl class="stats">';													
						foreach($context['file-popular'] as $file)
						{
							$content .='									
								<dt>
									<a href="'. $scripturl .'?action=downloads;sa=view;download='. $file['id_files-popular'] .'">'. $file['title-popular'] .'</a>
								</dt>
								<dd class="statsbar">';									
								if (!empty($file['total_downloads-popular']))
								{
									$content .='
									<span class="left"></span>
									<div style="width: '. $file['percent_downloads-popular'] .'px;" class="stats_bar"></div>
									<span class="right"></span>';
								}	
								
							$content .='										
									<span class="righttext">'. $file['total_downloads-popular'] .'</span>
								</dd>';

						}	
						$content .='
							</dl>';													
					}	
$content .= '								
							<div class="clear"></div>
						</div>
						<span class="botslice"><span></span></span>
					</div>
				</div>				
				<br class="clear" />				
				<div class="flow_hidden">
					<h3 class="catbg"><span class="left"></span>
						<img src="'. $settings['images_url'] .'/stats_views.gif" class="icon" alt="" /> '. $txt['down_module_last_files_title'] .'
					</h3>						
					<div class="windowbg">
						<span class="topslice"><span></span></span>
						<div class="content">';
					if(!empty($context['view-file-rows-last']))
					{
						foreach($context['file-last'] as $file)
						{
							$content .='									
								<a href="'. $scripturl .'?action=downloads;sa=view;download='. $file['id_files-last'] .'">'. $file['title-last'] .'</a>
								<br/>';
						}	
					}	
$content .= '								
							<div class="clear"></div>
						</div>
						<span class="botslice"><span></span></span>
					</div>
				</div>				
			</div>
		</td>
	</tr>
</table>';		
				
	//The Download Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'up-download', $txt['down_module_title'] . ' - ' . $txt['down_module_title2']);
	
}

//Show the Ultimate Portal - Module Download - Profile
function template_down_profile()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">
	<tr>
		<td  align="left" width="100%">
			<table  style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td  align="left" width="90%">
						<strong>'. $context['down-linktree'] .'</strong>
					</td>
				</tr>
			</table>
		</td>
	</tr>';

	$content .= '
	<tr>
		<td align="left" width="100%">
			<table  cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td align="left" width="100%">					
						<h3 class="DownloadH3">'. $txt['down_profile_title'] . $context['membername'] .'</h3><br/>
						<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] .'<br/>
						<strong>'. $txt['down_profile_total_files'] .'</strong>'. $context['total_files'] .'
						<dl id="DownBrowsing">';	
				if(!empty($context['view_profile']))
				{	
					foreach($context['down_profile'] as $down_profile)
					{
							$content .= '
							<dt>
								<a href="'. $scripturl .'?action=downloads;sa=view;download='. $down_profile['id_files'] .'">'. $down_profile['title'] .'</a>
							</dt>
							<dd>
								<p>'. $down_profile['small_description'] .'</p>
								<ul>
									<li>'. $txt['down_date_created'] .': '. $down_profile['date_created'] .'</li>
									<li>'. $txt['down_date_updated'] .': '. $down_profile['date_updated'] .'</li>
									<li>'. $txt['down_total_downloads'] .': '. $down_profile['total_downloads'] .'</li>
								</ul>
							</dd>';							
					}
				}				
	$content .= '		
						</dl>
					</td>
				</tr>		
				<tr>
					<td align="left" width="100%">					
						<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] .'<br/>
					</td>
				</tr>					
			</table>	
		</td>
	</tr>
</table>';		
				
	//The Download Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'up-download', $txt['down_module_title'] . ' - ' . $txt['down_module_title2']);
	
}

?>