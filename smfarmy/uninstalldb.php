<?php
/**
 * Army System - Full Database Uninstall
 * Drops all Army System tables. Only run if you want to completely remove all data.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

global $smcFunc;

$tables = array(
	'army_settings',
	'army_items',
	'army_races',
	'army_members',
	'army_inventory',
	'army_attack_logs',
	'army_attack_logs_inv',
	'army_spy_logs',
	'army_ip_tracking',
	'army_staff_logs',
	'army_modules',
	'army_versions',
	'army_events',
	'army_clans',
	'army_clan_members',
	'army_clan_pending',
	'army_transfer_services',
	'army_transfer_log',
);

foreach ($tables as $table)
	$smcFunc['db_drop_table']('{db_prefix}' . $table);
