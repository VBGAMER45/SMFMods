<?php

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	$ssi = true;
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc;

if(!array_key_exists('db_add_column', $smcFunc))
	db_extend('packages');

$column = array(
	'name' => 'join_reason',
	'type' => 'tinytext',
);

$smcFunc['db_add_column']('{db_prefix}members', $column);

if(!empty($ssi))
	echo 'Database installation complete!';

?>