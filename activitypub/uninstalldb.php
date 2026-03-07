<?php
/**
 * ActivityPub Federation - Database Uninstaller
 *
 * Drops all tables and removes settings.
 *
 * @package ActivityPub
 * @version 1.0.0
 * By: vbgamer45
 * https://www.smfhacks.com
 */

if (!defined('SMF'))
	die('No direct access...');

global $smcFunc;

// =========================================================================
// 1. Drop database tables
// =========================================================================

db_extend('packages');

$smcFunc['db_drop_table']('{db_prefix}ap_actors');
$smcFunc['db_drop_table']('{db_prefix}ap_objects');
$smcFunc['db_drop_table']('{db_prefix}ap_activities');
$smcFunc['db_drop_table']('{db_prefix}ap_followers');
$smcFunc['db_drop_table']('{db_prefix}ap_delivery_queue');
$smcFunc['db_drop_table']('{db_prefix}ap_blocks');

// =========================================================================
// 2. Remove settings
// =========================================================================

$settings_to_remove = array(
	'activitypub_enabled',
	'activitypub_auto_accept_follows',
	'activitypub_user_actors_enabled',
	'activitypub_user_opt_in',
	'activitypub_max_delivery_attempts',
	'activitypub_delivery_batch_size',
	'activitypub_content_mode',
	'activitypub_encryption_key',
	'activitypub_rate_limit_inbox',
);

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable IN ({array_string:settings})',
	array(
		'settings' => $settings_to_remove,
	)
);

// Also remove per-board settings.
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable LIKE {string:prefix}',
	array(
		'prefix' => 'activitypub_board_%',
	)
);

// Refresh settings cache.
updateSettings(array('activitypub_removed' => '1'));
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable = {string:var}',
	array(
		'var' => 'activitypub_removed',
	)
);
