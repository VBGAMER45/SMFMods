<?php
/**
 * Ultimate Shoutbox & Chatroom - Uninstaller
 *
 * Removes database tables, and settings.
 *
 * @package Shoutbox
 * @version 1.1.0
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

$smcFunc['db_drop_table']('{db_prefix}shoutbox_messages');
$smcFunc['db_drop_table']('{db_prefix}shoutbox_bans');
$smcFunc['db_drop_table']('{db_prefix}shoutbox_rooms');
$smcFunc['db_drop_table']('{db_prefix}shoutbox_reactions');
$smcFunc['db_drop_table']('{db_prefix}shoutbox_attachments');

// =========================================================================
// 2. Remove settings
// =========================================================================

$settings_to_remove = array(
	'shoutbox_enabled',
	'shoutbox_poll_interval',
	'shoutbox_max_length',
	'shoutbox_max_display',
	'shoutbox_max_display_chatroom',
	'shoutbox_flood_time',
	'shoutbox_placement',
	'shoutbox_show_avatars',
	'shoutbox_enable_bbcode',
	'shoutbox_enable_smileys',
	'shoutbox_enable_mentions',
	'shoutbox_enable_whispers',
	'shoutbox_gif_provider',
	'shoutbox_gif_api_key',
	'shoutbox_enable_sounds',
	'shoutbox_enable_reactions',
	'shoutbox_guest_access',
	'shoutbox_auto_prune_days',
	'shoutbox_disable_cache',
	'shoutbox_disable_chatroom',
	'shoutbox_show_smiley_picker',
	'shoutbox_show_bbc_toolbar',
	'shoutbox_newest_first',
	'shoutbox_show_on_boardindex',
	'shoutbox_show_on_portal',
	'shoutbox_show_on_boards',
	'shoutbox_show_on_topics',
	'shoutbox_show_on_other',
	'shoutbox_exclude_actions',
	'shoutbox_widget_height',
	'shoutbox_enable_attachments',
	'shoutbox_attachment_max_size',
);

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable IN ({array_string:settings})',
	array(
		'settings' => $settings_to_remove,
	)
);

// Refresh settings cache.
updateSettings(array('shoutbox_removed' => '1'));
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable = {string:var}',
	array(
		'var' => 'shoutbox_removed',
	)
);

// Delete uploaded attachment files.
global $boarddir;
$upload_dir = $boarddir . '/shoutbox_uploads';
if (is_dir($upload_dir))
{
	$files = glob($upload_dir . '/*');
	if ($files)
	{
		foreach ($files as $file)
		{
			if (is_file($file))
				@unlink($file);
		}
	}
	@rmdir($upload_dir);
}