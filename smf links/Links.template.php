<?php
/*
SMF Links
Version 3.0
by:vbgamer45
http://www.smfhacks.com
*/
function template_mainview()
{
	global $scripturl, $txt, $settings, $modSettings, $ID_MEMBER, $subcats_linktree, $context;

	// To get Permissions text
	loadLanguage('Admin');

	// See if they can approve links
	$a_links = allowedTo('approve_links');
	$addlink = allowedTo('add_links');
	$m_cats = allowedTo('links_manage_cat');

	// Get the Category
	if (!empty($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];

	echo '
		<div style="padding: 3px;">', theme_linktree(), '</div>';

	// Check if there was a category
	if (empty($cat))
	{
		// No category found show the links index
		echo '<h1 align="center"><strong>', $txt['smflinks_indextitle'], '</strong></h1>';

		$ratelink = allowedTo('rate_links');

		// List all the catagories

		// Get category count
		$cat_count = $context['cat_count'];

		echo '<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
			<tr>
				<td class="titlebg" colspan="2">', $txt['smflinks_ctitle'], '</td>
				<td class="titlebg">', $txt['smflinks_description'], '</td>
				<td class="titlebg">', $txt['smflinks_totallinks'], '</td>
				', $m_cats ? '<td class="titlebg">' . $txt['smflinks_options'] . '</td>' : '', '
			</tr>
		';

		foreach($context['catlist'] as $row)
		{
			$totallinks  = GetLinkTotals($row['ID_CAT']);
			echo '<tr>';

			if (empty($row['image']))
				echo '
					<td colspan="2" class="windowbg2">
						<a href="', $scripturl, '?action=links;cat=', $row['ID_CAT'], '">', parse_bbc($row['title']), '</a>
					</td>
					<td class="windowbg2">', parse_bbc($row['description']), '</td>
				';
			else
			{
				echo '
					<td class="windowbg2">
						<a href="', $scripturl, '?action=links;cat=', $row['ID_CAT'], '">
							<img src="', $row['image'], '" border="0" alt="" />
						</a>
					</td>
					<td class="windowbg2">
						<a href="', $scripturl, '?action=links;cat=', $row['ID_CAT'], '">', parse_bbc($row['title']), '</a>
					</td>
					<td class="windowbg2">', parse_bbc($row['description']), '</td>
				';
			}
			echo '<td class="windowbg2">', $totallinks, '</td>';

			// Show Edit Delete and Order category
			if ($m_cats)
				echo '<td class="windowbg2"><a href="', $scripturl, '?action=links;sa=catup;cat=', $row['ID_CAT'], ';sesc=', $context['session_id'], '">', $txt['smflinks_txtup'], '</a>&nbsp;<a href="', $scripturl, '?action=links;sa=catdown;cat=', $row['ID_CAT'], ';', 'sesc=', $context['session_id'], '">', $txt['smflinks_txtdown'], '</a></span>&nbsp;<a href="', $scripturl, '?action=links;sa=catperm;cat=', $row['ID_CAT'], ';',  'sesc=', $context['session_id'], '">', $txt['smflinks_txt_perm'], '</a>&nbsp;<a href="', $scripturl, '?action=links;sa=editcat;cat=', $row['ID_CAT'], ';',  'sesc=', $context['session_id'], '">', $txt['smflinks_txtedit'], '</a>&nbsp;<a href="', $scripturl, '?action=links;sa=deletecat;cat=', $row['ID_CAT'], ';', 'sesc=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a></td>';

			echo '</tr>';

			if (!empty($subcats_linktree))
				echo '<tr class="titlebg">
					<td colspan="' . ($m_cats  ? '5' : '4') . '">&nbsp;<span class="smalltext">',($subcats_linktree != '' ? $txt['smflinks_sub_cats'] . $subcats_linktree : ''),'</span></td>
				</tr>';
		}
		echo '</table>';

		// Permissions for Top 5 Rated, Top 5 Hits and Approval
		$editlink_own = allowedTo('edit_links_own');
		$editlink_any = allowedTo('edit_links_any');
		$deletelink_own = allowedTo('delete_links_own');
		$deletelink_any = allowedTo('delete_links_any');

echo '<table border="0" cellpadding="0" cellspacing="0" width="90%" align="center">
  <tr>
    <td width="50%" valign="top">';

		// Show Top 5 rated
		if (!empty($modSettings['smflinks_setshowtoprate']))
		{
			echo '<div class="tborder" style="margin: 2%;"><div class="catbg2" align="center">' . $txt['smflinks_topfiverated'] . '</div>';
			echo '<table align="center">';

			foreach($context['linkstop5'] as $row)
			{
				echo '<tr>
				<td align="center">
				<a href="' . $scripturl . '?action=links;sa=visit&id=' . $row['ID_LINK'] . '" target="blank">' . $row['title'] . '</a>&nbsp;';


				echo $txt['smflinks_rating'] . $row['rating'];

				if ($editlink_any || ($editlink_own && $ID_MEMBER == $row['ID_MEMBER']))
					echo '&nbsp;<a href="' . $scripturl . '?action=links;sa=editlink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtedit'] . '</a>&nbsp;';
				if ($deletelink_any || ($deletelink_own && $ID_MEMBER == $row['ID_MEMBER']))
					echo '<a href="' . $scripturl . '?action=links;sa=deletelink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtdel'] . '</a>';

				echo '</td></tr>';
			}
			echo '</table>';

			echo '</div>';
		}

	echo '</td>';
		// Show Top 5 hits
	echo '<td width="50%" valign="top">';
		if (!empty($modSettings['smflinks_setshowmostvisited']))
		{
			echo '<div class="tborder" style="margin: 2%;"><div class="catbg2" align="center">' . $txt['smflinks_topfivevisited'] . '</div>';
			echo '<table align="center">';
			foreach($context['linkstophits'] as $row)
			{

				echo '<tr>
						<td align="center">';
				echo '<a href="' . $scripturl . '?action=links;sa=visit&id=' . $row['ID_LINK'] . '" target="blank">' . $row['title'] . '</a>&nbsp;';

				echo $txt['smflinks_hits'] . $row['hits'] . '&nbsp;';

				if ($editlink_any || ($editlink_own && $ID_MEMBER == $row['ID_MEMBER']))
					echo '<a href="' . $scripturl . '?action=links;sa=editlink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtedit'] . '</a>&nbsp;';
				if ($deletelink_any || ($deletelink_own && $ID_MEMBER == $row['ID_MEMBER']))
					echo '<a href="' . $scripturl . '?action=links;sa=deletelink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtdel'] . '</a>';

				echo '</td></tr>';

			}
			echo '</table>';

			echo '</div>';
		}

	echo '</td>';
	echo '</tr>
	</table>';
		//####################

		// See if they are allowed to add catagories Main Index only
		if ($m_cats)
		{
			echo '<div class="tborder" style="margin: 2%;"><div class="catbg2" align="center">' . $txt['smflinks_linkspanel'] . '</div>';
			echo '<div align="center" class="windowbg2"><a href="' . $scripturl . '?action=links;sa=addcat">' . $txt['smflinks_addcat'] . '</a>&nbsp;';
			if ($addlink)
				echo '<a href="' . $scripturl . '?action=links;sa=addlink">' . $txt['smflinks_addlink'] . '</a>&nbsp;';

			echo '<a href="' . $scripturl . '?action=links;sa=admin">' . $txt['smflinks_linkssettings'] . '</a>&nbsp;';


			echo '<a href="' . $scripturl . '?action=links;sa=adminperm">' . $txt['edit_permissions'] . '</a>';

			echo '</div></div>';
		}
		// See if they can approve links
		if ($a_links)
		{
			$alinks_total = $context['alinks_total'];
			echo '<div class="tborder" style="margin: 2%;"><div class="catbg2" align="center">' . $txt['smflinks_approvepanel'] . '</div>';
			echo '<div align="center" class="windowbg2"><a href="' . $scripturl . '?action=links;sa=alist">' . $txt['smflinks_approvelinks'] . '</a><br />';
			echo $txt['smflinks_thereare'] . '<strong>' . $alinks_total . '</strong>' . $txt['smflinks_waitingapproval'];
			echo '</div></div>';
		}

		// Stats
		if (!empty($modSettings['smflinks_setshowstats']))
		{
			$link_count = $context['link_count'];
			echo '<div class="tborder" style="margin: 2%;"><div class="catbg2" align="center">' . $txt['smflinks_stats'] . '</div>';
			echo '<div align="center" class="windowbg2">' . $txt['smflinks_thereare'] . '<strong>' . $cat_count . '</strong>' . $txt['smflinks_catand'] . '<strong>' . $link_count . '</strong>' . $txt['smflinks_linkssystem'] . '</div></div>';

		}
	}
	else
	{
		// Category found show the links


		// Get the category name
		$row = $context['linkcatrow'];

		// Setup their permissions
		$editlink_own = allowedTo('edit_links_own');
		$editlink_any = allowedTo('edit_links_any');
		$deletelink_own = allowedTo('delete_links_own');
		$deletelink_any = allowedTo('delete_links_any');

		$ratelink = allowedTo('rate_links');
		$cattitle =  $row['title'];

		echo '<h1 align="center"><strong>', $cattitle, '</strong></h1>';

		ShowSubCats($cat, $m_cats);

		if (!empty($_REQUEST['start']))
			$context['start'] = (int) $_REQUEST['start'];
		else
			$context['start'] = 0;

		if (!empty($_REQUEST['sort']))
		{
			switch($_REQUEST['sort'])
			{
				case 'title':
					$sort = 'l.title';
					break;
				case 'date':
					$sort = 'l.date';
					break;
				case 'rating':
					$sort = 'l.rating';
					break;
				case 'hits':
					$sort = 'l.hits';
					break;
				case 'username':
					$sort = 'm.realName';
					break;
				default:
					$sort = 'l.ID_LINK';
			}
		}
		else
			$sort = 'l.ID_LINK';

		if (!empty($_REQUEST['sorto']) && $_REQUEST['sorto'] == 'ASC')
			$sorto = 'ASC';
		else
			$sorto = 'DESC';

		//Change sort order for links
		if ($sorto == 'DESC')
			$newsorto = 'ASC';
		else
			$newsorto = 'DESC';

		if (empty($modSettings['smflinks_setlinksperpage']))
			$modSettings['smflinks_setlinksperpage'] = 10;

		// Get Total Pages
		$total = $context['linkstotalpages'];

		// Show the links in that category

		// Check if no links where found
		if ($context['linkslist_count']  == 0)
		{

			echo '
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
				<tr>
						<td class="titlebg" align="center"><a href="' . $scripturl . '?action=links;cat=' . $cat .  '">' . $cattitle . '</a></td>
				</tr>
				<tr class="windowbg">
					<td align="center">', $txt['smflinks_nolinks'],'</td>
				</tr>



					';
			$numofspans = 1;
		}
		else
		{
			$numofspans = 1;
			// There are links found in the category
			echo '<br /><table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
					<tr>
						<td class="titlebg"><a href="' . $scripturl . '?action=links;cat=' . $cat .  ';start=' . $context['start'] . ';sort=title;sorto=' . $newsorto . '">' . $txt['smflinks_ctitle'] . '</a></td>';

						if (!empty($modSettings['smflinks_disp_description']))
						{
							echo '<td class="titlebg">' . $txt['smflinks_description'] .'</td>';
							$numofspans++;
						}


						if (!empty($modSettings['smflinks_disp_hits']))
						{
							echo '<td class="titlebg"><a href="' . $scripturl . '?action=links;cat=' . $cat .  ';start=' . $context['start'] . ';sort=hits;sorto=' . $newsorto . '">' . $txt['smflinks_chits'] . '</a></td>';
							$numofspans++;
						}

						if (!empty($modSettings['smflinks_disp_rating']))
						{
							echo '<td class="titlebg"><a href="' . $scripturl . '?action=links;cat=' . $cat .  ';start=' . $context['start'] . ';sort=rating;sorto=' . $newsorto . '">' . $txt['smflinks_crating'] . '</a></td>';
							$numofspans++;
						}
						if (!empty($modSettings['smflinks_disp_membername']))
						{
							echo '<td class="titlebg"><a href="' . $scripturl . '?action=links;cat=' . $cat .  ';start=' . $context['start'] . ';sort=username;sorto=' . $newsorto . '">' . $txt['smflinks_cusername'] . '</a></td>';
							$numofspans++;
						}
						if (!empty($modSettings['smflinks_disp_date']))
						{
							echo '<td class="titlebg"><a href="' . $scripturl . '?action=links;cat=' . $cat .  ';start=' . $context['start'] . ';sort=date;sorto=' . $newsorto . '">' . $txt['smflinks_cdate'] . '</a></td>';
							$numofspans++;
						}


						$showOptions = false;

						foreach($context['linkslist'] as $row)
						{
							if ($editlink_any || ($editlink_own && $ID_MEMBER == $row['ID_MEMBER']))
								$showOptions = true;
							if ($deletelink_any || ($deletelink_own && $ID_MEMBER == $row['ID_MEMBER']))
								$showOptions = true;
							if ($a_links)
								$showOptions = true;
						}


						if ($showOptions == true)
						{
							echo '
							<td class="titlebg">' . $txt['smflinks_options'] .'</td>';

							$numofspans++;
						}

						echo '
					</tr>';

			$styleclass = "windowbg";

			foreach($context['linkslist'] as $row)
			{

				echo '<tr class="' . $styleclass . '">';
				echo '<td valign="top"><a href="' . $scripturl . '?action=links;sa=visit&id=' . $row['ID_LINK'] . '" target="blank">' . $row['title'] . '</a></td>';


				if (!empty($modSettings['smflinks_disp_description']))
					if(empty($modSettings['smflinks_setallowbbc']))
						echo '<td>' . $row['description'] . '</td>';
					else
						echo '<td>' . parse_bbc($row['description']) . '</td>';



				if (!empty($modSettings['smflinks_disp_hits']))
					echo '<td>' . $row['hits'] . '</td>';


				if (!empty($modSettings['smflinks_disp_rating']))
				{
					echo '<td>' . $row['rating'];
					if($ratelink)
						echo '<br /><a href="' . $scripturl . '?action=links;sa=rate;value=1&id=' . $row['ID_LINK'] . '"><img src="', $settings['images_url'], '/post/thumbup.gif" alt="Good Link" border="0" /></a>&nbsp;&nbsp;<a href="' . $scripturl . '?action=links;sa=rate;value=0&id=' . $row['ID_LINK'] . '"><img src="', $settings['images_url'], '/post/thumbdown.gif" alt="Bad Link" border="0" /></a>';

					echo '</td>';
				}
				// Check if it was a guest link
				if (!empty($modSettings['smflinks_disp_membername']))
					if($row['realName'] != '')
						echo '<td><a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">'  . $row['realName'] . '</a></td>';
					else
						echo '<td>' . $txt['smflinks_txtguest'] . '</td>';

				if (!empty($modSettings['smflinks_disp_date']))
					echo '<td>' . timeformat($row['date']) . '</td>';

				if ($showOptions == true)
				{
					echo '<td>';

					if ($editlink_any || ($editlink_own && $ID_MEMBER == $row['ID_MEMBER']))
						echo '<a href="' . $scripturl . '?action=links;sa=editlink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtedit'] . '</a>&nbsp;';
					if ($deletelink_any || ($deletelink_own && $ID_MEMBER == $row['ID_MEMBER']))
						echo '<a href="' . $scripturl . '?action=links;sa=deletelink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtdel'] . '</a>&nbsp;';
					if ($a_links)
							echo '<a href="' . $scripturl . '?action=links;sa=noapprove&id=' . $row['ID_LINK'] . ';' . 'sesc=' . $context['session_id'] . '">' . $txt['smflinks_txtunapprove'] . '</a>';
					echo '</td>';
				}

				echo '</tr>';


				if ($styleclass == 'windowbg')
					$styleclass = 'windowbg2';
				else
					$styleclass = 'windowbg';

			}

			// Show the pages
				echo '
				<tr class="titlebg">
						<td align="left" colspan="' . $numofspans . '">
						' . $txt['smflinks_pages'];


						$context['page_index'] = constructPageIndex($scripturl . '?action=links;cat=' . $cat . (!empty($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (!empty($_REQUEST['sorto']) ? ';sorto=' . $_REQUEST['sorto'] : ''), $context['start'], $total, $modSettings['smflinks_setlinksperpage']);

						echo $context['page_index'];


				echo '
						</td>
					</tr>';


		}
			// Show return to links index link
			echo '

			<tr class="titlebg"><td align="center" colspan="' . $numofspans . '">';

			// See if they are allowed to add a subcategory
			if ($m_cats)
				echo '<a href="' . $scripturl . '?action=links;sa=addcat;cat=' . $cat . '">' .$txt['smflinks_addsubcat'] . '</a>&nbsp;&nbsp;';


			// See if they are allowed to add links
			if ($addlink)
			{
				echo '<a href="' . $scripturl . '?action=links;sa=addlink;cat=' . $cat . '">' . $txt['smflinks_addlink'] . '</a>&nbsp;';
				echo '<br /><br />';
			}

			echo '
			<a href="' . $scripturl . '?action=links">'. $txt['smflinks_returnindex'] . '</a>';
			echo '</td></tr>
			</table>';
	}


LinksCopyright();

}

function template_addcat()
{
	global $scripturl, $txt, $context;

	if (!empty($_REQUEST['cat']))
		$parent  = (int) $_REQUEST['cat'];
	else
		$parent = 0;

	echo '
<form method="POST" action="' . $scripturl . '?action=links&sa=addcat2">
<table cellspacing="0" align="center" cellpadding="4" class="tborder" width="100%">
  <tr>
    <td width="50%" colspan="2"   align="center" class="catbg">
    <strong>' . $txt['smflinks_addcat'] . '</strong></td>
  </tr>
  <tr>
    <td width="28%"  class="windowbg2" align="right"><span class="gen"><strong>' . $txt['smflinks_ctitle'] . '</strong></span></td>
    <td width="72%"  class="windowbg2"><input type="text" name="title" size="64" maxlength="100" /></td>
  </tr>
  <tr>
    <td width="28%" class="windowbg2" align="right"><span class="gen"><strong>' . $txt['smflinks_parentcategory'] .'</strong>&nbsp;</span></td>
    <td width="72%" class="windowbg2"><select name="parent">
    <option value="0">' . $txt['smflinks_text_catnone'] . '</option>
    ';

	foreach ($context['links_cat'] as $i => $category)
	{
		echo '<option value="' . $category['ID_CAT']  . '" ' . (($parent == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';
	}

	echo '</select>
	</td>
  </tr>
  <tr>
    <td width="28%"   valign="top" class="windowbg2" align="right"><span class="gen"><strong>' . $txt['smflinks_description'] . '</strong></span></td>
    <td width="72%"   class="windowbg2"><textarea rows="6" name="description" cols="54"></textarea></td>
  </tr>
  <tr>
    <td width="28%"   class="windowbg2" align="right"><span class="gen"><strong>' . $txt['smflinks_image'] . '</strong>&nbsp;</span></td>
    <td width="72%"   class="windowbg2"><input type="text" name="image" size="64" maxlength="100" /></td>
  </tr>
  <tr>
    <td width="28%" colspan="2"  align="center" class="windowbg2">
    <input type="submit" value="' . $txt['smflinks_addcat'] . '" name="submit" /></td>
    <input type="hidden" name="sc" value="', $context['session_id'], '" />
  </tr>
</table>
</form>
';

LinksCopyright();

}

function template_editcat()
{
	global $scripturl, $txt, $context;

	if (!empty($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];

	if (empty($cat))
		fatal_error($txt['smflinks_nocatselected']);

	$row = $context['links_editcat'];

echo '
<form method="POST" action="' . $scripturl . '?action=links&sa=editcat2">
<table cellspacing="0" align="center" cellpadding="4" class="tborder"  width="100%">
  <tr>
    <td width="50%" colspan="2" align="center" class="catbg">
    <strong>' . $txt['smflinks_editcat'] . '</strong></td>
  </tr>
  <tr>
    <td width="28%"  class="windowbg2" align="right"><span class="gen"><strong>' . $txt['smflinks_ctitle'] . '</strong></span></td>
    <td width="72%" class="windowbg2"><input type="text" name="title" size="64" maxlength="100" value="' . $row['title'] . '" /></td>
  </tr>
 <tr>
    <td width="28%" class="windowbg2" align="right"><span class="gen"><strong>' . $txt['smflinks_parentcategory'] .'</strong>&nbsp;</span></td>
    <td width="72%" class="windowbg2"><select name="parent">
    <option value="0">' . $txt['smflinks_text_catnone'] . '</option>
    ';

	foreach ($context['links_cat'] as $i => $category)
	{
		// Category can not be a parent to itself!!
		if ($category['ID_CAT'] == $cat)
			continue;

		echo '<option value="' . $category['ID_CAT']  . '" ' . (($row['ID_PARENT'] == $category['ID_CAT']) ? ' selected="selected"' : '') .'>' . $category['title'] . '</option>';
	}

	echo '</select>
	</td>
  </tr>
  <tr>
    <td width="28%" valign="top" class="windowbg2" align="right"><span class="gen"><strong>' . $txt['smflinks_description'] . '</strong></span></td>
    <td width="72%" class="windowbg2"><textarea rows="6" name="description" cols="54">' . $row['description'] . '</textarea></td>
  </tr>
  <tr>
    <td width="28%" class="windowbg2" align="right"><span class="gen"><strong>' . $txt['smflinks_image'] . '</strong>&nbsp;</span></td>
    <td width="72%" class="windowbg2"><input type="text" name="image" size="64" maxlength="100" value="' . $row['image'] . '" /></td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="hidden" value="' . $row['ID_CAT'] . '" name="catid" />
    <input type="submit" value="' . $txt['smflinks_editcat'] . '" name="submit" /></td>
    <input type="hidden" name="sc" value="', $context['session_id'], '" />
  </tr>
</table>
</form>
';


	LinksCopyright();

}

function template_deletecat()
{
	global $scripturl, $txt, $context;

	echo '
	<form method="POST" action="' . $scripturl . '?action=links&sa=deletecat2">
<table border="1" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="50%" colspan="2" align="center" class="catbg">
    <strong>' . $txt['smflinks_deltcat'] . '</strong></td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <strong>' . $txt['smflinks_warndel2'] . '</strong>
    <br />
    <input type="hidden" value="' . $context['links_catid'] . '" name="catid" />
    <input type="hidden" name="sc" value="', $context['session_id'], '" />
    <input type="submit" value="' . $txt['smflinks_deltcat'] . '" name="submit" /></td>
  </tr>
</table>
</form>';


LinksCopyright();

}

function template_addlink()
{
	global $scripturl, $txt, $modSettings, $context, $settings;

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';


	echo '
<form method="POST"  name="links" id="links" action="' . $scripturl . '?action=links&sa=addlink2">
<table cellspacing="0"  align="center" cellpadding="4" class="tborder" width="100%">
  <tr>
    <td width="50%" colspan="2"   align="center" class="catbg">
    <strong>' . $txt['smflinks_addlink'] . '</strong></td>
  </tr>
  <tr>
    <td width="28%"   class="windowbg2"><span class="gen"><strong>' . $txt['smflinks_ctitle'] . '</strong></span></td>
    <td width="72%"   class="windowbg2"><input type="text" name="title" size="64" maxlength="100" /></td>
  </tr>
  <tr>
    <td width="28%"   class="windowbg2"><span class="gen"><strong>' . $txt['smflinks_category'] . '</strong></span></td>
    <td width="72%"   class="windowbg2"><select name="catid">';
		foreach ($context['links_cats'] as $row)
  			echo '<option value="' . $row['ID_CAT'] . '" ' . (($row['ID_CAT'] == $context['links_catid']) ? 'selected="selected" ' : '') .' >' . $row['title'] . '</option>';

echo '</select>
    </td>
  </tr>
  <tr>
    <td width="28%"   class="windowbg2"><span class="gen"><strong>' . $txt['smflinks_url'] . '</strong></span></td>
    <td width="72%"   class="windowbg2"><input type="text" name="url" size="64" maxlength="250" value="http://" /></td>
  </tr>
  <tr>
    <td width="28%"   valign="top" class="windowbg2"><span class="gen"><strong>' . $txt['smflinks_description'] . '</strong></span></td>
    <td width="72%"   class="windowbg2">';

	if (!empty($modSettings['smflinks_setallowbbc']))
	{

		echo '<table> ';
		theme_postbox('');
		echo '</table>';
	}
	else
	{
		// No BBC
		echo '<textarea rows="6" name="description" cols="54"></textarea><br />';
	}
       	if ($context['show_spellchecking'])
   		echo '
   									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'links\', \'description\');" />';

   echo '
    </td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="hidden" name="sc" value="', $context['session_id'], '" />
    <input type="submit" value="' . $txt['smflinks_addlink'] .'" name="submit" /></td>
  </tr>
</table>
</form>';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';


LinksCopyright();

}

function template_editlink()
{
	global $context, $scripturl, $txt, $modSettings, $settings;

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/spellcheck.js"></script>';

	echo '
<form name="links" id="links" method="POST" action="' . $scripturl . '?action=links&sa=editlink2">
<table cellspacing="0" align="center" cellpadding="4" class="tborder" width="100%">
  <tr>
    <td width="50%" colspan="2"   align="center" class="catbg">
    <strong>' . $txt['smflinks_editlink'] . '</strong></td>
  </tr>
  <tr>
    <td width="28%"   class="windowbg2"><span class="gen"><strong>' . $txt['smflinks_ctitle'] . '</strong></span></td>
    <td width="72%"   class="windowbg2"><input type="text" name="title" size="64" maxlength="100" value="' . $context['links_link']['title'] . '" /></td>
  </tr>
  <tr>
    <td width="28%" class="windowbg2"><span class="gen"><strong>' . $txt['smflinks_category'] . '</strong></span></td>
    <td width="72%" class="windowbg2"><select name="catid">';

		foreach($context['links_cats'] as $row2)
  			echo '<option value="' . $row2['ID_CAT'] . '" ' . (($row2['ID_CAT'] == $context['links_link']['ID_CAT']) ? 'selected="selected" ' : '') .'>' . $row2['title'] . '</option>';


echo '</select>
    </td>
  </tr>
  <tr>
    <td width="28%"   class="windowbg2"><span class="gen"><strong>' . $txt['smflinks_url'] . '</strong></span></td>
    <td width="72%"   class="windowbg2"><input type="text" name="url" size="64" maxlength="250" value="' . $context['links_link']['url'] . '" /></td>
  </tr>
  <tr>
    <td width="28%"   valign="top" class="windowbg2"><span class="gen"><strong>' . $txt['smflinks_description'] . '</strong></span></td>
    <td width="72%"   class="windowbg2">';

	if (!empty($modSettings['smflinks_setallowbbc']))
	{

			echo '<table>';
		   theme_postbox($context['links_link']['description']);
		   echo '</table>';
	}
	else
	{
		// No BBC
		echo ' <textarea rows="6" name="description" cols="54">' . $context['links_link']['description'] . '</textarea><br />';
	}
       	if ($context['show_spellchecking'])
   		echo '
   									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'links\', \'description\');" />';
echo '

    </td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="hidden" name="sc" value="', $context['session_id'], '" />
    <input type="hidden" value="' . $context['link_id'] . '" name="id" />
    <input type="submit" value="' . $txt['smflinks_editlink'] . '" name="submit" /></td>

  </tr>
</table>
</form>';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';


LinksCopyright();

}

function template_deletelink()
{
	global $scripturl, $txt, $context;

	echo '<form method="POST" action="' . $scripturl . '?action=links&sa=deletelink2">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="50%" colspan="2" align="center" class="catbg">
    <strong>', $txt['smflinks_dellink'], '</strong></td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <strong>', $txt['smflinks_warndel'], '</strong>
    <br />
    <input type="hidden" value="' . $context['links_id'] . '" name="id" />
    <input type="hidden" name="sc" value="', $context['session_id'], '" />
    <input type="submit" value="' . $txt['smflinks_dellink'] . '" name="submit" /></td>
  </tr>
</table>
</form>';

LinksCopyright();

}

function template_approvelinks()
{
	global $scripturl, $txt, $context;

	// Edit and Delete permissions
	$editlink = allowedTo('edit_links_any');
	$deletelink = allowedTo('delete_links_any');

	// Show all the links waiting for approval.
echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>' .  $txt['smflinks_approvelinks'] . '</td>
		</tr>
		<tr class="windowbg">
			<td class="windowbg">
<table cellspacing="0" align="center" cellpadding="4" class="tborder" width="100%">
  <tr>
    <td class="catbg"><strong>' . $txt['smflinks_url'] . '</strong></td>
    <td class="catbg"><strong>' . $txt['smflinks_ctitle'] .'</strong></td>
    <td class="catbg"><strong>' . $txt['smflinks_description'] . '</strong></td>
    <td class="catbg"><strong>' .  $txt['smflinks_category'] . '</strong></td>
    <td class="catbg"><strong>' . $txt['smflinks_submittedby'] . '</strong></td>
    <td class="catbg"><strong>' . $txt['smflinks_options'] . '</strong></td>
  </tr>';

		// Get Total Pages

	$total = $context['approvetotal'];

	if (!empty($_REQUEST['start']))
		$context['start'] = (int) $_REQUEST['start'];
	else
		$context['start'] = 0;

    $styleClass = 'windowbg';
	foreach($context['linksapprovallist'] as $row)
		{
		  echo '<tr class="' . $styleClass  . '">';
		  echo '<td class="windowbg2"><a href="' . $row['url'] . '" target="blank">' . $row['url'] . '</a></td>';
		  echo '<td class="windowbg2">' . $row['title'] . '</td>';
		  echo '<td class="windowbg2">' . $row['description'] . '</td>';
		  echo '<td class="windowbg2">' . $row['catname'] . '</td>';

		  if($row['realName'] == '')
		  	 echo '<td class="windowbg2">' . $txt['smflinks_txtguest'] . '</td>';
		  else
		  	echo '<td class="windowbg2"><a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['realName'] . '</a></td>';


		  echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;sa=approve&id=' . $row['ID_LINK'] . ';sesc=' . $context['session_id'] . '">' . $txt['smflinks_txtapprove'] . '</a>&nbsp;';

		  if ($editlink)
				echo '<a href="' . $scripturl . '?action=links;sa=editlink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtedit'] . '</a>&nbsp;';
		  if ($deletelink)
				echo '<a href="' . $scripturl . '?action=links;sa=deletelink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtdel'] .'</a>';

		  echo '</td>';
		  echo '</tr>';

                if ($styleClass == 'windowbg')
        		  $styleClass = 'windowbg2';
        		else
        		  $styleClass = 'windowbg';

		 }


 			// Show the pages
				echo '<tr class="titlebg">
						<td align="left" colspan="6">
						' . $txt['smflinks_pages'];

						$context['page_index'] = constructPageIndex($scripturl . '?action=links;sa=alist', $context['start'], $total, 20);

						echo $context['page_index'];

				echo '
						</td>
					</tr>';

echo '
	</table>
	</td>
	</tr>
		<tr>
		<td class="windowbg">
		<strong>Has SMF Links helped you?</strong> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="SMF Links">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
	</td>
	</tr>
	</table>
	';

	LinksCopyright();
}

function template_settings()
{
	global $scripturl, $txt, $modSettings, $context;
echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>' . $txt['smflinks_linkssettings'] . '</td>
		</tr>
		<tr class="windowbg">
			<td class="windowbg">

			<form method="post" action="' . $scripturl . '?action=links;sa=admin2">
			' . $txt['smflinks_setlinksperpage'] . '&nbsp;<input type="text" name="smflinks_setlinksperpage" value="' .  $modSettings['smflinks_setlinksperpage'] . '" /><br />

				<input type="checkbox" name="smflinks_setshowtoprate" ' . ($modSettings['smflinks_setshowtoprate'] ? ' checked="checked" ' : '') . ' />' . $txt['smflinks_setshowtoprate'] . '<br />
				<input type="checkbox" name="smflinks_setshowmostvisited" ' . ($modSettings['smflinks_setshowmostvisited'] ? ' checked="checked" ' : '') . ' />' . $txt['smflinks_setshowmostvisited'] . '<br />
				<input type="checkbox" name="smflinks_set_count_child" ' . ($modSettings['smflinks_set_count_child'] ? ' checked="checked" ' : '') . ' />' . $txt['smflinks_set_count_child'] . '<br />
				<input type="checkbox" name="smflinks_setshowstats" ' . ($modSettings['smflinks_setshowstats'] ? ' checked="checked" ' : '') . ' />' . $txt['smflinks_setshowstats'] . '<br />
				<input type="checkbox" name="smflinks_setallowbbc" ' . ($modSettings['smflinks_setallowbbc'] ? ' checked="checked" ' : '') . ' />' . $txt['smflinks_setallowbbc'] . '<br />


				<strong>' . $txt['smflinks_linkdisplay'] . '</strong><br />
				<input type="checkbox" name="smflinks_disp_description" ' . ($modSettings['smflinks_disp_description'] ? ' checked="checked" ' : '') . ' />' . $txt['smflinks_disp_description'] . '<br />
				<input type="checkbox" name="smflinks_disp_hits" ' . ($modSettings['smflinks_disp_hits'] ? ' checked="checked" ' : '') . ' />' . $txt['smflinks_disp_hits'] . '<br />
				<input type="checkbox" name="smflinks_disp_rating" ' . ($modSettings['smflinks_disp_rating'] ? ' checked="checked" ' : '') . ' />' . $txt['smflinks_disp_rating'] . '<br />
				<input type="checkbox" name="smflinks_disp_membername" ' . ($modSettings['smflinks_disp_membername'] ? ' checked="checked" ' : '') . ' />' . $txt['smflinks_disp_membername'] . '<br />
				<input type="checkbox" name="smflinks_disp_date" ' . ($modSettings['smflinks_disp_date'] ? ' checked="checked" ' : '') . ' />' . $txt['smflinks_disp_date'] . '<br />
				<br />
                <input type="hidden" name="sc" value="', $context['session_id'], '" />
				<input type="submit" name="savesettings" value="' . $txt['smflinks_settings_save'] .'" />
			</form>
			</td>
		</tr>

		<tr>
		<td class="windowbg">
		<strong>Has SMF Links helped you?</strong> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="SMF Links">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
<br />

	</td>
	</tr>

	</table>';

	LinksCopyright();
}

function template_manage_cats()
{
	global $scripturl, $txt, $context;

echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>' .$txt['smflinks_managecats'] . '</td>
		</tr>
		<tr class="windowbg">
			<td>

		<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
			<tr>
				<td class="titlebg">' . $txt['smflinks_ctitle'] . '</td>
				<td class="titlebg">' . $txt['smflinks_description'] .'</td>
				<td class="titlebg">' . $txt['smflinks_totallinks'] . '</td>
				<td class="titlebg">' . $txt['smflinks_options'] .'</td>
			</tr>
		';

        $styleClass = 'windowbg';
		foreach($context['links_cats'] as $row)
		{

			$totallinks = GetLinkTotals($row['ID_CAT']);

			echo '<tr class="' . $styleClass  . '">';

			echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></td>';
			echo '<td class="windowbg2">' . parse_bbc($row['description']) . '</td>';
			echo '<td class="windowbg2">' . $totallinks . '</td>';

			// Show Edit Delete and Order category
			echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;sa=catperm;cat=' . $row['ID_CAT'] . ';sesc=' . $context['session_id'] . '">' . $txt['smflinks_txt_perm'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=catup;cat=' . $row['ID_CAT'] . ';a=admin;sesc=' . $context['session_id'] . '">' . $txt['smflinks_txtup'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=catdown;cat=' . $row['ID_CAT'] . ';a=admin;sesc=' . $context['session_id'] . '">' . $txt['smflinks_txtdown'] .'</a></span>&nbsp;<a href="' . $scripturl . '?action=links;sa=editcat;cat=' . $row['ID_CAT'] . ';a=admin;sesc=' . $context['session_id'] . '">' . $txt['smflinks_txtedit'] .'</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=deletecat;cat=' . $row['ID_CAT'] . ';a=admin;sesc=' . $context['session_id'] . '">' . $txt['smflinks_txtdel'] . '</a></td>';
			echo '</tr>';


            if ($styleClass == 'windowbg')
    		  $styleClass = 'windowbg2';
    		else
    		  $styleClass = 'windowbg';
		}

		echo '<tr><td class="windowbg2" colspan="4" align="center">
			<a href="' . $scripturl . '?action=links;sa=addcat;a=admin">' . $txt['smflinks_addcat'] . '</a>

		</td></tr>
		</table>
		</tr>
		<tr>
		<td class="windowbg">
		<strong>Has SMF Links helped you?</strong> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="SMF Links">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
<br />

	</td>
	</tr>
	</table>';

	LinksCopyright();
}

function template_catperm()
{
	global $scripturl, $txt, $context;

	echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>' .$txt['smflinks_text_catperm'] . ' - ' . $context['links_cat_name']  . '</td>
		</tr>
		<tr class="windowbg">
		<td>
		<form method="post" action="' . $scripturl . '?action=links;sa=catperm2">
		<table align="center" class="tborder">
		<tr class="titlebg">
			<td colspan="2">'  . $txt['smflinks_text_addperm'] . '</td>
		</tr>
	    <tr class="windowbg2">
			  	<td align="right"><strong>' . $txt['smflinks_groupname'] . '</strong>&nbsp;</td>
			  	<td><select name="groupname">
			  					<option value="-1">' . $txt['membergroups_guests'] . '</option>
								<option value="0">' . $txt['membergroups_members'] . '</option>';
								foreach ($context['groups'] as $group)
									echo '<option value="', $group['ID_GROUP'], '">', $group['groupName'], '</option>';

							echo '</select>
				</td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="view" checked="checked" /></td>
			  	<td><strong>' . $txt['smflinks_perm_view'] .'</strong></td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="add" checked="checked" /></td>
			  	<td><strong>' . $txt['smflinks_perm_add'] .'</strong></td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="edit" checked="checked" /></td>
			  	<td><strong>' . $txt['smflinks_perm_edit'] .'</strong></td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="delete" checked="checked" /></td>
			  	<td><strong>' . $txt['smflinks_perm_delete'] .'</strong></td>
			  </tr>

			  <tr class="windowbg2">
			  	<td align="center" colspan="2">
			  	<input type="hidden" name="cat" value="' . $context['links_cat'] . '" />
                <input type="hidden" name="sc" value="', $context['session_id'], '" />
			  	<input type="submit" value="' . $txt['smflinks_text_addperm'] . '" /></td>

			  </tr>
		</table>
		</form>
		</td>
		</tr>
			<tr class="windowbg">
			<td>
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
			<tr class="catbg">
				<td>' . $txt['smflinks_groupname'] . '</td>
				<td>' .  $txt['smflinks_perm_view']  . '</td>
				<td>' .  $txt['smflinks_perm_add']  . '</td>
				<td>' .  $txt['smflinks_perm_edit']  . '</td>
				<td>' .  $txt['smflinks_perm_delete']  . '</td>
				<td>' .  $txt['smflinks_options']  . '</td>
				</tr>';

		// Show the member groups
        $styleClass = 'windowbg';
		  	foreach($context['linksmembergroups'] as $row)
			{
				echo '<tr class="' . $styleClass  . '">';
				echo '<td>'  . $row['groupName'] . '</td>';
				echo '<td>' . ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';

				echo '<td><a href="' . $scripturl . '?action=links;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['smflinks_txtdel'] . '</a></td>';
				echo '</tr>';

                if ($styleClass == 'windowbg')
        		  $styleClass = 'windowbg2';
        		else
        		  $styleClass = 'windowbg';
			}

			// Show Regular members
		  	foreach($context['linksreggroups'] as $row)
			{
				echo '<tr class="' . $styleClass  . '">';
				echo '<td>'  . $txt['membergroups_members'] . '</td>';
				echo '<td>' . ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=links;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['smflinks_txtdel'] . '</a></td>';
				echo '</tr>';

                if ($styleClass == 'windowbg')
        		  $styleClass = 'windowbg2';
        		else
        		  $styleClass = 'windowbg';
			}

			// Show Guests
		  	foreach($context['linksguestgroups'] as $row)
			{
				echo '<tr class="' . $styleClass  . '">';
				echo '<td>'  . $txt['membergroups_guests'] . '</td>';
				echo '<td>' . ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=links;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['smflinks_txtdel'] . '</a></td>';
				echo '</tr>';

                if ($styleClass == 'windowbg')
        		  $styleClass = 'windowbg2';
        		else
        		  $styleClass = 'windowbg';

			}
		echo '
				</table>
			</td>
		</tr>
</table>';
}

function template_catpermlist()
{
	global $scripturl, $txt, $context;
	echo '
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>' . $txt['smflinks_catpermlist'] . '</td>
		</tr>
		<tr class="windowbg">
			<td>
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
			<tr class="catbg">
				<td>' . $txt['smflinks_groupname'] . '</td>
				<td>' . $txt['smflinks_category']  . '</td>
				<td>' .  $txt['smflinks_perm_view']  . '</td>
				<td>' .  $txt['smflinks_perm_add']  . '</td>
				<td>' .  $txt['smflinks_perm_edit']  . '</td>
				<td>' .  $txt['smflinks_perm_delete']  . '</td>
				<td>' .  $txt['smflinks_options']  . '</td>
				</tr>';

			// Show the member groups
            $styleClass = 'windowbg';
		  	foreach($context['links_membergroups'] as $row)
			{
				echo '<tr class="' . $styleClass  . '">';
				echo '<td>'  . $row['groupName'] . '</td>';
				echo '<td><a href="' . $scripturl . '?action=links;sa=catperm;cat=' . $row['ID_CAT'] . '">'  . $row['catname'] . '</a></td>';
				echo '<td>' . ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td><a href="' . $scripturl . '?action=links;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['smflinks_txtdel'] . '</a></td>';
				echo '</tr>';


                if ($styleClass == 'windowbg')
        		  $styleClass = 'windowbg2';
        		else
        		  $styleClass = 'windowbg';

			}

			//Show Regular members
			foreach($context['links_reggroups'] as $row)
			{
				echo '<tr class="windowbg2">';
				echo '<td>'  . $txt['membergroups_members'] . '</td>';
				echo '<td><a href="' . $scripturl . '?action=links;sa=catperm;cat=' . $row['ID_CAT'] . '">'  . $row['catname'] . '</a></td>';
				echo '<td>' . ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';

				echo '<td><a href="' . $scripturl . '?action=links;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['smflinks_txtdel'] . '</a></td>';
				echo '</tr>';


                if ($styleClass == 'windowbg')
        		  $styleClass = 'windowbg2';
        		else
        		  $styleClass = 'windowbg';
			}

			// Show Guests
		  	foreach($context['links_guestsgroups'] as $row)
			{
				echo '<tr class="windowbg2">';
				echo '<td>'  . $txt['membergroups_guests'] . '</td>';
				echo '<td><a href="' . $scripturl . '?action=links;sa=catperm;cat=' . $row['ID_CAT'] . '">'  . $row['catname'] . '</a></td>';
				echo '<td>' . ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';
				echo '<td>' . ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']) . '</td>';

				echo '<td><a href="' . $scripturl . '?action=links;sa=catpermdelete&id=' . $row['ID'] . '">' . $txt['smflinks_txtdel'] . '</a></td>';
				echo '</tr>';


                if ($styleClass == 'windowbg')
        		  $styleClass = 'windowbg2';
        		else
        		  $styleClass = 'windowbg';

			}

		echo '
				</table>
			</td>
		</tr>
		<tr>
		<td class="windowbg">
		<strong>Has SMF Links helped you?</strong> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="SMF Links">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
	</td>
	</tr>
</table>';
}

function LinksCopyright()
{
	// The Copyright is required to remain or contact me to purchase link removal.
	// http://www.smfhacks.com/copyright_removal.php
	echo '<br /><div align="center"><span class="smalltext">Powered by: <a href="http://www.smfhacks.com" target="blank">SMF Links</a> by <a href="http://www.createaforum.com" title="Forum Hosting">CreateAForum.com</a></span></div>';

}