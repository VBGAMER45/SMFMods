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

// List all mod settings here to REMOVE
$mod_settings_to_remove = array(
	'smushit_attachments_age',
	'smushit_attachments_png',
	'smushit_attachment_size',
);


// REMOVE columns from an existing table
$columns = array();
$columns[] = array(
	'table_name' => '{db_prefix}attachments',
	'column_name' => 'smushit',
	'parameters' => array(),
	'error' => 'fatal',
);

// REMOVE rows from an existing table
$smcFunc['db_query']('', "
DELETE FROM {db_prefix}scheduled_tasks
WHERE task = 'scheduled_smushit'");
$row = $smcFunc['db_fetch_assoc']($dbresult);


if (count($mod_settings_to_remove) > 0) {

	// Remove the mod_settings if applicable, first the session
	foreach ($mod_settings_to_remove as $setting)
		if (isset($modSettings[$setting]))
			unset($modSettings[$setting]);

	// And now the database values
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}settings
		WHERE variable IN ({array_string:settings})',
		array(
			'settings' => $mod_settings_to_remove,
		)
	);

	// Make sure the cache is reset as well
	updateSettings(array(
		'settings_updated' => time(),
	));
}

foreach ($columns as $column)
  $smcFunc['db_remove_column']($column['table_name'], $column['column_name'], $column['parameters'], $column['error']);

if (SMF == 'SSI')
   echo 'Congratulations! You have successfully removed the integration hooks.';