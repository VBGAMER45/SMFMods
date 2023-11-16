<?php
/*
SMF Articles
Version 3.0
by:vbgamer45
http://www.smfhacks.com
*/
function template_articlesmain()
{
	global $scripturl, $txt, $context, $subcats_linktree, $modSettings;


	ShowArticlesBar($txt['smfarticles_indextitle']);


		// List all the categories
		echo '<table cellspacing="0" cellpadding="10" border="0" align="center" width="100%" class="table_grid">

		<thead>
		<tr class="title_bar">
				<th  class="lefttext first_th" colspan="2">', $txt['smfarticles_ctitle'], '</th>
				<th  class="centertext' . ($context['m_cats'] ? '' : ' last_th') . '" align="center">', $txt['smfarticles_totalarticles'], '</th>
				';

				if ($context['m_cats'])
					echo '
					<th  class="lefttext">',$txt['smfarticles_text_reorder'],'</th>
					<th  class="lefttext last_th">', $txt['smfarticles_options'],'</th>';
		echo '
			</tr>
			</thead>';

		foreach ($context['articles_cat'] as $row)
		{

			$totalarticles = GetArticleTotals($row['ID_CAT']);

			echo '<tr>';

				if ($row['imageurl'] == '')
					echo '<td class="windowbg" width="10%"></td><td  class="windowbg2"><b><a href="', $scripturl, '?action=articles;cat=', $row['ID_CAT'], '">',parse_bbc($row['title']), '</a></b> ' . (!empty($modSettings['smfarticles_showrss']) ? '<a href="' . $scripturl . '?action=articles;sa=rss;cat=' . $row['ID_CAT'] . '"><img src="' . $modSettings['articles_url'] . 'rss.png" alt="rss" /></a>' : '') . '<br />', parse_bbc($row['description']), '</td>';
				else
				{
					echo '
					<td class="windowbg"><a href="', $scripturl, '?action=articles;cat=', $row['ID_CAT'], '"><img src="', $row['imageurl'], '" border="0" alt="" /></a></td>
					<td class="windowbg2"><b><a href="', $scripturl, '?action=articles;cat=', $row['ID_CAT'], '">', parse_bbc($row['title']), '</a></b> ' . (!empty($modSettings['smfarticles_showrss']) ? '<a href="' . $scripturl . '?action=articles;sa=rss;cat=' . $row['ID_CAT'] . '"><img src="' . $modSettings['articles_url'] . 'rss.png" alt="rss" /></a>' : '') . '<br />', parse_bbc($row['description']), '</td>';
				}

			// Show total articles
			echo '<td class="windowbg" align="center">', $totalarticles, '</td>';

			// Show Edit Delete and Order category
			if ($context['m_cats'])
			{
				echo '<td class="windowbg2"><a href="', $scripturl, '?action=articles;sa=catup;cat=', $row['ID_CAT'], '">', $txt['smfarticles_txtup'], '</a>&nbsp;<a href="', $scripturl, '?action=articles;sa=catdown;cat=', $row['ID_CAT'], '">', $txt['smfarticles_txtdown'], '</a></span></td>
				<td class="windowbg"><a href="', $scripturl, '?action=articles;sa=editcat;cat=', $row['ID_CAT'], '">', $txt['smfarticles_txtedit'], '</a>&nbsp;<a href="', $scripturl, '?action=articles;sa=deletecat;cat=', $row['ID_CAT'], '">', $txt['smfarticles_txtdel'], '</a>
				<br />
				<a href="', $scripturl, '?action=articles;sa=catperm;cat=', $row['ID_CAT'], '">', $txt['smfarticles_txt_perm'], '</a>
				</td>';
			}


			echo '</tr>';

			// Show child Boards
			if ($subcats_linktree  != '')
				echo '<tr>
				<td colspan="',($context['m_cats'] == true ? '5' : '3'), '" class="windowbg3">
					<span class="smalltext">',($subcats_linktree != '' ? '<b>' . $txt['smfarticles_sub_cats'] . '</b>' . $subcats_linktree : ''),'</span>
				</td>
			</tr>';

		}

		echo '</table>';


		// See if they are allowed to add catagories
		if ($context['m_cats'])
		{
			echo '<br />
            <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smfarticles_articlespanel'], '
        </h3>
</div>

            <table border="0" cellspacing="1" cellpadding="5" class="bordercolor" style="margin-top: 1px;" align="center" width="100%">
			<tr class="windowbg2" >
				<td align="center">
				<a href="', $scripturl, '?action=articles;sa=addcat">', $txt['smfarticles_addcat'], '</a>&nbsp;-&nbsp;';

			if ($context['addarticle'])
				echo '<a href="', $scripturl, '?action=articles;sa=addarticle">', $txt['smfarticles_addarticle'], '</a>&nbsp;-&nbsp;';

			echo '<a href="', $scripturl, '?action=admin;area=articles;sa=admin">', $txt['smfarticles_articlessettings'], '</a>&nbsp;-&nbsp;
			<a href="', $scripturl, '?action=admin;area=articles;sa=adminperm">', $txt['edit_permissions'], '</a>
			<br />',
			$txt['smfarticles_thereare'], '<b>', $context['articlesapproval'], '</b>', $txt['smfarticles_waitingapproval'],' <a href="', $scripturl, '?action=admin;area=articles;sa=alist">', $txt['smfarticles_articlecheckapproval'], '</a>
			<br />',
			$txt['smfarticles_thereare'], '<b>', $context['commentsapproval'], '</b>', $txt['smfarticles_comwaitapproval'],' <a href="', $scripturl, '?action=admin;area=articles;sa=comlist">', $txt['smfarticles_comcheckapproval'], '</a>
			<br />

			</td>
			</tr>
			</table>
			';

		}


	ArticleSystemCopyright();
}

function template_articlelisting()
{
	global $txt, $scripturl, $context, $modSettings;



	ShowArticlesBar($context['articles_cat_title']);


	ShowSubCats($context['articles_cat_id'],$context['m_cats']);

			$spancount = 1;

			echo '<br /><table cellspacing="0" cellpadding="10" border="0" align="center" width="100%" class="table_grid">

		<thead>
		<tr class="title_bar">
						<th  class="lefttext first_th">
						<a href="' , $scripturl , '?action=articles;cat=' , $context['articles_cat_id'] , ';start=' , $context['start'] , ';sort=title;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_ctitle'] , '</a></th>';

						if (!empty($modSettings['smfarticles_disp_rating']))
						{
							echo '<th  class="lefttext"><a href="' , $scripturl , '?action=articles;cat=' , $context['articles_cat_id'] , ';start=' , $context['start'] , ';sort=rating;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_crating'] , '</a></th>';
							$spancount++;
						}

						if (!empty($modSettings['smfarticles_disp_totalcomment']))
						{
							echo '<th  class="lefttext"><a href="' , $scripturl , '?action=articles;cat=' , $context['articles_cat_id'] , ';start=' , $context['start'] , ';sort=comment;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_txt_comments'] , '</a></th>';
							$spancount++;
						}

						if (!empty($modSettings['smfarticles_disp_views']))
						{
							echo '<th  class="lefttext"><a href="' , $scripturl , '?action=articles;cat=' , $context['articles_cat_id'] , ';start=' , $context['start'] , ';sort=views;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_cviews'] , '</a></th>';
							$spancount++;
						}

						if (!empty($modSettings['smfarticles_disp_membername']))
						{
							echo '<th  class="lefttext"><a href="' , $scripturl , '?action=articles;cat=' , $context['articles_cat_id'] , ';start=' , $context['start'] , ';sort=username;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_cusername'] , '</a></th>';
							$spancount++;
						}

						if (!empty($modSettings['smfarticles_disp_date']))
						{
							echo '<th  class="lefttext"><a href="' , $scripturl , '?action=articles;cat=' , $context['articles_cat_id'] , ';start=' , $context['start'] , ';sort=date;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_cdate'] , '</a></th>';
							$spancount++;
						}

						if ($context['m_cats'])
						{
							echo '
							<th  class="lefttext last_th">' , $txt['smfarticles_options'] ,'</th>';
							$spancount++;
						}

						echo '
					</tr></thead>';
			$max_num_stars = 5;

			$styleclass = "windowbg";

			foreach($context['articles_listing'] as $row)
			{

				echo '<tr  class="' , $styleclass  , '">';

				echo '<td><a href="' , $scripturl , '?action=articles;sa=view;article=', $row['ID_ARTICLE'], '">', $row['title'], '</a></td>';

				if (!empty($modSettings['smfarticles_disp_rating']))
				{
					echo '<td>' , GetStarsByPrecent(($row['totalratings'] != 0) ? ($row['rating'] / ($row['totalratings']* $max_num_stars) * 100) : 0),'</td>';
				}


				if (!empty($modSettings['smfarticles_disp_totalcomment']))
					echo '<td>', $row['commenttotal'], '</td>';


				if (!empty($modSettings['smfarticles_disp_views']))
					echo '<td>', $row['views'], '</td>';




				// Check if it was a guest article
				if (!empty($modSettings['smfarticles_disp_membername']))
					if ($row['real_name'] != '')
						echo '<td><a href="', $scripturl, '?action=profile;u=', $row['ID_MEMBER'], '">', $row['real_name'], '</a></td>';
					else
						echo '<td>', $txt['smfarticles_txtguest'], '</td>';

				if (!empty($modSettings['smfarticles_disp_date']))
					echo '<td><span class="smalltext">', timeformat($row['date']), '</span></td>';

				if ($context['m_cats'])
				{
					echo '<td>
					<a href="' , $scripturl , '?action=articles;sa=editarticle&id=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_txtedit'] , '</a>&nbsp;
					<a href="' , $scripturl , '?action=articles;sa=deletearticle&id=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_txtdel'] , '</a>&nbsp;
					<a href="' , $scripturl , '?action=articles;sa=noapprove&id=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_txtunapprove'] , '</a>
					</td>';
				}

				echo '</tr>';


				// Alternate style class
				if ($styleclass == 'windowbg')
					$styleclass = 'windowbg2';
				else
					$styleclass = 'windowbg';
			}



			// Show the pages
				echo '

				<tr class="titlebg">
						<td align="left" colspan="', $spancount, '">
						',  $context['page_index'],
						'</td>
					</tr>

			</table>';


			// Show return to articles index link

   	echo '
                    <div class="tborder">
            <div class="roundframe centertext">';

			// See if they are allowed to add a subcategory
			if ($context['m_cats'])
				echo '<a href="' , $scripturl , '?action=articles;sa=addcat;cat=' , $context['articles_cat_id'] , '">' ,$txt['smfarticles_text_addsubcat'] , '</a>&nbsp;-&nbsp;';


			// See if they are allowed to add articles
			if ($context['addarticle'])
				echo '<a href="' , $scripturl , '?action=articles;sa=addarticle;cat=' , $context['articles_cat_id'] , '">' , $txt['smfarticles_addarticle'] , '</a>&nbsp;-&nbsp;';

			echo '
			<a href="', $scripturl, '?action=articles">', $txt['smfarticles_returnindex'], '</a>
            </div>
        </div>';


	ArticleSystemCopyright();

}

function template_viewarticle()
{
	global $txt, $context, $scripturl, $modSettings, $settings, $memberContext, $user_info;

	$m_cats = $context['m_cats'];


	ShowArticlesBar();


	// Show the main article

if ($m_cats == true || $context['article']['ID_MEMBER'] == $user_info['id'])
	template_button_strip($context['articles']['view_article'], 'bottom');


	echo '
    <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $context['article']['title'], '
        </h3>
</div>

    <table cellspacing="0" cellpadding="10" border="0" align="center" width="100%" class="tborder">';

		// Show the title


		echo '<tr class="windowbg2">
				<td><span class="smalltext"><b>',$txt['smfarticles_submittedby2'],'</b>', '
				<a href="',$scripturl, '?action=profile;u=',$context['article']['ID_MEMBER'],'">', $context['article']['real_name'], '</a>
		 <b>',$txt['smfarticles_date2'],'</b> ',

		 timeformat($context['article']['date']), '

		 <b>',$txt['smfarticles_views2'],'</b> ',

		 $context['article']['views'], '

		 </span>
		 </td>
			</tr>';


		// Show Summary
		if (!empty( $context['article']['description']))
		echo '<tr class="windowbg2">
				<td><b>',$txt['smfarticles_summary2'],'</b>',
		 $context['article']['description'], '</td>
			</tr>';

		 // Show article
		echo '<tr class="windowbg2">
				<td><hr />
				',parse_bbc($context['article_page']['pagetext']),'
				</td>
			</tr>';


	  if ($modSettings['smfarticles_images_view_article'] == 1)
   {

   				foreach($context['articles_images'] as $row)
   				{
   					echo '<tr class="windowbg">
   						<td>
   							<img src="' .$modSettings['articles_url'] . $row['thumbnail']  . '" alt="" />

   						</td>

   					</tr>';

   				}



   }


	// Show Ratings
	$ratearticle = allowedTo('rate_articles');
	if ($modSettings['smfarticles_enableratings'] == 1)
	{
		echo '<tr class="windowbg2">
				<td><hr />';


					$max_num_stars = 5;

					if ( $context['article']['totalratings'] == 0)
					{
						// Display message that no ratings are in yet
						echo $txt['smfarticles_form_rating'], $txt['smfarticles_form_norating'];
					}
					else
					{
						// Compute the rating in %
						$rating =( $context['article']['rating'] / ( $context['article']['totalratings']* $max_num_stars) * 100);

						echo $txt['smfarticles_form_rating'] , GetStarsByPrecent($rating)  , ' ' , $txt['smfarticles_form_ratingby'] , $context['article']['totalratings'] , $txt['smfarticles_form_ratingmembers'], '<br />';
					}

					if ($ratearticle == true)
					{
						echo '<form method="post" action="', $scripturl , '?action=articles;sa=rate">';
							for($i = 1; $i <= $max_num_stars;$i++)
								echo '<input type="radio" name="rating" value="', $i ,'" />' , str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', $i);


					echo '
							 <input type="hidden" name="id" value="' ,  $context['article']['ID_ARTICLE'], '" />
							 <input type="submit" name="submit" value="' , $txt['smfarticles_form_ratearticle'], '" />
						';

						// Admin let them see who voted for what and option to delete rating
						if (allowedTo('articles_admin'))
							echo '&nbsp;<a href="', $scripturl, '?action=articles;sa=viewrating&id=',  $context['article']['ID_ARTICLE'], '">', $txt['smfarticles_form_viewratings'], '</a>';
						echo '</form><br />';
					}

		echo '
				</td>
			</tr>';
	}

	if ($modSettings['smfarticles_sharingicons'])
	{
		echo '<tr class="windowbg2">
				<td><table>
				<tr>

					<td><a href="https://twitter.com/share" class="twitter-share-button" data-count="horizontal">Tweet</a><script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script></td>
					<td>							<iframe src="https://www.facebook.com/plugins/like.php?href=' , urlencode($scripturl . '?action=articles;sa=view;article=' . $context['article']['ID_ARTICLE']), '&amp;layout=button_count&amp;show_faces=true&amp;width=450&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:21px;" allowTransparency="true"></iframe></td>
					</tr>
				</table>
				</td>
				</tr>';
	}




	echo '</table>';


	// Show Comments
	if ($modSettings['smfarticles_enablecomments'] == 1)
	{
		// Show comments
		echo '
        <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smfarticles_text_comments'], '
        </h3>
</div>


        <table cellspacing="0" cellpadding="10" border="0" align="center" width="100%" class="tborder">
			';

		if (allowedTo('articles_comment'))
		{
			// Show Add Comment
			echo '
				<tr class="titlebg"><td colspan="2">
				<a href="', $scripturl , '?action=articles;sa=comment&id=', $context['article']['ID_ARTICLE'] , '">' , $txt['smfarticles_text_addcomment']  , '</a></td>
				</tr>';
		}

		$context['allow_hide_email'] = !empty($modSettings['allow_hideEmail']) || ($user_info['is_guest'] && !empty($modSettings['guest_hideContacts']));

		$common_permissions = array(
						'can_send_pm' => 'pm_send',
			);

		foreach ($common_permissions as $contextual => $perm)
			$context[$contextual] = allowedTo($perm);



		foreach($context['article_comments'] as $row)
		{
			echo '<tr class="windowbg2">';
			// Display member info
			echo '<td width="10%" valign="top"><a name="c', $row['ID_COMMENT'] , '"></a>';

			if ($row['real_name'] == '')
				echo $txt['smfarticles_guest'], '<br />';


			// Display the users avatar
            $memCommID = $row['ID_MEMBER'];
            if ($row['real_name'])
            {
	            $memCommID = $row['ID_MEMBER'];
	            loadMemberData($memCommID);
				loadMemberContext($memCommID);

				//echo $memberContext[$memCommID]['avatar']['image'];

				ShowUserBox($memCommID);

            }


			echo '
			</td>
			<td width="90%" valign="top"><span class="smalltext">', timeformat($row['date']) , '</span><hr />';

			echo  parse_bbc($row['comment']);

			// Check if the user is allowed to delete the comment,
			if ($m_cats)
				echo '<br /><a href="' , $scripturl , '?action=articles;sa=delcomment&id=' , $row['ID_COMMENT'] , '">' , $txt['smfarticles_text_delcomment'] ,'</a>';


			echo '</td></tr>';
		}



		if (allowedTo('articles_comment') && $context['article_comment_count']!= 0)
		{
		// Show Add Comment
			echo '
				<tr class="titlebg"><td colspan="2">
				<a href="', $scripturl , '?action=articles;sa=comment&id=', $context['article']['ID_ARTICLE'], '">', $txt['smfarticles_text_addcomment'], '</a></td>
				</tr>';
		}

		echo '</table><br />';
	}




	ArticleSystemCopyright();
}


function template_addcat()
{
	global $scripturl, $txt, $context;

	echo '

<form name="catform" id="catform" method="post" action="' , $scripturl , '?action=articles;sa=addcat2" onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smfarticles_addcat'], '
        </h3>
</div>
<table cellpadding="0" cellspacing="0" class="tborder" width="100%">
  <tr class="windowbg2">
    <td width="28%" align="right"><b>' , $txt['smfarticles_ctitle'] , '</b></td>
    <td width="72%"><input type="text" name="title" size="64" maxlength="100" /></td>
  </tr>
  <tr class="windowbg2">
    <td width="28%" align="right"><b>' , $txt['smfarticles_parentcategory'] ,'</b>&nbsp;</td>
    <td width="72%"><select name="parent">
    <option value="0">' , $txt['smfarticles_text_catnone'] , '</option>
    ';

	foreach ($context['articles_cat'] as $i => $category)
	{
		echo '<option value="' , $category['ID_CAT']  , '" ' , (($context['articles_parent']  == $category['ID_CAT']) ? ' selected="selected"' : '') ,'>' , $category['title'] , '</option>';
	}

	echo '</select>
	</td>
  </tr>
  <tr class="windowbg2">
    <td width="28%"  valign="top" align="right"><b>' , $txt['smfarticles_description'] , '</b></td>
    <td width="72%"><table>
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
  <tr class="windowbg2">
    <td width="28%" align="right"><b>' , $txt['smfarticles_image'] , '</b>&nbsp;</td>
    <td width="72%"><input type="text" name="image" size="64" maxlength="100" /></td>
  </tr>
  <tr class="windowbg2">
    <td width="28%" colspan="2" align="center">
    <input type="submit" value="' , $txt['smfarticles_addcat'] , '" name="submit" /></td>

  </tr>
</table>
</form>
';

	ArticleSystemCopyright();
}

function template_editcat()
{
	global $scripturl, $txt, $context;

echo '<div class="tborder" >
<form method="post" action="' , $scripturl , '?action=articles;sa=editcat2" onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smfarticles_editcat'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" class="tborder" width="100%">
  <tr class="windowbg2">
    <td width="28%" align="right"><b>' , $txt['smfarticles_ctitle'] , '</b></td>
    <td width="72%"><input type="text" name="title" size="64" maxlength="100" value="' , $context['articles_data']['title'] , '" /></td>
  </tr>
 <tr class="windowbg2">
    <td width="28%"  align="right"><b>' , $txt['smfarticles_parentcategory'] ,'</b>&nbsp;</td>
    <td width="72%"><select name="parent">
    <option value="0">' , $txt['smfarticles_text_catnone'] , '</option>
    ';

	foreach ($context['articles_cat'] as $i => $category)
		echo '<option value="' , $category['ID_CAT']  , '" ' , (($context['articles_data']['ID_PARENT'] == $category['ID_CAT']) ? ' selected="selected"' : '') ,'>' , $category['title'] , '</option>';

	echo '</select>
	</td>
  </tr>
  <tr class="windowbg2">
    <td width="28%"  valign="top" align="right"><b>' , $txt['smfarticles_description'] , '</b></td>
    <td width="72%"><table>
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
					</table>
					</td>
  </tr>
  <tr  class="windowbg2">
    <td width="28%" align="right"><b>' , $txt['smfarticles_image'] , '</b>&nbsp;</td>
    <td width="72%"><input type="text" name="image" size="64" maxlength="100" value="' , $context['articles_data']['imageurl'] , '" /></td>
  </tr>
  <tr class="windowbg2">
    <td width="28%" colspan="2"  align="center">
    <input type="hidden" value="' , $context['articles_data']['ID_CAT'] , '" name="catid" />
    <input type="submit" value="' , $txt['smfarticles_editcat'] , '" name="submit" /></td>

  </tr>
</table>
</form>
</div>';

	ArticleSystemCopyright();

}

function template_deletecat()
{
	global $context, $scripturl, $txt;

	echo '
	<form method="post" action="' , $scripturl , '?action=articles;sa=deletecat2">
    <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smfarticles_deltcat'], '
        </h3>
</div>
<table  cellpadding="0" cellspacing="0" class="tborder" width="100%">
  <tr class="windowbg2">
    <td width="28%" colspan="2" align="center">
    <b>' , $txt['smfarticles_warndel2'] , '</b>
    <br />
    <input type="hidden" value="' , $context['arcticle_cat'], '" name="catid" />
    <input type="submit" value="' , $txt['smfarticles_deltcat'] , '" name="submit" /></td>
  </tr>
</table>
</form>';

	ArticleSystemCopyright();

}

function template_addarticle()
{
	global $scripturl, $txt, $modSettings, $context, $settings;

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>';


    ShowArticlesBar();

	echo '
<form method="post" enctype="multipart/form-data" action="' , $scripturl , '?action=articles;sa=addarticle2" name="addarticle" id="addarticle" onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smfarticles_addarticle'], '
        </h3>
</div>
<table  cellpadding="0" cellspacing="0" class="tborder" width="100%">
  <tr class="windowbg2">
    <td width="28%" align="right"><b>' , $txt['smfarticles_ctitle'] , '</b></td>
    <td width="72%"><input type="text" name="title" size="64" maxlength="100" /></td>
  </tr>
  <tr class="windowbg2">
    <td width="28%" align="right"><b>' , $txt['smfarticles_category'] , '</b></td>
    <td width="72%"><select name="catid">';
		foreach ($context['articles_cat'] as $row)
  			echo '<option value="' , $row['ID_CAT'] , '" ' , (($row['ID_CAT'] == $context['articles_catid']) ? 'selected="selected" ' : '') ,' >' , $row['title'] , '</option>';

echo '</select>
    </td>
  </tr>
  <tr class="windowbg2">
    <td width="28%" valign="top" align="right"><b>' , $txt['smfarticles_summary'] , '</b></td>
    <td width="72%"><textarea rows="5" name="description" cols="60"></textarea>
    </td>
  </tr>
     <tr class="windowbg2">
   <td colspan="2" align="center">
   <hr />
   <b>',$txt['smfarticles_articletext'],'</b>
   </td>
   </tr>

   <tr class="windowbg2">
   <td colspan="2" align="center">
   <table>
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
								<tr class="windowbg2" colspan="2">
		<td>';
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
			</table>
   </td>
   </tr>
';

   if ($modSettings['smfarticles_allow_attached_images'] > 0)
   {
   		echo '<tr class="windowbg2">
   			<td valign="top" align="right"><b>', $txt['smfarticles_txt_upload_image'], '</b></td>
   			<td><input type="file" size="75" name="uploadimage" /><br /><br /></td>
   		</tr>
   		';
   }

   echo '
  <tr class="windowbg2">
    <td width="28%" colspan="2" align="center">';

  	if ($context['show_spellchecking'])
		echo '
									<input type="button" value="', $txt['spell_check'], '" tabindex="', $context['tabindex']++, '" onclick="spellCheck(\'addarticle\', \'message\');" />';


   echo '
    <input type="submit" value="', $txt['smfarticles_addarticle'], '" name="submit" /></td>

  </tr>
</table>
</form>';

   	// Some hidden information is needed in order to make the spell checking work.
	if ($context['show_spellchecking'])
		echo '
		<form name="spell_form" id="spell_form" method="post" accept-charset="', $context['character_set'], '" target="spellWindow" action="', $scripturl, '?action=spellcheck"><input type="hidden" name="spellstring" value="" /></form>';



	ArticleSystemCopyright();
}

function template_editarticle()
{
	global  $scripturl, $txt, $settings, $context, $modSettings;


	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>';

        ShowArticlesBar();

	echo '<div class="tborder">
<form method="post" enctype="multipart/form-data" action="' , $scripturl , '?action=articles;sa=editarticle2" name="editarticle" id="editarticle" onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smfarticles_editarticle'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" class="tborder"  width="100%">
  <tr class="windowbg2">
    <td width="28%" align="right"><b>' , $txt['smfarticles_ctitle'] , '</b></td>
    <td width="72%"><input type="text" name="title" size="64" maxlength="100" value="' , $context['article_data']['title'] , '" /></td>
  </tr>
  <tr class="windowbg2">
    <td width="28%" align="right"><b>' , $txt['smfarticles_category'] , '</b></td>
    <td width="72%"><select name="catid">';

		foreach($context['articles_cat'] as $row2)
  			echo '<option value="' , $row2['ID_CAT'] , '" ' , (($row2['ID_CAT'] == $context['article_data']['ID_CAT']) ? 'selected="selected" ' : '') ,'>' , $row2['title'] , '</option>';


echo '</select>
    </td>
  </tr>

  <tr class="windowbg2">
    <td width="28%" valign="top" align="right"><b>' , $txt['smfarticles_summary'] , '</b></td>
    <td width="72%"><textarea rows="6" name="description" cols="54">' , $context['article_data']['description'] , '</textarea></td>
  </tr>
    <tr  class="windowbg2">
   <td colspan="2" align="center">
   <hr />
   <b>',$txt['smfarticles_articletext'],'</b>
   </td>
   </tr>

   <tr class="windowbg2">
   <td colspan="2" align="center">
   <table>
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
					</table>
   </td>
   </tr>
';

   if ($modSettings['smfarticles_allow_attached_images'] > 0)
   {
   		echo '<tr class="windowbg2">
   			<td align="right" valign="top"><b>', $txt['smfarticles_txt_upload_image'], '</b></td>
   			<td><input type="file" size="75" name="uploadimage" /><br /><br /></td>
   		</tr>
   		';

   		echo '<tr class="windowbg2">
   			<td colspan="2" align="center">
   				<table align="center">';
   				$javaMessage = '';
   				foreach($context['articles_images'] as $row)
   				{
   					echo '<tr>
   						<td>
   							<a href="javascript:void(0);" onclick="return false;"><img id="cmd_image' . $row['ID_FILE'] . '" src="' .$modSettings['articles_url'] . $row['thumbnail']  . '" alt="" /></a>

   						</td>
   						<td>
   							', round($row['filesize'] / 1024, 2) . 'kb
   						</td>
   						<td>
   							<a href="' . $scripturl . '?action=articles;sa=delimage;id=' . $row['ID_FILE'] . '">' . $txt['smfarticles_txtdel'] . '</a>
   						</td>
   					</tr>';

   					$javaMessage .= "editorHandlemessage.addButton('cmd_image" . $row['ID_FILE'] . "', '[img]" .$modSettings['articles_url'] . $row['filename']  . "[/img]', '');" . "\n";

   				}

   				echo '</table>
   				<script language="JavaScript" type="text/javascript">
   				' .$javaMessage . '

   				smf_editorArray[smf_editorArray.length] = editorHandlemessage;
   				</script>
   			</td>
   			</tr>';

   }

   echo '

  <tr class="windowbg2">
    <td width="28%" colspan="2" align="center" >';

  	if ($context['show_spellchecking'])
		echo '
								<input type="button" value="', $txt['spell_check'], '" tabindex="', $context['tabindex']++, '" onclick="spellCheck(\'editarticle\', \'message\');" />';


echo '
    <input type="hidden" value="' , $context['article_id'] , '" name="id" />
    <input type="submit" value="' , $txt['smfarticles_editarticle'] , '" name="submit" /></td>

  </tr>
</table>
</form></div>';


   	// Some hidden information is needed in order to make the spell checking work.
	if ($context['show_spellchecking'])
		echo '
		<form name="spell_form" id="spell_form" method="post" accept-charset="', $context['character_set'], '" target="spellWindow" action="', $scripturl, '?action=spellcheck"><input type="hidden" name="spellstring" value="" /></form>';


	ArticleSystemCopyright();

}

function template_deletearticle()
{
	global $scripturl, $txt, $context;

	echo '<div class="tborder" ><form method="post" action="' , $scripturl , '?action=articles;sa=deletearticle2">
 <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smfarticles_delarticle'], '
        </h3>
</div>
<table cellpadding="0" cellspacing="0" class="tborder" width="100%">
  <tr class="windowbg2">
    <td width="28%" colspan="2"  align="center">"',$context['article_title'],'"<br />
    <b>' , $txt['smfarticles_warndel'] , '</b>
    <br />
    <input type="hidden" value="' , $context['article_id'] , '" name="id" />
    <input type="submit" value="' , $txt['smfarticles_delarticle'] , '" name="submit" /></td>
  </tr>
</table>
</form></div>';


	ArticleSystemCopyright();
}

function template_approvearticles()
{
	global $settings, $scripturl, $txt, $context;

	// Edit and Delete permissions
	$editarticle = $context['editarticle'];
	$deletearticle = $context['deletearticle'];


echo '
<div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smfarticles_approvearticles'], '
        </h3>
</div>
<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg2">
			<td>
<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">

		<thead>
		<tr class="title_bar">
    <th  class="lefttext first_th">', $txt['smfarticles_ctitle'], '</th>
    <th  class="lefttext">', $txt['smfarticles_category'], '</th>
    <th  class="lefttext">', $txt['smfarticles_submittedby'], '</th>
    <th  class="lefttext last_th">', $txt['smfarticles_options'], '</th>
  </tr>
  </thead>';

foreach($context['articles_list'] as $row)
{
  echo '<tr class="windowbg2">
  <td>' , $row['title'] , '</td>
  <td>' , $row['catname'] , '</td>';

  if ($row['real_name'] == '')
  	 echo '<td>' , $txt['smfarticles_txtguest'] , '</td>';
  else
  	echo '<td><a href="' , $scripturl , '?action=profile;u=' , $row['ID_MEMBER'] , '">' , $row['real_name'] , '</a></td>';


  echo '<td>
  <a href="' , $scripturl , '?action=articles;sa=approve&id=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_txtapprove'] , '</a>&nbsp;';

  if ($editarticle)
		echo '<a href="' , $scripturl , '?action=articles;sa=editarticle&id=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_txtedit'] , '</a>&nbsp;';
  if ($deletearticle)
		echo '<a href="' , $scripturl , '?action=articles;sa=deletearticle&id=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_txtdel'] ,'</a>';

  echo '</td>
  </tr>';

 }

 	// Show the pages
	echo '<tr class="titlebg">
				<td align="left" colspan="4">'
,$context['page_index'],

			'</td>
		</tr>
	</table>
	</td>
	</tr>
	</table>';

	ArticleSystemCopyright();
}

function template_settings()
{
	global $scripturl, $txt, $modSettings, $currentVersion;


echo '
<div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smfarticles_articlesconfig'], '
        </h3>
</div>
<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg2">
			<td>
			',$txt['smfarticles_txt_yourversion'] , $currentVersion, '&nbsp;',$txt['smfarticles_txt_latestversion'],'<span id="lastarticles" name="lastarticles"></span>
			<br />

			<b>' , $txt['smfarticles_articlesconfig'] , '</b><br />
			<form method="post" action="' , $scripturl , '?action=articles;sa=admin2">
			' , $txt['smfarticles_setarticlessperpage'] , '&nbsp;<input type="text" name="smfarticles_setarticlesperpage" value="' ,  $modSettings['smfarticles_setarticlesperpage'] , '" /><br />
			<input type="checkbox" name="smfarticles_countsubcats" ' , ($modSettings['smfarticles_countsubcats'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_countsubcats'] , '<br />
			<input type="checkbox" name="smfarticles_enableratings" ' , ($modSettings['smfarticles_enableratings'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_enableratings'] , '<br />
			<input type="checkbox" name="smfarticles_enablecomments" ' , ($modSettings['smfarticles_enablecomments'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_enablecomments'] , '<br />
			<input type="checkbox" name="smfarticles_sharingicons" ' , ($modSettings['smfarticles_sharingicons'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_sharingicons'] , '<br />
			<input type="checkbox" name="smfarticles_showrss" ' , ($modSettings['smfarticles_showrss'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_showrss'] , '<br />

			<br />
				<b>' , $txt['smfarticles_listingdisplay'] , '</b><br />

				<input type="checkbox" name="smfarticles_disp_views" ' , ($modSettings['smfarticles_disp_views'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_disp_views'] , '<br />
				<input type="checkbox" name="smfarticles_disp_rating" ' , ($modSettings['smfarticles_disp_rating'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_disp_rating'] , '<br />
				<input type="checkbox" name="smfarticles_disp_membername" ' , ($modSettings['smfarticles_disp_membername'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_disp_membername'] , '<br />
				<input type="checkbox" name="smfarticles_disp_date" ' , ($modSettings['smfarticles_disp_date'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_disp_date'] , '<br />
				<input type="checkbox" name="smfarticles_disp_totalcomment" ' , ($modSettings['smfarticles_disp_totalcomment'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_disp_totalcomment'] , '<br />
				<br />
				<b>' , $txt['smfarticles_txt_image_settings'] , '</b><br />
				<input type="checkbox" name="smfarticles_allow_attached_images" ' , ($modSettings['smfarticles_allow_attached_images'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_allow_attached_images'] , '<br />
				<input type="checkbox" name="smfarticles_images_view_article" ' , ($modSettings['smfarticles_images_view_article'] ? ' checked="checked" ' : '') , ' />' , $txt['smfarticles_images_view_article'] , '<br />

				' , $txt['articles_url'] , '&nbsp;<input type="text" name="articles_url" size="50" value="' ,  $modSettings['articles_url'] , '" /><br />
				' , $txt['articles_path'] , '&nbsp;<input type="text" name="articles_path" size="50" value="' ,  $modSettings['articles_path'] , '" /><br />

				' , $txt['smfarticles_max_num_attached'] , '&nbsp;<input type="text" name="smfarticles_max_num_attached" value="' ,  $modSettings['smfarticles_max_num_attached'] , '" /><br />
				' , $txt['smfarticles_max_filesize'] , '&nbsp;<input type="text" name="smfarticles_max_filesize" value="' ,  $modSettings['smfarticles_max_filesize'] , '" /><br />


				<br />
				<input type="submit" name="savesettings" value="' , $txt['smfarticles_settings_save'] ,'" />
			</form>
			<br />
			<br />
			<form method="post" action="' , $scripturl , '?action=articles;sa=recount">
			<input type="submit" value="',$txt['smfarticles_txt_recount_article_totals'],'" />
			</form>


<script language="JavaScript" type="text/javascript" src="https://www.smfhacks.com/versions/articles_version.js?t=' . time() .'"></script>
			<script language="JavaScript" type="text/javascript">

			function ArticlesCurrentVersion()
			{
				if (!window.articlesVersion)
					return;

				articlesspan = document.getElementById("lastarticles");

				if (window.articlesVersion != "', $currentVersion, '")
				{
					setInnerHTML(articlesspan, "<b><font color=\"red\">" + window.articlesVersion + "</font>&nbsp;', $txt['smfarticles_txt_version_outofdate'], '</b>");
				}
				else
				{
					setInnerHTML(articlesspan, "', $currentVersion, '")
				}
			}

			document.addEventListener(\'DOMContentLoaded\', function(event) {
			ArticlesCurrentVersion();
			});

			</script>

			</td>
		</tr>

		<tr>
		<td class="windowbg2">
		<b>Has SMF Articles helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="sales@visualbasiczone.com" />
	<input type="hidden" name="item_name" value="SMF Articles" />
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

	ArticleSystemCopyright();
}

function template_catperm()
{
	global $scripturl, $txt, $context;

	echo '
    <div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smfarticles_text_catperm'] , ' - ' , $context['articles_cat_name'], '
        </h3>
</div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg2">
		<td>
		<form method="post" action="' , $scripturl , '?action=articles;sa=catperm2">
		<table align="center" class="tborder">
		<tr class="titlebg">
			<td colspan="2">'  , $txt['smfarticles_text_addperm'] , '</td>
		</tr>

			  <tr class="windowbg2">
			  	<td align="right"><b>' , $txt['smfarticles_groupname'] , '</b>&nbsp;</td>
			  	<td><select name="groupname">
			  					<option value="-1">' , $txt['membergroups_guests'] , '</option>
								<option value="0">' , $txt['membergroups_members'] , '</option>';
								foreach ($context['groups'] as $group)
									echo '<option value="', $group['ID_GROUP'], '">', $group['group_name'], '</option>';

							echo '</select>
				</td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="view" checked="checked" /></td>
			  	<td><b>' , $txt['smfarticles_perm_view'] ,'</b></td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="add" checked="checked" /></td>
			  	<td><b>' , $txt['smfarticles_perm_add'] ,'</b></td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="edit" checked="checked" /></td>
			  	<td><b>' , $txt['smfarticles_perm_edit'] ,'</b></td>
			  </tr>
			  <tr class="windowbg2">
			  	<td align="right"><input type="checkbox" name="delete" checked="checked" /></td>
			  	<td><b>' , $txt['smfarticles_perm_delete'] ,'</b></td>
			  </tr>

			  <tr class="windowbg2">
			  	<td align="center" colspan="2">
			  	<input type="hidden" name="cat" value="' , $context['articles_cat'] , '" />
			  	<input type="submit" value="' , $txt['smfarticles_text_addperm'] , '" /></td>

			  </tr>
		</table>
		</form>
		</td>
		</tr>
			<tr class="windowbg2">
			<td>
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">

		<thead>
		<tr class="catbg">
				<th  class="lefttext first_th">' , $txt['smfarticles_groupname'] , '</th>
				<th  class="lefttext">' ,  $txt['smfarticles_perm_view']  , '</th>
				<th  class="lefttext">' ,  $txt['smfarticles_perm_add']  , '</th>
				<th  class="lefttext">' ,  $txt['smfarticles_perm_edit']  , '</th>
				<th  class="lefttext">' ,  $txt['smfarticles_perm_delete']  , '</th>
				<th  class="lefttext last_th">' ,  $txt['smfarticles_options']  , '</th>
				</tr>
				</thead>';

		// Show the member groups
        $styleclass = 'windowbg';
			foreach($context['articles_membergroup'] as $row)
			{

				echo '<tr class="' . $styleclass . '">
				<td>'  , $row['group_name'] , '</td>
				<td>' , ($row['view'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['addarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['editarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['delarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td><a href="' , $scripturl , '?action=articles;sa=catpermdelete&id=' , $row['ID'] , '">' , $txt['smfarticles_txtdel'] , '</a></td>
				</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';

			}

			// Show Regular members
			foreach($context['articles_reggroup'] as $row)
			{

				echo '<tr class="' . $styleclass . '">
				<td>'  , $txt['membergroups_members'] , '</td>
				<td>' , ($row['view'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['addarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['editarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['delarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td><a href="' , $scripturl , '?action=articles;sa=catpermdelete&id=' , $row['ID'] , '">' , $txt['smfarticles_txtdel'] , '</a></td>
				</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';
			}

			// Show Guests
			foreach($context['articles_guest'] as $row)
			{
				echo '<tr class="' . $styleclass . '">
				<td>'  , $txt['membergroups_guests'] , '</td>
				<td>' , ($row['view'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['addarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['editarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['delarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td><a href="' , $scripturl , '?action=articles;sa=catpermdelete&id=' , $row['ID'] , '">' , $txt['smfarticles_txtdel'] , '</a></td>
				</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';
			}


		echo '
				</table>
			</td>
		</tr>
</table>';

		ArticleSystemCopyright();
}

function template_catpermlist()
{
	global $scripturl, $txt, $context;

echo '
    <div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smfarticles_catpermlist'], '
        </h3>
</div>
<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg">
			<td>
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">

		<thead>
		<tr class="title_bar">
				<th  class="lefttext first_th">' , $txt['smfarticles_groupname'] , '</th>
				<th  class="lefttext">' , $txt['smfarticles_category']  , '</th>
				<th  class="lefttext">' ,  $txt['smfarticles_perm_view']  , '</th>
				<th  class="lefttext">' ,  $txt['smfarticles_perm_add']  , '</th>
				<th  class="lefttext">' ,  $txt['smfarticles_perm_edit']  , '</th>
				<th  class="lefttext">' ,  $txt['smfarticles_perm_delete']  , '</th>
				<th  class="lefttext last_th">' ,  $txt['smfarticles_options']  , '</th>
				</tr>
				</thead>';

			// Show the member groups
            $styleclass = 'windowbg';
			foreach($context['articles_mbgroups'] as $row)
			{

				echo '<tr class="' . $styleclass . '">
				<td>'  , $row['group_name'] , '</td>
				<td><a href="' , $scripturl , '?action=articles;sa=catperm;cat=' , $row['ID_CAT'] , '">'  , $row['catname'] , '</a></td>
				<td>' , ($row['view'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['addarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['editarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['delarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td><a href="' , $scripturl , '?action=articles;sa=catpermdelete&id=' , $row['ID'] , '">' , $txt['smfarticles_txtdel'] , '</a></td>
				</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';

			}

			// Show Regular members
		  	foreach($context['articles_regular'] as $row)
			{

				echo '<tr class="' . $styleclass . '">
				<td>'  , $txt['membergroups_members'] , '</td>
				<td><a href="' , $scripturl , '?action=articles;sa=catperm;cat=' , $row['ID_CAT'] , '">'  , $row['catname'] , '</a></td>
				<td>' , ($row['view'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['addarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['editarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['delarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td><a href="' , $scripturl , '?action=articles;sa=catpermdelete&id=' , $row['ID'] , '">' , $txt['smfarticles_txtdel'] , '</a></td>
				</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';
			}


			// Show Guests
			foreach($context['articles_guests'] as $row)
			{

				echo '<tr class="' . $styleclass . '">
				<td>'  , $txt['membergroups_guests'] , '</td>
				<td><a href="' , $scripturl , '?action=articles;sa=catperm;cat=' , $row['ID_CAT'] , '">'  , $row['catname'] , '</a></td>
				<td>' , ($row['view'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['addarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['editarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td>' , ($row['delarticle'] ? $txt['smfarticles_perm_allowed'] : $txt['smfarticles_perm_denied']) , '</td>
				<td><a href="' , $scripturl , '?action=articles;sa=catpermdelete&id=' , $row['ID'] , '">' , $txt['smfarticles_txtdel'] , '</a></td>
				</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';
			}


		echo '


				</table>
			</td>
		</tr>

</table>';

		ArticleSystemCopyright();
}

function GetStarsByPrecent($percent)
{
	global $settings, $txt, $context;

    if ($context['articles21beta'] == false)
    {
    	if ($percent == 0)
    		return $txt['smfarticles_text_catnone'];
    	else if ($percent <= 20)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 1);
    	else if ($percent <= 40)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 2);
    	else if ($percent <= 60)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 3);
    	else if ($percent <= 80)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 4);
    	else if ($percent <= 100)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 5);

       }
    else
    {
        if ($percent == 0)
    		return $txt['smfarticles_text_catnone'];
    	else if ($percent <= 20)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 1);
    	else if ($percent <= 40)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 2);
    	else if ($percent <= 60)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 3);
    	else if ($percent <= 80)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 4);
    	else if ($percent <= 100)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 5);
    }



}

function ArticleSystemCopyright()
{


	// Do NOT CHANGE THIS CODE UNLESS you have COPYRIGHT Link Removal
	// http://www.smfhacks.com/copyright_removal.php

	//Copyright link must remain. To remove you need to purchase link removal at smfhacks.com
    $showInfo = ArticlesCheckInfo();

    if ($showInfo == true)
	   echo '<br /><div align="center"><span class="smalltext">Powered By <a href="https://www.smfhacks.com" target="blank">SMF Articles</a> by <a href="https://www.createaforum.com" title="Forum Hosting">CreateAForum.com</a></span></div>';

}

function template_myarticles()
{
	global $txt, $context, $scripturl, $modSettings, $scripturl;

	// Setup their permissions
	$addarticle = $context['addarticle'];
	$editarticle = $context['editarticle'];
	$deletearticle = $context['deletearticle'];


	ShowArticlesBar($txt['smfarticles_indextitle']);



			echo '<br /><table cellspacing="0" cellpadding="10" border="0" align="center" width="100%" class="table_grid">

		<thead>
		<tr class="title_bar">
						<th  class="lefttext first_th">
						<a href="' , $scripturl , '?action=articles;sa=myarticles;start=' , $context['start'] , ';sort=title;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_ctitle'] , '</a></th>';

						if (!empty($modSettings['smfarticles_disp_rating']))
							echo '<th  class="lefttext"><a href="' , $scripturl , '?action=articles;sa=myarticles;start=' , $context['start'] , ';sort=rating;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_crating'] , '</a></th>';

						if (!empty($modSettings['smfarticles_disp_totalcomment']))
							echo '<th  class="lefttext"><a href="' , $scripturl , '?action=articles;sa=myarticles;start=' , $context['start'] , ';sort=comment;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_txt_comments'] , '</a></th>';

						if (!empty($modSettings['smfarticles_disp_views']))
							echo '<th  class="lefttext"><a href="' , $scripturl , '?action=articles;sa=myarticles;start=' , $context['start'] , ';sort=views;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_cviews'] , '</a></th>';

						if (!empty($modSettings['smfarticles_disp_membername']))
							echo '<th  class="lefttext"><a href="' , $scripturl , '?action=articles;sa=myarticles;start=' , $context['start'] , ';sort=username;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_cusername'] , '</a></th>';

						if (!empty($modSettings['smfarticles_disp_date']))
							echo '<th  class="lefttext"><a href="' , $scripturl , '?action=articles;sa=myarticles;start=' , $context['start'] , ';sort=date;sorto=' , $context['articles_newsorto'] , '">' , $txt['smfarticles_cdate'] , '</a></th>';

						echo '
						<th  class="lefttext last_th">' , $txt['smfarticles_options'] ,'</th>
					</tr>
					</thead>';

			$max_num_stars = 5;

			$styleclass = "windowbg";

			foreach($context['articles_listing'] as $row)
			{

				echo '<tr  class="' , $styleclass  , '">
				<td><a href="' , $scripturl , '?action=articles;sa=view;article=', $row['ID_ARTICLE'], '">', $row['title'], '</a></td>';

				if (!empty($modSettings['smfarticles_disp_rating']))
					echo '<td>' , GetStarsByPrecent(($row['totalratings'] != 0) ? ($row['rating'] / ($row['totalratings']* $max_num_stars) * 100) : 0) ,'</td>';

				if (!empty($modSettings['smfarticles_disp_totalcomment']))
					echo '<td>', $row['commenttotal'], '</td>';

				if (!empty($modSettings['smfarticles_disp_views']))
					echo '<td>', $row['views'], '</td>';

				// Check if it was a guest article
				if (!empty($modSettings['smfarticles_disp_membername']))
					if ($row['real_name'] != '')
						echo '<td><a href="', $scripturl, '?action=profile;u=', $row['ID_MEMBER'], '">', $row['real_name'], '</a></td>';
					else
						echo '<td>', $txt['smfarticles_txtguest'], '</td>';

				if (!empty($modSettings['smfarticles_disp_date']))
					echo '<td>', timeformat($row['date']), '</td>';

				echo '<td>';

				if ($editarticle)
					echo '<a href="' , $scripturl , '?action=articles;sa=editarticle&id=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_txtedit'] , '</a>&nbsp;';
				if ($deletearticle)
					echo '<a href="' , $scripturl , '?action=articles;sa=deletearticle&id=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_txtdel'] , '</a>&nbsp;';

				echo '</td>
				</tr>';


				// Alternate style class

				if ($styleclass == 'windowbg')
					$styleclass = 'windowbg2';
				else
					$styleclass = 'windowbg';
			}




			echo '</table>';

			// Show the pages
				echo '
				<table cellspacing="0" cellpadding="10" border="0" align="center" width="100%" class="tborder">
				<tr class="titlebg">
						<td align="left">
						',$context['page_index'],'
						</td>
					</tr>';




			// Show return to articles index link
			echo '</table>';



   	echo '
   		<div class="tborder">
            <div class="roundframe centertext">';

				// See if they are allowed to add articles
			if ($addarticle)
			{
				echo '<a href="' , $scripturl , '?action=articles;sa=addarticle">' , $txt['smfarticles_addarticle'] , '</a>&nbsp;-&nbsp;';

			}

			echo '
			<a href="', $scripturl, '?action=articles">', $txt['smfarticles_returnindex'], '</a>
            </div>
        </div>';



	ArticleSystemCopyright();
}

function template_search()
{
	global $txt, $context, $scripturl, $settings;

	ShowArticlesBar($txt['smfarticles_indextitle']);


	echo '
<form method="post" action="' , $scripturl , '?action=articles;sa=search2">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smfarticles_search_article'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%"  class="tborder" align="center">
  <tr class="windowbg2">
    <td width="50%"  align="right"><b>' , $txt['smfarticles_search_for'] , '</b>&nbsp;</td>
    <td width="50%"><input type="text" name="searchfor" size= "50" />
    </td>
  </tr>
  <tr class="windowbg2" align="center">
  	<td colspan="2"><input type="checkbox" name="searchtitle" checked="checked" />' , $txt['smfarticles_search_title'] , '&nbsp;<input type="checkbox" name="searchdescription" checked="checked" />' , $txt['smfarticles_search_description'] , '&nbsp;
  	</td>
  </tr>
  <tr class="windowbg2">
  	<td colspan="2" align="center">
  	<hr />
  	<b>',$txt['smfarticles_search_advsearch'],'</b><br />
  	<hr />

  	</td>
  </tr>
    <tr class="windowbg2">
    <td width="30%" align="right">' , $txt['smfarticles_category'], '&nbsp;</td>
  	<td width="70%">
		<select name="cat">
    	<option value="0">' , $txt['smfarticles_text_catnone'] , '</option>
    ';

	foreach ($context['articles_cat'] as $i => $category)
		echo '<option value="' , $category['ID_CAT']  , '" >' , $category['title'] , '</option>';


	echo '</select></td>
    </tr>
    <tr class="windowbg2">
     <td width="30%" align="right">' , $txt['smfarticles_search_daterange'], '&nbsp;</td>
  	<td width="70%">
		<select name="daterange">
    	<option value="0">' , $txt['smfarticles_search_alltime']  , '</option>
    	<option value="30">' , $txt['smfarticles_search_days30']  , '</option>
    	<option value="60">' , $txt['smfarticles_search_days60']  , '</option>
    	<option value="90">' , $txt['smfarticles_search_days90']  , '</option>
    	<option value="180">' , $txt['smfarticles_search_days180']  , '</option>
    	<option value="365">' , $txt['smfarticles_search_days365']  , '</option>

</select></td>
    </tr>

    <tr class="windowbg2">
     <td width="30%"  align="right">' , $txt['smfarticles_search_membername'], '&nbsp;</td>
  	<td width="70%">
		<input type="text" name="pic_postername" id="pic_postername" value="" />
	  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/members.png" alt="', $txt['find_members'], '" /></a>
	  <a href="', $scripturl, '?action=findmember;input=pic_postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a>
	  </td>
    </tr>


  <tr class="windowbg2">
    <td width="100%" colspan="2"  align="center"><br />
    <input type="submit" value="' , $txt['smfarticles_search'] , '" name="submit" />

    <br /></td>

  </tr>
  <tr class="titlebg">
  <td align="center" colspan="2">
  <a href="' , $scripturl , '?action=articles">' , $txt['smfarticles_returnindex'] , '</a>
  </td>
  </tr>
</table>
</form>
<br />

';

	ArticleSystemCopyright();
}

function template_search_results()
{
	global $txt, $context, $scripturl, $modSettings;

	// Setup their permissions
	$m_cats = allowedTo('articles_admin');

	ShowArticlesBar($txt['smfarticles_searchresults']);

			$spancount = 1;

			echo '<br /><table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">

		<thead>
		<tr class="title_bar">
						<th  class="lefttext first_th">
						' , $txt['smfarticles_ctitle'] , '</th>';

						if (!empty($modSettings['smfarticles_disp_rating']))
						{
							echo '<th  class="lefttext">' , $txt['smfarticles_crating'] , '</th>';
							$spancount++;
						}

						if (!empty($modSettings['smfarticles_disp_totalcomment']))
						{
							echo '<th  class="lefttext">' , $txt['smfarticles_txt_comments'] , '</th>';
							$spancount++;
						}

						if (!empty($modSettings['smfarticles_disp_views']))
						{
							echo '<th  class="lefttext">' , $txt['smfarticles_cviews'] , '</th>';
							$spancount++;
						}

						if (!empty($modSettings['smfarticles_disp_membername']))
						{
							echo '<th  class="lefttext">' , $txt['smfarticles_cusername'] , '</th>';
							$spancount++;
						}

						if (!empty($modSettings['smfarticles_disp_date']))
						{
							echo '<th  class="lefttext">' , $txt['smfarticles_cdate'] , '</th>';
							$spancount++;
						}

						if ($m_cats)
						{
							echo '
							<th  class="lefttext last_th">' , $txt['smfarticles_options'] ,'</th>';
							$spancount++;
						}

						echo '
					</tr>
					</thead>';

			$max_num_stars = 5;

			$styleclass = "windowbg";

			foreach($context['articles_listing'] as $row)
			{

				echo '<tr  class="' , $styleclass  , '">
				<td><a href="' , $scripturl , '?action=articles;sa=view;article=', $row['ID_ARTICLE'], '">', $row['title'], '</a></td>';

				if (!empty($modSettings['smfarticles_disp_rating']))
					echo '<td>' , GetStarsByPrecent(($row['totalratings'] != 0) ? ($row['rating'] / ($row['totalratings']* $max_num_stars) * 100) : 0), '</td>';

				if (!empty($modSettings['smfarticles_disp_totalcomment']))
					echo '<td>', $row['commenttotal'], '</td>';



				if (!empty($modSettings['smfarticles_disp_views']))
					echo '<td>', $row['views'], '</td>';


				// Check if it was a guest article
				if (!empty($modSettings['smfarticles_disp_membername']))
					if ($row['real_name'] != '')
						echo '<td><a href="', $scripturl, '?action=profile;u=', $row['ID_MEMBER'], '">', $row['real_name'], '</a></td>';
					else
						echo '<td>', $txt['smfarticles_txtguest'], '</td>';

				if (!empty($modSettings['smfarticles_disp_date']))
					echo '<td>', timeformat($row['date']), '</td>';

				if ($m_cats)
				{
					echo '<td>
					<a href="' , $scripturl , '?action=articles;sa=editarticle&id=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_txtedit'] , '</a>&nbsp;
					<a href="' , $scripturl , '?action=articles;sa=deletearticle&id=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_txtdel'] , '</a>&nbsp;
					<a href="' , $scripturl , '?action=articles;sa=noapprove&id=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_txtunapprove'] , '</a>
					</td>';
				}

				echo '</tr>';


				// Alternate style class
				if ($styleclass == 'windowbg')
					$styleclass = 'windowbg2';
				else
					$styleclass = 'windowbg';
			}


			// Show the pages
				echo '

				<tr class="titlebg">
						<td align="left" colspan="', $spancount, '">
						', $context['page_index'],'
						</td>
					</tr>';

			// Show return to articles index link
			echo '</table>';



   	echo '
                    <div class="tborder">
            <div class="roundframe centertext">';


			echo '
			<a href="', $scripturl, '?action=articles">', $txt['smfarticles_returnindex'], '</a>
            </div>
        </div>';


	ArticleSystemCopyright();
}

function template_view_rating()
{
	global $settings, $scripturl, $txt, $context;

	echo '<table cellspacing="0" cellpadding="5" border="0" align="center" width="50%" class="tborder">
				<tr class="titlebg">
					<td align="center" colspan="3">' , $txt['smfarticles_form_viewratings'], '</td>
				</tr>
				<tr class="titlebg">
					<td align="center">' , $txt['smfarticles_submittedby'] , '</td>
					<td align="center">' , $txt['smfarticles_text_rating'] , '</td>
					<td align="center">' , $txt['smfarticles_options'] , '</td>
				</tr>';

	foreach($context['articles_rating'] as $row)
	{
		echo '<tr class="windowbg2">
				<td align="center"><a href="' , $scripturl , '?action=profile;u=' , $row['ID_MEMBER'] , '">'  , $row['real_name'] , '</a></td>
				<td align="center">';
		// Show the star images
		for ($i=0; $i < $row['value']; $i++)
			echo '<img src="', $settings['images_url'], '/membericons/icon.png" alt="*" border="0" />';

		echo '</td>
			  <td align="center"><a href="' , $scripturl , '?action=articles;sa=delrating&id=' , $row['ID'] , '">'  , $txt['smfarticles_txtdel'] , '</a></td>
		      </tr>';
	}
	echo '
			<tr class="titlebg">
				<td align="center" colspan="3"><a href="' , $scripturl , '?action=articles;sa=view;article=' , $context['article_id'] , '">' , $txt['smfarticles_returnart'] , '</a></td>
			</tr>
	</table>';


	ArticleSystemCopyright();
}

function template_comment_list()
{
	global $scripturl, $txt, $context;

echo '
    <div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smfarticles_form_approvecomments'], '
        </h3>
</div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg2">
			<td>
			<form method="post" action="' , $scripturl , '?action=articles;sa=apprcomall">
			<input type="submit" value="' , $txt['smfarticles_approveallcomments'] , '" />
			</form>
			<br />
			<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">

		<thead>
		<tr class="title_bar">
				<th  class="lefttext first_th">' , $txt['smfarticles_comment_link'] , '</th>
				<th  class="lefttext">' , $txt['smfarticles_txt_comments']  , '</th>
				<th  class="lefttext">' , $txt['smfarticles_cdate'] , '</th>
				<th  class="lefttext">' , $txt['smfarticles_cusername'] , '</th>
				<th  class="lefttext last_th">' , $txt['smfarticles_options'] , '</th>
				</tr>
				</thead>';

			// List all Comments waiting approval
            $styleclass = 'windowbg';
			foreach($context['comment_list'] as $row)
			{
				echo '<tr class="' . $styleclass . '">
				<td><a href="' , $scripturl , '?action=articles;sa=view;article=' , $row['ID_ARTICLE'] , '">' , $txt['smfarticles_view_comment'] ,'</a></td>
				<td>' , $row['comment'] , '</td>
				<td>' , timeformat($row['date']) , '</td>';

				if($row['real_name'] != '')
					echo '<td><a href="' , $scripturl , '?action=profile;u=' , $row['ID_MEMBER'] , '">'  , $row['real_name'] , '</a></td>';
				else
					echo '<td>'  , $txt['smfarticles_txtguest'], '</td>';

				echo '<td><a href="' , $scripturl , '?action=articles;sa=apprcomment&id=' , $row['ID_COMMENT'] , '">' , $txt['smfarticles_txtapprove']  , '</a>
				<br /><br /><a href="' , $scripturl , '?action=articles;sa=delcomment&id=' , $row['ID_COMMENT'] , '">' ,$txt['smfarticles_txtdel']  , '</a></td>
				</tr>';

                if ($styleclass == 'windowbg')
    				$styleclass = 'windowbg2';
    			else
    				$styleclass = 'windowbg';

			}


			echo '<tr class="titlebg">
					<td align="left" colspan="5">
					', $context['page_index'],'
					</td>
				</tr>
			</table>

		</td>
		</tr>
</table>';

		ArticleSystemCopyright();

}

function template_add_comment()
{
	global $txt, $scripturl, $context, $settings;

	// Load the spell checker?
	if ($context['show_spellchecking'])
		echo '
									<script language="JavaScript" type="text/javascript" src="', $settings['default_theme_url'], '/scripts/spellcheck.js"></script>';


    ShowArticlesBar();

echo '<div class="tborder">
<form method="post" name="addcomment" id="addcomment" action="' , $scripturl , '?action=articles;sa=comment2" onsubmit="submitonce(this);">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smfarticles_text_addcomment'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0"  width="100%">
   <tr class="windowbg2">
   <td colspan="2" align="center">
   <table>
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
   </table></td></tr>

  <tr class="windowbg2">
    <td width="28%" colspan="2"  align="center">
    <input type="hidden" name="id" value="' , $context['article_id'] , '" />';

   	if (allowedTo('articles_autocomment') == false)
   		echo $txt['smfarticles_text_commentwait'] , '<br />';

   	if ($context['show_spellchecking'])
   		echo '
   									<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'addcomment\', \'message\');" />';


 echo '
    <input type="submit" value="', $txt['smfarticles_text_addcomment'], '" name="submit" /></td>

  </tr>
</table>
</form>';

	if ($context['show_spellchecking'])
			echo '<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>';


	echo '</div>';

	ArticleSystemCopyright();
}

function template_import()
{
	global $txt, $context, $scripturl;

	echo '
    <div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smfarticles_txt_importtparticles'] , '
        </h3>
</div>
<form method="post" action="' , $scripturl , '?action=articles;sa=importtp;doimport">
<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
 <tr class="windowbg2">
 <td colspan="2"  align="center">',$context['import_results'],'</td>
</tr>
  <tr class="windowbg">
    <td width="50%"  align="right"><b>' , $txt['smfarticles_category'] , '</b></td>
    <td width="50%"><select name="catid">';
		foreach ($context['articles_cat'] as $row)
  			echo '<option value="' , $row['ID_CAT'] , '">' , $row['title'] , '</option>';

echo '</select>
    </td>
  </tr>
  <tr  class="windowbg2">
    <td colspan="2" align="center">
    <input type="submit" value="', $txt['smfarticles_txt_importtparticles'], '" name="submit" /></td>
  </tr>
</table>
</form>';

echo '<br />';

	echo '
     <div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smfarticles_txt_importfaqmod'] , '
        </h3>
</div>
<form method="post" action="' , $scripturl , '?action=articles;sa=importfaq;doimport">
<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
 <tr class="windowbg2">
 <td colspan="2" align="center">',$context['import_results'],'</td>
</tr>
  <tr class="windowbg">
    <td width="50%" align="right"><b>' , $txt['smfarticles_category'] , '</b></td>
    <td width="50%"><select name="catid">';
		foreach ($context['articles_cat'] as $row)
  			echo '<option value="' , $row['ID_CAT'] , '">' , $row['title'] , '</option>';

echo '</select>
    </td>
  </tr>
  <tr class="windowbg2">
    <td colspan="2" align="center">
    <input type="submit" value="', $txt['smfarticles_txt_importfaqmod'], '" name="submit" /></td>
  </tr>
</table>
</form>';

echo '<br />';

	echo '
      <div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smfarticles_txt_importknowledgebase'], '
        </h3>
</div>
<form method="post" action="' , $scripturl , '?action=articles;sa=importkb;doimport">
<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
 <tr class="windowbg2">
 <td colspan="2" align="center">',$context['import_results'],'</td>
</tr>
  <tr class="windowbg2">
    <td width="50%" align="right"><b>' , $txt['smfarticles_category'] , '</b></td>
    <td width="50%"><select name="catid">';
		foreach ($context['articles_cat'] as $row)
  			echo '<option value="' , $row['ID_CAT'] , '">' , $row['title'] , '</option>';

echo '</select>
    </td>
  </tr>
  <tr class="windowbg2">
    <td colspan="2" align="center">
    <input type="submit" value="', $txt['smfarticles_txt_importknowledgebase'], '" name="submit" /></td>
  </tr>
</table>
</form>';

	ArticleSystemCopyright();
}

function ShowUserBox($memCommID)
{
	global $memberContext, $settings, $modSettings, $txt, $context, $scripturl, $options;

	echo '
	<b>', $memberContext[$memCommID]['link'], '</b>
	<div class="smalltext">';

	// Show the member's custom title, if they have one.
	if (isset($memberContext[$memCommID]['title']) && $memberContext[$memCommID]['title'] != '')
		echo '
		', $memberContext[$memCommID]['title'], '<br />';

	// Show the member's primary group (like 'Administrator') if they have one.
	if (isset($memberContext[$memCommID]['group']) && $memberContext[$memCommID]['group'] != '')
		echo '
		', $memberContext[$memCommID]['group'], '<br />';

	// Don't show these things for guests.
	if (!$memberContext[$memCommID]['is_guest'])
	{
		// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
		if ((empty($settings['hide_post_group']) || $memberContext[$memCommID]['group'] == '') && $memberContext[$memCommID]['post_group'] != '')
			echo '
		', $memberContext[$memCommID]['post_group'], '<br />';

		// Show online and offline buttons?
		if (!empty($modSettings['onlineEnable']) && !$memberContext[$memCommID]['is_guest'])
			echo '
		', $context['can_send_pm'] ? '<a href="' . $memberContext[$memCommID]['online']['href'] . '" title="' . $memberContext[$memCommID]['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $memberContext[$memCommID]['online']['image_href'] . '" alt="' . $memberContext[$memCommID]['online']['text'] . '" border="0" style="margin-top: 2px;" />' : $memberContext[$memCommID]['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $memberContext[$memCommID]['online']['text'] . '</span>' : '', '<br /><br />';

		// Show the member's gender icon?
		if (!empty($settings['show_gender']) && $memberContext[$memCommID]['gender']['image'] != '')
			echo '
		', $txt['gender'], ': ', $memberContext[$memCommID]['gender']['image'], '<br />';

		// Show how many posts they have made.
		echo '
		', $txt['member_postcount'], ': ', $memberContext[$memCommID]['posts'], '<br />
		<br />';

		// Show avatars, images, etc.?
		if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($memberContext[$memCommID]['avatar']['image']))
			echo '
		<div style="overflow: hidden; width: 100%;">', $memberContext[$memCommID]['avatar']['image'], '</div><br />';

		// Show their personal text?
		if (!empty($settings['show_blurb']) && $memberContext[$memCommID]['blurb'] != '')
			echo '
		', $memberContext[$memCommID]['blurb'], '<br />
		<br />';

		// This shows the popular messaging icons.
		echo '
		', $memberContext[$memCommID]['icq']['link'], $memberContext[$memCommID]['msn']['link'], $memberContext[$memCommID]['aim']['link'], $memberContext[$memCommID]['yim']['link'], '<br />';

		// Show the profile, website, email address, and personal message buttons.
		if ($settings['show_profile_buttons'])
		{
			echo '
		<a href="', $memberContext[$memCommID]['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt['view_profile'] . '" title="' . $txt['view_profile'] . '" border="0" />' : $txt['view_profile']), '</a>';

			// Don't show an icon if they haven't specified a website.
			if ($memberContext[$memCommID]['website']['url'] != '')
				echo '
		<a href="', $memberContext[$memCommID]['website']['url'], '" title="' . $memberContext[$memCommID]['website']['title'] . '" target="_blank">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt['www'] . '" border="0" />' : $txt['www']), '</a>';

			// Don't show the email address if they want it hidden.
			if (empty($memberContext[$memCommID]['hide_email']))
				echo '
		<a href="mailto:', $memberContext[$memCommID]['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" border="0" />' : $txt['email']), '</a>';

			// Since we know this person isn't a guest, you *can* message them.
			if ($context['can_send_pm'])
				echo '
		<a href="', $scripturl, '?action=pm;sa=send;u=', $memberContext[$memCommID]['id'], '" title="', $memberContext[$memCommID]['online']['label'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($memberContext[$memCommID]['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . $memberContext[$memCommID]['online']['label'] . '" border="0" />' : $memberContext[$memCommID]['online']['label'], '</a>';
		}
	}
	// Otherwise, show the guest's email.
	elseif (empty($memberContext[$memCommID]['hide_email']))
		echo '
		<br />
		<br />
		<a href="mailto:', $memberContext[$memCommID]['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" border="0" />' : $txt['email']), '</a>';

	// Done with the information about the poster... on to the post itself.
	echo '
	</div>';
}

function template_articles_copyright()
{
	global $txt, $scripturl, $context, $boardurl, $modSettings;


    $modID = 42;

    $urlBoardurl = urlencode(base64_encode($boardurl));

    	echo '
    <div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smfarticles_txt_copyrightremoval'], '
        </h3>
</div>

	<form method="post" action="',$scripturl,'?action=articles;sa=copyright;save=1">
<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
	<tr class="windowbg2">
		<td valign="top" align="right">',$txt['smfarticles_txt_copyrightkey'],'</td>
		<td><input type="text" name="articles_copyrightkey" size="50" value="' . $modSettings['articles_copyrightkey'] . '" />
        <br />
        <a href="http://www.smfhacks.com/copyright_removal.php?mod=' . $modID .  '&board=' . $urlBoardurl . '" target="_blank">' . $txt['smfarticles_txt_ordercopyright'] . '</a>
        </td>
	</tr>
    <tr class="windowbg2">
        <td colspan="2">' . $txt['smfarticles_txt_copyremovalnote'] . '</td>
    </tr>

	<tr class="windowbg2">
		<td valign="top" colspan="2" align="center"><input type="submit" value="' . $txt['smfarticles_settings_save'] . '" />
		</td>
		</tr>
	</table>
	</form>
    ';


    ArticleSystemCopyright();

}


function template_manage_cats()
{
	global $scripturl, $txt, $currentclass, $context, $cat_sep, $smcFunc;

echo '

<div class="cat_bar">
		<h3 class="catbg">
        ', $txt['smfarticles_txt_managecategories'], '
        </h3>
  </div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg2">
		<td>
			<a href="' . $scripturl . '?action=articles;sa=addcat">' . $txt['smfarticles_addcat'] . '</a>
			<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="table_grid">
			<thead>
			<tr class="title_bar">
				<th class="lefttext">' . $txt['smfarticles_category'] . '</th>
				<th class="centertext">' . $txt['smfarticles_totalarticles'] . '</th>
				<th class="leftext">' . $txt['smfarticles_options'] . '</th>
				</tr>
			</thead>
			';

		$currentclass = "windowbg";


		foreach ($context['articles_cat'] as $i => $category)
		{
					echo '<tr class="',$currentclass ,'">
				<td>
				<a href="' . $scripturl . '?action=articles;cat=' . $category['id_cat'] . '">',$category['title'],'</a>
				</td>
				<td align="center">' .  $category['total'] . '</td>
				<td><a href="' . $scripturl . '?action=articles;sa=editcat;cat=' . $category['id_cat'] . '">' . $txt['smfarticles_txtedit'] . '</a>&nbsp;
				<a href="' . $scripturl . '?action=articles;sa=deletecat;cat=' . $category['id_cat'] . '">' . $txt['smfarticles_txtdel'] . '</a>&nbsp;
				<a href="' . $scripturl . '?action=articles;sa=catperm;cat=' . $category['id_cat'] . '">' . $txt['smfarticles_txt_perm'] . '</a>';

				echo '
				</td>

				</tr>';

				if ($currentclass == "windowbg")
					$currentclass = "windowbg2";
				else
					$currentclass = "windowbg";
				$cat_sep = 1;
				GetManageSubCats($category['id_cat'],$context['articles_cat']);
				$cat_sep = 0;

		}



	echo '
		<tr class="windowbg2">
				<td colspan="3" align="center"><a href="' . $scripturl . '?action=articles;sa=addcat">' . $txt['smfarticles_addcat'] . '</a></td>
			</tr>
	</table>
	</td>
	</tr>
</table>';
}

function GetManageSubCats($id_parent,$categories)
{
	global $currentclass, $cat_sep, $scripturl, $txt;

	foreach ($categories as $i => $category)
	{
		if ($category['id_parent'] == $id_parent)
		{
			echo '
	<tr class="',$currentclass ,'">
		<td>
			<a href="' . $scripturl . '?action=articles;cat=' . $category['id_cat'] . '">',str_repeat('-',$cat_sep),$category['title'],'</a>
		</td>
		<td>' .  $category['total'] . '</td>
		<td><a href="' . $scripturl . '?action=articles;sa=editcat;cat=' . $category['id_cat'] . '">' . $txt['smfarticles_txtedit'] . '</a>&nbsp;
				<a href="' . $scripturl . '?action=articles;sa=deletecat;cat=' . $category['id_cat'] . '">' . $txt['smfarticles_txtdel'] . '</a>&nbsp;
				<a href="' . $scripturl . '?action=articles;sa=catperm;cat=' . $category['id_cat'] . '">' . $txt['smfarticles_txt_perm'] . '</a>
		</td>
	</tr>';

			if ($currentclass == "windowbg")
				$currentclass = "windowbg2";
			else
				$currentclass = "windowbg";
			$cat_sep++;
			GetManageSubCats($category['id_cat'],$categories);
			$cat_sep--;
		}
	}
}
?>