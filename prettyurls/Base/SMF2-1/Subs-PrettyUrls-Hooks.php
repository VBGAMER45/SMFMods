<?php
//	Version: 1.0RC; Subs-PrettyUrls

if (!defined('SMF'))
	die('Hacking attempt...');


function prettyurls_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;

	global $txt;

	loadLanguage('PrettyUrls');
	$admin_areas['config']['areas']['pretty'] = array(
		'label' => (isset($txt['pretty_urls']) ? $txt['pretty_urls'] : 'Pretty URLs'),
		'file' => 'PrettyUrls.php',
		'function' => 'PrettyInterface',
		'icon' => 'prettyurls.png',
		'custom_url' => $scripturl . '?action=admin;area=pretty',
		'subsections' => array(
			'settings' => array($txt['pretty_chrome_menu_settings']),
			'nginx' => array($txt['pretty_chrome_menu_nginx']),
			'maintenance' => array($txt['pretty_chrome_menu_maintenance']),
		),
	);

}

