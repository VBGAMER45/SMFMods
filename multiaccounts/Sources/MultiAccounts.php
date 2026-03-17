<?php

/**
 * Multi Accounts - Core Operations
 *
 * Profile area for managing linked accounts: browse, create, delete,
 * merge, split, reassign parent, share, and switch.
 *
 * @package MultiAccounts
 * @author vbgamer45
 * @license BSD
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Dispatcher for the profile area.
 *
 * @param int $memID The member ID
 */
function MultiAccountsMain($memID)
{
	global $context, $txt, $user_info, $cur_profile;

	loadLanguage('MultiAccounts');
	loadLanguage('Login');
	loadTemplate('MultiAccounts');

	$context['page_title'] = $txt['multiaccounts_manage'];

	// Determine permissions
	$context['can_create'] = allowedTo('multiaccounts_create_any') || ($memID == $user_info['id'] && allowedTo('multiaccounts_create_own'));
	$context['can_delete'] = allowedTo('multiaccounts_delete_any') || ($memID == $user_info['id'] && allowedTo('multiaccounts_delete_own'));
	$context['can_merge'] = allowedTo('multiaccounts_merge_any') || ($memID == $user_info['id'] && allowedTo('multiaccounts_merge_own'));
	$context['can_split'] = allowedTo('multiaccounts_split_any') || ($memID == $user_info['id'] && allowedTo('multiaccounts_split_own'));
	$context['can_reassign'] = $context['can_merge'];

	$subActions = array(
		'browse' => 'MultiAccountsBrowse',
		'create' => 'MultiAccountsCreate',
		'delete' => 'MultiAccountsDelete',
		'merge' => 'MultiAccountsMerge',
		'split' => 'MultiAccountsSplit',
		'reassign' => 'MultiAccountsParent',
		'share' => 'MultiAccountsShare',
	);

	$sa = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'browse';
	$subActions[$sa]($memID);
}

/**
 * Browse/list all linked accounts.
 *
 * @param int $memID The member ID
 */
function MultiAccountsBrowse($memID)
{
	global $context, $txt, $cur_profile, $smcFunc, $memberContext;

	$context['sub_template'] = 'manage_multiaccounts';
	$context['page_desc'] = $txt['multiaccounts_desc'];

	$context['multiaccounts_list'] = array();

	// Load the linked accounts for this profile
	$request = $smcFunc['db_query']('', '
		SELECT sub.id_member, mem.real_name, mem.is_shareable, mem.posts,
			mem.instant_messages, mem.unread_messages
		FROM {db_prefix}multiaccounts AS sub
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = sub.id_member)
		WHERE sub.id_parent = {int:id_parent}',
		array(
			'id_parent' => $memID,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		loadMemberData($row['id_member']);
		if (loadMemberContext($row['id_member']))
		{
			$context['multiaccounts_list'][] = array(
				'id' => $row['id_member'],
				'name' => $memberContext[$row['id_member']]['name'],
				'href' => $memberContext[$row['id_member']]['href'],
				'group' => !empty($memberContext[$row['id_member']]['group']) ? $memberContext[$row['id_member']]['group'] : $memberContext[$row['id_member']]['post_group'],
				'icons' => $memberContext[$row['id_member']]['group_icons'],
				'posts' => $memberContext[$row['id_member']]['posts'],
				'messages' => array(
					'total' => $row['instant_messages'],
					'unread' => $row['unread_messages'],
				),
				'website' => $memberContext[$row['id_member']]['website'],
				'permissions' => array(
					'can_delete' => $context['can_delete'],
					'can_merge' => $context['can_merge'] && (empty($row['is_shareable']) || $row['is_shareable'] == $cur_profile['id_member']),
					'can_split' => $context['can_split'] && (empty($row['is_shareable']) || $row['is_shareable'] == $cur_profile['id_member']),
					'can_reassign' => $context['can_reassign'] && (empty($row['is_shareable']) || $row['is_shareable'] == $cur_profile['id_member']),
					'can_share' => $context['can_create'] && (empty($row['is_shareable']) || $row['is_shareable'] == $cur_profile['id_member']),
				),
				'is_shared' => !empty($row['is_shareable']),
			);
		}
	}
	$smcFunc['db_free_result']($request);
}

/**
 * Create a new linked account or link an existing one.
 *
 * @param int $memID The member ID
 */
function MultiAccountsCreate($memID)
{
	global $context, $smcFunc, $txt, $sourcedir, $user_info, $cur_profile, $modSettings;

	$context['sub_template'] = 'manage_multiaccounts_create';

	if (empty($cur_profile['additional_groups']))
		$user_groups = array($cur_profile['id_group'], $cur_profile['id_post_group']);
	else
		$user_groups = array_merge(
			array($cur_profile['id_group'], $cur_profile['id_post_group']),
			explode(',', $cur_profile['additional_groups'])
		);

	$context['member']['is_admin'] = in_array(1, $user_groups);

	// Handle share toggle via GET
	if (isset($_REQUEST['make_shared']))
	{
		MultiAccountsShare($memID);
		return;
	}

	if (!isset($_POST['submit']))
		return;

	// Make sure they came from *somewhere*, have a session.
	checkSession();

	foreach ($_POST as $key => $value)
	{
		if (!is_array($_POST[$key]))
			$_POST[$key] = htmltrim__recursive(str_replace(array("\n", "\r"), '', $_POST[$key]));
	}

	$username = !empty($_POST['username']) ? $_POST['username'] : '';

	require_once($sourcedir . '/Subs-Members.php');

	// Check if this is an existing member
	$request = $smcFunc['db_query']('', '
		SELECT mem.id_member, mem.passwd, mem.member_name, IFNULL(sub.id_parent, 0) as is_child, mem.is_shareable
		FROM {db_prefix}members AS mem
			LEFT JOIN {db_prefix}multiaccounts AS sub ON (sub.id_member = mem.id_member)
		WHERE ' . ($smcFunc['db_case_sensitive'] ? 'LOWER(member_name)' : 'member_name') . ' = {string:user_name}
		LIMIT 1',
		array(
			'user_name' => $smcFunc['db_case_sensitive'] ? strtolower($username) : $username,
		)
	);

	if ($member = $smcFunc['db_fetch_assoc']($request))
	{
		$smcFunc['db_free_result']($request);

		// Can't link to yourself or an account already linked elsewhere
		if ($member['id_member'] == $cur_profile['id_member'] || (!empty($member['is_child']) && (empty($member['is_shareable']) || $member['is_child'] == $cur_profile['id_member'])))
		{
			loadLanguage('Errors');
			$context['custom_error_title'] = $txt['multiaccounts_error'];
			$context['post_errors'][] = 'name_taken';
			return;
		}

		// Verify the password using SMF 2.1 bcrypt
		$password = !empty($_POST['passwrd1']) ? un_htmlspecialchars($_POST['passwrd1']) : '';

		if (hash_verify_password($member['member_name'], $password, $member['passwd']))
		{
			// Get existing child accounts of the member being linked
			$request = $smcFunc['db_query']('', '
				SELECT sub.id_member, mem.is_shareable
				FROM {db_prefix}multiaccounts AS sub
					INNER JOIN {db_prefix}members AS mem ON (mem.id_member = sub.id_member)
				WHERE sub.id_parent = {int:parent}',
				array(
					'parent' => $member['id_member'],
				)
			);

			$changeUsers = array();
			$sharedUsers = array();
			$createdSharedUsers = array();

			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				if (!empty($row['is_shareable']) && $row['is_shareable'] != $member['id_member'])
					$sharedUsers[] = $row['id_member'];
				elseif (!empty($row['is_shareable']) && $row['is_shareable'] == $member['id_member'])
					$createdSharedUsers[] = $row['id_member'];

				$changeUsers[] = $row['id_member'];
			}
			$smcFunc['db_free_result']($request);

			// Remove duplicates that would violate unique index
			if (!empty($changeUsers))
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}multiaccounts
					WHERE id_member IN ({array_int:changeusers})
						AND id_parent = {int:new_parent}',
					array(
						'changeusers' => $changeUsers,
						'new_parent' => $cur_profile['id_member'],
					)
				);

			// Re-parent existing child accounts
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}multiaccounts
				SET id_parent = {int:new_parent}
				WHERE id_parent = {int:old_parent}',
				array(
					'new_parent' => $cur_profile['id_member'],
					'old_parent' => $member['id_member'],
				)
			);

			$changeUsers[] = $member['id_member'];

			// Sync email for non-shared accounts
			updateMemberData(array_diff($changeUsers, $sharedUsers), array('email_address' => $cur_profile['email_address']));

			// Transfer shared account ownership
			if (!empty($createdSharedUsers))
				updateMemberData($createdSharedUsers, array('is_shareable' => $cur_profile['id_member']));

			// Add the member to the multiaccounts table
			$smcFunc['db_insert']('ignore',
				'{db_prefix}multiaccounts',
				array('id_member' => 'int', 'id_parent' => 'int'),
				array($member['id_member'], $cur_profile['id_member']),
				array('id_parent', 'id_member')
			);

			cache_put_data('user_multiaccounts-' . $cur_profile['id_member'], null, 240);

			redirectexit('action=profile;area=managemultiaccounts;u=' . $cur_profile['id_member']);
		}
		else
		{
			loadLanguage('Errors');
			$context['custom_error_title'] = $txt['multiaccounts_error'];
			$context['post_errors'][] = 'bad_password';
			return;
		}
	}

	$smcFunc['db_free_result']($request);

	// Not an existing member - create a new account
	$regOptions = array(
		'interface' => '',
		'username' => $username,
		'email' => substr(preg_replace('/\W/', '', md5(rand())), 0, 4) . '@' . substr(preg_replace('/\W/', '', md5(rand())), 0, 5) . '.com',
		'password' => !empty($_POST['passwrd1']) ? un_htmlspecialchars($_POST['passwrd1']) : '',
		'password_check' => !empty($_POST['passwrd2']) ? un_htmlspecialchars($_POST['passwrd2']) : '',
		'check_reserved_name' => true,
		'check_password_strength' => true,
		'check_email_ban' => false,
		'send_welcome_email' => false,
		'require' => 'nothing',
		'theme_vars' => array(),
		'memberGroup' => !empty($modSettings['multiaccountsInheritParentGroup']) ? $cur_profile['id_group'] : 0,
		'extra_register_vars' => array(
			'email_address' => $cur_profile['email_address'],
			'warning' => $cur_profile['warning'],
			'time_offset' => $cur_profile['time_offset'],
			'lngfile' => $cur_profile['lngfile'],
		),
	);

	if (!empty($cur_profile['options']))
	{
		foreach ($cur_profile['options'] as $var => $value)
			$regOptions['theme_vars'][$var] = $value;
	}

	$memberID = registerMember($regOptions, true);

	// Was there an error?
	if (is_array($memberID))
	{
		$context['custom_error_title'] = $txt['multiaccounts_error'];
		$context['post_errors'] = $memberID;
		return;
	}

	// Add to multiaccounts table
	$smcFunc['db_insert']('ignore',
		'{db_prefix}multiaccounts',
		array('id_member' => 'int', 'id_parent' => 'int'),
		array($memberID, $cur_profile['id_member']),
		array('id_parent', 'id_member')
	);

	cache_put_data('user_multiaccounts-' . $cur_profile['id_member'], null, 240);

	redirectexit('action=profile;area=managemultiaccounts;u=' . $cur_profile['id_member']);
}

/**
 * Delete a linked account and transfer posts to parent.
 *
 * @param int $memID The member ID
 */
function MultiAccountsDelete($memID)
{
	global $sourcedir, $modSettings, $user_info, $smcFunc, $txt, $context, $cur_profile;

	checkSession('get');

	if (empty($_GET['subaccount']))
	{
		$context['custom_error_title'] = $txt['multiaccounts_error'];
		$context['post_errors'][] = $txt['multiaccounts_not_selected'];
		return MultiAccountsBrowse($memID);
	}

	$subaccount = (int) $_GET['subaccount'];

	// Verify the account belongs to this parent
	$request = $smcFunc['db_query']('', '
		SELECT id_parent
		FROM {db_prefix}multiaccounts
		WHERE id_member = {int:id_member}
			AND id_parent = {int:id_parent}',
		array(
			'id_member' => $subaccount,
			'id_parent' => $memID,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('cannot_multiaccounts_delete', false);
	}
	$smcFunc['db_free_result']($request);

	// Get their info
	$request = $smcFunc['db_query']('', '
		SELECT id_member, member_name, is_shareable,
			CASE WHEN id_group = {int:admin_group} OR FIND_IN_SET({int:admin_group}, additional_groups) != 0 THEN 1 ELSE 0 END AS is_admin
		FROM {db_prefix}members
		WHERE id_member = {int:user}',
		array(
			'user' => $subaccount,
			'admin_group' => 1,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($row['is_admin']) && !allowedTo('admin_forum'))
		fatal_lang_error('cannot_multiaccounts_delete', false);

	// If shared and not owned by this parent, just unlink
	if (!empty($row['is_shareable']) && $row['is_shareable'] != $cur_profile['id_member'])
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}multiaccounts
			WHERE id_member = {int:user}
				AND id_parent = {int:parent}',
			array(
				'user' => $subaccount,
				'parent' => $cur_profile['id_member'],
			)
		);

		cache_put_data('user_multiaccounts-' . $cur_profile['id_member'], null, 240);
		redirectexit('action=profile;area=managemultiaccounts;u=' . $cur_profile['id_member']);
	}

	// Log the action
	if (!empty($modSettings['modlog_enabled']))
	{
		require_once($sourcedir . '/Logging.php');
		logAction('delete_multiaccount', array('member' => $row['id_member'], 'name' => $row['member_name'], 'parent' => $cur_profile['member_name']));
	}

	// Remove from multiaccounts table (all references)
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}multiaccounts
		WHERE id_member = {int:user}',
		array(
			'user' => $subaccount,
		)
	);

	// Transfer posts to parent
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}messages
		SET id_member = {int:parent_id}, poster_name = {string:parent_name}
		WHERE id_member = {int:user}',
		array(
			'parent_id' => $cur_profile['id_member'],
			'parent_name' => $cur_profile['real_name'],
			'user' => $subaccount,
		)
	);
	$messageCount = $smcFunc['db_affected_rows']();

	// Transfer polls
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}polls
		SET id_member = {int:parent_id}, poster_name = {string:parent_name}
		WHERE id_member = {int:user}',
		array(
			'parent_id' => $cur_profile['id_member'],
			'parent_name' => $cur_profile['real_name'],
			'user' => $subaccount,
		)
	);

	// Transfer topic ownership
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}topics
		SET id_member_started = {int:parent_id}
		WHERE id_member_started = {int:user}',
		array(
			'parent_id' => $cur_profile['id_member'],
			'user' => $subaccount,
		)
	);
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}topics
		SET id_member_updated = {int:parent_id}
		WHERE id_member_updated = {int:user}',
		array(
			'parent_id' => $cur_profile['id_member'],
			'user' => $subaccount,
		)
	);

	// Transfer log actions
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}log_actions
		SET id_member = {int:parent_id}
		WHERE id_member = {int:user}',
		array(
			'parent_id' => $cur_profile['id_member'],
			'user' => $subaccount,
		)
	);

	// Delete PMs
	require_once($sourcedir . '/PersonalMessage.php');
	deleteMessages(null, null, $subaccount);

	// Transfer PM senders
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}personal_messages
		SET id_member_from = {int:parent_id}
		WHERE id_member_from = {int:user}',
		array(
			'parent_id' => $cur_profile['id_member'],
			'user' => $subaccount,
		)
	);

	// Remove avatar
	require_once($sourcedir . '/ManageAttachments.php');
	removeAttachments(array('id_member' => $subaccount));

	// Transfer attachments
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}attachments
		SET id_member = {int:parent_id}
		WHERE id_member = {int:user}',
		array(
			'parent_id' => $cur_profile['id_member'],
			'user' => $subaccount,
		)
	);

	// Use SMF's built-in member deletion for the rest
	require_once($sourcedir . '/Subs-Members.php');
	deleteMembers($subaccount);

	updateStats('member');

	if (!empty($messageCount))
		updateMemberData($cur_profile['id_member'], array('posts' => 'posts + ' . $messageCount));

	cache_put_data('user_multiaccounts-' . $cur_profile['id_member'], null, 240);

	redirectexit('action=profile;area=managemultiaccounts;u=' . $cur_profile['id_member']);
}

/**
 * Merge a linked account into a target account.
 *
 * @param int $memID The member ID
 */
function MultiAccountsMerge($memID)
{
	global $sourcedir, $modSettings, $user_info, $smcFunc, $txt, $context, $cur_profile;

	if (empty($_REQUEST['subaccount']))
	{
		$context['custom_error_title'] = $txt['multiaccounts_error'];
		$context['post_errors'][] = $txt['multiaccounts_not_selected'];
		return MultiAccountsBrowse($memID);
	}

	$subaccount = (int) $_REQUEST['subaccount'];

	// Verify ownership
	$request = $smcFunc['db_query']('', '
		SELECT id_parent
		FROM {db_prefix}multiaccounts
		WHERE id_member = {int:id_member}
			AND id_parent = {int:id_parent}',
		array(
			'id_member' => $subaccount,
			'id_parent' => $memID,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('cannot_multiaccounts_merge', false);
	}
	$smcFunc['db_free_result']($request);

	// Check shared status
	$request = $smcFunc['db_query']('', '
		SELECT is_shareable
		FROM {db_prefix}members
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => $subaccount,
		)
	);
	$share_row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($share_row['is_shareable']) && $cur_profile['id_member'] != $share_row['is_shareable'])
		fatal_lang_error('cannot_multiaccounts_merge_shared', false);

	// Build list of accounts for the template
	$context['merge_accounts'] = array();
	$context['merge_accounts'][$cur_profile['id_member']] = array('id' => $cur_profile['id_member'], 'name' => $cur_profile['real_name']);

	// Load all linked accounts
	$request = $smcFunc['db_query']('', '
		SELECT sub.id_member, mem.real_name
		FROM {db_prefix}multiaccounts AS sub
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = sub.id_member)
		WHERE sub.id_parent = {int:id_parent}',
		array(
			'id_parent' => $memID,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['merge_accounts'][$row['id_member']] = array('id' => $row['id_member'], 'name' => $row['real_name']);
	$smcFunc['db_free_result']($request);

	// Show the template if not submitting
	if (!isset($_POST['submit']))
	{
		$context['sub_template'] = 'manage_multiaccounts_merge';
		$context['subaccount'] = $subaccount;
		$context['page_desc'] = sprintf($txt['multiaccounts_merge_desc'], $context['merge_accounts'][$subaccount]['name']);

		// Don't merge with itself
		unset($context['merge_accounts'][$subaccount]);
		return;
	}

	checkSession();

	if (empty($_POST['parent']) || !isset($context['merge_accounts'][$_POST['parent']]))
	{
		$context['custom_error_title'] = $txt['multiaccounts_error'];
		$context['post_errors'][] = $txt['multiaccounts_not_selected'];
		return MultiAccountsBrowse($memID);
	}

	$parentAccount = (int) $_POST['parent'];

	// Get info for logging
	$request = $smcFunc['db_query']('', '
		SELECT id_member, member_name,
			CASE WHEN id_group = {int:admin_group} OR FIND_IN_SET({int:admin_group}, additional_groups) != 0 THEN 1 ELSE 0 END AS is_admin
		FROM {db_prefix}members
		WHERE id_member = {int:user}',
		array(
			'user' => $subaccount,
			'admin_group' => 1,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($row['is_admin']) && !allowedTo('admin_forum'))
		fatal_lang_error('cannot_multiaccounts_merge', false);

	// Log the action
	if (!empty($modSettings['modlog_enabled']))
	{
		require_once($sourcedir . '/Logging.php');
		logAction('merge_multiaccount', array('member' => $row['id_member'], 'name' => $row['member_name'], 'target' => $context['merge_accounts'][$parentAccount]['name']));
	}

	// Remove from multiaccounts table
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}multiaccounts
		WHERE id_member = {int:user}',
		array(
			'user' => $subaccount,
		)
	);

	// Transfer all posts
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}messages
		SET id_member = {int:parent_id}
		WHERE id_member = {int:user}',
		array(
			'parent_id' => $parentAccount,
			'user' => $subaccount,
		)
	);
	$messageCount = $smcFunc['db_affected_rows']();

	// Transfer polls
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}polls
		SET id_member = {int:parent_id}
		WHERE id_member = {int:user}',
		array(
			'parent_id' => $parentAccount,
			'user' => $subaccount,
		)
	);

	// Transfer topic ownership
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}topics
		SET id_member_started = {int:parent_id}
		WHERE id_member_started = {int:user}',
		array(
			'parent_id' => $parentAccount,
			'user' => $subaccount,
		)
	);
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}topics
		SET id_member_updated = {int:parent_id}
		WHERE id_member_updated = {int:user}',
		array(
			'parent_id' => $parentAccount,
			'user' => $subaccount,
		)
	);

	// Transfer log actions
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}log_actions
		SET id_member = {int:parent_id}
		WHERE id_member = {int:user}',
		array(
			'parent_id' => $parentAccount,
			'user' => $subaccount,
		)
	);

	// Transfer PM recipients
	$smcFunc['db_query']('', '
		UPDATE IGNORE {db_prefix}pm_recipients
		SET id_member = {int:parent_id}
		WHERE id_member = {int:user}',
		array(
			'parent_id' => $parentAccount,
			'user' => $subaccount,
		)
	);

	// Transfer PM senders
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}personal_messages
		SET id_member_from = {int:parent_id}
		WHERE id_member_from = {int:user}',
		array(
			'parent_id' => $parentAccount,
			'user' => $subaccount,
		)
	);

	// Transfer attachments
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}attachments
		SET id_member = {int:parent_id}
		WHERE id_member = {int:user}',
		array(
			'parent_id' => $parentAccount,
			'user' => $subaccount,
		)
	);

	// Transfer poll votes
	$smcFunc['db_query']('', '
		UPDATE IGNORE {db_prefix}log_polls
		SET id_member = {int:parent_id}
		WHERE id_member = {int:user}',
		array(
			'parent_id' => $parentAccount,
			'user' => $subaccount,
		)
	);

	// Remove avatar
	require_once($sourcedir . '/ManageAttachments.php');
	removeAttachments(array('id_member' => $subaccount));

	// Delete the member using SMF's built-in function
	require_once($sourcedir . '/Subs-Members.php');
	deleteMembers($subaccount);

	updateStats('member');

	if (!empty($messageCount))
		updateMemberData($parentAccount, array('posts' => 'posts + ' . $messageCount));

	cache_put_data('user_multiaccounts-' . $cur_profile['id_member'], null, 240);

	redirectexit('action=profile;area=managemultiaccounts;u=' . $cur_profile['id_member']);
}

/**
 * Split a linked account into an independent account.
 *
 * @param int $memID The member ID
 */
function MultiAccountsSplit($memID)
{
	global $context, $smcFunc, $user_info, $txt, $cur_profile, $sourcedir, $user_profile;

	if (empty($_REQUEST['subaccount']))
	{
		$context['custom_error_title'] = $txt['multiaccounts_error'];
		$context['post_errors'][] = $txt['multiaccounts_not_selected'];
		return MultiAccountsBrowse($memID);
	}

	$subaccount = (int) $_REQUEST['subaccount'];

	// Verify ownership
	$request = $smcFunc['db_query']('', '
		SELECT id_parent
		FROM {db_prefix}multiaccounts
		WHERE id_member = {int:id_member}
			AND id_parent = {int:id_parent}',
		array(
			'id_member' => $subaccount,
			'id_parent' => $memID,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('cannot_multiaccounts_split', false);
	}
	$smcFunc['db_free_result']($request);

	// Check shared status
	$request = $smcFunc['db_query']('', '
		SELECT is_shareable, real_name
		FROM {db_prefix}members
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => $subaccount,
		)
	);
	$sub_info = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($sub_info['is_shareable']) && $cur_profile['id_member'] != $sub_info['is_shareable'])
		fatal_lang_error('cannot_multiaccounts_split_shared', false);

	if (empty($_POST['submit']))
	{
		$context['sub_template'] = 'manage_multiaccounts_split';
		$context['page_desc'] = $txt['multiaccounts_split_desc'];
		$context['subaccount'] = array(
			'id' => $subaccount,
			'name' => $sub_info['real_name'],
		);
		return;
	}

	// Process the split
	$_POST = htmltrim__recursive($_POST);
	$_POST = htmlspecialchars__recursive($_POST);

	require_once($sourcedir . '/Subs-Auth.php');

	$context['post_errors'] = array();

	// Validate password
	$passwordErrors = validatePassword($_POST['pwmain'], $sub_info['real_name']);
	if ($passwordErrors != null)
		$context['post_errors'][] = 'password_' . $passwordErrors;

	// Validate email
	$emailErrors = profileValidateEmail($_POST['email']);
	if ($emailErrors !== true)
		$context['post_errors'][] = $emailErrors;

	if ($_POST['pwmain'] != $_POST['pwverify'])
		$context['post_errors'][] = $txt['registration_password_no_match'];

	if (!empty($context['post_errors']))
	{
		loadLanguage('Errors');
		$context['custom_error_title'] = $txt['multiaccounts_error'];
		$context['form_email'] = $_POST['email'];
		$context['sub_template'] = 'manage_multiaccounts_split';
		$context['page_desc'] = $txt['multiaccounts_split_desc'];
		$context['subaccount'] = array(
			'id' => $subaccount,
			'name' => $sub_info['real_name'],
		);
		return;
	}

	checkSession();

	// Update the member with new password and email
	loadMemberData($subaccount, false, 'minimal');
	updateMemberData($subaccount, array(
		'email_address' => $_POST['email'],
		'is_shareable' => 0,
		'passwd' => hash_password($user_profile[$subaccount]['member_name'], un_htmlspecialchars($_POST['pwmain'])),
	));

	// Remove from multiaccounts table
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}multiaccounts
		WHERE id_member = {int:user}',
		array(
			'user' => $subaccount,
		)
	);

	cache_put_data('user_multiaccounts-' . $cur_profile['id_member'], null, 240);

	redirectexit('action=profile;area=managemultiaccounts;u=' . $cur_profile['id_member']);
}

/**
 * Reassign the parent of all linked accounts.
 *
 * @param int $memID The member ID
 */
function MultiAccountsParent($memID)
{
	global $context, $txt, $cur_profile, $smcFunc, $sourcedir, $user_profile;

	if (empty($_REQUEST['subaccount']))
	{
		$context['custom_error_title'] = $txt['multiaccounts_error'];
		$context['post_errors'][] = $txt['multiaccounts_not_selected'];
		return MultiAccountsBrowse($memID);
	}

	$subaccount = (int) $_REQUEST['subaccount'];

	// Verify ownership
	$request = $smcFunc['db_query']('', '
		SELECT id_parent
		FROM {db_prefix}multiaccounts
		WHERE id_member = {int:id_member}
			AND id_parent = {int:id_parent}',
		array(
			'id_member' => $subaccount,
			'id_parent' => $memID,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('cannot_multiaccounts_reassign', false);
	}
	$smcFunc['db_free_result']($request);

	// Check shared status
	$request = $smcFunc['db_query']('', '
		SELECT is_shareable, real_name
		FROM {db_prefix}members
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => $subaccount,
		)
	);
	$sub_info = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($sub_info['is_shareable']) && $cur_profile['id_member'] != $sub_info['is_shareable'])
		fatal_lang_error('cannot_multiaccounts_reassign_shared', false);

	// Validate password if submitting
	$passwordError = null;
	if (!empty($_POST['submit']))
	{
		require_once($sourcedir . '/Subs-Auth.php');
		$_POST['pwmain'] = htmltrim__recursive(str_replace(array("\n", "\r"), '', $_POST['pwmain']));
		$_POST['pwverify'] = htmltrim__recursive(str_replace(array("\n", "\r"), '', $_POST['pwverify']));
		$passwordError = validatePassword($_POST['pwmain'], $sub_info['real_name']);
		$passwordError = $passwordError != null ? 'password_' . $passwordError : null;
		$passwordError = $_POST['pwmain'] != $_POST['pwverify'] ? 'bad_new_password' : $passwordError;
	}

	if (empty($_POST['submit']) || $passwordError != null)
	{
		if ($passwordError != null)
		{
			loadLanguage('Errors');
			$context['custom_error_title'] = $txt['multiaccounts_error'];
			$context['post_errors'][] = $txt['profile_error_' . $passwordError];
		}
		$context['sub_template'] = 'manage_multiaccounts_reassign';
		$context['page_desc'] = $txt['multiaccounts_reassign_desc'];
		$context['subaccount'] = array('id' => $subaccount, 'name' => $sub_info['real_name']);
		return;
	}

	checkSession('post');

	// Step 1: Remove the new parent from being a child
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}multiaccounts
		WHERE id_member = {int:user}',
		array(
			'user' => $subaccount,
		)
	);

	// Step 2: Re-parent all children to the new parent
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}multiaccounts
		SET id_parent = {int:new_parent}
		WHERE id_parent = {int:old_parent}',
		array(
			'new_parent' => $subaccount,
			'old_parent' => $cur_profile['id_member'],
		)
	);

	// Step 3: Add old parent as a child of new parent
	$smcFunc['db_insert']('ignore',
		'{db_prefix}multiaccounts',
		array('id_member' => 'int', 'id_parent' => 'int'),
		array($cur_profile['id_member'], $subaccount),
		array('id_parent', 'id_member')
	);

	// Update the new parent's password
	loadMemberData($subaccount, false, 'minimal');
	updateMemberData($subaccount, array(
		'is_shareable' => 0,
		'passwd' => hash_password($user_profile[$subaccount]['member_name'], un_htmlspecialchars($_POST['pwmain'])),
	));

	// Invalidate caches
	cache_put_data('user_multiaccounts-' . $cur_profile['id_member'], null, 240);
	cache_put_data('user_multiaccounts-' . $subaccount, null, 240);

	// Switch to the new parent
	SwitchMultiAccount('action=profile;area=managemultiaccounts');
}

/**
 * Toggle the shared/shareable status of a linked account.
 *
 * @param int $memID The member ID
 */
function MultiAccountsShare($memID)
{
	global $context, $txt, $cur_profile, $smcFunc;

	if (empty($_GET['subaccount']))
	{
		$context['custom_error_title'] = $txt['multiaccounts_error'];
		$context['post_errors'][] = $txt['multiaccounts_not_selected'];
		return MultiAccountsBrowse($memID);
	}

	$subaccount = (int) $_GET['subaccount'];

	// Verify ownership
	$request = $smcFunc['db_query']('', '
		SELECT id_parent
		FROM {db_prefix}multiaccounts
		WHERE id_member = {int:id_member}
			AND id_parent = {int:id_parent}',
		array(
			'id_member' => $subaccount,
			'id_parent' => $memID,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('cannot_multiaccounts_share', false);
	}
	$smcFunc['db_free_result']($request);

	// Check current shareable status
	$request = $smcFunc['db_query']('', '
		SELECT is_shareable
		FROM {db_prefix}members
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => $subaccount,
		)
	);
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($row['is_shareable']))
	{
		// Un-share: remove links from other parents
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}multiaccounts
			WHERE id_member = {int:subaccount}
				AND id_parent != {int:parent}',
			array(
				'subaccount' => $subaccount,
				'parent' => $cur_profile['id_member'],
			)
		);

		updateMemberData($subaccount, array('is_shareable' => 0));
	}
	else
	{
		updateMemberData($subaccount, array('is_shareable' => $cur_profile['id_member']));
	}

	cache_put_data('user_multiaccounts-' . $cur_profile['id_member'], null, 240);

	redirectexit('action=profile;area=managemultiaccounts;u=' . $cur_profile['id_member']);
}

/**
 * Switch the active account to a linked account.
 *
 * @param string $location Optional redirect location
 */
function SwitchMultiAccount($location = '')
{
	global $smcFunc, $user_info, $sourcedir, $modSettings, $cookiename;

	checkSession('request');

	$_REQUEST['subaccount'] = !empty($_REQUEST['subaccount']) ? (int) $_REQUEST['subaccount'] : -1;

	// Validate the subaccount exists in user's list
	if (!isset($user_info['multiaccounts'][$_REQUEST['subaccount']]))
		redirectexit(empty($location) ? (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '') : $location);

	// Get the subaccount's credentials
	$request = $smcFunc['db_query']('', '
		SELECT id_member, passwd, password_salt, is_shareable
		FROM {db_prefix}members
		WHERE id_member = {int:to_switch}
		LIMIT 1',
		array(
			'to_switch' => $_REQUEST['subaccount'],
		)
	);
	$new_user = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Get the current cookie timeout
	if (isset($_COOKIE[$cookiename]))
	{
		$cookie_data = $smcFunc['json_decode']($_COOKIE[$cookiename], true);
		if (is_array($cookie_data) && isset($cookie_data[2]))
			$timeout = $cookie_data[2];
	}

	if (empty($timeout) && isset($_SESSION['login_' . $cookiename]))
	{
		$cookie_data = $smcFunc['json_decode']($_SESSION['login_' . $cookiename], true);
		if (is_array($cookie_data) && isset($cookie_data[2]))
			$timeout = $cookie_data[2];
	}

	if (empty($timeout))
		$timeout = time() + 3600;

	$timeout -= time();

	require_once($sourcedir . '/Subs-Auth.php');

	// Set the login cookie for the new account using SMF 2.1 hash_salt
	setLoginCookie($timeout, $new_user['id_member'], hash_salt($new_user['passwd'], $new_user['password_salt']));

	// Handle parent cookie for shared accounts
	if (!empty($new_user['is_shareable']))
	{
		$id_parent = !empty($user_info['id_parent']) ? $user_info['id_parent'] : $user_info['id'];

		$request = $smcFunc['db_query']('', '
			SELECT passwd, password_salt
			FROM {db_prefix}members
			WHERE id_member = {int:id_parent}',
			array(
				'id_parent' => $id_parent,
			)
		);
		$old_user = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		setMultiAccountsParentCookie($timeout, $id_parent, hash_salt($old_user['passwd'], $old_user['password_salt']));
	}
	else
	{
		setMultiAccountsParentCookie(-3600, 0);
	}

	// Update online log
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}log_online
		SET id_member = {int:new_user}
		WHERE id_member = {int:user}',
		array(
			'new_user' => $new_user['id_member'],
			'user' => $user_info['id'],
		)
	);

	// Update last login info
	updateMemberData($new_user['id_member'], array('last_login' => time(), 'member_ip' => $user_info['ip'], 'member_ip2' => $_SERVER['BAN_CHECK_IP']));

	redirectexit(empty($location) ? (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '') : $location);
}

/**
 * Set the parent account cookie for shared account tracking.
 *
 * @param int $cookie_length Cookie length in seconds
 * @param int $id Parent member ID
 * @param string $password Hashed password
 */
function setMultiAccountsParentCookie($cookie_length, $id, $password = '')
{
	global $smcFunc, $cookiename, $boardurl, $modSettings;

	$parent_cookie = $cookiename . '_parent';
	$expiry_time = ($cookie_length >= 0 ? time() + $cookie_length : 1);

	$cookie_url = url_parts(!empty($modSettings['localCookies']), !empty($modSettings['globalCookies']));

	// Clear existing cookie if format changed
	if (isset($_COOKIE[$parent_cookie]))
	{
		$old_data = $smcFunc['json_decode']($_COOKIE[$parent_cookie], true);
		if (is_array($old_data) && isset($old_data[3]) && isset($old_data[4]))
		{
			if ($old_data[3] != $cookie_url[0] || $old_data[4] != $cookie_url[1])
				smf_setcookie($parent_cookie, $smcFunc['json_encode'](array(0, '', 0, $old_data[3], $old_data[4]), JSON_FORCE_OBJECT), 1, $old_data[4], $old_data[3]);
		}
	}

	// Set the cookie data using JSON format (SMF 2.1 style)
	$data = $smcFunc['json_encode'](
		empty($id) ? array(0, '', 0, $cookie_url[0], $cookie_url[1]) : array($id, $password, $expiry_time, $cookie_url[0], $cookie_url[1]),
		JSON_FORCE_OBJECT
	);

	smf_setcookie($parent_cookie, $data, $expiry_time, $cookie_url[1], $cookie_url[0]);

	// Handle subdomain-independent cookies
	if (empty($id) && !empty($modSettings['globalCookies']))
		smf_setcookie($parent_cookie, $data, $expiry_time, $cookie_url[1], '');

	// Handle alias URLs
	if (!empty($modSettings['forum_alias_urls']))
	{
		$aliases = explode(',', $modSettings['forum_alias_urls']);
		$temp = $boardurl;

		foreach ($aliases as $alias)
		{
			$alias = strtr(trim($alias), array('http://' => '', 'https://' => ''));
			$boardurl = 'http://' . $alias;

			$cookie_url = url_parts(!empty($modSettings['localCookies']), !empty($modSettings['globalCookies']));

			if ($cookie_url[0] == '')
				$cookie_url[0] = strtok($alias, '/');

			$alias_data = $smcFunc['json_decode']($data, true);
			$alias_data[3] = $cookie_url[0];
			$alias_data[4] = $cookie_url[1];
			$alias_data = $smcFunc['json_encode']($alias_data, JSON_FORCE_OBJECT);

			smf_setcookie($parent_cookie, $alias_data, $expiry_time, $cookie_url[1], $cookie_url[0]);
		}

		$boardurl = $temp;
	}

	$_COOKIE[$parent_cookie] = $data;

	if (!isset($_SESSION['login_' . $parent_cookie]) || $_SESSION['login_' . $parent_cookie] !== $data)
		$_SESSION['login_' . $parent_cookie] = $data;
}
