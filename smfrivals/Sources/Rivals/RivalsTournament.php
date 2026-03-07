<?php
/**
 * SMF Rivals - Tournament Display, Brackets, Signup
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Tournament listing page.
 * Shows active, signups open, and archived tournaments.
 */
function RivalsTournamentList()
{
	global $context, $smcFunc, $txt, $scripturl, $settings;

	$context['sub_template'] = 'rivals_tournament_list';
	loadTemplate('RivalsTournament');
	$context['page_title'] = $txt['rivals_tournaments'];

	// Filter by status
	$status_filter = isset($_GET['status']) ? (int) $_GET['status'] : -1;

	$where = '1=1';
	$params = array();

	if ($status_filter >= 0 && $status_filter <= 3)
	{
		$where = 't.status = {int:status}';
		$params['status'] = $status_filter;
	}

	// Pagination
	$per_page = 20;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) FROM {db_prefix}rivals_tournaments AS t WHERE ' . $where,
		$params
	);
	list ($total) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex(
		$scripturl . '?action=rivals;sa=tournaments' . ($status_filter >= 0 ? ';status=' . $status_filter : ''),
		$start, $total, $per_page, true
	);

	$context['rivals_tournaments'] = array();
	$context['rivals_status_filter'] = $status_filter;

	$request = $smcFunc['db_query']('', '
		SELECT t.*
		FROM {db_prefix}rivals_tournaments AS t
		WHERE ' . $where . '
		ORDER BY t.status ASC, t.created_at DESC
		LIMIT {int:start}, {int:limit}',
		array_merge($params, array('start' => $start, 'limit' => $per_page))
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Count entries
		$req2 = $smcFunc['db_query']('', '
			SELECT COUNT(*) FROM {db_prefix}rivals_tournament_entries
			WHERE id_tournament = {int:tid}',
			array('tid' => $row['id_tournament'])
		);
		list ($entry_count) = $smcFunc['db_fetch_row']($req2);
		$smcFunc['db_free_result']($req2);

		$status_labels = array(
			0 => $txt['rivals_tournament_draft'],
			1 => $txt['rivals_tournament_signups'],
			2 => $txt['rivals_tournament_active'],
			3 => $txt['rivals_tournament_archived'],
		);

		$type_labels = array(
			0 => $txt['rivals_single_elimination'],
			1 => $txt['rivals_double_elimination'],
			2 => $txt['rivals_round_robin'],
			3 => $txt['rivals_league'],
		);

		$context['rivals_tournaments'][$row['id_tournament']] = array(
			'id' => $row['id_tournament'],
			'name' => $row['name'],
			'short_name' => $row['short_name'],
			'logo' => $row['logo'],
			'info' => $row['info'],
			'bracket_size' => $row['bracket_size'],
			'status' => $row['status'],
			'status_label' => isset($status_labels[$row['status']]) ? $status_labels[$row['status']] : '?',
			'tournament_type' => $row['tournament_type'],
			'type_label' => isset($type_labels[$row['tournament_type']]) ? $type_labels[$row['tournament_type']] : '?',
			'is_user_based' => !empty($row['is_user_based']),
			'entry_count' => $entry_count,
			'start_date' => !empty($row['start_date']) ? timeformat($row['start_date']) : '-',
			'created_at' => timeformat($row['created_at']),
			'href_brackets' => $scripturl . '?action=rivals;sa=brackets;tournament=' . $row['id_tournament'],
			'href_signup' => $scripturl . '?action=rivals;sa=signup;tournament=' . $row['id_tournament'],
		);
	}
	$smcFunc['db_free_result']($request);

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=tournaments',
		'name' => $txt['rivals_tournaments'],
	);
}

/**
 * Display tournament brackets.
 * Renders single/double elimination bracket visualization.
 */
function RivalsBrackets()
{
	global $context, $smcFunc, $txt, $scripturl, $settings;

	$context['sub_template'] = 'rivals_brackets';
	loadTemplate('RivalsTournament');

	$id_tournament = isset($_GET['tournament']) ? (int) $_GET['tournament'] : 0;
	if ($id_tournament <= 0)
		fatal_lang_error('rivals_tournament_not_found', false);

	// Fetch tournament
	$request = $smcFunc['db_query']('', '
		SELECT * FROM {db_prefix}rivals_tournaments
		WHERE id_tournament = {int:tid}',
		array('tid' => $id_tournament)
	);
	$tournament = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($tournament))
		fatal_lang_error('rivals_tournament_not_found', false);

	$context['rivals_tournament'] = $tournament;
	$context['page_title'] = $tournament['name'] . ' - ' . $txt['rivals_tournament_brackets'];

	$is_user_based = !empty($tournament['is_user_based']);
	$is_double_elim = ($tournament['tournament_type'] == 1);

	// Load all entries
	$request = $smcFunc['db_query']('', '
		SELECT * FROM {db_prefix}rivals_tournament_entries
		WHERE id_tournament = {int:tid}
		ORDER BY bracket_round, position',
		array('tid' => $id_tournament)
	);
	$entries = array();
	$entity_ids = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$entries[] = $row;
		if ($row['id_clan'] > 0)
			$entity_ids['clan'][$row['id_clan']] = $row['id_clan'];
	}
	$smcFunc['db_free_result']($request);

	// Load all match results
	$request = $smcFunc['db_query']('', '
		SELECT * FROM {db_prefix}rivals_tournament_matches
		WHERE id_tournament = {int:tid}',
		array('tid' => $id_tournament)
	);
	$matches = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$matches[$row['match_uid']] = $row;
		if ($row['team1_id'] > 0)
			$entity_ids['clan'][$row['team1_id']] = $row['team1_id'];
		if ($row['team2_id'] > 0)
			$entity_ids['clan'][$row['team2_id']] = $row['team2_id'];
	}
	$smcFunc['db_free_result']($request);

	// Resolve entity names
	$entity_names = array();
	if (!empty($entity_ids['clan']))
	{
		if ($is_user_based)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member, real_name FROM {db_prefix}members
				WHERE id_member IN ({array_int:ids})',
				array('ids' => array_values($entity_ids['clan']))
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$entity_names[$row['id_member']] = $row['real_name'];
			$smcFunc['db_free_result']($request);
		}
		else
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_clan, name FROM {db_prefix}rivals_clans
				WHERE id_clan IN ({array_int:ids})',
				array('ids' => array_values($entity_ids['clan']))
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$entity_names[$row['id_clan']] = $row['name'];
			$smcFunc['db_free_result']($request);
		}
	}

	$context['rivals_entity_names'] = $entity_names;

	// Build bracket rounds structure
	$total_rounds = max(1, (int) ceil(log(max(2, $tournament['bracket_size']), 2)));
	$context['rivals_bracket_rounds'] = array();

	// Organize entries by round
	$entries_by_round = array();
	foreach ($entries as $entry)
	{
		$round = max(1, $entry['bracket_round']);
		if (empty($entry['is_loser']))
			$entries_by_round[$round][] = $entry;
	}

	// Build winners bracket visualization
	for ($round = 1; $round <= $total_rounds; $round++)
	{
		$matches_in_round = max(1, (int) ($tournament['bracket_size'] / pow(2, $round)));

		// Round label
		if ($round == $total_rounds)
			$round_label = $txt['rivals_bracket_final'];
		elseif ($round == $total_rounds - 1 && $total_rounds > 2)
			$round_label = $txt['rivals_bracket_semifinal'];
		elseif ($round == $total_rounds - 2 && $total_rounds > 3)
			$round_label = $txt['rivals_bracket_quarterfinal'];
		else
			$round_label = sprintf($txt['rivals_bracket_round'], $round);

		$round_matches = array();

		for ($m = 1; $m <= $matches_in_round; $m++)
		{
			$match_uid = 'W-' . $round . '-' . $m;

			// Find teams for this position
			$team1_id = 0;
			$team2_id = 0;
			$team1_name = $txt['rivals_bye'];
			$team2_name = $txt['rivals_bye'];
			$team1_score = '';
			$team2_score = '';
			$winner_id = 0;

			// Look up from entries
			if (isset($entries_by_round[$round]))
			{
				$pos1 = ($m - 1) * 2 + 1;
				$pos2 = ($m - 1) * 2 + 2;

				foreach ($entries_by_round[$round] as $entry)
				{
					if ($entry['position'] == $pos1)
					{
						$team1_id = $entry['id_clan'];
						$team1_name = isset($entity_names[$entry['id_clan']]) ? $entity_names[$entry['id_clan']] : $txt['rivals_bye'];
					}
					if ($entry['position'] == $pos2)
					{
						$team2_id = $entry['id_clan'];
						$team2_name = isset($entity_names[$entry['id_clan']]) ? $entity_names[$entry['id_clan']] : $txt['rivals_bye'];
					}
				}
			}

			// Check match results
			if (isset($matches[$match_uid]))
			{
				$match_data = $matches[$match_uid];
				$team1_score = $match_data['team1_score'];
				$team2_score = $match_data['team2_score'];
				$winner_id = $match_data['winner_id'];

				if ($match_data['team1_id'] > 0 && isset($entity_names[$match_data['team1_id']]))
					$team1_name = $entity_names[$match_data['team1_id']];
				if ($match_data['team2_id'] > 0 && isset($entity_names[$match_data['team2_id']]))
					$team2_name = $entity_names[$match_data['team2_id']];
			}

			// Also look up by entry match_uid field
			foreach ($entries as $entry)
			{
				if ($entry['match_uid'] === $match_uid && !empty($entry['id_clan']))
				{
					if (isset($matches[$entry['match_uid']]))
					{
						$md = $matches[$entry['match_uid']];
						$team1_score = $md['team1_score'];
						$team2_score = $md['team2_score'];
						$winner_id = $md['winner_id'];
					}
				}
			}

			$round_matches[] = array(
				'match_uid' => $match_uid,
				'team1_id' => $team1_id,
				'team1_name' => $team1_name,
				'team1_score' => $team1_score,
				'team2_id' => $team2_id,
				'team2_name' => $team2_name,
				'team2_score' => $team2_score,
				'winner_id' => $winner_id,
				'is_bye' => ($team1_id == 0 || $team2_id == 0),
			);
		}

		$context['rivals_bracket_rounds'][$round] = array(
			'label' => $round_label,
			'matches' => $round_matches,
		);
	}

	// Losers bracket (double elimination)
	$context['rivals_losers_bracket'] = array();
	if ($is_double_elim)
	{
		$loser_entries = array();
		foreach ($entries as $entry)
		{
			if (!empty($entry['is_loser']))
				$loser_entries[$entry['bracket_round']][] = $entry;
		}

		$loser_rounds = max(0, ($total_rounds - 1) * 2);
		for ($round = 1; $round <= $loser_rounds; $round++)
		{
			$round_label = $txt['rivals_losers_bracket'] . ' ' . sprintf($txt['rivals_bracket_round'], $round);
			$round_matches = array();

			if (isset($loser_entries[$round]))
			{
				$match_count = max(1, (int) ceil(count($loser_entries[$round]) / 2));
				for ($m = 0; $m < $match_count; $m++)
				{
					$t1 = isset($loser_entries[$round][$m * 2]) ? $loser_entries[$round][$m * 2] : null;
					$t2 = isset($loser_entries[$round][$m * 2 + 1]) ? $loser_entries[$round][$m * 2 + 1] : null;

					$round_matches[] = array(
						'match_uid' => 'L-' . $round . '-' . ($m + 1),
						'team1_id' => $t1 ? $t1['id_clan'] : 0,
						'team1_name' => ($t1 && isset($entity_names[$t1['id_clan']])) ? $entity_names[$t1['id_clan']] : $txt['rivals_bye'],
						'team1_score' => '',
						'team2_id' => $t2 ? $t2['id_clan'] : 0,
						'team2_name' => ($t2 && isset($entity_names[$t2['id_clan']])) ? $entity_names[$t2['id_clan']] : $txt['rivals_bye'],
						'team2_score' => '',
						'winner_id' => 0,
						'is_bye' => false,
					);
				}
			}

			if (!empty($round_matches))
			{
				$context['rivals_losers_bracket'][$round] = array(
					'label' => $round_label,
					'matches' => $round_matches,
				);
			}
		}
	}

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=tournaments',
		'name' => $txt['rivals_tournaments'],
	);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=brackets;tournament=' . $id_tournament,
		'name' => $tournament['name'],
	);
}

/**
 * Tournament signup page.
 */
function RivalsTournamentSignup()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info;

	if ($user_info['is_guest'])
		fatal_lang_error('rivals_error_not_logged_in', false);

	$context['sub_template'] = 'rivals_tournament_signup';
	loadTemplate('RivalsTournament');

	$id_tournament = isset($_GET['tournament']) ? (int) $_GET['tournament'] : 0;

	$request = $smcFunc['db_query']('', '
		SELECT * FROM {db_prefix}rivals_tournaments
		WHERE id_tournament = {int:tid}',
		array('tid' => $id_tournament)
	);
	$tournament = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($tournament))
		fatal_lang_error('rivals_tournament_not_found', false);

	if ($tournament['status'] != 1)
		fatal_lang_error('rivals_error_tournament_closed', false);

	$context['rivals_tournament'] = $tournament;
	$context['page_title'] = $tournament['name'] . ' - ' . $txt['rivals_tournament_signup'];
	$context['rivals_errors'] = array();

	$is_user_based = !empty($tournament['is_user_based']);
	$my_clan = $is_user_based ? 0 : rivals_get_member_clan($user_info['id']);

	// Check if already signed up
	$entity_id = $is_user_based ? $user_info['id'] : $my_clan;

	if ($entity_id > 0)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_entry FROM {db_prefix}rivals_tournament_entries
			WHERE id_tournament = {int:tid} AND id_clan = {int:entity}',
			array('tid' => $id_tournament, 'entity' => $entity_id)
		);
		$context['rivals_already_signed_up'] = $smcFunc['db_num_rows']($request) > 0;
		$smcFunc['db_free_result']($request);
	}
	else
	{
		$context['rivals_already_signed_up'] = false;
	}

	// Check capacity
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) FROM {db_prefix}rivals_tournament_entries
		WHERE id_tournament = {int:tid}',
		array('tid' => $id_tournament)
	);
	list ($entry_count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
	$context['rivals_entry_count'] = $entry_count;
	$context['rivals_is_full'] = ($entry_count >= $tournament['bracket_size']);

	// Load rosters if restricted clan tournament
	$context['rivals_rosters'] = array();
	if (!$is_user_based && !empty($tournament['is_restricted']) && $my_clan > 0)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_roster, name, members
			FROM {db_prefix}rivals_rosters
			WHERE id_clan = {int:clan}',
			array('clan' => $my_clan)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$member_ids = array_filter(array_map('intval', explode('|', $row['members'])));
			$member_count = count($member_ids);

			// Check size constraints
			$valid = true;
			if ($tournament['min_members'] > 0 && $member_count < $tournament['min_members'])
				$valid = false;
			if ($tournament['max_members'] > 0 && $member_count > $tournament['max_members'])
				$valid = false;

			$context['rivals_rosters'][$row['id_roster']] = array(
				'id' => $row['id_roster'],
				'name' => $row['name'],
				'member_count' => $member_count,
				'valid' => $valid,
			);
		}
		$smcFunc['db_free_result']($request);
	}

	// Handle signup submission
	if (isset($_POST['do_signup']))
	{
		checkSession();

		if ($context['rivals_already_signed_up'])
			$context['rivals_errors'][] = $txt['rivals_error_already_signed_up'];

		if ($context['rivals_is_full'])
			$context['rivals_errors'][] = $txt['rivals_error_tournament_full'];

		if (!$is_user_based && $my_clan <= 0)
			$context['rivals_errors'][] = $txt['rivals_error_no_clan'];

		if (!$is_user_based && $my_clan > 0 && !rivals_is_clan_leader($my_clan, $user_info['id']))
			$context['rivals_errors'][] = $txt['rivals_error_no_permission'];

		// Check invite list
		if ($tournament['signup_type'] == 1 && !empty($tournament['invite_list']))
		{
			$invited = array_filter(array_map('intval', explode(',', $tournament['invite_list'])));
			if (!in_array($entity_id, $invited))
				$context['rivals_errors'][] = $txt['rivals_error_not_invited'];
		}

		$id_roster = 0;
		if (!$is_user_based && !empty($tournament['is_restricted']))
		{
			$id_roster = isset($_POST['id_roster']) ? (int) $_POST['id_roster'] : 0;
			if ($id_roster > 0 && isset($context['rivals_rosters'][$id_roster]) && !$context['rivals_rosters'][$id_roster]['valid'])
				$context['rivals_errors'][] = $txt['rivals_error_roster_invalid'];
		}

		if (empty($context['rivals_errors']))
		{
			$smcFunc['db_insert']('',
				'{db_prefix}rivals_tournament_entries',
				array(
					'id_tournament' => 'int',
					'id_clan' => 'int',
					'id_roster' => 'int',
					'bracket_round' => 'int',
					'position' => 'int',
					'created_at' => 'int',
				),
				array(
					$id_tournament,
					$entity_id,
					$id_roster,
					1,
					0, // Unpositioned until admin seeds
					time(),
				),
				array('id_entry')
			);

			redirectexit('action=rivals;sa=brackets;tournament=' . $id_tournament . ';signedup=1');
		}
	}

	// Handle withdraw
	if (isset($_GET['withdraw']))
	{
		checkSession('get');

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}rivals_tournament_entries
			WHERE id_tournament = {int:tid} AND id_clan = {int:entity}',
			array('tid' => $id_tournament, 'entity' => $entity_id)
		);

		redirectexit('action=rivals;sa=brackets;tournament=' . $id_tournament);
	}

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=tournaments',
		'name' => $txt['rivals_tournaments'],
	);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=signup;tournament=' . $id_tournament,
		'name' => $tournament['name'] . ' - ' . $txt['rivals_tournament_signup'],
	);
}

/**
 * View user's tournament entries and report/confirm matches.
 */
function RivalsMyTournaments()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info;

	if ($user_info['is_guest'])
		fatal_lang_error('rivals_error_not_logged_in', false);

	$context['sub_template'] = 'rivals_my_tournaments';
	loadTemplate('RivalsTournament');
	$context['page_title'] = $txt['rivals_my_tournaments'];

	$my_clan = rivals_get_member_clan($user_info['id']);

	// Get all tournament entries for user/clan
	$context['rivals_my_entries'] = array();

	$where_parts = array('te.id_clan = {int:member}');
	$params = array('member' => $user_info['id']);

	if ($my_clan > 0)
	{
		$where_parts[] = 'te.id_clan = {int:clan}';
		$params['clan'] = $my_clan;
	}

	$request = $smcFunc['db_query']('', '
		SELECT te.*, t.name AS tournament_name, t.status AS tournament_status,
			t.tournament_type, t.is_user_based, t.bracket_size
		FROM {db_prefix}rivals_tournament_entries AS te
			INNER JOIN {db_prefix}rivals_tournaments AS t ON (t.id_tournament = te.id_tournament)
		WHERE (' . implode(' OR ', $where_parts) . ')
			AND t.status IN (1, 2)
		ORDER BY t.status ASC, t.created_at DESC',
		$params
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$status_labels = array(
			1 => $txt['rivals_tournament_signups'],
			2 => $txt['rivals_tournament_active'],
		);

		$context['rivals_my_entries'][] = array(
			'id_entry' => $row['id_entry'],
			'id_tournament' => $row['id_tournament'],
			'tournament_name' => $row['tournament_name'],
			'tournament_status' => $row['tournament_status'],
			'status_label' => isset($status_labels[$row['tournament_status']]) ? $status_labels[$row['tournament_status']] : '?',
			'bracket_round' => $row['bracket_round'],
			'position' => $row['position'],
			'match_uid' => $row['match_uid'],
			'href_brackets' => $scripturl . '?action=rivals;sa=brackets;tournament=' . $row['id_tournament'],
		);
	}
	$smcFunc['db_free_result']($request);

	// Handle tournament match reporting
	if (isset($_POST['report_tournament_match']))
	{
		checkSession();

		$match_uid = isset($_POST['match_uid']) ? trim($_POST['match_uid']) : '';
		$id_tournament = (int) $_POST['id_tournament'];
		$team1_score = (int) $_POST['team1_score'];
		$team2_score = (int) $_POST['team2_score'];
		$winner_id = (int) $_POST['winner_id'];

		$mvp1 = isset($_POST['mvp1']) ? (int) $_POST['mvp1'] : 0;
		$mvp2 = isset($_POST['mvp2']) ? (int) $_POST['mvp2'] : 0;
		$mvp3 = isset($_POST['mvp3']) ? (int) $_POST['mvp3'] : 0;

		$my_entity = $my_clan > 0 ? $my_clan : $user_info['id'];

		// Determine team1/team2
		$request = $smcFunc['db_query']('', '
			SELECT * FROM {db_prefix}rivals_tournament_matches
			WHERE match_uid = {string:uid} AND id_tournament = {int:tid}',
			array('uid' => $match_uid, 'tid' => $id_tournament)
		);
		$existing = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($existing))
		{
			// Update existing match
			$confirm_field = ($my_entity == $existing['team1_id']) ? 'team1_confirmed' : 'team2_confirmed';

			$smcFunc['db_query']('', '
				UPDATE {db_prefix}rivals_tournament_matches
				SET team1_score = {int:s1}, team2_score = {int:s2}, winner_id = {int:winner},
					' . $confirm_field . ' = 1,
					mvp1 = {int:mvp1}, mvp2 = {int:mvp2}, mvp3 = {int:mvp3}
				WHERE match_uid = {string:uid} AND id_tournament = {int:tid}',
				array(
					's1' => $team1_score, 's2' => $team2_score, 'winner' => $winner_id,
					'mvp1' => $mvp1, 'mvp2' => $mvp2, 'mvp3' => $mvp3,
					'uid' => $match_uid, 'tid' => $id_tournament,
				)
			);
		}
		else
		{
			// Find opponent for this match position
			$request = $smcFunc['db_query']('', '
				SELECT * FROM {db_prefix}rivals_tournament_entries
				WHERE match_uid = {string:uid} AND id_tournament = {int:tid}',
				array('uid' => $match_uid, 'tid' => $id_tournament)
			);
			$match_entries = array();
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$match_entries[] = $row;
			$smcFunc['db_free_result']($request);

			$team1_id = isset($match_entries[0]) ? $match_entries[0]['id_clan'] : 0;
			$team2_id = isset($match_entries[1]) ? $match_entries[1]['id_clan'] : 0;

			$confirm_field = ($my_entity == $team1_id) ? 1 : 0;

			$smcFunc['db_insert']('replace',
				'{db_prefix}rivals_tournament_matches',
				array(
					'match_uid' => 'string',
					'id_tournament' => 'int',
					'team1_id' => 'int',
					'team2_id' => 'int',
					'team1_score' => 'int',
					'team2_score' => 'int',
					'winner_id' => 'int',
					'team1_confirmed' => 'int',
					'team2_confirmed' => 'int',
					'mvp1' => 'int',
					'mvp2' => 'int',
					'mvp3' => 'int',
				),
				array(
					$match_uid,
					$id_tournament,
					$team1_id,
					$team2_id,
					$team1_score,
					$team2_score,
					$winner_id,
					$confirm_field ? 1 : 0,
					$confirm_field ? 0 : 1,
					$mvp1, $mvp2, $mvp3,
				),
				array('match_uid')
			);
		}

		// Check if both sides confirmed -> advance winner
		$request = $smcFunc['db_query']('', '
			SELECT * FROM {db_prefix}rivals_tournament_matches
			WHERE match_uid = {string:uid} AND id_tournament = {int:tid}',
			array('uid' => $match_uid, 'tid' => $id_tournament)
		);
		$final_match = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($final_match) && $final_match['team1_confirmed'] && $final_match['team2_confirmed'] && $final_match['winner_id'] > 0)
		{
			rivals_advance_tournament_winner($id_tournament, $match_uid, $final_match['winner_id']);
		}

		redirectexit('action=rivals;sa=mytournaments');
	}

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=mytournaments',
		'name' => $txt['rivals_my_tournaments'],
	);
}

/**
 * Advance tournament winner to next round.
 */
function rivals_advance_tournament_winner($id_tournament, $match_uid, $winner_id)
{
	global $smcFunc;

	// Find the winner's current entry
	$request = $smcFunc['db_query']('', '
		SELECT te.*, t.bracket_size, t.tournament_type
		FROM {db_prefix}rivals_tournament_entries AS te
			INNER JOIN {db_prefix}rivals_tournaments AS t ON (t.id_tournament = te.id_tournament)
		WHERE te.id_tournament = {int:tid} AND te.id_clan = {int:clan} AND te.match_uid = {string:uid}',
		array('tid' => $id_tournament, 'clan' => $winner_id, 'uid' => $match_uid)
	);
	$winner_entry = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($winner_entry))
		return;

	$current_round = $winner_entry['bracket_round'];
	$current_pos = $winner_entry['position'];
	$total_rounds = (int) ceil(log(max(2, $winner_entry['bracket_size']), 2));

	// Already at final round? Tournament is finished
	if ($current_round >= $total_rounds)
		return;

	// Calculate next round position
	$next_round = $current_round + 1;
	$next_pos = (int) ceil($current_pos / 2);
	$next_uid = 'W-' . $next_round . '-' . $next_pos;

	// Check if entry already exists in next round
	$request = $smcFunc['db_query']('', '
		SELECT id_entry FROM {db_prefix}rivals_tournament_entries
		WHERE id_tournament = {int:tid} AND id_clan = {int:clan} AND bracket_round = {int:round}',
		array('tid' => $id_tournament, 'clan' => $winner_id, 'round' => $next_round)
	);
	if ($smcFunc['db_num_rows']($request) > 0)
	{
		$smcFunc['db_free_result']($request);
		return; // Already advanced
	}
	$smcFunc['db_free_result']($request);

	// Create next round entry
	$smcFunc['db_insert']('',
		'{db_prefix}rivals_tournament_entries',
		array(
			'id_tournament' => 'int',
			'id_clan' => 'int',
			'id_roster' => 'int',
			'bracket_round' => 'int',
			'position' => 'int',
			'match_uid' => 'string',
			'created_at' => 'int',
		),
		array(
			$id_tournament,
			$winner_id,
			$winner_entry['id_roster'],
			$next_round,
			$next_pos,
			$next_uid,
			time(),
		),
		array('id_entry')
	);
}
?>