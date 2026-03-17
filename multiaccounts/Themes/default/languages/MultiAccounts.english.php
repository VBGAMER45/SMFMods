<?php

/**
 * Multi Accounts - Language strings (English)
 *
 * @package MultiAccounts
 * @author vbgamer45
 * @license BSD
 */

// General
$txt['multiaccounts_manage'] = 'Multi Accounts';
$txt['multiaccounts_desc'] = 'From here you can manage your linked accounts. Create new accounts, link existing ones, or manage the relationships between them.';
$txt['multiaccounts_none'] = 'You do not currently have any linked accounts.';
$txt['multiaccounts_not_selected'] = 'No account was selected.';
$txt['multiaccounts_error'] = 'Multi Accounts Error';
$txt['multiaccounts_account'] = 'Account';
$txt['multiaccounts_parent_account'] = 'Parent Account';
$txt['multiaccounts_shared'] = 'Shared';

// Actions
$txt['multiaccounts_create'] = 'Create / Link Account';
$txt['multiaccounts_create_desc'] = 'Enter a username and password below. If the username already exists and you provide the correct password, that account will be linked. Otherwise, a new account will be created.';
$txt['multiaccounts_username'] = 'Username';
$txt['multiaccounts_username_desc'] = 'Enter a new or existing username.';
$txt['multiaccounts_password_desc'] = 'For existing accounts, enter the current password. For new accounts, choose a password.';
$txt['multiaccounts_merge'] = 'Merge';
$txt['multiaccounts_merge_desc'] = 'Merge account &quot;%1$s&quot; into another account. All posts and data will be transferred to the target account, and the merged account will be permanently deleted.';
$txt['multiaccounts_split'] = 'Split';
$txt['multiaccounts_split_desc'] = 'Split this account into an independent account. You must provide a new email address and password for the split account.';
$txt['multiaccounts_reassign'] = 'Reassign Parent';
$txt['multiaccounts_reassign_desc'] = 'Reassign this account as the new parent of all linked accounts. The current parent will become a child account. A new password is required for the new parent.';
$txt['multiaccounts_new_parent'] = 'New Parent';
$txt['multiaccounts_share'] = 'Share';
$txt['multiaccounts_unshare'] = 'Unshare';
$txt['multiaccounts_switch_to'] = 'Switch Account:';
$txt['multiaccounts_switch_to_name'] = 'Switch to: %1$s';
$txt['multiaccounts_post_as'] = 'Post as';
$txt['multiaccounts_search_linked'] = 'Include linked accounts in search';

// Confirmations
$txt['multiaccounts_confirm_delete'] = 'Are you sure you want to delete this linked account? All posts will be transferred to the parent account and the account will be permanently removed.';
$txt['multiaccounts_confirm_merge'] = 'Are you sure? This action cannot be undone. The merged account will be permanently deleted.';

// Errors
$txt['cannot_multiaccounts_delete'] = 'You are not allowed to delete this linked account.';
$txt['cannot_multiaccounts_merge'] = 'You are not allowed to merge this linked account.';
$txt['cannot_multiaccounts_merge_shared'] = 'You cannot merge a shared account that belongs to another parent.';
$txt['cannot_multiaccounts_split'] = 'You are not allowed to split this linked account.';
$txt['cannot_multiaccounts_split_shared'] = 'You cannot split a shared account that belongs to another parent.';
$txt['cannot_multiaccounts_reassign'] = 'You are not allowed to reassign this linked account.';
$txt['cannot_multiaccounts_reassign_shared'] = 'You cannot reassign a shared account that belongs to another parent.';
$txt['cannot_multiaccounts_share'] = 'You are not allowed to share this linked account.';
$txt['multiaccounts_login_blocked'] = 'This account is a linked account and cannot be logged into directly. Please log in with your parent account and use the account switcher.';

// Permissions
$txt['permissiongroup_multiaccounts'] = 'Multi Accounts';
$txt['permissionname_multiaccounts_create'] = 'Create and link accounts';
$txt['permissionhelp_multiaccounts_create'] = 'This permission allows members to create new linked accounts or link existing accounts.';
$txt['permissionname_multiaccounts_create_own'] = 'Own accounts';
$txt['permissionname_multiaccounts_create_any'] = 'Any member\'s accounts';
$txt['permissionname_multiaccounts_delete'] = 'Delete linked accounts';
$txt['permissionhelp_multiaccounts_delete'] = 'This permission allows members to delete linked accounts and transfer posts.';
$txt['permissionname_multiaccounts_delete_own'] = 'Own accounts';
$txt['permissionname_multiaccounts_delete_any'] = 'Any member\'s accounts';
$txt['permissionname_multiaccounts_merge'] = 'Merge linked accounts';
$txt['permissionhelp_multiaccounts_merge'] = 'This permission allows members to merge linked accounts together.';
$txt['permissionname_multiaccounts_merge_own'] = 'Own accounts';
$txt['permissionname_multiaccounts_merge_any'] = 'Any member\'s accounts';
$txt['permissionname_multiaccounts_split'] = 'Split linked accounts';
$txt['permissionhelp_multiaccounts_split'] = 'This permission allows members to split linked accounts into independent accounts.';
$txt['permissionname_multiaccounts_split_own'] = 'Own accounts';
$txt['permissionname_multiaccounts_split_any'] = 'Any member\'s accounts';

// Admin
$txt['multiaccounts_admin_title'] = 'Multi Accounts';
$txt['multiaccounts_admin_desc'] = 'Configure Multi Accounts settings and manage all linked accounts.';
$txt['enableMultiAccounts'] = 'Enable Multi Accounts';
$txt['multiaccountsInheritParentGroup'] = 'New child accounts inherit parent\'s member group';
$txt['multiaccountsShowInMemberlist'] = 'Show linked accounts in member list';
$txt['multiaccountsShowInProfile'] = 'Show linked accounts in profile summary';
$txt['multiaccounts_group_limits_title'] = 'Per-Group Account Limits';
$txt['multiaccounts_group_limits_desc'] = 'Set the maximum number of linked accounts each member group can create. Set to 0 for unlimited.';
$txt['multiaccounts_group_limits'] = 'Account Limits by Group';
$txt['multiaccounts_zero_unlimited'] = '(0 = unlimited)';
$txt['multiaccounts_view_all'] = 'View All Linked Accounts';
$txt['multiaccounts_none_found'] = 'No linked accounts found.';
$txt['multiaccounts_parent'] = 'Parent Account';
$txt['multiaccounts_child'] = 'Linked Account';
