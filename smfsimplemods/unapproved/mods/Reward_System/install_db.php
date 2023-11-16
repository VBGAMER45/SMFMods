<?php
/*---------------------------------------------------------------------------------
*	SSRS By Smfsimple.com
**********************************************************************************/
	global $context;
	global $smcFunc;
	// Define the Manual Installation Status
    $manual_install = false;
    if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')){
		require_once(dirname(__FILE__) . '/SSI.php');

		$manual_install = true;
    }
    elseif (!defined('SMF'))
	die('The SSRS installer wasn\'t able to connect to SMF! Make sure that you are either installing this via the Package Manager or the SSI.php file is in the same directory.');
    if ($manual_install)
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
	<title>SSRS Database Installer</title>
     <link rel="stylesheet" type="text/css" href="Themes/default/style.css" />
</head>
<body>
	<br /><br />';
	//Call db_extend
	db_extend('packages');
	//Basic Settings :)
	$ssrs_settings = array(
	'ssrs_points_enabled' => 0,
	'ssrs_points_guests' => 0,
	'ssrs_points_show_givers_display' => 2,
	'ssrs_points_per_post' => 0,
	'ssrs_points_per_reply' => 0,
	'ssrs_points_per_registered' => 0,
	'ssrs_points_per_word' => 0,
	'ssrs_points_bonus_words' => 0,
	'ssrs_points_bonus_words_min' => 0,
	'ssrs_points_points_on_messageindex' => 0,
	'ssrs_points_per_poll' => 0,
	'ssrs_points_per_vote_poll' => 0,
	'ssrs_points_per_topic_views' => 0,
	'ssrs_points_per_topic_views_min' => 0,
	'ssrs_points_per_topic_replies' => 0,
	'ssrs_points_per_topic_replies_min' => 0,
	'ssrs_points_per_sticky' => 0,
	'ssrs_points_per_warning' => 0,	
);

updateSettings($ssrs_settings);
	
$smcFunc['db_insert']('replace',
	'{db_prefix}scheduled_tasks',
	array(
		'next_time' => 'int', 'time_offset' => 'int', 'time_regularity' => 'int', 'time_unit' => 'string', 'disabled' => 'int', 'task' => 'string',
	),
	array(
		0, 0, 1, "d", 0, "srss_addPoints"
	),
	array('id_task')
);		

$smcFunc['db_add_column'] (
	'{db_prefix}boards',
	array
	(
		'name' => 'ssrs_give_points_on',
		'type' => 'tinyint',
		'size' => 4,
		'default' => 1,
	)
);

$smcFunc['db_add_column'] (
	'{db_prefix}membergroups',
	array
	(
		'name' => 'ssrs_points',
		'type' => 'int',
		'size' => 10,
		'default' => 0,
	)
);

$smcFunc['db_add_column'] (
	'{db_prefix}members',
	array
	(
		'name' => 'ssrs_points',
		'type' => 'int',
		'size' => 10,
		'default' => 0,
	)
);

$smcFunc['db_add_column'] (
	'{db_prefix}members',
	array
	(
		'name' => 'ssrs_points_day',
		'type' => 'int',
		'size' => 10,
		'default' => 0,
	)
);

$smcFunc['db_add_column'] (
	'{db_prefix}topics',
	array
	(
		'name' => 'ssrs_points',
		'type' => 'int',
		'size' => 10,
		'default' => 0,
	)
);


$tables = array(
  'log_ssrs_good_post' => array(
				'name' => 'ssrs_good_post',
				//Columns
				'columns' => array(
				array(
					'name' => 'id_member',
					'type' => 'mediumint',
					'size' => '8',
					'null' => false,
				),

				array(
					'name' => 'id_topic',
					'type' => 'mediumint',
					'size' => '8',
					'default' => 0,
					'null' => false,
				),

				array(
					'name' => 'points',
					'type' => 'int',
					'size' => '10',
					'null' => false,
				),
			),
			//End Columns
			'indexes' => array(
				array(
					'type' => 'index',
					'columns' => array('id_member')
				),
			)
		),
		//End Table

	);

	//Creating Tables
	foreach ($tables as $table)
	{
		$table_name = $table['name'];
		$smcFunc['db_create_table']('{db_prefix}' . $table_name, $table['columns'], $table['indexes']);
		$currentTable = $smcFunc['db_table_structure']('{db_prefix}' . $table_name);
		// Check that all columns are in
		foreach ($table['columns'] as $id => $col)
		{
			$exists = false;
			// TODO: Check that definition is correct
			foreach ($currentTable['columns'] as $col2)
			{
				if ($col['name'] === $col2['name'])
				{
					$exists = true;
					break;
				}
			}

			// Add missing columns
			if (!$exists)
				$smcFunc['db_add_column']('{db_prefix}' . $table_name, $col);

			//Check, not change anything?
			if($exists)
			{
				$smcFunc['db_change_column']('{db_prefix}' . $table_name, $col['name'], $col);
			}

		}
		//End add missing columns
		// Check that all indexes are in and correct
		if ($table['indexes'] > 0)
		{
			foreach ($table['indexes'] as $id => $index)
			{
				$exists = false;

				foreach ($currentTable['indexes'] as $index2)
				{
					// Primary is special case
					if ($index['type'] == 'primary' && $index2['type'] == 'primary')
					{
						$exists = true;

						if ($index['columns'] !== $index2['columns'])
						{
							$smcFunc['db_remove_index']('{db_prefix}' . $table_name, 'primary');
							$smcFunc['db_add_index']('{db_prefix}' . $table_name, $index);
						}

						break;
					}
					// Make sure index is correct
					elseif (isset($index['name']) && isset($index2['name']) && $index['name'] == $index2['name'])
					{
						$exists = true;

						// Need to be changed?
						if ($index['type'] != $index2['type'] || $index['columns'] !== $index2['columns'])
						{
							$smcFunc['db_remove_index']('{db_prefix}' . $table_name, $index['name']);
							$smcFunc['db_add_index']('{db_prefix}' . $table_name, $index);
						}

						break;
					}
				}

				if (!$exists)
					$smcFunc['db_add_index']('{db_prefix}' . $table_name, $index);
			}
		}
		//End check indexes
	}

	// OK, time to report, output all the stuff to be shown to the user
	if ($manual_install){
echo '
<div class="titlebg" style="padding: 1ex" align="center">
SSRS POINTS DB CREATED! WWW.SMFSIMPLE.COM!
</div>
<br />
</body></html>';
    }

?>