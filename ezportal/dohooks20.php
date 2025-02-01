<?php
/*
EzPortal
Version 3.0
by:vbgamer45
http://www.ezportal.com
Copyright 2010-2021 http://www.samsonsoftware.com
*/

// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/EzPortalHooks.php',
	    'integrate_menu_buttons' => 'ezphook_menu_buttons',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);



?>