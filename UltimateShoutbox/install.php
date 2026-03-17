<?php
/**
 * Ultimate Shoutbox & Chatroom - Installer
 *
 * Creates database tables, registers hooks, and inserts default settings.
 *
 * @package Shoutbox
 * @version 1.1.0
 * By: vbgamer45
 * https://www.smfhacks.com
 */

// Safety check - must be run from SMF.
if (!defined('SMF'))
	die('No direct access...');

global $smcFunc, $modSettings;

// =========================================================================
// 1. Create database tables
// =========================================================================

$tables = array();

$tables[] = array(
	'table_name' => '{db_prefix}shoutbox_messages',
	'columns' => array(
		array('name' => 'id_msg', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'member_name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'body', 'type' => 'text'),
		array('name' => 'parsed_body', 'type' => 'text'),
		array('name' => 'is_whisper', 'type' => 'tinyint', 'size' => 3, 'unsigned' => true, 'default' => 0),
		array('name' => 'whisper_to', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'is_action', 'type' => 'tinyint', 'size' => 3, 'unsigned' => true, 'default' => 0),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'edited_by', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'edited_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_msg')),
		array('type' => 'index', 'name' => 'idx_created', 'columns' => array('created_at')),
		array('type' => 'index', 'name' => 'idx_member', 'columns' => array('id_member')),
		array('type' => 'index', 'name' => 'idx_whisper', 'columns' => array('is_whisper', 'whisper_to')),
	),
);

$tables[] = array(
	'table_name' => '{db_prefix}shoutbox_rooms',
	'columns' => array(
		array('name' => 'id_room', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'auto' => true),
		array('name' => 'room_name', 'type' => 'varchar', 'size' => 80, 'default' => ''),
		array('name' => 'room_desc', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'is_private', 'type' => 'tinyint', 'size' => 3, 'unsigned' => true, 'default' => 0),
		array('name' => 'allowed_groups', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'sort_order', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'default' => 0),
		array('name' => 'is_default', 'type' => 'tinyint', 'size' => 3, 'unsigned' => true, 'default' => 0),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_room')),
		array('type' => 'index', 'name' => 'idx_sort', 'columns' => array('sort_order', 'id_room')),
	),
);

$tables[] = array(
	'table_name' => '{db_prefix}shoutbox_bans',
	'columns' => array(
		array('name' => 'id_ban', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'banned_by', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'reason', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'expires_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_ban')),
		array('type' => 'index', 'name' => 'idx_member', 'columns' => array('id_member')),
	),
);

$tables[] = array(
	'table_name' => '{db_prefix}shoutbox_reactions',
	'columns' => array(
		array('name' => 'id_reaction', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_msg', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'reaction_type', 'type' => 'varchar', 'size' => 20, 'default' => ''),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_reaction')),
		array('type' => 'unique', 'name' => 'idx_msg_member_type', 'columns' => array('id_msg', 'id_member', 'reaction_type')),
		array('type' => 'index', 'name' => 'idx_msg', 'columns' => array('id_msg')),
	),
);

$tables[] = array(
	'table_name' => '{db_prefix}shoutbox_attachments',
	'columns' => array(
		array('name' => 'id_attachment', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_msg', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'filename', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'stored_name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'mime_type', 'type' => 'varchar', 'size' => 100, 'default' => ''),
		array('name' => 'file_size', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_attachment')),
		array('type' => 'index', 'name' => 'idx_msg', 'columns' => array('id_msg')),
		array('type' => 'index', 'name' => 'idx_member', 'columns' => array('id_member')),
		array('type' => 'index', 'name' => 'idx_orphan', 'columns' => array('id_msg', 'created_at')),
	),
);

// Use SMF's db_create_table function.
db_extend('packages');

foreach ($tables as $table)
{
	$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], array(), 'ignore');
}

// Add id_room column to shoutbox_messages (upgrade-safe: only adds if missing).
$smcFunc['db_add_column'](
	'{db_prefix}shoutbox_messages',
	array('name' => 'id_room', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'default' => 0),
	array(),
	'ignore'
);

$smcFunc['db_add_index'](
	'{db_prefix}shoutbox_messages',
	array('type' => 'index', 'name' => 'idx_room_msg', 'columns' => array('id_room', 'id_msg')),
	array(),
	'ignore'
);

// Insert default "General" room if no rooms exist yet.
$request = $smcFunc['db_query']('', '
	SELECT COUNT(*) FROM {db_prefix}shoutbox_rooms',
	array()
);
list($room_count) = $smcFunc['db_fetch_row']($request);
$smcFunc['db_free_result']($request);

if ((int) $room_count === 0)
{
	$smcFunc['db_insert']('insert',
		'{db_prefix}shoutbox_rooms',
		array(
			'room_name' => 'string',
			'room_desc' => 'string',
			'is_private' => 'int',
			'allowed_groups' => 'string',
			'sort_order' => 'int',
			'is_default' => 'int',
			'created_at' => 'int',
		),
		array(
			'General',
			'',
			0,
			'',
			0,
			1,
			time(),
		),
		array('id_room')
	);

	// Get the ID of the default room we just inserted.
	$default_room_id = $smcFunc['db_insert_id']('{db_prefix}shoutbox_rooms');

	// Migrate existing messages to the default room.
	if ($default_room_id > 0)
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}shoutbox_messages
			SET id_room = {int:room_id}
			WHERE id_room = 0',
			array(
				'room_id' => $default_room_id,
			)
		);
	}
}

// =========================================================================
// 2. Insert default settings
// =========================================================================

$settings = array(
	'shoutbox_enabled' => '1',
	'shoutbox_poll_interval' => '3000',
	'shoutbox_max_length' => '500',
	'shoutbox_max_display' => '25',
	'shoutbox_max_display_chatroom' => '100',
	'shoutbox_flood_time' => '5',
	'shoutbox_placement' => 'top',
	'shoutbox_show_avatars' => '1',
	'shoutbox_enable_bbcode' => '1',
	'shoutbox_enable_smileys' => '1',
	'shoutbox_enable_mentions' => '1',
	'shoutbox_enable_whispers' => '1',
	'shoutbox_gif_provider' => 'tenor',
	'shoutbox_gif_api_key' => '',
	'shoutbox_enable_sounds' => '1',
	'shoutbox_enable_reactions' => '1',
	'shoutbox_show_smiley_picker' => '1',
	'shoutbox_show_bbc_toolbar' => '1',
	'shoutbox_guest_access' => '0',
	'shoutbox_auto_prune_days' => '30',
	'shoutbox_disable_cache' => '0',
	'shoutbox_disable_chatroom' => '0',
	'shoutbox_newest_first' => '0',
	'shoutbox_widget_height' => '280',
	'shoutbox_enable_attachments' => '0',
	'shoutbox_attachment_max_size' => '1024',
	'shoutbox_show_on_boardindex' => '1',
	'shoutbox_show_on_portal' => '1',
	'shoutbox_show_on_boards' => '1',
	'shoutbox_show_on_topics' => '1',
	'shoutbox_show_on_other' => '1',
	'shoutbox_exclude_actions' => '',
);

// Only insert settings that don't already exist (preserves user config on reinstall).
$new_settings = array();
foreach ($settings as $key => $value)
{
	if (!isset($modSettings[$key]))
		$new_settings[$key] = $value;
}

if (!empty($new_settings))
	updateSettings($new_settings);

// =========================================================================
// 3. Register integration hooks
// =========================================================================

$hooks = array(
	array('integrate_actions', 'shoutbox_actions', '$sourcedir/Shoutbox.php'),
	array('integrate_menu_buttons', 'shoutbox_menu_buttons', '$sourcedir/Shoutbox.php'),
	array('integrate_load_theme', 'shoutbox_load_theme', '$sourcedir/Shoutbox.php'),
	array('integrate_load_permissions', 'shoutbox_load_permissions', '$sourcedir/Shoutbox.php'),
	array('integrate_pre_log_stats', 'shoutbox_pre_log_stats', '$sourcedir/Shoutbox.php'),
	array('integrate_credits', 'shoutbox_credits', '$sourcedir/Shoutbox.php'),
	array('integrate_admin_areas', 'shoutbox_admin_areas', '$sourcedir/Shoutbox-Admin.php'),
	array('integrate_general_mod_settings', 'shoutbox_general_settings', '$sourcedir/Shoutbox-Admin.php'),
);

foreach ($hooks as $hook)
{
	add_integration_function($hook[0], $hook[1], true, $hook[2], false);
}


