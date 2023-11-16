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


$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}profile_comments
(ID_COMMENT int(11) NOT NULL auto_increment,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
comment text,
subject varchar (255),
date int(11) NOT NULL default '0',
COMMENT_MEMBER_ID mediumint(8) unsigned NOT NULL default '0',
ID_PARENT int(11) unsigned NOT NULL default '0',
approved tinyint(4) NOT NULL default '1',
PRIMARY KEY (ID_COMMENT))
");

$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}profile_comments_report
(ID_REPORT int(11) NOT NULL auto_increment,
ID_COMMENT int(11)     default '0'
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
comment text,
date int(11) NOT NULL default '0',
PRIMARY KEY (ID_REPORT))
");


// Alter the Profile comments table
$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}profile_comments");
$approved =  1;
$ID_PARENT = 1;
while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if($row[0] == 'approved')
		$approved = 0;

	if($row[0] == 'ID_PARENT ')
		$ID_PARENT = 0;
}
$smcFunc['db_free_result']($dbresult);

if ($approved)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}profile_comments ADD approved tinyint(4) NOT NULL default '1'");

if ($ID_PARENT)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}profile_comments ADD ID_PARENT int(11) unsigned NOT NULL default '0'");



$dbresult = $smcFunc['db_query']('', "SHOW INDEX FROM  {db_prefix}profile_comments");

$indexCOMMENT_MEMBER_ID= 1;
$indexID_PARENT= 1;


while ($row = $smcFunc['db_fetch_assoc']($dbresult))
{
	if ($row['Column_name'] == 'COMMENT_MEMBER_ID')
		$indexCOMMENT_MEMBER_ID = 0;

	if ($row['Column_name'] == 'ID_PARENT')
		$indexID_PARENT = 0;
}

if ($indexCOMMENT_MEMBER_ID)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}profile_comments ADD INDEX (COMMENT_MEMBER_ID)");

if ($indexID_PARENT)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}profile_comments ADD INDEX (ID_PARENT)");

// Add Package Servers
$smcFunc['db_query']('', "DELETE FROM {db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'");
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')");


// Add settings
// max comment length
// enable alerts on new comment
// comments require approval?
// items per page on comment page.
// Enable the latest profile posts box on forum homepage
// Allow reactions to statuses
// profile visitors enabled.
// How many to show.
// show avatars

?>