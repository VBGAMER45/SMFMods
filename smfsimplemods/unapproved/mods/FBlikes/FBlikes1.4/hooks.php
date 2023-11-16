<?php
/*---------------------------------------------------------------------------------
*	FBLIKE																	 	  *
*	Author: SSimple Team														  *
*	Copyright 2012 														          *
*	Powered by www.smfsimple.com												  *
***********************************************************************************/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');

$hooks = array(
	'integrate_pre_include' => '$sourcedir/Fblike-hooks.php',
	'integrate_actions' => 'Fblike_actions',	
	'integrate_admin_areas' => 'Fblike_Admin',
	'integrate_modify_modifications' => 'Fblike_Admin_Settings',
	'integrate_bbc_codes' => 'Fblike_add_code',
	'integrate_bbc_buttons' => 'Fblike_add_button',
	'integrate_load_theme' => 'Fblike_load_theme',	
);

if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hooks as $hook => $function)
	$call($hook, $function);
