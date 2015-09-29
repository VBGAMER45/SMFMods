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

function KB()
{
	global $txt, $sourcedir, $scripturl, $modSettings, $context;

	if (empty($modSettings['kb_enabled']))
		redirectexit();

	isAllowedTo('view_knowledge');

	KB_file_include(array('KBEditer','KBPerm','KBReport','KBApprove','KBSearch','KBEdit_Add','KBView_All','KBView','KBCats','KBMisc','KBMenu','KBSubs','KBRSS'));
	
	loadTemplate('KB');
	
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=kb',
		'name' => $txt['knowledgebase']
	);

	//fix wysig
	KB_wysig_descript();
	//Are we jumping?
	KB_dojump();	
	//Any headers?
	KB_doheaders();
	//Menu anyone?
	KB_Menu();
	
	if(isset($_REQUEST['comment_recount']) && allowedTo('manage_kb'))
	    KBrecountcomments();
	   
	if(isset($_REQUEST['article_recount']) && allowedTo('manage_kb'))
	    KBrecountItems();
	   
	if(isset($_REQUEST['cache_clean']) && allowedTo('manage_kb'))
	    KB_cleanCache();

	//I am a robot
	if(empty($modSettings['kb_search_engines']))
	    $context['robot_no_index'] = true;
		
	$context['canonical_url'] = $scripturl . '?action=kb';
		
	//Put all the subactions into an array
	$subActions = array(
	    'main' => 'KB_main',
		'cats' => 'KB_know',
		'article' => 'KB_knowcont',
		'catadd' => 'KB_catadd',
		'listcat' => 'KB_catlist',
		'addknow' => 'KB_addknow',
		'del' => 'KB_del',
		'permcat' => 'KB_perm',
		'search' => 'KB_search',
		'searchmain' => 'KB_searchmain',
		'rate' => 'KB_rate',
		'edit' => 'KB_edit',
		'reporta' => 'KB_reporta',
		'manage' => 'KB_manage',
		'rss' => 'KB_rss',
		'catup' => 'KB_movecat',
		'catdown' => 'KB_movecat',
	);
	
	// Default the sub-action'.
	$_REQUEST['area'] = isset($_REQUEST['area']) && isset($subActions[$_REQUEST['area']]) ? $_REQUEST['area'] : 'main';
	
	// Set title and default sub-action.
	$context['page_title'] = $txt['knowledgebase'];
	$context['sub_action'] = $_REQUEST['area'];

	// Call the right function for this sub-acton.
	$subActions[$_REQUEST['area']]();
	
}

function KB_main(){
  global $txt, $context, $smcFunc, $modSettings, $scripturl, $user_info, $sourcedir;

    if(!empty($modSettings['kb_efeaturedarticle']))
		$context['get_featured'] = KB_get_featured();	
	
	$catperpage = !empty($modSettings['kb_cpp']) ? $modSettings['kb_cpp'] : 30;
	
    $list_options = array(
		'id' => 'kb_list',
		'title' => $txt['knowledgebase'],
		'items_per_page' => $catperpage,
		'base_href' => $scripturl . '?action=kb',
		'default_sort_col' => 'roword',
		'get_items' => array(
			'function' => create_function('$start, $items_per_page, $sort', '
				global $context, $modSettings, $scripturl, $user_info, $smcFunc;
		
		$context[\'sa_cat\'] = array();
		
		if ($context[\'user\'][\'is_guest\'])
			$groupid = -1;
		else
			$groupid =  $user_info[\'groups\'][0];
			
		if (($context[\'sa_cat\'] = cache_get_data(\'kb_get_main\'.$start.\'\', 3600)) === null)
	    {
		$context[\'sa_cat\'] = array();
		$request = $smcFunc[\'db_query\'](\'\', \'
		    SELECT c.kbid, c.name, c.description, c.count, p.view, c.image
            FROM {db_prefix}kb_category AS c
			LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND c.kbid = p.id_cat) 
            WHERE id_parent = 0			
            ORDER BY {raw:sort}
            LIMIT {int:start}, {int:per_page}\',
            array(
			  \'groupid\' => $groupid,
			   \'sort\' => $sort,
			  \'start\' => $start,
			  \'per_page\' => $items_per_page,
            )
	    );

	    // Loop through all results
	    while ($row = $smcFunc[\'db_fetch_assoc\']($request))
	    {
		    if($row[\'view\'] != 0 || $row[\'view\'] == \'\'){    
		   
		       // And add them to the list
		       $context[\'sa_cat\'][$row[\'kbid\']] = $row;
		       $context[\'sa_cat\'][$row[\'kbid\']][\'subcats\'] = array();
		   
		    }
	    }
	    $smcFunc[\'db_free_result\']($request);
        if (!empty($modSettings[\'kb_countsub\'])){
		foreach($context[\'sa_cat\'] as $test){
	    // Find the sub categories.
	    $request = $smcFunc[\'db_query\'](\'\', \'
		    SELECT c.kbid, c.name, c.id_parent, p.view, c.image, c.count
		    FROM {db_prefix}kb_category AS c
			LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND c.kbid = p.id_cat)
		    WHERE id_parent = {int:cat} AND id_parent > 0
		    ORDER BY kbid ASC\',
		    array(
			    \'cat\' => $test[\'kbid\'],
				\'groupid\' => $groupid,
		    )
	    );

	    if ($smcFunc[\'db_num_rows\']($request) > 0)
	    {
		    while ($row = $smcFunc[\'db_fetch_assoc\']($request))
			
			if($row[\'view\'] != 0 || $row[\'view\'] == \'\'){    
				
			    $context[\'sa_cat\'][$row[\'id_parent\']][\'subcats\'][] = \'<a href="\'.$scripturl.\'?action=kb;area=cats;cat=\'.$row[\'kbid\'].\'">\' . $row[\'name\'] . \'</a>\';
	        
			} 
		}
	    $smcFunc[\'db_free_result\']($request);
		}
		}
		cache_put_data(\'kb_get_main\'.$start.\'\',  $context[\'sa_cat\'], 3600);
		}  
		return $context[\'sa_cat\'];
		
			'),
		),
		'get_count' => array(
			'function' => create_function('', '
				global $smcFunc;
            if (($total_kb = cache_get_data(\'kb_totalkb_main\', 3600)) === null)
	        {
				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}kb_category
					WHERE id_parent = 0	\',
			       array());
				   
				list ($total_kb) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);
            
			    cache_put_data(\'kb_totalkb_main\',  $total_kb, 3600);
		    }     
				return $total_kb;
			'),
		),
		'no_items_label' => $txt['knowledgebasenone'],
		'columns' => array(
			'icon' => array(
				'header' => array(
					'value' => '',
				),
				'data' => array(
					'function' => create_function('$row', '
					global $settings;
					if($row[\'image\']){
					    return \'<img src="\'.$row[\'image\'].\'" alt="" />\';
				    }	
                    else{
					 return \'<img src="\'.$settings[\'default_images_url\'].\'/noimg.png" alt="" />\';
                    }					
					'),
					'style' => 'width: 5%; text-align: center;',
				),
			),
			'roword' => array(
				'header' => array(
					'value' => $txt['knowledgebase_cat'],
					
				),
				'data' => array(
					'function' => create_function('$row', '
					global $txt, $settings, $modSettings, $scripturl;
                    
					$rss_icon = !empty($modSettings[\'kb_enablersscat\']) ? \'<a href="\'.$scripturl.\'?action=kb;area=rss;cat=\'.$row[\'kbid\'].\';type=rss"><img src="\'.$settings[\'default_images_url\'].\'/kb_feed.png" alt="" /></a>\' : \'\';
				       
					   if (!empty($row[\'subcats\']) && !empty($modSettings[\'kb_countsub\'])){
			
						    return \'<a href="\'.$scripturl.\'?action=kb;area=cats;cat=\'.$row[\'kbid\'].\'">\'.parse_bbc($row[\'name\']).\'</a>&nbsp; 
							\'.$rss_icon.\'
							<br />\'.parse_bbc($row[\'description\']).\'
						   <hr /><span class="smalltext"><strong>\'.$txt[\'kb_xubcat1\'].\':&nbsp;&nbsp;</strong> \' . implode(\',&nbsp;&nbsp;\', $row[\'subcats\']) . \'</span>\';
						}
						else{
						    return \'<a href="\'.$scripturl.\'?action=kb;area=cats;cat=\'.$row[\'kbid\'].\'">\'.parse_bbc($row[\'name\']).\'</a>&nbsp;
							\'.$rss_icon.\'
							<br />\'.parse_bbc($row[\'description\']).\'\';
						}
					
					'),
					'style' => 'width: 80%; text-align: left;',
					 
				),
				'sort' =>  array(
					'default' => 'roword',
					'reverse' => 'roword DESC',
				),
			),
			'count' => array(
				'header' => array(
					'value' => $txt['knowledgebasecount'],
				),
				'data' => array(
					'function' => create_function('$row', '
					
						global $total;
					    
						    if (($total = cache_get_data(\'kb_total_main\'.$row[\'kbid\'].\'\', 3600)) === null)
	                        {
							    KB_cattotalbyid($row[\'kbid\']);
							    cache_put_data(\'kb_total_main\'.$row[\'kbid\'].\'\',  $total, 3600);
		                    }  
						return $total;
					
					'),
					
					'style' => 'width: 10%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'count',
					'reverse' => 'count DESC',
				),
			),
		),
	);

	if(allowedTo('search_kb') && !empty($modSettings['kb_quiksearchindex'])){
	    KB_array_insert($list_options, 'count',
		    array(
		        'additional_rows' => array(
                    array( 
                       'position' => 'above_column_headers',
                        'value' => template_show_search(),   
		            ),
			    ),  
		    )
	    );
    }
	
	require_once($sourcedir . '/Subs-List.php');
	createList($list_options);		
}
?>