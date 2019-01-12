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

function gplus_integrate_logout(){

    if(isset($_SESSION['token']))
	    unset($_SESSION['token']);
}

function gplus_integrate_login($user, $hashPasswd, $cookieTime){

    global $user_settings;

    if(isset($_GET['syncgp'])){
	
        $gdata = $_SESSION['gplusdata'];
        $_SESSION['gplus']['id'] = $gdata['id'];
	    $_SESSION['gplus']['name'] = $gdata['name'];
	
	    updateMemberData($user_settings['id_member'], 
	        array(
		        'gpid' => $_SESSION['gplus']['id'],
			    'gpname' => $_SESSION['gplus']['name'],	
		    )
	    );
   
        unset($_SESSION['gplus']['id']);
        unset($_SESSION['gplus']['name']);
        unset($_SESSION['gplusdata']);
	}
	else{
	    return;
	}
}

function ob_gplus(&$buffer){
    global $authUrl, $context, $modSettings, $txt;
	
	if(empty($modSettings['gp_app_enabled']) || isset($_REQUEST['xml']))
	   return $buffer;
	
	if (!$context['user']['is_logged']){
	    
		gplus_init_auth_url();
	
	    $txt['guestnew'] = sprintf($txt['welcome_guest'], $txt['guest_title']);
	        
	    $buffer = preg_replace('~(' . preg_quote('<div class="info">'. $txt['guestnew']. '</div>') . ')~', '<a href="'.$authUrl.'"><img src="'.$modSettings['gp_app_custon_logimg'].'" alt="" /></a><div class="info">'. $txt['guestnew']. '</div>', $buffer);	
	    $buffer = preg_replace('~(' . preg_quote($txt['forgot_your_password']. '</a></p>') . ')~', $txt['forgot_your_password']. '</a></p><div align="center"><a href="'.$authUrl.'"><img src="'.$modSettings['gp_app_custon_logimg'].'" alt="" /></a></div>', $buffer);
	    $buffer = preg_replace('~(' . preg_quote('<dt><strong><label for="smf_autov_username">'. $txt['username']. ':</label></strong></dt>') . ')~','<dt><strong>'.$txt['gp_app_rwf'].':</strong><div class="smalltext">'.$txt['gp_app_regmay'].'</div></dt><dd><a href="'.$authUrl.'"><img src="'.$modSettings['gp_app_custon_logimg'].'" alt="" /></a></dd><dt><strong><label for="smf_autov_username">'. $txt['username']. ':</label></strong></dt>', $buffer);
	}
	return $buffer;
}

function gplus_actions(&$actionArray){

    $actionArray['gplus'] = array('GPlus/GPlus.php', 'GPlus');
}

function gplus_admin_areas(&$admin_areas){
	global $scripturl, $txt;
	
	if(allowedTo('admin_forum')){
        gplus_array_insert($admin_areas, 'layout',
	        array(
	            'sa_gplus' => array(
		            'title' => $txt['gp_googplus'],
		            'areas' => array(
			            'gplus' => array(
				            'label' => $txt['gp_app_config'],
				           'file' => 'GPlus/GplusAdmin.php',
				            'function' => 'gplusa',
				            'custom_url' => $scripturl . '?action=admin;area=gplus',
				            'icon' => 'server.gif',
			                'subsections' => array(
				                'gplus' => array($txt['gp_app_config']),
				                'gplus_logs' => array($txt['gp_app_logs']),
			                ),
						),
		            ),
		        ),
	        )
        );
    }
}

function gplus_profile_areas(&$profile_areas){
	global $user_settings, $txt, $authUrl, $scripturl, $modSettings, $sc;

	if(empty($user_settings['gpid']) && !empty($modSettings['gp_app_enabled'])){
	    
		gplus_init_auth_url();
	
		gplus_array_insert($profile_areas, 'profile_action',
		    array(
			    'profile_gp' => array(
			        'title' => $txt['gp_googplus'],
			        'areas' => array(
				        'gsettings' => array(
					        'label' => $txt['gp_app_aso_account'],
					        'custom_url' => $authUrl.'" onclick="return confirm(\''.$txt['gp_app_aso_account_confirm'].'\');"',
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
	if(!empty($user_settings['gpid']) && !empty($modSettings['gp_app_enabled'])){
	    gplus_array_insert($profile_areas, 'profile_action',
		    array(
			    'profile_gp' => array(
			        'title' => $txt['gp_googplus'],
			        'areas' => array(
				        'gsettings' => array(
					        'label' => 'Settings',
					        'file' => 'GPlus/GPlus.php',
					        'function' => 'gplus_Profile',
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


function gplus_USettings($id,$row,$where) {

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

function gplus_loadTheme(){
    global $modSettings, $user_info, $context;
	
	loadLanguage('GPlus');
	
	if (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (isset($_REQUEST['action']) || in_array(isset($_REQUEST['action']), array('gplus'))))
    {
	    $modSettings['allow_guestAccess'] = 1;
    }
	
	if(isset($_SESSION['gplus']['idm']) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'login' && !empty($modSettings['gp_app_enabledautolog'])){

		$context['gplus_id'] = gplus_USettings($_SESSION['gplus']['idm'],'id_member','gpid');

		if (!empty($context['gplus_id'])) {
			redirectexit('action=gplus;area=connectlog');   
		}
    }	
	
	if (!isset($_REQUEST['xml']))
    {
        $layers = $context['template_layers'];
        $context['template_layers'] = array();
        foreach ($layers as $layer)
        {
            $context['template_layers'][] = $layer;
                if ($layer == 'body' || $layer == 'main')
                    $context['template_layers'][] = 'gplus';
        }
    }
}

function template_gplus_above(){
    global $context, $board, $modSettings, $scripturl;

	$show_gplus = explode(',', !empty($modSettings['gp_app_board_showplus1']) ? $modSettings['gp_app_board_showplus1'] : 0);
	if(in_array($board,$show_gplus) && !empty($modSettings['gp_app_enabled']) && !empty($context['current_topic']) && !empty($_GET['topic']) && !empty($_GET['action']) != 'post'){
	    echo '<g:plusone href="' . $scripturl . '?topic=' . $context['current_topic'] . '" size="medium"></g:plusone>
	    <script type="text/javascript" src="http://apis.google.com/js/plusone.js"></script>';
	}
}

function template_gplus_below(){}

function gplus_load(){
    global $boarddir;
	
    require_once($boarddir.'/gplusauth/apiClient.php');
	require_once($boarddir.'/gplusauth/contrib/apiOauth2Service.php');
}

function gplus_init_auth_url(){
    global $authUrl;
	
    gplus_load();
    try { 	
	    $client = new apiClient();
        $plus = new apiOauth2Service($client);
        $authUrl = $client->createAuthUrl(); 
	} 
	catch (Exception $e) {
        $authUrl = '';
    }
}

function gplus_init_auth(){
    
	gplus_load();
    $client = new apiClient();
    $oauth2 = new apiOauth2Service($client);
	
	if (isset($_GET['code'])) {
        $client->authenticate();
        $_SESSION['token'] = $client->getAccessToken();
    }
    if (isset($_SESSION['token'])) {
        $client->setAccessToken($_SESSION['token']);
    }

    if ($client->getAccessToken()) {
       $user = $oauth2->userinfo->get();
       $_SESSION['token'] = $client->getAccessToken();
    }
	if(isset($user) && isset($_GET['code']))
    { 
        $_SESSION['gplusdata'] = $user;
		$_SESSION['gplus']['idm'] = $user['id'];
		$_SESSION['gplus']['pic'] = !empty($user['picture']) ? $user['picture'] : '';
		redirectexit('action=gplus;auth=done');
    } 
}

function gplus_show_auth_login(){
    global $authUrl, $modSettings;
    
	gplus_init_auth_url();
	echo'<a href="'.$authUrl.'"><img src="'.$modSettings['gp_app_custon_logimg'].'" alt="" /></a>';
}

function gplus_loadUser($member_id,$where_id) {

	global $smcFunc;

	$results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}members
		WHERE {raw:where_id} = {string:member_id}
		LIMIT 1',
		array(
			'member_id' => $member_id,
			'where_id' => $where_id,
		)
	);
	$temp = $smcFunc['db_fetch_assoc']($results);
	$smcFunc['db_free_result']($results);

	return $temp;
}

function gplus_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
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
?>