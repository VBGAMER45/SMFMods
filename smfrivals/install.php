<?php
/**
 * SMF Rivals - Hook Registration + Default Settings
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc, $modSettings;

// =========================================================================
// 1. Register integration hooks
// =========================================================================

$hooks = array(
	// Core routing
	array('integrate_actions', 'rivals_actions', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_menu_buttons', 'rivals_menu_buttons', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_current_action', 'rivals_current_action', '$sourcedir/Rivals/RivalsHooks.php'),

	// Admin
	array('integrate_admin_areas', 'rivals_admin_areas', '$sourcedir/Rivals/RivalsHooks.php'),

	// Permissions
	array('integrate_load_permissions', 'rivals_load_permissions', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_load_illegal_guest_permissions', 'rivals_illegal_guest_permissions', '$sourcedir/Rivals/RivalsHooks.php'),

	// Theme/CSS/JS
	array('integrate_load_theme', 'rivals_load_theme', '$sourcedir/Rivals/RivalsHooks.php'),

	// Alerts
	array('integrate_alert_types', 'rivals_alert_types', '$sourcedir/Rivals/RivalsHooks.php'),
	array('integrate_fetch_alerts', 'rivals_fetch_alerts', '$sourcedir/Rivals/RivalsHooks.php'),

	// Profile
	array('integrate_profile_areas', 'rivals_profile_areas', '$sourcedir/Rivals/RivalsHooks.php'),

	// Credits
	array('integrate_credits', 'rivals_credits', '$sourcedir/Rivals/RivalsHooks.php'),

	// Pre-log stats
	array('integrate_pre_log_stats', 'rivals_pre_log_stats', '$sourcedir/Rivals/RivalsHooks.php'),
);

foreach ($hooks as $hook)
{
	add_integration_function($hook[0], $hook[1], true, $hook[2], false);
}

// =========================================================================
// 2. Insert default settings
// =========================================================================

$settings = array(
	'rivals_enabled' => 1,
	'rivals_min_posts' => 0,
	'rivals_banned_group' => 0,
	'rivals_frost_cost' => 25,
	'rivals_inactivity_penalty' => 0,
	'rivals_max_report_hours' => 72,
	'rivals_kickout_days' => 30,
	'rivals_mod_override' => 0,
	'rivals_version' => '1.0.0',
	'rivals_logo_max_size' => 163840,
	'rivals_logo_max_width' => 500,
	'rivals_logo_max_height' => 500,
);

$inserts = array();
foreach ($settings as $variable => $value)
{
	$inserts[] = array($variable, $value);
}

$smcFunc['db_insert']('ignore',
	'{db_prefix}settings',
	array('variable' => 'string', 'value' => 'string'),
	$inserts,
	array('variable')
);

// =========================================================================
// 3. Insert default permissions
// =========================================================================

$permissions = array(
	'rivals_view' => array(-1, 0, 2),
	'rivals_manage_clan' => array(0, 2),
	'rivals_challenge' => array(0, 2),
	'rivals_report' => array(0, 2),
	'rivals_moderate' => array(2, 3),
	'rivals_admin' => array(1),
);

$perm_inserts = array();
foreach ($permissions as $permission => $groups)
{
	$result = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}permissions
		WHERE permission = {string:permission}',
		array('permission' => $permission)
	);
	list ($count) = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);

	if ($count == 0)
	{
		foreach ($groups as $group)
			$perm_inserts[] = array($group, $permission);
	}
}

if (!empty($perm_inserts))
{
	$smcFunc['db_insert']('insert',
		'{db_prefix}permissions',
		array('id_group' => 'int', 'permission' => 'string'),
		$perm_inserts,
		array()
	);
}
?>