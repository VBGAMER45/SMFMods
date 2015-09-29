<?php

if (file_exists(dirname(dirname(dirname(__FILE__))) . '/Settings.php'))
{
	require(dirname(dirname(dirname(__FILE__))) . '/Settings.php');
	header('Location: ' . $boardurl);
}
else
	exit;

?>