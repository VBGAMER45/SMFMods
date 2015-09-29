<?php
/**********************************************************************************
* Shop-Subs.php                                                                   *
* General SMFShop subprocedures                                                   *
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

// Write the money in a suitable format.
// Casts the value to a float, and add the prefix and suffix
function formatMoney($money)
{
	global $modSettings;

	// Cast to float
	$money = (float) $money;
	// Return amount with prefix and suffix added
	return $modSettings['shopCurrencyPrefix'] . $money . $modSettings['shopCurrencySuffix'];
}

// Get an array of all selectable item images
// TODO: Clean this up a bit
function getImageList()
{
	global $sourcedir;

	// Start with an empty array
	$imageList = array();
	// Try to open the images directory
	if ($handle = opendir($sourcedir . '/shop/item_images'))
	{
		// For each file in the directory...
		while (false !== ($file = readdir($handle)))
		{
			// ...if it's a valid file, add it to the list
			if (!in_array($file, array('.', '..', 'blank.gif')))
				$imageList[] = $file;
		}
		// Sort the list
		sort($imageList);
		return $imageList;
	}
	// Otherwise, if directory inaccessible, show an error
	else
	{
		fatal_lang_error('shop_cannot_open_images');
	}
}

// Get an array of all the categories
function getCatList()
{
	global $smcFunc;
	
	// Start with an empty array
	$cats = array();
	// Get all the categories
	$result = $smcFunc['db_query']('', "
		SELECT id, name, count
		FROM {db_prefix}shop_categories
		ORDER BY name ASC", array());

	// Loop through all the categories
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		// Let's add this to our array
		$cats[] = array(
			'id' => $row['id'],
			'name' => $row['name'],
			'count' => $row['count'],
		);
	}

	$smcFunc['db_free_result']($result);
	
	// Return the array
	return $cats;
}

// Fix all the item category counts. Use this when the counts are incorrect
function recountItems()
{
	global $smcFunc;
	
	// Start with an empty count array
	$counts = array();
	
	// Get all the items
	$result = $smcFunc['db_query']('', "
		SELECT category
		FROM {db_prefix}shop_items", array());
	
	// Loop through them
	while ($row = $smcFunc['db_fetch_assoc']($result))
		// Is it categorised at all?
		if ($row['category'] != 0)
			// Add one to the category's count. If it's not defined yet, set it to 1
			$counts[$row['category']] = (isset($counts[$row['category']]) ? $counts[$row['category']] + 1 : 1);
		
	$smcFunc['db_free_result']($result);
	
	// Loop through all the categories
	foreach ($counts as $key => $value)
	{
		// Update this category's count
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}shop_categories
			SET count = {int:count}
			WHERE id = {int:key}",
			array(
				'key' => $key,
				'count' => $value,
			));
	}
}
?>
