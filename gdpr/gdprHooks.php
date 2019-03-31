<?php
/*
GDPR Helper
Version 1.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2018-2019 SMFHacks.com

############################################
License Information:

Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################

*/

if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function gdpr_actions(&$actionArray)
{
	global $sourcedir, $modSettings;

    // Load the language files
    if (loadlanguage('gpdr') == false)
        loadLanguage('gpdr','english');
   
  $actionArray += array('gpdr' => array('gpdr2.php', 'GPDR_Main'));
  
}


function gdpr_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;
   

    gdpr_array_insert($admin_areas, 'layout',
	        array(
                'gpdr' => array(
			'title' => $txt['gpdr_title'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'gpdr' => array(
									'label' => $txt['gpdr_title'],
									'file' => 'gpdr2.php',
									'function' => 'GPDR_Main',
									'custom_url' => $scripturl . '?action=admin;area=gpdr;sa=settings',
									'icon' => 'gdpr.png',
									'subsections' => array(
										'settings' => array($txt['gpdr_text_settings']),
										'privacyadmin' => array($txt['gpdr_privacypolicy']),
									),
				),),
		),
                
	        )
        );
		
        


}


function gdpr_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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