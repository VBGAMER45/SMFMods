<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
***********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');
	
	global $settings, $db_prefix, $scripturl, $txt;
	global $ultimateportalSettings, $sourcedir;
	
	require_once($sourcedir . '/Subs-UltimatePortal.php');
	
	// Limit banners per view
	$top = $ultimateportalSettings['aff_limit'];
	
	// Check Direction	
	$direction = ($ultimateportalSettings['aff_direction'] == 1) ? 'up' : 'down';
	// Check Target
	$target = ($ultimateportalSettings['aff_target'] == 1) ? '_self' : '_blank';
	
	//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
	if(!empty($ultimateportalSettings['up_reduce_site_overload']))
	{
		if((cache_get_data('bk_affiliates', 1800)) === NULL)
		{
			LoadAffiliates("LIMIT $top");	
			cache_put_data('bk_affiliates', $context['up-aff'], 1800);		
		}else{
			$context['up-aff'] = cache_get_data('bk_affiliates', 1800);
		}
	}else{
		LoadAffiliates("LIMIT $top");
	}	
	
	// Make a quick array to list the links in.
	echo '
		<table style="border-spacing:5px;table-layout:fixed;width:100%;" border="0" cellspacing="1" cellpadding="3">
				<tr>
				<td width="100%" align="center">';
				
	// Ask for marquee move..			
				if ( $ultimateportalSettings['aff_direction'] != 3)
				{
					echo '
					<marquee width="100%" scrolldelay="'.$ultimateportalSettings['aff_scrolldelay'].'" direction="'.$direction.'" loop="infinite" onmouseover="this.stop()" onmouseout="this.start()">';
				}
			
	foreach ($context['up-aff'] as $aff)
	{
		echo '
			'. $aff['imageurl'] . '<br /><br />';
	}

	if ( $ultimateportalSettings['aff_direction'] != 3)
	{
	echo '
		</marquee>';
	}
	echo '
				</td>
			</tr>
		</table>';		
		// End ;)
?>