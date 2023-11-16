<?php
/*---------------------------------------------------------------------------------
*	SMFSimple Rewards System											 		  *
*	Version 3.0																	  *
*	Author: 4kstore																  *
*	Copyright 2012												        		  *
*	Powered by www.smfsimple.com												  *
***********************************************************************************
**********************************************************************************/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

$hooks = array(
	'integrate_pre_include' => '$sourcedir/SsrsHooks.php',
	'integrate_actions' => 'ssrs_actions',
	'integrate_load_permissions' => 'ssrs_permissions',
	'integrate_admin_areas' => 'ssrs_admin_area',
	'integrate_load_theme' => 'ssrs_load_theme',
	'integrate_buffer' => 'ssrs_Buffer',
);

if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';

else
	$call = 'add_integration_function';

foreach ($hooks as $hook => $function)
	call_user_func($call, $hook, $function);

if (SMF == 'SSI')
	echo 'Database changes are complete! Please wait...';