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
	
function twitter_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
{
	$position = array_search($key, array_keys($input), $strict);
	
	// Key not found -> insert as last
	if ($position === false)
	{
		$input = array_merge($input, $insert);
		return;
	}
	
	if ($where === 'after')
		$position += 1;

	// Insert as first
	if ($position === 0)
		$input = array_merge($insert, $input);
	else
		$input = array_merge(
			array_slice($input, 0, $position),
			$insert,
			array_slice($input, $position)
		);
}

function twit_integrate_login($user, $hashPasswd, $cookieTime){

    global $user_settings;

    if(isset($_GET['synctw'])){
	
	    $twitter_profile = ''.$_SESSION['twit_name'].'';
	
       updateMemberData($user_settings['id_member'], 
            array( 
		        'twitname' => $_SESSION['twit_name'],
			    'twitid' => $_SESSION['twit_id'],
			    'twitrn' => $_SESSION['twit_sn']
	        )
	    );

        update_themes_twitter($user_settings['id_member'], 'twit_pro', $twitter_profile);
   
       unset($_SESSION['twit_name']);
       unset($_SESSION['twit_id']);
       unset($_SESSION['twit_sn']);
	}
	else{
	    return;
	}
}

function twit_post_members(&$regOptions, &$theme_vars){
    
	global $scripturl, $context, $modSettings;
	  
	if(!empty($modSettings['tw_enpub'])){
	if($modSettings['tw_enpub'] == 1 || $modSettings['tw_enpub'] == 3){
	
	    if(!empty($modSettings['tw_app_enabled'])){
		
		    $consumer_key = !empty($modSettings['tw_app_id']) ? $modSettings['tw_app_id'] : '';
            $consumer_secret = !empty($modSettings['tw_app_key']) ? $modSettings['tw_app_key'] : '';
	        $token = !empty($modSettings['tw_app_token']) ? $modSettings['tw_app_token'] : '';
            $secret = !empty($modSettings['tw_app_tokensecret']) ? $modSettings['tw_app_tokensecret'] : '';
	
            $to = new EpiTwitter($consumer_key, $consumer_secret, $token, $secret);
    
	        $twtmessage = utf8_decode($regOptions['register_vars']['member_name'].' Just Registerd at '.$context['forum_name']); 
			$twturl = $scripturl;  
			
			if(!empty($modSettings['tw_app_shorturl']) && !empty($modSettings['tw_app_bituname']) && !empty($modSettings['tw_app_bitkey'])){
	            $newtwturl = tw_shorten($twturl, $modSettings['tw_app_bituname'], $modSettings['tw_app_bitkey']);
			}else{
			    $newtwturl = $twturl;
			}
			$hash = strtolower(str_replace(' ','_',$context['forum_name']));
            $message = $twtmessage.'  '.$newtwturl.' #'.$hash;
	
            try{//Try it!!!!!
			    $status = $to->post('/statuses/update.json', array('status' => utf8_encode($message)));
			}
	        catch(EpiTwitterException $e){//Catch it!!!!!
        
		        return;//On EpiTwitterException error just return 
            }
			catch(EpiTwitterForbiddenException $e){//Catch it!!!!!
        
		        return;//On EpiTwitterForbiddenException error just return 
            }
		}
		else{
	        return;//not enabled just return
	    }
	}else{
	    return;//not publishing this just return
	}
	}
	else{
	    return;//not publishing this just return
	}
}

function twit_post_topic($msgOptions, $topicOptions, $posterOptions){
    
	global $scripturl, $context, $smcFunc, $modSettings;
	
	tiwtt_get_boards2($topicOptions['board']);
	if(!empty($modSettings['tw_enpub'])){
	if($modSettings['tw_enpub'] == 2 || $modSettings['tw_enpub'] == 3){
	    
		if(!empty($modSettings['tw_app_enabled']) && !empty($context['pub_tw'])){
	    
		    $consumer_key = !empty($modSettings['tw_app_id']) ? $modSettings['tw_app_id'] : '';
            $consumer_secret = !empty($modSettings['tw_app_key']) ? $modSettings['tw_app_key'] : '';
	        $token = !empty($modSettings['tw_app_token']) ? $modSettings['tw_app_token'] : '';
            $secret = !empty($modSettings['tw_app_tokensecret']) ? $modSettings['tw_app_tokensecret'] : '';
	
            $to = new EpiTwitter($consumer_key, $consumer_secret, $token, $secret);
    
	        $req1 = $smcFunc['db_query']('', '
	            SELECT name
	            FROM {db_prefix}boards
	            WHERE id_board = {string:current_board}',
	            array(
                    'one' => 1,
                    'current_board' => $topicOptions['board'], 
                )
	        );	
		
            $row = $smcFunc['db_fetch_assoc']($req1);
            $result = $row['name'];
            $smcFunc['db_free_result']($req1);
			$hash = strtolower(str_replace(' ','_',$result));
			
	        $twtmessage = utf8_decode($msgOptions['subject']);
	        $twturl = $scripturl . '?topic=' . $topicOptions['id'] . '.0';     
            
	        if(!empty($modSettings['tw_app_shorturl']) && !empty($modSettings['tw_app_bituname']) && !empty($modSettings['tw_app_bitkey'])){
	            $newtwturl = tw_shorten($twturl, $modSettings['tw_app_bituname'], $modSettings['tw_app_bitkey']);
			}else{
			    $newtwturl = $twturl;
			}
			
			$message = $twtmessage.'  '.$newtwturl.' #'.$hash;
			
	        try{//Try it!!!!!
                $status = $to->post('/statuses/update.json', array('status' => utf8_encode($message)));
		    }
			catch(EpiTwitterException $e){//Catch it!!!!!
        
		        return;//On EpiTwitterException error just return 
            }
			catch(EpiTwitterForbiddenException $e){//Catch it!!!!!

		        return;//On EpiTwitterForbiddenException error just return 
            }
		}
		else{
	        return;//not enabled just return
	    }
	}
	else{
	    return;//not publishing this just return
	}
	}
	else{
	    return;//not publishing this just return
	}
}

function twitter_loadTheme(){
    global $modSettings, $context, $user_info;
   
    loadLanguage('Twitter');
   
    twit_load();

	if (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (isset($_REQUEST['action']) || in_array(isset($_REQUEST['action']), array('twitter'))))
    {
	    $modSettings['allow_guestAccess'] = 1;
    }
	
	if(isset($_SESSION['twuserid']) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'login' && !empty($modSettings['tw_app_enabledauto'])){
			
		$context['member_id'] = twit_USettings($_SESSION['twuserid'],'id_member','twitid');
		
		if (!empty($context['member_id'])) {
			redirectexit('action=twitter;area=connectlog');   
		}
    }	
	
    if (!isset($_REQUEST['xml']))
    {
       $layers = $context['template_layers'];
       $context['template_layers'] = array();
       foreach ($layers as $layer)
       {
          $context['template_layers'][] = $layer;
             if ($layer == 'body')
                 $context['template_layers'][] = 'twitter';
       }
    }
	
	if(!empty($modSettings['tw_app_enabledanyhere'])){
	    
		$consumer_key = !empty($modSettings['tw_app_id']) ? $modSettings['tw_app_id'] : '';
		
	    $context['html_headers'] .= '
	    <script src="http://platform.twitter.com/anywhere.js?id='.$consumer_key.'&v=1" type="text/javascript"></script>';
		if(!empty($modSettings['tw_app_enabledanyheretype'])){
		    
			$context['html_headers'] .= '
			<script type="text/javascript">
                twttr.anywhere(function (T) {

                    T(".section").linkifyUsers({
                        username: function(node) {
                            return node.alt;
                        }
                    });
                });

                twttr.anywhere(function(twitter) {
                    twitter.linkifyUsers();
                });
            </script>';
		}
		else{
		    $context['html_headers'] .= '
		    <script type="text/javascript">
                twttr.anywhere(function (T) {

                    T(".section").hovercards({
                        username: function(node) {
                            return node.alt;
                        }
                    });
                });

                twttr.anywhere(function(twitter) {
                    twitter.hovercards();
                });
            </script>';
		}
	}
}

function twitter_admin_areas(&$admin_areas){
	global $scripturl, $txt;
	
	if(allowedTo('admin_forum')){
        twitter_array_insert($admin_areas, 'layout',
	        array(
	            'sa_twit' => array(
		            'title' => $txt['twittmain'],
		            'areas' => array(
			            'twitter' => array(
				            'label' => $txt['twittmain1'],
				            'file' => 'Twitter/TwitterAdmin.php',
				            'function' => 'Twittera',
				            'custom_url' => $scripturl . '?action=admin;area=twitter',
				            'icon' => 'server.gif',
			                'subsections' => array(
				                'twitterm' => array($txt['twittmain1']),
				                'boards' => array($txt['twittaboard']),
								'twittlog' => array($txt['tw_app_logs']),
								'about' => array('About'),
			               ),
			            ),
		            ),
		        ),
	        )
        );
    }
}

function template_twitter_above(){
    global $modSettings, $board, $context, $txt;
  
	if(!empty($_GET['topic'])){
	    tiwtt_get_boards($board);
	}

	if(!empty($context['show_tw']) && !empty($modSettings['tw_app_enabled']) && !empty($_GET['topic']) && !empty($_GET['action']) != 'post'){
	 
	    $context['data_via'] = !empty($modSettings['tw_app_uname']) ? $modSettings['tw_app_uname'] : 'sleepyarcade';
	  
	    echo'<a href="http://twitter.com/share" class="twitter-share-button" data-count="horizontal" data-via="'.$context['data_via'].'">'.$txt['twti'].'</a>
	       <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
	}
	
	$context['twitter_messages'] = array(
		'TwitterSync' => array(
			'get' => isset($_GET['success_sync']),
			'message' => $txt['twittprofile2'],
		),
		'TwitterUnsync' => array(
			'get' => isset($_GET['success_unsync']),
			'message' => $txt['twittprofile3'],
		),
	   'Twitterpic' => array(
			'get' => isset($_GET['avatardone']),
			'message' => $txt['tw_app_pro_doneavatat'],
		),
	);

	foreach ($context['twitter_messages'] as $message){
	    if($message['get']){
	        echo'
	        <div class="windowbg" id="profile_success" align="center">
		        '.$message['message'].'
		    </div>';
	    }
    }

}
function template_twitter_below(){

    global $context, $board, $topic, $txt, $modSettings, $settings;

	if(empty($board) && empty($topic) && $context['current_action'] == '' && !empty($modSettings['tw_app_enabledlatetweet'])){
	    
		$context['twitterappname'] = !empty($modSettings['tw_app_uname']) ? $modSettings['tw_app_uname'] : 'sleepyarcade';
		$context['twitterapptcount'] = !empty($modSettings['tw_app_tweetcount']) ? $modSettings['tw_app_tweetcount'] : '6';
		
	    echo ' <br />
		<span class="upperframe"><span></span></span>
	        <div class="roundframe">
		
		<div class="cat_bar">
		    <h3 class="catbg">
		        <a class="section href="http://www.twitter.com/'.$context['twitterappname'].'" target="_blank">
                <img class="icon" src="', $settings['images_url'], '/twitter.png" alt="'.$context['twitterappname'].'"/>'.$txt['twittmain11'].'</a>
	        </h3>
		</div>';
			
	        echo'
		       <br /><div id="twitter_update_list"></div>
		    </div>
	     <span class="lowerframe"><span></span></span>';
	
	    echo'
		<script type="text/javascript" src="http://twitter.com/javascripts/blogger.js"></script>
	    <script type="text/javascript" src="http://twitter.com/statuses/user_timeline/'.$context['twitterappname'].'.json?callback=twitterCallback2&amp;count='.$context['twitterapptcount'].'"></script>';
		
	}
}


function ob_twitter(&$buffer){
    global $txt, $context,$settings, $modSettings, $url;
    
	if(empty($modSettings['tw_app_enabled']) || isset($_REQUEST['xml']))
	   return $buffer;
	
	if (!$context['user']['is_logged']){
	    
		$twitterObjUnAuth = new EpiTwitter($modSettings['tw_app_id'], $modSettings['tw_app_key']);
        try { 
		    $url = $twitterObjUnAuth->getAuthenticateUrl();
	    } 
		catch (Exception $e) {
            $url = '';
        }

	    $txt['guestnew'] = sprintf($txt['welcome_guest'], $txt['guest_title']);
	
	    $buffer = preg_replace('~(' . preg_quote($txt['forgot_your_password']. '</a></p>') . ')~', ''. $txt['forgot_your_password']. '</a></p><div align="center"><a href="'.$url.'"><img src="http://si0.twimg.com/images/dev/buttons/sign-in-with-twitter-l.png" alt="'.$txt['twittsign'].'"/></a></div>', $buffer);
	    $buffer = preg_replace('~(' . preg_quote('<div class="info">'. $txt['guestnew']. '</div>') . ')~', '<a href="'.$url.'"><img src="'.$modSettings['tw_app_log_img'].'" alt="'.$txt['twittsign'].'"/></a><br /><div class="info">'. $txt['guestnew']. '</div>', $buffer);
	    $buffer = preg_replace('~(' . preg_quote('<dt><strong><label for="smf_autov_username">'. $txt['username']. ':</label></strong></dt>') . ')~', '<dt>'.$txt['twittregister'].'</dt><dd><a href="'.$url.'"><img src="http://si0.twimg.com/images/dev/buttons/sign-in-with-twitter-l.png" alt="'.$txt['twittsign'].'"/></a></dd><dt><strong><label for="smf_autov_username">'. $txt['username']. ':</label></strong></dt>', $buffer);
	}
	
	return $buffer;
}

function twitter_profile_areas(&$profile_areas){
	global $context,$url, $sc, $user_settings, $modSettings, $scripturl, $user_info, $txt;

	$twitterObjUnAuth = new EpiTwitter($modSettings['tw_app_id'], $modSettings['tw_app_key']);
	try { 
		$url = $twitterObjUnAuth->getAuthenticateUrl();
	} 
	catch (Exception $e) {
        $url = '';
    }
   
    if(empty($user_settings['twitname']) && !empty($modSettings['tw_app_enabled'])){
	    twitter_array_insert($profile_areas, 'profile_action',
		    array(
			    'profile_twitter' => array(
			        'title' => $txt['twittmain11'],
			        'areas' => array(
				        'tsettings' => array(
					        'label' => $txt['twittprofile'],
					        'custom_url' => $url.'" onclick="return confirm(\''.$txt['twittprofileconfirm'].'\');"',
					        'sc' => $sc,
					        'permission' => array(
						        'own' => 'profile_view_own',
						         'any' => '',
				            ),
				        ),		
			        ),
		        ),
		    )
	    );
    }
    if(!empty($user_settings['twitname']) && !empty($modSettings['tw_app_enabled'])){
        twitter_array_insert($profile_areas, 'profile_action',
		    array(
			    'profile_twitter' => array(
			        'title' => $txt['twittmain11'],
			        'areas' => array(
				        'tsettings' => array(
					        'label' => $txt['tw_app_sett_pro'],
					        'file' => 'Twitter/Twitter.php',
					        'function' => 'TwitterProfile',
					        'sc' => $sc,
					        'permission' => array(
						       'own' => 'profile_view_own',
						       'any' => '',
					        ),
				        ),		
			        ),
		        ),
	        )
	    );
    }
}

function twitter_actions(&$actionArray){

    $actionArray['twitter'] = array('Twitter/Twitter.php', 'Twitter');
}

function twit_load(){
    global $sourcedir, $boarddir;
   
    if(!class_exists('Services_JSON')) //This may or may not have been loaded by other mod ie my twitter or gplus mods
        require_once($sourcedir.'/Twitter/TwitterCompat.php');
    
	require_once($boarddir.'/twitterauth/EpiCurl.php');
    require_once($boarddir.'/twitterauth/EpiOAuth.php');
    require_once($boarddir.'/twitterauth/EpiTwitter.php');
}

function twit_init(){
    global $consumer_key, $twpic, $username, $txt, $modSettings, $twitterObj, $realname, $userid, $url, $consumer_secret;
   
    $consumer_key = !empty($modSettings['tw_app_id']) ? $modSettings['tw_app_id'] : '';
    $consumer_secret = !empty($modSettings['tw_app_key']) ? $modSettings['tw_app_key'] : '';
	$token = !empty($modSettings['tw_app_token']) ? $modSettings['tw_app_token'] : '';
    $secret = !empty($modSettings['tw_app_tokensecret']) ? $modSettings['tw_app_tokensecret'] : '';
	
	$twitterObjUnAuth = new EpiTwitter($modSettings['tw_app_id'], $modSettings['tw_app_key']);
    try { 
		$url = $twitterObjUnAuth->getAuthenticateUrl();
	} 
	catch (Exception $e) {
        $url = '';
    }
	
	$twitterObj = new EpiTwitter($consumer_key, $consumer_secret, $token, $secret);
	
	try{ //Try it!!!!!
	    if(empty($_SESSION['twpic']) || empty($_SESSION['twusername']) || empty($_SESSION['twuserid']) || empty($_SESSION['twrealname'])){
	     
			if(isset($_GET['oauth_token']) || (isset($_COOKIE['oauth_token']) && isset($_COOKIE['oauth_token_secret'])))
            {
                // user accepted access
	            if( !isset($_COOKIE['oauth_token']) || !isset($_COOKIE['oauth_token_secret']) )
	            {
		            // user comes from twitter
	                $twitterObj->setToken($_GET['oauth_token']);
                    $token = $twitterObj->getAccessToken();
                    $twitterObj->setToken($token->oauth_token, $token->oauth_token_secret);		
					setcookie('oauth_token', $token->oauth_token);
                    setcookie('oauth_token_secret', $token->oauth_token_secret);
                    $twitterInfo = $twitterObj->get('/account/verify_credentials.json');  
	            }
	            else
	            {
	                // user switched pages and came back or got here directly, stilled logged in
	                $twitterObj->setToken($_COOKIE['oauth_token'],$_COOKIE['oauth_token_secret']);
					$twitterInfo = $twitterObj->get('/account/verify_credentials.json');

	            }

	            $_SESSION['twusername'] = $twitterInfo->screen_name;
                $_SESSION['twrealname'] = $twitterInfo->name;
                $_SESSION['twuserid'] = $twitterInfo->id;
	            $_SESSION['twpic'] = $twitterInfo->profile_image_url;
        }
        elseif(isset($_GET['denied'])){
            // user denied access
           
        }
        else{
            // user not logged in
        }
			/*$twitterObj->setToken($_GET['oauth_token']);
            $token = $twitterObj->getAccessToken();
            $twitterObj->setToken($token->oauth_token, $token->oauth_token_secret);		
            $twitterInfo = $twitterObj->get('/account/verify_credentials.json');  

	        $_SESSION['twusername'] = $twitterInfo->screen_name;
            $_SESSION['twrealname'] = $twitterInfo->name;
            $_SESSION['twuserid'] = $twitterInfo->id;
	        $_SESSION['twpic'] = $twitterInfo->profile_image_url;*/
	    }
    }
	catch(EpiTwitterException $e){//Catch it!!!!!
        
		$mes = ''.$txt['tw_app_oauthexception1'].'<br /><br />'.$e->getMessage().'';  
        fatal_error($mes,false); 
    }
	catch(Exception $e){  //Catch it!!!!!
        
		//Thorw it!!!!!
		$mes = ''.$txt['tw_app_oauthexception2'].'<br /><br />'.$e->getMessage().'';   
        fatal_error($mes,false);  
    }  
    
	$username = $_SESSION['twusername'];	
	$realname = $_SESSION['twrealname'];
	$userid = $_SESSION['twuserid'];
	$twpic = $_SESSION['twpic'];
}
	
function tw_shorten($url, $user, $key) {
    global $sourcedir;
	
	require_once($sourcedir .'/Subs-Package.php');
					
	$bitly_url = 'http://api.bit.ly/v3/shorten?login='.$user.'&apiKey='.$key.'&uri='.$url.'&format=txt';		
	$bitly_url_short = fetch_web_data($bitly_url);			
			
	if($bitly_url_short == null)
		$bitly_url_short = $url;
										
	return $bitly_url_short;
}
		
function twit_USettings($id,$row,$where) {

	global $smcFunc;

	$results = $smcFunc['db_query']('', '
		SELECT m.{raw:row}
		FROM {db_prefix}members AS m
		WHERE m.{raw:where} = {string:member_id}
		LIMIT 1',
		array(
			'member_id' => $id,
			'row' => $row,
			'where' => $where
		)
	);
	$temp = $smcFunc['db_fetch_assoc']($results);
	$smcFunc['db_free_result']($results);
	
	return $temp[$row];
}

function update_themes_twitter_del($var, $userid){
    global $smcFunc;
	
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}themes
		WHERE variable = {string:var} AND id_member = {int:mem}',
	array(
		'mem' => $userid,
		'var' => $var,
	));
}

function update_themes_twitter($member, $var, $userid){
    global $smcFunc;
	
	$smcFunc['db_insert']('ignore',
           '{db_prefix}themes',
    array('id_member' => 'int', 'id_theme' => 'int', 'variable' => 'string', 'value' => 'string',),
    array($member, 1, $var, $userid,),
    array('id_member', 'id_theme')
    );
}

function tiwtt_get_boards2($board=0){
global $smcFunc, $context, $board;

   $req1 = $smcFunc['db_query']('', '
	SELECT 
		COUNT(*) AS total 
	FROM {db_prefix}boards
	WHERE tweet_pubenable = {int:one} AND id_board = {string:current_board}',
	    array(
            'one' => 1,
            'current_board' => $board, 
        )
	);	
		
   $row = $smcFunc['db_fetch_assoc']($req1);
   $result = $row['total'];
   $smcFunc['db_free_result']($req1);
   
   if ($result == 0)
		$context['pub_tw'] = false;
	else
        $context['pub_tw'] = true;

}

function tiwtt_get_boards($board=0){
global $smcFunc, $context, $board;

   $req1 = $smcFunc['db_query']('', '
	SELECT 
		COUNT(*) AS total 
	FROM {db_prefix}boards
	WHERE tweet_enable = {int:one} AND id_board = {string:current_board}',
	    array(
            'one' => 1,
            'current_board' => $board, 
        )
	);	
		
   $row = $smcFunc['db_fetch_assoc']($req1);
   $result = $row['total'];
   $smcFunc['db_free_result']($req1);
   
   if ($result == 0)
		$context['show_tw'] = false;
	else
        $context['show_tw'] = true;

}

function show_twitter_login(){
    global $modSettings;
	
	if(!empty($modSettings['tw_app_enabled'])){
	
	    $twitterObjUnAuth = new EpiTwitter($modSettings['tw_app_id'], $modSettings['tw_app_key']);
        try { 
		    $url = $twitterObjUnAuth->getAuthenticateUrl();
	    } 
		catch (Exception $e) {
            $url = '';
        }
	    echo'<a href="'.$url.'"><img src="'.$modSettings['tw_app_log_img'].'" alt=""/></a>';
	}
}
?>