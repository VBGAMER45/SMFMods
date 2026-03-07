<?php
/**********************************************************************************
 * dohooks.php                                                                     *
 ***********************************************************************************
 * SMF Garage: Simple Machines Forum Garage (MOD)                                  *
 * =============================================================================== *
 * Software Version:           SMF Garage 3.0.0                                    *
 * Install for:                2.1.0-2.1.99                                        *
 * Copyright 2026 by:          vbgamer45 (https://www.smfhacks.com)               *
 ***********************************************************************************
 * Registers/unregisters SMF integration hooks for the Garage mod.                 *
 * Called by package-info.xml during install and uninstall.                         *
 **********************************************************************************/

// Define the hooks
$hook_functions = array(
	'integrate_pre_include' => '$sourcedir/GarageHooks.php',
	'integrate_actions' => 'garage_hook_actions',
	'integrate_admin_areas' => 'garage_hook_admin_areas',
	'integrate_menu_buttons' => 'garage_hook_menu_buttons',
	'integrate_load_permissions' => 'garage_hook_load_permissions',
	'integrate_heavy_permissions_session' => 'garage_hook_heavy_permissions',
	'integrate_whos_online' => 'garage_hook_whos_online',
	'whos_online_after' => 'garage_hook_whos_online_after',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);

?>
