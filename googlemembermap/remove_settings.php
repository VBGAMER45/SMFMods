<?php
/**********************************************************************************
* remove_settings.php                                                             *
***********************************************************************************
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* This file is a simplified database uninstaller. It does what it is suppoed to.  *
**********************************************************************************/

// If we have found SSI.php and we are outside of SMF, then we are running standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF')) // If we are outside SMF and can't find SSI.php, then throw an error
	die('<b>Error:</b> Cannot uninstall - please verify you put this file in the same place as SMF\'s SSI.php.');

if (SMF == 'SSI')
	db_extend('packages');
	
global $modSettings, $smcFunc;

// Only do database changes on uninstall if requested.
if (!empty($_POST['do_db_changes']))
{
	// List all mod settingss here to REMOVE
	$mod_settings_to_remove = array(
		'googleMap_Enable',
		'googleMap_EnableLegend',
		'googleMap_Key',
		'googleMap_PinGender',
		'googleMap_KMLoutput_enable',
		'googleMap_PinNumber',
		'googleMap_Type',
		'googleMap_NavType',
		'googleMap_Sidebar',
		'googleMap_PinBackground',
		'googleMap_PinForeground',
		'googleMap_PinStyle',
		'googleMap_PinShadow',
		'googleMap_PinText',
		'googleMap_PinIcon',
		'googleMap_PinSize',
		'googleMap_DefaultLat',
		'googleMap_DefaultLong',
		'googleMap_DefaultZoom',
		'googleMap_EnableClusterer',
		'googleMap_MinMarkerCluster',
		'googleMap_MaxVisMarker',
		'googleMap_MaxNumClusters',
		'googleMap_MaxLinesCluster',
		'googleMap_ClusterBackground',
		'googleMap_ClusterForeground',
		'googleMap_ClusterSize',
		'googleMap_ClusterStyle',
		'googleMap_ClusterShadow',
		'googleMap_ClusterText',
		'googleMap_ClusterIcon',
		'googleMap_BoldMember',
	);

	// Remove the modsettings from the settings table
	if (count($mod_settings_to_remove) > 0) 
	{
		// Remove the mod_settings if applicable, first the session
		foreach ($mod_settings_to_remove as $setting)
			if (isset($modSettings[$setting]))
				unset($modSettings[$setting]);
		
		// And now the database values
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}settings
			WHERE variable IN ({array_string:settings})',
			array(
				'settings' => $mod_settings_to_remove,
			)
		);
		
		// Make sure the cache is reset as well
		updateSettings(array(
			'settings_updated' => time(),
		));
	}

	if (SMF == 'SSI')
	   echo 'Congratulations! You have successfully removed this mod!';
}