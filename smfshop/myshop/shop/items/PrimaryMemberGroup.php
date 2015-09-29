<?php
/**********************************************************************************
* SMFShop item                                                                    *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-01-18 19:40:57 +1100 (Thu, 18 Jan 2007)                          $ *
* $Id:: PrimaryMemberGroup.php 80 2007-01-18 08:40:57Z daniel15                 $ *
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

// Item to change a user's primary membergroup
// Based off code originally posted to http://www.daniel15.com/forum/index.php/topic,316.msg1583.html#msg1583

if (!defined('SMF'))
	die('Hacking attempt...');

class item_PrimaryMemberGroup extends itemTemplate
{
	function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';
		
		$this->name = 'Change Membergroup';
		$this->desc = 'Change your Membergroup!';
		$this->price = 50000;

		$this->require_input = false;
		$this->can_use_item = true;
	}

	function getAddInput()
	{
		global $db_prefix;

		$selectBox = '
			<select name="info1">';

		// Get all non post-based membergroups
		$result = db_query("SELECT ID_GROUP, groupName
			FROM {$db_prefix}membergroups
			WHERE minPosts = -1", __FILE__, __LINE__);

		// For each membergroup, add it to the list
		while ($row = mysql_fetch_assoc($result))
			$selectBox .= '
				<option value="' . $row['ID_GROUP'] . '">' . $row['groupName'] . '</option>';

		$selectBox .= '
			</select>';
		return 'Membergroup: ' . $selectBox;
	}

	function onUse()
	{
		global $context, $item_info;
		
		updateMemberData($context['user']['id'], array('ID_GROUP' => $item_info[1]));
		return 'Changed Member Group!';
	}

}

?>