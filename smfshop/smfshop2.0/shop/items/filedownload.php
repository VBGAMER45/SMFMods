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

class item_filedownload extends itemTemplate
{

	function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail = 'ansoft@dansoftaustralia.net';

		$this->name = 'Download xxx File';
		$this->desc = 'Download a file [INSERT FILE DESCRIPTION HERE]';
		$this->price = 200;

		$this->require_input = false;
		$this->can_use_item = true;
		$this->addInput_editable = true;
	}

	// See AddToPostCount for more info
	function getAddInput()
	{
		global $item_info, $boarddir;
		if ($item_info[1] == '') $item_info[1] = 'file_name_here.txt';
		if ($item_info[2] == '') $item_info[2] = dirname(dirname($boarddir)) . '/files/file_name_here.txt';

		return 'File Name: <input type="text" name="info1" value="' . $item_info[1] . '" size="60" /><br />
				This file name does NOT need to be the same name as the file on the server. This name
				is what the file will be saved as on the user\'s computer.<br /><br />

				File Path (INCLUDING FILE NAME): <input type="text" name="info2" value="' . $item_info[2] . '" size="60" /><br />
				<b>IMPORTANT: </b>Make SURE that this file path is <b>OUT of your webroot.</b> If your forum is
				stored at /home/myuser/public_html/forum/ then store the downloadable file at
				/home/myuser/files/ or similar. Otherwise, you run the risk of the file being
				downloaded without being paid for.<br />
				<input type="checkbox" name="info3" /> Delete item from inventory after use<br /><br />
				Note that the shop\'s normal "Delete from Inventory" setting will not work for this item.';
	}

	function onUse()
	{
		global $item_info;
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . $item_info[1]);
		readfile($item_info[2]);

		if (isset($item_info[3]) && $item_info[3] = 'on')
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}shop_inventory
				WHERE id = {int:id}',
				array(
					'id' => $_GET['id'],
				));
		}
		exit();

	}
}


?>
