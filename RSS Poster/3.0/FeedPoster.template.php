<?php
/*
RSS Feed Poster
Version 4.2
by:vbgamer45
http://www.smfhacks.com
*/

function template_main()
{
	global $scripturl, $txt, $context, $modSettings, $boardurl;

	// Displays the Current Feed Bots
	echo '
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['feedposter_feedlist'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
				<form action="',$scripturl,'?action=feedsadmin;sa=bulkactions" method="post">
				<table width="95%" cellspacing="0" align="center" cellpadding="4" class="tborder">
					<tr class="titlebg">
						<td></td>
						<td>', $txt['feedposter_feedtitle'], '</td>
						<td>', $txt['feedposter_feedurl'], '</td>
						<td>', $txt['feedposter_postername'], '</td>
						<td>', $txt['feedposter_total_posts'], '</td>
						<td>', $txt['feedposter_feedstatus'], '</td>
						<td>', $txt['feedposter_nextupdatetime']. '</td>
						<td>', $txt['feedposter_options'], '</td>
					</tr>';
	
					$styleClass = 'windowbg';
	
					foreach ($context['feeds'] as $key => $feed)
					{
						echo '<tr class="' . $styleClass . '">
								<td><input type="checkbox" name="feed[', $feed['ID_FEED'], ']" value="', $feed['ID_FEED'], '" /></td>
								<td><a href="', $scripturl, '?action=feedsadmin;sa=editfeed;id=', $feed['ID_FEED'], '">', $feed['title'], '</a></td> 
								<td><a href="', $feed['feedurl'], '" target="_blank">', $feed['feedurl'], '</a></td> 
								<td>', $feed['postername'], '</td>
								<td align="center">',  number_format($feed['total_posts']), '</td>
								<td>', ($feed['enabled'] ? $txt['feedposter_enabled'] : $txt['feedposter_disabled']), '</td>
								<td>', (empty($feed['updatetime']) ? '' : timeformat($feed['updatetime'])), '</td>
								<td><a href="', $scripturl, '?action=feedsadmin;sa=editfeed;id=', $feed['ID_FEED'], '">', $txt['feedposter_editfeed'], '</a></td> 
							</tr>
						';
						
						if ($styleClass == 'windowbg')
							$styleClass = 'windowbg2';
						else 
							$styleClass = 'windowbg';
					}
	
	echo '<tr class="windowbg">
			<td colspan="7">' .$txt['feedposter_withselected']  . '<select name="bulk">
			<option value=""></option>
			<option value="delete">' .$txt['feedposter_delete']  . '</option>
			<option value="enablefeed">' .$txt['feedposter_enable_feed']  . '</option>
			<option value="disablefeed">' .$txt['feedposter_disable_feed']  . '</option>
			<option value="runnow">' .$txt['feedposter_runnow']  . '</option>
			</select> <input type="submit" value="' . $txt['feedposter_go'] . '">
			</td>
		 </tr>	
	<tr class="windowbg">
					<td colspan="7" align="center"><a href="', $scripturl, '?action=feedsadmin;sa=addfeed">', $txt['feedposter_addfeed'], '</a></td>
				</tr>
				</table>
				</form>
				<br />
				', $txt['feedposter_cronjoburl'], '&nbsp;<b>', $boardurl, '/cronrss.php</b><br />
				
				<br />
				<b>', $txt['feedposter_settings'], '</b><br />
				<form method="post" action="', $scripturl, '?action=feedsadmin;sa=saveset">
				<input type="checkbox" name="rss_fakecron" ', ($modSettings['rss_fakecron'] == 1 ? ' checked="checked" ' : ''), ' />', $txt['feedposter_rss_fakecron'], '<br />
                <input type="checkbox" name="rss_embedimages" ', ($modSettings['rss_embedimages'] == 1 ? ' checked="checked" ' : ''), ' />', $txt['rss_embedimages'], '<br />
                <input type="checkbox" name="rss_usedescription" ', ($modSettings['rss_usedescription'] == 1 ? ' checked="checked" ' : ''), ' />', $txt['rss_usedescription'], '<br />
				
				<br />
				',$txt['feedposter_rssdownloadmethod'],'
				<select name="rss_feedmethod">
					<option value="', $modSettings['rss_feedmethod'] ,'" selected="selected">', $modSettings['rss_feedmethod'] ,'</option>
					<option value="curl">curl</option>
					<option value="All">All</option>
					<option value="fopen">fopen</option>
					<option value="fsockopen">fsockopen</option>
					
				</select><br />
				<input type="submit" value="', $txt['feedposter_savesettings'], '" />
				</form>
				<br />
				<b>Has RSS Feed Poster helped you?</b> Then support the developers:<br />
				    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="business" value="sales@visualbasiczone.com">
					<input type="hidden" name="item_name" value="RSS Feed Poster">
					<input type="hidden" name="no_shipping" value="1">
					<input type="hidden" name="no_note" value="1">
					<input type="hidden" name="currency_code" value="USD">
					<input type="hidden" name="tax" value="0">
					<input type="hidden" name="bn" value="PP-DonationsBF">
					<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
					<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
				</form>
				<br />
				<b>Contributors</b><br />
				Version 3.0 paid for by <a href="http://www.simplemachines.org/community/index.php?action=profile;u=42226">MoreBloodWine</a>
				
				

			</td>
		</tr>
</table>';

	// The Copyright is required to remain or contact me to purchase link removal.
	echo '<br /><div align="center"><a href="http://www.smfhacks.com" target="blank">RSS Feed Poster</a></div>';

}

function template_addfeed()
{
	global $context, $txt, $scripturl, $settings;
	
echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['feedposter_addfeed'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
			<form method="post" action="', $scripturl, '?action=feedsadmin;sa=addfeed2" name="frmfeed">
				<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4">
				<tr><td width="30%">', $txt['feedposter_feedtitle'], '</td><td><input type="text" name="feedposter_feedtitle" size="50" /></td></tr>
				<tr><td width="30%">', $txt['feedposter_feedurl'], '</td><td><input type="text" name="feedposter_feedurl" size="50" /></td></tr>
				
				<tr><td width="30%">', $txt['feedposter_forum'], '</td><td>
				<select name="boardselect" id="boardselect">
  ';

	foreach ($context['feed_boards'] as $key => $option)
		 echo '<option value="', $key, '">', $option, '</option>';

echo '</select></td></tr>


<tr><td width="30%">', $txt['feedposter_msg_icon'], '</td><td>
				<select name="msgicon" id="msgicon" onchange="ChangeIconPic(this.value)">
  ';

	foreach ($context['msg_icons'] as $key => $option)
		 echo '<option value="', $option['filename'], '">', $option['title'], '</option>';

echo '</select> <img id="iconPick" src="" alter="" />

		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	// iconSelect
	var selectIcons = new Array();
	';

foreach ($context['msg_icons'] as $key => $option)
{
	echo ' selectIcons["', $option['filename'], '"] = "', ($settings[file_exists($settings['theme_dir'] . '/images/post/' . $option['filename'] . '.gif') ? 'actual_images_url' : 'default_images_url'] . '/post/' . $option['filename'] . '.gif') . '";';
	

}

echo '
	 

	function ChangeIconPic(iconIndex)
	{
		document.frmfeed.iconPick.src = selectIcons[iconIndex];
	}
	
	ChangeIconPic("xx");
	
	// ]]></script>



</td></tr>

				<tr><td width="30%">', $txt['feedposter_postername'], '</td><td><input type="text" name="feedposter_postername" id="feedposter_postername" /><a href="', $scripturl, '?action=findmember;input=feedposter_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a> <a href="', $scripturl, '?action=findmember;input=feedposter_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a></td></tr>
				<tr><td width="30%">', $txt['feedposter_topicprefix'], '</td><td><input type="text" name="feedposter_topicprefix" /></td></tr>
				<tr><td width="30%">', $txt['feedposter_importevery'], '</td><td><input type="text" name="feedposter_importevery" value="360" /></td></tr>
				<tr><td width="30%">', $txt['feedposter_numbertoimport'], '</td><td><input type="text" name="feedposter_numbertoimport" value="1" /></td></tr>
				<tr><td width="30%">', $txt['feedposter_topicid'], '</td><td><input type="text" name="topicid" value="0" /></td></tr>
				<tr><td width="30%">', $txt['feedposter_footer'], '</td><td><textarea name="footer" rows="3" cols="50"></textarea></td></tr>
				
				
				<tr><td width="30%" align="right"><input type="checkbox" name="feedposter_feedenabled" checked="checked" /></td><td>', $txt['feedposter_feedenabled'], '</td></tr>
				<tr><td width="30%" align="right"><input type="checkbox" name="feedposter_htmlenabled" checked="checked" /></td><td>', $txt['feedposter_htmlenabled'], '</td></tr>
				<tr><td width="30%" align="right"><input type="checkbox" name="feedposter_topiclocked" /></td><td>', $txt['feedposter_topiclocked'], '</td></tr>

				<tr>
				<td colspan="2" align="center">
				<input type="submit" name="addfeed" value="', $txt['feedposter_addfeed'],  '" />
				</td>
				</tr>
				</table>
			</form>
			
			<br />
			<b>Has RSS Feed Poster helped you?</b> Then support the developers:<br />
			    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="sales@visualbasiczone.com">
				<input type="hidden" name="item_name" value="RSS Feed Poster">
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

	// The Copyright is required to remain or contact me to purchase link removal.
	echo '<br /><div align="center"><a href="http://www.smfhacks.com" target="blank">RSS Feed Poster</a></div>';

}

function template_editfeed()
{
	global $context, $txt, $scripturl, $settings;
	
echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['feedposter_editfeed'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
			<form method="post" action="', $scripturl, '?action=feedsadmin;sa=editfeed2" name="frmfeed">
				<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4">
				<tr><td width="30%">', $txt['feedposter_feedtitle'], '</td><td><input type="text" name="feedposter_feedtitle" size="50" value="', $context['feed']['title'], '" /></td></tr>
				<tr><td width="30%">', $txt['feedposter_feedurl'], '</td><td><input type="text" name="feedposter_feedurl" size="50" value="', $context['feed']['feedurl'], '" /></td></tr>
				
				<tr><td width="30%">', $txt['feedposter_forum'], '</td><td>
				<select name="boardselect" id="boardselect">
  ';

	foreach ($context['feed_boards'] as $key => $option)
		 echo '<option value="', $key, ' " ', ($key == $context['feed']['ID_BOARD'] ? ' selected="selected" ' : ''), '>', $option, '</option>';

echo '</select></td></tr>


<tr><td width="30%">', $txt['feedposter_msg_icon'], '</td><td>
				<select name="msgicon" id="msgicon"  onchange="ChangeIconPic(this.value)">
  ';

	foreach ($context['msg_icons'] as $key => $option)
		 echo '<option value="', $option['filename'], '"' . ($context['feed']['msgicon'] == $option['filename'] ? ' selected="selected"' : '') . '>', $option['title'], '</option>';

echo '</select>
<img id="iconPick" src="" alter="" />

		<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	// iconSelect
	var selectIcons = new Array();
	';

foreach ($context['msg_icons'] as $key => $option)
{
	echo ' selectIcons["', $option['filename'], '"] = "', ($settings[file_exists($settings['theme_dir'] . '/images/post/' . $option['filename'] . '.gif') ? 'actual_images_url' : 'default_images_url'] . '/post/' . $option['filename'] . '.gif') . '";';
	

}

echo '
	 

	function ChangeIconPic(iconIndex)
	{
		document.frmfeed.iconPick.src = selectIcons[iconIndex];
	}
	
	ChangeIconPic("' . $context['feed']['msgicon'] . '");
	
	// ]]></script>


</td></tr>
				<tr><td width="30%">', $txt['feedposter_postername'], '</td><td><input type="text" name="feedposter_postername" id="feedposter_postername" value="', $context['feed']['postername'], '" /><a href="', $scripturl, '?action=findmember;input=feedposter_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a> <a href="', $scripturl, '?action=findmember;input=feedposter_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a></td></tr>
				<tr><td width="30%">', $txt['feedposter_topicprefix'], '</td><td><input type="text" name="feedposter_topicprefix" value="', $context['feed']['topicprefix'], '" /></td></tr>
				<tr><td width="30%">', $txt['feedposter_importevery'], '</td><td><input type="text" name="feedposter_importevery"  value="', $context['feed']['importevery'], '" /></td></tr>
				<tr><td width="30%">', $txt['feedposter_numbertoimport'], '</td><td><input type="text" name="feedposter_numbertoimport" value="', $context['feed']['numbertoimport'], '" /></td></tr>
				<tr><td width="30%">', $txt['feedposter_topicid'], '</td><td><input type="text" name="topicid" value="', $context['feed']['id_topic'], '" /></td></tr>
				
				<tr><td width="30%">', $txt['feedposter_footer'], '</td><td><textarea name="footer" rows="3" cols="50">', $context['feed']['footer'], '</textarea></td></tr>
				
				
				<tr><td width="30%" align="right"><input type="checkbox" name="feedposter_feedenabled" ', (($context['feed']['enabled'] == 1) ? ' checked="checked" ' : ''), ' /></td><td>', $txt['feedposter_feedenabled'], '</td></tr>
				<tr><td width="30%" align="right"><input type="checkbox" name="feedposter_htmlenabled" ', (($context['feed']['html'] == 1) ? ' checked="checked" ' : ''), ' /></td><td>', $txt['feedposter_htmlenabled'], '</td></tr>
				<tr><td width="30%" align="right"><input type="checkbox" name="feedposter_topiclocked" ', (($context['feed']['locked'] == 1) ? ' checked="checked" ' : ''), ' /></td><td>', $txt['feedposter_topiclocked'], '</td></tr>

				<tr>
				<td colspan="2" align="center">
				<input type="hidden" name="id" value="', $context['feed']['ID_FEED'], '" />
				<input type="submit" name="editfeed" value="', $txt['feedposter_editfeed'],  '" />
				</td>
				</tr>
				</table>
			</form>
			
			<br />
			<b>Has RSS Feed Poster helped you?</b> Then support the developers:<br />
			    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="sales@visualbasiczone.com">
				<input type="hidden" name="item_name" value="RSS Feed Poster">
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

	
	// The Copyright is required to remain or contact me to purchase link removal.
	echo '<br /><div align="center"><a href="http://www.smfhacks.com" target="blank">RSS Feed Poster</a></div>';

}

?>