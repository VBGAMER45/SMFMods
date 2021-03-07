<?php
/*
SMF Articles
Version 3.0
by:vbgamer45
https://www.smfhacks.com
*/


if (!defined('SMF'))
	die('Hacking attempt...');

function ArticlesMain()
{
	global $currentVersion, $modSettings, $boarddir, $boardurl, $context, $smcFunc;
	
	// Current version of the article system
	$currentVersion = '3.1';
	
	$context['articles21beta'] = false;
	
	// Load the main template file
    if (function_exists("set_tld_regex"))
    {
	   loadtemplate('Articles2.1');
        $context['articles21beta'] = true;
         $context['show_bbc'] = true;

			$modSettings['disableQueryCheck'] = 1;
			$smcFunc['db_query']('', "SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
			$modSettings['disableQueryCheck'] = 0;
        
    }
    else
        loadtemplate('Articles2');


	
	
	// Load the language files
	if (loadlanguage('Articles') == false)
		loadLanguage('Articles', 'english');
		
	// Check the articles path
	if (empty($modSettings['articlespath']))
		$modSettings['articles_path'] = $boarddir . '/articles/';
		
	if (empty($modSettings['articles_url']))
		$modSettings['articles_url'] = $boardurl . '/articles/';

	// Articles actions
	$subActions = array(
		'addarticle' => 'AddArticle',
		'addarticle2' => 'AddArticle2',
		'editarticle' => 'EditArticle',
		'editarticle2' => 'EditArticle2',
		'deletearticle' => 'DeleteArticle',
		'deletearticle2' => 'DeleteArticle2',
		'catup' => 'CatUpDown',
		'catdown' => 'CatUpDown',
		'addcat' => 'AddCat',
		'addcat2' => 'AddCat2',
		'editcat' => 'EditCat',
		'editcat2' => 'EditCat2',
		'deletecat' => 'DeleteCat',
		'deletecat2' => 'DeleteCat2',
		'rate' => 'RateArticle',
		'viewrating' => 'ViewRating',
		'delrating' => 'DeleteRating',
		'approve' => 'Approve',
		'noapprove' => 'NoApprove',
		'alist' => 'ApproveList',
		'admin' => 'ArticlesAdmin',
		'admin2' => 'ArticlesAdmin2',
		'admincat' => 'ArticlesAdminCats',
		'adminperm' => 'ArticlesAdminPerm',
		'catperm' => 'CatPerm',
		'catperm2' => 'CatPerm2',
		'catpermdelete' => 'CatPermDelete',
		'myarticles' => 'MyArticles',
		'search' => 'Search',
		'search2' => 'Search2',
		'view' => 'ViewArticle',
		'comlist' => 'CommentList',
		'comment' => 'AddComment',
		'comment2' => 'AddComment2',
		'apprcomment' => 'ApproveComment',
		'apprcomall' => 'ApproveAllComments',
		'delcomment' => 'DeleteComment',
		'importtp' => 'ImportTinyPortal',
		'importfaq' => 'ImportFAQ',
		'importkb' => 'ImportKnowledgeBase',
		'recount' => 'RecountArticleTotals',
		'delimage' => 'DeleteImage',
		'rss' => 'ShowRSSFeed',
        'copyright' => 'Articles_CopyrightRemoval',

	);

	// Follow the sa or just go to main article index.
	if (isset($_REQUEST['sa']) && !empty($subActions[$_REQUEST['sa']]))
		$subActions[$_REQUEST['sa']]();
	else
		MainView();

}

function MainView()
{
	global $context, $mbname, $txt, $smcFunc, $modSettings, $user_info, $scripturl, $user_info;

	// Check if the current user can view the articles list
	isAllowedTo('view_articles');

	// Load the main index articles template
	$context['sub_template']  = 'articlesmain';

	// To get Permissions text
	loadLanguage('Admin');
	
	$m_cats = allowedTo('articles_admin');
	$context['m_cats'] = $m_cats;
	
	$context['articles_cat_id'] = 0;
	
	$addarticle = allowedTo('add_articles');
	$context['addarticle'] = $addarticle;
	
	
	// MyArticles
	if ($addarticle && !($context['user']['is_guest']))	
		$context['articles']['buttons']['mylisting'] =  array(
		'text' => 'smfarticles_myarticles',
		'url' =>$scripturl . '?action=articles;sa=myarticles;u=' . $user_info['id'],
		'lang' => true,

	);

	// Search
	$context['articles']['buttons']['search'] =  array(
		'text' => 'smfarticles_search',
		'url' => $scripturl . '?action=articles;sa=search',
		'lang' => true,

	);
	
	// Check if there was a category
	if (isset($_REQUEST['cat']))
	{
		$cat = (int) $_REQUEST['cat'];
		// Check category level permission to view
		GetCatPermission($cat,'view');

		$dbresult = $smcFunc['db_query']('', "
		SELECT
			title
		FROM
			{db_prefix}articles_cat
		WHERE
			ID_CAT = $cat LIMIT 1");
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		// Set the page title
		$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' . $row['title'];
		$smcFunc['db_free_result']($dbresult);

		$context['articles_cat_id'] = $cat;
		$context['articles_cat_title'] = $row['title'];
		
		// Load the sub template
		$context['sub_template']  = 'articlelisting';
		
		if (isset($_REQUEST['sort']))
			$context['articles_sort'] = $_REQUEST['sort'];
		else 
			$context['articles_sort'] = '';
		
		if (isset($_REQUEST['sorto']))
			$context['articles_sorto'] = $_REQUEST['sorto'];
		else 
			$context['articles_sorto'] = '';	
		

		if (isset($_REQUEST['sort']))
		{
			switch(($_REQUEST['sort']))
			{
				case 'title':
					$sort = 'a.title';
				break;
				case 'date':
					$sort = 'a.date';
				break;
				case 'rating':
					$sort = 'a.rating';
				break;
				case 'views':
					$sort = 'a.views';
				break;
				case 'username':
					$sort = 'm.real_name';
				break;
				case 'comment':
					$sort = 'a.commenttotal';
				break;		
				
				default:
					$sort = 'a.ID_ARTICLE';
			}

		}
		else
			$sort = 'a.ID_ARTICLE';

		if (isset($_REQUEST['sorto']))
		{
			if ($_REQUEST['sorto'] == 'ASC')
				$sorto = 'ASC';
			else
				$sorto = 'DESC';
		}
		else
			$sorto = 'DESC';


		// Change sort order for articles
		if ($sorto == 'DESC')
			$newsorto = 'ASC';
		else
			$newsorto = 'DESC';

		$context['articles_newsorto'] = $newsorto;

		if (empty($modSettings['smfarticles_setarticlesperpage']))
			$modSettings['smfarticles_setarticlesperpage'] = 10;

		$context['start'] = (int) $_REQUEST['start'];
		
		// Show the articles in that category
		$dbresult = $smcFunc['db_query']('', "
		SELECT 
			a.ID_ARTICLE, a.title, a.date, a.rating, a.totalratings, m.real_name, a.ID_MEMBER, a.description, a.views, a.commenttotal  
		FROM {db_prefix}articles AS a 
		LEFT JOIN {db_prefix}members AS m  ON (a.ID_MEMBER = m.ID_MEMBER) 
		WHERE a.ID_CAT = $cat  AND a.approved = 1 ORDER BY $sort $sorto LIMIT $context[start]," . $modSettings['smfarticles_setarticlesperpage']);
		 $context['articles_listing'] = array();
		while ($row = $smcFunc['db_fetch_assoc']($dbresult))
			$context['articles_listing'][] = $row;
		$smcFunc['db_free_result']($dbresult);
		
		// Setup the paging
		$context['page_index'] = constructPageIndex($scripturl . '?action=articles;cat=' . $cat . ';sort=' . $context['articles_sort'] . ';sorto=' .$context['articles_sorto'], $_REQUEST['start'], GetTotalByCATID($cat), $modSettings['smfarticles_setarticlesperpage']);
			
		// Setup the Link Tree
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=articles',
			'name' => $txt['smfarticles_title']
		);
		
		GetParentLink($cat);
	
		

	}
	else
	{
		
		if ($context['user']['is_guest'])
			$groupid = -1;
		else
			$groupid =  $user_info['groups'][0];
		
		// Set page title
		$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'];

		$dbresult = $smcFunc['db_query']('', "
		SELECT 
			c.ID_CAT, c.title, c.imageurl, c.filename, c.roworder, c.description, p.view  
		FROM {db_prefix}articles_cat AS c
		LEFT JOIN {db_prefix}articles_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)  
		WHERE c.ID_PARENT = 0 ORDER BY roworder ASC");
		$context['articles_cat'] = array();
		while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			// Skip category if they do not have permission to view it
			if ($row['view'] == '0')
				continue;
			
			$context['articles_cat'][] = $row;
		}
		$smcFunc['db_free_result']($dbresult);
		
		
		// Only Admins need these queries to run
		
		if ($m_cats == true)
		{
		
			// Number of articles waiting approval
			
			$dbresult = $smcFunc['db_query']('', "
			SELECT 
				COUNT(*) AS total
			FROM {db_prefix}articles 
			WHERE approved = 0");
			$row = $smcFunc['db_fetch_assoc']($dbresult);
			$context['articlesapproval'] = $row['total'];
			$smcFunc['db_free_result']($dbresult);
			
			// Get the number of comments waiting approval
			$dbresult = $smcFunc['db_query']('', "
			SELECT 
				COUNT(*) AS total
			FROM {db_prefix}articles_comment  
			WHERE approved = 0");
			$row = $smcFunc['db_fetch_assoc']($dbresult);
			$context['commentsapproval'] = $row['total'];
			$smcFunc['db_free_result']($dbresult);
		}
		
	}
}

function AddCat()
{
	global $context, $mbname, $txt, $smcFunc, $sourcedir;
	
	// Check if they are allowed to add a category
	isAllowedTo('articles_admin');
	
	// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'descript',
		'value' => '',
		'width' => '90%',
		'form' => 'catform',
		'labels' => array(
			'addacat' => $txt['smfarticles_addcat']
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	
	

	$context['sub_template']  = 'addcat';

	 // Set the page title
	$context['page_title'] = $mbname  . ' - ' .  $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_addcat'];

	$dbresult = $smcFunc['db_query']('', "
	 SELECT
	 	ID_CAT, title, roworder, ID_PARENT 
	 FROM
	 	{db_prefix}articles_cat
	 ORDER BY title ASC");

	$context['articles_cat'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['articles_cat'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
	
	CreateArticlesPrettryCategory();
	
	if (isset($_REQUEST['cat']))
		$context['articles_parent']  = (int) $_REQUEST['cat'];
	else 
		$context['articles_parent']  = 0;

}

function AddCat2()
{
	global $smcFunc, $txt, $sourcedir;

	isAllowedTo('articles_admin');
	
		// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['descript_mode']) && isset($_REQUEST['descript']))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['descript'] = html_to_bbc($_REQUEST['descript']);

		// We need to unhtml it now as it gets done shortly.
		$_REQUEST['descript'] = un_htmlspecialchars($_REQUEST['descript']);


	}


	// Clean the input
	$title = $smcFunc['htmlspecialchars']($_REQUEST['title'], ENT_QUOTES);
	$description =  $smcFunc['htmlspecialchars']($_REQUEST['descript'], ENT_QUOTES);
	$image =  $smcFunc['htmlspecialchars']($_REQUEST['image'], ENT_QUOTES);
	$parent = (int) $_REQUEST['parent'];

	if ($title == '')
		fatal_error($txt['smfarticles_nocattitle'],false);

	// Do the order
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		roworder
	FROM {db_prefix}articles_cat
	ORDER BY roworder DESC");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	$order = $row['roworder'];
	$order++;

	// Insert the category
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles_cat
			(title, description,roworder,imageurl,ID_PARENT)
		VALUES ('$title', '$description',$order,'$image',$parent)");
	
	// Redirect back to the articles page
	redirectexit('action=articles');

}

function EditCat()
{
	global $context, $mbname, $txt, $smcFunc, $sourcedir;
	
	// Check if they can add categories
	isAllowedTo('articles_admin');
	
	$cat = (int) $_REQUEST['cat'];
	// Check if an article was selected
	if (empty($cat))
		fatal_error($txt['smfarticles_nocatselected']);

	
	// Load the subtemplate for the category
	$context['sub_template']  = 'editcat';

	// Set the page title
	$context['page_title'] = $mbname  . ' - ' .  $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_editcat'];

	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_CAT, title, roworder, ID_PARENT 
	FROM {db_prefix}articles_cat ORDER BY title ASC");
	$context['articles_cat'] = array();
	 while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		// Can't be a parent of itself
		if ($row['ID_CAT'] == $cat)
			continue;
			
		$context['articles_cat'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
	
	CreateArticlesPrettryCategory();
	
	// Get category information
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_CAT, title, description, imageurl, ID_PARENT 
	FROM {db_prefix}articles_cat 
	WHERE ID_CAT = $cat");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	$context['articles_data'] = $row;
    
    
    		
	// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'descript',
		'value' => $row['description'],
		'width' => '90%',
		'form' => 'catform',
		'labels' => array(
			'editcat' => $txt['smfarticles_editcat']
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	
	
}

function EditCat2()
{
	global $smcFunc, $txt, $sourcedir;
	
	isAllowedTo('articles_admin');
	
	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['descript_mode']) && isset($_REQUEST['descript']))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['descript'] = html_to_bbc($_REQUEST['descript']);

		// We need to unhtml it now as it gets done shortly.
		$_REQUEST['descript'] = un_htmlspecialchars($_REQUEST['descript']);


	}

	// Clean the input
	$title =  $smcFunc['htmlspecialchars']($_REQUEST['title'], ENT_QUOTES);
	$description =  $smcFunc['htmlspecialchars']($_REQUEST['descript'], ENT_QUOTES);
	$image =  $smcFunc['htmlspecialchars']($_REQUEST['image'], ENT_QUOTES);
	$catid = (int) $_REQUEST['catid'];
	$parent = (int) $_REQUEST['parent'];
	
	// Check if a category title was entered.
	if ($title == '')
		fatal_error($txt['smfarticles_nocattitle'],false);

	// Update the category
	$smcFunc['db_query']('', "UPDATE {db_prefix}articles_cat
		SET title = '$title', ID_PARENT = $parent, description = '$description', imageurl = '$image' WHERE ID_CAT = $catid LIMIT 1");

	// Redirect back to the articles index
	redirectexit('action=articles');

}

function DeleteCat()
{
	global $context, $mbname, $txt;
	
	isAllowedTo('articles_admin');
	
	// Get category id
	$catid = (int) $_REQUEST['cat'];

	if (empty($catid))
		fatal_error($txt['smfarticles_nocatselected']);

	$context['arcticle_cat'] = $catid;

	$context['sub_template']  = 'deletecat';

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_deltcat'];

}

function DeleteCat2()
{
	global $smcFunc;
	
	isAllowedTo('articles_admin');
	
	$catid = (int) $_REQUEST['catid'];

	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_ARTICLE 
	FROM {db_prefix}articles
	WHERE ID_CAT = $catid");

	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		// Delete Files
		$smcFunc['db_query']('', "DELETE FROM {db_prefix}articles_comment
		 WHERE ID_ARTICLE  = " . $row['ID_ARTICLE']);
		
		$smcFunc['db_query']('', "DELETE FROM {db_prefix}articles_rating
		 WHERE ID_ARTICLE  = " . $row['ID_ARTICLE']);
	}
	// Update Category parent
	$smcFunc['db_query']('', "UPDATE {db_prefix}articles_cat SET ID_PARENT = 0 WHERE ID_PARENT = $catid");

	// Delete All articles
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}articles WHERE ID_CAT = $catid");
	// Finally delete the category
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}articles_cat WHERE ID_CAT = $catid LIMIT 1");

	redirectexit('action=articles');
}

function AddArticle()
{
	global $context, $mbname, $txt, $smcFunc, $user_info, $modSettings, $sourcedir, $scripturl;
	
	isAllowedTo('add_articles');
    
    
        
  	$addarticle = allowedTo('add_articles');
	
	// MyArticles
	if ($addarticle && !($context['user']['is_guest']))	
		$context['articles']['buttons']['mylisting'] =  array(
		'text' => 'smfarticles_myarticles',
		'url' =>$scripturl . '?action=articles;sa=myarticles;u=' . $user_info['id'],
		'lang' => true,

	);

	// Search
	$context['articles']['buttons']['search'] =  array(
		'text' => 'smfarticles_search',
		'url' => $scripturl . '?action=articles;sa=search',
		'lang' => true,

	);

	// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'message',
		'value' => '',
		'width' => '90%',
		'form' => 'addarticle',
		'labels' => array(
			'addarticle' => $txt['smfarticles_addarticle']
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	

	$context['sub_template']  = 'addarticle';

	// Set the page title
	$context['page_title'] = $mbname  . ' - ' .  $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_addarticle'];

	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];
	
	if (isset($_REQUEST['cat']))
	{
		GetCatPermission($_REQUEST['cat'],'addarticle');
		$catid = (int) $_REQUEST['cat'];
	}
	else 
		$catid = 0;
	
	$context['articles_catid'] = $catid;
		
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		c.ID_CAT, c.title, c.roworder, p.view, p.addarticle, c.ID_PARENT    
	FROM {db_prefix}articles_cat AS c
	LEFT JOIN {db_prefix}articles_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)  
	ORDER BY c.title ASC");

	// Get category count
	$cat_count = $smcFunc['db_affected_rows']();
	if ($cat_count == 0)
		fatal_error($txt['smfarticles_nofirstcat'], false);
	
	$context['articles_cat'] = array();
		
	 while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		// Check if they have permission to add to this category.
		if ($row['view'] == '0' || $row['addarticle'] == '0' )
			continue;
				
					
		$context['articles_cat'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
	
	CreateArticlesPrettryCategory();
	
	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

}

function AddArticle2()
{
	global $user_info, $smcFunc, $txt, $sourcedir, $modSettings;

	isAllowedTo('add_articles');
	
	require_once($sourcedir . '/Subs-Post.php');
	
	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['message_mode']) && isset($_REQUEST['message']))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['message'] = html_to_bbc($_REQUEST['message']);

		// We need to unhtml it now as it gets done shortly.
		$_REQUEST['message'] = un_htmlspecialchars($_REQUEST['message']);


	}

	// Clean the input
	$title =  $smcFunc['htmlspecialchars']($_REQUEST['title'], ENT_QUOTES);
	$description = $smcFunc['htmlspecialchars']($_REQUEST['description'], ENT_QUOTES);
	$message = $smcFunc['htmlspecialchars']($_REQUEST['message'], ENT_QUOTES);
	$catid = (int) $_REQUEST['catid'];

	GetCatPermission($catid, 'addarticle');

	if (trim($title) == '')
		fatal_error($txt['smfarticles_noarticletitle'], false);

	if (trim($message) == '')
		fatal_error($txt['smfarticles_noarticletext'], false);
		
	preparsecode($message);
		
	// SEO Link not used
	$seotitle = str_replace(" ","_",$title);
	
	// Get the current date/time
	$t = time();
	
	
// Check if there is an image attachements
	$image_upload = false;
	if ($modSettings['smfarticles_allow_attached_images'] > 0 && isset($_FILES['uploadimage']['name']) && $_FILES['uploadimage']['name'] != '')
	{

		$sizes = @getimagesize($_FILES['uploadimage']['tmp_name']);
		
		// No size, then it's probably not a valid pic.
		if ($sizes === false)
		{
			@unlink($_FILES['uploadimage']['tmp_name']);
			fatal_error($txt['smfarticles_error_invalid_picture'],false);
		}
			
		$filesize = $_FILES['uploadimage']['size'];
		
		// Check FileSize
		if (!empty($modSettings['smfarticles_max_filesize']) && $filesize > $modSettings['smfarticles_max_filesize'])
		{

			// Delete the temp file
			@unlink($_FILES['uploadimage']['tmp_name']);
			fatal_error($txt['smfarticles_error_img_filesize'] . round($modSettings['smfarticles_max_filesize'] / 1024, 2) . 'kb',false);
		}
		
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
		
		// Copy the image
		$filename = $user_info['id']  . '_' . date('d_m_y_g_i_s') . '.' . $extension;
		move_uploaded_file($_FILES['uploadimage']['tmp_name'], $modSettings['articles_path'] .  $filename);
		@chmod($modSettings['articles_path'] .  $filename, 0644);
		require_once($sourcedir . '/Subs-Graphics.php');
		createThumbnail($modSettings['articles_path'] .   $filename, 100, 100);
		rename($modSettings['articles_path'] .  $filename . '_thumb',  $modSettings['articles_path'] .  'thumb_' . $filename);
		$thumbname = 'thumb_' . $filename;
		@chmod($modSettings['articles_path'] .   'thumb_' . $filename, 0755);
					
		$image_upload  = true;
		
	}
	
	// Check if aarticle is already approved
	$approved = allowedTo('articles_auto_approve') ? 1 : 0;
	
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles
			(ID_CAT, title, description,ID_MEMBER,date,approved,seotitle)
		VALUES ($catid,'$title', {string:description}," . $user_info['id'] . ",$t,$approved,'$seotitle')",
		array('description' => $description)
		);

	$articleid =  $smcFunc['db_insert_id']('{db_prefix}articles', 'ID_ARTICLE');
	
	// Add image attachments if found
	if ($image_upload == true)
	{
		// Create the Database Entry
		$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles_attachments
			(ID_ARTICLE , filename, thumbnail ,ID_MEMBER,date,filesize)
		VALUES ($articleid,'$filename', '$thumbname'," . $user_info['id'] . ",$t,$filesize)");

	}
	

	// Insert the Page
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles_page
			(ID_ARTICLE,pagetext)
		VALUES ($articleid, {string:message})",
		array('message' => $message)
		);

	UpdateCategoryTotals($catid);
	
	ArticlesCheckBadgeAwards($user_info['id']);
	
	// Redirect
	if ($approved)
		redirectexit('action=articles;cat=' . $catid);
	else 
		fatal_error($txt['smfarticles_articleneedsapproval']);
}

function EditArticle()
{
	global $context, $mbname, $txt, $smcFunc, $modSettings, $sourcedir, $user_info, $user_info, $scripturl;

	isAllowedTo('edit_articles');

	$context['sub_template']  = 'editarticle';
    
    
	$addarticle = allowedTo('add_articles');
	
	// MyArticles
	if ($addarticle && !($context['user']['is_guest']))	
		$context['articles']['buttons']['mylisting'] =  array(
		'text' => 'smfarticles_myarticles',
		'url' =>$scripturl . '?action=articles;sa=myarticles;u=' . $user_info['id'],
		'lang' => true,

	);

	// Search
	$context['articles']['buttons']['search'] =  array(
		'text' => 'smfarticles_search',
		'url' => $scripturl . '?action=articles;sa=search',
		'lang' => true,

	);
	
	// Set the page title
	$context['page_title'] = $mbname  . ' - ' . $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_editarticle'];

	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

	// Lookup the article and see if they can edit it.
	$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smfarticles_noarticleselected']);
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_ARTICLE,ID_CAT,ID_MEMBER 
	FROM {db_prefix}articles 
	WHERE ID_ARTICLE = $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	
	GetCatPermission($row['ID_CAT'], 'editarticle');
	
	$m_cats = allowedTo('articles_admin');
	
	$edit_article = allowedTo('edit_articles');

	// Check if the article belongs to them
	if ($m_cats == FALSE && ($edit_article == false || $row['ID_MEMBER'] != $user_info['id']))
		fatal_error($txt['smfarticles_err_articleedit'],false);
	// End security check
		
	$context['article_id'] = $id;

	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_ARTICLE, title, ID_CAT, description 
	FROM {db_prefix}articles 
	WHERE ID_ARTICLE = $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	$context['article_data'] = $row;
	
	// Get Article Page Data
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		pagetext 
	FROM {db_prefix}articles_page
	WHERE ID_ARTICLE = $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$context['article_page'] = $row;
	$smcFunc['db_free_result']($dbresult);

	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		c.ID_CAT, c.title, c.roworder, p.view, p.addarticle, c.ID_PARENT    
	FROM {db_prefix}articles_cat AS c
	LEFT JOIN {db_prefix}articles_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)  
	ORDER BY c.title ASC");

	//Get category count
	$cat_count = $smcFunc['db_affected_rows']();
	if ($cat_count == 0)
		fatal_error($txt['smfarticles_nofirstcat']);
	
	$context['articles_cat'] = array();
		
	 while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		// Check if they have permission to add to this category.
		if ($row['view'] == '0' || $row['addarticle'] == '0' )
			continue;
					
		$context['articles_cat'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
	
	CreateArticlesPrettryCategory();
	
	require_once($sourcedir . '/Subs-Post.php');
	
		// Needed for the WYSIWYG editor.
		require_once($sourcedir . '/Subs-Editor.php');
		

		// Now create the editor.
		$editorOptions = array(
			'id' => 'message',
			'value' => un_preparsecode($context['article_page']['pagetext']),
			'width' => '90%',
			'form' => 'editarticle',
			'labels' => array(
				'post_button' =>  $txt['smfarticles_editarticle']
			),
		);
		create_control_richedit($editorOptions);
		$context['post_box_name'] = $editorOptions['id'];
	
	
	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	
	$dbresult = $smcFunc['db_query']('', "
				SELECT 
					thumbnail, filesize, filename, ID_FILE  
				FROM {db_prefix}articles_attachments 
				WHERE ID_ARTICLE = " . $context['article_id']);
	$context['articles_images'] = array();
   	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
   	{
   		$context['articles_images'][] = $row;
   	}
   $smcFunc['db_free_result']($dbresult);
	
 
     foreach($context['articles_images'] as $row)
   	{
   	
   		//$newTag = array('code'=> 'cmd_image'  . $row['ID_FILE'] ,'before'=> "[img]" .$modSettings['articles_url'] . $row['filename']  . "[/img]", 'after' => '');
		//print_r($context['bbc_tags']);
	//	$context['bbc_tags'][1][] = $newTag;
   	}
   
  
	
}

function EditArticle2()
{
	global $smcFunc, $txt, $user_info, $sourcedir, $modSettings;

	isAllowedTo('edit_articles');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['smfarticles_noarticleselected']);
		
	require_once($sourcedir . '/Subs-Post.php');
		
		
	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['message_mode']) && isset($_REQUEST['message']))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['message'] = html_to_bbc($_REQUEST['message']);

		// We need to unhtml it now as it gets done shortly.
		$_REQUEST['message'] = un_htmlspecialchars($_REQUEST['message']);


	}
	
	// Clean the input
	$title = $smcFunc['htmlspecialchars']($_REQUEST['title'], ENT_QUOTES);
	$description = $smcFunc['htmlspecialchars']($_REQUEST['description'], ENT_QUOTES);
	$message = $smcFunc['htmlspecialchars']($_REQUEST['message'], ENT_QUOTES);
	$catid = (int) $_REQUEST['catid'];
	
	// Check category level permission
	GetCatPermission($catid,'editarticle');
	
	// Check if the article belongs to them
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_ARTICLE,ID_CAT,ID_MEMBER 
	FROM {db_prefix}articles 
	WHERE ID_ARTICLE = $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	
	$m_cats = allowedTo('articles_admin');
	$edit_article = allowedTo('edit_articles');

	// Check if the article belongs to them
	if ($m_cats == FALSE && ($edit_article == false || $row['ID_MEMBER'] != $user_info['id']))
		fatal_error($txt['smfarticles_err_articleedit'],false);
	
	if (trim($title) == '')
		fatal_error($txt['smfarticles_noarticletitle'], false);

	if (trim($message) == '')
		fatal_error($txt['smfarticles_noarticletext'], false);
		
	preparsecode($message);
		
if ($modSettings['smfarticles_allow_attached_images'] == 1 && isset($_FILES['uploadimage']['name']) && $_FILES['uploadimage']['name'] != '')
	{

		$sizes = @getimagesize($_FILES['uploadimage']['tmp_name']);
		
		// No size, then it's probably not a valid pic.
		if ($sizes === false)
		{
			@unlink($_FILES['uploadimage']['tmp_name']);
			fatal_error($txt['smfarticles_error_invalid_picture'],false);
		}
			
		$filesize = $_FILES['uploadimage']['size'];
		
		// Check FileSize
		if (!empty($modSettings['smfarticles_max_filesize']) && $filesize > $modSettings['smfarticles_max_filesize'])
		{
			// Delete the temp file
			@unlink($_FILES['uploadimage']['tmp_name']);
			fatal_error($txt['smfarticles_error_img_filesize'] . round($modSettings['smfarticles_max_filesize'] / 1024, 2) . 'kb',false);
		}
		
		$result = $smcFunc['db_query']('', "
			SELECT COUNT(*) as total FROM {db_prefix}articles_attachments
		 WHERE ID_ARTICLE = $id LIMIT 1");
		$rowCount = $smcFunc['db_fetch_assoc']($result);
		$smcFunc['db_free_result']($result);
		
		if ($rowCount['total'] >= $modSettings['smfarticles_max_num_attached'])
			fatal_error($txt['smfarticles_error_max_attached_images'] . $modSettings['smfarticles_max_num_attached'],false);
		
		
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
		
		// Copy the image
		$filename =  $user_info['id'] . '_' . date('d_m_y_g_i_s') . '.' . $extension;
		move_uploaded_file($_FILES['uploadimage']['tmp_name'], $modSettings['articles_path'] .  $filename);
		@chmod($modSettings['articles_path'] .  $filename, 0644);
		require_once($sourcedir . '/Subs-Graphics.php');
		createThumbnail($modSettings['articles_path'] .   $filename, 100, 100);
		rename($modSettings['articles_path'] .  $filename . '_thumb',  $modSettings['articles_path'] .  'thumb_' . $filename);
		$thumbname = 'thumb_' . $filename;
		@chmod($modSettings['articles_path'] .   'thumb_' . $filename, 0755);
		$t = time();
			
		$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles_attachments
					(ID_ARTICLE , filename, thumbnail ,ID_MEMBER,date,filesize)
				VALUES ($id,'$filename', '$thumbname'," . $user_info['id'] . ",$t,$filesize)");

		
	}
		
	
	// Update the article
	$smcFunc['db_query']('', "UPDATE {db_prefix}articles
		SET title = '$title', description = {string:description}, ID_CAT = $catid 
		WHERE ID_ARTICLE = $id LIMIT 1",
		array('description' => $description));
	
	// Update cateogry totals
	if ($row['ID_CAT'] != $catid)
	{
		UpdateCategoryTotals($catid);
		UpdateCategoryTotals($row['ID_CAT']);
	}
	
	
	// Update the article page
	$smcFunc['db_query']('', "UPDATE {db_prefix}articles_page 
		SET pagetext = {string:message} WHERE ID_ARTICLE = $id LIMIT 1",
		array('message' => $message));

// Redirect back to article
	redirectexit('action=articles;sa=view;article=' . $id);
}

function DeleteArticle()
{
	global $context, $mbname, $txt, $smcFunc, $user_info;
	
	isAllowedTo('delete_articles');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['smfarticles_noarticleselected']);

	// Check if they are allowed to delete the articles
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_ARTICLE, ID_CAT, ID_MEMBER, title 
	FROM {db_prefix}articles
	WHERE
		ID_ARTICLE = $id LIMIT 1");

	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	GetCatPermission($row['ID_CAT'], 'delarticle');
	
	// Check if they own this article
	$m_cats = allowedTo('articles_admin');
	
	$delete_article = allowedTo('delete_articles');

	// Check if the article belongs to them
	if ($m_cats == FALSE && ($delete_article == false || $row['ID_MEMBER'] != $user_info['id']))
		fatal_error($txt['smfarticles_err_articledelete'],false);
	
	$context['sub_template']  = 'deletearticle';
	$context['article_id'] = $id;
	$context['article_title'] = $row['title'];
	// Set the page title
	$context['page_title'] = $mbname  . ' - ' . $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_delarticle'];


}

function DeleteArticle2()
{
	global $smcFunc, $txt, $user_info;

	isAllowedTo('delete_articles');

	$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smfarticles_noarticleselected']);
		
	// Check if they are allowed to delete the articles
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		ID_ARTICLE, ID_CAT, ID_MEMBER
	FROM {db_prefix}articles
	WHERE
		ID_ARTICLE = $id LIMIT 1");

	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	GetCatPermission($row['ID_CAT'], 'delarticle');
		
	// Check if they own this article
	$m_cats = allowedTo('articles_admin');
	
	$delete_article = allowedTo('delete_articles');

	// Check if the article belongs to them
	if ($m_cats == FALSE && ($delete_article == false || $row['ID_MEMBER'] != $user_info['id']))
		fatal_error($txt['smfarticles_err_articledelete'],false);	
	
	// Delete the Article
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}articles WHERE ID_ARTICLE = $id LIMIT 1");

	// Delete pages
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}articles_page WHERE ID_ARTICLE = $id LIMIT 1");
		
	// Delete Comments
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}articles_comment WHERE ID_ARTICLE = $id LIMIT 1");
	// Delete Ratings
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}articles_rating WHERE ID_ARTICLE = $id LIMIT 1");

	// Update the totals for that category
	UpdateCategoryTotals($row['ID_CAT']);
	
	redirectexit('action=articles;cat=' . $row['ID_CAT']);
}

function CatUpDown()
{
	global $smcFunc, $txt;
	// Check if they are allowed to manage cats
	isAllowedTo('articles_admin');

	// Get the cat id
	$cat = (int) $_REQUEST['cat'];
	ReOrderCats($cat);
	
	// First get our row order
	$dbresult1 = $smcFunc['db_query']('', "
	SELECT 
		roworder 
	FROM {db_prefix}articles_cat 
	WHERE ID_CAT = $cat");
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	
	if ($_GET['sa'] == 'catup')
		$o--;
	else 
		$o++;
		
	$smcFunc['db_free_result']($dbresult1);
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_CAT, roworder 
	FROM {db_prefix}articles_cat 
	WHERE roworder = $o");
	
	if ($smcFunc['db_affected_rows']()== 0)
	{
		if ($_GET['sa'] == 'catup')
			fatal_error($txt['smfarticles_nocatabove'],false);
		else
			fatal_error($txt['smfarticles_nocatbelow'],false);
	}
		
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	// Swap the order Id's
	$smcFunc['db_query']('', "UPDATE {db_prefix}articles_cat 
		SET roworder = $oldrow WHERE ID_CAT = " .$row2['ID_CAT']);

	$smcFunc['db_query']('', "UPDATE {db_prefix}articles_cat 
		SET roworder = $o WHERE ID_CAT = $cat");

	// Redirect to index to view cats
	redirectexit('action=articles');
	
}

function ApproveList()
{
	global $context, $mbname, $txt, $smcFunc, $scripturl;
	
	isAllowedTo('articles_admin');

	$context['editarticle'] = allowedTo('edit_articles');
	$context['deletearticle'] = allowedTo('delete_articles');

	$context['sub_template']  = 'approvearticles';
	
	DoArticleAdminTabs();
	
	// Get Total Pages
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		COUNT(*) AS total 
	FROM {db_prefix}articles 
	WHERE approved = 0");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);
	
	$context['start'] = (int) $_REQUEST['start'];
	
	 $dbresult = $smcFunc['db_query']('', "
	 SELECT 
	 	l.ID_ARTICLE, l.approved,l.description, l.title,l.date, m.real_name,
	 	l.ID_MEMBER, l.description,l.views, l.ID_CAT, c.title catname 
	 FROM ({db_prefix}articles AS l, {db_prefix}articles_cat AS c) 
	 LEFT JOIN {db_prefix}members AS m ON (l.ID_MEMBER = m.ID_MEMBER) 
	 WHERE l.ID_CAT = c.ID_CAT AND l.approved = 0 ORDER BY l.ID_ARTICLE DESC 
	 LIMIT $context[start],20");
	$context['articles_list'] = array();
		while ($row = $smcFunc['db_fetch_assoc']($dbresult))
			$context['articles_list'][] = $row;
		$smcFunc['db_free_result']($dbresult);
		
		
	$context['page_index'] = constructPageIndex($scripturl . '?action=articles;cat=' , $_REQUEST['start'], $total, 20);
		
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] .' - ' . $txt['smfarticles_approvearticles'] ;
}

function Approve()
{
	global $smcFunc, $txt;

	isAllowedTo('articles_admin');
	
	// Get articles id
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['smfarticles_noarticleselected']);

	$smcFunc['db_query']('', "UPDATE {db_prefix}articles
		SET approved = 1 WHERE ID_ARTICLE = $id LIMIT 1");

	UpdateCategoryTotalByArticleID($id);
	
	redirectexit('action=admin;area=articles;sa=alist');
}

function NoApprove()
{
	global $smcFunc, $txt;

	isAllowedTo('articles_admin');
	
	// Get article id
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['smfarticles_noarticleselected']);

	$smcFunc['db_query']('', "UPDATE {db_prefix}articles
		SET approved = 0 WHERE ID_ARTICLE = $id LIMIT 1");

	UpdateCategoryTotalByArticleID($id);
	
	redirectexit('action=articles');
}

function RateArticle()
{
	global $scripturl, $smcFunc, $txt, $user_info, $modSettings;

	is_not_guest();

	// Check if they are allowed to rate articles
	isAllowedTo('rate_articles');
	
	if ($modSettings['smfarticles_enableratings'] == 0)
		fatal_error($txt['smfarticles_error_ratingsdisabled']);

	$id = (int) $_REQUEST['id'];
	
	if (empty($id))
		fatal_error($txt['smfarticles_noarticleselected']);
		
	$rating = (int) $_REQUEST['rating'];
	
	if (empty($rating))
		fatal_error($txt['smfarticles_err_no_rating_selected']);

	// Check if they rated this article?
    $dbresult = $smcFunc['db_query']('', "
    SELECT 
    	ID_MEMBER, ID_ARTICLE 
    FROM {db_prefix}articles_rating 
    WHERE ID_MEMBER = " . $user_info['id'] . " AND ID_ARTICLE = $id");

    $found = $smcFunc['db_affected_rows']();
 	$smcFunc['db_free_result']($dbresult);

	// Get the article owner
    $dbresult = $smcFunc['db_query']('', "
    SELECT 
    	ID_MEMBER 
    FROM {db_prefix}articles 
    WHERE ID_ARTICLE = $id LIMIT 1");
    $row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	
	// Check if they are rating their own article.
	if($user_info['id'] == $row['ID_MEMBER'])
		fatal_error($txt['smfarticles_error_norate_own'],false);

	if($found != 0)
		fatal_error($txt['smfarticles_error_already_rated'],false);

	// If they try and be tricky enter an average rating
	if ($rating < 1 || $rating > 5)
		$rating = 3;

	// Add the Rating
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles_rating (ID_MEMBER, ID_ARTICLE, value) VALUES (" . $user_info['id'] . ", $id,$rating)");
 
	// Add rating information to the artilcle
	$smcFunc['db_query']('', "UPDATE {db_prefix}articles SET totalratings = totalratings + 1, rating = rating + $rating WHERE ID_ARTICLE = $id LIMIT 1");


	// Redirect to article
	redirectexit('action=articles;sa=view;article=' . $id);

}

function DeleteRating()
{
	global $smcFunc, $txt;
	
	isAllowedTo('articles_admin');

	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['smfarticles_error_no_rating_selected']);

	// First lookup the ID to get the article id and value of rating
	 $dbresult = $smcFunc['db_query']('', "
	 SELECT 
	 	ID, ID_ARTICLE, value 
	 FROM {db_prefix}articles_rating 
	 WHERE ID = $id");
	 $row = $smcFunc['db_fetch_assoc']($dbresult);
	 $value = $row['value'];
	 $artid = $row['ID_ARTICLE'];
	 $smcFunc['db_free_result']($dbresult);
	 
	// Delete the Rating
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}articles_rating WHERE ID = " . $id . ' LIMIT 1');
	// Update the article rating information
	$dbresult = $smcFunc['db_query']('', "UPDATE {db_prefix}articles SET totalratings = totalratings - 1, rating = rating - $value WHERE ID_ARTICLE = $artid LIMIT 1");
	// Redirect to the ratings
	redirectexit('action=articles;sa=viewrating&id=' .  $artid);
}

function ViewRating()
{
	global $context, $mbname, $txt, $smcFunc;

	isAllowedTo('articles_admin');
	
	// Get the article ID for the ratings
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['smfarticles_noarticleselected']);

	$context['sub_template']  = 'view_rating';

	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_form_viewratings'];

	$context['article_id'] = $id;


	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		r.ID, r.value, r.ID_ARTICLE,  r.ID_MEMBER, m.real_name 
	FROM ({db_prefix}articles_rating as r, {db_prefix}members AS m) 
	WHERE r.ID_ARTICLE = $id AND r.ID_MEMBER = m.ID_MEMBER");
	
	$context['articles_rating'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['articles_rating'][] = $row;
	$smcFunc['db_free_result']($dbresult);
	
	
}

function ArticlesAdmin()
{
	global $context, $mbname, $txt;
	
	isAllowedTo('articles_admin');

	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_articlesconfig'];
	
	DoArticleAdminTabs();
	$context['sub_template']  = 'settings';

}

function ArticlesAdmin2()
{
	isAllowedTo('articles_admin');

	$smfarticles_setarticlesperpage = (int) $_REQUEST['smfarticles_setarticlesperpage'];
	$smfarticles_countsubcats = isset($_REQUEST['smfarticles_countsubcats']) ? 1 : 0;
	$smfarticles_enableratings = isset($_REQUEST['smfarticles_enableratings']) ? 1 : 0;
	$smfarticles_enablecomments = isset($_REQUEST['smfarticles_enablecomments']) ? 1 : 0;
	$smfarticles_sharingicons = isset($_REQUEST['smfarticles_sharingicons']) ? 1 : 0;
	$smfarticles_showrss = isset($_REQUEST['smfarticles_showrss']) ? 1 : 0;
	
	$smfarticles_disp_views = isset($_REQUEST['smfarticles_disp_views']) ? 1 : 0;
	$smfarticles_disp_rating = isset($_REQUEST['smfarticles_disp_rating']) ? 1 : 0;
	$smfarticles_disp_membername = isset($_REQUEST['smfarticles_disp_membername']) ? 1 : 0;
	$smfarticles_disp_date = isset($_REQUEST['smfarticles_disp_date']) ? 1 : 0;
	$smfarticles_disp_totalcomment = isset($_REQUEST['smfarticles_disp_totalcomment']) ? 1 : 0;
	
	$smfarticles_allow_attached_images = isset($_REQUEST['smfarticles_allow_attached_images']) ? 1 : 0;
	$smfarticles_max_num_attached = (int) $_REQUEST['smfarticles_max_num_attached'];
	$smfarticles_max_filesize = (int) $_REQUEST['smfarticles_max_filesize'];
	$smfarticles_images_view_article = isset($_REQUEST['smfarticles_images_view_article']) ? 1 : 0;
	$articles_path = $_REQUEST['articles_path'];
	$articles_url = $_REQUEST['articles_url'];
	
	
    // Save the setting information
	updateSettings(
	array(
	'smfarticles_setarticlesperpage' => $smfarticles_setarticlesperpage,
	'smfarticles_countsubcats' => $smfarticles_countsubcats,
	'smfarticles_enableratings' => $smfarticles_enableratings,
	'smfarticles_enablecomments' => $smfarticles_enablecomments,
	'smfarticles_sharingicons' => $smfarticles_sharingicons,
	'smfarticles_showrss' => $smfarticles_showrss,
	
	'smfarticles_disp_views' => $smfarticles_disp_views,
	'smfarticles_disp_rating' => $smfarticles_disp_rating,
	'smfarticles_disp_membername' => $smfarticles_disp_membername,
	'smfarticles_disp_date' => $smfarticles_disp_date,
	'smfarticles_disp_totalcomment' => $smfarticles_disp_totalcomment,
	
	'smfarticles_images_view_article' => $smfarticles_images_view_article,
	'smfarticles_allow_attached_images' => $smfarticles_allow_attached_images,
	'smfarticles_max_num_attached' => $smfarticles_max_num_attached,
	'smfarticles_max_filesize' => $smfarticles_max_filesize,
	'articles_path' => $articles_path,
	'articles_url' => $articles_url,
	
	
	));


	redirectexit('action=admin;area=articles;sa=admin');
}


function ArticlesAdminPerm()
{
	global $context, $mbname, $txt, $smcFunc;

	isAllowedTo('articles_admin');

	DoArticleAdminTabs();
	
	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_catpermlist'];

	$context['sub_template']  = 'catpermlist';
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		c.ID_CAT, c.ID, c.view, c.addarticle, c.editarticle, c.delarticle, c.ID_GROUP, m.group_name,a.title catname 
	FROM ({db_prefix}articles_catperm as c, {db_prefix}membergroups AS m,{db_prefix}articles_cat as a) 
	WHERE c.ID_GROUP = m.ID_GROUP AND a.ID_CAT = c.ID_CAT");
	$context['articles_mbgroups'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['articles_mbgroups'][] = $row;
	$smcFunc['db_free_result']($dbresult);
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		c.ID_CAT, c.ID, c.view, c.addarticle, c.editarticle, c.delarticle, c.ID_GROUP,a.title catname 
	FROM ({db_prefix}articles_catperm as c,{db_prefix}articles_cat as a) 
	WHERE  c.ID_GROUP = 0 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['articles_regular'] = array();		
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['articles_regular'][] = $row;
	$smcFunc['db_free_result']($dbresult);
	
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		c.ID_CAT, c.ID, c.view, c.addarticle, c.editarticle, c.delarticle, c.ID_GROUP,a.title catname 
	FROM ({db_prefix}articles_catperm as c,{db_prefix}articles_cat as a) 
	WHERE  c.ID_GROUP = -1 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['articles_guests'] = array();		
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['articles_guests'][] = $row;
	$smcFunc['db_free_result']($dbresult);
	
}

function GetCatPermission($cat,$perm)
{
	global $user_info,$smcFunc, $txt, $user_info;
	
	$cat = (int) $cat;
	
	if (!$user_info['is_guest'])
	{
		$dbresult = $smcFunc['db_query']('', "
		SELECT 
			m.ID_MEMBER, c.view, c.addarticle, c.editarticle, c.delarticle,c.ratearticle, c.report 
		FROM ({db_prefix}articles_catperm as c, {db_prefix}members as m) 
		WHERE m.ID_MEMBER = " . $user_info['id'] . " AND c.ID_GROUP = m.ID_GROUP AND c.ID_CAT = $cat LIMIT 1");
	}
	else
		$dbresult = $smcFunc['db_query']('', "
		SELECT 
			c.view, c.addarticle, c.editarticle, c.delarticle,c.ratearticle, c.report 
		FROM {db_prefix}articles_catperm as c 
		WHERE c.ID_GROUP = -1 AND c.ID_CAT = $cat LIMIT 1");

	if ($smcFunc['db_affected_rows']()== 0)
		$smcFunc['db_free_result']($dbresult);
	else
	{
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$smcFunc['db_free_result']($dbresult);
		
		if($perm == 'view' && $row['view'] == 0)
			fatal_error($txt['smfarticles_perm_no_view'],false);
		else if($perm == 'addarticle' && $row['addarticle'] == 0)
			fatal_error($txt['smfarticles_perm_no_add'],false);
		else if($perm == 'editarticle' && $row['editarticle'] == 0)
			fatal_error($txt['smfarticles_perm_no_edit'],false);
		else if($perm == 'delarticle' && $row['delarticle'] == 0)
			fatal_error($txt['smfarticles_perm_no_delete'],false);
		else if($perm == 'ratelarticle' && $row['ratearticle'] == 0)
			fatal_error($txt['smfarticles_perm_no_ratelink'],false);
		else if($perm == 'report' && $row['report'] == 0)
			fatal_error($txt['smfarticles_perm_no_report'],false);
	}


}

function CatPermDelete()
{
	global $smcFunc;
	
	isAllowedTo('articles_admin');

	$id = (int) $_REQUEST['id'];

	// Delete the Permission
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}articles_catperm WHERE ID = " . $id . ' LIMIT 1');
	// Redirect to the permissions list
	redirectexit('action=admin;area=articles;sa=adminperm');

}

function CatPerm()
{
	global $mbname, $txt, $smcFunc, $context;
	
	isAllowedTo('articles_admin');

	$cat = (int) $_REQUEST['cat'];
	if (empty($cat))
		fatal_error($txt['smfarticles_nocatselected']);

	$dbresult1 = $smcFunc['db_query']('', "
	SELECT 
		ID_CAT, title 
	FROM {db_prefix}articles_cat 
	WHERE ID_CAT = $cat LIMIT 1");
	$row1 = $smcFunc['db_fetch_assoc']($dbresult1);
	$context['articles_cat_name'] = $row1['title'];
	$smcFunc['db_free_result']($dbresult1);

	loadLanguage('Admin');

	$context['articles_cat'] = $cat;

	// Load the template
	$context['sub_template']  = 'catperm';
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_text_catperm'] . ' -' . $context['articles_cat_name'];

	// Load the membergroups
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_GROUP, group_name 
	FROM {db_prefix}membergroups 
	WHERE min_posts = -1 ORDER BY group_name");
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
			);
	}
	$smcFunc['db_free_result']($dbresult);
	
	
	// membergroups
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		c.ID_CAT, c.ID, c.view, c.addarticle, c.editarticle, c.delarticle, c.ID_GROUP, m.group_name,a.title catname 
	FROM ({db_prefix}articles_catperm as c, {db_prefix}membergroups AS m,{db_prefix}articles_cat as a) 
	WHERE  c.ID_CAT = " . $context['articles_cat'] . " AND c.ID_GROUP = m.ID_GROUP AND a.ID_CAT = c.ID_CAT");
	$context['articles_membergroup'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['articles_membergroup'][] = $row;
	$smcFunc['db_free_result']($dbresult);
	
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		c.ID_CAT, c.ID, c.view, c.addarticle, c.editarticle, c.delarticle, c.ID_GROUP,a.title catname 
	FROM ({db_prefix}articles_catperm as c,{db_prefix}articles_cat as a) 
	WHERE c.ID_CAT = " . $context['articles_cat'] . " AND c.ID_GROUP = 0 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['articles_reggroup'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['articles_reggroup'][] = $row;
	$smcFunc['db_free_result']($dbresult);	
	

	// Guests
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		c.ID_CAT, c.ID, c.view, c.addarticle, c.editarticle, c.delarticle, c.ID_GROUP,a.title catname 
	FROM ({db_prefix}articles_catperm as c,{db_prefix}articles_cat as a) 
	WHERE c.ID_CAT = " . $context['articles_cat'] . " AND c.ID_GROUP = -1 AND a.ID_CAT = c.ID_CAT LIMIT 1");
	$context['articles_guest'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['articles_guest'][] = $row;
	$smcFunc['db_free_result']($dbresult);	
	
}

function CatPerm2()
{
	global  $smcFunc, $txt;
	
	isAllowedTo('articles_admin');

	$groupname = (int) $_REQUEST['groupname'];
	$cat = (int) $_REQUEST['cat'];

	// Check if permission exits
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_GROUP,ID_CAT 
	FROM {db_prefix}articles_catperm 
	WHERE ID_GROUP = $groupname AND ID_CAT = $cat");
	if ($smcFunc['db_affected_rows']()!= 0)
	{
		$smcFunc['db_free_result']($dbresult);
		fatal_error($txt['smfarticles_permerr_permexist'],false);
	}
	$smcFunc['db_free_result']($dbresult);

	// Permissions
	$view = isset($_REQUEST['view']) ? 1 : 0;
	$add = isset($_REQUEST['add']) ? 1 : 0;
	$edit = isset($_REQUEST['edit']) ? 1 : 0;
	$delete = isset($_REQUEST['delete']) ? 1 : 0;

	// Insert into database
	$smcFunc['db_query']('', "
	INSERT INTO {db_prefix}articles_catperm
			(ID_GROUP,ID_CAT,view,addarticle,editarticle,delarticle)
		VALUES ($groupname,$cat,$view,$add,$edit,$delete)");
	
	redirectexit('action=articles;sa=catperm;cat=' . $cat);
}

function ReOrderCats($cat)
{
	global $smcFunc;

	$dbresult1 = $smcFunc['db_query']('', "
	SELECT 
		roworder,ID_PARENT 
	FROM {db_prefix}articles_cat  
	WHERE ID_CAT = $cat");
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$smcFunc['db_free_result']($dbresult1);

	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_CAT, roworder 
	FROM {db_prefix}articles_cat 
	WHERE ID_PARENT = $ID_PARENT ORDER BY roworder ASC");
	if ($smcFunc['db_affected_rows']() != 0)
	{
		$count = 1;
		while($row2 = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$smcFunc['db_query']('', "UPDATE {db_prefix}articles_cat
			SET roworder = $count WHERE ID_CAT = " . $row2['ID_CAT']);
			$count++;
		}
	}
	$smcFunc['db_free_result']($dbresult);
}

function DoArticleAdminTabs($overrideSelected = '')
{
	global $context, $txt, $smcFunc;
	
	$tmpSA = '';
	if (!empty($overrideSelected))
		$_REQUEST['sa'] = $overrideSelected;
	
	// Get the number articles waiting for approval
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) as total
	FROM {db_prefix}articles
	WHERE approved = 0");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$articles_total = $row['total'];
	$smcFunc['db_free_result']($dbresult);
	
	// Get the number comments waiting for approval
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) as total
	FROM {db_prefix}articles_comment 
	WHERE approved = 0");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$comment_total = $row['total'];
	$smcFunc['db_free_result']($dbresult);


	if (empty($context['admin_menu_name']))
    {
        $context['admin_menu_id'] = $context['max_menu_id'];
        $context['admin_menu_name'] = 'menu_data_' . $context['admin_menu_id'];
    }
		
	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' =>  $txt['smfarticles_admin'],
			'description' => '',
			'tabs' => array(
				'admin' => array(
					'description' => $txt['smfarticles_tabdesc_admin'],
				),
				'admincat' => array(
					'description' => $txt['smfarticles_tabdesc_admincat'],
				),
				'alist' => array(
					'description' => $txt['smfarticles_tabdesc_alist'],
					'label' => $txt['smfarticles_approvearticles'] . ' (' . $articles_total . ')',
				),
				'comlist' => array(
					'description' => $txt['smfarticles_tabdesc_comlist'],
					'label' =>  $txt['smfarticles_form_approvecomments'] . ' (' . $comment_total . ')',
					
				),
				'adminperm' => array(
					'description' => $txt['smfarticles_tabdesc_adminperm'],
					
				),
				'importtp' => array(
					'description' => $txt['smfarticles_tabdesc_importtp'],
					
				),
				'copyright' => array(
					'description' => $txt['smfarticles_tabdesc_copyright'],
					
				),
	
			),
		);	
		

}

function GetArticleTotals($ID_CAT)
{
	global $modSettings, $smcFunc, $subcats_linktree, $scripturl;
	
	$total = 0;

	$total += GetTotalByCATID($ID_CAT);
	$subcats_linktree = '';
	
	// Get the child categories to this category
	if ($modSettings['smfarticles_countsubcats'])
	{
		$firstCatDone = 0;
		
		$dbresult3 = $smcFunc['db_query']('', "
		SELECT 
			ID_CAT, total, title 
		FROM {db_prefix}articles_cat 
		WHERE ID_PARENT = $ID_CAT ORDER BY roworder ASC");
		while($row3 = $smcFunc['db_fetch_assoc']($dbresult3))
		{
			
			if ($firstCatDone == 1)
				$subcats_linktree .=',&nbsp;';
			
			$firstCatDone = 1;
			
			
			$subcats_linktree .= '<a href="' . $scripturl . '?action=articles;cat=' . $row3['ID_CAT'] . '">' . $row3['title'] . '</a>';
			
			if ($row3['total'] == -1)
			{
				$dbresult = $smcFunc['db_query']('', "
				SELECT 
					COUNT(*) AS total 
				FROM {db_prefix}articles 
				WHERE ID_CAT = " . $row3['ID_CAT'] . " AND approved = 1");
				$row = $smcFunc['db_fetch_assoc']($dbresult);
				$total2 = $row['total'];
				$smcFunc['db_free_result']($dbresult);
				
				
				$dbresult = $smcFunc['db_query']('', "UPDATE {db_prefix}articles_cat SET total = $total2 WHERE ID_CAT =  " . $row3['ID_CAT'] . " LIMIT 1");
			}
		}
		$smcFunc['db_free_result']($dbresult3);
		
		
		$dbresult3 = $smcFunc['db_query']('', "
		SELECT 
			SUM(total) AS finaltotal 
		FROM {db_prefix}articles_cat 
		WHERE ID_PARENT = $ID_CAT");
		$row3 = $smcFunc['db_fetch_assoc']($dbresult3);
		$smcFunc['db_free_result']($dbresult3);
		if ($row3['finaltotal'] != '')
			$total += $row3['finaltotal'];

	}

	return $total;
}

function GetTotalByCATID($ID_CAT)
{
	global $smcFunc;
	
		$dbresult = $smcFunc['db_query']('', "
		SELECT 
			total 
		FROM {db_prefix}articles_cat 
		WHERE ID_CAT = $ID_CAT");
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$smcFunc['db_free_result']($dbresult);
		
		if ($row['total'] != -1)
			return $row['total'];
		else
		{
			$dbresult = $smcFunc['db_query']('', "
			SELECT 
				COUNT(*) AS total 
			FROM {db_prefix}articles 
			WHERE ID_CAT = $ID_CAT AND approved = 1");
			$row = $smcFunc['db_fetch_assoc']($dbresult);
			$total = $row['total'];
			$smcFunc['db_free_result']($dbresult);
			
			// Update the count
			$dbresult = $smcFunc['db_query']('', "UPDATE {db_prefix}articles_cat SET total = $total WHERE ID_CAT = $ID_CAT LIMIT 1");
			
			// Return the total pictures
			return $total;
		}
		
}

function MyArticles()
{
	global $txt, $context, $user_info, $scripturl, $smcFunc, $modSettings, $mbname;
	
	is_not_guest();
	
	isAllowedTo('view_articles');
	
	$addarticle = allowedTo('add_articles');
	$context['addarticle'] = $addarticle;

	$context['editarticle'] = allowedTo('edit_articles');
	$context['deletearticle'] = allowedTo('delete_articles');
	
	// MyArticles
	if ($addarticle && !($context['user']['is_guest']))	
		$context['articles']['buttons']['mylisting'] =  array(
		'text' => 'smfarticles_myarticles',
		'url' =>$scripturl . '?action=articles;sa=myarticles;u=' . $user_info['id'],
		'lang' => true,

	);

	// Search
	$context['articles']['buttons']['search'] =  array(
		'text' => 'smfarticles_search',
		'url' => $scripturl . '?action=articles;sa=search',
		'lang' => true,

	);
	
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		COUNT(*) AS total 
	FROM {db_prefix}articles 
	WHERE ID_MEMBER = " . $user_info['id']);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);
	
	
	if (isset($_REQUEST['sort']))
		$context['articles_sort'] = $_REQUEST['sort'];
	else 
		$context['articles_sort'] = '';
		
	if (isset($_REQUEST['sorto']))
		$context['articles_sorto'] = $_REQUEST['sorto'];
	else 
		$context['articles_sorto'] = '';	
		

		if (isset($_REQUEST['sort']))
		{
			switch(($_REQUEST['sort']))
			{
				case 'title':
					$sort = 'a.title';
				break;
				case 'date':
					$sort = 'a.date';
				break;
				case 'rating':
					$sort = 'a.rating';
				break;

				case 'views':
					$sort = 'a.views';
				break;

				case 'username':
					$sort = 'm.real_name';
				break;
				
				case 'comment':
					$sort = 'a.commenttotal';
				break;		
				
				default:
					$sort = 'a.ID_ARTICLE';
			}

		}
		else
			$sort = 'a.ID_ARTICLE';


		if (isset($_REQUEST['sorto']))
		{
			if($_REQUEST['sorto'] == 'ASC')
				$sorto = 'ASC';
			else
				$sorto = 'DESC';
		}
		else
			$sorto = 'DESC';


	// Change sort order for articles
	if ($sorto == 'DESC')
		$newsorto = 'ASC';
	else
		$newsorto = 'DESC';

	$context['articles_newsorto'] = $newsorto;		
	
	
	if (empty($modSettings['smfarticles_setarticlesperpage']))
		$modSettings['smfarticles_setarticlesperpage'] = 10;

	$context['start'] = (int) $_REQUEST['start'];
		
	// Show the articles in that category
		$dbresult = $smcFunc['db_query']('', "
		SELECT 
			a.ID_ARTICLE, a.title, a.date, a.approved, a.rating, a.totalratings, m.real_name, a.ID_MEMBER, a.description, a.views, a.commenttotal  
		FROM {db_prefix}articles AS a 
		LEFT JOIN {db_prefix}members AS m  ON (a.ID_MEMBER = m.ID_MEMBER) 
		WHERE a.ID_MEMBER = " . $user_info['id'] . " ORDER BY $sort $sorto LIMIT $context[start]," . $modSettings['smfarticles_setarticlesperpage']);
		 $context['articles_listing'] = array();
		while ($row = $smcFunc['db_fetch_assoc']($dbresult))
			$context['articles_listing'][] = $row;
		$smcFunc['db_free_result']($dbresult);
		
	$context['page_index'] = constructPageIndex($scripturl . '?action=articles;sa=myarticles', $_REQUEST['start'], $total, $modSettings['smfarticles_setarticlesperpage']);
			
		
		
	$context['linktree'][] = array(
			'url' => $scripturl . '?action=articles',
			'name' => $txt['smfarticles_title']
		);
	
	
	$context['sub_template']  = 'myarticles';
	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_myarticles'];

	
}

function Search()
{
	global $txt, $context, $user_info, $scripturl, $user_info, $smcFunc, $mbname;
	
	isAllowedTo('view_articles');
	
	$addarticle = allowedTo('add_articles');
	
	// MyArticles
	if ($addarticle && !($context['user']['is_guest']))	
		$context['articles']['buttons']['mylisting'] =  array(
		'text' => 'smfarticles_myarticles',
		'url' =>$scripturl . '?action=articles;sa=myarticles;u=' . $user_info['id'],
		'lang' => true,

	);

	// Search
	$context['articles']['buttons']['search'] =  array(
		'text' => 'smfarticles_search',
		'url' => $scripturl . '?action=articles;sa=search',
		'lang' => true,

	);
	
	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		c.ID_CAT, c.title, p.view, c.ID_PARENT  
	FROM {db_prefix}articles_cat as c 
	LEFT JOIN {db_prefix}articles_catperm AS p ON (p.ID_GROUP = $groupid AND c.ID_CAT = p.ID_CAT)  
	ORDER BY c.title ASC");
	$context['articles_cat'] = array();
	 while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		// Check if they have permission to search these categories
		if ($row['view'] == '0')
				continue;
			
		$context['articles_cat'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
	
	CreateArticlesPrettryCategory();

	$context['sub_template']  = 'search';

	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' .$txt['smfarticles_search'];

}

function Search2()
{
	global $txt, $context, $user_info, $scripturl, $smcFunc, $modSettings, $mbname;
	
	isAllowedTo('view_articles');
	
	$addarticle = allowedTo('add_articles');
	
	// MyArticles
	if ($addarticle && !($context['user']['is_guest']))	
		$context['articles']['buttons']['mylisting'] =  array(
		'text' => 'smfarticles_myarticles',
		'url' =>$scripturl . '?action=articles;sa=myarticles;u=' . $user_info['id'],
		'lang' => true,

	);

	// Search
	$context['articles']['buttons']['search'] =  array(
		'text' => 'smfarticles_search',
		'url' => $scripturl . '?action=articles;sa=search',
		'lang' => true,

	);
	
	
	if (isset($_REQUEST['q']))
	{
		$data = json_decode(base64_decode($_REQUEST['q']),true);
		@$_REQUEST['cat'] = $data['cat'];
		@$_REQUEST['searchtitle'] = $data['searchtitle'];
		@$_REQUEST['searchdescription'] = $data['searchdescription'];
		@$_REQUEST['daterange'] = $data['daterange'];
		@$_REQUEST['pic_postername'] = $data['pic_postername'];
		@$_REQUEST['searchfor'] = $data['searchfor'];
		
	}

		if (isset($_REQUEST['cat']))
			$cat = (int) $_REQUEST['cat'];
		else 
			$cat = 0;
	
		
			// Probably a normal Search
			if (empty($_REQUEST['searchfor']))
				fatal_error($txt['smfarticles_error_no_search'],false);
	
			$searchfor =  $smcFunc['htmlspecialchars']($_REQUEST['searchfor'],ENT_QUOTES);
	
	
			if (strlen(trim($searchfor)) <= 3)
				fatal_error($txt['smfarticles_error_search_small'],false);
	
			// Check the search options
			$searchtitle = isset($_REQUEST['searchtitle']) ? 1 : 0;
			$searchdescription =  isset($_REQUEST['searchdescription']) ? 1 : 0;
			if (isset($_REQUEST['daterange']))
				$daterange = (int) $_REQUEST['daterange'];
			else 
				$daterange = 0;
				
			$memid = 0;
			
			// Check if searching by member id
			if (!empty($_REQUEST['pic_postername']))
			{
				$pic_postername = str_replace('"','', $_REQUEST['pic_postername']);
				$pic_postername = str_replace("'",'', $pic_postername);
				$pic_postername = str_replace('\\','', $pic_postername);
				$pic_postername = $smcFunc['htmlspecialchars']($pic_postername, ENT_QUOTES);
				$searchArray['pic_postername'] = $pic_postername;
						
						
				$dbresult = $smcFunc['db_query']('', "
						SELECT 
							real_name, ID_MEMBER 
						FROM {db_prefix}members 
						WHERE real_name = '$pic_postername' OR member_name = '$pic_postername'  LIMIT 1");
						$row = $smcFunc['db_fetch_assoc']($dbresult);
						$smcFunc['db_free_result']($dbresult);
						
				if ($smcFunc['db_affected_rows']() != 0)
					$memid = $row['ID_MEMBER'];
					
			}
			
			$searchArray['searchfor'] = $searchfor;
			$searchArray['cat'] = $cat;
			$searchArray['searchtitle'] = $searchtitle;
			$searchArray['searchdescription'] = $searchdescription;
			$searchArray['daterange'] = $daterange;
			$searchArray['memid'] = $memid;		
			$context['articles_search_query_encoded'] = base64_encode(json_encode($searchArray));
			
			
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
	
			
			if ($searchdescription)
			{
				if ($s1 == 1)
					$searchquery = "p.title LIKE '%$searchfor%' OR p.description LIKE '%$searchfor%'";
				else
					$searchquery = "p.description LIKE '%$searchfor%'";
			}
		
	
	
			if ($searchquery == '')
				$searchquery = "p.title LIKE '%$searchfor%' ";
	
			$context['smfarticles_search_query'] = $searchquery;

	
			$context['smfarticles_search'] = $searchfor;
		
	// Now actually do the results
	
	$smfarticles_where = '';
	if (isset($context['catwhere']))
		$smfarticles_where = $context['catwhere'];

		
    $dbresult = $smcFunc['db_query']('', "
    SELECT 
    	p.ID_ARTICLE
    FROM {db_prefix}articles as p 
    WHERE  " . $smfarticles_where . " p.approved = 1 AND (" . $context['smfarticles_search_query'] . ")");
    $numrows = $smcFunc['db_num_rows']($dbresult);
    $smcFunc['db_free_result']($dbresult);
    
    
	$context['start'] = (int) $_REQUEST['start'];
	

    $dbresult = $smcFunc['db_query']('', "
    SELECT 
    	p.ID_ARTICLE, p.title, p.date, p.rating, p.totalratings, m.real_name, p.ID_MEMBER, p.description, p.views, p.commenttotal   
    FROM {db_prefix}articles as p 
    LEFT JOIN {db_prefix}members AS m ON (m.ID_MEMBER = p.ID_MEMBER)  
    WHERE  " . $smfarticles_where . " p.approved = 1 AND (" . $context['smfarticles_search_query'] . ")
     LIMIT $context[start]," . $modSettings['smfarticles_setarticlesperpage']);
	$context['articles_listing'] =array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['articles_listing'][] = $row;
    $smcFunc['db_free_result']($dbresult);
    
    
   	$q = $context['articles_search_query_encoded'];
			
    $context['page_index'] = constructPageIndex($scripturl . '?action=articles;sa=search2;q=' .$q, $_REQUEST['start'], $numrows, $modSettings['smfarticles_setarticlesperpage']);
			
	$context['sub_template']  = 'search_results';
	
 	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' .$txt['smfarticles_searchresults'];
}

function ShowSubCats($cat, $m_cats)
{
	global $txt, $smcFunc, $scripturl, $subcats_linktree, $modSettings, $context;

		// List all the catagories
		$dbresult = $smcFunc['db_query']('', "
		SELECT 
			ID_CAT, title, roworder, description, imageurl 
		FROM {db_prefix}articles_cat 
		WHERE ID_PARENT = $cat ORDER BY roworder ASC");
		if ($smcFunc['db_affected_rows']() != 0)
		{

		
			
			if ($context['articles21beta'] == false)
			{
				echo '<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">
						
		<thead>
		<tr class="catbg">
				<th scope="col" class="smalltext first_th" colspan="2">', $txt['smfarticles_ctitle'], '</th>
				<th scope="col" class="smalltext' . ($m_cats ? '' : ' last_th') . '" align="center">', $txt['smfarticles_totalarticles'], '</th>
				';
		
				if ($m_cats)
					echo '
					<th scope="col" class="smalltext">',$txt['smfarticles_text_reorder'],'</th>
					<th scope="col" class="smalltext last_th">', $txt['smfarticles_options'],'</th>';
		echo '
			</tr>
			</thead>';
			}
			else
			{
					echo '<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">
							
			<thead>
			<tr class="title_bar">
					<th class="lefttext first_th" colspan="2">', $txt['smfarticles_ctitle'], '</th>
					<th class="lefttext' . ($m_cats ? '' : ' last_th') . '" align="center">', $txt['smfarticles_totalarticles'], '</th>
					';
			
					if ($m_cats)
						echo '
						<th class="lefttext">',$txt['smfarticles_text_reorder'],'</th>
						<th class="lefttext last_th">', $txt['smfarticles_options'],'</th>';
			echo '
				</tr>
				</thead>';	
			}
			

			while($row = $smcFunc['db_fetch_assoc']($dbresult))
			{
	
				$totalarticles = GetArticleTotals($row['ID_CAT']);
	
				echo '<tr>';
	
					if ($row['imageurl'] == '')
						echo '<td class="windowbg" width="10%"></td><td class="windowbg2"><b><a href="', $scripturl, '?action=articles;cat=', $row['ID_CAT'], '">', parse_bbc($row['title']), '</a></b> ' . (!empty($modSettings['smfarticles_showrss']) ? '<a href="' . $scripturl . '?action=articles;sa=rss;cat=' . $row['ID_CAT'] . '"><img src="' . $modSettings['articles_url'] . 'rss.png" alt="rss" /></a>' : '') . '<br />',  parse_bbc($row['description']), '</td>';
					else
					{
						echo '<td class="windowbg"><a href="', $scripturl, '?action=articles;cat=', $row['ID_CAT'], '"><img src="', $row['imageurl'], '" border="0" alt="" /></a></td>';
						echo '<td class="windowbg2"><b><a href="', $scripturl, '?action=articles;cat=', $row['ID_CAT'], '">', parse_bbc($row['title']), '</a></b> ' . (!empty($modSettings['smfarticles_showrss']) ? '<a href="' . $scripturl . '?action=articles;sa=rss;cat=' . $row['ID_CAT'] . '"><img src="' . $modSettings['articles_url'] . 'rss.png" alt="rss" /></a>' : '') . '<br />', parse_bbc($row['description']), '</td>';
					}
	
				// Show total articles
				echo '<td class="windowbg" align="center">', $totalarticles, '</td>';
	
				// Show Edit Delete and Order category
				if ($m_cats)
				{
					echo '
					<td class="windowbg2"><a href="', $scripturl, '?action=articles;sa=catup;cat=', $row['ID_CAT'], '">', $txt['smfarticles_txtup'], '</a>&nbsp;<a href="', $scripturl, '?action=articles;sa=catdown;cat=', $row['ID_CAT'], '">', $txt['smfarticles_txtdown'], '</a></span></td>
					<td class="windowbg"><a href="', $scripturl, '?action=articles;sa=editcat;cat=', $row['ID_CAT'], '">', $txt['smfarticles_txtedit'], '</a>&nbsp;<a href="', $scripturl, '?action=articles;sa=deletecat;cat=', $row['ID_CAT'], '">', $txt['smfarticles_txtdel'], '</a>
					<br />
					<a href="', $scripturl, '?action=articles;sa=catperm;cat=', $row['ID_CAT'], '">', $txt['smfarticles_txt_perm'], '</a>
					</td>';
				}
	
	
				echo '</tr>';
				
				// Show child Boards
				if ($subcats_linktree  != '')
					echo '<tr>
					<td colspan="',($m_cats == true ? '5' : '3'), '" class="windowbg3">
						<span class="smalltext">',($subcats_linktree != '' ? '<b>' . $txt['smfarticles_sub_cats'] . '</b>' . $subcats_linktree : ''),'</span>
					</td>
				</tr>';
					
			}

		echo '</table><br /><br />';
		}
		$smcFunc['db_free_result']($dbresult);
	
}

function ViewArticle()
{
	global $txt, $context, $user_info, $scripturl, $smcFunc, $mbname, $user_info;
	
	// Check if the current user can view the articles list
	isAllowedTo('view_articles');
	
	$m_cats = allowedTo('articles_admin');
	$context['m_cats'] = $m_cats;

	if (isset($_REQUEST['id']))
		$_REQUEST['article'] = $_REQUEST['id'];
	
	if (!isset($_REQUEST['article']))
		fatal_error($txt['smfarticles_noarticleselected'],false);
		
	$article = (int) $_REQUEST['article'];
	
	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];
	
	// Show the articles in that category
	$dbresult = $smcFunc['db_query']('', "
		SELECT 
			a.ID_ARTICLE, a.title, a.date, p.view, a.approved, a.rating, a.totalratings, m.real_name, a.ID_MEMBER,
			 a.description, a.views, a.commenttotal, a.ID_CAT  
		FROM {db_prefix}articles AS a 
		LEFT JOIN {db_prefix}members AS m  ON (a.ID_MEMBER = m.ID_MEMBER) 
		LEFT JOIN {db_prefix}articles_catperm AS p ON (p.ID_GROUP = $groupid AND a.ID_CAT = p.ID_CAT)  
	WHERE a.ID_ARTICLE = $article LIMIT 1");
	
	if ($smcFunc['db_num_rows']($dbresult) == 0)
		fatal_error($txt['smfarticles_err_article_not_found'],false);
		
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	
	
	$context['article'] = $row;
	$smcFunc['db_free_result']($dbresult);
	

		

	// Check Approval
	if ($row['approved'] == 0 && $m_cats == false && $row['ID_MEMBER'] != $user_info['id'])
		fatal_error($txt['smfarticles_err_articlenotapproved'],false);
	
	// Check if article is allowed to be viewed
	if ($row['view'] == '0' && ($m_cats == false && $row['ID_MEMBER'] != $user_info['id']))
		fatal_error($txt['smfarticles_perm_no_view_article'],false);


	$addarticle = allowedTo('add_articles');
	
	// MyArticles
	if ($addarticle && !($context['user']['is_guest']))	
		$context['articles']['buttons']['mylisting'] =  array(
		'text' => 'smfarticles_myarticles',
		'url' =>$scripturl . '?action=articles;sa=myarticles;u=' . $user_info['id'],
		'lang' => true,

	);

	// Search
	$context['articles']['buttons']['search'] =  array(
		'text' => 'smfarticles_search',
		'url' => $scripturl . '?action=articles;sa=search',
		'lang' => true,

	);
	
	
	// View Article Buttons
	
	// Edit Article
	if ($m_cats == true  || $row['ID_MEMBER'] == $user_info['id'])
		$context['articles']['view_article']['edit'] =  array(
		'text' => 'smfarticles_txtedit3',
		'url' =>$scripturl . '?action=articles;sa=editarticle&id=' . $article,
		'lang' => true,

	);

	// Delete Article
	if ($m_cats == true  || $row['ID_MEMBER'] == $user_info['id'])
		$context['articles']['view_article']['delete'] =  array(
		'text' => 'smfarticles_txtdel3',
		'url' => $scripturl . '?action=articles;sa=deletearticle&id=' . $article,
		'lang' => true,

	);
	
	// Link Tree
	$context['linktree'][] = array(
			'url' => $scripturl . '?action=articles',
			'name' => $txt['smfarticles_title']
		);
		
	GetParentLink($context['article']['ID_CAT']);
	
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		pagetext 
	FROM {db_prefix}articles_page
	WHERE ID_ARTICLE = $article LIMIT 1");
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);
	$context['article_page'] = $row2;
	$smcFunc['db_free_result']($dbresult);
	
	// Display all user comments
	$dbresult = $smcFunc['db_query']('', "
		SELECT 
			c.ID_ARTICLE, c.ID_COMMENT, c.date, c.comment, c.ID_MEMBER, c.lastmodified, 
			c.modified_ID_MEMBER, m.posts, m.real_name, c.approved 
		FROM ({db_prefix}articles_comment as c)
		LEFT JOIN {db_prefix}members AS m ON (c.ID_MEMBER = m.ID_MEMBER) 
		WHERE c.ID_ARTICLE = " . $context['article']['ID_ARTICLE'] . " AND c.approved = 1   ORDER BY c.ID_COMMENT DESC");
	$comment_count = $smcFunc['db_affected_rows']();	
	$context['article_comments'] = array();
	while($row3 = $smcFunc['db_fetch_assoc']($dbresult))
		$context['article_comments'][] = $row3;
		
	$smcFunc['db_free_result']($dbresult);
	
	$context['article_comment_count'] = $comment_count;
		
	// Update Views
	 $dbresult = $smcFunc['db_query']('', "UPDATE {db_prefix}articles 
		SET views = views + 1 WHERE ID_ARTICLE = $article LIMIT 1");
	
	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' . $row['title'];
	
	$context['sub_template']  = 'viewarticle';
	
	
	$dbresult = $smcFunc['db_query']('', "
				SELECT 
					thumbnail, filesize, filename, ID_FILE  
				FROM {db_prefix}articles_attachments 
				WHERE ID_ARTICLE = " . $article);
	$context['articles_images'] = array();
   	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
   	{
   		$context['articles_images'][] = $row;
   	}
   $smcFunc['db_free_result']($dbresult);
	
}

function UpdateCategoryTotals($ID_CAT)
{
	global $smcFunc;
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		COUNT(*) AS total 
	FROM {db_prefix}articles 
	WHERE ID_CAT = $ID_CAT AND approved = 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);
			
	// Update the count
	$smcFunc['db_query']('', "UPDATE {db_prefix}articles_cat SET total = $total WHERE ID_CAT = $ID_CAT LIMIT 1");
			
}

function UpdateCategoryTotalByArticleID($id)
{
	global $smcFunc;
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_CAT 
	FROM {db_prefix}articles  
	WHERE ID_ARTICLE = $id");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	
	UpdateCategoryTotals($row['ID_CAT']);

}

function ApproveComment()
{
	global $smcFunc;
	
	isAllowedTo('articles_admin');

	$id = (int) $_REQUEST['id'];
	// Approve all the comments
	$smcFunc['db_query']('', "UPDATE {db_prefix}articles_comment
		SET approved = 1 WHERE ID_COMMENT = $id AND approved = 0");

	// Reditrect the comment list
	redirectexit('action=articles;sa=comlist');
}

function ApproveAllComments()
{
	global $scripturl, $smcFunc;
	
	isAllowedTo('articles_admin');

	// Approve all the comments
	$smcFunc['db_query']('', "UPDATE {db_prefix}articles_comment
		SET approved = 1 WHERE approved = 0");

	// Reditrect the comment list
	redirectexit('action=admin;area=articles;sa=comlist');
}

function CommentList()
{
	global $context, $mbname, $txt, $smcFunc, $scripturl;
	
	isAllowedTo('articles_admin');
	
	
	DoArticleAdminTabs();
	
	// Get Total Pages
	$dbresult = $smcFunc['db_query']('', "
		SELECT 
			COUNT(*) AS total 
		FROM {db_prefix}articles_comment 
		WHERE approved = 0 ORDER BY ID_COMMENT DESC");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);
		
	$context['start'] = (int) $_REQUEST['start'];

	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		c.ID_COMMENT, c.ID_ARTICLE, c.comment, c.date, c.ID_MEMBER, m.real_name 
	FROM {db_prefix}articles_comment as c 
	LEFT JOIN {db_prefix}members AS m ON (c.ID_MEMBER = m.ID_MEMBER) 
	WHERE c.approved = 0 ORDER BY c.ID_COMMENT DESC LIMIT $context[start],10");
	$context['comment_list'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['comment_list'][] = $row;
	$smcFunc['db_free_result']($dbresult);
	
	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=articles;sa=comlist', $_REQUEST['start'], $total, 10);
			
	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_commentlist'];

	$context['sub_template']  = 'comment_list';
}

function AddComment()
{
	global $txt, $smcFunc, $context, $modSettings, $mbname, $sourcedir,  $user_info, $scripturl;
	// Check if they are allowed to comment
	isAllowedTo('articles_comment');
	
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['smfarticles_noarticleselected']);
        
        
  	$addarticle = allowedTo('add_articles');
	
	// MyArticles
	if ($addarticle && !($context['user']['is_guest']))	
		$context['articles']['buttons']['mylisting'] =  array(
		'text' => 'smfarticles_myarticles',
		'url' =>$scripturl . '?action=articles;sa=myarticles;u=' . $user_info['id'],
		'lang' => true,

	);

	// Search
	$context['articles']['buttons']['search'] =  array(
		'text' => 'smfarticles_search',
		'url' => $scripturl . '?action=articles;sa=search',
		'lang' => true,

	);


	// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'message',
		'value' => '',
		'width' => '90%',
		'form' => 'addcomment',
		'labels' => array(
			'addcomment' => $txt['smfarticles_text_addcomment']
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	
	
	
	// Save the article id
	$context['article_id'] = $id;
	

	// Comments allowed check
    $dbresult = $smcFunc['db_query']('', "
    SELECT 
    	p.ID_CAT  
    FROM {db_prefix}articles as p 
    WHERE ID_ARTICLE = $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$ID_CAT = $row['ID_CAT'];
	$smcFunc['db_free_result']($dbresult);

	// Check the category level permissions
	if ($row['ID_CAT'] != 0 )
		GetCatPermission($ID_CAT,'addcomment');
	// Load the subtemplate
	$context['sub_template']  = 'add_comment';
	// Setup the page name
	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - '  . $txt['smfarticles_text_addcomment'];
	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

}

function AddComment2()
{
	global $txt, $smcFunc, $user_info, $sourcedir;	

	isAllowedTo('articles_comment');
	
	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['message_mode']) && isset($_REQUEST['message']))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['message'] = html_to_bbc($_REQUEST['message']);

		// We need to unhtml it now as it gets done shortly.
		$_REQUEST['message'] = un_htmlspecialchars($_REQUEST['message']);

	}
	
	$comment = $smcFunc['htmlspecialchars']($_REQUEST['message'],ENT_QUOTES);
	$id = (int) $_REQUEST['id'];
	if (empty($id))
		fatal_error($txt['smfarticles_noarticleselected']);

	// Check if that picture allows comments.
    $dbresult = $smcFunc['db_query']('', "
    SELECT 
    	p.ID_CAT, m.email_address,p.ID_MEMBER,p.title 
    FROM {db_prefix}articles as p
    LEFT JOIN {db_prefix}members as m ON (p.ID_MEMBER  = m.ID_MEMBER) 
    WHERE p.ID_ARTICLE = $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	// Check if they are allowed to add comments to that category
	if ($row['ID_CAT'] != 0)
		GetCatPermission($row['ID_CAT'],'addcomment');

	if (trim($comment) == '')
		fatal_error($txt['smfarticles_error_no_comment'],false);

	$commentdate = time();

	// Check if you have automatic approval
	$approved = (allowedTo('articles_autocomment') ? 1 : 0);

	$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles_comment
			(ID_MEMBER, comment, date, ID_ARTICLE,approved)
		VALUES (" . $user_info['id'] . ",'$comment', $commentdate,$id,$approved)");

	// Update Comment total
	 $smcFunc['db_query']('', "UPDATE {db_prefix}articles 
		SET commenttotal = commenttotal + 1 WHERE ID_ARTICLE = $id LIMIT 1");
	 
	 ArticlesCheckBadgeAwards($user_info['id']);


	redirectexit('action=articles;sa=view;article=' . $id);
}

function DeleteComment()
{
	global $context, $smcFunc, $txt;

	isAllowedTo('articles_admin');

	$id = (int) $_REQUEST['id'];
	if (isset($_REQUEST['ret']))
		$ret = $_REQUEST['ret'];

	if (empty($id))
		fatal_error($txt['smfarticles_error_no_com_selected']);

	// Get the article ID for redirect
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_ARTICLE,ID_COMMENT, ID_MEMBER 
	FROM {db_prefix}articles_comment 
	WHERE ID_COMMENT = $id LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$articleid = $row['ID_ARTICLE'];
	$smcFunc['db_free_result']($dbresult);
	
	// Now delete the comment.
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}articles_comment WHERE ID_COMMENT = $id LIMIT 1");

	// Update Comment total
	$smcFunc['db_query']('', "UPDATE {db_prefix}articles
		SET commenttotal = commenttotal - 1 WHERE ID_ARTICLE = $articleid LIMIT 1");

	// Redirect to the article
	if (empty($ret))
		redirectexit('action=articles;sa=view;article=' . $articleid);
	else
		redirectexit('action=admin;area=articles;sa=comlist');
}

function ImportTinyPortal()
{
	global $smcFunc, $context, $txt, $modSettings, $db_prefix;
	
	isAllowedTo('articles_admin');
	
	$tp_prefix = $db_prefix .'tp_';
	

	DoArticleAdminTabs();
	
	$context['import_results'] = '';
	
	$context['page_title'] = $txt['smfarticles_txt_import'];

	$context['sub_template']  = 'import';
	
	
		$dbresult = $smcFunc['db_query']('', "
		SELECT 
			c.ID_CAT, c.title, c.roworder, c.ID_PARENT 
		FROM {db_prefix}articles_cat AS c 
		ORDER BY c.title ASC");

		
		$context['articles_cat'] = array();
			
		 while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
	
			$context['articles_cat'][] = $row;
		}
		$smcFunc['db_free_result']($dbresult);
		
		CreateArticlesPrettryCategory();
		
		
	
	if (isset($_REQUEST['doimport']))
	{
		$cat = (int) $_REQUEST['catid'];
		if (empty($cat))
			fatal_error($txt['smfarticles_nocatselected']);
		
		$modSettings['disableQueryCheck'] = 1;


		$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {$tp_prefix}articles");
        $author_idField = "authorid";

        while ($row = $smcFunc['db_fetch_row']($dbresult))
        {
            if ($row[0] == 'author_id')
                $author_idField = "author_id";
        }


		
		$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles (ID_MEMBER,title,description,views,approved,date )
		 SELECT $author_idField ID_MEMBER, subject title, intro description, views, approved, date FROM {$tp_prefix}articles ");
		// Insert all the pages.
		$result = $smcFunc['db_query']('', "
		SELECT 
			a.ID_ARTICLE, a.ID_MEMBER, t.body, t.subject 
		FROM {db_prefix}articles AS a, {$tp_prefix}articles AS t
		WHERE t.$author_idField = a.ID_MEMBER AND t.date = a.date AND a.title = t.subject
		 ");
		$context['import_results'] = '<b>' . $txt['smfarticles_txt_importedarticles'] . '</b><br />';
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$context['import_results'] .= $row['subject'] . '<br />';
			
			$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles_page
			(ID_ARTICLE,pagetext)
		VALUES (" . $row['ID_ARTICLE'] . ", '" . $smcFunc['db_escape_string']($row['body']) . "')");

			
			// Update each article with the category
			$smcFunc['db_query']('', "UPDATE {db_prefix}articles
			SET ID_CAT = $cat WHERE ID_ARTICLE = " . $row['ID_ARTICLE'] );
			
		}
		$smcFunc['db_free_result']($result);
		
		// Update cateogry totals
		UpdateCategoryTotals($cat);
		
		$modSettings['disableQueryCheck'] = 0;
		
	}
	

}

function ImportKnowledgeBase()
{
	global $smcFunc, $txt, $modSettings;
	
	if (isset($_REQUEST['doimport']))
	{
		$cat = (int) $_REQUEST['catid'];
		if (empty($cat))
			fatal_error($txt['smfarticles_nocatselected']);
		
		$modSettings['disableQueryCheck'] = 1;
		
		$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles (ID_MEMBER,approved,title,views,date)
		 SELECT id_member,approved,title,views,date FROM {db_prefix}kb_articles");
		// Insert all the pages.
		$result = $smcFunc['db_query']('', "
		SELECT 
			a.ID_ARTICLE, a.ID_MEMBER, t.content, t.title 
		FROM {db_prefix}articles AS a, {db_prefix}kb_articles AS t
		WHERE t.id_member = a.ID_MEMBER AND t.date = a.date AND a.title = t.title 
		 ");
		$context['import_results'] = '<b>' . $txt['smfarticles_txt_importedarticles'] . '</b><br />';
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$context['import_results'] .= $row['title'] . '<br />';
			
			$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles_page
			(ID_ARTICLE,pagetext)
		VALUES (" . $row['ID_ARTICLE'] . ", '" . $smcFunc['db_escape_string']($row['content']) . "')");

			
			// Update each article with the category
			$smcFunc['db_query']('', "UPDATE {db_prefix}articles
			SET ID_CAT = $cat WHERE ID_ARTICLE = " . $row['ID_ARTICLE'] );
			
		}
		$smcFunc['db_free_result']($result);
		
		// Update cateogry totals
		UpdateCategoryTotals($cat);
		
		$modSettings['disableQueryCheck'] = 0;
		
		redirectexit('action=articles;cat=' . $cat);
		
	}
}

function ImportFAQ()
{
	global $smcFunc, $txt, $modSettings;
	
	if (isset($_REQUEST['doimport']))
	{
		$cat = (int) $_REQUEST['catid'];
		if (empty($cat))
			fatal_error($txt['smfarticles_nocatselected']);
		
		$modSettings['disableQueryCheck'] = 1;
		
		$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles (ID_MEMBER,title,date )
		 SELECT last_user,title,UNIX_TIMESTAMP(timestamp) FROM {db_prefix}faq");
		// Insert all the pages.
		$result = $smcFunc['db_query']('', "
		SELECT 
			a.ID_ARTICLE, a.ID_MEMBER, t.body, t.title 
		FROM {db_prefix}articles AS a, {db_prefix}faq AS t
		WHERE t.last_user = a.ID_MEMBER AND t.last_user = a.id_member AND a.title = t.title
		 ");
		$context['import_results'] = '<b>' . $txt['smfarticles_txt_importedarticles'] . '</b><br />';
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$context['import_results'] .= $row['title'] . '<br />';
			
			$smcFunc['db_query']('', "INSERT INTO {db_prefix}articles_page
			(ID_ARTICLE,pagetext)
		VALUES (" . $row['ID_ARTICLE'] . ", '" . $smcFunc['db_escape_string']($row['body']) . "')");

			
			// Update each article with the category
			$smcFunc['db_query']('', "UPDATE {db_prefix}articles
			SET ID_CAT = $cat WHERE ID_ARTICLE = " . $row['ID_ARTICLE'] );
			
		}
		$smcFunc['db_free_result']($result);
		
		// Update cateogry totals
		UpdateCategoryTotals($cat);
		
		$modSettings['disableQueryCheck'] = 0;
		
		redirectexit('action=articles;cat=' . $cat);
		
	}
}

function DoToolBarStrip($button_strip, $direction )
{	
	global $settings, $txt;

	if (!empty($settings['use_tabs']))
	{
		echo '<td>';
		template_button_strip($button_strip, $direction);
		echo '</td>';
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

function GetParentLink($ID_CAT)
{
	global  $smcFunc, $context, $scripturl;
	
	if ($ID_CAT == 0)
		return;
		
	$dbresult1 = $smcFunc['db_query']('', "
		SELECT
			ID_PARENT,title
		FROM {db_prefix}articles_cat
		WHERE ID_CAT = $ID_CAT LIMIT 1");
	$row1 = $smcFunc['db_fetch_assoc']($dbresult1);

	$smcFunc['db_free_result']($dbresult1);
		
	GetParentLink($row1['ID_PARENT']);
		
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=articles;cat=' . $ID_CAT ,
					'name' => $row1['title']
				);
}

function RecountArticleTotals()
{
	global $smcFunc;
	
	isAllowedTo('articles_admin');
	
	$dbresult1 = $smcFunc['db_query']('', "
		SELECT
			ID_CAT
		FROM {db_prefix}articles_cat
		");
	while($row = $smcFunc['db_fetch_assoc']($dbresult1))
	{
		UpdateCategoryTotals($row['ID_CAT']);
	}
	$smcFunc['db_free_result']($dbresult1);
	
	redirectexit('action=admin;area=articles;sa=admin');
}

function DeleteImage()
{
	global $smcFunc, $modSettings;
	
	$id =  (int) $_REQUEST['id'];
	
	$dbresult = $smcFunc['db_query']('', "
		SELECT 
			ID_ARTICLE, filename, thumbnail 
		FROM {db_prefix}articles_attachments 
		WHERE ID_FILE = $id
		");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	
	// Delete the main image
	@unlink($modSettings['articles_path'] . $row['filename']);
	// Delete the thumbnail
	@unlink($modSettings['articles_path'] . $row['thumbnail']);
	
	$smcFunc['db_query']('', "
		DELETE 
		FROM {db_prefix}articles_attachments 
		WHERE ID_FILE = $id
		");
	
	
	redirectexit('action=articles;sa=editarticle&id=' . $row['ID_ARTICLE']);
}

function CreateArticlesPrettryCategory()
{
	global $context;
	
		$finalArray = array();
		
		$parentList = array(0);
		$newParentList = array();
		$spacer = 0;
		for ($g = 0;$g < count($parentList); $g++)
		{
			$tmpLevelArray = array();
			for ($i = 0;$i < count($context['articles_cat']);$i++)
			{
				if ($context['articles_cat'][$i]['ID_PARENT'] == $parentList[$g])
				{
			
					$newParentList[] = $context['articles_cat'][$i]['ID_CAT'];
					$newParentList = array_unique($newParentList);
					$context['articles_cat'][$i]['title'] = str_repeat('-', $spacer) .$context['articles_cat'][$i]['title'];
					$tmpLevelArray[] = $context['articles_cat'][$i];
				}
			}
			
			// Check Top Level ID_PARENT
			if ($parentList[$g] == 0)
			{
				$finalArray = $tmpLevelArray;
			}
			else 
			{
				$tmpArray2 = array();
				for($j = 0;$j<count($finalArray);$j++)
				{
					$tmpArray2[] = $finalArray[$j];
					// Find Parent good Now we just insert the records that we found right after the parent
					if ($finalArray[$j]['ID_CAT'] == $parentList[$g])
					{
						for ($z = 0;$z < count($tmpLevelArray);$z++)
						{
							$tmpArray2[] = $tmpLevelArray[$z];
						}
					}
				}
				
				$finalArray = $tmpArray2;
			}
			$tmpLevelArray = array();
			
		
			if ($g == (count($parentList) -1) && !empty($newParentList))
			{

				$parentList = array();
				$parentList = $newParentList;
				$newParentList = array();
				$g=-1;
				$spacer++;
			}
			else if ($g == (count($parentList) -1) && empty($newParentList))
			{
	
			}
			

		}
		
		$context['articles_cat'] = array();
		$context['articles_cat'] = $finalArray;
}

function ShowArticlesBar($title = '&nbsp;')
{
	global $txt, $context;
		echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $title, '
        </h3>
</div>';


	if ($context['articles21beta'] == false)
	{
	echo '
					<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
							<tr>
								<td style="padding-right: 1ex;" align="right" width="100%">
							<table cellpadding="0" cellspacing="0" align="right">
										<tr>
										<td align="right">
							', DoToolBarStrip($context['articles']['buttons'], 'top'), '
							</td>	
							</tr>
								</table>
							</td>
							</tr>
						</table>
	
	<br />';
	}
	else
	{
		echo template_button_strip($context['articles']['buttons'], 'right');  
	    echo '<br /><br />';
	}


}

function ShowRSSFeed()
{
	global $smcFunc, $txt, $user_info, $scripturl, $context, $modSettings;
	
	isAllowedTo('view_articles');
	
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();
	
	header('Content-Type: application/rss+xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));
	
	
		
	if (isset($_REQUEST['limit']))
		$limit = (int) $_REQUEST['limit'];
	else 
		$limit = 20;
	
	if (isset($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];
	else 
		$cat = 0;
		
	echo '<?xml version="1.0" encoding="ISO-8859-1"?>
<rss version="0.92" xml:lang="', strtr($txt['lang_locale'], '_', '-'), '">
<channel>
	';

	$catwhere = '';
	if (!empty($cat))
	{
		$catwhere = ' AND a.ID_CAT = ' . $cat;
		
		$dbresult = $smcFunc['db_query']('', "
		SELECT 
			ID_CAT, title, description, imageurl, ID_PARENT 
		FROM {db_prefix}articles_cat 
		WHERE ID_CAT = $cat");
		$catRow = $smcFunc['db_fetch_assoc']($dbresult);
		
	
		echo '
			<title>' . $catRow['title'] . '</title>';
	}
	else
	echo '
		<title>' . $txt['smfarticles_title'] . '</title>';

		
	echo '
	<link>', $scripturl, '?action=articles</link>
	<description></description>
	
	';
	
	
	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];
	

	$result = $smcFunc['db_query']('', "
		SELECT 
			a.ID_ARTICLE, a.title, a.date, p.view, a.approved, a.rating, 
			a.totalratings, m.real_name, a.ID_MEMBER,
			 a.description, a.views, a.commenttotal, a.ID_CAT  
		FROM {db_prefix}articles AS a 
		LEFT JOIN {db_prefix}members AS m  ON (a.ID_MEMBER = m.ID_MEMBER) 
		LEFT JOIN {db_prefix}articles_catperm AS p ON (p.ID_GROUP = $groupid AND a.ID_CAT = p.ID_CAT)  
	WHERE a.approved = 1 $catwhere ORDER BY a.ID_ARTICLE DESC  LIMIT $limit");
		
		
		while($row = $smcFunc['db_fetch_assoc']($result))
		{
			
		
	echo '	<item>
			<title><![CDATA[', $row['title'], ']]></title>
			<link><![CDATA[', $scripturl, '?action=articles;sa=view&article=',$row['ID_ARTICLE'],']]></link>
			<author>',$row['real_name'],'</author>
			<comments><![CDATA[', $scripturl, '?action=articles;sa=view&article=',$row['ID_ARTICLE'],']]>0.0</comments>
			<pubDate>',gmdate('D, d M Y H:i:s \G\M\T', $row['date']),'</pubDate>
			<description><![CDATA[';
			
				
				$dbresult = $smcFunc['db_query']('', "
				SELECT 
					pagetext 
				FROM {db_prefix}articles_page
				WHERE ID_ARTICLE = " . $row['ID_ARTICLE'] . " LIMIT 1");
				$row2 = $smcFunc['db_fetch_assoc']($dbresult);
				$context['article_page'] = $row2;

				
			
			
			echo parse_bbc($row2['pagetext']),']]></description>
			
			<guid><![CDATA[', $scripturl, '?action=articles;sa=view&article=',$row['ID_ARTICLE'],']]></guid>
		</item>
';
			

		}
		

	


	
	echo '
	</channel>
</rss>';
	
	obExit(false);
	
	die();
}

function ArticlesCheckBadgeAwards($memID = 0)
{
	global $sourcedir, $modSettings;
	
	if (empty($memID))
		return;
	
	if (!empty($modSettings['badgeawards_enable']))
	{
	
		require_once($sourcedir . '/badgeawards2.php');
		Badges_CheckMember($memID);
	}
}

function Articles_CopyrightRemoval()
{
    global $context, $mbname, $txt, $modSettings;
	isAllowedTo('articles_admin');

    if (isset($_REQUEST['save']))
    {
        $articles_copyrightkey = $_REQUEST['articles_copyrightkey'];

        updateSettings(
    	array(
    	'articles_copyrightkey' => $articles_copyrightkey,
    	)

    	);
    }

	DoArticleAdminTabs();

	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_txt_copyrightremoval'];

	$context['sub_template']  = 'articles_copyright';
}

function ArticlesCheckInfo()
{
    global $modSettings, $boardurl;

    if (isset($modSettings['articles_copyrightkey']))
    {
        $m = 42;
        if (!empty($modSettings['articles_copyrightkey']))
        {
            if ($modSettings['articles_copyrightkey'] == sha1($m . '-' . $boardurl))
            {
                return false;
            }
            else
                return true;
        }
    }

    return true;
}

function ArticlesAdminCats()
{
	global $context, $mbname, $txt, $smcFunc;

	isAllowedTo('articles_admin');

	$context['page_title'] = $mbname . ' - ' . $txt['smfarticles_title'] . ' - ' . $txt['smfarticles_txt_managecategories'];

	DoArticleAdminTabs();

	$context['sub_template']  = 'manage_cats';

	$dbresult = $smcFunc['db_query']('', "
		SELECT
			id_cat, title, id_parent, total
		FROM {db_prefix}articles_cat
		 ORDER BY roworder ASC");

	$context['articles_cat'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['articles_cat'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
}

?>