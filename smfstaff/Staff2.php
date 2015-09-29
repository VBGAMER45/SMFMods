<?php
/*
SMF Staff Page
Version 1.6
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function Staff()
{
	// Load the main staff template
	loadtemplate('Staff2');
	
	// Load the language files
	if (loadlanguage('Staff') == false)
		loadLanguage('Staff','english');
		
	// Staff page actions
	$subActions = array(
		'admin' => 'StaffSettings',
		'admin2' => 'StaffSettings2',
		'add' => 'AddGroup',
		'delete' => 'DeleteGroup',
		'catup' => 'CatUp',
		'catdown' => 'CatDown',
	);

	// Follow the sa or just go to main staff page.
	if (!empty($subActions[@$_GET['sa']]))
		$subActions[$_GET['sa']]();
	else
		ViewStaffPage();	
	
}

function ViewStaffPage()
{
	global $context, $mbname, $txt, $modSettings, $smcFunc, $user_info, $scripturl;
	
	// Check if the current user can view the staff list
	isAllowedTo('view_stafflist');

	// Load the main staff template
	$context['sub_template']  = 'main';

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['smfstaff_stafflist'];	
	
	// Link tree
	$context['linktree'][] = array(
					'url' => $scripturl . '?action=staff',
					'name' => $txt['smfstaff_stafflist']
				);
	
	// Get all the Groups
		$groups = array();
		// Does all the real work.
		$query = $smcFunc['db_query']('', "
		SELECT 
			m.ID_GROUP, m.group_name, m.online_color, s.roworder 
		FROM ({db_prefix}membergroups as m,{db_prefix}staff as s) 
		WHERE m.ID_GROUP = s.ID_GROUP AND m.min_posts = -1 
		ORDER BY s.roworder");
		while ($row = $smcFunc['db_fetch_assoc']($query))
		{

			$groups[$row['ID_GROUP']]  = array(
				'id' => $row['ID_GROUP'],
				'name' => $row['group_name'],
				'color' => empty($row['online_color']) ? '' : $row['online_color'],
				'roworder' => $row['roworder'],
			);
		}
		$smcFunc['db_free_result']($query);
		
		$context['smfstaff_groups'] = $groups;
		
		// Get the Users in the Group
		
		$users = array();
		foreach ($groups as $id => $data)
		{

			// Now get all the user's
			$query2 = $smcFunc['db_query']('', "
			SELECT 
				ID_GROUP, avatar, ID_MEMBER, real_name, email_address, hide_email, last_login, date_registered, ICQ, AIM, YIM, MSN
			FROM {db_prefix}members
			 WHERE ID_GROUP = " . $data['id'] . " OR FIND_IN_SET(" . $data['id'] . ", additional_groups) ");
			while ($row2 = $smcFunc['db_fetch_assoc']($query2))
			{
				$users[$data['id']][] = array(
				'ID_GROUP' => $row2['ID_GROUP'],
				'avatar' => $row2['avatar'],
				'ID_MEMBER' => $row2['ID_MEMBER'],
				'realName' => $row2['real_name'],
				'emailAddress' => $row2['email_address'],
				'hideEmail' => $row2['hide_email'],
				'lastLogin' => $row2['last_login'],
				'dateRegistered' => $row2['date_registered'],
				'ICQ' => $row2['ICQ'],
				'AIM' => $row2['AIM'],
				'MSN' => $row2['MSN'],
				'YIM' => $row2['YIM'],
				);
				
				
				
			}
			$smcFunc['db_free_result']($query2);
			
		}
		
		$context['smfstaff_users'] = $users;
	
	
	// Show Local mods?
	if ($modSettings['smfstaff_showlocalmods'])
	{
			//Show local mod's
			$localmods = array();
			//Stores the boards that member is a moderateor of
			$bmods = array();

			$query3 = $smcFunc['db_query']('', "
			SELECT 
				m.ID_GROUP, m.avatar, m.ID_MEMBER, m.real_name, m.last_login, m.date_registered, m.ICQ, m.AIM, m.YIM, m.MSN, m.hide_email, m.email_address, b.name, b.ID_BOARD
			FROM ({db_prefix}members AS m, {db_prefix}moderators AS o, {db_prefix}boards AS b) 
			WHERE o.ID_MEMBER = m.ID_MEMBER AND b.ID_BOARD = o.ID_BOARD AND $user_info[query_see_board]");
			
				while ($row3 = $smcFunc['db_fetch_assoc']($query3))
				{
					@$bmods[$row3['ID_MEMBER']] .= '<a href="' . $scripturl . '?board=' . $row3['ID_BOARD'] . '">' . $row3['name']  . '</a><br />';

					$localmods[$row3['ID_MEMBER']]  = array(
					'id' => $row3['ID_MEMBER'],
					'realName' => $row3['real_name'],
					'lastLogin' => $row3['last_login'],
					'dateRegistered' => $row3['date_registered'],
					'hideEmail'  => $row3['hide_email'],
					'emailAddress' => $row3['email_address'],
					'avatar' => $row['avatar'],
					'ICQ' => $row3['ICQ'],
					'YIM' => $row3['YIM'],
					'AIM' => $row3['AIM'],
					'MSN' => $row3['MSN'],
					'forums' =>  $bmods[$row3['ID_MEMBER']],
					);

				}
				
				$smcFunc['db_free_result']($query3);
				
				$context['smfstaff_localmods'] = $localmods;
	}
	
}

function StaffSettings()
{
	global $context, $mbname, $txt, $smcFunc;
	
	isAllowedTo('admin_forum');

	DoStaffAdminTabs();
	
	$context['sub_template']  = 'adminset';
	
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['smfstaff_staffsetting'];	
	
	// Get all Staff Groups
	$query2 = $smcFunc['db_query']('', "
	SELECT 
		ID_GROUP
	FROM {db_prefix}staff");
	$staff_groups = array();
	while ($row = $smcFunc['db_fetch_assoc']($query2))
		$staff_groups[ $row['ID_GROUP']] = array('ID_GROUP' => $row['ID_GROUP']);

	$smcFunc['db_free_result']($query2);
	
	$groups = array();
	$groups2 = array();
	
	$query = $smcFunc['db_query']('', "
	SELECT 
		m.ID_GROUP, m.group_name, m.online_color
	FROM {db_prefix}membergroups as m WHERE m.min_posts = -1 
	ORDER BY m.group_name");
		while ($row = $smcFunc['db_fetch_assoc']($query))
		{
			// Check if the group is not listed already.
			if (!isset($staff_groups[$row['ID_GROUP']]))
			{
				$groups[$row['ID_GROUP']]  = array(
					'id' => $row['ID_GROUP'],
					'name' => $row['group_name'],
					'color' => empty($row['online_color']) ? '' : $row['online_color'],
				);
			}
			else 
			{
				$groups2[$row['ID_GROUP']]  = array(
					'id' => $row['ID_GROUP'],
					'name' => $row['group_name'],
					'color' => empty($row['online_color']) ? '' : $row['online_color'],
				);
			}
			
		}
		$smcFunc['db_free_result']($query);
		
		$context['smfstaff_groups'] = $groups;
		
		$context['smfstaff_showgroups'] = $groups2;
}

function StaffSettings2()
{
	isAllowedTo('admin_forum');
	
	// Staff Page settings
	$smfstaff_showavatar = isset($_REQUEST['smfstaff_showavatar']) ? 1 : 0;
	$smfstaff_showlastactive = isset($_REQUEST['smfstaff_showlastactive']) ? 1 : 0;
	$smfstaff_showdateregistered = isset($_REQUEST['smfstaff_showdateregistered']) ? 1 : 0;
	$smfstaff_showcontactinfo = isset($_REQUEST['smfstaff_showcontactinfo']) ? 1 : 0;
	$smfstaff_showlocalmods = isset($_REQUEST['smfstaff_showlocalmods']) ? 1 : 0;

	// Save the setting information
	updateSettings(
	array('smfstaff_showavatar' => $smfstaff_showavatar,
	'smfstaff_showlastactive' => $smfstaff_showlastactive,
	'smfstaff_showdateregistered' => $smfstaff_showdateregistered,
	'smfstaff_showcontactinfo' => $smfstaff_showcontactinfo,
	'smfstaff_showlocalmods' => $smfstaff_showlocalmods,

	));

	
	// Redirect to Staff settings page
	redirectexit('action=admin;area=staff;sa=admin');
}

function AddGroup()
{
	global $smcFunc, $txt;
	
	// Check permissions
	isAllowedTo('admin_forum');

	// Get the Group ID
	$id = (int) $_REQUEST['id'];
	
	// Get Last Group ID
	$query = $smcFunc['db_query']('', "
	SELECT 
		roworder 
	FROM {db_prefix}staff 
	ORDER BY roworder DESC LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($query);
	
	$lastgroupid = $row['roworder'];
	$lastgroupid++;
	$smcFunc['db_free_result']($query);
	
	// Check if that group exists already
	$query = $smcFunc['db_query']('', "
	SELECT 
		ID_GROUP 
	FROM {db_prefix}staff 
	WHERE ID_GROUP = $id LIMIT 1");
	if ($smcFunc['db_affected_rows']() != 0)
	{
		// The group already exists!
		
		$smcFunc['db_free_result']($query);
		
		fatal_error($txt['smfstaff_errgroupexists'],false);
	}
	else 
	{	
		$row = $smcFunc['db_fetch_assoc']($query);
		$smcFunc['db_free_result']($query);
		
		// Insert the Group
		$smcFunc['db_query']('', "INSERT INTO {db_prefix}staff
			(ID_GROUP, roworder)
		VALUES ($id, $lastgroupid)");	
	}
	
	redirectexit('action=admin;area=staff;sa=admin');
}

function DeleteGroup()
{
	global $smcFunc;
	
	// Check Admin Permission
	isAllowedTo('admin_forum');
	
	// Get the Group ID
	$id = (int) $_REQUEST['id'];
	
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}staff 
			WHERE ID_GROUP = " . $id);
	
	// Fix the row orders
	ReAdjustGroupOrder();
	
	if (isset($_REQUEST['ret']))
		// Return to staff page
		redirectexit('action=staff');
	else 
		redirectexit('action=admin;area=staff;sa=admin');

	
}

function CatUp()
{
	global $smcFunc, $txt;
	// Check if they are allowed to manage the forum
	isAllowedTo('admin_forum');

	// Get the cat id
	$cat = (int) @$_REQUEST['id'];
	// Check if there is a category above it
	// First get our row order
	$dbresult1 = $smcFunc['db_query']('', "
	SELECT 
		roworder 
	FROM {db_prefix}staff 
	WHERE ID_GROUP = $cat");
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o--;

	$smcFunc['db_free_result']($dbresult1);
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_GROUP, roworder 
	FROM {db_prefix}staff 
	WHERE roworder = $o");
	if ($smcFunc['db_affected_rows']()== 0)
		fatal_error($txt['smfstaff_nocatabove'],false);
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);


	// Swap the order Id's
	$smcFunc['db_query']('', "UPDATE {db_prefix}staff
		SET roworder = $oldrow WHERE ID_GROUP = " .$row2['ID_GROUP']);

	$smcFunc['db_query']('', "UPDATE {db_prefix}staff
		SET roworder = $o WHERE ID_GROUP = $cat");


	$smcFunc['db_free_result']($dbresult);

	// Redirect to index to view the staff order
	redirectexit('action=staff');
}

function CatDown()
{
	global $smcFunc, $txt;

	// Check if they are allowed to manage the forum
	isAllowedTo('admin_forum');

	// Get the cat id
	$cat = (int) @$_REQUEST['id'];
	// Check if there is a category below it
	// First get our row order
	$dbresult1 = $smcFunc['db_query']('', "
	SELECT 
		roworder 
	FROM {db_prefix}staff 
	WHERE ID_GROUP = $cat");
	$row = $smcFunc['db_fetch_assoc']($dbresult1);
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o++;
	$smcFunc['db_free_result']($dbresult1);
	
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		ID_GROUP, roworder 
	FROM {db_prefix}staff 
	WHERE roworder = $o");
	if ($smcFunc['db_affected_rows']()== 0)
		fatal_error($txt['smfstaff_nocatbelow'],false);
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);


	// Swap the order Id's
	$smcFunc['db_query']('', "UPDATE {db_prefix}staff
		SET roworder = $oldrow WHERE ID_GROUP = " .$row2['ID_GROUP']);

	$smcFunc['db_query']('', "UPDATE {db_prefix}staff
		SET roworder = $o WHERE ID_GROUP = $cat");


	$smcFunc['db_free_result']($dbresult);


	// Redirect to index to view the staff page
	redirectexit('action=staff');
}

function ReAdjustGroupOrder()
{
	global $smcFunc;
	
	$query = $smcFunc['db_query']('', "
	SELECT 
		ID_GROUP 
	FROM {db_prefix}staff 
	ORDER BY roworder");
	$groups = array();
	while ($row = $smcFunc['db_fetch_assoc']($query))
		$groups[] = array('ID_GROUP' =>$row['ID_GROUP']);

	$smcFunc['db_free_result']($query);
	
	$roworder = 0;
	foreach ($groups as $id => $data)
	{
	
		$smcFunc['db_query']('', "UPDATE {db_prefix}staff
		SET roworder = $roworder WHERE ID_GROUP = " . $data['ID_GROUP']);
		$roworder++;	
	}
}

function DoStaffAdminTabs($overrideSelected = '')
{
	global $context, $txt, $scripturl, $smcFunc;
	
	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['smfstaff_admin'],
			'description' => '',
			'tabs' => array(
				'admin' => array(
					'description' => '',
					'label' => '',
				),
				
			),
		);	
	


}

?>