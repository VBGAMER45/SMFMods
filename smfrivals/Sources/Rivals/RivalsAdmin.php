<?php
/**
 * SMF Rivals - Admin Panel Controller
 * Handles all admin area actions for Rivals.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Main admin entry point.
 * Called from ?action=admin;area=rivals
 */
function RivalsAdmin()
{
	global $context, $txt, $scripturl, $sourcedir;

	if (loadLanguage('Rivals') == false)
		loadLanguage('Rivals', 'english');

	isAllowedTo('rivals_admin');

	require_once($sourcedir . '/Rivals/RivalsLib.php');

	$subActions = array(
		'settings'    => 'RivalsAdminSettings',
		'platforms'   => 'RivalsAdminPlatforms',
		'ladders'     => 'RivalsAdminLadders',
		'clans'       => 'RivalsAdminClans',
		'tournaments' => 'RivalsAdminTournaments',
		'seasons'     => 'RivalsAdminSeasons',
		'mvp'         => 'RivalsAdminMVP',
		'gamemodes'   => 'RivalsAdminGameModes',
		'random'      => 'RivalsAdminRandom',
		'matches'     => 'RivalsAdminMatches',
	);

	$sa = isset($_GET['sa']) && isset($subActions[$_GET['sa']]) ? $_GET['sa'] : 'settings';

	$context['page_title'] = $txt['rivals_admin'];
	$context['sub_action'] = $sa;

	// Set up admin tab_data so SMF renders section tabs
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['rivals_admin'],
		'description' => '',
		'tabs' => array(
			'settings' => array(
				'description' => $txt['rivals_admin_tab_settings'],
			),
			'platforms' => array(
				'description' => $txt['rivals_admin_tab_platforms'],
			),
			'ladders' => array(
				'description' => $txt['rivals_admin_tab_ladders'],
			),
			'clans' => array(
				'description' => $txt['rivals_admin_tab_clans'],
			),
			'tournaments' => array(
				'description' => $txt['rivals_admin_tab_tournaments'],
			),
			'seasons' => array(
				'description' => $txt['rivals_admin_tab_seasons'],
			),
			'mvp' => array(
				'description' => $txt['rivals_admin_tab_mvp'],
			),
			'gamemodes' => array(
				'description' => $txt['rivals_admin_tab_gamemodes'],
			),
			'random' => array(
				'description' => $txt['rivals_admin_tab_random'],
			),
			'matches' => array(
				'description' => $txt['rivals_admin_tab_matches'],
			),
		),
	);

	loadTemplate('RivalsAdmin');

	call_user_func($subActions[$sa]);
}

/**
 * General settings page.
 */
function RivalsAdminSettings()
{
	global $context, $txt, $scripturl, $modSettings, $smcFunc;

	$context['sub_template'] = 'rivals_admin_settings';
	$context['page_title'] = $txt['rivals_admin'] . ' - ' . $txt['rivals_admin_settings'];

	// Handle save
	if (isset($_POST['save']))
	{
		checkSession();

		$settings = array(
			'rivals_enabled' => isset($_POST['rivals_enabled']) ? 1 : 0,
			'rivals_min_posts' => (int) $_POST['rivals_min_posts'],
			'rivals_banned_group' => (int) $_POST['rivals_banned_group'],
			'rivals_frost_cost' => (int) $_POST['rivals_frost_cost'],
			'rivals_inactivity_penalty' => isset($_POST['rivals_inactivity_penalty']) ? 1 : 0,
			'rivals_max_report_hours' => (int) $_POST['rivals_max_report_hours'],
			'rivals_kickout_days' => (int) $_POST['rivals_kickout_days'],
			'rivals_mod_override' => isset($_POST['rivals_mod_override']) ? 1 : 0,
			'rivals_logo_max_size' => (int) $_POST['rivals_logo_max_size'],
			'rivals_logo_max_width' => (int) $_POST['rivals_logo_max_width'],
			'rivals_logo_max_height' => (int) $_POST['rivals_logo_max_height'],
		);

		updateSettings($settings);
		redirectexit('action=admin;area=rivals;sa=settings');
	}

	// Load current settings for display
	$context['rivals_settings'] = array(
		'enabled' => !empty($modSettings['rivals_enabled']),
		'min_posts' => isset($modSettings['rivals_min_posts']) ? $modSettings['rivals_min_posts'] : 0,
		'banned_group' => isset($modSettings['rivals_banned_group']) ? $modSettings['rivals_banned_group'] : 0,
		'frost_cost' => isset($modSettings['rivals_frost_cost']) ? $modSettings['rivals_frost_cost'] : 25,
		'inactivity_penalty' => !empty($modSettings['rivals_inactivity_penalty']),
		'max_report_hours' => isset($modSettings['rivals_max_report_hours']) ? $modSettings['rivals_max_report_hours'] : 72,
		'kickout_days' => isset($modSettings['rivals_kickout_days']) ? $modSettings['rivals_kickout_days'] : 30,
		'mod_override' => !empty($modSettings['rivals_mod_override']),
		'logo_max_size' => isset($modSettings['rivals_logo_max_size']) ? $modSettings['rivals_logo_max_size'] : 163840,
		'logo_max_width' => isset($modSettings['rivals_logo_max_width']) ? $modSettings['rivals_logo_max_width'] : 500,
		'logo_max_height' => isset($modSettings['rivals_logo_max_height']) ? $modSettings['rivals_logo_max_height'] : 500,
	);
}

// ============================================================
// Platforms CRUD
// ============================================================
function RivalsAdminPlatforms()
{
	global $context, $smcFunc, $txt, $scripturl, $settings, $modSettings;

	$context['sub_template'] = 'rivals_admin_platforms';
	$context['page_title'] = $txt['rivals_admin'] . ' - ' . $txt['rivals_admin_platforms'];
	$context['rivals_errors'] = array();

	// Delete
	if (isset($_GET['delete']))
	{
		checkSession('get');
		$id = (int) $_GET['delete'];
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_platforms WHERE id_platform = {int:id}', array('id' => $id));
		redirectexit('action=admin;area=rivals;sa=platforms');
	}

	// Edit mode
	$context['rivals_editing'] = null;
	if (isset($_GET['edit']))
	{
		$request = $smcFunc['db_query']('', 'SELECT * FROM {db_prefix}rivals_platforms WHERE id_platform = {int:id}', array('id' => (int) $_GET['edit']));
		$context['rivals_editing'] = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
	}

	// Save (add or edit)
	if (isset($_POST['save_platform']))
	{
		checkSession();

		$name = trim($smcFunc['htmlspecialchars']($_POST['platform_name']));
		$id = isset($_POST['id_platform']) ? (int) $_POST['id_platform'] : 0;

		if (empty($name))
			$context['rivals_errors'][] = $txt['rivals_admin_error_name_required'];

		// Logo upload
		$logo = '';
		$logo_w = 0;
		$logo_h = 0;

		if (!empty($_FILES['platform_logo']['name']) && $_FILES['platform_logo']['error'] == 0)
		{
			$result = _rivalsAdminUploadLogo('platform_logo', 'ladderlogo');
			if ($result === false)
				$context['rivals_errors'][] = $txt['rivals_error_logo_too_large'];
			else
			{
				$logo = $result['filename'];
				$logo_w = $result['width'];
				$logo_h = $result['height'];
			}
		}

		if (empty($context['rivals_errors']))
		{
			if ($id > 0)
			{
				$update = array('name' => $name);
				$set = 'name = {string:name}';
				if (!empty($logo))
				{
					$set .= ', logo = {string:logo}, logo_width = {int:lw}, logo_height = {int:lh}';
					$update['logo'] = $logo;
					$update['lw'] = $logo_w;
					$update['lh'] = $logo_h;
				}
				$update['id'] = $id;
				$smcFunc['db_query']('', 'UPDATE {db_prefix}rivals_platforms SET ' . $set . ' WHERE id_platform = {int:id}', $update);
			}
			else
			{
				$smcFunc['db_insert']('',
					'{db_prefix}rivals_platforms',
					array('name' => 'string', 'logo' => 'string', 'logo_width' => 'int', 'logo_height' => 'int'),
					array($name, $logo, $logo_w, $logo_h),
					array('id_platform')
				);
			}
			redirectexit('action=admin;area=rivals;sa=platforms');
		}
	}

	// Load all platforms
	$context['rivals_platforms'] = array();
	$request = $smcFunc['db_query']('', 'SELECT * FROM {db_prefix}rivals_platforms ORDER BY name', array());
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_platforms'][$row['id_platform']] = $row;
	$smcFunc['db_free_result']($request);
}

// ============================================================
// Ladders CRUD
// ============================================================
function RivalsAdminLadders()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['sub_template'] = 'rivals_admin_ladders';
	$context['page_title'] = $txt['rivals_admin'] . ' - ' . $txt['rivals_admin_ladders'];
	$context['rivals_errors'] = array();

	// Delete
	if (isset($_GET['delete']))
	{
		checkSession('get');
		$id = (int) $_GET['delete'];
		// Delete ladder and all sub-ladders
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_ladders WHERE id_ladder = {int:id} OR id_parent = {int:id}', array('id' => $id));
		redirectexit('action=admin;area=rivals;sa=ladders');
	}

	// Move up/down
	if (isset($_GET['move']) && isset($_GET['lid']))
	{
		checkSession('get');
		$lid = (int) $_GET['lid'];
		$dir = $_GET['move'] === 'up' ? -1 : 1;

		$request = $smcFunc['db_query']('', 'SELECT id_ladder, display_order, id_parent FROM {db_prefix}rivals_ladders WHERE id_ladder = {int:id}', array('id' => $lid));
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($row))
		{
			$new_order = $row['display_order'] + ($dir * 2);
			$smcFunc['db_query']('', 'UPDATE {db_prefix}rivals_ladders SET display_order = {int:order} WHERE id_ladder = {int:id}',
				array('order' => $new_order, 'id' => $lid));
		}
		redirectexit('action=admin;area=rivals;sa=ladders');
	}

	// Edit mode
	$context['rivals_editing'] = null;
	if (isset($_GET['edit']))
	{
		$request = $smcFunc['db_query']('', 'SELECT * FROM {db_prefix}rivals_ladders WHERE id_ladder = {int:id}', array('id' => (int) $_GET['edit']));
		$context['rivals_editing'] = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
	}

	// Save
	if (isset($_POST['save_ladder']))
	{
		checkSession();

		$id = isset($_POST['id_ladder']) ? (int) $_POST['id_ladder'] : 0;
		$name = trim($smcFunc['htmlspecialchars']($_POST['ladder_name']));
		$short_name = trim($smcFunc['htmlspecialchars']($_POST['short_name']));
		$description = $smcFunc['htmlspecialchars']($_POST['description']);
		$id_parent = (int) $_POST['id_parent'];
		$id_platform = (int) $_POST['id_platform'];
		$is_1v1 = isset($_POST['is_1v1']) ? 1 : 0;
		$is_locked = isset($_POST['is_locked']) ? 1 : 0;
		$ladder_style = (int) $_POST['ladder_style'];
		$ranking_system = (int) $_POST['ranking_system'];
		$win_system = (int) $_POST['win_system'];
		$enable_mvp = isset($_POST['enable_mvp']) ? 1 : 0;
		$enable_advstats = isset($_POST['enable_advstats']) ? 1 : 0;
		$limit_level = (int) $_POST['limit_level'];
		$id_moderator = (int) $_POST['id_moderator'];

		if (empty($name))
			$context['rivals_errors'][] = $txt['rivals_admin_error_name_required'];

		// Logo upload
		$logo = '';
		$logo_w = 0;
		$logo_h = 0;
		if (!empty($_FILES['ladder_logo']['name']) && $_FILES['ladder_logo']['error'] == 0)
		{
			$result = _rivalsAdminUploadLogo('ladder_logo', 'ladderlogo');
			if ($result !== false)
			{
				$logo = $result['filename'];
				$logo_w = $result['width'];
				$logo_h = $result['height'];
			}
		}

		if (empty($context['rivals_errors']))
		{
			$data = array(
				'name' => $name, 'short_name' => $short_name, 'description' => $description,
				'id_parent' => $id_parent, 'id_platform' => $id_platform, 'is_1v1' => $is_1v1,
				'is_locked' => $is_locked, 'ladder_style' => $ladder_style,
				'ranking_system' => $ranking_system, 'win_system' => $win_system,
				'enable_mvp' => $enable_mvp, 'enable_advstats' => $enable_advstats,
				'limit_level' => $limit_level, 'id_moderator' => $id_moderator,
			);

			if ($id > 0)
			{
				$set = 'name = {string:name}, short_name = {string:short_name}, description = {string:description},
					id_parent = {int:id_parent}, id_platform = {int:id_platform}, is_1v1 = {int:is_1v1},
					is_locked = {int:is_locked}, ladder_style = {int:ladder_style},
					ranking_system = {int:ranking_system}, win_system = {int:win_system},
					enable_mvp = {int:enable_mvp}, enable_advstats = {int:enable_advstats},
					limit_level = {int:limit_level}, id_moderator = {int:id_moderator}';
				if (!empty($logo))
				{
					$set .= ', logo = {string:logo}, logo_width = {int:lw}, logo_height = {int:lh}';
					$data['logo'] = $logo;
					$data['lw'] = $logo_w;
					$data['lh'] = $logo_h;
				}
				$data['id'] = $id;
				$smcFunc['db_query']('', 'UPDATE {db_prefix}rivals_ladders SET ' . $set . ' WHERE id_ladder = {int:id}', $data);
			}
			else
			{
				// Get max display_order
				$request = $smcFunc['db_query']('', 'SELECT MAX(display_order) FROM {db_prefix}rivals_ladders WHERE id_parent = {int:parent}', array('parent' => $id_parent));
				list ($max_order) = $smcFunc['db_fetch_row']($request);
				$smcFunc['db_free_result']($request);

				$smcFunc['db_insert']('',
					'{db_prefix}rivals_ladders',
					array('name' => 'string', 'short_name' => 'string', 'description' => 'string',
						'id_parent' => 'int', 'id_platform' => 'int', 'is_1v1' => 'int',
						'is_locked' => 'int', 'ladder_style' => 'int', 'ranking_system' => 'int',
						'win_system' => 'int', 'enable_mvp' => 'int', 'enable_advstats' => 'int',
						'limit_level' => 'int', 'id_moderator' => 'int', 'display_order' => 'int',
						'logo' => 'string', 'logo_width' => 'int', 'logo_height' => 'int'),
					array($name, $short_name, $description, $id_parent, $id_platform, $is_1v1,
						$is_locked, $ladder_style, $ranking_system, $win_system, $enable_mvp,
						$enable_advstats, $limit_level, $id_moderator, (int) $max_order + 1,
						$logo, $logo_w, $logo_h),
					array('id_ladder')
				);
			}
			redirectexit('action=admin;area=rivals;sa=ladders');
		}
	}

	// Load platforms for dropdown
	$context['rivals_platforms'] = array();
	$request = $smcFunc['db_query']('', 'SELECT id_platform, name FROM {db_prefix}rivals_platforms ORDER BY name', array());
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_platforms'][$row['id_platform']] = $row['name'];
	$smcFunc['db_free_result']($request);

	// Load all ladders (hierarchical)
	$context['rivals_ladders'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT l.*, p.name AS platform_name
		FROM {db_prefix}rivals_ladders AS l
			LEFT JOIN {db_prefix}rivals_platforms AS p ON (p.id_platform = l.id_platform)
		ORDER BY l.id_platform, l.display_order, l.sub_order',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_ladders'][$row['id_ladder']] = $row;
	$smcFunc['db_free_result']($request);
}

// ============================================================
// Clans Admin
// ============================================================
function RivalsAdminClans()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['sub_template'] = 'rivals_admin_clans';
	$context['page_title'] = $txt['rivals_admin'] . ' - ' . $txt['rivals_admin_clans'];

	// Delete
	if (isset($_GET['delete']))
	{
		checkSession('get');
		$id = (int) $_GET['delete'];
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_clans WHERE id_clan = {int:id}', array('id' => $id));
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_clan_members WHERE id_clan = {int:id}', array('id' => $id));
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_standings WHERE id_clan = {int:id}', array('id' => $id));
		redirectexit('action=admin;area=rivals;sa=clans');
	}

	// Toggle closed
	if (isset($_GET['toggle_close']))
	{
		checkSession('get');
		$id = (int) $_GET['toggle_close'];
		$smcFunc['db_query']('', 'UPDATE {db_prefix}rivals_clans SET is_closed = 1 - is_closed WHERE id_clan = {int:id}', array('id' => $id));
		redirectexit('action=admin;area=rivals;sa=clans');
	}

	// Pagination
	$per_page = 30;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	$request = $smcFunc['db_query']('', 'SELECT COUNT(*) FROM {db_prefix}rivals_clans', array());
	list ($total) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=rivals;sa=clans', $start, $total, $per_page, true);

	$context['rivals_clans'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT c.*, (SELECT COUNT(*) FROM {db_prefix}rivals_clan_members AS cm WHERE cm.id_clan = c.id_clan AND cm.is_pending = 0) AS member_count
		FROM {db_prefix}rivals_clans AS c
		ORDER BY c.name
		LIMIT {int:start}, {int:limit}',
		array('start' => $start, 'limit' => $per_page)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_clans'][] = $row;
	$smcFunc['db_free_result']($request);
}

// ============================================================
// Tournaments Admin
// ============================================================
function RivalsAdminTournaments()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['sub_template'] = 'rivals_admin_tournaments';
	$context['page_title'] = $txt['rivals_admin'] . ' - ' . $txt['rivals_admin_tournaments'];
	$context['rivals_errors'] = array();

	// Delete
	if (isset($_GET['delete']))
	{
		checkSession('get');
		$id = (int) $_GET['delete'];
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_tournaments WHERE id_tournament = {int:id}', array('id' => $id));
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_tournament_entries WHERE id_tournament = {int:id}', array('id' => $id));
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_tournament_matches WHERE id_tournament = {int:id}', array('id' => $id));
		redirectexit('action=admin;area=rivals;sa=tournaments');
	}

	// Change status
	if (isset($_GET['setstatus']) && isset($_GET['tid']))
	{
		checkSession('get');
		$tid = (int) $_GET['tid'];
		$status = max(0, min(3, (int) $_GET['setstatus']));
		$smcFunc['db_query']('', 'UPDATE {db_prefix}rivals_tournaments SET status = {int:status} WHERE id_tournament = {int:id}',
			array('status' => $status, 'id' => $tid));
		redirectexit('action=admin;area=rivals;sa=tournaments');
	}

	// Edit mode
	$context['rivals_editing'] = null;
	if (isset($_GET['edit']))
	{
		$request = $smcFunc['db_query']('', 'SELECT * FROM {db_prefix}rivals_tournaments WHERE id_tournament = {int:id}', array('id' => (int) $_GET['edit']));
		$context['rivals_editing'] = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
	}

	// Save
	if (isset($_POST['save_tournament']))
	{
		checkSession();

		$id = isset($_POST['id_tournament']) ? (int) $_POST['id_tournament'] : 0;
		$name = trim($smcFunc['htmlspecialchars']($_POST['tournament_name']));
		$short_name = trim($smcFunc['htmlspecialchars']($_POST['short_name']));
		$info = $smcFunc['htmlspecialchars']($_POST['info']);
		$bracket_size = (int) $_POST['bracket_size'];
		$tournament_type = (int) $_POST['tournament_type'];
		$signup_type = (int) $_POST['signup_type'];
		$is_user_based = isset($_POST['is_user_based']) ? 1 : 0;
		$enable_advstats = isset($_POST['enable_advstats']) ? 1 : 0;
		$enable_decerto = isset($_POST['enable_decerto']) ? 1 : 0;
		$is_restricted = isset($_POST['is_restricted']) ? 1 : 0;
		$min_members = (int) $_POST['min_members'];
		$max_members = (int) $_POST['max_members'];
		$status = (int) $_POST['status'];

		if (empty($name))
			$context['rivals_errors'][] = $txt['rivals_admin_error_name_required'];

		// Ensure bracket_size is power of 2
		$bracket_size = max(2, $bracket_size);
		$bracket_size = pow(2, (int) ceil(log($bracket_size, 2)));

		// Logo upload
		$logo = '';
		if (!empty($_FILES['tournament_logo']['name']) && $_FILES['tournament_logo']['error'] == 0)
		{
			$result = _rivalsAdminUploadLogo('tournament_logo', 'tournamentlogo');
			if ($result !== false)
				$logo = $result['filename'];
		}

		if (empty($context['rivals_errors']))
		{
			if ($id > 0)
			{
				$set = 'name = {string:name}, short_name = {string:short_name}, info = {string:info},
					bracket_size = {int:bracket_size}, tournament_type = {int:ttype}, signup_type = {int:stype},
					is_user_based = {int:ub}, enable_advstats = {int:advs}, enable_decerto = {int:dec},
					is_restricted = {int:restr}, min_members = {int:minm}, max_members = {int:maxm}, status = {int:status}';
				$params = array(
					'name' => $name, 'short_name' => $short_name, 'info' => $info,
					'bracket_size' => $bracket_size, 'ttype' => $tournament_type, 'stype' => $signup_type,
					'ub' => $is_user_based, 'advs' => $enable_advstats, 'dec' => $enable_decerto,
					'restr' => $is_restricted, 'minm' => $min_members, 'maxm' => $max_members,
					'status' => $status, 'id' => $id,
				);
				if (!empty($logo))
				{
					$set .= ', logo = {string:logo}';
					$params['logo'] = $logo;
				}
				$smcFunc['db_query']('', 'UPDATE {db_prefix}rivals_tournaments SET ' . $set . ' WHERE id_tournament = {int:id}', $params);
			}
			else
			{
				$smcFunc['db_insert']('',
					'{db_prefix}rivals_tournaments',
					array('name' => 'string', 'short_name' => 'string', 'info' => 'string',
						'bracket_size' => 'int', 'status' => 'int', 'tournament_type' => 'int',
						'signup_type' => 'int', 'is_user_based' => 'int', 'enable_advstats' => 'int',
						'enable_decerto' => 'int', 'is_restricted' => 'int', 'min_members' => 'int',
						'max_members' => 'int', 'logo' => 'string', 'created_at' => 'int'),
					array($name, $short_name, $info, $bracket_size, $status, $tournament_type,
						$signup_type, $is_user_based, $enable_advstats, $enable_decerto,
						$is_restricted, $min_members, $max_members, $logo, time()),
					array('id_tournament')
				);
			}
			redirectexit('action=admin;area=rivals;sa=tournaments');
		}
	}

	// Load all tournaments
	$context['rivals_tournaments'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT t.*,
			(SELECT COUNT(*) FROM {db_prefix}rivals_tournament_entries AS te WHERE te.id_tournament = t.id_tournament) AS entry_count
		FROM {db_prefix}rivals_tournaments AS t
		ORDER BY t.status ASC, t.created_at DESC',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_tournaments'][] = $row;
	$smcFunc['db_free_result']($request);
}

// ============================================================
// Seasons Admin
// ============================================================
function RivalsAdminSeasons()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['sub_template'] = 'rivals_admin_seasons';
	$context['page_title'] = $txt['rivals_admin'] . ' - ' . $txt['rivals_admin_seasons'];
	$context['rivals_errors'] = array();

	// Start new season
	if (isset($_POST['start_season']))
	{
		checkSession();

		$name = trim($smcFunc['htmlspecialchars']($_POST['season_name']));
		$id_ladder = (int) $_POST['id_ladder'];

		if (empty($name))
			$context['rivals_errors'][] = $txt['rivals_admin_error_name_required'];

		// Check no active season for this ladder
		$request = $smcFunc['db_query']('', '
			SELECT id_season FROM {db_prefix}rivals_seasons WHERE id_ladder = {int:ladder} AND status = 1',
			array('ladder' => $id_ladder)
		);
		if ($smcFunc['db_num_rows']($request) > 0)
			$context['rivals_errors'][] = $txt['rivals_admin_season_already_active'];
		$smcFunc['db_free_result']($request);

		if (empty($context['rivals_errors']))
		{
			$smcFunc['db_insert']('',
				'{db_prefix}rivals_seasons',
				array('id_ladder' => 'int', 'name' => 'string', 'status' => 'int'),
				array($id_ladder, $name, 1),
				array('id_season')
			);
			redirectexit('action=admin;area=rivals;sa=seasons');
		}
	}

	// End season
	if (isset($_GET['end_season']))
	{
		checkSession('get');
		$id_season = (int) $_GET['end_season'];

		// Get season info
		$request = $smcFunc['db_query']('', 'SELECT * FROM {db_prefix}rivals_seasons WHERE id_season = {int:id} AND status = 1',
			array('id' => $id_season));
		$season = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($season))
		{
			$id_ladder = $season['id_ladder'];

			// Archive current standings to season_data
			$request = $smcFunc['db_query']('', '
				SELECT * FROM {db_prefix}rivals_standings WHERE id_ladder = {int:ladder}',
				array('ladder' => $id_ladder)
			);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$smcFunc['db_insert']('',
					'{db_prefix}rivals_season_data',
					array('id_clan' => 'int', 'id_season' => 'int', 'wins' => 'int', 'losses' => 'int',
						'draws' => 'int', 'score' => 'int', 'streak' => 'string', 'current_rank' => 'int',
						'best_rank' => 'int', 'worst_rank' => 'int', 'last_rank' => 'int',
						'goals_for' => 'int', 'goals_against' => 'int', 'ratio' => 'string',
						'pwner_award' => 'int', 'is_frozen' => 'int'),
					array(
						$row['id_clan'] > 0 ? $row['id_clan'] : $row['id_member'],
						$id_season, $row['wins'], $row['losses'], $row['draws'], $row['score'],
						$row['streak'], $row['current_rank'], $row['best_rank'], $row['worst_rank'],
						$row['last_rank'], $row['goals_for'], $row['goals_against'], $row['ratio'],
						$row['pwner_award'], $row['is_frozen'],
					),
					array()
				);
			}
			$smcFunc['db_free_result']($request);

			// Reset standings
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}rivals_standings
				SET wins = 0, losses = 0, draws = 0, score = 1500, last_score = 0,
					current_rank = 0, last_rank = 0, best_rank = 0, worst_rank = 0,
					streak = {string:empty}, goals_for = 0, goals_against = 0,
					ratio = {string:zero}, pwner_award = 0, is_frozen = 0, frozen_time = 0
				WHERE id_ladder = {int:ladder}',
				array('ladder' => $id_ladder, 'empty' => '', 'zero' => '0')
			);

			// Re-rank sequentially
			$request = $smcFunc['db_query']('', '
				SELECT id_standing FROM {db_prefix}rivals_standings
				WHERE id_ladder = {int:ladder} ORDER BY id_standing',
				array('ladder' => $id_ladder)
			);
			$rank = 1;
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}rivals_standings SET current_rank = {int:rank}, best_rank = {int:rank}
					WHERE id_standing = {int:id}',
					array('rank' => $rank++, 'id' => $row['id_standing'])
				);
			}
			$smcFunc['db_free_result']($request);

			// Mark season ended
			$smcFunc['db_query']('', 'UPDATE {db_prefix}rivals_seasons SET status = 0 WHERE id_season = {int:id}',
				array('id' => $id_season));
		}

		redirectexit('action=admin;area=rivals;sa=seasons');
	}

	// Load ladders and their seasons
	$context['rivals_ladders_seasons'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT l.id_ladder, l.name AS ladder_name, s.id_season, s.name AS season_name, s.status
		FROM {db_prefix}rivals_ladders AS l
			LEFT JOIN {db_prefix}rivals_seasons AS s ON (s.id_ladder = l.id_ladder)
		WHERE l.id_parent > 0 OR l.id_parent = 0
		ORDER BY l.name, s.id_season DESC',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($context['rivals_ladders_seasons'][$row['id_ladder']]))
			$context['rivals_ladders_seasons'][$row['id_ladder']] = array(
				'name' => $row['ladder_name'],
				'id' => $row['id_ladder'],
				'active_season' => null,
				'seasons' => array(),
			);

		if (!empty($row['id_season']))
		{
			$context['rivals_ladders_seasons'][$row['id_ladder']]['seasons'][] = array(
				'id' => $row['id_season'],
				'name' => $row['season_name'],
				'status' => $row['status'],
			);
			if ($row['status'] == 1)
				$context['rivals_ladders_seasons'][$row['id_ladder']]['active_season'] = $row['id_season'];
		}
	}
	$smcFunc['db_free_result']($request);
}

// ============================================================
// MVP Definitions Admin
// ============================================================
function RivalsAdminMVP()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['sub_template'] = 'rivals_admin_mvp';
	$context['page_title'] = $txt['rivals_admin'] . ' - ' . $txt['rivals_admin_mvp'];
	$context['rivals_errors'] = array();

	// Delete
	if (isset($_GET['delete']))
	{
		checkSession('get');
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_mvp_definitions WHERE id_mvp = {int:id}', array('id' => (int) $_GET['delete']));
		redirectexit('action=admin;area=rivals;sa=mvp');
	}

	// Save
	if (isset($_POST['save_mvp']))
	{
		checkSession();

		$id = isset($_POST['id_mvp']) ? (int) $_POST['id_mvp'] : 0;
		$name = trim($smcFunc['htmlspecialchars']($_POST['mvp_name']));
		$description = $smcFunc['htmlspecialchars']($_POST['mvp_description']);
		$id_platform = (int) $_POST['id_platform'];
		$id_ladder = (int) $_POST['id_ladder'];

		if (empty($name))
			$context['rivals_errors'][] = $txt['rivals_admin_error_name_required'];

		if (empty($context['rivals_errors']))
		{
			if ($id > 0)
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}rivals_mvp_definitions
					SET name = {string:name}, description = {string:desc}, id_platform = {int:plat}, id_ladder = {int:lad}
					WHERE id_mvp = {int:id}',
					array('name' => $name, 'desc' => $description, 'plat' => $id_platform, 'lad' => $id_ladder, 'id' => $id)
				);
			}
			else
			{
				$smcFunc['db_insert']('',
					'{db_prefix}rivals_mvp_definitions',
					array('name' => 'string', 'description' => 'string', 'id_platform' => 'int', 'id_ladder' => 'int'),
					array($name, $description, $id_platform, $id_ladder),
					array('id_mvp')
				);
			}
			redirectexit('action=admin;area=rivals;sa=mvp');
		}
	}

	// Edit mode
	$context['rivals_editing'] = null;
	if (isset($_GET['edit']))
	{
		$request = $smcFunc['db_query']('', 'SELECT * FROM {db_prefix}rivals_mvp_definitions WHERE id_mvp = {int:id}', array('id' => (int) $_GET['edit']));
		$context['rivals_editing'] = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
	}

	// Load all
	$context['rivals_mvp_list'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT m.*, l.name AS ladder_name, p.name AS platform_name
		FROM {db_prefix}rivals_mvp_definitions AS m
			LEFT JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = m.id_ladder)
			LEFT JOIN {db_prefix}rivals_platforms AS p ON (p.id_platform = m.id_platform)
		ORDER BY m.name',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_mvp_list'][] = $row;
	$smcFunc['db_free_result']($request);

	// Load platforms + ladders for dropdowns
	$context['rivals_platforms'] = array();
	$request = $smcFunc['db_query']('', 'SELECT id_platform, name FROM {db_prefix}rivals_platforms ORDER BY name', array());
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_platforms'][$row['id_platform']] = $row['name'];
	$smcFunc['db_free_result']($request);

	$context['rivals_ladders_list'] = array();
	$request = $smcFunc['db_query']('', 'SELECT id_ladder, name FROM {db_prefix}rivals_ladders ORDER BY name', array());
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_ladders_list'][$row['id_ladder']] = $row['name'];
	$smcFunc['db_free_result']($request);
}

// ============================================================
// Game Modes Admin
// ============================================================
function RivalsAdminGameModes()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['sub_template'] = 'rivals_admin_gamemodes';
	$context['page_title'] = $txt['rivals_admin'] . ' - ' . $txt['rivals_admin_gamemodes'];
	$context['rivals_errors'] = array();

	// Delete
	if (isset($_GET['delete']))
	{
		checkSession('get');
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_game_modes WHERE id_mode = {int:id}', array('id' => (int) $_GET['delete']));
		redirectexit('action=admin;area=rivals;sa=gamemodes');
	}

	// Toggle active
	if (isset($_GET['toggle']))
	{
		checkSession('get');
		$smcFunc['db_query']('', 'UPDATE {db_prefix}rivals_game_modes SET is_active = 1 - is_active WHERE id_mode = {int:id}',
			array('id' => (int) $_GET['toggle']));
		redirectexit('action=admin;area=rivals;sa=gamemodes');
	}

	// Save
	if (isset($_POST['save_gamemode']))
	{
		checkSession();

		$id = isset($_POST['id_mode']) ? (int) $_POST['id_mode'] : 0;
		$game_name = trim($smcFunc['htmlspecialchars']($_POST['game_name']));
		$short_name = trim($smcFunc['htmlspecialchars']($_POST['short_name']));
		$mode_type = (int) $_POST['mode_type'];
		$parent_id = (int) $_POST['parent_id'];
		$is_cpc = isset($_POST['is_cpc']) ? 1 : 0;

		if (empty($game_name))
			$context['rivals_errors'][] = $txt['rivals_admin_error_name_required'];

		if (empty($context['rivals_errors']))
		{
			if ($id > 0)
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}rivals_game_modes
					SET game_name = {string:name}, short_name = {string:short}, mode_type = {int:type},
						parent_id = {int:parent}, is_cpc = {int:cpc}
					WHERE id_mode = {int:id}',
					array('name' => $game_name, 'short' => $short_name, 'type' => $mode_type,
						'parent' => $parent_id, 'cpc' => $is_cpc, 'id' => $id)
				);
			}
			else
			{
				$smcFunc['db_insert']('',
					'{db_prefix}rivals_game_modes',
					array('game_name' => 'string', 'short_name' => 'string', 'mode_type' => 'int',
						'parent_id' => 'int', 'is_cpc' => 'int', 'is_active' => 'int'),
					array($game_name, $short_name, $mode_type, $parent_id, $is_cpc, 1),
					array('id_mode')
				);
			}
			redirectexit('action=admin;area=rivals;sa=gamemodes');
		}
	}

	// Edit mode
	$context['rivals_editing'] = null;
	if (isset($_GET['edit']))
	{
		$request = $smcFunc['db_query']('', 'SELECT * FROM {db_prefix}rivals_game_modes WHERE id_mode = {int:id}', array('id' => (int) $_GET['edit']));
		$context['rivals_editing'] = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
	}

	// Load all modes grouped by type
	$context['rivals_game_modes'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT gm.*, pgm.game_name AS parent_name
		FROM {db_prefix}rivals_game_modes AS gm
			LEFT JOIN {db_prefix}rivals_game_modes AS pgm ON (pgm.id_mode = gm.parent_id)
		ORDER BY gm.mode_type, gm.game_name',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_game_modes'][] = $row;
	$smcFunc['db_free_result']($request);

	// Parent modes for dropdown
	$context['rivals_parent_modes'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT id_mode, game_name FROM {db_prefix}rivals_game_modes WHERE mode_type = 0 ORDER BY game_name',
		array()
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_parent_modes'][$row['id_mode']] = $row['game_name'];
	$smcFunc['db_free_result']($request);
}

// ============================================================
// Random Map Admin
// ============================================================
function RivalsAdminRandom()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['sub_template'] = 'rivals_admin_random';
	$context['page_title'] = $txt['rivals_admin'] . ' - ' . $txt['rivals_admin_random'];
	$context['rivals_errors'] = array();

	// Delete
	if (isset($_GET['delete']))
	{
		checkSession('get');
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_random_maps WHERE id_random = {int:id}', array('id' => (int) $_GET['delete']));
		redirectexit('action=admin;area=rivals;sa=random');
	}

	// Save
	if (isset($_POST['save_random']))
	{
		checkSession();

		$id = isset($_POST['id_random']) ? (int) $_POST['id_random'] : 0;
		$game_name = trim($smcFunc['htmlspecialchars']($_POST['game_name']));
		$short_name = trim($smcFunc['htmlspecialchars']($_POST['short_name']));

		if (empty($game_name))
			$context['rivals_errors'][] = $txt['rivals_admin_error_name_required'];

		// Image upload
		$image = '';
		if (!empty($_FILES['random_image']['name']) && $_FILES['random_image']['error'] == 0)
		{
			$result = _rivalsAdminUploadLogo('random_image', 'icons');
			if ($result !== false)
				$image = $result['filename'];
		}

		if (empty($context['rivals_errors']))
		{
			if ($id > 0)
			{
				$set = 'game_name = {string:name}, short_name = {string:short}';
				$params = array('name' => $game_name, 'short' => $short_name, 'id' => $id);
				if (!empty($image))
				{
					$set .= ', image = {string:image}';
					$params['image'] = $image;
				}
				$smcFunc['db_query']('', 'UPDATE {db_prefix}rivals_random_maps SET ' . $set . ', last_updated = {int:now} WHERE id_random = {int:id}',
					array_merge($params, array('now' => time())));
			}
			else
			{
				$smcFunc['db_insert']('',
					'{db_prefix}rivals_random_maps',
					array('game_name' => 'string', 'short_name' => 'string', 'image' => 'string', 'last_updated' => 'int'),
					array($game_name, $short_name, $image, time()),
					array('id_random')
				);
			}
			redirectexit('action=admin;area=rivals;sa=random');
		}
	}

	// Edit mode
	$context['rivals_editing'] = null;
	if (isset($_GET['edit']))
	{
		$request = $smcFunc['db_query']('', 'SELECT * FROM {db_prefix}rivals_random_maps WHERE id_random = {int:id}', array('id' => (int) $_GET['edit']));
		$context['rivals_editing'] = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
	}

	// Load all
	$context['rivals_random_maps'] = array();
	$request = $smcFunc['db_query']('', 'SELECT * FROM {db_prefix}rivals_random_maps ORDER BY game_name', array());
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_random_maps'][] = $row;
	$smcFunc['db_free_result']($request);
}

// ============================================================
// Matches Admin (moderation)
// ============================================================
function RivalsAdminMatches()
{
	global $context, $smcFunc, $txt, $scripturl;

	$context['sub_template'] = 'rivals_admin_matches';
	$context['page_title'] = $txt['rivals_admin'] . ' - ' . $txt['rivals_admin_matches'];

	// Delete match
	if (isset($_GET['delete']))
	{
		checkSession('get');
		$id = (int) $_GET['delete'];
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_matches WHERE id_match = {int:id}', array('id' => $id));
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_match_stats WHERE id_match = {int:id}', array('id' => $id));
		$smcFunc['db_query']('', 'DELETE FROM {db_prefix}rivals_match_comments WHERE id_match = {int:id}', array('id' => $id));
		redirectexit('action=admin;area=rivals;sa=matches');
	}

	// Resolve dispute
	if (isset($_GET['resolve']) && isset($_GET['winner']))
	{
		checkSession('get');
		$id_match = (int) $_GET['resolve'];
		$winner_id = (int) $_GET['winner'];

		$request = $smcFunc['db_query']('', 'SELECT * FROM {db_prefix}rivals_matches WHERE id_match = {int:id}',
			array('id' => $id_match));
		$match = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if (!empty($match) && $match['status'] == 2)
		{
			$loser_id = ($winner_id == $match['challenger_id']) ? $match['challengee_id'] : $match['challenger_id'];

			$smcFunc['db_query']('', '
				UPDATE {db_prefix}rivals_matches
				SET status = 1, winner_id = {int:winner}, is_contested = 0, completed_at = {int:now}
				WHERE id_match = {int:id}',
				array('winner' => $winner_id, 'now' => time(), 'id' => $id_match)
			);

			// Update standings if ranked
			if (empty($match['is_unranked']))
				rivals_update_standings($match['id_ladder'], $winner_id, $loser_id);
		}

		redirectexit('action=admin;area=rivals;sa=matches');
	}

	// Filter
	$status_filter = isset($_GET['status']) ? (int) $_GET['status'] : -1;
	$where = '1=1';
	$params = array();
	if ($status_filter >= 0 && $status_filter <= 2)
	{
		$where = 'm.status = {int:status}';
		$params['status'] = $status_filter;
	}

	// Pagination
	$per_page = 25;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	$request = $smcFunc['db_query']('', 'SELECT COUNT(*) FROM {db_prefix}rivals_matches AS m WHERE ' . $where, $params);
	list ($total) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex(
		$scripturl . '?action=admin;area=rivals;sa=matches' . ($status_filter >= 0 ? ';status=' . $status_filter : ''),
		$start, $total, $per_page, true
	);
	$context['rivals_match_filter'] = $status_filter;

	$context['rivals_matches'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT m.*, l.name AS ladder_name
		FROM {db_prefix}rivals_matches AS m
			LEFT JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = m.id_ladder)
		WHERE ' . $where . '
		ORDER BY m.created_at DESC
		LIMIT {int:start}, {int:limit}',
		array_merge($params, array('start' => $start, 'limit' => $per_page))
	);

	$entity_ids = array();
	$matches_raw = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$matches_raw[] = $row;
		$entity_ids[] = $row['challenger_id'];
		$entity_ids[] = $row['challengee_id'];
	}
	$smcFunc['db_free_result']($request);

	// Resolve entity names (check both clans and members)
	$entity_names = array();
	$entity_ids = array_unique(array_filter($entity_ids));
	if (!empty($entity_ids))
	{
		$request = $smcFunc['db_query']('', 'SELECT id_clan, name FROM {db_prefix}rivals_clans WHERE id_clan IN ({array_int:ids})',
			array('ids' => $entity_ids));
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$entity_names[$row['id_clan']] = $row['name'];
		$smcFunc['db_free_result']($request);

		$request = $smcFunc['db_query']('', 'SELECT id_member, real_name FROM {db_prefix}members WHERE id_member IN ({array_int:ids})',
			array('ids' => $entity_ids));
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (!isset($entity_names[$row['id_member']]))
				$entity_names[$row['id_member']] = $row['real_name'];
		}
		$smcFunc['db_free_result']($request);
	}

	foreach ($matches_raw as $row)
	{
		$row['challenger_name'] = isset($entity_names[$row['challenger_id']]) ? $entity_names[$row['challenger_id']] : '#' . $row['challenger_id'];
		$row['challengee_name'] = isset($entity_names[$row['challengee_id']]) ? $entity_names[$row['challengee_id']] : '#' . $row['challengee_id'];
		$context['rivals_matches'][] = $row;
	}
}

// ============================================================
// Shared helper: Logo upload
// ============================================================
function _rivalsAdminUploadLogo($field_name, $subdir)
{
	global $settings, $modSettings;

	$max_size = !empty($modSettings['rivals_logo_max_size']) ? (int) $modSettings['rivals_logo_max_size'] : 163840;
	$max_w = !empty($modSettings['rivals_logo_max_width']) ? (int) $modSettings['rivals_logo_max_width'] : 500;
	$max_h = !empty($modSettings['rivals_logo_max_height']) ? (int) $modSettings['rivals_logo_max_height'] : 500;

	if ($_FILES[$field_name]['size'] > $max_size)
		return false;

	$allowed = array('jpg', 'jpeg', 'png', 'gif');
	$ext = strtolower(pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION));
	if (!in_array($ext, $allowed))
		return false;

	$upload_dir = $settings['default_theme_dir'] . '/images/rivals/' . $subdir;

	// Ensure directory exists
	if (!is_dir($upload_dir))
		@mkdir($upload_dir, 0755, true);

	// Generate unique filename
	$filename = 'rivals_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
	$dest = $upload_dir . '/' . $filename;

	if (!move_uploaded_file($_FILES[$field_name]['tmp_name'], $dest))
		return false;

	@chmod($dest, 0644);

	$size = @getimagesize($dest);
	$width = !empty($size[0]) ? $size[0] : 0;
	$height = !empty($size[1]) ? $size[1] : 0;

	return array(
		'filename' => $filename,
		'width' => $width,
		'height' => $height,
	);
}
?>