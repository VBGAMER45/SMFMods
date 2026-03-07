<?php
/*
Tagging System
Version 4.2
by:vbgamer45
https://www.smfhacks.com
*/
function template_main()
{
	global $txt, $context, $scripturl;

	// Tag Cloud
	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['smftags_popular'], '</h3>
	</div>
	<div class="windowbg centertext">';

	if (isset($context['poptags']))
		echo $context['poptags'];
	else
		echo $txt['smftags_no_tags'];

	echo '
	</div>';

	// Latest Tagged Posts
	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['smftags_latest'], '</h3>
	</div>
	<table class="table_grid">
		<thead>
			<tr class="title_bar">
				<th scope="col">', $txt['smftags_subject'], '</th>
				<th scope="col" class="centercol" style="width: 11%;">', $txt['smftags_topictag'], '</th>
				<th scope="col" class="centercol" style="width: 11%;">', $txt['smftags_startedby'], '</th>
				<th scope="col" class="centercol" style="width: 6%;">', $txt['smftags_replies'], '</th>
				<th scope="col" class="centercol" style="width: 6%;">', $txt['smftags_views'], '</th>
			</tr>
		</thead>
		<tbody>';

	if (empty($context['tags_topics']))
	{
		echo '
			<tr class="windowbg">
				<td colspan="5" class="centertext">', $txt['smftags_no_tags'], '</td>
			</tr>';
	}
	else
	{
		foreach ($context['tags_topics'] as $topic)
		{
			echo '
			<tr class="windowbg">
				<td><a href="', $scripturl, '?topic=', $topic['id_topic'], '.0">', $topic['subject'], '</a></td>
				<td class="centertext"><a href="', $scripturl, '?action=tags;tagid=', $topic['id_tag'], '">', $topic['tag'], '</a></td>
				<td class="centertext"><a href="', $scripturl, '?action=profile;u=', $topic['id_member'], '">', $topic['poster_name'], '</a></td>
				<td class="centertext">', $topic['num_replies'], '</td>
				<td class="centertext">', $topic['num_views'], '</td>
			</tr>';
		}
	}

	echo '
		</tbody>
	</table>';

	TagsCopyright();
}

function template_results()
{
	global $scripturl, $txt, $context;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['smftags_resultsfor'], $context['tag_search'], '</h3>
	</div>
	<div class="pagesection">
		<div class="pagelinks">', $context['page_index'], '</div>
	</div>
	<table class="table_grid">
		<thead>
			<tr class="title_bar">
				<th scope="col">', $txt['smftags_subject'], '</th>
				<th scope="col" class="centercol" style="width: 11%;">', $txt['smftags_startedby'], '</th>
				<th scope="col" class="centercol" style="width: 6%;">', $txt['smftags_replies'], '</th>
				<th scope="col" class="centercol" style="width: 6%;">', $txt['smftags_views'], '</th>
			</tr>
		</thead>
		<tbody>';

	if (empty($context['tags_topics']))
	{
		echo '
			<tr class="windowbg">
				<td colspan="4" class="centertext">', $txt['smftags_no_tags'], '</td>
			</tr>';
	}
	else
	{
		foreach ($context['tags_topics'] as $topic)
		{
			echo '
			<tr class="windowbg">
				<td><a href="', $scripturl, '?topic=', $topic['id_topic'], '.0">', $topic['subject'], '</a></td>
				<td class="centertext"><a href="', $scripturl, '?action=profile;u=', $topic['id_member'], '">', $topic['poster_name'], '</a></td>
				<td class="centertext">', $topic['num_replies'], '</td>
				<td class="centertext">', $topic['num_views'], '</td>
			</tr>';
		}
	}

	echo '
		</tbody>
	</table>
	<div class="pagesection">
		<div class="pagelinks">', $context['page_index'], '</div>
	</div>';

	TagsCopyright();
}

function template_addtag()
{
	global $scripturl, $txt, $context;

	echo '
	<form method="post" action="', $scripturl, '?action=tags;sa=addtag2">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['smftags_addtag2'], '</h3>
		</div>
		<div class="roundframe">
			<dl class="settings">
				<dt>
					<strong>', $txt['smftags_tagtoadd'], '</strong><br>
					<span class="smalltext">', $txt['smftags_seperate'], '</span>
				</dt>
				<dd>
					<input type="text" name="tag" size="64" maxlength="100">
				</dd>
			</dl>
			<input type="hidden" name="topic" value="', $context['tags_topic'], '">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			<input type="submit" value="', $txt['smftags_addtag2'], '" class="button">
		</div>
	</form>';

	TagsCopyright();
}

function template_admin_settings()
{
	global $scripturl, $txt, $modSettings, $context;

	echo '
	<form method="post" action="', $scripturl, '?action=tags;sa=admin2">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['smftags_settings'], '</h3>
		</div>
		<div class="windowbg">
			<dl class="settings">
				<dt>
					<strong>', $txt['smftags_set_mintaglength'], '</strong>
				</dt>
				<dd>
					<input type="number" name="smftags_set_mintaglength" value="', $modSettings['smftags_set_mintaglength'], '" min="1">
				</dd>
				<dt>
					<strong>', $txt['smftags_set_maxtaglength'], '</strong>
				</dt>
				<dd>
					<input type="number" name="smftags_set_maxtaglength" value="', $modSettings['smftags_set_maxtaglength'], '" min="1">
				</dd>
				<dt>
					<strong>', $txt['smftags_set_maxtags'], '</strong>
				</dt>
				<dd>
					<input type="number" name="smftags_set_maxtags" value="', $modSettings['smftags_set_maxtags'], '" min="1">
				</dd>
				<dt>
					<strong>', $txt['smftags_set_msgindex'], '</strong>
				</dt>
				<dd>
					<input type="checkbox" name="smftags_set_msgindex"', !empty($modSettings['smftags_set_msgindex']) ? ' checked' : '', '>
				</dd>
				<dt>
					<strong>', $txt['smftags_set_msgindex_max_show'], '</strong>
				</dt>
				<dd>
					<input type="number" name="smftags_set_msgindex_max_show" value="', $modSettings['smftags_set_msgindex_max_show'], '" min="1">
				</dd>
				<dt>
					<strong>', $txt['smftags_set_use_css_tags'], '</strong>
				</dt>
				<dd>
					<input type="checkbox" name="smftags_set_use_css_tags"', !empty($modSettings['smftags_set_use_css_tags']) ? ' checked' : '', '>
				</dd>
				<dt>
					<strong>', $txt['smftags_set_css_tag_background_color'], '</strong>
				</dt>
				<dd>
					<input type="color" name="smftags_set_css_tag_background_color" value="', $modSettings['smftags_set_css_tag_background_color'], '">
				</dd>
				<dt>
					<strong>', $txt['smftags_set_css_tag_font_color'], '</strong>
				</dt>
				<dd>
					<input type="color" name="smftags_set_css_tag_font_color" value="', $modSettings['smftags_set_css_tag_font_color'], '">
				</dd>
			</dl>
		</div>
		<div class="cat_bar">
			<h3 class="catbg">', $txt['smftags_tagcloud_settings'], '</h3>
		</div>
		<div class="windowbg">
			<dl class="settings">
				<dt>
					<strong>', $txt['smftags_set_cloud_tags_to_show'], '</strong>
				</dt>
				<dd>
					<input type="number" name="smftags_set_cloud_tags_to_show" value="', $modSettings['smftags_set_cloud_tags_to_show'], '" min="1">
				</dd>
				<dt>
					<strong>', $txt['smftags_set_cloud_tags_per_row'], '</strong>
				</dt>
				<dd>
					<input type="number" name="smftags_set_cloud_tags_per_row" value="', $modSettings['smftags_set_cloud_tags_per_row'], '" min="1">
				</dd>
				<dt>
					<strong>', $txt['smftags_set_cloud_max_font_size_precent'], '</strong>
				</dt>
				<dd>
					<input type="number" name="smftags_set_cloud_max_font_size_precent" value="', $modSettings['smftags_set_cloud_max_font_size_precent'], '" min="50">
				</dd>
				<dt>
					<strong>', $txt['smftags_set_cloud_min_font_size_precent'], '</strong>
				</dt>
				<dd>
					<input type="number" name="smftags_set_cloud_min_font_size_precent" value="', $modSettings['smftags_set_cloud_min_font_size_precent'], '" min="50">
				</dd>
			</dl>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			<input type="submit" name="savesettings" value="', $txt['smftags_savesettings'], '" class="button">
		</div>
	</form>';

	TagsCopyright();
}

function template_suggest()
{
	global $scripturl, $txt, $context;

	echo '
	<form method="post" action="', $scripturl, '?action=tags;sa=suggest2">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['smftags_suggest'], '</h3>
		</div>
		<div class="roundframe">
			<dl class="settings">
				<dt>
					<strong>', $txt['smftags_tagtosuggest'], '</strong>
				</dt>
				<dd>
					<input type="text" name="tag" size="64" maxlength="100">
				</dd>
			</dl>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			<input type="submit" value="', $txt['smftags_suggest'], '" class="button">
		</div>
	</form>';

	TagsCopyright();
}

function TagsCopyright()
{
	echo '<div class="centertext smalltext">Powered by: <a href="https://www.smfhacks.com" target="_blank" rel="noopener">SMF Tags</a></div>';
}
?>
