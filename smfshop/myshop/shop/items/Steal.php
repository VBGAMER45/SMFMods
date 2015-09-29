<?php
/**********************************************************************************
* SMFShop item                                                                    *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-01-18 19:26:55 +1100 (Thu, 18 Jan 2007)                          $ *
* $Id:: Steal.php 79 2007-01-18 08:26:55Z daniel15                              $ *
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

class item_Steal extends itemTemplate
{
	function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';

		$this->name = 'Steal Credits';
		$this->desc = 'Try to steal credits from another member!';
		$this->price = 50;
		
		$this->require_input = true;
		$this->can_use_item = true;
	}

	function getAddInput()
	{
		global $item_info;
		if ($item_info[1] == 0) $item_info[1] = 40;
		return 'For steal, user <b>does NOT need to, and shouldn\'t</b> know the probability! It\'s more fun this way :-)<br />Probability of successful steal: <input type="text" name="info1" value="' . $item_info[1]  . '" />%';
	}

	function getUseInput()
	{
		global $context, $scripturl, $settings, $txt;
		return 'Steal From: <input type="text" name="stealfrom" size="50" />
				<a href="' . $scripturl . '?action=findmember;input=username;quote=0;sesc=' . $context['session_id'] . '" onclick="return reqWin(this.href, 350, 400);"><img src="' . $settings['images_url'] . '/icons/assist.gif" border="0" alt="' . $txt['find_members'] . '" /> Find Member</a><br />';
	}

	function onUse()
	{
		global $db_prefix, $ID_MEMBER, $item_info;
		
		// Check some inputs
		if (!isset($_POST['stealfrom']) || $_POST['stealfrom'] == '') die('ERROR: Please enter a username to steal from!');
		
		// This code from PersonalMessage.php5. It trims the " characters off the membername posted, 
		// and then puts all names into an array
		$_POST['stealfrom'] = strtr($_POST['stealfrom'], array('\\"' => '"'));
		preg_match_all('~"([^"]+)"~', $_POST['stealfrom'], $matches);
		$userArray = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_POST['stealfrom']))));
		
		// We only want the first memberName found
		$user = $userArray[0];
		
		// Get a random number between 0 and 100
		$try = mt_rand(0, 100);

		// If successful
		if ($try < $item_info[1])
		{

			// Get stealee's (person we're stealing from) money count
			$result = db_query("
				SELECT money
				FROM {$db_prefix}members
				WHERE memberName = '{$user}'", __FILE__, __LINE__);

			// If user doesn't exist
			if (mysql_num_rows($result) == 0)
				die('ERROR: The specified user doesn\'t exist!');

			$row = mysql_fetch_assoc($result);

			// Get random amount between 0 and amount of money stealee has
			$steal_amount = mt_rand(0, $row['money']);

			// Take this money away from stealee...
			$result = db_query("
				UPDATE {$db_prefix}members
				SET money = money - {$steal_amount}
				WHERE memberName = '{$user}'
				LIMIT 1", __FILE__, __LINE__);
			cache_put_data('user_settings-' . $user, null, 60);	
			//...and give to stealer (robber)
			$result = db_query("
				UPDATE {$db_prefix}members
				SET money = money + {$steal_amount}
				WHERE ID_MEMBER = {$ID_MEMBER}
				LIMIT 1", __FILE__, __LINE__);
			cache_put_data('user_settings-' . $ID_MEMBER, null, 60);	
			if ($steal_amount < 50)
				return 'Steal successful, although you only stole ' . $steal_amount . '!';
			else
				return 'Successfully stole ' . $steal_amount . ' from ' . $user . '! It\'s their fault they don\'t have their money in the bank!';
		}
		else
		{
			// If reducing Karma doesn't work, replace
			// 'karmaBad = karmaBad + 10' with 'karmaGood = karmaGood - 10'
			$result = db_query("
				UPDATE {$db_prefix}members
				SET karmaBad = karmaBad + 10
				WHERE ID_MEMBER = {$ID_MEMBER}", __FILE__, __LINE__);
		   return 'Steal <b>unsuccessful!</b> You Karma is now reduced by 10!';
		}
	}
}

?>
