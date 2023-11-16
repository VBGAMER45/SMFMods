<?php
/*
Profile Comments
Version 2.0
by:vbgamer45
http://www.smfhacks.com
*/
function template_commentmain()
{
	 ProfileCommentsCopyright();
}

function template_commentsadd()
{
	global $context, $scripturl, $txt, $settings;

	// Get the profile id
	$u = (int) @$_REQUEST['u'];

	 if (empty($u))
		fatal_error($txt['pcomments_err_noprofile']);

		// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>';


	echo '
    
    <div class="tborder">
<div class="cat_bar">
                <h3 class="catbg">
                ', $txt['pcomments_addcomment'], '
                </h3>
        </div>
<form method="POST" name="cprofile" id="cprofile" action="', $scripturl, '?action=comment&sa=add2" onsubmit="submitonce(this);">
<table border="0" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" width="100%" >
  <tr>
    <td width="28%" class="windowbg2" align="right"><b>',$txt['pcomments_subject'],'</b></td>
    <td width="72%" class="windowbg2"><input type="text" name="subject" size="64" maxlength="100" /></td>
  </tr>
  <tr>
    <td width="28%"  valign="top" class="windowbg2" align="right"><b>',$txt['pcomments_acomment'] ,'</b></td>
    <td width="72%"  class="windowbg2"><table>
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
		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		
		echo '</td></tr>';
	}
	
	
	
   echo '</table></td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="hidden" name="userid" value="', $u , '" />';



// Check if comments are autoapproved
   	if(allowedTo('pcomments_autocomment') == false)
   			echo $txt['pcomments_text_commentwait'] . '<br />';

   	if ($context['show_spellchecking'])
   		echo '
   									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'cprofile\', \'comment\');" />';

echo '
    <input type="submit" value="',$txt['pcomments_addcomment'],'" name="submit" /></td>

  </tr>
</table>
</form>';


	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';
		
	echo '</div>';
	
	
	 ProfileCommentsCopyright();
}

function template_commentsedit()
{
	global $context, $scripturl, $txt, $settings;

	$row = $context['profilerow'];

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>';


	echo '<div class="tborder">
<div class="cat_bar">
                <h3 class="catbg">
                ', $txt['pcomments_editcomment'], '
                </h3>
        </div>
<form method="POST" name="cprofile" id="cprofile" action="', $scripturl, '?action=comment&sa=edit2" onsubmit="submitonce(this);">
<table border="0" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" width="100%" >
  <tr>
    <td width="28%" class="windowbg2" align="right"><b>',$txt['pcomments_subject'],'</b></td>
    <td width="72%" class="windowbg2"><input type="text" name="subject" size="64" maxlength="100" value="', $row['subject'], '" /></td>
  </tr>
  <tr>
    <td width="28%"  valign="top" class="windowbg2" align="right"><b>',$txt['pcomments_acomment'],'</b></td>
    <td width="72%"  class="windowbg2">
   <table>
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
		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		
		echo '</td></tr>';
	}
	
   echo '</table></td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="hidden" name="commentid" value="', $row['ID_COMMENT'], '" />';


	// Check if comments are autoapproved
   	if (allowedTo('pcomments_autocomment') == false)
   			echo $txt['pcomments_text_commentwait'] . '<br />';

 	if ($context['show_spellchecking'])
   		echo '
   									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'cprofile\', \'comment\');" />';


echo '
    <input type="submit" value="',$txt['pcomments_editcomment'],'" name="submit" /></td>
  </tr>
</table>
</form>';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';
	

	echo '</div>';

	
	 ProfileCommentsCopyright();
}

function template_commentsadmin()
{
	global $txt, $context, $scripturl;
	
	
	echo '
            <div class="cat_bar">
                <h3 class="catbg">
                ', $txt['pcomments_admin'], '
                </h3>
        </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg">
			<td>
			<b>' . $txt['pcomments_com_wait_appproval']. '</b><br />';

	
			echo '
			<table class="tborder" cellspacing="0" align="center" cellpadding="4">
				<tr class="titlebg">
					<td>',$txt['pcomments_com_commnet'],'</td>
					<td>',$txt['pcomments_com_postedby'],'</td>
					<td>',$txt['pcomments_com_profile'],'</td>
					<td>',$txt['pcomments_com_date'],'</td>
					<td>',$txt['pcomments_com_options'],'</td>
				</tr>
			';
			
			foreach($context['pcomments_list'] as $row)
			{

				echo '<td>',$row['subject'],'<br />',parse_bbc($row['comment']),'</td>';
				echo '<td><a href="',$scripturl,'?action=profile;u='  . $row['ID_MEMBER'] . '">' . $row['real_name'],'</td>';
				echo '<td><a href="',$scripturl,'?action=profile;u='  . $row['COMMENT_MEMBER_ID'] . '">' . $row['ProfileName'],'</td>';
				echo '<td>',timeformat($row['date']),'</td>';
				echo '<td><a href="', $scripturl, '?action=comment;sa=approve;id=' . $row['ID_COMMENT'] . '">',$txt['pcomments_approve'],'</a><br />
				<a href="', $scripturl, '?action=comment;sa=delete;id=' . $row['ID_COMMENT'] . '">',$txt['pcomments_delcomment'],'</a></td>';
				
			}
	
			
			if ($context['pcomments_count'] > 0)
			{
				echo '<tr class="titlebg">
						<td align="left" colspan="5">
						' . $txt['pcomments_text_pages'];
		
							
						$context['page_index'] = constructPageIndex($scripturl . '?action=comment;sa=admin' , $_REQUEST['start'], $context['pcomments_count'], 10);
				
						echo $context['page_index'];
		
				echo '
						</td>
					</tr>';
			}
			
			echo '
			</table>
			
			
			<br />
<b>Has Profile Comments helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="Profile Comments">
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
	
 ProfileCommentsCopyright();
}

function ProfileCommentsCopyright()
{
	// DO NOT Edit this function
	
// http://www.smfhacks.com/copyright_removal.php
echo '
<div align="center"><!--Link must remain or contact me to pay to remove.-->Powered by <a href="http://www.smfhacks.com" target="blank">Profile Comments</a> by <a href="http://www.createaforum.com" title="Forum Hosting">CreateAForum.com</a><!--End Copyright link--></div>';

}


?>