<?php
/**
 * Army System - Installation Script
 * Creates database tables, inserts default data, registers hooks and scheduled tasks.
 *
 * @package ArmySystem
 * @version 1.0
 */

// Safety check
if (!defined('SMF'))
	die('No direct access...');

global $smcFunc, $db_prefix;

db_extend('packages');
// =========================================================================
// 1. CREATE TABLES
// =========================================================================

// Settings table
$smcFunc['db_create_table']('{db_prefix}army_settings', array(
	array('name' => 'core', 'type' => 'varchar', 'size' => 32, 'default' => 'armysystem'),
	array('name' => 'setting', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'current_value', 'type' => 'text'),
	array('name' => 'default_value', 'type' => 'text'),
), array(
	array('name' => 'setting_core', 'type' => 'unique', 'columns' => array('core', 'setting')),
), array(), 'ignore');

// Items table (weapons, armor, spy tools, sentry tools, fort, siege, upgrades, training, mercs)
$smcFunc['db_create_table']('{db_prefix}army_items', array(
	array('name' => 'id', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'auto' => true),
	array('name' => 'type', 'type' => 'varchar', 'size' => 8, 'default' => ''),
	array('name' => 'number', 'type' => 'smallint', 'size' => 5, 'default' => 0),
	array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'value', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'price', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'letter', 'type' => 'varchar', 'size' => 8, 'default' => ''),
	array('name' => 'icon', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'repair', 'type' => 'bigint', 'size' => 20, 'default' => 0),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('id')),
	array('name' => 'idx_type_number', 'type' => 'index', 'columns' => array('type', 'number')),
), array(), 'ignore');

// Races table
$smcFunc['db_create_table']('{db_prefix}army_races', array(
	array('name' => 'race_id', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'auto' => true),
	array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'bonus_income', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'bonus_discount', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'bonus_casualties', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'bonus_attack', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'bonus_defence', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'bonus_spy', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'bonus_sentry', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'default_icon', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'train_atk_icon', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'train_def_icon', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'merc_icon', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'merc_atk_icon', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'merc_def_icon', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'spy_icon', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'sentry_icon', 'type' => 'varchar', 'size' => 255, 'default' => ''),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('race_id')),
), array(), 'ignore');

// Members table
$smcFunc['db_create_table']('{db_prefix}army_members', array(
	array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'race_id', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
	array('name' => 'army_points', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'army_size', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'soldiers_attack', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'soldiers_defense', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'soldiers_spy', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'soldiers_sentry', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'soldiers_untrained', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'mercs_attack', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'mercs_defense', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'mercs_untrained', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'fort_level', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'default' => 1),
	array('name' => 'siege_level', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'default' => 1),
	array('name' => 'unit_prod_level', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'default' => 0),
	array('name' => 'spy_skill_level', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'default' => 0),
	array('name' => 'attack_turns', 'type' => 'smallint', 'size' => 5, 'unsigned' => true, 'default' => 0),
	array('name' => 'total_attacks', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'total_defends', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'rank_level', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'last_active', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'vacation_start', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'vacation_end', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'is_active', 'type' => 'tinyint', 'size' => 1, 'default' => 1),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('id_member')),
	array('name' => 'idx_race', 'type' => 'index', 'columns' => array('race_id')),
	array('name' => 'idx_rank', 'type' => 'index', 'columns' => array('rank_level')),
), array(), 'ignore');

// Inventory table
$smcFunc['db_create_table']('{db_prefix}army_inventory', array(
	array('name' => 'i_id', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'auto' => true),
	array('name' => 'i_section', 'type' => 'varchar', 'size' => 1, 'default' => 'n'),
	array('name' => 'i_number', 'type' => 'smallint', 'size' => 5, 'default' => 0),
	array('name' => 'i_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'i_quantity', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'i_strength', 'type' => 'float', 'size' => 15, 'default' => 0),
	array('name' => 'i_spy_sabbed', 'type' => 'bigint', 'size' => 20, 'default' => 0),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('i_id')),
	array('name' => 'idx_member', 'type' => 'index', 'columns' => array('i_member')),
	array('name' => 'idx_identity', 'type' => 'index', 'columns' => array('i_section', 'i_number')),
), array(), 'ignore');

// Attack logs table
$smcFunc['db_create_table']('{db_prefix}army_attack_logs', array(
	array('name' => 'id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
	array('name' => 'attack_time', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'money_stolen', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'turns_used', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'attacker', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'defender', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'atk_damage', 'type' => 'bigint', 'size' => 20, 'unsigned' => true, 'default' => 0),
	array('name' => 'def_damage', 'type' => 'bigint', 'size' => 20, 'unsigned' => true, 'default' => 0),
	array('name' => 'atk_kill', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'def_kill', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'atk_army_gen', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'atk_army_atk', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'atk_army_mgen', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'atk_army_matk', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'def_army_gen', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'def_army_def', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'def_army_mgen', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'def_army_mdef', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'atk_spy_killed', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'atk_sen_killed', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'def_spy_killed', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'def_sen_killed', 'type' => 'bigint', 'size' => 20, 'default' => 0),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('id')),
	array('name' => 'idx_attacker', 'type' => 'index', 'columns' => array('attacker')),
	array('name' => 'idx_defender', 'type' => 'index', 'columns' => array('defender')),
	array('name' => 'idx_time', 'type' => 'index', 'columns' => array('attack_time')),
), array(), 'ignore');

// Attack logs inventory (per-item breakdown)
$smcFunc['db_create_table']('{db_prefix}army_attack_logs_inv', array(
	array('name' => 'a_section', 'type' => 'varchar', 'size' => 1, 'default' => 'n'),
	array('name' => 'a_number', 'type' => 'smallint', 'size' => 5, 'default' => 0),
	array('name' => 'a_memberid', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'a_logid', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'a_quantity', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'a_used', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'a_sab_total', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'a_original_strength', 'type' => 'float', 'size' => 15, 'default' => 0),
	array('name' => 'a_after_strength', 'type' => 'float', 'size' => 15, 'default' => 0),
	array('name' => 'a_spy_sabbed', 'type' => 'bigint', 'size' => 20, 'default' => 0),
), array(
	array('name' => 'idx_logid', 'type' => 'index', 'columns' => array('a_logid')),
), array(), 'ignore');

// Spy logs
$smcFunc['db_create_table']('{db_prefix}army_spy_logs', array(
	array('name' => 's_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
	array('name' => 'spy_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'target_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'spies_used', 'type' => 'smallint', 'size' => 5, 'default' => 0),
	array('name' => 'spy_time', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'result', 'type' => 'varchar', 'size' => 10, 'default' => 'success'),
	array('name' => 'mission', 'type' => 'varchar', 'size' => 10, 'default' => 'recon'),
	array('name' => 'caught', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'link_id', 'type' => 'int', 'size' => 10, 'default' => 0),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('s_id')),
	array('name' => 'idx_spy', 'type' => 'index', 'columns' => array('spy_member')),
	array('name' => 'idx_target', 'type' => 'index', 'columns' => array('target_member')),
), array(), 'ignore');

// IP tracking
$smcFunc['db_create_table']('{db_prefix}army_ip_tracking', array(
	array('name' => 'ip', 'type' => 'varchar', 'size' => 45, 'default' => ''),
	array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'last_time', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
), array(
	array('name' => 'idx_ip', 'type' => 'index', 'columns' => array('ip')),
	array('name' => 'idx_member', 'type' => 'index', 'columns' => array('id_member')),
), array(), 'ignore');

// Staff logs
$smcFunc['db_create_table']('{db_prefix}army_staff_logs', array(
	array('name' => 'sl_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
	array('name' => 'action', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'target_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'log_time', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'note', 'type' => 'mediumtext'),
	array('name' => 'reason', 'type' => 'tinytext'),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('sl_id')),
	array('name' => 'idx_time', 'type' => 'index', 'columns' => array('log_time')),
), array(), 'ignore');

// Modules (extensibility)
$smcFunc['db_create_table']('{db_prefix}army_modules', array(
	array('name' => 'link', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'filename', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'setting', 'type' => 'mediumtext'),
	array('name' => 'skin_file', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'lang_file', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'extra', 'type' => 'text'),
	array('name' => 'link_all', 'type' => 'tinyint', 'size' => 1, 'default' => 1),
	array('name' => 'link_race', 'type' => 'tinyint', 'size' => 1, 'default' => 1),
	array('name' => 'link_guest', 'type' => 'tinyint', 'size' => 1, 'default' => 1),
	array('name' => 'blocked', 'type' => 'tinyint', 'size' => 1, 'default' => 1),
	array('name' => 'position', 'type' => 'mediumint', 'size' => 8, 'default' => 0),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('link')),
), array(), 'ignore');

// Versions
$smcFunc['db_create_table']('{db_prefix}army_versions', array(
	array('name' => 'upgrade_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
	array('name' => 'core', 'type' => 'varchar', 'size' => 32, 'default' => ''),
	array('name' => 'version_id', 'type' => 'int', 'size' => 10, 'default' => 0),
	array('name' => 'name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'upgrade_time', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('upgrade_id')),
), array(), 'ignore');

// Events
$smcFunc['db_create_table']('{db_prefix}army_events', array(
	array('name' => 'id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
	array('name' => 'event_time', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'event_from', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'event_to', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'event_type', 'type' => 'smallint', 'size' => 5, 'default' => 0),
	array('name' => 'event_text', 'type' => 'varchar', 'size' => 255, 'default' => ''),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('id')),
	array('name' => 'idx_time', 'type' => 'index', 'columns' => array('event_time')),
), array(), 'ignore');

// Clans
$smcFunc['db_create_table']('{db_prefix}army_clans', array(
	array('name' => 'c_id', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'auto' => true),
	array('name' => 'c_name', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'c_description', 'type' => 'tinytext'),
	array('name' => 'c_leader', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'c_started', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'c_by_invite', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
	array('name' => 'c_by_join', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
	array('name' => 'c_notes', 'type' => 'text'),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('c_id')),
), array(), 'ignore');

// Clan members
$smcFunc['db_create_table']('{db_prefix}army_clan_members', array(
	array('name' => 'clan_id', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
	array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'time_joined', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('id_member')),
	array('name' => 'idx_clan', 'type' => 'index', 'columns' => array('clan_id', 'id_member')),
), array(), 'ignore');

// Clan pending
$smcFunc['db_create_table']('{db_prefix}army_clan_pending', array(
	array('name' => 'status', 'type' => 'tinyint', 'size' => 1, 'default' => 0),
	array('name' => 'clan_id', 'type' => 'mediumint', 'size' => 8, 'unsigned' => true, 'default' => 0),
	array('name' => 'id_member', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'time_pending', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('clan_id', 'id_member')),
), array(), 'ignore');

// Transfer services
$smcFunc['db_create_table']('{db_prefix}army_transfer_services', array(
	array('name' => 's_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
	array('name' => 'title', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'description', 'type' => 'tinytext'),
	array('name' => 'field_from', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'field_to', 'type' => 'varchar', 'size' => 255, 'default' => ''),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('s_id')),
), array(), 'ignore');

// Transfer log
$smcFunc['db_create_table']('{db_prefix}army_transfer_log', array(
	array('name' => 'l_id', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'auto' => true),
	array('name' => 'field_from', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'field_to', 'type' => 'varchar', 'size' => 255, 'default' => ''),
	array('name' => 'member_from', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'member_to', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
	array('name' => 'amount', 'type' => 'bigint', 'size' => 20, 'default' => 0),
	array('name' => 'l_time', 'type' => 'int', 'size' => 10, 'unsigned' => true, 'default' => 0),
), array(
	array('name' => 'primary', 'type' => 'primary', 'columns' => array('l_id')),
	array('name' => 'idx_time', 'type' => 'index', 'columns' => array('l_time')),
), array(), 'ignore');

// =========================================================================
// 2. INSERT DEFAULT SETTINGS
// =========================================================================

$default_settings = array(
	// General
	array('armysystem', 'army_enabled', '1', '1'),
	array('armysystem', 'allow_guest_view', '1', '1'),
	array('armysystem', 'name', 'Army System', 'Army System'),
	array('armysystem', 'currency_name', 'Gold', 'Gold'),

	// Resell rates
	array('armysystem', 'tool_resell', '45', '45'),
	array('armysystem', 'armor_resell', '75', '75'),
	array('armysystem', 'ship_resell', '60', '60'),

	// Combat limits
	array('armysystem', 'max_spy', '10', '10'),
	array('armysystem', 'max_attack', '5', '5'),
	array('armysystem', 'turns_max', '10', '15'),
	array('armysystem', 'attack_money', '1', '1'),
	array('armysystem', 'view_money', '5', '5'),

	// Reset defaults
	array('armysystem', 'reset_bank', '1', '1'),
	array('armysystem', 'reset_sentry', '0', '0'),
	array('armysystem', 'reset_spy', '0', '0'),
	array('armysystem', 'reset_def_merc', '0', '0'),
	array('armysystem', 'reset_atk_merc', '0', '0'),
	array('armysystem', 'reset_merc', '0', '0'),
	array('armysystem', 'reset_def_sold', '0', '0'),
	array('armysystem', 'reset_atk_sold', '0', '0'),
	array('armysystem', 'reset_army', '10', '10'),
	array('armysystem', 'reset_turn', '25', '25'),
	array('armysystem', 'reset_money', '50000', '50000'),

	// Post gains
	array('armysystem', 'post_point_reply', '5000', '5000'),
	array('armysystem', 'post_point_topic', '10000', '10000'),
	array('armysystem', 'guy_per_post', '10', '10'),
	array('armysystem', 'post_per_guy', '10', '10'),

	// Log timing
	array('armysystem', 'log_time_unit', '86400', '86400'),
	array('armysystem', 'log_time', '14', '14'),

	// Vacation
	array('armysystem', 'vacation_allowed', '1', '1'),
	array('armysystem', 'vacation_min_time', '4', '4'),
	array('armysystem', 'vacation_max_time', '28', '28'),
	array('armysystem', 'vacation_back', '0', '0'),

	// Turn gain timing
	array('armysystem', 'rank_time_unit', '60', '60'),
	array('armysystem', 'rank_time', '60', '60'),
	array('armysystem', 'turn_gain', '2', '1'),

	// Mercenary timing
	array('armysystem', 'mercanery_time_unit', '3600', '3600'),
	array('armysystem', 'mercanery_time', '2', '12'),

	// Production timing
	array('armysystem', 'production_time_unit', '60', '3600'),
	array('armysystem', 'production_time', '60', '24'),
	array('armysystem', 'production_constant', '1', '1'),
	array('armysystem', 'production_base', '1', '2'),

	// Money timing
	array('armysystem', 'money_time_unit', '60', '60'),
	array('armysystem', 'money_time', '60', '30'),

	// Money gains
	array('armysystem', 'money_mercanery', '5', '10'),
	array('armysystem', 'money_convert', '5', '30'),
	array('armysystem', 'money_amount', '5', '50'),

	// Auto gain
	array('armysystem', 'auto_gain_prod', '1', '1'),
	array('armysystem', 'auto_gain_money', '1', '1'),
	array('armysystem', 'real_time_auto', '1', '1'),

	// Security
	array('armysystem', 'security_check', '0', '0'),

	// Icons
	array('armysystem', 'max_height', '32', '32'),
	array('armysystem', 'max_width', '32', '32'),

	// Inactive
	array('armysystem', 'inactive_time', '14', '14'),
	array('armysystem', 'inactive_time_unit', '86400', '86400'),

	// Production type
	array('armysystem', 'production_type', '0', '1'),

	// Fort/Siege bonuses
	array('armysystem', 'fort_percent', '10', '25'),
	array('armysystem', 'siege_percent', '10', '25'),

	// Factor size
	array('armysystem', 'factor_size', '0', '0'),
);

foreach ($default_settings as $setting)
{
	$smcFunc['db_insert']('ignore',
		'{db_prefix}army_settings',
		array('core' => 'string', 'setting' => 'string', 'current_value' => 'string', 'default_value' => 'string'),
		$setting,
		array('core', 'setting')
	);
}

// =========================================================================
// 3. INSERT DEFAULT RACES
// =========================================================================

$default_races = array(
	// name, income, discount, casualties, attack, defence, spy, sentry, default_icon
	array('Humans', 40, 0, 0, -10, -10, -10, -10, 'human.png'),
	array('Elves', 0, 0, 0, 10, 10, -5, -5, 'elf.png'),
	array('Orcs', 0, 0, 0, -5, -5, 10, 10, 'orc.png'),
	array('Ghosts', -5, 0, 25, 0, 0, 0, 0, 'ghost.png'),
	array('Corpses', 0, 0, 0, 40, -10, -15, -5, 'corpse.png'),
	array('Ogres', 0, 0, 0, -10, 40, -5, -15, 'ogre.png'),
	array('Wizards', 0, 0, 0, -15, -5, 40, -10, 'wizard.png'),
	array('Wights', 0, 0, 0, -5, -15, -10, 40, 'wight.png'),
	array('Dragons', -50, 0, 0, 15, 15, 15, 15, 'dragon.png'),
	array('Dwarfs', 0, 0, 40, -10, 0, 0, 0, 'dwarf.png'),
);

foreach ($default_races as $race)
{
	$smcFunc['db_insert']('ignore',
		'{db_prefix}army_races',
		array(
			'name' => 'string', 'bonus_income' => 'int', 'bonus_discount' => 'int',
			'bonus_casualties' => 'int', 'bonus_attack' => 'int', 'bonus_defence' => 'int',
			'bonus_spy' => 'int', 'bonus_sentry' => 'int', 'default_icon' => 'string',
		),
		$race,
		array('race_id')
	);
}

// =========================================================================
// 4. INSERT DEFAULT ITEMS
// =========================================================================

$default_items = array();
$num = 1;

// Attack weapons (type 'a')
$weapons = array(
	array('Dagger', 10, 10000, 'a_dagger.png'),
	array('Hand Axe', 20, 20000, 'a_hand_axe.png'),
	array('Spear', 50, 30000, 'a_spear.png'),
	array('Mace', 100, 50000, 'a_mace.png'),
	array('Flail', 450, 200000, 'a_flail.png'),
	array('Hammer', 750, 300000, 'a_hammer.png'),
	array('War Hammer', 1000, 380000, 'a_war_hammer.png'),
	array('Battle Axe', 3000, 1000000, 'a_battle_axe.png'),
	array('Morningstar', 5000, 1400000, 'a_morningstar.png'),
	array('Two Handed Sword', 10000, 2000000, 'a_two_handed_sword.png'),
);
$num = 1;
foreach ($weapons as $w)
	$default_items[] = array('a', $num++, $w[0], $w[1], $w[2], '', $w[3], 0);

// Defense armor (type 'd')
$armor = array(
	array('Leather Helmet', 10, 10000, 'd_leather_helmet.png'),
	array('Leather Armor', 20, 20000, 'd_leather_armor.png'),
	array('Wooden Shield', 50, 30000, 'd_wooden_shield.png'),
	array('Scale Mail', 100, 50000, 'd_scale_armor.png'),
	array('Iron Shield', 450, 200000, 'd_iron_shield.png'),
	array('Plate Armor', 750, 300000, 'q_plate_armor.png'),
	array('Steel Shield', 1000, 380000, 'd_steel_shield.png'),
	array('Steel Helmet', 3000, 1000000, 'd_steel_helmet.png'),
	array('Steel Plate Armor', 5000, 1400000, 'd_steel_plate_armor.png'),
	array('Elven Mail', 10000, 2000000, 'd_elven_mail.png'),
);
$num = 1;
foreach ($armor as $a)
	$default_items[] = array('d', $num++, $a[0], $a[1], $a[2], '', $a[3], 0);

// Spy tools (type 'q')
$spy_tools = array(
	array('Rope', 1, 10000, 'q_rope.png'),
	array('Dirk', 4, 20000, 'q_dirk.png'),
	array('Cloak', 10, 30000, 'q_cloak.png'),
	array('Grappling Hook', 25, 50000, 'q_grappling_hook.png'),
	array('Stealth Horse', 60, 100000, 'q_stealth_horse.png'),
);
$num = 1;
foreach ($spy_tools as $s)
	$default_items[] = array('q', $num++, $s[0], $s[1], $s[2], '', $s[3], 0);

// Sentry tools (type 'e')
$sentry_tools = array(
	array('Big Candle', 1, 10000, 'e_big_candle.png'),
	array('Horn', 4, 20000, 'e_horn.png'),
	array('Tripwire', 10, 30000, 'e_tripwire.png'),
	array('Guard Dog', 25, 50000, 'e_guard_dog.png'),
	array('Watch Post', 60, 100000, 'e_watch_post.png'),
);
$num = 1;
foreach ($sentry_tools as $e)
	$default_items[] = array('e', $num++, $e[0], $e[1], $e[2], '', $e[3], 0);

// Fort levels (type 'f')
$forts = array(
	array('Camp', 0, 0, 'f_camp.png'),
	array('Stockade', 0, 40000, 'f_stockade.png'),
	array('Walled Town', 0, 80000, 'f_walled_town.png'),
	array('Tower', 0, 160000, 'f_tower.png'),
	array('Battlements', 0, 320000, 'f_battlements.png'),
	array('Fortress', 0, 640000, 'f_fortress.png'),
	array('Moat', 0, 1280000, 'f_moat.png'),
	array('Stronghold', 0, 2560000, 'f_grand_citadel.png'),
	array('Citadel', 0, 5120000, 'f_citadel.png'),
);
$num = 1;
foreach ($forts as $f)
	$default_items[] = array('f', $num++, $f[0], $f[1], $f[2], '', $f[3], 0);

// Siege levels (type 's')
$sieges = array(
	array('None', 0, 0, ''),
	array('Ballistas', 0, 40000, 's_ballistas.png'),
	array('Battering Ram', 0, 80000, 's_battering_ram.png'),
	array('Ladders', 0, 160000, 's_ladders.png'),
	array('Catapult', 0, 320000, 's_catapult.png'),
	array('Siege Tower', 0, 640000, 'siege_towers.png'),
	array('Trebuchets', 0, 1280000, 's_trebuchets.png'),
	array('Dynamite', 0, 2560000, 's_dynamite.png'),
	array('Cannons', 0, 5120000, 's_cannons.png'),
);
$num = 1;
foreach ($sieges as $s)
	$default_items[] = array('s', $num++, $s[0], $s[1], $s[2], '', $s[3], 0);

// Unit production levels (type 'up') — 30 levels, doubling prices starting at 10000
$price = 10000;
for ($i = 1; $i <= 30; $i++)
{
	$default_items[] = array('up', $i, 'Production Level ' . $i, 0, $price, '', '', 0);
	$price *= 2;
}

// Spy skill levels (type 'sl') — 10 levels, doubling from 18000
$price = 18000;
for ($i = 1; $i <= 10; $i++)
{
	$default_items[] = array('sl', $i, 'Spy Level ' . $i, 0, $price, '', '', 0);
	$price *= 2;
}

// Training prices (type 'ta', 'td', 'tu', 'ts', 'tw')
$default_items[] = array('ta', 1, 'Train Attack', 0, 2500, '', '', 0);
$default_items[] = array('td', 1, 'Train Defense', 0, 2500, '', '', 0);
$default_items[] = array('tu', 1, 'Untrain', 0, 500, '', '', 0);
$default_items[] = array('ts', 1, 'Train Spy', 0, 3500, '', '', 0);
$default_items[] = array('tw', 1, 'Train Sentry', 0, 3500, '', '', 0);

// Mercenary prices (type 'ma', 'md', 'mu')
$default_items[] = array('ma', 1, 'Attack Mercenary', 10, 3500, '', '', 0);
$default_items[] = array('md', 1, 'Defense Mercenary', 10, 3500, '', '', 0);
$default_items[] = array('mu', 1, 'Untrained Mercenary', 25, 3000, '', '', 0);

// Ships (type 'b') — contribute to both attack AND defense
$ships = array(
	array('Canoe', 5, 8000, 'canoe.png'),
	array('Rowboat', 10, 15000, 'rowboat.png'),
	array('Skiff', 20, 25000, 'skiff.png'),
	array('Dhow', 40, 40000, 'dhow.png'),
	array('Fishing Trawler', 75, 70000, 'fishing_trawler.png'),
	array('Merchant Cog', 120, 120000, 'merchant_cog.png'),
	array('Viking Longship', 200, 200000, 'viking_longship.png'),
	array('Two-Masted Schooner', 350, 350000, 'two_masted_schooner.png'),
	array('Chinese Junk Warship', 500, 500000, 'chinese_junk_warship.png'),
	array('Caravel', 750, 750000, 'compact_caravel.png'),
	array('Carrack', 1000, 1000000, 'carrack.png'),
	array('Frigate', 1500, 1500000, 'sleeker_frigate.png'),
	array('Small Warship', 2000, 2000000, 'small_warship.png'),
	array('Galleon', 3000, 3000000, 'multi_deck_galleon.png'),
	array('Man of War', 5000, 5000000, 'compact_man_of_war.png'),
	array('Icebreaker', 7000, 7000000, 'compact_icebreaker.png'),
	array('Elven Ship', 9000, 9000000, 'elven_ship.png'),
	array('Ghost Ship', 12000, 12000000, 'ghost_ship.png'),
	array('Dwarven Submersible', 15000, 15000000, 'dwarven_submersible.png'),
	array('Airship', 20000, 20000000, 'airship.png'),
);
$num = 1;
foreach ($ships as $b)
	$default_items[] = array('b', $num++, $b[0], $b[1], $b[2], '', $b[3], 0);

foreach ($default_items as $item)
{
	$smcFunc['db_insert']('ignore',
		'{db_prefix}army_items',
		array(
			'type' => 'string', 'number' => 'int', 'name' => 'string',
			'value' => 'int', 'price' => 'int', 'letter' => 'string',
			'icon' => 'string', 'repair' => 'int',
		),
		$item,
		array('id')
	);
}

// =========================================================================
// 5. INSERT DEFAULT TRANSFER SERVICES
// =========================================================================

$smcFunc['db_insert']('ignore',
	'{db_prefix}army_transfer_services',
	array('title' => 'string', 'description' => 'string', 'field_from' => 'string', 'field_to' => 'string'),
	array('Weapons Transfer', 'Transfer some weapons to your friends.', '{army2_weapons}', '{army2_weapons}'),
	array('s_id')
);

$smcFunc['db_insert']('ignore',
	'{db_prefix}army_transfer_services',
	array('title' => 'string', 'description' => 'string', 'field_from' => 'string', 'field_to' => 'string'),
	array('Money Transfer', 'Send money to your friends.', 'army_points', 'army_points'),
	array('s_id')
);

// =========================================================================
// 6. INSERT VERSION RECORD
// =========================================================================

$smcFunc['db_insert']('ignore',
	'{db_prefix}army_versions',
	array('core' => 'string', 'version_id' => 'int', 'name' => 'string', 'upgrade_time' => 'int'),
	array('armysystem', 10000, 'Army System 1.0 for SMF', time()),
	array('upgrade_id')
);

// =========================================================================
// 7. GRANT DEFAULT PERMISSIONS
// (Hooks are registered via <hook> tags in package-info.xml)
// =========================================================================

// Group 0 = Regular Members, Group -1 = Guests
// Grant army_view to guests and members; army_play/attack/spy to members only
$default_permissions = array(
	// Regular members (group 0)
	array(0, 'army_view', 0),
	array(0, 'army_play', 0),
	array(0, 'army_attack', 0),
	array(0, 'army_spy', 0),
	// Guests (group -1) — view only
	array(-1, 'army_view', 0),
);

foreach ($default_permissions as $perm)
{
	$smcFunc['db_insert']('ignore',
		'{db_prefix}permissions',
		array('id_group' => 'int', 'permission' => 'string', 'add_deny' => 'int'),
		$perm,
		array('id_group', 'permission')
	);
}

// =========================================================================
// 8. REGISTER SCHEDULED TASKS
// =========================================================================

$smcFunc['db_insert']('ignore',
	'{db_prefix}scheduled_tasks',
	array('next_time' => 'int', 'time_offset' => 'int', 'time_regularity' => 'int', 'time_unit' => 'string', 'disabled' => 'int', 'task' => 'string', 'callable' => 'string'),
	array(time() + 3600, 0, 1, 'h', 0, 'army_auto_gain', '$sourcedir/ArmySystem-Scheduled.php|scheduled_army_auto_gain'),
	array('task')
);

$smcFunc['db_insert']('ignore',
	'{db_prefix}scheduled_tasks',
	array('next_time' => 'int', 'time_offset' => 'int', 'time_regularity' => 'int', 'time_unit' => 'string', 'disabled' => 'int', 'task' => 'string', 'callable' => 'string'),
	array(time() + 7200, 0, 2, 'h', 0, 'army_merc_upkeep', '$sourcedir/ArmySystem-Scheduled.php|scheduled_army_merc_upkeep'),
	array('task')
);

$smcFunc['db_insert']('ignore',
	'{db_prefix}scheduled_tasks',
	array('next_time' => 'int', 'time_offset' => 'int', 'time_regularity' => 'int', 'time_unit' => 'string', 'disabled' => 'int', 'task' => 'string', 'callable' => 'string'),
	array(time() + 86400, 0, 1, 'd', 0, 'army_inactive_check', '$sourcedir/ArmySystem-Scheduled.php|scheduled_army_inactive_check'),
	array('task')
);
