<?php
/*
Zapier for SMF
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/



if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

// Define the hooks
$hook_functions = array(
	   'integrate_pre_include' => '$sourcedir/zapierhooks.php',
       'integrate_admin_areas' => 'zapier_admin_areas',
);


// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);


if (empty($modSettings['zapier_hash']))
{
    $zaphash = hash('sha256', mt_rand());

    $smcFunc['db_query']('', "REPLACE INTO {db_prefix}settings VALUES ('zapier_hash', '$zaphash')");
}
 



?>