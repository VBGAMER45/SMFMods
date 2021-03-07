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
	
function KB_searchmain(){
   global $context, $modSettings, $txt, $scripturl, $user_info, $smcFunc;
    
	$context['sub_template']  = 'kb_searchmain';
	$context['page_title'] = $txt['kb_searchforform1'];
	
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=kb;area=searchmain',
		'name' => $txt['kb_searchforform1']
	);
	isAllowedTo('search_kb'); 
	
	if(empty($modSettings['kb_esearch']))
	   redirectexit();
		
	if ($context['user']['is_guest'])
	$groupid = -1;
	else
	$groupid =  $user_info['groups'][0];
	
    $result = $smcFunc['db_query']('', '
	    SELECT c.kbid, c.name, c.id_parent, p.view
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
}
function KB_search(){
   global $smcFunc, $txt, $scripturl, $modSettings, $searchquery, $sc, $kb_where, $sourcedir, $user_info, $context, $settings;
      
    $context['sub_template']  = 'kb_search';
	$context['page_title'] = $txt['kb_searchsearch1'];

    isAllowedTo('search_kb'); 
	
	if(empty($modSettings['kb_esearch']))
	    redirectexit();
	
	checkSession('get');	
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=kb;area=search;q='.(!empty($_SESSION['kb_search_query_encoded']) ? $_SESSION['kb_search_query_encoded'] : '').';sesc='.$sc.'',
		'name' => $txt['kb_searchsearch1']
	);
	
	if (isset($_REQUEST['q']))
	{
		$data = unserialize(base64_decode($_REQUEST['q']));
		$_REQUEST['cat'] = $data['cat'];
		$_REQUEST['searchtitle'] = $data['searchtitle'];
		$_REQUEST['searchdescription'] = $data['searchdescription'];
		$_REQUEST['search'] = $data['searchfor'];
		$_REQUEST['daterange'] = $data['daterange'];
		$_REQUEST['postername'] = !empty($data['postername']) ? $data['postername'] : '';
	}
	
	if (isset($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];
	else 
		$cat = 0;
			
    if (empty($_REQUEST['search']))
	   fatal_lang_error('kb_searchnoquery',false);
	
	$searchfor =  $smcFunc['htmlspecialchars']($_REQUEST['search'], ENT_QUOTES);
	
	if (strlen(trim($searchfor)) <= 3)
		fatal_lang_error('kb_searchnoquery1',false);
				
    $searchtitle = isset($_REQUEST['searchtitle']) ? 1 : 0;
    $searchdescription =  isset($_REQUEST['searchdescription']) ? 1 : 0;
	$daterange = (int) !empty($_REQUEST['daterange']) ? $_REQUEST['daterange'] : '';
	$memid = 0;
	
	if (!empty($_REQUEST['postername']))
	{
		$postername = str_replace('"','', $_REQUEST['postername']);
		$postername = str_replace("'",'', $postername);
		$postername = str_replace('\\','', $postername);
		$postername = htmlspecialchars($postername, ENT_QUOTES);
		$searchArray['postername'] = $postername;
						
						
		$dbresult = $smcFunc['db_query']('', '
			SELECT 
			real_name, id_member 
			FROM {db_prefix}members 
			WHERE real_name = {string:name} OR member_name = {string:name}  
			LIMIT 1',
		    array(
			    'name' => $postername,
		    )
	    );
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$smcFunc['db_free_result']($dbresult);
		
		if ($smcFunc['db_affected_rows']() != 0)
			$memid = $row['id_member'];				
	}
			
	$s1 = 1;
	$searchquery = '';
		
	    $searchArray['searchfor'] = $searchfor;
	    $searchArray['cat'] = $cat;
		$searchArray['searchtitle'] = $searchtitle;
		$searchArray['searchdescription'] = $searchdescription;
		$searchArray['memid'] = $memid;	
		$searchArray['daterange'] = $daterange;
		$context['kb_search_query_encoded'] = base64_encode(serialize($searchArray));
		$_SESSION['kb_search_query_encoded'] = $context['kb_search_query_encoded'];
	
	$context['kbwhere'] = '';
	
    if ($cat != 0)
		$context['kbwhere'] = "k.id_cat = $cat AND ";	
	
	if ($memid != 0)
		$context['kbwhere'] .= "k.id_member = $memid AND ";
	
    if ($daterange != 0)
	{
		$currenttime = time();
		$pasttime = $currenttime - ($daterange * 24 * 60 * 60);
				
		$context['kbwhere'] .=  "(k.date BETWEEN '" . $pasttime . "' AND '" . $currenttime . "')  AND";
	}
	
    if ($searchtitle){	
      $searchquery = "k.title LIKE '%$searchfor%' ";
    }
	else{
		$s1 = 0;
	}	
	
	if ($searchdescription)
	{
		if ($s1 == 1){
			$searchquery = "k.title LIKE '%$searchfor%' OR k.content LIKE '%$searchfor%'";
		}else{
			$searchquery = "k.content LIKE '%$searchfor%'";
	}   }
			
    if ($searchquery == ''){
		$searchquery = "k.title LIKE '%$searchfor%' ";
	}
	
	$kb_where = '';
	if (isset($context['kbwhere']))
		$kb_where = $context['kbwhere'];
	
	$question = $context['kb_search_query_encoded'];
	
	$artperpage = !empty($modSettings['kb_app']) ? $modSettings['kb_app'] : 30;
	
	$list_options = array(
		'id' => 'kb_search',
		'title' => $txt['kb_searchsearch1'],
		'items_per_page' => $artperpage,
		'base_href' => $scripturl.'?action=kb;area=search;q='.$question.';sesc='.$sc.'',
		'default_sort_col' => 'title',
		'get_items' => array(
			'function' => function($start, $items_per_page, $sort) use($searchquery, $kb_where, $context, $user_info, $smcFunc)
			{

				  if ($context['user']['is_guest'])
				   $groupid = -1;
				  else
				   $groupid =  $user_info['groups'][0];

				$request = $smcFunc['db_query']('', "
					SELECT k.kbnid, k.title, k.views, k.date, k.id_cat, p.view, k.id_member, m.real_name, k.rate, k.comments
					FROM {db_prefix}kb_articles AS k
					LEFT JOIN {db_prefix}members AS m ON (m.id_member = k.id_member)
					LEFT JOIN {db_prefix}kb_category AS c ON (k.id_cat = c.kbid)
					LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND k.id_cat = p.id_cat)
					WHERE  {raw:kb_where} ({raw:searchquery}) AND approved = 1
					ORDER BY {raw:sort}
					LIMIT {int:start}, {int:per_page}",
					array(
					   'groupid' => $groupid,
					   'sort' => $sort,
					   'start' => $start,
					   'per_page' => $items_per_page,
					   'kb_where' => $kb_where,
					   'searchquery' => $searchquery,
					)
				);
			$kbs = array();
				while ($row = $smcFunc['db_fetch_assoc']($request))

					if($row['view'] != '0')
					 $kbs[] = $row;

				$smcFunc['db_free_result']($request);

			return $kbs;

			},
		),
		'get_count' => array(
			'function' => function() use ($searchquery,$kb_where, $smcFunc)
			{
				$dbresult = $smcFunc['db_query']('', "
                        SELECT k.kbnid
                        FROM {db_prefix}kb_articles AS k
                        WHERE  {raw:kb_where} ({raw:searchquery}) AND approved = 1",
	                    array(
		                   'kb_where' => $kb_where,
			               'searchquery' => $searchquery,
	                    )
					);
                    $numrows = $smcFunc['db_num_rows']($dbresult);
                    $smcFunc['db_free_result']($dbresult);

				return $numrows;
			},
		),
		'no_items_label' => $txt['kb_noresults'],
		'columns' => array(
			'icon' => array(
				'header' => array(
					'value' => '',
				),
				'data' => array(
					'function' => function($row) use ($settings)
					{
						if($row['views'] >= 25){
							return '<img src="'.$settings['images_url'].'/topic/veryhot_post.gif" alt="" align="middle" />';
						}elseif($row['views'] >= 15){
							return '<img src="'.$settings['images_url'].'/topic/hot_post.gif" alt="" align="middle" />';
						}else{
							return '<img src="'.$settings['images_url'].'/topic/normal_post.gif" alt="" align="middle" />';
						}

					},
					'style' => 'width: 1%; text-align: left;',
				),
				'sort' =>  array(
					'default' => 'kbnid DESC',
					'reverse' => 'kbnid',
				),
			),
			'title' => array(
				'header' => array(
					'value' => $txt['knowledgebasetitle'],
				),
				'data' => array(
					'function' => function($row) use ($scripturl)
					{
						return '<a href="'.$scripturl.'?action=kb;area=article;cont='.$row['kbnid'].'">'.$row['title'].'</a>';
					},
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
					'function' => function($row)
					{
						return ''.KB_profileLink($row['real_name'], $row['id_member']).'';
					},
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
					'function' => function($row)
					{
						return '<div class="smalltext">'.timeformat($row['date']).'</div>';
					},
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
					'function' => function($row)
					{
						return KB_Stars_Precent($row['rate']);
					},
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
					'function' => function($row)
					{
						return $row['comments'];
					},
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
					'function' => function($row)
					{
						return $row['views'];
					},
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
	if(empty($modSettings['kb_salegend'])){
        unset($list_options['columns']['comments']);
	}
	if(empty($modSettings['kb_salegend'])){
        unset($list_options['columns']['icon']);
	}
	
	require_once($sourcedir . '/Subs-List.php');

	createList($list_options);
}

?>	