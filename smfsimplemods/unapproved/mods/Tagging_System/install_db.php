<?php
/*---------------------------------------------------------------------------------
	Tagging System SMFSIMPLE - BY 4Kstore										  *
**********************************************************************************/
	global $smcFunc;
	// Define the Manual Installation Status
    $manual_install = false;
	$modName = "Tagging System SMFSIMPLE";
    if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	{
		require_once(dirname(__FILE__) . '/SSI.php');
		$manual_install = true;
    }
    elseif (!defined('SMF'))
		die('The '.$modName.' installer wasn\'t able to connect to SMF! Make sure that you are either installing this via the Package Manager or the SSI.php file is in the same directory.');
    if ($manual_install)
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
	<title>'.$modName.' Database Installer</title>
     <link rel="stylesheet" type="text/css" href="Themes/default/style.css" />
</head>
<body>
<br /><br />';
//Call db_extend
db_extend('packages');
//Basic Settings :)
$tagging_settings = array(
	'tag_enabled' => 1,
	'tag_required' => 1,
	'tag_board_disabled' => '',
	'tag_max_per_topic' => 8,
	'tag_min_length' => 3,
	'tag_max_length' => 15,
	'tag_max_suggested' => 4,
	'tag_enabled_related_topics' => 1,
	'tag_max_related_topics' => 5,
	'tag_cloud_enabled' => 1,
	'tag_cloud_limit' => 30,
	'tag_cloud_smallest_color' => '',
	'tag_cloud_smallest_opacity' => '',
	'tag_cloud_smallest_fontsize' => '',
	'tag_cloud_small_color' => '',
	'tag_cloud_small_opacity' => '',
	'tag_cloud_small_fontsize' => '',
	'tag_cloud_medium_color' => '',
	'tag_cloud_medium_opacity' => '',
	'tag_cloud_medium_fontsize' => '',
	'tag_cloud_large_color' => '',
	'tag_cloud_large_opacity' => '',
	'tag_cloud_large_fontsize' => '',
	'tag_cloud_largest_color' => '',
	'tag_cloud_largest_opacity' => '',
	'tag_cloud_largest_fontsize' => '',
	'tag_list_enabled' => 1,
	'tag_list_show_count' => 1,
	'tag_search_paginate_limit' => 15,

);
updateSettings($tagging_settings);

$tables = array(
	'tags' => array(
			'name' => 'tags',
			//Columns
			'columns' => array(
			array(
				'name' => 'id_tag',
				'type' => 'mediumint',
				'size' => '8',
				'null' => 'not null',
				'auto' => true,
			),
			array(
				'name' => 'tag',
				'type' => 'tinytext',
				'null' => false,
			),
		),
		//End Columns
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_tag')
			),
		)
	),
	//End Table
	'tags_topic' => array(
			'name' => 'tags_topic',
			//Columns
			'columns' => array(
			array(
				'name' => 'id',
				'type' => 'mediumint',
				'size' => '8',
				'null' => 'not null',
				'auto' => true,
			),
			array(
				'name' => 'id_tag',
				'type' => 'mediumint',
				'size' => '8',
				'default' => 0,
				'null' => false,
			),
			array(
				'name' => 'id_topic',
				'type' => 'mediumint',
				'size' => '8',
				'default' => 0,
				'null' => false,
			),
		),
		//End Columns
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id')
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
if ($manual_install)
{
	echo '
	<table cellpadding="0" cellspacing="0" border="0" class="tborder" width="800" align="center"><tr><td>
	<div class="titlebg" style="padding: 1ex" align="center">
		'.$modName.' DB CREATED! WWW.SMFSIMPLE.COM!
	</div>
	</td></tr></table>
	<br />
	</body></html>';
}