<?php

/**
 * Multi Accounts - Templates
 *
 * @package MultiAccounts
 * @author vbgamer45
 * @license BSD
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Main account listing template.
 */
function template_manage_multiaccounts()
{
	global $context, $settings, $scripturl, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['multiaccounts_manage'], !$context['user']['is_owner'] ? ' - &quot;' . $context['member']['name'] . '&quot;' : '', '
		</h3>
	</div>';

	// Error messages
	if (!empty($context['post_errors']))
	{
		echo '
	<div class="errorbox">
		<strong>', !empty($context['custom_error_title']) ? $context['custom_error_title'] : $txt['error_occurred'], '</strong>
		<ul>';

		foreach ($context['post_errors'] as $error)
			echo '
			<li>', isset($txt[$error]) ? $txt[$error] : $error, '</li>';

		echo '
		</ul>
	</div>';
	}

	if (!empty($context['page_desc']))
	{
		echo '
	<div class="information">', $context['page_desc'], '</div>';
	}

	if (empty($context['multiaccounts_list']))
		echo '
	<div class="information">', $txt['multiaccounts_none'], '</div>';
	else
	{
		echo '
	<div class="multiaccounts_grid">';

		foreach ($context['multiaccounts_list'] as $account)
		{
			echo '
		<div class="multiaccounts_card roundframe">
			<div class="multiaccounts_card_header">
				<a href="', $account['href'], '">', $account['name'], '</a>
				', $account['is_shared'] ? '<span class="generic_icons live" title="' . $txt['multiaccounts_shared'] . '"></span>' : '', '
			</div>
			<div class="multiaccounts_card_body">
				<div class="multiaccounts_card_info">
					<span class="multiaccounts_group">', $account['group'], '</span>';

			if (!empty($account['icons']))
				echo '
					<span class="multiaccounts_icons">', $account['icons'], '</span>';

			echo '
					<span class="multiaccounts_posts">', $account['posts'], ' ', $txt['posts'], '</span>
					<span class="multiaccounts_pms">', $txt['personal_messages'], ': ', !empty($account['messages']['unread']) ? $account['messages']['unread'] . '/' : '', $account['messages']['total'], '</span>
				</div>
			</div>
			<div class="multiaccounts_card_actions">';

			if ($account['permissions']['can_delete'])
				echo '
				<a href="', $scripturl, '?action=profile;area=managemultiaccounts;sa=delete;u=', $context['member']['id'], ';subaccount=', $account['id'], ';', $context['session_var'], '=', $context['session_id'], '" class="button" onclick="return confirm(\'', $txt['multiaccounts_confirm_delete'], '\');">', $txt['delete'], '</a>';

			if ($account['permissions']['can_merge'])
				echo '
				<a href="', $scripturl, '?action=profile;area=managemultiaccounts;sa=merge;u=', $context['member']['id'], ';subaccount=', $account['id'], '" class="button">', $txt['multiaccounts_merge'], '</a>';

			if ($account['permissions']['can_split'])
				echo '
				<a href="', $scripturl, '?action=profile;area=managemultiaccounts;sa=split;u=', $context['member']['id'], ';subaccount=', $account['id'], '" class="button">', $txt['multiaccounts_split'], '</a>';

			if ($account['permissions']['can_reassign'])
				echo '
				<a href="', $scripturl, '?action=profile;area=managemultiaccounts;sa=reassign;u=', $context['member']['id'], ';subaccount=', $account['id'], '" class="button">', $txt['multiaccounts_reassign'], '</a>';

			if ($account['permissions']['can_share'])
				echo '
				<a href="', $scripturl, '?action=profile;area=managemultiaccounts;sa=create;u=', $context['member']['id'], ';subaccount=', $account['id'], ';make_shared" class="button">', $account['is_shared'] ? $txt['multiaccounts_unshare'] : $txt['multiaccounts_share'], '</a>';

			echo '
			</div>
		</div>';
		}

		echo '
	</div>';
	}

	if (!empty($context['can_create']))
		echo '
	<form action="', $scripturl, '?action=profile;area=managemultiaccounts;sa=create;u=', $context['member']['id'], '" method="post" accept-charset="', $context['character_set'], '">
		<input type="submit" name="create" value="', $txt['multiaccounts_create'], '" class="button">
	</form>';
}

/**
 * Create/link account form template.
 */
function template_manage_multiaccounts_create()
{
	global $context, $settings, $scripturl, $modSettings, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['multiaccounts_manage'], !$context['user']['is_owner'] ? ' - &quot;' . $context['member']['name'] . '&quot;' : '', '
		</h3>
	</div>';

	// Error messages
	if (!empty($context['post_errors']))
	{
		echo '
	<div class="errorbox">
		<strong>', !empty($context['custom_error_title']) ? $context['custom_error_title'] : $txt['error_occurred'], '</strong>
		<ul>';

		foreach ($context['post_errors'] as $error)
			echo '
			<li>', isset($txt[$error]) ? $txt[$error] : $error, '</li>';

		echo '
		</ul>
	</div>';
	}

	echo '
	<div class="information">', $txt['multiaccounts_create_desc'], '</div>
	<form action="', $scripturl, '?action=profile;area=managemultiaccounts;sa=create;u=', $context['member']['id'], '" method="post" accept-charset="', $context['character_set'], '" name="postForm" id="postForm">
		<div class="roundframe">
			<dl class="settings">
				<dt>
					<strong><label for="smf_autov_username">', $txt['multiaccounts_username'], ':</label></strong>
					<span class="smalltext">', $txt['multiaccounts_username_desc'], '</span>
				</dt>
				<dd>
					<input type="text" name="username" id="smf_autov_username" size="30" tabindex="', $context['tabindex']++, '" maxlength="25" value="', isset($context['username']) ? $context['username'] : '', '">
					<span id="smf_autov_username_div" style="display: none;">
						<a id="smf_autov_username_link" href="#">
							<img id="smf_autov_username_img" src="', $settings['images_url'], '/icons/field_check.png" alt="*">
						</a>
					</span>
				</dd>
				<dt>
					<strong><label for="smf_autov_pwmain">', $txt['password'], ':</label></strong>
					<span class="smalltext">', $txt['multiaccounts_password_desc'], '</span>
				</dt>
				<dd>
					<input type="password" id="smf_autov_pwmain" name="passwrd1" size="15" tabindex="', $context['tabindex']++, '">
					<span id="smf_autov_pwmain_div" style="display: none;">
						<img id="smf_autov_pwmain_img" src="', $settings['images_url'], '/icons/field_invalid.png" alt="*">
					</span>
				</dd>
				<dt>
					<strong><label for="smf_autov_pwverify">', $txt['verify_pass'], ':</label></strong>
				</dt>
				<dd>
					<input type="password" id="smf_autov_pwverify" name="passwrd2" size="15" tabindex="', $context['tabindex']++, '">
					<span id="smf_autov_pwverify_div" style="display: none;">
						<img id="smf_autov_pwverify_img" src="', $settings['images_url'], '/icons/field_invalid.png" alt="*">
					</span>
				</dd>
			</dl>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			<input type="submit" name="submit" value="', $txt['multiaccounts_create'], '" class="button" tabindex="', $context['tabindex']++, '">
		</div>
	</form>
	<script>
		var regTextStrings = {
			"username_valid": "', $txt['registration_username_available'], '",
			"username_invalid": "', $txt['registration_username_unavailable'], '",
			"username_check": "', $txt['registration_username_check'], '",
			"password_short": "', $txt['registration_password_short'], '",
			"password_reserved": "', $txt['registration_password_reserved'], '",
			"password_numbercase": "', $txt['registration_password_numbercase'], '",
			"password_no_match": "', $txt['registration_password_no_match'], '",
			"password_valid": "', $txt['registration_password_valid'], '"
		};
		var verificationHandle = new smfRegister("postForm", ', empty($modSettings['password_strength']) ? 0 : $modSettings['password_strength'], ', regTextStrings);
	</script>';
}

/**
 * Merge selection template.
 */
function template_manage_multiaccounts_merge()
{
	global $context, $settings, $scripturl, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['multiaccounts_manage'], !$context['user']['is_owner'] ? ' - &quot;' . $context['member']['name'] . '&quot;' : '', '
		</h3>
	</div>
	<div class="information">', $context['page_desc'], '</div>
	<form action="', $scripturl, '?action=profile;u=', $context['member']['id'], ';area=managemultiaccounts;sa=merge" method="post" accept-charset="', $context['character_set'], '">
		<div class="roundframe">
			<dl class="settings">';

	foreach ($context['merge_accounts'] as $account)
		echo '
				<dt>
					<input type="radio" name="parent" value="', $account['id'], '"> ', $account['name'],
					$account['id'] == $context['member']['id'] ? ' <strong class="smalltext">(' . $txt['multiaccounts_parent_account'] . ')</strong>' : '', '
				</dt>';

	echo '
			</dl>
			<input type="submit" name="submit" value="', $txt['multiaccounts_merge'], '" class="button" onclick="return confirm(\'', $txt['multiaccounts_confirm_merge'], '\');">
			<input type="hidden" name="subaccount" value="', $context['subaccount'], '">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</div>
	</form>';
}

/**
 * Split form template.
 */
function template_manage_multiaccounts_split()
{
	global $context, $settings, $scripturl, $modSettings, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['multiaccounts_manage'], !$context['user']['is_owner'] ? ' - &quot;' . $context['member']['name'] . '&quot;' : '', '
		</h3>
	</div>';

	// Error messages
	if (!empty($context['post_errors']))
	{
		echo '
	<div class="errorbox">
		<strong>', !empty($context['custom_error_title']) ? $context['custom_error_title'] : $txt['error_occurred'], '</strong>
		<ul>';

		foreach ($context['post_errors'] as $error)
			echo '
			<li>', isset($txt[$error]) ? $txt[$error] : $error, '</li>';

		echo '
		</ul>
	</div>';
	}

	echo '
	<div class="information">', $context['page_desc'], '</div>
	<form action="', $scripturl, '?action=profile;area=managemultiaccounts;sa=split;u=', $context['member']['id'], '" name="creator" id="creator" method="post" accept-charset="', $context['character_set'], '">
		<div class="roundframe">
			<dl class="settings">
				<dt><strong>', $txt['multiaccounts_account'], ':</strong></dt>
				<dd>
					<input type="hidden" name="subaccount" value="', $context['subaccount']['id'], '">
					<strong>', $context['subaccount']['name'], '</strong>
				</dd>
				<dt><strong><label for="email">', $txt['email'], ':</label></strong></dt>
				<dd>
					<input type="text" id="email" name="email" size="40" tabindex="', $context['tabindex']++, '" value="', !empty($context['form_email']) ? $context['form_email'] : '', '">
				</dd>
				<dt><strong><label for="smf_autov_pwmain">', $txt['choose_pass'], ':</label></strong></dt>
				<dd>
					<input type="password" id="smf_autov_pwmain" name="pwmain" size="15" tabindex="', $context['tabindex']++, '">
					<span id="smf_autov_pwmain_div" style="display: none;">
						<img id="smf_autov_pwmain_img" src="', $settings['images_url'], '/icons/field_invalid.png" alt="*">
					</span>
				</dd>
				<dt><strong><label for="smf_autov_pwverify">', $txt['verify_pass'], ':</label></strong></dt>
				<dd>
					<input type="password" id="smf_autov_pwverify" name="pwverify" size="15" tabindex="', $context['tabindex']++, '">
					<span id="smf_autov_pwverify_div" style="display: none;">
						<img id="smf_autov_pwverify_img" src="', $settings['images_url'], '/icons/field_invalid.png" alt="*">
					</span>
				</dd>
			</dl>
			<input type="submit" name="submit" value="', $txt['multiaccounts_split'], '" class="button">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</div>
	</form>
	<script>
		var regTextStrings = {
			"username_valid": "', $txt['registration_username_available'], '",
			"username_invalid": "', $txt['registration_username_unavailable'], '",
			"username_check": "', $txt['registration_username_check'], '",
			"password_short": "', $txt['registration_password_short'], '",
			"password_reserved": "', $txt['registration_password_reserved'], '",
			"password_numbercase": "', $txt['registration_password_numbercase'], '",
			"password_no_match": "', $txt['registration_password_no_match'], '",
			"password_valid": "', $txt['registration_password_valid'], '"
		};
		var verificationHandle = new smfRegister("creator", ', empty($modSettings['password_strength']) ? 0 : $modSettings['password_strength'], ', regTextStrings);
	</script>';
}

/**
 * Reassign parent form template.
 */
function template_manage_multiaccounts_reassign()
{
	global $context, $settings, $scripturl, $modSettings, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['multiaccounts_manage'], !$context['user']['is_owner'] ? ' - &quot;' . $context['member']['name'] . '&quot;' : '', '
		</h3>
	</div>';

	// Error messages
	if (!empty($context['post_errors']))
	{
		echo '
	<div class="errorbox">
		<strong>', !empty($context['custom_error_title']) ? $context['custom_error_title'] : $txt['error_occurred'], '</strong>
		<ul>';

		foreach ($context['post_errors'] as $error)
			echo '
			<li>', isset($txt[$error]) ? $txt[$error] : $error, '</li>';

		echo '
		</ul>
	</div>';
	}

	echo '
	<div class="information">', $context['page_desc'], '</div>
	<form action="', $scripturl, '?action=profile;u=', $context['member']['id'], ';area=managemultiaccounts;sa=reassign" name="creator" id="creator" method="post" accept-charset="', $context['character_set'], '">
		<div class="roundframe">
			<dl class="settings">
				<dt><strong>', $txt['multiaccounts_new_parent'], ':</strong></dt>
				<dd>
					<input type="hidden" name="subaccount" value="', $context['subaccount']['id'], '">
					<strong>', $context['subaccount']['name'], '</strong>
				</dd>
				<dt><strong><label for="smf_autov_pwmain">', $txt['choose_pass'], ':</label></strong></dt>
				<dd>
					<input type="password" id="smf_autov_pwmain" name="pwmain" size="15" tabindex="', $context['tabindex']++, '">
					<span id="smf_autov_pwmain_div" style="display: none;">
						<img id="smf_autov_pwmain_img" src="', $settings['images_url'], '/icons/field_invalid.png" alt="*">
					</span>
				</dd>
				<dt><strong><label for="smf_autov_pwverify">', $txt['verify_pass'], ':</label></strong></dt>
				<dd>
					<input type="password" id="smf_autov_pwverify" name="pwverify" size="15" tabindex="', $context['tabindex']++, '">
					<span id="smf_autov_pwverify_div" style="display: none;">
						<img id="smf_autov_pwverify_img" src="', $settings['images_url'], '/icons/field_invalid.png" alt="*">
					</span>
				</dd>
			</dl>
			<input type="submit" name="submit" value="', $txt['multiaccounts_reassign'], '" class="button">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</div>
	</form>
	<script>
		var regTextStrings = {
			"username_valid": "', $txt['registration_username_available'], '",
			"username_invalid": "', $txt['registration_username_unavailable'], '",
			"username_check": "', $txt['registration_username_check'], '",
			"password_short": "', $txt['registration_password_short'], '",
			"password_reserved": "', $txt['registration_password_reserved'], '",
			"password_numbercase": "', $txt['registration_password_numbercase'], '",
			"password_no_match": "', $txt['registration_password_no_match'], '",
			"password_valid": "', $txt['registration_password_valid'], '"
		};
		var verificationHandle = new smfRegister("creator", ', empty($modSettings['password_strength']) ? 0 : $modSettings['password_strength'], ', regTextStrings);
	</script>';
}

/**
 * Account switcher widget (output in the menu area via above_content).
 */
function template_multiaccounts_switcher_above()
{
	global $context, $txt, $scripturl;

	if (empty($context['multiaccounts_switcher']))
		return;

	echo '
	<div id="multiaccounts_switcher">
		<span class="multiaccounts_switch_label">', $txt['multiaccounts_switch_to'], '</span>
		<ul class="multiaccounts_switch_list">';

	foreach ($context['multiaccounts_switcher'] as $account)
		echo '
			<li><a href="', $account['href'], '">', $account['name'], '</a></li>';

	echo '
		</ul>
	</div>';
}
