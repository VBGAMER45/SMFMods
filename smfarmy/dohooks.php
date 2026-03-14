<?php
/**
 * Army System - Hook Registration
 * Registers or removes integration hooks during install/uninstall.
 *
 * @package ArmySystem
 * @version 1.0
 */

// Define the hooks
$hook_functions = array(
	'integrate_pre_include' => '$sourcedir/ArmySystem.php',
	'integrate_actions' => 'army_integrate_actions',
	'integrate_admin_areas' => 'army_integrate_admin_areas',
	'integrate_menu_buttons' => 'army_integrate_menu_buttons',
	'integrate_after_create_post' => 'army_integrate_post_gain',
	'integrate_load_permissions' => 'army_integrate_permissions',
	'integrate_pre_profile_areas' => 'army_integrate_profile',
	'integrate_load_theme' => 'army_integrate_theme',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);
