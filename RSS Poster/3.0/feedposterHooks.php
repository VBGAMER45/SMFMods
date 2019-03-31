<?php
/*
RSS Feed Poster
Version 5.0
by:vbgamer45
https://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function feedposter_actions(&$actionArray)
{
	global $sourcedir, $modSettings;

   
  $actionArray += array('feedsadmin' => array('FeedPoster2.php', 'FeedsMain'));
  
}


function feedposter_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;


    feedposter_array_insert($admin_areas, 'layout',
	        array(
                'feedsadmin' => array(
			'title' => $txt['smfrssposter_admin'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'feedsadmin' => array(
						'label' => $txt['smfrssposter_admin'],
						'file' => 'FeedPoster2.php',
						'function' => 'FeedsMain',
						'custom_url' => $scripturl . '?action=admin;area=feedsadmin',
						'icon' => 'feedposter.png',
					'subsections' => array(),
			),),
		),
                
	        )
        );
		
        


}

function feedposter_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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