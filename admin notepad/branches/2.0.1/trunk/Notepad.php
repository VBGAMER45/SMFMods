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
	global $db_prefix;
	
	//Check if they are allowed to admin forum.
	isAllowedTo('admin_forum');

	//Make the html safe if used so it does not mess up the page
	$anotes = htmlspecialchars($_POST['txtnotes'], ENT_QUOTES);

	//Insert the admin notes into the database
	db_query("REPLACE INTO {$db_prefix}settings
			(variable, value)
		VALUES ('adminnotes','$anotes')", __FILE__, __LINE__);
	
	
	//Redirect to the main admin page to see the changed notes
	redirectexit('action=admin');
}

?>