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
global $smcFunc;

$smcFunc['db_query']('', "
CREATE TABLE IF NOT EXISTS {db_prefix}ads (
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
)");

$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ads CHANGE HITS HITS bigint NOT NULL default '0'");



$smcFunc['db_query']('', "
CREATE TABLE IF NOT EXISTS {db_prefix}ads_settings (
  variable tinytext NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (variable(30))
)");

/*
If you've already had the mod installed this will just make sure that you have the most current version of the database
*/
$result = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}ads LIKE 'show_lastpost'");
if ($smcFunc['db_fetch_assoc']($result) == 0)
	$smcFunc['db_query']('',"
		ALTER IGNORE TABLE {db_prefix}ads
		ADD	show_lastpost smallint(4) NOT NULL default '0' AFTER show_threadindex");

$result = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}ads LIKE 'CATEGORY'");
if ($smcFunc['db_fetch_assoc']($result) == 0)	
	$smcFunc['db_query']('',"
		ALTER IGNORE TABLE {db_prefix}ads
		ADD	CATEGORY tinytext AFTER POSTS");

$result = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}ads LIKE 'show_underchildren'");
if ($smcFunc['db_fetch_assoc']($result) == 0)		
	$smcFunc['db_query']('',"
		ALTER IGNORE TABLE {db_prefix}ads
		ADD	show_underchildren smallint(4) NOT NULL default '0'");

	
/*
Inserts into the settings table
*/
$smcFunc['db_query']('', "
INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES ('ads_displayAdsAdmin', '1'),
	('ads_updateReports', '0'),
	('ads_quickDisable', '0'),
	('ads_lookLikePosts', '1'),
    ('ads_copyrightkey','')
    ");


// Permissions

// Permissions array
$permissions = array(
	'ad_manageperm' => array(-1, 0, 2), // ALL
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
