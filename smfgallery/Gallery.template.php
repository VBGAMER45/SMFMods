<?php
/*
SMF Gallery Lite Edition
Version 5.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2009-2014 SMFHacks.com

############################################
License Information:
SMF Gallery is NOT free software.
This software may not be redistributed.

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/
function template_mainview()
{
	global $scripturl, $txt, $context, $modSettings;

	// Permissions
	$g_manage = allowedTo('smfgallery_manage');

	if ($g_manage)
	{

		// Warn the user if they are managing the gallery that it is not writable
		if (!is_writable($modSettings['gallery_path']))
			echo '<font color="#FF0000"><b>', $txt['gallery_write_error'], $modSettings['gallery_path'] . '</b></font>';
	}

	echo '<table border="0" cellspacing="0" cellpadding="4" align="center" width="90%" class="tborder" >
					<tr class="titlebg">
						<td align="center">', $txt['gallery_text_title'], '</td>
					</tr>
					</table>
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" >
						<table cellpadding="0" cellspacing="0" align="right">
									<tr>
						', DoToolBarStrip($context['gallery']['buttons'], 'top'), '
							</tr>
							</table>
						</td>
						</tr>
					</table>
				<br />';

		// List all the catagories

		echo '<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
				<tr class="titlebg">
				<td colspan="2">', $txt['gallery_text_galleryname'], '</td>
				<td>', $txt['gallery_text_gallerydescription'], '</td>
				<td align="center">', $txt['gallery_text_totalimages'], '</td>
				';
		if ($g_manage)
		echo '
				<td>', $txt['gallery_text_reorder'], '</td>
				<td>', $txt['gallery_text_options'], '</td>
				';

		echo '</tr>';


		foreach($context['gallery_cat_list'] as $row)
		{

			$totalpics = GetTotalPicturesBYCATID($row['ID_CAT']);

			echo '<tr class="windowbg2">';

			if ($row['image'] == '')
				echo '<td colspan="2"><a href="', $scripturl, '?action=gallery;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></td><td>' . parse_bbc($row['description']) . '</td>';
			else
			{
				echo '<td><a href="', $scripturl, '?action=gallery;cat=' . $row['ID_CAT'] . '"><img src="' . $row['image'] . '" border="0" alt="" /></a></td>';
				echo '<td><a href="', $scripturl, '?action=gallery;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></td><td>' . parse_bbc($row['description']) . '</td>';
			}

			// Show total pictures in the category
			echo '<td align="center">', $totalpics, '</td>';

			// Show Edit Delete and Order category
			if ($g_manage)
			{
				echo '<td><a href="' . $scripturl . '?action=gallery;sa=catup;cat=' . $row['ID_CAT'] . '">' . $txt['gallery_text_up'] . '</a>&nbsp;<a href="' . $scripturl . '?action=gallery;sa=catdown;cat=' . $row['ID_CAT'] . '">' . $txt['gallery_text_down'] . '</a></td>
				<td><a href="' . $scripturl . '?action=gallery;sa=editcat;cat=' . $row['ID_CAT'] . '">' . $txt['gallery_text_edit'] . '</a>&nbsp;<a href="' . $scripturl . '?action=gallery;sa=deletecat;cat=' . $row['ID_CAT'] . '">' . $txt['gallery_text_delete'] . '</a>

						<br />
					<a href="' . $scripturl . '?action=gallery;sa=regen;cat=' . $row['ID_CAT'] . '">' . $txt['gallery_text_regeneratethumbnails'] . '</a>

				</td>';
			}


			echo '</tr>';

		}
		echo '</table><br /><br /><br />';

		// See if they are allowed to add catagories Main Index only
		if ($g_manage)
		{
			echo '<table cellspacing="0" cellpadding="5" border="0" align="center" width="90%" class="tborder">
				<tr class="titlebg">
					<td align="center">', $txt['gallery_text_adminpanel'], '</td>
				</tr>
				<tr class="windowbg2">
			<td align="center"><a href="' . $scripturl . '?action=gallery;sa=addcat">' . $txt['gallery_text_addcategory'] . '</a>&nbsp;-&nbsp;
			<a href="' . $scripturl . '?action=gallery;sa=adminset">' . $txt['gallery_text_settings'] . '</a>';


			if (allowedTo('manage_permissions'))
				echo '&nbsp;-&nbsp;<a href="' . $scripturl . '?action=permissions">' . $txt['gallery_text_permissions'] . '</a>';



			echo '<br />' . $txt['gallery_text_imgwaitapproval'] . '<b>' . $context['total_unapproved'] . '</b>&nbsp;&nbsp;<a href="' . $scripturl . '?action=gallery;sa=approvelist">' . $txt['gallery_text_imgcheckapproval'] . '</a>';

			echo '<br />' . $txt['gallery_text_imgreported'] . '<b>' . $context['total_reported_images'] . '</b>&nbsp;&nbsp;<a href="' . $scripturl . '?action=gallery;sa=reportlist">' . $txt['gallery_text_imgcheckreported'] . '</a>';

			echo '</td></tr></table><br /><br />';
		}


	GalleryCopyright();

}

function template_image_listing()
{
	global $scripturl, $txt, $context, $modSettings, $ID_MEMBER;

	// Permissions if they are allowed to edit or delete their own gallery pictures.
	$g_edit_own = allowedTo('smfgallery_edit');
	$g_delete_own = allowedTo('smfgallery_delete');

	$g_add = allowedTo('smfgallery_add');
	$g_manage = allowedTo('smfgallery_manage');


	// Check if GD is installed if not we will not show the thumbnails
	$GD_Installed = function_exists('imagecreate');

	if ($g_manage)
	{

		// Warn the user if they are managing the gallery that it is not writable
		if (!is_writable($modSettings['gallery_path']))
			echo '<font color="#FF0000"><b>', $txt['gallery_write_error'], $modSettings['gallery_path'] . '</b></font>';
	}


	// Get the Category
	$cat = $context['gallery_catid'];

	$context['start'] = (int) $_REQUEST['start'];


	$totalPics = GetTotalPicturesByCATID($cat);

echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';

	echo '<table border="0" cellspacing="0" cellpadding="4" align="center" width="90%" class="tborder" >
					<tr class="titlebg">
						<td align="center">&nbsp;</td>
					</tr>
					</table>
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" >
						<table cellpadding="0" cellspacing="0" align="right">
									<tr>
						', DoToolBarStrip($context['gallery']['buttons'], 'top'), '
							</tr>
							</table>
						</td>
						</tr>
					</table>
				<br />';


		$maxrowlevel = $modSettings['gallery_set_images_per_row'];
		echo '<br />
		<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
			<tr class="catbg">
				<td align="center" colspan="' . $maxrowlevel . '">' . @$context['gallery_cat_name'] . '</td>
			</tr>';

		// Show the pictures
		$rowlevel = 0;
		$styleclass = 'windowbg';


		$image_count = db_affected_rows();

		if ($image_count == 0)
		{
			echo '
			<tr class="' . $styleclass . '">
				<td colspan="' . $maxrowlevel . '" align="center"><b>',$txt['gallery_nopicsincategory'],'</b></td>
			</tr>

			';


		}


		foreach($context['gallery_image_list'] as $row)
		{
			if ($rowlevel == 0)
				echo '<tr class="' . $styleclass . '">';

			echo '<td align="center"><a href="' . $scripturl . '?action=gallery;sa=view&id=' . $row['ID_PICTURE'] . '">
			<img ' . ($GD_Installed == true ?  'src="' . $modSettings['gallery_url'] . $row['thumbfilename'] . '" ' : 'src="' . $modSettings['gallery_url'] . $row['filename'] . '" height="78" width="120" ')  . ' border="0" alt="' . $row['title'] . '" /></a><br />';
			echo '<span class="smalltext">' . $txt['gallery_text_views'] . $row['views'] . '<br />';
			echo $txt['gallery_text_filesize'] . gallery_format_size($row['filesize'], 2) . '<br />';
			echo $txt['gallery_text_date'] . timeformat($row['date']) . '<br />';
			echo $txt['gallery_text_comments'] . ' (<a href="' . $scripturl . '?action=gallery;sa=view&id=' . $row['ID_PICTURE'] . '">' . $row['commenttotal'] . '</a>)<br />';

			if ($row['realName'] != '')
				echo $txt['gallery_text_by'] . ' <a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">'  . $row['realName'] . '</a><br />';
			else
				echo $txt['gallery_text_by'], $txt['gallery_guest'],  '<br />';


			if ($g_manage)
				echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=unapprove&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_unapprove'] . '</a>';
			if ($g_manage || $g_edit_own && $row['ID_MEMBER'] == $ID_MEMBER)
				echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=edit&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_edit'] . '</a>';
			if ($g_manage || $g_delete_own && $row['ID_MEMBER'] == $ID_MEMBER)
				echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=delete&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_delete'] . '</a>';

			echo '</span></td>';


			if($rowlevel < ($maxrowlevel-1))
				$rowlevel++;
			else
			{
				echo '</tr>';
				$rowlevel = 0;
			}

			if ($styleclass == 'windowbg')
				$styleclass = 'windowbg2';
			else
				$styleclass = 'windowbg';




		}


		if ($rowlevel !=0)
		{
			echo '<td colspan="' . ($maxrowlevel - $rowlevel) . '"> </td>';

			echo '</tr>';
		}


		// Display who is viewing the picture.
		if (!empty($modSettings['gallery_who_viewing']))
		{
			echo '<tr class="' . $styleclass . '">
			<td align="center" colspan="' . $maxrowlevel . '"><span class="smalltext">';

			// Show just numbers...?
			// show the actual people viewing the gallery?
			echo empty($context['view_members_list']) ? '0 ' . $txt['gallery_who_members'] : implode(', ', $context['view_members_list']) . (empty($context['view_num_hidden']) || @$context['can_moderate_forum'] ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['gallery_who_hidden'] . ')');

			// Now show how many guests are here too.
			echo $txt['who_and'], @$context['view_num_guests'], ' ', @$context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['gallery_who_viewgallery'], '</span></td></tr>';
		}

			echo '<tr class="titlebg">
					<td align="left" colspan="' . $maxrowlevel . '">
					' . $txt['gallery_text_pages'];

					$context['page_index'] = constructPageIndex($scripturl . '?action=gallery;cat=' . $cat, $context['start'], $totalPics, $modSettings['gallery_set_images_per_page']);

					echo $context['page_index'];


			echo '
					</td>
				</tr>';


		// Show return to gallery link and Show add picture if they can
		echo '
				<tr class="titlebg"><td align="center" colspan="' . $maxrowlevel . '">';
				if($g_add)
				echo '<a href="' . $scripturl . '?action=gallery;sa=add;cat=' . $cat . '">' . $txt['gallery_text_addpicture'] .'</a><br />';

				echo '
				<a href="' . $scripturl . '?action=gallery">' . $txt['gallery_text_returngallery'] . '</a></td>
			</tr>';


		echo '</table><br />';

echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';

		// Footer padding
		echo '<br /><br />';

		GalleryCopyright();
}

function template_add_category()
{
	global $scripturl, $txt, $context, $settings;


	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';


	echo '
<form method="post" name="catform" id="catform" action="' . $scripturl . '?action=gallery&sa=addcat2" accept-charset="', $context['character_set'], '">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="50%" colspan="2" align="center" class="catbg">
    <b>', $txt['gallery_text_addcategory'], '</b></td>
  </tr>
  <tr>
    <td width="28%" class="windowbg2" align="right"><span class="gen"><b>' . $txt['gallery_form_title'] .'</b>&nbsp;</span></td>
    <td width="72%" class="windowbg2"><input type="text" name="title" size="64" maxlength="100" /></td>
  </tr>
  <tr>
    <td width="28%"  valign="top" class="windowbg2" align="right"><span class="gen"><b>' . $txt['gallery_form_description'] . '</b>&nbsp;</span></td>
    <td width="72%"  class="windowbg2"><table>
   ';
   theme_postbox('');
   echo '</table>';

   	if ($context['show_spellchecking'])
   		echo '
   									<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'catform\', \'description\');" />';
echo '</td>
  </tr>
  <tr>
    <td width="28%"  class="windowbg2" align="right"><span class="gen"><b>' . $txt['gallery_form_icon'] . '</b>&nbsp;</span></td>
    <td width="72%"  class="windowbg2"><input type="text" name="image" size="64" maxlength="100" /></td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="submit" value="' . $txt['gallery_text_addcategory'] . '" name="submit" /></td>

  </tr>
</table>
</form>';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';


	GalleryCopyright();
}

function template_edit_category()
{
	global $scripturl, $txt, $context, $settings;

	@$cat = (int) $_REQUEST['cat'];

	if (empty($cat))
		fatal_error($txt['gallery_error_no_cat']);


	$row = $context['gallery_row'];


	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';


	echo '
<form method="post" name="catform" id="catform" action="' . $scripturl . '?action=gallery&sa=editcat2" accept-charset="', $context['character_set'], '">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="50%" colspan="2" align="center" class="catbg">
    <b>' . $txt['gallery_text_editcategory'] . '</b></td>
  </tr>
  <tr>
    <td width="28%"  class="windowbg2" align="right"><span class="gen"><b>' . $txt['gallery_form_title'] . '</b>&nbsp;</span></td>
    <td width="72%"  class="windowbg2"><input type="text" name="title" size="64" maxlength="100" value="' . $row['title'] . '" /></td>
  </tr>
  <tr>
    <td width="28%"  valign="top" class="windowbg2" align="right"><span class="gen"><b>' . $txt['gallery_form_description'] . '</b>&nbsp;</span></td>
    <td width="72%"  class="windowbg2">
    <table>
   ';
   theme_postbox($row['description']);
   echo '</table>

   ';

   	if ($context['show_spellchecking'])
   		echo '
   									<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'catform\', \'description\');" />';
echo '</td>
  </tr>
  <tr>
    <td width="28%"  class="windowbg2" align="right"><span class="gen"><b>' . $txt['gallery_form_icon'] . '</b>&nbsp;</span></td>
    <td width="72%"  class="windowbg2"><input type="text" name="image" size="64" maxlength="100" value="' . $row['image'] . '" /></td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="hidden" value="' . $row['ID_CAT'] . '" name="catid" />
    <input type="submit" value="' . $txt['gallery_text_editcategory'] . '" name="submit" /></td>

  </tr>
</table>
</form>';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';


	GalleryCopyright();
}

function template_delete_category()
{
	global $scripturl, $txt, $context;

	@$catid = (int) $_REQUEST['cat'];

	if (empty($catid))
		fatal_error($txt['gallery_error_no_cat']);

	echo '
<form method="post" action="' . $scripturl . '?action=gallery&sa=deletecat2" accept-charset="', $context['character_set'], '">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="50%" colspan="2" align="center" class="catbg">
    <b>',$txt['gallery_text_delcategory'], '</b></td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <b>', $txt['gallery_warn_category'], '</b>
    <br />
    <input type="hidden" value="' . $catid . '" name="catid" />
    <input type="submit" value="' . $txt['gallery_text_delcategory'] . '" name="submit" /></td>
  </tr>
</table>
</form>
';

	GalleryCopyright();

}

function template_add_picture()
{
	global $scripturl, $modSettings, $txt, $context, $settings;
echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';


	echo '<table border="0" cellspacing="0" cellpadding="4" align="center" width="90%" class="tborder" >
					<tr class="titlebg">
						<td align="center">&nbsp;</td>
					</tr>
					</table>
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" >
						<table cellpadding="0" cellspacing="0" align="right">
									<tr>
						', DoToolBarStrip($context['gallery']['buttons'], 'top'), '
							</tr>
							</table>
						</td>
						</tr>
					</table>
				<br />';

	// Get the category
	@$cat = (int) $_REQUEST['cat'];


	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';


	echo '<form method="post" enctype="multipart/form-data" name="picform" id="picform" action="' . $scripturl . '?action=gallery&sa=add2" accept-charset="', $context['character_set'], '">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="catbg">
    <td width="50%" colspan="2" align="center">
    <b>' . $txt['gallery_form_addpicture'] . '</b></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_title'] . '</b>&nbsp;</td>
  	<td><input type="text" name="title" size="50" /></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_category'] . '</b>&nbsp;</td>
  	<td><select name="cat">';

 	foreach($context['gallery_cat_list'] as $row)
	{
		echo '<option value="' . $row['ID_CAT']  . '" ' . (($cat == $row['ID_CAT']) ? ' selected="selected"' : '') .'>' . $row['title'] . '</option>';
	}


 echo '</select>
  	</td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_description'] . '</b>&nbsp;</td>
  	<td><table>
   ';
   theme_postbox('');
   echo '</table></textarea>';

     	if ($context['show_spellchecking'])
     		echo '
     									<br /><input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'picform\', \'description\');" />';



echo '
  	</td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_keywords'] . '</b>&nbsp;</td>
  	<td><input type="text" name="keywords" maxlength="100" size="100" /></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right" valign="top"><b>' . $txt['gallery_form_uploadpic'] . '</b>&nbsp;</td>

    <td><input type="file" size="48" name="picture" />';

  if(!empty($modSettings['gallery_max_width']))
 	echo '<br />' . $txt['gallery_form_maxwidth'] .  $modSettings['gallery_max_width'] . $txt['gallery_form_pixels'];
  if(!empty($modSettings['gallery_max_height']))
  	echo '<br />' . $txt['gallery_form_maxheight'] .  $modSettings['gallery_max_height'] . $txt['gallery_form_pixels'];

 echo '
    </td>
  </tr>';

  if(!empty($modSettings['gallery_commentchoice']))
  {
	echo '
	   <tr class="windowbg2">
		<td align="right"><b>' . $txt['gallery_form_additionaloptions'] . '</b>&nbsp;</td>
		<td><input type="checkbox" name="allowcomments" checked="checked" /><b>' . $txt['gallery_form_allowcomments'] .'</b></td>
	  </tr>';
  }

echo '
  <tr class="windowbg2">
    <td width="28%" colspan="2"  align="center" class="windowbg2">

    <input type="submit" value="' . $txt['gallery_form_addpicture'] . '" name="submit" /><br />';

  	if(!allowedTo('smfgallery_autoapprove'))
  		echo $txt['gallery_form_notapproved'];

echo '
    </td>
  </tr>
</table>

		</form>
';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';

}

function template_edit_picture()
{
	global $scripturl, $modSettings, $txt, $context, $settings;


echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';


	echo '<table border="0" cellspacing="0" cellpadding="4" align="center" width="90%" class="tborder" >
					<tr class="titlebg">
						<td align="center">&nbsp;</td>
					</tr>
					</table>
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" >
						<table cellpadding="0" cellspacing="0" align="right">
									<tr>
						', DoToolBarStrip($context['gallery']['buttons'], 'top'), '
							</tr>
							</table>
						</td>
						</tr>
					</table>
				<br />';

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';


	echo '<form method="post" enctype="multipart/form-data" name="picform" id="picform" action="' . $scripturl . '?action=gallery&sa=edit2" accept-charset="', $context['character_set'], '">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="catbg">
    <td width="50%" colspan="2"  align="center">
    <b>' . $txt['gallery_form_editpicture'] . '</b></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_title'] . '</b>&nbsp;</td>
  	<td><input type="text" name="title" size="50" value="' . $context['gallery_pic']['title'] . '" /></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right"><b>' . $txt['gallery_form_category'] . '</b>&nbsp;</td>
  	<td><select name="cat">';

	foreach($context['gallery_cat_list'] as  $row)
	{
		echo '<option value="' . $row['ID_CAT']  . '" ' . (($context['gallery_pic']['ID_CAT'] == $row['ID_CAT']) ? ' selected="selected"' : '') .'>' . $row['title'] . '</option>';
	}

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
  	<td><input type="text" name="keywords" maxlength="100"  size="100" value="' . $context['gallery_pic']['keywords'] . '" /></td>
  </tr>
  <tr class="windowbg2">
  	<td align="right" valign="top"><b>' . $txt['gallery_form_uploadpic'] . '</b>&nbsp;</td>

    <td><input type="file" size="48" name="picture" />';

  if(!empty($modSettings['gallery_max_width']))
 	echo '<br />' . $txt['gallery_form_maxwidth'] .  $modSettings['gallery_max_width'] . $txt['gallery_form_pixels'];
  if(!empty($modSettings['gallery_max_height']))
  	echo '<br />' . $txt['gallery_form_maxheight'] .  $modSettings['gallery_max_height'] . $txt['gallery_form_pixels'];

 echo '
    </td>
  </tr>';

  if ($modSettings['gallery_commentchoice'])
  {
	echo '
	   <tr class="windowbg2">
		<td align="right"><b>' . $txt['gallery_form_additionaloptions'] . '</b>&nbsp;</td>
		<td><input type="checkbox" name="allowcomments" ' . ($context['gallery_pic']['allowcomments'] ? 'checked="checked"' : '' ) . ' /><b>',$txt['gallery_form_allowcomments'],'</b></td>
	  </tr>';
  }

echo '
  <tr class="windowbg2">
    <td width="28%" colspan="2" align="center" class="windowbg2">
	<input type="hidden" name="id" value="' . $context['gallery_pic']['ID_PICTURE'] . '" />
    <input type="submit" value="' . $txt['gallery_form_editpicture'] . '" name="submit" /><br />';

  	if (!allowedTo('smfgallery_autoapprove'))
  		echo $txt['gallery_form_notapproved'];

echo '<div align="center"><br /><b>' . $txt['gallery_text_oldpicture'] . '</b><br />
<a href="' . $scripturl . '?action=gallery;sa=view&id=' . $context['gallery_pic']['ID_PICTURE'] . '" target="blank"><img src="' . $modSettings['gallery_url'] . $context['gallery_pic']['thumbfilename']  . '" border="0" /></a><br />
			<span class="smalltext">' . $txt['gallery_text_views']  . $context['gallery_pic']['views'] . '<br />
			' . $txt['gallery_text_filesize']  . gallery_format_size($context['gallery_pic']['filesize'],2) . '<br />
			' . $txt['gallery_text_date'] . $context['gallery_pic']['date'] . '<br />
	</div>
    </td>
  </tr>
</table>

		</form>
';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';


}

function template_view_picture()
{
	global $scripturl, $context, $txt, $ID_MEMBER, $modSettings, $memberContext;

	// Load permissions
	$g_manage = allowedTo('smfgallery_manage');
	$g_edit_own = allowedTo('smfgallery_edit');
	$g_delete_own = allowedTo('smfgallery_delete');

	$previousImage = PreviousImage($context['gallery_pic']['ID_PICTURE'],$context['gallery_pic']['ID_CAT'],true);
	$nextImage = NextImage($context['gallery_pic']['ID_PICTURE'],$context['gallery_pic']['ID_CAT'],true);


	// Keywords
	$keywords = explode(' ',$context['gallery_pic']['keywords']);
 	$keywordscount = count($keywords);


echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';


	echo '<table border="0" cellspacing="0" cellpadding="4" align="center" width="90%" class="tborder" >
					<tr class="titlebg">
						<td align="center">&nbsp;</td>
					</tr>
					</table>
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" >
						<table cellpadding="0" cellspacing="0" align="right">
									<tr>
						', DoToolBarStrip($context['gallery']['buttons'], 'top'), '
							</tr>
							</table>
						</td>
						</tr>
					</table>
				<br />';



	echo '<br /><table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
			<tr class="catbg">
				<td align="center">' . $context['gallery_pic']['title'] . '</td>
			</tr>
			<tr class="windowbg2">
				<td align="center"><img height="' . $context['gallery_pic']['height']  . '" width="' . $context['gallery_pic']['width']  . '" src="' . $modSettings['gallery_url'] . $context['gallery_pic']['filename']  . '" alt="' . $context['gallery_pic']['title']  . '" /></td>
			</tr>

				<tr class="windowbg2">
			<td align="center"><b>';
			$showSpacer = false;
			if ($previousImage != $context['gallery_pic']['ID_PICTURE'])
			{
				$showSpacer = true;
				echo '<a href="', $scripturl, '?action=gallery;sa=prev&id=', $context['gallery_pic']['ID_PICTURE'], '">', $txt['gallery_text_prev'], '</a>';
			}

			if ($nextImage != $context['gallery_pic']['ID_PICTURE'])
			{
				if ($showSpacer == true)
					echo ' | ';
				echo '<a href="', $scripturl, '?action=gallery;sa=next&id=', $context['gallery_pic']['ID_PICTURE'], '">', $txt['gallery_text_next'], '</a>';

			}
				echo '</b>
			</td>
			</tr>

			<tr class="windowbg2">

				<td>
				<b>' . $txt['gallery_form_description'] . ' </b>' . (parse_bbc($context['gallery_pic']['description'])  ). '
				<hr />
				' . $txt['gallery_text_picstats'] . '<br />

				' . $txt['gallery_text_views'] . $context['gallery_pic']['views'] . '<br />
				' . $txt['gallery_text_filesize']  . gallery_format_size($context['gallery_pic']['filesize'],2) . '<br />
				'  . $txt['gallery_text_height'] . ' ' . $context['gallery_pic']['height']  . '  ' . $txt['gallery_text_width'] . ' ' . $context['gallery_pic']['width'] . '<br />
				';

				if (!empty($context['gallery_pic']['keywords']))
				{

					echo $txt['gallery_form_keywords'] . ' ';
					for($i = 0; $i < $keywordscount;$i++)
					{
						echo '<a href="' . $scripturl . '?action=gallery;sa=search2;key=' . $keywords[$i] . '">' . $keywords[$i] . '</a>&nbsp;';
					}
					echo '<br />';

				}

				if ($context['gallery_pic']['realName'] != '')
					echo $txt['gallery_text_postedby'] . '<a href="' . $scripturl . '?action=profile;u=' . $context['gallery_pic']['ID_MEMBER'] . '">'  . $context['gallery_pic']['realName'] . '</a> ' . $txt['gallery_at'] . $context['gallery_pic']['date'] . '<br /><br />';
				else
					echo $txt['gallery_text_postedby'] . $txt['gallery_guest']  . $txt['gallery_at'] . $context['gallery_pic']['date'] . '<br /><br />';


				// Show image linking codes
				if ($modSettings['gallery_set_showcode_bbc_image']  || $modSettings['gallery_set_showcode_directlink'] || $modSettings['gallery_set_showcode_htmllink'])
				{
					echo '<b>',$txt['gallery_txt_image_linking'],'</b><br />
					<table border="0">
					';

					if ($modSettings['gallery_set_showcode_bbc_image'])
					{
						echo '<tr><td width="30%">', $txt['gallery_txt_bbcimage'], '</td><td> <input type="text" value="[img]' . $modSettings['gallery_url'] . $context['gallery_pic']['filename']  . '[/img]" size="50" /></td></tr>';
					}
					if ($modSettings['gallery_set_showcode_directlink'])
					{
						echo '<tr><td width="30%">', $txt['gallery_txt_directlink'], '</td><td> <input type="text" value="' . $modSettings['gallery_url'] . $context['gallery_pic']['filename']  . '" size="50" /></td></tr>';
					}
					if ($modSettings['gallery_set_showcode_htmllink'])
					{
						echo '<tr><td width="30%">', $txt['gallery_set_showcode_htmllink'], '</td><td> <input type="text" value="<img src=&#34;' . $modSettings['gallery_url'] . $context['gallery_pic']['filename']  . '&#34; />" size="50" /></td></tr>';
					}

					echo '</table>';

				}

				// Show edit picture links if allowed

				if ($g_manage)
					echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=unapprove&id=' . $context['gallery_pic']['ID_PICTURE'] . '">' . $txt['gallery_text_unapprove'] . '</a>';
				if ($g_manage || $g_edit_own && $context['gallery_pic']['ID_MEMBER'] == $ID_MEMBER)
					echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=edit&id=' . $context['gallery_pic']['ID_PICTURE']. '">' . $txt['gallery_text_edit'] . '</a>';
				if ($g_manage || $g_delete_own && $context['gallery_pic']['ID_MEMBER'] == $ID_MEMBER)
					echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=delete&id=' . $context['gallery_pic']['ID_PICTURE'] . '">' . $txt['gallery_text_delete'] . '</a>';


				// Show report picture link
				if (allowedTo('smfgallery_report'))
				{
					echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=report&id=' . $context['gallery_pic']['ID_PICTURE'] . '">' . $txt['gallery_text_reportpicture'] . '</a>';
				}

				echo '
				</td>
			</tr>';

		// Display who is viewing the picture.
		if (!empty($modSettings['gallery_who_viewing']))
		{
			echo '<tr>
			<td align="center" class="windowbg2"><span class="smalltext">';

			// Show just numbers...?
			// show the actual people viewing the topic?
			echo empty($context['view_members_list']) ? '0 ' . $txt['gallery_who_members'] : implode(', ', $context['view_members_list']) . (empty($context['view_num_hidden']) || $context['can_moderate_forum'] ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['gallery_who_hidden'] . ')');

			// Now show how many guests are here too.
			echo $txt['who_and'], @$context['view_num_guests'], ' ', @$context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['gallery_who_viewpicture'], '</span></td></tr>';
		}

echo '
		</table><br />';
	//Check if allowed to display comments for this picture
	if ($context['gallery_pic']['allowcomments'])
	{

       $comment_count = $context['gallery_comment_count'];
		// Show comments
		echo '<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
				<tr class="catbg">
					<td colspan="2">' . $txt['gallery_text_comments'] . ' (' . $comment_count . ')</td>
				</tr>';

		if (allowedTo('smfgallery_comment'))
		{
			//Show Add Comment
			echo '
				<tr class="titlebg"><td colspan="2">
				<a href="' . $scripturl . '?action=gallery;sa=comment&id=' . $context['gallery_pic']['ID_PICTURE'] . '">' . $txt['gallery_text_addcomment']  . '</a></td>
				</tr>';
		}

		// Display all user comments


		foreach($context['gallery_comment_list'] as $row)
		{
			echo '<tr class="windowbg">';
			// Display member info
			echo '<td width="10%" valign="top">';

			if ($row['realName'] != '')
			{
				echo '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">'  . $row['realName'] . '</a><br />
				<span class="smalltext">Posts: ' . $row['posts'] . '</span><br />';
				// Display the users avatar
	            $memCommID = $row['ID_MEMBER'];
	            loadMemberData($memCommID);
				loadMemberContext($memCommID);


				echo $memberContext[$memCommID]['avatar']['image'];
			}
			else
				echo $txt['gallery_guest'] . '<br />';


			echo '
			</td>';
			// Display the comment
			echo '<td width="90%"><span class="smalltext">' . timeformat($row['date']) . '</span><hr />';

			echo   parse_bbc($row['comment']);

			// Check if the user is allowed to delete the comment.
			if($g_manage)
				echo '<br /><a href="' . $scripturl . '?action=gallery;sa=delcomment&id=' . $row['ID_COMMENT'] . '">' . $txt['gallery_text_delcomment'] .'</a>';


			echo '</td>';
			echo '</tr>';
		}



		// Show Add Comment link again if there are more than one comment
		if( allowedTo('smfgallery_comment') && $comment_count != 0)
		{
		 // Show Add Comment
			echo '
				<tr class="titlebg">
					<td colspan="2">
					<a href="' . $scripturl . '?action=gallery;sa=comment&id=' . $context['gallery_pic']['ID_PICTURE'] . '">' . $txt['gallery_text_addcomment'] . '</a>
					</td>
				</tr>';
		}

		echo '</table><br />';
	}

echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';

	// Link back to the gallery index
	echo '<div align="center"><a href="', $scripturl, '?action=gallery">' . $txt['gallery_text_returngallery'] . '</a></div><br />';



	GalleryCopyright();
}

function template_delete_picture()
{
	global $scripturl, $modSettings, $txt, $context;

echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';


	echo '<table border="0" cellspacing="0" cellpadding="4" align="center" width="90%" class="tborder" >
					<tr class="titlebg">
						<td align="center">&nbsp;</td>
					</tr>
					</table>
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" >
						<table cellpadding="0" cellspacing="0" align="right">
									<tr>
						', DoToolBarStrip($context['gallery']['buttons'], 'top'), '
							</tr>
							</table>
						</td>
						</tr>
					</table>
				<br />';

	echo '
	<form method="post" action="' . $scripturl . '?action=gallery&sa=delete2" accept-charset="', $context['character_set'], '">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr class="catbg">
    <td width="50%" colspan="2" align="center">
    <b>' . $txt['gallery_form_delpicture'] . '</b></td>
  </tr>
  <tr class="windowbg2">
    <td width="28%" colspan="2" align="center" class="windowbg2">
	' . $txt['gallery_warn_deletepicture'] . '
	<br />
<div align="center"><br /><b>' . $txt['gallery_form_delpicture'] . '</b><br />
<a href="' . $scripturl . '?action=gallery;sa=view&id=' . $context['gallery_pic']['ID_PICTURE'] . '" target="blank"><img src="' . $modSettings['gallery_url'] . $context['gallery_pic']['thumbfilename']  . '" border="0" /></a><br />
			<span class="smalltext">' . $txt['gallery_text_views'] . $context['gallery_pic']['views'] . '<br />
			' . $txt['gallery_text_filesize']  . gallery_format_size($context['gallery_pic']['filesize'],2) . '<br />
			' . $txt['gallery_text_date'] . $context['gallery_pic']['date'] . '<br />
			' . $txt['gallery_text_comments'] . ' (<a href="' . $scripturl . '?action=gallery;sa=view&id=' .  $context['gallery_pic']['ID_PICTURE'] . '" target="blank">' .  $context['gallery_pic']['commenttotal'] . '</a>)<br />
	</div><br />
	<input type="hidden" name="id" value="' . $context['gallery_pic']['ID_PICTURE'] . '" />
    <input type="submit" value="' . $txt['gallery_form_delpicture'] . '" name="submit" /><br />
    </td>
  </tr>
</table>

		</form>
';

	GalleryCopyright();
}

function template_add_comment()
{
	global $context, $scripturl, $txt,$settings;

echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';


	echo '<table border="0" cellspacing="0" cellpadding="4" align="center" width="90%" class="tborder" >
					<tr class="titlebg">
						<td align="center">&nbsp;</td>
					</tr>
					</table>
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" >
						<table cellpadding="0" cellspacing="0" align="right">
									<tr>
						', DoToolBarStrip($context['gallery']['buttons'], 'top'), '
							</tr>
							</table>
						</td>
						</tr>
					</table>
				<br />';

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';


	echo '
<form method="post" name="cprofile" id="cprofile" action="' . $scripturl . '?action=gallery&sa=comment2" accept-charset="', $context['character_set'], '">
<table border="0" cellpadding="0" cellspacing="0" width="100%" >
  <tr>
    <td width="50%" colspan="2" align="center" class="catbg">
    <b>' . $txt['gallery_text_addcomment'] . '</b></td>
  </tr>


  <tr>
    <td width="28%"  valign="top" class="windowbg2" align="right"><span class="gen"><b>' . $txt['gallery_form_comment'] . '</b>&nbsp;</span></td>
    <td width="72%"  class="windowbg2"><table>
   ';
   theme_postbox('');
   echo '</table></textarea></td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="hidden" name="id" value="' . $context['gallery_pic_id'] . '" />';
   	if ($context['show_spellchecking'])
   		echo '
   									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'cprofile\', \'comment\');" />';

echo '
    <input type="submit" value="' . $txt['gallery_text_addcomment'] . '" name="submit" /></td>

  </tr>
</table>
</form>';


	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';




	GalleryCopyright();
}

function template_report_picture()
{
	global $scripturl, $context, $txt;

	echo '
<form method="post" name="cprofile" id="cprofile" action="' . $scripturl . '?action=gallery;sa=report2" accept-charset="', $context['character_set'], '">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="50%" colspan="2" align="center" class="catbg">
    <b>' . $txt['gallery_form_reportpicture'] . '</b></td>
  </tr>
  <tr>
    <td width="28%"  valign="top" class="windowbg2" align="right"><span class="gen"><b>' . $txt['gallery_form_comment'] . '</b>&nbsp;</span></td>
    <td width="72%" class="windowbg2"><textarea rows="6" name="comment" cols="54"></textarea></td>
  </tr>
  <tr>
    <td width="28%" colspan="2"  align="center" class="windowbg2">
    <input type="hidden" name="id" value="' . $context['gallery_pic_id'] . '" />
    <input type="submit" value="' . $txt['gallery_form_reportpicture'] . '" name="submit" /></td>

  </tr>
</table>
</form>';

	GalleryCopyright();
}

function template_manage_cats()
{
	global $scripturl, $txt, $context;

echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>' . $txt['gallery_form_managecats'] . '</td>
		</tr>

		<tr class="windowbg">
			<td>
			<br />';


		// List all the catagories
		echo '<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
				<tr class="titlebg">
				<td>', $txt['gallery_text_galleryname'], '</td>
				<td>', $txt['gallery_text_gallerydescription'], '</td>
				<td>', $txt['gallery_text_totalimages'], '</td>
				<td>', $txt['gallery_text_reorder'], '</td>
				<td>', $txt['gallery_text_options'], '</td>
				</tr>';
        $styleclass = 'windowbg';
		foreach($context['gallery_manage_cats'] as $row)
		{

			$totalpics = GetTotalPicturesBYCATID($row['ID_CAT']);

			echo '<tr class="' . $styleclass . '">';

			echo '<td><a href="', $scripturl, '?action=gallery;cat=', $row['ID_CAT'], '">' . parse_bbc($row['title']) . '</a></td><td>' . nl2br(parse_bbc($row['description'])) . '</td>';



			echo '<td align="center">', $totalpics, '</td>';

			// Show Edit Delete and Order category
			echo '<td><a href="', $scripturl, '?action=gallery;sa=catup;cat=' . $row['ID_CAT'] . '">' . $txt['gallery_text_up'] . '</a>&nbsp;<a href="' . $scripturl . '?action=gallery;sa=catdown;cat=' . $row['ID_CAT'] . '">' . $txt['gallery_text_down'] . '</a></td><td><a href="' . $scripturl . '?action=gallery;sa=editcat;cat=' . $row['ID_CAT'] . '">' . $txt['gallery_text_edit'] .'</a>&nbsp;<a href="' . $scripturl . '?action=gallery;sa=deletecat;cat=' . $row['ID_CAT'] . '">' . $txt['gallery_text_delete'] .'</a>
			<br />
			<a href="' . $scripturl . '?action=gallery;sa=regen;cat=' .  $row['ID_CAT'] . '">' . $txt['gallery_text_regeneratethumbnails'] . '</a>
			</td>
            </tr>';

            if ($styleclass == 'windowbg')
				    $styleclass = 'windowbg2';
			else
    				$styleclass = 'windowbg';

		}


	echo '
			<tr class="windowbg2">
				<td colspan="5" align="center"><a href="', $scripturl, '?action=gallery;sa=addcat">' . $txt['gallery_text_addcategory'] . '</a></td>
			</tr>
	</table><br />
			</td>
		</tr>
	<tr class="windowbg"><td><b>Has SMF Gallery helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="SMF Gallery">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
<br />You can also upgrade to the SMF Gallery Pro edition at <a href="http://www.smfhacks.com/smf-gallery-pro.php" target="blank">http://www.smfhacks.com/smf-gallery-pro.php</a>
</td>
</tr>
</table>';

	GalleryCopyright();
}

function template_settings()
{
	global $scripturl, $modSettings, $txt, $context;

echo '

	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>' . $txt['gallery_text_settings'] . '</td>
		</tr>

		<tr class="windowbg">
			<td>
			<b>' . $txt['gallery_text_settings'] . '</b> - <span class="smalltext">' . $txt['gallery_set_description'] . '</span><br />
			<form method="post" action="' . $scripturl . '?action=gallery;sa=adminset2" accept-charset="', $context['character_set'], '">

			<table  border="0" width="100%" cellspacing="0"  align="center" cellpadding="4">


				<tr class="windowbg2"><td>' . $txt['gallery_set_path'] . '</td><td><input type="text" name="gallery_path" value="' .  $modSettings['gallery_path'] . '" size="50" /></td></tr>
				<tr class="windowbg"><td>' . $txt['gallery_set_url'] . '</td><td><input type="text" name="gallery_url" value="' .  $modSettings['gallery_url'] . '" size="50" /></td></tr>

				<tr class="windowbg"><td>' . $txt['gallery_set_maxheight'] . '</td><td><input type="text" name="gallery_max_height" value="' .  $modSettings['gallery_max_height'] . '" /></td></tr>
				<tr class="windowbg2"><td>' . $txt['gallery_set_maxwidth'] . '</td><td><input type="text" name="gallery_max_width" value="' .  $modSettings['gallery_max_width'] . '" /></td></tr>

				<tr class="windowbg"><td>' . $txt['gallery_set_thumb_height'] . '</td><td><input type="text" name="gallery_thumb_height" value="' .  $modSettings['gallery_thumb_height'] . '" /></td></tr>
				<tr class="windowbg2"><td>' . $txt['gallery_set_thumb_width'] . '</td><td><input type="text" name="gallery_thumb_width" value="' .  $modSettings['gallery_thumb_width'] . '" /></td></tr>


				<tr class="windowbg"><td>' . $txt['gallery_set_filesize'] . '</td><td><input type="text" name="gallery_max_filesize" value="' .  $modSettings['gallery_max_filesize'] . '" /> (bytes)</td></tr>
				<tr class="windowbg2"><td width="30%">' . $txt['gallery_upload_max_filesize'] . '</td><td><a href="http://www.php.net/manual/en/ini.core.php#ini.upload-max-filesize" target="_blank">' . @ini_get("upload_max_filesize") . '</a></td></tr>
				<tr class="windowbg"><td width="30%">' . $txt['gallery_post_max_size'] . '</td><td><a href="http://www.php.net/manual/en/ini.core.php#ini.post-max-size" target="_blank">' . @ini_get("post_max_size") . '</a></td></tr>
				<tr class="windowbg2"><td colspan="2">',$txt['gallery_upload_limits_notes'] ,'</td></tr>

				<tr class="windowbg"><td>' . $txt['gallery_set_images_per_page'] . '</td><td><input type="text" name="gallery_set_images_per_page" value="' .  $modSettings['gallery_set_images_per_page'] . '" /></td></tr>
				<tr class="windowbg2"><td>' . $txt['gallery_set_images_per_row'] . '</td><td><input type="text" name="gallery_set_images_per_row" value="' .  $modSettings['gallery_set_images_per_row'] . '" /></td></tr>


				<tr class="windowbg2"><td colspan="2"><input type="checkbox" name="gallery_who_viewing" ' . ($modSettings['gallery_who_viewing'] ? ' checked="checked" ' : '') . ' />' . $txt['gallery_set_whoonline'] . '</td></tr>
				';

				if (!is_writable($modSettings['gallery_path']))
					echo '<tr class="windowbg"><td colspan="2"><font color="#FF0000"><b>' . $txt['gallery_write_error']  . $modSettings['gallery_path'] . '</b></font></td></tr>';

				echo '
				<tr class="windowbg"><td colspan="2"><input type="checkbox" name="gallery_commentchoice" ' . (!empty($modSettings['gallery_commentchoice']) ? ' checked="checked" ' : '') . ' />' . $txt['gallery_set_commentschoice'] . '</td></tr>
				<tr class="windowbg2"><td colspan="2">' . $txt['gallery_shop_settings'] . '</td></tr>
				<tr class="windowbg"><td>' . $txt['gallery_shop_picadd'] . '</td><td><input type="text" name="gallery_shop_picadd" value="' .  $modSettings['gallery_shop_picadd'] . '" /></td></tr>
				<tr class="windowbg2"><td>' . $txt['gallery_shop_commentadd'] . '</td><td><input type="text" name="gallery_shop_commentadd" value="' .  $modSettings['gallery_shop_commentadd'] . '" /></td></tr>

				<tr class="windowbg2"><td colspan="2"><b>' . $txt['gallery_txt_image_linking'] . '</b></td></tr>
				<tr class="windowbg"><td colspan="2"><input type="checkbox" name="gallery_set_showcode_bbc_image" ' . ($modSettings['gallery_set_showcode_bbc_image'] ? ' checked="checked" ' : '') . ' />' . $txt['gallery_set_showcode_bbc_image'] . '</td></tr>
				<tr class="windowbg2"><td colspan="2"><input type="checkbox" name="gallery_set_showcode_directlink" ' . ($modSettings['gallery_set_showcode_directlink'] ? ' checked="checked" ' : '') . ' />' . $txt['gallery_set_showcode_directlink'] . '</td></tr>
				<tr class="windowbg"><td colspan="2"><input type="checkbox" name="gallery_set_showcode_htmllink" ' . ($modSettings['gallery_set_showcode_htmllink'] ? ' checked="checked" ' : '') . ' />' . $txt['gallery_set_showcode_htmllink'] . '</td></tr>



				<tr class="windowbg2"><td colspan="2"><input type="submit" name="savesettings" value="',$txt['gallery_save_settings'],'" /></td></tr>
				</table>
			</form>
			<br />
			<b>' . $txt['gallery_text_permissions'] . '</b><br/><span class="smalltext">' . $txt['gallery_set_permissionnotice'] . '</span>
			<br /><a href="' . $scripturl . '?action=permissions">' . $txt['gallery_set_editpermissions']  . '</a>

			</td>
		</tr>
<tr class="windowbg"><td><b>Has SMF Gallery helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="SMF Gallery">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<br />You can also upgrade to the SMF Gallery Pro edition at <a href="http://www.smfhacks.com/smf-gallery-pro.php" target="blank">http://www.smfhacks.com/smf-gallery-pro.php</a>


			<br />


</td>
</tr>
</table>';

	GalleryCopyright();
}

function template_approvelist()
{
	global $scripturl, $modSettings, $txt, $context;


	// Check if GD is installed if not we will not show the thumbnails
	$GD_Installed = function_exists('imagecreate');

echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>' . $txt['gallery_form_approveimages'] . '</td>
		</tr>

		<tr class="windowbg">
			<td>
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
				<tr class="catbg">
				<td>' . $txt['gallery_app_image'] . '</td>
				<td>' . $txt['gallery_app_title'] . '</td>
				<td>' . $txt['gallery_app_description'] . '</td>
				<td>' . $txt['gallery_app_date'] . '</td>
				<td>' . $txt['gallery_app_membername'] . '</td>
				<td>' . $txt['gallery_text_options'] . '</td>
				</tr>';

			// List all the unapproved pictures
            $styleclass = 'windowbg';
			foreach($context['gallery_approve_list'] as $row)
			{

				echo '<tr class="' . $styleclass . '">';
				echo '<td><a href="' . $scripturl . '?action=gallery;sa=view&id=' . $row['ID_PICTURE'] . '">
				<img ' . ($GD_Installed == true ?  'src="' . $modSettings['gallery_url'] . $row['thumbfilename'] . '" ' : 'src="' . $modSettings['gallery_url'] . $row['filename'] . '" height="78" width="120" ')  . ' border="0" /></a></td>';
				echo '<td>' . $row['title'] . '</td>';
				echo '<td>' . $row['description'] . '</td>';
				echo '<td>' . timeformat($row['date']) . '</td>';
				if ($row['realName'] != '')
					echo '<td><a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">'  . $row['realName'] . '</a></td>';
				else
						echo '<td>' . $txt['gallery_guest'] . '</td>';

				echo '<td><a href="' . $scripturl . '?action=gallery;sa=approve&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_approve']  . '</a><br /><a href="' . $scripturl . '?action=gallery;sa=edit&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_edit'] . '</a><br /><a href="' . $scripturl . '?action=gallery;sa=delete&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_delete'] . '</a></td>';
				echo '</tr>';

                if ($styleclass == 'windowbg')
				    $styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';

			}


echo '
			</table>
			</td>
		</tr>
<tr class="windowbg"><td><b>Has SMF Gallery helped you?</b> <br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="SMF Gallery">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
<br />You can also upgrade to the SMF Gallery Pro edition at <a href="http://www.smfhacks.com/smf-gallery-pro.php" target="blank">http://www.smfhacks.com/smf-gallery-pro.php</a>
</td>
</tr>
</table>';

	GalleryCopyright();
}

function template_reportlist()
{
	global $scripturl, $txt, $context;
echo '

	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>' . $txt['gallery_form_reportimages'] . '</td>
		</tr>

		<tr class="windowbg">
			<td>
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
				<tr class="catbg">
				<td>' . $txt['gallery_rep_piclink'] . '</td>
				<td>' . $txt['gallery_rep_comment']  . '</td>
				<td>' . $txt['gallery_app_date'] . '</td>
				<td>' . $txt['gallery_rep_reportby'] . '</td>
				<td>' . $txt['gallery_text_options'] . '</td>
				</tr>';

			// List all reported pictures
            $styleclass = 'windowbg';
			foreach($context['gallery_report_list'] as $row)
			{

				echo '<tr class="' . $styleclass . '">';
				echo '<td><a href="' . $scripturl . '?action=gallery;sa=view&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_rep_viewpic'] .'</a></td>';
				echo '<td>' . $row['comment'] . '</td>';
				echo '<td>' . timeformat($row['date']) . '</td>';
				if ($row['realName'] != '')
					echo '<td><a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">'  . $row['realName'] . '</a></td>';
				else
					echo '<td>' .  $txt['gallery_guest'] . '</td>';

				echo '<td><a href="' . $scripturl . '?action=gallery;sa=delete&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_rep_deletepic']  . '</a>';
				echo '<br /><a href="' . $scripturl . '?action=gallery;sa=deletereport&id=' . $row['ID'] . '">' . $txt['gallery_rep_delete'] . '</a></td>';
				echo '</tr>';

                if ($styleclass == 'windowbg')
				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';

			}


echo '
			</table>
			</td>
		</tr>
<tr class="windowbg"><td><b>Has SMF Gallery helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="sales@visualbasiczone.com" />
	<input type="hidden" name="item_name" value="SMF Gallery" />
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="no_note" value="1" />
	<input type="hidden" name="currency_code" value="USD" />
	<input type="hidden" name="tax" value="0" />
	<input type="hidden" name="bn" value="PP-DonationsBF" />
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
<br />You can also upgrade to the SMF Gallery Pro edition at <a href="http://www.smfhacks.com/smf-gallery-pro.php" target="blank">http://www.smfhacks.com/smf-gallery-pro.php</a>
</td>
</tr>
</table>';

	GalleryCopyright();
}

function template_search()
{
	global $scripturl, $txt, $context;


echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';


	echo '<table border="0" cellspacing="0" cellpadding="4" align="center" width="90%" class="tborder" >
					<tr class="titlebg">
						<td align="center">&nbsp;</td>
					</tr>
					</table>
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" >
						<table cellpadding="0" cellspacing="0" align="right">
									<tr>
						', DoToolBarStrip($context['gallery']['buttons'], 'top'), '
							</tr>
							</table>
						</td>
						</tr>
					</table>
				<br />';


	echo '
<form method="post" action="' . $scripturl . '?action=gallery;sa=search2" accept-charset="', $context['character_set'], '">
<table border="0" cellpadding="0" cellspacing="0" width="50%"  class="tborder" align="center">
  <tr>
    <td width="100%" colspan="2" align="center" class="catbg">
    <b>', $txt['gallery_search_pic'], '</b></td>
  </tr>
  <tr class="windowbg2">
    <td width="50%"  align="right"><b>' . $txt['gallery_search_for'] . '</b>&nbsp;</td>
    <td width="50%"><input type="text" name="searchfor" />
    </td>
  </tr>
  <tr class="windowbg2" align="center">
  	<td colspan="2"><input type="checkbox" name="searchtitle" checked="checked" />', $txt['gallery_search_title'], '&nbsp;<input type="checkbox" name="searchdescription" checked="checked" />' . $txt['gallery_search_description'] . '<br />
  	<input type="checkbox" name="searchkeywords" />', $txt['gallery_search_keyword'], '</td>
  </tr>
  <tr>
    <td width="100%" colspan="2" align="center" class="windowbg2">
    <input type="submit" value="', $txt['gallery_search'], '" name="submit" /></td>

  </tr>
</table>
</form>';

echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';

	GalleryCopyright();

}

function template_search_results()
{
	global $context, $ID_MEMBER, $modSettings, $scripturl, $txt;

	//Get the permissions for the user
	$g_add = allowedTo('smfgallery_add');
	$g_manage = allowedTo('smfgallery_manage');
	$g_edit_own = allowedTo('smfgallery_edit');
	$g_delete_own = allowedTo('smfgallery_delete');


	// Check if GD is installed if not we will not show the thumbnails
	$GD_Installed = function_exists('imagecreate');


echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';


	echo '<table border="0" cellspacing="0" cellpadding="4" align="center" width="90%" class="tborder" >
					<tr class="titlebg">
						<td align="center">&nbsp;</td>
					</tr>
					</table>
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" >
						<table cellpadding="0" cellspacing="0" align="right">
									<tr>
						', DoToolBarStrip($context['gallery']['buttons'], 'top'), '
							</tr>
							</table>
						</td>
						</tr>
					</table>
				<br />';


	$maxrowlevel = $modSettings['gallery_set_images_per_row'];
	echo '<br /><table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
					<tr class="catbg">
						<td align="center" colspan="' . $maxrowlevel . '">' . $txt['gallery_searchresults'] . '</td>
					</tr>';

	$rowlevel = 0;

    $styleclass = 'windowbg';

  	foreach($context['gallery_search_results'] as $row)
	{
			if ($rowlevel == 0)
				echo '<tr class="' . $styleclass . '">';

			echo '<td align="center"><a href="' . $scripturl . '?action=gallery;sa=view&id=' . $row['ID_PICTURE'] . '">
			<img ' . ($GD_Installed == true ?  'src="' . $modSettings['gallery_url'] . $row['thumbfilename'] . '" ' : 'src="' . $modSettings['gallery_url'] . $row['filename'] . '" height="78" width="120" ')  . ' border="0" alt="' . $row['title'] . '" /></a><br />';
			echo '<span class="smalltext">' . $txt['gallery_text_views'] . $row['views'] . '<br />';
			echo $txt['gallery_text_filesize'] . gallery_format_size($row['filesize'], 2) . '<br />';
			echo $txt['gallery_text_date'] . timeformat($row['date']) . '<br />';
			echo $txt['gallery_text_comments'] . ' (<a href="' . $scripturl . '?action=gallery;sa=view&id=' . $row['ID_PICTURE'] . '">' . $row['commenttotal'] . '</a>)<br />';
			if ($row['realName'] != '')
				echo $txt['gallery_text_by'] . ' <a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">'  . $row['realName'] . '</a><br />';
			else
				echo $txt['gallery_text_by'] . $txt['gallery_guest'] . '<br />';
			if ($g_manage)
				echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=unapprove&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_unapprove'] . '</a>';
			if ($g_manage || $g_edit_own && $row['ID_MEMBER'] == $ID_MEMBER)
				echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=edit&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_edit'] . '</a>';
			if ($g_manage || $g_delete_own && $row['ID_MEMBER'] == $ID_MEMBER)
				echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=delete&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_delete'] . '</a>';

			echo '</span></td>';


			if ($rowlevel < ($maxrowlevel-1))
				$rowlevel++;
			else
			{
				echo '</tr>';
				$rowlevel = 0;
			}


			if ($styleclass == 'windowbg')
				$styleclass = 'windowbg2';
			else
				$styleclass = 'windowbg';


	}
		if($rowlevel !=0)
		{
			echo '<td colspan="' . ($maxrowlevel - $rowlevel) . '"> </td>';
			echo '</tr>';
		}


		// Show return to gallery link and Show add picture if they can
		echo '
				<tr class="titlebg"><td align="center" colspan="' . $maxrowlevel . '">';
				if ($g_add)
				echo '<a href="' . $scripturl . '?action=gallery;sa=add">' . $txt['gallery_text_addpicture'] . '</a><br />';

				echo '
				<a href="' . $scripturl . '?action=gallery">' . $txt['gallery_text_returngallery'] . '</a></td>
			</tr>';

		echo '</table>';

echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';

	GalleryCopyright();
}

function template_myimages()
{
	global $context, $ID_MEMBER, $modSettings, $scripturl, $txt;

	//Get the permissions for the user
	$g_add = allowedTo('smfgallery_add');
	$g_manage = allowedTo('smfgallery_manage');
	$g_edit_own = allowedTo('smfgallery_edit');
	$g_delete_own = allowedTo('smfgallery_delete');


	// Check if GD is installed if not we will not show the thumbnails
	$GD_Installed = function_exists('imagecreate');


echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';


	echo '<table border="0" cellspacing="0" cellpadding="4" align="center" width="90%" class="tborder" >
					<tr class="titlebg">
						<td align="center">&nbsp;</td>
					</tr>
					</table>
				<table border="0" cellpadding="0" cellspacing="0" align="center" width="90%">
						<tr>
							<td style="padding-right: 1ex;" align="right" >
						<table cellpadding="0" cellspacing="0" align="right">
									<tr>
						', DoToolBarStrip($context['gallery']['buttons'], 'top'), '
							</tr>
							</table>
						</td>
						</tr>
					</table>
				<br />';


	$maxrowlevel =  $modSettings['gallery_set_images_per_row'];
	echo '<br /><table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
					<tr class="catbg">
						<td align="center" colspan="' . $maxrowlevel . '">' . $context['gallery_usergallery_name'] . '</td>
					</tr>';

	$rowlevel = 0;
	$userid = $context['gallery_userid'];


	$context['start'] = (int) $_REQUEST['start'];


	$totalPics = $context['gallery_totalpic'];
	// Check if it is the user ids gallery mainly to show unapproved pictures or not

    $styleclass = 'windowbg';

	foreach($context['gallery_myimages'] as $row)
	{
			if($rowlevel == 0)
				echo '<tr class="' . $styleclass . '">';

			echo '<td align="center"><a href="' . $scripturl . '?action=gallery;sa=view&id=' . $row['ID_PICTURE'] . '">
			<img ' . ($GD_Installed == true ?  'src="' . $modSettings['gallery_url'] . $row['thumbfilename'] . '" ' : 'src="' . $modSettings['gallery_url'] . $row['filename'] . '" height="78" width="120" ')  . ' border="0" alt="' . $row['title'] . '" /></a><br />';
			if($ID_MEMBER == $userid)
			{
				if($row['approved'] == 1)
					echo '<b>' . $txt['gallery_myimages_app'] . '</b><br />';
				else
					echo '<b>' . $txt['gallery_myimages_notapp'] . '</b><br />';
			}

			echo '<span class="smalltext">' . $txt['gallery_text_views'] . $row['views'] . '<br />';
			echo $txt['gallery_text_filesize'] . gallery_format_size($row['filesize'], 2) . '<br />';
			echo $txt['gallery_text_date'] . timeformat($row['date']) . '<br />';
			echo $txt['gallery_text_comments'] . ' (<a href="' . $scripturl . '?action=gallery;sa=view&id=' . $row['ID_PICTURE'] . '">' . $row['commenttotal'] . '</a>)<br />';
			echo $txt['gallery_text_by'] . ' <a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">'  . $row['realName'] . '</a><br />';
			if($g_manage)
				echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=unapprove&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_unapprove'] . '</a>';
			if($g_manage || $g_edit_own && $row['ID_MEMBER'] == $ID_MEMBER)
				echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=edit&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_edit'] . '</a>';
			if($g_manage || $g_delete_own && $row['ID_MEMBER'] == $ID_MEMBER)
				echo '&nbsp;<a href="' . $scripturl . '?action=gallery;sa=delete&id=' . $row['ID_PICTURE'] . '">' . $txt['gallery_text_delete'] . '</a>';

			echo '</span></td>';


			if($rowlevel < ($maxrowlevel-1))
				$rowlevel++;
			else
			{
				echo '</tr>';
				$rowlevel = 0;
			}


			if ($styleclass == 'windowbg')
				$styleclass = 'windowbg2';
			else
				$styleclass = 'windowbg';



	}
		if($rowlevel !=0)
		{
			echo '<td colspan="' . ($maxrowlevel - $rowlevel) . '"> </td>';
			echo '</tr>';
		}


			echo '<tr class="titlebg">
					<td align="left" colspan="' . $maxrowlevel . '">
					' . $txt['gallery_text_pages'];

					$context['page_index'] = constructPageIndex($scripturl . '?action=gallery;sa=myimages;u=' . $userid, $context['start'], $totalPics, $modSettings['gallery_set_images_per_page']);

					echo $context['page_index'];


			echo '
					</td>
				</tr>';

		// Show return to gallery link and Show add picture if they can
		echo '
				<tr class="titlebg"><td align="center" colspan="' . $maxrowlevel . '">';
				if ($g_add)
				echo '<a href="' . $scripturl . '?action=gallery;sa=add">' . $txt['gallery_text_addpicture'] . '</a><br />';

				echo '
				<a href="' . $scripturl . '?action=gallery">' . $txt['gallery_text_returngallery'] . '</a></td>
			</tr>';


		echo '</table>';

echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';

	GalleryCopyright();

}

function GalleryCopyright()
{
	// Purchase copyright removal
	// http://www.smfhacks.com/copyright_removal.php


	// Copyright link must remain. To remove you need to purchase link removal at smfhacks.com
    $showInfo = GalleryCheckInfo();

    if ($showInfo == true)
	echo '<div align="center"><span class="smalltext">Powered by: <a href="https://www.smfhacks.com/smf-gallery.php" target="blank">SMF Gallery</a></span></div>';

}

function template_regenerate()
{
	global $scripturl, $context, $txt, $modSettings;

	echo '<div class="tborder">
		<form method="post" action="' . $scripturl . '?action=gallery;sa=regen2">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		  <tr>
		    <td width="50%" colspan="2" align="center" class="catbg">
		    <b>' . $txt['gallery_text_regeneratethumbnails2']. '</b></td>
		  </tr>
		  <tr>
  			 <td width="50%"  class="windowbg2" align="right"><b>' . $txt['gallery_form_category']. '</b>&nbsp;</td>
  			<td width="50%"  class="windowbg2">' . $context['gallery_cat_name']  . '</td>
  		</tr>
		  <tr>
		    <td width="28%" colspan="2" class="windowbg2">
		    ' . $txt['gallery_regen_notes'] . '

			    </td>
		  </tr>
		<tr>
		    <td width="28%" colspan="2"  align="center" class="windowbg2">
		    <b>',$txt['gallery_set_thumb_height'],'</b> ',$modSettings['gallery_thumb_height'],'<br />
		    <b>',$txt['gallery_set_thumb_width'],'</b> ',$modSettings['gallery_thumb_width'],'<br />
		    <br />
		    <hr />
		    <br />
		    <input type="hidden" value="' . $context['catid'] . '" name="id" />

		    <input type="submit" value="' . $txt['gallery_text_regeneratethumbnails2'] . '" name="submit" />
		    <br />
		    </td>
		  </tr>
		</table>
		</form>
		</div>';
}

function template_regenerate2()
{
	global $scripturl, $context, $txt;

	if (empty($context['continue_countdown']))
		$context['continue_countdown'] = 3;

	if (empty($context['continue_get_data']))
		$context['continue_get_data'] ='';

	if (empty($context['continue_post_data']))
		$context['continue_post_data'] ='';


	echo '<b>' . $txt['gallery_text_regeneratethumbnails2']. '</b><br />';

		if (!empty($context['continue_percent']))
		echo '
					<div style="padding-left: 20%; padding-right: 20%; margin-top: 1ex;">
						<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
							<div style="padding-top: ',  $context['browser']['is_konqueror'] ? '2pt' : '1pt', '; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $context['continue_percent'], '%</div>
							<div style="width: ', $context['continue_percent'], '%; height: 12pt; z-index: 1; background-color: red;">&nbsp;</div>
						</div>
					</div>';

	echo '<form action="' . $scripturl . '?action=gallery;sa=regen2;' , $context['continue_get_data'], '" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;" name="autoSubmit" id="autoSubmit">
				<div style="margin: 1ex; text-align: right;"><input type="submit" name="cont" value="', $txt['gallery_txt_continue'], '" class="button_submit" /></div>
				', $context['continue_post_data'], '


		    <input type="hidden" value="' . $context['catid'] . '" name="id" />


			</form>

			<script type="text/javascript"><!-- // --><![CDATA[
		var countdown = ', $context['continue_countdown'], ';
		doAutoSubmit();

		function doAutoSubmit()
		{
			if (countdown == 0)
				document.forms.autoSubmit.submit();
			else if (countdown == -1)
				return;

			document.forms.autoSubmit.cont.value = "',$txt['gallery_txt_continue'] , ' (" + countdown + ")";
			countdown--;

			setTimeout("doAutoSubmit();", 1000);
		}
	// ]]></script>';


}

function template_gallerycopyright()
{
	global $txt, $scripturl, $context, $boardurl, $modSettings;

    $modID = 19;

    $urlBoardurl = urlencode(base64_encode($boardurl));

    	echo '
	<form method="post" action="',$scripturl,'?action=gallery;sa=copyright;save=1">
<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td colspan="2">', $txt['gallery_txt_copyrightremoval'], '</td>
		</tr>
	<tr class="windowbg2">
		<td valign="top" align="right">',$txt['gallery_txt_copyrightkey'],'</td>
		<td><input type="text" name="gallery_copyrightkey" size="50" value="' . $modSettings['gallery_copyrightkey'] . '" />
        <br />
        <a href="http://www.smfhacks.com/copyright_removal.php?mod=' . $modID .  '&board=' . $urlBoardurl . '" target="_blank">' . $txt['gallery_txt_ordercopyright'] . '</a>
        </td>
	</tr>
    <tr class="windowbg2">
        <td colspan="2">' . $txt['gallery_txt_copyremovalnote'] . '</td>
    </tr>
	<tr class="windowbg2">
		<td valign="top" colspan="2" align="center"><input type="submit" value="' . $txt['gallery_save_settings'] . '" />
		</td>
		</tr>
	</table>
	</form>

    ';


    GalleryCopyright();
}

function template_importconvert()
{
    global $context, $scripturl;


   	if (empty($context['continue_countdown']))
		$context['continue_countdown'] = 3;

	if (empty($context['continue_get_data']))
		$context['continue_get_data'] ='';

	if (empty($context['continue_post_data']))
		$context['continue_post_data'] ='';

        if (!empty($context['import_step_title']))
echo '<b>' . $context['import_step_title']. '</b><br />';

		if (!empty($context['continue_percent']))
		echo '
					<div style="padding-left: 20%; padding-right: 20%; margin-top: 1ex;">
						<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
							<div style="padding-top: ', $context['browser']['is_webkit'] || $context['browser']['is_konqueror'] ? '2pt' : '1pt', '; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $context['continue_percent'], '%</div>
							<div style="width: ', $context['continue_percent'], '%; height: 12pt; z-index: 1; background-color: red;">&nbsp;</div>
						</div>
					</div>';

	echo '<form action="', '' .$scripturl . '?action=gallery;sa=convert;importstep=' . $context['continue_action'], $context['continue_get_data'], '" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;" name="autoSubmit" id="autoSubmit">
				<div style="margin: 1ex; text-align: right;"><input type="submit" name="cont" value="', 'Continue', '" class="button_submit" /></div>
				', $context['continue_post_data'], '
			</form>

			<script type="text/javascript"><!-- // --><![CDATA[
		var countdown = ', $context['continue_countdown'], ';
		doAutoSubmit();

		function doAutoSubmit()
		{
			if (countdown == 0)
				document.forms.autoSubmit.submit();
			else if (countdown == -1)
				return;

			document.forms.autoSubmit.cont.value = "', 'Continue', ' (" + countdown + ")";
			countdown--;

			setTimeout("doAutoSubmit();", 1000);
		}
	// ]]></script>';
}

function template_import_welcomeaeva()
{
    global $AevaSettings, $txt, $scripturl;

    echo '
            <div class="tborder">
            <form action="' .$scripturl . '?action=gallery;sa=convert;importstep=import0" method="post">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		  <tr>
		    <td colspan="2" align="center" class="catbg">
		    <b>' . $txt['gallery_import_welcome']. '</b></td>
		  </tr>
		  <tr>
		    <td colspan="2" class="windowbg2" align="center">
		' . $txt['gallery_txt_inorderimport']  . '<br />
		' . $txt['gallery_txt_pathtoimportaeva']  . $AevaSettings['data_dir_path'] . '<br />
		';

		if (!file_exists($AevaSettings['data_dir_path']))
			echo '<strong>' . $txt['gallery_text_aevapathnotfound'] . '</strong>';

		echo '
		<br />
		<b>' . $txt['gallery_text_import_warning'] . '</b>
		<br /><br />
		<b>' . $txt['gallery_text_import_warning'] . '</b>
		<br /><br />
		<input type="submit" value="' . $txt['gallery_txt_begin_import'] . '" />
        </td>
		  </tr>

		</table>
        </form>
		</div>


		';
}

function template_import_completeaeva()
{
    global $txt, $scripturl, $context, $boardurl;


 	echo '<div class="tborder">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		  <tr>
		    <td colspan="2" align="center" class="catbg">
		    <b>' . $txt['gallery_avea_imported']. '</b></td>
		  </tr>
		  <tr>
		    <td colspan="2" class="windowbg2">
		    ' . $txt['gallery_visit_imported_gallery'] . ' <a href="' . $scripturl . '?action=gallery">' . $scripturl . '?action=gallery</a>
            <br />
            <br />
            <h2>' . $txt['gallery_visit_promo1'] . '</h2>
            <br />
            ' .  $txt['gallery_visit_promo2']  . '<a href="http://www.smfhacks.com/promos/aevamediaconvert.php?site=' . urlencode(base64_encode($boardurl)) . '" target="_blank">http://www.smfhacks.com/promos/aevamediaconvert.php</a>

			    </td>
		  </tr>

		</table>

		</div>';

}

function template_convertgallery()
{
    global $txt, $scripturl, $context;

    echo '

	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>' . $txt['gallery_txt_convertors'] . '</td>
		</tr>

		<tr class="windowbg">
            <td>';

            echo $txt['gallery_txt_findotherconvetors'] . '<br />';


        if (isAevaInstalled() == true)
        {
            echo '<form action="' .$scripturl . '?action=gallery;sa=convert;convertavea=1" method="post">';
            echo '<input type="submit" value="' . $txt['gallery_txt_importaeva'] . '" />';
            echo '</form>';
        }
        else
        {
            echo $txt['gallery_txt_noimport'];
        }



        echo '</td>
        </tr>
  </table>
        ';


}

?>