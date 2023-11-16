<?php
/*
SMF Archive
Version 3.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2008-2023 http://www.samsonsoftware.com
*/

// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/ArchiveHooks.php',
	   'integrate_buffer' => 'archive_buffer',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);



?>