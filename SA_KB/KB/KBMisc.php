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

function KB_manage(){
   global $total_report, $total_approvecom, $total_approve, $txt, $scripturl, $context;
   
    $context['sub_template'] = 'kbmanage';
    $context['kb_do'] = $txt['kb_manage1'];
    
	isAllowedTo('manage_kb');
	
    $context['linktree'][] = array(
		'url' => $scripturl . '?action=kb;area=manage',
		'name' => $txt['kb_manage1']
	);

	KB_approvecounts();
	KB_approvecomcounts();
	KB_reportcounts();
	KB_mreports();
	KB_approvecom();
	KB_approve();
}

function KB_movecat(){
    global $smcFunc, $txt;
	
	isAllowedTo('manage_kb');

	$cat = (int) $_REQUEST['cat'];
	
	KB_ReOrderCats($cat);

	$result1 = $smcFunc['db_query']('', '
	    SELECT roword 
	    FROM {db_prefix}kb_category 
	    WHERE kbid = {int:cat}',
		array(
		    'cat' => $cat,
		)
	);
	$row = $smcFunc['db_fetch_assoc']($result1);
	$oldorder = $row['roword'];
	$order = $row['roword'];
	
	if ($_GET['area'] == 'catup')
		$order--;
	else 
		$order++;
		
	$smcFunc['db_free_result']($result1);
	
	$dbresult = $smcFunc['db_query']('', '
	    SELECT 
		kbid, roword 
	    FROM {db_prefix}kb_category  
	    WHERE roword = {int:ord}',
		array(
		    'ord' => $order,
		)
	);
	
	if ($smcFunc['db_affected_rows']()== 0)
	{
		switch ($_GET['area']) {
            
			case 'catup':
                fatal_lang_error('kb_no_cat_above',false);
			break;
			
            case 'catdown':
                fatal_lang_error('kb_no_cat_below',false);
			break;
        }	
	}
		
	$row2 = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	$query_params = array(
		'table' => 'kb_category',
	    'set' => 'roword = {string:oldroword}',
	    'where' => 'kbid = {int:kbid}',
	);

    $query_data = array(
	    'kbid' => $row2['kbid'],
		'oldroword' => $oldorder,
    );
	
	$query_params1 = array(
		'table' => 'kb_category',
	    'set' => 'roword = {string:roword}',
	    'where' => 'kbid = {int:kbid}',
	);

    $query_data1 = array(
	    'kbid' => $cat,
		'roword' => $order,
    );
		
	kb_UpdateData($query_params,$query_data);
	kb_UpdateData($query_params1,$query_data1);
	KB_cleanCache();    
	redirectexit('action=kb;area=listcat');
}	

function KB_dojprint(){
    global $context, $scripturl, $txt;
	
    if(isset($_GET['print']))
	{
		if(!isset($_GET['cont']))
			redirectexit();
						
		$doit = '<h2>' . html_entity_decode($context['know'][0]['title'], ENT_QUOTES, $context['character_set']) . ' </h2>'.html_entity_decode($context['know'][0]['content'], ENT_QUOTES, $context['character_set']);
		
		$context['kbprintbody'] = $doit;
						
		$context['kbprint'] = '<a href="' .$scripturl . '?action=kb;area=article;cont='. $_GET['cont'] . '"><strong>'.$txt['kb_print1'].'</strong></a>';

		$context['template_layers'] = array('kb_print');
		$context['sub_template'] = 'kb_print_body';	
	}
}

function KB_rate(){
   global $txt, $smcFunc, $user_info;;
 
    $id = !empty($_REQUEST['kbnid']) ? (int) $_REQUEST['kbnid'] : 0;
	
	if (empty($id))
		fatal_lang_error('kb_ratenosel',false);

	isAllowedTo('rate_kb'); 
	checkSession('get');
	
	$dbresult = $smcFunc['db_query']('', '
	    SELECT id_article, id_member
	    FROM {db_prefix}kb_rating
	    WHERE id_member = {int:member} AND id_article = {int:art}',
		array(
			'art' => $id,
			'member' => $user_info['id'],
		)
	);
	
	if ($smcFunc['db_affected_rows']()!= 0)
		fatal_lang_error('kb_rateoneonly',false);
	$smcFunc['db_free_result']($dbresult);

	$value = !empty($_REQUEST['value']) ? (int) $_REQUEST['value'] : 0;

	if ($value == 0){
	
		$data = array(
		    'table' => 'kb_rating',
		    'cols' => array('id_article' => 'int','id_member' => 'int','value' => 'int'),
	    );
	
	    $values = array($id,$user_info['id'],0);
		
	    $indexes = array();
		
		KB_InsertData($data, $values, $indexes);
		
		$query_params = array(
		    'table' => 'kb_articles',
	        'set' => 'rate = rate - {int:one}',
	        'where' => 'kbnid = {int:kbnid}',
	    );

        $query_data = array(
	        'kbnid' => $id,
			'one' => 1,
        );
		
	    kb_UpdateData($query_params,$query_data);
		
		$request = $smcFunc['db_query']('', '
		    SELECT rate
	        FROM {db_prefix}kb_articles
	        WHERE kbnid = {int:kbnid}',
			array(
				 'kbnid' => $id,
			)
		);
	    list ($quantity) = $smcFunc['db_fetch_row']($request);
	    $smcFunc['db_free_result']($request);

	    if($quantity < 0){
	        
			$query_params1 = array(
		        'table' => 'kb_articles',
	            'set' => 'rate = {int:one}',
	            'where' => 'kbnid = {int:kbnid}',
	        );

            $query_data1 = array(
	            'kbnid' => $id,
			    'one' => 0,
            );
		
	        kb_UpdateData($query_params1,$query_data1);
	    }	
        	KB_cleanCache();	
	}
	else
	{
		$data = array(
		    'table' => 'kb_rating',
		    'cols' => array('id_article' => 'int','id_member' => 'int','value' => 'int'),
	    );
	
	    $values = array($id,$user_info['id'],1);
		
	    $indexes = array();
		
		KB_InsertData($data, $values, $indexes);
		
		$query_params2 = array(
		    'table' => 'kb_articles',
	        'set' => 'rate = rate + {int:one}',
	        'where' => 'kbnid = {int:kbnid}',
	    );

        $query_data2 = array(
	        'kbnid' => $id,
			'one' => 1,
        );
		
	    kb_UpdateData($query_params2,$query_data2);
		KB_cleanCache();
	}
	redirectexit('action=kb;area=article;cont='.$id.'');
}
?>	