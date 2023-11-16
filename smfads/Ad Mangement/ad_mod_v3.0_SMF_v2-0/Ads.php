<?php
/******************************************************************************
* SMF: Simple Machines Forum - Ad Management Mod                              *
*                                                                             *
* =========================================================================== *
* Software Version:           Ad mod: 3.3                                     *
* Software by:                smfhacks.com                                    *
* Copyright 2010-2017 by:     smfhacks.com                                    *
* Support site:               www.smfads.com                                  *
*******************************************************************************
* This mod is free software; you may not redistribute or provide a modified   *
* version to redistribute.  This mod is distributed in the hope that it is    *
* and will be useful, but WITHOUT ANY WARRANTIES; without even any implied    *
* warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            *
******************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

function Ads()
{
	global $context, $txt, $scripturl;

	// You need to be an admin to edit settings!
	isAllowedTo('admin_forum');

	// All the admin bar, to make it right.
	loadTemplate('Ads');
	loadLanguage('Ads');



	$context['page_title'] = $txt['ad_management'];

	// Delegation makes the world... that is, the package manager go 'round.
	$subActions = array(
		'main' => array('mainAds','admin_forum'),
		'add' => array('addAds','admin_forum'),
		'edit' => array('editAds','admin_forum'),
		'delete' => array('deleteAds','admin_forum'),
		'reports' => array('reportsAds','admin_forum'),
		'settings' => array('settingsAds','admin_forum'),
        'copyright' => array('AdsCopyright','admin_forum'),
		'credits' => array('creditsAds','admin_forum'),
		'help' => array('helpAds','admin_forum'),
	);

	// By default do the basic settings.
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';


	// Set up some tabs...
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['ad_management'],
		'description' => $txt['ad_management_disc'],
		'tabs' => array(
			'main' => array(
				'title' => $txt['ad_management_main'],

			),
			'add' => array(
				'title' => $txt['ad_management_add'],

			),
			'reports' => array(
				'title' => $txt['ad_management_reports'],

			),
			'settings' => array(
				'title' => $txt['ad_management_settings'],

			),
            'copyright' => array(
				'title' => $txt['ads_txt_copyrightremoval'],

			),
			'credits' => array(
				'title' => $txt['ad_management_credits'],

			),
		),
	);


	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['sa']][0]();


}

function mainAds()
{
	global $context, $txt;

	$context['sub_template'] = 'adminmain';
	$context['page_title'] = $txt['ad_management_main'];

}

function addAds()
{
	global $context, $txt, $smcFunc;

	// The delete this membergroup button was pressed.
	if (isset($_POST['add']))
	{
		if(empty($_POST['name']) || empty($_POST['content']))
			fatal_lang_error('error_ads_missing_info', false);
		else
		{
			//Insert the basic info for the ads. Settings will come after this

			$_POST = escapestring__recursive($_POST);


			$smcFunc['db_query']('',"
			INSERT INTO {db_prefix}ads
				(NAME,CONTENT)
					VALUES ('$_POST[name]', '$_POST[content]')");
			$advertsid = $smcFunc['db_insert_id']("{db_prefix}ads", 'ADS_ID');;

			//Settings for the ads
			$adsUpdate = array();

			//Which Boards should this add appear on? 0 for all boards
			if (isset($_POST['boards']))
				$adsUpdate[] = 'BOARDS = "' . $_POST['boards'] .'"';
			//Which membergroups should see this add? 0 for all membergroups
			//Should this add display in posts? If so which ones? 0 for no posts
			if (isset($_POST['posts']))
				$adsUpdate[] = 'POSTS = "' . $_POST['posts'] .'"';
			//Display this ad between cateogires
			if (isset($_POST['category']))
				$adsUpdate[] = 'CATEGORY = "' . $_POST['category'] .'"';
			//What type of ad is this? Html=0 PHP=1
			if (isset($_POST['type']))
				$adsUpdate[] = 'TYPE = ' . ($_POST['type'] ? '1' : '0');
			//Display this ad under the Menu?
			if (isset($_POST['show_index']))
				$adsUpdate[] = 'show_index = ' . ($_POST['show_index'] ? '1' : '0');
			//Display this ad on the boards
			if (isset($_POST['show_board']))
				$adsUpdate[] = 'show_board = ' . ($_POST['show_board'] ? '1' : '0');
			//Display this ad on the thread index
			if (isset($_POST['show_threadindex']))
				$adsUpdate[] = 'show_threadindex = ' . ($_POST['show_threadindex'] ? '1' : '0');
			//Display this ad on the thread
			if (isset($_POST['show_thread']))
				$adsUpdate[] = 'show_thread = ' . ($_POST['show_thread'] ? '1' : '0');
			//Display this ad after the last post
			if (isset($_POST['show_lastpost']))
				$adsUpdate[] = 'show_lastpost = ' . ($_POST['show_lastpost'] ? '1' : '0');
			//Display this ad on the bottom of the page
			if (isset($_POST['show_bottom']))
				$adsUpdate[] = 'show_bottom = ' . ($_POST['show_bottom'] ? '1' : '0');
			//Display this ad in the welcome area
			if (isset($_POST['show_welcome']))
				$adsUpdate[] = 'show_welcome = ' . ($_POST['show_welcome'] ? '1' : '0');
			//Display this ad on the top of the page
			if (isset($_POST['show_topofpage']))
				$adsUpdate[] = 'show_topofpage = ' . ($_POST['show_topofpage'] ? '1' : '0');
			//Display this ad tower right
			if (isset($_POST['show_towerright']))
				$adsUpdate[] = 'show_towerright = ' . ($_POST['show_towerright'] ? '1' : '0');
			//Display this ad tower left
			if (isset($_POST['show_towerleft']))
				$adsUpdate[] = 'show_towerleft = ' . ($_POST['show_towerleft'] ? '1' : '0');
			//Display this ad under child boards
			if (isset($_POST['show_underchildren']))
				$adsUpdate[] = 'show_underchildren = ' . ($_POST['show_underchildren'] ? '1' : '0');

			// Do the updates (if any).
			if (!empty($adsUpdate))
				$smcFunc['db_query']('',"
					UPDATE {db_prefix}ads
					SET
						" . implode(',
						', $adsUpdate) . "
					WHERE ADS_ID = $advertsid
					LIMIT 1");

			redirectexit('action=admin;area=ads');
		}
	}


	$context['sub_template'] = 'addAds';
	$context['page_title'] = $txt['ad_management_add'];
}

function reportsAds()
{
	global $context, $txt;

	$context['sub_template'] = 'reportsAds';
	$context['page_title'] = $txt['ad_management_reports'];

}

function settingsAds()
{
	global $context, $txt;
	if (isset($_POST['save']))
	{

		updateSettings(array(
			'ads_displayAdsAdmin' => isset($_POST['ads_displayAdsAdmin']) ? '1' : '0',
			'ads_updateReports' => isset($_POST['ads_updateReports']) ? '1' : '0',
			'ads_quickDisable' => isset($_POST['ads_quickDisable']) ? '1' : '0',
			'ads_lookLikePosts' => isset($_POST['ads_lookLikePosts']) ? '1' : '0',
			));

		redirectexit('action=admin;area=ads;sa=settings');
	}

	$context['sub_template'] = 'settingsAds';
	$context['page_title'] = $txt['ad_management_settings'];

}

function editAds()
{

	global $context, $txt, $adverts, $advertsEdit, $smcFunc;



	// Make sure this group is editable.
	if (empty($_REQUEST['ad']) || (int) $_REQUEST['ad'] < 1)
		fatal_lang_error('membergroup_does_not_exist', false);
	$_REQUEST['ad'] = (int) $_REQUEST['ad'];

	//Deletes the Ad
	if (isset($_POST['delete']))
	{
		//Delete the ad
		$smcFunc['db_query']('',"
			DELETE FROM {db_prefix}ads
			WHERE ADS_ID = '$_REQUEST[ad]'");

		redirectexit('action=admin;area=ads');
	}
	// Updates the info for the ad
	elseif (isset($_POST['save']))
	{
		if(empty($_POST['name']) || empty($_POST['content']))
			fatal_lang_error('error_ads_missing_info', false);
		else
		{
			$_POST = escapestring__recursive($_POST);

			//Insert the basic info for the ads. Settings will come after this
			$adsUpdate = array();
			//Fill the array with all the information about ads
			$adsUpdate[] = 'BOARDS = "' . $_POST['boards'] .'"';
			$adsUpdate[] = 'POSTS = "' . $_POST['posts'] . '"';
			$adsUpdate[] = 'CATEGORY = "' . $_POST['category'] . '"';
			$adsUpdate[] = 'TYPE = ' . $_POST['type'];
			$adsUpdate[] = 'show_index = ' . (empty($_POST['show_index']) ? '0' : '1');
			$adsUpdate[] = 'show_board = ' . (empty($_POST['show_board']) ? '0' : '1');
			$adsUpdate[] = 'show_threadindex = ' . (empty($_POST['show_threadindex']) ? '0' : '1');
			$adsUpdate[] = 'show_thread = ' . (empty($_POST['show_thread']) ? '0' : '1');
			$adsUpdate[] = 'show_lastpost = ' . (empty($_POST['show_lastpost']) ? '0' : '1');
			$adsUpdate[] = 'show_bottom = ' . (empty($_POST['show_bottom']) ? '0' : '1');
			$adsUpdate[] = 'show_welcome = ' . (empty($_POST['show_welcome']) ? '0' : '1');
			$adsUpdate[] = 'show_topofpage = ' . (empty($_POST['show_topofpage']) ? '0' : '1');
			$adsUpdate[] = 'show_towerright = ' . (empty($_POST['show_towerright']) ? '0' : '1');
			$adsUpdate[] = 'show_towerleft = ' . (empty($_POST['show_towerleft']) ? '0' : '1');
			$adsUpdate[] = 'show_underchildren = ' . (empty($_POST['show_underchildren']) ? '0' : '1');

				$smcFunc['db_query']('',"
					UPDATE {db_prefix}ads
						SET NAME = '$_POST[name]', CONTENT = '$_POST[content]'
							WHERE ADS_ID = '$_REQUEST[ad]'");

				$smcFunc['db_query']('',"
					UPDATE {db_prefix}ads
					SET " . implode(',', $adsUpdate) . "
					WHERE ADS_ID = '$_REQUEST[ad]'");

		}

		redirectexit('action=admin;area=ads');
	}
	//If nothing is set, then just display the ad.

	for ($i=0;$i<count($adverts);$i++)
		if($adverts[$i]['id'] == $_REQUEST['ad'])
			$advertsEdit = $adverts[$i];

	$context['sub_template'] = 'editAds';
	$context['page_title'] = $txt['ad_management_main'];

}

function creditsAds()
{

	global $context, $txt;

	$context['sub_template'] = 'creditsAds';
	$context['page_title'] = $txt['ad_management_credits'];

}

function helpAds()
{

	global $txt, $helptxt, $context;


	// What help string should be used?
	if (isset($helptxt[$_GET['help']]))
		$context['help_text'] = &$helptxt[$_GET['help']];
	elseif (isset($txt[$_GET['help']]))
		$context['help_text'] = &$txt[$_GET['help']];
	else
		$context['help_text'] = $_GET['help'];

	// Set the page title to something relevant.
	$context['page_title'] = $helptxt['ad_manage_help'];

	// Don't show any template layers, just the popup sub template.
	$context['template_layers'] = array();
	$context['sub_template'] = 'helpAds';

}

function AdsCopyright()
{
    global $context, $mbname, $txt, $scripturl;

    if (isset($_REQUEST['save']))
    {

        $ads_copyrightkey = addslashes($_REQUEST['ads_copyrightkey']);

        updateSettings(
    	array(
    	'ads_copyrightkey' => $ads_copyrightkey,
    	)

    	);
    }



	$context['page_title'] = $mbname . ' - '  . $txt['ads_txt_copyrightremoval'];

	$context['sub_template']  = 'ads_copyright';
}
?>