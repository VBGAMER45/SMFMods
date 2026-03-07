<?php
/**
 * SMF Rivals - Profile Integration
 * Gamer tag, stats, clan display in user profiles.
 * Supports editing gamer name for own profile.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Profile section for Rivals.
 * Called from integrate_profile_areas hook.
 */
function RivalsProfile($memID)
{
	global $context, $smcFunc, $txt, $scripturl, $sourcedir, $user_info;

	if (loadLanguage('Rivals') == false)
		loadLanguage('Rivals', 'english');

	require_once($sourcedir . '/Rivals/RivalsLib.php');

	$context['page_title'] = $txt['rivals_profile'];
	$context['sub_template'] = 'rivals_profile';

	loadTemplate('Rivals');

	// Can this user edit the profile? (own profile or admin)
	$context['rivals_can_edit'] = ($memID == $user_info['id'] || allowedTo('rivals_admin'));

	// Handle gamer name save
	if (isset($_POST['save_gamer_name']) && $context['rivals_can_edit'])
	{
		checkSession();

		$gamer_name = trim($smcFunc['htmlspecialchars']($_POST['rivals_gamer_name']));
		updateMemberData($memID, array('rivals_gamer_name' => $gamer_name));

		redirectexit('action=profile;u=' . $memID . ';area=rivals');
	}

	// Get member's rivals data
	$request = $smcFunc['db_query']('', '
		SELECT rivals_gamer_name, rivals_clan_session, rivals_mvp_count,
			rivals_exp, rivals_ladder_level, rivals_ladder_value,
			rivals_round_wins, rivals_round_losses, rivals_chicken_count,
			rivals_pwner_count, rivals_rep_value
		FROM {db_prefix}members
		WHERE id_member = {int:member}',
		array('member' => $memID)
	);
	$context['rivals_member'] = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Get clan info if they have one
	$context['rivals_clan'] = null;
	if (!empty($context['rivals_member']['rivals_clan_session']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT c.id_clan, c.name, c.logo, cm.role
			FROM {db_prefix}rivals_clans AS c
				INNER JOIN {db_prefix}rivals_clan_members AS cm ON (cm.id_clan = c.id_clan)
			WHERE cm.id_member = {int:member}
				AND cm.is_pending = 0
				AND c.id_clan = {int:clan}',
			array(
				'member' => $memID,
				'clan' => (int) $context['rivals_member']['rivals_clan_session'],
			)
		);
		$context['rivals_clan'] = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
	}

	// Get ladder standings for this member
	$context['rivals_standings'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT s.id_ladder, s.score, s.wins, s.losses, s.draws, s.current_rank,
			l.name AS ladder_name
		FROM {db_prefix}rivals_standings AS s
			INNER JOIN {db_prefix}rivals_ladders AS l ON (l.id_ladder = s.id_ladder)
		WHERE s.id_member = {int:member}
		ORDER BY l.name',
		array('member' => $memID)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_standings'][] = $row;
	$smcFunc['db_free_result']($request);

	// Get tournament history
	$context['rivals_tournament_history'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT DISTINCT t.id_tournament, t.name, t.status
		FROM {db_prefix}rivals_tournament_player_stats AS tps
			INNER JOIN {db_prefix}rivals_tournaments AS t ON (t.id_tournament = tps.id_tournament)
		WHERE tps.id_member = {int:member}
		ORDER BY t.created_at DESC
		LIMIT 10',
		array('member' => $memID)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['rivals_tournament_history'][] = $row;
	$smcFunc['db_free_result']($request);
}
?>