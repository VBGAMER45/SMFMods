<?php
/**
 * SMF Rivals - Hook Callback Functions
 * All integrate_* hook implementations.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Register the rivals action.
 * Hook: integrate_actions
 */
function rivals_actions(&$actionArray)
{
	$actionArray['rivals'] = array('Rivals/Rivals.php', 'Rivals');
}

/**
 * Add Rivals button to main menu.
 * Hook: integrate_menu_buttons
 */
function rivals_menu_buttons(&$menu_buttons)
{
	global $txt, $scripturl, $modSettings;

	if (empty($modSettings['rivals_enabled']))
		return;

	if (loadLanguage('Rivals') == false)
		loadLanguage('Rivals', 'english');

	// Insert before 'mlist' (member list)
	$insert_after = 'home';
	$counter = 0;
	$new_buttons = array();

	foreach ($menu_buttons as $key => $button)
	{
		$new_buttons[$key] = $button;

		if ($key == $insert_after)
		{
			$new_buttons['rivals'] = array(
				'title' => isset($txt['rivals_menu']) ? $txt['rivals_menu'] : 'Rivals',
				'href' => $scripturl . '?action=rivals',
				'show' => allowedTo('rivals_view'),
				'icon' => 'reports',
				'sub_buttons' => array(
					'platforms' => array(
						'title' => isset($txt['rivals_platforms']) ? $txt['rivals_platforms'] : 'Platforms',
						'href' => $scripturl . '?action=rivals;sa=platforms',
						'show' => true,
					),
					'clans' => array(
						'title' => isset($txt['rivals_clans']) ? $txt['rivals_clans'] : 'Clans',
						'href' => $scripturl . '?action=rivals;sa=clans',
						'show' => true,
					),
					'tournaments' => array(
						'title' => isset($txt['rivals_tournaments']) ? $txt['rivals_tournaments'] : 'Tournaments',
						'href' => $scripturl . '?action=rivals;sa=tournaments',
						'show' => true,
					),
					'matches' => array(
						'title' => isset($txt['rivals_matches']) ? $txt['rivals_matches'] : 'Matches',
						'href' => $scripturl . '?action=rivals;sa=matches',
						'show' => true,
					),
				),
			);
		}
	}

	$menu_buttons = $new_buttons;
}

/**
 * Mark rivals as the current action for menu highlighting.
 * Hook: integrate_current_action
 */
function rivals_current_action(&$current_action)
{
	if (isset($_GET['action']) && $_GET['action'] === 'rivals')
		$current_action = 'rivals';
}

/**
 * Register admin areas for Rivals.
 * Hook: integrate_admin_areas
 */
function rivals_admin_areas(&$admin_areas)
{
	global $txt, $scripturl;

	if (loadLanguage('Rivals') == false)
		loadLanguage('Rivals', 'english');

	$admin_areas['config']['areas']['rivals'] = array(
		'label' => isset($txt['rivals_admin']) ? $txt['rivals_admin'] : 'Rivals',
		'file' => 'Rivals/RivalsAdmin.php',
		'function' => 'RivalsAdmin',
		'custom_url' => $scripturl . '?action=admin;area=rivals',
		'icon' => 'reports',
		'permission' => array('rivals_admin'),
		'subsections' => array(
			'settings' => array(isset($txt['rivals_admin_settings']) ? $txt['rivals_admin_settings'] : 'Settings'),
			'platforms' => array(isset($txt['rivals_admin_platforms']) ? $txt['rivals_admin_platforms'] : 'Platforms'),
			'ladders' => array(isset($txt['rivals_admin_ladders']) ? $txt['rivals_admin_ladders'] : 'Ladders'),
			'clans' => array(isset($txt['rivals_admin_clans']) ? $txt['rivals_admin_clans'] : 'Clans'),
			'tournaments' => array(isset($txt['rivals_admin_tournaments']) ? $txt['rivals_admin_tournaments'] : 'Tournaments'),
			'seasons' => array(isset($txt['rivals_admin_seasons']) ? $txt['rivals_admin_seasons'] : 'Seasons'),
			'mvp' => array(isset($txt['rivals_admin_mvp']) ? $txt['rivals_admin_mvp'] : 'MVP Definitions'),
			'gamemodes' => array(isset($txt['rivals_admin_gamemodes']) ? $txt['rivals_admin_gamemodes'] : 'Game Modes'),
			'random' => array(isset($txt['rivals_admin_random']) ? $txt['rivals_admin_random'] : 'Random Map'),
			'matches' => array(isset($txt['rivals_admin_matches']) ? $txt['rivals_admin_matches'] : 'Matches'),
		),
	);
}

/**
 * Register Rivals permissions.
 * Hook: integrate_load_permissions
 */
function rivals_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	if (loadLanguage('Rivals') == false)
		loadLanguage('Rivals', 'english');

	$permissionGroups['membergroup']['simple'][] = 'rivals';
	$permissionGroups['membergroup']['classic'][] = 'rivals';
	$leftPermissionGroups[] = 'rivals';

	$permissionList['membergroup'] += array(
		'rivals_view' => array(false, 'rivals', 'rivals'),
		'rivals_manage_clan' => array(false, 'rivals', 'rivals'),
		'rivals_challenge' => array(false, 'rivals', 'rivals'),
		'rivals_report' => array(false, 'rivals', 'rivals'),
		'rivals_moderate' => array(false, 'rivals', 'rivals'),
		'rivals_admin' => array(false, 'rivals', 'rivals'),
	);
}

/**
 * Prevent guests from having certain permissions.
 * Hook: integrate_load_illegal_guest_permissions
 */
function rivals_illegal_guest_permissions()
{
	global $context;

	$context['non_guest_permissions'] = array_merge(
		$context['non_guest_permissions'],
		array(
			'rivals_manage_clan',
			'rivals_challenge',
			'rivals_report',
			'rivals_moderate',
			'rivals_admin',
		)
	);
}

/**
 * Load CSS/JS for Rivals pages.
 * Hook: integrate_load_theme
 */
function rivals_load_theme()
{
	global $context, $modSettings, $settings;

	if (empty($modSettings['rivals_enabled']))
		return;

	// Always load CSS so menu styling works
	$context['html_headers'] .= '
	<link rel="stylesheet" href="' . $settings['default_theme_url'] . '/css/rivals.css" />';

	// Only load JS on rivals pages
	if (isset($_GET['action']) && $_GET['action'] === 'rivals')
	{
		$context['html_headers'] .= '
	<script src="' . $settings['default_theme_url'] . '/scripts/rivals.js"></script>';
	}
}

/**
 * Register alert types for Rivals.
 * Hook: integrate_alert_types
 */
function rivals_alert_types(&$alert_types, &$group_options)
{
	if (loadLanguage('Rivals') == false)
		loadLanguage('Rivals', 'english');

	$alert_types['rivals'] = array(
		'rivals_challenge_received' => array('alert' => 'yes', 'email' => 'yes'),
		'rivals_challenge_accepted' => array('alert' => 'yes', 'email' => 'never'),
		'rivals_match_reported' => array('alert' => 'yes', 'email' => 'yes'),
		'rivals_match_confirmed' => array('alert' => 'yes', 'email' => 'never'),
		'rivals_match_disputed' => array('alert' => 'yes', 'email' => 'yes'),
		'rivals_clan_invite' => array('alert' => 'yes', 'email' => 'yes'),
		'rivals_clan_join_request' => array('alert' => 'yes', 'email' => 'never'),
		'rivals_clan_join_approved' => array('alert' => 'yes', 'email' => 'never'),
		'rivals_tournament_starting' => array('alert' => 'yes', 'email' => 'yes'),
		'rivals_tournament_match' => array('alert' => 'yes', 'email' => 'never'),
	);
}

/**
 * Format Rivals alerts for display.
 * Hook: integrate_fetch_alerts
 */
function rivals_fetch_alerts(&$alerts, &$formats)
{
	global $scripturl, $txt;

	if (loadLanguage('Rivals') == false)
		loadLanguage('Rivals', 'english');

	foreach ($alerts as &$alert)
	{
		if ($alert['content_type'] !== 'rivals')
			continue;

		$sender_name = !empty($alert['extra']['sender_name']) ? '<strong>' . $alert['extra']['sender_name'] . '</strong>' : '';
		$content_name = !empty($alert['extra']['content_name']) ? $alert['extra']['content_name'] : '';

		switch ($alert['content_action'])
		{
			case 'challenge_received':
				$link = $scripturl . '?action=rivals;sa=challenges';
				if (!empty($alert['show_links']) && !empty($content_name))
					$item = '<a href="' . $link . '">' . $content_name . '</a>';
				else
					$item = '<strong>' . $content_name . '</strong>';

				if (isset($txt['rivals_alert_challenge_received']))
					$alert['text'] = str_replace(array('{sender}', '{ladder}'), array($sender_name, $item), $txt['rivals_alert_challenge_received']);
				$alert['extra']['content_link'] = $link;
				break;

			case 'challenge_accepted':
				$link = $scripturl . '?action=rivals;sa=mymatch;match=' . $alert['content_id'];
				if (isset($txt['rivals_alert_challenge_accepted']))
					$alert['text'] = str_replace('{sender}', $sender_name, $txt['rivals_alert_challenge_accepted']);
				$alert['extra']['content_link'] = $link;
				break;

			case 'match_reported':
				$link = $scripturl . '?action=rivals;sa=confirmmatch;match=' . $alert['content_id'];
				if (!empty($alert['show_links']))
					$item = '<a href="' . $link . '">' . $content_name . '</a>';
				else
					$item = '<strong>' . $content_name . '</strong>';

				if (isset($txt['rivals_alert_match_reported']))
					$alert['text'] = str_replace(array('{sender}', '{match}'), array($sender_name, $item), $txt['rivals_alert_match_reported']);
				$alert['extra']['content_link'] = $link;
				break;

			case 'match_confirmed':
				$link = $scripturl . '?action=rivals;sa=mymatch;match=' . $alert['content_id'];
				if (isset($txt['rivals_alert_match_confirmed']))
					$alert['text'] = str_replace('{sender}', $sender_name, $txt['rivals_alert_match_confirmed']);
				$alert['extra']['content_link'] = $link;
				break;

			case 'match_disputed':
				$link = $scripturl . '?action=rivals;sa=mymatch;match=' . $alert['content_id'];
				if (isset($txt['rivals_alert_match_disputed']))
					$alert['text'] = str_replace('{sender}', $sender_name, $txt['rivals_alert_match_disputed']);
				$alert['extra']['content_link'] = $link;
				break;

			case 'clan_invite':
				$clan_id = !empty($alert['extra']['clan_id']) ? $alert['extra']['clan_id'] : 0;
				$link = $scripturl . '?action=rivals;sa=clan;id=' . $clan_id;
				if (!empty($alert['show_links']) && !empty($content_name))
					$item = '<a href="' . $link . '">' . $content_name . '</a>';
				else
					$item = '<strong>' . $content_name . '</strong>';

				if (isset($txt['rivals_alert_clan_invite']))
					$alert['text'] = str_replace(array('{sender}', '{clan}'), array($sender_name, $item), $txt['rivals_alert_clan_invite']);
				$alert['extra']['content_link'] = $link;
				break;

			case 'clan_join_request':
				$link = $scripturl . '?action=rivals;sa=pending';
				if (isset($txt['rivals_alert_clan_join_request']))
					$alert['text'] = str_replace('{sender}', $sender_name, $txt['rivals_alert_clan_join_request']);
				$alert['extra']['content_link'] = $link;
				break;

			case 'clan_join_approved':
				$clan_id = !empty($alert['extra']['clan_id']) ? $alert['extra']['clan_id'] : 0;
				$link = $scripturl . '?action=rivals;sa=clan;id=' . $clan_id;
				if (!empty($alert['show_links']) && !empty($content_name))
					$item = '<a href="' . $link . '">' . $content_name . '</a>';
				else
					$item = '<strong>' . $content_name . '</strong>';

				if (isset($txt['rivals_alert_clan_join_approved']))
					$alert['text'] = str_replace('{clan}', $item, $txt['rivals_alert_clan_join_approved']);
				$alert['extra']['content_link'] = $link;
				break;

			case 'tournament_starting':
				$tourn_id = !empty($alert['extra']['tournament_id']) ? $alert['extra']['tournament_id'] : $alert['content_id'];
				$link = $scripturl . '?action=rivals;sa=brackets;tournament=' . $tourn_id;
				if (!empty($alert['show_links']) && !empty($content_name))
					$item = '<a href="' . $link . '">' . $content_name . '</a>';
				else
					$item = '<strong>' . $content_name . '</strong>';

				if (isset($txt['rivals_alert_tournament_starting']))
					$alert['text'] = str_replace('{tournament}', $item, $txt['rivals_alert_tournament_starting']);
				$alert['extra']['content_link'] = $link;
				break;

			case 'tournament_match':
				$tourn_id = !empty($alert['extra']['tournament_id']) ? $alert['extra']['tournament_id'] : $alert['content_id'];
				$link = $scripturl . '?action=rivals;sa=brackets;tournament=' . $tourn_id;
				if (!empty($alert['show_links']) && !empty($content_name))
					$item = '<a href="' . $link . '">' . $content_name . '</a>';
				else
					$item = '<strong>' . $content_name . '</strong>';

				if (isset($txt['rivals_alert_tournament_match']))
					$alert['text'] = str_replace(array('{sender}', '{tournament}'), array($sender_name, $item), $txt['rivals_alert_tournament_match']);
				$alert['extra']['content_link'] = $link;
				break;
		}
	}
}

/**
 * Add Rivals section to user profiles.
 * Hook: integrate_profile_areas
 */
function rivals_profile_areas(&$profile_areas)
{
	global $txt, $modSettings;

	if (empty($modSettings['rivals_enabled']))
		return;

	if (loadLanguage('Rivals') == false)
		loadLanguage('Rivals', 'english');

	$profile_areas['info']['areas']['rivals'] = array(
		'label' => isset($txt['rivals_profile']) ? $txt['rivals_profile'] : 'Rivals',
		'file' => 'Rivals/RivalsProfile.php',
		'function' => 'RivalsProfile',
		'permission' => array(
			'own' => 'rivals_view',
			'any' => 'rivals_view',
		),
	);
}

/**
 * Add Rivals credit to the credits page.
 * Hook: integrate_credits
 */
function rivals_credits()
{
	global $context;

	$context['copyrights']['mods'][] = '<a href="https://github.com/smfrivals">SMF Rivals</a> v1.0.0';
}

/**
 * Exclude rivals actions from topic statistics.
 * Hook: integrate_pre_log_stats
 */
function rivals_pre_log_stats(&$no_stat_actions)
{
	$no_stat_actions[] = 'rivals';
}
?>