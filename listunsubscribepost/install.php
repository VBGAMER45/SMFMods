<?php
/*
ListUnsubscribePost
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');




$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}listsubscribe
(id int(11) NOT NULL auto_increment,
email varchar(255),
unsubscribed tinyint(1) default 0,
PRIMARY KEY  (id),
KEY email (email)    
    ) ");


?>