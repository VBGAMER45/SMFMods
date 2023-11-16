<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

//Show the Ultimate Portal - Module About Us
function template_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = ''; //only initialized
	
	if (!empty($ultimateportalSettings['about_us_extrainfo_title']) && !empty($ultimateportalSettings['about_us_extra_info']))
	$content .= '
	<table cellspacing="3" cellpadding="6" width="100%">
		<tr>
			<td class="titlebg" align="left" colspan="2" width="100%">
				'. $ultimateportalSettings['about_us_extrainfo_title'] .'
			</td>
		</tr>
		<tr>
			<td class="windowbg2" align="left" colspan="2" width="100%">
				'. parse_bbc($ultimateportalSettings['about_us_extra_info']) .'
			</td>
		</tr>		
	</table>
	<hr />
	<br />';


	$GroupsView = explode(',',$ultimateportalSettings['about_us_group_view']);	
	foreach($context['staff'] as $staff)
	{
		if (in_array($staff['id_group'],$GroupsView) == true)
		{
			$i = 1; //flag
			$column = 2;
			$content .= '
			<table cellspacing="3" cellpadding="6" width="100%">
				<tr>
					<td style="border:1px solid;" class="windowbg" align="left" colspan="2" width="100%">
						'. $staff['stars'] . '&nbsp;<span style="color:'. $staff['online_color'] .'"><strong>' . $staff['group_name'] .'</strong></span>'. $staff['description'] .'
					</td>	
				</tr>
				<tr>
					<td class="windowbg2" width="100%" align="left">';
			if (!empty($staff['members']))
			{	
				$div_float = true;
				foreach($staff['members'] as $member)
				{
					$content .= '
						<div style="width:49.5%;float:'. ($div_float ? 'left' : 'right') .'">
							<div style="float:left;width:30%" class="avatar">
								<a href="'. $scripturl .'?action=profile;u='. $member['id_member'] .'">'. $member['avatar']['image'] .'</a>
							</div>											
							<span class="largetext"><a href="'. $member['href'] .'"><strong>'. $member['member_name'] .'</strong></a></span>
							<ul>
								'. (!empty($ultimateportalSettings['about_us_registered']) ? '<li><strong>'. $txt['about_date_registered'] .':</strong>&nbsp;'. $member['date_registered'] .'</li>' : '') .'
								'. (!empty($ultimateportalSettings['about_us_view_mail']) ? '<li><strong>'. $txt['about_email_address'] .':</strong>&nbsp;<a href="mailto:'. $member['email_address'] .'">'. $member['email_address'] .'</a></li>' : '') .'
								<li>'. (!empty($ultimateportalSettings['about_us_view_pm']) ? '<a href="'. $member['online']['href'] .'">' : '') .'<img alt="" style="vertical-align:middle" src="'. $member['online']['image_href'] .'" />&nbsp;'. $member['online']['label'] .''. (!empty($ultimateportalSettings['about_us_view_pm']) ? '</a>' : '') .'</li>							
							</ul>
						</div>';
					$div_float = !$div_float;	
					$i++;
					if ($i==$column+1)
					{
						$content .= '<br class="clear"/>';
						$i=1;
					} 
				}
			}else{
				$content .= $txt['ultport_error_no_members'];
			}
			$content .='
					</td>
				</tr>
			</table>';
		}
	}

	//The About Us Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="https://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_about_title'] .'</a> &copy; 2010 - 2021 <a href="http://www.smfsimple.com">SMFSimple.com</a><br />';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page(0, 0, $content, $copyright, 'about-us', $txt['up_module_title'] . ' - ' . $txt['up_module_about_title']);
	
}

?>