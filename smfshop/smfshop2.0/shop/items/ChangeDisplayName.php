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

class item_ChangeDisplayName extends itemTemplate
{
	function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';

		$this->name = 'Change Display Name';
		$this->desc = 'Change your display name!';
		$this->price = 50;

		$this->require_input = true;
		$this->can_use_item = true;
		$this->addInput_editable = true;
	}

	function getAddInput()
	{
		global $item_info;
		if ($item_info[1] == 0) $item_info[1] = 5;
		return 'Minimum length of name: <input type="text" name="info1" value="' . $item_info[1] . '" />';
	}

	function getUseInput()
	{
		// Use length of 5 as default
		if (!isset($item_info[1]) || $item_info[1] == 0) $item_info[1] = 5;

		return 'New Display Name: <input type="text" name="newDisplayName" size="50" /><br />
				Please choose a name which is at least ' . $item_info[1] . ' characters long.';
	}

	function onUse()
	{
		global $context, $item_info, $smcFunc;

		// Use a length of 5 as default
		if (!isset($item_info[1]) || $item_info[1] == 0) $item_info[1] = 5;

		if (strlen($_POST['newDisplayName']) < $item_info[1])
			die('ERROR: The name you chose was not long enough! Please go back and choose a name which is at least ' . $item_info[1] . ' characters long.');

		$_POST['newDisplayName'] = $smcFunc['htmlspecialchars']($_POST['newDisplayName'], ENT_QUOTES);

		updateMemberData($context['user']['id'], array('real_name' => $_POST['newDisplayName']));
		return 'Successfully changed your display name to ' . $_POST['newDisplayName'];
	}

}

?>
