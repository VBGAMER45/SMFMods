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

class SAFacebook
{

	private $subActions = array();
	protected $fb_admin_invite_text;


	/**
     * Setup the object, gather all of the relevant settings
     */
	protected function __construct() {

	    global $scripturl, $facebook, $user, $modSettings, $settings, $context;

		$this->fb_admin_invite_text = str_replace('{burl}', $scripturl,$modSettings['fb_admin_invite_text']);

		$this->subActions = array(
	        'main' => 'fbc_main',
		    'friends' => 'fbc_friend',
	    );
	}

	public function Facebook(){

		global $fb_object, $fb_hook_object, $context;

		loadTemplate('Facebook');
        $_SESSION['safbKeys'] = false;

		$fb_object = new SAFacebook;

		if(!$fb_hook_object->modSettings['fb_app_enabled'])
		    redirectexit();

		if(!$fb_hook_object->modSettings['fb_mode1'])
		    redirectexit();

	    $fb_object->fbc_doheaders();

	    $_REQUEST['area'] = isset($_REQUEST['area']) && isset($fb_object->subActions[$_REQUEST['area']]) ? $_REQUEST['area'] : 'main';

	    $context['page_title'] = $fb_hook_object->txt['fb_main5'];
	    $context['sub_action'] = $_REQUEST['area'];

	    call_user_func(array('SAFacebook', $fb_object->subActions[$_REQUEST['area']]));
    }

    public function fbc_main(){

		global $context;

        $context['sub_template']  = 'fbc_main';
        $context['fb_do'] = 'main';

    }

    public function fbc_friend(){

	    global $context, $fbuser, $Url, $FacebookId, $fb_hook_object, $friends, $scripturl, $fb_object, $curOffset;

        $context['sub_template']  = 'fbc_friends';
        $context['fb_do'] = 'friends';
        SAFacebookhooks::face_init();
        $facebook = new Facebook(array('appId'  => $fb_hook_object->modSettings['fb_app_id'],'secret' => $fb_hook_object->modSettings['fb_app_secret'],));
		$Url = $facebook->getLoginUrl(array('redirect_uri' => $scripturl.'?action=facebook','scope' => 'email,publish_actions'));

		$fb_object->fbc_cons_pages('friends',32);

        if(!$fb_hook_object->modSettings['fb_mode2'])
		    redirectexit();

		$fbuser = $FacebookId;

        if($fbuser){

			//Try it!!!!!
			try{

				//These api calls can be slow so were only do it if needed
				if(empty($_SESSION['safbfriends'][$curOffset])){

					$_SESSION['safbfriends'][$curOffset] = $facebook->api('/me/friends?offset='.$curOffset.'&limit=32');
				}

				$friends = $_SESSION['safbfriends'][$curOffset];
			}
			//Catch it!!!!!
		    catch(FacebookApiException $e){

				//Throw it!!!!!
				fatal_error($e,false);

            }
        }
    }

	public static function fbc_doheaders(){

		global $fb_object, $forum_slogan, $forum_name_body, $fb_hook_object, $context;

	    $forum_invite_body = strip_tags(htmlspecialchars($fb_object->fb_admin_invite_text, ENT_QUOTES));

	    $context['html_headers'] .= '
		    <script type="text/javascript">
		        function newInvite(){
                    var receiverUserIds = FB.ui({
                        method : \'apprequests\',
                        message: \''.$forum_invite_body.'\',
                    },
                        function(receiverUserIds) {
                            console.log("IDS : " + receiverUserIds.request_ids);
                        }
                    );
                }
		    </script>';
    }

	public static function fbc_cons_pages($area,$cur){

		global $fb_object, $curOffset, $fb_hook_object, $context;

	    $context['page'] = !empty($_REQUEST['page']) ? $_REQUEST['page'] : '0';
        $curPage = $context['page'];
        $curOffset = $curPage * $cur;
        $nextPage = $curPage + 1;

	    if ($curPage > 0) {
            $prevPage = $curPage - 1;
        }
		else {
            $prevPage = 0;
        }

        $context['page_index'] = '
	        <a href="'.$fb_hook_object->scripturl. '?action=facebook;area='.$area.';page='.$prevPage.'">'.$fb_hook_object->txt['previous_next_back'].'</a>
            <a href="'.$fb_hook_object->scripturl. '?action=facebook;area='.$area.';page='.$nextPage.'">'.$fb_hook_object->txt['previous_next_forward'].'</a>';
    }
}
?>