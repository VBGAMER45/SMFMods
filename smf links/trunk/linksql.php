<?php
//SMFHacks.com
//Table SQL

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


//Create category SQL
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}links_cat
(ID_CAT mediumint(8) NOT NULL auto_increment,
title VARCHAR(100) NOT NULL,
description VARCHAR(255) NOT NULL,
roworder mediumint(8) unsigned NOT NULL default '0',
ID_PARENT mediumint(8) unsigned NOT NULL default '0',
image tinytext,
PRIMARY KEY  (ID_CAT)) Engine=MyISAM", __FILE__, __LINE__);

//Create Links Table
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}links
(ID_LINK int(11) NOT NULL auto_increment,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
ID_CAT mediumint(8) unsigned NOT NULL default '0',
url VARCHAR(255) NOT NULL,
image tinytext,
title VARCHAR(100) NOT NULL,
description VARCHAR(255) NOT NULL,
hits int(11) NOT NULL default '0',
approved tinyint(1) NOT NULL default '0',
rating int(11) NOT NULL default '0',
date int(10) unsigned NOT NULL default '0',
alexa int(10) unsigned NOT NULL default '0',
pagerank tinyint(3) NOT NULL default '0',
PRIMARY KEY  (ID_LINK)) Engine=MyISAM", __FILE__, __LINE__);

db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}links_rating
(ID int(11) NOT NULL auto_increment,
ID_LINK int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
value tinyint(1) NOT NULL,
PRIMARY KEY  (ID)) Engine=MyISAM", __FILE__, __LINE__);


db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}links_catperm
(ID mediumint(8) NOT NULL auto_increment,
ID_GROUP mediumint(8) NOT NULL default '0',
ID_CAT mediumint(8) unsigned NOT NULL default '0',
view tinyint(4) NOT NULL default '0',
addlink tinyint(4) NOT NULL default '0',
editlink tinyint(4) NOT NULL default '0',
dellink tinyint(4) NOT NULL default '0',
ratelink tinyint(4) NOT NULL default '0',
report tinyint(4) NOT NULL default '0',
PRIMARY KEY  (ID)) Engine=MyISAM", __FILE__, __LINE__);

//Insert the settings
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_setlinksperpage', '10')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_setshowtoprate', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_setshowmostvisited', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_setshowstats', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_set_count_child', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_setallowbbc', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_setgetpr', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_setgetalexa', '0')", __FILE__, __LINE__);


db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_disp_description', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_disp_hits', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_disp_rating', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_disp_membername', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_disp_date', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_disp_alexa', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smflinks_disp_pagerank', '0')", __FILE__, __LINE__);


//Upgrade the database if older version
$dbresult = db_query("SHOW COLUMNS FROM {$db_prefix}links_cat", __FILE__, __LINE__);
$ID_PARENT = 1;
$image = 1;
while ($row = mysql_fetch_row($dbresult))
{
	if($row[0] == 'ID_PARENT')
		$ID_PARENT = 0;
	if($row[0] == 'image')
		$image = 0;


}
mysql_free_result($dbresult);

if($ID_PARENT)
	db_query("ALTER TABLE {$db_prefix}links_cat ADD ID_PARENT mediumint(8) unsigned NOT NULL default '0'", __FILE__, __LINE__);
if($image)
	db_query("ALTER TABLE {$db_prefix}links_cat ADD image tinytext", __FILE__, __LINE__);

//Links table updates heh
$dbresult = db_query("SHOW COLUMNS FROM {$db_prefix}links", __FILE__, __LINE__);
$alexa = 1;
$pagerank = 1;
while ($row = mysql_fetch_row($dbresult))
{
	if($row[0] == 'alexa')
		$alexa = 0;
	if($row[0] == 'pagerank')
		$pagerank = 0;


}
mysql_free_result($dbresult);

if($alexa)
	db_query("ALTER TABLE {$db_prefix}links ADD alexa int(10) unsigned NOT NULL default '0'", __FILE__, __LINE__);
if ($pagerank)
	db_query("ALTER TABLE {$db_prefix}links ADD pagerank tinyint(3) NOT NULL default '0'", __FILE__, __LINE__);

// Add Package Servers
db_query("DELETE FROM {$db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'", __FILE__, __LINE__);
db_query("REPLACE INTO {$db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')", __FILE__, __LINE__);

	
// Permissions array
$permissions = array(
	'view_smflinks' => array(-1, 0, 2), // ALL
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