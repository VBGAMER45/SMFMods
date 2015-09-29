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

if (!defined('SMF'))
	require '../SSI.php';
	
    $twitterHooks = array(
	  'integrate_pre_include' => '$boarddir/Sources/Twitter/TwitterHooks.php',
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
	remove_integration_function($hook, $function);
?>
