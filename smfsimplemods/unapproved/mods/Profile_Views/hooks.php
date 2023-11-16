<?php
/*---------------------------------------------------------------------------------
*	Profile Views 2.0															  *
*	Author: SSimple Team														  *
*	Copyright 2013 														          *
*	Powered by www.smfsimple.com												  *
***********************************************************************************/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

$hooks = array(
	'integrate_pre_include' => '$sourcedir/ProfileViewsMod.php',
	'integrate_general_mod_settings' => 'pvm_settings',
	'integrate_load_theme' => 'pvm_load_theme',
);

if (!empty($context['uninstalling']))
{
	$call = 'remove_integration_function';
	
	db_extend('packages');
	
	$smcFunc['db_query']('', "
		DELETE FROM {db_prefix}settings 
		WHERE variable LIKE 'pvm_%'");

	$smcFunc['db_drop_table']('{db_prefix}log_profile_views', array(), 'ignore');
}
else
	$call = 'add_integration_function';

foreach ($hooks as $hook => $function)
	call_user_func($call, $hook, $function);

if (SMF == 'SSI')
	echo 'Database changes are complete! Please wait...';