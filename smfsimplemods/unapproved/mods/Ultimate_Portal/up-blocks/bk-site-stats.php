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

global $context, $db_prefix, $txt, $scripturl, $modSettings, $settings, $boardurl;
global $sourcedir;
global $smcFunc, $boarddir;

//Call SSI.php
require_once($boarddir . '/SSI.php');

//Total Stats 
$totals = array();

//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
if(!empty($ultimateportalSettings['up_reduce_site_overload']))
{
	if((cache_get_data('bk_site_stats_totals', 1800)) === NULL)
	{
		$totals = ssi_boardStats('array');
		cache_put_data('bk_site_stats_totals', $totals, 1800);		
	}else{
		$totals = cache_get_data('bk_site_stats_totals', 1800);
	}
}else{
	$totals = ssi_boardStats('array');
}

//Last Member from SSI.php
$latestMember = array();
//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
if(!empty($ultimateportalSettings['up_reduce_site_overload']))
{
	if((cache_get_data('bk_site_stats_lastmember', 1800)) === NULL)
	{
		$latestMember = ssi_latestMember('array'); //return $context['common_stats']['latest_member'] 
		cache_put_data('bk_site_stats_lastmember', $latestMember, 1800);		
	}else{
		$latestMember = cache_get_data('bk_site_stats_lastmember', 1800);
	}
}else{
	$latestMember = ssi_latestMember('array'); //return $context['common_stats']['latest_member'] 
}

echo '
	<table border="0" width="100%" style="margin-top:3px;">
		<tr>
			<td align="left" width="100%">	
				<img style="float:left" width="16" height="16" alt="'.$txt['total_members'].'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/user.png" />&nbsp;&nbsp;'. $txt['total_members'] . ': <strong><a href="' . $scripturl . '?action=mlist">'. comma_format($totals['members']) . '</a></strong>
			</td>
		</tr>
		<tr>
			<td align="left" width="100%">		
				<img style="float:left" width="16" height="16" alt="'.$txt['total_topics'].'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/total-topics.png" />&nbsp;&nbsp;'. $txt['total_topics'] . ': <strong>'. comma_format($totals['topics']) . '</strong>
			</td>
		</tr>	
		<tr>
			<td align="left" width="100%">				
				<img style="float:left" width="16" height="16" alt="'.$txt['total_posts'].'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/total-messages.png" />&nbsp;&nbsp;'. $txt['total_posts'] . ': <strong>'. comma_format($totals['posts']) . '</strong>
			</td>
		</tr>	
		<tr>
			<td align="left" width="100%">		
				<img style="float:left" width="16" height="16" alt="'.$txt['total_cats'].'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/categories.png" />&nbsp;&nbsp;'. $txt['total_cats'] . ': <strong>'. comma_format($totals['categories']) . '</strong>
			</td>
		</tr>	
		<tr>
			<td align="left" width="100%">		
				<img style="float:left" width="16" height="16" alt="'.$txt['total_boards'].'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/board.png" />&nbsp;&nbsp;'. $txt['total_boards'] . ': <strong>'. comma_format($totals['boards']) . '</strong>
			</td>
		</tr>
		<tr>
			<td align="left" width="100%">		
				<img style="float:left" width="16" height="16" alt="'.$txt['newest_member'].'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/bk-personal-menu.png" />&nbsp;&nbsp;'. $txt['ultport_lastest_member'] . ':&nbsp;<strong>'. $latestMember['link'] .'</strong>
			</td>
		</tr>	
        <tr>
			<td align="left" width="100%">		
				<img style="float:left" width="16" height="16" alt="'.$txt['newest_member'].'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/bk-site-stats.png" />&nbsp;&nbsp;<a href="' . $scripturl . '?action=stats">'. $txt['more_stats'] . '</a>
			</td>
		</tr>			
	</table>';
?>