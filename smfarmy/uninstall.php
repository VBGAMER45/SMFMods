<?php
/**
 * Army System - Uninstall
 * Removes scheduled tasks. Hooks are removed via package-info.xml.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

global $smcFunc;

// Remove scheduled tasks
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}scheduled_tasks
	WHERE task IN ({array_string:tasks})',
	array('tasks' => array('army_auto_gain', 'army_merc_upkeep', 'army_inactive_check'))
);
