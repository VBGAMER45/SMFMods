<?php
/*
S3 System for SMF
Version 1.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2021 SMFHacks.com

############################################
License Information:
S3 System for SMF is NOT free software.
This software may not be redistributed.

The license is good for a single instance / install on a website.
You are allowed only one active install for each license purchase.

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


$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}attachments");

$s3 = 1;
while ($row = $smcFunc['db_fetch_row']($dbresult))
{

	if ($row[0] == 's3')
		$s3 = 0;
}

if ($s3)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}attachments ADD s3 tinyint(4) NOT NULL default '0'");


// Insert the settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('s3_enabled','0'),
('s3_cron_items','100'),
('s3_delete_local','0'),
('s3_access_key',''),
('s3_secret_access_key',''),
('s3_thumbnails','0'),
('s3_bucket',''),
('s3_region',''),
('s3_domain','')
");


?>