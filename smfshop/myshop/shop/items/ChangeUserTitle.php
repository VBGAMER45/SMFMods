<?php
/**********************************************************************************
* SMFShop item                                                                    *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-11-25 15:43:36 +1100 (Sun, 25 Nov 2007)                          $ *
* $Id:: ChangeUserTitle.php 127 2007-11-25 04:43:36Z daniel15                   $ *
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

class item_ChangeUserTitle extends itemTemplate
{
	function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';

		$this->name = 'Change User Title';
		$this->desc = 'Change your user title';
		$this->price = 50;
	}

	function getUseInput()
	{
		return 'New Title: <input type="text" name="newtitle" size="50" />';
	}

	function onUse()
	{
		global $context, $func;

		if (!isset($_POST['newtitle']) || $_POST['newtitle'] == '')
			die('ERROR: Please enter a new user title!');
			
		$_POST['newtitle'] = $func['htmlspecialchars']($_POST['newtitle'], ENT_QUOTES);

		updateMemberData($context['user']['id'], array('usertitle' => '"' . $_POST['newtitle'] . '"'));
		return 'Successfully changed your user title to ' . $_POST['newtitle'];
	}

}

?>
