<?php
/*
Admin Notepad
Version 1.0
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function SaveNote()
{
	
	global $smcFunc;
	// Check if they are allowed to admin forum.
	isAllowedTo('admin_forum');

	// Make the html safe if used so it does not mess up the page
	$anotes = htmlspecialchars($_POST['txtnotes'], ENT_QUOTES);

	// Insert the admin notes into the database
	updateSettings(array('adminnotes' => $anotes,));

	// Redirect to the main admin page to see the changed notes
	redirectexit('action=admin');
}

?>