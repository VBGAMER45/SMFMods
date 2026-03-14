<?php

/**
 * Global Announcements - Installation script
 *
 * Creates database tables and settings.
 *
 * @package GlobalAnnouncements
 * @license MIT
 */

// Cannot be run directly.
if (!defined('SMF'))
	die('No direct access...');

db_extend('packages');

// Create the global_announcements table.
$smcFunc['db_create_table']('{db_prefix}global_announcements',
	array(
		array('name' => 'id_announcement', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'member_name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'title', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'body', 'type' => 'text'),
		array('name' => 'enabled', 'type' => 'tinyint', 'size' => 3, 'unsigned' => true, 'default' => 1),
		array('name' => 'allow_comments', 'type' => 'tinyint', 'size' => 3, 'unsigned' => true, 'default' => 1),
		array('name' => 'boards', 'type' => 'text'),
		array('name' => 'groups', 'type' => 'text'),
		array('name' => 'views', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'num_comments', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'default' => 0),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'updated_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'sort_order', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'default' => 0),
	),
	array(
		array('name' => 'id_announcement', 'type' => 'primary', 'columns' => array('id_announcement')),
	),
	array(),
	'ignore'
);

// Create the global_announcement_comments table.
$smcFunc['db_create_table']('{db_prefix}global_announcement_comments',
	array(
		array('name' => 'id_comment', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_announcement', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'member_name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'body', 'type' => 'text'),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'updated_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'modified_by', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	),
	array(
		array('name' => 'id_comment', 'type' => 'primary', 'columns' => array('id_comment')),
		array('name' => 'idx_announcement', 'type' => 'index', 'columns' => array('id_announcement')),
	),
	array(),
	'ignore'
);

// Create the global_announcement_log table.
$smcFunc['db_create_table']('{db_prefix}global_announcement_log',
	array(
		array('name' => 'id_announcement', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'viewed_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	array(
		array('name' => 'id_announcement_member', 'type' => 'primary', 'columns' => array('id_announcement', 'id_member')),
	),
	array(),
	'ignore'
);

// Insert default settings (only if not already set).
$defaults = array(
	'globalannouncements_enabled' => '1',
	'globalannouncements_force_id' => '0',
	'globalannouncements_sticky_bar' => '1',
	'globalannouncements_per_page' => '10',
);

$existing = array();
$request = $smcFunc['db_query']('', '
	SELECT variable
	FROM {db_prefix}settings
	WHERE variable IN ({array_string:vars})',
	array(
		'vars' => array_keys($defaults),
	)
);
while ($row = $smcFunc['db_fetch_assoc']($request))
	$existing[] = $row['variable'];
$smcFunc['db_free_result']($request);

$new_settings = array();
foreach ($defaults as $var => $val)
{
	if (!in_array($var, $existing))
		$new_settings[$var] = $val;
}

if (!empty($new_settings))
	updateSettings($new_settings);