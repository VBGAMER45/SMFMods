<?php
/**
 * Ultimate Shoutbox & Chatroom - Uninstaller
 *
 * Removes database tables, hooks, and settings.
 *
 * @package Shoutbox
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

global $smcFunc;

// =========================================================================
// 1. Remove integration hooks
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
	remove_integration_function($hook[0], $hook[1], true, $hook[2], false);
}

