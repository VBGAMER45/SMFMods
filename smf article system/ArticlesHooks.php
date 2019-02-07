<?php
/*
SMF Articles
Version 3.0
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function articles_actions(&$actionArray)
{
  global $sourcedir, $modSettings;
   
  $actionArray += array('articles' => array('Articles2.php', 'ArticlesMain'));
  
}

// Permissions
function articles_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
   global $context;
	
   $permissionList['membergroup'] += array(
        'view_articles' => array(false, 'smfarticles', 'smfarticles'),
			'add_articles' => array(false, 'smfarticles', 'smfarticles'),
			'edit_articles' => array(false, 'smfarticles', 'smfarticles'),
			'delete_articles' => array(false, 'smfarticles', 'smfarticles'),
			'rate_articles' => array(false, 'smfarticles', 'smfarticles'),
			'articles_comment' => array(false, 'smfarticles', 'smfarticles'),
			'articles_auto_approve' => array(false, 'smfarticles', 'smfarticles'),
			'articles_autocomment' => array(false, 'smfarticles', 'smfarticles'),
			'articles_admin' => array(false, 'smfarticles', 'smfarticles'),
    );
	

}

function articles_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;
   

    articles_array_insert($admin_areas, 'layout',
	        array(
                'articles' => array(
			'title' => $txt['smfarticles_admin'],
			'permission' => array('articles_admin'),
			'areas' => array(
				'articles' => array(
					'label' => $txt['smfarticles_admin'],
					'file' => 'Articles2.php',
					'function' => 'ArticlesMain',
					'custom_url' => $scripturl . '?action=admin;area=articles;sa=admin',
					'icon' => 'articles.png',
					'subsections' => array(
						'admin' => array($txt['smfarticles_articlessettings']),
						'admincat' => array($txt['smfarticles_txt_managecategories']),
						'alist' => array($txt['smfarticles_approvearticles']),
						'comlist' => array($txt['smfarticles_form_approvecomments']),
						'adminperm' => array($txt['smfarticles_catpermlist']),
						'importtp' => array($txt['smfarticles_txt_import']),
						'copyright' => array($txt['smfarticles_txt_copyrightremoval']),
						
					),
				),),
		),
                
	        )
        );
		
        


}

function articles_menu_buttons(&$menu_buttons)
{
	global $txt, $user_info, $context, $modSettings, $scripturl;

	#You can use these settings to move the button around or even disable the button and use a sub button
	#Main menu button options
	
	#Where the button will be shown on the menu
	$button_insert = 'mlist';
	
	#before or after the above
	$button_pos = 'before';
	#default is before the memberlist
    
    articles_array_insert($menu_buttons, $button_insert,
		     array(
                    'articles' => array(
					'title' => $txt['smfarticles_menu'],
					'href' => $scripturl . '?action=articles',
					'show' => allowedTo('view_articles'),
					'icon' => '',
    		
				    
			    )	
		    )
	    ,$button_pos);
        
 


}

function articles_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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