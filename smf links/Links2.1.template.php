<?php
/*
SMF Links
Version 4.0
by:vbgamer45
https://www.smfhacks.com
*/
function template_mainview()
{
	global $scripturl, $context, $txt, $settings, $modSettings, $user_info, $subcats_linktree;



	// See if they can approve links
	$a_links = allowedTo('approve_links');
	$addlink = allowedTo('add_links');
	$m_cats = allowedTo('links_manage_cat');

	// Get the Category
	if (!empty($_REQUEST['cat']))
		$cat = (int) $_REQUEST['cat'];

	// Check if there was a category
	if (empty($cat))
	{
		// No category found show the links index
		echo '
        <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smflinks_indextitle'], '
        </h3>
  </div>
  <br />


		';

		// List all the categories
		// Get category count
		$cat_count = $context['cat_count'];

		echo '<table cellspacing="0" cellpadding="10" border="0" align="center" width="100%" class="table_grid">

		<thead>
		<thead>
				<tr class="title_bar">
				<th class="lefttext first_th" colspan="2">' . $txt['smflinks_ctitle'] . '</th>
				<th class="lefttext">' . $txt['smflinks_description'] .'</th>
				<th class="centertext" align="center">' . $txt['smflinks_totallinks'] . '</th>';
				if ($m_cats)
					echo '<th class="lefttext last_th">' . $txt['smflinks_options'] .'</th>';
		echo '
			</tr>
			</thead>';

		foreach($context['links_cats'] as $row)
		{

			$totallinks  = GetLinkTotals($row['ID_CAT']);

			echo '<tr>';

			if ($row['image'] == '')
				echo '<td colspan="2" class="windowbg2"><a href="' . $scripturl . '?action=links;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></td><td class="windowbg2">' . parse_bbc($row['description']) . '</td>';
			else
			{
				echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;cat=' . $row['ID_CAT'] . '"><img src="' . $row['image'] . '" border="0" alt="" /></a></td>';
				echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></td><td class="windowbg2">' . parse_bbc($row['description']) . '</td>';
			}


			echo '<td class="windowbg2">', $totallinks, '</td>';

			// Show Edit Delete and Order category
			if ($m_cats)
				echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;sa=catup;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtup'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=catdown;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtdown'] . '</a></span>&nbsp;<a href="' . $scripturl . '?action=links;sa=catperm;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txt_perm'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=editcat;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtedit'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=deletecat;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtdel'] . '</a></td>';


			echo '</tr>';

			if ($subcats_linktree != '')
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

echo '

<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
  <tr>
    <td width="50%" valign="top">';

		// Show Top 5 rated
		if (!empty($modSettings['smflinks_setshowtoprate']))
		{
			echo '
            <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smflinks_topfiverated'], '
        </h3>
  </div>
            ';


			echo '<table align="center">';


			foreach($context['links_toprated']  as $row)
			{
				echo '<tr>
				<td align="center">
				<a href="' . $scripturl . '?action=links;sa=visit&id=' . $row['ID_LINK'] . '" target="blank">' . $row['title'] . '</a>&nbsp;';


				echo $txt['smflinks_rating'] . $row['rating'];

				if ($editlink_any || ($editlink_own && $user_info['id'] == $row['ID_MEMBER']))
					echo '&nbsp;<a href="' . $scripturl . '?action=links;sa=editlink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtedit'] . '</a>&nbsp;';
				if ($deletelink_any || ($deletelink_own && $user_info['id'] == $row['ID_MEMBER']))
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

			echo '<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smflinks_topfivevisited'], '
        </h3>
  </div>';

			echo '<table align="center">';
			foreach($context['links_tophits'] as $row)
			{

				echo '<tr>
						<td align="center">';
				echo '<a href="' . $scripturl . '?action=links;sa=visit&id=' . $row['ID_LINK'] . '" target="blank">' . $row['title'] . '</a>&nbsp;';

				echo $txt['smflinks_hits'] . $row['hits'] . '&nbsp;';


				if ($editlink_any || ($editlink_own && $user_info['id'] == $row['ID_MEMBER']))
					echo '<a href="' . $scripturl . '?action=links;sa=editlink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtedit'] . '</a>&nbsp;';
				if ($deletelink_any || ($deletelink_own && $user_info['id'] == $row['ID_MEMBER']))
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
			echo '<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smflinks_linkspanel'], '
        </h3>
  </div>

';

			echo '<div align="center" class="windowbg2"><a href="' . $scripturl . '?action=links;sa=addcat">' . $txt['smflinks_addcat'] . '</a>&nbsp; | ';
			if ($addlink)
				echo '<a href="' . $scripturl . '?action=links;sa=addlink">' . $txt['smflinks_addlink'] . '</a>&nbsp; | ';

			echo '<a href="' . $scripturl . '?action=admin;area=links;sa=admin">' . $txt['smflinks_linkssettings'] . '</a>&nbsp; | ';


			echo '<a href="' . $scripturl . '?action=admin;area=links;sa=adminperm">' . $txt['edit_permissions'] . '</a>';

			echo '</div></div>';
		}
		// See if they can approve links
		if ($a_links)
		{

			$alinks_total = $context['alinks_total'];
			echo '
            <div class="cat_bar">
            <h3 class="catbg centertext">
        ', $txt['smflinks_approvepanel'], '
        </h3>
  </div>
';
			echo '<div align="center" class="windowbg2"><a href="' . $scripturl . '?action=admin;area=links;sa=alist">' . $txt['smflinks_approvelinks'] . '</a><br />';
			echo $txt['smflinks_thereare'] . '<strong>' . $alinks_total . '</strong>' . $txt['smflinks_waitingapproval'];
			echo '</div>';

		}

		// Stats
		if (!empty($modSettings['smflinks_setshowstats']))
		{
			$link_count = $context['link_count'];
			echo '
            <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smflinks_stats'], '
        </h3>
  </div>
  ';
			echo '<div align="center" class="windowbg2">' . $txt['smflinks_thereare'] . '<strong>' . $cat_count . '</strong>' . $txt['smflinks_catand'] . '<strong>' . $link_count . '</strong>' . $txt['smflinks_linkssystem'] . '</div>';

		}
	}
	else
	{
		// Category found show the links


		// Get the category name
		$row = $context['linkscatrow'];

		// Setup their permissions
		$editlink_own = allowedTo('edit_links_own');
		$editlink_any = allowedTo('edit_links_any');
		$deletelink_own = allowedTo('delete_links_own');
		$deletelink_any = allowedTo('delete_links_any');

		$ratelink = allowedTo('rate_links');
		$cattitle =  $row['title'];

		echo '
        <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $cattitle, '
        </h3>
  </div>


';

		ShowSubCats($cat,$m_cats);

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
					$sort = 'm.real_name';
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
		$total = $context['links_total_pages'];

		// Show the links in that category

		// Check if no links where found
		if ($context['totallinks'] == 0)
		{

			echo '

			<table cellspacing="0" cellpadding="10" border="0" align="center" width="100%" class="tborder">
				<tr class="windowbg2">
					<td align="center">', $txt['smflinks_nolinks'],'</td>
				</tr>

					';
			$numofspans = 1;
		}
		else
		{
			$numofspans = 1;
			// There are links found in the category
			echo '<br /><table cellspacing="0" cellpadding="10" border="0" align="center" width="100%" class="table_grid">

		<thead>
		<tr class="title_bar">
						<th scope="col" class="smalltext first_th"><a href="' . $scripturl . '?action=links;cat=' . $cat .  ';start=' . $context['start'] . ';sort=title;sorto=' . $newsorto . '">' . $txt['smflinks_ctitle'] . '</a></th>';

						if (!empty($modSettings['smflinks_disp_description']))
						{
							echo '<th scope="col" class="smalltext">' . $txt['smflinks_description'] .'</th>';
							$numofspans++;
						}


						if (!empty($modSettings['smflinks_disp_hits']))
						{
							echo '<th class="centertext" align="center"><a href="' . $scripturl . '?action=links;cat=' . $cat .  ';start=' . $context['start'] . ';sort=hits;sorto=' . $newsorto . '">' . $txt['smflinks_chits'] . '</a></th>';
							$numofspans++;
						}

						if (!empty($modSettings['smflinks_disp_rating']))
						{
							echo '<th  class="centertext" align="center"><a href="' . $scripturl . '?action=links;cat=' . $cat .  ';start=' . $context['start'] . ';sort=rating;sorto=' . $newsorto . '">' . $txt['smflinks_crating'] . '</a></th>';
							$numofspans++;
						}
						if (!empty($modSettings['smflinks_disp_membername']))
						{
							echo '<th  class="centertext" align="center"><a href="' . $scripturl . '?action=links;cat=' . $cat .  ';start=' . $context['start'] . ';sort=username;sorto=' . $newsorto . '">' . $txt['smflinks_cusername'] . '</a></th>';
							$numofspans++;
						}
						if (!empty($modSettings['smflinks_disp_date']))
						{
							echo '<th  class="centertext" align="center"><a href="' . $scripturl . '?action=links;cat=' . $cat .  ';start=' . $context['start'] . ';sort=date;sorto=' . $newsorto . '">' . $txt['smflinks_cdate'] . '</a></th>';
							$numofspans++;
						}


						$showOptions = false;

						foreach($context['links_cat_list'] as $row)
						{
							if ($editlink_any || ($editlink_own && $user_info['id'] == $row['ID_MEMBER']))
								$showOptions = true;
							if ($deletelink_any || ($deletelink_own && $user_info['id'] == $row['ID_MEMBER']))
								$showOptions = true;
							if ($a_links)
								$showOptions = true;
						}


						if ($showOptions == true)
						{
							echo '
							<th scope="col" class="smalltext last_th">' . $txt['smflinks_options'] .'</th>';


							$numofspans++;
						}

						echo '
					</tr>
					</thead>';

			$styleclass = "windowbg";

			foreach($context['links_cat_list'] as $row)
			{

				echo '<tr class="' . $styleclass . '">';
				echo '<td valign="top"><a href="' . $scripturl . '?action=links;sa=visit&id=' . $row['ID_LINK'] . '" target="blank">' . $row['title'] . '</a></td>';


				if (!empty($modSettings['smflinks_disp_description']))
					if (empty($modSettings['smflinks_setallowbbc']))
						echo '<td>' . $row['description'] . '</td>';
					else
						echo '<td>' . parse_bbc($row['description']) . '</td>';



				if (!empty($modSettings['smflinks_disp_hits']))
					echo '<td>' . $row['hits'] . '</td>';


				if (!empty($modSettings['smflinks_disp_rating']))
				{
					echo '<td>' . $row['rating'];
					if($ratelink)
						echo '<br /><a href="' . $scripturl . '?action=links;sa=rate;value=1&id=' . $row['ID_LINK'] . '"><img src="', $settings['images_url'], '/post/thumbup.png" alt="Good Link" border="0" /></a>&nbsp;&nbsp;<a href="' . $scripturl . '?action=links;sa=rate;value=0&id=' . $row['ID_LINK'] . '"><img src="', $settings['images_url'], '/post/thumbdown.png" alt="Bad Link" border="0" /></a>';

					echo '</td>';
				}
				// Check if it was a guest link
				if (!empty($modSettings['smflinks_disp_membername']))
					if ($row['real_name'] != '')
						echo '<td><a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">'  . $row['real_name'] . '</a></td>';
					else
						echo '<td>' . $txt['smflinks_txtguest'] . '</td>';

				if (!empty($modSettings['smflinks_disp_date']))
					echo '<td>' . timeformat($row['date']) . '</td>';

				if ($showOptions == true)
				{
				echo '<td>';

				if ($editlink_any || ($editlink_own && $user_info['id'] == $row['ID_MEMBER']))
					echo '<a href="' . $scripturl . '?action=links;sa=editlink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtedit'] . '</a>&nbsp;';
				if ($deletelink_any || ($deletelink_own && $user_info['id'] == $row['ID_MEMBER']))
					echo '<a href="' . $scripturl . '?action=links;sa=deletelink&id=' . $row['ID_LINK'] . '">' . $txt['smflinks_txtdel'] . '</a>&nbsp;';
				if ($a_links)
						echo '<a href="' . $scripturl . '?action=links;sa=noapprove&id=' . $row['ID_LINK'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtunapprove'] . '</a>';
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
						' ;


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
				echo '<a href="' . $scripturl . '?action=links;sa=addcat;cat=' . $cat . '">' .$txt['smflinks_addsubcat'] . '</a>&nbsp;| ';


			// See if they are allowed to add links
			if ($addlink)
			{
				echo '<a href="' . $scripturl . '?action=links;sa=addlink;cat=' . $cat . '">' . $txt['smflinks_addlink'] . '</a>&nbsp; | ';

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
	global $scripturl, $txt,$context;

	if (!empty($_REQUEST['cat']))
		$parent = (int) $_REQUEST['cat'];
	else
		$parent = 0;

	echo '
<form name="links" id="links" method="post" action="' . $scripturl . '?action=links&sa=addcat2"  onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smflinks_addcat'], '
        </h3>
  </div>
<table cellspacing="0" align="center" cellpadding="4" class="tborder" width="100%">
  <tr>
    <td width="28%"  class="windowbg2" align="right"><strong>' . $txt['smflinks_ctitle'] . '</strong></td>
    <td width="72%"  class="windowbg2"><input type="text" name="title" size="64" maxlength="100" /></td>
  </tr>
  <tr>
    <td width="28%" class="windowbg2" align="right"><strong>' . $txt['smflinks_parentcategory'] .'</strong>&nbsp;</td>
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
    <td width="28%"   valign="top" class="windowbg2" align="right"><strong>' . $txt['smflinks_description'] . '</strong></td>
    <td width="72%"   class="windowbg2"><table>
	   ';

	if (!function_exists('getLanguages'))
	{
	// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
								<tr class="windowbg2">

									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'bbc'), '
									</td>
								</tr>';
		}

		// What about smileys?
		if (!empty($context['smileys']['postform']))
			echo '
								<tr class="windowbg2">

									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'smileys'), '
									</td>
								</tr>';

		// Show BBC buttons, smileys and textbox.
		echo '
								<tr class="windowbg2">

									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'message'), '
									</td>
								</tr>';
	}
	else
	{
		echo '
								<tr class="windowbg2">
		<td colspan="2">';
			// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
					<div id="bbcBox_message"></div>';
		}

		// What about smileys?
		if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			echo '
					<div id="smileyBox_message"></div>';

		// Show BBC buttons, smileys and textbox.
		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');


		echo '</td></tr>';
	}

	echo '
	   </table></td>
  </tr>
  <tr>
    <td width="28%"   class="windowbg2" align="right"><strong>' . $txt['smflinks_image'] . '</strong>&nbsp</td>
    <td width="72%"   class="windowbg2"><input type="text" name="image" size="64" maxlength="100" /></td>
  </tr>
  <tr>
    <td width="28%" colspan="2"  align="center" class="windowbg2">
    <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
    <input type="submit" value="' . $txt['smflinks_addcat'] . '" name="submit" /></td>

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

	$row = $context['links_edit_cat'];

echo '
<form name="links" id="links" method="post" action="' . $scripturl . '?action=links&sa=editcat2"  onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smflinks_editcat'], '
        </h3>
  </div>
<table cellspacing="0" align="center" cellpadding="4" class="tborder"  width="100%">
  <tr>
    <td width="28%"  class="windowbg2" align="right"><strong>' . $txt['smflinks_ctitle'] . '</strong></td>
    <td width="72%" class="windowbg2"><input type="text" name="title" size="64" maxlength="100" value="' . $row['title'] . '" /></td>
  </tr>
 <tr>
    <td width="28%" class="windowbg2" align="right"><strong>' . $txt['smflinks_parentcategory'] .'</strong>&nbsp;</td>
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
    <td width="28%" valign="top" class="windowbg2" align="right"><strong>' . $txt['smflinks_description'] . '</strong></td>
    <td width="72%" class="windowbg2"><table>
	   ';

		if (!function_exists('getLanguages'))
		{
		// Showing BBC?
			if ($context['show_bbc'])
			{
				echo '
									<tr class="windowbg2">

										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'bbc'), '
										</td>
									</tr>';
			}

			// What about smileys?
			if (!empty($context['smileys']['postform']))
				echo '
									<tr class="windowbg2">

										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'smileys'), '
										</td>
									</tr>';

			// Show BBC buttons, smileys and textbox.
			echo '
									<tr class="windowbg2">

										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'message'), '
										</td>
									</tr>';
		}
		else
	{
		echo '
								<tr class="windowbg2">
		<td colspan="2">';
			// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
					<div id="bbcBox_message"></div>';
		}

		// What about smileys?
		if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			echo '
					<div id="smileyBox_message"></div>';

		// Show BBC buttons, smileys and textbox.
		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');


		echo '</td></tr>';
	}

		echo '
	   </table></td>
  </tr>
  <tr>
    <td width="28%" class="windowbg2" align="right"><strong>' . $txt['smflinks_image'] . '</strong>&nbsp;</td>
    <td width="72%" class="windowbg2"><input type="text" name="image" size="64" maxlength="100" value="' . $row['image'] . '" /></td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="hidden" value="' . $row['ID_CAT'] . '" name="catid" />
    <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
    <input type="submit" value="' . $txt['smflinks_editcat'] . '" name="submit" /></td>

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
    <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smflinks_deltcat'], '
        </h3>
  </div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <strong>' . $txt['smflinks_warndel2'] . '</strong>
    <br />
    <input type="hidden" value="' . $context['links_catid'] . '" name="catid" />
    <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
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
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>';


	echo '
<form method="POST"  name="links" id="links" action="' . $scripturl . '?action=links&sa=addlink2"  onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smflinks_addlink'], '
        </h3>
  </div>
<table cellspacing="0"  align="center" cellpadding="4" class="tborder" width="100%">
  <tr>
    <td width="28%"   class="windowbg2"><strong>' . $txt['smflinks_ctitle'] . '</strong></td>
    <td width="72%"   class="windowbg2"><input type="text" name="title" size="64" maxlength="100" /></td>
  </tr>
  <tr>
    <td width="28%"   class="windowbg2"><strong>' . $txt['smflinks_category'] . '</strong></td>
    <td width="72%"   class="windowbg2"><select name="catid">';
		foreach ($context['links_cats'] as $row)
  			echo '<option value="' . $row['ID_CAT'] . '" ' . (($row['ID_CAT'] == $context['links_catid']) ? 'selected="selected" ' : '') .' >' . $row['title'] . '</option>';

echo '</select>
    </td>
  </tr>
  <tr>
    <td width="28%"   class="windowbg2"><strong>' . $txt['smflinks_url'] . '</strong></td>
    <td width="72%"   class="windowbg2"><input type="text" name="url" size="64" maxlength="250" value="" /></td>
  </tr>
  <tr>
    <td width="28%"   valign="top" class="windowbg2"><strong>' . $txt['smflinks_description'] . '</strong></td>
    <td width="72%"   class="windowbg2">';

		if (!empty($modSettings['smflinks_setallowbbc']))
		{

			echo '  <table>';

			if (!function_exists('getLanguages'))
			{
		// Showing BBC?
			if ($context['show_bbc'])
			{
				echo '
									<tr class="windowbg2">

										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'bbc'), '
										</td>
									</tr>';
			}

			// What about smileys?
			if (!empty($context['smileys']['postform']))
				echo '
									<tr class="windowbg2">

										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'smileys'), '
										</td>
									</tr>';

			// Show BBC buttons, smileys and textbox.
			echo '
									<tr class="windowbg2">

										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'message'), '
										</td>
									</tr>';
			}
			else
	{
		echo '
								<tr class="windowbg2">
		<td colspan="2">';
			// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
					<div id="bbcBox_message"></div>';
		}

		// What about smileys?
		if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			echo '
					<div id="smileyBox_message"></div>';

		// Show BBC buttons, smileys and textbox.
		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');


		echo '</td></tr>';
	}

			echo '
		   </table>';

		}
	else
	{
		// No BBC
		echo '<textarea rows="6" name="descript" cols="54"></textarea><br />';
	}
       	if ($context['show_spellchecking'])
   		echo '
   									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'links\', \'description\');" />';

   echo '
    </td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
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
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>';

	echo '
<form name="links" id="links" method="POST" action="' . $scripturl . '?action=links&sa=editlink2"  onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smflinks_editlink'], '
        </h3>
  </div>
<table cellspacing="0" align="center" cellpadding="4" class="tborder" width="100%">
  <tr>
    <td width="28%"   class="windowbg2"><strong>' . $txt['smflinks_ctitle'] . '</strong><</td>
    <td width="72%"   class="windowbg2"><input type="text" name="title" size="64" maxlength="100" value="' . $context['links_link']['title'] . '" /></td>
  </tr>
  <tr>
    <td width="28%" class="windowbg2"><strong>' . $txt['smflinks_category'] . '</strong></td>
    <td width="72%" class="windowbg2"><select name="catid">';

		foreach($context['links_cats'] as $row2)
  			echo '<option value="' . $row2['ID_CAT'] . '" ' . (($row2['ID_CAT'] == $context['links_link']['ID_CAT']) ? 'selected="selected" ' : '') .'>' . $row2['title'] . '</option>';


echo '</select>
    </td>
  </tr>
  <tr>
    <td width="28%"   class="windowbg2"><strong>' . $txt['smflinks_url'] . '</strong></td>
    <td width="72%"   class="windowbg2"><input type="text" name="url" size="64" maxlength="250" value="' . $context['links_link']['url'] . '" /></td>
  </tr>
  <tr>
    <td width="28%"   valign="top" class="windowbg2"><strong>' . $txt['smflinks_description'] . '</strong></td>
    <td width="72%"   class="windowbg2">';

	if (!empty($modSettings['smflinks_setallowbbc']))
	{

		echo '  <table>';

		if (!function_exists('getLanguages'))
		{
			// Showing BBC?
			if ($context['show_bbc'])
			{
				echo '
									<tr class="windowbg2">

										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'bbc'), '
										</td>
									</tr>';
			}

			// What about smileys?
			if (!empty($context['smileys']['postform']))
				echo '
									<tr class="windowbg2">

										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'smileys'), '
										</td>
									</tr>';

			// Show BBC buttons, smileys and textbox.
			echo '
									<tr class="windowbg2">

										<td colspan="2" align="center">
											', template_control_richedit($context['post_box_name'], 'message'), '
										</td>
									</tr>';
		}
		else
	{
		echo '
								<tr class="windowbg2">
		<td colspan="2">';
			// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
					<div id="bbcBox_message"></div>';
		}

		// What about smileys?
		if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			echo '
					<div id="smileyBox_message"></div>';

		// Show BBC buttons, smileys and textbox.
		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');


		echo '</td></tr>';
	}


		echo '

	   </table>';
	}
	else
	{
		// No BBC
		echo ' <textarea rows="6" name="descript" cols="54">' . $context['links_link']['description'] . '</textarea><br />';
	}
       	if ($context['show_spellchecking'])
   		echo '
   									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'links\', \'description\');" />';
echo '

    </td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="hidden" value="' . $context['link_id'] . '" name="id" />
    <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
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
    <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smflinks_dellink'], '
        </h3>
  </div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <strong>', $txt['smflinks_warndel'], '</strong>
    <br />
    <input type="hidden" value="' . $context['links_id'] . '" name="id" />
    <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
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
    <div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smflinks_approvelinks'], '
        </h3>
  </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
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

	$total = $context['approval_total_links'];

	if (!empty($_REQUEST['start']))
		$context['start'] = (int) $_REQUEST['start'];
	else
		$context['start'] = 0;

    $styleClass = 'windowbg';
	foreach($context['links_approval_list'] as $row)
		{
		  echo '<tr class="' . $styleClass  . '">';
		  echo '<td class="windowbg2"  ><a href="' . $row['url'] . '" target="blank">' . $row['url'] . '</a></td>';
		  echo '<td class="windowbg2"  >' . $row['title'] . '</td>';
		  echo '<td class="windowbg2"  >' . $row['description'] . '</td>';
		  echo '<td class="windowbg2"  >' . $row['catname'] . '</td>';

		  if ($row['real_name'] == '')
		  	 echo '<td class="windowbg2">' . $txt['smflinks_txtguest'] . '</td>';
		  else
		  	echo '<td class="windowbg2"><a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['real_name'] . '</a></td>';


		  echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;sa=approve&id=' . $row['ID_LINK'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtapprove'] . '</a>&nbsp;';

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
						' ;

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
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="sales@visualbasiczone.com" />
	<input type="hidden" name="item_name" value="SMF Links" />
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="no_note" value="1" />
	<input type="hidden" name="currency_code" value="USD" />
	<input type="hidden" name="tax" value="0" />
	<input type="hidden" name="bn" value="PP-DonationsBF" />
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
<br />

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
<div class="cat_bar">
		<h3 class="catbg">
        ' .$txt['smflinks_linkssettings']  . '
        </h3>
  </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">

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
                <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="submit" name="savesettings" value="' . $txt['smflinks_settings_save'] .'" />
			</form>
			</td>
		</tr>

		<tr>
		<td class="windowbg">
		<strong>Has SMF Links helped you?</strong> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="sales@visualbasiczone.com" />
	<input type="hidden" name="item_name" value="SMF Links" />
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="no_note" value="1" />
	<input type="hidden" name="currency_code" value="USD" />
	<input type="hidden" name="tax" value="0" />
	<input type="hidden" name="bn" value="PP-DonationsBF" />
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
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
	global $scripturl, $txt, $context, $cat_sep;

echo '
	<div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smflinks_managecats'], '
        </h3>
  </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg2">
			<td>

		<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="tborder">
			<tr>
				<td class="titlebg">' . $txt['smflinks_ctitle'] . '</td>
				<td class="titlebg">' . $txt['smflinks_description'] .'</td>
				<td class="titlebg">' . $txt['smflinks_totallinks'] . '</td>
				<td class="titlebg">' . $txt['smflinks_options'] .'</td>
			</tr>
		';

        $styleClass = 'windowbg2';
		foreach($context['links_cats'] as $row)
		{


			if ($row['ID_PARENT'] == 0)
			{
				$totallinks = GetLinkTotals($row['ID_CAT']);

				echo '<tr>';
				echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></td>';
				echo '<td class="windowbg2">' . parse_bbc($row['description']) . '</td>';
				echo '<td class="windowbg2">' . $totallinks . '</td>';

				// Show Edit Delete and Order category
				echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;sa=catperm;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txt_perm'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=catup;cat=' . $row['ID_CAT'] . ';a=admin;' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtup'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=catdown;cat=' . $row['ID_CAT'] . ';a=admin;' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtdown'] . '</a></span>&nbsp;<a href="' . $scripturl . '?action=links;sa=editcat;cat=' . $row['ID_CAT'] . ';a=admin;' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtedit'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=deletecat;cat=' . $row['ID_CAT'] . ';a=admin;' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtdel'] . '</a></td>';
				echo '</tr>';

				if ($styleClass == 'windowbg')
					$styleClass = 'windowbg2';
				else
					$styleClass = 'windowbg';

				$cat_sep = 1;
				GetManageSubCats($row['ID_CAT'],$context['links_cats']);
				$cat_sep = 0;

			}
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
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="sales@visualbasiczone.com" />
	<input type="hidden" name="item_name" value="SMF Links" />
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="no_note" value="1" />
	<input type="hidden" name="currency_code" value="USD" />
	<input type="hidden" name="tax" value="0" />
	<input type="hidden" name="bn" value="PP-DonationsBF" />
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
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
	<div class="cat_bar">
		<h3 class="catbg">
        ' .$txt['smflinks_text_catperm'] . ' - ' . $context['links_cat_name']  . '
        </h3>
  </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg">
		<td>
		<form method="post" action="' . $scripturl . '?action=links;sa=catperm2">
		<table align="center" class="tborder">
		<tr class="titlebg">
			<td colspan="2">'  . $txt['smflinks_text_addperm'] . '</td>
		</tr>
	    <tr class="windowbg">
			  	<td align="right"><strong>' . $txt['smflinks_groupname'] . '</strong>&nbsp;</td>
			  	<td><select name="group_name">
			  					<option value="-1">' . $txt['membergroups_guests'] . '</option>
								<option value="0">' . $txt['membergroups_members'] . '</option>';
								foreach ($context['groups'] as $group)
									echo '<option value="', $group['ID_GROUP'], '">', $group['group_name'], '</option>';

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
                <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
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

		//Show the member groups
        $styleClass = 'windowbg';
		  	foreach($context['links_mgroups'] as $row)
			{

				echo '<tr class="' . $styleClass  . '">';
				echo '<td>'  . $row['group_name'] . '</td>';
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
			foreach($context['links_guests_groups'] as $row)
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

	<div class="cat_bar">
		<h3 class="catbg">
        ',  $txt['smflinks_catpermlist'], '
        </h3>
  </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
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

		//Show the member groups
        $styleClass = 'windowbg';
		  	foreach($context['links_m_groups'] as $row)
			{

				echo '<tr class="' . $styleClass  . '">';
				echo '<td>'  . $row['group_name'] . '</td>';
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
		  	foreach($context['links_reg_groups'] as $row)
			{

				echo '<tr class="' . $styleClass  . '">';
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
		  	foreach($context['links_guests_groups'] as $row)
			{

				echo '<tr class="' . $styleClass  . '">';
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
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="sales@visualbasiczone.com" />
	<input type="hidden" name="item_name" value="SMF Links" />
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="no_note" value="1" />
	<input type="hidden" name="currency_code" value="USD" />
	<input type="hidden" name="tax" value="0" />
	<input type="hidden" name="bn" value="PP-DonationsBF" />
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
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
	echo '<br /><div align="center"><span class="smalltext">Powered by: <a href="https://www.smfhacks.com" target="blank">SMF Links</a> by <a href="https://www.createaforum.com" title="Forum Hosting">CreateAForum.com</a></span></div>';

}

function GetManageSubCats($ID_PARENT,$categories)
{
	global $currentclass, $cat_sep, $scripturl, $txt, $context;

		foreach ($categories as $i => $row)
		{
			if ($row['ID_PARENT'] == $ID_PARENT)
			{

				$totallinks = GetLinkTotals($row['ID_CAT']);
				echo '<tr>';

				echo '<td class="windowbg2">',str_repeat('-',$cat_sep),'<a href="' . $scripturl . '?action=links;cat=' . $row['ID_CAT'] . '">' . parse_bbc($row['title']) . '</a></td>';
				echo '<td class="windowbg2">' . parse_bbc($row['description']) . '</td>';
				echo '<td class="windowbg2">' . $totallinks . '</td>';

				// Show Edit Delete and Order category
				echo '<td class="windowbg2"><a href="' . $scripturl . '?action=links;sa=catperm;cat=' . $row['ID_CAT'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txt_perm'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=catup;cat=' . $row['ID_CAT'] . ';a=admin;' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtup'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=catdown;cat=' . $row['ID_CAT'] . ';a=admin;' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtdown'] . '</a></span>&nbsp;<a href="' . $scripturl . '?action=links;sa=editcat;cat=' . $row['ID_CAT'] . ';a=admin;' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtedit'] . '</a>&nbsp;<a href="' . $scripturl . '?action=links;sa=deletecat;cat=' . $row['ID_CAT'] . ';a=admin;' . $context['session_var'] . '=' . $context['session_id'] . '">' . $txt['smflinks_txtdel'] . '</a></td>';
				echo '</tr>';


				$cat_sep++;
				GetManageSubCats($row['ID_CAT'],$categories);
				$cat_sep--;


			}
		}

}