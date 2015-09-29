<?php
/*
RSS Feed Poster
Version 4.2
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function FeedsMain()
{
	// Only Admins should see these options
	isAllowedTo('admin_forum');
	
	// Load the main feeds template
	loadtemplate('FeedPoster');

	// Load the language files
	if (loadlanguage('FeedPoster') == false)
		loadLanguage('FeedPoster', 'english');

	// FeedPoster actions
	$subActions = array(
		'addfeed' => 'AddFeed',
		'addfeed2' => 'AddFeed2',
		'editfeed' => 'EditFeed',
		'editfeed2' => 'EditFeed2',
		'delfeed' => 'DeleteFeed',
		'admin' => 'FeedsAdmin',
		'saveset' => 'SaveSettings',
		'bulkactions' => 'BulkActions',
	);


	// Follow the sa or just go to feeds admin
	if (!empty($subActions[@$_REQUEST['sa']]))
		$subActions[$_REQUEST['sa']]();
	else
		FeedsAdmin();

}

function FeedsAdmin()
{
	global $txt, $mbname, $context, $db_prefix;
	
	adminIndex('feeds_settings');
	
	// Get all the feeds
	$context['feeds'] = array();
	
	$request = db_query("
			SELECT 
				ID_FEED, feedurl, title, postername, enabled, total_posts, updatetime  
			FROM {$db_prefix}feedbot", __FILE__, __LINE__);
	
	while ($row = mysql_fetch_assoc($request))
	{
		$context['feeds'][] = $row;
	}
		
	mysql_free_result($request);
	
	
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['feedposter_feedlist'];
}

function AddFeed()
{
	global $txt, $mbname, $context, $db_prefix;
	
	loadLanguage('index');
	
	adminIndex('feeds_settings');
	
	// Show the boards for the feeds.
	$context['feed_boards'] = array();
	$request = db_query("
	SELECT 
		b.ID_BOARD, b.name AS bName, c.name AS cName 
	FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c 
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
	
	while ($row = mysql_fetch_assoc($request))
		$context['feed_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
		
	mysql_free_result($request);
	
	// Load Message Icons
	$context['msg_icons'] = array();
	$result = db_query("SELECT title, filename
				FROM {$db_prefix}message_icons
				", __FILE__, __LINE__);
	while($row = mysql_fetch_assoc($result))
		$context['msg_icons'][] = $row;
	
	$context['page_title'] = $mbname . ' - ' . $txt['feedposter_addfeed'];
	
	// Set the page title
	$context['sub_template']  = 'addfeed';
	
}

function AddFeed2()
{
	global $db_prefix, $txt, $sourcedir, $func;
	
	// Get the Fields
	$feedposter_feedtitle = $func['htmlspecialchars']($_REQUEST['feedposter_feedtitle'], ENT_QUOTES);
	$feedposter_feedurl = $_REQUEST['feedposter_feedurl'];
	$boardselect = (int) $_REQUEST['boardselect'];
	$feedposter_postername = str_replace('"','', $_REQUEST['feedposter_postername']);
	$feedposter_postername = str_replace("'",'', $feedposter_postername);
	$feedposter_postername = str_replace('\\','', $feedposter_postername);
	
	$feedposter_postername = $func['htmlspecialchars']($feedposter_postername, ENT_QUOTES);
	$feedposter_topicprefix = $func['htmlspecialchars']($_REQUEST['feedposter_topicprefix'], ENT_QUOTES);
	$feedposter_importevery = (int) $_REQUEST['feedposter_importevery'];
	$feedposter_numbertoimport = (int) $_REQUEST['feedposter_numbertoimport'];
	
	$msgicon = $func['htmlspecialchars']($_REQUEST['msgicon'], ENT_QUOTES);
	$footer = $func['htmlspecialchars']($_REQUEST['footer'], ENT_QUOTES);
    
    $topicid = (int) $_REQUEST['topicid'];
	
	if ($feedposter_feedtitle == '')
		fatal_error($txt['feedposter_err_feedtitle'], false);

	if ($feedposter_feedurl == '')
		fatal_error($txt['feedposter_err_feedurl'], false);
		

	if ($feedposter_postername == '')
		fatal_error($txt['feedposter_err_postername'], false);
		
	if ($boardselect == 0)
		fatal_error($txt['feedposter_err_forum'], false);
	
	if ($feedposter_importevery < 5)
		$feedposter_importevery = 5;
	
	if ($feedposter_numbertoimport < 1)
		$feedposter_numbertoimport  = 1;
		
	if ($feedposter_numbertoimport > 50)
		$feedposter_numbertoimport  = 25;
		
	
	$feedposter_feedenabled = isset($_REQUEST['feedposter_feedenabled']) ? 1 : 0;
	$feedposter_htmlenabled = isset($_REQUEST['feedposter_htmlenabled']) ? 1 : 0;
	$feedposter_topiclocked = isset($_REQUEST['feedposter_topiclocked']) ? 1 : 0;
		
	$feedposter_markread = isset($_REQUEST['feedposter_markread']) ? 1 : 0;
		
	// Verify the field url
	require_once($sourcedir . '/Subs-RSS.php');
	
    $json = 0;
    if (substr_count($feedposter_feedurl,'format=json') > 0)
    {
        $jsondata = disguise_curl($feedposter_feedurl);
        $jsondata2 = json_decode($jsondata);
        if (empty($jsondata2))
            fatal_error($txt['feedposter_err_nodownload'],false);
         $json = 1;
    }
    else
        verify_rss_url($feedposter_feedurl);

		
	// Lookup the Memeber ID of the postername
	$memid = 0;
	
	$dbresult = db_query("
	SELECT 
		realName, ID_MEMBER 
	FROM {$db_prefix}members 
	WHERE realName = '$feedposter_postername' OR memberName = '$feedposter_postername'  LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	
	if (db_affected_rows() != 0)
		$memid = $row['ID_MEMBER'];
	
	// Set the RSS Feed Update time
	$updatetime = time();
		
	// Insert into feedbot table
		db_query("
		INSERT INTO {$db_prefix}feedbot
			(ID_BOARD, feedurl, title, enabled, html, postername, ID_MEMBER, locked, 
			articlelink, topicprefix, numbertoimport, importevery, updatetime,markasread,
			msgicon,footer,json, id_topic)
		VALUES 
			($boardselect,'$feedposter_feedurl','$feedposter_feedtitle',$feedposter_feedenabled,
		 	$feedposter_htmlenabled, '$feedposter_postername', $memid, $feedposter_topiclocked,1,
		 	'$feedposter_topicprefix',$feedposter_numbertoimport,$feedposter_importevery,$updatetime,$feedposter_markread,
		 	'$msgicon','$footer','$json', '$topicid')", __FILE__, __LINE__);	
	
	// Redirect to the Feed Admin
	redirectexit('action=feedsadmin;sa=admin');
}

function EditFeed()
{
	global $txt, $mbname, $context, $db_prefix;
	
	// Get the Feed ID
	$id = (int) $_REQUEST['id'];
	
	if (empty($id))
		fatal_error($txt['feedposter_nofeedselected'], false);
	
	adminIndex('feeds_settings');
		
	// Show the boards for the feeds.
	$context['feed_boards'] = array('');
	$request = db_query("
	SELECT 
		b.ID_BOARD, b.name AS bName, c.name AS cName 
	FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c 
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
	
	while ($row = mysql_fetch_assoc($request))
		$context['feed_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	mysql_free_result($request);
	
	
	// Get the Feed Data
	$context['feed'] = array();
	
	$request = db_query("
	SELECT 
		ID_FEED, ID_BOARD, feedurl, title, postername, enabled, html, ID_MEMBER, locked, 
		articlelink, topicprefix, numbertoimport, importevery,markasread, msgicon, footer, id_topic   
	FROM {$db_prefix}feedbot 
	WHERE ID_FEED = $id LIMIT 1", __FILE__, __LINE__);
	
	$row = mysql_fetch_assoc($request);

	$context['feed'] = $row;

		
	mysql_free_result($request);
	
	// Load Message Icons
	$context['msg_icons'] = array();
	$result = db_query("SELECT title, filename
				FROM {$db_prefix}message_icons
				", __FILE__, __LINE__);
	while($row = mysql_fetch_assoc($result))
		$context['msg_icons'][] = $row;
	

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['feedposter_editfeed'];
	
	$context['sub_template']  = 'editfeed';
		
}

function EditFeed2()
{
	global $txt, $db_prefix, $sourcedir, $func;
	
	// Get the Feed ID
	$id = (int) $_REQUEST['id'];
	
	if (empty($id))
		fatal_error($txt['feedposter_nofeedselected'], false);
		
	// Get the Fields
	$feedposter_feedtitle = $func['htmlspecialchars']($_REQUEST['feedposter_feedtitle'], ENT_QUOTES);
	$feedposter_feedurl = $_REQUEST['feedposter_feedurl'];
	$boardselect = (int) $_REQUEST['boardselect'];
	$feedposter_postername = str_replace('"','', $_REQUEST['feedposter_postername']);
	$feedposter_postername = str_replace("'",'', $feedposter_postername);
	$feedposter_postername = str_replace('\\','', $feedposter_postername);
	
	$feedposter_postername = $func['htmlspecialchars']($feedposter_postername, ENT_QUOTES);
	$feedposter_topicprefix = $func['htmlspecialchars']($_REQUEST['feedposter_topicprefix'], ENT_QUOTES);
	$feedposter_importevery = (int) $_REQUEST['feedposter_importevery'];
	$feedposter_numbertoimport = (int) $_REQUEST['feedposter_numbertoimport'];
	
	$msgicon = $func['htmlspecialchars']($_REQUEST['msgicon'], ENT_QUOTES);
	$footer = $func['htmlspecialchars']($_REQUEST['footer'], ENT_QUOTES);
    $topicid = (int) $_REQUEST['topicid'];
	
	if ($feedposter_feedtitle == '')
		fatal_error($txt['feedposter_err_feedtitle'], false);

	if ($feedposter_feedurl == '')
		fatal_error($txt['feedposter_err_feedurl'], false);
		

	if ($feedposter_postername == '')
		fatal_error($txt['feedposter_err_postername'], false);
		
	if ($boardselect == 0)
		fatal_error($txt['feedposter_err_forum'], false);
	
	if ($feedposter_importevery < 5)
		$feedposter_importevery = 5;
	
	if ($feedposter_numbertoimport < 1)
		$feedposter_numbertoimport  = 1;
		
	if ($feedposter_numbertoimport > 50)
		$feedposter_numbertoimport  = 25;
		
	
	$feedposter_feedenabled = isset($_REQUEST['feedposter_feedenabled']) ? 1 : 0;
	$feedposter_htmlenabled = isset($_REQUEST['feedposter_htmlenabled']) ? 1 : 0;
	$feedposter_topiclocked = isset($_REQUEST['feedposter_topiclocked']) ? 1 : 0;
		
		
	$feedposter_markread = isset($_REQUEST['feedposter_markread']) ? 1 : 0;
		
	// Verify the field url
	require_once($sourcedir . '/Subs-RSS.php');
    $json = 0;
    if (substr_count($feedposter_feedurl,'format=json') > 0)
    {
        $jsondata = disguise_curl($feedposter_feedurl);
        $jsondata2 = json_decode($jsondata);
        if (empty($jsondata2))
            fatal_error($txt['feedposter_err_nodownload'],false);
        $json = 1;
    }
    else
        verify_rss_url($feedposter_feedurl);


		
	// Lookup the Memeber ID of the postername
	$memid = 0;
	
	$dbresult = db_query("
	SELECT 
		realName, ID_MEMBER 
	FROM {$db_prefix}members 
	WHERE realName = '$feedposter_postername' OR memberName = '$feedposter_postername'  LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	
	if (db_affected_rows() != 0)
		$memid = $row['ID_MEMBER'];
		
	// Update the feedbot
		db_query("
		UPDATE {$db_prefix}feedbot
		SET 
			ID_BOARD = $boardselect, feedurl = '$feedposter_feedurl', title = '$feedposter_feedtitle', enabled = $feedposter_feedenabled,
		  	html = $feedposter_htmlenabled, postername = '$feedposter_postername', ID_MEMBER = $memid,
		  	locked = $feedposter_topiclocked,articlelink = 1, topicprefix = '$feedposter_topicprefix',
		 	numbertoimport = $feedposter_numbertoimport, importevery = $feedposter_importevery, markasread = $feedposter_markread,
		 	msgicon = '$msgicon', footer = '$footer', json = '$json', id_topic = '$topicid'   
	    WHERE ID_FEED = $id LIMIT 1", __FILE__, __LINE__);	
	
	// Redirect to the Feed Admin
	redirectexit('action=feedsadmin;sa=admin');
}

function DeleteFeed()
{
	global $db_prefix, $txt;
	
	// Get the Feed ID
	$id = (int) $_REQUEST['id'];
	
	if (empty($id))
		fatal_error($txt['feedposter_nofeedselected'], false);	
		
		
	DeleteFeedByID($id);
	
	// Redirect to the Feed Admin
	redirectexit('action=feedsadmin;sa=admin');
	
}

function DeleteFeedByID($id)
{
	global $db_prefix;
	// Delete the feed
	db_query("
	DELETE 
	FROM {$db_prefix}feedbot 
	WHERE ID_FEED = $id LIMIT 1", __FILE__, __LINE__);
	
	// Delete all feed logs
	db_query("
	DELETE 
	FROM {$db_prefix}feedbot_log 
	WHERE ID_FEED = $id", __FILE__, __LINE__);
}

function SaveSettings()
{

	$rss_fakecron = isset($_REQUEST['rss_fakecron']) ? 1 : 0;

	$rss_feedmethod = htmlspecialchars($_REQUEST['rss_feedmethod'],ENT_QUOTES);
    
    $rss_embedimages = isset($_REQUEST['rss_embedimages']) ? 1 : 0;
    
    $rss_usedescription = isset($_REQUEST['rss_usedescription']) ? 1 : 0;
		
	// Save the setting information
	updateSettings(
	array(
	'rss_fakecron' => $rss_fakecron,
	'rss_feedmethod' => $rss_feedmethod,
    'rss_embedimages' => $rss_embedimages,
    'rss_usedescription' => $rss_usedescription,
	));
	
	// Redirect to the Feed Admin
	redirectexit('action=feedsadmin;sa=admin');
}

function BulkActions()
{
	global $db_prefix, $sourcedir;
	
	require_once($sourcedir . '/Subs-RSS.php');
	
	$runnow = 0;
	
	if (isset($_REQUEST['feed']))
	{
		$bulk = $_REQUEST['bulk'];
		
		foreach($_REQUEST['feed'] as $feed => $key)
		{
			$feed = (int) $feed;
			if ($bulk == 'delete')
				DeleteFeedByID($feed);
				
			if ($bulk == 'enablefeed')
				db_query("UPDATE {$db_prefix}feedbot SET enabled = 1 WHERE ID_FEED = $feed LIMIT 1", __FILE__, __LINE__);
					
			if ($bulk == 'disablefeed')
				db_query("UPDATE {$db_prefix}feedbot SET enabled = 0 WHERE ID_FEED = $feed LIMIT 1", __FILE__, __LINE__);
						
				
			if ($bulk == 'runnow')
			{
				db_query("UPDATE {$db_prefix}feedbot SET updatetime = " . (time() - 10) . " WHERE ID_FEED = $feed LIMIT 1", __FILE__, __LINE__);	
				$runnow = 1;
			}
				
		}
		
		
		if ($runnow == 1)
		{
			sleep(1);
			UpdateRSSFeedBots();
			UpdateJSONFeedBots();
		}


	}
	
	// Redirect to the Feed Admin
	redirectexit('action=feedsadmin;sa=admin');
}

?>