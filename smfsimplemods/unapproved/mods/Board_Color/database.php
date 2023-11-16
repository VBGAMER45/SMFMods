<?php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

db_extend('packages');

$smcFunc['db_add_column'] (
	'{db_prefix}boards',
	array
	(
		'name' => 'color_title',
		'type' => 'varchar',
		'size' => 255,
	)
);

?>
