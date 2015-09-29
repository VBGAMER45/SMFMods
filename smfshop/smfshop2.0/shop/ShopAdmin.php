<?php
/**********************************************************************************
* ShopAdmin.php                                                                   *
* SMFShop Administration page                                                     *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.1 (Build 14)                              *
* $Date:: 2009-11-14 19:26:55 +1100 (Thu, 14 Nov 2009)                          $ *
* $Id:: Shop.english.php 79 2009-11-14 08:26:55Z daniel15                       $ *
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
**********************************************************************************/

// TODO: Split this into multiple files (like Shop.php)?

// If the file isn't called by SMF, it's bad!
if (!defined('SMF'))
	die('Hacking attempt...');

// During testing, caching was causing many problems. So, we try to disable the caching here
header('Expires: Fri, 1 Jun 1990 00:00:00 GMT'); // My birthday ;)
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Pragma: no-cache');

// Load the language file
loadLanguage('Shop');
// Check if they're allowed here
isAllowedTo('shop_admin');

// General shop administration page
function ShopGeneral()
{
	global $smcFunc, $context, $db_prefix, $modSettings, $txt;

	// We haven't saved yet (this is for the 'Settings Saved' message' on the admin page)
	$context['shop_saved'] = false;

	// If they've pressed the 'Save' button
	if (isset($_GET['save']))
	{
		// Put all the settings into an array, to save
		$newSettings = array(
			'shopCurrencyPrefix' => $_POST['prefix'],
			'shopCurrencySuffix' => $_POST['suffix'],
			'shopPointsPerTopic' => (float) $_POST['pertopic'],
			'shopPointsPerPost' => (float) $_POST['perpost'],
			'shopImageWidth' => (int) $_POST['image_width'],
			'shopImageHeight' => (int) $_POST['image_height'],
			'shopInterest' => (float) $_POST['interest'],
			'shopItemsPerPage' => (int) $_POST['itemspage'],
			'shopMinDeposit' => (float) $_POST['minDeposit'],
			'shopMinWithdraw' => (float) $_POST['minWithdraw'],
			'shopFeeDeposit' => (float) $_POST['feeDeposit'],
			'shopFeeWithdraw' => (float) $_POST['feeWithdraw'],
			'shopRegAmount' => (int) $_POST['regamount'],
			'shopBankEnabled' => isset($_POST['bankenabled']) ? 1 : 0,
			'shopTradeEnabled' => isset($_POST['tradeenabled']) ? 1 : 0,
			'shopPointsPerChar' => (float) $_POST['perchar'],
			'shopPointsPerWord' => (float) $_POST['perword'],
			'shopPointsLimit' => (float) $_POST['limit'],
			);

		// Save all these settings
		//updateSettings($newSettings);

		// !!! DIRTY!
		foreach ($newSettings as $variable => $value)
		{
			$smcFunc['db_insert']('replace',  '{db_prefix}settings',
				array(
					'variable' => 'string',
					'value' => 'string',
					),
				array(
					'variable' => $variable,
					'value' => $value,
					),
				array());

			$modSettings[$variable] = $value;
		}
		// Kill the cache - it needs redoing now, but we won't bother ourselves with that here.
		cache_put_data('modSettings', null, 90);

		// We've saved, tell the user that it was successful
		$context['shop_saved'] = true;
	}
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_admin_general'];
	// Load the template
	loadTemplate('ShopAdmin');
}

// Inventory administration
function ShopInventory()
{
	global $smcFunc, $context, $txt, $scripturl;

	// If we need to do something (ie. not the main inventory admin page)
	if (!empty($_GET['do']) && $_GET['do'] != '')
	{
		// If we're deleting an item from the inventory
		if ($_GET['do'] == 'delete')
		{
			// Make sure the ID is a number
			$_GET['id'] = (int) $_GET['id'];
			// Delete the actual item
			$smcFunc['db_query']('', "
				DELETE
				FROM {db_prefix}shop_inventory
				WHERE id = {int:id}
				LIMIT 1",
				array(
					'id' => $_GET['id']
				));
			// Tell the user that everything was OK :-)
			$context['shop_message'] = sprintf($txt['shop_deleted_item'], $_GET['id']);
		}
		// If we're editing the user's money
		elseif ($_GET['do'] == 'editmoney')
		{
			// Check inputs were numbers
			$_POST['money_pocket'] = (float) $_POST['money_pocket'];
			$_POST['money_bank'] = (float) $_POST['money_bank'];
			$_POST['userid'] = (int) $_POST['userid'];

			// Update the user's details
			$smcFunc['db_query']('', "
				UPDATE {db_prefix}members
				SET
					money = {float:money},
					moneyBank = {float:bank}
				WHERE ID_MEMBER = {int:id}
				LIMIT 1",
				array(
					'money' => $_POST['money_pocket'],
					'bank' => $_POST['money_bank'],
					'id' => $_POST['userid'],
				));
			cache_put_data('user_settings-' . $_POST['userid'], null, 60);
			// Tell the user that everthing worked find
			$context['shop_message'] = sprintf($txt['shop_changed_money'], $_POST['userid'], $_POST['money_pocket'], $_POST['money_bank']);
		}

		// User ID rather than name?
		if (isset($_REQUEST['userid']) && $_REQUEST['userid'] != 0)
		{
			$_REQUEST['userid'] = (int) $_REQUEST['userid'];
			$clause = 'ID_MEMBER = ' . $_REQUEST['userid'];
		}
		// A name is passed instead
		else
		{
			// This code from PersonalMessage.php, lines 1531-1535. It trims the " characters off the membername posted,
			// and then puts all names into an array
			$_REQUEST['searchfor'] = strtr($_REQUEST['searchfor'], array('\\"' => '"'));
			preg_match_all('~"([^"]+)"~', $_REQUEST['searchfor'], $matches);
			$searchforArray = array_unique(array_merge($matches[1], explode(',', preg_replace('~"([^"]+)"~', '', $_REQUEST['searchfor']))));

			// We only want the first memberName found
			$searchfor = $searchforArray[0];

			$clause = 'member_name = "' . $searchfor . '"';
		}


		// Get the user's information
		$result = $smcFunc['db_query']('', "
			SELECT id_member, money, moneyBank, member_name, real_name
			FROM {db_prefix}members
			WHERE {raw:clause}
			LIMIT 1",
			array(
				'clause' => $clause,
			));

		// If this user doesn't exist
		if ($smcFunc['db_num_rows']($result) == 0)
		{
			$context['shop_inventory_search'] = 'message';
			// Show an error!
			//$context['shop_message'] = sprintf($txt['shop_member_no_exist'], $_REQUEST['searchfor']);
			fatal_error(sprintf($txt['shop_member_no_exist'], $_REQUEST['searchfor']));
		}
		else
		{
			// Get their information
			$row = $smcFunc['db_fetch_assoc']($result);
			$smcFunc['db_free_result']($result);

			// Set up the array of inventory information
			$context['shop_inv'] = array(
				'member' => $row['id_member'],
				'realName' => $row['real_name'],
				'money_pocket' => $row['money'],
				'money_bank' => $row['moneyBank']);

			// Now, get their inventory
			$result = $smcFunc['db_query']('', "
				SELECT it.name, inv.amtpaid, inv.id
				FROM {db_prefix}shop_inventory AS inv, {db_prefix}shop_items AS it
				WHERE inv.ownerid = {int:member}
					AND inv.itemid = it.id
				ORDER BY inv.id",
				array(
					'member' => $context['shop_inv']['member'],
				));

			// Start with an empty array
			$context['shop_inv']['list'] = array();

			// Loop through all their inventory items
			while ($row = $smcFunc['db_fetch_assoc']($result))
				// Add to the list
				$context['shop_inv']['list'][] = array(
					'id' => $row['id'],
					'name' => $row['name'],
					'amtpaid' => $row['amtpaid']);
			$smcFunc['db_free_result']($result);
		}
	}

	// We need to load the inventory template
	$context['sub_template'] = 'inventory';
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_admin_inventory'];
	// Load the administration template
	loadTemplate('ShopAdmin');
}

// Add an item to the shop
function ShopItemsAdd()
{
	global $smcFunc, $boarddir, $context, $db_prefix, $txt, $sourcedir, $scripturl, $item_info;

	// We need the subs!
	require_once($sourcedir . '/shop/Shop-Subs.php');

	// If we're on the first step (list items)
	if (empty($_GET['step']) || $_GET['step'] == 0)
	{
		// Heh, this has moved to the 'Edit Items' section
		// Well, we should redirect this confused user to the correct place :)
		header('Location: ' . $scripturl . '?action=admin;area=shop_items_edit');
		die();
	}
	// Add item, step 1.
	// They've chosen an item, and clicked 'Next'. Prompt for input...
	elseif ($_GET['step'] == 1)
	{
		// Include the item engine (defaults and stuff)
		include($sourcedir . '/shop/item_engine.php');
		// Include the actual item
		require($sourcedir . '/shop/items/' . $_POST['item'] . '.php');
		// Create an instance of the item
		// TODO: Simplify this somehow?
		eval('$tempItem = new item_' . $_POST['item'] . ';');
		// Get the item's details
		$tempItem->getItemDetails();
		// At this stage, there's no additional information
		$item_info = array(
			1 => '',
			2 => '',
			3 => '',
			4 => '');

		// Put all the details into an array
		$context['shop_item'] = array(
			'name' => $_POST['item'],
			'friendlyname' => $tempItem->name,
			'desc' => $tempItem->desc,
			'price' => $tempItem->price,
			'stock' => 50,
			'require_input' => (int) $tempItem->require_input,
			'can_use_item' => (int) $tempItem->can_use_item,
			'delete_after_use' => (int) $tempItem->delete_after_use,
			'authorName' => $tempItem->authorName,
			'authorWeb' => $tempItem->authorWeb,
			'authorEmail' => $tempItem->authorEmail,
			'addInput' => ($tempItem->getAddInput() == false) ? '' : $tempItem->getAddInput(),
		);
		// Images...
		$context['shop_images'] = getImageList();
		// ... and categories
		$context['shop_categories'] = getCatList();
	}
	// Step 2... Input is all set, prepare to add item.
	elseif ($_GET['step'] == 2)
	{
		// If item is not set, something is terribly wrong
		// Probably best to just exit
		if (!isset($_POST['item'])) die();

		// To avoid errors, check for non-existant values and set them to blank
		if (!isset($_POST['info1']))
			$_POST['info1'] = '';
		if (!isset($_POST['info2']))
			$_POST['info2'] = '';
		if (!isset($_POST['info3']))
			$_POST['info3'] = '';
		if (!isset($_POST['info4']))
			$_POST['info4'] = '';

		// If no image selected, default to 'blank.gif'
		if (!isset($_POST['icon']) || $_POST['icon'] == 'none' || $_POST['icon'] == '')
			$_POST['icon'] = 'blank.gif';

		// Check that numeric inputs are indeed numeric
		$_POST['itemprice'] = (float) $_POST['itemprice'];
		$_POST['itemstock'] = (int) $_POST['itemstock'];
		$_POST['require_input'] = $_POST['require_input'] == 1 ? 1 : 0;
		$_POST['can_use_item'] = $_POST['can_use_item'] == 1 ? 1 : 0;
		$delete = isset($_POST['itemdelete']) ? 1 : 0;
		$_POST['cat'] = (int) $_POST['cat'];

		// Insert the actual item
		$smcFunc['db_insert']('insert', '{db_prefix}shop_items',
			array(
				'name' => 'string',
				'desc' => 'string',
				'price' => 'float',
				'module' => 'string',
				'stock' => 'int',
				'input_needed' => 'int',
				'can_use_item' => 'int',
				'delete_after_use' => 'int',
				'info1' => 'string',
				'info2' => 'string',
				'info3' => 'string',
				'info4' => 'string',
				'image' => 'string',
				'category' => 'int',
				),
			array(
				'name' => $_POST['itemname'],
				'desc' => $_POST['itemdesc'],
				'price' => $_POST['itemprice'],
				'module' => $_POST['item'],
				'stock' => $_POST['itemstock'],
				'input_needed' => $_POST['require_input'],
				'can_use_item' => $_POST['can_use_item'],
				'delete_after_use' => $delete,
				'info1' => $_POST['info1'],
				'info2' => $_POST['info2'],
				'info3' => $_POST['info3'],
				'info4' => $_POST['info4'],
				'image' => $_POST['icon'],
				'category' => $_POST['cat'],
				),
			array());
		$id = $smcFunc['db_insert_id']('{db_prefix}shop_items', 'id');

		// Increase count for this category
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}shop_categories
			SET count = count + 1
			WHERE id = {int:id}",
			array(
				'id' => $_POST['cat'],
			));

		// Return to the Edit Items page, and show a message saying it was successful
		header('Location: ' . $scripturl . '?action=admin;area=shop_items_edit;do=add_success&id=' . $id);
		// Since we're redirecting, exit this script
		exit;
	}
	// We are loading the 'Add Items' template
	$context['sub_template'] = 'items_add';
	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_admin_items_add'];
	// Load the template
	loadTemplate('ShopAdmin');
}

// Edit the shop items
function ShopItemsEdit()
{
	global $smcFunc, $context, $txt, $scripturl, $sourcedir, $item_info;

	$context['shop_edit_message'] = '';

	// We need the subs!
	require_once($sourcedir . '/shop/Shop-Subs.php');

	// If we're editing something (they've clicked the 'Edit' link)
	if (isset($_GET['do']) && $_GET['do'] == 'edit')
	{
		// Make sure ID is numeric
		$_GET['id'] = (int) $_GET['id'];
		// Get the item's information
		$result = $smcFunc['db_query']('', "
			SELECT `name`, `desc`, price, stock, image,
				module, info1, info2, info3, info4,
				delete_after_use, category
			FROM {db_prefix}shop_items
			WHERE id = {int:id}",
			array(
				'id' => $_GET['id'],
			));
		$row = $smcFunc['db_fetch_assoc']($result);
		$smcFunc['db_free_result']($result);

		// Set all the information (for use in the template)
		$context['shop_edit'] = array(
			'id' => $_GET['id'],
			'name' => $row['name'],
			'desc' => $row['desc'],
			'price' => $row['price'],
			'stock' => $row['stock'],
			'image' => $row['image'],
			'delete_after_use' => $row['delete_after_use'],
			'category' => $row['category'],
			);
		// Images
		$context['shop_images'] = getImageList();
		// And categories
		$context['shop_categories'] = getCatList();

		// We need to grab the extra input required by this item.
		// The actual information.
		$item_info[1] = $row['info1'];
		$item_info[2] = $row['info2'];
		$item_info[3] = $row['info3'];
		$item_info[4] = $row['info4'];

		// Include the item engine (defaults and stuff)
		include($sourcedir . '/shop/item_engine.php');
		// Include the actual item
		require($sourcedir . '/shop/items/' . $row['module'] . '.php');
		// Create an instance of the item (it's used below)
		eval('$tempItem = new item_' . $row['module'] . ';');
		// Get the actual info
		$tempItem->getItemDetails();

		// Can we edit the getAddInput() info?
		if ($tempItem->addInput_editable == true)
		{
			$context['shop_edit']['addInputEditable'] = true;
			$context['shop_edit']['addInput'] = $tempItem->getAddInput();
		}
		else
			$context['shop_edit']['addInputEditable'] = false;

	}
	// If they've chosen to delete an item
	elseif (isset($_GET['do']) && $_GET['do'] == 'del')
	{
		// If nothing was chosen to delete
		// TODO: Should this just return to the do=edit page, and show the error there?
		if (!isset($_POST['delete']))
			fatal_lang_error('shop_item_delete_error');

		// Make sure all IDs are numeric
		foreach ($_POST['delete'] as $key => $value)
			$_POST['delete'][$key] = (int) $value;

		// Start with an empty array of items
		$context['shop_delete'] = array();

		// Get information on all the items selected to be deleted
		$result = $smcFunc['db_query']('', '
			SELECT id, name
			FROM {db_prefix}shop_items
			WHERE id IN ({array_int:ids})
			ORDER BY name ASC',
			array(
				'ids' => $_POST['delete']
			));

		// Loop through all the results...
		while ($row = $smcFunc['db_fetch_assoc']($result))
			// ... and add them to the array
			$context['shop_delete'][] = array(
				'id' => $row['id'],
				'name' => $row['name']
			);
		$smcFunc['db_free_result']($result);
	}
	// We're neither editing nor deleting an item. In this case, output from whatever we're doing
	// should appear on the main Add/Edit/Delete page.
	else
	{
		// If we're saving changes to the item
		if (isset($_GET['do']) && $_GET['do'] == 'edit2')
		{
			// Make sure some inputs are numeric
			$_POST['id'] = (int) $_POST['id'];
			$_POST['itemprice'] = (float) $_POST['itemprice'];
			$_POST['itemstock'] = (int) $_POST['itemstock'];
			$_POST['cat'] = (int) $_POST['cat'];

			// Delete from inventory after use?
			$delete = isset($_POST['itemdelete']) ? 1 : 0;

			// Additional fields to update
			$additional = '';

			if (isset($_POST['info1']))
				$additional .= ', `info1` = "' . intval($_POST['info1']) . '"';
			if (isset($_POST['info2']))
				$additional .= ', `info2` = "' . intval($_POST['info2']) . '"';
			if (isset($_POST['info3']))
				$additional .= ', `info3` = "' . intval($_POST['info3']) . '"';
			if (isset($_POST['info4']))
				$additional .= ', `info4` = "' . intval($_POST['info4']) . '"';

			// Get the old category
			$result = $smcFunc['db_query']('', "
				SELECT category
				FROM {db_prefix}shop_items
				WHERE id = {int:id}",
				array(
					'id' => $_POST['id'],
				));
			$row = $smcFunc['db_fetch_assoc']($result);
			$smcFunc['db_free_result']($result);

			// Update the item information
			$smcFunc['db_query']('', "
				UPDATE {db_prefix}shop_items
				SET
					`name` = {string:name},
					`desc` = {string:desc},
					`price` = {float:price},
					`stock` = {int:stock},
					`image` = {string:image},
					`delete_after_use` = {int:delete},
					`category` = {int:cat}
					{raw:additional}
				WHERE id = {int:id}
				LIMIT 1",
				array(
					'name' => $_POST['itemname'],
					'desc' => $_POST['itemdesc'],
					'price' => $_POST['itemprice'],
					'stock' => $_POST['itemstock'],
					'image' => $_POST['icon'],
					'delete' => $delete,
					'cat' => $_POST['cat'],
					'additional' => $additional,
					'id' => $_POST['id'],
					));

			// Increase count for the new category
			$smcFunc['db_query']('', "
				UPDATE {db_prefix}shop_categories
				SET count = count + 1
				WHERE id = {int:cat}",
				array(
					'cat' => $_POST['cat'],
				));

			// Decrease count for the old category
			$smcFunc['db_query']('', "
				UPDATE {db_prefix}shop_categories
				SET count = count - 1
				WHERE id = {int:cat}",
				array(
					'cat' => $_POST['cat'],
				));

			// This is used in the template for the message at the top
			$context['shop_edit_message'] = $txt['shop_saved'];
		}
		// Actually deleting an item
		elseif (isset($_GET['do']) && $_GET['do'] == 'del2')
		{
			// If nothing was chosen to delete (shouldn't happen, but meh)
			if (!isset($_POST['delete']))
				fatal_lang_error($txt['shop_item_delete_error']);

			// Make sure all IDs are numeric
			foreach ($_POST['delete'] as $key => $value)
				$_POST['delete'][$key] = (int) $value;

			// Delete all the items
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}shop_items
				WHERE id IN ({array_int:ids})',
				array(
					'ids' => $_POST['delete'],
				));

			// If anyone owned this item, they don't anymore :P
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}shop_inventory
				WHERE itemid IN ({array_int:ids})',
				array(
					'ids' => $_POST['delete'],
				));

			// This is used in the template for the message at the top
			$context['shop_edit_message'] = $txt['shop_deleted'];
		}
		// If they've successfully added an item
		elseif (isset($_GET['do']) && $_GET['do'] == 'add_success')
		{
			// Check if ID is numeric
			$_GET['id'] = (int) $_GET['id'];
			// Pass the message on to the template
			$context['shop_edit_message'] = $txt['shop_added_item'] . ' ' . $_GET['id'];
		}

		// If we got here, it means that we're on the main page (or we returned to it
		// after an action such as deleting an item).

		// OK, the first bit of this is for adding items:
		// Include the 'item engine' (skeleton item, with defaults)
		require($sourcedir . '/shop/item_engine.php');
		// Open the items directory
		if ($handle = opendir($sourcedir . '/shop/items'))
		{
			// Loop through all files in the items directory
			while (false !== ($file = readdir($handle)))
			{
				// If this item is a PHP file, then...
				if (substr($file, -4) == '.php')
				{
					// Get the name (file name without .php extension)
					$name = basename($file, '.php');
					// Load this item
					include($sourcedir . '/shop/items/' . $file);

					// Code to check if object exists, and if so, create new instance of object
					// TODO: Simplify, somehow?
					$code = '
						if (class_exists(\'item_' . $name . '\'))
						{
							$tempItem = new item_' . $name . ';
							return true;
						}
						else
							return false;';

					// If we could create an instance of the item...
					if (eval($code) !== FALSE)
					{
						// Get the item details
						$tempItem->getItemDetails();
						// Add this item to the list
						$context['shop_add'][$tempItem->name] = array(
							'name' => $name,
							'friendlyname' => $tempItem->name,
							'authorName' => $tempItem->authorName,
							'authorWeb' => $tempItem->authorWeb,
							'authorEmail' => $tempItem->authorEmail,
						);
					}
					// Otherwise, this item is broken
					else
					{
						// Inform the user of the sad news... Their item is DEAD!
						$context['shop_edit_message'] .= sprintf($txt['shop_item_error'], $name);
					}
				}
			}
			// Sort the array by key (ie. by item name)
			ksort($context['shop_add']);
		}
		// Otherwise, there's a problem with the items directory
		else
		{
			// Inform the user of this
			fatal_lang_error('shop_cannot_open_items');
		}

		// Now, for the edit items section:
		// Start with an empty list
		$context['shop_edit'] = array();
		// Get a list of all the item
		$result = $smcFunc['db_query']('', "
			SELECT name, id
			FROM {db_prefix}shop_items
			ORDER by name ASC", array());

		// Loop through all the items
		while ($row = $smcFunc['db_fetch_assoc']($result))
			// Add this item to the list
			$context['shop_edit'][] = array(
				'id' => $row['id'],
				'name' => $row['name']
			);
		$smcFunc['db_free_result']($result);
	}

	// Set the page title
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_admin_items_addedit'];
	// Tell the template what we want
	$context['sub_template'] = 'items_edit';
	// Load the template
	loadTemplate('ShopAdmin');

}

// Restock any shop items
function ShopRestock()
{
	global $smcFunc, $boarddir, $context, $txt;

	// If they pressed the button, update the stock
	if (isset($_GET['step']) && $_GET['step'] == 2)
	{
		// Make sure inputs were numeric
		$_POST['amount'] = (int) $_POST['amount'];
		$_POST['lessthan'] = (int) $_POST['lessthan'];

		$smcFunc['db_query']('', "
			UPDATE {db_prefix}shop_items
			SET stock = stock + {int:amount}
			WHERE stock < {int:lessthan}",
			array(
				'amount' => $_POST['amount'],
				'lessthan' => $_POST['lessthan'],
			));
	}

	loadTemplate('ShopAdmin');
	$context['sub_template'] = 'restock';
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_admin_restock'];
}

// Shop membergroup functions
function ShopUserGroup()
{
	global $smcFunc, $boarddir, $context, $txt;

	// If form wasn't submitted yet...
	if (!isset($_GET['step']) || $_GET['step'] == 1)
	{
		// Start with an empty list
		$context['shop_usergroups'] = array();

		// Get all non post-based membergroups
		$result = $smcFunc['db_query']('', "
			SELECT id_group, group_name
			FROM {db_prefix}membergroups
			WHERE min_posts = -1", array());

		// For each membergroup, add it to the list
		while ($row = $smcFunc['db_fetch_assoc']($result))
			$context['shop_usergroups'][] = array(
				'id' => $row['id_group'],
				'groupName' => $row['group_name']
			);
		$smcFunc['db_free_result']($result);
	}
	// If the user has submitted the form
	else
	{
		// Adding, or subtracting?
		$action = ($_POST['m_action'] == 'sub') ? '-' : '+';
		// Make sure inputs were numeric
		$_POST['usergroup'] = (int) $_POST['usergroup'];
		$_POST['value'] = (float) $_POST['value'];
		// Do it!
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}members
			SET money = money {raw:action} {int:value}
			WHERE id_group = {int:group}",
			array(
				'action' => $action,
				'value' => $_POST['value'],
				'group' => $_POST['usergroup'],
			));
		clean_cache();
	}

	// We're using the "usergroup" template
	$context['sub_template'] = 'usergroup';
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_admin_usergroup'];
	// Load the actual template
	loadTemplate('ShopAdmin');
}

// Category management
function ShopCategories()
{
	global $smcFunc, $txt, $context, $db_prefix, $sourcedir;

	// We need the subs!
	require_once($sourcedir . '/shop/Shop-Subs.php');

	// Are we adding a new category?
	if (isset($_GET['do']) && $_GET['do'] == 'add')
	{
		// Make sure a value was set
		if (!isset($_POST['cat_name']) || $_POST['cat_name'] == '')
			fatal_lang_error($txt['shop_enter_cat_name']);

		// Insert the database entry
		$smcFunc['db_insert']('insert', '{db_prefix}shop_categories',
			array(
				'name' => 'string',
				'count' => 'int',
				),
			array(
				'name' => $_POST['cat_name'],
				'count' => 0,
				),
			array());

		$context['shop_cat_message'] = $txt['shop_added_cat'];
	}
	elseif (isset($_GET['do']) && $_GET['do'] == 'del')
	{
		// Delete it!
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}shop_categories
			WHERE ID = {int:id}',
			array(
				'id' => $_GET['id'],
			));
		// Tell the user
		$context['shop_cat_message'] = sprintf($txt['shop_deleted_cat'], $_GET['id']);

	}

	// Get a list of all the categories
	$context['shop_cats'] = getCatList();

	// We're using the "categories" template
	$context['sub_template'] = 'categories';
	$context['page_title'] = $txt['shop'] . ' - ' . $txt['shop_admin_cat'];
	// Load the actual template
	loadTemplate('ShopAdmin');
}
?>
