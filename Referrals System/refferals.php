<?php
/*
Referrals System
Version 3.0
http://www.smfhacks.com
*/

function refferalsMain()
{
	loadtemplate('refferals');

	$subActions = array(
		'settings' => 'refferalsSettings',
		'settings2' => 'refferalsSettings2',
        'copyright' => 'Referrals_CopyrightRemoval',
	);

	if (isset($_REQUEST['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';

	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		RefferalsLinkClick();
}

function ProcessRefferalLink()
{
	global $modSettings, $db_prefix, $sourcedir;



	if (!isset($_REQUEST['refferedby']) && !isset($_REQUEST['referredby']) )
		return;


	if (isset($_REQUEST['referredby']))
		$refferedBy = (int) $_REQUEST['referredby'];

	if (isset($_REQUEST['refferedby']))
		$refferedBy = (int) $_REQUEST['refferedby'];

	if (empty($refferedBy) && isset($_COOKIE['refferedby']))
		$refferedBy = (int) $_COOKIE['refferedby'];

	if (empty($refferedBy) && isset($_SESSION['refferedby']))
		$refferedBy = (int) $_SESSION['refferedby'];


	if (!empty($refferedBy) && !isset($_COOKIE['refferedby']) && !isset($_SESSION['refferedby']))
	{

		// Track hits??
		if ($modSettings['ref_trackcookiehits'])
		{

			 db_query("
			UPDATE {$db_prefix}members
			SET referrals_hits = referrals_hits + 1
			WHERE ID_MEMBER =  " . $refferedBy, __FILE__, __LINE__);
		}
		require_once($sourcedir . '/Subs-Auth.php');
		// Set cookie
		$cookie_url = url_parts(!empty($modSettings['localCookies']), !empty($modSettings['globalCooki0es']));
		setcookie('refferedby', $refferedBy, time() + ($modSettings['ref_cookietrackdays']  * 24 * 60 * 60), $cookie_url[1], $cookie_url[0], 0);


		$_SESSION['refferedby'] = $refferedBy ;
	}

}

function refferalsProcessSignup($refferedByName = '',$NewMemberID = 0)
{
	global $db_prefix;

	if (empty($NewMemberID))
		return;

	// If not an integer an error occured...!
	if (is_array($NewMemberID))
		return;


	$refferedMemberID = 0;
	$refferedByName = htmlspecialchars($refferedByName,ENT_QUOTES);
	$result = db_query("
			SELECT
				ID_MEMBER
			FROM {$db_prefix}members
			WHERE memberName =  '$refferedByName' OR realName = '$refferedByName'" , __FILE__, __LINE__);
	$refferalRow = mysql_fetch_assoc($result);

	if (!empty($refferalRow['ID_MEMBER']))
		$refferedMemberID = $refferalRow['ID_MEMBER'];
	else
	{
		if (isset($_COOKIE['refferedby']) && !empty($_COOKIE['refferedby']))
			$refferedMemberID = (int) $_COOKIE['refferedby'];

		if (isset($_SESSION['refferedby']) && !empty($_SESSION['refferedby']))
			$refferedMemberID = (int) $_SESSION['refferedby'];
	}



	if (!empty($refferedMemberID))
	{
		 db_query("
			UPDATE {$db_prefix}members
			SET referrals_no = referrals_no + 1
			WHERE ID_MEMBER =  " . $refferedMemberID , __FILE__, __LINE__);

		 // Update reffered by
		 $t = time();
		  db_query("
			UPDATE {$db_prefix}members
			SET  referred_by = $refferedMemberID, referred_date = '$t'
			WHERE ID_MEMBER =  " . $NewMemberID , __FILE__, __LINE__);

	}



}

function RefferalsLinkClick()
{
	ProcessRefferalLink();

	// Redirect
	redirectexit('action=register');
}

function refferalsSettings()
{
	global $txt, $context, $scripturl;

	isAllowedTo('admin_forum');

	$context['sub_template'] = 'ref_settings';
	$context['page_title'] = $txt['ref_admin'];

	adminIndex('ref_admin');

    $context['admin_tabs'] = array(
		'title' => $txt['ref_admin'],
		'description' => '',
		'tabs' => array(),
	);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['ref_settings'],
			'description' => '',
			'href' => $scripturl . '?action=refferals;sa=settings',
			'is_selected' => $_REQUEST['sa'] == 'settings',
		);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['ref_txt_copyrightremoval'],
			'description' => '',
			'href' => $scripturl . '?action=refferals;sa=copyright',
			'is_selected' => $_REQUEST['sa'] == 'copyright',
		);



}

function refferalsSettings2()
{
	isAllowedTo('admin_forum');

	checkSession('post');

	$ref_cookietrackdays = (int) $_REQUEST['ref_cookietrackdays'];
	$ref_showreflink = isset($_REQUEST['ref_showreflink']) ? 1 : 0;
	$ref_showonpost = isset($_REQUEST['ref_showonpost']) ? 1 : 0;
	$ref_trackcookiehits = isset($_REQUEST['ref_trackcookiehits']) ? 1 : 0;

	updateSettings(
	array(
	'ref_cookietrackdays' => $ref_cookietrackdays,
	'ref_showreflink' => $ref_showreflink,
	'ref_showonpost' => $ref_showonpost,
	'ref_trackcookiehits' => $ref_trackcookiehits,

	));


	redirectexit('action=refferals;sa=settings');
}

function Referrals_CopyrightRemoval()
{
    global $context, $mbname, $txt, $scripturl;
	isAllowedTo('admin_forum');

    if (isset($_REQUEST['save']))
    {

        $ref_copyrightkey = addslashes($_REQUEST['ref_copyrightkey']);

        updateSettings(
    	array(
    	'ref_copyrightkey' => $ref_copyrightkey,
    	)

    	);
    }


   adminIndex('ref_admin');



    $context['admin_tabs'] = array(
		'title' => $txt['ref_admin'],
		'description' => '',
		'tabs' => array(),
	);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['ref_settings'],
			'description' => '',
			'href' => $scripturl . '?action=refferals;sa=settings',
			'is_selected' => $_REQUEST['sa'] == 'settings',
		);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['ref_txt_copyrightremoval'],
			'description' => '',
			'href' => $scripturl . '?action=refferals;sa=copyright',
			'is_selected' => $_REQUEST['sa'] == 'copyright',
		);


	$context['page_title'] = $mbname . ' - '  . $txt['ref_txt_copyrightremoval'];

	$context['sub_template']  = 'refcopyright';
}


?>