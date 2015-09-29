<?php
/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://www.sa-mods.info
 *
 * The Initial Developer of the Original Code is
 * wayne Mankertz.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 * ***** END LICENSE BLOCK ***** */
if (!defined('SMF'))
	die('Hacking attempt...');	

function GPlus(){
    global $txt, $modSettings, $context;

	loadTemplate('GPlus');

	if(empty($modSettings['gp_app_enabled']))
	    fatal_lang_error('gp__app_error1',false);
		
	$subActions = array(
	    'main' => 'gplus_main',
		'connect' => 'gplus_connect',
		'auto' => 'gplus_connectAuto',
		'connectlog' => 'gplus_connectlog',
		'sync' => 'gplus_sync',
		'unsync' => 'gplus_unsync',
		'logsync' => 'gplus_logsync',
	);
	
	$_REQUEST['area'] = isset($_REQUEST['area']) && isset($subActions[$_REQUEST['area']]) ? $_REQUEST['area'] : 'main';
	
	$context['page_title'] = $txt['gp_googplus'];
	$context['sub_action'] = $_REQUEST['area'];

	$subActions[$_REQUEST['area']]();
}

function gplus_Profile(){
    global $twpic, $scripturl, $modSettings, $context;
	
	loadTemplate('GPlus');
	$context['sub_template']  = 'gppro';

	if(isset($_GET['gpdoavatar'])){
	    
		if(empty($_SESSION['gplus']['pic']))
		    fatal_error('you have no avatar associated with this google account',false);
			
	    $_SESSION['gplus']['pic'] = str_replace('https','http',$_SESSION['gplus']['pic']);
	    updateMemberData($_GET['u'], array('avatar' => $_SESSION['gplus']['pic']));
	    redirectexit('action=profile;area=gsettings;u='.$_GET['u'].';avatardone');
    }
}

function gplus_logsync(){
    global $context;
	
	$context['sub_template']  = 'gplus_logsync';
    $context['default_username'] = &$_REQUEST['u'];
    $context['default_password'] = '';
}

function gplus_sync(){
    global $user_info;
    
	checkSession('get');
	
	$gdata = $_SESSION['gplusdata'];
    $_SESSION['gplus']['id'] = $gdata['id'];
	$_SESSION['gplus']['name'] = $gdata['name'];
	
	updateMemberData($user_info['id'], 
	    array(
		    'gpid' => $_SESSION['gplus']['id'],
			'gpname' => $_SESSION['gplus']['name'],			
		)
	);
	
	redirectexit('action=profile');   
}

function gplus_unsync(){
    global $user_info;
    
	checkSession('get');
	
	updateMemberData($user_info['id'], 
	    array(
		    'gpid' => '',
			'gpname' => '',
		)
	);
	
	redirectexit('action=profile');   
}

function gplus_main(){
    global $context, $sc, $user_info, $user_settings, $modSettings;
   
    gplus_init_auth();

	if (!empty($_SESSION['gplusdata']) && isset($_REQUEST['auth']) && $_REQUEST['auth'] == 'done') {
	
	    $me = !empty($_SESSION['gplusdata']) ? $_SESSION['gplusdata'] : '';
	    $_SESSION['gplus']['id'] = $me['id'];
   
        if ($context['user']['is_logged']){
		    if (empty($user_settings['gpid'])){ 
                redirectexit('action=gplus;area=sync;sesc='.$sc.'');
		    }
		    else{
		        redirectexit('action=profile;area=gsettings;u='.$user_info['id'].';gpdoavatar');
		    }
        }
        else{
            
			$member_load = gplus_loadUser($_SESSION['gplus']['id'],'gpid');
		
		    if (empty($_SESSION['login_url']) && isset($_SESSION['old_url']) && strpos($_SESSION['old_url'], 'dlattach') === false && preg_match('~(board|topic)[=,]~', $_SESSION['old_url']) != 0)
		        $_SESSION['login_url'] = $_SESSION['old_url'];
   
            if ($member_load['gpid']){  
	            redirectexit('action=gplus;area=connectlog');   
            }
            else{  
	            if(!empty($modSettings['requireAgreement'])){
				    $mode = empty($modSettings['gp_reg_auto']) ? 'connect' : 'auto';
	                redirectexit('action=gplus;area='.$mode.';agree');
		        }
		        else{
				    $mode = empty($modSettings['gp_reg_auto']) ? 'connect' : 'auto';
		            redirectexit('action=gplus;area='.$mode.'');
		        }
            }
        }
	}else{
	    fatal_lang_error('gp__app_error2',false);
	}
}

function gplus_connectlog(){
	global $scripturl, $modSettings, $sourcedir;
  
    $_SESSION['gplus']['id'] = $_SESSION['gplus']['idm'];
	
    if(empty($_SESSION['gplus']['id']))
	    fatal_lang_error('gp__app_error3',false);
		
	$member_load = gplus_loadUser($_SESSION['gplus']['id'],'gpid');
	
    $modSettings['cookieTime'] = 3153600;
  
    require_once($sourcedir.'/Subs-Auth.php');
    include_once($sourcedir.'/LogInOut.php');
    setLoginCookie(60 * $modSettings['cookieTime'], $member_load['id_member'], sha1($member_load['passwd'].$member_load['password_salt']));
    
	unset($_SESSION['gplus']['id']);
    unset($_SESSION['gplus']['name']);
    unset($_SESSION['gplusdata']);
	
	$gplus_log_url = !empty($modSettings['gp_app_custon_logurl']) ? $modSettings['gp_app_custon_logurl'] : $scripturl;
	redirectexit($gplus_log_url);
}

function gplus_createRandomPassword($length=8, $strength=8) { 

    $vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength & 2) {
		$vowels .= "AEUY";
	}
	if ($strength & 4) {
		$consonants .= '23456789';
	}
	if ($strength & 8) {
		$consonants .= '@#$%';
	}
 
	$password = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
} 

function gplus_connectAuto(){
    global $modSettings, $sourcedir, $context;
	
    $gdata = !empty($_SESSION['gplusdata']) ? $_SESSION['gplusdata'] : '';
    $_SESSION['gplus']['id'] = $gdata['id'];
	$_SESSION['gplus']['name'] = $gdata['name'];
	$_SESSION['gplus']['email'] = $gdata['email'];
	
	if(empty($gdata))
	    fatal_lang_error('gp__app_error3',false);
		
	$member_load = gplus_loadUser($_SESSION['gplus']['name'],'real_name');
		
	if($member_load['real_name'])
	    redirectexit('action=gplus;area=logsync;nt;u='.$member_load['real_name'].'');
	
	$pass = gplus_createRandomPassword();
	
	$regOptions = array(
	    'interface' => 'guest',
        'auth_method' => 'password',
        'username' => $_SESSION['gplus']['name'],
        'email' => $_SESSION['gplus']['email'],
        'require' => 'nothing',
        'password' => $pass,
		'password_check' => $pass,
		'password_salt' => substr(md5(mt_rand()), 0, 4),
        'send_welcome_email' => !empty($modSettings['send_welcomeEmail']),
        'check_password_strength' => false,
        'check_email_ban' => false,
        'extra_register_vars' => array('id_group' => !empty($modSettings['gp_app_detait_gid']) ? $modSettings['gp_app_detait_gid'] : '0',),
    );

    require_once($sourcedir . '/Subs-Members.php');
    $memberID = registerMember($regOptions);
		
	updateMemberData($memberID, 
	    array(
		    'gpid' => $_SESSION['gplus']['id'],
		    'gpname' => $_SESSION['gplus']['name'],		
		)
	);
		
    redirectexit('action=gplus;auth=done');
}

function gplus_connect(){
    global $modSettings, $sourcedir, $context;
    
	$context['sub_template']  = 'gplus_cconnect';

    $gdata = !empty($_SESSION['gplusdata']) ? $_SESSION['gplusdata'] : '';
    $_SESSION['gplus']['id'] = $gdata['id'];
	$_SESSION['gplus']['name'] = $gdata['name'];
	
	if(empty($gdata))
	    fatal_lang_error('gp__app_error3',false);
    
	gplus_do_agree();
	
	if(isset($_REQUEST['register'])){  
	    
		$member_load = gplus_loadUser($_POST['real_name'],'real_name');
		
		if($member_load['real_name'])
	        redirectexit('action=gplus;area=logsync;nt;u='.$member_load['real_name'].'');

	    $regOptions = array(
	        'interface' => 'guest',
            'auth_method' => 'password',
            'username' => $_POST['real_name'],
            'email' => $_POST['email'],
            'require' => 'nothing',
            'password' => !empty($_POST['passwrd1']) ? $_POST['passwrd1'] : '',
		    'password_check' => !empty($_POST['passwrd2']) ? $_POST['passwrd2'] : '',
		    'password_salt' => substr(md5(mt_rand()), 0, 4),
			'send_welcome_email' => !empty($modSettings['send_welcomeEmail']),
            'check_password_strength' => false,
            'check_email_ban' => false,
            'extra_register_vars' => array('id_group' => !empty($modSettings['gp_app_detait_gid']) ? $modSettings['gp_app_detait_gid'] : '0',),
        );

        require_once($sourcedir . '/Subs-Members.php');
        $memberID = registerMember($regOptions);
		
	    updateMemberData($memberID, 
	        array(
		        'gpid' => $_SESSION['gplus']['id'],
				'gpname' => $_SESSION['gplus']['name'],		
		    )
	    );
		
        redirectexit('action=gplus;auth=done');
    }
}

function gplus_do_agree(){
    global $sourcedir, $context, $boarddir, $boardurl, $user_info, $modSettings;
	
	require_once($sourcedir .'/Subs-Package.php');
	
	if(isset($_GET['agree'])){
            
		loadLanguage('Login');
	    $context['sub_template']  = 'gplus_agree';
	            
		if (file_exists($boarddir . '/agreement.' . $user_info['language'] . '.txt'))
	        $context['agreement'] = parse_bbc(fetch_web_data($boardurl . '/agreement.' . $user_info['language'] . '.txt'), true, 'agreement_' . $user_info['language']);
	    elseif (file_exists($boarddir . '/agreement.txt'))
	        $context['agreement'] = parse_bbc(fetch_web_data($boardurl . '/agreement.txt'), true, 'agreement');
	    else
	        $context['agreement'] = '';
    }
    else{
            
		if(!isset($_POST['accept_agreement']) && !empty($modSettings['requireAgreement']))
			redirectexit('action=gplus;area=connect;agree');	
    }
}
?>