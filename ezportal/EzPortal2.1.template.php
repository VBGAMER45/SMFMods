<?php
/*
EzPortal
Version 4.5
by:vbgamer45
http://www.ezportal.com
Copyright 2010-2019 http://www.samsonsoftware.com
*/


function template_ezportal_addpage()
{
	global $context, $txt, $scripturl;

	echo '
	<form method="post" name="frmaddpage" id="frmaddpage" action="', $scripturl, '?action=ezportal;sa=addpage2" accept-charset="', $context['character_set'], '"  onsubmit="submitonce(this);">
	<div class="cat_bar">
		<h3 class="catbg centertext">
        	', $txt['ezp_addpage'], '
        </h3>
	</div>
	<div class="windowbg">
		<dl id="ezPortal_admin" class="ezpsettings">
			<dt>
				', $txt['ezp_txt_page_title'],'
			</dt>
			<dd>
				<input type="text" name="pagetitle" size="100">
			</dd>
			
			<dt>
				', $txt['ezp_txt_metatags'],'
			</dt>
			<dd>	
				<textarea name="metatags" rows="5" cols="55" class=""></textarea>
				<br>', $txt['ezp_txt_metatags2'],'
			</dd>

			<dt>
				', $txt['ezp_txt_page_content'],'
			</dt>
	
			
			';

		if ($context['ezp_page_bbc'] == 0)
		{
	echo '
			<dd>
	    		<textarea name="pagecontent" id="myTextEditor" rows="15" cols="55"></textarea>
				<p><a href="#" id="switchtinymce" onclick="switchTinyMCE(this.id, \'myTextEditor\'); return false;">' . $txt['ezp_hide_tinymce'] . '</a></p>
			</dd>';
		}
		else
		{


				   	echo '<dd><table>';

		   		if (!function_exists('getLanguages'))
					{
						// Showing BBC?
						if ($context['show_bbc'])
						{
							echo '
									<tr class="windowbg2">
										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'bbc'), '
										</td>
									</tr>';
						}

						// What about smileys?
						if (!empty($context['smileys']['postform']))
							echo '
									<tr class="windowbg2">
										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'smileys'), '
										</td>
									</tr>';

						// Show BBC buttons, smileys and textbox.
						echo '
									<tr class="windowbg2">
										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'message'), '
										</td>
									</tr>';
					}
					else
					{
						echo '
									<tr class="windowbg2">
										<td colspan="2">';
							// Showing BBC?
							if ($context['show_bbc'])
							{
								echo '
											<div id="bbcBox_message"></div>';
							}

							// What about smileys?
							if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
								echo '
											<div id="smileyBox_message"></div>';

							// Show BBC buttons, smileys and textbox.
											template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');


							echo '
										</td>
									</tr>';
						}
							echo '
								</table></dd>';


			}



echo '
			<dt>
				', $txt['ezp_txt_permissions'],'
			</dt>
			<dd>
	    		<input type="checkbox" name="groups[-1]" value="-1">',$txt['membergroups_guests'],'<br>
				<input type="checkbox" name="groups[0]" value="0">', $txt['membergroups_members'],'<br>';

	foreach ($context['groups'] as $group)
	echo '
				<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" />', $group['group_name'], '<br>';

	echo '
				<input type="checkbox" id="checkAllGroups"  onclick="invertAll(this, this.form, \'groups\');" class="check">', $txt['ezp_txt_checkall'],'
			</dd>
			<dt>
				', $txt['ezp_txt_showinmenu'],'
			</dt>
			<dd>
				<input type="checkbox" name="showinmenu">
			</dd>
			<dt>
				', $txt['ezp_txt_menutext'],'
			</dt>
			<dd>
				<input type="text" name="menutitle" size="30" />
			</dd>
			
			<dt>
				', $txt['ezp_txt_icon'],'
			</dt>
			<dd>
				<input type="text" name="icon" size="30" value="page.png" />
			</dd>
			
		</dl>
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="bbc" value="',$context['ezp_page_bbc'], '">
		<input class="button" type="submit" name="addpage" value="',$txt['ezp_addpage'],'">
	</div>
	</form>';


if ($context['ezp_page_bbc'] == 0)
	echo '
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
</script>';
}

function template_ezportal_editpage()
{
	global $context, $txt, $scripturl;

	echo '
	<form method="post" name="frmeditpage" id="frmeditpage" action="', $scripturl, '?action=ezportal;sa=editpage2" accept-charset="', $context['character_set'], '"   onsubmit="submitonce(this);">
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['ezp_editpage'], '
		</h3>
	</div>
	<div class="windowbg">
		<dl id="ezPortal_admin"  class="ezpsettings">
			<dt>
				', $txt['ezp_txt_page_title'],'
			</dt>
			<dd>
				<input type="text" name="pagetitle" size="100" value="',$context['ezp_editpage_data']['title'], '">
			</dd>
			<dt>
				', $txt['ezp_txt_metatags'],'
			</dt>
			<dd>	
				<textarea name="metatags" rows="5" cols="55" class="">', $context['ezp_editpage_data']['metatags'],'</textarea>
				<br />', $txt['ezp_txt_metatags2'],'
			</dd>

			
			<dt>
				', $txt['ezp_txt_page_content'],'
			</dt>
';

		if ($context['ezp_page_bbc'] == 0)
		{
	echo '
			<dd>
				<textarea name="pagecontent" id="myTextEditor" rows="15" cols="55">', htmlentities($context['ezp_editpage_data']['content']),'</textarea>
				<p><a href="#" id="switchtinymce" onclick="switchTinyMCE(this.id, \'myTextEditor\'); return false;">' . $txt['ezp_hide_tinymce'] . '</a></p>
			</dd>';
		}
		else
		{


				   	echo '<dd><table>';

		   		if (!function_exists('getLanguages'))
					{
						// Showing BBC?
						if ($context['show_bbc'])
						{
							echo '
									<tr class="windowbg2">
										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'bbc'), '
										</td>
									</tr>';
						}

						// What about smileys?
						if (!empty($context['smileys']['postform']))
							echo '
									<tr class="windowbg2">
										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'smileys'), '
										</td>
									</tr>';

						// Show BBC buttons, smileys and textbox.
						echo '
									<tr class="windowbg2">
										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'message'), '
										</td>
									</tr>';
					}
					else
					{
						echo '
									<tr class="windowbg2">
										<td colspan="2">';
							// Showing BBC?
							if ($context['show_bbc'])
							{
								echo '
											<div id="bbcBox_message"></div>';
							}

							// What about smileys?
							if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
								echo '
											<div id="smileyBox_message"></div>';

							// Show BBC buttons, smileys and textbox.
											template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');


							echo '
										</td>
									</tr>';
						}
							echo '
								</table></dd>';


			}


		echo '
			<dt>
				', $txt['ezp_txt_permissions'],'
			</dt>';

		$permissionsGroups = explode(',',$context['ezp_editpage_data']['permissions']);

	echo '
			<dd>
				<input type="checkbox" name="groups[-1]" value="-1" ', ((in_array(-1,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_guests'],'<br>
				<input type="checkbox" name="groups[0]" value="0" ', ((in_array(0,$permissionsGroups) === true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_members'],'<br>';

		foreach ($context['groups'] as $group)
			echo '
				<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />', $group['group_name'], '<br>';

	echo '
				<input type="checkbox" id="checkAllGroups"  onclick="invertAll(this, this.form, \'groups\');" class="check">', $txt['ezp_txt_checkall'],'
			<dt>
				', $txt['ezp_txt_showinmenu'],'
			</dt>
			<dd>
				<input type="checkbox" name="showinmenu" ' . ($context['ezp_editpage_data']['showinmenu']  == 1 ? ' checked="checked" ' : '') . '>
			</dd>
			<dt>
				', $txt['ezp_txt_menutext'],'
			</dt>
			<dd>
				<input type="text" name="menutitle"  size="30" value="',$context['ezp_editpage_data']['menutitle'], '">
			</dd>
			
			<dt>
				', $txt['ezp_txt_icon'],'
			</dt>
			<dd>
				<input type="text" name="icon"  size="30" value="',$context['ezp_editpage_data']['icon'], '">
			</dd>
			
			
		</dl>
		<input type="hidden" name="sc" value="', $context['session_id'], '">
	    <input type="hidden" name="p" value="', $context['ezp_editpage_data']['id_page'],'">
		<input class="button" type="submit" name="editpage" value="', $txt['ezp_editpage'], '">
	</div>
	</form>';

	if ($context['ezp_page_bbc'] == 0)
		echo '
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
</script>';
}

function template_ezportal_deletepage()
{
	global $context, $txt, $scripturl;

	echo '
	<form method="post" name="frmdelpage" id="frmdelpage" action="', $scripturl, '?action=ezportal;sa=deletepage2" accept-charset="', $context['character_set'], '" class="centertext">
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ezp_deletepage'], '
		</h3>
	</div>
	<div class="windowbg">
		<p>',$txt['ezp_deletepage_confirm'], '</p>
	    <p class="strong">',$context['ezp_deletepage_data']['title'],'</p>
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="p" value="', $context['ezp_deletepage_data']['id_page'],'">
		<input class="button" type="submit" name="delpage" value="', $txt['ezp_deletepage'], '">
	</div>
	</form>';
}

function template_ezportal_settings()
{
	global $context, $txt, $ezPortalVersion, $scripturl, $ezpSettings;

	echo '
	<form method="post" action="', $scripturl, '?action=ezportal;sa=settings2" accept-charset="', $context['character_set'], '">
	<div class="cat_bar">
		<h3 class="catbg">
        	', $txt['ezp_settings'], '
		</h3>
	</div>
	<div class="windowbg">
		<dl id="ezPortal_admin" class="settings">
			<dt>
				', $txt['ezp_txt_yourversion'], $ezPortalVersion, '&nbsp;',$txt['ezp_txt_latestversion'],'<span id="lastezportal"></span>
			</dt>
			<dd></dd>
			<dt>
				',$txt['ezp_url'], '
			</dt>
			<dd>
				<input type="text" name="ezp_url" value="',  $ezpSettings['ezp_url'], '" size="50">
			</dd>
			<dt>
				', $txt['ezp_path'], '
			</dt>
			<dd>
				<input type="text" name="ezp_path" value="',  $ezpSettings['ezp_path'], '" size="50">
			</dd>
			<dt>
				', $txt['ezp_portal_enable'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_portal_enable" ',($ezpSettings['ezp_portal_enable'] ? ' checked="checked" ' : ''), '>
			</dd>
			<dt>
				', $txt['ezp_portal_homepage_title'], '
			</dt>
			<dd>
				<input type="text" name="ezp_portal_homepage_title" value="',  $ezpSettings['ezp_portal_homepage_title'], '" size="50">
			</dd>
			<dt>
				', $txt['ezp_hide_edit_delete'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_hide_edit_delete" ',($ezpSettings['ezp_hide_edit_delete'] ? ' checked="checked" ' : ''), '>
			</dd>
			<dt>
				', $txt['ezp_disable_tinymce_html'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_disable_tinymce_html" ',($ezpSettings['ezp_disable_tinymce_html'] ? ' checked="checked" ' : ''), '>
			</dd>
			<dt>
				', $txt['ezp_disableblocksinadmin'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_disableblocksinadmin" ',($ezpSettings['ezp_disableblocksinadmin'] ? ' checked="checked" ' : ''), '>
			</dd>
			<dt>
				', $txt['ezp_disablemobiledevices'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_disablemobiledevices" ',($ezpSettings['ezp_disablemobiledevices'] ? ' checked="checked" ' : ''), '>
			</dd>
		</dl>
		<hr>
		<p class="strong">', $txt['ezp_shoutbox_settings'], '</p>
		<dl id="ezPortal_admin" class="settings">
			<dt>
				', $txt['ezp_shoutbox_enable'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_shoutbox_enable" ',($ezpSettings['ezp_shoutbox_enable'] ? ' checked="checked" ' : ''), '>
			</dd>
			<dt>
				', $txt['ezp_shoutbox_showdate'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_shoutbox_showdate" ',($ezpSettings['ezp_shoutbox_showdate'] ? ' checked="checked" ' : ''), '>
			</dd>
			<dt>
				', $txt['ezp_shoutbox_archivehistory'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_shoutbox_archivehistory" ',($ezpSettings['ezp_shoutbox_archivehistory'] ? ' checked="checked" ' : ''), '>
			</dd>
			<dt>
				', $txt['ezp_shoutbox_hidesays'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_shoutbox_hidesays" ',($ezpSettings['ezp_shoutbox_hidesays'] ? ' checked="checked" ' : ''), '>
			</dd>
			<dt>
				', $txt['ezp_shoutbox_hidedelete'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_shoutbox_hidedelete" ',($ezpSettings['ezp_shoutbox_hidedelete'] ? ' checked="checked" ' : ''), '>
			</dd>
			<dt>
				', $txt['ezp_shoutbox_history_number'], '
			</dt>
			<dd>
				<input type="text" name="ezp_shoutbox_history_number" value="',  $ezpSettings['ezp_shoutbox_history_number'], '" size="50">
			</dd>
			<dt>
				', $txt['ezp_shoutbox_showbbc'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_shoutbox_showbbc" ',($ezpSettings['ezp_shoutbox_showbbc'] ? ' checked="checked" ' : ''), '>
			</dd>
			<dt>
				', $txt['ezp_shoutbox_showsmilies'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_shoutbox_showsmilies" ',($ezpSettings['ezp_shoutbox_showsmilies'] ? ' checked="checked" ' : ''), '>
			</dd>
			<dt>
				', $txt['ezp_shoutbox_refreshseconds'], '
			</dt>
			<dd>
				<input type="text" name="ezp_shoutbox_refreshseconds" value="',  $ezpSettings['ezp_shoutbox_refreshseconds'], '" size="50">
			</dd>
		</dl>
		<hr>
		<dl id="ezPortal_admin" class="settings">
			<dt>
				', $txt['ezp_allowstats'], '
			</dt>
			<dd>
				<input type="checkbox" name="ezp_allowstats" ',($ezpSettings['ezp_allowstats'] ? ' checked="checked" ' : ''), '>
			</dd>
		</dl>
		<p>', $txt['ezp_forumgetlisted'], '</p>
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="submit" name="savesettings" value="', $txt['ezp_savesettings'], '" class="button">

		<p>',$txt['ezp_donate'],'<br>
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
			<img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		</p>
	</div>
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

EzPortalCurrentVersion();

 // ]]></script>';
}

function template_ezportal_messageform()
{
	global $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $context['ezportal_message_title'], '
		</h3>
	</div>
	<div class="windowbg centertext">
		', $context['ezportal_message_description'], '
	</div>';
}
// @to-do -Which page is this?
function template_ezportal_modules()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ezp_modules'], '
		</h3>
	</div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg2">
			<td>
				';

				echo '<table  width="90%" cellspacing="0" align="center" cellpadding="4" class="tborder">';
				$styleclass = 'windowbg';

				foreach($context['module-list'] as $module)
				{
					echo '
					<tr class="',$styleclass,'">
						<td>',$module['title'],'<br />
						',$module['text'],'<br />
						<a href="', $scripturl, '?action=admin;area=packages;pgdownload;auto;package=',$module['filename1'],';sesc=', $context['session_id'], '">',$txt['ezp_module_download'] ,'</a>

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
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ezp_blocks'], '
		</h3>
	</div>';

	foreach($context['ezPortalAdminColumns']  as $row)
	{
	echo '
		<form method="post" action="', $scripturl, '?action=ezportal;sa=blocks2" accept-charset="', $context['character_set'], '">
			<table class="table_grid">
				<tr class="title_bar">
					<td colspan="4">
						',$row['column_title'],' - <a href="', $scripturl, '?action=admin;area=ezpblocks;sa=editcolumn;column=',$row['id_column'],'"><img src="',$ezpSettings['ezp_url'],'icons/page_white_edit.png" alt="',$txt['ezp_edit2'], '" /> ',$txt['ezp_editcolumn'],'</a>
						', ' - ', ($row['active'] ? '<font color="#00FF00">' . $txt['ezp_txt_active'] . '</font>' :  '<font color="#FF0000">' . $txt['ezp_txt_disabled'] . '</font>'), '
					</td>
				</tr>
				<tr class="windowbg">
					<td>',$txt['ezp_txt_order'] ,'</td>
					<td>',$txt['ezp_txt_title'] ,'</td>
					<td>',$txt['ezp_txt_active'],'</td>
					<td>',$txt['ezp_txt_options'],'</td>
				</tr>';

		// Now show all the ezBlocks under the column
		foreach($row['blocks'] as $blockRow)
		{
			echo '
				<tr class="windowbg">
					<td><input type="text" name="order[',$blockRow['id_layout'],']" value="',($blockRow['id_order']*10),'" size="4" /></td>
					<td><input type="text" name="title[',$blockRow['id_layout'],']" value="',$blockRow['customtitle'],'" size="50" /></td>
					<td>
						<select name="active[',$blockRow['id_layout'],']">
						<option value="1" ',($blockRow['active'] == 1 ? ' selected="selected" ' : ''),'>',$txt['ezp_yes'],'</option>
						<option value="0" ',($blockRow['active'] == 0 ? ' selected="selected" ' : ''),'>',$txt['ezp_no'],'</option>
						</select>
					</td>
					<td>
						<a href="',$scripturl,'?action=admin;area=ezpblocks;sa=editblock;block=',$blockRow['id_layout'],'">',$txt['ezp_edit'], '</a>
						&nbsp;
						<a href="',$scripturl,'?action=admin;area=ezpblocks;sa=deleteblock;block=',$blockRow['id_layout'],'">',$txt['ezp_delete'], '</a>
					</td>
				</tr>';
		}

			echo '
				<tr class="windowbg">
					<td colspan="4">
						<a href="', $scripturl, '?action=admin;area=ezpblocks;sa=addblock;column=',$row['id_column'],'" class="button">',$txt['ezp_addblock'],'</a>
						<input type="hidden" name="sc" value="', $context['session_id'], '">
						<input type="hidden" name="column" value="',$row['id_column'],'">
						<input type="submit" value="',$txt['ezp_saveblocks'],'" class="button">
					</td>
				</tr>
			</table>
		</form>';
	}
}

function template_ezportal_import()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ezp_import'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="smalltext">',$txt['ezp_import_information'],'</span><br />';

		// Setup the import buttons
		if ($context['portals']['MX'] == true)
			echo '
				<form method="post" action="', $scripturl,'?action=admin;area=ezpsettings;sa=import2;type=mx">
					<input type="submit" value="',$txt['ezp_import_mx'],'" class="button">
				</form>';

		if ($context['portals']['SP'] == true)
			echo '
				<form method="post" action="', $scripturl,'?action=admin;area=ezpsettings;sa=import2;type=sp">
					<input type="submit" value="',$txt['ezp_import_sp'],'" class="button">
				</form>';

		if ($context['portals']['TP'] == true)
			echo '
				<form method="post" action="', $scripturl,'?action=admin;area=ezpsettings;sa=import2;type=tp">
					<input type="submit" value="',$txt['ezp_import_tp'],'" class="button">
				</form>';

	echo '
	</div>';
}

function template_ezportal_pagemanager()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			' . $txt['ezp_pagemanager']  . '
		</h3>
	</div>
	<table class="table_grid">
		<thead>
			<tr class="title_bar">
				<th class="lefttext">',$txt['ezp_txt_title'] ,'</th>
				<th class="lefttext">',$txt['ezp_txt_views'],'</th>
				<th class="lefttext">',$txt['ezp_txt_date'],'</th>
				<th class="lefttext">',$txt['ezp_txt_options'],'</th>
			</tr>
		</thead>';

		foreach($context['ezp_pages']  as $row)
		echo '
			<tr class="windowbg">
				<td>
					<a href="',$scripturl,'?action=ezportal;sa=page;p=',$row['id_page'],'">',$row['title'], '</a>
					<br>
					<a href="',$scripturl,'?action=ezportal;sa=page;p=',$row['id_page'],'">',$scripturl,'?action=ezportal;sa=page;p=',$row['id_page'],'</a>
				</td>
				<td>
					',$row['views'], '
				</td>
				<td>
					',timeformat($row['date']), '
				</td>
				<td>
					<a href="',$scripturl,'?action=admin;area=ezppagemanager;sa=editpage;p=',$row['id_page'],'">',$txt['ezp_edit'], '</a>
					<br>
					<a href="',$scripturl,'?action=admin;area=ezppagemanager;sa=deletepage;p=',$row['id_page'],'">',$txt['ezp_delete'], '</a>
				</td>
			</tr>';

		echo '
	</table>
	<div class="pagesection">
		', $context['page_index'], '
	</div>
	<form method="post" action="', $scripturl, '?action=admin;area=ezppagemanager;sa=addpage">
		<input type="submit" name="btnsubmit" value="', $txt['ezp_addpage'], '" class="button">
		<input type="submit" name="btnsubmit" value="', $txt['ezp_bbc_addpage'], '" class="button">
	</form>';
}

function template_ezportal_credits()
{
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ezp_credits'], '
		</h3>
	</div>
	<div class="windowbg">
		', $txt['ezp_developedby'], '
		<hr>
		', $txt['ezp_helpout'], '
		', $txt['ezp_donate'], '<br>
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
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
	</div>';
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

	if (isset($context['ezp_loaded']))
	if ($context['ezp_loaded'] != true && $showInfo == true)
		echo '<div align="center"><span class="smalltext">Powered by <a href="https://www.ezportal.com" target="blank">EzPortal</a></span></div>';
}

function template_ezportal_viewpage()
{
	global $context, $txt, $ezpSettings , $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $context['ezpage_info']['title'], '';

		if (allowedTo('ezportal_page') || allowedTo('ezportal_manage'))
		{
			echo '
			<a href="', $scripturl, '?action=admin;area=ezppagemanager;sa=editpage;p=',$context['ezpage_info']['id_page'],'"><img src="',$ezpSettings['ezp_url'],'icons/page_white_edit.png" alt="',$txt['ezp_edit2'], '" /></a>
			<a href="',$scripturl,'?action=admin;area=ezppagemanager;sa=deletepage;p=',$context['ezpage_info']['id_page'],'"><img src="',$ezpSettings['ezp_url'],'icons/plugin_delete.png" alt="',$txt['ezp_delete2'], '" /></a>';
        }

	echo '
		</h3>
	</div>
	<div class="wikicontent windowbg">
		', $context['ezp_pagecontent'], '
	</div>';
}

function template_ezportal_edit_column()
{
	global $context, $txt, $scripturl;

	echo '
	<form method="post" name="frmeditcolumn" id="frmeditcolumn" action="', $scripturl, '?action=ezportal;sa=editcolumn2" accept-charset="', $context['character_set'], '">
	<div class="cat_bar">
		<h3 class="catbg centertext">
		', $txt['ezp_editcolumn'], '
		</h3>
	</div>
	<div class="windowbg">
		<dl id="ezPortal_admin" class="settings">
			<dt>
				', $txt['ezp_txt_columntitle'], '
			</dt>
			<dd>
				', $context['ezp_column_data']['column_title'], '
			</dd>
			<dt>
				', $txt['ezp_txt_columnwidth'], '
			</dt>
			<dd>
				<input type="text" name="columnwidth" size="10" value="',$context['ezp_column_data']['column_width'],'">
			</dd>
			<dt>
				', $txt['ezp_txt_columnwidth_percent'],'
			</dt>
			<dd>
				<input type="text" name="columnpercent" size="10" value="',$context['ezp_column_data']['column_percent'],'">
				<p><span class="smalltext">', $txt['ezp_txt_columnwidth_percent_note'], '</span></p>
			</dd>
			<dt>
				', $txt['ezp_txt_active'], '
			</dt>
			<dd>
				<select name="active">
					<option value="1" ',($context['ezp_column_data']['active'] == 1 ? ' selected="selected" ' : ''),'>',$txt['ezp_yes'],'</option>
					<option value="0" ',($context['ezp_column_data']['active'] == 0 ? ' selected="selected" ' : ''),'>',$txt['ezp_no'],'</option>
				</select>
			</dd>';

		if ($context['ezp_column_data']['column_title'] == 'Left' || $context['ezp_column_data']['column_title'] == 'Right')
		{
			echo '
			<dt>
				', $txt['ezp_txt_can_collapse_column'],'
			</dt>
			<dd>
				<input type="checkbox" name="can_collapse" ' . ($context['ezp_column_data']['can_collapse'] == 1  ? ' checked="checked" ' : '') . '>
			</dd>';
		}

	echo '
			<dt>
				', $txt['ezp_txt_sticky'],'
			</dt>
			<dd>
				<input type="checkbox" name="sticky" ' . ($context['ezp_column_data']['sticky'] == 1  ? ' checked="checked" ' : '') . '>
				<br>
				' . $txt['ezp_txt_sticky_note'] . ' 
			</dd>

			<dt>
				', $txt['ezp_txt_visible_areas'], '
			</dt>
			<dd>
				<a href="', $scripturl, '?action=admin;area=ezpblocks;sa=visiblesettings;column=',$context['ezp_column_data']['id_column'],'">',$txt['ezp_txt_update_visible_options'],'</a>
			</dd>
		</dl>
	    <input type="hidden" name="sc" value="', $context['session_id'], '">
	    <input type="hidden" name="column" value="',$context['ezp_column_data']['id_column'],'">
	    <input type="submit" name="editcolumn" value="',$txt['ezp_editcolumn'],'" class="button">
	</div>
	</form>';
}

function template_ezportal_add_block()
{
	global $context, $txt, $scripturl;

	echo '
	<form method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=admin;area=ezpblocks;sa=addblock2" accept-charset="', $context['character_set'], '">
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['ezp_addblock'], '
		</h3>
	</div>
	<div class="windowbg">
		<p class="centertext">
			', $txt['ezp_txt_block_type'],'
			<select name="blocktype">';

		foreach ($context['ezp_blocks'] as $row)
				echo '<option value="', $row['id_block'], '">', $row['blocktitle'], '</option>';

	echo '
			</select>
		</p>
	    <input type="hidden" name="column" value="',$context['ezportal_column'],'">
		<input type="submit" name="addblock" value="',$txt['ezp_addblock'],'" class="button">
	</div>
	</form>';
}

function template_ezportal_add_block2()
{
	global $context, $txt, $scripturl, $ezpSettings;

	echo '
	<form method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=addblock3" accept-charset="', $context['character_set'], '"  onsubmit="submitonce(this);">
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['ezp_addblock'], '
		</h3>
	</div>
	<div class="windowbg">
		<dl id="ezPortal_admin" class="ezpsettings">
			<dt>
				', $txt['ezp_txt_block_title'], '
			</dt>
			<dd>
				<input type="text" name="blocktitle" size="60" value="',$context['ezp_block_data']['blocktitle'],'">
			</dd>
			<dt>
				', $txt['ezp_txt_column'], '
			</dt>
			<dd>
				<select name="column">';

		foreach ($context['ezp_columns'] as $column)
				echo '<option value="', $column['id_column'], '" ',($column['id_column'] == $context['ezportal_column'] ? ' selected="selected" ' : ''), '>', $column['column_title'], '</option>';

	echo '
				</select>
			</dd>
			<dt>
				', $txt['ezp_txt_icon'], '
			</dt>
			<dd>
				<select id="iconchoice" name="icon" onchange="ChangeIconPic(this.value)">
					<option value="0">',$txt['ezp_txt_noicon'],'</option>';

		foreach ($context['ezp_icons'] as $icon)
				echo '
					<option value="', $icon['id_icon'], '">', $icon['icon'], '</option>';


	echo '
				</select>
				<img id="iconPick" src="" alter="">
			</dd>';

	// Check if we need to setup an editable box for this ezBlock
	if ($context['ezp_block_data']['data_editable'] == 1)
	{
		echo '
			<dt>
				', $txt['ezp_txt_block_data'], '
			</dt>
			<dd>
				<textarea name="blockdata" id="myTextEditor" rows="10" cols="60">',$context['ezp_block_data']['blockdata'],'</textarea>';

		if ($context['ezp_showtinymcetoggle'] == true)
			echo '
				<a href="#" id="switchtinymce" onclick="switchTinyMCE(this.id, \'myTextEditor\'); return false;">' . $txt['ezp_hide_tinymce'] . '</a>';

		echo '
			</dd>';
	}


	if ($context['ezp_block_data']['blocktitle'] == 'Menu ezBlock')
	{
		echo '
			<dt>
				', $txt['ezp_txt_menu_add_block_note'], '
			</dt>
			<dd></dd>';
	}

	// Now show all parameters for this ezBlock
	foreach ($context['ezp_block_parameters'] as $parameter)
	{
		if ($parameter['parameter_type'] == 'hidden')
			continue;

		echo '
			<dt>
				', $parameter['title'],($parameter['required'] == 1 ? '<span style="color: red;">*</span>' : ''), '
			</dt>
			<dd>';

				if ($parameter['parameter_type'] == 'select')
				{
						echo '<select name="parameter[', $parameter['id_parameter'],']">';
						foreach ($context['ezp_select_' . $parameter['id_parameter']] as $key => $option)
							echo '<option value="' . $key . '">' . $option . '</option>';
						echo '</select>';
				}
				elseif  ($parameter['parameter_type'] == 'checkbox')
				{
					echo '<input type="checkbox" name="parameter[', $parameter['id_parameter'],']" ', ($parameter['defaultvalue'] == 1 ? 'checked="checked" ' : ''),' />';
				}
				elseif ($parameter['parameter_type'] == 'boardselect')
				{
				 	echo '<select name="parameter[', $parameter['id_parameter'],']">';

					foreach ($context['ezportal_boards'] as $key => $option)
							echo '<option value="' . $key . '">' . $option . '</option>';

				echo '</select>';
				}
				elseif ($parameter['parameter_type'] == 'multiboardselect')
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
				elseif ($parameter['parameter_type'] == 'bbc')
				{
			   	echo '<table>';

		   		if (!function_exists('getLanguages'))
					{
						// Showing BBC?
						if ($context['show_bbc'])
						{
							echo '
									<tr class="windowbg2">
										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'bbc'), '
										</td>
									</tr>';
						}

						// What about smileys?
						if (!empty($context['smileys']['postform']))
							echo '
									<tr class="windowbg2">
										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'smileys'), '
										</td>
									</tr>';

						// Show BBC buttons, smileys and textbox.
						echo '
									<tr class="windowbg2">
										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'message'), '
										</td>
									</tr>';
					}
					else
					{
						echo '
									<tr class="windowbg2">
										<td colspan="2">';
							// Showing BBC?
							if ($context['show_bbc'])
							{
								echo '
											<div id="bbcBox_message"></div>';
							}

							// What about smileys?
							if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
								echo '
											<div id="smileyBox_message"></div>';

							// Show BBC buttons, smileys and textbox.
											template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');


							echo '
										</td>
									</tr>';
						}
							echo '
								</table>';
					}

				  else
				  {
						echo '<input type="text" name="parameter[', $parameter['id_parameter'],']" size="60" value="', $parameter['defaultvalue'],'" />';
				  }

		echo '
			</dd>';
	}

	echo '
			<dt>
				', $txt['ezp_txt_permissions'],'
			</dt>
			<dd>
				<input type="checkbox" name="groups[-1]" value="-1" checked="checked">',$txt['membergroups_guests'],'<br>
				<input type="checkbox" name="groups[0]" value="0" checked="checked">',$txt['membergroups_members'],'<br>';

			foreach ($context['groups'] as $group)
					echo '<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" checked="checked" />', $group['group_name'], '<br />';

		echo '
				<input type="checkbox" id="checkAllGroups"  onclick="invertAll(this, this.form, \'groups\');" class="check" />', $txt['ezp_txt_checkall'], '
			</dd>
			<dt>
				', $txt['ezp_txt_block_managers'],'
			</dt>
			<dd>';

			foreach ($context['groups'] as $group)
			{
				if ($group['ID_GROUP'] == 1)
					continue;

				echo '<input type="checkbox" name="managers[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" />', $group['group_name'], '<br />';
			}

		echo '
			</dd>
			<dt>
				', $txt['ezp_txt_additional_settings'],'
			</dt>
			<dd>
				<input type="checkbox" name="can_collapse">', $txt['ezp_txt_can_collapse'], '<br>
				<input type="checkbox" name="hidetitlebar">', $txt['ezp_hidetitlebar'], '<br>
				<input type="checkbox" name="hidemobile">', $txt['ezp_hidemobile'], '<br>
				<input type="checkbox" name="showonlymobile">', $txt['ezp_showonlymobile'], '
			</dd>
		</dl>
		
		<dl>
			<dt>', $txt['ezp_txt_css_header_class'],'&nbsp;</dt>
	    	<dd><input type="text" name="block_header_class" size="60" /></dd>

			<dt>', $txt['ezp_txt_css_body_class'],'&nbsp;</dt>
			<dd><input type="text" name="block_body_class" size="60" /></dd>
	  </dl>	
		
		
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="blocktype" value="',$context['ezportal_blocktype'],'">
		<input type="submit" name="addblock" value="',$txt['ezp_addblock'],'" class="button">
	</div>
	</form>';

// All Scripts here
echo '
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
// iconSelect
var selectIcons = new Array();
selectIcons[0] = "";';

foreach($context['ezp_icons'] as $c => $icon)
	echo 'selectIcons[',$icon['id_icon'],'] = "' . $ezpSettings['ezp_url'] . 'icons/' . $icon['icon'] .  '";' . "\n";

echo  '
function ChangeIconPic(iconIndex)
{
	document.frmaddblock.iconPick.src = selectIcons[iconIndex];
}

ChangeIconPic(document.frmaddblock.iconchoice.selectedIndex);

// ]]></script>
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

</script>';
}

function template_ezportal_edit_block()
{
	global $context, $txt, $scripturl, $ezpSettings;

	echo '
	<form method="post" name="frmeditblock" id="frmeditblock" action="', $scripturl, '?action=ezportal;sa=editblock2" accept-charset="', $context['character_set'], '"  onsubmit="submitonce(this);">
	<div class="cat_bar">
		<h3 class="catbg centertext">
			' . $txt['ezp_editblock']. '
		</h3>
	</div>
	<div class="windowbg">
		<dl class="ezpsettings">
			<dt>
				', $txt['ezp_txt_block_title'], '
			</dt>
			<dd>
				<input type="text" name="blocktitle" size="60" value="',$context['ezp_block_info']['customtitle'],'">
			</dd>
			<dt>
				', $txt['ezp_txt_menu_enabled'], '
			</dt>
			<dd>
				<select name="active">
						<option value="1" ',($context['ezp_block_info']['active'] == 1 ? ' selected="selected" ' : ''),'>',$txt['ezp_yes'],'</option>
						<option value="0" ',($context['ezp_block_info']['active'] == 0 ? ' selected="selected" ' : ''),'>',$txt['ezp_no'],'</option>
				</select>
			</dd>
			
			<dt>
				', $txt['ezp_txt_column'], '
			</dt>
			
			<dd>
				<select name="column">';

		foreach ($context['ezp_columns'] as $column)
				echo '<option value="', $column['id_column'], '" ',($column['id_column'] == $context['ezp_block_info']['id_column'] ? ' selected="selected" ' : ''), '>', $column['column_title'], '</option>';

	echo '
				</select>
			</dd>
			<dt>
				', $txt['ezp_txt_icon'], '
			</dt>
			<dd>
				<select id="iconchoice" name="icon" onchange="ChangeIconPic(this.value)">
					<option value="0"',(empty($context['ezp_block_info']['id_icon']) ? ' selected="selected" ': ''),  '>',$txt['ezp_txt_noicon'],'</option>';

		foreach ($context['ezp_icons'] as $icon)
				echo '<option value="', $icon['id_icon'], '"',(($context['ezp_block_info']['id_icon'] == $icon['id_icon']) ? ' selected="selected" ': ''),  '>', $icon['icon'], '</option>';

	echo '
				</select>
				<img id="iconPick" src="" alter="">
			</dd>';

	// Check if we need to setup an editable box for this ezBlock
	if ($context['ezp_block_info']['data_editable'] == 1)
	{
		echo '
			<dt>
				', $txt['ezp_txt_block_data'], '
			</dt>
			<dd>
				<textarea name="blockdata" id="myTextEditor" rows="10" cols="60">',$context['ezp_block_info']['blockdata'],'</textarea>';

		if ($context['ezp_showtinymcetoggle'] == true)
			echo '
				<a href="#" id="switchtinymce" onclick="switchTinyMCE(this.id, \'myTextEditor\'); return false;">' . $txt['ezp_hide_tinymce'] . '</a>';

		echo '
			</dd>';
	}

	echo '
		</dl>';

	if ($context['ezp_block_info']['blocktitle'] == 'Menu ezBlock')
	{
		// Display the menu items
		echo '
		<table class="table_grid">
			<tr class="title_bar">
				<td>', $txt['ezp_txt_menu_title'], '</td>
				<td>', $txt['ezp_txt_menu_enabled'], '</td>
				<td>', $txt['ezp_txt_menu_order'], '</td>
				<td>', $txt['ezp_txt_options'], '</td>
			</tr>';

		foreach($context['ezp_menu_block_items'] as $menuRow)
		{
		echo '
			<tr class="windowbg">
				<td>', $menuRow['title'], '</td>
				<td>' . ($menuRow['enabled'] == 1 ? $txt['ezp_yes'] : $txt['ezp_no']) . '</td>
				<td><a href="',$scripturl,'?action=ezportal;sa=menuup&id=',$menuRow['id_menu'],'&block=',$context['ezp_block_info']['id_layout'],'">' . $txt['ezp_txt_menu_up'] . '</a>&nbsp;<a href="',$scripturl,'?action=ezportal;sa=menudown&id=',$menuRow['id_menu'],'&block=',$context['ezp_block_info']['id_layout'],'">' . $txt['ezp_txt_menu_down'] . '</a></td>
				<td>
					<a href="',$scripturl,'?action=ezportal;sa=menuedit&id=',$menuRow['id_menu'],'">',$txt['ezp_edit'], '</a>
					&nbsp;
					<a href="',$scripturl,'?action=ezportal;sa=menudelete&id=',$menuRow['id_menu'],'">',$txt['ezp_delete'], '</a>
				</td>
			</tr>';
		}

		echo '
			<tr class="windowbg">
				<td colspan="4" align="center"><a href="', $scripturl, '?action=ezportal;sa=menuadd;block=',$context['ezp_block_info']['id_layout'],'">' . $txt['ezp_txt_menu_add'] . '</a></td>
			</tr>
		</table>';
	}

	echo '
		<dl class="ezpsettings">';

	// Now show all parameters for this ezBlock
	foreach ($context['ezp_block_parameters'] as $parameter)
	{
		if ($parameter['parameter_type'] == 'hidden')
			continue;

		echo '
			<dt>
				', $parameter['title'],($parameter['required'] == 1 ? '<span style="color: red;">*</span>' : ''),'
			</dt>
			<dd>';

			if ($parameter['parameter_type'] == 'select')
			{
				echo '<select name="parameter[', $parameter['id_parameter'],']">';
					foreach ($context['ezp_select_' . $parameter['id_parameter']] as $key => $option)
					echo '<option value="' . $key . '" ' . ($key == $parameter['data'] ? ' selected="selected" ' : '') .  '>' . $option . '</option>';
				echo '</select>';
			}
			elseif ($parameter['parameter_type'] == 'checkbox')
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
			elseif ($parameter['parameter_type'] == 'multiboardselect')
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
			elseif ($parameter['parameter_type'] == 'bbc')
			{
				echo '<table>';

				if (!function_exists('getLanguages'))
				{
					// Showing BBC?
					if ($context['show_bbc'])
					{
						echo '
								<tr class="windowbg2">
									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'bbc'), '
									</td>
								</tr>';
					}

					// What about smileys?
					if (!empty($context['smileys']['postform']))
						echo '
								<tr class="windowbg2">
									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'smileys'), '
									</td>
								</tr>';

					// Show BBC buttons, smileys and textbox.
					echo '
								<tr class="windowbg2">
										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'message'), '
										</td>
								</tr>';
				}
				else
				{
					echo '
								<tr class="windowbg2">
									<td colspan="2">';
							// Showing BBC?
							if ($context['show_bbc'])
							{
								echo '
										<div id="bbcBox_message"></div>';
							}

							// What about smileys?
							if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
								echo '
										<div id="smileyBox_message"></div>';

							// Show BBC buttons, smileys and textbox.
								echo template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');


							echo '
									</td>
								</tr>';
				}

				echo '
							</table>';
			}
			else
			{
				echo '<input type="text" name="parameter[', $parameter['id_parameter'],']" size="60" value="', $parameter['data'],'" />';
			}

					echo '
			</dd>';
	}

	echo '
			<dt>
				', $txt['ezp_txt_permissions'], '
			</dt>
			<dd>';

			$permissionsGroups = explode(',',$context['ezp_block_info']['permissions']);

			echo '
	   	 	<input type="checkbox" name="groups[-1]" value="-1" ', ((in_array(-1,$permissionsGroups) == true) ? ' checked="checked" ' : ''), '>',$txt['membergroups_guests'],'<br />
	   	 	<input type="checkbox" name="groups[0]" value="0" ', ((in_array(0,$permissionsGroups) == true) ? ' checked="checked" ' : ''), '>',$txt['membergroups_members'],'<br />';

			foreach ($context['groups'] as $group)
				echo '<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />', $group['group_name'], '<br />';

		echo '
				<input type="checkbox" id="checkAllGroups"  onclick="invertAll(this, this.form, \'groups\');" class="check">', $txt['ezp_txt_checkall'], '
			</dd>
			<dt>
				', $txt['ezp_txt_block_managers'], '
			</dt>
			<dd>';

			$blockManagers = explode(',',$context['ezp_block_info']['blockmanagers']);

			foreach ($context['groups'] as $group)
			{
				if ($group['ID_GROUP'] == 1)
					continue;

				echo '<input type="checkbox" name="managers[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$blockManagers) == true) ? ' checked="checked" ' : ''), ' />', $group['group_name'], '<br />';

			}

		echo '
			</dd>
			<dt>
				', $txt['ezp_txt_additional_settings'], '
			</dt>
			<dd>
				<input type="checkbox" name="can_collapse" ',($context['ezp_block_info']['can_collapse'] ? ' checked="checked" ' : ''), '>', $txt['ezp_txt_can_collapse'], '<br>
				<input type="checkbox" name="hidetitlebar" ',($context['ezp_block_info']['hidetitlebar'] ? ' checked="checked" ' : ''), '>', $txt['ezp_hidetitlebar'], '<br>
				<input type="checkbox" name="hidemobile" ',($context['ezp_block_info']['hidemobile'] ? ' checked="checked" ' : ''), '>', $txt['ezp_hidemobile'], '<br>
				<input type="checkbox" name="showonlymobile" ',($context['ezp_block_info']['showonlymobile'] ? ' checked="checked" ' : ''), '>',$txt['ezp_showonlymobile'], '
			</dd>
			
	
			<dt>', $txt['ezp_txt_css_header_class'],'&nbsp;</dt>
	    	<dd><input type="text" name="block_header_class" size="60" value="',$context['ezp_block_info']['block_header_class'],'" /></dd>

			<dt>', $txt['ezp_txt_css_body_class'],'&nbsp;</dt>
			<dd><input type="text" name="block_body_class" size="60" value="',$context['ezp_block_info']['block_body_class'],'" /></dd>

			
			
			<dt>
				', $txt['ezp_txt_visible_areas'], '
			</dt>
			<dd>
				<a href="', $scripturl, '?action=admin;area=ezpblocks;sa=visiblesettings;block=',$context['ezp_block_info']['id_layout'],'">',$txt['ezp_txt_update_visible_options'],'</a>
			</dd>
		</dl>
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="block" value="',$context['ezp_block_info']['id_layout'],'">
		<input type="submit" name="editblock" value="',$txt['ezp_editblock'],'" class="button">
	</div>
	</form>';

// Scripts here
echo '
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	// iconSelect
	var selectIcons = new Array();
	selectIcons[0] = "";';

	foreach($context['ezp_icons'] as $c => $icon)
		echo 'selectIcons[',$icon['id_icon'],'] = "' . $ezpSettings['ezp_url'] . 'icons/' . $icon['icon'] .  '";' . "\n";

	echo  '
	function ChangeIconPic(iconIndex)
	{
		document.frmeditblock.iconPick.src = selectIcons[iconIndex];
	}

	ChangeIconPic(document.frmeditblock.iconchoice.selectedIndex);

// ]]></script>
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

</script>';
}

function template_ezportal_delete_block()
{
	global $context, $txt, $scripturl;

	echo '
	<form method="post" name="frmdelblock" id="frmdelblock" action="', $scripturl, '?action=ezportal;sa=deleteblock2" accept-charset="', $context['character_set'], '">
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['ezp_deleteblock'], '
		</h3>
	</div>
	<div class="windowbg">
		<p>
			', $txt['ezp_deleteblock_confirm'], '<br>
			<b>', $context['ezp_block_layout_title'], '</b>
		</p>
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="hidden" name="blockid" value="',$context['ezp_block_layout_id'],'">
		<input type="submit" name="delblock" value="',$txt['ezp_deleteblock'],'" class="button">
	</div>
  </form>';
}

function template_ezportal_download_block()
{
	global $context, $txt, $scripturl;

	echo '
	<form action="',$scripturl,'?action=ezportal;sa=importblock" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data" style="margin-bottom: 0;">
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ezp_txt_import_block'], '
		</h3>
	</div>
	<div class="windowbg">
		<dl id="ezPortal_admin" class="settings">
			<dt>
				<b>',$txt['ezp_txt_import_block_file'],'</b>
			</dt>
			<dd>
				<input type="file" name="blockfile" size="45">
			</dd>
		</dl>
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="submit" value="',$txt['ezp_txt_upload_block'],'" class="button">
		<p>', $txt['ezp_txt_createezBlock'], '</p>
		<p>', $txt['ezp_txt_createezBlock_note'], '</p>
	</div>
	</form>';
}

function template_ezportal_installed_blocks()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ezp_installed_blocks'], '
		</h3>
	</div>
	<table class="table_grid">
		<tr class="title_bar">
			<td>', $txt['ezp_txt_block_title'], '</td>
			<td>', $txt['ezp_txt_block_version'], '</td>
			<td>', $txt['ezp_txt_block_author'], '</td>
			<td>', $txt['ezp_txt_options'], '</td>
		</tr>';

	foreach($context['ezportal_installed_blocks']  as $row)
	{
	echo '
		<tr class="windowbg">
			<td>', $row['blocktitle'], '</td>
			<td>', $row['blockversion'], '</td>
			<td>', ($row['blockwebsite'] == '' ? $row['blockauthor'] : '<a href="' . $row['blockwebsite'] . '" target="_blank">' . $row['blockauthor'] . '</a>'), '</td>
			<td>';

			if ($row['no_delete'] == 0)
			echo '
				<a href="',$scripturl,'?action=admin;area=ezpblocks;sa=uninstallblock;block=' . $row['id_block'],';sesc=',$context['session_id'],'">',$txt['ezp_txt_uninstall_block'],'</a>';

	echo '
			</td>
		</tr>';
	}

	echo '
	</table>';
}

function template_ezblock_above()
{
	global $context, $scripturl, $settings, $ezpSettings;

	if (empty($context['ezPortalColumns']))
		return false;

	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Top')
		{
			if (count($ezColumn['blocks']) == 0)
				continue;

			if ($ezColumn['can_collapse'] == 1)
				echo '<img src="' . $ezpSettings['ezp_url'],'icons/' .( $ezColumn['IsCollapsed'] ? 'expand.png' : 'collapse.png' ). '" onclick="javacscript:EzToogle(\'column',$ezColumn['id_column'],'\',',$ezColumn['id_column'],',this,0)" alt="+-">';
		}
		// Column can collapse
		if ($ezColumn['column_title'] == 'Left' || $ezColumn['column_title'] == 'Right')
		{
			if (count($ezColumn['blocks']) == 0)
				continue;

			if ($ezColumn['can_collapse'] == 1)
				echo '<img src="' . $ezpSettings['ezp_url'],'icons/' .( $ezColumn['IsCollapsed'] ? 'expand.png' : 'collapse.png' ). '" onclick="javacscript:EzToogle(\'column',$ezColumn['id_column'],'\',',$ezColumn['id_column'],',this,0)" alt="+-">';
		}
	}

		// Wrap it in
		echo '
			<div id="ezPortal">';
			
	

	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Top')
		{
			if (count($ezColumn['blocks']) == 0)
				continue;

			echo '<!--start column ' . $ezColumn['column_title'] . '-->';

				if (empty($ezColumn['column_percent']))
					echo '<div style="flex:1 ',$ezColumn['column_width'], 'px" id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
				else
					echo '<div style="flex:1 ',$ezColumn['column_percent'], '%" id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';


				if (!empty($ezColumn['sticky']))
					echo '<span style="position: sticky; top: 0;">';

							// Show Collapse icon if allowed for column if allowed
							ShowEzBlocks($ezColumn);


				if (!empty($ezColumn['sticky']))
					echo '</span>';

			echo '
						</div>';

			echo '<!--end column ' . $ezColumn['column_title'] . '-->';
		}
	}
	// End Top Blocks
	$shownHeader = false;
	$center = 0;
	$right = 0;
	$left = 0;
	$onlyCenter = 1;

	// Show left
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Right' || $ezColumn['column_title'] == 'Top' || $ezColumn['column_title'] == 'Bottom' || $ezColumn['column_title'] == 'Center')
			continue;

		if (count($ezColumn['blocks']) == 0)
			continue;

		echo '<!--start column ' . $ezColumn['column_title'] . '-->';

		if (empty($ezColumn['column_percent']))
			echo '<div style="flex:1 ',$ezColumn['column_width'], 'px" id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
		else
			echo '<div style="flex:1 ',$ezColumn['column_percent'], '%" id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';


				if (!empty($ezColumn['sticky']))
					echo '<span style="position: sticky; top: 0;">';

					// Show Collapse icon if allowed for column if allowed
					ShowEzBlocks($ezColumn);


				if (!empty($ezColumn['sticky']))
					echo '</span>';

		$tdopen = 1;
		echo '
				</div><!--end column.. ' . $ezColumn['column_title'] . '-->';
	}

	// Only load center
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Right' || $ezColumn['column_title'] == 'Top' || $ezColumn['column_title'] == 'Bottom' || $ezColumn['column_title'] == 'Left')
			continue;

		if (count($ezColumn['blocks']) == 0  && $ezColumn['column_title'] != 'Center') // Make sure the center column always loads
			continue;

		echo '<!--start column ' . $ezColumn['column_title'] . '-->';

		if (empty($ezColumn['column_percent']))
			echo '<div style="flex:1 ',$ezColumn['column_width'], 'px" id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
		else
			echo '<div style="flex:1 ',$ezColumn['column_percent'], '%" id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';

				if (!empty($ezColumn['sticky']))
					echo '<span style="position: sticky; top: 0;">';
					// Show Collapse icon if allowed for column if allowed
					ShowEzBlocks($ezColumn);


				if (!empty($ezColumn['sticky']))
					echo '</span>';
	}
}

function ShowEzBlocks($ezColumn)
{
	global $context, $txt, $settings, $scripturl, $ezpSettings;
	

	if ($ezColumn['column_title'] == 'Center')
	{
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'ezportal')
			return;
	}

	// Make sure the center column always loads
	if ($ezColumn['column_title'] == 'Center')
	{
		
		if (empty($ezColumn['blocks']))
		{
			echo '
			<div  id="block999">
			</div>
			';
		}
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

	foreach($ezColumn['blocks'] as $ezBlock)
	{

			$headerClass = 'catbg';
			$bodyClass = 'windowbg';

			if (!empty($ezBlock['block_header_class']))
				$headerClass = $ezBlock['block_header_class'];

			if (!empty($ezBlock['bodyClass']))
				$bodyClass = $ezBlock['block_body_class'];

		if (empty($ezBlock['hidetitlebar']))
		{
		echo '
			<div class="cat_bar">
				<h3 class="' . $headerClass . '">';

						// Check if they can collapse the ezBlock
						if ($ezBlock['can_collapse'])
							echo ' <span style="float:right"><img src="' . $ezpSettings['ezp_url'],'icons/' .( $ezBlock['IsCollapsed'] ? 'expand.png' : 'collapse.png' ). '" onclick="javacscript:EzToogle(\'block',$ezBlock['id_layout'],'\',',$ezBlock['id_layout'],',this,1)" alt="+-" /></span>';

						// Show icon
						if (!empty($ezBlock['icon']))
							echo '<img src="',$ezpSettings['ezp_url'],"icons/" . $ezBlock['icon'] . '" alt="" /> ';

						// Show title of ezBlock
						echo $ezBlock['customtitle'];

						if (($context['ezportal_block_manager'] == 1 || $ezBlock['IsManager'] == 1) && empty($ezpSettings['ezp_hide_edit_delete']))
						{
							/*
							echo '<br /><a href="',$scripturl,'?action=admin;area=ezpblocks;sa=editblock;block=',$ezBlock['id_layout'],'">',$txt['ezp_edit'], '</a>
							&nbsp;
							<a href="',$scripturl,'?action=admin;area=ezpblocks;sa=deleteblock;block=',$ezBlock['id_layout'],'">',$txt['ezp_delete'], '</a>';
							*/

							echo ' <a href="',$scripturl,'?action=ezportal;sa=editblock;block=',$ezBlock['id_layout'],'"><img src="',$ezpSettings['ezp_url'],'icons/page_white_edit.png" alt="',$txt['ezp_edit2'], '" /></a>
							&nbsp;
							<a href="',$scripturl,'?action=ezportal;sa=deleteblock;block=',$ezBlock['id_layout'],'"><img src="',$ezpSettings['ezp_url'],'icons/plugin_delete.png" alt="',$txt['ezp_delete2'], '"></a>';
						}

		echo '
				</h3>
			</div>';
		}

		echo '
			<div id="block',$ezBlock['id_layout'],'" ',($ezBlock['IsCollapsed'] ? 'style="display:none"' : ''),' class="' . $bodyClass .'">';

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
						call_user_func($ezBlock['blockdata'],array());
				}

			echo '
			</div>';
	}
}

function template_ezblock_below()
{
	global $context, $settings, $ezpSettings;

	if (empty($context['ezPortalColumns']))
		return false;

	$count = 0;
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Left' || $ezColumn['column_title'] == 'Center' )
		$count++;
	}

	// Close center block here (to contain forum and other things)
	echo '</div><!--end column Center -->';

	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] != 'Right')
			continue;

		if (count($ezColumn['blocks']) == 0)
			continue;

		echo '<!--start column ' . $ezColumn['column_title'] . '-->';

		if (empty($ezColumn['column_percent']))
			echo '<div style="flex:1 ',$ezColumn['column_width'], 'px" id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
		else
			echo '<div style="flex:1 ',$ezColumn['column_percent'], '%" id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';


				if (!empty($ezColumn['sticky']))
					echo '<span style="position: sticky; top: 0;">';
					// Show Collapse icon if allowed
					ShowEzBlocks($ezColumn);


				if (!empty($ezColumn['sticky']))
					echo '</span>';

		echo '
				</div>
			<!--end column ' . $ezColumn['column_title'] . '-->';

	}

	// Bottom Blocks
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Bottom')
		{
			if (count($ezColumn['blocks']) == 0)
				      		continue;

			echo '<!--start column ' . $ezColumn['column_title'] . '-->';

			if ($ezColumn['can_collapse'] == 1)
		      	echo ' <img src="' . $ezpSettings['ezp_url'],'icons/' .( $ezColumn['IsCollapsed'] ? 'expand.png' : 'collapse.png' ). '" onclick="javacscript:EzToogle(\'column',$ezColumn['id_column'],'\',',$ezColumn['id_column'],',this,0)" alt="+-" />';

				if (empty($ezColumn['column_percent']))
					echo '<div width="',$ezColumn['column_width'], 'px" valign="top" align="center"  id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
				else
					echo '<div width="',$ezColumn['column_percent'], '%" valign="top" align="center"  id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';



				if (!empty($ezColumn['sticky']))
					echo '<span style="position: sticky; top: 0;">';
							// Show Collapse icon if allowed for column if allowed
							ShowEzBlocks($ezColumn);


				if (!empty($ezColumn['sticky']))
					echo '</span>';

			echo '
						</div><!--end column ' . $ezColumn['column_title'] . '-->';
		}
	}

	// Show EzPortal Copyright
	// DO NOT MODIFY OR REMOVE THIS COPYRIGHT UNLESS THE BRANDING FREE OPTION HAS BEEN PURCHASED
	// http://www.ezportal.com/copyright_removal.php
    
	$showInfo = EzPortalCheckInfo();

	if ($showInfo == true)
		echo '<div align="center"><span class="smalltext">Powered by <a href="https://www.ezportal.com" target="blank">EzPortal</a></span></div>';

	// Closure of #ezPortal
	echo '
			</div>';
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
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['ezp_txt_visible_areas'], '
		</h3>
	</div>
	<div class="windowbg">
			<input type="checkbox" name="all" value="all" ',($context['ezp_all'] ? ' checked="checked" ' : ''),' />',$txt['ezp_txt_visible_all'],'<br />
	    <input type="checkbox" name="cus[forum]" value="forum" ',(in_array('forum',$context['ezp_visibleCustom']) ? ' checked="checked"' : '' ) ,'  />',$txt['ezp_txt_visible_forum'],'<br />
	    <input type="checkbox" name="cus[boardindex]" value="boardindex"  ',(in_array('boardindex',$context['ezp_visibleCustom']) ? ' checked="checked"' : '' ) ,' />',$txt['ezp_txt_visible_board_index']  ,'<br />
			<input type="checkbox" name="cus[portal]" value="portal"  ',(in_array('portal',$context['ezp_visibleCustom']) ? ' checked="checked"' : '' ) ,' />',$txt['ezp_txt_visible_homepage'] ,'<br />
			<div class="half_content">
	    	<p class="strong">', $txt['ezp_txt_visible_actions'], '</p>';

		foreach ($context['ezportal_actions'] as $ezpActions)
		{
			if ($ezpActions['is_mod'] == 0)
				echo '<input type="checkbox" name="visactions[', $ezpActions['action'], ']" value="', $ezpActions['action'], '" ' . ($ezpActions['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpActions['title'], '<br>';
		}

	echo '
			</div>
			<div class="half_content">
				<p class="strong">', $txt['ezp_txt_visible_actions_modifcations'], '</p>
				<p class="smalltext">',$txt['ezp_txt_visible_actions_modifcations_note'],'</p>';

		foreach ($context['ezportal_actions'] as $ezpActions)
		{
			if ($ezpActions['is_mod'] == 1)
				echo '<input type="checkbox" name="visactions[', $ezpActions['action'], ']" value="', $ezpActions['action'], '" ' . ($ezpActions['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpActions['title'], ' <a href="', $scripturl, '?action=admin;area=ezpblocks;sa=deletevisibleaction;newaction=', $ezpActions['action'], '">',$txt['ezp_delete'],'</a><br />';
		}

	echo '<a href="', $scripturl, '?action=admin;area=ezpblocks;sa=addvisibleaction">',$txt['ezp_txt_visible_add_new_action'],'</a><br>
			</div>
			<div class="half_content">
	  		<p class="strong">', $txt['ezp_txt_visible_pages'], '</p>';

		foreach ($context['ezportal_pages'] as $ezpPages)
		{
				echo '<input type="checkbox" name="vispages[', $ezpPages['id_page'], ']" value="', $ezpPages['id_page'], '" ' . ($ezpPages['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpPages['title'], '<br />';
		}

		echo '
			</div>
			<div class="half_content">
				<p class="strong">', $txt['ezp_txt_visible_boards'], '</p>';

		foreach ($context['ezportal_boards'] as $ezpBoards)
			echo '<input type="checkbox" name="visboards[',$ezpBoards['ID_BOARD'], ']" value="', $ezpBoards['ID_BOARD'], '" ' . ($ezpBoards['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpBoards['bName'], '<br />';

	echo '
			</div>
	    <input type="hidden" name="block" value="', $context['ezportal_block'], '">
	    <input type="hidden" name="column" value="', $context['ezportal_column'], '">
	    <input type="submit" name="addblock" value="', $txt['ezp_txt_savevisible'], '" class="button">
	  </div>
  	</form>';
}

function template_ezportal_add_visible_action()
{
	global $txt, $scripturl, $context;

	echo '
	<form method="post" name="addaction" id="addaction" action="', $scripturl, '?action=ezportal;sa=addvisibleaction2" accept-charset="', $context['character_set'], '">
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['ezp_txt_visible_add_new_action'], '
		</h3>
	</div>
	<div class="windowbg">
		<dl id="ezPortal_admin" class="settings">
			<dt>
				', $txt['ezp_txt_visible_add_new_action_title'],'
			</dt>
			<dd>
				<input type="text" name="actiontitle" size="50" value="">
			</dd>
			<dt>
				', $txt['ezp_txt_visible_add_new_action_action'], '
				<p class="smalltext">' . $txt['ezp_txt_visible_add_new_action_note'] . '</p>
			</dt>
			<dd>
				<input type="text" name="newaction" size="50" value="">
			</dd>
		</dl>
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="submit" name="addaction" value="',$txt['ezp_txt_visible_add_new_action'],'" class="button">
	</div>
	</form>';
}

function template_ezportal_delete_shoutboxhistory()
{
	global $txt, $scripturl, $context;

	echo '
	<form method="post" name="addaction" id="addaction" action="', $scripturl, '?action=ezportal;sa=deleteshouthistory2">
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['ezp_txt_deleteallshoutbox'], '
		</h3>
	</div>
	<div class="windowbg centertext">
		<p class="strong>', $txt['ezp_txt_deleteallshoutbox_confirm'],'</p>
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="submit" value="',$txt['ezp_txt_deleteallshoutbox'],'" class="button">
	</div>
	</form>';
}

function template_ezportal_shoutbox_history()
{
	global $txt, $context, $ezpSettings, $scripturl;

	$adminShoutBox = allowedTo('admin_forum');

	echo '
	<table class="table_grid">
		<tr class="title_bar">
			<td colspan="4">', $txt['ezp_txt_shouthistory'], '</td>
		</tr>';

	foreach($context['ezshouts_history'] as $row)
	{
		// Censor the shout
		censorText($row['shout']);

		echo '
		<tr class="windowbg">
			<td>';
		if ($ezpSettings['ezp_shoutbox_showdate'])
			echo timeformat($row['date']) . ' ';

		echo '
			</td>
			<td>
				<a href="',$scripturl,'?action=profile;u=',$row['id_member'],'" style="color: ' . $row['online_color'] . ';">',$row['real_name'],'</a>
			</td>
			<td>';


		echo $txt['ezp_shoutbox_says'];

		echo parse_bbc($row['shout']);

		echo '
			</td>';

		if ($adminShoutBox)
			echo '
			<td>
				<a href="',$scripturl,'?action=ezportal;sa=removeshout;shout=',$row['id_shout'],'" style="color: #FF0000">[X]</a>
			</td>';

		echo '
		</tr>';
	}

	if ($adminShoutBox)
	echo '
		<tr class="windowbg">
			<td colspan="4"><a href="',$scripturl,'?action=ezportal;sa=deleteshouthistory">' . $txt['ezp_txt_deleteallshoutbox'] . '</a></td>
		</tr>';

	echo '
	</table>
	<div class="pagesection">
		', $context['page_index'], '
	</div>';
}

function template_ezportal_menu_add()
{
	global $txt, $scripturl, $context;

	echo '
	<form method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=menuadd2" accept-charset="', $context['character_set'], '">
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['ezp_txt_menu_add'], '
		</h3>
	</div>
	<div class="windowbg">
		<dl id="ezPortal_admin" class="settings">
			<dt>
				', $txt['ezp_txt_menu_title'], '
			</dt>
			<dd>
				<input type="text" name="menutitle" size="60" value="">
			</dd>
			<dt>
				', $txt['ezp_txt_menu_link'], '
			</dt>
			<dd>
				<input type="text" name="menulink" size="60" value="">
			</dd>
			<dt>
				', $txt['ezp_txt_menu_newwindow'], '
			</dt>
			<dd>
				<input type="checkbox" name="newwindow">
			</dd>
			<dt>
				', $txt['ezp_txt_permissions'], '
			</dt>
			<dd>
				<input type="checkbox" name="groups[-1]" value="-1" checked="checked">',$txt['membergroups_guests'],'<br>
				<input type="checkbox" name="groups[0]" value="0" checked="checked">',$txt['membergroups_members'],'<br>';

				foreach ($context['groups'] as $group)
						echo '<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" checked="checked">', $group['group_name'], '<br>';

			echo '
			</dd>
		</dl>
	  <input type="hidden" name="layoutid" value="', $context['ezp_layout_id'], '">
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="submit" name="addblock" value="',$txt['ezp_txt_menu_add'],'" class="button">
	</div>
	</form>';
}

function template_ezportal_menu_edit()
{
	global $txt, $scripturl, $context;

	echo '
	<form method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=menuedit2" accept-charset="', $context['character_set'], '">
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['ezp_txt_menu_edit'], '
		</h3>
	</div>
	<div class="windowbg">
		<dl id="ezPortal_admin" class="settings">
			<dt>
				', $txt['ezp_txt_menu_title'], '
			</dt>
			<dd>
				<input type="text" name="menutitle" size="60" value="' .  $context['ezp_menu_row']['title'] . '">
			</dd>
			<dt>
				', $txt['ezp_txt_menu_enabled'], '
			</dt>
			<dd>
				<input type="checkbox" name="menuenabled" ' .  ($context['ezp_menu_row']['enabled'] ? ' checked="checked"' : '') . '>
			</dd>
			<dt>
				', $txt['ezp_txt_menu_link'], '
			</dt>
			<dd>
				<input type="text" name="menulink" size="60" value="' .  $context['ezp_menu_row']['linkurl'] . '">
			</dd>
			<dt>
				', $txt['ezp_txt_menu_newwindow'], '
			</dt>
			<dd>
				<input type="checkbox" name="newwindow" ' .  ($context['ezp_menu_row']['newwindow'] ? ' checked="checked"' : '') . '>
			</dd>
			<dt>
				', $txt['ezp_txt_permissions'], '
			</dt>
			<dd>';

			$permissionsGroups = explode(',',$context['ezp_menu_row']['permissions']);

			echo '
				<input type="checkbox" name="groups[-1]" value="-1" ', ((in_array(-1,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_guests'],'<br />
				<input type="checkbox" name="groups[0]" value="0" ', ((in_array(0,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_members'],'<br />';

			foreach ($context['groups'] as $group)
				echo '<input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />', $group['group_name'], '<br />';

		echo '
			</dd>
		</dl>
		<input type="hidden" name="layoutid" value="', $context['ezp_menu_row']['id_layout'], '">
		<input type="hidden" name="menuid" value="', $context['ezp_menu_row']['id_menu'], '">
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="submit" name="editmenu" value="',$txt['ezp_txt_menu_edit'],'" class="button">
	</div>
	</form>';
}

function template_ezportal_menu_delete()
{
	global $txt, $scripturl, $context;

	echo '
	<form method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=menudelete2" accept-charset="', $context['character_set'], '">
	<div class="cat_bar">
		<h3 class="catbg centertext">
			', $txt['ezp_txt_menu_delete'], '
		</h3>
	</div>
	<div class="windowbg centertext">
		<p>
			', $context['ezp_menu_row']['title'], '<br>
			', $context['ezp_menu_row']['linkurl'], '
		</p>
		<input type="hidden" name="layoutid" value="', $context['ezp_menu_row']['id_layout'], '">
		<input type="hidden" name="menuid" value="', $context['ezp_menu_row']['id_menu'], '">
		<input type="hidden" name="sc" value="', $context['session_id'], '">
		<input type="submit" name="editmenu" value="',$txt['ezp_txt_menu_delete'],'" class="button">
	</div>
	</form>';
}

function template_ezpotal_shoutbox()
{
	global $settings, $context;
	echo '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '">
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index.css">';

	echo $context['html_headers'];

	echo '
	<style type="text/css">
			body, td {
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
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['ezp_txt_copyrightremoval'], '
		</h3>
	</div>
	<div class="windowbg">
		<dl id="ezPortal_admin" class="settings">
			<dt>
				', $txt['ezp_txt_copyrightkey'], '<br>
				<a href="https://www.smfhacks.com/copyright_removal.php?mod=' . $modID .  '&board=' . $urlBoardurl . '" target="_blank">' . $txt['ezp_txt_ordercopyright'] . '</a>
			</dt>
			<dd>
				<input type="text" name="ezp_copyrightkey" size="50" value="' . $modSettings['ezp_copyrightkey'] . '">
			</dd>
		</dl>
		<input type="submit" value="' . $txt['ezp_savesettings'] . '" class="button">
	</div>
	</form>';
}
?>