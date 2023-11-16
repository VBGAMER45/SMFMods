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
	
global $settings, $db_prefix, $boardurl, $context;
global $smcFunc, $sourcedir;

//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
if(!empty($ultimateportalSettings['up_reduce_site_overload']))
{
	if((cache_get_data('bk_menu', 1800)) === NULL)
	{
		LoadMainLinks();
		cache_put_data('bk_menu', $context['main-links'], 1800);		
	}else{
		$context['main-links'] = cache_get_data('bk_menu', 1800);
	}
}else{
	LoadMainLinks();
}

echo '
	<table border="0" width="100%" cellpadding="5" cellspacing="1">';
foreach($context['main-links'] as $main_link) 
{
	//Is Active?
if ($main_link['active'])  
{
		echo '
			<tr>
				<td class="'.!empty($main_link['class']).'" align="left">
					'. $main_link['icon'] .'&nbsp;
					<a href="'. $main_link['url'] .'">
						<span>'. $main_link['title'] .'</span>
					</a>
				</td>
			</tr>';		
}			
	
}
echo '
	</table>';				

?>