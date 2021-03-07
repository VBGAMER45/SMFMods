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

function KB_profileLink($name, $id = 0){
	global $user_info, $modSettings, $scripturl;
	static $any = null;
	static $own = null;

	if (!empty($modSettings['kb_privmode']) && !allowedTo('admin_forum'))
	    return $name;

	if ($any === null)
	{
		$any = allowedTo('profile_view_any');
		$own = allowedTo('profile_view_own');
	}

	if (empty($id))
		return $name;
	elseif ($any || ($own && $id == $user_info['id']) && empty($modSettings['kb_privmode']))
		return '<a href="' . $scripturl . '?action=profile;u=' . $id . '">' . $name . '</a>';
	else
		return $name;
}

function KB_cleanCache(){
    global $cachedir;

	if (!is_dir($cachedir))
		return;

    $files = scandir($cachedir);
	$failed = true;

    foreach($files as $key => $value){
        if(strpos($value, 'kb_') && $value != 'index.php' && $value != '.htaccess'){

			@unlink($cachedir . '/' . $value);
			$failed = false;
		}
    }

	if($failed == true)//fall back just incase you never know :P
	    clean_cache();

}

function KB_parseTags($toParse, $article = 0, $groupe = 1){
	global $context, $modSettings;

	if ($toParse === '')
		return'';
		
		global $kb_article;
		$kb_article =  $article;
		$kb_groupe = $groupe;

	$highslide = !empty($modSettings['kb_enablehs_img']) ? true : false;

	if ($highslide === true){

		$patterns = array();
		
		$toParse = preg_replace_callback('~&lt;img\s+src=((?:&quot;)?)((?:https?://|ftps?://)\S+?)\\1(?:\s+alt=(&quot;.*?&quot;|\S*?))?(?:\s?/)?&gt;~is', 'KB_fix_imgtag__preg_callback', $toParse);
		
		$toParse = preg_replace_callback('~(\[img.*?\])(.+?)\[/img\]~is', 'KB_fix_imgtag__preg_callback', $toParse);
		
		
/*
	    $patterns += array(
	        '~(\[img.*?\])(.+?)\[/img\]~eis',
	        '~&lt;img\s+src=((?:&quot;)?)((?:https?://|ftps?://)\S+?)\\1(?:\s+alt=(&quot;.*?&quot;|\S*?))?(?:\s?/)?&gt;~eis',
	    );
	    
	    

        foreach($patterns as $pattern){

            if (preg_match_all($pattern, $toParse, $matches)){

		        $replace = array();
			    $replace_pattern = array();

		        foreach ($matches[2] as $match => $imgtag){

			        $replace[$matches[0][$match]] = '<a id="thumb2'.$article.'" href="'.$imgtag.'" class="highslide" onclick="return hs.expand(this, { slideshowGroup: '.$groupe.', thumbnailId: \'thumb2'.$article.'\' } )"><img class="resizeme" id="img_kbparsed'.$article.'" src="'.$imgtag.'" title="'.$imgtag.'" /></a>';

					$replace_pattern = array(
					    $matches[0][$match] => $replace[$matches[0][$match]]
					);

					$toParse = str_replace(array_keys($replace_pattern), array_values($replace_pattern), $toParse);
		        }
	        }
	    }
	    
	    */
	    
	    
	}

	KB_wikilinks($toParse);
	return parse_bbc($toParse);
}

function KB_fix_imgtag__preg_callback($matches)
{
	global $kb_article, $kb_groupe;
	$imgtag = $matches[2];

	
	 	return  '<a id="thumb2'.$kb_article.'" href="'.$matches[2].'" class="highslide" onclick="return hs.expand(this, { slideshowGroup: '.$kb_groupe . ', thumbnailId: \'thumb2'.$article.'\' } )"><img class="resizeme" id="img_kbparsed'.$kb_article.'" src="'.$imgtag.'" title="'.$imgtag.'" /></a>';


}


function KB_wikilinks(&$message)
{
	global $modSettings, $smcFunc, $scripturl;
	static $wikilinks = array();

	$backtrace = debug_backtrace();
	for ($i = 0, $n = count($backtrace); $i < $n; $i++)
		if (isset($backtrace[$i]['function']) && $backtrace[$i]['function'] == 'bbc_to_html')
			return;
	unset($backtrace);

	if (preg_match_all('~\[\[article\:([0-9]+)\]\]~iU', $message, $matches, PREG_SET_ORDER))
	{
		$articlelist = array();
		$articlecount = count($matches);
		for ($i = 0; $i < $articlecount; $i++)
		{
			$id = (int) $matches[$i][1];
			if (!isset($wikilinks[$id]))
				$articlelist[$id] = false;
		}

		if (!empty($articlelist))
		{
			$query = $smcFunc['db_query']('', '
				SELECT kbnid, title
				FROM {db_prefix}kb_articles
				WHERE kbnid IN ({array_int:kbnid})',
				array(
					'kbnid' => array_keys($articlelist),
				)
			);
			while ($row = $smcFunc['db_fetch_assoc']($query))
			{
				$row['kbnid'] = (int) $row['kbnid'];
				$articlelist[$row['kbnid']] = $row['title'];
			}

			$wikilinks += $articlelist;
		}

		$replacements = array();
		for ($i = 0; $i < $articlecount; $i++)
		{
			$id = (int) $matches[$i][1];
			if (!empty($wikilinks[$id]))
				$replacements[$matches[$i][0]] = '<a href="' . $scripturl . '?action=kb;area=article;cont=' . $id . '">' . $wikilinks[$id] . '</a>';
		}

		$message = str_replace(array_keys($replacements), array_values($replacements), $message);
	}
}

function kb_UpdateData($params = null, $data = null)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}'.$params['table'].'
		SET '.$params['set'].'
		'.(!empty($params['where']) ? 'WHERE '.$params['where'] : '').'
		',$data
	);
}

function KB_DeleteData($params = null, $data = null)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
	    DELETE FROM {db_prefix}'.$params['table'] .'
		'.(!empty($params['where']) ? 'WHERE '.$params['where'] : '').'
		',$data
	);
}

function KB_InsertData($data, $values, $indexes)
{
	global $smcFunc;

	if(is_null($values) || is_null($indexes) || is_null($data))
		return false;

	$indexes = isset($indexes) ? array($indexes) : null;
	$values = !is_array($values) ? array($values) : $values;
	$data = !is_array($data) ? array($data) : $data;

	$smcFunc['db_insert']('replace',
		'{db_prefix}'.$data['table'] .'',
		$data['cols'] ,
		$values ,
		$indexes
	);
}

function KB_ListData($params = null, $data = null)
{
	global $smcFunc;

	if(is_null($params))
		$params = array();

	if(is_null($data))
		$data = array();

	$data = !is_array($data) ? array($data) : $data;
	$where = isset($params['where']) ? 'WHERE '.trim($params['where']) : null;
	$left = isset($params['left_join']) ? 'LEFT JOIN '.trim($params['left_join']) : null;

	$request = $smcFunc['db_query']('', '
		SELECT '.$params['call'].'
		FROM {db_prefix}'.$params['table'].'
		' . $left . '
		' . $where . '
		',$data
	);

	$temp = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	return $temp;
}

function KB_ReOrderCats($cat)
{
	global $smcFunc;

	$params = array(
		'table' => 'kb_category',
		'call' => 'roword,id_parent',
		'where' => 'kbid = {int:cat}',
	);

	$data = array(
	     'cat' => $cat,
	);

    $listData = KB_ListData($params, $data);
	$id_parent = $listData['id_parent'];

	$dbresult = $smcFunc['db_query']('', '
	    SELECT
		kbid, roword
	    FROM {db_prefix}kb_category
	    WHERE id_parent = {int:parent} ORDER BY roword ASC',
		array(
		    'parent' => $id_parent,
		)
	);
	if ($smcFunc['db_affected_rows']() != 0)
	{
		$count = 1;
		while($row2 = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$query_params = array(
			    'table' => 'kb_category',
			    'set' => 'roword = {string:roword}',
			    'where' => 'kbid = {int:kbid}',
		    );

		    $query_data = array(
		       'kbid' => $row2['kbid'],
				'roword' => $count,
		    );

		    kb_UpdateData($query_params,$query_data);
			$count++;
		}
	}
	$smcFunc['db_free_result']($dbresult);
}

function KB_log_actions($action, $article_id, $reason)
{
	global $user_info, $modSettings, $smcFunc;

	if (empty($modSettings['kb_disable_log']))
		return;

	$logoption = array(
		'add_article' => 'kb_add_article',
		'add_cat' => 'kb_add_cat',
		'del_article' => 'kb_del_article',
		'edit_cat' => 'kb_edit_cat',
		'edit_article' => 'kb_edit_article',
		'app_com' => 'kb_app_com',
		'app_article' => 'kb_app_article',
		'unapp_article' => 'kb_unapp_article',
		'perm_cat' => 'kb_perm_cat',
		'del_cat' => 'kb_del_cat',
		'del_com' => 'kb_del_com',
		'add_com' => 'kb_add_com',
		'add_report' => 'kb_add_report',
		'del_report' => 'kb_del_report',
	);

	if (empty($logoption[$action]) || empty($modSettings[$logoption[$action]]))
		return;

    $data = array(
		'table' => 'kb_log_actions',
		'cols' => array('action' => 'string','article_id' => 'int','user_id' => 'int','reason' => 'string','time' => 'int','user_ip' => 'string'),
	);

	$values = array($action,$article_id,$user_info['id'],$reason,time(),$user_info['ip']);

	$indexes = array();

    KB_InsertData($data, $values, $indexes);
}

function KB_get_featured(){
    global $context, $user_info, $smcFunc;

	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];

	$context['featured'] = array();

	if (($context['get_featured'] = cache_get_data('kb_get_featured', 3600)) === null)
	{
	    $result = $smcFunc['db_query']('', '
	        SELECT k.kbnid, k.title, k.date, k.id_member, m.real_name, k.approved, p.view, c.name, k.id_cat
	        FROM {db_prefix}kb_articles AS k
		    LEFT JOIN {db_prefix}members AS m ON (k.id_member = m.id_member)
		    LEFT JOIN {db_prefix}kb_category AS c ON (k.id_cat = c.kbid)
		    LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND k.id_cat = p.id_cat)
	        WHERE k.approved = {int:one} AND k.featured = {int:one}
		    ORDER BY RAND()',
		    array(
			    'one' => 1,
                'groupid' => $groupid,
		    )
	    );
	    $context['featured'] = array();
	    while ($row = $smcFunc['db_fetch_assoc']($result))
	    {
            if ($row['view'] == '0')
			    continue;

	        $context['featured'][] = array(
			    'title' => parse_bbc($row['title']),
			    'kbnid' => $row['kbnid'],
			    'id_cat' => $row['id_cat'],
			    'name' => $row['name'],
			    'date' => timeformat($row['date']),
			    'id_member' => $row['id_member'],
			    'real_name' => $row['real_name'],
		    );
	    }
	    $smcFunc['db_free_result']($result);
	    cache_put_data('kb_get_featured', $context['get_featured'], 3600);
	}

	return $context['featured'];
}

function KB_getimages($ida){
    global $smcFunc, $scripturl, $boardurl, $modSettings, $context;

	$modSettings['kb_num_attachment'] = !empty($modSettings['kb_num_attachment']) ? $modSettings['kb_num_attachment'] : '100';

	if (($context['kbimg'] = cache_get_data('kb_article_pics'.$ida.'', 3600)) === null)
	{
	    $result = $smcFunc['db_query']('', '
	        SELECT a.id_article, a.thumbnail, a.filename, a.id_file
	        FROM {db_prefix}kb_attachments AS a
	        WHERE a.id_article = {int:kbnid}
			ORDER BY a.id_file DESC',
		    array(
			    'kbnid' => (int) $ida,
			    'max_attach' => $modSettings['kb_num_attachment'],
		    )
	    );

	    $context['kbimg'] = array();
	    while ($row = $smcFunc['db_fetch_assoc']($result))
	    {
	        $context['kbimg'][] = array(
		        'id_article' => $row['id_article'],
			    'thumbnail' => $row['thumbnail'],
			    'filename' => $row['filename'],
				'id_file' => $row['id_file'],
		    );
	    }
		$smcFunc['db_free_result']($result);
		cache_put_data('kb_article_pics'.$ida.'',  $context['kbimg'], 3600);
	}
	return $context['kbimg'];
}

function KB_getcomments($ida){
    global $smcFunc, $scripturl, $boardurl, $context;

    if (($listData = cache_get_data('kb_article_pagecomments'.$_REQUEST['start'].''.$ida.'', 3600)) === null)
	{
	    $params = array(
		    'table' => 'kb_comments',
		    'call' => 'COUNT(*) AS total',
		    'where' => 'id_article = {int:kbnid} AND approved = 1',
	    );

	    $data = array(
	        'kbnid' => (int) $ida,
	    );

        $listData = KB_ListData($params, $data);

	     cache_put_data('kb_article_pagecomments'.$_REQUEST['start'].''.$ida.'',  $listData, 3600);
    }

	$tot = $listData['total'];

	// Now create the page index.
	$context['page_index'] = constructPageIndex($scripturl . '?action=kb;area=article;cont='.$ida.';sort=' . $_REQUEST['start'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $tot, 10);
	$context['start'] = (int) $_REQUEST['start'];

	if (($context['kbcom'] = cache_get_data('kb_article_comments'.$context['start'].''.$ida.'', 3600)) === null)
	{
	    $result = $smcFunc['db_query']('', '
	        SELECT c.id_article, c.id, c.id_member, c.date, c.comment, m.real_name
	        FROM {db_prefix}kb_comments AS c
		    LEFT JOIN {db_prefix}members AS m ON (c.id_member = m.id_member)
		    LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = m.id_member)
	        WHERE c.id_article = {int:kbnid} AND c.approved = 1
		    ORDER BY c.id DESC
		    LIMIT {int:start}, 10',
		    array(
			    'kbnid' => (int) $ida,
			    'start' => $context['start'],
		    )
	    );

	    $context['kbcom'] = array();
	    while ($row = $smcFunc['db_fetch_assoc']($result))
	    {
	        $context['kbcom'][] = array(
		        'id_article' => $row['id_article'],
			    'comment' => KB_parseTags($row['comment'], $row['id'], 1),
			    'date' => timeformat($row['date']),
			    'id_member' => $row['id_member'],
			    'real_name' => $row['real_name'],
			    'id' => $row['id'],
		    );
	    }
		$smcFunc['db_free_result']($result);
	   cache_put_data('kb_article_comments'.$context['start'].''.$ida.'',  $context['kbcom'], 3600);
    }
	return $context['kbcom'];
}

function KB_PrettyCategory()
{
	global $context;

	$finalArray = array();

	$parentList = array(0);
	$newParentList = array();
	$spacer = 0;

	for ($g = 0;$g < count($parentList); $g++)
	{
		$tmpLevelArray = array();
		for ($i = 0;$i < count($context['knowcat']);$i++)
		{
			if ($context['knowcat'][$i]['id_parent'] == $parentList[$g])
			{
				$newParentList[] = $context['knowcat'][$i]['kbid'];
				$newParentList = array_unique($newParentList);
				$context['knowcat'][$i]['name'] = str_repeat('- ', $spacer) .$context['knowcat'][$i]['name'];
				$tmpLevelArray[] = $context['knowcat'][$i];
			}
		}

		if ($parentList[$g] == 0)
		{
			$finalArray = $tmpLevelArray;
		}
		else
		{
			$tmpArray2 = array();
			for($j = 0;$j<count($finalArray);$j++)
			{
				$tmpArray2[] = $finalArray[$j];
				if ($finalArray[$j]['kbid'] == $parentList[$g])
				{
					for ($z = 0;$z < count($tmpLevelArray);$z++)
					{
						$tmpArray2[] = $tmpLevelArray[$z];
					}
				}
			}

			$finalArray = $tmpArray2;
		}
		$tmpLevelArray = array();

		if ($g == (count($parentList) -1) && !empty($newParentList))
		{
			$parentList = array();
			$parentList = $newParentList;
			$newParentList = array();
			$g=-1;
			$spacer++;
		}
		else if ($g == (count($parentList) -1) && empty($newParentList)){}
	}

	$context['knowcat'] = array();
	$context['knowcat'] = $finalArray;
}

function KB_cattotalbyid($cat){
    global $total, $modSettings, $smcFunc;

	$total = 0;
	$total += kb_Totalcatid($cat);

	if(!empty($modSettings['kb_countsub'])){

	    $params = array(
		    'table' => 'kb_category',
		    'call' => 'SUM(count) AS total',
		    'where' => 'id_parent = {int:cat}',
	    );

	    $data = array(
	        'cat' => $cat,
	    );

        $listData = KB_ListData($params, $data);

		if ($listData['total'] != '')
			$total += $listData['total'];
	}
	return $total;
}

function kb_Totalcatid($cat)
{
	global $smcFunc;

	$params = array(
	    'table' => 'kb_category',
		'call' => 'count',
		'where' => 'kbid = {int:cat}',
	);

    $data = array(
	    'cat' => $cat,
	);

    $listData = KB_ListData($params, $data);

	return $listData['count'];
}

function KB_approvecomcounts(){
    global $total_approvecom, $smcFunc;

	if (($total_approvecom = cache_get_data('kb_totalkb_comments', 3600)) === null)
	{
	    $params = array(
		    'table' => 'kb_comments',
		    'call' => 'COUNT(*) AS total',
		    'where' => 'approved = 0',
	    );

	    $data = array();

        $data = KB_ListData($params, $data);
        $total_approvecom = $data['total'];

		cache_put_data('kb_totalkb_comments',  $total_approvecom, 3600);
    }
}

function KB_approvecounts(){
    global $total_approve, $smcFunc;

	if (($total_approve = cache_get_data('kb_totalkb_approve', 3600)) === null)
	{
	    $params = array(
		    'table' => 'kb_articles',
		    'call' => 'COUNT(*) AS total',
		    'where' => 'approved = 0',
	    );

	    $data = array();

        $data = KB_ListData($params, $data);
        $total_approve = $data['total'];
	    cache_put_data('kb_totalkb_approve',  $total_approve, 3600);
    }
}

function KB_reportcounts(){
    global $total_report, $smcFunc;

	if (($total_report = cache_get_data('kb_totalkb_report', 3600)) === null)
	{
	    $params = array(
		    'table' => 'kb_reports',
		    'call' => 'COUNT(*) AS total',
	    );

	    $data = array();

        $data = KB_ListData($params, $data);
        $total_report = $data['total'];
	    cache_put_data('kb_totalkb_report',  $total_report, 3600);
    }
}

function KB_wysig_descript()
{

   	global $sourcedir;

	require_once($sourcedir . '/Subs-Editor.php');

	if (!empty($_REQUEST['description_mode']) && isset($_REQUEST['description']))
    {
        $_REQUEST['description'] = html_to_bbc($_REQUEST['description']);
        $_REQUEST['description'] = un_htmlspecialchars($_REQUEST['description']);
        $_POST['description'] = $_REQUEST['description'];
    }
}

function KB_doheaders(){
    global $settings, $modSettings, $context;

	if(file_exists($settings['default_theme_url'] . '/hs4smf/highslide.js'))
	    $hs_js = $settings['default_theme_url'] . '/hs4smf/highslide.js';
	else
	   $hs_js = $settings['default_theme_url'] .'/scripts/sa_kb/hs/highslide-full.js';

	$max_height = !empty($modSettings['kb_img_max_height']) ? $modSettings['kb_img_max_height'] : 150;
    $max_width = !empty($modSettings['kb_img_max_width']) ? $modSettings['kb_img_max_width'] : 150;

	$context['html_headers'] .= '
	<script type="text/javascript">
        !window.jQuery && document.write(unescape(\'%3Cscript src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"%3E%3C/script%3E%3Cscript src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"%3E%3C/script%3E\'));
    </script>
	<script type="text/javascript" src="'.$settings['default_theme_url'] .'/scripts/sa_kb/jquery.ae.image.resize.min.js"></script>
	<script type="text/javascript" src="'.$settings['default_theme_url'] .'/scripts/sa_kb/jquery.validate.min.js"></script>

	<script type="text/javascript"><!--
        function kbsearch_showhide(layer_ref) {
			jQuery.noConflict()(function($){

				$(document.getElementById(layer_ref)).slideToggle(\'slow\');
			});
		}

	    jQuery.noConflict()(function($){
		    $(function() {
                $( ".resizeme" ).aeImageResize({ height: '.$max_height.', width: '.$max_width.' });
            });
		});

		jQuery.noConflict()(function($){
		    $(function() {
                $( ".resizeav" ).aeImageResize({ height: 75, width: 75 });
            });
		});

		//-->
	</script>';

	$kb_area = !empty($_REQUEST['area']) ? $_REQUEST['area'] : 'main';

	switch ($kb_area) {

	    case 'addknow':

		$context['html_headers'] .='
	    <script type="text/javascript">
	        jQuery.noConflict()(function($){
	            $(document).ready(function(){

					$("#myarform").validate({

			            submitHandler: function(form) {

                            var submit = this.submitButton;
					        //alert(" "+submit.id);

					        if(submit.id == "previewbutton"){
			                   $(\'#ajax_in_progress\').show();
							   $.post(\'index.php?action=kb;area=addknow;save;cat='.(!empty($_GET['cat']) ? $_GET['cat'] : '0').';preview\', $("#myarform").serialize(), function(data) {

						            var results = $(data).find(\'#results\');
						            $("#results").empty().append(results);
						            $(\'#ajax_in_progress\').hide();
						            var new_position = $(\'#results\').offset();
                                    window.scrollTo(new_position.left,new_position.top);
                                });

					            return false;
					        }
							else{
					            var results = $(data).find(\'#results\');
					            $("#results").empty().append(results);
					        }
			            }
		            });
	            });
	        });
	    </script>';

		break;

		case 'edit';

		$context['html_headers'] .='
	    <script type="text/javascript">
	        jQuery.noConflict()(function($){

				$(document).ready(function(){

					$("#kbeditform").validate({

			            submitHandler: function(form) {

                            var submit = this.submitButton;
					        //alert(" "+submit.id);

					        if(submit.id == "previewbutton"){
					        $(\'#ajax_in_progress\').show();
			                $.post(\'index.php?action=kb;area=edit;save;aid='.$_GET['aid'].';preview\', $("#kbeditform").serialize(), function(data) {

						        var results = $(data).find(\'#results\');
						        $("#results").empty().append(results);
								$(\'#ajax_in_progress\').hide();
								var new_position = $(\'#results\').offset();
                                window.scrollTo(new_position.left,new_position.top);
                            });

					        return false;
					        }else{
					            var results = $(data).find(\'#results\');
					            $("#results").empty().append(results);
					        }
			            }
		            });
	            });
	        });
	    </script>';

	    break;

	    case 'article';

		#Highslide JS License:
		#Highslide JS is licensed by the MIT-license.
		$context['html_headers'] .='

		<script type="text/javascript" src="'.$hs_js.'"></script>
        <link rel="stylesheet" href="'. $settings['default_theme_url'] .'/scripts/sa_kb/hs/highslide.css" type="text/css" media="screen" />

	    <script type="text/javascript">
	        hs.graphicsDir = \''.$settings['default_theme_url'] .'/scripts/sa_kb/hs/graphics/\';
	        hs.align = \'center\';
	        hs.transitions = [\'expand\', \'crossfade\'];
	        hs.outlineType = \'rounded-white\';
	        hs.fadeInOut = true;
	        hs.dimmingOpacity = 0.75;

	        // define the restraining box
	        hs.useBox = true;
	        hs.width = 640;
	        hs.height = 480;

	        // Add the controlbar
	        hs.addSlideshow({
		        //slideshowGroup: \'group1\',
		        interval: 5000,
		        repeat: false,
		        useControls: true,
		        fixedControls: \'fit\',
		        overlayOptions: {
			        opacity: 1,
			        position: \'bottom center\',
			        hideOnMouseOut: true
		        }
	        });
        </script>';
		#Highslide end
		$_GET['cont'] = !empty($_GET['cont']) ? $_GET['cont'] : '';
		$context['html_headers'] .='
	    <script type="text/javascript">
	        jQuery.noConflict()(function($){
	            $(document).ready(function(){

		            $("#mykbform").validate({
			            debug: false,
			        rules: {
				        description: "required",
			        },
			        messages: {
				        description: "",
			        },

			        submitHandler: function(form) {
					    $(\'#ajax_in_progress\').show();
			            $.post(\'index.php?action=kb;area=kb;area=article;comment;arid='.$_GET['cont'].';cont='.$_GET['cont'].'\', $("#mykbform").serialize(), function(data) {
                            var results = $(data).find(\'#results\');
                            $("#results").empty().append(results);
						    $(\'#ajax_in_progress\').hide();
							$(\'#commentkb\').hide();
							$(\'#com_done\').fadeIn(\'slow\').delay(5000).fadeOut(\'slow\');
							mykbform.reset();
                        });
			        }
		        });
	        });
	    });
	    </script>';

	    break;
	}
}

function KB_dojump(){
    global $smcFunc, $txt;

    if (isset($_REQUEST['jump'])){

		$_POST['jump'] = (int) $_POST['jump'];

	    $params = array(
		    'table' => 'kb_articles',
		    'call' => 'kbnid',
			'where' => 'kbnid = {int:jump}',
	    );

	    $data = array('jump' => $_POST['jump']);

        $data = KB_ListData($params, $data);

        if($data['kbnid']){
	       redirectexit('action=kb;area=article;cont='.$data['kbnid'].'');
		}
		else{
		   fatal_error(''.$txt['kb_pinfi7'].' <strong>'.$_POST['jump'].'</strong> '.$txt['kb_jumpgo1'].'',false);
		}
	}
}

function KB_Stars_Precent($percent)
{
	global $settings, $txt;

	if ($percent == 0)
		return $txt['kb_notrated'];
	elseif ($percent <= 20)
		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" title="'.$percent.'" alt="'.$percent.'" border="0" />', 1);
	elseif ($percent <= 40)
		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" title="'.$percent.'" alt="'.$percent.'" border="0" />', 2);
	elseif ($percent <= 60)
		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" title="'.$percent.'" alt="'.$percent.'" border="0" />', 3);
	elseif ($percent <= 80)
		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" title="'.$percent.'" alt="'.$percent.'" border="0" />', 4);
	elseif ($percent <= 100)
		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" title="'.$percent.'" alt="'.$percent.'" border="0" />', 5);
}

function KB_sendpm($to,$title,$message) {
    global $txt, $modSettings, $sourcedir;

	require_once($sourcedir.'/Subs-Post.php');

    //Set up the pm
    $pmfrom = array(
        'id' => 0,
        'name' => $txt['knowledgebase'],
        'username' => $txt['knowledgebase'],
    );

    $pmto = array(
        'to' => array($to),
        'bcc' => array()
    );

	if(empty($modSettings['kb_privmes'])){
	    sendpm($pmto, $title, $message , 0, $pmfrom);
	}
}


function KBrecountcomments(){
	global $smcFunc;

	$counts = array();

	$result = $smcFunc['db_query']('','
		SELECT id_article
		FROM {db_prefix}kb_comments',
		array());

	while ($row = $smcFunc['db_fetch_assoc']($result)){
		if ($row['id_article'] != 0){
			// Add one to the category's count. If it's not defined yet, set it to 1
			$counts[$row['id_article']] = (isset($counts[$row['id_article']]) ? $counts[$row['id_article']] + 1 : 1);
	    }
	}
	$smcFunc['db_free_result']($result);

	foreach ($counts as $key => $value){

		$query_params = array(
			'table' => 'kb_articles',
			'set' => 'comments = {int:value}',
			'where' => 'kbnid = {int:key}',
		);

		$query_data = array(
		    'value' => $value,
			'key' => $key,
		);

		kb_UpdateData($query_params,$query_data);
	}
}

function KBrecountItems(){
	global $smcFunc;

	$counts = array();

	$result = $smcFunc['db_query']('','
		SELECT id_cat
		FROM {db_prefix}kb_articles',
		array());

	while ($row = $smcFunc['db_fetch_assoc']($result)){
		if ($row['id_cat'] != 0){
			// Add one to the category's count. If it's not defined yet, set it to 1
			$counts[$row['id_cat']] = (isset($counts[$row['id_cat']]) ? $counts[$row['id_cat']] + 1 : 1);
	    }
	}
	$smcFunc['db_free_result']($result);

	foreach ($counts as $key => $value){

		$query_params = array(
			'table' => 'kb_category',
			'set' => 'count = {int:value}',
			'where' => 'kbid = {int:key}',
		);

		$query_data = array(
		    'value' => $value,
			'key' => $key,
		);

		kb_UpdateData($query_params,$query_data);
	}

	$result = $smcFunc['db_query']('','
		SELECT c.kbid, k.id_cat
		FROM {db_prefix}kb_category AS c
		LEFT JOIN {db_prefix}kb_articles AS k ON (k.id_cat = c.kbid)',
		array());

	while ($row = $smcFunc['db_fetch_assoc']($result)){

		if (empty($row['id_cat'])){

			$query_params = array(
			    'table' => 'kb_category',
			    'set' => 'count = {int:value}',
			    'where' => 'kbid = {int:key}',
		    );

		    $query_data = array(
		        'value' => 0,
			    'key' => $row['kbid'],
		    );

		    kb_UpdateData($query_params,$query_data);
		}
	}
}
?>