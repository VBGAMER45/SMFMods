<?php
/*
EzPortal
Version 3.1
by:vbgamer45
https://www.ezportal.com
Copyright 2010-2019 http://www.samsonsoftware.com
*/

function template_ezportal_addpage()
{
	global $context, $txt, $scripturl;

	echo '
	<form id="postmodify" method="post" name="frmaddpage" id="frmaddpage" action="', $scripturl, '?action=ezportal;sa=addpage2" accept-charset="', $context['character_set'], '"  onsubmit="submitonce(this);">
	<div class="cat_bar">
		<h3 class="catbg centertext">', $txt['ezp_addpage'], '</h3>
	</div>
	<span class="upperframe"><span></span></span>
	<div class="roundframe">
		<dl id="post_header">
			<dt>', $txt['ezp_txt_page_title'],'</dt>
			<dd><input type="text" name="pagetitle" size="100" /></dd>
		</dl>
		';

		if ($context['ezp_page_bbc'] == 0)
		echo '
		<b>', $txt['ezp_txt_page_content'],'</b>
		<textarea name="pagecontent" id="myTextEditor" rows="15" cols="55"></textarea>

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
			
		</script>';
		else
		{
					echo '
		<b>', $txt['ezp_txt_page_content'],'</b>';

				   	if (!function_exists('getLanguages'))
					{
					// Showing BBC?
						if ($context['show_bbc'])
						{
							echo '
														', template_control_richedit($context['post_box_name'], 'bbc'), '';
						}

						// What about smileys?
						if (!empty($context['smileys']['postform']))
							echo '
														', template_control_richedit($context['post_box_name'], 'smileys'), '';

						// Show BBC buttons, smileys and textbox.
						echo '
														', template_control_richedit($context['post_box_name'], 'message'), '';
					}
					else
					{
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
						echo '
									', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
						}
		}



		echo '
	 <br /> <b>', $txt['ezp_txt_metatags'],'</b>&nbsp;<br />
	  <textarea name="metatags" rows="5" cols="55" class=""></textarea>
	    ', $txt['ezp_txt_metatags2'],'
	   
	<p><b>', $txt['ezp_txt_permissions'],'</b></p>
	<ul class="post_options">
		<li><input type="checkbox" name="groups[-1]" value="-1" />',$txt['membergroups_guests'],'</li>
		<li><input type="checkbox" name="groups[0]" value="0" />', $txt['membergroups_members'],'</li>';

		foreach ($context['groups'] as $group)
			echo '<li><input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" />', $group['group_name'], '</li>';

	echo '<li><input type="checkbox" id="checkAllGroups"  onclick="invertAll(this, this.form, \'groups\');" class="check" />',$txt['ezp_txt_checkall'],'</li>
	</ul>
	

      <dl>
	    <dt><b>', $txt['ezp_txt_showinmenu'],'</b>&nbsp;</dt>	 
		<dd><input type="checkbox" name="showinmenu"></dd>
	  </dl>
	  <dl>
	    <dt><b>', $txt['ezp_txt_menutext'],'</b>&nbsp;</dt>	 
		<dd><input type="text" name="menutitle"  size="30" /></dd>
	  </dl>	  
	  <dl>
	    <dt><b>', $txt['ezp_txt_icon'],'</b>&nbsp;</dt>	 
		<dd><input type="text" name="icon"  size="30" value="page.png" /></dd>
	  </dl>	  
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="bbc" value="',$context['ezp_page_bbc'], '">
	    <input type="submit" name="addpage" value="',$txt['ezp_addpage'],'" />

	</div>
	<span class="lowerframe"><span></span></span>
  	</form>
	<br class="clear" />';

}

function template_ezportal_editpage()
{
	global $context, $txt, $scripturl;

	echo '
	<form id="postmodify" method="post" name="frmeditpage" id="frmeditpage" action="', $scripturl, '?action=ezportal;sa=editpage2" accept-charset="', $context['character_set'], '"  onsubmit="submitonce(this);">
    <div class="cat_bar">
		<h3 class="catbg centertext">', $txt['ezp_editpage'], '</h3>
	</div>
	<span class="upperframe"><span></span></span>
	<div class="roundframe">
		<dl id="post_header">
			<dt><b>', $txt['ezp_txt_page_title'],'</b>&nbsp;</dt>
			<dd><input type="text" name="pagetitle" size="80" value="',$context['ezp_editpage_data']['title'], '" /></dd>
		</dl>
		';

		if ($context['ezp_page_bbc'] == 0)
		echo '
		<b>', $txt['ezp_txt_page_content'],'</b>
	    <textarea name="pagecontent" id="myTextEditor" rows="15" cols="55">', htmlentities($context['ezp_editpage_data']['content']),'</textarea>

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
		<br />';
				else
		{
					echo '
		<b>', $txt['ezp_txt_page_content'],'</b>';

				   	if (!function_exists('getLanguages'))
					{
					// Showing BBC?
						if ($context['show_bbc'])
						{
							echo '
														', template_control_richedit($context['post_box_name'], 'bbc'), '';
						}

						// What about smileys?
						if (!empty($context['smileys']['postform']))
							echo '
														', template_control_richedit($context['post_box_name'], 'smileys'), '';

						// Show BBC buttons, smileys and textbox.
						echo '
														', template_control_richedit($context['post_box_name'], 'message'), '';
					}
					else
					{
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
						echo '
									', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
						}
		}




		echo '
		
	  <b>', $txt['ezp_txt_metatags'],'</b>&nbsp;<br />
	    <textarea name="metatags" rows="5" cols="55" class="">', $context['ezp_editpage_data']['metatags'],'</textarea><br />
	    ', $txt['ezp_txt_metatags2'],'
	

		<b>', $txt['ezp_txt_permissions'],'</b>
		<ul class="post_options">';

		$permissionsGroups = explode(',',$context['ezp_editpage_data']['permissions']);

		echo '
			<li><input type="checkbox" name="groups[-1]" value="-1" ', ((in_array(-1,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_guests'],'</li>
			<li><input type="checkbox" name="groups[0]" value="0" ', ((in_array(0,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_members'],'</li>';

	foreach ($context['groups'] as $group)
		echo '
			<li><input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />', $group['group_name'], '</li>';

	echo '
			<li><input type="checkbox" id="checkAllGroups"  onclick="invertAll(this, this.form, \'groups\');" class="check" />',$txt['ezp_txt_checkall'],'</li>
		</ul>
		
		
			  
	  <dl>
	    <dt><b>', $txt['ezp_txt_showinmenu'],'</b>&nbsp;</dt>	 
		<dd><input type="checkbox" name="showinmenu" ' . ($context['ezp_editpage_data']['showinmenu']  == 1 ? ' checked="checked" ' : '') . '></dd>
	  </dl>
	  <dl>
	    <dt><b>', $txt['ezp_txt_menutext'],'</b>&nbsp;</dt>	 
		<dd><input type="text" name="menutitle"  size="30" value="',$context['ezp_editpage_data']['menutitle'], '" /></dd>
	  </dl>	  
	  	  <dl>
	    <dt><b>', $txt['ezp_txt_icon'],'</b>&nbsp;</dt>	 
		<dd><input type="text" name="icon"  size="30" value="',$context['ezp_editpage_data']['icon'], '" /></dd>
	  </dl>	  

		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="p" value="', $context['ezp_editpage_data']['id_page'],'" />
		<input type="submit" name="editpage" value="', $txt['ezp_editpage'], '" />
	</div>
	<span class="lowerframe"><span></span></span>
  	</form>
	<br class="clear" />';

}

function template_ezportal_deletepage()
{
	global $context, $txt, $scripturl;

	echo '
	<form method="post" name="frmdelpage" id="frmdelpage" action="', $scripturl, '?action=ezportal;sa=deletepage2" accept-charset="', $context['character_set'], '">
	    <div class="cat_bar">
			<h3 class="catbg centertext">', $txt['ezp_deletepage'], '</h3>
		</div>
		<div class="windowbg2 centertext">
		<span class="topslice"><span></span></span>
			',$txt['ezp_deletepage_confirm'], '<br />
			<b>',$context['ezp_deletepage_data']['title'],'</b><br />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="hidden" name="p" value="', $context['ezp_deletepage_data']['id_page'],'" />
			<input type="submit" name="delpage" value="', $txt['ezp_deletepage'], '" />
		<span class="botslice"><span></span></span>
		</div>
  	</form>
	<br class="clear" />';
}

function template_ezportal_settings()
{
	global $context, $txt, $ezPortalVersion, $scripturl, $ezpSettings, $modSettings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">' .$txt['ezp_settings']  . '</h3>
	</div>
	<div class="windowbg2">
	<span class="topslice"><span></span></span>
		<form method="post" class="content" action="', $scripturl, '?action=ezportal;sa=settings2" accept-charset="', $context['character_set'], '">
			<dl class="settings">
				<dt>',$txt['ezp_txt_yourversion'] , $ezPortalVersion, '</dt>
				<dd>',$txt['ezp_txt_latestversion'],'<span id="lastezportal"></span></dd>
				<dt>',$txt['ezp_url'], '</dt>
				<dd><input type="text" name="ezp_url" value="',  $ezpSettings['ezp_url'], '" size="50" /></dd>
				<dt>', $txt['ezp_path'], '</dt>
				<dd><input type="text" name="ezp_path" value="',  $ezpSettings['ezp_path'], '" size="50" /></dd>
				<dt>', $txt['ezp_portal_enable'] . '</dt>
				<dd><input type="checkbox" name="ezp_portal_enable" ',($ezpSettings['ezp_portal_enable'] ? ' checked="checked" ' : ''), ' /></dd>
				<dt>', $txt['ezp_portal_homepage_title'], '</dt>
				<dd><input type="text" name="ezp_portal_homepage_title" value="',  $ezpSettings['ezp_portal_homepage_title'], '" size="50" /></dd>
				
				<dt>', $txt['ezp_responsivemode'] . '<br />', $txt['ezp_responsivemode_desc'] . '</dt>
				<dd><input type="checkbox" name="ezp_responsivemode" ',($modSettings['ezp_responsivemode'] ? ' checked="checked" ' : ''), ' /></dd>
				

				
				<dt>', $txt['ezp_hide_edit_delete'] . '</dt>
				<dd><input type="checkbox" name="ezp_hide_edit_delete" ',($ezpSettings['ezp_hide_edit_delete'] ? ' checked="checked" ' : ''), ' /></dd>
				<dt>', $txt['ezp_disable_tinymce_html'] . '</dt>
				<dd><input type="checkbox" name="ezp_disable_tinymce_html" ',($ezpSettings['ezp_disable_tinymce_html'] ? ' checked="checked" ' : ''), ' /></dd>
				<dt>', $txt['ezp_disableblocksinadmin'] . '</dt>
				<dd><input type="checkbox" name="ezp_disableblocksinadmin" ',($ezpSettings['ezp_disableblocksinadmin'] ? ' checked="checked" ' : ''), ' /></dd>
				<dt>', $txt['ezp_disablemobiledevices'] . '</dt>
				<dd><input type="checkbox" name="ezp_disablemobiledevices" ',($ezpSettings['ezp_disablemobiledevices'] ? ' checked="checked" ' : ''), ' /></dd>
				
				
				<dt>', $txt['ezp_pages_seourls'] . '</dt>
				<dd><input type="checkbox" name="ezp_pages_seourls" ',($ezpSettings['ezp_pages_seourls'] ? ' checked="checked" ' : ''), ' /></dd>

				
				<dt><b>',$txt['ezp_shoutbox_settings'],'</b></dt>		
				<dd></dd>
				<hr class="hrcolor clear" />
				<dt>', $txt['ezp_shoutbox_enable'] . '</dt>
				<dd><input type="checkbox" name="ezp_shoutbox_enable" ',($ezpSettings['ezp_shoutbox_enable'] ? ' checked="checked" ' : ''), ' /></dd>
				<dt>', $txt['ezp_shoutbox_showdate'] . '</dt>
				<dd><input type="checkbox" name="ezp_shoutbox_showdate" ',($ezpSettings['ezp_shoutbox_showdate'] ? ' checked="checked" ' : ''), ' /></dd>
				<dt>', $txt['ezp_shoutbox_archivehistory'] . '</dt>
				<dd><input type="checkbox" name="ezp_shoutbox_archivehistory" ',($ezpSettings['ezp_shoutbox_archivehistory'] ? ' checked="checked" ' : ''), ' /></dd>
				<dt>', $txt['ezp_shoutbox_hidesays'] . '</dt>
				<dd><input type="checkbox" name="ezp_shoutbox_hidesays" ',($ezpSettings['ezp_shoutbox_hidesays'] ? ' checked="checked" ' : ''), ' /></dd>
				<dt>', $txt['ezp_shoutbox_hidedelete'] . '</dt>
				<dd><input type="checkbox" name="ezp_shoutbox_hidedelete" ',($ezpSettings['ezp_shoutbox_hidedelete'] ? ' checked="checked" ' : ''), ' /></dd>
				<dt>', $txt['ezp_shoutbox_history_number'], '</dt>
				<dd><input type="text" name="ezp_shoutbox_history_number" value="',  $ezpSettings['ezp_shoutbox_history_number'], '" size="50" /></dd>
				<dt>', $txt['ezp_shoutbox_showbbc'] . '</dt>
				<dd><input type="checkbox" name="ezp_shoutbox_showbbc" ',($ezpSettings['ezp_shoutbox_showbbc'] ? ' checked="checked" ' : ''), ' /></dd>
				<dt>', $txt['ezp_shoutbox_showsmilies'] . '</dt>
				<dd><input type="checkbox" name="ezp_shoutbox_showsmilies" ',($ezpSettings['ezp_shoutbox_showsmilies'] ? ' checked="checked" ' : ''), ' /></dd>
				<dt>', $txt['ezp_shoutbox_refreshseconds'], '</dt>
				<dd><input type="text" name="ezp_shoutbox_refreshseconds" value="',  $ezpSettings['ezp_shoutbox_refreshseconds'], '" size="50" /></dd>
				<dt>', $txt['ezp_allowstats'] . '</dt>
				<dd><input type="checkbox" name="ezp_allowstats" ',($ezpSettings['ezp_allowstats'] ? ' checked="checked" ' : ''), ' /></dd>
				<hr class="hrcolor clear" />
				<dt>' . $txt['ezp_forumgetlisted'] . '</dt>
				<dd></dd>
			</dl>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="submit" name="savesettings" value="', $txt['ezp_savesettings'], '" />
		</form>
		<div class="padding">
			',$txt['ezp_donate'],'
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
		</div>
	<span class="botslice"><span></span></span>
	</div>
	<br class="clear" />';

	echo '
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
		
			 // ]]>
		</script>';
}

function template_ezportal_messageform()
{
	global $context;

	echo '
    	<div class="cat_bar">
			<h3 class="catbg centertext">', $context['ezportal_message_title'], '</h3>
		</div>
		<div class="windowbg2">
		<span class="topslice"><span></span></span>
			',$context['ezportal_message_description'],'
		<span class="botslice"><span></span></span>
		</div>
		<br class="clear" />';
}

function template_ezportal_modules()
{
	global $context, $txt, $scripturl;

	$styleclass = 'windowbg';
	echo '
		<div class="cat_bar">
			<h3 class="catbg">' . $txt['ezp_modules']  . '</h3>
		</div>
		<div class="windowbg2">
		<span class="topslice"><span></span></span>
			<ul class="reset">';

			foreach($context['module-list'] as $module)
			{
				echo '
					<li class="',$styleclass,'">
						',$module['title'],'<br />
						',$module['text'],'<br />
						<a href="', $scripturl, '?action=admin;area=packages;pgdownload;auto;package=',$module['filename1'],';sesc=', $context['session_id'], '">',$txt['ezp_module_download'] ,'</a>
					</li>';

					if ($styleclass == 'windowbg')
						$styleclass = 'windowbg2';
					else
						$styleclass = 'windowbg';
			}
				echo '
			</ul>
		<span class="botslice"><span></span></span>
		</div>
		<br class="clear" />';
}

function template_ezportal_blocks()
{
	global $context, $txt, $scripturl, $ezpSettings;

	$styleclass = "windowbg";
	echo '
		<div class="cat_bar">
			<h3 class="catbg">' . $txt['ezp_blocks']  . '</h3>
		</div>';

	foreach($context['ezPortalAdminColumns']  as $row)
	{
		echo '
		<form method="post" action="', $scripturl, '?action=ezportal;sa=blocks2" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					<div class="ezTCell">',$row['column_title'],'</div>
					<div class="ezTCell"><a href="', $scripturl, '?action=admin;area=ezpblocks;sa=editcolumn;column=',$row['id_column'],'"><img src="',$ezpSettings['ezp_url'],'icons/page_white_edit.png" alt="',$txt['ezp_edit2'], '" /> ',$txt['ezp_editcolumn'],'</a></div>
					<div class="ezTCell">', ($row['active'] ? '<font color="#00FF00">' . $txt['ezp_txt_active'] . '</font>' :  '<font color="#FF0000">' . $txt['ezp_txt_disabled'] . '</font>'),'</div>
				</h3>
			</div>
			<div class="ezTable">
				<div class="ezTRow titlebg">
					<div class="ezTCell ez10p">',$txt['ezp_txt_order'] ,'</div>
					<div class="ezTCell ez50p">',$txt['ezp_txt_title'] ,'</div>
					<div class="ezTCell ez20p">',$txt['ezp_txt_active'],'</div>
					<div class="ezTCell ez20p">',$txt['ezp_txt_options'],'</div>
				</div>';
			// Now show all the ezBlocks under the column
			foreach($row['blocks'] as $blockRow)
			{
				echo '
					<div class="ezTRow ',$styleclass,'">
						<div class="ezTCell ez10p"><input type="text" name="order[',$blockRow['id_layout'],']" value="',($blockRow['id_order']*10),'" size="4" /></div>
						<div class="ezTCell ez50p"><input type="text" name="title[',$blockRow['id_layout'],']" value="',$blockRow['customtitle'],'" size="50" /></div>
						<div class="ezTCell ez20p">
							<select name="active[',$blockRow['id_layout'],']">
							<option value="1" ',($blockRow['active'] == 1 ? ' selected="selected" ' : ''),'>',$txt['ezp_yes'],'</option>
							<option value="0" ',($blockRow['active'] == 0 ? ' selected="selected" ' : ''),'>',$txt['ezp_no'],'</option>
							</select>
						</div>
						<div class="ezTCell ez20p">
							<a href="',$scripturl,'?action=admin;area=ezpblocks;sa=editblock;block=',$blockRow['id_layout'],'">',$txt['ezp_edit'], '</a>
							&nbsp;
							<a href="',$scripturl,'?action=admin;area=ezpblocks;sa=deleteblock;block=',$blockRow['id_layout'],'">',$txt['ezp_delete'], '</a>
						</div>
					</div>';

						// Alternate the style class
						if ($styleclass == "windowbg")
							$styleclass = "windowbg2";
						else
							$styleclass = "windowbg";
					}
		// The grand finale!
		echo '
			</div>
				<div class="windowbg2 centertext padding">
					<a href="', $scripturl, '?action=admin;area=ezpblocks;sa=addblock;column=',$row['id_column'],'">',$txt['ezp_addblock'],'</a>
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="hidden" name="column" value="',$row['id_column'],'" />
					<input type="submit" value="',$txt['ezp_saveblocks'],'" />
				</div>
		</form>
		<br class="clear" />';
	}
}

function template_ezportal_import()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">' . $txt['ezp_import']  . '</h3>
	</div>
	<div class="windowbg2">
		<span class="topslice"><span></span></span>
			<span class="smalltext">',$txt['ezp_import_information'],'</span><br />';

				// Setup the import buttons
				if ($context['portals']['MX'] == true)
					echo '<form method="post" action="', $scripturl,'?action=admin;area=ezpsettings;sa=import2;type=mx">
							<input type="submit" value="',$txt['ezp_import_mx'],'" />
						</form>';

				if ($context['portals']['SP'] == true)
					echo '<form method="post" action="', $scripturl,'?action=admin;area=ezpsettings;sa=import2;type=sp">
							<input type="submit" value="',$txt['ezp_import_sp'],'" />
						</form>';

				if ($context['portals']['TP'] == true)
					echo '<form method="post" action="', $scripturl,'?action=admin;area=ezpsettings;sa=import2;type=tp">
							<input type="submit" value="',$txt['ezp_import_tp'],'" />
						</form>';


				echo '
		<span class="botslice"><span></span></span>
	</div>';
}

function template_ezportal_pagemanager()
{
	global $context, $txt, $scripturl, $boardurl, $ezpSettings;

	$styleclass = "windowbg";
	echo '
	<div class="cat_bar">
		<h3 class="catbg">' . $txt['ezp_pagemanager']  . '</h3>
	</div>
	<div class="ezTable">
		<div class="ezTRow titlebg">
			<div class="ezTCell ez25p">',$txt['ezp_txt_title'] ,'</div>
			<div class="ezTCell ez25p">',$txt['ezp_txt_views'],'</div>
			<div class="ezTCell ez25p">',$txt['ezp_txt_date'],'</div>
			<div class="ezTCell ez25p">',$txt['ezp_txt_options'],'</div>
		</div>';

		foreach($context['ezp_pages']  as $row)
		{
			echo '
			<div class="ezTRow ',$styleclass,'">
				<div class="ezTCell ez50p"><a href="',$scripturl,'?action=ezportal;sa=page;p=',$row['id_page'],'">',$row['title'], '</a><br />';
				
				
					$pageurl = $scripturl . '?action=ezportal;sa=page;p='  . $row['id_page'];
			
						if (!empty($ezpSettings['ezp_pages_seourls']))
								$pageurl = $boardurl . '/pages/' . MakeSEOUrl($row['title']) . '-' . $row['id_page'];	
		
				
				echo '
				<a href="',$pageurl,'">',$pageurl,'</a>
				</div>
				<div class="ezTCell ez10p">',$row['views'], '</div>
				<div class="ezTCell ez20p">',timeformat($row['date']), '</div>
				<div class="ezTCell ez20p">
				<a href="',$scripturl,'?action=admin;area=ezppagemanager;sa=editpage;p=',$row['id_page'],'">',$txt['ezp_edit'], '</a>
				<br />
				<a href="',$scripturl,'?action=admin;area=ezppagemanager;sa=deletepage;p=',$row['id_page'],'">',$txt['ezp_delete'], '</a>
				</div>
			</div>';

			// Alternate the style class
			if ($styleclass == "windowbg")
				$styleclass = "windowbg2";
			else
				$styleclass = "windowbg";
		}

		echo '
	</div>
	<div class="padding">
		',$txt['ezp_txt_pages'],' ',$context['page_index'],'
		<div class="floatright">
			<form method="post" action="', $scripturl,'?action=admin;area=ezppagemanager;sa=addpage">
				<input type="submit" name="btnsubmit" value="',$txt['ezp_addpage'],'" />
				<input type="submit" name="btnsubmit" value="', $txt['ezp_bbc_addpage'], '">
			</form>
		</div>
	</div>
	<br class="clear" />';
}

function template_ezportal_credits()
{
	global $context, $txt;

	echo '
    <div class="cat_bar">
		<h3 class="catbg">' . $txt['ezp_credits'] . '</h3>
	</div>
	<div class="windowbg2">
		<span class="topslice"><span></span></span>
			<div class="padding">
				', $txt['ezp_developedby'], '
				<hr />
				',$txt['ezp_helpout'],'
				',$txt['ezp_donate'],'<br />
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
			</div>
		<span class="botslice"><span></span></span>
	</div>
	<br class="clear" />';
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
	
	echo '    <div class="cat_bar">
		<h3 class="catbg centertext">
        ' . $context['ezpage_info']['title'] . '
        ';
        
        if (allowedTo('ezportal_page') || allowedTo('ezportal_manage'))
        {
        	echo ' <a href="', $scripturl, '?action=admin;area=ezppagemanager;sa=editpage;p=',$context['ezpage_info']['id_page'],'"><img src="',$ezpSettings['ezp_url'],'icons/page_white_edit.png" alt="',$txt['ezp_edit2'], '" /></a>
        	<a href="',$scripturl,'?action=admin;area=ezppagemanager;sa=deletepage;p=',$context['ezpage_info']['id_page'],'"><img src="',$ezpSettings['ezp_url'],'icons/plugin_delete.png" alt="',$txt['ezp_delete2'], '" /></a>';

        }
        
        echo '</h3>
  </div>';
	
	echo '
<div class="wikicontent windowbg2 clearright">
		<span class="topslice"><span></span></span>
		<div class="content">';
	// Show the html content
	echo $context['ezp_pagecontent'];
	
	
	echo '</div></div>';
}

function template_ezportal_edit_column()
{
	global $context, $txt, $scripturl;

	echo '
    <div class="cat_bar">
		<h3 class="catbg centertext">' . $txt['ezp_editcolumn'] . '</h3>
	</div>
	<div class="windowbg2">
	<span class="topslice"><span></span></span>
		<form method="post" class="content" name="frmeditcolumn" id="frmeditcolumn" action="', $scripturl, '?action=ezportal;sa=editcolumn2" accept-charset="', $context['character_set'], '">
			<dl class="settings">
				<dt>', $txt['ezp_txt_columntitle'],'</dt>
				<dd>',$context['ezp_column_data']['column_title'],'</dd>
				<dt>', $txt['ezp_txt_columnwidth'],'</dt>
				<dd><input type="text" name="columnwidth" size="10" value="',$context['ezp_column_data']['column_width'],'" /></dd>
				<dt>', $txt['ezp_txt_columnwidth_percent'],'<br /><span class="smalltext">',$txt['ezp_txt_columnwidth_percent_note'],'</span></dt>
				<dd><input type="text" name="columnpercent" size="10" value="',$context['ezp_column_data']['column_percent'],'" />
				<dt>', $txt['ezp_txt_active'],'</dt>
				<dd>	   		
					<select name="active">
						<option value="1" ',($context['ezp_column_data']['active'] == 1 ? ' selected="selected" ' : ''),'>',$txt['ezp_yes'],'</option>
						<option value="0" ',($context['ezp_column_data']['active'] == 0 ? ' selected="selected" ' : ''),'>',$txt['ezp_no'],'</option>
					</select>
				</dd>';

	if ($context['ezp_column_data']['column_title'] == 'Left' || $context['ezp_column_data']['column_title'] == 'Right')
	{
		echo '
				<dt>', $txt['ezp_txt_can_collapse_column'],'</dt>
				<dd><input type="checkbox" name="can_collapse" ' . ($context['ezp_column_data']['can_collapse'] == 1  ? ' checked="checked" ' : '') . ' /></dd>';
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



				<dt>', $txt['ezp_txt_visible_areas'],'</dt>
				<dd><a href="', $scripturl, '?action=admin;area=ezpblocks;sa=visiblesettings;column=',$context['ezp_column_data']['id_column'],'">',$txt['ezp_txt_update_visible_options'],'</a></dd>
			</dl>
			<p class="centertext">
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				<input type="hidden" name="column" value="',$context['ezp_column_data']['id_column'],'" />
				<input type="submit" name="editcolumn" value="',$txt['ezp_editcolumn'],'" />
			</p>
	  	</form>
	<span class="botslice"><span></span></span>
	</div>
	<br class="clear" />';
}

function template_ezportal_add_block()
{
	global $context, $txt, $scripturl;

echo '
    <div class="cat_bar">
		<h3 class="catbg centertext">' . $txt['ezp_addblock']. '</h3>
	</div>
	<form method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=admin;area=ezpblocks;sa=addblock2" accept-charset="', $context['character_set'], '">
	<div class="ezTable">
		<div class="ezTRow windowbg2">
			<div class="ezTCell ez50p righttext"><b>', $txt['ezp_txt_block_type'],'</b></div>
			<div class="ezTCell ez50p lefttext"><select name="blocktype">';

			foreach ($context['ezp_blocks'] as $row)
				echo '<option value="', $row['id_block'], '">', $row['blocktitle'], '</option>';

	echo '</select>
			</div>
		</div>
	</div>
	<div class="windowbg2 centertext padding">
		<input type="hidden" name="column" value="',$context['ezportal_column'],'" />
		<input type="submit" name="addblock" value="',$txt['ezp_addblock'],'" /></td>
	</div>
  	</form>';
}

function template_ezportal_add_block2()
{
	global $context, $txt, $scripturl, $ezpSettings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">' . $txt['ezp_addblock']. '</h3>
	</div>
	<form id="postmodify" method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=addblock3" accept-charset="', $context['character_set'], '"  onsubmit="submitonce(this);">
	<span class="upperframe"><span></span></span>
	<div class="roundframe">
		<dl id="post_header">
			<dt>', $txt['ezp_txt_block_title'],'</dt>
			<dd><input type="text" name="blocktitle" size="60" value="',$context['ezp_block_data']['blocktitle'],'" /></dd>
			<dt>', $txt['ezp_txt_column'],'</dt>
			<dd>
				<select name="column">';

				foreach ($context['ezp_columns'] as $column)
					echo '
						<option value="', $column['id_column'], '" ',($column['id_column'] == $context['ezportal_column'] ? ' selected="selected" ' : ''), '>', $column['column_title'], '</option>';

	echo '
				</select>
			</dd>
			<dt>', $txt['ezp_txt_icon'],'</dt>
			<dd>
				<select id="iconchoice" name="icon" onchange="ChangeIconPic(this.value)">
				<option value="0">',$txt['ezp_txt_noicon'],'</option>';

		foreach ($context['ezp_icons'] as $icon)
				echo '<option value="', $icon['id_icon'], '">', $icon['icon'], '</option>';


	echo '
				</select>
				<img id="iconPick" src="" alter="" />
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
			</dd>
	    </dl>';

	// Check if we need to setup an editable box for this ezBlock
	if ($context['ezp_block_data']['data_editable'] == 1)
	{
		echo '
		<b>', $txt['ezp_txt_block_data'],'</b>
		<textarea name="blockdata" id="myTextEditor" rows="10" cols="60">',$context['ezp_block_data']['blockdata'],'</textarea>';

		if ($context['ezp_showtinymcetoggle'] == true)
		{
			echo '
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
					</script>';
		}
	}


	if ($context['ezp_block_data']['blocktitle'] == 'Menu ezBlock')
	{
		echo '
			<b>' . $txt['ezp_txt_menu_add_block_note'] . '</b>';
	}

	echo '
			<dl class="settings">';

	// Now show all parameters for this ezBlock
	foreach ($context['ezp_block_parameters'] as $parameter)
	{
		if ($parameter['parameter_type'] == 'hidden')
			continue;

		echo '
				<dt>', $parameter['title'],($parameter['required'] == 1 ? '<span style="color: red;">*</span>' : ''),'</dt>
				<dd>';

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
				   	if (!function_exists('getLanguages'))
					{
					// Showing BBC?
						if ($context['show_bbc'])
						{
							echo '
														', template_control_richedit($context['post_box_name'], 'bbc'), '';
						}

						// What about smileys?
						if (!empty($context['smileys']['postform']))
							echo '
														', template_control_richedit($context['post_box_name'], 'smileys'), '';

						// Show BBC buttons, smileys and textbox.
						echo '
														', template_control_richedit($context['post_box_name'], 'message'), '';
					}
					else
					{
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
							echo '
										', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
						}
				}

				else
				{
						echo '<input type="text" name="parameter[', $parameter['id_parameter'],']" size="60" value="', $parameter['defaultvalue'],'" />';
				}
		echo '
				</dd>';
	}

	echo '
			</dl>
			<p><b>', $txt['ezp_txt_permissions'],'</b></p>
			<ul class="post_options">
				<li><input type="checkbox" name="groups[-1]" value="-1" checked="checked" />',$txt['membergroups_guests'],'</li>
				<li><input type="checkbox" name="groups[0]" value="0" checked="checked" />',$txt['membergroups_members'],'</li>';

	foreach ($context['groups'] as $group)
		echo '
				<li><input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" checked="checked" />', $group['group_name'], '</li>';

		echo '
				<li><input type="checkbox" id="checkAllGroups"  onclick="invertAll(this, this.form, \'groups\');" class="check" />',$txt['ezp_txt_checkall'],'</li>
			</ul>
		    <p><b>', $txt['ezp_txt_block_managers'],'</b></p>
			<ul class="post_options">';

			foreach ($context['groups'] as $group)
			{
				if ($group['ID_GROUP'] == 1)
					continue;

				echo '
				<li><input type="checkbox" name="managers[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" />', $group['group_name'], '</li>';
			}

		echo '
			</ul>
			<p><b>', $txt['ezp_txt_additional_settings'],'</b></p>
			<ul class="post_options">
				<li><input type="checkbox" name="can_collapse" />',$txt['ezp_txt_can_collapse'],'</li>
				<li><input type="checkbox" name="hidetitlebar" />',$txt['ezp_hidetitlebar'],'</li>
				<li><input type="checkbox" name="hidemobile" />',$txt['ezp_hidemobile'],'</li>
				<li><input type="checkbox" name="showonlymobile" />',$txt['ezp_showonlymobile'],'</li>
			</ul>
			
		<dl>
			<dt>', $txt['ezp_txt_css_header_class'],'&nbsp;</dt>
	    	<dd><input type="text" name="block_header_class" size="60" value="" /></dd>

			<dt>', $txt['ezp_txt_css_body_class'],'&nbsp;</dt>
			<dd><input type="text" name="block_body_class" size="60" value="" /></dd>
	  </dl>	
			
			
	<div class="floatright">
	    <input type="hidden" name="sc" value="', $context['session_id'], '" />
	    <input type="hidden" name="blocktype" value="',$context['ezportal_blocktype'],'" />
	    <input type="submit" name="addblock" value="',$txt['ezp_addblock'],'" /></td>
	</div>
	</div>
	<span class="lowerframe"><span></span></span>
  	</form>';

}

function template_ezportal_edit_block()
{
	global $context, $txt, $scripturl, $ezpSettings;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">' . $txt['ezp_editblock']. '</h3>
	</div>
	<form id="postmodify" method="post" name="frmeditblock" id="frmeditblock" action="', $scripturl, '?action=ezportal;sa=editblock2" accept-charset="', $context['character_set'], '"  onsubmit="submitonce(this);">
	<span class="upperframe"><span></span></span>
	<div class="roundframe">
		<dl id="post_header">
			<dt>', $txt['ezp_txt_block_title'],'</dt>
			<dd><input type="text" name="blocktitle" size="60" value="',$context['ezp_block_info']['customtitle'],'"  /></dd>

			<dt>
				', $txt['ezp_txt_menu_enabled'], '
			</dt>
			<dd>
				<select name="active">
						<option value="1" ',($context['ezp_block_info']['active'] == 1 ? ' selected="selected" ' : ''),'>',$txt['ezp_yes'],'</option>
						<option value="0" ',($context['ezp_block_info']['active'] == 0 ? ' selected="selected" ' : ''),'>',$txt['ezp_no'],'</option>
				</select>
			</dd>

			<dt>', $txt['ezp_txt_column'],'</dt>
			<dd>
				<select name="column">';

		foreach ($context['ezp_columns'] as $column)
				echo '<option value="', $column['id_column'], '" ',($column['id_column'] == $context['ezp_block_info']['id_column'] ? ' selected="selected" ' : ''), '>', $column['column_title'], '</option>';


	echo '
				</select>
			</dd>
			<dt>', $txt['ezp_txt_icon'],'</dt>
			<dd>
				<select id="iconchoice" name="icon" onchange="ChangeIconPic(this.value)">
					<option value="0"',(empty($context['ezp_block_info']['id_icon']) ? ' selected="selected" ': ''),  '>',$txt['ezp_txt_noicon'],'</option>';

		foreach ($context['ezp_icons'] as $icon)
				echo '
					<option value="', $icon['id_icon'], '"',(($context['ezp_block_info']['id_icon'] == $icon['id_icon']) ? ' selected="selected" ': ''),  '>', $icon['icon'], '</option>';


	echo '
				</select>
				<img id="iconPick" src="" alter="" />

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
			</dd>
		</dl>';

	// Check if we need to setup an editable box for this ezBlock
	if ($context['ezp_block_info']['data_editable'] == 1)
	{
		echo '
			<b>', $txt['ezp_txt_block_data'],'</b>
			<textarea name="blockdata" id="myTextEditor" rows="10" cols="60">',$context['ezp_block_info']['blockdata'],'</textarea>';

		if ($context['ezp_showtinymcetoggle'] == true)
		{
			echo '
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
			</script>';
		}

	}

	if ($context['ezp_block_info']['blocktitle'] == 'Menu ezBlock')
	{
		$styleClass = 'windowbg2';
		// Display the menu items
		echo '
			<div class="ezTable">
				<div class="ezTRow">
					<div class="ezTCell">',$txt['ezp_txt_menu_title'],'</div>
					<div class="ezTCell">',$txt['ezp_txt_menu_enabled'],'</div>
					<div class="ezTCell">', $txt['ezp_txt_menu_order'],'</div>
					<div class="ezTCell">',$txt['ezp_txt_options'],'</div>
				</div>';

		foreach($context['ezp_menu_block_items'] as $menuRow)
		{
			echo '
				<div class="ezTRow ' . $styleClass . '">
					<div class="ezTCell">' . $menuRow['title'] . '</div>
					<div class="ezTCell">' . ($menuRow['enabled'] == 1 ? $txt['ezp_yes'] : $txt['ezp_no']) . '</div>
					<div class="ezTCell"><a href="',$scripturl,'?action=ezportal;sa=menuup&id=',$menuRow['id_menu'],'&block=',$context['ezp_block_info']['id_layout'],'">' . $txt['ezp_txt_menu_up'] . '</a>&nbsp;<a href="',$scripturl,'?action=ezportal;sa=menudown&id=',$menuRow['id_menu'],'&block=',$context['ezp_block_info']['id_layout'],'">' . $txt['ezp_txt_menu_down'] . '</a></div>
					<div class="ezTCell">
							<a href="',$scripturl,'?action=ezportal;sa=menuedit&id=',$menuRow['id_menu'],'">',$txt['ezp_edit'], '</a>
							&nbsp;
							<a href="',$scripturl,'?action=ezportal;sa=menudelete&id=',$menuRow['id_menu'],'">',$txt['ezp_delete'], '</a>
					</div>
				</div>';

			if ($styleClass == 'windowbg2')
				$styleClass = 'windowbg';
			else
				$styleClass = 'windowbg2';
		}

		echo '
			</div>
			<div class="centertext">
				<a href="', $scripturl, '?action=ezportal;sa=menuadd;block=',$context['ezp_block_info']['id_layout'],'">' . $txt['ezp_txt_menu_add'] . '</a></td>
			</div>';
	}


	// Now show all parameters for this ezBlock
	foreach ($context['ezp_block_parameters'] as $parameter)
	{
		if ($parameter['parameter_type'] == 'hidden')
			continue;

		echo '
			<dl class="settings">
				<dt>', $parameter['title'],($parameter['required'] == 1 ? '<span style="color: red;">*</span>' : ''),'</dt>
				<dd>';

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
				   	if (!function_exists('getLanguages'))
					{
					// Showing BBC?
						if ($context['show_bbc'])
						{
							echo '
														', template_control_richedit($context['post_box_name'], 'bbc'), '';
						}

						// What about smileys?
						if (!empty($context['smileys']['postform']))
							echo '
														', template_control_richedit($context['post_box_name'], 'smileys'), '';

						// Show BBC buttons, smileys and textbox.
						echo '
														', template_control_richedit($context['post_box_name'], 'message'), '';
					}
					else
					{
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
						echo '
									', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
						}
					}
					else
					{
						echo '<input type="text" name="parameter[', $parameter['id_parameter'],']" size="60" value="', $parameter['data'],'" />';
					}
			echo '
				</dd>
			</dl>';
	}


	echo '
		<p><b>', $txt['ezp_txt_permissions'],'</b></p>';

			$permissionsGroups = explode(',',$context['ezp_block_info']['permissions']);

	echo '
		<ul class="post_options">
			<li><input type="checkbox" name="groups[-1]" value="-1" ', ((in_array(-1,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_guests'],'</li>
			<li><input type="checkbox" name="groups[0]" value="0" ', ((in_array(0,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_members'],'</li>';

	foreach ($context['groups'] as $group)
		echo '
			<li><input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />', $group['group_name'], '</li>';


		echo '
			<li><input type="checkbox" id="checkAllGroups"  onclick="invertAll(this, this.form, \'groups\');" class="check" />',$txt['ezp_txt_checkall'],'</li>
		</ul>
		<p><b>', $txt['ezp_txt_block_managers'],'</b></p>
		<ul class="post_options">';

		$blockManagers = explode(',',$context['ezp_block_info']['blockmanagers']);

		foreach ($context['groups'] as $group)
		{
			if ($group['ID_GROUP'] == 1)
				continue;

		echo '
			<li><input type="checkbox" name="managers[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$blockManagers) == true) ? ' checked="checked" ' : ''), ' />', $group['group_name'], '</li>';

			}
		echo '
		</ul>
		<p><b>', $txt['ezp_txt_additional_settings'],'</b></p>
		<ul class="post_options">
			<li><input type="checkbox" name="can_collapse" ',($context['ezp_block_info']['can_collapse'] ? ' checked="checked" ' : ''), ' />',$txt['ezp_txt_can_collapse'],'</li>
			<li><input type="checkbox" name="hidetitlebar" ',($context['ezp_block_info']['hidetitlebar'] ? ' checked="checked" ' : ''), ' />',$txt['ezp_hidetitlebar'],'</li>
			<li><input type="checkbox" name="hidemobile" ',($context['ezp_block_info']['hidemobile'] ? ' checked="checked" ' : ''), ' />',$txt['ezp_hidemobile'],'</li>
			<li><input type="checkbox" name="showonlymobile" ',($context['ezp_block_info']['showonlymobile'] ? ' checked="checked" ' : ''), ' />',$txt['ezp_showonlymobile'],'</li>
			
		</ul>
		
		<dl>
			<dt>', $txt['ezp_txt_css_header_class'],'&nbsp;</dt>
	    	<dd><input type="text" name="block_header_class" size="60" value="',$context['ezp_block_info']['block_header_class'],'" /></dd>

			<dt>', $txt['ezp_txt_css_body_class'],'&nbsp;</dt>
			<dd><input type="text" name="block_body_class" size="60" value="',$context['ezp_block_info']['block_body_class'],'" /></dd>
	  </dl>	
		
		
		<p><b>', $txt['ezp_txt_visible_areas'],'</b></p>
		<ul class="post_options">
			<li><a href="', $scripturl, '?action=admin;area=ezpblocks;sa=visiblesettings;block=',$context['ezp_block_info']['id_layout'],'">',$txt['ezp_txt_update_visible_options'],'</a></li>
		</ul>
		<div class="floatright">
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="hidden" name="block" value="',$context['ezp_block_info']['id_layout'],'" />
			<input type="submit" name="editblock" value="',$txt['ezp_editblock'],'" />
		</div>
		</div>
		<span class="lowerframe"><span></span></span>
  	</form>';

}

function template_ezportal_delete_block()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">' . $txt['ezp_deleteblock'] . '</h3>
	</div>
	<form method="post" name="frmdelblock" id="frmdelblock" action="', $scripturl, '?action=ezportal;sa=deleteblock2" accept-charset="', $context['character_set'], '">
		<div class="windowbg2 centertext">
		<span class="topslice"><span></span></span>
			',$txt['ezp_deleteblock_confirm'],'<br />
			<b>',$context['ezp_block_layout_title'],'</b><br />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="hidden" name="blockid" value="',$context['ezp_block_layout_id'],'" />
			<input type="submit" name="delblock" value="',$txt['ezp_deleteblock'],'" />
		<span class="botslice"><span></span></span>
		</div>
  	</form>';
}

function template_ezportal_download_block()
{
	global $context, $txt, $scripturl;

	echo '
    <div class="cat_bar">
		<h3 class="catbg">' . $txt['ezp_txt_import_block'] . '</h3>
	</div>
	<div class="windowbg2">
	<span class="topslice"><span></span></span>
		<form class="padding" action="',$scripturl,'?action=ezportal;sa=importblock" method="post" accept-charset="', $context['character_set'], '" enctype="multipart/form-data" style="margin-bottom: 0;">
			<b>',$txt['ezp_txt_import_block_file'],'</b> <input type="file" name="blockfile" size="45" />
			<div class="floatright"><input type="submit" value="',$txt['ezp_txt_upload_block'],'" /></div>
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<h3>',$txt['ezp_txt_createezBlock'],'</h3>
			',$txt['ezp_txt_createezBlock_note'],'
		</form>
	<span class="botslice"><span></span></span>
	</div>
	<br class="clear" />';
}

function template_ezportal_installed_blocks()
{
	global $context, $txt, $scripturl;

	$styleclass = 'windowbg';
	echo '
	<div class="cat_bar">
		<h3 class="catbg">' . $txt['ezp_installed_blocks'] . '</h3>
	</div>
	<div class="ezTable">
		<div class="ezTRow titlebg">
			<div class="ezTCell ez25p">',$txt['ezp_txt_block_title'] ,'</div>
			<div class="ezTCell ez25p">',$txt['ezp_txt_block_version'] ,'</div>
			<div class="ezTCell ez25p">',$txt['ezp_txt_block_author'] ,'</div>
			<div class="ezTCell ez25p">',$txt['ezp_txt_options'],'</div>
		</div>';

	foreach($context['ezportal_installed_blocks']  as $row)
	{
		echo '
		<div class="ezTRow ',$styleclass,'">
			<div class="ezTCell ez25p">',$row['blocktitle'] ,'</div>
			<div class="ezTCell ez25p">',$row['blockversion'] ,'</div>
			<div class="ezTCell ez25p">', ($row['blockwebsite'] == '' ? $row['blockauthor'] : '<a href="' . $row['blockwebsite'] . '" target="_blank">' . $row['blockauthor'] . '</a>') ,'</div>
			<div class="ezTCell ez25p">';

			if ($row['no_delete'] == 0)
				echo '<a href="',$scripturl,'?action=admin;area=ezpblocks;sa=uninstallblock;block=' . $row['id_block'],';sesc=',$context['session_id'],'">',$txt['ezp_txt_uninstall_block'],'</a>';

		echo '
			</div>
		</div>';

					if ($styleclass == 'windowbg')
						$styleclass = 'windowbg2';
					else
						$styleclass = 'windowbg';
	}

	echo '
	</div>';
}

function template_ezblock_above()
{
	global $context, $scripturl, $settings;

	if (empty($context['ezPortalColumns']))
		return false;

	$context['ezportal_center_open'] = 0;

	// Table starts here
	$columnToggleText = '';
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Top')
		{

			if (count($ezColumn['blocks']) == 0)
				continue;

			echo '<!--start column ' . $ezColumn['column_title'] . '-->';
			if ($ezColumn['can_collapse'] == 1)
		      	 	echo '<span class="toggle_icon"><img src="' . $settings['images_url'] . '/' .( $ezColumn['IsCollapsed'] ? 'expand.gif' : 'collapse.gif' ). '" onclick="javacscript:EzToogle(\'column',$ezColumn['id_column'],'\',',$ezColumn['id_column'],',this,0)" alt="+-" /></span>';

				if (empty($ezColumn['column_percent']))
					echo '<div style="width:',$ezColumn['column_width'], 'px;" align="center"  id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
				else
					echo '<div style="width:',$ezColumn['column_percent'], '%;" align="center"  id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';

				if (!empty($ezColumn['sticky']))
					echo '<span style="position: sticky; top: 0;">';

				// Show Collapse icon if allowed for column if allowed
				ShowEzBlocks($ezColumn);

				if (!empty($ezColumn['sticky']))
					echo '</span>';

					echo '</div><!--end column ' . $ezColumn['column_title'] . '-->';
		}

		// Column can collapse

		if ($ezColumn['column_title'] == 'Left' || $ezColumn['column_title'] == 'Right')
		{

			if (count($ezColumn['blocks']) == 0)
				continue;

			if ($ezColumn['can_collapse'] == 1)
			{
				$columnToggleText .= '<img src="' . $settings['images_url'] . '/' .( $ezColumn['IsCollapsed'] ? 'expand.gif' : 'collapse.gif' ). '" onclick="javacscript:EzToogle(\'column' . $ezColumn['id_column'] .'\',' . $ezColumn['id_column'] .',this,0)" alt="+-" />';
			}
		}



	}
	// End Top Blocks
		if (!empty($columnToggleText))
		{
			echo '<span class="toggle_icon">' . $columnToggleText . '</span>';
		}
	$center = 0;
	$right = 0;
	$left = 0;

	foreach($context['ezPortalColumns'] AS $ezColumn)
	{

		if ($ezColumn['column_title'] == 'Top' || $ezColumn['column_title'] == 'Bottom')
			continue;

		if (count($ezColumn['blocks']) == 0)
			continue;


	}
	$context['ezportal_center_open'] = 0;
	// Start the container table
	echo '<div class="ezTable"><div class="ezTRow">';
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Right' || $ezColumn['column_title'] == 'Top' || $ezColumn['column_title'] == 'Bottom')
			continue;

		//if (count($ezColumn['blocks']) == 0 && $ezColumn['column_title'] != 'Center')
		//	continue;
		if (count($ezColumn['blocks']) == 0)
			continue;

		echo '<!--start column ' . $ezColumn['column_title'] . '-->';



		if (empty($ezColumn['column_percent']))
			echo '<div style="width:',$ezColumn['column_width'], 'px;" id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
		else
			echo '<div style="width: ',$ezColumn['column_percent'], '%;" id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';


		if (!empty($ezColumn['sticky']))
			echo '<span style="position: sticky; top: 0;">';
		// Show Collapse icon if allowed for column if allowed
		ShowEzBlocks($ezColumn);


				if (!empty($ezColumn['sticky']))
					echo '</span>';

		if ($ezColumn['column_title'] != 'Center')
		{
			echo '</div><!-- end not center -->';
		}
		else
		{
			//$context['ezportal_center_open'] = 1;

		}



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

	/* Needs attention of vBGamer
	if ($onlyCenter == true && $tdopen == 1)
	{
		echo '</div>';
		$shownHeader  = false;
	}*/

	if (($right == 1 || $left == 1 || $center == 1))
	{
		if (empty($context['ezportal_center_open']))
		echo '
			<div id="ezContainer">';
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
			$bodyClass = 'windowbg2';

			if (!empty($ezBlock['block_header_class']))
				$headerClass = $ezBlock['block_header_class'];

			if (!empty($ezBlock['bodyClass']))
				$bodyClass = $ezBlock['block_body_class'];

		if (empty($ezBlock['hidetitlebar']))
		{
		echo '
			<div class="cat_bar">
				<h3 class="' .$headerClass . '">';

					// Show icon
					if (!empty($ezBlock['icon']))
						echo '<img src="',$ezpSettings['ezp_url'],"icons/" . $ezBlock['icon'] . '" alt="" /> ';

					// Show title of ezBlock
					echo $ezBlock['customtitle'];

					if (($context['ezportal_block_manager'] == 1 || $ezBlock['IsManager'] == 1) && empty($ezpSettings['ezp_hide_edit_delete']))
					{
						echo '
						<a href="',$scripturl,'?action=ezportal;sa=editblock;block=',$ezBlock['id_layout'],'"><img src="',$ezpSettings['ezp_url'],'icons/page_white_edit.png" alt="',$txt['ezp_edit2'], '" /></a>
						&nbsp;
						<a href="',$scripturl,'?action=ezportal;sa=deleteblock;block=',$ezBlock['id_layout'],'"><img src="',$ezpSettings['ezp_url'],'icons/plugin_delete.png" alt="',$txt['ezp_delete2'], '" /></a>';
					}

					// Check if they can collapse the ezBlock
					if ($ezBlock['can_collapse'])
						echo '<img class="floatright collapse_fix" src="' . $settings['images_url'] . '/' .( $ezBlock['IsCollapsed'] ? 'expand.gif' : 'collapse.gif' ). '" onclick="javacscript:EzToogle(\'block',$ezBlock['id_layout'],'\',',$ezBlock['id_layout'],',this,1)" alt="+-" /></span>';

		echo '
				</h3>
			</div>';
		}

		echo '
			<div class="' . $bodyClass .' padding" id="block',$ezBlock['id_layout'],'" ',($ezBlock['IsCollapsed'] ? 'style="display:none"' : ''),'>';

			// Check the ezblock type
			if (strtolower($ezBlock['blocktype']) == 'html')
			{
					global $scripturl, $user_info;
					$ezBlock['blockdata'] = str_replace('{$member.id}',$user_info['id'], $ezBlock['blockdata']);
					$ezBlock['blockdata'] = str_replace('{$member.name}',$user_info['name'], $ezBlock['blockdata']);
					$ezBlock['blockdata'] = str_replace('{$member.email}',$user_info['email'], $ezBlock['blockdata']);
					$ezBlock['blockdata'] = str_replace('{$member.link}','<a href="' . $scripturl . '?action=profile;u=' . $user_info['id'] . '">' . $user_info['name'] . '</a>', $ezBlock['blockdata']);



				echo html_entity_decode($ezBlock['blockdata'], ENT_QUOTES);

			}
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
			</div><!-- end block-->';
	}
}

function template_ezblock_below()
{
	global $context, $settings;

	if (empty($context['ezPortalColumns']))
		return false;


	$center = 0;
	$right = 0;
	$left = 0;

	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Left')
			$left = 1;
		if ($ezColumn['column_title'] == 'Center' )
			$center = 1;
		if ($ezColumn['column_title'] == 'Right')
			$right = 1;
	}



	if (($right == 1 || $left == 1 || $center == 1))
	{
		echo '
			</div><!-- ezc center-->';

		if ($center)
			echo '
			</div><!-- ezc center2-->';
	}



	$count = 0;
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Left' || $ezColumn['column_title'] == 'Center' )
		{
			$count++;
			if ($ezColumn['column_title'] == 'Center')
			{
				/*
				if (empty($context['ezportal_center_open']))
					echo '</div><!-- ezc center-->';
				else
					echo '</div>';
				*/
			}
		}
	}

	//if(empty($context['ezPortal']))
		//echo '</div>';

	//if ($count != 0)


	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] != 'Right')
			continue;

		if (count($ezColumn['blocks']) == 0)
			continue;

	echo '<!--start column ' . $ezColumn['column_title'] . '-->';

		if (empty($ezColumn['column_percent']))
			echo '<div style="width:',$ezColumn['column_width'], 'px;" id="column',$ezColumn['id_column'],'" ',($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';
		else
			echo '<div style="width: ',$ezColumn['column_percent'], '%;"  id="column',$ezColumn['id_column'],'" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''),'>';


		if (!empty($ezColumn['sticky']))
			echo '<span style="position: sticky; top: 0;">';
		// Show Collapse icon if allowed
		ShowEzBlocks($ezColumn);


		if (!empty($ezColumn['sticky']))
			echo '</span>';

		echo '</div><!-- end not right -->';
		echo '<!--start column ' . $ezColumn['column_title'] . '-->';
	}
	// End the container table
	echo '<!-- ezp end divs--></div></div><!-- ezp end divs-->';
	// Bottom Blocks
	foreach($context['ezPortalColumns'] AS $ezColumn)
	{
		if ($ezColumn['column_title'] == 'Bottom')
		{
			if (count($ezColumn['blocks']) == 0)
				continue;

			echo '<br class="clear" /><!--start column ' . $ezColumn['column_title'] . '-->';

			if ($ezColumn['can_collapse'] == 1)
				echo '<img class="toggle_icon" src="' . $settings['images_url'] . '/' . ($ezColumn['IsCollapsed'] ? 'expand.gif' : 'collapse.gif') . '" onclick="javacscript:EzToogle(\'column', $ezColumn['id_column'], '\',', $ezColumn['id_column'], ',this,0)" alt="+-" />';

			if (empty($ezColumn['column_percent']))
				echo '<div style="width:', $ezColumn['column_width'], 'px;" align="center"  id="column', $ezColumn['id_column'], '" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''), '>';
			else
				echo '<div style="width: ', $ezColumn['column_percent'], '%;" align="center"  id="column', $ezColumn['id_column'], '" ', ($ezColumn['can_collapse'] ? ($ezColumn['IsCollapsed'] ? 'style="display:none"' : '') : ''), '>';



			if (!empty($ezColumn['sticky']))
					echo '<span style="position: sticky; top: 0;">';
			// Show Collapse icon if allowed for column if allowed
			ShowEzBlocks($ezColumn);


			if (!empty($ezColumn['sticky']))
				echo '</span>';


			echo '</div>';

			echo '<!-- end bottom -->';
			echo '<!--end column ' . $ezColumn['column_title'] . '-->';
		}

	}

	// Show EzPortal Copyright

	// DO NOT MODIFY OR REMOVE THIS COPYRIGHT UNLESS THE BRANDING FREE OPTION HAS BEEN PURCHASED
	// http://www.ezportal.com/copyright_removal.php
    
    $showInfo = EzPortalCheckInfo();
    
    if ($showInfo == true)
	   echo '<br class="clear" /><div align="center" class="smalltext">Powered by <a href="https://www.ezportal.com" target="blank">EzPortal</a></div>';
}

function template_ezportal_frontpage()
{
	// Empty place holder just for the frontpage.
}

function template_ezportal_visible_options()
{
	global $txt, $context, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">' . $txt['ezp_txt_visible_areas'] . '</h3>
	</div>
	<div class="windowbg2">
	<span class="topslice"><span></span></span>
		<form class="padding" method="post" name="frmvisoption" id="frmvisoption" action="', $scripturl, '?action=ezportal;sa=visiblesettings2" accept-charset="', $context['character_set'], '">
			<div class="centertext">
				<input type="checkbox" name="all" value="all" ',($context['ezp_all'] ? ' checked="checked" ' : ''),' />',$txt['ezp_txt_visible_all'],'<br />
				<input type="checkbox" name="cus[forum]" value="forum" ',(in_array('forum',$context['ezp_visibleCustom']) ? ' checked="checked"' : '' ) ,'  />',$txt['ezp_txt_visible_forum'],'<br />
				<input type="checkbox" name="cus[boardindex]" value="boardindex"  ',(in_array('boardindex',$context['ezp_visibleCustom']) ? ' checked="checked"' : '' ) ,' />',$txt['ezp_txt_visible_board_index']  ,'<br />
				<input type="checkbox" name="cus[portal]" value="portal"  ',(in_array('portal',$context['ezp_visibleCustom']) ? ' checked="checked"' : '' ) ,' />',$txt['ezp_txt_visible_homepage'] ,'<br />
			</div>
			<ul class="ignoreboards floatleft">
				<li><b>', $txt['ezp_txt_visible_actions'],'</b></li>';

			foreach ($context['ezportal_actions'] as $ezpActions)
			{
				if ($ezpActions['is_mod'] == 0)
					echo '
				<li class="board"><input type="checkbox" name="visactions[', $ezpActions['action'], ']" value="', $ezpActions['action'], '" ' . ($ezpActions['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpActions['title'], '</li>';
			}

	echo '
			</ul>
			<ul class="ignoreboards floatleft">
				<li><b>', $txt['ezp_txt_visible_actions_modifcations'] ,'</b><br />
				<span class="smalltext">',$txt['ezp_txt_visible_actions_modifcations_note'],'</span></li>';

		foreach ($context['ezportal_actions'] as $ezpActions)
		{
			if ($ezpActions['is_mod'] == 1)
				echo '
				<li class="board"><input type="checkbox" name="visactions[', $ezpActions['action'], ']" value="', $ezpActions['action'], '" ' . ($ezpActions['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpActions['title'], ' <a href="', $scripturl, '?action=admin;area=ezpblocks;sa=deletevisibleaction;newaction=', $ezpActions['action'], '">',$txt['ezp_delete'],'</a></li>';
		}

	echo '
				<li class="board"><a href="', $scripturl, '?action=admin;area=ezpblocks;sa=addvisibleaction">',$txt['ezp_txt_visible_add_new_action'],'</a></li>
			</ul>
			<ul class="ignoreboards floatleft">
				<li><b>',$txt['ezp_txt_visible_pages'],'</b></li>';

		foreach ($context['ezportal_pages'] as $ezpPages)
		{
				echo '
				<li class="board"><input type="checkbox" name="vispages[', $ezpPages['id_page'], ']" value="', $ezpPages['id_page'], '" ' . ($ezpPages['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpPages['title'], '</li>';
		}

	echo '
			</ul>
			<ul class="ignoreboards floatleft">
				<li><b>', $txt['ezp_txt_visible_boards'],'</b></li>';

		foreach ($context['ezportal_boards'] as $ezpBoards)
			echo '
				<li class="board"><input type="checkbox" name="visboards[',$ezpBoards['ID_BOARD'], ']" value="', $ezpBoards['ID_BOARD'], '" ' . ($ezpBoards['selected'] ? ' checked="checked"' : '' ) .  ' />', $ezpBoards['bName'], '</li>';

	echo '
			</ul>
			<br class="clear" />
			<div class="floatright">
				<input type="hidden" name="block" value="', $context['ezportal_block'], '" />
				<input type="hidden" name="column" value="', $context['ezportal_column'], '" />
				<input type="submit" name="addblock" value="', $txt['ezp_txt_savevisible'], '" />
			</div>
			<br class="clear" />
		</form>
	<span class="botslice"><span></span></span>
	</div>
	<br class="clear" />';
}

function template_ezportal_add_visible_action()
{
	global $txt, $scripturl, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">' . $txt['ezp_txt_visible_add_new_action'] . '</h3>
	</div>
	<div class="windowbg2">
	<span class="topslice"><span></span></span>
		<form class="padding" method="post" name="addaction" id="addaction" action="', $scripturl, '?action=ezportal;sa=addvisibleaction2" accept-charset="', $context['character_set'], '">
			<dl class="settings">
				<dt>', $txt['ezp_txt_visible_add_new_action_title'],'</dt>
				<dd><input type="text" name="actiontitle" size="50" value="" /></dd>
				<dt>', $txt['ezp_txt_visible_add_new_action_action'],'</dt>
				<dd><input type="text" name="newaction" size="50" value="" /></dd>
			</dl>
			<span class="smalltext">' . $txt['ezp_txt_visible_add_new_action_note'] . '</span>
			<div class="floatright">
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				<input type="submit" name="addaction" value="',$txt['ezp_txt_visible_add_new_action'],'" />
			</div>
			<br class="clear" />
		</form>
	<span class="botslice"><span></span></span>
	</div>
	<br class="clear" />';
}

function template_ezportal_delete_shoutboxhistory()
{
	global $txt, $scripturl, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">' . $txt['ezp_txt_deleteallshoutbox'] . '</h3>
	</div>
	<div class="windowbg2">
	<span class="topslice"><span></span></span>
		<form class="padding" method="post" name="addaction" id="addaction" action="', $scripturl, '?action=ezportal;sa=deleteshouthistory2">
			<p class="centertext"><b>', $txt['ezp_txt_deleteallshoutbox_confirm'],'</b></p>
			<div class="floatright padding">
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
				<input type="submit" value="',$txt['ezp_txt_deleteallshoutbox'],'" />
			</div>
			<br class="clear" />
		</form>
	<span class="botslice"><span></span></span>
	</div>';
}

function template_ezportal_shoutbox_history()
{
	global $txt, $context, $ezpSettings, $scripturl;

	$adminShoutBox = allowedTo('admin_forum');
	$styleClass = "windowbg";

	echo '
		<div class="cat_bar">
			<h3 class="catbg">',$txt['ezp_txt_shouthistory'],'</h3>
		</div>
		<div class="ezTable">';

	foreach($context['ezshouts_history'] as $row)
	{
		echo '
			<div class="ezTRow ',$styleClass,'">';
		// Censor the shout
		censorText($row['shout']);

		if ($ezpSettings['ezp_shoutbox_showdate'])
			echo '
				<div class="ezTCell ez_equal">
					', timeformat($row['date']) ,'
				</div>';

		echo '
				<div class="ezTCell ez10p">
					<a href="',$scripturl,'?action=profile;u=',$row['id_member'],'" style="color: ' . $row['online_color'] . ';">',$row['real_name'],'</a>
				</div>';

		echo '
				<div class="ezTCell ez50p">
					', $txt['ezp_shoutbox_says'] . parse_bbc($row['shout']),'
				</div>';

		if ($adminShoutBox)
			echo '
				<div class="ezTCell ez5p">
					<a href="',$scripturl,'?action=ezportal;sa=removeshout;shout=',$row['id_shout'],'" style="color: #FF0000">[X]</a>
				</div>';

		echo '
			</div>';


		if ($styleClass == 'windowbg')
			$styleClass = 'windowbg2';
		else
			$styleClass = 'windowbg';
	}

	echo '
		</div>
		<div class="pagelinks">
			',$txt['ezp_txt_pages'],$context['page_index'] ,'
		</div>';
    
    if ($adminShoutBox)
        echo '<a href="',$scripturl,'?action=ezportal;sa=deleteshouthistory">' . $txt['ezp_txt_deleteallshoutbox'] . '</a>';
}

function template_ezportal_menu_add()
{
	global $txt, $scripturl, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">' . $txt['ezp_txt_menu_add'] . '</h3>
	</div>
	<div class="windowbg2">
	<span class="topslice"><span></span></span>
	<form class="padding" method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=menuadd2" accept-charset="', $context['character_set'], '">
		<dl class="settings">
			<dt>', $txt['ezp_txt_menu_title'],'</dt>
			<dd><input type="text" name="menutitle" size="60" value="" /></dd>
	  	    <dt>', $txt['ezp_txt_menu_link'],'</dt>
	  	    <dd><input type="text" name="menulink" size="60" value="" /></dd>
	  	    <dt>', $txt['ezp_txt_menu_newwindow'] ,'</b>&nbsp;</dt>
	  	    <dd><input type="checkbox" name="newwindow" /></dd>
		</dl>
		<b>', $txt['ezp_txt_permissions'],'</b>
		<ul class="post_options">
			<li><input type="checkbox" name="groups[-1]" value="-1" checked="checked" />',$txt['membergroups_guests'],'</li>
			<li><input type="checkbox" name="groups[0]" value="0" checked="checked" />',$txt['membergroups_members'],'</li>';

	foreach ($context['groups'] as $group)
		echo '
			<li><input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" checked="checked" />', $group['group_name'], '</li>';

	echo '
		</ul>
		<div class="floatright">
			<input type="hidden" name="layoutid" value="', $context['ezp_layout_id'], '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="submit" name="addblock" value="',$txt['ezp_txt_menu_add'],'" />
		</div>
		<br class="clear" />
  	</form>
	<span class="botslice"><span></span></span>
	</div>';
}

function template_ezportal_menu_edit()
{
	global $txt, $scripturl, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">' . $txt['ezp_txt_menu_edit'] . '</h3>
	</div>
	<div class="windowbg2">
	<span class="topslice"><span></span></span>
	<form class="padding" method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=menuedit2" accept-charset="', $context['character_set'], '">
		<dl class="settings">
			<dt>', $txt['ezp_txt_menu_title'],'</dt>
			<dd><input type="text" name="menutitle" size="60" value="' .  $context['ezp_menu_row']['title'] . '" /></dd>
			<dt>', $txt['ezp_txt_menu_enabled'] ,'</dt>
			<dd><input type="checkbox" name="menuenabled" ' .  ($context['ezp_menu_row']['enabled'] ? ' checked="checked"' : '') . ' /></dd>
			<dt>', $txt['ezp_txt_menu_link'],'</dt>
			<dd><input type="text" name="menulink" size="60" value="' .  $context['ezp_menu_row']['linkurl'] . '" /></dd>
			<dt>', $txt['ezp_txt_menu_newwindow'] ,'</dt>
			<dd><input type="checkbox" name="newwindow" ' .  ($context['ezp_menu_row']['newwindow'] ? ' checked="checked"' : '') . ' /></dd>
		</dl>
		<b>', $txt['ezp_txt_permissions'],'</b>
		<ul class="post_options">';

		   $permissionsGroups = explode(',',$context['ezp_menu_row']['permissions']);

		echo '
			<li><input type="checkbox" name="groups[-1]" value="-1" ', ((in_array(-1,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_guests'],'</li>
			<li><input type="checkbox" name="groups[0]" value="0" ', ((in_array(0,$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />',$txt['membergroups_members'],'</li>';

	foreach ($context['groups'] as $group)
		echo '
			<li><input type="checkbox" name="groups[', $group['ID_GROUP'], ']" value="', $group['ID_GROUP'], '" ', ((in_array($group['ID_GROUP'],$permissionsGroups) == true) ? ' checked="checked" ' : ''), ' />', $group['group_name'], '</li>';

	echo '
		</ul>
		<div class="floatright">
			<input type="hidden" name="layoutid" value="', $context['ezp_menu_row']['id_layout'], '" />
			<input type="hidden" name="menuid" value="', $context['ezp_menu_row']['id_menu'], '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="submit" name="editmenu" value="',$txt['ezp_txt_menu_edit'],'" />
		</div>
		<br class="clear" />
  	</form>
	<span class="botslice"><span></span></span>
	</div>';
}

function template_ezportal_menu_delete()
{
	global $txt, $scripturl, $context;

	echo '
    <div class="cat_bar">
		<h3 class="catbg centertext">' . $txt['ezp_txt_menu_delete'] . '</h3>
	</div>
	<div class="windowbg2">
	<span class="topslice"><span></span></span>
	<form class="padding" method="post" name="frmaddblock" id="frmaddblock" action="', $scripturl, '?action=ezportal;sa=menudelete2" accept-charset="', $context['character_set'], '">
		<div class="centertext">
	  	 	', $context['ezp_menu_row']['title'], '<br />
	  	 	', $context['ezp_menu_row']['linkurl'], '
		</div>
		<div class="floatright">
			<input type="hidden" name="layoutid" value="', $context['ezp_menu_row']['id_layout'], '" />
			<input type="hidden" name="menuid" value="', $context['ezp_menu_row']['id_menu'], '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="submit" name="editmenu" value="',$txt['ezp_txt_menu_delete'],'" />
		</div>
		<br class="clear" />
  	</form>
	<span class="botslice"><span></span></span>
	</div>';
}

function template_ezpotal_shoutbox()
{
	global $settings, $context;
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index.css" />';

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
	<div class="cat_bar">
		<h3 class="catbg">', $txt['ezp_txt_copyrightremoval'], '</h3>
	</div>
	<div class="windowbg2">
	<span class="topslice"><span></span></span>
	<form class="padding" method="post" action="',$scripturl,'?action=ezportal;sa=copyright;save=1">
		<dl class="settings">
			<dt>',$txt['ezp_txt_copyrightkey'],' (<a href="https://www.smfhacks.com/copyright_removal.php?mod=' . $modID .  '&board=' . $urlBoardurl . '" target="_blank">' . $txt['ezp_txt_ordercopyright'] . '</a>)</dt>
			<dd><input type="text" name="ezp_copyrightkey" size="50" value="' . $modSettings['ezp_copyrightkey'] . '" /></dd>
		</dl>
		<p>' . $txt['ezp_txt_copyremovalnote'] . '</p>
		<div class="floatright">		
			<input type="submit" value="' . $txt['ezp_savesettings'] . '" />
		</div>
		<br class="clear" />
	</form>
	<span class="botslice"><span></span></span>
	</div>';
}
?>