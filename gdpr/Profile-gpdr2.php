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

function GPDR_ExportProfile($memID)
{
	global $scripturl, $txt, $db_prefix, $context, $smcFunc, $user_info, $sourcedir, $modSettings;

    if (empty($context['profile_fields']))
            $context['profile_fields'] = array();

	// Load the language files
	if (loadlanguage('gpdr') == false)
		loadLanguage('gpdr','english');




	$txt['gpdr_txt_user_exportdata2'] = str_replace("%link",$scripturl . '?action=gpdr;sa=exportdata;type=profile&u=' . $context['id_member'], $txt['gpdr_txt_user_exportdata2']);



	$context['profile_prehtml'] = '<strong>' . $txt['gpdr_txt_export_information'] . '</strong><br />
' . $txt['gpdr_txt_user_exportdata2'] .'
<hr width="100%" size="1" class="hrcolor clear" />
<strong>' . $txt['gpdr_txt_message_exportdata'] . '</strong><br />
' . $txt['gpdr_txt_message_exportdata2'].'<br>
' . $txt['gpdr_txt_message_startid'] . '<input type="text" name="startindex" size="8" value="0" />
' . $txt['gpdr_txt_message_endid'] . '<input type="text" name="endindex" size="8" value="' . $modSettings['maxMsgID'] . '" />

';


	// Template
	$context['profile_custom_submit_url'] = $scripturl . '?action=gpdr;sa=exportdata;type=posts;u=' . $memID;
	$context['page_desc'] =  $txt['gpdr_txt_export_information'];
	$context['sub_template'] = 'edit_options';
	$context['submit_button_text'] = $txt['gpdr_txt_exportdata'];
}

?>