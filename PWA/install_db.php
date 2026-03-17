<?php

/**
 * Mobile-First PWA Shell — Database Installation
 *
 * Creates the push subscription table.
 *
 * @package smfmods:pwa-shell
 * @license MIT
 */

if (!defined('SMF'))
	die('No direct access...');

global $smcFunc;

$tables = [];

$tables[] = [
	'table_name' => '{db_prefix}pwa_push_subscriptions',
	'columns' => [
		[
			'name'     => 'id',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'auto'     => true,
		],
		[
			'name'     => 'id_member',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0,
		],
		[
			'name' => 'endpoint',
			'type' => 'text',
		],
		[
			'name'    => 'p256dh',
			'type'    => 'varchar',
			'size'    => 255,
			'default' => '',
		],
		[
			'name'    => 'auth',
			'type'    => 'varchar',
			'size'    => 255,
			'default' => '',
		],
		[
			'name'     => 'created_at',
			'type'     => 'int',
			'size'     => 10,
			'unsigned' => true,
			'default'  => 0,
		],
	],
	'indexes' => [
		[
			'type'    => 'primary',
			'columns' => ['id'],
		],
		[
			'name'    => 'idx_member',
			'type'    => 'index',
			'columns' => ['id_member'],
		],
	],
];

foreach ($tables as $table)
{
	$smcFunc['db_create_table'](
		$table['table_name'],
		$table['columns'],
		$table['indexes'],
		[],
		'ignore'
	);
}

// Register the scheduled task for push notification polling.
// Runs every 2 minutes to check for new alerts and send push notifications.
// Only insert if the task doesn't already exist (re-install safe).
$result = $smcFunc['db_query']('', '
	SELECT id_task
	FROM {db_prefix}scheduled_tasks
	WHERE task = {string:task}',
	[
		'task' => 'pwa_push_alerts',
	]
);

if ($smcFunc['db_num_rows']($result) == 0)
{
	$smcFunc['db_insert']('insert',
		'{db_prefix}scheduled_tasks',
		[
			'next_time'        => 'int',
			'time_offset'      => 'int',
			'time_regularity'  => 'int',
			'time_unit'        => 'string',
			'disabled'         => 'int',
			'task'             => 'string',
			'callable'         => 'string',
		],
		[
			time() + 120,      // next_time: 2 minutes from now
			0,                 // time_offset
			2,                 // time_regularity: every 2...
			'm',               // time_unit: ...minutes
			0,                 // disabled: enabled
			'pwa_push_alerts', // task name
			'$sourcedir/PWA/PWAPush.php|PWA\\PWAPush::scheduledPushAlerts#', // callable (file|method)
		],
		['id_task']
	);
}
$smcFunc['db_free_result']($result);
