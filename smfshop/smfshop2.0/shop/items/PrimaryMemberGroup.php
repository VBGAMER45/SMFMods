<?php
/**********************************************************************************
* SMFShop item                                                                    *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.1 (Build 14)                              *
* Software by:                DanSoft Australia (http://www.dansoftaustralia.net/)*
* Copyright 2009-2010 by:          vbgamer45 (http://www.smfhacks.com)                 *
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
		global $smcFunc;

		$selectBox = '
			<select name="info1">';

		// Get all non post-based membergroups
		$result = $smcFunc['db_query']('', "
			SELECT id_group, group_name
			FROM {db_prefix}membergroups
			WHERE min_posts = -1");

		// For each membergroup, add it to the list
		while ($row = $smcFunc['db_fetch_assoc']($result))
			$selectBox .= '
				<option value="' . $row['id_group'] . '">' . $row['group_name'] . '</option>';

		$selectBox .= '
			</select>';
		return 'Membergroup: ' . $selectBox;
	}

	function onUse()
	{
		global $smcFunc, $context, $item_info;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET `id_group` = {int:group}
			WHERE id_member = {int:id}',
			array(
				'id' => $context['user']['id'],
				'group' => $item_info[1],
			));

		return 'Changed Member Group!';
	}

}

?>
