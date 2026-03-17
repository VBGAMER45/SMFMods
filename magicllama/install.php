<?php
/******************************************************************************
 * install.php - Magic Llama Mod 2.0 for SMF 2.1
 * Creates database tables, default settings, and registers hooks.
 ******************************************************************************/

// Safety check - must be called from SMF package manager.
if (!defined('SMF'))
	die('This file may not be accessed directly.');

global $smcFunc, $modSettings;
db_extend('packages');
// Create the main llama tracking table.
$smcFunc['db_create_table']('{db_prefix}magic_llama',
	array(
		array('name' => 'id_llama', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'llama_type', 'type' => 'tinyint', 'size' => 3, 'unsigned' => true, 'default' => 1),
		array('name' => 'points', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'llama_hash', 'type' => 'varchar', 'size' => 32, 'default' => ''),
		array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'released_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'caught_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	array(
		array('name' => 'id_llama', 'columns' => array('id_llama'), 'type' => 'primary'),
		array('name' => 'idx_llama_hash', 'columns' => array('llama_hash'), 'type' => 'unique'),
	),
	array(),
	'update'
);

// Create the member stats table (replaces adding columns to core members table).
$smcFunc['db_create_table']('{db_prefix}magic_llama_members',
	array(
		array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
		array('name' => 'good_llamas', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'good_points', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'bad_llamas', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'bad_points', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'hide_llama', 'type' => 'tinyint', 'size' => 3, 'unsigned' => true, 'default' => 0),
	),
	array(
		array('name' => 'id_member', 'columns' => array('id_member'), 'type' => 'primary'),
	),
	array(),
	'update'
);

// Insert default settings.
$defaults = array(
	'magic_llama_enabled' => 1,
	'magic_llama_show_stats' => 1,
	'magic_llama_allow_hide' => 1,
	'magic_llama_show_in_posts' => 1,
	'magic_llama_chances' => 5,
	'magic_llama_image' => 'golden_llama2.gif',
	'magic_llama_width' => 0,
	'magic_llama_height' => 0,
	'magic_llama_speed' => 3,
	'magic_llama_type1_name' => 'Golden Llama',
	'magic_llama_type1_min' => 1,
	'magic_llama_type1_max' => 10,
	'magic_llama_type1_msg' => '%N caught a %K worth %P points!',
	'magic_llama_type2_name' => 'Evil Llama',
	'magic_llama_type2_min' => 1,
	'magic_llama_type2_max' => 5,
	'magic_llama_type2_msg' => '%N was bitten by a %K and lost %P points!',
	'magic_llama_late_msg' => 'Too late! Someone else caught that llama.',
	'magic_llama_freed' => 0,
);

// Only set defaults that don't already exist (preserves settings on reinstall).
$new_settings = array();
foreach ($defaults as $key => $value)
{
	if (!isset($modSettings[$key]))
		$new_settings[$key] = $value;
}

if (!empty($new_settings))
	updateSettings($new_settings);

// Register all hooks.
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
	add_integration_function($hook, $function, true, '$sourcedir/MagicLlama.php');
