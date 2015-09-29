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
	
function KBAllowedto($cat = 0,$perm)
{
	global $txt, $smcFunc, $user_info;
	
	if(allowedTo('admin_forum') || allowedTo('manage_kb'))
	   return true;
	
	$cat = (int) $cat;
	
	if (!$user_info['is_guest'])
	{		
	    $dbresult = $smcFunc['db_query']('', '
		    SELECT m.id_member, c.delarticle, c.delanyarticle, c.editanyarticle, c.editarticle, c.addarticle, c.view
		    FROM {db_prefix}kb_catperm as c 
			LEFT JOIN {db_prefix}members as m ON (m.id_member = {int:memid})
		    WHERE  c.id_group = m.id_group AND c.id_cat = {int:cat} 
			LIMIT 1',
		    array(
		       'memid' => $user_info['id'],
		       'cat' => $cat,
			)
		);
	}
	else{
		
		$dbresult = $smcFunc['db_query']('', '
		    SELECT c.delarticle, c.delanyarticle, c.editanyarticle, c.editarticle, c.addarticle, c.view
		    FROM {db_prefix}kb_catperm as c 
		    WHERE c.id_group = -1 AND c.id_cat = {int:cat} 
			LIMIT 1',
		    array(
		       'cat' => $cat,
		    )
		);
	}	
	if ($smcFunc['db_affected_rows']()== 0)
		$smcFunc['db_free_result']($dbresult);
	else
	{
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$smcFunc['db_free_result']($dbresult);
		
		if($perm == 'addarticle' && $row['addarticle'] != 0)
			return true;
		if($perm == 'editanyarticle' && $row['editanyarticle'] != 0)
			return true;
		if($perm == 'editarticle' && $row['editarticle'] != 0)
			return true;
		if($perm == 'delarticle' && $row['delarticle'] != 0)
			return true;
		if($perm == 'delanyarticle' && $row['delanyarticle'] != 0)
			return true;
	}
}

function KBisAllowedto($cat,$perm)
{
	global $txt, $smcFunc, $user_info;
	
	$cat = (int) $cat;
	
	if (!$user_info['is_guest'])
	{		
	    $dbresult = $smcFunc['db_query']('', '
		    SELECT m.id_member, c.delarticle, c.delanyarticle, c.editanyarticle, c.addarticle, c.view
		    FROM {db_prefix}kb_catperm as c 
			LEFT JOIN {db_prefix}members as m ON (m.id_member = {int:memid})
		    WHERE  c.id_group = m.id_group AND c.id_cat = {int:cat} 
			LIMIT 1',
		    array(
		       'memid' => $user_info['id'],
		       'cat' => $cat,
			)
		);
	}
	else{
		
		$dbresult = $smcFunc['db_query']('', '
		    SELECT c.editanyarticle, c.delarticle, c.delanyarticle, c.addarticle, c.view
		    FROM {db_prefix}kb_catperm as c 
		    WHERE c.id_group = -1 AND c.id_cat = {int:cat} 
			LIMIT 1',
		    array(
		       'cat' => $cat,
		    )
		);
	}	
	if ($smcFunc['db_affected_rows']()== 0)
		$smcFunc['db_free_result']($dbresult);
	else
	{
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$smcFunc['db_free_result']($dbresult);
		
		if($perm == 'view' && $row['view'] == 0)
			fatal_lang_error('cannot_view_knowledge',false);
		if($perm == 'addarticle' && $row['addarticle'] == 0)
			fatal_lang_error('cannot_view_knowledge',false);
		if($perm == 'editanyarticle' && $row['editanyarticle'] == 0)
			fatal_lang_error('cannot_view_knowledge',false);
	}
}
	
function KB_perm(){
    global $context, $txt, $cname, $scripturl, $smcFunc;
	
	$context['sub_template']  = 'kb_perm';
	
	isAllowedTo('manage_kb');
	
	if(!isset($_GET['save'])){
	
	    // Load the membergroups
	    $dbresult = $smcFunc['db_query']('', "
	        SELECT id_group, group_name 
	        FROM {db_prefix}membergroups 
	        WHERE min_posts = -1 ORDER BY group_name");
	    while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	    {
		    $context['groups'][$row['id_group']] = array(
			    'ID_GROUP' => $row['id_group'],
			    'group_name' => $row['group_name'],
		    );
	    }
	    $smcFunc['db_free_result']($dbresult);
	
	    $request = $smcFunc['db_query']('', '
		    SELECT name
		    FROM {db_prefix}kb_category
		    WHERE kbid = {int:perm}',
		    array(
			    'perm' => (int) $_GET['perm'],
		    )
	    );
	    list ($cname) = $smcFunc['db_fetch_row']($request);
	    $smcFunc['db_free_result']($request);
	
	    $context['linktree'][] = array(
		    'url' => $scripturl . '?action=kb;area=permcat;perm='.$_GET['perm'].'',
		    'name' => ''.$txt['kb_catperm7'].'  - '.$cname.''
	    );
	
	    // membergroup
	    $dbresult = $smcFunc['db_query']('', "
	        SELECT c.id_cat, c.id, c.editanyarticle, c.delarticle, c.delanyarticle, c.editarticle, c.addarticle, c.view, c.id_group, m.group_name
	        FROM {db_prefix}kb_catperm as c 
		    LEFT JOIN {db_prefix}membergroups AS m ON (m.id_group = c.id_group)
		    LEFT JOIN {db_prefix}kb_category AS a ON (a.kbid = c.id_cat)
	        WHERE  c.id_cat = {int:perm} AND m.id_group = c.id_group AND a.kbid = c.id_cat",
		    array(
			    'perm' => (int) $_GET['perm'],
		    )
	    );
	    $context['kb_membergroup'] = array();
	    while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		    $context['kb_membergroup'][] = $row;
	    $smcFunc['db_free_result']($dbresult);
	
	    // Guests
	    $dbresult = $smcFunc['db_query']('', "
	        SELECT c.id_cat, c.id, c.editanyarticle, c.delarticle, c.delanyarticle, c.editarticle, c.addarticle, c.view, c.id_group 
	        FROM {db_prefix}kb_catperm as c 
		    LEFT JOIN {db_prefix}kb_category AS a ON (a.kbid = c.id_cat)
	        WHERE c.id_cat = {int:perm} AND c.id_group = -1 AND a.kbid = c.id_cat LIMIT 1",
		    array(
			    'perm' => (int) $_GET['perm'],
		    )
	    );
	    $context['kb_guest'] = array();
	    while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		    $context['kb_guest'][] = $row;
	    $smcFunc['db_free_result']($dbresult);	
	
	    //Regular members
	    $dbresult = $smcFunc['db_query']('', "
	        SELECT c.id_cat, c.id, c.addarticle, c.delarticle, c.delanyarticle, c.editarticle, c.editanyarticle, c.view, c.id_group 
	        FROM {db_prefix}kb_catperm as c 
		    LEFT JOIN {db_prefix}kb_category AS a ON (a.kbid = c.id_cat)
	        WHERE c.id_cat = {int:perm} AND c.id_group = 0 AND a.kbid = c.id_cat LIMIT 1",
		    array(
			    'perm' => (int) $_GET['perm'],
		    )
	    );
	    $context['reg_reggroup'] = array();
	    while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		    $context['reg_reggroup'][] = $row;
	    $smcFunc['db_free_result']($dbresult);	
	}

	if(isset($_GET['save'])){
	
	    checkSession();
	    $groupname = (int) $_REQUEST['groupname'];
	    $cat = (int) $_REQUEST['save'];
	    $view = isset($_REQUEST['view']) ? 1 : 0;
	    $addarticle = isset($_REQUEST['addarticle']) ? 1 : 0;
	    $editanyarticle = isset($_REQUEST['editanyarticle']) ? 1 : 0;
	    $editarticle = isset($_REQUEST['editarticle']) ? 1 : 0;
	    $delarticle = isset($_REQUEST['delarticle']) ? 1 : 0;
	    $delanyarticle = isset($_REQUEST['delanyarticle']) ? 1 : 0;
	
	    $request = $smcFunc['db_query']('', '
		    SELECT kbid,name
            FROM {db_prefix}kb_category
		    WHERE kbid = {int:kbid}
            LIMIT 1',
            array(
		       'kbid' => $cat,
            )
	    );
	    list ($nameid,$title) = $smcFunc['db_fetch_row']($request);
	    $smcFunc['db_free_result']($request);
		
		$mes = ''.$txt['kb_log_text12'].'  <strong><a href="'.$scripturl.'?action=kb;area=cats;cat='.$nameid.'">'. $title.'</a></strong>';
	  	KB_log_actions('perm_cat',$nameid, $mes);
		
		// No point in given the add article permission if they cant view the category
		if($view == 0)
		    $addarticle = 0;
		if($view == 0 && $addarticle == 1)
		    $addarticle = 0;
			
	    // Check if permission exits
	    $dbresult = $smcFunc['db_query']('', "
	        SELECT id_group,id_cat 
	        FROM {db_prefix}kb_catperm 
	        WHERE id_group = {int:ig} AND id_cat = {int:ccat}",
		    array(
			    'ig' => $groupname,
			    'ccat' => $cat
		    )
	    );
			
	    if ($smcFunc['db_affected_rows']()!= 0)
	    {
		    $smcFunc['db_free_result']($dbresult);
			
			$query_params = array(
			    'table' => 'kb_catperm',
			    'set' => 'id_group = {int:ig},id_cat = {int:ccat},view = {int:view},addarticle = {int:addarticle},editanyarticle = {int:editanyarticle},editarticle = {int:editarticle},delarticle = {int:delarticle},delanyarticle = {int:delanyarticle}',
			    'where' => 'id_cat = {int:ccat} AND id_group = {int:ig}',
		    );

		    $query_data = array(
		        'view' => (int) $view,
				'ig' => $groupname,
				'ccat' => (int) $cat,
				'addarticle' => (int) $addarticle,
				'editanyarticle' => (int) $editanyarticle,
				'editarticle' => (int) $editarticle,
				'delarticle' => (int) $delarticle,
			    'delanyarticle' => (int) $delanyarticle
		    );
		
		    kb_UpdateData($query_params,$query_data);
		    KB_cleanCache();
    	    redirectexit('action=kb;area=permcat;perm='.$cat.'');
	    }
	    else{
	        // Insert into database
	        $data = array(
		        'table' => 'kb_catperm',
		        'cols' => array('id_group' => 'int', 'id_cat' => 'int', 'view' => 'int', 'addarticle' => 'int', 'editanyarticle' => 'int', 'editarticle' => 'int', 'delarticle' => 'int', 'delanyarticle' => 'int'),
	        );
	
	        $values = array($groupname, $cat, $view,$addarticle,$editanyarticle,$editarticle,$delarticle,$delanyarticle);
		
	        $indexes = array();
		
		    KB_InsertData($data, $values, $indexes);
			KB_cleanCache();
		    redirectexit('action=kb;area=permcat;perm='.$cat.'');
	    }
	}
}	
?>	