<?php
/*
Login Menu Button
Version 1.0
by:vbgamer45
https://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');


function login_menu_buttons(&$menu_buttons)
{
	global $txt, $user_info, $context, $modSettings, $scripturl;
    

	#You can use these settings to move the button around or even disable the button and use a sub button
	#Main menu button options
	
	#Where the button will be shown on the menu
	$button_insert = 'signup';
	
	#before or after the above
	$button_pos = 'before';
	#default is before the memberlist
    
    login_array_insert($menu_buttons, $button_insert,
		     array(
                    'login' => array(
						'title' => $txt['login'],
						'href' => $scripturl . '?action=login',
						'show' => $user_info['is_guest'],
						'icon' => 'key_go.png',
						'sub_buttons' => array(
						),
					),   
                    'logout' => array(
						'title' => $txt['logout'],
						'href' => $scripturl . '?action=logout',
						'show' => !$user_info['is_guest'],
						'icon' => 'key_go.png',
						'sub_buttons' => array(
						),
					),

		    )
	    ,$button_pos);
        
 


}

function login_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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

function login_menu_integrate_load_theme()
{
    global $settings;

    $settings['login_main_menu'] = true;
}
?>