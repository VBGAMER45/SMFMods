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
	
global $context, $scripturl, $txt, $settings;
global $ultimateportalSettings;
global $user_info, $memberContext;
global $db_prefix, $sourcedir;
global $smcFunc;

// Load Language
if (loadlanguage('UPNews') == false)
	loadLanguage('UPNews','english');

//Load the News
if(!empty($ultimateportalSettings['up_reduce_site_overload']))
{
	if((cache_get_data('bk_news', 1800)) === NULL)
	{
		LoadBlockNews();
		//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
		cache_put_data('bk_news', $context['news'], 1800);		
		cache_put_data('page_index_news', $context['page_index'], 1800);		
	}else{
		$context['news'] = cache_get_data('bk_news', 1800);
		$context['page_index'] = cache_get_data('page_index_news', 1800);
	}
}else{
	LoadBlockNews();
}

echo "
	<script type=\"text/javascript\">
		function makesurelink() {
			if (confirm('".$txt['ultport_delete_news_confirmation']."')) {
				return true;
			} else {
				return false;
			}
		}
	</script>";

foreach($context['news'] as $news)
{	
	echo '<div class="description"><img style="vertical-align: middle;" alt="'. stripslashes($news['title_cat']) .'" src="'. $news['icon'] .'" width="20" height="20" />
					&nbsp;<strong><a href="'. $scripturl .'?action=news;sa=show-cat;id='. $news['id_cat'] .'">'. stripslashes($news['title_cat']) .'</a></strong>
					 - <strong>'. $news['title'] .'</strong> <span style="text-align:right;">'. $news['view'] .'</span>
				 <br />'. $news['added-news'] .'</div>					
                 <div>'. $news['body'] .'</div><br />';
							
	if (!empty($user_info['up-modules-permissions']['news_moderate']) || $user_info['is_admin'])
	{			
		echo '<div style="border: 1px dashed #aaa;padding: 3px;">'. $news['edit'] .'
					'. $news['delete'] .'</div>';
	}								

}
	//Page Index
echo '
			<div style="text-align:center">
				<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] . '
			</div>';
	//End Page Index

?>