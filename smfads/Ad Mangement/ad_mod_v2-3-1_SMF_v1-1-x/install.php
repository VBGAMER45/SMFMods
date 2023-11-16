<?php
/******************************************************************************
* SMF: Simple Machines Forum - Ad Management Mod                              *
*                                                                             *
* =========================================================================== *
* Software Version:           Ad mod: 3.1                                     *
* Software by:                smfhacks.com                                    *
* Copyright 2010-2013 by:     smfhacks.com                                    *
* Support site:               www.smfads.com                                  *
*******************************************************************************
* This mod is free software; you may not redistribute or provide a modified   *
* version to redistribute.  This mod is distributed in the hope that it is    *
* and will be useful, but WITHOUT ANY WARRANTIES; without even any implied    *
* warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            *
******************************************************************************/

// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


/*
Installing the ad mod for the first time
*/
db_query("
CREATE TABLE IF NOT EXISTS {$db_prefix}ads (
ADS_ID mediumint(8) unsigned NOT NULL auto_increment,
NAME tinytext NOT NULL,
CONTENT text NOT NULL,
BOARDS tinytext,
POSTS tinytext,
CATEGORY tinytext,
HITS bigint NOT NULL default '0',
TYPE smallint(4) NOT NULL default '0',
show_index smallint(4) NOT NULL default '0',
show_board smallint(4) NOT NULL default '0',
show_threadindex smallint(4) NOT NULL default '0',
show_lastpost smallint(4) NOT NULL default '0',
show_thread smallint(4) NOT NULL default '0',
show_bottom smallint(4) NOT NULL default '0',
show_welcome smallint(4) NOT NULL default '0',
show_topofpage smallint(4) NOT NULL default '0',
show_towerright smallint(4) NOT NULL default '0',
show_towerleft smallint(4) NOT NULL default '0',
show_betweencategories smallint(4) NOT NULL default '0',
show_underchildren smallint(4) NOT NULL default '0',
PRIMARY KEY (ADS_ID)
)", __FILE__, __LINE__);

db_query("
CREATE TABLE IF NOT EXISTS {$db_prefix}ads_settings (
  variable tinytext NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (variable(30))
)", __FILE__, __LINE__);

/*
If you've already had the mod installed this will just make sure that you have the most current version of the database
*/
$result = db_query("SHOW COLUMNS FROM {$db_prefix}ads LIKE 'show_lastpost'", __FILE__, __LINE__);
if (mysql_num_rows($result) == 0)
	db_query("
		ALTER IGNORE TABLE {$db_prefix}ads
		ADD	show_lastpost smallint(4) NOT NULL default '0' AFTER show_threadindex", __FILE__, __LINE__);

$result = db_query("SHOW COLUMNS FROM {$db_prefix}ads LIKE 'CATEGORY'", __FILE__, __LINE__);
if (mysql_num_rows($result) == 0)
	db_query("
		ALTER IGNORE TABLE {$db_prefix}ads
		ADD	CATEGORY tinytext AFTER POSTS", __FILE__, __LINE__);

$result = db_query("SHOW COLUMNS FROM {$db_prefix}ads LIKE 'show_underchildren'", __FILE__, __LINE__);
if (mysql_num_rows($result) == 0)
	db_query("
		ALTER IGNORE TABLE {$db_prefix}ads
		ADD	show_underchildren smallint(4) NOT NULL default '0'", __FILE__, __LINE__);


/*
Inserts into the settings table
*/
db_query("
INSERT IGNORE INTO {$db_prefix}settings
	(variable, value)
VALUES ('ads_displayAdsAdmin', '1'),
	('ads_updateReports', '0'),
	('ads_quickDisable', '0'),
	('ads_lookLikePosts', '1'),
    ('ads_copyrightkey','')
    ", __FILE__, __LINE__);


// Permissions

// Permissions array
$permissions = array(
	'ad_manageperm' => array(-1, 0, 2), // ALL
);

addPermissions($permissions);

function addPermissions($permissions)
{
	global $db_prefix;

	foreach ($permissions as $permission => $default)
	{
		$result = db_query("
			SELECT COUNT(*)
			FROM {$db_prefix}permissions
			WHERE permission = '$permission'", __FILE__, __LINE__);

		list ($num) = mysql_free_result($result);

		if ($num == 0)
		{
			foreach ($default as $ID_GROUP)
			{
				db_query("
				INSERT IGNORE INTO {$db_prefix}permissions
					(id_group, permission)
				VALUES ('$ID_GROUP', '$permission')", __FILE__, __LINE__);

			}

		}
	}



}
?>
