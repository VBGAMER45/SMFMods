<?php
/**********************************************************************************
* Shop_Trade.php                                                                  *
* Trade centre stuff (trade listing, etc.)                                        *
* -- This is not called directly. The code in here is used in Shop.php --         *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-04-10 17:23:33 +0200 (di, 10 apr 2007)                           $ *
* $Id:: Shop-Trade.php 110 2007-04-10 15:23:33Z daniel15                        $ *
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
	$result = $smcFunc['db_query']('', "
		SELECT it.name, it.desc, it.image, inv.id, m.real_name, inv.tradecost
		FROM {db_prefix}shop_inventory AS inv
		INNER JOIN {db_prefix}shop_items AS it ON inv.itemid = it.id
		INNER JOIN {db_prefix}members AS m ON m.id_member = inv.ownerid
		WHERE inv.trading = 1 AND inv.itemid = it.id
			AND	m.id_member = inv.ownerid", array());
	// Loop through all items
	while ($row = $smcFunc['db_fetch_assoc']($result))
		$context['shop_trade_items'][] = array(
			'id' => $row['id'],
			'name' => $row['name'],
			'desc' => $row['desc'],
			'image' => $row['image'],
			'realName' => $row['real_name'],
			'tradecost' => $row['tradecost'],
		);
	$smcFunc['db_free_result']($result);
	
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_trade'];
	// Use the trade centre sub template
	$context['sub_template'] = 'userTrade';
	loadTemplate('Shop');
}
// If they're buying an item from the trade centre
elseif ($_GET['do'] == 'trade_buy')
{
	spamProtection('login');
	$context['linktree'][] = array(
		'url' => $scripturl .  '?action=shop;do=trade',
		'name' => $txt['shop_trade'],
	);

	// Make sure item ID was numeric
	$_GET['id'] = (int) $_GET['id'];
					
	// Get information on the item in question	
	$result = $smcFunc['db_query']('', "
		SELECT
			it.name,
			inv.tradecost, inv.trading, inv.ownerid,
			m.real_name, m.email_address
		FROM {db_prefix}shop_inventory AS inv
		INNER JOIN {db_prefix}shop_items AS it ON inv.itemid = it.id
		INNER JOIN {db_prefix}members AS m ON inv.ownerid = m.id_member
		WHERE inv.id = {int:id}
		LIMIT 1",
		array(
			'id' => $_GET['id'],
		));
						
	$row = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);
	
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
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}shop_inventory
			SET ownerid = {int:ownerid}, amtpaid = {float:tradecost},
				trading = 0
			WHERE id = {int:id}
			LIMIT 1",
			array(
				'ownerid' => $context['user']['id'],
				'tradecost' => $row['tradecost'],
				'id' => $_GET['id'],
			));
							
		// Decrease user's money
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}members
			SET money = money - {float:tradecost}
			WHERE id_member = {int:id_member}
			LIMIT 1",
			array(
				'tradecost' => $row['tradecost'],
				'id_member' => $context['user']['id'],
			));
		cache_put_data('user_settings-' . $context['user']['id'], null, 60);					
		// Give money to old owner
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}members
			SET money = money + {float:tradecost}
			WHERE id_member = {int:id_member}
			LIMIT 1",
			array(
				'tradecost' => $row['tradecost'],
				'id_member' => $row['ownerid'],
			));	
		cache_put_data('user_settings-' . $row['ownerid'], null, 60); 
		// Who is sending the IM
		$pmfrom = array(
			'id' => $context['user']['id'],
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
		$message = sprintf($txt['shop_im_trade_message'], $context['user']['id'], $context['user']['name'], $row['name'], formatMoney($row['tradecost']));
		// Send the PM
		sendpm($pmto, $subject, $message, 0, $pmfrom);
			
		$context['shop_buy_message'] = sprintf($txt['shop_trade_bought_item'], $row['name'], $row['real_name']);
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
	spamProtection('login');
	// Make sure ID was numeric
	$_GET['id'] = (int) $_GET['id'];
	// Get information on the item
	$result = $smcFunc['db_query']('', "
		SELECT amtpaid
		FROM {db_prefix}shop_inventory
		WHERE id = {int:id}",
		array(
			'id' => $_GET['id'],
		));
	$row = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);
	
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
	
	// Make sure certain values were numeric
	$_POST['id'] = (int) $_POST['id'];
	$_POST['sellfor'] = (int) $_POST['sellfor'];
	// Check the owner of the item
	$result = $smcFunc['db_query']('', "
		SELECT ownerid
		FROM {db_prefix}shop_inventory
		WHERE id = {int:id}
		LIMIT 1",
		array(
			'id' => $_POST['id'],
		));

	$row = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);
	
	// Sell it for a negative amount? That very odd...
	if ($_POST['sellfor'] <= 0)
		$context['shop_buy_message'] = $txt['shop_trade_negative'];
	 // Sorry, stealing is illegal :P
	elseif ($row['ownerid'] !== $context['user']['id'])
		$context['shop_buy_message'] = $txt['shop_use_others_item'];
	else {
		// Update item's information
		$result = $smcFunc['db_query']('', "
			UPDATE {db_prefix}shop_inventory
			SET trading = 1, 
				tradecost = {float:tradecost}
			WHERE id = {int:id}
			LIMIT 1",
			array(
				'tradecost' => $_POST['sellfor'],
				'id' => $_POST['id'],
			));
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
	$result = $smcFunc['db_query']('', "
		SELECT ownerid
		FROM {db_prefix}shop_inventory
		WHERE id = {int:id}
		LIMIT 1",
		array(
			'id' => $_GET['id'],
		));

	$row = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);
	
	// Sorry, stealing isn't allowed here!
	if ($row['ownerid'] !== $context['user']['id'])
		$context['shop_buy_message'] = $txt['shop_use_others_item'];
	else {
		// Cancel the trade centre stuff
		$result = $smcFunc['db_query']('', "
			UPDATE {db_prefix}shop_inventory
			SET trading = 0, tradecost = 0
			WHERE id = {int:id}
			LIMIT 1",
			array(
				'id' => $_GET['id'],
			));
		// Tell the user what we did
		$context['shop_buy_message'] = $txt['shop_trade_cancelled'];
	}

	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_yourinv'];
	// Use the message template
	$context['sub_template'] = 'message';
}
?>
