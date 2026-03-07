<?php
/**
 * SMF Rivals - Main Action Router
 * Dispatches ?action=rivals;sa=X to appropriate handler functions.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Main entry point for ?action=rivals
 * Routes to sub-actions based on 'sa' parameter.
 */
function Rivals()
{
	global $context, $txt, $modSettings, $sourcedir;

	// Must be enabled
	if (empty($modSettings['rivals_enabled']))
		fatal_lang_error('rivals_disabled', false);

	// Load language
	if (loadLanguage('Rivals') == false)
		loadLanguage('Rivals', 'english');

	// Require view permission
	isAllowedTo('rivals_view');

	// Load the library
	require_once($sourcedir . '/Rivals/RivalsLib.php');

	// Define sub-actions and their source files + functions
	$subActions = array(
		// Public pages
		'platforms'     => array('Rivals/Rivals.php', 'RivalsPlatforms'),
		'ladders'       => array('Rivals/Rivals.php', 'RivalsLadders'),
		'standings'     => array('Rivals/RivalsLadder.php', 'RivalsStandings'),
		'rules'         => array('Rivals/RivalsLadder.php', 'RivalsRules'),
		'clans'         => array('Rivals/RivalsClan.php', 'RivalsClanList'),
		'clan'          => array('Rivals/RivalsClan.php', 'RivalsClanProfile'),
		'createclan'    => array('Rivals/RivalsClan.php', 'RivalsCreateClan'),
		'joinclan'      => array('Rivals/RivalsClan.php', 'RivalsJoinClan'),
		'tournaments'   => array('Rivals/RivalsTournament.php', 'RivalsTournamentList'),
		'brackets'      => array('Rivals/RivalsTournament.php', 'RivalsBrackets'),
		'signup'        => array('Rivals/RivalsTournament.php', 'RivalsTournamentSignup'),
		'matches'       => array('Rivals/Rivals.php', 'RivalsMatchHistory'),
		'mvp'           => array('Rivals/Rivals.php', 'RivalsMVP'),
		'mvpchart'      => array('Rivals/Rivals.php', 'RivalsMVPChart'),
		'random'        => array('Rivals/Rivals.php', 'RivalsRandom'),
		'leaderboard'   => array('Rivals/Rivals.php', 'RivalsLeaderboard'),

		// Clan management (authenticated)
		'manageclan'    => array('Rivals/RivalsClan.php', 'RivalsManageClan'),
		'editclan'      => array('Rivals/RivalsClan.php', 'RivalsEditClan'),
		'members'       => array('Rivals/RivalsClan.php', 'RivalsClanMembers'),
		'pending'       => array('Rivals/RivalsClan.php', 'RivalsPendingMembers'),
		'invite'        => array('Rivals/RivalsClan.php', 'RivalsInviteMembers'),
		'roster'        => array('Rivals/RivalsClan.php', 'RivalsManageRoster'),
		'clanchat'      => array('Rivals/RivalsClan.php', 'RivalsClanChat'),

		// Match/challenge workflow
		'challenge'     => array('Rivals/RivalsMatch.php', 'RivalsCreateChallenge'),
		'challenges'    => array('Rivals/RivalsMatch.php', 'RivalsChallengeList'),
		'mymatch'       => array('Rivals/RivalsMatch.php', 'RivalsMyMatch'),
		'confirmmatch'  => array('Rivals/RivalsMatch.php', 'RivalsConfirmMatch'),
		'matchmvp'      => array('Rivals/RivalsMatch.php', 'RivalsMatchMVP'),
		'matchfinder'   => array('Rivals/RivalsMatch.php', 'RivalsMatchFinder'),
		'matchchat'     => array('Rivals/RivalsMatch.php', 'RivalsMatchChat'),
		'mytournaments' => array('Rivals/RivalsTournament.php', 'RivalsMyTournaments'),

		// League
		'league'        => array('Rivals/RivalsLeague.php', 'RivalsLeagueStandings'),
		'seasons'       => array('Rivals/RivalsLeague.php', 'RivalsSeasonHistory'),

		// AJAX
		'ajax'          => array('Rivals/RivalsAjax.php', 'RivalsAjax'),
	);

	// Determine which sub-action to use
	$sa = isset($_GET['sa']) && isset($subActions[$_GET['sa']]) ? $_GET['sa'] : 'platforms';

	// Set up the page
	$context['page_title'] = isset($txt['rivals_menu']) ? $txt['rivals_menu'] : 'Rivals';
	$context['sub_action'] = $sa;

	// Set linktree
	$context['linktree'][] = array(
		'url' => $context['canonical_url'] = smf_scripturl() . '?action=rivals',
		'name' => isset($txt['rivals_menu']) ? $txt['rivals_menu'] : 'Rivals',
	);

	// Load the required source file and call the function
	$file = $subActions[$sa][0];
	$function = $subActions[$sa][1];

	// Only require if it's a different file
	if ($file !== 'Rivals/Rivals.php')
		require_once($sourcedir . '/' . $file);

	call_user_func($function);
}

/**
 * Helper to get the script URL.
 */
function smf_scripturl()
{
	global $scripturl;
	return $scripturl;
}

/**
 * Platform listing page.
 * Shows all gaming platforms with their logos.
 */
function RivalsPlatforms()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['page_title'] = $txt['rivals_platforms'];
	$context['sub_template'] = 'rivals_platforms';

	loadTemplate('Rivals');

	// Fetch all platforms
	$context['rivals_platforms'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT p.id_platform, p.name, p.logo, p.logo_width, p.logo_height,
			COUNT(l.id_ladder) AS ladder_count
		FROM {db_prefix}rivals_platforms AS p
			LEFT JOIN {db_prefix}rivals_ladders AS l ON (l.id_platform = p.id_platform AND l.id_parent = 0)
		GROUP BY p.id_platform, p.name, p.logo, p.logo_width, p.logo_height
		ORDER BY p.name',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['rivals_platforms'][$row['id_platform']] = array(
			'id' => $row['id_platform'],
			'name' => $row['name'],
			'logo' => $row['logo'],
			'logo_width' => $row['logo_width'],
			'logo_height' => $row['logo_height'],
			'ladder_count' => $row['ladder_count'],
			'href' => $scripturl . '?action=rivals;sa=ladders;platform=' . $row['id_platform'],
		);
	}
	$smcFunc['db_free_result']($request);

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=platforms',
		'name' => $txt['rivals_platforms'],
	);
}

/**
 * Ladder listing for a platform.
 * Shows parent ladders and their sub-ladders.
 */
function RivalsLadders()
{
	global $context, $smcFunc, $txt, $scripturl;

	$id_platform = isset($_GET['platform']) ? (int) $_GET['platform'] : 0;

	if ($id_platform <= 0)
		redirectexit('action=rivals;sa=platforms');

	// Get platform info
	$request = $smcFunc['db_query']('', '
		SELECT id_platform, name, logo
		FROM {db_prefix}rivals_platforms
		WHERE id_platform = {int:platform}',
		array('platform' => $id_platform)
	);
	$context['rivals_platform'] = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($context['rivals_platform']))
		fatal_lang_error('rivals_platform_not_found', false);

	$context['page_title'] = $context['rivals_platform']['name'] . ' - ' . $txt['rivals_ladders'];
	$context['sub_template'] = 'rivals_ladders';

	loadTemplate('Rivals');

	// Fetch ladders (parent + children) for this platform
	$context['rivals_ladders'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT id_ladder, id_parent, name, short_name, description, logo,
			logo_width, logo_height, is_locked, is_1v1, ladder_style,
			ranking_system, display_order, sub_order
		FROM {db_prefix}rivals_ladders
		WHERE id_platform = {int:platform}
		ORDER BY display_order, sub_order, name',
		array('platform' => $id_platform)
	);

	$all_ladders = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$all_ladders[$row['id_ladder']] = $row;
	$smcFunc['db_free_result']($request);

	// Build hierarchy
	foreach ($all_ladders as $ladder)
	{
		if ($ladder['id_parent'] == 0)
		{
			$context['rivals_ladders'][$ladder['id_ladder']] = array(
				'id' => $ladder['id_ladder'],
				'name' => $ladder['name'],
				'short_name' => $ladder['short_name'],
				'description' => $ladder['description'],
				'logo' => $ladder['logo'],
				'is_locked' => $ladder['is_locked'],
				'is_1v1' => $ladder['is_1v1'],
				'href' => $scripturl . '?action=rivals;sa=standings;ladder=' . $ladder['id_ladder'],
				'children' => array(),
			);
		}
	}

	foreach ($all_ladders as $ladder)
	{
		if ($ladder['id_parent'] > 0 && isset($context['rivals_ladders'][$ladder['id_parent']]))
		{
			$context['rivals_ladders'][$ladder['id_parent']]['children'][$ladder['id_ladder']] = array(
				'id' => $ladder['id_ladder'],
				'name' => $ladder['name'],
				'short_name' => $ladder['short_name'],
				'description' => $ladder['description'],
				'is_locked' => $ladder['is_locked'],
				'is_1v1' => $ladder['is_1v1'],
				'href' => $scripturl . '?action=rivals;sa=standings;ladder=' . $ladder['id_ladder'],
			);
		}
	}

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=platforms',
		'name' => $txt['rivals_platforms'],
	);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=ladders;platform=' . $id_platform,
		'name' => $context['rivals_platform']['name'],
	);
}

/**
 * Recent match history.
 */
function RivalsMatchHistory()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['page_title'] = $txt['rivals_matches'];
	$context['sub_template'] = 'rivals_match_history';

	loadTemplate('RivalsMatch');

	// Pagination
	$per_page = 20;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	// Count total completed matches
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}rivals_matches
		WHERE status = 1',
		array()
	);
	list ($total) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=rivals;sa=matches', $start, $total, $per_page, true);

	// Fetch recent matches
	$context['rivals_matches'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT m.id_match, m.id_ladder, m.match_type, m.challenger_id, m.challengee_id,
			m.challenger_score, m.challengee_score, m.winner_id, m.status,
			m.completed_at, l.name AS ladder_name
		FROM {db_prefix}rivals_matches AS m
			LEFT JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = m.id_ladder)
		WHERE m.status = 1
		ORDER BY m.completed_at DESC
		LIMIT {int:start}, {int:limit}',
		array('start' => $start, 'limit' => $per_page)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['rivals_matches'][$row['id_match']] = $row;
	}
	$smcFunc['db_free_result']($request);

	// Linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=matches',
		'name' => $txt['rivals_matches'],
	);
}

/**
 * MVP leaderboard.
 */
function RivalsMVP()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['page_title'] = $txt['rivals_mvp'];
	$context['sub_template'] = 'rivals_mvp';

	loadTemplate('Rivals');

	$context['rivals_mvp_leaders'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT mem.id_member, mem.real_name, mem.rivals_mvp_count
		FROM {db_prefix}members AS mem
		WHERE mem.rivals_mvp_count > 0
		ORDER BY mem.rivals_mvp_count DESC
		LIMIT 50',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['rivals_mvp_leaders'][] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'mvp_count' => $row['rivals_mvp_count'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
		);
	}
	$smcFunc['db_free_result']($request);

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=mvp',
		'name' => $txt['rivals_mvp'],
	);
}

/**
 * MVP chart per ladder.
 */
function RivalsMVPChart()
{
	global $context, $txt;

	$context['page_title'] = $txt['rivals_mvp_chart'];
	$context['sub_template'] = 'rivals_mvp_chart';

	loadTemplate('Rivals');
}

/**
 * Random map of the day.
 */
function RivalsRandom()
{
	global $context, $smcFunc, $txt;

	$context['page_title'] = $txt['rivals_random'];
	$context['sub_template'] = 'rivals_random';

	loadTemplate('Rivals');

	$request = $smcFunc['db_query']('', '
		SELECT id_random, game_name, short_name, image, last_updated
		FROM {db_prefix}rivals_random_maps
		ORDER BY RAND()
		LIMIT 1',
		array()
	);
	$context['rivals_random_map'] = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
}

/**
 * User leaderboard.
 */
function RivalsLeaderboard()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['page_title'] = $txt['rivals_leaderboard'];
	$context['sub_template'] = 'rivals_leaderboard';

	loadTemplate('Rivals');

	$context['rivals_leaderboard'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT mem.id_member, mem.real_name, mem.rivals_ladder_value,
			mem.rivals_mvp_count, mem.rivals_pwner_count, mem.rivals_chicken_count
		FROM {db_prefix}members AS mem
		WHERE CAST(mem.rivals_ladder_value AS SIGNED) > 0
		ORDER BY CAST(mem.rivals_ladder_value AS SIGNED) DESC
		LIMIT 100',
		array()
	);
	$rank = 1;
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['rivals_leaderboard'][] = array(
			'rank' => $rank++,
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'exp' => $row['rivals_ladder_value'],
			'mvp_count' => $row['rivals_mvp_count'],
			'pwner_count' => $row['rivals_pwner_count'],
			'chicken_count' => $row['rivals_chicken_count'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
		);
	}
	$smcFunc['db_free_result']($request);

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=rivals;sa=leaderboard',
		'name' => $txt['rivals_leaderboard'],
	);
}
?>