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

function KB_Menu(){
    global $total_report, $total_approvecom, $total_approve, $txt, $sourcedir, $smcFunc, $scripturl, $sc, $user_info, $modSettings, $context, $kb_menutype;
	
	//Get approve, approve comments, report counts 
	KB_approvecounts();
	KB_approvecomcounts();
	KB_reportcounts();
	
	//Add them all together
	$mcounts = $total_report + $total_approvecom + $total_approve;
	
	//Are we on an article?
	if(isset($_REQUEST['cont']) || isset($_GET['aid'])){
	    
		if (($context['knowmini'] = cache_get_data('kb_articles_menuinfo'.(isset($_GET['cont']) ? $_GET['cont'] : $_GET['aid']).'', 3600)) === null)
	    {
		    //Guess so lets get some info on this article
		    $result = $smcFunc['db_query']('', '
	            SELECT k.kbnid,k.id_cat,k.approved,id_member
	            FROM {db_prefix}kb_articles AS k
	            WHERE kbnid = {int:kbnid}',
		        array(
			        'kbnid' => isset($_GET['cont']) ? (int) $_GET['cont'] : (int) $_GET['aid'],
		        )
	        );
	        $context['knowmini'] = array();
	        while ($row = $smcFunc['db_fetch_assoc']($result))
	        {	  
	            $context['knowmini']['kbid'] = $row['kbnid'];
			    $context['knowmini']['approved'] = $row['approved'];
			    $context['knowmini']['id_cat'] = $row['id_cat'];
			    $context['knowmini']['id_member'] = $row['id_member'];
	        }
	        $smcFunc['db_free_result']($result);
		    cache_put_data('kb_articles_menuinfo'.(isset($_GET['cont']) ? $_GET['cont'] : $_GET['aid']).'',  $context['knowmini'], 3600);
	    }
	}

	//Some menu Vars to use in menu
	$memid = !empty($context['knowmini']['id_member']) ? $context['knowmini']['id_member'] : 0;
    $category = !empty($_REQUEST['cat']) ? $_REQUEST['cat'] : 0;
    $category1 = !empty($context['knowmini']['id_cat']) ? $context['knowmini']['id_cat'] : 0;
	$cont = !empty($context['knowmini']['kbid']) ? $context['knowmini']['kbid'] : 0;
	$approved = !empty($context['knowmini']['approved']) ? 1 : 0;
	$categorynew = $category1 ? $category1 : $category;
	
	if(isset($_REQUEST['area'])){
	    $searchtab = $_REQUEST['area'] == 'searchmain' ? 'searchmain' : 'search';
		$editcattab = $_REQUEST['area'] == 'listcat' ? 'listcat' : 'permcat';
	}
    else{
	    $searchtab = '';
		$editcattab = '';
	}

	//menu areas array
    $menuAreas = array(
		'kb' => array(
			'title' => $txt['kb_manage_main'],
			'areas' => array(
				'main' => array(
					'label' => $txt['knowledgebase'],
					'custom_url' => $scripturl . '?action=kb',
				),
				
				$searchtab => array(
					'label' => $txt['kb_searchforform'],
					'enabled' => allowedTo('search_kb') && !empty($modSettings['kb_esearch']),
					'custom_url' => $scripturl . '?action=kb;area=searchmain',
				),
			),
		),
		'tools' => array(
			'title' => $txt['kb_tool_main'],
			'areas' => array(
				'addknow' => array(
					'label' => $txt['knowledgebasecataddedit1'],
					'enabled' => KBAllowedto($categorynew,'addarticle') || allowedTo('manage_kb'),
					'custom_url' => $scripturl.'?action=kb;area=addknow;cat='.$categorynew,
				),
				'reporta' => array(
					'label' => $txt['kb_reports22'],
					'enabled' => allowedTo('rparticle_kb') && isset($_GET['cont']),
					'custom_url' => $scripturl.'?action=kb;area=reporta;aid='.$cont,
				),
				'unapprove' => array(
					'label' => $txt['kb_alist2'],
					'enabled' => allowedTo('manage_kb') && isset($_GET['cont']) && $approved == 1,
					'custom_url' => $scripturl.'?action=kb;area=article;unapprove;inap='.$cont.';sesc='.$sc.'',
				),
				'approve' => array(
					'label' => $txt['kb_alist1'],
					'enabled' => allowedTo('manage_kb') && isset($_GET['cont']) && $approved == 0,
					'custom_url' => $scripturl.'?action=kb;area=article;approve;aid='.$cont.';sesc='.$sc.'',
				),
				'delete' => array(
					'label' => $txt['knowledgebase_del'],
					'enabled' => KBAllowedto($category1,'delanyarticle') && isset($_GET['cont']) || KBAllowedto($category1,'delarticle') && $memid == $user_info['id'] && isset($_GET['cont']),
					'custom_url' => $scripturl.'?action=kb;area=del;et='.$cont.';sesc='.$sc.'" onclick="return confirm(\''.$txt['knowledgebaseeditedsure'].'\'); ',
				),
				'edit' => array(
					'label' => $txt['knowledgebase_edit'],
					'enabled' => KBAllowedto($category1,'editanyarticle') && isset($_GET['cont']) || KBAllowedto($category1,'editarticle') && $memid == $user_info['id'] && isset($_GET['cont']),
					'custom_url' => $scripturl.'?action=kb;area=edit;aid='.$cont,
				),
				'print' => array(
					'label' => $txt['kb_print'],
					'enabled' => isset($_GET['cont']),
					'custom_url' => $scripturl.'?action=kb;area=article;cont='.$cont.';print',
				),
			),
		),
		'manage' => array(
			'title' => $txt['kb_manage1'],
			'areas' => array(
				'admin' => array(
					'label' => $txt['admin'],
					'enabled' => allowedTo('admin_forum'),
					'custom_url' => $scripturl . '?action=admin;area=kb',
				),
				'manage' => array(
					'label' => $txt['kb_manage1'].' ('.$mcounts.')',
					'enabled' => allowedTo('manage_kb'),
					'custom_url' => $scripturl . '?action=kb;area=manage',
				),
				'catadd' => array(
					'label' => $txt['knowledgebasecatadd'],
					'enabled' => allowedTo('manage_kb'),
					'custom_url' => $scripturl.'?action=kb;area=catadd',
				),
				$editcattab => array(
					'label' => $txt['knowledgebasecataddedit'],
					'enabled' => allowedTo('manage_kb'),
					'custom_url' => $scripturl.'?action=kb;area=listcat',
				),
				'cache' => array(
					'label' => $txt['kb_menu_cache'],
					'enabled' => allowedTo('manage_kb'),
					'custom_url' => $scripturl . '?action=kb;cache_clean',
				),
				'recounta' => array(
					'label' => $txt['kb_menu_ra'],
					'enabled' => allowedTo('manage_kb'),
					'custom_url' => $scripturl . '?action=kb;article_recount',
				),
				'recountc' => array(
					'label' => $txt['kb_menu_rc'],
					'enabled' => allowedTo('manage_kb') && !empty($modSettings['kb_ecom']),
					'custom_url' => $scripturl . '?action=kb;comment_recount',
				),
			),
		),
    );
	
	if(!allowedTo('manage_kb'))
	    unset($menuAreas['manage']);
		
	//Work out the current area
	$current_area = isset($_REQUEST['area']) ? $_REQUEST['area'] : 'main';

	//These are needed when using toggle button
	if(isset($_GET['cat']) && $current_area == 'cats')
	    $context['current_get'] = ';cat='.$_GET['cat'];
	elseif(isset($_GET['cont']) && $current_area == 'article')
	    $context['current_get'] = ';cont='.$_GET['cont'];
	elseif(isset($_GET['aid']) && $current_area == 'reporta')
	    $context['current_get'] = ';aid='.$_GET['aid'];
	elseif(isset($_GET['cat']) && isset($_GET['aid']) && $current_area == 'edit')
	    $context['current_get'] = ';aid='.$_GET['aid'].';cat='.$_GET['cat'];
	elseif(isset($_GET['aid']) && $current_area == 'edit')
	    $context['current_get'] = ';aid='.$_GET['aid'];
	elseif(isset($_GET['cat']) && $current_area == 'addknow')
	    $context['current_get'] = ';cat='.$_GET['cat'];
	elseif(isset($_GET['perm']) && $current_area == 'permcat')
	    $context['current_get'] = ';perm='.$_GET['perm'];
	elseif(isset($_GET['edit']) && $current_area == 'listcat')
	    $context['current_get'] = ';edit='.$_GET['edit'];
	elseif($current_area == 'search' &&  isset($_REQUEST['search']))
		$context['current_get'] = ';q='.(!empty($_SESSION['kb_search_query_encoded']) ? $_SESSION['kb_search_query_encoded'] : '').';sesc='.$sc.'';
	elseif($current_area == 'search' &&  isset($_REQUEST['q']))
		$context['current_get'] = ';q='.(!empty($_SESSION['kb_search_query_encoded']) ? $_SESSION['kb_search_query_encoded'] : '').';sesc='.$sc.'';
	else
	    $context['current_get'] = '';
	
	//Define our menu type
	$menu_type = empty($modSettings['kb_menutype']) ? '_dropdown' : '_sidebar';
	
	//menu options array
	$menuOptions = array(
	    'menu_type' => $menu_type,
		'disable_url_session_check' => true,
		'toggle_redirect_url' => $scripturl.'?action=kb;area='.$current_area.''.$context['current_get'],	
		'toggle_url' => $scripturl.'?action=kb;area='.$current_area.''.$context['current_get'].';togglebar',	
	);
   
    //Member Choice
    if($modSettings['kb_menutype'] == 2)
	    unset($menuOptions['menu_type']);
		
	require_once($sourcedir . '/Subs-Menu.php');
	$kbmod_data = createMenu($menuAreas, $menuOptions);
}
?>