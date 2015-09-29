<?php
/*
Welcome Topic Mod
Version 1.0
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function WelcomeTopic()
{
	// Check if they are allowed to admin the forum
	isAllowedTo('admin_forum');
	
	// Load the WelcomeTopic template
	loadtemplate('WelcomeTopic');
	
	// Load the language files
	if (loadlanguage('WelcomeTopic') == false)
		loadLanguage('WelcomeTopic','english');
		
		
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
	global $context, $mbname, $txt, $db_prefix;
	
	adminIndex('welcome_settings');

	// Load the main Welcome Topic
	$context['sub_template']  = 'main';

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['welcome_settings'];	
	
	// Show the boards for the feeds.
	$context['welcome_boards'] = array('');
	$request = db_query("
	SELECT 
		b.ID_BOARD, b.name AS bName, c.name AS cName 
	FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c 
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
	
	while ($row = mysql_fetch_assoc($request))
		$context['welcome_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
		
	mysql_free_result($request);
	
	// Get all the Welcome Topics
	$context['welcome_topics'] = array();
	
	$request = db_query("
	SELECT 
		ID, welcomesubject
	FROM {$db_prefix}welcome", __FILE__, __LINE__);
	
	while ($row = mysql_fetch_assoc($request))
	{

		$context['welcome_topics'][] = array(
			'ID' => $row['ID'],
			'SUBJECT' => $row['welcomesubject'],

		);
		
		
	}
		
	mysql_free_result($request);

	
}

function WelcomeTopicSettings2()
{
	global $db_prefix;
	
	$boardselect = (int) $_REQUEST['boardselect'];
	$welcome_postername = str_replace('"','', $_REQUEST['welcome_postername']);
	$welcome_postername = str_replace("'",'', $welcome_postername);
	$welcome_postername = str_replace('\\','', $welcome_postername);
	$welcome_postername = htmlspecialchars($welcome_postername, ENT_QUOTES);
	
	// Get the topic name
	// Lookup the Memeber ID of the postername
	$memid = 0;
	
	$dbresult = db_query("
	SELECT 
		realName, ID_MEMBER 
	FROM {$db_prefix}members 
	WHERE realName = '$welcome_postername' OR memberName = '$welcome_postername'  LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	
	if (db_affected_rows() != 0)
		$memid = $row['ID_MEMBER'];
	
	

	// Save the setting information
	updateSettings(
	array('welcome_boardid' => $boardselect,
	'welcome_membername' => $welcome_postername,
	'welcome_memberid' => $memid,
	));

	
	// Redirect to Welcome Topic settings page
	redirectexit('action=welcome;sa=admin');
}

function AddTopic()
{
	global $db_prefix, $txt, $context, $mbname, $modSettings, $user_info, $settings;
	

	adminIndex('welcome_settings');

	loadlanguage('Post');

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	// Initialize smiley array...
	$context['smileys'] = array(
		'postform' => array(),
		'popup' => array(),
	);

	if(function_exists('parse_bbc'))
		$esmile = 'embarrassed.gif';
	else
		$esmile = 'embarassed.gif';
	// Load smileys - don't bother to run a query if we're not using the database's ones anyhow.
	if (empty($modSettings['smiley_enable']) && $user_info['smiley_set'] != 'none')
		$context['smileys']['postform'][] = array(
			'smileys' => array(
				array('code' => ':)', 'filename' => 'smiley.gif', 'description' => $txt[287]),
				array('code' => ';)', 'filename' => 'wink.gif', 'description' => $txt[292]),
				array('code' => ':D', 'filename' => 'cheesy.gif', 'description' => $txt[289]),
				array('code' => ';D', 'filename' => 'grin.gif', 'description' => $txt[293]),
				array('code' => '>:(', 'filename' => 'angry.gif', 'description' => $txt[288]),
				array('code' => ':(', 'filename' => 'sad.gif', 'description' => $txt[291]),
				array('code' => ':o', 'filename' => 'shocked.gif', 'description' => $txt[294]),
				array('code' => '8)', 'filename' => 'cool.gif', 'description' => $txt[295]),
				array('code' => '???', 'filename' => 'huh.gif', 'description' => $txt[296]),
				array('code' => '::)', 'filename' => 'rolleyes.gif', 'description' => $txt[450]),
				array('code' => ':P', 'filename' => 'tongue.gif', 'description' => $txt[451]),
				array('code' => ':-[', 'filename' => $esmile, 'description' => $txt[526]),
				array('code' => ':-X', 'filename' => 'lipsrsealed.gif', 'description' => $txt[527]),
				array('code' => ':-\\', 'filename' => 'undecided.gif', 'description' => $txt[528]),
				array('code' => ':-*', 'filename' => 'kiss.gif', 'description' => $txt[529]),
				array('code' => ':\'(', 'filename' => 'cry.gif', 'description' => $txt[530])
			),
			'last' => true,
		);
	elseif ($user_info['smiley_set'] != 'none')
	{
		$request = db_query("
			SELECT code, filename, description, smileyRow, hidden
			FROM {$db_prefix}smileys
			WHERE hidden IN (0, 2)
			ORDER BY smileyRow, smileyOrder", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($request))
			$context['smileys'][empty($row['hidden']) ? 'postform' : 'popup'][$row['smileyRow']]['smileys'][] = $row;
		mysql_free_result($request);
	}

	// Clean house... add slashes to the code for javascript.
	foreach (array_keys($context['smileys']) as $location)
	{
		foreach ($context['smileys'][$location] as $j => $row)
		{
			$n = count($context['smileys'][$location][$j]['smileys']);
			for ($i = 0; $i < $n; $i++)
			{
				$context['smileys'][$location][$j]['smileys'][$i]['code'] = addslashes($context['smileys'][$location][$j]['smileys'][$i]['code']);
				$context['smileys'][$location][$j]['smileys'][$i]['js_description'] = addslashes($context['smileys'][$location][$j]['smileys'][$i]['description']);
			}

			$context['smileys'][$location][$j]['smileys'][$n - 1]['last'] = true;
		}
		if (!empty($context['smileys'][$location]))
			$context['smileys'][$location][count($context['smileys'][$location]) - 1]['last'] = true;
	}
	$settings['smileys_url'] = $modSettings['smileys_url'] . '/' . $user_info['smiley_set'];

	// Allow for things to be overridden.
	if (!isset($context['post_box_columns']))
		$context['post_box_columns'] = 60;
	if (!isset($context['post_box_rows']))
		$context['post_box_rows'] = 12;
	if (!isset($context['post_box_name']))
		$context['post_box_name'] = 'topicbody';
	if (!isset($context['post_form']))
		$context['post_form'] = 'cprofile';


	// Set a flag so the sub template knows what to do...
	$context['show_bbc'] = !empty($modSettings['enableBBC']) && !empty($settings['show_bbc']);

	// Generate a list of buttons that shouldn't be shown - this should be the fastest way to do this.
	if (!empty($modSettings['disabledBBC']))
	{
		$disabled_tags = explode(',', $modSettings['disabledBBC']);
		foreach ($disabled_tags as $tag)
			$context['disabled_tags'][trim($tag)] = true;
	}
	
	// Load the add topic template
	$context['sub_template']  = 'addtopic';

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['welcome_addtopic'];
	
}
function AddTopic2()
{
	global $db_prefix, $func, $txt;
	
	$topicsubject = $func['htmlspecialchars']($_REQUEST['topicsubject'], ENT_QUOTES);
	$topicbody = $func['htmlspecialchars']($_REQUEST['topicbody'], ENT_QUOTES);
		
	if ($topicsubject == '')
		fatal_error($txt['welcome_err_nosubject'], false);
		
	if ($topicbody == '')
		fatal_error($txt['welcome_err_nobody'], false);
		
		// Insert the Topic
		db_query("INSERT INTO {$db_prefix}welcome
			(welcomesubject, welcomebody)
		VALUES ('$topicsubject', '$topicbody')", __FILE__, __LINE__);	
	
	// Redirect to the main settings
	redirectexit('action=welcome;sa=admin');
}
function EditTopic()
{
	global $txt, $context, $db_prefix, $mbname, $user_info, $modSettings, $settings;
	
	
	adminIndex('welcome_settings');

	// Load the edit topic template
	$context['sub_template']  = 'edittopic';

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['welcome_edittopic'];
	
	
	// Get the Topic ID	
	$id = (int) $_REQUEST['id'];
	
	
	$request = db_query("
	SELECT 
		ID, welcomesubject, welcomebody
	FROM {$db_prefix}welcome
	WHERE ID = $id LIMIT 1", __FILE__, __LINE__);
	
	$row = mysql_fetch_assoc($request);

		$context['welcome_topic']= array(
			'ID' => $row['ID'],
			'SUBJECT' => $row['welcomesubject'],
			'BODY' => $row['welcomebody'],
		);

		
	mysql_free_result($request);
	
	loadlanguage('Post');

		// Check if spellchecking is both enabled and actually working.
		$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

		// Initialize smiley array...
		$context['smileys'] = array(
			'postform' => array(),
			'popup' => array(),
		);

		if(function_exists('parse_bbc'))
			$esmile = 'embarrassed.gif';
		else
			$esmile = 'embarassed.gif';

		// Load smileys - don't bother to run a query if we're not using the database's ones anyhow.
		if (empty($modSettings['smiley_enable']) && $user_info['smiley_set'] != 'none')
			$context['smileys']['postform'][] = array(
				'smileys' => array(
					array('code' => ':)', 'filename' => 'smiley.gif', 'description' => $txt[287]),
					array('code' => ';)', 'filename' => 'wink.gif', 'description' => $txt[292]),
					array('code' => ':D', 'filename' => 'cheesy.gif', 'description' => $txt[289]),
					array('code' => ';D', 'filename' => 'grin.gif', 'description' => $txt[293]),
					array('code' => '>:(', 'filename' => 'angry.gif', 'description' => $txt[288]),
					array('code' => ':(', 'filename' => 'sad.gif', 'description' => $txt[291]),
					array('code' => ':o', 'filename' => 'shocked.gif', 'description' => $txt[294]),
					array('code' => '8)', 'filename' => 'cool.gif', 'description' => $txt[295]),
					array('code' => '???', 'filename' => 'huh.gif', 'description' => $txt[296]),
					array('code' => '::)', 'filename' => 'rolleyes.gif', 'description' => $txt[450]),
					array('code' => ':P', 'filename' => 'tongue.gif', 'description' => $txt[451]),
					array('code' => ':-[', 'filename' => $esmile, 'description' => $txt[526]),
					array('code' => ':-X', 'filename' => 'lipsrsealed.gif', 'description' => $txt[527]),
					array('code' => ':-\\', 'filename' => 'undecided.gif', 'description' => $txt[528]),
					array('code' => ':-*', 'filename' => 'kiss.gif', 'description' => $txt[529]),
					array('code' => ':\'(', 'filename' => 'cry.gif', 'description' => $txt[530])
				),
				'last' => true,
			);
		elseif ($user_info['smiley_set'] != 'none')
		{
			$request = db_query("
				SELECT code, filename, description, smileyRow, hidden
				FROM {$db_prefix}smileys
				WHERE hidden IN (0, 2)
				ORDER BY smileyRow, smileyOrder", __FILE__, __LINE__);
			while ($row = mysql_fetch_assoc($request))
				$context['smileys'][empty($row['hidden']) ? 'postform' : 'popup'][$row['smileyRow']]['smileys'][] = $row;
			mysql_free_result($request);
		}

		// Clean house... add slashes to the code for javascript.
		foreach (array_keys($context['smileys']) as $location)
		{
			foreach ($context['smileys'][$location] as $j => $row)
			{
				$n = count($context['smileys'][$location][$j]['smileys']);
				for ($i = 0; $i < $n; $i++)
				{
					$context['smileys'][$location][$j]['smileys'][$i]['code'] = addslashes($context['smileys'][$location][$j]['smileys'][$i]['code']);
					$context['smileys'][$location][$j]['smileys'][$i]['js_description'] = addslashes($context['smileys'][$location][$j]['smileys'][$i]['description']);
				}

				$context['smileys'][$location][$j]['smileys'][$n - 1]['last'] = true;
			}
			if (!empty($context['smileys'][$location]))
				$context['smileys'][$location][count($context['smileys'][$location]) - 1]['last'] = true;
		}
		$settings['smileys_url'] = $modSettings['smileys_url'] . '/' . $user_info['smiley_set'];

		// Allow for things to be overridden.
		if (!isset($context['post_box_columns']))
			$context['post_box_columns'] = 60;
		if (!isset($context['post_box_rows']))
			$context['post_box_rows'] = 12;
		if (!isset($context['post_box_name']))
			$context['post_box_name'] = 'topicbody';
		if (!isset($context['post_form']))
			$context['post_form'] = 'cprofile';


		// Set a flag so the sub template knows what to do...
		$context['show_bbc'] = !empty($modSettings['enableBBC']) && !empty($settings['show_bbc']);

		// Generate a list of buttons that shouldn't be shown - this should be the fastest way to do this.
		if (!empty($modSettings['disabledBBC']))
		{
			$disabled_tags = explode(',', $modSettings['disabledBBC']);
			foreach ($disabled_tags as $tag)
				$context['disabled_tags'][trim($tag)] = true;
		}
	
}

function EditTopic2()
{
	global $func, $db_prefix, $txt;
	
	$id = (int) $_REQUEST['id'];
	
	$topicsubject = $func['htmlspecialchars']($_REQUEST['topicsubject'], ENT_QUOTES);
	$topicbody = $func['htmlspecialchars']($_REQUEST['topicbody'], ENT_QUOTES);
	
	if ($topicsubject == '')
		fatal_error($txt['welcome_err_nosubject'], false);
		
	if ($topicbody == '')
		fatal_error($txt['welcome_err_nobody'], false);
				
	// Update the Topic
		db_query("UPDATE {$db_prefix}welcome 
		SET welcomesubject = '$topicsubject', welcomebody = '$topicbody' 
		WHERE ID = $id LIMIT 1", __FILE__, __LINE__);	
	
	// Redirect to the main settings
	redirectexit('action=welcome;sa=admin');
	
}

function DeleteTopic()
{
	global $db_prefix;
	
	// Get the Topic ID
	$id = (int) $_REQUEST['id'];
	
	
	db_query("DELETE FROM {$db_prefix}welcome 
			WHERE ID = " . $id, __FILE__, __LINE__);
	
	// Redirect make to the main config page
	redirectexit('action=welcome;sa=admin');
	
}

?>