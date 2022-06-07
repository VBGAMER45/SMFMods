<?php
/*
 * Mod Version Checker
 * By: vbgamer45
 * https://www.smfhacks.com
 *
 */


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


if (empty($context['uninstalling']))
{
	// Add the Scheduled Task
	$dbresult = $smcFunc['db_query']('', "
	SELECT 
		COUNT(*) as total 
	FROM {db_prefix}scheduled_tasks
	WHERE task = 'scheduled_modvercheck'");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	if ($row['total'] == 0)
	{
		$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}scheduled_tasks
		   (time_offset, time_regularity, time_unit, disabled, task)
		VALUES ('39620', '1', 'd', '0', 'scheduled_modvercheck')");
	}

	$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('modcheckupdates', '')");

}
else
{
	// Remove Scheduled Task
	$smcFunc['db_query']('', "
	DELETE FROM {db_prefix}scheduled_tasks
	WHERE task = 'scheduled_modvercheck'");

	// Delete the setting
	$smcFunc['db_query']('', "DELETE FROM {db_prefix}settings WHERE variable = 'modcheckupdates'");
}



?>