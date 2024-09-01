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
	'integrate_pre_include' => '$sourcedir/tenor.php',
	'integrate_bbc_buttons' => 'tenor_bbc_buttons',
	'integrate_credits' => 'tenor_credits',
	'integrate_sceditor_options' => 'tenor_sceditor',
	'integrate_general_mod_settings' => 'tenor_mod_settings'
);

// Actually do it
foreach ($hooks as $hook => $function)
	$call($hook, $function);
