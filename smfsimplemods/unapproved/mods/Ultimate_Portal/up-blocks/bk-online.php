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
	
global $scripturl, $db_prefix, $user_info, $txt;
global $modSettings;
global $smcFunc, $boarddir;

require_once($boarddir . '/SSI.php');
$show_buddies = !empty($user_info['buddies']);
$return = array();

//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
if(!empty($ultimateportalSettings['up_reduce_site_overload']))
{
	if((cache_get_data('bk_online', 120)) === NULL)
	{
		$return = ssi_logOnline('array');
		cache_put_data('bk_online', $return, 120);		
	}else{
		$return = cache_get_data('bk_online', 120);
	}
}else{
	$return = ssi_logOnline('array');
}

//Start Table
echo 
			'<table  width="100%" border="0" cellspacing="1" cellpadding="2" style="margin-top:3px;">';
				
echo '
				<tr>
					<td align="left">
						<img src="', $settings['default_images_url'],'/ultimate-portal/download/menu.png" alt="" />&nbsp;'. $return['total_users'] . '&nbsp;<strong>' .$txt['ultport_users_online'] .'</strong>
					</td>
				</tr>' ;

echo '
                <tr>
					<td align="left">				
						<img src="', $settings['default_images_url'],'/ultimate-portal/download/menu.png" alt="" />&nbsp;' . $return['num_users'] . '&nbsp;'. ($return['num_users'] == 1 ? $txt['user'] : $txt['users']) .'
					</td>
				</tr>
				<tr>
					<td align="left">
						<img src="', $settings['default_images_url'],'/ultimate-portal/download/menu.png" alt="" />&nbsp;'. $return['guests'] . '&nbsp;'. ($return['guests'] == 1 ? $txt['guest'] : $txt['guests']) . '
					</td>
				</tr>';	

// Hidden users, or buddies?
if ($return['hidden'] > 0 || $show_buddies)
	echo '
				<tr>
					<td align="left">					
						<img src="', $settings['default_images_url'],'/ultimate-portal/download/menu.png" alt="" />&nbsp;' . ($show_buddies ? ($return['buddies'] . '&nbsp;' . ($return['buddies'] == 1 ? $txt['buddy'] : $txt['buddies'])) : '') . ($show_buddies && $return['hidden'] ? ', ' : '') . (!$return['hidden'] ? '' : $return['hidden'] . ' ' . $txt['hidden']) . '
					</td>
				</tr>';

echo 
				'<tr>
					<td align="center"><hr /><strong style="border-bottom: 1px solid #aaa;">', $txt['users_online_bk'],'</strong><br />';					
$i = 1;
foreach ($return['users'] as $user)
{
	echo ($user['hidden'] ? '<i>' . $user['link'] . '</i>' : $user['link']) . ($user['is_last'] ? '' : ',&nbsp;');
	if($i==3)
	{
		echo '<br />';
		$i = 0;
	}
	++$i;	
}
echo '
					</td>
				</tr>';
				
//Close Table
echo 
			'	<tr>
					<td align="center">
						<img width="13" height="13" alt="'.$txt['ultport_users_who'].'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/who.png" />&nbsp;<a href="' . $scripturl . '?action=who"><strong>' . $txt['ultport_users_who'] . '</strong></a>
					</td>
				</tr>	
			</table>';
?>