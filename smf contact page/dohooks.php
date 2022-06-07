<?php
/*
Contact Page
Version 6.0
by:vbgamer45
https://www.smfhacks.com
*/

// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/contactHooks.php',
        'integrate_general_mod_settings' => 'contact_settings',
	    'integrate_menu_buttons' => 'contact_menu_buttons',
		'integrate_actions' => 'contact_actions',
		'integrate_load_permissions' => 'contact_load_permissions',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);

// Install setting
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('smfcontactpage_board', '0')");

?>