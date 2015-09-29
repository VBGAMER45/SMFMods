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

function KB_approvecom()
{
	global $scripturl, $sourcedir, $txt, $smcFunc, $context;

	isAllowedTo('manage_kb');
	
	$list_optionscom = array(
		'id' => 'kb_knowcomappr',
		'title' => $txt['kb_approvecom'],
		'items_per_page' => 30,
		'base_href' => $scripturl . '?action=kb;area=manage',
		'default_sort_col' => 'id',
		'start_var_name' => 'startcom',
		'request_vars' => array(
             'desc' => 'desccom',
             'sort' => 'sortcom',
        ),
		'get_items' => array(
			'function' => create_function('$start, $items_per_page, $sort', '
				global $smcFunc;
		
		    $request = $smcFunc[\'db_query\'](\'\', \'
			    SELECT c.id, c.id_article, m.id_member, c.date, m.real_name, c.comment
                FROM {db_prefix}kb_comments AS c
			    LEFT JOIN {db_prefix}members AS m  ON (c.id_member = m.id_member) 
			    WHERE approved = 0
                ORDER BY {raw:sort}
                LIMIT {int:start}, {int:per_page}\',
            array(
			  
			   \'sort\' => $sort,
			   \'start\' => $start,
			   \'per_page\' => $items_per_page,
            )
		 );
		$kbcn1 = array();
			while ($row = $smcFunc[\'db_fetch_assoc\']($request))
				
				   $kbcn1[] = $row;
				   
			$smcFunc[\'db_free_result\']($request);

		return $kbcn1;
			'),
		),
		'get_count' => array(
			'function' => create_function('', '
				global $smcFunc;

				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}kb_comments
					WHERE approved = 0 \',
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
					'value' => $txt['knowledgebaseauthor'],
				),
				'data' => array(
					'function' => create_function('$row', '
                        global $txt, $scripturl;
						if($row[\'id_member\'] != 0){
			              	return \'<a href="\'.$scripturl.\'?action=profile;u=\'.$row[\'id_member\'].\'">\'.$row[\'real_name\'].\'</a>\';
			            }      
			            else{
			              return $txt[\'guest_title\'];
			            }
					
					'),
					'style' => 'width: 4%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'real_name',
					'reverse' => 'real_name DESC',
				),
			),
			'date1' => array(
				'header' => array(
					'value' => $txt['knowledgebasecreated'],
				),
				'data' => array(
					'function' => create_function('$row', '

						return \'<div class="smalltext">\'.timeformat($row[\'date\']).\'</div>\';
					'),
					'style' => 'width: 5%; text-align: center;',
				),
			),
			
			'action' => array(
				'header' => array(
					'value' => '<input type="checkbox" name="all" class="input_check" onclick="invertAll(this, this.form);" />',
				),
				'data' => array(
					'function' => create_function('$row', '
                         global $sc,$scripturl;
						return \'<input type="checkbox" class="input_check" name="approve[]" value="\' . $row[\'id\'] . \'" />\';
					'),
					'style' => 'width: 2%; text-align: center;',
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
						
						<input type="submit" name="approve_com_sel" value="'.$txt['kb_app_aart'].'" class="button_submit" onclick="return confirmSubmit();" />
						<input type="submit" name="approve_com_all" value="'.$txt['kb_app_art'].'" class="button_submit" onclick="return confirmSubmit();" />
						<input type="submit" name="remove" value="'.$txt['kb_remove_log2'].'" class="button_submit" onclick="return confirmSubmit();" />
						<input type="submit" name="removeall2" value="'.$txt['kb_remove_log1'].'" class="button_submit" onclick="return confirmSubmit()" />'
			),
		),
	);
	require_once($sourcedir . '/Subs-List.php');

	createList($list_optionscom);
	if (isset($_POST['removeall2']))
    {
		checkSession();
        
		$query_params = array(
			'table' => 'kb_comments',
			'where' => 'approved = {int:one}',
		);

		$query_data = array('one' => 0,);
		
		KB_DeleteData($query_params,$query_data);
		
		KB_cleanCache();
		redirectexit('action=kb;area=manage');
    }
	if (!empty($_POST['remove']) && isset($_POST['approve']))
	{
		checkSession();
		
		$query_params = array(
			'table' => 'kb_comments',
			'where' => 'id IN ({array_string:delete_actions}) AND approved = {int:one}',
		);

		$query_data = array(
		    'delete_actions' => array_unique($_POST['approve']),
			'one' => 0,
		);
		
		KB_DeleteData($query_params,$query_data);
		
		KB_cleanCache();
		redirectexit('action=kb;area=manage');
	}
	if (isset($_POST['approve_com_all']))
    {
		checkSession();

	    $query_params = array(
		    'table' => 'kb_comments',
	        'set' => 'approved = {int:one}',
	        'where' => '',
	    );

        $query_data = array(
			'one' => 1,
        );
		
	    kb_UpdateData($query_params,$query_data);
	    
		$mes = $txt['kb_app_acom'];
	    KB_log_actions('app_com',0, $mes);
		
		KBrecountcomments();
		KB_cleanCache();
		
		redirectexit('action=kb;area=manage');
    }
	elseif (!empty($_POST['approve_com_sel']) && isset($_POST['approve']))
	{
		checkSession();
		
		$query_params = array(
		    'table' => 'kb_comments',
	        'set' => 'approved = {int:one}',
	        'where' => 'id IN ({array_string:delete_actions})',
	    );

        $query_data = array(
			'delete_actions' => array_unique($_POST['approve']),
			'one' => 1,
        );
		
	    kb_UpdateData($query_params,$query_data);
		
		$mes = $txt['kb_app_com'];
	    KB_log_actions('app_com',0, $mes);
	    KBrecountcomments();
		KB_cleanCache();
		
		redirectexit('action=kb;area=manage');
	}
}

function KB_approve()
{
	global $scripturl, $sourcedir, $txt, $smcFunc, $context;

	$list_options = array(
		'id' => 'kb_know',
		'title' => $txt['kb_alist'],
		'items_per_page' => 30,
		'base_href' => $scripturl . '?action=kb;area=manage',
		'default_sort_col' => 'title',
		'start_var_name' => 'startarticle',
		'request_vars' => array(
             'desc' => 'descarticle',
             'sort' => 'sortarticle',
        ),
		'get_items' => array(
			'function' => create_function('$start, $items_per_page, $sort', '
				global $user_info, $context, $smcFunc;

        	if ($context[\'user\'][\'is_guest\'])
		       $groupid = -1;
	         else
	           $groupid =  $user_info[\'groups\'][0];
			   
		    $request = $smcFunc[\'db_query\'](\'\', \'
			    SELECT k.kbnid, k.title, k.views, k.date, p.view, k.id_cat, k.id_member, m.real_name
                FROM {db_prefix}kb_articles AS k
			    LEFT JOIN {db_prefix}members AS m  ON (k.id_member = m.id_member) 
			    LEFT JOIN {db_prefix}kb_category AS c ON (k.id_cat = c.kbid)
			    LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND k.id_cat = p.id_cat)
			    WHERE approved = 0
                ORDER BY {raw:sort}
                LIMIT {int:start}, {int:per_page}\',
            array(
			   \'groupid\' => $groupid,
			   \'sort\' => $sort,
			   \'start\' => $start,
			   \'per_page\' => $items_per_page,
            )
		 );
		$kbcn = array();
			while ($row = $smcFunc[\'db_fetch_assoc\']($request))
				
				if($row[\'view\'] != \'0\')
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
					FROM {db_prefix}kb_articles
					WHERE approved = 0 \',
			        array());
				
				list ($total_kbn) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_kbn;
			'),
		),
		'no_items_label' => $txt['knowledgebasenone'],
		'columns' => array(
			'title' => array(
				'header' => array(
					'value' => $txt['knowledgebasetitle'],
				),
				'data' => array(
					'function' => create_function('$row', '
					global $scripturl;
						return \'<a href="\'.$scripturl.\'?action=kb;area=article;cont=\'.$row[\'kbnid\'].\'">\'.$row[\'title\'].\'</a>\';
					'),
					'style' => 'width: 20%; text-align: left;',
				),
				'sort' =>  array(
					'default' => 'title',
					'reverse' => 'title DESC',
				),
			),
			'author' => array(
				'header' => array(
					'value' => $txt['knowledgebaseauthor'],
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
			),
			
			'views' => array(
				'header' => array(
					'value' => '<input type="checkbox" name="all" class="input_check" onclick="invertAll(this, this.form);" />',
				),
				'data' => array(
					'function' => create_function('$row', '
                         global $sc,$scripturl;
						return \'<input type="checkbox" class="input_check" name="approve1[]" value="\' . $row[\'kbnid\'] . \'" />\';
					'),
					'style' => 'width: 2%; text-align: center;',
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
						<input type="submit" name="approve_article" value="'.$txt['kb_app_aart'].'" class="button_submit" onclick="return confirmSubmit();" />
						<input type="submit" name="approve_article_all" value="'.$txt['kb_app_art'].'" class="button_submit" onclick="return confirmSubmit();" />
						<input type="submit" name="remove" value="'.$txt['kb_remove_log2'].'" class="button_submit" onclick="return confirmSubmit();" />
						<input type="submit" name="removeall3" value="'.$txt['kb_remove_log1'].'" class="button_submit" onclick="return confirmSubmit();" />'
			),
		),
	);
	
	require_once($sourcedir . '/Subs-List.php');

	createList($list_options);
	if (isset($_POST['removeall3']))
    {
		checkSession();
        
		$query_params = array(
			'table' => 'kb_articles',
			'where' => 'approved = {int:one}',
		);

		$query_data = array('one' => 0,);
		
		KB_DeleteData($query_params,$query_data);
		
		KB_cleanCache();
		redirectexit('action=kb;area=manage');
    }
	if (!empty($_POST['remove']) && isset($_POST['approve1']))
	{
		checkSession();
		
		$query_params = array(
			'table' => 'kb_articles',
			'where' => 'kbnid IN ({array_string:delete_actions}) AND approved = {int:one}',
		);

		$query_data = array(
		    'delete_actions' => array_unique($_POST['approve1']),
			'one' => 0,
		);
		
		KB_DeleteData($query_params,$query_data);
		
		KB_cleanCache();
		redirectexit('action=kb;area=manage');
	}
	if (isset($_POST['approve_article_all']))
    {
		checkSession();

	    $result = $smcFunc['db_query']('','
		    SELECT id_member,title,kbnid
		    FROM {db_prefix}kb_articles
		    WHERE approved = {int:approved}',
		    array( 
			     'approved' => 0,
			)
		);
        $context['kbinfo'] = array();
	    
		while ($row = $smcFunc['db_fetch_assoc']($result)){
	        $context['kbinfo'][] = $row;
	    }
		
	    $smcFunc['db_free_result']($result);
		
		foreach($context['kbinfo'] as $kb){
	
	
		    $kbmes = ''.$txt['kb_aapprove1'].' [url='.$scripturl.'?action=kb;area=article;cont='.$kb['kbnid'].']'.$txt['kb_aapprove2'].'[/url] '.$txt['kb_aapprove3'] .'';
		    KB_sendpm($kb['id_member'],$txt['kb_aapprove6'],$kbmes);
		
		    $mes = ''.$txt['kb_log_text2'].' <strong><a href="'.$scripturl.'?action=kb;area=article;cont='.$kb['kbnid'].'">'.$kb['title'].'</a></strong>';
	        KB_log_actions('app_article',0, $mes);
	    }
		
		$query_params = array(
		    'table' => 'kb_articles',
	        'set' => 'approved = {int:one}',
	        'where' => '',
	    );

        $query_data = array(
			'one' => 1,
        );
		
	    kb_UpdateData($query_params,$query_data);
		
		KBrecountItems();
		KB_cleanCache();
		
		redirectexit('action=kb;area=manage');
    }
	elseif (!empty($_POST['approve_article']) && isset($_POST['approve1']))
	{
		checkSession();
		
		$query_params = array(
		    'table' => 'kb_articles',
	        'set' => 'approved = {int:one}',
	        'where' => 'kbnid IN ({array_string:delete_actions})',
	    );

        $query_data = array(
			'one' => 1,
			'delete_actions' => array_unique($_POST['approve1']),
        );
		
	    kb_UpdateData($query_params,$query_data);
		
		$result = $smcFunc['db_query']('','
		    SELECT id_member,title,kbnid
		    FROM {db_prefix}kb_articles
		    WHERE kbnid IN ({array_string:delete_actions})',
		    array( 
			    'delete_actions' => array_unique($_POST['approve1']),
			)
		);
        $context['kbinfo'] = array();
	    
		while ($row = $smcFunc['db_fetch_assoc']($result)){
	        $context['kbinfo'][] = $row;
	    }
		
	    $smcFunc['db_free_result']($result);
		
	    foreach($context['kbinfo'] as $kb){
	
	
		    $kbmes = ''.$txt['kb_aapprove1'].' [url='.$scripturl.'?action=kb;area=article;cont='.$kb['kbnid'].']'.$txt['kb_aapprove2'].'[/url] '.$txt['kb_aapprove3'] .'';
		    KB_sendpm($kb['id_member'],$txt['kb_aapprove6'],$kbmes);
		
		    $mes = ''.$txt['kb_log_text2'].' <strong><a href="'.$scripturl.'?action=kb;area=article;cont='.$kb['kbnid'].'">'.$kb['title'].'</a></strong>';
	        KB_log_actions('app_article',0, $mes);
	    }
		KBrecountItems();
		KB_cleanCache();
		
	    redirectexit('action=kb;area=manage');
	}
}
?>	