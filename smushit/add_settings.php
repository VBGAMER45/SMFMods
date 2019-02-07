<?php

/**
 * Smush.it for SMF
 *
 * @author spuds http://addons.elkarte.net/2015/05/Smushit/
 * @license MPL 1.1 http://mozilla.org/MPL/1.1/
 * Ported to SMF by vbgamer45 http://www.smfhacks.com
 *
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $modSettings;

db_extend('packages');
db_extend('extra');

// List settings here in the format: setting_key => default_value.  Escape any "s. (" => \")
$mod_settings = array(
	'smushit_attachments_age' => 0,
	'smushit_attachments_png' => 1,
	'smushit_attachment_size' => 125,
);



// Add the scheduled task
$dbresult = $smcFunc['db_query']('', "
SELECT 
	COUNT(*) as total 
FROM {db_prefix}scheduled_tasks
WHERE task = 'scheduled_smushit'");
$row = $smcFunc['db_fetch_assoc']($dbresult);
if ($row['total'] == 0)
{
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}scheduled_tasks
	   (time_offset, time_regularity, time_unit, disabled, task)
	VALUES ('39620', '1', 'd', '1', 'scheduled_smushit')");
}






	$smcFunc['db_add_column']('{db_prefix}attachments', 
        array(
		 'name' => 'smushit',
		 'auto' => false,
		 'default' => 0,
		 'type' => 'tinyint',
		 'size' => 1,
		 'null' => true,
	    )
    );


// Update mod settings if applicable
foreach ($mod_settings as $new_setting => $new_value)
{
	if (!isset($modSettings[$new_setting]))
		updateSettings(array($new_setting => $new_value));
}




if (SMF == 'SSI')
   echo 'Congratulations! You have successfully installed this addon!';