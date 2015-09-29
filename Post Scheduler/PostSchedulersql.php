<?php
/*
Post Scheduler
Version 1.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2012 http://www.samsonsoftware.com
*/
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}postscheduler
(ID_POST mediumint(8) NOT NULL auto_increment,
ID_BOARD smallint(5) unsigned NOT NULL default '0',
id_topic mediumint(8) unsigned NOT NULL default '0',
subject varchar(255),
body text NOT NULL,
postername tinytext,
ID_MEMBER mediumint(8) unsigned,
post_time int(10),
msgicon varchar(50) default 'xx',
locked tinyint(1) NOT NULL default '0',
hasposted tinyint(1) NOT NULL default '0',
PRIMARY KEY  (ID_POST),
KEY (post_time)
 )", __FILE__, __LINE__);


// Add Setting
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('post_fakecron', '0')", __FILE__, __LINE__);



	
?>