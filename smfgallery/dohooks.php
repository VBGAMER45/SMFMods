<?php
/*
SMF Gallery Lite Edition
Version 7.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2008-2021 SMFHacks.com

############################################
License Information:
SMF Gallery is NOT free software.
This software may not be redistributed.

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/


// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/GalleryHooks.php',
        'integrate_admin_areas' => 'gallery_admin_areas',
	    'integrate_menu_buttons' => 'gallery_menu_buttons',
		'integrate_actions' => 'gallery_actions',
		'integrate_load_permissions' => 'gallery_load_permissions',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);

// Insert the settings for 2.1
if (function_exists("set_tld_regex"))
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('gallery_smfversion','2.1beta')
");

?>