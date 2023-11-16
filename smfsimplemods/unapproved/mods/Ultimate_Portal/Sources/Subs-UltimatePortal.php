<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.4
*	Project manager: vicram10
*	Copyright 2011-2021
*	Powered by SMFSimple.com
**********************************************************************************/
/*
Functions

	-	void template_top_menu_ultimate_portal()!!
		//
	
	- 	void updateUltimatePortalSettings($changeArray, $section, $update = false)!!
		//
	
	-	void UltimatePortalBlockDir()!!
		//
	
	-	void UltimatePortalLangs()!!
		//
	
	-	void LoadSpecificLang($this_file)!!
		//
	
	-	void save_cover_in_folder($image = URL image)
			+	Function of User Post Module
			
	-	void context_html_headers()!!
		//Load the TinyMCE link headers

*/

if (!defined('SMF'))
	die('Hacking attempt...');

//Reduce Site Overload is Checked? okay... if save blocks, edit blocks, delete existing cache files....
function RSODeleteCacheFiles()
{
	global $ultimateportalSettings, $cachedir;
	
	if(!empty($ultimateportalSettings['up_reduce_site_overload']))
	{
		if((cache_get_data('up_bk', 3600)) != NULL 
			|| (cache_get_data('load_block_center', 3600)) != NULL 
			|| (cache_get_data('load_block_left', 3600)) != NULL 
			|| (cache_get_data('load_block_right', 3600)) != NULL)
		{
			$no_load_files = array('index.php', '.htaccess', 'index.html');
			if ($handle = opendir($cachedir)) 
			{
				while (false !== ($file = readdir($handle))) 
				{
					$ext = substr(strrchr($file, "."), 1); //Get file extension			
					if ($ext == "php") 
					{
						$file_explode = explode('-', str_replace(".php", "", $file));
						if (in_array('up_bk', $file_explode) 
							|| in_array('load_block_center', $file_explode) 
							|| in_array('load_block_left', $file_explode) 
							|| in_array('load_block_right', $file_explode)) 
						{
							//Borramos el archivo
							unlink($cachedir .'/'.$file);	
						}
					}
				}
				closedir($handle);
			}			
		}
	}
}
// Load this user's permissions for the Modules.
function LoadMemberGroupsPermissions()
{
	global $user_info, $db_prefix, $board, $board_info, $modSettings;
	global $smcFunc;		
	
	$user_info['up-modules-permissions'] = array();

	if ($user_info['is_admin'])
		return;

	$cache_groups = $user_info['groups'];
	asort($cache_groups);
	$cache_groups = implode(',', $cache_groups);

	// Get the general permissions.
	$request = $smcFunc['db_query']('',"
		SELECT permission, value
		FROM {$db_prefix}up_groups_perms
		WHERE ID_GROUP IN (" . implode(', ', $user_info['groups']) . ')');
	$removals = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (empty($row['value']))
			$removals[] = $row['permission'];
		else
			$user_info['up-modules-permissions'][$row['permission']] = $row['value'];
	}
	$smcFunc['db_free_result']($request);

	if (isset($cache_groups))
		cache_put_data('up-modules-permissions:' . $cache_groups, array($user_info['up-modules-permissions'], $removals), 240);

}

// Ultimate Portal [Show the top menu] ...
function template_top_menu_ultimate_portal()
{
	global $context, $settings, $options, $scripturl, $txt;
	global $sourcedir;

	echo '
		<ul class="dropmenu">';
	foreach ($context['top_menu_buttons'] as $top_buttons)
	{
		echo '
				<li id="button_', $top_buttons['id'], '">
					<a class="', !empty($context[$top_buttons['id']]['active_button']) ? $context[$top_buttons['id']]['active_button'] : '' ,'firstlevel" href="', $top_buttons['href'], '">
						<span class="', isset($top_buttons['is_last']) ? 'last ' : '', 'firstlevel">', $top_buttons['title'], '</span>
					</a>
				</li>';
	}
	echo '
		</ul>';
		
}
	
// Helper function, it sets up the context for database ULTIMATE PORTAL settings.
function prepareUltimatePortalSettingContext($section)
{
	global $db_prefix, $context, $modSettings;
	global $smcFunc;
	
	$context['up-config_vars'] = array();
	$myquery = $smcFunc['db_query']('', "
				SELECT variable 
				FROM {$db_prefix}ultimate_portal_settings
				WHERE section = {string:section}",
				array(
					'section' => $section,	  
				));
				
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$configvars = &$context['up-config_vars'][];	
		$configvars['variable'] = $row['variable'];
	}
	
	$smcFunc['db_free_result']($myquery);
}
	
// Helper function for saving database settings.
function saveUltimatePortalSettings($section)
{
	global $db_prefix, $context, $scripturl, $txt;
	global $smcFunc;

	prepareUltimatePortalSettingContext($section);
	
	foreach ($context['up-config_vars'] as $configvars)
	{		
		$configUltimatePortalVar[$configvars['variable']] = isset($_POST[$configvars['variable']]) ? $_POST[$configvars['variable']] : '';
	}	

	updateUltimatePortalSettings($configUltimatePortalVar, $section);
}
	
// Updates the Ultimate Portal Settings table as well as $ultimateportalSettings... only does one at a time if $update is true.
// All input variables and values are assumed to have escaped apostrophes(')!
function updateUltimatePortalSettings($changeArray, $section, $update = false)
{
	global $db_prefix, $ultimateportalSettings;
	global $smcFunc;
	
	if (empty($changeArray) || !is_array($changeArray))
		return;

	// In some cases, this may be better and faster, but for large sets we don't want so many UPDATEs.
	if ($update)
	{
		foreach ($changeArray as $variable => $value)
		{
			$smcFunc['db_query']('',"
				UPDATE {$db_prefix}ultimate_portal_settings
				SET value = " . ($value === true ? 'value + 1' : ($value === false ? 'value - 1' : "'$value'")) . "
				WHERE variable = {string:variable}
				LIMIT {int:limit}",
				array(
					'variable' => $variable,
					'limit' => 1,
				));
			$ultimateportalSettings[$variable] = $value === true ? $ultimateportalSettings[$variable] + 1 : ($value === false ? $ultimateportalSettings[$variable] - 1 : stripslashes($value));
		}

		// Clean out the cache and make sure the cobwebs are gone too.
		cache_put_data('ultimateportalSettings', null, 90);

		return;
	}

	$replaceArray = array();
	foreach ($changeArray as $variable => $value)
	{
		// Don't bother if it's already like that ;).
		if (isset($ultimateportalSettings[$variable]) && $ultimateportalSettings[$variable] == stripslashes($value))
			continue;
		// If the variable isn't set, but would only be set to nothing'ness, then don't bother setting it.
		elseif (!isset($ultimateportalSettings[$variable]) && empty($value))
			continue;
		
		$value = $smcFunc['htmlspecialchars']($value, ENT_QUOTES);
		
		$replaceArray[] = "(SUBSTRING('$variable', 1, 255), SUBSTRING('$value', 1, 65534), SUBSTRING('$section', 1, 65534))";
		$ultimateportalSettings[$variable] = stripslashes($value);
	}

	if (empty($replaceArray))
		return;

	$smcFunc['db_query']('',"
		REPLACE INTO {$db_prefix}ultimate_portal_settings
			(variable, value, section)
		VALUES " . implode(',
			', $replaceArray));

	// Kill the cache - it needs redoing now, but we won't bother ourselves with that here.
	cache_put_data('ultimateportalSettings', null, 90);
	
}

//Load Block Directory
function UltimatePortalBlockDir()
{
	global $db_prefix, $context, $scripturl, $txt, $sourcedir, $boarddir;
	global $smcFunc;
	
	$listfile = ''; //only is declared
	$file = '';//only is declared
	$listfile2 = '';//only is declared

	$myquery = $smcFunc['db_query']('',"
				SELECT file 
				FROM {$db_prefix}ultimate_portal_blocks");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$listfile .= $row['file'];
	}

	$dirb = $boarddir."/up-blocks/";
	
	//First, not load this files
	$no_load_files = array('index.php', '.htaccess', 'index.html');
	//Add database entries for new system blocks (uploaded php block files)
	if ($handle = opendir($dirb)) {
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, $no_load_files))	
			{
				$ext = substr(strrchr($file, "."), 1); //Get file extension			
				if ($ext == "php") {
					$pos = strpos($listfile, $file);
					if ($pos === false) {
						$title = str_replace(".php", "", $file);
						$icon = $title;
						$smcFunc['db_query']('',"INSERT INTO {$db_prefix}ultimate_portal_blocks(file, title, icon, content, bk_collapse, bk_no_title, bk_style) 
								VALUES('$file', '$title', '$icon', '', 'on', '', 'on')");
					}
					$listfile2 .= $file.' ';
				}
			}
		}
		closedir($handle);
   	}
	
	//Delete database entries for missing system blocks
	$myquery = $smcFunc['db_query']('',"
					SELECT file 
					FROM {db_prefix}ultimate_portal_blocks 
					WHERE personal={int:personal}",
					array(
						'personal' => 0,
					));
					
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$pos = strpos($listfile2, $row['file']);
			if ($pos === false) {
			$smcFunc['db_query']('', "DELETE FROM {$db_prefix}ultimate_portal_blocks 
					WHERE file={string:file}",
					array(
						'file' => $row['file'],
					));
		}
	}
}

//Load Lang Directory
function UltimatePortalLangs()
{
	global $db_prefix, $context, $scripturl, $txt, $boarddir;

	$dir = '';
	$dir = opendir($boarddir . '/Themes/default/languages');

	$context['ult_port_langs'] = "\n<select size='1' name='file'>\n";
	while (($file = readdir($dir)) !== false) {
		if ($file != "." && $file != ".." /* && preg_match("`(.*)\.".$context['user']['language']."`i", $file)*/) {
			$context['ult_port_langs'] .= "<option value='$file' >$file</option>\n";		
		}
	}
	closedir($dir);
	$context['ult_port_langs'] .= "</select>\n";
	
}

//Load Specific lang file
function LoadSpecificLang($this_file)
{
	global $db_prefix, $context, $scripturl, $txt;
	global $boarddir;
	global $smcFunc;

	//Language Dir 
	$this_file = $boarddir . '/Themes/default/languages/'. $this_file;
	
	//Cached title and text values
	if (!$handle = fopen($this_file, "rb")) {
		fatal_lang_error('ultport_error_no_add_bk_fopen_error',false);
		exit;
	}
	$context['content'] = fread($handle, filesize($this_file));
	$context['content_htmlspecialchars'] = htmlspecialchars($context['content']);
	fclose($handle);

}

//Create Specific lang file
function CreateSpecificLang($file, $content)
{
	global $db_prefix, $context, $scripturl, $txt;
	global $boarddir;
	global $smcFunc;

	$file = str_replace("..","",$file);

	$file = str_replace("\\","",$file);

		$dir = $boarddir . '/Themes/default/languages/'.$file;
		
		//Create cached php file		
		if (!$handle = fopen($dir, 'wb')) {
 			fatal_lang_error('ultport_error_fopen_error',false);
			exit;
		}
		//Write $content to cached php file
   		if (!fwrite($handle, $content)) {
			fatal_lang_error('ultport_error_no_add_bk_nofile',false);
			exit;
   		}
		fclose($handle);
		//Close cached php file				

}

//Prepare the Ultimate Portal Permissions Groups
function LoadUPModulesPermissions()
{
	global $db_prefix, $context, $txt;

	//Add a new value, and this automatically added in the Permissions Forms, from Ultimate Portal Permissions Settings 	
	$perms_text_name = array(
		'user_posts_add',
		'user_posts_moderate',
		'news_add',
		'news_moderate',
		'download_add',
		'download_moderate',		
		'ipage_add',
		'ipage_moderate',						
		'faq_add',
		'faq_moderate',				
	);

	$context['permissions'] = array();
	
	foreach ($perms_text_name as $text_name)
	{
		/*
			The $txt['ultport_perms_'] localization is language/UltimatePortalCP.YOUR-LANGUAGE.php
			Search for //Perms - Names 
		*/
		$permissions = &$context['permissions'][];
		$permissions['name'] = $text_name; 
		$permissions['text-name'] = $txt['ultport_perms_'.$text_name]; 
		
	}
}

//Load MemberGroups
function LoadMemberGroups($group_selected = -99, $call = '')
{
	global $db_prefix, $context, $txt, $smcFunc;

	$guest = true;
	$regularmember = true;
	
	$dbresult = $smcFunc['db_query']('', "
		SELECT id_group, group_name
		FROM {$db_prefix}membergroups
		". (empty($call) ? 'WHERE id_group <> {int:id_group} AND min_posts = {int:min_posts}' : ($call=='about_us' ? 'WHERE min_posts = {int:min_posts}' : '')) ."
		ORDER BY group_name",
		array(
			'id_group' => 1,
			'min_posts' => -1,
		)
	);
	  
	$context['groups'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{	
		$context['groups'][$row['id_group']] = array(
			'id_group' => $row['id_group'],
			'group_name' => $row['group_name'],
			'selected' => (($row['id_group'] == $group_selected) ? 'selected="selected"' : ''),
		);	
	}
	//Add Regular member
	if ($regularmember === true)
	{
		$context['groups'][0] = array(
			'id_group' => 0,
			'group_name' => $txt['membergroups_members'],
			'selected' => (($group_selected == 0) ? 'selected="selected"' : ''),		
		);	
	}	
	//Add Guest
	if ($guest === true)
	{
		$context['groups'][-1] = array(
			'id_group' => -1,
			'group_name' => '<strong>'. $txt['membergroups_guests'] .'</strong>',
			'selected' => (($group_selected == -1) ? 'selected="selected"' : ''),		
		);	
	}
	$smcFunc['db_free_result']($dbresult);	

}

//Load STAFF Members (About Us Module Requires)
function LoadStaffMembers()
{
	global $db_prefix, $context, $txt, $smcFunc;
	global $settings, $scripturl, $modSettings, $ultimateportalSettings;

	// Parameters for the avatars.
	if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
	{
		$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
		$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
	}
	else
	{
		$avatar_width = '';
		$avatar_height = '';
	}

	$dbresult = $smcFunc['db_query']('', '
		SELECT 	g.id_group, g.group_name, g.description, g.online_color, g.stars
		FROM {db_prefix}membergroups g
		WHERE g.min_posts = {int:min_posts}
		ORDER BY g.id_group',
		array(
			'min_posts' => -1,	  
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{	
		$stars = explode('#', $row['stars']);
		$context['staff'][$row['id_group']] = array(
			'id_group' => $row['id_group'],
			'group_name' => $row['group_name'],
			'description' => !empty($row['description']) ? ' - '.$row['description'] : '',
			'online_color' => $row['online_color'],
			'stars' => !empty($stars[0]) && !empty($stars[1]) ? str_repeat('<img style="vertical-align:middle;" src="' . $settings['images_url'] . '/' . $stars[1] . '" alt="" border="0" />', $stars[0]) : '',
		);			
		$sql = $smcFunc['db_query']('', '
			SELECT 
				mem.id_member, mem.member_name, mem.date_registered, mem.email_address, mem.avatar,
				IFNULL(lo.log_time, 0) AS is_online, 
				IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type
			FROM {db_prefix}members AS mem
				LEFT JOIN {db_prefix}log_online AS lo ON (lo.id_member = mem.id_member)
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member)
			WHERE mem.id_group = {int:id_group}',
			array(
				'id_group' => $row['id_group'],
			)
		);

		while ($row_member = $smcFunc['db_fetch_assoc']($sql))
		{
			$context['staff'][$row['id_group']]['members'][] = array(
				'id_member' => $row_member['id_member'],
				'member_name' => $row_member['member_name'],
				'href' => $scripturl . '?action=profile;u=' . $row_member['id_member'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row_member['id_member'] . '"><span style="color: ' . $row['online_color'] . ';">' . $row_member['member_name'] . '</span></a>',				
				'date_registered' => timeformat($row_member['date_registered']),
				'email_address' => $row_member['email_address'],
				'avatar' => array(
					'name' => $row_member['avatar'],
					'image' => $row_member['avatar'] == '' ? ($row_member['id_attach'] > 0 ? '<img src="' . (empty($row_member['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row_member['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row_member['filename']) . '" alt="" class="avatar" border="0" />' : '<img alt="No Avatar" title="No Avatar" width="65" height="65" src="'. $settings['default_images_url'] . '/ultimate-portal/no_avatar.png" />') : (stristr($row_member['avatar'], 'http://') ? '<img src="' . $row_member['avatar'] . '"' . $avatar_width . $avatar_height . ' alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row_member['avatar']) . '" alt="" class="avatar" border="0" />'),
					'href' => $row_member['avatar'] == '' ? ($row_member['id_attach'] > 0 ? (empty($row_member['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row_member['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row_member['filename']) : '') : (stristr($row_member['avatar'], 'http://') ? $row_member['avatar'] : $modSettings['avatar_url'] . '/' . $row_member['avatar']),
					'url' => $row_member['avatar'] == '' ? '' : (stristr($row_member['avatar'], 'http://') ? $row_member['avatar'] : $modSettings['avatar_url'] . '/' . $row_member['avatar'])
				),
				'online' => array(
					'label' => $txt[$row_member['is_online'] ? 'online' : 'offline'],
					'href' => $scripturl . '?action=pm;sa=send;u=' . $row_member['id_member'],
					'link' => '<a href="' . $scripturl . '?action=pm;sa=send;u=' . $row_member['id_member'] . '">' . $txt[$row_member['is_online'] ? 'online' : 'offline'] . '</a>',
						'image_href' => $settings['images_url'] . '/' . ($row_member['is_online'] ? 'useron' : 'useroff') . '.gif',
				),				
			);			
		}
		$smcFunc['db_free_result']($sql);		
	}
	$smcFunc['db_free_result']($dbresult);	
}

function LoadBlocksTableLEFT()
{
	global $db_prefix, $context, $txt, $smcFunc;

	$context['exists_left'] = 0;
	$myquery = $smcFunc['db_query']('',"
		SELECT * FROM {$db_prefix}ultimate_portal_blocks 
		WHERE position = {string:position}
		ORDER BY active DESC, progressive, id",
		array(
			'position' => 'left',	  
		));
	$totprog = 	$smcFunc['db_num_rows']($myquery);
	
	$context['left-progoption'] = '';//only is declared
	
	for ($i = 1; $i <= $totprog; $i++) {
   		$context['left-progoption'] .= "<option value=\"$i\">$i</option>";
	}

	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['exists_left'] = 1;
		$block_left = &$context['block-left'][];
		$block_left['id'] = $row['id'];
		$block_left['title'] = $row['title'];
		$block_left['position'] = $txt['ultport_blocks_left'];
		$block_left['progressive'] = $row['progressive'];
		if ($block_left['progressive'] == 100) {
   			$block_left['progressive'] = $totprog;
		}

		$block_left['active'] = $row['active'];		
		$block_left['activestyle'] = $block_left['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
		$block_left['active'] = $block_left['active'] ? "checked=\"checked\"" : "";

		$block_left['title_form'] = $block_left['id']."_title";
		$block_left['position_form'] = $block_left['id']."_position";
		$block_left['progressive_form'] = $block_left['id']."_progressive";
		$block_left['active_form'] = $block_left['id']."_active";
	}
	
	$smcFunc['db_free_result']($myquery);
}

function LoadBlocksTableCENTER()
{
	global $db_prefix, $context, $txt, $smcFunc;

	$context['exists_center'] = 0;
	$myquery = $smcFunc['db_query']('',"
		SELECT * FROM {db_prefix}ultimate_portal_blocks 
		WHERE position = {string:position}
		ORDER BY active DESC, progressive, id",
		array(
			'position' => 'center',	  
		));
	$totprog = 	$smcFunc['db_num_rows']($myquery);
	
	$context['center-progoption'] = '';//only is declared
	
	for ($i = 1; $i <= $totprog; $i++) {
   		$context['center-progoption'] .= "<option value=\"$i\">$i</option>";
	}

	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['exists_center'] = 1;
		$block_center = &$context['block-center'][];
		$block_center['id'] = $row['id'];
		$block_center['title'] = $row['title'];
		$block_center['position'] = $txt['ultport_blocks_center'];
		$block_center['progressive'] = $row['progressive'];
		if ($block_center['progressive'] == 100) {
   			$block_center['progressive'] = $totprog;
		}

		$block_center['active'] = $row['active'];		
		$block_center['activestyle'] = $block_center['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
		$block_center['active'] = $block_center['active'] ? "checked=\"checked\"" : "";

		$block_center['title_form'] = $block_center['id']."_title";
		$block_center['position_form'] = $block_center['id']."_position";
		$block_center['progressive_form'] = $block_center['id']."_progressive";
		$block_center['active_form'] = $block_center['id']."_active";
	}
	$smcFunc['db_free_result']($myquery);
}

function LoadBlocksTableRIGHT()
{
	global $db_prefix, $context, $txt, $smcFunc;

	$context['exists_right'] = 0;
	$myquery = 	$smcFunc['db_query']('',"
		SELECT * FROM {db_prefix}ultimate_portal_blocks 
		WHERE position = {string:position}
		ORDER BY active DESC, progressive, id",
		array(
			'position' => 'right',
		));
	$totprog = $smcFunc['db_num_rows']($myquery);
	
	$context['right-progoption'] = '';//only is declared
	
	for ($i = 1; $i <= $totprog; $i++) {
   		$context['right-progoption'] .= "<option value=\"$i\">$i</option>";
	}

	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['exists_right'] = 1;
		$block_right = &$context['block-right'][];
		$block_right['id'] = $row['id'];
		$block_right['title'] = $row['title'];
		$block_right['position'] = $txt['ultport_blocks_right'];
		$block_right['progressive'] = $row['progressive'];
		if ($block_right['progressive'] == 100) {
   			$block_right['progressive'] = $totprog;
		}

		$block_right['active'] = $row['active'];		
		$block_right['activestyle'] = $block_right['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
		$block_right['active'] = $block_right['active'] ? "checked=\"checked\"" : "";

		$block_right['title_form'] = $block_right['id']."_title";
		$block_right['position_form'] = $block_right['id']."_position";
		$block_right['progressive_form'] = $block_right['id']."_progressive";
		$block_right['active_form'] = $block_right['id']."_active";
	}

}

function LoadBlocksTableHEADER($filter = "")
{
	global $db_prefix, $context, $txt, $smcFunc;

	$context['exists_multiheader'] = 0;
	
	$mbquery = 	$smcFunc['db_query']('',"
		SELECT * FROM {db_prefix}up_multiblock 
		WHERE position = {string:position}
		". (!empty($filter) ? " ".$filter." " : "") ."
		ORDER BY id",
		array(
			'position' => 'header',
		));	
	while($row = $smcFunc['db_fetch_assoc']($mbquery) ) 
	{
		$context['exists_multiheader'] = 1;
		$context['block-header'][$row['id']] = array(
			'id' => $row['id'],
			'mbtitle' => $row['title'],
			'blocks' => $row['blocks'],
			'position' => $row['position'],
			'design' => $row['design'],
			'mbk_title' => $row['mbk_title'],
			'mbk_collapse' => $row['mbk_collapse'],
			'mbk_style' => $row['mbk_style'],
		);			
	
		$id_blocks = $context['block-header'][$row['id']]['blocks'];
		
		$myquery = 	$smcFunc['db_query']('',"
			SELECT * FROM {db_prefix}ultimate_portal_blocks 
			WHERE position = {string:position} and id in($id_blocks)
			ORDER BY mbk_view ASC, progressive, id",
			array(
				'position' => 'header',
			));
		
		$totprog = $smcFunc['db_num_rows']($myquery);
		
		$context['header-progoption-'.$row['id']] = '';//only is declared
		
		for ($i = 1; $i <= $totprog; $i++) {
			$context['header-progoption-'.$row['id']] .= "<option value=\"$i\">$i</option>";
		}
	
		while( $row2 = $smcFunc['db_fetch_assoc']($myquery) ) 
		{
			$context['block-header'][$row['id']]['vblocks'][] = array(
				'id' => $row2['id'],
				'title' => $row2['title'],
				'position' => $row2['position'],
				'progressive' => $row2['progressive']!=100 ? $row2['progressive'] : $totprog,
				'activestyle' => $row2['active'] ? "windowbg" : "windowbg2", //Active block highlighting
				'active' => $row2['active'] ? "checked=\"checked\"" : "",
				'title_form' => $row2['id']."_title",
				'position_form' => $row2['id']."_position",
				'progressive_form' => $row2['id']."_progressive",
				'active_form' => $row2['id']."_active",
				'file' => $row2['file'],
				'icon' => $row2['icon'],
				'personal' => $row2['personal'],
				'content' => $row2['content'],
				'perms' => $row2['perms'],
				'bk_collapse' => $row2['bk_collapse'],
				'bk_no_title' => $row2['bk_no_title'],
				'bk_style' => $row2['bk_style'],
				'mbk_view' => $row2['mbk_view'],
			);	
		}
	}
}

function LoadBlocksTableFOOTER($filter = "")
{
	global $db_prefix, $context, $txt, $smcFunc;

	$context['exists_footer'] = 0;
	
	$mbquery = 	$smcFunc['db_query']('',"
		SELECT * FROM {db_prefix}up_multiblock 
		WHERE position = {string:position}
		". (!empty($filter) ? " ".$filter." " : "") ."
		ORDER BY id",
		array(
			'position' => 'footer',
		));	
	while($row = $smcFunc['db_fetch_assoc']($mbquery) ) 
	{
		$context['exists_footer'] = 1;
		$context['block-footer'][$row['id']] = array(
			'id' => $row['id'],
			'mbtitle' => $row['title'],
			'blocks' => $row['blocks'],
			'position' => $row['position'],
			'design' => $row['design'],
			'mbk_title' => $row['mbk_title'],
			'mbk_collapse' => $row['mbk_collapse'],
			'mbk_style' => $row['mbk_style'],
		);			
	
		$id_blocks = $context['block-footer'][$row['id']]['blocks'];
		
		$myquery = 	$smcFunc['db_query']('',"
			SELECT * FROM {db_prefix}ultimate_portal_blocks 
			WHERE position = {string:position} and id in($id_blocks)
			ORDER BY mbk_view ASC, progressive, id",
			array(
				'position' => 'footer',
			));
		
		$totprog = $smcFunc['db_num_rows']($myquery);
		
		$context['footer-progoption-'.$row['id']] = '';//only is declared
		
		for ($i = 1; $i <= $totprog; $i++) {
			$context['footer-progoption-'.$row['id']] .= "<option value=\"$i\">$i</option>";
		}
	
		while( $row2 = $smcFunc['db_fetch_assoc']($myquery) ) 
		{
			$context['block-footer'][$row['id']]['vblocks'][] = array(
				'id' => $row2['id'],
				'title' => $row2['title'],
				'position' => $row2['position'],
				'progressive' => $row2['progressive']!=100 ? $row2['progressive'] : $totprog,
				'activestyle' => $row2['active'] ? "windowbg" : "windowbg2", //Active block highlighting
				'active' => $row2['active'] ? "checked=\"checked\"" : "",
				'title_form' => $row2['id']."_title",
				'position_form' => $row2['id']."_position",
				'progressive_form' => $row2['id']."_progressive",
				'active_form' => $row2['id']."_active",
				'file' => $row2['file'],
				'icon' => $row2['icon'],
				'personal' => $row2['personal'],
				'content' => $row2['content'],
				'perms' => $row2['perms'],
				'bk_collapse' => $row2['bk_collapse'],
				'bk_no_title' => $row2['bk_no_title'],
				'bk_style' => $row2['bk_style'],
				'mbk_view' => $row2['mbk_view'],
			);	
		}
	}
}

function LoadBlocksHEADERPortal($filter = "")
{
	global $db_prefix, $context, $txt, $smcFunc;

	$context['exists_multiheader'] = 0;
	
	$mbquery = 	$smcFunc['db_query']('',"
		SELECT * FROM {db_prefix}up_multiblock 
		WHERE position = {string:position}
		". (!empty($filter) ? " ".$filter." " : "") ."
		ORDER BY id",
		array(
			'position' => 'header',
		));	
	while($row = $smcFunc['db_fetch_assoc']($mbquery) ) 
	{
		$context['exists_multiheader'] = 1;
		$context['block-header'][$row['id']] = array(
			'id' => $row['id'],
			'mbtitle' => $row['title'],
			'blocks' => $row['blocks'],
			'position' => $row['position'],
			'design' => $row['design'],
			'mbk_title' => $row['mbk_title'],
			'mbk_collapse' => $row['mbk_collapse'],
			'mbk_style' => $row['mbk_style'],
		);			
	
		$id_blocks = $context['block-header'][$row['id']]['blocks'];
		
		$myquery = 	$smcFunc['db_query']('',"
			SELECT * FROM {db_prefix}ultimate_portal_blocks 
			WHERE position = {string:position} and id in($id_blocks) and active = 'checked'
			ORDER BY mbk_view ASC, progressive, id",
			array(
				'position' => 'header',
			));
		
		$totprog = $smcFunc['db_num_rows']($myquery);
		
		$context['header-progoption-'.$row['id']] = '';//only is declared
		
		for ($i = 1; $i <= $totprog; $i++) {
			$context['header-progoption-'.$row['id']] .= "<option value=\"$i\">$i</option>";
		}
	
		while( $row2 = $smcFunc['db_fetch_assoc']($myquery) ) 
		{
			$context['block-header'][$row['id']]['vblocks'][$row2['mbk_view']][] = array(
				'id' => $row2['id'],
				'title' => $row2['title'],
				'position' => $row2['position'],
				'active' => $row2['active'],
				'file' => $row2['file'],
				'icon' => $row2['icon'],
				'personal' => $row2['personal'],
				'content' => $row2['content'],
				'perms' => $row2['perms'],
				'bk_collapse' => $row2['bk_collapse'],
				'bk_no_title' => $row2['bk_no_title'],
				'bk_style' => $row2['bk_style'],
				'mbk_view' => $row2['mbk_view'],
			);				
		}
	}
}

function LoadBlocksFOOTERPortal($filter = "")
{
	global $db_prefix, $context, $txt, $smcFunc;

	$context['exists_multifooter'] = 0;
	$mbquery = 	$smcFunc['db_query']('',"
		SELECT * FROM {db_prefix}up_multiblock 
		WHERE position = {string:position}
		". (!empty($filter) ? " ".$filter." " : "") ."
		ORDER BY id",
		array(
			'position' => 'footer',
		));	
	while($row = $smcFunc['db_fetch_assoc']($mbquery) ) 
	{
		$context['exists_multifooter'] = 1;
		$context['block-footer'][$row['id']] = array(
			'id' => $row['id'],
			'mbtitle' => $row['title'],
			'blocks' => $row['blocks'],
			'position' => $row['position'],
			'design' => $row['design'],
			'mbk_title' => $row['mbk_title'],
			'mbk_collapse' => $row['mbk_collapse'],
			'mbk_style' => $row['mbk_style'],
		);			
	
		$id_blocks = $context['block-footer'][$row['id']]['blocks'];
		
		$myquery = 	$smcFunc['db_query']('',"
			SELECT * FROM {db_prefix}ultimate_portal_blocks 
			WHERE position = {string:position} and id in($id_blocks) and active = 'checked'
			ORDER BY mbk_view ASC, progressive, id",
			array(
				'position' => 'footer',
			));
		
		$totprog = $smcFunc['db_num_rows']($myquery);
		
		$context['header-progoption-'.$row['id']] = '';//only is declared
		
		for ($i = 1; $i <= $totprog; $i++) {
			$context['header-progoption-'.$row['id']] .= "<option value=\"$i\">$i</option>";
		}
	
		while( $row2 = $smcFunc['db_fetch_assoc']($myquery) ) 
		{
			$context['block-footer'][$row['id']]['vblocks'][$row2['mbk_view']][] = array(
				'id' => $row2['id'],
				'title' => $row2['title'],
				'position' => $row2['position'],
				'active' => $row2['active'],
				'file' => $row2['file'],
				'icon' => $row2['icon'],
				'personal' => $row2['personal'],
				'content' => $row2['content'],
				'perms' => $row2['perms'],
				'bk_collapse' => $row2['bk_collapse'],
				'bk_no_title' => $row2['bk_no_title'],
				'bk_style' => $row2['bk_style'],
				'mbk_view' => $row2['mbk_view'],
			);				
		}
	}
}

function LoadBlocksTitle()
{
	global $db_prefix, $context, $txt, $smcFunc;
	
	$myquery = $smcFunc['db_query']('',"
		SELECT id, title, active 
		FROM {db_prefix}ultimate_portal_blocks 
		ORDER BY active DESC, id");
	
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$block_title = &$context['block-title'][];
		$block_title['id'] = $row['id'];
		$block_title['title'] = $row['title'];
		$block_title['active'] = $row['active'];		
		$block_title['activestyle'] = $block_title['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
		$block_title['title_block'] = $block_title['id']."_title";
	}

}

function LoadMainLinks()
{
	global $db_prefix, $context, $txt, $settings, $boardurl, $smcFunc;
	
	$myquery = 	$smcFunc['db_query']('',"SELECT id, icon, title, url, position, active, top_menu
					FROM {$db_prefix}ultimate_portal_main_links 
					ORDER BY active DESC, position");
	
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$main_link = &$context['main-links'][];
		$main_link['id'] = $row['id'];
		$main_link['icon'] = str_replace("<UP_MAIN_LINK_ICON>", $settings['default_images_url'] . '/ultimate-portal/main-links', $row['icon']);		
		$main_link['icon'] = '<img width="16" height="16" src="'. $main_link['icon'] .'" alt="" />';
		$main_link['title'] = $row['title'];		
		$main_link['url'] = str_replace("<UP_BOARDURL>", $boardurl, $row['url']);
		$main_link['position'] = $row['position'];
		$context['last_position'] = $main_link['position'];
		$main_link['active'] = $row['active'];	
		$main_link['top_menu'] = $row['top_menu'];	
		$main_link['active'] = $main_link['active'] ? "checked=\"checked\"" : "";
		$main_link['top_menu'] = $main_link['top_menu'] ? "checked=\"checked\"" : "";
		$main_link['activestyle'] = $main_link['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
		$main_link['active_form'] = $main_link['id']."_active";
		$main_link['position_form'] = $main_link['id']."_position";
		$main_link['top_menu_form'] = $main_link['id']."_top_menu";		
	}
	//Position for the new add main link
	$context['last_position'] = !empty($context['last_position']) ? ($context['last_position'] + 1) : 1;
}

function LoadTopMenu()
{
	global $db_prefix, $context, $txt, $settings, $boardurl, $smcFunc, $scripturl;
	global $user_info;
	
	$context['top_menu_view'] = 0;
	$b = 0;
	$myquery = 	$smcFunc['db_query']('',"SELECT id, icon, title, url, position, active, top_menu 
					FROM {db_prefix}ultimate_portal_main_links 
					WHERE top_menu = {int:top_menu}
					ORDER BY position",
					array(
						'top_menu' => 1,
					));
	//url?
	$current_url = ReturnCurrentUrl();
	$active = 0; //only is a flag xD
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		if (!empty($row['top_menu']))
		{
			$context['top_menu_view'] = 1;
			$top_buttons = &$context['top_menu_buttons'][];
			$top_buttons['id'] = $row['id'];
			$top_buttons['title'] = $row['title'];
			$top_buttons['href'] = str_replace("<UP_BOARDURL>", $boardurl, $row['url']);
			$top_buttons['is_last'] = ($b <= $row['position']) ? 1 : 0;
			$top_buttons['active_button'] = false;
			$top_buttons['action'] = str_replace("<UP_BOARDURL>/index.php?action=", '', $row['url']);
			$b = $row['position'];
			
			// Figure out which action we are doing so we can set the active tab.
			// Default to home.
			$explode_url = explode('index.php', $top_buttons['href']);
			if (empty($active))
			{
				//Board or Topic URL?
				if(!empty($_REQUEST['board']) || !empty($_REQUEST['topic']))
				{
					if ($top_buttons['action'] == 'forum')
					{
						$context[$row['id']]['active_button'] = 'active ';						
						$active = 1;
					}	
				}												
				if ($explode_url[0] == $current_url) //Portal Button Active
				{
					$context[$row['id']]['active_button'] = 'active ';									
					$active = 1;	
				}				
				if ($top_buttons['href'] == $current_url)
				{
					$context[$row['id']]['active_button'] = 'active ';									
					$active = 1;	
				}				
				if (($top_buttons['action'] == 'forum' && !empty($_REQUEST['action'])) || ($top_buttons['action'] == 'forum' && isset($_REQUEST['theme'])))
				{
					if(in_array($_REQUEST['action'], array('admin', 'pm', 'profile', 'help', 'moderate', 'search', 'mlist', 'unread', 'unreadreplies', 'recent', 'stats', 'who', 'groups')) || isset($_REQUEST['theme']))
					{
						$context[$row['id']]['active_button'] = 'active ';	
						$active = 1;
					}	
				}
				
				if (!empty($_REQUEST['action']))
				{
					if ($_REQUEST['action'] == strstr($row['url'], $_REQUEST['action']))		
					{
						$context[$row['id']]['active_button'] = 'active ';															
						$active = 1;
					}	
				}	
			}	
		}	
	}
}

//load the custom block
function CustomBlock()
{
	global $settings, $db_prefix, $context, $scripturl, $txt, $smcFunc;

	$context['bkcustom_view'] = 0;
	$myquery = $smcFunc['db_query']('',"
		SELECT id, title, active, icon, personal 
		FROM {db_prefix}ultimate_portal_blocks 
		WHERE personal > {int:personal} 
		ORDER BY active DESC, id",
		array(
			'personal' => 0
		));
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['bkcustom_view'] = 1;		
		$block_custom = &$context['block-custom'][];
		$block_custom['id'] = $row['id'];
		$block_custom['title'] = $row['title'];
		$block_custom['title_link_edit'] = ($row['personal']==1) ? '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';sesc=' . $context['session_id'].'">'. $row['title'] .'</a>' : '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';type-php=created;sesc=' . $context['session_id'].'">'. $row['title'] .'</a>';
		$block_custom['active'] = $row['active'];		
		$block_custom['activestyle'] = $block_custom['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
		$block_custom['permissions'] = '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-perms;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_permission'] .'</a>';
		$block_custom['edit'] = ($row['personal']==1) ? '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>' : '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';type-php=created;sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';
		$block_custom['delete'] = '<a onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-delete;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
				
		//Block type
		switch($row['personal']) {
			case '1': // HTML Block
				$block_custom['type-img'] = '<img alt="HTML" title="HTML" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/bk-html.png"/>';
                break;
			default: // case: 2 - PHP Block
				$block_custom['type-img'] = '<img alt="PHP" title="PHP" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/bk-php.png"/>';
                break;
		}
	}
	
}

//load the System block
function SystemBlock()
{
	global $settings, $db_prefix, $context, $scripturl, $txt, $smcFunc;

	$myquery = $smcFunc['db_query']('',"
		SELECT id, file, title, active, personal 
		FROM {db_prefix}ultimate_portal_blocks 
		WHERE personal = {int:personal} 
		ORDER BY active DESC, id",
		array(
			'personal' => 0
		));
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$block_system = &$context['block-system'][];
		$block_system['id'] = $row['id'];
		$block_system['title'] = $row['title'] . '&nbsp; <strong><em>('. $row['file'] .')</em></strong>';
		$block_system['active'] = $row['active'];		
		$block_system['activestyle'] = $block_system['active'] ? "windowbg" : "windowbg2"; //Active block highlighting
		$block_system['permissions'] = '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-perms;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_permission'] .'</a>';
		$block_system['edit'] = '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';type-php=system;sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';
		$block_system['delete'] = '<a onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-delete;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
		$block_system['type-img'] = '<img alt="PHP" title="PHP" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/bk-php.png"/>';
	}
}

//load the Extra Field from User Post Module
function LoadExtraField()
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $smcFunc;
	
	$condition = ""; //variable declare
	
	if (!empty($ultimateportalSettings['user_posts_field_type_posts']))
		$condition = "WHERE field = 'type'";		

	if (!empty($ultimateportalSettings['user_posts_field_add_language']))
		$condition = "WHERE field = 'lang'";		
	
	if (!empty($ultimateportalSettings['user_posts_field_type_posts']) && !empty($ultimateportalSettings['user_posts_field_add_language']))
		$condition = "";		
		
	$context['view'] = 0;
	
	$myquery = $smcFunc['db_query']('',"SELECT * 
					FROM {$db_prefix}uposts_extra_field 
					". $condition ."
					ORDER BY field, id ASC");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		++$context['view'];
		$extfield = &$context['up-extfield'][];
		$extfield['id'] = $row['id'];
		$extfield['title'] = '<a href="'. $scripturl .'?action=admin;area=user-posts;sa=edit-extra-field;id='. $row['id'] .'">'. $row['title'] .'</a>';
		$extfield['icon'] = '<img alt="" src="'. $row['icon'] .'" width="20" height="20"/>';		
		$extfield['field'] = ($row['field'] == 'type') ? $txt['ultport_admin_extra_field_type'] : $txt['ultport_admin_extra_field_lang'];				
		$extfield['edit'] = '<a href="'. $scripturl .'?action=admin;area=user-posts;sa=edit-extra-field;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';
		$extfield['delete'] = '<a style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=user-posts;sa=del-extra-field;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
	}
	
}

//Load the User Post Table smf_up_user_posts
function LoadUserPostsRows($sql = 'view', $id = 0, $call_filter = 'module')
{
	global $settings, $db_prefix, $context, $scripturl, $txt, $boardurl;
	global $ultimateportalSettings, $memberContext,$smcFunc;

	// Load Language
	if ($call_filter == 'block')
	{
		if (loadlanguage('UPUserPosts') == false)
			loadLanguage('UPUserPosts','english');
	}
	$context['view-userpost'] = 0;

	$condition = "";

	//Prepare the constructPageIndex() function
	$start = (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'user-posts') ? (int) $_REQUEST['start'] : 0;
	$db_count =	$smcFunc['db_query']('',"SELECT count(id)
						FROM {$db_prefix}up_user_posts
						ORDER BY id DESC");
	$numUP = array();
	list($numUP) = $smcFunc['db_fetch_row']($db_count);
	$smcFunc['db_free_result']($db_count);
	
	//Call from Module?
	if ($call_filter == 'module')
	{
		$context['page_index'] = constructPageIndex($scripturl . '?action=user-posts', $start, $numUP, $ultimateportalSettings['user_posts_limit']);
	}
	//Call from Block?
	if ($call_filter == 'block')
	{
		$context['page_index'] = constructPageIndex($scripturl . '?sa=user-posts', $start, $numUP, $ultimateportalSettings['user_posts_limit']);
	}

	// Calculate the fastest way to get the messages!
	$limit = $ultimateportalSettings['user_posts_limit'];
	//End Prepare constructPageIndex() function
	
	if(($sql == 'edit') || ($sql == 'view-single'))
		$condition = "WHERE id = $id";
			
	$myquery = $smcFunc['db_query']('',"
					SELECT id, title, cover, description, link_topic, author, id_member_add, username_add, id_member_updated, username_updated,
					date_add, date_updated, type_post, lang_post 
					FROM {$db_prefix}up_user_posts 
					". $condition ."
					ORDER BY id DESC
					". ($limit < 0 ? "" : "LIMIT $start, $limit "));
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		++$context['view-userpost'];
		$userpost = &$context['userpost'][];
		//Is Edit Form or view a user post in INTERNAL PAGE?
		if(($sql == 'edit') || ($sql == 'view-single'))
		{
			$context['id'] = $row['id'];
			$context['user-post-title'] = $row['title'];
			$context['cover'] = $row['cover'];
		}
		//End
		//load the author information
		if(!empty($row['author']))
		{
			$id_author = LoadID_MEMBER($row['author']);
			loadMemberData($id_author);
			loadMemberContext($id_author);
		}				
		$userpost['id'] = $row['id'];
		$userpost['title'] = $row['title'];
		$userpost['cover'] = $row['cover'];		
		$userpost['cover-img'] = ($ultimateportalSettings['user_posts_cover_view'] == 'advanced' && empty($ultimateportalSettings['user_posts_cover_save_host'])) ? '<img alt="'. $row['title'] .'" src="'. $boardurl .'/up-covers/view.php?url='. $row['cover'] .'" width="250" height="250" />' : '<img alt="'. $row['title'] .'" src="'. $row['cover'] .'" width="250" height="250"/>';		
		$userpost['description'] = $row['description'];		
		$userpost['link_topic'] = $row['link_topic'];				
		$userpost['author'] = $row['author'];				
		if(!empty($id_author))
		{
			$userpost['avatar-author'] = !empty($memberContext[$id_author]['avatar']['image']) ? $memberContext[$id_author]['avatar']['image'] : '<img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/no_avatar.png" width="65" height="65"/>';				
			$userpost['link-author'] = $memberContext[$id_author]['link'];				
		}
		$userpost['id_member_add'] = $row['id_member_add'];						
		$userpost['username_add'] = $row['username_add'];
		$userpost['username_add_link'] = '<a href="'. $scripturl .'?action=profile;u='.$row['id_member_add'].'" target="_blank">'. $row['username_add'] .'</a>';						
		$userpost['date_add'] = timeformat($row['date_add']);								
		//text for added user
		$userpost['added-for'] = $txt['user_posts_added_for'];
		$userpost['added-for'] = str_replace('[MEMBER]', $userpost['username_add_link'], $userpost['added-for']);
		$userpost['added-for'] = str_replace('[DATE]', $userpost['date_add'], $userpost['added-for']);
		
		$userpost['id_member_updated'] = $row['id_member_updated'];						
		$userpost['username_updated'] = $row['username_updated'];						
		$userpost['username_updated_link'] = '<a href="'. $scripturl .'?action=profile;u='.$row['id_member_updated'].'" target="_blank">'. $row['username_updated'] .'</a>';								
		$userpost['date_updated'] = timeformat($row['date_updated']);
		//text for updated user		
		$userpost['updated-for'] = $txt['user_posts_updated_for'];
		$userpost['updated-for'] = str_replace('[UPDATED_MEMBER]', $userpost['username_updated_link'], $userpost['updated-for']);
		$userpost['updated-for'] = str_replace('[UPDATED_DATE]', $userpost['date_updated'], $userpost['updated-for']);		
		//Icon Type and Lang
		if (!empty($row['type_post']))
		{
			$type_posts = explode('#', $row['type_post']);
			$userpost['id_type_post'] = $type_posts[2];
			$userpost['type'] = '<img alt="'. $type_posts[1] .'" title="'. $type_posts[1] .'" src="'. $type_posts[0] .'" width="20" height="20"/>';
			$userpost['type-title'] = $type_posts[1];		
		}
		if (!empty($row['lang_post']))
		{		
			$lang_posts = explode('#', $row['lang_post']);
			$userpost['id_lang_post'] = $lang_posts[2];						
			$userpost['lang'] = '<img alt="'. $lang_posts[1] .'" title="'. $lang_posts[1] .'" src="'. $lang_posts[0] .'" width="20" height="20"/>';
			$userpost['lang-title'] = $lang_posts[1];		
		}
		//End icon Type and Lang
	}
	
	$smcFunc['db_free_result']($myquery);	
	
}

//Load the User Post Cover
function UserPostCoverShow($limit = 10, $width = 150, $height = 150)
{
	global $settings, $db_prefix, $context, $scripturl, $txt, $boardurl;
	global $ultimateportalSettings, $memberContext,$smcFunc;
	
	$context['view_cover'] = 0;

	$myquery = $smcFunc['db_query']('',"
					SELECT id, title, cover, link_topic, author
					FROM {db_prefix}up_user_posts 
					ORDER BY id DESC
					". ($limit < 0 ? "" : "LIMIT $limit "));
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		if(!empty($row['cover']))
		{
			$context['view_cover'] = 1;
			$context['userpost_cover'][$row['id']] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'cover' => $row['cover'],														  
				'cover_img' => ($ultimateportalSettings['user_posts_cover_view'] == 'advanced' && empty($ultimateportalSettings['user_posts_cover_save_host'])) ? '<img alt="'. $row['title'] .'" src="'. $boardurl .'/up-covers/view.php?url='. $row['cover'] .'" width="'. $width .'" height="'. $height .'" />' : '<img alt="'. $row['title'] .'" src="'. $row['cover'] .'" width="'. $width .'" height="'. $height .'" />',
				'link_topic' => $row['link_topic'],				
				'author' => $row['author'],							
			);
		}
	}
	
	$smcFunc['db_free_result']($myquery);	
	
}

//Load ID_MEMBER 
function LoadID_MEMBER($memberName)
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $smcFunc;
		
	$myquery = $smcFunc['db_query']('',"SELECT * 
					FROM {$db_prefix}members
					WHERE member_name = '$memberName'");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$id_member = $row['id_member'];		
	}
	$smcFunc['db_free_result']($myquery);	
	return $id_member;
}

//Load the ExtraField Row
function LoadSelectedLangOrTypePosts($id, $filter = 'title')
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $smcFunc;
		
	$myquery = 	$smcFunc['db_query']('',"SELECT * 
					FROM {$db_prefix}uposts_extra_field 
					WHERE id = $id");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$icon = '<img alt="" title="'. $row['title'] .'" src="'. $row['icon'] .'" width="20" height="20"/>';		
		$title = $row['title'];
	}
	
	$smcFunc['db_free_result']($myquery);	
	
	if ($filter == 'icon')	
		return $icon;

	if ($filter == 'title')	
		return $title;
		
}

//load the Select Type Post for User Post Module
function LoadSelectTypePosts()
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings;
	global $smcFunc;
	
	$context['view-type'] = 0;
	
	$myquery = $smcFunc['db_query']('',"SELECT * 
					FROM {$db_prefix}uposts_extra_field 
					WHERE field ='type'");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		++$context['view-type'];
		$type = &$context['type'][];
		$type['id'] = $row['id'];
		$type['title'] = $row['title'];
		$type['icon'] = $row['icon'];		
		$type['icon-img'] = '<img alt="" src="'. $row['icon'] .'" width="20" height="20"/>';		
	}
	$smcFunc['db_free_result']($myquery);	
}

//load the Select Lang Post for User Post Module
function LoadSelectLangPosts()
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $smcFunc;
	
	$context['view-lang'] = 0;
	
	$myquery = $smcFunc['db_query']('',"SELECT * 
					FROM {$db_prefix}uposts_extra_field 
					WHERE field ='lang'");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		++$context['view-lang'];
		$lang = &$context['lang'][];
		$lang['id'] = $row['id'];
		$lang['title'] = $row['title'];
		$lang['icon'] = $row['icon'];		
		$lang['icon-img'] = '<img alt="" src="'. $row['icon'] .'" width="20" height="20"/>';		
	}
	$smcFunc['db_free_result']($myquery);	
}

//load the News Sections
function LoadNewsSection()
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $smcFunc;

	$context['news_rows'] = 0;
	$context['last_position'] = 0;
	$myquery = $smcFunc['db_query']('',"SELECT id, title, icon, position FROM {$db_prefix}up_news_sections ORDER BY id ASC, position");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		if(!empty($row['id']))
		{
			$context['news_rows'] = 1;		
			$section = &$context['news-section'][];
			$section['id'] = $row['id'];
			$section['title'] = $row['title'];
			$section['icon'] = '<img alt="'.$row['title'].'" src="'. $row['icon'] .'" width="35" height="35" />';		
			$section['position'] = $row['position'];				
			$section['edit'] = '<a href="'. $scripturl .'?action=admin;area=up-news;sa=edit-section;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';
			$section['delete'] = '<a style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=up-news;sa=delete-section;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
			$context['last_position'] =	$section['position'];		
		}
	}
	
	++$context['last_position'];			
}

//Load the News 
function LoadNews()
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings;
	global $smcFunc;
	
	//Prepare the constructPageIndex() function
	$start = (int) $_REQUEST['start'];	
	$db_count = $smcFunc['db_query']('',"SELECT count(id)
						FROM {$db_prefix}up_news
						ORDER BY id DESC");
	$numNews = array();
	list($numNews) = $smcFunc['db_fetch_row']($db_count);
	$smcFunc['db_free_result']($db_count);

	$context['page_index'] = constructPageIndex($scripturl . "?action=admin;area=up-news;sa=admin-news;sesc=" . $context['session_id'], $start, $numNews, $ultimateportalSettings['up_news_limit']);

	// Calculate the fastest way to get the messages!
	$limit = $ultimateportalSettings['up_news_limit'];
	//End Prepare constructPageIndex() function
	
	$myquery = $smcFunc['db_query']('',"SELECT n.id, n.title, n.id_category, n.id_member, n.username, n.body, n.date, s.title as section
						FROM {$db_prefix}up_news n, {$db_prefix}up_news_sections s
						WHERE n.id_category = s.id
						ORDER BY id DESC ". ($limit < 0 ? "" : "LIMIT $start, $limit "));
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		if(!empty($row['id']))
		{
			$context['load_news_admin'] = 1;	
			$news = &$context['news-admin'][];
			$news['id'] = $row['id'];
			$news['title'] = $row['title'];
			$news['title-edit'] = '<a href="'. $scripturl .'?action=admin;area=up-news;sa=edit-news;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $row['title'] .'</a>';
			$news['id_cat'] = $row['id_category'];
			$news['title-section'] = '<a href="'. $scripturl .'?action=admin;area=up-news;sa=edit-section;id='. $row['id_category'] .';sesc=' . $context['session_id'].'">'. $row['section'] .'</a>';						
			$news['id_member'] = $row['id_member'];				
			$news['username'] = $row['username'];								
			$news['body'] = $row['body'];								
			$news['date'] = $row['date'];										
			$news['edit'] = '<a href="'. $scripturl .'?action=admin;area=up-news;sa=edit-news;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';
			$news['delete'] = '<a style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=up-news;sa=delete-news;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
		}
	}
	
}
//Load Block NEWS
function LoadBlockNews()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	global $user_info, $memberContext;
	global $db_prefix, $sourcedir;
	global $smcFunc;
	
	// Load Language
	if (loadlanguage('UPNews') == false)
		loadLanguage('UPNews','english');
	
	//Prepare the constructPageIndex() function
	$start = (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == 'news') ? (int) $_REQUEST['start'] : 0;
	$db_count = $smcFunc['db_query']('',"SELECT count(id)
						FROM {$db_prefix}up_news
						ORDER BY id DESC");
	$numNews = array();
	list($numNews) = $smcFunc['db_fetch_row']($db_count);
	$smcFunc['db_free_result']($db_count);
	
	$context['page_index'] = constructPageIndex($scripturl . '?sa=news', $start, $numNews, $ultimateportalSettings['up_news_limit']);
	
	// Calculate the fastest way to get the messages!
	$limit = $ultimateportalSettings['up_news_limit'];
	//End Prepare constructPageIndex() function
		
	//Load the NEWS
	$query = $smcFunc['db_query']('',"SELECT n.id, n.id_category, n.id_member, n.title, n.username, n.body, n.date, n.id_member_updated, 
					n.username_updated, n.date_updated, s.id AS id_cat, s.title AS title_cat, s.icon
					FROM {$db_prefix}up_news AS n
					LEFT JOIN {$db_prefix}up_news_sections AS s ON(s.id = n.id_category)
					ORDER BY id DESC ". ($limit < 0 ? "" : "LIMIT $start, $limit "));
	
	while($row2 = $smcFunc['db_fetch_assoc']($query))
	{	
		$user = $row2['id_member'];
		//load the member information
		if(!empty($row2['id_member']))
		{
			loadMemberData($user);
			loadMemberContext($user);
		}
		$news = &$context['news'][];
		$news['id'] = $row2['id'];
		$news['title'] = '<a href="'. $scripturl .'?action=news;sa=view-new;id='. $row2['id'] .'">'. stripslashes($row2['title']) .'</a>';
		$news['id_member'] = $row2['id_member'];
		$news['username'] = $row2['username'];
		$news['author'] = '<a href="'. $scripturl .'?action=profile;u='. $row2['id_member'] .'">'. stripslashes($row2['username']) .'</a>';
		$news['avatar'] = $memberContext[$user]['avatar']['image'];
		$news['date'] = timeformat($row2['date']);		
		$news['added-news'] = $txt['up_module_news_added_portal_for'];
		$news['added-news'] = str_replace('[MEMBER]', $news['author'], $news['added-news']);
		$news['added-news'] = str_replace('[DATE]', $news['date'], $news['added-news']);
		$news['body'] = stripslashes($row2['body']);
		$news['id_member_updated'] = !empty($row2['id_member_updated']) ? $row2['id_member_updated'] : '';		
		$news['username_updated'] = !empty($row2['username_updated']) ? $row2['username_updated'] : '';				
		$news['author_updated'] = '<a href="'. $scripturl .'?action=profile;u='. $row2['id_member_updated'] .'">'. stripslashes($row2['username_updated']) .'</a>';		
		$news['date_updated'] = !empty($row2['date_updated']) ? timeformat($row2['date_updated']) : '';						
		$news['updated-news'] = !empty($news['id_member_updated']) ? $txt['up_module_news_updated_for'] : '';		
		$news['updated-news'] = str_replace('[UPDATED_MEMBER]', $news['author_updated'], $news['updated-news']);
		$news['updated-news'] = str_replace('[UPDATED_DATE]', $news['date_updated'], $news['updated-news']);
		$news['view'] = '<img style="vertical-align: middle;" border="0" alt="'. $txt['ultport_button_view'] .'" src="'.$settings['default_images_url'].'/ultimate-portal/view.png" />&nbsp;<a href="'. $scripturl .'?action=news;sa=view-new;id='. $row2['id'] .'">'. $txt['ultport_button_view'] .'</a>';		
		$news['edit'] = '<img style="vertical-align: middle;" alt="'. $txt['ultport_button_edit'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png" />&nbsp;<a href="'. $scripturl .'?action=news;sa=edit-new;id='. $row2['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';		
		$news['delete'] = '<img style="vertical-align: middle;" border="0" alt="'. $txt['ultport_button_delete'] .'" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png" />&nbsp;<a onclick="return makesurelink()" href="'. $scripturl .'?action=news;sa=delete-new;id='. $row2['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';				
		$news['id_cat'] = $row2['id_cat'];				
		$news['title_cat'] = $row2['title_cat'];				
		$news['icon'] = $row2['icon'];						
	}
	$smcFunc['db_free_result']($query);
}	
//addslashes before inserting data into database - Portal CP
function up_convert_savedbadmin($t="")
{
	$t = addslashes($t);
	//$t = get_magic_quotes_gpc() ? $t : addslashes($t);
	return $t;
}

function up_db_xss($value)
{
	global $smcFunc;
	return $smcFunc['htmlspecialchars']($value,ENT_QUOTES);
}

//update the blocks perms
function up_update_block_perms($id)
{
	global $db_prefix, $context;
	global $smcFunc;
	
	$permissionsArray = array();
	if (isset($_POST['perms']))
	{
		foreach ($_POST['perms'] as $rgroup)
			$permissionsArray[] = (int) $rgroup;
	}
	$finalPermissions = implode(",",$permissionsArray);

	//Now UPDATE the Ultimate portal Blocks			
	$smcFunc['db_query']('',"UPDATE {$db_prefix}ultimate_portal_blocks 
			SET	perms = '$finalPermissions' 
			WHERE id = '$id'");
	
	//redirect the Blocks Admin
	redirectexit('action=admin;area=ultimate_portal_blocks;sa=admin-block;sesc=' . $context['session_id']);
}

//save the Cover in the folder up-cover
function save_cover_in_folder($image)
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info, $ultimateportalSettings, $boarddir,$boardurl;

	//Extract Filename and Extension
    $image_path = parse_url($image);
    $img_parts = pathinfo($image_path['path']); 
    $filename = $img_parts['filename'];
    $img_ext = $img_parts['extension'];
	//End Extract
    $path = $boarddir .'/up-covers/';
	//Image extension is valid?
	$ext_valid = false;
	//Image extension? any image extension (jpg, png, gif, bmp), convert to JPG 
	$newwidth = 324; //new width - nuevo ancho 
	$newheight = 465; //new height - nuevo alto				
	if ($img_ext == 'gif')
	{
		$ext_valid = true;
		$i=imagecreatefromgif($image);
		//resize the image
		$width = imagesx($i); //original width - ancho original
		$height = imagesy($i); //original height - alto original
		$im_destiny = imagecreatetruecolor($newwidth, $newheight);
		//Now Resize - Library GD RULES :P
		imagecopyresampled($im_destiny, $i, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);	
	}
	if ($img_ext == 'jpg' || $img_ext == 'jpeg')
	{
		$ext_valid = true;		
		$i=imagecreatefromjpeg($image);
		//resize the image
		$width = imagesx($i); //original width - ancho original
		$height = imagesy($i); //original height - alto original
		$im_destiny = imagecreatetruecolor($newwidth, $newheight);
		//Now Resize - Library GD RULES :P
		imagecopyresampled($im_destiny, $i, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);		
	}	
	if ($img_ext == 'png')
	{
		$ext_valid = true;		
		$i=imagecreatefrompng($image);
		//resize the image
		$width = imagesx($i); //original width - ancho original
		$height = imagesy($i); //original height - alto original
		$im_destiny = imagecreatetruecolor($newwidth, $newheight);
		//Now Resize - Library GD RULES :P
		imagecopyresampled($im_destiny, $i, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);		
	}
	//Extension valid?
	if ($ext_valid === false)
		fatal_lang_error('ultport_no_extension_image',false);
	if ($ultimateportalSettings['user_posts_cover_view'] == 'advanced')
	{
		//image copy in BOX DVD image
		$box = imagecreatefrompng($settings['default_images_url'] . '/ultimate-portal/up-box.png');
		imagecopymerge($box, $im_destiny, 114/*horizontal position*/, 17/*vertical position*/, 0, 0, $newwidth, $newheight, 75);		
		//Watermark?
		if (!empty($ultimateportalSettings['ultimate_portal_cover_watermark']))
		{
			// create colors - default is "black"
			$color = imagecolorallocate($box, 0, 0, 0);
			//WaterMark?
			$watermark = $ultimateportalSettings['ultimate_portal_cover_watermark'];
			//Fonts TTF
			$fonts = $boarddir . '/Themes/default/fonts/Forgottb.ttf';
			//Write text
			imagettftext($box, 12/*size letter*/, 90/*sense*/, 100 /*horizontal direction*/ , 480 /*vertical direction*/ , $color, $fonts, $watermark);
		}
		//PNG Transparency
		imagealphablending($box, true);
		imagesavealpha($box, true);	
		//Ok, Now save the new image on the "up-covers" folder	
		imagepng($box,($path . $filename . ".png"));					
		imagedestroy($i);
		imagedestroy($box);		
	}
	if ($ultimateportalSettings['user_posts_cover_view'] == 'normal')
	{
		//PNG Transparency
		imagealphablending($im_destiny, true);
		imagesavealpha($im_destiny, true);			
		//Ok, Now save the new image on the "up-covers" folder	
		imagepng($im_destiny,($path . $filename . ".png"));							
		imagedestroy($i);
	}

	//Return the new image url
	$new_image_url = $boardurl . '/up-covers/'. $filename . ".png";
	return $new_image_url;
}

//Load Image from Ultimate Portal image folder
function load_image_folder($folder = " ", $width = 'width="16"', $height = 'height="16"')
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $user_info, $ultimateportalSettings, $boarddir,$boardurl;

   //extension
   $arr_ext=array("jpg","png","gif");
   //open folder dir
   $mydir=opendir($boarddir . "/Themes/default/images/ultimate-portal". $folder);
   //read files
	while($files=readdir($mydir))
	{
		$ext=substr($files,-3);
		$ext_selected = '.'.$ext;
		//si la extension del archivo es correcta muestra la imagen
		if(in_array($ext,$arr_ext) && ($ultimateportalSettings['ultimate_portal_icons_extention'] == $ext_selected))
		{
			$context['folder_images'][] = array(
				'file' => $files,
				'value' => str_replace('.'.$ext, "", $files),
				'image' => '<img '. $width .' '. $height .'src="'. $settings['default_images_url'] . '/ultimate-portal'. $folder . '/'. $files .'" alt="'. $files .'" title="'. $files .'" />',
			);
		}
	}
}

//Load the download Section Table smf_up_download_sections
function LoadDownloadSection($sql = 'view', $id = 0, $origin = 'admin')
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $memberContext, $user_info;
	global $smcFunc;
		
	$context['view'] = 0;

	$condition = "";

	if($sql == 'edit')
		$condition = "WHERE id = $id";

	if($origin == 'down_stats')
		$condition = "ORDER BY total_files DESC";
			
	$myquery = $smcFunc['db_query']('',"SELECT * 
					FROM {$db_prefix}up_download_sections 
					". $condition ."");
					
	$max_num_posts = 1;				
	
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$perms = '';
		$perms = array();
		if (!empty($row['id_groups'])) {
			$perms =  $row['id_groups'];
		}else{
			$perms = 1; //only admin can see
		}
		
		if(!$perms) {
			$perms = array();
		}
		$perms = !empty($perms) ? explode(',', $perms) : 1;//1 = only admin can see		
		$viewsection = false;		
		//Can user view this section?
		if(!empty($perms))
		{
			foreach($user_info['groups'] as $group_id) 
				if(in_array($group_id, $perms)) 
				{
					$viewsection = true;
				}
		}
		
		if ($viewsection === true || $user_info['is_admin'])
		{
			++$context['view'];
			$dowsect = &$context['dowsect'][];
			$dowsect['id'] = $row['id'];
			$dowsect['title'] = $row['title'];
			$dowsect['description'] = parse_bbc($row['description']);		
			$dowsect['description-original'] = $row['description'];
			$context['description-original'] = $row['description'];
			$dowsect['icon'] = $row['icon'];
			$dowsect['icon-img'] = '<img style="vertical-align: middle;" alt="'. $row['title'] .'" src="'. $row['icon'] .'" width="30" height="30" />';		
			$dowsect['id_groups'] = $row['id_groups'];		
			$dowsect['total_files'] = $row['total_files'];				
			$dowsect['edit'] = '<a href="'. $scripturl .'?action=admin;area=download;sa=edit;id='. $row['id'] .';sesc=' . $context['session_id'].'"><img src="'. $settings['default_images_url'] . '/ultimate-portal/edit.png"  alt="'. $txt['ultport_button_edit'] .'" title="'. $txt['ultport_button_edit'] .'"/></a>';
			$dowsect['delete'] = '<a  style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=download;sa=delete;id='. $row['id'] .';sesc=' . $context['session_id'].'"><img src="'. $settings['default_images_url'] . '/ultimate-portal/delete.png" alt="'. $txt['ultport_button_delete'] .'" title="'. $txt['ultport_button_delete'] .'"/></a>';

			if ($max_num_posts < $row['total_files'])
				$max_num_posts = $row['total_files'];
				
			$dowsect['total_percent'] = round(($row['total_files'] * 100) / $max_num_posts);
			$dowsect['id_board'] = $row['id_board'];
		}
	}	
}

//Load the download Section Table smf_up_download_sections
function SpecificSection($id)
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $memberContext, $user_info;
	global $smcFunc;

	$myquery = $smcFunc['db_query']('',"SELECT * 
					FROM {$db_prefix}up_download_sections 
					WHERE id = $id");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$perms = '';
		$perms = array();
		if (!empty($row['id_groups'])) {
			$perms =  $row['id_groups'];
		}else{
			$perms = 1; // only admin can see
		}
		
		if(!$perms) {
			$perms = array();
		}
		$perms = !empty($perms) ? explode(',', $perms) : 1;//only admin can see		
		$context['canview'] = 0;
		if(!empty($perms))
		{
			//Can user view this section?
			foreach($user_info['groups'] as $group_id) 
				if(in_array($group_id, $perms)) {
					$context['canview'] = 1;
				}
		}
		if ($user_info['is_admin'])	
			$context['canview'] = 1;
			
		$context['id'] = $id;
		$context['title'] = $row['title'];
		$context['description'] = parse_bbc($row['description']);		
		$context['description-original'] = $row['description'];				
		$context['icon'] = $row['icon'];
		$context['icon-img'] = '<img style="float:left" alt="'. $row['title'] .'" src="'. $row['icon'] .'" width="30" height="30"/>';		
		$context['id_groups'] = $row['id_groups'];		
		$context['id_board'] = $row['id_board'];		
		$context['total_files'] = $row['total_files'];				
		$context['edit'] = '<a href="'. $scripturl .'?action=admin;area=download;sa=edit;id='. $row['id'] .'"><img src="'. $settings['default_images_url'] . '/ultimate-portal/edit.png"  alt="'. $txt['ultport_button_edit'] .'" title="'. $txt['ultport_button_edit'] .'"/></a>';
		$context['delete'] = '<a  style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=download;sa=delete;id='. $row['id'] .'"><img src="'. $settings['default_images_url'] . '/ultimate-portal/delete.png" alt="'. $txt['ultport_button_delete'] .'" title="'. $txt['ultport_button_delete'] .'"/></a>';						
	}
}

//Updated total files for section
function UpdatedSectionTotalFiles($id_section)
{
	global $db_prefix;
	global $smcFunc;

	$id_section = (int) $id_section;
	
	//Now update 
	$smcFunc['db_query']('',"UPDATE {$db_prefix}up_download_sections 
			SET 
				total_files = total_files + 1
			WHERE id = $id_section");
	//End Update		
}

//Updated total files for section
function SubstractSectionTotalFiles($id_section)
{
	global $db_prefix;
	global $smcFunc;
	
	//Now update 
	$smcFunc['db_query']('',"UPDATE {$db_prefix}up_download_sections 
			SET 
				total_files = total_files - 1
			WHERE id = $id_section");
	//End Update		
}

//Load the Download Search
function DownloadSearchResult($filter, $filter2)
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc;
	
	if (!empty($_REQUEST['type']) && empty($_REQUEST['basic_search']))
		$filter = 'section';
	if (!empty($_REQUEST['basic_search']) && empty($_REQUEST['type']))
		$filter = 'basic_search';
	if (empty($_REQUEST['type']) && empty($_REQUEST['basic_search']))
		$filter = 'search';
		
	if($filter == 'section')
	{
		$id_section = $smcFunc['db_escape_string']($_REQUEST['type']);
		$id_section = (int) $id_section;
		$condition = "WHERE id_section = $id_section";
		$page_index = ';type='. $id_section;
	}	

	if($filter == 'search')
	{
		$search = $filter2;
		$condition = "WHERE (title like '%". $search. "%') OR (small_description like '%". $search. "%')";
		$page_index = ';basic_search='. $search;		
		$context['whatsearch'] = $search;
	}

	if($filter == 'basic_search')
	{
		$search = $filter2;
		$condition = "WHERE (title like '%". $search. "%') OR (small_description like '%". $search. "%')";
		$page_index = ';basic_search='. $search;		
		$context['whatsearch'] = $search;
	}

	//Prepare the constructPageIndex() function
	$start = (int) $_REQUEST['start'];
	$db_count = $smcFunc['db_query']('',"SELECT count(id_files)
						FROM {$db_prefix}up_download_files
						". $condition ."
						ORDER BY approved, title ASC");
	$num = array();
	list($num) = $smcFunc['db_fetch_row']($db_count);
	$smcFunc['db_free_result']($db_count);

	$context['page_index'] = constructPageIndex($scripturl . '?action=downloads;sa=search'. $page_index, $start, $num, $ultimateportalSettings['download_file_limit_page']);

	// Calculate the fastest way to get the messages!
	$limit = $ultimateportalSettings['download_file_limit_page'];
	//End Prepare constructPageIndex() function

	$context['view-downsearch'] = 0;
	$myquery = $smcFunc['db_query']('',"SELECT * 
					FROM {db_prefix}up_download_files
					". $condition ." 
					ORDER BY approved, title ASC
					". ($limit < 0 ? "" : "LIMIT $start, $limit "));
	while( $row = $smcFunc['db_fetch_assoc']($myquery) )
	{	
		//Load Specific ID
		SpecificSection($row['id_section']);
		//End	
		$downsearch = &$context['downsearch'][];	
		if ($user_info['is_admin'] || !empty($row['approved']) || !empty($user_info['up-modules-permissions']['download_moderate'])){
			$downsearch['can_view'] = true;
		}
		if(empty($row['approved']) && !$user_info['is_admin'] && empty($user_info['up-modules-permissions']['download_moderate']))
		{
			$downsearch['can_view'] = (!$user_info['is_guest'] && $user_info['id'] == $row['id_member']) ? true : false;
		}
		
		if ($downsearch['can_view'] === true && !empty($context['canview']))
		{
			$context['view-downsearch'] = 1;
			$downsearch['id_files'] = $row['id_files'];
			$downsearch['title'] = $row['title'];
			$downsearch['id_member'] = $row['id_member'];
			$downsearch['membername'] = $row['membername'];
			$downsearch['small_description'] = $row['small_description'];
			$downsearch['date_created'] = timeformat($row['date_created']);
			$downsearch['date_updated'] = !empty($row['date_updated']) ? timeformat($row['date_updated']) : '-';
			$downsearch['total_downloads'] = $row['total_downloads'];		
			$downsearch['approved'] = $row['approved'];			
		}
	}
				
}

//Load the Unapproved Files
function ViewUnapprovedFiles()
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc;
	
	//Prepare the constructPageIndex() function
	$start = (int) $_REQUEST['start'];
	$db_count = $smcFunc['db_query']('',"SELECT count(id_files)
						FROM {$db_prefix}up_download_files
						WHERE approved <> 1
						ORDER BY date_updated DESC");
	$num = array();
	list($num) = $smcFunc['db_fetch_row']($db_count);
	$smcFunc['db_free_result']($db_count);

	$context['page_index'] = constructPageIndex($scripturl . '?action=downloads;sa=unapproved', $start, $num, $ultimateportalSettings['download_file_limit_page']);

	// Calculate the fastest way to get the messages!
	$limit = $ultimateportalSettings['download_file_limit_page'];
	//End Prepare constructPageIndex() function

	$context['view-downsearch'] = 0;
	$myquery = $smcFunc['db_query']('',"SELECT * 
					FROM {db_prefix}up_download_files
					WHERE approved <> 1
					ORDER BY date_updated DESC
					". ($limit < 0 ? "" : "LIMIT $start, $limit "));
	while( $row = $smcFunc['db_fetch_assoc']($myquery) )
	{	
		//Load Specific ID
		SpecificSection($row['id_section']);
		//End	
		$downsearch = &$context['downsearch'][];	
		if ($user_info['is_admin'] || !empty($row['approved']) || !empty($user_info['up-modules-permissions']['download_moderate'])){
			$downsearch['can_view'] = true;
		}
		if(empty($row['approved']) && !$user_info['is_admin'] && empty($user_info['up-modules-permissions']['download_moderate']))
		{
			$downsearch['can_view'] = (!$user_info['is_guest'] && $user_info['id'] == $row['id_member']) ? true : false;
		}
		
		if ($downsearch['can_view'] === true && !empty($context['canview']))
		{
			$context['view-downsearch'] = 1;
			$downsearch['id_files'] = $row['id_files'];
			$downsearch['title'] = $row['title'];
			$downsearch['id_member'] = $row['id_member'];
			$downsearch['membername'] = $row['membername'];
			$downsearch['small_description'] = $row['small_description'];
			$downsearch['date_created'] = timeformat($row['date_created']);
			$downsearch['date_updated'] = !empty($row['date_updated']) ? timeformat($row['date_updated']) : '-';
			$downsearch['total_downloads'] = $row['total_downloads'];		
			$downsearch['approved'] = $row['approved'];			
		}
	}
				
}

//Load File when have condition
function LoadFileInformationRows($condition, $filter)
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc;
	
	$context['view-file-rows-'.$filter] = 0;
	$myquery = $smcFunc['db_query']('',"
					SELECT f.id_files, f.title,	f.description, f.id_member,	
					f.membername, f.small_description, f.id_section, f.date_created, 
					f.date_updated, f.total_downloads, f.approved, f.id_topic, s.id, s.id_groups 
					FROM {db_prefix}up_download_files f, {db_prefix}up_download_sections s
					". $condition ."");
	$max_num_files = 1;				
	while( $row = $smcFunc['db_fetch_assoc']($myquery) )
	{
		//View Perms
		$perms = '';
		$perms = array();
		if ($row['id_groups']) {
			$perms =  $row['id_groups'];
		}
		
		if(!$perms) {
			$perms = array();
		}
		$perms = explode(',', $perms);		
		$file['can_view-'.$filter] = false;		
		//Can user view this files?
		foreach($user_info['groups'] as $group_id) 
			if(in_array($group_id, $perms)) {
				$file['can_view-'.$filter] = true;
			}
		//End
		if ($user_info['is_admin']){
			$file['can_view-'.$filter] = true;
		}		
		if ($file['can_view-'.$filter] === true)
		{
			++$context['view-file-rows-'.$filter];
			$file = &$context['file-'.$filter][];
			$file['id_files-'.$filter] = $row['id_files'];
			$file['title-'.$filter] = $row['title'];
			$file['id_member-'.$filter] = $row['id_member'];
			$file['membername-'.$filter] = $row['membername'];
			$file['small_description-'.$filter] = $row['small_description'];
			$file['date_created-'.$filter] = timeformat($row['date_created']);
			$file['date_updated-'.$filter] = !empty($row['date_updated']) ? timeformat($row['date_updated']) : '-';
			$file['total_downloads-'.$filter] = $row['total_downloads'];		
			$file['approved-'.$filter] = $row['approved'];
			//Load Specific ID
			SpecificSection($row['id_section']);
			//End	
			
			if ($max_num_files < $row['total_downloads'])
				$max_num_files = $row['total_downloads'];
	
			$file['percent_downloads-'.$filter] = round(($row['total_downloads'] * 100) / $max_num_files);
		}
	}
}
//Load Specific Files Information?
function LoadSpecificFileInformation($id_files)
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc;
	
	$context['view-files'] = 0;
	$context['perm_view'] = 0;					
	$myquery = $smcFunc['db_query']('',"
					SELECT f.id_files, f.title,	f.description, f.id_member,	
					f.membername, f.small_description, f.id_section, f.date_created, 
					f.date_updated, f.total_downloads, f.approved, f.id_topic, s.id, s.id_groups 
					FROM {$db_prefix}up_download_files f, {$db_prefix}up_download_sections s
					WHERE f.id_files = $id_files
					LIMIT 1");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) )
	{
		$context['view-files'] = 1;
		$context['id_files'] = $row['id_files'];
		$context['filetitle'] = $row['title'];
		$context['id_member'] = $row['id_member'];
		$context['membername'] = $row['membername'];
		$context['id_section'] = $row['id_section'];
		$context['small_description'] = $row['small_description'];
		$context['filedescription'] = parse_bbc($row['description']);		
		$context['file_description_original'] = $row['description'];		
		$context['date_created'] = timeformat($row['date_created']);
		$context['date_updated'] = !empty($row['date_updated']) ? timeformat($row['date_updated']) : '-';
		$context['filetotal_downloads'] = !empty($row['total_downloads']) ? $row['total_downloads'] : 0;		
		$context['approved'] = $row['approved'];
		$context['id_topic'] = $row['id_topic'];

		//Load Especific ID
		SpecificSection($context['id_section']);
		//End

		if ($user_info['is_admin'] || !empty($user_info['up-modules-permissions']['download_moderate'])){
			$context['can_view'] = 1;
			$context['perm_view'] = 1;		
		}
		if(empty($row['approved']) && !$user_info['is_admin'] && !empty($context['canview']) && empty($user_info['up-modules-permissions']['download_moderate']))
		{
			$context['can_view'] = (!$user_info['is_guest'] && $user_info['id'] == $row['id_member']) ? 1 : 0;
			$context['perm_view'] = (!$user_info['is_guest'] && $user_info['id'] == $row['id_member']) ? 1 : 0;	
		}
		if(!empty($row['approved']) && !$user_info['is_admin'] && !empty($context['canview']))		
		{
			$context['can_view'] = 1;
			$context['perm_view'] = 1;	
		}			
	}
				
}

//Load Specific Files Attachments?
function LoadSpecificFileAttachemnt($id_files)
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc;

	/*
		Attachments Type
		----------------
			1 => no image.... zip, tar.gz, php, etcetera...
			2 => full image
			3 => thumbnail
	*/
	$context['view-attachments'] = 0;
	$myquery = $smcFunc['db_query']('',"SELECT * 
					FROM {$db_prefix}up_download_attachments
					WHERE id_files = $id_files");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) )
	{
		if ($row['attachmentType'] == 1)
		{
			$context['view-attachments']=1;
			$attachment = &$context['attachment'][];
			$attachment['ID_ATTACH'] = $row['ID_ATTACH'];
			$attachment['attachmentType'] = $row['attachmentType'];
			$attachment['filename'] = $row['filename'];
			$attachment['file_hash'] = trim($row['file_hash']);
			$attachment['size'] = round(($row['size'] / 1024),1);
			$attachment['downloads'] = !empty($row['downloads']) ? $row['downloads'] : 0;
		}
	}
				
}

//Load Specific Files Attachments?
function LoadSpecificImageAttachemnt($id_files)
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc;

	/*
		Attachments Type
		----------------
			1 => no image.... zip, tar.gz, php, etcetera...
			2 => full image
			3 => thumbnail
	*/
	$context['view_attach_image'] = 0;
	$myquery = $smcFunc['db_query']('',"SELECT * 
					FROM {$db_prefix}up_download_attachments
					WHERE id_files = $id_files
					ORDER BY ID_ATTACH ASC");
	while( $row = $smcFunc['db_fetch_assoc']($myquery) )
	{
		if($row['attachmentType'] == 2)
		{
			$context['view_attach_image'] = 1;
			$context['full_image'][$row['ID_ATTACH']] = array(
				'ID_ATTACH' => $row['ID_ATTACH'],
				'ID_THUMB' => $row['ID_THUMB'],
				'attachmentType' => $row['attachmentType'],
				'filename' => $row['filename'],
				'width' => $row['width'],
				'height' => $row['height'],
				'mime_type' => $row['mime_type'],
				'fileext' => $row['fileext'],
				'thumbnail' => array(),
			);	
			$sqlthumb = $smcFunc['db_query']('',"SELECT * 
							FROM {$db_prefix}up_download_attachments
							WHERE ID_ATTACH = ". $row['ID_THUMB'] ."
							LIMIT 1");
			while( $rowthumb = $smcFunc['db_fetch_assoc']($sqlthumb) )
			{
				$context['full_image'][$row['ID_ATTACH']]['thumbnail'][$rowthumb['ID_ATTACH']] = array(
					'ID_ATTACH' => $rowthumb['ID_ATTACH'],
					'attachmentType' => $rowthumb['attachmentType'],
					'filename' => $rowthumb['filename'],
					'width' => $rowthumb['width'],
					'height' => $rowthumb['height'],
					'mime_type' => $rowthumb['mime_type'],
					'fileext' => $rowthumb['fileext'],
				);					
			}
		}
	}
				
}

//Delete Specific Attachment?
function DeleteAttach($ID_ATTACH)
{
	global $settings, $db_prefix, $context, $scripturl, $txt;
	global $ultimateportalSettings, $user_info;
	global $smcFunc;
	
	//Ok, delete 
	$smcFunc['db_query']('',"DELETE FROM {$db_prefix}up_download_attachments
			WHERE ID_ATTACH = $ID_ATTACH");
	//End	

}

//Sum 1 the downloads field
function UpdatedFilesDownloads($ID_ATTACH)
{
	global $db_prefix;
	global $smcFunc;
	
	//Now update 
	$smcFunc['db_query']('',"UPDATE {$db_prefix}up_download_attachments 
			SET 
				downloads = downloads + 1
			WHERE ID_ATTACH = $ID_ATTACH");
	//End Update		
}

//Sum 1 the TOTAL downloads field in Downloads Files tables
function UpdatedTotalDownloadFile($id_files)
{

	global $smcFunc;
	
	$id_files = (int) $id_files;
	if(!empty($id_files))
	$smcFunc['db_query']('','
			UPDATE {db_prefix}up_download_files 
			SET total_downloads = total_downloads + 1
			WHERE id_files = {int:id_files}',
			array(
				'id_files' => $id_files,
			)		
	);
	//End Update		
}

//Add extra headers from index.template.php
function extra_context_html_headers()
{
	global $context, $settings, $txt;
	
	$context['html_headers'] .= "
		<script language=\"JavaScript\" type=\"text/javascript\"><!-- // --><![CDATA[
		var i = 2;
		function addAttachment()
		{
			setOuterHTML(document.getElementById(\"addAttachments\"), '<br /><input type=\"file\" size=\"48\" name=\"file_'+i+'\" /><span id=\"addAttachments\"></span><br/>');
			i++;
		}
	// ]]></script>";

}

//Add extra headers from index.template.php
function context_html_headers()
{
	global $context, $settings;

	$context['html_headers'] .= '		
		<!-- TinyMCE -->
		<script type="text/javascript" src="'. $settings['default_theme_url'] .'/up-editor/jscripts/tiny_mce/tiny_mce.js"></script>
		<script type="text/javascript">
			tinyMCE.init({
				// General options
				mode : "textareas",
                width : "350",
				theme : "advanced",
				plugins : "safari,layer,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,xhtmlxtras",
				extended_valid_elements : "iframe[src|width|height|name|align]",
				// Theme options
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontselect,fontsizeselect",
				theme_advanced_buttons2 : "bullist,numlist,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,preview,|,forecolor,backcolor",
				theme_advanced_buttons3 : "hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
				theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				language : "es",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,
		
				// Example content CSS (should be your site CSS)
				content_css : "'. $settings['default_theme_url'] .'/up-editor/examples/css/content.css",
		
				// Drop lists for link/image/media/template dialogs
				template_external_list_url : "'. $settings['default_theme_url'] .'/up-editor/examples/lists/template_list.js",
				external_link_list_url : "'. $settings['default_theme_url'] .'/up-editor/examples/lists/link_list.js",
				media_external_list_url : "'. $settings['default_theme_url'] .'/up-editor/examples/lists/media_list.js",
		
				// Replace values for the template plugin
				template_replace_values : {
					username : "Some User",
					staffid : "991234"
				}
			});
		</script>
		<!-- /TinyMCE -->	
	';

}

//Load the Ultimate Portal Settings
function ultimateportalSettings()
{
	global $ultimateportalSettings;
	global $db_prefix;
	global $smcFunc;	
	global $sourcedir, $context, $boardurl, $boarddir,$mbname;
	$ultimateportalSettings = array();
	
	if (($ultimateportalSettings = cache_get_data('ultimateportalSettings', 480)) == null)
	{
		$request = $smcFunc['db_query']('',"
			SELECT *
			FROM {db_prefix}ultimate_portal_settings");

		$ultimateportalSettings = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))		
			$ultimateportalSettings[$row['variable']] = $row['value'];
		
		$smcFunc['db_free_result']($request);
		cache_put_data('ultimateportalSettings', $ultimateportalSettings, 900);
	}
	//Add extra value - The UltimatePortal Version
	$ultimateportalSettings['ultimate_portal_version'] = '0.4';
}

// Prints a post box.  Used everywhere you post or send.
function up_theme_postbox($msg, $post_box_name, $post_form)
{
	global $txt, $modSettings, $options, $smcFunc;
	global $context, $settings, $user_info, $sourcedir, $scripturl;

	$post = up_template_control_richedit($post_box_name,'smileyBox_'.$post_box_name, 'bbcBox_'.$post_box_name);

	return $post;

}

// Creates a box that can be used for richedit stuff like BBC, Smileys etc.
function up_create_control_richedit($editorOptions)
{
	global $txt, $modSettings, $options, $smcFunc;
	global $context, $settings, $user_info, $sourcedir, $scripturl;

	require_once($sourcedir . '/Subs-Editor.php');
	// Load the Post language file... for the moment at least.
	loadLanguage('Post');

	// Every control must have a ID!
	assert(isset($editorOptions['id']));
	assert(isset($editorOptions['value']));

	// Is this the first richedit - if so we need to ensure some template stuff is initialised.
	if (empty($context['controls']['richedit']))
	{
		// Some general stuff.
		$settings['smileys_url'] = $modSettings['smileys_url'] . '/' . $user_info['smiley_set'];

		// This really has some WYSIWYG stuff.
		loadTemplate('GenericControls', $context['browser']['is_ie'] ? 'editor_ie' : 'editor');
		$context['html_headers'] .= '
		<script type="text/javascript"><!-- // --><![CDATA[
			var smf_smileys_url = \'' . $settings['smileys_url'] . '\';
			var oEditorStrings= {
				wont_work: \'' . addcslashes($txt['rich_edit_wont_work'], "'") . '\',
				func_disabled: \'' . addcslashes($txt['rich_edit_function_disabled'], "'") . '\',
				prompt_text_email: \'' . addcslashes($txt['prompt_text_email'], "'") . '\',
				prompt_text_ftp: \'' . addcslashes($txt['prompt_text_ftp'], "'") . '\',
				prompt_text_url: \'' . addcslashes($txt['prompt_text_url'], "'") . '\',
				prompt_text_img: \'' . addcslashes($txt['prompt_text_img'], "'") . '\'
			}
		// ]]></script>
		<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/editor.js"></script>';

		$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');
		if ($context['show_spellchecking'])
		{
			$context['html_headers'] .= '
				<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';

			// Some hidden information is needed in order to make the spell checking work.
			if (!isset($_REQUEST['xml']))
				$context['insert_after_template'] .= '
				<form name="spell_form" id="spell_form" method="post" accept-charset="' . $context['character_set'] . '" target="spellWindow" action="' . $scripturl . '?action=spellcheck">
					<input type="hidden" name="spellstring" value="" />
				</form>';

			// Also make sure that spell check works with rich edit.
			$context['html_headers'] .= '
				<script type="text/javascript"><!-- // --><![CDATA[
				function spellCheckDone()
				{
					for (i = 0; i < smf_editorArray.length; i++)
						setTimeout("smf_editorArray[" + i + "].spellCheckEnd()", 150);
				}
				// ]]></script>';
		}
	}

	// Start off the editor...
	$context['controls']['richedit'][$editorOptions['id']] = array(
		'id' => $editorOptions['id'],
		'value' => $editorOptions['value'],
		'rich_value' => addcslashes(bbc_to_html($editorOptions['value']), "'"),
		'rich_active' => empty($modSettings['disable_wysiwyg']) && (!empty($options['wysiwyg_default']) || !empty($editorOptions['force_rich']) || !empty($_REQUEST[$editorOptions['id'] . '_mode'])),
		'disable_smiley_box' => !empty($editorOptions['disable_smiley_box']),
		'columns' => isset($editorOptions['columns']) ? $editorOptions['columns'] : 60,
		'rows' => isset($editorOptions['rows']) ? $editorOptions['rows'] : 12,
		'width' => isset($editorOptions['width']) ? $editorOptions['width'] : '100%',
		'height' => isset($editorOptions['height']) ? $editorOptions['height'] : '150px',
		'form' => isset($editorOptions['form']) ? $editorOptions['form'] : 'postmodify',
		'bbc_level' => !empty($editorOptions['bbc_level']) ? $editorOptions['bbc_level'] : 'full',
		'preview_type' => isset($editorOptions['preview_type']) ? (int) $editorOptions['preview_type'] : 1,
		'labels' => !empty($editorOptions['labels']) ? $editorOptions['labels'] : array(),
	);

	// Switch between default images and back... mostly in case you don't have an PersonalMessage template, but do have a Post template.
	if (isset($settings['use_default_images']) && $settings['use_default_images'] == 'defaults' && isset($settings['default_template']))
	{
		$temp1 = $settings['theme_url'];
		$settings['theme_url'] = $settings['default_theme_url'];

		$temp2 = $settings['images_url'];
		$settings['images_url'] = $settings['default_images_url'];

		$temp3 = $settings['theme_dir'];
		$settings['theme_dir'] = $settings['default_theme_dir'];
	}

	if (empty($context['bbc_tags']))
	{
		// The below array makes it dead easy to add images to this control. Add it to the array and everything else is done for you!
		$context['bbc_tags'] = array();
		$context['bbc_tags'][] = array(
			array(
				'image' => 'bold',
				'code' => 'b',
				'before' => '[b]',
				'after' => '[/b]',
				'description' => $txt['bold'],
			),
			array(
				'image' => 'italicize',
				'code' => 'i',
				'before' => '[i]',
				'after' => '[/i]',
				'description' => $txt['italic'],
			),
			array(
				'image' => 'underline',
				'code' => 'u',
				'before' => '[u]',
				'after' => '[/u]',
				'description' => $txt['underline']
			),
			array(
				'image' => 'strike',
				'code' => 's',
				'before' => '[s]',
				'after' => '[/s]',
				'description' => $txt['strike']
			),
			array(),
			array(
				'image' => 'pre',
				'code' => 'pre',
				'before' => '[pre]',
				'after' => '[/pre]',
				'description' => $txt['preformatted']
			),
			array(
				'image' => 'left',
				'code' => 'left',
				'before' => '[left]',
				'after' => '[/left]',
				'description' => $txt['left_align']
			),
			array(
				'image' => 'center',
				'code' => 'center',
				'before' => '[center]',
				'after' => '[/center]',
				'description' => $txt['center']
			),
			array(
				'image' => 'right',
				'code' => 'right',
				'before' => '[right]',
				'after' => '[/right]',
				'description' => $txt['right_align']
			),
		);
		$context['bbc_tags'][] = array(
			array(
				'image' => 'flash',
				'code' => 'flash',
				'before' => '[flash=200,200]',
				'after' => '[/flash]',
				'description' => $txt['flash']
			),
			array(
				'image' => 'img',
				'code' => 'img',
				'before' => '[img]',
				'after' => '[/img]',
				'description' => $txt['image']
			),
			array(
				'image' => 'url',
				'code' => 'url',
				'before' => '[url]',
				'after' => '[/url]',
				'description' => $txt['hyperlink']
			),
			array(
				'image' => 'email',
				'code' => 'email',
				'before' => '[email]',
				'after' => '[/email]',
				'description' => $txt['insert_email']
			),
			array(
				'image' => 'ftp',
				'code' => 'ftp',
				'before' => '[ftp]',
				'after' => '[/ftp]',
				'description' => $txt['ftp']
			),
			array(),
			array(
				'image' => 'glow',
				'code' => 'glow',
				'before' => '[glow=red,2,300]',
				'after' => '[/glow]',
				'description' => $txt['glow']
			),
			array(
				'image' => 'shadow',
				'code' => 'shadow',
				'before' => '[shadow=red,left]',
				'after' => '[/shadow]',
				'description' => $txt['shadow']
			),
			array(
				'image' => 'move',
				'code' => 'move',
				'before' => '[move]',
				'after' => '[/move]',
				'description' => $txt['marquee']
			),
			array(),
			array(
				'image' => 'sup',
				'code' => 'sup',
				'before' => '[sup]',
				'after' => '[/sup]',
				'description' => $txt['superscript']
			),
			array(
				'image' => 'sub',
				'code' => 'sub',
				'before' => '[sub]',
				'after' => '[/sub]',
				'description' => $txt['subscript']
			),
			array(
				'image' => 'tele',
				'code' => 'tt',
				'before' => '[tt]',
				'after' => '[/tt]',
				'description' => $txt['teletype']
			),
			array(),
			array(
				'image' => 'table',
				'code' => 'table',
				'before' => '[table]\n[tr]\n[td]',
				'after' => '[/td]\n[/tr]\n[/table]',
				'description' => $txt['table']
			),
			array(
				'image' => 'code',
				'code' => 'code',
				'before' => '[code]',
				'after' => '[/code]',
				'description' => $txt['bbc_code']
			),
			array(
				'image' => 'quote',
				'code' => 'quote',
				'before' => '[quote]',
				'after' => '[/quote]',
				'description' => $txt['bbc_quote']
			),
			array(),
			array(
				'image' => 'list',
				'code' => 'list',
				'before' => '[list]\n[li]',
				'after' => '[/li]\n[li][/li]\n[/list]',
				'description' => $txt['list']
			),
			array(
				'image' => 'orderlist',
				'code' => 'orderlist',
				'before' => '[list type=decimal]\n[li]',
				'after' => '[/li]\n[li][/li]\n[/list]',
				'description' => $txt['list']
			),
			array(
				'image' => 'hr',
				'code' => 'hr',
				'before' => '[hr]',
				'description' => $txt['horizontal_rule']
			),
		);

		// Show the toggle?
		if (empty($modSettings['disable_wysiwyg']))
		{
			$context['bbc_tags'][count($context['bbc_tags']) - 1][] = array();
			$context['bbc_tags'][count($context['bbc_tags']) - 1][] = array(
				'image' => 'unformat',
				'code' => 'unformat',
				'before' => '',
				'description' => $txt['unformat_text'],
			);
			$context['bbc_tags'][count($context['bbc_tags']) - 1][] = array(
				'image' => 'toggle',
				'code' => 'toggle',
				'before' => '',
				'description' => $txt['toggle_view'],
			);
		}

		foreach ($context['bbc_tags'] as $row => $tagRow)
			$context['bbc_tags'][$row][count($tagRow) - 1]['isLast'] = true;
	}

	// Initialize smiley array... if not loaded before.
	if (empty($context['smileys']) && empty($editorOptions['disable_smiley_box']))
	{
		$context['smileys'] = array(
			'postform' => array(),
			'popup' => array(),
		);

		// Load smileys - don't bother to run a query if we're not using the database's ones anyhow.
		if (empty($modSettings['smiley_enable']) && $user_info['smiley_set'] != 'none')
			$context['smileys']['postform'][] = array(
				'smileys' => array(
					array(
						'code' => ':)',
						'filename' => 'smiley.gif',
						'description' => $txt['icon_smiley'],
					),
					array(
						'code' => ';)',
						'filename' => 'wink.gif',
						'description' => $txt['icon_wink'],
					),
					array(
						'code' => ':D',
						'filename' => 'cheesy.gif',
						'description' => $txt['icon_cheesy'],
					),
					array(
						'code' => ';D',
						'filename' => 'grin.gif',
						'description' => $txt['icon_grin']
					),
					array(
						'code' => '>:(',
						'filename' => 'angry.gif',
						'description' => $txt['icon_angry'],
					),
					array(
						'code' => ':(',
						'filename' => 'sad.gif',
						'description' => $txt['icon_sad'],
					),
					array(
						'code' => ':o',
						'filename' => 'shocked.gif',
						'description' => $txt['icon_shocked'],
					),
					array(
						'code' => '8)',
						'filename' => 'cool.gif',
						'description' => $txt['icon_cool'],
					),
					array(
						'code' => '???',
						'filename' => 'huh.gif',
						'description' => $txt['icon_huh'],
					),
					array(
						'code' => '::)',
						'filename' => 'rolleyes.gif',
						'description' => $txt['icon_rolleyes'],
					),
					array(
						'code' => ':P',
						'filename' => 'tongue.gif',
						'description' => $txt['icon_tongue'],
					),
					array(
						'code' => ':-[',
						'filename' => 'embarrassed.gif',
						'description' => $txt['icon_embarrassed'],
					),
					array(
						'code' => ':-X',
						'filename' => 'lipsrsealed.gif',
						'description' => $txt['icon_lips'],
					),
					array(
						'code' => ':-\\',
						'filename' => 'undecided.gif',
						'description' => $txt['icon_undecided'],
					),
					array(
						'code' => ':-*',
						'filename' => 'kiss.gif',
						'description' => $txt['icon_kiss'],
					),
					array(
						'code' => ':\'(',
						'filename' => 'cry.gif',
						'description' => $txt['icon_cry'],
						'isLast' => true,
					),
				),
				'isLast' => true,
			);
		elseif ($user_info['smiley_set'] != 'none')
		{
			if (($temp = cache_get_data('posting_smileys', 480)) == null)
			{
				$request = $smcFunc['db_query']('', '
					SELECT code, filename, description, smiley_row, hidden
					FROM {db_prefix}smileys
					WHERE hidden IN (0, 2)
					ORDER BY smiley_row, smiley_order',
					array(
					)
				);
				while ($row = $smcFunc['db_fetch_assoc']($request))
				{
					$row['filename'] = htmlspecialchars($row['filename']);
					$row['description'] = htmlspecialchars($row['description']);

					$context['smileys'][empty($row['hidden']) ? 'postform' : 'popup'][$row['smiley_row']]['smileys'][] = $row;
				}
				$smcFunc['db_free_result']($request);

				foreach ($context['smileys'] as $section => $smileyRows)
				{
					foreach ($smileyRows as $rowIndex => $smileys)
						$context['smileys'][$section][$rowIndex]['smileys'][count($smileys['smileys']) - 1]['isLast'] = true;

					if (!empty($smileyRows))
						$context['smileys'][$section][count($smileyRows) - 1]['isLast'] = true;
				}

				cache_put_data('posting_smileys', $context['smileys'], 480);
			}
			else
				$context['smileys'] = $temp;
		}
	}

	// Set a flag so the sub template knows what to do...
	$context['show_bbc'] = !empty($modSettings['enableBBC']) && !empty($settings['show_bbc']);

	// Generate a list of buttons that shouldn't be shown - this should be the fastest way to do this.
	$disabled_tags = array();
	if (!empty($modSettings['disabledBBC']))
		$disabled_tags = explode(',', $modSettings['disabledBBC']);
	if (empty($modSettings['enableEmbeddedFlash']))
		$disabled_tags[] = 'flash';

	foreach ($disabled_tags as $tag)
	{
		if ($tag == 'list')
			$context['disabled_tags']['orderlist'] = true;

		$context['disabled_tags'][trim($tag)] = true;
	}

	// Switch the URLs back... now we're back to whatever the main sub template is.  (like folder in PersonalMessage.)
	if (isset($settings['use_default_images']) && $settings['use_default_images'] == 'defaults' && isset($settings['default_template']))
	{
		$settings['theme_url'] = $temp1;
		$settings['images_url'] = $temp2;
		$settings['theme_dir'] = $temp3;
	}
}


// This function displays all the stuff you'd expect to see with a message box, the box, BBC buttons and of course smileys.
//Adapted, by vicram10, to display fine in "Ultimate Portal Modules"
function up_template_control_richedit($editor_id, $smileyContainer, $bbcContainer)
{
	global $context, $settings, $options, $txt, $modSettings, $scripturl;

	$editor_context = &$context['controls']['richedit'][$editor_id];

	$content =  '
		<div>
			<div>
				<textarea class="editor" name="'. $editor_id .'" id="'. $editor_id .'" rows="'. $editor_context['rows'] .'" cols="'. $editor_context['columns'] .'" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);" onchange="storeCaret(this);" tabindex="'. $context['tabindex']++ .'" style="'. ($context['browser']['is_ie8'] ? 'max-width: ' . $editor_context['width'] . '; min-width: ' . $editor_context['width'] : 'width: ' . $editor_context['width'] ) .'; height: '. $editor_context['height'] .';">'. $editor_context['value'] .'</textarea>
			</div>
			<div id="'. $editor_id .'_resizer" style="display: none; '. ($context['browser']['is_ie8'] ? 'max-width: ' . $editor_context['width'] . '; min-width: ' . $editor_context['width'] : 'width: ' . $editor_context['width']) .'" class="richedit_resize"></div>
		</div>
		<input type="hidden" name="'. $editor_id .'_mode" id="'. $editor_id .'_mode" value="0" />
		<script type="text/javascript"><!-- // --><![CDATA[';

		// Show the smileys.
		if (!empty($smileyContainer))
		{		
			$content .=  '
				var oSmileyBox_'. $editor_id. ' = new smc_SmileyBox({
					sUniqueId: '. JavaScriptEscape('smileyBox_' . $editor_id). ',
					sContainerDiv: '. JavaScriptEscape($smileyContainer). ',
					sClickHandler: '. JavaScriptEscape('oEditorHandle_' . $editor_id . '.insertSmiley'). ',
					oSmileyLocations: {';

			foreach ($context['smileys'] as $location => $smileyRows)
			{
				$content .=  '
						'. $location .': [';
				foreach ($smileyRows as $smileyRow)
				{
					$content .=  '
							[';
					foreach ($smileyRow['smileys'] as $smiley)
						$content .=  '
								{
									sCode: '. JavaScriptEscape($smiley['code']). ',
									sSrc: '. JavaScriptEscape($settings['smileys_url'] . '/' . $smiley['filename']). ',
									sDescription: '. JavaScriptEscape($smiley['description']). '
								}'. (empty($smiley['isLast']) ? ',' : '');

				$content .=  '
							]'. (empty($smileyRow['isLast']) ? ',' : '');
				}
				$content .=  '
						]'. ($location === 'postform' ? ',' : '');
			}
			$content .=  '
					},
					sSmileyBoxTemplate: '. JavaScriptEscape('
						%smileyRows% %moreSmileys%
					') .',
					sSmileyRowTemplate: '. JavaScriptEscape('
						<div>%smileyRow%</div>
					') .',
					sSmileyTemplate: '. JavaScriptEscape('
						<img src="%smileySource%" align="bottom" alt="%smileyDescription%" title="%smileyDescription%" id="%smileyId%" />
					') .',
					sMoreSmileysTemplate: '. JavaScriptEscape('
						<a href="#" id="%moreSmileysId%">[' . (!empty($context['smileys']['postform']) ? $txt['more_smileys'] : $txt['more_smileys_pick']) . ']</a>
					') .',
					sMoreSmileysLinkId: '. JavaScriptEscape('moreSmileys_' . $editor_id). ',
					sMoreSmileysPopupTemplate: '. JavaScriptEscape('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
						<html>
							<head>
								<title>' . $txt['more_smileys_title'] . '</title>
								<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/index' . $context['theme_variant'] . '.css?rc2" />
							</head>
							<body id="help_popup">
								<div class="padding windowbg">
									<h3 class="catbg"><span class="left"></span>
										' . $txt['more_smileys_pick'] . '
									</h3>
									<div class="padding">
										%smileyRows%
									</div>
									<div class="smalltext centertext">
										<a href="#" id="%moreSmileysCloseLinkId%">' . $txt['more_smileys_close_window'] . '</a>
									</div>
								</div>
							</body>
						</html>') .'
				});';
		}

		if (!empty($bbcContainer))
		{
			$content .=  '
				var oBBCBox_'. $editor_id. ' = new smc_BBCButtonBox({
					sUniqueId: '. JavaScriptEscape('BBCBox_' . $editor_id). ',
					sContainerDiv: '. JavaScriptEscape($bbcContainer). ',
					sButtonClickHandler: '. JavaScriptEscape('oEditorHandle_' . $editor_id . '.handleButtonClick'). ',
					sSelectChangeHandler: '. JavaScriptEscape('oEditorHandle_' . $editor_id . '.handleSelectChange'). ',
					aButtonRows: [';

			// Here loop through the array, printing the images/rows/separators!
			foreach ($context['bbc_tags'] as $i => $buttonRow)
			{
				$content .=  '
						[';
				foreach ($buttonRow as $tag)
				{
					// Is there a "before" part for this bbc button? If not, it can't be a button!!
					if (isset($tag['before']))
						$content .=  '
							{
								sType: \'button\',
								bEnabled: '. (empty($context['disabled_tags'][$tag['code']]) ? 'true' : 'false'). ',
								sImage: '. JavaScriptEscape($settings['images_url'] . '/bbc/' . $tag['image'] . '.gif'). ',
								sCode: '. JavaScriptEscape($tag['code']). ',
								sBefore: '. JavaScriptEscape($tag['before']). ',
								sAfter: '. (isset($tag['after']) ? JavaScriptEscape($tag['after']) : 'null'). ',
								sDescription: '. JavaScriptEscape($tag['description']). '
							}'. (empty($tag['isLast']) ? ',' : '');

					// Must be a divider then.
					else
						$content .=  '
							{
								sType: \'divider\'
							}'. (empty($tag['isLast']) ? ',' : '');
				}

				// Add the select boxes to the first row.
				if ($i == 0)
				{
					// Show the font drop down...
					if (!isset($context['disabled_tags']['font']))
						$content .=  ',
							{
								sType: \'select\',
								sName: \'sel_face\',
								oOptions: {
									\'\': '. JavaScriptEscape($txt['font_face']) .',
									\'courier\': \'Courier\',
									\'arial\': \'Arial\',
									\'arial black\': \'Arial Black\',
									\'impact\': \'Impact\',
									\'verdana\': \'Verdana\',
									\'times new roman\': \'Times New Roman\',
									\'georgia\': \'Georgia\',
									\'andale mono\': \'Andale Mono\',
									\'trebuchet ms\': \'Trebuchet MS\',
									\'comic sans ms\': \'Comic Sans MS\'
								}
							}';

					// Font sizes anyone?
					if (!isset($context['disabled_tags']['size']))
						$content .=  ',
							{
								sType: \'select\',
								sName: \'sel_size\',
								oOptions: {
									\'\': '. JavaScriptEscape($txt['font_size']) .',
									\'1\': \'8pt\',
									\'2\': \'10pt\',
									\'3\': \'12pt\',
									\'4\': \'14pt\',
									\'5\': \'18pt\',
									\'6\': \'24pt\',
									\'7\': \'36pt\'
								}
							}';

					// Print a drop down list for all the colors we allow!
					if (!isset($context['disabled_tags']['color']))
						$content .=  ',
							{
								sType: \'select\',
								sName: \'sel_color\',
								oOptions: {
									\'\': '. JavaScriptEscape($txt['change_color']) .',
									\'black\': '. JavaScriptEscape($txt['black']) .',
									\'red\': '. JavaScriptEscape($txt['red']) .',
									\'yellow\': '. JavaScriptEscape($txt['yellow']) .',
									\'pink\': '. JavaScriptEscape($txt['pink']) .',
									\'green\': '. JavaScriptEscape($txt['green']) .',
									\'orange\': '. JavaScriptEscape($txt['orange']) .',
									\'purple\': '. JavaScriptEscape($txt['purple']) .',
									\'blue\': '. JavaScriptEscape($txt['blue']) .',
									\'beige\': '. JavaScriptEscape($txt['beige']) .',
									\'brown\': '. JavaScriptEscape($txt['brown']) .',
									\'teal\': '. JavaScriptEscape($txt['teal']) .',
									\'navy\': '. JavaScriptEscape($txt['navy']) .',
									\'maroon\': '. JavaScriptEscape($txt['maroon']) .',
									\'limegreen\': '. JavaScriptEscape($txt['lime_green']) .',
									\'white\': '. JavaScriptEscape($txt['white']) .'
								}
							}';
				}
				$content .=  '
						]'. ($i == count($context['bbc_tags']) - 1 ? '' : ',');
			}
			$content .=  '
					],
					sButtonTemplate: '. JavaScriptEscape('
						<img id="%buttonId%" src="%buttonSrc%" align="bottom" width="23" height="22" alt="%buttonDescription%" title="%buttonDescription%" />
					'). ',
					sButtonBackgroundImage: '. JavaScriptEscape($settings['images_url'] . '/bbc/bbc_bg.gif'). ',
					sButtonBackgroundImageHover: '. JavaScriptEscape($settings['images_url'] . '/bbc/bbc_hoverbg.gif'). ',
					sActiveButtonBackgroundImage: '. JavaScriptEscape($settings['images_url'] . '/bbc/bbc_hoverbg.gif'). ',
					sDividerTemplate: '. JavaScriptEscape('
						<img src="' . $settings['images_url'] . '/bbc/divider.gif" alt="|" style="margin: 0 3px 0 3px;" />
					'). ',
					sSelectTemplate: '. JavaScriptEscape('
						<select name="%selectName%" id="%selectId%" style="margin-bottom: 1ex; font-size: x-small;">
							%selectOptions%
						</select>
					'). ',
					sButtonRowTemplate: '. JavaScriptEscape('
						<div>%buttonRow%</div>
					'). '
				});';
		}


		// Now it's all drawn out we'll actually setup the box.
		$content .=  '
				var oEditorHandle_'. $editor_id. ' = new smc_Editor({
					sSessionId: '. JavaScriptEscape($context['session_id']). ',
					sSessionVar: '. JavaScriptEscape($context['session_var']). ',
					sFormId: '. JavaScriptEscape($editor_context['form']). ',
					sUniqueId: '. JavaScriptEscape($editor_id). ',
					bWysiwyg: '. ($editor_context['rich_active'] ? 'true' : 'false'). ',
					sText: '. JavaScriptEscape($editor_context['rich_active'] ? $editor_context['rich_value'] : ''). ',
					sEditWidth: '. JavaScriptEscape($editor_context['width']). ',
					sEditHeight: '. JavaScriptEscape($editor_context['height']). ',
					bRichEditOff: '. (empty($modSettings['disable_wysiwyg']) ? 'false' : 'true') .',
					oSmileyBox: '. (!empty($context['smileys']['postform']) && !$editor_context['disable_smiley_box'] && $smileyContainer !== null ? 'oSmileyBox_' . $editor_id : 'null'). ',
					oBBCBox: '. ($context['show_bbc'] && $bbcContainer !== null ? 'oBBCBox_' . $editor_id : 'null'). '
				});
				smf_editorArray[smf_editorArray.length] = oEditorHandle_'. $editor_id .';';

		$content .=  '
			// ]]></script>';
				
	return $content;

}

function up_loadJumpTo()
{
	global $smcFunc, $context, $user_info;
	
	// Based on the loadJumpTo() from SMF 1.1.X
	if (isset($context['jump_to']))
		return;

	// Find the boards/cateogories they can see.
	$request = $smcFunc['db_query']('', "
		SELECT c.name AS cat_name, c.id_cat, b.id_board, b.name AS board_name, b.child_level
		FROM {db_prefix}boards AS b
			LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		WHERE $user_info[query_see_board]"
		);
		
	$context['jump_to'] = array();
	$this_cat = array('id' => -1);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($this_cat['id'] != $row['id_cat'])
		{
			$this_cat = &$context['jump_to'][];
			$this_cat['id'] = $row['id_cat'];
			$this_cat['name'] = $row['cat_name'];
			$this_cat['boards'] = array();
		}

		$this_cat['boards'][] = array(
			'id' => $row['id_board'],
			'name' => $row['board_name'],
			'child_level' => $row['child_level'],
			'is_current' => isset($context['current_board']) && $row['id_board'] == $context['current_board']
		);
	}
	$smcFunc['db_free_result']($request);
}

function ReturnCurrentUrl()
{
	global $smcFunc, $context, $user_info;
	
	$url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	return $url;
}

//Ultimate Portal: Module Download Stats
//Top User Upload Files
function TopUserUpload()
{
	global $smcFunc, $context, $user_info;
	global $memberContext, $settings;
	
	$context['view-top-uploader'] = 0;
	
	$request = $smcFunc['db_query']('', "
			SELECT count( id_files ) AS Total_Upload, id_member, membername
			FROM `smf_up_download_files`
			WHERE approved = 1
			GROUP BY id_member
			LIMIT 5");
	$max_num_posts = 1;	
	$context['down_top_user'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!empty($row['Total_Upload']))
		{
			$context['view-top-uploader'] = 1;//only for, no see any error in log
			$TopUploadMember = &$context['down_top_user'][];
			$TopUploadMember['Total_Upload'] = $row['Total_Upload'];
			$TopUploadMember['id_member'] = $row['id_member'];
			$TopUploadMember['membername'] = $row['membername'];
			//Load more information for this member
			loadMemberData($row['id_member']);
			loadMemberContext($row['id_member']);
			$TopUploadMember['avatar'] = !empty($memberContext[$row['id_member']]['avatar']['href']) ? '<img alt="" style="vertical-align: middle;" border="0" src="'. $memberContext[$row['id_member']]['avatar']['href'] .'" width="50" height="50"/>' : '<img alt="" style="vertical-align: middle;" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/no_avatar.png" width="50" height="50"/>';				
			$TopUploadMember['profile'] = $memberContext[$row['id_member']]['link'];				

			if ($max_num_posts < $row['Total_Upload'])
				$max_num_posts = $row['Total_Upload'];
				
			$TopUploadMember['upload_percent'] = round(($row['Total_Upload'] * 100) / $max_num_posts);
		}	
	}
	$smcFunc['db_free_result']($request);
}
//End Download Module Stats

//User Upload File Profile
function LoadUserProfile($id_member)
{
	global $smcFunc, $context, $user_info;
	global $memberContext, $settings, $db_prefix;
	global $ultimateportalSettings, $scripturl;
	
	$context['view_profile'] = 0;
	$context['total_files'] = 0;

	//Prepare the constructPageIndex() function
	$start = (int) $_REQUEST['start'];
	$db_count = $smcFunc['db_query']('',"SELECT count(id_files)
						FROM {$db_prefix}up_download_files
						WHERE id_member = $id_member and approved = 1
						ORDER BY title ASC");
	$num = array();
	list($num) = $smcFunc['db_fetch_row']($db_count);
	$smcFunc['db_free_result']($db_count);

	$context['page_index'] = constructPageIndex($scripturl . '?action=downloads;sa=profile;u='. $id_member, $start, $num, $ultimateportalSettings['download_file_limit_page']);

	// Calculate the fastest way to get the messages!
	$limit = $ultimateportalSettings['download_file_limit_page'];
	//End Prepare constructPageIndex() function
	
	$request = $smcFunc['db_query']('', "
			SELECT *
			FROM {$db_prefix}up_download_files
			WHERE approved = 1 and id_member = $id_member
			". ($limit < 0 ? "" : "LIMIT $start, $limit "));

	$context['down_profile'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		//Load Especific ID
		SpecificSection($row['id_section']);
		//End
		if(!empty($context['canview']))
		{
			$context['view_profile'] = 1;//only for, no see any error in log
			$context['id_member'] = $row['id_member'];
			$context['membername'] = $row['membername'];		
			//Load more information for this member
			loadMemberData($row['id_member']);
			loadMemberContext($row['id_member']);
			$context['avatar'] = !empty($memberContext[$row['id_member']]['avatar']['href']) ? '<img alt="" style="vertical-align: middle;" border="0" src="'. $memberContext[$row['id_member']]['avatar']['href'] .'" width="50" height="50"/>' : '<img alt="" style="vertical-align: middle;" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/no_avatar.png" width="50" height="50"/>';				
			$context['profile'] = $memberContext[$row['id_member']]['link'];						
			$down_profile = &$context['down_profile'][];
			$down_profile['id_files'] = $row['id_files'];
			$down_profile['title'] = $row['title'];
			$down_profile['small_description'] = $row['small_description'];
			$down_profile['total_downloads'] = $row['total_downloads'];
			$down_profile['date_created'] = timeformat($row['date_created']);				
			$down_profile['date_updated'] = timeformat($row['date_updated']);				
			if (!empty($row['id_member']))
				$context['total_files']++;
		}
	}
	$smcFunc['db_free_result']($request);
}

//Load Internal Page with a Condition = Disable Page?
function DisablePage($condition = "WHERE active = 'off'")
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings, $user_info;
	
	$db_count = $smcFunc['db_query']('',"SELECT *
						FROM {$db_prefix}ultimate_portal_ipage
						". $condition ."");
	$context['disabled_page'] = $smcFunc['db_num_rows']($db_count);
	$smcFunc['db_free_result']($db_count);
}

//Load Internal Page with a Condition
function LoadInternalPage($id, $condition = "WHERE active = 'on'")
{
	global $db_prefix, $context, $scripturl, $txt, $settings;
	global $smcFunc, $ultimateportalSettings, $user_info;
	
	if (empty($id))
	{
		//Prepare the constructPageIndex() function
		$num = 0;
		$start = (int) $_REQUEST['start'];
		$db_count = $smcFunc['db_query']('',"SELECT *
							FROM {$db_prefix}ultimate_portal_ipage
							". $condition ."
							ORDER BY sticky DESC, id DESC ");
		while ($sql_count = $smcFunc['db_fetch_assoc']($db_count))
		{
			$perms = '';
			$perms = array();
			if ($sql_count['perms']) {
				$perms =  $sql_count['perms'];
			}
			if(!$perms) {
				$perms = array();
			}
			$perms = explode(',', $perms);		
			$can_view = false;
			if (!$user_info['is_admin'])
			{
				foreach($user_info['groups'] as $group_id) 
					if(in_array($group_id, $perms)) {
						$can_view = true;
					}
			}else{
				$can_view = true;			
			}
			if ($can_view == true)
			{
				++$num;
			}
		}
		
		$smcFunc['db_free_result']($db_count);
		$context['num_rows'] = $num;
		$context['page_index'] = constructPageIndex($scripturl . '?action=internal-page', $start, $num, $ultimateportalSettings['ipage_limit']);
	}
	// Calculate the fastest way to get the messages!
	$limit = $ultimateportalSettings['ipage_limit'];
	//End Prepare constructPageIndex() function
	
	$context['view_ipage']	= !$user_info['is_admin'] ? 0 : 1;
	//Load 
	$myquery = $smcFunc['db_query']('',"SELECT *
						FROM {$db_prefix}ultimate_portal_ipage 
						". $condition ."". (!empty($id) ? " AND id = $id" : "") ."
						ORDER BY sticky DESC, id DESC ". (($limit < 0 || !empty($id)) ? "" : "LIMIT $start, $limit "));			
	while( $row = $smcFunc['db_fetch_assoc']($myquery) ) {
		$context['view_ipage'] = 1;
		$ipage = &$context['ipage'][];
		$ipage['id'] = $row['id'];
		$context['id'] = $row['id'];
		$ipage['title'] = '<a href="'. $scripturl .'?action=internal-page;sa='. (isset($context['is_inactive_page']) ? 'view-inactive' : 'view') .';id='. $row['id'] .'">'. stripslashes($row['title']) .'</a>';
		$context['title'] = $row['title'];
		if(!empty($id))
			$context['title'] = stripslashes($row['title']);
		$ipage['sticky'] = $row['sticky'];				
		$context['sticky'] = $row['sticky'];						
		$ipage['active'] = $row['active'];				
		$context['active'] = $row['active'];						
		$ipage['type_ipage'] = $row['type_ipage'];
			$context['type_ipage'] = $row['type_ipage'];
			$context['content'] = $row['content'];								
		$ipage['content'] = $row['content'];				
		$ipage['parse_content'] = ($row['type_ipage'] == 'html') ? stripslashes($row['content']) : parse_bbc($row['content']);
		//Can see the internal page?
		$perms = '';
		$perms = array();
		if ($row['perms']) {
			$perms =  $row['perms'];
				$context['perms'] =  $row['perms'];
		}
		if(!$perms) {
			$perms = array();
		}
		$perms = explode(',', $perms);		
		$ipage['can_view'] = false;		
		$context['can_view'] = false;
		if (!$user_info['is_admin'])
		{
			foreach($user_info['groups'] as $group_id) 
				if(in_array($group_id, $perms)) {
					$ipage['can_view'] = true;
						$context['can_view'] = true;
				}
		}else{
			$ipage['can_view'] = true;
			$context['can_view'] = true;			
		}
		//End
		$ipage['column_left'] = $row['column_left'];				
		$ipage['column_right'] = $row['column_right'];				
			$context['column_left'] = $row['column_left'];				
			$context['column_right'] = $row['column_right'];							
		$ipage['date_created'] = timeformat($row['date_created']);		
		$ipage['month_created'] = strftime('%m',$row['date_created']);		
		$ipage['day_created'] = strftime('%d',$row['date_created']);		
		$ipage['year_created'] = strftime('%y',$row['date_created']);		
		$ipage['id_member'] = $row['id_member'];
		$ipage['username'] = $row['username'];	
		$ipage['profile'] = '<a href="'. $scripturl .'?action=profile;u='. $row['id_member'] .'">'. $row['username'] .'</a>';	
		$ipage['is_updated'] = !empty($row['date_updated']);				
		$ipage['date_updated'] = timeformat($row['date_updated']);				
		$ipage['id_member_updated'] = !empty($row['date_updated']) ? $row['id_member_updated'] : '';
		$ipage['username_updated'] = !empty($row['date_updated']) ? $row['username_updated'] : '';	
		$ipage['profile_updated'] = !empty($row['date_updated']) ? '<a href="'. $scripturl .'?action=profile;u='. $row['id_member_updated'] .'">'. $row['username_updated'] .'</a>' : '';	
		//Extra Information 
		loadMemberData($ipage['id_member']);
		loadMemberContext($ipage['id_member']);
		$ipage['read_more'] = '<strong><a href="'. $scripturl .'?action=internal-page;sa='. (isset($context['is_inactive_page']) ? 'view-inactive' : 'view') .';id='. $row['id'] .'">'. $txt['ultport_read_more'] .'</a></strong>';
		$ipage['edit'] = '<a href="'. $scripturl .'?action=internal-page;sa=edit;id='. $row['id'] .';sesc=' . $context['session_id'].'"><img alt="" style="vertical-align: middle;" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png" /></a>';
		$ipage['delete'] = '<a onclick="return makesurelink()" href="'. $scripturl .'?action=internal-page;sa=delete;id='. $row['id'] .';sesc=' . $context['session_id'].'"><img alt="" style="vertical-align: middle;" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png" />&nbsp;</a>';						

	}

	$smcFunc['db_free_result']($myquery);	
}

//Social Bookmarks
function UpSocialBookmarks($url)
{
	global $smcFunc, $context, $user_info, $txt;
	global $memberContext, $settings, $db_prefix;
	global $ultimateportalSettings, $scripturl;

	//The language for this function, is in UltimatePortal.yourlanguage.php
	
	// Load Language
	if (loadlanguage('UltimatePortal') == false)
		loadLanguage('UltimatePortal','english');
		
	$twitter = 'http://twitter.com/home?status='. $txt['ultport_social_bookmarks_recommends'] .':%20';
	$facebook = 'http://www.facebook.com/share.php?u=';
	$delicious = 'http://del.icio.us/post?url=';
	$digg = 'http://digg.com/submit?phase=2&amp;url=';
	
	$social_bookmarks = '
		<table class="tborder" style="border: 1px solid" cellpadding="5" cellspacing="1" width="100%">
			<tr>
				<td valign="top" class="catbg" width="100%" align="left">															
					<strong><u>'. $txt['ultport_social_bookmarks_share'] .'</u></strong>
				</td>
			</tr>		
			<tr>
				<td valign="top" class="windowbg" width="100%" align="left">																		
					<a href="'.$facebook.''.$url.'" target="_blank"><img src="'.$settings['default_images_url'].'/ultimate-portal/social-bookmarks/facebook.png"  alt="Facebook" title="Facebook" /></a> 
					<a href="'.$twitter.''.$url.'" target="_blank"><img src="'.$settings['default_images_url'].'/ultimate-portal/social-bookmarks/twitter.png" alt=" | Twitter" title="Twitter" /></a> 
					<a href="'.$delicious.''.$url.'" target="_blank"><img src="'.$settings['default_images_url'].'/ultimate-portal/social-bookmarks/delicious.png" alt=" | del.icio.us" title="delicious" /></a> 
					<a href="'.$digg.''.$url.'" target="_blank"><img src="'.$settings['default_images_url'].'/ultimate-portal/social-bookmarks/digg.png" alt=" | digg" title="digg" /></a> 
				</td>
			</tr>
		</table>';

	return $social_bookmarks;
}

//Load the Affilates
function LoadAffiliates($condition = "")
{
	global $settings, $db_prefix, $context, $scripturl, $txt, $smcFunc;
	global $ultimateportalSettings;
	
	$context['view'] = 0;
	
	$request = $smcFunc['db_query']('', "
					SELECT id, url, title, alt, imageurl  
					FROM {db_prefix}up_affiliates
					ORDER BY id ASC
					". $condition ."");
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		++$context['view'];
		$aff = &$context['up-aff'][];
		$aff['id'] = $row['id'];
		$aff['title'] = '<a href="'. $row['url'] .'">'. $row['title'] .'</a>';
		$aff['imageurl'] = '<a href="'. $row['url'] .'" title="'.$row['alt'].'"><img src="'. $row['imageurl'] .' " alt="'.$row['alt'].'" /></a>';	
		$aff['edit'] = '<a href="'. $scripturl .'?action=admin;area=up-affiliates;sa=edit_aff;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a>';
		$aff['delete'] = '<a style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=up-affiliates;sa=del_aff;id='. $row['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
	}
	
}

//Load the FAQ SECTION
function LoadFaqSection()
{
	global $settings, $db_prefix, $context, $scripturl, $txt, $smcFunc;
	global $ultimateportalSettings;
	
	$context['view_section'] = 0;
	
	$request = $smcFunc['db_query']('', "
					SELECT id_section, section  
					FROM {db_prefix}up_faq_section
					ORDER BY id_section ASC");
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		++$context['view_section'];
		$context['faq_section'][] = array(
			'id_section' => $row['id_section'],
			'section' => $row['section'],
			'edit' => '<a href="'. $scripturl .'?action=faq;sa=edit-section;id='. $row['id_section'] .'"><img alt="" title="'. $txt['ultport_button_edit'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png"/></a>',
			'delete' => '<a style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=faq;sa=del-section;id='. $row['id_section'] .'"><img alt="" title="'. $txt['ultport_button_delete'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png"/></a>',			
		);
	}
	$smcFunc['db_free_result']($request);
}

//Load the FAQ's
function LoadFAQMain()
{
	global $settings, $db_prefix, $context, $scripturl, $txt, $smcFunc;
	global $ultimateportalSettings;
	
	$context['view_faq_main'] = 0;
	
	$request = $smcFunc['db_query']('', "
					SELECT id_section, section  
					FROM {db_prefix}up_faq_section
					ORDER BY id_section ASC");
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['faq_main'][$row['id_section']] = array(
			'id_section' => $row['id_section'],
			'section' => $row['section'],
			'edit' => '<a href="'. $scripturl .'?action=faq;sa=edit-section;id='. $row['id_section'] .';sesc=' . $context['session_id'].'"><img alt="" title="'. $txt['ultport_button_edit'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png"/></a>',
			'delete' => '<a onclick="return makesurelink(\'section\')" href="'. $scripturl .'?action=faq;sa=del-section;id='. $row['id_section'] .';sesc=' . $context['session_id'].'"><img alt="" title="'. $txt['ultport_button_delete'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png"/></a>',			
			'question' => array(),
		);
		//FAQ
		$sql_faq = $smcFunc['db_query']('', "
						SELECT id, question, answer  
						FROM {db_prefix}up_faq
						WHERE id_section = {int:id_sect}
						ORDER BY id_section ASC",
						array(
							'id_sect' => $row['id_section'],	  
						)
					);
		while ($row_faq = $smcFunc['db_fetch_assoc']($sql_faq))
		{
			++$context['view_faq_main'];
			$context['faq_main'][$row['id_section']]['question'][] = array(
				'id' => $row_faq['id'],
				'question' => $row_faq['question'],
				'answer' => parse_bbc($row_faq['answer']),
				'edit' => '<a href="'. $scripturl .'?action=faq;sa=edit-faq;id='. $row_faq['id'] .';sesc=' . $context['session_id'].'"><img alt="" title="'. $txt['ultport_button_edit'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png"/></a>',
				'delete' => '<a onclick="return makesurelink(\'faq\')" href="'. $scripturl .'?action=faq;sa=del-faq;id='. $row_faq['id'] .';sesc=' . $context['session_id'].'"><img alt="" title="'. $txt['ultport_button_delete'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png"/></a>',							
		 	);
		}
		$smcFunc['db_free_result']($sql_faq);		
	}
	$smcFunc['db_free_result']($request);
}

//Load the Specific FAQ's
function LoadFAQSpecific($id)
{
	global $settings, $db_prefix, $context, $scripturl, $txt, $smcFunc;
	global $ultimateportalSettings;

	if (empty($id))
		fatal_lang_error('ultport_error_no_edit',false);
	
	$sql_faq = $smcFunc['db_query']('', "
					SELECT id, question, answer, id_section  
					FROM {db_prefix}up_faq
					WHERE id = {int:id}",
					array(
						'id' => $id,	  
					)
				);
	while ($row_faq = $smcFunc['db_fetch_assoc']($sql_faq))
	{
		$context['id'] = $row_faq['id'];
		$context['question'] = $row_faq['question'];
		$context['answer'] = $row_faq['answer'];
		$context['id_section'] = $row_faq['id_section'];
	}
	$smcFunc['db_free_result']($sql_faq);		
}

//Load the FAQ Specific SECTION
function LoadFaqSpecificSection($id)
{
	global $settings, $db_prefix, $context, $scripturl, $txt, $smcFunc;
	global $ultimateportalSettings;

	$id = (int) $id;
	
	$request = $smcFunc['db_query']('', "
					SELECT id_section, section  
					FROM {db_prefix}up_faq_section
					WHERE id_section = {int:id_section}",
					array(
						'id_section' => $id,	  
					)
				);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['id_section'] = $row['id_section'];
		$context['section'] = $row['section'];
	}
	$smcFunc['db_free_result']($request);
}

// Generate a strip of buttons.
function up_template_button_strip($button_strip, $direction = 'top', $strip_options = array())
{
	global $settings, $context, $txt, $scripturl;
	global $user_info, $ultimateportalSettings;

	if (!is_array($strip_options))
		$strip_options = array();

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		if ((isset($value['condition']) && $value['condition']))
			$buttons[] = '<a ' . (isset($value['active']) ? 'class="active" ' : '') . 'href="' . $value['url'] . '" ' . (isset($value['custom']) ? $value['custom'] : '') . '><span>' . $txt[$value['text']] . '</span></a>';
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	// Make the last one, as easy as possible.
	$buttons[count($buttons) - 1] = str_replace('<span>', '<span class="last">', $buttons[count($buttons) - 1]);

	$construct_button = '
		<div class="UPbuttonlist'. (!empty($direction) ? ' align_' . $direction : ''). '"'. ((empty($buttons) ? ' style="display: none;"' : '')) . ((!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"': '')) . '>
			<ul>
				<li>'. implode('</li><li>', $buttons) .'</li>
			</ul>
		</div>';
		
	return $construct_button;	
}

function UPResizeImage($src_img, $destName, $fileext, $src_width, $src_height, $max_width, $max_height, $force_resize = false)
{
	global $modSettings;
	
	$dst_width = ($src_width * $max_width) / 100;
	$dst_width = floor($dst_width);	
	$dst_height = ($src_height * $max_height) / 100;
	$dst_height = floor($dst_height);
	// (make a true color image, because it just looks better for resizing.)
	$dst_img = imagecreatetruecolor($dst_width, $dst_height);
	// Resize it!
	imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);
	// Save it!
	$imageextension = 'image'.$fileext;
	$imageextension($dst_img, $destName);
	// Free the memory.
	imagedestroy($src_img);
	if ($dst_img != $src_img)
		imagedestroy($dst_img);
}

function warning_delete($text)
{
	$warning = "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$text."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";
		
	return $warning;
}

//Board News Module
function upSSI_BoardNews($num_recent = 8, $exclude_boards = null, $include_boards = null, $length = 0, $bkcall = '')
{
	global $context, $settings, $scripturl, $txt, $db_prefix, $user_info;
	global $modSettings, $smcFunc;

	loadLanguage('Stats');

	if ($exclude_boards === null && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0)
		$exclude_boards = array($modSettings['recycle_board']);
	else
		$exclude_boards = empty($exclude_boards) ? array() : (is_array($exclude_boards) ? $exclude_boards : array($exclude_boards));

	// Only some boards?.
	if (is_array($include_boards) || (int) $include_boards === $include_boards)
	{
		$include_boards = is_array($include_boards) ? $include_boards : array($include_boards);
	}
	elseif ($include_boards != null)
	{
		$output_method = $include_boards;
		$include_boards = array();
	}

	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	//Prepare the constructPageIndex() function
	$start = (!empty($_REQUEST['sa']) && $_REQUEST['sa'] == $bkcall.'boardnews') ? (int) $_REQUEST['start'] : 0;
	$db_count = $smcFunc['db_query']('', '
		SELECT count(m.id_topic)
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			INNER JOIN {db_prefix}messages AS ms ON (ms.id_msg = t.id_first_msg)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)' . (!$user_info['is_guest'] ? '
			LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
			LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = b.id_board AND lmr.id_member = {int:current_member})' : '') . '
		WHERE m.id_topic > 0
			' . (empty($exclude_boards) ? '' : '
			AND b.id_board NOT IN ({array_int:exclude_boards})') . '
			' . (empty($include_boards) ? '' : '
			AND b.id_board IN ({array_int:include_boards})') . '
			AND {query_wanna_see_board}' . ($modSettings['postmod_active'] ? '
			AND t.approved = {int:is_approved}
			AND m.approved = {int:is_approved}' : '') . '
		ORDER BY t.id_first_msg DESC',
		array(
			'current_member' => $user_info['id'],
			'include_boards' => empty($include_boards) ? '' : $include_boards,
			'exclude_boards' => empty($exclude_boards) ? '' : $exclude_boards,
			'is_approved' => 1,
		)
	);	
	
	$numNews = array();
	list($numNews) = $smcFunc['db_fetch_row']($db_count);
	$smcFunc['db_free_result']($db_count);
	
	$context['page_index'] = constructPageIndex($scripturl . '?sa='.$bkcall.'boardnews', $start, $numNews, $num_recent);

	// Find all the posts in distinct topics.  Newer ones will have higher IDs.
	$request = $smcFunc['db_query']('', '
		SELECT
			m.poster_time, ms.subject, m.id_topic, m.id_member, m.id_msg, b.id_board, b.name AS board_name, t.num_replies, t.num_views,
			t.locked,
			IFNULL(mem.real_name, m.poster_name) AS poster_name, ' . ($user_info['is_guest'] ? '1 AS is_read, 0 AS new_from' : '
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, 0)) >= m.id_msg_modified AS is_read,
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1 AS new_from') . ', m.body AS body, m.smileys_enabled, m.icon
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			INNER JOIN {db_prefix}messages AS ms ON (ms.id_msg = t.id_first_msg)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)' . (!$user_info['is_guest'] ? '
			LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
			LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = b.id_board AND lmr.id_member = {int:current_member})' : '') . '
		WHERE m.id_topic > 0
			' . (empty($exclude_boards) ? '' : '
			AND b.id_board NOT IN ({array_int:exclude_boards})') . '
			' . (empty($include_boards) ? '' : '
			AND b.id_board IN ({array_int:include_boards})') . '
			AND {query_wanna_see_board}' . ($modSettings['postmod_active'] ? '
			AND t.approved = {int:is_approved}
			AND m.approved = {int:is_approved}' : '') . '
		ORDER BY t.id_first_msg DESC
		 '. ($num_recent < 0 ? "" : " LIMIT {int:start}, {int:limit} ") .'',
		array(
			'current_member' => $user_info['id'],
			'include_boards' => empty($include_boards) ? '' : $include_boards,
			'exclude_boards' => empty($exclude_boards) ? '' : $exclude_boards,
			'is_approved' => 1,
			'start' => $start,
			'limit' => $num_recent,
		)
	);
	
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
			// If we want to limit the length of the post.
		if (!empty($length) && $smcFunc['strlen']($row['body']) > $length)
		{
			$row['body'] = $smcFunc['substr']($row['body'], 0, $length);

			// The first space or line break. (<br />, etc.)
			$cutoff = max(strrpos($row['body'], ' '), strrpos($row['body'], '<'));

			if ($cutoff !== false)
				$row['body'] = $smcFunc['substr']($row['body'], 0, $cutoff);
			$row['body'] .= '...';
		}

		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

		// Censor the subject.
		censorText($row['subject']);
		censorText($row['body']);

		if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.gif') ? 'images_url' : 'default_images_url';

		// Build the array.
		$return[] = array(
			'board' => array(
				'id' => $row['id_board'],
				'name' => $row['board_name'],
				'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['board_name'] . '</a>'
			),
			'topic' => $row['id_topic'],
			'poster' => array(
				'id' => $row['id_member'],
				'name' => $row['poster_name'],
				'href' => empty($row['id_member']) ? '' : $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => empty($row['id_member']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>'
			),
			'subject' => $row['subject'],
			'replies' => $row['num_replies'],
			'views' => $row['num_views'],
			'short_subject' => shorten_subject($row['subject'], 25),
			'preview' => $row['body'],
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . ';topicseen#new',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#new" rel="nofollow">' . $row['subject'] . '</a>',
			// Retained for compatibility - is technically incorrect!
			'new' => !empty($row['is_read']),
			'is_new' => empty($row['is_read']),
			'new_from' => $row['new_from'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.gif" align="middle" alt="' . $row['icon'] . '" border="0" />',
			'comment_link' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'] . '">' . $txt['ssi_write_comment'] . '</a>',
		);
	}
	$smcFunc['db_free_result']($request);

	//ok return now
	return $return;
}

//Super Array for Load Enable Modules (like Core Features section)
function LoadEnableModules()
{
	global $context, $txt, $ultimateportalSettings;
	$context['array_modules'] = array(
		// up = User Posts Module
		'user_posts_enable' => array(
			'title' =>	$txt['ultport_admin_up_enable'],	  
			'desc' => '',
			'images' => 'user-posts.png', //located in default/images/ultimate-portal/admin-main/
			'url' => 'action=admin;area=user-posts',
			'settings' => array(
				'enable' => !empty($ultimateportalSettings['user_posts_enable']),
			),
			//Very important this part.
			'section' => 'enable_user_posts',
		),
		//End UP Module
		// News Module
		'up_news_enable' => array(
			'title' =>	$txt['ultport_admin_news_enable'],	  
			'desc' => '',
			'images' => 'news.png', //located in default/images/ultimate-portal/admin-main/
			'url' => 'action=admin;area=up-news',
			'settings' => array(
				'enable' => !empty($ultimateportalSettings['up_news_enable']),
			),
			//Very important this part.
			'section' => 'enable_up_news',
		),
		//End News Module
		// Download Module
		'download_enable' => array(
			'title' =>	$txt['up_download_enable'],	  
			'desc' => '',
			'images' => 'download.png', //located in default/images/ultimate-portal/admin-main/
			'url' => 'action=admin;area=download',
			'settings' => array(
				'enable' => !empty($ultimateportalSettings['download_enable']),
			),
			//Very important this part.
			'section' => 'enable_download',
		),
		//End Download Module		
		// Internal Page Module
		'ipage_enable' => array(
			'title' =>	$txt['ipage_enable'],	  
			'desc' => '',
			'images' => 'internal-page.png', //located in default/images/ultimate-portal/admin-main/
			'url' => 'action=admin;area=internal-page',
			'settings' => array(
				'enable' => !empty($ultimateportalSettings['ipage_enable']),
			),
			//Very important this part.
			'section' => 'enable_ipage',
		),
		//End Internal Page Module
		// About Us Module
		'about_us_enable' => array(
			'title' =>	$txt['up_about_enable'],	  
			'desc' => '',
			'images' => 'up-aboutus.png', //located in default/images/ultimate-portal/admin-main/
			'url' => 'action=admin;area=up-aboutus',
			'settings' => array(
				'enable' => !empty($ultimateportalSettings['about_us_enable']),
			),
			//Very important this part.
			'section' => 'enable_about_us',
		),
		//End About Us Module
		// FAQ Module
		'faq_enable' => array(
			'title' =>	$txt['up_faq_enable'],	  
			'desc' => '',
			'images' => 'up-faq.png', //located in default/images/ultimate-portal/admin-main/
			'url' => 'action=admin;area=up-faq',
			'settings' => array(
				'enable' => !empty($ultimateportalSettings['faq_enable']),
			),
			//Very important this part.
			'section' => 'enable_faq',
		),
		//End FAQ Module		
	);
}

//Load Multiblocks
function MultiBlocksLoads()
{
	global $settings, $db_prefix, $context, $scripturl, $txt, $smcFunc;
	global $ultimateportalSettings;
	
	$context['mb_view'] = false;
	
	$request = $smcFunc['db_query']('', "
					SELECT id, title, blocks, position, design, mbk_title, mbk_collapse, mbk_style, enable
					FROM {db_prefix}up_multiblock
					ORDER BY id ASC");
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['mb_view'] = true;
		if ($context['mb_view'])
		{
			$context['multiblocks'][] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'blocks' => $row['blocks'],
				'position' => $row['position'],
				'design' => $row['design'],
				'mbk_title' => $row['mbk_title'],
				'mbk_collapse' => $row['mbk_collapse'],
				'mbk_style' => $row['mbk_style'],
				'enable' => $row['enable'],
				'edit' => '<a href="'. $scripturl .'?action=admin;area=multiblock;sa=edit;id='. $row['id'] .';'. $context['session_var'] .'=' . $context['session_id'].'"><img alt="" title="'. $txt['ultport_button_edit'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png"/></a>',
				'delete' => '<a style="color:red" onclick="return makesurelink()" href="'. $scripturl .'?action=admin;area=multiblock;sa=delete;id='. $row['id'] .';'. $context['session_var'] .'=' . $context['session_id'].'"><img alt="" title="'. $txt['ultport_button_delete'] .'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png"/></a>',			
			);
		}
	}
	$smcFunc['db_free_result']($request);
}

function LoadsBlocksForMultiBlock($view = false) {
	global $db_prefix, $context, $user_info, $ID_MEMBER;
	global $smcFunc;

	$result = $smcFunc['db_query']('',"SELECT id, file, title, icon, position, progressive, active, personal, content, perms, bk_collapse, bk_no_title, bk_style
					FROM {db_prefix}ultimate_portal_blocks 
					". ($view ? "WHERE position in ('left', 'right', 'center')" : "") ."
					ORDER BY progressive");

	while( $row = $smcFunc['db_fetch_assoc']($result) ) {
		$context['blocks'][] = $row;
	}
}

//Load Specific Multiblocks
function SpecificMultiBlocks($id)
{
	global $settings, $db_prefix, $context, $scripturl, $txt, $smcFunc;
	global $ultimateportalSettings;
	
	$context['mb_view'] = false;
	
	$request = $smcFunc['db_query']('', "
					SELECT id, title, blocks, position, design, mbk_title, mbk_collapse, mbk_style, enable
					FROM {db_prefix}up_multiblock
					WHERE id = $id
					ORDER BY id ASC");
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['mb_view'] = true;
		if ($context['mb_view'])
		{
			$context['multiblocks'][$id] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'blocks' => $row['blocks'],
				'position' => $row['position'],
				'design' => $row['design'],
				'mbk_title' => $row['mbk_title'],
				'mbk_collapse' => $row['mbk_collapse'],
				'mbk_style' => $row['mbk_style'],
				'enable' => $row['enable'],
			);			
		}
	}

	$smcFunc['db_free_result']($request);
	
	//Order of Blocks
	$id_blocks = explode(',', $context['multiblocks'][$id]['blocks']);
	foreach($id_blocks as $bk)
	{
		$rbk = $smcFunc['db_query']('', "
						SELECT mbk_view
						FROM {db_prefix}ultimate_portal_blocks
						WHERE id = $bk");
		while ($row = $smcFunc['db_fetch_assoc']($rbk))
		{
			$context['oblocks'][$bk] = array(
				'mbk_view' => $row['mbk_view'],
			);
		}
	}
	$smcFunc['db_free_result']($rbk);
}

?>