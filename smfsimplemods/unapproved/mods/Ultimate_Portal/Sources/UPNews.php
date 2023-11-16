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
	
function UPNewsMain()
{
	global $sourcedir, $context, $txt;
	global $ultimateportalSettings;
	
	//Inicialized the Ultimate Portal?
	$context['ultimate_portal_initialized'] = true;	

	// Load UltimatePortal Settings
	ultimateportalSettings();
	
	// Load UltimatePortal template
	loadtemplate('UPNews');
	// Load Language
	if (loadlanguage('UPNews') == false)
		loadLanguage('UPNews','english');

	//Is active the NEWS module?
	if(empty($ultimateportalSettings['up_news_enable']))
		fatal_lang_error('ultport_error_no_active_news',false);

	//Load Important Files
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Subs-UltimatePortal.php');
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');	
	
	//Load subactions for the Ultimate Portal - Module NEWS
	$subActions = array(
		'main' => 'ShowNewsMain',
		'show-cat' => 'ShowCat',
		'view-new' => 'ViewNew',
			'add-new' => 'AddNew',		
			'edit-new' => 'EditNew',
			'delete-new' => 'DeleteNew',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$subActions[$_REQUEST['sa']]();	


}

function ShowNewsMain()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc;
	
	//News Link-tree
	$context['news-linktree'] = '<img alt="'. $txt['up_module_news_title'] .'" style="float:left" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=news">'. $txt['up_module_news_title'] .'</a>';	
	
	//Load the section
	$context['news_rows'] = 0;	
	$myquery = $smcFunc['db_query']('',"SELECT id, title, icon 
						FROM {$db_prefix}up_news_sections 
						ORDER BY position");			
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['news_rows'] = 1;		
		$section = &$context['news-section'][];
		$section['id'] = $row['id'];
		$section['title'] = '<a href="'. $scripturl .'?action=news;sa=show-cat;id='. $row['id'] .'">'. stripslashes($row['title']) .'</a>';
		$section['icon'] = '<img alt="'.$row['title'].'" src="'. $row['icon'] .'" width="35" height="35"/>';		

		$myquery2 = $smcFunc['db_query']('',"SELECT id, title, date
							FROM {$db_prefix}up_news
							WHERE id_category = ". $section['id'] ."
							ORDER BY id DESC
							LIMIT 1");
		while( $row2 = $smcFunc['db_fetch_assoc']($myquery2) ) {
			$section['last_new'] = '<a href="'. $scripturl .'?action=news;sa=view-new;id='. $row2['id'] .'">'. stripslashes($row2['title']) .'</a>';
			$section['date'] = timeformat($row2['date']);
		}
		
	}

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=news',
		'name' => $txt['up_module_news_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'news_main';
	$context['page_title'] = $txt['up_module_title'] . ' - ' . $txt['up_module_news_title'];

}

function ShowCat()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	global $smcFunc;
	
	$id_cat = $smcFunc['db_escape_string']($_REQUEST['id']);
	$id_cat = (int) $id_cat;
	
	//Load the section
	$myquery = $smcFunc['db_query']('',"SELECT id, title, icon 
						FROM {$db_prefix}up_news_sections 
						WHERE id = $id_cat");			
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['id'] = $row['id'];
		$context['title'] = stripslashes($row['title']);
		$context['title2'] = '<a href="'. $scripturl .'?action=news;sa=show-cat;id='. $row['id'] .'">'. stripslashes($row['title']) .'</a>';
		$context['icon'] = '<img src="'. $row['icon'] .'" alt="" width="35" height="35"/>';		
	}

	//Prepare the constructPageIndex() function
	$start = (int) $_REQUEST['start'];
	$db_count = $smcFunc['db_query']('',"SELECT count(id)
						FROM {$db_prefix}up_news
						WHERE id_category = $id_cat
						ORDER BY id DESC");
	$numNews = array();
	list($numNews) = $smcFunc['db_fetch_row']($db_count);
	$smcFunc['db_free_result']($db_count);

	$context['page_index'] = constructPageIndex($scripturl . '?action=news;sa=show-cat;id='. $context['id'], $start, $numNews, $ultimateportalSettings['up_news_limit']);

	// Calculate the fastest way to get the messages!
	$limit = $ultimateportalSettings['up_news_limit'];
	//End Prepare constructPageIndex() function
		
	//Load the NEWS
	$myquery2 = $smcFunc['db_query']('',"SELECT *
						FROM {$db_prefix}up_news
						WHERE id_category = $id_cat 
						ORDER BY id DESC ". ($limit < 0 ? "" : "LIMIT $start, $limit "));
	while( $row2 = $smcFunc['db_fetch_assoc']($myquery2) ) {
		//display the news for this category? and no log errors if this context is "0"
		$context['display-news'] = !empty($row2['id']) ? 1 : 0;
		//end
		$news = &$context['news'][];
		$news['id'] = $row2['id'];
		$news['title'] = '<a href="'. $scripturl .'?action=news;sa=view-new;id='. $row2['id'] .'">'. stripslashes($row2['title']) .'</a>';
		$news['id_member'] = $row2['id_member'];
		//load the member information
		if(!empty($row2['id_member']))
		{
			loadMemberData($news['id_member']);
			loadMemberContext($news['id_member']);
		}						
		$news['username'] = $row2['username'];
		$news['author'] = '<a href="'. $scripturl .'?action=profile;u='. $row2['id_member'] .'">'. stripslashes($row2['username']) .'</a>';
		$news['date'] = timeformat($row2['date']);		
		$news['added-news'] = $txt['up_module_news_added_portal_for'];
		$news['added-news'] = str_replace('[MEMBER]', $news['author'], $news['added-news']);
		$news['added-news'] = str_replace('[DATE]', $news['date'], $news['added-news']);
		$news['body'] = stripslashes($row2['body']);
		$news['id_member_updated'] = !empty($row2['id_member_updated']) ? $row2['id_member_updated'] : '';		
		$news['username_updated'] = !empty($row2['username_updated']) ? $row2['username_updated'] : '';				
		$news['author_updated'] = '<a href="'. $scripturl .'?action=profile;u='. $row2['id_member_updated'] .'">'. stripslashes($row2['username_updated']) .'</a>';		
		$news['date_updated'] = !empty($row2['date_updated']) ? timeformat($row2['date_updated']) : '';						
		$news['updated-news'] = !empty($news['id_member_updated']) ? $txt['up_module_news_updated_for'] : '';		
		$news['updated-news'] = str_replace('[UPDATED_MEMBER]', $news['author_updated'], $news['updated-news']);
		$news['updated-news'] = str_replace('[UPDATED_DATE]', $news['date_updated'], $news['updated-news']);
		$news['view'] = '<img alt="'. $txt['ultport_button_view'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/view.png" />&nbsp;<a href="'. $scripturl .'?action=news;sa=view-new;id='. $row2['id'] .'">'. $txt['ultport_button_view'] .'</a>';		
		$news['edit'] = '<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png" alt="" />&nbsp;<a href="'. $scripturl .'?action=news;sa=edit-new;id='. $row2['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';		
		$news['delete'] = '<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png" alt="" />&nbsp;<a onclick="return makesurelink()" href="'. $scripturl .'?action=news;sa=delete-new;id='. $row2['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';				
	}

	//News Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="'. $txt['up_module_news_title'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=news">'. $txt['up_module_news_title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $context['title2'];	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=news',
		'name' => $txt['up_module_news_title']
	);

	// Call the sub template.
	$context['sub_template'] = 'show_cat';
	$context['page_title'] = $txt['up_module_title'] . ' - ' . $txt['up_module_news_title'] .' - '. $context['title'];

}

function ViewNew()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc;
	
	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	$id = (int) $id;
	
	//Load the NEWS
	$myquery2 = $smcFunc['db_query']('',"SELECT *
						FROM {$db_prefix}up_news
						WHERE id = $id
						ORDER BY id DESC");
	while( $row2 = $smcFunc['db_fetch_assoc']($myquery2) ) {
		$context['id_cat'] = $row2['id_category'];
		//display the news for this category? and no log errors if this context is "0"
		$context['display-news'] = !empty($row2['id']) ? 1 : 0;
		//end
		$news = &$context['news'][];
		$news['id'] = $row2['id'];
		$news['title'] = '<a href="'. $scripturl .'?action=news;sa=view-new;id='. $row2['id'] .'">'. stripslashes($row2['title']) .'</a>';
		$context['title_news'] = $news['title'];
		$context['page-title-news'] = $row2['title'];
		$news['id_member'] = $row2['id_member'];
		//load the member information
		if(!empty($row2['id_member']))
		{
			loadMemberData($news['id_member']);
			loadMemberContext($news['id_member']);
		}				
		$news['username'] = $row2['username'];
		$news['author'] = '<a href="'. $scripturl .'?action=profile;u='. $row2['id_member'] .'">'. stripslashes($row2['username']) .'</a>';
		$news['date'] = timeformat($row2['date']);		
		$news['added-news'] = $txt['up_module_news_added_portal_for'];
		$news['added-news'] = str_replace('[MEMBER]', $news['author'], $news['added-news']);
		$news['added-news'] = str_replace('[DATE]', $news['date'], $news['added-news']);
		$news['body'] = stripslashes($row2['body']);
		$news['id_member_updated'] = !empty($row2['id_member_updated']) ? $row2['id_member_updated'] : '';		
		$news['username_updated'] = !empty($row2['username_updated']) ? $row2['username_updated'] : '';				
		$news['author_updated'] = '<a href="'. $scripturl .'?action=profile;u='. $row2['id_member_updated'] .'">'. stripslashes($row2['username_updated']) .'</a>';		
		$news['date_updated'] = !empty($row2['date_updated']) ? timeformat($row2['date_updated']) : '';						
		$news['updated-news'] = !empty($news['id_member_updated']) ? $txt['up_module_news_updated_for'] : '';		
		$news['updated-news'] = str_replace('[UPDATED_MEMBER]', $news['author_updated'], $news['updated-news']);
		$news['updated-news'] = str_replace('[UPDATED_DATE]', $news['date_updated'], $news['updated-news']);
		$news['view'] = '<img alt="'. $txt['ultport_button_view'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/view.png" />&nbsp;<a href="'. $scripturl .'?action=news;sa=view-new;id='. $row2['id'] .'">'. $txt['ultport_button_view'] .'</a>';		
		$news['edit'] = '<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png" alt="" />&nbsp;<a href="'. $scripturl .'?action=news;sa=edit-new;id='. $row2['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';		
		$news['delete'] = '<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png" alt="" />&nbsp;<a onclick="return makesurelink()" href="'. $scripturl .'?action=news;sa=delete-new;id='. $row2['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';				
	}

	//Load the section
	$myquery = $smcFunc['db_query']('',"SELECT id, title, icon 
						FROM {$db_prefix}up_news_sections 
						WHERE id = ". $context['id_cat'] ."");			
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['id'] = $row['id'];
		$context['title'] = stripslashes($row['title']);
		$context['title2'] = '<a href="'. $scripturl .'?action=news;sa=show-cat;id='. $row['id'] .'">'. stripslashes($row['title']) .'</a>';
		$context['icon'] = '<img alt="'.$row['title'].'" src="'. $row['icon'] .'" width="35" height="35"/>';		
	}

	//News Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="'. $txt['up_module_news_title'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=news">'. $txt['up_module_news_title'] .'</a> &raquo; '. $context['title2'] .'<br /><img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" alt="" />&nbsp;'. $context['title_news'];	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=news',
		'name' => $txt['up_module_news_title']
	);

	//Social Bookmarks
	$context['social_bookmarks'] = UpSocialBookmarks($scripturl .'?action=news;sa=view-new;id='. $id );
	
	// Call the sub template.
	$context['sub_template'] = 'view_news';
	$context['page_title'] = $txt['up_module_news_title'] .' - '. $context['title'] .' - '. $context['page-title-news'];

}

//Modules News - Sect: Add News  
function AddNew()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Security - Can User Add the NEW?
	if (empty($user_info['up-modules-permissions']['news_add']) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_perms_groups',false);
	//End Security

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
		$username = $smcFunc['htmlspecialchars']($_POST['username'],ENT_QUOTES);
		$date = time();
		
		//Now insert the NEWS in the smf_up_news
		$smcFunc['db_query']('',"INSERT INTO {$db_prefix}up_news(id_category, id_member, title, username, body, date) 
				VALUES($id_cat, $id_member, '$title', '$username', '$body', $date)");
		
		//redirect the New Category
		redirectexit('action=news;sa=show-cat;id='. $id_cat .'');		
	}

	$id_cat = isset($_REQUEST['id-cat']) ? $smcFunc['db_escape_string']($_REQUEST['id-cat']) : 0;
	$id_cat = (int) $id_cat;
	
	//Load the sections
	$context['section'] = '';
	$context['title-section'] = '';
	$myquery = $smcFunc['db_query']('',"SELECT id, title 
						FROM {$db_prefix}up_news_sections 
						ORDER BY id ASC");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$active = '';
		if (!empty($id_cat) && ($row['id'] == $id_cat))
		{
			$active = 'selected="selected"';
			$context['title-section'] = '<a href="'. $scripturl .'?action=news;sa=show-cat;id='. $row['id'] .'">'. stripslashes($row['title']) .'</a>';
		}	
		$context['section'] .= '<option '. $active .' value="'. $row['id'] .'">'. $row['title'] .'</option>';
	}
	
	//Load the html headers and load the Editor - Source/Subs-UltimatePortal.php
	context_html_headers();

	//News Link-tree
	$context['news-linktree'] = '&nbsp;<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" alt="" />&nbsp;<a href="'. $scripturl .'?action=news">'. $txt['up_module_news_title'] .'</a> &raquo; '. $context['title-section'] .' <br /><img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" alt="" />&nbsp;'. $txt['up_module_news_add'];	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=news',
		'name' => $txt['up_module_news_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'add_news';
	$context['page_title'] = $txt['up_module_news_add'];

}


//Modules News - Sect: Edit News  
function EditNew()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Security - Can User Edit the NEW?
	if (empty($user_info['up-modules-permissions']['news_moderate']) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_perms_groups',false);
	//End Security

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
		$username_updated = $smcFunc['htmlspecialchars']($_POST['username_updated'],ENT_QUOTES);
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
		redirectexit('action=news;sa=view-new;id='. $id .'');		
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
		$context['title-news'] = '<a href="'. $scripturl .'?action=news;sa=view-new;id='. $row['id'] .'">'. stripslashes($row['title']) .'</a>';
		$context['username'] = $row['username'];
		$context['body'] = stripslashes($row['body']);		
		$context['date'] = $row['date'];		
	}

	//Load the sections
	$context['section-edit'] = '';
	$myquery2 = $smcFunc['db_query']('',"SELECT id, title 
						FROM {$db_prefix}up_news_sections 
						ORDER BY id ASC");
	//for the linktree
	$section = '';					
	while( $row2 = $smcFunc['db_fetch_assoc']($myquery2) ) {
		$active = '';
		if ($context['id_category'] == $row2['id'])
		{
			$active = 'selected="selected"';
			$section = '<a href="'. $scripturl .'?action=news;sa=show-cat;id='. $row2['id'] .'">'. stripslashes($row2['title']) .'</a>';
		}
			
		$context['section-edit'] .= '<option '. $active .' value="'. $row2['id'] .'">'. $row2['title'] .'</option>';
		
	}
	
	//Load the html headers and load the Editor - Source/Subs-UltimatePortal.php
	context_html_headers();

	//News Link-tree
	$context['news-linktree'] = '&nbsp;<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" alt="" />&nbsp;<a href="'. $scripturl .'?action=news">'. $txt['up_module_news_title'] .'</a> &raquo; '. $section .'<br /><img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" alt="" />&nbsp;'. $txt['up_module_news_edit'] . '&nbsp;<em>('. $context['title-news'] . ')</em>';	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=news',
		'name' => $txt['up_module_news_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'edit_news';
	$context['page_title'] = $txt['up_module_news_edit'] . ' - ' . $context['title'];

}

//Modules News - Sect: Delete News 
function DeleteNew()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info;
	global $smcFunc;

	checkSession('get');
	if (empty($_REQUEST['id']))
		fatal_lang_error('ultport_error_no_delete_news',false);

	//Security - Can User Delete the NEW?
	if (empty($user_info['up-modules-permissions']['news_moderate']) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_groups_delete',false);
	//End Security
	
	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	$id = (int) $id;
	
	//load the category for redirect after delete
	$myquery = $smcFunc['db_query']('',"SELECT id_category
						FROM {$db_prefix}up_news 
						WHERE id = $id");	
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$id_cat = $row['id_category'];
	}	
	
	//Now is delete the news
	$smcFunc['db_query']('',"DELETE FROM {$db_prefix}up_news WHERE id = '$id'");	

	//redirect the News Admin News
	redirectexit('action=news;sa=show-cat;id='. $id_cat .'');		
						

}


?>