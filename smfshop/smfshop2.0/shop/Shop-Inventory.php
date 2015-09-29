<?php
/**********************************************************************************
* Shop-Inventory.php                                                              *
* Inventory stuff (view inventory, use item, view other's inventory)              *
* -- This is not called directly. The code in here is used in Shop.php --         *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.1 (Build 14)                              *
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
***********************************************************************************/

// If this script isn't called by SMF, it's bad!
if (!defined('SMF'))
	die('Hacking attempt...');

// Viewing your inventory
if ($_GET['do'] == 'inv')
{
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=inv',
		'name' => $txt['shop_yourinv'],
	);

	// Initialise the context variable we use, with some settings
	$context['shop_inv'] = array(
		'last_col_type' => 'inv',
		'last_col_header' => 'Paid/Use'
	);

	// Get the number of items available
	$result = $smcFunc['db_query']('', "
		SELECT COUNT(id) 
		FROM {db_prefix}shop_inventory
		WHERE ownerid = {int:id}",
		array(
			'id' => $user_info['id'],
		));
	list ($context['shop_inv']['item_count']) = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);	

	// If the page is not set, assume page 1
	if (!isset($_GET['page']))
		$_GET['page'] = 1;
	// If items per page not set, assume 15
	if (!isset($modSettings['shopItemsPerPage']))
		$modSettings['shopItemsPerPage'] = 15;

	// The page count - Total number of items divided by number per page
	$context['shop_inv']['pages']['total'] = ceil($context['shop_inv']['item_count'] / $modSettings['shopItemsPerPage']);
	// First item
	$firstItem = ($_GET['page'] * $modSettings['shopItemsPerPage']) - $modSettings['shopItemsPerPage'];

	// Start with empty items array
	$context['shop_inv']['items'] = array();

	// Get all the inventory items on current page
	$result = $smcFunc['db_query']('', "
		SELECT it.name, it.desc, it.input_needed, it.can_use_item, it.image,
			inv.amtpaid, inv.id, inv.trading, inv.tradecost
		FROM {db_prefix}shop_inventory AS inv, {db_prefix}shop_items AS it
		WHERE inv.ownerid = {int:ownerid} AND inv.itemid = it.id
		ORDER BY it.can_use_item DESC, it.name ASC, inv.id ASC
		LIMIT {int:firstitem}, {int:itemsperpage}",
		array(
			'ownerid' => $user_info['id'],
			'firstitem' => $firstItem,
			'itemsperpage' => $modSettings['shopItemsPerPage'],
		));

	// Loop through results
	while ($row = $smcFunc['db_fetch_assoc']($result))		
		$context['shop_inv']['items'][] = array(
			'id' => $row['id'],
			'image' => $row['image'],
			'name' => $row['name'],
			'desc' => $row['desc'],
			'amtpaid' => $row['amtpaid'],
			'can_use_item' => $row['can_use_item'],
			'input_needed' => $row['input_needed'],
			'trading' => $row['trading'],
			'tradecost' => $row['tradecost']
		);
	$smcFunc['db_free_result']($result);

	// Set some miscellaneous variables
	$context['shop_inv']['pages']['current'] = $_GET['page'];
	$context['shop_inv']['pages']['link'] = $scripturl . '?action=shop;do=inv';
	// The shop action
	$context['shop_do'] = 'inv';
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_yourinv'];
	// Use the inventory template
	$context['sub_template'] = 'inventory';
		
}
// Inv2 - Using an item, and user needs to enter some input
elseif ($_GET['do'] == 'inv2')
{
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=inv',
		'name' => $txt['shop_yourinv'],
	);

	// Make sure input was numeric
	$_GET['id'] = (int) $_GET['id'];
	
	// Get the item's information
	$result = $smcFunc['db_query']('', "
		SELECT it.module, it.can_use_item, it.info1, it.info2, it.info3,
			it.info4, inv.ownerid
		FROM {db_prefix}shop_inventory AS inv, {db_prefix}shop_items AS it
		WHERE inv.id = {int:id} AND inv.itemid = it.id
		LIMIT 1",
		array(
			'id' => $_GET['id'],
		));

	$row = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);
	
	// Trying to use other person's item? Nope, not here ;)
	if ($row['ownerid'] !== $user_info['id'])
		fatal_lang_error('shop_use_others_item');
	// If the item is unusable
	elseif ($row['can_use_item'] == false)
		fatal_lang_error('shop_unusable');

	// Show the 'Item Requires Input' message, and start the form
	// TODO: Create seperate function in template for this? Current method is ugly.
	$context['shop_buy_message'] = '
		' . $txt['shop_input'] . '<br />
		 <form action="' . $scripturl . '?action=shop;do=inv3&id=' . $_GET['id'] . '" method="post">';

	// Additional info, just in case it's needed in getUseInput() function
	$item_info[1] = $row['info1'];
	$item_info[2] = $row['info2'];
	$item_info[3] = $row['info3'];
	$item_info[4] = $row['info4'];

	// Include the item engine...
	require($sourcedir . '/shop/item_engine.php');
	//... and the actual item.
	require($sourcedir . '/shop/items/' . $row['module'] . '.php');
	// Create the item, ...
	eval('$temp = new item_' . $row['module'] . ';');
	// ...get the input required...
	$context['shop_buy_message'] .= $temp->getUseInput();
	// ... and show the submit button!
	$context['shop_buy_message'] .= '<br /><input type="submit" value="' . $txt['shop_use'] . '"></form>';
	
	// The shop action
	$context['shop_do'] = 'inv';
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_yourinv'];
	// Use the 'message' template
	$context['sub_template'] = 'message';
}
// Inv3 - Final stages of using the item
elseif ($_GET['do'] == 'inv3')
{
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=inv',
		'name' => $txt['shop_yourinv'],
	);
	spamProtection('login');
	// Make sure input is numeric
	$_GET['id'] = (int) $_GET['id'];
	// Get the item's information
	$result = $smcFunc['db_query']('', "
		SELECT it.module, it.can_use_item, it.delete_after_use, 
			it.info1, it.info2, it.info3, it.info4, inv.ownerid
		FROM ({db_prefix}shop_inventory AS inv, {db_prefix}shop_items AS it)
		WHERE inv.id = {int:id} AND inv.itemid = it.id
		LIMIT 1",
		array(
			'id' => $_GET['id'],
		));

	$row = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);
	
	// Trying to use someone else's item? Nice try! :P
	if ($row['ownerid'] !== $user_info['id'])
		fatal_lang_error('shop_use_others_item2');
	// Sorry, item is a rock (unusable)
	elseif ($row['can_use_item'] == false)
		fatal_lang_error('shop_unusable');

	// Additional information used by the item (entered by admin when adding item)
	$item_info[1] = $row['info1'];
	$item_info[2] = $row['info2'];
	$item_info[3] = $row['info3'];
	$item_info[4] = $row['info4'];

	// Require the item engine ...
	require($sourcedir . '/shop/item_engine.php');
	// ... and the actual item.
	require($sourcedir . '/shop/items/' . $row['module'] . '.php');

	// Create an instance of the item
	eval('$temp = new item_' . $row['module'] . ';');
	// Now, do the actual action!
	$context['shop_buy_message'] = $temp->onUse();
	// And show the 'Back to Inventory' link
	$context['shop_buy_message'] .= '
			<br /><br /><a href="' . $scripturl . '?action=shop;do=inv">' . $txt['shop_back2inv'] . '</a>';

	// If we got here, everything worked OK.
	// If we have to, remove the item from the user's inventory
	if ($row['delete_after_use'] == 1)
		$smcFunc['db_query']('', "
			DELETE FROM {db_prefix}shop_inventory
			WHERE id = {int:id}
			LIMIT 1",
			array(
				'id' => $_GET['id'],
			));

	// The shop action
	$context['shop_do'] = 'inv';
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_yourinv'];						
	// Use the message template
	$context['sub_template'] = 'message';
}
// View another member's inventory
elseif ($_GET['do'] == 'invother')
{
	$context['linktree'][] = array(
		'url' => $scripturl . 'action=shop;do=invother',
		'name' => $txt['shop_invother'],
	);
	
	// Are they allowed to view another member's inventory?
	isAllowedTo('shop_invother');
	
	// The shop action
	$context['shop_do'] = 'invother';
	// This is simple: Set the page title, and show the template!
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_invother'];
	$context['sub_template'] = 'otherInventory';
}
// View another member's inventory - Form was submitted, and a name was entered
elseif ($_GET['do'] == 'invother2')
{
	spamProtection('login');
	 $context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=invother',
		'name' => $txt['shop_invother'],
	);
	
	// Are they allowed to view another member's inventory?
	isAllowedTo('shop_invother');

	// Initialise the context variable we use, with some settings
	$context['shop_inv'] = array(
		'last_col_type' => 'none'
	);
	
	// This code from PersonalMessage.php. It trims the " characters off the membername posted, 
	// and then puts all names into an array
	$_REQUEST['member'] = strtr($_REQUEST['member'], array('\\"' => '"'));
	preg_match_all('~"([^"]+)"~', $_REQUEST['member'], $matches);
	$members = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_REQUEST['member']))));
	
	// Loop through all the names found
	foreach ($members as $index => $member)
		if (strlen(trim($member)) > 0)
			$members[$index] = $smcFunc['htmlspecialchars']($smcFunc['strtolower'](stripslashes(trim($member))));
		else
			unset($members[$index]);
	
	// Find all these members
	$context['shop_invother'] = findMembers($members);
	
	// None of the entered members exist?
	if (count($context['shop_invother']) == 0)
		fatal_lang_error('shop_members_no_exist', true, array(implode(', ', $members)));
	// Loop through all the members we found
	foreach ($context['shop_invother'] as $key => $member)
	{
		// Start with an empty inventory array
		$context['shop_invother'][$key]['items'] = array();
		// TODO: Can this be more efficient?
		// Get the user's inventory
		$result = $smcFunc['db_query']('', "
			SELECT it.name, it.desc, it.image, inv.id
			FROM ({db_prefix}shop_inventory AS inv, {db_prefix}shop_items AS it)
			WHERE ownerid = {int:id} AND inv.itemid = it.id",
			array(
				'id' => $member['id'],
			));

		// Loop through all the items
		while ($row = $smcFunc['db_fetch_assoc']($result))
			$context['shop_invother'][$key]['items'][] = array(
				'name' => $row['name'],
				'desc' => $row['desc'],
				'image' => $row['image']
			);
		$smcFunc['db_free_result']($result);
	}
	
	// The shop action
	$context['shop_do'] = 'invother';
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_invother'];		
	// Use the inventory template
	$context['sub_template'] = 'otherInventory';
}
?>
