<?php
/*
Tagging System
Version 4.1
by:vbgamer45
https://www.smfhacks.com
*/
function template_main()
{
	global $txt, $context, $scripturl;

	echo '
<div class="cat_bar">
		<h3 class="catbg centertext">
		', $txt['smftags_popular'], '
		</h3>
  </div>
	<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
  	<td align="center" class="windowbg2">';

  	if (isset($context['poptags']))
  		echo $context['poptags'];

 echo '
  	</td>
  	</tr>
  	</table>
  	<br />
<div class="cat_bar">
		<h3 class="catbg centertext">
		', $txt['smftags_latest'], '
		</h3>
  </div>

  	<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
					<tr>
						<td class="catbg">',$txt['smftags_subject'],'</td>
						<td class="catbg" width="11%">',$txt['smftags_topictag'],'</td>
						<td class="catbg" width="11%">',$txt['smftags_startedby'],'</td>
						<td class="catbg" width="4%" align="center">',$txt['smftags_replies'],'</td>
						<td class="catbg" width="4%" align="center">', $txt['smftags_views'], '</td>
					</tr>';
		foreach ($context['tags_topics'] as $i => $topic)
		{
				echo '<tr>';
					echo '<td class="windowbg2"><a href="' . $scripturl . '?topic=' . $topic['id_topic'] . '.0">' . $topic['subject'] . '</a></td>';
					echo '<td class="windowbg2"><a href="' . $scripturl . '?action=tags;tagid=' . $topic['ID_TAG'] . '">' . $topic['tag'] . '</a></td>';
					echo '<td class="windowbg2"><a href="' . $scripturl . '?action=profile;u=' . $topic['id_member'] . '">' . $topic['poster_name'] . '</a></td>';
					echo '<td class="windowbg2">' . $topic['num_replies'] . '</td>';
					echo '<td class="windowbg2">' . $topic['num_views'] . '</td>';
				echo '</tr>';

		}
echo '

  	</table>
  	<br />
  	';

	TagsCopyright();
}

function template_results()
{
	global $scripturl, $txt, $context;
echo '
<div class="cat_bar">
		<h3 class="catbg centertext">
		' . $txt['smftags_resultsfor'] . $context['tag_search'] . '
		</h3>
  </div>
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
					<tr>
						<td class="catbg">',$txt['smftags_subject'],'</td>
						<td class="catbg" width="11%">',$txt['smftags_startedby'],'</td>
						<td class="catbg" width="4%" align="center">',$txt['smftags_replies'],'</td>
						<td class="catbg" width="4%" align="center">', $txt['smftags_views'], '</td>
					</tr>';
		foreach ($context['tags_topics'] as $i => $topic)
		{
				echo '<tr>';
					echo '<td class="windowbg2"><a href="' . $scripturl . '?topic=' . $topic['id_topic'] . '.0">' . $topic['subject'] . '</a></td>';
					echo '<td class="windowbg2"><a href="' . $scripturl . '?action=profile;u=' . $topic['id_member'] . '">' . $topic['poster_name'] . '</a></td>';
					echo '<td class="windowbg2">', $topic['num_replies'], '</td>';
					echo '<td class="windowbg2">', $topic['num_views'], '</td>';
				echo '</tr>';

		}
echo '
	<tr>
	<td colspan="4">' . $context['page_index'] . '</td>
  	</tr>
  	</table><br />
  	';

	TagsCopyright();

}

function template_addtag()
{
		global $scripturl, $txt, $context;

	echo '
<form method="post" action="', $scripturl, '?action=tags;sa=addtag2">
<div class="cat_bar">
		<h3 class="catbg centertext">
        ',  $txt['smftags_addtag2'], '
        </h3>
</div>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td width="28%"  class="windowbg2" align="right"><span class="gen"><b>', $txt['smftags_tagtoadd'], '</b></span></td>
    <td width="72%" class="windowbg2"><input type="text" name="tag" size="64" maxlength="100" /></td>
  </tr>

  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="hidden" name="topic" value="', $context['tags_topic'], '" />
    <input type="submit" value="', $txt['smftags_addtag2'], '" name="submit" /></td>

  </tr>
</table>
</form>
';
	
	
	TagsCopyright();

}

function template_admin_settings()
{
	global $scripturl, $txt, $modSettings;

	echo '
<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['smftags_settings'], '
        </h3>
</div>
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg2">
			<td>
			<b>' . $txt['smftags_settings']. '</b><br />
			<form method="post" action="' . $scripturl . '?action=tags;sa=admin2">
				<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4">
				<tr><td width="30%">' . $txt['smftags_set_mintaglength'] . '</td><td><input type="text" name="smftags_set_mintaglength" value="' .  $modSettings['smftags_set_mintaglength'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['smftags_set_maxtaglength'] . '</td><td><input type="text" name="smftags_set_maxtaglength" value="' .  $modSettings['smftags_set_maxtaglength'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['smftags_set_maxtags'] . '</td><td><input type="text" name="smftags_set_maxtags" value="' .  $modSettings['smftags_set_maxtags'] . '" /></td></tr>
				
				
				<tr><td width="30%">' . $txt['smftags_set_msgindex'] . '</td><td><input type="checkbox" name="smftags_set_msgindex" ' .  ($modSettings['smftags_set_msgindex'] ? ' checked="checked"' : '') . '" /></td></tr>
				
				<tr><td width="30%">' . $txt['smftags_set_msgindex_max_show'] . '</td><td><input type="text" name="smftags_set_msgindex_max_show" value="' .  $modSettings['smftags_set_msgindex_max_show'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['smftags_set_use_css_tags'] . '</td><td><input type="checkbox" name="smftags_set_use_css_tags" ' .  ($modSettings['smftags_set_use_css_tags'] ? ' checked="checked"' : '') . '" /></td></tr>
				
				<tr><td width="30%">' . $txt['smftags_set_css_tag_background_color'] . '</td><td><input type="text" name="smftags_set_css_tag_background_color" value="' .  $modSettings['smftags_set_css_tag_background_color'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['smftags_set_css_tag_font_color'] . '</td><td><input type="text" name="smftags_set_css_tag_font_color" value="' .  $modSettings['smftags_set_css_tag_font_color'] . '" /></td></tr>

				<tr>
				<td colspan="2"><b>',$txt['smftags_tagcloud_settings'],'</b></td>
				</tr>
				<tr><td width="30%">' . $txt['smftags_set_cloud_tags_to_show'] . '</td><td><input type="text" name="smftags_set_cloud_tags_to_show" value="' .  $modSettings['smftags_set_cloud_tags_to_show'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['smftags_set_cloud_tags_per_row'] . '</td><td><input type="text" name="smftags_set_cloud_tags_per_row" value="' .  $modSettings['smftags_set_cloud_tags_per_row'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['smftags_set_cloud_max_font_size_precent'] . '</td><td><input type="text" name="smftags_set_cloud_max_font_size_precent" value="' .  $modSettings['smftags_set_cloud_max_font_size_precent'] . '" /></td></tr>
				<tr><td width="30%">' . $txt['smftags_set_cloud_min_font_size_precent'] . '</td><td><input type="text" name="smftags_set_cloud_min_font_size_precent" value="' .  $modSettings['smftags_set_cloud_min_font_size_precent'] . '" /></td></tr>
				</table>

				<input type="submit" name="savesettings" value="', $txt['smftags_savesettings'],  '" />
			</form>
<b>Has SMF Tags helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@visualbasiczone.com">
	<input type="hidden" name="item_name" value="SMF Tags">
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

	TagsCopyright();
}

function template_suggesttag()
{
	global $scripturl, $txt;

	echo '
<form method="POST" action="', $scripturl, '?action=tags;sa=suggest2">
<div class="cat_bar">
		<h3 class="catbg centertext">
		' . $txt['smftags_suggest'] . '
		</h3>
  </div>
<table border="1" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="28%" class="windowbg2" align="right"><span class="gen"><b>', $txt['smftags_tagtosuggest'], '</b></span></td>
    <td width="72%"  class="windowbg2"><input type="text" name="tag" size="64" maxlength="100" /></td>
  </tr>
  <tr>
    <td width="28%" colspan="2" align="center" class="windowbg2">
    <input type="submit" value="', $txt['smftags_suggest'], '" name="submit" /></td>
  </tr>
</table>
</form>
';
	
	TagsCopyright();
	
}

function TagsCopyright()
{
	//The Copyright is required to remain or contact me to purchase link removal.
	//http://www.smfhacks.com/copyright_removal.php
	echo '<br /><div align="center"><span class="smalltext">Powered by: <a href="https://www.smfhacks.com" target="blank">SMF Tags</a></span></div>';

}
?>