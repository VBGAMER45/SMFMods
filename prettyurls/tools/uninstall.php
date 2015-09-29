<?php

/*******************************************************************************	ATTENTION: If you are trying to install this manually, you should try
	the package manager. If that does not work, you can run this file by
	accessing it by url. Please ensure it is in the same location as your
	forum's SSI.php file.
*******************************************************************************/

//	Pretty URLs - Uninstall all database changes

//	If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	require_once(dirname(__FILE__) . '/SSI.php');
	$standalone = true;
}
//	Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot uninstall - please verify you put this in the same place as SMF\'s SSI.php.');

//	Start the list
$output = '<ul>';

//	Remove the pretty_topic_urls table
db_query("DROP TABLE IF EXISTS {$db_prefix}pretty_topic_urls", __FILE__, __LINE__);
$output .= '<li>Removing the pretty_topic_urls table</li>';

//	Remove the pretty_urls_cache table
db_query("DROP TABLE IF EXISTS {$db_prefix}pretty_urls_cache", __FILE__, __LINE__);
$output .= '<li>Removing the pretty_urls_cache table</li>';

//	Remove stuff from the settings table
db_query("
	DELETE FROM {$db_prefix}settings
	WHERE variable
	IN ('pretty_board_lookup', 'pretty_board_urls', 'pretty_enable_filters', 'pretty_filters', 'pretty_filter_callbacks', 'pretty_root_url')", __FILE__, __LINE__);
$output .= '<li>Removing some settings</li>';

//	Output the list of database changes
if (isset($standalone))
{
	echo '<title>Uninstalling Pretty URLs</title>
<h1>Uninstalling Pretty URLs</h1>
<h2>Database changes</h2>
', $output;
}

?>
