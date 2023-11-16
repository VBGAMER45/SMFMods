<?php
/*---------------------------------------------------------------------------------
*	Force Read Pms														 		  *
*	Version 1.0																	  *
*	Author: 4kstore																  *
*	Copyright 2013												        		  *
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
	'integrate_pre_include' => '$sourcedir/FixedSocialBookmarks.php',
	'integrate_admin_areas' => 'fsb_admin_area',
	'integrate_load_theme' => 'fsb_load_theme',
	'integrate_modify_modifications' => 'fsb_modify_modifications',
	'integrate_buffer' => 'fsb_Buffer',
);

if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';

else
	$call = 'add_integration_function';

foreach ($hooks as $hook => $function)
	call_user_func($call, $hook, $function);

if (SMF == 'SSI')
	echo 'Database changes are complete! Please wait...';