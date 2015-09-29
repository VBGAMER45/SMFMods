<?php
/*
SMF Staff Page
Version 1.5
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function Staff()
{
	
	//Load the main staff template
	loadtemplate('Staff');
	
	//Load the language files
	if (loadlanguage('Staff') == false)
		loadLanguage('Staff','english');
		
		
	//Staff page actions
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
	global $context, $mbname, $txt, $modSettings, $db_prefix, $user_info, $scripturl;
	// Check if the current user can view the staff list
	isAllowedTo('view_stafflist');

	// Load the main staff template
	$context['sub_template']  = 'main';

	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['smfstaff_stafflist'];	
	
	
	// Get all the Groups
		$groups = array();
		//Does all the real work.
		$query = db_query("SELECT m.ID_GROUP, m.groupName, m.onlineColor, s.roworder
			FROM ({$db_prefix}membergroups as m,{$db_prefix}staff as s)
			WHERE m.ID_GROUP = s.ID_GROUP AND m.minPosts = -1 
			ORDER BY s.roworder", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
		{

			$groups[$row['ID_GROUP']]  = array(
				'id' => $row['ID_GROUP'],
				'name' => $row['groupName'],
				'color' => empty($row['onlineColor']) ? '' : $row['onlineColor'],
				'roworder' => $row['roworder'],
			);
		}
		mysql_free_result($query);
		
		$context['smfstaff_groups'] = $groups;
		
		// Get the Users in the Group
		
		$users = array();
		
		foreach ($groups as $id => $data)
		{

			//Now get all the user's
			$query2 = db_query("SELECT ID_GROUP, avatar, ID_MEMBER, realName, emailAddress, hideEmail, lastLogin, dateRegistered, ICQ, AIM, YIM, MSN
			FROM {$db_prefix}members
			 WHERE ID_GROUP = " . $data['id'] . " OR FIND_IN_SET(" . $data['id'] . ", additionalGroups) ", __FILE__, __LINE__);
			while ($row2 = mysql_fetch_assoc($query2))
			{
				$users[$data['id']][] = array(
				'ID_GROUP' => $row2['ID_GROUP'],
				'avatar' => $row2['avatar'],
				'ID_MEMBER' => $row2['ID_MEMBER'],
				'realName' => $row2['realName'],
				'emailAddress' => $row2['emailAddress'],
				'hideEmail' => $row2['hideEmail'],
				'lastLogin' => $row2['lastLogin'],
				'dateRegistered' => $row2['dateRegistered'],
				'ICQ' => $row2['ICQ'],
				'AIM' => $row2['AIM'],
				'MSN' => $row2['MSN'],
				'YIM' => $row2['YIM'],
				);
				
				
				
			}
			mysql_free_result($query2);
			
		}
		
		$context['smfstaff_users'] = $users;
	
	
	// Show Local mods?
	if ($modSettings['smfstaff_showlocalmods'])
	{

			//Show local mod's
			$localmods = array();
			//Stores the boards that member is a moderateor of
			$bmods = array();

			$query3 = db_query("SELECT m.ID_GROUP, m.avatar, m.ID_MEMBER, m.realName, m.lastLogin, m.dateRegistered, m.ICQ, m.AIM, m.YIM, m.MSN, m.hideEmail, m.emailAddress, b.name, b.ID_BOARD
			FROM ({$db_prefix}members AS m, {$db_prefix}moderators AS o, {$db_prefix}boards AS b) WHERE o.ID_MEMBER = m.ID_MEMBER AND b.ID_BOARD = o.ID_BOARD AND $user_info[query_see_board]", __FILE__, __LINE__);
			
		
				while ($row3 = mysql_fetch_assoc($query3))
				{
					@$bmods[$row3['ID_MEMBER']] .= '<a href="' . $scripturl . '?board=' . $row3['ID_BOARD'] . '">' . $row3['name']  . '</a><br />';

						$localmods[$row3['ID_MEMBER']]  = array(
					'id' => $row3['ID_MEMBER'],
					'realName' => $row3['realName'],
					'lastLogin' => $row3['lastLogin'],
					'dateRegistered' => $row3['dateRegistered'],
					'hideEmail'  => $row3['hideEmail'],
					'emailAddress' => $row3['emailAddress'],
					'avatar' => $row['avatar'],
					'ICQ' => $row3['ICQ'],
					'YIM' => $row3['YIM'],
					'AIM' => $row3['AIM'],
					'MSN' => $row3['MSN'],
					'forums' =>  $bmods[$row3['ID_MEMBER']],
					);

				}
				
				mysql_free_result($query3);
				
				
				$context['smfstaff_localmods'] = $localmods;
				
	}
	
}
function StaffSettings()
{
	global $context, $mbname, $txt, $db_prefix;
	
	isAllowedTo('admin_forum');
	
	adminIndex('staff_settings');
	
	$context['sub_template']  = 'adminset';
	
	// Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['smfstaff_staffsetting'];	
	
	// Get all Staff Groups
	$query2 = db_query("
	SELECT 
		ID_GROUP
	FROM {$db_prefix}staff", __FILE__, __LINE__);
	$staff_groups = array();
	while ($row = mysql_fetch_assoc($query2))
	{
		$staff_groups[ $row['ID_GROUP']] = array('ID_GROUP' => $row['ID_GROUP']);
		
	}
	mysql_free_result($query2);
	
	$groups = array();
	$groups2 = array();
	
	$query = db_query("
	SELECT 
		m.ID_GROUP, m.groupName, m.onlineColor 
	FROM {$db_prefix}membergroups as m WHERE m.minPosts = -1 
			ORDER BY m.groupName", __FILE__, __LINE__);
		while ($row = mysql_fetch_assoc($query))
		{

			// Check if the group is not listed already.
			if(!isset($staff_groups[$row['ID_GROUP']]))
			{
				$groups[$row['ID_GROUP']]  = array(
					'id' => $row['ID_GROUP'],
					'name' => $row['groupName'],
					'color' => empty($row['onlineColor']) ? '' : $row['onlineColor'],
				);
			}
			else 
			{
				$groups2[$row['ID_GROUP']]  = array(
					'id' => $row['ID_GROUP'],
					'name' => $row['groupName'],
					'color' => empty($row['onlineColor']) ? '' : $row['onlineColor'],
				);
			}
			
		}
		mysql_free_result($query);
		
		$context['smfstaff_groups'] = $groups;
		
		$context['smfstaff_showgroups'] = $groups2;
}

function StaffSettings2()
{
	isAllowedTo('admin_forum');
	
	// Staff Page settings
	$smfstaff_showavatar = isset($_REQUEST['smfstaff_showavatar']);
	$smfstaff_showlastactive = isset($_REQUEST['smfstaff_showlastactive']);
	$smfstaff_showdateregistered = isset($_REQUEST['smfstaff_showdateregistered']);
	$smfstaff_showcontactinfo = isset($_REQUEST['smfstaff_showcontactinfo']);
	$smfstaff_showlocalmods = isset($_REQUEST['smfstaff_showlocalmods']);

    $smfstaff_showavatar ? 1 : 0;
    $smfstaff_showlastactive ? 1 : 0;
    $smfstaff_showdateregistered ? 1 : 0;
    $smfstaff_showcontactinfo ? 1 : 0;
	$smfstaff_showlocalmods ? 1 : 0;

	// Save the setting information
	updateSettings(
	array('smfstaff_showavatar' => $smfstaff_showavatar,
	'smfstaff_showlastactive' => $smfstaff_showlastactive,
	'smfstaff_showdateregistered' => $smfstaff_showdateregistered,
	'smfstaff_showcontactinfo' => $smfstaff_showcontactinfo,
	'smfstaff_showlocalmods' => $smfstaff_showlocalmods,

	));

	
	// Redirect to Staff settings page
	redirectexit('action=staff;sa=admin');
}

function AddGroup()
{
	global $db_prefix, $txt;
	
	// Check permissions
	isAllowedTo('admin_forum');

	// Get the Group ID
	$id = (int) $_REQUEST['id'];
	
	
	// Get Last Group ID
	$query = db_query("SELECT roworder FROM {$db_prefix}staff 
			ORDER BY roworder DESC LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($query);
	
	$lastgroupid = $row['roworder'];
	$lastgroupid++;
	mysql_free_result($query);
	
	// Check if that group exists already
	$query = db_query("
	SELECT 
		ID_GROUP 
	FROM {$db_prefix}staff 
	WHERE ID_GROUP = $id LIMIT 1", __FILE__, __LINE__);
	if (db_affected_rows() != 0)
	{
		//The group already exists!
		
		mysql_free_result($query);
		
		fatal_error($txt['smfstaff_errgroupexists'],false);
	}
	else 
	{	$row = mysql_fetch_assoc($query);
		mysql_free_result($query);
		
		// Insert the Group
		db_query("INSERT INTO {$db_prefix}staff
			(ID_GROUP, roworder)
		VALUES ($id, $lastgroupid)", __FILE__, __LINE__);	
	}
	
	redirectexit('action=staff;sa=admin');
}

function DeleteGroup()
{
	global $db_prefix;
	
	// Check Admin Permission
	isAllowedTo('admin_forum');
	
	// Get the Group ID
	$id = (int) $_REQUEST['id'];
	
	
	db_query("DELETE FROM {$db_prefix}staff 
			WHERE ID_GROUP = " . $id, __FILE__, __LINE__);
	
	// Fix the row orders
	ReAdjustGroupOrder();
	
	if (isset($_REQUEST['ret']))
	{
		// Return to staff page
		redirectexit('action=staff');
	}
	else 
	{
		redirectexit('action=staff;sa=admin');
	}
	
	
	
}

function CatUp()
{
	global $db_prefix, $txt;
	// Check if they are allowed to manage the forum
	isAllowedTo('admin_forum');

	// Get the cat id
	$cat = (int) @$_REQUEST['id'];
	// Check if there is a category above it
	// First get our row order
	$dbresult1 = db_query("SELECT roworder FROM {$db_prefix}staff WHERE ID_GROUP = $cat", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o--;

	mysql_free_result($dbresult1);
	$dbresult = db_query("SELECT ID_GROUP, roworder FROM {$db_prefix}staff WHERE roworder = $o", __FILE__, __LINE__);
	if (db_affected_rows()== 0)
		fatal_error($txt['smfstaff_nocatabove'],false);
	$row2 = mysql_fetch_assoc($dbresult);


	// Swap the order Id's
	db_query("UPDATE {$db_prefix}staff
		SET roworder = $oldrow WHERE ID_GROUP = " .$row2['ID_GROUP'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}staff
		SET roworder = $o WHERE ID_GROUP = $cat", __FILE__, __LINE__);


	mysql_free_result($dbresult);

	// Redirect to index to view the staff order
	redirectexit('action=staff');
}

function CatDown()
{
	global $db_prefix, $txt;

	// Check if they are allowed to manage the forum
	isAllowedTo('admin_forum');

	// Get the cat id
	$cat = (int) @$_REQUEST['id'];
	// Check if there is a category below it
	// First get our row order
	$dbresult1 = db_query("SELECT roworder FROM {$db_prefix}staff WHERE ID_GROUP = $cat", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult1);
	$oldrow = $row['roworder'];
	$o = $row['roworder'];
	$o++;
	mysql_free_result($dbresult1);
	
	$dbresult = db_query("SELECT ID_GROUP, roworder FROM {$db_prefix}staff WHERE roworder = $o", __FILE__, __LINE__);
	if (db_affected_rows()== 0)
		fatal_error($txt['smfstaff_nocatbelow'],false);
	$row2 = mysql_fetch_assoc($dbresult);


	// Swap the order Id's
	db_query("UPDATE {$db_prefix}staff
		SET roworder = $oldrow WHERE ID_GROUP = " .$row2['ID_GROUP'], __FILE__, __LINE__);

	db_query("UPDATE {$db_prefix}staff
		SET roworder = $o WHERE ID_GROUP = $cat", __FILE__, __LINE__);


	mysql_free_result($dbresult);


	// Redirect to index to view the staff page
	redirectexit('action=staff');
}

function ReAdjustGroupOrder()
{
	global $db_prefix;
	
	$query = db_query("
	SELECT 
		ID_GROUP 
	FROM {$db_prefix}staff 
			ORDER BY roworder", __FILE__, __LINE__);
	$groups = array();
	while ($row = mysql_fetch_assoc($query))
	{
			$groups[] = array('ID_GROUP' =>$row['ID_GROUP']);
	}
	mysql_free_result($query);
	
	$roworder = 0;
	foreach ($groups as $id => $data)
	{
	
		db_query("UPDATE {$db_prefix}staff
		SET roworder = $roworder WHERE ID_GROUP = " . $data['ID_GROUP'], __FILE__, __LINE__);
		$roworder++;
				
	}
}
?>