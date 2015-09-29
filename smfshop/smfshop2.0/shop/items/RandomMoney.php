<?php
/**********************************************************************************
* SMFShop item                                                                    *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-04-14 10:39:52 +0200 (za, 14 apr 2007)                           $ *
* $Id:: RandomMoney.php 113 2007-04-14 08:39:52Z daniel15                       $ *
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

if (!defined('SMF'))
	die('Hacking attempt...');

class item_RandomMoney extends itemTemplate
{
    function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';

        $this->name = 'Random Money (between xxx and xxx)';
        $this->desc = 'Get a random amount of money, between xxx and xxx!';
        $this->price = 75;
        
        $this->require_input = false;
        $this->can_use_item = true;
		$this->addInput_editable = true;
    }

    function getAddInput()
	{
		global $item_info;
		if ($item_info[1] == 0) $item_info[1] = '-190';
		if ($item_info[2] == 0) $item_info[2] = '190';
        return 'Minimum amount winnable: <input type="text" name="info1" value="' . $item_info[1] . '" /><br />
                Maximum amount winnable: <input type="text" name="info2" value="' . $item_info[2] . '" />';
    }

    function onUse()
	{
        global $smcFunc, $context, $item_info;
        
		// If an amount was not defined by the admin, assume defaults
        if (!isset($item_info[1]) || $item_info[1] == '')
            $item_info[1] = -190;

        if (!isset($item_info[2]) || $item_info[2] == '')
            $item_info[2] = 190;

        $amount = mt_rand($item_info[1], $item_info[2]);
		
		// Did we lose money?
		if ($amount < 0)
		{
			$result = $smcFunc['db_query']('', "
				SELECT money
				FROM {db_prefix}members
				WHERE id_member = {int:id}",
				array(
					'id' => $context['user']['id'],
				));
			
			$row = $smcFunc['db_fetch_assoc']($result);
			
			$amountLoss = abs($amount);
			
			// If the user has enough money to pay for it out of his/her pocket
			if ($row['money'] > $amountLoss)
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}members
					SET `money` = `money` - {int:amount}
					WHERE id_member = {int:id}',
					array(
						'id' => $context['user']['id'],
						'amount' => $amountLoss,
					));

				return 'You lost ' . formatMoney($amountLoss) . '!';
			}
			// Do we need to get the bank money instead?
			else
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}members
					SET `moneyBank` = `moneyBank` - {int:amount}
					WHERE id_member = {int:id}',
					array(
						'id' => $context['user']['id'],
						'amount' => $amountLoss,
					));

				return 'You lost ' . formatMoney($amountLoss) . '!<br /><br />You didn\'t have enough money in your pocket, so the money was taken from your bank! :(';
			}
		}
		// Congratulations! You won some money! :D
		else
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}members
				SET `money` = `money` + {int:amount}
				WHERE id_member = {int:id}',
				array(
					'id' => $context['user']['id'],
					'amount' => $amount,
				));

			return 'You got ' . formatMoney($amount) . '!';
		}
    }

}

?>
