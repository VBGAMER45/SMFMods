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
	
function UltimatePortalMainCP()
{
	global $sourcedir, $context;

	//Inicialized the Ultimate Portal?
	$context['ultimate_portal_initialized'] = false;	
	
	// Load UltimatePortal Settings
	ultimateportalSettings();
	// Load UltimatePortal template
	loadtemplate('UltimatePortalCP');
	// Load Language
	if (loadlanguage('UltimatePortalCP') == false)
		loadLanguage('UltimatePortalCP','english');

	//Load Important Files
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Subs-UltimatePortal.php');
	
	$areas = array(
		'preferences' => array('', 'ShowPreferences'),
		'ultimate_portal_blocks' => array('UltimatePortal-BlocksMain.php', 'ShowBlocksMain'),
		'multiblock' => array('', 'ShowMultiblock'),
	);

	$_REQUEST['area'] = isset($_REQUEST['area']) && isset($areas[$_REQUEST['area']]) ? $_REQUEST['area'] : 'preferences';
	$context['admin_area'] = $_REQUEST['area'];

	if (!empty($areas[$_REQUEST['area']][0]))
		require_once($sourcedir . '/' . $areas[$_REQUEST['area']][0]);

	$areas[$_REQUEST['area']][1]();


}

function ShowPreferences()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;

	if (!allowedTo('ultimate_portal_cp'))
		isAllowedTo('ultimate_portal_cp');
		
	loadTemplate('UltimatePortalCP');
	
	//Load subactions for the ultimate portal preferences
	$subActions = array(
		'main' => 'ShowPreferencesMain',
		'gral-settings' => 'ShowPreferencesGralSettings',
		'lang-maintenance' => 'ShowPreferencesLangMaintenance',
		'lang-edit' => 'UltimatePortalEditLangs',
		'permissions-settings' => 'ShowPreferencesPermissionsSettings',
		'portal-menu' => 'ShowPortalMenuSettings',
		'save-portal-menu' => 'SaveMainLinks',
		'add-portal-menu' => 'AddMainLinks',
		'edit-portal-menu' => 'EditMainLinks',
		'delete-portal-menu' => 'DeleteMainLinks',
		'seo' => 'ShowSEO',		
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ultport_admin_preferences_title'] . ' - ' . $txt['ultport_preferences_title'],
		'description' =>  $txt['ultport_admin_preferences_description'],
		'tabs' => array(
			'main' => array(
				'description' => $txt['ultport_admin_preferences_description'],
			),
			'gral-settings' => array(
				'description' => $txt['ultport_admin_gral_settings_description'],
			),
			'lang-maintenance' => array(
				'description' => $txt['ultport_admin_gral_settings_description'],
			),
			'permissions-settings' => array(
				'description' => $txt['ultport_admin_gral_settings_description'],
			),
			'portal-menu' => array(
				'description' => $txt['ultport_admin_gral_settings_description'],
			),
			'seo' => array(
				'description' => $txt['ultport_seo_description'],
			),			
		),
	);

	$subActions[$_REQUEST['sa']]();	

}

function ShowPreferencesMain()
{
	global $db_prefix, $context, $scripturl, $txt,$sourcedir;

	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	// Call the sub template.
	$context['sub_template'] = 'preferences_main';
	$context['page_title'] = $txt['ultport_admin_preferences_title'] . ' - ' . $txt['ultport_preferences_title'];

}

function ShowPreferencesGralSettings()
{
	global $db_prefix, $context, $scripturl, $txt,$sourcedir;
	
	if(!isset($_POST['save']))
		checkSession('get');
		
	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		//save the ultimate portal settings section preferences
		saveUltimatePortalSettings("config_preferences");
		redirectexit('action=admin;area=preferences;sa=gral-settings;'. $context['session_var'] .'=' . $context['session_id']);
	}
	//End Save
	
	// Call the sub template.
	$context['sub_template'] = 'preferences_gral_settings';
	$context['page_title'] = $txt['ultport_admin_gral_settings_title'] . ' - ' . $txt['ultport_preferences_title'];
	
}

function ShowPreferencesLangMaintenance()
{
	global $db_prefix, $context, $scripturl, $txt,$sourcedir;

	checkSession('get');

	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	

	//Load all Language from language folder
	UltimatePortalLangs();
	
	// Call the sub template.
	$context['sub_template'] = 'preferences_lang_maintenance';
	$context['page_title'] = $txt['ultport_admin_lang_maintenance_title'] . ' - ' . $txt['ultport_preferences_title'];

}

function UltimatePortalEditLangs()
{
	global $db_prefix, $context, $scripturl, $txt, $boarddir,$sourcedir;

	if(!isset($_POST['save']) && !isset($_POST['duplicate']) && !isset($_POST['editing']))
		checkSession('get');

	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	

	if (isset($_POST['save']))
	{
		checkSession('post');

		//Content and File
		$file = trim($_POST['file']);
		$content = trim($_POST['content']);

		//Create Edit Lang File
		CreateSpecificLang($file, $content);
		
		//Ok redirect 
		redirectexit('action=admin;area=preferences;sa=lang-maintenance;sesc=' . $context['session_id']);
		
	}	

	if (isset($_POST['duplicate']))
	{
		checkSession('post');		
		if (empty($_POST['new_file']))
			fatal_lang_error('ultport_error_no_name',false);	
	
		//Content and File
		$file = trim($_POST['file']);
		
		//Load the original lang
		LoadSpecificLang($file);
		
		$new_file_name = $_POST['new_file'] .'.php';
		//Create Edit Lang File
		CreateSpecificLang($new_file_name, $context['content']);
		
		//Ok redirect 
		redirectexit('action=admin;area=preferences;sa=lang-maintenance;sesc=' . $context['session_id']);
		
	}	

	if(isset($_POST['editing']))
		checkSession('post');

	//If not select the lang file, then redirect the selec lang form
	if (!isset($_POST['file']))
		redirectexit('action=admin;area=preferences;sa=lang-maintenance');
	
	$context['file'] = stripslashes($_POST['file']);	
	$this_file = $context['file'];

	//Load Specific Lang - from Subs-UltimatePortal.php
	LoadSpecificLang($this_file);
	
	// Call the sub template.
	$context['sub_template'] = 'preferences_lang_edit';
	$context['page_title'] = $txt['ultport_admin_lang_maintenance_edit'] . ' - ' . $txt['ultport_preferences_title'];
	
}

//Load Permissions Settings
function ShowPreferencesPermissionsSettings()
{
	global $db_prefix, $context, $scripturl, $txt, $boarddir;
	global $smcFunc,$sourcedir;

	if(!isset($_POST['save']) && !isset($_POST['view-perms']))
		checkSession('get');

	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	$context['view-perms'] = 0;
	$group_selected = '';

	//Load Permissions - Source/Subs-UltimatePortal.php
	LoadUPModulesPermissions();

	//View Perms?
	if (isset($_POST['view-perms']))
	{
		checkSession('post');		
		$context['view-perms'] = 1;
		$group_selected = (int) $_POST['group'];
		$context['group-selected'] = $group_selected;
		
		$permissions = array();
				
		$result = $smcFunc['db_query']('',"
			SELECT permission, value
			FROM {$db_prefix}up_groups_perms
			WHERE ID_GROUP = $group_selected");
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$context[$row['permission']]['value'] = $row['value'];
		}
			
		$smcFunc['db_free_result']($result);
		
	}	
	
	if (isset($_POST['save']))
	{
		checkSession('post');		
		$id_group = (int) $_POST['group_selected'];
		$smcFunc['db_query']('',"DELETE FROM {$db_prefix}up_groups_perms
			WHERE ID_GROUP = $id_group");
		
		foreach ($context['permissions'] as $permissions)
		{
			$smcFunc['db_query']('',"INSERT IGNORE INTO {$db_prefix}up_groups_perms(ID_GROUP, permission, value)
						VALUES($id_group, '". $permissions['name'] ."', ". (isset($_POST[$permissions['name']]) ? 1 : 0 ) .")");
				
		}
		
		//Redirect exit 
		redirectexit('action=admin;area=preferences;sa=permissions-settings;sesc=' . $context['session_id']);
	}

	//Load the MemberGroups
	LoadMemberGroups($group_selected);
	
	// Call the sub template.
	$context['sub_template'] = 'preferences_permissions_settings';
	$context['page_title'] = $txt['ultport_admin_permissions_settings_title'] . ' - ' . $txt['ultport_preferences_title'];

}

//Portal Menu Settings
function ShowPortalMenuSettings()
{
	global $db_prefix, $context, $scripturl, $txt, $boarddir,$sourcedir;

	checkSession('get');
	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	

	//Load the Main links from BD
	LoadMainLinks();
	// Call the sub template.
	$context['sub_template'] = 'preferences_main_links';
	$context['page_title'] = $txt['ultport_admin_portal_menu_title'] . ' - ' . $txt['ultport_preferences_title'];

}

//Save Main Links
function SaveMainLinks()
{
	global $db_prefix, $context, $scripturl, $txt, $boarddir;
	global $smcFunc,$sourcedir;
	
	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	if (!isset($_POST['save-menu']))
		redirectexit('action=admin;area=preferences;sa=portal-menu');	

	checkSession('post');
	$myquery = $smcFunc['db_query']('',"
				SELECT id 
				FROM {$db_prefix}ultimate_portal_main_links");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$id = $row['id'];
		$position_form = $id."_position";
		$active_form = $id."_active";
		$top_menu_form = $id."_top_menu";
		
		$position_form = isset($_POST[$position_form]) ? $_POST[$position_form] : 0;
		$active_form = isset($_POST[$active_form]) ? 1 : 0;
		$top_menu_form = isset($_POST[$top_menu_form]) ? 1 : 0;				
		
		$smcFunc['db_query']('',"UPDATE {$db_prefix}ultimate_portal_main_links
			SET position = $position_form, 
				active = $active_form,
				top_menu = $top_menu_form
			WHERE id = $id");
	}
					
	//redirect to Portal Menu Settings
	redirectexit('action=admin;area=preferences;sa=portal-menu;sesc=' . $context['session_id']);
	
}

//Add Main Links
function AddMainLinks()
{
	global $db_prefix, $context, $scripturl, $txt, $boarddir;
	global $smcFunc,$sourcedir;

	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	if (!isset($_POST['add-menu']))
		redirectexit('action=admin;area=preferences;sa=portal-menu');	

	checkSession('post');
	$icon = !empty($_POST['icon']) ? $_POST['icon'] : '';
	$title = !empty($_POST['title']) ? $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES) : '';
	$url = !empty($_POST['url']) ? $_POST['url'] : '';
	$position = (int) $_POST['position'];
	$active = !empty($_POST['active']) ? $_POST['active'] : '0';
		
	$result = $smcFunc['db_query']('',"INSERT INTO {$db_prefix}ultimate_portal_main_links
					VALUES(0, '$icon', '$title', '$url', $position, $active, 0)");
					
	//redirect to Portal Menu Settings
	redirectexit('action=admin;area=preferences;sa=portal-menu;sesc=' . $context['session_id']);
	
}

//Edit Main Links
function EditMainLinks()
{
	global $db_prefix, $context, $scripturl, $txt, $boarddir;
	global $smcFunc,$sourcedir;
	
	if(!isset($_POST['save']))
		checkSession('get');

	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	if (!isset($_REQUEST['id']))
		redirectexit('action=admin;area=preferences;sa=portal-menu');	

	if (isset($_REQUEST['id']))
	{
		$id = (int) $_REQUEST['id'];
		
		$myquery = $smcFunc['db_query']('',"
					SELECT * 
					FROM {$db_prefix}ultimate_portal_main_links
					WHERE id = $id");
		while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
			$edit_main_link = &$context['edit-main-links'][];
			$edit_main_link['id'] = $row['id'];
			$edit_main_link['icon'] = $row['icon'];			
			$edit_main_link['title'] = $row['title'];
			$edit_main_link['url'] = $row['url'];
			$edit_main_link['position'] = $row['position'];
			$edit_main_link['active'] = $row['active'];	
		}
		// Call the sub template.
		$context['sub_template'] = 'preferences_edit_main_links';
		$context['page_title'] = $txt['ultport_admin_portal_menu_title'] . ' - ' . $txt['ultport_preferences_title'];		
	}				
	
	//redirect to Portal Menu Settings
	if (isset($_POST['save']))
	{	
		checkSession('post');	
		$id = (int) $_POST['id'];
		$icon = !empty($_POST['icon']) ? $_POST['icon'] : '';
		$title = !empty($_POST['title']) ? $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES) : '';
		$url = !empty($_POST['url']) ? $_POST['url'] : '';
		$position = (int) $_POST['position'];
		$active = !empty($_POST['active']) ? 1 : 0;
	
		$smcFunc['db_query']('',"UPDATE {$db_prefix}ultimate_portal_main_links
			SET icon = '$icon',
				title = '$title',
				url = '$url',
				position = $position, 
				active = $active 
			WHERE id='$id'");
		
		redirectexit('action=admin;area=preferences;sa=portal-menu;sesc=' . $context['session_id']);
	}	
	
}

//Delete Main Link
function DeleteMainLinks()
{
	global $db_prefix, $context;
	global $smcFunc,$sourcedir;

	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	if (!isset($_REQUEST['id']))
		redirectexit('action=admin;area=preferences;sa=portal-menu');	

	checkSession('get');
	$id = (int) $_REQUEST['id'];
	
	$myquery = $smcFunc['db_query']('',"
				DELETE  
				FROM {$db_prefix}ultimate_portal_main_links
				WHERE id = $id");
				
	redirectexit('action=admin;area=preferences;sa=portal-menu;sesc=' . $context['session_id']);

}

//Settings SEO
function ShowSEO()
{
	global $db_prefix, $context, $scripturl, $txt,$sourcedir;
	global $boarddir, $smcFunc, $ultimateportalSettings;

	if(!isset($_POST['save_robot']) && !isset($_POST['save_seo_config']) && !isset($_POST['save_seo_google_verification_code']))
		checkSession('get');

	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	

	//Save Robot
	if (isset($_POST['save_robot']))
	{
		checkSession('post');		
		if (!empty($_POST['robots_add']))
		{
			$robots_txt = stripslashes($_POST['robots_add']);
			$filename = $boarddir . '/robots.txt';
			@chmod($filename, 0644);
			if (!$handle = fopen($filename, 'w'))
				fatal_error($txt['ultport_error_fopen_error'] . $filename   . '.',false);
		
			// Write the headers to our opened file.
			if (!fwrite($handle, $robots_txt))
			{
				fatal_error($txt['ultport_error_fopen_error'] . $filename   . '.',false);
			}
		
			fclose($handle);
		}	
	}
	//End Save
	//Save Config General
	if (isset($_POST['save_seo_config']))
	{
		checkSession('post');		
		//save the ultimate portal settings section seo
		saveUltimatePortalSettings("config_seo");
		redirectexit('action=admin;area=preferences;sa=seo;sesc=' . $context['session_id']);
	}
	//End Save
	//Save Google Verification Code
	if (isset($_POST['save_seo_google_verification_code']))
	{
		checkSession('post');		
		$verification = $smcFunc['db_escape_string']($_POST['seo_google_verification_code']);
		$extension_code = strtolower(substr(strrchr($verification, '.'), 1));		
		if (!empty($extension_code))
			fatal_error($txt['seo_google_verification_code_error'], false);											
			
		//save the ultimate portal settings section seo
		$configUltimatePortalVar['seo_google_verification_code'] = empty($ultimateportalSettings['seo_google_verification_code']) ? $verification : $ultimateportalSettings['seo_google_verification_code'].','.$verification;
		updateUltimatePortalSettings($configUltimatePortalVar, 'config_seo');
		if (!empty($verification))
		{			
			$filename = $boarddir . '/'. $verification . '.html';
			$content = 'google-site-verification: '. $verification . '.html';
			if (!$handle = fopen($filename, 'a'))
				fatal_error($txt['ultport_error_fopen_error'] . $filename,false);
			fwrite($handle, $content);	
			fclose($handle);
		}			
		redirectexit('action=admin;area=preferences;sa=seo;sesc=' . $context['session_id']);
	}
	//End Save
	//Delete google Verification Code?
	if(isset($_REQUEST['file']))
	{
		checkSession('get');
		$verification = $smcFunc['db_escape_string']($_REQUEST['file']);
		unlink($boarddir . '/'. $verification . '.html');
		$verifications_codes = explode(',', $ultimateportalSettings['seo_google_verification_code']);
		$count = count($verifications_codes);
		if($count > 1)
		{
			for($i = 0; $i <= $count; $i++)
			{
				if(!empty($verifications_codes[$i]))
				{
					//save the ultimate portal settings section seo
					if($verifications_codes[$i] == $verification)
						$position = $i;
				}
			}
		}else{
			$configUltimatePortalVar['seo_google_verification_code'] = '';
		}
		//Not first?
		if(!empty($position) && $position >= 1 && (($position != count($verifications_codes)-1) || ($position == count($verifications_codes)-1)))
		{
			$configUltimatePortalVar['seo_google_verification_code'] = str_replace(','.$verification,'', $ultimateportalSettings['seo_google_verification_code']);
		}
		//Okay, is first :P
		if($count > 1 && $position == 0)
		{
			$configUltimatePortalVar['seo_google_verification_code'] = str_replace($verification.',','', $ultimateportalSettings['seo_google_verification_code']);
		}		
		updateUltimatePortalSettings($configUltimatePortalVar, 'config_seo');						
		redirectexit('action=admin;area=preferences;sa=seo;sesc=' . $context['session_id']);
	}
	if(file_exists($boarddir . '/robots.txt'))
	{
		$context['robots_txt'] = file_get_contents($boarddir . '/robots.txt');
	}
	
	// Call the sub template.
	$context['sub_template'] = 'preferences_seo';
	$context['page_title'] = $txt['ultport_seo_title'] . ' - ' . $txt['ultport_preferences_title'];
	
}

function ShowMultiblock()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;

	if (!allowedTo('ultimate_portal_cp'))
		isAllowedTo('ultimate_portal_cp');
		
	loadTemplate('UltimatePortalCP');
	
	//Load subactions for the ultimate portal preferences
	$subActions = array(
		'main' => 'ShowMBMain',
		'add' => 'ShowMBAdd',
		'edit' => 'ShowMBEdit',
		'delete' => 'ShowMBDelete',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ultport_mb_title'],
		'description' =>  $txt['ultport_mb_main_descrip'],
		'tabs' => array(
			'main' => array(
				'description' => $txt['ultport_mb_main_descrip'],
			),
			'add' => array(
				'description' => $txt['ultport_mb_main_descrip'],
			),
		),
	);

	$subActions[$_REQUEST['sa']]();	

}

function ShowMBMain()
{
	global $db_prefix, $context, $scripturl, $txt,$sourcedir;

	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	

	//Load
	MultiBlocksLoads();
	
	// Call the sub template.
	$context['sub_template'] = 'mb_main';
	$context['page_title'] = $txt['ultport_mb_title'] . ' - ' . $txt['ultport_mb_main'];

}

function ShowMBAdd()
{
	global $db_prefix, $context, $scripturl, $txt,$sourcedir;
	global $smcFunc;
	
	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');	
	
	if (!isset($_POST['next']) && !isset($_POST['save']))
		checkSession('get');				
	
	if(isset($_POST['next']))
	{
		checkSession('post');		
		$step = $_POST['step'];
		$context['title'] = !empty($_POST['title']) ? $_POST['title'] : '';
		$context['position'] = !empty($_POST['position']) ? $_POST['position'] : '';
		$context['enable'] = isset($_POST['enable']) ? 1 : 0;
		$context['mbk_title'] = isset($_POST['mbk_title']) ? 'on' : '';
		$context['mbk_collapse'] = isset($_POST['mbk_collapse']) ? 'on' : '';
		$context['mbk_style'] = isset($_POST['mbk_style']) ? 'on' : '';
		
		if (isset($_POST['block']))
		{
			foreach ($_POST['block'] as $i => $v)
				 if (!is_numeric($_POST['block'][$i])) 
				 	unset($_POST['block'][$i]);	
			$context['id_blocks'] = implode(',', $_POST['block']);
		}
		$context['design'] = !empty($_POST['design']) ? $_POST['design'] : '';
	}
	
	if (!empty($step))
	{
		$context['sub_template'] = 'mb_add_'.$step;
	}else{
		$context['sub_template'] = 'mb_add';
	}
	
	if (isset($_POST['save']))
	{
		checkSession('post');			
		$title = !empty($_POST['title']) ?  up_db_xss($_POST['title']) : '';
		$position = !empty($_POST['position']) ?  up_db_xss($_POST['position']) : '';
		$id_blocks = !empty($_POST['blocks']) ?  up_db_xss($_POST['blocks']) : '';
		$design = !empty($_POST['design']) ?  up_db_xss($_POST['design']) : '';
		$enable = !empty($_POST['enable']) ? 1 : 0;
		$mbk_title = !empty($_POST['mbk_title']) ? 'on' : '';
		$mbk_collapse = !empty($_POST['mbk_collapse']) ? 'on' : '';
		$mbk_style = !empty($_POST['mbk_style']) ? 'on' : '';
		
		//Create New MultiBlock
		$smcFunc['db_query']('',"
			INSERT INTO {db_prefix}up_multiblock(id, title, blocks, position, design, mbk_title, mbk_collapse, mbk_style, enable)
			VALUES(0, '$title', '$id_blocks', '$position', '$design', '$mbk_title', '$mbk_collapse', '$mbk_style', $enable)");
		
		//Updates the blocks
		$id_block = explode(',', $id_blocks);
		foreach($id_block as $bk)
		{
			$smcFunc['db_query']('',"UPDATE {db_prefix}ultimate_portal_blocks
				SET position = '$position', 
					mbk_view = '". $_POST['mbk_view_'.$bk] ."'
				WHERE id = $bk");
		}
		redirectexit('action=admin;area=multiblock;sa=main;'. $context['session_var'] .'=' . $context['session_id']);

	}
	
	//Loads only right, left, and center blocks
	LoadsBlocksForMultiBlock(true);
	
	// Call the sub template.
	$context['page_title'] = $txt['ultport_mb_title'] . ' - ' . $txt['ultport_mb_add'];

}

function ShowMBEdit()
{
	global $db_prefix, $context, $scripturl, $txt,$sourcedir;
	global $smcFunc;
	
	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');	

	if(!isset($_GET['id']))
		redirectexit('action=admin;area=multiblock;sa=main;'. $context['session_var'] .'=' . $context['session_id']);
	
	//Catch id
	$context['idmbk'] = addslashes($_GET['id']);
	//Load Specific
	SpecificMultiBlocks($context['idmbk']);
	//Loads all blocks
	LoadsBlocksForMultiBlock(false);
	
	if (!isset($_POST['next']) && !isset($_POST['save']) && !isset($_POST['back']))
		checkSession('get');				
	
	if(isset($_POST['next']))
	{
		checkSession('post');		
		$step = $_POST['step'];
		$context['title'] = !empty($_POST['title']) ? up_db_xss($_POST['title']) : '';
		$context['position'] = !empty($_POST['position']) ?  up_db_xss($_POST['position']) : '';
		$context['enable'] = isset($_POST['enable']) ? 1 : 0;
		$context['mbk_title'] = isset($_POST['mbk_title']) ? 'on' : '';
		$context['mbk_collapse'] = isset($_POST['mbk_collapse']) ? 'on' : '';
		$context['mbk_style'] = isset($_POST['mbk_style']) ? 'on' : '';
		
		if (isset($_POST['block']))
		{
			foreach ($_POST['block'] as $i => $v)
				 if (!is_numeric($_POST['block'][$i])) 
				 	unset($_POST['block'][$i]);	
			$context['id_blocks'] = implode(',', $_POST['block']);
		}
		$context['design'] = !empty($_POST['design']) ?  up_db_xss($_POST['design']) : '';
	}
	
	if (!empty($step))
	{
		$context['sub_template'] = 'mb_edit_'.$step;
	}else{
		$context['sub_template'] = 'mb_edit';
	}
	
	//Save
	if (isset($_POST['save']))
	{
		checkSession('post');			
		$title = !empty($_POST['title']) ?  up_db_xss($_POST['title']) : '';
		$position = !empty($_POST['position']) ?  up_db_xss($_POST['position']) : '';
		$id_blocks = !empty($_POST['blocks']) ?  up_db_xss($_POST['blocks']) : '';
		$design = !empty($_POST['design']) ?  up_db_xss($_POST['design']) : '';
		$enable = !empty($_POST['enable']) ? 1 : 0;
		$mbk_title = !empty($_POST['mbk_title']) ? 'on' : '';
		$mbk_collapse = !empty($_POST['mbk_collapse']) ? 'on' : '';
		$mbk_style = !empty($_POST['mbk_style']) ? 'on' : '';		
		
		//Create New MultiBlock
		$smcFunc['db_query']('',"
			UPDATE {db_prefix}up_multiblock
				SET title = '$title', 
					blocks = '$id_blocks', 
					position = '$position', 
					design = '$design', 
					mbk_title = '$mbk_title', 
					mbk_collapse =  '$mbk_collapse', 
					mbk_style =  '$mbk_style', 
					enable = $enable
			WHERE id = ".$context['idmbk']);
		
		//Updates the blocks
		$id_block = explode(',', $id_blocks);
		foreach($id_block as $bk)
		{
			$smcFunc['db_query']('',"
				UPDATE {db_prefix}ultimate_portal_blocks
					SET position = '$position', 
						mbk_view = '".  up_db_xss($_POST['mbk_view_'.$bk]) ."'
				WHERE id = $bk");
		}
		
		//Unchecked block
		$old_blocks = explode(',',$context['multiblocks'][$context['idmbk']]['blocks']);
		foreach($old_blocks as $obk)
		{
			if(!in_array($obk, $id_block))
			{
				$smcFunc['db_query']('',"
					UPDATE {db_prefix}ultimate_portal_blocks
						SET position = 'left', 
							mbk_view = '',
							active = ''
					WHERE id = $obk");				
			}
		}		
		redirectexit('action=admin;area=multiblock;sa=main;'. $context['session_var'] .'=' . $context['session_id']);
	}
		
	// Call the sub template.
	$context['page_title'] = $txt['ultport_mb_title'] . ' - ' . $txt['ultport_mb_edit'] .' - '.$context['multiblocks'][$context['idmbk']]['title'];

}

function ShowMBDelete()
{
	global $db_prefix, $context;
	global $smcFunc,$sourcedir;

	//Load File
	require_once($sourcedir . '/Subs-UltimatePortal.php');	
	
	if (!isset($_REQUEST['id']))
		redirectexit('action=admin;area=multiblock;sa=main;'. $context['session_var'] .'=' . $context['session_id']);

	checkSession('get');
	$id = (int) $_REQUEST['id'];

	//Load Specific
	SpecificMultiBlocks($id);

	$id_blocks = explode(',',$context['multiblocks'][$id]['blocks']);
	foreach($id_blocks as $bk)
	{
		$smcFunc['db_query']('',"
			UPDATE {db_prefix}ultimate_portal_blocks
				SET position = 'left', 
					mbk_view = '',
					active = ''
			WHERE id = $bk");				
	}		
	
	//Now Delete	
	$myquery = $smcFunc['db_query']('',"
				DELETE FROM {db_prefix}up_multiblock
				WHERE id = $id");
				
	redirectexit('action=admin;area=multiblock;sa=main;'. $context['session_var'] .'=' . $context['session_id']);
}

?>