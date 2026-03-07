<?php
/*
SMF Links
Version 5.0
by:vbgamer45
https://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function LinksMain()
{
	global $context;
	// Load the main links template
	if (!function_exists("set_tld_regex"))
		loadtemplate('Links2');
	else
	{
		loadtemplate('Links2.1');
		$context['show_bbc'] = 1;
	}
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
		'reordercat' => 'LinksReorderCats',
		'checklinks' => 'LinksCheckLinks',
		'checklinks2' => 'LinksCheckLinks2',
		'deletebadlinks' => 'LinksDeleteBadLinks',
		'disalloweddomains' => 'LinksDisallowedDomains',
		'disalloweddomains2' => 'LinksDisallowedDomains2',
		'disalloweddomainsdelete' => 'LinksDisallowedDomainsDelete',
		'notifybadlinks' => 'LinksNotifyBadLinks',
	);

	// Follow the sa or just go to main links index.
	if (!empty($_GET['sa']) && array_key_exists($_GET['sa'], $subActions))
		call_user_func($subActions[$_GET['sa']]);
	else
		ViewLinksMain();
}

function ViewLinksMain()
{
	global $context, $mbname, $txt, $smcFunc, $scripturl, $modSettings;

	// Check if the current user can view the links list
	isAllowedTo('view_smflinks');

	// To get Permissions text
	loadLanguage('Admin');

	// Load the main index links template
	$context['sub_template']  = 'mainview';

	// Setup Initial Link Tree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=links',
		'name' => $txt['smflinks_menu']
	);

	// Check if there was a category
	if (isset($_REQUEST['cat']))
	{
		if (!empty($_REQUEST['cat']))
			$cat = (int) $_REQUEST['cat'];
		else
			$cat = 0;

		if (empty($cat))
			fatal_error($txt['smflinks_nocatselected'], false);

		GetCatPermission($cat, 'view');
		// List all the categories
		$dbresult = $smcFunc['db_query']('', '
			SELECT title, ID_CAT
			FROM {db_prefix}links_cat
			WHERE ID_CAT = {int:this_cat}
			LIMIT 1',
			array(
				'this_cat' => $cat
			)
		);
		$row = $smcFunc['db_fetch_assoc']($dbresult);
        
		if (empty($row['ID_CAT']))
			fatal_error($txt['smflinks_nocatselected'], false);
        
		// Set the page title
		$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $row['title'];

		GetParentLink($cat);

		$smcFunc['db_free_result']($dbresult);
		
		$dbresult = $smcFunc['db_query']('', '
			SELECT ID_CAT, title, description 
			FROM {db_prefix}links_cat 
			WHERE ID_CAT = {int:this_cat}
			LIMIT 1',
			array(
				'this_cat' => $cat
			)
		);
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$context['linkscatrow'] = $row;
		$smcFunc['db_free_result']($dbresult);

		if (!empty($_REQUEST['start']))
			$context['start'] = (int) $_REQUEST['start'];	
		else
			$context['start'] = 0;
			
		if (!empty($_REQUEST['sort']))
		{
			switch($_REQUEST['sort'])
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
					$sort = 'm.real_name';
					break;	
				default:
					$sort = 'l.ID_LINK';
			}
		}
		else
			$sort = 'l.ID_LINK';
		
		if (!empty($_REQUEST['sorto']) && $_REQUEST['sorto'] == 'ASC')
			$sorto = 'ASC';
		else 
			$sorto = 'DESC';

		$dbresult = $smcFunc['db_query']('', '
			SELECT COUNT(*) AS total 
			FROM {db_prefix}links 
			WHERE ID_CAT = {int:this_cat}
				AND approved = {int:this_approved}',
			array(
				'this_cat' => $cat,
				'this_approved' => 1
			)
		);
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$context['links_total_pages'] = $row['total'];
		$smcFunc['db_free_result']($dbresult);
		
		// Show the links in that category
		$dbresult = $smcFunc['db_query']('', '
			SELECT l.ID_LINK, l.title, l.date,
				l.rating, m.real_name,
				l.ID_MEMBER, l.description, l.hits, l.url
			FROM {db_prefix}links AS l
			LEFT JOIN {db_prefix}members AS m ON (l.ID_MEMBER = m.ID_MEMBER)
			WHERE l.ID_CAT = {int:this_cat}
				AND l.approved = {int:this_approved}
			ORDER BY {raw:this_sort} {raw:this_sorto} LIMIT {int:this_start},{int:this_items_per_page}',
			array(
				'this_cat' => $cat,
				'this_approved' => 1,
				'this_sort' => $sort,
				'this_sorto' => $sorto,
				'this_start' => $context['start'],
				'this_items_per_page' => $modSettings['smflinks_setlinksperpage']
			)
		);
		$context['totallinks'] = $smcFunc['db_affected_rows']();
		
		$context['links_cat_list'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['links_cat_list'][] = $row;
		}
		$smcFunc['db_free_result']($dbresult);	
	}
	else
	{
		$context['page_title'] = $mbname . $txt['smflinks_title'];
		
		$dbresult = $smcFunc['db_query']('', '
			SELECT ID_CAT, title, image, roworder, description 
			FROM {db_prefix}links_cat 
			WHERE ID_PARENT = 0
			ORDER BY roworder ASC'
		);
	
		// Get category count
		$context['cat_count'] = $smcFunc['db_affected_rows']();	
		
		$context['links_cats'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['links_cats'][] = $row;
		}
		$smcFunc['db_free_result']($dbresult);
		
		// Show top five rated
		$dbresult = $smcFunc['db_query']('', '
			SELECT l.ID_LINK, l.rating, l.title,l.date,
				m.real_name, l.ID_MEMBER, l.description,l.hits 
			FROM {db_prefix}links AS l 
			LEFT JOIN {db_prefix}members AS m ON (l.ID_MEMBER = m.ID_MEMBER)  
			WHERE l.approved = 1
			ORDER BY l.rating DESC
			LIMIT 5'
		);
		$context['links_toprated'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['links_toprated'][] = $row;	
		}
		$smcFunc['db_free_result']($dbresult);
		
		// Show top five hits
		$dbresult = $smcFunc['db_query']('', '
			SELECT l.ID_LINK, l.rating, l.title,l.date,
				m.real_name, l.ID_MEMBER, l.description,l.hits 
			FROM {db_prefix}links AS l 
			LEFT JOIN {db_prefix}members AS m ON (l.ID_MEMBER = m.ID_MEMBER) 
			WHERE l.approved = 1
			ORDER BY l.hits DESC
			LIMIT 5'
		);
		$context['links_tophits'] = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$context['links_tophits'][] = $row;	
		}
		$smcFunc['db_free_result']($dbresult);
		
		// Unapproved links
		$dbresult = $smcFunc['db_query']('', '
			SELECT COUNT(*) AS total 
			FROM {db_prefix}links AS l 
			WHERE l.approved = 0'
		);
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$context['alinks_total'] = $row['total'];
		$smcFunc['db_free_result']($dbresult);
		
		$dbresult = $smcFunc['db_query']('', '
			SELECT COUNT(*) AS total 
			FROM {db_prefix}links
			WHERE approved = 1'
		);
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$context['link_count'] = $row['total'];
		$smcFunc['db_free_result']($dbresult);
	}
}

function GetParentLink($ID_CAT)
{
	global  $smcFunc, $context, $scripturl;

	if ($ID_CAT == 0)
		return;

	$dbresult1 = $smcFunc['db_query']('', '
		SELECT ID_PARENT,title
		FROM {db_prefix}links_cat
		WHERE ID_CAT = {int:this_cat}
		LIMIT 1',
		array(
			'this_cat' => $ID_CAT
		)
	);
	$row1 = $smcFunc['db_fetch_assoc']($dbresult1);

	$smcFunc['db_free_result']($dbresult1);
		
	GetParentLink($row1['ID_PARENT']);
		
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=links;cat=' . $ID_CAT ,
		'name' => $row1['title']
	);
}

function AddCat()
{
	global $context, $mbname, $txt, $smcFunc, $sourcedir;
	isAllowedTo('links_manage_cat');

	$context['sub_template']  = 'addcat';

	// Set the page title
	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_addcat'];

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title,roworder
		FROM {db_prefix}links_cat
		ORDER BY roworder ASC'
	);
	$context['links_cat'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['links_cat'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);

	// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'descript',
		'value' => '',
		'width' => '90%',
		'form' => 'links',
		'labels' => array(
			'addlink' => ''
		)
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	
}

function AddCat2()
{
	global $smcFunc, $txt, $sourcedir;

	isAllowedTo('links_manage_cat');
    
    checkSession('post');
	
	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['descript_mode']))
	{
		require_once($sourcedir . '/Subs-Editor.php');
		$_REQUEST['descript'] = un_htmlspecialchars(html_to_bbc($_REQUEST['descript']));
	}

	//Clean the input
	if (!empty($_POST['title']))
		$title = $smcFunc['htmlspecialchars'](trim($_POST['title']), ENT_QUOTES);
    else
        $title = '';   
        
	if (!empty($_REQUEST['descript']))
		$description =  $smcFunc['htmlspecialchars']($_REQUEST['descript'], ENT_QUOTES);
     else
        $description = '';  
        
	if (!empty($_POST['image']))
		$image =  htmlspecialchars($_POST['image'], ENT_QUOTES);
    else
        $image = 0;
        
	if (!empty($_REQUEST['parent']))
		$parent = (int) $_REQUEST['parent'];
    else
        $parent = 0;

	if ($title == '')
		fatal_error($txt['smflinks_nocattitle'], false);

	// Do the order
	$dbresult = $smcFunc['db_query']('', '
		SELECT roworder
		FROM {db_prefix}links_cat
		ORDER BY roworder DESC'
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);

	if (empty($row['roworder']))
		$row['roworder'] = 0;

	$order = (int) $row['roworder'];
	$order++;
    
    
    $smcFunc['db_insert']('insert',
		'{db_prefix}links_cat',
		array(
			'title' => 'string',
			'description' => 'string',
			'roworder' => 'int',
			'image' => 'string',
			'ID_PARENT' => 'int',
		),
		array(
			$title,
			$description,
			$order,
			$image,
			$parent,
		),
		array('ID_CAT')
	);


	$smcFunc['db_free_result']($dbresult);
	redirectexit('action=links');
}

function EditCat()
{
	global $context, $mbname, $txt, $smcFunc, $sourcedir;
	isAllowedTo('links_manage_cat');

	if (!empty($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];

	if (empty($cat))
		fatal_error($txt['smflinks_nocatselected'],false);

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, description, image, ID_PARENT
		FROM {db_prefix}links_cat
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $cat,
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
 
    if (empty($row['ID_CAT']))
           fatal_error($txt['smflinks_nocatselected'],false);
    
	$context['links_edit_cat'] = $row;
	$smcFunc['db_free_result']($dbresult);

	$context['sub_template']  = 'editcat';

	// Set the page title
	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_editcat'];

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, roworder
		FROM {db_prefix}links_cat
		ORDER BY roworder ASC'
	);
	$context['links_cat'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['links_cat'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, description, image, ID_PARENT
		FROM {db_prefix}links_cat
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $cat,
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);

	// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'descript',
		'value' => $row['description'],
		'width' => '90%',
		'form' => 'links',
		'labels' => array(
			'addlink' => ''
		)
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	
}

function EditCat2()
{
	global $smcFunc, $txt, $sourcedir;

	isAllowedTo('links_manage_cat');
    
    checkSession('post');
	
	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['descript_mode']) && isset($_REQUEST['descript']))
	{
		require_once($sourcedir . '/Subs-Editor.php');
		$_REQUEST['descript'] = un_htmlspecialchars(html_to_bbc($_REQUEST['descript']));
	}

	// Clean the input
	if (!empty($_POST['title']))
		$title = $smcFunc['htmlspecialchars'](trim($_POST['title']), ENT_QUOTES);
    else
        $title = '';   
        
	if (!empty($_REQUEST['descript']))
		$description =  $smcFunc['htmlspecialchars']($_REQUEST['descript'], ENT_QUOTES);
     else
        $description = '';  
        
	if (!empty($_POST['image']))
		$image =  htmlspecialchars($_POST['image'], ENT_QUOTES);
    else
        $image = 0;
        
	if (!empty($_REQUEST['parent']))
		$parent = (int) $_REQUEST['parent'];
    else
        $parent = 0;
        
        
	if (!empty($_REQUEST['catid']))
		$catid = (int) $_REQUEST['catid'];


	if (empty($catid))
		fatal_error($txt['smflinks_nocatselected'],false);

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, description, image, ID_PARENT 
		FROM {db_prefix}links_cat 
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $catid
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
    
    if (empty($row['ID_CAT']))
           fatal_error($txt['smflinks_nocatselected'], false);

	if (empty($title))
		fatal_error($txt['smflinks_nocattitle'], false);

	// Update the category
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}links_cat
		SET
			title = {string:title},
			ID_PARENT = {int:parent},
			description = {string:description},
			image = {string:image}
		WHERE ID_CAT = {int:this_cat}
		LIMIT 1',
		array(
			'title' => $title,
			'parent' => $parent,
			'description' => $description,
			'image' => $image,
			'this_cat' => $catid,
		)
	);
    

	redirectexit('action=links');
}

function DeleteCat()
{
	global $context, $mbname, $txt, $smcFunc;
	isAllowedTo('links_manage_cat');

	$context['sub_template']  = 'deletecat';

	if (!empty($_REQUEST['cat']))
		$catid = (int) $_REQUEST['cat'];

	if (empty($catid))
		fatal_error($txt['smflinks_nocatselected'],false);
        
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, description, image, ID_PARENT 
		FROM {db_prefix}links_cat 
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $catid
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
 
    if (empty($row['ID_CAT']))
		fatal_error($txt['smflinks_nocatselected'],false);

	$context['links_catid'] = $catid;

	// Set the page title
	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_deltcat'];

}

function DeleteCat2()
{
	global $smcFunc, $txt;
    
    checkSession('post');
    
	isAllowedTo('links_manage_cat');

	if (!empty($_REQUEST['catid']))
		$catid = (int) $_REQUEST['catid'];

	if (empty($catid))
		fatal_error($txt['smflinks_nocatselected'],false);
    
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, description, image, ID_PARENT 
		FROM {db_prefix}links_cat 
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $catid
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
    
    if (empty($row['ID_CAT']))
           fatal_error($txt['smflinks_nocatselected'],false);

	// Delete All links
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}links
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $catid
		)
	);
	// Finally delete the category
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}links_cat
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $catid
		)
	);
	redirectexit('action=links');
}

function AddLink()
{
	global $context, $mbname, $txt, $smcFunc, $sourcedir, $modSettings;

	isAllowedTo('add_links');

	$context['sub_template']  = 'addlink';

	// Set the page title
	$context['page_title'] = $mbname  . $txt['smflinks_title'] . ' - ' . $txt['smflinks_addlink'];

	// Check the category level permission
	if (!empty($_REQUEST['cat']))
		$catid = (int) $_REQUEST['cat'];
    else
        $catid = 0;

	if (!empty($catid))
	   GetCatPermission($catid, 'addlink');

	$context['links_catid'] = $catid;

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, roworder
		FROM {db_prefix}links_cat
		ORDER BY roworder ASC'
	);

	// Get category count
	$cat_count = $smcFunc['db_affected_rows']();
	if (empty($cat_count))
		fatal_error($txt['smflinks_nofirstcat'], false);

	$context['links_cats'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['links_cats'][] = $row;
	$smcFunc['db_free_result']($dbresult);

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'descript',
		'value' => '',
		'width' => '90%',
		'form' => 'links',
		'labels' => array(
			'addlink' => ''
		)
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	

}

function AddLink2()
{
	global $user_info, $smcFunc, $txt, $sourcedir;

	isAllowedTo('add_links');
    
    checkSession('post');
	
	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['descript_mode']) && isset($_REQUEST['descript']))
	{
		require_once($sourcedir . '/Subs-Editor.php');
		$_REQUEST['descript'] = un_htmlspecialchars(html_to_bbc($_REQUEST['descript']));
	}

	// Clean the input
	if (!empty($_POST['title']))
		$title =  $smcFunc['htmlspecialchars'](trim($_POST['title']), ENT_QUOTES);
    else
        $title = '';
        
	if (!empty($_REQUEST['descript']))
		$description = $smcFunc['htmlspecialchars']($_REQUEST['descript'], ENT_QUOTES);
    else
        $description = '';    
        
	if (!empty($_POST['url']))
		$url = $smcFunc['htmlspecialchars'](trim($_POST['url']), ENT_QUOTES);
	else
		$url = '';

	if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0)
		fatal_error($txt['smflinks_err_linkmuststart'], false);

	if (CheckDisallowedDomain($url))
		fatal_error($txt['smflinks_err_domain_disallowed'], false);

	if (!empty($_REQUEST['catid']))
		$catid = (int) $_REQUEST['catid'];
	else
		$catid = 0;

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, description, image, ID_PARENT
		FROM {db_prefix}links_cat
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $catid
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);

    if (empty($row['ID_CAT']))
           fatal_error($txt['smflinks_nocatselected'], false);

	GetCatPermission($catid, 'addlink');

	if (empty($title))
		fatal_error($txt['smflinks_nolinktitle'], false);

	if (empty($url))
		fatal_error($txt['smflinks_nolinkurl'], false);

	// Check if the url already exists?
	$dbresult = $smcFunc['db_query']('', '
		SELECT l.url,l.ID_CAT, l.title linkname, c.title cname
		FROM {db_prefix}links AS l, {db_prefix}links_cat AS c
		WHERE l.url = {string:this_url}
			AND l.ID_CAT = c.ID_CAT',
		array(
			'this_url' => $url
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	// The link already exists
	if ($smcFunc['db_affected_rows']() != 0)
	{
		$msg = $txt['smflinks_linkexists'];
		$msg = str_replace('%c', $row['cname'], $msg);
		$msg = str_replace('%l', $row['linkname'], $msg);
		fatal_error($msg, false);
	}
	$smcFunc['db_free_result']($dbresult);

	//Insert the links
	$t = time();

	$approved = allowedTo('links_auto_approve') ? 1 : 0;

	$smcFunc['db_insert']('insert',
		'{db_prefix}links',
		array(
			'ID_CAT' => 'int',
			'url' => 'string',
			'title' => 'string',
			'description' => 'string',
			'ID_MEMBER' => 'int',
			'date' => 'int',
			'approved' => 'int',
		),
		array(
			$catid,
			$url,
			$title,
			$description,
			$user_info['id'],
			$t,
			$approved,
		),
		array()
	);

	// Redirect back to category
	if ($approved)
		redirectexit('action=links;cat=' . $catid);
	else
		fatal_error($txt['smflinks_linkneedsapproval'], false);
}

function EditLink()
{
	global $context, $mbname, $txt, $smcFunc, $modSettings, $sourcedir, $user_info;

	is_not_guest();

	$context['sub_template']  = 'editlink';

	//Set the page title
	$context['page_title'] = $mbname  . $txt['smflinks_title'] . ' - ' . $txt['smflinks_editlink'];

	// Lookup the link and see if they can edit it.
	if (!empty($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected'],false);

	$context['link_id'] = $id;

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_LINK, title, ID_CAT, description, url, ID_MEMBER
		FROM {db_prefix}links
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	$context['links_link'] = $row;

	if (!allowedTo('edit_links_any') && (!allowedTo('edit_links_own') || $row['ID_MEMBER'] != $user_info['id']))
		fatal_error($txt['smflinks_perm_link_no_edit'],false);

	GetCatPermission($row['ID_CAT'], 'editlink');

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, roworder
		FROM {db_prefix}links_cat
		ORDER BY roworder ASC'
	);

	// Get category count
	$cat_count = $smcFunc['db_affected_rows']();
	if ($cat_count == 0)
		fatal_error($txt['smflinks_nofirstcat'], false);

	$context['links_cats'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['links_cats'][] = $row;
	$smcFunc['db_free_result']($dbresult);

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'descript',
		'value' => $context['links_link']['description'],
		'width' => '90%',
		'form' => 'links',
		'labels' => array(
			'addlink' => ''
		)
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];	
}

function EditLink2()
{
	global $smcFunc, $txt, $user_info, $sourcedir;

	is_not_guest();
    
    checkSession('post');

	if (!empty($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];

	if (empty($_REQUEST['id']))
		fatal_error($txt['smflinks_nolinkselected'],false);

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_MEMBER
		FROM {db_prefix}links
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if (!allowedTo('edit_links_any') && (!allowedTo('edit_links_own') || $row['ID_MEMBER'] != $user_info['id']))
		fatal_error($txt['smflinks_perm_link_no_edit']);

	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['descript_mode']) && isset($_REQUEST['descript']))
	{
		require_once($sourcedir . '/Subs-Editor.php');
		$_REQUEST['descript'] = un_htmlspecialchars(html_to_bbc($_REQUEST['descript']));
	}

	// Clean the input
	if (!empty($_POST['title']))
		$title = $smcFunc['htmlspecialchars'](trim($_POST['title']), ENT_QUOTES);
    else
        $title = '';
        
	if (!empty($_REQUEST['descript']))
		$description = $smcFunc['htmlspecialchars']($_REQUEST['descript'], ENT_QUOTES);
    else
        $description = '';
        
	if (!empty($_POST['url']))
		$url = $smcFunc['htmlspecialchars'](trim($_POST['url']), ENT_QUOTES);
	else
		$url = '';

	if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0)
		fatal_error($txt['smflinks_err_linkmuststart'], false);

	if (CheckDisallowedDomain($url))
		fatal_error($txt['smflinks_err_domain_disallowed'], false);

	if (!empty($_REQUEST['catid']))
		$catid = (int) $_REQUEST['catid'];
	else
		$catid = 0;

	if (empty($catid))
		fatal_error($txt['smflinks_nocatselected'], false);

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, description, image, ID_PARENT
		FROM {db_prefix}links_cat
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $catid
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);

    if (empty($row['ID_CAT']))
		fatal_error($txt['smflinks_nocatselected'],false);

	GetCatPermission($catid, 'editlink');

	if (empty($title))
		fatal_error($txt['smflinks_nolinktitle'], false);
	elseif (empty($url))
		fatal_error($txt['smflinks_nolinkurl'], false);

	// Update the link
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}links
		SET
			title = {string:title},
			url = {string:url},
			description = {string:description},
			ID_CAT = {int:this_cat}
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'title' => $title,
			'url' => $url,
			'description' => $description,
			'this_cat' => $catid,
			'this_id' => $id,
		)
	);
	// Redirect back to category
	redirectexit('action=links');
}

function DeleteLink()
{
	global $context, $mbname, $txt, $smcFunc, $user_info;

	if (!empty($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];

	is_not_guest();

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected'],false);

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_MEMBER
		FROM {db_prefix}links
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if (!allowedTo('delete_links_any') && (!allowedTo('delete_links_own') || $row['ID_MEMBER'] != $user_info['id']))
		fatal_error($txt['smflinks_perm_link_no_delete'],false);

	$context['links_id'] = $id;

	$context['sub_template']  = 'deletelink';

	// Set the page title
	$context['page_title'] = $mbname  . $txt['smflinks_title'] . ' - ' . $txt['smflinks_dellink'];

	// Check if they are allowed to delete the link
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_LINK,ID_CAT
		FROM {db_prefix}links
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	GetCatPermission($row['ID_CAT'],'dellink');
}

function DeleteLink2()
{
	global $smcFunc, $txt, $user_info;

	is_not_guest();
    
    checkSession('post');

	if (!empty($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected'],false);

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_MEMBER
		FROM {db_prefix}links
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if (!allowedTo('delete_links_any') && (!allowedTo('delete_links_own') || $row['ID_MEMBER'] != $user_info['id']))
		fatal_error($txt['smflinks_perm_link_no_delete'],false);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}links
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	redirectexit('action=links');
}

function VisitLink()
{
	global $smcFunc, $txt, $modSettings;

	// Check if the current user can view the links list
	isAllowedTo('view_smflinks');

	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	if (!empty($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected'],false);

	// Update site lists
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}links
		SET
			hits = hits + 1
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);

	// Redirect to the site
	$dbresult = $smcFunc['db_query']('', '
		SELECT url,ID_LINK
		FROM {db_prefix}links
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);

    if (empty($row['ID_LINK']))
        fatal_error($txt['smflinks_nolinkselected'],false);
    
	$smcFunc['db_free_result']($dbresult);
	header("Location: " . $row['url']);

	obExit(false);
	die();
}

function CatUp()
{
	global $smcFunc, $txt;
    
    checkSession('get');
	// Check if they are allowed to manage cats
	isAllowedTo('links_manage_cat');

	// Get the cat id
	if (!empty($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];

	if (empty($cat))
		fatal_error($txt['smflinks_nocatselected'],false);

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, description, image, ID_PARENT 
		FROM {db_prefix}links_cat 
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $cat
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
    
    if (empty($row['ID_CAT']))
           fatal_error($txt['smflinks_nocatselected'],false);

	ReOrderCats($cat);
	// Check if there is a category above it
	// First get our row order
	$dbresult1 = $smcFunc['db_query']('', '
		SELECT roworder, ID_PARENT
		FROM {db_prefix}links_cat
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $cat
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$oldrow = $row['roworder'];
	$ID_PARENT = $row['ID_PARENT'];
	$o = $row['roworder'];
	$o--;

	$smcFunc['db_free_result']($dbresult1);
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, roworder
		FROM {db_prefix}links_cat
		WHERE ID_PARENT = {int:id_parent}
			AND roworder = {int:row_order}',
		array(
			'id_parent' => $ID_PARENT,
			'row_order' => $o
		)
	);
	if ($smcFunc['db_affected_rows']()== 0)
		fatal_error($txt['smflinks_nocatabove'],false);
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);

	// Swap the order Id's
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}links_cat
		SET
			roworder = {int:row_order}
		WHERE ID_CAT = {int:id_cat}',
		array(
			'row_order' => $oldrow,
			'id_cat' => $row2['ID_CAT']
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}links_cat
		SET roworder = {int:row_order}
		WHERE ID_CAT = {int:this_cat}',
		array(
			'row_order' => $o,
			'this_cat' => $cat
		)
	);
	$smcFunc['db_free_result']($dbresult);

	// Redirect to index to view cats
	if ($ID_PARENT == 0)
		redirectexit('action=links');
	else 
		redirectexit('action=links;cat=' . $ID_PARENT);
}

function CatDown()
{
	global $smcFunc, $txt;
    
    checkSession('get');

	// Check if they are allowed to manage cats
	isAllowedTo('links_manage_cat');

	// Get the cat id
	if (!empty($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];

	if (empty($cat))
		fatal_error($txt['smflinks_nocatselected'],false);
    
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, description, image, ID_PARENT
		FROM {db_prefix}links_cat
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $cat,
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
    
    if (empty($row['ID_CAT']))
           fatal_error($txt['smflinks_nocatselected'],false);

	ReOrderCats($cat);
	// Check if there is a category below it
	// First get our row order
	$dbresult1 = $smcFunc['db_query']('', '
		SELECT roworder, ID_PARENT
		FROM {db_prefix}links_cat
		WHERE ID_CAT = {int:this_cat}',
		array(
			'this_cat' => $cat
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o++;

	$smcFunc['db_free_result']($dbresult1);
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, roworder
		FROM {db_prefix}links_cat
		WHERE ID_PARENT = {int:this_parent}
			AND roworder = {int:row_order}',
		array(
			'this_parent' => $ID_PARENT,
			'row_order' => $o
		)
	);
	if ($smcFunc['db_affected_rows']()== 0)
		fatal_error($txt['smflinks_nocatbelow'],false);
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);

	// Swap the order Id's
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}links_cat
		SET
			roworder = {int:row_order}
		WHERE ID_CAT = {int:id_cat}',
		array(
			'row_order' => $oldrow,
			'id_cat' => $row2['ID_CAT']
		)
	);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}links_cat
		SET
			roworder = {int:row_order}
		WHERE ID_CAT = {int:id_cat}',
		array(
			'row_order' => $o,
			'id_cat' => $cat
		)
	);
	$smcFunc['db_free_result']($dbresult);

	// Redirect to index to view cats
	if ($ID_PARENT == 0)
		redirectexit('action=links');
	else 
		redirectexit('action=links;cat=' . $ID_PARENT);
}

function ApproveList()
{
	global $context, $mbname, $txt, $smcFunc;
	isAllowedTo('approve_links');

	$context['sub_template']  = 'approvelinks';

	DoLinksAdminTabs();

	// Set the page title
	$context['page_title'] = $mbname . $txt['smflinks_title'] .' - ' . $txt['smflinks_approvelinks'] ;

	$dbresult = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS total 
		FROM {db_prefix}links 
		WHERE approved = 0'
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$context['approval_total_links'] = $row['total'];
	$smcFunc['db_free_result']($dbresult);

	if (!empty($_REQUEST['start']))
		$context['start'] = (int) $_REQUEST['start'];
	else
		$context['start'] = 0;
	
	$dbresult = $smcFunc['db_query']('', '
		SELECT l.ID_LINK, l.approved, l.description,
			l.url, l.title, l.date, m.real_name,
			l.ID_MEMBER, l.hits, l.ID_CAT, c.title catname 
 		FROM ({db_prefix}links AS l, {db_prefix}links_cat AS c) 
		LEFT JOIN {db_prefix}members AS m ON (l.ID_MEMBER = m.ID_MEMBER) 
		WHERE l.ID_CAT = c.ID_CAT
			AND l.approved = 0
		ORDER BY l.ID_LINK DESC
		LIMIT {int:this_start},20',
		array(
			'this_start' => $context['start']
		)
	);
	$context['links_approval_list'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['links_approval_list'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
}

function Approve()
{
	global $smcFunc, $txt;

	isAllowedTo('approve_links');
    checkSession('get');

	// Get link id
	if (!empty($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected'],false);
    
 	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_LINK
		FROM {db_prefix}links
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);

    if (empty($row['ID_LINK']))
        fatal_error($txt['smflinks_nolinkselected'],false);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}links
		SET
			approved = 1
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	redirectexit('action=admin;area=links;sa=alist');
}

function NoApprove()
{
	global $smcFunc, $txt;
    
    checkSession('get');
	isAllowedTo('approve_links');

	// Get link id
	if (!empty($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected'],false);
    
 	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_LINK
		FROM {db_prefix}links
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
    
    if (empty($row['ID_LINK']))
        fatal_error($txt['smflinks_nolinkselected'],false);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}links
		SET
			approved = 0
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	redirectexit('action=links');
}

function RateLink()
{
	global $smcFunc, $txt, $user_info;

	isAllowedTo('rate_links');

	// Guests can't rate links? Cause how can we keep track??? Unless we do ip's but blew
	is_not_guest();

	// Get the link ID
	if (!empty($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smflinks_nolinkselected'],false);
        
 	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_LINK
		FROM {db_prefix}links
		WHERE ID_LINK = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);

    if (empty($row['ID_LINK']))
        fatal_error($txt['smflinks_nolinkselected'],false);

	//See if the user already rated the link.
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_LINK, ID_MEMBER
		FROM {db_prefix}links_rating
		WHERE ID_MEMBER = {int:this_user}
			AND ID_LINK = {int:this_id}',
		array(
			'this_user' => $user_info['id'],
			'this_id' => $id
		)
	);
	if ($smcFunc['db_affected_rows']()!= 0)
		fatal_error($txt['smflinks_alreadyrated'],false);
	$smcFunc['db_free_result']($dbresult);

	// Get the value of rating
	if (!empty($_REQUEST['value']))
		$value = (int) $_REQUEST['value'];
	else
		$value = 0;

	// Check value
	if ($value == 0)
	{
		// Lower Ranking

		//Insert rating
		$smcFunc['db_insert']('insert',
			'{db_prefix}links_rating',
			array(
				'ID_LINK' => 'int',
				'ID_MEMBER' => 'int',
				'value' => 'int'
			),
			array(
				$id,
				$user_info['id'],
				0
			),
			array()
		);

		// Update main link rating
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}links
			SET
				rating = rating - 1
			WHERE ID_LINK = {int:this_id}
			LIMIT 1',
			array(
				'this_id' => $id
			)
		);
	}
	else
	{
		//Higher Ranking
		//Insert rating
		$smcFunc['db_insert']('insert',
			'{db_prefix}links_rating',
			array(
				'ID_LINK' => 'int',
				'ID_MEMBER' => 'int',
				'value' => 'int'
			),
			array(
				$id,
				$user_info['id'],
				1
			),
			array()
		);

		//Update main link rating
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}links
			SET
				rating = rating + 1
			WHERE ID_LINK = {int:this_id}
			LIMIT 1',
			array(
				'this_id' => $id
			)
		);
	}
	redirectexit('action=links');
}

function LinksAdmin()
{
	global $context, $mbname, $txt;
	isAllowedTo('links_manage_cat');
	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_settings'];
	DoLinksAdminTabs();
	$context['sub_template']  = 'settings';
}

function LinksAdmin2()
{
	isAllowedTo('links_manage_cat');
    
    checkSession('post');

	if (!empty($_REQUEST['smflinks_setlinksperpage']))
		$smflinks_setlinksperpage = (int) $_REQUEST['smflinks_setlinksperpage'];

	$smflinks_setshowtoprate = isset($_REQUEST['smflinks_setshowtoprate'])? 1 : 0;
	$smflinks_setshowmostvisited = isset($_REQUEST['smflinks_setshowmostvisited'])? 1 : 0;
	$smflinks_setshowstats = isset($_REQUEST['smflinks_setshowstats'])? 1 : 0;
	$smflinks_setallowbbc = isset($_REQUEST['smflinks_setallowbbc'])? 1 : 0;
	$smflinks_set_count_child = isset($_REQUEST['smflinks_set_count_child']) ? 1 : 0;

	// Link Display Settings
	$smflinks_disp_description = isset($_REQUEST['smflinks_disp_description'])? 1 : 0;
	$smflinks_disp_hits = isset($_REQUEST['smflinks_disp_hits'])? 1 : 0;
	$smflinks_disp_rating = isset($_REQUEST['smflinks_disp_rating'])? 1 : 0;
	$smflinks_disp_membername = isset($_REQUEST['smflinks_disp_membername'])? 1 : 0;
	$smflinks_disp_date = isset($_REQUEST['smflinks_disp_date'])? 1 : 0;
	$smflinks_disp_thumbnail = isset($_REQUEST['smflinks_disp_thumbnail'])? 1 : 0;

	// Link Checker Settings
	$smflinks_check_batch_size = !empty($_REQUEST['smflinks_check_batch_size']) ? max(5, min(200, (int) $_REQUEST['smflinks_check_batch_size'])) : 25;
	$smflinks_check_notify_pm = isset($_REQUEST['smflinks_check_notify_pm']) ? 1 : 0;

	// Save the setting information
	updateSettings(
		array(
			'smflinks_setlinksperpage' => $smflinks_setlinksperpage,
			'smflinks_setshowtoprate' => $smflinks_setshowtoprate,
			'smflinks_setshowmostvisited' => $smflinks_setshowmostvisited,
			'smflinks_setshowstats' => $smflinks_setshowstats,
			'smflinks_set_count_child' => $smflinks_set_count_child,
			'smflinks_setallowbbc' => $smflinks_setallowbbc,
			'smflinks_disp_description' => $smflinks_disp_description,
			'smflinks_disp_hits' => $smflinks_disp_hits,
			'smflinks_disp_rating' => $smflinks_disp_rating,
			'smflinks_disp_membername' => $smflinks_disp_membername,
			'smflinks_disp_date' => $smflinks_disp_date,
			'smflinks_disp_thumbnail' => $smflinks_disp_thumbnail,
			'smflinks_check_batch_size' => $smflinks_check_batch_size,
			'smflinks_check_notify_pm' => $smflinks_check_notify_pm,
		)
	);
	redirectexit('action=admin;area=links;sa=admin');
}

function LinksAdminCats()
{
	global $context, $mbname, $txt, $smcFunc;
	isAllowedTo('links_manage_cat');
	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_managecats'];
	DoLinksAdminTabs();
	$context['sub_template']  = 'manage_cats';

	// List all the categories
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, roworder, description,  ID_PARENT 
		FROM {db_prefix}links_cat
	
			ORDER BY roworder ASC'
	);
	$context['links_cats'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['links_cats'][] = $row;
	$smcFunc['db_free_result']($dbresult);
}

function LinksAdminPerm()
{
	global $context, $mbname, $txt, $smcFunc;
	isAllowedTo('links_manage_cat');
	DoLinksAdminTabs();
	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_catpermlist'];
	$context['sub_template']  = 'catpermlist';

	// Show the member groups
	$dbresult = $smcFunc['db_query']('', '
		SELECT c.ID_CAT, c.ID, c.view, c.addlink, c.editlink,
			c.dellink, c.ID_GROUP, m.group_name,a.title catname 
		FROM ({db_prefix}links_catperm AS c, {db_prefix}membergroups AS m, {db_prefix}links_cat AS a) 
		WHERE c.ID_GROUP = m.ID_GROUP AND a.ID_CAT = c.ID_CAT'
	);
	$context['links_m_groups'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['links_m_groups'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
	
	//Show Regular members
	$dbresult = $smcFunc['db_query']('', '
		SELECT c.ID_CAT, c.ID, c.view, c.addlink,
			c.editlink, c.dellink,   c.ID_GROUP,a.title catname 
		FROM {db_prefix}links_catperm AS c,{db_prefix}links_cat AS a 
		WHERE  c.ID_GROUP = 0
			AND a.ID_CAT = c.ID_CAT LIMIT 1'
	);
	$context['links_reg_groups'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['links_reg_groups'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
	
	// Show Guests
	$dbresult = $smcFunc['db_query']('', '
		SELECT c.ID_CAT, c.ID, c.view, c.addlink,
			c.editlink, c.dellink, c.ID_GROUP,a.title catname 
		FROM {db_prefix}links_catperm AS c,{db_prefix}links_cat AS a 
		WHERE c.ID_GROUP = -1
			AND a.ID_CAT = c.ID_CAT LIMIT 1'
	);
	$context['links_guests_groups'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['links_guests_groups'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
}

function GetCatPermission($cat, $perm)
{
	global $smcFunc, $txt, $user_info;

	$cat = (int) $cat;
	if (!$user_info['is_guest'])
	{
		$dbresult = $smcFunc['db_query']('', '
			SELECT m.ID_MEMBER, c.view, c.addlink,
				c.editlink, c.dellink,c.ratelink, c.report
			FROM {db_prefix}links_catperm AS c, {db_prefix}members AS m
			WHERE m.ID_MEMBER = {int:this_user}
				AND c.ID_GROUP = m.ID_GROUP
				AND c.ID_CAT = {int:this_cat}
			LIMIT 1',
			array(
				'this_user' => $user_info['id'],
				'this_cat' => $cat
			)
		);
	}
	else
		$dbresult = $smcFunc['db_query']('', '
			SELECT c.view, c.addlink, c.editlink,
				c.dellink,c.ratelink, c.report
			FROM {db_prefix}links_catperm AS c
			WHERE c.ID_GROUP = -1
				AND c.ID_CAT = {int:this_cat}
			LIMIT 1',
			array(
				'this_cat' => $cat
			)
		);

	if ($smcFunc['db_affected_rows']() != 0)
	{
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		if ($perm == 'view' && $row['view'] == 0)
			fatal_error($txt['smflinks_perm_no_view'], false);
		elseif ($perm == 'addlink' && $row['addlink'] == 0)
			fatal_error($txt['smflinks_perm_no_add'], false);
		elseif ($perm == 'editlink' && $row['editlink'] == 0)
			fatal_error($txt['smflinks_perm_no_edit'], false);
		elseif ($perm == 'dellink' && $row['dellink'] == 0)
			fatal_error($txt['smflinks_perm_no_delete'], false);
		elseif ($perm == 'ratelink' && $row['ratelink'] == 0)
			fatal_error($txt['smflinks_perm_no_ratelink'], false);
		elseif ($perm == 'report' && $row['report'] == 0)
			fatal_error($txt['smflinks_perm_no_report'], false);
	}
	$smcFunc['db_free_result']($dbresult);
}

function CatPermDelete()
{
	global $smcFunc, $txt;
	isAllowedTo('links_manage_cat');
	checkSession('get');

	if (!empty($_REQUEST['id']))
		$id = (int) $_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['smflinks_nocatselected'], false);

	// Delete the Permission
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}links_catperm
		WHERE ID = {int:this_id}
		LIMIT 1',
		array(
			'this_id' => $id
		)
	);
	// Redirect to the ratings
	redirectexit('action=admin;area=links;sa=adminperm');
}

function CatPerm()
{
	global $mbname, $txt, $smcFunc, $context;
	isAllowedTo('links_manage_cat');

	if (!empty($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];
	if (empty($cat))
		fatal_error($txt['smflinks_nocatselected'],false);

	$dbresult1 = $smcFunc['db_query']('', '
		SELECT ID_CAT, title
		FROM {db_prefix}links_cat
		WHERE ID_CAT = {int:this_cat}
		LIMIT 1',
		array(
			'this_cat' => $cat
		)
	);
	$row1 = $smcFunc['db_fetch_assoc']($dbresult1);

    if (empty($row1['ID_CAT']))
		fatal_error($txt['smflinks_nocatselected'], false);

	$context['links_cat_name'] = $row1['title'];
	$smcFunc['db_free_result']($dbresult1);

	loadLanguage('Admin');

	$context['links_cat'] = $cat;

	// Load the template
	$context['sub_template']  = 'catperm';
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['smflinks_title'] . ' - ' . $txt['smflinks_text_catperm'] . ' -' . $context['links_cat_name'];

	// Load the membergroups
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_GROUP, group_name
		FROM {db_prefix}membergroups
		WHERE min_posts = -1
		ORDER BY group_name'
	);
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['groups'][$row['ID_GROUP']] = array(
			'ID_GROUP' => $row['ID_GROUP'],
			'group_name' => $row['group_name'],
		);
	}
	$smcFunc['db_free_result']($dbresult);
	
	$dbresult = $smcFunc['db_query']('', '
		SELECT c.ID_CAT, c.ID, c.view, c.addlink,
			c.editlink, c.dellink,  c.ID_GROUP, m.group_name,a.title catname 
		FROM ({db_prefix}links_catperm AS c, {db_prefix}membergroups AS m,{db_prefix}links_cat AS a) 
		WHERE c.ID_CAT = {int:id_cat}
			AND c.ID_GROUP = m.ID_GROUP
			AND a.ID_CAT = c.ID_CAT',
		array(
			'id_cat' => $context['links_cat'],
		)
	);
	$context['links_mgroups'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['links_mgroups'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
	
	// Regular members
	$dbresult = $smcFunc['db_query']('', '
		SELECT c.ID_CAT, c.ID, c.view, c.addlink,
			c.editlink, c.dellink, c.ID_GROUP,a.title catname 
		FROM ({db_prefix}links_catperm AS c,{db_prefix}links_cat AS a) 
		WHERE c.ID_CAT = {int:id_cat}
			AND c.ID_GROUP = 0
			AND a.ID_CAT = c.ID_CAT
		LIMIT 1',
 		array(
		  	'id_cat' => $context['links_cat']
		)
	);
	$context['links_reggroups'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['links_reggroups'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
	
	// Show Guests
	$dbresult = $smcFunc['db_query']('', '
		SELECT c.ID_CAT, c.ID, c.view, c.addlink,
			c.editlink, c.dellink,  c.ID_GROUP,a.title catname 
		FROM {db_prefix}links_catperm AS c, {db_prefix}links_cat AS a 
		WHERE c.ID_CAT = {int:id_cat}
			AND c.ID_GROUP = -1
			AND a.ID_CAT = c.ID_CAT
		LIMIT 1',
		array(
			'id_cat' => $context['links_cat']
		)
	);
	$context['links_guests_groups'] = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['links_guests_groups'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
}

function CatPerm2()
{
	global  $smcFunc, $txt;
	isAllowedTo('links_manage_cat');
    checkSession('post');

	if (!empty($_REQUEST['group_name']))
		$group_name = (int) $_REQUEST['group_name'];
	else
		$group_name = 0;
		
	if (!empty($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];

	if (empty($cat))
		fatal_error($txt['smflinks_nocatselected'],false);
    
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, description, image, ID_PARENT 
		FROM {db_prefix}links_cat 
		WHERE ID_CAT = {int:id_cat}',
		array(
			'id_cat' => $cat
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
    
    if (empty($row['ID_CAT']))
		fatal_error($txt['smflinks_nocatselected'], false);

	// Check if permission exits
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_GROUP,ID_CAT
		FROM {db_prefix}links_catperm
		WHERE ID_GROUP = {int:group_name}
			AND ID_CAT = {int:id_cat}',
		array(
			'group_name' => $group_name,
			'id_cat' => $cat
		)
	);
	if ($smcFunc['db_affected_rows']() != 0)
	{
		$smcFunc['db_free_result']($dbresult);
		fatal_error($txt['smflinks_permerr_permexist'], false);
	}
	$smcFunc['db_free_result']($dbresult);

	//Permissions
	$view = isset($_REQUEST['view']) ? 1 : 0;
	$add = isset($_REQUEST['add']) ? 1 : 0;
	$edit = isset($_REQUEST['edit']) ? 1 : 0;
	$delete = isset($_REQUEST['delete']) ? 1 : 0;

	// Insert into database
	$smcFunc['db_insert']('insert',
		'{db_prefix}links_catperm',
		array(
			'ID_GROUP' => 'int',
			'ID_CAT' => 'int',
			'view' => 'int',
			'addlink' => 'int',
			'editlink' => 'int',
			'dellink' => 'int'
		),
		array(
			$group_name,
			$cat,
			$view,
			$add,
			$edit,
			$delete
		),
		array()
	);
	redirectexit('action=links;sa=catperm;cat=' . $cat);
}

function ReOrderCats($cat)
{
	global $smcFunc;
	$dbresult1 = $smcFunc['db_query']('', '
		SELECT roworder,ID_PARENT
		FROM {db_prefix}links_cat
		WHERE ID_CAT = {int:id_cat}',
		array(
			'id_cat' => $cat
		)
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$ID_PARENT = $row['ID_PARENT'];
	$smcFunc['db_free_result']($dbresult1);

	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, roworder
		FROM {db_prefix}links_cat
		WHERE ID_PARENT = {int:id_parent}
		ORDER BY roworder ASC',
		array(
			'id_parent' => $ID_PARENT
		)
	);
	if ($smcFunc['db_affected_rows']() != 0)
	{
		$count = 1;
		while($row2 = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}links_cat
				SET
					roworder = {int:row_order}
				WHERE ID_CAT = {int:id_cat}',
				array(
					'row_order' => $count,
					'id_cat' => $row2['ID_CAT']
				)
			);
			$count++;
		}
	}
	$smcFunc['db_free_result']($dbresult);
}

function DoLinksAdminTabs($overrideSelected = '')
{
	global $context, $txt, $scripturl, $smcFunc;

	$tmpSA = '';
	if (!empty($overrideSelected))
		$_REQUEST['sa'] = $overrideSelected;

	// Get the number links waiting for approval
	$dbresult = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS total
		FROM {db_prefix}links
		WHERE approved = 0'
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$alinks_total = $row['total'];
	$smcFunc['db_free_result']($dbresult);

	if (!empty($overrideSelected))
		$_REQUEST['sa'] = $tmpSA;
	
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' =>  $txt['smflinks_admin'],
		'description' => '',
		'tabs' => array(
			'admin' => array(
				'description' => $txt['smflinks_linkssettings_des']
			),
			'admincat' => array(
				'description' => $txt['smflinks_anagecats_des']
			),
			'alist' => array(
				'description' => $txt['smflinks_approvelinks_desc']
			),
			'adminperm' => array(
				'description' => $txt['smflinks_catpermlist_desc']
			),
			'checklinks' => array(
				'description' => $txt['smflinks_checklinks_desc']
			),
			'disalloweddomains' => array(
				'description' => $txt['smflinks_disallowed_domains_desc']
			)
		)
	);
}

function GetLinkTotals($ID_CAT)
{
	global $modSettings, $smcFunc, $subcats_linktree, $scripturl;

	$total = 0;
	// First get the parents total links
	$dbresult2 = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS totallinks
		FROM {db_prefix}links
		WHERE ID_CAT = {int:id_cat}
			AND approved = 1',
		array(
			'id_cat' => $ID_CAT
		)
	);
	$row2 = $smcFunc['db_fetch_assoc']($dbresult2);
	$total += $row2['totallinks'];
	$smcFunc['db_free_result']($dbresult2);

	$subcats_linktree = '';

	// Get the child categories to this category
	if ($modSettings['smflinks_set_count_child'])
	{
		$dbresult3 = $smcFunc['db_query']('', '
			SELECT ID_CAT,title
			FROM {db_prefix}links_cat
			WHERE ID_PARENT = {int:id_cat}',
			array(
				'id_cat' => $ID_CAT
			)
		);
		while($row3 = $smcFunc['db_fetch_assoc']($dbresult3))
		{
			$subcats_linktree .= '<a href="' . $scripturl . '?action=links;cat=' . $row3['ID_CAT'] . '">' . $row3['title'] . '</a>&nbsp;&nbsp;';

			/*
			$dbresult2 = $smcFunc['db_query']('', "
			SELECT
				COUNT(*) as totallinks
			FROM {db_prefix}links
			WHERE ID_CAT = " . $row3['ID_CAT'] . " AND approved = 1");
			while($row2 = $smcFunc['db_fetch_assoc']($dbresult2))
			{
				$total += $row2['totallinks'];
			}
			$smcFunc['db_free_result']($dbresult2);
			*/
		}

		$dbresult3 = $smcFunc['db_query']('', '
			SELECT ID_CAT, ID_PARENT
			FROM {db_prefix}links_cat
			WHERE ID_PARENT <> 0'
		);
		$childArray = array();
		while($row3 = $smcFunc['db_fetch_assoc']($dbresult3))
		{
			$dbresult2 = $smcFunc['db_query']('', '
				SELECT COUNT(*) AS totallinks 
				FROM {db_prefix}links 
				WHERE ID_CAT = {int:id_cat}
					AND approved = {int:is_approved}',
				array(
					'id_cat' => $row3['ID_CAT'],
					'is_approved' => 1
				)
			);
			$row2 = $smcFunc['db_fetch_assoc']($dbresult2);
			$row3['total']	= $row2['totallinks'];
			$childArray[] = $row3;
		}
		$total += Links_GetFileTotalsByParent($ID_CAT, $childArray);
		$smcFunc['db_free_result']($dbresult3);
	}
	return $total;
}

function Links_GetFileTotalsByParent($ID_PARENT, $data)
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

function ShowSubCats($cat)
{
	global $smcFunc, $context, $subcats_linktree;

	// List all the subcategories
	$dbresult = $smcFunc['db_query']('', '
		SELECT ID_CAT, title, roworder, description, image
		FROM {db_prefix}links_cat
		WHERE ID_PARENT = {int:id_cat}
		ORDER BY roworder ASC',
		array(
			'id_cat' => $cat,
		)
	);

	$context['links_subcats'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$row['totallinks'] = GetLinkTotals($row['ID_CAT']);
		$row['subcats_linktree'] = $subcats_linktree;
		$context['links_subcats'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
}

function LinksReorderCats()
{
	global $smcFunc;

	isAllowedTo('links_manage_cat');
	checkSession('get');

	$input = file_get_contents('php://input');
	$data = json_decode($input, true);

	if (empty($data) || !is_array($data))
	{
		header('Content-Type: application/json');
		echo json_encode(array('success' => false, 'error' => 'Invalid data'));
		obExit(false);
		die();
	}

	foreach ($data as $item)
	{
		$id = (int) $item['id'];
		$order = (int) $item['order'];
		$parent = (int) $item['parent'];

		if ($id > 0)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}links_cat
				SET roworder = {int:row_order}, ID_PARENT = {int:parent}
				WHERE ID_CAT = {int:id_cat}',
				array(
					'row_order' => $order,
					'parent' => $parent,
					'id_cat' => $id,
				)
			);
		}
	}

	header('Content-Type: application/json');
	echo json_encode(array('success' => true));
	obExit(false);
	die();
}

function LinksCheckLinks()
{
	global $context, $mbname, $txt, $smcFunc, $modSettings;

	isAllowedTo('links_manage_cat');
	DoLinksAdminTabs();

	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_checklinks'];
	$context['sub_template'] = 'checklinks';

	// Get total link count
	$dbresult = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS total
		FROM {db_prefix}links
		WHERE approved = 1'
	);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$context['links_total'] = $row['total'];
	$smcFunc['db_free_result']($dbresult);

	$context['check_batch_size'] = !empty($modSettings['smflinks_check_batch_size']) ? (int) $modSettings['smflinks_check_batch_size'] : 25;
	$context['check_notify_pm'] = !empty($modSettings['smflinks_check_notify_pm']);
}

function LinksCheckLinks2()
{
	global $smcFunc, $modSettings;

	isAllowedTo('links_manage_cat');

	$offset = !empty($_REQUEST['offset']) ? (int) $_REQUEST['offset'] : 0;
	$batch_size = !empty($modSettings['smflinks_check_batch_size']) ? (int) $modSettings['smflinks_check_batch_size'] : 25;

	// Get a batch of links
	$dbresult = $smcFunc['db_query']('', '
		SELECT l.ID_LINK, l.url, l.title, l.ID_CAT, l.ID_MEMBER, l.check_fails, c.title AS catname
		FROM {db_prefix}links AS l
		LEFT JOIN {db_prefix}links_cat AS c ON (l.ID_CAT = c.ID_CAT)
		WHERE l.approved = 1
		ORDER BY l.ID_LINK ASC
		LIMIT {int:offset}, {int:batch}',
		array(
			'offset' => $offset,
			'batch' => $batch_size,
		)
	);

	$links = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$links[$row['ID_LINK']] = $row;
	$smcFunc['db_free_result']($dbresult);

	// Nothing to check
	if (empty($links))
	{
		header('Content-Type: application/json');
		echo json_encode(array('results' => array()));
		obExit(false);
		die();
	}

	// Header storage for Cloudflare detection
	$response_headers = array();

	// Pass 1: HEAD requests in parallel using curl_multi
	$status_codes = array();
	$mh = curl_multi_init();
	$ch_handles = array();

	foreach ($links as $id => $row)
	{
		$response_headers[$id] = '';
		$ch_handles[$id] = curl_init($row['url']);
		curl_setopt($ch_handles[$id], CURLOPT_NOBODY, true);
		curl_setopt($ch_handles[$id], CURLOPT_TIMEOUT, 5);
		curl_setopt($ch_handles[$id], CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch_handles[$id], CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch_handles[$id], CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch_handles[$id], CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch_handles[$id], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36');
		// Capture response headers for Cloudflare detection
		$link_id = $id;
		curl_setopt($ch_handles[$id], CURLOPT_HEADERFUNCTION, function($ch, $header) use (&$response_headers, $link_id) {
			$response_headers[$link_id] .= $header;
			return strlen($header);
		});
		curl_multi_add_handle($mh, $ch_handles[$id]);
	}

	do
	{
		curl_multi_exec($mh, $running);
		if ($running > 0)
			curl_multi_select($mh);
	} while ($running > 0);

	// Collect HEAD results and clean up
	$retry_ids = array();
	foreach ($ch_handles as $id => $ch)
	{
		$status_codes[$id] = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_multi_remove_handle($mh, $ch);
		curl_close($ch);

		// Mark for GET retry if HEAD returned 405/403/0
		if ($status_codes[$id] == 405 || $status_codes[$id] == 403 || $status_codes[$id] == 0)
			$retry_ids[] = $id;
	}
	curl_multi_close($mh);

	// Pass 2: GET retries in parallel for links that need it
	if (!empty($retry_ids))
	{
		$mh = curl_multi_init();
		$ch_handles = array();

		foreach ($retry_ids as $id)
		{
			$response_headers[$id] = '';
			$ch_handles[$id] = curl_init($links[$id]['url']);
			curl_setopt($ch_handles[$id], CURLOPT_TIMEOUT, 8);
			curl_setopt($ch_handles[$id], CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch_handles[$id], CURLOPT_MAXREDIRS, 5);
			curl_setopt($ch_handles[$id], CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch_handles[$id], CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch_handles[$id], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36');
			$link_id = $id;
			curl_setopt($ch_handles[$id], CURLOPT_HEADERFUNCTION, function($ch, $header) use (&$response_headers, $link_id) {
				$response_headers[$link_id] .= $header;
				return strlen($header);
			});
			curl_multi_add_handle($mh, $ch_handles[$id]);
		}

		do
		{
			curl_multi_exec($mh, $running);
			if ($running > 0)
				curl_multi_select($mh);
		} while ($running > 0);

		foreach ($ch_handles as $id => $ch)
		{
			$status_codes[$id] = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_multi_remove_handle($mh, $ch);
			curl_close($ch);
		}
		curl_multi_close($mh);
	}

	// Build results and update DB
	$results = array();
	$checked_time = time();

	foreach ($links as $id => $row)
	{
		$status_code = $status_codes[$id];

		// Detect Cloudflare blocks — check for server: cloudflare or cf-ray header
		$is_cloudflare = false;
		if (($status_code == 403 || $status_code == 503) && !empty($response_headers[$id]))
		{
			$hdrs = strtolower($response_headers[$id]);
			if (strpos($hdrs, 'server: cloudflare') !== false || strpos($hdrs, 'cf-ray:') !== false)
				$is_cloudflare = true;
		}

		// Determine status text
		if ($is_cloudflare)
			$status_text = 'Cloudflare Block';
		elseif ($status_code == 200)
			$status_text = 'OK';
		elseif ($status_code == 301 || $status_code == 302)
			$status_text = 'Redirect';
		elseif ($status_code == 404)
			$status_text = 'Not Found';
		elseif ($status_code >= 400 && $status_code < 500)
			$status_text = 'Client Error';
		elseif ($status_code >= 500)
			$status_text = 'Server Error';
		else
			$status_text = 'Timeout/Unreachable';

		// Cloudflare blocks are not truly bad — don't increment fail counter
		$is_bad = !$is_cloudflare && ($status_code == 0 || $status_code >= 400);

		if ($is_bad)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}links
				SET check_fails = check_fails + 1, last_checked = {int:checked}, last_status = {int:status}
				WHERE ID_LINK = {int:id_link}',
				array(
					'checked' => $checked_time,
					'status' => $status_code,
					'id_link' => $id,
				)
			);
		}
		else
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}links
				SET check_fails = 0, last_checked = {int:checked}, last_status = {int:status}
				WHERE ID_LINK = {int:id_link}',
				array(
					'checked' => $checked_time,
					'status' => $status_code,
					'id_link' => $id,
				)
			);
		}

		$results[] = array(
			'id' => $id,
			'url' => $row['url'],
			'title' => $row['title'],
			'catname' => $row['catname'],
			'status_code' => $status_code,
			'status_text' => $status_text,
			'check_fails' => $is_bad ? $row['check_fails'] + 1 : 0,
			'member_id' => $row['ID_MEMBER'],
			'cloudflare' => $is_cloudflare,
		);
	}

	header('Content-Type: application/json');
	echo json_encode(array('results' => $results));
	obExit(false);
	die();
}

function LinksNotifyBadLinks()
{
	global $smcFunc, $sourcedir, $txt;

	isAllowedTo('links_manage_cat');
	checkSession('post');

	if (empty($_POST['notify_ids']) || !is_array($_POST['notify_ids']))
		redirectexit('action=admin;area=links;sa=checklinks');

	$ids = array();
	foreach ($_POST['notify_ids'] as $id)
		$ids[] = (int) $id;

	if (empty($ids))
		redirectexit('action=admin;area=links;sa=checklinks');

	// Get link details for selected IDs
	$dbresult = $smcFunc['db_query']('', '
		SELECT l.ID_LINK, l.title, l.url, l.ID_MEMBER, l.last_status
		FROM {db_prefix}links AS l
		WHERE l.ID_LINK IN ({array_int:ids})',
		array(
			'ids' => $ids,
		)
	);

	// Group by member
	$by_member = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		if (!empty($row['ID_MEMBER']))
			$by_member[$row['ID_MEMBER']][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);

	if (!empty($by_member))
	{
		require_once($sourcedir . '/Subs-Post.php');

		foreach ($by_member as $member_id => $links)
		{
			$body = $txt['smflinks_pm_body_intro'] . "\n\n";
			foreach ($links as $link)
			{
				$status_code = (int) $link['last_status'];
				if ($status_code == 404)
					$status_label = '404 Not Found';
				elseif ($status_code >= 400 && $status_code < 500)
					$status_label = $status_code . ' Client Error';
				elseif ($status_code >= 500)
					$status_label = $status_code . ' Server Error';
				else
					$status_label = 'Timeout/Unreachable';

				$body .= '- ' . $link['title'] . ' - ' . $link['url'] . ' (Status: ' . $status_label . ")\n";
			}
			$body .= "\n" . $txt['smflinks_pm_body_outro'];

			sendpm(
				array('to' => array($member_id)),
				$txt['smflinks_pm_subject'],
				$body,
				false,
				null,
				0
			);
		}
	}

	redirectexit('action=admin;area=links;sa=checklinks');
}

function LinksDeleteBadLinks()
{
	global $smcFunc, $txt;

	isAllowedTo('links_manage_cat');
	checkSession('post');

	if (empty($_POST['delete_ids']) || !is_array($_POST['delete_ids']))
		redirectexit('action=admin;area=links;sa=checklinks');

	$ids = array();
	foreach ($_POST['delete_ids'] as $id)
		$ids[] = (int) $id;

	if (!empty($ids))
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}links
			WHERE ID_LINK IN ({array_int:ids})',
			array(
				'ids' => $ids,
			)
		);
	}

	redirectexit('action=admin;area=links;sa=checklinks');
}

function CheckDisallowedDomain($url)
{
	global $smcFunc;

	$host = parse_url($url, PHP_URL_HOST);
	if (empty($host))
		return false;

	// Strip www. prefix
	$host = strtolower($host);
	if (strpos($host, 'www.') === 0)
		$host = substr($host, 4);

	// Get all disallowed domains
	$dbresult = $smcFunc['db_query']('', '
		SELECT domain
		FROM {db_prefix}links_disallowed_domains'
	);

	$blocked = false;
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$disallowed = $row['domain'];
		// Exact match or subdomain match (host ends with .disallowed)
		if ($host === $disallowed || substr($host, -(strlen($disallowed) + 1)) === '.' . $disallowed)
		{
			$blocked = true;
			break;
		}
	}
	$smcFunc['db_free_result']($dbresult);

	return $blocked;
}

function LinksDisallowedDomains()
{
	global $context, $mbname, $txt, $smcFunc;

	isAllowedTo('links_manage_cat');
	DoLinksAdminTabs();

	$context['page_title'] = $mbname . $txt['smflinks_title'] . ' - ' . $txt['smflinks_disallowed_domains'];
	$context['sub_template'] = 'disallowed_domains';

	// Get all disallowed domains
	$dbresult = $smcFunc['db_query']('', '
		SELECT domain
		FROM {db_prefix}links_disallowed_domains
		ORDER BY domain ASC'
	);

	$context['disallowed_domains'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$context['disallowed_domains'][] = $row['domain'];
	$smcFunc['db_free_result']($dbresult);
}

function LinksDisallowedDomains2()
{
	global $smcFunc, $txt;

	isAllowedTo('links_manage_cat');
	checkSession('post');

	if (!empty($_POST['domain']))
		$domain = trim(strtolower($_POST['domain']));
	else
		$domain = '';

	if (empty($domain))
		fatal_error($txt['smflinks_err_no_domain'], false);

	// Strip protocol and www prefix if user included them
	$domain = preg_replace('#^https?://#', '', $domain);
	$domain = preg_replace('#^www\.#', '', $domain);
	// Strip trailing slash
	$domain = rtrim($domain, '/');

	// INSERT IGNORE to handle duplicates
	$smcFunc['db_query']('', '
		INSERT IGNORE INTO {db_prefix}links_disallowed_domains (domain)
		VALUES ({string:domain})',
		array(
			'domain' => $domain,
		)
	);

	redirectexit('action=admin;area=links;sa=disalloweddomains');
}

function LinksDisallowedDomainsDelete()
{
	global $smcFunc, $txt;

	isAllowedTo('links_manage_cat');
	checkSession('get');

	if (!empty($_REQUEST['domain']))
		$domain = $_REQUEST['domain'];
	else
		$domain = '';

	if (empty($domain))
		fatal_error($txt['smflinks_err_no_domain'], false);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}links_disallowed_domains
		WHERE domain = {string:domain}
		LIMIT 1',
		array(
			'domain' => $domain,
		)
	);

	redirectexit('action=admin;area=links;sa=disalloweddomains');
}