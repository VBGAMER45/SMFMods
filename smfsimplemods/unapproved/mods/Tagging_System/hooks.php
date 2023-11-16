<?php
/*---------------------------------------------------------------------------------
*	SMFSIMPLE Tagging System												 	  *
*	Author: SSimple Team - 4KSTORE										          *
*	Powered by www.smfsimple.com												  *
**********************************************************************************/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

$hooks = array(
	'integrate_pre_include' => '$sourcedir/TaggingSystem-hooks.php, $sourcedir/TaggingSystem.php',
	'integrate_actions' => 'tagging_actions',
	'integrate_menu_buttons' => 'tagging_menu_button',
	'integrate_admin_areas' => 'tagging_admin_area',
	'integrate_load_theme' => 'tagging_load_theme',
);

if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';

else
	$call = 'add_integration_function';

foreach ($hooks as $hook => $function)
	call_user_func($call, $hook, $function);

if (SMF == 'SSI')
	echo 'Database changes are complete! Please wait...';