<?php

/**
 * Global Announcements - Main controller
 *
 * Handles public viewing, commenting, and admin management of global announcements.
 *
 * @package GlobalAnnouncements
 * @license MIT
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Main public action dispatcher.
 */
function GlobalAnnouncementsMain()
{
	global $modSettings;

	// Must be enabled for public actions.
	if (empty($modSettings['globalannouncements_enabled']))
		fatal_lang_error('globalannouncements_error_not_enabled', false);

	loadLanguage('GlobalAnnouncements');
	loadTemplate('GlobalAnnouncements');
	loadCSSFile('globalannouncements.css', array('default_theme' => true, 'minimize' => true));

	$sa = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : 'view';

	switch ($sa)
	{
		case 'view':
			GlobalAnnouncementsView();
			break;
		case 'comment':
			GlobalAnnouncementsAddComment();
			break;
		case 'editcomment':
			GlobalAnnouncementsEditComment();
			break;
		case 'deletecomment':
			GlobalAnnouncementsDeleteComment();
			break;
		default:
			GlobalAnnouncementsView();
			break;
	}
}

/**
 * View a single announcement with comments.
 */
function GlobalAnnouncementsView()
{
	global $context, $smcFunc, $user_info, $scripturl, $txt, $modSettings, $sourcedir;

	$aid = isset($_REQUEST['aid']) ? (int) $_REQUEST['aid'] : 0;
	if ($aid <= 0)
		fatal_lang_error('globalannouncements_error_not_found', false);

	// Load the announcement.
	$request = $smcFunc['db_query']('', '
		SELECT id_announcement, id_member, member_name, title, body, enabled, allow_comments,
			boards, groups, views, num_comments, created_at, updated_at
		FROM {db_prefix}global_announcements
		WHERE id_announcement = {int:aid}
		LIMIT 1',
		array(
			'aid' => $aid,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('globalannouncements_error_not_found', false);
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Check if enabled (unless user has manage permission).
	if (empty($row['enabled']) && !allowedTo('globalannouncements_manage'))
		fatal_lang_error('globalannouncements_error_not_found', false);

	// Check membergroup visibility.
	if (!empty($row['groups']) && !allowedTo('globalannouncements_manage'))
	{
		$allowed_groups = explode(',', $row['groups']);
		$user_groups = array_map('strval', $user_info['groups']);

		if (empty(array_intersect($allowed_groups, $user_groups)))
			fatal_lang_error('globalannouncements_error_no_permission', false);
	}

	// Increment view counter.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}global_announcements
		SET views = views + 1
		WHERE id_announcement = {int:aid}',
		array(
			'aid' => $aid,
		)
	);

	// Log the view (insert or update).
	if (!$user_info['is_guest'])
	{
		$smcFunc['db_insert']('replace',
			'{db_prefix}global_announcement_log',
			array(
				'id_announcement' => 'int',
				'id_member' => 'int',
				'viewed_at' => 'int',
			),
			array(
				$aid,
				$user_info['id'],
				time(),
			),
			array('id_announcement', 'id_member')
		);
	}

	// Parse BBC in body.
	$row['body'] = parse_bbc($row['body']);

	// Set up context.
	$context['announcement'] = array(
		'id' => $row['id_announcement'],
		'title' => $row['title'],
		'body' => $row['body'],
		'author' => array(
			'id' => $row['id_member'],
			'name' => $row['member_name'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['member_name'] . '</a>',
		),
		'views' => $row['views'] + 1,
		'num_comments' => $row['num_comments'],
		'allow_comments' => !empty($row['allow_comments']),
		'created_at' => timeformat($row['created_at']),
		'updated_at' => !empty($row['updated_at']) ? timeformat($row['updated_at']) : '',
		'enabled' => !empty($row['enabled']),
	);

	// Can comment?
	$context['can_comment'] = !empty($row['allow_comments']) && allowedTo('globalannouncements_comment') && !$user_info['is_guest'];
	$context['can_manage'] = allowedTo('globalannouncements_manage');

	// Load comments with pagination.
	$per_page = !empty($modSettings['globalannouncements_per_page']) ? (int) $modSettings['globalannouncements_per_page'] : 10;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	// Total comments.
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}global_announcement_comments
		WHERE id_announcement = {int:aid}',
		array(
			'aid' => $aid,
		)
	);
	list($total_comments) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=globalannouncements;sa=view;aid=' . $aid, $start, $total_comments, $per_page);
	$context['start'] = $start;

	// Fetch comments.
	$context['comments'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT c.id_comment, c.id_announcement, c.id_member, c.member_name, c.body,
			c.created_at, c.updated_at, c.modified_by
		FROM {db_prefix}global_announcement_comments AS c
		WHERE c.id_announcement = {int:aid}
		ORDER BY c.created_at ASC
		LIMIT {int:start}, {int:per_page}',
		array(
			'aid' => $aid,
			'start' => $start,
			'per_page' => $per_page,
		)
	);

	while ($comment = $smcFunc['db_fetch_assoc']($request))
	{
		$context['comments'][] = array(
			'id' => $comment['id_comment'],
			'author' => array(
				'id' => $comment['id_member'],
				'name' => $comment['member_name'],
				'href' => $scripturl . '?action=profile;u=' . $comment['id_member'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $comment['id_member'] . '">' . $comment['member_name'] . '</a>',
			),
			'body' => parse_bbc($comment['body']),
			'created_at' => timeformat($comment['created_at']),
			'updated_at' => !empty($comment['updated_at']) ? timeformat($comment['updated_at']) : '',
			'modified_by' => $comment['modified_by'],
			'can_edit' => allowedTo('globalannouncements_manage') || ($comment['id_member'] == $user_info['id'] && allowedTo('globalannouncements_comment')),
			'can_delete' => allowedTo('globalannouncements_manage') || ($comment['id_member'] == $user_info['id'] && allowedTo('globalannouncements_comment')),
		);
	}
	$smcFunc['db_free_result']($request);

	// Set up the BBC editor for commenting.
	if ($context['can_comment'])
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$editorOptions = array(
			'id' => 'comment_body',
			'value' => '',
			'width' => '100%',
			'height' => '200px',
			'preview_type' => 0,
		);
		create_control_richedit($editorOptions);
		$context['post_box_name'] = $editorOptions['id'];
	}

	// Linktree.
	$context['linktree'][] = array(
		'name' => $txt['globalannouncements_title'],
		'url' => $scripturl . '?action=globalannouncements',
	);
	$context['linktree'][] = array(
		'name' => $row['title'],
		'url' => $scripturl . '?action=globalannouncements;sa=view;aid=' . $aid,
	);

	$context['page_title'] = $row['title'] . ' - ' . $txt['globalannouncements_title'];
	$context['sub_template'] = 'globalannouncements_view';
}

/**
 * Process comment submission.
 */
function GlobalAnnouncementsAddComment()
{
	global $smcFunc, $user_info, $txt, $sourcedir;

	// Permission check.
	isAllowedTo('globalannouncements_comment');

	if ($user_info['is_guest'])
		fatal_lang_error('globalannouncements_error_no_permission', false);

	checkSession();

	$aid = isset($_REQUEST['aid']) ? (int) $_REQUEST['aid'] : 0;
	if ($aid <= 0)
		fatal_lang_error('globalannouncements_error_not_found', false);

	// Verify announcement exists and allows comments.
	$request = $smcFunc['db_query']('', '
		SELECT id_announcement, allow_comments
		FROM {db_prefix}global_announcements
		WHERE id_announcement = {int:aid}
			AND enabled = {int:enabled}
		LIMIT 1',
		array(
			'aid' => $aid,
			'enabled' => 1,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('globalannouncements_error_not_found', false);
	}

	$announcement = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($announcement['allow_comments']))
		fatal_lang_error('globalannouncements_error_no_permission', false);

	// Get the comment body from the editor.
	require_once($sourcedir . '/Subs-Post.php');
	require_once($sourcedir . '/Subs-Editor.php');
	$body = $smcFunc['htmlspecialchars']($_REQUEST['comment_body'], ENT_QUOTES);
	preparsecode($body);

	if (empty(trim(strip_tags(parse_bbc($body, false)))))
		fatal_lang_error('globalannouncements_error_no_comment_body', false);

	// Insert comment.
	$smcFunc['db_insert']('insert',
		'{db_prefix}global_announcement_comments',
		array(
			'id_announcement' => 'int',
			'id_member' => 'int',
			'member_name' => 'string-255',
			'body' => 'string',
			'created_at' => 'int',
			'updated_at' => 'int',
			'modified_by' => 'string-255',
		),
		array(
			$aid,
			$user_info['id'],
			$user_info['name'],
			$body,
			time(),
			0,
			'',
		),
		array('id_comment')
	);

	// Increment comment count.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}global_announcements
		SET num_comments = num_comments + 1
		WHERE id_announcement = {int:aid}',
		array(
			'aid' => $aid,
		)
	);

	redirectexit('action=globalannouncements;sa=view;aid=' . $aid);
}

/**
 * Edit an existing comment.
 */
function GlobalAnnouncementsEditComment()
{
	global $context, $smcFunc, $user_info, $scripturl, $txt, $sourcedir;

	$cid = isset($_REQUEST['cid']) ? (int) $_REQUEST['cid'] : 0;
	if ($cid <= 0)
		fatal_lang_error('globalannouncements_error_comment_not_found', false);

	// Load the comment.
	$request = $smcFunc['db_query']('', '
		SELECT c.id_comment, c.id_announcement, c.id_member, c.member_name, c.body,
			c.created_at, c.updated_at, c.modified_by
		FROM {db_prefix}global_announcement_comments AS c
		WHERE c.id_comment = {int:cid}
		LIMIT 1',
		array(
			'cid' => $cid,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('globalannouncements_error_comment_not_found', false);
	}

	$comment = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Permission check: own comment or manage.
	if ($comment['id_member'] != $user_info['id'] || !allowedTo('globalannouncements_comment'))
	{
		if (!allowedTo('globalannouncements_manage'))
			fatal_lang_error('globalannouncements_error_no_permission', false);
	}

	// POST: save the edit.
	if (isset($_POST['save']))
	{
		checkSession();

		require_once($sourcedir . '/Subs-Post.php');
		require_once($sourcedir . '/Subs-Editor.php');
		$body = $smcFunc['htmlspecialchars']($_POST['comment_body'], ENT_QUOTES);
		preparsecode($body);

		if (empty(trim(strip_tags(parse_bbc($body, false)))))
			fatal_lang_error('globalannouncements_error_no_comment_body', false);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}global_announcement_comments
			SET body = {string:body},
				updated_at = {int:updated_at},
				modified_by = {string:modified_by}
			WHERE id_comment = {int:cid}',
			array(
				'body' => $body,
				'updated_at' => time(),
				'modified_by' => $user_info['name'],
				'cid' => $cid,
			)
		);

		redirectexit('action=globalannouncements;sa=view;aid=' . $comment['id_announcement']);
	}

	// GET: show edit form.
	require_once($sourcedir . '/Subs-Post.php');
	require_once($sourcedir . '/Subs-Editor.php');

	// Un-parse BBC for editing.
	$body = un_preparsecode($comment['body']);

	$editorOptions = array(
		'id' => 'comment_body',
		'value' => $body,
		'width' => '100%',
		'height' => '200px',
		'preview_type' => 0,
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];

	$context['comment'] = $comment;
	$context['page_title'] = $txt['globalannouncements_edit_comment'];
	$context['sub_template'] = 'globalannouncements_edit_comment';

	$context['linktree'][] = array(
		'name' => $txt['globalannouncements_title'],
		'url' => $scripturl . '?action=globalannouncements',
	);
	$context['linktree'][] = array(
		'name' => $txt['globalannouncements_edit_comment'],
	);
}

/**
 * Delete a comment.
 */
function GlobalAnnouncementsDeleteComment()
{
	global $smcFunc, $user_info;

	checkSession('get');

	$cid = isset($_REQUEST['cid']) ? (int) $_REQUEST['cid'] : 0;
	if ($cid <= 0)
		fatal_lang_error('globalannouncements_error_comment_not_found', false);

	// Load the comment.
	$request = $smcFunc['db_query']('', '
		SELECT id_comment, id_announcement, id_member
		FROM {db_prefix}global_announcement_comments
		WHERE id_comment = {int:cid}
		LIMIT 1',
		array(
			'cid' => $cid,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('globalannouncements_error_comment_not_found', false);
	}

	$comment = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Permission check: own comment or manage.
	if ($comment['id_member'] != $user_info['id'] || !allowedTo('globalannouncements_comment'))
	{
		if (!allowedTo('globalannouncements_manage'))
			fatal_lang_error('globalannouncements_error_no_permission', false);
	}

	// Delete the comment.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}global_announcement_comments
		WHERE id_comment = {int:cid}',
		array(
			'cid' => $cid,
		)
	);

	// Decrement comment count.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}global_announcements
		SET num_comments = CASE WHEN num_comments > 0 THEN num_comments - 1 ELSE 0 END
		WHERE id_announcement = {int:aid}',
		array(
			'aid' => $comment['id_announcement'],
		)
	);

	redirectexit('action=globalannouncements;sa=view;aid=' . $comment['id_announcement']);
}

/**
 * Admin dispatcher.
 */
function GlobalAnnouncementsAdmin()
{
	global $context, $txt;

	isAllowedTo('globalannouncements_manage');

	loadLanguage('GlobalAnnouncements');
	loadTemplate('GlobalAnnouncements');
	loadCSSFile('globalannouncements.css', array('default_theme' => true, 'minimize' => true));

	$sa = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : 'list';

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['globalannouncements_title'],
		'description' => $txt['globalannouncements_admin_desc'],
		'tabs' => array(
			'list' => array(),
			'add' => array(),
			'settings' => array(),
		),
	);

	switch ($sa)
	{
		case 'list':
			GlobalAnnouncementsAdminList();
			break;
		case 'add':
		case 'edit':
			GlobalAnnouncementsAdminEdit();
			break;
		case 'save':
			GlobalAnnouncementsAdminSave();
			break;
		case 'delete':
			GlobalAnnouncementsAdminDelete();
			break;
		case 'toggle':
			GlobalAnnouncementsAdminToggle();
			break;
		case 'settings':
			GlobalAnnouncementsAdminSettings();
			break;
		case 'log':
			GlobalAnnouncementsAdminLog();
			break;
		case 'maketopic':
			GlobalAnnouncementsMakeTopic();
			break;
		case 'fromtopic':
			GlobalAnnouncementsFromTopic();
			break;
		default:
			GlobalAnnouncementsAdminList();
			break;
	}
}

/**
 * Admin listing of all announcements using createList().
 */
function GlobalAnnouncementsAdminList()
{
	global $context, $txt, $scripturl, $sourcedir;

	require_once($sourcedir . '/Subs-List.php');

	$listOptions = array(
		'id' => 'globalannouncements_list',
		'title' => $txt['globalannouncements_manage'],
		'items_per_page' => 25,
		'no_items_label' => $txt['globalannouncements_list_none'],
		'base_href' => $scripturl . '?action=admin;area=globalannouncements;sa=list',
		'default_sort_col' => 'sort_order',
		'get_items' => array(
			'function' => 'list_getGlobalAnnouncements',
		),
		'get_count' => array(
			'function' => 'list_getGlobalAnnouncementsCount',
		),
		'columns' => array(
			'title' => array(
				'header' => array(
					'value' => $txt['globalannouncements_list_title'],
				),
				'data' => array(
					'function' => function ($row) use ($scripturl)
					{
						return '<a href="' . $scripturl . '?action=admin;area=globalannouncements;sa=edit;aid=' . $row['id_announcement'] . '">' . $row['title'] . '</a>';
					},
				),
				'sort' => array(
					'default' => 'title',
					'reverse' => 'title DESC',
				),
			),
			'author' => array(
				'header' => array(
					'value' => $txt['globalannouncements_list_author'],
				),
				'data' => array(
					'function' => function ($row) use ($scripturl)
					{
						return '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['member_name'] . '</a>';
					},
				),
				'sort' => array(
					'default' => 'member_name',
					'reverse' => 'member_name DESC',
				),
			),
			'status' => array(
				'header' => array(
					'value' => $txt['globalannouncements_list_status'],
					'class' => 'centertext',
				),
				'data' => array(
					'function' => function ($row) use ($txt)
					{
						return $row['enabled'] ? $txt['globalannouncements_enabled_status'] : $txt['globalannouncements_disabled_status'];
					},
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'enabled DESC',
					'reverse' => 'enabled',
				),
			),
			'views' => array(
				'header' => array(
					'value' => $txt['globalannouncements_list_views'],
					'class' => 'centertext',
				),
				'data' => array(
					'db' => 'views',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'views DESC',
					'reverse' => 'views',
				),
			),
			'comments' => array(
				'header' => array(
					'value' => $txt['globalannouncements_list_comments'],
					'class' => 'centertext',
				),
				'data' => array(
					'db' => 'num_comments',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'num_comments DESC',
					'reverse' => 'num_comments',
				),
			),
			'sort_order' => array(
				'header' => array(
					'value' => $txt['globalannouncements_list_order'],
					'class' => 'centertext',
				),
				'data' => array(
					'db' => 'sort_order',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'sort_order',
					'reverse' => 'sort_order DESC',
				),
			),
			'actions' => array(
				'header' => array(
					'value' => $txt['globalannouncements_list_actions'],
					'class' => 'centertext',
				),
				'data' => array(
					'function' => function ($row) use ($scripturl, $txt, $context)
					{
						$actions = '';
						$actions .= '<a href="' . $scripturl . '?action=admin;area=globalannouncements;sa=edit;aid=' . $row['id_announcement'] . '">' . $txt['globalannouncements_edit'] . '</a>';
						$actions .= ' | <a href="' . $scripturl . '?action=admin;area=globalannouncements;sa=toggle;aid=' . $row['id_announcement'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['globalannouncements_toggle'] . '</a>';
						$actions .= ' | <a href="' . $scripturl . '?action=admin;area=globalannouncements;sa=log;aid=' . $row['id_announcement'] . '">' . $txt['globalannouncements_view_log'] . '</a>';
						$actions .= ' | <a href="' . $scripturl . '?action=admin;area=globalannouncements;sa=maketopic;aid=' . $row['id_announcement'] . '">' . $txt['globalannouncements_make_topic'] . '</a>';
						$actions .= ' | <a href="' . $scripturl . '?action=admin;area=globalannouncements;sa=delete;aid=' . $row['id_announcement'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'' . $txt['globalannouncements_confirm_delete'] . '\');">' . $txt['globalannouncements_delete'] . '</a>';
						return $actions;
					},
					'class' => 'centertext',
				),
			),
		),
	);

	createList($listOptions);

	$context['page_title'] = $txt['globalannouncements_manage'];
	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'globalannouncements_list';
}

/**
 * Get announcements for createList.
 */
function list_getGlobalAnnouncements($start, $items_per_page, $sort)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT id_announcement, id_member, member_name, title, enabled, views, num_comments, created_at, sort_order
		FROM {db_prefix}global_announcements
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:per_page}',
		array(
			'sort' => $sort,
			'start' => $start,
			'per_page' => $items_per_page,
		)
	);

	$announcements = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$announcements[] = $row;
	$smcFunc['db_free_result']($request);

	return $announcements;
}

/**
 * Get announcement count for createList.
 */
function list_getGlobalAnnouncementsCount()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}global_announcements',
		array()
	);
	list($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $count;
}

/**
 * Admin add/edit form.
 */
function GlobalAnnouncementsAdminEdit()
{
	global $context, $smcFunc, $scripturl, $txt, $sourcedir, $user_info;

	require_once($sourcedir . '/Subs-Post.php');

	$aid = isset($_REQUEST['aid']) ? (int) $_REQUEST['aid'] : 0;
	$is_edit = $aid > 0;

	// Load existing announcement for editing.
	if ($is_edit)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_announcement, id_member, member_name, title, body, enabled, allow_comments,
				boards, groups, sort_order
			FROM {db_prefix}global_announcements
			WHERE id_announcement = {int:aid}
			LIMIT 1',
			array(
				'aid' => $aid,
			)
		);

		if ($smcFunc['db_num_rows']($request) == 0)
		{
			$smcFunc['db_free_result']($request);
			fatal_lang_error('globalannouncements_error_not_found', false);
		}

		$announcement = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		$context['announcement'] = array(
			'id' => $announcement['id_announcement'],
			'title' => $announcement['title'],
			'body' => un_preparsecode($announcement['body']),
			'enabled' => !empty($announcement['enabled']),
			'allow_comments' => !empty($announcement['allow_comments']),
			'boards' => !empty($announcement['boards']) ? explode(',', $announcement['boards']) : array(),
			'groups' => !empty($announcement['groups']) ? explode(',', $announcement['groups']) : array(),
			'sort_order' => $announcement['sort_order'],
		);
	}
	else
	{
		$context['announcement'] = array(
			'id' => 0,
			'title' => '',
			'body' => '',
			'enabled' => true,
			'allow_comments' => true,
			'boards' => array(),
			'groups' => array(),
			'sort_order' => 0,
		);
	}

	// Load boards for selection.
	$context['boards_list'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT b.id_board, b.name, c.name AS cat_name
		FROM {db_prefix}boards AS b
			LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		ORDER BY c.cat_order, b.board_order',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['boards_list'][] = $row;
	$smcFunc['db_free_result']($request);

	// Load membergroups for selection (include Guests and Regular Members).
	loadLanguage('Admin');
	$context['groups_list'] = array(
		array('id_group' => -1, 'group_name' => $txt['membergroups_guests']),
		array('id_group' => 0, 'group_name' => $txt['membergroups_members']),
	);
	$request = $smcFunc['db_query']('', '
		SELECT id_group, group_name
		FROM {db_prefix}membergroups
		WHERE min_posts = -1
			AND id_group != {int:mod_group}
		ORDER BY group_name',
		array(
			'mod_group' => 3,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['groups_list'][] = $row;
	$smcFunc['db_free_result']($request);

	// Set up the BBC editor.
	require_once($sourcedir . '/Subs-Editor.php');

	$editorOptions = array(
		'id' => 'announcement_body',
		'value' => $context['announcement']['body'],
		'width' => '100%',
		'height' => '300px',
		'preview_type' => 0,
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];

	$context['page_title'] = $is_edit ? $txt['globalannouncements_edit_title'] : $txt['globalannouncements_add_title'];
	$context['sub_template'] = 'globalannouncements_admin_edit';
}

/**
 * Process admin add/edit save.
 */
function GlobalAnnouncementsAdminSave()
{
	global $smcFunc, $user_info, $sourcedir;

	isAllowedTo('globalannouncements_manage');
	checkSession();

	require_once($sourcedir . '/Subs-Post.php');
	require_once($sourcedir . '/Subs-Editor.php');

	$aid = isset($_REQUEST['aid']) ? (int) $_REQUEST['aid'] : 0;
	$is_edit = $aid > 0;

	// Sanitize input.
	$title = $smcFunc['htmlspecialchars'](trim($_POST['title']), ENT_QUOTES);
	$body = $smcFunc['htmlspecialchars']($_POST['announcement_body'], ENT_QUOTES);
	preparsecode($body);

	if (empty($title))
		fatal_lang_error('globalannouncements_error_no_title', false);
	if (empty(trim(strip_tags(parse_bbc($body, false)))))
		fatal_lang_error('globalannouncements_error_no_body', false);

	$enabled = !empty($_POST['enabled']) ? 1 : 0;
	$allow_comments = !empty($_POST['allow_comments']) ? 1 : 0;
	$sort_order = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;

	// Boards.
	$boards = '';
	if (!empty($_POST['boards']) && is_array($_POST['boards']))
		$boards = implode(',', array_map('intval', $_POST['boards']));

	// Groups.
	$groups = '';
	if (!empty($_POST['groups']) && is_array($_POST['groups']))
		$groups = implode(',', array_map('intval', $_POST['groups']));

	$now = time();

	if ($is_edit)
	{
		// Verify announcement exists.
		$request = $smcFunc['db_query']('', '
			SELECT id_announcement
			FROM {db_prefix}global_announcements
			WHERE id_announcement = {int:aid}
			LIMIT 1',
			array(
				'aid' => $aid,
			)
		);

		if ($smcFunc['db_num_rows']($request) == 0)
		{
			$smcFunc['db_free_result']($request);
			fatal_lang_error('globalannouncements_error_not_found', false);
		}
		$smcFunc['db_free_result']($request);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}global_announcements
			SET title = {string:title},
				body = {string:body},
				enabled = {int:enabled},
				allow_comments = {int:allow_comments},
				boards = {string:boards},
				groups = {string:groups},
				sort_order = {int:sort_order},
				updated_at = {int:updated_at}
			WHERE id_announcement = {int:aid}',
			array(
				'title' => $title,
				'body' => $body,
				'enabled' => $enabled,
				'allow_comments' => $allow_comments,
				'boards' => $boards,
				'groups' => $groups,
				'sort_order' => $sort_order,
				'updated_at' => $now,
				'aid' => $aid,
			)
		);
	}
	else
	{
		$smcFunc['db_insert']('insert',
			'{db_prefix}global_announcements',
			array(
				'id_member' => 'int',
				'member_name' => 'string-255',
				'title' => 'string-255',
				'body' => 'string',
				'enabled' => 'int',
				'allow_comments' => 'int',
				'boards' => 'string',
				'groups' => 'string',
				'views' => 'int',
				'num_comments' => 'int',
				'created_at' => 'int',
				'updated_at' => 'int',
				'sort_order' => 'int',
			),
			array(
				$user_info['id'],
				$user_info['name'],
				$title,
				$body,
				$enabled,
				$allow_comments,
				$boards,
				$groups,
				0,
				0,
				$now,
				0,
				$sort_order,
			),
			array('id_announcement')
		);
	}

	redirectexit('action=admin;area=globalannouncements;sa=list');
}

/**
 * Admin delete announcement.
 */
function GlobalAnnouncementsAdminDelete()
{
	global $smcFunc;

	isAllowedTo('globalannouncements_manage');
	checkSession('get');

	$aid = isset($_REQUEST['aid']) ? (int) $_REQUEST['aid'] : 0;
	if ($aid <= 0)
		fatal_lang_error('globalannouncements_error_not_found', false);

	// Delete announcement, comments, and log entries.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}global_announcement_comments
		WHERE id_announcement = {int:aid}',
		array(
			'aid' => $aid,
		)
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}global_announcement_log
		WHERE id_announcement = {int:aid}',
		array(
			'aid' => $aid,
		)
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}global_announcements
		WHERE id_announcement = {int:aid}',
		array(
			'aid' => $aid,
		)
	);

	redirectexit('action=admin;area=globalannouncements;sa=list');
}

/**
 * Admin toggle enabled status.
 */
function GlobalAnnouncementsAdminToggle()
{
	global $smcFunc;

	isAllowedTo('globalannouncements_manage');
	checkSession('get');

	$aid = isset($_REQUEST['aid']) ? (int) $_REQUEST['aid'] : 0;
	if ($aid <= 0)
		fatal_lang_error('globalannouncements_error_not_found', false);

	// Flip enabled.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}global_announcements
		SET enabled = CASE WHEN enabled = 1 THEN 0 ELSE 1 END
		WHERE id_announcement = {int:aid}',
		array(
			'aid' => $aid,
		)
	);

	redirectexit('action=admin;area=globalannouncements;sa=list');
}

/**
 * Admin settings page.
 */
function GlobalAnnouncementsAdminSettings()
{
	global $context, $txt, $scripturl, $sourcedir;

	isAllowedTo('admin_forum');

	loadLanguage('ManageSettings');
	require_once($sourcedir . '/ManageServer.php');

	$context['page_title'] = $txt['globalannouncements_settings_title'];
	$context['post_url'] = $scripturl . '?action=admin;area=globalannouncements;sa=settings;save';
	$context['settings_title'] = $txt['globalannouncements_settings_title'];
	$context['sub_template'] = 'show_settings';

	$config_vars = array(
		array('check', 'globalannouncements_enabled', 'subtext' => $txt['globalannouncements_setting_enabled_desc']),
		'',
		array('int', 'globalannouncements_force_id', 'subtext' => $txt['globalannouncements_setting_force_id_desc'], 'size' => 5),
		array('check', 'globalannouncements_sticky_bar', 'subtext' => $txt['globalannouncements_setting_sticky_bar_desc']),
		array('int', 'globalannouncements_per_page', 'subtext' => $txt['globalannouncements_setting_per_page_desc'], 'size' => 5),
	);

	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=globalannouncements;sa=settings');
	}

	prepareDBSettingContext($config_vars);
}

/**
 * Admin view log for a specific announcement.
 */
function GlobalAnnouncementsAdminLog()
{
	global $context, $smcFunc, $scripturl, $txt;

	$aid = isset($_REQUEST['aid']) ? (int) $_REQUEST['aid'] : 0;
	if ($aid <= 0)
		fatal_lang_error('globalannouncements_error_not_found', false);

	// Load announcement title.
	$request = $smcFunc['db_query']('', '
		SELECT title
		FROM {db_prefix}global_announcements
		WHERE id_announcement = {int:aid}
		LIMIT 1',
		array(
			'aid' => $aid,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('globalannouncements_error_not_found', false);
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$context['announcement_title'] = $row['title'];
	$context['announcement_id'] = $aid;

	// Pagination.
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
	$per_page = 30;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}global_announcement_log
		WHERE id_announcement = {int:aid}',
		array(
			'aid' => $aid,
		)
	);
	list($total) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=globalannouncements;sa=log;aid=' . $aid, $start, $total, $per_page);
	$context['start'] = $start;

	// Load log entries.
	$context['log_entries'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT l.id_member, l.viewed_at, COALESCE(m.real_name, {string:unknown}) AS member_name
		FROM {db_prefix}global_announcement_log AS l
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = l.id_member)
		WHERE l.id_announcement = {int:aid}
		ORDER BY l.viewed_at DESC
		LIMIT {int:start}, {int:per_page}',
		array(
			'aid' => $aid,
			'unknown' => '???',
			'start' => $start,
			'per_page' => $per_page,
		)
	);

	while ($entry = $smcFunc['db_fetch_assoc']($request))
	{
		$context['log_entries'][] = array(
			'member' => array(
				'id' => $entry['id_member'],
				'name' => $entry['member_name'],
				'href' => $scripturl . '?action=profile;u=' . $entry['id_member'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $entry['id_member'] . '">' . $entry['member_name'] . '</a>',
			),
			'viewed_at' => timeformat($entry['viewed_at']),
		);
	}
	$smcFunc['db_free_result']($request);

	$context['page_title'] = sprintf($txt['globalannouncements_log_for'], $row['title']);
	$context['sub_template'] = 'globalannouncements_admin_log';
}

/**
 * Convert announcement to a forum topic.
 */
function GlobalAnnouncementsMakeTopic()
{
	global $context, $smcFunc, $scripturl, $txt, $user_info, $sourcedir;

	isAllowedTo('globalannouncements_manage');

	$aid = isset($_REQUEST['aid']) ? (int) $_REQUEST['aid'] : 0;
	if ($aid <= 0)
		fatal_lang_error('globalannouncements_error_not_found', false);

	// Load announcement.
	$request = $smcFunc['db_query']('', '
		SELECT id_announcement, id_member, member_name, title, body, num_comments, created_at
		FROM {db_prefix}global_announcements
		WHERE id_announcement = {int:aid}
		LIMIT 1',
		array(
			'aid' => $aid,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('globalannouncements_error_not_found', false);
	}

	$announcement = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// POST: do the conversion.
	if (isset($_POST['convert']))
	{
		checkSession();

		$board_id = isset($_POST['board_id']) ? (int) $_POST['board_id'] : 0;
		if ($board_id <= 0)
			fatal_lang_error('globalannouncements_error_not_found', false);

		$is_sticky = !empty($_POST['sticky']) ? 1 : 0;
		$is_locked = !empty($_POST['locked']) ? 1 : 0;
		$convert_comments = !empty($_POST['convert_comments']);

		require_once($sourcedir . '/Subs-Post.php');

		// Create the first message.
		$msgOptions = array(
			'subject' => $announcement['title'],
			'body' => $announcement['body'],
			'smileys_enabled' => 1,
		);
		$topicOptions = array(
			'board' => $board_id,
			'is_sticky' => $is_sticky,
			'lock_mode' => $is_locked ? 1 : 0,
			'mark_as_read' => true,
		);
		$posterOptions = array(
			'id' => $announcement['id_member'],
			'name' => $announcement['member_name'],
			'update_post_count' => false,
		);

		createPost($msgOptions, $topicOptions, $posterOptions);

		$new_topic_id = $topicOptions['id'];

		// Optionally convert comments to replies.
		if ($convert_comments && $announcement['num_comments'] > 0)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member, member_name, body, created_at
				FROM {db_prefix}global_announcement_comments
				WHERE id_announcement = {int:aid}
				ORDER BY created_at ASC',
				array(
					'aid' => $aid,
				)
			);

			while ($comment = $smcFunc['db_fetch_assoc']($request))
			{
				$replyMsgOptions = array(
					'subject' => 'Re: ' . $announcement['title'],
					'body' => $comment['body'],
					'smileys_enabled' => 1,
				);
				$replyTopicOptions = array(
					'id' => $new_topic_id,
					'board' => $board_id,
					'mark_as_read' => true,
				);
				$replyPosterOptions = array(
					'id' => $comment['id_member'],
					'name' => $comment['member_name'],
					'update_post_count' => false,
				);

				createPost($replyMsgOptions, $replyTopicOptions, $replyPosterOptions);
			}
			$smcFunc['db_free_result']($request);
		}

		redirectexit('topic=' . $new_topic_id . '.0');
	}

	// GET: show form.
	$context['announcement'] = array(
		'id' => $announcement['id_announcement'],
		'title' => $announcement['title'],
		'num_comments' => $announcement['num_comments'],
	);

	// Load boards for selection.
	$context['boards_list'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT b.id_board, b.name, c.name AS cat_name
		FROM {db_prefix}boards AS b
			LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		ORDER BY c.cat_order, b.board_order',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['boards_list'][] = $row;
	$smcFunc['db_free_result']($request);

	$context['page_title'] = $txt['globalannouncements_make_topic_title'];
	$context['sub_template'] = 'globalannouncements_make_topic';
}

/**
 * Convert a topic to an announcement.
 */
function GlobalAnnouncementsFromTopic()
{
	global $context, $smcFunc, $scripturl, $txt, $user_info;

	isAllowedTo('globalannouncements_manage');

	$topic_id = isset($_REQUEST['topic']) ? (int) $_REQUEST['topic'] : 0;
	if ($topic_id <= 0)
		fatal_lang_error('globalannouncements_error_not_found', false);

	// Load topic info.
	$request = $smcFunc['db_query']('', '
		SELECT t.id_topic, t.id_first_msg, t.num_replies, t.id_member_started,
			m.subject, m.body, mem.real_name
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = t.id_member_started)
		WHERE t.id_topic = {int:topic}
		LIMIT 1',
		array(
			'topic' => $topic_id,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('globalannouncements_error_not_found', false);
	}

	$topic = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// POST: do the conversion.
	if (isset($_POST['convert']))
	{
		checkSession();

		$convert_replies = !empty($_POST['convert_replies']);
		$now = time();

		// Create announcement from the first message.
		$smcFunc['db_insert']('insert',
			'{db_prefix}global_announcements',
			array(
				'id_member' => 'int',
				'member_name' => 'string-255',
				'title' => 'string-255',
				'body' => 'string',
				'enabled' => 'int',
				'allow_comments' => 'int',
				'boards' => 'string',
				'groups' => 'string',
				'views' => 'int',
				'num_comments' => 'int',
				'created_at' => 'int',
				'updated_at' => 'int',
				'sort_order' => 'int',
			),
			array(
				$topic['id_member_started'],
				!empty($topic['real_name']) ? $topic['real_name'] : $user_info['name'],
				$topic['subject'],
				$topic['body'],
				1,
				1,
				'',
				'',
				0,
				0,
				$now,
				0,
				0,
			),
			array('id_announcement')
		);

		$new_aid = $smcFunc['db_insert_id']('{db_prefix}global_announcements', 'id_announcement');

		// Optionally convert replies to comments.
		if ($convert_replies && $topic['num_replies'] > 0)
		{
			$request = $smcFunc['db_query']('', '
				SELECT m.id_member, m.body, m.poster_time,
					COALESCE(mem.real_name, m.poster_name) AS member_name
				FROM {db_prefix}messages AS m
					LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
				WHERE m.id_topic = {int:topic}
					AND m.id_msg != {int:first_msg}
				ORDER BY m.poster_time ASC',
				array(
					'topic' => $topic_id,
					'first_msg' => $topic['id_first_msg'],
				)
			);

			$comment_count = 0;
			while ($reply = $smcFunc['db_fetch_assoc']($request))
			{
				$smcFunc['db_insert']('insert',
					'{db_prefix}global_announcement_comments',
					array(
						'id_announcement' => 'int',
						'id_member' => 'int',
						'member_name' => 'string-255',
						'body' => 'string',
						'created_at' => 'int',
						'updated_at' => 'int',
						'modified_by' => 'string-255',
					),
					array(
						$new_aid,
						$reply['id_member'],
						$reply['member_name'],
						$reply['body'],
						$reply['poster_time'],
						0,
						'',
					),
					array('id_comment')
				);
				$comment_count++;
			}
			$smcFunc['db_free_result']($request);

			// Update comment count.
			if ($comment_count > 0)
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}global_announcements
					SET num_comments = {int:count}
					WHERE id_announcement = {int:aid}',
					array(
						'count' => $comment_count,
						'aid' => $new_aid,
					)
				);
			}
		}

		redirectexit('action=admin;area=globalannouncements;sa=list');
	}

	// GET: show form.
	$context['topic_info'] = array(
		'id' => $topic['id_topic'],
		'subject' => $topic['subject'],
		'num_replies' => $topic['num_replies'],
	);

	$context['page_title'] = $txt['globalannouncements_from_topic_title'];
	$context['sub_template'] = 'globalannouncements_from_topic';
}