<?php
/*
	SMF Trader System: 1.2
	http://www.smfhacks.com

	smftraderinstall.php - SMF Trader System
	Purpose - Installs the database tables for SMF Trader System
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}feedback 
(feedbackid int(11) NOT NULL auto_increment, 
ID_MEMBER mediumint(8) unsigned NOT NULL default '0', 
comment_short tinytext NOT NULL, 
comment_long text, topicurl tinytext, 
saletype tinyint(4) NOT NULL default '0', 
salevalue tinyint(4) NOT NULL default '0', 
saledate int(11) NOT NULL default '0', 
FeedBackMEMBER_ID mediumint(8) unsigned NOT NULL default '0',
approved tinyint(4) NOT NULL default '1',
ID_LISTING int(11) NOT NULL default '0',
KEY ID_LISTING (ID_LISTING),
KEY ID_MEMBER (ID_MEMBER),
PRIMARY KEY  (feedbackid))", __FILE__, __LINE__);


$dbresult = db_query("SHOW COLUMNS FROM {$db_prefix}feedback", __FILE__, __LINE__);
$approved =  1;
$ID_LISTING = 1;

while ($row = mysql_fetch_row($dbresult))
{
	if($row[0] == 'approved')
		$approved =0;
	if($row[0] == 'ID_LISTING')
		$ID_LISTING =0;
			
}
mysql_free_result($dbresult);

if ($approved)
	db_query("ALTER TABLE {$db_prefix}feedback ADD approved tinyint(4) NOT NULL default '1'", __FILE__, __LINE__);

if ($ID_LISTING)
	db_query("ALTER TABLE {$db_prefix}feedback ADD ID_LISTING int(11) NOT NULL default '0'", __FILE__, __LINE__);

// Other Settings
db_query("INSERT IGNORE INTO {$db_prefix}settings
	(variable, value)
VALUES
('trader_approval', '0'),
('trader_feedbackperpage', '10'),
('trader_use_pos_neg', '0'),
('trader_membergroupspm', '1')
", __FILE__, __LINE__);

?>