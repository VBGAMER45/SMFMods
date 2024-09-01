<?php
/*
Download System
Version 2.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2008-2021 SMFHacks.com

############################################
License Information:

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################

Download System Function Information:

	void DownloadsMain()
	void main()
	void AddCategory()
	void AddCategory2()
	void EditCategory()
	void EditCategory2()
	void DeleteCategory()
	void DeleteCategory2()
	void ViewDownload()
	void AddDownload()
	void AddDownload2()
	void EditDownload()
	void EditDownload2()
	void DeleteDownload();
	void DeleteDownload2()
	void ReportDownload()
	void ReportDownload2()
	void AddComment()
	void AddComment2()
	void DeleteComment()
	void AdminSettings()
	void AdminSettings2()
	void CatUp()
	void CatDown()
	void MyFiles()
	void RateDownload()
	void ViewRating()
	void DeleteRating()
	void Stats()
	void UpdateUserFileSizeTable($memberid, $filesize = 0)
	void FileSpaceAdmin()
	void FileSpaceList()
	void RecountFileQuotaTotals($redirect = true)


*/

if (!defined('SMF'))
	die('Hacking attempt...');

function DownloadsMain()
{
	global $boardurl, $modSettings, $boarddir, $currentVersion;

	$currentVersion = '3.0.14';

	if (empty($modSettings['down_url']))
		$modSettings['down_url'] = $boardurl . '/downloads/';

	if (empty($modSettings['down_path']))
		$modSettings['down_path'] = $boarddir . '/downloads/';


	// Load the language files
	if (loadlanguage('Downloads') == false)
		loadLanguage('Downloads','english');

	// Load the main template file
	loadtemplate('Downloads');

	TopDownloadTabs();

	// Download Actions pretty big array heh
	$subActions = array(
		'view' => 'Downloads_ViewDownload',
		'bulkactions' => 'Downloads_BulkActions',
		'adminset'=> 'Downloads_AdminSettings',
		'adminset2'=> 'Downloads_AdminSettings2',
		'delete' => 'Downloads_DeleteDownload',
		'delete2' => 'Downloads_DeleteDownload2',
		'edit' => 'Downloads_EditDownload',
		'edit2' => 'Downloads_EditDownload2',
		'report' => 'Downloads_ReportDownload',
		'report2' => 'Downloads_ReportDownload2',
		'deletereport' => 'Downloads_DeleteReport',
		'reportlist' => 'Downloads_ReportList',
		'comment' => 'Downloads_AddComment',
		'comment2' => 'Downloads_AddComment2',
		'editcomment' => 'Downloads_EditComment',
		'editcomment2' => 'Downloads_EditComment2',
		'apprcomment' => 'Downloads_ApproveComment',
		'apprcomall' => 'Downloads_ApproveAllComments',
		'reportcomment' => 'Downloads_ReportComment',
		'reportcomment2' => 'Downloads_ReportComment2',
		'delcomment' => 'Downloads_DeleteComment',
		'delcomreport' => 'Downloads_DeleteCommentReport',
		'commentlist' => 'Downloads_CommentList',
		'rate' => 'Downloads_RateDownload',
		'viewrating' => 'Downloads_ViewRating',
		'delrating' => 'Downloads_DeleteRating',
		'catup' => 'Downloads_CatUp',
		'catdown' => 'Downloads_CatDown',
		'catperm' => 'Downloads_CatPerm',
		'catperm2' => 'Downloads_CatPerm2',
		'catpermlist' => 'Downloads_CatPermList',
		'catpermdelete' => 'Downloads_CatPermDelete',
		'catimgdel' => 'Downloads_CatImageDelete',
		'addcat' => 'Downloads_AddCategory',
		'addcat2' => 'Downloads_AddCategory2',
		'editcat' => 'Downloads_EditCategory',
		'editcat2' => 'Downloads_EditCategory2',
		'deletecat' => 'Downloads_DeleteCategory',
		'deletecat2' => 'Downloads_DeleteCategory2',
		'viewc' => 'Downloads_ViewC',
		'myfiles' => 'Downloads_MyFiles',
		'approvelist' => 'Downloads_ApproveList',
		'approve' => 'Downloads_ApproveDownload',
		'unapprove' => 'Downloads_UnApproveDownload',
		'add' => 'Downloads_AddDownload',
		'add2' => 'Downloads_AddDownload2',
		'search' => 'Downloads_Search',
		'search2' => 'Downloads_Search2',
		'stats' => 'Downloads_Stats',
		'filespace' => 'Downloads_FileSpaceAdmin',
		'filelist' => 'Downloads_FileSpaceList',
		'recountquota' => 'Downloads_RecountFileQuotaTotals',
		'addquota' => 'Downloads_AddQuota',
		'deletequota' => 'Downloads_DeleteQuota',
		'next' => 'Downloads_NextDownload',
		'prev' => 'Downloads_PreviousDownload',
		'cusup' => 'Downloads_CustomUp',
		'cusdown' => 'Downloads_CustomDown',
		'cusadd' => 'Downloads_CustomAdd',
		'cusdelete' => 'Downloads_CustomDelete',
		'downfile' => 'Downloads_DownloadFile',
		'import' => 'Downloads_ImportDownloads',
		'importtp' => 'Downloads_ImportTinyPortalDownloads',

	);


	// Follow the sa or just go to  the main function
	if (isset($_GET['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';

	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		Downloads_MainView();

}

function Downloads_MainView()
{
	global $context, $scripturl, $mbname, $txt, $db_prefix, $modSettings, $user_info;




	// View the main Downloads

	// Is the user allowed to view the downloads?
	isAllowedTo('downloads_view');

	// Load the main downloads template
	$context['sub_template']  = 'mainview';


	// Get the main groupid
	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

	if (isset($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];
	else
		$cat = 0;

	if (!empty($cat))
	{

		// Check the permission
		Downloads_GetCatPermission($cat,'view');

		// Get category name used for the page title
		$dbresult1 = db_query("
		SELECT
			ID_CAT, title, roworder, description, image,
			disablerating, orderby, sortby,ID_PARENT
		FROM {$db_prefix}down_cat
		WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
		$row1 = mysql_fetch_assoc($dbresult1);

		if (empty($row1['ID_CAT']))
			fatal_error($txt['downloads_error_no_cat'],false);+

		$context['downloads_cat_name'] = $row1['title'];
		$context['downloads_sortby'] = $row1['sortby'];
		$context['downloads_orderby'] = $row1['orderby'];
		$context['downloads_cat_norate'] = $row1['disablerating'];
		if ($context['downloads_cat_norate'] == '')
			$context['downloads_cat_norate'] = 0;



		mysql_free_result($dbresult1);

		Downloads_GetParentLink($row1['ID_PARENT']);

		// Link Tree
		$context['linktree'][] = array(
					'url' => $scripturl . '?action=downloads;cat=' . $cat,
					'name' => $context['downloads_cat_name']
				);

		// Set the page title
		$context['page_title'] = $mbname . ' - ' . $context['downloads_cat_name'];

		// Get the total number of pages
		$total = Downloads_GetTotalByCATID($cat);


		$context['start'] = (int) $_REQUEST['start'];

		$context['downloads_total'] = $total;


		// Check if we are sorting stuff heh
		$sortby = '';
		$orderby = '';
		if (isset($_REQUEST['sortby']))
		{
			switch ($_REQUEST['sortby'])
			{
				case 'date':
					$sortby = 'p.ID_FILE';

				break;
				case 'title':
					$sortby = 'p.title';
				break;

				case 'mostview':
					$sortby = 'p.views';
				break;

				case 'mostdowns':
					$sortby = 'p.totaldownloads';
				break;
				case 'filesize':
					$sortby = 'p.filesize';
				break;
				case 'membername':
					$sortby = 'm.realname';
				break;

				case 'mostcom':
					$sortby = 'p.commenttotal';
				break;

				case 'mostrated':
					$sortby = 'p.totalratings';
				break;


				default:
					$sortby = 'p.ID_FILE';
				break;
			}

			$sortby2 = $_REQUEST['sortby'];

			$context['downloads_sortby'] = $sortby2;
		}
		else
		{
			if (!empty($context['downloads_sortby']))
				$sortby = $context['downloads_sortby'];
			else
				$sortby = 'p.ID_FILE';

			$sortby2 = 'date';

			$context['downloads_sortby'] = $sortby2;
		}


		if (isset($_REQUEST['orderby']))
		{
			switch ($_REQUEST['orderby'])
			{
				case 'asc':
					$orderby = 'ASC';

				break;
				case 'desc':
					$orderby = 'DESC';
				break;



				default:
					$orderby = 'DESC';
				break;
			}

			$orderby2 = $_REQUEST['orderby'];

			$context['downloads_orderby2'] = $orderby2;
		}
		else
		{

			if (!empty($context['downloads_orderby']))
				$orderby = $context['downloads_orderby'];
			else
				$orderby = 'DESC';

			$orderby2 = 'desc';

			$context['downloads_orderby2'] = $orderby2;
		}


		// Show the downloads
		$dbresult = db_query("
		SELECT
			p.ID_FILE, p.totalratings, p.rating, p.commenttotal,
		 	p.filesize, p.views, p.title, p.ID_MEMBER, m.realName,
		 	 p.date, p.description, p.totaldownloads
		FROM {$db_prefix}down_file as p
			LEFT JOIN {$db_prefix}members AS m ON (p.ID_MEMBER = m.ID_MEMBER)
		WHERE  p.ID_CAT = $cat AND p.approved = 1
		ORDER BY $sortby $orderby
		LIMIT $context[start]," . $modSettings['down_set_files_per_page'], __FILE__, __LINE__);
		$context['downloads_files'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_files'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'totalratings' => $row['totalratings'],
			'rating' => $row['rating'],
			'commenttotal' => $row['commenttotal'],
			'filesize' => $row['filesize'],
			'views' => $row['views'],
			'ID_MEMBER' => $row['ID_MEMBER'],
			'realName' => $row['realName'],
			'date' => $row['date'],
			'description' => $row['description'],
			'totaldownloads' => $row['totaldownloads'],

			);

		}
		mysql_free_result($dbresult);



		$context['page_index'] = constructPageIndex($scripturl . '?action=downloads;cat=' . $cat . ';sortby=' . $context['downloads_sortby'] . ';orderby=' . $context['downloads_orderby2'], $_REQUEST['start'], $total, $modSettings['down_set_files_per_page']);




		if (!empty($modSettings['down_who_viewing']))
		{
			$context['can_moderate_forum'] = allowedTo('moderate_forum');

				// SMF 1.1.x
				// Taken from Display.php
				// Start out with no one at all viewing it.
				$context['view_members'] = array();
				$context['view_members_list'] = array();
				$context['view_num_hidden'] = 0;

				$whoID = (string) $cat;

				// Search for members who have this downloads id set in their GET data.
				$request = db_query("
					SELECT
						lo.ID_MEMBER, lo.logTime, mem.realName, mem.memberName, mem.showOnline,
						mg.onlineColor, mg.ID_GROUP, mg.groupName
					FROM {$db_prefix}log_online AS lo
						LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = lo.ID_MEMBER)
						LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
					WHERE INSTR(lo.url, 's:9:\"downloads\";s:3:\"cat\";s:" . strlen($whoID ) .":\"$cat\";') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'", __FILE__, __LINE__);
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
		$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'];

	$dbresult = db_query("
	SELECT
		c.ID_CAT, c.title, p.view, c.roworder, c.description, c.image, c.filename, c.redirect
	FROM {$db_prefix}down_cat AS c
	LEFT JOIN {$db_prefix}down_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
	WHERE c.ID_PARENT = 0 ORDER BY c.roworder ASC", __FILE__, __LINE__);
	$context['downloads_cats'] = array();
	while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_cats'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'view' => $row['view'],
			'roworder' => $row['roworder'],
			'description' => $row['description'],
			'filename' => $row['filename'],
			'redirect' => $row['redirect'],
			'image' => $row['image'],
			);

		}
		mysql_free_result($dbresult);


		// Downloads waiting for approval
		$dbresult3 = db_query("
		SELECT
			COUNT(*) as totalfiles
		FROM {$db_prefix}down_file
		WHERE approved = 0", __FILE__, __LINE__);
		$row2 = mysql_fetch_assoc($dbresult3);
		$totalfiles = $row2['totalfiles'];
		mysql_free_result($dbresult3);
		$context['downloads_waitapproval'] = $totalfiles;
		// Reported Downloads
		$dbresult4 = db_query("
		SELECT
			COUNT(*) as totalreport
		FROM {$db_prefix}down_report", __FILE__, __LINE__);
		$row2 = mysql_fetch_assoc($dbresult4);
		$totalreport = $row2['totalreport'];
		mysql_free_result($dbresult4);
		$context['downloads_totalreport'] = $totalreport;

		// Total Comments Rating for Approval
		$dbresult5 = db_query("
		SELECT
			COUNT(*) as totalcom
		FROM {$db_prefix}down_comment
		WHERE approved = 0", __FILE__, __LINE__);
		$row2 = mysql_fetch_assoc($dbresult5);
		$totalcomments = $row2['totalcom'];
		mysql_free_result($dbresult5);
		$context['downloads_totalcom'] = $totalcomments;

		// Total reported Comments
		$dbresult6 = db_query("
		SELECT
			COUNT(*) as totalcreport
		FROM {$db_prefix}down_creport", __FILE__, __LINE__);
		$row2 = mysql_fetch_assoc($dbresult6);
		$totalcomments = $row2['totalcreport'];
		mysql_free_result($dbresult6);
		$context['downloads_totalcreport'] = $totalcomments;

	}


}

function Downloads_AddCategory()
{
	global $context, $mbname, $txt, $modSettings, $db_prefix, $sourcedir;

	isAllowedTo('downloads_manage');

	adminIndex('downloads_settings');

	// Show the boards where the user can select to post in.
	$context['downloads_boards'] = array('');
	$request = db_query("SELECT b.ID_BOARD, b.name AS bName, c.name AS cName FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c WHERE b.ID_CAT = c.ID_CAT ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$context['downloads_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	mysql_free_result($request);

	 $dbresult = db_query("
	 SELECT
	 	c.ID_CAT, c.title,c.roworder
	 FROM {$db_prefix}down_cat AS c
	 ORDER BY c.roworder ASC", __FILE__, __LINE__);
	$context['downloads_cat'] = array();
	 while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_cat'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'roworder' => $row['roworder'],
			);
		}
	mysql_free_result($dbresult);

	if (isset($_REQUEST['cat']))
		$parent  = (int) $_REQUEST['cat'];
	else
		$parent = 0;

	$context['cat_parent'] = $parent;


	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_addcategory'];

	$context['sub_template']  = 'add_category';

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');
	$context['post_box_name'] = 'description';
	$context['post_form'] = 'catform';

}

function Downloads_AddCategory2()
{
	global $db_prefix, $txt, $scripturl, $sourcedir, $modSettings, $boarddir, $func;

	isAllowedTo('downloads_manage');

	// Get the category information and clean the input for bad stuff
	$title = $func['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
	$description = $func['htmlspecialchars']($_REQUEST['description'],ENT_QUOTES);
	$image =  htmlspecialchars($_REQUEST['image'],ENT_QUOTES);
	$boardselect = (int) $_REQUEST['boardselect'];
	$parent = (int) $_REQUEST['parent'];


	$locktopic = isset($_REQUEST['locktopic']) ? 1 : 0;
	$disablerating  = isset($_REQUEST['disablerating']) ? 1 : 0;


	// Title is required for a category
	if (empty($title))
		fatal_error($txt['downloads_error_cat_title'],false);


		$sortby = '';
		$orderby = '';
		if (isset($_REQUEST['sortby']))
		{
			switch ($_REQUEST['sortby'])
			{
				case 'date':
					$sortby = 'p.ID_FILE';

				break;
				case 'title':
					$sortby = 'p.title';
				break;

				case 'mostview':
					$sortby = 'p.views';
				break;

				case 'mostcom':
					$sortby = 'p.commenttotal';
				break;

				case 'mostrated':
					$sortby = 'p.totalratings';
				break;

				case 'mostdowns':
					$sortby = 'p.totaldownloads';
				break;
				case 'filesize':
					$sortby = 'p.filesize';
				break;
				case 'membername':
					$sortby = 'm.realname';
				break;


				default:
					$sortby = 'p.ID_FILE';
				break;
			}

		}
		else
		{
			$sortby = 'p.ID_FILE';
		}


		if (isset($_REQUEST['orderby']))
		{
			switch ($_REQUEST['orderby'])
			{
				case 'asc':
					$orderby = 'ASC';

				break;
				case 'desc':
					$orderby = 'DESC';
				break;

				default:
					$orderby = 'DESC';
				break;
			}
		}
		else
		{
			$orderby = 'DESC';
		}

	// Do the order
	$dbresult = db_query("
	SELECT
		MAX(roworder) as catorder
	FROM {$db_prefix}down_cat
	WHERE ID_PARENT = $parent
	ORDER BY roworder DESC", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);

	if (db_affected_rows()== 0)
		$order = 0;
	else
		$order = $row['catorder'];
	$order++;



	// Insert the category
	db_query("INSERT INTO {$db_prefix}down_cat
			(title, description,roworder,image,ID_BOARD,ID_PARENT,disablerating,locktopic,sortby,orderby)
		VALUES ('$title', '$description',$order,'$image',$boardselect,$parent,$disablerating,$locktopic,'$sortby','$orderby')", __FILE__, __LINE__);
	mysql_free_result($dbresult);

	// Get the Category ID
	$cat_id = db_insert_id();


	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);

	// Upload Category image File
	if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
	{

		$sizes = @getimagesize($_FILES['picture']['tmp_name']);

			// No size, then it's probably not a valid pic.
			if ($sizes === false)
				fatal_error($txt['downloads_error_invalid_picture'],false);

			require_once($sourcedir . '/Subs-Graphics.php');

			if ((!empty($modSettings['down_set_cat_width']) && $sizes[0] > $modSettings['down_set_cat_width']) || (!empty($modSettings['down_set_cat_height']) && $sizes[1] > $modSettings['down_set_cat_height']))
			{

					// Delete the temp file
					@unlink($_FILES['picture']['tmp_name']);
					fatal_error($txt['downloads_error_img_size_height'] . $sizes[1] . $txt['downloads_error_img_size_width'] . $sizes[0],false);

			}

		// Move the file
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


		$filename = $cat_id . '.' . $extension;

		move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['down_path'] . 'catimgs/' . $filename);
		@chmod($modSettings['down_path'] . 'catimgs/' . $filename, 0644);

		// Update the filename for the category
		db_query("UPDATE {$db_prefix}down_cat
		SET filename = '$filename' WHERE ID_CAT = $cat_id LIMIT 1", __FILE__, __LINE__);


	}


	// Redirect to the category listing
	redirectexit('action=downloads;sa=admincat');
}

function Downloads_ViewC()
{
	die(base64_decode('RG93bmxvYWRzIFN5c3RlbSBieSB2YmdhbWVyNDUgaHR0cDovL3d3dy5zbWZoYWNrcy5jb20='));
}

function Downloads_EditCategory()
{
	global $context, $mbname, $txt, $modSettings, $db_prefix, $sourcedir;
	isAllowedTo('downloads_manage');

	adminIndex('downloads_settings');

	$cat = (int) $_REQUEST['cat'];
	if (empty($cat))
		fatal_error($txt['downloads_error_no_cat']);

	$context['downloads_boards'] = array('');
	$request = db_query("
	SELECT
		b.ID_BOARD, b.name AS bName, c.name AS cName
	FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c
	WHERE b.ID_CAT = c.ID_CAT ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
		$context['downloads_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	mysql_free_result($request);

	$dbresult = db_query("
	SELECT
		ID_CAT, title,roworder
	FROM {$db_prefix}down_cat
	ORDER BY roworder ASC", __FILE__, __LINE__);
	$context['downloads_cat'] = array();
	 while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_cat'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'roworder' => $row['roworder'],
			);
		}
	mysql_free_result($dbresult);

	$dbresult = db_query("
	SELECT
		ID_CAT, title, image, filename, description,ID_BOARD,
		ID_PARENT,disablerating, redirect, showpostlink, locktopic, sortby, orderby
	FROM {$db_prefix}down_cat
	WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);

	$row = mysql_fetch_assoc($dbresult);
			$context['down_catinfo'] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'image' => $row['image'],
			'filename' => $row['filename'],
			'description' => $row['description'],
			'ID_BOARD' => $row['ID_BOARD'],
			'ID_PARENT' => $row['ID_PARENT'],
			'disablerating' => $row['disablerating'],
			'redirect' => $row['redirect'],
			'showpostlink' => $row['showpostlink'],
			'locktopic' => $row['locktopic'],
			'sortby' => $row['sortby'],
			'orderby' => $row['orderby'],
			);
	mysql_free_result($dbresult);

	if (empty($row['ID_CAT']))
		fatal_error($txt['downloads_error_no_cat'],false);

	// Get all the custom fields
	$dbresult = db_query("
	SELECT
		title, defaultvalue, is_required, ID_CUSTOM
	FROM  {$db_prefix}down_custom_field
	WHERE ID_CAT = " . $context['down_catinfo']['ID_CAT'] . "
	ORDER BY roworder desc", __FILE__, __LINE__);
	$context['down_custom'] = array();
	while($row = mysql_fetch_assoc($dbresult))
	{
			$context['down_custom'][] = array(
			'title' => $row['title'],
			'ID_CUSTOM' => $row['ID_CUSTOM'],
			'defaultvalue' => $row['defaultvalue'],
			'is_required' => $row['is_required'],

			);
	}
	mysql_free_result($dbresult);


	$context['catid'] = $cat;

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_editcategory'];
	// Load the edit category subtemplate
	$context['sub_template']  = 'edit_category';

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');
	$context['post_box_name'] = 'description';
	$context['post_form'] = 'catform';


}

function Downloads_EditCategory2()
{
	global $db_prefix, $txt, $scripturl, $modSettings,$sourcedir, $func;

	isAllowedTo('downloads_manage');

	// Clean the input
	$title = $func['htmlspecialchars']($_REQUEST['title'], ENT_QUOTES);
	$description = $func['htmlspecialchars']($_REQUEST['description'], ENT_QUOTES);
	$catid = (int) $_REQUEST['catid'];
	$image = htmlspecialchars($_REQUEST['image'], ENT_QUOTES);
	$parent = (int) $_REQUEST['parent'];

	$boardselect = (int) $_REQUEST['boardselect'];

	$locktopic = isset($_REQUEST['locktopic']) ? 1 : 0;
	$disablerating  = isset($_REQUEST['disablerating']) ? 1 : 0;


	// The category field requires a title
	if (empty($title))
		fatal_error($txt['downloads_error_cat_title'],false);

		$sortby = '';
		$orderby = '';
		if (isset($_REQUEST['sortby']))
		{
			switch ($_REQUEST['sortby'])
			{
				case 'date':
					$sortby = 'p.ID_FILE';

				break;
				case 'title':
					$sortby = 'p.title';
				break;

				case 'mostview':
					$sortby = 'p.views';
				break;

				case 'mostcom':
					$sortby = 'p.commenttotal';
				break;

				case 'mostrated':
					$sortby = 'p.totalratings';
				break;

				case 'mostdowns':
					$sortby = 'p.totaldownloads';
				break;
				case 'filesize':
					$sortby = 'p.filesize';
				break;
				case 'membername':
					$sortby = 'm.realname';
				break;

				default:
					$sortby = 'p.ID_FILE';
				break;
			}

		}
		else
		{
			$sortby = 'p.ID_FILE';
		}


		if (isset($_REQUEST['orderby']))
		{
			switch ($_REQUEST['orderby'])
			{
				case 'asc':
					$orderby = 'ASC';

				break;
				case 'desc':
					$orderby = 'DESC';
				break;

				default:
					$orderby = 'DESC';
				break;
			}
		}
		else
		{
			$orderby = 'DESC';
		}

	// Update the category
	db_query("UPDATE {$db_prefix}down_cat
		SET title = '$title', image = '$image', description = '$description', ID_BOARD = $boardselect,
		ID_PARENT = $parent, disablerating = $disablerating, locktopic = $locktopic,
		orderby = '$orderby', sortby = '$sortby'
		WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);


	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);

	// Upload Category image File
	if (isset($_FILES['picture']['name']) && $_FILES['picture']['name'] != '')
	{
		$sizes = @getimagesize($_FILES['picture']['tmp_name']);

			// No size, then it's probably not a valid pic.
			if ($sizes === false)
				fatal_error($txt['downloads_error_invalid_picture'],false);

			require_once($sourcedir . '/Subs-Graphics.php');

			if ((!empty($modSettings['down_set_cat_width']) && $sizes[0] > $modSettings['down_set_cat_width']) || (!empty($modSettings['down_set_cat_height']) && $sizes[1] > $modSettings['down_set_cat_height']))
			{

				// Delete the temp file
				@unlink($_FILES['picture']['tmp_name']);
				fatal_error($txt['downloads_error_img_size_height'] . $sizes[1] . $txt['downloads_error_img_size_width'] . $sizes[0],false);

			}
		// Move the file
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


		$filename = $catid . '.' . $extension;

		move_uploaded_file($_FILES['picture']['tmp_name'], $modSettings['down_path'] . 'catimgs/' . $filename);
		@chmod($modSettings['down_path'] . 'catimgs/' . $filename, 0644);


		// Update the filename for the category
		db_query("UPDATE {$db_prefix}down_cat
		SET filename = '$filename' WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);


	}


	redirectexit('action=downloads;sa=admincat');

}
function Downloads_DeleteCategory()
{
	global $context, $mbname, $txt, $db_prefix;

	isAllowedTo('downloads_manage');

	adminIndex('downloads_settings');

	$catid = (int) $_REQUEST['cat'];

	if (empty($catid))
		fatal_error($txt['downloads_error_no_cat']);

	$context['catid'] = $catid;

	// Lookup the category to get its name
	$dbresult = db_query("
	SELECT
		ID_CAT, title
	FROM {$db_prefix}down_cat
	WHERE ID_CAT = $catid", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$context['cat_title'] = $row['title'];
	mysql_free_result($dbresult);

	// Get total files in the category
	$dbresult2 = db_query("
	SELECT
		COUNT(*) as totalfiles
	FROM {$db_prefix}down_file
	WHERE ID_CAT = $catid AND approved = 1", __FILE__, __LINE__);
	$row2 = mysql_fetch_assoc($dbresult2);
	$context['totalfiles'] = $row2['totalfiles'];
	mysql_free_result($dbresult2);

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_delcategory'];

	$context['sub_template']  = 'delete_category';
}

function Downloads_DeleteCategory2()
{
	global $db_prefix, $modSettings, $boarddir, $scripturl;

	isAllowedTo('downloads_manage');

	$catid = (int) $_REQUEST['catid'];
	// Increase the max time just in case it takes a long to delete the category and files.
	@ini_set('max_execution_time', '300');
	$dbresult = db_query("
	SELECT
		ID_FILE, filename
	FROM {$db_prefix}down_file
	WHERE ID_CAT = $catid", __FILE__, __LINE__);

	while($row = mysql_fetch_assoc($dbresult))
	{
		// Delete Files
		// Delete the download
		@unlink($modSettings['down_path'] . $row['filename']);
		db_query("DELETE FROM {$db_prefix}down_comment WHERE ID_FILE  = " . $row['ID_FILE'], __FILE__, __LINE__);
		db_query("DELETE FROM {$db_prefix}down_rating WHERE ID_FILE  = " . $row['ID_FILE'], __FILE__, __LINE__);
		db_query("DELETE FROM {$db_prefix}down_report WHERE ID_FILE  = " . $row['ID_FILE'], __FILE__, __LINE__);
		db_query("DELETE FROM {$db_prefix}down_creport WHERE ID_FILE  = " . $row['ID_FILE'], __FILE__, __LINE__);
	}
	mysql_free_result($dbresult);
	// Update Category parent
	db_query("UPDATE {$db_prefix}down_cat SET ID_PARENT = 0 WHERE ID_PARENT = $catid", __FILE__, __LINE__);

	// Delete All Files
	db_query("DELETE FROM {$db_prefix}down_file WHERE ID_CAT = $catid", __FILE__, __LINE__);

	// Finally delete the category
	db_query("DELETE FROM {$db_prefix}down_cat WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);

	// Last Recount the totals
	Downloads_RecountFileQuotaTotals(false);

	redirectexit('action=downloads;sa=admincat');
}

function Downloads_ViewDownload()
{
	global $context, $mbname, $db_prefix, $modSettings, $user_info, $scripturl, $txt, $ID_MEMBER;

	isAllowedTo('downloads_view');



	if (isset($_REQUEST['down']))
		$id = (int) $_REQUEST['down'];

	if (isset($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];
	// Get the file id
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected'], false);



	// Get the download information
	$dbresult = db_query("
	SELECT
		p.ID_FILE, p.totalratings, p.rating, p.allowcomments, p.ID_CAT, p.keywords,
		p.commenttotal, p.filesize, p.filename, p.orginalfilename, p.fileurl,
	 	p.approved, p.views, p.title, p.ID_MEMBER, m.realName, p.date, p.description,
	   	c.title CAT_TITLE, c.ID_PARENT, c.disablerating, p.credits, p.totaldownloads,  p.lastdownload
	FROM ({$db_prefix}down_file as p,  {$db_prefix}down_cat AS c)
		LEFT JOIN {$db_prefix}members AS m ON  (p.ID_MEMBER = m.ID_MEMBER)
	WHERE p.ID_FILE = $id AND p.ID_CAT = c.ID_CAT LIMIT 1", __FILE__, __LINE__);


   	// Check if download exists
    if (db_affected_rows()== 0)
    	fatal_error($txt['downloads_error_no_downloadexist'],false);


    $row = mysql_fetch_assoc($dbresult);

    // Check if they can view the download
    Downloads_GetCatPermission($row['ID_CAT'],'view');

	// Checked if they are allowed to view an unapproved download.
	if ($row['approved'] == 0 && $ID_MEMBER != $row['ID_MEMBER'])
	{
		if (!allowedTo('downloads_manage'))
			fatal_error($txt['downloads_error_file_notapproved'],false);
	}

	// Download information
	$context['downloads_file'] = array(
		'ID_FILE' => $row['ID_FILE'],
		'ID_MEMBER' => $row['ID_MEMBER'],
		'commenttotal' => $row['commenttotal'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'allowcomments' => $row['allowcomments'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => timeformat($row['date']),
		'keywords' => $row['keywords'],
		'realName' => $row['realName'],
		'totalratings' => $row['totalratings'],
		'rating' => $row['rating'],
		'CAT_TITLE' => $row['CAT_TITLE'],
		'disablerating' => @$row['disablerating'],
		'credits' => $row['credits'],
		'orginalfilename' => $row['orginalfilename'],
		'totaldownloads' => $row['totaldownloads'],
		'lastdownload' => $row['lastdownload'],
		'fileurl' => $row['fileurl'],


	);
	mysql_free_result($dbresult);

	Downloads_GetParentLink($row['ID_PARENT']);

	// Link Tree
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=downloads;cat=' . $row['ID_CAT'],
					'name' => $row['CAT_TITLE']
				);


	// Show Custom Fields
	$result = db_query("
	SELECT
		f.title, d.value
	FROM  ({$db_prefix}down_custom_field as f,{$db_prefix}down_custom_field_data as d)
	WHERE d.ID_CUSTOM = f.ID_CUSTOM AND d.ID_FILE = " . $context['downloads_file']['ID_FILE'] .  "
	ORDER BY f.roworder desc", __FILE__, __LINE__);
	$context['downloads_custom'] = array();
	while ($row = mysql_fetch_assoc($result))
	{
		$context['downloads_custom'][] = array(
			'value' => $row['value'],
			'title' => $row['title'],
		);
	}
	mysql_free_result($result);

	if (!empty($modSettings['down_set_commentsnewest']))
		$commentorder = 'DESC';
	else
		$commentorder = 'ASC';
		// Display all user comments
		$dbresult = db_query("
		SELECT
			c.ID_FILE,  c.ID_COMMENT, c.date, c.comment, c.ID_MEMBER,
			c.lastmodified,c.modified_ID_MEMBER, m.posts, m.realName, c.approved, md.realName modmember
		 FROM {$db_prefix}down_comment as c
		 	LEFT JOIN {$db_prefix}members AS m ON (c.ID_MEMBER = m.ID_MEMBER)
		 	LEFT JOIN {$db_prefix}members AS md ON (c.modified_ID_MEMBER = md.ID_MEMBER)
		 WHERE c.ID_FILE = " . $context['downloads_file']['ID_FILE'] . " AND c.approved = 1
		 ORDER BY c.ID_COMMENT $commentorder", __FILE__, __LINE__);

		$context['comment_count'] =   db_affected_rows();
	$context['downloads_comments'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['downloads_comments'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'ID_COMMENT' => $row['ID_COMMENT'],
			'date' => $row['date'],
			'comment' => $row['comment'],
			'ID_MEMBER' => $row['ID_MEMBER'],
			'lastmodified' => $row['lastmodified'],
			'modified_ID_MEMBER' => $row['modified_ID_MEMBER'],
			'posts' => $row['posts'],
			'realName' => $row['realName'],
			'approved' => $row['approved'],
			'modmember' => $row['modmember'],
		);
	}
	mysql_free_result($dbresult);

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	// Update the number of views.
	db_query("UPDATE {$db_prefix}down_file
		SET views = views + 1 WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);


	$context['sub_template']  = 'view_download';

	$context['page_title'] = $mbname . ' - ' . $context['downloads_file']['title'];

	if (!empty($modSettings['down_who_viewing']))
	{
		$context['can_moderate_forum'] = allowedTo('moderate_forum');

				// SMF 1.1
				// Taken from Display.php
				// Start out with no one at all viewing it.
				$context['view_members'] = array();
				$context['view_members_list'] = array();
				$context['view_num_hidden'] = 0;
				$whoID = (string) $id;

				// Search for members who have this download id set in their GET data.
				$request = db_query("
					SELECT
						lo.ID_MEMBER, lo.logTime, mem.realName, mem.memberName, mem.showOnline,
						mg.onlineColor, mg.ID_GROUP, mg.groupName
					FROM {$db_prefix}log_online AS lo
						LEFT JOIN {$db_prefix}members AS mem ON (mem.ID_MEMBER = lo.ID_MEMBER)
						LEFT JOIN {$db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
					WHERE INSTR(lo.url, 's:9:\"downloads\";s:2:\"sa\";s:4:\"view\";s:2:\"id\";s:" . strlen($whoID ) .":\"$id\";') OR lo.session = '" . ($user_info['is_guest'] ? 'ip' . $user_info['ip'] : session_id()) . "'", __FILE__, __LINE__);
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

function Downloads_AddDownload()
{
	global $context, $mbname, $txt, $modSettings, $db_prefix, $ID_MEMBER, $user_info, $sourcedir;

	isAllowedTo('downloads_add');

	if (isset($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];
	else
		$cat = 0;

	$context['down_cat'] = $cat;

	Downloads_GetCatPermission($cat,'addfile');

	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

		$dbresult = db_query("
		SELECT
			c.ID_CAT, c.title, p.view, p.addfile
		FROM {$db_prefix}down_cat AS c
			LEFT JOIN {$db_prefix}down_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		WHERE c.redirect = 0 ORDER BY c.roworder ASC", __FILE__, __LINE__);
		if (mysql_num_rows($dbresult) == 0)
		 	fatal_error($txt['downloads_error_no_catexists'] , false);

		$context['downloads_cat'] = array();
		 while($row = mysql_fetch_assoc($dbresult))
			{
				// Check if they have permission to add to this category.
				if ($row['view'] == '0' || $row['addfile'] == '0' )
					continue;

				$context['downloads_cat'][] = array(
					'ID_CAT' => $row['ID_CAT'],
					'title' => $row['title'],
				);
			}
		mysql_free_result($dbresult);

	$result = db_query("
	SELECT
		title, defaultvalue, is_required, ID_CUSTOM
	FROM  {$db_prefix}down_custom_field
	WHERE ID_CAT = " . $cat, __FILE__, __LINE__);
	$context['downloads_custom'] = array();
	while ($row = mysql_fetch_assoc($result))
	{
			$context['downloads_custom'][] = array(
					'ID_CUSTOM' => $row['ID_CUSTOM'],
					'title' => $row['title'],
					'defaultvalue' => $row['defaultvalue'],
					'is_required' => $row['is_required'],
				);
	}
	mysql_free_result($result);

	// Get Quota Limits to Display
	$context['quotalimit'] = Downloads_GetQuotaGroupLimit($ID_MEMBER);
	$context['userspace'] = Downloads_GetUserSpaceUsed($ID_MEMBER);

	$context['sub_template']  = 'add_download';

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_adddownload'];

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');


	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');
	$context['post_box_name'] = 'description';
	$context['post_form'] = 'picform';

}

function Downloads_AddDownload2()
{
	global $ID_MEMBER, $txt, $db_prefix, $scripturl, $modSettings,  $sourcedir, $gd2, $user_info, $func;

	isAllowedTo('downloads_add');

	// Check if downloads path is writable
	if (!is_writable($modSettings['down_path']))
		fatal_error($txt['downloads_write_error'] . $modSettings['down_path']);


	$title = $func['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
	$description = $func['htmlspecialchars']($_REQUEST['description'],ENT_QUOTES);
	$keywords = $func['htmlspecialchars']($_REQUEST['keywords'],ENT_QUOTES);
	$cat = (int) $_REQUEST['cat'];
	$fileurl = htmlspecialchars($_REQUEST['fileurl'],ENT_QUOTES);
	$allowcomments = isset($_REQUEST['allowcomments']) ? 1 : 0;
	$sendemail = isset($_REQUEST['sendemail']) ? 1 : 0;
	$filesize = 0;

	Downloads_GetCatPermission($cat,'addfile');


	// Check if downloads are auto approved
	$approved = (allowedTo('downloads_autoapprove') ? 1 : 0);

	// Allow comments on file if no setting set.
	if (empty($modSettings['down_commentchoice']))
		$allowcomments = 1;


	if ($title == '')
		fatal_error($txt['downloads_error_no_title'],false);
	if ($cat == '')
		fatal_error($txt['downloads_error_no_cat'],false);

	if ($modSettings['down_set_enable_multifolder'])
		Downloads_CreateDownloadFolder();


		$result = db_query("
		SELECT
			f.title, f.is_required, f.ID_CUSTOM
		FROM  {$db_prefix}down_custom_field as f
		WHERE f.is_required = 1 AND f.ID_CAT = " . $cat, __FILE__, __LINE__);
		while ($row2 = mysql_fetch_assoc($result))
		{
	 		if (!isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
	 		{
	 			fatal_error($txt['downloads_err_req_custom_field'] . $row2['title'], false);
	 		}
	 		else
	 		{
	 			if ($_REQUEST['cus_' . $row2['ID_CUSTOM']] == '')
	 				fatal_error($txt['downloads_err_req_custom_field'] . $row2['title'], false);
	 		}
	 	}
		mysql_free_result($result);


	// Get category infomation
	$dbresult = db_query("
	SELECT
		ID_BOARD,locktopic
	FROM {$db_prefix}down_cat
	WHERE ID_CAT = $cat", __FILE__, __LINE__);
	$rowcat = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);


	// Process Uploaded file
	if (isset($_FILES['download']['name']) && $_FILES['download']['name'] != '')
	{
		// Store the orginal filename
		$orginalfilename =  addslashes($_FILES['download']['name']);

		// Get the filesize
		$filesize = $_FILES['download']['size'];


		if (!empty($modSettings['down_max_filesize']) && $filesize > $modSettings['down_max_filesize'])
		{
			// Delete the temp file
			@unlink($_FILES['download']['tmp_name']);
			fatal_error($txt['downloads_error_file_filesize'] . Downloads_format_size($modSettings['down_max_filesize'], 2),false);
		}


		// Check Quota
		$quotalimit = Downloads_GetQuotaGroupLimit($ID_MEMBER);
		$userspace = Downloads_GetUserSpaceUsed($ID_MEMBER);
		// Check if exceeds quota limit or if there is a quota
		if ($quotalimit != 0  &&  ($userspace + $filesize) >  $quotalimit)
		{
			@unlink($_FILES['download']['tmp_name']);
			fatal_error($txt['downloads_error_space_limit'] . Downloads_format_size($userspace, 2) . ' / ' . Downloads_format_size($quotalimit, 2),false);
		}

		// Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
		$filename = $ID_MEMBER . '_' . date('d_m_y_g_i_s'); //. '.' . $extension;

		$extrafolder = '';

		if ($modSettings['down_set_enable_multifolder'])
			$extrafolder = $modSettings['down_folder_id'] . '/';


		move_uploaded_file($_FILES['download']['tmp_name'], $modSettings['down_path'] . $extrafolder .  $filename);
		@chmod($modSettings['down_path'] . $extrafolder .  $filename, 0644);


		// Create the Database entry
		$t = time();
		$file_id = 0;

		db_query("INSERT INTO {$db_prefix}down_file
							(ID_CAT, filesize,filename, orginalfilename, keywords, title, description,ID_MEMBER,date,approved,allowcomments,sendemail)
						VALUES ($cat, $filesize, '" . $extrafolder . $filename . "', '$orginalfilename',   '$keywords','$title', '$description',$ID_MEMBER,$t,$approved, $allowcomments,$sendemail)", __FILE__, __LINE__);

		$file_id = db_insert_id();

		// If we are using multifolders get the next folder id
		if ($modSettings['down_set_enable_multifolder'])
				Downloads_ComputeNextFolderID($file_id);

	}
	else
	{

		// Check if they entered a fileurl
		if (empty($fileurl))
			fatal_error($txt['downloads_error_no_download']);
		else
		{

				if (substr($fileurl, 0, 7) != "http://" && substr($fileurl, 0, 8) != "https://")
            		fatal_error($txt['downloads_error_invalid_upload_url'],false);

			// Process the fileurl specific settings
			// Create the Database entry
			$filesize = Downloads_getRemoteFilesize($fileurl);
			$t = time();
			$file_id = 0;

			db_query("INSERT INTO {$db_prefix}down_file
								(ID_CAT, fileurl, filesize, keywords, title, description,ID_MEMBER,date,approved,allowcomments,sendemail)
							VALUES ($cat, '$fileurl', '$filesize', '$keywords', '$title', '$description',$ID_MEMBER,$t,$approved, $allowcomments,$sendemail)", __FILE__, __LINE__);

			$file_id = db_insert_id();

		}

	}

					// Check for any custom fields
					$result = db_query("
					SELECT
						f.title, f.is_required, f.ID_CUSTOM
					FROM  {$db_prefix}down_custom_field as f
					WHERE f.ID_CAT = " . $cat, __FILE__, __LINE__);
					while ($row2 = mysql_fetch_assoc($result))
					{
						if (isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
						{

							$custom_data = $func['htmlspecialchars']($_REQUEST['cus_' . $row2['ID_CUSTOM']],ENT_QUOTES);

							db_query("INSERT INTO {$db_prefix}down_custom_field_data
							(ID_FILE, ID_CUSTOM, value)
							VALUES('$file_id', " . $row2['ID_CUSTOM'] . ", '$custom_data')", __FILE__, __LINE__);
						}
					}
					mysql_free_result($result);



				if ($filesize != 0)
					Downloads_UpdateUserFileSizeTable($ID_MEMBER,$filesize);

				if ($rowcat['ID_BOARD'] != 0 && $approved == 1)
				{
					// Create the post
					require_once($sourcedir . '/Subs-Post.php');

					$showpostlink = '\n\n' . $scripturl . '?action=downloads;sa=view;down=' . $file_id;

					$msgOptions = array(
						'id' => 0,
						'subject' => $title,
						'body' => '[b]' . $title . "[/b]\n\n$showpostlink",
						'icon' => 'xx',
						'smileys_enabled' => 1,
						'attachments' => array(),
					);
					$topicOptions = array(
						'id' => 0,
						'board' => $rowcat['ID_BOARD'],
						'poll' => null,
						'lock_mode' => $rowcat['locktopic'],
						'sticky_mode' => null,
						'mark_as_read' => true,
					);
					$posterOptions = array(
						'id' => $ID_MEMBER,
						'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']),
					);
					preparsecode($msgOptions['body']);


					createPost($msgOptions, $topicOptions, $posterOptions);

					$ID_TOPIC = $topicOptions['id'];

					// Update the download with the topic id
					db_query("UPDATE {$db_prefix}down_file SET ID_TOPIC = $ID_TOPIC WHERE ID_FILE = $file_id LIMIT 1", __FILE__, __LINE__);


				}


				Downloads_UpdateCategoryTotals($cat);

			// Update the SMF Shop Points
			if (isset($modSettings['shopVersion']))
 				db_query("UPDATE {$db_prefix}members
				 	SET money = money + " . $modSettings['down_shop_fileadd'] . "
				 	WHERE ID_MEMBER = $ID_MEMBER
				 	LIMIT 1", __FILE__, __LINE__);


		// Redirect to the users files page.
		if ($ID_MEMBER != 0)
			redirectexit('action=downloads;sa=myfiles;u=' . $ID_MEMBER);
		else
			redirectexit('action=downloads;cat=' . $cat);

}

function Downloads_EditDownload()
{
	global $context, $mbname, $txt, $sourcedir, $ID_MEMBER, $db_prefix, $modSettings, $user_info;

	is_not_guest();

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

		if ($context['user']['is_guest'])
			$groupid = -1;
		else
			$groupid =  $user_info['groups'][0];

	// Check if the user owns the file or is admin
    $dbresult = db_query("
    SELECT
    	p.ID_FILE, p.allowcomments, p.ID_CAT, p.keywords, p.commenttotal, p.filesize,
    	p.filename, p.approved, p.views, p.title, p.ID_MEMBER,
      	m.realName, p.date, p.description, p.sendemail, p.fileurl,p.orginalfilename
    FROM {$db_prefix}down_file as p
       LEFT JOIN {$db_prefix}members AS m ON (m.ID_MEMBER = p.ID_MEMBER)
     WHERE p.ID_FILE = $id  LIMIT 1", __FILE__, __LINE__);
	if (db_affected_rows()== 0)
    	fatal_error($txt['downloads_error_no_downloadexist'],false);
    $row = mysql_fetch_assoc($dbresult);


    // Check the category permission
	Downloads_GetCatPermission($row['ID_CAT'],'editfile');

	// Download information
	$context['downloads_file'] = array(
		'ID_FILE' => $row['ID_FILE'],
		'ID_MEMBER' => $row['ID_MEMBER'],
		'commenttotal' => $row['commenttotal'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'fileurl' => $row['fileurl'],
		'allowcomments' => $row['allowcomments'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => timeformat($row['date']),
		'keywords' => $row['keywords'],
		'realName' => $row['realName'],
		'sendemail' => $row['sendemail'],
		'orginalfilename' => $row['orginalfilename'],
	);
	mysql_free_result($dbresult);



	// Custom Fields
	$result = db_query("
	SELECT
		f.title, f.is_required, f.ID_CUSTOM, d.value
	FROM  {$db_prefix}down_custom_field as f
		LEFT JOIN {$db_prefix}down_custom_field_data as d ON (d.ID_CUSTOM = f.ID_CUSTOM)
	WHERE ID_FILE = " . $context['downloads_file']['ID_FILE'] . " AND ID_CAT = " . $context['downloads_file']['ID_CAT'], __FILE__, __LINE__);
	$context['downloads_custom'] = array();
	while ($row = mysql_fetch_assoc($result))
	{
		$context['downloads_custom'][] = array(
			'ID_CUSTOM' => $row['ID_CUSTOM'],
			'title' => $row['title'],
			'is_required' => $row['is_required'],
			'value' => $row['value'],

		);
	}
	mysql_free_result($result);


	if (allowedTo('downloads_manage') || (allowedTo('downloads_edit') && $ID_MEMBER == $context['downloads_file']['ID_MEMBER']))
	{
		// Get the category information

		 	$dbresult = db_query("
		 	SELECT
		 		c.ID_CAT, c.title, p.view, p.addfile
		 	FROM {$db_prefix}down_cat AS c
		 		LEFT JOIN {$db_prefix}down_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		 	WHERE c.redirect = 0 ORDER BY c.roworder ASC", __FILE__, __LINE__);
			$context['downloads_cat'] = array();
		 	while($row = mysql_fetch_assoc($dbresult))
			{
				// Check if they have permission to add to this category.
				if ($row['view'] == '0' || $row['addfile'] == '0' )
					continue;

				$context['downloads_cat'][] = array(
				'ID_CAT' => $row['ID_CAT'],
				'title' => $row['title'],
				);
			}
			mysql_free_result($dbresult);

		// Get Quota Limits to Display
		$context['quotalimit'] = Downloads_GetQuotaGroupLimit($ID_MEMBER);
		$context['userspace'] = Downloads_GetUserSpaceUsed($ID_MEMBER);

		$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_editdownload'];
		$context['sub_template']  = 'edit_download';

		// Check if spellchecking is both enabled and actually working.
		$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

		/// Used for the editor
		require_once($sourcedir . '/Subs-Post.php');
		$context['post_box_name'] = 'description';
		$context['post_form'] = 'picform';

	}
	else
	{
		fatal_error($txt['downloads_error_noedit_permission']);
	}



}

function Downloads_EditDownload2()
{
	global $ID_MEMBER, $txt, $db_prefix, $scripturl, $modSettings, $sourcedir, $func;

	is_not_guest();

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	// Check the user permissions
    $dbresult = db_query("
    SELECT
    	ID_MEMBER,ID_CAT, filename,filesize
    FROM {$db_prefix}down_file
    WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);
    $row = mysql_fetch_assoc($dbresult);
	$memID = $row['ID_MEMBER'];
	$oldfilesize = $row['filesize'];
	$oldfilename = $row['filename'];

	// Check the category permission
	Downloads_GetCatPermission($row['ID_CAT'],'editfile');

	mysql_free_result($dbresult);
	if (allowedTo('downloads_manage') || (allowedTo('downloads_edit') && $ID_MEMBER == $memID))
	{

		if (!is_writable($modSettings['down_path']))
			fatal_error($txt['downloads_write_error'] . $modSettings['down_path']);

		$title = $func['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
		$description = $func['htmlspecialchars']($_REQUEST['description'],ENT_QUOTES);
		$keywords = $func['htmlspecialchars']($_REQUEST['keywords'],ENT_QUOTES);
		$cat = (int) $_REQUEST['cat'];
		$allowcomments = isset($_REQUEST['allowcomments']) ? 1 : 0;
		$sendemail = isset($_REQUEST['sendemail']) ? 1 : 0;
		$fileurl = htmlspecialchars($_REQUEST['fileurl'],ENT_QUOTES);
		$filesize = 0;

		// Check if downloads are auto approved
		$approved = (allowedTo('downloads_autoapprove') ? 1 : 0);

		// Allow comments on file if no setting set.
		if (empty($modSettings['down_commentchoice']))
			$allowcomments = 1;

		if ($title == '')
			fatal_error($txt['downloads_error_no_title'],false);
		if ($cat == '')
			fatal_error($txt['downloads_error_no_cat'],false);



		// Check for any required custom fields
		$result = db_query("
		SELECT
			f.title, f.is_required, f.ID_CUSTOM
		FROM  {$db_prefix}down_custom_field as f
		WHERE f.is_required = 1 AND f.ID_CAT = " . $cat, __FILE__, __LINE__);
		while ($row2 = mysql_fetch_assoc($result))
		{
	 		if (!isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
	 		{
	 			fatal_error($txt['downloads_err_req_custom_field'] . $row2['title'], false);
	 		}
	 		else
	 		{
	 			if ($_REQUEST['cus_' . $row2['ID_CUSTOM']] == '')
	 				fatal_error($txt['downloads_err_req_custom_field'] . $row2['title'], false);
	 		}
	 	}
		mysql_free_result($result);

		// Process Uploaded file
		if (isset($_FILES['download']['name']) && $_FILES['download']['name'] != '')
		{

			// Store the orginal filename
			$orginalfilename =  addslashes($_FILES['download']['name']);
			$filesize = $_FILES['download']['size'];


			if (!empty($modSettings['down_max_filesize']) && $filesize > $modSettings['down_max_filesize'])
			{
				// Delete the temp file
				@unlink($_FILES['download']['tmp_name']);
				fatal_error($txt['downloads_error_file_filesize'] . Downloads_format_size($modSettings['down_max_filesize'], 2) ,false);
			}
			// Check Quota
			$quotalimit = Downloads_GetQuotaGroupLimit($ID_MEMBER);
			$userspace = Downloads_GetUserSpaceUsed($ID_MEMBER);
			// Check if exceeds quota limit or if there is a quota
			if ($quotalimit != 0  &&  ($userspace + $filesize) >  $quotalimit)
			{
				@unlink($_FILES['download']['tmp_name']);
				fatal_error($txt['downloads_error_space_limit'] . Downloads_format_size($userspace, 2) . ' / ' . Downloads_format_size($quotalimit, 2),false);
			}

			// Delete the old files
			@unlink($modSettings['down_path'] . $oldfilename );

			$extrafolder = '';

			if ($modSettings['down_set_enable_multifolder'])
				$extrafolder = $modSettings['down_folder_id'] . '/';


			// Filename Member Id + Day + Month + Year + 24 hour, Minute Seconds
			$filename = $ID_MEMBER . '_' . date('d_m_y_g_i_s');
			move_uploaded_file($_FILES['download']['tmp_name'], $modSettings['down_path'] . $extrafolder . $filename);
			@chmod($modSettings['down_path'] . $extrafolder . $filename, 0644);


			// Update the Database entry
			$t = time();

			db_query("UPDATE {$db_prefix}down_file
					SET ID_CAT = $cat, filesize = $filesize, filename = '" . $extrafolder . $filename . "', approved = $approved,
					 date =  $t, title = '$title', description = '$description', keywords = '$keywords',
					  allowcomments = $allowcomments, sendemail = $sendemail, orginalfilename = '$orginalfilename'
					  WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);

			Downloads_UpdateUserFileSizeTable($memID,$oldfilesize * -1);
			Downloads_UpdateUserFileSizeTable($memID,$filesize);


			// Update the file totals
			if ($cat != $row['ID_CAT'])
			{
				Downloads_UpdateCategoryTotals($cat);
				Downloads_UpdateCategoryTotals($row['ID_CAT']);
			}


					// Change the file owner if selected
					if (allowedTo('downloads_manage') && isset($_REQUEST['pic_postername']))
					{
						$pic_postername = str_replace('"','', $_REQUEST['pic_postername']);
						$pic_postername = str_replace("'",'', $pic_postername);
						$pic_postername = str_replace('\\','', $pic_postername);
						$pic_postername = $func['htmlspecialchars']($pic_postername, ENT_QUOTES);

						$memid = 0;

						$dbresult = db_query("
						SELECT
							realName, ID_MEMBER
						FROM {$db_prefix}members
						WHERE realName = '$pic_postername' OR memberName = '$pic_postername'  LIMIT 1", __FILE__, __LINE__);
						$row = mysql_fetch_assoc($dbresult);
						mysql_free_result($dbresult);

						if (db_affected_rows() != 0)
						{
							// Member found update the file owner

							$memid = $row['ID_MEMBER'];
							db_query("UPDATE {$db_prefix}down_file
							SET ID_MEMBER = $memid WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);

						}

					}
					Downloads_UpdateCategoryTotalByFileID($id);
					// Redirect to the users files page.
					redirectexit('action=downloads;sa=myfiles;u=' . $ID_MEMBER);


		}
		else
		{
			// Update the download properties if no upload has been set

				if (!empty($fileurl))
				{

				if (substr($fileurl, 0, 7) != "http://" && substr($fileurl, 0, 8) != "https://")
            		fatal_error($txt['downloads_error_invalid_upload_url'],false);

					$filesize = Downloads_getRemoteFilesize($fileurl);
					db_query("UPDATE {$db_prefix}down_file
					SET
					filesize = '$filesize'

					WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);
				}

				db_query("UPDATE {$db_prefix}down_file
				SET ID_CAT = $cat, title = '$title', description = '$description', keywords = '$keywords',
				allowcomments = $allowcomments, sendemail = $sendemail, approved = $approved,
				fileurl = '$fileurl'

				WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);


					// Update the file totals
					if ($cat != $row['ID_CAT'])
					{
						Downloads_UpdateCategoryTotals($cat);
						Downloads_UpdateCategoryTotals($row['ID_CAT']);
					}

				// Change the file owner if selected

					if (allowedTo('downloads_manage') && isset($_REQUEST['pic_postername']))
					{
						$pic_postername = str_replace('"','', $_REQUEST['pic_postername']);
						$pic_postername = str_replace("'",'', $pic_postername);
						$pic_postername = str_replace('\\','', $pic_postername);
						$pic_postername = $func['htmlspecialchars']($pic_postername, ENT_QUOTES);

						$memid = 0;

						$dbresult = db_query("
						SELECT
							realName, ID_MEMBER
						FROM {$db_prefix}members
						WHERE realName = '$pic_postername' OR memberName = '$pic_postername'  LIMIT 1", __FILE__, __LINE__);
						$row = mysql_fetch_assoc($dbresult);
						mysql_free_result($dbresult);

						if (db_affected_rows() != 0)
						{
							// Member found update the file owner
							$memid = $row['ID_MEMBER'];
							db_query("UPDATE {$db_prefix}down_file
							SET ID_MEMBER = $memid WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);


						}

					}

					Downloads_UpdateCategoryTotalByFileID($id);

					// Check for any custom fields

					db_query("DELETE FROM  {$db_prefix}down_custom_field_data
							WHERE ID_FILE = " . $id, __FILE__, __LINE__);

					$result = db_query("
					SELECT
						f.title, f.is_required, f.ID_CUSTOM
					FROM  {$db_prefix}down_custom_field as f
					WHERE f.ID_CAT = " . $cat, __FILE__, __LINE__);
					while ($row2 = mysql_fetch_assoc($result))
					{
						if (isset($_REQUEST['cus_' . $row2['ID_CUSTOM']]))
						{

							$custom_data = $func['htmlspecialchars']($_REQUEST['cus_' . $row2['ID_CUSTOM']],ENT_QUOTES);

							db_query("INSERT INTO {$db_prefix}down_custom_field_data
							(ID_FILE, ID_CUSTOM, value)
							VALUES('$id', " . $row2['ID_CUSTOM'] . ", '$custom_data')", __FILE__, __LINE__);
						}
					}
					mysql_free_result($result);


			// Redirect to the users files page.
			redirectexit('action=downloads;sa=myfiles;u=' . $ID_MEMBER);

		}

	}
	else
		fatal_error($txt['downloads_error_noedit_permission']);


}

function Downloads_DeleteDownload()
{
	global $context, $mbname, $txt, $ID_MEMBER, $db_prefix;

	is_not_guest();

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	// Check if the user owns the download or is admin
    $dbresult = db_query("
    SELECT
    	p.ID_FILE, p.fileurl, p.allowcomments, p.ID_CAT, p.keywords, p.commenttotal, p.totaldownloads,
     	p.filesize, p.filename, p.approved, p.views, p.title, p.ID_MEMBER, p.date, m.realName, p.description
    FROM {$db_prefix}down_file as p
    LEFT JOIN {$db_prefix}members AS m ON (p.ID_MEMBER = m.ID_MEMBER)
    WHERE ID_FILE = $id  LIMIT 1", __FILE__, __LINE__);
	if (db_affected_rows()== 0)
    	fatal_error($txt['downloads_error_no_downloadexist'],false);
    $row = mysql_fetch_assoc($dbresult);
	// Check the category permission
	Downloads_GetCatPermission($row['ID_CAT'],'delfile');
	// File information
	$context['downloads_file'] = array(
		'ID_FILE' => $row['ID_FILE'],
		'ID_MEMBER' => $row['ID_MEMBER'],
		'commenttotal' => $row['commenttotal'],
		'views' => $row['views'],
		'title' => $row['title'],
		'description' => $row['description'],
		'filesize' => $row['filesize'],
		'filename' => $row['filename'],
		'allowcomments' => $row['allowcomments'],
		'ID_CAT' => $row['ID_CAT'],
		'date' => timeformat($row['date']),
		'keywords' => $row['keywords'],
		'realName' => $row['realName'],
		'fileurl' => $row['fileurl'],
		'totaldownloads' => $row['totaldownloads'],
	);
	mysql_free_result($dbresult);

	if (allowedTo('downloads_manage') || (allowedTo('downloads_delete') && $ID_MEMBER == $context['downloads_file']['ID_MEMBER']))
	{
		$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_deldownload'];
		$context['sub_template']  = 'delete_download';

	}
	else
	{
		fatal_error($txt['downloads_error_nodelete_permission']);
	}


}

function Downloads_DeleteDownload2()
{
	global $context, $txt, $ID_MEMBER, $scripturl, $db_prefix, $modSettings;

	$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	// Check if the user owns the download or is admin
    $dbresult = db_query("
    SELECT
    	p.ID_FILE, p.ID_CAT, p.ID_MEMBER
    FROM {$db_prefix}down_file as p
    WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);


	if (empty($row['ID_FILE']))
		fatal_error($txt['downloads_error_no_file_selected'],false);

	$memID = $row['ID_MEMBER'];

	mysql_free_result($dbresult);
	// Check the category permission

	Downloads_GetCatPermission($row['ID_CAT'],'delfile');

	if (allowedTo('downloads_manage') || (allowedTo('downloads_delete') && $ID_MEMBER == $memID))
	{

		Downloads_DeleteFileByID($id);

		Downloads_UpdateCategoryTotals($row['ID_CAT']);

		// Redirect to the users files page.
		redirectexit('action=downloads;sa=myfiles;u=' . $ID_MEMBER);
	}
	else
	{
		fatal_error($txt['downloads_error_nodelete_permission']);
	}


}

function Downloads_DeleteFileByID($id)
{
	global $modSettings, $sourcedir, $boarddir, $db_prefix;

	require_once($sourcedir . '/RemoveTopic.php');

    $dbresult = db_query("
    SELECT
    	p.ID_FILE,  p.ID_CAT, p.filesize, p.filename,  p.ID_MEMBER, p.ID_TOPIC
    FROM {$db_prefix}down_file as p
    WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$oldfilesize = $row['filesize'];
	$memID = $row['ID_MEMBER'];
	mysql_free_result($dbresult);


	// Delete the download
	if ($row['filename'] != '')
		@unlink($modSettings['down_path'] . $row['filename']);


	// Update the quota
	$oldfilesize = $oldfilesize * -1;

	if ($oldfilesize != 0)
		Downloads_UpdateUserFileSizeTable($memID,$oldfilesize);

	Downloads_UpdateCategoryTotalByFileID($id);

	// Delete all the download related db entries

		db_query("DELETE FROM {$db_prefix}down_comment WHERE ID_FILE  = $id", __FILE__, __LINE__);
		db_query("DELETE FROM {$db_prefix}down_rating WHERE ID_FILE  = $id", __FILE__, __LINE__);
		db_query("DELETE FROM {$db_prefix}down_report WHERE ID_FILE  = $id", __FILE__, __LINE__);
		db_query("DELETE FROM {$db_prefix}down_creport WHERE ID_FILE  = $id", __FILE__, __LINE__);
		db_query("DELETE FROM {$db_prefix}down_custom_field_data WHERE ID_FILE  = $id", __FILE__, __LINE__);

		// Delete the download
		db_query("DELETE FROM {$db_prefix}down_file WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);

		// Update the SMF Shop Points
			if (isset($modSettings['shopVersion']))
 				db_query("UPDATE {$db_prefix}members
				 	SET money = money - " . $modSettings['down_shop_fileadd'] . "
				 	WHERE ID_MEMBER = $memID
				 	LIMIT 1", __FILE__, __LINE__);

 	// Remove the Topic
 	if ($row['ID_TOPIC'] != 0)
		removeTopics($row['ID_TOPIC']);

}

function Downloads_ReportDownload()
{
	global $context, $mbname, $txt;

	isAllowedTo('downloads_report');
	is_not_guest();
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);


	$context['downloads_file_id'] = $id;

	$context['sub_template']  = 'report_download';

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_reportdownload'];

}

function Downloads_ReportDownload2()
{
	global $context, $scripturl, $db_prefix, $ID_MEMBER, $txt, $func;

	isAllowedTo('downloads_report');

	$comment = $func['htmlspecialchars']($_REQUEST['comment'],ENT_QUOTES);
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	if ($comment == '')
		fatal_error($txt['downloads_error_no_comment'],false);

	$commentdate = time();

	db_query("INSERT INTO {$db_prefix}down_report
			(ID_MEMBER, comment, date, ID_FILE)
		VALUES ($ID_MEMBER,'$comment', $commentdate,$id)", __FILE__, __LINE__);

	redirectexit('action=downloads;sa=view;down=' . $id);

}

function Downloads_AddComment()
{
	global $context, $mbname, $txt, $modSettings, $user_info, $db_prefix, $sourcedir;

	isAllowedTo('downloads_comment');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	$context['downloads_file_id'] = $id;

	// Comments allowed check
    $dbresult = db_query("
    SELECT
    	p.allowcomments, p.ID_CAT
    FROM {$db_prefix}down_file as p
    WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$ID_CAT = $row['ID_CAT'];
	mysql_free_result($dbresult);
	// Checked if comments are allowed
	if ($row['allowcomments'] == 0)
	{
		fatal_error($txt['downloads_error_not_allowcomment']);
	}
	Downloads_GetCatPermission($ID_CAT,'addcomment');



	$context['sub_template']  = 'add_comment';

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_addcomment'];

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');
	$context['post_box_name'] = 'comment';
	$context['post_form'] = 'cprofile';

	// Register this form and get a sequence number in $context.
	checkSubmitOnce('register');

	// Spam Protect
	spamProtection('down');

}

function Downloads_AddComment2()
{
	global $context, $scripturl, $db_prefix, $ID_MEMBER, $txt, $sourcedir, $modSettings, $func;

	isAllowedTo('downloads_comment');

	$comment = $func['htmlspecialchars']($_REQUEST['comment'],ENT_QUOTES);
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	// Check if that download allows comments.
    $dbresult = db_query("
    SELECT
    	p.allowcomments, p.ID_CAT, p.sendemail,m.emailAddress,p.ID_MEMBER,p.title
    FROM {$db_prefix}down_file as p
    LEFT JOIN {$db_prefix}members as m ON (p.ID_MEMBER  = m.ID_MEMBER)
    WHERE p.ID_FILE = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$mem_email = $row['emailAddress'];
	$title = $row['title'];
	$doemail = $row['sendemail'];
	$pic_memid = $row['ID_MEMBER'];

	mysql_free_result($dbresult);
	// Checked if comments are allowed
	if ($row['allowcomments'] == 0)
		fatal_error($txt['downloads_error_not_allowcomment']);

	// Check if they are allowed to add comments to that category
	if ($row['ID_CAT'] != 0)
		Downloads_GetCatPermission($row['ID_CAT'],'addcomment');

	if ($comment == '')
		fatal_error($txt['downloads_error_no_comment'],false);

	$commentdate = time();

	// Check if you have automatic approval
	$approved = (allowedTo('downloads_autocomment') ? 1 : 0);

	db_query("INSERT INTO {$db_prefix}down_comment
			(ID_MEMBER, comment, date, ID_FILE,approved)
		VALUES ($ID_MEMBER,'$comment', $commentdate,$id,$approved)", __FILE__, __LINE__);
	$comment_id = db_insert_id();

	// Update Comment total
	 db_query("UPDATE {$db_prefix}down_file
		SET commenttotal = commenttotal + 1 WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);

	// Check to send email on new comment
	 if ($doemail == 1 && $pic_memid != $ID_MEMBER && $pic_memid != 0)
	 {
	 	require_once($sourcedir . '/Subs-Post.php');
	 	sendmail($mem_email, str_replace("%s", $title, $txt['downloads_notify_subject']), str_replace("%s", $scripturl . '?action=downloads;sa=view;down=' . $id . '#c' . $comment_id, $txt['downloads_notify_body']));
	 }

			// Update the SMF Shop Points
			if (isset($modSettings['shopVersion']))
 				db_query("UPDATE {$db_prefix}members
				 	SET money = money + " . $modSettings['down_shop_commentadd'] . "
				 	WHERE ID_MEMBER = $ID_MEMBER
				 	LIMIT 1", __FILE__, __LINE__);

	redirectexit('action=downloads;sa=view;down=' . $id);

}

function Downloads_EditComment()
{
	global $context, $mbname, $txt, $sourcedir, $modSettings, $user_info, $db_prefix, $ID_MEMBER;

	is_not_guest();

	$g_manage = allowedTo('downloads_manage');
	$g_edit_comment = allowedTo('downloads_editcomment');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);


	// Check if allowed to edit the comment
    $dbresult = db_query("
    SELECT
    	ID_COMMENT,ID_FILE,ID_MEMBER,approved,comment,date,lastmodified
    FROM {$db_prefix}down_comment
    WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);

   // Comment information
	$context['downloads_comment'] = array(
		'ID_COMMENT' => $row['ID_COMMENT'],
		'ID_FILE' => $row['ID_FILE'],
		'ID_MEMBER' => $row['ID_MEMBER'],
		'approved' => $row['approved'],
		'comment' => $row['comment'],
	);

	mysql_free_result($dbresult);



	if ($g_manage || $g_edit_comment && $context['downloads_comment']['ID_MEMBER'] == $ID_MEMBER)
	{
		$context['sub_template']  = 'edit_comment';

		$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_editcomment'];

		// Check if spellchecking is both enabled and actually working.
		$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

		/// Used for the editor
		require_once($sourcedir . '/Subs-Post.php');
		$context['post_box_name'] = 'comment';
		$context['post_form'] = 'cprofile';
	}
	else
		fatal_error($txt['downloads_error_nocomedit_permission']);



}

function Downloads_EditComment2()
{
	global $context, $txt, $db_prefix, $ID_MEMBER, $scripturl, $func;

	is_not_guest();

	$g_manage = allowedTo('downloads_manage');
	$g_edit_comment = allowedTo('downloads_editcomment');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);


	// Check if allowed to edit the comment
    $dbresult = db_query("
    SELECT
    	ID_MEMBER,ID_FILE
    FROM {$db_prefix}down_comment
    WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);

   // Comment information
	$context['downloads_comment'] = array(
		'ID_FILE' => $row['ID_FILE'],
		'ID_MEMBER' => $row['ID_MEMBER'],

	);

	mysql_free_result($dbresult);

	if ($g_manage || $g_edit_comment && $context['downloads_comment']['ID_MEMBER'] == $ID_MEMBER)
	{

		$comment = $func['htmlspecialchars']($_REQUEST['comment'],ENT_QUOTES);
		if ($comment == '')
			fatal_error($txt['downloads_error_no_comment'],false);

		$edittime = time();
		// Check if you have automatic approval
		$approved = (allowedTo('downloads_autocomment') ? 1 : 0);
		// Update the comment
	  $dbresult = db_query("UPDATE {$db_prefix}down_comment
		SET comment = '$comment', lastmodified = '$edittime',modified_ID_MEMBER = $ID_MEMBER, approved =  $approved WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);
		// Redirect to the file
		redirectexit('action=downloads;sa=view;down=' .  $context['downloads_comment']['ID_FILE']);
	}
	else
		fatal_error($txt['downloads_error_nocomedit_permission']);
}

function Downloads_DeleteComment()
{
	global $context, $db_prefix, $txt, $scripturl, $modSettings;

	is_not_guest();
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (isset($_REQUEST['ret']))
		$ret = $_REQUEST['ret'];

	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);


	// Get the file ID for redirect
	$dbresult = db_query("
	SELECT
		ID_FILE,ID_COMMENT, ID_MEMBER
	FROM {$db_prefix}down_comment
	WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$fileid = $row['ID_FILE'];
	$memID = $row['ID_MEMBER'];
	mysql_free_result($dbresult);

	// Delete all the comment reports that comment
	db_query("DELETE FROM {$db_prefix}down_creport WHERE ID_COMMENT = $id", __FILE__, __LINE__);
	// Now delete the comment.
	db_query("DELETE FROM {$db_prefix}down_comment WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);


	// Update Comment total
	  $dbresult = db_query("UPDATE {$db_prefix}down_file
		SET commenttotal = commenttotal - 1 WHERE ID_FILE = $fileid LIMIT 1", __FILE__, __LINE__);

	  // Update the SMF Shop Points
			if (isset($modSettings['shopVersion']))
 				db_query("UPDATE {$db_prefix}members
				 	SET money = money - " . $modSettings['down_shop_commentadd'] . "
				 	WHERE ID_MEMBER = $memID
				 	LIMIT 1", __FILE__, __LINE__);

	// Redirect to the download
	if (empty($ret))
		redirectexit('action=downloads;sa=view;down=' . $fileid);
	else
		redirectexit('action=downloads;sa=commentlist');

}

function Downloads_ReportComment()
{
	global $context, $mbname, $txt;

	isAllowedTo('downloads_report');

	// Guest's can't report comments
	is_not_guest();


	$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);

	$context['downloads_comment_id'] = $id;

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_reportcomment'];

	$context['sub_template']  = 'report_comment';
}

function Downloads_ReportComment2()
{
	global $scripturl, $db_prefix, $ID_MEMBER, $txt, $func;

	isAllowedTo('downloads_report');

	$comment = $func['htmlspecialchars']($_REQUEST['comment'],ENT_QUOTES);
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);

	if (empty($comment))
		fatal_error($txt['downloads_error_no_comment'],false);

	$dbresult = db_query("
	SELECT
		ID_FILE
	FROM {$db_prefix}down_comment
	WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$fileid = $row['ID_FILE'];
	mysql_free_result($dbresult);


	$commentdate = time();

	db_query("INSERT INTO {$db_prefix}down_creport
			(ID_MEMBER, comment, date, ID_COMMENT, ID_FILE)
		VALUES ($ID_MEMBER,'$comment', $commentdate,$id,$fileid)", __FILE__, __LINE__);

	redirectexit('action=downloads;sa=view;down=' . $fileid);

}

function Downloads_ApproveComment()
{
	global $scripturl, $db_prefix, $txt;
	isAllowedTo('downloads_manage');


	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_com_selected']);

	// Approve the comment
	db_query("UPDATE {$db_prefix}down_comment
		SET approved = 1 WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);

	// Reditrect the comment list
	redirectexit('action=downloads;sa=commentlist');
}

function Downloads_CommentList()
{
	global $context, $mbname, $txt, $db_prefix, $scripturl;

	isAllowedTo('downloads_manage');
	adminIndex('downloads_settings');

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_approvecomments'];
	$context['sub_template']  = 'comment_list';


	$context['start'] = (int) $_REQUEST['start'];

		// Get Total Pages
		$dbresult = db_query("
		SELECT
			COUNT(*) AS total
		FROM {$db_prefix}down_comment
		WHERE approved = 0 ORDER BY ID_COMMENT DESC", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
		$total = $row['total'];
		mysql_free_result($dbresult);
		$context['downloads_total'] = $total;

		$dbresult = db_query("
		SELECT
			c.ID_COMMENT, c.ID_FILE, c.comment, c.date, c.ID_MEMBER, m.realName
		FROM {$db_prefix}down_comment as c
			LEFT JOIN {$db_prefix}members AS m ON (c.ID_MEMBER = m.ID_MEMBER)
		WHERE c.approved = 0 ORDER BY c.ID_COMMENT DESC LIMIT $context[start],10", __FILE__, __LINE__);
		$context['downloads_comments'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_comments'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'ID_COMMENT' => $row['ID_COMMENT'],
			'ID_MEMBER' => $row['ID_MEMBER'],
			'realName' => $row['realName'],
			'date' => $row['date'],
			'comment' => $row['comment'],

			);
		}

		mysql_free_result($dbresult);

		$context['page_index'] = constructPageIndex($scripturl . '?action=downloads;sa=commentlist', $_REQUEST['start'], $total, 10);


	// Reported Comments
	$dbresult = db_query("
	SELECT
		c.ID, c.ID_FILE, c.ID_COMMENT,  c.ID_MEMBER, m.realName, c.date,c.comment,
		d.comment OringalComment
	FROM ({$db_prefix}down_creport as c, {$db_prefix}down_comment AS d)
	LEFT JOIN {$db_prefix}members AS m on  (c.ID_MEMBER = m.ID_MEMBER)
	WHERE  c.ID_COMMENT = d.ID_COMMENT
	ORDER BY c.ID_FILE DESC", __FILE__, __LINE__);
	$context['downloads_reports'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_reports'][] = array(
			'ID' => $row['ID'],
			'ID_FILE' => $row['ID_FILE'],
			'ID_COMMENT' => $row['ID_COMMENT'],
			'ID_MEMBER' => $row['ID_MEMBER'],
			'realName' => $row['realName'],
			'date' => $row['date'],
			'comment' => $row['comment'],
			'OringalComment' => $row['OringalComment'],

			);
		}

	mysql_free_result($dbresult);


	DoDownloadsAdminTabs();

}

function Downloads_AdminSettings()
{
	global $context, $mbname, $txt;
	isAllowedTo('downloads_manage');

	adminIndex('downloads_settings');
	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_settings'];

	DoDownloadsAdminTabs();

	$context['sub_template']  = 'settings';

}

function Downloads_AdminSettings2()
{
	global $scripturl;

	isAllowedTo('downloads_manage');

	// Get the settings
	$down_max_filesize =  (int) $_REQUEST['down_max_filesize'];
	$down_set_files_per_page = (int) $_REQUEST['down_set_files_per_page'];
	$down_commentchoice =  isset($_REQUEST['down_commentchoice']) ? 1 : 0;
	$down_path = $_REQUEST['down_path'];
	$down_url = $_REQUEST['down_url'];
	$down_who_viewing = isset($_REQUEST['down_who_viewing']) ? 1 : 0;

	$down_set_commentsnewest = isset($_REQUEST['down_set_commentsnewest']) ? 1 : 0;
	$down_set_enable_multifolder = isset($_REQUEST['down_set_enable_multifolder']) ? 1 : 0;
	$down_show_ratings =  isset($_REQUEST['down_show_ratings']) ? 1 : 0;
	$down_index_toprated =  isset($_REQUEST['down_index_toprated']) ? 1 : 0;
	$down_index_recent =   isset($_REQUEST['down_index_recent']) ? 1 : 0;
	$down_index_mostviewed =  isset($_REQUEST['down_index_mostviewed']) ? 1 : 0;
	$downloads_index_mostdownloaded = isset($_REQUEST['downloads_index_mostdownloaded']) ? 1 : 0;
	$down_index_mostcomments = isset($_REQUEST['down_index_mostcomments']) ? 1 : 0;
	$down_index_showtop = isset($_REQUEST['down_index_showtop']) ? 1 : 0;
	$down_set_show_quickreply = isset($_REQUEST['down_set_show_quickreply']) ? 1 : 0;
	$down_set_cat_width = (int) $_REQUEST['down_set_cat_width'];
	$down_set_cat_height = (int) $_REQUEST['down_set_cat_height'];
	// Category view category settings
	$down_set_t_downloads = isset($_REQUEST['down_set_t_downloads']) ? 1 : 0;
	$down_set_t_views = isset($_REQUEST['down_set_t_views']) ? 1 : 0;
	$down_set_t_filesize = isset($_REQUEST['down_set_t_filesize']) ? 1 : 0;
	$down_set_t_date = isset($_REQUEST['down_set_t_date']) ? 1 : 0;
	$down_set_t_comment = isset($_REQUEST['down_set_t_comment']) ? 1 : 0;
	$down_set_t_username = isset($_REQUEST['down_set_t_username']) ? 1 : 0;
	$down_set_t_rating = isset($_REQUEST['down_set_t_rating']) ? 1 : 0;
	$down_set_t_title = isset($_REQUEST['down_set_t_title']) ? 1 : 0;
	$down_set_count_child = isset($_REQUEST['down_set_count_child']) ? 1 : 0;

	// Download display settings
	$down_set_file_prevnext = isset($_REQUEST['down_set_file_prevnext']) ? 1 : 0;
	$down_set_file_desc = isset($_REQUEST['down_set_file_desc']) ? 1 : 0;
	$down_set_file_title = isset($_REQUEST['down_set_file_title']) ? 1 : 0;
	$down_set_file_views = isset($_REQUEST['down_set_file_views']) ? 1 : 0;
	$down_set_file_downloads = isset($_REQUEST['down_set_file_downloads']) ? 1 : 0;
	$down_set_file_lastdownload = isset($_REQUEST['down_set_file_lastdownload']) ? 1 : 0;
	$down_set_file_poster = isset($_REQUEST['down_set_file_poster']) ? 1 : 0;
	$down_set_file_date = isset($_REQUEST['down_set_file_date']) ? 1 : 0;
	$down_set_file_showfilesize = isset($_REQUEST['down_set_file_showfilesize']) ? 1 : 0;
	$down_set_file_showrating = isset($_REQUEST['down_set_file_showrating']) ? 1 : 0;
	$down_set_file_keywords = isset($_REQUEST['down_set_file_keywords']) ? 1 : 0;

	// Shop settings
	$down_shop_fileadd = (int) $_REQUEST['down_shop_fileadd'];
	$down_shop_commentadd = (int) $_REQUEST['down_shop_commentadd'];

	// Download Linking codes
	$down_set_showcode_directlink = isset($_REQUEST['down_set_showcode_directlink']) ? 1 : 0;
	$down_set_showcode_htmllink = isset($_REQUEST['down_set_showcode_htmllink']) ? 1 : 0;

	if (empty($down_set_cat_height))
		$down_set_cat_height = 120;

	if (empty($down_set_cat_width))
		$down_set_cat_width = 120;


	// Save the setting information
	updateSettings(
	array(
	'down_max_filesize' => $down_max_filesize,
	'down_path' => $down_path,
	'down_url' => $down_url,
	'down_commentchoice' => $down_commentchoice,
	'down_who_viewing' => $down_who_viewing,
	'down_set_count_child' => $down_set_count_child,
	'down_show_ratings' => $down_show_ratings,
	'down_index_toprated' => $down_index_toprated,
	'down_index_recent' => $down_index_recent,
	'down_index_mostviewed' => $down_index_mostviewed,
	'downloads_index_mostdownloaded' => $downloads_index_mostdownloaded,
	'down_index_mostcomments' => $down_index_mostcomments,
	'down_index_showtop' => $down_index_showtop,

	'down_set_files_per_page' => $down_set_files_per_page,
	'down_set_commentsnewest' => $down_set_commentsnewest,
	'down_set_show_quickreply' => $down_set_show_quickreply,
	'down_set_enable_multifolder' => $down_set_enable_multifolder,

	'down_set_cat_height' => $down_set_cat_height,
	'down_set_cat_width' => $down_set_cat_width,
	'down_set_t_downloads' => $down_set_t_downloads,
	'down_set_t_views' => $down_set_t_views,
	'down_set_t_filesize' => $down_set_t_filesize,
	'down_set_t_date' => $down_set_t_date,
	'down_set_t_comment' => $down_set_t_comment,
	'down_set_t_username' => $down_set_t_username,
	'down_set_t_rating' => $down_set_t_rating,
	'down_set_t_title' => $down_set_t_title,
	'down_set_file_prevnext' => $down_set_file_prevnext,
	'down_set_file_desc' => $down_set_file_desc,
	'down_set_file_title' => $down_set_file_title,
	'down_set_file_views' => $down_set_file_views,
	'down_set_file_downloads' => $down_set_file_downloads,
	'down_set_file_lastdownload' => $down_set_file_lastdownload,
	'down_set_file_poster' => $down_set_file_poster,
	'down_set_file_date' => $down_set_file_date,
	'down_set_file_showfilesize' => $down_set_file_showfilesize,
	'down_set_file_showrating' => $down_set_file_showrating,
	'down_set_file_keywords' => $down_set_file_keywords,
	'down_shop_commentadd' => $down_shop_commentadd,
	'down_shop_fileadd' => $down_shop_fileadd,
	'down_set_showcode_directlink' => $down_set_showcode_directlink,
	'down_set_showcode_htmllink' => $down_set_showcode_htmllink,

	));

	redirectexit('action=downloads;sa=adminset');

}

function Downloads_CatUp()
{
	global $db_prefix, $scripturl, $txt;
	// Check if they are allowed to manage cats
	isAllowedTo('downloads_manage');

	// Get the category id
	$cat = (int) $_REQUEST['cat'];

	Downloads_ReOrderCats($cat);

	// Check if there is a category above it
	// First get our row order
	$dbresult1 = db_query("
	SELECT
		roworder,ID_PARENT
	FROM {$db_prefix}down_cat
	WHERE ID_CAT = $cat", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o--;

	mysql_free_result($dbresult1);
	$dbresult = db_query("
	SELECT
		ID_CAT, roworder
	FROM {$db_prefix}down_cat
	WHERE ID_PARENT = $ID_PARENT AND roworder = $o", __FILE__, __LINE__);
	if (db_affected_rows()== 0)
		fatal_error($txt['downloads_error_nocat_above'],false);
	$row2 = mysql_fetch_assoc($dbresult);


	// Swap the order Id's
	db_query("UPDATE {$db_prefix}down_cat
		SET roworder = $oldrow WHERE ID_CAT = " .$row2['ID_CAT'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}down_cat
		SET roworder = $o WHERE ID_CAT = $cat", __FILE__, __LINE__);


	mysql_free_result($dbresult);

	// Redirect to index to view cats
	redirectexit('action=downloads;cat=' . $ID_PARENT);
}

function Downloads_CatDown()
{
	global $db_prefix, $scripturl, $txt;

	// Check if they are allowed to manage cats
	isAllowedTo('downloads_manage');

	// Get the cat id
	$cat = (int) $_REQUEST['cat'];

	Downloads_ReOrderCats($cat);

	// Check if there is a category below it
	// First get our row order
	$dbresult1 = db_query("
	SELECT
		ID_PARENT, roworder
	FROM {$db_prefix}down_cat
	WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o++;

	mysql_free_result($dbresult1);
	$dbresult = db_query("
	SELECT
		ID_CAT, roworder
	FROM {$db_prefix}down_cat
	WHERE ID_PARENT = $ID_PARENT AND roworder = $o", __FILE__, __LINE__);
	if (db_affected_rows()== 0)
		fatal_error($txt['downloads_error_nocat_below'],false);
	$row2 = mysql_fetch_assoc($dbresult);


	// Swap the order Id's
	db_query("UPDATE {$db_prefix}down_cat
		SET roworder = $oldrow WHERE ID_CAT = " .$row2['ID_CAT'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}down_cat
		SET roworder = $o WHERE ID_CAT = $cat", __FILE__, __LINE__);


	mysql_free_result($dbresult);


	// Redirect to index to view cats
	redirectexit('action=downloads;cat=' . $ID_PARENT);
}

function Downloads_MyFiles()
{
	global $context, $mbname, $txt, $db_prefix, $ID_MEMBER, $modSettings, $scripturl;

	isAllowedTo('downloads_view');



	$u = (int) $_REQUEST['u'];
	if (empty($u))
		fatal_error($txt['downloads_error_no_user_selected']);

	// Get the downloads userid
	$context['downloads_userid'] = $u;

    $dbresult = db_query("
    SELECT
    	realName
    FROM {$db_prefix}members
    WHERE ID_MEMBER = $u LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$context['downloads_userdownloads_name'] = $row['realName'];
	mysql_free_result($dbresult);

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $context['downloads_userdownloads_name'];

	$context['sub_template']  = 'myfiles';


	// Get userid
	$userid = $context['downloads_userid'];

	$context['start'] = (int) $_REQUEST['start'];

	// Get Total Pages
	$extra_page = '';
	if ($ID_MEMBER == $userid)
		$extra_page = '';
	else
		$extra_page = ' AND p.approved = 1';

	$dbresult = db_query("
	SELECT
		COUNT(*) AS total
	FROM {$db_prefix}down_file as p
	WHERE p.ID_MEMBER = $userid " . $extra_page, __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$total = $row['total'];
	mysql_free_result($dbresult);
	$context['downloads_total'] = $total;


	// Check if it is the user ids downloads mainly to show unapproved downloads or not
	if ($ID_MEMBER == $userid)
    	$dbresult = db_query("
    	SELECT
    		p.ID_FILE, p.commenttotal, p.filesize, p.approved, p.views, p.ID_MEMBER,
    		 m.realName, p.date, p.totaldownloads, p.rating, p.totalratings, p.title
    	FROM {$db_prefix}down_file as p, {$db_prefix}members AS m
    	WHERE p.ID_MEMBER = $userid AND p.ID_MEMBER = m.ID_MEMBER
    	ORDER BY p.ID_FILE DESC LIMIT $context[start]," . $modSettings['down_set_files_per_page'], __FILE__, __LINE__);
	else
    	$dbresult = db_query("
    	SELECT
    		p.ID_FILE, p.commenttotal, p.filesize, p.approved, p.views,
    		p.ID_MEMBER, m.realName, p.date, p.totaldownloads, p.rating, p.totalratings, p.title
    	FROM {$db_prefix}down_file as p, {$db_prefix}members AS m
    	WHERE p.ID_MEMBER = $userid AND p.ID_MEMBER = m.ID_MEMBER AND p.approved = 1
    	ORDER BY p.ID_FILE DESC LIMIT $context[start]," . $modSettings['down_set_files_per_page'], __FILE__, __LINE__);

    	$context['downloads_files'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_files'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'totalratings' => $row['totalratings'],
			'rating' => $row['rating'],
			'commenttotal' => $row['commenttotal'],
			'filesize' => $row['filesize'],
			'views' => $row['views'],
			'ID_MEMBER' => $row['ID_MEMBER'],
			'realName' => $row['realName'],
			'date' => $row['date'],
			'totaldownloads' => $row['totaldownloads'],
			'approved' => $row['approved'],

			);

		}
		mysql_free_result($dbresult);

		$context['page_index'] = constructPageIndex($scripturl . '?action=downloads;sa=myfiles;u=' . $context['downloads_userid'], $_REQUEST['start'], $total, $modSettings['down_set_files_per_page']);

}

function Downloads_ApproveList()
{
	global $context, $mbname, $txt, $db_prefix, $scripturl;

	isAllowedTo('downloads_manage');

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_approvedownloads'];

	adminIndex('downloads_settings');

	$context['sub_template']  = 'approvelist';

	DoDownloadsAdminTabs();

	$context['start'] = (int) $_REQUEST['start'];

	// Get Total Pages
		$dbresult = db_query("
		SELECT
			COUNT(*) AS total
		FROM {$db_prefix}down_file as p
		WHERE p.approved = 0 ORDER BY ID_FILE DESC", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
		$total = $row['total'];
		mysql_free_result($dbresult);
	$context['downloads_total'] = $total;

	// List all the unapproved downloads
	$dbresult = db_query("
	SELECT
		p.ID_FILE, p.ID_CAT, p.title, p.ID_MEMBER, m.realName, p.date, p.description, c.title catname
	FROM {$db_prefix}down_file AS p
		LEFT JOIN {$db_prefix}members AS m ON (m.ID_MEMBER = p.ID_MEMBER)
		LEFT JOIN {$db_prefix}down_cat AS c ON (c.ID_CAT = p.ID_CAT)
	WHERE p.approved = 0
	ORDER BY p.ID_FILE DESC LIMIT $context[start],10", __FILE__, __LINE__);
	$context['downloads_file'] = array();
	 while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_file'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'ID_MEMBER' => $row['ID_MEMBER'],
			'realName' => $row['realName'],
			'date' => $row['date'],
			'description' => $row['description'],
			'catname' => $row['catname'],
			);
		}
	mysql_free_result($dbresult);

	$context['page_index'] = constructPageIndex($scripturl . '?action=downloads;sa=approvelist', $_REQUEST['start'], $total, 10);


}

function Downloads_ApproveDownload()
{
	global $txt;
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	// Approve the download
	Downloads_ApproveFileByID($id);

	// Redirect to approval list
	redirectexit('action=downloads;sa=approvelist');

}

function Downloads_ApproveFileByID($id)
{
	global $scripturl, $db_prefix, $txt, $modSettings, $boardurl, $sourcedir, $user_info;

	// Look up the download and get the category
	$dbresult = db_query("
	SELECT
		p.ID_FILE, p.ID_MEMBER, p.filename, p.title, p.description, c.ID_BOARD,
		p.ID_CAT, c.locktopic
	FROM {$db_prefix}down_file AS p
	LEFT JOIN {$db_prefix}down_cat AS c ON (c.ID_CAT = p.ID_CAT)
	WHERE p.ID_FILE = $id LIMIT 1", __FILE__, __LINE__);
	$rowcat = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);

	if ($rowcat['ID_BOARD'] != 0  && $rowcat['ID_BOARD'] != '' )
	{

		$showpostlink = '\n\n' . $scripturl . '?action=downloads;sa=view;down=' . $id;


					// Create the post
					require_once($sourcedir . '/Subs-Post.php');
					$msgOptions = array(
						'id' => 0,
						'subject' => $rowcat['title'],
						'body' => '[b]' . $rowcat['title'] . "[/b]\n\n$showpostlink\n\n" . $rowcat['description'],
						'icon' => 'xx',
						'smileys_enabled' => 1,
						'attachments' => array(),
					);
					$topicOptions = array(
						'id' => 0,
						'board' => $rowcat['ID_BOARD'],
						'poll' => null,
						'lock_mode' => $rowcat['locktopic'],
						'sticky_mode' => null,
						'mark_as_read' => true,
					);
					$posterOptions = array(
						'id' => $rowcat['ID_MEMBER'],
						'update_post_count' => !$user_info['is_guest'] && !isset($_REQUEST['msg']),
					);


					preparsecode($msgOptions['body']);
					createPost($msgOptions, $topicOptions, $posterOptions);

				}


	// Update the approval
	db_query("UPDATE {$db_prefix}down_file SET approved = 1 WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);


	Downloads_UpdateCategoryTotals($rowcat['ID_CAT']);

}

function Downloads_UnApproveDownload()
{
	global $scripturl, $db_prefix, $txt;
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	Downloads_UnApproveFileByID($id);

	// Redirect to approval list
	redirectexit('action=downloads;sa=approvelist');
}

function Downloads_UnApproveFileByID($id)
{
	global $db_prefix;
	// Update the approval
	db_query("UPDATE {$db_prefix}down_file SET approved = 0 WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);

	Downloads_UpdateCategoryTotalByFileID($id);
}

function Downloads_ReportList()
{
	global $context, $mbname, $txt, $db_prefix;

	isAllowedTo('downloads_manage');

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_reportdownloads'];

	adminIndex('downloads_settings');

	$context['sub_template']  = 'reportlist';

	$dbresult = db_query("
	SELECT
		r.ID, r.ID_FILE, r.ID_MEMBER, m.realName, r.date, r.comment
	FROM {$db_prefix}down_report as r
		  LEFT JOIN {$db_prefix}members AS m ON  (m.ID_MEMBER = r.ID_MEMBER)
	ORDER BY r.ID_FILE DESC", __FILE__, __LINE__);

	$context['downloads_reports'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
	{
			$context['downloads_reports'][] = array(
			'ID' => $row['ID'],
			'ID_FILE' => $row['ID_FILE'],
			'comment' => $row['comment'],
			'ID_MEMBER' => $row['ID_MEMBER'],
			'realName' => $row['realName'],
			'date' => $row['date'],


			);
	}
	mysql_free_result($dbresult);

	DoDownloadsAdminTabs();

}

function Downloads_DeleteReport()
{
	global $scripturl, $db_prefix, $txt;
	// Check the permission
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_report_selected']);

	db_query("DELETE FROM {$db_prefix}down_report WHERE ID = $id LIMIT 1", __FILE__, __LINE__);

	// Redirect to redirect list
	redirectexit('action=downloads;sa=reportlist');
}

function Downloads_DeleteCommentReport()
{
	global $scripturl, $db_prefix, $txt;
	// Check the permission
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_report_selected']);

	db_query("DELETE FROM {$db_prefix}down_creport WHERE ID = $id LIMIT 1", __FILE__, __LINE__);

	// Redirect to redirect list
	redirectexit('action=downloads;sa=commentlist');
}

function Downloads_Search()
{
	global $context, $mbname, $txt, $db_prefix, $user_info;



	// Is the user allowed to view the downloads?
	isAllowedTo('downloads_view');

	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

	$dbresult = db_query("
	SELECT
		c.ID_CAT, c.title, p.view
	FROM {$db_prefix}down_cat as c
	LEFT JOIN {$db_prefix}down_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
	ORDER BY c.roworder ASC", __FILE__, __LINE__);
	$context['downloads_cat'] = array();
	 while($row = mysql_fetch_assoc($dbresult))
		{
			// Check if they have permission to search these categories
			if ($row['view'] == '0')
					continue;

			$context['downloads_cat'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title']
			);
		}
	mysql_free_result($dbresult);

	$context['sub_template']  = 'search';

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_search'];
}

function Downloads_Search2()
{
	global $context, $mbname, $txt, $db_prefix, $modSettings, $func, $user_info;

	// Is the user allowed to view the downloads?
	isAllowedTo('downloads_view');


	if (!$user_info['is_guest'])
		$groupsdata = implode(',',$user_info['groups']);
	else
		$groupsdata = -1;


	if (isset($_REQUEST['q']))
	{
		$data = json_decode(base64_decode($_REQUEST['q']),true);
		@$_REQUEST['cat'] = $data['cat'];
		@$_REQUEST['key'] = $data['keyword'];
		if (!empty($data['searchkeywords']))
			@$_REQUEST['searchkeywords'] = $data['searchkeywords'];

		if (!empty($data['searchtitle']))
			@$_REQUEST['searchtitle'] = $data['searchtitle'];

		if (!empty($data['searchdescription']))
			@$_REQUEST['searchdescription'] = $data['searchdescription'];

		if (!empty($data['searchcustom']))
			@$_REQUEST['searchcustom'] = $data['searchcustom'];

		@$_REQUEST['daterange'] = $data['daterange'];
		@$_REQUEST['pic_postername'] = $data['pic_postername'];
		@$_REQUEST['searchfor'] = $data['searchfor'];

	}

		@$cat = (int) $_REQUEST['cat'];

		// Check if keyword search was selected
		@$keyword =  $func['htmlspecialchars']($_REQUEST['key'],ENT_QUOTES);

		$searchArray = array();
		$searchArray['keyword'] = $keyword;
		$context['downloads_search_query_encoded'] = base64_encode(json_encode($searchArray));


		if ($keyword == '')
		{
			// Probably a normal Search
			if (empty($_REQUEST['searchfor']))
				fatal_error($txt['downloads_error_no_search'],false);

			$searchfor =  $func['htmlspecialchars']($_REQUEST['searchfor'],ENT_QUOTES);


			if (strlen($searchfor) <= 3)
				fatal_error($txt['downloads_error_search_small'],false);

			// Check the search options
			@$searchkeywords = $_REQUEST['searchkeywords'];
			@$searchtitle = $_REQUEST['searchtitle'];
			@$searchdescription = $_REQUEST['searchdescription'];
			@$daterange = (int) $_REQUEST['daterange'];
			$memid = 0;

			// Check if searching by member id
			if (!empty($_REQUEST['pic_postername']))
			{
				$pic_postername = str_replace('"','', $_REQUEST['pic_postername']);
				$pic_postername = str_replace("'",'', $pic_postername);
				$pic_postername = str_replace('\\','', $pic_postername);
				$pic_postername = $func['htmlspecialchars']($pic_postername, ENT_QUOTES);
				$searchArray['pic_postername'] = $pic_postername;


				$dbresult = db_query("
						SELECT
							realName, ID_MEMBER
						FROM {$db_prefix}members
						WHERE realName = '$pic_postername' OR memberName = '$pic_postername'  LIMIT 1", __FILE__, __LINE__);
						$row = mysql_fetch_assoc($dbresult);
						mysql_free_result($dbresult);

					if (db_affected_rows() != 0)
					{
						$memid = $row['ID_MEMBER'];
					}

			}


			$searchArray['searchfor'] = $searchfor;
			$searchArray['searchkeywords'] = $searchkeywords;
			$searchArray['cat'] = $cat;
			$searchArray['searchtitle'] = $searchtitle;
			$searchArray['searchdescription'] = $searchdescription;
			$searchArray['daterange'] = $daterange;
			$context['downloads_search_query_encoded'] = base64_encode(json_encode($searchArray));



			$context['catwhere'] = '';


			if ($cat != 0)
				$context['catwhere'] = "p.ID_CAT = $cat AND ";

			// Check if searching by member id
			if ($memid != 0)
				$context['catwhere'] .= "p.ID_MEMBER = $memid AND ";

			// Date Range check
			if ($daterange!= 0)
			{
				$currenttime = time();
				$pasttime = $currenttime - ($daterange * 24 * 60 * 60);

				$context['catwhere'] .=  "(p.date BETWEEN '" . $pasttime . "' AND '" . $currenttime . "')  AND";
			}

			$s1 = 1;
			$searchquery = '';
			if ($searchtitle)
				$searchquery = "p.title LIKE '%$searchfor%' ";
			else
				$s1 = 0;

			$s2 = 1;
			if ($searchdescription)
			{
				if ($s1 == 1)
					$searchquery = "p.title LIKE '%$searchfor%' OR p.description LIKE '%$searchfor%'";
				else
					$searchquery = "p.description LIKE '%$searchfor%'";
			}
			else
				$s2 = 0;

			if ($searchkeywords)
			{
				if ($s1 == 1 || $s2 == 1)
					$searchquery .= " OR p.keywords LIKE '$searchfor'";
				else
					$searchquery = "p.keywords LIKE '$searchfor'";
			}


			if ($searchquery == '')
				$searchquery = "p.title LIKE '%$searchfor%' ";

			$context['downloads_search_query'] = $searchquery;



			$context['downloads_search'] = $searchfor;
		}
		else
		{
			// Search for the keyword


			//Debating if I should add string length check for keywords...
			//if (strlen($keyword) <= 3)
				//fatal_error($txt['downloads_error_search_small']);

			$context['downloads_search'] = $keyword;

			$context['downloads_search_query'] = "p.keywords LIKE '$keyword'";
		}



	$downloads_where = '';
	if (isset($context['catwhere']))
		$downloads_where = $context['catwhere'];

	$context['downloads_where'] = $downloads_where;


	$context['start'] = (int) $_REQUEST['start'];

    $dbresult = db_query("
    SELECT
    	p.ID_FILE
    FROM {$db_prefix}down_file as p
    	LEFT JOIN {$db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupsdata) AND c.ID_CAT = p.ID_CAT)
    WHERE  " . $downloads_where . " p.approved = 1 AND (c.view IS NULL OR c.view =1) AND  (" . $context['downloads_search_query'] . ") GROUP by p.ID_FILE", __FILE__, __LINE__);
    $numrows = mysql_num_rows($dbresult);
    mysql_free_result($dbresult);

    $total = $numrows;
	$context['downloads_total'] = $total;


    $dbresult = db_query("
    SELECT
    	p.ID_FILE, p.ID_CAT, p.commenttotal, p.rating, p.filesize, p.title,
    	p.views, p.ID_MEMBER, m.realName, p.date, p.totaldownloads, p.totalratings
    FROM {$db_prefix}down_file as p
   	 	LEFT JOIN {$db_prefix}members AS m ON (m.ID_MEMBER = p.ID_MEMBER)
   	 	LEFT JOIN {$db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupsdata) AND c.ID_CAT = p.ID_CAT)

    WHERE  " . $downloads_where . " p.approved = 1 AND (c.view IS NULL OR c.view =1) AND (" . $context['downloads_search_query'] . ") GROUP by p.ID_FILE
    LIMIT $context[start],10", __FILE__, __LINE__);
    $context['downloads_files'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_files'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'totalratings' => $row['totalratings'],
			'rating' => $row['rating'],
			'commenttotal' => $row['commenttotal'],
			'filesize' => $row['filesize'],
			'views' => $row['views'],
			'ID_MEMBER' => $row['ID_MEMBER'],
			'realName' => $row['realName'],
			'date' => $row['date'],
			'totaldownloads' => $row['totaldownloads'],

			);

		}
	mysql_free_result($dbresult);


	$context['sub_template']  = 'search_results';

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_searchresults'];
}

function Downloads_RateDownload()
{
	global $scripturl, $db_prefix, $txt, $ID_MEMBER;

	is_not_guest();

	// Check if they are allowed to rate download
	isAllowedTo('downloads_ratefile');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);
	$rating = (int) $_REQUEST['rating'];
	if (empty($rating))
		fatal_error($txt['downloads_error_no_rating_selected']);

	// Check if they rated this download?
    $dbresult = db_query("
    SELECT
    	ID_MEMBER, ID_FILE
    FROM {$db_prefix}down_rating
    WHERE ID_MEMBER = $ID_MEMBER AND ID_FILE = $id", __FILE__, __LINE__);

    $found = db_affected_rows();
 	mysql_free_result($dbresult);

	// Get the download owner
    $dbresult = db_query("
    SELECT
    	ID_MEMBER
    FROM {$db_prefix}down_file
    WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);
    $row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	// Check if they are rating their own download.
	if ($ID_MEMBER == $row['ID_MEMBER'])
		fatal_error($txt['downloads_error_norate_own'],false);

	if ($found != 0)
		fatal_error($txt['downloads_error_already_rated'],false);

	// Check the Rating
	if ($rating < 1 || $rating > 5)
		$rating = 3;

	// Add the Rating
	db_query("INSERT INTO {$db_prefix}down_rating (ID_MEMBER, ID_FILE, value) VALUES ($ID_MEMBER, $id,$rating)", __FILE__, __LINE__);

	// Add rating information to the download
	db_query("
	UPDATE {$db_prefix}down_file
		SET totalratings = totalratings + 1, rating = rating + $rating
	WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);

	// Redirect to the download
	redirectexit('action=downloads;sa=view;down=' . $id);

}

function Downloads_ViewRating()
{
	global $context, $mbname, $txt, $db_prefix;

	// Get the download ID for the ratings
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	$context['downloads_id'] = $id;

	$dbresult = db_query("
	SELECT
		r.ID, r.value, r.ID_FILE, r.ID_MEMBER, m.realName
	FROM {$db_prefix}down_rating as r, {$db_prefix}members AS m
	WHERE r.ID_FILE = $id AND r.ID_MEMBER = m.ID_MEMBER", __FILE__, __LINE__);
	$context['downloads_rating'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_rating'][] = $row;

		}
	mysql_free_result($dbresult);

	isAllowedTo('downloads_manage');

	$context['sub_template']  = 'view_rating';

	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_form_viewratings'];

}

function Downloads_DeleteRating()
{
	global $db_prefix, $scripturl, $txt;
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_rating_selected']);

	// First lookup the ID to get the download id and value of rating
	 $dbresult = db_query("
	 SELECT
	 	ID, ID_FILE, value
	 FROM {$db_prefix}down_rating
	 WHERE ID = $id LIMIT 1", __FILE__, __LINE__);
	 $row = mysql_fetch_assoc($dbresult);
	 $value = $row['value'];
	 $fileid = $row['ID_FILE'];
	 mysql_free_result($dbresult);
	// Delete the Rating
	db_query("DELETE FROM {$db_prefix}down_rating
	WHERE ID = " . $id . ' LIMIT 1', __FILE__, __LINE__);
	// Update the download rating information
	$dbresult = db_query("UPDATE {$db_prefix}down_file SET totalratings = totalratings - 1, rating = rating - $value WHERE ID_FILE = $fileid LIMIT 1", __FILE__, __LINE__);
	// Redirect to the ratings
	redirectexit('action=downloads;sa=viewrating&id=' .  $fileid);
}

function Downloads_Stats()
{
	global $context, $mbname, $user_info, $txt, $db_prefix, $context, $scripturl;

	// Is the user allowed to view the downloads?
	isAllowedTo('downloads_view');

	if (!$context['user']['is_guest'])
		$groupsdata = implode(',',$user_info['groups']);
	else
		$groupsdata = -1;

	// Get views total and comments total and total filesize
	$result = db_query("
	SELECT
		SUM(views) AS views, SUM(filesize) AS filesize, SUM(commenttotal) AS commenttotal,
	 	COUNT(*) AS filetotal
	FROM {$db_prefix}down_file", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($result);
	mysql_free_result($result);

	$result2 = db_query("
	SELECT
		COUNT(*) AS filetotal
	FROM {$db_prefix}down_file", __FILE__, __LINE__);
	$row2 = mysql_fetch_assoc($result2);
	mysql_free_result($result2);

	$context['total_files'] = $row2['filetotal'];
	$context['total_views'] = $row['views'];
	$context['total_filesize'] =  Downloads_format_size($row['filesize'], 2);
	$context['total_comments'] = $row['commenttotal'];


	// Top Viewed Downloads
	$result = db_query("
	SELECT
		p.ID_FILE, p.title, p.views
	FROM {$db_prefix}down_file as p
	LEFT JOIN {$db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupsdata) AND c.ID_CAT = p.ID_CAT)
	WHERE (p.approved =1  AND (c.view IS NULL OR c.view =1)) AND views > 0 GROUP by p.ID_FILE
	ORDER BY p.views DESC LIMIT 10", __FILE__, __LINE__);
	$context['top_viewed'] = array();
	$max_views = 1;
	while ($row = mysql_fetch_assoc($result))
	{
		$context['top_viewed'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'views' => $row['views'],
			'link' => '<a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);

		if ($max_views < $row['views'])
			$max_views = $row['views'];
	}
	mysql_free_result($result);

	foreach ($context['top_viewed'] as $i => $file)
		$context['top_viewed'][$i]['percent'] = round(($file['views'] * 100) / $max_views);

	// Top Rated
	$result = db_query("
	SELECT
		p.ID_FILE, p.title, p.totalratings, p.rating , (p.rating / p.totalratings ) AS ratingaverage
	FROM {$db_prefix}down_file as p
	LEFT JOIN {$db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupsdata) AND c.ID_CAT = p.ID_CAT)
	WHERE (p.approved =1  AND (c.view IS NULL OR c.view =1)) AND p.totalratings > 0 GROUP by p.ID_FILE
	ORDER BY ratingaverage DESC LIMIT 10", __FILE__, __LINE__);
	$context['top_rating'] = array();
	$max_rating = 1;
	while ($row = mysql_fetch_assoc($result))
	{
		$context['top_rating'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'rating' => $row['rating'],
			'link' => '<a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);

		if ($max_rating < $row['rating'])
			$max_rating = $row['rating'];
	}
	mysql_free_result($result);

	foreach ($context['top_rating'] as $i => $file)
		$context['top_rating'][$i]['percent'] = round(($file['rating'] * 100) / $max_rating);

	// Most Commented
	$result = db_query("
	SELECT
		p.ID_FILE, p.title,p.commenttotal
	FROM {$db_prefix}down_file as p
	LEFT JOIN {$db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupsdata) AND c.ID_CAT = p.ID_CAT)
	WHERE (p.approved =1  AND (c.view IS NULL OR c.view =1)) AND p.commenttotal > 0 GROUP by p.ID_FILE
	ORDER BY p.commenttotal DESC LIMIT 10", __FILE__, __LINE__);
	$context['most_comments'] = array();
	$max_commenttotal = 1;
	while ($row = mysql_fetch_assoc($result))
	{
		$context['most_comments'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'commenttotal' => $row['commenttotal'],
			'link' => '<a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);

		if ($max_commenttotal < $row['commenttotal'])
			$max_commenttotal = $row['commenttotal'];
	}
	mysql_free_result($result);

	foreach ($context['most_comments'] as $i => $file)
		$context['most_comments'][$i]['percent'] = round(($file['commenttotal'] * 100) / $max_commenttotal);

	// Last 10 downloads uploaded
	$result = db_query("
	SELECT
		p.ID_FILE, p.title
	FROM {$db_prefix}down_file as p
	LEFT JOIN {$db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupsdata) AND c.ID_CAT = p.ID_CAT)
	WHERE (p.approved =1  AND (c.view IS NULL OR c.view =1)) GROUP by p.ID_FILE
	ORDER BY p.ID_FILE DESC LIMIT 10", __FILE__, __LINE__);
	$context['last_upload'] = array();
	while ($row = mysql_fetch_assoc($result))
	{
		$context['last_upload'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'link' => '<a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">' . $row['title'] . '</a>',
		);
	}
	mysql_free_result($result);


	// Load the template
	$context['sub_template']  = 'stats';
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_stats'];

}

function Downloads_UpdateUserFileSizeTable($memberid, $filesize)
{
	global $db_prefix;

	// Check if a record exits
	$dbresult = db_query("
	SELECT
		ID_MEMBER,totalfilesize
	FROM {$db_prefix}down_userquota
	WHERE ID_MEMBER = $memberid LIMIT 1", __FILE__, __LINE__);
	$count = db_affected_rows();
	mysql_free_result($dbresult);

	if ($count == 0)
	{
		// Create the record
		db_query("INSERT INTO {$db_prefix}down_userquota (ID_MEMBER, totalfilesize) VALUES ($memberid, $filesize)", __FILE__, __LINE__);
	}
	else
	{
		// Update the record
		if ($filesize >= 0)
			db_query("UPDATE {$db_prefix}down_userquota SET totalfilesize = totalfilesize + $filesize WHERE ID_MEMBER = $memberid LIMIT 1", __FILE__, __LINE__);
		else
			db_query("UPDATE {$db_prefix}down_userquota SET totalfilesize = totalfilesize + $filesize WHERE ID_MEMBER = $memberid LIMIT 1", __FILE__, __LINE__);
	}
}

function Downloads_FileSpaceAdmin()
{
	global $mbname, $txt, $context, $db_prefix, $scripturl;
	// Check if they are allowed to manage the downloads
	isAllowedTo('downloads_manage');
	adminIndex('downloads_settings');
	loadLanguage('Admin');

	// Set the page tile
	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_filespace'];
	// Load the subtemplate for the file manager
	$context['sub_template']  = 'filespace';

	// Load the membergroups
	$dbresult = db_query("
	SELECT
		ID_GROUP, groupName
	FROM {$db_prefix}membergroups
	WHERE minPosts = -1 ORDER BY groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'groupName' => $row['groupName'],
			);
	}
	mysql_free_result($dbresult);


	$dbresult = db_query("
	SELECT
		q.totalfilesize,  q.ID_GROUP, m.groupName
	FROM {$db_prefix}down_groupquota as q, {$db_prefix}membergroups AS m
	WHERE  q.ID_GROUP = m.ID_GROUP ORDER BY q.totalfilesize", __FILE__, __LINE__);
	$context['downloads_membergroups'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_membergroups'][] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'totalfilesize' => $row['totalfilesize'],
			'groupName' => $row['groupName'],


			);

		}
	mysql_free_result($dbresult);

	$dbresult = db_query("
	SELECT
		q.totalfilesize, q.ID_GROUP
	FROM {$db_prefix}down_groupquota as q
	WHERE  q.ID_GROUP = 0 LIMIT 1", __FILE__, __LINE__);
	$context['downloads_reggroup'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_reggroup'][] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'totalfilesize' => $row['totalfilesize'],
			);

		}
	mysql_free_result($dbresult);


	$context['start'] = (int) $_REQUEST['start'];



	// Get Total Pages
	$dbresult = db_query("
	SELECT
		COUNT(*) AS total
	FROM {$db_prefix}down_userquota as q", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$total = $row['total'];
	mysql_free_result($dbresult);
	$context['downloads_total'] = $total;

	$dbresult = db_query("
	SELECT
		q.totalfilesize,  q.ID_MEMBER, m.realName
	FROM {$db_prefix}down_userquota as q, {$db_prefix}members AS m
	WHERE  q.ID_MEMBER = m.ID_MEMBER
	ORDER BY q.totalfilesize DESC  LIMIT $context[start],20", __FILE__, __LINE__);
	$context['downloads_members'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_members'][] = array(
			'ID_MEMBER' => $row['ID_MEMBER'],
			'totalfilesize' => $row['totalfilesize'],
			'realName' => $row['realName'],


			);

		}
	mysql_free_result($dbresult);

	DoDownloadsAdminTabs();

	$context['page_index'] = constructPageIndex($scripturl . '?action=downloads;sa=filespace', $_REQUEST['start'], $total, 20);


}

function Downloads_format_size($size, $round = 0)
{
    //Size must be bytes!
    $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    for ($i=0; $size > 1024 && $i < count($sizes) - 1; $i++) $size /= 1024;
    return round($size,$round).$sizes[$i];
}

function Downloads_FileSpaceList()
{
	global $mbname, $txt, $context, $db_prefix, $scripturl;
	// Check if they are allowed to manage the downloads
	isAllowedTo('downloads_manage');
	adminIndex('downloads_settings');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_user_selected']);

    $dbresult = db_query("
    SELECT
    	m.realName
    FROM {$db_prefix}members AS m
    WHERE m.ID_MEMBER = $id  LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$context['downloads_filelist_realname'] = $row['realName'];
	$context['downloads_filelist_userid'] = $id;
	mysql_free_result($dbresult);

	// Set the page tile
	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_filespace'] . ' - ' . $context['downloads_filelist_realname'];
	// Load the subtemplate for the file manager
	$context['sub_template']  = 'filelist';



	$context['start'] = (int) $_REQUEST['start'];



	// Get Total Pages
	$dbresult = db_query("
	SELECT
		COUNT(*) AS total
	FROM {$db_prefix}down_file
	WHERE ID_MEMBER = " . $context['downloads_filelist_userid'], __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$total = $row['total'];
	mysql_free_result($dbresult);
	$context['downloads_total'] = $total;



	$dbresult = db_query("
	SELECT
		p.ID_FILE,p.title, p.filesize,p.ID_MEMBER
	FROM {$db_prefix}down_file as p
	WHERE p.ID_MEMBER = " . $context['downloads_filelist_userid'] . "
	ORDER BY p.filesize DESC  LIMIT $context[start],20", __FILE__, __LINE__);
	$context['downloads_files'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_files'][] = array(
			'ID_FILE' => $row['ID_FILE'],
			'title' => $row['title'],
			'filesize' => $row['filesize'],
			'ID_MEMBER' => $row['ID_MEMBER'],


			);

		}
	mysql_free_result($dbresult);

	DoDownloadsAdminTabs('filespace');

	$context['page_index'] = constructPageIndex($scripturl . '?action=downloads;sa=filelist&id=' . $context['downloads_filelist_userid'], $_REQUEST['start'], $total, 20);

}

function Downloads_RecountFileQuotaTotals($redirect = true)
{
	global $db_prefix;

	if ($redirect == true)
		isAllowedTo('downloads_manage');

	// Show all the user's with quota information
	$dbresult = db_query("
	SELECT
		ID_MEMBER
	FROM {$db_prefix}down_userquota", __FILE__, __LINE__);
	while($row = mysql_fetch_assoc($dbresult))
	{
		// Loop though the all the files for the member and get the total
		$dbresult2 = db_query("
		SELECT
			SUM(filesize) as total
		FROM {$db_prefix}down_file
		WHERE ID_MEMBER = " . $row['ID_MEMBER'], __FILE__, __LINE__);

		$row2 = mysql_fetch_assoc($dbresult2);
		$total = $row2['total'];

		if ($total == '')
			$total = 0;

		mysql_free_result($dbresult2);
		// Update the quota
		db_query("UPDATE {$db_prefix}down_userquota SET totalfilesize = $total WHERE ID_MEMBER = " . $row['ID_MEMBER'] . " LIMIT 1", __FILE__, __LINE__);

	}
	mysql_free_result($dbresult);

	if ($redirect == true)
		redirectexit('action=downloads;sa=filespace');
}

function Downloads_GetQuotaGroupLimit($memberid)
{
	global $db_prefix;
	$dbresult = db_query("
	SELECT
		m.ID_MEMBER, q.ID_GROUP, q.totalfilesize
	FROM {$db_prefix}down_groupquota as q, {$db_prefix}members as m
	WHERE m.ID_MEMBER = $memberid AND q.ID_GROUP = m.ID_GROUP LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	if (db_affected_rows()== 0)
	{
		mysql_free_result($dbresult);
		return 0;
	}
	else
	{
		mysql_free_result($dbresult);

		return $row['totalfilesize'];
	}

}

function Downloads_GetUserSpaceUsed($memberid)
{
	global $db_prefix;

	$dbresult = db_query("
	SELECT
		ID_MEMBER,totalfilesize
	FROM {$db_prefix}down_userquota
	WHERE ID_MEMBER = $memberid LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	if (db_affected_rows()== 0)
	{
		mysql_free_result($dbresult);
		return 0;
	}
	else
	{
		mysql_free_result($dbresult);

		return $row['totalfilesize'];
	}

}

function Downloads_AddQuota()
{
	global $txt, $db_prefix;
	isAllowedTo('downloads_manage');

	$groupid = (int) $_REQUEST['groupname'];

	$filelimit = (double) $_REQUEST['filelimit'];
	if (empty($filelimit))
	{
		fatal_error($txt['downloads_error_noquota'],false);
	}

	$dbresult = db_query("
	SELECT
		ID_GROUP
	FROM {$db_prefix}down_groupquota
	WHERE ID_GROUP = $groupid LIMIT 1", __FILE__, __LINE__);
	$count = db_affected_rows();
	mysql_free_result($dbresult);

	if ($count == 0)
	{
		// Create the record
		db_query("INSERT INTO {$db_prefix}down_groupquota (ID_GROUP, totalfilesize) VALUES ($groupid, $filelimit)", __FILE__, __LINE__);
	}
	else
	{
		fatal_error($txt['downloads_error_quotaexist'],false);
	}

	redirectexit('action=downloads;sa=filespace');
}

function Downloads_DeleteQuota()
{
	global $db_prefix;
	isAllowedTo('downloads_manage');
	$id = (int) $_REQUEST['id'];

	db_query("DELETE FROM {$db_prefix}down_groupquota WHERE ID_GROUP = " . $id . ' LIMIT 1', __FILE__, __LINE__);

	redirectexit('action=downloads;sa=filespace');
}


function Downloads_ApproveAllComments()
{
	global $scripturl, $db_prefix;
	isAllowedTo('downloads_manage');

	// Approve all the comments
	db_query("UPDATE {$db_prefix}down_comment
		SET approved = 1 WHERE approved = 0", __FILE__, __LINE__);

	// Reditrect the comment list
	redirectexit('action=downloads;sa=commentlist');
}

function Downloads_CatPerm()
{
	global $mbname, $txt, $db_prefix, $context;
	isAllowedTo('downloads_manage');

	$cat = (int) $_REQUEST['cat'];
	if (empty($cat))
		fatal_error($txt['downloads_error_no_cat']);

	$dbresult1 = db_query("
	SELECT
		ID_CAT, title
	FROM {$db_prefix}down_cat
	WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
	$row1 = mysql_fetch_assoc($dbresult1);
	$context['downloads_cat_name'] = $row1['title'];
	// Free the SQL Result
	mysql_free_result($dbresult1);

	loadLanguage('Admin');

	$context['downloads_cat'] = $cat;

	// Load the template
	$context['sub_template']  = 'catperm';
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_catperm'] . ' -' . $context['downloads_cat_name'];

	// Load the membergroups
	$dbresult = db_query("
	SELECT
		ID_GROUP, groupName
	FROM {$db_prefix}membergroups
	WHERE minPosts = -1 ORDER BY groupName", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'groupName' => $row['groupName'],
			);
	}
	mysql_free_result($dbresult);


	// Membergroups
	$dbresult = db_query("
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile, c.addcomment,  c.ID_GROUP, m.groupName,a.title catname
	FROM ({$db_prefix}down_catperm as c, {$db_prefix}membergroups AS m,{$db_prefix}down_cat as a)
	WHERE  c.ID_CAT = " . $context['downloads_cat'] . " AND c.ID_GROUP = m.ID_GROUP AND a.ID_CAT = c.ID_CAT", __FILE__, __LINE__);
	$context['downloads_membergroups'] = array();
	while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_membergroups'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'addcomment' => $row['addcomment'],
			'ID_GROUP' => $row['ID_GROUP'],
			'groupName' => $row['groupName'],
			'catname' => $row['catname'],
			);

		}
	mysql_free_result($dbresult);


	$dbresult = db_query("
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile, c.addcomment,  c.ID_GROUP,a.title catname
	FROM {$db_prefix}down_catperm as c,{$db_prefix}down_cat as a
	WHERE c.ID_CAT = " . $context['downloads_cat'] . " AND c.ID_GROUP = 0 AND a.ID_CAT = c.ID_CAT LIMIT 1", __FILE__, __LINE__);
	$context['downloads_reggroup'] = array();
	while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_reggroup'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'addcomment' => $row['addcomment'],
			'ID_GROUP' => $row['ID_GROUP'],
			'catname' => $row['catname'],
			);

		}
	mysql_free_result($dbresult);


	$dbresult = db_query("
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile, c.addcomment,  c.ID_GROUP,a.title catname
	FROM {$db_prefix}down_catperm as c,{$db_prefix}down_cat as a
	WHERE c.ID_CAT = " . $context['downloads_cat'] . " AND c.ID_GROUP = -1 AND a.ID_CAT = c.ID_CAT LIMIT 1", __FILE__, __LINE__);
	$context['downloads_guestgroup'] = array();
	while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_guestgroup'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'addcomment' => $row['addcomment'],
			'ID_GROUP' => $row['ID_GROUP'],
			'catname' => $row['catname'],
			);

		}
	mysql_free_result($dbresult);


}

function Downloads_CatPerm2()
{
	global  $scripturl, $db_prefix, $txt;
	isAllowedTo('downloads_manage');

	$groupname = (int) $_REQUEST['groupname'];
	$cat = (int) $_REQUEST['cat'];

	// Check if permission exits
	$dbresult = db_query("
	SELECT
		ID_GROUP,ID_CAT
	FROM {$db_prefix}down_catperm
	WHERE ID_GROUP = $groupname AND ID_CAT = $cat", __FILE__, __LINE__);
	if (db_affected_rows()!= 0)
	{
		mysql_free_result($dbresult);
		fatal_error($txt['downloads_permerr_permexist'],false);
	}
	mysql_free_result($dbresult);

	// Permissions
	$view = isset($_REQUEST['view']) ? 1 : 0;
	$add = isset($_REQUEST['add']) ? 1 : 0;
	$edit = isset($_REQUEST['edit']) ? 1 : 0;
	$delete = isset($_REQUEST['delete']) ? 1 : 0;
	$addcomment = isset($_REQUEST['addcomment']) ? 1 : 0;

	// Insert into database
	db_query("INSERT INTO {$db_prefix}down_catperm
			(ID_GROUP,ID_CAT,view,addfile,editfile,delfile,addcomment)
		VALUES ($groupname,$cat,$view,$add,$edit,$delete,$addcomment)", __FILE__, __LINE__);

	redirectexit('action=downloads;sa=catperm;cat=' . $cat);
}

function Downloads_CatPermList()
{
	global $mbname, $txt, $db_prefix, $context, $db_prefix;
	isAllowedTo('downloads_manage');
	adminIndex('downloads_settings');

	// Load the template
	$context['sub_template']  = 'catpermlist';

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['downloads_text_title'] . ' - ' . $txt['downloads_text_catpermlist'];

	$dbresult = db_query("
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile, c.addcomment,
		c.ID_GROUP, m.groupName,a.title catname
	FROM ({$db_prefix}down_catperm as c, {$db_prefix}membergroups AS m,{$db_prefix}down_cat as a)
	WHERE  c.ID_GROUP = m.ID_GROUP AND a.ID_CAT = c.ID_CAT", __FILE__, __LINE__);
	$context['downloads_membergroups'] = array();
	while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_membergroups'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'addcomment' => $row['addcomment'],
			'ID_GROUP' => $row['ID_GROUP'],
			'groupName' => $row['groupName'],
			'catname' => $row['catname'],
			);

		}
	mysql_free_result($dbresult);

	$dbresult = db_query("
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile, c.addcomment,  c.ID_GROUP,a.title catname
	FROM {$db_prefix}down_catperm as c,{$db_prefix}down_cat as a
	WHERE  c.ID_GROUP = 0 AND a.ID_CAT = c.ID_CAT LIMIT 1", __FILE__, __LINE__);
	$context['downloads_regmem'] = array();
	while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_regmem'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'addcomment' => $row['addcomment'],
			'ID_GROUP' => $row['ID_GROUP'],
			'catname' => $row['catname'],
			);

		}
	mysql_free_result($dbresult);


	$dbresult = db_query("
	SELECT
		c.ID_CAT, c.ID, c.view, c.addfile, c.editfile, c.delfile, c.addcomment,  c.ID_GROUP,a.title catname
	FROM {$db_prefix}down_catperm as c,{$db_prefix}down_cat as a
	WHERE  c.ID_GROUP = -1 AND a.ID_CAT = c.ID_CAT LIMIT 1", __FILE__, __LINE__);
	$context['downloads_guestmem'] = array();
	while($row = mysql_fetch_assoc($dbresult))
		{
			$context['downloads_guestmem'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'ID' => $row['ID'],
			'view' => $row['view'],
			'addfile' => $row['addfile'],
			'editfile' => $row['editfile'],
			'delfile' => $row['delfile'],
			'addcomment' => $row['addcomment'],
			'ID_GROUP' => $row['ID_GROUP'],
			'catname' => $row['catname'],
			);

		}
	mysql_free_result($dbresult);

	DoDownloadsAdminTabs();
}

function Downloads_CatPermDelete()
{
	global $scripturl, $db_prefix;
	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];

	// Delete the Permission
	db_query("DELETE FROM {$db_prefix}down_catperm WHERE ID = " . $id . ' LIMIT 1', __FILE__, __LINE__);
	// Redirect to the ratings
	redirectexit('action=downloads;sa=catpermlist');

}

function Downloads_GetCatPermission($cat,$perm)
{
	global $ID_MEMBER, $db_prefix, $txt, $user_info;
	$cat = (int) $cat;
	if (!$user_info['is_guest'])
	{
		$dbresult = db_query("
		SELECT
			m.ID_MEMBER, c.view, c.addfile, c.editfile, c.delfile,c.ratefile, c.addcomment,
			c.editcomment, c.report
		FROM {$db_prefix}down_catperm as c, {$db_prefix}members as m
		WHERE m.ID_MEMBER = $ID_MEMBER AND c.ID_GROUP = m.ID_GROUP AND c.ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
	}
	else
		$dbresult = db_query("
		SELECT
			c.view, c.addfile, c.editfile, c.delfile,c.ratefile, c.addcomment, c.editcomment,
			c.report
		FROM {$db_prefix}down_catperm as c
		WHERE c.ID_GROUP = -1 AND c.ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);

	if (db_affected_rows()== 0)
	{
		mysql_free_result($dbresult);
	}
	else
	{
		$row = mysql_fetch_assoc($dbresult);

		mysql_free_result($dbresult);
		if ($perm == 'view' && $row['view'] == 0)
			fatal_error($txt['downloads_perm_no_view'],false);
		else if ($perm == 'addfile' && $row['addfile'] == 0)
			fatal_error($txt['downloads_perm_no_add'],false);
		else if ($perm == 'editfile' && $row['editfile'] == 0)
			fatal_error($txt['downloads_perm_no_edit'],false);
		else if ($perm == 'delfile' && $row['delfile'] == 0)
			fatal_error($txt['downloads_perm_no_delete'],false);
		else if ($perm == 'ratefile' && $row['ratefile'] == 0)
			fatal_error($txt['downloads_perm_no_ratefile'],false);
		else if ($perm == 'addcomment' && $row['addcomment'] == 0)
			fatal_error($txt['downloads_perm_no_addcomment'],false);
		else if ($perm == 'editcomment' && $row['editcomment'] == 0)
			fatal_error($txt['downloads_perm_no_editcomment'],false);
		else if ($perm == 'report' && $row['report'] == 0)
			fatal_error($txt['downloads_perm_no_report'],false);

	}


}

function Downloads_PreviousDownload()
{
	global $db_prefix, $txt, $scripturl;

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	// Get the category
	$dbresult = db_query("
	SELECT
		ID_FILE, ID_CAT
	FROM {$db_prefix}down_file
	WHERE ID_FILE = $id  LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	if (empty($row['ID_FILE']))
		fatal_error($txt['downloads_error_no_file_selected'],false);

	$ID_CAT = $row['ID_CAT'];

	mysql_free_result($dbresult);



	// Get previous download
	$dbresult = db_query("
	SELECT
		ID_FILE
	FROM {$db_prefix}down_file
	WHERE ID_CAT = $ID_CAT AND approved = 1 AND ID_FILE < $id ORDER BY ID_FILE DESC LIMIT 1", __FILE__, __LINE__);
	if (db_affected_rows() != 0)
	{
		$row = mysql_fetch_assoc($dbresult);
		$ID_FILE = $row['ID_FILE'];
	}
	else
		$ID_FILE = $id;

	mysql_free_result($dbresult);

	redirectexit('action=downloads;sa=view;down=' . $ID_FILE);
}

function Downloads_NextDownload()
{
	global $db_prefix, $txt, $scripturl;

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['downloads_error_no_file_selected']);

	// Get the category
	$dbresult = db_query("
	SELECT
		ID_FILE, ID_CAT
	FROM {$db_prefix}down_file
	WHERE ID_FILE = $id  LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	if (empty($row['ID_FILE']))
		fatal_error($txt['downloads_error_no_file_selected'],false);

	$ID_CAT = $row['ID_CAT'];

	mysql_free_result($dbresult);




	// Get next download
	$dbresult = db_query("
	SELECT
		ID_FILE
	FROM {$db_prefix}down_file
	WHERE ID_CAT = $ID_CAT AND approved = 1 AND ID_FILE > $id
	ORDER BY ID_FILE ASC LIMIT 1", __FILE__, __LINE__);
	if (db_affected_rows() != 0)
	{
		$row = mysql_fetch_assoc($dbresult);
		$ID_FILE = $row['ID_FILE'];
	}
	else
		$ID_FILE = $id;
	mysql_free_result($dbresult);

	redirectexit('action=downloads;sa=view;down=' . $ID_FILE);
}

function Downloads_CatImageDelete()
{
	global $db_prefix;

	isAllowedTo('downloads_manage');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		exit;

		db_query("UPDATE {$db_prefix}down_cat
		SET filename = '' WHERE ID_CAT = $id LIMIT 1", __FILE__, __LINE__);

	redirectexit('action=downloads;sa=editcat;cat=' . $id);
}

function Downloads_ReOrderCats($cat)
{
	global $db_prefix;

	$dbresult1 = db_query("
	SELECT
		roworder,ID_PARENT
	FROM {$db_prefix}down_cat
	WHERE ID_CAT = $cat", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	mysql_free_result($dbresult1);

	$dbresult = db_query("
	SELECT
		ID_CAT, roworder
	FROM {$db_prefix}down_cat
	WHERE ID_PARENT = $ID_PARENT
	ORDER BY roworder ASC", __FILE__, __LINE__);
	if (db_affected_rows() != 0)
	{
		$count = 1;
		while($row2 = mysql_fetch_assoc($dbresult))
		{
			db_query("UPDATE {$db_prefix}down_cat
			SET roworder = $count WHERE ID_CAT = " . $row2['ID_CAT'], __FILE__, __LINE__);
			$count++;
		}
	}
	mysql_free_result($dbresult);
}



function Downloads_BulkActions()
{
	isAllowedTo('downloads_manage');

	if (isset($_REQUEST['files']))
	{
		$baction = $_REQUEST['doaction'];

		foreach ($_REQUEST['files'] as $value)
		{

			if ($baction == 'approve')
				Downloads_ApproveFileByID($value);
			if ($baction == 'delete')
				Downloads_DeleteFileByID($value);

		}
	}

	// Redirect to approval list
	redirectexit('action=downloads;sa=approvelist');
}

function Downloads_UpdateCategoryTotals($ID_CAT)
{
	global $db_prefix;

	if (empty($ID_CAT))
		return;

	$dbresult = db_query("
	SELECT
		COUNT(*) AS total
	FROM {$db_prefix}down_file
	WHERE ID_CAT = $ID_CAT AND approved = 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$total = $row['total'];
	mysql_free_result($dbresult);

	// Update the count
	$dbresult = db_query("UPDATE {$db_prefix}down_cat SET total = $total WHERE ID_CAT = $ID_CAT LIMIT 1", __FILE__, __LINE__);

}

function Downloads_UpdateCategoryTotalByFileID($id)
{
	global $db_prefix;

	$dbresult = db_query("
	SELECT
		ID_CAT FROM {$db_prefix}down_file
	WHERE ID_FILE = $id", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);

	Downloads_UpdateCategoryTotals($row['ID_CAT']);

}

function Downloads_CustomUp()
{
	global $db_prefix, $txt;

	// Check Permission
	isAllowedTo('downloads_manage');
	// Get the id
	$id = (int) $_REQUEST['id'];

	Downloads_ReOrderCustom($id);

	// Check if there is a category above it
	// First get our row order
	$dbresult1 = db_query("
	SELECT
		ID_CAT, ID_CUSTOM, roworder
	FROM {$db_prefix}down_custom_field
	WHERE ID_CUSTOM = $id", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);

	$ID_CAT = $row['ID_CAT'];
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o--;

	mysql_free_result($dbresult1);
	$dbresult = db_query("
	SELECT
		ID_CUSTOM, roworder
	FROM {$db_prefix}down_custom_field
	WHERE ID_CAT = $ID_CAT AND roworder = $o", __FILE__, __LINE__);

	if (db_affected_rows()== 0)
		fatal_error($txt['downloads_error_nocustom_above'], false);
	$row2 = mysql_fetch_assoc($dbresult);


	// Swap the order Id's
	db_query("UPDATE {$db_prefix}down_custom_field
		SET roworder = $oldrow WHERE ID_CUSTOM = " .$row2['ID_CUSTOM'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}down_custom_field
		SET roworder = $o WHERE ID_CUSTOM = $id", __FILE__, __LINE__);


	mysql_free_result($dbresult);

	// Redirect to index to view cats
	redirectexit('action=downloads;sa=editcat;cat=' . $ID_CAT);

}

function Downloads_CustomDown()
{
	global $db_prefix, $txt;

	isAllowedTo('downloads_manage');

	// Get the id
	$id = (int) $_REQUEST['id'];

	Downloads_ReOrderCustom($id);

	// Check if there is a category below it
	// First get our row order
	$dbresult1 = db_query("
	SELECT
		ID_CUSTOM,ID_CAT, roworder
	FROM {$db_prefix}down_custom_field
	WHERE ID_CUSTOM = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);
	$ID_CAT = $row['ID_CAT'];

	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o++;

	mysql_free_result($dbresult1);
	$dbresult = db_query("
	SELECT
		ID_CUSTOM, ID_CAT, roworder
	FROM {$db_prefix}down_custom_field
	WHERE ID_CAT = $ID_CAT AND roworder = $o", __FILE__, __LINE__);
	if (db_affected_rows()== 0)
		fatal_error($txt['downloads_error_nocustom_below'], false);
	$row2 = mysql_fetch_assoc($dbresult);


	// Swap the order Id's
	db_query("UPDATE {$db_prefix}down_custom_field
		SET roworder = $oldrow WHERE ID_CUSTOM = " .$row2['ID_CUSTOM'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}down_custom_field
		SET roworder = $o WHERE ID_CUSTOM = $id", __FILE__, __LINE__);


	mysql_free_result($dbresult);


	// Redirect to index to view cats
	redirectexit('action=downloads;sa=editcat;cat=' . $ID_CAT);

}

function Downloads_CustomAdd()
{
	global $db_prefix, $txt, $func;
	// Check Permission
	isAllowedTo('downloads_manage');


	$id = (int) $_REQUEST['id'];

	$title = $func['htmlspecialchars']($_REQUEST['title'],ENT_QUOTES);
	$defaultvalue = $func['htmlspecialchars']($_REQUEST['defaultvalue'],ENT_QUOTES);
	$required = isset($_REQUEST['required']) ? 1 : 0;


	if ($title == '')
		fatal_error($txt['downloads_custom_err_title'], false);


	db_query("INSERT INTO {$db_prefix}down_custom_field
			(ID_CAT,title, defaultvalue, is_required)
		VALUES ($id,'$title','$defaultvalue', '$required')", __FILE__, __LINE__);


	// Redirect back to the edit category page
	redirectexit('action=downloads;sa=editcat;cat=' . $id);

}

function Downloads_CustomDelete()
{
	global $db_prefix;

	// Check Permission
	isAllowedTo('downloads_manage');

	// Custom ID
	$id = (int) $_REQUEST['id'];

	// Get the CAT ID to redirect to the page
	$result = db_query("
	SELECT
		ID_CAT
	FROM {$db_prefix}down_custom_field
	WHERE ID_CUSTOM =  $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($result);
	mysql_free_result($result);


	// Delete all custom data for downloads that use it
	db_query("DELETE FROM {$db_prefix}down_custom_field_data
	WHERE ID_CUSTOM = $id ", __FILE__, __LINE__);

	// Finaly delete the field
	db_query("DELETE FROM {$db_prefix}down_custom_field
	WHERE ID_CUSTOM = $id LIMIT 1", __FILE__, __LINE__);

	// Redirect to the edit category page
	redirectexit('action=downloads;sa=editcat;cat=' . $row['ID_CAT']);


}

function Downloads_ReOrderCustom($id)
{
	global $db_prefix;

	// Get the Category ID by id
	$dbresult = db_query("
	SELECT
		ID_CAT, roworder
	FROM {$db_prefix}down_custom_field
	WHERE ID_CUSTOM = $id", __FILE__, __LINE__);
	$row1 = mysql_fetch_assoc($dbresult);
	$ID_CAT = $row1['ID_CAT'];
	mysql_free_result($dbresult);

	$dbresult = db_query("
	SELECT
		ID_CUSTOM, roworder
	FROM {$db_prefix}down_custom_field
	WHERE ID_CAT = $ID_CAT ORDER BY roworder ASC", __FILE__, __LINE__);
	if (db_affected_rows() != 0)
	{
		$count = 1;
		while($row2 = mysql_fetch_assoc($dbresult))
		{
			db_query("UPDATE {$db_prefix}down_custom_field
			SET roworder = $count WHERE ID_CUSTOM = " . $row2['ID_CUSTOM'], __FILE__, __LINE__);
			$count++;
		}
	}
	mysql_free_result($dbresult);
}


function Downloads_ComputeNextFolderID($ID_FILE)
{
	global $modSettings;

	$folderid = floor($ID_FILE / 1000);

	// If the current folder ID does not match the new folder ID update the settings
	if ($modSettings['down_folder_id'] != $folderid)
		updateSettings(array('down_folder_id' => $folderid));


}
function Downloads_CreateDownloadFolder()
{
	global $modSettings, $boarddir;


	$newfolderpath = $modSettings['down_path'] . $modSettings['down_folder_id'] . '/';

	// Check if the folder exists if it doess just exit
	if  (!file_exists($newfolderpath))
	{
		// If the folder does not exist then create it
		@mkdir ($newfolderpath);
		// Try to make sure that the correct permissions are on the folder
		@chmod ($newfolderpath,0755);
	}

}

function Downloads_GetFileTotals($ID_CAT)
{
	global $modSettings, $db_prefix, $subcats_linktree, $scripturl;

	$total = 0;

	$total += Downloads_GetTotalByCATID($ID_CAT);
	$subcats_linktree = '';

	// Get the child categories to this category
	if ($modSettings['down_set_count_child'])
	{
		$dbresult3 = db_query("
		SELECT
			ID_CAT, total, title
		FROM {$db_prefix}down_cat WHERE ID_PARENT = $ID_CAT", __FILE__, __LINE__);
		while($row3 = mysql_fetch_assoc($dbresult3))
		{
			$subcats_linktree .= '<a href="' . $scripturl . '?action=downloads;cat=' . $row3['ID_CAT'] . '">' . $row3['title'] . '</a>&nbsp;&nbsp;';

			if ($row3['total'] == -1)
			{
				$dbresult = db_query("
				SELECT
					COUNT(*) AS total
				FROM {$db_prefix}down_file
				WHERE ID_CAT = " . $row3['ID_CAT'] . " AND approved = 1", __FILE__, __LINE__);
				$row = mysql_fetch_assoc($dbresult);
				$total2 = $row['total'];
				mysql_free_result($dbresult);


				$dbresult = db_query("UPDATE {$db_prefix}down_cat SET total = $total2 WHERE ID_CAT =  " . $row3['ID_CAT'] . " LIMIT 1", __FILE__, __LINE__);
			}
		}
		mysql_free_result($dbresult3);

		/*
		$dbresult3 = db_query("
		SELECT
			SUM(total) AS finaltotal
		FROM {$db_prefix}down_cat
		WHERE ID_PARENT = $ID_CAT", __FILE__, __LINE__);
		$row3 = mysql_fetch_assoc($dbresult3);

		mysql_free_result($dbresult3);
		if ($row3['finaltotal'] != '')
			$total += $row3['finaltotal'];
		*/
		$dbresult3 = db_query("
		SELECT
			total, ID_CAT, ID_PARENT
		FROM {$db_prefix}down_cat
		WHERE ID_PARENT <> 0", __FILE__, __LINE__);

		$childArray = array();
		while($row3 = mysql_fetch_assoc($dbresult3))
		{
			$childArray[] = $row3;
		}
		 mysql_fetch_assoc($dbresult3);

		$total += Downloads_GetFileTotalsByParent($ID_CAT,$childArray);

	}


	return $total;
}

function Downloads_GetFileTotalsByParent($ID_PARENT,$data)
{
	$total = 0;
	foreach($data as $row)
	{
		if ($row['ID_PARENT'] == $ID_PARENT)
		{
			$total += $row['total'];
			$total += Downloads_GetFileTotalsByParent($row['ID_CAT'],$data);
		}
	}

	return $total;
}

function Downloads_GetTotalByCATID($ID_CAT)
{
	global $db_prefix;

	$dbresult = db_query("
	SELECT
		total
	FROM {$db_prefix}down_cat
	WHERE ID_CAT = $ID_CAT", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);

	if ($row['total'] != -1)
		return $row['total'];
	else
	{
		$dbresult = db_query("
		SELECT
			COUNT(*) AS total
		FROM {$db_prefix}down_file
		WHERE ID_CAT = $ID_CAT AND approved = 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
		$total = $row['total'];
		mysql_free_result($dbresult);

		// Update the count
		$dbresult = db_query("UPDATE {$db_prefix}down_cat SET total = $total WHERE ID_CAT = $ID_CAT LIMIT 1", __FILE__, __LINE__);

		// Return the total files
		return $total;

	}

}

function Downloads_DownloadFile()
{
	global $db_prefix, $ID_MEMBER, $boarddir, $modSettings, $txt, $context;

	// Check Permission
	isAllowedTo('downloads_view');



	if (isset($_REQUEST['down']))
		$id = (int) $_REQUEST['down'];
	else
		$id = (int) $_REQUEST['id'];

	// Get the download information
	$dbresult = db_query("
	SELECT
		f.filename, f.fileurl, f.orginalfilename, f.approved, f.credits, f.ID_CAT, f.ID_MEMBER, f.id_file
	FROM {$db_prefix}down_file as f
	WHERE f.ID_FILE = $id", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);

	if (empty($row['id_file']))
		fatal_error($txt['downloads_error_no_file_selected'],false);

	// Check if File is approved
	if ($row['approved'] == 0 && $ID_MEMBER != $row['ID_MEMBER'])
	{
		if (!allowedTo('downloads_manage'))
			fatal_error($txt['downloads_error_file_notapproved'],false);
	}

	// Check if they can download from this category
	Downloads_GetCatPermission($row['ID_CAT'],'view');

	// Check credits

	// End Credit check

	// Download File or Redirect to the download location
	if ($row['fileurl'] != '')
	{
		$lastdownload = time();
		// Update download count
		$dbresult = db_query("
		UPDATE {$db_prefix}down_file
			SET totaldownloads = totaldownloads + 1, lastdownload  = '$lastdownload'
		WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);

		// Redirect to the download
		header("Location: " . $row['fileurl']);

		exit;
	}
	else
	{
		$lastdownload = time();
		// Update download count
		$dbresult = db_query("
		UPDATE {$db_prefix}down_file
			SET totaldownloads = totaldownloads + 1, lastdownload  = '$lastdownload'
		WHERE ID_FILE = $id LIMIT 1", __FILE__, __LINE__);


		$real_filename = $row['orginalfilename'];
		$filename = $modSettings['down_path'] . $row['filename'];


		ob_end_clean();
		if (!empty($modSettings['enableCompressedOutput'])  && @filesize($filename) <= 4194304)
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


}

function Downloads_ShowSubCats($cat,$g_manage)
{
	global $txt, $db_prefix, $scripturl, $modSettings, $boardurl, $subcats_linktree, $user_info, $context;


	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];


		// List all the catagories
		$dbresult = db_query("
		SELECT
			c.ID_CAT, c.title, p.view, c.roworder, c.description, c.image, c.filename
		FROM {$db_prefix}down_cat AS c
			LEFT JOIN {$db_prefix}down_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)
		WHERE c.ID_PARENT = $cat ORDER BY c.roworder ASC", __FILE__, __LINE__);
		if (db_affected_rows() != 0)
		{

			echo '<br /><table border="0" cellspacing="1" cellpadding="5" class="bordercolor" style="margin-top: 1px;" align="center" width="90%">
					<tr class="titlebg">
					<td colspan="2">' . $txt['downloads_text_categoryname'] . '</td>
					<td align="center">' . $txt['downloads_text_totalfiles'] . '</td>
					';
			if ($g_manage)
			echo '
					<td>' . $txt['downloads_text_reorder'] . '</td>
					<td>' . $txt['downloads_text_options'] . '</td>';

			echo '</tr>';


			while($row = mysql_fetch_assoc($dbresult))
			{
				// Check permission to show the downloads category
				if ($row['view'] == '0')
					continue;

				$totalfiles = Downloads_GetFileTotals($row['ID_CAT']);

				echo '<tr>';

					if ($row['image'] == '' && $row['filename'] == '')
						echo '<td class="windowbg" width="10%"></td><td  class="windowbg2"><b><a href="' . $scripturl . '?action=downloads;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></b><br />' . parse_bbc($row['description']) . '</td>';
					else
					{
						if ($row['filename'] == '')
							echo '<td class="windowbg" width="10%"><a href="' . $scripturl . '?action=downloads;cat=' . $row['ID_CAT'] . '"><img src="' . $row['image'] . '" /></a></td>';
						else
							echo '<td class="windowbg" width="10%"><a href="' . $scripturl . '?action=downloads;cat=' . $row['ID_CAT'] . '"><img src="' . $modSettings['down_url'] . 'catimgs/' . $row['filename'] . '" /></a></td>';

						echo '<td class="windowbg2"><b><a href="' . $scripturl . '?action=downloads;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></b><br />' . parse_bbc($row['description']) . '</td>';
					}



				// Show total files in the category
				echo '<td align="center" valign="middle" class="windowbg">' . $totalfiles . '</td>';

				// Show Edit Delete and Order category
				if ( $g_manage)
				{
					echo '
					<td class="windowbg2"><a href="' . $scripturl . '?action=downloads;sa=catup;cat=' . $row['ID_CAT'] . '">' . $txt['downloads_text_up'] . '</a>&nbsp;<a href="' . $scripturl . '?action=downloads;sa=catdown;cat=' . $row['ID_CAT'] . '">' . $txt['downloads_text_down'] . '</a></td>
					<td class="windowbg"><a href="' . $scripturl . '?action=downloads;sa=editcat;cat=' . $row['ID_CAT'] . '">' . $txt['downloads_text_edit'] . '</a>&nbsp;<a href="' . $scripturl . '?action=downloads;sa=deletecat;cat=' . $row['ID_CAT'] . '">' . $txt['downloads_text_delete'] . '</a>
					<br /><br />
					<a href="' . $scripturl . '?action=downloads;sa=catperm;cat=' . $row['ID_CAT'] . '">[' . $txt['downloads_text_permissions'] . ']</a>
					</td>';

				}


				echo '</tr>';

				if ($subcats_linktree != '')
					echo '
					<tr class="windowbg3">
						<td colspan="',($g_manage ? '6' : '4'), '">&nbsp;<span class="smalltext">',($subcats_linktree != '' ? '<b>' . $txt['downloads_sub_cats'] . '</b>' . $subcats_linktree : ''),'</span></td>
					</tr>';

			}
			mysql_free_result($dbresult);
			echo '</table><br /><br />';
		}
}

function MainPageBlock($title, $type = 'recent')
{
	global $db_prefix, $scripturl, $txt, $modSettings, $boardurl, $context, $user_info;


	if (!$context['user']['is_guest'])
		$groupsdata = implode(',',$user_info['groups']);
	else
		$groupsdata = -1;


	$maxrowlevel = 4;
	echo '<table cellspacing="0" cellpadding="5" border="0" align="center" width="90%" class="tborder">
				<tr class="titlebg">
					<td align="center" colspan="4">', $title, '</td>
				</tr>
				';
			//Check what type it is
			$query = ' ';
			$query_type = 'p.ID_FILE';
			switch($type)
			{
				case 'recent':
					$query_type = 'p.ID_FILE';
				break;

				case 'viewed':

					$query_type = 'p.views';
				break;

				case 'mostcomments':
					$query_type = 'p.commenttotal';

				break;
				case 'mostdownloaded':
					$query_type = 'p.totaldownloads';
				break;

				case 'toprated':
					$query_type = 'p.rating';
				break;
			}

				$query = "SELECT p.ID_FILE, p.commenttotal, p.totalratings, p.rating, p.filesize, p.views, p.title, p.ID_MEMBER, m.realName, p.date, p.description,
				p.totaldownloads
					FROM {$db_prefix}down_file as p
					LEFT JOIN {$db_prefix}members AS m  ON (m.ID_MEMBER = p.ID_MEMBER)
					LEFT JOIN {$db_prefix}down_catperm AS c ON (c.ID_GROUP IN ($groupsdata) AND c.ID_CAT = p.ID_CAT)
					WHERE p.approved = 1 AND (c.view IS NULL OR c.view =1) GROUP by p.ID_FILE  ORDER BY $query_type DESC LIMIT 4";

			// Execute the SQL query
			$dbresult = db_query($query, __FILE__, __LINE__);
			$rowlevel = 0;
		while($row = mysql_fetch_assoc($dbresult))
		{
			if ($rowlevel == 0)
				echo '<tr class="windowbg2">';

			echo '<td align="center"><a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">',$row['title'],'</a><br />';
			echo '<span class="smalltext">';
			if (!empty($modSettings['down_set_t_rating']))
				echo $txt['downloads_form_rating'] . Downloads_GetStarsByPrecent(($row['totalratings'] != 0) ? ($row['rating'] / ($row['totalratings']* 5) * 100) : 0) . '<br />';
			if (!empty($modSettings['down_set_t_downloads']))
				echo $txt['downloads_text_downloads'] . $row['totaldownloads'] . '<br />';

			if (!empty($modSettings['down_set_t_views']))
				echo $txt['downloads_text_views'] . $row['views'] . '<br />';
			if (!empty($modSettings['down_set_t_filesize']))
				echo $txt['downloads_text_filesize'] . Downloads_format_size($row['filesize'], 2) . '<br />';
			if (!empty($modSettings['down_set_t_date']))
				echo $txt['downloads_text_date'] . timeformat($row['date']) . '<br />';
			if (!empty($modSettings['down_set_t_comment']))
				echo $txt['downloads_text_comments'] . ' (<a href="' . $scripturl . '?action=downloads;sa=view;down=' . $row['ID_FILE'] . '">' . $row['commenttotal'] . '</a>)<br />';
			if (!empty($modSettings['down_set_t_username']))
			{
				if ($row['realName'] != '')
					echo $txt['downloads_text_by'] . ' <a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">'  . $row['realName'] . '</a><br />';
				else
					echo $txt['downloads_text_by'] . ' ' . $txt['downloads_guest'] . '<br />';
			}
			echo '</span></td>';


			if ($rowlevel < ($maxrowlevel-1))
				$rowlevel++;
			else
			{
				echo '</tr>';
				$rowlevel = 0;
			}
		}
		if ($rowlevel !=0)
		{
			echo '</tr>';
		}

	echo '
	      </table><br />';

	mysql_free_result($dbresult);

}

function DoDownloadsAdminTabs($overrideSelected = '')
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
			FROM {$db_prefix}down_file
			WHERE approved = 0", __FILE__, __LINE__);
	$totalrow = mysql_fetch_assoc($dbresult3);
	$totalappoval = $totalrow['total'];
	mysql_free_result($dbresult3);


	// Create the tabs for the template.
	$context['admin_tabs'] = array(
		'title' => $txt['downloads_admin'],
		'description' => '',
		'tabs' => array(),
	);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['downloads_text_settings'],
			'description' => '',
			'href' => $scripturl . '?action=downloads;sa=adminset',
			'is_selected' => $_REQUEST['sa'] == 'adminset',
		);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['downloads_form_approvedownloads']  . ' (' . $totalappoval . ')',
			'description' => '',
			'href' => $scripturl . '?action=downloads;sa=approvelist',
			'is_selected' => $_REQUEST['sa'] == 'approvelist',
		);

	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['downloads_form_reportdownloads'],
			'description' => '',
			'href' => $scripturl . '?action=downloads;sa=reportlist',
			'is_selected' => $_REQUEST['sa'] == 'reportlist',
		);

	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['downloads_form_approvecomments'],
			'description' => '',
			'href' => $scripturl . '?action=downloads;sa=commentlist',
			'is_selected' => $_REQUEST['sa'] == 'commentlist',
		);

	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['downloads_filespace'],
			'description' => '',
			'href' => $scripturl . '?action=downloads;sa=filespace',
			'is_selected' => $_REQUEST['sa'] == 'filespace',
		);

	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['downloads_text_catpermlist2'],
			'description' => '',
			'href' => $scripturl . '?action=downloads;sa=catpermlist',
			'is_selected' => $_REQUEST['sa'] == 'catpermlist',
		);

	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['downloads_txt_import_downloads'] ,
			'description' => '',
			'href' => $scripturl . '?action=downloads;sa=import',
			'is_selected' => $_REQUEST['sa'] == 'import',
		);


	if (!empty($overrideSelected))
	{
		$_REQUEST['sa'] = $tmpSA;
	}

	$context['admin_tabs']['tabs'][count($context['admin_tabs']['tabs']) - 1]['is_last'] = true;
}


function TopDownloadTabs()
{
	global $context, $txt, $scripturl, $ID_MEMBER;

	$g_add = allowedTo('downloads_add');

	$catWhere = '';

	if (isset($_REQUEST['cat']))
    {
        $cat = (int) $_REQUEST['cat'];
	    $catWhere = ';cat=' . $cat;
    }

	// Add Download Button
	if ($g_add)
		$context['downloads']['buttons']['add'] =  array(
			'text' => 'downloads_form_adddownload',
			'url' =>$scripturl . '?action=downloads;sa=add'. $catWhere,
			'lang' => true,

		);

	// MyFiles
	if ($g_add && !($context['user']['is_guest']))
		$context['downloads']['buttons']['myfiles'] =  array(
			'text' => 'downloads_text_myfiles2',
			'url' =>$scripturl . '?action=downloads;sa=myfiles;u=' . $ID_MEMBER,
			'lang' => true,

		);

	// Search
	$context['downloads']['buttons']['search'] =  array(
		'text' => 'downloads_text_search2',
		'url' => $scripturl . '?action=downloads;sa=search',
		'lang' => true,

	);

	// Setup Intial Link Tree
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=downloads',
					'name' => $txt['downloads_text_title']
				);
}

function Downloads_GetParentLink($ID_CAT)
{
	global $db_prefix, $context, $scripturl;
	if ($ID_CAT == 0)
		return;

			$dbresult1 = db_query("
		SELECT
			ID_PARENT,title
		FROM {$db_prefix}down_cat
		WHERE ID_CAT = $ID_CAT LIMIT 1", __FILE__, __LINE__);
		$row1 = mysql_fetch_assoc($dbresult1);

		mysql_free_result($dbresult1);

		Downloads_GetParentLink($row1['ID_PARENT']);

		$context['linktree'][] = array(
					'url' => $scripturl . '?action=downloads;cat=' . $ID_CAT ,
					'name' => $row1['title']
				);
}

function Downloads_DoToolBarStrip($button_strip, $direction )
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

function Downloads_ImportTinyPortalDownloads()
{
	global $txt, $db_prefix, $boarddir, $context, $modSettings, $sourcedir, $downloadSettings;
	isAllowedTo('downloads_manage');

	// No limit on how long it takes
	ini_set('max_execution_time', 0);
	ini_set('display_errors', 1);

	$testGD = get_extension_funcs('gd');
	$gd2 = in_array('imagecreatetruecolor', $testGD) && function_exists('imagecreatetruecolor');
	unset($testGD);


	require_once($sourcedir . '/Subs-Graphics.php');

	$catCount = 0;
	$fileCount = 0;


	$catArray = array();

	// Process Categories
	$catResult = db_query("
	SELECT
		id, name, description, parent
	FROM {$db_prefix}tp_dlmanager
	WHERE type = 'dlcat' ORDER by parent ASC", __FILE__, __LINE__);
	while ($catRow = mysql_fetch_assoc($catResult))
	{


		$ID_PARENT = 0;
		// Get the new parent id
		if ($catRow['parent'] != 0)
			$ID_PARENT = $catArray[$catRow['parent']];

		if (empty($ID_PARENT))
			$ID_PARENT = 0;

		$title = addslashes($catRow['name']);
		$description = addslashes($catRow['description']);

		// Insert the category
		db_query("INSERT INTO {$db_prefix}down_cat
				(title, description, ID_PARENT)
			VALUES ('$title', '$description',$ID_PARENT)", __FILE__, __LINE__);


		// Get the Category ID
		$cat_id = db_insert_id();
		ReOrderCats($cat_id);

		$catArray[$catRow['id']] = $cat_id;

		$catCount++;

	}
	mysql_free_result($catResult);

	// Process Files
	$fileResult = db_query("
	SELECT
		id, name, description, category, downloads, views,
		created, last_access, filesize, authorid, screenshot, rating, voters,
		file
	FROM {$db_prefix}tp_dlmanager
	WHERE type = 'dlitem'", __FILE__, __LINE__);
	while ($fileRow = mysql_fetch_assoc($fileResult))
	{
		$category = (int) $catArray[$fileRow['category']];
		$filesize = $fileRow['filesize'];
		$orginalfilename = addslashes($fileRow['file']);
		$filename =   addslashes($fileRow['file']);
		$description = addslashes($fileRow['description']);
		$title = addslashes($fileRow['name']);
		$authorid = $fileRow['authorid'];
		$filedate =  $fileRow['created'];
		$lastdownload = $fileRow['last_access'];
		$views = $fileRow['views'];
		$totaldownloads = $fileRow['downloads'];
		$screenshot = addslashes($fileRow['screenshot']);

		db_query("INSERT INTO {$db_prefix}down_file
							(ID_CAT, filesize, filename, orginalfilename, title, description,ID_MEMBER,date,approved, views, totaldownloads, lastdownload)
						VALUES ($category, $filesize, '" . $filename . "', '$orginalfilename','$title', '$description',$authorid,$filedate,1, $views, $totaldownloads, $lastdownload )", __FILE__, __LINE__);

		$file_id = db_insert_id();

		// Copy the files to the main downloads folder
		copy($boarddir . '/tp-downloads/' . $filename , $modSettings['down_path'] . $filename);
		@chmod($modSettings['down_path'] .  $filename, 0644);

		// Do screenshots if any add them to file pictures
		/*
		if (!empty($screenshot))
		{

			$orginalScreenshot = $screenshot;
			$screenshot = str_replace('tp-images/Image/','',$orginalScreenshot);
			// Copy screenshot to downloads folder

			copy($boarddir . '/'.  $orginalScreenshot, $modSettings['down_path'] . $screenshot);

			@chmod($modSettings['down_path'] .  $screenshot, 0644);

			$picFileSize = filesize($modSettings['down_path'] .  $screenshot);


			$sizes = getimagesize($modSettings['down_path'] .  $screenshot);

				createThumbnail($modSettings['down_path'] .  $screenshot, $downloadSettings['screenshot_thumb_width'], $downloadSettings['screenshot_thumb_height']);
				rename($modSettings['down_path'] .   $screenshot . '_thumb',  $modSettings['down_path'] .   'thumb_' . $screenshot);
				$thumbname = 'thumb_' . $screenshot;
				@chmod($modSettings['down_path'] .   'thumb_' . $screenshot, 0755);

				// Medium Image
				$mediumimage = '';

				if ($downloadSettings['screenshot_make_medium'])
				{
					createThumbnail($modSettings['down_path'] .  $screenshot, $downloadSettings['screenshot_medium_width'], $downloadSettings['screenshot_medium_height']);
					rename($modSettings['down_path'] .  $screenshot . '_thumb',  $modSettings['down_path'] .   'medium_' . $screenshot);
					$mediumimage = 'medium_' . $screenshot;
					@chmod($modSettings['down_path'] . 'medium_' . $screenshot, 0755);

					// Check for Watermark
					if ($downloadSettings['screenshot_set_water_enabled'])
						DoWaterMark($modSettings['down_path'] .   'medium_' .  $screenshot);

				}

				// Create the Database entry

				$down_pic_id = 0;
						db_query("INSERT INTO {$db_prefix}down_file_pic
								(ID_FILE, filesize,thumbfilename,filename, height, width, ID_MEMBER, date, approved, mediumfilename)
							VALUES ($file_id, $picFileSize,'" .  $thumbname . "', '" .  $screenshot. "', $sizes[1], $sizes[0],$authorid,$filedate,1, '" . $mediumimage . "')", __FILE__, __LINE__);

				$down_pic_id = db_insert_id();



			// If there is no Picture set make it the primary picture
				db_query("
				UPDATE {$db_prefix}down_file
				SET ID_PICTURE = $down_pic_id
				WHERE ID_FILE = $file_id AND ID_PICTURE = 0", __FILE__, __LINE__);


		}
		*/


		// Do rating conversions
		$ratingsArray = explode(",",$fileRow['rating']);
		$votersArray = explode(",",$fileRow['voters']);

		foreach($ratingsArray as $key => $rating)
		{
			if (empty($votersArray[$key]))
				continue;
			if ($rating == '')
				continue;


			db_query("INSERT INTO {$db_prefix}down_rating (ID_MEMBER, ID_FILE, value) VALUES (" . $votersArray[$key] . ", $file_id,$rating)", __FILE__, __LINE__);

			// Add rating information to the download
			db_query("
			UPDATE {$db_prefix}down_file
				SET totalratings = totalratings + 1, rating = rating + $rating
			WHERE ID_FILE = $file_id LIMIT 1", __FILE__, __LINE__);
		}

		if ($fileRow['filesize'] != 0)
			Downloads_UpdateUserFileSizeTable($fileRow['authorid'],$fileRow['filesize']);

		Downloads_UpdateCategoryTotals($category);
		//UpdateMemberTotalFiles($fileRow['authorid']);

		$fileCount++;

	}
	mysql_free_result($fileResult);


	$context['tp_imported_files'] = $fileCount;
	$context['tp_imported_categories'] = $catCount;
	$context['sub_template'] = 'import_results';
	$context['page_title'] = $txt['downloads_txt_importtp_results'];

}

function Downloads_ImportDownloads()
{
	global $txt, $context;

	isAllowedTo('downloads_manage');

	adminIndex('downloads_settings');

	DoDownloadsAdminTabs();

	$context['sub_template'] = 'import';

	$context['page_title'] = $txt['downloads_txt_import_downloads'];

}


function Downloads_ShowUserBox($memCommID, $onlineColor = '')
{
	global $memberContext, $settings, $modSettings, $txt, $context, $scripturl, $options, $downloadSettings;


	echo '
	<b>', $memberContext[$memCommID]['link'], '</b>
							<div class="smalltext">';

		// Show the member's custom title, if they have one.
		if (isset($memberContext[$memCommID]['title']) && $memberContext[$memCommID]['title'] != '')
			echo '
								', $memberContext[$memCommID]['title'], '<br />';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (isset($memberContext[$memCommID]['group']) && $memberContext[$memCommID]['group'] != '')
			echo '
								', $memberContext[$memCommID]['group'], '<br />';

		// Don't show these things for guests.
		if (!$memberContext[$memCommID]['is_guest'])
		{
			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $memberContext[$memCommID]['group'] == '') && $memberContext[$memCommID]['post_group'] != '')
				echo '
								', $memberContext[$memCommID]['post_group'], '<br />';
			echo '
								', $memberContext[$memCommID]['group_stars'], '<br />';

			// Is karma display enabled?  Total or +/-?
			if ($modSettings['karmaMode'] == '1')
				echo '
								<br />
								', $modSettings['karmaLabel'], ' ', $memberContext[$memCommID]['karma']['good'] - $memberContext[$memCommID]['karma']['bad'], '<br />';
			elseif ($modSettings['karmaMode'] == '2')
				echo '
								<br />
								', $modSettings['karmaLabel'], ' +', $memberContext[$memCommID]['karma']['good'], '/-', $memberContext[$memCommID]['karma']['bad'], '<br />';

			// Is this user allowed to modify this member's karma?
			if ($memberContext[$memCommID]['karma']['allow'])
				echo '
								<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $memberContext[$memCommID]['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaApplaudLabel'], '</a>
								<a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $memberContext[$memCommID]['id'],  ';sesc=', $context['session_id'], '">', $modSettings['karmaSmiteLabel'], '</a><br />';

			// Show online and offline buttons?
			if (!empty($modSettings['onlineEnable']) && !$memberContext[$memCommID]['is_guest'])
				echo '
								', $context['can_send_pm'] ? '<a href="' . $memberContext[$memCommID]['online']['href'] . '" title="' . $memberContext[$memCommID]['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $memberContext[$memCommID]['online']['image_href'] . '" alt="' . $memberContext[$memCommID]['online']['text'] . '" border="0" style="margin-top: 2px;" />' : $memberContext[$memCommID]['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $memberContext[$memCommID]['online']['text'] . '</span>' : '', '<br /><br />';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $memberContext[$memCommID]['gender']['image'] != '')
				echo '
								', $txt['downloads_txt_gender'], ': ', $memberContext[$memCommID]['gender']['image'], '<br />';

			// Show how many posts they have made.
			echo '
								', $txt['downloads_txt_posts'], ': ', $memberContext[$memCommID]['posts'], '<br />
								<br />';

			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($memberContext[$memCommID]['avatar']['image']))
				echo '
								<div style="overflow: hidden; width: 100%;">', $memberContext[$memCommID]['avatar']['image'], '</div><br />';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $memberContext[$memCommID]['blurb'] != '')
				echo '
								', $memberContext[$memCommID]['blurb'], '<br />
								<br />';

			// This shows the popular messaging icons.
			echo '
								', $memberContext[$memCommID]['icq']['link'], '
								', $memberContext[$memCommID]['msn']['link'], '
								', $memberContext[$memCommID]['aim']['link'], '
								', $memberContext[$memCommID]['yim']['link'], '<br />';

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{

					echo '
								<a href="', $memberContext[$memCommID]['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt['downloads_txt_view_profile'] . '" title="' . $txt['downloads_txt_view_profile'] . '" border="0" />' : $txt['downloads_txt_view_profile']), '</a>';

				// Don't show an icon if they haven't specified a website.
				if ($memberContext[$memCommID]['website']['url'] != '')
					echo '
								<a href="', $memberContext[$memCommID]['website']['url'], '" title="' . $memberContext[$memCommID]['website']['title'] . '" target="_blank">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt['downloads_txt_www'] . '" border="0" />' : $txt['downloads_txt_www']), '</a>';

				// Don't show the email address if they want it hidden.
				if (empty($memberContext[$memCommID]['hide_email']))
					echo '
								<a href="mailto:', $memberContext[$memCommID]['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['downloads_txt_profile_email'] . '" title="' . $txt['downloads_txt_profile_email'] . '" border="0" />' : $txt['downloads_txt_profile_email']), '</a>';

				// Since we know this person isn't a guest, you *can* message them.
				if ($context['can_send_pm'])
					echo '
								<a href="', $scripturl, '?action=pm;sa=send;u=', $memberContext[$memCommID]['id'], '" title="', $memberContext[$memCommID]['online']['label'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($memberContext[$memCommID]['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . $memberContext[$memCommID]['online']['label'] . '" border="0" />' : $memberContext[$memCommID]['online']['label'], '</a>';
			}
		}
		// Otherwise, show the guest's email.
		elseif (empty($memberContext[$memCommID]['hide_email']))
			echo '
								<br />
								<br />
								<a href="mailto:', $memberContext[$memCommID]['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['downloads_txt_profile_email'] . '" title="' . $txt['downloads_txt_profile_email'] . '" border="0" />' : $txt['downloads_txt_profile_email']), '</a>';

		// Done with the information about the poster... on to the post itself.
		echo '
							</div>';
}

function Downloads_getRemoteFilesize($file_url)
{
	$file_url = trim($file_url);

	if (empty($file_url))
		return 0;

	 if (ini_get('allow_url_fopen') == 1)
	 {

		$head = array();
		$head['content-length'] = 0;


		try
		{
			$head = array_change_key_case(get_headers($file_url, 1));
		}
		 catch (Exception $e)
		{

		}

		$result = isset($head['content-length']) ? $head['content-length'] : 0;

		if (is_array($result))
			return 0;

		return $result;
	}
	 else
	 {
	 	return 0;

	 }
}
?>