<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.4
*	Project manager: vicram10
*	Copyright 2011-2021
*	Powered by SMFSimple.com
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');
	
function ShowBlocksMain()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $sourcedir;
	
	//Load the Source/Subs-UltimatePortal-Init-Blocks.php
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');
	//Load the Source/Subs-UltimatePortal.php
	require_once($sourcedir . '/Subs-UltimatePortal.php');
		
	if (!allowedTo('ultimate_portal_cp'))		
		isAllowedTo('ultimate_portal_blocks');
		
	loadTemplate('UltimatePortal-BlocksMain');

	$subActions = array(
		'positions' => 'ShowBlockPositions',
		'save-positions' =>	'SavePositions',
		'blocks-titles' =>	'ShowBlockTitle',
		'save-blocks-titles' => 'SaveBlockTitle',	
		'create-blocks' => 'ShowCreateBlocks',			
		'add-block-html' => 'ShowAddBlockHTML',					
		'add-block-php' => 'ShowAddBlockPHP',							
		'admin-block' => 'ShowAdminBlock',									
		'blocks-edit' => 'SwitchBlockType',
		'blocks-html-edit' => 'EditBlockHtml',													
		'blocks-php-edit' => 'EditBlockPhp',															
		'blocks-perms' => 'ShowBlockPerms',
		'blocks-delete' => 'DeleteBlock',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'positions';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ultport_blocks_title'],
		'description' => $txt['ultport_blocks_description'],
		'tabs' => array(
			'positions' => array(
				'description' => $txt['ultport_blocks_description'],
			),
			'blocks-titles' => array(
				'description' => $txt['ultport_blocks_titles_description'],
			),
			'create-blocks' => array(
				'description' => $txt['ultport_create_blocks_description'],
			),
			'admin-block' => array(
				'description' => $txt['ultport_admin_bk_description'],
			),
		),
	);

	$subActions[$_REQUEST['sa']]();	

}

function ShowBlockPositions()
{
	global $db_prefix, $context, $scripturl, $txt;

	checkSession('get');
	
	//Calling the Block Directory and added a new blocks, on the block table, if that exist.
	UltimatePortalBlockDir();

	//Load the Block table (smf_ultimate_portal_blocks) where position = LEFT
	LoadBlocksTableLEFT();
	//Load the Block table (smf_ultimate_portal_blocks) where position = CENTER
	LoadBlocksTableCENTER();
	//Load the Block table (smf_ultimate_portal_blocks) where position = RIGHT
	LoadBlocksTableRIGHT();
	//Load MultiBlock Header
	LoadBlocksTableHEADER();
	//Load MultiBlock Footer
	LoadBlocksTableFOOTER();
	
	// Call the sub template.
	$context['sub_template'] = 'positions';
	$context['page_title'] = $txt['ultport_blocks_position_title'] . ' - ' . $txt['ultport_blocks_title'];

}

//Blocks: position save
function SavePositions()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $smcFunc, $cachedir;
	global $ultimateportalSettings;
	
	if (!isset($_POST['save']))
		redirectexit('action=admin;area=ultimate_portal_blocks;sa=positions');

	checkSession('post');
	
	//Reduce Site Overload is Checked? okay... if save blocks, edit blocks, delete existing cache files....
	RSODeleteCacheFiles();
	//End Reduce Site Overload
	
	$myquery = $smcFunc['db_query']('',"
				SELECT id 
				FROM {$db_prefix}ultimate_portal_blocks");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$id = (int) $row['id'];
		$title_form = $id."_title";
		$position_form = $id."_position";
		$progressive_form = $id."_progressive";
		$active_form = $id."_active";
		
		$title_form = isset($_POST[$title_form]) ?  up_db_xss($_POST[$title_form]) : '';
		$position_form = isset($_POST[$position_form]) ?  up_db_xss($_POST[$position_form]) : '';
		$progressive_form = isset($_POST[$progressive_form]) ?  up_db_xss($_POST[$progressive_form]) : '';
		$active_form = isset($_POST[$active_form]) ?  up_db_xss($_POST[$active_form]) : '';
				
		$smcFunc['db_query']('',"UPDATE {$db_prefix}ultimate_portal_blocks
			SET title ='$title_form', 
				position ='$position_form', 
				progressive ='$progressive_form', 
				active = '$active_form' 
			WHERE id='$id'");
	}
		
	//redirect the Blocks Positions
	redirectexit('action=admin;area=ultimate_portal_blocks;sa=positions;'. $context['session_var'] .'=' . $context['session_id']);

}

function ShowBlockTitle()
{
	global $db_prefix, $context, $scripturl, $txt;
	
	checkSession('get');
	//Load the blocks Titles
	LoadBlocksTitle();

	// Call the sub template.
	$context['sub_template'] = 'blocks_titles';
	$context['page_title'] = $txt['ultport_blocks_titles'] . ' - ' . $txt['ultport_blocks_title'];

}

//Blocks: Titles save
function SaveBlockTitle()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $smcFunc;
	
	if (!isset($_POST['save']))
		redirectexit('action=admin;area=ultimate_portal_blocks;sa=blocks-titles');
		
	checkSession('post');	
	$myquery = $smcFunc['db_query']('',"
				SELECT id 
				FROM {$db_prefix}ultimate_portal_blocks");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$id = $row['id'];
		$title_block = $id."_title";
		$title_block = isset($_POST[$title_block]) ?  up_db_xss($_POST[$title_block]) : '';


		//Now Updated the Ultimate portal Blocks Titles		
		$smcFunc['db_query']('',"UPDATE {$db_prefix}ultimate_portal_blocks
			SET title ='$title_block' 
			WHERE id='$id'");
	}
		
	//redirect the Blocks Titles
	redirectexit('action=admin;area=ultimate_portal_blocks;sa=blocks-titles;sesc=' . $context['session_id']);

}

function ShowCreateBlocks()
{
	global $db_prefix, $context, $scripturl, $txt;

	checkSession('get');
	// Call the sub template.
	$context['sub_template'] = 'create_blocks';
	$context['page_title'] = $txt['ultport_create_blocks_titles'] . ' - ' . $txt['ultport_blocks_title'];

}

function ShowAddBlockHTML()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $settings;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	
		
	if (isset($_POST['save']))
	{
		checkSession('post');		
		if ($_POST['bk-title'] == '')
			fatal_lang_error('ultport_error_no_add_bk_title',false);

		$title = up_convert_savedbadmin($_POST['bk-title']);
		$icon = !empty($_POST['icon']) ?  up_db_xss($_POST['icon']) : 'bk-html';
		$bk_collapse = !empty($_POST['can_collapse']) ?  up_db_xss($_POST['can_collapse']) : '';
		$bk_style = !empty($_POST['bk_style']) ?  up_db_xss($_POST['bk_style']) : '';
		$bk_no_title = !empty($_POST['no_title']) ?  up_db_xss($_POST['no_title']) : '';
		
		$textarea = up_convert_savedbadmin($_POST['elm1']);		
		//Now Insert the Ultimate portal Blocks HTML		
		$smcFunc['db_query']('',"INSERT INTO {$db_prefix}ultimate_portal_blocks (title, icon, personal, content, bk_collapse, bk_no_title, bk_style)
								VALUES ('$title', '$icon', '1', '$textarea', '$bk_collapse', '$bk_no_title', '$bk_style')");
		
		//redirect the Blocks Positions
		redirectexit('action=admin;area=ultimate_portal_blocks;sa=positions;sesc=' . $context['session_id']);
	}
	//Load image folder
	load_image_folder("/icons");
	//load the context_html_headers from Sources/Sub-Ultimate-Portal.php
	context_html_headers();
	
	// Call the sub template.
	$context['sub_template'] = 'add_block_html';
	$context['page_title'] = $txt['ultport_add_bk_html_titles'] . ' - ' . $txt['ultport_blocks_title'];

}

function ShowAddBlockPHP()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $boarddir;
	global $smcFunc;

	if(!isset($_POST['save']) && !isset($_POST['preview']))
		checkSession('get');	

	$title = $txt['ultport_add_bk_title'];
	$content = stripslashes($txt['ultport_tmp_bk_php_content']);
	$icon = 'bk-php';
	$bk_collapse = 'on';
	$bk_style = 'on';
	$bk_no_title = '';
	
	$context['preview'] = 0;
		
	//Save?
	if (isset($_POST['save'])) 
	{
		checkSession('post');		
		if ($_POST['bk-title'] == '')
			fatal_lang_error('ultport_error_no_add_bk_title',false);	
		//User-defined title & text values
		$title = stripslashes($_POST['bk-title']); //strip slashes added by $_POST
		$content = $_POST['content'];
		$content = trim('<?php'."\n".$content."\n".'?>');
		$icon = !empty($_POST['icon']) ?  up_db_xss($_POST['icon']) : 'bk-php';
		$bk_collapse = !empty($_POST['can_collapse']) ?  up_db_xss($_POST['can_collapse']) : '';
		$bk_style = !empty($_POST['bk_style']) ?  up_db_xss($_POST['bk_style']) : '';
		$bk_no_title = !empty($_POST['no_title']) ?  up_db_xss($_POST['no_title']) : '';
		//Create new block ID
		$title_file = strtolower($title);
		$title_file = str_replace (" ", "-", $title_file);
		$filename2 = "up-bk-";
		$filename2 .= $title_file;
		$filename2 .= ".php";
		$dir = $boarddir."/up-php-blocks/". $filename2;
		//Create cached php file		
		if (!$handle = fopen($dir, 'wb')) {
 			fatal_lang_error('ultport_error_no_add_bk_fopen_error',false);
			exit;
		}
		//Write $content to cached php file
   		if (!fwrite($handle, $content)) {
			fatal_lang_error('ultport_error_no_add_bk_nofile',false);
			exit;
   		}
		fclose($handle);
		//Close cached php file				
		
		//Insert filename, title, & content into database
		$smcFunc['db_query']('',"INSERT INTO {$db_prefix}ultimate_portal_blocks 
			(file, title, icon, personal, bk_collapse, bk_no_title, bk_style) 
			VALUES ('$filename2', '$title', '$icon', '2', '$bk_collapse', '$bk_no_title', '$bk_style')");		
		
		//redirect the Blocks Positions
		redirectexit('action=admin;area=ultimate_portal_blocks;sa=positions;sesc=' . $context['session_id']);		   		

	}
	//Preview?	
	if (isset($_POST['preview'])) {
		checkSession('post');
		$context['preview'] = 1;
		//User-defined title & text values
		$title =  up_db_xss($_POST['bk-title']); //strip slashes added by $_POST
		$content = $_POST['content'];
		$icon = !empty($_POST['icon']) ?  up_db_xss($_POST['icon']) : 'bk-php';
		$bk_collapse = !empty($_POST['can_collapse']) ?  up_db_xss($_POST['can_collapse']) : '';
		$bk_style = !empty($_POST['bk_style']) ?  up_db_xss($_POST['bk_style']) : '';
		$bk_no_title = !empty($_POST['no_title']) ?  up_db_xss($_POST['no_title']) : '';
		
		//Open tmp_block.php
		$filetext = $content;
				
		$filename = $boarddir."/up-php-blocks/tmp-bk.php";
		@chmod($boarddir."/up-php-blocks", 0777);
		@chmod($boarddir."/up-php-blocks/tmp-bk.php",0777);
				
		if (!$handle = fopen($filename, 'wb')) {
 			fatal_lang_error('ultport_error_no_add_bk_fopen_error',false);
			exit;
		}
		//Write content to tmp_block.php
   		if (!fwrite($handle, $filetext)) {
			fatal_lang_error('ultport_error_no_add_bk_nofile',false);
			exit;
   		}
		fclose($handle);   		
		
	}

	$context['title'] = $title;
	$context['content'] = $content;	
	$context['icon'] = $icon;
	$context['bk_collapse'] = $bk_collapse;
	$context['bk_style'] = $bk_style;
	$context['bk_no_title'] = $bk_no_title;
	
	//Load image folder
	load_image_folder("/icons");

	// Call the sub template.
	$context['sub_template'] = 'add_block_php';
	$context['page_title'] = $txt['ultport_add_bk_php_titles'] . ' - ' . $txt['ultport_blocks_title'];

}

function ShowAdminBlock()
{
	global $db_prefix, $context, $scripturl, $txt;

	checkSession('get');
	//load the Custom Block	
	CustomBlock();
	//load the System block
	SystemBlock();
	
	// Call the sub template.
	$context['sub_template'] = 'admin_block';
	$context['page_title'] = $txt['ultport_admin_bk_title'];

}

//Blocks: switch type - only redirect
function SwitchBlockType() {
	global $smcFunc, $context;
	
	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	$personal = $smcFunc['db_escape_string']($_REQUEST['personal']);
	$type_php = !empty($_REQUEST['type-php']) ? $smcFunc['db_escape_string']($_REQUEST['type-php']) : '';
	switch($personal) {
		case '2':
			//redirect the Blocks PHP edit (created block)
			redirectexit('action=admin;area=ultimate_portal_blocks;sa=blocks-php-edit;id='. $id .';type-php='.$type_php.';sesc=' . $context['session_id']);		   		
			break;
		case '1':
			//redirect the Blocks HTML edit
			redirectexit('action=admin;area=ultimate_portal_blocks;sa=blocks-html-edit;id='. $id.';sesc=' . $context['session_id']);		   		
			break;			
		default:
			//redirect the Blocks PHP edit (System block)
			redirectexit('action=admin;area=ultimate_portal_blocks;sa=blocks-php-edit;id='. $id .';type-php='.$type_php.';sesc=' . $context['session_id']);		   		
			break;
	}
	exit;
}

function EditBlockHtml()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	if (isset($_POST['save']))
	{
		checkSession('post');
		if ($_POST['bk-title'] == '')
			fatal_lang_error('ultport_error_no_add_bk_title',false);

		$id = (int) $_POST['id'];
		$title = up_convert_savedbadmin($_POST['bk-title']);
		$icon = !empty($_POST['icon']) ?  up_db_xss($_POST['icon']) : 'bk-html';
		$bk_collapse = !empty($_POST['can_collapse']) ?  up_db_xss($_POST['can_collapse']) : '';
		$bk_style = !empty($_POST['bk_style']) ?  up_db_xss($_POST['bk_style']) : '';
		$bk_no_title = !empty($_POST['no_title']) ?  up_db_xss($_POST['no_title']) : '';
		
		$textarea = up_convert_savedbadmin($_POST['elm1']);		

		//Now UPDATE the Ultimate portal Blocks HTML		
		$smcFunc['db_query']('',"UPDATE {$db_prefix}ultimate_portal_blocks 
				SET	title = '$title', 
					icon = '$icon', 
					content = '$textarea',
					bk_collapse = '$bk_collapse',
					bk_style = '$bk_style',
					bk_no_title = '$bk_no_title'
				WHERE id = '$id'");
		
		//redirect the Blocks Admin
		redirectexit('action=admin;area=ultimate_portal_blocks;sa=admin-block;sesc=' . $context['session_id']);
	}

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	$context['id'] = $smcFunc['db_escape_string']($id);
	
	$myquery = $smcFunc['db_query']('',"SELECT title, icon, content, bk_collapse, bk_style, bk_no_title FROM {$db_prefix}ultimate_portal_blocks WHERE id='$id'");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['title'] = stripslashes($row['title']);
		$context['icon'] = stripslashes($row['icon']);
		$context['content'] = stripslashes($row['content']);
		$context['bk_collapse'] = stripslashes($row['bk_collapse']);
		$context['bk_style'] = stripslashes($row['bk_style']);
		$context['bk_no_title'] = stripslashes($row['bk_no_title']);
	}

	//Load image folder
	load_image_folder("/icons");

	//load the context_html_headers from Sources/Sub-Ultimate-Portal.php
	context_html_headers();
	
	// Call the sub template.
	$context['sub_template'] = 'edit_block_html';
	$context['page_title'] = $txt['ultport_admin_edit_bk_html'];

}

function EditBlockPhp()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $boarddir;
	global $smcFunc;
	
	if(!isset($_POST['save']) && !isset($_POST['preview']))
		checkSession('get');	

	$type_php = !empty($_REQUEST['type-php']) ? $smcFunc['db_escape_string']($_REQUEST['type-php']) : '';
	$context['type_php'] = $type_php;
	if($type_php == 'created')
	{
		$use_folder = 'up-php-blocks';
		$context['use_folder'] = $use_folder;
	}
	if($type_php == 'system'){
		$use_folder = 'up-blocks';
		$context['use_folder'] = $use_folder;		
	}
		
	if (isset($_POST['save']))
	{
		checkSession('post');		
		//User input cleanup
		$id = intval($smcFunc['db_escape_string']($_REQUEST['id']));
		if ($_POST['bk-title'] == '')
			fatal_lang_error('ultport_error_no_add_bk_title',false);	
		//User-defined title & text values
		$title = stripslashes($_POST['bk-title']); //strip slashes added by $_POST
		$content = $_POST['content'];
		$content = trim($content);
		$icon =  up_db_xss($_POST['icon']);
		$bk_collapse = !empty($_POST['can_collapse']) ?  up_db_xss($_POST['can_collapse']) : '';
		$bk_style = !empty($_POST['bk_style']) ?  up_db_xss($_POST['bk_style']) : '';
		$bk_no_title = !empty($_POST['no_title']) ?  up_db_xss($_POST['no_title']) : '';

		$myquery = $smcFunc['db_query']('',"SELECT file FROM {$db_prefix}ultimate_portal_blocks WHERE id='$id'");
		while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
			$filename2 = stripslashes($row['file']);
		}
		//Folder?
		$use_folder = $smcFunc['db_escape_string']($_POST['use_folder']);
		//End
		$dir = $boarddir."/". $use_folder ."/". $filename2;
		//Create cached php file		
		if (!$handle = fopen($dir, 'wb')) {
 			fatal_lang_error('ultport_error_no_add_bk_fopen_error',false);
			exit;
		}
		//Write $content to cached php file
   		if (!fwrite($handle, '<?php'."\n".$content."\n".'?>')) {
			fatal_lang_error('ultport_error_no_add_bk_nofile',false);
			exit;
   		}
		fclose($handle);
		//Close cached php file				

		//Now UPDATE the Ultimate portal Blocks PHP			
		$smcFunc['db_query']('',"UPDATE {$db_prefix}ultimate_portal_blocks 
				SET	title = '$title',
					icon = '$icon',
					bk_collapse = '$bk_collapse',
					bk_style = '$bk_style',
					bk_no_title = '$bk_no_title'
				WHERE id = '$id'");
		
		//redirect the Blocks Admin
		redirectexit('action=admin;area=ultimate_portal_blocks;sa=admin-block;sesc=' . $context['session_id']);
		
	}	
	//Load image folder
	load_image_folder("/icons");

	$id = $smcFunc['db_escape_string']($_REQUEST['id']);	
	$context['id'] = $id;
	$context['preview'] = 1;
	$context['title'] = stripslashes(!empty($_POST['bk-title']) ?  up_db_xss($_POST['bk-title']) : '');
	$context['content'] = !empty($_POST['content']) ?  up_db_xss($_POST['content']) : '';
	$context['icon'] = stripslashes(!empty($_POST['icon']) ?  up_db_xss($_POST['icon']) : '');
	if (isset($_POST['preview']))
	{
		checkSession('post');
		$context['bk_collapse'] = empty($_POST['can_collapse']) ? '' :  up_db_xss($_POST['can_collapse']);
		$context['bk_style'] = empty($_POST['bk_style']) ? '' :  up_db_xss($_POST['bk_style']);
		$context['bk_no_title'] = empty($_POST['no_title']) ? '' :  up_db_xss($_POST['no_title']);
	}
	$myquery = $smcFunc['db_query']('',"SELECT id, file, title, icon, bk_collapse, bk_style, bk_no_title FROM {$db_prefix}ultimate_portal_blocks WHERE id='$id'");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		if (!$context['title'])
			$context['title'] = stripslashes($row['title']);
			
		$filename = stripslashes($row['file']);
		$context['icon'] = empty($_POST['icon']) ? stripslashes($row['icon']) : $_POST['icon'];
		if (!isset($_POST['preview']))
		{		
			$context['bk_collapse'] = stripslashes($row['bk_collapse']);
			$context['bk_style'] = stripslashes($row['bk_style']);
			$context['bk_no_title'] = stripslashes($row['bk_no_title']);
		}
	}
	
	//Cached title and text values
	if (!$context['content']) {
		if (!$handle = fopen($boarddir."/". $use_folder ."/".$filename, "rb")) {
 			fatal_lang_error('ultport_error_no_add_bk_fopen_error',false);
			exit;
		}
		$context['content'] = fread($handle, filesize($boarddir."/". $use_folder ."/".$filename));
		fclose($handle);
	}		
	$context['content'] =  trim($context['content']);
	$filetext = $context['content'];	
	$filename = $boarddir."/up-php-blocks/tmp-bk.php";
	
	if (!$handle = fopen($filename, 'wb')) {
		fatal_lang_error('ultport_error_no_add_bk_fopen_error',false);
		exit;
	}
	//Write content to tmp_block.php
	if (!fwrite($handle, $filetext)) {
		fatal_lang_error('ultport_error_no_add_bk_nofile',false);
		exit;
	}
	fclose($handle);   		

	// Call the sub template.
	$context['sub_template'] = 'edit_block_php';
	$context['page_title'] = $txt['ultport_admin_edit_bk_php'];

}

function ShowBlockPerms()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $boarddir;
	global $smcFunc;
	
	//catch - id
	$id = $smcFunc['db_escape_string']($_REQUEST['id']);

	if(!isset($_POST['save']))
		checkSession('get');	

	if (isset($_POST['save']))
	{
		checkSession('post');
		//go the update perms in Sources/Subs-UltimatePortal.php
		up_update_block_perms($id);
	}
		
	$myquery = $smcFunc['db_query']('',"SELECT id, title, perms FROM {$db_prefix}ultimate_portal_blocks WHERE id='$id'");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['id'] = $row['id'];
		$context['title'] = $row['title'];
		$context['perms'] = $row['perms'];
	}

	
	// We need the membergroups / Para poder usar un vector (array) que contenga el ID y nombre del grupo
	LoadMemberGroups();
	
	// Call the sub template.
	$context['sub_template'] = 'perms_block';
	$context['page_title'] = $context['title'] .' - '. $txt['ultport_admin_edit_bk_php'];

}

function DeleteBlock()
{
	global $db_prefix, $context, $scripturl, $txt;
	global $boarddir;
	global $smcFunc;
	
	checkSession('get');	
	//catch - id
	$id = $smcFunc['db_escape_string']($_REQUEST['id']);
	
	$myquery = $smcFunc['db_query']('',"SELECT file, personal FROM {$db_prefix}ultimate_portal_blocks WHERE id='$id'");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		if ($row['personal'] == '2')
		{
			$file = $boarddir."/up-php-blocks/". $row['file'];
			@unlink("$file");
		}
		if ($row['personal'] == '0')
		{
			$file = $boarddir."/up-blocks/". $row['file'];
			@unlink("$file");
		}		
	}


	$smcFunc['db_query']('',"DELETE FROM {$db_prefix}ultimate_portal_blocks WHERE id='$id'");	

	//redirect the Blocks Admin
	redirectexit('action=admin;area=ultimate_portal_blocks;sa=admin-block;sesc=' . $context['session_id']);

}

?>