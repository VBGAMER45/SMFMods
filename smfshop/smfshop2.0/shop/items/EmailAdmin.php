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

class item_EmailAdmin extends itemTemplate
{

	function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';

		$this->name = 'Send Email to Admin v3';
		$this->desc = 'Send an email to the admin. The text used can be defined by you.';
		$this->price = 10;
		
		$this->require_input = true;
		$this->can_use_item = true;
		$this->addInput_editable = true;
	}

	function getAddInput()
	{
		global $item_info;
		if ($item_info[3] == '') $item_info[3] = 'Example field: <input type="text" name="example" />';
		if ($item_info[4] == '') $item_info[4] = 'This is an example of the Send Email to Admin item. The example field contained {$_POST["example"]}';
		
		return 'Your email address: <input type="text" name="info1" size="50" value="' . $item_info[1] . '" /><br />
				Subject of message: <input type="text" name="info2" size="50" value="' . $item_info[2] . '" /><br />
				Additional fields needed:<br />
					<textarea name="info3" rows="6" cols="40">
' . htmlspecialchars($item_info[3]) . '
					</textarea><br />

				Message to send:<br />
					<textarea name="info4" rows="10" cols="80" />
' . $item_info[4] . '
					</textarea><br />

<b>NOTE:</b> The additional fields will be filled in by the user when they use the item.
To use one of your additional fields in either the message or subject, use the {$_POST["varname"]}
format, where "varname" is the name of the field (see the above dummy data for an example)';
	}
	
	function getUseInput()
	{
		global $item_info;
		// The 'additional fields needed' entered during item setup
		return htmlspecialchars_decode($item_info[3]);
	}

	function onUse()
	{
		global $sourcedir, $item_info;
		$to = $item_info[1];
		$subject = $item_info[2];
		$message = $item_info[4];
		// We need sendmail!
		require_once($sourcedir . '/Subs-Post.php');

		// Hack put in place to allow $_POST and $_GET vars in the $message var
		// --Daniel15, 4 Septemeber 2005 2:15PM
		foreach ($_POST as $postKey => $postVar)
			$message = str_replace('{$_POST["' . $postKey . '"]}', $postVar, $message);
		  
		foreach ($_GET as $getKey => $getVar)
			$message = str_replace('{$_GET["' . $getKey . '"]}', $getVar, $message);

		// Send the email!
		sendmail($to, $subject, $message) or die('Error sending message to admin! Please inform the Admin of this error. This item will still be available in your inventory.');

		return 'Message sent to admin!';
	}
}


?>
