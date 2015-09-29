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
class SAFacebookadmin
{

	private $subActions = array();

	protected $txt;
	protected $scripturl;

	/**
     * Setup the object, gather all of the relevant settings
     */
	protected function __construct() {

		global $scripturl, $txt;

		$this->txt = $txt;
		$this->scripturl = $scripturl;

		$this->subActions = array(
	        'facebook' => 'faceadmin',
	        'facebooklog' => 'facelogs',
	        'boards' => 'face_tweet',
	        'social' => 'faceplug',
	        'about' => 'faceab',
	        'hooks' => 'facehook',
			'publisher' => 'facepub',
			'og' => 'faceog',
	    );
	}

	public function Facebooka(){

		global $fb_object, $fb_hook_object, $modSettings, $sourcedir, $context;

	    require_once($sourcedir . '/ManageServer.php');
	    loadTemplate('FacebookAdmin');
	    allowedTo('admin_forum');

        $fb_object = new SAFacebookadmin;

		$context['html_headers'] .= '
		    <script type="text/javascript">
		        function fbLogin() {

					FB.login(function(response) {
                        if (response.authResponse) {
                            alert(\''. $fb_object->txt['fb_grantperm1'].'\');
                            window.location = \'https://www.facebook.com/dialog/oauth?client_id='.$modSettings['fb_app_id'].'&redirect_uri='.urlencode($fb_hook_object->scripturl.'').'&scope=email,publish_actions\';
                        }
						else {
                            alert(\''.$fb_object->txt['fb_grantperm'].'\');
                        }
                    }, {scope: \'email,publish_actions\'});

                }
		    </script>';

        $context['page_title'] = $fb_hook_object->txt['fb_main1'];
	    $context[$context['admin_menu_name']]['tab_data']['title'] = $fb_hook_object->txt['fb_main1'];
	    $context[$context['admin_menu_name']]['tab_data']['description'] = $fb_hook_object->txt['fb_main1'];


	    $_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($fb_object->subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'facebook';

		call_user_func(array('SAFacebookadmin', $fb_object->subActions[$_REQUEST['sa']]));
    }

	protected function faceog() {
        global $fb_hook_object, $context, $sourcedir, $modSettings;

	    // We need this
	    require_once($sourcedir.'/ManageServer.php');
	    $context['sub_template'] = 'show_settings';

        $config_vars = array(
	        array('check', 'fb_app_eog'),
		    array('text', 'fb_app_image'),

	    );

	    if (isset($_GET['save']))
	    {
		    checkSession();
		    saveDBSettings($config_vars);;
		    redirectexit('action=admin;area=facebook;sa=og');
	    }

	    $context['post_url'] = $fb_hook_object->scripturl .'?action=admin;area=facebook;sa=og;save';
	    $context['settings_title'] = $fb_hook_object->txt['fb_app_ogopengraph'];
	    prepareDBSettingContext($config_vars);
    }

	protected function facepub() {
        global $fb_hook_object, $txt, $scripturl, $context, $smcFunc, $sourcedir, $modSettings;

	    // We need this
	    require_once($sourcedir.'/ManageServer.php');
	    $context['sub_template'] = 'show_settings';

		$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['fb_pub_desc'];

		$config_vars = array(
		    array('select', 'fb_admin_grant', array($fb_hook_object->txt['fb_dfbreg5'],$fb_hook_object->txt['fb_pub_adsetnt'],$fb_hook_object->txt['fb_pub_adsetmem'],$fb_hook_object->txt['fb_pub_adsetmematp'])),
			array('select', 'fb_postto', array($fb_hook_object->txt['fb_pub_adsetpage'], $fb_hook_object->txt['fb_pub_adsetprofile'])),
			'',
			array('text', 'fb_admin_pid'),
			array('text', 'fb_admin_uid'),
			'',
			array('check', 'fb_reply'),
			'',
			array('text', 'fb_atoken'),
		    array('text', 'fb_app_atokenpage'),
		    array('text', 'fb_replytoken'),
			/*'',
			array('check', 'fb_app_shorturl'),
		    array('text', 'fb_app_bituname'),
		    array('text', 'fb_app_bitkey'),*/

	    );

	    if (isset($_GET['save']))
	    {
			checkSession();
		    saveDBSettings($config_vars);
			$_SESSION['safbKeys'] = true;
		    redirectexit('action=admin;area=facebook;sa=publisher');

	    }

	    $context['post_url'] = $fb_hook_object->scripturl .'?action=admin;area=facebook;sa=publisher;save';
	    $context['settings_title'] = $fb_hook_object->txt['fb_app_publisher'];
	    prepareDBSettingContext($config_vars);
    }

    protected function facehook() {
        global $boarddir, $user_info, $context;
        $context['sub_template'] = 'facehook';

	    if (file_exists($boarddir . '/facebookauth/facebookhooks.' . $user_info['language'] . '.txt'))
	        $context['hooks'] = parse_bbc(file_get_contents($boarddir . '/facebookauth/facebookhooks.' . $user_info['language'] . '.txt'), true, 'facebookhooks_' . $user_info['language']);
	    elseif (file_exists($boarddir . '/facebookauth/facebookhooks.txt'))
	        $context['hooks'] = parse_bbc(file_get_contents($boarddir . '/facebookauth/facebookhooks.txt'), true, 'facebookhooks');
	    else
	        $context['hooks'] = '';
    }

    protected function faceab() {

	    global $fb_object, $settings, $context;

	    $context['sub_template'] = 'faceab';

	    $context['facebook_credits'] = array(
		    'SA' => array(
			    'name' => 'Wayne Mankertz',
			    'nickname' => 'SA',
			    'site' => 'http://sa-mods.info',
			     'position' => 'Creator, developer',
		    ),
		    'Spoogs' => array(
			    'name' => 'Shomari Scott',
			    'nickname' => 'Spoogs',
			    'site' => '#',
			    'position' => 'Admin, Beta Tester',
		    ),
		    'DS' => array(
			    'name' => 'Nathan House',
			    'nickname' => 'DS',
			    'site' => 'http://iapplecafe.org/community/',
			    'position' => 'Beta Tester',
		    ),
		    'DjScrappy' => array(
			    'name' => 'Scott Guillet',
			    'nickname' => 'DjScrappy',
			    'site' => 'http://www.maddsmokerz.com/index.php',
			    'position' => 'Beta Tester',
		    ),
		    'Bobomaster' => array(
			    'name' => 'N/A',
			    'nickname' => 'Bobomaster',
			    'site' => 'http://epicpwnage.co.cc/forums/',
			    'position' => 'Beta Tester',
		    ),
	    );
	    $context['facebook_thanks'] = array(
		    'Facebook' => array(
			    'name' => 'Facebook',
			    'site' => 'https://developers.facebook.com/docs/',
			    'position' => 'PHP SDK',
		    ),
		    'Testers' => array(
			    'name' => 'All of you!',
			    'site' => 'http://www.smfhacks.com',
			    'position' => 'Thanks to the SMFHacks community who cared about the project and spent time to help me find bugs!',
		    ),
	    );

	    if (function_exists('curl_init')) {
	        $context['sayesno'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_on.png" width="26" height="26" title="" alt=""/> '.$fb_object->txt['fb_sinfo1'].'';
	    }
	    else{
	        $context['sayesno'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_off.png" width="26" height="26" title="" alt="" /> '.$fb_object->txt['fb_sinfo2'].'';
	    }

	    if (function_exists('json_decode')) {
	       $context['sayesnojson'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_on.png" width="26" height="26" title="" alt="" /> '.$fb_object->txt['fb_sinfo3'].'';
	    }
	    else{
	       $context['sayesnojson'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_off.png" width="26" height="26" title="" alt="" /> '.$fb_object->txt['fb_sinfo4'].'';
	    }

	    if (!defined('PHP_VERSION_ID')) {
           $version = explode('.', PHP_VERSION);
           define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }

        if(PHP_VERSION_ID < 50000){
           $version = explode('.', PHP_VERSION);
           $context['saphpver'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_off.png" width="26" height="26" title="" alt="" /> '.$fb_object->txt['fb_sinfo5'].' '.phpversion();

	    }
	    else{
	        $context['saphpver'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_on.png" width="26" height="26" title="" alt="" /> '.$fb_object->txt['fb_sinfo6'].' '.phpversion();

	    }

	    $context['safe_mode'] = ini_get('safe_mode');

	    if($context['safe_mode']){
	        $context['safe_mode_go'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_on.png" width="26" height="26" title="" alt="" /> '.$fb_object->txt['fb_sinfo7'].'';
	    }
	    else{
	        $context['safe_mode_go'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_off.png" width="26" height="26" title="" alt="" /> '.$fb_object->txt['fb_sinfo8'].'';
	    }
    }

    protected function facelogs(){
        global $sourcedir, $smcFunc, $scripturl, $fb_hook_object, $txt, $context;

	    $context['sub_template'] = 'facelog';
	    $context['settings_title'] = $txt['fb_logmain'];

	    $list_options = array(
		    'id' => 'fb_list',
		    'title' => $fb_hook_object->txt['fb_logmain'],
		    'items_per_page' => 30,
		    'base_href' => $fb_hook_object->scripturl . '?action=admin;area=facebook;sa=facebooklog',
		    'default_sort_col' => 'id_member',
		    'get_items' => array(
			    'function' => create_function('$start, $items_per_page, $sort', '
				    global $smcFunc, $user_info, $txt;

			    $request = $smcFunc[\'db_query\'](\'\', \'
			        SELECT m.id_member,  m.real_name, m.date_registered, mg.online_color, m.fbid, m.fbname
                    FROM {db_prefix}members AS m
                    LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN m.id_group = {int:reg_mem_group}
			        THEN m.id_post_group ELSE m.id_group END)
                    WHERE fbid != {string:one} AND fbid != {string:zero} AND fbname != {string:zero} AND fbname != {string:zero}
			        ORDER BY {raw:sort}
                    LIMIT {int:start}, {int:per_page}\',
                    array(
                       \'one\' => \'\',
			           \'zero\' => \'0\',
			           \'sort\' => $sort,
			           \'start\' => $start,
			           \'per_page\' => $items_per_page,
                       \'reg_mem_group\' => 0,
                    )
				);

				$fbu = array();
				while ($row = $smcFunc[\'db_fetch_assoc\']($request))
					$fbu[] = $row;
				$smcFunc[\'db_free_result\']($request);

				return $fbu;
			'),
		),
		'get_count' => array(
			'function' => create_function('', '
				global $smcFunc, $user_info;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}members
		            WHERE fbid != {string:one} AND fbid != {string:zero} AND fbname != {string:zero} AND fbname != {string:zero}\',
			       array(
			         \'one\' => \'\',
			         \'zero\' => \'0\',
			       )
				);
				list ($total_fbu) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_fbu;
			'),
		),
		'no_items_label' => $fb_hook_object->txt['fb_lognus'],
		'columns' => array(
			'id_member' => array(
				'header' => array(
					'value' => $fb_hook_object->txt['fb_logm'],
				),
				'data' => array(
					'function' => create_function('$log', '
					global $scripturl, $txt;
						return \'<a href="\'. $scripturl. \'?action=profile;u=\'.$log[\'id_member\'].\'"><span style="color:\'.$log[\'online_color\'].\'">\'.$log[\'real_name\'].\'</span></a><br /><div class="smalltext"><strong>\'.$txt[\'fb_loguid\'].\':</strong> \'.$log[\'id_member\'].\'</div>\';
					'),
					'style' => 'width: 10%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'id_member',
					'reverse' => 'id_member DESC',
				),
			),
			'fbname' => array(
				'header' => array(
					'value' => $fb_hook_object->txt['fb_logfbn'],
				),
				'data' => array(
					'function' => create_function('$row', '
						return $row[\'fbname\'];'),
					'style' => 'width: 8%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'fbname',
					'reverse' => 'fbname DESC',
				),
			),
			'fbid' => array(
				'header' => array(
					'value' => $fb_hook_object->txt['fb_logfbid'],
				),
				'data' => array(
					'function' => create_function('$row', '
						return $row[\'fbid\'];'),
					'style' => 'width: 8%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'fbid',
					'reverse' => 'fbid DESC',
				),
			),

			'time' => array(
				'header' => array(
					'value' => $txt['fb_logdr'],
				),
				'data' => array(
					'function' => create_function('$row', '
						return \'<div class="smalltext">\'.timeformat($row[\'date_registered\']).\'</div>\';'),
					'style' => 'width: 10%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'date_registered',
					'reverse' => 'date_registered DESC',
				),
			),
			'actions' => array(
				'header' => array(
					'value' => $txt['fb_logfbp'],
				),
				'data' => array(
					'function' => create_function('$row', '
						global $context, $txt, $scripturl;

						return \'<a href="http://facebook.com/profile.php?id=\'.$row[\'fbid\'].\'" target="blank">\'.$txt[\'fb_logfbp\'].\'</a>\';
					'),
					'style' => 'width: 3%; text-align: center;',
				),
			),
			'action' => array(
				'header' => array(
					'value' => '<input type="checkbox" name="all" class="input_check" onclick="invertAll(this, this.form);" />',
				),
				'data' => array(
					'function' => create_function('$row', '
                         global $sc,$scripturl;
						return \'<input type="checkbox" class="input_check" name="dis[]" value="\' . $row[\'id_member\'] . \'" />\';
					'),
					'style' => 'width: 2%; text-align: center;',
				),
	    	),
		),
		'form' => array(
			'href' => $scripturl.'?action=admin;area=facebook;sa=facebooklog',
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
				$context['session_var'] => $context['session_id'],
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '

						<input type="submit" name="dis_sel" value="'.$txt['fb_app_dissel'].'" class="button_submit" />
						<input type="submit" name="dis_all" value="'.$txt['fb_app_disall'].'" class="button_submit" />'
			),
		),
	);

	    require_once($sourcedir . '/Subs-List.php');

	    createList($list_options);

		if (isset($_POST['dis_all']))
        {
		    checkSession();

	        $smcFunc['db_query']('', '
	            UPDATE {db_prefix}members
		        SET fbname = {string:blank_string}, fbid = {string:blank_string}',
	            array(
			        'blank_string' => '',
	            )
	        );

		    redirectexit('action=admin;area=facebook;sa=facebooklog');
        }
	    elseif (!empty($_POST['dis_sel']) && isset($_POST['dis']))
	    {
		    checkSession();

		    $smcFunc['db_query']('', '
	            UPDATE {db_prefix}members
		        SET fbname = {string:blank_string}, fbid = {string:blank_string}
			    WHERE id_member IN ({array_string:dis_actions})',
	            array(
			        'dis_actions' => array_unique($_POST['dis']),
			        'blank_string' => '',
	            )
	        );

		    redirectexit('action=admin;area=facebook;sa=facebooklog');
	    }
    }

    protected function face_tweet() {

        global $fb_hook_object, $smcFunc, $context;

	    $context['sub_template'] = 'face_boards';
	    $context['settings_title'] = $fb_hook_object->txt['fb_board'];
	    $context['post_url'] = $fb_hook_object->scripturl . '?action=admin;area=facebook;sa=boards;save';

	    if (isset($_GET['save']))
	    {
	        checkSession();
	        $board_post = array('boardspub','boardslike','boardscom');
	        $board_feild = array('pub_enable','like_enable','com_enable');
	        for ($i=0;$i<=2;$i++){
	            $board_id = array_map('intval', !empty($_POST[$board_post[$i]]) ? $_POST[$board_post[$i]] : array());

		        $smcFunc['db_query']('', '
				    UPDATE {db_prefix}boards
				    SET {raw:board_feild} = IF(FIND_IN_SET(id_board, {string:board_ids}), 1, 0)',
				    array(
					    'board_ids' => join(',', $board_id),
					    'board_feild' => $board_feild[$i],
				    )
	            );
	        }
	        redirectexit('action=admin;area=facebook;sa=boards');
	    }

	    $req = $smcFunc['db_query']('', '
			SELECT
			b.id_board, b.id_cat, b.board_order, b.name AS board_name, b.like_enable, b.com_enable, b.pub_enable,
			c.cat_order, c.name AS cat_name
			FROM {db_prefix}boards b
			LEFT JOIN {db_prefix}categories c ON b.id_cat = c.id_cat
			WHERE b.redirect = {string:blank}
			ORDER BY c.cat_order, cat_name, b.board_order, board_name',
			array(
				'blank' => '',
			)
	    );

	    $cats = array();

	    $context['face_boards'] = array();

	    while ($row = $smcFunc['db_fetch_assoc']($req))
	    {
		    if (!in_array($row['id_cat'], $cats))
		    {
		        $cats[] = $row['id_cat'];
		        $context['face_boards'][] = array(
			       'is_cat' => true,
			       'id_cat' => $row['id_cat'],
			       'cat_name' => $row['cat_name'],
		        );
		    }
	        $context['face_boards'][] = $row;
	    }

    }

    protected function faceplug(){

		global $fb_hook_object, $txt, $context, $sourcedir, $modSettings;

	    // We need this
	    require_once($sourcedir.'/ManageServer.php');
	    $context['sub_template'] = 'show_settings';

	    $config_vars = array(
	        array('check', 'fb_app_liketopic'),
		    array('select', 'liketopiclayout', array($fb_hook_object->txt['standard'], $fb_hook_object->txt['button_count'])),
		    array('select', 'liketopicverb', array($fb_hook_object->txt['like'], $fb_hook_object->txt['recommend'])),
		    array('select', 'liketopiccolour', array($fb_hook_object->txt['light'], $fb_hook_object->txt['dark'])),
		    array('select', 'likeshowfaces', array($fb_hook_object->txt['true'], $fb_hook_object->txt['false'])),
		    array('select', 'likeshowsend', array($fb_hook_object->txt['true'], $fb_hook_object->txt['false'])),
		    '',
		    array('check', 'fb_app_enablecom'),
		    array('text', 'fb_admin_commets_post'),
		    array('text', 'fb_admin_commets_post_board'),
		    array('select', 'comcolour', array($fb_hook_object->txt['light'], $fb_hook_object->txt['dark'])),
		    '',
		    array('check', 'fb_app_enablelbox'),
			array('large_text', 'fb_admin_page_url'),
		    array('select', 'likesbhowface', array($fb_hook_object->txt['true'], $fb_hook_object->txt['false'])),
		    array('select', 'likesbhowhead', array($fb_hook_object->txt['true'], $fb_hook_object->txt['false'])),
		    array('select', 'lboxcolour', array($fb_hook_object->txt['light'], $fb_hook_object->txt['dark'])),
		    array('select', 'lboxshowstream', array($fb_hook_object->txt['true'], $fb_hook_object->txt['false'])),
	    );

	    if (isset($_GET['save']))
	    {
		    checkSession();
		    saveDBSettings($config_vars);;
		    redirectexit('action=admin;area=facebook;sa=social');
	    }

		$context['post_url'] = $fb_hook_object->scripturl .'?action=admin;area=facebook;sa=social;save';
	    $context['settings_title'] = $fb_hook_object->txt['fb_social'];
	    prepareDBSettingContext($config_vars);
    }

    protected function faceadmin(){

		global $fb_hook_object, $smcFunc, $txt, $context, $sourcedir, $modSettings;

	    // We need this
	    require_once($sourcedir.'/ManageServer.php');
	    $context['sub_template'] = 'show_settings';

	    $config_vars = array(

			array('check', 'fb_app_enabled'),
		    array('check', 'fb_app_enablecp'),
			array('check', 'fb_app_enablelogauto'),
			'',
			array('check', 'fb_app_enableguest'),
			array('check', 'fb_mode1'),
			array('check', 'fb_mode2'),
			array('check', 'fb_mode3'),
			array('check', 'fb_mode4'),
		    '',
		    array('select', 'fb_dfbreg2', array($fb_hook_object->txt['fb_dfbreg3'],  $fb_hook_object->txt['fb_dfbreg4'], $fb_hook_object->txt['fb_dfbreg5'])),
			array('select', 'fb_reg_auto', array($fb_hook_object->txt['fb_dfbregauto'],  $fb_hook_object->txt['fb_dfbregauto1'])),
			array('int', 'fb_admin_mem_groupe'),
		    '',
		    array('text', 'fb_app_id'),
		    array('text', 'fb_app_secret'),
			array('text', 'fb_admin_intern1'),
		    '',
		    array('large_text', 'fb_admin_invite_text'),
		    array('text', 'fb_log_logo'),
		    array('text', 'fb_log_url'),
	    );

	    if (isset($_GET['save']))
	    {
			saveDBSettings($config_vars);;
		    redirectexit('action=admin;area=facebook');
	    }

	    $context['post_url'] = $fb_hook_object->scripturl .'?action=admin;area=facebook;save';
	    $context['settings_title'] = $fb_hook_object->txt['fb_main1'];
	    prepareDBSettingContext($config_vars);

    }
}
?>