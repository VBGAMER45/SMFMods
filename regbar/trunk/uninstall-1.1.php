<?php
/**
 * Regbar Warning (regbar-warning_)
 *
 * @file ./uninstall-2.0.php
 * @author SMFHacks <http://www.smfhacks.com/>
 * @copyright SMFHacks.com Team, 2012
 *
 * @version 1.0.3
 */

// Using SSI?
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<strong>Error:</strong> Cannot uninstall - please make sure that this file in the same directory as SMF\'s SSI.php file.');

// Erm, admins, only.
global $context;
if (!$context['user']['is_admin'])
	exit('Nice try.');

global $db_prefix, $boarddir, $sourcedir, $settings;
$remove_files = array(
	$sourcedir . '/smfhacks_source' => array(
		'regbar-warning.php'
	),
	$settings['default_theme_dir'] . '/smfhacks_templates' => array(
		'regbar-warning.template.php'
	),
	$settings['default_theme_dir'] . '/smfhacks_css' => array(
		'regbar-warning.css'
	),
	$settings['default_theme_dir'] . '/images/smfhacks_images' => array(
		'regbar-warning.png'
	),
	$settings['default_theme_dir'] . '/languages/smfhacks_languages' => array(
		'regbar-warning.english.php',
		'regbar-warning.english-utf8.php'
	)
);
foreach ($remove_files as $key => $value)
	remove__recursive($key, $value);
function remove__recursive($dir, $file)
{
	if (is_array($file))
	{
		foreach ($file as $key => $value)
		{
			if (is_dir($dir . '/' . $key))
				remove__recursive($dir . '/' . $key, $value);
			elseif (is_file($dir . '/' . $value))
				unlink($dir . '/' . $value);
			$dir_contents = scandir($dir);
			unset($dir_contents[0], $dir_contents[1]);
			$index_file = array_search('index.php', $dir_contents);
			if (!empty($index_file))
				unset($dir_contents[$index_file]);
			if (empty($dir_contents)) {
				if (is_file($dir . '/index.php'))
					unlink($dir . '/index.php');
				rmdir($dir);
				continue;
			}
		}
	}
}

// Show the iframe with the uninstall
echo '
	<iframe src="http://www.smfhacks.com/uninstall.php?modname=UmVnYmFyV2FybmluZw==" width="100%" height="200px">
		<p>Your browser does not support iframes.</p>
	</iframe>
	<br />
	<strong>Other Modifications of Interest:</strong>
	<ul>
		<li><a href="http://www.ezportal.com" target="_blank">EzPortal</a></li>
		<li><a href="http://www.smfhacks.com/smf-gallery-pro.php" target="_blank">SMF Gallery Pro</a></li>
		<li><a href="http://www.smfhacks.com/smf-store.php" target="_blank">SMF Store</a></li>
		<li><a href="http://www.smfhacks.com/smf-classifieds.php" target="_blank">SMF Classifieds</a></li>
		<li><a href="http://www.smfhacks.com/newsletter-pro.php" target="_blank">Newsletter Pro</a></li>
		<li><a href="http://www.smfhacks.com/download-system-pro.php">Downloads System Pro</a> - A complete downloads system for SMF. With credits, paypal intergration and more!</li>
		<li><a href="http://www.smfhacks.com/ad-seller-pro.php" target="_blank">Ad Seller Pro</a></li>
	</ul>
	<br /><br />
';