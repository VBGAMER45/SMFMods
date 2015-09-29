<?php
/**
 * Global Headers and Footers (global-hf_)
 *
 * @file ./smfhacks_templates/global-hf.template.php
 * @author SMFHacks <http://www.smfhacks.com/>
 * @copyright SMFHacks.com Team, 2012
 *
 * @version 2.0.1
 */

function template_global_hf_admin()
{
	global $context, $settings, $scripturl, $txt, $global_hf, $modSettings;
	if (isset($_GET['success']) && $_GET['success'] == 'saved')
	{
		echo '
			<div class="global_hf_success">
				<span class="floatleft success_cont">
					<img src="', $settings['default_images_url'], '/smfhacks_images/tick-circle.png" alt="" />
				</span>
				', $txt['global_hf']['success'], '
			</div>
		';
	}
	echo '
		<form action="', $scripturl, '?action=admin;area=globalhf" method="post">
			<table border="0" cellspacing="0" cellpadding="4" width="100%" class="tborder">
				<tbody>
					<tr class="catbg">
						<td colspan="3">', $txt['global_hf']['title'], '</td>
					</tr>
					<tr class="windowbg2">
						<td class="windowbg2" colspan="1" width="30%" valign="top">
							<label for="global_head">
								<strong>', $txt['global_hf']['head_content'], ':</strong>
							</label>
							<div class="smalltext">', $txt['global_hf']['head_content_desc'], '</div>
						</td>
						<td class="windowbg2" colspan="2" width="70%">
							<textarea id="global_head" name="global_head" class="global_hf_textarea" cols="" rows="">', $global_hf['head'], '</textarea>
						</td>
					</tr>
					<tr class="windowbg2">
						<td colspan="3" class="windowbg2">
							<hr size="1" width="100%" class="hrcolor" />
						</td>
					</tr>
					<tr class="windowbg2">
						<td class="windowbg2" colspan="1" width="30%" valign="top">
							<label for="global_header">
								<strong>', $txt['global_hf']['header_content'], ':</strong>
							</label>
							<div class="smalltext">', $txt['global_hf']['header_content_desc'], '</div>
						</td>
						<td class="windowbg2" colspan="2" width="70%">
							<textarea id="global_header" name="global_header" class="global_hf_textarea" cols="" rows="">', $global_hf['header'], '</textarea>
							<div class="smalltext">', $txt['global_hf']['html_allowed'], '</div>
						</td>
					</tr>
					<tr class="windowbg2">
						<td class="windowbg2" colspan="1" width="28%" valign="top">
							<label for="global_header_bbc">
								<strong>', $txt['global_hf']['parse_bbc'], ':</strong>
							</label>
							<div class="smalltext">', $txt['global_hf']['parse_header_bbc_desc'], '</div>
						</td>
						<td class="windowbg2" colspan="2" width="72%">
							<select id="global_header_bbc" name="global_header_bbc">
								<optgroup label="', $txt['global_hf']['options'], ':">
									<option value="0"', !isset($modSettings['global_header_bbc']) || empty($modSettings['global_header_bbc']) ? ' selected="selected"' : '' , '>', $txt['global_hf']['no'], '</option>
									<option value="1"', !empty($modSettings['global_header_bbc']) ? ' selected="selected"' : '' , '>', $txt['global_hf']['yes'], '</option>
								</optgroup>
							</select>
						</td>
					</tr>
					<tr class="windowbg2">
						<td colspan="3" class="windowbg2">
							<hr size="1" width="100%" class="hrcolor" />
						</td>
					</tr>
					<tr class="windowbg2">
						<td class="windowbg2" colspan="1" width="30%" valign="top">
							<label for="global_footer">
								<strong>', $txt['global_hf']['footer_content'], ':</strong>
							</label>
							<div class="smalltext">', $txt['global_hf']['footer_content_desc'], '</div>
						</td>
						<td class="windowbg2" colspan="2" width="70%">
							<textarea id="global_footer" name="global_footer" class="global_hf_textarea" cols="" rows="">', $global_hf['footer'], '</textarea>
							<div class="smalltext">', $txt['global_hf']['html_allowed'], '</div>
						</td>
					</tr>
					<tr class="windowbg2">
						<td class="windowbg2" colspan="1" width="28%" valign="top">
							<label for="global_footer_bbc">
								<strong>', $txt['global_hf']['parse_bbc'], ':</strong>
							</label>
							<div class="smalltext">', $txt['global_hf']['parse_footer_bbc_desc'], '</div>
						</td>
						<td class="windowbg2" colspan="2" width="72%">
							<select id="global_footer_bbc" name="global_footer_bbc">
								<optgroup label="', $txt['global_hf']['options'], ':">
									<option value="0"', !isset($modSettings['global_footer_bbc']) || empty($modSettings['global_footer_bbc']) ? ' selected="selected"' : '' , '>', $txt['global_hf']['no'], '</option>
									<option value="1"', !empty($modSettings['global_footer_bbc']) ? ' selected="selected"' : '' , '>', $txt['global_hf']['yes'], '</option>
								</optgroup>
							</select>
						</td>
					</tr>
					<tr class="windowbg2">
						<td colspan="3" class="windowbg2">
							<hr size="1" width="100%" class="hrcolor" />
						</td>
					</tr>
					<tr>
						<td class="windowbg2" colspan="3" align="center" valign="middle">
							<input type="hidden" id="sc" name="sc" value="', $context['session_id'], '" />
							<input type="submit" value="', $txt['global_hf']['save'], '" class="global_hf_button" />
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	';
}

function template_global_hf_above()
{
	global $global_hf;
	echo isset($global_hf['parsed']['header']) ? $global_hf['parsed']['header'] : $global_hf['header'];
}

function template_global_hf_below()
{
	global $global_hf;
	echo isset($global_hf['parsed']['footer']) ? $global_hf['parsed']['footer'] : $global_hf['footer'];
}

function template_global_hf_copyright_above()
{
}

function template_global_hf_copyright_below()
{
	echo '<div class="centertext smalltext">' . GlobalHF_VERSION . ' | ' . GlobalHF_COPYRIGHT . '</div>';
}