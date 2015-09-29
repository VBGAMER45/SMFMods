<?php
/**********************************************************************************
* SMFShop item                                                                    *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-04-14 10:39:52 +0200 (za, 14 apr 2007)                           $ *
* $Id:: ChangeUsername.php 113 2007-04-14 08:39:52Z daniel15                    $ *
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

class item_ChangeUsername extends itemTemplate
{
	function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';

		$this->name = 'Change Username';
		$this->desc = 'Change your Username!';
		$this->price = 50;
	}

	function getUseInput()
	{
		return 'New Username: <input type="text" name="newusername" size="50" />
				NOTE: Due to the way SMF works, you may need to use the "Forgot your Password" feature to reset your password after changing your username.';
	}

	function onUse()
	{
		global $context, $smcFunc;
		
		if (!isset($_POST['newusername']) || $_POST['newusername'] == '')
			die('ERROR: Please enter a new username!');
		
		$_POST['newusername'] = $smcFunc['htmlspecialchars']($_POST['newusername'], ENT_QUOTES);

		// Check if username is in use
		$result = $smcFunc['db_query']('', "
		SELECT 
			member_name
		FROM {db_prefix}members
		WHERE member_name = '" . $_POST['newusername'] . "' LIMIT 1");
		$memCount = $smcFunc['db_num_rows']($result);

		if ($memCount > 0)
			die('ERROR: Username is already in use!');
		
		updateMemberData($context['user']['id'], array('member_name' => $_POST['newusername']));
		return 'Successfully changed your username to ' . $_POST['newusername'];
	}

}

?>
