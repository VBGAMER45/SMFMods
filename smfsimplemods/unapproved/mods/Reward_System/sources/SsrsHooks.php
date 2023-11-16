<?php
/*---------------------------------------------------------------------------------
*	SMFSimple Rewards System											 		  *
*	Version 3.0										     						  *
*	Author: 4Kstore																  *
*	Copyright 2012												        		  *
*	Powered by www.smfsimple.com												  *
***********************************************************************************
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

//---Hooks Part....
function ssrs_actions(&$actionArray) //Action!
{
	$actionArray['ssrspergoodpost'] = array('SsrsPoints.php', 'SendPoints');
}

function ssrs_permissions(&$permissionGroups, &$permissionList)
{
	global $context;

	loadLanguage('Ssrs');
	$context['non_guest_permissions'][] = 'ssrs_give_points';
	$permissionList['membergroup']['ssrs_give_points'] = array(false, 'topic', 'participate');
}

function ssrs_Buffer($buffer)
{
	global $modSettings;
	if (!empty($modSettings['ssrs_points_enabled']))
	{
		$buffer = preg_replace(''.base64_decode('figsIFNpbXBsZSBNYWNoaW5lcyBMTEM8L2E+KX4=').'', ', '.base64_decode('U2ltcGxlIE1hY2hpbmVzIExMQzwvYT48YnIgLz48c3BhbiBjbGFzcz0ic21hbGx0ZXh0Ij48YSBocmVmPSJodHRwOi8vd3d3LnNtZnNpbXBsZS5jb20iIHRpdGxlPSJTaXN0ZW1hIGRlIHJlY29tcGVuc2FzIGRlc2Fycm9sbGFkbyBwb3IgU01GU2ltcGxlLmNvbSIgdGFyZ2V0PSJfYmxhbmsiPlNNRlNpbXBsZSBSZXdhcmRzIFN5c3RlbTwvYT48L3NwYW4+').'', $buffer);
		$buffer = preg_replace(''. base64_decode('fihjbGFzcz0ibmV3X3dpbiI+U2ltcGxlIE1hY2hpbmVzPC9hPil+').'', ''.base64_decode('Y2xhc3M9Im5ld193aW4iPlNpbXBsZSBNYWNoaW5lczwvYT48YnIgLz48c3BhbiBjbGFzcz0ic21hbGx0ZXh0Ij48YSBocmVmPSJodHRwOi8vd3d3LnNtZnNpbXBsZS5jb20iIHRpdGxlPSJTaXN0ZW1hIGRlIHJlY29tcGVuc2FzIGRlc2Fycm9sbGFkbyBwb3IgU01GU2ltcGxlLmNvbSIgdGFyZ2V0PSJfYmxhbmsiPlNNRlNpbXBsZSBSZXdhcmRzIFN5c3RlbTwvYT48L3NwYW4+').'', $buffer);
	}
	return $buffer;
}

function ssrs_admin_area(&$admin_areas)
{
	global $txt;

	loadLanguage('Ssrs');
	$admin_areas['SSRewardSystem'] = array(
		'title' => $txt['admin_ssrs_title'],
		'permission' => array('moderate_forum', 'manage_membergroups', 'manage_bans', 'manage_permissions', 'admin_forum'),
		'areas' => array(
			'rewardPoints' => array(
				'label' => $txt['admin_ssrs_reward_points'],
				'file' => 'SsrsPoints.php',
				'function' => 'SsrsPointsSettings',
				'icon' => 'membergroups.gif',
				'permission' => array('manage_membergroups'),
				'subsections' => array(
					'main' => array($txt['admin_ssrs_points_desc']),
					'permissions' => array($txt['admin_ssrs_permissions'], 'manage_permissions'),
					'permissionsboard' => array($txt['admin_ssrs_permissions_board'], 'manage_permissions'),
				),
			),
		),
	);
}

function ssrs_load_theme()
{
	global $context, $settings, $modSettings, $scripturl, $topic;

	loadLanguage('Ssrs');
	$topic_id = !empty($_REQUEST['topic']) ? (int) $_REQUEST['topic'] : '';
	$context['html_headers'] .= '<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url']. '/css/SSRS_style.css" />';

	if (!empty($modSettings['ssrs_points_enabled']) && $topic_id != '')
		$context['html_headers'] .='
		<script type="text/javascript">window.jQuery || document.write(unescape(\'%3Cscript src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"%3E%3C/script%3E\'))</script>
		<script type="text/javascript" src="'. $settings['default_theme_url']. '/scripts/ssrs.js"></script>';
}