<?php
/*---------------------------------------------------------------------------------
*	Limit Posters By Smfsimple.com
**********************************************************************************/
	global $db_prefix, $context;
	global $smcFunc, $db_name;
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
	<title>Limit Posters Database Installer</title>
     <link rel="stylesheet" type="text/css" href="Themes/default/style.css" />
</head>
<body>
	<br /><br />';
	//Call db_extend
	db_extend('packages');


$query = $smcFunc['db_query']('', '
	SELECT id_task FROM {db_prefix}scheduled_tasks WHERE task = "limit_posters"
');

if ($smcFunc['db_num_rows']($query) == 0)
	$smcFunc['db_query']('', '
		INSERT INTO {db_prefix}scheduled_tasks (id_task, next_time, time_offset, time_regularity, time_unit, disabled, task)
		VALUES (NULL, 0, 0, 1, "d", 0, "limit_posters")'
	);

$smcFunc['db_add_column'] (
	'{db_prefix}membergroups',
	array
	(
		'name' => 'limit_posters',
		'type' => 'int',
		'size' => 10,
		'default' => (-1),
		'null' => false,
	)
);

$smcFunc['db_add_column'] (
	'{db_prefix}members',
	array
	(
		'name' => 'limit_posts',
		'type' => 'int',
		'size' => 10,
		'default' => (-1),
	)
);

	// OK, time to report, output all the stuff to be shown to the user
	if ($manual_install){
echo '
<table cellpadding="0" cellspacing="0" border="0" class="tborder" width="800" align="center"><tr><td>
<div class="titlebg" style="padding: 1ex" align="center">
	Limit Posters DB CREATED! WWW.SMFSIMPLE.COM!
</div>
</td></tr></table>
<br />
</body></html>';
    }

?>