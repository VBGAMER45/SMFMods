<?php
/*
Telegram Autopost
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
	   'integrate_pre_include' => '$sourcedir/telegramhooks.php',
       'integrate_admin_areas' => 'telegram_admin_areas',
);

// if 2.0
if (!function_exists("set_tld_regex"))
{
    $hook_functions['integrate_create_topic'] = ' telegram_integrate_create_topic';
}


// Adding or removing them?
if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hook_functions as $hook => $function)
	$call($hook, $function);
 
// Set up default settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('telegram_enable_bot_auth_token', '')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('telegram_enable_chat_id', '')");

$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('telegram_enable_push_registration', '0')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('telegram_enable_push_topic', '1')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('telegram_enable_push_post', '0')");


$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('telegram_boardstopush', '')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('telegram_dateformat', 'F j, Y, g:i a')");



$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('telegram_msg_reg', 'A new user - **(username)** - has registered an account on **(date)**')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('telegram_msg_topic', '@(username) created a new thread *(title)* - (url) on *(board)* board')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('telegram_msg_post', '@(username) wrote a new post *(title)* - (url) on *(board)* board')");


?>