<?php
/******************************************************************************
 * uninstalldb.php - Magic Llama Mod 2.0 for SMF 2.1
 * Drops database tables and removes all mod settings.
 ******************************************************************************/

if (!defined('SMF'))
	die('This file may not be accessed directly.');

global $smcFunc;
db_extend('packages');

// Drop tables.
$smcFunc['db_drop_table']('{db_prefix}magic_llama');
$smcFunc['db_drop_table']('{db_prefix}magic_llama_members');

// Remove all mod settings.
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable LIKE {string:pattern}',
	array(
		'pattern' => 'magic_llama_%',
	)
);
