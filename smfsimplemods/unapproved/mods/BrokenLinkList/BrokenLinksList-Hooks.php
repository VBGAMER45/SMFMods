<?php
/*---------------------------------------------------------------------------------
*	Broken Links List															  *
*	Version 1.1																	  *
*	Author: 4kstore																  *
*	Powered by www.smfsimple.com												  *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

//---Hooks Part....
function brokenlinklist_actions(&$actionArray) //Action!
{
	$actionArray['brokenlinkslist'] = array('BrokenLinksList.php', 'BrokenLinksList');
	$actionArray['brokenlinkslist2'] = array('BrokenLinksList.php', 'BrokenLinksList2');	
}

function brokenlinklist_permissions(&$permissionGroups, &$permissionList)
{
	global $context;
	loadLanguage('BrokenLinkList');
	$context['non_guest_permissions'][] = 'brokenlinklist';
	$permissionList['membergroup']['brokenlinklist'] = array(false, 'post', 'participate');
}

function brokenlinklist_menu_button(&$buttons) 
{
	global $scripturl, $txt, $context, $modSettings;	
	loadLanguage('BrokenLinkList');
	if(!empty($modSettings['bll_enabled']) && allowedTo('brokenlinklist'))
	{
		if (!$context['user']['is_logged'])
			$boton = 'login';
		else
			$boton = 'logout';
		$find_me = 0;
		reset($buttons);

		foreach($buttons as $key => $value)
		{
			if ($key != $boton)
				$find_me++;
			else
				break;
		}


		$buttons = array_merge(
			array_slice($buttons, 0, $find_me),
			array(
				'broken_link_list' => array(
					'title' => $txt['bll_admin_menu_button'],
					'href' => $scripturl . '?action=brokenlinkslist2',
					'show' => !empty($modSettings['bll_enabled']) ? $modSettings['bll_enabled'] : '',
					'sub_buttons' => array(
					)
				),
			),
			array_slice($buttons, $find_me)
		);
	}
}
function brokenlinklist_admin_area(&$admin_areas)
{
	global $txt;
	loadLanguage('BrokenLinkList');
	$admin_areas['config']['areas']['brokenlinkslist'] = array(
		'label' => $txt['bll_admin_menu_button'],
		'file' => 'BrokenLinksList.php',
		'function' => 'BrokenLinksListAdmin',
		'icon' => 'post_moderation_moderate.gif',
		'subsections' => array(
			'main' => array($txt['bll_admin_settings']),
		),
	);
}