<?php
// SMFShop uninstall script, version 3.0 (Build 12)
// $Date: 2007-01-18 19:26:55 +1100 (Thu, 18 Jan 2007) $
// $Id: uninstallScript.php 79 2007-01-18 08:26:55Z daniel15 $


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot UNinstall - please verify you put this in the same place as SMF\'s index.php.');
	
db_query("DROP TABLE `{$db_prefix}shop_items`", __FILE__, __LINE__);
db_query("DROP TABLE `{$db_prefix}shop_inventory`", __FILE__, __LINE__);
db_query("DROP TABLE `{$db_prefix}shop_categories`", __FILE__, __LINE__);

db_query("ALTER TABLE `{$db_prefix}members` DROP `money`", __FILE__, __LINE__);
db_query("ALTER TABLE `{$db_prefix}members` DROP `moneyBank`", __FILE__, __LINE__);
db_query("ALTER TABLE `{$db_prefix}boards` DROP `countMoney`", __FILE__, __LINE__);

db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopVersion'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopBuild'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopDate'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopCurrencyPrefix'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopCurrencySuffix'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopPointsPerTopic'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopPointsPerPost'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopInterest'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopBankEnabled'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopImageWidth'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopImageHeight'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopTradeEnabled'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopItemsPerPage'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopMinDeposit'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopMinWithdraw'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopRegAmount'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopPointsPerWord'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopPointsPerChar'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopPointsLimit'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopFeeDeposit'", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings` WHERE variable = 'shopFeeWithdraw'", __FILE__, __LINE__);
?>