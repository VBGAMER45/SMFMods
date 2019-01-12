<?php
/**********************************************************************************
* Shop-Send.php                                                                   *
* Various sending stuff (Send money to other person, send item to other person)   *
* -- This is not called directly. The code in here is used in Shop.php --         *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-01-19 21:57:22 +1100 (Fri, 19 Jan 2007)                          $ *
* $Id:: Shop-Send.php 92 2007-01-19 10:57:22Z daniel15                          $ *
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

if ($_GET['do'] == 'sendmoney')
{
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=sendmoney',
		'name' => $txt['shop_send_money'],
	);
	
	// Are they allowed to send money to someone?
	isAllowedTo('shop_sendmoney');
	
	// If $_GET['member'] is set, pass it to the page. Otherwise, set it to blank.
	$context['shopSendMoneyMember'] = isset($_GET['member']) ?  $_GET['member'] : '';
	
	// The shop action
	$context['shop_do'] = 'sendmoney';
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_send_money'];
	// Use the sendmoney template
	$context['sub_template'] = 'sendmoney';
}
// If they've submitted the form - Actually send the money
elseif($_GET['do'] == 'sendmoney2')
{
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=sendmoney',
		'name' => $txt['shop_send_money'],
	);
	spamProtection('login');
	// Are they allowed to send money to someone?
	isAllowedTo('shop_sendmoney');

	// Make sure amount is numeric
	$amount = (float) $_POST['amount'];

	// Trying to give more than they have
	if ($context['user']['money'] < $amount)
		$context['shop_buy_message'] = $txt['shop_dont_have_much'];
	// Trying to *give* a negative amount? Nice try...
	elseif ($amount < 1) 
		$context['shop_buy_message'] = $txt['shop_give_negative'];
	// Giving 0 credits...? What's the point?
	elseif ($amount == 0)
		$context['shop_buy_message'] = $txt['shop_invalid_send_amount'];
	else {
	
		// This code from PersonalMessage.php, lines 1531-1535. It trims the " characters off the membername posted, 
		// and then puts all names into an array
		$_POST['membername'] = strtr($_POST['membername'], array('\\"' => '"'));
		preg_match_all('~"([^"]+)"~', $_POST['membername'], $matches);
		$moneyArrayTo = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_POST['membername']))));
		
		// We only want the first memberName found
		$moneyTo = $moneyArrayTo[0];
		
		// Get the id and name of member to send to
		$result = db_query("
			SELECT ID_MEMBER, memberName
			FROM {$db_prefix}members
			WHERE memberName = '{$moneyTo}' or realName = '{$moneyTo}'
			LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		
		// No results? They don't exist!
		if (mysql_num_rows($result) == 0)
			$context['shop_buy_message'] = sprintf($txt['shop_member_no_exist'], htmlentities($moneyTo));
		else
		{
			mysql_free_result($result);
			
			// Take money off sender
			db_query("
				UPDATE {$db_prefix}members
				SET money = money - {$amount}
				WHERE ID_MEMBER = {$ID_MEMBER}
				LIMIT 1", __FILE__, __LINE__);
		cache_put_data('user_settings-' . $ID_MEMBER, null, 60);
			// Give money to receiver
			db_query("
				UPDATE {$db_prefix}members
				SET money = money + {$amount}
				WHERE ID_MEMBER = {$row['ID_MEMBER']}
				LIMIT 1", __FILE__, __LINE__);
			cache_put_data('user_settings-' . $row['ID_MEMBER'], null, 60);
								
			// Who the IM will come from		 
			$pmfrom = array(
				'id' => $ID_MEMBER,
				'name' => $context['user']['name'],
				'username' => $context['user']['username']
			);
		
			// Who the IM is going to
			$pmto = array(
				'to' => array($row['ID_MEMBER']),
				'bcc' => array()
			);
		
			// The subject of the message
			$subject = sprintf($txt['shop_im_sendmoney_subject'], formatMoney($amount), $context['user']['name']);
			// The actual message contents
			$message = sprintf($txt['shop_im_sendmoney_message'], $context['user']['name'], formatMoney($amount), $_POST['message']);
			// Now, send!
			sendpm($pmto, $subject, $message, 0, $pmfrom);
			
			// Tell the user that their request was successful
			//$context['shop_buy_message'] = sprintf($txt['shop_successfull_send'], formatMoney($amount), $context['user']['name']);
			$context['shop_buy_message'] = sprintf($txt['shop_successfull_send'], formatMoney($amount), $moneyTo);
			
		}
	}
	
	// The shop action
	$context['shop_do'] = 'sendmoney';

	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_send_money'];
	// Use the 'message' template
	$context['sub_template'] = 'message';
	
// Send an item to someone
} elseif($_GET['do'] == 'senditems') {
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=senditems',
		'name' => $txt['shop_send_item'],
	);
	// Are they allowed to send items to someone?
	isAllowedTo('shop_senditems');
	
	// Start with an empty array
	$context['shop_send_items'] = array();
	
	// Get all the user's current items
	$result = db_query("
		SELECT it.name, inv.id AS ivid, inv.trading
		FROM {$db_prefix}shop_inventory AS inv, {$db_prefix}shop_items AS it
		WHERE inv.ownerid = {$ID_MEMBER} AND inv.itemid = it.id AND inv.trading = 0
		GROUP BY it.id
		ORDER BY it.name ASC ", __FILE__, __LINE__);				
	// Loop through all the items...
	while ($row = mysql_fetch_assoc($result))
		// ...and add them to the list
		$context['shop_send_items'][] = array(
			'id' => $row['ivid'],
			'name' => $row['name']
		);
	mysql_free_result($result);

	// The shop action
	$context['shop_do'] = 'senditems';

	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_send_item'];
	// Use the 'send items' template
	$context['sub_template'] = 'sendItems';
}
// Send an item to someone - Actual send
elseif($_GET['do'] == "senditems2")
{
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=shop;do=senditems2',
		'name' => $txt['shop_send_money'],
	);
	spamProtection('login');
	// Are they allowed to send items to someone?
	isAllowedTo('shop_senditems');

	// Make sure the ID was numeric
	$_POST['giftid'] = (int) $_POST['giftid'];
	
	// Current item information
	$result = db_query("
		SELECT inv.ownerid, inv.itemid, it.name
		FROM {$db_prefix}shop_inventory AS inv, {$db_prefix}shop_items AS it
		WHERE inv.id = '{$_POST['giftid']}' AND it.id = inv.itemid", __FILE__, __LINE__);
	$rowItem = mysql_fetch_array($result, MYSQL_ASSOC);
	mysql_free_result($result);
	
	// You don't add the item? Sorry, but we don't allow stealing! :D
	if ($rowItem['ownerid'] !== $ID_MEMBER)
		fatal_lang_error('shop_use_others_item');
	
	// This code from PersonalMessage.php, lines 1531-1535. It trims the " characters off the membername posted, 
	// and then puts all names into an array
	$_POST['membername'] = strtr($_POST['membername'], array('\\"' => '"'));
	preg_match_all('~"([^"]+)"~', $_POST['membername'], $matches);
	$itemArrayTo = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_POST['membername']))));
	
	// We only want the first memberName found
	$itemTo = $itemArrayTo[0];
	
	// Get new owner's information
	$result = db_query("
		SELECT ID_MEMBER
		FROM {$db_prefix}members
		WHERE memberName = '{$itemTo}' or realName = '{$itemTo}'", __FILE__, __LINE__);
	$rowNewOwner = mysql_fetch_assoc($result);

	// No results? They don't exist!
	if (mysql_num_rows($result) == 0)
		$context['shop_buy_message'] = sprintf($txt['shop_member_no_exist'], htmlentities($itemTo));
	else {
		mysql_free_result($result);
		
	   // Set the new owner
		db_query("
			UPDATE {$db_prefix}shop_inventory
			SET ownerid = {$rowNewOwner['ID_MEMBER']}
			WHERE id = {$_POST['giftid']}", __FILE__, __LINE__);
		
		// The sender of the IM
		$pmfrom = array(
			'id' => $ID_MEMBER,
			'name' => $context['user']['name'],
			'username' => $context['user']['username']
		);
	
		// The receiver of the IM
		$pmto = array(
			'to' => array($rowNewOwner['ID_MEMBER']),	
			'bcc' => array()
		);
				
		// The message subject
		$subject = $txt['shop_im_senditem_subject'];
		// The actual message
		$message = sprintf($txt['shop_im_senditem_message'], $context['user']['name'], $rowItem['name'], $_POST['message']);
		// Send the IM :)
		sendpm($pmto, $subject, $message, 0, $pmfrom);
		
		// Show the 'success' message :)
		$context['shop_buy_message'] = $txt['shop_transfer_success'];
	}

	// The shop action
	$context['shop_do'] = 'senditems';

	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_send_item'];
	// Use the 'message' template
	$context['sub_template'] = 'message';
	
}	
?>
