<?php
/**
 * SMF Rivals - Database Installation
 * Creates all 26 tables + member profile columns
 *
 * @package SMFRivals
 * @version 1.0.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

db_extend('packages');

$tables = array();

// Table 1: rivals_platforms - Game platforms (PC, Xbox, PlayStation, etc.)
$tables[] = array(
	'table_name' => '{db_prefix}rivals_platforms',
	'columns' => array(
		array('name' => 'id_platform', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'logo', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'logo_width', 'type' => 'smallint', 'size' => 5, 'default' => 0),
		array('name' => 'logo_height', 'type' => 'smallint', 'size' => 5, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_platform')),
	),
);

// Table 2: rivals_ladders - Ladder hierarchy (platform > parent > sub-ladder)
$tables[] = array(
	'table_name' => '{db_prefix}rivals_ladders',
	'columns' => array(
		array('name' => 'id_ladder', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_parent', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_platform', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'short_name', 'type' => 'varchar', 'size' => 20, 'default' => ''),
		array('name' => 'description', 'type' => 'text'),
		array('name' => 'rules', 'type' => 'text'),
		array('name' => 'logo', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'logo_width', 'type' => 'smallint', 'size' => 5, 'default' => 0),
		array('name' => 'logo_height', 'type' => 'smallint', 'size' => 5, 'default' => 0),
		array('name' => 'display_order', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'sub_order', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'is_locked', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'is_1v1', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'ladder_style', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'ranking_system', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'win_system', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'enable_mvp', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'enable_advstats', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'limit_level', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'id_moderator', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_ladder')),
		array('type' => 'index', 'columns' => array('id_parent')),
		array('type' => 'index', 'columns' => array('id_platform')),
	),
);

// Table 3: rivals_ladder_rules - Extended rules per ladder
$tables[] = array(
	'table_name' => '{db_prefix}rivals_ladder_rules',
	'columns' => array(
		array('name' => 'id_rule', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_ladder', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_platform', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'requirements', 'type' => 'text'),
		array('name' => 'general_rules', 'type' => 'text'),
		array('name' => 'configuration', 'type' => 'text'),
		array('name' => 'prohibitions', 'type' => 'text'),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_rule')),
		array('type' => 'index', 'columns' => array('id_ladder')),
	),
);

// Table 4: rivals_clans - Clan/group definitions
$tables[] = array(
	'table_name' => '{db_prefix}rivals_clans',
	'columns' => array(
		array('name' => 'id_clan', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'description', 'type' => 'text'),
		array('name' => 'website', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'logo', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'logo_ext', 'type' => 'varchar', 'size' => 5, 'default' => ''),
		array('name' => 'logo_width', 'type' => 'smallint', 'size' => 5, 'default' => 0),
		array('name' => 'logo_height', 'type' => 'smallint', 'size' => 5, 'default' => 0),
		array('name' => 'total_wins', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'total_losses', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'total_draws', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'clan_level', 'type' => 'tinyint', 'size' => 3, 'default' => 0),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'is_closed', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'favorite_maps', 'type' => 'text'),
		array('name' => 'favorite_teams', 'type' => 'text'),
		array('name' => 'guid', 'type' => 'varchar', 'size' => 8, 'default' => ''),
		array('name' => 'uac', 'type' => 'varchar', 'size' => 6, 'default' => ''),
		array('name' => 'achievement_10streak', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'achievement_ladderwin', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'chicken_count', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'pwner_count', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'rep_value', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'rep_time', 'type' => 'int', 'size' => 10, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_clan')),
	),
);

// Table 5: rivals_clan_members - User-to-clan membership with roles and stats
$tables[] = array(
	'table_name' => '{db_prefix}rivals_clan_members',
	'columns' => array(
		array('name' => 'id_clan', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'role', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'is_pending', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'mvp_count', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'kills', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'deaths', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'assists', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'goals_for', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'goals_against', 'type' => 'int', 'size' => 10, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_clan', 'id_member')),
		array('type' => 'index', 'columns' => array('id_member')),
	),
);

// Table 6: rivals_standings - Unified standings for clans and users on ladders
$tables[] = array(
	'table_name' => '{db_prefix}rivals_standings',
	'columns' => array(
		array('name' => 'id_standing', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_ladder', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_clan', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'wins', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'losses', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'draws', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'score', 'type' => 'int', 'size' => 10, 'default' => 1500),
		array('name' => 'last_score', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'current_rank', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'last_rank', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'best_rank', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'worst_rank', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'streak', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'goals_for', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'goals_against', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'ratio', 'type' => 'varchar', 'size' => 20, 'default' => '0'),
		array('name' => 'pwner_award', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'is_frozen', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'frozen_time', 'type' => 'int', 'size' => 10, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_standing')),
		array('type' => 'index', 'columns' => array('id_ladder', 'id_clan')),
		array('type' => 'index', 'columns' => array('id_ladder', 'id_member')),
		array('type' => 'index', 'columns' => array('id_ladder', 'score')),
	),
);

// Table 7: rivals_matches - Unified match records (clan and 1v1)
$tables[] = array(
	'table_name' => '{db_prefix}rivals_matches',
	'columns' => array(
		array('name' => 'id_match', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_ladder', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'match_type', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		// Challenger side
		array('name' => 'challenger_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'challenger_ip', 'type' => 'varchar', 'size' => 45, 'default' => ''),
		array('name' => 'challenger_score', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'challenger_team', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		// Challengee side
		array('name' => 'challengee_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'challengee_ip', 'type' => 'varchar', 'size' => 45, 'default' => ''),
		array('name' => 'challengee_score', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'challengee_team', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		// Per-round scores (3 rounds for Decerto/CPC)
		array('name' => 'round1_map', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'round1_mode', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'round1_score_challenger', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'round1_score_challengee', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'round2_map', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'round2_mode', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'round2_score_challenger', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'round2_score_challengee', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'round3_map', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'round3_mode', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'round3_score_challenger', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'round3_score_challengee', 'type' => 'int', 'size' => 10, 'default' => 0),
		// Result
		array('name' => 'winner_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'is_unranked', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'status', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'details', 'type' => 'text'),
		// Workflow
		array('name' => 'id_reporter', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_confirmer', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'is_contested', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'challenger_feedback', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'challengee_feedback', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		// MVPs
		array('name' => 'mvp1', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'mvp2', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'mvp3', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		// Timestamps
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'reported_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'completed_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_match')),
		array('type' => 'index', 'columns' => array('id_ladder')),
		array('type' => 'index', 'columns' => array('challenger_id')),
		array('type' => 'index', 'columns' => array('challengee_id')),
		array('type' => 'index', 'columns' => array('status')),
	),
);

// Table 8: rivals_challenges - Pending match challenges
$tables[] = array(
	'table_name' => '{db_prefix}rivals_challenges',
	'columns' => array(
		array('name' => 'id_challenge', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_ladder', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'challenger_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'challengee_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'challenger_ip', 'type' => 'varchar', 'size' => 45, 'default' => ''),
		array('name' => 'is_unranked', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'is_1v1', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'details', 'type' => 'text'),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_challenge')),
		array('type' => 'index', 'columns' => array('challengee_id')),
		array('type' => 'index', 'columns' => array('id_ladder')),
	),
);

// Table 9: rivals_match_stats - Per-match player statistics
$tables[] = array(
	'table_name' => '{db_prefix}rivals_match_stats',
	'columns' => array(
		array('name' => 'id_stat', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_match', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_ladder', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'kills', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'deaths', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'assists', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'goals_for', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'goals_against', 'type' => 'int', 'size' => 10, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_stat')),
		array('type' => 'index', 'columns' => array('id_match')),
		array('type' => 'index', 'columns' => array('id_member')),
		array('type' => 'index', 'columns' => array('id_ladder', 'id_member')),
	),
);

// Table 10: rivals_user_ladder_stats - Aggregated user stats per ladder
$tables[] = array(
	'table_name' => '{db_prefix}rivals_user_ladder_stats',
	'columns' => array(
		array('name' => 'id_ladder', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'ranking', 'type' => 'varchar', 'size' => 60, 'default' => ''),
		array('name' => 'kills', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'deaths', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'assists', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'goals_for', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'goals_against', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'mvp_count', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'matches_played', 'type' => 'int', 'size' => 10, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_ladder', 'id_member')),
		array('type' => 'index', 'columns' => array('id_member')),
	),
);

// Table 11: rivals_match_comments - Match discussion/comments
$tables[] = array(
	'table_name' => '{db_prefix}rivals_match_comments',
	'columns' => array(
		array('name' => 'id_comment', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_match', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_clan', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'comment_type', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'tournament_round', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'tournament_position', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'body', 'type' => 'text'),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_comment')),
		array('type' => 'index', 'columns' => array('id_match')),
	),
);

// Table 12: rivals_clan_messages - Clan internal message board
$tables[] = array(
	'table_name' => '{db_prefix}rivals_clan_messages',
	'columns' => array(
		array('name' => 'id_message', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_clan', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'body', 'type' => 'text'),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_message')),
		array('type' => 'index', 'columns' => array('id_clan')),
	),
);

// Table 13: rivals_tournaments - Tournament definitions
$tables[] = array(
	'table_name' => '{db_prefix}rivals_tournaments',
	'columns' => array(
		array('name' => 'id_tournament', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'short_name', 'type' => 'varchar', 'size' => 50, 'default' => ''),
		array('name' => 'logo', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'info', 'type' => 'text'),
		array('name' => 'bracket_size', 'type' => 'int', 'size' => 10, 'default' => 8),
		array('name' => 'status', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'tournament_type', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'signup_type', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'is_user_based', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'invite_list', 'type' => 'text'),
		array('name' => 'finished_groups', 'type' => 'text'),
		array('name' => 'enable_advstats', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'enable_decerto', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'is_restricted', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'min_members', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'max_members', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'league_cycles', 'type' => 'int', 'size' => 4, 'default' => 0),
		array('name' => 'start_date', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_tournament')),
		array('type' => 'index', 'columns' => array('status')),
	),
);

// Table 14: rivals_tournament_entries - Clans/users signed up for tournaments
$tables[] = array(
	'table_name' => '{db_prefix}rivals_tournament_entries',
	'columns' => array(
		array('name' => 'id_entry', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_tournament', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_clan', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_roster', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'bracket_round', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'position', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'position_temp', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'is_loser', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'match_uid', 'type' => 'varchar', 'size' => 100, 'default' => ''),
		array('name' => 'id_reporter', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'loser_confirmed', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'reputation', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_entry')),
		array('type' => 'index', 'columns' => array('id_tournament')),
		array('type' => 'index', 'columns' => array('id_tournament', 'id_clan')),
	),
);

// Table 15: rivals_tournament_matches - Tournament match results
$tables[] = array(
	'table_name' => '{db_prefix}rivals_tournament_matches',
	'columns' => array(
		array('name' => 'match_uid', 'type' => 'varchar', 'size' => 100, 'default' => ''),
		array('name' => 'id_tournament', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'team1_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'team2_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'team1_score', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'team2_score', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'winner_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'team1_confirmed', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'team2_confirmed', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'first_home', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'home_score1', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'home_score2', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'has_dispute', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'mvp1', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'mvp2', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'mvp3', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('match_uid')),
		array('type' => 'index', 'columns' => array('id_tournament')),
	),
);

// Table 16: rivals_tournament_player_stats - Per-tournament player statistics
$tables[] = array(
	'table_name' => '{db_prefix}rivals_tournament_player_stats',
	'columns' => array(
		array('name' => 'match_uid', 'type' => 'varchar', 'size' => 100, 'default' => ''),
		array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_tournament', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_clan', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'kills', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'deaths', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'assists', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'team1_confirmed', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'team2_confirmed', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('match_uid', 'id_member')),
		array('type' => 'index', 'columns' => array('id_tournament', 'id_member')),
	),
);

// Table 17: rivals_tournament_reports - Tournament match dispute reports
$tables[] = array(
	'table_name' => '{db_prefix}rivals_tournament_reports',
	'columns' => array(
		array('name' => 'id_report', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_tournament', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_reporter', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'team1_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'team2_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'winner_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_report')),
		array('type' => 'index', 'columns' => array('id_tournament')),
	),
);

// Table 18: rivals_tournament_decerto - Tournament game mode/map configuration
$tables[] = array(
	'table_name' => '{db_prefix}rivals_tournament_decerto',
	'columns' => array(
		array('name' => 'id_tournament', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'round_num', 'type' => 'int', 'size' => 6, 'default' => 0),
		array('name' => 'modes', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'map1', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'map2', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'map3', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_tournament', 'round_num')),
	),
);

// Table 19: rivals_seasons - Season definitions
$tables[] = array(
	'table_name' => '{db_prefix}rivals_seasons',
	'columns' => array(
		array('name' => 'id_season', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_ladder', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'status', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_season')),
		array('type' => 'index', 'columns' => array('id_ladder')),
	),
);

// Table 20: rivals_season_data - Archived standings per season
$tables[] = array(
	'table_name' => '{db_prefix}rivals_season_data',
	'columns' => array(
		array('name' => 'id_clan', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_season', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'wins', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'losses', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'draws', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'score', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'streak', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'current_rank', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'best_rank', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'worst_rank', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'last_rank', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'goals_for', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'goals_against', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'ratio', 'type' => 'varchar', 'size' => 20, 'default' => '0'),
		array('name' => 'pwner_award', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'is_frozen', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_clan', 'id_season')),
		array('type' => 'index', 'columns' => array('id_season')),
	),
);

// Table 21: rivals_game_modes - Game mode and map definitions (consolidated)
$tables[] = array(
	'table_name' => '{db_prefix}rivals_game_modes',
	'columns' => array(
		array('name' => 'id_mode', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'mode_type', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'game_name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'short_name', 'type' => 'varchar', 'size' => 20, 'default' => ''),
		array('name' => 'parent_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'is_cpc', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'is_active', 'type' => 'tinyint', 'size' => 1, 'default' => 1),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_mode')),
		array('type' => 'index', 'columns' => array('short_name')),
		array('type' => 'index', 'columns' => array('mode_type')),
	),
);

// Table 22: rivals_mvp_definitions - MVP award definitions per ladder
$tables[] = array(
	'table_name' => '{db_prefix}rivals_mvp_definitions',
	'columns' => array(
		array('name' => 'id_mvp', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'description', 'type' => 'text'),
		array('name' => 'id_platform', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_ladder', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_mvp')),
		array('type' => 'index', 'columns' => array('id_ladder')),
	),
);

// Table 23: rivals_rosters - Named team rosters for tournaments
$tables[] = array(
	'table_name' => '{db_prefix}rivals_rosters',
	'columns' => array(
		array('name' => 'id_roster', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_clan', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'members', 'type' => 'text'),
		array('name' => 'id_leader', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_roster')),
		array('type' => 'index', 'columns' => array('id_clan')),
	),
);

// Table 24: rivals_matchfinder - Matchmaking queue
$tables[] = array(
	'table_name' => '{db_prefix}rivals_matchfinder',
	'columns' => array(
		array('name' => 'id_entry', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'id_clan', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_ladder', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'wait_time', 'type' => 'int', 'size' => 10, 'default' => 0),
		array('name' => 'is_unranked', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
		array('name' => 'created_at', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_entry')),
		array('type' => 'index', 'columns' => array('id_ladder')),
	),
);

// Table 25: rivals_random_maps - Random map of the day
$tables[] = array(
	'table_name' => '{db_prefix}rivals_random_maps',
	'columns' => array(
		array('name' => 'id_random', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
		array('name' => 'game_name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'short_name', 'type' => 'varchar', 'size' => 20, 'default' => ''),
		array('name' => 'image', 'type' => 'varchar', 'size' => 255, 'default' => ''),
		array('name' => 'last_updated', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_random')),
	),
);

// Table 26: rivals_challenge_rights - Which groups can challenge in which ladders
$tables[] = array(
	'table_name' => '{db_prefix}rivals_challenge_rights',
	'columns' => array(
		array('name' => 'id_clan', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'id_ladder', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
		array('name' => 'is_1v1', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
	),
	'indexes' => array(
		array('type' => 'primary', 'columns' => array('id_clan', 'id_ladder')),
	),
);

// Create all tables
foreach ($tables as $table)
{
	$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], array(), 'ignore');
}

// Add profile columns to SMF members table
$member_columns = array(
	array('name' => 'rivals_clan_session', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'rivals_gamer_name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'rivals_mvp_count', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'rivals_exp', 'type' => 'varchar', 'size' => 20, 'default' => '0'),
	array('name' => 'rivals_ladder_level', 'type' => 'tinyint', 'size' => 3, 'default' => 0),
	array('name' => 'rivals_ladder_value', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'rivals_round_wins', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'rivals_round_losses', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'rivals_chicken_count', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'rivals_pwner_count', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'rivals_rep_value', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'rivals_rep_time', 'type' => 'int', 'size' => 10, 'default' => 0),
);

foreach ($member_columns as $column)
{
	$smcFunc['db_add_column']('{db_prefix}members', $column, array(), 'update');
}
?>