<?php
/*
GDPR Helper
Version 1.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2018-2019 SMFHacks.com

############################################
License Information:

Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################

*/

// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/gdprHooks.php',
        'integrate_admin_areas' => 'gdpr_admin_areas',
		'integrate_actions' => 'gdpr_actions',
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
('downloads_smfversion','2.1')
");

?>