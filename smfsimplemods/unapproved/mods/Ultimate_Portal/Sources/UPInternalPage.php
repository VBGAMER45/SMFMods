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
	
function UPInternalPageMain()
{
	global $sourcedir, $context, $txt;
	global $ultimateportalSettings;
	
	//Inicialized the Ultimate Portal?
	$context['ultimate_portal_initialized'] = true;	

	// Load UltimatePortal Settings
	ultimateportalSettings();
	
	// Load UltimatePortal template
	loadtemplate('UPInternalPage');
	// Load Language
	if (loadlanguage('UPInternalPage') == false)
		loadLanguage('UPInternalPage','english');

	//Is active the Internal Page module?
	if(empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	//Load Important Files
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Subs-UltimatePortal.php');
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');	
	
	//Load subactions for the Ultimate Portal - Module NEWS
	$subActions = array(
		'main' => 'Main',
		'view' => 'View',
			'add' => 'Add',		
			'edit' => 'Edit',
			'delete' => 'Delete',
		'inactive' => 'Inactive',			
		'view-inactive' => 'ViewInactive',		
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$subActions[$_REQUEST['sa']]();	


}

function Main()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings;

	//Active Internal Page Module?
	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);
	//End 
	
	//News Link-tree
	$context['news-linktree'] = '<img alt="" style="float:left" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a>';	

	//Load Internal Page
	LoadInternalPage('',"WHERE active = 'on'");
	
	//It is disabled, any internal page?
	DisablePage("WHERE active = 'off'");
	
	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=internal-page',
		'name' => $txt['up_module_ipage_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'main';
	$context['page_title'] = $txt['up_module_title'] . ' - ' . $txt['up_module_ipage_title'];

}

function View()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings, $user_info;
	
	//Active Internal Page Module?
	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);
	//End 

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	$id = (int) $id;
	
	if (empty($id))
		fatal_lang_error('ultport_error_no_action',false);
	
	//Load Specific Internal Page
	LoadInternalPage($id);
	
	//Can VIEW?, disabled page?, is admin?
	if ((empty($context['view_ipage']) || ($context['can_view'] === false)) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_view',false);
	
	//News Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $context['title'];	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=internal-page',
		'name' => $txt['up_module_ipage_title']
	);

	//Social Bookmarks
	$context['social_bookmarks'] = UpSocialBookmarks($scripturl .'?action=internal-page;sa=view;id='. $id );
	
	// Call the sub template.
	$context['sub_template'] = 'view';
	$context['page_title'] = $context['title'];

}

//Modules Internal Page - Sect: Add 
function Add()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info, $ultimateportalSettings;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Active Internal Page Module?
	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);
	//End 

	//Security - Can User Add the NEW?
	if (!$user_info['is_admin'] && !$user_info['up-modules-permissions']['ipage_add'])
		fatal_lang_error('ultport_error_no_perms_groups',false);
	//End Security

	//Security - 
	if (!empty($_REQUEST['type']) && !in_array($_REQUEST['type'], array('html', 'bbc')))
		fatal_lang_error('ultport_error_no_action',false);
	//End Security

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_ipage_title',false);

		$title = up_convert_savedbadmin($_POST['title']);
		$column_left = !empty($_POST['column_left']) ? $smcFunc['htmlspecialchars']($_POST['column_left'],ENT_QUOTES) : 0;
		$column_right = !empty($_POST['column_right']) ?  up_db_xss($_POST['column_right']) : 0;
		$content = ($_POST['type_ipage'] == 'html') ? up_convert_savedbadmin($_POST['elm1']) : $smcFunc['htmlspecialchars']($_POST['ipage_content'], ENT_QUOTES);						
		$id_member = (int) $user_info['id'];
		$username =  up_db_xss($user_info['username']);
		$date_created = time();
		$type_ipage =  up_db_xss($_POST['type_ipage']);
		$permissionsArray = array();
		if (isset($_POST['perms']))
		{
			foreach ($_POST['perms'] as $rgroup)
				$permissionsArray[] = (int) $rgroup;
		}
		$finalPermissions = implode(",",$permissionsArray);
		$active = !empty($_POST['active']) ?  up_db_xss($_POST['active']) : 'off';
		$sticky = !empty($_POST['sticky']) ?  up_db_xss($_POST['sticky']) : 0;
		
		//Now insert the NEWS in the smf_up_news
		$smcFunc['db_query']('',"
		INSERT INTO {$db_prefix}ultimate_portal_ipage
			(title, sticky, active, type_ipage, content, perms, column_left, column_right, date_created, id_member, username) 
		VALUES
			('$title',$sticky,'$active','$type_ipage','$content','$finalPermissions',$column_left,$column_right,$date_created,$id_member,'$username')");
		
		//redirect 
		redirectexit('action=internal-page');		
	}

	//Load Member Groups
	LoadMemberGroups();
	
	//Type Internal Page
	$context['type_ipage'] = !empty($_REQUEST['type']) ? $smcFunc['db_escape_string']($_REQUEST['type']) : 'html';
	
	//Load the Editor HTML or BBC?
	if ($_REQUEST['type'] == 'html')
	{
		context_html_headers();
		$type_ipage_linktree = $txt['up_ipage_add_html'];
	}	
	if ($_REQUEST['type'] == 'bbc')
	{
		// Used for the custom editor
		// Now create the editor.
		$editorOptions = array(
			'id' => 'ipage_content',
			'value' => '',
			'form' => 'ipageform',
		);
		$context['smileyBox_container'] = 'smileyBox_'.$editorOptions['id'];
		$context['bbcBox_container'] = 'bbcBox_'.$editorOptions['id'];
		up_create_control_richedit($editorOptions);	
		$context['post_box_name'] = $editorOptions['id'];
		$type_ipage_linktree = $txt['up_ipage_add_bbc'];				
	}	
	//End Editor
	
	//News Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $type_ipage_linktree;	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=internal-page',
		'name' => $txt['up_module_ipage_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'add';
	$context['page_title'] = $type_ipage_linktree;

}

//Modules Internal Page - Sect: Edit
function Edit()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info, $ultimateportalSettings;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Active Internal Page Module?
	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);
	//End 

	//Security - Can User Edit the NEW?
	if (!$user_info['is_admin'] && !$user_info['up-modules-permissions']['ipage_moderate'])
		fatal_lang_error('ultport_error_no_perms_groups',false);
	//End Security

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_ipage_title',false);
		$id = (int) $_POST['id_ipage'];
		$title = up_convert_savedbadmin($_POST['title']);
		$column_left = !empty($_POST['column_left']) ? $smcFunc['htmlspecialchars']($_POST['column_left'],ENT_QUOTES) : 0;
		$column_right = !empty($_POST['column_right']) ? $smcFunc['htmlspecialchars']($_POST['column_right'],ENT_QUOTES) : 0;
		$content = ($_POST['type_ipage'] == 'html') ? up_convert_savedbadmin($_POST['elm1']) : $smcFunc['htmlspecialchars']($_POST['ipage_content'], ENT_QUOTES);						
		$id_member_updated = (int) $user_info['id'];
		$username_updated = $smcFunc['htmlspecialchars']($user_info['username'],ENT_QUOTES);
		$date_updated = time();
		$type_ipage = $smcFunc['htmlspecialchars']($_POST['type_ipage'],ENT_QUOTES);
		$permissionsArray = array();
		if (isset($_POST['perms']))
		{
			foreach ($_POST['perms'] as $rgroup)
				$permissionsArray[] = (int) $rgroup;
		}
		$finalPermissions = implode(",",$permissionsArray);
		$active = !empty($_POST['active']) ? $smcFunc['htmlspecialchars']($_POST['active'],ENT_QUOTES) : 'off';
		$sticky = !empty($_POST['sticky']) ? $smcFunc['htmlspecialchars']($_POST['sticky'],ENT_QUOTES) : 0;
		
		//Now insert the NEWS in the smf_up_news
		$smcFunc['db_query']('',"
		UPDATE {$db_prefix}ultimate_portal_ipage
		SET 	title = '$title', 
				sticky = $sticky, 
				active = '$active', 
				type_ipage = '$type_ipage', 
				content = '$content', 
				perms = '$finalPermissions', 
				column_left = $column_left, 
				column_right = $column_right, 
				date_updated = $date_updated, 
				id_member_updated = $id_member_updated, 
				username_updated = '$username_updated'
		WHERE id = $id");
		
		//redirect 
		if ($active == 'on')
			redirectexit('action=internal-page;sa=view;id='. $id);		
		else
			redirectexit('action=internal-page;sa=view-inactive;id='. $id);			
	}
	
	$id = (int) $_REQUEST['id'];
	
	//Load Specific Information
	LoadInternalPage('', $condition = "WHERE id = $id");
	
	//Load Member Groups
	LoadMemberGroups();
	
	//Load the Editor HTML or BBC?
	if ($context['type_ipage'] == 'html')
	{
		context_html_headers();
		$type_ipage_linktree = $txt['up_ipage_add_html'];
	}	
	if ($context['type_ipage'] == 'bbc')
	{
		// Used for the custom editor
		// Now create the editor.
		$editorOptions = array(
			'id' => 'ipage_content',
			'value' => $context['content'],
			'form' => 'ipageform',
		);
		$context['smileyBox_container'] = 'smileyBox_'.$editorOptions['id'];
		$context['bbcBox_container'] = 'bbcBox_'.$editorOptions['id'];
		up_create_control_richedit($editorOptions);	
		$context['post_box_name'] = $editorOptions['id'];
		$type_ipage_linktree = $txt['up_ipage_add_bbc'];				
	}	
	//End Editor
	
	//News Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $txt['ultport_button_edit'] . '( <em><a href="'. $scripturl .'?action=internal-page;sa='. (($context['active'] == 'off' && $user_info['is_admin']) ? 'view-inactive' : 'view') .';id='. $context['id'] .'">'. $context['title'] .'</a></em> )';	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=internal-page',
		'name' => $txt['up_module_ipage_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'edit';
	$context['page_title'] = $txt['ultport_button_edit'] . '('. $context['title'] .')';

}

//Modules Internal Page - Sect: Delete Page
function Delete()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info;
	global $smcFunc;

	checkSession('get');
	if (empty($_REQUEST['id']))
		fatal_lang_error('ultport_error_no_delete_ippage',false);

	//Security - Can User Delete the Internal Page?
	if (!$user_info['is_admin'] && !$user_info['up-modules-permissions']['ipage_moderate'])
		fatal_lang_error('ultport_error_no_perms_groups',false);
	//End Security
	
	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	$id = (int) $id;
	
	//Now delete
	$smcFunc['db_query']('',"DELETE FROM {$db_prefix}ultimate_portal_ipage WHERE id = $id");	

	//redirect 
	redirectexit('action=internal-page');		
						

}

function Inactive()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings;

	//Active Internal Page Module?
	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);
	//End 
	
	//News Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $txt['up_ipage_disabled_any_ipage_title'];	

	$context['is_inactive_page'] = 1;
	//Load Internal Page
	LoadInternalPage('',"WHERE active = 'off'");
	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=internal-page',
		'name' => $txt['up_module_ipage_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'main';
	$context['page_title'] = $txt['up_module_title'] . ' - ' . $txt['up_module_ipage_title'];

}

function ViewInactive()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings, $user_info;
	
	//Active Internal Page Module?
	if (empty($ultimateportalSettings['ipage_enable']))
		fatal_lang_error('ultport_error_no_active',false);
	//End 

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	$id = (int) $id;
	
	if (empty($id))
		fatal_lang_error('ultport_error_no_action',false);
	
	//Load Specific Internal Page
	LoadInternalPage($id, "WHERE active = 'off'");
	
	//Can VIEW?, disabled page?, is admin?
	if ((empty($context['view_ipage']) || ($context['can_view'] === false)) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_view',false);
	
	//News Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=internal-page">'. $txt['up_module_ipage_title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $context['title'];	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=internal-page',
		'name' => $txt['up_module_ipage_title']
	);

	//Social Bookmarks
	$context['social_bookmarks'] = UpSocialBookmarks($scripturl .'?action=internal-page;sa=view;id='. $id );
	
	// Call the sub template.
	$context['sub_template'] = 'view';
	$context['page_title'] = $txt['up_module_ipage_title'] .' - '. $context['title'];

}

?>