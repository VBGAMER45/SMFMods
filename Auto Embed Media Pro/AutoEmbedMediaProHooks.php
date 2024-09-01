<?php
/*
Simple Audio Video Embedder
Version 4.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
*/
if (!defined('SMF'))
	die('Hacking attempt...');




function automedia_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;
   

    automedia_array_insert($admin_areas, 'layout',
	        array(
                'mediapro' => array(
			'title' => $txt['mediapro_admin'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'mediapro' => array(
					'label' => $txt['mediapro_settings'],
					'file' => 'AutoEmbedMediaPro2.php',
					'function' => 'MediaProMain',
					'custom_url' => $scripturl . '?action=admin;area=mediapro;sa=settings',
					'icon' => 'server.gif',
					'permission' => array('admin_forum'),
					'subsections' => array(
						'settings' => array($txt['mediapro_settings']),
						'copyright' => array($txt['mediapro_copyremove']),
					),
				),
	
			),
		),
                
	        )
        );
		
        


}

function automedia_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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