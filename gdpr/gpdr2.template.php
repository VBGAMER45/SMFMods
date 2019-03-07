<?php
/*
GDPR Helper
Version 1.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2018 SMFHacks.com

############################################
License Information:

Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################

*/

function template_privacypolicy_admin()
{
	global $scripturl, $modSettings, $txt, $context, $settings, $boarddir;

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';


	echo '<form method="post" name="picform" id="picform" action="' . $scripturl . '?action=admin;area=gpdr&sa=privacyadmin2" onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg">
        ', $txt['gpdr_privacypolicy'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">';

	// Check if cache folder is writable
	if (!is_writable($boarddir . "/privacypolicy.txt"))
	{
		echo '<tr>
	    <td width="50%" colspan="2"  align="center" class="windowbg2">
	    ' . $txt['gpdr_err_writable_policy'] . ' ' . $boarddir . '/privacypolicy.txt
	    </td>
	    </tr>';

	}

	echo '
  <tr class="windowbg2">
  	<td colspan="2"><table width="100%">
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
			</table>';

     	if ($context['show_spellchecking'])
     		echo '
     									<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'picform\', \'privacypolicy\');" />';




echo '
  <tr class="windowbg2">
    <td width="100%" colspan="2"  align="center" class="windowbg2">
    <input type="submit" value="' . $txt['gpdr_txt_update'] . '" name="submit" />
	</div>
    </td>
  </tr>
</table>

		</form>
';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';

}


function template_gpdr_settings()
{
	global $scripturl, $modSettings, $txt, $currentVersion;

echo '
<div class="cat_bar">
		<h3 class="catbg">
        ', $txt['gpdr_text_settings'], '
        </h3>
</div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg">
			<td>
			    ',$txt['gpdr_txt_yourversion'] , $currentVersion, '&nbsp;',$txt['gpdr_txt_latestversion'],'<span id="lastgpdr" name="lastgpdr"></span>';
			    
			    
			    
			$isSecure = false;
			if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			    $isSecure = true;
			}
			elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
			    $isSecure = true;
			}
			$REQUEST_PROTOCOL = $isSecure ? 'https' : 'http';
			    
			// Warn the user if they are category image path is not writable
			if ($isSecure == false)
				echo '<br /><font color="#FF0000"><b>' . $txt['gpdr_err_no_ssl']. '</b></font><br />';

			    
			    
			    echo '
				<form method="post" action="' . $scripturl . '?action=admin;area=gpdr;sa=settings2">

				<input type="checkbox" name="gpdr_enable_privacy_policy" ' . ($modSettings['gpdr_enable_privacy_policy'] ? ' checked="checked" ' : '') . ' />' . $txt['gpdr_enable_privacy_policy'] . '<br />
				<input type="checkbox" name="gpdr_force_privacy_agree" ' . ($modSettings['gpdr_force_privacy_agree'] ? ' checked="checked" ' : '') . ' />' . $txt['gpdr_force_privacy_agree'] . '<br />
				<input type="checkbox" name="gpdr_force_agreement_agree" ' . ($modSettings['gpdr_force_agreement_agree'] ? ' checked="checked" ' : '') . ' />' . $txt['gpdr_force_agreement_agree'] . '<br />
				<input type="checkbox" name="gpdr_clear_memberinfo" ' . ($modSettings['gpdr_clear_memberinfo'] ? ' checked="checked" ' : '') . ' />' . $txt['gpdr_clear_memberinfo'] . '<br />
				<input type="checkbox" name="gpdr_allow_export_userdata" ' . ($modSettings['gpdr_allow_export_userdata'] ? ' checked="checked" ' : '') . ' />' . $txt['gpdr_allow_export_userdata'] . '<br />


				<input type="submit" name="savesettings" value="' . $txt['gpdr_save_settings']. '" />
			</form>

			</td>
		</tr>

<tr class="windowbg"><td>Need more features? Find more about <a href="https://www.smfhacks.com/gdprpro.php" target="_blank">GDPR Pro by clicking here</a><br /><br />


				<b>Has GDPR Helper helped you?</b> Then support the developers:<br />
				    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="business" value="sales@visualbasiczone.com">
					<input type="hidden" name="item_name" value="GPDR Helper">
					<input type="hidden" name="no_shipping" value="1">
					<input type="hidden" name="no_note" value="1">
					<input type="hidden" name="currency_code" value="USD">
					<input type="hidden" name="tax" value="0">
					<input type="hidden" name="bn" value="PP-DonationsBF">
					<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
					<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
				</form>
				<br />
			<br />
		</td>
	</tr>
</table>
		<script language="JavaScript" type="text/javascript" src="https://www.smfhacks.com/versions/gpdr.js?t=' . time() . '"></script>
			<script language="JavaScript" type="text/javascript">

              function GPDRCurrentVersion()
			{
				if (!window.gpdrVersion)
					return;

				gpdrspan = document.getElementById("lastgpdr");

				if (window.gpdrVersion != "' . $currentVersion . '")
				{
					setInnerHTML(gpdrspan, "<b><font color=\"red\">" + window.gpdrVersion + "</font>&nbsp;' . $txt['gpdr_txt_version_outofdate'] . '</b>");
				}
				else
				{
					setInnerHTML(gpdrspan, "' . $currentVersion . '");
				}
			}

			// Override on the onload function
			window.onload = function ()
			{
				GPDRCurrentVersion();
			}
			</script>
';

}

function template_view_privacypolicy()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=gpdr;sa=privacypolicy;save=1" method="post" accept-charset="', $context['character_set'], '" id="registration">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['gpdr_privacypolicy'], '</h3>
			</div>
			<span class="upperframe"><span></span></span>
			<div class="roundframe">
				<p>', parse_bbc($context['privacy_policy_data']),'</p>
			</div>
			<span class="lowerframe"><span></span></span>';

    if (isset($_REQUEST['reagree']))
    {
        echo '
			<div id="confirm_buttons">';

        echo '
				<input type="submit" name="accept_view_privacypolicy" value="', $txt['gpdr_policy_agree'], '" class="button_submit" /> 
				
				<input type="submit" name="decline" value="', $txt['gpdr_txt_privacy_decline'], '" class="button_submit"  />';

        echo '
			</div>';
    }

			echo '
			<input type="hidden" name="step" value="1" />
		</form>';

}

function template_view_registrationagreement()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=gpdr;sa=registeragreement;save=1" method="post" accept-charset="', $context['character_set'], '" id="registration">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['gpdr_registration_agreement'], '</h3>
			</div>
			<span class="upperframe"><span></span></span>
			<div class="roundframe">
				<p>', parse_bbc($context['registrationagreement_data']),'</p>
			</div>
			<span class="lowerframe"><span></span></span>';

    if (isset($_REQUEST['reagree']))
    {
        echo '
			<div id="confirm_buttons">';

        echo '
				<input type="submit" name="accept_view_registeragreement" value="', $txt['gpdr_agreement_agree'], '" class="button_submit" /> 
				<input type="submit" name="decline" value="', $txt['gpdr_agreement_decline'], '" class="button_submit"   />';

        echo '
			</div>';
    }

			echo '
			<input type="hidden" name="step" value="1" />
		</form>';

}