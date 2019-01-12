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

function Twittera(){
    global $txt, $sourcedir, $context;

	require_once($sourcedir . '/ManageServer.php');
	loadTemplate('TwitterAdmin');
    allowedTo('admin_forum');

	$context['page_title'] = $txt['twittmaina'];
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['twittmaina'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['twittmaina'];

	$subActions = array(
	   'twitter' => 'twitadmin',
	   'twittlog' => 'twitlogs',
	   'boards' => 'twit_tweet',
	   'about' => 'twit_about',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'twitter';
	$subActions[$_REQUEST['sa']]();
}

function twit_about(){

	global $settings, $txt, $context;
    $context['sub_template'] = 'twit_about';

	 $context['tw_credits'] = array(
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
	    );
	    $context['tw_thanks'] = array(
		    'Twitter' => array(
			    'name' => 'jmathai',
			    'site' => 'https://github.com/jmathai/twitter-async',
			    'position' => 'twitter-async',
		    ),
		    'Testers' => array(
			    'name' => 'All of you!',
			    'site' => 'http://www.smfhacks.com',
			    'position' => 'Thanks to the SMFHacks.com community who cared about the project and spent time to help me find bugs!',
		    ),
	    );

	    if (function_exists('curl_init')) {
	        $context['sayesno'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_on.png" width="26" height="26" title="" alt=""/> '.$txt['tw_sinfo1'].'';
	    }
	    else{
	        $context['sayesno'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_off.png" width="26" height="26" title="" alt="" /> '.$txt['tw_sinfo2'].'';
	    }

	    if (function_exists('json_decode')) {
	       $context['sayesnojson'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_on.png" width="26" height="26" title="" alt="" /> '.$txt['tw_sinfo3'].'';
	    }
	    else{
	       $context['sayesnojson'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_off.png" width="26" height="26" title="" alt="" /> '.$txt['tw_sinfo4'].'';
	    }

	    if (!defined('PHP_VERSION_ID')) {
           $version = explode('.', PHP_VERSION);
           define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }

        if(PHP_VERSION_ID < 50000){
           $version = explode('.', PHP_VERSION);
           $context['saphpver'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_off.png" width="26" height="26" title="" alt="" /> '.$txt['tw_sinfo5'].' '.phpversion();

	    }
	    else{
	        $context['saphpver'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_on.png" width="26" height="26" title="" alt="" /> '.$txt['tw_sinfo6'].' '.phpversion();

	    }

	    $context['safe_mode'] = ini_get('safe_mode');

	    if($context['safe_mode']){
	        $context['safe_mode_go'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_on.png" width="26" height="26" title="" alt="" /> '.$txt['tw_sinfo7'].'';
	    }
	    else{
	        $context['safe_mode_go'] = '<img src="' . $settings['default_images_url'] . '/admin/switch_off.png" width="26" height="26" title="" alt="" /> '.$txt['tw_sinfo8'].'';
	    }
}
function twit_tweet()
{
global $scripturl, $smcFunc, $context;

	$context['sub_template'] = 'twit_boards';
	$context['settings_title'] = '';
	$context['post_url'] = $scripturl . '?action=admin;area=twitter;sa=boards;save';

	if (isset($_GET['save']))
	{
	checkSession();
	    $board_post = array('boardstweet','boardspub');
	    $board_feild = array('tweet_enable','tweet_pubenable');

		for ($i=0;$i<=1;$i++){
	        $board_ids = array_map('intval', !empty($_POST[$board_post[$i]]) ? $_POST[$board_post[$i]] : array());

			$smcFunc['db_query']('', '
				UPDATE {db_prefix}boards
				SET
				{raw:board_feild} = IF(FIND_IN_SET(id_board, {string:board_ids}), 1, 0)',
				array(
					'board_ids' => join(',', $board_ids),
					'board_feild' => $board_feild[$i],
				)

	        );
		}
	    redirectexit('action=admin;area=twitter;sa=boards');
	}

	$req = $smcFunc['db_query']('', '
			SELECT
				b.id_board, b.id_cat, b.board_order, b.name AS board_name, b.tweet_enable, b.tweet_pubenable,
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

	 $context['twit_boards'] = array();

	 while ($row = $smcFunc['db_fetch_assoc']($req))
	 {
			if (!in_array($row['id_cat'], $cats))
			{
			$cats[] = $row['id_cat'];
			$context['twit_boards'][] = array(
				'is_cat' => true,
				'id_cat' => $row['id_cat'],
				'cat_name' => $row['cat_name'],
			);
		    }
	 $context['twit_boards'][] = $row;
	 }

}

function twitadmin(){
 global $txt, $scripturl, $context;

	$context['sub_template'] = 'show_settings';

	$config_vars = array(
	    array('check', 'tw_app_enabled'),
		'',
		array('check', 'tw_app_enabledauto'),
		'',
		array('check', 'tw_app_enabledanyhere'),
		array('select', 'tw_app_enabledanyheretype', array($txt['tw_app_enabledanyheretype1'],$txt['tw_app_enabledanyheretype2'])),
		'',
		array('text', 'tw_app_id'),
		array('text', 'tw_app_key'),
		array('text', 'tw_app_token'),
		array('text', 'tw_app_tokensecret'),
		array('text', 'tw_app_uname'),
		'',
		array('int', 'tw_admin_mem_groupe'),
		array('text', 'tw_app_log_url'),
		array('text', 'tw_app_log_img'),
		'',
		array('check', 'tw_app_enabledlatetweet'),
		array('int', 'tw_app_tweetcount'),
		'',
		array('select', 'tw_enpub', array($txt['tw_enpub1'],$txt['tw_enpub2'],$txt['tw_enpub3'],$txt['tw_enpub4'])),
		array('check', 'tw_app_shorturl'),
		array('text', 'tw_app_bituname'),
		array('text', 'tw_app_bitkey'),
	);

	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);;
		redirectexit('action=admin;area=twitter');
	}
	$context['post_url'] = $scripturl .'?action=admin;area=twitter;save';
	$context['settings_title'] = $txt['twittmaina'];
	prepareDBSettingContext($config_vars);

}

function twitlogs(){
        global $sourcedir, $smcFunc, $scripturl, $txt, $context;

	    $context['sub_template'] = 'twitlog';
	    $context['settings_title'] = $txt['tw_app_logs'];

	    $list_options = array(
		    'id' => 'twit_list',
		    'title' => $txt['tw_app_logs'],
		    'items_per_page' => 30,
		    'base_href' => $scripturl . '?action=admin;area=twitter;sa=twittlog',
		    'default_sort_col' => 'id_member',
		    'get_items' => array(
			    'function' => create_function('$start, $items_per_page, $sort', '
				    global $smcFunc, $user_info, $txt;

			   $request = $smcFunc[\'db_query\'](\'\', \'
			        SELECT m.id_member,  m.real_name, m.date_registered, mg.online_color, m.twitid, m.twitname
                    FROM {db_prefix}members AS m
                    LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN m.id_group = {int:reg_mem_group}
			        THEN m.id_post_group ELSE m.id_group END)
                    WHERE twitid != {string:one} AND twitid != {string:zero} AND twitname != {string:zero} AND twitname != {string:zero}
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
		            WHERE twitname != {string:one} AND twitid != {string:zero} AND twitname != {string:zero} AND twitname != {string:zero}\',
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
		'no_items_label' => $txt['twittlog9'],
		'columns' => array(
			'id_member' => array(
				'header' => array(
					'value' => $txt['twittlog1'],
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
					'value' => $txt['twittlog4'],
				),
				'data' => array(
					'function' => create_function('$row', '
						return \'@\'.$row[\'twitname\'].\'\';

					'),
					'style' => 'width: 8%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'twitname',
					'reverse' => 'twitname DESC',
				),
			),
			'fbid' => array(
				'header' => array(
					'value' => $txt['twittlog3'],
				),
				'data' => array(
					'function' => create_function('$row', '
						return $row[\'twitid\'];'),
					'style' => 'width: 8%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'twitid',
					'reverse' => 'twitid DESC',
				),
			),

			'time' => array(
				'header' => array(
					'value' => $txt['twittlog2'],
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
					'value' => $txt['twittlog5'],
				),
				'data' => array(
					'function' => create_function('$row', '
						global $context, $txt, $scripturl;

						return \'<a href="https://twitter.com/#!/\'.$row[\'twitname\'].\'" target="blank">\'.$txt[\'twittlog5\'].\'</a>\';
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
			'href' => $scripturl.'?action=admin;area=twitter;sa=twittlog',
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

						<input type="submit" name="dis_sel" value="'.$txt['tw_app_dissel'].'" class="button_submit" />
						<input type="submit" name="dis_all" value="'.$txt['tw_app_disall'].'" class="button_submit" />'
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
		        SET twitname = {string:blank_string}, twitid = {string:blank_string}',
	            array(
			        'blank_string' => '',
	            )
	        );

		    redirectexit('action=admin;area=twitter;sa=twittlog');
        }
	    elseif (!empty($_POST['dis_sel']) && isset($_POST['dis']))
	    {
		    checkSession();

		    $smcFunc['db_query']('', '
	            UPDATE {db_prefix}members
		        SET twitname = {string:blank_string}, twitid = {string:blank_string}
			    WHERE id_member IN ({array_string:dis_actions})',
	            array(
			        'dis_actions' => array_unique($_POST['dis']),
			        'blank_string' => '',
	            )
	        );

		    redirectexit('action=admin;area=twitter;sa=twittlog');
	    }
    }
?>