<?php
/**********************************************************************************
* Shop-Bank.php                                                                   *
* Bank stuff (view bank, deposit, withdraw)                                       *
* -- This is not called directly. The code in here is used in Shop.php --         *
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

// If the file isn't called by SMF, it's bad!
if (!defined('SMF'))
	die('Hacking attempt...');
	
// The shop action (for the template)
$context['shop_do'] = 'bank';

// Add to the linktree
$context['linktree'][] = array(
	'url' => $scripturl . '?action=shop;do=bank',
	'name' => $txt['shop_bank'],
);

// Are they allowed in the bank?
isAllowedTo('shop_bank');

// If we're in the main page of the bank
if ($_GET['do'] == 'bank')
{	
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_bank'];
	// Load the 'bank' sub template
	$context['sub_template'] = 'bank';
}
// If they've submitted something on the bank page
elseif($_GET['do'] == 'bank2')
{

	// Make sure the amount of money is numeric
	$_POST['amount'] = (float) $_POST['amount'];
		
	// If they're depositing some money
	if ($_POST['type'] == 'deposit')
	{
		// If user is trying to deposit more money than they have
		if (($_POST['amount'] + $modSettings['shopFeeDeposit']) > $context['user']['money'])
			$context['shop_buy_message'] = $txt['shop_dont_have_much'];
		// If they're trying to deposit less than 0
		elseif ($_POST['amount'] <= 0)
			$context['shop_buy_message'] = $txt['shop_no_negative'];
		// If the amount they're transferring is less than the minimum
		elseif ($_POST['amount'] < $modSettings['shopMinDeposit'])
			$context['shop_buy_message'] = sprintf($txt['shop_deposit_small'], formatMoney($modSettings['shopMinDeposit']));
		// All is good :-)
		else
		{
			// Add amount to member's bank money, and remove from money in pockey
			$smcFunc['db_query']('', "
				UPDATE {db_prefix}members
				SET moneyBank = moneyBank + {int:amount},
					money = money - ({float:amount} + {int:fee})
				WHERE id_member = {int:id}
				LIMIT 1",
				array(
					'amount' => $_POST['amount'],
					'fee' => $modSettings['shopFeeDeposit'],
					'id' => $context['user']['id'],
					));
			cache_put_data('user_settings-' . $context['user']['id'], null, 60);
			// Get new money amounts (pocket and bank)
			$result = $smcFunc['db_query']('', "
				SELECT money, moneyBank
				FROM {db_prefix}members
				WHERE id_member = {int:id}
				LIMIT 1",
				array(
					'id' => $context['user']['id'],
					));
			$row = $smcFunc['db_fetch_assoc']($result);
			$smcFunc['db_free_result']($result);
			
			// Format the amounts
			$money = formatMoney($row['money']);
			$moneyBank = formatMoney($row['moneyBank']);
			// Set the message to show them
			// TODO: Make this a seperate function in the template, rather than piggybacking off shop_buy_message? This is ugly :P
			$context['shop_buy_message'] = sprintf($txt['shop_deposit'], $moneyBank, $money) . '
					<br /><a href="' . $scripturl . '?action=shop;do=bank">' . $txt['shop_back2bank'] . '</a>';
		}
	}
	// If they're withdrawing some money
	// TODO: Maybe merge some of the below code with the deposit section?
	elseif ($_POST['type'] == 'withdraw')
	{
		// Make sure amount is numeric
		$_POST['amount'] = (float) $_POST['amount'];

		// If user is trying to withdraw more money than they have
		if ($_POST['amount'] + $modSettings['shopFeeWithdraw'] > $context['user']['moneyBank'])
			$context['shop_buy_message'] = $txt['shop_dont_have_much2'];
		// If they're trying to take out less than 0
		elseif ($_POST['amount'] <= 0)
			$context['shop_buy_message'] = $txt['shop_no_negative'];
		// If the amount they specified is less than the minimum
		elseif ($_POST['amount'] < $modSettings['shopMinWithdraw'])
			$context['shop_buy_message'] = sprintf($txt['shop_withdraw_small'], formatMoney($modSettings['shopMinWithdraw']));
		// All is good
		else
		{
			// Remove amount from member's bank money, and add to money in pockey
			$smcFunc['db_query']('', "
				UPDATE {db_prefix}members
				SET moneyBank = moneyBank - ( {float:amount} + {float:fee}), 
					money = money + {float:amount}
				WHERE id_member = {int:id}
				LIMIT 1",
				array(
					'amount' => $_POST['amount'],
					'fee' => $modSettings['shopFeeWithdraw'],
					'id' => $context['user']['id'],
				));
			cache_put_data('user_settings-' . $context['user']['id'], null, 60);					
			// Get current money amounts (pocket and bank)
			$result = $smcFunc['db_query']('', "
				SELECT money, moneyBank
				FROM {db_prefix}members
				WHERE id_member = {int:id}
				LIMIT 1",
				array(
					'id' => $context['user']['id'],
				));
			$row = $smcFunc['db_fetch_assoc']($result);
			$smcFunc['db_free_result']($result);
			
			// Format the amounts
			$money = formatMoney($row['money']);
			$moneyBank = formatMoney($row['moneyBank']);
			// Show them the success message
			$context['shop_buy_message'] = sprintf($txt['shop_withdraw'], $money, $moneyBank).'
					<br /><a href="' . $scripturl . '?action=shop;do=bank">' . $txt['shop_back2bank'] . '</a>';
		}
	}
	// This should not happen! Hacker?
	else
	{
	   fatal_error('ERROR: The type passed was not valid!');
	}
	
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_bank'];
	// Use the 'message' template
	$context['sub_template'] = 'message';
}
?>
