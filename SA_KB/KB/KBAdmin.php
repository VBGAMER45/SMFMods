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

function KBa(){
    global $txt, $context;

	loadTemplate('KBAdmin');
    allowedTo('admin_forum');
	
    $context['page_title'] = $txt['knowledgebase'];
	
	KB_file_include('KBSubs');
	
	$context[$context['admin_menu_name']]['tab_data']['title'] = $txt['knowledgebase'];
	$context[$context['admin_menu_name']]['tab_data']['description'] = $txt['knowledgebase'];
	
	$subActions = array(
	   'kb' => 'KBadmin',
	   'kbstand' => 'KB_standalone',
	   'kbactionlog' => 'KB_actionlog',
	   'import' => 'KB_Import',
	   'showlog' => 'KB_show_logs',
	   'importsmfa' => 'KB_ImportSMFarticle',
	   'importtpa' => 'KB_ImportTParticle',
	   'importfaq' => 'KB_Importfaq',
	   'attach' => 'KBAttachment',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'kb';
	$subActions[$_REQUEST['sa']]();	
}

function KB_show_logs(){
  global $txt, $sourcedir, $modSettings, $smcFunc, $scripturl, $context;
  
    $context['sub_template']  = 'kbdolog';
  
    $context['can_delete'] = allowedTo('admin_forum');
    $context['hoursdisable'] = $modSettings['kb_del_wait'];
    $context['waittime'] = time() - $context['hoursdisable'] * 3600;
    $logperpage = !empty($modSettings['kb_log_perpage']) ? $modSettings['kb_log_perpage'] : 30;
    
	if (isset($_POST['removeall']) && $context['can_delete'])
    {
		checkSession();

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}kb_log_actions
			WHERE time < {int:twenty_four_hours_wait}',
			array(
				'twenty_four_hours_wait' => $context['waittime'],
			)
		);
		KB_cleanCache();
    }
	elseif (!empty($_POST['remove']) && isset($_POST['delete']) && $context['can_delete'])
	{
		checkSession();
		
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}kb_log_actions
			WHERE id_log IN ({array_string:delete_actions}) AND time < {int:twenty_four_hours_wait}',
			array(
				'delete_actions' => array_unique($_POST['delete']),
				'twenty_four_hours_wait' => $context['waittime'],
				
			)
		);
		KB_cleanCache();
	}
	
    $list_options = array(
		'id' => 'kb_list',
		'title' => $txt['kb_log_admin1'],
		'items_per_page' => $logperpage,
		'base_href' => $scripturl . '?action=admin;area=kb;sa=showlog',
		'default_sort_col' => 'id_log',
		'default_sort_dir' => 'desc',
		'get_items' => array(
			'function' => create_function('$start, $items_per_page, $sort', '
				global $context, $modSettings, $scripturl, $user_info, $smcFunc;
		
		
		$request = $smcFunc[\'db_query\'](\'\', \'
			SELECT a.id_log, a.user_id, a.reason, a.time, a.user_ip, m.real_name, mg.group_name
            FROM {db_prefix}kb_log_actions AS a
			LEFT JOIN {db_prefix}members AS m ON (a.user_id = m.id_member)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN m.id_group = {int:reg_group_id} THEN m.id_post_group ELSE m.id_group END)
             ORDER BY {raw:sort}
			LIMIT {int:start}, {int:per_page}\',
            array(
			  \'reg_group_id\' => 0,
			  \'sort\' => $sort,
			  \'start\' => $start,
			  \'per_page\' => $items_per_page,
            )
		);
		$context[\'knowact\'] = array();
		while ($row = $smcFunc[\'db_fetch_assoc\']($request))
				
				 $context[\'knowact\'][] = $row;
				  
		$smcFunc[\'db_free_result\']($request);
		return $context[\'knowact\'];
		
			'),
		),
		'get_count' => array(
			'function' => create_function('', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}kb_log_actions\',
			       array());
				   
				list ($total_kb) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_kb;
			'),
		),
		'no_items_label' => $txt['knowledgebasenone'],
		'columns' => array(
			'id_log' => array(
				'header' => array(
					'value' => $txt['knowledgebase_actions'],
				),
				'data' => array(
					'function' => create_function('$row', '
					
					    return \'<div class="smalltext">\'.$row[\'reason\'].\'</div>\';
				    				
					'),
					'style' => 'width: 10%; text-align: left;',
				),
				'sort' =>  array(
					'default' => 'id_log',
					'reverse' => 'id_log DESC',
				),
			),	
			'time' => array(
				'header' => array(
					'value' => $txt['kb_log_admin2'],
				),
				'data' => array(
					'function' => create_function('$row', '
					
					    return \'\'.timeformat($row[\'time\']).\'\';
				    				
					'),
					'style' => 'width: 5%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'time',
					'reverse' => 'time DESC',
				),
			),	
			'user_id' => array(
				'header' => array(
					'value' => $txt['kb_log_admin3'],
				),
				'data' => array(
					'function' => create_function('$row', '
					global $txt, $scripturl;
					   if($row[\'user_id\'] != 0){
			              	return \'<a href="\'.$scripturl.\'?action=profile;u=\'.$row[\'user_id\'].\'">\'.$row[\'real_name\'].\'</a>\';
			            }      
			            else{
			              return $txt[\'guest_title\'];
			            }
				    				
					'),
					'style' => 'width: 2%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'user_id',
					'reverse' => 'user_id DESC',
				),
			),	
			'group_name' => array(
				'header' => array(
					'value' => $txt['kb_log_admin4'],
				),
				'data' => array(
					'function' => create_function('$row', '
					
					    if($row[\'group_name\']){
			              	return \'\'.$row[\'group_name\'].\'\';
			            }      
			            else{
			              return \'N/A\';
			            }
				    				
					'),
					'style' => 'width: 2%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'group_name',
					'reverse' => 'group_name DESC',
				),
			),	
			'user_ip' => array(
				'header' => array(
					'value' => $txt['kb_log_admin5'],
				),
				'data' => array(
					'function' => create_function('$row', '
					
					    return \'\'.$row[\'user_ip\'].\'\';
				    				
					'),
					'style' => 'width: 2%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'user_ip',
					'reverse' => 'user_ip DESC',
				),
			),	
			'delete' => array(
				'header' => array(
					'value' => '<input type="checkbox" name="all" class="input_check" onclick="invertAll(this, this.form);" />',
				),
				'data' => array(
					'function' => create_function('$row', '
						return \'<input type="checkbox" class="input_check" name="delete[]" value="\' . $row[\'id_log\'] . \'" />\';
					'),
					'style' => 'width: 2%; text-align: center;',
				),
			),
		),
		'form' => array(
			'href' => $scripturl.'?action=admin;area=kb;sa=showlog',
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
						' . ($context['can_delete'] ? '
						<input type="submit" name="remove" value="'.$txt['kb_remove_log2'].'" class="button_submit" />
						<input type="submit" name="removeall" value="'.$txt['kb_remove_log1'].'" class="button_submit" />' : ''),
			),
		),
	);
	require_once($sourcedir . '/Subs-List.php');

	createList($list_options);	
}

function KB_Import()
{
	global $txt, $scripturl, $context;

	$context['sub_template'] = 'kbimport';
}

function KB_Importfaq()
{
	global $smcFunc, $context, $txt, $db_prefix;

	$context['import_results'] = '';
	$context['sub_template']  = 'kbimportfaq';
		
	$dbresult = $smcFunc['db_query']('', "
		SELECT 
		c.kbid, c.name
		FROM {db_prefix}kb_category AS c 
		ORDER BY c.name ASC");

	$context['kb_cat'] = array();	
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['kb_cat'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
		

	if (isset($_REQUEST['doimport']))
	{
	    checkSession();
		$cat = (int) $_REQUEST['catid'];
		
		db_extend();
		$tp_articles_tables = $smcFunc['db_list_tables'](false, $db_prefix . 'faq');
	
	    if (empty($tp_articles_tables))
		    fatal_lang_error('kb_importtp3',false);
			
		if (empty($cat))
			fatal_lang_error('kb_importtp1');
		
		$result = $smcFunc['db_query']('', '
			SELECT id, title, body, category_id, last_user, timestamp
		    FROM {db_prefix}faq',
		    array()
		);
		
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
		    $smcFunc['db_insert']('',
				'{db_prefix}kb_articles',
				array('id_member' => 'int','title' => 'string','content' => 'string','views' => 'int','approved' => 'int','date' => 'string'),
				array($row['last_user'],$row['title'],$row['body'],0,1,time()),
				array()
		    );
		}
		$smcFunc['db_free_result']($result);
		KB_cleanCache();
		$result = $smcFunc['db_query']('', '
		    SELECT 
			k.kbnid, k.title 
		    FROM {db_prefix}kb_articles AS k, {db_prefix}faq AS f
		    WHERE f.last_user = k.id_member AND f.title = k.title',
		    array()
		);
		
		$context['import_results'] = '<strong>'.$txt['kb_import1'].'</strong><br />';
		
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$context['import_results'] .= $row['title'] . '<br />';
		
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}kb_articles
				SET
				id_cat = {int:cat}
				WHERE kbnid = {int:kbid}',
			    array(
				    'kbid' => (int) $row['kbnid'],
					'cat' => $cat,
				)
			);			
		}
		$smcFunc['db_free_result']($result);	
    }
}

function KB_ImportTParticle()
{
	global $smcFunc, $context, $txt, $db_prefix;

	$context['import_results'] = '';
	$context['sub_template']  = 'kbimporttp';
		
	$dbresult = $smcFunc['db_query']('', "
		SELECT 
			c.kbid, c.name
		FROM {db_prefix}kb_category AS c 
		ORDER BY c.name ASC");

	$context['kb_cat'] = array();	
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['kb_cat'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
		

	if (isset($_REQUEST['doimport']))
	{
	    checkSession();
		$cat = (int) $_REQUEST['catid'];
		
		db_extend();
		$tp_articles_tables = $smcFunc['db_list_tables'](false, $db_prefix . 'tp_articles');
	
	    if (empty($tp_articles_tables))
		    fatal_lang_error('kb_importtp3',false);
			
		if (empty($cat))
			fatal_lang_error('kb_importtp1');
		
		$result = $smcFunc['db_query']('', '
		    SELECT 
			author_id, subject, body, views, approved, date
		    FROM {db_prefix}tp_articles',
		    array(
		    )
		);
		
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
		    $smcFunc['db_insert']('',
				'{db_prefix}kb_articles',
				array('id_member' => 'int','title' => 'string','content' => 'string','views' => 'int','approved' => 'int','date' => 'int'),
				array($row['author_id'],$row['subject'],$row['body'],$row['views'],$row['approved'],$row['date']),
				array()
		    );
		}
		$smcFunc['db_free_result']($result);
		KB_cleanCache();
		$result = $smcFunc['db_query']('', '
		    SELECT 
			k.kbnid, k.title 
		    FROM {db_prefix}kb_articles AS k, {db_prefix}tp_articles AS a
		    WHERE a.author_id = k.id_member AND k.date = a.date AND a.subject = k.title',
		    array(
		    )
		);
		
		$context['import_results'] = '<strong>'.$txt['kb_import1'].'</strong><br />';
		
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$context['import_results'] .= $row['title'] . '<br />';
		
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}kb_articles
				SET
				id_cat = {int:cat}
				WHERE kbnid = {int:kbid}',
			    array(
				    'kbid' => (int) $row['kbnid'],
					'cat' => $cat,
				)
			);			
		}
		$smcFunc['db_free_result']($result);	
    }
}

function KB_ImportSMFarticle()
{
	global $smcFunc, $context, $txt, $db_prefix;

	$context['import_results'] = '';
	$context['sub_template']  = 'kbimportasmfa';
	
	$dbresult = $smcFunc['db_query']('', "
	    SELECT 
			c.kbid, c.name
		FROM {db_prefix}kb_category AS c 
		ORDER BY c.name ASC");

	$context['kb_cat'] = array();	
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['kb_cat'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);
		

	if (isset($_REQUEST['doimport']))
	{
	    checkSession();
		$cat = (int) $_REQUEST['catid'];
		
		db_extend();
		$articles_tables = $smcFunc['db_list_tables'](false, $db_prefix . 'articles');
	
	    if (empty($articles_tables))
		    fatal_lang_error('kb_importytp4',false);
		
		if (empty($cat))
			fatal_lang_error('kb_importtp1');
		
		$result = $smcFunc['db_query']('', '
		    SELECT 
			a.ID_MEMBER, a.title, p.pagetext, a.views, a.approved, a.date
		    FROM {db_prefix}articles AS a
			LEFT JOIN {db_prefix}articles_page AS p ON (a.ID_ARTICLE = p.ID_ARTICLE)',
		    array(
		    )
		);
		
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
		    $smcFunc['db_insert']('',
				'{db_prefix}kb_articles',
				array('id_member' => 'int','title' => 'string','content' => 'string','views' => 'int','approved' => 'int','date' => 'int'),
				array($row['ID_MEMBER'],$row['title'],$row['pagetext'],$row['views'],$row['approved'],$row['date']),
				array()
		    );
		}
		$smcFunc['db_free_result']($result);
		KB_cleanCache();
		$result = $smcFunc['db_query']('', '
		    SELECT 
			k.kbnid, k.title 
		    FROM {db_prefix}kb_articles AS k, {db_prefix}articles AS a
		    WHERE a.ID_MEMBER = k.id_member AND k.date = a.date AND a.title = k.title',
		    array(
		    )
		);
		
		$context['import_results'] = '<strong>'.$txt['kb_import1'].'</strong><br />';
		
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$context['import_results'] .= $row['title'] . '<br />';
		
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}kb_articles
				SET
				id_cat = {int:cat}
				WHERE kbnid = {int:kbid}',
			    array(
				    'kbid' => (int) $row['kbnid'],
					'cat' => $cat,
				)
			);			
		}
		$smcFunc['db_free_result']($result);	
	}
}

function KB_actionlog(){
  global $txt, $scripturl, $modSettings, $context, $sourcedir;
	
	require_once($sourcedir.'/ManageServer.php');
	$context['sub_template'] = 'show_settings';
	
	$config_vars = array(
	    array('check', 'kb_disable_log'),
		array('int', 'kb_log_perpage'),
		array('int', 'kb_del_wait'),
		'',
		array('check', 'kb_add_article'),
		array('check', 'kb_del_article'),
		array('check', 'kb_edit_article'),
		array('check', 'kb_app_article'),
		array('check', 'kb_unapp_article'),
		'',
		array('check', 'kb_add_cat'),
		array('check', 'kb_edit_cat'),
		array('check', 'kb_perm_cat'),
		array('check', 'kb_del_cat'),
		'',
		array('check', 'kb_app_com'),
		array('check', 'kb_add_com'),
		array('check', 'kb_del_com'),
		'',
		array('check', 'kb_add_report'),
		array('check', 'kb_del_report'),
		
	);	
	
	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);
		KB_cleanCache();
		redirectexit('action=admin;area=kb;sa=kbactionlog');
	}
	
	$context['post_url'] = $scripturl .'?action=admin;area=kb;sa=kbactionlog;save';
	$context['settings_title'] = $txt['kb_log_admin'];
	prepareDBSettingContext($config_vars);		

}

function KBadmin(){
 global $txt, $scripturl, $context, $sourcedir;
	
	require_once($sourcedir.'/ManageServer.php');
	
	$context['sub_template'] = 'show_settings';
    
	$config_vars = array(
	    array('check', 'kb_enabled'),
	    '',
	    array('check', 'kb_search_engines'),
		'',
	    array('check', 'kb_countsub'),
	    '',
		array('check', 'kb_privmode'),
		array('check', 'kb_privmes'),
	    '',
	    array('select', 'kb_menutype', array($txt['kb_dropdown'], $txt['kb_sidebar'], $txt['kb_memchoice'])),
	    '',
	    array('check', 'kb_eratings'),
	    array('check', 'kb_ecom'),
	    array('check', 'kb_esearch'),
		array('check', 'kb_efeaturedarticle'),
		array('check', 'kb_enablersscat'),
	    '',
	    array('check', 'kb_social'),
	    array('check', 'kb_spinfo'),
	    array('check', 'kb_salegend'),
		array('check', 'kb_show_view'),
	    array('check', 'kb_quiksearchindex'),
	    '',
	    array('int', 'kb_app'),
	    array('int', 'kb_cpp'),
		'',
	    array('check', 'kb_enablehs_img'),
		//array('check', 'kb_parse_wiki'),
	);	
	
	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);
		KB_cleanCache();
		redirectexit('action=admin;area=kb');
	}
	
	$context['post_url'] = $scripturl .'?action=admin;area=kb;save';
	$context['settings_title'] = $txt['kbase_config'];
	prepareDBSettingContext($config_vars);		

}

function KBAttachment()
{
	global $txt, $scripturl, $context, $sourcedir;

	require_once($sourcedir.'/ManageServer.php');

	$context['sub_template'] = 'show_settings';
	
	$config_vars = array(
		array('check', 'kb_enable_attachment'),
		'',
		array('check', 'kb_attachmentExtensions'),
		array('text', 'kb_attachmentCheckExtensions', 40),
		'',
		array('text', 'kb_attachmentDirSizeLimit', 6, 'postinput' => $txt['kilobyte']),
		array('text', 'kb_mfile_attachment', 6, 'postinput' => $txt['kilobyte']),
		array('text', 'kb_num_attachment', 6),
		'',
		array('text', 'kb_url_attachment',40),
		array('text', 'kb_path_attachment',40),	
		'',
		array('text', 'kb_img_max_height',6),	
		array('text', 'kb_img_max_width',6),
		'',
		array('check', 'kb_enablehs_attach'),
		
	);	
	
	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);
		KB_cleanCache();
		redirectexit('action=admin;area=kb;sa=attach');
	}
	
	$context['post_url'] = $scripturl .'?action=admin;area=kb;sa=attach;save';
	$context['settings_title'] = $txt['kb_attach7'];
	prepareDBSettingContext($config_vars);		

}

function KB_standalone()
{
	global $context, $sourcedir, $scripturl, $modSettings, $txt;
	
	require_once($sourcedir.'/ManageServer.php');
	$context['sub_template'] = 'show_settings';
    $value = false;
    $disabled = !empty($modSettings['kb_knowledge_only']) ? false : true;
	
	$config_vars = array(
	    array('check', 'kb_knowledge_only', 'subtext' => $txt['kb_knowledge_only_note']),
	);
    $disabled_options = array(
	    'kb_disable_pm',
		'kb_disable_mlist',
	);

	foreach ($disabled_options as $name)
	{
		$value = !isset($modSettings[$name]) ? $value : $modSettings[$name];
		$config_vars[] = array('check', $name, 'value' => $value, 'disabled' => $disabled);
	}
	
	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);
		KB_cleanCache();
		redirectexit('action=admin;area=kb;sa=kbstand');
	}
	$context['post_url'] = $scripturl .'?action=admin;area=kb;sa=kbstand;save';
	$context['settings_title'] = $txt['kbase_sto'];
	prepareDBSettingContext($config_vars);		
}
?>	