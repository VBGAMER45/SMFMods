<?php
/**********************************************************************************
* SMFShop item                                                                    *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-04-14 10:39:52 +0200 (za, 14 apr 2007)                           $ *
* $Id:: IncreaseTimeLoggedIn.php 113 2007-04-14 08:39:52Z daniel15              $ *
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

class item_IncreaseTimeLoggedIn extends itemTemplate
{
	function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';

		$this->name = 'Increase Total Time by xxx';
		$this->desc = 'Increase your total time logged in by xxx (default is 12 hours)';
		$this->price = 50;
		
		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;
	}
	
	function getAddInput()
	{
		global $item_info;
		if ($item_info[1] == 0) $item_info[1] = '43200';
		return 'Amount to increase total time by (in seconds): <input type="text" name="info1" value="' . $item_info[1] . '" />';
	}

	function onUse()
	{
		global $smcFunc, $context, $item_info;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET `total_time_logged_in` = `total_time_logged_in` + {int:amount}
			WHERE id_member = {int:id}',
			array(
				'id' => $context['user']['id'],
				'amount' => $item_info[1],
			));

		$time_hours = (int) $item_info[1] / 3600;
		return 'Successfully added ' . $item_info[1] . ' seconds (' . $time_hours . ' hours) to total logged in time.';
	}

}

?>
