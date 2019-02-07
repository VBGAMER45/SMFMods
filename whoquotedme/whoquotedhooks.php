<?php
/*
Who Quoted Me
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/
if (!defined('SMF'))
	die('Hacking attempt...');


// Hook Add Action
function whoquoted_actions(&$actionArray)
{
	global $sourcedir, $modSettings;

 
  $actionArray += array('whoquoted' => array('whoquoted2.php', 'WhoQuotedMain'));
  
}



function whoquoted_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl, $sc;
   

    whoquoted_array_insert($admin_areas, 'layout',
	        array(
                        'whoquotedwebpush' => array(
			'title' => $txt['whoquoted_admin'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'whoquoted' => array(
					'label' => $txt['whoquoted_admin'],
					'file' => 'whoquoted2.php',
					'function' => 'WhoQuotedMain',
					'custom_url' => $scripturl . '?action=admin;area=whoquoted;sa=settings;sesc=' . $sc,
					'icon' => 'server.gif',
					'permission' => array('admin_forum'),
					'subsections' => array(
						'settings' => array($txt['whoquoted_admin']),					),
				),
	
			),
		),
                
	        )
        );
		



}

function whoquoted_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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