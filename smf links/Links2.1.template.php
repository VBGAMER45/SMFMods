<?php
/*
SMF Links
Version 5.0
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
			<h3 class="catbg centertext">', $txt['smflinks_indextitle'], '</h3>
		</div>';

		// List all the categories
		$cat_count = $context['cat_count'];

		echo '
		<table class="table_grid" style="width:100%;">
			<thead>
				<tr class="title_bar">
					<th class="lefttext" colspan="2">', $txt['smflinks_ctitle'], '</th>
					<th class="lefttext">', $txt['smflinks_description'], '</th>
					<th class="centertext">', $txt['smflinks_totallinks'], '</th>';

		if ($m_cats)
			echo '
					<th class="lefttext">', $txt['smflinks_options'], '</th>';

		echo '
				</tr>
			</thead>
			<tbody>';

		foreach ($context['links_cats'] as $row)
		{
			$totallinks = GetLinkTotals($row['ID_CAT']);

			echo '
				<tr class="windowbg">';

			if ($row['image'] == '')
				echo '
					<td colspan="2"><a href="', $scripturl, '?action=links;cat=', $row['ID_CAT'], '">', parse_bbc($row['title']), '</a></td>
					<td>', parse_bbc($row['description']), '</td>';
			else
				echo '
					<td><a href="', $scripturl, '?action=links;cat=', $row['ID_CAT'], '"><img src="', $row['image'], '" alt="', $row['title'], '" /></a></td>
					<td><a href="', $scripturl, '?action=links;cat=', $row['ID_CAT'], '">', parse_bbc($row['title']), '</a></td>
					<td>', parse_bbc($row['description']), '</td>';

			echo '
					<td class="centertext">', $totallinks, '</td>';

			if ($m_cats)
				echo '
					<td>
						<a href="', $scripturl, '?action=links;sa=catup;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtup'], '</a>
						<a href="', $scripturl, '?action=links;sa=catdown;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdown'], '</a>
						<a href="', $scripturl, '?action=links;sa=catperm;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txt_perm'], '</a>
						<a href="', $scripturl, '?action=links;sa=editcat;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtedit'], '</a>
						<a href="', $scripturl, '?action=links;sa=deletecat;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a>
					</td>';

			echo '
				</tr>';

			if ($subcats_linktree != '')
				echo '
				<tr class="windowbg">
					<td colspan="', ($m_cats ? '5' : '4'), '"><span class="smalltext">', $txt['smflinks_sub_cats'], $subcats_linktree, '</span></td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';

		// Permissions for Top 5 Rated, Top 5 Hits and Approval
		$editlink_own = allowedTo('edit_links_own');
		$editlink_any = allowedTo('edit_links_any');
		$deletelink_own = allowedTo('delete_links_own');
		$deletelink_any = allowedTo('delete_links_any');

		echo '
		<div class="smflinks_stats_wrapper" style="display:flex; gap:16px; margin-top:8px;">';

		// Show Top 5 rated
		if (!empty($modSettings['smflinks_setshowtoprate']))
		{
			echo '
			<div style="flex:1;">
				<div class="cat_bar">
					<h3 class="catbg centertext">', $txt['smflinks_topfiverated'], '</h3>
				</div>
				<div class="windowbg">';

			foreach ($context['links_toprated'] as $row)
			{
				echo '
					<p class="centertext">
						<a href="', $scripturl, '?action=links;sa=visit;id=', $row['ID_LINK'], '" target="_blank">', $row['title'], '</a>
						', $txt['smflinks_rating'], $row['rating'];

				if ($editlink_any || ($editlink_own && $user_info['id'] == $row['ID_MEMBER']))
					echo ' <a href="', $scripturl, '?action=links;sa=editlink;id=', $row['ID_LINK'], '">', $txt['smflinks_txtedit'], '</a>';
				if ($deletelink_any || ($deletelink_own && $user_info['id'] == $row['ID_MEMBER']))
					echo ' <a href="', $scripturl, '?action=links;sa=deletelink;id=', $row['ID_LINK'], '">', $txt['smflinks_txtdel'], '</a>';

				echo '
					</p>';
			}

			echo '
				</div>
			</div>';
		}

		// Show Top 5 hits
		if (!empty($modSettings['smflinks_setshowmostvisited']))
		{
			echo '
			<div style="flex:1;">
				<div class="cat_bar">
					<h3 class="catbg centertext">', $txt['smflinks_topfivevisited'], '</h3>
				</div>
				<div class="windowbg">';

			foreach ($context['links_tophits'] as $row)
			{
				echo '
					<p class="centertext">
						<a href="', $scripturl, '?action=links;sa=visit;id=', $row['ID_LINK'], '" target="_blank">', $row['title'], '</a>
						', $txt['smflinks_hits'], $row['hits'];

				if ($editlink_any || ($editlink_own && $user_info['id'] == $row['ID_MEMBER']))
					echo ' <a href="', $scripturl, '?action=links;sa=editlink;id=', $row['ID_LINK'], '">', $txt['smflinks_txtedit'], '</a>';
				if ($deletelink_any || ($deletelink_own && $user_info['id'] == $row['ID_MEMBER']))
					echo ' <a href="', $scripturl, '?action=links;sa=deletelink;id=', $row['ID_LINK'], '">', $txt['smflinks_txtdel'], '</a>';

				echo '
					</p>';
			}

			echo '
				</div>
			</div>';
		}

		echo '
		</div>';

		// See if they are allowed to add categories Main Index only
		if ($m_cats)
		{
			echo '
			<div class="cat_bar">
				<h3 class="catbg centertext">', $txt['smflinks_linkspanel'], '</h3>
			</div>
			<div class="windowbg centertext">
				<a href="', $scripturl, '?action=links;sa=addcat">', $txt['smflinks_addcat'], '</a> | ';

			if ($addlink)
				echo '<a href="', $scripturl, '?action=links;sa=addlink">', $txt['smflinks_addlink'], '</a> | ';

			echo '<a href="', $scripturl, '?action=admin;area=links;sa=admin">', $txt['smflinks_linkssettings'], '</a> | ';
			echo '<a href="', $scripturl, '?action=admin;area=links;sa=adminperm">', $txt['edit_permissions'], '</a>';

			echo '
			</div>';
		}

		// See if they can approve links
		if ($a_links)
		{
			$alinks_total = $context['alinks_total'];
			echo '
			<div class="cat_bar">
				<h3 class="catbg centertext">', $txt['smflinks_approvepanel'], '</h3>
			</div>
			<div class="windowbg centertext">
				<a href="', $scripturl, '?action=admin;area=links;sa=alist">', $txt['smflinks_approvelinks'], '</a><br />
				', $txt['smflinks_thereare'], '<strong>', $alinks_total, '</strong>', $txt['smflinks_waitingapproval'], '
			</div>';
		}

		// Stats
		if (!empty($modSettings['smflinks_setshowstats']))
		{
			$link_count = $context['link_count'];
			echo '
			<div class="cat_bar">
				<h3 class="catbg centertext">', $txt['smflinks_stats'], '</h3>
			</div>
			<div class="windowbg centertext">
				', $txt['smflinks_thereare'], '<strong>', $cat_count, '</strong>', $txt['smflinks_catand'], '<strong>', $link_count, '</strong>', $txt['smflinks_linkssystem'], '
			</div>';
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
		$cattitle = $row['title'];

		echo '
		<div class="cat_bar">
			<h3 class="catbg centertext">', $cattitle, '</h3>
		</div>';

		// Show subcategories - prepare data then render
		ShowSubCats($cat);
		template_show_subcats($m_cats);

		if (!empty($_REQUEST['start']))
			$context['start'] = (int) $_REQUEST['start'];
		else
			$context['start'] = 0;

		if (!empty($_REQUEST['sort']))
		{
			switch ($_REQUEST['sort'])
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

		// Change sort order for links
		if ($sorto == 'DESC')
			$newsorto = 'ASC';
		else
			$newsorto = 'DESC';

		if (empty($modSettings['smflinks_setlinksperpage']))
			$modSettings['smflinks_setlinksperpage'] = 10;

		// Get Total Pages
		$total = $context['links_total_pages'];

		// Check if no links were found
		if ($context['totallinks'] == 0)
		{
			echo '
			<div class="windowbg centertext" style="margin-top:8px;">
				', $txt['smflinks_nolinks'], '
			</div>';

			$numofspans = 1;
		}
		else
		{
			$numofspans = 1;
			// There are links found in the category
			echo '
			<table class="table_grid" style="width:100%; margin-top:8px;">
				<thead>
					<tr class="title_bar">
						<th class="lefttext"><a href="', $scripturl, '?action=links;cat=', $cat, ';start=', $context['start'], ';sort=title;sorto=', $newsorto, '">', $txt['smflinks_ctitle'], '</a></th>';

			if (!empty($modSettings['smflinks_disp_description']))
			{
				echo '
						<th class="lefttext">', $txt['smflinks_description'], '</th>';
				$numofspans++;
			}

			if (!empty($modSettings['smflinks_disp_hits']))
			{
				echo '
						<th class="centertext"><a href="', $scripturl, '?action=links;cat=', $cat, ';start=', $context['start'], ';sort=hits;sorto=', $newsorto, '">', $txt['smflinks_chits'], '</a></th>';
				$numofspans++;
			}

			if (!empty($modSettings['smflinks_disp_rating']))
			{
				echo '
						<th class="centertext"><a href="', $scripturl, '?action=links;cat=', $cat, ';start=', $context['start'], ';sort=rating;sorto=', $newsorto, '">', $txt['smflinks_crating'], '</a></th>';
				$numofspans++;
			}

			if (!empty($modSettings['smflinks_disp_membername']))
			{
				echo '
						<th class="centertext"><a href="', $scripturl, '?action=links;cat=', $cat, ';start=', $context['start'], ';sort=username;sorto=', $newsorto, '">', $txt['smflinks_cusername'], '</a></th>';
				$numofspans++;
			}

			if (!empty($modSettings['smflinks_disp_date']))
			{
				echo '
						<th class="centertext"><a href="', $scripturl, '?action=links;cat=', $cat, ';start=', $context['start'], ';sort=date;sorto=', $newsorto, '">', $txt['smflinks_cdate'], '</a></th>';
				$numofspans++;
			}

			$showOptions = false;
			foreach ($context['links_cat_list'] as $row)
			{
				if ($editlink_any || ($editlink_own && $user_info['id'] == $row['ID_MEMBER']))
					$showOptions = true;
				if ($deletelink_any || ($deletelink_own && $user_info['id'] == $row['ID_MEMBER']))
					$showOptions = true;
				if ($a_links)
					$showOptions = true;
			}

			if ($showOptions)
			{
				echo '
						<th class="lefttext">', $txt['smflinks_options'], '</th>';
				$numofspans++;
			}

			echo '
					</tr>
				</thead>
				<tbody>';

			foreach ($context['links_cat_list'] as $row)
			{
				echo '
					<tr class="windowbg">
						<td>';

				if (!empty($modSettings['smflinks_disp_thumbnail']) && !empty($row['url']))
					echo '<div style="margin-bottom:4px;"><img src="https://image.thum.io/get/width/200/', $row['url'], '" alt="" loading="lazy" style="max-width:200px; max-height:120px;" /></div>';

				echo '<a href="', $scripturl, '?action=links;sa=visit;id=', $row['ID_LINK'], '" target="_blank">', $row['title'], '</a></td>';

				if (!empty($modSettings['smflinks_disp_description']))
				{
					if (empty($modSettings['smflinks_setallowbbc']))
						echo '
						<td>', $row['description'], '</td>';
					else
						echo '
						<td>', parse_bbc($row['description']), '</td>';
				}

				if (!empty($modSettings['smflinks_disp_hits']))
					echo '
						<td class="centertext">', $row['hits'], '</td>';

				if (!empty($modSettings['smflinks_disp_rating']))
				{
					echo '
						<td class="centertext">', $row['rating'];
					if ($ratelink)
						echo '<br />
							<a href="', $scripturl, '?action=links;sa=rate;value=1;id=', $row['ID_LINK'], '"><img src="', $settings['images_url'], '/post/thumbup.png" alt="', $txt['smflinks_crating'], ' +" /></a>
							<a href="', $scripturl, '?action=links;sa=rate;value=0;id=', $row['ID_LINK'], '"><img src="', $settings['images_url'], '/post/thumbdown.png" alt="', $txt['smflinks_crating'], ' -" /></a>';
					echo '</td>';
				}

				if (!empty($modSettings['smflinks_disp_membername']))
				{
					if ($row['real_name'] != '')
						echo '
						<td class="centertext"><a href="', $scripturl, '?action=profile;u=', $row['ID_MEMBER'], '">', $row['real_name'], '</a></td>';
					else
						echo '
						<td class="centertext">', $txt['smflinks_txtguest'], '</td>';
				}

				if (!empty($modSettings['smflinks_disp_date']))
					echo '
						<td class="centertext">', timeformat($row['date']), '</td>';

				if ($showOptions)
				{
					echo '
						<td>';
					if ($editlink_any || ($editlink_own && $user_info['id'] == $row['ID_MEMBER']))
						echo '<a href="', $scripturl, '?action=links;sa=editlink;id=', $row['ID_LINK'], '">', $txt['smflinks_txtedit'], '</a> ';
					if ($deletelink_any || ($deletelink_own && $user_info['id'] == $row['ID_MEMBER']))
						echo '<a href="', $scripturl, '?action=links;sa=deletelink;id=', $row['ID_LINK'], '">', $txt['smflinks_txtdel'], '</a> ';
					if ($a_links)
						echo '<a href="', $scripturl, '?action=links;sa=noapprove;id=', $row['ID_LINK'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtunapprove'], '</a>';
					echo '</td>';
				}

				echo '
					</tr>';
			}

			// Show the pages
			echo '
				</tbody>
			</table>
			<div class="pagesection">
				<div class="pagelinks">';

			$context['page_index'] = constructPageIndex($scripturl . '?action=links;cat=' . $cat . (!empty($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (!empty($_REQUEST['sorto']) ? ';sorto=' . $_REQUEST['sorto'] : ''), $context['start'], $total, $modSettings['smflinks_setlinksperpage']);
			echo $context['page_index'];

			echo '
				</div>
			</div>';
		}

		// Show action bar
		echo '
		<div class="windowbg centertext" style="margin-top:8px;">';

		if ($m_cats)
			echo '<a href="', $scripturl, '?action=links;sa=addcat;cat=', $cat, '">', $txt['smflinks_addsubcat'], '</a> | ';

		if ($addlink)
			echo '<a href="', $scripturl, '?action=links;sa=addlink;cat=', $cat, '">', $txt['smflinks_addlink'], '</a> | ';

		echo '<a href="', $scripturl, '?action=links">', $txt['smflinks_returnindex'], '</a>';

		echo '
		</div>';
	}

	LinksCopyright();
}

// Render subcategories from data prepared by ShowSubCats()
function template_show_subcats($m_cats)
{
	global $scripturl, $context, $txt;

	if (empty($context['links_subcats']))
		return;

	echo '
	<table class="table_grid" style="width:100%; margin-top:8px;">
		<thead>
			<tr class="title_bar">
				<th class="lefttext" colspan="2">', $txt['smflinks_ctitle'], '</th>
				<th class="lefttext">', $txt['smflinks_description'], '</th>
				<th class="centertext">', $txt['smflinks_totallinks'], '</th>';

	if ($m_cats)
		echo '
				<th class="lefttext">', $txt['smflinks_options'], '</th>';

	echo '
			</tr>
		</thead>
		<tbody>';

	foreach ($context['links_subcats'] as $row)
	{
		echo '
			<tr class="windowbg">';

		if (empty($row['image']))
			echo '
				<td colspan="2"><a href="', $scripturl, '?action=links;cat=', $row['ID_CAT'], '">', parse_bbc($row['title']), '</a></td>
				<td>', parse_bbc($row['description']), '</td>';
		else
			echo '
				<td><a href="', $scripturl, '?action=links;cat=', $row['ID_CAT'], '"><img src="', $row['image'], '" alt="', $row['title'], '" /></a></td>
				<td><a href="', $scripturl, '?action=links;cat=', $row['ID_CAT'], '">', parse_bbc($row['title']), '</a></td>
				<td>', parse_bbc($row['description']), '</td>';

		echo '
				<td class="centertext">', $row['totallinks'], '</td>';

		if ($m_cats)
			echo '
				<td>
					<a href="', $scripturl, '?action=links;sa=catup;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtup'], '</a>
					<a href="', $scripturl, '?action=links;sa=catdown;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdown'], '</a>
					<a href="', $scripturl, '?action=links;sa=catperm;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txt_perm'], '</a>
					<a href="', $scripturl, '?action=links;sa=editcat;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtedit'], '</a>
					<a href="', $scripturl, '?action=links;sa=deletecat;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a>
				</td>';

		echo '
			</tr>';

		if (!empty($row['subcats_linktree']))
			echo '
			<tr class="windowbg">
				<td colspan="', ($m_cats ? '5' : '4'), '"><span class="smalltext">', $txt['smflinks_sub_cats'], $row['subcats_linktree'], '</span></td>
			</tr>';
	}

	echo '
		</tbody>
	</table>';
}

function template_addcat()
{
	global $scripturl, $txt, $context;

	if (!empty($_REQUEST['cat']))
		$parent = (int) $_REQUEST['cat'];
	else
		$parent = 0;

	echo '
	<form name="links" id="links" method="post" action="', $scripturl, '?action=links;sa=addcat2" onsubmit="submitonce(this);">
		<div class="cat_bar">
			<h3 class="catbg centertext">', $txt['smflinks_addcat'], '</h3>
		</div>
		<div class="roundframe noup">
			<dl class="settings">
				<dt><strong>', $txt['smflinks_ctitle'], '</strong></dt>
				<dd><input type="text" name="title" size="64" maxlength="100" /></dd>

				<dt><strong>', $txt['smflinks_parentcategory'], '</strong></dt>
				<dd>
					<select name="parent">
						<option value="0">', $txt['smflinks_text_catnone'], '</option>';

	foreach ($context['links_cat'] as $category)
		echo '
						<option value="', $category['ID_CAT'], '"', ($parent == $category['ID_CAT'] ? ' selected="selected"' : ''), '>', $category['title'], '</option>';

	echo '
					</select>
				</dd>

				<dt><strong>', $txt['smflinks_description'], '</strong></dt>
				<dd>';

	echo '
					<div id="bbcBox_message"></div>';

	if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
		echo '
					<div id="smileyBox_message"></div>';

	echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

	echo '
				</dd>

				<dt><strong>', $txt['smflinks_image'], '</strong></dt>
				<dd><input type="text" name="image" size="64" maxlength="100" /></dd>
			</dl>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="submit" value="', $txt['smflinks_addcat'], '" name="submit" class="button" />
		</div>
	</form>';

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
	<form name="links" id="links" method="post" action="', $scripturl, '?action=links;sa=editcat2" onsubmit="submitonce(this);">
		<div class="cat_bar">
			<h3 class="catbg centertext">', $txt['smflinks_editcat'], '</h3>
		</div>
		<div class="roundframe noup">
			<dl class="settings">
				<dt><strong>', $txt['smflinks_ctitle'], '</strong></dt>
				<dd><input type="text" name="title" size="64" maxlength="100" value="', $row['title'], '" /></dd>

				<dt><strong>', $txt['smflinks_parentcategory'], '</strong></dt>
				<dd>
					<select name="parent">
						<option value="0">', $txt['smflinks_text_catnone'], '</option>';

	foreach ($context['links_cat'] as $category)
	{
		if ($category['ID_CAT'] == $cat)
			continue;

		echo '
						<option value="', $category['ID_CAT'], '"', ($row['ID_PARENT'] == $category['ID_CAT'] ? ' selected="selected"' : ''), '>', $category['title'], '</option>';
	}

	echo '
					</select>
				</dd>

				<dt><strong>', $txt['smflinks_description'], '</strong></dt>
				<dd>';

	if ($context['show_bbc'])
		echo '
					<div id="bbcBox_message"></div>';

	if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
		echo '
					<div id="smileyBox_message"></div>';

	echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

	echo '
				</dd>

				<dt><strong>', $txt['smflinks_image'], '</strong></dt>
				<dd><input type="text" name="image" size="64" maxlength="100" value="', $row['image'], '" /></dd>
			</dl>
			<input type="hidden" value="', $row['ID_CAT'], '" name="catid" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="submit" value="', $txt['smflinks_editcat'], '" name="submit" class="button" />
		</div>
	</form>';

	LinksCopyright();
}

function template_deletecat()
{
	global $scripturl, $txt, $context;

	echo '
	<form method="post" action="', $scripturl, '?action=links;sa=deletecat2">
		<div class="cat_bar">
			<h3 class="catbg centertext">', $txt['smflinks_deltcat'], '</h3>
		</div>
		<div class="roundframe noup centertext">
			<strong>', $txt['smflinks_warndel2'], '</strong>
			<br /><br />
			<input type="hidden" value="', $context['links_catid'], '" name="catid" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="submit" value="', $txt['smflinks_deltcat'], '" name="submit" class="button" />
		</div>
	</form>';

	LinksCopyright();
}

function template_addlink()
{
	global $scripturl, $txt, $modSettings, $context, $settings;

	echo '
	<form method="post" name="links" id="links" action="', $scripturl, '?action=links;sa=addlink2" onsubmit="submitonce(this);">
		<div class="cat_bar">
			<h3 class="catbg centertext">', $txt['smflinks_addlink'], '</h3>
		</div>
		<div class="roundframe noup">
			<dl class="settings">
				<dt><strong>', $txt['smflinks_ctitle'], '</strong></dt>
				<dd><input type="text" name="title" size="64" maxlength="100" /></dd>

				<dt><strong>', $txt['smflinks_category'], '</strong></dt>
				<dd>
					<select name="catid">';

	foreach ($context['links_cats'] as $row)
		echo '
						<option value="', $row['ID_CAT'], '"', ($row['ID_CAT'] == $context['links_catid'] ? ' selected="selected"' : ''), '>', $row['title'], '</option>';

	echo '
					</select>
				</dd>

				<dt><strong>', $txt['smflinks_url'], '</strong></dt>
				<dd><input type="url" name="url" size="64" maxlength="250" value="" pattern="https?://.*" placeholder="https://" required /></dd>

				<dt><strong>', $txt['smflinks_description'], '</strong></dt>
				<dd>';

	if (!empty($modSettings['smflinks_setallowbbc']))
	{
		if ($context['show_bbc'])
			echo '
					<div id="bbcBox_message"></div>';

		if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			echo '
					<div id="smileyBox_message"></div>';

		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
	}
	else
		echo '
					<textarea rows="6" name="descript" cols="54"></textarea>';

	if ($context['show_spellchecking'])
		echo '
					<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'links\', \'description\');" class="button" />';

	echo '
				</dd>
			</dl>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="submit" value="', $txt['smflinks_addlink'], '" name="submit" class="button" />
		</div>
	</form>';

	if ($context['show_spellchecking'])
		echo '
	<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow">
		<input type="hidden" name="spellstring" value="" />
	</form>';

	LinksCopyright();
}

function template_editlink()
{
	global $context, $scripturl, $txt, $modSettings, $settings;

	echo '
	<form name="links" id="links" method="post" action="', $scripturl, '?action=links;sa=editlink2" onsubmit="submitonce(this);">
		<div class="cat_bar">
			<h3 class="catbg centertext">', $txt['smflinks_editlink'], '</h3>
		</div>
		<div class="roundframe noup">
			<dl class="settings">
				<dt><strong>', $txt['smflinks_ctitle'], '</strong></dt>
				<dd><input type="text" name="title" size="64" maxlength="100" value="', $context['links_link']['title'], '" /></dd>

				<dt><strong>', $txt['smflinks_category'], '</strong></dt>
				<dd>
					<select name="catid">';

	foreach ($context['links_cats'] as $row2)
		echo '
						<option value="', $row2['ID_CAT'], '"', ($row2['ID_CAT'] == $context['links_link']['ID_CAT'] ? ' selected="selected"' : ''), '>', $row2['title'], '</option>';

	echo '
					</select>
				</dd>

				<dt><strong>', $txt['smflinks_url'], '</strong></dt>
				<dd><input type="url" name="url" size="64" maxlength="250" value="', $context['links_link']['url'], '" pattern="https?://.*" placeholder="https://" required /></dd>

				<dt><strong>', $txt['smflinks_description'], '</strong></dt>
				<dd>';

	if (!empty($modSettings['smflinks_setallowbbc']))
	{
		if ($context['show_bbc'])
			echo '
					<div id="bbcBox_message"></div>';

		if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			echo '
					<div id="smileyBox_message"></div>';

		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
	}
	else
		echo '
					<textarea rows="6" name="descript" cols="54">', $context['links_link']['description'], '</textarea>';

	if ($context['show_spellchecking'])
		echo '
					<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'links\', \'description\');" class="button" />';

	echo '
				</dd>
			</dl>
			<input type="hidden" value="', $context['link_id'], '" name="id" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="submit" value="', $txt['smflinks_editlink'], '" name="submit" class="button" />
		</div>
	</form>';

	if ($context['show_spellchecking'])
		echo '
	<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow">
		<input type="hidden" name="spellstring" value="" />
	</form>';

	LinksCopyright();
}

function template_deletelink()
{
	global $scripturl, $txt, $context;

	echo '
	<form method="post" action="', $scripturl, '?action=links;sa=deletelink2">
		<div class="cat_bar">
			<h3 class="catbg centertext">', $txt['smflinks_dellink'], '</h3>
		</div>
		<div class="roundframe noup centertext">
			<strong>', $txt['smflinks_warndel'], '</strong>
			<br /><br />
			<input type="hidden" value="', $context['links_id'], '" name="id" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="submit" value="', $txt['smflinks_dellink'], '" name="submit" class="button" />
		</div>
	</form>';

	LinksCopyright();
}

function template_approvelinks()
{
	global $scripturl, $txt, $context;

	// Edit and Delete permissions
	$editlink = allowedTo('edit_links_any');
	$deletelink = allowedTo('delete_links_any');

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['smflinks_approvelinks'], '</h3>
	</div>
	<div class="windowbg">
		<table class="table_grid" style="width:100%;">
			<thead>
				<tr class="title_bar">
					<th class="lefttext">', $txt['smflinks_url'], '</th>
					<th class="lefttext">', $txt['smflinks_ctitle'], '</th>
					<th class="lefttext">', $txt['smflinks_description'], '</th>
					<th class="lefttext">', $txt['smflinks_category'], '</th>
					<th class="lefttext">', $txt['smflinks_submittedby'], '</th>
					<th class="lefttext">', $txt['smflinks_options'], '</th>
				</tr>
			</thead>
			<tbody>';

	$total = $context['approval_total_links'];

	if (!empty($_REQUEST['start']))
		$context['start'] = (int) $_REQUEST['start'];
	else
		$context['start'] = 0;

	foreach ($context['links_approval_list'] as $row)
	{
		echo '
				<tr class="windowbg">
					<td><a href="', $row['url'], '" target="_blank">', $row['url'], '</a></td>
					<td>', $row['title'], '</td>
					<td>', $row['description'], '</td>
					<td>', $row['catname'], '</td>';

		if ($row['real_name'] == '')
			echo '
					<td>', $txt['smflinks_txtguest'], '</td>';
		else
			echo '
					<td><a href="', $scripturl, '?action=profile;u=', $row['ID_MEMBER'], '">', $row['real_name'], '</a></td>';

		echo '
					<td>
						<a href="', $scripturl, '?action=links;sa=approve;id=', $row['ID_LINK'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtapprove'], '</a> ';

		if ($editlink)
			echo '<a href="', $scripturl, '?action=links;sa=editlink;id=', $row['ID_LINK'], '">', $txt['smflinks_txtedit'], '</a> ';
		if ($deletelink)
			echo '<a href="', $scripturl, '?action=links;sa=deletelink;id=', $row['ID_LINK'], '">', $txt['smflinks_txtdel'], '</a>';

		echo '
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>
	</div>
	<div class="pagesection">
		<div class="pagelinks">';

	$context['page_index'] = constructPageIndex($scripturl . '?action=links;sa=alist', $context['start'], $total, 20);
	echo $context['page_index'];

	echo '
		</div>
	</div>';

	LinksCopyright();
}

function template_settings()
{
	global $scripturl, $txt, $modSettings, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['smflinks_linkssettings'], '</h3>
	</div>
	<div class="windowbg">
		<form method="post" action="', $scripturl, '?action=links;sa=admin2">
			<dl class="settings">
				<dt>', $txt['smflinks_setlinksperpage'], '</dt>
				<dd><input type="text" name="smflinks_setlinksperpage" value="', $modSettings['smflinks_setlinksperpage'], '" size="5" /></dd>
			</dl>
			<hr />
			<label><input type="checkbox" name="smflinks_setshowtoprate"', ($modSettings['smflinks_setshowtoprate'] ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_setshowtoprate'], '</label><br />
			<label><input type="checkbox" name="smflinks_setshowmostvisited"', ($modSettings['smflinks_setshowmostvisited'] ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_setshowmostvisited'], '</label><br />
			<label><input type="checkbox" name="smflinks_set_count_child"', ($modSettings['smflinks_set_count_child'] ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_set_count_child'], '</label><br />
			<label><input type="checkbox" name="smflinks_setshowstats"', ($modSettings['smflinks_setshowstats'] ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_setshowstats'], '</label><br />
			<label><input type="checkbox" name="smflinks_setallowbbc"', ($modSettings['smflinks_setallowbbc'] ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_setallowbbc'], '</label><br />
			<hr />
			<strong>', $txt['smflinks_linkdisplay'], '</strong><br />
			<label><input type="checkbox" name="smflinks_disp_description"', ($modSettings['smflinks_disp_description'] ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_disp_description'], '</label><br />
			<label><input type="checkbox" name="smflinks_disp_hits"', ($modSettings['smflinks_disp_hits'] ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_disp_hits'], '</label><br />
			<label><input type="checkbox" name="smflinks_disp_rating"', ($modSettings['smflinks_disp_rating'] ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_disp_rating'], '</label><br />
			<label><input type="checkbox" name="smflinks_disp_membername"', ($modSettings['smflinks_disp_membername'] ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_disp_membername'], '</label><br />
			<label><input type="checkbox" name="smflinks_disp_date"', ($modSettings['smflinks_disp_date'] ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_disp_date'], '</label><br />
			<label><input type="checkbox" name="smflinks_disp_thumbnail"', (!empty($modSettings['smflinks_disp_thumbnail']) ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_disp_thumbnail'], '</label><br />
			<hr />
			<strong>', $txt['smflinks_linkchecksettings'], '</strong><br />
			<dl class="settings">
				<dt>', $txt['smflinks_check_batch_size'], '</dt>
				<dd><input type="text" name="smflinks_check_batch_size" value="', (!empty($modSettings['smflinks_check_batch_size']) ? $modSettings['smflinks_check_batch_size'] : '25'), '" size="5" /><br /><span class="smalltext">', $txt['smflinks_check_batch_size_hint'], '</span></dd>
			</dl>
			<label><input type="checkbox" name="smflinks_check_notify_pm"', (!empty($modSettings['smflinks_check_notify_pm']) ? ' checked="checked"' : ''), ' /> ', $txt['smflinks_check_notify_pm'], '</label><br />
			<br />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="submit" name="savesettings" value="', $txt['smflinks_settings_save'], '" class="button" />
		</form>
	</div>';

	LinksCopyright();
}

function template_manage_cats()
{
	global $scripturl, $txt, $context, $settings;

	echo '
	<style>
		.links-drag-over { background-color: #d4e6f1 !important; }
		tr[draggable="true"] { cursor: grab; }
		tr[draggable="true"]:active { cursor: grabbing; }
	</style>
	<div class="cat_bar">
		<h3 class="catbg">', $txt['smflinks_managecats'], '</h3>
	</div>
	<div class="windowbg">
		<p id="drag_hint" style="display:none; font-style:italic; margin-bottom:8px;">', $txt['smflinks_drag_reorder'], '</p>
		<span id="sort_status" style="font-weight:bold;"></span>
		<table class="table_grid" id="sortable_cats" style="width:100%;">
			<thead>
				<tr class="title_bar">
					<th class="lefttext">', $txt['smflinks_ctitle'], '</th>
					<th class="lefttext">', $txt['smflinks_description'], '</th>
					<th class="centertext">', $txt['smflinks_totallinks'], '</th>
					<th class="lefttext">', $txt['smflinks_options'], '</th>
				</tr>
			</thead>
			<tbody>';

	// Build a flat list with depth info for rendering
	template_render_sortable_cats(0, $context['links_cats'], 0);

	echo '
				<tr class="windowbg">
					<td colspan="4" class="centertext">
						<a href="', $scripturl, '?action=links;sa=addcat;a=admin">', $txt['smflinks_addcat'], '</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<script>
		var smflinks_order_saved = ', JavaScriptEscape($txt['smflinks_order_saved']), ';
		var smflinks_order_error = ', JavaScriptEscape($txt['smflinks_order_error']), ';
	</script>
	<script src="', $settings['default_theme_url'], '/scripts/LinksSort.js"></script>';

	LinksCopyright();
}

function template_render_sortable_cats($parent_id, $categories, $depth)
{
	global $scripturl, $txt, $context;

	foreach ($categories as $row)
	{
		if ($row['ID_PARENT'] == $parent_id)
		{
			$totallinks = GetLinkTotals($row['ID_CAT']);
			$indent = str_repeat('&mdash; ', $depth);

			echo '
				<tr class="windowbg" draggable="true" data-cat-id="', $row['ID_CAT'], '" data-parent="', $row['ID_PARENT'], '">
					<td>', $indent, '<a href="', $scripturl, '?action=links;cat=', $row['ID_CAT'], '">', parse_bbc($row['title']), '</a></td>
					<td>', parse_bbc($row['description']), '</td>
					<td class="centertext">', $totallinks, '</td>
					<td>
						<a href="', $scripturl, '?action=links;sa=catperm;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txt_perm'], '</a>
						<span class="links-nojsonly">
							<a href="', $scripturl, '?action=links;sa=catup;cat=', $row['ID_CAT'], ';a=admin;', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtup'], '</a>
							<a href="', $scripturl, '?action=links;sa=catdown;cat=', $row['ID_CAT'], ';a=admin;', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdown'], '</a>
						</span>
						<a href="', $scripturl, '?action=links;sa=editcat;cat=', $row['ID_CAT'], ';a=admin;', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtedit'], '</a>
						<a href="', $scripturl, '?action=links;sa=deletecat;cat=', $row['ID_CAT'], ';a=admin;', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a>
					</td>
				</tr>';

			template_render_sortable_cats($row['ID_CAT'], $categories, $depth + 1);
		}
	}
}

function template_checklinks()
{
	global $scripturl, $txt, $context;

	$notify_enabled = !empty($context['check_notify_pm']);
	$batch_size = !empty($context['check_batch_size']) ? (int) $context['check_batch_size'] : 25;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['smflinks_checklinks'], '</h3>
	</div>
	<div class="windowbg">
		<p>', $txt['smflinks_checklinks_desc'], '</p>
		<p><strong>', $txt['smflinks_totallinks'], ':</strong> <span id="links_total">', $context['links_total'], '</span></p>

		<div id="checklinks_progress" style="display:none; margin:10px 0;">
			<div style="background:#ddd; border-radius:4px; overflow:hidden; height:20px;">
				<div id="checklinks_bar" style="background:#5b9bd5; height:100%; width:0%; transition:width 0.3s;"></div>
			</div>
			<p id="checklinks_status" style="margin-top:4px;"></p>
		</div>

		<button id="btn_start_check" class="button" onclick="startLinkCheck();">', $txt['smflinks_start_check'], '</button>

		<form method="post" action="', $scripturl, '?action=links;sa=deletebadlinks" id="deleteform" style="display:none; margin-top:12px;">
			<table class="table_grid" style="width:100%;">
				<thead>
					<tr class="title_bar">
						<th style="width:30px;"><input type="checkbox" id="checkall" onclick="toggleAllChecks(this, \'delete\');" /></th>
						<th class="lefttext">', $txt['smflinks_ctitle'], '</th>
						<th class="lefttext">', $txt['smflinks_url'], '</th>
						<th class="lefttext">', $txt['smflinks_category'], '</th>
						<th class="centertext">', $txt['smflinks_status'], '</th>
						<th class="centertext">', $txt['smflinks_check_fails'], '</th>';

	if ($notify_enabled)
		echo '
						<th class="centertext">', $txt['smflinks_notify'], '</th>';

	echo '
					</tr>
				</thead>
				<tbody id="checklinks_results">
				</tbody>
			</table>
			<br />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="submit" value="', $txt['smflinks_delete_selected'], '" class="button" />
		</form>';

	if ($notify_enabled)
		echo '
		<form method="post" action="', $scripturl, '?action=links;sa=notifybadlinks" id="notifyform" style="display:none; margin-top:8px;">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<div id="notify_ids_container"></div>
			<input type="submit" value="', $txt['smflinks_notify_selected'], '" class="button" />
		</form>';

	echo '
		<p id="checklinks_complete" style="display:none; color:green; font-weight:bold; margin-top:10px;">', $txt['smflinks_check_complete'], '</p>
		<p id="checklinks_noerrors" style="display:none; color:green; font-weight:bold; margin-top:10px;">', $txt['smflinks_no_bad_links'], '</p>
	</div>

	<script>
	var linksTotal = ', $context['links_total'], ';
	var linksBatchSize = ', $batch_size, ';
	var linksChecked = 0;
	var hasBadLinks = false;
	var notifyEnabled = ', ($notify_enabled ? 'true' : 'false'), ';

	function startLinkCheck() {
		document.getElementById("btn_start_check").style.display = "none";
		document.getElementById("checklinks_progress").style.display = "block";
		linksChecked = 0;
		hasBadLinks = false;
		checkBatch(0);
	}

	function checkBatch(offset) {
		var xhr = new XMLHttpRequest();
		xhr.open("GET", "', $scripturl, '?action=links;sa=checklinks2;offset=" + offset + ";', $context['session_var'], '=', $context['session_id'], '", true);
		xhr.onreadystatechange = function() {
			if (xhr.readyState == 4 && xhr.status == 200) {
				var data = JSON.parse(xhr.responseText);
				if (data.results && data.results.length > 0) {
					linksChecked += data.results.length;
					var pct = Math.round((linksChecked / linksTotal) * 100);
					document.getElementById("checklinks_bar").style.width = pct + "%";
					document.getElementById("checklinks_status").textContent = "Checked " + linksChecked + " of " + linksTotal + " links";

					var tbody = document.getElementById("checklinks_results");
					for (var i = 0; i < data.results.length; i++) {
						var r = data.results[i];
						var isCF = r.cloudflare || false;
						var isBad = !isCF && (r.status_code == 0 || r.status_code >= 400);
						if (isBad) {
							hasBadLinks = true;
							document.getElementById("deleteform").style.display = "block";
							if (notifyEnabled)
								document.getElementById("notifyform").style.display = "block";
						}

						var color = "green";
						if (isCF) color = "#b8860b";
						else if (r.status_code == 301 || r.status_code == 302) color = "orange";
						else if (r.status_code >= 400 || r.status_code == 0) color = "red";

						var failsCell = \'<td class="centertext">\' + (r.check_fails || 0) + \'</td>\';

						var notifyCell = "";
						if (notifyEnabled)
							notifyCell = \'<td class="centertext">\' + (isBad && r.member_id > 0 ? \'<input type="checkbox" class="notify_check" data-id="\' + r.id + \'" checked />\' : \'\') + \'</td>\';

						var tr = document.createElement("tr");
						tr.className = "windowbg";
						tr.innerHTML = \'<td>\' + (isBad ? \'<input type="checkbox" name="delete_ids[]" value="\' + r.id + \'" checked />\' : \'\') + \'</td>\' +
							\'<td>\' + r.title + \'</td>\' +
							\'<td><a href="\' + r.url + \'" target="_blank">\' + r.url + \'</a></td>\' +
							\'<td>\' + (r.catname || \'\') + \'</td>\' +
							\'<td class="centertext" style="color:\' + color + \'; font-weight:bold;">\' + r.status_code + \' \' + r.status_text + \'</td>\' +
							failsCell + notifyCell;
						tbody.appendChild(tr);
					}

					// Check next batch
					checkBatch(offset + linksBatchSize);
				} else {
					// Done
					document.getElementById("checklinks_bar").style.width = "100%";
					document.getElementById("checklinks_complete").style.display = "block";
					if (!hasBadLinks) {
						document.getElementById("checklinks_noerrors").style.display = "block";
					}
				}
			}
		};
		xhr.send();
	}

	function toggleAllChecks(src, type) {
		var selector = type === "delete" ? \'#checklinks_results input[name="delete_ids[]"]\' : \'#checklinks_results input.notify_check\';
		var checks = document.querySelectorAll(selector);
		for (var i = 0; i < checks.length; i++) {
			checks[i].checked = src.checked;
		}
	}';

	if ($notify_enabled)
		echo '
	// Before notify form submit, copy checked notify IDs into hidden inputs
	document.getElementById("notifyform").addEventListener("submit", function(e) {
		var container = document.getElementById("notify_ids_container");
		container.innerHTML = "";
		var checks = document.querySelectorAll("#checklinks_results input.notify_check:checked");
		if (checks.length === 0) {
			e.preventDefault();
			return;
		}
		for (var i = 0; i < checks.length; i++) {
			var inp = document.createElement("input");
			inp.type = "hidden";
			inp.name = "notify_ids[]";
			inp.value = checks[i].getAttribute("data-id");
			container.appendChild(inp);
		}
	});';

	echo '
	</script>';

	LinksCopyright();
}

function template_disallowed_domains()
{
	global $scripturl, $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['smflinks_disallowed_domains'], '</h3>
	</div>
	<div class="windowbg">
		<p>', $txt['smflinks_disallowed_domains_desc'], '</p>
	</div>';

	// List current disallowed domains
	if (!empty($context['disallowed_domains']))
	{
		echo '
	<table class="table_grid" style="width:100%;">
		<thead>
			<tr class="title_bar">
				<th class="lefttext">', $txt['smflinks_domain'], '</th>
				<th style="width:100px;" class="centertext">', $txt['smflinks_options'], '</th>
			</tr>
		</thead>
		<tbody>';

		foreach ($context['disallowed_domains'] as $domain)
		{
			echo '
			<tr class="windowbg">
				<td>', $domain, '</td>
				<td class="centertext"><a href="', $scripturl, '?action=links;sa=disalloweddomainsdelete;domain=', urlencode($domain), ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a></td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';
	}
	else
	{
		echo '
	<div class="windowbg">
		<p>', $txt['smflinks_no_disallowed_domains'], '</p>
	</div>';
	}

	// Add domain form
	echo '
	<div class="cat_bar" style="margin-top:10px;">
		<h3 class="catbg">', $txt['smflinks_add_domain'], '</h3>
	</div>
	<div class="windowbg">
		<form action="', $scripturl, '?action=links;sa=disalloweddomains2" method="post">
			<dl class="settings">
				<dt><label for="domain">', $txt['smflinks_domain'], ':</label></dt>
				<dd>
					<input type="text" name="domain" id="domain" size="40" />
					<br /><span class="smalltext">', $txt['smflinks_domain_hint'], '</span>
				</dd>
			</dl>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="submit" value="', $txt['smflinks_add_domain'], '" class="button" />
		</form>
	</div>';

	LinksCopyright();
}

function template_catperm()
{
	global $scripturl, $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['smflinks_text_catperm'], ' - ', $context['links_cat_name'], '</h3>
	</div>
	<div class="windowbg">
		<form method="post" action="', $scripturl, '?action=links;sa=catperm2">
			<table class="table_grid" style="width:auto; margin:0 auto;">
				<thead>
					<tr class="title_bar">
						<th colspan="2">', $txt['smflinks_text_addperm'], '</th>
					</tr>
				</thead>
				<tbody>
					<tr class="windowbg">
						<td class="righttext"><strong>', $txt['smflinks_groupname'], '</strong></td>
						<td>
							<select name="group_name">
								<option value="-1">', $txt['membergroups_guests'], '</option>
								<option value="0">', $txt['membergroups_members'], '</option>';

	foreach ($context['groups'] as $group)
		echo '
								<option value="', $group['ID_GROUP'], '">', $group['group_name'], '</option>';

	echo '
							</select>
						</td>
					</tr>
					<tr class="windowbg">
						<td class="righttext"><input type="checkbox" name="view" checked="checked" /></td>
						<td><strong>', $txt['smflinks_perm_view'], '</strong></td>
					</tr>
					<tr class="windowbg">
						<td class="righttext"><input type="checkbox" name="add" checked="checked" /></td>
						<td><strong>', $txt['smflinks_perm_add'], '</strong></td>
					</tr>
					<tr class="windowbg">
						<td class="righttext"><input type="checkbox" name="edit" checked="checked" /></td>
						<td><strong>', $txt['smflinks_perm_edit'], '</strong></td>
					</tr>
					<tr class="windowbg">
						<td class="righttext"><input type="checkbox" name="delete" checked="checked" /></td>
						<td><strong>', $txt['smflinks_perm_delete'], '</strong></td>
					</tr>
					<tr class="windowbg">
						<td colspan="2" class="centertext">
							<input type="hidden" name="cat" value="', $context['links_cat'], '" />
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="submit" value="', $txt['smflinks_text_addperm'], '" class="button" />
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>

	<div class="windowbg">
		<table class="table_grid" style="width:100%;">
			<thead>
				<tr class="title_bar">
					<th class="lefttext">', $txt['smflinks_groupname'], '</th>
					<th class="lefttext">', $txt['smflinks_perm_view'], '</th>
					<th class="lefttext">', $txt['smflinks_perm_add'], '</th>
					<th class="lefttext">', $txt['smflinks_perm_edit'], '</th>
					<th class="lefttext">', $txt['smflinks_perm_delete'], '</th>
					<th class="lefttext">', $txt['smflinks_options'], '</th>
				</tr>
			</thead>
			<tbody>';

	// Show the member groups
	foreach ($context['links_mgroups'] as $row)
	{
		echo '
				<tr class="windowbg">
					<td>', $row['group_name'], '</td>
					<td>', ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td><a href="', $scripturl, '?action=links;sa=catpermdelete;id=', $row['ID'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a></td>
				</tr>';
	}

	// Show Regular members
	foreach ($context['links_reggroups'] as $row)
	{
		echo '
				<tr class="windowbg">
					<td>', $txt['membergroups_members'], '</td>
					<td>', ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td><a href="', $scripturl, '?action=links;sa=catpermdelete;id=', $row['ID'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a></td>
				</tr>';
	}

	// Show Guests
	foreach ($context['links_guests_groups'] as $row)
	{
		echo '
				<tr class="windowbg">
					<td>', $txt['membergroups_guests'], '</td>
					<td>', ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td><a href="', $scripturl, '?action=links;sa=catpermdelete;id=', $row['ID'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a></td>
				</tr>';
	}

	echo '
			</tbody>
		</table>
	</div>';
}

function template_catpermlist()
{
	global $scripturl, $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['smflinks_catpermlist'], '</h3>
	</div>
	<div class="windowbg">
		<table class="table_grid" style="width:100%;">
			<thead>
				<tr class="title_bar">
					<th class="lefttext">', $txt['smflinks_groupname'], '</th>
					<th class="lefttext">', $txt['smflinks_category'], '</th>
					<th class="lefttext">', $txt['smflinks_perm_view'], '</th>
					<th class="lefttext">', $txt['smflinks_perm_add'], '</th>
					<th class="lefttext">', $txt['smflinks_perm_edit'], '</th>
					<th class="lefttext">', $txt['smflinks_perm_delete'], '</th>
					<th class="lefttext">', $txt['smflinks_options'], '</th>
				</tr>
			</thead>
			<tbody>';

	// Show the member groups
	foreach ($context['links_m_groups'] as $row)
	{
		echo '
				<tr class="windowbg">
					<td>', $row['group_name'], '</td>
					<td><a href="', $scripturl, '?action=links;sa=catperm;cat=', $row['ID_CAT'], '">', $row['catname'], '</a></td>
					<td>', ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td><a href="', $scripturl, '?action=links;sa=catpermdelete;id=', $row['ID'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a></td>
				</tr>';
	}

	// Show Regular members
	foreach ($context['links_reg_groups'] as $row)
	{
		echo '
				<tr class="windowbg">
					<td>', $txt['membergroups_members'], '</td>
					<td><a href="', $scripturl, '?action=links;sa=catperm;cat=', $row['ID_CAT'], '">', $row['catname'], '</a></td>
					<td>', ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td><a href="', $scripturl, '?action=links;sa=catpermdelete;id=', $row['ID'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a></td>
				</tr>';
	}

	// Show Guests
	foreach ($context['links_guests_groups'] as $row)
	{
		echo '
				<tr class="windowbg">
					<td>', $txt['membergroups_guests'], '</td>
					<td><a href="', $scripturl, '?action=links;sa=catperm;cat=', $row['ID_CAT'], '">', $row['catname'], '</a></td>
					<td>', ($row['view'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['addlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['editlink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td>', ($row['dellink'] ? $txt['smflinks_perm_allowed'] : $txt['smflinks_perm_denied']), '</td>
					<td><a href="', $scripturl, '?action=links;sa=catpermdelete;id=', $row['ID'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a></td>
				</tr>';
	}

	echo '
			</tbody>
		</table>
	</div>';
}

function LinksCopyright()
{
	// The Copyright is required to remain or contact me to purchase link removal.
	// https://www.smfhacks.com/copyright_removal.php
	echo '<br /><div class="centertext"><span class="smalltext">Powered by: <a href="https://www.smfhacks.com" target="_blank">SMF Links</a> by <a href="https://www.createaforum.com" title="Forum Hosting" target="_blank">CreateAForum.com</a></span></div>';
}

function GetManageSubCats($ID_PARENT, $categories)
{
	global $cat_sep, $scripturl, $txt, $context;

	foreach ($categories as $row)
	{
		if ($row['ID_PARENT'] == $ID_PARENT)
		{
			$totallinks = GetLinkTotals($row['ID_CAT']);

			echo '
				<tr class="windowbg">
					<td>', str_repeat('&mdash;', $cat_sep), ' <a href="', $scripturl, '?action=links;cat=', $row['ID_CAT'], '">', parse_bbc($row['title']), '</a></td>
					<td>', parse_bbc($row['description']), '</td>
					<td class="centertext">', $totallinks, '</td>
					<td>
						<a href="', $scripturl, '?action=links;sa=catperm;cat=', $row['ID_CAT'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txt_perm'], '</a>
						<a href="', $scripturl, '?action=links;sa=catup;cat=', $row['ID_CAT'], ';a=admin;', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtup'], '</a>
						<a href="', $scripturl, '?action=links;sa=catdown;cat=', $row['ID_CAT'], ';a=admin;', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdown'], '</a>
						<a href="', $scripturl, '?action=links;sa=editcat;cat=', $row['ID_CAT'], ';a=admin;', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtedit'], '</a>
						<a href="', $scripturl, '?action=links;sa=deletecat;cat=', $row['ID_CAT'], ';a=admin;', $context['session_var'], '=', $context['session_id'], '">', $txt['smflinks_txtdel'], '</a>
					</td>
				</tr>';

			$cat_sep++;
			GetManageSubCats($row['ID_CAT'], $categories);
			$cat_sep--;
		}
	}
}
