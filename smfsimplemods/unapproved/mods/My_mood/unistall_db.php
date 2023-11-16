<?php
/*---------------------------------------------------------------------------------
*	Version 1.0																	  *
*	Author: 4kstore																  *
*	Copyright 2013												        		  *
*	Powered by www.smfsimple.com												  *
***********************************************************************************
**********************************************************************************/	
global $smcFunc;
// Define the Manual Installation Status
$manual_install = false;

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	require_once(dirname(__FILE__) . '/SSI.php');
	$manual_install = true;
}

elseif (!defined('SMF'))
	die('The Unistaller wasn\'t able to connect to SMF! Make sure that you are either installing this via the Package Manager or the SSI.php file is in the same directory.');
	
if ($manual_install)	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml"><head>
		<title>Database Unistaller</title>
		 <link rel="stylesheet" type="text/css" href="Themes/default/style.css" />
	</head>
	<body>
		<br /><br />';
		
//Call db_extend
db_extend('packages');

//Remove settings
$smcFunc['db_query']('', "
	DELETE FROM {db_prefix}settings 
	WHERE variable LIKE 'mymood_%'");

$smcFunc['db_drop_table']('{db_prefix}my_mood', array(), 'ignore');
	
// OK, time to report, output all the stuff to be shown to the user
if ($manual_install)
echo '
<div class="titlebg" style="padding: 1ex" align="center">
MOD Unistalled!
</div>
<br />
</body></html>';