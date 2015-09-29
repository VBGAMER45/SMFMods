<?php

/*
Thumbnail topic
Version 3.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2012-2015 http://www.samsonsoftware.com
*/
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
  
db_extend('packages');
db_extend('extra');


$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('topic_thumb_height', '0')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('topic_thumb_width', '0')");
