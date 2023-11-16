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
	
$topNumber = 6;

global $txt, $db_prefix, $scripturl, $user_info;
global $smcFunc, $boarddir;
global $memberContext;

//Load Top Poster (ssi_topPoster from SSI.php)
require_once($boarddir . '/SSI.php');
$return = array();

//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
if(!empty($ultimateportalSettings['up_reduce_site_overload']))
{
	if((cache_get_data('bk_top_poster', 1800)) === NULL)
	{
		$return = ssi_topPoster($topNumber,'array');
		cache_put_data('bk_top_poster', $return, 1800);		
	}else{
		$return = cache_get_data('bk_top_poster', 1800);
	}
}else{
	$return = ssi_topPoster($topNumber,'array');
}

// Make a quick array to list the links in.
echo '
	<table  style="border-spacing:5px;width:100%;" border="0" cellspacing="1" cellpadding="3">
		';
$count=0;
foreach ($return as $member)
{
	//load member data
	loadMemberData($member['id']);
	loadMemberContext($member['id']);
	//end load member data...
	$count++;
	echo '
		<tr>
		<td align="left">';
		if (!empty($memberContext[$member['id']]['avatar']['href'])) {
				echo'<img src="'. $memberContext[$member['id']]['avatar']['href'] . '" 
				style="-moz-box-shadow: 0px 0px 5px #444;
                       -webkit-box-shadow: 0px 0px 5px #444;
                       box-shadow: 0px 0px 5px #444;" width="50px;" alt="" />';}
			echo'</td>
			<td width="100%" valign="middle">
				', $count == 1 ? '<img src="'.$settings['default_images_url'].'/ultimate-portal/icons/1.gif" width="22px" alt="" />' : '',' 
				', $count == 2 ? '<img src="'.$settings['default_images_url'].'/ultimate-portal/icons/2.gif" width="22px" alt="" />' : '','
				', $count == 3 ? '<img src="'.$settings['default_images_url'].'/ultimate-portal/icons/3.gif" width="22px" alt="" />' : '','<strong>'. $member['link'] . '</strong> <br /><strong>', $txt['posts'],':</strong>
				'. $member['posts'] .'
			</td>
		</tr>
		
	';
}

echo '
	</table>';
?>