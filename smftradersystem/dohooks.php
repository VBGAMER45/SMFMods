<?php
/*
SMF Trader System
Version 2.0
by:vbgamer45
http://www.smfhacks.com
*/


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
        include_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
        die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/tradersHooks.php',
        'integrate_admin_areas' => 'trader_admin_areas',
		'integrate_actions' => 'trader_actions',
		'integrate_load_permissions' => 'trader_load_permissions',
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
('trader_smfversion','2.1')
");

?>