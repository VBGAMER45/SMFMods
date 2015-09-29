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

if (!defined('SMF'))
	require '../SSI.php';
	global $sourcedir;
    $twitterHooks = array(
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
    foreach ($twitterHooks as $hook => $function)
	remove_integration_function($hook, $function);
?>
