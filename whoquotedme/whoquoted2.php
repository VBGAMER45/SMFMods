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
	loadtemplate('whoquoted2');

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
	global $txt, $context, $smcFunc, $user_info, $scripturl;
	
	is_not_guest();
	
	$context['start'] = (int) $_REQUEST['start'];
	
	$request = $smcFunc['db_query']('', "
			SELECT 
				COUNT(*) as total
			FROM {db_prefix}quoted_log as l
			LEFT JOIN {db_prefix}messages as m ON (m.id_msg = l.id_msg)
			LEFT JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board) 
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = l.id_member_from) 
		WHERE l.id_member = {int:userid} AND {query_see_board} 
			",
			array(
		 	'userid' => $user_info['id'],
		 ));
	$totalRow = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
	
	// Fetch all the messages for the user
	$dbresult = $smcFunc['db_query']('', "
		SELECT
			l.id_msg, l.id_topic, l.logdate, l.id_member_from,
			m.subject, mem.real_name 
			 
		FROM {db_prefix}quoted_log as l
			LEFT JOIN {db_prefix}messages as m ON (m.id_msg = l.id_msg)
			LEFT JOIN {db_prefix}boards AS b ON (b.id_board = m.id_board) 
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = l.id_member_from) 
		WHERE l.id_member = {int:userid} AND {query_see_board} 
		ORDER BY l.logdate DESC
		 LIMIT  {int:start},20",
		  array(
		 	'start' => $context['start'],
		 	'userid' => $user_info['id'],
		 )
		 );
		 
		 ;
		 

	$messages = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$messages[] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
	
	
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
	
	// Set template
	$context['sub_template'] = 'whoquoted_settings';

	// Set page title
	$context['page_title'] = $txt['whoquoted_admin'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['whoquoted_admin'],
			'description' => '',
			'label' => $txt['whoquoted_admin'],
			'tabs' => array(
				'settings' => array(
					'label' => $txt['whoquoted_txt_settings'],
					'description' => $txt['whoquoted_txt_settings_desc'],
				),
				'rebuild' => array(
					'label' => $txt['whoquoted_txt_rebuild'],	
					'description' => '',
				),

			),
		);	

}

function WhoQuotedSettings2()
{	isAllowedTo('admin_forum');
	
	// Security Check
	checkSession('post');

	// Settings
	$whoquoted_enabled = isset($_REQUEST['whoquoted_enabled']) ? 1 : 0;	

		updateSettings(
	array(
	'whoquoted_enabled' => $whoquoted_enabled,
	));
	
	
	// Redirect to the admin area
	redirectexit('action=admin;area=whoquoted;sa=settings');
}

function WhoQuotedRebuildQuoteLog()
{
	global $smcFunc, $txt, $modSettings, $context;

	isAllowedTo('admin_forum');
	
	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['whoquoted_admin'],
			'description' => '',
			'label' => $txt['whoquoted_admin'],
			'tabs' => array(
				'settings' => array(
					'label' => $txt['whoquoted_txt_settings'],
					'description' => $txt['whoquoted_txt_settings_desc'],
				),
				'rebuild' => array(
					'label' => $txt['whoquoted_txt_rebuild'],	
					'description' => '',
				),

			),
		);	

	// Increase the max time to process the images
	@ini_set('max_execution_time', '900');

	$context['start'] = empty($_REQUEST['start']) ? 100 : (int) $_REQUEST['start'];

	$request = $smcFunc['db_query']('', "
	SELECT
		COUNT(*)
	FROM {db_prefix}messages
	");
	list($totalProcess) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Initialize the variables.
	$increment = 100;
	if (empty($_REQUEST['start']))
		$_REQUEST['start'] = 0;


	$dbresult = $smcFunc['db_query']('', "
		SELECT
			id_member, id_msg, id_topic, body, poster_time   
		FROM {db_prefix}messages
		 LIMIT {int:start},{int:increment}",
		 array(
		 	'start' => $_REQUEST['start'],
		 	'increment' => $increment,
		 )
		 );
		 
		 
		 
	$counter = 0;
	$messages = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$messages[] = $row;
	}
	$smcFunc['db_free_result']($dbresult);

	foreach($messages as $row)
	{
		WhoQuoted_ParseMessage($row['id_msg'],$row['id_topic'],$row['body'],$row['id_member'],$row['poster_time'],true);

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
		redirectexit('action=admin;area=whoquoted;sa=settings');
	else
	{
		$context['sub_template']  = 'rebuild_quotelog';

		$context['page_title'] =  $txt['whoquoted_txt_rebuild'];

	}

}

function WhoQuoted_ParseMessage($id_msg = 0, $id_topic = 0, $body = '' ,$postMemberID = 0, $posterTime = 0, $reIndex = false)
{
	global $modSettings, $smcFunc;
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

		$result = $smcFunc['db_query']('', "
		SELECT 
			id_member
		FROM {db_prefix}members
		WHERE member_name IN({array_string:quotedmembers}) OR real_name IN({array_string:quotedmembers})",
		array(
			'quotedmembers' => $quotedMembers,
			)
		);

		$insertMembers = array();

		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			// Don't quote yourself
			if ($postMemberID != $row['id_member'])
				$insertMembers[] = $row['id_member'];
		}	
		
		$smcFunc['db_free_result']($result);
		
		// Clear Log to handle case when modify posts
		$smcFunc['db_query']('', "DELETE FROM {db_prefix}quoted_log WHERE id_msg = {int:id_msg}",array(
						'id_msg' => $id_msg
						));

		if (!empty($insertMembers))
		foreach($insertMembers as $memberID)
		{
			$t = time();
		 	$memberID = (int)  $memberID;
		 	
		 	if (!empty($posterTime))
		 		$t = $posterTime;
			
			$smcFunc['db_insert']('insert',
				'{db_prefix}quoted_log',
				array('id_member' => 'int', 'id_member_from' => 'int', 'id_topic' => 'int', 'id_msg' => 'int', 'logdate' => 'int'),
				array($memberID, $postMemberID, $id_topic,$id_msg,$t),
				array('id_log')
			);

			if ($reIndex == false)
			{
				// Push Notification support
				if (file_exists($sourcedir . '/webpush2.php'))
				{
					require_once($sourcedir . '/webpush2.php');
					
					
					$dbresult = $smcFunc['db_query']('', "SELECT subject FROM {db_prefix}messages WHERE id_msg = {int:id_msg}",
					array(
						'id_msg' => $id_msg
						)
					);
					$subjectRow = $smcFunc['db_fetch_assoc']($dbresult);
					$smcFunc['db_free_result']($dbresult);
					
					
					$dbresult = $smcFunc['db_query']('', "SELECT real_name FROM {db_prefix}members WHERE id_member = {int:id_member}",
					array(
						'id_member' => $postMemberID,
						)
					);
					$memRow = $smcFunc['db_fetch_assoc']($dbresult);
					$smcFunc['db_free_result']($dbresult);
					
					webpush_send($memberID,$postMemberID,$memRow['real_name'],'quote',$scripturl . '?topic=' . $id_topic . ".msg=" . $id_msg,array('title' => $subjectRow['subject']));
				}
			}

			
		}

	}
	

}


?>