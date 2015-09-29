<?php
/*
Referrals System
Version 3.0
http://www.smfhacks.com
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

// Member table checks
$dbresult = db_query("SHOW COLUMNS FROM {$db_prefix}members", __FILE__, __LINE__);
$referrals_no = 1;
$referred_by = 1;
$referred_date = 1;
$referrals_hits = 1;
while ($row = mysql_fetch_row($dbresult))
{
	if($row[0] == 'referrals_no')
		$referrals_no = 0;
	if($row[0] == 'referred_by')
		$referred_by = 0;
		
	if($row[0] == 'referred_date')
		$referred_date = 0;		
		
	if($row[0] == 'referrals_hits')
		$referrals_hits = 0;
}
mysql_free_result($dbresult);

if ($referrals_no)
	db_query("ALTER TABLE {$db_prefix}members ADD referrals_no mediumint(8) NOT NULL default '0'", __FILE__, __LINE__);

if ($referred_by)
	db_query("ALTER TABLE {$db_prefix}members ADD referred_by mediumint(8) NOT NULL default '0'", __FILE__, __LINE__);

if ($referrals_hits)
	db_query("ALTER TABLE {$db_prefix}members ADD referrals_hits int(11) NOT NULL default '0'", __FILE__, __LINE__);

if ($referrals_hits)
	db_query("ALTER TABLE {$db_prefix}members ADD referred_date int(10) NOT NULL default '0'", __FILE__, __LINE__);


// Insert Settings
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('ref_showreflink', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('ref_showonpost', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('ref_trackcookiehits', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('ref_cookietrackdays', '60')", __FILE__, __LINE__);

db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('ref_copyrightkey', '')", __FILE__, __LINE__);


?>