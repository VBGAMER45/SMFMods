<?php
/*
SMF Links
Version 2.0
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function LinksMain()
{
	// Load the main links template
	loadtemplate('Links');

	// Load the language files
	if (loadlanguage('Links') == false)
		loadLanguage('Links','english');

	// Link actions
	$subActions = array(

		'addlink' => 'AddLink',
		'addlink2' => 'AddLink2',
		'editlink' => 'EditLink',
		'editlink2' => 'EditLink2',
		'visit' => 'VisitLink',
		'deletelink' => 'DeleteLink',
		'deletelink2' => 'DeleteLink2',
		'catup' => 'CatUp',
		'catdown' => 'CatDown',
		'addcat' => 'AddCat',
		'addcat2' => 'AddCat2',
		'editcat' => 'EditCat',
		'editcat2' => 'EditCat2',
		'deletecat' => 'DeleteCat',
		'deletecat2' => 'DeleteCat2',
		'rate' => 'RateLink',
		'approve' => 'Approve',
		'noapprove' => 'NoApprove',
		'alist' => 'ApproveList',
		'admin' => 'LinksAdmin',
		'admin2' => 'LinksAdmin2',
		'admincat' => 'LinksAdminCats',
		'adminperm' => 'LinksAdminPerm',
		'catperm' => 'CatPerm',
		'catperm2' => 'CatPerm2',
		'catpermdelete' => 'CatPermDelete',
	);


	// Follow the sa or just go to main links index.
	if (!empty($subActions[@$_GET['sa']]))
		$subActions[$_GET['sa']]();
	else
		view();

}

function view()
{
	global $context, $mbname, $txt, $db_prefix, $scripturl, $modSettings;

	// Check if the current user can view the links list
	isAllowedTo('view_smflinks');

	// Load the main index links template
	$context['sub_template']  = 'mainview';
	
	// Setup Intial Link Tree
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=links',
					'name' => $txt['smflinks_menu']
				);

	// Check if there was a category
	if (isset($_REQUEST['cat']))
	{
		$cat = (int) $_REQUEST['cat'];

		GetCatPermission($cat,'view');
		// List all the catagories
		$dbresult = db_query("
		SELECT 
			title, ID_CAT 
		FROM {$db_prefix}links_cat 
		WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
        
        if (empty($row['ID_CAT']))
            fatal_error($txt['smflinks_nocatselected'],false);
        
		// Set the page title
		$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $row['title'];
		
		GetParentLink($cat);

		mysql_free_result($dbresult);
		
		// Cat Info
		$dbresult = db_query("
		SELECT 
			ID_CAT, title, description 
		FROM {$db_prefix}links_cat 
		WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
		$context['linkcatrow'] = $row;
		mysql_free_result($dbresult);
		
		// Total Pages
		$dbresult = db_query("
		SELECT 
			COUNT(*) AS total 
		FROM {$db_prefix}links 
		WHERE ID_CAT = $cat  AND approved = 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
		$total = $row['total'];
		$context['linkstotalpages'] = $total;
		mysql_free_result($dbresult);
		
		$context['start'] = (int) $_REQUEST['start'];	
		if (isset($_REQUEST['sort']))
		{
			switch(($_REQUEST['sort']))
			{

				case 'title':
					$sort = 'l.title';
				
				break;
				case 'date':
					$sort = 'l.date';
				break;
				case 'rating':
					$sort = 'l.rating';
				break;
				
				case 'hits':
					$sort = 'l.hits';
				break;
				
				case 'username':
					$sort = 'm.realName';
				break;
	
				default:
					$sort = 'l.ID_LINK';
			}
			
		}
		else
		{
			$sort = 'l.ID_LINK';
		}
		
		
		if (isset($_REQUEST['sorto']))
		{
			if($_REQUEST['sorto'] == 'ASC')
				$sorto = 'ASC';
			else 
				$sorto = 'DESC';
		}
		else 
			$sorto = 'DESC';
			

		$dbresult = db_query("
		SELECT 
			l.ID_LINK,l.title,l.date, l.pagerank, l.alexa, l.rating, m.realName, l.ID_MEMBER, l.description,l.hits FROM {$db_prefix}links AS l 
		LEFT JOIN {$db_prefix}members AS m  ON (l.ID_MEMBER = m.ID_MEMBER) 
		WHERE l.ID_CAT = $cat  AND l.approved = 1 ORDER BY $sort $sorto LIMIT $context[start]," . $modSettings['smflinks_setlinksperpage'], __FILE__, __LINE__);
		$context['linkslist_count'] = db_affected_rows();
		$context['linkslist'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['linkslist'][] = $row;
		}
		mysql_free_result($dbresult);
		
		

	}
	else
	{
	
		$context['page_title'] = $mbname . $txt['smflinks_title'];
		
		$dbresult = db_query("
		SELECT 
			ID_CAT, title, image, roworder, description 
		FROM {$db_prefix}links_cat 
		WHERE ID_PARENT = 0 ORDER BY roworder ASC", __FILE__, __LINE__);
		$context['cat_count'] = db_affected_rows();
		$context['catlist'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['catlist'][] = $row;
		}
		mysql_free_result($dbresult);
		
		// Top 5 rated links
		$dbresult = db_query("
			SELECT 
				l.ID_LINK, l.rating, l.title,l.date, m.realName, l.ID_MEMBER, l.description,l.hits 
			FROM {$db_prefix}links AS l 
			LEFT JOIN {$db_prefix}members AS m ON (l.ID_MEMBER = m.ID_MEMBER)  
			WHERE l.approved = 1 ORDER BY l.rating DESC LIMIT 5", __FILE__, __LINE__);
		
		$context['linkstop5'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['linkstop5'][] = $row;
		}
		mysql_free_result($dbresult);
		
		// Top 5 Hits
		$dbresult = db_query("
			SELECT 
				l.ID_LINK, l.rating, l.title,l.date, m.realName, l.ID_MEMBER, l.description,l.hits 
			FROM {$db_prefix}links AS l 
				LEFT JOIN {$db_prefix}members AS m  ON (l.ID_MEMBER = m.ID_MEMBER) 
			WHERE l.approved = 1 ORDER BY l.hits DESC LIMIT 5", __FILE__, __LINE__);
		$context['linkstophits'] = array();
		while($row = mysql_fetch_assoc($dbresult))
		{
			$context['linkstophits'][] = $row;
		}
		mysql_free_result($dbresult);
			
		
		// Unapproved links
		 
		$dbresult = db_query("
			SELECT 
				COUNT(*) AS total 
			FROM {$db_prefix}links AS l 
			WHERE l.approved = 0", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
		$context['alinks_total'] = $row['total'];
		mysql_free_result($dbresult);
		
		// Stats
		$dbresult = db_query("
			SELECT 
				COUNT(*) AS total 
			FROM {$db_prefix}links WHERE approved = 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
		$context['link_count'] = $row['total'];
		mysql_free_result($dbresult);
		
		
	}
}

function GetParentLink($ID_CAT)
{
	global $db_prefix, $context, $scripturl;
	if ($ID_CAT == 0)
		return;
		
	$dbresult1 = db_query("
		SELECT
			ID_PARENT, title
		FROM {$db_prefix}links_cat
		WHERE ID_CAT = $ID_CAT LIMIT 1", __FILE__, __LINE__);
	$row1 = mysql_fetch_assoc($dbresult1);

	mysql_free_result($dbresult1);
		
	GetParentLink($row1['ID_PARENT']);
		
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=links;cat=' . $ID_CAT ,
					'name' => $row1['title']
				);
}


function AddCat()
{
	global $context, $mbname, $txt, $db_prefix;
	isAllowedTo('links_manage_cat');

	$context['sub_template']  = 'addcat';

	// Set the page title
	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_addcat'];

	$dbresult = db_query("
	SELECT 
		ID_CAT, title,roworder 
	FROM {$db_prefix}links_cat 
	ORDER BY roworder ASC", __FILE__, __LINE__);
	$context['links_cat'] = array();
	 while($row = mysql_fetch_assoc($dbresult))
		{
			$context['links_cat'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'roworder' => $row['roworder'],
			);
		}
	mysql_free_result($dbresult);

}

function AddCat2()
{
	global $db_prefix, $txt, $func;
	
	isAllowedTo('links_manage_cat');

	//Clean the input
	$title = $func['htmlspecialchars'](trim($_POST['title']), ENT_QUOTES);
	$description =  $func['htmlspecialchars']($_POST['description'], ENT_QUOTES);
	$image =  $func['htmlspecialchars']($_POST['image'], ENT_QUOTES);
	$parent = (int) $_REQUEST['parent'];
    

	if ($title == '')
		fatal_error($txt['smflinks_nocattitle'],false);

	// Do the order
	$dbresult = db_query("
	SELECT 
		roworder 
	FROM {$db_prefix}links_cat 
	ORDER BY roworder DESC", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);

	$order = $row['roworder'];
	$order++;

	// Insert the category
	db_query("INSERT INTO {$db_prefix}links_cat
			(title, description,roworder,image,ID_PARENT)
		VALUES ('$title', '$description',$order,'$image',$parent)", __FILE__, __LINE__);
	mysql_free_result($dbresult);


	redirectexit('action=links');


}
function EditCat()
{
	global $context, $mbname, $txt, $db_prefix;
	isAllowedTo('links_manage_cat');
	
	$cat = (int) $_REQUEST['cat'];

	if (empty($cat))
		fatal_error($txt['smflinks_nocatselected']);
        
        
	$dbresult = db_query("
		SELECT 
			ID_CAT 
		FROM {$db_prefix}links_cat 
		WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
        
        if (empty($row['ID_CAT']))
            fatal_error($txt['smflinks_nocatselected'],false);

	$context['sub_template']  = 'editcat';

	// Set the page title
	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_editcat'];

	$dbresult = db_query("
	SELECT 
		ID_CAT, title,roworder 
	FROM {$db_prefix}links_cat 
	ORDER BY roworder ASC", __FILE__, __LINE__);
	$context['links_cat'] = array();
	 while($row = mysql_fetch_assoc($dbresult))
		{
			$context['links_cat'][] = array(
			'ID_CAT' => $row['ID_CAT'],
			'title' => $row['title'],
			'roworder' => $row['roworder'],
			);
		}
	mysql_free_result($dbresult);
	
	
	$dbresult = db_query("
	SELECT ID_CAT, title, description, image, ID_PARENT 
	FROM {$db_prefix}links_cat 
	WHERE ID_CAT = $cat", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	
	$context['links_editcat'] = $row;
	mysql_free_result($dbresult);
	
}

function EditCat2()
{
	global $db_prefix, $txt, $func;
	
	isAllowedTo('links_manage_cat');
    
    checkSession('post');

	// Clean the input
	$title =  $func['htmlspecialchars'](trim($_POST['title']), ENT_QUOTES);
	$description =  $func['htmlspecialchars']($_POST['description'], ENT_QUOTES);
	$image =  $func['htmlspecialchars']($_POST['image'], ENT_QUOTES);
	$catid = (int) $_REQUEST['catid'];
	$parent = (int) $_REQUEST['parent'];
    
	$dbresult = db_query("
		SELECT 
			ID_CAT 
		FROM {$db_prefix}links_cat 
		WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
        
        if (empty($row['ID_CAT']))
            fatal_error($txt['smflinks_nocatselected'],false);

	if ($title == '')
		fatal_error($txt['smflinks_nocattitle'],false);

	// Update the category
	db_query("UPDATE {$db_prefix}links_cat
		SET title = '$title', ID_PARENT = $parent, description = '$description', image = '$image' WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);



	redirectexit('action=links');

}

function DeleteCat()
{
	global $context, $mbname, $txt, $db_prefix;
	isAllowedTo('links_manage_cat');

	$context['sub_template']  = 'deletecat';
	
	
	$catid = (int) @$_REQUEST['cat'];
    
	$dbresult = db_query("
		SELECT 
			ID_CAT 
		FROM {$db_prefix}links_cat 
		WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
        
        if (empty($row['ID_CAT']))
            fatal_error($txt['smflinks_nocatselected'],false);

	if (empty($catid))
		fatal_error($txt['smflinks_nocatselected']);
		
	$context['links_catid'] = $catid;

	// Set the page title
	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_deltcat'];

}

function DeleteCat2()
{
	global $db_prefix, $txt;
	isAllowedTo('links_manage_cat');
	$catid = (int) $_REQUEST['catid'];
    
    checkSession('post');
    
	$dbresult = db_query("
		SELECT 
			ID_CAT 
		FROM {$db_prefix}links_cat 
		WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
        
        if (empty($row['ID_CAT']))
            fatal_error($txt['smflinks_nocatselected'],false);

	// Delete All links
	db_query("DELETE FROM {$db_prefix}links WHERE ID_CAT = $catid", __FILE__, __LINE__);
	// Finally delete the category
	db_query("DELETE FROM {$db_prefix}links_cat WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);

	redirectexit('action=links');
}


function AddLink()
{
	global $context, $mbname, $txt, $db_prefix, $sourcedir, $modSettings;
	
	isAllowedTo('add_links');

	$context['sub_template']  = 'addlink';

	// Set the page title
	$context['page_title'] = $mbname  . $txt['smflinks_title'] . ' - ' . $txt['smflinks_addlink'];

	// Check the category level permission
	if (isset($_REQUEST['cat']))
		GetCatPermission($_REQUEST['cat'],'addlink');
		
	if (isset($_REQUEST['cat']))
		$catid = (int) @$_REQUEST['cat'];
	else 
		$catid = 0;
		
	$context['links_catid'] = $catid;
	
	$dbresult = db_query("
	SELECT 
		ID_CAT, title, roworder 
	FROM {$db_prefix}links_cat ORDER BY roworder ASC", __FILE__, __LINE__);
	
	// Get category count
	$cat_count = db_affected_rows();
	if ($cat_count == 0)
		fatal_error($txt['smflinks_nofirstcat'], false);
		
	$context['links_cats'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
		$context['links_cats'][] = $row;
	mysql_free_result($dbresult);
		
	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
		
	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');	
	$context['post_box_name'] = 'description';
	$context['post_form'] = 'links';
	
}

function AddLink2()
{
	global $ID_MEMBER, $db_prefix, $txt, $func;


	isAllowedTo('add_links');
    
    checkSession('post');

	// Clean the input
	$title =  $func['htmlspecialchars'](trim($_POST['title']), ENT_QUOTES);
	$description = $func['htmlspecialchars']($_POST['description'], ENT_QUOTES);
	$url = addslashes(trim($_POST['url']));
	$catid = (int) $_REQUEST['catid'];
    
	$dbresult = db_query("
		SELECT 
			ID_CAT 
		FROM {$db_prefix}links_cat 
		WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
        
        if (empty($row['ID_CAT']))
            fatal_error($txt['smflinks_nocatselected'],false);

	GetCatPermission($catid,'addlink');

	if ($title == '')
		fatal_error($txt['smflinks_nolinktitle'],false);

	if ($url == '')
		fatal_error($txt['smflinks_nolinkurl'],false);

	// Check if the url already exists?
	$dbresult = db_query("
	SELECT 
		l.url,l.ID_CAT, l.title linkname, c.title cname 
	FROM {$db_prefix}links AS l, {$db_prefix}links_cat AS c 
	WHERE l.url = '$url' AND l.ID_CAT = c.ID_CAT", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	// The link already exists
	if (db_affected_rows() != 0)
	{
		$msg = $txt['smflinks_linkexists'];

		$msg = str_replace('%c', $row['cname'], $msg);
		$msg = str_replace('%l', $row['linkname'], $msg);

		fatal_error($msg,false);

	}
	mysql_free_result($dbresult);

	$alexa = 0;
	$pagerank = 0;
	//Insert the links
	$t = time();
	
	$approved = allowedTo('links_auto_approve') ? 1 : 0;
	

	db_query("INSERT INTO {$db_prefix}links
			(ID_CAT, url, title, description,ID_MEMBER,date,approved,alexa,pagerank)
		VALUES ($catid,'$url','$title', '$description',$ID_MEMBER,$t,$approved,$alexa,$pagerank)", __FILE__, __LINE__);

	// Redirect back to category
	if ($approved)
		redirectexit('action=links;cat=' . $catid);
	else 
		fatal_error($txt['smflinks_linkneedsapproval'],false);


}

function EditLink()
{
	global $context, $mbname, $txt, $db_prefix, $modSettings, $sourcedir, $ID_MEMBER;
	
	is_not_guest();

	$context['sub_template']  = 'editlink';

	//Set the page title
	$context['page_title'] = $mbname  . $txt['smflinks_title'] . ' - ' . $txt['smflinks_editlink'];

	// Lookup the link and see if they can edit it.
	$id = (int) @$_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected']);
		
	$context['link_id'] = $id;

	$dbresult = db_query("
	SELECT 
		ID_LINK, title, ID_CAT, description, url, ID_MEMBER   
	FROM {$db_prefix}links 
	WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	$context['links_link'] = $row;
	
	if (!allowedTo('edit_links_any') && (!allowedTo('edit_links_own') || $row['ID_MEMBER'] != $ID_MEMBER))
	{
		fatal_error($txt['smflinks_perm_link_no_edit']);
	}
	
	
	
	GetCatPermission($row['ID_CAT'],'editlink');
	
	
	$dbresult = db_query("
	SELECT 
		ID_CAT, title, roworder 
	FROM {$db_prefix}links_cat ORDER BY roworder ASC", __FILE__, __LINE__);
	
	// Get category count
	$cat_count = db_affected_rows();
	if ($cat_count == 0)
		fatal_error($txt['smflinks_nofirstcat'], false);
		
	$context['links_cats'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
		$context['links_cats'][] = $row;
	mysql_free_result($dbresult);
	
	
	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
		
	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');	
	$context['post_box_name'] = 'description';
	$context['post_form'] = 'links';


}

function EditLink2()
{
	global $db_prefix, $txt, $ID_MEMBER, $func;
	
	is_not_guest();
	
	$id = (int) $_REQUEST['id'];
    
    checkSession('post');
	
	$dbresult = db_query("
	SELECT 
		ID_MEMBER   
	FROM {$db_prefix}links 
	WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	
	if (!allowedTo('edit_links_any') && (!allowedTo('edit_links_own') || $row['ID_MEMBER'] != $ID_MEMBER))
	{
		fatal_error($txt['smflinks_perm_link_no_edit']);
	}
	
	
	// Clean the input
	$title = $func['htmlspecialchars'](trim($_POST['title']), ENT_QUOTES);
	$description = $func['htmlspecialchars']($_POST['description'], ENT_QUOTES);
	$url = addslashes(trim($_POST['url']));
	$catid = (int) $_REQUEST['catid'];
    
 	$dbresult = db_query("
		SELECT 
			ID_CAT 
		FROM {$db_prefix}links_cat 
		WHERE ID_CAT = $catid LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
        
        if (empty($row['ID_CAT']))
            fatal_error($txt['smflinks_nocatselected'],false);

	GetCatPermission($catid,'editlink');
	

	if ($title == '')
		fatal_error($txt['smflinks_nolinktitle'],false);

	if ($url == '')
		fatal_error($txt['smflinks_nolinkurl'],false);

	$alexa = 0;
	$pagerank = 0;

	// Update the link
	db_query("UPDATE {$db_prefix}links
		SET title = '$title',url= '$url', description = '$description', alexa = $alexa, pagerank = $pagerank, ID_CAT = $catid WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);


	// Redirect back to category
	redirectexit('action=links');

}

function DeleteLink()
{
	global $context, $mbname, $txt, $db_prefix, $ID_MEMBER;
	
	$id = (int) @$_REQUEST['id'];
	
	is_not_guest();

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected']);
		
	$dbresult = db_query("
	SELECT 
		ID_MEMBER, ID_LINK   
	FROM {$db_prefix}links 
	WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
    
   	if (empty($row['ID_LINK']))
		fatal_error($txt['smflinks_nolinkselected']);
	
	if (!allowedTo('delete_links_any') && (!allowedTo('delete_links_own') || $row['ID_MEMBER'] != $ID_MEMBER))
	{
		fatal_error($txt['smflinks_perm_link_no_delete']);
	}
		
	
	$context['links_id'] = $id;

	$context['sub_template']  = 'deletelink';

	// Set the page title
	$context['page_title'] = $mbname  . $txt['smflinks_title'] . ' - ' . $txt['smflinks_dellink'];
	$id = (int) $_REQUEST['id'];
	// Check if they are allowed to delete the link
	$dbresult = db_query("
	SELECT 
		ID_LINK,ID_CAT 
	FROM {$db_prefix}links 
	WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	GetCatPermission($row['ID_CAT'],'dellink');
}

function DeleteLink2()
{
	global $db_prefix, $txt, $ID_MEMBER;

	is_not_guest();
    checkSession('post');

	$id = (int) @$_REQUEST['id'];
	
	$dbresult = db_query("
	SELECT 
		ID_MEMBER, ID_LINK   
	FROM {$db_prefix}links 
	WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
    
   	if (empty($row['ID_LINK']))
		fatal_error($txt['smflinks_nolinkselected']);
        
	mysql_free_result($dbresult);
	
	if (!allowedTo('delete_links_any') && (!allowedTo('delete_links_own') || $row['ID_MEMBER'] != $ID_MEMBER))
	{
		fatal_error($txt['smflinks_perm_link_no_delete']);
	}

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected']);

	db_query("DELETE FROM {$db_prefix}links WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);

	redirectexit('action=links');
}

function VisitLink()
{
	global $db_prefix, $txt, $modSettings;
	
	// Check if the current user can view the links list
	isAllowedTo('view_smflinks');
	
	
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();
	

	$id = (int) @$_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected']);
	$dbresult = db_query("
	SELECT 
		url, ID_LINK  
	FROM {$db_prefix}links 
	WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
    
   	if (empty($row['ID_LINK']))
		fatal_error($txt['smflinks_nolinkselected']);
        
	// Update site lists
	db_query("UPDATE {$db_prefix}links
		SET hits = hits + 1 WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);

	// Redirect to the site

	mysql_free_result($dbresult);
	header("Location: " . $row['url']);
	
	obExit(false);
	die('');
}

function CatUp()
{
	global $db_prefix, $txt;
	// Check if they are allowed to manage cats
	isAllowedTo('links_manage_cat');
    checkSession('get');

	// Get the cat id
	$cat = (int) $_REQUEST['cat'];
    
		$dbresult = db_query("
		SELECT 
			title, ID_CAT 
		FROM {$db_prefix}links_cat 
		WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
        
        if (empty($row['ID_CAT']))
            fatal_error($txt['smflinks_nocatselected'],false);
    
	ReOrderCats($cat);
	// Check if there is a category above it
	// First get our row order
	$dbresult1 = db_query("
	SELECT 
		roworder, ID_PARENT
	FROM {$db_prefix}links_cat 
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
	FROM {$db_prefix}links_cat 
	WHERE ID_PARENT = $ID_PARENT AND roworder = $o", __FILE__, __LINE__);
	if (db_affected_rows()== 0)
		fatal_error($txt['smflinks_nocatabove'],false);
	$row2 = mysql_fetch_assoc($dbresult);

	// Swap the order Id's
	db_query("UPDATE {$db_prefix}links_cat
		SET roworder = $oldrow WHERE ID_CAT = " .$row2['ID_CAT'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}links_cat
		SET roworder = $o WHERE ID_CAT = $cat", __FILE__, __LINE__);


	mysql_free_result($dbresult);

	// Redirect to index to view cats
	if ($ID_PARENT == 0)
		redirectexit('action=links');
	else 
		redirectexit('action=links;cat=' . $ID_PARENT);
}

function CatDown()
{
	global $db_prefix, $txt;
    
    checkSession('get');

	// Check if they are allowed to manage cats
	isAllowedTo('links_manage_cat');

	// Get the cat id
	$cat = (int) @$_REQUEST['cat'];
    
	$dbresult = db_query("
		SELECT 
			title, ID_CAT 
		FROM {$db_prefix}links_cat 
		WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
        
        if (empty($row['ID_CAT']))
            fatal_error($txt['smflinks_nocatselected'],false);
    
    
	ReOrderCats($cat);
	// Check if there is a category below it
	// First get our row order
	$dbresult1 = db_query("
	SELECT 
		roworder, ID_PARENT 
	FROM {$db_prefix}links_cat 
	WHERE ID_CAT = $cat", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o++;

	mysql_free_result($dbresult1);
	$dbresult = db_query("
	SELECT 
		ID_CAT, roworder 
	FROM {$db_prefix}links_cat 
	WHERE ID_PARENT = $ID_PARENT AND roworder = $o", __FILE__, __LINE__);
	if (db_affected_rows()== 0)
		fatal_error($txt['smflinks_nocatbelow'],false);
	$row2 = mysql_fetch_assoc($dbresult);

	// Swap the order Id's
	db_query("UPDATE {$db_prefix}links_cat
		SET roworder = $oldrow WHERE ID_CAT = " .$row2['ID_CAT'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}links_cat
		SET roworder = $o WHERE ID_CAT = $cat", __FILE__, __LINE__);


	mysql_free_result($dbresult);


	// Redirect to index to view cats
	if ($ID_PARENT == 0)
		redirectexit('action=links');
	else 
		redirectexit('action=links;cat=' . $ID_PARENT);
}

function ApproveList()
{
	global $context, $mbname, $txt, $db_prefix;
	isAllowedTo('approve_links');

	adminIndex('links_settings');

	$context['sub_template']  = 'approvelinks';
	
	DoLinksAdminTabs();

	// Set the page title
	$context['page_title'] = $mbname . $txt['smflinks_title'] .' - ' . $txt['smflinks_approvelinks'] ;


	$dbresult = db_query("
	SELECT 
		COUNT(*) AS total 
	FROM {$db_prefix}links 
	WHERE approved = 0", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$total = $row['total'];
	$context['approvetotal'] = $total;
	mysql_free_result($dbresult);
	
	$context['start'] = (int) $_REQUEST['start'];
	
	 $dbresult = db_query("
 SELECT 
 	l.ID_LINK, l.approved,l.description,l.pagerank, l.alexa, l.url, l.title,l.date, m.realName, l.ID_MEMBER, l.description,l.hits, 
 	l.ID_CAT, c.title catname 
 	FROM ({$db_prefix}links AS l, {$db_prefix}links_cat AS c) 
 	LEFT JOIN {$db_prefix}members AS m ON (l.ID_MEMBER = m.ID_MEMBER) 
 	WHERE l.ID_CAT = c.ID_CAT AND l.approved = 0 ORDER BY l.ID_LINK DESC LIMIT $context[start],20", __FILE__, __LINE__);
	$context['linksapprovallist'] = array();
 	while($row = mysql_fetch_assoc($dbresult))
 	{
 		$context['linksapprovallist'][] = $row;
 	}
	mysql_free_result($dbresult);

}

function Approve()
{
	global $db_prefix, $txt;

	isAllowedTo('approve_links');
    
    checkSession('get');
	// Get link id
	$id = (int) $_REQUEST['id'];
    
	$dbresult = db_query("
	SELECT 
	   ID_LINK  
	FROM {$db_prefix}links 
	WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
    
   	if (empty($row['ID_LINK']))
		fatal_error($txt['smflinks_nolinkselected']);
    

	db_query("UPDATE {$db_prefix}links
		SET approved = 1 WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);


	redirectexit('action=links;sa=alist');
}

function NoApprove()
{
	global $db_prefix, $txt;

	isAllowedTo('approve_links');
    checkSession('get');
	// Get link id
	$id = (int) $_REQUEST['id'];
    
	$dbresult = db_query("
	SELECT 
	   ID_LINK  
	FROM {$db_prefix}links 
	WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
    
   	if (empty($row['ID_LINK']))
		fatal_error($txt['smflinks_nolinkselected']);
    

	db_query("UPDATE {$db_prefix}links
		SET approved = 0 WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);


	redirectexit('action=links');
}

function RateLink()
{
	global $db_prefix, $ID_MEMBER, $txt;

	isAllowedTo('rate_links');

	// Guests can't rate links? Cause how can we keep track???
	is_not_guest();

	// Get the link ID
	$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected']);
        
	$dbresult = db_query("
	SELECT 
	   ID_LINK  
	FROM {$db_prefix}links 
	WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
    
   	if (empty($row['ID_LINK']))
		fatal_error($txt['smflinks_nolinkselected']);
    

	// See if the user already rated the link.
	$dbresult = db_query("
	SELECT 
		ID_LINK, ID_MEMBER 
	FROM {$db_prefix}links_rating 
	WHERE ID_MEMBER = $ID_MEMBER AND ID_LINK = $id", __FILE__, __LINE__);
	if (db_affected_rows()!= 0)
		fatal_error($txt['smflinks_alreadyrated'],false);
	mysql_free_result($dbresult);

	//Get the value of rating
	@$value = (int) $_REQUEST['value'];


	//Check value
	if ($value == 0)
	{
		//Lower Ranking

			//Insert rating
		db_query("INSERT INTO {$db_prefix}links_rating
				(ID_LINK, ID_MEMBER,value)
			VALUES ($id, $ID_MEMBER,0)", __FILE__, __LINE__);
	
		// Update main link rating
		db_query("UPDATE {$db_prefix}links
			SET rating = rating - 1 WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);

	}
	else
	{
		//Higher Ranking
		//Insert rating
		db_query("INSERT INTO {$db_prefix}links_rating
				(ID_LINK, ID_MEMBER,value)
			VALUES ($id, $ID_MEMBER,1)", __FILE__, __LINE__);

		//Update main link rating
		db_query("UPDATE {$db_prefix}links
			SET rating = rating + 1 WHERE ID_LINK = $id LIMIT 1", __FILE__, __LINE__);

	}


	redirectexit('action=links');
}

function LinksAdmin()
{
	global $context, $mbname, $txt;
	
	isAllowedTo('links_manage_cat');

	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_settings'];

	DoLinksAdminTabs();
	
	adminIndex('links_settings');

	$context['sub_template']  = 'settings';

}
function LinksAdmin2()
{
	isAllowedTo('links_manage_cat');
    
    checkSession('post');

	$smflinks_setlinksperpage = (int) $_REQUEST['smflinks_setlinksperpage'];

	$smflinks_setshowtoprate = isset($_REQUEST['smflinks_setshowtoprate'])? 1 : 0;
	$smflinks_setshowmostvisited = isset($_REQUEST['smflinks_setshowmostvisited'])? 1 : 0;
	$smflinks_setshowstats = isset($_REQUEST['smflinks_setshowstats'])? 1 : 0;
	$smflinks_setallowbbc = isset($_REQUEST['smflinks_setallowbbc'])? 1 : 0;
	$smflinks_setgetpr = isset($_REQUEST['smflinks_setgetpr'])? 1 : 0;
	$smflinks_setgetalexa = isset($_REQUEST['smflinks_setgetalexa'])? 1 : 0;
	$smflinks_set_count_child = isset($_REQUEST['smflinks_set_count_child']) ? 1 : 0;

	// Link Display Settings
	$smflinks_disp_description = isset($_REQUEST['smflinks_disp_description'])? 1 : 0;
	$smflinks_disp_hits = isset($_REQUEST['smflinks_disp_hits'])? 1 : 0;
	$smflinks_disp_rating = isset($_REQUEST['smflinks_disp_rating'])? 1 : 0;
	$smflinks_disp_membername = isset($_REQUEST['smflinks_disp_membername'])? 1 : 0;
	$smflinks_disp_date = isset($_REQUEST['smflinks_disp_date'])? 1 : 0;
	$smflinks_disp_alexa = isset($_REQUEST['smflinks_disp_alexa'])? 1 : 0;
	$smflinks_disp_pagerank = isset($_REQUEST['smflinks_disp_pagerank'])? 1 : 0;

    // Save the setting information
	updateSettings(
	array('smflinks_setlinksperpage' => $smflinks_setlinksperpage,
	'smflinks_setshowtoprate' => $smflinks_setshowtoprate,
	'smflinks_setshowmostvisited' => $smflinks_setshowmostvisited,
	'smflinks_setshowstats' => $smflinks_setshowstats,
	'smflinks_set_count_child' => $smflinks_set_count_child,
	'smflinks_setallowbbc' => $smflinks_setallowbbc,
	'smflinks_setgetpr' => $smflinks_setgetpr,
	'smflinks_setgetalexa' => $smflinks_setgetalexa,
	'smflinks_disp_description' => $smflinks_disp_description,
	'smflinks_disp_hits' => $smflinks_disp_hits,
	'smflinks_disp_rating' => $smflinks_disp_rating,
	'smflinks_disp_membername' => $smflinks_disp_membername,
	'smflinks_disp_date' => $smflinks_disp_date,
	'smflinks_disp_alexa' => $smflinks_disp_alexa,
	'smflinks_disp_pagerank' => $smflinks_disp_pagerank,
	));


	redirectexit('action=links;sa=admin');
}

function LinksAdminCats()
{
	global $context, $mbname, $txt, $db_prefix;

	isAllowedTo('links_manage_cat');

	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_managecats'];

	DoLinksAdminTabs();
	
	adminIndex('links_settings');

	$context['sub_template']  = 'manage_cats';
	
	// List all the catagories
	$dbresult = db_query("
		SELECT 
			ID_CAT, title, roworder, description 
		FROM {$db_prefix}links_cat ORDER BY ID_CAT DESC, roworder ASC", __FILE__, __LINE__);
	$context['links_cats'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
		$context['links_cats'][] = $row;
	mysql_free_result($dbresult);
	
	
}

function LinksAdminPerm()
{
	global $context, $mbname, $txt, $db_prefix;

	isAllowedTo('links_manage_cat');

	adminIndex('links_settings');
	
	DoLinksAdminTabs();

	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_catpermlist'];

	$context['sub_template']  = 'catpermlist';
	
	//Show the member groups
	$dbresult = db_query("
		  	SELECT 
		  		c.ID_CAT, c.ID, c.view, c.addlink, c.editlink, c.dellink,   c.ID_GROUP, m.groupName,a.title catname 
		  	FROM ({$db_prefix}links_catperm as c, {$db_prefix}membergroups AS m,{$db_prefix}links_cat as a) 
		  	WHERE  c.ID_GROUP = m.ID_GROUP AND a.ID_CAT = c.ID_CAT", __FILE__, __LINE__);
	$context['links_membergroups'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['links_membergroups'][] = $row;
	}
	mysql_free_result($dbresult);
	
	// Regular Member Groups
	$dbresult = db_query("
		  	SELECT 
		  		c.ID_CAT, c.ID, c.view, c.addlink, c.editlink, c.dellink,   c.ID_GROUP,a.title catname 
		  	FROM {$db_prefix}links_catperm as c,{$db_prefix}links_cat as a 
		  	WHERE  c.ID_GROUP = 0 AND a.ID_CAT = c.ID_CAT LIMIT 1", __FILE__, __LINE__);
	$context['links_reggroups'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['links_reggroups'][] = $row;
	}
	mysql_free_result($dbresult);
	
	// Guests
	$dbresult = db_query("
		  	SELECT 
		  		c.ID_CAT, c.ID, c.view, c.addlink, c.editlink, c.dellink, c.ID_GROUP,a.title catname 
		  	FROM {$db_prefix}links_catperm as c,{$db_prefix}links_cat as a 
		  	WHERE  c.ID_GROUP = -1 AND a.ID_CAT = c.ID_CAT LIMIT 1", __FILE__, __LINE__);
	$context['links_guestsgroups'] = array();
	while ($row = mysql_fetch_assoc($dbresult))
	{
		$context['links_guestsgroups'][] = $row;
	}
	mysql_free_result($dbresult);
	
}

function GetCatPermission($cat,$perm)
{
	global $ID_MEMBER,$db_prefix, $txt, $user_info;
	
	$cat = (int) $cat;
	if (!$user_info['is_guest'])
	{
		$dbresult = db_query("
		SELECT 
			m.ID_MEMBER, c.view, c.addlink, c.editlink, c.dellink,c.ratelink, c.report 
		FROM {$db_prefix}links_catperm as c, {$db_prefix}members as m 
		WHERE m.ID_MEMBER = $ID_MEMBER AND c.ID_GROUP = m.ID_GROUP AND c.ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
	}
	else
		$dbresult = db_query("
		SELECT 
			c.view, c.addlink, c.editlink, c.dellink,c.ratelink, c.report 
		FROM {$db_prefix}links_catperm as c 
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
			fatal_error($txt['smflinks_perm_no_view'],false);
		else if ($perm == 'addlink' && $row['addlink'] == 0)
			fatal_error($txt['smflinks_perm_no_add'],false);
		else if ($perm == 'editlink' && $row['editlink'] == 0)
			fatal_error($txt['smflinks_perm_no_edit'],false);
		else if ($perm == 'dellink' && $row['dellink'] == 0)
			fatal_error($txt['smflinks_perm_no_delete'],false);
		else if ($perm == 'ratelink' && $row['ratelink'] == 0)
			fatal_error($txt['smflinks_perm_no_ratelink'],false);
		else if ($perm == 'report' && $row['report'] == 0)
			fatal_error($txt['smflinks_perm_no_report'],false);
	}


}
function CatPermDelete()
{
	global $db_prefix;
	isAllowedTo('links_manage_cat');

	$id = (int) $_REQUEST['id'];

	// Delete the Permission
	db_query("DELETE FROM {$db_prefix}links_catperm WHERE ID = " . $id . ' LIMIT 1', __FILE__, __LINE__);
	// Redirect to the ratings
	redirectexit('action=links;sa=adminperm');

}

function CatPerm()
{
	global $mbname, $txt, $db_prefix, $context;
	isAllowedTo('links_manage_cat');


	$cat = (int) $_REQUEST['cat'];
	if (empty($cat))
		fatal_error($txt['smflinks_nocatselected']);

	$dbresult1 = db_query("
	SELECT 
		ID_CAT, title 
	FROM {$db_prefix}links_cat 
	WHERE ID_CAT = $cat LIMIT 1", __FILE__, __LINE__);
	$row1 = mysql_fetch_assoc($dbresult1);
	$context['links_cat_name'] = $row1['title'];
	mysql_free_result($dbresult1);

	loadLanguage('Admin');

	$context['links_cat'] = $cat;

	// Load the template
	$context['sub_template']  = 'catperm';
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['smflinks_title'] . ' - ' . $txt['smflinks_text_catperm'] . ' -' . $context['links_cat_name'];


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
	
	//Show the member groups
	 $dbresult = db_query("
		  	SELECT 
		  		c.ID_CAT, c.ID, c.view, c.addlink, c.editlink, c.dellink,  c.ID_GROUP, m.groupName,a.title catname 
		  	FROM ({$db_prefix}links_catperm as c, {$db_prefix}membergroups AS m,{$db_prefix}links_cat as a) 
		  	WHERE  c.ID_CAT = " . $context['links_cat'] . " AND c.ID_GROUP = m.ID_GROUP AND a.ID_CAT = c.ID_CAT", __FILE__, __LINE__);
	$context['linksmembergroups'] = array();
	while($row = mysql_fetch_assoc($dbresult))
	{
		$context['linksmembergroups'][] = $row;
	}
	mysql_free_result($dbresult);
	
	// Regular membergroups
	$dbresult = db_query("
		  	SELECT 
		  		c.ID_CAT, c.ID, c.view, c.addlink, c.editlink, c.dellink, c.ID_GROUP,a.title catname 
		  	FROM ({$db_prefix}links_catperm as c,{$db_prefix}links_cat as a) 
		  	WHERE c.ID_CAT = " . $context['links_cat'] . " AND c.ID_GROUP = 0 AND a.ID_CAT = c.ID_CAT LIMIT 1", __FILE__, __LINE__);
	$context['linksreggroups'] = array();
	while($row = mysql_fetch_assoc($dbresult))
	{
		$context['linksreggroups'][] = $row;
	}
	mysql_free_result($dbresult);
	
			// Show Guests
	$dbresult = db_query("
		  	SELECT 
		  		c.ID_CAT, c.ID, c.view, c.addlink, c.editlink, c.dellink,  c.ID_GROUP,a.title catname 
		  	FROM {$db_prefix}links_catperm as c,{$db_prefix}links_cat as a 
		  	WHERE c.ID_CAT = " . $context['links_cat'] . " AND c.ID_GROUP = -1 AND a.ID_CAT = c.ID_CAT LIMIT 1", __FILE__, __LINE__);
	$context['linksguestgroups'] = array();
	while($row = mysql_fetch_assoc($dbresult))
	{
		$context['linksguestgroups'][] = $row;
	}
	mysql_free_result($dbresult);
}

function CatPerm2()
{
	global  $db_prefix, $txt;
	isAllowedTo('links_manage_cat');
    
    checkSession('post');

	$groupname = (int) $_REQUEST['groupname'];
	$cat = (int) $_REQUEST['cat'];

	// Check if permission exits
	$dbresult = db_query("
	SELECT 
		ID_GROUP,ID_CAT 
	FROM {$db_prefix}links_catperm 
	WHERE ID_GROUP = $groupname AND ID_CAT = $cat", __FILE__, __LINE__);
	if (db_affected_rows()!= 0)
	{
		mysql_free_result($dbresult);
		fatal_error($txt['smflinks_permerr_permexist'],false);
	}
	mysql_free_result($dbresult);

	//Permissions
	$view = isset($_REQUEST['view']) ? 1 : 0;
	$add = isset($_REQUEST['add']) ? 1 : 0;
	$edit = isset($_REQUEST['edit']) ? 1 : 0;
	$delete = isset($_REQUEST['delete']) ? 1 : 0;


	// Insert into database
	db_query("INSERT INTO {$db_prefix}links_catperm
			(ID_GROUP,ID_CAT,view,addlink,editlink,dellink)
		VALUES ($groupname,$cat,$view,$add,$edit,$delete)", __FILE__, __LINE__);

	redirectexit('action=links;sa=catperm;cat=' . $cat);
}

function ReOrderCats($cat)
{
	global $db_prefix;

	$dbresult1 = db_query("
	SELECT 
		roworder,ID_PARENT 
	FROM {$db_prefix}links_cat 
	WHERE ID_CAT = $cat", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	mysql_free_result($dbresult1);

	$dbresult = db_query("
	SELECT 
		ID_CAT, roworder 
	FROM {$db_prefix}links_cat 
	WHERE ID_PARENT = $ID_PARENT ORDER BY roworder ASC", __FILE__, __LINE__);
	if (db_affected_rows() != 0)
	{
		$count = 1;
		while($row2 = mysql_fetch_assoc($dbresult))
		{
			db_query("UPDATE {$db_prefix}links_cat
			SET roworder = $count WHERE ID_CAT = " . $row2['ID_CAT'], __FILE__, __LINE__);
			$count++;
		}
	}
	mysql_free_result($dbresult);
}

function DoLinksAdminTabs($overrideSelected = '')
{
	global $context, $txt, $scripturl, $db_prefix;
	
	$tmpSA = '';
	if (!empty($overrideSelected))
		$_REQUEST['sa'] = $overrideSelected;

	
	// Get the number links waiting for approval
	$dbresult = db_query("
	SELECT 
		COUNT(*) AS total
	FROM {$db_prefix}links WHERE approved = 0", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$alinks_total = $row['total'];
	mysql_free_result($dbresult);
		
	// Create the tabs for the template.
	$context['admin_tabs'] = array(
		'title' => $txt['smflinks_admin'] ,
		'description' => '',
		'tabs' => array(),
	);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['smflinks_linkssettings'],
			'description' => '',
			'href' => $scripturl . '?action=links;sa=admin',
			'is_selected' => $_REQUEST['sa'] == 'admin',
		);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['smflinks_managecats'],
			'description' => '',
			'href' => $scripturl . '?action=links;sa=admincat',
			'is_selected' => $_REQUEST['sa'] == 'admincat',
		);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['smflinks_approvelinks'] . ' (' . $alinks_total . ')',
			'description' => '',
			'href' => $scripturl . '?action=links;sa=alist',
			'is_selected' => $_REQUEST['sa'] == 'alist',
		);

	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['smflinks_catpermlist'],
			'description' => '',
			'href' => $scripturl . '?action=links;sa=adminperm',
			'is_selected' => $_REQUEST['sa'] == 'adminperm',
		);
		

	if (!empty($overrideSelected))
	{
		$_REQUEST['sa'] = $tmpSA;
		
	}
		
	$context['admin_tabs']['tabs'][count($context['admin_tabs']['tabs']) - 1]['is_last'] = true;
}

function GetLinkTotals($ID_CAT)
{
	global $modSettings, $db_prefix, $subcats_linktree, $scripturl;
	
	$total = 0;
	// First get the parents total links
	$dbresult2 = db_query("
	SELECT 
		COUNT(*) as totallinks FROM {$db_prefix}links 
	WHERE ID_CAT = $ID_CAT AND approved = 1", __FILE__, __LINE__);
	$row2 = mysql_fetch_assoc($dbresult2);
	$total += $row2['totallinks'];
	mysql_free_result($dbresult2);
	
	$subcats_linktree = '';
	
	// Get the child categories to this category
	if ($modSettings['smflinks_set_count_child'])
	{

		
		
		
		$dbresult3 = db_query("
		SELECT 
			ID_CAT,title FROM {$db_prefix}links_cat 
		WHERE ID_PARENT = $ID_CAT", __FILE__, __LINE__);
		while($row3 = mysql_fetch_assoc($dbresult3))
		{
			$subcats_linktree .= '<a href="' . $scripturl . '?action=links;cat=' . $row3['ID_CAT'] . '">' . $row3['title'] . '</a>&nbsp;&nbsp;';
			
			/*
			$dbresult2 = db_query("
			SELECT 
				COUNT(*) as totallinks 
			FROM {$db_prefix}links 
			WHERE ID_CAT = " . $row3['ID_CAT'] . " AND approved = 1", __FILE__, __LINE__);
			while($row2 = mysql_fetch_assoc($dbresult2))
			{
				$total += $row2['totallinks'];
			}
			mysql_free_result($dbresult2);
			
			*/
		}

		
		mysql_free_result($dbresult3);

		$dbresult3 = db_query("
		SELECT
			ID_CAT, ID_PARENT
		FROM {$db_prefix}links_cat
		WHERE ID_PARENT <> 0", __FILE__, __LINE__);
		
		$childArray = array();
		while($row3 = mysql_fetch_assoc($dbresult3))
		{
			$dbresult2 = db_query("
					SELECT 
						COUNT(*) as totallinks 
					FROM {$db_prefix}links 
					WHERE ID_CAT = " . $row3['ID_CAT'] . " AND approved = 1", __FILE__, __LINE__);
			$row2 = mysql_fetch_assoc($dbresult2);
				
			$row3['total']	= $row2['totallinks'];
			
			$childArray[] = $row3;
		}
	
		$total += Links_GetFileTotalsByParent($ID_CAT,$childArray);
	
	}

	
	return $total;
}


function Links_GetFileTotalsByParent($ID_PARENT,$data)
{
	$total = 0;
	foreach($data as $row)
	{
		if ($row['ID_PARENT'] == $ID_PARENT)
		{
			$total += $row['total'];
			$total += Links_GetFileTotalsByParent($row['ID_CAT'],$data);
		}
	}
	
	return $total;
}

function ShowSubCats($cat,$g_manage)
{
	global $txt, $db_prefix, $scripturl, $subcats_linktree, $context;
	
		// List all the catagories
		$dbresult = db_query("
		SELECT 
			ID_CAT, title, roworder, description, image 
		FROM {$db_prefix}links_cat 
		WHERE ID_PARENT = $cat ORDER BY roworder ASC", __FILE__, __LINE__);
		
		if (db_affected_rows() != 0)
		{
		
		echo '<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
			<tr>
				<td class="titlebg" colspan="2">' . $txt['smflinks_ctitle'] . '</td>
				<td class="titlebg">' . $txt['smflinks_description'] .'</td>
				<td class="titlebg">' . $txt['smflinks_totallinks'] . '</td>';
				if($g_manage)
					echo '<td class="titlebg">' . $txt['smflinks_options'] .'</td>';
		echo '
			</tr>';
			
		while($row = mysql_fetch_assoc($dbresult))
		{
			
			$totallinks = GetLinkTotals($row['ID_CAT']);
			
			echo '<tr>';
			
				if($row['image'] == '')
					echo '<td colspan="2" class="windowbg2"><a href="' . $scripturl . '?action=links;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></td><td class="windowbg2">' . parse_bbc($row['description']) . '</td>';
				else
				{
					echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;cat=' . $row['ID_CAT'] . '"><img src="' . $row['image'] . '" border="0" /></a></td>';
					echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></td><td class="windowbg2">' . parse_bbc($row['description']) . '</td>';
				}
				

			
			echo '<td class="windowbg2">' . $totallinks . '</td>';
			

			// Show Edit Delete and Order category
			if ($g_manage)
				echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;sa=catup;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtup'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=catdown;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtdown'] . '</a></span>&nbsp;<a href="' . $scripturl . '?action=links;sa=catperm;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txt_perm'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=editcat;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtedit'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=deletecat;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtdel'] . '</a></td>';
			
			
			echo '</tr>';
			
			if ($subcats_linktree != '')
			echo '<tr class="titlebg">
					<td colspan="',($g_manage ? '5' : '4'),'">&nbsp;<span class="smalltext">',($subcats_linktree != '' ? $txt['smflinks_sub_cats'] . $subcats_linktree : ''),'</span></td>
				</tr>';
		
	
			
		}
		
		echo '</table><br /><br />';
		mysql_free_result($dbresult);
		}
}

?>