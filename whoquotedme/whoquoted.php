<?php
/*
Who Quoted Me
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

function WhoQuotedMain()
{
	// Load the language files
	if (loadlanguage('whoquoted') == false)
		loadLanguage('whoquoted','english');

	// Load template
	loadtemplate('whoquoted');

	// Sub Action Array
	$subActions = array(
		'settings' => 'WhoQuotedSettings',
		'settings2' => 'WhoQuotedSettings2',
		'rebuild' => 'WhoQuotedRebuildQuoteLog'
	);

	if (isset($_REQUEST['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';

	if (isset($subActions[$sa]) &&  !empty($subActions[$sa]))
		$subActions[$sa]();
	else
		WhoQuotedMe();
}

function WhoQuotedMe()
{
	global $txt, $context, $db_prefix, $ID_MEMBER, $scripturl, $user_info;
	
	is_not_guest();
	
	$context['start'] = (int) $_REQUEST['start'];
	
	$request = db_query("
			SELECT 
				COUNT(*) as total
			FROM {$db_prefix}quoted_log as l
			LEFT JOIN {$db_prefix}messages as m ON (m.id_msg = l.id_msg)
			LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board) 
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = l.id_member_from) 
		WHERE l.id_member = $ID_MEMBER AND $user_info[query_see_board]
			", __FILE__, __LINE__);
	$totalRow = mysql_fetch_assoc($request);
	mysqli_free_result($request);
	
	// Fetch all the messages for the user
	$dbresult = db_query("
		SELECT
			l.id_msg, l.id_topic, l.logdate, l.id_member_from,
			m.subject, mem.realName 
			 
		FROM {$db_prefix}quoted_log as l
			LEFT JOIN {$db_prefix}messages as m ON (m.id_msg = l.id_msg)
			LEFT JOIN {$db_prefix}boards AS b ON (b.id_board = m.id_board) 
			LEFT JOIN {$db_prefix}members AS mem ON (mem.id_member = l.id_member_from) 
		WHERE l.id_member = $ID_MEMBER AND $user_info[query_see_board]
		ORDER BY l.logdate DESC
		 LIMIT  " . $context['start'] .",20", __FILE__, __LINE__);
		 
		 ;
		 

	$messages = array();
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$messages[] = $row;
	}

	mysql_free_result($dbresult);
	
	
	$context['who_quoted_msgs'] = $messages;
		
	// Setup the paging
	$context['page_index'] = constructPageIndex($scripturl . '?action=whoquoted', $_REQUEST['start'], $totalRow['total'], 20);
		

	// Set template
	$context['sub_template'] = 'whoquoted_display';

	// Set page title
	$context['page_title'] = $txt['whoquoted_txt_me'];
	
}

function WhoQuotedSettings()
{
	global $txt, $context;
	
	isAllowedTo('admin_forum');
	adminIndex('whoquoted_settings');
	
	// Set template
	$context['sub_template'] = 'whoquoted_settings';

	// Set page title
	$context['page_title'] = $txt['whoquoted_admin'];


}

function WhoQuotedSettings2()
{
	isAllowedTo('admin_forum');
	
	// Security Check
	checkSession('post');

	// Settings
	$whoquoted_enabled = isset($_REQUEST['whoquoted_enabled']) ? 1 : 0;	

		updateSettings(
	array(
	'whoquoted_enabled' => $whoquoted_enabled,
	));
	
	
	// Redirect to the admin area
	redirectexit('action=whoquoted;sa=settings');
}

function WhoQuotedRebuildQuoteLog()
{
	global $txt, $modSettings, $context, $db_prefix;

	isAllowedTo('admin_forum');
	adminIndex('whoquoted_settings');


	// Increase the max time to process the images
	@ini_set('max_execution_time', '900');

	$context['start'] = empty($_REQUEST['start']) ? 100 : (int) $_REQUEST['start'];

	$request = db_query("
	SELECT
		COUNT(*)
	FROM {$db_prefix}messages
	", __FILE__, __LINE__);
	list($totalProcess) = mysql_fetch_row($request);
	mysqli_free_result($request);

	// Initialize the variables.
	$increment = 100;
	if (empty($_REQUEST['start']))
		$_REQUEST['start'] = 0;


	$dbresult = db_query("
		SELECT
			id_member, id_msg, id_topic, body, posterTime   
		FROM {$db_prefix}messages
		 LIMIT " . $context['start']  . ",$increment"
		 , __FILE__, __LINE__);
		 
		 
		 
	$counter = 0;
	$messages = array();
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$messages[] = $row;
	}
	mysqli_free_result($dbresult);

	foreach($messages as $row)
	{
		WhoQuoted_ParseMessage($row['id_msg'],$row['id_topic'],$row['body'],$row['id_member'],$row['posterTime'],true);

		$counter++;

	}

	$_REQUEST['start'] += $increment;

	$complete = 0;
	if ($_REQUEST['start'] < $totalProcess)
	{

		$context['continue_get_data'] = 'start=' . $_REQUEST['start'];
		$context['continue_percent'] = round(100 * $_REQUEST['start'] / $totalProcess);


	}
	else
		$complete = 1;

	// Redirect back to the category
	if ($complete == 1)
		redirectexit('action=whoquoted;sa=settings');
	else
	{
		$context['sub_template']  = 'rebuild_quotelog';

		$context['page_title'] =  $txt['whoquoted_txt_rebuild'];

	}

}

function WhoQuoted_ParseMessage($id_msg = 0, $id_topic = 0, $body ,$postMemberID = 0, $posterTime = 0, $reIndex = false)
{

	global $modSettings, $db_prefix;
	global $sourcedir, $scripturl;

	if (empty($modSettings['whoquoted_enabled']))
		return;
		
	$postMemberID = (int) $postMemberID;

	$quoteList = array();
	// Lets get all the quotes in the message
	preg_match_all("~\[quote author=(.+?) link=(.+?) date=(.+?)\]~smi", $body, $quoteList);

	if (count($quoteList[1]) > 0)
	{
		// Get unique member names
		$quotedMembers = array_unique($quoteList[1]);

		asort($quotedMembers);
		$list = '';
		foreach($quotedMembers as $item)
		{
			if (!empty($list))
				$list .= ',';

			$list .= "'" .$item . "'";
		}


		$result = db_query("
		SELECT 
			id_member
		FROM {$db_prefix}members
		WHERE memberName IN($list) OR realName IN($list)", __FILE__, __LINE__);




		$insertMembers = array();

		while ($row = mysql_fetch_assoc($result))
		{
			// Don't quote yourself
			if ($postMemberID != $row['id_member'])
				$insertMembers[] = $row['id_member'];
		}	
		
		mysql_free_result($result);


		// Clear Log to handle case when modify posts
		db_query("DELETE FROM {$db_prefix}quoted_log WHERE id_msg = $id_msg", __FILE__, __LINE__);

		if (!empty($insertMembers))
		foreach($insertMembers as $memberID)
		{
			$t = time();
		 	$memberID = (int)  $memberID;
		 	
		 	if (!empty($posterTime))
		 		$t = $posterTime;
			


	db_query("INSERT INTO {$db_prefix}quoted_log 
			(id_member,id_member_from,id_topic,id_msg,logdate)
		VALUES ($memberID,$postMemberID, $id_topic,$id_msg,$t)", __FILE__, __LINE__);


			if ($reIndex == false)
			{
				// Push Notification support
				if (file_exists($sourcedir . '/webpush.php'))
				{
					require_once($sourcedir . '/webpush.php');
					
					
					$dbresult = db_query("SELECT subject FROM {db_prefix}messages WHERE id_msg = $id_msg", __FILE__, __LINE__);
					$subjectRow = mysql_fetch_assoc($dbresult);
					mysqli_free_result($dbresult);
					
					
					$dbresult = db_query("SELECT realName FROM {db_prefix}members WHERE id_member = $postMemberID", __FILE__, __LINE__);
					$memRow = mysql_fetch_assoc($dbresult);
					mysqli_free_result($dbresult);
					
					webpush_send($memberID,$postMemberID,$memRow['realName'],'quote',$scripturl . '?topic=' . $id_topic . ".msg=" . $id_msg,array('title' => $subjectRow['subject']));
				}
			}

			
		}

	}
	

}


?>