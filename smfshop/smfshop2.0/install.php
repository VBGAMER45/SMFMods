<?php
/*
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

global $smcFunc, $db_prefix;

// New settings for the shop mod
$newSettings = array(
	'shopVersion' => '3.0',
	'shopDate' => '23th November 2009',
	'shopBuild' => '15',
	'shopCurrencyPrefix' => '',
	'shopCurrencySuffix' => ' credits',
	'shopPointsPerTopic' => '10',
	'shopPointsPerPost' => '8',
	'shopInterest' => '2',
	'shopBankEnabled' => '1',
	'shopImageWidth' => '32',
	'shopImageHeight' => '32',
	'shopTradeEnabled' => '1',
	'shopItemsPerPage' => '10',
	'shopMinDeposit' => '0',
	'shopMinWithdraw' => '0',
	'shopRegAmount' => '0',
	'shopPointsPerWord' => '0',
	'shopPointsPerChar' => '0',
	'shopPointsLimit' => '0',
	'shopFeeWithdraw' => '0',
	'shopFeeDeposit' => '0',
	);

// Insert them into the database
// !!! This is done evily like this!
foreach ($newSettings as $variable => $value)
{
	$smcFunc['db_insert']('replace', '{db_prefix}settings',
		array(
			'variable' => 'string',
			'value' => 'string',
			),
		array(
			'variable' => $variable,
			'value' => $value,
			),
		array()
	);
}

// Add a column for money
$smcFunc['db_add_column']('{db_prefix}members', array(
	'name' => 'money',
	'type' => 'decimal',
	'default' => '0.00',
	));

// Add a column for banked money
$smcFunc['db_add_column']('{db_prefix}members', array(
	'name' => 'moneyBank',
	'type' => 'decimal',
	'default' => '0.00',
	));
	
	
// Modify Boards
$smcFunc['db_add_column']('{db_prefix}boards', array(
	'name' => 'countMoney',
	'type' => 'tinyint',
	'size' => 1,
	'default' => '1',
	));	
	
	
$smcFunc['db_add_column']('{db_prefix}boards', array(
	'name' => 'shop_pertopic',
	'type' => 'decimal',
	'size' => '9,2',
	'default' => '0.00',
	));		
	
	
$smcFunc['db_add_column']('{db_prefix}boards', array(
	'name' => 'shop_perpost',
	'type' => 'decimal',
	'size' => '9,2',
	'default' => '0.00',
	));		
		

$smcFunc['db_add_column']('{db_prefix}boards', array(
	'name' => 'shop_bonuses',
	'type' => 'tinyint',
	'size' => 1,
	'default' => '1',
	));	
	

// Item table
$smcFunc['db_create_table']('{db_prefix}shop_items',
	array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 10,
			'auto' => true,
		),
		array(
			'name' => 'name',
			'type' => 'varchar',
			'size' => 50,
		),
		array(
			'name' => 'desc',
			'type' => 'text',
		),
		array(
			'name' => 'price',
			'type' => 'decimal',
			'size' => '8,2',
			'default' => '0.00',
		),
		array(
			'name' => 'module',
			'type' => 'tinytext',
		),
		array(
			'name' => 'stock',
			'type' => 'smallint',
			'size' => 6,
		),
		array(
			'name' => 'info1',
			'type' => 'text',
			'null' => true,
		),
		array(
			'name' => 'info2',
			'type' => 'text',
			'null' => true,
		),
		array(
			'name' => 'info3',
			'type' => 'text',
			'null' => true,
		),
		array(
			'name' => 'info4',
			'type' => 'text',
			'null' => true,
		),
		array(
			'name' => 'input_needed',
			'type' => 'tinyint',
			'size' => 1,
			'default' => 0,
		),
		array(
			'name' => 'can_use_item',
			'type' => 'tinyint',
			'size' => 1,
			'default' => 0,
		),
		array(
			'name' => 'delete_after_use',
			'type' => 'tinyint',
			'size' => 1,
			'default' => 0,
		),
		array(
			'name' => 'image',
			'type' => 'tinytext',
			'null' => true,
		),
		array(
			'name' => 'category',
			'type' => 'smallint',
			'size' => 6,
			'default' => 0,
		),
	),
	array(
		array(
			'name' => 'id',
			'type' => 'primary',
			'columns' => array('id'),
		),
	),
	array(),
	'overwrite');

// Inventory table
$smcFunc['db_create_table']('{db_prefix}shop_inventory',
	array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 10,
			'auto' => true,
		),
		array(
			'name' => 'ownerid',
			'type' => 'int',
			'size' => 10,
		),
		array(
			'name' => 'itemid',
			'type' => 'int',
			'size' => 10,
		),
		array(
			'name' => 'amtpaid',
			'type' => 'decimal',
			'size' => '8,2',
			'default' => '0.00',
		),
		array(
			'name' => 'trading',
			'type' => 'tinyint',
			'size' => 1,
		),
		array(
			'name' => 'tradecost',
			'type' => 'decimal',
			'size' => '8,2',
			'default' => '0.00',
		),
	),
	array(
		array(
			'name' => 'id',
			'type' => 'primary',
			'columns' => array('id'),
		),
	),
	array(),
	'overwrite');

// Category table
$smcFunc['db_create_table']('{db_prefix}shop_categories',
	array(
		array(
			'name' => 'id',
			'type' => 'smallint',
			'size' => 5,
			'auto' => true,
		),
		array(
			'name' => 'name',
			'type' => 'varchar',
			'size' => 50,
		),
		array(
			'name' => 'count',
			'type' => 'int',
			'size' => 10,
		),
	),
	array(
		array(
			'name' => 'id',
			'type' => 'primary',
			'columns' => array('id'),
		),
	),
	array(),
	'overwrite');

// Insert shop items
$smcFunc['db_insert']('insert', '{db_prefix}shop_items',

	// Fields
	array(
		'name' => 'string',
		'desc' => 'string',
		'price' => 'float',
		'module' => 'string',
		'stock' => 'int',
		'image' => 'string',
		'info1' => 'int',
		'info2' => 'int',
		'input_needed' => 'int',
		'can_use_item' => 'int',
		'delete_after_use' => 'int',
		),

	// Values
	array(
		// testitem
		array(
			'name' => 'Test Item',
			'desc' => 'Just a test item!',
			'price' => 10.00,
			'module' => 'testitem',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 0,
			'info2' => 0,
			'input_needed' => 0,
			'can_use_item' => 0,
			'delete_after_use' => 0,
			),

		// RandomMoney
		array(
			'name' => 'Random Money',
			'desc' => 'Get a random amount of money, between -190 and 190!',
			'price' => 75,
			'module' => 'RandomMoney',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => -190,
			'info2' => 190,
			'input_needed' => 0,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// AddToPostCount
		array(
			'name' => 'Add 100 to Post Count',
			'desc' => 'Increase your Post Count by 100!',
			'price' => 50,
			'module' => 'AddToPostCount',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 100,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// IncreaseKarma
		array(
			'name' => 'Increase Karma',
			'desc' => 'Increase your Karma by 5',
			'price' => 100,
			'module' => 'IncreaseKarma',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 5,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// ChangeUsername
		array(
			'name' => 'Change Username',
			'desc' => 'Change your Username!',
			'price' => 50,
			'module' => 'ChangeUsername',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 0,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// ChangeUserTitle
		array(
			'name' => 'Change User Title',
			'desc' => 'Change your User Title',
			'price' => 50,
			'module' => 'ChangeUserTitle',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 0,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// IncreaseTimeLoggedIn
		array(
			'name' => 'Increase Total Time',
			'desc' => 'Increase your total time logged in by 12 hours.',
			'price' => 50,
			'module' => 'IncreaseTimeLoggedIn',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 43200,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// ChangeOtherTitle
		array(
			'name' => 'Change Other\'s Title',
			'desc' => 'Change someone else\'s title',
			'price' => 200,
			'module' => 'ChangeOtherTitle',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 0,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// DecreasePost
		array(
			'name' => 'Decrease Posts by 100',
			'desc' => 'Decrease <i>Someone else\'s</i> post count by 100!',
			'price' => 200,
			'module' => 'DecreasePost',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 0,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// StickyTopic
		array(
			'name' => 'Sticky Topic',
			'desc' => 'Make any one of your topics a sticky!',
			'price' => 400,
			'module' => 'StickyTopic',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 0,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// StickyTopic
		array(
			'name' => 'Sticky Topic',
			'desc' => 'Make any one of your topics a sticky!',
			'price' => 400,
			'module' => 'StickyTopic',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 0,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// StickyTopic
		array(
			'name' => 'Steal Credits',
			'desc' => 'Try to steal credits from another member!',
			'price' => 50,
			'module' => 'Steal',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 40,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// Rock
		array(
			'name' => 'Rock',
			'desc' => 'Well... It does nothing',
			'price' => 5,
			'module' => 'Rock',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 40,
			'info2' => 0,
			'input_needed' => 0,
			'can_use_item' => 0,
			'delete_after_use' => 0,
			),

		// ChangeDisplayName
		array(
			'name' => 'Change Display Name',
			'desc' => 'Change your display name!',
			'price' => 5,
			'module' => 'ChangeDisplayName',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 0,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),

		// ChangeDisplayName
		array(
			'name' => 'Change Display Name',
			'desc' => 'Change your display name!',
			'price' => 5,
			'module' => 'ChangeDisplayName',
			'stock' => 50,
			'image' => 'blank.gif',
			'info1' => 0,
			'info2' => 0,
			'input_needed' => 1,
			'can_use_item' => 1,
			'delete_after_use' => 1,
			),
		),
	array());
?>