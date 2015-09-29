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

function gplusa(){
    global $txt, $sourcedir, $context;

	require_once($sourcedir . '/ManageServer.php');
    allowedTo('admin_forum');
    loadTemplate('GPlus');
	
	$context['page_title'] = $txt['gp_googplus'];
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['gp_googplus'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['gp_googplus'];
	
	$subActions = array(
	   'gplus' => 'gplus_admin',
	   'gplus_logs' => 'gplus_logs',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'gplus';
	$subActions[$_REQUEST['sa']]();	
}


function gplus_admin(){
 global $txt, $scripturl, $context;

	$context['sub_template'] = 'show_settings';

	$config_vars = array(
	    array('check', 'gp_app_enabled'),
		array('check', 'gp_app_enabledautolog'),
		'',
		array('text', 'gp_app_client_id'),
		array('text', 'gp_app_client_secret'),
		'',
		array('text', 'gp_app_custon_logurl'),
		array('text', 'gp_app_custon_logimg'),
		array('int', 'gp_app_detait_gid'),
		array('select', 'gp_reg_auto', array($txt['gp_dfbregauto'],  $txt['gp_dfbregauto1'])),
		'',
		array('text', 'gp_app_board_showplus1'),	
		
	);	
	
	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);;
		redirectexit('action=admin;area=gplus');
	}
	$context['post_url'] = $scripturl .'?action=admin;area=gplus;save';
	$context['settings_title'] = $txt['gp_googplus'];
	prepareDBSettingContext($config_vars);		
}

function gplus_logs(){
    global $sourcedir, $smcFunc, $scripturl, $txt, $context;
    
	    $context['sub_template'] = 'gplus_log';
	    $context['settings_title'] = $txt['gp_app_logs'] ;
	
	    $list_options = array(
		    'id' => 'gp_list',
		    'title' => $txt['gp_app_logs'] ,
		    'items_per_page' => 30,
		    'base_href' => $scripturl . '?action=admin;area=gplus;sa=gplus_logs',
		    'default_sort_col' => 'id_member',
		    'get_items' => array(
			    'function' => create_function('$start, $items_per_page, $sort', '
				    global $smcFunc, $user_info, $txt;

			    $request = $smcFunc[\'db_query\'](\'\', \'
			        SELECT m.id_member,  m.real_name, m.date_registered, mg.online_color, m.gpid, m.gpname
                    FROM {db_prefix}members AS m
                    LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN m.id_group = {int:reg_mem_group} 
			        THEN m.id_post_group ELSE m.id_group END)
                    WHERE gpid != {string:one} AND gpid != {string:zero} AND gpname != {string:zero} AND gpname != {string:zero}
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
		            WHERE gpid != {string:one} AND gpid != {string:zero} AND gpname != {string:zero} AND gpname != {string:zero}\',
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
		'no_items_label' => $txt['gp_app_logsnon'],
		'columns' => array(
			'id_member' => array(
				'header' => array(
					'value' => $txt['gp_app_logs1'],
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
			'gpname' => array(
				'header' => array(
					'value' => $txt['gp_app_logs2'] ,
				),
				'data' => array(
					'function' => create_function('$row', '
						return $row[\'gpname\'];'),
					'style' => 'width: 8%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'gpname',
					'reverse' => 'gpname DESC',
				),
			),
			'gpid' => array(
				'header' => array(
					'value' => $txt['gp_app_logs3'],
				),
				'data' => array(
					'function' => create_function('$row', '
						return $row[\'gpid\'];'),
					'style' => 'width: 8%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'gpid',
					'reverse' => 'gpid DESC',
				),
			),
			
			'time' => array(
				'header' => array(
					'value' => $txt['gp_app_logs4'],
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
					'value' => $txt['gp_app_logs5'],
				),
				'data' => array(
					'function' => create_function('$row', '
						global $context, $txt, $scripturl;

						return \'<a href="https://plus.google.com/\'.$row[\'gpid\'].\'" target="blank">\'.$txt[\'gp_app_logs5\'].\'</a>\';
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
			'href' => $scripturl.'?action=admin;area=gplus;sa=gplus_logs',
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
						
						<input type="submit" name="dis_sel" value="'.$txt['gp_app_logs6'].'" class="button_submit" />
						<input type="submit" name="dis_all" value="'.$txt['gp_app_logs7'].'" class="button_submit" />'
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
		        SET gpname = {string:blank_string}, gpid = {string:blank_string}',
	            array(
			        'blank_string' => '',
	            )
	        );
		
		    redirectexit('action=admin;area=gplus;sa=gplus_logs');
        }
	    elseif (!empty($_POST['dis_sel']) && isset($_POST['dis']))
	    {
		    checkSession();
		
		    $smcFunc['db_query']('', '
	            UPDATE {db_prefix}members
		        SET gpname = {string:blank_string}, gpid = {string:blank_string}
			    WHERE id_member IN ({array_string:dis_actions})',
	            array(
			        'dis_actions' => array_unique($_POST['dis']),
			        'blank_string' => '',
	            )
	        );
	
		    redirectexit('action=admin;area=gplus;sa=gplus_logs');
	    }
}
?>	