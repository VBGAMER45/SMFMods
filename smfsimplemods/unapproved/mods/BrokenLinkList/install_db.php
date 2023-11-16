<?php
/*---------------------------------------------------------------------------------
*	Broken Links List															  *
*	Version 1.1																	  *
*	Author: 4kstore																  *
*	Powered by www.smfsimple.com												  *
**********************************************************************************/
	global $smcFunc;
	// Define the Manual Installation Status
    $manual_install = false;
    if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')){
		require_once(dirname(__FILE__) . '/SSI.php');
	
		$manual_install = true;
    }
    elseif (!defined('SMF'))
	die('The Broken Links List installer wasn\'t able to connect to SMF! Make sure that you are either installing this via the Package Manager or the SSI.php file is in the same directory.');
    if ($manual_install)
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
	<title>Broken Links List Database Installer</title>
     <link rel="stylesheet" type="text/css" href="Themes/default/style.css" />
</head>
<body>
	<br /><br />';
db_extend('packages');	
$bll_settings = array(
	'bll_enabled' => 0,
	'bll_titleset' => '',
	'bll_senderid' => '',
	'bll_pm_title' => '',
	'bll_pm_text' => '',
	'bll_warning_link_time' => '',
	'bll_warning_link_color' => '',	
); 
updateSettings($bll_settings);
	
$smcFunc['db_insert']('replace',
	'{db_prefix}scheduled_tasks',
	array(
		'next_time' => 'int', 'time_offset' => 'int', 'time_regularity' => 'int', 'time_unit' => 'string', 'disabled' => 'int', 'task' => 'string',
	),
	array(
		0, 0, 1, "d", 0, "bll_count_old_links"
	),
	array('id_task')
);

$smcFunc['db_add_column'] (
	'{db_prefix}boards',
	array(
		'name' => 'bll_enabled',
		'type' => 'tinyint',
		'size' => '4',
		'default' =>0,
		'null' => false,
	)
);

// Creating tables
$tables = array(		
	'bllmod' => array(
		'name' => 'broken_links_list',
		//Columns
		'columns' => array(
			array(
				'name' => 'id_report',
				'type' => 'int',
				'size' => '10',
				'null' => 'not null',
				'auto' => true,
			),
			array(
				'name' => 'id_msg',
				'type' => 'int',
				'size' => '10',
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
			array(					
				'name' => 'id_member_reported',
				'type' => 'mediumint',
				'size' => '8',
				'default' => 0,
				'null' => false,							
			),
			array(					
				'name' => 'id_member',
				'type' => 'mediumint',
				'size' => '8',
				'default' => 0,
				'null' => false,							
			),				
			array(
				'name' => 'reported_time',
				'type' => 'int',
				'size' => '10',
				'default' => 0,
				'null' => false,								
			),				
			array(					
				'name' => 'reported_name',					
				'type' => 'varchar',
				'size' => '255',
				'null' => false							
			),				
			array(					
				'name' => 'notes',					
				'type' => 'varchar',
				'size' => '255',
				'null' => false							
			),				
			array(					
				'name' => 'status',					
				'type' => 'tinyint',
				'size' => '4',
				'default' => 0,		
				'null' => false,
			),				
		),
		//End Columns
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_report')
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
<table cellpadding="0" cellspacing="0" border="0" class="tborder" width="800" align="center"><tr><td>
<div class="titlebg" style="padding: 1ex" align="center">
	Broken Links List DB CREATED! WWW.SMFSIMPLE.COM!
</div>
</td></tr></table>
<br />
</body></html>';
    }
?>