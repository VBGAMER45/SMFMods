<?php
/**********************************************************************************
* SMFShop item                                                                    *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-01-18 09:26:55 +0100 (do, 18 jan 2007)                           $ *
* $Id:: testitem.php 79 2007-01-18 08:26:55Z daniel15                           $ *
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

// This is just to make sure that the item is used through SMF, and people aren't accessing it directly
// Additionally, this is used elsewhere in SMF (in almost all the files)
if (!defined('SMF'))
	die('Hacking attempt...');

/*
 * This is a very simple item example. For a slightly more advanced example
 * (one that gets input from the user), please see testitem2.php
 * Note that all items should try to follow the SMF Coding Guidelines, available
 * from http://custom.simplemachines.org/mods/guidelines.php
 *
 * Your class should always be called item_filename, eg. if your file is 
 * myCoolItem.php then the class should be called 'item_myCoolItem'. This 
 * class should always extend itemTemplate.
 */
class item_testitem extends itemTemplate
{
	
	// When this function is called, you should set all the item's
	// variables (see inside this example)
	function getItemDetails()
	{

		// The author's name
		$this->authorName = 'Daniel15';
		// The author's website
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		// The author's email address
		$this->authorEmail = 'dansoft@dansoftaustralia.net';

		// --- Values changeable from within the SMFShop admin panel ---
		// The name of the item
		$this->name = 'A Test Item';
		// The item's description
		$this->desc = 'Just a test item!';
		// The item's price
		$this->price = 5;
		
		// --- Unchageable values ---
		// These values can not be changed when adding the item, they are stuck on what you set them to here.
		
		// Whether the item requires input or not. In this case, we don't need
		// any input
		$this->require_input = false;
		// Set this to 'false' if the item is unusable. This is good for display
		// items, such as rocks :).
		$this->can_use_item = true;
	}

	// Since this item requires no input, we don't need to have a getUseInput function
	// here (see the testitem2.php file if you want to make an item that needs input)
	
	// This is where all the fun begins. This function is called when 
	// the user actually uses the item. Return stuff, DON'T ECHO!
	function onUse()
	{
		return 'Hello, I am a test!<br />This is all the test item does!!';
	}
}


?>
