<?php
/**
 * SMF Rivals - Ladder Display, Standings, Membership
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Standings page for a ladder.
 * Shows ranked teams/users with scores, status icons, and rank changes.
 * Supports ELO, SWAP, and RTH ranking systems.
 */
function RivalsStandings()
{
	global $context, $smcFunc, $txt, $scripturl, $user_info, $settings;

	$id_ladder = isset($_GET['ladder']) ? (int) $_GET['ladder'] : 0;
	if ($id_ladder <= 0)
		redirectexit('action=rivals;sa=platforms');

	$context['sub_template'] = 'rivals_standings';
	loadTemplate('Rivals');

	// Fetch ladder data
	$request = $smcFunc['db_query']('', '
		SELECT l.*, p.name AS platform_name, p.id_platform
		FROM {db_prefix}rivals_ladders AS l
			LEFT JOIN {db_prefix}rivals_platforms AS p ON (p.id_platform = l.id_platform)
		WHERE l.id_ladder = {int:ladder}',
		array('ladder' => $id_ladder)
	);
	$ladder = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($ladder))
		fatal_lang_error('rivals_ladder_not_found', false);

	$context['rivals_ladder'] = $ladder;
	$context['page_title'] = $ladder['name'] . ' - ' . $txt['rivals_standings'];

	$is_1v1 = !empty($ladder['is_1v1']);
	$id_field = $is_1v1 ? 'id_member' : 'id_clan';

	// Determine sort order based on ranking system
	// 0=ELO, 1=SWAP, 2=RTH
	switch ($ladder['ranking_system'])
	{
		case 1: // SWAP
			$order_by = 's.current_rank ASC';
			break;
		case 0: // ELO
		case 2: // RTH
		default:
			$order_by = 's.score DESC';
			break;
	}

	// RTH winner check: if any team has score >= 1000, ladder is won
	$context['rivals_rth_winner'] = null;
	if ($ladder['ranking_system'] == 2)
	{
		$request = $smcFunc['db_query']('', '
			SELECT s.*, ' . ($is_1v1 ? 'mem.real_name AS entity_name' : 'c.name AS entity_name') . '
			FROM {db_prefix}rivals_standings AS s
				' . ($is_1v1
				? 'LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = s.id_member)'
				: 'LEFT JOIN {db_prefix}rivals_clans AS c ON (c.id_clan = s.id_clan)') . '
			WHERE s.id_ladder = {int:ladder}
			ORDER BY s.score DESC
			LIMIT 1',
			array('ladder' => $id_ladder)
		);
		$top = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($top) && $top['score'] >= 1000)
			$context['rivals_rth_winner'] = $top;
	}

	// Pagination
	$per_page = 30;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}rivals_standings AS s
		WHERE s.id_ladder = {int:ladder}',
		array('ladder' => $id_ladder)
	);
	list ($total) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=rivals;sa=standings;ladder=' . $id_ladder, $start, $total, $per_page, true);

	// For football mode, pre-calculate goal extremes
	$context['rivals_gol_max'] = 0;
	$context['rivals_gol_min'] = 0;
	if ($ladder['ladder_style'] == 3)
	{
		$request = $smcFunc['db_query']('', '
			SELECT MAX(goals_for - goals_against) AS gol_max, MIN(goals_for - goals_against) AS gol_min
			FROM {db_prefix}rivals_standings
			WHERE id_ladder = {int:ladder}',
			array('ladder' => $id_ladder)
		);
		$gol = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
		$context['rivals_gol_max'] = (int) $gol['gol_max'];
		$context['rivals_gol_min'] = (int) $gol['gol_min'];
	}

	// Pre-calculate crown (best ratio) and tomb (worst ratio)
	$context['rivals_crown_id'] = 0;
	$context['rivals_tomb_id'] = 0;

	$request = $smcFunc['db_query']('', '
		SELECT ' . $id_field . ', ratio, wins, losses
		FROM {db_prefix}rivals_standings
		WHERE id_ladder = {int:ladder}
		ORDER BY CAST(ratio AS DECIMAL(10,4)) DESC',
		array('ladder' => $id_ladder)
	);
	$best_ratio = -1;
	$worst_ratio = 999999;
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$r = (float) $row['ratio'];
		if ($r > $best_ratio && $row['wins'] >= 8)
		{
			$best_ratio = $r;
			$context['rivals_crown_id'] = $row[$id_field];
		}
		if ($r < $worst_ratio && $row['losses'] >= 8)
		{
			$worst_ratio = $r;
			$context['rivals_tomb_id'] = $row[$id_field];
		}
	}
	$smcFunc['db_free_result']($request);

	// Main standings query
	$context['rivals_standings_data'] = array();

	if ($is_1v1)
	{
		$request = $smcFunc['db_query']('', '
			SELECT s.*, mem.real_name AS entity_name, mem.rivals_gamer_name AS gamer_name
			FROM {db_prefix}rivals_standings AS s
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = s.id_member)
			WHERE s.id_ladder = {int:ladder}
			ORDER BY ' . $order_by . '
			LIMIT {int:start}, {int:limit}',
			array('ladder' => $id_ladder, 'start' => $start, 'limit' => $per_page)
		);
	}
	else
	{
		$request = $smcFunc['db_query']('', '
			SELECT s.*, c.name AS entity_name, c.logo, c.logo_ext, c.is_closed
			FROM {db_prefix}rivals_standings AS s
				LEFT JOIN {db_prefix}rivals_clans AS c ON (c.id_clan = s.id_clan)
			WHERE s.id_ladder = {int:ladder}
				AND (c.is_closed = 0 OR c.is_closed IS NULL)
			ORDER BY ' . $order_by . '
			LIMIT {int:start}, {int:limit}',
			array('ladder' => $id_ladder, 'start' => $start, 'limit' => $per_page)
		);
	}

	$position = $start + 1;
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$entity_id = $is_1v1 ? $row['id_member'] : $row['id_clan'];

		// Rank movement icon
		$rank_change = 'same';
		if ($row['last_rank'] > 0 && $row['current_rank'] > 0)
		{
			if ($row['current_rank'] < $row['last_rank'])
				$rank_change = 'up';
			elseif ($row['current_rank'] > $row['last_rank'])
				$rank_change = 'down';
		}

		// Streak analysis for hot/cold icon
		$streak = $row['streak'];
		$streak_count = 0;
		if (!empty($streak))
		{
			$last_char = substr($streak, -1);
			for ($i = strlen($streak) - 1; $i >= 0; $i--)
			{
				if ($streak[$i] === $last_char)
					$streak_count++;
				else
					break;
			}
			if ($last_char === 'L')
				$streak_count = -$streak_count;
		}

		$hot_cold = 'none';
		if ($streak_count >= 4)
			$hot_cold = 'hot';
		elseif ($streak_count <= -4)
			$hot_cold = 'cold';

		// Trophy icons
		$is_crown = ($entity_id == $context['rivals_crown_id']);
		$is_tomb = ($entity_id == $context['rivals_tomb_id']);

		// Football icons
		$is_golden_shoe = false;
		$is_soap = false;
		if ($ladder['ladder_style'] == 3)
		{
			$goal_diff = $row['goals_for'] - $row['goals_against'];
			if ($goal_diff == $context['rivals_gol_max'] && $row['goals_for'] > 10)
				$is_golden_shoe = true;
			if ($goal_diff == $context['rivals_gol_min'] && $row['goals_against'] > 10)
				$is_soap = true;
		}

		// RTH chicken check
		$rth_chicken = false;
		if ($ladder['ranking_system'] == 2)
		{
			if ($is_1v1)
			{
				// Check user chicken count
				$chk = $smcFunc['db_query']('', '
					SELECT rivals_chicken_count FROM {db_prefix}members WHERE id_member = {int:member}',
					array('member' => $row['id_member'])
				);
				$chk_row = $smcFunc['db_fetch_assoc']($chk);
				$smcFunc['db_free_result']($chk);
				if (!empty($chk_row) && $chk_row['rivals_chicken_count'] >= 3)
					$rth_chicken = true;
			}
			else
			{
				$chk = $smcFunc['db_query']('', '
					SELECT chicken_count FROM {db_prefix}rivals_clans WHERE id_clan = {int:clan}',
					array('clan' => $row['id_clan'])
				);
				$chk_row = $smcFunc['db_fetch_assoc']($chk);
				$smcFunc['db_free_result']($chk);
				if (!empty($chk_row) && $chk_row['chicken_count'] >= 3)
					$rth_chicken = true;
			}
		}

		// Activity status
		$activity = rivals_get_activity_status($entity_id, $id_ladder, $is_1v1);

		// Entity link
		if ($is_1v1)
			$href = $scripturl . '?action=profile;u=' . $row['id_member'];
		else
			$href = $scripturl . '?action=rivals;sa=clan;id=' . $row['id_clan'];

		$entry = array(
			'position' => $position++,
			'entity_id' => $entity_id,
			'entity_name' => $row['entity_name'],
			'href' => $href,
			'score' => $row['score'],
			'last_score' => $row['last_score'],
			'wins' => $row['wins'],
			'losses' => $row['losses'],
			'draws' => $row['draws'],
			'current_rank' => $row['current_rank'],
			'streak' => $row['streak'],
			'streak_display' => $streak_count,
			'goals_for' => $row['goals_for'],
			'goals_against' => $row['goals_against'],
			'ratio' => $row['ratio'],
			'is_frozen' => !empty($row['is_frozen']),
			'rank_change' => $rank_change,
			'hot_cold' => $hot_cold,
			'is_crown' => $is_crown,
			'is_tomb' => $is_tomb,
			'is_golden_shoe' => $is_golden_shoe,
			'is_soap' => $is_soap,
			'rth_chicken' => $rth_chicken,
			'activity' => $activity,
		);

		// Clan-specific fields
		if (!$is_1v1)
		{
			$entry['logo'] = $row['logo'] ?? '';
			$entry['logo_ext'] = $row['logo_ext'] ?? '';
		}
		else
		{
			$entry['gamer_name'] = $row['gamer_name'] ?? '';
		}

		$context['rivals_standings_data'][] = $entry;
	}
	$smcFunc['db_free_result']($request);

	// Membership check: is current user/clan in this ladder?
	$context['rivals_in_ladder'] = false;
	$context['rivals_can_join'] = false;
	$context['rivals_can_leave'] = false;

	if (!$user_info['is_guest'])
	{
		if ($is_1v1)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_standing FROM {db_prefix}rivals_standings
				WHERE id_ladder = {int:ladder} AND id_member = {int:member}',
				array('ladder' => $id_ladder, 'member' => $user_info['id'])
			);
			$context['rivals_in_ladder'] = $smcFunc['db_num_rows']($request) > 0;
			$smcFunc['db_free_result']($request);
		}
		else
		{
			$my_clan = rivals_get_member_clan($user_info['id']);
			if ($my_clan > 0)
			{
				$request = $smcFunc['db_query']('', '
					SELECT id_standing FROM {db_prefix}rivals_standings
					WHERE id_ladder = {int:ladder} AND id_clan = {int:clan}',
					array('ladder' => $id_ladder, 'clan' => $my_clan)
				);
				$context['rivals_in_ladder'] = $smcFunc['db_num_rows']($request) > 0;
				$smcFunc['db_free_result']($request);
			}
		}

		if ($context['rivals_in_ladder'] && empty($ladder['is_locked']))
			$context['rivals_can_leave'] = true;
		elseif (!$context['rivals_in_ladder'] && empty($ladder['is_locked']))
			$context['rivals_can_join'] = true;
	}

	// Handle join/leave actions
	if (isset($_GET['do']) && !$user_info['is_guest'])
	{
		checkSession('get');

		if ($_GET['do'] === 'join' && $context['rivals_can_join'])
		{
			rivals_join_ladder($id_ladder, $ladder, $is_1v1);
			redirectexit('action=rivals;sa=standings;ladder=' . $id_ladder);
		}
		elseif ($_GET['do'] === 'leave' && $context['rivals_can_leave'])
		{
			rivals_leave_ladder($id_ladder, $ladder, $is_1v1);
			redirectexit('action=rivals;sa=standings;ladder=' . $id_ladder);
		}
		elseif ($_GET['do'] === 'freeze' && $context['rivals_in_ladder'] && empty($ladder['is_locked']))
		{
			rivals_toggle_freeze($id_ladder, $is_1v1);
			redirectexit('action=rivals;sa=standings;ladder=' . $id_ladder);
		}
	}

	// Advanced stats
	$context['rivals_advstats'] = array();
	if (!empty($ladder['enable_advstats']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT uls.*, mem.real_name, mem.rivals_gamer_name
			FROM {db_prefix}rivals_user_ladder_stats AS uls
				INNER JOIN {db_prefix}members AS mem ON (mem.id_member = uls.id_member)
			WHERE uls.id_ladder = {int:ladder}
			ORDER BY CAST(uls.ranking AS DECIMAL(10,4)) DESC
			LIMIT 30',
			array('ladder' => $id_ladder)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$kd_ratio = $row['deaths'] > 0 ? round($row['kills'] / $row['deaths'], 3) : (float) $row['kills'];
			$context['rivals_advstats'][] = array(
				'id_member' => $row['id_member'],
				'name' => $row['real_name'],
				'gamer_name' => $row['rivals_gamer_name'],
				'ranking' => $row['ranking'],
				'kills' => $row['kills'],
				'deaths' => $row['deaths'],
				'assists' => $row['assists'],
				'kd_ratio' => number_format($kd_ratio, 3),
				'goals_for' => $row['goals_for'],
				'goals_against' => $row['goals_against'],
				'mvp_count' => $row['mvp_count'],
				'matches_played' => $row['matches_played'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			);
		}
		$smcFunc['db_free_result']($request);
	}

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=platforms',
		'name' => $txt['rivals_platforms'],
	);
	if (!empty($ladder['platform_name']))
	{
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=rivals;sa=ladders;platform=' . $ladder['id_platform'],
			'name' => $ladder['platform_name'],
		);
	}
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=standings;ladder=' . $id_ladder,
		'name' => $ladder['name'],
	);
}

/**
 * Join a ladder.
 */
function rivals_join_ladder($id_ladder, $ladder, $is_1v1)
{
	global $smcFunc, $user_info, $modSettings;

	// License level check
	if (!empty($ladder['limit_level']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT rivals_ladder_level FROM {db_prefix}members WHERE id_member = {int:member}',
			array('member' => $user_info['id'])
		);
		$member = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if ($ladder['limit_level'] == 1 && (int) $member['rivals_ladder_level'] < 1)
			fatal_lang_error('rivals_error_insufficient_level', false);
		if ($ladder['limit_level'] == 2 && (int) $member['rivals_ladder_level'] < 2)
			fatal_lang_error('rivals_error_insufficient_level', false);
	}

	if ($is_1v1)
	{
		// Check duplicate
		$request = $smcFunc['db_query']('', '
			SELECT id_standing FROM {db_prefix}rivals_standings
			WHERE id_ladder = {int:ladder} AND id_member = {int:member}',
			array('ladder' => $id_ladder, 'member' => $user_info['id'])
		);
		if ($smcFunc['db_num_rows']($request) > 0)
		{
			$smcFunc['db_free_result']($request);
			return;
		}
		$smcFunc['db_free_result']($request);

		// Get next rank
		$request = $smcFunc['db_query']('', '
			SELECT COALESCE(MAX(current_rank), 0) AS max_rank
			FROM {db_prefix}rivals_standings
			WHERE id_ladder = {int:ladder}',
			array('ladder' => $id_ladder)
		);
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
		$new_rank = (int) $row['max_rank'] + 1;

		// Starting score based on system
		$start_score = ($ladder['ranking_system'] == 2) ? 50 : 1500;

		$smcFunc['db_insert']('',
			'{db_prefix}rivals_standings',
			array(
				'id_ladder' => 'int', 'id_member' => 'int', 'score' => 'int',
				'current_rank' => 'int', 'best_rank' => 'int', 'worst_rank' => 'int',
			),
			array($id_ladder, $user_info['id'], $start_score, $new_rank, $new_rank, $new_rank),
			array('id_standing')
		);

		// Add challenge rights
		$smcFunc['db_insert']('ignore',
			'{db_prefix}rivals_challenge_rights',
			array('id_clan' => 'int', 'id_ladder' => 'int', 'is_1v1' => 'int'),
			array($user_info['id'], $id_ladder, 1),
			array('id_clan', 'id_ladder')
		);
	}
	else
	{
		$clan_id = rivals_get_member_clan($user_info['id']);
		if ($clan_id <= 0)
			return;

		if (!rivals_is_clan_leader($clan_id, $user_info['id']))
			return;

		// Check duplicate
		$request = $smcFunc['db_query']('', '
			SELECT id_standing FROM {db_prefix}rivals_standings
			WHERE id_ladder = {int:ladder} AND id_clan = {int:clan}',
			array('ladder' => $id_ladder, 'clan' => $clan_id)
		);
		if ($smcFunc['db_num_rows']($request) > 0)
		{
			$smcFunc['db_free_result']($request);
			return;
		}
		$smcFunc['db_free_result']($request);

		// Get next rank
		$request = $smcFunc['db_query']('', '
			SELECT COALESCE(MAX(current_rank), 0) AS max_rank
			FROM {db_prefix}rivals_standings
			WHERE id_ladder = {int:ladder}',
			array('ladder' => $id_ladder)
		);
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
		$new_rank = (int) $row['max_rank'] + 1;

		$start_score = ($ladder['ranking_system'] == 2) ? 50 : 1500;

		$smcFunc['db_insert']('',
			'{db_prefix}rivals_standings',
			array(
				'id_ladder' => 'int', 'id_clan' => 'int', 'score' => 'int',
				'current_rank' => 'int', 'best_rank' => 'int', 'worst_rank' => 'int',
			),
			array($id_ladder, $clan_id, $start_score, $new_rank, $new_rank, $new_rank),
			array('id_standing')
		);

		// Add challenge rights
		$smcFunc['db_insert']('ignore',
			'{db_prefix}rivals_challenge_rights',
			array('id_clan' => 'int', 'id_ladder' => 'int', 'is_1v1' => 'int'),
			array($clan_id, $id_ladder, 0),
			array('id_clan', 'id_ladder')
		);
	}
}

/**
 * Leave a ladder.
 */
function rivals_leave_ladder($id_ladder, $ladder, $is_1v1)
{
	global $smcFunc, $user_info;

	if ($is_1v1)
	{
		// Check no active matches
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*) FROM {db_prefix}rivals_matches
			WHERE id_ladder = {int:ladder} AND match_type = 1 AND status = 0
				AND (challenger_id = {int:member} OR challengee_id = {int:member})',
			array('ladder' => $id_ladder, 'member' => $user_info['id'])
		);
		list ($active) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if ($active > 0)
			fatal_lang_error('rivals_error_active_matches', false);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}rivals_standings
			WHERE id_ladder = {int:ladder} AND id_member = {int:member}',
			array('ladder' => $id_ladder, 'member' => $user_info['id'])
		);

		// Remove pending challenges
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}rivals_challenges
			WHERE id_ladder = {int:ladder} AND is_1v1 = 1
				AND (challenger_id = {int:member} OR challengee_id = {int:member})',
			array('ladder' => $id_ladder, 'member' => $user_info['id'])
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}rivals_challenge_rights
			WHERE id_ladder = {int:ladder} AND id_clan = {int:member} AND is_1v1 = 1',
			array('ladder' => $id_ladder, 'member' => $user_info['id'])
		);
	}
	else
	{
		$clan_id = rivals_get_member_clan($user_info['id']);
		if ($clan_id <= 0 || !rivals_is_clan_leader($clan_id, $user_info['id']))
			return;

		// Check no active matches
		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*) FROM {db_prefix}rivals_matches
			WHERE id_ladder = {int:ladder} AND match_type = 0 AND status = 0
				AND (challenger_id = {int:clan} OR challengee_id = {int:clan})',
			array('ladder' => $id_ladder, 'clan' => $clan_id)
		);
		list ($active) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		if ($active > 0)
			fatal_lang_error('rivals_error_active_matches', false);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}rivals_standings
			WHERE id_ladder = {int:ladder} AND id_clan = {int:clan}',
			array('ladder' => $id_ladder, 'clan' => $clan_id)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}rivals_challenges
			WHERE id_ladder = {int:ladder} AND is_1v1 = 0
				AND (challenger_id = {int:clan} OR challengee_id = {int:clan})',
			array('ladder' => $id_ladder, 'clan' => $clan_id)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}rivals_challenge_rights
			WHERE id_ladder = {int:ladder} AND id_clan = {int:clan} AND is_1v1 = 0',
			array('ladder' => $id_ladder, 'clan' => $clan_id)
		);
	}
}

/**
 * Toggle freeze/hibernate status on a ladder.
 */
function rivals_toggle_freeze($id_ladder, $is_1v1)
{
	global $smcFunc, $user_info;

	if ($is_1v1)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_standing, is_frozen FROM {db_prefix}rivals_standings
			WHERE id_ladder = {int:ladder} AND id_member = {int:member}',
			array('ladder' => $id_ladder, 'member' => $user_info['id'])
		);
	}
	else
	{
		$clan_id = rivals_get_member_clan($user_info['id']);
		if ($clan_id <= 0)
			return;

		$request = $smcFunc['db_query']('', '
			SELECT id_standing, is_frozen FROM {db_prefix}rivals_standings
			WHERE id_ladder = {int:ladder} AND id_clan = {int:clan}',
			array('ladder' => $id_ladder, 'clan' => $clan_id)
		);
	}

	$standing = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($standing))
		return;

	$new_frozen = $standing['is_frozen'] ? 0 : 1;
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}rivals_standings
		SET is_frozen = {int:frozen}, frozen_time = {int:time}
		WHERE id_standing = {int:id}',
		array(
			'frozen' => $new_frozen,
			'time' => $new_frozen ? time() : 0,
			'id' => $standing['id_standing'],
		)
	);
}

/**
 * Ladder rules display.
 */
function RivalsRules()
{
	global $context, $smcFunc, $txt, $scripturl;

	$id_ladder = isset($_GET['ladder']) ? (int) $_GET['ladder'] : 0;
	if ($id_ladder <= 0)
		redirectexit('action=rivals;sa=platforms');

	$context['sub_template'] = 'rivals_rules';
	loadTemplate('Rivals');

	// Fetch ladder info
	$request = $smcFunc['db_query']('', '
		SELECT l.name, l.id_platform, p.name AS platform_name
		FROM {db_prefix}rivals_ladders AS l
			LEFT JOIN {db_prefix}rivals_platforms AS p ON (p.id_platform = l.id_platform)
		WHERE l.id_ladder = {int:ladder}',
		array('ladder' => $id_ladder)
	);
	$ladder = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($ladder))
		fatal_lang_error('rivals_ladder_not_found', false);

	$context['rivals_ladder_name'] = $ladder['name'];
	$context['page_title'] = $ladder['name'] . ' - ' . $txt['rivals_rules'];

	// Fetch rules
	$request = $smcFunc['db_query']('', '
		SELECT * FROM {db_prefix}rivals_ladder_rules
		WHERE id_ladder = {int:ladder}',
		array('ladder' => $id_ladder)
	);
	$context['rivals_rules'] = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=platforms',
		'name' => $txt['rivals_platforms'],
	);
	if (!empty($ladder['platform_name']))
	{
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=rivals;sa=ladders;platform=' . $ladder['id_platform'],
			'name' => $ladder['platform_name'],
		);
	}
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=standings;ladder=' . $id_ladder,
		'name' => $ladder['name'],
	);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=rules;ladder=' . $id_ladder,
		'name' => $txt['rivals_rules'],
	);
}
?>