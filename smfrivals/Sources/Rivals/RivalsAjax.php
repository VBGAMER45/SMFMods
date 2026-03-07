<?php
/**
 * SMF Rivals - AJAX Endpoints
 * Match chat, live updates, search.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * AJAX dispatcher.
 */
function RivalsAjax()
{
	global $context, $smcFunc, $user_info;

	$do = isset($_GET['do']) ? $_GET['do'] : '';

	switch ($do)
	{
		case 'fetchchat':
			RivalsAjaxFetchChat();
			break;
		case 'sendchat':
			RivalsAjaxSendChat();
			break;
		case 'findclan':
			RivalsAjaxFindClan();
			break;
		case 'finduser':
			RivalsAjaxFindUser();
			break;
		default:
			obExit(false);
	}
}

/**
 * Fetch match chat messages.
 */
function RivalsAjaxFetchChat()
{
	global $smcFunc, $context;

	$id_match = isset($_GET['match']) ? (int) $_GET['match'] : 0;
	$last_id = isset($_GET['last']) ? (int) $_GET['last'] : 0;

	$messages = array();

	if ($id_match > 0)
	{
		$request = $smcFunc['db_query']('', '
			SELECT mc.id_comment, mc.id_member, mc.body, mc.created_at,
				mem.real_name
			FROM {db_prefix}rivals_match_comments AS mc
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = mc.id_member)
			WHERE mc.id_match = {int:match}
				AND mc.id_comment > {int:last_id}
			ORDER BY mc.created_at ASC
			LIMIT 50',
			array('match' => $id_match, 'last_id' => $last_id)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$messages[] = array(
				'id' => $row['id_comment'],
				'member' => $row['real_name'],
				'body' => htmlspecialchars($row['body'], ENT_QUOTES, 'UTF-8'),
				'time' => timeformat($row['created_at']),
			);
		}
		$smcFunc['db_free_result']($request);
	}

	header('Content-Type: application/json');
	echo json_encode(array('messages' => $messages));
	obExit(false);
}

/**
 * Send a match chat message.
 */
function RivalsAjaxSendChat()
{
	global $smcFunc, $user_info, $sourcedir;

	checkSession('get');

	$id_match = isset($_POST['match']) ? (int) $_POST['match'] : 0;
	$body = isset($_POST['body']) ? trim($_POST['body']) : '';

	if ($id_match <= 0 || $body === '' || $user_info['is_guest'])
	{
		header('Content-Type: application/json');
		echo json_encode(array('error' => true));
		obExit(false);
		return;
	}

	require_once($sourcedir . '/Rivals/RivalsLib.php');

	$clan_id = rivals_get_member_clan($user_info['id']);

	$smcFunc['db_insert']('',
		'{db_prefix}rivals_match_comments',
		array(
			'id_match' => 'int',
			'id_member' => 'int',
			'id_clan' => 'int',
			'comment_type' => 'int',
			'body' => 'string',
			'created_at' => 'int',
		),
		array($id_match, $user_info['id'], $clan_id, 0, $body, time()),
		array('id_comment')
	);

	header('Content-Type: application/json');
	echo json_encode(array('success' => true));
	obExit(false);
}

/**
 * Search clans by name (for challenge popup).
 */
function RivalsAjaxFindClan()
{
	global $smcFunc;

	$search = isset($_GET['q']) ? trim($_GET['q']) : '';
	$results = array();

	if (strlen($search) >= 2)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_clan, name
			FROM {db_prefix}rivals_clans
			WHERE name LIKE {string:search}
				AND is_closed = 0
			ORDER BY name
			LIMIT 10',
			array('search' => '%' . $search . '%')
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$results[] = array('id' => $row['id_clan'], 'name' => $row['name']);
		$smcFunc['db_free_result']($request);
	}

	header('Content-Type: application/json');
	echo json_encode($results);
	obExit(false);
}

/**
 * Search users by name (for invite/1v1).
 */
function RivalsAjaxFindUser()
{
	global $smcFunc;

	$search = isset($_GET['q']) ? trim($_GET['q']) : '';
	$results = array();

	if (strlen($search) >= 2)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member, real_name
			FROM {db_prefix}members
			WHERE real_name LIKE {string:search}
				AND is_activated = 1
			ORDER BY real_name
			LIMIT 10',
			array('search' => '%' . $search . '%')
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$results[] = array('id' => $row['id_member'], 'name' => $row['real_name']);
		$smcFunc['db_free_result']($request);
	}

	header('Content-Type: application/json');
	echo json_encode($results);
	obExit(false);
}
?>