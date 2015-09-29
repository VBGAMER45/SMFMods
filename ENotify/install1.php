<?php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


db_query("
CREATE TABLE IF NOT EXISTS {$db_prefix}log_enotify_pms (
  id_enot mediumint(8) NOT NULL auto_increment,
  enot_item_id mediumint(8) NOT NULL,
  enot_title varchar(255) NOT NULL,
  enot_time int(10) NOT NULL,
  enot_link tinytext NOT NULL,
  enot_sender varchar(255) NOT NULL,
  enot_sender_link text NOT NULL,
  id_member mediumint(8) NOT NULL,
  enot_read tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (id_enot),
  UNIQUE KEY enot_item_id (enot_item_id)
)", __FILE__, __LINE__);

db_query("
CREATE TABLE IF NOT EXISTS {$db_prefix}log_enotify_replies (
  id_enot mediumint(8) NOT NULL auto_increment,
  enot_item_id mediumint(8) NOT NULL,
  enot_title varchar(255) NOT NULL,
  enot_time int(10) NOT NULL,
  enot_link tinytext NOT NULL,
  enot_sender varchar(255) NOT NULL,
  enot_sender_link text NOT NULL,
  id_member mediumint(8) NOT NULL,
  enot_read tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (id_enot),
  UNIQUE KEY enot_item_id (enot_item_id)
)", __FILE__, __LINE__);

db_query("
INSERT IGNORE INTO {$db_prefix}settings
	(variable, value)
VALUES ('enotify_life', '5000'),
	('enotify_refresh', '10000'),
	('enotify_pms', '1'),
	('enotify_replies', '1'),
	('enotify_exp', '48')", __FILE__, __LINE__);

?>
