<?php

/* *********************************************************************************
 * add_remove_hooks.php                                                            *
 ***********************************************************************************
 ***********************************************************************************
 * This program is distributed in the hope that it is and will be useful, but      *
 * WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
 * or FITNESS FOR A PARTICULAR PURPOSE.                                            *
 *                                                                                 *
 * This file is a simplified database installer. It does what it is suppoed to.    *
 ********************************************************************************* */

// If we have found SSI.php and we are outside of SMF, then we are running standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF')) // If we are outside SMF and can't find SSI.php, then throw an error
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as SMF\'s SSI.php.');

if (SMF == 'SSI')
	db_extend('packages');

// Define the hooks
$hook_functions = array(
	'integrate_pre_include' => '$sourcedir/GoogleMapIntegration.php',
	'integrate_admin_areas' => 'iaa_googlemap',
	'integrate_modify_modifications' => 'imm_googlemap',
	'integrate_actions' => 'ia_googlemap',
	'integrate_menu_buttons' => 'imb_googlemap',
	'integrate_load_permissions' => 'ilp_googlemap'
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

// Do the deed
foreach ($hook_functions as $hook => $function)
	$call($hook, $function);

if (SMF == 'SSI')
	echo 'Congratulations! You have successfully installed the mod hooks';


if (!empty($context['uninstalling']))
{

// Show the iframe with the uninstall
$modName = base64_encode("Google Member Map");
echo '<iframe src ="https://www.smfhacks.com/uninstall.php?modname=' . $modName . '" width="100%" height="200px">
  <p>Your browser does not support iframes.</p>
</iframe>
<b>Other Helpful Mods to make your forum stand out</b><br />
<a href="http://www.smfhacks.com/smf-gallery-pro.php" target="_blank">SMF Gallery Pro</a> - A fully featured gallery for SMF<br />
<a href="http://www.smfhacks.com/smf-store.php" target="_blank">SMF Store</a> - eCommerce Store system using PayPal<br />
<a href="http://www.smfhacks.com/smf-classifieds.php" target="_blank">SMF Classifieds</a> - Auction/Listing system for SMF<br />
<a href="http://www.smfhacks.com/ad-seller-pro.php" target="_blank">Ad Seller Pro</a> - Make more money from your forum with ads<br />
<a href="http://www.smfhacks.com/download-system-pro.php" target="_blank">Downloads System Pro</a> - charge people for downloads<br />
<a href="http://www.smfhacks.com/awesomepostratings.php" target="_blank">Awesome Post Ratings</a> - gain more interaction from your visitors<br />
<a href="http://www.smfhacks.com/newsletter-pro.php" target="_blank">Newsletter Pro</a> - send html emails with open, click and unsubscribe tracking.<br />

';

}