<?php
/*
Welcome Topic Mod
Version 2.0
by:vbgamer45
https://www.smfhacks.com
*/

// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/welcomeHooks.php',
        'integrate_admin_areas' => 'welcome_admin_areas',
		'integrate_actions' => 'welcome_actions',
		'integrate_activate' => 'welcome_integrate_activate',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);


?>