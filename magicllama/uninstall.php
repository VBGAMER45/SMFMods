<?php
/******************************************************************************
 * uninstall.php - Magic Llama Mod 2.0 for SMF 2.1
 * Removes all registered hooks.
 ******************************************************************************/

if (!defined('SMF'))
	die('This file may not be accessed directly.');

$hooks = array(
	'integrate_actions' => 'magic_llama_actions',
	'integrate_load_theme' => 'magic_llama_load_theme',
	'integrate_general_mod_settings' => 'magic_llama_settings',
	'integrate_admin_areas' => 'magic_llama_admin_areas',
	'integrate_profile_areas' => 'magic_llama_profile_areas',
	'integrate_load_member_data' => 'magic_llama_member_data',
	'integrate_member_context' => 'magic_llama_member_context',
	'integrate_prepare_display_context' => 'magic_llama_display_context',
);

foreach ($hooks as $hook => $function)
	remove_integration_function($hook, $function, true, '$sourcedir/MagicLlama.php');
