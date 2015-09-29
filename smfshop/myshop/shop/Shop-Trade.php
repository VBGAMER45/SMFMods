<?php
/**********************************************************************************
* Shop_Trade.php                                                                  *
* Trade centre stuff (trade listing, etc.)                                        *
* -- This is not called directly. The code in here is used in Shop.php --         *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-01-18 19:26:55 +1100 (Thu, 18 Jan 2007)                          $ *
* $Id:: Shop-Trade.php 79 2007-01-18 08:26:55Z daniel15                         $ *
* Software by:                DanSoft Australia (http://www.dansoftaustralia.net/)*
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

// If this script isn't called by SMF, it's bad!
if (!defined('SMF'))
	die('Hacking attempt...');
	
// Are they allowed here?
isAllowedto('shop_trade');

// The shop action
$context['shop_do'] = 'trade';

// Visiting the trade centre
if ($_GET['do'] == 'trade') {
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=trade',
		'name' => $txt['shop_trade'],
	);
		
	$context['shop_trade_items'] = array();
	$alternating = 'windowbg2';
	
	// Get all the items in the trade centre
	$result = db_query("
		SELECT it.name, it.desc, it.image, inv.id, m.realName, inv.tradecost
		FROM ({$db_prefix}shop_inventory AS inv, {$db_prefix}shop_items AS it,
			{$db_prefix}members AS m)
		WHERE inv.trading = 1 AND inv.itemid = it.id
			AND	m.ID_MEMBER = inv.ownerid", __FILE__, __LINE__);
	// Loop through all items
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
		$context['shop_trade_items'][] = array(
			'id' => $row['id'],
			'name' => $row['name'],
			'desc' => $row['desc'],
			'image' => $row['image'],
			'realName' => $row['realName'],
			'tradecost' => $row['tradecost'],
		);
	mysql_free_result($result);
	
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_trade'];
	// Use the trade centre sub template
	$context['sub_template'] = 'userTrade';
	loadTemplate('Shop');
}
// If they're buying an item from the trade centre
elseif ($_GET['do'] == 'trade_buy')
{
	$context['linktree'][] = array(
		'url' => $scripturl .  '?action=shop;do=trade',
		'name' => $txt['shop_trade'],
	);
	spamProtection('login');
	// Make sure item ID was numeric
	$_GET['id'] = (int) $_GET['id'];
					
	// Get information on the item in question	
	$result = db_query("
		SELECT it.name, inv.tradecost, inv.trading, inv.ownerid, m.realName,
			   m.emailAddress
		FROM {$db_prefix}shop_inventory AS inv, {$db_prefix}shop_items AS it,
			 {$db_prefix}members AS m
		WHERE inv.id = {$_GET['id']} AND inv.itemid = it.id
		LIMIT 1", __FILE__, __LINE__);
						
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	// If this item is not for trade
	if ($row['trading'] != 1)
		$context['shop_buy_message'] = $txt['shop_no_sale'];
	// If they can't afford it
	elseif ($context['user']['money'] < $row['tradecost'])
		$context['shop_buy_message'] = sprintf($txt['shop_not_enough_money'], $cost - $context['user']['money']);
	// All's well, they can get this itel
	else
	{	
		// Change item info (owner, amount paid, trading = not anymore)
		db_query("
			UPDATE {$db_prefix}shop_inventory
			SET ownerid = {$ID_MEMBER}, amtpaid = {$row['tradecost']},
				trading = 0
			WHERE id = {$_GET['id']}
			LIMIT 1", __FILE__, __LINE__);
							
		// Decrease user's money
		db_query("
			UPDATE {$db_prefix}members
			SET money = money - {$row['tradecost']}
			WHERE ID_MEMBER = {$ID_MEMBER}
			LIMIT 1", __FILE__, __LINE__);
		cache_put_data('user_settings-' . $ID_MEMBER, null, 60);					
		// Give money to old owner
		db_query("
			UPDATE {$db_prefix}members
			SET money = money + {$row['tradecost']}
			WHERE ID_MEMBER = {$row['ownerid']}
			LIMIT 1", __FILE__, __LINE__);			
		 cache_put_data('user_settings-' . $row['ownerid'], null, 60);
		// Who is sending the IM
		$pmfrom = array(
			'id' => $ID_MEMBER,
			'name' => $context['user']['name'],
			'username' => $context['user']['username']
		);

		// Who is receiving the IM		
		$pmto = array(
			'to' => array($row['ownerid']),
			'bcc' => array()
		);
		// The message subject
		$subject = sprintf($txt['shop_im_trade_subject'], $row['name']);
		// The actual message
		$message = sprintf($txt['shop_im_trade_message'], $ID_MEMBER, $context['user']['name'], $row['name'], formatMoney($row['tradecost']));
		// Send the PM
		sendpm($pmto, $subject, $message, 0, $pmfrom);
			
		$context['shop_buy_message'] = sprintf($txt['shop_trade_bought_item'], $row['name'], $row['realName']);
	}

	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_trade'];	
	// Use the message template
	$context['sub_template'] = 'message';
}
// If they're selling an item in the trade centre
elseif ($_GET['do'] == 'trade_sell')
{
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=inv',
		'name' => $txt['shop_yourinv'],
	);
	
	// Make sure ID was numeric
	$_GET['id'] = (int) $_GET['id'];
	// Get information on the item
	$result = db_query("
		SELECT amtpaid
		FROM {$db_prefix}shop_inventory
		WHERE id = {$_GET['id']}", __FILE__, __LINE__);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	// The amount paid for the item
	$context['shop_paid'] = $row['amtpaid'];
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_yourinv'];
	// Use the trade item sub template
	$context['sub_template'] = 'tradeItem';
}
// Sell an item - Part 2. Actually add the item to the trade centre
elseif ($_GET['do'] == 'trade_sell2')
{
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=inv',
		'name' => $txt['shop_yourinv'],
	);
	spamProtection('login');
	// Make sure certain values were numeric
	$_POST['id'] = (int) $_POST['id'];
	$_POST['sellfor'] = (int) $_POST['sellfor'];
	// Check the owner of the item
	$result = db_query("
		SELECT ownerid
		FROM {$db_prefix}shop_inventory
		WHERE id = {$_POST['id']}
		LIMIT 1", __FILE__, __LINE__);

	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	// Sell it for a negative amount? That very odd...
	if ($_POST['sellfor'] <= 0)
		$context['shop_buy_message'] = $txt['shop_trade_negative'];
	 // Sorry, stealing is illegal :P
	elseif ($row['ownerid'] !== $ID_MEMBER)
		$context['shop_buy_message'] = $txt['shop_use_others_item'];
	else {
		// Update item's information
		$result = db_query("
			UPDATE {$db_prefix}shop_inventory
			SET trading = 1, 
				tradecost = {$_POST['sellfor']}
			WHERE id = {$_POST['id']}
			LIMIT 1", __FILE__, __LINE__);
		$context['shop_buy_message'] = $txt['shop_trade_success'];
	}
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_yourinv'];
	// Use the message template
	$context['sub_template'] = 'message';
	
}
// Stop trading an item
elseif ($_GET['do'] == "trade_stop")
{
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=inv',
		'name' => $txt['shop_yourinv'],
	);
	
	// Make sure ID was numeric
	$_GET['id'] = (int) $_GET['id'];
	// Get the ownerID of the item
	$result = db_query("
		SELECT ownerid
		FROM {$db_prefix}shop_inventory
		WHERE id = {$_GET['id']}
		LIMIT 1", __FILE__, __LINE__);

	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	// Sorry, stealing isn't allowed here!
	if ($row['ownerid'] !== $ID_MEMBER)
		$context['shop_buy_message'] = $txt['shop_use_others_item'];
	else {
		// Cancel the trade centre stuff
		$result = db_query("
			UPDATE {$db_prefix}shop_inventory
			SET trading = 0, tradecost = 0
			WHERE id = {$_GET['id']}
			LIMIT 1", __FILE__, __LINE__);
		// Tell the user what we did
		$context['shop_buy_message'] = $txt['shop_trade_cancelled'];
	}

	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_yourinv'];
	// Use the message template
	$context['sub_template'] = 'message';
}
?>
