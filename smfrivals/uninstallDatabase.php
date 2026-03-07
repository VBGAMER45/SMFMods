<?php
/**
 * SMF Rivals - Database Removal
 * Drops all Rivals tables and removes member columns.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

db_extend('packages');

// Drop all Rivals tables
$tables = array(
	'{db_prefix}rivals_platforms',
	'{db_prefix}rivals_ladders',
	'{db_prefix}rivals_ladder_rules',
	'{db_prefix}rivals_clans',
	'{db_prefix}rivals_clan_members',
	'{db_prefix}rivals_standings',
	'{db_prefix}rivals_matches',
	'{db_prefix}rivals_challenges',
	'{db_prefix}rivals_match_stats',
	'{db_prefix}rivals_user_ladder_stats',
	'{db_prefix}rivals_match_comments',
	'{db_prefix}rivals_clan_messages',
	'{db_prefix}rivals_tournaments',
	'{db_prefix}rivals_tournament_entries',
	'{db_prefix}rivals_tournament_matches',
	'{db_prefix}rivals_tournament_player_stats',
	'{db_prefix}rivals_tournament_reports',
	'{db_prefix}rivals_tournament_decerto',
	'{db_prefix}rivals_seasons',
	'{db_prefix}rivals_season_data',
	'{db_prefix}rivals_game_modes',
	'{db_prefix}rivals_mvp_definitions',
	'{db_prefix}rivals_rosters',
	'{db_prefix}rivals_matchfinder',
	'{db_prefix}rivals_random_maps',
	'{db_prefix}rivals_challenge_rights',
);

foreach ($tables as $table)
{
	$smcFunc['db_drop_table']($table);
}

// Remove profile columns from members table
$member_columns = array(
	'rivals_clan_session',
	'rivals_gamer_name',
	'rivals_mvp_count',
	'rivals_exp',
	'rivals_ladder_level',
	'rivals_ladder_value',
	'rivals_round_wins',
	'rivals_round_losses',
	'rivals_chicken_count',
	'rivals_pwner_count',
	'rivals_rep_value',
	'rivals_rep_time',
);

foreach ($member_columns as $column)
{
	$smcFunc['db_remove_column']('{db_prefix}members', $column);
}
?>