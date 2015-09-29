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
	
class SAFacebookprofile
{

	protected $user;
	protected $pic;
	protected $facebook;
	
	protected function __construct() {
	
	    global $facebook, $pic, $user;
		
		SAFacebookhooks::face_init();
		
		$this->facebook = $facebook;
		$this->user = $user['user_id'];
		$this->fb_pic = $pic;
	}
	
    public function fbc_settings(){
        
        global $smcFunc, $profileUrl, $fb_hook_object, $fb_object, $user_profile, $context;
      
        $context['sub_template']  = 'fbc_set';
        loadTemplate('Facebook');
        $_SESSION['safbKeys'] = false;
		$fb_object = new SAFacebookprofile;
  
        if(!$fb_hook_object->modSettings['fb_app_enabled'])
		    redirectexit();
			
        if(empty($_GET['u'])){fatal_error($fb_hook_object->txt['fb_nomem'],false);}
  
        $context['fbpwp'] = $fb_hook_object->face_USettings($_GET['u'],'fbpw','id_member');
  
        if(isset($_GET['save'])){
  
            $_POST['pwp'] = isset($_REQUEST['pwp'])? 1 : 0;
	        updateMemberData($_GET['u'], array('fbpw' => $_POST['pwp']));
    
	        redirectexit('action=profile;area=fsettings;u='.$_GET['u']);
        }
		
        if(isset($_GET['doavatar'])){
	        
	        $fb_object->fb_pic = str_replace('https','http',$fb_object->fb_pic);
	        updateMemberData($_GET['u'], array('avatar' => $fb_object->fb_pic));
			
	        redirectexit('action=profile;area=fsettings;u='.$_GET['u'].';avatar');
        }
		
        if(isset($_GET['import'])){
            
			if($fb_object->user){ 
			
                try {
				
	                $buddiesArray = explode(',', $user_profile[$_GET['u']]['buddy_list']);
	
	                foreach ($buddiesArray as $k => $dummy)
		                if ($dummy == '')
			            unset($buddiesArray[$k]);
			
	                $context['fbid'] = $fb_hook_object->face_USettings($_GET['u'],'fbid','id_member');
	   
	                $fql = "SELECT uid FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=".$context['fbid'].") AND is_app_user = 1";  
                    $param = array(  
                        'method' => 'fql.query',  
                        'query' => $fql,  
                        'callback' => '',
                    ); 
					
                    $_friends   =   $fb_object->facebook->api($param); 

	                $friends = array();
			
	                if (is_array($_friends) && count($_friends)) {
		
		                foreach ($_friends as $friend) {
			                $friends[] = $friend['uid'];
		                }
	                }
	   
	                $friends = implode(',', $friends);

	                preg_match_all('~"([^"]+)"~', $friends, $matches);
	                $new_buddies = array_unique(array_merge($matches[1], explode(',', preg_replace('~"[^"]+"~', '', $friends))));
	   
	                $request = $smcFunc['db_query']('', '
		                SELECT id_member
		                FROM {db_prefix}members
		                WHERE fbid IN ({array_string:new_buddies})
		                LIMIT {int:count_new_buddies}',
	                    array(
		                    'new_buddies' => $new_buddies,
		                    'count_new_buddies' => count($new_buddies),
	                    )
	                );

	                while ($row = $smcFunc['db_fetch_assoc']($request))
		                $buddiesArray[] = (int) $row['id_member'];
	                $smcFunc['db_free_result']($request);

	                $user_profile[$_GET['u']]['buddy_list'] = implode(',', $buddiesArray);
	                updateMemberData($_GET['u'], array('buddy_list' => $user_profile[$_GET['u']]['buddy_list']));
       
                    redirectexit('action=profile;area=lists;u='.$_GET['u'].';bud');	   
                
				} 
                catch (FacebookApiException $e){
				
                    fatal_error($e,false);
                    $fb_object->user = null;
					
                }
            }
        }
    }
}
?>	