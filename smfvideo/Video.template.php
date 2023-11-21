<?php
/*
SMF Gallery Pro - Video Addon
Version 2.5
by: vbgamer45
http://www.smfhacks.com
Copyright 2006-2010 http://www.samsonsoftware.com

############################################
License Information:
SMF Gallery Pro - Video Addon is NOT free software.
This software may not be redistributed.

The pro edition license is good for a single instance / install on a website.
You are allowed only one active install for each license purchase.

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/

function template_addvideo()
{
	global $scripturl, $modSettings, $txt, $context, $settings, $db_prefix, $gallerySettings;
	
	// Get the category
	@$cat = (int) $_REQUEST['cat'];

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';

	echo '<form method="post" enctype="multipart/form-data" name="picform" id="picform" action="' . $scripturl . '?action=gallery&sa=addvideo2">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="catbg">
    <td width="50%" colspan="2" align="center">
    <b>' . $txt['gallery_form_addvideo'] . '</b></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_title'] . '</b>&nbsp;</td>
  	<td><input type="text" name="title" size="75" /></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_category'] . '</b>&nbsp;</td>
  	<td><select name="cat">';

 	foreach ($context['gallery_cat'] as $i => $category)
		echo '<option value="' . $category['ID_CAT']  . '" ' . (($cat == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';

 echo '</select>
  	</td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_description'] . '</b>&nbsp;</td>
  	<td><table>
   ';
   theme_postbox('');
   echo '</table>';

     	if ($context['show_spellchecking'])
     		echo '
     									<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'picform\', \'description\');" />';

echo '
  	</td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_keywords'] . '</b>&nbsp;</td>
  	<td><input type="text" name="keywords" maxlength="100" size="75" />
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_previewpic'] . '</b>&nbsp;</td>

    <td><input type="file" size="48" name="picture" />
    </td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_videofile'] . '</b>&nbsp;</td>
    <td><input type="file" size="48" name="video" />
    </td>
  </tr>';
  
  if ($modSettings['gallery_video_allowlinked'])
  echo '
  <tr class="windowbg2">
  	<td valign="top" align="right"><b>' . $txt['gallery_form_videourl']  . '</b>&nbsp;</td>
    <td><input type="text" size="75" name="videourl" />
    <br />',$txt['gallery_form_videourl2'],'
    </td>
  </tr>';

  echo '
  <tr>
  	<td colspan="2" class="windowbg2"><hr /></td>
  </tr>';

 	
	$result = db_query("
	SELECT 
		title, defaultvalue, is_required, ID_CUSTOM 
	FROM  {$db_prefix}gallery_custom_field
			WHERE ID_CAT = " . $cat, __FILE__, __LINE__);
	while ($row2 = mysql_fetch_assoc($result))
	{
		echo '<tr>
 	 		<td class="windowbg2" align="right"><b>', $row2['title'], ($row2['is_required'] ? '<font color="#FF0000">*</font>' : ''), '</b></td>
 	 		<td class="windowbg2"><input type="text" name="cus_', $row2['ID_CUSTOM'],'" value="' , $row2['defaultvalue'], '" /></td>
	
 	 	</tr>
 	 ';
	}
	mysql_free_result($result);
  
	echo '
	   <tr class="windowbg2">
		<td align="right"><b>' . $txt['gallery_form_additionaloptions'] . '</b>&nbsp;</td>
		<td><input type="checkbox" name="sendemail" checked="checked" /><b>' . $txt['gallery_notify_title'] .'</b>';

	if ($modSettings['gallery_allow_mature_tag'])
	{
		echo '
	   <input type="checkbox" name="markmature" /><b>' .$txt['gallery_txt_mature'] .'</b>
  ';
	}
	
	if ($gallerySettings['gallery_set_allowratings'])
	{
		echo '<br />
  	   <input type="checkbox" name="allow_ratings" checked="checked" /><b>' .$txt['gallery_txt_allow_ratings'] .'</b>
  ';
	}
	
  if ($gallerySettings['gallery_set_allow_copy'])
  {
  	echo '<br />
	   <input type="checkbox" name="copyimage" />',$txt['gallery_txt_copy_item'],'
	  ';	
  }
	

	echo '</td>
	  </tr>
  ';

	if ($modSettings['gallery_commentchoice'])
	{
		echo '
	<tr class="windowbg2">
		<td align="right">&nbsp;</td>
		<td><input type="checkbox" name="allowcomments" checked="checked" /><b>' . $txt['gallery_form_allowcomments'] .'</b></td>
	</tr>';
	}
    
echo '
  <tr class="windowbg2">
    <td width="28%" colspan="2" align="center" class="windowbg2">
	<input type="hidden" name="userid" value="'. $context['gallery_user_id'] . '" />
    <input type="submit" value="' .$txt['gallery_form_addvideo'] . '" name="submit" /><br />';

  	if (!allowedTo('smfgallery_autoapprove'))
  		echo $txt['gallery_form_notapproved'];

echo '
    </td>
  </tr>
</table>
</form>';

	if ($context['show_spellchecking'])
		echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';
	
	
	video_gallery_copyright();
}

function template_editvideo()
{
	global $scripturl, $modSettings, $txt, $context, $settings, $boardurl, $db_prefix, $gallerySettings;
	
	$g_manage = allowedTo('smfgallery_manage');
	
	if (empty($modSettings['gallery_url']))
		$modSettings['gallery_url'] = $boardurl . '/gallery/';

	
	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';

	echo '<form method="post" enctype="multipart/form-data" name="picform" id="picform" action="' . $scripturl . '?action=gallery&sa=editvideo2">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="catbg">
    <td width="50%" colspan="2" align="center">
    <b>' . $txt['gallery_form_editvideo'] . '</b></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_title'] . '</b>&nbsp;</td>
  	<td><input type="text" size="75" name="title" value="' . $context['gallery_pic']['title'] . '" /></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_category'] . '</b>&nbsp;</td>
  	<td><select name="cat">';

 	foreach ($context['gallery_cat'] as $i => $category)
		echo '<option value="' . $category['ID_CAT']  . '" ' . (($context['gallery_pic']['ID_CAT'] == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';

 echo '</select>
  	</td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_description'] . '</b>&nbsp;</td>
  	<td><table>
   ';
   theme_postbox($context['gallery_pic']['description']);
   echo '</table>';

     	if ($context['show_spellchecking'])
     		echo '
     									<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'picform\', \'description\');" />';

echo '
  	</td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_keywords'] . '</b>&nbsp;</td>
  	<td><input type="text" name="keywords" maxlength="100" size="75" value="' . $context['gallery_pic']['keywords'] . '" />
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_previewpic'] . '</b>&nbsp;</td>

    <td><input type="file" size="48" name="picture" />
    </td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_videofile'] . '</b>&nbsp;</td>
    <td><input type="file" size="48" name="video" />
    </td>
  </tr>';
  

if ($modSettings['gallery_video_allowlinked'])
echo '
  <tr class="windowbg2">
  	<td valign="top" align="right"><b>' . $txt['gallery_form_videourl']  . '</b>&nbsp;</td>
    <td><input type="text" size="75" name="videourl"  value="' . ($context['gallery_pic']['type'] == 1 ? '' : $context['gallery_pic']['videofile']) . '" />
    <br />',$txt['gallery_form_videourl2'],'
    </td>
  </tr>';
 

echo '
   <tr>
  	<td colspan="2" class="windowbg2"><hr /></td>
  </tr>';

	$result = db_query("
	SELECT 
		f.title, f.is_required, f.ID_CUSTOM, d.value 
	FROM  {$db_prefix}gallery_custom_field as f
			LEFT JOIN {$db_prefix}gallery_custom_field_data as d ON (d.ID_CUSTOM = f.ID_CUSTOM)
			WHERE ID_PICTURE = " . $context['gallery_pic']['ID_PICTURE'] . " AND ID_CAT = " . $context['gallery_pic']['ID_CAT'], __FILE__, __LINE__);
	while ($row2 = mysql_fetch_assoc($result))
	{
		echo '<tr>
 	 		<td class="windowbg2" align="right"><b>', $row2['title'], ($row2['is_required'] ? '<font color="#FF0000">*</font>' : ''), '</b></td>
 	 		<td class="windowbg2"><input type="text" name="cus_', $row2['ID_CUSTOM'],'" value="' , $row2['value'], '" /></td>
 	 	</tr>
 	 ';
	}
	mysql_free_result($result);
 

      
      
 echo '
	   <tr class="windowbg2">
		<td align="right"><b>' . $txt['gallery_form_additionaloptions'] . '</b>&nbsp;</td>
		<td><input type="checkbox" name="sendemail" ' . ($context['gallery_pic']['sendemail'] ? 'checked="checked"' : '' ) . ' /><b>' . $txt['gallery_notify_title'] .'</b>';


	if ($modSettings['gallery_allow_mature_tag'])
	{
	echo '
	   <input type="checkbox" name="markmature" ' . ($context['gallery_pic']['mature'] ? 'checked="checked"' : '' ) . ' /><b>' .$txt['gallery_txt_mature'] .'</b>

  ';
	}

	echo '</td>
	  </tr>';
	
	
	if ($context['is_usergallery'] == true)
	{
		echo '
	   <tr class="windowbg2">
		<td align="right">&nbsp;</td>
		<td><input type="checkbox" name="featured" ' . ($context['gallery_pic']['featured'] ? 'checked="checked"' : '' ) . ' /><b>',$txt['gallery_txt_featured_image'],'</b></td>
	  </tr>';
	}

  if ($modSettings['gallery_commentchoice'])
  {
	echo '
	   <tr class="windowbg2">
		<td align="right">&nbsp;</td>
		<td><input type="checkbox" name="allowcomments" ' . ($context['gallery_pic']['allowcomments'] ? 'checked="checked"' : '' ) . ' /><b>',$txt['gallery_form_allowcomments'],'</b></td>
	  </tr>';
  }
  
  	if ($gallerySettings['gallery_set_allowratings'])
	{
		echo ' <tr class="windowbg2">
		<td align="right">&nbsp;</td>
  	  	 <td><input type="checkbox" name="allow_ratings" ' . ($context['gallery_pic']['allowratings'] ? 'checked="checked"' : '' ) . ' /><b>' .$txt['gallery_txt_allow_ratings'] .'</b>
  	   	</td>
  	   </tr>
  ';
	}
    
  
  
  

  // If the user can manage the gallery give them the option to change the picture owner.
  if ($context['is_usergallery'] == false && $g_manage == true)
  {
	  echo '<tr class="windowbg2">
	  <td align="right">', $txt['gallery_text_changeowner'], '</td>
	  <td><input type="text" name="pic_postername" id="pic_postername" value="" />
	  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a> 
	  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a>
	  </td>
	  </tr>
	  <tr class="windowbg2">
	  <td colspan="2" align="center">
	  ',$txt['gallery_txt_picturemoveoptions'],'<a href="',$scripturl,'?action=gallery;sa=changegallery;mv=touser;id=',$context['gallery_pic']['ID_PICTURE'], '">',$txt['gallery_movetousergallery'],'</a>
	  </td>
	  </tr>
	  ';
  }
  else 
  {
  	if ($g_manage == true)
  	{
		echo ' <tr class="windowbg2">
	  <td colspan="2" align="center">
	  ',$txt['gallery_txt_picturemoveoptions'],'<a href="',$scripturl,'?action=gallery;sa=changegallery;mv=togallery;id=',$context['gallery_pic']['ID_PICTURE'], '">',$txt['gallery_movetomaingallery'],'</a>
	  </td>
	  </tr>
	  ';	
  	}
  	
  }
  
echo '
  <tr class="windowbg2">
    <td width="28%" colspan="2" align="center" class="windowbg2">
	<input type="hidden" name="id" value="' . $context['gallery_pic']['ID_PICTURE'] . '" />
    <input type="submit" value="' . $txt['gallery_form_editvideo'] . '" name="submit" /><br />';

  	if (!allowedTo('smfgallery_autoapprove'))
  		echo $txt['gallery_form_notapproved'];
  			
// Show Old Video
echo '<div align="center"><br /><b>' .  $txt['gallery_video_oldvideo'] . '</b><br />';
	// Show the video box
	showvideobox($context['gallery_pic']['videofile']);
echo '</div>';

echo '<div align="center"><br /><b>' . $txt['gallery_text_oldpicture'] . '</b><br />
<a href="' . $scripturl . '?action=gallery;sa=view;id=' . $context['gallery_pic']['ID_PICTURE'] . '" target="blank"><img src="' . $modSettings['gallery_url'] . $context['gallery_pic']['thumbfilename']  . '" /></a><br />
			<span class="smalltext">' . $txt['gallery_text_views']  . $context['gallery_pic']['views'] . '<br />
			' . $txt['gallery_text_filesize']  . $context['gallery_pic']['filesize'] . 'kb<br />
			' . $txt['gallery_text_date'] . $context['gallery_pic']['date'] . '<br />
	</div>
    </td>
  </tr>
</table>

		</form>';

	if ($context['show_spellchecking'])
		echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';

	
	video_gallery_copyright();
}

function template_video_settings()
{
	global $scripturl, $modSettings, $boarddir, $txt, $context;

	if(empty($modSettings['gallery_path']))
		$modSettings['gallery_path'] = $boarddir . '/gallery/';
		
		
	$context['gallery']['buttons_set']['settings'] =  array(
							'text' => 'gallery_text_features',
							'url' => $scripturl . '?action=gallery;sa=adminset',
							'lang' => true,
					);
					
	$context['gallery']['buttons_set']['layout'] =  array(
							'text' => 'gallery_txt_layout_settings2',
							'url' => $scripturl . '?action=gallery;sa=viewlayout' ,
							'lang' => true,
					);
					
	$context['gallery']['buttons_set']['exif'] =  array(
							'text' => 'gallery_txt_exif_settings2',
							'url' => $scripturl . '?action=gallery;sa=exifsettings' ,
							'lang' => true,
							'is_selected' => true,
					);
	if ($context['video_addon'])
		$context['gallery']['buttons_set']['videoaddon'] =  array(
							'text' => 'gallery_text_videosettings',
							'url' => $scripturl . '?action=gallery;sa=videoset' ,
							'lang' => true,
					);
					
	echo '	<table border="0" cellpadding="0" cellspacing="0" align="center" width="80%">
						<tr>
							<td style="padding-right: 1ex;" align="right" >
			<table cellpadding="0" cellspacing="0" align="left"  >
									<tr>
						', template_button_strip($context['gallery']['buttons_set'], 'bottom'), '
							</tr>
							</table>
				</td>
						</tr>
					</table>';

echo '<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['gallery_text_videosettings'], '</td>
		</tr>
		<tr class="windowbg">
			<td>
			<b>' . $txt['gallery_text_settings'] . '</b><br />
			<form method="post" action="' . $scripturl . '?action=gallery;sa=videoset2">
				<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4">			
				<tr><td width="30%">' . $txt['gallery_video_maxfilesize'] . '</td><td><input type="text" name="gallery_video_maxfilesize" value="' .  $modSettings['gallery_video_maxfilesize'] . '" /> (bytes)</td></tr>
				<tr><td width="30%">' . $txt['gallery_video_playerheight'] . '</td><td><input type="text" name="gallery_video_playerheight" value="' .  $modSettings['gallery_video_playerheight'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['gallery_video_playerwidth'] . '</td><td><input type="text" name="gallery_video_playerwidth" value="' .  $modSettings['gallery_video_playerwidth'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['gallery_video_filetypes'] . '</td><td><input type="text" name="gallery_video_filetypes" value="' .  $modSettings['gallery_video_filetypes'] . '" size="50" /></td></tr>
                
                <tr><td width="30%">' . $txt['gallery_txt_embed_default_height'] . '</td><td><input type="text" name="mediapro_default_height" value="' .  $modSettings['mediapro_default_height'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['gallery_txt_embed_default_width'] . '</td><td><input type="text" name="mediapro_default_width" value="' .  $modSettings['mediapro_default_width'] . '" /></td></tr>
                
                
				</table>
				<input type="checkbox" name="gallery_video_allowlinked" ' . ($modSettings['gallery_video_allowlinked'] ? ' checked="checked" ' : '') . ' />' . $txt['gallery_video_allowlinked'] . '<br />
				<input type="checkbox" name="gallery_video_showdowloadlink" ' . ($modSettings['gallery_video_showdowloadlink'] ? ' checked="checked" ' : '') . ' />' . $txt['gallery_video_showdowloadlink'] . '<br />
				<input type="checkbox" name="gallery_video_showbbclinks" ' . ($modSettings['gallery_video_showbbclinks'] ? ' checked="checked" ' : '') . ' />' . $txt['gallery_video_showbbclinks'] . '<br />
				';

				if (!is_writable($modSettings['gallery_path'] . 'videos/'))
					echo '<font color="#FF0000"><b>' . $txt['gallery_video_write_error']  . $modSettings['gallery_path'] . 'videos/' . '</b></font>';

				echo '
				<input type="submit" name="savesettings" value="' . $txt['gallery_save_settings'] . '" />
			</form>
		
			</td>
		</tr>
</table>';
	
	video_gallery_copyright();
}

function video_gallery_copyright()
{
	// This is required this most stay here
	echo '<!-- caa97f05b050bb9704008a58c9fd36b7 -->';
	
	
	// Do NOT CHANGE THIS CODE UNLESS you have COPYRIGHT Link Removal
	//http://www.smfhacks.com/gallery-linkremoval.php
	
	//Copyright link must remain. To remove you need to purchase link removal at smfhacks.com
	if (function_exists("GalleryCheckInfo"))
		$showInfo = GalleryCheckInfo();
	else 
		$showInfo = true;
    
    if ($showInfo == true)
		echo '<div align="center"><!--Link must remain or contact me to pay to remove.--><span class="smalltext">Powered by <a href="http://www.smfhacks.com/smf-gallery-pro.php" target="blank">SMF Gallery Pro</a></span><!--End Copyright link--></div>';

}


?>