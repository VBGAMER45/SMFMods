<?php
/*----------------------------------------------------------------------------------/
*	My Mood                                             	                        *
*	Author: SSimple Team - 4KSTORE						 							*
*	Powered by www.smfsimple.com						   							*
************************************************************************************/

global $smcFunc;

// Define the Manual Installation Status
$manual_install = false;

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	require_once(dirname(__FILE__) . '/SSI.php');
	$manual_install = true;
}

elseif (!defined('SMF'))
	die('The installer wasn\'t able to connect to SMF! Make sure that you are either installing this via the Package Manager or the SSI.php file is in the same directory.');

if ($manual_install)
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml"><head>
		<title>SMFSIMPLE Database Installer</title>
		 <link rel="stylesheet" type="text/css" href="Themes/default/style.css" />
	</head>
	<body>
		<br /><br />';

//Call db_extend
db_extend('packages');

//Basic Settings :)
$mm = array(
	'mymood_enabled' => 0,
	'mymood_limit_in_profile' => 5,
	'mymood_limit_chars' => 500,
	'mymood_allow_bbc' => 0,
	'mymood_allow_smileys' => 0,
	'mymood_show_on_boardindex' => 0,
	'mymood_boardindex_where' => 'mood_board_top',
	'mymood_limit_in_board_total' => 5,
	'mymood_limit_in_board_view' => 1,
	'mymood_second_per_mood_board' => 3,
	'mymood_groups_excluded_board' => 0,
);

updateSettings($mm);

$tables = array(
	'my_mood' => array(
		'name' => 'my_mood',
		//Columns
		'columns' => array(
			array(
				'name' => 'id_mood',
				'type' => 'int',
				'size' => '10',
				'null' => 'not null',
				'auto' => true,
			),
			array(
				'name' => 'id_member',
				'type' => 'int',
				'size' => '10',
				'null' => 'not null',
			),
			array(
				'name' => 'mood_content',
				'type' => 'text',
				'null' => 'not null',
			),
			array(
				'name' => 'date',
				'type' => 'int',
				'size' => '10',
				'null' => 'not null',
			),
		),
		//End Columns
		'indexes' => array(
			array(
				'type' => 'primary',
				'columns' => array('id_mood')
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
Mod Installed!
</div>
<br />
</body></html>';
    }

?>