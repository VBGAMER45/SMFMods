<?php
/*
Contact Page
Version 6.0
by:vbgamer45
https://www.smfhacks.com
*/


if (!defined('SMF'))
	die('Hacking attempt...');



// Hook Add Action
function contact_actions(&$actionArray)
{
	global $sourcedir, $modSettings;

  $actionArray += array('contact' => array('Contact2.php', 'Contact'));
  
}

// Permissions
function contact_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
   global $context;
	
   $permissionList['membergroup'] += array(
        'view_contact' => array(false, 'general', 'view_basic_info'),
    );
	

}

function contact_settings(&$config_vars)
{
	global $txt, $smcFunc;

    // Load the boards list
    $boardList = array();
    $boardList[0] = '';
    $request = $smcFunc['db_query']('order_by_board_order', '
		SELECT b.id_board, b.name AS board_name, c.name AS cat_name
		FROM {db_prefix}boards AS b
			LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		WHERE redirect = {string:empty_string}',
        array(
            'empty_string' => '',
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request))
        $boardList[$row['id_board']] = $row['cat_name'] . ' - ' . $row['board_name'];
    $smcFunc['db_free_result']($request);


	$config_vars = array_merge($config_vars, array(
		array('text', 'smfcontactpage_email'),
        array('select', 'smfcontactpage_board', $boardList)));
}

function contact_menu_buttons(&$menu_buttons)
{
	global $txt, $user_info, $context, $modSettings, $scripturl;

	#You can use these settings to move the button around or even disable the button and use a sub button
	#Main menu button options
	if (empty($txt['smfcontact_contact']))
        $txt['smfcontact_contact'] = 'Contact';
	
	#Where the button will be shown on the menu
	$button_insert = 'mlist';
	
	#before or after the above
	$button_pos = 'before';
	#default is before the memberlist
    
    contact_array_insert($menu_buttons, $button_insert,
		     array(
                 // [Contact Page] button
			    'contact' => array(
                    'title' => $txt['smfcontact_contact'],
                    'href' => $scripturl . '?action=contact',
                    'show' => allowedTo('view_contact'),
    				'icon' => 'contact.png',
    		
				    
			    )	
		    )
	    ,$button_pos);

}

function contact_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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