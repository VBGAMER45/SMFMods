<?php
/*
Post Scheduler
Version 1.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2012-2022 https://www.samsonsoftware.com
*/
if (!defined('SMF'))
	die('Hacking attempt...');

function PostSchedulerMain()
{
	// Only Admins should see these options
	isAllowedTo('admin_forum');
	
	// Load the maintemplate
	loadtemplate('PostScheduler2');

	// Load the language files
	if (loadlanguage('PostScheduler') == false)
		loadLanguage('PostScheduler', 'english');

	// Post Scheduler actions
	$subActions = array(
		'addpost' => 'PA_AddPost',
		'addpost2' => 'PA_AddPost2',
		'editpost' => 'PA_EditPost',
		'editpost2' => 'PA_EditPost2',
		'delpost' => 'PA_DeletePost',
		'admin' => 'PostSchedulerAdmin',
		'saveset' => 'PA_SaveSettings',
		'bulkactions' => 'PA_BulkActions',
	);


	// Follow the sa or just go to admin
	if (!empty($subActions[@$_REQUEST['sa']]))
		$subActions[$_REQUEST['sa']]();
	else
		PostSchedulerAdmin();

}

function PostSchedulerAdmin()
{
	global $txt, $mbname, $context, $smcFunc, $scripturl;
	
	// Get all the feeds
	$context['schedule_posts'] = array();
	
	$context['start'] = (int) $_REQUEST['start'];
	
	$request = $smcFunc['db_query']('', "
			SELECT 
				COUNT(*) as total
			FROM {db_prefix}postscheduler
			WHERE hasposted  = 0
			");
	$totalRow = $smcFunc['db_fetch_assoc']($request);
	
	$request = $smcFunc['db_query']('', "
			SELECT 
				ID_POST, subject, ID_MEMBER, postername, post_time
			FROM {db_prefix}postscheduler
			WHERE hasposted  = 0  
			ORDER BY post_time ASC LIMIT $context[start],10");
	
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['schedule_posts'][] = $row;
	}
		
	$smcFunc['db_free_result']($request);
	
	// Setup the paging
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=postscheduler;sa=admin', $_REQUEST['start'], $totalRow['total'], 10);
			
	$context['sub_template']  = 'postmain';
	
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['postscheduler_postlist'];
}

function PA_AddPost()
{
	global $txt, $mbname, $context, $smcFunc, $sourcedir, $modSettings;
	
	loadLanguage('index');
	
	require_once($sourcedir . '/Subs-Post.php');
	
	// Get the boards.
	$context['schedule_boards'] = array();
	$request = $smcFunc['db_query']('', "
	SELECT 
		b.ID_BOARD, b.name AS bName, c.name AS cName 
	FROM {db_prefix}boards AS b, {db_prefix}categories AS c 
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
	
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['schedule_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
		
	$smcFunc['db_free_result']($request);
	
	// Load Message Icons
	$context['msg_icons'] = array();
	$result = $smcFunc['db_query']('', "SELECT title, filename
				FROM {db_prefix}message_icons
				");
	while($row = $smcFunc['db_fetch_assoc']($result))
		$context['msg_icons'][] = $row;
	
	$context['page_title'] = $mbname . ' - ' . $txt['postscheduler_addpost'];
	
	// Set the page title
	$context['sub_template']  = 'addpost';
	
	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	$context['post_box_name'] = 'message';
	$context['post_form'] = 'frmfeed';
	
	
	// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'message',
		'value' => '',
		'width' => '90%',
		'form' => 'frmfeed',
		'labels' => array(
			'addpost' => $txt['postscheduler_addpost']
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	
	
}

function PA_AddPost2()
{
	global $smcFunc, $txt, $smcFunc, $sourcedir;
	
	checkSession('post');
	
	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['message_mode']) && isset($_REQUEST['message'])  && !function_exists("set_tld_regex"))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['message'] = html_to_bbc($_REQUEST['message']);

		// We need to unhtml it now as it gets done shortly.
		$_REQUEST['message'] = un_htmlspecialchars($_REQUEST['message']);

	}
	
	// Get the Fields
	$subject = $smcFunc['htmlspecialchars']($_REQUEST['subject'], ENT_QUOTES);
	$boardselect = (int) $_REQUEST['boardselect'];
	$postername = str_replace('"','', $_REQUEST['postername']);
	$postername = str_replace("'",'', $postername);
	$postername = str_replace('\\','', $postername);
	
	$postername = $smcFunc['htmlspecialchars']($postername, ENT_QUOTES);

	$msgicon = $smcFunc['htmlspecialchars']($_REQUEST['msgicon'], ENT_QUOTES);

	$message = $smcFunc['htmlspecialchars']($_REQUEST['message'], ENT_QUOTES);
	
	$topicid = (int) $_REQUEST['topicid'];
	
	if ($subject == '')
		fatal_error($txt['postscheduler_err_subject'], false);

	if ($postername == '')
		fatal_error($txt['postscheduler_err_postername'], false);
		
	if ($boardselect == 0)
		fatal_error($txt['postscheduler_err_forum'], false);
	
	if ($message == '')
		fatal_error($txt['postscheduler_err_message'], false);
		
	
	$topiclocked = isset($_REQUEST['topiclocked']) ? 1 : 0;
	
	$month = (int) $_REQUEST['month'];
	$day = (int) $_REQUEST['day'];
	$year = (int) $_REQUEST['year'];
	$hour = (int) $_REQUEST['hour'];
	$minute = (int) $_REQUEST['minute'];
	$ampm = $_REQUEST['ampm'];
	$minute = str_pad($minute,2,"0",STR_PAD_LEFT);
	$time_in_24_hour_format  = DATE("H", STRTOTIME("$hour:$minute $ampm"));

	
	if (!empty($month)  && !empty($day) && !empty($year))
	{
		$post_time = mktime($time_in_24_hour_format,$minute,0,$month,$day,$year);
	}
	else 
		fatal_error($txt['postscheduler_err_date'], false);
		
	// Lookup the Memeber ID of the postername
	$memid = 0;
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		real_name, ID_MEMBER 
	FROM {db_prefix}members 
	WHERE real_name = '$postername' OR member_name = '$postername'  LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	
	if ($smcFunc['db_affected_rows']() != 0)
		$memid = $row['ID_MEMBER'];
	
		
	// Insert into table
		$smcFunc['db_query']('', "
		INSERT INTO {db_prefix}postscheduler 
			(ID_BOARD, subject, postername, ID_MEMBER, locked, 
			body,id_topic,post_time,
			msgicon)
		VALUES 
			($boardselect,'$subject','$postername', $memid, $topiclocked,
		 	'$message','$topicid','$post_time',
		 	'$msgicon')");	
	
	// Redirect to the Admin
	redirectexit('action=admin;area=postscheduler;sa=admin');
}

function PA_EditPost()
{
	global $txt, $mbname, $context, $smcFunc, $sourcedir, $modSettings;
	
	// Get the ID
	$id = (int) $_REQUEST['id'];
	
	if (empty($id))
		fatal_error($txt['postscheduler_nopostselected'], false);
	
	require_once($sourcedir . '/Subs-Post.php');
		
	// Show the boards
	$context['schedule_boards'] = array('');
	$request = $smcFunc['db_query']('', "
	SELECT 
		b.ID_BOARD, b.name AS bName, c.name AS cName 
	FROM {db_prefix}boards AS b, {db_prefix}categories AS c 
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
	
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['schedule_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	$smcFunc['db_free_result']($request);
	
	
	// Get the Data
	$context['schedulepost'] = array();
	
	$request = $smcFunc['db_query']('', "
	SELECT 
		ID_POST, subject, ID_MEMBER, postername, post_time,
		msgicon, body, locked, ID_TOPIC, ID_BOARD 
	FROM {db_prefix}postscheduler 
	WHERE ID_POST = $id LIMIT 1");
	
	$row = $smcFunc['db_fetch_assoc']($request);

	$context['schedulepost'] = $row;

	$smcFunc['db_free_result']($request);
	
	// Load Message Icons
	$context['msg_icons'] = array();
	$result = $smcFunc['db_query']('', "SELECT title, filename
				FROM {db_prefix}message_icons
				");
	while($row = $smcFunc['db_fetch_assoc']($result))
		$context['msg_icons'][] = $row;
	

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['postscheduler_editpost'];
	
	$context['sub_template']  = 'editpost';
	
	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	$context['post_box_name'] = 'message';
	$context['post_form'] = 'frmfeed';
	
	// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'message',
		'value' => $context['schedulepost']['body'],
		'width' => '90%',
		'form' => 'frmfeed',
		'labels' => array(
			'editpost' => $txt['postscheduler_editpost']
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	
	
}

function PA_EditPost2()
{
	global $txt, $smcFunc, $sourcedir;
	
	checkSession('post');
	
	// Get the ID
	$id = (int) $_REQUEST['id'];
	
	if (empty($id))
		fatal_error($txt['postscheduler_nopostselected'], false);
		
	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['message_mode']) && isset($_REQUEST['message'])  && !function_exists("set_tld_regex"))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['message'] = html_to_bbc($_REQUEST['message']);

		// We need to unhtml it now as it gets done shortly.
		$_REQUEST['message'] = un_htmlspecialchars($_REQUEST['message']);

	}
		
		
	$subject = $smcFunc['htmlspecialchars']($_REQUEST['subject'], ENT_QUOTES);
	$boardselect = (int) $_REQUEST['boardselect'];
	$postername = str_replace('"','', $_REQUEST['postername']);
	$postername = str_replace("'",'', $postername);
	$postername = str_replace('\\','', $postername);
	
	$postername = $smcFunc['htmlspecialchars']($postername, ENT_QUOTES);

	$msgicon = $smcFunc['htmlspecialchars']($_REQUEST['msgicon'], ENT_QUOTES);

	$message = $smcFunc['htmlspecialchars']($_REQUEST['message'], ENT_QUOTES);
	
	$topicid = (int) $_REQUEST['topicid'];
	
	if ($subject == '')
		fatal_error($txt['postscheduler_err_subject'], false);

	if ($postername == '')
		fatal_error($txt['postscheduler_err_postername'], false);
		
	if ($boardselect == 0)
		fatal_error($txt['postscheduler_err_forum'], false);
	
	if ($message == '')
		fatal_error($txt['postscheduler_err_message'], false);
		
	
	$topiclocked = isset($_REQUEST['topiclocked']) ? 1 : 0;
	

	$month = (int) $_REQUEST['month'];
	$day = (int) $_REQUEST['day'];
	$year = (int) $_REQUEST['year'];
	$hour = (int) $_REQUEST['hour'];
	$minute = (int) $_REQUEST['minute'];
	$ampm = $_REQUEST['ampm'];
	$minute = str_pad($minute,2,"0",STR_PAD_LEFT);
	$time_in_24_hour_format  = DATE("H", STRTOTIME("$hour:$minute $ampm"));

	
	if (!empty($month)  && !empty($day) && !empty($year))
	{
		$post_time = mktime($time_in_24_hour_format,$minute,0,$month,$day,$year);
	}
	else 
		fatal_error($txt['postscheduler_err_date'], false);
		
	// Lookup the Memeber ID of the postername
	$memid = 0;
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		real_name, ID_MEMBER 
	FROM {db_prefix}members 
	WHERE real_name = '$postername' OR member_name = '$postername'  LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	
	if ($smcFunc['db_affected_rows']() != 0)
		$memid = $row['ID_MEMBER'];
		
		$smcFunc['db_query']('', "
		UPDATE {db_prefix}postscheduler 
		SET 
			ID_BOARD = $boardselect, subject = '$subject', postername  = '$postername', ID_MEMBER = $memid, locked = '$topiclocked', 
			body = '$message',id_topic = '$topicid',post_time = '$post_time',
			msgicon  = '$msgicon'

	    WHERE ID_POST = $id LIMIT 1");	
	
		
		
	// Redirect to the Admin
	redirectexit('action=admin;area=postscheduler;sa=admin');
}

function PA_DeletePost()
{
	global $txt;
	
	// Get the ID
	$id = (int) $_REQUEST['id'];
	
	if (empty($id))
		fatal_error($txt['postscheduler_nofeedselected'], false);	
		
	DeletePostByID($id);
	
	// Redirect to the Admin
	redirectexit('action=admin;area=postscheduler;sa=admin');
	
}

function DeletePostByID($id)
{
	global $smcFunc;
	
	// Delete the post
	$smcFunc['db_query']('', "
	DELETE 
	FROM {db_prefix}postscheduler  
	WHERE ID_POST = $id LIMIT 1");
	
}

function PA_SaveSettings()
{
	checkSession('post');

	$post_fakecron = isset($_REQUEST['post_fakecron']) ? 1 : 0;

	// Save the setting information
	updateSettings(
	array(
	'post_fakecron' => $post_fakecron,

	));
	
	// Redirect to the Admin
	redirectexit('action=admin;area=postscheduler;sa=admin');
}

function PA_BulkActions()
{

	if (isset($_REQUEST['post']))
	{
		$bulk = $_REQUEST['bulk'];
		
		foreach($_REQUEST['post'] as $post => $key)
		{
			$post = (int) $post;
			if ($bulk == 'delete')
				DeletePostByID($post);
		}
	}
	
	// Redirect to the Admin
	redirectexit('action=admin;area=postscheduler;sa=admin');
}

function CheckPostScheduler()
{
	global $sourcedir, $smcFunc;
	
	// For the createPost function
	require_once($sourcedir . '/Subs-Post.php');
	
	$t = time();

	$result = $smcFunc['db_query']('', "
	SELECT 
		ID_POST, subject, ID_MEMBER, postername, post_time,
		msgicon, body, locked, ID_TOPIC, ID_BOARD 
	FROM {db_prefix}postscheduler  
	WHERE hasposted = 0 AND post_time <= $t");
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$updatePostCount = (($row['ID_MEMBER'] == 0) ? 0 : 1);
								
		$msgOptions = array(
			'id' => 0,
			'subject' => $row['subject'],
			'body' => $row['body'],
			'icon' => $row['msgicon'],
			'smileys_enabled' => 1,
			'attachments' => array(),
		);
		$topicOptions = array(
			'id' => $row['ID_TOPIC'],
			'board' => $row['ID_BOARD'],
			'poll' => null,
			'lock_mode' => $row['locked'],
			'sticky_mode' => null,
			'mark_as_read' => false,
		);
		$posterOptions = array(
			'id' => $row['ID_MEMBER'],
			'name' => $row['postername'],
			'email' => '',
			'ip' => '127.0.0.1',
			'update_post_count' => $updatePostCount,
		);
		
		createPost($msgOptions, $topicOptions, $posterOptions);

		
		$smcFunc['db_query']('', "UPDATE {db_prefix}postscheduler
		SET hasposted = 1  
	WHERE ID_POST = " . $row['ID_POST']);
	}
	
	
	updateSettings(array('post_lastcron' => (time() +  + (1 * 60))));
}
?>