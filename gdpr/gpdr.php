<?php
/*
GDPR Helper
Version 1.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2018 SMFHacks.com

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

	$currentVersion = '1.0.4';

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
        'profile' => 'GPDR_ProfileExportData',
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
	adminIndex('gpdr_settings');

	loadTemplate('gpdr');


	$context['sub_template']  = 'privacypolicy_admin';

	$context['page_title'] = $txt['gpdr_title'] . ' - ' . $txt['gpdr_privacypolicy'];

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	$data = file_get_contents($boarddir . "/privacypolicy.txt");
	$data = str_replace("[business name]",$mbname,$data);
    $data = str_replace("[email address]",$webmaster_email,$data);
    //$data = str_replace("[date]",date("F j, Y, g:i a",$modSettings['gpdr_last_privacydate']),$data);


	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');
	$context['post_box_name'] = 'privacypolicy';
	$context['post_form'] = 'cprofile';
	$context['privacy_policy_data'] = $data;



}

function GPDR_AdminPrivacyPolicy2()
{
	global $scripturl, $txt, $sourcedir, $modSettings, $func, $user_info, $boarddir;

	isAllowedTo('admin_forum');


	$privacypolicy = $func['htmlspecialchars']($_REQUEST['privacypolicy']);


	if ($privacypolicy == '')
		fatal_error($txt['gpdr_error_no_privacypolicy'],false);


    file_put_contents($boarddir . "/privacypolicy.txt",$privacypolicy);

    // Save the date
    	updateSettings(
	array(
    'gpdr_last_privacydate' => time(),

	));



	redirectexit('action=gpdr;sa=privacyadmin');

}

function GPDR_AdminSettings()
{
	global $context,  $txt;
	isAllowedTo('admin_forum');
	adminIndex('gpdr_settings');

	loadTemplate('gpdr');

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

	redirectexit('action=gpdr;sa=settings');

}


function GPDR_ViewPrivacyPolicy()
{
	global $txt, $context, $boarddir, $modSettings, $webmaster_email, $mbname, $smcFunc, $user_info, $settings, $ID_MEMBER, $db_prefix;


	loadTemplate('gpdr');

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
            global $sc;
            redirectexit('action=logout;sesc=' . $sc);
        }
        else
        {
            $t = time();
            db_query("
                    REPLACE INTO {$db_prefix}themes
                        (ID_MEMBER, ID_THEME, variable, value)
                    VALUES
                    (" . $ID_MEMBER . "," . $settings['theme_id'] . ",'gpdr_policydate','$t')
                    ", __FILE__, __LINE__);

            cache_put_data('theme_settings-' . $settings['theme_id'] . ':' . $ID_MEMBER, null, 60);

            // Redirect to the board index
            redirectexit();
        }
    }

}


function GPDR_ReConfirmRegisterAgreement()
{
	global $txt, $context, $boarddir, $modSettings, $webmaster_email, $mbname, $db_prefix, $user_info, $settings, $ID_MEMBER;


	loadTemplate('gpdr');

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
            global $sc;
            redirectexit('action=logout;sesc=' . $sc);
        }
        else
        {
            $t = time();
            db_query("
				REPLACE INTO {$db_prefix}themes
					(ID_MEMBER, ID_THEME, variable, value)
				VALUES
                (" . $ID_MEMBER . "," . $settings['theme_id'] . ",'gpdr_agreementdate','$t')
                ", __FILE__, __LINE__);

            cache_put_data('theme_settings-' . $settings['theme_id'] . ':' . $ID_MEMBER, null, 60);

            // Redirect to the board index
            redirectexit();
        }
    }

}


function DoGPDRAdminTabs($overrideSelected = '')
{
	global $context, $txt, $scripturl;


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

	$tmpSA = '';
	if (!empty($overrideSelected))
	{
		$_REQUEST['sa'] = $overrideSelected;

	}


	// Create the tabs for the template.
	$context['admin_tabs'] = array(
		'title' =>$txt['gpdr_title'],
		'description' => '',
		'tabs' => array(),
	);
	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['gpdr_text_settings'],
			'description' =>  $txt['gpdr_admin_settings_desc'],
			'href' => $scripturl . '?action=gpdr;sa=settings',
			'is_selected' => $_REQUEST['sa'] == 'settings',
		);

	$context['admin_tabs']['tabs'][] = array(
			'title' => $txt['gpdr_privacypolicy'],
			'description' => $txt['gpdr_admin_privacypolicy_desc'],
			'href' => $scripturl . '?action=gpdr;sa=privacyadmin',
			'is_selected' => $_REQUEST['sa'] == 'privacyadmin',
		);



	if (!empty($overrideSelected))
	{
		$_REQUEST['sa'] = $tmpSA;
	}

	$context['admin_tabs']['tabs'][count($context['admin_tabs']['tabs']) - 1]['is_last'] = true;



}


function GPDR_CleanMemberInfo($users = array())
{
    global $db_prefix, $modSettings;

    if (empty($users))
        return;

    if (empty($modSettings['gpdr_clear_memberinfo']))
            return;


    foreach($users as $userID)
    {
        $userID = (int) $userID;

        db_query("
				UPDATE {$db_prefix}messages
				SET
				  postername ='guest$userID',posteremail='',posterip='127.0.0.1', id_member = 0, modifiedname = IF(modifiedname IS NOT NULL, '', '')
				WHERE ID_MEMBER = $userID
                ", __FILE__, __LINE__);
    }


}

function GPDR_ExportData()
{
    global $db_prefix, $ID_MEMBER, $txt;

    is_not_guest();

    $memID = (int) $_REQUEST['u'];

    if ($memID != $ID_MEMBER)
            fatal_error($txt['gpdr_err_export_user'],false);

    $type = $_REQUEST['type'];

    // Check what data we are exporting
    if ($type == 'profile')
    {

        $result = db_query("
				SELECT
				id_member,membername,realname,emailaddress,dateregistered,posts,gender,personaltext,
				birthdate,websitetitle,websiteurl,signature
				 FROM {$db_prefix}members
				WHERE ID_MEMBER =  $memID
                ", __FILE__, __LINE__);
        $row = mysql_fetch_assoc($result);

			$data = $txt['gpdr_profile_memid']  . ',' .  $txt['gpdr_profile_username'] . ','. $txt['gpdr_profile_displayname']  . ',';
			$data .= $txt['gpdr_profile_email'] . ',' . $txt['gpdr_profile_totalposts'] . ',' . $txt['gpdr_profile_dateregistered']  . ',' . $txt['gpdr_profile_gender']  . ',';
			$data .=  $txt['gpdr_profile_birthdate']  . ',' . $txt['gpdr_profile_personaltext'] . ',' . $txt['gpdr_profile_websitetitle'] . ',' . $txt['gpdr_profile_websiteurl'] . ',' . $txt['gpdr_profile_signature']  . "\r\n";
            //new line

            $row['gender'] =  ($row['gender']== 2 ? $txt['female'] : ($row['gender'] == 1 ? $txt['male'] : ''));

			$data .= '"' . $row['id_member'] . '",';
			$data .= '"' . $row['membername'] . '",';
			$data .= '"' . GPDR_FormatCSVData($row['realname']) . '",';
			$data .= '"' . $row['emailaddress'] . '",';
			$data .= '"' . $row['posts'] . '",';
			$data .= '"' . date("F j, Y, g:i a",$row['dateregistered']) . '",';
			$data .= '"' . $row['gender'] . '",';
			$data .= '"' . $row['birthdate'] . '",';
			$data .= '"' . GPDR_FormatCSVData($row['personaltext']) . '",';
			$data .= '"' . GPDR_FormatCSVData($row['websitetitle']) . '",';
			$data .= '"' . $row['websiteurl'] . '",';
			$data .= '"' . GPDR_FormatCSVData($row['signature']) . '",';

			$data .= "\r\n";


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

        $result = db_query("
				SELECT COUNT(*) AS total
				 FROM {$db_prefix}messages
				WHERE ID_MEMBER =  $memID AND ID_MSG >= $startindex and ID_MSG <= $endindex
                ", __FILE__, __LINE__);
        $row = mysql_fetch_assoc($result);

        if ($row['total'] > 1000)
                fatal_error($txt['gpdr_err_export_msg_limit'],false);


        $data = '';
        $result = db_query("
				SELECT subject,postertime,body
				 FROM {$db_prefix}messages
				WHERE ID_MEMBER =  $memID AND ID_MSG >= $startindex and ID_MSG <= $endindex ORDER BY id_msg DESC
                ", __FILE__, __LINE__);
            while($row = mysql_fetch_assoc($result))
            {
                $data .= $txt['gpdr_txt_message_subject']  . $row['subject'] . "\r\n";
                $data .= $txt['gpdr_txt_message_date'] . date("F j, Y, g:i a",$row['postertime']) . "\r\n";
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

function GPDR_ProfileExportData()
{
    global $scripturl, $context, $modSettings, $txt;

    loadTemplate('gpdr');

    $context['profile_MEMID'] = (int) $_REQUEST['u'];

    $txt['gpdr_txt_user_exportdata2'] = str_replace("%link",$scripturl . '?action=gpdr;sa=exportdata;type=profile&u=' . $context['profile_MEMID'], $txt['gpdr_txt_user_exportdata2']);



	$context['profile_prehtml'] = '<strong>' . $txt['gpdr_txt_export_information'] . '</strong><br />
' . $txt['gpdr_txt_user_exportdata2'] .'
<hr width="100%" size="1" class="hrcolor clear" />
<strong>' . $txt['gpdr_txt_message_exportdata'] . '</strong><br />
' . $txt['gpdr_txt_message_exportdata2'].'<br>
' . $txt['gpdr_txt_message_startid'] . '<input type="text" name="startindex" size="8" value="0" />
' . $txt['gpdr_txt_message_endid'] . '<input type="text" name="endindex" size="8" value="' . $modSettings['maxMsgID'] . '" />

';



	$context['sub_template'] = 'profile_export';

	$context['page_title'] = $txt['gpdr_txt_export_information'];


}


?>