<?php

/**
 * Global Announcements - Uninstallation script
 *
 * Removes database tables and settings.
 *
 * @package GlobalAnnouncements
 * @license MIT
 */

// Cannot be run directly.
if (!defined('SMF'))
	die('No direct access...');

db_extend('packages');

// Drop the tables.
$smcFunc['db_drop_table']('{db_prefix}global_announcements');
$smcFunc['db_drop_table']('{db_prefix}global_announcement_comments');
$smcFunc['db_drop_table']('{db_prefix}global_announcement_log');

// Remove settings.
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable IN ({array_string:vars})',
	array(
		'vars' => array(
			'globalannouncements_enabled',
			'globalannouncements_force_id',
			'globalannouncements_sticky_bar',
			'globalannouncements_per_page',
		),
	)
);