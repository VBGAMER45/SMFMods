<?php
/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://www.sa-mods.info
 *
 * The Initial Developer of the Original Code is
 * wayne Mankertz.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 * ***** END LICENSE BLOCK ***** */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $db_prefix, $modSettings, $smcFunc;

// First load the SMF 2's Extra DB Functions
db_extend('packages');
db_extend('extra');
pre_installCheck();

    $twitterHooks = array(
	    'integrate_pre_include' => '$sourcedir/Twitter/TwitterHooks.php',
		'integrate_load_theme' => 'twitter_loadTheme',
		'integrate_admin_areas' => 'twitter_admin_areas',
	    'integrate_buffer' => 'ob_twitter',
		'integrate_profile_areas' => 'twitter_profile_areas',
		'integrate_actions' => 'twitter_actions',
		'integrate_login' => 'twit_integrate_login',
		'integrate_create_topic' => 'twit_post_topic',
		'integrate_register' => 'twit_Post_members',
	);
    foreach ($twitterHooks as $hook => $function)
	add_integration_function($hook, $function);

    updateSettings(
        array(
		  'tw_app_enabled' => 0,
		  'tw_admin_mem_groupe' => 0,
		  'tw_app_log_img' => 'http://www.smfhacks.com/images/sign-in-with-twitter-l.png',
		  'tw_app_log_url' => '',
		)
    );

	db_add_col('members','twitname','varchar',255);
    db_add_col('members','twitid','int','10');
    db_add_col('members','twitrn','varchar',255);

	doprofiletwitter();

	db_add_col('boards','tweet_enable','int','10');
	db_add_col('boards','tweet_pubenable','int','10');


function pre_installCheck(){

    if (version_compare(PHP_VERSION, '5.2.0', '<'))
		fatal_error('<b>PHP 5 or geater is required to install SA Twitter.  Please advise your host that PHP4 is no longer maintained and ask that they upgrade you to PHP5.</b><br />');
}

function db_add_col($table, $col, $type, $size) {
	global $smcFunc;

	$smcFunc['db_add_column']('{db_prefix}'.$table,
       array(
	      'name' => $col,
		  'type' => $type,
		  'size' => $size,
		  'default' => 0,
	   )
    );
}
function doprofiletwitter(){
     	global $smcFunc;

	 $smcFunc['db_insert']('ignore',
            '{db_prefix}custom_fields',
        array(
          'col_name' => 'string',
		  'field_name' => 'string',
		  'field_desc' => 'string',
		  'field_type' => 'string',
		  'field_length' => 'int',
		  'field_options' => 'string',
		  'mask'  => 'string',
		  'show_reg'  => 'int',
		  'show_display'  => 'int',
		  'show_profile'  => 'string',
		  'private'  => 'int',
		  'active'  => 'int',
		  'bbc'  => 'int',
		  'can_search'  => 'int',
		  'default_value'  => 'string',
		  'enclose'  => 'string',
		  'placement'  => 'int'
        ),
        array(
          'twit_pro','Twitter username',
		  'Twitter Profile','text',255,'',
		  'nohtml',0,1,'forumProfile',0,1,1,0,'','<a href="https://twitter.com/{INPUT}"target="_blank"><img class="section" src="{DEFAULT_IMAGES_URL}/twitter.png" alt="{INPUT}" width="18" height="18" />',1
        ),
        array()
	);
}

?>