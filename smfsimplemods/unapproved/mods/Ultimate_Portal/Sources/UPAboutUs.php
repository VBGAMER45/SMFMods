<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');
	
function UPAboutUsMain()
{
	global $sourcedir, $context, $txt;
	global $ultimateportalSettings;
	
	//Inicialized the Ultimate Portal?
	$context['ultimate_portal_initialized'] = true;	

	// Load UltimatePortal Settings
	ultimateportalSettings();
	
	// Load UltimatePortal template
	loadtemplate('UPAboutUs');
	// Load Language
	if (loadlanguage('UPAboutUs') == false)
		loadLanguage('UPAboutUs','english');

	//Is active the Internal Page module?
	if(empty($ultimateportalSettings['about_us_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	//Load Important Files
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Subs-UltimatePortal.php');
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');	
	
	//Load subactions for the Ultimate Portal - Module NEWS
	$subActions = array(
		'main' => 'Main',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$subActions[$_REQUEST['sa']]();	


}

function Main()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings;

	//Active Internal Page Module?
	if (empty($ultimateportalSettings['about_us_enable']))
		fatal_lang_error('ultport_error_no_active',false);
	//End 
	
	//Load STAFF
	LoadStaffMembers();	
	
	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=about',
		'name' => $txt['up_module_about_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'main';
	$context['page_title'] = $txt['up_module_title'] . ' - ' . $txt['up_module_about_title'];

}

?>