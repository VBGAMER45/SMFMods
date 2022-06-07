<?php
/*
SMF Gallery Lite Edition
Version 5.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2008-2014 SMFHacks.com

############################################
License Information:
SMF Gallery is NOT free software.
This software may not be redistributed.

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/

if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function links_actions(&$actionArray)
{
  global $sourcedir, $modSettings;

	if (loadlanguage('Links') == false)
		loadLanguage('Links','english');
   
  $actionArray += array('links' => array('Links2.php', 'LinksMain'));
  
}

// Permissions
function links_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
   global $context;
	
   $permissionList['membergroup'] += array(
            'view_smflinks' => array(false, 'smflinks', 'smflinks'),
			'add_links' => array(false, 'smflinks', 'smflinks'),
			'edit_links' => array(true, 'smflinks', 'smflinks', 'links_manage_cat'),
			'delete_links' => array(true, 'smflinks', 'smflinks', 'links_manage_cat'),
			'links_manage_cat' => array(false, 'smflinks', 'smflinks'),
			'approve_links' => array(false, 'smflinks', 'smflinks'),
			'links_auto_approve' => array(false, 'smflinks', 'smflinks'),
			'rate_links' => array(false, 'smflinks', 'smflinks'),
    );
	

}

function links_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;


	$admin_areas['layout']['areas']['links'] = array(
					'label' => $txt['smflinks_admin'],
					'file' => 'Links2.php',
					'function' => 'LinksMain',
					'custom_url' => $scripturl . '?action=admin;area=links;sa=admin',
					'icon' => 'links.png',
					'subsections' => array(
						'admin' => array($txt['smflinks_linkssettings']),
						'admincat' => array($txt['smflinks_managecats']),
						'alist' => array($txt['smflinks_approvelinks']),
						'adminperm' => array($txt['smflinks_catpermlist']),


					),
	);


}

function links_menu_buttons(&$menu_buttons)
{
	global $txt, $user_info, $context, $modSettings, $scripturl;

	#You can use these settings to move the button around or even disable the button and use a sub button
	#Main menu button options
	
	if (!isset( $txt['smflinks_menu']))
		 $txt['smflinks_menu'] = 'Links';
		
	#Where the button will be shown on the menu
	$button_insert = 'mlist';
	
	#before or after the above
	$button_pos = 'before';
	#default is before the memberlist
    
    links_array_insert($menu_buttons, $button_insert,
		     array(
                    'links' => array(
                    				'title' =>  $txt['smflinks_menu'],
                    				'href' => $scripturl . '?action=links',
                    				'icon' => 'links.png',
                                    'show' => allowedTo('view_smflinks'),
            		                'sub_buttons' => array(),
				    
			    )	
		    )
	    ,$button_pos);
        
 


}

function links_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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