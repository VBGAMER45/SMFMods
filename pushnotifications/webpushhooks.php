<?php
/*
Push Notifications
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

if (!defined('SMF'))
	die('Hacking attempt...');



function webpush_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl, $sc;
   

    webpush_array_insert($admin_areas, 'layout',
	        array(
                        'webpush' => array(
			'title' => $txt['webpush_admin'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'webpush' => array(
					'label' => $txt['webpush_admin'],
					'file' => 'webpush2.php',
					'function' => 'WebPushMain',
					'custom_url' => $scripturl . '?action=admin;area=webpush;sa=settings;sesc=' . $sc,
					'icon' => 'server.gif',
					'permission' => array('admin_forum'),
					'subsections' => array(
						'settings' => array($txt['webpush_admin']),					),
				),
	
			),
		),
                
	        )
        );
		



}

function webpush_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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