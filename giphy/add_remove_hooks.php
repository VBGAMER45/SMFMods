<?php

// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

// Integration functions
$hooks = array(
	'integrate_pre_include' => '$sourcedir/giphy.php',
	'integrate_bbc_buttons' => 'giphy_bbc_buttons',
	'integrate_credits' => 'giphy_credits',
	'integrate_sceditor_options' => 'giphy_sceditor',
	'integrate_general_mod_settings' => 'giphy_mod_settings'
);

// Actually do it
foreach ($hooks as $hook => $function)
	$call($hook, $function);
