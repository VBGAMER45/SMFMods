<?php
/*
SMF Articles
Version 3.0
by:vbgamer45
http://www.smfhacks.com
*/


// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/ArticlesHooks.php',
        'integrate_admin_areas' => 'articles_admin_areas',
	    'integrate_menu_buttons' => 'articles_menu_buttons',
		'integrate_actions' => 'articles_actions',
		'integrate_load_permissions' => 'articles_load_permissions',
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
('articles_smfversion','2.1')
");

?>