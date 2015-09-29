<?php

/*******************************************************************************
	ATTENTION: If you are trying to install this manually, you should try
	the package manager. If that does not work, you can run this file by
	accessing it by url. Please ensure it is in the same location as your
	forum's SSI.php file.
*******************************************************************************/

//	Pretty URLs - Base v1.0

//	If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	require_once(dirname(__FILE__) . '/SSI.php');
	db_extend('Packages');
	$standalone = true;
}
//	Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s SSI.php.');

//	Create the pretty_topic_urls table
$smcFunc['db_create_table']('{db_prefix}pretty_topic_urls', array(
	array('name' => 'id_topic', 'type' => 'mediumint'),
	array('name' => 'pretty_url', 'type' => 'varchar', 'size' => 80),
), array(
	array('type' => 'primary', 'columns' => array('id_topic')),
	array('type' => 'unique', 'columns' => array('pretty_url')),
), array(), 'ignore');

//	Create the pretty_urls_cache table
$smcFunc['db_drop_table']('{db_grefix}pretty_urls_cache');
$smcFunc['db_create_table']('{db_prefix}pretty_urls_cache', array(
	array('name' => 'url_id', 'type' => 'varchar', 'size' => 255),
	array('name' => 'replacement', 'type' => 'varchar', 'size' => 255),
), array(
	array('type' => 'primary', 'columns' => array('url_id')),
), array(), 'overwrite');

//	Default filter settings
$prettyFilters = array(
	'boards' => array(
		'description' => 'Rewrite Board URLs',
		'enabled' => 1,
		'filter' => array(
			'priority' => 45,
			'callback' => 'pretty_urls_board_filter',
		),
		'rewrite' => array(
			'priority' => 50,
			'rule' => array(
				'RewriteRule ^ROOTURL([-_!~*\'()$a-zA-Z0-9]+)/?$ ./index.php?pretty;board=$1.0 [L,QSA]',
				'RewriteRule ^ROOTURL([-_!~*\'()$a-zA-Z0-9]+)/([0-9]*)/?$ ./index.php?pretty;board=$1.$2 [L,QSA]',
			),
		),
		'test_callback' => 'pretty_boards_test',
		'title' => 'Boards',
	),
	'topics' => array(
		'description' => 'Rewrite Topic URLs',
		'enabled' => 1,
		'filter' => array(
			'priority' => 40,
			'callback' => 'pretty_urls_topic_filter',
		),
		'rewrite' => array(
			'priority' => 55,
			'rule' => array(
				'RewriteRule ^ROOTURL([-_!~*\'()$a-zA-Z0-9]+)/([-_!~*\'()$a-zA-Z0-9]+)/?$ ./index.php?pretty;board=$1;topic=$2.0 [L,QSA]',
				'RewriteRule ^ROOTURL([-_!~*\'()$a-zA-Z0-9]+)/([-_!~*\'()$a-zA-Z0-9]+)/([0-9]*|msg[0-9]*|new)/?$ ./index.php?pretty;board=$1;topic=$2.$3 [L,QSA]',
			),
		),
		'test_callback' => 'pretty_topics_test',
		'title' => 'Topics',
	),
	'actions' => array(
		'description' => 'Rewrite Action URLs (ie, index.php?action=something)',
		'enabled' => 1,
		'filter' => array(
			'priority' => 55,
			'callback' => 'pretty_urls_actions_filter',
		),
		'rewrite' => array(
			'priority' => 45,
			'rule' => '#ACTIONS',	//	To be replaced in pretty_update_filters()
		),
		'test_callback' => 'pretty_actions_test',
		'title' => 'Actions',
	),
	'profiles' => array(
		'description' => 'Rewrite Profile URLs. As this uses the Username of an account rather than it\'s Display Name, it may not be desirable to your users.',
		'enabled' => 0,
		'filter' => array(
			'priority' => 50,
			'callback' => 'pretty_profiles_filter',
		),
		'rewrite' => array(
			'priority' => 40,
			'rule' => 'RewriteRule ^profile/([^/]+)/?$ ./index.php?pretty;action=profile;user=$1 [L,QSA]',
		),
		'test_callback' => 'pretty_profiles_test',
		'title' => 'Profiles',
	),
);

//	Add the pretty_root_url setting. pretty_enable_filters can't be set cause updateSettings will ignore it
$pretty_root_url = isset($modSettings['pretty_root_url']) ? $modSettings['pretty_root_url'] : $boardurl;

//	Update the settings table
updateSettings(array(
	'pretty_filters' => serialize($prettyFilters),
	'pretty_root_url' => $pretty_root_url,
	'queryless_urls' => 0,
	'pretty_bufferusecache' => 0,
));

//	Run maintenance
require_once($sourcedir . '/Subs-PrettyUrls.php');
pretty_run_maintenance(true);

//	Output a success message
//	Load the PrettyUrls template and language files
loadTemplate('PrettyUrls');
if (loadLanguage('PrettyUrls') == false)
	loadLanguage('PrettyUrls', 'english');

//	Shiny chrome interface
$context['page_title'] = $txt['pretty_chrome_install_title'];
$context['template_layers'][] = 'pretty_chrome';
$context['sub_template'] = 'pretty_install';
$context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/pretty/chrome.css" media="screen,projection" />';
$context['pretty']['chrome'] = array(
	'title' => $txt['pretty_chrome_install_title'],
);

// Add a basic stand alone html frame if its needed
if (isset($standalone))
{
	function template_standalone_above()
	{
		global $context, $txt;

		echo '<!doctype html>
<html><head>
	<meta charset="', $context['character_set'], '">
	<title>', $context['page_title'] , '</title>', $context['html_headers'], '
</head><body>';
	}

	function template_standalone_below()
	{
		echo '
</body></html>';
	}

	$context['template_layers'] = array('standalone', 'pretty_chrome');
	obExit();
}

?>
