<?php
// Install script for SMFBlog 2.0

// If you like how this install script works, please feel free to use any part
// of it in your own mods :)

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

// Fields to add
$db_fields = array(
	array(
		'name' => 'is_blog', 
		'table' => 'boards', 
		'attribs' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0'
	),
	
	array(
		'name' => 'blog_alias', 
		'table' => 'boards', 
		'attribs' => 'VARCHAR(50) NOT NULL'
	),
);

// Settings to add
$mod_settings = array(
	'blog_enable' => 1,
	'blog_enable_rewrite' => 0,
	'blog_posts_perpage' => 10,
	'blog_comments_perpage' => 10,
	'blog_hide_boards' => 0,
);

// Go through all the fields
foreach ($db_fields as $db_field)
{
	// Does it exist?
	$result = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}{$db_field['table']} LIKE '{$db_field['name']}'");
	// Nope...
	if ($smcFunc['db_num_rows']($result) == 0) 
		//... add it!
		$smcFunc['db_query']('', "ALTER TABLE {db_prefix}{$db_field['table']} ADD `{$db_field['name']}` {$db_field['attribs']}");
	$smcFunc['db_free_result']($result);
}	

// Now for the settings...
// Turn the array defined above into a string of MySQL data.
$string = '';
foreach ($mod_settings as $k => $v)
	$string .= '
			("' . $k . '", "' . $v . '"),';

// Sorted out the array defined above - now insert the data!
if ($string != '')
	$smcFunc['db_query']('', "
		INSERT IGNORE INTO {db_prefix}settings
			(variable, value)
		VALUES" . substr($string, 0, -1));

?>
