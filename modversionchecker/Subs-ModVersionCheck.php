<?php
/*
 * Mod Version Checker
 * By: vbgamer45
 * https://www.smfhacks.com
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function CheckModsForUpdate()
{
	global $smcFunc, $sourcedir, $forum_version;

	require_once($sourcedir . '/Subs-Package.php');

	$installedPackages = array();

	$dbresult = $smcFunc['db_query']('', "
	select distinct package_id,version
	FROM {db_prefix}log_packages
	WHERE time_removed = 0 AND install_state != 0");
		
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		if (empty($installedPackages[$row['package_id']]))
			$installedPackages[$row['package_id']] = $row['version'];
		else
		{
			if (version_compare($row['version'], $installedPackages[$row['package_id']],'>'))
				$installedPackages[$row['package_id']] = $row['version'];
		}

	}
	$smcFunc['db_free_result']($dbresult);

	// Build our query string to the api
	$modsToCheck = '';
	if (!empty($installedPackages))
	{
		foreach($installedPackages as $package => $version)
		{
			if (!empty($modsToCheck))
				$modsToCheck .= '|';

			$modsToCheck .= $package . ',' . $version;

		}
	}

	// Check if we are emulating a version just in case
	 if (defined("SMF_VERSION"))
		$the_version = SMF_VERSION;
	 else
		$the_version = $forum_version;

	if (!empty($_SESSION['version_emulate']))
		$the_version = $_SESSION['version_emulate'];

	$data = fetch_web_data("https://api.smfhacks.com/smfcheckversion.php?smfversion=" . urlencode($the_version),"mods=" . $modsToCheck);

	updateSettings(array('modcheckupdates' => $data));


}

function LoadModCheckDisplay()
{
	global $smcFunc, $modSettings, $context;

	// Does final checks to see if any mods/updates recently installed versus the data we have saved from scheduled task.

	$context['modvc_modupates'] = array();

	if (empty($modSettings['modcheckupdates']))
		return false;

	$data = unserialize($modSettings['modcheckupdates']);

	// check if there is not an empty array
	if (empty($data))
		return false;

	// grab a list of packages one query to make it faster
	$installedPackages = array();

	$dbresult = $smcFunc['db_query']('', "
	select distinct package_id,version,name
	FROM {db_prefix}log_packages
	WHERE time_removed = 0 AND install_state != 0");

	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		if (empty($installedPackages[$row['package_id']]))
			$installedPackages[$row['package_id']] = $row;
		else
		{
			if (version_compare($row['version'], $installedPackages[$row['package_id']]['version'],'>'))
				$installedPackages[$row['package_id']] = $row;
		}

	}
	$smcFunc['db_free_result']($dbresult);

	// Check against the serialized update array
	foreach($data as $mod)
	{

		foreach($installedPackages as $key => $pack)
		{
			// we found a match
			if ($key == $mod['packageid'])
			{
				if ($pack['version'] != $mod['version'])
				{
					$mod['oldversion'] = $pack['version'];
					$mod['name'] = $pack['name'];
					$context['modvc_modupates'][] = $mod;
				}
				break;
			}
		}

	}

}


?>