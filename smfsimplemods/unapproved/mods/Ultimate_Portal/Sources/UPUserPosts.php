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
	
function UPUserPostsMain()
{
	global $sourcedir, $context, $txt;
	global $ultimateportalSettings;
	
	//Inicialized the Ultimate Portal?
	$context['ultimate_portal_initialized'] = true;	

	// Load UltimatePortal Settings
	ultimateportalSettings();
	
	// Load UltimatePortal template
	loadtemplate('UPUserPosts');
	// Load Language
	if (loadlanguage('UPUserPosts') == false)
		loadLanguage('UPUserPosts','english');

	//Is active the NEWS module?
	if(empty($ultimateportalSettings['user_posts_enable']))
		fatal_lang_error('ultport_error_no_active_user_posts',false);

	//Load Important Files
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Subs-UltimatePortal.php');
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');	
	
	//Load subactions for the Ultimate Portal - Module NEWS
	$subActions = array(
		'main' => 'ShowUPMain',
		'add' => 'AddUserPosts',
		'search' => 'SearchUserPosts',
		'edit' => 'EditUserPosts',
		'delete' => 'DeleteUserPosts',
		'view-single' => 'ViewSingleUserPosts',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$subActions[$_REQUEST['sa']]();	


}

function ShowUPMain()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	
	//News Link-tree
	$context['news-linktree'] = '<img alt="'. $txt['up_module_user_posts_title'] .'" style="float:left" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=user-posts">'. $txt['up_module_user_posts_title'] .'</a>';	

	//Load the UserPosts Rows
	LoadUserPostsRows();

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=user-posts',
		'name' => $txt['up_module_title'] . ' - ' . $txt['up_module_user_posts_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'user_posts_view';
	$context['page_title'] = $txt['up_module_title'] . ' - ' . $txt['up_module_user_posts_title'];

}

function SearchUserPosts()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	global $smcFunc;
	
	//User Posts Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="'. $txt['up_module_user_posts_title'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=user-posts">'. $txt['up_module_user_posts_title'] .'</a> <br /><img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" alt="'. $txt['ultport_button_search'] .'" />&nbsp;<a href="'. $scripturl .'?action=user-posts;sa=search">'. $txt['ultport_button_search'] .'</a>';	

	$context['search_result'] = 0;
	if (!empty($_POST['search']))
	{
		$context['search_result'] = 1;
		$context['search_result_specific'] = 0;
		//Search: Title AND select ALL from extra field or empty extra field
		if(!empty($_POST['title']) && (empty($_POST['extra_field']) || $_POST['extra_field'] == 'all'))
		{
			$context['title'] = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);;
			$context['extra_field'] = $smcFunc['htmlspecialchars']($_POST['extra_field'], ENT_QUOTES);
			$sql = $smcFunc['db_query']('',"SELECT id, title, type_post, lang_post, author, id_member_add, username_add, date_add, link_topic 
							FROM {$db_prefix}up_user_posts
							WHERE title like '%". $context['title'] ."%'");
			while( $row = $smcFunc['db_fetch_assoc']($sql) ) {
				$context['search_result_specific'] = 1;
				$context['result'][$row['id']] = array(
					'id' => $row['id'],
					'title' => $row['title'],
					'author' => $row['author'],
					'link_topic' => $row['link_topic'],
					'id_member_add' => $row['id_member_add'],
					'username_add' => $row['username_add'],
					'username_add_link' => '<a href="'. $scripturl .'?action=profile;u='.$row['id_member_add'].'" target="_blank">'. $row['username_add'] .'</a>',
					'date_add' => timeformat($row['date_add']),
				);
			}
			$smcFunc['db_free_result']($sql);				
		}
		/*
		Isn't Empty Title? 
		Extra field is not empty ? 
		Isn't selected option "All"? 
		Ok this is the SQL				
		*/
		if(!empty($_POST['title']) && (!empty($_POST['extra_field']) || $_POST['extra_field'] != 'all'))
		{
			$context['title'] = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);;
			$context['extra_field'] = $smcFunc['htmlspecialchars']($_POST['extra_field'], ENT_QUOTES);
			$sql = $smcFunc['db_query']('',"SELECT id, title, type_post, lang_post, author, id_member_add, username_add, date_add, link_topic 
							FROM {$db_prefix}up_user_posts
							WHERE title like '%". $context['title'] ."%'");
			while( $row = $smcFunc['db_fetch_assoc']($sql) ) {
				$type_posts = explode('#', $row['type_post']);
				$lang_posts = explode('#', $row['lang_post']);
				if ($_POST['extra_field'] == $type_posts[2] || $_POST['extra_field'] == $lang_posts[2])
				{
					$context['search_result_specific'] = 1;
					$context['result'][$row['id']] = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'author' => $row['author'],
						'link_topic' => $row['link_topic'],						
						'id_member_add' => $row['id_member_add'],
						'username_add' => $row['username_add'],
						'username_add_link' => '<a href="'. $scripturl .'?action=profile;u='.$row['id_member_add'].'" target="_blank">'. $row['username_add'] .'</a>',
						'date_add' => timeformat($row['date_add']),
					);				}
			}
			$smcFunc['db_free_result']($sql);							
		}		
		/*
		Empty Title? 
		Extra field is not empty ? 
		Isn't selected option "All"? 
		Ok this is the SQL				
		*/		
		if(empty($_POST['title']) && (!empty($_POST['extra_field']) || $_POST['extra_field'] != 'all'))
		{
			$context['title'] = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);;
			$context['extra_field_filter'] = $smcFunc['htmlspecialchars']($_POST['extra_field'], ENT_QUOTES);
			$sql = $smcFunc['db_query']('',"SELECT id, title, type_post, lang_post, author, id_member_add, username_add, date_add, link_topic 
							FROM {$db_prefix}up_user_posts");
			while( $row = $smcFunc['db_fetch_assoc']($sql) ) {
				$type_posts = explode('#', $row['type_post']);
				$lang_posts = explode('#', $row['lang_post']);
				if ($_POST['extra_field'] == $type_posts[2] || $_POST['extra_field'] == $lang_posts[2])
				{
					$context['search_result_specific'] = 1;										
					$context['result'][$row['id']] = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'author' => $row['author'],
						'link_topic' => $row['link_topic'],						
						'id_member_add' => $row['id_member_add'],
						'username_add' => $row['username_add'],
						'username_add_link' => '<a href="'. $scripturl .'?action=profile;u='.$row['id_member_add'].'" target="_blank">'. $row['username_add'] .'</a>',
						'date_add' => timeformat($row['date_add']),
					);
				}
			}
			$smcFunc['db_free_result']($sql);							
		}	
		/*
		Empty Title? 
		Extra field is not empty ? 
		I selected option "All"? 
		Ok this is the SQL		
		*/		
		if(empty($_POST['title']) && !empty($_POST['extra_field']) && $_POST['extra_field'] == 'all')
		{		
			$context['title'] = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);;
			$context['extra_field_filter'] = $smcFunc['htmlspecialchars']($_POST['extra_field'], ENT_QUOTES);
			$sql = $smcFunc['db_query']('',"SELECT id, title, type_post, lang_post, author, id_member_add, username_add, date_add, link_topic 
							FROM {$db_prefix}up_user_posts");
			while( $row = $smcFunc['db_fetch_assoc']($sql) ) {
				$context['search_result_specific'] = 1;
				$context['result'][$row['id']] = array(
					'id' => $row['id'],
					'title' => $row['title'],
					'author' => $row['author'],
					'link_topic' => $row['link_topic'],
					'id_member_add' => $row['id_member_add'],
					'username_add' => $row['username_add'],
					'username_add_link' => '<a href="'. $scripturl .'?action=profile;u='.$row['id_member_add'].'" target="_blank">'. $row['username_add'] .'</a>',
					'date_add' => timeformat($row['date_add']),
				);
			}
			$smcFunc['db_free_result']($sql);							
		}				
	}

	//Extra Field?
	$context['view_extra_field'] = 0;
	
	$myquery = $smcFunc['db_query']('','SELECT id, title 
					FROM {db_prefix}uposts_extra_field');
	
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['view_extra_field'] = 1;
		$context['extra_field'][$row['id']] = array(
			'id' => $row['id'],
			'title' => $row['title'],
		);
	}
	$smcFunc['db_free_result']($myquery);	
	//End
	
	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=user-posts',
		'name' => $txt['up_module_title'] . ' - ' . $txt['up_module_user_posts_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'user_posts_search';
	$context['page_title'] = $txt['up_module_title'] . ' - ' . $txt['up_module_user_posts_title'];

}

function AddUserPosts()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info, $ultimateportalSettings, $boardurl;
	global $smcFunc, $boarddir, $memberContext;

	if(!isset($_POST['save']) && !isset($_POST['preview']))
		checkSession('get');	

	//Security - Can User Add the the User Post?
	if (empty($user_info['up-modules-permissions']['user_posts_add']) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_perms_groups',false);
	//End Security

	if (isset($_POST['save']))
	{
		checkSession('post');
		//one Author in the field?
		if (!empty($_POST['topic_author']) && strpos($_POST['topic_author'], ','))
			fatal_lang_error('ultport_error_field_author_array',false);

		//Empty Title?
		if (empty($_POST['title']))
			fatal_lang_error('user_posts_error_title',false);

		$title = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);
		
		//The cover save in the hosting?
		if (!empty($ultimateportalSettings['user_posts_cover_save_host']))
		{
			$cover_file = $smcFunc['db_escape_string']($_POST['cover']);
			//Ok move and copy the image
			$cover = save_cover_in_folder($cover_file);
		}else{
			$cover = !empty($_POST['cover']) ? $smcFunc['htmlspecialchars']($_POST['cover'],ENT_QUOTES) : '';
		}	
		
		$link_topic = !empty($_POST['link_topic']) ? $smcFunc['htmlspecialchars']($_POST['link_topic'],ENT_QUOTES) : '';
		$topic_author = !empty($_POST['topic_author']) ? $smcFunc['htmlspecialchars']($_POST['topic_author'],ENT_QUOTES) : '';
		$id_member_add = (int) $_POST['id_member_add'];
		$username_add = !empty($_POST['username_add']) ? $smcFunc['htmlspecialchars']($_POST['username_add'],ENT_QUOTES) : '';
		$date_add = time();
		$id_type_post = !empty($_POST['type']) ? $smcFunc['htmlspecialchars']($_POST['type'],ENT_QUOTES) : '';
		$id_lang_post = !empty($_POST['lang']) ? $smcFunc['htmlspecialchars']( $_POST['lang'],ENT_QUOTES) : '';
		$description = !empty($_POST['elm1']) ? up_convert_savedbadmin($_POST['elm1']) : '';							
		
		$smcFunc['db_query']('',"INSERT INTO {$db_prefix}up_user_posts(title,cover, description, link_topic, author, id_member_add, username_add, date_add, type_post, lang_post)
				VALUES('$title', '$cover', '$description', '$link_topic', '$topic_author', $id_member_add, '$username_add', $date_add, '$id_type_post', '$id_lang_post')");
				
		//redirect
		redirectexit('action=user-posts');		
		
	}
	//Preview
	$context['preview'] = 0;
	if (isset($_POST['preview']))
	{
		checkSession('post');
		$context['preview'] = 1;
		$context['title'] = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);	
		$context['cover_file'] = $smcFunc['db_escape_string']($_POST['cover']);
		$context['cover_img'] = ($ultimateportalSettings['user_posts_cover_view'] == 'advanced') ? '<img alt="'. $context['title'] .'" title="'. $context['title'] .'" src="'. $boardurl .'/up-covers/view.php?url='. $context['cover_file'] .'" width="412" height="412" />' : '<img alt="'. $context['title'] .'" title="'. $context['title'] .'" src="'. $context['cover_file'] .'" width="412" height="412"/>';				
		$context['link_topic'] = !empty($_POST['link_topic']) ? $smcFunc['htmlspecialchars']($_POST['link_topic'],ENT_QUOTES) : '';
		$context['topic_author'] = !empty($_POST['topic_author']) ? $smcFunc['htmlspecialchars']($_POST['topic_author'],ENT_QUOTES) : '';
			if (!empty($context['topic_author']))
			{
				$id_author = LoadID_MEMBER($context['topic_author']);
				loadMemberData($id_author);
				loadMemberContext($id_author);						
				$context['avatar-author'] = !empty($memberContext[$id_author]['avatar']['image']) ? $memberContext[$id_author]['avatar']['image'] : '<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/no_avatar.png" alt="" width="65" height="65"/>';				
				$context['link-author'] = $memberContext[$id_author]['link'];								
			}
		$context['id_member_add'] = (int) $_POST['id_member_add'];
		$context['username_add'] = !empty($_POST['username_add']) ? $smcFunc['htmlspecialchars']($_POST['username_add'],ENT_QUOTES) : '';
		$context['username_add_link'] = '<a href="'. $scripturl .'?action=profile;u='.$context['id_member_add'].'" target="_blank">'. $context['username_add'] .'</a>';						
		$context['date_add'] = timeformat(time());
		//text for added user
		$context['added-for'] = $txt['user_posts_added_for'];
		$context['added-for'] = str_replace('[MEMBER]', $context['username_add_link'], $context['added-for']);
		$context['added-for'] = str_replace('[DATE]', $context['date_add'], $context['added-for']);

		//Type Posts
		if (!empty($_POST['type']))
		{
			$type_post = explode('#', $smcFunc['htmlspecialchars']($_POST['type'],ENT_QUOTES));
			$context['img_type_post'] = '<img title="'. $type_post[1] .'" src="'. $type_post[0] .'" alt="" width="20" height="20"/>';
			$context['type-title'] = $type_post[1];
			$context['id_type'] = $type_post[2];
		}
		//Lang
		if (!empty($_POST['lang']))
		{
			$lang_post = explode('#', $smcFunc['htmlspecialchars']($_POST['lang'],ENT_QUOTES));
			$context['img_lang_post'] = '<img title="'. $lang_post[1] .'" src="'. $lang_post[0] .'" alt="" width="20" height="20"/>';
			$context['lang-title'] = $lang_post[1];		
			$context['id_lang'] = $lang_post[2];
		}		
		$context['description'] = !empty($_POST['elm1']) ? $_POST['elm1'] : '';								
	}
	
	//Load the Type User Post Select Field
	LoadSelectTypePosts();
	//Load the Language User Post Select Field
	LoadSelectLangPosts();
	
	//Load the html headers and load the Editor - Source/Subs-UltimatePortal.php
	context_html_headers();

	//User Posts Link-tree
	$context['news-linktree'] = '&nbsp;<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" alt="" />&nbsp;<a href="'. $scripturl .'?action=user-posts">'. $txt['up_module_user_posts_title'] .'</a> <br /><img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" alt="" />&nbsp;'. $txt['user_posts_module_add_title'];	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=user-posts',
		'name' => $txt['up_module_title'] . ' - ' . $txt['up_module_user_posts_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'user_posts_add';
	$context['page_title'] = $txt['up_module_title'] . ' - ' . $txt['up_module_user_posts_title'];

}

function EditUserPosts()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info, $ultimateportalSettings, $boardurl;
	global $smcFunc, $boarddir, $memberContext;

	if(!isset($_POST['save']) && !isset($_POST['preview']))
		checkSession('get');	

	//Security - Can User Add the the User Post?
	if (empty($user_info['up-modules-permissions']['user_posts_moderate']) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_perms_groups',false);
	//End Security

	$id = $smcFunc['db_escape_string']($_GET['id']);
		
	//Load the UserPosts Rows
	LoadUserPostsRows('edit', $id);
	
	if (isset($_POST['save']))
	{
		checkSession('post');		
		//one Author in the field?
		if (!empty($_POST['topic_author']) && strpos($_POST['topic_author'], ','))
			fatal_lang_error('ultport_error_field_author_array',false);

		//Empty Title?
		if (empty($_POST['title']))
			fatal_lang_error('user_posts_error_title',false);

		$title = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);
		
		//The cover save in the hosting?
		if (!empty($ultimateportalSettings['user_posts_cover_save_host']) && ($_POST['cover'] != $context['cover']))
		{
			$cover_file = $smcFunc['db_escape_string']($_POST['cover']);
			$cover = save_cover_in_folder($cover_file);
		}else{
			$cover = !empty($_POST['cover']) ? $smcFunc['htmlspecialchars']($_POST['cover'],ENT_QUOTES) : '';
		}	
		
		$link_topic = !empty($_POST['link_topic']) ? $smcFunc['htmlspecialchars']($_POST['link_topic'],ENT_QUOTES) : '';
		$topic_author = !empty($_POST['topic_author']) ? $smcFunc['htmlspecialchars']($_POST['topic_author'],ENT_QUOTES) : '';
		$id_member_updated = (int) $_POST['id_member_updated'];
		$username_updated = !empty($_POST['username_updated']) ? $smcFunc['htmlspecialchars']($_POST['username_updated'],ENT_QUOTES) : '';
		$date_updated = time();
		$id_type_post = !empty($_POST['type']) ? $smcFunc['htmlspecialchars']($_POST['type'],ENT_QUOTES) : '';
		$id_lang_post = !empty($_POST['lang']) ? $smcFunc['htmlspecialchars']($_POST['lang'],ENT_QUOTES) : '';
		$description = !empty($_POST['elm1']) ? up_convert_savedbadmin($_POST['elm1']) : '';							
		
		$smcFunc['db_query']('',"UPDATE {$db_prefix}up_user_posts
				SET title = '$title',
					cover = '$cover', 
					description = '$description', 
					link_topic = '$link_topic', 
					author = '$topic_author', 
					id_member_updated = $id_member_updated, 
					username_updated = '$username_updated', 
					date_updated = $date_updated, 
					type_post = '$id_type_post', 
					lang_post = '$id_lang_post'
				WHERE id = $id");
				
		//redirect
		if (!empty($ultimateportalSettings['user_posts_internal_page']))
		{
			redirectexit('action=user-posts;sa=view-single;id='. $id);
		}else{
			redirectexit('action=user-posts');
		}			
	}

	//Preview
	if (isset($_POST['preview']))
	{
		checkSession('post');
		$context['preview'] = 1;
		$context['title'] = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);	
		$context['cover_url'] = !empty($_POST['cover']) ? $smcFunc['htmlspecialchars']($_POST['cover'],ENT_QUOTES) : '';
		$context['cover_img'] = !empty($_POST['cover']) ? (($context['cover_url'] != $context['cover']) ? (($ultimateportalSettings['user_posts_cover_view'] == 'advanced') ? '<img alt="'. $context['title'] .'" title="'. $context['title'] .'" src="'. $boardurl .'/up-covers/view.php?url='. $context['cover_url'] .'" width="412" height="412" />' : '<img alt="'. $context['title'] .'" title="'. $context['title'] .'" src="'. $_POST['cover'] .'" width="412" height="412"/>') : '<img alt="'. $context['title'] .'" title="'. $context['title'] .'" src="'. $_POST['cover'] .'" width="412" height="412"/>') : '';				
		$context['link_topic'] = !empty($_POST['link_topic']) ? $smcFunc['htmlspecialchars']($_POST['link_topic'],ENT_QUOTES) : '';
		$context['topic_author'] = !empty($_POST['topic_author']) ? $smcFunc['htmlspecialchars']($_POST['topic_author'],ENT_QUOTES) : '';
			if (!empty($context['topic_author']))
			{
				$id_author = LoadID_MEMBER($context['topic_author']);
				loadMemberData($id_author);
				loadMemberContext($id_author);						
				$context['avatar-author'] = !empty($memberContext[$id_author]['avatar']['image']) ? $memberContext[$id_author]['avatar']['image'] : '<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/no_avatar.png" alt="" width="65" height="65"/>';				
				$context['link-author'] = $memberContext[$id_author]['link'];								
			}
		$context['id_member_add'] = (int) $_POST['id_member_add'];
		$context['username_add'] = !empty($_POST['username_add']) ? $_POST['username_add'] : '';		
		$context['username_add_link'] = '<a href="'. $scripturl .'?action=profile;u='.$context['id_member_add'].'" target="_blank">'. $context['username_add'] .'</a>';						
		$context['date_add'] = timeformat(time());
		//text for added user
		$context['added-for'] = $txt['user_posts_added_for'];
		$context['added-for'] = str_replace('[MEMBER]', $context['username_add_link'], $context['added-for']);
		$context['added-for'] = str_replace('[DATE]', $context['date_add'], $context['added-for']);

		//Type Posts
		if (!empty($_POST['type']))
		{
			$type_post = explode('#', $smcFunc['htmlspecialchars']($_POST['type'],ENT_QUOTES));
			$context['img_type_post'] = '<img title="'. $type_post[1] .'" src="'. $type_post[0] .'" alt="" width="20" height="20"/>';
			$context['type-title'] = $type_post[1];
			$context['id_type'] = $type_post[2];
		}
		//Lang
		if (!empty($_POST['lang']))
		{
			$lang_post = explode('#', $smcFunc['htmlspecialchars']($_POST['lang'],ENT_QUOTES));
			$context['img_lang_post'] = '<img title="'. $lang_post[1] .'" src="'. $lang_post[0] .'" alt="" width="20" height="20"/>';
			$context['lang-title'] = $lang_post[1];		
			$context['id_lang'] = $lang_post[2];
		}		
		$context['description'] = !empty($_POST['elm1']) ? $_POST['elm1'] : '';								
	}
	//End Preview
	//Load the Type User Post Select Field
	LoadSelectTypePosts();
	//Load the Language User Post Select Field
	LoadSelectLangPosts();
	
	//Load the html headers and load the Editor - Source/Subs-UltimatePortal.php
	context_html_headers();

	//User Posts Link-tree
	$context['news-linktree'] = '&nbsp;<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" alt="" />&nbsp;<a href="'. $scripturl .'?action=user-posts">'. $txt['up_module_user_posts_title'] .'</a> <br /><img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" alt="" />&nbsp;'. $txt['ultport_button_editing'] . ' - <em><a href="'.$scripturl .'?action=user-posts;sa=view-single;id='. $context['id'] .'">'. $context['user-post-title'] .'</a></em>';	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=user-posts',
		'name' => $txt['up_module_title'] . ' - ' . $txt['up_module_user_posts_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'user_posts_edit';
	$context['page_title'] = $context['user-post-title'];

}

function ViewSingleUserPosts()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	global $smcFunc;
	
	//Security - Can User Delete the User Post?
	if (empty($ultimateportalSettings['user_posts_internal_page']))
		fatal_lang_error('ultport_error_no_active_internal_page',false);
		
	$id = $smcFunc['db_escape_string']($_GET['id']);
	$id = (int) $id;
		
	//Load the UserPosts Rows
	LoadUserPostsRows('view-single', $id);
	
	//User Posts Link-tree
	$context['news-linktree'] = '&nbsp;<img alt="'. $txt['up_module_user_posts_title'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=user-posts">'. $txt['up_module_user_posts_title'] .'</a> <br /><img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" alt="'. $context['user-post-title'] .'" />&nbsp;'. $context['user-post-title'];	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=user-posts',
		'name' => $txt['up_module_title'] . ' - ' . $txt['up_module_user_posts_title']
	);

	//Social Bookmarks 
	$context['social_bookmarks'] = UpSocialBookmarks($scripturl .'?action=user-posts;sa=view-single;id='. $id );

	// Call the sub template.
	$context['sub_template'] = 'user_posts_view_single';
	$context['page_title'] = $context['user-post-title'];

}


function DeleteUserPosts()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info;
	global $smcFunc;

	checkSession('get');
	//Security - Can User Delete the User Post?
	if (empty($user_info['up-modules-permissions']['user_posts_moderate']) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_perms_groups',false);
	//End Security

	//ID
	$id = $smcFunc['db_escape_string']($_GET['id']);
	$id = (int) $id;
	
	//Now delete
	$smcFunc['db_query']('',"DELETE FROM {$db_prefix}up_user_posts 
			WHERE id = $id");

	//redirect
	redirectexit('action=user-posts');		
	
}

?>