<?php
/*---------------------------------------------------------------------------------
*	SMFSIMPLE BBCUserInfo													 	  *
*	Author: SSimple Team - 4KSTORE										          *
*	Powered by www.smfsimple.com												  *
**********************************************************************************/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

global $user_info, $context;

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

$hooks = array(
	'integrate_pre_include' => '$sourcedir/Userinfo.php',
	'integrate_load_theme' => 'User_info_load_theme',
	'integrate_bbc_codes' => 'User_info_add_code',
	'integrate_bbc_buttons' => 'User_info_add_button',
	'integrate_actions' => 'User_info_actions',
	'integrate_admin_areas' => 'User_info_Admin',
	'integrate_modify_modifications' => 'User_info_Admin_Settings',
	'integrate_buffer' => 'User_info_Buffer',
);

$final_variables = array( 'uic_enable', 'uic_guest_can', 'uic_style', 'uic_act_group_image', 'uic_act_personal_text', 'uic_act_contact_icons' );

if (!empty($context['uninstalling']))
{
	$call = 'remove_integration_function';
	global $smcFunc;
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}settings
		WHERE variable IN ({array_string:final_variables})',
		array(
			'final_variables' => $final_variables
		)
	);
}
else
{
	$call = 'add_integration_function';
	$final_values = array( 1, 1, 'qtip-blue', 1, 1, 1 );

	foreach ($final_variables as $key => $value)
		$final_array[$value] = $final_values[$key];

	updateSettings($final_array);
	reloadSettings();
}
foreach ($hooks as $hook => $function)
	call_user_func($call, $hook, $function);



if (SMF == 'SSI')
	echo 'Database changes are complete! Please wait...';