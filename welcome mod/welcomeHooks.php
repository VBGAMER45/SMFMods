<?php
/*
Welcome Topic Mod
Version 2.0
by:vbgamer45
https://www.smfhacks.com
*/
if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function welcome_actions(&$actionArray)
{
	global $sourcedir, $modSettings;


  $actionArray += array('welcome' => array('WelcomeTopic2.php', 'WelcomeTopic'));
  
}

function welcome_integrate_activate($memName)
{

    global $sourcedir;
    require_once($sourcedir . '/WelcomeTopic2.php');


	DoWelcomePost($memName);

	

}

function welcome_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;
   

    welcome_array_insert($admin_areas, 'layout',
	        array(
                'welcome' => array(
			'title' =>  $txt['welcome_admin'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'welcome' => array(
                    'label' => $txt['welcome_admin'],
					'file' => 'WelcomeTopic2.php',
					'function' => 'WelcomeTopic',
					'custom_url' => $scripturl . '?action=admin;area=welcome',
					'icon' => 'welcome.png',
					'subsections' => array(
						'welcome' => array($txt['welcome_admin']),
					),
				),),
		),
                
	        )
        );
		
        


}


function welcome_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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