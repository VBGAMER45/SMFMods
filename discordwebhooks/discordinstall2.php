<?php
/*
Discord Web Hooks
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
	   'integrate_pre_include' => '$sourcedir/discordhooks.php',
       'integrate_admin_areas' => 'discord_admin_areas',
);

// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);
 
// Set up default settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_enable_push_registration', '0')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_enable_push_topic', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_enable_push_post', '0')");

$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_webhook_url', '')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_webhook_topic_url', '')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_webhook_post_url', '')");

$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_boardstopush', '')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_dateformat', 'F j, Y, g:i a')");



$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_msg_reg', 'A new user - **(username)** - has registered an account on **(date)**')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_msg_topic', '@(username) created a new thread *(title)* - (url) on *(board)* board')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_msg_post', '@(username) wrote a new post *(title)* - (url) on *(board)* board')");


$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_botname_reg', '')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_botname_topic', '')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('discord_botname_post', '')");

?>