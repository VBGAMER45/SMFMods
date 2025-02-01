<?php
/*
EzPortal
Version 3.0
by:vbgamer45
http://www.ezportal.com
Copyright 2010-2018 http://www.samsonsoftware.com
*/
if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function ezphook_actions(&$actionArray)
{
  global $sourcedir, $modSettings;

  $actionArray += array('ezportal' => array('EzPortal2.php', 'EzPortalMain'));
  $actionArray += array('forum' => array('BoardIndex.php', 'BoardIndex'));

}

// Permissions
function ezphook_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
   global $context;

   $permissionList['membergroup'] += array(
        'ezportal_page' => array(false, 'ezportal', 'ezportal'),
		'ezportal_blocks' => array(false, 'ezportal', 'ezportal'),
		'ezportal_manage' => array(false, 'ezportal', 'ezportal'),
    );


}

function ezphook_integrate_default_action()
{
	global $ezpSettings, $sourcedir, $board, $topic, $context;
	

		if ($ezpSettings['ezp_portal_enable'] == 1 && empty($board) && empty($topic))
		{

			if ($context['ezportal_loaded'] == false)
			{
				require_once($sourcedir . '/BoardIndex.php');
				BoardIndex();

			}
			else
			{

				if (!function_exists('template_main'))
				{
					function template_main()
					{

					}

				}

				require_once($sourcedir . '/EzPortal2.php');

				EzPortalForumHomePage();
			}
		}
		else
		{

			require_once($sourcedir . '/BoardIndex.php');

				BoardIndex();
				if (!function_exists('template_main'))
							{
								function template_main()
								{

								}

							}



		}


}

function ezphook_integrate_pre_log_stats(&$no_stat_actions)
{
	global $sourcedir, $context;
	// Setup EzPortal
	require_once($sourcedir . '/EzPortal2.php');
	$ezPortalLoaded = SetupEzPortal();

	if ($ezPortalLoaded === false)
		$context['ezportal_loaded'] = false;
	else
		$context['ezportal_loaded'] = true;

}

function ezphook_integrate_mark_read_button()
{
	global $context;

		if (isset($_REQUEST['action']))
		{
				if ($_REQUEST['action'] == 'forum')
				{
					$context['robot_no_index'] = false;
				}
		}

}

function ezphook_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;


    // Load Language
    if (loadlanguage('EzPortal') == false)
        loadLanguage('EzPortal','english');

    ezphook_array_insert($admin_areas, 'layout',
	        array(
                'ezportal' => array(
			'title' => $txt['ezportal_admin'],
			'icon' => 'ezportal.png',
			'permission' => array('ezportal_page', 'ezportal_blocks', 'ezportal_manage'),
			'areas' => array(
				'ezpsettings' => array(
					'label' => $txt['ezportal_settings'],
					'file' => 'EzPortal2.php',
					'function' => 'EzPortalMain',
					'custom_url' => $scripturl . '?action=admin;area=ezpsettings;sa=settings',
					'icon' => 'features',
					'permission' => array('ezportal_manage'),
					'subsections' => array(
						'settings' => array($txt['ezp_settings']),
						//'modules' => array($txt['ezp_modules']),
						'import' => array($txt['ezp_import']),
						'copyright' => array($txt['ezp_txt_copyrightremoval']),
						'credits' => array($txt['ezp_credits']),
					),
				),
				'ezpblocks' => array(
					'label' => $txt['ezportal_block_manager'],
					'file' => 'EzPortal2.php',
					'function' => 'EzPortalMain',
					'custom_url' => $scripturl . '?action=admin;area=ezpblocks;sa=blocks',
					'icon' => 'boards',
					'permission' => array('ezportal_blocks', 'ezportal_manage'),
					'subsections' => array(
						'blocks' => array($txt['ezp_blocks']),
						'downloadblock' => array($txt['ezp_download_blocks']),
						'installedblocks' => array($txt['ezp_installed_blocks']),
					),
				),
				'ezppagemanager' => array(
					'label' => $txt['ezportal_page_manager'],
					'file' => 'EzPortal2.php',
					'function' => 'EzPortalMain',
					'custom_url' => $scripturl . '?action=admin;area=ezppagemanager;sa=pagemanager',
					'icon' => 'reports',
					'permission' => array('ezportal_page', 'ezportal_manage'),
					'subsections' => array(
						'pagemanager' => array($txt['ezp_pagemanager']),

					),
				),
			),
		),

	        )
        );




}

function ezphook_menu_buttons(&$menu_buttons)
{
	global $txt, $user_info, $sourcedir, $boardurl, $context, $modSettings, $scripturl, $ezpSettings, $smcFunc;

	require_once($sourcedir . '/Subs-EzPortalMain2.php');

	// Load EzPortal Settings
	LoadEzPortalSettings();

	$showezPortalAdmin = allowedTo(array('ezportal_manage','ezportal_blocks','ezportal_page'));
	if ($showezPortalAdmin == 1)
	{
		$menu_buttons['admin']['show'] = 1;
	}

	if (!isset($txt['ezp_forum_tab']))
	{
		// Load Language
		if (loadlanguage('EzPortal') == false)
			loadLanguage('EzPortal','english');
	}
	
	if (!isset($txt['ezp_forum_tab']))
	{
		$txt['ezp_forum_tab'] = 'Forum';
		$txt['ezportal_admin'] = 'EzPortal';
		$txt['ezportal_settings'] = 'Settings';
		$txt['ezportal_block_manager'] = 'ezBlock Manager';
		$txt['ezportal_page_manager'] = 'Page Manager';
	}


	#You can use these settings to move the button around or even disable the button and use a sub button
	#Main menu button options

	#Where the button will be shown on the menu
	$button_insert = 'search';

	#before or after the above
	$button_pos = 'before';
	#default is before the memberlist
 	if (isset($modSettings['ezportal_smfversion']) && $modSettings['ezportal_smfversion'] == "2.1")
    ezphook_array_insert($menu_buttons, $button_insert,
		     array(
                    'forum' => array(
				'title' => $txt['ezp_forum_tab'],
				'href' => $scripturl . '?action=forum',
				'show' => !empty($ezpSettings['ezp_portal_enable']),
				'icon' => 'forum.png',


			    )
		    )
	    ,$button_pos);

   // Insert Menu buttons for pages

   	if (isset($modSettings['ezportal_menucount']) && $modSettings['ezportal_menucount'] > 0)
   	{
	   	$button_insert = 'search';

		#before or after the above
		$button_pos = 'after';

		$dbresult = $smcFunc['db_query']('', "
		SELECT
			id_page, menutitle, permissions, title, icon 
		FROM {db_prefix}ezp_page
		WHERE showinmenu = 1");
		while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		{

			// Check Page Permissions
			$permissionsGroups = explode(',',$row['permissions']);
			$has_Permission = count(array_intersect($user_info['groups'], $permissionsGroups)) == 0 ? false : true;
			
			if (empty($row['icon']))
				$row['icon'] = 'page.png';
			
			$pageurl = $scripturl . '?action=ezportal;sa=page;p='  . $row['id_page'];
			
			if (!empty($ezpSettings['ezp_pages_seourls']))
				$pageurl = $boardurl . '/pages/' . MakeSEOUrl($row['title']) . '-' . $row['id_page'];
			
			

			ezphook_array_insert($menu_buttons, $button_insert,
			     array(
	                'page' . $row['id_page']  => array(
					'title' => $row['menutitle'],
					'href' => $pageurl,
					'show' => $has_Permission,
					'icon' => $row['icon'],


				    )
			    )
		    ,$button_pos);



		}
		$smcFunc['db_free_result']($dbresult);
	}



}

function ezphook_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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