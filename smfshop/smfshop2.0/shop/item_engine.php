<?php
/**********************************************************************************
* item_engine.php                                                                 *
* Item backend engine [what else can i call it...? ;)]                            *
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

// Basically, this is a bare-bones item. All the defaults are defined here.
// So, if anything isn't defined by the item, the defaults are inherited from here

class itemTemplate
{
	/* 
	 * All the below fields aren't in the item files themselves. They're just defaults
	 * The correct values are filled in in the getItemDetails() function inside the item
	 * These values are used as fallovers. If any of the variables aren't defined in the item,
	 * it will use the defaults from here.
	 */
	// The name of the item (can be changed by admin)
    var $name = 'No Name Specified';
	// The description of the item (can be changed)
    var $desc = 'No Description Specified';
	// The default price (can be changed)
    var $price = 20;

	// The name of the author
	var $authorName = 'Unknown Author';
	// The author's web address
	var $authorWeb = 'Unknown Website';
	// The email address of the author
	var $authorEmail = 'Unknown Email Address';

	// Whether the item requires additional input or not
	// SMFShop versions before 1.0 (I think) always accepted input, regardless
	// Because of this, this value is set to 'true' for backwards compatibility
    var $require_input = true; 
	// Whether the item is 'usable' (most items) or not (a rock :D)
    var $can_use_item = true;
	// Whether the item will be deleted from the user's inventory upon use
	// This only works in SMFShop New Version and later, and was always true
	// in previous versions
	var $delete_after_use = true;
	// Whether this item allows an admin to change values used by getAddInput()
	// when editing the item. To support this, the item must set the value
	// of the input field to $item_info[1], $item_info[2], etc.
	var $addInput_editable = false;

	// -----------------------------------------------------------------------

	// All of the stuff below is in the items:

    // This is called when we need to get the item details.
	// Here, you should fill in the $name, $desc and $price variables.
    function getItemDetails()
	{
		// You use the function like this:

		// $this->name = "Name";
		// $this->desc = "Description";
		// etc.
	}
    
    /* This is called when person is adding this item to their SMFShop installation via the admin
     * panel. If you need any additional fields, then return them here
	 * Any inputs should be called info1, info2, info3 and info4, and have a value of $item_info[1]
	 * (so that when the admin is editing the item, it shows the previous value)
	 * See AddToPostCount.php for a working demo
	 */
    function getAddInput()
	{
		// Example:
		// return 'Some input that the admin fills in: <input type="text" name="info1" value="' . $item_info[1] . '" />';
	}

    /* 
	 * This is called when person tries to use item. This is used to get input for the item.
     * If item needs no input then just return false here (you don't even need this function
	 * if the can_use_item variable is set to false)
	 * Call the fields whatever you want, as they're passed straight to the onUse() function (via the $_POST array)
	 */
    function getUseInput()
	{
		/* 
		 * Example:
		 *
		 * If input needed:
		 * return 'Enter your name: <input type="text" name="name" />';
		 *
		 * If no input needed:
		 * return false;
		 */
	}

    /* This is called when item is actually used
	 * The input fields in the getUseInput() function are passed here in the $_POST array
	 * The admin fields in the getAddInput() function are passed in the $item_info array
	 */
    function onUse()
	{
		/* See the included items for an example
		 * For an example of an item that uses getAddInput(), see AddToPostCount.php
		 * For an example of an item that uses getUseInput(), see ChangeDisplayName.php 
		 */
	}
}

?>
