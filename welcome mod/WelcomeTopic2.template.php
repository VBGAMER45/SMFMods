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

echo '<div class="cat_bar">
		<h3 class="catbg">
        ', $txt['welcome_settings'], '
        </h3>
  </div>
	<table class="tborder" align="center" border="0" cellpadding="4" cellspacing="0" width="100%">
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
								<td><a href="', $scripturl, '?action=welcome;sa=edit&id=', $topicwel['ID'], '">',$txt['welcome_edittopic'], '</a>&nbsp;&nbsp;<a href="', $scripturl, '?action=welcome;sa=delete&id=', $topicwel['ID'], '">', $txt['welcome_delete'], '</a></td>
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
<br />

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
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>';


echo '<div class="tborder">

<form method="post" name="cprofile" id="cprofile" action="', $scripturl, '?action=welcome&sa=add2" onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg">
        ', $txt['welcome_addtopic'], '
        </h3>
  </div>
	<table class="tborder" align="center" border="0" cellpadding="4" cellspacing="0" width="100%">

    <tr>
    <td width="28%"  valign="top" class="windowbg2" align="right"><span class="gen"><b>' . $txt['welcome_subject'] . '</b>&nbsp;</span></td>
    <td width="72%"  class="windowbg2"><input type="text" name="topicsubject" size="50" /></td>
  </tr>
  <tr>

  <td class="windowbg2" colspan="2"  align="center"><table>
	   ';

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
		<td>';
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


		echo '</td></tr>';
	}

		echo '
	   </table>
	     <br />
    ',$txt['welcome_topicnote'],'

	   </td></tr>



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
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>';


	echo '<div class="tborder">
<form method="post" name="cprofile" id="cprofile" action="', $scripturl, '?action=welcome&sa=edit2" onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg">
        ', $txt['welcome_edittopic'], '
        </h3>
  </div>
	<table class="tborder" align="center" border="0" cellpadding="4" cellspacing="0" width="100%">
  <tr>
    <tr>
    <td width="28%"  valign="top" class="windowbg2" align="right"><span class="gen"><b>' . $txt['welcome_subject'] . '</b>&nbsp;</span></td>
    <td width="72%"  class="windowbg2"><input type="text"  size="50" name="topicsubject" value="' .  $context['welcome_topic']['SUBJECT']  . '" /></td>
  </tr>
  <td class="windowbg2" align="center" colspan="2"><table>
	   ';

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
		<td>';
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


		echo '</td></tr>';
	}


	echo '
	   </table>
	     <br />
    ',$txt['welcome_topicnote'],'

	   </td></tr>



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


	// Copryright link must remain. To remove you need to purchase link removal at smfhacks.com
	echo '<div align="center"><a href="http://www.smfhacks.com" target="blank">Welcome Topic Mod</a></div>';

}


?>