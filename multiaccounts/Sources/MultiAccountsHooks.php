<?php

/**
 * Multi Accounts - Hook callbacks
 *
 * All hook callback functions for the Multi Accounts modification.
 * Replaces 30+ core file edits from the original SubAccounts mod.
 *
 * @package MultiAccounts
 * @author vbgamer45
 * @license BSD
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Register the switchmultiaccount action.
 *
 * Hook: integrate_actions
 */
function multiaccounts_actions(&$actionArray)
{
	$actionArray['switchmultiaccount'] = array('MultiAccounts.php', 'SwitchMultiAccount');
}

/**
 * Add the account switcher widget to the menu.
 *
 * Hook: integrate_menu_buttons
 */
function multiaccounts_menu_buttons(&$buttons)
{
	global $context, $txt, $scripturl, $user_info, $modSettings;

	if ($user_info['is_guest'] || empty($modSettings['enableMultiAccounts']))
		return;

	loadLanguage('MultiAccounts');

	if (empty($user_info['multiaccounts']))
		return;

	// Count other accounts (exclude current)
	$other_accounts = array();
	foreach ($user_info['multiaccounts'] as $id => $account)
	{
		if ($id != $user_info['id'])
			$other_accounts[$id] = $account;
	}

	if (empty($other_accounts))
		return;

	// Build sub-buttons for each linked account
	$sub_buttons = array();
	foreach ($other_accounts as $id => $account)
	{
		$sub_buttons['multiaccount_' . $id] = array(
			'title' => $account['name'],
			'href' => $scripturl . '?action=switchmultiaccount;subaccount=' . $id . ';' . $context['session_var'] . '=' . $context['session_id'],
			'show' => true,
		);
	}

	// Insert a "Switch Account" button before logout (or at the end)
	$new_buttons = array();
	foreach ($buttons as $key => $button)
	{
		if ($key === 'logout')
		{
			$new_buttons['multiaccounts'] = array(
				'title' => $txt['multiaccounts_switch_to'],
				'href' => $scripturl . '?action=profile;area=managemultiaccounts',
				'icon' => 'members',
				'show' => true,
				'sub_buttons' => $sub_buttons,
			);
		}
		$new_buttons[$key] = $button;
	}

	// If logout wasn't found (e.g. login_main_menu is off), just append
	if (!isset($new_buttons['multiaccounts']))
	{
		$new_buttons['multiaccounts'] = array(
			'title' => $txt['multiaccounts_switch_to'],
			'href' => $scripturl . '?action=profile;area=managemultiaccounts',
			'show' => true,
			'sub_buttons' => $sub_buttons,
		);
	}

	$buttons = $new_buttons;
}

/**
 * Add admin area for Multi Accounts settings.
 *
 * Hook: integrate_admin_areas
 */
function multiaccounts_admin_areas(&$admin_areas)
{
	global $txt;

	loadLanguage('MultiAccounts');

	$admin_areas['members']['areas']['multiaccounts'] = array(
		'label' => $txt['multiaccounts_admin_title'],
		'function' => 'MultiAccountsAdmin',
		'file' => 'MultiAccounts-Admin.php',
		'icon' => 'members',
		'permission' => array('moderate_forum'),
		'subsections' => array(
			'settings' => array($txt['settings']),
			'view' => array($txt['multiaccounts_view_all']),
		),
	);
}

/**
 * Add profile area for managing linked accounts.
 *
 * Hook: integrate_profile_areas
 */
function multiaccounts_profile_areas(&$profile_areas)
{
	global $txt, $modSettings;

	if (empty($modSettings['enableMultiAccounts']))
		return;

	loadLanguage('MultiAccounts');

	$profile_areas['info']['areas']['managemultiaccounts'] = array(
		'label' => $txt['multiaccounts_manage'],
		'file' => 'MultiAccounts.php',
		'function' => 'MultiAccountsMain',
		'icon' => 'members',
		'sc' => 'get',
		'permission' => array(
			'own' => array('multiaccounts_create_own'),
			'any' => array('multiaccounts_create_any'),
		),
	);
}

/**
 * Register permissions for multi accounts.
 *
 * Hook: integrate_load_permissions
 */
function multiaccounts_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$permissionGroups['membergroup'][] = 'multiaccounts';

	$permissionList['membergroup']['multiaccounts_create'] = array(true, 'multiaccounts');
	$permissionList['membergroup']['multiaccounts_delete'] = array(true, 'multiaccounts');
	$permissionList['membergroup']['multiaccounts_merge'] = array(true, 'multiaccounts');
	$permissionList['membergroup']['multiaccounts_split'] = array(true, 'multiaccounts');
}

/**
 * Block child accounts from logging in directly.
 *
 * Hook: integrate_validate_login
 */
function multiaccounts_validate_login($username, $password, $cookieTime)
{
	global $smcFunc, $modSettings;

	if (empty($modSettings['enableMultiAccounts']) || empty($username))
		return;

	// Look up the member by username
	$request = $smcFunc['db_query']('', '
		SELECT mem.id_member
		FROM {db_prefix}members AS mem
			INNER JOIN {db_prefix}multiaccounts AS ma ON (ma.id_member = mem.id_member)
		WHERE ' . ($smcFunc['db_case_sensitive'] ? 'LOWER(mem.member_name)' : 'mem.member_name') . ' = {string:username}
		LIMIT 1',
		array(
			'username' => $smcFunc['db_case_sensitive'] ? strtolower($username) : $username,
		)
	);

	$is_child = $smcFunc['db_num_rows']($request) > 0;
	$smcFunc['db_free_result']($request);

	if ($is_child)
		return 'retry';
}

/**
 * Load multi account data into user_info after user is loaded.
 *
 * Hook: integrate_user_info
 */
function multiaccounts_user_info()
{
	global $user_info, $smcFunc, $modSettings, $cookiename;

	if ($user_info['is_guest'] || empty($modSettings['enableMultiAccounts']))
		return;

	// Check if this user has a parent
	$request = $smcFunc['db_query']('', '
		SELECT id_parent
		FROM {db_prefix}multiaccounts
		WHERE id_member = {int:id_member}
		LIMIT 1',
		array(
			'id_member' => $user_info['id'],
		)
	);

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$user_info['id_parent'] = !empty($row) ? (int) $row['id_parent'] : 0;

	// Check if user is shareable
	$request = $smcFunc['db_query']('', '
		SELECT is_shareable
		FROM {db_prefix}members
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => $user_info['id'],
		)
	);
	$row2 = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
	$user_info['is_shareable'] = !empty($row2['is_shareable']);

	// Load the multiaccounts list
	$user_info['multiaccounts'] = array();

	// Determine who the parent is for loading the full account list
	$load_parent_id = $user_info['id'];

	// If this user is a child account, load the parent's list so they can switch back
	if (!empty($user_info['id_parent']))
		$load_parent_id = $user_info['id_parent'];

	// If this account is shareable, check the parent cookie to find who switched here
	if (!empty($user_info['is_shareable']))
	{
		$parent_cookie = $cookiename . '_parent';
		$id_parent = 0;

		if (isset($_COOKIE[$parent_cookie]))
		{
			$cookie_data = $smcFunc['json_decode']($_COOKIE[$parent_cookie], true);
			if (is_array($cookie_data) && !empty($cookie_data[0]) && !empty($cookie_data[1]))
			{
				$id_parent = (int) $cookie_data[0];

				// Validate the parent cookie hash
				$request = $smcFunc['db_query']('', '
					SELECT passwd, password_salt
					FROM {db_prefix}members
					WHERE id_member = {int:id_parent}',
					array(
						'id_parent' => $id_parent,
					)
				);

				if ($parent_row = $smcFunc['db_fetch_assoc']($request))
				{
					if ($cookie_data[1] !== hash_salt($parent_row['passwd'], $parent_row['password_salt']))
						$id_parent = 0;
				}
				else
					$id_parent = 0;

				$smcFunc['db_free_result']($request);
			}
		}
		elseif (isset($_SESSION['login_' . $parent_cookie]))
		{
			$cookie_data = $smcFunc['json_decode']($_SESSION['login_' . $parent_cookie], true);
			if (is_array($cookie_data) && !empty($cookie_data[0]))
				$id_parent = (int) $cookie_data[0];
		}

		if (!empty($id_parent))
		{
			$user_info['id_parent'] = $id_parent;
			$load_parent_id = $id_parent;
		}
	}

	// Load the linked accounts from the parent
	$subaccounts = cache_get_data('user_multiaccounts-' . $load_parent_id, 240);
	if ($subaccounts === null)
	{
		$subaccounts = multiaccounts_load_linked($load_parent_id);
		cache_put_data('user_multiaccounts-' . $load_parent_id, $subaccounts, 240);
	}

	$user_info['multiaccounts'] = $subaccounts;
}

/**
 * Load linked accounts for a given member ID.
 *
 * @param int $id_member The member to load linked accounts for
 * @return array Linked accounts data
 */
function multiaccounts_load_linked($id_member)
{
	global $smcFunc;

	$subaccounts = array();

	// First get all child accounts
	$request = $smcFunc['db_query']('', '
		SELECT sub.id_member, mem.real_name, mem.is_shareable
		FROM {db_prefix}multiaccounts AS sub
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = sub.id_member)
		WHERE sub.id_parent = {int:id_parent}',
		array(
			'id_parent' => $id_member,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$subaccounts[$row['id_member']] = array(
			'name' => $row['real_name'],
			'shareable' => $row['is_shareable'],
		);
	}
	$smcFunc['db_free_result']($request);

	// Also add the parent itself so switching back is possible
	$request = $smcFunc['db_query']('', '
		SELECT real_name
		FROM {db_prefix}members
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => $id_member,
		)
	);

	if ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$subaccounts[$id_member] = array(
			'name' => $row['real_name'],
			'shareable' => 0,
		);
	}
	$smcFunc['db_free_result']($request);

	return $subaccounts;
}

/**
 * Add id_parent column to member data queries.
 *
 * Hook: integrate_load_member_data
 */
function multiaccounts_load_member_data(&$select_columns, &$select_tables, $set)
{
	global $modSettings;

	if (empty($modSettings['enableMultiAccounts']))
		return;

	$select_columns .= ', IFNULL(ma.id_parent, 0) AS id_parent, mem.is_shareable';
	$select_tables .= ' LEFT JOIN {db_prefix}multiaccounts AS ma ON (ma.id_member = mem.id_member)';
}

/**
 * Add multi account data to member context.
 *
 * Hook: integrate_member_context
 */
function multiaccounts_member_context(&$data, $user, $display_custom_fields)
{
	global $user_profile, $modSettings;

	if (empty($modSettings['enableMultiAccounts']))
		return;

	$data['id_parent'] = !empty($user_profile[$user]['id_parent']) ? $user_profile[$user]['id_parent'] : 0;
	$data['is_shareable'] = !empty($user_profile[$user]['is_shareable']);
}

/**
 * Load CSS and language, inject account switcher widget, copy data to context.
 *
 * Hook: integrate_load_theme
 */
function multiaccounts_load_theme()
{
	global $context, $modSettings, $user_info, $smcFunc, $scripturl;

	if (empty($modSettings['enableMultiAccounts']))
		return;

	if (!$user_info['is_guest'])
	{
		loadLanguage('MultiAccounts');
		loadCSSFile('multiaccounts.css', array('default_theme' => true, 'minimize' => true));

		// Make multiaccounts data available in $context['user'] for templates
		if (!empty($user_info['multiaccounts']))
			$context['user']['multiaccounts'] = $user_info['multiaccounts'];
	}

	// Load linked accounts list for profile summary display
	if (!empty($modSettings['multiaccountsShowInProfile']) && isset($_REQUEST['action']) && $_REQUEST['action'] === 'profile')
	{
		$memID = !empty($_REQUEST['u']) ? (int) $_REQUEST['u'] : $user_info['id'];

		$request = $smcFunc['db_query']('', '
			SELECT sub.id_member, mem.real_name
			FROM {db_prefix}multiaccounts AS sub
				INNER JOIN {db_prefix}members AS mem ON (mem.id_member = sub.id_member)
			WHERE sub.id_parent = {int:id_parent}',
			array(
				'id_parent' => $memID,
			)
		);

		$list = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$list[] = array(
				'id' => $row['id_member'],
				'name' => $row['real_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			);
		}
		$smcFunc['db_free_result']($request);

		if (!empty($list))
			$context['member']['multiaccounts_list'] = $list;
	}
}

/**
 * Add option to include linked accounts in search.
 *
 * Hook: integrate_search_params
 */
function multiaccounts_search_params(&$search_params)
{
	global $user_info, $modSettings;

	if (empty($modSettings['enableMultiAccounts']) || $user_info['is_guest'])
		return;

	if (!empty($user_info['multiaccounts']) && !empty($_POST['multiaccounts_search']))
	{
		// Add all linked account IDs to the member search
		$member_ids = array_keys($user_info['multiaccounts']);
		if (!empty($search_params['userspec']))
			$search_params['userspec'] .= ',' . implode(',', $member_ids);
	}
}

/**
 * Sync email changes to child accounts.
 *
 * Hook: integrate_change_member_data
 */
function multiaccounts_change_member_data($member_names, $var, &$data, &$knownInts, &$knownFloats)
{
	global $smcFunc, $modSettings;

	if (empty($modSettings['enableMultiAccounts']) || $var !== 'email_address')
		return;

	// Look up member IDs from the usernames
	$request = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}members
		WHERE member_name IN ({array_string:names})',
		array(
			'names' => $member_names,
		)
	);

	$parent_ids = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$parent_ids[] = $row['id_member'];
	$smcFunc['db_free_result']($request);

	if (empty($parent_ids))
		return;

	// Get child accounts that are not shared by another parent
	$request = $smcFunc['db_query']('', '
		SELECT sub.id_member
		FROM {db_prefix}multiaccounts AS sub
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = sub.id_member)
		WHERE sub.id_parent IN ({array_int:parent_ids})
			AND (mem.is_shareable = 0 OR mem.is_shareable IN ({array_int:parent_ids}))',
		array(
			'parent_ids' => $parent_ids,
		)
	);

	$child_ids = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$child_ids[] = $row['id_member'];
	$smcFunc['db_free_result']($request);

	// Sync directly via query to avoid recursive updateMemberData call
	if (!empty($child_ids))
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET email_address = {string:email}
			WHERE id_member IN ({array_int:ids})',
			array(
				'email' => $data,
				'ids' => $child_ids,
			)
		);
	}
}

/**
 * Override poster if posting as a linked account.
 *
 * Hook: integrate_create_post
 */
function multiaccounts_create_post(&$msgOptions, &$topicOptions, &$posterOptions)
{
	global $user_info, $modSettings;

	if (empty($modSettings['enableMultiAccounts']) || empty($_POST['multiaccount_post_as']))
		return;

	$post_as = (int) $_POST['multiaccount_post_as'];

	// Validate this is a valid linked account
	if ($post_as > 0 && $post_as != $user_info['id'] && !empty($user_info['multiaccounts'][$post_as]))
	{
		$posterOptions['id'] = $post_as;
		$posterOptions['name'] = $user_info['multiaccounts'][$post_as]['name'];
	}
}

/**
 * Override poster when modifying a post.
 *
 * Hook: integrate_modify_post
 */
function multiaccounts_modify_post(&$messages_columns, &$update_parameters, &$msgOptions, &$topicOptions, &$posterOptions)
{
	global $user_info, $modSettings;

	if (empty($modSettings['enableMultiAccounts']) || empty($_POST['multiaccount_post_as']))
		return;

	$post_as = (int) $_POST['multiaccount_post_as'];

	// Validate this is a valid linked account
	if ($post_as > 0 && $post_as != $user_info['id'] && !empty($user_info['multiaccounts'][$post_as]))
	{
		$messages_columns['id_member'] = $post_as;
		$messages_columns['poster_name'] = $user_info['multiaccounts'][$post_as]['name'];
	}
}

/**
 * Add mod credits.
 *
 * Hook: integrate_credits
 */
function multiaccounts_credits()
{
	global $context;

	$context['copyrights']['mods'][] = '<a href="https://www.smfhacks.com" target="_blank">Multi Accounts</a> &copy; vbgamer45';
}

/**
 * Exclude account switches from online stats.
 *
 * Hook: integrate_pre_log_stats
 */
function multiaccounts_pre_log_stats(&$no_stat_actions)
{
	$no_stat_actions[] = 'switchmultiaccount';
}
