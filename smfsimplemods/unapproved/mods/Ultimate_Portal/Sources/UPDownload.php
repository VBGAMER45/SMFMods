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
	
function UPDownloadMain()
{
	global $sourcedir, $context, $txt;
	global $ultimateportalSettings;
	
	//Inicialized the Ultimate Portal?
	$context['ultimate_portal_initialized'] = true;	

	// Load UltimatePortal Settings
	ultimateportalSettings();
	
	// Load UltimatePortal template
	loadtemplate('UPDownload');
	// Load Language
	if (loadlanguage('UPDownload') == false)
		loadLanguage('UPDownload','english');

	//Is active the Download module?
	if(empty($ultimateportalSettings['download_enable']))
		fatal_lang_error('ultport_error_no_active',false);

	//Load Important Files
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Subs-UltimatePortal.php');
	require_once($sourcedir . '/Subs-UltimatePortal-Init-Blocks.php');	
	
	//Load subactions for the Ultimate Portal - Module NEWS
	$subActions = array(
		'main' => 'ShowDownMain',
		'view-more-last' => 'ShowViewMoreLast',
		'new' => 'NewDownload',
		'search' => 'SearchDownload',
		'view' => 'ViewFiles',
		'download' => 'download_file',
		'edit' => 'EditFile',
		'delete' => 'DeleteFile',
		'approved' => 'ApprovedFile',
		'unapproved' => 'UnapprovedFile',
		'stats' => 'ShowStats',
		'profile' => 'UserProfile',
	);
	
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';

	$subActions[$_REQUEST['sa']]();	

}

function ShowDownMain()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	//Enable Download Module?
	if (empty($ultimateportalSettings['download_enable']))
		fatal_lang_error('ultport_error_no_active',false);			
		
	//News Link-tree
	$context['down-linktree'] = '<img alt="" style="float:left" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=downloads">'. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a>';	

	//Load the Download section
	LoadDownloadSection('view', 0, 'portal');

	if (empty($context['view']))
		fatal_lang_error('down_error_no_section',false);		

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=downloads',
		'name' => $txt['down_module_title'] . ' - ' . $txt['down_module_title2']
	);
		
	// Call the sub template.
	$context['sub_template'] = 'down_main';
	$context['page_title'] = $txt['down_module_title'] . ' - ' . $txt['down_module_title2'];

}

function ShowViewMoreLast()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	//Enable Download Module?
	if (empty($ultimateportalSettings['download_enable']))
		fatal_lang_error('ultport_error_no_active',false);			

	$id_section = !empty($_REQUEST['section']) ? (int) $_REQUEST['section'] : 0;
	
	//not Request download id?
	if (empty($id_section))
		fatal_lang_error('down_error_no_action',false);			
	
	//Downloads more Popular
	LoadFileInformationRows('WHERE f.id_section = s.id AND s.id = '. $id_section .' AND f.approved = 1 ORDER BY f.total_downloads DESC LIMIT 5', 'popular');

	//Last Files Added
	LoadFileInformationRows('WHERE f.id_section = s.id AND s.id = '. $id_section .' AND f.approved = 1 ORDER BY f.id_files DESC LIMIT 5', 'last');

	$content = '';
	if (!empty($context['view-file-rows-popular']) && !empty($context['view-file-rows-last']))
	{
		$content .= '			
		<table cellpadding="5" cellspacing="2" width="100%">
			<tr>
				<td valign="top" align="left" width="50%">
					<div id="DownloadInfo">
						<div id="DownloadPopular">
							<h4>'. $txt['down_module_more_popular_title'] .'</h4>';
					if ($context['view-file-rows-popular'])
					{
						$content .= '		
							<ol>';
							$i = 1;
							foreach($context['file-popular'] as $file)
							{
								$content .= '									
								<li class="num'. $i .'"><a href="'. $scripturl .'?action=downloads;sa=view;download='. $file['id_files-popular'] .'">'. $file['title-popular'] .'</a></li>';
								$i++;
							}	
			$content .= '
							</ol>';
					}
			$content .= '				
						</div>		
						<div id="LastFiles">
							<h4>'. $txt['down_module_last_files_title'] .'</h4>';
					if ($context['view-file-rows-last'])
					{
						$content .= '		
							<ol>';
							foreach($context['file-last'] as $file)
							{
								$content .= '									
								<li><a href="'. $scripturl .'?action=downloads;sa=view;download='. $file['id_files-last'] .'">'. $file['title-last'] .'</a></li>';
							}	
			$content .= '
							</ol>';
					}
			$content .= '				
						</div>
					</div>
				</td>	
			</tr>
		</table>';				
	}else{
		$content .= '			
		<table cellpadding="5" cellspacing="2" width="100%">
			<tr>
				<td valign="top" align="left" width="100%">
					'. $txt['down_error_no_files_section'] .'
				</td>	
			</tr>
		</table>';			
	}
	
	//Print block
	echo $content;
	
	//No header, footer
	obExit(false);
	unset($content);
}

function NewDownload()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $sourcedir, $user_info;
	global $boarddir, $boardurl;
	global $smcFunc;

	if(!isset($_POST['save']))
		checkSession('get');	

	//Enable Download Module?
	if (empty($ultimateportalSettings['download_enable']))
		fatal_lang_error('ultport_error_no_active',false);			

	//Can ADD NEW File in this module?
	if (empty($user_info['up-modules-permissions']['download_add']) && !$user_info['is_admin'])								
		fatal_lang_error('ultport_error_no_perms_groups',false);			
		
	//Save?
	if (isset($_POST['save']))
	{
		checkSession('post');
		$title = $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES);
		$description = $smcFunc['htmlspecialchars']($_POST['file_description'], ENT_QUOTES);						
		$small_description = $smcFunc['htmlspecialchars']($_POST['small_description'], ENT_QUOTES);
		$id_section = (int) $_POST['section'];

		//Enable approved file?
		if (!empty($ultimateportalSettings['download_enable_approved_file']))
		{
			// Check if downloads are auto approved
			$approved = ($user_info['is_admin'] || !empty($user_info['up-modules-permissions']['download_moderate'])) ? 1 : 0;
		}else{
			$approved = 1;
		}	

		//is empty?
		if (empty($title))
			fatal_error($txt['down_error_no_title'],false);

		if (empty($description))
			fatal_error($txt['down_error_no_description'],false);
		
		if (empty($small_description))
			fatal_error($txt['down_error_no_small_description'],false);
		
				
		//new file...
		if (!empty($_FILES['attachment']))
		{
			$total_size = 0;
			
			$dir = $boarddir .'/up-attachments';

			// These are the only valid image types for Download Module.
			$validImageTypes = 'gif,jpeg,jpg';
			
			//Total size and Extension file?						
			foreach ($_FILES['attachment']['tmp_name'] as $n => $dummy)
			{
				if ($_FILES['attachment']['name'][$n] == '')
					continue;

				// Check the extension upload file...
				if (!in_array(strtolower(substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1)), explode(',', strtolower($ultimateportalSettings['download_extension_file']))) && !in_array(strtolower(substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1)), explode(',', strtolower($validImageTypes))))
					fatal_error($_FILES['attachment']['name'][$n] . '.<br />' . $txt['down_error_canot_upload_file'] . ' ' . $ultimateportalSettings['download_extension_file'] . ', '. $validImageTypes, false);											

				// Check the total upload size for this post...
				$total_size += $_FILES['attachment']['size'][$n];
			}
			
			//Exceeds max size upload?
			if (!empty($ultimateportalSettings['download_file_max_size']) && $total_size > $ultimateportalSettings['download_file_max_size'] * 1024)
			{
				foreach ($_FILES['attachment']['tmp_name'] as $n => $dummy)
				{
					// Delete the temp file
					@unlink($_FILES['attachment']['tmp_name'][$n]);
				}
			
				fatal_lang_error('file_too_big', false, array($ultimateportalSettings['download_file_max_size']));
			}	

			$id_files = 0;
			//INSERT
			$smcFunc['db_query']('',"INSERT INTO {$db_prefix}up_download_files
					(title, description, id_member, membername, small_description, id_section, date_created, total_downloads, approved)
					VALUES('$title', '$description',". $user_info['id'] .", '". $user_info['username'] ."', '$small_description', $id_section, ". time() .", 0, $approved)");
	
			$id_files = $smcFunc['db_insert_id']("{$db_prefix}up_download_files");
			
			//Updated total section and is approved?
			if(!empty($approved))
				UpdatedSectionTotalFiles($id_section);

			foreach ($_FILES['attachment']['tmp_name'] as $n => $dummy)
			{
				if (!empty($_FILES['attachment']['name'][$n]) && !in_array(strtolower(substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1)), explode(',', strtolower($validImageTypes))))
				{
					$filename = $id_files . '_' . sha1(md5($_FILES['attachment']['name'][$n] . time()) . mt_rand());
					$fileext = substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1);
					move_uploaded_file($_FILES['attachment']['tmp_name'][$n], $dir . '/'. $filename);
					@chmod($dir . '/'. $filename, 0644);	
					//Insert attachment
					$smcFunc['db_query']('',"
						INSERT INTO {$db_prefix}up_download_attachments
							(id_files, attachmentType, filename, file_hash, size, fileext)
						VALUES (" . (int) $id_files . ", 1, SUBSTRING('" . $_FILES['attachment']['name'][$n] . "', 1, 255), '". $filename ."', " . (int) $_FILES['attachment']['size'][$n] . ", '". $fileext ."')");
					//End insert	
				}
				//Attached it's image?
				if (in_array(strtolower(substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1)), explode(',', strtolower($validImageTypes))))				
				{
					//FILENAME full image
					$filename = $id_files . '_' . $_FILES['attachment']['name'][$n];
					//Okay, move it, full image
					move_uploaded_file($_FILES['attachment']['tmp_name'][$n], $boarddir . '/up-attachments/downloads/photos/'. $filename);					
					$imageFileURL = $boardurl . '/up-attachments/downloads/photos/'. $filename;
					//Size?
					$size = getimagesize($imageFileURL);
					list ($width, $height) = $size;					
					//file extension?
					$fileext = substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1);
					//MIME extension? only gif, jpg, jpeg
					//Is gif?
					if ($fileext == 'gif')
					{
						$mime_type = 'image/gif';
						$imagefrom = 'gif';
					}
					//or.... jpg, jpeg?
					if ($fileext == 'jpg' || $fileext == 'jpeg')
					{
						$mime_type = 'image/jpeg';					
						$imagefrom = 'jpeg';
					}
					//Insert Image full image
					$smcFunc['db_query']('',"
						INSERT INTO {db_prefix}up_download_attachments
							(id_files, attachmentType, filename, size, width, height, mime_type, fileext)
						VALUES (" . (int) $id_files . ", 2 , SUBSTRING('" . $filename . "', 1, 255), " . (int) $_FILES['attachment']['size'][$n] . ", ". (int) $width .", ". (int) $height .", '". $mime_type ."', '". $fileext ."')");
					//End insert full image
					
					$id_attach_image = $smcFunc['db_insert_id']("{db_prefix}up_download_attachments");
					
					//Okay, now create a Thumbnail
					$imagecreatefrom = 'imagecreatefrom' . $imagefrom;
					$src_img = @$imagecreatefrom($imageFileURL);
					//Filename THUMB
					$filename = $id_files . '_thumb_' . $_FILES['attachment']['name'][$n];					
					//Reduce image?
					$max_width = 13; //percent
					$max_height = 13; //percent
					UPResizeImage($src_img, $boarddir . '/up-attachments/downloads/thumbnails/'. $filename, $imagefrom, imagesx($src_img), imagesy($src_img), $max_width, $max_height, true);
					//Insert Image thumbnail 
					$smcFunc['db_query']('',"
						INSERT INTO {db_prefix}up_download_attachments
							(id_files, attachmentType, filename, size, width, height, mime_type, fileext)
						VALUES (" . (int) $id_files . ", 3 ,SUBSTRING('" . $filename . "', 1, 255), " . (int) $_FILES['attachment']['size'][$n] . ", ". (int) ($width * $max_width / 100) .", ". (int) ($height * $max_height / 100) .", '". $mime_type ."', '". $fileext ."')");
					//End insert thumbnail
					//okay, update, adding id_thumb to real image
					$id_thumbnail = $smcFunc['db_insert_id']("{db_prefix}up_download_attachments");
					$smcFunc['db_query']('',"
						UPDATE {db_prefix}up_download_attachments
							SET ID_THUMB = ". (int) $id_thumbnail ."
						WHERE ID_ATTACH = ". (int) $id_attach_image ."");
				}
			}
		}

		//Create a new POST in board selected?
		$id_topic = 0;
		//Load Specific Section, create a topic?
		SpecificSection($id_section);
		require_once($sourcedir . '/Subs-Post.php');
		if (!empty($context['id_board']) && !empty($approved))
		{
			$file_link = '[url=' . $scripturl . '?action=downloads;sa=view;download=' . $id_files . ']'. $txt['down_file_post_link'] .'[/url]';
			// Create the post
			$msgOptions = array(
				'id' => 0,
				'subject' => $title,
				'body' => "$file_link\n\n\n" . $description,
				'icon' => 'xx',
				'smileys_enabled' => 1,
				'attachments' => array(),
			);
			$topicOptions = array(
				'id' => 0,
				'board' => $context['id_board'],
				'poll' => null,
				'lock_mode' => null,
				'sticky_mode' => null,
				'mark_as_read' => true,
			);
			$posterOptions = array(
				'id' => $user_info['id'],
				'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']),
			);
	
			preparsecode($msgOptions['body']);
			createPost($msgOptions, $topicOptions, $posterOptions);
	
			$id_topic = $topicOptions['id'];

			//Updated id_topic from up_download_files table
			$smcFunc['db_query']('',"
				UPDATE {$db_prefix}up_download_files
					SET id_topic = $id_topic
				WHERE id_files = $id_files");			
		}
		
		//redirect 
		redirectexit('action=downloads;sa=view;download='.$id_files);				
	}

	//News Link-tree
	$context['down-linktree'] = '<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=downloads">'. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> <br /><img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $txt['down_module_new_file_title'];	

	//Load the Download section
	LoadDownloadSection('view', 0, 'portal');

	if (empty($context['view']))
		fatal_lang_error('down_error_no_section',false);		

	//Other Html Headers, no EDITOR HTML
	extra_context_html_headers();
	
	// Used for the custom editor
	// Now create the editor.
	$editorOptions = array(
		'id' => 'file_description',
		'value' => '',
		'form' => 'newform',
	);
	$context['smileyBox_container'] = 'smileyBox_'.$editorOptions['id'];
	$context['bbcBox_container'] = 'bbcBox_'.$editorOptions['id'];
	up_create_control_richedit($editorOptions);	
	$context['post_box_name'] = $editorOptions['id'];		
	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=downloads',
		'name' => $txt['down_module_title'] . ' - ' . $txt['down_module_title2']
	);

	// Call the sub template.
	$context['sub_template'] = 'down_new';
	$context['page_title'] = $txt['down_module_new_file_title'];

}

//Search download?
function SearchDownload()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	global $smcFunc, $user_info;
	
	//Enable Download Module?
	if (empty($ultimateportalSettings['download_enable']))
		fatal_lang_error('ultport_error_no_active',false);			
		
	//News Link-tree
	$context['down-linktree'] = '<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=downloads">'. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $txt['down_module_search_page_title'];	

	$filter = '';
	$filter2 = '';
	if (!empty($_REQUEST['type']) && empty($_REQUEST['basic_search']))
	{
		$filter = 'section';
		$filter2 = $smcFunc['htmlspecialchars']($_REQUEST['type'],ENT_QUOTES);
		
		//Load the Download section
		LoadDownloadSection('view', $_REQUEST['type'], 'portal');
		
		//for the link tree
		SpecificSection($_REQUEST['type']);
		
		if (empty($context['canview']) && !$user_info['is_admin'])
			fatal_lang_error('ultport_error_no_perms_groups',false);		

		//News Link-tree
		$context['down-linktree'] = '<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=downloads">'. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> &raquo; <a href="'. $scripturl .'?action=downloads;sa=search;type='. $context['id'] .'">'. $context['title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $txt['down_module_search_page_title'];				
	}
	if(!empty($_REQUEST['basic_search']) && empty($_REQUEST['type'])){
		$filter = 'basic_seach';
		$filter2 = $smcFunc['htmlspecialchars']($_REQUEST['basic_search'],ENT_QUOTES);
	}
	if(empty($_REQUEST['type']) && empty($_REQUEST['basic_search']) && !empty($_POST['basic_search'])){
		$filter = 'search';
		$filter2 = $smcFunc['htmlspecialchars']($_POST['basic_search'],ENT_QUOTES);
	}

	if(empty($_REQUEST['type']) && empty($_REQUEST['basic_search']) && empty($_POST['basic_search']))
			fatal_lang_error('down_error_no_action',false);		
					
	//View Result
	DownloadSearchResult($filter, $filter2);

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=downloads',
		'name' => $txt['down_module_title'] . ' - ' . $txt['down_module_title2']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'down_search';
	$context['page_title'] = $txt['down_module_search_page_title'];
}

function UnapprovedFile()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	global $smcFunc, $user_info;
	
	//Enable Download Module?
	if (empty($ultimateportalSettings['download_enable']))
		fatal_lang_error('ultport_error_no_active',false);			
		
	//News Link-tree
	$context['down-linktree'] = '<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=downloads">'. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $txt['down_module_unapproved_title'];	

	if(empty($user_info['up-modules-permissions']['download_moderate']) && !$user_info['is_admin'])
			fatal_lang_error('ultport_error_no_perms_groups',false);		
					
	//View Result
	ViewUnapprovedFiles();

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=downloads',
		'name' => $txt['down_module_title'] . ' - ' . $txt['down_module_title2']
	);
	
	// Call the sub template.
	$context['sub_template'] = 'down_unapproved_files';
	$context['page_title'] = $txt['down_module_unapproved_title'];
}

//View Files?
function ViewFiles()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;
	global $smcFunc;
	
	//Enable Download Module?
	if (empty($ultimateportalSettings['download_enable']))
		fatal_lang_error('ultport_error_no_active',false);			

	$id_files = !empty($_REQUEST['download']) ? (int) $_REQUEST['download'] : 0;
	
	//not Request download id?
	if (empty($id_files))
		fatal_lang_error('down_error_no_action',false);			
	
	//Load File Information
	LoadSpecificFileInformation($id_files);
	//Load Specific Image?
	LoadSpecificImageAttachemnt($id_files);
	
	if (empty($context['view-files']))
		fatal_lang_error('down_error_no_found',false);			

	//Not approved?
	if (empty($context['can_view']))
		fatal_lang_error('down_error_no_found',false);			

	//No Perms?
	if (empty($context['perm_view']))
		fatal_lang_error('ultport_error_no_perms_groups',false);		

 	//Load File Attachment
	LoadSpecificFileAttachemnt($id_files);
	//News Link-tree
	$context['down-linktree'] = '<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=downloads">'. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> &raquo; <a href="'. $scripturl .'?action=downloads;sa=search;type='. $context['id'] .'">'. $context['title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;<a href="'. $scripturl .'?action=downloads;sa=view;download='. $context['id_files'] .'">'. $context['filetitle'] .'</a>';	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=downloads',
		'name' => $txt['down_module_title'] . ' - ' . $txt['down_module_title2']
	);

	// Call the sub template.
	$context['sub_template'] = 'view_file';
	$context['page_title'] = $context['filetitle'];
}

function EditFile()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $sourcedir, $user_info;
	global $boarddir, $boardurl;
	global $smcFunc;
	
	//Enable Download Module?
	if (empty($ultimateportalSettings['download_enable']))
		fatal_lang_error('ultport_error_no_active',false);			

	//Id?
	if (!empty($_REQUEST['id']))
	{
		$id_files = $smcFunc['db_escape_string']($_REQUEST['id']);	
		$id_files = (int) $id_files;
	}else{
		$id_files = (int) $_POST['ID_FILE'];
	}	

	if(!isset($_POST['save']))
		checkSession('get');	
	
	if (empty($id_files))
		fatal_lang_error('down_error_no_action',false);			
	
	//Load the Specific File Information
	LoadSpecificFileInformation($id_files);
	
	//Load Specific Attachments 
	LoadSpecificFileAttachemnt($id_files);
	//Load Specific Image?
	LoadSpecificImageAttachemnt($id_files);
	//Load the Download section
	LoadDownloadSection('view', 0, 'portal');

	//Check if user can moderate
	if (empty($user_info['up-modules-permissions']['download_moderate']) && !$user_info['is_admin'] && $context['id_member'] != $user_info['id'])								
		fatal_lang_error('ultport_error_no_perms_groups',false);			
	
	//Save?
	if (isset($_POST['save']))
	{
		checkSession('post');
		require_once($sourcedir . '/Subs-Editor.php');
			
		$title = $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES);
		$description = $smcFunc['htmlspecialchars']($_POST['file_description'], ENT_QUOTES);						
		$small_description = $smcFunc['htmlspecialchars']($_POST['small_description'], ENT_QUOTES);
		$id_section = (int) $_POST['section'];
		$date_updated = time();

		//is empty?
		if (empty($title))
			fatal_error($txt['down_error_no_title'],false);

		if (empty($description))
			fatal_error($txt['down_error_no_description'],false);
		
		if (empty($small_description))
			fatal_error($txt['down_error_no_small_description'],false);
		
		//Delete Attach?
		// Check if they are trying to delete any current attachments....
		if (!empty($_POST['attach_del']))
		{
			foreach ($_POST['attach_del'] as $i => $dummy)
			{
				$dummy = (int) $dummy;

				$myquery = $smcFunc['db_query']('',"SELECT * 
								FROM {$db_prefix}up_download_attachments
								WHERE ID_ATTACH = $dummy");
				while ($row = $smcFunc['db_fetch_assoc']($myquery))
				{
					if($row['attachmentType'] == 1)
						$file_hash_deleted = $row['file_hash'];
					if($row['attachmentType'] == 2)	
						$IMAGE = $row['filename'];
					if($row['attachmentType'] == 3)	
						$THUMB = $row['filename'];
				}				
				$smcFunc['db_free_result']($myquery);
				//Delete of table
				DeleteAttach($dummy);
				//Delete of up-attachments folder - no image
				if(!empty($file_hash_deleted))
					@unlink($boarddir .'/up-attachments/'. $file_hash_deleted);
				//Delete FULL image
				if(!empty($IMAGE))
					@unlink($settings['default_theme_dir'] .'/up-slideshow/photos/'. $IMAGE);					
				//Delete THUMB image
				if(!empty($THUMB))
					@unlink($settings['default_theme_dir'] .'/up-slideshow/thumbnails/'. $THUMB);										
			}
		}		
		//Now Edit file...
		if (!empty($_FILES['attachment']))
		{
			$total_size = 0;
			
			$dir = $boarddir .'/up-attachments';

			// These are the only valid image types for Download Module.
			$validImageTypes = 'gif,jpeg,jpg';

			//Total size and Extension file?			
			foreach ($_FILES['attachment']['tmp_name'] as $n => $dummy)
			{
				if ($_FILES['attachment']['name'][$n] == '')
					continue;
				
				// Check the extension upload file...
				if (!in_array(strtolower(substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1)), explode(',', strtolower($ultimateportalSettings['download_extension_file'])))  && !in_array(strtolower(substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1)), explode(',', strtolower($validImageTypes))))
				{
					@unlink($_FILES['attachment']['tmp_name'][$n]);
					fatal_error($_FILES['attachment']['name'][$n] . '.<br />' . $txt['down_error_canot_upload_file'] . ' ' . $ultimateportalSettings['download_extension_file'] . ', ' . $validImageTypes, false);
				}
				// Check the total upload size for this file...
				$total_size += $_FILES['attachment']['size'][$n];
			}
				
			//Exceeds max size upload?
			if (!empty($ultimateportalSettings['download_file_max_size']) && $total_size > $ultimateportalSettings['download_file_max_size'] * 1024)
			{
				foreach ($_FILES['attachment']['tmp_name'] as $n => $dummy)
				{
					// Delete the temp file
					@unlink($_FILES['attachment']['tmp_name'][$n]);
				}
			
				fatal_lang_error('down_error_max_size', false, array($ultimateportalSettings['download_file_max_size']));
			}	

			//UPDATED
			$smcFunc['db_query']('',"UPDATE {$db_prefix}up_download_files
					SET	title = '$title', 
						description = '$description', 
						small_description = '$small_description', 
						id_section = $id_section, 
						date_updated = $date_updated
					WHERE id_files = $id_files");
			
			//Moved a other Section?
			if ($context['id_section'] != $id_section)
			{
				//Updated the total files for the new and old Section
				UpdatedSectionTotalFiles($id_section);//New Section
				SubstractSectionTotalFiles($context['id_section']);//OLD Section
			}	
			
			foreach ($_FILES['attachment']['tmp_name'] as $n => $dummy)
			{
				if (!empty($_FILES['attachment']['name'][$n]) && !in_array(strtolower(substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1)), explode(',', strtolower($validImageTypes))))
				{
					$filename = $id_files . '_' . sha1(md5($_FILES['attachment']['name'][$n] . time()) . mt_rand());
					
					move_uploaded_file($_FILES['attachment']['tmp_name'][$n], $dir . '/'. $filename);
					@chmod($dir . '/'. $filename, 0644);	
					//Insert attachment
					$smcFunc['db_query']('',"
						INSERT INTO {$db_prefix}up_download_attachments
							(id_files, attachmentType, filename, file_hash, size)
						VALUES (" . (int) $id_files . ", 1 , SUBSTRING('" . $_FILES['attachment']['name'][$n] . "', 1, 255), '". $filename ."', " . (int) $_FILES['attachment']['size'][$n] . ')');
					//End insert	
				}
				//Attached it's image?
				if (in_array(strtolower(substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1)), explode(',', strtolower($validImageTypes))))				
				{
					//FILENAME full image
					$filename = $id_files . '_' . $_FILES['attachment']['name'][$n];
					//Okay, move it, full image
					move_uploaded_file($_FILES['attachment']['tmp_name'][$n], $boarddir . '/up-attachments/downloads/photos/'. $filename);					
					$imageFileURL = $boardurl . '/up-attachments/downloads/photos/'. $filename;
					//Size?
					$size = getimagesize($imageFileURL);
					list ($width, $height) = $size;					
					//file extension?
					$fileext = substr(strrchr($_FILES['attachment']['name'][$n], '.'), 1);
					//MIME extension? only gif, jpg, jpeg
					//Is gif?
					if ($fileext == 'gif')
					{
						$mime_type = 'image/gif';
						$imagefrom = 'gif';
					}
					//or.... jpg, jpeg?
					if ($fileext == 'jpg' || $fileext == 'jpeg')
					{
						$mime_type = 'image/jpeg';					
						$imagefrom = 'jpeg';
					}
					//Insert Image full image
					$smcFunc['db_query']('',"
						INSERT INTO {db_prefix}up_download_attachments
							(id_files, attachmentType, filename, size, width, height, mime_type, fileext)
						VALUES (" . (int) $id_files . ", 2 , SUBSTRING('" . $filename . "', 1, 255), " . (int) $_FILES['attachment']['size'][$n] . ", ". (int) $width .", ". (int) $height .", '". $mime_type ."', '". $fileext ."')");
					//End insert full image
					
					$id_attach_image = $smcFunc['db_insert_id']("{db_prefix}up_download_attachments");
					
					//Okay, now create a Thumbnail
					$imagecreatefrom = 'imagecreatefrom' . $imagefrom;
					$src_img = @$imagecreatefrom($imageFileURL);
					//Filename THUMB
					$filename = $id_files . '_thumb_' . $_FILES['attachment']['name'][$n];					
					//Reduce image
					$max_width = 13; //percent
					$max_height = 13; //percent
					UPResizeImage($src_img, $boarddir . '/up-attachments/downloads/thumbnails/'. $filename, $imagefrom, imagesx($src_img), imagesy($src_img), $max_width, $max_height, true);
					//Insert Image thumbnail 
					$smcFunc['db_query']('',"
						INSERT INTO {db_prefix}up_download_attachments
							(id_files, attachmentType, filename, size, width, height, mime_type, fileext)
						VALUES (" . (int) $id_files . ", 3 ,SUBSTRING('" . $filename . "', 1, 255), " . (int) $_FILES['attachment']['size'][$n] . ", ". (int) ($width * $max_width / 100) .", ". (int) ($height * $max_height / 100) .", '". $mime_type ."', '". $fileext ."')");
					//End insert thumbnail
					//okay, update, adding id_thumb to real image
					$id_thumbnail = $smcFunc['db_insert_id']("{db_prefix}up_download_attachments");
					$smcFunc['db_query']('',"
						UPDATE {db_prefix}up_download_attachments
							SET ID_THUMB = ". (int) $id_thumbnail ."
						WHERE ID_ATTACH = ". (int) $id_attach_image ."");
				}				
			}
		}
		
		//redirect 
		redirectexit('action=downloads;sa=view;download='.$id_files);				
	}

	//News Link-tree
	$context['down-linktree'] = '<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=downloads">'. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> &raquo; <a href="'. $scripturl .'?action=downloads;sa=search;type='. $context['id'] .'">'. $context['title'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $txt['down_module_edit_file_title'] . '&nbsp;(<em><a href="'. $scripturl .'?action=downloads;sa=view;download='. $context['id_files'] .'">'. $context['filetitle'] .'</a></em>)';	

	if (empty($context['view']))
		fatal_lang_error('down_error_no_section',false);		

	//Other Html Headers, no EDITOR HTML
	extra_context_html_headers();
	
	// Used for the custom editor
	// Now create the editor.
	$editorOptions = array(
		'id' => 'file_description',
		'value' => $context['file_description_original'],
		'form' => 'newform',
	);
	$context['smileyBox_container'] = 'smileyBox_'.$editorOptions['id'];
	$context['bbcBox_container'] = 'bbcBox_'.$editorOptions['id'];
	up_create_control_richedit($editorOptions);	
	$context['post_box_name'] = $editorOptions['id'];		

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=downloads',
		'name' => $txt['down_module_title'] . ' - ' . $txt['down_module_title2']
	);

	// Call the sub template.
	$context['sub_template'] = 'down_edit';
	$context['page_title'] = $context['filetitle'];

}

function download_file()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;
	global $boarddir;
	global $smcFunc, $sourcedir;


	$ID_ATTACH = (int) $_REQUEST['id'];

	$myquery = $smcFunc['db_query']('',"SELECT id_files, filename, file_hash 
					FROM {$db_prefix}up_download_attachments
					WHERE ID_ATTACH = $ID_ATTACH");	
	while ($row2 = $smcFunc['db_fetch_assoc']($myquery))
	{	
	
		if(empty($ID_ATTACH))
			fatal_lang_error('down_error_no_action',false);
		
		// Get the download information
		$id_files = $row2['id_files'];
		
		$dbresult = $smcFunc['db_query']('',"
		SELECT	id_files, title, id_member, membername, id_section, approved 
		FROM {$db_prefix}up_download_files
		WHERE id_files = $id_files");
		while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		{	
			// Check if File is approved
			$candownload = 0;
			
			if ($user_info['is_admin']){
				$candownload = 1;
			}
			
			if(empty($row['approved']) && !$user_info['is_admin'])
			{
				$candownload = (!$user_info['is_guest'] && $user_info['id'] == $row['id_member']) ? 1 : 0;
			}else{
				$candownload = 1;
			}	
			
			if ($candownload == 0){
				fatal_lang_error('down_error_no_action',false);
			}
			
			//Load Section
			SpecificSection($row['id_section']);
			
			$perms = array();
			if ($context['id_groups']) {
				$perms =  $context['id_groups'];
			}
			
			if(!$perms) {
				$perms = array();
			}
			$perms = explode(',', $perms);		
			$viewsection = false;		
			//Can user download in this section?
			foreach($user_info['groups'] as $group_id) 
				if(in_array($group_id, $perms)) {
					$viewsection = true;
				}
			
			if ($viewsection === false && !$user_info['is_admin'] && $user_info['id'] != $row['id_member'])
			{
				fatal_lang_error('ultport_error_no_perms_groups',false);
			}
			
			//NOW CAN USER DOWNLOAD
			//UPDATED DOWNLOAD COUNT in Fildes AND ATTACH TABLES 
			UpdatedTotalDownloadFile($row2['id_files']);
			UpdatedFilesDownloads($ID_ATTACH);
			
		}
		$smcFunc['db_free_result']($dbresult);	

		//REAL NAME AND FILE_HASH	
		$real_filename = trim($row2['filename']);
		$filename = $boarddir . '/up-attachments/'. trim($row2['file_hash']);
		
	}
	$smcFunc['db_free_result']($myquery);
			
	// This is done to clear any output that was made before now. (would use ob_clean(), but that's PHP 4.2.0+...)
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']) && @version_compare(PHP_VERSION, '4.2.0') >= 0 && @filesize($filename) <= 4194304)
		@ob_start('ob_gzhandler');
	else
	{
		ob_start();
		header('Content-Encoding: none');
	}

	// No point in a nicer message, because this is supposed to be an attachment anyway...
	if (!file_exists($filename))
	{
		loadLanguage('Errors');

		header('HTTP/1.0 404 ' . $txt['attachment_not_found']);
		header('Content-Type: text/plain; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

		// We need to die like this *before* we send any anti-caching headers as below.
		die('404 - ' . $txt['attachment_not_found']);
	}



	// If it hasn't been modified since the last time this attachement was retrieved, there's no need to display it again.
	if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
	{
		list($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
		if (strtotime($modified_since) >= filemtime($filename))
		{
			ob_end_clean();

			// Answer the question - no, it hasn't been modified ;).
			header('HTTP/1.1 304 Not Modified');
			exit;
		}
	}

	// Check whether the ETag was sent back, and cache based on that...
	$file_md5 = '"' . md5_file($filename) . '"';
	if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $file_md5) !== false)
	{
		ob_end_clean();

		header('HTTP/1.1 304 Not Modified');
		exit;
	}

	// Send the attachment headers.
	header('Pragma: ');

	if (!$context['browser']['is_gecko'])
		header('Content-Transfer-Encoding: binary');

	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
	header('Accept-Ranges: bytes');
	header('Set-Cookie:');
	header('Connection: close');
	header('ETag: ' . $file_md5);

	if (filesize($filename) != 0)
	{
		$size = @getimagesize($filename);
		if (!empty($size))
		{
			// What headers are valid?
			$validTypes = array(
				1 => 'gif',
				2 => 'jpeg',
				3 => 'png',
				5 => 'psd',
				6 => 'bmp',
				7 => 'tiff',
				8 => 'tiff',
				9 => 'jpeg',
				14 => 'iff',
			);

			// Do we have a mime type we can simpy use?
			if (!empty($size['mime']))
				header('Content-Type: ' . $size['mime']);
			elseif (isset($validTypes[$size[2]]))
				header('Content-Type: image/' . $validTypes[$size[2]]);
			// Otherwise - let's think safety first... it might not be an image...
			elseif (isset($_REQUEST['image']))
				unset($_REQUEST['image']);
		}
		// Once again - safe!
		elseif (isset($_REQUEST['image']))
			unset($_REQUEST['image']);
	}

	if (!isset($_REQUEST['image']))
	{
		header('Content-Disposition: attachment; filename="' . $real_filename . '"');
		header('Content-Type: application/octet-stream');
	}

	if (empty($modSettings['enableCompressedOutput']) || filesize($filename) > 4194304)
		header('Content-Length: ' . filesize($filename));

	// Try to buy some time...
	@set_time_limit(0);

	// For text files.....
	if (!isset($_REQUEST['image']) && in_array(substr($real_filename, -4), array('.txt', '.css', '.htm', '.php', '.xml')))
	{
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false)
				$callback = function($buffer){return preg_replace('~[\r]?\n~', "\r\n", $buffer);};
			elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false)
				$callback = function($buffer){return preg_replace('~[\r]?\n~', "\r", $buffer);};
			else
				$callback = function($buffer){return preg_replace('~[\r]?\n~', "\n", $buffer);};
	}

	// Since we don't do output compression for files this large...
	if (filesize($filename) > 4194304)
	{
		// Forcibly end any output buffering going on.
		if (function_exists('ob_get_level'))
		{
			while (@ob_get_level() > 0)
				@ob_end_clean();
		}
		else
		{
			@ob_end_clean();
			@ob_end_clean();
			@ob_end_clean();
		}

		$fp = fopen($filename, 'rb');
		while (!feof($fp))
		{
			if (isset($callback))
				echo $callback(fread($fp, 8192));
			else
				echo fread($fp, 8192);
			flush();
		}
		fclose($fp);
	}
	// On some of the less-bright hosts, readfile() is disabled.  It's just a faster, more byte safe, version of what's in the if.
	elseif (isset($callback) || @readfile($filename) == null)
		echo isset($callback) ? $callback(file_get_contents($filename)) : file_get_contents($filename);

	obExit(false);

	exit;

}

//Delete Specific File?
function DeleteFile()
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc, $boarddir;

	//Security
	checkSession('get');
	//Check if user can Delete
	if (empty($user_info['up-modules-permissions']['download_moderate']) && !$user_info['is_admin'])								
		fatal_lang_error('ultport_error_no_perms_groups',false);
		
	$id_file = (int) $_REQUEST['id'];

	//Empty id file?
	if (empty($id_file))
		fatal_lang_error('down_error_no_action',false);
		
	//Load Specific Files Information?
	LoadSpecificFileInformation($id_file);

	//Ok, delete 
	$smcFunc['db_query']('',"DELETE FROM {$db_prefix}up_download_files
			WHERE id_files = $id_file");
	//End	
		
	//Updated total files for section, but this file is approved?
	if (!empty($context['approved']))
		SubstractSectionTotalFiles($context['id_section']);
	
	//Delete Attach?
	// Check if they are trying to delete any current attachments....
	$del_attach = $smcFunc['db_query']('',"SELECT * 
					FROM {$db_prefix}up_download_attachments
					WHERE id_files = $id_file");
	while ($row = $smcFunc['db_fetch_assoc']($del_attach))
	{
		//Delete of table
		DeleteAttach($row['ID_ATTACH']);		
		if($row['attachmentType'] == 1)
			@unlink($boarddir .'/up-attachments/'. $row['file_hash']);
		if($row['attachmentType'] == 2)	
			@unlink($boarddir .'/up-attachments/downloads/photos/'. $row['filename']);					
		if($row['attachmentType'] == 3)	
			@unlink($boarddir .'/up-attachments/downloads/thumbnails/'. $row['filename']);
	}				
	$smcFunc['db_free_result']($del_attach);	
	//Ok now Redirect 
	redirectexit('action=downloads;sa=search;type='. $context['id_section']);				
}

//Approved Specific File?
function ApprovedFile()
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc, $sourcedir, $memberContext;

	//Security
	if (!$user_info['is_admin'] && empty($user_info['up-modules-permissions']['download_moderate']))
	{
		fatal_lang_error('ultport_error_no_perms_groups',false);
	}
	
	$id_file = (int) $_REQUEST['id'];

	//Empty $id_file?
	if (empty($id_file))
		fatal_lang_error('down_error_no_action',false);			

	//Load File Information
	LoadSpecificFileInformation($id_file);
	
	//Create a new POST in board selected?
	$id_topic = 0;
	require_once($sourcedir . '/Subs-Post.php');
	if (!empty($context['id_board']))
	{
		$file_link = '[url=' . $scripturl . '?action=downloads;sa=view;download=' . $id_file . ']'. $txt['down_file_post_link'] .'[/url]';
		// Create the post
		$msgOptions = array(
			'id' => 0,
			'subject' => $context['filetitle'],
			'body' => "$file_link\n\n\n" . $context['file_description_original'],
			'icon' => 'xx',
			'smileys_enabled' => 1,
			'attachments' => array(),
		);
		$topicOptions = array(
			'id' => 0,
			'board' => $context['id_board'],
			'poll' => null,
			'lock_mode' => null,
			'sticky_mode' => null,
			'mark_as_read' => true,
		);
		$posterOptions = array(
			'id' => $context['id_member'],
			'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']),
		);

		preparsecode($msgOptions['body']);
		createPost($msgOptions, $topicOptions, $posterOptions);

		$id_topic = $topicOptions['id'];
	}
	
	//Send PM?
	if (!empty($ultimateportalSettings['down_enable_send_pm_approved']))
	{
		$subject = str_replace('{FILENAME}',$context['filetitle'],$ultimateportalSettings['download_pm_subject']);
        $pm_body = str_replace('{FILENAME}','[url=' . $scripturl . '?action=downloads;sa=view;download=' . $id_file . ']'. $context['filetitle'] .'[/url]',$ultimateportalSettings['download_pm_body']);
		$pmto = array(
			'to' => array($context['id_member']),
			'bcc' => array(),
		);
		
		if (!empty($ultimateportalSettings['download_pm_id_member']))
		{
			loadMemberData($ultimateportalSettings['download_pm_id_member']);
			loadMemberContext($ultimateportalSettings['download_pm_id_member']);
			
			$from = array( 
				'id' => $ultimateportalSettings['download_pm_id_member'],
				'name' => $memberContext[$ultimateportalSettings['download_pm_id_member']]['name'],
				'username' => $memberContext[$ultimateportalSettings['download_pm_id_member']]['username'],
			);		
		}else{
			$from = null;
		}		
		
		sendpm($pmto,$subject,$pm_body,false,$from);
	}
	//Ok, approved
	$smcFunc['db_query']('',"
		UPDATE {$db_prefix}up_download_files
			SET approved = 1,
				id_topic = $id_topic
		WHERE id_files = $id_file");

	//Updated total files for this Section
	UpdatedSectionTotalFiles($context['id_section']);
	
	//redirect 
	redirectexit('action=downloads;sa=view;download='.$id_file);				

}

//Download Module Stats
function ShowStats()
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc;

	//Linktree
	$context['down-linktree'] = '<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=downloads">'. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $txt['down_module_stats_title'];	
	
	//Top Upload User
	TopUserUpload();
	
	//Top Sections;
	LoadDownloadSection('view', 0, 'down_stats');

	//Downloads more Popular
	LoadFileInformationRows('WHERE f.id_section = s.id AND f.approved = 1 ORDER BY f.total_downloads DESC LIMIT 5', 'popular');

	//Last Files Added
	LoadFileInformationRows('WHERE f.id_section = s.id AND f.approved = 1 ORDER BY f.id_files DESC LIMIT 5', 'last');
	
	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=downloads;sa=stats',
		'name' => $txt['down_module_stats_title']
	);

	// Call the sub template.
	$context['sub_template'] = 'down_stats';
	$context['page_title'] = $txt['down_module_stats_title'];

}

//User Uploader Profile
function UserProfile()
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $smcFunc;

	//Enable Download Module?
	if (empty($ultimateportalSettings['download_enable']))
		fatal_lang_error('ultport_error_no_active',false);			

	//Empty "u"?
	if (empty($_REQUEST['u']))
		fatal_lang_error('down_error_no_action',false);			
	
	$id_member = $smcFunc['db_escape_string']($_REQUEST['u']);
	$id_member = (int) $id_member;
	
	//Load Profile	
	LoadUserProfile($id_member);	
	
	//News Link-tree
	$context['down-linktree'] = '<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/open_linktree.gif" />&nbsp;<a href="'. $scripturl .'?action=downloads">'. $txt['down_module_title'] . ' '. $txt['down_module_title2'] .'</a> <br /><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/linktree_side.gif" />&nbsp;'. $txt['down_profile_title'] . $context['membername'];	

	//Forum linktree
	$context['linktree'][1] = array(
		'url' => $scripturl . '?action=downloads',
		'name' => $txt['down_module_title'] . ' - ' . $txt['down_module_title2']
	);
		
	// Call the sub template.
	$context['sub_template'] = 'down_profile';
	$context['page_title'] = $txt['down_profile_title'] . '&nbsp;'. $context['membername'];

}

?>