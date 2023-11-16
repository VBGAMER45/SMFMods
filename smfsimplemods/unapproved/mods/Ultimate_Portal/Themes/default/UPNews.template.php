<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

//Show the Ultimate Portal - Module NEWS
function template_news_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;

	$content = '
<table width="100%" cellpadding="5" cellspacing="1">	
	<tr>
		<td class="smalltext" align="left" width="100%">
			<table class="tborder" style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td class="smalltext" align="left" width="100%">
						<strong>'. $context['news-linktree'] .'</strong>
					</td>
				</tr>
			</table>	
		</td>
	</tr>		
	<tr>
		<td width="100%">
			<table class="tborder" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td class="information" width="2%" align="center">
						&nbsp;
					</td>
					<td class="information" width="40%" align="left">
						'. $txt['up_module_category_name'] .'
					</td>
					<td class="information" width="34%" align="left">
						'. $txt['up_module_last_news'] .'
					</td>
					<td class="information" width="24%" align="left">
						'. $txt['up_module_news_date'] .'
					</td>
				</tr>';
	if(!empty($context['news_rows']))	
	{
		foreach ($context['news-section'] as $section)
		{		
			$content .= '		
				<tr>
					<td class="description" width="2%" align="center">
						'. $section['icon'] .'
					</td>
					<td class="description" width="40%" align="left">
						<h2><strong>'. $section['title'] .'</strong></h2>
					</td>
					<td class="description" width="34%" align="left">
						<h3><strong>'. (!empty($section['last_new'])? $section['last_new'] : '') .'</strong></h3>
					</td>
					<td class="description" width="24%" align="left">
						'. (!empty($section['date'])? $section['date'] : '') .'
					</td>
				</tr>';
		}
	}			
		$content .= '		
			</table>
		</td>
	</tr>
</table>';

	//The News Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_news_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'news', $txt['up_module_title'] . ' - ' . $txt['up_module_news_title']);
	
}

//Show the Ultimate Portal - Module News - Show Category
function template_show_cat()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings;
	global $user_info, $memberContext;

	$content = "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_news_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";

	$content .= '
<table width="100%" cellpadding="5" cellspacing="1">
	<tr>
		<td class="smalltext" align="left" width="100%">
			<table class="tborder" style="border: 1px dashed" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td class="smalltext" align="left" '. ((empty($user_info['up-modules-permissions']['news_add']) && !$user_info['is_admin']) ? 'width="100%"' : 'width="85%"') .'>
						<strong>'. $context['news-linktree'] .'</strong>
					</td>';
	if (!empty($user_info['up-modules-permissions']['news_add']) || $user_info['is_admin'])
	{
		$content .= '					
					<td style="font-size:10px" align="left" width="15%">
						<img alt="" style="vertical-align: middle;" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png" />&nbsp;<a href="'. $scripturl .'?action=news;sa=add-new;id-cat='. $context['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_add_new'] .'</a>
					</td>';
	}				
	$content .= '				
				</tr>
			</table>	
		</td>
	</tr>
	<tr>
		<td '. ((empty($user_info['up-modules-permissions']['news_add']) && !$user_info['is_admin']) ? 'colspan="1"' : 'colspan="2"') .' width="100%">';
	if (!empty($context['display-news']))	
	{
		//Page Index
		$content .= '
		<br />
			<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] .'
		<br /><br />';
		//End Page Index						
		
		foreach ($context['news'] as $news)
		{
		$content .= '		
			<table class="tborder" cellpadding="5" cellspacing="1" width="100%">
				<tr>
					<td style="font-size:10px" class="information" width="12%" align="center">
						'. $news['view'] .'
					</td>';
		if (!empty($user_info['up-modules-permissions']['news_moderate']) || $user_info['is_admin'])
		{			
			$content .= '				
					<td style="font-size:10px" class="information" width="12%" align="center">
						'. $news['edit'] .'
					</td>	
					<td style="font-size:10px" class="information" width="12%" align="center">
						'. $news['delete'] .'
					</td>
				</tr>';
		}			
		$content .= '			
				
				<tr>
					<td class="description" '. ((empty($user_info['up-modules-permissions']['news_moderate']) && !$user_info['is_admin']) ? 'colspan="2"' : 'colspan="4"') .' width="100%" align="left">
						<table class="tborder" cellpadding="5" cellspacing="1" width="100%">
							<tr>
								<td valign="top" width="100%" align="left">					
									<h2><strong>'. $news['title'] .'</strong></h2>
									'. $news['added-news'] .'
									<br />'. $news['updated-news'] .'
								</td>
							</tr>
						</table>			
					</td>	
				</tr>
			</table><br />';
		}	
		//Page Index
		$content .= '<strong>'. $txt['pages'] .':</strong> '. $context['page_index'];
		//End Page Index						
		
	}		
	$content .= '
		</td>
	</tr>
</table>';

	//The News Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_news_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'news', $txt['up_module_title'] . ' - ' . $txt['up_module_news_title'] .' - '. $context['title']);
	
}

//Show the Ultimate Portal - Module News - View News
function template_view_news()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $memberContext;
	global $user_info;

	$content = "
	    <script type=\"text/javascript\">
			function makesurelink() {
				if (confirm('".$txt['ultport_delete_news_confirmation']."')) {
					return true;
				} else {
					return false;
				}
			}
	    </script>";
		

	$content .= '
<div>	
		<div style="display:block;border: 1px dashed #aaa;margin-top:3px;margin-bottom:3px;"><strong>'. $context['news-linktree'] .'</strong></div>
					';
	if (!empty($user_info['up-modules-permissions']['news_add']) || $user_info['is_admin'])
	{
		$content .= '<div style="display:block;border: 1px dashed #aaa;text-align:center;"><img alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/add.png" />&nbsp;<a href="'. $scripturl .'?action=news;sa=add-new;id-cat='. $context['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_add_new'] .'</a></div>';
	}				
	if (!empty($context['display-news']))	
	{
		foreach ($context['news'] as $news)
		{
		if (!empty($user_info['up-modules-permissions']['news_moderate']) || $user_info['is_admin'])
		{			
			$content .= '<div style="display:block;border: 1px dashed #aaa;margin-top:3px;text-align:center;">'. $news['edit'] .'
						'. $news['delete'] .'</div>';
		}			
		$content .= '<div>
							<div class="description" align="center"><h2><strong>'. $news['title'] .'</strong></h2>
							'. $news['added-news'] .'<br />'. $news['updated-news'] .'</div>
							<div>'. $news['body'] .'</div>
						'. $context['social_bookmarks'] .'</div>';
		}	
	}		
	$content .= '</div>';

	//The News Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_news_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'news', $txt['up_module_news_title'] .' - '. $context['title'] .' <em>('. $context['page-title-news'] .')</em>');
	
}

//Form for Add NEWS
function template_add_news()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings, $user_info;

	$content = '<div style="display:block;border: 1px dashed #aaa;margin-top:3px;"><strong>'. $context['news-linktree'] .'</strong></div>
<br />';
		
			$content .=	'
			<div style="display:block;"><form method="post" action="'. $scripturl .'?action=news;sa=add-new" accept-charset="'. $context['character_set'] .'">												
                     <hr /><div style="font-weight:bold;">'. $txt['ultport_edit_news_title'] .':						
							<input type="text" name="title" size="50" maxlength="150"/></div><hr />						
							<div>'. $txt['ultport_edit_news_section'] .'<select size="1" name="id_cat">
								'. $context['section'] .'
							</select></div><hr />
						
							<div align="center"><textarea id="elm1" name="elm1" rows="15" cols="80" >'. $context['body'] .'</textarea></div>
	
							<div align="right"><br /><input type="hidden" name="save" value="ok" />						
							<input type="hidden" name="id_member" value="'. $user_info['id'] .'" />						
							<input type="hidden" name="username" value="'. $user_info['username'] .'" />
							<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
							<input type="submit" name="'.$txt['ultport_button_add_new'].'" value="'.$txt['ultport_button_add_new'].'" /></div>
			</form></div>';
		
	//The News Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_news_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';

	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'news-add', $txt['up_module_news_add']);

}


//Form for Edit NEWS
function template_edit_news()
{
	global $context, $txt, $settings, $scripturl, $ultimateportalSettings, $user_info;

	$content = '<br />
			<div style="border: 1px dashed;display:block;" width="100%">
						<strong>'. $context['news-linktree'] .'</strong>	
			</div>	
		<br />';
		
			$content .=	'
			<div><form method="post" action="'. $scripturl .'?action=news;sa=edit-new" accept-charset="'. $context['character_set'] .'">												
								<hr />
							<div style="font-weight:bold;">'. $txt['ultport_edit_news_title'] .': <input type="text" value="'. $context['title'] .'" name="title" size="65" maxlength="150"/></div>
							<hr />'. $txt['ultport_edit_news_section'] .'			
							<select size="1" name="id_cat">
								'. $context['section-edit'] .'
							</select><br /></div><hr />
						<div align="center"><textarea id="elm1" name="elm1" rows="15" cols="80" >'. $context['body'] .'</textarea></div>
                      <div><input type="hidden" name="save" value="ok" />						
							<input type="hidden" name="id" value="'. $context['id'] .'" />											
							<input type="hidden" name="id_member_updated" value="'. $user_info['id'] .'" />						
							<input type="hidden" name="username_updated" value="'. $user_info['username'] .'" />	
							<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
							<input type="submit" name="'.$txt['ultport_button_edit'].'" value="'.$txt['ultport_button_edit'].'" /></form>';
             $content .= '		
	                 	</div>';
		

	//The News Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_news_title'] .'</a> &copy; 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a>';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page('1', '1', $content, $copyright, 'news', $txt['up_module_news_edit'] .' - '. $context['title-news']);

}

?>
