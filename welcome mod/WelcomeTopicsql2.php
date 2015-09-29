<?php
//SMFHacks.com
//Table SQL

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc;

// Create the Welcome Topic Table
$smcFunc['db_query']('',"CREATE TABLE IF NOT EXISTS {db_prefix}welcome
(ID smallint(5) unsigned NOT NULL auto_increment,
welcomesubject tinytext,
welcomebody text,
PRIMARY KEY (ID))");


// Insert the settings
$smcFunc['db_query']('',"INSERT IGNORE INTO {db_prefix}settings VALUES ('welcome_boardid', '0')");
$smcFunc['db_query']('',"INSERT IGNORE INTO {db_prefix}settings VALUES ('welcome_memberid', '0')");
$smcFunc['db_query']('',"INSERT IGNORE INTO {db_prefix}settings VALUES ('welcome_membername', '')");


?>