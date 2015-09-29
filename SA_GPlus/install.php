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
	die('<strong>Error:</strong> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $db_prefix, $modSettings, $smcFunc;

// First load the SMF 2's Extra DB Functions
db_extend('packages');
db_extend('extra');
pre_installCheck();

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
	add_integration_function($hook, $function);
	
	db_add_col('members','gpname','varchar',255);
    db_add_col('members','gpid','varchar',255);
	
	
function pre_installCheck(){

    if (version_compare(PHP_VERSION, '5.2.0', '<'))
		fatal_error('<strong>PHP 5 or geater is required to install SA Google+.  Please advise your host that PHP4 is no longer maintained and ask that they upgrade you to PHP5.</strong><br />');
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

?>