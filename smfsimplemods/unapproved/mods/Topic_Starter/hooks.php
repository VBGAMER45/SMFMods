<?php
/*-----------------------------------
*	Topic Starter BBC 1.1			*
*	Author: SSimple Team - 4KSTORE	*
*	Powered by www.smfsimple.com	*
************************************/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

global $user_info, $context;

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

$hooks = array(
	'integrate_pre_include' => '$sourcedir/TopicStarterBBC.php',
	'integrate_bbc_codes' => 'tsbbc_add_code',
	'integrate_buffer' => 'tsbbc_Buffer',
);

if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';

else
	$call = 'add_integration_function';

foreach ($hooks as $hook => $function)
	call_user_func($call, $hook, $function);

if (SMF == 'SSI')
	echo 'Database changes are complete! Please wait...';