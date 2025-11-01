<?php
/*
Download System
Version 2.5
by:vbgamer45
http://www.smfhacks.com
Copyright 2008-2018 SMFHacks.com

############################################
License Information:

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/
// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


// Installs the Database tables for Download System
$modSettings['disableQueryCheck'] = 1;
global $smcFunc;
if (function_exists("set_tld_regex"))
$dbresult = $smcFunc['db_query']('', 'SELECT @@sql_mode as mode',
		array(),
	);
else
$dbresult = $smcFunc['db_query']('', 'SELECT @@sql_mode as mode',
		array(),
		false
	);
$row = $smcFunc['db_fetch_assoc']($dbresult);	
if (substr_count($row['mode'],"ONLY_FULL_GROUP_BY") > 0)
{
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('smfhacks_sqlmode', '1')
");

$smcFunc['db_query']('', "SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");

}


// Installs the Database tables for Download System
	// MySQL users below 4.0 can not use Engine
//if (version_compare('4', preg_replace('~\-.+?$~', '', min(mysql_get_server_info(), mysql_get_client_info()))) > 0)
//		$schema_type = ' TYPE=MyISAM';
//else
		$schema_type = ' ENGINE=MyISAM';


// File Table
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}down_file(
id_file int(11) NOT NULL auto_increment,
id_member mediumint(8) unsigned NOT NULL default '0',
date int(10) unsigned NOT NULL default '0',
title VARCHAR(100),
description text,
views int(10) NOT NULL default '0',
totaldownloads int(10) NOT NULL default '0',
lastdownload int(10) unsigned NOT NULL default '0',
filesize int(10) NOT NULL default '0',
orginalfilename tinytext,
filename tinytext,
fileurl tinytext,
commenttotal int(10) NOT NULL default '0',
id_cat int(10) NOT NULL default '0',
approved tinyint(4) NOT NULL default '0',
allowcomments tinyint(4) NOT NULL default '0',
totalratings int(10) NOT NULL default '0',
rating int(10) NOT NULL default '0',
type tinyint(4) NOT NULL default '0',
sendemail tinyint(4) NOT NULL default '0',
id_topic mediumint(8) unsigned NOT NULL default '0',
keywords VARCHAR(100),
credits int(10) NOT NULL default '0',
PRIMARY KEY (id_file)) $schema_type");


// Download comments
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}down_comment(
id_comment int(11) NOT NULL auto_increment,
id_file int(11) NOT NULL,
id_member mediumint(8) unsigned NOT NULL default '0',
approved tinyint(4) NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
modified_id_member mediumint(8) unsigned NOT NULL default '0',
lastmodified int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (id_comment)) $schema_type");

// Downloads Category
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}down_cat
(id_cat mediumint(8) NOT NULL auto_increment,
id_member mediumint(8) unsigned NOT NULL default '0',
title VARCHAR(100) NOT NULL,
description VARCHAR(255),
roworder mediumint(8) unsigned NOT NULL default '0',
image VARCHAR(255),
filename tinytext,
id_board smallint(5) unsigned NOT NULL default '0',
id_parent smallint(5) unsigned NOT NULL default '0',
disablerating tinyint(4) NOT NULL default '0',
total int(11) NOT NULL default '0',
redirect tinyint(4) NOT NULL default '0',
locktopic tinyint(4) NOT NULL default '0',
showpostlink tinyint(4) NOT NULL default '0',
sortby tinytext,
orderby tinytext,
PRIMARY KEY  (id_cat)) $schema_type");

// File Ratings
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}down_rating
(id int(11) NOT NULL auto_increment,
id_file int(11) NOT NULL,
id_member mediumint(8) unsigned NOT NULL default '0',
value tinyint(2) NOT NULL,
PRIMARY KEY  (ID)) $schema_type");


// Reported Files
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}down_report
(id int(11) NOT NULL auto_increment,
id_file int(11) NOT NULL,
id_member mediumint(8) unsigned NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (id)) $schema_type");

// Reported Comment
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}down_creport
(id int(11) NOT NULL auto_increment,
id_file int(11) NOT NULL,
id_comment int(11) NOT NULL,
id_member mediumint(8) unsigned NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (id)) $schema_type");

// Member Quota Information
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}down_userquota
(id_member mediumint(8) unsigned NOT NULL,
totalfilesize int(12) NOT NULL default '0',
PRIMARY KEY  (id_member)) $schema_type");

// Group Quota limit
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}down_groupquota
(id_group smallint(5) unsigned NOT NULL default '0',
totalfilesize int(12) NOT NULL default '0',
PRIMARY KEY (id_group)) $schema_type");

$smcFunc['db_query']('', "ALTER TABLE {db_prefix}down_userquota CHANGE totalfilesize totalfilesize BIGINT NOT NULL  default '0'");
$smcFunc['db_query']('', "ALTER TABLE {db_prefix}down_groupquota CHANGE totalfilesize totalfilesize BIGINT NOT NULL  default '0'");




$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}down_catperm
(id mediumint(8) NOT NULL auto_increment,
id_group mediumint(8) NOT NULL default '0',
id_cat mediumint(8) unsigned NOT NULL default '0',
view tinyint(4) NOT NULL default '0',
addfile tinyint(4) NOT NULL default '0',
editfile tinyint(4) NOT NULL default '0',
delfile tinyint(4) NOT NULL default '0',
ratefile tinyint(4) NOT NULL default '0',
addcomment tinyint(4) NOT NULL default '0',
editcomment tinyint(4) NOT NULL default '0',
report tinyint(4) NOT NULL default '0',
PRIMARY KEY  (id)) $schema_type");

// Insert the settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('down_max_filesize', '5000000'),
('down_who_viewing', '0'),
('down_set_commentsnewest', '1'),
('down_commentchoice', '0'),
('down_set_count_child', '1'),
('down_show_ratings', '1'),
('down_index_recent', '1'),
('down_index_mostviewed', '1'),
('down_index_toprated', '0'),
('down_index_mostcomments', '0'),
('downloads_index_mostdownloaded', '0'),
('down_set_files_per_page', '20'),
('down_set_t_views', '1'),
('down_set_t_downloads', '1'),
('down_set_t_filesize', '1'),
('down_set_t_date', '1'),
('down_set_t_comment', '1'),
('down_set_t_username', '1'),
('down_set_t_rating', '1'),
('down_set_t_title', '1'),
('down_index_showtop', '0'),
('down_set_cat_width', '120'),
('down_set_cat_height', '120'),
('down_set_show_quickreply', '0'),
('down_set_file_prevnext', '1'),
('down_set_file_desc', '1'),
('down_set_file_title', '1'),
('down_set_file_views', '1'),
('down_set_file_downloads','1'),
('down_set_file_lastdownload','1'),
('down_set_file_poster', '1'),
('down_set_file_date', '1'),
('down_set_file_showfilesize', '1'),
('down_set_file_showrating', '1'),
('down_set_file_keywords', '1'),
('down_shop_commentadd', '0'),
('down_shop_fileadd', '0'),
('down_set_showcode_directlink', '0'),
('down_set_showcode_htmllink', '0'),
('down_set_enable_multifolder', '0'),
('down_folder_id', '0')
");


// Update Intial Totals set them to negative -1
$smcFunc['db_query']('', "UPDATE {db_prefix}down_cat SET total = -1");

// Custom Fields table
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}down_custom_field
(id_custom mediumint(8) NOT NULL auto_increment,
id_cat int(10) NOT NULL,
roworder mediumint(8) unsigned NOT NULL default '0',
title tinytext,
defaultvalue tinytext,
is_required tinyint(4) NOT NULL default '0',
showoncatlist tinyint(4) NOT NULL default '0',
PRIMARY KEY  (id_custom))
$schema_type");

$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}down_custom_field_data
(
id_custom mediumint(8) NOT NULL,
id_file int(11) NOT NULL default '0',
value tinytext)
$schema_type");


// Add Package Servers
$smcFunc['db_query']('', "DELETE FROM {db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'");
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')");


// Recount Totals
echo 'Recounting Download Totals...<br />';
$dbresult = $smcFunc['db_query']('', "
SELECT
	id_cat
FROM {db_prefix}down_cat");
while($row = $smcFunc['db_fetch_assoc']($dbresult))
	InstallUpdateCategoryTotals($row['id_cat']);

$smcFunc['db_free_result']($dbresult);

function InstallUpdateCategoryTotals($ID_CAT)
{
	global $smcFunc;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}down_file
	WHERE id_cat = $ID_CAT AND approved = 1");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);

	// Update the count
	$smcFunc['db_query']('', "UPDATE {db_prefix}down_cat SET total = $total WHERE ID_CAT = $ID_CAT LIMIT 1");

}

// Permissions array
$permissions = array(
	'downloads_view' => array(-1, 0, 2), // ALL
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