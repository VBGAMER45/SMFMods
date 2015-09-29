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
			<div id="profile_success" class="global_hf_success">
				<span class="floatleft success_cont">
					<img src="', $settings['default_images_url'], '/smfhacks_images/global-hf-tick-circle.png" alt="" />
				</span>
				', $txt['global_hf']['success'], '
			</div>
		';
	}
	echo '
		<div class="cat_bar" style="height: 28px;">
			<h3 class="catbg">', $txt['global_hf']['title'], '</h3>
		</div>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">
					<form action="', $scripturl, '?action=admin;area=globalhf;sa=save_settings" method="post">
						<div class="floatleft" style="width: 30%;">
							<label for="global_head">
								<strong>', $txt['global_hf']['head_content'], ':</strong>
							</label>
							<div class="smalltext">', $txt['global_hf']['head_content_desc'], '</div>
						</div>
						<div class="floatright" style="width: 70%;">
							<textarea id="global_head" name="global_head" class="global_hf_textarea" cols="" rows="">', $global_hf['head'], '</textarea>
						</div>
						<br class="clear" />
						<hr />
						<div class="floatleft" style="width: 30%;">
							<label for="global_header">
								<strong>', $txt['global_hf']['header_content'], ':</strong>
							</label>
							<div class="smalltext">', $txt['global_hf']['header_content_desc'], '.</div>
						</div>
						<div class="floatright" style="width: 70%;">
							<textarea id="global_header" name="global_header" class="global_hf_textarea" cols="" rows="">', $global_hf['header'], '</textarea>
							<div class="smalltext">', $txt['global_hf']['html_allowed'], '</div>
						</div>
						<br class="clear" />
						<div class="floatleft" style="width: 29.8%;">
							<label for="global_header_bbc">
								<strong>', $txt['global_hf']['parse_bbc'], ':</strong>
							</label>
							<div class="smalltext">', $txt['global_hf']['parse_header_bbc_desc'], '</div>
						</div>
						<div class="floatright" style="width: 70.2%;">
							<select id="global_header_bbc" name="global_header_bbc">
								<optgroup label="', $txt['global_hf']['options'], ':">
									<option value="0"', !isset($modSettings['global_header_bbc']) || empty($modSettings['global_header_bbc']) ? ' selected="selected"' : '' , '>', $txt['global_hf']['no'], '</option>
									<option value="1"', !empty($modSettings['global_header_bbc']) ? ' selected="selected"' : '' , '>', $txt['global_hf']['yes'], '</option>
								</optgroup>
							</select>
						</div>
						<br class="clear" />
						<hr />
						<div class="floatleft" style="width: 30%;">
							<label for="global_footer">
								<strong>', $txt['global_hf']['footer_content'], ':</strong>
							</label>
							<div class="smalltext">', $txt['global_hf']['footer_content_desc'], '</div>
						</div>
						<div class="floatright" style="width: 70%;">
							<textarea id="global_footer" name="global_footer" class="global_hf_textarea" cols="" rows="">', $global_hf['footer'], '</textarea>
							<div class="smalltext">', $txt['global_hf']['html_allowed'], '</div>
						</div>
						<br class="clear" />
						<div class="floatleft" style="width: 29.8%;">
							<label for="global_footer_bbc">
								<strong>', $txt['global_hf']['parse_bbc'], ':</strong>
							</label>
							<div class="smalltext">', $txt['global_hf']['parse_footer_bbc_desc'], '</div>
						</div>
						<div class="floatright" style="width: 70.2%;">
							<select id="global_footer_bbc" name="global_footer_bbc">
								<optgroup label="', $txt['global_hf']['options'], ':">
									<option value="0"', !isset($modSettings['global_footer_bbc']) || empty($modSettings['global_footer_bbc']) ? ' selected="selected"' : '' , '>', $txt['global_hf']['no'], '</option>
									<option value="1"', !empty($modSettings['global_footer_bbc']) ? ' selected="selected"' : '' , '>', $txt['global_hf']['yes'], '</option>
								</optgroup>
							</select>
						</div>
						<br class="clear" />
						<hr />
						<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="submit" value="', $txt['global_hf']['save'], '" class="button_submit global_hf_button" />
					</form>
				</div>
			</div>
		</div>
		<span class="lowerframe"><span></span></span>
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