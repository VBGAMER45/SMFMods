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
	
function UltimatePortalMainModules()
{
	global $sourcedir, $context;

	//Inicialized the Ultimate Portal?
	$context['ultimate_portal_initialized'] = false;	
	// Load UltimatePortal Settings
	ultimateportalSettings();
	// Load UltimatePortal template
	loadtemplate('UltimatePortalModules');
	// Load Language
	if (loadlanguage('UltimatePortalModules') == false)
		loadLanguage('UltimatePortalModules','english');

	//Load Important Files
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Subs-UltimatePortal.php');
	
	$areas = array(
		'upmodulesenable' => array('', 'ShowEnableModules'),		   
		'user-posts' => array('', 'ShowUserPosts'),
		'up-news' => array('', 'ShowNews'),		
		'board-news' => array('', 'ShowBoardNews'),		
		'download' => array('', 'ShowDownload'),		
		'internal-page' => array('', 'ShowInternalPage'),	
		'up-affiliates' => array('', 'ShowAffiliates'),
		'up-aboutus' => array('', 'ShowAboutUs'),		
		'up-faq' => array('', 'ShowFaq'),		
	);

	$_REQUEST['area'] = isset($_REQUEST['area']) && isset($areas[$_REQUEST['area']]) ? $_REQUEST['area'] : 'preferences';
	$context['admin_area'] = $_REQUEST['area'];

	if (!empty($areas[$_REQUEST['area']][0]))
		require_once($sourcedir . '/' . $areas[$_REQUEST['area']][0]);

	$areas[$_REQUEST['area']][1]();


}

//Core Features
function ShowEnableModules()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $sourcedir;
	//Inicialized the Ultimate Portal?
	$context['ultimate_portal_initialized'] = false;	
	// Load UltimatePortal Settings
	ultimateportalSettings();
	// Load UltimatePortal template
	loadtemplate('UltimatePortalModules');
	// Load Language
	if (loadlanguage('UltimatePortalModules') == false)
		loadLanguage('UltimatePortalModules','english');

	loadLanguage('ManageSettings');
	//Load Important Files
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Subs-UltimatePortal.php');

	//Load Array
	LoadEnableModules();

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{	
		checkSession('post');
		foreach($context['array_modules'] as $id => $modules)
		{
			$configUltimatePortalVar[$id] = !empty($_POST[$id]) ? $_POST[$id] : '';
			updateUltimatePortalSettings($configUltimatePortalVar, $modules['section']);		
		}		
		
		//redirect
		redirectexit('action=admin;area=upmodulesenable;' . $context['session_var'] . '=' . $context['session_id']);
	}

	// Put them in context.
	$context['modules'] = array();
	foreach ($context['array_modules'] as $id => $modules)
		$context['modules'][$id] = array(
			'title' => $modules['title'],
			'desc' => $modules['desc'],
			'images' => $modules['images'],
			'enabled' => $modules['settings']['enable'],
			'url' => !empty($modules['url']) ? $scripturl . '?' . $modules['url'] . ';' . $context['session_var'] . '=' . $context['session_id'] : '',
		);
	
	// Call the sub template.
	$context['sub_template'] = 'enableModules_main';
	$context['page_title'] = $txt['ultport_enablemodules_title'];

}

//Users Posts SubActions
function ShowUserPosts()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;

	if (!allowedTo('ultimate_portal_modules'))
		isAllowedTo('ultimate_portal_modules');
		
	loadTemplate('UltimatePortalModules');
	
	//Load subactions for the ultimate portal user posts
	$subActions = array(
		'up-main' => 'ShowUserPostsMain',
		'extra-field' => 'ShowExtraField',
			'add-extra-field' => 'AddExtraField',
			'edit-extra-field' => 'EditExtraField',
			'del-extra-field' => 'DeleteExtraField',			
		'up-perms' => '',						
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'up-main';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ultport_admin_user_posts_title'] . ' - ' . $txt['ultport_admin_module_title2'],
		'description' => '',
		'tabs' => array(
			'up-main' => array(
				'description' => $txt['ultport_admin_user_posts_descrip'],
			),
			'extra-field' => array(
				'description' => $txt['ultport_admin_up_extra_field_description'],
			),
			'up-perms' => array(
				'description' => '',
			),
		),
	);

	$subActions[$_REQUEST['sa']]();	

}

//Users Posts Main
function ShowUserPostsMain()
{
	global $db_prefix, $context, $scripturl, $txt;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		//save the ultimate portal settings section users posts module			
		saveUltimatePortalSettings('config_user_posts');
	}
	
	// Call the sub template.
	$context['sub_template'] = 'user_posts_main';
	$context['page_title'] = $txt['ultport_admin_user_posts_title'] . ' - ' . $txt['ultport_admin_user_posts_main'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Users Posts Extra Field - Select Type User Post and Select Languange Field
function ShowExtraField()
{
	global $db_prefix, $context, $scripturl, $txt;

	checkSession('get');
	//Load the ExtraField rows from Source/Subs-UltimatePortal.php
	LoadExtraField();	
	
	// Call the sub template.
	$context['sub_template'] = 'up_extra_field';
	$context['page_title'] = $txt['ultport_admin_user_posts_title'] . ' - ' . $txt['ultport_admin_up_extra_field_title'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Modules - Area User Posts - Sect: Add Extra Field 
function AddExtraField()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_title',false);
		if (empty($_POST['icon']))
			fatal_lang_error('ultport_error_no_add_icon',false);
		
		$icon =  up_db_xss($_POST['icon']);
		$title = $smcFunc['db_escape_string']($_POST['title']);
		$field =  up_db_xss($_POST['field']);
		
		//Now insert the new section in the smf_up_news_sections 
		$smcFunc['db_query']('',"INSERT INTO {$db_prefix}uposts_extra_field (title, icon, field) VALUES ('$title', '$icon', '$field')");
		
		//redirect the News Admin Section
		redirectexit('action=admin;area=user-posts;sa=extra-field;sesc=' . $context['session_id']);		
	}
	
	// Call the sub template.
	$context['sub_template'] = 'add_extra_field';
	$context['page_title'] = $txt['ultport_admin_up_extra_field_title'];

}

//Modules - Area User Posts - Sect: Edit Extra Field 
function EditExtraField()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_title',false);
		if (empty($_POST['icon']))
			fatal_lang_error('ultport_error_no_add_icon',false);
		
		$id = (int) $_POST['id'];	
		$icon =  up_db_xss($_POST['icon']);
		$title = $smcFunc['db_escape_string']($_POST['title']);
		$field =  up_db_xss($_POST['field']);
		
		//Now update 
		$smcFunc['db_query']('',"UPDATE {$db_prefix}uposts_extra_field 
				SET title = '$title', 
					icon = '$icon', 
					field = '$field'
				WHERE id = $id");
		
		//redirect the Extra field area from User Posts
		redirectexit('action=admin;area=user-posts;sa=extra-field;sesc=' . $context['session_id']);		
	}

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	
	$myquery = $smcFunc['db_query']('',"SELECT id, title, icon, field 
						FROM {$db_prefix}uposts_extra_field 
						WHERE id = '$id'");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['id'] = $row['id'];
		$context['title'] = $row['title'];
		$context['icon'] = $row['icon'];
		$context['field'] = $row['field'];				
	}

		
	// Call the sub template.
	$context['sub_template'] = 'edit_extra_field';
	$context['page_title'] = $txt['ultport_admin_up_extra_field_title'];

}

//Modules - Area User Posts - Sect: Delete ExtraField
function DeleteExtraField()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc;
	
	checkSession('get');	
	if (empty($_REQUEST['id']))
		fatal_lang_error('ultport_error_no_delete',false);

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	
	//Now is delete
	$smcFunc['db_query']('',"DELETE FROM {$db_prefix}uposts_extra_field WHERE id = '$id'");	

	//redirect the Extra field area from User Posts
	redirectexit('action=admin;area=user-posts;sa=extra-field;sesc=' . $context['session_id']);		
						

}

//Modules - Area News
function ShowNews()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;

	if (!allowedTo('ultimate_portal_modules'))
		isAllowedTo('ultimate_portal_modules');
		
	loadTemplate('UltimatePortalModules');
	
	//Load subactions for the ultimate portal board news
	$subActions = array(
		'ns-main' => 'ShowNewsMain',
		'section' => 'ShowNewsSection',
		'add-section' => 'ShowAddSection',
		'edit-section' => 'EditSection',
		'delete-section' => 'DeleteSection',
		'admin-news' => 'ShowAdminNews',		
		'add-news' => 'ShowAddNews',
		'edit-news' => 'EditUPNews',						
		'delete-news' => 'DeleteNews',								
		'announcements' => 'ShowAnnouncements'
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'ns-main';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_module_title2'],
		'description' => $txt['ultport_admin_news_descrip'],
		'tabs' => array(
			'ns-main' => array(
				'description' => $txt['ultport_admin_news_descrip'],
			),
			'section' => array(
				'description' => $txt['ultport_admin_news_section_descrip'],
			),
			'admin-news' => array(
				'description' => $txt['ultport_admin_news_descrip2'],
			),
			'announcements' => array(
				'description' => $txt['ultport_admin_announcements_descrip'],
			),
		),
	);

	$subActions[$_REQUEST['sa']]();	

}

//Modules - Area News - Sect: Gral Settings
function ShowNewsMain()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $sourcedir;
	global $ultimateportalSettings;

	if(!isset($_POST['save']))
		checkSession('get');	

	if (isset($_POST['save']))
	{
		checkSession('post');
		//save the ultimate portal settings section news
		$configUltimatePortalVar['up_news_limit'] = !empty($_POST['up_news_limit']) ? $_POST['up_news_limit'] : 10;	
		updateUltimatePortalSettings($configUltimatePortalVar, 'config_up_news');		
	}

	// Call the sub template.
	$context['sub_template'] = 'news_main';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_news_main'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Modules - Area News - Sect: Announcements
function ShowAnnouncements()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $sourcedir, $smcFunc;
	global $ultimateportalSettings;

	if(!isset($_POST['save']))
		checkSession('get');	

	if (isset($_POST['save']))
	{
		checkSession('post');
		//save the ultimate portal settings section news
		$configUltimatePortalVar['up_news_global_announcement'] = censorText($_POST['up_news_global_announcement']);
		updateUltimatePortalSettings($configUltimatePortalVar, 'config_up_news');		
	}

	// Needed for the editor and message icons.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'up_news_global_announcement',
		'value' => $ultimateportalSettings['up_news_global_announcement'],
		'form' => 'newsform',		
	);
	create_control_richedit($editorOptions);
	// Store the ID.
	$context['post_box_name'] = $editorOptions['id'];


	// Call the sub template.
	$context['sub_template'] = 'announcement';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_announcements_title'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Modules - Area News - Sect: Config Add - Delete - News Section 
function ShowNewsSection()
{
	global $db_prefix, $context, $scripturl, $txt;

	checkSession('get');	
	//Load the News Section - Source/Subs-UltimatePortal.php
	LoadNewsSection();

	// Call the sub template.
	$context['sub_template'] = 'news_section';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_news_section_title'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Modules - Area News - Sect: Add News Section 
function ShowAddSection()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_news_section_title',false);
		
		$icon = !empty($_POST['icon']) ? $_POST['icon'] : $settings['default_images_url'].'/ultimate-portal/news-icon.png';
		$title = $smcFunc['db_escape_string']($_POST['title']);
		$position = (int) $_POST['position'];
		
		//Now insert the new section in the smf_up_news_sections 
		$smcFunc['db_query']('',"INSERT INTO {$db_prefix}up_news_sections (title, icon, position) VALUES ('$title', '$icon', '$position')");
		
		//redirect the News Admin Section
		redirectexit('action=admin;area=up-news;sa=section;sesc=' . $context['session_id']);		
	}
	//only load the $context['last_position']
	LoadNewsSection();	
	
	// Call the sub template.
	$context['sub_template'] = 'add_news_section';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_add_sect_title'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Modules - Area News - Sect: Edit News Section 
function EditSection()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_news_section_title',false);

		$id = (int) $_POST['id'];
		$icon = !empty($_POST['icon']) ? $_POST['icon'] : $settings['default_images_url'].'/ultimate-portal/news-icon.png';
		$title = $smcFunc['db_escape_string']($_POST['title']);
		$position = (int) $_POST['position'];
		
		//Now update the new section in the smf_up_news_sections 
		$smcFunc['db_query']('',"UPDATE {$db_prefix}up_news_sections 
				SET title = '$title', 
					icon = '$icon', 
					position = '$position'
				WHERE id = $id");
		
		//redirect the News Admin Section
		redirectexit('action=admin;area=up-news;sa=section;sesc=' . $context['session_id']);		
	}

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	
	$myquery = $smcFunc['db_query']('',"SELECT id, title, icon, position 
						FROM {$db_prefix}up_news_sections 
						WHERE id = '$id'");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['id'] = $row['id'];
		$context['title'] = $row['title'];
		$context['icon'] = $row['icon'];
		$context['position'] = $row['position'];				
	}
		
	// Call the sub template.
	$context['sub_template'] = 'edit_news_section';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_add_sect_title'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Modules - Area News - Sect: Delete News Section 
function DeleteSection()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc;

	checkSession('get');
	if (empty($_REQUEST['id']))
		fatal_lang_error('ultport_error_no_delete_section',false);

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	
	//Now is delete the section and the news for this section
	$smcFunc['db_query']('',"DELETE FROM {$db_prefix}up_news_sections WHERE id = '$id'");
	$smcFunc['db_query']('',"DELETE FROM {$db_prefix}up_news WHERE id_category = '$id'");	

	//redirect the News Admin Section
	redirectexit('action=admin;area=up-news;sa=section;sesc=' . $context['session_id']);		
						

}

//Modules - Area News - Sect: Config Add - Delete - News  
function ShowAdminNews()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings;

	checkSession('get');
	//Load News - Source/Subs-UltimatePortal.php
	LoadNews();

	// Call the sub template.
	$context['sub_template'] = 'admin_news';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_admin_news_title'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Modules - Area News - Sect: Add News  
function ShowAddNews()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_news_title',false);

		$title = $smcFunc['db_escape_string']($_POST['title']);
		$id_cat = (int) $_POST['id_cat'];
		$body = up_convert_savedbadmin($_POST['elm1']);
		$id_member = (int) $_POST['id_member'];
		$username =  up_db_xss($_POST['username']);
		$date = time();
		
		//Now insert the NEWS in the smf_up_news
		$smcFunc['db_query']('',"INSERT INTO {$db_prefix}up_news(id_category, id_member, title, username, body, date) VALUES($id_cat, $id_member, '$title', '$username', '$body', $date)");
		
		//redirect the News Admin Section
		redirectexit('action=admin;area=up-news;sa=admin-news;sesc=' . $context['session_id']);		
	}


	//Load the sections
	$context['section'] = '';
	$myquery = $smcFunc['db_query']('',"SELECT id, title 
						FROM {$db_prefix}up_news_sections 
						ORDER BY id ASC");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['section'] .= '<option value="'. $row['id'] .'">'. $row['title'] .'</option>';
	}
	
	//Load the html headers and load the Editor
	context_html_headers();
	
	// Call the sub template.
	$context['sub_template'] = 'add_news';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_add_news_title2'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Modules - Area News - Sect: Edit News  
function EditUPNews()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_news_title',false);

		$id = (int) $_POST['id'];
		$title = $smcFunc['db_escape_string']($_POST['title']);
		$id_cat = (int) $_POST['id_cat'];
		$body = up_convert_savedbadmin($_POST['elm1']);
		$id_member_updated = (int) $_POST['id_member_updated'];
		$username_updated =  up_db_xss($_POST['username_updated']);
		$date_updated = time();
		
		//Now insert the NEWS in the smf_up_news
		$smcFunc['db_query']('',"UPDATE {$db_prefix}up_news
				SET id_category = $id_cat, 
					title = '$title', 
					body = '$body', 
					id_member_updated = $id_member_updated,
					username_updated = '$username_updated',										
					date_updated = $date_updated
				WHERE id = $id");
		
		//redirect the News Admin Section
		redirectexit('action=admin;area=up-news;sa=admin-news;sesc=' . $context['session_id']);		
	}

	//Load the News
	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	
	$myquery = $smcFunc['db_query']('',"SELECT * 
						FROM {$db_prefix}up_news 
						WHERE id = $id");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['id'] = $row['id'];
		$context['id_category'] = $row['id_category'];
		$context['id_member'] = $row['id_member'];
		$context['title'] = stripslashes($row['title']);
		$context['username'] = $row['username'];
		$context['body'] = stripslashes($row['body']);		
		$context['date'] = $row['date'];		
	}

	//Load the sections
	$context['section-edit'] = '';
	$myquery2 = $smcFunc['db_query']('',"SELECT id, title 
						FROM {$db_prefix}up_news_sections 
						ORDER BY id ASC");

	while( $row2 = $smcFunc['db_fetch_assoc']($myquery2) ) {
		$active = '';						
		if ($context['id_category'] == $row2['id'])
		{
			$active = 'selected="selected"';
		}
			
		$context['section-edit'] .= '<option '. $active .' value="'. $row2['id'] .'">'. $row2['title'] .'</option>';
	}
	
	//Load the html headers and load the Editor
	context_html_headers();
	
	// Call the sub template.
	$context['sub_template'] = 'edit_news';
	$context['page_title'] = $txt['ultport_admin_news_title'] . ' - ' . $txt['ultport_admin_edit_news_title'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Modules - Area News - Sect: Delete News 
function DeleteNews()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc;

	checkSession('get');
	if (empty($_REQUEST['id']))
		fatal_lang_error('ultport_error_no_delete_news',false);

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	
	//Now is delete the news
	$smcFunc['db_query']('',"DELETE FROM {$db_prefix}up_news WHERE id = '$id'");	

	//redirect the News Admin News
	redirectexit('action=admin;area=up-news;sa=admin-news;sesc=' . $context['session_id']);		
						

}

//Modules - Area Board News
function ShowBoardNews()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;

	if (!allowedTo('ultimate_portal_modules'))
		isAllowedTo('ultimate_portal_modules');
		
	loadTemplate('UltimatePortalModules');
	
	//Load subactions for the ultimate portal board news
	$subActions = array(
		'bn-main' => 'ShowBoardNewsMain',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'bn-main';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ultport_admin_board_news_title'] . ' - ' . $txt['ultport_admin_module_title2'],
		'description' => $txt['ultport_admin_board_news_descrip'],
		'tabs' => array(
			'bn-main' => array(
				'description' => $txt['ultport_admin_board_news_descrip'],
			),
		),
	);

	$subActions[$_REQUEST['sa']]();	

}

function ShowBoardNewsMain()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $smcFunc;

	if(!isset($_POST['bn-save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['bn-save']))
	{
		checkSession('post');
		if (isset($_POST['boards']))
		{
			foreach ($_POST['boards'] as $i => $v)
				 if (!is_numeric($_POST['boards'][$i])) 
				 	unset($_POST['boards'][$i]);	
			$id_boards = implode(',', $_POST['boards']);
		}
				
		//save the ultimate portal settings section board news
		saveUltimatePortalSettings("config_board_news");		
		
		//save the select multiple in the ultimate portal table settings
		$board_news_view = $id_boards;		
		$configUltimatePortalVar['board_news_view'] = $board_news_view;
		updateUltimatePortalSettings($configUltimatePortalVar, 'config_board_news');		

		redirectexit('action=admin;area=board-news;sa=bn-main;sesc=' . $context['session_id']);
	}
	//End Save
	
	// Based on the up_loadJumpTo() from SMF 1.1.X
	up_loadJumpTo();
	
	// Call the sub template.
	$context['sub_template'] = 'board_news_main';
	$context['page_title'] = $txt['ultport_admin_board_news_title'] . ' - ' . $txt['ultport_admin_board_news_main'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Modules - Area Download
function ShowDownload()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;

	if (!allowedTo('ultimate_portal_modules'))
		isAllowedTo('ultimate_portal_modules');
		
	loadTemplate('UltimatePortalModules');
	
	//Load subactions for the ultimate portal board news
	$subActions = array(
		'main' => 'ShowDownloadMain',
		'section' => 'ShowSection',
		'add' => 'AddDownloadSection',
		'edit' => 'EditDownloadSection',
		'delete' => 'DeleteDownloadSection',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['up_download_title'] . ' - ' . $txt['ultport_admin_module_title2'],
		'description' => '',
		'tabs' => array(
			'main' => array(
				'description' => $txt['up_down_settings_descrip'],
			),
			'section' => array(
				'description' => $txt['up_down_section_description'],
			),
		),
	);

	$subActions[$_REQUEST['sa']]();	

}

function ShowDownloadMain()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $sourcedir, $ultimateportalSettings;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{		
		checkSession('post');
		
		//save the ultimate portal settings section download module
		saveUltimatePortalSettings('config_download');
		redirectexit('action=admin;area=download;sa=main;sesc=' . $context['session_id']);
	}
	//End Save

	// Used for the custom editor
	// Now create the editor.
	$editorOptions = array(
		'id' => 'download_pm_body',
		'value' => $ultimateportalSettings['download_pm_body'],
		'form' => 'downform',
	);
	$context['smileyBox_container'] = 'smileyBox_'.$editorOptions['id'];
	$context['bbcBox_container'] = 'bbcBox_'.$editorOptions['id'];
	up_create_control_richedit($editorOptions);	
	$context['post_box_name'] = $editorOptions['id'];		

	// Call the sub template.
	$context['sub_template'] = 'download_main';
	$context['page_title'] = $txt['up_download_title'] . ' - ' . $txt['up_down_settings_tab'] . ' - ' . $txt['ultport_admin_module_title2'];

}

function ShowSection()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $sourcedir;

	checkSession('get');
	//Load the Download Sections	
	LoadDownloadSection('view');
	//Call the up_loadJumpTo, we need the boards id and name / llamando a la funcion up_loadJumpTo para poder visualizar los foros
	up_loadJumpTo();
	
	// Call the sub template.
	$context['sub_template'] = 'download_section';
	$context['page_title'] = $txt['up_download_title'] . ' - ' . $txt['up_down_section_tab'] . ' - ' . $txt['ultport_admin_module_title2'];

}

function AddDownloadSection()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $sourcedir, $settings;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');		
		
	//Save 
	if (isset($_POST['save']))
	{	
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_title',false);
		
		$title = $smcFunc['htmlspecialchars']($_POST['title'],ENT_QUOTES);
		$icon = !empty($_POST['icon']) ? $smcFunc['htmlspecialchars']($_POST['icon'],ENT_QUOTES) : ($settings['default_theme_url'] . '/images/ultimate-portal/download/package.png');
		$description = !empty($_POST['section_body']) ? $smcFunc['htmlspecialchars']($_POST['section_body'],ENT_QUOTES) : '';
		$id_board = (int) $_POST['id_board_posts']; 
		$permissionsArray = array();
		if (isset($_POST['perms']))
		{
			foreach ($_POST['perms'] as $rgroup)
				$permissionsArray[] = (int) $rgroup;
		}
		$finalPermissions = implode(",",$permissionsArray);

		//Now insert the section in the smf_up_download_sections 
		$smcFunc['db_query']('',"INSERT INTO {$db_prefix}up_download_sections (title, description, icon, id_groups, id_board) 
							   VALUES ('$title', '$description', '$icon', '$finalPermissions', $id_board)");
				
		redirectexit('action=admin;area=download;sa=section;sesc=' . $context['session_id']);
	}
	//End Save

	//Call the up_loadJumpTo, we need the boards id and name / llamando a la funcion up_loadJumpTo para poder visualizar los foros
	up_loadJumpTo();

	//Load the MemberGroups
	LoadMemberGroups(); 
	
	// Used for the custom editor
	// Now create the editor.
	$editorOptions = array(
		'id' => 'section_body',
		'value' => '',
		'form' => 'sectform',
	);
	$context['smileyBox_container'] = 'smileyBox_'.$editorOptions['id'];
	$context['bbcBox_container'] = 'bbcBox_'.$editorOptions['id'];
	up_create_control_richedit($editorOptions);	
	$context['post_box_name'] = $editorOptions['id'];		
	
	// Call the sub template.
	$context['sub_template'] = 'download_add_section';
	$context['page_title'] = $txt['up_download_title'] . ' - ' . $txt['up_down_section_tab'] . ' - ' . $txt['ultport_admin_module_title2'];

}

function EditDownloadSection()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $sourcedir, $settings;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{	
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_title',false);
		
		$id = $smcFunc['db_escape_string']($_POST['id']);		
		$title = $smcFunc['htmlspecialchars']($_POST['title'],ENT_QUOTES);
		$icon = !empty($_POST['icon']) ? $smcFunc['htmlspecialchars']($_POST['icon'],ENT_QUOTES) : ($settings['default_theme_url'] . '/images/ultimate-portal/download/package.png');
		$description = !empty($_POST['section_body']) ? $smcFunc['htmlspecialchars']($_POST['section_body'],ENT_QUOTES) : '';
		$id_board = (int) $_POST['id_board_posts']; 		
		$permissionsArray = array();
		if (isset($_POST['perms']))
		{
			foreach ($_POST['perms'] as $rgroup)
				$permissionsArray[] = (int) $rgroup;
		}
		$finalPermissions = implode(",",$permissionsArray);
		
		//Now insert the section in the smf_up_download_sections 
		$smcFunc['db_query']('',"UPDATE {$db_prefix}up_download_sections 
				SET	title = '$title', 
					description  = '$description', 
					icon = '$icon', 
					id_groups = '$finalPermissions',
					id_board = $id_board
				WHERE id = $id");
				
		redirectexit('action=admin;area=download;sa=section;sesc=' . $context['session_id']);
	}
	//End Save

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	
	//Load selected Section
	LoadDownloadSection('edit',$id);
	
	//Load the MemberGroups
	LoadMemberGroups(); 

	//Call the up_loadJumpTo, we need the boards id and name / llamando a la funcion up_loadJumpTo para poder visualizar los foros
	up_loadJumpTo();

	// Used for the custom editor
	// Now create the editor.
	$editorOptions = array(
		'id' => 'section_body',
		'value' => $context['description-original'],
		'form' => 'sectform',
	);
	$context['smileyBox_container'] = 'smileyBox_'.$editorOptions['id'];
	$context['bbcBox_container'] = 'bbcBox_'.$editorOptions['id'];
	up_create_control_richedit($editorOptions);	
	$context['post_box_name'] = $editorOptions['id'];		

	// Call the sub template.
	$context['sub_template'] = 'download_edit_section';
	$context['page_title'] = $txt['up_download_title'] . ' - ' . $txt['up_down_section_tab'] . ' - ' . $txt['ultport_admin_module_title2'];

}

//Modules - Area Download - Delete Section
function DeleteDownloadSection()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info;
	global $smcFunc;

	checkSession('get');	
	//Is Admin?
	if ($user_info['is_admin'])
	{
		if (empty($_REQUEST['id']))
			fatal_lang_error('ultport_error_no_delete',false);
	
		$id = $smcFunc['db_escape_string']($_REQUEST['id']);
		
		//Now is delete the news
		$smcFunc['db_query']('',"DELETE FROM {$db_prefix}up_download_sections WHERE id = '$id'");	
	
		//redirect the News Admin News
		redirectexit('action=admin;area=download;sa=section;sesc=' . $context['session_id']);		
	}					
}

//Internal Page SubActions
function ShowInternalPage()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;

	if (!allowedTo('ultimate_portal_modules'))
		isAllowedTo('ultimate_portal_modules');
		
	loadTemplate('UltimatePortalModules');
	
	//Load subactions for the ultimate portal Internal Page
	$subActions = array(
		'main' => 'ShowInternalPageMain',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ipage_title'] . ' - ' . $txt['ultport_admin_module_title2'],
		'description' => '',
		'tabs' => array(
			'main' => array(
				'description' => $txt['ipage_settings_description'],
			),
		),
	);

	$subActions[$_REQUEST['sa']]();	

}

//Internal Page Main
function ShowInternalPageMain()
{
	global $db_prefix, $context, $scripturl, $txt;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		
		//save the ultimate portal settings section internal page module
		saveUltimatePortalSettings('config_ipage');					
	}
	
	// Call the sub template.
	$context['sub_template'] = 'ipage_main';
	$context['page_title'] = $txt['ipage_title'] . ' - ' . $txt['ipage_settings_title'] . ' - ' . $txt['ultport_admin_module_title2'];
}

// Affiliates Mod Start Code 
function ShowAffiliates()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;

	if (!allowedTo('ultimate_portal_modules')) 
		isAllowedTo('ultimate_portal_modules');
		
	loadTemplate('UltimatePortalModules');
	
	//Load subactions for the ultimate portal Affiliates
	$subActions = array(
		'aff-main' => 'ShowAffiliatesMain',
		'aff_affiliates' => 'ShowAff_Affiliates',
			'add_aff' => 'AddAffiliates',
			'edit_aff' => 'EditAffiliates',
			'del_aff' => 'DeleteAffiliates',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'aff-main';

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ultport_admin_affiliates_main'] . ' - ' . $txt['ultport_admin_module_title2'],
		'description' => '',
		'tabs' => array(
				'aff-main' => array(
					'description' => $txt['ultport_admin_affiliates_descrip'],
				),				
				'aff_affiliates' => array(
					'description' => $txt['ultport_admin_aff_description'],
			),
		),
	);

	$subActions[$_REQUEST['sa']]();	

}

//Modules - Area Affiliates - Main
function ShowAffiliatesMain()
{
	global $db_prefix, $context, $scripturl, $txt;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		//save the ultimate portal settings section users posts module
		saveUltimatePortalSettings("config_affiliates");	
		redirectexit('action=admin;area=up-affiliates;sa=aff-main;sesc=' . $context['session_id']);
	}
	
	// Call the sub template.
	$context['sub_template'] = 'affiliates_main';
	$context['page_title'] = $txt['ultport_admin_affiliates_title'];

}

function ShowAff_Affiliates()
{
	global $db_prefix, $context, $scripturl, $txt;

	checkSession('get');
	//Load the Affiliates rows from Source/Subs-UltimatePortal.php
	LoadAffiliates();	
	
	// Call the sub template.
	$context['sub_template'] = 'aff_affiliates';
	$context['page_title'] = $txt['ultport_admin_affiliates_title'];

}

function AddAffiliates()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		$url = $_POST['url'];
		$title = $smcFunc['db_escape_string']($_POST['title']);
		$imageurl = $_POST['imageurl'];	
		$alt = $_POST['alt'];
		
		//Now add Affiliate 
		$smcFunc['db_query']('', "INSERT INTO {db_prefix}up_affiliates (title, url, imageurl,alt) VALUES ('$title', '$url', '$imageurl', '$alt')");
		
		//redirect
		redirectexit('action=admin;area=up-affiliates;sa=aff_affiliates;sesc=' . $context['session_id']);		
	}
	
	// Call the sub template.
	$context['sub_template'] = 'add_aff';
	$context['page_title'] = $txt['ultport_admin_add_title'];

}

//Affiliates - Edit Afiliate
function EditAffiliates()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');		
		$id = (int) $_POST['id'];
		$url =  up_db_xss($_POST['url']);
		$title = $smcFunc['db_escape_string']($_POST['title']);
		$imageurl =  up_db_xss($_POST['imageurl']);
		$alt =  up_db_xss($_POST['alt']);
		
		//Now update 
		$smcFunc['db_query']('', "UPDATE {db_prefix}up_affiliates
				SET title = '$title', 
					url = '$url', 
					imageurl = '$imageurl',
					alt = '$alt'
				WHERE id = $id");
		
		
		//redirect
		redirectexit('action=admin;area=up-affiliates;sa=aff_affiliates;sesc=' . $context['session_id']);		
	}

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	
	$myquery = $smcFunc['db_query']('', "SELECT id, title, url, imageurl, alt
						FROM {$db_prefix}up_affiliates
						WHERE id = '$id'");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['id'] = $row['id'];
		$context['title'] = $row['title'];
		$context['url'] = $row['url'];
		$context['imageurl'] = $row['imageurl'];
		$context['alt'] = $row['alt'];
	}

		
	// Call the sub template.
	$context['sub_template'] = 'edit_aff';
	$context['page_title'] = $txt['ultport_admin_aff_main_title'];

}

// Affiliates: Delete affiliate
function DeleteAffiliates()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $user_info;

	checkSession('get');
	
	if (!$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_perm',false);
	
	if (empty($_REQUEST['id']))
		fatal_lang_error('ultport_error_no_delete',false);

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	
	//Now is delete
	$smcFunc['db_query']('', "DELETE FROM {$db_prefix}up_affiliates WHERE id = '$id'");	

	//redirect
	redirectexit('action=admin;area=up-affiliates;sa=aff_affiliates;sesc=' . $context['session_id']);		
						

}

//About Us Module SubActions
function ShowAboutUs()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;

	if (!allowedTo('ultimate_portal_modules'))
		isAllowedTo('ultimate_portal_modules');
		
	loadTemplate('UltimatePortalModules');
	
	//Load subactions for the ultimate portal user posts
	$subActions = array(
		'main' => 'ShowAboutUsMain',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['up_about_title'] . ' - ' . $txt['ultport_admin_module_title2'],
		'description' => '',
		'tabs' => array(
			'main' => array(
				'description' => $txt['up_aboutus_settings_descrip'],
			),
		),
	);

	$subActions[$_REQUEST['sa']]();	

}

//Modules - Area About Us - Main
function ShowAboutUsMain()
{
	global $db_prefix, $context, $scripturl, $sourcedir, $txt;
	global $ultimateportalSettings, $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		//save the ultimate portal settings
		$configUltimatePortalVar['about_us_registered'] = isset($_POST['about_us_registered']) ? 'on' : '';
		$configUltimatePortalVar['about_us_view_mail'] = isset($_POST['about_us_view_mail']) ? 'on' : '';
		$configUltimatePortalVar['about_us_view_pm'] = isset($_POST['about_us_view_pm']) ? 'on' : '';
		$configUltimatePortalVar['about_us_show_nick'] = 'on';
		$configUltimatePortalVar['about_us_show_rank'] = 'on';
		$configUltimatePortalVar['about_us_extrainfo_title'] = !empty($_POST['about_us_extrainfo_title']) ? $smcFunc['htmlspecialchars']($_POST['about_us_extrainfo_title'], ENT_QUOTES) : '';
		$configUltimatePortalVar['about_us_extra_info'] = !empty($_POST['about_us_extra_info']) ? (censorText($_POST['about_us_extra_info'])) : '';
		//Groups View
		$groupsArray = array();
		if (isset($_POST['about_us_group_view']))
		{
			foreach ($_POST['about_us_group_view'] as $rgroup)
				$groupsArray[] = (int) $rgroup;
		}
		$configUltimatePortalVar['about_us_group_view'] = implode(",",$groupsArray);
		//End
		updateUltimatePortalSettings($configUltimatePortalVar, 'config_about_us');		
		redirectexit('action=admin;area=up-aboutus;sa=main;sesc=' . $context['session_id']);
	}

	// We need the membergroups / Para poder usar un vector (array) que contenga el ID y nombre del grupo
	LoadMemberGroups(0, 'about_us');

	// Needed for the editor and message icons.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'about_us_extra_info',
		'value' => !empty($ultimateportalSettings['about_us_extra_info']) ? censorText($ultimateportalSettings['about_us_extra_info']) : '',
		'form' => 'aboutform',		
	);
	create_control_richedit($editorOptions);
	// Store the ID.
	$context['post_box_name'] = $editorOptions['id'];

	// Call the sub template.
	$context['sub_template'] = 'aboutus_main';
	$context['page_title'] = $txt['up_about_title'];
}

//FAQ Module SubActions
function ShowFaq()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;

	if (!allowedTo('ultimate_portal_modules'))
		isAllowedTo('ultimate_portal_modules');
		
	loadTemplate('UltimatePortalModules');
	
	//Load subactions for the ultimate portal user posts
	$subActions = array(
		'main' => 'ShowFaqMain',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['up_faq_title'] . ' - ' . $txt['ultport_admin_module_title2'],
		'description' => '',
		'tabs' => array(
			'main' => array(
				'description' => $txt['up_faq_description'],
			),
		),
	);

	$subActions[$_REQUEST['sa']]();	

}

//Modules - Area FAQ - Main
function ShowFaqMain()
{
	global $db_prefix, $context, $scripturl, $sourcedir, $txt;
	global $ultimateportalSettings, $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Save 
	if (isset($_POST['save']))
	{
		checkSession('post');
		//save the ultimate portal settings
		$configUltimatePortalVar['faq_title'] = !empty($_POST['faq_title']) ? $smcFunc['htmlspecialchars']($_POST['faq_title'], ENT_QUOTES) : '';
		$configUltimatePortalVar['faq_small_description'] = !empty($_POST['faq_small_description']) ? ($smcFunc['htmlspecialchars']($_POST['faq_small_description'], ENT_QUOTES)) : '';
		updateUltimatePortalSettings($configUltimatePortalVar, 'config_faq');		
		redirectexit('action=admin;area=up-faq;sa=main;sesc=' . $context['session_id']);
	}

	// Call the sub template.
	$context['sub_template'] = 'faq_main';
	$context['page_title'] = $txt['up_faq_title'];
}

?>