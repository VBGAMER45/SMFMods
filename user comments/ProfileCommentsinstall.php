<?php
/*
	Profile Comments: 2.0
	http://www.smfhacks.com

	ProfileCommentsinstall.php - Profile Comments
	Purpose - Installs the database tables for Profile Comments
*/


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}profile_comments
(ID_COMMENT int(11) NOT NULL auto_increment,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
comment text,
subject varchar (100),
date int(11) NOT NULL default '0',
COMMENT_MEMBER_ID mediumint(8) unsigned NOT NULL default '0',
approved tinyint(4) NOT NULL default '1',
PRIMARY KEY  (ID_COMMENT))
Engine=MyISAM", __FILE__, __LINE__);

// Alter the Profile comments table
$dbresult = db_query("SHOW COLUMNS FROM {$db_prefix}profile_comments", __FILE__, __LINE__);
$approved =  1;
while ($row = mysql_fetch_row($dbresult))
{
	if($row[0] == 'approved')
		$approved = 0;

}
mysql_free_result($dbresult);

if ($approved)
	db_query("ALTER TABLE {$db_prefix}profile_comments ADD approved tinyint(4) NOT NULL default '1'", __FILE__, __LINE__);

// Add Package Servers
db_query("DELETE FROM {$db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'", __FILE__, __LINE__);
db_query("REPLACE INTO {$db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')", __FILE__, __LINE__);

	
?>