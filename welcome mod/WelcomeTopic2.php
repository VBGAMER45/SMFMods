<?php
/*
Welcome Topic Mod
Version 2.0
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function WelcomeTopic()
{
	global $txt;
	// Check if they are allowed to admin the forum
	isAllowedTo('admin_forum');
	
	// Load the WelcomeTopic template
	loadtemplate('WelcomeTopic2');
	
	// Load the language files
	if (loadlanguage('WelcomeTopic') == false)
		loadLanguage('WelcomeTopic','english');
		
	$txt['welcome_topicnote'] = str_replace("{","[",$txt['welcome_topicnote']);
	$txt['welcome_topicnote'] = str_replace("}","]",$txt['welcome_topicnote']);
		
	// Welcome Topic actions
	$subActions = array(
		'admin' => 'WelcomeTopicSettings',
		'admin2' => 'WelcomeTopicSettings2',
		'add' => 'AddTopic',
		'add2' => 'AddTopic2',
		'edit' => 'EditTopic',
		'edit2' => 'EditTopic2',
		'delete' => 'DeleteTopic',
		
	);

	// Follow the sa or main Welcome Topic Settings page.
	if (!empty($subActions[@$_GET['sa']]))
		$subActions[$_GET['sa']]();
	else
		WelcomeTopicSettings();	
	
}

function WelcomeTopicSettings()
{	
	global $context, $mbname, $txt, $smcFunc;
	
	DoWelcomeAdminTabs();

	// Load the main Welcome Topic
	$context['sub_template']  = 'main';

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['welcome_settings'];	
	
	// Show the boards for the feeds.
	$context['welcome_boards'] = array('');
	$request = $smcFunc['db_query']('',"
	SELECT 
		b.ID_BOARD, b.name AS bName, c.name AS cName 
	FROM {db_prefix}boards AS b, {db_prefix}categories AS c 
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
	
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['welcome_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
		
	$smcFunc['db_free_result']($request);
	
	// Get all the Welcome Topics
	$context['welcome_topics'] = array();
	
	$request = $smcFunc['db_query']('',"
	SELECT 
		ID, welcomesubject
	FROM {db_prefix}welcome");
	
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{

		$context['welcome_topics'][] = array(
			'ID' => $row['ID'],
			'SUBJECT' => $row['welcomesubject'],

		);
		
		
	}
		
	$smcFunc['db_free_result']($request);

	
}

function WelcomeTopicSettings2()
{
	global $smcFunc;
	
	$boardselect = (int) $_REQUEST['boardselect'];
	$welcome_postername = str_replace('"','', $_REQUEST['welcome_postername']);
	$welcome_postername = str_replace("'",'', $welcome_postername);
	$welcome_postername = str_replace('\\','', $welcome_postername);
	$welcome_postername = htmlspecialchars($welcome_postername, ENT_QUOTES);
	
	// Get the topic name
	// Lookup the Memeber ID of the postername
	$memid = 0;
	
	$dbresult = $smcFunc['db_query']('',"
	SELECT 
		real_name, ID_MEMBER 
	FROM {db_prefix}members 
	WHERE real_name = '$welcome_postername' OR member_name = '$welcome_postername'  LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	
	if ($smcFunc['db_affected_rows']() != 0)
		$memid = $row['ID_MEMBER'];
	
	

	// Save the setting information
	updateSettings(
	array('welcome_boardid' => $boardselect,
	'welcome_membername' => $welcome_postername,
	'welcome_memberid' => $memid,
	));

	
	// Redirect to Welcome Topic settings page
	redirectexit('action=admin;area=welcome;sa=admin');
}

function AddTopic()
{
	global $smcFunc, $txt, $context, $mbname, $modSettings, $sourcedir;
	

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	
	// Load the add topic template
	$context['sub_template']  = 'addtopic';

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['welcome_addtopic'];
	
// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'topicbody',
		'value' => '',
		'width' => '90%',
		'form' => 'cprofile',
		'labels' => array(
			'addtopic' => ''
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	

	
	
}

function AddTopic2()
{
	global $smcFunc, $txt, $sourcedir;
	
	
	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['topicbody_mode']) && isset($_REQUEST['topicbody']))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['topicbody'] = html_to_bbc($_REQUEST['topicbody']);

		// We need to unhtml it now as it gets done shortly.
		$_REQUEST['topicbody'] = un_htmlspecialchars($_REQUEST['topicbody']);

	}
	
	$topicsubject = $smcFunc['htmlspecialchars']($_REQUEST['topicsubject'], ENT_QUOTES);
	$topicbody = $smcFunc['htmlspecialchars']($_REQUEST['topicbody'], ENT_QUOTES);
		
	if ($topicsubject == '')
		fatal_error($txt['welcome_err_nosubject'], false);
		
	if ($topicbody == '')
		fatal_error($txt['welcome_err_nobody'], false);
		
		
		// Insert the Topic
		$smcFunc['db_query']('',"INSERT INTO {db_prefix}welcome 
			(welcomesubject, welcomebody)
		VALUES ({string:topicsubject}, {string:topicbody})",
		array('topicsubject' => $topicsubject,
		'topicbody' => $topicbody,
		));	
	
	// Redirect to the main settings
	redirectexit('action=admin;area=welcome;sa=admin');
}

function EditTopic()
{
	global $txt, $context, $smcFunc, $mbname, $sourcedir, $modSettings;

	// Load the edit topic template
	$context['sub_template']  = 'edittopic';

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['welcome_edittopic'];
	
	// Get the Topic ID	
	$id = (int) $_REQUEST['id'];
	
	
	$request = $smcFunc['db_query']('',"
	SELECT 
		ID, welcomesubject, welcomebody
	FROM {db_prefix}welcome
	WHERE ID = $id LIMIT 1");
	
	$row = $smcFunc['db_fetch_assoc']($request);

		$context['welcome_topic']= array(
			'ID' => $row['ID'],
			'SUBJECT' => $row['welcomesubject'],
			'BODY' => $row['welcomebody'],
		);

		
	$smcFunc['db_free_result']($request);
	
	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	
// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'topicbody',
		'value' => $context['welcome_topic']['BODY'],
		'width' => '90%',
		'form' => 'cprofile',
		'labels' => array(
			'addtopic' => ''
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	

	
	
}

function EditTopic2()
{
	global $smcFunc, $txt, $sourcedir;
	
	$id = (int) $_REQUEST['id'];
	
	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['topicbody_mode']) && isset($_REQUEST['topicbody']))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['topicbody'] = html_to_bbc($_REQUEST['topicbody']);

		// We need to unhtml it now as it gets done shortly.
		$_REQUEST['topicbody'] = un_htmlspecialchars($_REQUEST['topicbody']);

	}
	
	$topicsubject = $smcFunc['htmlspecialchars']($_REQUEST['topicsubject'], ENT_QUOTES);
	$topicbody = $smcFunc['htmlspecialchars']($_REQUEST['topicbody'], ENT_QUOTES);
	
	if ($topicsubject == '')
		fatal_error($txt['welcome_err_nosubject'], false);
		
	if ($topicbody == '')
		fatal_error($txt['welcome_err_nobody'], false);
				
	// Update the Topic
		$smcFunc['db_query']('',"UPDATE {db_prefix}welcome 
		SET welcomesubject = {string:topicsubject}, welcomebody = {string:topicbody} 
		WHERE ID = $id LIMIT 1",
		array('topicsubject' => $topicsubject,
		'topicbody' => $topicbody,
		));	
		

	
	// Redirect to the main settings
	redirectexit('action=admin;area=welcome;sa=admin');
	
}

function DeleteTopic()
{
	global $smcFunc;
	
	// Get the Topic ID
	$id = (int) $_REQUEST['id'];
	
	
	$smcFunc['db_query']('',"DELETE FROM {db_prefix}welcome 
			WHERE ID = " . $id);
	
	// Redirect make to the main config page
	redirectexit('action=admin;area=welcome;sa=admin');
	
}

function DoWelcomePost($memberName = '', $memberID = 0)
{
	global $smcFunc, $modSettings, $sourcedir;
	
	if (empty($memberName))
	{
		$result = $smcFunc['db_query']('',"
		SELECT 
			real_name 
		FROM {db_prefix}members 
		WHERE ID_MEMBER = $memberID LIMIT 1");
		$memRow = $smcFunc['db_fetch_assoc']($result);
		$smcFunc['db_free_result']($result);
		
		$memberName = $memRow['real_name'];
	}
	
	
	
	require_once($sourcedir . '/Subs-Post.php');
	
	if ($modSettings['welcome_boardid'] != 0)
	{

		
		$result = $smcFunc['db_query']('',"
		SELECT 
			welcomesubject, welcomebody 
		FROM {db_prefix}welcome 
		 ORDER BY RAND() LIMIT 1");
		if ($smcFunc['db_num_rows']($result) != 0)
		{
			$row2 =  $smcFunc['db_fetch_assoc']($result);


						$msgOptions = array(
									'id' => 0,
									'subject' => str_replace("[username]",$memberName,$row2['welcomesubject']),
									'body' => str_replace("[username]",$memberName,$row2['welcomebody']),
									'icon' => 'xx',
									'smileys_enabled' => 1,
									'attachments' => array(),
								);
								$topicOptions = array(
									'id' => 0,
									'board' => $modSettings['welcome_boardid'],
									'poll' => null,
									'lock_mode' => null,
									'sticky_mode' => null,
									'mark_as_read' => false,
								);
								$posterOptions = array(
									'id' => $modSettings['welcome_memberid'],
									'name' => $modSettings['welcome_membername'],
									'email' => '',
									'update_post_count' => (($modSettings['welcome_memberid'] == 0) ? 0 : 1),
								);

			createPost($msgOptions, $topicOptions, $posterOptions);
		}

		$smcFunc['db_free_result']($result);
								
								
	}
}

function DoWelcomeAdminTabs($overrideSelected = '')
{
	global $context, $txt;


		@$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' =>  $txt['welcome_admin'],
			'description' => '',
			'tabs' => array(
				'admin' => array(
					'description' => '',
					'label' => '',
				),



				
				
			),
		);	


}


?>