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


	echo '<form method="post" name="picform" id="picform" action="' . $scripturl . '?action=gpdr&sa=privacyadmin2" onsubmit="submitonce(this);">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="50%" colspan="2"  align="center" class="catbg">
    <b>', $txt['gpdr_privacypolicy'], '</b></td>
  </tr>';


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

 	 theme_postbox($context['privacy_policy_data']);



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
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['gpdr_text_settings'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
			    ',$txt['gpdr_txt_yourversion'] , $currentVersion, '&nbsp;',$txt['gpdr_txt_latestversion'],'<span id="lastgpdr" name="lastgpdr"></span>
				<form method="post" action="' . $scripturl . '?action=gpdr;sa=settings2">
				
';
			    
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
				<b>Has GPDR Helper helped you?</b> Then support the developers:<br />
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
<table width="100%" align="center" border="0" cellspacing="0" cellpadding="5" class="tborder" style="border-top: 0;">
  <tr>
    <td width="50%"  align="center" class="catbg">
    <b>', $txt['gpdr_privacypolicy'], '</b></td>
  </tr>
		<tr>
			<td class="windowbg2" style="padding-top: 8px; padding-bottom: 8px;">
				', parse_bbc($context['privacy_policy_data']), '
			</td>
		</tr>';


    if (isset($_REQUEST['reagree']))
    {
        echo '		<tr>
			<td class="windowbg2" style="padding-top: 8px; padding-bottom: 8px;" align="center">
				<input type="submit" name="accept_view_privacypolicy" value="', $txt['gpdr_policy_agree'], '"  /> 
				
				<input type="submit" name="decline" value="', $txt['gpdr_txt_privacy_decline'], '"  />
                <input type="hidden" name="step" value="1" />
			    </td>
			</tr>';
    }

			echo '
			
			</table>
		</form>';

}


function template_view_registrationagreement()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
		<form action="', $scripturl, '?action=gpdr;sa=registeragreement;save=1" method="post" accept-charset="', $context['character_set'], '" id="registration">
<table width="100%" align="center" border="0" cellspacing="0" cellpadding="5" class="tborder" style="border-top: 0;">
  <tr>
    <td width="50%"  align="center" class="catbg">
    <b>',  $txt['gpdr_registration_agreement'], '</b></td>
  </tr>
		<tr>
			<td class="windowbg2" style="padding-top: 8px; padding-bottom: 8px;">
				', parse_bbc($context['registrationagreement_data']), '
			</td>
		</tr>';



    if (isset($_REQUEST['reagree']))
    {

                echo '		<tr>
			<td class="windowbg2" style="padding-top: 8px; padding-bottom: 8px;" align="center">
				<input type="submit" name="accept_view_registeragreement" value="', $txt['gpdr_agreement_agree'], '"  /> 
                <input type="submit" name="decline" value="', $txt['gpdr_agreement_decline'], '"  />
                <input type="hidden" name="step" value="1" />
			    </td>
			</tr>';


    }

			echo '
			</table>
		</form>';

}

function template_profile_export()
{
    global $txt, $context, $scripturl;

 	echo '
		<form action="', $scripturl . '?action=gpdr;sa=exportdata;type=posts;u=' . $context['profile_MEMID'] . '" method="post" accept-charset="', $context['character_set'], '" id="registration">   
    <table width="100%" align="center" border="0" cellspacing="0" cellpadding="5" class="tborder" style="border-top: 0;">
  <tr>
    <td width="50%"  align="center" class="catbg">
    <b>',  $txt['gpdr_txt_export_information'] , '</b></td>
  </tr>
		<tr>
			<td class="windowbg2"  colspan="2">
				', $context['profile_prehtml'], '
			</td>
		</tr>
		<tr>
			<td class="windowbg2"  colspan="2">
				<input type="submit" value="' .  $txt['gpdr_txt_exportdata'] . '" />
			</td>
		</tr>
		</table>
		</form>';


}