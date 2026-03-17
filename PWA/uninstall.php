<?php

/**
 * Mobile-First PWA Shell — Uninstall Script
 *
 * Removes settings and database tables.
 *
 * @package smfmods:pwa-shell
 * @license MIT
 */

if (!defined('SMF'))
	die('No direct access...');

global $smcFunc;

db_extend('packages');
// Remove mod settings
$settingsToRemove = [
	'pwa_enabled',
	'pwa_push_enabled',
	'pwa_dark_default',
	'pwa_accent_color',
	'pwa_a2hs_delay',
	'pwa_offline_msg',
	'pwa_vapid_public',
	'pwa_vapid_private',
	'pwa_vapid_private_pem',
	'pwa_vapid_email',
	'pwa_push_last_alert_id',
];

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable IN ({array_string:settings})',
	[
		'settings' => $settingsToRemove,
	]
);

// Drop the push subscriptions table
$smcFunc['db_drop_table']('{db_prefix}pwa_push_subscriptions');

// Remove the scheduled task
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}scheduled_tasks
	WHERE task = {string:task}',
	[
		'task' => 'pwa_push_alerts',
	]
);



// Clear settings cache
updateSettings([
	'settings_updated' => time(),
]);
