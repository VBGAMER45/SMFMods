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
	
function UPFaqMain()
{
	global $sourcedir, $context, $txt;
	global $ultimateportalSettings;
	
	//Inicialized the Ultimate Portal?
	$context['ultimate_portal_initialized'] = true;	

	// Load UltimatePortal template
	loadtemplate('UPFaq');
	// Load Language
	if (loadlanguage('UPFaq') == false)
		loadLanguage('UPFaq','english');

	//Is active module?
	if(empty($ultimateportalSettings['faq_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	//Load Important Files
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Subs-UltimatePortal.php');
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');	
	
	//Load subactions for the Ultimate Portal - Module NEWS
	$subActions = array(
		'main' => 'Main',
		'add' => 'AddFaq',
		'add-section' => 'AddSection',		
			'edit-faq' => 'EditFaq',
			'edit-section' => 'EditSection',
			'del-faq' => 'DeleteFaq',
			'del-section' => 'DeleteSection',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$subActions[$_REQUEST['sa']]();	


}

function Main()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings;

	//Active Module?
	if (empty($ultimateportalSettings['faq_enable']))
		fatal_lang_error('ultport_error_no_active',false);
	//End 
	
	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=faq',
		'name' => $txt['up_module_faq_title']
	);

	//Load FAQS Page
	LoadFAQMain();
	
	// Call the sub template.
	$context['sub_template'] = 'main';
	$context['page_title'] = $txt['up_module_title'] . ' - ' . $txt['up_module_faq_title'];

}

function AddFaq()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings, $user_info;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Active Module?
	if (empty($ultimateportalSettings['faq_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	//You Can Add?
	if (empty($user_info['up-modules-permissions']['faq_add']) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_perm',false);

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=faq',
		'name' => $txt['up_module_faq_title']
	);

	//Load Section	
	LoadFaqSection();
	//Create Section?
	if (empty($context['view_section']))
		fatal_lang_error('ultport_error_no_add_section',false);

	//SAVE?
	if (isset($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['question']) || empty($_POST['answer']))
			fatal_lang_error('ultport_error_no_empty',false);
		
		$question = $smcFunc['htmlspecialchars']($_POST['question'], ENT_QUOTES);
		$answer = $smcFunc['htmlspecialchars']($_POST['answer'], ENT_QUOTES);						
		$id_section = (int) $_POST['section'];
		
		//Now insert 
		$smcFunc['db_query']('',"
		INSERT INTO {db_prefix}up_faq(id, question, answer, id_section) 
					VALUES(0, '$question', '$answer', $id_section)");
		
		//redirect 
		redirectexit('action=faq');				
	}

	// Used for the custom editor
	// Now create the editor.
	$editorOptions = array(
		'id' => 'answer',
		'value' => '',
		'form' => 'faqform',
	);
	$context['smileyBox_container'] = 'smileyBox_'.$editorOptions['id'];
	$context['bbcBox_container'] = 'bbcBox_'.$editorOptions['id'];
	up_create_control_richedit($editorOptions);	
	$context['post_box_name'] = $editorOptions['id'];		

	// Call the sub template.
	$context['sub_template'] = 'add_faq';
	$context['page_title'] = $txt['up_faq_add'];

}

function AddSection()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings, $user_info;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Active Module?
	if (empty($ultimateportalSettings['faq_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	//You Can Add?
	if (empty($user_info['up-modules-permissions']['faq_add']) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_perm',false);

	//SAVE?
	if (isset($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_title',false);
		
		$title = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);
		//Now insert 
		$smcFunc['db_query']('',"
		INSERT INTO {db_prefix}up_faq_section(id_section, section) 
					VALUES(0, '$title')");
		
		//redirect 
		redirectexit('action=faq');				
	}
	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=faq',
		'name' => $txt['up_module_faq_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'add_section';
	$context['page_title'] = $txt['up_faq_add_section'];

}

function EditFaq()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings, $user_info;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Active Module?
	if (empty($ultimateportalSettings['faq_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	//You Can Add?
	if (empty($user_info['up-modules-permissions']['faq_moderate']) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_perm',false);

	//SAVE?
	if (isset($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['question']) || empty($_POST['answer']))
			fatal_lang_error('ultport_error_no_empty',false);
		
		$id = (int) $smcFunc['db_escape_string']($_POST['id']);
		$question = $smcFunc['htmlspecialchars']($_POST['question'], ENT_QUOTES);
		$answer = $smcFunc['htmlspecialchars']($_POST['answer'], ENT_QUOTES);						
		$id_section = (int) $_POST['section'];
		
		//Now insert 
		$smcFunc['db_query']('',"
			UPDATE {db_prefix}up_faq
				SET question = '$question', 
					answer = '$answer', 
					id_section = $id_section 
			WHERE id = {int:id}",
			array(
				'id' => $id,	  
			)
		);
		
		//redirect 
		redirectexit('action=faq');				
	}

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=faq',
		'name' => $txt['up_module_faq_title']
	);

	$id = $smcFunc['db_escape_string']($_GET['id']);
	$id = (int) $id;
	//Load Section	
	LoadFaqSection();
	//Load Specific FAQ'S
	LoadFAQSpecific($id);
	
	// Used for the custom editor
	// Now create the editor.
	$editorOptions = array(
		'id' => 'answer',
		'value' => $context['answer'],
		'form' => 'faqform',
	);
	$context['smileyBox_container'] = 'smileyBox_'.$editorOptions['id'];
	$context['bbcBox_container'] = 'bbcBox_'.$editorOptions['id'];
	up_create_control_richedit($editorOptions);	
	$context['post_box_name'] = $editorOptions['id'];		

	// Call the sub template.
	$context['sub_template'] = 'edit_faq';
	$context['page_title'] = $txt['up_faq_edit'];

}

function EditSection()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings, $user_info;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Active Module?
	if (empty($ultimateportalSettings['faq_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	//You Can Add?
	if (empty($user_info['up-modules-permissions']['faq_moderate']) && !$user_info['is_admin'])
		fatal_lang_error('ultport_error_no_perm',false);

	//SAVE?
	if (isset($_POST['save']))
	{
		checkSession('post');
		if (empty($_POST['title']))
			fatal_lang_error('ultport_error_no_add_title',false);
		
		$id_section = $smcFunc['db_escape_string']($_POST['id_section']);
		$title = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);
		//Now insert 
		$smcFunc['db_query']('',"
			UPDATE {db_prefix}up_faq_section
				SET section = '$title'
			WHERE id_section = $id_section"
		);
		
		//redirect 
		redirectexit('action=faq');				
	}

	//Load the FAQ Specific SECTION
	$id = $smcFunc['db_escape_string']($_GET['id']);
	$id = (int) $id;
	LoadFaqSpecificSection($id);

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=faq',
		'name' => $txt['up_module_faq_title']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'edit_section';
	$context['page_title'] = $txt['up_faq_edit_section'];

}

//Delete Specific FAQ?
function DeleteFaq()
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc, $boarddir;

	checkSession('get');
	//Security
	//Check if user can Delete
	if (empty($user_info['up-modules-permissions']['faq_moderate']) && !$user_info['is_admin'])								
		fatal_lang_error('ultport_error_no_perm',false);
		
	$id = (int) $_GET['id'];

	//Empty id FAQ?
	if (empty($id))
		fatal_lang_error('ultport_error_no_delete',false);
		
	//Ok, delete 
	$smcFunc['db_query']('',"
		DELETE FROM {db_prefix}up_faq
		WHERE id = $id");
	//End	
		
	//Ok now Redirect 
	redirectexit('action=faq');				
}

//Delete Section?
function DeleteSection()
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc, $boarddir;

	checkSession('get');
	//Security
	//Check if user can Delete
	if (empty($user_info['up-modules-permissions']['faq_moderate']) && !$user_info['is_admin'])								
		fatal_lang_error('ultport_error_no_perm',false);
		
	$id_section = (int) $_GET['id'];

	//Empty id FAQ?
	if (empty($id_section))
		fatal_lang_error('ultport_error_no_delete_section',false);
		
	//Ok, delete FAQ's for this section
	$smcFunc['db_query']('',"
		DELETE FROM {db_prefix}up_faq
		WHERE id_section = $id_section");
	//Ok, delete this section
	$smcFunc['db_query']('',"
		DELETE FROM {db_prefix}up_faq_section
		WHERE id_section = $id_section");
	
	//Ok now Redirect 
	redirectexit('action=faq');				
}

?>