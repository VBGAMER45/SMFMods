<?php
// SMFHacks.com
// By: vbgamer45
// Table SQL

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


// Create Feeds Poster Table
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}feedbot
(ID_FEED mediumint(8) NOT NULL auto_increment,
 ID_BOARD smallint(5) unsigned NOT NULL default '0',
feedurl tinytext NOT NULL,
title tinytext NOT NULL,
enabled tinyint(4) NOT NULL default '1',
html tinyint(4) NOT NULL default '1',
postername tinytext,
ID_MEMBER mediumint(8) unsigned,
locked tinyint(4) NOT NULL default '0',
markasread tinyint(4) NOT NULL default '0',
articlelink tinyint(4) NOT NULL default '0',
topicprefix tinytext,
numbertoimport smallint(5) NOT NULL default 1,
importevery smallint(5) NOT NULL default 180,
updatetime int(10) unsigned NOT NULL default '0',
total_posts int(10) unsigned NOT NULL default '0',
footer text,
msgicon varchar(50) default 'xx',
json tinyint(1) default 0,
id_topic int(10)  unsigned NOT NULL default '0',
PRIMARY KEY  (ID_FEED))");

// Feed Log
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}feedbot_log
(ID_FEEDITEM mediumint(8) NOT NULL  auto_increment,
ID_FEED mediumint(8) NOT NULL,
feedhash tinytext NOT NULL,
feedtime int(10) unsigned NOT NULL default '0',
ID_MSG int(10) unsigned  NOT NULL default '0',
ID_TOPIC int(10)  unsigned NOT NULL default '0',
PRIMARY KEY  (ID_FEEDITEM))");

// Fake cron setting default false
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('rss_fakecron', '0')");

// Feed try method
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('rss_feedmethod', 'All')");

$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('rss_embedimages', '0')");

$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('rss_usedescription', '0')");

$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}feedbot");
$removed =  1;
$total_posts  = 1;
$footer = 1;
$msgicon = 1;
$json = 1;
$id_topic = 1;

while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if($row[0] == 'markasread')
		$removed = 0;
	if($row[0] == 'total_posts')
		$total_posts =  0;
	if($row[0] == 'footer')
		$footer =  0;	
		
	if($row[0] == 'msgicon')
		$msgicon =  0;	
        			
	if($row[0] == 'json')
		$json =  0;		
        
	if($row[0] == 'id_topic')
		$id_topic =  0;	    
        
}
$smcFunc['db_free_result']($dbresult);

if ($removed)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}feedbot ADD markasread tinyint(4) NOT NULL default '0'");

if ($total_posts)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}feedbot ADD total_posts int(10) unsigned NOT NULL default '0'");

if ($footer)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}feedbot ADD footer text");

if ($msgicon)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}feedbot ADD msgicon varchar(50) default 'xx'");

if ($json)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}feedbot ADD json tinyint(1) default 0");

if ($id_topic)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}feedbot ADD id_topic int(10)  unsigned NOT NULL default '0'");



$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}feedbot_log");
$ID_MSG =  1;
$ID_TOPIC = 1;
while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if($row[0] == 'ID_MSG')
		$ID_MSG = 0;
	if($row[0] == 'ID_TOPIC')
		$ID_TOPIC = 0;	


}
$smcFunc['db_free_result']($dbresult);

if ($ID_MSG)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}feedbot_log ADD ID_MSG int(10) unsigned  NOT NULL default '0'");


if ($ID_TOPIC)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}feedbot_log ADD ID_TOPIC mediumint(8) unsigned NOT NULL default '0'");

	
	
// Addd Package Servers
$smcFunc['db_query']('', "DELETE FROM {db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'");
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')");


// Add the scheduled task
$dbresult = $smcFunc['db_query']('', "
SELECT 
	COUNT(*) as total 
FROM {db_prefix}scheduled_tasks
WHERE task = 'update_feedbots'");
$row = $smcFunc['db_fetch_assoc']($dbresult);
if ($row['total'] == 0)
{
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}scheduled_tasks
	   (time_offset, time_regularity, time_unit, disabled, task)
	VALUES ('0', '2', 'm', '0', 'update_feedbots')");
}

?>