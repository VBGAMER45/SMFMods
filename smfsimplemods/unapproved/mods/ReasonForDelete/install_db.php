<?php
/*---------------------------------------------------------------------------------
*	UP User Post Vote By Smfsimple.com
**********************************************************************************/
	global $mbname, $boardurl, $db_prefix, $context;
	global $smcFunc, $db_name;
	// Define the Manual Installation Status
    $manual_install = false;
    if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')){
		require_once(dirname(__FILE__) . '/SSI.php');
	
		$manual_install = true;
    }
    elseif (!defined('SMF'))
	die('The UP USER POST VOTE installer wasn\'t able to connect to SMF! Make sure that you are either installing this via the Package Manager or the SSI.php file is in the same directory.');
    if ($manual_install)
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
	<title>UP USER POST VOTE Database Installer</title>
     <link rel="stylesheet" type="text/css" href="Themes/default/style.css" />
</head>
<body>
	<br /><br />';
	//Call db_extend
	db_extend('packages');
	//Basic Settings :)
	$rfd_settings = array(
	'rfd_enabled' => 0,
	'rfd_titleset' => '',
	'rfd_senderid' => '',
);  
	
updateSettings($rfd_settings);
	
    // Creating tables
	$tables = array(
		
		'rfdmod' => array(
			'name' => 'rfdmod',
			//Columns
			'columns' => array(
				array(
					'name' => 'id_rfd',
					'type' => 'int',
					'null' => 'not null',
					'auto' => true,
				),							   								
				array(
					'name' => 'title',
					'type' => 'text',
					'size' =>  60,									
				),
					
				array(
					'name' => 'description',
					'type' => 'text',
					'size' =>  255,									
				),

				array(
					'name' => 'default_text',
					'type' => 'longtext',
					
				),		
			),
			//End Columns
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_rfd')
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
	BD CREATED! WWW.SMFSIMPLE.COM!
</div>
</td></tr></table>
<br />
</body></html>';
    }
?>