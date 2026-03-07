<?php
/**
 * SMF Rivals - Hook Removal
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc;

// =========================================================================
// 1. Remove integration hooks
// =========================================================================

$hooks = array(
	array('integrate_actions', 'rivals_actions', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_menu_buttons', 'rivals_menu_buttons', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_current_action', 'rivals_current_action', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_admin_areas', 'rivals_admin_areas', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_load_permissions', 'rivals_load_permissions', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_load_illegal_guest_permissions', 'rivals_illegal_guest_permissions', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_load_theme', 'rivals_load_theme', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_alert_types', 'rivals_alert_types', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_fetch_alerts', 'rivals_fetch_alerts', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_profile_areas', 'rivals_profile_areas', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_credits', 'rivals_credits', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_pre_log_stats', 'rivals_pre_log_stats', '$sourcedir/Rivals/RivalsHooks.php'),
);

foreach ($hooks as $hook)
{
	remove_integration_function($hook[0], $hook[1], true, $hook[2], false);
}

// =========================================================================
// 2. Remove permissions
// =========================================================================

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}permissions
	WHERE permission IN ({array_string:permissions})',
	array(
		'permissions' => array(
			'rivals_view',
			'rivals_manage_clan',
			'rivals_challenge',
			'rivals_report',
			'rivals_moderate',
			'rivals_admin',
		),
	)
);

// =========================================================================
// 3. Remove settings
// =========================================================================

$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable LIKE {string:prefix}',
	array('prefix' => 'rivals_%')
);
?>