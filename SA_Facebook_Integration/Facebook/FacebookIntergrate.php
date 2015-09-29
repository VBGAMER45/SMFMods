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
	
class SAFacebookintergrate
{  

	private $subActions = array();
	
	protected $user_info_fbid;
	protected $user_info_fbname;
	protected $user_info_fbemail;
	protected $user_info_fbuname;
	protected $user_info_fbuname1;
	
	protected function __construct() {
	
	    global $FaceBookEmail, $FacebookName, $FaceBookUsername, $FaceBookUsername1, $FacebookId;
		
		SAFacebookhooks::face_init();
		
		$this->user_info_fbid = $FacebookId;
		$this->user_info_fbname = $FacebookName;
		$this->user_info_fbemail = $FaceBookEmail;
		$this->user_info_fbuname = $FaceBookUsername;
		$this->user_info_fbuname1 = $FaceBookUsername1;
		
		$this->subActions = array(
	        'main' => 'fb_main',
		    'connect' => 'fb_connect',
			'auto' => 'fb_reg_auto',
		    'connectlog' => 'fb_log',
		    'syncc' => 'fb_sync',
		    'usyncc' => 'fb_usync',
		    'fbbp' => 'fb_bypass',
		    'logsync' => 'fb_logsync',
	    );
	}
	
    public function Facebookint(){
        
		global $fb_object, $fb_hook_object, $context;

	    loadTemplate('Facebook');
        $_SESSION['safbKeys'] = false;
		$fb_object = new SAFacebookintergrate();
		
		if(!$fb_hook_object->modSettings['fb_app_enabled'])
		    redirectexit();
			
	    $_REQUEST['area'] = isset($_REQUEST['area']) && isset($fb_object->subActions[$_REQUEST['area']]) ? $_REQUEST['area'] : 'main';
	
	    $context['page_title'] = $fb_hook_object->txt['fb_main5'];
	    $context['sub_action'] = $_REQUEST['area'];

	    call_user_func(array('SAFacebookintergrate', $fb_object->subActions[$_REQUEST['area']])); 
    }
    
	public function fb_logsync(){
  
        global $context;
  
        $context['sub_template']  = 'fblogsync';
        $context['default_username'] = &$_REQUEST['u'];
        $context['default_password'] = '';
    }

    public function fb_bypass(){
        
		global $loginUrl, $context;
  
        $context['sub_template']  = 'fbbp';
    }

    public function fb_usync(){
        
		global $fb_hook_object;
   
        checkSession('get');
   
        updateMemberData($fb_hook_object->user_info_id, 
            array( 
		        'fbname' => '',
			    'fbid' => '',
	        )
	    );
	
        $fb_hook_object->update_themes_face_del('face_pro', $fb_hook_object->user_info_id);
   
        redirectexit('action=profile;u='.$fb_hook_object->user_info_id.';facebook_unsync');

    }

    public function fb_sync(){
        
		global $fb_object, $fb_hook_object;
	 
		if(empty($fb_object->user_info_fbid)){fatal_error($fb_hook_object->txt['fb_no_connections'],false);}
	 
	    $face_profile = 'http://facebook.com/profile.php?id='.$fb_object->user_info_fbid.'';
   
        updateMemberData($fb_hook_object->user_info_id, 
            array( 
		        'fbname' => $fb_object->user_info_fbname,
			    'fbid' => $fb_object->user_info_fbid,
	        )
	    );

        $fb_hook_object->update_themes_face($fb_hook_object->user_info_id, 'face_pro', $face_profile);
	   
        redirectexit('action=profile;u='.$fb_hook_object->user_info_id.';facebook_sync');
 
    }

    public function fb_main(){
        
		global $context, $fb_hook_object, $fb_object, $sc, $smcFunc, $modSettings;
   
		if(empty($fb_object->user_info_fbid)){fatal_error($fb_hook_object->txt['fb_no_connections'],false);}
		
        if ($context['user']['is_logged']){
            redirectexit('action=facebookintegrate;area=syncc;sesc='.$sc.'');
        }
        else{
   
	        $face_user['id_member'] = $fb_hook_object->face_USettings($fb_object->user_info_fbid,'id_member','fbid');
	  
            if($face_user['id_member']){  
	  
	        if (empty($_SESSION['login_url']) && isset($_SESSION['old_url']) && strpos($_SESSION['old_url'], 'dlattach') === false && preg_match('~(board|topic)[=,]~', $_SESSION['old_url']) != 0)
		        $_SESSION['login_url'] = $_SESSION['old_url'];
		
	            redirectexit('action=facebookintegrate;area=connectlog');   
            }
            else{  
			    $mode = !empty($fb_hook_object->modSettings['fb_reg_auto']) ? 'auto' : 'connect';
	            if(!empty($fb_hook_object->modSettings['requireAgreement'])){
	                redirectexit('action=facebookintegrate;area='.$mode.';agree');
		        }
		        else{
		            redirectexit('action=facebookintegrate;area='.$mode.'');
		        }
            }
        } 
    }

    public function fb_log(){
        
		global $fb_hook_object, $smcFunc, $fb_object, $user_info, $scripturl, $modSettings, $sourcedir;
		
		$face_userid['id_member'] = $fb_hook_object->face_USettings($fb_object->user_info_fbid,'id_member','fbid');
        $face_pass['passwd'] = $fb_hook_object->face_USettings($face_userid['id_member'],'passwd','id_member');
        $face_passsalt['password_salt'] = $fb_hook_object->face_USettings($face_userid['id_member'],'password_salt','id_member');
  
        $modSettings['cookieTime'] = 3153600;
  
        require_once($sourcedir.'/Subs-Auth.php');
        include_once($sourcedir.'/LogInOut.php');
        setLoginCookie(60 * $modSettings['cookieTime'], $face_userid['id_member'], sha1($face_pass['passwd'].$face_passsalt['password_salt']));
  
        $face_pwp['fbpw'] = $fb_hook_object->face_USettings($face_userid['id_member'],'fbpw','id_member');
  	
        if(!empty($face_pwp['fbpw'])){setcookie("pwdone", 1);}else{setcookie("pwdone", 0);}
	        
			if (empty($_SESSION['login_url'])){
	           
			    $fb_log_url = !empty($fb_hook_object->modSettings['fb_log_url']) ? $fb_hook_object->modSettings['fb_log_url'] : $fb_hook_object->scripturl;
				header('Location: '.$fb_log_url.'');	
            }
		    else{
	           
      			$temp = $_SESSION['login_url'];
			    $fb_log_url = !empty($fb_hook_object->modSettings['fb_log_url']) ? $fb_hook_object->modSettings['fb_log_url'] : $temp;
	            unset($_SESSION['login_url']);
				header('Location: '.$fb_log_url.'');	
            }
			
    }

    public function fb_connect(){
        
		global $sourcedir, $fb_hook_object, $fb_object, $context;
    
        $context['sub_template']  = 'fbconnect';
  
        if(empty($fb_object->user_info_fbid)){fatal_error($fb_hook_object->txt['fb_no_connections'],false);}

        $fb_object->fb_do_agree();
        $fb_object->fb_do_custom();
        $fb_object->fb_do_register();
    }
	
	public static function fbc_custom_regfeild_check(){
        
		global $fb_object,$fb_hook_object, $smcFunc;

        $request = $smcFunc['db_query']('', '
	        SELECT col_name, field_name, field_type, field_length, mask, show_reg
	        FROM {db_prefix}custom_fields
	        WHERE active = {int:is_active}',
	        array(
	           'is_active' => 1,
	        )
	    );
	    $custom_field_errors = array();
	    while ($row = $smcFunc['db_fetch_assoc']($request))
	    {
		    // Don't allow overriding of the theme variables.
		    if (isset($regOptions['theme_vars'][$row['col_name']]))
			    unset($regOptions['theme_vars'][$row['col_name']]);

		    // Not actually showing it then?
		    if (!$row['show_reg'])
			    continue;

		    // We only care for text fields as the others are valid to be empty.
		    if (!in_array($row['field_type'], array('check', 'select', 'radio')))
		    {
			    $value = isset($_POST['customfield'][$row['col_name']]) ? trim($_POST['customfield'][$row['col_name']]) : '';
			    // Is it too long?
			    if ($row['field_length'] && $row['field_length'] < $smcFunc['strlen']($value))
				    $custom_field_errors[] = array('custom_field_too_long', array($row['field_name'], $row['field_length']));

			    // Any masks to apply?
			    if ($row['field_type'] == 'text' && !empty($row['mask']) && $row['mask'] != 'none')
			    {
				    //!!! We never error on this - just ignore it at the moment...
				    if ($row['mask'] == 'email' && (preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', $value) === 0 || strlen($value) > 255))
					    $custom_field_errors[] = array('custom_field_invalid_email', array($row['field_name']));
				    elseif ($row['mask'] == 'number' && preg_match('~[^\d]~', $value))
					    $custom_field_errors[] = array('custom_field_not_number', array($row['field_name']));
				    elseif (substr($row['mask'], 0, 5) == 'regex' && preg_match(substr($row['mask'], 5), $value) === 0)
					    $custom_field_errors[] = array('custom_field_inproper_format', array($row['field_name']));
			    }

			    // Is this required but not there?
			    if (trim($value) == '' && $row['show_reg'] > 1)
				    $custom_field_errors[] = array('custom_field_empty', array($row['field_name']));
		    }
	    }
	    $smcFunc['db_free_result']($request);
			    
				// Process any errors.
	    if (!empty($custom_field_errors))
	    {
		    loadLanguage('Errors');
		    foreach ($custom_field_errors as $error)
			    $reg_errors = vsprintf($fb_hook_object->txt['error_' . $error[0]], $error[1]);
	    }
        if (!empty($reg_errors))
	    {
            fatal_error($reg_errors ,false);
	    }
    }
	
	public static function fb_do_agree(){
	
	    global $sourcedir, $context, $boarddir, $boardurl, $user_info, $fb_hook_object;
	
		require_once($sourcedir .'/Subs-Package.php');

		if(isset($_GET['agree'])){
            
			loadLanguage('Login');
	        $context['sub_template']  = 'regfb_agree';
	            
				if (file_exists($boarddir . '/agreement.' . $user_info['language'] . '.txt'))
	                $context['agreement'] = parse_bbc(fetch_web_data($boardurl . '/agreement.' . $user_info['language'] . '.txt'), true, 'agreement_' . $user_info['language']);
	            elseif (file_exists($boarddir . '/agreement.txt'))
	                $context['agreement'] = parse_bbc(fetch_web_data($boardurl . '/agreement.txt'), true, 'agreement');
	            else
	                $context['agreement'] = '';
        }
        else{
            
			if(!isset($_POST['accept_agreement']) && !empty($fb_hook_object->modSettings['requireAgreement'])){
			    redirectexit('action=facebookintegrate;area=connect;agree');
			}
			
        }
	}
	
	public static function fb_do_custom(){
	
	    global $context, $sourcedir, $fb_hook_object, $user_info, $smcFunc;
	    
		if (!empty($fb_hook_object->modSettings['fb_app_enablecp'])){
           
		    require_once($sourcedir . '/Profile.php');
            loadCustomFields(0, 'register');
  
            if (!empty($fb_hook_object->modSettings['registration_fields']))
            {
	            require_once($sourcedir . '/Profile-Modify.php');

	            loadLanguage('Profile');
	            loadTemplate('Profile');

	            $context['user']['is_owner'] = true;
	            $user_info['permissions'] = array_merge($user_info['permissions'], array('profile_account_own', 'profile_extra_own'));
	            $reg_fields = explode(',', $fb_hook_object->modSettings['registration_fields']);

	            foreach ($reg_fields as $field)
		            if (isset($_POST[$field]))
			            $cur_profile[$field] = $smcFunc['htmlspecialchars']($_POST[$field]);

	                setupProfileContext($reg_fields);
            }
        }
	}
	
	static function fb_createRandomPassword() { 

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

	public static function fb_reg_auto(){
	    
		global $context, $fb_object, $fb_hook_object, $sourcedir, $txt, $user_info, $modSettings;
		
	    $face_user['real_name'] = $fb_hook_object->face_USettings($fb_object->user_info_fbuname,'real_name','real_name');
			
	    if($face_user['real_name']){
	        redirectexit('action=facebookintegrate;area=logsync;nt;u='.$fb_object->user_info_fbuname.'');
	    }
		
		$real_name = $fb_hook_object->character_clean($fb_object->user_info_fbuname);
	    
		if($fb_object->user_info_fbuname1)
		    $newEmail = ''.$fb_object->user_info_fbuname1.'@facebook.com';
		else
		    $newEmail = $fb_object->user_info_fbemail;
		    
	    $password = self::fb_createRandomPassword(); 
		
		$regOptions = array(
	        'interface' => 'guest',
            'auth_method' => 'password',
            'username' => $real_name,
            'email' => $newEmail,
            'require' => 'nothing',
            'password' => $password,
		    'password_check' => $password,
		    'password_salt' => substr(md5(mt_rand()), 0, 4),
			'send_welcome_email' => !empty($modSettings['send_welcomeEmail']),
            'check_password_strength' => false,
            'check_email_ban' => false,
            'extra_register_vars' => array(
		        'id_group' => !empty($fb_hook_object->modSettings['fb_admin_mem_groupe']) ? $fb_hook_object->modSettings['fb_admin_mem_groupe'] : '0',
		     ),
        );
	
        require_once($sourcedir . '/Subs-Members.php');
        $memberID = registerMember($regOptions);
		
		updateMemberData($memberID, 
	        array(
			    'fbname' => $fb_object->user_info_fbname,
		        'fbid' => $fb_object->user_info_fbid,
		    )
	    );
	
	    $face_profile = 'http://facebook.com/profile.php?id='.$fb_object->user_info_fbid.'';
	
        $fb_hook_object->update_themes_face($memberID, 'face_pro', $face_profile);
	
	    redirectexit('action=facebookintegrate');
	}
	
	public static function fb_do_register(){
	
	    global $context, $fb_object, $fb_hook_object, $sourcedir, $txt, $user_info, $modSettings;
		
		if(isset($_GET['register'])){ 
  
	        if(empty($_POST['real_name'])){
	            fatal_error($fb_hook_object->txt['fb_regname1'], false);
	        }
			
	        $face_user['real_name'] = $fb_hook_object->face_USettings($_POST['real_name'],'real_name','real_name');
			
	        if($face_user['real_name']){
	            redirectexit('action=facebookintegrate;area=logsync;nt;u='.$_POST['real_name'].'');
	        }
	
	        if (!empty($modSettings['fb_app_enablecp'])){
	            $fb_object->fbc_custom_regfeild_check();
	        }
	        
	        $real_name = $fb_hook_object->character_clean($_POST['real_name']);
	        $newEmail = $fb_object->user_info_fbemail;
	
	        $regOptions = array(
	            'interface' => 'guest',
                'auth_method' => 'password',
                'username' => $real_name,
                'email' => $newEmail,
                'require' => 'nothing',
                'password' => $_POST['passwrd1'],
		        'password_check' => $_POST['passwrd2'],
				'send_welcome_email' => !empty($modSettings['send_welcomeEmail']),
		        'password_salt' => substr(md5(mt_rand()), 0, 4),
                'check_password_strength' => false,
                'check_email_ban' => false,
                'extra_register_vars' => array(
		            'id_group' => !empty($fb_hook_object->modSettings['fb_admin_mem_groupe']) ? $fb_hook_object->modSettings['fb_admin_mem_groupe'] : '0',
		        ),
            );
	
            require_once($sourcedir . '/Subs-Members.php');
            $memberID = registerMember($regOptions);
	
	        if (!empty($fb_hook_object->modSettings['fb_app_enablecp'])){
	            if (!empty($_POST['customfield'])){
	                require_once($sourcedir . '/Profile.php');
	                require_once($sourcedir . '/Profile-Modify.php');
	                makeCustomFieldChanges($memberID, 'register');
	            }
	        }
	       
		    updateMemberData($memberID, 
	            array(
			        'fbname' => $fb_object->user_info_fbname,
		            'fbid' => $fb_object->user_info_fbid,
		        )
	        );
	
	        $face_profile = 'http://facebook.com/profile.php?id='.$fb_object->user_info_fbid.'';
	
            $fb_hook_object->update_themes_face($memberID, 'face_pro', $face_profile);
	
	        redirectexit('action=facebookintegrate');
        }
	}
}
?>