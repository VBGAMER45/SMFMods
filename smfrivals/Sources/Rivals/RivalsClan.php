<?php
/**
 * SMF Rivals - Clan Management
 * Create, edit, join, leave, manage clans.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Clan directory with alpha filter + ladder filter + pagination.
 */
function RivalsClanList()
{
	global $context, $smcFunc, $txt, $scripturl, $settings;

	$context['page_title'] = $txt['rivals_clans'];
	$context['sub_template'] = 'rivals_clan_list';
	loadTemplate('RivalsClan');

	// Filters
	$alpha = isset($_GET['alpha']) ? substr(preg_replace('/[^a-zA-Z]/', '', $_GET['alpha']), 0, 1) : '';
	$ladder_filter = isset($_GET['ladder']) ? (int) $_GET['ladder'] : 0;
	$per_page = 30;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	$context['rivals_alpha_filter'] = strtoupper($alpha);
	$context['rivals_ladder_filter'] = $ladder_filter;

	// Get ladders for filter dropdown
	$context['rivals_ladder_options'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT id_ladder, name
		FROM {db_prefix}rivals_ladders
		WHERE id_parent = 0
		ORDER BY name',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_ladder_options'][$row['id_ladder']] = $row['name'];
	$smcFunc['db_free_result']($request);

	// Build WHERE clause
	$where = 'c.is_closed = 0';
	$params = array();

	if ($alpha !== '')
	{
		$where .= ' AND c.name LIKE {string:alpha}';
		$params['alpha'] = $alpha . '%';
	}

	if ($ladder_filter > 0)
	{
		$where .= ' AND EXISTS (SELECT 1 FROM {db_prefix}rivals_standings AS s WHERE s.id_clan = c.id_clan AND s.id_ladder = {int:ladder})';
		$params['ladder'] = $ladder_filter;
	}

	// Count
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}rivals_clans AS c
		WHERE ' . $where,
		$params
	);
	list ($total) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$base_url = $scripturl . '?action=rivals;sa=clans' . ($alpha !== '' ? ';alpha=' . $alpha : '') . ($ladder_filter > 0 ? ';ladder=' . $ladder_filter : '');
	$context['page_index'] = constructPageIndex($base_url, $start, $total, $per_page, true);

	// Fetch clans
	$context['rivals_clans'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT c.id_clan, c.name, c.logo, c.logo_ext, c.total_wins, c.total_losses,
			c.total_draws, c.clan_level, c.created_at,
			(SELECT COUNT(*) FROM {db_prefix}rivals_clan_members AS cm WHERE cm.id_clan = c.id_clan AND cm.is_pending = 0) AS member_count
		FROM {db_prefix}rivals_clans AS c
		WHERE ' . $where . '
		ORDER BY c.name ASC
		LIMIT {int:start}, {int:limit}',
		array_merge($params, array('start' => $start, 'limit' => $per_page))
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['rivals_clans'][$row['id_clan']] = array(
			'id' => $row['id_clan'],
			'name' => $row['name'],
			'logo' => $row['logo'],
			'logo_ext' => $row['logo_ext'],
			'wins' => $row['total_wins'],
			'losses' => $row['total_losses'],
			'draws' => $row['total_draws'],
			'level' => $row['clan_level'],
			'created_at' => $row['created_at'],
			'member_count' => $row['member_count'],
			'href' => $scripturl . '?action=rivals;sa=clan;id=' . $row['id_clan'],
		);
	}
	$smcFunc['db_free_result']($request);

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=clans',
		'name' => $txt['rivals_clans'],
	);
}

/**
 * Clan profile page with tabs: Info, Members, Stats, History.
 */
function RivalsClanProfile()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info, $settings;

	$id_clan = isset($_GET['id']) ? (int) $_GET['id'] : 0;
	if ($id_clan <= 0)
		fatal_lang_error('rivals_clan_not_found', false);

	$context['sub_template'] = 'rivals_clan_profile';
	loadTemplate('RivalsClan');

	// Fetch clan data
	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}rivals_clans
		WHERE id_clan = {int:clan}',
		array('clan' => $id_clan)
	);
	$clan = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($clan))
		fatal_lang_error('rivals_clan_not_found', false);

	$context['rivals_clan'] = $clan;
	$context['page_title'] = $clan['name'];

	// Get leader
	$request = $smcFunc['db_query']('', '
		SELECT cm.id_member, cm.role, mem.real_name
		FROM {db_prefix}rivals_clan_members AS cm
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = cm.id_member)
		WHERE cm.id_clan = {int:clan}
			AND cm.role = 1
			AND cm.is_pending = 0
		LIMIT 1',
		array('clan' => $id_clan)
	);
	$context['rivals_clan_leader'] = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Get all members (non-pending)
	$context['rivals_clan_members'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT cm.id_member, cm.role, cm.mvp_count, cm.kills, cm.deaths, cm.assists,
			cm.goals_for, cm.goals_against,
			mem.real_name, mem.rivals_gamer_name
		FROM {db_prefix}rivals_clan_members AS cm
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = cm.id_member)
		WHERE cm.id_clan = {int:clan}
			AND cm.is_pending = 0
		ORDER BY cm.role DESC, mem.real_name ASC',
		array('clan' => $id_clan)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$role_text = $txt['rivals_role_member'];
		if ($row['role'] == 1) $role_text = $txt['rivals_role_leader'];
		elseif ($row['role'] == 2) $role_text = $txt['rivals_role_coleader'];

		$context['rivals_clan_members'][$row['id_member']] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'gamer_name' => $row['rivals_gamer_name'],
			'role' => $row['role'],
			'role_text' => $role_text,
			'mvp_count' => $row['mvp_count'],
			'kills' => $row['kills'],
			'deaths' => $row['deaths'],
			'assists' => $row['assists'],
			'goals_for' => $row['goals_for'],
			'goals_against' => $row['goals_against'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
		);
	}
	$smcFunc['db_free_result']($request);

	// Get ladder standings for this clan
	$context['rivals_clan_standings'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT s.*, l.name AS ladder_name, l.ranking_system, l.ladder_style
		FROM {db_prefix}rivals_standings AS s
			INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = s.id_ladder)
		WHERE s.id_clan = {int:clan}
		ORDER BY l.name',
		array('clan' => $id_clan)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_clan_standings'][] = $row;
	$smcFunc['db_free_result']($request);

	// Recent matches
	$context['rivals_clan_matches'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT m.id_match, m.id_ladder, m.challenger_id, m.challengee_id,
			m.challenger_score, m.challengee_score, m.winner_id,
			m.completed_at, l.name AS ladder_name,
			c1.name AS challenger_name, c2.name AS challengee_name
		FROM {db_prefix}rivals_matches AS m
			LEFT JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = m.id_ladder)
			LEFT JOIN {db_prefix}rivals_clans AS c1 ON (c1.id_clan = m.challenger_id)
			LEFT JOIN {db_prefix}rivals_clans AS c2 ON (c2.id_clan = m.challengee_id)
		WHERE m.status = 1
			AND m.match_type = 0
			AND (m.challenger_id = {int:clan} OR m.challengee_id = {int:clan})
		ORDER BY m.completed_at DESC
		LIMIT 10',
		array('clan' => $id_clan)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_clan_matches'][] = $row;
	$smcFunc['db_free_result']($request);

	// Check if current user is a member / leader
	$context['rivals_is_member'] = false;
	$context['rivals_is_leader'] = false;
	$context['rivals_is_pending'] = false;
	if (!$user_info['is_guest'])
	{
		$request = $smcFunc['db_query']('', '
			SELECT role, is_pending
			FROM {db_prefix}rivals_clan_members
			WHERE id_clan = {int:clan}
				AND id_member = {int:member}',
			array('clan' => $id_clan, 'member' => $user_info['id'])
		);
		$membership = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($membership))
		{
			if ($membership['is_pending'])
				$context['rivals_is_pending'] = true;
			else
			{
				$context['rivals_is_member'] = true;
				$context['rivals_is_leader'] = $membership['role'] >= 1;
			}
		}
	}

	// Can the current user challenge this clan?
	$context['rivals_can_challenge'] = false;
	if (!$user_info['is_guest'] && !$context['rivals_is_member'] && allowedTo('rivals_challenge'))
	{
		$my_clan = rivals_get_member_clan($user_info['id']);
		if ($my_clan > 0 && $my_clan != $id_clan)
			$context['rivals_can_challenge'] = true;
	}

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=clans',
		'name' => $txt['rivals_clans'],
	);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=clan;id=' . $id_clan,
		'name' => $clan['name'],
	);
}

/**
 * Create a new clan.
 */
function RivalsCreateClan()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info, $modSettings, $settings;

	isAllowedTo('rivals_manage_clan');

	if ($user_info['is_guest'])
		fatal_lang_error('rivals_error_not_logged_in', false);

	$context['sub_template'] = 'rivals_create_clan';
	$context['page_title'] = $txt['rivals_create_clan'];
	loadTemplate('RivalsClan');

	$context['rivals_errors'] = array();

	// Check minimum posts
	if (!empty($modSettings['rivals_min_posts']) && $user_info['posts'] < $modSettings['rivals_min_posts'])
	{
		$context['rivals_errors'][] = sprintf($txt['rivals_error_min_posts'] ?? 'You need at least %d posts.', $modSettings['rivals_min_posts']);
		return;
	}

	// Check if user already has a clan
	$request = $smcFunc['db_query']('', '
		SELECT cm.id_clan
		FROM {db_prefix}rivals_clan_members AS cm
		WHERE cm.id_member = {int:member}
			AND cm.role = 1
			AND cm.is_pending = 0',
		array('member' => $user_info['id'])
	);
	if ($smcFunc['db_num_rows']($request) > 0)
	{
		$context['rivals_errors'][] = $txt['rivals_error_already_leader'] ?? 'You already lead a clan.';
		$smcFunc['db_free_result']($request);
		return;
	}
	$smcFunc['db_free_result']($request);

	// Handle form submission
	if (isset($_POST['create_clan']))
	{
		checkSession();

		$name = isset($_POST['clan_name']) ? trim($smcFunc['htmlspecialchars']($_POST['clan_name'])) : '';
		$description = isset($_POST['clan_description']) ? trim($smcFunc['htmlspecialchars']($_POST['clan_description'])) : '';
		$website = isset($_POST['clan_website']) ? trim($smcFunc['htmlspecialchars']($_POST['clan_website'])) : '';

		// Validate name
		if (empty($name) || strlen($name) < 2)
		{
			$context['rivals_errors'][] = $txt['rivals_error_clan_name_short'] ?? 'Clan name must be at least 2 characters.';
			return;
		}

		// Check name uniqueness
		$request = $smcFunc['db_query']('', '
			SELECT id_clan
			FROM {db_prefix}rivals_clans
			WHERE name = {string:name}',
			array('name' => $name)
		);
		if ($smcFunc['db_num_rows']($request) > 0)
		{
			$context['rivals_errors'][] = $txt['rivals_error_clan_name_taken'];
			$smcFunc['db_free_result']($request);
			return;
		}
		$smcFunc['db_free_result']($request);

		// Handle logo upload
		$logo = '';
		$logo_ext = '';
		$logo_width = 0;
		$logo_height = 0;

		if (!empty($_FILES['clan_logo']['name']) && $_FILES['clan_logo']['error'] == 0)
		{
			$upload_result = rivals_process_logo_upload($_FILES['clan_logo'], 'clanlogo');
			if ($upload_result['error'])
			{
				$context['rivals_errors'][] = $upload_result['message'];
				return;
			}
			$logo = $upload_result['filename'];
			$logo_ext = $upload_result['ext'];
			$logo_width = $upload_result['width'];
			$logo_height = $upload_result['height'];
		}

		// Insert clan
		$smcFunc['db_insert']('',
			'{db_prefix}rivals_clans',
			array(
				'name' => 'string', 'description' => 'string', 'website' => 'string',
				'logo' => 'string', 'logo_ext' => 'string',
				'logo_width' => 'int', 'logo_height' => 'int',
				'created_at' => 'int',
			),
			array($name, $description, $website, $logo, $logo_ext, $logo_width, $logo_height, time()),
			array('id_clan')
		);
		$new_clan_id = $smcFunc['db_insert_id']('{db_prefix}rivals_clans');

		// Add creator as leader
		$smcFunc['db_insert']('',
			'{db_prefix}rivals_clan_members',
			array('id_clan' => 'int', 'id_member' => 'int', 'role' => 'int', 'is_pending' => 'int'),
			array($new_clan_id, $user_info['id'], 1, 0),
			array('id_clan', 'id_member')
		);

		// Set as active clan
		updateMemberData($user_info['id'], array('rivals_clan_session' => $new_clan_id));

		redirectexit('action=rivals;sa=clan;id=' . $new_clan_id);
	}

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=createclan',
		'name' => $txt['rivals_create_clan'],
	);
}

/**
 * Join a clan (request to join).
 */
function RivalsJoinClan()
{
	global $context, $smcFunc, $txt, $user_info;

	isAllowedTo('rivals_manage_clan');

	if ($user_info['is_guest'])
		fatal_lang_error('rivals_error_not_logged_in', false);

	$id_clan = isset($_GET['id']) ? (int) $_GET['id'] : 0;
	if ($id_clan <= 0)
		fatal_lang_error('rivals_clan_not_found', false);

	checkSession('get');

	// Verify clan exists and is open
	$request = $smcFunc['db_query']('', '
		SELECT id_clan, name, is_closed
		FROM {db_prefix}rivals_clans
		WHERE id_clan = {int:clan}',
		array('clan' => $id_clan)
	);
	$clan = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($clan))
		fatal_lang_error('rivals_clan_not_found', false);

	if (!empty($clan['is_closed']))
		fatal_lang_error('rivals_error_clan_closed', false);

	// Check if already a member
	$request = $smcFunc['db_query']('', '
		SELECT id_clan
		FROM {db_prefix}rivals_clan_members
		WHERE id_clan = {int:clan}
			AND id_member = {int:member}',
		array('clan' => $id_clan, 'member' => $user_info['id'])
	);
	if ($smcFunc['db_num_rows']($request) > 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('rivals_error_already_in_clan', false);
	}
	$smcFunc['db_free_result']($request);

	// Insert as pending
	$smcFunc['db_insert']('',
		'{db_prefix}rivals_clan_members',
		array('id_clan' => 'int', 'id_member' => 'int', 'role' => 'int', 'is_pending' => 'int'),
		array($id_clan, $user_info['id'], 0, 1),
		array('id_clan', 'id_member')
	);

	// Alert clan leaders
	$request = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}rivals_clan_members
		WHERE id_clan = {int:clan}
			AND role >= 1
			AND is_pending = 0',
		array('clan' => $id_clan)
	);
	$leaders = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$leaders[] = $row['id_member'];
	$smcFunc['db_free_result']($request);

	if (!empty($leaders))
	{
		rivals_send_alert('clan_join_request', $leaders, $user_info['id'], array(
			'content_id' => $id_clan,
			'content_name' => $clan['name'],
			'clan_id' => $id_clan,
		));
	}

	redirectexit('action=rivals;sa=clan;id=' . $id_clan);
}

/**
 * Clan management dashboard.
 */
function RivalsManageClan()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info;

	isAllowedTo('rivals_manage_clan');

	$context['sub_template'] = 'rivals_manage_clan';
	$context['page_title'] = $txt['rivals_manage_clan'];
	loadTemplate('RivalsClan');

	$clan_id = rivals_get_member_clan($user_info['id']);
	if ($clan_id <= 0)
	{
		redirectexit('action=rivals;sa=createclan');
		return;
	}

	if (!rivals_is_clan_leader($clan_id, $user_info['id']) && !allowedTo('rivals_admin'))
		fatal_lang_error('rivals_error_no_permission', false);

	// Fetch clan data
	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}rivals_clans
		WHERE id_clan = {int:clan}',
		array('clan' => $clan_id)
	);
	$context['rivals_clan'] = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Count members
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) FROM {db_prefix}rivals_clan_members
		WHERE id_clan = {int:clan} AND is_pending = 0',
		array('clan' => $clan_id)
	);
	list ($context['rivals_member_count']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Count pending
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) FROM {db_prefix}rivals_clan_members
		WHERE id_clan = {int:clan} AND is_pending = 1',
		array('clan' => $clan_id)
	);
	list ($context['rivals_pending_count']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Count open challenges
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) FROM {db_prefix}rivals_challenges
		WHERE challengee_id = {int:clan} AND is_1v1 = 0',
		array('clan' => $clan_id)
	);
	list ($context['rivals_challenge_count']) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=manageclan',
		'name' => $txt['rivals_manage_clan'],
	);
}

/**
 * Edit clan details (name, description, website, logo, favorites, GUID, UAC).
 */
function RivalsEditClan()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info, $modSettings, $settings;

	isAllowedTo('rivals_manage_clan');

	$clan_id = rivals_get_member_clan($user_info['id']);
	if ($clan_id <= 0)
		redirectexit('action=rivals;sa=clans');

	if (!rivals_is_clan_leader($clan_id, $user_info['id']) && !allowedTo('rivals_admin'))
		fatal_lang_error('rivals_error_no_permission', false);

	$context['sub_template'] = 'rivals_edit_clan';
	$context['page_title'] = $txt['rivals_edit_clan'];
	loadTemplate('RivalsClan');

	$context['rivals_errors'] = array();

	// Fetch current data
	$request = $smcFunc['db_query']('', '
		SELECT * FROM {db_prefix}rivals_clans WHERE id_clan = {int:clan}',
		array('clan' => $clan_id)
	);
	$context['rivals_clan'] = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Handle form submission
	if (isset($_POST['save_clan']))
	{
		checkSession();

		$name = trim($smcFunc['htmlspecialchars']($_POST['clan_name'] ?? ''));
		$description = trim($smcFunc['htmlspecialchars']($_POST['clan_description'] ?? ''));
		$website = trim($smcFunc['htmlspecialchars']($_POST['clan_website'] ?? ''));
		$favorite_maps = trim($smcFunc['htmlspecialchars']($_POST['favorite_maps'] ?? ''));
		$favorite_teams = trim($smcFunc['htmlspecialchars']($_POST['favorite_teams'] ?? ''));
		$guid = trim(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['guid'] ?? ''));
		$uac = trim(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['uac'] ?? ''));
		$is_closed = isset($_POST['is_closed']) ? 1 : 0;

		if (empty($name) || strlen($name) < 2)
		{
			$context['rivals_errors'][] = $txt['rivals_error_clan_name_short'] ?? 'Clan name must be at least 2 characters.';
			return;
		}

		// Check name uniqueness (exclude self)
		$request = $smcFunc['db_query']('', '
			SELECT id_clan FROM {db_prefix}rivals_clans
			WHERE name = {string:name} AND id_clan != {int:clan}',
			array('name' => $name, 'clan' => $clan_id)
		);
		if ($smcFunc['db_num_rows']($request) > 0)
		{
			$context['rivals_errors'][] = $txt['rivals_error_clan_name_taken'];
			$smcFunc['db_free_result']($request);
			return;
		}
		$smcFunc['db_free_result']($request);

		$update = array(
			'name' => $name,
			'description' => $description,
			'website' => $website,
			'favorite_maps' => $favorite_maps,
			'favorite_teams' => $favorite_teams,
			'guid' => substr($guid, 0, 8),
			'uac' => substr($uac, 0, 6),
			'is_closed' => $is_closed,
		);

		// Handle logo upload
		if (!empty($_FILES['clan_logo']['name']) && $_FILES['clan_logo']['error'] == 0)
		{
			$upload_result = rivals_process_logo_upload($_FILES['clan_logo'], 'clanlogo');
			if ($upload_result['error'])
			{
				$context['rivals_errors'][] = $upload_result['message'];
				return;
			}
			$update['logo'] = $upload_result['filename'];
			$update['logo_ext'] = $upload_result['ext'];
			$update['logo_width'] = $upload_result['width'];
			$update['logo_height'] = $upload_result['height'];
		}

		// Build SET clause
		$set_parts = array();
		$params = array('clan' => $clan_id);
		foreach ($update as $key => $value)
		{
			$param_key = 'val_' . $key;
			if (is_int($value))
			{
				$set_parts[] = $key . ' = {int:' . $param_key . '}';
				$params[$param_key] = $value;
			}
			else
			{
				$set_parts[] = $key . ' = {string:' . $param_key . '}';
				$params[$param_key] = $value;
			}
		}

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}rivals_clans
			SET ' . implode(', ', $set_parts) . '
			WHERE id_clan = {int:clan}',
			$params
		);

		// Refresh data
		$request = $smcFunc['db_query']('', '
			SELECT * FROM {db_prefix}rivals_clans WHERE id_clan = {int:clan}',
			array('clan' => $clan_id)
		);
		$context['rivals_clan'] = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		$context['rivals_saved'] = true;
	}

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=editclan',
		'name' => $txt['rivals_edit_clan'],
	);
}

/**
 * Manage clan members (promote/demote/kick).
 */
function RivalsClanMembers()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info;

	isAllowedTo('rivals_manage_clan');

	$clan_id = rivals_get_member_clan($user_info['id']);
	if ($clan_id <= 0)
		redirectexit('action=rivals;sa=clans');

	if (!rivals_is_clan_leader($clan_id, $user_info['id']) && !allowedTo('rivals_admin'))
		fatal_lang_error('rivals_error_no_permission', false);

	$context['sub_template'] = 'rivals_clan_members';
	$context['page_title'] = $txt['rivals_clan_members'];
	loadTemplate('RivalsClan');

	// Handle actions
	if (isset($_POST['member_action']) && isset($_POST['target_member']))
	{
		checkSession();
		$target = (int) $_POST['target_member'];
		$action = $_POST['member_action'];

		if ($target > 0 && $target != $user_info['id'])
		{
			switch ($action)
			{
				case 'promote':
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}rivals_clan_members
						SET role = LEAST(role + 1, 2)
						WHERE id_clan = {int:clan} AND id_member = {int:member} AND is_pending = 0',
						array('clan' => $clan_id, 'member' => $target)
					);
					break;

				case 'demote':
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}rivals_clan_members
						SET role = GREATEST(role - 1, 0)
						WHERE id_clan = {int:clan} AND id_member = {int:member} AND is_pending = 0',
						array('clan' => $clan_id, 'member' => $target)
					);
					break;

				case 'kick':
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}rivals_clan_members
						WHERE id_clan = {int:clan} AND id_member = {int:member}',
						array('clan' => $clan_id, 'member' => $target)
					);
					// Clear their clan session if it was this clan
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}members
						SET rivals_clan_session = 0
						WHERE id_member = {int:member} AND rivals_clan_session = {int:clan}',
						array('member' => $target, 'clan' => $clan_id)
					);
					break;
			}
		}
	}

	// Fetch members
	$context['rivals_clan_members'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT cm.id_member, cm.role, cm.mvp_count, cm.kills, cm.deaths, cm.assists,
			mem.real_name, mem.rivals_gamer_name
		FROM {db_prefix}rivals_clan_members AS cm
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = cm.id_member)
		WHERE cm.id_clan = {int:clan}
			AND cm.is_pending = 0
		ORDER BY cm.role DESC, mem.real_name ASC',
		array('clan' => $clan_id)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$role_text = $txt['rivals_role_member'];
		if ($row['role'] == 1) $role_text = $txt['rivals_role_leader'];
		elseif ($row['role'] == 2) $role_text = $txt['rivals_role_coleader'];

		$context['rivals_clan_members'][$row['id_member']] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'gamer_name' => $row['rivals_gamer_name'],
			'role' => $row['role'],
			'role_text' => $role_text,
			'mvp_count' => $row['mvp_count'],
			'kills' => $row['kills'],
			'deaths' => $row['deaths'],
			'assists' => $row['assists'],
		);
	}
	$smcFunc['db_free_result']($request);

	$context['rivals_clan_id'] = $clan_id;
}

/**
 * Approve/deny pending join requests.
 */
function RivalsPendingMembers()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info;

	isAllowedTo('rivals_manage_clan');

	$clan_id = rivals_get_member_clan($user_info['id']);
	if ($clan_id <= 0)
		redirectexit('action=rivals;sa=clans');

	if (!rivals_is_clan_leader($clan_id, $user_info['id']) && !allowedTo('rivals_admin'))
		fatal_lang_error('rivals_error_no_permission', false);

	$context['sub_template'] = 'rivals_pending_members';
	$context['page_title'] = $txt['rivals_pending_members'];
	loadTemplate('RivalsClan');

	// Handle approve/deny
	if (isset($_POST['pending_action']) && isset($_POST['target_member']))
	{
		checkSession();
		$target = (int) $_POST['target_member'];
		$action = $_POST['pending_action'];

		if ($target > 0)
		{
			if ($action === 'approve')
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}rivals_clan_members
					SET is_pending = 0
					WHERE id_clan = {int:clan} AND id_member = {int:member}',
					array('clan' => $clan_id, 'member' => $target)
				);

				// Get clan name for alert
				$request = $smcFunc['db_query']('', '
					SELECT name FROM {db_prefix}rivals_clans WHERE id_clan = {int:clan}',
					array('clan' => $clan_id)
				);
				$clan = $smcFunc['db_fetch_assoc']($request);
				$smcFunc['db_free_result']($request);

				rivals_send_alert('clan_join_approved', $target, $user_info['id'], array(
					'content_id' => $clan_id,
					'content_name' => $clan['name'],
					'clan_id' => $clan_id,
				));
			}
			elseif ($action === 'deny')
			{
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}rivals_clan_members
					WHERE id_clan = {int:clan} AND id_member = {int:member} AND is_pending = 1',
					array('clan' => $clan_id, 'member' => $target)
				);
			}
		}
	}

	// Fetch pending members
	$context['rivals_pending'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT cm.id_member, mem.real_name, mem.rivals_gamer_name, mem.posts, mem.date_registered
		FROM {db_prefix}rivals_clan_members AS cm
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = cm.id_member)
		WHERE cm.id_clan = {int:clan}
			AND cm.is_pending = 1
		ORDER BY mem.real_name ASC',
		array('clan' => $clan_id)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['rivals_pending'][] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'gamer_name' => $row['rivals_gamer_name'],
			'posts' => $row['posts'],
			'registered' => timeformat($row['date_registered']),
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
		);
	}
	$smcFunc['db_free_result']($request);

	$context['rivals_clan_id'] = $clan_id;
}

/**
 * Invite members to clan.
 */
function RivalsInviteMembers()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info;

	isAllowedTo('rivals_manage_clan');

	$clan_id = rivals_get_member_clan($user_info['id']);
	if ($clan_id <= 0)
		redirectexit('action=rivals;sa=clans');

	if (!rivals_is_clan_leader($clan_id, $user_info['id']) && !allowedTo('rivals_admin'))
		fatal_lang_error('rivals_error_no_permission', false);

	$context['sub_template'] = 'rivals_invite_members';
	$context['page_title'] = $txt['rivals_invite_members'];
	loadTemplate('RivalsClan');

	$context['rivals_invite_sent'] = false;

	if (isset($_POST['invite_member']))
	{
		checkSession();
		$target_id = isset($_POST['member_id']) ? (int) $_POST['member_id'] : 0;

		if ($target_id > 0)
		{
			// Check target isn't already a member
			$request = $smcFunc['db_query']('', '
				SELECT id_clan FROM {db_prefix}rivals_clan_members
				WHERE id_clan = {int:clan} AND id_member = {int:member}',
				array('clan' => $clan_id, 'member' => $target_id)
			);
			if ($smcFunc['db_num_rows']($request) == 0)
			{
				$request2 = $smcFunc['db_query']('', '
					SELECT name FROM {db_prefix}rivals_clans WHERE id_clan = {int:clan}',
					array('clan' => $clan_id)
				);
				$clan = $smcFunc['db_fetch_assoc']($request2);
				$smcFunc['db_free_result']($request2);

				rivals_send_alert('clan_invite', $target_id, $user_info['id'], array(
					'content_id' => $clan_id,
					'content_name' => $clan['name'],
					'clan_id' => $clan_id,
				));
				$context['rivals_invite_sent'] = true;
			}
			$smcFunc['db_free_result']($request);
		}
	}

	$context['rivals_clan_id'] = $clan_id;
}

/**
 * Manage rosters.
 */
function RivalsManageRoster()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info;

	isAllowedTo('rivals_manage_clan');

	$clan_id = rivals_get_member_clan($user_info['id']);
	if ($clan_id <= 0)
		redirectexit('action=rivals;sa=clans');

	if (!rivals_is_clan_leader($clan_id, $user_info['id']) && !allowedTo('rivals_admin'))
		fatal_lang_error('rivals_error_no_permission', false);

	$context['sub_template'] = 'rivals_manage_roster';
	$context['page_title'] = $txt['rivals_manage_roster'];
	loadTemplate('RivalsClan');

	// Handle create roster
	if (isset($_POST['create_roster']))
	{
		checkSession();
		$roster_name = trim($smcFunc['htmlspecialchars']($_POST['roster_name'] ?? ''));
		$members = isset($_POST['roster_members']) ? array_filter(array_map('intval', $_POST['roster_members'])) : array();

		if (!empty($roster_name) && !empty($members))
		{
			$smcFunc['db_insert']('',
				'{db_prefix}rivals_rosters',
				array('id_clan' => 'int', 'name' => 'string', 'members' => 'string', 'id_leader' => 'int'),
				array($clan_id, $roster_name, implode('|', $members), $user_info['id']),
				array('id_roster')
			);
		}
	}

	// Handle delete roster
	if (isset($_POST['delete_roster']))
	{
		checkSession();
		$roster_id = (int) ($_POST['roster_id'] ?? 0);
		if ($roster_id > 0)
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}rivals_rosters
				WHERE id_roster = {int:roster} AND id_clan = {int:clan}',
				array('roster' => $roster_id, 'clan' => $clan_id)
			);
		}
	}

	// Fetch existing rosters
	$context['rivals_rosters'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT id_roster, name, members, id_leader
		FROM {db_prefix}rivals_rosters
		WHERE id_clan = {int:clan}
		ORDER BY name',
		array('clan' => $clan_id)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['rivals_rosters'][] = array(
			'id' => $row['id_roster'],
			'name' => $row['name'],
			'members' => $row['members'],
			'member_count' => !empty($row['members']) ? count(explode('|', $row['members'])) : 0,
		);
	}
	$smcFunc['db_free_result']($request);

	// Fetch available members for roster creation
	$context['rivals_available_members'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT cm.id_member, mem.real_name
		FROM {db_prefix}rivals_clan_members AS cm
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = cm.id_member)
		WHERE cm.id_clan = {int:clan} AND cm.is_pending = 0
		ORDER BY mem.real_name',
		array('clan' => $clan_id)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_available_members'][$row['id_member']] = $row['real_name'];
	$smcFunc['db_free_result']($request);

	$context['rivals_clan_id'] = $clan_id;
}

/**
 * Clan internal message board.
 */
function RivalsClanChat()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info;

	$clan_id = rivals_get_member_clan($user_info['id']);
	if ($clan_id <= 0)
		redirectexit('action=rivals;sa=clans');

	$context['sub_template'] = 'rivals_clan_chat';
	$context['page_title'] = $txt['rivals_clan_chat'];
	loadTemplate('RivalsClan');

	// Handle new message
	if (isset($_POST['send_message']))
	{
		checkSession();
		$body = trim($smcFunc['htmlspecialchars']($_POST['message_body'] ?? ''));

		if (!empty($body))
		{
			$smcFunc['db_insert']('',
				'{db_prefix}rivals_clan_messages',
				array('id_clan' => 'int', 'id_member' => 'int', 'body' => 'string', 'created_at' => 'int'),
				array($clan_id, $user_info['id'], $body, time()),
				array('id_message')
			);
		}
	}

	// Fetch messages
	$context['rivals_messages'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT cm.id_message, cm.id_member, cm.body, cm.created_at,
			COALESCE(mem.real_name, {string:unknown}) AS poster_name
		FROM {db_prefix}rivals_clan_messages AS cm
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = cm.id_member)
		WHERE cm.id_clan = {int:clan}
		ORDER BY cm.created_at DESC
		LIMIT 50',
		array('clan' => $clan_id, 'unknown' => '???')
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['rivals_messages'][] = array(
			'id' => $row['id_message'],
			'id_member' => $row['id_member'],
			'poster_name' => $row['poster_name'],
			'body' => $row['body'],
			'time' => timeformat($row['created_at']),
		);
	}
	$smcFunc['db_free_result']($request);

	$context['rivals_clan_id'] = $clan_id;
}

/**
 * Process a logo upload.
 *
 * @param array $file $_FILES array element
 * @param string $subdir Subdirectory under images/rivals/
 * @return array Result with 'error', 'message', 'filename', 'ext', 'width', 'height'
 */
function rivals_process_logo_upload($file, $subdir)
{
	global $modSettings, $settings;

	$result = array('error' => false, 'message' => '', 'filename' => '', 'ext' => '', 'width' => 0, 'height' => 0);

	$max_size = !empty($modSettings['rivals_logo_max_size']) ? (int) $modSettings['rivals_logo_max_size'] : 163840;
	$max_width = !empty($modSettings['rivals_logo_max_width']) ? (int) $modSettings['rivals_logo_max_width'] : 500;
	$max_height = !empty($modSettings['rivals_logo_max_height']) ? (int) $modSettings['rivals_logo_max_height'] : 500;

	// Check file size
	if ($file['size'] > $max_size)
	{
		$result['error'] = true;
		$result['message'] = 'rivals_error_logo_too_large';
		return $result;
	}

	// Check file type
	$allowed_types = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif');
	$mime = $file['type'];
	if (!isset($allowed_types[$mime]))
	{
		$result['error'] = true;
		$result['message'] = 'rivals_error_invalid_format';
		return $result;
	}

	// Get image dimensions
	$image_info = @getimagesize($file['tmp_name']);
	if ($image_info === false)
	{
		$result['error'] = true;
		$result['message'] = 'rivals_error_invalid_format';
		return $result;
	}

	if ($image_info[0] > $max_width || $image_info[1] > $max_height)
	{
		$result['error'] = true;
		$result['message'] = 'rivals_error_logo_dimensions';
		return $result;
	}

	$ext = $allowed_types[$mime];
	$filename = md5(time() . $file['name'] . mt_rand()) . '.' . $ext;
	$dest = $settings['default_theme_dir'] . '/images/rivals/' . $subdir . '/' . $filename;

	if (!move_uploaded_file($file['tmp_name'], $dest))
	{
		$result['error'] = true;
		$result['message'] = 'Upload failed.';
		return $result;
	}

	$result['filename'] = $filename;
	$result['ext'] = $ext;
	$result['width'] = $image_info[0];
	$result['height'] = $image_info[1];

	return $result;
}
?>