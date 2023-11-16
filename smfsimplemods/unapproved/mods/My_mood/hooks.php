<?php
/*----------------------------------------------------------------------------------/
*	My Mood                                             	                        *
*	Author: SSimple Team - 4KSTORE						 							*
*	Powered by www.smfsimple.com						   							*
************************************************************************************/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

$hooks = array(
	'integrate_pre_include' => '$sourcedir/MyMood.php',
	'integrate_load_theme' => 'Mymood_load_theme',
	'integrate_admin_areas' => 'Mymood_admin_area',
	'integrate_modify_modifications' => 'Mymood_modify_modifications',
	'integrate_buffer' => 'Mymood_Buffer',
);

if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hooks as $hook => $function)
	$call($hook, $function);