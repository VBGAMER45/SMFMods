<?php
/**********************************************************************************
* Shop-Send.php                                                                   *
* Various sending stuff (Send money to other person, send item to other person)   *
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
	
	// Are they allowed to send money to someone?
	isAllowedTo('shop_sendmoney');
	spamProtection('login');
	// Make sure amount is numeric
	$amount = (float) $_POST['amount'];

	// Trying to give more than they have
	if ($context['user']['money'] < $amount)
		$context['shop_buy_message'] = $txt['shop_dont_have_much'];
	// Trying to *give* a negative amount? Nice try...
	elseif ($amount < 0) 
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
		$result = $smcFunc['db_query']('', "
			SELECT id_member, member_name
			FROM {db_prefix}members
			WHERE member_name = {string:moneyto} or real_name = {string:moneyto}
			LIMIT 1",
			array(
				'moneyto' => $moneyTo,
			));
		$row = $smcFunc['db_fetch_assoc']($result);
		
		// No results? They don't exist!
		if ($smcFunc['db_num_rows']($result) == 0)
			$context['shop_buy_message'] = sprintf($txt['shop_member_no_exist'], htmlentities($moneyTo));
		else
		{
			$smcFunc['db_free_result']($result);
			
			// Take money off sender
			$smcFunc['db_query']('', "
				UPDATE {db_prefix}members
				SET money = money - {float:amount}
				WHERE id_member = {int:id}
				LIMIT 1",
				array(
					'amount' => $amount,
					'id' => $context['user']['id'],
				));
			cache_put_data('user_settings-' . $context['user']['id'], null, 60);

			// Give money to receiver
			$smcFunc['db_query']('', "
				UPDATE {db_prefix}members
				SET money = money + {float:amount}
				WHERE id_member = {int:id}
				LIMIT 1",
				array(
					'amount' => $amount,
					'id' => $row['id_member'],
				));
			cache_put_data('user_settings-' . $row['id_member'], null, 60);
								
			// Who the IM will come from		 
			$pmfrom = array(
				'id' => $context['user']['id'],
				'name' => $context['user']['name'],
				'username' => $context['user']['username']
			);
		
			// Who the IM is going to
			$pmto = array(
				'to' => array($row['id_member']),
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
	$result = $smcFunc['db_query']('', "
		SELECT it.name, inv.id AS ivid, inv.trading
		FROM {db_prefix}shop_inventory AS inv
		INNER JOIN {db_prefix}shop_items AS it ON inv.itemid = it.id
		WHERE inv.ownerid = {int:ownerid} AND inv.itemid = it.id AND inv.trading = 0
		GROUP BY it.id
		ORDER BY it.name ASC",
		array(
			'ownerid' => $context['user']['id'],
		));
	// Loop through all the items...
	while ($row = $smcFunc['db_fetch_assoc']($result))
		// ...and add them to the list
		$context['shop_send_items'][] = array(
			'id' => $row['ivid'],
			'name' => $row['name']
		);
	$smcFunc['db_free_result']($result);

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
	$result = $smcFunc['db_query']('', "
		SELECT inv.ownerid, inv.itemid, it.name
		FROM {db_prefix}shop_inventory AS inv
		INNER JOIN {db_prefix}shop_items AS it ON inv.itemid = it.id
		WHERE inv.id = {int:id} AND it.id = inv.itemid",
		array(
			'id' => $_POST['giftid'],
		));
	$rowItem = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);
	
	// You don't add the item? Sorry, but we don't allow stealing! :D
	if ($rowItem['ownerid'] !== $context['user']['id'])
		fatal_lang_error('shop_use_others_item');
	
	// This code from PersonalMessage.php, lines 1531-1535. It trims the " characters off the membername posted, 
	// and then puts all names into an array
	$_POST['membername'] = strtr($_POST['membername'], array('\\"' => '"'));
	preg_match_all('~"([^"]+)"~', $_POST['membername'], $matches);
	$itemArrayTo = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_POST['membername']))));
	
	// We only want the first memberName found
	$itemTo = $itemArrayTo[0];
	
	// Get new owner's information
	$result = $smcFunc['db_query']('', "
		SELECT id_member
		FROM {db_prefix}members
		WHERE member_name = {string:name} or real_name = {string:name}",
		array(
			'name' => $itemTo,
		));
	$rowNewOwner = $smcFunc['db_fetch_assoc']($result);

	// No results? They don't exist!
	if ($smcFunc['db_num_rows']($result) == 0)
		$context['shop_buy_message'] = sprintf($txt['shop_member_no_exist'], htmlentities($itemTo));
	else {
		$smcFunc['db_free_result']($result);
		
		// Set the new owner
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}shop_inventory
			SET ownerid = {int:id_member}
			WHERE id = {int:id}",
			array(
				'id_member' => $rowNewOwner['id_member'],
				'id' => $_POST['giftid'],
				));
		
		// The sender of the IM
		$pmfrom = array(
			'id' => $context['user']['id'],
			'name' => $context['user']['name'],
			'username' => $context['user']['username']
		);
	
		// The receiver of the IM
		$pmto = array(
			'to' => array($rowNewOwner['id_member']),	
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
