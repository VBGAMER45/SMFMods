<?php
/*
Welcome Topic
Version 1.0
by:vbgamer45
http://www.smfhacks.com
*/
function template_main()
{
		global $scripturl, $modSettings, $txt, $context, $settings;

echo '<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['welcome_settings'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
			<b>', $txt['welcome_settings'], '</b><br />
			<form method="post" action="', $scripturl, '?action=welcome;sa=admin2">
				', $txt['welcome_forum'], '
				<select name="boardselect" id="boardselect">
  ';

	foreach ($context['welcome_boards'] as $key => $option)
		 echo '<option value="', $key, ' " ', ($key == $modSettings['welcome_boardid'] ? ' selected="selected" ' : ''), '>', $option, '</option>';

echo '</select><br />
			    
			', $txt['welcome_postername'], '<input type="text" name="welcome_postername" id="welcome_postername" value="', $modSettings['welcome_membername'], '" /><a href="', $scripturl, '?action=findmember;input=welcome_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a> <a href="', $scripturl, '?action=findmember;input=welcome_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a>
				<br />
				<input type="submit" name="savesettings" value="', $txt['welcome_savesettings'], '" />
			</form>
			<br /><br />
			
			<table align="center" class="tborder" cellspacing="0" cellpadding="4">
			<tr class="titlebg">
				<td>',$txt['welcome_subject'],'</td>
				<td>', $txt['welcome_options'], '</td>
			</tr>';
			
			// List all the Welcome Topics
		
			
			foreach ($context['welcome_topics'] as $key => $topicwel)
			{
				
						echo '<tr class="windowbg">
								<td>', $topicwel['SUBJECT'], '</td> 
								<td><a href="', $scripturl, '?action=welcome;sa=edit;id=', $topicwel['ID'], '">',$txt['welcome_edittopic'], '</a>&nbsp;&nbsp;<a href="', $scripturl, '?action=welcome;sa=delete;id=', $topicwel['ID'], '">', $txt['welcome_delete'], '</a></td> 
							</tr>
						';
				}
			

		
echo '		<tr>
				<td align="center" colspan="2" class="windowbg"><a href="',$scripturl,'?action=welcome;sa=add">',$txt['welcome_addtopic'],'</a></td>
			</tr>
			</table>

			
			<br /><br />
			<b>Has Welcome Topic Mod helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="Welcome Topic Mod">
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
</table></td>
		</tr>
</table>';

}
function template_addtopic()
{
	global $context, $scripturl, $txt, $settings, $modSettings;
	


	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';


echo '<div class="tborder">
<form method="post" name="cprofile" id="cprofile" action="', $scripturl, '?action=welcome&sa=add2">
<table border="0" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" width="100%" height="129">
  <tr>
    <td width="50%" colspan="2" height="19" align="center" class="catbg">
    <b>', $txt['welcome_addtopic'], '</b></td>
  </tr>

  <tr>
  <td class="windowbg2">';

	//show the bbc box
	display_bbcbox();

echo '</td></tr>

  <tr>
    <td width="28%" height="19" valign="top" class="windowbg2" align="right"><span class="gen"><b>' . $txt['welcome_subject'] . '</b>&nbsp;</span></td>
    <td width="72%" height="19" class="windowbg2"><input type="text" name="topicsubject" size="50" /></td>
  </tr>
  <tr>
    <td width="28%" height="19" valign="top" class="windowbg2" align="right"><span class="gen"><b>' . $txt['welcome_topicbody'] . '</b>&nbsp;</span></td>
    <td width="72%" height="19" class="windowbg2"><textarea rows="6" name="topicbody" cols="54"></textarea>
    <br />
    ',$txt['welcome_topicnote'],'
    </td>
  </tr>
  <tr>
    <td width="28%" colspan="2" height="26" align="center" class="windowbg2">';


   	if ($context['show_spellchecking'])
   		echo '
   									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'cprofile\', \'topicbody\');" />';



echo '
    <input type="submit" value="', $txt['welcome_addtopic'], '" name="submit" /></td>

  </tr>
</table>
</form>';


	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';
	

	echo '</div>';
	//Copryright link must remain. To remove you need to purchase link removal at smfhacks.com
	echo '<div align="center"><!--Link must remain or contact me to pay to remove.--><a href="http://www.smfhacks.com" target="blank">Welcome Topic Mod</a><!--End Copyright link--></div>';

}
function template_edittopic()
{
	global $context, $scripturl, $txt, $settings, $modSettings;


	
	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';


	echo '<div class="tborder">
<form method="post" name="cprofile" id="cprofile" action="', $scripturl, '?action=welcome&sa=edit2">
<table border="0" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" width="100%" height="129">
  <tr>
    <td width="50%" colspan="2" height="19" align="center" class="catbg">
    <b>', $txt['welcome_edittopic'], '</b></td>
  </tr>

  <tr>
  <td class="windowbg2">';

	//show the bbc box
	display_bbcbox();

echo '</td></tr>

  <tr>
    <td width="28%" height="19" valign="top" class="windowbg2" align="right"><span class="gen"><b>' . $txt['welcome_subject'] . '</b>&nbsp;</span></td>
    <td width="72%" height="19" class="windowbg2"><input type="text"  size="50" name="topicsubject" value="' .  $context['welcome_topic']['SUBJECT']  . '" /></td>
  </tr>
  <tr>
    <td width="28%" height="19" valign="top" class="windowbg2" align="right"><span class="gen"><b>' . $txt['welcome_topicbody'] . '</b>&nbsp;</span></td>
    <td width="72%" height="19" class="windowbg2"><textarea rows="6" name="topicbody" cols="54">' .  $context['welcome_topic']['BODY']  . '</textarea>
    <br />
    ',$txt['welcome_topicnote'],'</td>
  </tr>
  <tr>
    <td width="28%" colspan="2" height="26" align="center" class="windowbg2">
    <input type="hidden" name="id" value="' .  $context['welcome_topic']['ID']  . '" />
    ';


   	if ($context['show_spellchecking'])
   		echo '
   									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'cprofile\', \'topicbody\');" />';


echo '
    <input type="submit" value="', $txt['welcome_edittopic'], '" name="submit" /></td>

  </tr>
</table>
</form></div>';


	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';


	//Copryright link must remain. To remove you need to purchase link removal at smfhacks.com
	echo '<div align="center"><a href="http://www.smfhacks.com" target="blank">Welcome Topic Mod</a></div>';

}


function display_bbcbox()
{
	global $context, $scripturl, $txt,$settings;
	// Assuming BBC code is enabled then print the buttons and some javascript to handle it.
	if ($context['show_bbc'])
	{
		echo '
			<tr>
				<td align="right" class="windowbg2"></td>
				<td valign="middle" class="windowbg2">
					<script language="JavaScript" type="text/javascript"><!--
						function bbc_highlight(something, mode)
						{
							something.style.backgroundImage = "url(" + smf_images_url + (mode ? "/bbc/bbc_hoverbg.gif)" : "/bbc/bbc_bg.gif)");
						}
					// --></script>';

		// The below array makes it dead easy to add images to this page. Add it to the array and everything else is done for you!
		$context['bbc_tags'] = array();
		$context['bbc_tags'][] = array(
			'bold' => array('code' => 'b', 'before' => '[b]', 'after' => '[/b]', 'description' => $txt[253]),
			'italicize' => array('code' => 'i', 'before' => '[i]', 'after' => '[/i]', 'description' => $txt[254]),
			'underline' => array('code' => 'u', 'before' => '[u]', 'after' => '[/u]', 'description' => $txt[255]),
			'strike' => array('code' => 's', 'before' => '[s]', 'after' => '[/s]', 'description' => $txt[441]),
			array(),
			'glow' => array('code' => 'glow', 'before' => '[glow=red,2,300]', 'after' => '[/glow]', 'description' => $txt[442]),
			'shadow' => array('code' => 'shadow', 'before' => '[shadow=red,left]', 'after' => '[/shadow]', 'description' => $txt[443]),
			'move' => array('code' => 'move', 'before' => '[move]', 'after' => '[/move]', 'description' => $txt[439]),
			array(),
			'pre' => array('code' => 'pre', 'before' => '[pre]', 'after' => '[/pre]', 'description' => $txt[444]),
			'left' => array('code' => 'left', 'before' => '[left]', 'after' => '[/left]', 'description' => $txt[445]),
			'center' => array('code' => 'center', 'before' => '[center]', 'after' => '[/center]', 'description' => $txt[256]),
			'right' => array('code' => 'right', 'before' => '[right]', 'after' => '[/right]', 'description' => $txt[446]),
			array(),
			'hr' => array('code' => 'hr', 'before' => '[hr]', 'description' => $txt[531]),
			array(),
			'size' => array('code' => 'size', 'before' => '[size=10pt]', 'after' => '[/size]', 'description' => $txt[532]),
			'face' => array('code' => 'font', 'before' => '[font=Verdana]', 'after' => '[/font]', 'description' => $txt[533]),
		);
		$context['bbc_tags'][] = array(
			'flash' => array('code' => 'flash', 'before' => '[flash=200,200]', 'after' => '[/flash]', 'description' => $txt[433]),
			'img' => array('code' => 'img', 'before' => '[img]', 'after' => '[/img]', 'description' => $txt[435]),
			'url' => array('code' => 'url', 'before' => '[url]', 'after' => '[/url]', 'description' => $txt[257]),
			'email' => array('code' => 'email', 'before' => '[email]', 'after' => '[/email]', 'description' => $txt[258]),
			'ftp' => array('code' => 'ftp', 'before' => '[ftp]', 'after' => '[/ftp]', 'description' => $txt[434]),
			array(),
			'table' => array('code' => 'table', 'before' => '[table]', 'after' => '[/table]', 'description' => $txt[436]),
			'tr' => array('code' => 'td', 'before' => '[tr]', 'after' => '[/tr]', 'description' => $txt[449]),
			'td' => array('code' => 'td', 'before' => '[td]', 'after' => '[/td]', 'description' => $txt[437]),
			array(),
			'sup' => array('code' => 'sup', 'before' => '[sup]', 'after' => '[/sup]', 'description' => $txt[447]),
			'sub' => array('code' => 'sub', 'before' => '[sub]', 'after' => '[/sub]', 'description' => $txt[448]),
			'tele' => array('code' => 'tt', 'before' => '[tt]', 'after' => '[/tt]', 'description' => $txt[440]),
			array(),
			'code' => array('code' => 'code', 'before' => '[code]', 'after' => '[/code]', 'description' => $txt[259]),
			'quote' => array('code' => 'quote', 'before' => '[quote]', 'after' => '[/quote]', 'description' => $txt[260]),
			array(),
			'list' => array('code' => 'list', 'before' => '[list]\n[li]', 'after' => '[/li]\n[li][/li]\n[/list]', 'description' => $txt[261]),
		);

		// Here loop through the array, printing the images/rows/separators!
		foreach ($context['bbc_tags'][0] as $image => $tag)
		{
			// Is there a "before" part for this bbc button?  If not, it can't be a button!!
			if (isset($tag['before']))
			{
				// Is this tag disabled?
				if (!empty($context['disabled_tags'][$tag['code']]))
					continue;

				// If there's no after, we're just replacing the entire selection in the post box.
				if (!isset($tag['after']))
					echo '<a href="javascript:void(0);" onclick="replaceText(\'', $tag['before'], '\', document.', $context['post_form'], '.', $context['post_box_name'], '); return false;">';
				// On the other hand, if there is one we are surrounding the selection ;).
				else
					echo '<a href="javascript:void(0);" onclick="surroundText(\'', $tag['before'], '\', \'', $tag['after'], '\', document.', $context['post_form'], '.', $context['post_box_name'], '); return false;">';

				// Okay... we have the link.  Now for the image and the closing </a>!
				echo '<img onmouseover="bbc_highlight(this, true);" onmouseout="if (window.bbc_highlight) bbc_highlight(this, false);" src="', $settings['images_url'], '/bbc/', $image, '.gif" align="bottom" width="23" height="22" alt="', $tag['description'], '" title="', $tag['description'], '" border="0" style="background-image: url(', $settings['images_url'], '/bbc/bbc_bg.gif); margin: 1px 2px 1px 1px;" /></a>';
			}
			// I guess it's a divider...
			else
				echo '<img src="', $settings['images_url'], '/bbc/divider.gif" alt="|" style="margin: 0 3px 0 3px;" />';
		}

		// Print a drop down list for all the colors we allow!
		if (!isset($context['disabled_tags']['color']))
			echo ' <select onchange="surroundText(\'[color=\'+this.options[this.selectedIndex].value+\']\', \'[/color]\', document.', $context['post_form'], '.', $context['post_box_name'], '); this.selectedIndex = 0;" style="margin-bottom: 1ex;">
							<option value="" selected="selected">', $txt['change_color'], '</option>
							<option value="Black">', $txt[262], '</option>
							<option value="Red">', $txt[263], '</option>
							<option value="Yellow">', $txt[264], '</option>
							<option value="Pink">', $txt[265], '</option>
							<option value="Green">', $txt[266], '</option>
							<option value="Orange">', $txt[267], '</option>
							<option value="Purple">', $txt[268], '</option>
							<option value="Blue">', $txt[269], '</option>
							<option value="Beige">', $txt[270], '</option>
							<option value="Brown">', $txt[271], '</option>
							<option value="Teal">', $txt[272], '</option>
							<option value="Navy">', $txt[273], '</option>
							<option value="Maroon">', $txt[274], '</option>
							<option value="LimeGreen">', $txt[275], '</option>
						</select>';
		echo '<br />';

		// Print the buttom row of buttons!
		foreach ($context['bbc_tags'][1] as $image => $tag)
		{
			if (isset($tag['before']))
			{
				// Is this tag disabled?
				if (!empty($context['disabled_tags'][$tag['code']]))
					continue;

				// If there's no after, we're just replacing the entire selection in the post box.
				if (!isset($tag['after']))
					echo '<a href="javascript:void(0);" onclick="replaceText(\'', $tag['before'], '\', document.', $context['post_form'], '.', $context['post_box_name'], '); return false;">';
				// On the other hand, if there is one we are surrounding the selection ;).
				else
					echo '<a href="javascript:void(0);" onclick="surroundText(\'', $tag['before'], '\', \'', $tag['after'], '\', document.', $context['post_form'], '.', $context['post_box_name'], '); return false;">';

				// Okay... we have the link.  Now for the image and the closing </a>!
				echo '<img onmouseover="bbc_highlight(this, true);" onmouseout="if (window.bbc_highlight) bbc_highlight(this, false);" src="', $settings['images_url'], '/bbc/', $image, '.gif" align="bottom" width="23" height="22" alt="', $tag['description'], '" title="', $tag['description'], '" border="0" style="background-image: url(', $settings['images_url'], '/bbc/bbc_bg.gif); margin: 1px 2px 1px 1px;" /></a>';
			}
			// I guess it's a divider...
			else
				echo '<img src="', $settings['images_url'], '/bbc/divider.gif" alt="|" style="margin: 0 3px 0 3px;" />';
		}

		echo '
				</td>
			</tr>';
	}
	// Now start printing all of the smileys.
	if (!empty($context['smileys']['postform']))
	{
		echo '
			<tr>
				<td align="right" class="windowbg2"></td>
				<td valign="middle" class="windowbg2">';

		// Show each row of smileys ;).
		foreach ($context['smileys']['postform'] as $smiley_row)
		{
			foreach ($smiley_row['smileys'] as $smiley)
				echo '
					<a href="javascript:void(0);" onclick="replaceText(\' ', $smiley['code'], '\', document.', $context['post_form'], '.', $context['post_box_name'], '); return false;"><img src="', $settings['smileys_url'], '/', $smiley['filename'], '" align="bottom" alt="', $smiley['description'], '" title="', $smiley['description'], '" border="0" /></a>';

			// If this isn't the last row, show a break.
			if (empty($smiley_row['last']))
				echo '<br />';
		}

		// If the smileys popup is to be shown... show it!
		if (!empty($context['smileys']['popup']))
			echo '
					<a href="javascript:moreSmileys();">[', $txt['more_smileys'], ']</a>';

		echo '
				</td>
			</tr>';
	}

	// If there are additional smileys then ensure we provide the javascript for them.
	if (!empty($context['smileys']['popup']))
	{
		echo '
			<script language="JavaScript" type="text/javascript"><!--
				var smileys = [';

		foreach ($context['smileys']['popup'] as $smiley_row)
		{
			echo '
					[';
			foreach ($smiley_row['smileys'] as $smiley)
			{
				echo '
						["', $smiley['code'], '","', $smiley['filename'], '","', $smiley['js_description'], '"]';
				if (empty($smiley['last']))
					echo ',';
			}

			echo ']';
			if (empty($smiley_row['last']))
				echo ',';
		}

		echo '];
				var smileyPopupWindow;

				function moreSmileys()
				{
					var row, i;

					if (smileyPopupWindow)
						smileyPopupWindow.close();

					smileyPopupWindow = window.open("", "add_smileys", "toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=220,resizable=yes");
					smileyPopupWindow.document.write(\'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\n<html>\');
					smileyPopupWindow.document.write(\'\n\t<head>\n\t\t<title>', $txt['more_smileys_title'], '</title>\n\t\t<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />\n\t</head>\');
					smileyPopupWindow.document.write(\'\n\t<body style="margin: 1ex;">\n\t\t<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder">\n\t\t\t<tr class="titlebg"><td align="left">', $txt['more_smileys_pick'], '</td></tr>\n\t\t\t<tr class="windowbg"><td align="left">\');

					for (row = 0; row < smileys.length; row++)
					{
						for (i = 0; i < smileys[row].length; i++)
						{
							smileys[row][i][2] = smileys[row][i][2].replace(/"/g, \'&quot;\');
							smileyPopupWindow.document.write(\'<a href="javascript:void(0);" onclick="window.opener.replaceText(&quot; \' + smileys[row][i][0] + \'&quot;, window.opener.document.', $context['post_form'], '.', $context['post_box_name'], '); window.focus(); return false;"><img src="', $settings['smileys_url'], '/\' + smileys[row][i][1] + \'" alt="\' + smileys[row][i][2] + \'" title="\' + smileys[row][i][2] + \'" style="padding: 4px;" border="0" /></a>\');
						}
						smileyPopupWindow.document.write("<br />");
					}

					smileyPopupWindow.document.write(\'</td></tr>\n\t\t\t<tr><td align="center" class="windowbg"><a href="javascript:window.close();\\">', $txt['more_smileys_close_window'], '</a></td></tr>\n\t\t</table>\n\t</body>\n</html>\');
					smileyPopupWindow.document.close();
				}
			// --></script>';
	}
}
?>