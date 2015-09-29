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
$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}members");
$referrals_no = 1;
$referred_date = 1;
$referred_by = 1;
$referrals_hits = 1;
while ($row = $smcFunc['db_fetch_row']($dbresult))
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


if ($referrals_no)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}members ADD referrals_no mediumint(8) NOT NULL default '0'");

if ($referred_by)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}members ADD referred_by mediumint(8) NOT NULL default '0'");

if ($referrals_hits)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}members ADD referrals_hits int(11) NOT NULL default '0'");

if ($referred_date)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}members ADD referred_date int(10) NOT NULL default '0'");

// Insert Settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ref_showreflink', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ref_showonpost', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ref_trackcookiehits', '0')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ref_cookietrackdays', '60')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ref_copyrightkey', '')");


?>