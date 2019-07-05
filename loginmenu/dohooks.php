<?php
/*
Login Menu Button
Version 1.0
by:vbgamer45
https://www.smfhacks.com
*/


// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/LoginMenuHooks.php',
	    'integrate_menu_buttons' => 'login_menu_buttons',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);


?>