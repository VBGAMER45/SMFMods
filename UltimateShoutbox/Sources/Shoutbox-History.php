<?php
/**
 * Ultimate Shoutbox & Chatroom - History/Archive Page
 *
 * Provides searchable, paginated history with CSV/text export.
 *
 * @package Shoutbox
 * @version 1.1.0
 * By: vbgamer45
 * https://www.smfhacks.com
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * History page (HTML view with search and pagination).
 */
function ShoutboxHistory()
{
	global $context, $txt, $smcFunc, $scripturl, $modSettings, $user_info, $sourcedir;

	isAllowedTo('shoutbox_view');
	loadLanguage('Shoutbox');
	loadTemplate('Shoutbox');
	loadCSSFile('shoutbox.css', array(), 'shoutbox_css');

	require_once($sourcedir . '/Shoutbox.php');

	$context['page_title'] = $txt['shoutbox_history_title'];
	$context['sub_template'] = 'shoutbox_history';
	$context['linktree'][] = array(
		'name' => $txt['shoutbox_history_title'],
		'url' => $scripturl . '?action=shoutbox;sa=history_page',
	);

	// Load rooms for the filter dropdown.
	$raw_rooms = shoutbox_get_rooms();
	$context['shoutbox_rooms'] = array();
	foreach ($raw_rooms as $room)
	{
		$context['shoutbox_rooms'][] = array(
			'id' => (int) $room['id_room'],
			'name' => $room['room_name'],
		);
	}

	// Search/filter parameters.
	$search = isset($_REQUEST['search']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_REQUEST['search'])) : '';
	$date_from = isset($_REQUEST['date_from']) ? trim($_REQUEST['date_from']) : '';
	$date_to = isset($_REQUEST['date_to']) ? trim($_REQUEST['date_to']) : '';
	$room_filter = isset($_REQUEST['room_id']) ? (int) $_REQUEST['room_id'] : 0;
	$per_page = 50;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	$context['shoutbox_search'] = $search;
	$context['shoutbox_date_from'] = $date_from;
	$context['shoutbox_date_to'] = $date_to;
	$context['shoutbox_room_filter'] = $room_filter;

	// Build WHERE clauses.
	$where = array('1=1');
	$params = array();

	// Room filter.
	if ($room_filter > 0)
	{
		$where[] = 'sm.id_room = {int:room_id}';
		$params['room_id'] = $room_filter;
	}

	// Whisper visibility.
	if ($user_info['is_guest'])
	{
		$where[] = 'sm.is_whisper = 0';
	}
	elseif (!allowedTo('shoutbox_moderate'))
	{
		$where[] = '(sm.is_whisper = 0 OR sm.id_member = {int:user_id} OR sm.whisper_to = {int:user_id})';
		$params['user_id'] = (int) $user_info['id'];
	}

	if (!empty($search))
	{
		$where[] = '(sm.body LIKE {string:search} OR sm.member_name LIKE {string:search})';
		$params['search'] = '%' . $search . '%';
	}

	if (!empty($date_from))
	{
		$ts = strtotime($date_from);
		if ($ts !== false)
		{
			$where[] = 'sm.created_at >= {int:date_from}';
			$params['date_from'] = $ts;
		}
	}

	if (!empty($date_to))
	{
		$ts = strtotime($date_to . ' 23:59:59');
		if ($ts !== false)
		{
			$where[] = 'sm.created_at <= {int:date_to}';
			$params['date_to'] = $ts;
		}
	}

	$where_clause = implode(' AND ', $where);

	// Count total messages.
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}shoutbox_messages AS sm
		WHERE ' . $where_clause,
		$params
	);

	list($total) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Pagination.
	$base_url = $scripturl . '?action=shoutbox;sa=history_page';
	if (!empty($search))
		$base_url .= ';search=' . urlencode($search);
	if (!empty($date_from))
		$base_url .= ';date_from=' . urlencode($date_from);
	if (!empty($date_to))
		$base_url .= ';date_to=' . urlencode($date_to);
	if ($room_filter > 0)
		$base_url .= ';room_id=' . $room_filter;

	$context['shoutbox_page_index'] = constructPageIndex($base_url, $start, $total, $per_page);

	// Fetch messages.
	$request = $smcFunc['db_query']('', '
		SELECT sm.id_msg, sm.id_member, sm.member_name, sm.parsed_body, sm.is_whisper,
			sm.whisper_to, sm.is_action, sm.created_at, sm.edited_by, sm.edited_at,
			COALESCE(mem.real_name, sm.member_name) AS real_name,
			COALESCE(mg.online_color, pg.online_color, {string:empty}) AS member_color
		FROM {db_prefix}shoutbox_messages AS sm
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sm.id_member)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
			LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
		WHERE ' . $where_clause . '
		ORDER BY sm.id_msg DESC
		LIMIT {int:start}, {int:limit}',
		array_merge($params, array(
			'start' => $start,
			'limit' => $per_page,
			'empty' => '',
		))
	);

	$context['shoutbox_messages'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['shoutbox_messages'][] = array(
			'id' => (int) $row['id_msg'],
			'memberId' => (int) $row['id_member'],
			'memberName' => $row['real_name'],
			'memberColor' => !empty($row['member_color']) ? $row['member_color'] : '',
			'body' => $row['parsed_body'],
			'isWhisper' => !empty($row['is_whisper']),
			'isAction' => !empty($row['is_action']),
			'createdAt' => (int) $row['created_at'],
			'time_formatted' => timeformat($row['created_at']),
			'profileUrl' => $row['id_member'] > 0 ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
		);
	}
	$smcFunc['db_free_result']($request);
}

/**
 * History AJAX endpoint for dynamic loading.
 */
function ShoutboxHistoryAjax()
{
	global $smcFunc, $modSettings, $user_info, $scripturl;

	isAllowedTo('shoutbox_view');

	$search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
	$date_from = isset($_REQUEST['date_from']) ? trim($_REQUEST['date_from']) : '';
	$date_to = isset($_REQUEST['date_to']) ? trim($_REQUEST['date_to']) : '';
	$room_filter = isset($_REQUEST['room_id']) ? (int) $_REQUEST['room_id'] : 0;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
	$limit = 50;

	$where = array('1=1');
	$params = array();

	if ($room_filter > 0)
	{
		$where[] = 'sm.id_room = {int:room_id}';
		$params['room_id'] = $room_filter;
	}

	if ($user_info['is_guest'])
	{
		$where[] = 'sm.is_whisper = 0';
	}
	elseif (!allowedTo('shoutbox_moderate'))
	{
		$where[] = '(sm.is_whisper = 0 OR sm.id_member = {int:user_id} OR sm.whisper_to = {int:user_id})';
		$params['user_id'] = (int) $user_info['id'];
	}

	if (!empty($search))
	{
		$where[] = '(sm.body LIKE {string:search} OR sm.member_name LIKE {string:search})';
		$params['search'] = '%' . $search . '%';
	}

	if (!empty($date_from))
	{
		$ts = strtotime($date_from);
		if ($ts !== false)
		{
			$where[] = 'sm.created_at >= {int:date_from}';
			$params['date_from'] = $ts;
		}
	}

	if (!empty($date_to))
	{
		$ts = strtotime($date_to . ' 23:59:59');
		if ($ts !== false)
		{
			$where[] = 'sm.created_at <= {int:date_to}';
			$params['date_to'] = $ts;
		}
	}

	$where_clause = implode(' AND ', $where);

	$request = $smcFunc['db_query']('', '
		SELECT sm.id_msg, sm.id_member, sm.member_name, sm.parsed_body, sm.is_whisper,
			sm.whisper_to, sm.is_action, sm.created_at,
			COALESCE(mem.real_name, sm.member_name) AS real_name,
			COALESCE(mg.online_color, pg.online_color, {string:empty}) AS member_color
		FROM {db_prefix}shoutbox_messages AS sm
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sm.id_member)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
			LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
		WHERE ' . $where_clause . '
		ORDER BY sm.id_msg DESC
		LIMIT {int:start}, {int:limit}',
		array_merge($params, array(
			'start' => $start,
			'limit' => $limit,
			'empty' => '',
		))
	);

	$messages = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$messages[] = array(
			'id' => (int) $row['id_msg'],
			'memberId' => (int) $row['id_member'],
			'memberName' => $row['real_name'],
			'memberColor' => !empty($row['member_color']) ? $row['member_color'] : '',
			'body' => $row['parsed_body'],
			'isWhisper' => !empty($row['is_whisper']),
			'isAction' => !empty($row['is_action']),
			'createdAt' => (int) $row['created_at'],
			'time_formatted' => timeformat($row['created_at']),
			'profileUrl' => $row['id_member'] > 0 ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
		);
	}
	$smcFunc['db_free_result']($request);

	// Get total count.
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}shoutbox_messages AS sm
		WHERE ' . $where_clause,
		$params
	);
	list($total) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	ob_end_clean();
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode(array(
		'messages' => $messages,
		'total' => (int) $total,
		'start' => $start,
		'limit' => $limit,
	));
	obExit(false);
}

/**
 * Export history as CSV or plain text.
 */
function ShoutboxExportData()
{
	global $smcFunc, $user_info;

	isAllowedTo('shoutbox_moderate');

	$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'csv';
	$search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
	$date_from = isset($_REQUEST['date_from']) ? trim($_REQUEST['date_from']) : '';
	$date_to = isset($_REQUEST['date_to']) ? trim($_REQUEST['date_to']) : '';
	$room_filter = isset($_REQUEST['room_id']) ? (int) $_REQUEST['room_id'] : 0;

	$where = array('1=1');
	$params = array();

	if ($room_filter > 0)
	{
		$where[] = 'sm.id_room = {int:room_id}';
		$params['room_id'] = $room_filter;
	}

	if (!empty($search))
	{
		$where[] = '(sm.body LIKE {string:search} OR sm.member_name LIKE {string:search})';
		$params['search'] = '%' . $search . '%';
	}

	if (!empty($date_from))
	{
		$ts = strtotime($date_from);
		if ($ts !== false)
		{
			$where[] = 'sm.created_at >= {int:date_from}';
			$params['date_from'] = $ts;
		}
	}

	if (!empty($date_to))
	{
		$ts = strtotime($date_to . ' 23:59:59');
		if ($ts !== false)
		{
			$where[] = 'sm.created_at <= {int:date_to}';
			$params['date_to'] = $ts;
		}
	}

	$where_clause = implode(' AND ', $where);

	$request = $smcFunc['db_query']('', '
		SELECT sm.id_msg, sm.id_member, sm.member_name, sm.body, sm.is_whisper,
			sm.is_action, sm.created_at,
			COALESCE(mem.real_name, sm.member_name) AS real_name
		FROM {db_prefix}shoutbox_messages AS sm
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sm.id_member)
		WHERE ' . $where_clause . '
		ORDER BY sm.id_msg ASC
		LIMIT 10000',
		$params
	);

	$messages = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$messages[] = $row;
	$smcFunc['db_free_result']($request);

	ob_end_clean();

	if ($format === 'csv')
	{
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="shoutbox_export_' . date('Y-m-d') . '.csv"');

		// BOM for Excel UTF-8 compatibility.
		echo "\xEF\xBB\xBF";
		echo "ID,Date,Author,Message,Whisper,Action\n";

		foreach ($messages as $msg)
		{
			echo implode(',', array(
				$msg['id_msg'],
				'"' . date('Y-m-d H:i:s', $msg['created_at']) . '"',
				'"' . str_replace('"', '""', $msg['real_name']) . '"',
				'"' . str_replace('"', '""', strip_tags($msg['body'])) . '"',
				$msg['is_whisper'] ? 'Yes' : 'No',
				$msg['is_action'] ? 'Yes' : 'No',
			)) . "\n";
		}
	}
	else
	{
		header('Content-Type: text/plain; charset=UTF-8');
		header('Content-Disposition: attachment; filename="shoutbox_export_' . date('Y-m-d') . '.txt"');

		foreach ($messages as $msg)
		{
			$prefix = '';
			if (!empty($msg['is_whisper']))
				$prefix = '[WHISPER] ';
			if (!empty($msg['is_action']))
				$prefix .= '* ';

			echo '[' . date('Y-m-d H:i:s', $msg['created_at']) . '] ' .
				$prefix . $msg['real_name'] . ': ' .
				strip_tags($msg['body']) . "\n";
		}
	}

	obExit(false);
}
