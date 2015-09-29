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
	die('Hacking attempt...');
	
function KB_array_insert(&$input, $key, $insert, $where = 'before', $strict = false){
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

function KB_file_include($file)
{
	global $sourcedir;
    
	$kb_dir = $sourcedir.'/KB';	
	
	if(is_array($file))
	{
		foreach($file as $n => $filename)
		{
   			$path = $kb_dir . '/' . $filename . '.php';	
			if(file_exists($path)) 
				include($path);
			else
			    redirectexit();
		}
	}
	else
	{
		$path = $kb_dir . '/' . $file . '.php';
		if(file_exists($path)) 
		    include($path);
		else
			redirectexit();	
	}
}	

function KB_profile_areas(&$profile_areas){
    global $txt, $user_info, $modSettings;

    if(!empty($modSettings['kb_enabled']) && allowedTo('view_knowledge')){
	    KB_array_insert($profile_areas, 'profile_action',
		    array(
			   'profile_kb' => array(
			   'title' => $txt['knowledgebase'],
			        'areas' => array(
				        'kb' => array(
					        'label' => $txt['kb_profile_maintab'],
					        'file' => 'KB/KBProfile.php',
					        'function' => 'KB_profile_main',
							    'subsections' => array(
								    'main' => array($txt['kb_profile_maintab'], array('profile_view_own', 'profile_view_any')),
						            'articles' => array($txt['kb_proart'], array('profile_view_own', 'profile_view_any')),
						            'unapproved' => array($txt['kb_no_approveprofile'],'profile_view_own'),
					            ),
					        'password' => false,
					        'permission' => array(
						        'own' => 'profile_view_own',
						        'any' => 'profile_view_any',
					        ),
				        ),	
			        ),
		        ),
		    )
	    );
		
		if(isset($_GET['u'])){
		    $context['user']['is_owner'] = $_GET['u'] == $user_info['id'];
		    if(!$context['user']['is_owner'] && !allowedTo('admin_forum')){
		        unset($profile_areas['profile_kb']['areas']['kb']['subsections']['unapproved']);
		    }
		}
	}
}

function KB_menu_buttons(&$menu_buttons){
	global $txt, $user_info, $context, $modSettings, $scripturl;

	#You can use these settings to move the button around or even disable the button and use a sub button
	#Main menu button options
	
	#Where the button will be shown on the menu
	$kb_button_insert = 'mlist';
	
	#before or after the above
	$kb_button_pos = 'before';
	#default is before the meberlist
	
	#Sub button options
	
	#Insert as sub button of help example $kb_button_insert_sub = 'help'; 
	$kb_button_insert_sub = ''; 
	#Leave blank to disable

	if(!empty($modSettings['kb_enabled']) && $kb_button_insert_sub != '' && allowedTo('view_knowledge')){
	    
		$counter = 0;
		
		if ($context['current_action'] == 'kb')
		    $context['current_action'] = $kb_button_insert_sub;
			
		foreach ($menu_buttons[$kb_button_insert_sub]['sub_buttons'] as $area => $dummy)
			if (++$counter)
				break;		
        $menu_buttons[$kb_button_insert_sub]['sub_buttons'] = array_merge(
		    array_slice($menu_buttons[$kb_button_insert_sub]['sub_buttons'], 0, $counter, TRUE),
		        array('kb' => array(
					'title' => $txt['knowledgebase'],
					'href' => $scripturl . '?action=kb',
					'show' => allowedTo('view_knowledge') && !empty($modSettings['kb_enabled']),
			    ),  
			),
		    array_slice($menu_buttons[$kb_button_insert_sub]['sub_buttons'], $counter, NULL, TRUE)
        );
	}
	if(!empty($modSettings['kb_enabled']) && $kb_button_insert_sub == '' && $kb_button_insert && allowedTo('view_knowledge')){
	    KB_array_insert($menu_buttons, $kb_button_insert,
		     array(
			    'kb' => array(
				    'title' => $txt['knowledgebase'],
				    'href' => $scripturl . '?action=kb',
				    'show' => true,
				    'sub_buttons' => array(
				     ),
				    'active_button' => false,
			    ),	
		    )
	    ,$kb_button_pos);
	}
	
	//profile tab
	$counter = 0;
		foreach ($menu_buttons['profile']['sub_buttons'] as $area => $dummy)
			if (++$counter && $area == 'account')
				break;
				
    $menu_buttons['profile']['sub_buttons'] = array_merge(
		array_slice($menu_buttons['profile']['sub_buttons'], 0, $counter, TRUE),
		    array('kb' => array(
						'title' => $txt['knowledgebase'],
						   'href' => $scripturl . '?action=profile;area=kb;u='.$user_info['id'].'',
						'show' => allowedTo('view_knowledge') && !empty($modSettings['kb_enabled']),
			    ),  
			),
		    array_slice($menu_buttons['profile']['sub_buttons'], $counter, NULL, TRUE)
    );
	
	//admin tab
    $counter = 0;
		foreach ($menu_buttons['admin']['sub_buttons'] as $area => $dummy)
			if (++$counter && $area == 'featuresettings')
				break;
				
    $menu_buttons['admin']['sub_buttons'] = array_merge(
		array_slice($menu_buttons['admin']['sub_buttons'], 0, $counter, TRUE),
		    array('kb' => array(
						'title' => $txt['knowledgebase'],
						'href' => $scripturl . '?action=admin;area=kb',
						'show' => allowedTo('admin_forum') && !empty($modSettings['kb_enabled']),
			    ),  
			),
		    array_slice($menu_buttons['admin']['sub_buttons'], $counter, NULL, TRUE)
    );
	
	if (!empty($modSettings['kb_knowledge_only'])){
		unset($menu_buttons['profile']['sub_buttons']['profile'], $menu_buttons['profile']['sub_buttons']['summary']);
	}
				
	if (!empty($modSettings['kb_knowledge_only']))
	{
		$menu_buttons['home'] = array(
			'title' => $txt['knowledgebase'],
			'href' => $scripturl . '?action=kb',
			'show' => true,
			'sub_buttons' => array(
					
			),
			'active_button' => false,
		);

		$item = false;
		foreach ($menu_buttons['home']['sub_buttons'] as $key => $value)
			if (!empty($value['show']))
				$item = $key;
			else
				unset($menu_buttons['home']['sub_buttons'][$key]);

			if (!empty($item))
				$menu_buttons['home']['sub_buttons'][$item]['is_last'] = true;

		unset($menu_buttons['kb']);
		unset($menu_buttons['help'], $menu_buttons['search'], $menu_buttons['calendar'], $menu_buttons['moderate']);

		$context['allow_search'] = false;
		$context['allow_calendar'] = false;
		$context['allow_moderation_center'] = false;

		if (!empty($modSettings['kb_disable_pm']))
		{
			$context['allow_pm'] = false;
			unset($menu_buttons['pm']);
			$context['user']['unread_messages'] = 0; 
		}

		if (!empty($modSettings['kb_disable_mlist']))
		{
			$context['allow_memberlist'] = false;
			unset($menu_buttons['mlist']);
		}
	}
}

function KB_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
   global $context;
	
   $permissionList['membergroup'] += array(
        'view_knowledge' => array(false, 'kbperm', 'kbperm'),
	    'auto_approve_kb' => array(false, 'kbperm', 'kbperm'),
	    'rate_kb' => array(false, 'kbperm', 'kbperm'),
	    'comdel_kb' => array(false, 'kbperm', 'kbperm'),
	    'rparticle_kb' => array(false, 'kbperm', 'kbperm'),
		'search_kb' => array(false, 'kbperm', 'kbperm'),
		'auto_approvecom_kb' => array(false, 'kbperm', 'kbperm'),
		'manage_kb' => array(false, 'kbperm', 'kbperm'),
		'com_kb' => array(false, 'kbperm', 'kbperm'),
    );
	
    $context['non_guest_permissions'] = array_merge(
	    $context['non_guest_permissions'],
	        array(
		        'comdel_kb',
		        'manage_kb'
	        )
    );
}

function KB_loadTheme()
{
 loadLanguage('KB');
}

function KB_admin_areas(&$admin_areas){
   global $txt,$modSettings,$scripturl;
   
    if(allowedTo('admin_forum')){
        KB_array_insert($admin_areas, 'layout',
	        array(
	            'sa_kb' => array(
		            'title' => $txt['knowledgebase'],
		            'areas' => array(
			            'kb' => array(
				            'label' => $txt['kbase_config'],
				            'file' => 'KB/KBAdmin.php',
				            'function' => 'kba',
				            'custom_url' => $scripturl . '?action=admin;area=kb',
				            'icon' => 'server.gif',
				            'subsections' => array(
				                'kb' => array($txt['kbase_config']),
								'attach' => array($txt['kb_attach7']),
				                'kbstand' => array($txt['kbase_sto']),
								'kbactionlog' => array($txt['kb_log_admin']),
				                'import' => array($txt['kb_import2']),
								'showlog' => array($txt['kb_log_admin1']),
				 
			                ),
			            ),
		            ),
		        ),
	        )
        );
		
		if(empty($modSettings['kb_disable_log']))
	        unset($admin_areas['sa_kb']['areas']['kb']['subsections']['showlog']);	
    }
}

function KB_ob(&$buffer)
{
	global $modSettings;

	if (!empty($modSettings['kb_enabled']))
	{
		$kb_replacements = array();

		if (!empty($modSettings['kb_knowledge_only']))
		{
			$kb_replacements += array(
				'~<a(.+)action=unread(.+)</a>~iuU' => '',
				'~<form([^<]+)action=search2(.+)</form>~iuUs' => '',
			);
		}

	    $buffer = preg_replace(array_keys($kb_replacements), array_values($kb_replacements), $buffer);
	}

	return $buffer;
}

function KB_actions(&$actionArray){
  global $sourcedir, $modSettings;
   

    $actionArray += array('kb' => array('KB/KBMain.php', 'KB'));
   
    if (!empty($modSettings['kb_knowledge_only']) && $modSettings['kb_enabled']) 
	{
		$notwanted_actions = array(
		    'announce', 'attachapprove', 'buddy', 'calendar', 'clock', 'collapse', 'deletemsg', 'display', 'editpoll', 'editpoll2',
			'emailuser', 'lock', 'lockvoting', 'markasread', 'mergetopics', 'moderate', 'modifycat', 'modifykarma', 'movetopic', 'movetopic2',
			'notify', 'notifyboard', 'post', 'post2', 'printpage', 'quotefast', 'quickmod', 'quickmod2', 'recent', 'reminder', 'removepoll', 'removetopic2',
			'reporttm', 'restoretopic', 'search', 'search2', 'sendtopic', 'smstats', 'splittopics', 'stats', 'sticky', 'about:mozilla', 'about:unknown',
			'unread', 'unreadreplies', 'vote', 'viewquery', 'who', '.xml', 'xmlhttp'
		);

		if (!empty($modSettings['kb_disable_pm']))
			$notwanted_actions[] = 'pm';

		if (!empty($modSettings['kb_disable_mlist']))
			$notwanted_actions[] = 'mlist';

		foreach ($notwanted_actions as $notwanted)
			if (isset($actionArray[$notwanted]))
				unset($actionArray[$notwanted]);

		if (empty($actionArray[$_GET['action']]))
			$_GET['action'] = 'kb';
	}
}
?>