<?php
/*
EzPortal
Version 3.0
by:vbgamer45
http://www.ezportal.com
Copyright 2010-2015 http://www.samsonsoftware.com
*/

// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/EzPortalHooks.php',
        'integrate_admin_areas' => 'ezphook_admin_areas',
	    'integrate_menu_buttons' => 'ezphook_menu_buttons',
		'integrate_actions' => 'ezphook_actions',
		'integrate_load_permissions' => 'ezphook_load_permissions',
		'integrate_mark_read_button' => 'ezphook_integrate_mark_read_button',
		'integrate_pre_log_stats' => 'ezphook_integrate_pre_log_stats',
		'integrate_default_action' => 'ezphook_integrate_default_action',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);

// Insert the settings for 2.1
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('ezportal_smfversion','2.1')
");

?>