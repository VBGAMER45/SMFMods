<?php/*---------------------------------------------------------------------------------*	Tiny Comments System												 		  **	Version 1.0																	  **	Author: manix																  **	Copyright 2013												        		  **	Powered by www.smfsimple.com												  ***********************************************************************************/if (!defined('SMF'))	die('Hacking attempt...');//-Hooks Partfunction tcs_action(&$actionArray) //Action!{	$actionArray['comment'] = array('Comment.php', 'DoComment');}function tcs_admin_area(&$admin_areas){	global $txt;	loadLanguage('Comment');	$admin_areas['config']['areas']['comments'] = array(		'label' => $txt['tcs_admin_btn'],		'file' => 'Comment.php',		'function' => 'TcsAdmin',		'icon' => 'post_moderation_moderate.gif',		'subsections' => array(			'main' => array($txt['tcs_admin_btn']),			'permissions' => array($txt['tcs_admin_perm_group'], 'manage_permissions'),			'permissionsboard' => array($txt['tcs_admin_perm_board'], 'manage_permissions'),		),	);}function tcs_Buffer($buffer){	global $forum_copyright, $context, $sourcedir;		require_once($sourcedir . '/QueryString.php');	ob_sessrewrite($buffer);		if (empty($context['deletforum']))	{		$context['deletforum'] = base64_decode('IHwgPGEgc3R5bGU9ImZvbnQtc2l6ZToxMHB4OyIgaHJlZj0iaHR0cDovL3d3dy5zbWZzaW1wbGUuY29tIiB0aXRsZT0iVG9kbyBwYXJhIHR1IGZvcm8gU01GIj5Nb2RzIGJ5IFNNRlNpbXBsZS5jb208L2E+');			$buffer = str_replace($forum_copyright, $forum_copyright.$context['deletforum'],$buffer);				}	return $buffer;}function tcs_permissions(&$permissionGroups, &$permissionList){	global $context;	loadLanguage('Comment');	$context['non_guest_permissions'][] = 'tcs_do_comments';	$permissionList['membergroup']['tcs_do_comments'] = array(false, 'topic', 'participate');}function TcsAdmin(){	global $context, $txt;	if (!allowedTo('admin_forum'))		isAllowedTo('admin_forum');	loadLanguage('Comment');	loadTemplate('Comment');	$subActions = array(		'main' => 'TcsAdminGeneral',		'permissions' => 'TcsShowPerms',		'permissionsboard' => 'TcsShowPermsBoard',	);	$context[$context['admin_menu_name']]['tab_data'] = array(		'title' => $txt['tcs_admin_btn'],		'description' => $txt['tcs_admin_desc'],		'tabs' => array(			'main' => array(				'description' => $txt['tcs_admin_desc'],			),		),	);	$sa = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';	$context['sub_action'] = $sa;	if (!empty($subActions[$sa]))		call_user_func($subActions[$sa]);}function TcsAdminGeneral(){	global $context, $txt;	if (!empty($_POST['save']))	{		$tcs_settings = array(			'tcs_enabled' => !empty($_POST['tcs_enabled']) ? '1' : '0',			'tcs_initial_number' => !empty($_POST['tcs_initial_number']) ? (int) $_POST['tcs_initial_number'] : 10,			'tcs_see_more_number' => !empty($_POST['tcs_see_more_number']) ? (int) $_POST['tcs_see_more_number'] : 5,			'tcs_characters_limit' => !empty($_POST['tcs_characters_limit']) ? (int) $_POST['tcs_characters_limit'] : 0,			'tcs_bbc' => !empty($_POST['tcs_bbc']) ? '1' : '0',			'tcs_smiles' => !empty($_POST['tcs_smiles']) ? '1' : '0',		);		updateSettings($tcs_settings);		redirectexit('action=admin;area=comments;');	}	$context['sub_template'] = 'tcs_settings';	$context['page_title'] = $txt['tcs_admin_btn'];}function TcsShowPerms($return_config = false){	global $context, $sourcedir, $txt, $scripturl;	require_once($sourcedir . '/ManageServer.php');	isAllowedTo('manage_permissions');	loadLanguage('Comment');	$config_vars = array(		array('title', 'tcs_admin_perm_group'),		array('permissions', 'tcs_do_comments', 'subtext' => $txt['permissionname_tcs_can_do_comments']),	);	if ($return_config)		return $config_vars;	$context['page_title'] = $txt['tcs_admin_perm_group'];	$context['sub_template'] = 'show_settings';	if (isset($_GET['save']))	{		checkSession();		saveDBSettings($config_vars);		redirectexit('action=admin;area=comments;sa=permissions');	}	$context['post_url'] = $scripturl . '?action=admin;area=comments;save;sa=permissions';	prepareDBSettingContext($config_vars);}function TcsShowPermsBoard(){	global $context, $txt, $smcFunc;	loadLanguage('Comment');	if (isset($_POST['save']) && !empty($_POST['boards']))	{		foreach ($_POST['boards'] as $i => $v)			 if (!is_numeric($_POST['boards'][$i]))				unset($_POST['boards'][$i]);		$smcFunc['db_query']('', "			UPDATE {db_prefix}boards			SET tcs_board_on = 1			WHERE id_board IN ({array_int:board})",			array(				'board' => $_POST['boards'],			)		);		$smcFunc['db_query']('', "			UPDATE {db_prefix}boards			SET tcs_board_on = 0			WHERE id_board NOT IN ({array_int:board})",			array(				'board' => $_POST['boards'],			)		);		redirectexit('action=admin;area=comments;sa=permissionsboard');	}	if (isset($context['jump_to']))		return;	$request = $smcFunc['db_query']('', "		SELECT c.name AS cat_name, c.id_cat, b.id_board, b.name AS board_name, b.child_level		FROM {db_prefix}boards AS b		LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)		WHERE {query_see_board}"	);	$context['jump_to'] = array();	$this_cat = array('id' => -1);	while ($row = $smcFunc['db_fetch_assoc']($request))	{		if ($this_cat['id'] != $row['id_cat'])		{			$this_cat = &$context['jump_to'][];			$this_cat['id'] = $row['id_cat'];			$this_cat['name'] = $row['cat_name'];			$this_cat['boards'] = array();		}		$this_cat['boards'][] = array(			'id' => $row['id_board'],			'name' => $row['board_name'],			'child_level' => $row['child_level'],			'is_current' => isset($context['current_board']) && $row['id_board'] == $context['current_board']		);	}	$smcFunc['db_free_result']($request);	$request = $smcFunc['db_query']('', '		SELECT id_board, tcs_board_on		FROM {db_prefix}boards		WHERE tcs_board_on = {int:active}',		array(			'active' => 1,		)	);	$context['tcs_board_enabled'] = array();	while ($row = $smcFunc['db_fetch_assoc']($request))		$context['tcs_board_enabled'][] = $row['id_board'];	$smcFunc['db_free_result']($request);	$context['sub_template'] = 'tcs_permissions_board';	$context['page_title'] = $txt['tcs_admin_perm_board'];}function tcs_load_theme(){	global $context, $settings, $modSettings;	loadLanguage('Comment');	if (!empty($modSettings['tcs_enabled']) && !empty($_REQUEST['topic']))	{		$context['html_headers'] .='		<link rel="stylesheet" type="text/css" href="'.$settings['default_theme_url'].'/css/comments.css" />		<script type="text/javascript">window.jQuery || document.write(unescape(\'%3Cscript src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"%3E%3C/script%3E\'))</script>		<script type="text/javascript" src="'. $settings['default_theme_url']. '/scripts/comments.js?fin20"></script>		<script type="text/javascript">			var tcs = jQuery.noConflict();        </script>';	}}function DoComment(){	global $context;    $subActions = array(		'insert' => 'InsertComment',		'delete' => 'DeleteComment',	);	$sa = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'insert';	$context['sub_action'] = $sa;	if (!empty($subActions[$sa]))		call_user_func($subActions[$sa]);}function InsertComment(){    global $context, $smcFunc, $modSettings, $memberContext, $txt;		if(!empty($modSettings['tcs_enabled']))	{		loadLanguage('Comment');				//Check for some errors..		if (!allowedTo('tcs_do_comments'))				$return = array('error' => $txt['tcs_error_do_comments']);				if (empty($_POST['comment']))			$return = array('error' => $txt['tcs_error_empty_comment']);				if (empty($_POST['message']) || empty($_POST['topic']))			$return = array('error' => $txt['tcs_error_empty_msg_topic']);				if (!empty($modSettings['tcs_characters_limit']) && $smcFunc['strlen']($_POST['comment']) > $modSettings['tcs_characters_limit'])			$return = array('error' => $txt['tcs_error_max_characters']);					//If there is some error we need to return a message and exit..		if (!empty($return))		{			echo json_encode($return);			obExit(false);			}				$memid = $context['user']['id'];		loadMemberData($memid);		loadMemberContext($memid);		$context['comment']['body'] = iconv('UTF-8', $context['character_set'], $smcFunc['htmlspecialchars']($_POST['comment'], ENT_QUOTES));		$context['comment']['message'] = (int)$_POST['message'];		$context['comment']['topic'] = (int)$_POST['topic'];		$context['comment']['poster_time'] = time();		$context['comment']['modified_time'] = time();		$context['comment']['member'] = (int) $memid;		$smcFunc['db_insert']('insert',			'{db_prefix}comments',			array(				'body' => 'string', 'modified_time' => 'int', 'id_msg' => 'int', 'id_topic' => 'int', 'poster_time' => 'int', 'id_member' => 'int'			),			array(				$context['comment']['body'],				$context['comment']['modified_time'],				$context['comment']['message'],				$context['comment']['topic'],				$context['comment']['poster_time'],				$context['comment']['member']			),			array('id_comment')		);		$context['comment']['id'] = $smcFunc['db_insert_id']('{db_prefix}comments', 'id_comment');		$context['comment']['body'] = !empty($modSettings['tcs_bbc']) ? parse_bbc($context['comment']['body'], false) : $context['comment']['body']; //Parsing BBC?		if (!empty($modSettings['tcs_smiles'])) //Parsing Smileys?			parsesmileys($context['comment']['body']);		$return = array(			'body' => iconv($context['character_set'], 'UTF-8', $context['comment']['body']),			'id' => $context['comment']['id'],			'avatar' => $memberContext[$memid]['avatar']['image'],			'group_color' => !empty($memberContext[$memid]['group_color']) ? 'style="color:'.$memberContext[$memid]['group_color'].';"' : '',			'member_name' => $memberContext[$memid]['name'],			'member_id' => $memid,			'date' => timeformat($context['comment']['poster_time']),			'delete_url' => ($context['user']['is_admin'] || $context['user']['id'] == $memid) ? '<a href="#" class="delete_comment" name="' . $context['comment']['id'] . '">'.$txt['tcs_btn_delete'].'</a>' : '',			'error' => '',		);		echo json_encode($return);		obExit(false);	}}function DeleteComment(){    global $smcFunc, $user_info, $modSettings;		if(!empty($modSettings['tcs_enabled']) && !empty($_POST['id_comment']))	{			$can_delete = false;				if($user_info['is_admin'])			$can_delete = true;				else		{			$request = $smcFunc['db_query']('', '				SELECT id_member				FROM {db_prefix}comments				WHERE id_member = {int:user}				LIMIT 1',				array(					'user' => $user_info['id'],				)			);			if ($smcFunc['db_num_rows']($request) == 1)				$can_delete = true;						$smcFunc['db_free_result']($request);		}				if ($can_delete)		{			$id_comment = (int) $_POST['id_comment'];			if (!empty($id_comment))			{				$smcFunc['db_query']('', '					DELETE FROM {db_prefix}comments					WHERE id_comment = {int:id_comment}',					array(						'id_comment' => $id_comment,					)				);			}		}			}	obExit(false);}function loadPostComments($array_msg){    global $smcFunc, $modSettings, $scripturl, $settings;    $request = $smcFunc['db_query']('', '		SELECT c.*, m.id_member, m.real_name, m.id_group, mg.id_group, mg.online_color,		m.real_name AS member_name, m.avatar,		IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type		FROM {db_prefix}comments c		INNER JOIN {db_prefix}members AS m ON (c.id_member = m.id_member)		LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN m.id_group = {int:reg_mem_group} THEN m.id_post_group ELSE m.id_group END)		LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = c.id_member)		WHERE c.id_msg IN ({array_int:message_list})        ORDER BY c.id_comment ASC',		array(			'message_list' => $array_msg,			'reg_mem_group' => 0,		)	);    $comments = array();    while ($row = $smcFunc['db_fetch_assoc']($request))    {        $body = !empty($modSettings['tcs_bbc']) ? parse_bbc($row['body'], false) : $row['body']; //Parsing BBC?        if (!empty($modSettings['tcs_smiles'])) //Parsing Smileys?			parsesmileys($body);        $comments[$row['id_msg']][] = array(            'body'=> $body,			'member_name' => $row['real_name'],			'id_member' => $row['id_member'],			'group_color' => (!empty($row['online_color'])) ? 'style="color:' . $row['online_color'] . ';"' : '',			'member_avatar' => (empty($row['avatar'])) ? ($row['id_attach'] > 0 ? '<img width="32px" height="32px" src="' . (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" border="0" />' : '<img width="32px" height="32px" src="' . $settings['images_url'] . '/noavatar.png" alt="" />') : (stristr($row['avatar'], 'http://') ? '<img width="32px" height="32px" src="' . $row['avatar'] . '" alt="" border="0" />' : '<img width="32px" height="32px" src="' . $modSettings['avatar_url'] . '/' . $smcFunc['htmlspecialchars']($row['avatar']) . '" alt="" border="0" />'),            'poster_time' => timeformat($row['poster_time']),            'id' => $row['id_comment'],					);    }    $smcFunc['db_free_result']($request);    return $comments;}function removeCommentIfPostDelete($id_topic){	global $smcFunc;		if (is_array($id_topic))			$smcFunc['db_query']('', '			DELETE FROM {db_prefix}comments			WHERE id_topic IN({array_int:id})',			array(				'id' => $id_topic,			)		);			else	{		$id_topic = !empty($id_topic) ? (int) $id_topic : '';				if (!empty($id_topic))			$smcFunc['db_query']('', '			DELETE FROM {db_prefix}comments			WHERE id_topic = {int:id}',			array(				'id' => $id_topic,			)		);	}}