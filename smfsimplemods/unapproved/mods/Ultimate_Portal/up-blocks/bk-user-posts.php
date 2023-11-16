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
	
global $settings, $db_prefix, $boardurl, $context, $scripturl;
global $smcFunc, $sourcedir, $ultimateportalSettings, $txt;

//Load User Posts
if(!empty($ultimateportalSettings['up_reduce_site_overload']))
{
	if((cache_get_data('bk_userpost', 1800)) === NULL)
	{
		LoadUserPostsRows('view', 0, 'block');
		//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
		cache_put_data('bk_userpost', $context['userpost'], 1800);		
		cache_put_data('view_userpost', $context['view-userpost'], 1800);		
		cache_put_data('page_index_user_post', $context['page_index'], 1800);		
	}else{
		$context['userpost'] = cache_get_data('bk_userpost', 1800);
		$context['view-userpost'] = cache_get_data('view_userpost', 1800);
		$context['page_index'] = cache_get_data('page_index_user_post', 1800);
	}
}else{
	LoadUserPostsRows('view', 0, 'block');
}

// Load Language
if (loadlanguage('UPUserPosts') == false)
	loadLanguage('UPUserPosts','english');

echo warning_delete($txt['ultport_delete_confirmation']);				

if (!empty($context['view-userpost']))
{
	foreach ($context['userpost'] as $userpost)
	{		
		//Social Bookmarks 
		if(!empty($ultimateportalSettings['user_posts_internal_page']))
			$context['social_bookmarks'] = UpSocialBookmarks($scripturl .'?action=user-posts;sa=view-single;id='. $userpost['id'] );
		else
			$context['social_bookmarks'] = UpSocialBookmarks($userpost['link_topic']);

		echo '
		<div >
		<div class="description"><table><tr><td><img alt="" title="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/information.png" width="15" /></td><td style="font-size: 14px;font-weight:bold;"> '. $txt['up_information_title'] .'</td></tr></table>
		<hr />
						<div style="float:right;text-align:center">							
							'.  (!empty($ultimateportalSettings['user_posts_field_type_posts']) ? $userpost['type'] : '') .'
							'. (!empty($ultimateportalSettings['user_posts_field_add_language']) ? $userpost['lang'] : '') .'
						</div>
					
						<strong>'. $txt['up_post_title'] .': </strong>'. $userpost['title'] .'
						'. (!empty($ultimateportalSettings['user_posts_field_topic_author']) ? '<br /><strong>'. $txt['up_author_title'] .': </strong>'. $userpost['link-author'] : '') .'
						'. (!empty($ultimateportalSettings['user_posts_field_type_posts']) ? ' | <strong>'. $txt['up_type_post'] .': </strong>'. $userpost['type-title'] : '') .'											
						'. (!empty($ultimateportalSettings['user_posts_field_add_language']) ? ' | <strong>'. $txt['up_lang_post'] .': </strong>'. $userpost['lang-title'] : '') .'
						<div>';
						if (!empty($ultimateportalSettings['user_posts_field_member_use_module']))
							echo	$userpost['added-for'] .'<br />';
						
						if (!empty($ultimateportalSettings['user_posts_field_member_updated_module']) && !empty($userpost['id_member_updated']))				
							echo	$userpost['updated-for'] .'<br />';								
				echo '		
						</div></div>
						<span class="clear upperframe"><span></span></span>
				 <div class="roundframe"><table style="font-family:fantasy;"><tr><td valign="top"><a href="'. $userpost['link_topic'] .'">'. $userpost['cover-img'] .'</a></td><td valign="top" style="border-radius:8px;">
						<strong style="font-size:14px;text-decoration:underline;">'. $txt['up_description_post'] .':</strong><br /><br />
							'. $userpost['description'] .'
						</td></tr></table><br /></div><span class="lowerframe"><span></span></span>
						<div>';
						
					echo'<div style="padding:3px;font-family:arial;" align="center"><img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/view.png" alt="" title="" />
						<a href="'. $userpost['link_topic'] .'">'. $txt['up_view_post'] .'</a> - ';
					if (!empty($user_info['up-modules-permissions']['user_posts_moderate']) || $user_info['is_admin'])
					{										
						echo '
						<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/edit.png" alt="" title="" />
						<a href="'.$scripturl .'?action=user-posts;sa=edit;id='. $userpost['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_edit'] .'</a> -
						<img border="0" src="'.$settings['default_images_url'].'/ultimate-portal/delete.png" alt="" title="" />
						<a onclick="return makesurelink()" href="'.$scripturl .'?action=user-posts;sa=delete;id='. $userpost['id'] .';sesc=' . $context['session_id'].'">'. $txt['ultport_button_delete'] .'</a>';
					}	
					echo'</div>';
					
				//Social Bookmarks
				echo '<br />
				'. (!empty($ultimateportalSettings['user_posts_social_bookmarks']) ? $context['social_bookmarks'] : '') .'';		
				
				echo '</div>		
					</div>';
	}
	  echo '<br /><div style="text-align:center">
			<strong>'. $txt['pages'] .':</strong> '. $context['page_index'] . '
			</div>';
	
}else{
echo '<div style="text-align:center">
'. $txt['ultport_error_no_user_posts_rows'] .'
</div>	';
}
?>