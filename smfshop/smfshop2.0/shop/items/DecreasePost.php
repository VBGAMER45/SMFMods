<?php
/**********************************************************************************
* SMFShop item                                                                    *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-04-14 10:39:52 +0200 (za, 14 apr 2007)                           $ *
* $Id:: DecreasePost.php 113 2007-04-14 08:39:52Z daniel15                      $ *
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

class item_DecreasePost extends itemTemplate
{
	function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';

		$this->name = 'Decrease Posts by xxx';
		$this->desc = 'Decrease <i>Someone else\'s</i> post count by xxx!!';
		$this->price = 200;
		
		$this->require_input = true;
		$this->can_use_item = true;
		$this->addInput_editable = true;
	}
	
	function getAddInput()
	{
		global $item_info;
		if ($item_info[1] == 0) $item_info[1] = 100;
		return 'Amount to decrease by: <input type="text" name="info1" value="' . $item_info[1] . '" />';
	}

	function getUseInput()
	{
		global $context, $scripturl, $settings, $txt;
		return 'Member\'s Name: <input type="text" name="username" />
				<a href="' . $scripturl . '?action=findmember;input=username;quote=0;sesc=' . $context['session_id'] . '" onclick="return reqWin(this.href, 350, 400);"><img src="' . $settings['images_url'] . '/icons/assist.gif" border="0" alt="' . $txt['find_members'] . '" /> Find Member</a><br />';
	}

	function onUse()
	{
		global $smcFunc, $item_info;
		
		if ($item_info[1] == 0) $item_info[1] = 100;

		if (!isset($_POST['username']) || $_POST['username'] == '')
			die('ERROR: Please enter a username!');
			
		// This code from PersonalMessage.php5. It trims the " characters off the membername posted, 
		// and then puts all names into an array
		$_POST['username'] = strtr($_POST['username'], array('\\"' => '"'));
		preg_match_all('~"([^"]+)"~', $_POST['username'], $matches);
		$userArray = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_POST['username']))));
		
		// We only want the first memberName found
		$user = $userArray[0];

		$result = $smcFunc['db_query']('', "
			UPDATE {db_prefix}members
			SET posts = posts - {int:amount}
			WHERE member_name = {string:member_name}",
			array(
				'amount' => $item_info[1],
				'member_name' => $user,
			));
		return 'Successfully decreased ' . $_POST['username'] . '\'s posts by ' . $item_info[1] . '!';
	}

}

?>
