<?php
/******************************************************************************
* SMF: Simple Machines Forum - Ad Management Mod                              *
*                                                                             *
* =========================================================================== *
* Software Version:           Ad mod: 3.2                                     *
* Software by:                smfhacks.com                                    *
* Copyright 2010-2019 by:     smfhacks.com                                    *
* Support site:               www.smfads.com                                  *
*******************************************************************************
* This mod is free software; you may not redistribute or provide a modified   *
* version to redistribute.  This mod is distributed in the hope that it is    *
* and will be useful, but WITHOUT ANY WARRANTIES; without even any implied    *
* warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            *
******************************************************************************/


if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function ads_actions(&$actionArray)
{
  global $sourcedir, $modSettings;

  $actionArray += array('ads' => array('Ads.php', 'Ads'));

}

// Permissions
function ads_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
   global $context;

   $permissionList['membergroup'] += array(
			'ad_manageperm' => array(false, 'ad_manage'),
    );


}

function ads_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;

	// Load the language files
	if (loadlanguage('Ads') == false)
		loadLanguage('Ads','english');


    ads_array_insert($admin_areas, 'layout',
	        array(
                'ads' => array(
			'title' => $txt['ad_management'],
			'icon' => 'chart_bar.png',
			'permission' => array('admin_forum'),
			'areas' => array(
	            'ads' => array(
					'label' => $txt['ad_management'],
					'file' => 'Ads.php',
					'function' => 'Ads',
					'custom_url' => $scripturl . '?action=admin;area=ads;sa=main',
					'icon' => 'chart_bar.png',
					'subsections' => array(
						'main' => array($txt['ad_management_main']),
						'add' => array($txt['ad_management_add']),
						'reports' => array($txt['ad_management_reports']),
						'settings' => array($txt['ad_management_settings']),
						'copyright' => array($txt['ads_txt_copyrightremoval']),
						'credits' => array($txt['ad_management_credits']),
					),
				),

	        ),
	        ),
	        )
        );




}


function ads_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
{
	$position = array_search($key, array_keys($input), $strict);

	// Key not found -> insert as last
	if ($position === false)
	{
		$input = array_merge($input, $insert);
		return;
	}

	if ($where === 'after')
		$position += 1;

	// Insert as first
	if ($position === 0)
		$input = array_merge($insert, $input);
	else
		$input = array_merge(
			array_slice($input, 0, $position),
			$insert,
			array_slice($input, $position)
		);
}


?>