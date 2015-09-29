<?php
/**********************************************************************************
* Shop-Buy.php                                                                    *
* Used when buying something from the shop.                                       *
* -- This is not called directly. The code in here is used in Shop.php --         *
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

// If this script isn't called by SMF, it's bad!
if (!defined('SMF'))
	die('Hacking attempt...');

	
// Are they allowed to buy stuff?
isAllowedTo('shop_buy');
	
// The shop action
$context['shop_do'] = 'buy';
// Add link to the linktree
$context['linktree'][] = array(
	'url' => "$scripturl?action=shop;do=buy",
	'name' => $txt['shop_buy'],
);
	
// If they're buying something	
if ($_GET['do'] == 'buy')
{	
	// Initialise the context variable we use, with some settings
	$context['shop_inv'] = array(
		'last_col_type' => 'buy',
		'last_col_header' => $txt['shop_price'] . '/' . $txt['shop_stock']
	);

	// If the sort method isn't defined, assume a default
	if (!isset($_GET['sort']))
		$_GET['sort'] = '0';
	if (!isset($_GET['sortDir']))
		$_GET['sortDir'] = '0';
	
	// Get the sort type
	switch($_GET['sort'])
	{
		case 0:
			$context['shop_inv']['sort_type'] = $txt['shop_name'];
			$sortQuery = 'name';
			break;
		case 1:
			$context['shop_inv']['sort_type'] = $txt['shop_price'];
			$sortQuery = 'price';
			break;
		case 2:
			$context['shop_inv']['sort_type'] = $txt['shop_description'];
			$sortQuery = '`desc`';
			break;
		case 3:
			$context['shop_inv']['sort_type'] = $txt['shop_stock'];
			$sortQuery = 'stock';
			break;
		default:
			fatal_error("Invalid sort method passed");
			break;
	}
	
	// And the direction
	switch($_GET['sortDir'])
	{
		case 0:
			$context['shop_inv']['sort_dir'] = $txt['shop_asc'];
			$sortDirQuery = 'ASC';
			break;
		case 1:
			$context['shop_inv']['sort_dir'] = $txt['shop_desc'];
			$sortDirQuery = 'DESC';
			break;	
		default:
			fatal_error("Invalid sort direction passed");
			break;
	}	
	// Tell the template we need the sort box at the top
	$context['shop_inv']['sort'] = true;

	// Are we only displaying a certain category?
	if (isset($_GET['cat']) && $_GET['cat'] != -1)
	{
		$context['shop_inv']['category'] = (int) $_GET['cat'];
		$catClause = 'WHERE category = ' . $context['shop_inv']['category'];
	}
	else
	{
		$context['shop_inv']['category'] = -1;
		$catClause = '';
	}
		
	// List of all categories
	$context['shop_inv']['categories'] = getCatList();

	// Get the number of items available
	$result = $smcFunc['db_query']('', "
		SELECT COUNT(id)
		FROM {db_prefix}shop_items
		{raw:clause}",
		array(
			'clause' => $catClause
		));
	list ($context['shop_inv']['item_count']) = $smcFunc['db_fetch_row']($result);

	// !!! constructPageIndex()!
	// If the page is not set, assume page 1
	if (!isset($_GET['page']))
		$_GET['page'] = 1;
	// If items per page not set, assume 15
	if (!isset($modSettings['shopItemsPerPage']))
		$modSettings['shopItemsPerPage'] = 15;
	
	// The page count - Total number of items divided by number per page
	$context['shop_inv']['pages']['total'] = ceil($context['shop_inv']['item_count'] / $modSettings['shopItemsPerPage']);
	// First item
	$firstItem = ($_GET['page'] * $modSettings['shopItemsPerPage']) - $modSettings['shopItemsPerPage'];

	// Start with empty items array
	$context['shop_inv']['items'] = array();
	// Get all the items on this page
	$result = $smcFunc['db_query']('', "
		SELECT `name`, `desc`, price, stock, id, image
		FROM {db_prefix}shop_items
		{raw:clause}
		ORDER BY {raw:sort} {raw:sortdir}
		LIMIT {int:firstItem}, {int:perPage}",
		array(
			'clause' => $catClause,
			'sort' => $sortQuery,
			'sortdir' => $sortDirQuery,
			'firstItem' => $firstItem,
			'perPage' => $modSettings['shopItemsPerPage'],
		));

	// Loop through results...
	while ($row = $smcFunc['db_fetch_assoc']($result))
		// ... and add it to the array
		$context['shop_inv']['items'][] = array(
			'id' => $row['id'],
			'image' => $row['image'],
			'name' => $row['name'],
			'desc' => $row['desc'],
			'price' => $row['price'],
			'stock' => $row['stock']
		);
	$smcFunc['db_free_result']($result);
	
	// Miscellaneous stuff
	$context['shop_inv']['pages']['current'] = $_GET['page'];
	$context['shop_inv']['pages']['link'] = $scripturl . '?action=shop;do=buy;sort=' . $_GET['sort'] . ';sortDir=' . $_GET['sortDir'];
	if (isset($context['shop_inv']['category']))
		$context['shop_inv']['pages']['link'] .= ';cat=' . $context['shop_inv']['category'];
	// Set the page title
	$context['page_title'] = $txt['shop'].' - '.$txt['shop_buy'];
	// Use 'Inventory' template
	$context['sub_template'] = 'inventory';	
}
// If they're buying an item
elseif ($_GET['do'] == 'buy2')
{
	spamProtection('login');
	// Make sure the item ID is numeric
	$_GET['id'] = (int) $_GET['id'];

	// Item information
	$result = $smcFunc['db_query']('', "
		SELECT `price`, `stock`, `name`
		FROM {db_prefix}shop_items
		WHERE id = {int:id}
		LIMIT 1",
		array(
			'id' => $_GET['id'],
		));
	$row = $smcFunc['db_fetch_assoc']($result);

	// Hey, that's not a valid item! Hacker?
	if ($smcFunc['db_num_rows']($result) == 0)
		// TODO: Move to language file (not that it needs translating, as normal users should never see this)
		fatal_error('Invalid item ID!');
	// If they don't have enough money
	elseif ($context['user']['money'] < $row['price'])
		$context['shop_buy_message'] = sprintf($txt['shop_need'], formatMoney($row['price'] - $context['user']['money']));
	// If there's no stock left
	elseif ($row['stock'] == 0)
		$context['shop_buy_message'] = $txt['shop_soldout_full'];
	// All is well, they can buy it :)
	else
	{
		$smcFunc['db_free_result']($result);
		
		// Put item in user's inventory
		$smcFunc['db_insert']('insert', '{db_prefix}shop_inventory',
			array(
				'ownerid' => 'int',
				'itemid' => 'int',
				'amtpaid' => 'float',
				'trading' => 'int',
				'tradecost' => 'float',
				),
			array(
				array(
					'ownerid' => $user_info['id'],
					'itemid' => $_GET['id'],
					'amtpaid' => $row['price'],
					'trading' => 0,
					'tradecost' => 0.00,
					),
				),
			array());
					
		// Decrease user's money
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}members
			SET money = money - {float:price}
			WHERE id_member = {int:id}
			LIMIT 1",
			array(
				'price' => $row['price'],
				'id' => $user_info['id'],
			));
		cache_put_data('user_settings-' . $user_info['id'], null, 60);
		// Decrease stock by 1
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}shop_items
			SET stock = stock - 1
			WHERE id = {int:id}
			LIMIT 1",
			array(
				'id' => $_GET['id'],
			));

		// Tell them that they purchased it
		$context['shop_buy_message'] = sprintf($txt['shop_bought_item'], $row['name']);
	}

	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_buy'];	
	// Use the 'message' template
	$context['sub_template'] = 'message';
}
?>