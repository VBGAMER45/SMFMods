<?php
/*---------------------------------------------------------------------------------
*	Broken Links List															  *
*	Version 1.1																	  *
*	Author: 4kstore																  *
*	Powered by www.smfsimple.com												  *
**********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');
function BrokenLinksListAdmin()
{
	global $context, $txt;
	isAllowedTo('admin_forum');
	loadTemplate('BrokenLinksList');
	loadLanguage('BrokenLinkList');
	$subActions = array(
		'main' => 'ShowSettingsMain',
		'deleteok' => 'deleteok',
	);
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['bll_admin_menu_button'] . ' - ' . $txt['bll_admin_settings'],
		'description' => $txt['bll_admin_settings_desc'],
		'tabs' => array(
			'main' => array(
				'description' => $txt['bll_admin_settings_desc'],
			),
		),
	);
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';
	$context['sub_action'] = $_REQUEST['sa'];
	$subActions[$_REQUEST['sa']]();
}
function ShowSettingsMain() //Admin Main
{
	global $context, $txt;
	loadLanguage('BrokenLinkList');
	
	if (!empty($_POST['save']))
	{
		checkSession('');
		$bll_settings = array(
			'bll_enabled' => !empty($_POST['bll_enabled']) ? '1' : '0', //ENABLED MOD?
			'bll_titleset' => !empty($_POST['bll_titleset']) ? $_POST['bll_titleset'] : '', //TITLE OF THE TABLE
			'bll_senderid' => !empty($_POST['bll_senderid']) ? $_POST['bll_senderid'] : '', //PM SENDER ID
			'bll_pm_title' => !empty($_POST['bll_pm_title']) ? $_POST['bll_pm_title'] : '', //PM TITLE
			'bll_pm_text' => !empty($_POST['bll_pm_text']) ? $_POST['bll_pm_text'] : '', //PM TEXT
			'bll_warning_link_time' => !empty($_POST['bll_warning_link_time']) ? $_POST['bll_warning_link_time'] : '', //TIME TO BE WARNING
			'bll_warning_link_color' => !empty($_POST['bll_warning_link_color']) ? $_POST['bll_warning_link_color'] : '', //COLOR LINK WARNING
		);
		updateSettings($bll_settings);
		redirectexit('action=admin;area=brokenlinkslist;sesc='.$context['session_id']);
	}
	$context['sub_template'] = 'bll_settings';
	$context['page_title'] = $txt['bll_admin_menu_button'];
}
function BrokenLinksList()
{
	global $modSettings;
	$subActions = array(
		'edit' => 'edit',
		'delete' => 'delete',
		'report' => 'reportingLinks'
	);
	// Follow the sa or just go to main function
	$sa = $_GET['sa'];
	
	if (!empty($subActions[$sa]))
		$subActions[$sa]();
		
	loadTemplate('BrokenLinksList');
	
	if (empty($modSettings['bll_enabled']))
		redirectexit('');
}
function BrokenLinksList2()
{
	global  $context, $txt, $modSettings;
	loadLanguage('BrokenLinkList');
	loadTemplate('BrokenLinksList');
	
	if(empty($modSettings['bll_enabled']) || !allowedTo('brokenlinklist'))
		redirectexit('');
		
	infoTopic();
	$context['page_title'] = !empty($modSettings['bll_titleset']) ? $modSettings['bll_titleset'] : $txt['bll_admin_menu_button'];
	$context['sub_template'] = 'ShowBll';
}
function infoTopic()
{
	global $smcFunc, $context, $user_info,$scripturl,$settings, $modSettings, $txt,$sourcedir;
	loadLanguage('BrokenLinkList');
	require_once($sourcedir . '/Display.php');
	//START SORT AND INDEX CONTENT!
	$sort_methods = array(
		'id' =>  array(
			'down' => 'br.id_report ASC',
			'up' => 'br.id_report DESC'
		),
		'subject' =>  array(
			'down' => 'm.subject ASC',
			'up' => 'm.subject DESC'
		),
		'date' => array(
			'down' => 'br.reported_time ASC',
			'up' => 'br.reported_time DESC'
		),
		'WhoReports' => array(
			'down' => 'br.reported_name ASC',
			'up' => 'br.reported_name DESC'
		),
		'status' => array(
			'down' => 'br.status ASC',
			'up' => 'br.status DESC'
		),
		'Notes' => array(
			'down' => 'br.notes ASC',
			'up' => 'br.notes DESC'
		),
	);

	$context['columns'] = array(
		'id' => array(
			'label' => $txt['bll_report_links_id'],
			'sortable' => true
		),
		'subject' => array(
			'label' => $txt['bll_report_links_subject'],
			'sortable' => true
		),

		'WhoReports' => array(
			'label' => $txt['bll_report_links_name'],
			'sortable' => true
		),
		'Notes' => array(
			'label' => $txt['bll_report_links_notes'],
			'sortable' => true
		),
		'date' => array(
			'label' => $txt['bll_report_links_date'],
			'sortable' => true
		),
		'status' => array(
			'label' => $txt['bll_report_links_status'],
			'sortable' => true
		),
	);

	countBrokenLinks();
	if ($context['broken_links_count'] || $user_info['is_admin'] || $context['user']['can_mod'])
		$context['columns']['moderate'] = array(
			'label' => $txt['bll_report_links_moderate'],
			'sortable' => true,
		);

	if (empty($_REQUEST['sort']) || empty($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = $_REQUEST['desc'] = 'date';

	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=brokenlinkslist2;sort=' . $col;

		if (empty($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	$context['inforows'] = array();
	$inforows = array();
	$request = $smcFunc['db_query']('', '
		SELECT m.id_topic,m.id_msg,m.id_member,m.subject,m.poster_name,
			   br.id_topic, br.id_msg, br.reported_time, br.reported_name, br.status, br.notes, br.id_member_reported, br.id_report
		FROM {db_prefix}messages AS m
		INNER JOIN {db_prefix}broken_links_list AS br ON (m.id_msg = br.id_msg)
		ORDER BY '.$sort_methods[$_REQUEST['sort']][$context['sort_direction']].''
	);

	$context['canMod'] = false;
	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		$inforows = &$context['inforows'][];
		$inforows['id_report'] = $row['id_report'];
		$inforows['id_topic'] = $row['id_topic'];
		$inforows['id_member'] = $row['id_member'];
		$inforows['subject'] = $row['subject'];
		$inforows['poster_name'] = $row['poster_name'];
		$inforows['notes'] = !empty($row['notes']) ? $row['notes'] : "";
		$inforows['id_member_reported'] = $row['id_member_reported'];
		$inforows['reported_time'] = timeformat($row['reported_time']);
		$inforows['reported_name_href'] = '<a href="'. $scripturl .'?action=profile;u='. $row['id_member_reported'] .'">'.$row['reported_name'].'</a>';
		$inforows['status'] = $row['status'];
		$inforows['posterhref'] = '<a href="'. $scripturl .'?action=profile;u='. $row['id_member'] .'">'.$row['poster_name'].'</a>';
		$inforows['subjecthref'] = '<a href="'. $scripturl .'?topic='. $row['id_topic'] .'.msg'.$row['id_msg'].'#msg'.$row['id_msg'].'">'.$row['subject'].'</a>';
		$inforows['canMod'] = ($row['id_member'] == $user_info['id'] || $user_info['is_admin'] || $context['user']['can_mod']) ? true : false;
		
		if ($inforows['canMod']) //Si puede moderar o administrar mostramos el delete y edit.
		{
			$inforows['edit'] = '<a href="'. $scripturl .'?action=brokenlinkslist;sa=edit;id='. $row['id_report'] .';sesc=' . $context['session_id'].'"><img src="'. $settings['default_images_url'] . '/buttons/modify.gif"  alt="'. $txt['bll_report_links_moderate_edit'] .'" title="'. $txt['bll_report_links_moderate_edit'] .'"/></a>';
			$inforows['delete'] = '<a  onclick="return confirm(\''.$txt['bll_report_links_moderate_delete_confirm'].'\');" href="'. $scripturl .'?action=brokenlinkslist;sa=delete;id='. $row['id_report'] .';sesc=' . $context['session_id'].'"><img src="'. $settings['default_images_url'] . '/buttons/delete.gif" alt="'. $txt['bll_report_links_moderate_delete'] .'" title="'. $txt['bll_report_links_moderate_delete'] .'"/></a>';
		}		
		else //No puede, no mostramos nada.
		{
			$inforows['edit'] = '';
			$inforows['delete'] = '';
		}
		$inforows['iconstatus'] = '<img style="vertical-align:middle;" src="'. $settings['images_url']. '/warn.gif" alt="'.$txt['bll_report_links_status_icon'].'" title="'.$txt['bll_report_links_status_icon'].'" />';
		
		if ($inforows['status'] == 1)//Algunos cambios para el warn STATUS
		{
			if (!empty($modSettings['bll_warning_link_color']))
				$inforows['subjecthref'] = '<a style="color:'. $modSettings['bll_warning_link_color']. ';" href="'. $scripturl .'?topic='. $row['id_topic'] .'.msg'.$row['id_msg'].'#msg'.$row['id_msg'].'">'.$row['subject'].'</a>';
			else
				$inforows['subjecthref'] = '<a href="'. $scripturl .'?topic='. $row['id_topic'] .'.msg'.$row['id_msg'].'#msg'.$row['id_msg'].'">'.$row['subject'].'</a>';
			$inforows['iconstatus'] = '<img src="'. $settings['images_url']. '/warning_mute.gif" alt="'.$txt['bll_report_links_status_icon1'].'" title="'.$txt['bll_report_links_status_icon1'].'" /></a>';
		}
		if ($inforows['status'] == 2)//Algunos cambios para el OK status
		{
			$inforows['subjecthref'] = '<a style="color:green" href="'. $scripturl .'?topic='. $row['id_topic'] .'.msg'.$row['id_msg'].'#msg'.$row['id_msg'].'">'.$row['subject'].'</a>';
			$inforows['iconstatus'] = '<img src="'. $settings['images_url']. '/warning_watch.gif" alt="'.$txt['bll_report_links_status_icon2'].'" title="'.$txt['bll_report_links_status_icon2'].'" /></a>';
		}
		$context['canMod'] = $inforows['canMod'];
	}
	$smcFunc['db_free_result']($request);
}
function reportingLinks() //Time to report msg!
{
	global $context, $scripturl, $smcFunc, $txt, $user_info, $modSettings, $sourcedir;
	isAllowedTo('brokenlinklist');
	loadLanguage('BrokenLinkList');
	$id_msg =  !empty($_REQUEST['msg']) ? (int) $smcFunc['db_escape_string']($_REQUEST['msg']) : '';
	$id_topic = !empty($_REQUEST['topic']) ? (int) $smcFunc['db_escape_string']($_REQUEST['topic']) : '';
	$id_member2 = !empty($_REQUEST['member']) ? (int) $smcFunc['db_escape_string']($_REQUEST['member']) : '';

	if(!empty($id_msg) && !empty($id_member2) && !empty($id_topic))
	{
		$context['bll_id_msg'] = $id_msg;
		$context['bll_id_topic'] = $id_topic;
		$context['bll_id_member'] = $id_member2;
		
		$result = $smcFunc['db_query']('', '
			SELECT id_report
			FROM {db_prefix}broken_links_list
			WHERE id_msg = {int:id}
			LIMIT 1',
			array(
				'id' => $id_msg,
			)
		);
		
		if ($smcFunc['db_num_rows']($result) == 1)//Already in the list
		{
			list($id_report) = $smcFunc['db_fetch_row']($result);
			fatal_lang_error('bll_error_id', false, array($id_report));
		}
		
		if (!empty($_POST['save']) && $smcFunc['db_num_rows']($result) == 0)
		{
			checkSession('post');
			$notes = !empty($_POST['notes']) ? $smcFunc['htmlspecialchars']($_POST['notes'],ENT_QUOTES) : '';
			$reported_time = time();
			$reported_name = $context['user']['name'];
			$id_member_reported = $user_info['id'];
			$status = 0;

			$smcFunc['db_insert']('replace',
				'{db_prefix}broken_links_list',
				array(
					'id_msg' => 'int', 'id_topic' => 'int', 'reported_time' => 'int', 'reported_name' => 'string', 'status' => 'int', 'notes' => 'string', 'id_member_reported' => 'int', 'id_member' => 'int',
				),
				array(
					$id_msg,$id_topic,$reported_time,$reported_name,$status,$notes,$id_member_reported,$id_member2
				),
				array('id')
			);
			// Send PM!
			include_once($sourcedir . '/Subs-Post.php'); //Get info ::)
			$request = $smcFunc['db_query']('', '
				SELECT id_member,subject,poster_name,id_msg
				FROM {db_prefix}messages
				WHERE id_msg = {int:id}
				LIMIT 1',
				array(
					'id' => $id_msg,
				)
			);
			list($id_member,$subject_topic,$poster_name) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			$recipients = array(
				'to' => array($id_member),
				'bcc' => array()
			);
			$subject = (!empty($modSettings['bll_pm_title'])) ? $modSettings['bll_pm_title'] : $txt['bll_admin_menu_button'];
			$message = (!empty($modSettings['bll_pm_text'])) ? $modSettings['bll_pm_text'] : $txt['bll_pm_text_default'];
			if(!empty($notes))
			{
				$message .='
'.$txt['bll_report_links_notes'].' '.$notes.'';
			}
			$outbox_store = false;
			$from = array (
				'id' => (!empty($modSettings['bll_senderid'])) ? $modSettings['bll_senderid'] : $user_info['id'],
				'name' =>'',
				'username' => '',
			);

			//Matches (?)
			$author_name = $poster_name;
			$subjecthref = '[url='. $scripturl .'?topic='.$id_topic.'.msg'.$id_msg.'#msg'.$id_msg.']'.$subject_topic.'[/url]';
			$messagetoTransform  = $message;
			$search = array("(member_name)", "(subject_topic)","(reported_name)");
			$finalmessage   = array($author_name, $subjecthref, $reported_name);
			$newphrase = str_replace($search, $finalmessage, $messagetoTransform);

			if(!empty($message)) // IF have something send PM!
			sendpm($recipients, $subject, $newphrase, $outbox_store, $from);

			redirectexit('action=brokenlinkslist2');
		}
		$smcFunc['db_free_result']($result);
	}
	$context['sub_template'] = 'reportinglinks';
	$context['page_title'] = $txt['bll_admin_menu_button'];
}
function delete() // Bye bye reports!
{
	global $smcFunc, $context;
	checkSession('get');
	
	$id = !empty($_REQUEST['id']) ? (int) $smcFunc['db_escape_string']($_REQUEST['id']) : '';
	if(!empty($id))
	{
		canmod($id);
		
		if($context['caneditdel'])
		{
			$smcFunc['db_query']('',"
				DELETE FROM {db_prefix}broken_links_list
				WHERE id_report = {int:id}
				LIMIT 1",
				array(
					'id' => $id,
				)
			);
		}
	}
	redirectexit('action=brokenlinkslist2');
}
function edit()
{
	global $context, $txt, $smcFunc, $user_info;
	loadLanguage('BrokenLinkList');
	$context['sub_template'] = 'bll_edit';
	$context['page_title'] = $txt['bll_report_links_moderate_edit'];

	if (!empty($_POST['save']))
	{
		checkSession('post');
		$id = !empty($_POST['id']) ? (int)$smcFunc['db_escape_string']($_POST['id']) : '';
		$notes = $smcFunc['htmlspecialchars']($_POST['notes'],ENT_QUOTES);
		$status = $smcFunc['db_escape_string']($_POST['status']);
		if(!empty($id))
		{
			canmod($id);
			if($context['caneditdel'])
			{
				$smcFunc['db_query']('',"
					UPDATE {db_prefix}broken_links_list
					SET	notes  = {string:notes},
						status = {int:status}
					WHERE id_report = {int:id}
					LIMIT 1",
					array(
						'id' => $id,
						'notes' => $notes,
						'status' => $status,
					)
				);
			}
		}
		redirectexit('action=brokenlinkslist2');
	}
	$id = !empty($_REQUEST['id']) ? (int) $smcFunc['db_escape_string']($_REQUEST['id']) : '';
	if(!empty($id))
		loadEdit($id);
}
function loadEdit($id)
{
	global $smcFunc, $context, $user_info;
	$context['bllrows'] = array();
	$bll = array();
	$context['canedit'] = false;
	$request = $smcFunc['db_query']('', "
		SELECT id_report, status, notes, id_member
		FROM {db_prefix}broken_links_list
		WHERE id_report = {int:id}
		LIMIT 1",
		array(
			'id' => $id,
		)
	);
	 while($row = $smcFunc['db_fetch_assoc']($request))
	 {
		$bllrows = &$context['bllrows'][];
		$bllrows['notes'] = !empty($row['notes']) ? $row['notes'] : '';
		$bllrows['id_report'] = $row['id_report'];
		$bllrows['status'] = $row['status'];
		$context['canedit'] = ($row['id_member'] == $user_info['id'] || $user_info['is_admin'] || $context['user']['can_mod']) ? true : false;
	 }
	$smcFunc['db_free_result']($request);
}
function countBrokenLinks() //called from Subs for use the counter in the menu
{
	global $context, $smcFunc, $user_info;
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(id_member)
			FROM {db_prefix}broken_links_list
			WHERE id_member = {int:member}
			AND status <> {int:stat}',
			array(
				'member' => $user_info['id'],
				'stat' => 2,
			)
		);
	list ($context['broken_links_count']) = $smcFunc['db_fetch_row']($request);
}
function deleteok() // delete all report in OK status called from admin panel
{
	global $context, $smcFunc;
	$status = 2;
	$smcFunc['db_query']('',"
		DELETE FROM {db_prefix}broken_links_list
		WHERE status = {int:status}",
		array(
			'status' => $status,
		)
	);
	redirectexit('action=admin;area=brokenlinkslist;sesc='.$context['session_id']);
}
function canmod($id)
{
	global $context, $smcFunc, $user_info;
	$id = !empty($id) ? (int) $id : '';
	
	if(!empty($id))
	{
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(id_member)
			FROM {db_prefix}broken_links_list
			WHERE id_member = {int:member}
			AND id_report = {int:id}',
			array(
				'member' => $user_info['id'],
				'id' => $id,
			)
		);
		list ($context['caneditdel']) = $smcFunc['db_fetch_row']($request);
		if (empty($context['caneditdel']))
		$context['caneditdel'] = ($user_info['is_admin'] || $context['user']['can_mod']) ? true : false;
	}
}