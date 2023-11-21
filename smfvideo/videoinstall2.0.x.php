<?php
/*
SMF Gallery Pro - Video AddOn
Version 4.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2010-2013 SMFHacks.com

############################################
License Information:
SMF Gallery is NOT free software.
This software may not be redistributed.

The pro edition license is good for a single instance / install on a website.
You are allowed only one active install for each license purchase.

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/
// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
        include_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
        die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc;
// Installs the settings for SMF Gallery Pro - Video AddOn

$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('gallery_video_maxfilesize', '0'),
('gallery_video_allowlinked', '1'),
('gallery_video_playerheight', '480'),
('gallery_video_playerwidth', '640'),
('gallery_video_showbbclinks', '0'),
('gallery_video_showdowloadlink', '0'),
('gallery_video_filetypes', 'wmv,avi,mpg,mov,rm,ram,rpm,flv,asf,asx,swf,mp4,webm,ogg,wav,mid,mp3'),
('mediapro_default_width', '0'),
('mediapro_default_height','0')
");


$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}gallery_pic");
$videofile = 1;

while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if($row[0] == 'videofile')
		$videofile = 0;
}
$smcFunc['db_free_result']($dbresult);

if ($videofile)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD videofile tinytext");





?>