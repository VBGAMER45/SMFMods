<?php
/*
Download System
Version 2.5
by:vbgamer45
http://www.smfhacks.com
Copyright 2008-2019 SMFHacks.com

############################################
License Information:

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/

if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function downloads_actions(&$actionArray)
{
	global $sourcedir, $modSettings;

    // Load the language files
    if (loadlanguage('Downloads') == false)
        loadLanguage('Downloads','english');
   
  $actionArray += array('downloads' => array('Downloads2.php', 'DownloadsMain'));
  
}

// Permissions
function downloads_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
   global $context;
	
   $permissionList['membergroup'] += array(
        'downloads_view' => array(false, 'downloads', 'downloads'),
			'downloads_add' => array(false, 'downloads', 'downloads'),
			'downloads_edit' => array(false, 'downloads', 'downloads'),
			'downloads_delete' => array(false, 'downloads', 'downloads'),
			'downloads_ratefile' => array(false, 'downloads', 'downloads'),
			'downloads_comment' => array(false, 'downloads', 'downloads'),
			'downloads_editcomment' => array(false, 'downloads', 'downloads'),
			'downloads_report' => array(false, 'downloads', 'downloads'),
			'downloads_autocomment' => array(false, 'downloads', 'downloads'),
			'downloads_autoapprove' => array(false, 'downloads', 'downloads'),
			'downloads_manage' => array(false, 'downloads', 'downloads'),
    );
	

}

function downloads_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;
   

    // Load the language files
    if (loadlanguage('Downloads') == false)
        loadLanguage('Downloads','english');

    downloads_array_insert($admin_areas, 'layout',
	        array(
                'downloads' => array(
			'title' => $txt['downloads_admin'],
			'permission' => array('downloads_manage'),
			'areas' => array(
				'downloads' => array(
									'label' => $txt['downloads_admin'],
									'file' => 'Downloads2.php',
									'function' => 'DownloadsMain',
									'custom_url' => $scripturl . '?action=admin;area=downloads;sa=adminset',
									'icon' => 'drive.png',
									'subsections' => array(
										'adminset' => array($txt['downloads_text_settings']),
										'approvelist' => array($txt['downloads_form_approvedownloads']),
										'reportlist' => array($txt['downloads_form_reportdownloads']),
										'commentlist' => array($txt['downloads_form_approvecomments']),
										'filespace' => array($txt['downloads_filespace']),
										'catpermlist' => array($txt['downloads_text_catpermlist2']),
										'import' => array($txt['downloads_txt_import']),
									),
				),),
		),
                
	        )
        );
		
        


}

function downloads_menu_buttons(&$menu_buttons)
{
	global $txt, $user_info, $context, $modSettings, $scripturl;

	#You can use these settings to move the button around or even disable the button and use a sub button
	#Main menu button options

    // Load the language files
    if (loadlanguage('Downloads') == false)
        loadLanguage('Downloads','english');
	
	if (!isset($txt['downloads_menu']))
		$txt['downloads_menu'] = 'Downloads';
	
	#Where the button will be shown on the menu
	$button_insert = 'mlist';
	
	#before or after the above
	$button_pos = 'before';
	#default is before the memberlist
    
    downloads_array_insert($menu_buttons, $button_insert,
		     array(
                    'downloads' => array(
    				'title' => $txt['downloads_menu'],
    				'href' => $scripturl . '?action=downloads',
    				'show' => allowedTo('downloads_view'),
    				'icon' => 'drive.png',
    		
				    
			    )	
		    )
	    ,$button_pos);
        
 


}

function downloads_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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