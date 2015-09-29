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
	
function KB_addknow(){
   global $smcFunc, $txt, $modSettings, $sourcedir, $scripturl, $user_info, $context;
   
    $context['sub_template']  = 'kb_addknow';

    $context['linktree'][] = array(
	   'url' => $scripturl . '?action=kb;area=addknow;cat=',!empty($_GET['cat']) ? $_GET['cat'] : '0','',
	   'name' => $txt['knowledgebasecataddedit1']
	);
   
    if ($context['user']['is_guest'])
		$groupid = -1;
	else
	    $groupid =  $user_info['groups'][0];
	
    $result = $smcFunc['db_query']('', '
	    SELECT c.kbid, c.name, p.view, c.id_parent
	    FROM {db_prefix}kb_category AS c
		LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND c.kbid = p.id_cat)',
		array(
		    'groupid' => $groupid,
		)
	);
	$context['knowcat'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['knowcat'][] = $row;  
	}
	$smcFunc['db_free_result']($result);
	KB_PrettyCategory();
	
	KB_showediter(!empty($_POST['description']) ? $_POST['description'] : '','description');
	
	if(empty($context['knowcat']))
	    fatal_lang_error('kb_no_cats_p',false);
		
	if (isset($_REQUEST['preview'])){
		KB_showediterpreview($_POST['title'],$_POST['description'],'kb_addknow');
	}
	else{
			
	    if(isset($_GET['save'])){
	
	        checkSession();
		    kb_checkAttachment();
	        $_POST['title'] = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);
	        $_POST['description'] = $smcFunc['htmlspecialchars']($_POST['description'], ENT_QUOTES);
	   
	        if(empty($_POST['title']))
	           fatal_lang_error('knowledgebase_emtytitle',false);
	   
	        if(empty($_POST['description']))
		       fatal_lang_error('knowledgebase_emtydesc',false);
		
		    $approved = allowedTo('auto_approve_kb') ? 1 : 0;
		    $_POST['featured'] = isset($_POST['featured']) ? 1 : 0;
	   
	        $data = array(
		        'table' => 'kb_articles',
		        'cols' => array('featured' => 'int','source' => 'string','title' => 'string', 'content' => 'string', 'id_cat' => 'string', 'id_member' => 'string', 'date' => 'string','approved' => 'int'),
	        );
	
	        $values = array($_POST['featured'], $_POST['source'], $_POST['title'], $_POST['description'], $_POST['cat'], $user_info['id'], time(),$approved);
		
	        $indexes = array();
		
		    KB_InsertData($data, $values, $indexes);
		
		    $request = $smcFunc['db_query']('', '
		        SELECT MAX(kbnid)
			    FROM {db_prefix}kb_articles');
			
            list ($max_ind) = $smcFunc['db_fetch_row']($request);
            $smcFunc['db_free_result']($request);
		
		    $attachment_params = array(
		        'article_id' => $max_ind,
			    'article_edit' => false
		    );
		
		    kb_makeAttachment($attachment_params);

		    $mes = ''.$txt['kb_log_text10'].' <strong><a href="'.$scripturl.'?action=kb;area=article;cont='.$max_ind.'">'.$_POST['title'].'</a></strong>';
		    KB_log_actions('add_article',0, $mes);
		
	        KBrecountItems();
		    
			KB_cleanCache();
			
	        if($approved == 1)
	           redirectexit('action=kb;area=article;cont='.$max_ind.';yesa'); 
		    else
		       redirectexit('action=kb;area=article;cont='.$max_ind.';noa'); 
	    }
    }
}

function KB_catadd(){
   global $txt, $smcFunc, $scripturl, $user_info, $context;
   
    isAllowedTo('manage_kb');
    $context['sub_template']  = 'kb_catadd';
   
    $context['linktree'][] = array(
	    'url' => $scripturl . '?action=kb;area=catadd',
	    'name' => $txt['knowledgebasecatadd'],
    ); 		
	
	if ($context['user']['is_guest'])
		$groupid = -1;
	else
	$groupid =  $user_info['groups'][0];
	
    $result = $smcFunc['db_query']('', '
	    SELECT c.kbid, c.name, p.view, c.name, c.id_parent
	    FROM {db_prefix}kb_category AS c
		LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND c.kbid = p.id_cat)',
		array(
		    'groupid' => $groupid,
		)
	);
	$context['knowcat'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['knowcat'][] = $row;	
	}
	$smcFunc['db_free_result']($result);
	KB_PrettyCategory();
	
    if(isset($_GET['save'])){
       
	    checkSession();
	   
        $_POST['title'] = $smcFunc['htmlspecialchars']($_POST['title'], ENT_QUOTES);
	    $_POST['description'] = $smcFunc['htmlspecialchars']($_POST['description'], ENT_QUOTES);
	    $_POST['image'] = $smcFunc['htmlspecialchars']($_POST['image'], ENT_QUOTES);
	   
	    if(empty($_POST['title']))
	        fatal_lang_error('knowledgebase_emtytitle',false);
		   
		if(empty($_POST['image']))
		    $_POST['image'] = '';
	   
	    if (isset($_POST['cat']))
	        $context['kb_parent']  = (int) $_POST['cat'];
	    else 
	        $context['kb_parent']  = 0;
		
        $data = array(
		    'table' => 'kb_category',
		    'cols' => array('name' => 'string', 'description' => 'string','id_parent' => 'int','image' => 'string'),
	    );
	
	    $values = array($_POST['title'], $_POST['description'], $context['kb_parent'],$_POST['image']);
		
	    $indexes = array();
		
		KB_InsertData($data, $values, $indexes);
		
	    KBrecountItems();
		KB_cleanCache();
		$mes = ''.$txt['kb_log_text9'].' <strong>'.$_POST['title'].'</strong>';
	    KB_log_actions('add_cat',0, $mes);
		redirectexit('action=kb;area=catadd;added');   
    }
}

function KB_del(){
    global $user_info, $txt, $smcFunc;
	 
	checkSession('get');
	
	$params = array(
		'table' => 'kb_articles',
		'call' => 'id_cat,title,id_member',
	    'where' => 'kbnid = {int:kbnid} AND approved = 1',
	);

	$data = array('kbnid' => (int) $_GET['et']);
	
    $del_data = KB_ListData($params, $data);
	$cat = $del_data['id_cat'];
	$title = $del_data['title'];
	$memberid = $del_data['id_member'];
	
	if(!KBAllowedto($cat,'delanyarticle') && $memberid != $user_info['id'])
	    fatal_lang_error('cannot_del_knowledge',false);
	
	$mes = ''.$txt['kb_log_text8'].' <strong>'.$title.'</strong>';
	KB_log_actions('del_article',0, $mes);
	
	$query_params = array(
		'table' => 'kb_articles',
		'where' => 'kbnid = {int:kbid}',
	);

	$query_data = array(
		'kbid' => (int) $_GET['et'],
	);
	
	$query_params1 = array(
		'table' => 'kb_comments',
		'where' => 'id_article = {int:kbid}',
	);

	$query_data1 = array(
		'kbid' => (int) $_GET['et'],
	);
	
	$query_params2 = array(
		'table' => 'kb_rating',
		'where' => 'id_article = {int:kbid}',
	);

	$query_data2 = array(
		'kbid' => (int) $_GET['et'],
	);
		
	KB_DeleteData($query_params,$query_data);
	KB_DeleteData($query_params1,$query_data1);
	KB_DeleteData($query_params2,$query_data2);
	
	KBrecountItems();
	KB_cleanCache();
	
	redirectexit('action=kb;deleted');
}

function KB_delcat(){
    global $user_info,$txt,$smcFunc;
	
	if(isset($_GET['delete'])){
		
		checkSession('get');
		
		$params = array(
		    'table' => 'kb_articles',
		    'call' => 'kbnid',
	        'where' => 'id_cat = {int:kbnid}',
	    );

		$params1 = array(
		    'table' => 'kb_category',
		    'call' => 'name',
	        'where' => 'kbid = {int:kbnid}',
	    );
	    $data = array('kbnid' => (int) $_GET['delete']);
	
        $del_cdata = KB_ListData($params, $data);
		$del_cdata1 = KB_ListData($params1, $data);
		$kid = $del_cdata['kbnid'];
		$name = $del_cdata1['name'];
	
	    $mes = ''.$txt['kb_log_text7'].' <strong>'.$name.'</strong>';
	    KB_log_actions('del_cat',$_GET['delete'], $mes);
	
		$query_params0 = array(
		    'table' => 'kb_category',
		    'where' => 'kbid = {int:kbid}',
	    );

	    $query_data0 = array(
		    'kbid' => (int) $_GET['delete'],
	    );
		
		$query_params1 = array(
		    'table' => 'kb_articles',
		    'where' => 'id_cat = {int:kbid}',
	    );

	    $query_data1 = array(
		    'kbid' => (int) $_GET['delete'],
	    );
		
		$query_params2 = array(
		    'table' => 'kb_catperm',
		    'where' => 'id_cat = {int:kbid}',
	    );

	    $query_data2 = array(
		    'kbid' => (int) $_GET['delete'],
	    );
		
		$query_params3 = array(
		    'table' => 'kb_comments',
		    'where' => 'id_article = {int:kbid}',
	    );

	    $query_data3 = array(
		    'kbid' => (int) $kid,
	    );
		
		$query_params4 = array(
		    'table' => 'kb_rating',
		    'where' => 'id_article = {int:kbid}',
	    );

	    $query_data4 = array(
		    'kbid' => (int) $kid,
	    );
		
		$query_params = array(
			'table' => 'kb_category',
			'set' => 'id_parent = {int:zero}',
			'where' => 'id_parent = {int:kbid}',
		);

		$query_data = array(
		    'kbid' => (int) $_GET['delete'],
			'zero' => 0,
		);
		
	    KB_DeleteData($query_params0,$query_data0);
		KB_DeleteData($query_params1,$query_data1);
		KB_DeleteData($query_params2,$query_data2);
		KB_DeleteData($query_params3,$query_data3);
		KB_DeleteData($query_params4,$query_data4);
		
		kb_UpdateData($query_params,$query_data);
		
	    KBrecountItems();
		KB_cleanCache();
		
	    redirectexit('action=kb;area=listcat;deleted');
	}
}

function KB_editcat()
{
	global $smcFunc, $user_info, $scripturl, $txt, $context;

	if(isset($_GET['edit'])){
	    
		$result = $smcFunc['db_query']('', '
	       SELECT kbid,description,name, id_parent, image
	       FROM {db_prefix}kb_category
	       WHERE kbid = {int:kbid}',
		   array(
			 'kbid' => (int) $_GET['edit'],
			)
		);
	    $context['know'] = array();
	    while ($row = $smcFunc['db_fetch_assoc']($result))
	    {
		    $context['know'][] = array(
		       'content' => $row['description'],
			   'kbid' => $row['kbid'],
			   'title' => $row['name'],
			   'image' => $row['image'],
			   'id_parent' => $row['id_parent']
		    );
	    }
	    $smcFunc['db_free_result']($result);
		
		if ($context['user']['is_guest'])
		    $groupid = -1;
	    else
	        $groupid =  $user_info['groups'][0];
	
        $result = $smcFunc['db_query']('', '
	        SELECT c.id_parent, c.kbid, c.name, p.view, c.name
	        FROM {db_prefix}kb_category AS c
		    LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND c.kbid = p.id_cat)
			WHERE kbid != {int:kbid}',
		    array(
		      'groupid' => $groupid,
			  'kbid' => (int) $_GET['edit'],
			)
	    );
	    $context['knowcat'] = array();
	    while ($row = $smcFunc['db_fetch_assoc']($result))
	    {
		    $context['knowcat'][] = $row;
	    }
	    $smcFunc['db_free_result']($result);
	    KB_PrettyCategory();
	}
	
	if(isset($_GET['update'])){
	
	    checkSession();
	    
	    $_POST['name'] = $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES);
	    $_POST['description'] = $smcFunc['htmlspecialchars']($_POST['description'], ENT_QUOTES);
		$_POST['image'] = $smcFunc['htmlspecialchars']($_POST['image'], ENT_QUOTES);
	   
	    if(empty($_POST['name']))
	       fatal_lang_error('knowledgebase_emtytitle',false);
	    
		if(empty($_POST['image']))
            $_POST['image'] = '';
		
		$query_params = array(
			'table' => 'kb_category',
			'set' => 'name = {string:name}, description = {string:description}, id_parent = {int:idp}, image = {string:image}',
			'where' => 'kbid = {int:kbid}',
		);

		$query_data = array(
		    'kbid' => (int) $_GET['update'],
			'idp' => (int) $_POST['cat'],
			'image' => $_POST['image'],
			'name' => $_POST['name'],
			'description' => $_POST['description']
		);
		
		kb_UpdateData($query_params,$query_data);
		
	    KBrecountItems();
		KB_cleanCache();
		$mes = ''.$txt['kb_log_text6'].' <strong><a href="'.$scripturl.'?action=kb;area=cats;cat='.$_GET['update'].'">'. $_POST['name'].'</a></strong>';
	    KB_log_actions('edit_cat',$_GET['update'], $mes);
		redirectexit('action=kb;area=listcat;edited');
	}
}

function KB_edit(){
 global $smcFunc, $scripturl, $modSettings, $sourcedir, $user_info, $kname, $txt, $context;
	
	if(!isset($_GET['save']) || isset($_REQUEST['preview'])){
	    
		$context['sub_template']  = 'kb_edit';
	
	    $request = $smcFunc['db_query']('', '
		    SELECT title,id_cat,id_member
            FROM {db_prefix}kb_articles
		    WHERE kbnid = {int:kbnid} AND approved = 1
            LIMIT 1',
            array(
		       'kbnid' => (int) $_GET['aid'],
           )
	    );
	    list ($kname,$cat,$memberid) = $smcFunc['db_fetch_row']($request);
	    $smcFunc['db_free_result']($request);
		
	    $context['linktree'][] = array(
	        'url' => $scripturl . '?action=kb;area=edit;aid='. $_GET['aid'].'',
	        'name' => ''.$txt['kb_xubcat2'].' - '.$kname.'',
        ); 

	    if(!KBAllowedto($cat,'editanyarticle') && $memberid != $user_info['id'])
	        fatal_lang_error('cannot_add_knowledge',false);
		
	    $result = $smcFunc['db_query']('', '
	        SELECT k.kbnid,k.content,k.source,k.title,k.id_cat,k.id_member,k.featured
	        FROM {db_prefix}kb_articles AS k
	        WHERE kbnid = {int:kbnid}
		    LIMIT 1',
	        array(
		        'kbnid' => (int)  $_GET['aid'],
	        )
        );
	    $context['edit'] = array();
	    while ($row = $smcFunc['db_fetch_assoc']($result))
	    {
		    $context['edit'][] = array(
		        'content' => $row['content'],
				 'source' => $row['source'],
			    'title' => $row['title'],
			    'kbnid' => $row['kbnid'],
			    'id_cat' => $row['id_cat'],
			    'id_member' => $row['id_member'],
			    'featured' => $row['featured'],
		    );
	    }
	
	    if (isset($_REQUEST['preview'])){
		    KB_showediterpreview($_POST['name'],$_POST['description'],'kb_edit');}
	
	    KB_showediter(!empty($_POST['description']) ? $_POST['description'] : $context['edit'][0]['content'],'description');
	
	    if ($context['user']['is_guest'])
		    $groupid = -1;
	    else
	        $groupid =  $user_info['groups'][0];
	
       $result = $smcFunc['db_query']('', '
	        SELECT c.kbid, c.name, p.view, c.id_parent
	        FROM {db_prefix}kb_category AS c
		    LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND c.kbid = p.id_cat)',
		    array(
		       'groupid' => $groupid,
			)
		);
	    $context['knowcat'] = array();
	    while ($row = $smcFunc['db_fetch_assoc']($result))
	    {
		    $context['knowcat'][] = $row;
	    }
	    KB_PrettyCategory();
	
	    $context['kb_article_images'] = array();
	
	    $dbresult = $smcFunc['db_query']('', "
		    SELECT thumbnail, filesize, filename, id_file  
			FROM {db_prefix}kb_attachments 
			WHERE id_article = " . (int) $_GET['aid']);
	    $context['kb_article_images'] = array();
   	    while ($row = $smcFunc['db_fetch_assoc']($dbresult))
   	    {
   		    $context['kb_article_images'][] = $row;
   	    }
        $smcFunc['db_free_result']($dbresult);
	}
	if(isset($_GET['save']) && !isset($_REQUEST['preview'])){
	     
		checkSession();
		
		kb_checkAttachment();
		
		if(allowedTo('manage_kb'))
		    $_POST['memid'] = $_POST['memid'];
		else
		    $_POST['memid'] = $context['edit'][0]['id_member'];
		    
		$_POST['name'] = $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES);
	    $_POST['description'] = $smcFunc['htmlspecialchars']($_POST['description'], ENT_QUOTES);
		$_POST['memid'] = (int) $_POST['memid'];
		$_POST['featured'] = isset($_POST['featured']) ? 1 : 0;
	   
	    if(empty($_POST['name']))
	       fatal_lang_error('knowledgebase_emtytitle',false);
	  
	    if(empty($_POST['description']))
		   fatal_lang_error('knowledgebase_emtytitle',false);  
		
		$attachment_params = array(
		    'article_id' => (int) $_GET['aid'],
			'article_edit' => true
		);
		
		kb_makeAttachment($attachment_params);
		
		if(allowedTo('manage_kb'))
		$query_params = array(
			'table' => 'kb_articles',
			'set' => 'title = {string:name}, source = {string:source}, content = {string:description}, id_cat = {int:cat}, id_member = {int:memid}, featured = {int:featured}',
			'where' => 'kbnid = {int:kbid}',
		);
		else 
			$query_params = array(
			'table' => 'kb_articles',
			'set' => 'title = {string:name}, source = {string:source}, content = {string:description}, id_cat = {int:cat}, featured = {int:featured}',
			'where' => 'kbnid = {int:kbid}',
		);
		
		

		$query_data = array(
		    'kbid' => (int) $_GET['aid'],
			'name' => $_POST['name'],
			'source' => $_POST['source'],
			'description' => $_POST['description'],
			'cat' => $_POST['cat'],
			'featured' => (int) $_POST['featured'],
		);
		
		if(allowedTo('manage_kb'))
			$query_data['memid'] = $_POST['memid'];
		
		kb_UpdateData($query_params,$query_data);
		
	    KBrecountItems();
		KB_cleanCache();
		$mes = ''.$txt['kb_log_text5'].' <strong><a href="'.$scripturl.'?action=kb;area=article;cont='.$_GET['aid'].'">'. $_POST['name'].'</a></strong>';
	    KB_log_actions('edit_article',$_GET['aid'], $mes);
		redirectexit('action=kb;area=article;cont='.$_GET['aid'].';edited');
	}
}

function kb_checkAttachment(){
    global $smcFunc, $uploadsDirectory, $txt, $sourcedir, $modSettings;
	
	$uploadsDirectory = $modSettings['kb_path_attachment'];
    if (!empty($modSettings['kb_enable_attachment']) && !empty($_FILES['attachment']['name']))
	{
        $fieldname = 'attachment';
		
		// possible PHP upload errors
        $errors = array(
		    1 => $txt['kb_attach_error1'], 
            2 => $txt['kb_attach_error2'], 
            3 => $txt['kb_attach_error3'], 
            4 => $txt['kb_attach_error4'],
	    );

        // check if any files were uploaded and if 
        // so store the active $_FILES array keys
        $active_keys = array();
        foreach($_FILES[$fieldname]['name'] as $key => $filename)
        {
	        if(!empty($filename)){
		        $active_keys[] = $key;
	        }
        }
		
		//make sure the uploads directorys isnt full if enabled
		if (!empty($modSettings['kb_attachmentDirSizeLimit']))
	    {
		    $dirSize = 0;
		    $dir = @opendir($uploadsDirectory) or fatal_lang_error('cant_access_upload_path', 'critical');
		    while ($file = readdir($dir))
		    {
			    $dirSize += filesize($uploadsDirectory . '' . $file);
		    }
		    closedir($dir);

		    foreach($active_keys as $key){
		    
		        if ($_FILES[$fieldname]['size'][$key] + $dirSize > $modSettings['kb_attachmentDirSizeLimit'] * 1024){
			        fatal_lang_error('kb_attach_error5',false);
			    }
		    }
		}
			
		//check the extensions if enabled
		if (!empty($modSettings['kb_attachmentExtensions']))
	    {
		    $allowed = explode(',', strtolower($modSettings['kb_attachmentCheckExtensions']));
		    foreach ($allowed as $k => $dummy)
			    $allowed[$k] = trim($dummy);
						
		    foreach($active_keys as $key)
            {
		        if (!in_array(strtolower(substr(strrchr($_FILES[$fieldname]['name'][$key], '.'), 1)), $allowed)){
			        $kb_error_notallowed = $_FILES[$fieldname]['name'][$key].' '.$txt['kb_attach_error6'].'';
					fatal_error($kb_error_notallowed,false);
			    }
		   }
	    }
	
		//check the file size if enabled
		foreach($active_keys as $key)
        {
			$filesize = $_FILES[$fieldname]['size'][$key];

		    if (!empty($modSettings['kb_mfile_attachment']) && $filesize > $modSettings['kb_mfile_attachment'])
		    {
			    @unlink($_FILES[$fieldname]['tmp_name'][$key]);
			    fatal_error($txt['kb_attach_error7'] . round($modSettings['kb_mfile_attachment'] / 1024, 2) . 'kb',false);
		    }
		}
            
		// check for standard uploading errors
        foreach($active_keys as $key)
        {
	        if($_FILES[$fieldname]['error'][$key] != 0){
		        $kb_error_standard = $_FILES[$fieldname]['name'][$key].': '.$errors[$_FILES[$fieldname]['error'][$key]];
				fatal_error($kb_error_standard, false);
			}
        }
	
        // check that the file we are working on really was an HTTP upload
        foreach($active_keys as $key)
        {
	        if(!is_uploaded_file($_FILES[$fieldname]['tmp_name'][$key])){
			    $kb_error_http = $_FILES[$fieldname]['name'][$key].' '.$txt['kb_attach_error8'].'';
		        fatal_error($kb_error_http, false);
		    }
        }
	
        // validation... since this is an image upload script we 
        // should run a check to make sure the upload is an image
        foreach($active_keys as $key)
        {
	        if(!getimagesize($_FILES[$fieldname]['tmp_name'][$key])){
		        $kb_error_notimg = $_FILES[$fieldname]['name'][$key].' '.$txt['kb_attach_error9'].'';
				fatal_error($kb_error_notimg, false);	
	        }
        }
	
        // make a unique filename for the uploaded file and check it is 
        // not taken... if it is keep trying until we find a vacant one
        foreach($active_keys as $key)
        {
	        $now = time();
	        while(file_exists($uploadFilename[$key] = $uploadsDirectory.$now.'-'.$_FILES[$fieldname]['name'][$key])){
		        $now++;
	        }
        }
	}
}

function kb_makeAttachment($data){
    global $smcFunc, $uploadsDirectory, $txt, $sourcedir, $modSettings;

	$uploadsDirectory = $modSettings['kb_path_attachment'];
	
    // Check if they are trying to delete any current attachments....
	if (isset($_POST['kb_attach_del']) && !empty($data['article_edit']) && !empty($modSettings['kb_enable_attachment']))
	{
		$del_temp = array();
		foreach ($_POST['kb_attach_del'] as $i => $dummy)
			$del_temp[$i] = (int) $dummy;
		
		
		$dbresult = $smcFunc['db_query']('', '
		    SELECT thumbnail, filesize, filename, id_file  
			FROM {db_prefix}kb_attachments 
			WHERE id_file NOT IN ({array_int:parent_attachments}) AND id_article = '.$data['article_id'].'',
		    array(
		       'parent_attachments' => $del_temp,
			)
		);
   	    while ($row = $smcFunc['db_fetch_assoc']($dbresult))
   	    {
			
			@unlink($uploadsDirectory . '' . $row['filename']);
		    @unlink($uploadsDirectory . '' . $row['thumbnail']);
			
			$query_params = array(
			    'table' => 'kb_attachments',
			    'where' => 'id_file NOT IN ({array_int:parent_attachments}) AND id_article = '.$data['article_id'].'',
		    );

		    $query_data = array(
		        'parent_attachments' => $del_temp,
		    );
		
	        KB_DeleteData($query_params,$query_data);	
		}
		$smcFunc['db_free_result']($dbresult);
	}
	
	if (!empty($modSettings['kb_enable_attachment']) && !empty($_FILES['attachment']['name']))
	{
        $fieldname = 'attachment';

        // check if any files were uploaded and if 
        // so store the active $_FILES array keys
        $active_keys = array();
        foreach($_FILES[$fieldname]['name'] as $key => $filename)
        {
	        if(!empty($filename)){
		        $active_keys[] = $key;
	        }
        }
		
        // make a unique filename for the uploaded file and check it is 
        // not taken... if it is keep trying until we find a vacant one
        foreach($active_keys as $key)
        {
	        $now = time();
	        while(file_exists($uploadFilename[$key] = $uploadsDirectory.$now.'-'.$_FILES[$fieldname]['name'][$key])){
		        $now++;
	        }
        }

        // now let's move the file to its final and allocate it with the new filename
        foreach($active_keys as $key)
        {
	        $filesize = $_FILES[$fieldname]['size'][$key];
			@move_uploaded_file($_FILES[$fieldname]['tmp_name'][$key], $uploadFilename[$key]);
			@chmod($uploadsDirectory .  $uploadFilename[$key], 0644);
			$filename = $_FILES[$fieldname]['name'][$key];
            $nname = $now.'-'.$filename;

		    $smcFunc['db_insert']('','{db_prefix}kb_attachments',
			array(
				'id_article' => 'int',
				'filename' => 'string',
				'date' => 'string',
				'filesize' => 'string',
				'thumbnail' => 'string',
			),
			array($data['article_id'],$nname,time(),$filesize, ''),
		    array());
		}	  
	}
}
?>	