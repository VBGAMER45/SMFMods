<?php
/*
Social Login Pro
Version 2.0
by:vbgamer45
http://www.smfhacks.com

License Information:
Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
*/

function SocialLoginProMain()
{
	global $socialloginproVersion;



	// Hold Current Version
	$socialloginproVersion = '2.0';

	// Load template
	loadtemplate('SocialLoginPro2');

	// Sub Action Array
	$subActions = array(
		'settings' => 'SocialLoginProSettings',
		'settings2' => 'SocialLoginProSettings2',
		'merge' => 'slp_mergeaccount',
		'delete' => 'slp_deleteaccount',
	);

	if (isset($_REQUEST['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';

	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		SocialLoginProSettings();
}

function SocialLoginProSettings()
{
	global $txt, $context, $smcFunc;

	// Only admins can access SocialLoginPro Settings
	isAllowedTo('admin_forum');
	
	// Set template
	$context['sub_template'] = 'slp_settings';

	// Set page title
	$context['page_title'] = $txt['slp_admin'];


	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' =>  $txt['slp_admin'],
			'description' => '',
			'tabs' => array(
				'settings' => array(
					'description' => $txt['slp_settings'],
				),
			),
		);


}

function SocialLoginProSettings2()
{
	global $smcFunc;
	
	// Only admins can access SocialLoginPro Settings
	isAllowedTo('admin_forum');

	// Security Check
	checkSession('post');
	
	$sites = '';
	
	// Check for enabled sites
	if (isset($_REQUEST['site']))
	{
		$sites = $_REQUEST['site'];
		$siteArray = array();
		foreach($sites as $site  => $key)
		{
			$site = htmlspecialchars($site,ENT_QUOTES);
			$siteArray[] = $site;
		}

		
		$sites = implode(',',$siteArray);

	}

	
	$slp_apikey = $_REQUEST['slp_apikey'];
	$slp_secretkey = $_REQUEST['slp_secretkey'];
	$slp_loginheight = (int) $_REQUEST['slp_loginheight'];
	$slp_loginwidth = (int) $_REQUEST['slp_loginwidth'];
	$slp_registerheight = (int) $_REQUEST['slp_registerheight'];
	$slp_registerwidth = (int) $_REQUEST['slp_registerwidth'];
	$slp_disableregistration = isset($_REQUEST['slp_disableregistration']) ? 1 : 0;
	$slp_allowaccountmerge = isset($_REQUEST['slp_allowaccountmerge']) ? 1 : 0;
	$slp_importavatar = isset($_REQUEST['slp_importavatar']) ? 1 : 0;
	
	updateSettings(
	array(
	'slp_apikey' => $slp_apikey,
	'slp_secretkey' => $slp_secretkey,
	'slp_loginheight' => $slp_loginheight,
	'slp_loginwidth' => $slp_loginwidth,
	'slp_registerheight' => $slp_registerheight,
	'slp_registerwidth' => $slp_registerwidth,
	'slp_enabledProviders' => $sites,
	'slp_disableregistration' => $slp_disableregistration, 
	'slp_allowaccountmerge' => $slp_allowaccountmerge,
	'slp_importavatar' => $slp_importavatar,
	));



	// Redirect to the admin area
	redirectexit('action=admin;area=slp;sa=settings');
}

function slp_mergeaccount()
{
	global $txt, $context, $smcFunc, $user_info;
	
	is_not_guest();
	
	// Load template
	loadtemplate('SocialLoginPro2');
	
	$context['page_title'] = $txt['slp_merge_account'];
	$context['sub_template'] = 'slp_mergeaccount';
	
}

function slp_deleteaccount()
{
	global $txt, $context, $smcFunc, $user_info;
	
	is_not_guest();
	
	$id = (int) $_REQUEST['id'];
	
	$smcFunc['db_query']('', "
	DELETE FROM
	{db_prefix}social_logins WHERE id_member = " . $user_info['id'] . ' AND id = ' . $id);
	
	redirectexit('action=profile;area=mergeaccount');
}

?>