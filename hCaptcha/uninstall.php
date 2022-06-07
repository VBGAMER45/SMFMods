<?php
/**
 *  hCaptcha for SMF by vbgamer45
 *  @license   https://choosealicense.com/licenses/bsd-3-clause/ BSD-3-Clause
 *
 *  Based on reCAPTCHA for SMF
 * @author    Michael Johnson <youngmug@animeneko.net>
 * @copyright 2007-2018 Michael Johnson
 * @license   https://choosealicense.com/licenses/bsd-3-clause/ BSD-3-Clause
 */


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

remove_integration_function('integrate_theme_include', '$sourcedir/hcaptcha.php', TRUE);
remove_integration_function('integrate_load_theme', 'load_hcaptcha', TRUE);


// Show the iframe with the uninstall
$modName = base64_encode("hCaptcha");
echo '<iframe src ="https://www.smfhacks.com/uninstall.php?modname=' . $modName . '" width="100%" height="200px">
  <p>Your browser does not support iframes.</p>
</iframe>
<br />
<b>Other Software of interest</b><br />
<a href="https://www.ezportal.com" target="_blank">EzPortal</a><br />
<a href="https://www.smfhacks.com/smf-gallery-pro.php" target="_blank">SMF Gallery Pro</a><br />
<a href="https://www.smfhacks.com/smf-store.php" target="_blank">SMF Store</a><br />
<a href="https://www.smfhacks.com/smf-classifieds.php" target="_blank">SMF Classifieds</a><br />
<a href="https://www.smfhacks.com/newsletter-pro.php" target="_blank">Newsletter Pro</a><br />
<a href="https://www.smfhacks.com/download-system-pro.php">Downloads System Pro</a> - A complete downloads system for SMF. With credits, paypal integration and more!<br />


';