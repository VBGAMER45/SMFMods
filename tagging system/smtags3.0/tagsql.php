<?php
/*
Tagging System
Version 3.0+stef
http://www.smfhacks.com
  by: vbgamer45 and stefann
  license: this modification is licensed under the Creative Commons BY-NC-SA 3.0 License

Included icons are from Silk Icons 1.3 available at http://www.famfamfam.com/lab/icons/silk/
  and are licensed under the Creative Commons Attribution 2.5 License
*/
// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
        include_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
        die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


//Create Tags Table
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}tags
(ID_TAG mediumint(8) NOT NULL auto_increment,
tag tinytext NOT NULL,
approved tinyint(4) NOT NULL default '0',
`parent_id` MEDIUMINT(8) NULL DEFAULT NULL,
`taggable` TINYINT(4) NOT NULL DEFAULT '1',
PRIMARY KEY  (ID_TAG))", __FILE__, __LINE__);

// Create Tags Log
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}tags_log
(ID int(11) NOT NULL auto_increment,
ID_TAG mediumint(8) unsigned NOT NULL default '0',
ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
approved` TINYINT(4) NOT NULL DEFAULT '0',
PRIMARY KEY  (ID))", __FILE__, __LINE__);

if ($modSettings['smftagsver'] != '3.0') 
{
  // add unique constraint to tags log table for ease of use (functions like merge rely on this)
  db_query("DELETE FROM b USING `{$db_prefix}tags_log` AS a, `{$db_prefix}tags_log` as b WHERE a.id != b.id AND a.id_tag = b.id_tag AND a.id_topic = b.id_topic");
  db_query("ALTER TABLE {$db_prefix}tags_log ADD UNIQUE INDEX(`id_tag`, `id_topic`)");

  // and also to the tags table as a safeguard for stupid cases we must also change to VARCHAR
  db_query("ALTER TABLE  `{$db_prefix}tags` CHANGE  `tag`  `tag` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
  db_query("ALTER TABLE  `{$db_prefix}tags` ADD UNIQUE (`tag`)");

  // approve all pre-existing tags and taggings
  db_query("UPDATE `{$db_prefix}tags` SET approved = 1", __FILE__, __LINE__);
  db_query("UPDATE `{$db_prefix}tags_log` SET approved = 1", __FILE__, __LINE__);
  
  updateSettings(array('smftagsver' => '3.0'));
}


//Insert the settings
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_mintaglength', '3')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_maxtaglength', '64')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_maxtags', '10')", __FILE__, __LINE__);

// Tags Cloud settings
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_cloud_tags_per_row', '5')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_cloud_tags_to_show', '50')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_cloud_max_font_size_precent', '250')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_cloud_min_font_size_precent', '100')", __FILE__, __LINE__);

// default to the former defaults for minimal confusion, but only if not already there
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_listtags', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_manualtags', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_display_top', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_display_bottom', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_listcols', '4')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_cloud_sort','count')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smftags_set_latest_limit','10')", __FILE__, __LINE__);

db_query("DELETE FROM {$db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'", __FILE__, __LINE__);
db_query("REPLACE INTO {$db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')", __FILE__, __LINE__);

?>