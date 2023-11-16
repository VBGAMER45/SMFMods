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
	
global $context, $settings, $scripturl, $txt, $db_prefix, $ID_MEMBER;
global $user_info, $modSettings;
global $smcFunc, $boarddir;

$num_recent = 3; //limit the consult sql - change if you want
//Load Recent Posts (SSI.php)
require_once($boarddir . '/SSI.php');
$posts = array();

//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
if(!empty($ultimateportalSettings['up_reduce_site_overload']))
{
	if((cache_get_data('bk_top_topics', 1800)) === NULL)
	{
		$posts = ssi_recentTopics($num_recent,null,null,'array');
		cache_put_data('bk_top_topics', $posts, 1800);		
	}else{
		$posts = cache_get_data('bk_top_topics', 1800);
	}
}else{
	$posts = ssi_recentTopics($num_recent,null,null,'array');
}

echo '
	<table width="100%" border="0" style="font-size:10px;font-weight:bold;">
		';
	foreach ($posts as $post) {
	$longTitle = strlen($post['subject']);
	if ($longTitle > 30)
    {
          $title = substr($post['subject'], 0, 30) . '...';
    }else{
          $title = $post['subject'];   
    }
		echo '<tr><td><img src="', $settings['default_images_url'],'/ultimate-portal/icons/arrowup.png" alt="" /></td>	
				<td>
					<a href="'. $post['href']. '" title="'.$post['subject'].'">'. $title . '</a>
				</td>
			</tr>';
	}
		
echo '
	</table>';
?>