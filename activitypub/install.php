<?php
/**
 * ActivityPub Federation - Installer
 *
 * Creates database tables, registers hooks, and inserts default settings.
 *
 * @package ActivityPub
 * @version 1.0.0
 * By: vbgamer45
 * https://www.smfhacks.com
 */

if (!defined('SMF'))
	die('No direct access...');

global $smcFunc, $modSettings;

// =========================================================================
// 1. Create database tables
// =========================================================================

$tables = array();

// ap_actors - Local (boards/users) + remote actor records
$tables[] = array(
	'table_name' => '{db_prefix}ap_actors',
	'columns' => array(
		array('name' => 'id_actor', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'ap_id', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'type', 'type' => 'varchar', 'size' => 20, 'default' => 'Person'),
		array('name' => 'preferred_username', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'summary', 'type' => 'text'),
		array('name' => 'inbox_url', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'outbox_url', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'shared_inbox_url', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'followers_url', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'following_url', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'url', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'icon_url', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'public_key_pem', 'type' => 'text'),
		array('name' => 'private_key_pem', 'type' => 'text'),
		array('name' => 'is_local', 'type' => 'tinyint', 'size' => 1, 'unsigned' => true, 'default' => 0),
		array('name' => 'local_type', 'type' => 'varchar', 'size' => 10, 'default' => ''),
		array('name' => 'local_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'enabled', 'type' => 'tinyint', 'size' => 1, 'unsigned' => true, 'default' => 1),
		array('name' => 'last_fetched', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'updated_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'raw_data', 'type' => 'mediumtext'),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_actor')),
		array('type' => 'unique', 'name' => 'idx_ap_id', 'columns' => array('ap_id(255)')),
		array('type' => 'index', 'name' => 'idx_local', 'columns' => array('is_local', 'local_type', 'local_id')),
		array('type' => 'index', 'name' => 'idx_shared_inbox', 'columns' => array('shared_inbox_url(255)')),
	),
);

// ap_objects - AP objects (local posts + remote content)
$tables[] = array(
	'table_name' => '{db_prefix}ap_objects',
	'columns' => array(
		array('name' => 'id_object', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'ap_id', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'type', 'type' => 'varchar', 'size' => 50, 'default' => 'Note'),
		array('name' => 'actor_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'in_reply_to', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'context_url', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'audience_url', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'content', 'type' => 'mediumtext'),
		array('name' => 'summary', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'url', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'published', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'updated_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'is_local', 'type' => 'tinyint', 'size' => 1, 'unsigned' => true, 'default' => 0),
		array('name' => 'local_msg_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'local_topic_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'local_board_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'is_deleted', 'type' => 'tinyint', 'size' => 1, 'unsigned' => true, 'default' => 0),
		array('name' => 'raw_data', 'type' => 'mediumtext'),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_object')),
		array('type' => 'unique', 'name' => 'idx_ap_id', 'columns' => array('ap_id(255)')),
		array('type' => 'index', 'name' => 'idx_local_msg', 'columns' => array('local_msg_id')),
		array('type' => 'index', 'name' => 'idx_context', 'columns' => array('context_url(255)')),
		array('type' => 'index', 'name' => 'idx_actor', 'columns' => array('actor_id')),
	),
);

// ap_activities - Activity log (inbound + outbound)
$tables[] = array(
	'table_name' => '{db_prefix}ap_activities',
	'columns' => array(
		array('name' => 'id_activity', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'ap_id', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'type', 'type' => 'varchar', 'size' => 50, 'default' => ''),
		array('name' => 'actor_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'object_ap_id', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'object_type', 'type' => 'varchar', 'size' => 50, 'default' => ''),
		array('name' => 'target_ap_id', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'direction', 'type' => 'varchar', 'size' => 10, 'default' => 'outbound'),
		array('name' => 'status', 'type' => 'varchar', 'size' => 15, 'default' => 'pending'),
		array('name' => 'raw_data', 'type' => 'mediumtext'),
		array('name' => 'error_message', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'processed_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_activity')),
		array('type' => 'unique', 'name' => 'idx_ap_id', 'columns' => array('ap_id(255)')),
		array('type' => 'index', 'name' => 'idx_type_status', 'columns' => array('type', 'status')),
		array('type' => 'index', 'name' => 'idx_direction_status', 'columns' => array('direction', 'status')),
		array('type' => 'index', 'name' => 'idx_created', 'columns' => array('created_at')),
	),
);

// ap_followers - Follow relationships
$tables[] = array(
	'table_name' => '{db_prefix}ap_followers',
	'columns' => array(
		array('name' => 'id_follow', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'actor_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'follower_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'follow_activity_id', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'status', 'type' => 'varchar', 'size' => 10, 'default' => 'pending'),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'accepted_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_follow')),
		array('type' => 'unique', 'name' => 'idx_pair', 'columns' => array('actor_id', 'follower_id')),
		array('type' => 'index', 'name' => 'idx_actor_status', 'columns' => array('actor_id', 'status')),
	),
);

// ap_delivery_queue - Outbound delivery with exponential retry
$tables[] = array(
	'table_name' => '{db_prefix}ap_delivery_queue',
	'columns' => array(
		array('name' => 'id_delivery', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'activity_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'target_inbox', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'actor_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'payload', 'type' => 'mediumtext'),
		array('name' => 'status', 'type' => 'varchar', 'size' => 15, 'default' => 'queued'),
		array('name' => 'attempts', 'type' => 'tinyint', 'size' => 3, 'unsigned' => true, 'default' => 0),
		array('name' => 'max_attempts', 'type' => 'tinyint', 'size' => 3, 'unsigned' => true, 'default' => 8),
		array('name' => 'last_attempt', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'next_retry', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'error_message', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_delivery')),
		array('type' => 'index', 'name' => 'idx_status_retry', 'columns' => array('status', 'next_retry')),
		array('type' => 'index', 'name' => 'idx_activity', 'columns' => array('activity_id')),
	),
);

// ap_blocks - Domain block/silence list
$tables[] = array(
	'table_name' => '{db_prefix}ap_blocks',
	'columns' => array(
		array('name' => 'id_block', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'domain', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'block_type', 'type' => 'varchar', 'size' => 10, 'default' => 'block'),
		array('name' => 'reason', 'type' => 'varchar', 'size' => 512, 'default' => ''),
		array('name' => 'created_by', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_block')),
		array('type' => 'unique', 'name' => 'idx_domain', 'columns' => array('domain')),
	),
);

// Use SMF's db_create_table function.
db_extend('packages');

foreach ($tables as $table)
{
	$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], array(), 'ignore');
}

// =========================================================================
// 2. Insert default settings
// =========================================================================

$settings = array(
	'activitypub_enabled' => '0',
	'activitypub_auto_accept_follows' => '1',
	'activitypub_user_actors_enabled' => '0',
	'activitypub_user_opt_in' => '1',
	'activitypub_max_delivery_attempts' => '8',
	'activitypub_delivery_batch_size' => '50',
	'activitypub_content_mode' => 'note',
	'activitypub_rate_limit_inbox' => '100',
);

// Only insert settings that don't already exist (preserves user config on reinstall).
$new_settings = array();
foreach ($settings as $key => $value)
{
	if (!isset($modSettings[$key]))
		$new_settings[$key] = $value;
}

// Generate encryption key for private key storage if not set.
if (!isset($modSettings['activitypub_encryption_key']))
{
	$new_settings['activitypub_encryption_key'] = bin2hex(openssl_random_pseudo_bytes(32));
}

if (!empty($new_settings))
	updateSettings($new_settings);

// =========================================================================
// 3. Register integration hooks
// =========================================================================

$hooks = array(
	// Action routing
	array('integrate_actions', 'activitypub_actions', '$sourcedir/ActivityPub.php'),
	// Admin menu
	array('integrate_admin_areas', 'activitypub_admin_areas', '$sourcedir/ActivityPub-Admin.php'),
	// Content hooks - trigger outbound activities
	array('integrate_after_create_post', 'activitypub_after_create_post', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_modify_post', 'activitypub_after_modify_post', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_remove_message', 'activitypub_after_remove_message', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_remove_topics', 'activitypub_after_remove_topics', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_issue_like', 'activitypub_after_issue_like', '$sourcedir/Subs-ActivityPub.php'),
	// Board settings
	array('integrate_board_info', 'activitypub_board_info', '$sourcedir/Subs-ActivityPub.php'),
	// User profile
	array('integrate_profile_areas', 'activitypub_profile_areas', '$sourcedir/Subs-ActivityPub.php'),
	// HTML head injection (alternate links)
	array('integrate_buffer', 'activitypub_buffer_replace', '$sourcedir/Subs-ActivityPub.php'),
	// Permissions
	array('integrate_load_permissions', 'activitypub_load_permissions', '$sourcedir/Subs-ActivityPub.php'),
);

foreach ($hooks as $hook)
{
	add_integration_function($hook[0], $hook[1], true, $hook[2], false);
}
