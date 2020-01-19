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
// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


$schema_type = ' ENGINE=MyISAM';



// Insert the settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('gpdr_last_agreementdate', '" . time() . "'),
('gpdr_last_privacydate', '" . time() . "'),
('gpdr_clear_memberinfo', '1'),
('gpdr_enable_privacy_policy', '1'),
('gpdr_force_privacy_agree', '0'),
('gpdr_force_agreement_agree', '0'),
('gpdr_allow_export_userdata', '1')
");


// Add Package Servers
$smcFunc['db_query']('', "DELETE FROM {db_prefix}package_servers WHERE url = 'https://www.smfhacks.com'");
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'https://www.smfhacks.com')");

// SMF 2.0.16
global $user_info;
if (empty($modSettings['policy_updated_' . $user_info['language']]))
{
    global $boarddir, $mbname, $webmaster_email;
	$data = file_get_contents($boarddir . "/privacypolicy.txt");
	
	if (empty($modSettings['gpdr_last_privacydate']))
		$modSettings['gpdr_last_privacydate'] = time();

	$data = str_replace("[business name]",$mbname,$data);
    $data = str_replace("[email address]",$webmaster_email,$data);
    $data = str_replace("[date]",date("F j, Y, g:i a",$modSettings['gpdr_last_privacydate']),$data);
    
    
    
     	updateSettings(
	array(
    'policy_updated_' . $user_info['language'] => $modSettings['gpdr_last_privacydate'],
	'policy_' . $user_info['language'] => $data,
	));

    
    
}

// GDPR 
$modSettings['disableQueryCheck'] = 1;

$smcFunc['db_query']('', "REPLACE INTO {db_prefix}themes
                        (ID_MEMBER, ID_THEME, variable, value)
                        SELECT ID_MEMBER, 1, 'policy_accepted', value FROM {db_prefix}themes WHERE 
                        variable = 'gpdr_policydate'

                        "         
);


$smcFunc['db_query']('', "REPLACE INTO {db_prefix}themes
                        (ID_MEMBER, ID_THEME, variable, value)
                        SELECT ID_MEMBER, 1, 'agreement_accepted', value FROM {db_prefix}themes WHERE 
                        variable = 'gpdr_agreementdate'

                        "         
);




?>