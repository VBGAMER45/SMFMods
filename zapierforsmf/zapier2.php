<?php
/*
Zapier for SMF
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

function zapierMain()
{
    global $modSettings;

	// Only admins can access Settings
	isAllowedTo('admin_forum');


    if (empty($modSettings['zapier_hash']))
    {
        $zaphash = hash('sha256', mt_rand());
        $modSettings['zapier_hash'] = $zaphash;

          updateSettings(
    	array(
    	'zapier_hash' => $zaphash,
    	)

    	);

    }


	// Load the language files
	if (loadlanguage('zapier') == false)
		loadLanguage('zapier','english');

	// Load template
	loadtemplate('zapier2');

	// Sub Action Array
	$subActions = array(
		'settings' => 'zapierSettings',
		'settings2' => 'zapierSettings2',
	);

	if (isset($_REQUEST['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';

	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		zapierSettings();
}

function zapierSettings()
{
	global $txt, $context, $smcFunc;
	
	$context['zapier_boards'] = array();
	$request = $smcFunc['db_query']('', "
				SELECT
					b.ID_BOARD, b.name AS bName, c.name AS cName
				FROM {db_prefix}boards AS b, {db_prefix}categories AS c
				WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
	while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['zapier_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	$smcFunc['db_free_result']($request);
	

	// Set template
	$context['sub_template'] = 'zapier_settings';

	// Set page title
	$context['page_title'] = $txt['zapier_admin'];


	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['zapier_admin'],
			'description' => '',
			'tabs' => array(
				'settings' => array(
					'description' => $txt['zapier_txt_settings_desc'],
				),


			),
		);

}

function zapierSettings2()
{
	global $smcFunc;

	// Security Check
	checkSession('post');



	// Redirect to the admin area
	redirectexit('action=admin;area=zapier;sa=settings');
}



?>