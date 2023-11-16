<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.4
*	Project manager: vicram10
*	Copyright 2011-2021
*	Powered by SMFSimple.com
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');
	
function UltimatePortalMain()
{
	global $sourcedir, $context, $ultimateportalSettings, $boardurl, $boarddir,$mbname;
	global $scripturl, $txt, $settings, $user_info;
	
	if(WIRELESS)
		redirectexit('action=forum');
			
	// Load UltimatePortal Settings
	ultimateportalSettings();

	// Load Language
	if (loadlanguage('UltimatePortal') == false)
		loadLanguage('UltimatePortal','english');

	if (!empty($context['linktree']) && !empty($ultimateportalSettings['ultimate_portal_enable']))
    {  
		foreach ($context['linktree'] as $key => $tree)
		 if (strpos($tree['url'], '#') !== false && strpos($tree['url'], 'action=forum#') === false)
			$context['linktree'][$key]['url'] = str_replace('#', '?action=forum#', $tree['url']);
	}
	//Inicialized the Ultimate Portal?
	$context['ultimate_portal_initialized'] = true;
	
	//Load Headers UP
	$context['html_headers'] .= '
	<meta name="generator" content="Ultimate Portal By Smfsimple.com" />
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
		function collapse(id,span)
		{
			var hide = new Array();
			hide[1] = "up_left";
			hide[2] = "up_right";
			mode = document.getElementById(hide[id]).style.display == "" ? 0 : 1;' . ($user_info['is_guest'] ? '
			document.cookie = hide[id] + "=" + (mode ? 0 : 1);' : '
			smf_setThemeOption(hide[id], mode ? 0 : 1, null, "' . $context['session_id'] . '", "' . $context['session_var'] . '");') . '
			document.getElementById(span).innerHTML = (mode ? "'. $txt['ultport_hide'] .'" : "'. $txt['ultport_unhide'] .'"); 						
			document.getElementById(hide[id]).style.display = mode ? "" : "none";
		}
		function collapseBlock(id,img_id)
		{
			var hide = new Array();
			hide[id] = "up_bk_"+ id;
			mode = document.getElementById(hide[id]).style.display == "" ? 0 : 1;' . ($user_info['is_guest'] ? '
			document.cookie = hide[id] + "=" + (mode ? 0 : 1);' : '
			smf_setThemeOption(hide[id], mode ? 0 : 1, null, "' . $context['session_id'] . '", "' . $context['session_var'] . '");') . '
			document.getElementById(img_id).src = (mode ? "' . $settings['default_theme_url'] . '/images/ultimate-portal/collapse.gif" : "' . $settings['default_theme_url'] . '/images/ultimate-portal/expand.gif");
			document.getElementById(hide[id]).style.display = mode ? "" : "none";
		}				
	// ]]></script>'. 
		((!empty($ultimateportalSettings['favicons'])) ? '<link rel="shortcut icon" href="'.$settings['default_theme_url'].'/images/ultimate-portal/favicon.png" />' : '' ).
		'
		<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url'] .'/css/UltimatePortalCSS.css" />
		<!-- Add this to have a specific theme-->';		
	//End Load headers UP

	//Seo Google Analytics
	if(!empty($ultimateportalSettings['seo_google_analytics']))
	{
		$context['insert_after_template'] .= '
			<!-- Ultimate Portal Seo Google Analytics -->
			<script type="text/javascript"><!-- // --><![CDATA[
				var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
				document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
				</script>
				<script type="text/javascript">
				try {
				var pageTracker = _gat._getTracker("'. $ultimateportalSettings['seo_google_analytics'] .'");
				pageTracker._trackPageview();
				} catch(err) {}
			// ]]></script>';
	}
}

function UltimatePortal_Home_Page()
{
	global $context, $mbname, $sourcedir, $ultimateportalSettings;

	//Loads Blocks
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');
	//Loads Functions for Ultimate Portal
	require_once($sourcedir . '/Subs-UltimatePortal.php');
	
	// Setup Page Title
	if (empty($ultimateportalSettings['ultimate_portal_home_title']))
		$context['page_title'] = $mbname;
	else 
		$context['page_title'] = $ultimateportalSettings['ultimate_portal_home_title'];

	loadtemplate('UltimatePortal');
	$context['sub_template']  = 'ultimate_portal_frontpage';

}

?>