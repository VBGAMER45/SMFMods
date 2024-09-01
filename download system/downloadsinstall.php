<?php
/*
Download System
Version 2.5
by:vbgamer45
http://www.smfhacks.com
Copyright 2008-2014 SMFHacks.com

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
	// MySQL users below 4.0 can not use Engine
//if (version_compare('4', preg_replace('~\-.+?$~', '', min(mysql_get_server_info(), mysql_get_client_info()))) > 0)
//		$schema_type = ' TYPE=MyISAM';
//else
		$schema_type = ' ENGINE=MyISAM';

// File Table
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}down_file(
ID_FILE int(11) NOT NULL auto_increment,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
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
ID_CAT int(10) NOT NULL default '0',
approved tinyint(4) NOT NULL default '0',
allowcomments tinyint(4) NOT NULL default '0',
totalratings int(10) NOT NULL default '0',
rating int(10) NOT NULL default '0',
type tinyint(4) NOT NULL default '0',
sendemail tinyint(4) NOT NULL default '0',
ID_TOPIC mediumint(8) unsigned NOT NULL default '0',
keywords VARCHAR(100),
credits int(10) NOT NULL default '0',
PRIMARY KEY (ID_FILE)) $schema_type", __FILE__, __LINE__);


// Download comments
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}down_comment(
ID_COMMENT int(11) NOT NULL auto_increment,
ID_FILE int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
approved tinyint(4) NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
modified_ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
lastmodified int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (ID_COMMENT)) $schema_type", __FILE__, __LINE__);

// Downloads Category
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}down_cat
(ID_CAT mediumint(8) NOT NULL auto_increment,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
title VARCHAR(100) NOT NULL,
description VARCHAR(255),
roworder mediumint(8) unsigned NOT NULL default '0',
image VARCHAR(255),
filename tinytext,
ID_BOARD smallint(5) unsigned NOT NULL default '0',
ID_PARENT smallint(5) unsigned NOT NULL default '0',
disablerating tinyint(4) NOT NULL default '0',
total int(11) NOT NULL default '0',
redirect tinyint(4) NOT NULL default '0',
locktopic tinyint(4) NOT NULL default '0',
showpostlink tinyint(4) NOT NULL default '0',
sortby tinytext,
orderby tinytext,
PRIMARY KEY  (ID_CAT)) $schema_type", __FILE__, __LINE__);

// File Ratings
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}down_rating
(ID int(11) NOT NULL auto_increment,
ID_FILE int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
value tinyint(2) NOT NULL,
PRIMARY KEY  (ID)) $schema_type", __FILE__, __LINE__);


// Reported Files
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}down_report
(ID int(11) NOT NULL auto_increment,
ID_FILE int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (ID)) $schema_type", __FILE__, __LINE__);

// Reported Comment
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}down_creport
(ID int(11) NOT NULL auto_increment,
ID_FILE int(11) NOT NULL,
ID_COMMENT int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (ID)) $schema_type", __FILE__, __LINE__);

// Member Quota Information
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}down_userquota
(ID_MEMBER mediumint(8) unsigned NOT NULL,
totalfilesize int(12) NOT NULL default '0',
PRIMARY KEY  (ID_MEMBER)) $schema_type", __FILE__, __LINE__);

// Group Quota limit
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}down_groupquota
(ID_GROUP smallint(5) unsigned NOT NULL default '0',
totalfilesize int(12) NOT NULL default '0',
PRIMARY KEY (ID_GROUP)) $schema_type", __FILE__, __LINE__);

db_query("ALTER TABLE {$db_prefix}down_userquota CHANGE totalfilesize totalfilesize BIGINT NOT NULL  default '0'", __FILE__, __LINE__);
db_query("ALTER TABLE {$db_prefix}down_groupquota CHANGE totalfilesize totalfilesize BIGINT NOT NULL  default '0'", __FILE__, __LINE__);



db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}down_catperm
(ID mediumint(8) NOT NULL auto_increment,
ID_GROUP mediumint(8) NOT NULL default '0',
ID_CAT mediumint(8) unsigned NOT NULL default '0',
view tinyint(4) NOT NULL default '0',
addfile tinyint(4) NOT NULL default '0',
editfile tinyint(4) NOT NULL default '0',
delfile tinyint(4) NOT NULL default '0',
ratefile tinyint(4) NOT NULL default '0',
addcomment tinyint(4) NOT NULL default '0',
editcomment tinyint(4) NOT NULL default '0',
report tinyint(4) NOT NULL default '0',
PRIMARY KEY  (ID)) $schema_type", __FILE__, __LINE__);

// Insert the settings
db_query("INSERT IGNORE INTO {$db_prefix}settings
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
", __FILE__, __LINE__);


// Update Intial Totals set them to negative -1
db_query("UPDATE {$db_prefix}down_cat SET total = -1", __FILE__, __LINE__);


// Custom Fields table
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}down_custom_field
(ID_CUSTOM mediumint(8) NOT NULL auto_increment,
ID_CAT int(10) NOT NULL,
roworder mediumint(8) unsigned NOT NULL default '0',
title tinytext,
defaultvalue tinytext,
is_required tinyint(4) NOT NULL default '0',
showoncatlist tinyint(4) NOT NULL default '0',
PRIMARY KEY  (ID_CUSTOM))
 $schema_type", __FILE__, __LINE__);

db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}down_custom_field_data
(
ID_CUSTOM mediumint(8) NOT NULL,
ID_FILE int(11) NOT NULL default '0',
value tinytext)
 $schema_type", __FILE__, __LINE__);


// Add Package Servers
db_query("DELETE FROM {$db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'", __FILE__, __LINE__);
db_query("REPLACE INTO {$db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')", __FILE__, __LINE__);


// Recount Totals
echo 'Recounting Download Totals...<br />';
$dbresult = db_query("SELECT ID_CAT FROM {$db_prefix}down_cat", __FILE__, __LINE__);
while($row = mysql_fetch_assoc($dbresult))
{
	UpdateCategoryTotals($row['ID_CAT']);
}
mysql_free_result($dbresult);

function UpdateCategoryTotals($ID_CAT)
{
	global $db_prefix;

	$dbresult = db_query("
	SELECT
		COUNT(*) AS total
	FROM {$db_prefix}down_file
	WHERE ID_CAT = $ID_CAT AND approved = 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	$total = $row['total'];
	mysql_free_result($dbresult);

	// Update the count
	$dbresult = db_query("UPDATE {$db_prefix}down_cat SET total = $total WHERE ID_CAT = $ID_CAT LIMIT 1", __FILE__, __LINE__);

}

// Permissions array
$permissions = array(
	'downloads_view' => array(-1, 0, 2), // ALL
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