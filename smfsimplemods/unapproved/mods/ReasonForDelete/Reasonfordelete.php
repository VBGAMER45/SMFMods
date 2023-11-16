<?php
/**
 * Reason For Delete (SMF)
 *
 * @package SMF
 * @author 4kstore
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 *
 * @version 1.0
 */
 
if (!defined('SMF'))
	die('Hacking attempt...');

function ReasonfordeleteAdmin()
{
	global $context, $txt;

	if (!allowedTo('admin_forum'))
		isAllowedTo('admin_forum');
		
	loadTemplate('Reasonfordelete');
	
	//Load subactions for the ultimate portal preferences
	$subActions = array(
		'main' => 'ShowSettingsMain',
		'add' => 'AddProfileReason',
		'edit' => 'EditProfileReason',
		'delete' => 'DeleteProfileReason',
	);
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['rfd_menu_button'] . ' - ' . $txt['rfd_settings'],
		'description' => $txt['rfd_description'],
		'tabs' => array(
			'main' => array(
				'description' => $txt['rfd_description'],
			),
		),
	);
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';
	$context['sub_action'] = $_REQUEST['sa'];
	$subActions[$_REQUEST['sa']]();	

}

function Reasonfordelete()
{
	global $context, $txt, $modSettings;
	
	//Is Active?
	if (empty($modSettings['rfd_enabled']))
	redirectexit('action=removetopic2;topic='.$context['current_topic'].'.0;'.$context['session_var'].'='.$context['session_id'].'');
	
	loadTemplate('Reasonfordelete');
	loadrfdTable();
	infoTopic($context['current_topic']);
	
	//Load template
	$context['page_title'] = $txt['rfd_menu_button'];
	$context['sub_template'] = 'ShowRFD';	
}	

function infoTopic($id)
{
	global $smcFunc, $context,$scripturl;
	
	if (empty($id))
	fatal_lang_error('rfd_error_noid',false);

	$context['inforows'] = array();
	$inforows = array();
	$request = $smcFunc['db_query']('', "
		SELECT id_topic, id_board, id_member, subject, poster_name
		FROM {db_prefix}messages
		WHERE id_topic = {int:id}
		LIMIT 1",
		array(
				'id' => $id,
			  )
		);
		
	 while($row = $smcFunc['db_fetch_assoc']($request))
	 {
		  $inforows = &$context['inforows'][];		
		  $inforows['id_topic'] = $row['id_topic'];
		  $inforows['id_board'] = $row['id_board'];
		  $inforows['id_member'] = $row['id_member'];
		  $inforows['subject'] = $row['subject'];
		  $inforows['poster_name'] = $row['poster_name'];
		  $inforows['subjecthref'] = '<a href="'. $scripturl .'?topic='. $row['id_topic'] .'">'.$row['subject'].'</a>';
		  $inforows['posterhref'] = '<a href="'. $scripturl .'?action=profile;u='. $row['id_member'] .'">'.$row['poster_name'].'</a>';	  
	 }	 
	$smcFunc['db_free_result']($request);
	
}

function ShowSettingsMain()
{
	global $context, $txt, $smcFunc;

	if (!isset($_POST['save']))
			
	//Load Functions
	loadrfdTable();
	
	// Saving?
	if (isset($_POST['save']))
	{	   
			
		$rfd_settings = array(
			'rfd_enabled' => !empty($_POST['rfd_enabled']) ? '1' : '0',
			'rfd_titleset' => !empty($_POST['rfd_titleset']) ? $smcFunc['htmlspecialchars']($_POST['rfd_titleset'],ENT_QUOTES) : '',
			'rfd_senderid' => !empty($_POST['rfd_senderid']) ? (int) $smcFunc['htmlspecialchars']($_POST['rfd_senderid'],ENT_QUOTES) : '',
		);   
		updateSettings($rfd_settings);	
		redirectexit('action=admin;area=reasonfordelete;sesc='.$context['session_id']);
	}
	
	// Call the sub template.
	$context['sub_template'] = 'rfd_settings';
	$context['page_title'] = $txt['rfd_menu_button'];

}

function AddProfileReason()
{
	global $db_prefix, $context, $txt, $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');		
		
	//Save 
	if (isset($_POST['save']))
	{	
		checkSession('post');
		
		$title = (!empty($_POST['title'])) ? (string) $smcFunc['htmlspecialchars']($_POST['title'],ENT_QUOTES): '';
		$description = (!empty($_POST['description'])) ? (string) $smcFunc['htmlspecialchars']($_POST['description'],ENT_QUOTES) : '';
		$default_text = (!empty($_POST['default_text'])) ? (string) $smcFunc['htmlspecialchars']($_POST['default_text'],ENT_QUOTES): '';
		
		if(!empty($title) && !empty($default_text)) //at least the title and the default text should be completed
		//Now insert the section in the smf_up_download_sections 
		$smcFunc['db_query']('',"INSERT INTO 
					{db_prefix}rfdmod 		(title,description,default_text) 
								 VALUES 	({string:title},{string:description},{string:default_text})",
								 array(
									'description' => $description,
									'title' => $title,
									'default_text' => $default_text,									
									)
								 
								 );				
		redirectexit('action=admin;area=reasonfordelete;sa=main;sesc=' . $context['session_id']);
	}
	//End Save
	// Call the sub template.
	$context['sub_template'] = 'rfd_add';
	$context['page_title'] = $txt['rfd_AddProfiles'];
}

function EditProfileReason()
{
	global $context, $smcFunc, $txt;
	if(!isset($_POST['save']))
		checkSession('get');	
		
	//Save 
	if (isset($_POST['save']))
	{	
		checkSession('post');
			
		$id = (!empty($_POST['id'])) ? (int) $smcFunc['db_escape_string']($_POST['id']) : '';
		$title = (!empty($_POST['title'])) ? (string) $smcFunc['htmlspecialchars']($_POST['title'],ENT_QUOTES) : '';
		$description = (string) (!empty($_POST['description'])) ? (string) $smcFunc['htmlspecialchars']($_POST['description'],ENT_QUOTES) : '';
		$default_text = (string) (!empty($_POST['default_text'])) ? (string) $smcFunc['htmlspecialchars']($_POST['default_text'],ENT_QUOTES) : '';
				
		$smcFunc['db_query']('','
			UPDATE {db_prefix}rfdmod 
			SET description  = {string:description}, title = {string:title}, default_text = {string:default_text}
			WHERE id_rfd = {int:id}',
		array(
			'description' => $description,
			'title' => $title,
			'default_text' => $default_text,
			'id' => $id
			  )
		);				
				
		redirectexit('action=admin;area=reasonfordelete;sa=main;sesc=' . $context['session_id']);
	}
	//End Save
	if (empty($_REQUEST['id']))
		fatal_lang_error('rfd_error_noid',false);

	$id = (int) $smcFunc['db_escape_string']($_REQUEST['id']);
	
	//Load selected Section
	loadEdit($id);
	
	// Call the sub template.
	$context['sub_template'] = 'rfd_edit';
	$context['page_title'] = $txt['rfd_EditProfiles'];

}

function DeleteProfileReason()
{
	global $context, $smcFunc;
	checkSession('get');	
		if (empty($_REQUEST['id']))
		fatal_lang_error('rfd_error_noid',false);
		
		$id = (int) $smcFunc['db_escape_string']($_REQUEST['id']);
		//Now is delete the profiles
		$smcFunc['db_query']('',"DELETE FROM {db_prefix}rfdmod WHERE id_rfd = {int:id}",
		array(
				'id' => $id,
			  )		
		);
				
		//redirect
		redirectexit('action=admin;area=reasonfordelete;sa=main;sesc=' . $context['session_id']);				
}


function loadrfdTable()
{
	global $smcFunc, $context,$scripturl,$settings,$txt;
	$context['rfdrows'] = array();
	$rfdrows = array();
	
	$request = $smcFunc['db_query']('', "
		SELECT id_rfd, title, description, default_text
		FROM {db_prefix}rfdmod"
		);
		

	 while($row = $smcFunc['db_fetch_assoc']($request))
	 {
		$rfdrows = &$context['rfdrows'][];		
          $rfdrows['id'] = $row['id_rfd'];
          $rfdrows['title'] = !empty($row['title']) ? $row['title'] :'';
          $rfdrows['description'] = !empty($row['description']) ? $row['description'] :'';
		  $rfdrows['default_text'] = parse_bbc($row['default_text']);
		  $rfdrows['edit'] = '<a href="'. $scripturl .'?action=admin;area=reasonfordelete;sa=edit;id='. $row['id_rfd'] .';sesc=' . $context['session_id'].'"><img src="'. $settings['default_images_url'] . '/buttons/edit.png"  alt="'. $txt['rfd_EditProfiles'] .'" title="'. $txt['rfd_EditProfiles'] .'"/></a>';
		  $rfdrows['delete'] = '<a  style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=reasonfordelete;sa=delete;id='. $row['id_rfd'] .';sesc=' . $context['session_id'].'"><img src="'. $settings['default_images_url'] . '/buttons/del.png" alt="'. $txt['rfd_delete'] .'" title="'. $txt['rfd_delete'] .'"/></a>';
		  
	 }	 
	$smcFunc['db_free_result']($request);
	
}

function loadEdit($id)
{
	global $smcFunc, $context;
	if (empty($_REQUEST['id']))
		fatal_lang_error('rfd_error_noid',false);

	$context['rfdrows'] = array();
	$rfdrows = array();
	
	$request = $smcFunc['db_query']('', "
		SELECT id_rfd, title, description, default_text
		FROM {db_prefix}rfdmod
		WHERE id_rfd = {int:id} 
		LIMIT 1",
		array(
				'id' => $id,
			  )		
		);
	
	if ($smcFunc['db_num_rows']($request) == 0)
        fatal_lang_error('rfd_error_noid',false);
	
	 while($row = $smcFunc['db_fetch_assoc']($request))
	 {
		$rfdrows = &$context['rfdrows'][];		
          $rfdrows['id'] = $row['id_rfd'];
          $rfdrows['title'] = !empty($row['title']) ? $row['title'] : '';
          $rfdrows['description'] = !empty($row['description']) ? $row['description'] : '';
		  $rfdrows['default_text'] = !empty($row['default_text']) ? $row['default_text'] : '';		  
	 }	 
	$smcFunc['db_free_result']($request);
	
}

function loadReasons($id)
{
	global $smcFunc, $context;
	
	$context['loadreas'] = array();
	$loadreas = array();
	$request = $smcFunc['db_query']('', "
		SELECT id_rfd, default_text
		FROM {db_prefix}rfdmod
		WHERE id_rfd = {int:id}",
		array(
				'id' => $id,
			  )
		);
		
	 while($row = $smcFunc['db_fetch_assoc']($request))
	 {
		  $loadreas = &$context['loadreas'][];		
          $loadreas['id_rfd'] = $row['id_rfd'];
          $loadreas['default_text'] = !empty($row['default_text']) ? $row['default_text'] : '';	  
	 }	 
	 
	$smcFunc['db_free_result']($request);
	$context['reasontext'] = !empty($loadreas['default_text']) ? $loadreas['default_text'] : '';
}


?>