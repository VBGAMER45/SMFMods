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
	
function KB_catlist()
{
   global $smcFunc, $txt, $sourcedir, $settings, $modSettings, $user_info, $scripturl, $context, $sc;
   
    $context['sub_template']  = 'kb_catlist';
    
	isAllowedTo('manage_kb');
	
    $context['linktree'][] = array(
	    'url' => $scripturl . '?action=kb;area=listcat',
	    'name' => $txt['knowledgebasecataddedit'],
    ); 
	$catperpage = !empty($modSettings['kb_cpp']) ? $modSettings['kb_cpp'] : 30;
	
    $list_options = array(
		'id' => 'kb_list',
		'title' => $txt['knowledgebase'],
		'items_per_page' => $catperpage,
		'base_href' => $scripturl . '?action=kb;area=listcat',
		'default_sort_col' => 'roword',
		'get_items' => array(
			'function' => function($start, $items_per_page, $sort) use (&$context, $user_info, $smcFunc)
			{

		if ($context['user']['is_guest'])
		  $groupid = -1;
	    else
	     $groupid =  $user_info['groups'][0];

		$request = $smcFunc['db_query']('', '
			SELECT c.kbid, c.name, c.description, c.count, p.view, c.id_parent
            FROM {db_prefix}kb_category AS c
			LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND c.kbid = p.id_cat)  
            ORDER BY {raw:sort}
            LIMIT {int:start}, {int:per_page}',
            array(
			'groupid' => $groupid,
			  'sort' => $sort,
			  'start' => $start,
			  'per_page' => $items_per_page,
            )
		);
		$context['knowcat'] = array();
			while ($row = $smcFunc['db_fetch_assoc']($request))

				if ($row['view'] != '0')
				  $context['knowcat'][] = $row;

			$smcFunc['db_free_result']($request);
         KB_PrettyCategory();
		return $context['knowcat'];
			}
		),
		'get_count' => array(
			'function' => function() use($smcFunc)
			{
				global $smcFunc;

				$request = $smcFunc['db_query']('', '
					SELECT COUNT(*)
					FROM {db_prefix}kb_category',
			       array());

				list ($total_kb) = $smcFunc['db_fetch_row']($request);
				$smcFunc['db_free_result']($request);

				return $total_kb;
			}
		),
		'no_items_label' => $txt['knowledgebasenone'],
		'columns' => array(
			'roword' => array(
				'header' => array(
					'value' => $txt['knowledgebase_cat'],
				),
				'data' => array(
					'function' => function($row) use ($settings, $scripturl)
					{
						return $row['name'].'
						<div class="floatright"><a href="'.$scripturl.'?action=kb;area=catup;cat='.$row['kbid'].'">[&#x25B2;]</a>
						<a href="'.$scripturl.'?action=kb;area=catdown;cat='.$row['kbid'].'">[&#x25BC;]</a></div>
						';
					},
					'style' => 'width: 20%; text-align: left;',
				),
				'sort' =>  array(
					'default' => 'roword',
					'reverse' => 'roword DESC',
				),
			),
			'actions' => array(
				'header' => array(
					'value' => $txt['knowledgebase_actions'],
				),
				'data' => array(
					'function' => function ($row) use ($txt,$sc,$scripturl)
					{

						return '<a href="'.$scripturl.'?action=kb;area=permcat;perm='.$row['kbid'].'">['.$txt['kb_catperm7'].']</a> <a href="'.$scripturl.'?action=kb;area=listcat;edit='.$row['kbid'].'" onclick="return confirm(\'' .$txt['knowledgebase_editconf'].'\');">['.$txt['knowledgebase_edit'].']</a> <a href="'.$scripturl.'?action=kb;area=listcat;delete='.$row['kbid'].';sesc='.$sc.'" onclick="return confirm(\''.$txt['knowledgebase_delconf'].'\');">['.$txt['knowledgebase_del'].']</a>';
					},
					'style' => 'width: 5%; text-align: center;',
				),
			),		
		),
	);

	require_once($sourcedir . '/Subs-List.php');

	createList($list_options);
	
	if(isset($_GET['delete'])){
	    KB_delcat();
	}
	if(isset($_GET['edit']) || isset($_GET['update'])){
        KB_editcat();
	}
}

function KB_subcat(){
    global $txt, $sourcedir, $catname, $modSettings, $smcFunc, $user_info, $scripturl, $context, $settings;
	
	$params = array(
		'table' => 'kb_category',
		'call' => 'name',
		'where' => 'kbid = {int:cat}',
	);

	$data = array(
		'cat' => (int) $_GET['cat'],
	);
    
	$catname = KB_ListData($params, $data);
	$catname = $catname['name'];
	
	$catperpage = !empty($modSettings['kb_cpp']) ? $modSettings['kb_cpp'] : 30;
	
	$list_options1 = array(
		'id' => 'kb_listcat',
		'title' => ''.$txt['kb_xubcat1'].' - '.$catname,
		'base_href' => $scripturl . '?action=kb;area=cats;cat='.$_GET['cat'].'',
		'default_sort_col' => 'kbid',
		'items_per_page' => $catperpage,
		 'start_var_name' => 'startcat',
		'request_vars' => array(
             'desc' => 'desccat',
             'sort' => 'sortcat',
        ),
		'get_items' => array(
			'function' => function($start, $items_per_page, $sort) use (&$context, $scripturl, $modSettings, $user_info, $smcFunc)
			{

				$context['sa_cat'] = array();

				if ($context['user']['is_guest'])
					$groupid = -1;
				else
					$groupid =  $user_info['groups'][0];

				$request = $smcFunc['db_query']('', '
					SELECT c.kbid, c.name, c.description, c.count, p.view, c.image
					FROM {db_prefix}kb_category AS c
					LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND c.kbid = p.id_cat) 
					WHERE id_parent = {int:cat}		
					ORDER BY {raw:sort}
					LIMIT {int:start}, {int:per_page}',
					array(
					  'groupid' => $groupid,
					  'sort' => $sort,
					  'cat' => $_GET['cat'],
					  'start' => $start,
					  'per_page' => $items_per_page,
					)
				);

				// Loop through all results
				while ($row = $smcFunc['db_fetch_assoc']($request))
				{

					if($row['view'] != 0 || $row['view'] == ''){

						// And add them to the list
						$context['sa_cat'][$row['kbid']] = $row;
						$context['sa_cat'][$row['kbid']]['subcats'] = array();
					}
				}
				$smcFunc['db_free_result']($request);
				if (!empty($modSettings['kb_countsub'])){
				foreach($context['sa_cat'] as $test){
			   // Find the sub categories.
				$request = $smcFunc['db_query']('', '
					SELECT kbid, name, id_parent, p.view, c.image, c.count
					FROM {db_prefix}kb_category AS c
					LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND c.kbid = p.id_cat)
					WHERE id_parent = {int:cat} AND id_parent > 0
					ORDER BY kbid ASC',
					array(
						'cat' => $test['kbid'],
						 'groupid' => $groupid,
					)
				);

				if ($smcFunc['db_num_rows']($request) > 0)
				{
					while ($row = $smcFunc['db_fetch_assoc']($request))

						if($row['view'] != 0 || $row['view'] == ''){

							$context['sa_cat'][$row['id_parent']]['subcats'][] = '<a href="'.$scripturl.'?action=kb;area=cats;cat='.$row['kbid'].'"">' . $row['name'] . ' </a>';
						}
				}
				$smcFunc['db_free_result']($request);
				}
				}

				return $context['sa_cat'];

			},
		),
		'get_count' => array(
			'function' => function() use ($smcFunc)
			{

				$request = $smcFunc['db_query']('', '
					SELECT COUNT(*)
					FROM {db_prefix}kb_category
					WHERE id_parent = {int:cat}	',
			       array(
				   'cat' => $_GET['cat'],
				   )
				);

				list ($total_kb) = $smcFunc['db_fetch_row']($request);
				$smcFunc['db_free_result']($request);

				return $total_kb;
			}
		),
		'no_items_label' => $txt['knowledgebasenone'],
		'columns' => array(
			'icon' => array(
				'header' => array(
					'value' => '',
				),
				'data' => array(
					'function' => function($row) use ($settings)
					{
						if($row['image']){
							return '<img src="'.$row['image'].'" alt="" />';
						}
						else{
						 return '<img src="'.$settings['default_images_url'].'/noimg.png" alt="" />';
						}
					},
					'style' => 'width: 10%; text-align: center;',
				),
			),
			'kbid' => array(
				'header' => array(
					'value' => $txt['knowledgebase_cat'],
				),
				'data' => array(
					'function' => function($row) use ($txt, $settings, $modSettings, $scripturl)
					{
						$rss_icon = !empty($modSettings['kb_enablersscat']) ? '<a href="'.$scripturl.'?action=kb;area=rss;cat='.$row['kbid'].':type=rss"><img src="'.$settings['default_images_url'].'/kb_feed.png" alt="" /></a>' : '';

				        if (!empty($row['subcats']) && !empty($modSettings['kb_countsub'])){

						    return '<a href="'.$scripturl.'?action=kb;area=cats;cat='.$row['kbid'].'">'.parse_bbc($row['name']).'</a>&nbsp;
							'.$rss_icon.'
							<br />'.parse_bbc($row['description']).'
						    <hr /><span class="smalltext"><strong>'.$txt['kb_xubcat1'].':&nbsp;&nbsp;</strong> ' . implode(',&nbsp;&nbsp;', $row['subcats']) . '</span>';
						}
						else{
						    return '<a href="'.$scripturl.'?action=kb;area=cats;cat='.$row['kbid'].'">'.parse_bbc($row['name']).'</a>&nbsp;
							'.$rss_icon.'
							<br />'.parse_bbc($row['description']).'';
						}
					},
					'style' => 'width: 80%; text-align: left;',
				),
				'sort' =>  array(
					'default' => 'kbid',
					'reverse' => 'kbid DESC',
				),
			),
			'count' => array(
				'header' => array(
					'value' => $txt['knowledgebasecount'],
				),
				'data' => array(
					'function' => function ($row)
					{
						global $total;
					    KB_cattotalbyid($row['kbid']);

						return $total;
					},
					'style' => 'width: 10%; text-align: center;',
				),
				'sort' =>  array(
					'default' => 'count',
					'reverse' => 'count DESC',
				),
			),			
		),
	);

	require_once($sourcedir . '/Subs-List.php');

	createList($list_options1);
}
?>	