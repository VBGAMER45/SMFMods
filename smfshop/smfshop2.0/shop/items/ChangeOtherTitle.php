<?php
/**********************************************************************************
* SMFShop item                                                                    *
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

if (!defined('SMF'))
	die('Hacking attempt...');

class item_ChangeOtherTitle extends itemTemplate
{
	function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail ='dansoft@dansoftaustralia.net';

		$this->name = 'Change Other\'s Title';
		$this->desc = 'Change someone else\'s title';
		$this->price = 200;
		
		$this->require_input = true;
		$this->can_use_item = true;
	}

	function getUseInput()
	{
		global $context, $scripturl, $settings, $txt;
		return 'User\'s name: <input type="text" name="username" size="50" />
				<a href="' . $scripturl . '?action=findmember;input=username;quote=0;sesc=' . $context['session_id'] . '" onclick="return reqWin(this.href, 350, 400);"><img src="' . $settings['images_url'] . '/icons/assist.gif" border="0" alt="' . $txt['find_members'] . '" /> Find Member</a><br />
				New title: <input type="text" name="newtitle" size="50" />';
	}

	function onUse()
	{
		global $smcFunc, $context, $smcFunc;

		if (!isset($_POST['username']))
			die('ERROR: Please enter a username!');
		if (!isset($_POST['newtitle']))
			die('ERROR: Please enter a new title to use!');
			
		$_POST['newtitle'] = $smcFunc['htmlspecialchars']($_POST['newtitle'], ENT_QUOTES);
		
		// This code from PersonalMessage.php. It trims the " characters off the membername posted, 
		// and then puts all names into an array
		$_POST['username'] = strtr($_POST['username'], array('\\"' => '"'));
		preg_match_all('~"([^"]+)"~', $_POST['username'], $matches);
		$userArray = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_POST['username']))));
		
		// We only want the first memberName found
		$user = $userArray[0];

		$result = $smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET usertitle = {string:newtitle}
			WHERE member_name = {string:member_name}',
			array(
				'newtitle' => $_POST['newtitle'],
				'member_name' => $user,
			));
		return 'Successfully changed ' . $user . '\'s title to ' . $_POST['newtitle'];
	}

}

?>
