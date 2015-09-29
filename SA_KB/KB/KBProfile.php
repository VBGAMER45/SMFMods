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
	
function KB_profile_main(){
    global $txt, $context;
	
	loadTemplate('KB');
    KB_file_include('KBSubs');
	
	$context[$context['profile_menu_name']]['tab_data']['description'] = $txt['knowledgebase'];
	$context[$context['profile_menu_name']]['tab_data']['title'] = $txt['knowledgebase'];
	$context['page_title'] = $txt['knowledgebase'];
	
    if(isset($_REQUEST['sa'])){
    
	    if($_REQUEST['sa']=='main'){
           KB_profile_articles_main();
        }
	    if($_REQUEST['sa']=='articles'){
            KB_profile_articles();
        }
	    if($_REQUEST['sa']=='unapproved'){
            KB_profile_notapproved();
        }
    }
    else{
        KB_profile_articles_main();
    }
}

function KB_profile_articles_main(){
    global $membername, $total_articles, $smcFunc;
	
	if(empty($_REQUEST['u']))
	   fatal_lang_error('kb_profile_no_mem');
	
	$memid = (int) $_REQUEST['u'];
	
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}kb_articles
		WHERE approved = 1 AND id_member = {int:mem} ',
		array(
		   'mem' => (int)  $memid,
		)
	);
				
	list ($total_articles) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
	
	$request = $smcFunc['db_query']('', '
		SELECT real_name
		FROM {db_prefix}members
		WHERE id_member = {int:mem} ',
		array(
		   'mem' => (int)  $memid,
		)
	);
				
	list ($membername) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
}

function KB_profile_articles(){
   global $scripturl, $context, $txt, $modSettings, $sourcedir;

   if(empty($_REQUEST['u']))
	    fatal_lang_error('kb_profile_no_mem');
	
   $memid = (int) $_REQUEST['u'];
   
   $artperpage = !empty($modSettings['kb_app']) ? $modSettings['kb_app'] : 30;
   
   $list_options = array(
		'id' => 'kb_profile',
		'title' => $txt['kb_proart'],
		'items_per_page' => $artperpage,
		'base_href' => $scripturl . '?action=profile;area=kb;u='.$memid.'',
		'default_sort_col' => 'kbnid',
		'get_items' => array(
			'function' => create_function('$start, $items_per_page, $sort', '
				global $context, $user_info, $memid, $smcFunc;
		
		$memid = (int) $_REQUEST[\'u\'];	
		
		if ($context[\'user\'][\'is_guest\'])
		  $groupid = -1;
	    else
	      $groupid =  $user_info[\'groups\'][0];
		  
		$request = $smcFunc[\'db_query\'](\'\', \'
		    SELECT k.kbnid, k.title, k.views, k.date, p.view, k.id_cat, k.id_member, m.real_name, k.rate, k.comments
            FROM {db_prefix}kb_articles AS k
			LEFT JOIN {db_prefix}members AS m  ON (k.id_member = m.id_member)
			LEFT JOIN {db_prefix}kb_category AS c ON (k.id_cat = kbid)  
			LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND c.kbid = p.id_cat)  
			WHERE k.id_member = {int:mem} AND approved = 1
            ORDER BY {raw:sort}
            LIMIT {int:start}, {int:per_page}\',
            array(
			   \'groupid\' => $groupid,
			   \'mem\' => (int) $memid,
			   \'sort\' => $sort,
			   \'start\' => $start,
			   \'per_page\' => $items_per_page,
            )
		);
		$kbcn = array();
			while ($row = $smcFunc[\'db_fetch_assoc\']($request))
				
				if ($row[\'view\'] != \'0\')
				   $kbcn[] = $row;
				   
			$smcFunc[\'db_free_result\']($request);

		return $kbcn;
			'),
		),
		'get_count' => array(
			'function' => create_function('', '
				global $memid, $smcFunc;
                
				$memid = (int) $_REQUEST[\'u\'];
				
				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}kb_articles
					WHERE id_member = {int:mem} AND approved = 1\',
			        array(
			           \'mem\' => (int) $memid,
					)
				);
				
				list ($total_kbn) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_kbn;
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
					if($row[\'views\'] >= 25){
						return \'<img src="\'.$settings[\'images_url\'].\'/topic/veryhot_post.gif" alt="" align="middle" />\';
					}elseif($row[\'views\'] >= 15){
					    return \'<img src="\'.$settings[\'images_url\'].\'/topic/hot_post.gif" alt="" align="middle" />\';
					}else{
					    return \'<img src="\'.$settings[\'images_url\'].\'/topic/normal_post.gif" alt="" align="middle" />\';
					}
					
					'),
					'style' => 'width: 1%; text-align: left;',
				),
				'sort' =>  array(
					'default' => 'kbnid DESC',
					'reverse' => 'kbnid',
				),
			),
			'kbnid' => array(
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
					'default' => 'kbnid DESC',
					'reverse' => 'kbnid',
				),
			),
			'author' => array(
				'header' => array(
					'value' => $txt['knowledgebaseauthor'],
				),
				'data' => array(
					'function' => create_function('$row', '
                        global $scripturl;
						return \'\'.KB_profileLink($row[\'real_name\'], $row[\'id_member\']).\'\';
					'),
					'style' => 'width: 5%; text-align: center;',
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

						return \'<div class="smalltext">\'.timeformat($row[\'date\']).\'</div>\';
					'),
					'style' => 'width: 10%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'date',
					'reverse' => 'date DESC',
				),
			),
			'rate' => array(
				'header' => array(
					'value' => $txt['kb_pinfi2'],
				),
				'data' => array(
					'function' => create_function('$row', '
					    return KB_Stars_Precent($row[\'rate\']);
					'),
					'style' => 'width: 5%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'rate',
					'reverse' => 'rate DESC',
				),
			),
			'comments' => array(
				'header' => array(
					'value' => $txt['kb_ecom2'],
				),
				'data' => array(
					'function' => create_function('$row', '

						return $row[\'comments\'];
					'),
					'style' => 'width: 5%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'comments',
					'reverse' => 'comments DESC',
				),
	    	),		
			'views' => array(
				'header' => array(
					'value' => $txt['knowledgebaseviews'],
				),
				'data' => array(
					'function' => create_function('$row', '

						return $row[\'views\'];
					'),
					'style' => 'width: 5%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'views',
					'reverse' => 'views DESC',
				),
	    	),		
		),
	);

	if(empty($modSettings['kb_show_view'])){
        unset($list_options['columns']['views']);
	}
    if(empty($modSettings['kb_eratings'])){
        unset($list_options['columns']['rate']);
	}
	if(empty($modSettings['kb_ecom'])){
        unset($list_options['columns']['comments']);
	}
	if(empty($modSettings['kb_salegend'])){
        unset($list_options['columns']['icon']);
	}
	require_once($sourcedir . '/Subs-List.php');

	createList($list_options);
}
function KB_profile_notapproved(){
   global $scripturl, $txt, $modSettings, $sourcedir;
  
    if(empty($_REQUEST['u']))
	    fatal_lang_error('kb_profile_no_mem');
	
    $memid = (int) $_REQUEST['u'];
   
    $artperpage = !empty($modSettings['kb_app']) ? $modSettings['kb_app'] : 30;
   
    $list_options = array(
		'id' => 'kb_profile',
		'title' => $txt['kb_no_approveprofile'],
		'items_per_page' => $artperpage,
		'base_href' => $scripturl . '?action=profile;area=kb;u='.$memid.'',
		'default_sort_col' => 'kbnid',
		'get_items' => array(
			'function' => create_function('$start, $items_per_page, $sort', '
				global $context, $user_info, $memid, $smcFunc;
		
		$memid = (int) $_REQUEST[\'u\'];	
		
		if ($context[\'user\'][\'is_guest\'])
		  $groupid = -1;
	    else
	      $groupid =  $user_info[\'groups\'][0];
		  
		$request = $smcFunc[\'db_query\'](\'\', \'
		    SELECT k.kbnid, k.title, k.views, k.date, p.view, k.id_cat, k.id_member, m.real_name, k.rate, k.comments
            FROM {db_prefix}kb_articles AS k
			LEFT JOIN {db_prefix}members AS m  ON (k.id_member = m.id_member)
			LEFT JOIN {db_prefix}kb_category AS c ON (k.id_cat = kbid)  
			LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND c.kbid = p.id_cat)  
			WHERE k.id_member = {int:mem} AND approved = 0
            ORDER BY {raw:sort}
            LIMIT {int:start}, {int:per_page}\',
            array(
			   \'groupid\' => $groupid,
			   \'mem\' => (int) $memid,
			   \'sort\' => $sort,
			   \'start\' => $start,
			   \'per_page\' => $items_per_page,
            )
		);
		$kbcn = array();
			while ($row = $smcFunc[\'db_fetch_assoc\']($request))
				
				if ($row[\'view\'] != \'0\')
				   $kbcn[] = $row;
				   
			$smcFunc[\'db_free_result\']($request);

		return $kbcn;
			'),
		),
		'get_count' => array(
			'function' => create_function('', '
				global $memid, $smcFunc;
                
				$memid = (int) $_REQUEST[\'u\'];
				
				$request = $smcFunc[\'db_query\'](\'\', \'
					SELECT COUNT(*)
					FROM {db_prefix}kb_articles
					WHERE id_member = {int:mem} AND approved = 1\',
			        array(
			           \'mem\' => (int) $memid,
					)
				);
				
				list ($total_kbn) = $smcFunc[\'db_fetch_row\']($request);
				$smcFunc[\'db_free_result\']($request);

				return $total_kbn;
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
					if($row[\'views\'] >= 25){
						return \'<img src="\'.$settings[\'images_url\'].\'/topic/veryhot_post.gif" alt="" align="middle" />\';
					}elseif($row[\'views\'] >= 15){
					    return \'<img src="\'.$settings[\'images_url\'].\'/topic/hot_post.gif" alt="" align="middle" />\';
					}else{
					    return \'<img src="\'.$settings[\'images_url\'].\'/topic/normal_post.gif" alt="" align="middle" />\';
					}
					
					'),
					'style' => 'width: 1%; text-align: left;',
				),
				'sort' =>  array(
					'default' => 'kbnid DESC',
					'reverse' => 'kbnid',
				),
			),
			'kbnid' => array(
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
					'default' => 'kbnid DESC',
					'reverse' => 'kbnid',
				),
			),
			'author' => array(
				'header' => array(
					'value' => $txt['knowledgebaseauthor'],
				),
				'data' => array(
					'function' => create_function('$row', '
                        global $scripturl;
						return \'\'.KB_profileLink($row[\'real_name\'], $row[\'id_member\']).\'\';
					'),
					'style' => 'width: 5%; text-align: center;',
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

						return \'<div class="smalltext">\'.timeformat($row[\'date\']).\'</div>\';
					'),
					'style' => 'width: 10%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'date',
					'reverse' => 'date DESC',
				),
			),
			'rate' => array(
				'header' => array(
					'value' => $txt['kb_pinfi2'],
				),
				'data' => array(
					'function' => create_function('$row', '
					    return KB_Stars_Precent($row[\'rate\']);
					'),
					'style' => 'width: 5%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'rate',
					'reverse' => 'rate DESC',
				),
			),
			'comments' => array(
				'header' => array(
					'value' => $txt['kb_ecom2'],
				),
				'data' => array(
					'function' => create_function('$row', '

						return $row[\'comments\'];
					'),
					'style' => 'width: 5%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'comments',
					'reverse' => 'comments DESC',
				),
	    	),		
			'views' => array(
				'header' => array(
					'value' => $txt['knowledgebaseviews'],
				),
				'data' => array(
					'function' => create_function('$row', '

						return $row[\'views\'];
					'),
					'style' => 'width: 5%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'views',
					'reverse' => 'views DESC',
				),
	    	),		
		),
	);
		
	if(empty($modSettings['kb_show_view'])){
        unset($list_options['columns']['views']);
	}
    if(empty($modSettings['kb_eratings'])){
        unset($list_options['columns']['rate']);
	}
	if(empty($modSettings['kb_ecom'])){
        unset($list_options['columns']['comments']);
	}
	if(empty($modSettings['kb_salegend'])){
        unset($list_options['columns']['icon']);
	}
	require_once($sourcedir . '/Subs-List.php');

	createList($list_options);
}
?>