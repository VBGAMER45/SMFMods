<?php
/*
	Count Child Child Boards Posts
	https://www.smfhacks.com
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


// Other Settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('boardindex_max_depth', '2')
");

?>