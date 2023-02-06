<?php
/*
Social Login Pro
Version 2.0
by:vbgamer45
http://www.smfhacks.com

License Information:
Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
*/

function template_slp_settings()
{
	global $context, $txt, $scripturl, $socialloginproVersion, $modSettings;

	echo '
	<form method="post" name="frmsettings" id="frmsettings" action="', $scripturl, '?action=slp;sa=settings2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td colspan="2"  align="center" class="catbg">
	    <b>', $txt['slp_admin'], '</b></td>
	  </tr>';


	echo '<tr>
	    <td width="50%" colspan="2"  align="center" class="windowbg2">',$txt['slp_txt_yourversion'] , $socialloginproVersion, '&nbsp;',$txt['slp_txt_latestversion'],'<span id="lastsocialloginpro" name="lastsocialloginpro"></span>
	    </td>
	    </tr>';
	
	
echo '<tr>
		<td class="windowbg2" valign="top">' . $txt['slp_apikey'] . '</td>
		<td class="windowbg2"><input type="text" name="slp_apikey" size="50" value="' . $modSettings['slp_apikey'] .'" /><br /><span class="smalltext">' .$txt['slp_txt_getgigya'] . '</span></td>
	</tr>';

echo '<tr>
		<td class="windowbg2">' . $txt['slp_secretkey'] . '</td>
		<td class="windowbg2"><input type="text" name="slp_secretkey" size="50" value="' . $modSettings['slp_secretkey'] .'" /></td>
	</tr>';



	// Show all the sites
	echo '<tr>
		<td class="windowbg2">' . $txt['slp_enabledProviders'] . '</td>
	    <td class="windowbg2">
	    <table align="center">';

		$siteLevel = 0;
		$sitesList = explode(",",$modSettings['slp_providers']);
		$enabledSites = explode(",",$modSettings['slp_enabledProviders']);
		
		foreach($sitesList as $site)
		{
			if ($siteLevel == 0)
				echo '<tr>';

			echo '<td><input type="checkbox" name="site[' . $site . ']" ' . (in_array($site,$enabledSites) ? ' checked="checked" ' : '')  . ' />' . ucfirst($site) . '</td>';

			if ($siteLevel == 0)
				$siteLevel = 1;
			else
			{
				echo '</tr>';
				$siteLevel = 0;
			}
		}

		if ($siteLevel == 1)
		{
			echo '<td></td>
			</tr>';
			$siteLevel = 0;
		}

	echo '
	    </table>
	    </td>
	  </tr>';
	
	

echo '<tr>
		<td class="windowbg2">' . $txt['slp_loginheight'] . '</td>
		<td class="windowbg2"><input type="text" name="slp_loginheight" value="' . $modSettings['slp_loginheight'] .'" /></td>
	</tr>';

echo '<tr>
		<td class="windowbg2">' . $txt['slp_loginwidth'] . '</td>
		<td class="windowbg2"><input type="text" name="slp_loginwidth" value="' . $modSettings['slp_loginwidth'] .'" /></td>
	</tr>';


echo '<tr>
		<td class="windowbg2">' . $txt['slp_registerheight'] . '</td>
		<td class="windowbg2"><input type="text" name="slp_registerheight" value="' . $modSettings['slp_registerheight'] .'" /></td>
	</tr>';

echo '<tr>
		<td class="windowbg2">' . $txt['slp_registerwidth'] . '</td>
		<td class="windowbg2"><input type="text" name="slp_registerwidth" value="' . $modSettings['slp_registerwidth'] .'" /></td>
	</tr>';


echo '<tr>
		<td class="windowbg2">' . $txt['slp_disableregistration'] . '</td>
		<td class="windowbg2"><input type="checkbox" name="slp_disableregistration" ' . ($modSettings['slp_disableregistration'] ? ' checked="checked" ' : '') .' /></td>
	</tr>
	
	<tr>
		<td class="windowbg2">' . $txt['slp_allowaccountmerge'] . '</td>
		<td class="windowbg2"><input type="checkbox" name="slp_allowaccountmerge" ' . ($modSettings['slp_allowaccountmerge'] ? ' checked="checked" ' : '') .' /></td>
	</tr>	
	<tr>
		<td class="windowbg2">' . $txt['slp_importavatar'] . '</td>
		<td class="windowbg2"><input type="checkbox" name="slp_importavatar" ' . ($modSettings['slp_importavatar'] ? ' checked="checked" ' : '') .' /></td>
	</tr>	
	';

	
	
	echo '
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	     <input type="hidden" name="sc" value="', $context['session_id'], '" />
	    <input type="submit" name="savesettings" value="',$txt['slp_save_settings'],'" /></td>
	  </tr>
	  </table>
  	</form>

<script language="JavaScript" type="text/javascript" src="http://www.smfhacks.com/versions/socialloginpro.js?t=' . time() . '"></script>
			<script language="JavaScript" type="text/javascript">

			function socialloginproCurrentVersion()
			{
				if (!window.socialloginproVersion)
					return;

				socialloginprospan = document.getElementById("lastsocialloginpro");

				if (window.socialloginproVersion != "' . $socialloginproVersion . '")
				{
					setInnerHTML(socialloginprospan, "<b><font color=\"red\">" + window.socialloginproVersion + "</font>&nbsp;' . $txt['slp_txt_version_outofdate'] . '</b>");
				}
				else
				{
					setInnerHTML(socialloginprospan, "' . $socialloginproVersion . '");
				}
			}

			// Override on the onload function
			window.onload = function ()
			{
				socialloginproCurrentVersion();
			}
			</script>

  ';

}

function template_slp_register()
{
	global $txt, $modSettings, $context, $scripturl;


echo '
	<table border="0" width="100%" cellpadding="3" cellspacing="0" class="tborder">
		<tr class="titlebg">
			<td>', $txt[97], ' - ', $txt[517], '</td>
		</tr><tr class="windowbg">
			<td width="100%">
				<table cellpadding="3" cellspacing="0" border="0" width="100%">';
	


// Social Login Pro		
global $boardurl, $modSettings;
	if (!empty($modSettings['slp_apikey']) && !empty($modSettings['slp_enabledProviders']))
				echo '
			<tr><td><strong>' . $txt['slp_txt_toregister'] . '</strong></td>
			</tr>
			<tr><td>
								<script type="text/javascript">
var login_params=
{
	useHTML: \'true\'
	,showTermsLink: \'false\'
	,height: ' . $modSettings['slp_registerheight'] . '
	,width: ' . $modSettings['slp_registerwidth'] . '
	,containerID: \'componentDiv\'
	,redirectURL: \'' . $boardurl. '/gigya.php\'
}
</script>
<div id="componentDiv"></div>
<script type="text/javascript">
   gigya.services.socialize.showLoginUI(conf,login_params);
</script>

							</td>
			</tr>';
// End Social Login Pro			

echo '</table>
	</td>
</tr>
</table>';



	// Require them to agree here?
	if ($context['require_agreement'])
		echo '
	<table width="100%" align="center" border="0" cellspacing="0" cellpadding="5" class="tborder" style="border-top: 0;">
		<tr>
			<td class="windowbg2" style="padding-top: 8px; padding-bottom: 8px;">
				', $context['agreement'], '
			</td>
		</tr>
	</table>';

	echo '
	<br />
	<div align="center">

	</div>
';


}


function template_slp_mergeaccount()
{
	global $txt, $modSettings, $context, $scripturl;
	global $boardurl, $modSettings, $ID_MEMBER, $db_prefix;
	
	

echo '
	<table border="0" width="100%" cellpadding="3" cellspacing="0" class="tborder">
		<tr class="titlebg">
			<td>', $txt['slp_merge_account'], '</td>
		</tr>
		<tr class="windowbg">
		<td>';
		

	echo '<table>
	<tr>
		<td><strong>' . $txt['slp_account'] . '</strong></td>
		<td><strong>' . $txt['slp_socialnetwork'] . '</strong></td>
		<td><strong>' . $txt['slp_options'] .'</strong></td>
	</tr>
	
	';
	
	$result = db_query("
	SELECT id, id_member, nickname, provider FROM
	{$db_prefix}social_logins WHERE id_member = " . $ID_MEMBER . ' AND merged = 1', __FILE__, __LINE__);
	while($row =  mysql_fetch_assoc($result))
	{
		echo '<tr>
					<td>' . $row['nickname'] . '</td>
					<td>' . $row['provider'] . '</td>
					<td><a href="' . $scripturl . '?action=slp;sa=delete&id=' . $row['id'] . '">' . $txt['slp_delete'] .'</a></td>
				</tr>';
	}
	
	echo '</table>
	</td>
	</tr>';
		
		
		
echo '
<tr class="titlebg">
			<td>',  $txt['slp_merge_addaccount'], '</td>
		</tr>		
		<tr class="windowbg">
			<td width="100%">
				<table cellpadding="3" cellspacing="0" border="0" width="100%">';
	


// Social Login Pro		
global $boardurl, $modSettings;
	if (!empty($modSettings['slp_apikey']) && !empty($modSettings['slp_enabledProviders']))
				echo '
			<tr><td><strong>' . $txt['slp_txt_toregister'] . '</strong></td>
			</tr>
			<tr><td>
								<script type="text/javascript">
var login_params=
{
	useHTML: \'true\'
	,showTermsLink: \'false\'
	,height: ' . $modSettings['slp_registerheight'] . '
	,width: ' . $modSettings['slp_registerwidth'] . '
	,containerID: \'componentDiv\'
	,redirectURL: \'' . $boardurl. '/gigyamerge.php\'
}
</script>
<div id="componentDiv"></div>
<script type="text/javascript">
   gigya.services.socialize.showLoginUI(conf,login_params);
</script>

							</td>
			</tr>';
// End Social Login Pro			

echo '</table>
	</td>
</tr>
</table>';



}

?>