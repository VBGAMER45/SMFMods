<?php
/*
SMF Staff Page
Version 1.2
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function Staff()
{
	global $context, $mbname, $txt;

	//Check if the current user can view the staff list
	isAllowedTo('view_stafflist');

	loadtemplate('Staff');

	//Load the main staff template
	$context['sub_template']  = 'main';

	//Set the page title
	$context['page_title'] = $mbname . ' - ' . $txt['smfstaff_stafflist'];
}
?>