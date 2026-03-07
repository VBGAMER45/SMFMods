<?php
/**
 * SMF Rivals - Match/Challenge Workflow
 * Challenge creation, acceptance, reporting, confirmation, dispute.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Create a new challenge.
 * Clan leaders can challenge other clans; users can challenge other users on 1v1 ladders.
 */
function RivalsCreateChallenge()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info, $sourcedir;

	isAllowedTo('rivals_challenge');

	if ($user_info['is_guest'])
		fatal_lang_error('rivals_error_not_logged_in', false);

	$context['sub_template'] = 'rivals_create_challenge';
	loadTemplate('RivalsMatch');
	$context['page_title'] = $txt['rivals_create_challenge'];

	$context['rivals_errors'] = array();
	$context['rivals_my_clan'] = 0;
	$context['rivals_is_1v1'] = false;

	// Determine mode: if ladder is specified, check if 1v1
	$id_ladder = isset($_REQUEST['ladder']) ? (int) $_REQUEST['ladder'] : 0;

	// Get ladders available to the user
	$my_clan = rivals_get_member_clan($user_info['id']);
	$context['rivals_my_clan'] = $my_clan;

	// Fetch ladders user/clan is enrolled in
	$context['rivals_available_ladders'] = array();

	// Clan ladders
	if ($my_clan > 0 && rivals_is_clan_leader($my_clan, $user_info['id']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT s.id_ladder, l.name, l.is_1v1, l.is_locked, l.ranking_system, l.ladder_style
			FROM {db_prefix}rivals_standings AS s
				INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = s.id_ladder)
			WHERE s.id_clan = {int:clan}
				AND l.is_locked = 0
				AND l.is_1v1 = 0',
			array('clan' => $my_clan)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['rivals_available_ladders'][$row['id_ladder']] = array(
				'id_ladder' => $row['id_ladder'],
				'name' => $row['name'],
				'is_1v1' => false,
				'ranking_system' => $row['ranking_system'],
				'ladder_style' => $row['ladder_style'],
			);
		}
		$smcFunc['db_free_result']($request);
	}

	// 1v1 ladders
	$request = $smcFunc['db_query']('', '
		SELECT s.id_ladder, l.name, l.is_1v1, l.is_locked, l.ranking_system, l.ladder_style
		FROM {db_prefix}rivals_standings AS s
			INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = s.id_ladder)
		WHERE s.id_member = {int:member}
			AND l.is_locked = 0
			AND l.is_1v1 = 1',
		array('member' => $user_info['id'])
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['rivals_available_ladders'][$row['id_ladder']] = array(
			'id_ladder' => $row['id_ladder'],
			'name' => $row['name'] . ' (1v1)',
			'is_1v1' => true,
			'ranking_system' => $row['ranking_system'],
			'ladder_style' => $row['ladder_style'],
		);
	}
	$smcFunc['db_free_result']($request);

	// Pre-select ladder if passed
	$context['rivals_selected_ladder'] = $id_ladder;

	// Handle form submission
	if (isset($_POST['submit_challenge']))
	{
		checkSession();

		$id_ladder = (int) $_POST['id_ladder'];
		$opponent_id = (int) $_POST['opponent_id'];
		$is_unranked = !empty($_POST['is_unranked']) ? 1 : 0;
		$details = isset($_POST['details']) ? $smcFunc['htmlspecialchars'](trim($_POST['details']), ENT_QUOTES) : '';

		// Validate ladder
		if (!isset($context['rivals_available_ladders'][$id_ladder]))
			$context['rivals_errors'][] = $txt['rivals_error_invalid_ladder'];

		if (empty($context['rivals_errors']))
		{
			$ladder = $context['rivals_available_ladders'][$id_ladder];
			$is_1v1 = $ladder['is_1v1'];

			if ($is_1v1)
			{
				// Self-challenge check
				if ($opponent_id == $user_info['id'])
					$context['rivals_errors'][] = $txt['rivals_error_challenge_self'];

				// Opponent in ladder?
				if (empty($context['rivals_errors']))
				{
					$request = $smcFunc['db_query']('', '
						SELECT id_standing FROM {db_prefix}rivals_standings
						WHERE id_ladder = {int:ladder} AND id_member = {int:member}',
						array('ladder' => $id_ladder, 'member' => $opponent_id)
					);
					if ($smcFunc['db_num_rows']($request) == 0)
						$context['rivals_errors'][] = $txt['rivals_error_not_in_ladder'];
					$smcFunc['db_free_result']($request);
				}

				// SWAP rank constraint: opponent within 3 ranks
				if (empty($context['rivals_errors']) && $ladder['ranking_system'] == 1)
				{
					$request = $smcFunc['db_query']('', '
						SELECT current_rank FROM {db_prefix}rivals_standings
						WHERE id_ladder = {int:ladder} AND id_member = {int:member}',
						array('ladder' => $id_ladder, 'member' => $user_info['id'])
					);
					$my_standing = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					$request = $smcFunc['db_query']('', '
						SELECT current_rank FROM {db_prefix}rivals_standings
						WHERE id_ladder = {int:ladder} AND id_member = {int:member}',
						array('ladder' => $id_ladder, 'member' => $opponent_id)
					);
					$opp_standing = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					if (!empty($my_standing) && !empty($opp_standing))
					{
						if (abs($my_standing['current_rank'] - $opp_standing['current_rank']) > 3)
							$context['rivals_errors'][] = $txt['rivals_error_rank_distance'];
					}
				}

				// Duplicate challenge check
				if (empty($context['rivals_errors']))
				{
					$request = $smcFunc['db_query']('', '
						SELECT id_challenge FROM {db_prefix}rivals_challenges
						WHERE id_ladder = {int:ladder} AND is_1v1 = 1
							AND ((challenger_id = {int:me} AND challengee_id = {int:opp})
								OR (challenger_id = {int:opp} AND challengee_id = {int:me}))',
						array('ladder' => $id_ladder, 'me' => $user_info['id'], 'opp' => $opponent_id)
					);
					if ($smcFunc['db_num_rows']($request) > 0)
						$context['rivals_errors'][] = $txt['rivals_error_already_challenged'];
					$smcFunc['db_free_result']($request);
				}

				$challenger_id = $user_info['id'];
				$challengee_id = $opponent_id;
			}
			else
			{
				// Clan challenge
				if ($my_clan <= 0)
					$context['rivals_errors'][] = $txt['rivals_error_no_clan'];

				// Self-challenge
				if ($opponent_id == $my_clan)
					$context['rivals_errors'][] = $txt['rivals_error_challenge_self'];

				// Opponent in ladder?
				if (empty($context['rivals_errors']))
				{
					$request = $smcFunc['db_query']('', '
						SELECT id_standing FROM {db_prefix}rivals_standings
						WHERE id_ladder = {int:ladder} AND id_clan = {int:clan}',
						array('ladder' => $id_ladder, 'clan' => $opponent_id)
					);
					if ($smcFunc['db_num_rows']($request) == 0)
						$context['rivals_errors'][] = $txt['rivals_error_not_in_ladder'];
					$smcFunc['db_free_result']($request);
				}

				// SWAP rank constraint
				if (empty($context['rivals_errors']) && $ladder['ranking_system'] == 1)
				{
					$request = $smcFunc['db_query']('', '
						SELECT current_rank FROM {db_prefix}rivals_standings
						WHERE id_ladder = {int:ladder} AND id_clan = {int:clan}',
						array('ladder' => $id_ladder, 'clan' => $my_clan)
					);
					$my_standing = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					$request = $smcFunc['db_query']('', '
						SELECT current_rank FROM {db_prefix}rivals_standings
						WHERE id_ladder = {int:ladder} AND id_clan = {int:clan}',
						array('ladder' => $id_ladder, 'clan' => $opponent_id)
					);
					$opp_standing = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					if (!empty($my_standing) && !empty($opp_standing))
					{
						if (abs($my_standing['current_rank'] - $opp_standing['current_rank']) > 3)
							$context['rivals_errors'][] = $txt['rivals_error_rank_distance'];
					}
				}

				// Duplicate challenge check
				if (empty($context['rivals_errors']))
				{
					$request = $smcFunc['db_query']('', '
						SELECT id_challenge FROM {db_prefix}rivals_challenges
						WHERE id_ladder = {int:ladder} AND is_1v1 = 0
							AND ((challenger_id = {int:me} AND challengee_id = {int:opp})
								OR (challenger_id = {int:opp} AND challengee_id = {int:me}))',
						array('ladder' => $id_ladder, 'me' => $my_clan, 'opp' => $opponent_id)
					);
					if ($smcFunc['db_num_rows']($request) > 0)
						$context['rivals_errors'][] = $txt['rivals_error_already_challenged'];
					$smcFunc['db_free_result']($request);
				}

				$challenger_id = $my_clan;
				$challengee_id = $opponent_id;
			}
		}

		// Insert challenge
		if (empty($context['rivals_errors']))
		{
			$smcFunc['db_insert']('',
				'{db_prefix}rivals_challenges',
				array(
					'id_ladder' => 'int',
					'challenger_id' => 'int',
					'challengee_id' => 'int',
					'challenger_ip' => 'string',
					'is_unranked' => 'int',
					'is_1v1' => 'int',
					'details' => 'string',
					'created_at' => 'int',
				),
				array(
					$id_ladder,
					$challenger_id,
					$challengee_id,
					$user_info['ip'],
					$is_unranked,
					$is_1v1 ? 1 : 0,
					$details,
					time(),
				),
				array('id_challenge')
			);

			// Send alert to challengee
			if ($is_1v1)
			{
				rivals_send_alert('rivals_challenge_received', $challengee_id, $user_info['id'], array(
					'content_type' => 'rivals_challenge',
					'content_id' => $id_ladder,
					'extra' => array('ladder' => $context['rivals_available_ladders'][$id_ladder]['name']),
				));
			}
			else
			{
				// Alert all leaders of the challenged clan
				$request = $smcFunc['db_query']('', '
					SELECT id_member FROM {db_prefix}rivals_clan_members
					WHERE id_clan = {int:clan} AND role >= 1 AND is_pending = 0',
					array('clan' => $challengee_id)
				);
				while ($row = $smcFunc['db_fetch_assoc']($request))
				{
					rivals_send_alert('rivals_challenge_received', $row['id_member'], $user_info['id'], array(
						'content_type' => 'rivals_challenge',
						'content_id' => $id_ladder,
						'extra' => array('ladder' => $context['rivals_available_ladders'][$id_ladder]['name']),
					));
				}
				$smcFunc['db_free_result']($request);
			}

			redirectexit('action=rivals;sa=challenges;sent=1');
		}
	}

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=challenge',
		'name' => $txt['rivals_create_challenge'],
	);
}

/**
 * List challenges: incoming and outgoing.
 * Accept, decline, or view pending challenges.
 */
function RivalsChallengeList()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info, $sourcedir;

	if ($user_info['is_guest'])
		fatal_lang_error('rivals_error_not_logged_in', false);

	$context['sub_template'] = 'rivals_challenge_list';
	loadTemplate('RivalsMatch');
	$context['page_title'] = $txt['rivals_challenges'];

	$my_clan = rivals_get_member_clan($user_info['id']);
	$is_leader = ($my_clan > 0 && rivals_is_clan_leader($my_clan, $user_info['id']));

	$context['rivals_sent_success'] = isset($_GET['sent']);

	// Handle accept/decline
	if (isset($_GET['do']) && isset($_GET['cid']))
	{
		checkSession('get');

		$id_challenge = (int) $_GET['cid'];

		// Fetch challenge
		$request = $smcFunc['db_query']('', '
			SELECT c.*, l.name AS ladder_name, l.ranking_system, l.ladder_style, l.short_name,
				l.is_1v1, l.enable_mvp, l.enable_advstats
			FROM {db_prefix}rivals_challenges AS c
				INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = c.id_ladder)
			WHERE c.id_challenge = {int:id}',
			array('id' => $id_challenge)
		);
		$challenge = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (empty($challenge))
			fatal_lang_error('rivals_error_challenge_not_found', false);

		// Verify ownership
		$is_my_challenge = false;
		if ($challenge['is_1v1'] && $challenge['challengee_id'] == $user_info['id'])
			$is_my_challenge = true;
		elseif (!$challenge['is_1v1'] && $challenge['challengee_id'] == $my_clan && $is_leader)
			$is_my_challenge = true;

		if (!$is_my_challenge)
			fatal_lang_error('rivals_error_no_permission', false);

		if ($_GET['do'] === 'accept')
		{
			// Anti-spam: max 3 matches in 72h between same teams
			$request = $smcFunc['db_query']('', '
				SELECT COUNT(*) FROM {db_prefix}rivals_matches
				WHERE id_ladder = {int:ladder}
					AND ((challenger_id = {int:a} AND challengee_id = {int:b})
						OR (challenger_id = {int:b} AND challengee_id = {int:a}))
					AND created_at > {int:time_limit}',
				array(
					'ladder' => $challenge['id_ladder'],
					'a' => $challenge['challenger_id'],
					'b' => $challenge['challengee_id'],
					'time_limit' => time() - 259200,
				)
			);
			list ($recent_matches) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			if ($recent_matches >= 3)
				fatal_lang_error('rivals_error_too_many_matches', false);

			// Generate maps/modes for Decerto/CPC
			$round1_map = $round2_map = $round3_map = '';
			$round1_mode = $round2_mode = $round3_mode = '';

			if ($challenge['ladder_style'] == 1) // Decerto
			{
				// Get 3 random modes
				$request = $smcFunc['db_query']('', '
					SELECT id_mode, game_name FROM {db_prefix}rivals_game_modes
					WHERE mode_type = 0 AND is_active = 1
					ORDER BY RAND()
					LIMIT 3',
					array()
				);
				$modes = array();
				while ($row = $smcFunc['db_fetch_assoc']($request))
					$modes[] = $row;
				$smcFunc['db_free_result']($request);

				// Get random maps for each mode
				for ($i = 0; $i < 3; $i++)
				{
					if (isset($modes[$i]))
					{
						${'round' . ($i + 1) . '_mode'} = $modes[$i]['game_name'];

						$request = $smcFunc['db_query']('', '
							SELECT game_name FROM {db_prefix}rivals_game_modes
							WHERE mode_type = 1 AND parent_id = {int:parent} AND is_active = 1
							ORDER BY RAND()
							LIMIT 1',
							array('parent' => $modes[$i]['id_mode'])
						);
						$map = $smcFunc['db_fetch_assoc']($request);
						$smcFunc['db_free_result']($request);

						if (!empty($map))
							${'round' . ($i + 1) . '_map'} = $map['game_name'];
					}
				}
			}
			elseif ($challenge['ladder_style'] == 2) // CPC
			{
				// Get 3 random unique maps
				$request = $smcFunc['db_query']('', '
					SELECT game_name FROM {db_prefix}rivals_game_modes
					WHERE mode_type = 1 AND is_cpc = 1 AND is_active = 1
					ORDER BY RAND()
					LIMIT 3',
					array()
				);
				$i = 1;
				while ($row = $smcFunc['db_fetch_assoc']($request))
				{
					${'round' . $i . '_map'} = $row['game_name'];
					$i++;
				}
				$smcFunc['db_free_result']($request);
			}

			// Create match record
			$smcFunc['db_insert']('',
				'{db_prefix}rivals_matches',
				array(
					'id_ladder' => 'int',
					'match_type' => 'int',
					'challenger_id' => 'int',
					'challenger_ip' => 'string',
					'challengee_id' => 'int',
					'challengee_ip' => 'string',
					'is_unranked' => 'int',
					'status' => 'int',
					'details' => 'string',
					'round1_map' => 'string',
					'round1_mode' => 'string',
					'round2_map' => 'string',
					'round2_mode' => 'string',
					'round3_map' => 'string',
					'round3_mode' => 'string',
					'created_at' => 'int',
				),
				array(
					$challenge['id_ladder'],
					$challenge['is_1v1'] ? 1 : 0,
					$challenge['challenger_id'],
					$challenge['challenger_ip'],
					$challenge['challengee_id'],
					$user_info['ip'],
					$challenge['is_unranked'],
					0, // pending
					$challenge['details'],
					$round1_map,
					$round1_mode,
					$round2_map,
					$round2_mode,
					$round3_map,
					$round3_mode,
					time(),
				),
				array('id_match')
			);

			// If RTH, clear decline tracking
			if ($challenge['ranking_system'] == 2)
			{
				// Clear any RTH decline entries
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}rivals_standings
					WHERE id_ladder = {int:ladder} AND is_frozen = 1
						AND ' . ($challenge['is_1v1'] ? 'id_member = {int:entity}' : 'id_clan = {int:entity}'),
					array('ladder' => $challenge['id_ladder'], 'entity' => $challenge['challengee_id'])
				);
			}

			// Delete the challenge
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}rivals_challenges WHERE id_challenge = {int:id}',
				array('id' => $id_challenge)
			);

			// Send alert to challenger
			if ($challenge['is_1v1'])
			{
				rivals_send_alert('rivals_challenge_accepted', $challenge['challenger_id'], $user_info['id'], array(
					'content_type' => 'rivals_challenge',
					'content_id' => $challenge['id_ladder'],
				));
			}
			else
			{
				$request = $smcFunc['db_query']('', '
					SELECT id_member FROM {db_prefix}rivals_clan_members
					WHERE id_clan = {int:clan} AND role >= 1 AND is_pending = 0',
					array('clan' => $challenge['challenger_id'])
				);
				while ($row = $smcFunc['db_fetch_assoc']($request))
				{
					rivals_send_alert('rivals_challenge_accepted', $row['id_member'], $user_info['id'], array(
						'content_type' => 'rivals_challenge',
						'content_id' => $challenge['id_ladder'],
					));
				}
				$smcFunc['db_free_result']($request);
			}

			redirectexit('action=rivals;sa=challenges;accepted=1');
		}
		elseif ($_GET['do'] === 'decline')
		{
			// RTH decline penalty
			if ($challenge['ranking_system'] == 2)
			{
				rivals_process_rth_decline(
					$challenge['challengee_id'],
					$challenge['id_ladder'],
					$challenge['is_1v1']
				);
			}

			// Delete challenge
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}rivals_challenges WHERE id_challenge = {int:id}',
				array('id' => $id_challenge)
			);

			redirectexit('action=rivals;sa=challenges;declined=1');
		}
	}

	// Fetch incoming challenges
	$context['rivals_incoming'] = array();
	$context['rivals_outgoing'] = array();

	// Build WHERE conditions for incoming
	$where_incoming = array();
	$where_outgoing = array();
	$params = array();

	// 1v1 challenges
	$where_incoming[] = '(c.is_1v1 = 1 AND c.challengee_id = {int:member})';
	$where_outgoing[] = '(c.is_1v1 = 1 AND c.challenger_id = {int:member})';
	$params['member'] = $user_info['id'];

	// Clan challenges (if leader)
	if ($my_clan > 0 && $is_leader)
	{
		$where_incoming[] = '(c.is_1v1 = 0 AND c.challengee_id = {int:clan})';
		$where_outgoing[] = '(c.is_1v1 = 0 AND c.challenger_id = {int:clan})';
		$params['clan'] = $my_clan;
	}

	// Incoming
	$request = $smcFunc['db_query']('', '
		SELECT c.*, l.name AS ladder_name, l.is_1v1
		FROM {db_prefix}rivals_challenges AS c
			INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = c.id_ladder)
		WHERE (' . implode(' OR ', $where_incoming) . ')
		ORDER BY c.created_at DESC',
		$params
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Get challenger name
		if ($row['is_1v1'])
		{
			$req2 = $smcFunc['db_query']('', '
				SELECT real_name FROM {db_prefix}members WHERE id_member = {int:id}',
				array('id' => $row['challenger_id'])
			);
			$name_row = $smcFunc['db_fetch_assoc']($req2);
			$smcFunc['db_free_result']($req2);
			$row['challenger_name'] = !empty($name_row) ? $name_row['real_name'] : '???';
			$row['challenger_href'] = $scripturl . '?action=profile;u=' . $row['challenger_id'];
		}
		else
		{
			$req2 = $smcFunc['db_query']('', '
				SELECT name FROM {db_prefix}rivals_clans WHERE id_clan = {int:id}',
				array('id' => $row['challenger_id'])
			);
			$name_row = $smcFunc['db_fetch_assoc']($req2);
			$smcFunc['db_free_result']($req2);
			$row['challenger_name'] = !empty($name_row) ? $name_row['name'] : '???';
			$row['challenger_href'] = $scripturl . '?action=rivals;sa=clan;id=' . $row['challenger_id'];
		}

		$row['accept_href'] = $scripturl . '?action=rivals;sa=challenges;do=accept;cid=' . $row['id_challenge'] . ';' . $context['session_var'] . '=' . $context['session_id'];
		$row['decline_href'] = $scripturl . '?action=rivals;sa=challenges;do=decline;cid=' . $row['id_challenge'] . ';' . $context['session_var'] . '=' . $context['session_id'];
		$row['time'] = timeformat($row['created_at']);

		$context['rivals_incoming'][] = $row;
	}
	$smcFunc['db_free_result']($request);

	// Outgoing
	$request = $smcFunc['db_query']('', '
		SELECT c.*, l.name AS ladder_name, l.is_1v1
		FROM {db_prefix}rivals_challenges AS c
			INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = c.id_ladder)
		WHERE (' . implode(' OR ', $where_outgoing) . ')
		ORDER BY c.created_at DESC',
		$params
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Get challengee name
		if ($row['is_1v1'])
		{
			$req2 = $smcFunc['db_query']('', '
				SELECT real_name FROM {db_prefix}members WHERE id_member = {int:id}',
				array('id' => $row['challengee_id'])
			);
			$name_row = $smcFunc['db_fetch_assoc']($req2);
			$smcFunc['db_free_result']($req2);
			$row['challengee_name'] = !empty($name_row) ? $name_row['real_name'] : '???';
		}
		else
		{
			$req2 = $smcFunc['db_query']('', '
				SELECT name FROM {db_prefix}rivals_clans WHERE id_clan = {int:id}',
				array('id' => $row['challengee_id'])
			);
			$name_row = $smcFunc['db_fetch_assoc']($req2);
			$smcFunc['db_free_result']($req2);
			$row['challengee_name'] = !empty($name_row) ? $name_row['name'] : '???';
		}

		$row['time'] = timeformat($row['created_at']);
		$context['rivals_outgoing'][] = $row;
	}
	$smcFunc['db_free_result']($request);

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=challenges',
		'name' => $txt['rivals_challenges'],
	);
}

/**
 * View a match and report results.
 */
function RivalsMyMatch()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info, $sourcedir;

	if ($user_info['is_guest'])
		fatal_lang_error('rivals_error_not_logged_in', false);

	$context['sub_template'] = 'rivals_my_match';
	loadTemplate('RivalsMatch');

	$id_match = isset($_GET['match']) ? (int) $_GET['match'] : 0;
	if ($id_match <= 0)
		fatal_lang_error('rivals_match_not_found', false);

	// Fetch match with ladder info
	$request = $smcFunc['db_query']('', '
		SELECT m.*, l.name AS ladder_name, l.is_1v1, l.ranking_system, l.ladder_style,
			l.enable_mvp, l.enable_advstats, l.win_system
		FROM {db_prefix}rivals_matches AS m
			INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = m.id_ladder)
		WHERE m.id_match = {int:match}',
		array('match' => $id_match)
	);
	$match = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($match))
		fatal_lang_error('rivals_match_not_found', false);

	$context['page_title'] = $txt['rivals_match'] . ' #' . $id_match;

	$is_1v1 = !empty($match['is_1v1']);
	$my_clan = rivals_get_member_clan($user_info['id']);

	// Verify user is part of this match
	$is_challenger = false;
	$is_challengee = false;

	if ($is_1v1)
	{
		$is_challenger = ($match['challenger_id'] == $user_info['id']);
		$is_challengee = ($match['challengee_id'] == $user_info['id']);
	}
	else
	{
		$is_challenger = ($match['challenger_id'] == $my_clan);
		$is_challengee = ($match['challengee_id'] == $my_clan);
	}

	$context['rivals_match'] = $match;
	$context['rivals_is_challenger'] = $is_challenger;
	$context['rivals_is_challengee'] = $is_challengee;
	$context['rivals_can_report'] = ($match['status'] == 0 && $match['id_reporter'] == 0 && ($is_challenger || $is_challengee));
	$context['rivals_errors'] = array();

	// Resolve entity names
	if ($is_1v1)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member, real_name FROM {db_prefix}members
			WHERE id_member IN ({int:a}, {int:b})',
			array('a' => $match['challenger_id'], 'b' => $match['challengee_id'])
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ($row['id_member'] == $match['challenger_id'])
				$context['rivals_challenger_name'] = $row['real_name'];
			if ($row['id_member'] == $match['challengee_id'])
				$context['rivals_challengee_name'] = $row['real_name'];
		}
		$smcFunc['db_free_result']($request);
	}
	else
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_clan, name FROM {db_prefix}rivals_clans
			WHERE id_clan IN ({int:a}, {int:b})',
			array('a' => $match['challenger_id'], 'b' => $match['challengee_id'])
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ($row['id_clan'] == $match['challenger_id'])
				$context['rivals_challenger_name'] = $row['name'];
			if ($row['id_clan'] == $match['challengee_id'])
				$context['rivals_challengee_name'] = $row['name'];
		}
		$smcFunc['db_free_result']($request);
	}

	// Load clan members for MVP selection and advanced stats
	$context['rivals_team_members'] = array();
	if (!$is_1v1 && !empty($match['enable_mvp']))
	{
		// Get members of both clans
		foreach (array($match['challenger_id'], $match['challengee_id']) as $clan_id)
		{
			$request = $smcFunc['db_query']('', '
				SELECT cm.id_member, mem.real_name
				FROM {db_prefix}rivals_clan_members AS cm
					INNER JOIN {db_prefix}members AS mem ON (mem.id_member = cm.id_member)
				WHERE cm.id_clan = {int:clan} AND cm.is_pending = 0
				ORDER BY mem.real_name',
				array('clan' => $clan_id)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$context['rivals_team_members'][$row['id_member']] = $row['real_name'];
			$smcFunc['db_free_result']($request);
		}
	}

	// Handle report submission
	if (isset($_POST['submit_report']) && $context['rivals_can_report'])
	{
		checkSession();
		isAllowedTo('rivals_report');

		$winner_id = (int) $_POST['winner_id'];
		$challenger_score = (int) $_POST['challenger_score'];
		$challengee_score = (int) $_POST['challengee_score'];
		$challenger_team = isset($_POST['challenger_team']) ? $smcFunc['htmlspecialchars'](trim($_POST['challenger_team']), ENT_QUOTES) : '';
		$challengee_team = isset($_POST['challengee_team']) ? $smcFunc['htmlspecialchars'](trim($_POST['challengee_team']), ENT_QUOTES) : '';

		// Per-round scores
		$r1sc = isset($_POST['r1_challenger']) ? (int) $_POST['r1_challenger'] : 0;
		$r1se = isset($_POST['r1_challengee']) ? (int) $_POST['r1_challengee'] : 0;
		$r2sc = isset($_POST['r2_challenger']) ? (int) $_POST['r2_challenger'] : 0;
		$r2se = isset($_POST['r2_challengee']) ? (int) $_POST['r2_challengee'] : 0;
		$r3sc = isset($_POST['r3_challenger']) ? (int) $_POST['r3_challenger'] : 0;
		$r3se = isset($_POST['r3_challengee']) ? (int) $_POST['r3_challengee'] : 0;

		// MVPs
		$mvp1 = isset($_POST['mvp1']) ? (int) $_POST['mvp1'] : 0;
		$mvp2 = isset($_POST['mvp2']) ? (int) $_POST['mvp2'] : 0;
		$mvp3 = isset($_POST['mvp3']) ? (int) $_POST['mvp3'] : 0;

		// Draw check: only allowed in football
		$is_draw = ($winner_id == 9999999);
		if ($is_draw && $match['ladder_style'] != 3)
			$context['rivals_errors'][] = $txt['rivals_error_no_draws'];

		// Validate winner
		if (!$is_draw && $winner_id != $match['challenger_id'] && $winner_id != $match['challengee_id'])
			$context['rivals_errors'][] = $txt['rivals_error_invalid_winner'];

		if (empty($context['rivals_errors']))
		{
			// Update match with report
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}rivals_matches
				SET winner_id = {int:winner},
					challenger_score = {int:cs},
					challengee_score = {int:es},
					challenger_team = {string:ct},
					challengee_team = {string:et},
					round1_score_challenger = {int:r1c},
					round1_score_challengee = {int:r1e},
					round2_score_challenger = {int:r2c},
					round2_score_challengee = {int:r2e},
					round3_score_challenger = {int:r3c},
					round3_score_challengee = {int:r3e},
					mvp1 = {int:mvp1},
					mvp2 = {int:mvp2},
					mvp3 = {int:mvp3},
					id_reporter = {int:reporter},
					reported_at = {int:time}
				WHERE id_match = {int:match}',
				array(
					'winner' => $winner_id,
					'cs' => $challenger_score,
					'es' => $challengee_score,
					'ct' => $challenger_team,
					'et' => $challengee_team,
					'r1c' => $r1sc, 'r1e' => $r1se,
					'r2c' => $r2sc, 'r2e' => $r2se,
					'r3c' => $r3sc, 'r3e' => $r3se,
					'mvp1' => $mvp1, 'mvp2' => $mvp2, 'mvp3' => $mvp3,
					'reporter' => $is_challenger ? $match['challenger_id'] : $match['challengee_id'],
					'time' => time(),
					'match' => $id_match,
				)
			);

			// Save advanced stats if submitted
			if (!empty($match['enable_advstats']) && isset($_POST['stats']))
			{
				foreach ($_POST['stats'] as $member_id => $stats)
				{
					$member_id = (int) $member_id;
					if (empty($stats['played']))
						continue;

					$smcFunc['db_insert']('',
						'{db_prefix}rivals_match_stats',
						array(
							'id_match' => 'int',
							'id_ladder' => 'int',
							'id_member' => 'int',
							'kills' => 'int',
							'deaths' => 'int',
							'assists' => 'int',
							'goals_for' => 'int',
							'goals_against' => 'int',
						),
						array(
							$id_match,
							$match['id_ladder'],
							$member_id,
							isset($stats['kills']) ? (int) $stats['kills'] : 0,
							isset($stats['deaths']) ? (int) $stats['deaths'] : 0,
							isset($stats['assists']) ? (int) $stats['assists'] : 0,
							isset($stats['goals_for']) ? (int) $stats['goals_for'] : 0,
							isset($stats['goals_against']) ? (int) $stats['goals_against'] : 0,
						),
						array('id_stat')
					);
				}
			}

			// Send alert to opposing side
			$alert_to_entity = $is_challenger ? $match['challengee_id'] : $match['challenger_id'];
			if ($is_1v1)
			{
				rivals_send_alert('rivals_match_reported', $alert_to_entity, $user_info['id'], array(
					'content_type' => 'rivals_match',
					'content_id' => $id_match,
				));
			}
			else
			{
				$request = $smcFunc['db_query']('', '
					SELECT id_member FROM {db_prefix}rivals_clan_members
					WHERE id_clan = {int:clan} AND role >= 1 AND is_pending = 0',
					array('clan' => $alert_to_entity)
				);
				while ($row = $smcFunc['db_fetch_assoc']($request))
				{
					rivals_send_alert('rivals_match_reported', $row['id_member'], $user_info['id'], array(
						'content_type' => 'rivals_match',
						'content_id' => $id_match,
					));
				}
				$smcFunc['db_free_result']($request);
			}

			redirectexit('action=rivals;sa=mymatch;match=' . $id_match . ';reported=1');
		}
	}

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=mymatch;match=' . $id_match,
		'name' => $txt['rivals_match'] . ' #' . $id_match,
	);
}

/**
 * Confirm or dispute a reported match.
 */
function RivalsConfirmMatch()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info, $sourcedir, $modSettings;

	if ($user_info['is_guest'])
		fatal_lang_error('rivals_error_not_logged_in', false);

	$context['sub_template'] = 'rivals_confirm_match';
	loadTemplate('RivalsMatch');

	$id_match = isset($_GET['match']) ? (int) $_GET['match'] : 0;
	if ($id_match <= 0)
		fatal_lang_error('rivals_match_not_found', false);

	// Fetch match
	$request = $smcFunc['db_query']('', '
		SELECT m.*, l.name AS ladder_name, l.is_1v1, l.ranking_system, l.ladder_style,
			l.enable_mvp, l.enable_advstats
		FROM {db_prefix}rivals_matches AS m
			INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = m.id_ladder)
		WHERE m.id_match = {int:match}',
		array('match' => $id_match)
	);
	$match = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($match))
		fatal_lang_error('rivals_match_not_found', false);

	// Must have been reported but not yet confirmed
	if ($match['id_reporter'] == 0 || $match['status'] != 0)
		fatal_lang_error('rivals_error_match_not_pending', false);

	$is_1v1 = !empty($match['is_1v1']);
	$my_clan = rivals_get_member_clan($user_info['id']);

	// Determine if current user is the confirmer (non-reporter)
	$my_entity = $is_1v1 ? $user_info['id'] : $my_clan;
	$reporter_entity = $match['id_reporter'];

	// Confirmer must be the side that did NOT report
	$can_confirm = false;
	if ($is_1v1)
	{
		if ($match['challenger_id'] == $reporter_entity && $match['challengee_id'] == $user_info['id'])
			$can_confirm = true;
		elseif ($match['challengee_id'] == $reporter_entity && $match['challenger_id'] == $user_info['id'])
			$can_confirm = true;
	}
	else
	{
		if ($match['challenger_id'] == $reporter_entity && $match['challengee_id'] == $my_clan)
			$can_confirm = (rivals_is_clan_leader($my_clan, $user_info['id']));
		elseif ($match['challengee_id'] == $reporter_entity && $match['challenger_id'] == $my_clan)
			$can_confirm = (rivals_is_clan_leader($my_clan, $user_info['id']));
	}

	if (!$can_confirm)
		fatal_lang_error('rivals_error_no_permission', false);

	$context['rivals_match'] = $match;
	$context['page_title'] = $txt['rivals_confirm_match'] . ' #' . $id_match;

	// Resolve names
	if ($is_1v1)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member, real_name FROM {db_prefix}members
			WHERE id_member IN ({int:a}, {int:b})',
			array('a' => $match['challenger_id'], 'b' => $match['challengee_id'])
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ($row['id_member'] == $match['challenger_id'])
				$context['rivals_challenger_name'] = $row['real_name'];
			if ($row['id_member'] == $match['challengee_id'])
				$context['rivals_challengee_name'] = $row['real_name'];
		}
		$smcFunc['db_free_result']($request);
	}
	else
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_clan, name FROM {db_prefix}rivals_clans
			WHERE id_clan IN ({int:a}, {int:b})',
			array('a' => $match['challenger_id'], 'b' => $match['challengee_id'])
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ($row['id_clan'] == $match['challenger_id'])
				$context['rivals_challenger_name'] = $row['name'];
			if ($row['id_clan'] == $match['challengee_id'])
				$context['rivals_challengee_name'] = $row['name'];
		}
		$smcFunc['db_free_result']($request);
	}

	// Winner name
	$context['rivals_winner_name'] = '';
	if ($match['winner_id'] == 9999999)
		$context['rivals_winner_name'] = $txt['rivals_draw'];
	elseif ($match['winner_id'] == $match['challenger_id'])
		$context['rivals_winner_name'] = $context['rivals_challenger_name'];
	elseif ($match['winner_id'] == $match['challengee_id'])
		$context['rivals_winner_name'] = $context['rivals_challengee_name'];

	// Handle confirm/dispute action
	if (isset($_POST['do_confirm']))
	{
		checkSession();

		$feedback = isset($_POST['feedback']) ? max(1, min(5, (int) $_POST['feedback'])) : 5;

		// Determine which feedback field to set
		if ($my_entity == $match['challengee_id'])
			$feedback_field = 'challengee_feedback';
		else
			$feedback_field = 'challenger_feedback';

		// Update match as confirmed
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}rivals_matches
			SET id_confirmer = {int:confirmer},
				status = 1,
				' . $feedback_field . ' = {int:feedback},
				completed_at = {int:time}
			WHERE id_match = {int:match}',
			array(
				'confirmer' => $my_entity,
				'feedback' => $feedback,
				'time' => time(),
				'match' => $id_match,
			)
		);

		// Now update standings (only if ranked)
		if (empty($match['is_unranked']))
		{
			$is_draw = ($match['winner_id'] == 9999999);
			$winner = $match['winner_id'];
			$loser = ($winner == $match['challenger_id']) ? $match['challengee_id'] : $match['challenger_id'];

			if ($is_draw)
			{
				// Draw - update both sides
				rivals_update_standings($match['id_ladder'], $match['challenger_id'], $match['challengee_id'], true);
			}
			else
			{
				rivals_update_standings($match['id_ladder'], $winner, $loser, false);
			}

			// Award MVPs
			$mvps = array_filter(array($match['mvp1'], $match['mvp2'], $match['mvp3']));
			foreach ($mvps as $mvp_id)
			{
				if ($mvp_id <= 0)
					continue;

				// Update user_ladder_stats
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}rivals_user_ladder_stats
					SET mvp_count = mvp_count + 1
					WHERE id_ladder = {int:ladder} AND id_member = {int:member}',
					array('ladder' => $match['id_ladder'], 'member' => $mvp_id)
				);

				// Update member profile
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}members
					SET rivals_mvp_count = rivals_mvp_count + 1
					WHERE id_member = {int:member}',
					array('member' => $mvp_id)
				);

				// Update clan member stats
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}rivals_clan_members
					SET mvp_count = mvp_count + 1
					WHERE id_member = {int:member} AND is_pending = 0',
					array('member' => $mvp_id)
				);
			}

			// Process advanced stats
			if (!empty($match['enable_advstats']))
			{
				$request = $smcFunc['db_query']('', '
					SELECT * FROM {db_prefix}rivals_match_stats
					WHERE id_match = {int:match}',
					array('match' => $id_match)
				);
				while ($stat = $smcFunc['db_fetch_assoc']($request))
				{
					// Update user_ladder_stats
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}rivals_user_ladder_stats
						SET kills = kills + {int:k}, deaths = deaths + {int:d},
							assists = assists + {int:a}, goals_for = goals_for + {int:gf},
							goals_against = goals_against + {int:ga},
							matches_played = matches_played + 1
						WHERE id_ladder = {int:ladder} AND id_member = {int:member}',
						array(
							'k' => $stat['kills'], 'd' => $stat['deaths'], 'a' => $stat['assists'],
							'gf' => $stat['goals_for'], 'ga' => $stat['goals_against'],
							'ladder' => $match['id_ladder'], 'member' => $stat['id_member'],
						)
					);

					// Upsert: create row if not exists
					$smcFunc['db_insert']('ignore',
						'{db_prefix}rivals_user_ladder_stats',
						array(
							'id_ladder' => 'int', 'id_member' => 'int',
							'kills' => 'int', 'deaths' => 'int', 'assists' => 'int',
							'goals_for' => 'int', 'goals_against' => 'int',
							'matches_played' => 'int',
						),
						array($match['id_ladder'], $stat['id_member'], 0, 0, 0, 0, 0, 0),
						array('id_ladder', 'id_member')
					);

					// Update clan member stats
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}rivals_clan_members
						SET kills = kills + {int:k}, deaths = deaths + {int:d},
							assists = assists + {int:a}, goals_for = goals_for + {int:gf},
							goals_against = goals_against + {int:ga}
						WHERE id_member = {int:member} AND is_pending = 0',
						array(
							'k' => $stat['kills'], 'd' => $stat['deaths'], 'a' => $stat['assists'],
							'gf' => $stat['goals_for'], 'ga' => $stat['goals_against'],
							'member' => $stat['id_member'],
						)
					);

					// Recalculate EXP
					rivals_calculate_exp($stat['id_member']);
				}
				$smcFunc['db_free_result']($request);
			}

			// Update clan reputation from feedback
			if ($feedback > 0)
			{
				$rep_clan = ($my_entity == $match['challengee_id']) ? $match['challenger_id'] : $match['challengee_id'];
				if (!$is_1v1)
				{
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}rivals_clans
						SET rep_value = rep_value + {int:rep}, rep_time = rep_time + 1
						WHERE id_clan = {int:clan}',
						array('rep' => $feedback, 'clan' => $rep_clan)
					);
				}
			}
		}

		// Send confirmation alert to reporter
		if ($is_1v1)
		{
			$alert_to = ($match['challenger_id'] == $reporter_entity) ? $match['challenger_id'] : $match['challengee_id'];
			// Actually send to the reporter side
			$alert_to = $reporter_entity == $match['challenger_id'] ? $match['challenger_id'] : $match['challengee_id'];
			rivals_send_alert('rivals_match_confirmed', $alert_to, $user_info['id'], array(
				'content_type' => 'rivals_match',
				'content_id' => $id_match,
			));
		}
		else
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member FROM {db_prefix}rivals_clan_members
				WHERE id_clan = {int:clan} AND role >= 1 AND is_pending = 0',
				array('clan' => $reporter_entity)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				rivals_send_alert('rivals_match_confirmed', $row['id_member'], $user_info['id'], array(
					'content_type' => 'rivals_match',
					'content_id' => $id_match,
				));
			}
			$smcFunc['db_free_result']($request);
		}

		redirectexit('action=rivals;sa=matches');
	}
	elseif (isset($_POST['do_dispute']))
	{
		checkSession();

		// Mark as disputed
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}rivals_matches
			SET status = 2, is_contested = 1
			WHERE id_match = {int:match}',
			array('match' => $id_match)
		);

		// Alert
		if ($is_1v1)
		{
			rivals_send_alert('rivals_match_disputed', $reporter_entity, $user_info['id'], array(
				'content_type' => 'rivals_match',
				'content_id' => $id_match,
			));
		}
		else
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member FROM {db_prefix}rivals_clan_members
				WHERE id_clan = {int:clan} AND role >= 1 AND is_pending = 0',
				array('clan' => $reporter_entity)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				rivals_send_alert('rivals_match_disputed', $row['id_member'], $user_info['id'], array(
					'content_type' => 'rivals_match',
					'content_id' => $id_match,
				));
			}
			$smcFunc['db_free_result']($request);
		}

		redirectexit('action=rivals;sa=matches');
	}

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=confirmmatch;match=' . $id_match,
		'name' => $txt['rivals_confirm_match'],
	);
}

/**
 * MVP selection page (standalone, for editing MVPs post-report).
 */
function RivalsMatchMVP()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info;

	if ($user_info['is_guest'])
		fatal_lang_error('rivals_error_not_logged_in', false);

	$context['sub_template'] = 'rivals_match_mvp';
	loadTemplate('RivalsMatch');

	$id_match = isset($_GET['match']) ? (int) $_GET['match'] : 0;

	$request = $smcFunc['db_query']('', '
		SELECT m.*, l.name AS ladder_name, l.is_1v1, l.enable_mvp
		FROM {db_prefix}rivals_matches AS m
			INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = m.id_ladder)
		WHERE m.id_match = {int:match}',
		array('match' => $id_match)
	);
	$match = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($match) || empty($match['enable_mvp']))
		fatal_lang_error('rivals_match_not_found', false);

	$context['rivals_match'] = $match;
	$context['page_title'] = $txt['rivals_select_mvp'];

	// Get team members for both sides
	$context['rivals_team_members'] = array();
	if (!$match['is_1v1'])
	{
		foreach (array($match['challenger_id'], $match['challengee_id']) as $clan_id)
		{
			$request = $smcFunc['db_query']('', '
				SELECT cm.id_member, mem.real_name
				FROM {db_prefix}rivals_clan_members AS cm
					INNER JOIN {db_prefix}members AS mem ON (mem.id_member = cm.id_member)
				WHERE cm.id_clan = {int:clan} AND cm.is_pending = 0
				ORDER BY mem.real_name',
				array('clan' => $clan_id)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$context['rivals_team_members'][$row['id_member']] = $row['real_name'];
			$smcFunc['db_free_result']($request);
		}
	}

	// Handle MVP save
	if (isset($_POST['save_mvp']))
	{
		checkSession();

		$mvp1 = isset($_POST['mvp1']) ? (int) $_POST['mvp1'] : 0;
		$mvp2 = isset($_POST['mvp2']) ? (int) $_POST['mvp2'] : 0;
		$mvp3 = isset($_POST['mvp3']) ? (int) $_POST['mvp3'] : 0;

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}rivals_matches
			SET mvp1 = {int:mvp1}, mvp2 = {int:mvp2}, mvp3 = {int:mvp3}
			WHERE id_match = {int:match}',
			array('mvp1' => $mvp1, 'mvp2' => $mvp2, 'mvp3' => $mvp3, 'match' => $id_match)
		);

		redirectexit('action=rivals;sa=mymatch;match=' . $id_match);
	}
}

/**
 * Match finder queue.
 */
function RivalsMatchFinder()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info;

	if ($user_info['is_guest'])
		fatal_lang_error('rivals_error_not_logged_in', false);

	$context['sub_template'] = 'rivals_match_finder';
	loadTemplate('RivalsMatch');
	$context['page_title'] = $txt['rivals_match_finder'];

	$my_clan = rivals_get_member_clan($user_info['id']);

	// Clean up expired entries (older than 24 hours)
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}rivals_matchfinder
		WHERE created_at < {int:expire}',
		array('expire' => time() - 86400)
	);

	// Handle join/leave queue
	if (isset($_POST['join_queue']))
	{
		checkSession();

		$id_ladder = (int) $_POST['id_ladder'];
		$wait_time = max(15, min(1440, (int) $_POST['wait_time']));
		$is_unranked = !empty($_POST['is_unranked']) ? 1 : 0;

		$smcFunc['db_insert']('replace',
			'{db_prefix}rivals_matchfinder',
			array(
				'id_clan' => 'int',
				'id_ladder' => 'int',
				'wait_time' => 'int',
				'is_unranked' => 'int',
				'created_at' => 'int',
			),
			array(
				$my_clan > 0 ? $my_clan : $user_info['id'],
				$id_ladder,
				$wait_time,
				$is_unranked,
				time(),
			),
			array('id_entry')
		);

		redirectexit('action=rivals;sa=matchfinder');
	}

	if (isset($_GET['leave']) && isset($_GET['entry']))
	{
		checkSession('get');

		$id_entry = (int) $_GET['entry'];
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}rivals_matchfinder
			WHERE id_entry = {int:entry}
				AND id_clan IN ({int:clan}, {int:member})',
			array('entry' => $id_entry, 'clan' => $my_clan, 'member' => $user_info['id'])
		);

		redirectexit('action=rivals;sa=matchfinder');
	}

	// Get available ladders for join form
	$context['rivals_available_ladders'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT DISTINCT s.id_ladder, l.name, l.is_1v1
		FROM {db_prefix}rivals_standings AS s
			INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = s.id_ladder AND l.is_locked = 0)
		WHERE s.id_clan = {int:clan} OR s.id_member = {int:member}',
		array('clan' => $my_clan, 'member' => $user_info['id'])
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['rivals_available_ladders'][$row['id_ladder']] = array(
			'id_ladder' => $row['id_ladder'],
			'name' => $row['name'] . ($row['is_1v1'] ? ' (1v1)' : ''),
		);
	}
	$smcFunc['db_free_result']($request);

	// Fetch current queue
	$context['rivals_queue'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT mf.*, l.name AS ladder_name, l.is_1v1
		FROM {db_prefix}rivals_matchfinder AS mf
			INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = mf.id_ladder)
		ORDER BY mf.created_at DESC',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// Calculate expiration
		$expire_time = $row['created_at'] + ($row['wait_time'] * 60);

		// Get entity name
		if ($row['is_1v1'] || empty($row['id_clan']))
		{
			// This is a user entry
			$req2 = $smcFunc['db_query']('', '
				SELECT real_name FROM {db_prefix}members WHERE id_member = {int:id}',
				array('id' => $row['id_clan'])
			);
			$name_row = $smcFunc['db_fetch_assoc']($req2);
			$smcFunc['db_free_result']($req2);
			$row['entity_name'] = !empty($name_row) ? $name_row['real_name'] : '???';
		}
		else
		{
			$req2 = $smcFunc['db_query']('', '
				SELECT name FROM {db_prefix}rivals_clans WHERE id_clan = {int:id}',
				array('id' => $row['id_clan'])
			);
			$name_row = $smcFunc['db_fetch_assoc']($req2);
			$smcFunc['db_free_result']($req2);
			$row['entity_name'] = !empty($name_row) ? $name_row['name'] : '???';
		}

		$row['expire_time'] = $expire_time;
		$row['is_mine'] = ($row['id_clan'] == $my_clan || $row['id_clan'] == $user_info['id']);
		$row['is_expired'] = (time() > $expire_time);

		if (!$row['is_expired'])
			$context['rivals_queue'][] = $row;
	}
	$smcFunc['db_free_result']($request);

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=matchfinder',
		'name' => $txt['rivals_match_finder'],
	);
}

/**
 * Match chat view.
 */
function RivalsMatchChat()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info;

	$context['sub_template'] = 'rivals_match_chat';
	loadTemplate('RivalsMatch');

	$id_match = isset($_GET['match']) ? (int) $_GET['match'] : 0;

	$request = $smcFunc['db_query']('', '
		SELECT m.id_match, m.id_ladder, l.name AS ladder_name
		FROM {db_prefix}rivals_matches AS m
			INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = m.id_ladder)
		WHERE m.id_match = {int:match}',
		array('match' => $id_match)
	);
	$match = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($match))
		fatal_lang_error('rivals_match_not_found', false);

	$context['rivals_match'] = $match;
	$context['page_title'] = $txt['rivals_match_chat'] . ' - ' . $match['ladder_name'];
}

/**
 * Process RTH decline penalty.
 * First decline: create tracking entry. Second decline: apply chicken penalty.
 */
function rivals_process_rth_decline($entity_id, $id_ladder, $is_1v1)
{
	global $smcFunc;

	$id_field = $is_1v1 ? 'id_member' : 'id_clan';

	// Check for existing decline tracking (abuse rivals_challenge_rights as tracker)
	// We use a simple approach: check if they have a pending RTH penalty marker
	// The phpBB version uses a dedicated RTH_CHECK_TABLE; we'll use the chicken_count directly.
	// On each decline in RTH: increment chicken. At 3 chickens, apply penalty.

	if ($is_1v1)
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET rivals_chicken_count = rivals_chicken_count + 1
			WHERE id_member = {int:member}',
			array('member' => $entity_id)
		);

		// Check if reached penalty threshold (every 3 chickens)
		$request = $smcFunc['db_query']('', '
			SELECT rivals_chicken_count FROM {db_prefix}members WHERE id_member = {int:member}',
			array('member' => $entity_id)
		);
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($row) && $row['rivals_chicken_count'] % 3 == 0)
		{
			// Apply score penalty: reduce to 25%, minimum 50
			rivals_apply_chicken_penalty_entity($entity_id, $id_ladder, $is_1v1);
		}
	}
	else
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}rivals_clans
			SET chicken_count = chicken_count + 1
			WHERE id_clan = {int:clan}',
			array('clan' => $entity_id)
		);

		$request = $smcFunc['db_query']('', '
			SELECT chicken_count FROM {db_prefix}rivals_clans WHERE id_clan = {int:clan}',
			array('clan' => $entity_id)
		);
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($row) && $row['chicken_count'] % 3 == 0)
		{
			rivals_apply_chicken_penalty_entity($entity_id, $id_ladder, $is_1v1);
		}
	}
}

/**
 * Apply chicken penalty: reduce score to 25%, minimum 50.
 */
function rivals_apply_chicken_penalty_entity($entity_id, $id_ladder, $is_1v1)
{
	global $smcFunc;

	$field = $is_1v1 ? 'id_member' : 'id_clan';

	$request = $smcFunc['db_query']('', '
		SELECT id_standing, score FROM {db_prefix}rivals_standings
		WHERE id_ladder = {int:ladder} AND ' . $field . ' = {int:entity}',
		array('ladder' => $id_ladder, 'entity' => $entity_id)
	);
	$standing = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($standing))
	{
		$new_score = max(50, (int) ceil($standing['score'] / 4));

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}rivals_standings
			SET score = {int:score}
			WHERE id_standing = {int:id}',
			array('score' => $new_score, 'id' => $standing['id_standing'])
		);
	}
}
?>