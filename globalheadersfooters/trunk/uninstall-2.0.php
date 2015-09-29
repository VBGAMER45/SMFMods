<?php
/**
 * Global Headers and Footers (global-hf_)
 *
 * @file ./uninstall-2.0.php
 * @author SMFHacks <http://www.smfhacks.com/>
 * @copyright SMFHacks.com Team, 2012
 *
 * @version 2.0.1
 */

// Using SSI?
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<strong>Error:</strong> Cannot uninstall - please make sure that this file in the same directory as SMF\'s SSI.php file.');

// Erm, admins, only.
global $user_info;
if (!$user_info['is_admin'])
	exit('Nice try.');

global $boarddir, $sourcedir, $settings;
$remove_files = array(
	$sourcedir . '/smfhacks_source' => array(
		'global-hf.php'
	),
	$settings['default_theme_dir'] . '/smfhacks_templates' => array(
		'global-hf.template.php'
	),
	$settings['default_theme_dir'] . '/css/smfhacks_css' => array(
		'global-hf.css'
	),
	$settings['default_theme_dir'] . '/images/smfhacks_images' => array(
		'global-hf-table-select-row.png',
		'global-hf-tick-circle.png'
	),
	$settings['default_theme_dir'] . '/languages/smfhacks_languages' => array(
		'global-hf.english.php',
		'global-hf.english-utf8.php'
	),
	$boarddir . '/smfhacks_resources' => array(
		'global-hf-head.txt',
		'global-hf-header.txt',
		'global-hf-footer.txt'
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
$used_settings = array(
	'global_header_bbc',
	'global_footer_bbc'
);
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}settings
	WHERE variable IN({array_string:this_array})',
	array(
		'this_array' => $used_settings,
	)
);

// Show the iframe with the uninstall
echo '
	<iframe src="http://www.smfhacks.com/uninstall.php?modname=R2xvYmFsIEhlYWRlcnMgRm9vdGVycw==" width="100%" height="200px">
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