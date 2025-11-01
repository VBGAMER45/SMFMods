<?php
/*
IPv6
Version 1.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2025 SMFHacks.com

############################################
License Information:

Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################

*/
// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');




$smcFunc['db_query']('', "ALTER TABLE {db_prefix}log_online modify ip varbinary(16)
");

$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}ban_items");
$ip_low =  1;
$ip_high  = 1;
while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if($row[0] == 'ip_low')
		$ip_low = 0;

	if($row[0] == 'ip_high')
		$ip_high = 0;
}

if (!empty($ip_low))
$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ban_items add column ip_low VARBINARY(16)
");
if (!empty($ip_high))
$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ban_items add column ip_high VARBINARY(16)
");


$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ban_items add INDEX idx_id_ban_ip (ip_low,ip_high)
");






?>