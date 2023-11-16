<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');
	
global $scripturl, $db_prefix, $txt, $settings, $modSettings, $context;
global $func, $sourcedir, $memberContext;
global $ultimateportalSettings, $sc, $user_info;
global $smcFunc, $boarddir;
global $boarddir;

$limit = !empty($ultimateportalSettings['board_news_limit']) ? (int) $ultimateportalSettings['board_news_limit'] : 10;
$length = !empty($ultimateportalSettings['board_news_lenght']) ? (int) $ultimateportalSettings['board_news_lenght'] : ''; 
$boards = array();
$boards = !empty($ultimateportalSettings['board_news_view']) ? explode(',', $ultimateportalSettings['board_news_view']) : null;
$bnews = array();

if(!empty($ultimateportalSettings['up_reduce_site_overload']))
{
	if((cache_get_data('bk_boards_news', 1800)) === NULL)
	{
		$bnews = upSSI_BoardNews($limit, null, $boards, $length);
		//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
		cache_put_data('bk_boards_news', $bnews, 1800);		
	}else{
		$bnews = cache_get_data('bk_boards_news', 1800);
	}
}else{
	$bnews = upSSI_BoardNews($limit, null, $boards, $length);
}

loadLanguage('Stats');

foreach ($bnews as $news)
{
	//load the member information
	if(!empty($news['poster']['id']))
	{
		loadMemberData($news['poster']['id']);
		loadMemberContext($news['poster']['id']);
	}
	echo '<span class="clear upperframe"><span></span></span>
				 <div class="roundframe"><strong>'. $txt['topic'] .':</strong> '. $news['link'] .'<br />
					<strong>'.$txt['post_by'] .': '. $news['poster']['link'] .'</strong>
					</div><span class="lowerframe"><span></span></span>
					<div align="center">'. $news['time'] .'</div>
						
					<div class="post" style="padding: 2ex 0;">
						'. $news['preview'] .'
					</div>
							
					<div align="center" class="description"><a href="' . $scripturl . '?topic=' . $news['topic'] . '.0">', $txt['readmoreboardnews'] ,' (' . $news['replies'] . ' ' . ($news['replies'] == 1 ? $txt['ssi_comment'] : $txt['ssi_comments']) . ')</a>'. (empty($news['locked']) ? ' | '. $news['comment_link'] : '') . '
				</div>';
}

//Page Index
echo '
		<div style="text-align:center">
			<strong>'. $txt['pages'] .':</strong> '. !empty($context['page_index']) . '
		</div>';
//End Page Index		
			
?>