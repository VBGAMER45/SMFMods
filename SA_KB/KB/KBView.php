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
	
function KB_knowcont(){
   global $smcFunc, $txt, $scripturl, $sourcedir, $boardurl, $modSettings, $user_info, $context;
   
    $context['sub_template']  = 'kb_knowcont';
	
	if(isset($_REQUEST['cont'])){	
	
	    if (($listData = cache_get_data('kb_articles_listinfo'.$_GET['cont'].'', 3600)) === null)
	    {
		    $params = array(
		        'table' => 'kb_articles AS a',
		        'call' => 'a.title,a.kbnid,a.id_cat,c.name',
			    'left_join' => '{db_prefix}kb_category AS c ON (a.id_cat = c.kbid)',
		        'where' => 'a.kbnid = {int:kbnid}',
	        );

	        $data = array(
		       'kbnid' => (int)  $_GET['cont'],
	        );
		
            $listData = KB_ListData($params, $data);
		    cache_put_data('kb_articles_listinfo'.$_GET['cont'].'',  $listData, 3600);
	    }
		$artname = $listData['title'];
		$aid = $listData['kbnid'];
		$cid = $listData['id_cat'];
		$cname = $listData['name'];
		
        if(!$aid){		
		    fatal_error(''.$txt['kb_pinfi7'].' <strong>'.$_GET['cont'].'</strong> '.$txt['kb_jumpgo1'].'',false);
	    }	
	
	    $context['linktree'][] = array(
	        'url' => $scripturl . '?action=kb;area=cats;cat='.$cid.'',
	        'name' => $cname,
        ); 
	
	    $context['linktree'][] = array(
	        'url' => $scripturl . '?action=kb;area=article;cont='.$_GET['cont'].'',
	        'name' => $artname,
        ); 
	    if (($context['know'] = cache_get_data('kb_articles'.$_GET['cont'].'', 3600)) === null)
	    {
	        $result = $smcFunc['db_query']('', '
	            SELECT k.kbnid,k.content, k.source, k.title,k.id_cat,k.date,k.id_member,m.real_name, k.views, k.rate, k.approved
	            FROM {db_prefix}kb_articles AS k
		        LEFT JOIN {db_prefix}members AS m ON (k.id_member = m.id_member)
		        LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = m.id_member)
	            WHERE kbnid = {int:kbnid}',
		        array(
			        'kbnid' => (int) $_GET['cont'],	
		        )
	        );
	        $context['know'] = array();
	        while ($row = $smcFunc['db_fetch_assoc']($result))
	        {	  
		    $context['know'][] = array(
		            'content' => KB_parseTags($row['content'], $row['kbnid'], 3),
			        'title' => parse_bbc($row['title']),
					'source' => parse_bbc($row['source']),
			        'kbnid' => $row['kbnid'],
			        'approved' => $row['approved'],
			        'views' => $row['views'],
			        'rate' => $row['rate'],
			        'date' => date('D d M Y',$row['date']),
			        'id_cat' => $row['id_cat'],
			        'id_member' => $row['id_member'],
			        'real_name' => $row['real_name'],
			        
		        );
	        }
	        $smcFunc['db_free_result']($result);
		    cache_put_data('kb_articles'.$_GET['cont'].'',  $context['know'], 3600);
	    }
	    
		$context['page_title'] = $context['know'][0]['title'];	
        
        if($context['know'][0]['approved'] == 0 && $context['know'][0]['id_member'] != $user_info['id'] && !allowedTo('manage_kb'))
		    fatal_lang_error('kb_articlwnot_approved',false);		
	   
	    KBisAllowedto($context['know'][0]['id_cat'],'view');
		
		$context['kbimg'] = KB_getimages($_GET['cont']);
	
	    if(!empty($modSettings['kb_ecom'])){
	        $context['kbcom'] = KB_getcomments($_GET['cont']);
	        KB_showediter(!empty($_POST['description']) ? $_POST['description'] : '','description');
	    }
	 
        KB_dojprint();
	
	    $query_params = array(
			'table' => 'kb_articles',
			'set' => 'views = views + 1',
			'where' => 'kbnid = {int:kbnid}',
		);

		$query_data = array(
		    'kbnid' => (int) $_GET['cont'],
		);
		
		kb_UpdateData($query_params,$query_data);
    }
	if($user_info['is_guest']){
	    
		require_once($sourcedir . '/Subs-Editor.php');
		$verificationOptions = array(
			'id' => 'register',
		);
		$context['visual_verification'] = create_control_verification($verificationOptions);
		$context['visual_verification_id'] = $verificationOptions['id'];
	}
	//comment
	if(isset($_REQUEST['comment'])){
	    
		if($user_info['is_guest']){
		    
			require_once($sourcedir . '/Subs-Editor.php');
		    $verificationOptions = array(
			    'id' => 'register',
		    );
			
		    $context['visual_verification'] = create_control_verification($verificationOptions, true);

		    if (is_array($context['visual_verification']))
		    {
			    loadLanguage('Errors');
			    foreach ($context['visual_verification'] as $error)
			        fatal_error($txt['error_' . $error]);
		    }	
		}
		
		isAllowedTo('com_kb');
		checkSession();
	
		$_POST['description'] = $smcFunc['htmlspecialchars']($_POST['description'], ENT_QUOTES);
	    $_GET['arid'] = (int) $_GET['arid'];
	    
		if(empty($_POST['description']))
		   fatal_lang_error('knowledgebase_emtydesc',false);
		
	    $approved = allowedTo('auto_approvecom_kb') ? 1 : 0;
		
		$mes = ''.$txt['kb_log_text4'].' <strong><a href="'.$scripturl.'?action=kb;area=article;cont='.$_GET['arid'].'">'. $context['know'][0]['title'].'</a></strong>';
	    KB_log_actions('add_com',$_GET['arid'], $mes);
		
		$data = array(
			'table' => 'kb_comments',
			'cols' => array('id_article' => 'int', 'comment' => 'string', 'date' => 'int', 'id_member' => 'int','approved' => 'int'),
		);
		$values = array(
			$_GET['arid'],
			$_POST['description'],
			time(),
			$user_info['id'],
			$approved
		);
		
		$indexes = array(
			'id_article'
		);
	    KB_InsertData($data, $values, $indexes);
		
		KBrecountcomments();
		KB_cleanCache();
		redirectexit('action=kb;area=article;cont='.$_GET['arid'].'');
	}
	
	if(isset($_REQUEST['commentdel'])){
	    
		isAllowedTo('comdel_kb');
		
		$mes = ''.$txt['kb_log_text3'].' <strong><a href="'.$scripturl.'?action=kb;area=article;cont='.$_GET['cont'].'">'. $context['know'][0]['title'].'</a></strong>';
	    KB_log_actions('del_com',$_GET['cont'], $mes);
		
		$query_params = array(
			'table' => 'kb_comments',
			'where' => 'id = {int:kbid}',
		);

		$query_data = array(
		    'kbid' => (int) $_GET['arid'],
		);
		
		KB_DeleteData($query_params,$query_data);
		KB_cleanCache();
        KBrecountcomments();
		redirectexit('action=kb;area=article;cont='.$_GET['cont'].'');
	}
	
	//approve
	if(isset($_REQUEST['approve'])){
	
	    checkSession('get');
		
		$query_params = array(
			'table' => 'kb_articles',
			'set' => 'approved = {int:one}',
			'where' => 'kbnid = {int:kbnid}',
		);

		$query_data = array(
		    'kbnid' => (int) $_REQUEST['aid'],
			'one' => 1,
		);
		
		kb_UpdateData($query_params,$query_data);
		
	    $params = array(
		    'table' => 'kb_articles',
		    'call' => 'id_member, kbnid, title',
		    'where' => 'kbnid = {int:kbnid}',
	    );

	    $data = array(
		   'kbnid' => (int)  $_GET['aid'],
	    );
		
        $listData = KB_ListData($params, $data);
		$nameid = $listData['id_member'];
		$kid = $listData['kbnid'];
		$title = $listData['title'];
	
		$kbmes = ''.$txt['kb_aapprove1'].' [url='.$scripturl.'?action=kb;area=article;cont='.$kid.']'.$txt['kb_aapprove2'].'[/url] '.$txt['kb_aapprove3'].'';
		KB_sendpm($nameid,$txt['kb_aapprove6'],$kbmes);
		
		$mes = ''.$txt['kb_log_text2'].' <strong><a href="'.$scripturl.'?action=kb;area=article;cont='.$kid.'">'. $title.'</a></strong>';
	    KB_log_actions('app_article',$kid, $mes);
		KBrecountItems();
		KB_cleanCache();
		
		redirectexit('action=kb;area=article;cont='.$_REQUEST['aid'].'');
	}
	//unapprove
	if(isset($_REQUEST['unapprove']) && isset($_REQUEST['inap'])){
	    
		checkSession('get');
		
		$query_params = array(
			'table' => 'kb_articles',
			'set' => 'approved = {int:one}',
			'where' => 'kbnid = {int:kbnid}',
		);

		$query_data = array(
		    'kbnid' => (int) $_REQUEST['inap'],
			'one' => 0,
		);
		
		kb_UpdateData($query_params,$query_data);
		
		$params = array(
		    'table' => 'kb_articles',
		    'call' => 'id_member, kbnid, title',
		    'where' => 'kbnid = {int:kbnid}',
	    );

	    $data = array(
		   'kbnid' => (int)  $_GET['inap'],
	    );
		
        $listData = KB_ListData($params, $data);
		$nameid = $listData['id_member'];
		$kid = $listData['kbnid'];
		$title = $listData['title'];
		
		$kbmes = ''.$txt['kb_aapprove4'].' [url='.$scripturl.'?action=kb;area=article;cont='.$kid.']'.$txt['kb_aapprove2'].'[/url] '.$txt['kb_aapprove3'].'';
		KB_sendpm($nameid,$txt['kb_aapprove7'],$kbmes);
		
		$mes = ''.$txt['kb_log_text1'].' <strong><a href="'.$scripturl.'?action=kb;area=article;cont='.$kid.'">'. $title.'</a></strong>';
	  	KB_log_actions('unapp_article',$kid, $mes);
		KBrecountItems();
		KB_cleanCache();
		
		redirectexit('action=kb;area=article;cont='.$_REQUEST['inap'].'');
	}
}
?>	