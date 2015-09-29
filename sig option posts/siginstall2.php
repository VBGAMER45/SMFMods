<?php

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


//Alters the _message table adds one field for signature.
$dbresult = $smcFunc['db_query']('',"SHOW COLUMNS FROM {db_prefix}messages ");
$showSIG =  1;
while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if($row[0] == 'showSIG')
		$showSIG =0;

}
$smcFunc['db_free_result']($dbresult);

if($showSIG)
	$smcFunc['db_query']('',"ALTER TABLE {db_prefix}messages add showSIG TINYINT(4) DEFAULT '1' NOT NULL");

?>