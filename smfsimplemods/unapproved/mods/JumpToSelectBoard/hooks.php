<?php
/*---------------------------------------
*@Jump To Select Board V1.1				*
*@Author: SSimple Team - 4KSTORE		*
*@Powered by www.smfsimple.com			*
*@agustintari@hotmail.com				*
****************************************/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

global $user_info, $context, $smcFunc;

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

$hooks = array(
	'integrate_pre_include' => '$sourcedir/JumpToSelectBoard.php',
	'integrate_load_theme' => 'jtsb_mod_load_theme',
	'integrate_buffer' => 'jtsb_mod_Buffer',
);

db_extend('packages');

if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';


else
	$call = 'add_integration_function';


foreach ($hooks as $hook => $function)
	call_user_func($call, $hook, $function);

if (SMF == 'SSI')
	echo 'Database changes are complete! Please wait...';