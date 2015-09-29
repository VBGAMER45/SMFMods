<?php
/*
Post Scheduler
Version 1.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2012-2014 http://www.samsonsoftware.com
*/
if (!defined('SMF'))
	die('Hacking attempt...');

function PostSchedulerMain()
{
	// Only Admins should see these options
	isAllowedTo('admin_forum');
	
	// Load the maintemplate
	loadtemplate('PostScheduler');

	// Load the language files
	if (loadlanguage('PostScheduler') == false)
		loadLanguage('PostScheduler', 'english');

	// Post Scheduler actions
	$subActions = array(
		'addpost' => 'AddPost',
		'addpost2' => 'AddPost2',
		'editpost' => 'EditPost',
		'editpost2' => 'EditPost2',
		'delpost' => 'DeletePost',
		'admin' => 'PostSchedulerAdmin',
		'saveset' => 'SaveSettings',
		'bulkactions' => 'BulkActions',
	);


	// Follow the sa or just go to admin
	if (!empty($subActions[@$_REQUEST['sa']]))
		$subActions[$_REQUEST['sa']]();
	else
		PostSchedulerAdmin();

}

function PostSchedulerAdmin()
{
	global $txt, $mbname, $context, $db_prefix, $scripturl;
	
	adminIndex('postscheduler_settings');
	
	// Get all the feeds
	$context['schedule_posts'] = array();
	
	$context['start'] = (int) $_REQUEST['start'];
	
	$request = db_query("
			SELECT 
				COUNT(*) as total
			FROM {$db_prefix}postscheduler
			WHERE hasposted  = 0
			", __FILE__, __LINE__);
	$totalRow = mysql_fetch_assoc($request);
	
	$request = db_query("
			SELECT 
				ID_POST, subject, ID_MEMBER, postername, post_time
			FROM {$db_prefix}postscheduler
			WHERE hasposted  = 0  
			ORDER BY post_time ASC LIMIT $context[start],10", __FILE__, __LINE__);
	
	while ($row = mysql_fetch_assoc($request))
	{
		$context['schedule_posts'][] = $row;
	}
		
	mysql_free_result($request);
	
	// Setup the paging
	$context['page_index'] = constructPageIndex($scripturl . '?action=postscheduler;sa=admin', $_REQUEST['start'], $totalRow['total'], 10);
			
	$context['sub_template']  = 'postmain';
	
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['postscheduler_postlist'];
}

function AddPost()
{
	global $txt, $mbname, $context, $db_prefix, $sourcedir, $modSettings;
	
	loadLanguage('index');
	
	adminIndex('postscheduler_settings');
	
	require_once($sourcedir . '/Subs-Post.php');
	
	// Get the boards.
	$context['schedule_boards'] = array();
	$request = db_query("
	SELECT 
		b.ID_BOARD, b.name AS bName, c.name AS cName 
	FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c 
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
	
	while ($row = mysql_fetch_assoc($request))
		$context['schedule_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
		
	mysql_free_result($request);
	
	// Load Message Icons
	$context['msg_icons'] = array();
	$result = db_query("SELECT title, filename
				FROM {$db_prefix}message_icons
				", __FILE__, __LINE__);
	while($row = mysql_fetch_assoc($result))
		$context['msg_icons'][] = $row;
	
	$context['page_title'] = $mbname . ' - ' . $txt['postscheduler_addpost'];
	
	// Set the page title
	$context['sub_template']  = 'addpost';
	
	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	$context['post_box_name'] = 'message';
	$context['post_form'] = 'frmfeed';
	
}

function AddPost2()
{
	global $db_prefix, $txt, $func;
	
	checkSession('post');
	
	// Get the Fields
	$subject = $func['htmlspecialchars']($_REQUEST['subject'], ENT_QUOTES);
	$boardselect = (int) $_REQUEST['boardselect'];
	$postername = str_replace('"','', $_REQUEST['postername']);
	$postername = str_replace("'",'', $postername);
	$postername = str_replace('\\','', $postername);
	
	$postername = $func['htmlspecialchars']($postername, ENT_QUOTES);

	$msgicon = $func['htmlspecialchars']($_REQUEST['msgicon'], ENT_QUOTES);

	$message = $func['htmlspecialchars']($_REQUEST['message'], ENT_QUOTES);
	
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
	
	$dbresult = db_query("
	SELECT 
		realName, ID_MEMBER 
	FROM {$db_prefix}members 
	WHERE realName = '$postername' OR memberName = '$postername'  LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	
	if (db_affected_rows() != 0)
		$memid = $row['ID_MEMBER'];
	
		
	// Insert into table
		db_query("
		INSERT INTO {$db_prefix}postscheduler 
			(ID_BOARD, subject, postername, ID_MEMBER, locked, 
			body,id_topic,post_time,
			msgicon)
		VALUES 
			($boardselect,'$subject','$postername', $memid, $topiclocked,
		 	'$message','$topicid','$post_time',
		 	'$msgicon')", __FILE__, __LINE__);	
	
	// Redirect to the Admin
	redirectexit('action=postscheduler;sa=admin');
}

function EditPost()
{
	global $txt, $mbname, $context, $db_prefix, $sourcedir, $modSettings;
	
	// Get the ID
	$id = (int) $_REQUEST['id'];
	
	if (empty($id))
		fatal_error($txt['postscheduler_nopostselected'], false);
	
	adminIndex('postscheduler_settings');
	
	require_once($sourcedir . '/Subs-Post.php');
		
	// Show the boards
	$context['schedule_boards'] = array('');
	$request = db_query("
	SELECT 
		b.ID_BOARD, b.name AS bName, c.name AS cName 
	FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c 
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
	
	while ($row = mysql_fetch_assoc($request))
		$context['schedule_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	mysql_free_result($request);
	
	
	// Get the Data
	$context['schedulepost'] = array();
	
	$request = db_query("
	SELECT 
		ID_POST, subject, ID_MEMBER, postername, post_time,
		msgicon, body, locked, ID_TOPIC, ID_BOARD 
	FROM {$db_prefix}postscheduler 
	WHERE ID_POST = $id LIMIT 1", __FILE__, __LINE__);
	
	$row = mysql_fetch_assoc($request);

	$context['schedulepost'] = $row;

	mysql_free_result($request);
	
	// Load Message Icons
	$context['msg_icons'] = array();
	$result = db_query("SELECT title, filename
				FROM {$db_prefix}message_icons
				", __FILE__, __LINE__);
	while($row = mysql_fetch_assoc($result))
		$context['msg_icons'][] = $row;
	

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['postscheduler_editpost'];
	
	$context['sub_template']  = 'editpost';
	
	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	$context['post_box_name'] = 'message';
	$context['post_form'] = 'frmfeed';
		
}

function EditPost2()
{
	global $txt, $db_prefix, $func;
	
	checkSession('post');
	
	// Get the ID
	$id = (int) $_REQUEST['id'];
	
	if (empty($id))
		fatal_error($txt['postscheduler_nopostselected'], false);
		
	$subject = $func['htmlspecialchars']($_REQUEST['subject'], ENT_QUOTES);
	$boardselect = (int) $_REQUEST['boardselect'];
	$postername = str_replace('"','', $_REQUEST['postername']);
	$postername = str_replace("'",'', $postername);
	$postername = str_replace('\\','', $postername);
	
	$postername = $func['htmlspecialchars']($postername, ENT_QUOTES);

	$msgicon = $func['htmlspecialchars']($_REQUEST['msgicon'], ENT_QUOTES);

	$message = $func['htmlspecialchars']($_REQUEST['message'], ENT_QUOTES);
	
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
	
	$dbresult = db_query("
	SELECT 
		realName, ID_MEMBER 
	FROM {$db_prefix}members 
	WHERE realName = '$postername' OR memberName = '$postername'  LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	
	if (db_affected_rows() != 0)
		$memid = $row['ID_MEMBER'];
		
		db_query("
		UPDATE {$db_prefix}postscheduler 
		SET 
			ID_BOARD = $boardselect, subject = '$subject', postername  = '$postername', ID_MEMBER = $memid, locked = '$topiclocked', 
			body = '$message',id_topic = '$topicid',post_time = '$post_time',
			msgicon  = '$msgicon'

	    WHERE ID_POST = $id LIMIT 1", __FILE__, __LINE__);	
	
		
		
	// Redirect to the Admin
	redirectexit('action=postscheduler;sa=admin');
}

function DeletePost()
{
	global $txt;
	
	// Get the ID
	$id = (int) $_REQUEST['id'];
	
	if (empty($id))
		fatal_error($txt['postscheduler_nofeedselected'], false);	
		
	DeletePostByID($id);
	
	// Redirect to the Admin
	redirectexit('action=postscheduler;sa=admin');
	
}

function DeletePostByID($id)
{
	global $db_prefix;
	
	// Delete the post
	db_query("
	DELETE 
	FROM {$db_prefix}postscheduler  
	WHERE ID_POST = $id LIMIT 1", __FILE__, __LINE__);
	
}

function SaveSettings()
{
	checkSession('post');

	$post_fakecron = isset($_REQUEST['post_fakecron']) ? 1 : 0;

	// Save the setting information
	updateSettings(
	array(
	'post_fakecron' => $post_fakecron,

	));
	
	// Redirect to the Admin
	redirectexit('action=postscheduler;sa=admin');
}

function BulkActions()
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
	redirectexit('action=postscheduler;sa=admin');
}

function CheckPostScheduler()
{
	global $sourcedir, $db_prefix;
	
	// For the createPost function
	require_once($sourcedir . '/Subs-Post.php');
	
	$t = time();

	$result = db_query("
	SELECT 
		ID_POST, subject, ID_MEMBER, postername, post_time,
		msgicon, body, locked, ID_TOPIC, ID_BOARD 
	FROM {$db_prefix}postscheduler  
	WHERE hasposted = 0 AND post_time <= $t", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($result))
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

		
		db_query("UPDATE {$db_prefix}postscheduler
		SET hasposted = 1  
	WHERE ID_POST = " . $row['ID_POST'], __FILE__, __LINE__);
	}
	
	
	updateSettings(array('post_lastcron' => (time() +  + (1 * 60))));
}
?>