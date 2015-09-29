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
	
    $GplusHooks = array(
	    'integrate_pre_include' => '$sourcedir/GPlus/GPlusHooks.php',
	    'integrate_buffer' => 'ob_gplus',
		'integrate_actions' => 'gplus_actions',
		'integrate_profile_areas' => 'gplus_profile_areas',
		'integrate_admin_areas' => 'gplus_admin_areas',
		'integrate_login' => 'gplus_integrate_login',
		'integrate_logout' => 'gplus_integrate_logout',
		'integrate_load_theme' => 'gplus_loadTheme',
	);
    foreach ($GplusHooks as $hook => $function)
	remove_integration_function($hook, $function);
?>
