<?php
/*
Awesome Post Ratings
Version 1.0
by:vbgamer45
http://www.smfhacks.com

License Information:
Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
*/




// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/AwesomePostRatingsHooks.php',
		'integrate_actions' => 'awesomepost_actions',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);


?>