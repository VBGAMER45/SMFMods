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

global $db_prefix, $modSettings, $sourcedir, $smcFunc;

// First load the SMF 2's Extra DB Functions
db_extend('packages');
db_extend('extra');
pre_installCheck();

    $facebookHooks = array(
	    'integrate_pre_include' => $sourcedir.'/Facebook/FacebookHooks.php',
		'integrate_load_theme' => 'SAFacebookhooks::facebook_loadTheme',
		'integrate_admin_areas' => 'SAFacebookhooks::facebook_admin_areas',
	    'integrate_buffer' => 'SAFacebookhooks::ob_facebook',
		'integrate_profile_areas' => 'SAFacebookhooks::facebook_profile_areas',
		'integrate_actions' => 'SAFacebookhooks::facebook_actions',
		'integrate_menu_buttons' => 'SAFacebookhooks::facebook_menu_buttons',
		'integrate_create_topic' => 'SAFacebookhooks::Facebook_Post_topic',
		'integrate_register' => 'SAFacebookhooks::Facebook_Post_members',
		'integrate_pre_load' => 'SAFacebookhooks::Facebook_register_override',
		'integrate_login' => 'SAFacebookhooks::Facebook_integrate_login',
		'integrate_bbc_codes' => 'SAFacebookhooks::Facebook_bbc_add_code',
	);
    foreach ($facebookHooks as $hook => $function)
	add_integration_function($hook, $function);

    updateSettings(
        array(
		  'fb_app_enabled' => 1,
		  'fb_admin_mem_groupe' => 0,
		)
    );	
	
    db_add_col('members','fbname','varchar',255);
	db_add_col('members','fbid','varchar',255);
    db_add_col('members','fbpw','varchar',255);
	
	doprofilefacebook();
	
	db_add_col('boards','like_enable','int','10');
	db_add_col('boards','com_enable','int','10');
	db_add_col('boards','pub_enable','int','10');
	
	$smcFunc['db_add_column']('{db_prefix}topics', 
		array(
	        'name' => 'fb_post_id',
	        'type' => 'varchar',
	        'size' => 255,
	        'default' => 0,
		)
	);
	
	$smcFunc['db_insert']('ignore', '{db_prefix}settings',
		array('variable' => 'string','value' => 'string',),
	    array(
		//like
		array ('fb_app_liketopic' ,'0'),
		array ('liketopiclayout' ,'1'),
		array ('liketopicverb' ,'1'),
		array ('liketopiccolour' ,'1'),
		array ('likeshowfaces' ,'1'),
		array ('likeshowsend' ,'1'),
		//comments
		array ('fb_app_enablecom' ,'0'),
		array ('fb_admin_commets_post' ,'10'),
		array ('fb_admin_commets_post_board' ,'10'),
		array ('comcolour' ,'1'),
		//like box
		array ('fb_app_enablelbox' ,'0'),
		array ('likesbhowface' ,'1'),
		array ('likesbhowhead' ,'1'),
		array ('lboxcolour' ,'1'),
		array ('lboxshowstream' ,'1'),
		//main settings
		array ('fb_log_logo' ,'http://b.static.ak.fbcdn.net/rsrc.php/zB6N8/hash/4li2k73z.gif'),
		array ('fb_admin_invite_text' ,'checkout this site. visit {burl}'),
		array ('fb_app_enabled' ,'0'),
		array ('fb_app_enableduinfo' ,'1'),
		array ('fb_app_enableguest' ,'1'),
		array ('fb_dfbreg2' ,'0'),
		array ('fb_admin_intern1' ,'en_US'),
		array ('fb_admin_mem_groupe' ,'0'),
		array ('fb_app_id' ,'123456789'),
		array ('fb_app_key' ,'123456789'),
		array ('fb_app_secret' ,'123456789'),
		array ('fb_admin_grant' ,'0'),
		array ('fb_admin_postmem' ,'0'),
		array ('fb_admin_uid' ,''),
		array ('fb_atoken' ,''),
		array ('fb_app_unsync' ,'0'),
		array ('fb_app_enablecp' ,'0'),
		array ('fb_mode1' ,'1'),
		array ('fb_mode2' ,'1'),
		array ('fb_mode3' ,'1'),
		array ('fb_mode4' ,'1'),
		array ('fb_postto' ,'0'),
		array ('fb_reg_auto','0'),
		
		),
		array());
	
	
function pre_installCheck(){

    if (version_compare(PHP_VERSION, '5.0.0', '<'))
		fatal_error('<b>PHP 5 or geater is required to install SA Facebook.  Please advise your host that PHP4 is no longer maintained and ask that they upgrade you to PHP5.</b><br />');
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
function doprofilefacebook(){
     	global $smcFunc;
		
	/*$smcFunc['db_insert']('ignore',
            '{db_prefix}scheduled_tasks',
        array(
          'next_time' => 'int',
		'time_offset' => 'int',
		'time_regularity' => 'int',
		'time_unit' => 'string',
		'disabled' => 'int',
		'task' => 'string',
	),
	array(
		strtotime('now'),0,1,'h',0,'fbtoken',
	),
	array('task')
	);*/
	
	 $smcFunc['db_insert']('ignore',
            '{db_prefix}custom_fields',
        array(
          'col_name' => 'string','field_name' => 'string', 
		  'field_desc' => 'string','field_type' => 'string', 
		  'field_length' => 'int','field_options' => 'string', 
		  'mask'  => 'string','show_reg'  => 'int', 
		  'show_display'  => 'int','show_profile'  => 'string', 
		  'private'  => 'int','active'  => 'int', 
		  'bbc'  => 'int','can_search'  => 'int', 
		  'default_value'  => 'string','enclose'  => 'string', 
		  'placement'  => 'int'
        ),
        array(
          'face_pro','Facebook Profile',
		  'Facebook Profile','text',255,'',
		  'nohtml',0,0,'forumProfile',0,1,1,0,'','',0
        ),
        array()
	);
}

?>