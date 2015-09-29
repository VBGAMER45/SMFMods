<?php
// SMFShop install script, version 3.1
// http://www.smfhacks.com/smfshop/


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

// Shop items table
db_query("
	CREATE TABLE IF NOT EXISTS {$db_prefix}shop_items (
		id INT UNSIGNED NOT NULL AUTO_INCREMENT,
		name VARCHAR(50) NOT NULL,
		`desc` TEXT NOT NULL,
		price DECIMAL(8,2) UNSIGNED NOT NULL,
		module TINYTEXT NOT NULL,
		stock SMALLINT NOT NULL,
		info1 TEXT NOT NULL,
		info2 TEXT NOT NULL,
		info3 TEXT NOT NULL,
		info4 TEXT NOT NULL,
		input_needed TINYINT UNSIGNED DEFAULT '1' NOT NULL,
		can_use_item TINYINT UNSIGNED DEFAULT '1' NOT NULL,
		delete_after_use TINYINT(1) UNSIGNED DEFAULT '1' NOT NULL,
		image TINYTEXT NOT NULL,
		category SMALLINT NOT NULL,
		PRIMARY KEY (id)
	)", __FILE__, __LINE__);

// Shop Inventory table
db_query("
	CREATE TABLE IF NOT EXISTS {$db_prefix}shop_inventory (
		id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
		ownerid INT UNSIGNED NOT NULL ,
		itemid INT UNSIGNED NOT NULL ,
		amtpaid DECIMAL(8, 2) UNSIGNED DEFAULT '0.00' NOT NULL,
		trading TINYINT(1) UNSIGNED NOT NULL,
		tradecost DECIMAL(8, 2) NOT NULL,
		PRIMARY KEY (id)
	)", __FILE__, __LINE__);
	
// Category table
db_query("
	CREATE TABLE IF NOT EXISTS {$db_prefix}shop_categories (
		id SMALLINT( 5 ) UNSIGNED NOT NULL  AUTO_INCREMENT,
		name VARCHAR( 50 ) NOT NULL ,
		count INT UNSIGNED NOT NULL ,
		PRIMARY KEY ( id ) 
	)", __FILE__, __LINE__);
			
// Settings used in $modSettings array
db_query("REPLACE INTO {$db_prefix}settings (variable,value) VALUES ('shopVersion', '3.1.1')", __FILE__, __LINE__);
db_query("REPLACE INTO {$db_prefix}settings (variable,value) VALUES ('shopDate', 'May 20, 2009')", __FILE__, __LINE__);
db_query("REPLACE INTO {$db_prefix}settings (variable,value) VALUES ('shopBuild', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopCurrencyPrefix', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopCurrencySuffix', ' credits')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopPointsPerTopic', '10')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopPointsPerPost', '8')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopInterest', '2')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopBankEnabled', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopImageWidth', '32'), ('shopImageHeight', '32')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable, value) VALUES ('shopTradeEnabled', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable, value) VALUES ('shopItemsPerPage', '10')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable, value) VALUES ('shopMinDeposit', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable, value) VALUES ('shopMinWithdraw', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable, value) VALUES ('shopRegAmount', '0')", __FILE__, __LINE__);

// New settings in SMFShop New Version (Build 12)
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopPointsPerWord', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopPointsPerChar', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopPointsLimit', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopFeeWithdraw', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings (variable,value) VALUES ('shopFeeDeposit', '0')", __FILE__, __LINE__);

//SMFShop 1.0 items
db_query("INSERT IGNORE INTO {$db_prefix}shop_items ( id , name , `desc` , price , module , stock ) VALUES (
'', 'Test Item', 'Just a test item!', '10.00', 'testitem', '50')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}shop_items ( id , name , `desc` , price , module , stock ) VALUES (
'', 'Random Money', 'Get a random amount of money, between -190 and 190!', '75', 'RandomMoney', '50')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}shop_items ( id , name , `desc` , price , module , stock ) VALUES (
'', 'Add 100 to Post Count', 'Increase your Post Count by 100!', '50', 'AddToPostCount', '50')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}shop_items ( id , name , `desc` , price , module , stock ) VALUES (
'', 'Increase Karma', 'Increase your Karma by 5', '100', 'IncreaseKarma', '50')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}shop_items ( id , name , `desc` , price , module , stock ) VALUES (
'', 'Change Username', 'Change your Username!', '50', 'ChangeUsername', '50')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}shop_items ( id , name , `desc` , price , module , stock ) VALUES (
'', 'Change User Title', 'Change your User Title', '50', 'ChangeUserTitle', '50')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}shop_items ( id , name , `desc` , price , module , stock ) VALUES (
'', 'Increase Total Time', 'Increase your total time logged in by 12 hours.', '50', 'IncreaseTimeLoggedIn', '50')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}shop_items ( id , name , `desc` , price , module , stock ) VALUES (
'', 'Change Other\\'s Title',  'Change someone else\\'s title', '200', 'ChangeOtherTitle', '50')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}shop_items ( id , name , `desc` , price , module , stock ) VALUES (
'', 'Decrease Posts by 100', 'Decrease <i>Someone else\\'s</i> post count by 100!!', '200', 'DecreasePost', '50')", __FILE__, __LINE__);

//updating pre-1.1 items to 1.1 items
db_query("UPDATE {$db_prefix}shop_items SET info1 = '100', input_needed = 0 WHERE module = 'AddToPostCount'", __FILE__, __LINE__);
db_query("UPDATE {$db_prefix}shop_items SET info1 = '5', input_needed = 0 WHERE module = 'IncreaseKarma'", __FILE__, __LINE__);
db_query("UPDATE {$db_prefix}shop_items SET info1 = '43200', input_needed = 0 WHERE module = 'IncreaseTimeLoggedIn'", __FILE__, __LINE__);
db_query("UPDATE {$db_prefix}shop_items SET info1 = '-190', info2 = '190', input_needed = 0 WHERE module = 'RandomMoney'", __FILE__, __LINE__);
db_query("UPDATE {$db_prefix}shop_items SET info1 = '40', input_needed = 1 WHERE module = 'Steal'", __FILE__, __LINE__);

//SMFShop 1.1 items
db_query("INSERT IGNORE INTO {$db_prefix}shop_items ( id , name , `desc` , price , module , stock, info1, input_needed, can_use_item) VALUES ('', 'Steal Credits', 'Try to steal credits from another member!', 50, 'Steal', 50, '40', 1, 1)", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}shop_items ( id , name , `desc` , price , module , stock, input_needed, can_use_item) VALUES ('', 'Rock', 'Well.... It does nothing', 5, 'Rock', 50, 0, 0)", __FILE__, __LINE__);

//SMFShop 1.2 items
db_query("INSERT IGNORE INTO {$db_prefix}shop_items (name, `desc`, price, module, stock, input_needed, can_use_item) VALUES (
'Change Display Name', 'Change your display name!', 50.00, 'ChangeDisplayName', 49, 1, 1)", __FILE__, __LINE__);

//SMFShop 2.1 items
db_query("INSERT IGNORE INTO {$db_prefix}shop_items (name, `desc`, price, module, stock, input_needed, can_use_item) VALUES ('Sticky Topic', 'Make any one of your topics a sticky!', 400.00, 'StickyTopic', 50, 1, 1)", __FILE__, __LINE__);

//default all items to use 'blank.gif'
db_query("UPDATE {$db_prefix}shop_items SET image = 'blank.gif'", __FILE__, __LINE__);

//add money columns to members table, if they don't already exist
$result = db_query("SHOW COLUMNS FROM {$db_prefix}members LIKE 'money'", __FILE__, __LINE__);
if (mysql_num_rows($result) == 0) 
	db_query("ALTER TABLE {$db_prefix}members ADD money DECIMAL(9, 2) UNSIGNED DEFAULT '0.00' NOT NULL", __FILE__, __LINE__);
	
$result = db_query("SHOW COLUMNS FROM {$db_prefix}members LIKE 'moneyBank'", __FILE__, __LINE__);
if (mysql_num_rows($result) == 0) 	
	db_query("ALTER TABLE {$db_prefix}members ADD moneyBank DECIMAL(9, 2) UNSIGNED DEFAULT '0.00' NOT NULL", __FILE__, __LINE__);

//give admin money :-)
db_query("UPDATE {$db_prefix}members SET money = '10000' WHERE ID_MEMBER = '1' LIMIT 1", __FILE__, __LINE__);

//New field in boards table. This specifies whether credits is increased in this board or not.
$result = db_query("SHOW COLUMNS FROM {$db_prefix}boards LIKE 'countMoney'", __FILE__, __LINE__);
if (mysql_num_rows($result) == 0) 
	db_query("ALTER TABLE {$db_prefix}boards ADD countMoney TINYINT(1) UNSIGNED DEFAULT '1' NOT NULL", __FILE__, __LINE__);

// Field for custom credits per topic
$result = db_query("SHOW COLUMNS FROM {$db_prefix}boards LIKE 'shop_pertopic'", __FILE__, __LINE__);
if (mysql_num_rows($result) == 0) 
	db_query("ALTER TABLE {$db_prefix}boards ADD `shop_pertopic` DECIMAL (9, 2) UNSIGNED NOT NULL", __FILE__, __LINE__);

// Field for custom credits per post
$result = db_query("SHOW COLUMNS FROM {$db_prefix}boards LIKE 'shop_perpost'", __FILE__, __LINE__);
if (mysql_num_rows($result) == 0) 
	db_query("ALTER TABLE {$db_prefix}boards ADD `shop_perpost` DECIMAL (9, 2) UNSIGNED NOT NULL", __FILE__, __LINE__);

// Field for whether bonuses are enabled in this board
$result = db_query("SHOW COLUMNS FROM {$db_prefix}boards LIKE 'shop_bonuses'", __FILE__, __LINE__);
if (mysql_num_rows($result) == 0) 
	db_query("ALTER TABLE {$db_prefix}boards ADD `shop_bonuses` TINYINT (1) UNSIGNED NOT NULL DEFAULT '1'", __FILE__, __LINE__);


	
// ---------- Insert the permissions --------------
// Initialise the values array. Give the permissions to all ungrouped members (ID_GROUP = 0)
$values = array("
		('shop_main', 0, 1),
		('shop_buy', 0, 1),
		('shop_invother', 0, 1),
		('shop_sendmoney', 0, 1),
		('shop_senditems', 0, 1),
		('shop_bank', 0, 1),
		('shop_trade', 0, 1)");

// Get all the non-postcount based groups.
$request = db_query("
	SELECT ID_GROUP
	FROM {$db_prefix}membergroups
	WHERE minPosts = -1", __FILE__, __LINE__);
while ($row = mysql_fetch_assoc($request))
	// Add this to the values we need
	$values[] = "
		('shop_main', $row[ID_GROUP], 1),
		('shop_buy', $row[ID_GROUP], 1),
		('shop_invother', $row[ID_GROUP], 1),
		('shop_sendmoney', $row[ID_GROUP], 1),
		('shop_senditems', $row[ID_GROUP], 1),
		('shop_bank', $row[ID_GROUP], 1),
		('shop_trade', $row[ID_GROUP], 1)";
		
		
// Give them all their new permission.
db_query("
	INSERT IGNORE INTO {$db_prefix}permissions
		(permission, ID_GROUP, addDeny)
	VALUES
		" . implode(', ', $values), __FILE__, __LINE__);
?>
