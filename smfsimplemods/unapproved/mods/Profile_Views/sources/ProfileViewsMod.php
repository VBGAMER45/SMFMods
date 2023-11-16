<?php
/*---------------------------------------------------------------------------------
*	Profile Views 2.0															  *
*	Author: SSimple Team														  *
*	Copyright 2013														          *
*	Powered by www.smfsimple.com												  *
***********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');
	
function pvm_settings(&$config_vars)
{
	global $txt;
	loadLanguage('ProfileViewsMod');
	$config_vars[] = $txt['pvm_admin_desc'];
	$config_vars[] = array('check', 'pvm_enabled');
	$config_vars[] = array('int', 'pvm_height');
	$config_vars[] = array('check', 'pvm_show_avatar');
	$config_vars[] = array('check', 'pvm_show_visits');
	$config_vars[] = array('check', 'pvm_show_latest_visits');
}	
	
function pvm_load_theme()
{
	global $context, $settings, $smcFunc;
	
	$action = !empty($context['current_action']) ? (string) $context['current_action'] : '';
	$area = !empty($_REQUEST['area']) ? (string)$_REQUEST['area'] : '';

	if (($area == "summary" || empty($area)) && $action == "profile" )
	{
		loadLanguage('ProfileViewsMod');
		$context['html_headers'] .= '
		<link rel="stylesheet" type="text/css" href="'.$settings['default_theme_url'].'/css/ProfileViewsMod.css" />';	
	}
	
}	

function load_profile_visit_log($memID)
{
	global $user_info, $smcFunc, $context, $scripturl, $modSettings, $settings, $txt;
	
	if (!empty($memID))
	{
		$id_member_visit = !empty($user_info['id']) ? (int) $user_info['id'] : 0;
		
		if ($memID != $user_info['id'])
		{
			$smcFunc['db_insert']('insert',
				'{db_prefix}log_profile_views',
				array(
					'id_member_profile' => 'int', 'id_member_visit' => 'int', 'date' => 'int',
				),
				array(
					$memID, $id_member_visit, time(),
				),
				array()
			);		
		}
		
		$select_query = '';
		$joins_query = '';
		$order_query = '';
		$context['pv_mod'] = array();
		
		if (!empty($modSettings['pvm_show_avatar']))
		{
			$select_query .= ',mem.avatar ,IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type';
			$joins_query .= 'LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = pv.id_member_visit) ';
		}		
		
		if (!empty($modSettings['pvm_show_visits']))		
			$select_query .= ', COUNT(*) as total_visits';
		
		if (!empty($modSettings['pvm_show_latest_visits']))
		{
			$select_query .= ', MAX(pv.date) as last_visit';
			$order_query .= 'ORDER BY last_visit DESC';
		}
		
		$request = $smcFunc['db_query']('', '
			SELECT pv.id_member_profile, pv.id_member_visit, mem.real_name, mg.online_color
			'.$select_query.'
			FROM {db_prefix}log_profile_views as pv			
			LEFT JOIN {db_prefix}members AS mem ON mem.id_member = pv.id_member_visit			
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN mem.id_group = {int:reg_mem_group} THEN mem.id_post_group ELSE mem.id_group END)
			'.$joins_query.'
			WHERE pv.id_member_profile = {int:id_member}
			GROUP BY pv.id_member_visit 
			'.$order_query.'',
			array(
			  'id_member' => $memID,
			  'reg_mem_group' => 0,
			)
		);
		
		while($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['pv_mod'][] = array(
				'name' => !empty($row['real_name']) ? $row['real_name'] : $txt['pvm_guest'],
				'id_member_visit' => !empty($row['id_member_visit']) ? $row['id_member_visit'] : '0',
				'avatar' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? '<img width="50px" height="50px" src="' . (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" border="0" />' : '<img width="50px" height="50px" src="' . $settings['images_url'] . '/noavatar.png" alt="" />') : (stristr($row['avatar'], 'http://') ? '<img width="50px" height="50px" src="' . $row['avatar'] . '" alt="" border="0" />' : '<img width="50px" height="50px" src="' . $modSettings['avatar_url'] . '/' . $smcFunc['htmlspecialchars']($row['avatar']) . '" alt="" border="0" />'),
				'total_visits' => !empty($row['total_visits']) ? $row['total_visits'] : '',
				'last_visit' => !empty($row['last_visit']) ? timeformat($row['last_visit']) : '',
				'mg_color' => !empty($row['online_color']) ? $row['online_color'] : ''
			);
		}
		
		$smcFunc['db_free_result']($request);
	}
}