<?php
/*
GDPR Helper
Version 1.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2018-2019 SMFHacks.com

############################################
License Information:

Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################

*/

if (!defined('SMF'))
	die('Hacking attempt...');

function GPDR_Main()
{
	global $boardurl, $modSettings, $boarddir, $currentVersion, $context;

	$currentVersion = '1.0.9';

	// Load the language files
    if (loadlanguage('gpdr') == false)
        loadLanguage('gpdr','english');


	$subActions = array(
		'settings'=> 'GPDR_AdminSettings',
		'settings2'=> 'GPDR_AdminSettings2',
		'privacypolicy' => 'GPDR_ViewPrivacyPolicy',
		'privacyadmin' => 'GPDR_AdminPrivacyPolicy',
		'privacyadmin2' => 'GPDR_AdminPrivacyPolicy2',
        'exportdata' => 'GPDR_ExportData',
        'registeragreement' => 'GPDR_ReConfirmRegisterAgreement',
	);


	// Follow the sa
	if (isset($_GET['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';

	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		GPDR_ViewPrivacyPolicy();

}


function GPDR_AdminPrivacyPolicy()
{
	global $context, $mbname, $txt, $modSettings, $user_info, $sourcedir, $smcFunc, $boarddir, $webmaster_email;

	DoGPDRAdminTabs();

	isAllowedTo('admin_forum');

	if (function_exists("set_tld_regex"))
	    loadTemplate('gpdr2.1');
    else
        loadTemplate('gpdr2');

	$context['sub_template']  = 'privacypolicy_admin';

	$context['page_title'] = $txt['gpdr_title'] . ' - ' . $txt['gpdr_privacypolicy'];

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');


	$modSettings['disable_wysiwyg'] = !empty($modSettings['disable_wysiwyg']) || empty($modSettings['enableBBC']);


	$data = file_get_contents($boarddir . "/privacypolicy.txt");
	$data = str_replace("[business name]",$mbname,$data);
    $data = str_replace("[email address]",$webmaster_email,$data);
    //$data = str_replace("[date]",date("F j, Y, g:i a",$modSettings['gpdr_last_privacydate']),$data);


	// Needed for the WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Now create the editor.
	$editorOptions = array(
		'id' => 'privacypolicy',
		'value' => $data,
		'width' => '90%',
		'form' => 'cprofile',
		'labels' => array(
			'post_button' => $txt['gpdr_txt_update']
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];



}

function GPDR_AdminPrivacyPolicy2()
{
	global $scripturl, $txt, $sourcedir, $modSettings, $smcFunc, $user_info, $boarddir, $mbname, $webmaster_email;

	isAllowedTo('admin_forum');

	// If we came from WYSIWYG then turn it back into BBC regardless.
	if (!empty($_REQUEST['privacypolicy_mode']) && isset($_REQUEST['privacypolicy']) && !function_exists("set_tld_regex"))
	{
		require_once($sourcedir . '/Subs-Editor.php');

		$_REQUEST['privacypolicy'] = html_to_bbc($_REQUEST['privacypolicy']);

		// We need to unhtml it now as it gets done shortly.
		$_REQUEST['privacypolicy'] = un_htmlspecialchars($_REQUEST['privacypolicy']);

	}


	$privacypolicy = $smcFunc['htmlspecialchars']($_REQUEST['privacypolicy']);




	if ($privacypolicy == '')
		fatal_error($txt['gpdr_error_no_privacypolicy'],false);


    file_put_contents($boarddir . "/privacypolicy.txt",$privacypolicy);


    $data = $privacypolicy;
	$data = str_replace("[business name]",$mbname,$data);
    $data = str_replace("[email address]",$webmaster_email,$data);
    $data = str_replace("[date]",date("F j, Y, g:i a",time()),$data);

    // Save the date
    	updateSettings(
	array(
    'gpdr_last_privacydate' => time(),
    'policy_updated_' . $user_info['language'] => time(),
	'policy_' . $user_info['language'] => $data,
	));




	redirectexit('action=admin;area=gpdr;sa=privacyadmin');

}

function GPDR_AdminSettings()
{
	global $context,  $txt;
	isAllowedTo('admin_forum');

	if (function_exists("set_tld_regex"))
	    loadTemplate('gpdr2.1');
    else
        loadTemplate('gpdr2');

	$context['page_title'] = $txt['gpdr_title'] . ' - ' . $txt['gpdr_text_settings'];

	DoGPDRAdminTabs();

	$context['sub_template']  = 'gpdr_settings';

}

function GPDR_AdminSettings2()
{
	isAllowedTo('admin_forum');


	// Get the settings
	$gpdr_enable_privacy_policy = (int) isset($_REQUEST['gpdr_enable_privacy_policy']) ? 1 : 0;
	$gpdr_force_privacy_agree = (int) isset($_REQUEST['gpdr_force_privacy_agree']) ? 1 : 0;
    $gpdr_force_agreement_agree = (int) isset($_REQUEST['gpdr_force_agreement_agree']) ? 1 : 0;
    $gpdr_clear_memberinfo = (int) isset($_REQUEST['gpdr_clear_memberinfo']) ? 1 : 0;
    $gpdr_allow_export_userdata = (int) isset($_REQUEST['gpdr_allow_export_userdata']) ? 1 : 0;
	// Save the setting information
	updateSettings(
	array(
	'gpdr_enable_privacy_policy' => $gpdr_enable_privacy_policy,
    'gpdr_force_privacy_agree' => $gpdr_force_privacy_agree,
    'gpdr_force_agreement_agree' => $gpdr_force_agreement_agree,
    'gpdr_clear_memberinfo' => $gpdr_clear_memberinfo,
    'gpdr_allow_export_userdata' => $gpdr_allow_export_userdata,

	));

	redirectexit('action=admin;area=gpdr;sa=settings');

}


function GPDR_ViewPrivacyPolicy()
{
	global $txt, $context, $boarddir, $modSettings, $webmaster_email, $mbname, $smcFunc, $user_info, $settings;


	if (function_exists("set_tld_regex"))
	    loadTemplate('gpdr2.1');
    else
        loadTemplate('gpdr2');

	$data = file_get_contents($boarddir . "/privacypolicy.txt");

	$data = str_replace("[business name]",$mbname,$data);
    $data = str_replace("[email address]",$webmaster_email,$data);
    $data = str_replace("[date]",date("F j, Y, g:i a",$modSettings['gpdr_last_privacydate']),$data);



	$context['sub_template'] = 'view_privacypolicy';

	$context['page_title'] = $txt['gpdr_privacypolicy'];

	$context['privacy_policy_data'] = $data;


	// Save the users time for viewing the privacy policy if they agree save in theme settings
    if (isset($_REQUEST['save']))
    {

        if (isset($_REQUEST['decline']))
        {
            redirectexit('action=logout;' . $context['session_var'] . '=' . $context['session_id']);
        }
        else
        {

            $t = time();
            $smcFunc['db_query']('', "
                    REPLACE INTO {db_prefix}themes
                        (ID_MEMBER, ID_THEME, variable, value)
                    VALUES
                    (" . $user_info['id'] . "," . $settings['theme_id'] . ",'gpdr_policydate','$t')
                    ");

            cache_put_data('theme_settings-' . $settings['theme_id'] . ':' . $user_info['id'], null, 60);

           $smcFunc['db_query']('', "
                    REPLACE INTO {db_prefix}themes
                        (ID_MEMBER, ID_THEME, variable, value)
                    VALUES
                    (" . $user_info['id'] . ",1,'policy_accepted','$t')
                    ");

            // Redirect to the board index
            redirectexit();
        }
    }

}


function GPDR_ReConfirmRegisterAgreement()
{
	global $txt, $context, $boarddir, $modSettings, $webmaster_email, $mbname, $smcFunc, $user_info, $settings;


	if (function_exists("set_tld_regex"))
	    loadTemplate('gpdr2.1');
    else
        loadTemplate('gpdr2');

	$data = file_get_contents($boarddir . "/agreement.txt");

	$data = str_replace("[business name]",$mbname,$data);
    $data = str_replace("[email address]",$webmaster_email,$data);
    $data = str_replace("[date]",date("F j, Y, g:i a",$modSettings['gpdr_last_agreementdate']),$data);



	$context['sub_template'] = 'view_registrationagreement';

	$context['page_title'] = $txt['gpdr_registration_agreement'];

	$context['registrationagreement_data'] = $data;


	// Save the users time for viewing the agreement if they agree save in theme settings
    if (isset($_REQUEST['save']))
    {

        if (isset($_REQUEST['decline']))
        {
            redirectexit('action=logout;' . $context['session_var'] . '=' . $context['session_id']);
        }
        else
        {

            $t = time();
            $smcFunc['db_query']('', "
                    REPLACE INTO {db_prefix}themes
                        (ID_MEMBER, ID_THEME, variable, value)
                    VALUES
                    (" . $user_info['id'] . "," . $settings['theme_id'] . ",'gpdr_agreementdate','$t')
                    ");

            $smcFunc['db_query']('', "
                    REPLACE INTO {db_prefix}themes
                        (ID_MEMBER, ID_THEME, variable, value)
                    VALUES
                    (" . $user_info['id'] . ",1,'agreement_accepted','$t')
                    ");


            cache_put_data('theme_settings-' . $settings['theme_id'] . ':' . $user_info['id'], null, 60);

            // Redirect to the board index
            redirectexit();
        }
    }

}


function DoGPDRAdminTabs($overrideSelected = '')
{
	global $context, $txt;

	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['gpdr_title'],
			'description' => '',
			'tabs' => array(
				'settings' => array(
					'description' => $txt['gpdr_admin_settings_desc'],
				),
				'privacyadmin' => array(
					'description' => $txt['gpdr_admin_privacypolicy_desc'],
				),


			),
		);


}


function GPDR_CleanMemberInfo($users = array())
{
    global $smcFunc, $modSettings;

    if (empty($users))
        return;

    if (empty($modSettings['gpdr_clear_memberinfo']))
            return;


    foreach($users as $userID)
    {
        $userID = (int) $userID;

        $smcFunc['db_query']('', "
				UPDATE {db_prefix}messages
				SET
				  poster_name ='guest$userID',poster_email='',poster_ip='127.0.0.1', id_member = 0, modified_name = IF(modified_name IS NOT NULL, '', '')
				WHERE ID_MEMBER = $userID
                ");
    }


}

function GPDR_ExportData()
{
    global $smcFunc, $user_info, $txt, $context;

    is_not_guest();

    $memID = (int) $_REQUEST['u'];

    if ($memID != $user_info['id'])
            fatal_error($txt['gpdr_err_export_user'],false);

    $type = $_REQUEST['type'];

    if (empty($context['profile_fields']))
    $context['profile_fields'] = array();


    // Check what data we are exporting
    if ($type == 'profile')
    {

        $data = '';
        if (function_exists("set_tld_regex"))
        {
            $result = $smcFunc['db_query']('', "
                    SELECT
                    id_member,member_name,real_name,email_address,date_registered,posts,personal_text,
                    birthdate,website_title,website_url,signature
                     FROM {db_prefix}members
                    WHERE ID_MEMBER =  $memID
                    ");
            $row = $smcFunc['db_fetch_assoc']($result);

                $data = $txt['gpdr_profile_memid']  . ',' .  $txt['gpdr_profile_username'] . ','. $txt['gpdr_profile_displayname']  . ',';
                $data .= $txt['gpdr_profile_email'] . ',' . $txt['gpdr_profile_totalposts'] . ',' . $txt['gpdr_profile_dateregistered']  . ',';
                $data .=  $txt['gpdr_profile_birthdate']  . ',' . $txt['gpdr_profile_personaltext'] . ',' . $txt['gpdr_profile_websitetitle'] . ',' . $txt['gpdr_profile_websiteurl'] . ',' . $txt['gpdr_profile_signature']  . "\r\n";


                $data .= '"' . $row['id_member'] . '",';
                $data .= '"' . $row['member_name'] . '",';
                $data .= '"' . GPDR_FormatCSVData($row['real_name']) . '",';
                $data .= '"' . $row['email_address'] . '",';
                $data .= '"' . $row['posts'] . '",';
                $data .= '"' . date("F j, Y, g:i a",$row['date_registered']) . '",';
                $data .= '"' . $row['birthdate'] . '",';
                $data .= '"' . GPDR_FormatCSVData($row['personal_text']) . '",';
                $data .= '"' . GPDR_FormatCSVData($row['website_title']) . '",';
                $data .= '"' . $row['website_url'] . '",';
                $data .= '"' . GPDR_FormatCSVData($row['signature']) . '",';

                $data .= "\r\n";
        }
        else
        {



            $result = $smcFunc['db_query']('', "
                    SELECT
                    id_member,member_name,real_name,email_address,date_registered,posts,gender,personal_text,
                    birthdate,website_title,website_url,signature
                     FROM {db_prefix}members
                    WHERE ID_MEMBER =  $memID
                    ");
            $row = $smcFunc['db_fetch_assoc']($result);

                $data = $txt['gpdr_profile_memid']  . ',' .  $txt['gpdr_profile_username'] . ','. $txt['gpdr_profile_displayname']  . ',';
                $data .= $txt['gpdr_profile_email'] . ',' . $txt['gpdr_profile_totalposts'] . ',' . $txt['gpdr_profile_dateregistered']  . ',' . $txt['gpdr_profile_gender']  . ',';
                $data .=  $txt['gpdr_profile_birthdate']  . ',' . $txt['gpdr_profile_personaltext'] . ',' . $txt['gpdr_profile_websitetitle'] . ',' . $txt['gpdr_profile_websiteurl'] . ',' . $txt['gpdr_profile_signature']  . "\r\n";
                          $row['gender'] =  ($row['gender']== 2 ? $txt['female'] : ($row['gender'] == 1 ? $txt['male'] : ''));

                $data .= '"' . $row['id_member'] . '",';
                $data .= '"' . $row['member_name'] . '",';
                $data .= '"' . GPDR_FormatCSVData($row['real_name']) . '",';
                $data .= '"' . $row['email_address'] . '",';
                $data .= '"' . $row['posts'] . '",';
                $data .= '"' . date("F j, Y, g:i a",$row['date_registered']) . '",';
                $data .= '"' . $row['gender'] . '",';
                $data .= '"' . $row['birthdate'] . '",';
                $data .= '"' . GPDR_FormatCSVData($row['personal_text']) . '",';
                $data .= '"' . GPDR_FormatCSVData($row['website_title']) . '",';
                $data .= '"' . $row['website_url'] . '",';
                $data .= '"' . GPDR_FormatCSVData($row['signature']) . '",';

                $data .= "\r\n";
        }

			header("Pragma: no-cache");
			header('Content-Disposition: attachment; filename="MyProfile.csv";');
			header("Content-Length: " . strlen($data));
			header("Content-Transfer-Encoding: binary");
			header("Content-Type: application/force-download");
			echo $data;

			exit;

    }
    else
    {
        // Exporting Posts
        $startindex = (int) $_REQUEST['startindex'];
        $endindex = (int) $_REQUEST['endindex'];

        $result = $smcFunc['db_query']('', "
				SELECT COUNT(*) AS total
				 FROM {db_prefix}messages
				WHERE ID_MEMBER =  $memID AND ID_MSG >= $startindex and ID_MSG <= $endindex
                ");
        $row = $smcFunc['db_fetch_assoc']($result);

        if ($row['total'] > 1000)
                fatal_error($txt['gpdr_err_export_msg_limit'],false);


        $data = '';
        $result = $smcFunc['db_query']('', "
				SELECT subject,poster_time,body
				 FROM {db_prefix}messages
				WHERE ID_MEMBER =  $memID AND ID_MSG >= $startindex and ID_MSG <= $endindex ORDER BY id_msg DESC
                ");
            while($row = $smcFunc['db_fetch_assoc']($result))
            {
                $data .= $txt['gpdr_txt_message_subject']  . $row['subject'] . "\r\n";
                $data .= $txt['gpdr_txt_message_date'] . date("F j, Y, g:i a",$row['poster_time']) . "\r\n";
                $data .= $txt['gpdr_txt_message_body'] . $row['body'] . "\r\n";


                $data .= "\r\n";
            }


            header("Pragma: no-cache");
			header('Content-Disposition: attachment; filename="MyPostData.txt";');
			header("Content-Length: " . strlen($data));
			header("Content-Transfer-Encoding: binary");
			header("Content-Type: application/force-download");
			echo $data;

			exit;


    }


}

function GPDR_FormatCSVData($data)
{
    $data = str_replace('"',"'",$data);
    return $data;
}

?>