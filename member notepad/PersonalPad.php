<?php
/*
Member Notepad
Version 1.0
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function SavePad()
{
	global $db_prefix, $ID_MEMBER, $func, $txt;
	
	// Get the Member ID
	$id = (int) $_REQUEST['id'];
	
	// Check if they are allowed to edi th user's personal notepad
	if ($id == $ID_MEMBER || allowedTo('admin_forum'))
	{
		// Make the html safe if used so it does not mess up the page
		$anotes = $func['htmlspecialchars']($_POST['txtnotes'], ENT_QUOTES);
	
		// Insert the text into the users personal notepad
		db_query("REPLACE INTO {$db_prefix}themes
				(ID_MEMBER, variable, value)
			VALUES ($id,'notes','$anotes')", __FILE__, __LINE__);
		
		
		// Redirect to back to the users profile
		redirectexit('action=profile;u=' . $id);
	}
	else 
	{
		// Give them permission denied error
		fatal_error($txt['mempad_error'], false);
	}


}

?>