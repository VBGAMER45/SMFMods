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

// Quick and easy way to run sceduled taks this will be run every hour to ensure access tokens stay upto date
/*function scheduled_fbtoken(){

	global $sourcedir, $modSettings;

	require_once($sourcedir .'/Subs-Package.php');

	if(!empty($modSettings['fb_app_id']) && !empty($modSettings['fb_app_secret'])){

		$pub_data = $modSettings['fb_app_id'].'|'.$modSettings['fb_app_secret'];

		updateSettings(array('fb_atoken' => $pub_data));

		/*$pubtoken_url = "https://graph.facebook.com/oauth/access_token?client_id=" .$modSettings['fb_app_id']."&client_secret=".$modSettings['fb_app_secret']."&type=client_cred";

		$pub_data = fetch_web_data($pubtoken_url);

		if (strpos($pub_data,'access_token=') !== false) {
			$pub_data = str_replace('access_token=','',$pub_data);
		}

		updateSettings(array('fb_atoken' => $pub_data));*/
	/*}

	if(!empty($modSettings['fb_admin_pid']) && !empty($modSettings['fb_app_id']) && !empty($modSettings['fb_app_secret'])){

		$facebook =  new Facebook(array('appId'  => $modSettings['fb_app_id'],'secret' => $modSettings['fb_app_secret'],));
		$page_info = $facebook->api('/'.$modSettings['fb_admin_pid'].'?fields=access_token');

		if(!empty($page_info['access_token'])){
		    updateSettings(array('fb_app_atokenpage' => $page_info['access_token']));
        }

	}
	return true;
}*/

class SAFacebookhooks{

	const VERSION = '2.0.2';

	public $txt;
	public $scripturl;
	public $modSettings;
	public $user_info_id;

	public function __construct() {

	    global $scripturl, $user_info, $facebook, $modSettings, $txt;

		$this->txt = $txt;
		$this->scripturl = $scripturl;
		$this->modSettings = $modSettings;
		$this->user_info_id = $user_info['id'];
	}

    public static function facebook_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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

    public static function Facebook_register_override(){

		global $modSettings;

	    //overide normal registration if set
	    if (!empty($modSettings['fb_app_enabled']) && $modSettings['fb_dfbreg2'] == 1 && isset($_REQUEST['action']) && $_REQUEST['action'] == 'register'){
	        redirectexit('action=facebookintegrate;area=fbbp');
	    }

		if (!class_exists('Facebook'))
	        self::face_load();

		$user = fb_cookie_parse();

		if($user && isset($_REQUEST['action']) && $_REQUEST['action'] == 'login' && !empty($modSettings['fb_app_enablelogauto'])){

		    $face_user['id_member'] = self::face_USettings($user['user_id'],'id_member','fbid');

		    if (!empty($face_user['id_member'])) {
			    redirectexit('action=facebookintegrate;area=connectlog');
		    }
		}
    }

    public static function Facebook_integrate_login($user, $hashPasswd, $cookieTime){

        global $FacebookId, $fb_hook_object, $FacebookName, $user_settings, $user_info, $context;

        if(isset($_GET['sync'])){
	        self::face_init();
	        $face_profile = 'http://facebook.com/profile.php?id='.$FacebookId.'';

            updateMemberData($user_settings['id_member'],
                array(
		           'fbname' => $FacebookName,
			       'fbid' => $FacebookId,
	            )
	        );

            self::update_themes_face($user_settings['id_member'], 'face_pro', $face_profile);
	    }
	    else{
	        return;
	    }
    }

	static function facebook_htmlspecialchars($data){

		global $context;
		$data = strip_tags($data);
		$data = htmlspecialchars(htmlentities($data, ENT_QUOTES, $context['character_set']), ENT_QUOTES, $context['character_set']);
		return $data;
	}

	public static function facebook_showPub($data){

		global $txt, $modSettings, $context, $board;

		self::get_boardsfb('pub_enable',$board);
		$doit = false;

		if(empty($modSettings['fb_app_enabled']))
            return $doit;

		$cleaned[] = array_map(array("SAFacebookhooks","facebook_htmlspecialchars"), $data);

        /*
		$cleaned[0]['body'] = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $cleaned[0]['body']);
        $cleaned[0]['body'] = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $cleaned[0]['body']);
        */
        $cleaned[0]['body'] = preg_replace_callback('~&#x(0*[0-9a-f]{2,5});{0,1}~i',create_function ('$matches', 'return chr(hexdec($matches[1]));'),  $cleaned[0]['body']);
        $cleaned[0]['body'] = preg_replace_callback('~&#([0-9]{2,4});{0,1}~',create_function ('$matches', 'return chr($matches[1]);'),  $cleaned[0]['body']);


        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);


		$cleaned[0]['body'] = strtr($cleaned[0]['body'], $trans_tbl);
		$context['fb_publish_subject'] = html_entity_decode($cleaned[0]['subject']);
		$context['fb_publish_body'] = $cleaned[0]['body'];
		$context['fb_publish_href'] = $cleaned[0]['href'];

		if(!empty($data['txt_label']))
		    $dtxt = $data['txt_label'];
		else
		    $dtxt = $txt['fb_apub'];

		if(!empty($data['isPost']) && !empty($context['pub_enable'])){
		    $doit = '
			    <li class="fbpub_button">
		            <a href="javascript:void(0)" onclick="publishStream(\''.$context['fb_publish_subject'].'\',\''.$context['fb_publish_body'].'\',\''.$context['fb_publish_href'].'\');">'.$dtxt.'</a>
		        </li>';
	    }elseif(empty($data['isPost'])){
		    $doit = '<a href="javascript:void(0)" onclick="publishStream(\''.$context['fb_publish_subject'].'\',\''.$context['fb_publish_body'].'\',\''.$context['fb_publish_href'].'\'); return false;"><span>'.$dtxt.'</span></a>';
		}

		return $doit;
	}

    public static function facebook_loadTheme(){

	    global $fb_hook_object, $boardurl, $message, $modSettings, $board, $user_info, $txt , $settings ,$scripturl, $context;

		self::facebook_doreplys();//posts any replys to facebook if enabled
		self::facebook_dokeys();//gets access tokens

		loadLanguage('Facebook');

		$fb_hook_object = new SAFacebookhooks;

        if (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (isset($_REQUEST['action']) || in_array(isset($_REQUEST['action']), array('facebookintegrate'))))
        {
	        $modSettings['allow_guestAccess'] = 1;
        }


        if (empty($_REQUEST['action']) || !in_array($_REQUEST['action'], array('dlattach', 'jsoption', '.xml')))
        {
            if (!isset($_REQUEST['xml']))
            {
                $layers = $context['template_layers'];
                $context['template_layers'] = array();
                foreach ($layers as $layer)
                {
                    $context['template_layers'][] = $layer;
                        if ($layer == 'body' || $layer == 'main')
                            $context['template_layers'][] = 'facebook';
                }
            }
        }

		$context['html_headers'] .= '
		    <script type="text/javascript">

	            function streamPublish(name, description, hrefTitle, hrefLink, userPrompt){
                    FB.ui(
                    {
                        method: \'stream.publish\',
                        message: \'\',
                            attachment: {
                                name: name,
                                caption: \'\',


                                description: (description),
                                href: hrefLink,
						        media:[
								{
									\'type\': \'image\', \'src\': \''.(!empty($modSettings['fb_app_image']) ? $modSettings['fb_app_image'] : $settings['default_theme_url'].'/images/topic/veryhot_post.gif').'\', \'href\': hrefLink
								}
								]
                            },
                        action_links:[
                        {
			                text: hrefTitle,
				            href: hrefLink
			            }
                        ],
                           user_prompt_message: userPrompt
                        },
                        function(response) {});
                    }
	                function publishStream(suject,body,href){
                        streamPublish("" + suject + "","" + body + "", "" + suject + "", "" + href + "");
                    }

                </script>
			    <style type="text/css">
				    li.fbpub_button  {
	                   display: inline;
	                   font-weight: bold;
	                   background: url('.$settings['images_url'].'/facebook.png) no-repeat 0 0;
	                   padding-left: 18px;
                    }
			        #forumposts h3 span#facebook
                    {
	                    float: right;
	                    margin: 6px 0.5em 0 0;
                    }
			        ul.quickbuttons li.fbpub_button
                    {
	                    background: url('.$settings['images_url'].'/facebook.png) no-repeat 0 0;
                    }
		        </style>';

    }

    public static function facebook_menu_buttons(&$menu_buttons){

	    global $context, $boarddir,  $user_settings, $fb_hook_object, $user_info, $modSettings, $scripturl, $txt;

        if(!empty($user_settings['fbname']) && !empty($modSettings['fb_mode1']) && !empty($modSettings['fb_app_enabled'])){
	         self::facebook_array_insert($menu_buttons, 'mlist',
		        array(
			        'facebook' => array(
				        'title' => $txt['fb_main5'],
				        'href' => $scripturl . '?action=facebook',
				        'show' => true,
				        'sub_buttons' => array(),
				        'active_button' => false,
			        ),
		        )
	        );
        }

        if(!$context['user']['is_logged'] && !empty($modSettings['fb_mode1']) && !empty($modSettings['fb_app_enabled']) && !empty($modSettings['fb_app_enableguest'])){
	         self::facebook_array_insert($menu_buttons, 'mlist',
		        array(
			        'facebook' => array(
				        'title' => $txt['fb_main5'],
				        'href' => $scripturl . '?action=facebook',
				        'show' => true,
				        'sub_buttons' => array(),
				        'active_button' => false,
			        ),

		        )
	        );
        }

        if(!empty($user_settings['fbname']) && !empty($modSettings['fb_app_enabled'])){

		    $counter = 0;

		    foreach ($menu_buttons['profile']['sub_buttons'] as $area => $dummy)
			    if (++$counter && $area == 'account')
				    break;

            $menu_buttons['profile']['sub_buttons'] = array_merge(
		        array_slice($menu_buttons['profile']['sub_buttons'], 0, $counter, TRUE),

		    array(
		        'fb' => array(
				    'title' => $txt['fb_main5'],
				    'href' => $scripturl . '?action=profile;area=fsettings;u='.$user_info['id'].'',
				    'show' => true,
			    ),
		    ),

		      array_slice($menu_buttons['profile']['sub_buttons'], $counter, NULL, TRUE)

            );
        }

     //admin tab
        if(allowedTo('admin_forum') && !empty($modSettings['fb_app_enabled'])){

		    $counter = 0;

		    foreach ($menu_buttons['admin']['sub_buttons'] as $area => $dummy)
			    if (++$counter && $area == 'featuresettings')
				    break;

            $menu_buttons['admin']['sub_buttons'] = array_merge(
		        array_slice($menu_buttons['admin']['sub_buttons'], 0, $counter, TRUE),

		    array(
			    'fb' => array(
				    'title' => $txt['fb_main1'],
				    'href' => $scripturl . '?action=admin;area=facebook',
				    'show' => true,
			    ),
		    ),

		        array_slice($menu_buttons['admin']['sub_buttons'], $counter, NULL, TRUE)

            );
        }
    }

    public static function facebook_admin_areas(&$admin_areas){

	    global  $modSettings, $fb_hook_object, $scripturl, $txt;

	    if(allowedTo('admin_forum')){
             self::facebook_array_insert($admin_areas, 'layout',
	            array(
	                'sa_fb' => array(
		                'title' => $txt['fb_main1'],
		                'areas' => array(
			                'facebook' => array(
				                'label' => $txt['fb_main'],
				                'file' => 'Facebook/FacebookAdmin.php',
				                'function' => create_function(NULL, 'SAFacebookadmin::Facebooka();'),
				                'custom_url' => $scripturl . '?action=admin;area=facebook',
				                'icon' => 'server.gif',
			                    'subsections' => array(
				                    'facebookm' => array($txt['fb_main']),
				                    'social' => array($txt['fb_social']),
									'publisher' => array($txt['fb_app_publisher']),
									'og' => array($txt['fb_app_ogopengraph']),
									'boards' => array($txt['fb_board']),
									'facebooklog' => array($txt['fb_logmain']),
				                    'hooks' => array($txt['fb_hook']),
				                    'about' => array($txt['fb_credit']),
			                    ),
			                ),
		                ),
		            ),
	            )
            );
        }
    }

    public static function ob_facebook(&$buffer){

		global $txt, $modSettings, $settings, $message, $scripturl, $context, $loginUrl, $consumer_key, $consumer_secret;

	    if(empty($modSettings['fb_app_enabled']) || $modSettings['fb_dfbreg2'] == 2 || isset($_REQUEST['xml']) || isset($_REQUEST['type']))
	        return $buffer;

	    $facebook = new Facebook(array(
            'appId'  => $modSettings['fb_app_id'],
            'secret' => $modSettings['fb_app_secret'],
        ));

	    $loginUrl = $facebook->getLoginUrl(
            array(
		        'redirect_uri' => $scripturl.'?action=facebookintegrate',
		        'scope' => 'email,publish_actions'
		    )
	    );

	   if (!empty($modSettings['fb_app_eog']) && $context['current_action'] != 'admin'){

			$open_graph = '
			    <meta property="fb:app_id" content="'.$modSettings['fb_app_id'].'" />
                <meta property="og:type" content="article" />
				<meta property="og:locale" content="'.(empty($modSettings['fb_admin_intern1']) ? $modSettings['fb_admin_intern1'] : 'en_US').'" />
                <meta property="og:title" content="'.$context['page_title_html_safe']. '" />
                <meta property="og:image" content="'.(!empty($modSettings['fb_app_image']) ? $modSettings['fb_app_image'] : $settings['default_theme_url'].'/images/topic/veryhot_post.gif').'" />
                <meta property="og:description" content="'.$context['page_title_html_safe'].'" />
                <meta property="og:url" content="'.(empty($context['current_topic']) ? $scripturl : $scripturl .'?topic='.$context['current_topic'].'.0').'" />';

	     	$buffer = str_replace('</title>','</title>'.$open_graph, $buffer);
		}

		$buffer = str_replace('xmlns="http://www.w3.org/1999/xhtml"', 'xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml" xmlns:fb="http://ogp.me/ns/fb#"', $buffer);

		//$buffer = str_replace('xmlns="http://www.w3.org/1999/xhtml"', 'xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml"', $buffer);

	    if (!$context['user']['is_logged']){

	        $txt['guestnew'] = sprintf($txt['welcome_guest'], $txt['guest_title']);

			$buffer = preg_replace('~(' . preg_quote('<div class="info">'. $txt['guestnew']. '</div>') . ')~', '<a href="'.$loginUrl.'"><img src="'.$modSettings['fb_log_logo'].'" alt="" /></a><div class="info">'. $txt['guestnew']. '</div>', $buffer);
	        $buffer = preg_replace('~(' . preg_quote($txt['forgot_your_password']. '</a></p>') . ')~', $txt['forgot_your_password']. '</a></p><div align="center"><a href="'.$loginUrl.'"><img src="'.$modSettings['fb_log_logo'].'" alt="" /></a></div>', $buffer);
	        $buffer = preg_replace('~(' . preg_quote('<dt><strong><label for="smf_autov_username">'. $txt['username']. ':</label></strong></dt>') . ')~','<dt><strong>'.$txt['fb_rwf'].':</strong><div class="smalltext">'.$txt['fb_regmay'].'</div></dt><dd><a href="'.$loginUrl.'"><img src="'.$modSettings['fb_log_logo'].'" alt="" /></a></dd><dt><strong><label for="smf_autov_username">'. $txt['username']. ':</label></strong></dt>', $buffer);
	    }

	    if ($modSettings['fb_dfbreg2'] == 1 && !$context['user']['is_logged']){

	        $buffer = preg_replace('~(' . preg_quote($scripturl . '?action=register') . ')~', $scripturl . '?action=facebookintegrate;area=fbbp', $buffer);
	    }

	    return $buffer;
    }

    public static function facebook_profile_areas(&$profile_areas){

		global $context,$url, $sc, $user_settings, $fb_hook_object, $session, $modSettings, $loginUrl, $scripturl, $user_info, $txt;

        $facebook = new Facebook(array(
            'appId'  => $modSettings['fb_app_id'],
            'secret' => $modSettings['fb_app_secret'],
        ));

	    $loginUrl = $facebook->getLoginUrl(
            array(
		       'redirect_uri' => $scripturl.'?action=facebookintegrate',
		       'scope' => 'email,publish_actions'
		    )
	    );

        if(empty($user_settings['fbname']) && !empty($modSettings['fb_app_enabled'])){
	         self::facebook_array_insert($profile_areas, 'profile_action',
		        array(
			        'profile_fb' => array(
			            'title' => $txt['fb_main5'],
			            'areas' => array(
				            'fsettings' => array(
					            'label' => $txt['fb_sync1'],
					            'custom_url' => $loginUrl.'" onclick="return confirm(\''.$txt['fb_sync2'].'\');"',
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
        if(!empty($user_settings['fbname']) && !empty($modSettings['fb_app_enabled'])){
             self::facebook_array_insert($profile_areas, 'profile_action',
		        array(
			        'profile_fb' => array(
			            'title' => $txt['fb_main5'],
			            'areas' => array(
				            'fsettings' => array(
					            'label' => $txt['fb_pro_fileset'],
					            'file' => 'Facebook/FacebookProfile.php',
					            'function' => create_function(NULL, 'SAFacebookprofile::fbc_settings();'),
					            'sc' => $session,
					            'password' => true,
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

    public static function Facebook_Post_members(&$regOptions, &$theme_vars){

        global $scripturl, $context, $boardurl, $txt, $modSettings, $user_info;

        $modSettings['fb_app_atoken'] = !empty($modSettings['fb_app_atoken']) ? $modSettings['fb_app_atoken'] : '';
		$modSettings['fb_app_atokenpage'] = !empty($modSettings['fb_app_atokenpage']) ? $modSettings['fb_app_atokenpage'] : '';
		$modSettings['fb_admin_pid'] = !empty($modSettings['fb_admin_pid']) ? $modSettings['fb_admin_pid'] : '';
		$modSettings['fb_admin_uid'] = !empty($modSettings['fb_admin_uid']) ? $modSettings['fb_admin_uid'] : '';

		$twturl = $scripturl;

		if(!empty($modSettings['fb_app_shorturl']) && !empty($modSettings['fb_app_bituname']) && !empty($modSettings['fb_app_bitkey'])){
	        $newtwturl = fb_shorten($twturl, $modSettings['fb_app_bituname'], $modSettings['fb_app_bitkey']);
			$burl = fb_shorten($boardurl, $modSettings['fb_app_bituname'], $modSettings['fb_app_bitkey']);
	    }else{
			$newtwturl = $twturl;
			$burl = $boardurl;
		}

		if($modSettings['fb_postto'] == 0){	//page
		    $postto = $modSettings['fb_admin_pid'];
			$atoken = $modSettings['fb_app_atokenpage'];
		}
		elseif($modSettings['fb_postto'] == 1){	//Profile
		    $postto = $modSettings['fb_admin_uid'];
			$atoken = $modSettings['fb_atoken'];
		}
		else{//Default to page
		    $postto = $modSettings['fb_admin_pid'];
			$atoken = $modSettings['fb_app_atokenpage'];
		}

		if($modSettings['fb_admin_grant'] == 2 || $modSettings['fb_admin_grant'] == 3){

		    $facebook = new Facebook(array('appId'  => $modSettings['fb_app_id'],'secret' => $modSettings['fb_app_secret'],));

	        $mes = $regOptions['register_vars']['member_name'].' '.$txt['fb_postregt'].' '.$context['forum_name'].'';

			$attachment['access_token'] = $atoken;
	        $attachment['link'] = $newtwturl;
	        $attachment['caption'] = $burl;
	        $attachment['message'] = utf8_encode($mes);

            try {

                //Now Post it to facebook
                $post_app = $facebook->api(''.$postto.'/feed','POST',$attachment);

		    }catch (FacebookApiException $e){
			    return;
		    }
	    }
		else{
		    return;
		}
    }

    public static function Facebook_Post_topic($msgOptions, $topicOptions, $posterOptions){

        global $scripturl,  $fb_hook_object, $sourcedir, $context, $smcFunc, $settings, $boardurl, $txt, $modSettings, $user_info;

        $modSettings['fb_app_atoken'] = !empty($modSettings['fb_app_atoken']) ? $modSettings['fb_app_atoken'] : '';
		$modSettings['fb_app_atokenpage'] = !empty($modSettings['fb_app_atokenpage']) ? $modSettings['fb_app_atokenpage'] : '';
		$modSettings['fb_admin_pid'] = !empty($modSettings['fb_admin_pid']) ? $modSettings['fb_admin_pid'] : '';
		$modSettings['fb_admin_uid'] = !empty($modSettings['fb_admin_uid']) ? $modSettings['fb_admin_uid'] : '';

	    self::get_boardsfb('pub_enable',$topicOptions['board']);

		$twturl = $scripturl . '?topic=' . $topicOptions['id'] . '.0';
		$fb_topic = $topicOptions['id'];

		if(!empty($modSettings['fb_app_shorturl']) && !empty($modSettings['fb_app_bituname']) && !empty($modSettings['fb_app_bitkey'])){
	        $newtwturl = fb_shorten($twturl, $modSettings['fb_app_bituname'], $modSettings['fb_app_bitkey']);
	    }else{
			$newtwturl = $twturl;
		}

		if($modSettings['fb_postto'] == 0){	//page
		    $postto = $modSettings['fb_admin_pid'];
			$atoken = $modSettings['fb_app_atokenpage'];
		}
		elseif($modSettings['fb_postto'] == 1){	//Profile
		    $postto = $modSettings['fb_admin_uid'];
			$atoken = $modSettings['fb_atoken'];
		}
		else{//Default to page
		    $postto = $modSettings['fb_admin_pid'];
			$atoken = $modSettings['fb_app_atokenpage'];
		}

		   /*$result = $smcFunc['db_query']('', '
	            SELECT id_attach, id_thumb, width, height
	            FROM {db_prefix}attachments
	            WHERE id_msg = {int:id_msg}
				LIMIT 1',
		        array(
			        'id_msg' => $msgOptions['id'],
		        )
	        );
	        $context['attach'] = array();
	        while ($row = $smcFunc['db_fetch_assoc']($result))
	        {
	            $context['attach']['is_image'] = !empty($row['width']) && !empty($row['height']);
				$context['attach']['id_attach'] = $row['id_attach'];
				$context['attach']['id_thumb'] = $row['id_thumb'];

	        }
	        $smcFunc['db_free_result']($result);*/

		//grab the first img in the post
		if (preg_match_all('~(\[img.*?\])(.+?)\[/img\]~eis', $msgOptions['body'], $matches)){
		    foreach ($matches as $val) {
		        $val[0] = str_replace('[img]','',$val[0]);
				$val[0] = str_replace('[/img]','',$val[0]);
				$context['fb_pub_img'] = $val[0];
				$context['body_rid_img'] = preg_replace('~(\[img.*?\])(.+?)\[/img\]~eis','',$msgOptions['body']);
		    }
		}
		/*elseif($context['attach']['is_image'] && !empty($context['attach']['id_attach'])){

			$context['fb_pub_img'] = $scripturl . '?action=dlattach;topic=' . $topicOptions['id'] . '.0;attach=' . $context['attach']['id_thumb'] . ';image';
			$context['body_rid_img'] = preg_replace('~(\[img.*?\])(.+?)\[/img\]~eis','',$msgOptions['body']);
		}*/
		else{//no image lets try og:image tag else use the post icon
		    $context['fb_pub_img'] = (!empty($modSettings['fb_app_image']) ? $modSettings['fb_app_image'] : $settings['default_images_url'] . '/post/' . $msgOptions['icon'] . '.gif');
		    $context['body_rid_img'] = $msgOptions['body'];
		}

		if(!empty($context['pub_enable'])){
		    if($modSettings['fb_admin_grant'] == 1 || $modSettings['fb_admin_grant'] == 3){

		        $facebook = new Facebook(array('appId'  => $modSettings['fb_app_id'],'secret' => $modSettings['fb_app_secret'],));

	            $mes = $user_info['name'].' '.$txt['fb_postpostt'].' '.$context['forum_name'].'';
				$permalink = $newtwturl;
				$length = 100;

				if (strlen($context['body_rid_img']) <= $length) {$length = strlen($context['body_rid_img']);}

                $shortened = substr($context['body_rid_img'],0,strpos($context['body_rid_img'] ," ",$length));
				$nobbc_body = preg_replace('|[[\/\!]*?[^\[\]]*?]|si', '', $shortened);
				$nobbc_body = preg_replace('~\[attach\=([0-9]+)\]~iU','',$nobbc_body);
				$nobbc_body = preg_replace('~\[attachimg\=([0-9]+)\]~iU','',$nobbc_body);
				$nobbc_body = preg_replace('~\[attachurl\=([0-9]+)\]~iU','',$nobbc_body);
				$nobbc_body = preg_replace('~\[attachmini\=([0-9]+)\]~iU','',$nobbc_body);
				$attachment['access_token'] = $atoken;
	            $attachment['name'] = $msgOptions['subject'];
	            $attachment['link'] = $permalink;
	            $attachment['description'] = $nobbc_body;
	            $attachment['caption'] = ' ';
				$attachment['message'] = utf8_encode($mes);
	            $attachment['picture'] = $context['fb_pub_img'];

                try {

                    //Now Post it to facebook
                    $post_app = $facebook->api(''.$postto.'/feed','POST',$attachment);

					//if(!empty($modSettings['fb_reply']) && !empty($modSettings['fb_replytoken'])){

						//hmmm not sure if this should start colleting id\'s now so it works when people enable it
						$smcFunc['db_query']('', '
					        UPDATE {db_prefix}topics
		                    SET fb_post_id = {string:fb_post_id}
		                    WHERE id_first_msg = {int:id_topic}',
                            array(
                                'id_topic' => $msgOptions['id'],
							    'fb_post_id' => $post_app['id'],
                            )
                        );
				    //}

		        }catch (FacebookApiException $e){
			        return;
		        }
			}
			else{
		        return;
		    }
		}
		else{
		    return;
		}
    }

	public static function facebook_dokeys(){

		global $sourcedir, $modSettings, $scripturl, $context;

	    if(isset($_GET['code']) && !empty($_SESSION['safbKeys']) && $context['user']['is_logged'] && allowedTo('admin_forum')){

		    require_once($sourcedir .'/Subs-Package.php');
			//die($_GET['code']);
			////////////////////////////access token/////////////////////////////////////
			$code = $_REQUEST['code'];
			//die($code);
		    $token_url = "https://graph.facebook.com/oauth/access_token?" .
            "client_id=" . $modSettings['fb_app_id'] . "&redirect_uri=" . urlencode($scripturl) .
            "&client_secret=" . $modSettings['fb_app_secret'] . "&code=" .$code;

			$fb_data = fetch_web_data($token_url);
		    $params = null;
            parse_str($fb_data, $params);

		    if(!empty($params['access_token'])){
  		        updateSettings(array('fb_replytoken' => $params['access_token']));
            }

			/////////////////////////////App access token////////////////////////////////////


			$pub_data = $modSettings['fb_app_id'].'|'.$modSettings['fb_app_secret'];

			updateSettings(array('fb_atoken' => $pub_data));

			/*$pubtoken_url = "https://graph.facebook.com/oauth/access_token?client_id=" .$modSettings['fb_app_id']."&client_secret=".$modSettings['fb_app_secret']."&type=client_cred";

			$pub_data = fetch_web_data($pubtoken_url);

			if (strpos($pub_data,'access_token=') !== false) {
				$pub_data = str_replace('access_token=','',$pub_data);
			}

			updateSettings(array('fb_atoken' => $pub_data));*/

			/////////////////////////////Page access token////////////////////////////////////

			if(!empty($modSettings['fb_admin_pid'])){

				$facebook =  new Facebook(array('appId'  => $modSettings['fb_app_id'],'secret' => $modSettings['fb_app_secret'],));
			    $page_info = $facebook->api('/'.$modSettings['fb_admin_pid'].'?fields=access_token');
				if(!empty($page_info['access_token'])){
				    updateSettings(array('fb_app_atokenpage' => $page_info['access_token']));
				}

			}
			$_SESSION['safbKeys'] = false;
			redirectexit('action=admin;area=facebook;sa=publisher');
	    }
	}

	public static function facebook_doreplys(){

		global $modSettings, $topic, $user_info, $board, $smcFunc, $context;

	    if ($context['current_action'] == 'post2' && !empty($topic) && !isset($_REQUEST['msg']) && !empty($modSettings['fb_reply']) && !empty($modSettings['fb_replytoken'])){

		    $result = $smcFunc['db_query']('', '
	            SELECT fb_post_id
		        FROM {db_prefix}topics
		        WHERE id_topic = {int:id_topic}',
			    array(
				    'id_topic' => $topic,
				)
			);
	        $row = $smcFunc['db_fetch_assoc']($result);
	        $fb_post_id = $row['fb_post_id'];
            $smcFunc['db_free_result']($result);

			SAFacebookhooks::get_boardsfb('pub_enable',$board);

			if(!empty($fb_post_id) && !empty($context['pub_enable'])){

				$facebook = new Facebook(array('appId'  => $modSettings['fb_app_id'],'secret' => $modSettings['fb_app_secret'],));

			    $new_token = $facebook->getAccessToken();

			    if($new_token && isset($_COOKIE['fbsr_'.$modSettings['fb_app_id']]))
			        $token = $new_token;
			    else
			        $token = str_replace('access_token=','',$modSettings['fb_replytoken']);

				$nobbc_body = preg_replace('|[[\/\!]*?[^\[\]]*?]|si', '', $_POST['message']);

			    $attachment['access_token'] = $token;
	            $attachment['message'] = $user_info['name'].': '.$nobbc_body;

				try {

				    $post_app = $facebook->api(''.$fb_post_id.'/comments','POST',$attachment);

				}catch (FacebookApiException $e){
				    //what todo here? hmmmmmmmm
		        }
			}
		}
	}

    public static function facebook_actions(&$actionArray){

        $actionArray['facebook'] = array('Facebook/Facebook.php', array('SAFacebook', 'Facebook'));
        $actionArray['facebookintegrate'] = array('Facebook/FacebookIntergrate.php', array('SAFacebookintergrate', 'Facebookint'));
    }

    public static function face_load(){

		global $sourcedir, $boarddir;

        if (!class_exists('Services_JSON')) //This may or may not have been loaded by other mod ie my twitter or gplus mods
            include($sourcedir.'/Facebook/FacebookCompat.php');

		include($boarddir.'/facebookauth/facebook.php');
    }

    public static function face_init(){

	    global $scripturl, $access_token, $FaceBookUsername1, $loginUrl, $profileUrl, $FaceBookEmail, $pic, $Url, $user_info, $FaceBookUsername, $user, $facebook, $modSettings, $FacebookId, $FacebookName, $loginUrl;

        $facebook = new Facebook(array('appId'  => $modSettings['fb_app_id'],'secret' => $modSettings['fb_app_secret'],));

        //$user = fb_cookie_parse();

		//if(!isset($user)){
		    $user = $facebook->getUser();
		//}

        $Url = $facebook->getLoginUrl(array('redirect_uri' => $scripturl.'?action=facebook','scope' => 'email,publish_actions'));
        $loginUrl = $facebook->getLoginUrl(array('redirect_uri' => $scripturl.'?action=facebookintegrate','scope' => 'email,publish_actions'));
	    $profileUrl = $facebook->getLoginUrl(array('redirect_uri' => $scripturl.'?action=profile','scope' => 'email,publish_actions'));

        if($user){

			//Try it!!!!!
			try {

			    //These api calls can be slow so were only do it if needed
                if(empty($_SESSION['safbusername1']) || empty($_SESSION['safbpic']) || empty($_SESSION['safbuserid']) || empty($_SESSION['safbpemail']) || empty($_SESSION['safbname']) || empty($_SESSION['safbusername'])){

			        $user_profile = $facebook->api('/me?fields=id,name');

				    $_SESSION['safbname'] =  self::character_clean($user_profile['name']);
				    $_SESSION['safbusername'] = !empty($user_profile['username']) ? $user_profile['username'] : $user_profile['name'];
					$_SESSION['safbusername1'] = !empty($user_profile['name']) ? $user_profile['name'] : '';
					$_SESSION['safbuserid'] = $user_profile['id'];

			        $access_token = $facebook->getAccessToken();

			        $param = array(
                        'method'  => 'users.getinfo',
                        'uids'    => $user_profile['id'],
                        'fields'  => 'pic_with_logo, contact_email, proxied_email',
                        'callback'=> '',
		                'access_token'=> $access_token
                    );

					$userInfo =  $facebook->api($param);
				    $_SESSION['safbpic'] = $userInfo[0]['pic_with_logo'];
				    $_SESSION['safbpemail'] = !empty($userInfo[0]['contact_email']) ? $userInfo[0]['contact_email'] : $userInfo[0]['proxied_email'];
			    }

				$FacebookId = $_SESSION['safbuserid'];
		        $FacebookName = $_SESSION['safbname'];
                $FaceBookUsername = $_SESSION['safbusername'];
				$FaceBookUsername1 = $_SESSION['safbusername1'];
			    $FaceBookEmail = $_SESSION['safbpemail'];
			    $pic = $_SESSION['safbpic'];

		    }
			//Catch it!!!!!
		    catch (FacebookApiException $e) {

			    //Throw it!!!!!
				fatal_error($e,false);
                $user = null;
            }
        }
    }

	/***********************************************************************************************************
	* So you found this aint you clever here have a cookie for your effort :P                                  *
	* you must at least specify the type when using these in you post                                          *
    *                                                                                                          *
	* TODO:Fix this doesnt work properly                                                                       *
	*                                                                                                          *
	* Example Usage:                                                                                           *
	* [fb-like type=standard faces=false]http://developers.facebook.com/docs/reference/plugins/like/[/fb-like] *
	* [fb-like type=box_count][/fb-like]                                                                       *
	* [fb-like type=button_count][/fb-like]                                                                    *
	* [fb-like type=standard][/fb-like]                                                                        *
	*                                                                                                          *
	***********************************************************************************************************/
	public static function Facebook_bbc_add_code($codes)
    {
	    global $modSettings, $txt;

		$like_colour = empty($modSettings['liketopiccolour']) ? 'light' : 'dark';

	    $codes[] = array(
			'tag' => 'fb-like',
			'type' => 'unparsed_content',
			'parameters' => array(
				'type' => array('optional' => true, 'value' => ' layout="$1"'),
				'faces' => array('optional' => true, 'value' => ' show_faces="$1"'),
			),
			'content' => '<fb:like href="$1" send="false" {type} width="450" colorscheme="'.$like_colour.'" {faces}></fb:like>',
	    );
    }

    public static function call_facebook_hook($hook, $parameters = array()){

	    $results = array();

	    $functions = array(
	        'show_facebook_friendpile',
		    'show_facebook_comments',
		    'show_facebook_like_button',
		    'show_facebook_send',
		    'show_facebook_live',
		    'show_facebook_activity',
		    'show_facebook_recomendation',
		    'show_facebook_likebox',
		    'show_facebook_login',

	    );

	    // Loop through each function.
	    foreach ($functions as $function)
	    {
	        if($function == $hook){

		        $function = trim($function);
			    $results[$function] = call_user_func_array(array('SAFacebookhooks',$function), $parameters);

	        }
        }
	    return $results;
    }

    public static function face_USettings($mem,$lect,$we) {

	    global $smcFunc;

	    $results = $smcFunc['db_query']('', '
		    SELECT m.{raw:lect}
		    FROM {db_prefix}members AS m
		    WHERE m.{raw:we} = {string:mem}
		    LIMIT 1',
		    array(
			    'mem' => $mem,
			    'lect' => $lect,
			    'we' => $we
		    )
	    );
	    $temp = $smcFunc['db_fetch_assoc']($results);
	    $smcFunc['db_free_result']($results);

	    return $temp[$lect];
    }

    public static function update_themes_face_del($var, $userid){

		global $smcFunc;

	    $smcFunc['db_query']('', '
		    DELETE FROM {db_prefix}themes
		    WHERE variable = {string:var} AND id_member = {int:mem}',
	        array(
		        'mem' => $userid,
		        'var' => $var,
	        )
		);
    }

    public static function update_themes_face($member, $var, $userid){

	    global $smcFunc;

	    $smcFunc['db_insert']('ignore',
               '{db_prefix}themes',
            array('id_member' => 'int', 'id_theme' => 'int', 'variable' => 'string', 'value' => 'string',),
            array($member, 1, $var, $userid,),
            array('id_member', 'id_theme')
        );
    }

    public static function get_boardsfb($type,$id=0){

	    global $smcFunc, $context, $board;

        $req1 = $smcFunc['db_query']('', '
	        SELECT COUNT(*) AS total
	        FROM {db_prefix}boards
	        WHERE {raw:type} = {int:one} AND id_board = {raw:current_board}',
	        array(
                'one' => 1,
                'current_board' => $id,
			    'type' => $type,
            )
	    );

       $row = $smcFunc['db_fetch_assoc']($req1);
       $result = $row['total'];
       $smcFunc['db_free_result']($req1);

        if ($result == 0)
		    $context[$type] = false;
	    else
            $context[$type] = true;

    }

    public static function character_clean($replace){

        global $context;

        $replace = preg_replace('~[\t\n\r\x0B\0' . (!empty($context['utf8']) ? ($context['server']['complex_preg_chars'] ? '\x{A0}' : "\xC2\xA0") : '\xA0') . ']+~' . (!empty($context['utf8']) ? 'u' : ''), ' ', $replace);

        return $replace;
    }

    public static function show_facebook_login($insmf){

		global $scripturl, $modSettings;

        if($insmf == false){
		    fb_call_js_sdk();
		}

        $facebook = new Facebook(array('appId'  => $modSettings['fb_app_id'],'secret' => $modSettings['fb_app_secret'],));

        $loginUrl = $facebook->getLoginUrl(array('redirect_uri' => $scripturl.'?action=facebookintegrate','scope' => 'email,publish_actions'));

		echo'<a href="'.$loginUrl.'"><img src="'.$modSettings['fb_log_logo'].'" alt="" /></a>';
    }

    public static function show_facebook_friendpile($width,$rows,$insmf){
        global $modSettings;

		if($insmf == false){
		    fb_call_js_sdk();
		}

		echo'<script src="http://connect.facebook.net/'.$modSettings['fb_admin_intern1'].'/all.js#appId='.$modSettings['fb_app_id'].'&amp;xfbml=1"></script>';
        echo'<fb:facepile width="'.$width.'" max_rows="'.$rows.'"></fb:facepile>';
    }

    public static function show_facebook_likebox($furl,$width,$colour,$faces,$stream,$header,$insmf){
	    global $modSettings;

		if($insmf == false){
		    fb_call_js_sdk();
		}

		echo'<script src="http://connect.facebook.net/'.$modSettings['fb_admin_intern1'].'/all.js#appId='.$modSettings['fb_app_id'].'&amp;xfbml=1"></script>';
        echo'<fb:like-box href="'.$furl.'" width="'.$width.'" colorscheme="'.$colour.'" show_faces="'.$faces.'" stream="'.$stream.'" header="'.$header.'"></fb:like-box>';
    }

    public static function show_facebook_recomendation($url,$width,$height,$colour,$header,$insmf){
        global $modSettings;

	    if($insmf == false){
		    fb_call_js_sdk();
		}

		echo'<script src="http://connect.facebook.net/'.$modSettings['fb_admin_intern1'].'/all.js#appId='.$modSettings['fb_app_id'].'&amp;xfbml=1"></script>';
	    echo'<fb:recommendations '.$url.'" width="'.$width.'" height="'.$height.'" header="'.$header.'" colorscheme="'.$colour.'" font="" border_color=""></fb:recommendations>';
    }

    public static function show_facebook_activity($url,$width,$height,$colour,$header,$insmf){
        global $modSettings;

		if($insmf == false){
		    fb_call_js_sdk();
		}

		echo'<script src="http://connect.facebook.net/'.$modSettings['fb_admin_intern1'].'/all.js#appId='.$modSettings['fb_app_id'].'&amp;xfbml=1"></script>';
	    echo'<fb:activity site="'.$url.'" width="'.$width.'" height="'.$height.'" header="'.$header.'" colorscheme"'.$colour.'" font="" border_color="" recommendations="false"></fb:activity>';
    }

    public static function show_facebook_live($width,$height,$xid,$insmf){
		global $modSettings;

		if($insmf == false){
		    fb_call_js_sdk();
		}

		echo'<script src="http://connect.facebook.net/'.$modSettings['fb_admin_intern1'].'/all.js#appId='.$modSettings['fb_app_id'].'&amp;xfbml=1"></script>';
        echo'<fb:live-stream event_app_id="'.$modSettings['fb_app_id'].'" width="'.$height.'" height="'.$height.'" xid="'.$xid.'" always_post_to_friends="true"></fb:live-stream>';
    }

    public static function show_facebook_send($url,$insmf){
        global $modSettings;

		if($insmf == false){
		    fb_call_js_sdk();
		}

		echo'<script src="http://connect.facebook.net/'.$modSettings['fb_admin_intern1'].'/all.js#appId='.$modSettings['fb_app_id'].'&amp;xfbml=1"></script>';
	    echo'<fb:send href="'.$url.'" font=""></fb:send>';
    }

    public static function show_facebook_comments($url,$numpost,$width,$colour,$insmf){
		global $modSettings;

        if($insmf == false){
		    fb_call_js_sdk();
		}

		echo'<script src="http://connect.facebook.net/'.$modSettings['fb_admin_intern1'].'/all.js#appId='.$modSettings['fb_app_id'].'&amp;xfbml=1"></script>';
        echo'<fb:comments href="'.$url.'" num_posts="'.$numpost.'" width="'.$width.'" colorscheme="'.$colour.'"></fb:comments>';
    }


    public static function show_facebook_like_button($url,$verb,$send,$layout,$colour,$faces,$insmf){
        global $modSettings;

		if($insmf == false){
		    fb_call_js_sdk();
		}

		echo'<script src="http://connect.facebook.net/'.$modSettings['fb_admin_intern1'].'/all.js#appId='.$modSettings['fb_app_id'].'&amp;xfbml=1"></script>';
	    echo'<fb:like href="',$url,'" send="',$send,'" layout="',$layout,'"  action="'.$verb.'" colorscheme="'.$colour.'" width="450" show_faces="'.$faces.'" font=""></fb:like>';
    }

	public static function show_facebook_like_bar($url,$pos,$verb,$rtime,$insmf){
	    global $modSettings;

		if($insmf == false){
		    fb_call_js_sdk();
		}

		echo'<script src="http://connect.facebook.net/'.$modSettings['fb_admin_intern1'].'/all.js#appId='.$modSettings['fb_app_id'].'&amp;xfbml=1"></script>';
	    echo'<fb:recommendations-bar href="'.$url.'" action="'.$verb.'" trigger="manual" read_time="'.$rtime.'" side="'.$pos.'"></fb:recommendations-bar>';
    }
///////////////////////////////////////////End Facebook Plugins////////////////////////////////////////////////
}

function fb_check_locale() {
	global $modSettings;
		// validate that they're using a valid locale string
		$fb_valid_fb_locales = array(
			'ca_ES', 'cs_CZ', 'cy_GB', 'da_DK', 'de_DE', 'eu_ES', 'en_PI', 'en_UD', 'ck_US', 'en_US', 'es_LA', 'es_CL', 'es_CO', 'es_ES', 'es_MX',
			'es_VE', 'fb_FI', 'fi_FI', 'fr_FR', 'gl_ES', 'hu_HU', 'it_IT', 'ja_JP', 'ko_KR', 'nb_NO', 'nn_NO', 'nl_NL', 'pl_PL', 'pt_BR', 'pt_PT',
			'ro_RO', 'ru_RU', 'sk_SK', 'sl_SI', 'sv_SE', 'th_TH', 'tr_TR', 'ku_TR', 'zh_CN', 'zh_HK', 'zh_TW', 'fb_LT', 'af_ZA', 'sq_AL', 'hy_AM',
			'az_AZ', 'be_BY', 'bn_IN', 'bs_BA', 'bg_BG', 'hr_HR', 'nl_BE', 'en_GB', 'eo_EO', 'et_EE', 'fo_FO', 'fr_CA', 'ka_GE', 'el_GR', 'gu_IN',
			'hi_IN', 'is_IS', 'id_ID', 'ga_IE', 'jv_ID', 'kn_IN', 'kk_KZ', 'la_VA', 'lv_LV', 'li_NL', 'lt_LT', 'mk_MK', 'mg_MG', 'ms_MY', 'mt_MT',
			'mr_IN', 'mn_MN', 'ne_NP', 'pa_IN', 'rm_CH', 'sa_IN', 'sr_RS', 'so_SO', 'sw_KE', 'tl_PH', 'ta_IN', 'tt_RU', 'te_IN', 'ml_IN', 'uk_UA',
			'uz_UZ', 'vi_VN', 'xh_ZA', 'zu_ZA', 'km_KH', 'tg_TJ', 'ar_AR', 'he_IL', 'ur_PK', 'fa_IR', 'sy_SY', 'yi_DE', 'gn_PY', 'qu_PE', 'ay_BO',
			'se_NO', 'ps_AF', 'tl_ST'
		);

		$locale = $modSettings['fb_admin_intern1'];

		// convert locales like "es" to "es_ES", in case that works for the given locale (sometimes it does)
		if (strlen($locale) == 2) {
			$locale = strtolower($locale).'_'.strtoupper($locale);
		}

		// convert things like de-DE to de_DE
		$locale = str_replace('-', '_', $locale);

		// some people use UK instead of GB
		$locale = str_replace('en_UK', 'en_GB', $locale);

		// check to see if the locale is a valid FB one, if not, use en_US as a fallback
		if ( !in_array($locale, $fb_valid_fb_locales) ) {
			$locale = 'en_US';
		}

	return $locale;
}

function fb_call_js_sdk(){

	global $boardurl, $modSettings;

    echo'<div id="fb-root"></div>
    <script type="text/javascript">
        window.fbAsyncInit = function() {
        FB.init({
            appId      : \''.$modSettings['fb_app_id'].'\', // App ID
            channelURL : \''.$boardurl.'/facebookauth/channel.html\', // Channel File
            status     : true, // check login status
            cookie     : true, // enable cookies to allow the server to access the session
            oauth      : true, // enable OAuth 2.0
            xfbml      : true,  // parse XFBML
        });

	        FB.XFBML.parse();

        };

        // Load the SDK Asynchronously
        (function(d){
           var js, id = \'facebook-jssdk\'; if (d.getElementById(id)) {return;}
           js = d.createElement(\'script\'); js.id = id; js.async = true;
           js.src = \'//connect.facebook.net/'.fb_check_locale().'/all.js\';
           d.getElementsByTagName(\'head\')[0].appendChild(js);
        }(document));
    </script>';

}
// the cookie is signed using our application secret so its unfakable as long as you dont give away the secret
function fb_cookie_parse() {
    global $modSettings;

	$args = array();

	if(!empty($_COOKIE['fbsr_'. $modSettings['fb_app_id']])){
	    if (list($encoded_sig, $payload) = explode('.', $_COOKIE['fbsr_'. $modSettings['fb_app_id']], 2) ) {
		    $sig = fb_base64_url_decode($encoded_sig);
		    if (hash_hmac('sha256', $payload, $modSettings['fb_app_secret'], true) == $sig) {
			    $args = json_decode(fb_base64_url_decode($payload), true);
		    }
	    }
	}

	return $args;
}

//this isnt used right now but will be in the futer for registering
function fb_parse_signed_request($signed_request, $secret) {

	list($encoded_sig, $payload) = explode('.', $signed_request, 2);

    // decode the data
    $sig = fb_base64_url_decode($encoded_sig);
    $data = json_decode(fb_base64_url_decode($payload), true);

    if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
        die('Unknown algorithm. Expected HMAC-SHA256');
        return null;
    }

    // check sig
    $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
    if ($sig !== $expected_sig) {
        die('Bad Signed JSON signature!');
        return null;
    }

    return $data;
}

// this is not a hack or a dangerous function the base64 decode is required
// because Facebook is sending back base64 encoded data in the signed_request bits.
// See http://developers.facebook.com/docs/authentication/signed_request/ for more info
function fb_base64_url_decode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
}

function fb_shorten($url, $user, $key) {
    global $sourcedir;

	require_once($sourcedir .'/Subs-Package.php');

	$bitly_url = 'http://api.bit.ly/v3/shorten?login='.$user.'&apiKey='.$key.'&uri='.$url.'&format=txt';
	$bitly_url_short = fetch_web_data($bitly_url);

	if($bitly_url_short == null)
		$bitly_url_short = $url;

	return $bitly_url_short;
}

function template_facebook_above(){

	global $context, $scripturl, $sourcedir, $board, $boardurl, $modSettings, $txt;

    fb_call_js_sdk();

	if(!empty($_GET['topic'])){

	    SAFacebookhooks::get_boardsfb('like_enable',$board);
    }

	if(!empty($context['like_enable']) && !empty($modSettings['fb_app_liketopic']) && !empty($_GET['topic']) && !empty($_GET['action']) != 'post'){

	    $like_layout = empty($modSettings['liketopiclayout']) ? 'standard' : 'button_count';
        $like_verb = empty($modSettings['liketopicverb']) ? 'like' : 'recommend';
        $like_colour = empty($modSettings['liketopiccolour']) ? 'light' : 'dark';
	    $like_send = empty($modSettings['likeshowsend']) ? 'true' : 'false';
	    $like_faces = empty($modSettings['likeshowfaces']) ? 'true' : 'false';
	    $topiccom = (isset($_GET['topic']) ? $_GET['topic'] : '');
	    $turl = ''.$scripturl .'?topic='.$context['current_topic'].'.0';

		SAFacebookhooks::call_facebook_hook('show_facebook_like_button',array($turl,$like_verb,$like_send,$like_layout,$like_colour,$like_faces,true));

		echo'<br />';
	}

	$context['facebook_messages'] = array(
		'FacebookProfile' => array(
			'get' => isset($_GET['facebook_sync']),
			'message' => $txt['fb_synct'],
		),
		'FacebookAvatar' => array(
			'get' => isset($_GET['avatar']),
			'message' => $txt['fb_aimp2'],
		),
		'FacebookBuddy' => array(
			'get' => isset($_GET['bud']),
			'message' => $txt['fb_app_bsynctrue'],
		),
		'FacebookUnsync' => array(
			'get' => isset($_GET['facebook_unsync']),
			'message' => $txt['fb_syncf'],
		),
	);

	foreach ($context['facebook_messages'] as $message){
	    if($message['get']){
	        echo'
	        <div class="windowbg" id="profile_success" align="center">
		          '.$message['message'].'
		    </div>';
	    }
    }
}

function template_facebook_below(){

    global $modSettings, $txt, $user_info, $scripturl, $board,$context;

    if(!empty($_GET['topic'])){

        SAFacebookhooks::get_boardsfb('com_enable',$board);
    }

    if (!empty($modSettings['fb_app_enablecom'])&& !empty($_GET['topic']) && !empty($_GET['action']) != 'post'){

		if(!empty($context['com_enable']) && $modSettings['fb_admin_commets_post_board'] >= 1){

	        $com_colour = empty($modSettings['comcolour']) ? 'light' : 'dark';
	        $com_com = !empty($modSettings['fb_admin_commets_post_board']) ? $modSettings['fb_admin_commets_post_board'] : '0';
	        $topiccom = (isset($_GET['topic']) ? $_GET['topic'] : '');
	        $turl = ''.$scripturl .'?topic='.$context['current_topic'].'.0';

            echo'<br />
            <div class="cat_bar">
	            <h3 class="catbg">
	                '.$txt['fb_comments'].'
	            </h3>
	        </div>

            <span class="upperframe"><span></span></span>
	            <div class="roundframe centertext">';

                    SAFacebookhooks::call_facebook_hook('show_facebook_comments',array($turl,$com_com,'800',$com_colour,true));

			    echo'
				</div>
	        <span class="lowerframe"><span></span></span>';
	    }
    }

	if(!empty($_COOKIE['pwdone']))
    {

		setcookie("pwdone", 0);

		SAFacebookhooks::face_init();
        global $FacebookName;

		$forum_name_body = strip_tags(htmlspecialchars($context['forum_name'], ENT_QUOTES));

		echo' <script type="text/javascript">

	            function streamPublish(name, description, hrefTitle, hrefLink, userPrompt){
                    FB.ui(
                    {
                        method: \'stream.publish\',
                        message: \'\',
                            attachment: {
                                name: name,
                                caption: \'\',
                                description: (description),
                                href: hrefLink
                            },
                    action_links:[
                    {
			            text: hrefTitle,
				        href: hrefLink
			        }
                    ],
                       user_prompt_message: userPrompt
                    },
                    function(response) {

                    });
                }
	        function publishStream(){
                streamPublish("'.$forum_name_body.' ", "'.$FacebookName.' '.$txt['fb_haslogged'].' '.$forum_name_body.' '.$txt['fb_haslogged2'].'", \''.$forum_name_body.'\', "'.$scripturl.'");
            }


            window.onload = function()
            {
                publishStream();
                facebook_onload(true);
            };
        </script>';
    }
}
?>