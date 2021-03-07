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
	
function KB_know(){
   global $txt, $sourcedir, $smcFunc, $modSettings, $user_info, $scripturl, $context, $settings;
   
    $context['sub_template']  = 'kb_know';
   
    $_GET['cat'] = (int) $_GET['cat'];
   
    $params = array(
		'table' => 'kb_category',
		'call' => 'name',
		'where' => 'kbid = {int:cat}',
	);

	$data = array(
		'cat' => (int) $_GET['cat'],
	);
    $catname = KB_ListData($params, $data);
	
    $context['linktree'][] = array(
	  'url' => $scripturl . '?action=kb;area=cats;cat='.$_GET['cat'].'',
	  'name' => $catname['name'],
    ); 	
	
	$context['page_title'] = $catname['name'];
    KBisAllowedto($_GET['cat'],'view');
    
	$params1 = array(
		'table' => 'kb_category',
		'call' => 'kbid',
		'where' => 'id_parent = {int:cat}',
	);

	$data1 = array(
		 'cat' => (int) $_GET['cat'],
	);
	
    $kbid = KB_ListData($params1, $data1);
	
	if($kbid['kbid'])  {
	  KB_subcat();
    }
	
	$artperpage = !empty($modSettings['kb_app']) ? $modSettings['kb_app'] : 30;
    $list_options = array(
		'id' => 'kb_know',
		'title' => $catname['name'],
		'items_per_page' => $artperpage,
		'base_href' => $scripturl . '?action=kb;area=cats;cat='.$_GET['cat'].'',
		'default_sort_col' => 'kbnid',
		'get_items' => array(
			'function' => function($start, $items_per_page, $sort)  use($user_info, $smcFunc)
			{
				$request = $smcFunc['db_query']('', '
					SELECT k.kbnid, k.title, k.views, k.date, k.id_cat, k.approved, k.id_member, m.real_name, k.rate, k.comments
					FROM {db_prefix}kb_articles AS k
					LEFT JOIN {db_prefix}members AS m  ON (k.id_member = m.id_member) 
					WHERE id_cat = {int:cat} AND approved = 1
					ORDER BY {raw:sort}
					LIMIT {int:start}, {int:per_page}',
					array(
					   'current_member' => $user_info['id'],
					   'cat' => (int) $_GET['cat'],
					   'sort' => $sort,
					   'start' => $start,
					   'per_page' => $items_per_page,
					)
				);
				$kbcn = array();
					while ($row = $smcFunc['db_fetch_assoc']($request))
						$kbcn[] = $row;
					$smcFunc['db_free_result']($request);

				return $kbcn;
			},
		),
		'get_count' => array(
			'function' => function () use ($smcFunc)
			{
				$request = $smcFunc['db_query']('', '
					SELECT COUNT(*)
					FROM {db_prefix}kb_articles
					WHERE id_cat = {int:cat} AND approved = 1',
			        array('cat' => (int) $_GET['cat'],));

				list ($total_kbn) = $smcFunc['db_fetch_row']($request);
				$smcFunc['db_free_result']($request);

				return $total_kbn;
			},
		),
		'no_items_label' => $txt['knowledgebasenone'],
		'columns' => array(
		    'kbnid' => array(
				'header' => array(
					'value' => '-',
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
					'default' => 'title DESC',
					'reverse' => 'title',
				),
			),
			'author' => array(
				'header' => array(
					'value' => $txt['knowledgebaseauthor'],
				),
				'data' => array(
					'function' =>function($row)
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