<?php
// SMFHacks.com
// Login Security
// By: vbgamer45


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


// Create the Login Security
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}login_security
(
id_member mediumint(8) unsigned NOT NULL, 
allowedips text,
lastfailedlogintime int(10) unsigned NOT NULL default '0',
lockedaccountuntiltime int(10) unsigned NOT NULL default '0',
secureloginhash tinytext,
secureloginhashexpiretime int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (id_member)
)");

$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}login_security_log
(
id_log mediumint(8) NOT NULL auto_increment,
id_member mediumint(8) unsigned NOT NULL default '0',
date int(10) unsigned NOT NULL default '0',
ip tinytext,

PRIMARY KEY  (id_log))");

// Settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ls_securehash_expire_minutes', '30')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ls_allowed_login_attempts', '5')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ls_allowed_login_attempts_mins', '60')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ls_login_retry_minutes', '15')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ls_allow_ip_security', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ls_send_mail_failed_login', '1')");


// Add Package Servers
$smcFunc['db_query']('', "DELETE FROM {db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'");
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')");

	
?>