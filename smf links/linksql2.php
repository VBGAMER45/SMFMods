<?php
//SMFHacks.com
//Table SQL

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


//Create category SQL
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}links_cat
(ID_CAT mediumint(8) NOT NULL auto_increment,
title VARCHAR(100) NOT NULL,
description TEXT NOT NULL,
roworder mediumint(8) unsigned NOT NULL default '0',
ID_PARENT mediumint(8) unsigned NOT NULL default '0',
image tinytext,
PRIMARY KEY  (ID_CAT),
INDEX idx_parent (ID_PARENT),
INDEX idx_roworder (roworder)) ENGINE=InnoDB");

//Create Links Table
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}links
(ID_LINK int(11) NOT NULL auto_increment,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
ID_CAT mediumint(8) unsigned NOT NULL default '0',
url VARCHAR(255) NOT NULL,
image tinytext,
title VARCHAR(100) NOT NULL,
description TEXT NOT NULL,
hits int(11) NOT NULL default '0',
approved tinyint(1) NOT NULL default '0',
rating int(11) NOT NULL default '0',
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (ID_LINK),
INDEX idx_cat (ID_CAT),
INDEX idx_member (ID_MEMBER),
INDEX idx_approved (approved)) ENGINE=InnoDB");

$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}links_rating
(ID int(11) NOT NULL auto_increment,
ID_LINK int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
value tinyint(1) NOT NULL,
PRIMARY KEY  (ID),
INDEX idx_link (ID_LINK),
INDEX idx_member (ID_MEMBER),
UNIQUE INDEX idx_link_member (ID_LINK, ID_MEMBER)) ENGINE=InnoDB");


$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}links_catperm
(ID mediumint(8) NOT NULL auto_increment,
ID_GROUP mediumint(8) NOT NULL default '0',
ID_CAT mediumint(8) unsigned NOT NULL default '0',
view tinyint(4) NOT NULL default '0',
addlink tinyint(4) NOT NULL default '0',
editlink tinyint(4) NOT NULL default '0',
dellink tinyint(4) NOT NULL default '0',
ratelink tinyint(4) NOT NULL default '0',
report tinyint(4) NOT NULL default '0',
PRIMARY KEY  (ID),
INDEX idx_cat (ID_CAT),
INDEX idx_group (ID_GROUP),
UNIQUE INDEX idx_group_cat (ID_GROUP, ID_CAT)) ENGINE=InnoDB");

//Insert the settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_setlinksperpage', '10')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_setshowtoprate', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_setshowmostvisited', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_setshowstats', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_set_count_child', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_setallowbbc', '0')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_disp_description', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_disp_hits', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_disp_rating', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_disp_membername', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_disp_date', '1')");

// Clean up obsolete settings from older versions
$smcFunc['db_query']('', "DELETE FROM {db_prefix}settings WHERE variable IN ('smflinks_setgetpr', 'smflinks_setgetalexa', 'smflinks_disp_alexa', 'smflinks_disp_pagerank')");

//Upgrade the database if older version
$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}links_cat");
$ID_PARENT = 1;
$image = 1;
while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if($row[0] == 'ID_PARENT')
		$ID_PARENT = 0;
	if($row[0] == 'image')
		$image = 0;
}
$smcFunc['db_free_result']($dbresult);

if($ID_PARENT)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}links_cat ADD ID_PARENT mediumint(8) unsigned NOT NULL default '0'");
if($image)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}links_cat ADD image tinytext");

// Upgrade existing tables from MyISAM to InnoDB if needed
$smcFunc['db_query']('', "ALTER TABLE {db_prefix}links_cat ENGINE=InnoDB");
$smcFunc['db_query']('', "ALTER TABLE {db_prefix}links ENGINE=InnoDB");
$smcFunc['db_query']('', "ALTER TABLE {db_prefix}links_rating ENGINE=InnoDB");
$smcFunc['db_query']('', "ALTER TABLE {db_prefix}links_catperm ENGINE=InnoDB");

// Upgrade description columns from VARCHAR(255) to TEXT if needed
$smcFunc['db_query']('', "ALTER TABLE {db_prefix}links_cat MODIFY description TEXT NOT NULL");
$smcFunc['db_query']('', "ALTER TABLE {db_prefix}links MODIFY description TEXT NOT NULL");

// v5.0: Add columns for link checker
$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}links");
$has_last_checked = false;
$has_last_status = false;
$has_check_fails = false;
while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if ($row[0] == 'last_checked')
		$has_last_checked = true;
	if ($row[0] == 'last_status')
		$has_last_status = true;
	if ($row[0] == 'check_fails')
		$has_check_fails = true;
}
$smcFunc['db_free_result']($dbresult);

if (!$has_last_checked)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}links ADD last_checked int(10) unsigned NOT NULL default '0'");
if (!$has_last_status)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}links ADD last_status smallint(5) NOT NULL default '0'");
if (!$has_check_fails)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}links ADD check_fails smallint(5) unsigned NOT NULL default '0'");

// v5.0: Disallowed domains table for link checker
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}links_disallowed_domains
(domain VARCHAR(75) NOT NULL,
PRIMARY KEY (domain)) ENGINE=InnoDB");

// v5.0: Thumbnail setting
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_disp_thumbnail', '0')");

// v5.0: Link checker settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_check_batch_size', '25')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smflinks_check_notify_pm', '0')");

// Add Package Servers
$smcFunc['db_query']('', "DELETE FROM {db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'");
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'https://www.smfhacks.com')");


// Permissions array
$permissions = array(
	'view_smflinks' => array(-1, 0, 2), // ALL
);

addPermissions($permissions);

function addPermissions($permissions)
{
	global $smcFunc;

	$perm = array();

	foreach ($permissions as $permission => $default)
	{
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}permissions
			WHERE permission = {string:permission}',
			array(
				'permission' => $permission
			)
		);

		list ($num) = $smcFunc['db_fetch_row']($result);

		if ($num == 0)
		{
			foreach ($default as $grp)
				$perm[] = array($grp, $permission);
		}
	}

	if (empty($perm))
		return;

	$smcFunc['db_insert']('insert',
		'{db_prefix}permissions',
		array(
			'id_group' => 'int',
			'permission' => 'string'
		),
		$perm,
		array()
	);
}

?>