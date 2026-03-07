<?php
/**
 * SMF Rivals - Core Library Functions
 * ELO, rankings, brackets, experience, and shared utilities.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Calculate ELO rating change.
 *
 * @param int $score1 Current score of participant 1
 * @param int $score2 Current score of participant 2
 * @param bool $player1_wins True if participant 1 won
 * @return int New score for participant 1
 */
function rivals_calculate_elo($score1, $score2, $player1_wins)
{
	$outcome = $player1_wins ? 1 : 0;
	$difference = $score1 - $score2;
	$exponent = -$difference / 400;
	$expected_outcome = 1 / (1 + pow(10, $exponent));

	$k = rivals_determine_k_factor($score1);
	$new_score = round($score1 + $k * ($outcome - $expected_outcome));

	return $new_score;
}

/**
 * Determine K-factor based on current ELO score.
 * K=30 for developing players (<2000), K=20 for mid-tier, K=10 for established.
 *
 * @param int $score Current ELO score
 * @return int K-factor value
 */
function rivals_determine_k_factor($score)
{
	if ($score < 2000)
		return 30;
	elseif ($score < 2400)
		return 20;
	else
		return 10;
}

/**
 * Update ranks using SWAP system.
 * Winner takes loser's rank if it's better (lower number).
 *
 * @param int $winner_rank Current rank of the winner
 * @param int $loser_rank Current rank of the loser
 * @return array Array with 'winner_rank' and 'loser_rank' after swap
 */
function rivals_swap_ranks($winner_rank, $loser_rank)
{
	// Only swap if loser has a better (lower) rank
	if ($loser_rank < $winner_rank)
	{
		return array(
			'winner_rank' => $loser_rank,
			'loser_rank' => $winner_rank,
		);
	}

	return array(
		'winner_rank' => $winner_rank,
		'loser_rank' => $loser_rank,
	);
}

/**
 * Process match result and update standings.
 * Handles ELO, SWAP, and RTH ranking systems.
 *
 * @param int $id_ladder Ladder ID
 * @param int $winner_id Winning clan/member ID
 * @param int $loser_id Losing clan/member ID
 * @param bool $is_draw Whether the match was a draw
 */
function rivals_update_standings($id_ladder, $winner_id, $loser_id, $is_draw = false)
{
	global $smcFunc;

	// Get ladder info
	$request = $smcFunc['db_query']('', '
		SELECT ranking_system, is_1v1
		FROM {db_prefix}rivals_ladders
		WHERE id_ladder = {int:ladder}',
		array('ladder' => $id_ladder)
	);
	$ladder = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($ladder))
		return;

	$id_field = $ladder['is_1v1'] ? 'id_member' : 'id_clan';

	// Get current standings for both
	$request = $smcFunc['db_query']('', '
		SELECT id_standing, ' . $id_field . ', score, current_rank, best_rank, worst_rank,
			wins, losses, draws, streak, goals_for, goals_against
		FROM {db_prefix}rivals_standings
		WHERE id_ladder = {int:ladder}
			AND ' . $id_field . ' IN ({int:winner}, {int:loser})',
		array(
			'ladder' => $id_ladder,
			'winner' => $winner_id,
			'loser' => $loser_id,
		)
	);

	$standings = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$standings[$row[$id_field]] = $row;
	$smcFunc['db_free_result']($request);

	if (empty($standings[$winner_id]) || empty($standings[$loser_id]))
		return;

	$winner = $standings[$winner_id];
	$loser = $standings[$loser_id];

	if ($is_draw)
	{
		// Draw: both get ELO update with 0.5 expected
		$new_winner_score = rivals_calculate_elo_draw($winner['score'], $loser['score']);
		$new_loser_score = rivals_calculate_elo_draw($loser['score'], $winner['score']);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}rivals_standings
			SET score = {int:score}, draws = draws + 1,
				streak = {string:streak}
			WHERE id_standing = {int:id}',
			array(
				'score' => $new_winner_score,
				'streak' => rivals_update_streak($winner['streak'], 'D'),
				'id' => $winner['id_standing'],
			)
		);
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}rivals_standings
			SET score = {int:score}, draws = draws + 1,
				streak = {string:streak}
			WHERE id_standing = {int:id}',
			array(
				'score' => $new_loser_score,
				'streak' => rivals_update_streak($loser['streak'], 'D'),
				'id' => $loser['id_standing'],
			)
		);
		return;
	}

	// Ranking system: 0=ELO, 1=SWAP, 2=RTH
	switch ($ladder['ranking_system'])
	{
		case 1: // SWAP
			$swap = rivals_swap_ranks($winner['current_rank'], $loser['current_rank']);

			$winner_best = ($swap['winner_rank'] > 0 && ($winner['best_rank'] == 0 || $swap['winner_rank'] < $winner['best_rank']))
				? $swap['winner_rank'] : $winner['best_rank'];
			$loser_worst = ($swap['loser_rank'] > 0 && ($loser['worst_rank'] == 0 || $swap['loser_rank'] > $loser['worst_rank']))
				? $swap['loser_rank'] : $loser['worst_rank'];

			$smcFunc['db_query']('', '
				UPDATE {db_prefix}rivals_standings
				SET wins = wins + 1, last_rank = current_rank,
					current_rank = {int:rank}, best_rank = {int:best},
					streak = {string:streak}
				WHERE id_standing = {int:id}',
				array(
					'rank' => $swap['winner_rank'],
					'best' => $winner_best,
					'streak' => rivals_update_streak($winner['streak'], 'W'),
					'id' => $winner['id_standing'],
				)
			);
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}rivals_standings
				SET losses = losses + 1, last_rank = current_rank,
					current_rank = {int:rank}, worst_rank = {int:worst},
					streak = {string:streak}
				WHERE id_standing = {int:id}',
				array(
					'rank' => $swap['loser_rank'],
					'worst' => $loser_worst,
					'streak' => rivals_update_streak($loser['streak'], 'L'),
					'id' => $loser['id_standing'],
				)
			);
			break;

		case 2: // RTH (same as SWAP but with chicken penalty)
			$swap = rivals_swap_ranks($winner['current_rank'], $loser['current_rank']);

			$smcFunc['db_query']('', '
				UPDATE {db_prefix}rivals_standings
				SET wins = wins + 1, last_rank = current_rank,
					current_rank = {int:rank},
					streak = {string:streak}
				WHERE id_standing = {int:id}',
				array(
					'rank' => $swap['winner_rank'],
					'streak' => rivals_update_streak($winner['streak'], 'W'),
					'id' => $winner['id_standing'],
				)
			);
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}rivals_standings
				SET losses = losses + 1, last_rank = current_rank,
					current_rank = {int:rank},
					streak = {string:streak}
				WHERE id_standing = {int:id}',
				array(
					'rank' => $swap['loser_rank'],
					'streak' => rivals_update_streak($loser['streak'], 'L'),
					'id' => $loser['id_standing'],
				)
			);
			break;

		default: // ELO (0)
			$new_winner_score = rivals_calculate_elo($winner['score'], $loser['score'], true);
			$new_loser_score = rivals_calculate_elo($loser['score'], $winner['score'], false);

			$smcFunc['db_query']('', '
				UPDATE {db_prefix}rivals_standings
				SET score = {int:score}, last_score = {int:last_score},
					wins = wins + 1,
					streak = {string:streak}
				WHERE id_standing = {int:id}',
				array(
					'score' => $new_winner_score,
					'last_score' => $winner['score'],
					'streak' => rivals_update_streak($winner['streak'], 'W'),
					'id' => $winner['id_standing'],
				)
			);
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}rivals_standings
				SET score = {int:score}, last_score = {int:last_score},
					losses = losses + 1,
					streak = {string:streak}
				WHERE id_standing = {int:id}',
				array(
					'score' => $new_loser_score,
					'last_score' => $loser['score'],
					'streak' => rivals_update_streak($loser['streak'], 'L'),
					'id' => $loser['id_standing'],
				)
			);
			break;
	}
}

/**
 * Calculate ELO for a draw (0.5 expected outcome).
 *
 * @param int $score1 Score of player
 * @param int $score2 Score of opponent
 * @return int Updated score
 */
function rivals_calculate_elo_draw($score1, $score2)
{
	$difference = $score1 - $score2;
	$exponent = -$difference / 400;
	$expected_outcome = 1 / (1 + pow(10, $exponent));

	$k = rivals_determine_k_factor($score1);
	return round($score1 + $k * (0.5 - $expected_outcome));
}

/**
 * Update win/loss streak string.
 *
 * @param string $streak Current streak string (e.g., "WWLWW")
 * @param string $result 'W', 'L', or 'D'
 * @return string Updated streak (max 20 chars)
 */
function rivals_update_streak($streak, $result)
{
	$streak .= $result;

	// Keep last 20 results
	if (strlen($streak) > 20)
		$streak = substr($streak, -20);

	return $streak;
}

/**
 * Generate ordinal suffix for a number (1st, 2nd, 3rd, etc.)
 *
 * @param int $number The number
 * @return string Number with ordinal suffix in superscript
 */
function rivals_ordinal($number)
{
	$test_c = abs($number) % 10;
	$ext = ((abs($number) % 100 < 21 && abs($number) % 100 > 4) ? 'th'
		: (($test_c < 4) ? ($test_c < 3) ? ($test_c < 2) ? ($test_c < 1)
		? 'th' : 'st' : 'nd' : 'rd' : 'th'));

	return $number . '<sup>' . $ext . '</sup>';
}

/**
 * Get activity status for a clan/user on a ladder.
 * Returns status key: 'hot', 'warm', 'cool', 'cold', 'inactive', 'frozen'
 *
 * @param int $id Clan or member ID
 * @param int $id_ladder Ladder ID
 * @param bool $is_1v1 Whether this is a 1v1 ladder
 * @return string Status key
 */
function rivals_get_activity_status($id, $id_ladder, $is_1v1 = false)
{
	global $smcFunc;

	$id_field = $is_1v1 ? 'id_member' : 'id_clan';

	// Check if frozen
	$request = $smcFunc['db_query']('', '
		SELECT is_frozen
		FROM {db_prefix}rivals_standings
		WHERE id_ladder = {int:ladder}
			AND ' . $id_field . ' = {int:id}',
		array('ladder' => $id_ladder, 'id' => $id)
	);
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($row['is_frozen']))
		return 'frozen';

	// Get last completed match time
	if ($is_1v1)
	{
		$request = $smcFunc['db_query']('', '
			SELECT MAX(completed_at) AS last_match
			FROM {db_prefix}rivals_matches
			WHERE id_ladder = {int:ladder}
				AND match_type = 1
				AND status = 1
				AND (challenger_id = {int:id} OR challengee_id = {int:id})',
			array('ladder' => $id_ladder, 'id' => $id)
		);
	}
	else
	{
		$request = $smcFunc['db_query']('', '
			SELECT MAX(completed_at) AS last_match
			FROM {db_prefix}rivals_matches
			WHERE id_ladder = {int:ladder}
				AND match_type = 0
				AND status = 1
				AND (challenger_id = {int:id} OR challengee_id = {int:id})',
			array('ladder' => $id_ladder, 'id' => $id)
		);
	}
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$last_match = !empty($row['last_match']) ? (int) $row['last_match'] : 0;

	if ($last_match == 0)
		return 'inactive';

	$days_since = (time() - $last_match) / 86400;

	// Thresholds: 4 days, 8 days, 14 days, 30 days
	if ($days_since <= 4)
		return 'hot';
	elseif ($days_since <= 8)
		return 'warm';
	elseif ($days_since <= 14)
		return 'cool';
	elseif ($days_since <= 30)
		return 'cold';
	else
		return 'inactive';
}

/**
 * Calculate total experience for a member.
 * Formula: (positive + assists + MVPs×20 + 1v1bonus + pwner×35) - (negative + chicken×50)
 *
 * @param int $id_member Member ID
 * @return int Total experience value
 */
function rivals_calculate_exp($id_member)
{
	global $smcFunc;

	// Get member data
	$request = $smcFunc['db_query']('', '
		SELECT rivals_exp, rivals_chicken_count, rivals_mvp_count, rivals_pwner_count
		FROM {db_prefix}members
		WHERE id_member = {int:member}',
		array('member' => $id_member)
	);
	$member = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($member))
		return 0;

	$oneone_bonus = (int) ceil((int) $member['rivals_exp'] * 30);
	$chicken_count = (int) $member['rivals_chicken_count'];
	$mvp_count = (int) $member['rivals_mvp_count'];
	$pwner_count = (int) $member['rivals_pwner_count'];

	// Aggregate clan stats
	$request = $smcFunc['db_query']('', '
		SELECT SUM(kills) AS total_kills, SUM(deaths) AS total_deaths,
			SUM(assists) AS total_assists, SUM(goals_for) AS total_gf,
			SUM(goals_against) AS total_ga
		FROM {db_prefix}rivals_clan_members
		WHERE id_member = {int:member}
			AND is_pending = 0',
		array('member' => $id_member)
	);
	$clan_stats = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$kills = (int) $clan_stats['total_kills'];
	$deaths = (int) $clan_stats['total_deaths'];
	$assists = (int) $clan_stats['total_assists'];
	$goals_for = (int) $clan_stats['total_gf'];
	$goals_against = (int) $clan_stats['total_ga'];

	// Tournament stats
	$request = $smcFunc['db_query']('', '
		SELECT SUM(kills) AS total_kills, SUM(deaths) AS total_deaths,
			SUM(assists) AS total_assists
		FROM {db_prefix}rivals_tournament_player_stats
		WHERE id_member = {int:member}
			AND (team1_confirmed > 0 OR team2_confirmed > 0)',
		array('member' => $id_member)
	);
	$tourn_stats = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$t_kills = (int) $tourn_stats['total_kills'];
	$t_deaths = (int) $tourn_stats['total_deaths'];
	$t_assists = (int) $tourn_stats['total_assists'];

	// Calculate
	$positive = ($kills + $goals_for + $t_kills) * 3;
	$negative = ($deaths + $goals_against + $t_deaths) * 3;
	$mvp_bonus = $mvp_count * 20;
	$chicken_penalty = $chicken_count * 50;
	$pwner_bonus = $pwner_count * 35;

	$total = ($positive + $assists + $t_assists + $mvp_bonus + $oneone_bonus + $pwner_bonus) - ($chicken_penalty + $negative);

	return $total;
}

/**
 * Calculate user ranking value from match stats.
 *
 * @param int $matches_played Total matches played
 * @param int $positive Positive points (wins, kills, etc.)
 * @param int $negative Negative points (losses, deaths, etc.)
 * @return float Ranking value rounded to 4 decimals
 */
function rivals_get_user_rank($matches_played, $positive, $negative)
{
	$ratio = ($negative == 0) ? $positive : $positive / $negative;
	$ratio = ($ratio == 0) ? 1 : $ratio;

	$rank1 = $ratio * sqrt($matches_played / $ratio);
	$rank2 = (1 / 10) * ($positive / (100 / (5 * $ratio)));
	$newrank = round($rank1 + $rank2 + ($ratio / 5), 4);

	return $newrank;
}

/**
 * Generate bracket positions for a tournament.
 * Creates properly seeded positions for power-of-2 bracket sizes with BYE handling.
 *
 * @param int $size Bracket size (must be power of 2)
 * @param bool $double_elim Whether to include losers bracket
 * @return array Bracket structure with rounds and positions
 */
function rivals_generate_brackets($size, $double_elim = false)
{
	// Ensure power of 2
	$rounds = (int) ceil(log($size, 2));
	$bracket_size = pow(2, $rounds);

	$brackets = array(
		'winners' => array(),
		'total_rounds' => $rounds,
		'bracket_size' => $bracket_size,
	);

	// Generate seeded positions for first round
	$seeds = rivals_generate_seeds($bracket_size);

	// Build winners bracket rounds
	$matches_in_round = $bracket_size / 2;
	for ($round = 1; $round <= $rounds; $round++)
	{
		$brackets['winners'][$round] = array();
		for ($match = 1; $match <= $matches_in_round; $match++)
		{
			$brackets['winners'][$round][$match] = array(
				'team1' => 0,
				'team2' => 0,
				'winner' => 0,
				'match_uid' => 'W-' . $round . '-' . $match,
			);
		}
		$matches_in_round = $matches_in_round / 2;
	}

	// Assign seeds to first round
	for ($i = 0; $i < count($seeds); $i += 2)
	{
		$match_num = ($i / 2) + 1;
		$brackets['winners'][1][$match_num]['team1'] = $seeds[$i];
		$brackets['winners'][1][$match_num]['team2'] = $seeds[$i + 1];
	}

	// Double elimination: add losers bracket
	if ($double_elim)
	{
		$brackets['losers'] = array();
		$loser_rounds = ($rounds - 1) * 2;

		$matches_in_round = $bracket_size / 4;
		for ($round = 1; $round <= $loser_rounds; $round++)
		{
			$brackets['losers'][$round] = array();
			for ($match = 1; $match <= $matches_in_round; $match++)
			{
				$brackets['losers'][$round][$match] = array(
					'team1' => 0,
					'team2' => 0,
					'winner' => 0,
					'match_uid' => 'L-' . $round . '-' . $match,
				);
			}

			// Losers bracket: every other round halves the matches
			if ($round % 2 == 0)
				$matches_in_round = max(1, $matches_in_round / 2);
		}

		// Grand final
		$brackets['grand_final'] = array(
			'team1' => 0,
			'team2' => 0,
			'winner' => 0,
			'match_uid' => 'GF-1',
		);
	}

	return $brackets;
}

/**
 * Generate proper seeded positions for tournament bracket.
 * Ensures top seeds are maximally separated.
 *
 * @param int $size Number of positions (power of 2)
 * @return array Array of seed positions
 */
function rivals_generate_seeds($size)
{
	if ($size <= 1)
		return array(1);

	$seeds = array(1, 2);

	while (count($seeds) < $size)
	{
		$next_seeds = array();
		$sum = count($seeds) + 1;
		foreach ($seeds as $seed)
		{
			$next_seeds[] = $seed;
			$next_seeds[] = $sum - $seed;
		}
		$seeds = $next_seeds;
	}

	return $seeds;
}

/**
 * Generate round-robin schedule for a set of teams.
 * Uses the rotation algorithm to ensure each team plays every other.
 *
 * @param array $teams Array of team IDs
 * @return array Array of rounds, each containing match pairings
 */
function rivals_round_robin_schedule($teams)
{
	$count = count($teams);

	// Need even number of teams; add BYE if odd
	if ($count % 2 != 0)
	{
		$teams[] = 0; // BYE
		$count++;
	}

	$rounds = $count - 1;
	$matches_per_round = $count / 2;
	$schedule = array();

	// Fix first team, rotate the rest
	$fixed = $teams[0];
	$rotating = array_slice($teams, 1);

	for ($round = 0; $round < $rounds; $round++)
	{
		$schedule[$round + 1] = array();

		// First match: fixed team vs current first of rotating
		$schedule[$round + 1][] = array($fixed, $rotating[0]);

		// Remaining matches: pair from ends toward middle
		for ($i = 1; $i < $matches_per_round; $i++)
		{
			$schedule[$round + 1][] = array(
				$rotating[$i],
				$rotating[$count - 1 - $i]
			);
		}

		// Rotate: move last to front
		$last = array_pop($rotating);
		array_unshift($rotating, $last);
	}

	return $schedule;
}

/**
 * Apply RTH chicken penalty.
 * After 3 declines, apply -25% score penalty (minimum score 50).
 *
 * @param int $id_standing Standing ID to penalize
 */
function rivals_apply_chicken_penalty($id_standing)
{
	global $smcFunc, $modSettings;

	$frost_cost = !empty($modSettings['rivals_frost_cost']) ? (int) $modSettings['rivals_frost_cost'] : 25;

	$request = $smcFunc['db_query']('', '
		SELECT id_standing, score, id_clan, id_member, id_ladder
		FROM {db_prefix}rivals_standings
		WHERE id_standing = {int:id}',
		array('id' => $id_standing)
	);
	$standing = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($standing))
		return;

	// Calculate penalty: lose frost_cost% of score, minimum 50
	$penalty = (int) floor($standing['score'] * ($frost_cost / 100));
	$new_score = max(50, $standing['score'] - $penalty);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}rivals_standings
		SET score = {int:score}
		WHERE id_standing = {int:id}',
		array(
			'score' => $new_score,
			'id' => $id_standing,
		)
	);

	// Update clan/member chicken count
	if (!empty($standing['id_clan']))
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}rivals_clans
			SET chicken_count = chicken_count + 1
			WHERE id_clan = {int:clan}',
			array('clan' => $standing['id_clan'])
		);
	}
	elseif (!empty($standing['id_member']))
	{
		updateMemberData($standing['id_member'], array('rivals_chicken_count' => '+'));
	}
}

/**
 * Get roster total EXP value.
 *
 * @param int $id_roster Roster ID
 * @return int Total EXP of all roster members
 */
function rivals_get_roster_exp($id_roster)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT members
		FROM {db_prefix}rivals_rosters
		WHERE id_roster = {int:roster}',
		array('roster' => $id_roster)
	);
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($row) || empty($row['members']))
		return 0;

	$member_ids = array_filter(array_map('intval', explode('|', $row['members'])));
	if (empty($member_ids))
		return 0;

	$request = $smcFunc['db_query']('', '
		SELECT SUM(CAST(rivals_ladder_value AS SIGNED)) AS total_exp
		FROM {db_prefix}members
		WHERE id_member IN ({array_int:members})',
		array('members' => $member_ids)
	);
	$result = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	return (int) $result['total_exp'];
}

/**
 * Send a Rivals alert to one or more users.
 *
 * @param string $type Alert action type (e.g., 'challenge_received')
 * @param array|int $to Member ID(s) to notify
 * @param int $from Sender member ID
 * @param array $data Extra data for the alert
 */
function rivals_send_alert($type, $to, $from, $data = array())
{
	global $smcFunc, $sourcedir;

	if (!is_array($to))
		$to = array($to);

	$to = array_filter(array_map('intval', $to));
	if (empty($to))
		return;

	// Remove sender from recipients
	$to = array_diff($to, array((int) $from));
	if (empty($to))
		return;

	// Get sender name
	$request = $smcFunc['db_query']('', '
		SELECT real_name
		FROM {db_prefix}members
		WHERE id_member = {int:member}',
		array('member' => $from)
	);
	$sender = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$extra = array_merge($data, array(
		'sender_name' => !empty($sender['real_name']) ? $sender['real_name'] : '',
	));

	$alert_rows = array();
	foreach ($to as $member_id)
	{
		$alert_rows[] = array(
			'alert_time' => time(),
			'id_member' => $member_id,
			'id_member_started' => $from,
			'content_type' => 'rivals',
			'content_id' => !empty($data['content_id']) ? (int) $data['content_id'] : 0,
			'content_action' => $type,
			'is_read' => 0,
			'extra' => json_encode($extra),
		);
	}

	if (!empty($alert_rows))
	{
		$smcFunc['db_insert']('',
			'{db_prefix}user_alerts',
			array(
				'alert_time' => 'int',
				'id_member' => 'int',
				'id_member_started' => 'int',
				'content_type' => 'string',
				'content_id' => 'int',
				'content_action' => 'string',
				'is_read' => 'int',
				'extra' => 'string',
			),
			$alert_rows,
			array()
		);

		// Increment alert count for each recipient
		updateMemberData($to, array('alerts' => '+'));
	}
}

/**
 * Get the current clan for a member (their active session clan).
 *
 * @param int $id_member Member ID
 * @return int Clan ID or 0
 */
function rivals_get_member_clan($id_member)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT rivals_clan_session
		FROM {db_prefix}members
		WHERE id_member = {int:member}',
		array('member' => $id_member)
	);
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$clan_id = !empty($row['rivals_clan_session']) ? (int) $row['rivals_clan_session'] : 0;

	// Verify they're actually in this clan
	if ($clan_id > 0)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_clan
			FROM {db_prefix}rivals_clan_members
			WHERE id_clan = {int:clan}
				AND id_member = {int:member}
				AND is_pending = 0',
			array('clan' => $clan_id, 'member' => $id_member)
		);
		if ($smcFunc['db_num_rows']($request) == 0)
			$clan_id = 0;
		$smcFunc['db_free_result']($request);
	}

	return $clan_id;
}

/**
 * Check if a member is a clan leader/co-leader.
 *
 * @param int $id_clan Clan ID
 * @param int $id_member Member ID
 * @return bool True if leader or co-leader
 */
function rivals_is_clan_leader($id_clan, $id_member)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT role
		FROM {db_prefix}rivals_clan_members
		WHERE id_clan = {int:clan}
			AND id_member = {int:member}
			AND is_pending = 0',
		array('clan' => $id_clan, 'member' => $id_member)
	);
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	return !empty($row) && $row['role'] >= 1;
}

/**
 * Standard pagination helper.
 *
 * @param int $total Total number of items
 * @param int $per_page Items per page
 * @param string $base_url Base URL for pagination links
 * @param int $start Current start position
 * @return string Pagination HTML
 */
function rivals_pagination($total, $per_page, $base_url, $start)
{
	return constructPageIndex($base_url, $_REQUEST['start'], $total, $per_page, true);
}
?>