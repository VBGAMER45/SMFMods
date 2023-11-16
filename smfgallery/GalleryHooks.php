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
function gallery_actions(&$actionArray)
{
  global $sourcedir, $modSettings;

	if (loadlanguage('Gallery') == false)
		loadLanguage('Gallery','english');
   
  $actionArray += array('gallery' => array('Gallery2.php', 'GalleryMain'));
  
}

// Permissions
function gallery_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
   global $context;
	
   $permissionList['membergroup'] += array(
            'smfgallery_view' => array(false, 'smfgallery', 'smfgallery'),
			'smfgallery_add' => array(false, 'smfgallery', 'smfgallery'),
			'smfgallery_edit' => array(false, 'smfgallery', 'smfgallery'),
			'smfgallery_delete' => array(false, 'smfgallery', 'smfgallery'),
			'smfgallery_comment' => array(false, 'smfgallery', 'smfgallery'),
			'smfgallery_report' => array(false, 'smfgallery', 'smfgallery'),
			'smfgallery_autoapprove' => array(false, 'smfgallery', 'smfgallery'),
			'smfgallery_manage' => array(false, 'smfgallery', 'smfgallery'),
    );
	

}

function gallery_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;
   	if (loadlanguage('Gallery') == false)
		loadLanguage('Gallery','english');

    gallery_array_insert($admin_areas, 'layout',
	        array(
	            'gallery' => array(
        			'title' => $txt['smfgallery_admin'],
        			'permission' => array('smfgallery_manage'),
        			'areas' => array(
                        'gallery' => array(
        									'label' => $txt['smfgallery_admin'],
        									'file' => 'Gallery2.php',
        									'function' => 'GalleryMain',
        									'custom_url' => $scripturl . '?action=admin;area=gallery;sa=adminset',
        									'icon' => 'gallery.png',
        									'subsections' => array(
        										'adminset' => array($txt['gallery_text_settings']),
        										'admincat' => array($txt['gallery_form_managecats']),
        										'reportlist' => array($txt['gallery_form_reportimages']),
        										'approvelist' => array($txt['gallery_form_approveimages']),
        										'copyright' => array($txt['gallery_txt_copyrightremoval']),
        										'convert' => array($txt['gallery_txt_convertors']),
        										
        									),
        				),),
                        
        		),
                
	        )
        );
		
        


}

function gallery_menu_buttons(&$menu_buttons)
{
	global $txt, $user_info, $context, $modSettings, $scripturl;

	#You can use these settings to move the button around or even disable the button and use a sub button
	#Main menu button options
	if (loadlanguage('Gallery') == false)
		loadLanguage('Gallery','english');

	if (!isset($txt['smfgallery_menu']))
		$txt['smfgallery_menu'] = 'Gallery';	
		
	#Where the button will be shown on the menu
	$button_insert = 'mlist';
	
	#before or after the above
	$button_pos = 'before';
	#default is before the memberlist
    
    gallery_array_insert($menu_buttons, $button_insert,
		     array(
                    'gallery' => array(
                    				'title' => $txt['smfgallery_menu'],
                    				'href' => $scripturl . '?action=gallery',
                    				'icon' => 'gallery.png',
                                    'show' => allowedTo('smfgallery_view'),
            		                'sub_buttons' => array(),
				    
			    )	
		    )
	    ,$button_pos);
        
 


}

function gallery_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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