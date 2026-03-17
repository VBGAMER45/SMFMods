<?php
/**
 * Ultimate Shoutbox & Chatroom - Admin Settings Panel
 *
 * @package Shoutbox
 * @version 1.1.0
 * By: vbgamer45
 * https://www.smfhacks.com
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Register admin area under Modifications.
 * Hook: integrate_admin_areas
 */
function shoutbox_admin_areas(&$admin_areas)
{
	global $txt;

	loadLanguage('Shoutbox');

	$admin_areas['config']['areas']['shoutbox'] = array(
		'label' => $txt['shoutbox_admin_title'],
		'file' => 'Shoutbox-Admin.php',
		'function' => 'ShoutboxAdminDispatch',
		'icon' => 'maintain',
		'subsections' => array(
			'settings' => array($txt['shoutbox_admin_title']),
			'rooms' => array($txt['shoutbox_admin_rooms']),
		),
	);
}

/**
 * Add quick-access link in general mod settings.
 * Hook: integrate_general_mod_settings
 */
function shoutbox_general_settings(&$config_vars)
{
	global $txt;

	loadLanguage('Shoutbox');

	$config_vars[] = '';
	$config_vars[] = array('desc', 'shoutbox_admin_desc');
}

/**
 * Admin sub-action dispatcher.
 */
function ShoutboxAdminDispatch()
{
	global $context, $txt;

	isAllowedTo('admin_forum');
	loadLanguage('Shoutbox');
	loadTemplate('Shoutbox');

	$sa = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : 'settings';

	$subActions = array(
		'settings' => 'ShoutboxAdminSettings',
		'rooms' => 'ShoutboxAdminRooms',
	);

	// Mark the active tab.
	$context['admin_area'] = 'shoutbox';

	// Set up the tab data for SMF's native tab bar.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['shoutbox_admin_title'],
		'description' => $txt['shoutbox_admin_desc'],
		'tabs' => array(
			'settings' => array(
				'description' => !empty($txt['shoutbox_admin_settings_desc']) ? $txt['shoutbox_admin_settings_desc'] : $txt['shoutbox_admin_desc'],
			),
			'rooms' => array(
				'description' => !empty($txt['shoutbox_admin_rooms_desc']) ? $txt['shoutbox_admin_rooms_desc'] : $txt['shoutbox_admin_rooms'],
			),
		),
	);

	if (isset($subActions[$sa]))
	{
		$context['sub_action'] = $sa;
		call_user_func($subActions[$sa]);
	}
	else
		ShoutboxAdminSettings();
}

/**
 * Admin settings page handler.
 */
function ShoutboxAdminSettings()
{
	global $context, $scripturl, $txt, $modSettings;

	$context['page_title'] = $txt['shoutbox_admin_title'];
	$context['sub_template'] = 'shoutbox_admin';

	$context['linktree'][] = array(
		'name' => $txt['shoutbox_admin_title'],
		'url' => $scripturl . '?action=admin;area=shoutbox;sa=settings',
	);

	// Handle save.
	if (isset($_GET['save']))
	{
		checkSession();

		$save_vars = array(
			'shoutbox_enabled' => !empty($_POST['shoutbox_enabled']) ? '1' : '0',
			'shoutbox_poll_interval' => max(1000, (int) ($_POST['shoutbox_poll_interval'] ?? 3000)),
			'shoutbox_max_length' => max(10, (int) ($_POST['shoutbox_max_length'] ?? 500)),
			'shoutbox_max_display' => max(5, (int) ($_POST['shoutbox_max_display'] ?? 25)),
			'shoutbox_max_display_chatroom' => max(10, (int) ($_POST['shoutbox_max_display_chatroom'] ?? 100)),
			'shoutbox_flood_time' => max(0, (int) ($_POST['shoutbox_flood_time'] ?? 5)),
			'shoutbox_placement' => in_array($_POST['shoutbox_placement'] ?? '', array('top', 'bottom', 'none')) ? $_POST['shoutbox_placement'] : 'top',
			'shoutbox_show_avatars' => !empty($_POST['shoutbox_show_avatars']) ? '1' : '0',
			'shoutbox_enable_bbcode' => !empty($_POST['shoutbox_enable_bbcode']) ? '1' : '0',
			'shoutbox_enable_smileys' => !empty($_POST['shoutbox_enable_smileys']) ? '1' : '0',
			'shoutbox_enable_mentions' => !empty($_POST['shoutbox_enable_mentions']) ? '1' : '0',
			'shoutbox_enable_whispers' => !empty($_POST['shoutbox_enable_whispers']) ? '1' : '0',
			'shoutbox_gif_provider' => in_array($_POST['shoutbox_gif_provider'] ?? '', array('none', 'tenor', 'giphy', 'klipy')) ? $_POST['shoutbox_gif_provider'] : 'none',
			'shoutbox_gif_api_key' => isset($_POST['shoutbox_gif_api_key']) ? trim($_POST['shoutbox_gif_api_key']) : '',
			'shoutbox_enable_sounds' => !empty($_POST['shoutbox_enable_sounds']) ? '1' : '0',
			'shoutbox_enable_reactions' => !empty($_POST['shoutbox_enable_reactions']) ? '1' : '0',
			'shoutbox_show_smiley_picker' => !empty($_POST['shoutbox_show_smiley_picker']) ? '1' : '0',
			'shoutbox_show_bbc_toolbar' => !empty($_POST['shoutbox_show_bbc_toolbar']) ? '1' : '0',
			'shoutbox_guest_access' => !empty($_POST['shoutbox_guest_access']) ? '1' : '0',
			'shoutbox_auto_prune_days' => max(0, (int) ($_POST['shoutbox_auto_prune_days'] ?? 30)),
			'shoutbox_disable_cache' => !empty($_POST['shoutbox_disable_cache']) ? '1' : '0',
			'shoutbox_disable_chatroom' => !empty($_POST['shoutbox_disable_chatroom']) ? '1' : '0',
			'shoutbox_newest_first' => !empty($_POST['shoutbox_newest_first']) ? '1' : '0',
			'shoutbox_widget_height' => max(100, (int) ($_POST['shoutbox_widget_height'] ?? 280)),
			'shoutbox_enable_attachments' => !empty($_POST['shoutbox_enable_attachments']) ? '1' : '0',
			'shoutbox_attachment_max_size' => max(1, (int) ($_POST['shoutbox_attachment_max_size'] ?? 1024)),
			'shoutbox_show_on_boardindex' => !empty($_POST['shoutbox_show_on_boardindex']) ? '1' : '0',
			'shoutbox_show_on_portal' => !empty($_POST['shoutbox_show_on_portal']) ? '1' : '0',
			'shoutbox_show_on_boards' => !empty($_POST['shoutbox_show_on_boards']) ? '1' : '0',
			'shoutbox_show_on_topics' => !empty($_POST['shoutbox_show_on_topics']) ? '1' : '0',
			'shoutbox_show_on_other' => !empty($_POST['shoutbox_show_on_other']) ? '1' : '0',
			'shoutbox_exclude_actions' => isset($_POST['shoutbox_exclude_actions']) ? trim($_POST['shoutbox_exclude_actions']) : '',
		);

		updateSettings($save_vars);
		redirectexit('action=admin;area=shoutbox;sa=settings');
	}

	// Build config vars for the template.
	$context['config_vars'] = array();

	// General Settings section.
	$context['config_vars'][] = $txt['shoutbox_admin_general'];

	$context['config_vars'][] = array(
		'name' => 'shoutbox_enabled',
		'label' => $txt['shoutbox_enabled'],
		'subtext' => $txt['shoutbox_enabled_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_enabled']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_poll_interval',
		'label' => $txt['shoutbox_poll_interval'],
		'subtext' => $txt['shoutbox_poll_interval_desc'],
		'type' => 'int',
		'value' => !empty($modSettings['shoutbox_poll_interval']) ? $modSettings['shoutbox_poll_interval'] : 3000,
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_max_length',
		'label' => $txt['shoutbox_max_length'],
		'subtext' => $txt['shoutbox_max_length_desc'],
		'type' => 'int',
		'value' => !empty($modSettings['shoutbox_max_length']) ? $modSettings['shoutbox_max_length'] : 500,
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_flood_time',
		'label' => $txt['shoutbox_flood_time'],
		'subtext' => $txt['shoutbox_flood_time_desc'],
		'type' => 'int',
		'value' => !empty($modSettings['shoutbox_flood_time']) ? $modSettings['shoutbox_flood_time'] : 5,
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_guest_access',
		'label' => $txt['shoutbox_guest_access'],
		'subtext' => $txt['shoutbox_guest_access_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_guest_access']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_auto_prune_days',
		'label' => $txt['shoutbox_auto_prune_days'],
		'subtext' => $txt['shoutbox_auto_prune_days_desc'],
		'type' => 'int',
		'value' => isset($modSettings['shoutbox_auto_prune_days']) ? $modSettings['shoutbox_auto_prune_days'] : 30,
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_disable_chatroom',
		'label' => $txt['shoutbox_disable_chatroom'],
		'subtext' => $txt['shoutbox_disable_chatroom_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_disable_chatroom']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_disable_cache',
		'label' => $txt['shoutbox_disable_cache'],
		'subtext' => $txt['shoutbox_disable_cache_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_disable_cache']),
	);

	// Display Settings section.
	$context['config_vars'][] = $txt['shoutbox_admin_display'];

	$context['config_vars'][] = array(
		'name' => 'shoutbox_placement',
		'label' => $txt['shoutbox_placement'],
		'subtext' => $txt['shoutbox_placement_desc'],
		'type' => 'select',
		'value' => !empty($modSettings['shoutbox_placement']) ? $modSettings['shoutbox_placement'] : 'top',
		'data' => array(
			array('top', $txt['shoutbox_placement_top']),
			array('bottom', $txt['shoutbox_placement_bottom']),
			array('none', $txt['shoutbox_placement_none']),
		),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_max_display',
		'label' => $txt['shoutbox_max_display'],
		'subtext' => $txt['shoutbox_max_display_desc'],
		'type' => 'int',
		'value' => !empty($modSettings['shoutbox_max_display']) ? $modSettings['shoutbox_max_display'] : 25,
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_max_display_chatroom',
		'label' => $txt['shoutbox_max_display_chatroom'],
		'subtext' => $txt['shoutbox_max_display_chatroom_desc'],
		'type' => 'int',
		'value' => !empty($modSettings['shoutbox_max_display_chatroom']) ? $modSettings['shoutbox_max_display_chatroom'] : 100,
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_show_avatars',
		'label' => $txt['shoutbox_show_avatars'],
		'subtext' => $txt['shoutbox_show_avatars_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_show_avatars']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_newest_first',
		'label' => $txt['shoutbox_newest_first'],
		'subtext' => $txt['shoutbox_newest_first_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_newest_first']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_widget_height',
		'label' => $txt['shoutbox_widget_height'],
		'subtext' => $txt['shoutbox_widget_height_desc'],
		'type' => 'int',
		'value' => !empty($modSettings['shoutbox_widget_height']) ? $modSettings['shoutbox_widget_height'] : 280,
	);

	// Widget Visibility section.
	$context['config_vars'][] = $txt['shoutbox_admin_visibility'];

	$context['config_vars'][] = array(
		'name' => 'shoutbox_show_on_boardindex',
		'label' => $txt['shoutbox_show_on_boardindex'],
		'subtext' => $txt['shoutbox_show_on_boardindex_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_show_on_boardindex']),
	);
	$context['config_vars'][] = array(
		'name' => 'shoutbox_show_on_portal',
		'label' => $txt['shoutbox_show_on_portal'],
		'subtext' => $txt['shoutbox_show_on_portal_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_show_on_portal']),
	);
	$context['config_vars'][] = array(
		'name' => 'shoutbox_show_on_boards',
		'label' => $txt['shoutbox_show_on_boards'],
		'subtext' => $txt['shoutbox_show_on_boards_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_show_on_boards']),
	);
	$context['config_vars'][] = array(
		'name' => 'shoutbox_show_on_topics',
		'label' => $txt['shoutbox_show_on_topics'],
		'subtext' => $txt['shoutbox_show_on_topics_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_show_on_topics']),
	);
	$context['config_vars'][] = array(
		'name' => 'shoutbox_show_on_other',
		'label' => $txt['shoutbox_show_on_other'],
		'subtext' => $txt['shoutbox_show_on_other_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_show_on_other']),
	);
	$context['config_vars'][] = array(
		'name' => 'shoutbox_exclude_actions',
		'label' => $txt['shoutbox_exclude_actions'],
		'subtext' => $txt['shoutbox_exclude_actions_desc'],
		'type' => 'text',
		'value' => isset($modSettings['shoutbox_exclude_actions']) ? $modSettings['shoutbox_exclude_actions'] : '',
		'size' => 60,
	);

	// Feature Settings section.
	$context['config_vars'][] = $txt['shoutbox_admin_features'];

	$context['config_vars'][] = array(
		'name' => 'shoutbox_enable_bbcode',
		'label' => $txt['shoutbox_enable_bbcode'],
		'subtext' => $txt['shoutbox_enable_bbcode_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_enable_bbcode']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_enable_smileys',
		'label' => $txt['shoutbox_enable_smileys'],
		'subtext' => $txt['shoutbox_enable_smileys_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_enable_smileys']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_enable_mentions',
		'label' => $txt['shoutbox_enable_mentions'],
		'subtext' => $txt['shoutbox_enable_mentions_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_enable_mentions']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_enable_whispers',
		'label' => $txt['shoutbox_enable_whispers'],
		'subtext' => $txt['shoutbox_enable_whispers_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_enable_whispers']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_enable_sounds',
		'label' => $txt['shoutbox_enable_sounds'],
		'subtext' => $txt['shoutbox_enable_sounds_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_enable_sounds']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_enable_reactions',
		'label' => $txt['shoutbox_enable_reactions'],
		'subtext' => $txt['shoutbox_enable_reactions_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_enable_reactions']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_show_smiley_picker',
		'label' => $txt['shoutbox_show_smiley_picker'],
		'subtext' => $txt['shoutbox_show_smiley_picker_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_show_smiley_picker']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_show_bbc_toolbar',
		'label' => $txt['shoutbox_show_bbc_toolbar'],
		'subtext' => $txt['shoutbox_show_bbc_toolbar_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_show_bbc_toolbar']),
	);

	// Attachment Settings section.
	$context['config_vars'][] = $txt['shoutbox_admin_attachments'];

	$context['config_vars'][] = array(
		'name' => 'shoutbox_enable_attachments',
		'label' => $txt['shoutbox_enable_attachments'],
		'subtext' => $txt['shoutbox_enable_attachments_desc'],
		'type' => 'check',
		'value' => !empty($modSettings['shoutbox_enable_attachments']),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_attachment_max_size',
		'label' => $txt['shoutbox_attachment_max_size'],
		'subtext' => $txt['shoutbox_attachment_max_size_desc'],
		'type' => 'int',
		'value' => !empty($modSettings['shoutbox_attachment_max_size']) ? $modSettings['shoutbox_attachment_max_size'] : 1024,
	);

	// GIF Settings section.
	$context['config_vars'][] = $txt['shoutbox_admin_gif'];

	$context['config_vars'][] = array(
		'name' => 'shoutbox_gif_provider',
		'label' => $txt['shoutbox_gif_provider'],
		'subtext' => $txt['shoutbox_gif_provider_desc'],
		'type' => 'select',
		'value' => !empty($modSettings['shoutbox_gif_provider']) ? $modSettings['shoutbox_gif_provider'] : 'none',
		'data' => array(
			array('none', $txt['shoutbox_gif_provider_none']),
			array('tenor', $txt['shoutbox_gif_provider_tenor']),
			array('giphy', $txt['shoutbox_gif_provider_giphy']),
			array('klipy', $txt['shoutbox_gif_provider_klipy']),
		),
	);

	$context['config_vars'][] = array(
		'name' => 'shoutbox_gif_api_key',
		'label' => $txt['shoutbox_gif_api_key'],
		'subtext' => $txt['shoutbox_gif_api_key_desc'],
		'type' => 'text',
		'value' => !empty($modSettings['shoutbox_gif_api_key']) ? $modSettings['shoutbox_gif_api_key'] : '',
		'size' => 50,
	);
}

/**
 * Admin rooms management page.
 */
function ShoutboxAdminRooms()
{
	global $context, $scripturl, $txt, $smcFunc, $sourcedir;

	require_once($sourcedir . '/Shoutbox.php');

	$context['page_title'] = $txt['shoutbox_admin_rooms'];
	$context['sub_template'] = 'shoutbox_admin_rooms';

	$context['linktree'][] = array(
		'name' => $txt['shoutbox_admin_rooms'],
		'url' => $scripturl . '?action=admin;area=shoutbox;sa=rooms',
	);

	// Handle form submissions.
	if (isset($_POST['save_room']))
	{
		checkSession();

		$id_room = isset($_POST['id_room']) ? (int) $_POST['id_room'] : 0;
		$room_name = isset($_POST['room_name']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['room_name'])) : '';

		if (empty($room_name))
		{
			$context['shoutbox_error'] = $txt['shoutbox_room_name_required'];
		}
		else
		{
			$room_desc = isset($_POST['room_desc']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['room_desc'])) : '';
			$is_private = !empty($_POST['is_private']) ? 1 : 0;
			$allowed_groups = '';
			if ($is_private && !empty($_POST['allowed_groups']) && is_array($_POST['allowed_groups']))
				$allowed_groups = implode(',', array_map('intval', $_POST['allowed_groups']));
			$sort_order = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;

			if ($id_room > 0)
			{
				// Edit existing room.
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}shoutbox_rooms
					SET room_name = {string:room_name},
						room_desc = {string:room_desc},
						is_private = {int:is_private},
						allowed_groups = {string:allowed_groups},
						sort_order = {int:sort_order}
					WHERE id_room = {int:id_room}',
					array(
						'room_name' => $room_name,
						'room_desc' => $room_desc,
						'is_private' => $is_private,
						'allowed_groups' => $allowed_groups,
						'sort_order' => $sort_order,
						'id_room' => $id_room,
					)
				);
			}
			else
			{
				// Create new room.
				$smcFunc['db_insert']('insert',
					'{db_prefix}shoutbox_rooms',
					array(
						'room_name' => 'string',
						'room_desc' => 'string',
						'is_private' => 'int',
						'allowed_groups' => 'string',
						'sort_order' => 'int',
						'is_default' => 'int',
						'created_at' => 'int',
					),
					array(
						$room_name,
						$room_desc,
						$is_private,
						$allowed_groups,
						$sort_order,
						0,
						time(),
					),
					array('id_room')
				);
			}

			shoutbox_invalidate_room_cache();
			redirectexit('action=admin;area=shoutbox;sa=rooms');
		}
	}

	// Handle delete.
	if (isset($_POST['delete_room']))
	{
		checkSession();

		$id_room = isset($_POST['id_room']) ? (int) $_POST['id_room'] : 0;

		if ($id_room > 0)
		{
			// Check it's not the default.
			$request = $smcFunc['db_query']('', '
				SELECT is_default
				FROM {db_prefix}shoutbox_rooms
				WHERE id_room = {int:id_room}
				LIMIT 1',
				array(
					'id_room' => $id_room,
				)
			);

			if ($smcFunc['db_num_rows']($request) > 0)
			{
				$row = $smcFunc['db_fetch_assoc']($request);
				$smcFunc['db_free_result']($request);

				if (empty($row['is_default']))
				{
					// Delete reactions for messages in the room.
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}shoutbox_reactions
						WHERE id_msg IN (SELECT id_msg FROM {db_prefix}shoutbox_messages WHERE id_room = {int:id_room})',
						array(
							'id_room' => $id_room,
						)
					);

					// Delete messages in the room.
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}shoutbox_messages
						WHERE id_room = {int:id_room}',
						array(
							'id_room' => $id_room,
						)
					);

					// Delete the room.
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}shoutbox_rooms
						WHERE id_room = {int:id_room}',
						array(
							'id_room' => $id_room,
						)
					);

					shoutbox_invalidate_room_cache();
				}
				else
				{
					$context['shoutbox_error'] = $txt['shoutbox_cannot_delete_default_room'];
				}
			}
			else
			{
				$smcFunc['db_free_result']($request);
			}
		}

		if (empty($context['shoutbox_error']))
			redirectexit('action=admin;area=shoutbox;sa=rooms');
	}

	// Check if editing an existing room.
	$context['shoutbox_edit_room'] = null;
	if (isset($_REQUEST['edit']))
	{
		$edit_id = (int) $_REQUEST['edit'];

		$request = $smcFunc['db_query']('', '
			SELECT id_room, room_name, room_desc, is_private, allowed_groups, sort_order, is_default
			FROM {db_prefix}shoutbox_rooms
			WHERE id_room = {int:id_room}
			LIMIT 1',
			array(
				'id_room' => $edit_id,
			)
		);

		if ($smcFunc['db_num_rows']($request) > 0)
			$context['shoutbox_edit_room'] = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);
	}

	// Fetch all rooms with message counts.
	$request = $smcFunc['db_query']('', '
		SELECT r.id_room, r.room_name, r.room_desc, r.is_private, r.allowed_groups,
			r.sort_order, r.is_default, r.created_at,
			COUNT(sm.id_msg) AS msg_count
		FROM {db_prefix}shoutbox_rooms AS r
			LEFT JOIN {db_prefix}shoutbox_messages AS sm ON (sm.id_room = r.id_room)
		GROUP BY r.id_room, r.room_name, r.room_desc, r.is_private, r.allowed_groups,
			r.sort_order, r.is_default, r.created_at
		ORDER BY r.sort_order ASC, r.id_room ASC',
		array()
	);

	$context['shoutbox_rooms'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['shoutbox_rooms'][] = $row;
	$smcFunc['db_free_result']($request);

	// Load SMF membergroups for the access control UI.
	$context['shoutbox_membergroups'] = array();
	$request = $smcFunc['db_query']('', '
		SELECT id_group, group_name
		FROM {db_prefix}membergroups
		WHERE min_posts = -1
			AND id_group != 3
		ORDER BY group_name ASC',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['shoutbox_membergroups'][] = $row;
	$smcFunc['db_free_result']($request);
}
