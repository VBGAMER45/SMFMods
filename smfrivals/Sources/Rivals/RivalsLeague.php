<?php
/**
 * SMF Rivals - League/Season Management
 * Handles league standings (round-robin), season history, and season archives.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * League standings page.
 * Shows round-robin standings for a tournament of type "league" (tournament_type=3)
 * or ladder-based league with season support.
 */
function RivalsLeagueStandings()
{
	global $context, $smcFunc, $txt, $scripturl, $settings;

	$context['sub_template'] = 'rivals_league_standings';
	loadTemplate('Rivals');

	$id_ladder = isset($_GET['ladder']) ? (int) $_GET['ladder'] : 0;
	$id_tournament = isset($_GET['tournament']) ? (int) $_GET['tournament'] : 0;

	// League can be viewed by ladder (seasonal league) or tournament (round-robin tournament)
	if ($id_tournament > 0)
	{
		_rivalsLeagueFromTournament($id_tournament);
		return;
	}

	if ($id_ladder <= 0)
		fatal_lang_error('rivals_ladder_not_found', false);

	// Load ladder info
	$request = $smcFunc['db_query']('', '
		SELECT l.*, p.name AS platform_name
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
	$context['page_title'] = $ladder['name'] . ' - ' . $txt['rivals_league_standings'];

	$is_1v1 = !empty($ladder['is_1v1']);
	$is_football = ($ladder['ladder_style'] == 3);

	// Load active season for this ladder
	$request = $smcFunc['db_query']('', '
		SELECT * FROM {db_prefix}rivals_seasons
		WHERE id_ladder = {int:ladder} AND status = 1
		LIMIT 1',
		array('ladder' => $id_ladder)
	);
	$context['rivals_active_season'] = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Load all seasons for this ladder (for season selector)
	$request = $smcFunc['db_query']('', '
		SELECT id_season, name, status
		FROM {db_prefix}rivals_seasons
		WHERE id_ladder = {int:ladder}
		ORDER BY id_season DESC',
		array('ladder' => $id_ladder)
	);
	$context['rivals_seasons'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_seasons'][$row['id_season']] = $row;
	$smcFunc['db_free_result']($request);

	// Viewing a specific season?
	$view_season = isset($_GET['season']) ? (int) $_GET['season'] : 0;
	$context['rivals_viewing_season'] = $view_season;

	// If viewing an archived season, load from season_data
	if ($view_season > 0 && isset($context['rivals_seasons'][$view_season]) && $context['rivals_seasons'][$view_season]['status'] == 0)
	{
		_rivalsLeagueSeasonArchive($id_ladder, $view_season, $ladder);
		return;
	}

	// Load current standings
	$id_field = $is_1v1 ? 'id_member' : 'id_clan';

	// Determine sort order based on ranking system
	switch ($ladder['ranking_system'])
	{
		case 1: // SWAP
			$order_by = 's.current_rank ASC';
			break;
		case 2: // RTH
		default: // ELO
			$order_by = 's.score DESC';
			break;
	}

	$request = $smcFunc['db_query']('', '
		SELECT s.*
		FROM {db_prefix}rivals_standings AS s
		WHERE s.id_ladder = {int:ladder}
			AND s.' . $id_field . ' > 0
		ORDER BY ' . $order_by,
		array('ladder' => $id_ladder)
	);

	$standings_raw = array();
	$entity_ids = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$standings_raw[] = $row;
		if ($is_1v1)
			$entity_ids[] = $row['id_member'];
		else
			$entity_ids[] = $row['id_clan'];
	}
	$smcFunc['db_free_result']($request);

	// Resolve entity names
	$entity_names = array();
	$entity_logos = array();
	if (!empty($entity_ids))
	{
		if ($is_1v1)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member, real_name, rivals_gamer_name
				FROM {db_prefix}members
				WHERE id_member IN ({array_int:ids})',
				array('ids' => $entity_ids)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$entity_names[$row['id_member']] = $row['real_name'];
				$entity_logos[$row['id_member']] = array('gamer_name' => $row['rivals_gamer_name']);
			}
			$smcFunc['db_free_result']($request);
		}
		else
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_clan, name, logo, logo_ext
				FROM {db_prefix}rivals_clans
				WHERE id_clan IN ({array_int:ids})',
				array('ids' => $entity_ids)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$entity_names[$row['id_clan']] = $row['name'];
				$entity_logos[$row['id_clan']] = array('logo' => $row['logo'], 'logo_ext' => $row['logo_ext']);
			}
			$smcFunc['db_free_result']($request);
		}
	}

	// Build standings array with league-style points (W=3, D=1, L=0)
	$context['rivals_standings_data'] = array();
	$position = 1;
	foreach ($standings_raw as $row)
	{
		$eid = $is_1v1 ? $row['id_member'] : $row['id_clan'];
		$name = isset($entity_names[$eid]) ? $entity_names[$eid] : '?';

		// League points: 3 per win, 1 per draw
		$points = ($row['wins'] * 3) + $row['draws'];
		$played = $row['wins'] + $row['losses'] + $row['draws'];

		$context['rivals_standings_data'][] = array(
			'position' => $position++,
			'entity_id' => $eid,
			'entity_name' => $name,
			'logo' => !$is_1v1 && isset($entity_logos[$eid]) ? $entity_logos[$eid]['logo'] : '',
			'gamer_name' => $is_1v1 && isset($entity_logos[$eid]) ? $entity_logos[$eid]['gamer_name'] : '',
			'played' => $played,
			'wins' => $row['wins'],
			'losses' => $row['losses'],
			'draws' => $row['draws'],
			'points' => $points,
			'goals_for' => $row['goals_for'],
			'goals_against' => $row['goals_against'],
			'goal_difference' => $row['goals_for'] - $row['goals_against'],
			'score' => $row['score'],
			'streak' => $row['streak'],
			'is_frozen' => !empty($row['is_frozen']),
			'href' => $is_1v1
				? $scripturl . '?action=profile;u=' . $eid
				: $scripturl . '?action=rivals;sa=clan;id=' . $eid,
		);
	}

	$context['rivals_is_1v1'] = $is_1v1;
	$context['rivals_is_football'] = $is_football;

	// Load round-robin schedule if we have a related league tournament
	$context['rivals_schedule'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT id_tournament, name
		FROM {db_prefix}rivals_tournaments
		WHERE tournament_type = 3 AND status IN (1, 2)
		ORDER BY created_at DESC
		LIMIT 1',
		array()
	);
	$league_tournament = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($league_tournament))
		$context['rivals_league_tournament'] = $league_tournament;

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=league;ladder=' . $id_ladder,
		'name' => $ladder['name'] . ' - ' . $txt['rivals_league_standings'],
	);
}

/**
 * Display league standings from a tournament (round-robin type).
 */
function _rivalsLeagueFromTournament($id_tournament)
{
	global $context, $smcFunc, $txt, $scripturl;

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
	$context['page_title'] = $tournament['name'] . ' - ' . $txt['rivals_league_standings'];
	$is_user_based = !empty($tournament['is_user_based']);

	// Load all entries
	$request = $smcFunc['db_query']('', '
		SELECT id_clan FROM {db_prefix}rivals_tournament_entries
		WHERE id_tournament = {int:tid}
		GROUP BY id_clan',
		array('tid' => $id_tournament)
	);
	$team_ids = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$team_ids[] = $row['id_clan'];
	$smcFunc['db_free_result']($request);

	// Resolve names
	$entity_names = array();
	if (!empty($team_ids))
	{
		if ($is_user_based)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member, real_name FROM {db_prefix}members
				WHERE id_member IN ({array_int:ids})',
				array('ids' => $team_ids)
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
				array('ids' => $team_ids)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$entity_names[$row['id_clan']] = $row['name'];
			$smcFunc['db_free_result']($request);
		}
	}

	// Load all matches and build W-L-D per team
	$request = $smcFunc['db_query']('', '
		SELECT * FROM {db_prefix}rivals_tournament_matches
		WHERE id_tournament = {int:tid}
			AND team1_confirmed = 1 AND team2_confirmed = 1',
		array('tid' => $id_tournament)
	);

	$team_stats = array();
	foreach ($team_ids as $tid)
	{
		$team_stats[$tid] = array(
			'wins' => 0, 'losses' => 0, 'draws' => 0,
			'goals_for' => 0, 'goals_against' => 0,
		);
	}

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($row['winner_id'] == 0)
		{
			// Draw
			if (isset($team_stats[$row['team1_id']]))
				$team_stats[$row['team1_id']]['draws']++;
			if (isset($team_stats[$row['team2_id']]))
				$team_stats[$row['team2_id']]['draws']++;
		}
		else
		{
			$loser_id = ($row['winner_id'] == $row['team1_id']) ? $row['team2_id'] : $row['team1_id'];
			if (isset($team_stats[$row['winner_id']]))
				$team_stats[$row['winner_id']]['wins']++;
			if (isset($team_stats[$loser_id]))
				$team_stats[$loser_id]['losses']++;
		}

		// Goals
		if (isset($team_stats[$row['team1_id']]))
		{
			$team_stats[$row['team1_id']]['goals_for'] += $row['team1_score'];
			$team_stats[$row['team1_id']]['goals_against'] += $row['team2_score'];
		}
		if (isset($team_stats[$row['team2_id']]))
		{
			$team_stats[$row['team2_id']]['goals_for'] += $row['team2_score'];
			$team_stats[$row['team2_id']]['goals_against'] += $row['team1_score'];
		}
	}
	$smcFunc['db_free_result']($request);

	// Build standings sorted by points, then GD, then GF
	$standings = array();
	foreach ($team_stats as $tid => $stats)
	{
		$points = ($stats['wins'] * 3) + $stats['draws'];
		$gd = $stats['goals_for'] - $stats['goals_against'];
		$played = $stats['wins'] + $stats['losses'] + $stats['draws'];

		$standings[] = array(
			'entity_id' => $tid,
			'entity_name' => isset($entity_names[$tid]) ? $entity_names[$tid] : '?',
			'logo' => '',
			'gamer_name' => '',
			'played' => $played,
			'wins' => $stats['wins'],
			'losses' => $stats['losses'],
			'draws' => $stats['draws'],
			'points' => $points,
			'goals_for' => $stats['goals_for'],
			'goals_against' => $stats['goals_against'],
			'goal_difference' => $gd,
			'score' => $points,
			'streak' => '',
			'is_frozen' => false,
			'href' => $is_user_based
				? $scripturl . '?action=profile;u=' . $tid
				: $scripturl . '?action=rivals;sa=clan;id=' . $tid,
		);
	}

	// Sort: points DESC, goal_difference DESC, goals_for DESC
	usort($standings, function($a, $b) {
		if ($a['points'] != $b['points'])
			return $b['points'] - $a['points'];
		if ($a['goal_difference'] != $b['goal_difference'])
			return $b['goal_difference'] - $a['goal_difference'];
		return $b['goals_for'] - $a['goals_for'];
	});

	// Assign positions
	$pos = 1;
	foreach ($standings as &$entry)
		$entry['position'] = $pos++;

	$context['rivals_standings_data'] = $standings;
	$context['rivals_is_1v1'] = $is_user_based;
	$context['rivals_is_football'] = true; // League tournaments use football-style display
	$context['rivals_seasons'] = array();
	$context['rivals_viewing_season'] = 0;
	$context['rivals_active_season'] = null;
	$context['rivals_ladder'] = array('name' => $tournament['name'], 'ranking_system' => 0);

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=league;tournament=' . $id_tournament,
		'name' => $tournament['name'],
	);
}

/**
 * Display archived season standings.
 */
function _rivalsLeagueSeasonArchive($id_ladder, $id_season, $ladder)
{
	global $context, $smcFunc, $txt, $scripturl;

	$is_1v1 = !empty($ladder['is_1v1']);
	$is_football = ($ladder['ladder_style'] == 3);

	$request = $smcFunc['db_query']('', '
		SELECT * FROM {db_prefix}rivals_season_data
		WHERE id_season = {int:season}
		ORDER BY current_rank ASC',
		array('season' => $id_season)
	);

	$standings_raw = array();
	$entity_ids = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$standings_raw[] = $row;
		$entity_ids[] = $row['id_clan'];
	}
	$smcFunc['db_free_result']($request);

	// Resolve entity names
	$entity_names = array();
	if (!empty($entity_ids))
	{
		if ($is_1v1)
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_member, real_name FROM {db_prefix}members
				WHERE id_member IN ({array_int:ids})',
				array('ids' => $entity_ids)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$entity_names[$row['id_member']] = $row['real_name'];
			$smcFunc['db_free_result']($request);
		}
		else
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_clan, name, logo FROM {db_prefix}rivals_clans
				WHERE id_clan IN ({array_int:ids})',
				array('ids' => $entity_ids)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$entity_names[$row['id_clan']] = $row['name'];
			}
			$smcFunc['db_free_result']($request);
		}
	}

	$season_name = isset($context['rivals_seasons'][$id_season]) ? $context['rivals_seasons'][$id_season]['name'] : '?';
	$context['page_title'] = $ladder['name'] . ' - ' . $season_name;

	$context['rivals_standings_data'] = array();
	$position = 1;
	foreach ($standings_raw as $row)
	{
		$eid = $row['id_clan'];
		$points = ($row['wins'] * 3) + $row['draws'];
		$played = $row['wins'] + $row['losses'] + $row['draws'];

		$context['rivals_standings_data'][] = array(
			'position' => $position++,
			'entity_id' => $eid,
			'entity_name' => isset($entity_names[$eid]) ? $entity_names[$eid] : '?',
			'logo' => '',
			'gamer_name' => '',
			'played' => $played,
			'wins' => $row['wins'],
			'losses' => $row['losses'],
			'draws' => $row['draws'],
			'points' => $points,
			'goals_for' => $row['goals_for'],
			'goals_against' => $row['goals_against'],
			'goal_difference' => $row['goals_for'] - $row['goals_against'],
			'score' => $row['score'],
			'streak' => $row['streak'],
			'is_frozen' => !empty($row['is_frozen']),
			'href' => $is_1v1
				? $scripturl . '?action=profile;u=' . $eid
				: $scripturl . '?action=rivals;sa=clan;id=' . $eid,
		);
	}

	$context['rivals_is_1v1'] = $is_1v1;
	$context['rivals_is_football'] = $is_football;

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=league;ladder=' . $id_ladder . ';season=' . $id_season,
		'name' => $season_name,
	);
}

/**
 * Season history page.
 * Shows all seasons for a ladder with their archived standings.
 */
function RivalsSeasonHistory()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['sub_template'] = 'rivals_season_history';
	loadTemplate('Rivals');

	$id_ladder = isset($_GET['ladder']) ? (int) $_GET['ladder'] : 0;

	if ($id_ladder <= 0)
		fatal_lang_error('rivals_ladder_not_found', false);

	// Load ladder info
	$request = $smcFunc['db_query']('', '
		SELECT id_ladder, name, is_1v1, ladder_style
		FROM {db_prefix}rivals_ladders
		WHERE id_ladder = {int:ladder}',
		array('ladder' => $id_ladder)
	);
	$ladder = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($ladder))
		fatal_lang_error('rivals_ladder_not_found', false);

	$context['rivals_ladder'] = $ladder;
	$context['page_title'] = $ladder['name'] . ' - ' . $txt['rivals_season_history'];
	$is_1v1 = !empty($ladder['is_1v1']);

	// Load all seasons
	$request = $smcFunc['db_query']('', '
		SELECT s.id_season, s.name, s.status,
			(SELECT COUNT(*) FROM {db_prefix}rivals_season_data AS sd WHERE sd.id_season = s.id_season) AS team_count
		FROM {db_prefix}rivals_seasons AS s
		WHERE s.id_ladder = {int:ladder}
		ORDER BY s.id_season DESC',
		array('ladder' => $id_ladder)
	);
	$context['rivals_seasons_list'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['rivals_seasons_list'][$row['id_season']] = array(
			'id' => $row['id_season'],
			'name' => $row['name'],
			'status' => $row['status'],
			'status_label' => $row['status'] == 1 ? $txt['rivals_season_active'] : $txt['rivals_season_ended'],
			'team_count' => $row['team_count'],
			'href' => $scripturl . '?action=rivals;sa=league;ladder=' . $id_ladder . ';season=' . $row['id_season'],
		);
	}
	$smcFunc['db_free_result']($request);

	// If a specific season is selected, load the top 3 for preview
	$view_season = isset($_GET['season']) ? (int) $_GET['season'] : 0;
	$context['rivals_season_detail'] = array();

	if ($view_season > 0)
	{
		$request = $smcFunc['db_query']('', '
			SELECT sd.*
			FROM {db_prefix}rivals_season_data AS sd
			WHERE sd.id_season = {int:season}
			ORDER BY sd.current_rank ASC',
			array('season' => $view_season)
		);

		$entity_ids = array();
		$rows = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$rows[] = $row;
			$entity_ids[] = $row['id_clan'];
		}
		$smcFunc['db_free_result']($request);

		// Resolve names
		$entity_names = array();
		if (!empty($entity_ids))
		{
			if ($is_1v1)
			{
				$request = $smcFunc['db_query']('', '
					SELECT id_member, real_name FROM {db_prefix}members
					WHERE id_member IN ({array_int:ids})',
					array('ids' => $entity_ids)
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
					array('ids' => $entity_ids)
				);
				while ($row = $smcFunc['db_fetch_assoc']($request))
					$entity_names[$row['id_clan']] = $row['name'];
				$smcFunc['db_free_result']($request);
			}
		}

		$pos = 1;
		foreach ($rows as $row)
		{
			$eid = $row['id_clan'];
			$points = ($row['wins'] * 3) + $row['draws'];

			$context['rivals_season_detail'][] = array(
				'position' => $pos++,
				'entity_name' => isset($entity_names[$eid]) ? $entity_names[$eid] : '?',
				'wins' => $row['wins'],
				'losses' => $row['losses'],
				'draws' => $row['draws'],
				'points' => $points,
				'score' => $row['score'],
				'goals_for' => $row['goals_for'],
				'goals_against' => $row['goals_against'],
				'goal_difference' => $row['goals_for'] - $row['goals_against'],
				'best_rank' => $row['best_rank'],
				'worst_rank' => $row['worst_rank'],
				'href' => $is_1v1
					? $scripturl . '?action=profile;u=' . $eid
					: $scripturl . '?action=rivals;sa=clan;id=' . $eid,
			);
		}

		$context['rivals_viewing_season'] = $view_season;
	}

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=seasons;ladder=' . $id_ladder,
		'name' => $ladder['name'] . ' - ' . $txt['rivals_season_history'],
	);
}
?>