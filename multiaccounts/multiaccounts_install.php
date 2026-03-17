<?php

/**
 * Multi Accounts - Installation script
 *
 * @package MultiAccounts
 * @author vbgamer45
 * @license BSD
 */

if (!defined('SMF'))
	die('No direct access...');

global $smcFunc, $modSettings;

db_extend('packages');
// Create the multiaccounts table
$smcFunc['db_create_table']('{db_prefix}multiaccounts',
	array(
		array(
			'name' => 'id_member',
			'type' => 'mediumint',
			'size' => 8,
			'unsigned' => true,
			'default' => 0,
		),
		array(
			'name' => 'id_parent',
			'type' => 'mediumint',
			'size' => 8,
			'unsigned' => true,
			'default' => 0,
		),
	),
	array(
		array(
			'type' => 'unique',
			'columns' => array('id_parent', 'id_member'),
		),
		array(
			'type' => 'index',
			'columns' => array('id_member'),
		),
	),
	array(),
	'ignore'
);

// Add is_shareable column to the members table
$smcFunc['db_add_column']('{db_prefix}members', array(
	'name' => 'is_shareable',
	'type' => 'mediumint',
	'size' => 8,
	'unsigned' => true,
	'default' => 0,
), array(), 'ignore');

// Insert default settings (only if they don't exist)
$defaults = array(
	'enableMultiAccounts' => '0',
	'multiaccountsInheritParentGroup' => '0',
	'multiaccountsShowInMemberlist' => '1',
	'multiaccountsShowInProfile' => '1',
);

foreach ($defaults as $setting => $value)
{
	if (!isset($modSettings[$setting]))
		updateSettings(array($setting => $value));
}
