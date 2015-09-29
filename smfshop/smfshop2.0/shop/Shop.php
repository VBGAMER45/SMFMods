<?php
/**********************************************************************************
* Shop.php                                                                        *
* The main shop page                                                              *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.1 (Build 14)                              *
* $Date:: 2009-11-14 19:26:55 +1100 (Thu, 14 Nov 2009)                          $ *
* $Id:: Shop.english.php 79 2009-11-14 08:26:55Z daniel15                       $ *
* Software by:                DanSoft Australia (http://www.dansoftaustralia.net/)*
* Copyright 2009 by:          vbgamer45 (http://www.smfhacks.com)                 *
* Copyright 2005-2007 by:     DanSoft Australia (http://www.dansoftaustralia.net/)*
* Support, News, Updates at:  http://www.dansoftaustralia.net/                    *
*                                                                                 *
* Forum software by:          Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version of the license can always be found at                        *
* http://www.simplemachines.org.                                                  *
**********************************************************************************/

// If file is not called by SMF, don't let them get anywhere!
if (!defined('SMF'))
	die('Hacking attempt...');

function Shop()
{
	global $context, $modSettings, $scripturl, $smcFunc;
	global $txt, $item_info, $boardurl, $sourcedir, $user_info;
	
	// Various things we need
	require_once($sourcedir . '/shop/Shop-Subs.php');  // Shop stuff
	include_once($sourcedir . '/Subs-Post.php');       // Sending PM's 
	include_once($sourcedir . '/Subs-Auth.php');       // 'Find Members' stuff
	loadLanguage('PersonalMessage', '', false);	
	
	// During testing, caching was causing many problems. So, we try to disable the caching here.
	header("Expires: Fri, 1 Jun 1990 00:00:00 GMT"); // My birthday ;)
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Pragma: no-cache");

	// Load the SMFShop language strings
	loadLanguage('Shop');
	// And the SMFShop template
	loadTemplate('Shop');
	// Check if guest is trying to access the shop. If so, give them an error 
	is_not_guest($txt['shop_guest_message']);
	// Are they allowed here?
	isAllowedTo('shop_main');
	
	// We want to use the SMFShop 'layer' (header and footer)
	$context['template_layers'][] = 'shop';

	// Set the link tree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop',
		'name' => $txt['shop'],
	);
		
	// !!! Rewrite to properly use subactions!
	
	// If no action passed, assume home
	if (empty($_GET['do']))
		$_GET['do'] = 'home';	
	
	// Are we home?
	if ($_GET['do'] == "home")
	{
		// Add this to the link tree
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=shop',
			'name' => $txt['shop'] . ' Home',
		); 

		// 10 richest people (pocket)
		// Start with empty list
		$context['shop_richest'] = array();
		// Get the richest people
		$result = $smcFunc['db_query']('', "
			SELECT id_member, real_name, money
			FROM {db_prefix}members
			ORDER BY money DESC, real_name
			LIMIT 10", array());
		// Loop through all results
		
		while ($row = $smcFunc['db_fetch_assoc']($result))
			// And add them to the list
			$context['shop_richest'][] = array(
				'ID_MEMBER' => $row['id_member'],
				'realName' => $row['real_name'],
				'money' => $row['money']
			);
			
		$smcFunc['db_free_result']($result);

		// 10 richest people (pocket)
		// Again, let's start with an empty list
		$context['shop_richestBank'] = array();
		// Get the richest people
		$result = $smcFunc['db_query']('', "
			SELECT id_member, real_name, moneyBank
			FROM {db_prefix}members
			ORDER BY moneyBank DESC, real_name
			LIMIT 10", array());
		
		// Loop through all results
		while ($row = $smcFunc['db_fetch_assoc']($result))
			// Add them to the list
			$context['shop_richestBank'][] = array(
				'ID_MEMBER' => $row['id_member'],
				'realName' => $row['real_name'],
				'moneyBank' => $row['moneyBank']
			);
			
		$smcFunc['db_free_result']($result);

		// The shop action (for highlighing the current action on the left-hand menu)
		$context['shop_do'] = 'main';
		// Set the page title
		$context['page_title'] = $txt['shop'];
		// Main template for the main page :)	
		$context['sub_template'] = 'main';

	// All of this code is in external files, to keep this Shop.php file relatively small :)
	// Some of the smaller, miscellaneous functions are still in this file.
	}
	elseif (substr($_GET['do'], 0, 3) == "buy") // Buy an item
		require "Shop-Buy.php";
	elseif (substr($_GET['do'], 0, 3) == "inv") // View inventory
		require "Shop-Inventory.php";
	elseif (substr($_GET['do'], 0, 4) == "send") //Send money
		require "Shop-Send.php";
	elseif (substr($_GET['do'], 0, 4) == "bank" || $_GET['do'] == "deposit" || $_GET['do'] == "withdraw") //Bank stuff
		require "Shop-Bank.php";
	elseif (substr($_GET['do'], 0, 5) == "trade") //Trade Centre
		require "Shop-Trade.php";
		
	// View all members by money in their pocket
	elseif($_GET['do'] == "viewall")
	{
		// Add to the link tree
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=shop;do=viewall',
			'name' => $txt['shop_view_all'],
		);

		// Start with an empty list
		$context['shop_members'] = array();
		// Get actual list of people with money != 0
		$result = $smcFunc['db_query']('', "
			SELECT real_name, money
			FROM {db_prefix}members
			WHERE money <> 0
			ORDER BY money DESC
			", array());

		// Loop through results
		while ($row = $smcFunc['db_fetch_assoc']($result))
			// Add user to the list
			$context['shop_members'][] = array(
				'realName' => $row['real_name'],
				'money' => $row['money']
			);
		$smcFunc['db_free_result']($result);
		
		// Set the page title
		$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_view_all'];
		// Use the viewAllMembers template
		$context['sub_template'] = 'viewAllMembers';

	// Similar to above, except for money in the bank
	} elseif($_GET['do'] == "viewallBank") {

		// Add to the link tree
		$context['linktree'][] = array(
			'url' => "$scripturl?action=shop;do=viewallBank",
			'name' => $txt['shop_view_all2'],
		);

		// Start with an empty list
		$context['shop_members'] = array();
		// Get actual list of people (moneyBank != 0)
		$result = $smcFunc['db_query']('', "
			SELECT real_name, moneyBank
			FROM {db_prefix}members
			WHERE moneyBank <> 0
			ORDER BY moneyBank DESC
			", array());

		// Loop through results
		while ($row = $smcFunc['db_fetch_assoc']($result))
			// Add them to the list
			$context['shop_members'][] = array(
				'realName' => $row['real_name'],
				'money' => $row['moneyBank']
			);
		$smcFunc['db_free_result']($result);
		
		// Set the page title
		$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_view_all2'];
		// Use the viewAllMembers template
		$context['sub_template'] = 'viewAllMembers';
	}
	// 'Who Owns This Item' option
	elseif($_GET['do'] == 'owners')
	{
		// Make sure item ID is a number
		$_GET['id'] = (int) $_GET['id'];
		
		// Get item name
		$result = $smcFunc['db_query']('', "
			SELECT name
			FROM {db_prefix}shop_items
			WHERE id = {int:id}
			LIMIT 1",
			array(
				'id' => $_GET['id']
			));
							 
		$row = $smcFunc['db_fetch_assoc']($result);
		$smcFunc['db_free_result']($result);

		// Add to the linktree
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=shop;do=owners;id=' . $_GET['id'],
			'name' => $txt['shop_owners'] . ' - ' . $row['name'],
		);
							 
		// Now, get the actual usernames
		// If user has more than one of this item, only count them once
		$result = $smcFunc['db_query']('', "
			SELECT DISTINCT m.real_name
			FROM {db_prefix}shop_inventory AS inv
			INNER JOIN {db_prefix}shop_items AS it
			INNER JOIN {db_prefix}members AS m
			WHERE
				inv.itemid = {int:id} AND
				m.ID_MEMBER = inv.ownerid",
			array(
				'id' => $_GET['id']
			));
							 
		// Add the header to the message (xx users own the item xx)
		// TODO: Fix the ugly code!
		$context['shop_buy_message'] = '
						<b>' . sprintf($txt['shop_users_own_item'], (int) mysql_num_rows($result), $row['name']) . '</b>
						<ul>';
									 
		// Loop through results
		while ($rowUser = $smcFunc['db_fetch_assoc']($result))
			// Add user to the list
			$context['shop_buy_message'] .= '
							<li>' . $rowUser['real_name'] . '</li>';
		$smcFunc['db_free_result']($result);
		
		// Close the list
		$context['shop_buy_message'] .= '
						</ul>';
		// Set the page title
		$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_owners'] . ' - ' . $row['name'];
		// Use the message template
		$context['sub_template'] = 'message';
	}
	// Otherwise... What do you want us to do?
	else
	{
		fatal_error('ERROR: The \'do\' action you passed was not valid!');
	}
}
?>
