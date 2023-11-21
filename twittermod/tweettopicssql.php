<?php
/*
Tweet Topics/FB Post System
Version 2.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2010-2012 SMFHacks.com

############################################
License Information:
Tweet Topics System is NOT free software.
This software may not be redistributed.

Thelicense is good for a single instance / install on a website.
You are allowed only one active install for each license purchase.

#############################################
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('twitterboards', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('oauth_token', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('oauth_token_secret', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('consumer_key', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('consumer_secret', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('bitly_username', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('bitly_apikey', '')", __FILE__, __LINE__);
// Facebook Boards
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('facebookboards', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('facebookappid', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('facebookappsecret', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('facebookfanpageid', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('facebookfanpageacesstoken', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('facebookacesstoken', '')", __FILE__, __LINE__);



?>