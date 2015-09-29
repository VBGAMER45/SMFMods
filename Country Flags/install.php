<?php
/**********************************************************************************
* install.php                                                                     *
***********************************************************************************
* Modification by:            Jason Clemons (http://gamingbrotherhood.com)        *
* Copyright 2009 by:          Socialite Development (http://socialiteproject.com) *
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
**********************************************************************************/

/*	This file is a simplified database installer. It does what it is suppoed to. */

// List settings here in the format: setting_key => default_value.  Escape any "s. (" => \")
$mod_settings = array();
$mod_settings = array(
	'country_flag_ask' => 0,
	'country_flag_required' => 0,
	'country_flag_show' => 1,
);

$columns = array();
$columns[] = array(
	'table_name' => '{db_prefix}members',
	'column_info' => array(
		'name' => 'country_flag',
		'type' => 'varchar',
		'size' => 10,
		'null' => false,
		'default' => '',
	),
	'error' => 'fatal',
	'if_exists' => 'update',
	'parameters' => array(),
);

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif(!defined('SMF'))
{
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as SMF\'s SSI.php.');
}

	db_extend('packages');
	
	updateSettings($mod_settings); 
			
	foreach ($columns as $column)
		$smcFunc['db_add_column']($column['table_name'], $column['column_info'], $column['parameters'], $column['if_exists'], $column['error']);

if (SMF == 'SSI')
	echo 'Congratulations! You have successfully installed Country Flags';

?>