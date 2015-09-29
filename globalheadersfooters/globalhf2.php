<?php
/*
Global Headers Footers
Version 2.0
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function globalhf()
{
	// Check if the current user can change headers footers
	isAllowedTo('admin_forum');

	loadtemplate('globalhf2');

	// Global Headers Footers Actions
	$subActions = array(
		'view' => 'globalhf_view',
		'save' => 'globalhf_save'
	);


	// Follow the sa or just go to View function
	if (!empty($subActions[@$_GET['sa']]))
		$subActions[@$_GET['sa']]();
	else
		$subActions['view']();

}
function globalhf_view()
{
	global $context, $txt;

	//checkSession('get');

	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['globalhf_title'],
			'description' => '',
			'tabs' => array(
				'view' => array(
					'description' => '',
				),
			),
		);


	// Load main global headers footers  template.
	$context['sub_template']  = 'main';

	// Set the page title
	$context['page_title'] = $txt['globalhf_title'];
}
function  globalhf_save()
{
	global $boarddir, $context;

	//checkSession('post');

	$styleheaders = $_POST['headers'];
	$stylefooters = $_POST['footers'];

	$styleheaders = stripslashes($styleheaders);
	$stylefooters = stripslashes($stylefooters);


	// Save Headers
	$filename = $boarddir . '/smfheader.txt';
	@chmod($filename, 0644);
	if (!$handle = fopen($filename, 'w'))
		fatal_error('Can not open' . $filename   . '.',false);

	// Write the headers to our opened file.
	if (!fwrite($handle, $styleheaders))
	{
		//fatal_error('Can not write to' . $filename   . '.',false);
	}
	fclose($handle);

	//Save Footers
	$filename = $boarddir . '/smffooter.txt';
	@chmod($filename, 0644);
	if (!$handle = fopen($filename, 'w'))
		fatal_error('Can not open' . $filename   . '.',false);

	// Write the headers to our opened file.
	if (!fwrite($handle, $stylefooters))
	{

		//fatal_error('Can not write to' . $filename   . '.',false);
	}

	fclose($handle);

	redirectexit('action=admin;area=globalhf;sesc=' . $context['session_id']);
}
?>