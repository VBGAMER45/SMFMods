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

function KB_reporta(){
 global $smcFunc, $scripturl, $user_info, $txt, $kbname, $context;
    
	$context['sub_template']  = 'kb_reporta';
	
	isAllowedTo('rparticle_kb');
	
	$request = $smcFunc['db_query']('', '
		SELECT title
		FROM {db_prefix}kb_articles		    
		WHERE kbnid = {int:aid}',
	    array(
			'aid' => (int) $_REQUEST['aid'],
	    )	
    );		
	list ($kbname) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
	
	$context['linktree'][] = array(
	   'url' => $scripturl . '?action=kb;area=reporta;aid='.$_GET['aid'].'',
	   'name' => $txt['kb_reports22'].' - '.$kbname,
	);
	
	if(isset($_REQUEST['save'])){
	
	    if(empty($_POST['description']))
		    fatal_error($txt['kb_pls_enter_com'],false);
		
        if(empty($_GET['aid']))
		    fatal_error($txt['kb_ratenosel'],false);
			
		$_POST['description'] = $smcFunc['htmlspecialchars']($_POST['description'], ENT_QUOTES);
	    $_GET['aid']= (int) $_GET['aid'];
		
		$mes = ''.$txt['kb_log_text13'].'  <strong><a href="'.$scripturl.'?action=kb;area=article;cont='.$_GET['aid'].'">'. $kbname.'</a></strong>';
	  	KB_log_actions('add_report',$_GET['aid'], $mes);
		
		$data = array(
		    'table' => 'kb_reports',
		    'cols' => array('id_article' => 'int','id_member' => 'int','comment' => 'string','date' => 'int'),
	    );
	
	    $values = array($_GET['aid'],$user_info['id'],$_POST['description'],time());
		
	    $indexes = array();
		
		KB_InsertData($data, $values, $indexes);
		KB_cleanCache();
		redirectexit('action=kb;area=article;cont='.$_GET['aid'].';reported');
	}
}

function KB_mreports(){
global $scripturl, $sourcedir, $txt, $smcFunc, $context;
    
	isAllowedTo('manage_kb');
	
	$list_options = array(
		'id' => 'kb_know_reports',
		'title' => $txt['kb_rlist1'],
		'items_per_page' => 30,
		'base_href' => $scripturl . '?action=kb;area=manage',
		'default_sort_col' => 'id',
		'start_var_name' => 'startreport',
		'request_vars' => array(
             'desc' => 'descreport',
             'sort' => 'sortreport',
        ),
		'get_items' => array(
			'function' => create_function('$start, $items_per_page, $sort', '
				global $context, $smcFunc;
			   
		    $request = $smcFunc[\'db_query\'](\'\', \'
			    SELECT k.id, k.id_article, k.id_member, k.comment, m.id_member, m.real_name, k.date
                FROM {db_prefix}kb_reports AS k
				LEFT JOIN {db_prefix}members AS m ON  (m.id_member = k.id_member)
                ORDER BY {raw:sort}
                LIMIT {int:start}, {int:per_page}\',
            array(
			   
			   \'sort\' => $sort,
			   \'start\' => $start,
			   \'per_page\' => $items_per_page,
            )
		 );
		$kbcn = array();
			while ($row = $smcFunc[\'db_fetch_assoc\']($request))
				
				   $kbcn[] = $row;
				   
			$smcFunc[\'db_free_result\']($request);

		return $kbcn;
			'),
		),
		'get_count' => array(
			'function' => create_function('', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}kb_reports\',
			        array());
				
				list ($total_kbn) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_kbn;
			'),
		),
		'no_items_label' => $txt['knowledgebasenone'],
		'columns' => array(
			'id' => array(
				'header' => array(
					'value' => $txt['kb_rlistcomment'],
				),
				'data' => array(
					'function' => create_function('$row', '
					global $scripturl;
						return \'\'.$row[\'comment\'].\'\';
					'),
					'style' => 'width: 20%; text-align: left;',
				),
				'sort' =>  array(
					'default' => 'id',
					'reverse' => 'id DESC',
				),
			),
			'real_name' => array(
				'header' => array(
					'value' => $txt['kb_rlistnor1'],
				),
				'data' => array(
					'function' => create_function('$row', '
                        global $scripturl;
						return \'<a href="\'.$scripturl.\'?action=profile;u=\'.$row[\'id_member\'].\'">\'.$row[\'real_name\'].\'</a>\';
					'),
					'style' => 'width: 4%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'real_name',
					'reverse' => 'real_name DESC',
				),
			),
			'date' => array(
				'header' => array(
					'value' => $txt['knowledgebasecreated'],
				),
				'data' => array(
					'function' => create_function('$row', '

						return timeformat($row[\'date\']);
					'),
					'style' => 'width: 5%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'date',
					'reverse' => 'date DESC',
				),
			),
			
			'id_article' => array(
				'header' => array(
					'value' => '<input type="checkbox" name="all" class="input_check" onclick="invertAll(this, this.form);" />',
				),
				'data' => array(
					'function' => create_function('$row', '
                         global $sc, $txt, $scripturl;
						return \'[<a href="\'.$scripturl.\'?action=kb;area=article;cont=\'.$row[\'id_article\'].\'">\'.$txt[\'kb_rlistnor44\'].\'</a>] 
						<input type="checkbox" class="input_check" name="delete[]" value="\' . $row[\'id\'] . \'" />\';
					'),
					'style' => 'width: 2%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'id_article',
					'reverse' => 'id_article DESC',
				),
	    	),		
		),
		'form' => array(
			'href' => $scripturl.'?action=kb;area=manage',
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
						<input type="submit" name="remove" value="'.$txt['kb_remove_log2'].'" class="button_submit" onclick="return confirmSubmit();" />
						<input type="submit" name="removeall" value="'.$txt['kb_remove_log1'].'" class="button_submit" onclick="return confirmSubmit();" />'
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');

	createList($list_options);
	
	if (isset($_POST['removeall']))
    {
		checkSession();
        
		$query_params = array(
			'table' => 'kb_reports',
			'where' => '',
		);

		$query_data = array();
		
		KB_DeleteData($query_params,$query_data);
		
		$mes = $txt['kb_del_areports'];
	  	KB_log_actions('del_report',0, $mes);
		KB_cleanCache();
		redirectexit('action=kb;area=manage');
    }
	elseif (!empty($_POST['remove']) && isset($_POST['delete']))
	{
		checkSession();
		
		$query_params = array(
			'table' => 'kb_reports',
			'where' => 'id IN ({array_string:delete_actions})',
		);

		$query_data = array(
		    'delete_actions' => array_unique($_POST['delete']),
		);
		
		KB_DeleteData($query_params,$query_data);
		
		$mes = $txt['kb_del_reports'];
	  	KB_log_actions('del_report',0, $mes);
		KB_cleanCache();
		redirectexit('action=kb;area=manage');
	}
}
?>	