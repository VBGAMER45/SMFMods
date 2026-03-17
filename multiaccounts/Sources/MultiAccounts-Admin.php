<?php

/**
 * Multi Accounts - Admin Panel
 *
 * Admin settings and management for the Multi Accounts modification.
 *
 * @package MultiAccounts
 * @author vbgamer45
 * @license BSD
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Main dispatcher for the admin panel.
 */
function MultiAccountsAdmin()
{
	global $context, $txt, $sourcedir;

	loadLanguage('MultiAccounts');

	$subActions = array(
		'settings' => 'MultiAccountsSettings',
		'view' => 'MultiAccountsView',
	);

	// Default to settings
	$sa = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'settings';

	// Tab data
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['multiaccounts_admin_title'],
		'description' => $txt['multiaccounts_admin_desc'],
		'tabs' => array(
			'settings' => array(),
			'view' => array(),
		),
	);

	$subActions[$sa]();
}

/**
 * Multi Accounts settings page.
 */
function MultiAccountsSettings()
{
	global $context, $txt, $scripturl, $sourcedir, $smcFunc;

	require_once($sourcedir . '/ManageServer.php');

	$context['page_title'] = $txt['multiaccounts_admin_title'];
	$context['sub_template'] = 'show_settings';

	$config_vars = array(
		array('check', 'enableMultiAccounts'),
		array('check', 'multiaccountsInheritParentGroup'),
		array('check', 'multiaccountsShowInMemberlist'),
		array('check', 'multiaccountsShowInProfile'),
		'',
		array('title', 'multiaccounts_group_limits_title'),
		array('desc', 'multiaccounts_group_limits_desc'),
		array('callback', 'multiaccounts_group_settings'),
	);

	if (isset($_GET['save']))
	{
		checkSession();

		// Save the group limits
		$group_limits = array();
		if (!empty($_POST['multiaccounts_group_limit']))
		{
			foreach ($_POST['multiaccounts_group_limit'] as $group_id => $limit)
			{
				$group_id = (int) $group_id;
				$limit = (int) $limit;
				if ($group_id >= 0 && $limit >= 0)
					$group_limits[$group_id] = $limit;
			}
		}
		updateSettings(array('multiaccountsGroupLimits' => $smcFunc['json_encode']($group_limits)));

		saveDBSettings($config_vars);
		redirectexit('action=admin;area=multiaccounts;sa=settings');
	}

	prepareDBSettingContext($config_vars);

	$context['post_url'] = $scripturl . '?action=admin;area=multiaccounts;sa=settings;save';
	$context['settings_title'] = $txt['multiaccounts_admin_title'];
}

/**
 * View all linked accounts in the system.
 */
function MultiAccountsView()
{
	global $context, $txt, $scripturl, $sourcedir, $smcFunc;

	require_once($sourcedir . '/Subs-List.php');

	$context['page_title'] = $txt['multiaccounts_view_all'];

	$listOptions = array(
		'id' => 'multiaccounts_list',
		'title' => $txt['multiaccounts_view_all'],
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=multiaccounts;sa=view',
		'default_sort_col' => 'parent_name',
		'no_items_label' => $txt['multiaccounts_none_found'],
		'get_items' => array(
			'function' => 'list_getMultiaccounts',
		),
		'get_count' => array(
			'function' => 'list_getNumMultiaccounts',
		),
		'columns' => array(
			'parent_name' => array(
				'header' => array(
					'value' => $txt['multiaccounts_parent'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=profile;u=%1$d">%2$s</a>',
						'params' => array(
							'id_parent' => false,
							'parent_name' => true,
						),
					),
				),
				'sort' => array(
					'default' => 'parent_name',
					'reverse' => 'parent_name DESC',
				),
			),
			'child_name' => array(
				'header' => array(
					'value' => $txt['multiaccounts_child'],
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=profile;u=%1$d">%2$s</a>',
						'params' => array(
							'id_member' => false,
							'child_name' => true,
						),
					),
				),
				'sort' => array(
					'default' => 'child_name',
					'reverse' => 'child_name DESC',
				),
			),
			'is_shared' => array(
				'header' => array(
					'value' => $txt['multiaccounts_shared'],
				),
				'data' => array(
					'function' => function($rowData) use ($txt)
					{
						return !empty($rowData['is_shareable']) ? $txt['yes'] : $txt['no'];
					},
				),
			),
		),
	);

	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'multiaccounts_list';
}

/**
 * Get multi account entries for the list.
 *
 * @param int $start Start offset
 * @param int $items_per_page Items per page
 * @param string $sort Sort column
 * @return array
 */
function list_getMultiaccounts($start, $items_per_page, $sort)
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT ma.id_member, ma.id_parent, parent.real_name AS parent_name,
			child.real_name AS child_name, child.is_shareable
		FROM {db_prefix}multiaccounts AS ma
			INNER JOIN {db_prefix}members AS parent ON (parent.id_member = ma.id_parent)
			INNER JOIN {db_prefix}members AS child ON (child.id_member = ma.id_member)
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:per_page}',
		array(
			'sort' => $sort,
			'start' => $start,
			'per_page' => $items_per_page,
		)
	);

	$entries = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$entries[] = $row;
	$smcFunc['db_free_result']($request);

	return $entries;
}

/**
 * Get the total count of multi account entries.
 *
 * @return int
 */
function list_getNumMultiaccounts()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}multiaccounts',
		array()
	);

	list($count) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return (int) $count;
}

/**
 * Template callback for per-group account limits.
 */
function template_callback_multiaccounts_group_settings()
{
	global $context, $txt, $smcFunc, $modSettings;

	// Load membergroups
	$request = $smcFunc['db_query']('', '
		SELECT id_group, group_name
		FROM {db_prefix}membergroups
		WHERE min_posts = -1
			AND id_group != {int:mod_group}
		ORDER BY group_name',
		array(
			'mod_group' => 3,
		)
	);

	$groups = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$groups[$row['id_group']] = $row['group_name'];
	$smcFunc['db_free_result']($request);

	// Get current limits
	$group_limits = !empty($modSettings['multiaccountsGroupLimits']) ? $smcFunc['json_decode']($modSettings['multiaccountsGroupLimits'], true) : array();

	echo '
		<fieldset>
			<legend>', $txt['multiaccounts_group_limits'], '</legend>
			<dl class="settings">';

	// Regular members (group 0)
	echo '
				<dt>', $txt['membergroups_members'], '</dt>
				<dd><input type="number" name="multiaccounts_group_limit[0]" value="', isset($group_limits[0]) ? $group_limits[0] : 0, '" min="0" size="5" /> <span class="smalltext">', $txt['multiaccounts_zero_unlimited'], '</span></dd>';

	foreach ($groups as $id => $name)
	{
		echo '
				<dt>', $name, '</dt>
				<dd><input type="number" name="multiaccounts_group_limit[', $id, ']" value="', isset($group_limits[$id]) ? $group_limits[$id] : 0, '" min="0" size="5" /> <span class="smalltext">', $txt['multiaccounts_zero_unlimited'], '</span></dd>';
	}

	echo '
			</dl>
		</fieldset>';
}
