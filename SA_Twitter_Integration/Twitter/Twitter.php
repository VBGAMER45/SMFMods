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

function Twitter(){
    global $txt, $context;

	loadTemplate('Twitter');

	//Put all the subactions into an array
	$subActions = array(
	    'main' => 'twit_main',
		'connect' => 'twit_connect',
		'auto' => 'twit_auto',
		'connectlog' => 'twit_log',
		'syncc' => 'twit_sync',
		'usyncc' => 'twit_usync',
		'logsync' => 'twit_logsync',
	);
	
	// Default the sub-action'.
	$_REQUEST['area'] = isset($_REQUEST['area']) && isset($subActions[$_REQUEST['area']]) ? $_REQUEST['area'] : 'main';
	
	// Set title and default sub-action.
	$context['page_title'] = $txt['twittmain'];
	$context['sub_action'] = $_REQUEST['area'];

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['area']]();
}

function TwitterProfile(){
    global $twpic, $scripturl, $modSettings, $context;
	
	loadTemplate('Twitter');
	$context['sub_template']  = 'tpro';

	if(isset($_GET['doavatar'])){
	    
	    updateMemberData($_GET['u'], array('avatar' => $_SESSION['twpic']));
	    redirectexit('action=profile;area=tsettings;u='.$_GET['u'].';avatardone');
    }
}

function twit_logsync(){
    global $context;
	
	$context['sub_template']  = 'twlogsync';
    $context['default_username'] = &$_REQUEST['u'];
    $context['default_password'] = '';
}

function twit_usync(){
    global $user_info;
   
    checkSession('get');
   
    updateMemberData($user_info['id'], 
        array( 
		    'twitname' => '',
			'twitid' => '',
			'twitrn' => ''
	    )
	);
	
    update_themes_twitter_del('twit_pro', $user_info['id']);
   
    redirectexit('action=profile;u='.$user_info['id'].';success_unsync');
}

function twit_sync(){
    global $modSettings, $user_info;
   
    checkSession('get');
      
	$twitter_profile = ''.$_SESSION['twit_name'].'';
	
    updateMemberData($user_info['id'], 
        array( 
		    'twitname' => $_SESSION['twit_name'],
			'twitid' => $_SESSION['twit_id'],
			'twitrn' => $_SESSION['twit_sn']
	    )
	);

    update_themes_twitter($user_info['id'], 'twit_pro', $twitter_profile);
   
    unset($_SESSION['twit_name']);
    unset($_SESSION['twit_id']);
    unset($_SESSION['twit_sn']);
   
    redirectexit('action=profile;u='.$user_info['id'].';success_sync');
}

function twit_main(){
    global $context, $userid, $user_settings, $modSettings, $user_info, $sc, $txt, $realname,$username;
   
    twit_init();
   
    $_SESSION['twit_name'] = $username;
    $_SESSION['twit_id'] = $userid;
    $_SESSION['twit_sn'] = $realname;  

	if(empty($_SESSION['twit_name']) || empty($_SESSION['twit_id']) || empty($_SESSION['twit_sn'])){
        fatal_error($txt['twnocon'],false);
    }
	
    if ($context['user']['is_logged']){
		
		if (empty($user_settings['twitname'])){ 
            redirectexit('action=twitter;area=syncc;sesc='.$sc.'');
		}
		else{
		    redirectexit('action=profile;area=tsettings;u='.$user_info['id'].';doavatar');
		}
    }
    else{
        $context['twit_user'] = twit_USettings($userid,'twitid','twitid');
		
		if (empty($_SESSION['login_url']) && isset($_SESSION['old_url']) && strpos($_SESSION['old_url'], 'dlattach') === false && preg_match('~(board|topic)[=,]~', $_SESSION['old_url']) != 0)
		        $_SESSION['login_url'] = $_SESSION['old_url'];
   
        if ($context['twit_user']){  
	        redirectexit('action=twitter;area=connectlog');   
        }
        else{  
	        if(!empty($modSettings['requireAgreement'])){
	            redirectexit('action=twitter;area=connect;agree');
		    }
		    else{
		        redirectexit('action=twitter;area=connect');
		    }
        }
    }
}

function twit_log(){
    global $context, $scripturl, $modSettings, $sourcedir;
  
    $context['member_id'] = twit_USettings($_SESSION['twuserid'],'id_member','twitid');
    $context['member_pw'] = twit_USettings($context['member_id'],'passwd','id_member');
    $context['member_pws'] = twit_USettings($context['member_id'],'password_salt','id_member');
  
    $modSettings['cookieTime'] = 3153600;
  
    require_once($sourcedir.'/Subs-Auth.php');
    include_once($sourcedir.'/LogInOut.php');
    setLoginCookie(60 * $modSettings['cookieTime'], $context['member_id'], sha1($context['member_pw'].$context['member_pws']));

    unset($_SESSION['twit_name']);
    unset($_SESSION['twit_id']);
    unset($_SESSION['twit_sn']);
    
	/*uncomment following line to use with enotify*/
	//if ((!empty($_SESSION['login_url']) && strpos($_SESSION['login_url'], 'enotify')) || empty($_SESSION['login_url'])){
		    
	/*comment the following line to use with enotify*/
	if (empty($_SESSION['login_url'])){
	    
		$tw_log_url = !empty($modSettings['tw_app_log_url']) ? $modSettings['tw_app_log_url'] : $scripturl;
	    header('Location: '.$tw_log_url.'');
	}
	else{
	   
	    $temp = $_SESSION['login_url'];
	    $tw_log_url = !empty($modSettings['tw_app_log_url']) ? $modSettings['tw_app_log_url'] : $temp;
	    unset($_SESSION['login_url']);
	    header('Location: '.$tw_log_url.'');
	}

}

function twit_createRandomPassword() { 

    $chars = "abcdefghijkmnopqrstuvwxyz023456789"; 
    srand((double)microtime()*1000000); 
    $i = 0; 
    $pass = '' ; 

    while ($i <= 7) { 
        $num = rand() % 33; 
        $tmp = substr($chars, $num, 1); 
        $pass = $pass . $tmp; 
        $i++; 
    } 

    return $pass; 
} 

function twit_auto(){
    
	global $sourcedir, $txt, $url, $boarddir, $boarddir, $boardurl, $user_info, $modSettings, $context;
    
    $twit_user['real_name'] = twit_USettings($_SESSION['twit_name'],'real_name','real_name');
			
	if($twit_user['real_name']){
	    redirectexit('action=twitter;area=logsync;nt;u='.$_SESSION['twit_name'].'');
	}
	    
	$password = twit_createRandomPassword(); 
	
	$regOptions = array(
	    'interface' => 'guest',
        'auth_method' => 'password',
        'username' => $_SESSION['twit_name'],
        'email' => $_POST['email'],
        'require' => 'nothing',
        'password' => !empty($password) ? $password : '',
		'password_check' => !empty($password) ? $password : '',
		'password_salt' => substr(md5(mt_rand()), 0, 4),
        'check_password_strength' => false,
        'check_email_ban' => false,
        'extra_register_vars' => array('id_group' => !empty($modSettings['tw_admin_mem_groupe']) ? $modSettings['tw_admin_mem_groupe'] : '0',),
    );

    require_once($sourcedir . '/Subs-Members.php');
    $memberID = registerMember($regOptions);
	
	updateMemberData($memberID, 
	    array(
			'twitname' => $_SESSION['twit_name'],
		    'twitid' => $_SESSION['twit_id'],
		    'twitrn' => $_SESSION['twit_sn']
		)
	);
	
	$twitter_profile = ''.$_SESSION['twit_name'].'';
	
    update_themes_twitter($memberID, 'twit_pro', $twitter_profile);
	
	unset($_SESSION['twit_name']);
    unset($_SESSION['twit_id']);
    unset($_SESSION['twit_sn']);
	    
	$consumer_key = !empty($modSettings['tw_app_id']) ? $modSettings['tw_app_id'] : '';
    $consumer_secret = !empty($modSettings['tw_app_key']) ? $modSettings['tw_app_key'] : '';
	$twitterObjUnAuth = new EpiTwitter($modSettings['tw_app_id'], $modSettings['tw_app_key']);
	$url = $twitterObjUnAuth->getAuthenticateUrl();
		
    redirectexit($url);
}

function twit_connect(){
   global $sourcedir, $txt, $url, $boarddir, $boarddir, $boardurl, $user_info, $modSettings, $context;
    
    $context['sub_template']  = 'connect';
    
	if(empty($_SESSION['twit_name']) || empty($_SESSION['twit_id']) || empty($_SESSION['twit_sn'])){
       fatal_error($txt['twnocon'],false);
    }
	
	require_once($sourcedir .'/Subs-Package.php');
	
	if(isset($_GET['agree'])){
            
		loadLanguage('Login');
	    $context['sub_template']  = 'regtw_agree';
	            
		if (file_exists($boarddir . '/agreement.' . $user_info['language'] . '.txt'))
			$context['agreement'] = parse_bbc(file_get_contents($boarddir . '/agreement.' . $user_info['language'] . '.txt'), true, 'agreement_' . $user_info['language']);
		elseif (file_exists($boarddir . '/agreement.txt'))
			$context['agreement'] = parse_bbc(file_get_contents($boarddir . '/agreement.txt'), true, 'agreement');
		else
			$context['agreement'] = '';
    }
   else{
            
		if(!isset($_POST['accept_agreement']) && !empty($modSettings['requireAgreement'])){
			redirectexit('action=twitter;area=connect;agree');
		}		
    }
    if(isset($_GET['register'])){ 
	
	    $twit_user['real_name'] = twit_USettings($_POST['real_name'],'real_name','real_name');
			
	    if($twit_user['real_name']){
	         redirectexit('action=twitter;area=logsync;nt;u='.$_POST['real_name'].'');
	    }
	    
	    $regOptions = array(
	        'interface' => 'guest',
            'auth_method' => 'password',
            'username' => $_POST['real_name'],
            'email' => $_POST['email'],
            'require' => 'nothing',
            'password' => !empty($_POST['passwrd1']) ? $_POST['passwrd1'] : '',
		    'password_check' => !empty($_POST['passwrd2']) ? $_POST['passwrd2'] : '',
		    'password_salt' => substr(md5(mt_rand()), 0, 4),
            'check_password_strength' => false,
            'check_email_ban' => false,
            'extra_register_vars' => array('id_group' => !empty($modSettings['tw_admin_mem_groupe']) ? $modSettings['tw_admin_mem_groupe'] : '0',),
        );

        require_once($sourcedir . '/Subs-Members.php');
        $memberID = registerMember($regOptions);
	
	    updateMemberData($memberID, 
	        array(
			    'twitname' => $_SESSION['twit_name'],
		        'twitid' => $_SESSION['twit_id'],
		        'twitrn' => $_SESSION['twit_sn']
		    )
	    );
	
	    $twitter_profile = ''.$_SESSION['twit_name'].'';
	
        update_themes_twitter($memberID, 'twit_pro', $twitter_profile);
	
	    unset($_SESSION['twit_name']);
        unset($_SESSION['twit_id']);
        unset($_SESSION['twit_sn']);
	    
		$consumer_key = !empty($modSettings['tw_app_id']) ? $modSettings['tw_app_id'] : '';
        $consumer_secret = !empty($modSettings['tw_app_key']) ? $modSettings['tw_app_key'] : '';
	    $twitterObjUnAuth = new EpiTwitter($modSettings['tw_app_id'], $modSettings['tw_app_key']);
	    $url = $twitterObjUnAuth->getAuthenticateUrl();
		
		redirectexit($url);
    }
}
?>