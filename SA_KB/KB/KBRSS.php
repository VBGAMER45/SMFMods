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
	
function KB_rss(){
    global $user_info, $scripturl, $txt, $smcFunc, $modSettings, $context;
	
	$xml_format = isset($_GET['type']) && $_GET['type'] == 'rss2' ? '2.0' : '0.92';
	$context['kb_rss_body'] = '';

	if (empty($modSettings['kb_enablersscat']))
		redirectexit('action=kb');

	if (empty($_GET['cat']))
		redirectexit('action=kb');

	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();	
	
	header('Content-Type: application/rss+xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));
	
	echo '<?xml version="1.0" encoding="', $context['character_set'], '"?' . '>';

	if ($context['user']['is_guest'])
		$groupid = -1;
	else
		$groupid =  $user_info['groups'][0];
		
	$modSettings['kb_xml_maxlen'] = 255;
	
	$request = $smcFunc['db_query']('', '
		SELECT name, description
		FROM {db_prefix}kb_category
		WHERE kbid = {int:cat} ',
		array(
		    'cat' => (int) $_GET['cat'], 	
		));
				
	list ($catname,$catdesc) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
	
	$context['kb_rss_body'] .= '
<rss version="'. $xml_format. '" xml:lang="'. strtr($txt['lang_locale'], '_', '-'). '">
	<channel>
		<title>'.$catname.'</title>
		<link>'.$scripturl.'?action=kb;area=cats;cat='.$_GET['cat'].'</link>
		<description><![CDATA['.$catdesc.']]></description>';
		
	$result = $smcFunc['db_query']('', '
	    SELECT k.kbnid, k.title, k.date, k.id_member, k.content, m.real_name, k.approved, p.view, c.name, k.id_cat
	    FROM {db_prefix}kb_articles AS k
		LEFT JOIN {db_prefix}members AS m ON (k.id_member = m.id_member)
		LEFT JOIN {db_prefix}kb_category AS c ON (k.id_cat = c.kbid)
		LEFT JOIN {db_prefix}kb_catperm AS p ON (p.id_group = {int:groupid} AND k.id_cat = p.id_cat) 
	    WHERE k.approved = {int:one} AND k.id_cat = {int:cat}
		ORDER BY k.date DESC',
		array(
			'one' => 1,
            'groupid' => $groupid,	
            'cat' => (int) $_GET['cat'], 			
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{	  
        if ($row['view'] == '0')
			continue;
        
		if (!empty($modSettings['kb_xml_maxlen']) && $smcFunc['strlen'](str_replace('<br />', "\n", $row['content'])) > $modSettings['kb_xml_maxlen'])
			$row['content'] = strtr($smcFunc['substr'](str_replace('<br />', "\n", $row['content']), 0, $modSettings['kb_xml_maxlen'] - 3), array("\n" => '<br />')) . '...';
		
	$context['kb_rss_body'] .='
		<item>
			<title>'.$row['title'].'</title>
			<link>'.$scripturl.'?action=kb;area=article;cont='.$row['kbnid'].'</link>
			<description><![CDATA['.parse_bbc($row['content']).']]></description>
			<author>'.$row['real_name'].'</author>
			<category><![CDATA['.$row['name'].']]></category>
			<comments>'.$scripturl.'?action=kb;area=article;cont='.$row['kbnid'].'</comments>
			<pubDate>'.timeformat($row['date']).'</pubDate>
		</item>';
	}
    $smcFunc['db_free_result']($result);
	
	$context['kb_rss_body'] .='
	</channel>
</rss>';
    
	echo trim($context['kb_rss_body'],'');
    
	obExit(false);
	die();//do we really need to go on?
}
?>	