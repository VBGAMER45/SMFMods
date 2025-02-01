<?php
/*
EzPortal
Version 3.1
by:vbgamer45
http://www.ezportal.com
Copyright 2010-2021 http://www.samsonsoftware.com
*/
function template_ezportal_addpage()
{
	global $context, $txt, $scripturl;
	
	echo '
	<form method="post" name="frmaddpage" id="frmaddpage" action="', $scripturl, '?action=ezportal;sa=addpage2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td width="50%" colspan="2"  align="center" class="catbg">
	    <b>', $txt['ezp_addpage'], '</b></td>
	  </tr>
	  <tr>
	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_page_title'],'</b>&nbsp;</td>
	    <td width="80%" class="windowbg2"><input type="text" name="pagetitle" size="100" /></td>
	  </tr>
	  <tr>
	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_page_content'],'</b>&nbsp;</td>
	    <td width="80%" class="windowbg2">
	    <textarea name="pagecontent" id="myTextEditor" rows="15" cols="55"></textarea>
	    </td>
	  </tr> 
	  
	<tr class="windowbg2"><td> </td><td>
			  <a href="#" id="switchtinymce" onclick="switchTinyMCE(this.id, \'myTextEditor\'); return false;">' . $txt['ezp_hide_tinymce'] . '</a>
			<script language="javascript">

function toggleEditor(id) {
	if (!tinyMCE.get(id))
		tinyMCE.execCommand(\'mceAddControl\', false, id);
	else
		tinyMCE.execCommand(\'mceRemoveControl\', false, id);
}

function switchTinyMCE(link_id, textarea_id) {
  toggleEditor(textarea_id);
  var link = document.getElementById(link_id);
  link.innerHTML = (link.innerHTML == \'' . $txt['ezp_hide_tinymce'] . '\') ? \'' . $txt['ezp_show_tinymce'] . '\' : \'' . $txt['ezp_hide_tinymce'] . '\';
  link.title = link.innerHTML;
}

tinyMCE.execCommand(\'mceAddControl\', false, \'myTextEditor\');

			</script>
			</td>
			</tr>
	  <tr>
	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_metatags'],'</b>&nbsp;</td>
	    <td width="80%" class="windowbg2"><textarea name="metatags" rows="5" cols="55"></textarea><br />
	    ', $txt['ezp_txt_metatags2'],'
	    </td>
	  </tr> 
	  
	  <tr>
	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_permissions'],'</b>&nbsp;</td>
	    <td width="80%" class="windowbg2">
	    <input type="checkbox" name="groups[-1]" value="-1" />',$txt['membergroups_guests'],'<br />
	    <input type="checkbox" name="groups[0]" value="0" />', $txt['membergroups_members'],'<br />';
	
		foreach ($context['groups'] as $group)
				echo '<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" />', $group['groupName'], '<br />';
							
	echo '<input type="checkbox" id="checkAllGroups" onclick="invertAll(this, this.form, \'groups\');" class="check" />', 
$txt['ezp_txt_checkall'],'
		        
	    </td>
	  </tr> 
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	     <input type="hidden" name="sc" value="', $context['session_id'], '" />	
	    <input type="submit" name="addpage" value="',$txt['ezp_addpage'],'" /></td>
	  </tr> 
	  </table>
  	</form>
  ';
  
}

function template_ezportal_editpage()
{
	global $context, $txt, $scripturl;
	
	echo '
	<form method="post" name="frmeditpage" id="frmeditpage" action="', $scripturl, '?action=ezportal;sa=editpage2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td width="50%" colspan="2" align="center" class="catbg">
	    <b>', $txt['ezp_editpage'], '</b></td>
	  </tr>
	  <tr>
	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_page_title'],'</b>&nbsp;</td>
	    <td width="80%" class="windowbg2"><input type="text" name="pagetitle" size="100" value="',$context['ezp_editpage_data']['title'], '" /></td>
	  </tr>
	  <tr>
	    <td width="20%"  class="windowbg2" align="right"><b>', $txt['ezp_txt_page_content'],'</b>&nbsp;</td>
	    <td width="80%"  class="windowbg2">
	    <textarea name="pagecontent" id="myTextEditor" rows="15" cols="55">', $context['ezp_editpage_data']['content'],'</textarea>
	  
	    </td>
	  </tr> 
	  
	<tr class="windowbg2"><td> </td><td>
			  <a href="#" id="switchtinymce" onclick="switchTinyMCE(this.id, \'myTextEditor\'); return false;">' . $txt['ezp_hide_tinymce'] . '</a>
			<script language="javascript">

function toggleEditor(id) {
	if (!tinyMCE.get(id))
		tinyMCE.execCommand(\'mceAddControl\', false, id);
	else
		tinyMCE.execCommand(\'mceRemoveControl\', false, id);
}

function switchTinyMCE(link_id, textarea_id) {
  toggleEditor(textarea_id);
  var link = document.getElementById(link_id);
  link.innerHTML = (link.innerHTML == \'' . $txt['ezp_hide_tinymce'] . '\') ? \'' . $txt['ezp_show_tinymce'] . '\' : \'' . $txt['ezp_hide_tinymce'] . '\';
  link.title = link.innerHTML;
}

tinyMCE.execCommand(\'mceAddControl\', false, \'myTextEditor\');

			</script>
			</td>
			</tr>
	  <tr>
	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_metatags'],'</b>&nbsp;</td>
	    <td width="80%" class="windowbg2"><textarea name="metatags" rows="5" cols="55">', $context['ezp_editpage_data']['metatags'],'</textarea><br />
	    ', $txt['ezp_txt_metatags2'],'
	    </td>
	  </tr> 
	  <tr>
	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_permissions'],'</b>&nbsp;</td>
	    <td width="80%" class="windowbg2">';
	
		$permissionsGroups = explode(',',$context['ezp_editpage_data']['permissions']);
	
		echo '
	    <input type="checkbox" name="groups[-1]" value="-1" ', ((in_array(-1,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_guests'],'<br />
	    <input type="checkbox" name="groups[0]" value="0" ', ((in_array(0,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_members'],'<br />';
	
		foreach ($context['groups'] as $group)
				echo '<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />', $group['groupName'], '<br />';
							
	echo '<input type="checkbox" id="checkAllGroups"  onclick="invertAll(this, this.form, \'groups\');" class="check" />', 
$txt['ezp_txt_checkall'],'
		        
	    </td>
	  </tr> 
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	     <input type="hidden" name="sc" value="', $context['session_id'], '" />	
	    <input type="hidden" name="p" value="', $context['ezp_editpage_data']['id_page'],'" />
	    <input type="submit" name="editpage" value="', $txt['ezp_editpage'], '" />
	    </td>
	  </tr> 
	  </table>
  	</form>
  ';
	
}

function template_ezportal_deletepage()
{
	global $context, $txt, $scripturl;
	
	echo '
	<form method="post" name="frmdelpage" id="frmdelpage" action="', $scripturl, '?action=ezportal;sa=deletepage2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="50%" align="center">
	  <tr>
	    <td align="center" class="catbg">
	    <b>', $txt['ezp_deletepage'], '</b></td>
	  </tr>
	  <tr>
	    <td class="windowbg2" align="center">
	    ',$txt['ezp_deletepage_confirm'], '<br />
	    <b>',$context['ezp_deletepage_data']['title'],'</b>
		</td>
	  </tr>
	  <tr>
	    <td class="windowbg2" align="center">
	    <input type="hidden" name="sc" value="', $context['session_id'], '" />	
	    <input type="hidden" name="p" value="', $context['ezp_deletepage_data']['id_page'],'" />
	    <input type="submit" name="delpage" value="', $txt['ezp_deletepage'], '" /></td>
	  </tr> 
	  </table>
  	</form>
  ';
  
}

function template_ezportal_settings()
{
	global $context, $txt, $ezPortalVersion, $scripturl, $ezpSettings;
	
echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['ezp_settings'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
			',$txt['ezp_txt_yourversion'] , $ezPortalVersion, '&nbsp;',$txt['ezp_txt_latestversion'],'<span id="lastezportal"></span>
			<br />
			<form method="post" action="', $scripturl, '?action=ezportal;sa=settings2" accept-charset="', $context['character_set'], '">
				<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4">
				<tr><td width="30%">',$txt['ezp_url'], '</td><td><input type="text" name="ezp_url" value="',  $ezpSettings['ezp_url'], '" size="50" /></td></tr>
				<tr><td width="30%">', $txt['ezp_path'], '</td><td><input type="text" name="ezp_path" value="',  $ezpSettings['ezp_path'], '" size="50" /></td></tr>
				<tr><td colspan="2"><input type="checkbox" name="ezp_portal_enable" ',($ezpSettings['ezp_portal_enable'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_portal_enable'] . '</td></tr>
				<tr><td width="30%">', $txt['ezp_portal_homepage_title'], '</td><td><input type="text" name="ezp_portal_homepage_title" value="',  $ezpSettings['ezp_portal_homepage_title'], '" size="50" /></td></tr>
				
				<tr><td colspan="2"><input type="checkbox" name="ezp_hide_edit_delete" ',($ezpSettings['ezp_hide_edit_delete'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_hide_edit_delete'] . '</td></tr>
				<tr><td colspan="2"><input type="checkbox" name="ezp_disable_tinymce_html" ',($ezpSettings['ezp_disable_tinymce_html'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_disable_tinymce_html'] . '</td></tr>
				<tr><td colspan="2"><input type="checkbox" name="ezp_disablemobiledevices" ',($ezpSettings['ezp_disablemobiledevices'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_disablemobiledevices'] . '</td></tr>
				
			
				<tr><td colspan="2"><input type="checkbox" name="ezp_pages_seourls" ',($ezpSettings['ezp_pages_seourls'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_pages_seourls'] . '</td></tr>
				
			
				<tr><td colspan="2">
				<hr />
				<br /><b>',$txt['ezp_shoutbox_settings'],'</b>
				</td>
				</tr>
				
				<tr><td colspan="2"><input type="checkbox" name="ezp_shoutbox_enable" ',($ezpSettings['ezp_shoutbox_enable'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_shoutbox_enable'] . '</td></tr>
				<tr><td colspan="2"><input type="checkbox" name="ezp_shoutbox_showdate" ',($ezpSettings['ezp_shoutbox_showdate'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_shoutbox_showdate'] . '</td></tr>
				<tr><td colspan="2"><input type="checkbox" name="ezp_shoutbox_archivehistory" ',($ezpSettings['ezp_shoutbox_archivehistory'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_shoutbox_archivehistory'] . '</td></tr>
				<tr><td colspan="2"><input type="checkbox" name="ezp_shoutbox_hidesays" ',($ezpSettings['ezp_shoutbox_hidesays'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_shoutbox_hidesays'] . '</td></tr>
				<tr><td colspan="2"><input type="checkbox" name="ezp_shoutbox_hidedelete" ',($ezpSettings['ezp_shoutbox_hidedelete'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_shoutbox_hidedelete'] . '</td></tr>
				
				<tr><td width="30%">', $txt['ezp_shoutbox_history_number'], '</td><td><input type="text" name="ezp_shoutbox_history_number" value="',  $ezpSettings['ezp_shoutbox_history_number'], '" size="50" /></td></tr>
				
				<tr><td colspan="2"><input type="checkbox" name="ezp_shoutbox_showbbc" ',($ezpSettings['ezp_shoutbox_showbbc'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_shoutbox_showbbc'] . '</td></tr>
				<tr><td colspan="2"><input type="checkbox" name="ezp_shoutbox_showsmilies" ',($ezpSettings['ezp_shoutbox_showsmilies'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_shoutbox_showsmilies'] . '</td></tr>
				<tr><td width="30%">', $txt['ezp_shoutbox_refreshseconds'], '</td><td><input type="text" name="ezp_shoutbox_refreshseconds" value="',  $ezpSettings['ezp_shoutbox_refreshseconds'], '" size="50" /></td></tr>
				
				
				<tr><td colspan="2"><hr /></td></tr>
				<tr><td colspan="2"><input type="checkbox" name="ezp_allowstats" ',($ezpSettings['ezp_allowstats'] ? ' checked="checked" ' : ''), ' />', $txt['ezp_allowstats'] . '</td></tr>
				<tr><td colspan="2">' . $txt['ezp_forumgetlisted'] . '</td></tr>
				</table>
				<br />';

	
				echo '
				<input type="hidden" name="sc" value="', $context['session_id'], '" />			
				<input type="submit" name="savesettings" value="', $txt['ezp_savesettings'], '" />
			</form>
					<script language="JavaScript" type="text/javascript" src="https://www.ezportal.com/versions/ezportal_smf_version.js?t=' . time() . '"></script>
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
			
			function EzPortalCurrentVersion()
			{
				if (!window.ezportalVersion)
					return;
				
				ezportalspan = document.getElementById("lastezportal");
					
				if (window.ezportalVersion != "' . $ezPortalVersion . '")
				{
					setInnerHTML(ezportalspan, "<span style=\"font-weight: bold;\"><span style=\"color: red;\">" + window.ezportalVersion + "</span>&nbsp;' . $txt['ezp_txt_version_outofdate'] . '</span>");
				}
				else
				{
					setInnerHTML(ezportalspan, "' . $ezPortalVersion . '")
				}
			}
			
			// Override on the onload function
			window.onload = function ()
			{
				EzPortalCurrentVersion();
			}
			 // ]]></script>
			</td>
		</tr>
		<tr class="windowbg">
			<td>',$txt['ezp_donate'],'<br />
			    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	            <input type="hidden" name="cmd" value="_xclick" />
	            <input type="hidden" name="business" value="sales@visualbasiczone.com" />
	            <input type="hidden" name="item_name" value="EzPortal" />
	            <input type="hidden" name="no_shipping" value="1" />
	            <input type="hidden" name="no_note" value="1" />
	            <input type="hidden" name="currency_code" value="USD" />
	            <input type="hidden" name="tax" value="0" />
	            <input type="hidden" name="bn" value="PP-DonationsBF" />
	            <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
	            <img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
				</form>
				
			

			</td>
		
		</tr>
</table>';

						
}

function template_ezportal_messageform()
{
	global $context;
	
	echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
	    <td align="center">
	    <b>', $context['ezportal_message_title'], '</b></td>
	  </tr>
	  <tr>
	    <td class="windowbg2" align="center">
	    ',$context['ezportal_message_description'],'
	  	</td>
	  </tr> 
	  </table>';
}

function template_ezportal_modules()
{
	global $context, $txt, $scripturl;
	
echo '
	<table border="0" width="90%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['ezp_modules'], '</td>
		</tr>
		<tr>
			<td class="windowbg">
				';
				
				echo '<table  width="90%" cellspacing="0" align="center" cellpadding="4" class="tborder">';
				$styleclass = 'windowbg';
				
				foreach($context['module-list'] as $module)
				{
					echo '
					<tr class="',$styleclass,'">
						<td>',$module['title'],'<br />
						',$module['text'],'<br />
						<a href="', $scripturl, '?action=pgdownload;auto;package=',$module['filename1'],';sesc=', $context['session_id'], '">',$txt['ezp_module_download'] ,'</a>
					
						</td>
					</tr>';
					
					if ($styleclass == 'windowbg')
						$styleclass = 'windowbg2';
					else 
						$styleclass = 'windowbg';
					
				}
				echo '</table>';

	echo '</td>
	</tr>
	</table>';
}

function template_ezportal_blocks()
{
	global $context, $txt, $scripturl, $ezpSettings;
	
echo '
	<table border="0" width="90%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['ezp_blocks'], '</td>
		</tr>
		<tr>
			<td class="windowbg">
				';
				
				$styleclass = "windowbg";
				foreach($context['ezPortalAdminColumns']  as $row)
				{
					echo '
					<form method="post" action="', $scripturl, '?action=ezportal;sa=blocks2" accept-charset="', $context['character_set'], '">
					<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
					<tr class="catbg">
						<td colspan="4">',$row['column_title'],' - <a href="', $scripturl, '?action=ezportal;sa=editcolumn;column=',$row['id_column'],'"><img src="',$ezpSettings['ezp_url'],'icons/page_white_edit.png" alt="',$txt['ezp_edit2'], '" /> ',$txt['ezp_editcolumn'],'</a> 
						'
						, ' - ', ($row['active'] ? '<font color="#00FF00">' . $txt['ezp_txt_active'] . '</font>' :  '<font color="#FF0000">' . $txt['ezp_txt_disabled'] . '</font>'),
						'
						</td>
					</tr>
					<tr class="titlebg">
						<td>',$txt['ezp_txt_order'] ,'</td>
						<td>',$txt['ezp_txt_title'] ,'</td>
						<td>',$txt['ezp_txt_active'],'</td>
						<td>',$txt['ezp_txt_options'],'</td>
					</tr>';
					
					// Now show all the ezBlocks under the column
					foreach($row['blocks'] as $blockRow)
					{
						echo '
						<tr class="',$styleclass,'">
							<td><input type="text" name="order[',$blockRow['id_layout'],']" value="',($blockRow['id_order']*10),'" size="4" /></td>
							<td><input type="text" name="title[',$blockRow['id_layout'],']" value="',$blockRow['customtitle'],'" size="50" /></td>
							<td>
								<select name="active[',$blockRow['id_layout'],']">
								<option value="1" ',($blockRow['active'] == 1 ? ' selected="selected" ' : ''),'>',$txt['ezp_yes'],'</option>
								<option value="0" ',($blockRow['active'] == 0 ? ' selected="selected" ' : ''),'>',$txt['ezp_no'],'</option>
								</select>
							</td>
						
							<td>
							<a href="',$scripturl,'?action=ezportal;sa=editblock;block=',$blockRow['id_layout'],'">',$txt['ezp_edit'], '</a>
							&nbsp;
							<a href="',$scripturl,'?action=ezportal;sa=deleteblock;block=',$blockRow['id_layout'],'">',$txt['ezp_delete'], '</a>
							</td>
						</tr>';
						
						// Alternate the style class
						if ($styleclass == "windowbg")
							$styleclass = "windowbg2";
						else 
							$styleclass = "windowbg";
					}
					
					echo '
					<tr class="',$styleclass,'">
						<td colspan="4" align="center">
						<a href="', $scripturl, '?action=ezportal;sa=addblock;column=',$row['id_column'],'">',$txt['ezp_addblock'],'</a>
						</td>
					</tr>
					<tr class="',$styleclass,'">
						<td colspan="4" align="center">
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="hidden" name="column" value="',$row['id_column'],'" />
						<input type="submit" value="',$txt['ezp_saveblocks'],'" />
						</td>
					</tr>
					</table>
					</form>
					<br />';
		
					
				}

				
			echo '
			</td>
		</tr>
	</table>';
				
				
}

function template_ezportal_import()
{
	global $context, $txt, $scripturl;
	
echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['ezp_import'], '</td>
		</tr>
		<tr>
			<td class="windowbg">
			<span class="smalltext">',$txt['ezp_import_information'],'</span><br />';
				
				// Setup the import buttons
				if ($context['portals']['MX'] == true)
					echo '<form method="post" action="', $scripturl,'?action=ezportal;sa=import2;type=mx">
							<input type="submit" value="',$txt['ezp_import_mx'],'" />
						</form>';
					
				if ($context['portals']['SP'] == true)
					echo '<form method="post" action="', $scripturl,'?action=ezportal;sa=import2;type=sp">
							<input type="submit" value="',$txt['ezp_import_sp'],'" />
						</form>';

				if ($context['portals']['TP'] == true)
					echo '<form method="post" action="', $scripturl,'?action=ezportal;sa=import2;type=tp">
							<input type="submit" value="',$txt['ezp_import_tp'],'" />
						</form>';
					
				
				echo '
			</td>
		</tr>
	</table>';
				
				
}

function template_ezportal_pagemanager()
{
	global $context, $txt, $scripturl, $ezpSettings, $boardurl;
	
echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['ezp_pagemanager'], '</td>
		</tr>
		<tr>
			<td class="windowbg">
				<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
				<tr class="titlebg">
					<td>',$txt['ezp_txt_title'] ,'</td>
					<td>',$txt['ezp_txt_views'],'</td>
					<td>',$txt['ezp_txt_date'],'</td>
					<td>',$txt['ezp_txt_options'],'</td>
				</tr>';
				
				$styleclass = "windowbg";
				foreach($context['ezp_pages']  as $row)
				{
					echo '
					<tr class="',$styleclass,'">
						<td><a href="',$scripturl,'?action=ezportal;sa=page;p=',$row['id_page'],'">',$row['title'], '</a><br />';
						
						
						$pageurl = $scripturl . '?action=ezportal;sa=page;p='  . $row['id_page'];
			
						if (!empty($ezpSettings['ezp_pages_seourls']))
								$pageurl = $boardurl . '/pages/' . MakeSEOUrl($row['title']) . '-' . $row['id_page'];	
		
						
						
						echo '
						<a href="',$pageurl,'">',$pageurl,'</a>
						</td>
						<td>',$row['views'], '</td>
						<td>',timeformat($row['date']), '</td>
						<td>
						<a href="',$scripturl,'?action=ezportal;sa=editpage;p=',$row['id_page'],'">',$txt['ezp_edit'], '</a>
						<br />
						<a href="',$scripturl,'?action=ezportal;sa=deletepage;p=',$row['id_page'],'">',$txt['ezp_delete'], '</a>
						</td>
					</tr>';
					
					// Alternate the style class
					if ($styleclass == "windowbg")
						$styleclass = "windowbg2";
					else 
						$styleclass = "windowbg";
					
				}

				echo '
				</table><br />
				',$txt['ezp_txt_pages'],' ',$context['page_index'],'
			</td>
		</tr>
		<tr>
			<td class="windowbg" align="left">
				<form method="post" action="', $scripturl,'?action=ezportal;sa=addpage">
					<input type="submit" value="',$txt['ezp_addpage'],'" />
				</form>
			</td>
		</tr>
	</table>';
	
}

function template_ezportal_credits()
{
	global $context, $txt;
	
	echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['ezp_credits'], '</td>
		</tr>
		<tr class="windowbg">
			<td>', $txt['ezp_developedby'], '<br />
			<hr /><br />
			',$txt['ezp_helpout'],'
			</td>
		</tr>
		<tr class="windowbg">
			<td>',$txt['ezp_donate'],'<br />
		    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="sales@visualbasiczone.com">
			<input type="hidden" name="item_name" value="EzPortal">
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

function template_ezportal_above()
{
	// Global Header for EzPortal not used. Only need for template layer
}

function template_ezportal_below()
{
	global $context;
	
	// Show EzPortal Copyright
	
	// DO NOT MODIFY OR REMOVE THIS COPYRIGHT UNLESS THE BRANDING FREE OPTION HAS BEEN PURCHASED
	// http://www.ezportal.com/copyright_removal.php
    $showInfo = EzPortalCheckInfo();
    
	if ($context['ezp_loaded'] != true && $showInfo == true)
		echo '<div align="center"><span class="smalltext">Powered by <a href="https://www.ezportal.com" target="blank">EzPortal</a></span></div>';
}

function template_ezportal_viewpage()
{
	global $context;
	
	// Show the html content
	echo $context['ezp_pagecontent'];
	
}

function template_ezportal_edit_column()
{
	global $context, $txt, $scripturl;
	
echo '
	<form method="post" name="frmeditcolumn" id="frmeditcolumn" action="', $scripturl, '?action=ezportal;sa=editcolumn2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td colspan="2"  align="center" class="catbg">
	    <b>', $txt['ezp_editcolumn'], '</b></td>
	  </tr>
	  <tr>
	    <td width="30%"  class="windowbg2" align="right"><b>', $txt['ezp_txt_columntitle'],'</b>&nbsp;</td>
	    <td width="70%"  class="windowbg2">',$context['ezp_column_data']['column_title'],'</td>
	  </tr>
	  <tr>
	    <td width="30%"  class="windowbg2" align="right"><b>', $txt['ezp_txt_columnwidth'],'</b>&nbsp;</td>
	    <td width="70%"  class="windowbg2"><input type="text" name="columnwidth" size="10" value="',$context['ezp_column_data']['column_width'],'" /></td>
	  </tr>
	  <tr>
	    <td width="30%"  class="windowbg2" align="right" valign="top"><b>', $txt['ezp_txt_columnwidth_percent'],'</b>&nbsp;</td>
	    <td width="70%"  class="windowbg2"><input type="text" name="columnpercent" size="10" value="',$context['ezp_column_data']['column_percent'],'" />
	    <br />
	    <span class="smalltext">',$txt['ezp_txt_columnwidth_percent_note'],'</span>
	    </td>
	  </tr>
	  <tr>
	    <td width="30%"  class="windowbg2" align="right"><b>', $txt['ezp_txt_active'],'</b>&nbsp;</td>
	    <td width="70%"  class="windowbg2">
	   		<select name="active">
				<option value="1" ',($context['ezp_column_data']['active'] == 1 ? ' selected="selected" ' : ''),'>',$txt['ezp_yes'],'</option>
				<option value="0" ',($context['ezp_column_data']['active'] == 0 ? ' selected="selected" ' : ''),'>',$txt['ezp_no'],'</option>
				</select>
	    </td>
	  </tr>
';

	if ($context['ezp_column_data']['column_title'] == 'Left' || $context['ezp_column_data']['column_title'] == 'Right')
	{
		echo '
	  <tr>
	    <td width="20%"  class="windowbg2" align="right"><b>', $txt['ezp_txt_can_collapse_column'],'</b>&nbsp;</td>
	    <td width="80%"  class="windowbg2"><input type="checkbox" name="can_collapse" ' . ($context['ezp_column_data']['can_collapse'] == 1  ? ' checked="checked" ' : '') . ' /></td>
	  </tr>';
	}

echo '
	  
	  <tr>
	    <td width="20%"  class="windowbg2" align="right"><b>', $txt['ezp_txt_visible_areas'],'</b>&nbsp;</td>
	    <td width="80%"  class="windowbg2"><a href="', $scripturl, '?action=ezportal;sa=visiblesettings;column=',$context['ezp_column_data']['id_column'],'">',$txt['ezp_txt_update_visible_options'],'</a></td>
	  </tr>
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    <input type="hidden" name="sc" value="', $context['session_id'], '" />	
	    <input type="hidden" name="column" value="',$context['ezp_column_data']['id_column'],'" />
	    <input type="submit" name="editcolumn" value="',$txt['ezp_editcolumn'],'" /></td>
	  </tr> 
	  </table>
  	</form>
  ';
	
	
}


function template_ezportal_add_block()
{
	global $context, $txt, $scripturl;
	
echo '
	<form method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=addblock2" accept-charset="', $context['character_set'], '">
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
	    <td colspan="2"  align="center">
	    <b>', $txt['ezp_addblock'], '</b></td>
	  </tr>
	  <tr>
	    <td width="50%" class="windowbg2" align="right"><b>', $txt['ezp_txt_block_type'],'</b>&nbsp;</td>
	    <td width="50%" class="windowbg2"><select name="blocktype">';

		foreach ($context['ezp_blocks'] as $row)
				echo '<option value="', $row['id_block'], '">', $row['blocktitle'], '</option>';
							
	echo '</select>
	    </td>
	  </tr> 
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    <input type="hidden" name="column" value="',$context['ezportal_column'],'" />
	    <input type="submit" name="addblock" value="',$txt['ezp_addblock'],'" /></td>
	  </tr> 
	  </table>
  	</form>
  ';
	
}

function template_ezportal_add_block2()
{
	global $context, $txt, $scripturl, $ezpSettings;
	
echo '
	<form method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=addblock3" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td colspan="2" align="center" class="catbg">
	    <b>', $txt['ezp_addblock'], '</b></td>
	  </tr>
	  <tr>
	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_block_title'],'</b>&nbsp;</td>
	    <td width="80%" class="windowbg2"><input type="text" name="blocktitle" size="60" value="',$context['ezp_block_data']['blocktitle'],'" /></td>
	  </tr>
	  <tr class="windowbg2">
	    <td width="20%" align="right"><b>', $txt['ezp_txt_column'],'</b>&nbsp;</td>
	    <td width="80%">
	    <select name="column">
	    ';
	  	
		foreach ($context['ezp_columns'] as $column)
				echo '<option value="', $column['id_column'], '" ',($column['id_column'] == $context['ezportal_column'] ? ' selected="selected" ' : ''), '>', $column['column_title'], '</option>';
							
	
	echo '</select>
	    </td>
	  </tr>
	    <tr class="windowbg2">
	    <td width="20%" align="right"><b>', $txt['ezp_txt_icon'],'</b>&nbsp;</td>
	 <td width="80%">
	    <select id="iconchoice" name="icon" onchange="ChangeIconPic(this.value)">
	    <option value="0">',$txt['ezp_txt_noicon'],'</option>
	    ';
	  	
		foreach ($context['ezp_icons'] as $icon)
				echo '<option value="', $icon['id_icon'], '">', $icon['icon'], '</option>';
							
	
	echo '</select>
	
	<img id="iconPick" src="" alter="" />
	
	
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	// iconSelect
	var selectIcons = new Array();
	selectIcons[0] = "";
	';
	
	foreach($context['ezp_icons'] as $c => $icon)
		echo 'selectIcons[',$icon['id_icon'],'] = "' . $ezpSettings['ezp_url'] . 'icons/' . $icon['icon'] .  '";' . "\n";

	echo  '
	function ChangeIconPic(iconIndex)
	{
		document.frmaddblock.iconPick.src = selectIcons[iconIndex];
	}
	
	ChangeIconPic(document.frmaddblock.iconchoice.selectedIndex);
	
	// ]]></script>
	    </td>
	  </tr>
	  
	  ';

	
	// Check if we need to setup an editable box for this ezBlock
	if ($context['ezp_block_data']['data_editable'] == 1)
	{
		echo '<tr class="windowbg2">
				 <td width="20%" align="right"><b>', $txt['ezp_txt_block_data'],'</b>&nbsp;</td>
				 <td width="80%">
				 <textarea name="blockdata" id="myTextEditor" rows="10" cols="60">',$context['ezp_block_data']['blockdata'],'</textarea>
				 </td>	 
			</tr>
		';
		
		if ($context['ezp_showtinymcetoggle'] == true)
		{
			echo '<tr class="windowbg2"><td colspan="2">
			  <a href="#" id="switchtinymce" onclick="switchTinyMCE(this.id, \'myTextEditor\'); return false;">' . $txt['ezp_hide_tinymce'] . '</a>
			<script language="javascript">

function toggleEditor(id) {
	if (!tinyMCE.get(id))
		tinyMCE.execCommand(\'mceAddControl\', false, id);
	else
		tinyMCE.execCommand(\'mceRemoveControl\', false, id);
}

function switchTinyMCE(link_id, textarea_id) {
  toggleEditor(textarea_id);
  var link = document.getElementById(link_id);
  link.innerHTML = (link.innerHTML == \'' . $txt['ezp_hide_tinymce'] . '\') ? \'' . $txt['ezp_show_tinymce'] . '\' : \'' . $txt['ezp_hide_tinymce'] . '\';
  link.title = link.innerHTML;
}

tinyMCE.execCommand(\'mceAddControl\', false, \'myTextEditor\');

			</script>
			</td>
			</tr>';
		}
	}
	
	if ($context['ezp_block_data']['blocktitle'] == 'Menu ezBlock')
	{
		echo '<tr class="windowbg2">
		<td colspan="2"><b>' . $txt['ezp_txt_menu_add_block_note'] . '</b></td>
		</tr>
		';
	}
	
	
	// Now show all parameters for this ezBlock
	foreach ($context['ezp_block_parameters'] as $parameter)
	{
		if ($parameter['parameter_type'] == 'hidden')
			continue;
		
		echo '<tr class="windowbg2">
				 <td width="50%" align="right"><b>', $parameter['title'],($parameter['required'] == 1 ? '<span style="color: red;">*</span>' : ''),'</b>&nbsp;</td>
				 <td width="50%">';
					
				if ($parameter['parameter_type'] == 'select')
				{
						echo '<select name="parameter[', $parameter['id_parameter'],']">';
						foreach ($context['ezp_select_' . $parameter['id_parameter']] as $key => $option)
							 echo '<option value="' . $key . '">' . $option . '</option>';
						echo '</select>';
				}
				else if  ($parameter['parameter_type'] == 'checkbox')
				{
					echo '<input type="checkbox" name="parameter[', $parameter['id_parameter'],']" ', ($parameter['defaultvalue'] == 1 ? 'checked="checked" ' : ''),' />';
				}
				else if ($parameter['parameter_type'] == 'boardselect')
				  {
				  	echo '<select name="parameter[', $parameter['id_parameter'],']">';

						foreach ($context['ezportal_boards'] as $key => $option)
							 echo '<option value="' . $key . '">' . $option . '</option>';
					
					echo '</select>';
				  }
				  else if ($parameter['parameter_type'] == 'multiboardselect')
				  {
				  	echo '<select name="parameter[', $parameter['id_parameter'],'][]" size="5" multiple="multiple">';

						foreach ($context['ezportal_boards'] as $key => $option)
						{
							if (empty($option ))
								continue;
								
							 echo '<option value="' . $key . '">' . $option . '</option>';
						}
					echo '</select>';
				  }
				  else if ($parameter['parameter_type'] == 'bbc')
				   {
				   	
				   	echo '<table>';
  						 theme_postbox('');
   					echo '</table>';
				   	 						
				   }
				  else
				  {
						echo '<input type="text" name="parameter[', $parameter['id_parameter'],']" size="60" value="', $parameter['defaultvalue'],'" />';
				  }
						
				echo '</td>	 
			</tr>
		';
		
		
	}
	
	echo '
	  <tr>
	    <td colspan="2" class="windowbg2"  valign="top">
	    <table width="50%" align="center">
	    <tr>
		    <td>
		    <b>', $txt['ezp_txt_permissions'],'</b><br />
		    <input type="checkbox" name="groups[-1]" value="-1" checked="checked" />',$txt['membergroups_guests'],'<br />
		    <input type="checkbox" name="groups[0]" value="0" checked="checked" />',$txt['membergroups_members'],'<br />';
		
			foreach ($context['groups'] as $group)
					echo '<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" checked="checked" />', $group['groupName'], '<br />';
								
		echo '<input type="checkbox" id="checkAllGroups"  onclick="invertAll(this, this.form, \'groups\');" class="check" />', 
$txt['ezp_txt_checkall'],'
		    
		    </td>
		    <td class="windowbg2" valign="top"><b>', $txt['ezp_txt_block_managers'],'</b><br />
		    ';
		
			foreach ($context['groups'] as $group)
			{
				if ($group['ID_GROUP'] == 1)
					continue;
				
				echo '<input type="checkbox" name="managers[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" />', $group['groupName'], '<br />';
			}					
		
		echo '    
		    </td>
		   </tr>
		  </table>
		 </td>
	  </tr> 
	  <tr>
	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_additional_settings'],'</b>&nbsp;</td>
	    <td width="80%" class="windowbg2"><input type="checkbox" name="can_collapse" />',$txt['ezp_txt_can_collapse'],'  <input type="checkbox" name="hidetitlebar" />',$txt['ezp_hidetitlebar'],'
		<input type="checkbox" name="hidemobile" />',$txt['ezp_hidemobile'],' <input type="checkbox" name="showonlymobile" />',$txt['ezp_showonlymobile'],'
		</td>
	  </tr>
	  
	  <tr>
	    <td width="20%"  class="windowbg2" align="right">', $txt['ezp_txt_css_header_class'],'&nbsp;</td>
	    <td width="80%"  class="windowbg2"><input type="text" name="block_header_class" size="60" value="" /></td>
	  </tr>
	 <tr>
	    <td width="20%"  class="windowbg2" align="right">', $txt['ezp_txt_css_body_class'],'&nbsp;</td>
	    <td width="80%"  class="windowbg2"><input type="text" name="block_body_class" size="60" value="" /></td>
	  </tr>
	  
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    <input type="hidden" name="sc" value="', $context['session_id'], '" />	
	    <input type="hidden" name="blocktype" value="',$context['ezportal_blocktype'],'" />
	    <input type="submit" name="addblock" value="',$txt['ezp_addblock'],'" /></td>
	  </tr> 
	  </table>
  	</form>
  ';
	
}

function template_ezportal_edit_block()
{
	global $context, $txt, $scripturl, $ezpSettings;
		
echo '
	<form method="post" name="frmeditblock" id="frmeditblock" action="', $scripturl, '?action=ezportal;sa=editblock2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td colspan="2"  align="center" class="catbg">
	    <b>', $txt['ezp_editblock'], '</b></td>
	  </tr>
	  <tr>
	    <td width="20%"  class="windowbg2" align="right"><b>', $txt['ezp_txt_block_title'],'</b>&nbsp;</td>
	    <td width="80%"  class="windowbg2"><input type="text" name="blocktitle" size="60" value="',$context['ezp_block_info']['customtitle'],'" /></td>
	  </tr>
	  <tr class="windowbg2">
	    <td width="20%" align="right"><b>', $txt['ezp_txt_column'],'</b>&nbsp;</td>
	    <td width="80%">
	    <select name="column">
	    ';
	  	
		foreach ($context['ezp_columns'] as $column)
				echo '<option value="', $column['id_column'], '" ',($column['id_column'] == $context['ezp_block_info']['id_column'] ? ' selected="selected" ' : ''), '>', $column['column_title'], '</option>';
							
	
	echo '</select>
	    </td>
	  </tr>
	 <tr class="windowbg2">
	    <td width="20%" align="right"><b>', $txt['ezp_txt_icon'],'</b>&nbsp;</td>
	 <td width="80%">
	    <select id="iconchoice" name="icon" onchange="ChangeIconPic(this.value)">
	    <option value="0"',(empty($context['ezp_block_info']['id_icon']) ? ' selected="selected" ': ''),  '>',$txt['ezp_txt_noicon'],'</option>
	    ';
	  	
		foreach ($context['ezp_icons'] as $icon)
				echo '<option value="', $icon['id_icon'], '"',(($context['ezp_block_info']['id_icon'] == $icon['id_icon']) ? ' selected="selected" ': ''),  '>', $icon['icon'], '</option>';
							
	
	echo '</select> <img id="iconPick" src="" alter="" />
	
	
		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	// iconSelect
	var selectIcons = new Array();
	selectIcons[0] = "";
	';
	
	foreach($context['ezp_icons'] as $c => $icon)
		echo 'selectIcons[',$icon['id_icon'],'] = "' . $ezpSettings['ezp_url'] . 'icons/' . $icon['icon'] .  '";' . "\n";

	echo  '
	function ChangeIconPic(iconIndex)
	{
		document.frmeditblock.iconPick.src = selectIcons[iconIndex];
	}
	
	ChangeIconPic(document.frmeditblock.iconchoice.selectedIndex);
	
	// ]]></script>
	
	
	    </td>
	  </tr>';

	// Check if we need to setup an editable box for this ezBlock
	if ($context['ezp_block_info']['data_editable'] == 1)
	{
		echo '<tr class="windowbg2">
				 <td width="20%" align="right"><b>', $txt['ezp_txt_block_data'],'</b>&nbsp;</td>
				 <td width="80%">
				 <textarea name="blockdata" id="myTextEditor" rows="10" cols="60">',$context['ezp_block_info']['blockdata'],'</textarea>
				 </td>	 
			</tr>
		';
		
		if ($context['ezp_showtinymcetoggle'] == true)
		{
			echo '<tr class="windowbg2"><td colspan="2">
			  <a href="#" id="switchtinymce" onclick="switchTinyMCE(this.id, \'blockdata\'); return false;">' . $txt['ezp_hide_tinymce'] . '</a>
			<script language="javascript">

function toggleEditor(id) {
	if (!tinyMCE.get(id))
		tinyMCE.execCommand(\'mceAddControl\', false, id);
	else
		tinyMCE.execCommand(\'mceRemoveControl\', false, id);
}

function switchTinyMCE(link_id, textarea_id) {
  toggleEditor(textarea_id);
  var link = document.getElementById(link_id);
  link.innerHTML = (link.innerHTML == \'' . $txt['ezp_hide_tinymce'] . '\') ? \'' . $txt['ezp_show_tinymce'] . '\' : \'' . $txt['ezp_hide_tinymce'] . '\';
  link.title = link.innerHTML;
}

tinyMCE.execCommand(\'mceAddControl\', false, \'myTextEditor\');

			</script>
			</td>
			</tr>';
		}
		
	}
	
	if ($context['ezp_block_info']['blocktitle'] == 'Menu ezBlock')
	{
		// Display the menu items
		echo '<tr class="windowbg2">
				<td colspan="2">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr class="titlebg">
							<td>',$txt['ezp_txt_menu_title'],'</td>
							<td>',$txt['ezp_txt_menu_enabled'],'</td>
							<td>', $txt['ezp_txt_menu_order'],'</td>
							<td>',$txt['ezp_txt_options'],'</td>
						</tr>';

					$styleClass = 'windowbg2';	
		
					foreach($context['ezp_menu_block_items'] as $menuRow)
					{
						echo '<tr class="' . $styleClass . '">
								<td>' . $menuRow['title'] . '</td>
								<td>' . ($menuRow['enabled'] == 1 ? $txt['ezp_yes'] : $txt['ezp_no']) . '</td>
								<td><a href="',$scripturl,'?action=ezportal;sa=menuup&id=',$menuRow['id_menu'],'&block=',$context['ezp_block_info']['id_layout'],'">' . $txt['ezp_txt_menu_up'] . '</a>&nbsp;<a href="',$scripturl,'?action=ezportal;sa=menudown&id=',$menuRow['id_menu'],'&block=',$context['ezp_block_info']['id_layout'],'">' . $txt['ezp_txt_menu_down'] . '</a></td>
								<td>
								<a href="',$scripturl,'?action=ezportal;sa=menuedit&id=',$menuRow['id_menu'],'">',$txt['ezp_edit'], '</a>
							&nbsp;
							<a href="',$scripturl,'?action=ezportal;sa=menudelete&id=',$menuRow['id_menu'],'">',$txt['ezp_delete'], '</a></td>

							</tr>';
						
						if ($styleClass == 'windowbg2')
							$styleClass = 'windowbg';	
						else 
							$styleClass = 'windowbg2';	
					}
		
		echo '	<tr class="windowbg2">
					<td colspan="4" align="center"><a href="', $scripturl, '?action=ezportal;sa=menuadd;block=',$context['ezp_block_info']['id_layout'],'">' . $txt['ezp_txt_menu_add'] . '</a></td>
				</tr>
					</table>
				</td>
			</tr>';
	}
	
	
	// Now show all parameters for this ezBlock
	foreach ($context['ezp_block_parameters'] as $parameter)
	{
		if ($parameter['parameter_type'] == 'hidden')
			continue;
		
		echo '<tr class="windowbg2">
				 <td width="20%" align="right"><b>', $parameter['title'],($parameter['required'] == 1 ? '<span style="color: red;">*</span>' : ''),'</b>&nbsp;</td>
				 <td width="80%">';
		
				if ($parameter['parameter_type'] == 'select')
				{
						echo '<select name="parameter[', $parameter['id_parameter'],']">';
						foreach ($context['ezp_select_' . $parameter['id_parameter']] as $key => $option)
							 echo '<option value="' . $key . '" ' . ($key == $parameter['data'] ? ' selected="selected" ' : '') .  '>' . $option . '</option>';
						echo '</select>';
				}
				else if  ($parameter['parameter_type'] == 'checkbox')
				{
					echo '<input type="checkbox" name="parameter[', $parameter['id_parameter'],']" ', ($parameter['data'] == 1 ? ' checked="checked" ' : ''),' />';
				}
				else if ($parameter['parameter_type'] == 'boardselect')
				  {
				  	echo '<select name="parameter[', $parameter['id_parameter'],']">';

						foreach ($context['ezportal_boards'] as $key => $option)
							 echo '<option value="' . $key . '" ' . ($key == $parameter['data'] ? ' selected="selected" ' : '') .  '>' . $option . '</option>';
					
					echo '</select>';
				  }
				  else if ($parameter['parameter_type'] == 'multiboardselect')
				  {
				  	$myboards = explode(',',$parameter['data'] );
				  	
				  	echo '<select name="parameter[', $parameter['id_parameter'],'][]" size="5" multiple="multiple">';

						foreach ($context['ezportal_boards'] as $key => $option)
						{
							
							if (empty($option ))
								continue;
								
							$selected = '';
							
							if (in_array($key,$myboards))
								$selected = ' selected="selected" ';
							
							 echo '<option value="' . $key . '"' . $selected  . '>' . $option . '</option>';
						}
					echo '</select>';
				  }
 				else if ($parameter['parameter_type'] == 'bbc')
				   {
				   	
				   	echo '<table>';
  						 theme_postbox($parameter['data']);
   					echo '</table>';
				   	 						
				   }
				  
				  else 
				  {
						echo '<input type="text" name="parameter[', $parameter['id_parameter'],']" size="60" value="', $parameter['data'],'" />';
					}
					
					echo '	
					</td>	 
			</tr>
		';
		
		
	}
	
	
	echo '
	  <tr>
	    <td colspan="2" class="windowbg2"  valign="top">
	    <table width="50%" align="center">
	    <tr>
		    <td>
		    <b>', $txt['ezp_txt_permissions'],'</b><br />';
	
			$permissionsGroups = explode(',',$context['ezp_block_info']['permissions']);
	
			echo '
	   	 	<input type="checkbox" name="groups[-1]" value="-1" ', ((in_array(-1,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_guests'],'<br />
	   	 	<input type="checkbox" name="groups[0]" value="0" ', ((in_array(0,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_members'],'<br />';
	
			foreach ($context['groups'] as $group)
				echo '<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />', $group['groupName'], '<br />';
							
		
		echo '<input type="checkbox" id="checkAllGroups"  onclick="invertAll(this, this.form, \'groups\');" class="check" />', 
$txt['ezp_txt_checkall'],'
		        
		    </td>
		    <td class="windowbg2" valign="top"><b>', $txt['ezp_txt_block_managers'],'</b><br />
		    ';
		
			$blockManagers = explode(',',$context['ezp_block_info']['blockmanagers']);
		
			foreach ($context['groups'] as $group)
			{
				if ($group['ID_GROUP'] == 1)
					continue;
	
				echo '<input type="checkbox" name="managers[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$blockManagers) == true) ? ' checked="checked" ' : ''), ' />', $group['groupName'], '<br />';

			}		
		echo '    
		    </td>
		   </tr>
		  </table>
		 </td>
	  </tr> 
	  <tr>
	    <td width="20%"  class="windowbg2" align="right"><b>', $txt['ezp_txt_additional_settings'],'</b>&nbsp;</td>
	    <td width="80%"  class="windowbg2"><input type="checkbox" name="can_collapse" ',($context['ezp_block_info']['can_collapse'] ? ' checked="checked" ' : ''), ' />',$txt['ezp_txt_can_collapse'],' <input type="checkbox" name="hidetitlebar" ',($context['ezp_block_info']['hidetitlebar'] ? ' checked="checked" ' : ''), ' />',$txt['ezp_hidetitlebar'],' 
	     <input type="checkbox" name="hidemobile" ',($context['ezp_block_info']['hidemobile'] ? ' checked="checked" ' : ''), ' />',$txt['ezp_hidemobile'],' <input type="checkbox" name="showonlymobile" ',($context['ezp_block_info']['showonlymobile'] ? ' checked="checked" ' : ''), ' />',$txt['ezp_showonlymobile'],'
		</td>
	  </tr>
	  
	  <tr>
	    <td width="20%"  class="windowbg2" align="right">', $txt['ezp_txt_css_header_class'],'&nbsp;</td>
	    <td width="80%"  class="windowbg2"><input type="text" name="block_header_class" size="60" value="',$context['ezp_block_info']['block_header_class'],'" /></td>
	  </tr>
	 <tr>
	    <td width="20%"  class="windowbg2" align="right">', $txt['ezp_txt_css_body_class'],'&nbsp;</td>
	    <td width="80%"  class="windowbg2"><input type="text" name="block_body_class" size="60" value="',$context['ezp_block_info']['block_body_class'],'" /></td>
	  </tr>
	  
	  <tr>
	    <td width="20%"  class="windowbg2" align="right"><b>', $txt['ezp_txt_visible_areas'],'</b>&nbsp;</td>
	    <td width="80%"  class="windowbg2"><a href="', $scripturl, '?action=ezportal;sa=visiblesettings;block=',$context['ezp_block_info']['id_layout'],'">',$txt['ezp_txt_update_visible_options'],'</a></td>
	  </tr>
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    <input type="hidden" name="sc" value="', $context['session_id'], '" />	
	    <input type="hidden" name="block" value="',$context['ezp_block_info']['id_layout'],'" />
	    <input type="submit" name="editblock" value="',$txt['ezp_editblock'],'" /></td>
	  </tr> 
	  </table>
  	</form>
  ';
	
}

function template_ezportal_delete_block()
{
	global $context, $txt, $scripturl;
	
echo '
	<form method="post" name="frmdelblock" id="frmdelblock" action="', $scripturl, '?action=ezportal;sa=deleteblock2" accept-charset="', $context['character_set'], '">
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
	    <td colspan="2"  align="center">
	    <b>', $txt['ezp_deleteblock'], '</b></td>
	  </tr>
	   <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    ',$txt['ezp_deleteblock_confirm'],'<br />
	    <b>',$context['ezp_block_layout_title'],'</b>
	    </td>
	  </tr>
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    <input type="hidden" name="sc" value="', $context['session_id'], '" />	
	    <input type="hidden" name="blockid" value="',$context['ezp_block_layout_id'],'" />
	    <input type="submit" name="delblock" value="',$txt['ezp_deleteblock'],'" /></td>
	  </tr> 
	  </table>
  	</form>
  ';
	
}

function template_ezportal_download_block()
{
	global $context, $txt, $scripturl;
	
	echo '<table border="0" width="90%" cellspacing="0" align="center" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>',$txt['ezp_txt_import_block'],'</td>
			</tr><tr>
				<td class="windowbg2" style="padding: 8px;">
					<form action="',$scripturl,'?action=ezportal;sa=importblock" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data" style="margin-bottom: 0;">
						<b>',$txt['ezp_txt_import_block_file'],'</b> <input type="file" name="blockfile" size="45" />

						<div style="margin: 1ex;" align="right"><input type="submit" value="',$txt['ezp_txt_upload_block'],'" /></div>
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
					</form>
				</td>
			</tr>
			<tr class="titlebg">
				<td>',$txt['ezp_txt_createezBlock'],'</td>
			</tr><tr>
				<td class="windowbg2" style="padding: 8px;">
					',$txt['ezp_txt_createezBlock_note'],'
				</td>
			</tr>
		</table>';
		
}

function template_ezportal_installed_blocks()
{
	global $context, $txt, $scripturl;
	
echo '
	<table border="0" width="90%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['ezp_installed_blocks'], '</td>
		</tr>
		<tr>
			<td class="windowbg">

				<table border="0" cellpadding="1" cellspacing="0" width="100%" align="center" class="tborder">

					<tr class="titlebg">
						<td>',$txt['ezp_txt_block_title'] ,'</td>
						<td>',$txt['ezp_txt_block_version'] ,'</td>
						<td>',$txt['ezp_txt_block_author'] ,'</td>
						<td>',$txt['ezp_txt_options'],'</td>
					</tr>';

				$styleclass = 'windowbg';
					
				foreach($context['ezportal_installed_blocks']  as $row)	
				{
					echo '<tr class="',$styleclass,'">
						<td>',$row['blocktitle'] ,'</td>
						<td>',$row['blockversion'] ,'</td>
						<td>', ($row['blockwebsite'] == '' ? $row['blockauthor'] : '<a href="' . $row['blockwebsite'] . '" target="_blank">' . $row['blockauthor'] . '</a>') ,'</td>
						<td>';
						
						if ($row['no_delete'] == 0)
							echo '<a href="',$scripturl,'?action=ezportal;sa=uninstallblock;block=' . $row['id_block'],';sesc=',$context['session_id'],'">',$txt['ezp_txt_uninstall_block'],'</a>';
						
						echo '
						</td>
						</tr>';
						
						if ($styleclass == 'windowbg')
							$styleclass = 'windowbg2';
						else 
							$styleclass = 'windowbg';
				}

				
			echo '</table>
			</td>
		</tr>
	</table>';
}

function template_ezblock_above()
{
	global $context, $scripturl, $settings;
	
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Top')
		{
			if (count($ezColumn['blocks']) == 0)
				      		continue;
echo '<!--start column ' . $ezColumn['column_title'] . '-->';
			 if ($ezColumn['can_collapse'] == 1)
		      	 	echo ' <img src="' . $settings['images_url'] . '/' .( $ezColumn['IsCollapsed'] ? 'expand.gif' : 'collapse.gif' ). '" onclick="javacscript:EzToogle(\'column',$ezColumn['id_column'],'\',',$ezColumn['id_column'],',this,0)" alt="+-" />';
		  
			
			 echo '<table style="margin: 0 auto;"  width="100%">
				<tr>';
			 
		      	
				if (empty($ezColumn['column_percent']))
					echo '<td width="',$ezColumn['column_width'], 'px" valign="top" align="center"  id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
				else 
					echo '<td width="',$ezColumn['column_percent'], '%" valign="top" align="center"  id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
				
			// Show Collapse icon if allowed for column if allowed
				ShowEzBlocks($ezColumn);
			 
			 echo '</td></tr>
			 </table><br />
			 ';

			 echo '<!--end column ' . $ezColumn['column_title'] . '-->';
		}
		
    // Column can collaspe 
      if ($ezColumn['column_title'] == 'Left' || $ezColumn['column_title'] == 'Right')
      {
      	
      	if (count($ezColumn['blocks']) == 0)
				      continue;
      	
      	 if ($ezColumn['can_collapse'] == 1)
      	 {
      	 	echo '<img src="' . $settings['images_url'] . '/' .( $ezColumn['IsCollapsed'] ? 'expand.gif' : 'collapse.gif' ). '" onclick="javacscript:EzToogle(\'column',$ezColumn['id_column'],'\',',$ezColumn['id_column'],',this,0)" alt="+-" />';
      	 }
      }
		
	 
	}
	// End Top Blocks
	
	$shownHeader = false;

	 
	 
	$center = 0;
	$right = 0;
	$left = 0;


	$onlyCenter = 1;
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{

		if ($ezColumn['column_title'] == 'Top' || $ezColumn['column_title'] == 'Bottom')
			continue;
		if (count($ezColumn['blocks']) == 0)
			continue;

		if ($ezColumn['column_title'] != 'Center')
			$onlyCenter = 0;
	}
	$tdopen = 0;

	foreach($context['ezPortalColumns'] AS $ezColumn)
	{



		if ($ezColumn['column_title'] == 'Right' || $ezColumn['column_title'] == 'Top' || $ezColumn['column_title'] == 'Bottom')
			continue;
			
		//if (count($ezColumn['blocks']) == 0 && $ezColumn['column_title'] != 'Center')
		//	continue;
		if (count($ezColumn['blocks']) == 0)
			continue;
		echo '<!--start column ' . $ezColumn['column_title'] . '-->';


	if ($shownHeader == false)
	{
	 echo '<table style="margin: 0 auto;"  width="100%">
		<tr>';
	 $shownHeader = true;

	}

    // Column can collaspe
    /*
    if ($ezColumn['column_title'] == 'Left' || $ezColumn['column_title'] == 'Right')
      {
      	 if ($ezColumn['can_collapse'] == 1)
      	 {
      	 	echo ' <img src="' . $settings['images_url'] . '/' .( $ezColumn['IsCollapsed'] ? 'expand.gif' : 'collapse.gif' ). '" onclick="javacscript:EzToogle(\'column',$ezColumn['id_column'],'\',',$ezColumn['id_column'],',this,0)" alt="+-" />';
      	 }
      }
      */
			
		if (empty($ezColumn['column_percent']))
			echo '<td width="',$ezColumn['column_width'], 'px" valign="top"  id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
		else 
			echo '<td width="',$ezColumn['column_percent'], '%" valign="top"  id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
		
		// Show Collapse icon if allowed for column if allowed
		ShowEzBlocks($ezColumn);

		//if ($ezColumn['column_title'] != 'Center')
			echo '</td><!-- end not center -->';
		$tdopen = 1;
		echo '<!--end column.. ' . $ezColumn['column_title'] . '-->';

	}
	

	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Left')
			$left = 1;

		if ($ezColumn['column_title'] == 'Center' )
			$center = 1;
		if ($ezColumn['column_title'] == 'Right')
			$right = 1;

	}

	if ($onlyCenter == true && $tdopen == 1)
	{
		echo '</tr></table>';
		$shownHeader  = false;
	}


	if ($right == 1 || $left == 1 || $center == 1)
	{
		if ($shownHeader == false)
		{
		 echo '<table style="margin: 0 auto;"  width="100%">
			<tr>';
		 $shownHeader = true;
		 
		}
		
		echo '<td valign="top">';

	}
	/*
	$count = 0;
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Left' || $ezColumn['column_title'] == 'Center' )
		$count++;
	}


	if ($count != 0)
		echo '<td>';
		*/


}

function ShowEzBlocks($ezColumn)
{
	global $context, $txt, $settings, $scripturl, $ezpSettings;

	if ($ezColumn['column_title'] == 'Center')
	{
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'ezportal')
			return;
	}


      if(empty($ezColumn['blocks']))
         return;
         $count = 0;
     foreach($ezColumn['blocks'] as $ezBlock)
		{	
			$count++;
		}
		
	if ($count == 0)
		return;
		
         
      echo '
      <table class="bordercolor" width="100%">';
	
		foreach($ezColumn['blocks'] as $ezBlock)
		{
			$headerClass = 'catbg';
			$bodyClass = 'windowbg2';

			if (!empty($ezBlock['block_header_class']))
				$headerClass = $ezBlock['block_header_class'];

			if (!empty($ezBlock['bodyClass']))
				$bodyClass = $ezBlock['block_body_class'];

			
			if (empty($ezBlock['hidetitlebar']))
			{
				echo '<tr>
							<td class="' . $headerClass . '">';
				
							// Check if they can collapse the ezBlock
							if ($ezBlock['can_collapse'])
								echo ' <span style="float:right"><img src="' . $settings['images_url'] . '/' .( $ezBlock['IsCollapsed'] ? 'expand.gif' : 'collapse.gif' ). '" onclick="javacscript:EzToogle(\'block',$ezBlock['id_layout'],'\',',$ezBlock['id_layout'],',this,1)" alt="+-" /></span>';
	
							// Show icon
							if (!empty($ezBlock['icon']))
								echo '<img src="',$ezpSettings['ezp_url'],"icons/" . $ezBlock['icon'] . '" alt="" /> ';
						
							// Custom Title
					 		echo $ezBlock['customtitle'];
							
							if (($context['ezportal_block_manager'] == 1 || $ezBlock['IsManager'] == 1) && empty($ezpSettings['ezp_hide_edit_delete']))
							{
								/*
								echo '<br /><a href="',$scripturl,'?action=ezportal;sa=editblock;block=',$ezBlock['id_layout'],'">',$txt['ezp_edit'], '</a>
								&nbsp;
								<a href="',$scripturl,'?action=ezportal;sa=deleteblock;block=',$ezBlock['id_layout'],'">',$txt['ezp_delete'], '</a>';
								*/
								echo ' <a href="',$scripturl,'?action=ezportal;sa=editblock;block=',$ezBlock['id_layout'],'"><img src="',$ezpSettings['ezp_url'],'icons/page_white_edit.png" alt="',$txt['ezp_edit2'], '" /></a>
								&nbsp;
								<a href="',$scripturl,'?action=ezportal;sa=deleteblock;block=',$ezBlock['id_layout'],'"><img src="',$ezpSettings['ezp_url'],'icons/plugin_delete.png" alt="',$txt['ezp_delete2'], '" /></a>';
	
							}
				
							echo '</td>
						</tr>';
			}
						
				echo '
					<tr class="' . $bodyClass . '"><td id="block',$ezBlock['id_layout'],'" ',($ezBlock['IsCollapsed'] ? 'style="display:none"' : ''),'>';

				// Check the ezblock type
				if (strtolower($ezBlock['blocktype']) == 'html')
					echo html_entity_decode($ezBlock['blockdata'],ENT_QUOTES);
				else if (strtolower($ezBlock['blocktype']) == 'php')
				{

					// Add Parameters
					$parameters = '';
				
					if (isset($ezBlock['parameters']))
					{
						foreach($ezBlock['parameters'] as $parameter)
						{
							$parameters .= '$' . $parameter['parameter_name'] . " = '" . $parameter['data'] . "';\n";
						}
					}
					
					// Check PHP code is valid
					if (EzPortalCheck_syntax($parameters . html_entity_decode($ezBlock['blockdata'],ENT_QUOTES)))
					{
						// PHP code looks good lets run it!!
						eval($parameters . html_entity_decode($ezBlock['blockdata'],ENT_QUOTES));
					}
					else 
						echo $txt['ezp_err_no_php_syntax_eror'];
				}
				else if (strtolower($ezBlock['blocktype']) == 'builtin')
				{
				
					
					if (isset($ezBlock['parameters']))
					{
						
						// Append the ezBlock ID
						$ezBlock['parameters'][] = array("data" => $ezBlock['id_layout'], 'parameter_name' => 'ezblocklayoutid');
						
						call_user_func($ezBlock['blockdata'], $ezBlock['parameters']);
					}
					else 
						call_user_func($ezBlock['blockdata']);
				}
				
			echo '</td>
			</tr>';
			
		}
		
		echo '</table>
		';
}

function template_ezblock_below()
{
	global $context, $settings;

	$count = 0;
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Left' || $ezColumn['column_title'] == 'Center' )
		$count++;
	}


	//if ($count != 0)
		echo '</td><!-- end center -->'; // End center ezBlock

	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] != 'Right')
			continue;
			
		if (count($ezColumn['blocks']) == 0)
			continue;

	echo '<!--start column ' . $ezColumn['column_title'] . '-->';

    // Column can collaspe
    /*
      if ($ezColumn['column_title'] == 'Left' || $ezColumn['column_title'] == 'Right')
      {
      	 if ($ezColumn['can_collapse'] == 1)
      	 {
      	 	echo ' <img src="' . $settings['images_url'] . '/' .( $ezColumn['IsCollapsed'] ? 'expand.gif' : 'collapse.gif' ). '" onclick="javacscript:EzToogle(\'column',$ezColumn['id_column'],'\',',$ezColumn['id_column'],',this,0)" alt="+-" />';
      	 }
      }
      
      */
			
		if (empty($ezColumn['column_percent']))
			echo '<td width="',$ezColumn['column_width'], 'px" valign="top"  id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
		else 
			echo '<td width="',$ezColumn['column_percent'], '%" valign="top"  id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
		
		// Show Collapse icon if allowed
		ShowEzBlocks($ezColumn);

		echo '</td><!-- end not right -->';

		echo '<!--start column ' . $ezColumn['column_title'] . '-->';

	}
	
	if (count($context['ezPortalColumns']) != 0)
		echo '
		</tr>
		</table>';
	
	
	// Bottom Blocks
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Bottom')
		{
			
			if (count($ezColumn['blocks']) == 0)
				      		continue;

			echo '<!--start column ' . $ezColumn['column_title'] . '-->';

			if ($ezColumn['can_collapse'] == 1)
		      	 	echo ' <img src="' . $settings['images_url'] . '/' .( $ezColumn['IsCollapsed'] ? 'expand.gif' : 'collapse.gif' ). '" onclick="javacscript:EzToogle(\'column',$ezColumn['id_column'],'\',',$ezColumn['id_column'],',this,0)" alt="+-" />';
		  
			
			 echo '<br /><table style="margin: 0 auto;"  width="100%">
				<tr>';
			 
		      	 
				if (empty($ezColumn['column_percent']))
					echo '<td width="',$ezColumn['column_width'], 'px" valign="top" align="center"  id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
				else 
					echo '<td width="',$ezColumn['column_percent'], '%" valign="top" align="center"  id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
				
			// Show Collapse icon if allowed for column if allowed
				ShowEzBlocks($ezColumn);
			 
			 echo '</td></tr>
			 </table><!-- end bottom -->
			 ';

			 echo '<!--end column ' . $ezColumn['column_title'] . '-->';
		}
	 
	}

	// Show EzPortal Copyright
	
	// DO NOT MODIFY OR REMOVE THIS COPYRIGHT UNLESS THE BRANDING FREE OPTION HAS BEEN PURCHASED
	// http://www.ezportal.com/copyright_removal.php
    $showInfo = EzPortalCheckInfo();
    
    if ($showInfo == true)
	   echo '<div align="center"><span class="smalltext">Powered by <a href="https://www.ezportal.com" target="blank">EzPortal</a></span></div>';
	
}

function template_ezportal_frontpage()
{
	// Empty place holder just for the frontpage.	
}

function template_ezportal_visible_options()
{
	global $txt, $context, $scripturl;
	
echo '
	<form method="post" name="frmvisoption" id="frmvisoption" action="', $scripturl, '?action=ezportal;sa=visiblesettings2" accept-charset="', $context['character_set'], '">
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
	    <td colspan="2"  align="center">
	    <b>', $txt['ezp_txt_visible_areas'], '</b></td>
	  </tr>
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    <input type="checkbox" name="all" value="all" ',($context['ezp_all'] ? ' checked="checked" ' : ''),' />',$txt['ezp_txt_visible_all'],'<br />
	    <input type="checkbox" name="cus[forum]" value="forum" ',(in_array('forum',$context['ezp_visibleCustom']) ? ' checked="checked"' : '' ) ,'  />',$txt['ezp_txt_visible_forum'],'<br />
	    <input type="checkbox" name="cus[boardindex]" value="boardindex"  ',(in_array('boardindex',$context['ezp_visibleCustom']) ? ' checked="checked"' : '' ) ,' />',$txt['ezp_txt_visible_board_index']  ,'<br />		
	     
	    <input type="checkbox" name="cus[portal]" value="portal"  ',(in_array('portal',$context['ezp_visibleCustom']) ? ' checked="checked"' : '' ) ,' />',$txt['ezp_txt_visible_homepage'] ,'<br />
	    
	    
	    </td>
	  </tr>
	  <tr>
	    <td width="50%" class="windowbg2"  valign="top"><b>', $txt['ezp_txt_visible_actions'],'</b><br />';
		
		foreach ($context['ezportal_actions'] as $ezpActions)
		{
			if ($ezpActions['is_mod'] == 0)
				echo '<input type="checkbox" name="visactions[', $ezpActions['action'], ']" value="', $ezpActions['action'], '" ' . ($ezpActions['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpActions['title'], '<br />';
		}
								
	echo '
	    </td>
	    <td width="50%" valign="top" class="windowbg2"><b>', $txt['ezp_txt_visible_actions_modifcations'] ,'</b><br />
	    <span class="smalltext">',$txt['ezp_txt_visible_actions_modifcations_note'],'</span><br />';
		
		foreach ($context['ezportal_actions'] as $ezpActions)
		{
			if ($ezpActions['is_mod'] == 1)
				echo '<input type="checkbox" name="visactions[', $ezpActions['action'], ']" value="', $ezpActions['action'], '" ' . ($ezpActions['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpActions['title'], ' <a href="', $scripturl, '?action=ezportal;sa=deletevisibleaction;newaction=', $ezpActions['action'], '">',$txt['ezp_delete'],'</a><br />';
		}
								
	echo '<a href="', $scripturl, '?action=ezportal;sa=addvisibleaction">',$txt['ezp_txt_visible_add_new_action'],'</a><br />
	     <b>',$txt['ezp_txt_visible_pages'],'</b><br />
		';
	
		foreach ($context['ezportal_pages'] as $ezpPages)
		{
				echo '<input type="checkbox" name="vispages[', $ezpPages['id_page'], ']" value="', $ezpPages['id_page'], '" ' . ($ezpPages['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpPages['title'], '<br />';
		}
	
		echo '
	
	
	</td>
	  </tr> 
	  <tr>
	   
	    <td width="50%" colspan="2" class="windowbg2"  valign="top"><b>', $txt['ezp_txt_visible_boards'],'</b><br />';
		
		foreach ($context['ezportal_boards'] as $ezpBoards)
			echo '<input type="checkbox" name="visboards[',$ezpBoards['ID_BOARD'], ']" value="', $ezpBoards['ID_BOARD'], '" ' . ($ezpBoards['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpBoards['bName'], '<br />';
					
	echo '
	    </td>
	  </tr> 
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    <input type="hidden" name="block" value="', $context['ezportal_block'], '" />
	    <input type="hidden" name="column" value="', $context['ezportal_column'], '" />
	    <input type="submit" name="addblock" value="', $txt['ezp_txt_savevisible'], '" /></td>
	  </tr> 
	  </table>
  	</form>
  ';
	
	
}

function template_ezportal_add_visible_action()
{
	global $txt, $scripturl, $context;
	
echo '
	<form method="post" name="addaction" id="addaction" action="', $scripturl, '?action=ezportal;sa=addvisibleaction2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td colspan="2"  align="center" class="catbg">
	    <b>', $txt['ezp_txt_visible_add_new_action'], '</b></td>
	  </tr>
	  <tr>
	    <td width="30%"  class="windowbg2" align="right"><b>', $txt['ezp_txt_visible_add_new_action_title'],'</b>&nbsp;</td>
	    <td width="70%"  class="windowbg2"><input type="text" name="actiontitle" size="50" value="" /></td>
	  </tr>
	  <tr>
	    <td width="30%"  class="windowbg2" align="right"><b>', $txt['ezp_txt_visible_add_new_action_action'],'</b>&nbsp;</td>
	    <td width="70%"  class="windowbg2"><input type="text" name="newaction" size="50" value="" />
	    <span class="smalltext">' . $txt['ezp_txt_visible_add_new_action_note'] . '</span>
	    </td>
	  </tr>
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    	<input type="hidden" name="sc" value="', $context['session_id'], '" />	
		    <input type="submit" name="addaction" value="',$txt['ezp_txt_visible_add_new_action'],'" /></td>
	  </tr> 
	  </table>
  	</form>
  ';

}

function template_ezportal_delete_shoutboxhistory()
{
	global $txt, $scripturl, $context;

echo '
	<form method="post" name="addaction" id="addaction" action="', $scripturl, '?action=ezportal;sa=deleteshouthistory2">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td colspan="2"  align="center" class="catbg">
	    <b>', $txt['ezp_txt_deleteallshoutbox'], '</b></td>
	  </tr>
	  <tr>
	    <td colspan="2" align="center"  class="windowbg2"><b>', $txt['ezp_txt_deleteallshoutbox_confirm'],'</b></td>
	  </tr>
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    	<input type="hidden" name="sc" value="', $context['session_id'], '" />
		    <input type="submit" value="',$txt['ezp_txt_deleteallshoutbox'],'" /></td>
	  </tr>
	  </table>
  	</form>
  ';

}

function template_ezportal_shoutbox_history()
{
	global $txt, $context, $ezpSettings, $scripturl;
	
	$adminShoutBox = allowedTo('admin_forum');
	
	echo '<table cellpadding="0" cellspacing="0" width="100%" class="bordercolor">
	<tr class="catbg">
		<td>',$txt['ezp_txt_shouthistory'],'</td>
	</tr>
	';
	
	$styleClass = "windowbg";
	
	foreach($context['ezshouts_history'] as $row)
	{
		echo '<tr class="',$styleClass,'">';
		echo '<td>';
		// Censor the shout
		censorText($row['shout']);
		
		if ($ezpSettings['ezp_shoutbox_showdate'])
			echo timeformat($row['date']) . ' ';

		echo '<a href="',$scripturl,'?action=profile;u=',$row['id_member'],'" style="color: ' . $row['onlineColor'] . ';">',$row['realName'],'</a>
		';
		
		echo $txt['ezp_shoutbox_says'];
		
		echo parse_bbc($row['shout']);
		
		if ($adminShoutBox)
			echo ' <a href="',$scripturl,'?action=ezportal;sa=removeshout;shout=',$row['id_shout'],'" style="color: #FF0000">[X]</a>';
			
		echo '
		</td>
		</tr>';
		
		if ($styleClass == 'windowbg')
			$styleClass = 'windowbg2';
		else 
			$styleClass = 'windowbg';
	}
	
	echo '<tr class="windowbg2">
		<td>',$txt['ezp_txt_pages'],$context['page_index'] ,'</td>
	</tr>
	</table>';
    
    IF ($adminShoutBox)
        echo '<br /><a href="',$scripturl,'?action=ezportal;sa=deleteshouthistory">' . $txt['ezp_txt_deleteallshoutbox'] . '</a>';
		
}

function template_ezportal_menu_add()
{
	global $txt, $scripturl, $context;
	
echo '
	<form method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=menuadd2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td colspan="2" align="center" class="catbg">
	    <b>', $txt['ezp_txt_menu_add'], '</b></td>
	  </tr>
	  <tr>
	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_menu_title'],'</b>&nbsp;</td>
	    <td width="80%" class="windowbg2"><input type="text" name="menutitle" size="60" value="" /></td>
	  </tr>
	  
	  <tr>
	  	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_menu_link'],'</b>&nbsp;</td>
	  	    <td width="80%" class="windowbg2"><input type="text" name="menulink" size="60" value="" /></td>
	  </tr>
	  <tr>
	  	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_menu_newwindow'] ,'</b>&nbsp;</td>
	  	    <td width="80%" class="windowbg2"><input type="checkbox" name="newwindow" /></td>
	  </tr>
	  
	  <tr>
	    <td colspan="2" class="windowbg2"  valign="top">
	    <table width="50%" align="center">
	    <tr>
		    <td colspan="2">
		    <b>', $txt['ezp_txt_permissions'],'</b><br />
		    <input type="checkbox" name="groups[-1]" value="-1" checked="checked" />',$txt['membergroups_guests'],'<br />
		    <input type="checkbox" name="groups[0]" value="0" checked="checked" />',$txt['membergroups_members'],'<br />';
		
			foreach ($context['groups'] as $group)
					echo '<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" checked="checked" />', $group['groupName'], '<br />';
								
		echo '    
		    </td>
		 
		   </tr>
		  </table>
		 </td>
	  </tr> 

	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    <input type="hidden" name="layoutid" value="', $context['ezp_layout_id'], '" />	
	    <input type="hidden" name="sc" value="', $context['session_id'], '" />	
	    <input type="submit" name="addblock" value="',$txt['ezp_txt_menu_add'],'" /></td>
	  </tr> 
	  </table>
  	</form>
  ';
	
}

function template_ezportal_menu_edit()
{
	global $txt, $scripturl, $context;
	
echo '
	<form method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=menuedit2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td colspan="2" align="center" class="catbg">
	    <b>', $txt['ezp_txt_menu_edit'], '</b></td>
	  </tr>
	  <tr>
	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_menu_title'],'</b>&nbsp;</td>
	    <td width="80%" class="windowbg2"><input type="text" name="menutitle" size="60" value="' .  $context['ezp_menu_row']['title'] . '" /></td>
	  </tr>
	   <tr>
	  	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_menu_enabled'] ,'</b>&nbsp;</td>
	  	    <td width="80%" class="windowbg2"><input type="checkbox" name="menuenabled" ' .  ($context['ezp_menu_row']['enabled'] ? ' checked="checked"' : '') . ' />
	  	    </td>
	  </tr>
	  
	  <tr>
	  	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_menu_link'],'</b>&nbsp;</td>
	  	    <td width="80%" class="windowbg2"><input type="text" name="menulink" size="60" value="' .  $context['ezp_menu_row']['linkurl'] . '" /></td>
	  </tr>
	  
	  <tr>
	  	    <td width="20%" class="windowbg2" align="right"><b>', $txt['ezp_txt_menu_newwindow'] ,'</b>&nbsp;</td>
	  	    <td width="80%" class="windowbg2"><input type="checkbox" name="newwindow" ' .  ($context['ezp_menu_row']['newwindow'] ? ' checked="checked"' : '') . ' /></td>
	  </tr>
	  
	  <tr>
	    <td colspan="2" class="windowbg2"  valign="top">
	    <table width="50%" align="center">
	    <tr>
		    <td colspan="2">
		    <b>', $txt['ezp_txt_permissions'],'</b><br />';


		   $permissionsGroups = explode(',',$context['ezp_menu_row']['permissions']);
	
			echo '
	   	 	<input type="checkbox" name="groups[-1]" value="-1" ', ((in_array(-1,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_guests'],'<br />
	   	 	<input type="checkbox" name="groups[0]" value="0" ', ((in_array(0,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_members'],'<br />';
	
			foreach ($context['groups'] as $group)
				echo '<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />', $group['groupName'], '<br />';
									
		echo '    
		    </td>
		 
		   </tr>
		  </table>
		 </td>
	  </tr> 

	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    <input type="hidden" name="layoutid" value="', $context['ezp_menu_row']['id_layout'], '" />	
	    <input type="hidden" name="menuid" value="', $context['ezp_menu_row']['id_menu'], '" />	
	    <input type="hidden" name="sc" value="', $context['session_id'], '" />	
	    <input type="submit" name="editmenu" value="',$txt['ezp_txt_menu_edit'],'" /></td>
	  </tr> 
	  </table>
  	</form>
  ';
}

function template_ezportal_menu_delete()
{
	global $txt, $scripturl, $context;
	
	echo '
	<form method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=menudelete2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td colspan="2" align="center" class="catbg">
	    <b>', $txt['ezp_txt_menu_delete'], '</b></td>
	  </tr>
	  <tr>
	  	 <td colspan="2" align="center" class="windowbg2">
	  	 	', $context['ezp_menu_row']['title'], '<br />
	  	 	', $context['ezp_menu_row']['linkurl'], '
	  	 </td>
	  </tr>
	  
	    <tr>
	    <td colspan="2" class="windowbg2" align="center">
	    <input type="hidden" name="layoutid" value="', $context['ezp_menu_row']['id_layout'], '" />	
	    <input type="hidden" name="menuid" value="', $context['ezp_menu_row']['id_menu'], '" />	
	    <input type="hidden" name="sc" value="', $context['session_id'], '" />	
	    <input type="submit" name="editmenu" value="',$txt['ezp_txt_menu_delete'],'" /></td>
	  </tr> 
	  </table>
  	</form>';
	
}

function template_ezpotal_shoutbox()
{
	global $settings, $context;
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />';
		
	echo $context['html_headers'];
	
	echo '
	<style type="text/css">
			body, td
			{
				
				margin: 0;
				padding: 0px 0px 0px;
			}
	</style>
	</head>
<body class="windowbg2">
<div class="windowbg2">';
}


function template_ezportalcopyright()
{
	global $txt, $scripturl, $context, $boardurl, $modSettings;
	
                    
    $modID = 40;
    
    $urlBoardurl = urlencode(base64_encode($boardurl));                
                    
    	echo '
	<form method="post" action="',$scripturl,'?action=ezportal;sa=copyright;save=1">
<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td colspan="2">', $txt['ezp_txt_copyrightremoval'], '</td>
		</tr>    
	<tr class="windowbg2">
		<td valign="top" align="right">',$txt['ezp_txt_copyrightkey'],'</td>
		<td><input type="text" name="ezp_copyrightkey" size="50" value="' . $modSettings['ezp_copyrightkey'] . '" />
        <br />
        <a href="https://www.smfhacks.com/copyright_removal.php?mod=' . $modID .  '&board=' . $urlBoardurl . '" target="_blank">' . $txt['ezp_txt_ordercopyright'] . '</a>
        </td>
	</tr>
    <tr class="windowbg2">
        <td colspan="2">' . $txt['ezp_txt_copyremovalnote'] . '</td>
    </tr>
    
    
	<tr class="windowbg2">
		<td valign="top" colspan="2" align="center"><input type="submit" value="' . $txt['ezp_savesettings'] . '" />
		</td>
		</tr>
	</table>
	</form>
    

    ';              

}


?>