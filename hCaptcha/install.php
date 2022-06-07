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

add_integration_function('integrate_theme_include', '$sourcedir/hcaptcha.php', TRUE);
add_integration_function('integrate_load_theme', 'load_hcaptcha', TRUE);



// Insert the settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('hcaptcha_enabled', '0'),
('hcaptcha_theme', 'light'),
('hcaptcha_private_key', ''),
('hcaptcha_public_key', '')");