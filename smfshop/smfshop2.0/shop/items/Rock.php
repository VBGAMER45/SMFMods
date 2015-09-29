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

// Your class should always be called item_filename, eg. if your file is 
// myCoolItem.php then the class should be called 'item_myCoolItem'. This 
// class should always extend itemTemplate.
class item_rock extends itemTemplate
{
	
	// When this function is called, you should set all the item's
	// variables (see inside this example)
	function getItemDetails()
	{

		// The author of the item
		$this->authorName = 'Daniel15';
		// The author's website
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		// And their email address
		$this->authorEmail = 'dansoft@dansoftaustralia.net';


		// VALUES CHANGEABLE FROM WITHIN ADMIN PANEL:
		  // The name of the item
		  $this->name = 'Rock';
		  // The item's description
		  $this->desc = 'Well.... It does nothing';
		  // The item's price
		  $this->price = 5;
		  
		//UNCHANGEABLE VALUES:
		  // Whether the item requires input or not. In this case, we don't need
		  // any input
		  $this->require_input = false;
		  // Since this is a rock (you can't use it), we set this to false
		  $this->can_use_item = false;
	}

	// Since this item requires no input, we don't need to have a getUseInput function
	// here (see the testitem2.php file if you want to make an item that needs input)

	// Also, since this is a rock, you don't need an onUse function (since it doesn't get used)
}
?>
