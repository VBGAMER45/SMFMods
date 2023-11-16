<?php
/*
SMF Gallery Lite Edition
Version 7.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2008-2021 SMFHacks.com

############################################
License Information:
SMF Gallery is NOT free software.
This software may not be redistributed.

Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/

if (!defined('SMF'))
	die('Hacking attempt...');

ini_set('gd.jpeg_ignore_warning', 1);

function GalleryMain()
{
	global $modSettings, $boardurl, $boarddir;

	$currentVersion = '7.0';

	if (empty($modSettings['gallery_url']))
		$modSettings['gallery_url'] = $boardurl . '/gallery/';


    if (empty($modSettings['gallery_path']))
		$modSettings['gallery_path'] = $boarddir . '/gallery/';


	if (empty($modSettings['gallery_set_images_per_page']))
		$modSettings['gallery_set_images_per_page'] = 20;

	if (empty($modSettings['gallery_thumb_height']))
		$modSettings['gallery_thumb_height'] = 78;

	if (empty($modSettings['gallery_thumb_width']))
		$modSettings['gallery_thumb_width'] = 120;


	// Load the main template file
	loadtemplate('Gallery');

	// Load the language files
	if (loadlanguage('Gallery') == false)
		loadLanguage('Gallery','english');

    GalleryTopButtons();

	// Gallery Actions
	$subActions = array(
		'main' => 'main',
		'view' => 'ViewPicture',
		'admincat' => 'AdminCats',
		'adminset'=> 'AdminSettings',
		'adminset2'=> 'AdminSettings2',
		'delete' => 'DeletePicture',
		'delete2' => 'DeletePicture2',
		'edit' => 'EditPicture',
		'edit2' => 'EditPicture2',
		'report' => 'ReportPicture',
		'report2' => 'ReportPicture2',
		'deletereport' => 'DeleteReport',
		'reportlist' => 'ReportList',
		'comment' => 'AddComment',
		'comment2' => 'AddComment2',
		'delcomment' => 'DeleteComment',
		'rate' => 'RatePicture',
		'catup' => 'CatUp',
		'catdown' => 'CatDown',
		'addcat' => 'AddCategory',
		'addcat2' => 'AddCategory2',
		'editcat' => 'EditCategory',
		'editcat2' => 'EditCategory2',
		'deletecat' => 'DeleteCategory',
		'deletecat2' => 'DeleteCategory2',
		'viewc' => 'ViewC',
		'myimages' => 'MyImages',
		'approvelist' => 'ApproveList',
		'approve' => 'ApprovePicture',
		'unapprove' => 'UnApprovePicture',
		'add' => 'AddPicture',
		'add2' => 'AddPicture2',
		'search' => 'Search',
		'search2' => 'Search2',
		'regen' => 'ReGenerateThumbnails',
		'regen2' => 'ReGenerateThumbnails2',
		'next' => 'NextImage',
		'prev' => 'PreviousImage',
        'copyright' => 'Gallery_CopyrightRemoval',
        'convert' => 'ConvertGallery',
	);


	// Follow the sa or just go to main function
    if (isset($_GET['sa']))
	   $sa = $_GET['sa'];
    else
        $sa = '';


	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		mainview();

}

function mainview()
{
	global $context, $scripturl, $mbname, $txt, $db_prefix, $modSettings, $user_info, $ID_MEMBER;
	// View the main gallery

	// Is the user allowed to view the gallery?
	isAllowedTo('smfgallery_view');

	// Load the main gallery template
	$context['sub_template']  = 'mainview';

	$context['gallery_cat_name'] = ' ';


	if (isset($_REQUEST['cat']))
        $cat =  (int) $_REQUEST['cat'];
    else
        $cat = 0;


	if (!empty($cat))
	{

	   $context['gallery_catid'] = $cat;

		// Get category name
		$dbresult1 = db_query("
		SELECT
			ID_CAT, title, roworder, description, image
		FROM {$db_prefix}gallery_cat
		WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
		$row1 = mysql_fetch_assoc($dbresult1);
		$context['gallery_cat_name'] = $row1['title'];
		mysql_free_result($dbresult1);

		$context['start'] = (int) $_REQUEST['start'];
		// Image Listing
		$dbresult = db_query("
		SELECT p.ID_PICTURE, p.commenttotal, p.filesize, p.views, p.thumbfilename, p.filename, p.height, p.width,
		 p.title, p.ID_MEMBER, m.memberName, m.realName, p.date, p.description
		 FROM {$db_prefix}gallery_pic as p
		LEFT JOIN {$db_prefix}members AS m on ( p.ID_MEMBER = m.ID_MEMBER)
		WHERE p.ID_CAT = $cat AND p.approved = 1 ORDER BY ID_PICTURE DESC LIMIT $context[start]," . $modSettings['gallery_set_images_per_page'], __FILE__, __LINE__);
		$context['gallery_image_list'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['gallery_image_list'][] = $row;
		}
		mysql_free_result($dbresult);


		// Link Tree
		$context['linktree'][] = array(
					'url' => $scripturl . '?action=gallery',
					'name' => $txt['gallery_text_title']
				);
		$context['linktree'][] = array(
					'url' =>  $scripturl . '?action=gallery;cat=' . $cat,
					'name' => $context['gallery_cat_name']
				);


		$context['page_title'] = $mbname . ' - ' . $context['gallery_cat_name'];
		$context['sub_template']  = 'image_listing';

		if (!empty($modSettings['gallery_who_viewing']))
		{
			$context['can_moderate_forum'] = allowedTo('moderate_forum');

				//SMF 1.1
				//Taken from Display.php
				// Start out with no one at all viewing it.
				$context['view_members'] = array();
				$context['view_members_list'] = array();
				$context['view_num_hidden'] = 0;

				$whoID = (string) $cat;

				// Search for members who have this picture id set in their GET data.
				$request = db_query("
					SELECT
						lo.ID_MEMBER, lo.logTime, mem.realName, mem.memberName, mem.showOnline,
						mg.onlineColor, mg.ID_GROUP, mg.groupName
					FROM {$db_prefix}log_online AS lo
						LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = lo.ID_MEMBER)
						LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
					WHERE INSTR(lo.url, 's:7:\"gallery\";s:3:\"cat\";s:" . strlen($whoID ) .":\"$cat\";') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'", __FILE__, __LINE__);
				while ($row = mysql_fetch_assoc($request))
				{
					if (empty($row['ID_MEMBER']))
						continue;

					if (!empty($row['onlineColor']))
						$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '" style="color: ' . $row['onlineColor'] . ';">' . $row['realName'] . '</a>';
					else
						$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>';

					$is_buddy = in_array($row['ID_MEMBER'], $user_info['buddies']);
					if ($is_buddy)
						$link = '<b>' . $link . '</b>';

					// Add them both to the list and to the more detailed list.
					if (!empty($row['showOnline']) || allowedTo('moderate_forum'))
						$context['view_members_list'][$row['logTime'] . $row['memberName']] = empty($row['showOnline']) ? '<i>' . $link . '</i>' : $link;
					$context['view_members'][$row['logTime'] . $row['memberName']] = array(
						'id' => $row['ID_MEMBER'],
						'username' => $row['memberName'],
						'name' => $row['realName'],
						'group' => $row['ID_GROUP'],
						'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
						'link' => $link,
						'is_buddy' => $is_buddy,
						'hidden' => empty($row['showOnline']),
					);

					if (empty($row['showOnline']))
						$context['view_num_hidden']++;
				}

				// The number of guests is equal to the rows minus the ones we actually used ;).
				$context['view_num_guests'] = mysql_num_rows($request) - count($context['view_members']);
				mysql_free_result($request);

				// Sort the list.
				krsort($context['view_members']);
				krsort($context['view_members_list']);


		}


	}
	else
	{
		$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'];

		$dbresult = db_query("
		SELECT
			ID_CAT, title, roworder, description, image
		FROM {$db_prefix}gallery_cat ORDER BY roworder ASC", __FILE__, __LINE__);
		$context['gallery_cat_list'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['gallery_cat_list'][] = $row;
		}
		mysql_free_result($dbresult);

		// Get unapproved pictures
		$dbresult3 = db_query("
			SELECT
				COUNT(*) AS total
			FROM {$db_prefix}gallery_pic
			WHERE approved = 0", __FILE__, __LINE__);
			$totalrow = mysql_fetch_assoc($dbresult3);
			$totalpics = $totalrow['total'];
		mysql_free_result($dbresult3);
		$context['total_unapproved'] = $totalpics;

		// Total reported images
		$dbresult4 = db_query("
			SELECT
				COUNT(*) AS total
			FROM {$db_prefix}gallery_report", __FILE__, __LINE__);
			$totalrow = mysql_fetch_assoc($dbresult4);
			$totalreport = $totalrow['total'];
			mysql_free_result($dbresult4);
		$context['total_reported_images'] = $totalreport;

	}
}

function AddCategory()
{
	global $context, $mbname, $txt, $modSettings, $sourcedir;

	isAllowedTo('smfgallery_manage');

	adminIndex('gallery_settings');

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_addcategory'];

	$context['sub_template']  = 'add_category';

   	$context['linktree'][] = array(
			'name' => '<em>' .  $txt['gallery_text_addcategory']. '</em>'
		);

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');
	$context['post_box_name'] = 'description';
	$context['post_form'] = 'catform';

}

function AddCategory2()
{
	global $db_prefix, $txt, $func;

	isAllowedTo('smfgallery_manage');

	$title = $func['htmlspecialchars']($_REQUEST['title'], ENT_QUOTES);
	$description = $func['htmlspecialchars']($_REQUEST['description'], ENT_QUOTES);
	$image =  $func['htmlspecialchars']($_REQUEST['image'], ENT_QUOTES);

	if (trim($title) == '')
		fatal_error($txt['gallery_error_cat_title'],false);

	// Do the order
	$dbresult = db_query("
	SELECT
		roworder
	FROM {$db_prefix}gallery_cat ORDER BY roworder DESC", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);

	$order = $row['roworder'];
	$order++;

	// Insert the category
	db_query("INSERT INTO {$db_prefix}gallery_cat
			(title, description,roworder,image)
		VALUES ('$title', '$description',$order,'$image')", __FILE__, __LINE__);
	mysql_free_result($dbresult);


	 redirectexit('action=gallery;sa=admincat');
}

function ViewC()
{
}

function EditCategory()
{
	global $context, $mbname, $txt, $modSettings, $db_prefix, $sourcedir;

	isAllowedTo('smfgallery_manage');

	adminIndex('gallery_settings');

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_editcategory'];

	$context['sub_template']  = 'edit_category';

    $context['linktree'][] = array(
			'name' => '<em>' .  $txt['gallery_text_editcategory']. '</em>'
		);

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	if (isset($_REQUEST['cat']))
	   $cat = (int) $_REQUEST['cat'];
    else
        $cat = 0;

	if (empty($cat))
		fatal_error($txt['gallery_error_no_cat']);

	$dbresult = db_query("
	SELECT
		ID_CAT, title, image, description
	FROM {$db_prefix}gallery_cat
	WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);

	$context['gallery_row'] = $row;

	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');
	$context['post_box_name'] = 'description';
	$context['post_form'] = 'catform';

}

function EditCategory2()
{
	global $db_prefix, $txt, $func;

	isAllowedTo('smfgallery_manage');

	// Clean the input
	$title = $func['htmlspecialchars']($_REQUEST['title'], ENT_QUOTES);
	$description = $func['htmlspecialchars']($_REQUEST['description'], ENT_QUOTES);
	$catid = (int) $_REQUEST['catid'];
	$image = $func['htmlspecialchars']($_REQUEST['image'], ENT_QUOTES);

	if (trim($title) == '')
		fatal_error($txt['gallery_error_cat_title'],false);

	// Update the category
	db_query("UPDATE {$db_prefix}gallery_cat
		SET title = '$title', image = '$image', description = '$description' WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);


	redirectexit('action=gallery;sa=admincat');

}

function DeleteCategory()
{
	global $context, $mbname, $txt;
	isAllowedTo('smfgallery_manage');

	adminIndex('gallery_settings');

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_delcategory'];

	$context['sub_template']  = 'delete_category';
}

function DeleteCategory2()
{
	global $db_prefix, $modSettings;

	isAllowedTo('smfgallery_manage');


	$catid = (int) $_REQUEST['catid'];

	$dbresult = db_query("
	SELECT
		ID_PICTURE, thumbfilename, filename
	FROM {$db_prefix}gallery_pic
	WHERE ID_CAT = $catid", __FILE__, __LINE__);

	while($row = mysql_fetch_assoc($dbresult))
	{
		// Delete Files

		// Delete Large image
		@unlink($modSettings['gallery_path'] . $row['filename']);
		// Delete Thumbnail
		@unlink($modSettings['gallery_path'] . $row['thumbfilename']);

		db_query("DELETE FROM {$db_prefix}gallery_comment WHERE ID_PICTURE  = " . $row['ID_PICTURE'], __FILE__, __LINE__);

		db_query("DELETE FROM {$db_prefix}gallery_report WHERE ID_PICTURE  = " . $row['ID_PICTURE'], __FILE__, __LINE__);

	}
	// Delete All Pictures
	db_query("DELETE FROM {$db_prefix}gallery_pic WHERE ID_CAT = $catid", __FILE__, __LINE__);

	// Finally delete the category
	db_query("DELETE FROM {$db_prefix}gallery_cat WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);


	redirectexit('action=gallery;sa=admincat');
}

function ViewPicture()
{
	global $context, $mbname, $db_prefix, $modSettings, $user_info, $scripturl, $txt, $ID_MEMBER;

	isAllowedTo('smfgallery_view');

	// Get the picture ID
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected'], false);


	// Get the picture information
    $dbresult = db_query("
    SELECT
    	p.ID_PICTURE, p.width, p.height, p.allowcomments, p.ID_CAT, p.keywords, p.commenttotal, p.filesize, p.filename, p.approved,
    	p.views, p.title, p.ID_MEMBER, m.memberName, m.realName, p.date, p.description, c.title CATNAME
    FROM {$db_prefix}gallery_pic as p
    LEFT JOIN {$db_prefix}gallery_cat AS c ON (c.ID_CAT= p.ID_CAT)
    LEFT JOIN {$db_prefix}members AS m ON (p.ID_MEMBER = m.ID_MEMBER)
    WHERE p.ID_PICTURE = $id   LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);

    if (empty($row['ID_PICTURE']))
    {
        fatal_error($txt['gallery_error_no_pic_selected'], false);
    }


	// Checked if they are allowed to view an unapproved picture.
	if ($row['approved'] == 0 && $ID_MEMBER != $row['ID_MEMBER'])
	{
		if(!allowedTo('smfgallery_manage'))
			fatal_error($txt['gallery_error_pic_notapproved'],false);
	}


	$context['linktree'][] = array(
					'url' => $scripturl . '?action=gallery;cat=' . $row['ID_CAT'],
					'name' => $row['CATNAME'],
				);

	// Gallery picture information
	$context['gallery_pic'] = array(
		'ID_PICTURE' => $row['ID_PICTURE'],
		'ID_MEMBER' => $row['ID_MEMBER'],
		'commenttotal' => $row['commenttotal'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'width' => $row['width'],
		'height' => $row['height'],
		'allowcomments' => $row['allowcomments'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => timeformat($row['date']),
		'keywords' => $row['keywords'],
		'memberName' => $row['memberName'],
		'realName' => $row['realName'],
	);
	mysql_free_result($dbresult);


	// Update the number of views.
	  $dbresult = db_query("UPDATE {$db_prefix}gallery_pic
		SET views = views + 1 WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);


	$context['sub_template']  = 'view_picture';

	$context['page_title'] = $mbname . ' - ' . $context['gallery_pic']['title'];

	if (!empty($modSettings['gallery_who_viewing']))
	{
		$context['can_moderate_forum'] = allowedTo('moderate_forum');

				//SMF 1.1
				//Taken from Display.php
				// Start out with no one at all viewing it.
				$context['view_members'] = array();
				$context['view_members_list'] = array();
				$context['view_num_hidden'] = 0;
				$whoID = (string) $id;

				// Search for members who have this picture id set in their GET data.
				$request = db_query("
					SELECT
						lo.ID_MEMBER, lo.logTime, mem.realName, mem.memberName, mem.showOnline,
						mg.onlineColor, mg.ID_GROUP, mg.groupName
					FROM {$db_prefix}log_online AS lo
						LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = lo.ID_MEMBER)
						LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
					WHERE INSTR(lo.url, 's:7:\"gallery\";s:2:\"sa\";s:4:\"view\";s:3:\"pic\";s:" . strlen($whoID ) .":\"$id\";') OR INSTR(lo.url, 's:7:\"gallery\";s:2:\"sa\";s:4:\"view\";s:2:\"id\";s:" . strlen($whoID ) .":\"$id\";') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'", __FILE__, __LINE__);
				while ($row = mysql_fetch_assoc($request))
				{
					if (empty($row['ID_MEMBER']))
						continue;

					if (!empty($row['onlineColor']))
						$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '" style="color: ' . $row['onlineColor'] . ';">' . $row['realName'] . '</a>';
					else
						$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a>';

					$is_buddy = in_array($row['ID_MEMBER'], $user_info['buddies']);
					if ($is_buddy)
						$link = '<b>' . $link . '</b>';

					// Add them both to the list and to the more detailed list.
					if (!empty($row['showOnline']) || allowedTo('moderate_forum'))
						$context['view_members_list'][$row['logTime'] . $row['memberName']] = empty($row['showOnline']) ? '<i>' . $link . '</i>' : $link;
					$context['view_members'][$row['logTime'] . $row['memberName']] = array(
						'id' => $row['ID_MEMBER'],
						'username' => $row['memberName'],
						'name' => $row['realName'],
						'group' => $row['ID_GROUP'],
						'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
						'link' => $link,
						'is_buddy' => $is_buddy,
						'hidden' => empty($row['showOnline']),
					);

					if (empty($row['showOnline']))
						$context['view_num_hidden']++;
				}

				// The number of guests is equal to the rows minus the ones we actually used ;).
				$context['view_num_guests'] = mysql_num_rows($request) - count($context['view_members']);
				mysql_free_result($request);

				// Sort the list.
				krsort($context['view_members']);
				krsort($context['view_members_list']);

	}

		$dbresult = db_query("
		SELECT
			c.ID_PICTURE,  c.ID_COMMENT, c.date, c.comment, c.ID_MEMBER, m.posts, m.memberName,m.realName
			FROM {$db_prefix}gallery_comment as c
			LEFT JOIN {$db_prefix}members AS m ON (c.ID_MEMBER = m.ID_MEMBER)
		WHERE   c.ID_PICTURE = " . $context['gallery_pic']['ID_PICTURE'] . " ORDER BY c.ID_COMMENT DESC", __FILE__, __LINE__);
		$context['gallery_comment_count'] = db_affected_rows();
		$context['gallery_comment_list'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['gallery_comment_list'][] = $row;
		}
		mysql_free_result($dbresult);


}

function AddPicture()
{
	global $context, $mbname, $txt, $modSettings, $db_prefix, $sourcedir;

	isAllowedTo('smfgallery_add');


	$context['sub_template']  = 'add_picture';

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_addpicture'];

    $context['linktree'][] = array(
			'name' => '<em>' .  $txt['gallery_form_addpicture']. '</em>'
		);

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	 $dbresult = db_query("
 	SELECT
 		ID_CAT, title
 	FROM {$db_prefix}gallery_cat ORDER BY roworder ASC", __FILE__, __LINE__);
	$context['gallery_cat_list'] = array();
	while($row = mysql_fetch_assoc($dbresult))
	{
		$context['gallery_cat_list'][] = $row;
	}
	mysql_free_result($dbresult);

	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');
	$context['post_box_name'] = 'description';
	$context['post_form'] = 'picform';
}

function AddPicture2()
{
	global $ID_MEMBER, $txt, $db_prefix, $modSettings, $sourcedir, $gd2, $func;

	isAllowedTo('smfgallery_add');

	// Check if gallery path is writable
	if (!is_writable($modSettings['gallery_path']))
		fatal_error($txt['gallery_write_error'] . $modSettings['gallery_path']);


	$title = $func['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
	$description = $func['htmlspecialchars']($_REQUEST['description'],ENT_QUOTES);
	$keywords = $func['htmlspecialchars']($_REQUEST['keywords'],ENT_QUOTES);
	$cat = (int) $_REQUEST['cat'];



   $allowcomments = isset($_REQUEST['allowcomments']) ? 1 : 0;

	// Check if pictures are auto approved
	$approved = (allowedTo('smfgallery_autoapprove') ? 1 : 0);

	// Allow comments on picture if no setting set.
	if(empty($modSettings['gallery_commentchoice']) || $modSettings['gallery_commentchoice'] == 0)
		$allowcomments = 1;
	else
	{
		if(empty($allowcomments))
			$allowcomments = 0;
		else
			$allowcomments = 1;
	}

	if (trim($title) == '')
		fatal_error($txt['gallery_error_no_title'],false);
	if (empty($cat))
		fatal_error($txt['gallery_error_no_cat'],false);

	CheckGalleryCategoryExists($cat);

	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);

	// Process Uploaded file
	if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
	{

		$sizes = getimagesize($_FILES['picture']['tmp_name']);
		$failed = false;
		if ($sizes === false)
		{
			@unlink($modSettings['gallery_path'] . 'img.tmp');
			move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . 'img.tmp');

			$_FILES['picture']['tmp_name'] = $modSettings['gallery_path'] . 'img.tmp';
			$sizes = getimagesize($_FILES['picture']['tmp_name']);
			$failed =true;
		}

			// No size, then it's probably not a valid pic.
			if ($sizes === false)
			{
				@unlink($modSettings['gallery_path'] . 'img.tmp');
				fatal_error($txt['gallery_error_invalid_picture'],false);
			}
			elseif ((!empty($modSettings['gallery_max_width']) && $sizes[0] > $modSettings['gallery_max_width']) || (!empty($modSettings['gallery_max_height']) && $sizes[1] > $modSettings['gallery_max_height']))
			{
				//Delete the temp file
				@unlink($_FILES['picture']['tmp_name']);
				fatal_error($txt['gallery_error_img_size_height'] . $sizes[1] . $txt['gallery_error_img_size_width'] . $sizes[0],false);
			}
			else
			{
				//Get the filesize
				$filesize = $_FILES['picture']['size'];

				if(!empty($modSettings['gallery_max_filesize']) && $filesize > $modSettings['gallery_max_filesize'])
				{
					//Delete the temp file
					@unlink($_FILES['picture']['tmp_name']);
					fatal_error($txt['gallery_error_img_filesize'] . gallery_format_size($modSettings['gallery_max_filesize'], 2),false);
				}

				//Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
				$extensions = array(
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
				$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';


				$filename = $ID_MEMBER . '_' . date('d_m_y_g_i_s') . '.' . $extension;

				if ($failed == false)
					move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . $filename);
				else
					rename($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . $filename);

				@chmod($modSettings['gallery_path'] . $filename, 0644);
				//Create thumbnail
				require_once($sourcedir . '/Subs-Graphics.php');

				createThumbnail($modSettings['gallery_path'] . $filename, $modSettings['gallery_thumb_width'], $modSettings['gallery_thumb_height']);
				rename($modSettings['gallery_path'] . $filename . '_thumb',  $modSettings['gallery_path'] . 'thumb_' . $filename);
				$thumbname = 'thumb_' . $filename;



				@chmod($modSettings['gallery_path'] . $thumbname, 0644);
				//Create the Database entry
				$t = time();
				db_query("INSERT INTO {$db_prefix}gallery_pic
							(ID_CAT, filesize,thumbfilename,filename, height, width, keywords, title, description,ID_MEMBER,date,approved,allowcomments)
						VALUES ($cat, $filesize,'$thumbname', '$filename', $sizes[1], $sizes[0], '$keywords','$title', '$description',$ID_MEMBER,$t,$approved, $allowcomments)", __FILE__, __LINE__);

			// Update the SMF Shop Points
			if (isset($modSettings['shopVersion']))
 				db_query("UPDATE {$db_prefix}members
				 	SET money = money + " . $modSettings['gallery_shop_picadd'] . "
				 	WHERE ID_MEMBER = {$ID_MEMBER}
				 	LIMIT 1", __FILE__, __LINE__);

			  	// Badge Awards Mod Check
			 	GalleryCheckBadgeAwards($ID_MEMBER);


				//Redirect to the users image page.
				if ($ID_MEMBER != 0)
					redirectexit('action=gallery;sa=myimages;u=' . $ID_MEMBER);
				else
					redirectexit('action=gallery;cat=' . $cat);
			}




	}
	else
		fatal_error($txt['gallery_error_no_picture']);

}

function EditPicture()
{
	global $context, $mbname, $txt, $ID_MEMBER, $db_prefix, $modSettings, $sourcedir;

	is_not_guest();

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected']);


	// Check if the user owns the picture or is admin
    $dbresult = db_query("
    SELECT p.ID_PICTURE, p.thumbfilename, p.width, p.height, p.allowcomments, p.ID_CAT, p.keywords,
    p.commenttotal, p.filesize, p.filename, p.approved, p.views, p.title, p.ID_MEMBER, m.memberName, m.realName, p.date, p.description
    FROM {$db_prefix}gallery_pic as p
    LEFT JOIN {$db_prefix}members AS m ON (m.ID_MEMBER = p.ID_MEMBER)
    WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);

	//Gallery picture information
	$context['gallery_pic'] = array(
		'ID_PICTURE' => $row['ID_PICTURE'],
		'ID_MEMBER' => $row['ID_MEMBER'],
		'commenttotal' => $row['commenttotal'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'thumbfilename' => $row['thumbfilename'],
		'width' => $row['width'],
		'height' => $row['height'],
		'allowcomments' => $row['allowcomments'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => timeformat($row['date']),
		'keywords' => $row['keywords'],
		'memberName' => $row['memberName'],
		'realName' => $row['realName'],
	);
	mysql_free_result($dbresult);


	 $dbresult = db_query("
 	SELECT
 		ID_CAT, title
 	FROM {$db_prefix}gallery_cat ORDER BY roworder ASC", __FILE__, __LINE__);
	$context['gallery_cat_list'] = array();
	while($row = mysql_fetch_assoc($dbresult))
	{
		$context['gallery_cat_list'][] = $row;
	}
	mysql_free_result($dbresult);


	if(allowedTo('smfgallery_manage') || (allowedTo('smfgallery_edit') && $ID_MEMBER == $context['gallery_pic']['ID_MEMBER']))
	{
		$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_editpicture'];
		$context['sub_template']  = 'edit_picture';

        $context['linktree'][] = array(
			'name' => '<em>' .  $txt['gallery_form_editpicture']. '</em>'
		);

		/// Used for the editor
		require_once($sourcedir . '/Subs-Post.php');
		$context['post_box_name'] = 'description';
		$context['post_form'] = 'picform';

		// Check if spellchecking is both enabled and actually working.
		$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
	}
	else
	{
		fatal_error($txt['gallery_error_noedit_permission']);
	}


}

function EditPicture2()
{
	global $ID_MEMBER, $txt, $db_prefix, $modSettings, $sourcedir, $gd2, $func;

	is_not_guest();

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected']);

	// Check the user permissions
    $dbresult = db_query("
    SELECT
    	ID_MEMBER,thumbfilename,filename
    FROM {$db_prefix}gallery_pic
    WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$memID = $row['ID_MEMBER'];
	$oldfilename = $row['filename'];
	$oldthumbfilename  = $row['thumbfilename'];

	mysql_free_result($dbresult);
	if (allowedTo('smfgallery_manage') || (allowedTo('smfgallery_edit') && $ID_MEMBER == $memID))
	{

		if(!is_writable($modSettings['gallery_path']))
			fatal_error($txt['gallery_write_error'] . $modSettings['gallery_path']);

		$title = $func['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
		$description = $func['htmlspecialchars']($_REQUEST['description'],ENT_QUOTES);
		$keywords = $func['htmlspecialchars']($_REQUEST['keywords'],ENT_QUOTES);
		$cat = (int) $_REQUEST['cat'];

		$allowcomments = isset($_REQUEST['allowcomments']) ? 1 : 0;

		//Check if pictures are auto approved
		$approved = (allowedTo('smfgallery_autoapprove') ? 1 : 0);

		//Allow comments on picture if no setting set.
		if (empty($modSettings['gallery_commentchoice']) || $modSettings['gallery_commentchoice'] == 0)
			$allowcomments = 1;
		else
		{
			if(empty($allowcomments))
				$allowcomments = 0;
			else
				$allowcomments = 1;
		}



		if (trim($title) == '')
			fatal_error($txt['gallery_error_no_title'],false);
		if (empty($cat))
			fatal_error($txt['gallery_error_no_cat'],false);

		CheckGalleryCategoryExists($cat);


		$testGD = get_extension_funcs('gd');
		$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
		unset($testGD);

		//Process Uploaded file
		if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
		{

				$sizes = getimagesize($_FILES['picture']['tmp_name']);
				$failed = false;
				if ($sizes === false)
				{
					@unlink($modSettings['gallery_path'] . 'img.tmp');
					move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . 'img.tmp');

					$_FILES['picture']['tmp_name'] = $modSettings['gallery_path'] . 'img.tmp';
					$sizes = getimagesize($_FILES['picture']['tmp_name']);
					$failed =true;
				}

				// No size, then it's probably not a valid pic.
				if ($sizes === false)
					fatal_error($txt['gallery_error_invalid_picture'],false);
				elseif ((!empty($modSettings['gallery_max_width']) && $sizes[0] > $modSettings['gallery_max_width']) || (!empty($modSettings['gallery_max_height']) && $sizes[1] > $modSettings['gallery_max_height']))
				{
					fatal_error($txt['gallery_error_img_size_height'] . $sizes[1] . $txt['gallery_error_img_size_width']. $sizes[0],false);
				}
				else
				{

					//Get the filesize
					$filesize = $_FILES['picture']['size'];
					if(!empty($modSettings['gallery_max_filesize']) && $filesize > $modSettings['gallery_max_filesize'])
					{
						//Delete the temp file
						@unlink($_FILES['picture']['tmp_name']);
						fatal_error($txt['gallery_error_img_filesize'] . gallery_format_size($modSettings['gallery_max_filesize'], 2),false);
					}
					//Delete the old files
					@unlink($modSettings['gallery_path'] . $oldfilename );
					@unlink($modSettings['gallery_path'] . $oldthumbfilename);

					//Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
					$extensions = array(
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
					$extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';


					$filename = $ID_MEMBER . '_' . date('d_m_y_g_i_s') . '.' . $extension;
					if ($failed == false)
						move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . $filename);
					else
						rename($_FILES['picture']['tmp_name'], $modSettings['gallery_path'] . $filename);

					@chmod($modSettings['gallery_path'] . $filename, 0644);
					//Create thumbnail
					require_once($sourcedir . '/Subs-Graphics.php');

					createThumbnail($modSettings['gallery_path'] . $filename, $modSettings['gallery_thumb_width'], $modSettings['gallery_thumb_height']);
					rename($modSettings['gallery_path'] . $filename . '_thumb',  $modSettings['gallery_path'] . 'thumb_' . $filename);
					$thumbname = 'thumb_' . $filename;


					@chmod($modSettings['gallery_path'] . $thumbname, 0644);

					//Update the Database entry
					$t = time();

					db_query("UPDATE {$db_prefix}gallery_pic
					SET ID_CAT = $cat, filesize = $filesize, filename = '$filename',  thumbfilename = '$thumbname', height = $sizes[1], width = $sizes[0], approved = $approved, date =  $t, title = '$title', description = '$description', keywords = '$keywords', allowcomments = $allowcomments WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);


					//Redirect to the users image page.
					redirectexit('action=gallery;sa=myimages;u=' . $ID_MEMBER);
				}

		}
		else
		{
			//Update the image properties if no upload has been set
			db_query("UPDATE {$db_prefix}gallery_pic
				SET ID_CAT = $cat, title = '$title', description = '$description', keywords = '$keywords', allowcomments = $allowcomments WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);

			//Redirect to the users image page.
			redirectexit('action=gallery;sa=myimages;u=' . $ID_MEMBER);

		}

	}
	else
		fatal_error($txt['gallery_error_noedit_permission']);


}

function DeletePicture()
{
	global $context, $mbname, $txt, $ID_MEMBER, $db_prefix;

	is_not_guest();

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected']);


	// Check if the user owns the picture or is admin
    $dbresult = db_query("
    SELECT
    	p.ID_PICTURE, p.thumbfilename, p.width, p.height, p.allowcomments, p.ID_CAT, p.keywords, p.commenttotal, p.filesize, p.filename, p.approved, p.views, p.title, p.ID_MEMBER, m.memberName, m.realName, p.date, p.description
    FROM {$db_prefix}gallery_pic as p
    LEFT JOIN {$db_prefix}members AS m ON (m.ID_MEMBER = p.ID_MEMBER)
    WHERE ID_PICTURE = $id  LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);

	// Gallery picture information
	$context['gallery_pic'] = array(
		'ID_PICTURE' => $row['ID_PICTURE'],
		'ID_MEMBER' => $row['ID_MEMBER'],
		'commenttotal' => $row['commenttotal'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'thumbfilename' => $row['thumbfilename'],
		'width' => $row['width'],
		'height' => $row['height'],
		'allowcomments' => $row['allowcomments'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => timeformat($row['date']),
		'keywords' => $row['keywords'],
		'memberName' => $row['memberName'],
		'realName' => $row['realName'],
	);
	mysql_free_result($dbresult);

	if (AllowedTo('smfgallery_manage') || (AllowedTo('smfgallery_delete') && $ID_MEMBER == $context['gallery_pic']['ID_MEMBER']))
	{
		$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_delpicture'];
		$context['sub_template']  = 'delete_picture';

        $context['linktree'][] = array(
			'name' => '<em>' .  $txt['gallery_form_delpicture']. '</em>'
		);

	}
	else
	{
		fatal_error($txt['gallery_error_nodelete_permission']);
	}

}

function DeletePicture2()
{
	global $txt, $ID_MEMBER, $db_prefix, $modSettings;

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected']);

	// Check if the user owns the picture or is admin
    $dbresult = db_query("
    SELECT
    	p.ID_PICTURE, p.filename, p.thumbfilename,  p.ID_MEMBER
    FROM {$db_prefix}gallery_pic as p
    WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$memID = $row['ID_MEMBER'];
	mysql_free_result($dbresult);

	if (AllowedTo('smfgallery_manage') || (AllowedTo('smfgallery_delete') && $ID_MEMBER == $memID))
	{
		//Delete Large image
		@unlink($modSettings['gallery_path'] . $row['filename']);
		//Delete Thumbnail
		@unlink($modSettings['gallery_path'] . $row['thumbfilename']);

		// Delete all the picture related db entries

		db_query("DELETE FROM {$db_prefix}gallery_comment WHERE ID_PICTURE  = $id LIMIT 1", __FILE__, __LINE__);

		db_query("DELETE FROM {$db_prefix}gallery_report WHERE ID_PICTURE  = $id LIMIT 1", __FILE__, __LINE__);

		// Delete the picture
		db_query("DELETE FROM {$db_prefix}gallery_pic WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);

		// Update the SMF Shop Points
			if (isset($modSettings['shopVersion']))
 				db_query("UPDATE {$db_prefix}members
				 	SET money = money - " . $modSettings['gallery_shop_picadd'] . "
				 	WHERE ID_MEMBER = {$memID}
				 	LIMIT 1", __FILE__, __LINE__);

		// Redirect to the users image page.
		redirectexit('action=gallery;sa=myimages;u=' . $ID_MEMBER);


	}
	else
	{
		fatal_error($txt['gallery_error_nodelete_permission']);
	}


}

function ReportPicture()
{
	global $context, $mbname, $txt;

	isAllowedTo('smfgallery_report');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected']);

	$context['gallery_pic_id'] = $id;

	$context['sub_template']  = 'report_picture';

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_reportpicture'];

    $context['linktree'][] = array(
			'name' => $txt['gallery_form_reportpicture']
		);

}

function ReportPicture2()
{
	global $db_prefix, $ID_MEMBER, $txt, $func;

	isAllowedTo('smfgallery_report');

	$comment = $func['htmlspecialchars']($_REQUEST['comment'],ENT_QUOTES);
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected']);

	if (trim($comment) == '')
		fatal_error($txt['gallery_error_no_comment'],false);

	$commentdate = time();

	db_query("INSERT INTO {$db_prefix}gallery_report
			(ID_MEMBER, comment, date, ID_PICTURE)
		VALUES ($ID_MEMBER,'$comment', $commentdate,$id)", __FILE__, __LINE__);

	redirectexit('action=gallery;sa=view&id=' . $id);

}

function AddComment()
{
	global $context, $mbname, $txt, $modSettings, $db_prefix, $settings, $sourcedir;



	isAllowedTo('smfgallery_comment');
	loadlanguage('Post');


	$id = (int) $_REQUEST['id'];
	if(empty($id) )
		fatal_error($txt['gallery_error_no_pic_selected']);

	$context['gallery_pic_id'] = $id;

	// Comments allowed check
    $dbresult = db_query("
    SELECT
    	p.allowcomments
    FROM {$db_prefix}gallery_pic as p
    WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	// Checked if comments are allowed
	if ($row['allowcomments'] == 0)
		fatal_error($txt['gallery_error_not_allowcomment']);


	$context['sub_template']  = 'add_comment';

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_addcomment'];

    $context['linktree'][] = array(
			'name' => '<em>' .  $txt['gallery_text_addcomment']. '</em>'
		);

	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');
	$context['post_box_name'] = 'comment';
	$context['post_form'] = 'cprofile';

}

function AddComment2()
{
	global $db_prefix, $ID_MEMBER, $txt, $modSettings, $func;

	isAllowedTo('smfgallery_comment');

	$comment = $func['htmlspecialchars']($_REQUEST['comment'],ENT_QUOTES);
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected']);

	//Check if that picture allows comments.
    $dbresult = db_query("
    SELECT
    	p.allowcomments
    FROM {$db_prefix}gallery_pic as p
    WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	//Checked if comments are allowed
	if ($row['allowcomments'] == 0)
		fatal_error($txt['gallery_error_not_allowcomment']);

	if (trim($comment) == '')
		fatal_error($txt['gallery_error_no_comment'],false);

	$commentdate = time();

	db_query("INSERT INTO {$db_prefix}gallery_comment
			(ID_MEMBER, comment, date, ID_PICTURE)
		VALUES ($ID_MEMBER,'$comment', $commentdate,$id)", __FILE__, __LINE__);


			// Update the SMF Shop Points
			if (isset($modSettings['shopVersion']))
 				db_query("UPDATE {$db_prefix}members
				 	SET money = money + " . $modSettings['gallery_shop_commentadd'] . "
				 	WHERE ID_MEMBER = {$ID_MEMBER}
				 	LIMIT 1", __FILE__, __LINE__);

 	// Badge Awards Mod Check
 	GalleryCheckBadgeAwards($ID_MEMBER);

	// Update Comment total
	 db_query("UPDATE {$db_prefix}gallery_pic
		SET commenttotal = commenttotal + 1 WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);


	redirectexit('action=gallery;sa=view&id=' . $id);

}

function DeleteComment()
{
	global $db_prefix, $txt, $modSettings;

	is_not_guest();

	isAllowedTo('smfgallery_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_com_selected']);


	//Get the picture ID for redirect
	$dbresult = db_query("
	SELECT
		ID_PICTURE,ID_COMMENT, ID_MEMBER
	FROM {$db_prefix}gallery_comment
	WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$picid = $row['ID_PICTURE'];
	$memID = $row['ID_MEMBER'];
	mysql_free_result($dbresult);
	//Now delete the comment.
	db_query("DELETE FROM {$db_prefix}gallery_comment WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);


	//Update Comment total
	  $dbresult = db_query("UPDATE {$db_prefix}gallery_pic
		SET commenttotal = commenttotal - 1 WHERE ID_PICTURE = $picid LIMIT 1", __FILE__, __LINE__);

	// Update the SMF Shop Points
	if (isset($modSettings['shopVersion']))
 				db_query("UPDATE {$db_prefix}members
				 	SET money = money - " . $modSettings['gallery_shop_commentadd'] . "
				 	WHERE ID_MEMBER = {$memID}
				 	LIMIT 1", __FILE__, __LINE__);

	// Redirect to the picture
	redirectexit('action=gallery;sa=view&id=' . $picid);
}

function AdminSettings()
{
	global $context, $mbname, $txt, $modSettings;
	isAllowedTo('smfgallery_manage');

	adminIndex('gallery_settings');

	DoGalleryAdminTabs();

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_settings'];

	$context['sub_template']  = 'settings';

    if (isset($_REQUEST['newstart']))
    {
        if (isAevaInstalled() == true && empty($modSettings['gallery_avea_imported']))
        {
            redirectexit('action=gallery;sa=convert');
        }
    }


}

function AdminSettings2()
{
	isAllowedTo('smfgallery_manage');

	// Get the settings

	$gallery_max_height = (int) $_REQUEST['gallery_max_height'];
	$gallery_max_width =  (int) $_REQUEST['gallery_max_width'];
	$gallery_max_filesize =  (int) $_REQUEST['gallery_max_filesize'];
	$gallery_commentchoice =  isset($_REQUEST['gallery_commentchoice']) ? 1 : 0;
	$gallery_set_images_per_page = (int) $_REQUEST['gallery_set_images_per_page'];
	$gallery_set_images_per_row = (int) $_REQUEST['gallery_set_images_per_row'];
	$gallery_thumb_width = (int) $_REQUEST['gallery_thumb_width'];
	$gallery_thumb_height = (int) $_REQUEST['gallery_thumb_height'];

	// Shop settings
	$gallery_shop_picadd = (int) $_REQUEST['gallery_shop_picadd'];
	$gallery_shop_commentadd = (int) $_REQUEST['gallery_shop_commentadd'];

	$gallery_path = $_REQUEST['gallery_path'];
	$gallery_url = $_REQUEST['gallery_url'];
	$gallery_who_viewing = isset($_REQUEST['gallery_who_viewing']) ? 1 : 0;


	// Image Linking codes
	$gallery_set_showcode_bbc_image = isset($_REQUEST['gallery_set_showcode_bbc_image']) ? 1 : 0;
	$gallery_set_showcode_directlink = isset($_REQUEST['gallery_set_showcode_directlink']) ? 1 : 0;
	$gallery_set_showcode_htmllink = isset($_REQUEST['gallery_set_showcode_htmllink']) ? 1 : 0;


	updateSettings(
	array(
	'gallery_max_height' => $gallery_max_height,
	'gallery_max_width' => $gallery_max_width,
	'gallery_max_filesize' => $gallery_max_filesize,
	'gallery_path' => $gallery_path,
	'gallery_url' => $gallery_url,
	'gallery_commentchoice' => $gallery_commentchoice,
	'gallery_who_viewing' => $gallery_who_viewing,
	'gallery_shop_commentadd' => $gallery_shop_commentadd,
	'gallery_shop_picadd' => $gallery_shop_picadd,
	'gallery_set_images_per_page' => $gallery_set_images_per_page,
	'gallery_set_images_per_row' => $gallery_set_images_per_row,
	'gallery_thumb_width' => $gallery_thumb_width,
	'gallery_thumb_height' => $gallery_thumb_height,

	'gallery_set_showcode_bbc_image' => $gallery_set_showcode_bbc_image,
	'gallery_set_showcode_directlink' => $gallery_set_showcode_directlink,
	'gallery_set_showcode_htmllink' => $gallery_set_showcode_htmllink,

	));

	redirectexit('action=gallery;sa=adminset');

}

function AdminCats()
{
	global $context, $mbname, $txt, $db_prefix;
	isAllowedTo('smfgallery_manage');

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_managecats'];

	adminIndex('gallery_settings');

	DoGalleryAdminTabs();

	$context['sub_template']  = 'manage_cats';

	$dbresult = db_query("
		SELECT
			ID_CAT, title, roworder, description, image
		FROM {$db_prefix}gallery_cat ORDER BY roworder ASC", __FILE__, __LINE__);
	$context['gallery_manage_cats'] = array();
	while($row = mysql_fetch_assoc($dbresult))
	{
		$context['gallery_manage_cats'][] = $row;
	}
	mysql_free_result($dbresult);

}

function CatUp()
{
	global $db_prefix, $txt;
	// Check if they are allowed to manage cats
	isAllowedTo('smfgallery_manage');

	// Get the cat id
	@$cat = (int) $_REQUEST['cat'];
	ReOrderCats($cat);

	//Check if there is a category above it
	//First get our row order
	$dbresult1 = db_query("
	SELECT
		roworder
	FROM {$db_prefix}gallery_cat
	WHERE ID_CAT = $cat", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o--;

	mysql_free_result($dbresult1);
	$dbresult = db_query("
	SELECT
		ID_CAT, roworder
	FROM {$db_prefix}gallery_cat
	WHERE roworder = $o", __FILE__, __LINE__);
	if(db_affected_rows()== 0)
		fatal_error($txt['gallery_nocatabove'],false);
	$row2 = mysql_fetch_assoc($dbresult);


	// Swap the order Id's
	db_query("UPDATE {$db_prefix}gallery_cat
		SET roworder = $oldrow WHERE ID_CAT = " .$row2['ID_CAT'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}gallery_cat
		SET roworder = $o WHERE ID_CAT = $cat", __FILE__, __LINE__);


	mysql_free_result($dbresult);

	// Redirect to index to view cats
	redirectexit('action=gallery');
}

function CatDown()
{
	global $db_prefix, $txt;

	// Check if they are allowed to manage cats
	isAllowedTo('smfgallery_manage');

	// Get the cat id
	@$cat = (int) $_REQUEST['cat'];
	ReOrderCats($cat);
	// Check if there is a category below it
	// First get our row order
	$dbresult1 = db_query("
	SELECT
		roworder
	FROM {$db_prefix}gallery_cat
	WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o++;

	mysql_free_result($dbresult1);
	$dbresult = db_query("
	SELECT
		ID_CAT, roworder
	FROM {$db_prefix}gallery_cat
	WHERE roworder = $o", __FILE__, __LINE__);
	if (db_affected_rows()== 0)
		fatal_error($txt['gallery_nocatbelow'],false);
	$row2 = mysql_fetch_assoc($dbresult);


	//Swap the order Id's
	db_query("UPDATE {$db_prefix}gallery_cat
		SET roworder = $oldrow WHERE ID_CAT = " .$row2['ID_CAT'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}gallery_cat
		SET roworder = $o WHERE ID_CAT = $cat", __FILE__, __LINE__);


	mysql_free_result($dbresult);

	//Redirect to index to view cats
	redirectexit('action=gallery');
}

function MyImages()
{
	global $context, $mbname, $txt, $db_prefix, $ID_MEMBER, $modSettings;

	isAllowedTo('smfgallery_view');


	$u = (int) $_REQUEST['u'];
	if (empty($u))
		fatal_error($txt['gallery_error_no_user_selected']);

	// Store the gallery userid
	$context['gallery_userid'] = $u;

    $dbresult = db_query("
    SELECT
    	m.memberName, m.realName
    FROM {$db_prefix}members AS m
    WHERE m.ID_MEMBER = $u  LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$context['gallery_usergallery_name'] = $row['realName'];
	mysql_free_result($dbresult);

	$userid = $context['gallery_userid'];
	$dbresult = db_query("
		SELECT COUNT(*) AS total
		 FROM {$db_prefix}gallery_pic as p, {$db_prefix}members AS m
		WHERE p.id_member = $userid AND p.id_member = m.id_member " . ($ID_MEMBER == $u ? '' : ' AND p.approved = 1'), __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$context['gallery_totalpic'] = $row['total'];
	mysql_free_result($dbresult);

	$context['start'] = (int) $_REQUEST['start'];
	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $context['gallery_usergallery_name'];
	$context['sub_template']  = 'myimages';

    $context['linktree'][] = array(
			'name' => $txt['gallery_myimages']
		);

	if ($ID_MEMBER == $context['gallery_userid'])
    	$dbresult = db_query("SELECT
    		p.ID_PICTURE, p.commenttotal, p.title, p.filesize, p.thumbfilename, p.approved, p.views,
    		p.ID_MEMBER, m.realName, p.date, p.filename, p.height, p.width
    	FROM {$db_prefix}gallery_pic as p, {$db_prefix}members AS m
    	WHERE p.ID_MEMBER = " . $context['gallery_userid']. " AND p.ID_MEMBER = m.ID_MEMBER  LIMIT $context[start]," . $modSettings['gallery_set_images_per_page'], __FILE__, __LINE__);
	else
    	$dbresult = db_query("SELECT
    		p.ID_PICTURE, p.commenttotal, p.title, p.filesize, p.thumbfilename, p.approved, p.views,
    		p.ID_MEMBER, m.realName, p.date, p.filename, p.height, p.width
    	FROM {$db_prefix}gallery_pic as p, {$db_prefix}members AS m
    	WHERE p.ID_MEMBER = " . $context['gallery_userid']  . " AND p.ID_MEMBER = m.ID_MEMBER AND p.approved = 1  LIMIT $context[start]," . $modSettings['gallery_set_images_per_page'], __FILE__, __LINE__);
	$context['gallery_myimages'] = array();
	while($row = mysql_fetch_assoc($dbresult))
	{
		$context['gallery_myimages'][] = $row;
	}
	mysql_free_result($dbresult);

}

function ApproveList()
{
	global $context, $mbname, $txt, $db_prefix;

	isAllowedTo('smfgallery_manage');

	DoGalleryAdminTabs();

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_approveimages'];

	adminIndex('gallery_settings');

	$context['sub_template']  = 'approvelist';


	$dbresult = db_query("
		  	SELECT
		  		p.ID_PICTURE, p.thumbfilename, p.title, p.ID_MEMBER, m.memberName, m.realName, p.date, p.description,
		  		p.filename, p.height, p.width
		  	FROM {$db_prefix}gallery_pic as p
		  	LEFT JOIN {$db_prefix}members AS m  on (p.ID_MEMBER = m.ID_MEMBER)
		  	WHERE p.approved = 0 ORDER BY p.ID_PICTURE DESC", __FILE__, __LINE__);
	$context['gallery_approve_list'] = array();
	while($row = mysql_fetch_assoc($dbresult))
	{
		$context['gallery_approve_list'][] = $row;
	}
	mysql_free_result($dbresult);

}

function ApprovePicture()
{
	global $db_prefix, $txt;
	isAllowedTo('smfgallery_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected']);

	// Update the approval
	db_query("UPDATE {$db_prefix}gallery_pic SET approved = 1 WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);


	// Redirect to approval list
	redirectexit('action=gallery;sa=approvelist');

}

function UnApprovePicture()
{
	global $db_prefix, $txt;
	isAllowedTo('smfgallery_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected']);

	// Update the approval
	 db_query("UPDATE {$db_prefix}gallery_pic SET approved = 0 WHERE ID_PICTURE = $id LIMIT 1", __FILE__, __LINE__);

	// Redirect to approval list
	redirectexit('action=gallery;sa=approvelist');
}

function ReportList()
{
	global $context, $mbname, $txt, $db_prefix;

	isAllowedTo('smfgallery_manage');

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_form_reportimages'];

	adminIndex('gallery_settings');

	DoGalleryAdminTabs();

	$context['sub_template']  = 'reportlist';

	$dbresult = db_query("
		  	SELECT
		  		r.ID, r.ID_PICTURE, r.ID_MEMBER, m.memberName, m.realName, r.date, r.comment
		  	FROM {$db_prefix}gallery_report as r
		  	LEFT JOIN {$db_prefix}members AS m on (r.ID_MEMBER = m.ID_MEMBER) ORDER BY r.ID_PICTURE DESC", __FILE__, __LINE__);
	$context['gallery_report_list'] = array();
	while($row = mysql_fetch_assoc($dbresult))
	{
		$context['gallery_report_list'][] = $row;
	}
	mysql_free_result($dbresult);

}

function DeleteReport()
{
	global $db_prefix, $txt;

	// Check the permission
	isAllowedTo('smfgallery_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['gallery_error_no_report_selected']);

	db_query("DELETE FROM {$db_prefix}gallery_report WHERE ID = $id LIMIT 1", __FILE__, __LINE__);

	// Redirect to redirect list
	redirectexit('action=gallery;sa=reportlist');
}

function Search()
{
	global $context, $mbname, $txt, $scripturl, $ID_MEMBER;

	//  the user allowed to view the gallery?
	isAllowedTo('smfgallery_view');


	$context['sub_template']  = 'search';

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_search'];

    $context['linktree'][] = array(
			'name' =>  $txt['gallery_search']
		);
}

function Search2()
{
	global $context, $mbname, $txt, $ID_MEMBER, $scripturl, $db_prefix, $func;

	// Is the user allowed to view the gallery?
	isAllowedTo('smfgallery_view');

	$g_add = allowedTo('smfgallery_add');

	// MyImages
	if ($g_add && !($context['user']['is_guest']))
	$context['gallery']['buttons']['mylisting'] =  array(
		'text' => 'gallery_myimages',
		'url' =>$scripturl . '?action=gallery;sa=myimages;u=' . $ID_MEMBER,
		'lang' => true,
		'image' => '',

	);

	// Search
	$context['gallery']['buttons']['search'] =  array(
		'text' => 'gallery_search',
		'url' => $scripturl . '?action=gallery;sa=search',
		'lang' => true,
		'image' => '',

	);

	// Link Tree
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=gallery',
					'name' => $txt['gallery_text_title']
				);

	//Check if keyword search was selected
	@$keyword =  $func['htmlspecialchars']($_REQUEST['key'],ENT_QUOTES);
	if($keyword == '')
	{
		//Probably a normal Search
		if (isset($_REQUEST['searchfor']))
			$searchfor =  $func['htmlspecialchars']($_REQUEST['searchfor'],ENT_QUOTES);
		else
			$searchfor = '';

		if($searchfor == '')
			fatal_error($txt['gallery_error_no_search'],false);

		if($func['strlen']($searchfor) <= 3)
			fatal_error($txt['gallery_error_search_small'],false);

		// Check the search options
		$searchkeywords =  isset($_REQUEST['searchkeywords']) ? 1 : 0;
		$searchtitle =  isset($_REQUEST['searchtitle']) ? 1 : 0;
		$searchdescription =  isset($_REQUEST['searchdescription']) ? 1 : 0;

		$s1 = 1;
		$searchquery = '';
		if($searchtitle)
			$searchquery = "p.title LIKE '%$searchfor%' ";
		else
			$s1 = 0;

		$s2 = 1;
		if($searchdescription)
		{
			if($s1 == 1)
				$searchquery = "p.title LIKE '%$searchfor%' OR p.description LIKE '%$searchfor%'";
			else
				$searchquery = "p.description LIKE '%$searchfor%'";
		}
		else
			$s2 = 0;

		if($searchkeywords)
		{
			if($s1 == 1 || $s2 == 1)
				$searchquery .= " OR p.keywords LIKE '%$searchfor%'";
			else
				$searchquery = "p.keywords LIKE '%$searchfor%'";
		}


		if($searchquery == '')
			$searchquery = "p.title LIKE '%$searchfor%' ";

		$context['gallery_search_query'] = $searchquery;



		$context['gallery_search'] = $searchfor;
	}
	else
	{
		//Search for the keyword


		//Debating if I should add string length check for keywords...
		//if(strlen($keyword) <= 3)
			//fatal_error($txt['gallery_error_search_small']);

		$context['gallery_search'] = $keyword;

		$context['gallery_search_query'] = "p.keywords LIKE '%$keyword%'";
	}

	$context['sub_template']  = 'search_results';

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_searchresults'];

    $context['linktree'][] = array(
			'name' =>  $txt['gallery_searchresults']
		);

 	$dbresult = db_query("
    SELECT
    	p.ID_PICTURE, p.commenttotal, p.keywords, p.filesize, p.thumbfilename, p.approved, p.views, p.title, p.ID_MEMBER, m.realName, p.date, p.width, p.height, p.filename FROM {$db_prefix}gallery_pic as p
    LEFT JOIN {$db_prefix}members AS m ON (p.ID_MEMBER = m.ID_MEMBER)
    WHERE p.approved = 1 AND (" . $context['gallery_search_query'] . ")", __FILE__, __LINE__);
	$context['gallery_search_results'] = array();
	while($row = mysql_fetch_assoc($dbresult))
	{
		$context['gallery_search_results'][] = $row;
	}
	mysql_free_result($dbresult);

}

function ReOrderCats($cat = 0)
{
	global $db_prefix;


	$dbresult = db_query("
	SELECT
		ID_CAT, roworder
	FROM {$db_prefix}gallery_cat ORDER BY roworder ASC", __FILE__, __LINE__);
	if(db_affected_rows() != 0)
	{
		$count = 1;
		while($row2 = mysql_fetch_assoc($dbresult))
		{
			db_query("UPDATE {$db_prefix}gallery_cat
			SET roworder = $count WHERE ID_CAT = " . $row2['ID_CAT'], __FILE__, __LINE__);
			$count++;
		}
	}
	mysql_free_result($dbresult);
}

function DoGalleryAdminTabs($overrideSelected = '')
{
	global $context, $txt, $scripturl, $db_prefix;

	$tmpSA = '';
	if (!empty($overrideSelected))
	{
		$_REQUEST['sa'] = $overrideSelected;

	}


	$dbresult3 = db_query("
			SELECT
				COUNT(*) AS total
			FROM {$db_prefix}gallery_pic
			WHERE approved = 0", __FILE__, __LINE__);
			$totalrow = mysql_fetch_assoc($dbresult3);
			$totalappoval = $totalrow['total'];
			mysql_free_result($dbresult3);

	$dbresult4 = db_query("
			SELECT
				COUNT(*) AS total
			FROM {$db_prefix}gallery_report", __FILE__, __LINE__);
			$totalrow = mysql_fetch_assoc($dbresult4);
	$totalreport = $totalrow['total'];
	mysql_free_result($dbresult4);

	// Create the tabs for the template.
	$context['admin_tabs'] = array(
		'title' => $txt['smfgallery_admin'],
		//'help' => 'edit_news',
		'description' => '',
		'tabs' => array(),
	);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['gallery_text_settings'],
			'description' => '',
			'href' => $scripturl . '?action=gallery;sa=adminset',
			'is_selected' => $_REQUEST['sa'] == 'adminset',
		);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['gallery_form_managecats'],
			'description' => '',
			'href' => $scripturl . '?action=gallery;sa=admincat',
			'is_selected' => $_REQUEST['sa'] == 'admincat',
		);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['gallery_form_approveimages'] . ' (' . $totalappoval . ')',
			'description' => '',
			'href' => $scripturl . '?action=gallery;sa=approvelist',
			'is_selected' => $_REQUEST['sa'] == 'approvelist',
		);

	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['gallery_form_reportimages'] . ' (' . $totalreport . ')',
			'description' => '',
			'href' => $scripturl . '?action=gallery;sa=reportlist',
			'is_selected' => $_REQUEST['sa'] == 'reportlist',
		);

    $context['admin_tabs']['tabs'][] = array(
			'title' => $txt['gallery_txt_copyrightremoval'],
			'description' => '',
			'href' => $scripturl . '?action=gallery;sa=copyright',
			'is_selected' => $_REQUEST['sa'] == 'copyright',
		);

    $context['admin_tabs']['tabs'][] = array(
			'title' => $txt['gallery_txt_convertors'],
			'description' => '',
			'href' => $scripturl . '?action=gallery;sa=convert',
			'is_selected' => $_REQUEST['sa'] == 'convert',
		);

	if (!empty($overrideSelected))
	{
		$_REQUEST['sa'] = $tmpSA;

	}

	$context['admin_tabs']['tabs'][count($context['admin_tabs']['tabs']) - 1]['is_last'] = true;
}

function DoToolBarStrip($button_strip, $direction )
{
	global $settings, $txt;

	if (!empty($settings['use_tabs']))
	{
		template_button_strip($button_strip, $direction);
	}
	else
	{
			echo '<td>';

			foreach ($button_strip as $tab)
			{


				echo '
							<a href="', $tab['url'], '">', $txt[$tab['text']], '</a>';

				if (empty($tab['is_last']))
					echo ' | ';
			}

			echo '</td>';

	}

}


function GetTotalPicturesBYCATID($ID_CAT)
{
	global $db_prefix;

	$dbresult2 = db_query("
		  	SELECT
		  		COUNT(*) AS total
		  	FROM {$db_prefix}gallery_pic
		  	WHERE ID_CAT = ". $ID_CAT. ' AND approved = 1', __FILE__, __LINE__);
	$rowTotal = mysql_fetch_assoc($dbresult2);
	mysql_free_result($dbresult2);

	return $rowTotal['total'];
}

function CheckGalleryCategoryExists($cat)
{
	global $db_prefix, $txt;

	$dbresult2 = db_query("
		  	SELECT
		  		COUNT(*) AS total
		  	FROM {$db_prefix}gallery_cat
		  	WHERE ID_CAT = $cat ", __FILE__, __LINE__);
	$rowTotal = mysql_fetch_assoc($dbresult2);
	mysql_free_result($dbresult2);

	if ($rowTotal['total'] == 0)
		fatal_error($txt['gallery_error_category'],false);
}

function gallery_format_size($size, $round = 0)
{
    //Size must be bytes!
    $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    for ($i=0; $size > 1024 && $i < count($sizes) - 1; $i++) $size /= 1024;
    return round($size,$round).$sizes[$i];
}

function GalleryTopButtons()
{
	global $context, $ID_MEMBER, $scripturl, $txt;

	$g_add = allowedTo('smfgallery_add');

	$catExtra = '';
	if (isset($_REQUEST['cat']))
        $catExtra = ';cat=' . (int) $_REQUEST['cat'];

	// Add Picture
	if ($g_add)
	$context['gallery']['buttons']['add'] =  array(
		'text' => 'gallery_form_addpicture',
		'url' => $scripturl . '?action=gallery;sa=add' . $catExtra,
		'lang' => true,
		'image' => '',

	);

	// MyImages
	if ($g_add && !($context['user']['is_guest']))
	$context['gallery']['buttons']['mylisting'] =  array(
		'text' => 'gallery_myimages',
		'url' =>$scripturl . '?action=gallery;sa=myimages;u=' . $ID_MEMBER,
		'lang' => true,
		'image' => '',

	);

	// Search
	$context['gallery']['buttons']['search'] =  array(
		'text' => 'gallery_search',
		'url' => $scripturl . '?action=gallery;sa=search',
		'lang' => true,
		'image' => '',

	);

	// Link Tree
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=gallery',
					'name' => $txt['gallery_text_title']
				);
}


function ReGenerateThumbnails()
{
	global $context, $mbname, $txt, $db_prefix;

	@$cat = (int) $_REQUEST['cat'];

	if (empty($cat))
		fatal_error($txt['gallery_error_no_cat']);

	isAllowedTo('smfgallery_manage');


	$dbresult1 = db_query("
		SELECT
			title
		FROM {$db_prefix}gallery_cat
		WHERE ID_CAT = $cat", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult1);
		$context['gallery_cat_name'] = $row['title'];
	mysql_free_result($dbresult1);

		$context['catid'] = $cat;



	$context['sub_template']  = 'regenerate';

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_regeneratethumbnails2'];

    $context['linktree'][] = array(
			'name' =>  $txt['gallery_text_regeneratethumbnails2']
		);

}

function ReGenerateThumbnails2()
{
	global $db_prefix, $txt, $modSettings, $gd2, $sourcedir, $context;

	$id = (int) $_REQUEST['id'];


	if (empty($id))
		return;

	isAllowedTo('smfgallery_manage');
	$catWhere = '';

	$context['catid'] = $id;

	$catWhere = " ID_CAT = $id";


	// Check if gallery path is writable
	if (!is_writable($modSettings['gallery_path']))
		fatal_error($txt['gallery_write_error'] . $modSettings['gallery_path']);



	// Increase the max time to process the images
	@ini_set('max_execution_time', '900');

	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);

	require_once($sourcedir . '/Subs-Graphics.php');

	$context['start'] = empty($_REQUEST['start']) ? 25 : (int) $_REQUEST['start'];

	$request = db_query("
	SELECT
		COUNT(*)
	FROM {$db_prefix}gallery_pic
	WHERE $catWhere", __FILE__, __LINE__);
	list($totalProcess) = mysql_Fetch_row($request);
	mysql_free_result($request);

	// Initialize the variables.
	$increment = 25;
	if (empty($_REQUEST['start']))
		$_REQUEST['start'] = 0;

	$_REQUEST['start'] = (int) $_REQUEST['start'];

	$dbresult = db_query("
	SELECT
		filename, ID_PICTURE
	FROM {$db_prefix}gallery_pic
	WHERE $catWhere LIMIT " . $_REQUEST['start'] . ","  . ($increment), __FILE__, __LINE__);
	$counter = 0;
	$gallery_pics = array();
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$gallery_pics[] = $row;
	}
	mysql_free_result($dbresult);

	foreach($gallery_pics as $row)
	{
		$filename = $row['filename'];
		$extra_path = '';



		$thumbnailPath = '';

		createThumbnail($modSettings['gallery_path'] . $extra_path .  $filename, $modSettings['gallery_thumb_width'], $modSettings['gallery_thumb_height']);
		@unlink($modSettings['gallery_path'] . $extra_path . 'thumb_' . $filename);
		rename($modSettings['gallery_path'] . $extra_path .  $filename . '_thumb',  $modSettings['gallery_path']  . $extra_path . 'thumb_' . $filename);
		@chmod($modSettings['gallery_path'] . $extra_path  .  'thumb_' . $filename, 0755);
		$thumbnailPath = $extra_path  .  'thumb_' . $filename;

		db_query("UPDATE {$db_prefix}gallery_pic SET thumbfilename = '$thumbnailPath'
					WHERE ID_PICTURE = " . $row['ID_PICTURE'], __FILE__, __LINE__);


		$counter++;
	}

	$_REQUEST['start'] += $increment;

	$complete = 0;
	if($_REQUEST['start'] < $totalProcess)
	{

		$context['continue_get_data'] = 'start=' . $_REQUEST['start'];
		$context['continue_percent'] = round(100 * $_REQUEST['start'] / $totalProcess);


	}
	else
		$complete = 1;




	// Redirect back to the category
	if ($complete == 1)
		redirectexit('action=gallery;cat=' .  $id);
	else
	{
		$context['sub_template']  = 'regenerate2';

		$context['page_title'] =  $txt['gallery_text_title'] . ' - ' . $txt['gallery_text_regeneratethumbnails2'];

	}


}


function PreviousImage($id = 0, $picCat = 0, $return = false)
{
	global $db_prefix, $txt;

	if (empty($id))
		$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected'],false);

	// Get the category
	if (empty($picCat) )
	{
		$dbresult = db_query("
		SELECT
			p.ID_PICTURE, p.ID_CAT
		FROM {$db_prefix}gallery_pic as p
		LEFT JOIN {$db_prefix}gallery_cat as c ON (p.ID_CAT = c.ID_CAT)
		WHERE p.ID_PICTURE = $id  LIMIT 1", __FILE__, __LINE__);

		if (mysql_num_rows($dbresult) == 0)
			fatal_error($txt['gallery_error_no_pic_selected'],false);

		$row = mysql_fetch_assoc($dbresult);
		$ID_CAT = $row['ID_CAT'];


		mysql_free_result($dbresult);
	}
	else
	{
		$ID_CAT = $picCat;

	}

	//if ($sortcat == '')
		$sortcat = 'p.ID_PICTURE';

	$ordersign = '>';

	//if ($ordercat == '')
		$ordercat = 'ASC';



	// Get previous image

	$dbresult = db_query("
	SELECT
		p.ID_PICTURE
	FROM {$db_prefix}gallery_pic as p
	WHERE p.ID_CAT = $ID_CAT AND  p.approved = 1 AND p.ID_PICTURE $ordersign $id ORDER BY $sortcat $ordercat  LIMIT 1", __FILE__, __LINE__);
	if(db_affected_rows() != 0)
	{
		$row = mysql_fetch_assoc($dbresult);
		$ID_PICTURE = $row['ID_PICTURE'];
	}
	else
		$ID_PICTURE = $id;

	mysql_free_result($dbresult);
	if ($return == false)
		redirectexit('action=gallery;sa=view&id=' . $ID_PICTURE);
	else
		return $ID_PICTURE;
}

function NextImage($id = 0, $picCat = 0, $return = false)
{
	global $db_prefix, $txt;

	if (empty($id))
		$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['gallery_error_no_pic_selected'],false);

	// Get the category
	if(empty($picCat))
	{
		$dbresult = db_query("
		SELECT
			p.ID_PICTURE, p.ID_CAT
		FROM {$db_prefix}gallery_pic as p
		LEFT JOIN {$db_prefix}gallery_cat as c ON (p.ID_CAT = c.ID_CAT)
		WHERE p.ID_PICTURE = $id  LIMIT 1", __FILE__, __LINE__);

		if (mysql_num_rows($dbresult) == 0)
			fatal_error($txt['gallery_error_no_pic_selected'],false);

		$row = mysql_fetch_assoc($dbresult);
		$ID_CAT = $row['ID_CAT'];


		mysql_free_result($dbresult);
	}
	else
	{
		$ID_CAT = $picCat;

	}

	//if ($sortcat == '')
		$sortcat = 'p.ID_PICTURE';

	//if ($ordercat == '')
		$ordercat = 'DESC';

	$ordersign = '<';

	// Get next image

	$dbresult = db_query("
	SELECT
		p.ID_PICTURE
	FROM {$db_prefix}gallery_pic as p
	WHERE
	p.ID_CAT = $ID_CAT AND   p.approved = 1 AND  p.ID_PICTURE $ordersign $id ORDER BY $sortcat $ordercat LIMIT 1", __FILE__, __LINE__);
	if (db_affected_rows() != 0)
	{
		$row = mysql_fetch_assoc($dbresult);
		$ID_PICTURE = $row['ID_PICTURE'];
	}
	else
		$ID_PICTURE = $id;
	mysql_free_result($dbresult);

	if ($return == false)
		redirectexit('action=gallery;sa=view&id=' . $ID_PICTURE);
	else
		return $ID_PICTURE;
}

function GalleryCheckBadgeAwards($memID = 0)
{
	global $sourcedir, $modSettings;

	if (!empty($modSettings['badgeawards_enable']))
	{

		require_once($sourcedir . '/badgeawards.php');
		Badges_CheckMember($memID);
	}
}

function GalleryCheckInfo()
{
    global $modSettings, $boardurl;

    if (isset($modSettings['gallery_copyrightkey']))
    {
        $m = 19;
        if (!empty($modSettings['gallery_copyrightkey']))
        {
            if ($modSettings['gallery_copyrightkey'] == sha1($m . '-' . $boardurl))
            {
                return false;
            }
            else
                return true;
        }
    }

    return true;
}

function Gallery_CopyrightRemoval()
{
    global $context, $mbname, $txt;
	isAllowedTo('smfgallery_manage');

    if (isset($_REQUEST['save']))
    {

        $gallery_copyrightkey = addslashes($_REQUEST['gallery_copyrightkey']);

        updateSettings(
    	array(
    	'gallery_copyrightkey' => $gallery_copyrightkey,
    	)

    	);
    }


	DoGalleryAdminTabs();
    adminIndex('gallery_settings');

	$context['page_title'] = $mbname . ' - ' . $txt['gallery_text_title'] . ' - ' . $txt['gallery_txt_copyrightremoval'];

	$context['sub_template']  = 'gallerycopyright';
}

function isAevaInstalled()
{
    global $sourcedir;

    if (file_exists($sourcedir . '/Aeva-Subs.php'))
        return true;
    else
        return false;
}

function ConvertGallery()
{
	global $context, $mbname, $txt, $sourcedir;
	isAllowedTo('smfgallery_manage');

	adminIndex('gallery_settings');

	DoGalleryAdminTabs();

	if (isset($_REQUEST['convertavea']) || isset($_REQUEST['importstep']))
    {
        require_once($sourcedir . '/Subs-ConvertAeva.php');
        GalleryAevaImportMain();
    }
    else
    {

        $context['page_title'] = $txt['gallery_txt_convertors'];

    	$context['sub_template']  = 'convertgallery';
    }




}
?>