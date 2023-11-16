<?php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
else if(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin priveleges required.');

$query = $smcFunc['db_query']('', '
	DELETE FROM {db_prefix}scheduled_tasks WHERE task = "limit_posters"
');

if (SMF == 'SSI')
	echo 'Database changes are complete!';
?>