<?php
/*
RSS Feed Poster
Version 5.0
by:vbgamer45
https://www.smfhacks.com
*/

// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/feedposterHooks.php',
        'integrate_admin_areas' => 'feedposter_admin_areas',
		'integrate_actions' => 'feedposter_actions',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);


?>