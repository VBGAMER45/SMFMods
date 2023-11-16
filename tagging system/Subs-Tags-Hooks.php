<?php
/*
Tagging System
Version 4.1
by:vbgamer45
https://www.smfhacks.com
*/
// Hook Add Action
function tags_actions(&$actionArray)
{

  $actionArray += array('tags' => array('Tags2.php', 'TagsMain'));

}

// Permissions
function tags_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{

   $permissionList['membergroup'] += array(
	   'smftags_add' => array(false, 'smftags', 'smftags'),
	   'smftags_del' => array(false, 'smftags', 'smftags'),
	   'smftags_manage' => array(false, 'smftags', 'smftags')
   );

}

function tags_remove_topics($topics = array())
{
	global $smcFunc;
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}tags_log 
		WHERE id_topic IN ({array_int:topics})',
		array(
			'topics' => $topics,
		)
	);
}

function tags_admin_areas(&$admin_areas)
{
   global $txt, $scripturl;

	// Load the language files
	if (loadlanguage('Tags') == false)
		loadLanguage('Tags','english');


    $admin_areas['config']['areas']['tags'] = array(
					'label' => $txt['smftags_admin'],
					'file' => 'Tags2.php',
					'function' => 'TagsMain',
					'custom_url' => $scripturl . '?action=admin;area=tags;sa=admin',
					'icon' => 'tags.png',
					'subsections' => array(
						'admin' => array($txt['smftags_settings']),
					),
				);

}

function tags_menu_buttons(&$menu_buttons)
{
	global $txt, $scripturl, $smcFunc;

	// Load the language files
	if (loadlanguage('Tags') == false)
		loadLanguage('Tags','english');


	#You can use these settings to move the button around or even disable the button and use a sub button
	#Main menu button options

	#Where the button will be shown on the menu
	$button_insert = 'search';

	#before or after the above
	$button_pos = 'before';
	#default is before the memberlist
    tags_array_insert($menu_buttons, $button_insert,
		     array(
                    'tags' => array(
				'title' => $txt['smftags_menu'],
				'href' => $scripturl . '?action=tags',
				'show' => true,
				'icon' =>  'tags.png',
			    )
		    )
	    ,$button_pos);

}

function tags_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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