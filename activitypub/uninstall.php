<?php
/**
 * ActivityPub Federation - Uninstaller
 *
 * Removes integration hooks (keeps tables and settings for uninstalldb.php).
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
// 1. Remove integration hooks
// =========================================================================

$hooks = array(
	array('integrate_actions', 'activitypub_actions', '$sourcedir/ActivityPub.php'),
	array('integrate_admin_areas', 'activitypub_admin_areas', '$sourcedir/ActivityPub-Admin.php'),
	array('integrate_after_create_post', 'activitypub_after_create_post', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_modify_post', 'activitypub_after_modify_post', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_remove_message', 'activitypub_after_remove_message', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_remove_topics', 'activitypub_after_remove_topics', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_issue_like', 'activitypub_after_issue_like', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_board_info', 'activitypub_board_info', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_profile_areas', 'activitypub_profile_areas', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_buffer', 'activitypub_buffer_replace', '$sourcedir/Subs-ActivityPub.php'),
	array('integrate_load_permissions', 'activitypub_load_permissions', '$sourcedir/Subs-ActivityPub.php'),
);

foreach ($hooks as $hook)
{
	remove_integration_function($hook[0], $hook[1], true, $hook[2], false);
}
