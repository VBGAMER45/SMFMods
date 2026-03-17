<?php

/**
 * Multi Accounts - Uninstallation script
 *
 * @package MultiAccounts
 * @author vbgamer45
 * @license BSD
 */

if (!defined('SMF'))
	die('No direct access...');

global $smcFunc;
db_extend('packages');

/*
// Drop the multiaccounts table
$smcFunc['db_drop_table']('{db_prefix}multiaccounts');

// Remove is_shareable column from members table
$smcFunc['db_remove_column']('{db_prefix}members', 'is_shareable');

// Remove all mod settings
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable IN ({array_string:settings})',
	array(
		'settings' => array(
			'enableMultiAccounts',
			'multiaccountsInheritParentGroup',
			'multiaccountsShowInMemberlist',
			'multiaccountsShowInProfile',
			'multiaccountsGroupLimits',
		),
	)
);
*/
