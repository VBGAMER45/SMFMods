<?php
/*
SMF Trader System
Version 2.0
by:vbgamer45
http://www.smfhacks.com
*/



if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function trader_actions(&$actionArray)
{
	global $sourcedir, $modSettings;


  $actionArray += array('trader' => array('Trader2.php', 'tradermain'));
  
}

// Permissions
function trader_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
   global $context;
	
   $permissionList['membergroup'] += array(
        'smftrader_feedback' => array(false, 'smftrader', 'smftrader'),
			'smftrader_deletefeed' => array(false, 'smftrader', 'smftrader'),
			'smftrader_autorating' => array(false, 'smftrader', 'smftrader'),
    );
	

}

function trader_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl;
   

    trader_array_insert($admin_areas, 'layout',
	        array(
                'trader' => array(
			'title' => $txt['smftrader_admin'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'trader' => array(
									'label' => $txt['smftrader_admin'],
                                    'file' => 'Trader2.php',
                                    'function' => 'tradermain',
                                    'custom_url' => $scripturl . '?action=admin;area=trader;sa=admin',
									'icon' => 'trader.png',
									'subsections' => array(
										'admin' => array($txt['smftrader_admin']),
									),
				),),
		),
                
	        )
        );
		
        


}



function trader_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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