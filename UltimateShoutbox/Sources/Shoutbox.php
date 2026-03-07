<?php
/**
 * Ultimate Shoutbox & Chatroom - Main Controller
 *
 * Handles action dispatching, AJAX endpoints, hook callbacks,
 * and widget injection for the shoutbox mod.
 *
 * @package Shoutbox
 * @version 1.1.0
 * By: vbgamer45
 * https://www.smfhacks.com
 */

if (!defined('SMF'))
	die('No direct access...');

// Cache buster version - increment on each release/update.
define('SHOUTBOX_VERSION', '1.1.0');

/**
 * Return the list of available reaction types with their icon files and labels.
 */
function shoutbox_reaction_types()
{
	return array(
		'thumbup' => array('icon' => 'thumb_up.png', 'label' => 'Like'),
		'thumbdown' => array('icon' => 'thumb_down.png', 'label' => 'Dislike'),
		'star' => array('icon' => 'star.png', 'label' => 'Star'),
		'heart' => array('icon' => 'heart.png', 'label' => 'Love'),
		'medal' => array('icon' => 'medal_gold_1.png', 'label' => 'Award'),
	);
}

/**
 * Load smiley data from the database for the smiley picker.
 * Returns array of visible smileys with code, filename, and description.
 *
 * SMF 2.1 stores filenames in a separate {db_prefix}smiley_files table
 * keyed by smiley_set, so we JOIN on the user's active set.
 */
function shoutbox_get_smileys()
{
	global $smcFunc, $modSettings, $user_info;

	$smiley_set = !empty($user_info['smiley_set']) ? $user_info['smiley_set'] : 'default';

	// Cache key includes smiley set so different users with different sets get correct filenames.
	$cache_key = 'shoutbox_smileys_' . $smiley_set;

	if (!empty($modSettings['shoutbox_disable_cache']) || ($smileys = cache_get_data($cache_key, 3600)) === null)
	{
		$smileys = array();
		$request = $smcFunc['db_query']('', '
			SELECT s.code, sf.filename, s.description
			FROM {db_prefix}smileys AS s
				INNER JOIN {db_prefix}smiley_files AS sf ON (sf.id_smiley = s.id_smiley AND sf.smiley_set = {string:smiley_set})
			WHERE s.hidden = {int:hidden}
			ORDER BY s.smiley_row, s.smiley_order',
			array(
				'hidden' => 0,
				'smiley_set' => $smiley_set,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$smileys[] = array(
				'code' => $row['code'],
				'file' => $row['filename'],
				'desc' => $row['description'],
			);
		}
		$smcFunc['db_free_result']($request);

		if (empty($modSettings['shoutbox_disable_cache']))
			cache_put_data($cache_key, $smileys, 3600);
	}

	return $smileys;
}

// =========================================================================
// Hook Callbacks
// =========================================================================

/**
 * Register ?action=shoutbox with SMF's action dispatcher.
 * Hook: integrate_actions
 */
function shoutbox_actions(&$actionArray)
{
	$actionArray['shoutbox'] = array('Shoutbox.php', 'ShoutboxMain');
}

/**
 * Add "Chatroom" link to the main navigation menu.
 * Hook: integrate_menu_buttons
 */
function shoutbox_menu_buttons(&$buttons)
{
	global $scripturl, $txt, $modSettings;

	if (empty($modSettings['shoutbox_enabled']))
		return;

	// Don't show menu link if chatroom page is disabled.
	if (!empty($modSettings['shoutbox_disable_chatroom']))
		return;

	if (!allowedTo('shoutbox_view'))
		return;

	loadLanguage('Shoutbox');

	// Insert after "Home" button.
	$insert_after = 'home';
	$new_buttons = array();

	foreach ($buttons as $key => $button)
	{
		$new_buttons[$key] = $button;

		if ($key == $insert_after)
		{
			$new_buttons['shoutbox'] = array(
				'title' => $txt['shoutbox_chatroom'],
				'href' => $scripturl . '?action=shoutbox',
				'icon' => 'shoutbox/comments.png',
				'show' => true,
			);
		}
	}

	$buttons = $new_buttons;
}

/**
 * Inject the shoutbox widget, CSS, and JS on every page load.
 * Hook: integrate_load_theme
 */
function shoutbox_load_theme()
{
	global $context, $modSettings, $scripturl, $user_info, $txt, $settings;

	if (empty($modSettings['shoutbox_enabled']))
		return;

	// Guests can only view if guest_access is enabled.
	if ($user_info['is_guest'] && empty($modSettings['shoutbox_guest_access']))
		return;

	if (!allowedTo('shoutbox_view'))
		return;

	// Don't load on XML/API requests.
	if (isset($_REQUEST['xml']) || isset($_REQUEST['api']))
		return;

	// Don't load on admin pages, chatroom (has its own), or non-HTML actions.
	if (isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('admin', 'shoutbox', 'suggest', 'jsmodify', 'jsoption', 'keepalive', 'verificationcode', 'viewquery', 'viewsmfile')))
		return;

	// Custom action exclusions from admin settings.
	if (isset($_REQUEST['action']) && !empty($modSettings['shoutbox_exclude_actions']))
	{
		$excluded = array_map('trim', explode(',', strtolower($modSettings['shoutbox_exclude_actions'])));
		if (in_array(strtolower($_REQUEST['action']), $excluded))
			return;
	}

	// Page visibility check - determine current page type and check settings.
	$page_type = 'other';
	if (empty($_REQUEST['action']) && empty($_REQUEST['board']) && empty($_REQUEST['topic']))
		$page_type = 'boardindex';
	elseif (!empty($_REQUEST['board']) && empty($_REQUEST['topic']))
		$page_type = 'boards';
	elseif (!empty($_REQUEST['topic']))
		$page_type = 'topics';

	$visibility_setting = 'shoutbox_show_on_' . $page_type;
	if (isset($modSettings[$visibility_setting]) && empty($modSettings[$visibility_setting]))
		return;

	loadLanguage('Shoutbox');
	loadCSSFile('shoutbox.css', array(), 'shoutbox_css');
	loadJavaScriptFile('shoutbox.js', array('minimize' => true), 'shoutbox_js');

	// Build JS config object.
	$default_room_id = shoutbox_get_default_room_id();

	$context['shoutbox_config'] = array(
		'ajaxUrl' => $scripturl . '?action=shoutbox',
		'sessionVar' => $context['session_var'],
		'sessionId' => $context['session_id'],
		'pollInterval' => !empty($modSettings['shoutbox_poll_interval']) ? (int) $modSettings['shoutbox_poll_interval'] : 3000,
		'maxLength' => !empty($modSettings['shoutbox_max_length']) ? (int) $modSettings['shoutbox_max_length'] : 500,
		'maxDisplay' => !empty($modSettings['shoutbox_max_display']) ? (int) $modSettings['shoutbox_max_display'] : 25,
		'showAvatars' => !empty($modSettings['shoutbox_show_avatars']),
		'enableBBCode' => !empty($modSettings['shoutbox_enable_bbcode']),
		'enableSmileys' => !empty($modSettings['shoutbox_enable_smileys']),
		'enableMentions' => !empty($modSettings['shoutbox_enable_mentions']),
		'enableWhispers' => !empty($modSettings['shoutbox_enable_whispers']),
		'enableSounds' => !empty($modSettings['shoutbox_enable_sounds']),
		'enableReactions' => !empty($modSettings['shoutbox_enable_reactions']),
		'reactionsImgUrl' => $settings['images_url'] . '/shoutbox/reactions/',
		'reactionTypes' => shoutbox_reaction_types(),
		'showSmileyPicker' => !empty($modSettings['shoutbox_show_smiley_picker']) && !empty($modSettings['shoutbox_enable_smileys']),
		'showBBCToolbar' => !empty($modSettings['shoutbox_show_bbc_toolbar']) && !empty($modSettings['shoutbox_enable_bbcode']),
		'smileys' => (!empty($modSettings['shoutbox_show_smiley_picker']) && !empty($modSettings['shoutbox_enable_smileys'])) ? shoutbox_get_smileys() : array(),
		'smileysUrl' => $modSettings['smileys_url'] . '/' . $user_info['smiley_set'] . '/',
		'gifProvider' => (!empty($modSettings['shoutbox_gif_provider']) && !empty($modSettings['shoutbox_gif_api_key'])) ? $modSettings['shoutbox_gif_provider'] : 'none',
		'userId' => (int) $user_info['id'],
		'userName' => $user_info['name'],
		'canPost' => allowedTo('shoutbox_post'),
		'canModerate' => allowedTo('shoutbox_moderate'),
		'canWhisper' => allowedTo('shoutbox_whisper'),
		'canGif' => allowedTo('shoutbox_gif'),
		'isGuest' => $user_info['is_guest'],
		'placement' => !empty($modSettings['shoutbox_placement']) ? $modSettings['shoutbox_placement'] : 'top',
		'newestFirst' => !empty($modSettings['shoutbox_newest_first']),
		'isChatroom' => false,
		'chatroomDisabled' => !empty($modSettings['shoutbox_disable_chatroom']),
		'roomId' => $default_room_id,
		'smfSuggestUrl' => $scripturl . '?action=suggest;type=member',
		'version' => SHOUTBOX_VERSION,
	);

	// Add inline JS config.
	addInlineJavaScript('
		window.smf_shoutbox_config = ' . json_encode($context['shoutbox_config']) . ';
	', false);

	// Use a template layer to inject the widget HTML.
	$context['template_layers'][] = 'shoutbox_widget';

	// Load the template.
	loadTemplate('Shoutbox');
}

/**
 * Register shoutbox permissions.
 * Hook: integrate_load_permissions
 */
function shoutbox_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$permissionGroups['membergroup'][] = 'shoutbox';

	$permissionList['membergroup']['shoutbox_view'] = array(false, 'shoutbox', 'shoutbox');
	$permissionList['membergroup']['shoutbox_post'] = array(false, 'shoutbox', 'shoutbox');
	$permissionList['membergroup']['shoutbox_moderate'] = array(false, 'shoutbox', 'shoutbox');
	$permissionList['membergroup']['shoutbox_whisper'] = array(false, 'shoutbox', 'shoutbox');
	$permissionList['membergroup']['shoutbox_gif'] = array(false, 'shoutbox', 'shoutbox');

	$leftPermissionGroups[] = 'shoutbox';
}

/**
 * Add mod credit to SMF's ?action=credits page.
 * Hook: integrate_credits
 */
function shoutbox_credits()
{
	global $context;

	$context['copyrights']['mods'][] = '<a href="https://www.smfhacks.com" target="_blank" rel="noopener">Ultimate Shoutbox &amp; Chatroom</a> &copy; SMFHacks';
}

/**
 * Prevent AJAX polls from inflating page hit stats.
 * Hook: integrate_pre_log_stats
 */
function shoutbox_pre_log_stats(&$no_stat_actions)
{
	$no_stat_actions[] = 'shoutbox';
}

// =========================================================================
// Main Action Dispatcher
// =========================================================================

/**
 * Main entry point for ?action=shoutbox.
 * Routes to sub-actions or renders the chatroom page.
 */
function ShoutboxMain()
{
	global $modSettings, $user_info, $context, $txt;

	if (empty($modSettings['shoutbox_enabled']))
		fatal_lang_error('shoutbox_disabled', false);

	loadLanguage('Shoutbox');

	// Check if user is banned from shoutbox.
	if (!$user_info['is_guest'])
		shoutbox_check_ban();

	$sa = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : 'chatroom';

	$subActions = array(
		'chatroom' => 'ShoutboxChatroom',
		'fetch' => 'ShoutboxFetch',
		'send' => 'ShoutboxSend',
		'whisper' => 'ShoutboxWhisper',
		'delete' => 'ShoutboxDelete',
		'edit' => 'ShoutboxEdit',
		'prune' => 'ShoutboxPrune',
		'clean' => 'ShoutboxClean',
		'ban' => 'ShoutboxBan',
		'unban' => 'ShoutboxUnban',
		'mute' => 'ShoutboxMute',
		'unmute' => 'ShoutboxUnmute',
		'mutelist' => 'ShoutboxMutelist',
		'admin_msg' => 'ShoutboxAdminMsg',
		'react' => 'ShoutboxReact',
		'gif_proxy' => 'ShoutboxGifProxy',
		'history_page' => 'ShoutboxHistoryPage',
		'history' => 'ShoutboxHistoryData',
		'export' => 'ShoutboxExport',
		'rooms' => 'ShoutboxRoomsList',
		'room_create' => 'ShoutboxRoomCreate',
		'room_edit' => 'ShoutboxRoomEdit',
		'room_delete' => 'ShoutboxRoomDelete',
	);

	if (isset($subActions[$sa]))
		call_user_func($subActions[$sa]);
	else
		ShoutboxChatroom();
}

// =========================================================================
// Chatroom Page (full-page view)
// =========================================================================

function ShoutboxChatroom()
{
	global $context, $modSettings, $scripturl, $txt, $user_info, $settings;

	// Redirect to forum index if chatroom page is disabled.
	if (!empty($modSettings['shoutbox_disable_chatroom']))
		redirectexit();

	isAllowedTo('shoutbox_view');

	$context['page_title'] = $txt['shoutbox_chatroom'];
	$context['sub_template'] = 'shoutbox_chatroom';
	$context['linktree'][] = array(
		'name' => $txt['shoutbox_chatroom'],
		'url' => $scripturl . '?action=shoutbox',
	);

	loadCSSFile('shoutbox.css', array(), 'shoutbox_css');
	loadJavaScriptFile('shoutbox.js', array('minimize' => true), 'shoutbox_js');

	if (!empty($modSettings['shoutbox_gif_provider']) && $modSettings['shoutbox_gif_provider'] !== 'none' && allowedTo('shoutbox_gif'))
		loadJavaScriptFile('shoutbox-gif.js', array('minimize' => true), 'shoutbox_gif_js');

	// Load rooms for the chatroom page.
	$rooms = shoutbox_get_rooms();
	$default_room_id = shoutbox_get_default_room_id();

	// Build rooms array for JS config and template.
	$js_rooms = array();
	$context['shoutbox_rooms'] = array();
	foreach ($rooms as $room)
	{
		$js_rooms[] = array(
			'id' => (int) $room['id_room'],
			'name' => $room['room_name'],
			'desc' => $room['room_desc'],
			'isPrivate' => !empty($room['is_private']),
			'isDefault' => !empty($room['is_default']),
		);
		$context['shoutbox_rooms'][] = array(
			'id' => (int) $room['id_room'],
			'name' => $room['room_name'],
			'desc' => $room['room_desc'],
			'is_private' => !empty($room['is_private']),
			'is_default' => !empty($room['is_default']),
		);
	}

	$context['shoutbox_config'] = array(
		'ajaxUrl' => $scripturl . '?action=shoutbox',
		'sessionVar' => $context['session_var'],
		'sessionId' => $context['session_id'],
		'pollInterval' => !empty($modSettings['shoutbox_poll_interval']) ? (int) $modSettings['shoutbox_poll_interval'] : 3000,
		'maxLength' => !empty($modSettings['shoutbox_max_length']) ? (int) $modSettings['shoutbox_max_length'] : 500,
		'maxDisplay' => !empty($modSettings['shoutbox_max_display_chatroom']) ? (int) $modSettings['shoutbox_max_display_chatroom'] : 100,
		'showAvatars' => !empty($modSettings['shoutbox_show_avatars']),
		'enableBBCode' => !empty($modSettings['shoutbox_enable_bbcode']),
		'enableSmileys' => !empty($modSettings['shoutbox_enable_smileys']),
		'enableMentions' => !empty($modSettings['shoutbox_enable_mentions']),
		'enableWhispers' => !empty($modSettings['shoutbox_enable_whispers']),
		'enableSounds' => !empty($modSettings['shoutbox_enable_sounds']),
		'enableReactions' => !empty($modSettings['shoutbox_enable_reactions']),
		'reactionsImgUrl' => $settings['images_url'] . '/shoutbox/reactions/',
		'reactionTypes' => shoutbox_reaction_types(),
		'showSmileyPicker' => !empty($modSettings['shoutbox_show_smiley_picker']) && !empty($modSettings['shoutbox_enable_smileys']),
		'showBBCToolbar' => !empty($modSettings['shoutbox_show_bbc_toolbar']) && !empty($modSettings['shoutbox_enable_bbcode']),
		'smileys' => (!empty($modSettings['shoutbox_show_smiley_picker']) && !empty($modSettings['shoutbox_enable_smileys'])) ? shoutbox_get_smileys() : array(),
		'smileysUrl' => $modSettings['smileys_url'] . '/' . $user_info['smiley_set'] . '/',
		'gifProvider' => (!empty($modSettings['shoutbox_gif_provider']) && !empty($modSettings['shoutbox_gif_api_key'])) ? $modSettings['shoutbox_gif_provider'] : 'none',
		'userId' => (int) $user_info['id'],
		'userName' => $user_info['name'],
		'canPost' => allowedTo('shoutbox_post'),
		'canModerate' => allowedTo('shoutbox_moderate'),
		'canWhisper' => allowedTo('shoutbox_whisper'),
		'canGif' => allowedTo('shoutbox_gif'),
		'isGuest' => $user_info['is_guest'],
		'placement' => 'none',
		'newestFirst' => !empty($modSettings['shoutbox_newest_first']),
		'isChatroom' => true,
		'roomId' => $default_room_id,
		'rooms' => $js_rooms,
		'smfSuggestUrl' => $scripturl . '?action=suggest;type=member',
		'version' => SHOUTBOX_VERSION,
	);

	addInlineJavaScript('
		window.smf_shoutbox_config = ' . json_encode($context['shoutbox_config']) . ';
	', false);

	loadTemplate('Shoutbox');
}

// =========================================================================
// AJAX Endpoints
// =========================================================================

/**
 * Fetch messages (polling endpoint).
 * GET ?action=shoutbox;sa=fetch;last_id=N
 */
function ShoutboxFetch()
{
	global $smcFunc, $modSettings, $user_info;

	isAllowedTo('shoutbox_view');

	$last_id = isset($_REQUEST['last_id']) ? (int) $_REQUEST['last_id'] : 0;
	$is_chatroom = !empty($_REQUEST['chatroom']);
	$max = $is_chatroom
		? (!empty($modSettings['shoutbox_max_display_chatroom']) ? (int) $modSettings['shoutbox_max_display_chatroom'] : 100)
		: (!empty($modSettings['shoutbox_max_display']) ? (int) $modSettings['shoutbox_max_display'] : 25);

	// Room scoping - validate access, fallback to default.
	$requested_room = isset($_REQUEST['room_id']) ? (int) $_REQUEST['room_id'] : 0;
	if ($requested_room <= 0)
		$requested_room = shoutbox_get_default_room_id();
	$id_room = shoutbox_validate_room_access($requested_room);

	// Auto-prune old messages.
	shoutbox_auto_prune();

	$user_id = (int) $user_info['id'];
	$is_mod = allowedTo('shoutbox_moderate');

	if ($last_id > 0)
	{
		// Delta fetch - fast indexed query, no cache needed.
		$whisper_clause = '';
		if ($user_info['is_guest'])
			$whisper_clause = 'AND sm.is_whisper = 0';
		elseif (!$is_mod)
			$whisper_clause = 'AND (sm.is_whisper = 0 OR sm.id_member = {int:user_id} OR sm.whisper_to = {int:user_id})';

		$request = $smcFunc['db_query']('', '
			SELECT sm.id_msg, sm.id_member, sm.member_name, sm.parsed_body, sm.is_whisper,
				sm.whisper_to, sm.is_action, sm.created_at, sm.edited_by, sm.edited_at,
				mem.avatar, COALESCE(mem.real_name, sm.member_name) AS real_name,
				COALESCE(mg.online_color, pg.online_color, {string:empty}) AS member_color
			FROM {db_prefix}shoutbox_messages AS sm
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sm.id_member)
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
				LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
			WHERE sm.id_msg > {int:last_id}
				AND sm.id_room = {int:id_room}
				' . $whisper_clause . '
			ORDER BY sm.id_msg ASC
			LIMIT {int:limit}',
			array(
				'last_id' => $last_id,
				'user_id' => $user_id,
				'id_room' => $id_room,
				'limit' => $max,
				'empty' => '',
			)
		);

		$rows = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$rows[] = $row;
		$smcFunc['db_free_result']($request);

		// Batch-load reactions for these messages.
		$msg_ids = array();
		foreach ($rows as $row)
			$msg_ids[] = (int) $row['id_msg'];
		$all_reactions = shoutbox_get_reactions_for_messages($msg_ids);

		$messages = array();
		foreach ($rows as $row)
		{
			$mid = (int) $row['id_msg'];
			$msg_reactions = isset($all_reactions[$mid]) ? $all_reactions[$mid] : array();
			$messages[] = shoutbox_format_message($row, $msg_reactions);
		}
	}
	else
	{
		// Initial fetch - use SMF cache to reduce DB load (1 second TTL).
		$cache_key = 'shoutbox_initial_' . ($is_chatroom ? 'chat' : 'widget') . '_r' . $id_room;
		$use_cache = empty($modSettings['shoutbox_disable_cache']);
		$rows = $use_cache ? cache_get_data($cache_key, 1) : null;

		if ($rows === null)
		{
			$request = $smcFunc['db_query']('', '
				SELECT sm.id_msg, sm.id_member, sm.member_name, sm.parsed_body, sm.is_whisper,
					sm.whisper_to, sm.is_action, sm.created_at, sm.edited_by, sm.edited_at,
					mem.avatar, COALESCE(mem.real_name, sm.member_name) AS real_name,
					COALESCE(mg.online_color, pg.online_color, {string:empty}) AS member_color
				FROM {db_prefix}shoutbox_messages AS sm
					LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sm.id_member)
					LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
					LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
				WHERE sm.id_room = {int:id_room}
				ORDER BY sm.id_msg DESC
				LIMIT {int:limit}',
				array(
					'id_room' => $id_room,
					'limit' => $max,
					'empty' => '',
				)
			);

			$rows = array();
			while ($row = $smcFunc['db_fetch_assoc']($request))
				$rows[] = $row;
			$smcFunc['db_free_result']($request);

			if ($use_cache)
				cache_put_data($cache_key, $rows, 1);
		}

		// Filter whispers in PHP (cached rows include all messages).
		$filtered_rows = array();
		foreach ($rows as $row)
		{
			if (!empty($row['is_whisper']))
			{
				if ($user_info['is_guest'])
					continue;
				if (!$is_mod && (int) $row['id_member'] !== $user_id && (int) $row['whisper_to'] !== $user_id)
					continue;
			}
			$filtered_rows[] = $row;
		}

		// Batch-load reactions for filtered messages.
		$msg_ids = array();
		foreach ($filtered_rows as $row)
			$msg_ids[] = (int) $row['id_msg'];
		$all_reactions = shoutbox_get_reactions_for_messages($msg_ids);

		$filtered = array();
		foreach ($filtered_rows as $row)
		{
			$mid = (int) $row['id_msg'];
			$msg_reactions = isset($all_reactions[$mid]) ? $all_reactions[$mid] : array();
			$filtered[] = shoutbox_format_message($row, $msg_reactions);
		}

		// Reverse to chronological order (query was DESC).
		$messages = array_reverse($filtered);
	}

	// Get online users (cached for 1 second).
	$online_users = shoutbox_get_online_users($id_room);

	shoutbox_json_response(array(
		'messages' => $messages,
		'onlineUsers' => $online_users,
		'roomId' => $id_room,
	));
}

/**
 * Post a new shout message.
 * POST ?action=shoutbox;sa=send
 */
function ShoutboxSend()
{
	global $smcFunc, $modSettings, $user_info;

	isAllowedTo('shoutbox_post');
	checkSession('request');

	$body = isset($_POST['body']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['body'])) : '';

	if (empty($body))
		shoutbox_json_error('shoutbox_empty_message');

	$max_length = !empty($modSettings['shoutbox_max_length']) ? (int) $modSettings['shoutbox_max_length'] : 500;
	if ($smcFunc['strlen']($body) > $max_length)
		shoutbox_json_error('shoutbox_too_long');

	// Room scoping.
	$requested_room = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;
	if ($requested_room <= 0)
		$requested_room = shoutbox_get_default_room_id();
	$id_room = shoutbox_validate_room_access($requested_room);

	// Flood check.
	shoutbox_flood_check();

	// Detect /me action.
	$is_action = 0;
	if (strpos($body, '/me ') === 0)
	{
		$is_action = 1;
		$body = substr($body, 4);
	}

	// Parse BBCode.
	$parsed_body = shoutbox_parse_bbc($body);

	$smcFunc['db_insert']('insert',
		'{db_prefix}shoutbox_messages',
		array(
			'id_member' => 'int',
			'id_room' => 'int',
			'member_name' => 'string',
			'body' => 'string',
			'parsed_body' => 'string',
			'is_whisper' => 'int',
			'whisper_to' => 'int',
			'is_action' => 'int',
			'created_at' => 'int',
		),
		array(
			(int) $user_info['id'],
			$id_room,
			$user_info['name'],
			$body,
			$parsed_body,
			0,
			0,
			$is_action,
			time(),
		),
		array('id_msg')
	);

	shoutbox_invalidate_cache($id_room);
	shoutbox_json_response(array('success' => true));
}

/**
 * Send a whisper message.
 * POST ?action=shoutbox;sa=whisper
 */
function ShoutboxWhisper()
{
	global $smcFunc, $modSettings, $user_info;

	isAllowedTo('shoutbox_whisper');
	checkSession('request');

	$body = isset($_POST['body']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['body'])) : '';
	$to_name = isset($_POST['to']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['to'])) : '';

	if (empty($body) || empty($to_name))
		shoutbox_json_error('shoutbox_whisper_missing');

	$max_length = !empty($modSettings['shoutbox_max_length']) ? (int) $modSettings['shoutbox_max_length'] : 500;
	if ($smcFunc['strlen']($body) > $max_length)
		shoutbox_json_error('shoutbox_too_long');

	// Room scoping.
	$requested_room = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;
	if ($requested_room <= 0)
		$requested_room = shoutbox_get_default_room_id();
	$id_room = shoutbox_validate_room_access($requested_room);

	// Flood check.
	shoutbox_flood_check();

	// Look up recipient.
	$request = $smcFunc['db_query']('', '
		SELECT id_member, real_name
		FROM {db_prefix}members
		WHERE real_name = {string:name}
		LIMIT 1',
		array(
			'name' => $to_name,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		shoutbox_json_error('shoutbox_whisper_not_found');
	}

	$recipient = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$parsed_body = shoutbox_parse_bbc($body);

	$smcFunc['db_insert']('insert',
		'{db_prefix}shoutbox_messages',
		array(
			'id_member' => 'int',
			'id_room' => 'int',
			'member_name' => 'string',
			'body' => 'string',
			'parsed_body' => 'string',
			'is_whisper' => 'int',
			'whisper_to' => 'int',
			'is_action' => 'int',
			'created_at' => 'int',
		),
		array(
			(int) $user_info['id'],
			$id_room,
			$user_info['name'],
			$body,
			$parsed_body,
			1,
			(int) $recipient['id_member'],
			0,
			time(),
		),
		array('id_msg')
	);

	shoutbox_invalidate_cache($id_room);
	shoutbox_json_response(array(
		'success' => true,
		'whisper_to_name' => $recipient['real_name'],
	));
}

/**
 * Delete a message.
 * POST ?action=shoutbox;sa=delete
 */
function ShoutboxDelete()
{
	global $smcFunc, $user_info;

	checkSession('request');

	$id_msg = isset($_POST['id_msg']) ? (int) $_POST['id_msg'] : 0;
	if ($id_msg <= 0)
		shoutbox_json_error('shoutbox_invalid_message');

	// Fetch message for ownership check and room ID.
	$request = $smcFunc['db_query']('', '
		SELECT id_member, id_room
		FROM {db_prefix}shoutbox_messages
		WHERE id_msg = {int:id_msg}
		LIMIT 1',
		array(
			'id_msg' => $id_msg,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		shoutbox_json_error('shoutbox_invalid_message');
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
	$msg_room_id = (int) $row['id_room'];

	// Check ownership or mod permission.
	if (!allowedTo('shoutbox_moderate') && $row['id_member'] != $user_info['id'])
		shoutbox_json_error('shoutbox_no_permission');

	// Delete reactions for this message.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_reactions
		WHERE id_msg = {int:id_msg}',
		array(
			'id_msg' => $id_msg,
		)
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_messages
		WHERE id_msg = {int:id_msg}',
		array(
			'id_msg' => $id_msg,
		)
	);

	shoutbox_invalidate_cache($msg_room_id);
	shoutbox_json_response(array('success' => true));
}

/**
 * Edit a message.
 * POST ?action=shoutbox;sa=edit
 */
function ShoutboxEdit()
{
	global $smcFunc, $user_info;

	checkSession('request');

	$id_msg = isset($_POST['id_msg']) ? (int) $_POST['id_msg'] : 0;
	$body = isset($_POST['body']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['body'])) : '';

	if ($id_msg <= 0 || empty($body))
		shoutbox_json_error('shoutbox_invalid_message');

	// Fetch message for ownership check and room ID.
	$request = $smcFunc['db_query']('', '
		SELECT id_member, id_room
		FROM {db_prefix}shoutbox_messages
		WHERE id_msg = {int:id_msg}
		LIMIT 1',
		array(
			'id_msg' => $id_msg,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		shoutbox_json_error('shoutbox_invalid_message');
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
	$msg_room_id = (int) $row['id_room'];

	// Check ownership or mod permission.
	if (!allowedTo('shoutbox_moderate') && $row['id_member'] != $user_info['id'])
		shoutbox_json_error('shoutbox_no_permission');

	$parsed_body = shoutbox_parse_bbc($body);

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}shoutbox_messages
		SET body = {string:body},
			parsed_body = {string:parsed_body},
			edited_by = {int:edited_by},
			edited_at = {int:edited_at}
		WHERE id_msg = {int:id_msg}',
		array(
			'body' => $body,
			'parsed_body' => $parsed_body,
			'edited_by' => (int) $user_info['id'],
			'edited_at' => time(),
			'id_msg' => $id_msg,
		)
	);

	shoutbox_invalidate_cache($msg_room_id);
	shoutbox_json_response(array('success' => true, 'parsed_body' => $parsed_body));
}

/**
 * Prune last N messages.
 * POST ?action=shoutbox;sa=prune
 */
function ShoutboxPrune()
{
	global $smcFunc;

	isAllowedTo('shoutbox_moderate');
	checkSession('request');

	$count = isset($_POST['count']) ? (int) $_POST['count'] : 0;
	if ($count <= 0 || $count > 1000)
		shoutbox_json_error('shoutbox_invalid_count');

	// Room scoping.
	$requested_room = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;
	if ($requested_room <= 0)
		$requested_room = shoutbox_get_default_room_id();
	$id_room = shoutbox_validate_room_access($requested_room);

	// Get the IDs of the last N messages in this room.
	$request = $smcFunc['db_query']('', '
		SELECT id_msg
		FROM {db_prefix}shoutbox_messages
		WHERE id_room = {int:id_room}
		ORDER BY id_msg DESC
		LIMIT {int:count}',
		array(
			'id_room' => $id_room,
			'count' => $count,
		)
	);

	$ids = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$ids[] = $row['id_msg'];
	$smcFunc['db_free_result']($request);

	if (!empty($ids))
	{
		// Delete reactions for these messages.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}shoutbox_reactions
			WHERE id_msg IN ({array_int:ids})',
			array(
				'ids' => $ids,
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}shoutbox_messages
			WHERE id_msg IN ({array_int:ids})',
			array(
				'ids' => $ids,
			)
		);
	}

	shoutbox_invalidate_cache($id_room);
	shoutbox_json_response(array('success' => true, 'deleted' => count($ids)));
}

/**
 * Delete all messages.
 * POST ?action=shoutbox;sa=clean
 */
function ShoutboxClean()
{
	global $smcFunc, $user_info;

	// Admin only.
	if (!$user_info['is_admin'])
		shoutbox_json_error('shoutbox_no_permission');

	checkSession('request');

	// Room scoping - delete only messages in the specified room.
	$requested_room = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;
	if ($requested_room <= 0)
		$requested_room = shoutbox_get_default_room_id();
	$id_room = shoutbox_validate_room_access($requested_room);

	// Delete reactions for messages in this room.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_reactions
		WHERE id_msg IN (SELECT id_msg FROM {db_prefix}shoutbox_messages WHERE id_room = {int:id_room})',
		array(
			'id_room' => $id_room,
		)
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_messages
		WHERE id_room = {int:id_room}',
		array(
			'id_room' => $id_room,
		)
	);

	shoutbox_invalidate_cache($id_room);
	shoutbox_json_response(array('success' => true));
}

/**
 * Ban a user from the shoutbox.
 * POST ?action=shoutbox;sa=ban
 */
function ShoutboxBan()
{
	global $smcFunc, $user_info;

	isAllowedTo('shoutbox_moderate');
	checkSession('request');

	$id_member = isset($_POST['id_member']) ? (int) $_POST['id_member'] : 0;
	$reason = isset($_POST['reason']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['reason'])) : '';
	$duration = isset($_POST['duration']) ? (int) $_POST['duration'] : 0; // hours, 0=permanent

	if ($id_member <= 0)
		shoutbox_json_error('shoutbox_invalid_member');

	// Don't ban admins.
	$request = $smcFunc['db_query']('', '
		SELECT id_group
		FROM {db_prefix}members
		WHERE id_member = {int:id_member}
		LIMIT 1',
		array(
			'id_member' => $id_member,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		shoutbox_json_error('shoutbox_invalid_member');
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if ($row['id_group'] == 1)
		shoutbox_json_error('shoutbox_cannot_ban_admin');

	$expires_at = $duration > 0 ? time() + ($duration * 3600) : 0;

	// Remove any existing ban for this user first.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_bans
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => $id_member,
		)
	);

	$smcFunc['db_insert']('insert',
		'{db_prefix}shoutbox_bans',
		array(
			'id_member' => 'int',
			'banned_by' => 'int',
			'reason' => 'string',
			'created_at' => 'int',
			'expires_at' => 'int',
		),
		array(
			$id_member,
			(int) $user_info['id'],
			$reason,
			time(),
			$expires_at,
		),
		array('id_ban')
	);

	shoutbox_json_response(array('success' => true));
}

/**
 * Unban a user.
 * POST ?action=shoutbox;sa=unban
 */
function ShoutboxUnban()
{
	global $smcFunc;

	isAllowedTo('shoutbox_moderate');
	checkSession('request');

	$id_member = isset($_POST['id_member']) ? (int) $_POST['id_member'] : 0;

	if ($id_member <= 0)
		shoutbox_json_error('shoutbox_invalid_member');

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_bans
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => $id_member,
		)
	);

	shoutbox_json_response(array('success' => true));
}

/**
 * Mute (ban) a user by display name.
 * POST ?action=shoutbox;sa=mute
 */
function ShoutboxMute()
{
	global $smcFunc, $user_info;

	isAllowedTo('shoutbox_moderate');
	checkSession('request');

	$username = isset($_POST['username']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['username'])) : '';

	if (empty($username))
		shoutbox_json_error('shoutbox_invalid_member');

	// Look up member by display name.
	$request = $smcFunc['db_query']('', '
		SELECT id_member, real_name, id_group
		FROM {db_prefix}members
		WHERE real_name = {string:name}
		LIMIT 1',
		array(
			'name' => $username,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		shoutbox_json_error('shoutbox_whisper_not_found');
	}

	$member = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Don't mute admins.
	if ($member['id_group'] == 1)
		shoutbox_json_error('shoutbox_cannot_ban_admin');

	$id_member = (int) $member['id_member'];

	// Remove any existing ban first.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_bans
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => $id_member,
		)
	);

	// Insert permanent ban.
	$smcFunc['db_insert']('insert',
		'{db_prefix}shoutbox_bans',
		array(
			'id_member' => 'int',
			'banned_by' => 'int',
			'reason' => 'string',
			'created_at' => 'int',
			'expires_at' => 'int',
		),
		array(
			$id_member,
			(int) $user_info['id'],
			'',
			time(),
			0,
		),
		array('id_ban')
	);

	shoutbox_json_response(array('success' => true, 'muted_name' => $member['real_name']));
}

/**
 * Unmute (unban) a user by display name.
 * POST ?action=shoutbox;sa=unmute
 */
function ShoutboxUnmute()
{
	global $smcFunc;

	isAllowedTo('shoutbox_moderate');
	checkSession('request');

	$username = isset($_POST['username']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['username'])) : '';

	if (empty($username))
		shoutbox_json_error('shoutbox_invalid_member');

	// Look up member by display name.
	$request = $smcFunc['db_query']('', '
		SELECT id_member, real_name
		FROM {db_prefix}members
		WHERE real_name = {string:name}
		LIMIT 1',
		array(
			'name' => $username,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		shoutbox_json_error('shoutbox_whisper_not_found');
	}

	$member = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_bans
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => (int) $member['id_member'],
		)
	);

	shoutbox_json_response(array('success' => true, 'unmuted_name' => $member['real_name']));
}

/**
 * List all currently muted (banned) users.
 * GET ?action=shoutbox;sa=mutelist
 */
function ShoutboxMutelist()
{
	global $smcFunc;

	isAllowedTo('shoutbox_moderate');

	$request = $smcFunc['db_query']('', '
		SELECT sb.id_ban, sb.id_member, sb.banned_by, sb.reason, sb.created_at, sb.expires_at,
			COALESCE(mem.real_name, {string:unknown}) AS member_name,
			COALESCE(bm.real_name, {string:unknown}) AS banned_by_name
		FROM {db_prefix}shoutbox_bans AS sb
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sb.id_member)
			LEFT JOIN {db_prefix}members AS bm ON (bm.id_member = sb.banned_by)
		WHERE sb.expires_at = 0 OR sb.expires_at > {int:now}
		ORDER BY sb.created_at DESC',
		array(
			'now' => time(),
			'unknown' => 'Unknown',
		)
	);

	$bans = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$bans[] = array(
			'name' => $row['member_name'],
			'reason' => $row['reason'],
			'created_at' => (int) $row['created_at'],
			'expires_at' => (int) $row['expires_at'],
			'banned_by_name' => $row['banned_by_name'],
		);
	}
	$smcFunc['db_free_result']($request);

	shoutbox_json_response(array('bans' => $bans));
}

/**
 * Send an admin-only message (visible to moderators only).
 * POST ?action=shoutbox;sa=admin_msg
 */
function ShoutboxAdminMsg()
{
	global $smcFunc, $modSettings, $user_info;

	isAllowedTo('shoutbox_moderate');
	checkSession('request');

	$body = isset($_POST['body']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['body'])) : '';

	if (empty($body))
		shoutbox_json_error('shoutbox_empty_message');

	$max_length = !empty($modSettings['shoutbox_max_length']) ? (int) $modSettings['shoutbox_max_length'] : 500;
	if ($smcFunc['strlen']($body) > $max_length)
		shoutbox_json_error('shoutbox_too_long');

	// Room scoping.
	$requested_room = isset($_POST['room_id']) ? (int) $_POST['room_id'] : 0;
	if ($requested_room <= 0)
		$requested_room = shoutbox_get_default_room_id();
	$id_room = shoutbox_validate_room_access($requested_room);

	// Flood check.
	shoutbox_flood_check();

	$parsed_body = shoutbox_parse_bbc($body);

	$smcFunc['db_insert']('insert',
		'{db_prefix}shoutbox_messages',
		array(
			'id_member' => 'int',
			'id_room' => 'int',
			'member_name' => 'string',
			'body' => 'string',
			'parsed_body' => 'string',
			'is_whisper' => 'int',
			'whisper_to' => 'int',
			'is_action' => 'int',
			'created_at' => 'int',
		),
		array(
			(int) $user_info['id'],
			$id_room,
			$user_info['name'],
			$body,
			$parsed_body,
			2,
			0,
			0,
			time(),
		),
		array('id_msg')
	);

	shoutbox_invalidate_cache($id_room);
	shoutbox_json_response(array('success' => true));
}

/**
 * Toggle a reaction on a message.
 * POST ?action=shoutbox;sa=react
 */
function ShoutboxReact()
{
	global $smcFunc, $modSettings, $user_info;

	if (empty($modSettings['shoutbox_enable_reactions']))
		shoutbox_json_error('shoutbox_no_permission');

	if ($user_info['is_guest'])
		shoutbox_json_error('shoutbox_no_permission');

	isAllowedTo('shoutbox_post');
	checkSession('request');

	$id_msg = isset($_POST['id_msg']) ? (int) $_POST['id_msg'] : 0;
	$reaction_type = isset($_POST['reaction_type']) ? trim($_POST['reaction_type']) : '';

	if ($id_msg <= 0)
		shoutbox_json_error('shoutbox_invalid_message');

	// Validate reaction type.
	$valid_types = shoutbox_reaction_types();
	if (!isset($valid_types[$reaction_type]))
		shoutbox_json_error('shoutbox_invalid_message');

	// Verify message exists.
	$request = $smcFunc['db_query']('', '
		SELECT id_msg
		FROM {db_prefix}shoutbox_messages
		WHERE id_msg = {int:id_msg}
		LIMIT 1',
		array(
			'id_msg' => $id_msg,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		shoutbox_json_error('shoutbox_invalid_message');
	}
	$smcFunc['db_free_result']($request);

	// Check if user already has this reaction.
	$request = $smcFunc['db_query']('', '
		SELECT id_reaction
		FROM {db_prefix}shoutbox_reactions
		WHERE id_msg = {int:id_msg}
			AND id_member = {int:id_member}
			AND reaction_type = {string:reaction_type}
		LIMIT 1',
		array(
			'id_msg' => $id_msg,
			'id_member' => (int) $user_info['id'],
			'reaction_type' => $reaction_type,
		)
	);

	if ($smcFunc['db_num_rows']($request) > 0)
	{
		// Toggle off - remove existing reaction.
		$row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}shoutbox_reactions
			WHERE id_reaction = {int:id_reaction}',
			array(
				'id_reaction' => (int) $row['id_reaction'],
			)
		);
	}
	else
	{
		$smcFunc['db_free_result']($request);

		// Toggle on - insert new reaction.
		$smcFunc['db_insert']('insert',
			'{db_prefix}shoutbox_reactions',
			array(
				'id_msg' => 'int',
				'id_member' => 'int',
				'reaction_type' => 'string',
				'created_at' => 'int',
			),
			array(
				$id_msg,
				(int) $user_info['id'],
				$reaction_type,
				time(),
			),
			array('id_reaction')
		);
	}

	// Return updated reactions for this message.
	$reactions = shoutbox_get_reactions_for_messages(array($id_msg));
	$msg_reactions = isset($reactions[$id_msg]) ? $reactions[$id_msg] : new stdClass();

	shoutbox_json_response(array(
		'success' => true,
		'reactions' => $msg_reactions,
		'msgId' => $id_msg,
	));
}

/**
 * Proxy GIF search to Tenor, Giphy, or Klipy.
 * GET ?action=shoutbox;sa=gif_proxy;q=search_term
 */
function ShoutboxGifProxy()
{
	global $modSettings;

	isAllowedTo('shoutbox_gif');

	$provider = !empty($modSettings['shoutbox_gif_provider']) ? $modSettings['shoutbox_gif_provider'] : 'none';
	$api_key = !empty($modSettings['shoutbox_gif_api_key']) ? $modSettings['shoutbox_gif_api_key'] : '';

	if ($provider === 'none' || empty($api_key))
		shoutbox_json_error('shoutbox_gif_not_configured');

	$query = isset($_REQUEST['q']) ? trim($_REQUEST['q']) : '';
	$pos = isset($_REQUEST['pos']) ? trim($_REQUEST['pos']) : '';
	$limit = 20;

	if ($provider === 'tenor')
	{
		$params = array(
			'key' => $api_key,
			'client_key' => 'smf_shoutbox',
			'limit' => $limit,
			'contentfilter' => 'medium',
			'media_filter' => 'gif,tinygif',
		);

		if (!empty($query))
		{
			$params['q'] = $query;
			$url = 'https://tenor.googleapis.com/v2/search?' . http_build_query($params);
		}
		else
		{
			$url = 'https://tenor.googleapis.com/v2/featured?' . http_build_query($params);
		}

		if (!empty($pos))
			$url .= '&pos=' . urlencode($pos);
	}
	elseif ($provider === 'giphy')
	{
		$params = array(
			'api_key' => $api_key,
			'limit' => $limit,
			'rating' => 'pg-13',
		);

		if (!empty($query))
		{
			$params['q'] = $query;
			$url = 'https://api.giphy.com/v1/gifs/search?' . http_build_query($params);
		}
		else
		{
			$url = 'https://api.giphy.com/v1/gifs/trending?' . http_build_query($params);
		}

		if (!empty($pos))
			$url .= '&offset=' . (int) $pos;
	}
	elseif ($provider === 'klipy')
	{
		global $user_info;

		$params = array(
				'per_page' => $limit,
				'customer_id' => !empty($user_info['id']) ? (string) $user_info['id'] : 'guest',
			);

		if (!empty($query))
		{
			$params['q'] = $query;
			$url = 'https://api.klipy.com/api/v1/' . urlencode($api_key) . '/gifs/search?' . http_build_query($params);
		}
		else
		{
			$url = 'https://api.klipy.com/api/v1/' . urlencode($api_key) . '/gifs/trending?' . http_build_query($params);
		}

		if (!empty($pos))
			$url .= '&page=' . (int) $pos;
	}
	else
	{
		shoutbox_json_error('shoutbox_gif_not_configured');
	}

	// Make the API request.
	$response = shoutbox_http_request($url);

	if ($response === false)
		shoutbox_json_error('shoutbox_gif_api_error');

	$data = json_decode($response, true);
	if ($data === null)
		shoutbox_json_error('shoutbox_gif_api_error');

	// Normalize the response format.
	$gifs = array();

	if ($provider === 'tenor')
	{
		if (!empty($data['results']))
		{
			foreach ($data['results'] as $gif)
			{
				$gifs[] = array(
					'id' => $gif['id'],
					'thumb' => !empty($gif['media_formats']['tinygif']['url']) ? $gif['media_formats']['tinygif']['url'] : '',
					'full' => !empty($gif['media_formats']['gif']['url']) ? $gif['media_formats']['gif']['url'] : '',
					'alt' => !empty($gif['content_description']) ? $gif['content_description'] : 'GIF',
				);
			}
		}

		$next_pos = !empty($data['next']) ? $data['next'] : '';
	}
	elseif ($provider === 'giphy')
	{
		if (!empty($data['data']))
		{
			foreach ($data['data'] as $gif)
			{
				$gifs[] = array(
					'id' => $gif['id'],
					'thumb' => !empty($gif['images']['fixed_width_small']['url']) ? $gif['images']['fixed_width_small']['url'] : '',
					'full' => !empty($gif['images']['original']['url']) ? $gif['images']['original']['url'] : '',
					'alt' => !empty($gif['title']) ? $gif['title'] : 'GIF',
				);
			}
		}

		$offset = !empty($data['pagination']['offset']) ? (int) $data['pagination']['offset'] : 0;
		$count = !empty($data['pagination']['count']) ? (int) $data['pagination']['count'] : 0;
		$next_pos = (string) ($offset + $count);
	}
	elseif ($provider === 'klipy')
	{
		$klipy_data = !empty($data['data']['data']) ? $data['data']['data'] : array();

		foreach ($klipy_data as $gif)
		{
			$thumb = '';
			if (!empty($gif['file']['xs']['jpg']['url']))
				$thumb = $gif['file']['xs']['jpg']['url'];

			$full = '';
			if (!empty($gif['file']['hd']['gif']['url']))
				$full = $gif['file']['hd']['gif']['url'];
			elseif (!empty($gif['file']['gif']['url']))
				$full = $gif['file']['gif']['url'];

			$gifs[] = array(
				'id' => !empty($gif['slug']) ? $gif['slug'] : '',
				'thumb' => $thumb,
				'full' => $full,
				'alt' => !empty($gif['title']) ? $gif['title'] : 'GIF',
			);
		}

		$has_next = !empty($data['data']['has_next']);
		$current_page = !empty($data['data']['current_page']) ? (int) $data['data']['current_page'] : 1;
		$next_pos = $has_next ? (string) ($current_page + 1) : '';
	}

	shoutbox_json_response(array(
		'gifs' => $gifs,
		'next' => $next_pos,
		'provider' => $provider,
	));
}

/**
 * History page (HTML).
 */
function ShoutboxHistoryPage()
{
	global $sourcedir;

	require_once($sourcedir . '/Shoutbox-History.php');
	ShoutboxHistory();
}

/**
 * History data (AJAX JSON for lazy load/search).
 */
function ShoutboxHistoryData()
{
	global $sourcedir;

	require_once($sourcedir . '/Shoutbox-History.php');
	ShoutboxHistoryAjax();
}

/**
 * Export history as CSV/text.
 */
function ShoutboxExport()
{
	global $sourcedir;

	require_once($sourcedir . '/Shoutbox-History.php');
	ShoutboxExportData();
}

// =========================================================================
// Room Helper Functions
// =========================================================================

/**
 * Get all rooms accessible to the current user.
 * Results are cached for 60 seconds.
 */
function shoutbox_get_rooms()
{
	global $smcFunc, $user_info, $modSettings;

	// Get all rooms from cache or DB.
	$use_cache = empty($modSettings['shoutbox_disable_cache']);
	$all_rooms = $use_cache ? cache_get_data('shoutbox_rooms_all', 60) : null;

	if ($all_rooms === null)
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_room, room_name, room_desc, is_private, allowed_groups, sort_order, is_default
			FROM {db_prefix}shoutbox_rooms
			ORDER BY sort_order ASC, id_room ASC',
			array()
		);

		$all_rooms = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$all_rooms[] = $row;
		$smcFunc['db_free_result']($request);

		if ($use_cache)
			cache_put_data('shoutbox_rooms_all', $all_rooms, 60);
	}

	// Filter by access.
	if ($user_info['is_admin'])
		return $all_rooms;

	$user_groups = !empty($user_info['groups']) ? array_map('intval', $user_info['groups']) : array();

	$accessible = array();
	foreach ($all_rooms as $room)
	{
		if (empty($room['is_private']))
		{
			$accessible[] = $room;
		}
		else
		{
			// Check if user is in one of the allowed groups.
			$allowed = array_filter(array_map('intval', explode(',', $room['allowed_groups'])));
			if (!empty($allowed) && !empty(array_intersect($user_groups, $allowed)))
				$accessible[] = $room;
		}
	}

	return $accessible;
}

/**
 * Get the default room ID.
 */
function shoutbox_get_default_room_id()
{
	global $smcFunc, $modSettings;

	$use_cache = empty($modSettings['shoutbox_disable_cache']);
	$rooms = $use_cache ? cache_get_data('shoutbox_rooms_all', 60) : null;

	if ($rooms === null)
	{
		// Force a full load via shoutbox_get_rooms.
		$rooms_list = shoutbox_get_rooms();

		// Re-read from cache (now populated).
		$rooms = $use_cache ? cache_get_data('shoutbox_rooms_all', 60) : null;
		if ($rooms === null)
			$rooms = $rooms_list;
	}

	foreach ($rooms as $room)
	{
		if (!empty($room['is_default']))
			return (int) $room['id_room'];
	}

	// Fallback: return the first room.
	if (!empty($rooms))
		return (int) $rooms[0]['id_room'];

	return 1;
}

/**
 * Validate that the current user can access a room.
 * Returns the room ID if valid, or the default room ID as fallback.
 */
function shoutbox_validate_room_access($id_room)
{
	$rooms = shoutbox_get_rooms();

	foreach ($rooms as $room)
	{
		if ((int) $room['id_room'] === (int) $id_room)
			return (int) $id_room;
	}

	return shoutbox_get_default_room_id();
}

/**
 * Invalidate room list cache.
 */
function shoutbox_invalidate_room_cache()
{
	cache_put_data('shoutbox_rooms_all', null, 0);
}

// =========================================================================
// Room CRUD Endpoints
// =========================================================================

/**
 * Get list of accessible rooms (JSON).
 * GET ?action=shoutbox;sa=rooms
 */
function ShoutboxRoomsList()
{
	isAllowedTo('shoutbox_view');

	$rooms = shoutbox_get_rooms();
	$result = array();

	foreach ($rooms as $room)
	{
		$result[] = array(
			'id' => (int) $room['id_room'],
			'name' => $room['room_name'],
			'desc' => $room['room_desc'],
			'isPrivate' => !empty($room['is_private']),
			'isDefault' => !empty($room['is_default']),
		);
	}

	shoutbox_json_response(array('rooms' => $result));
}

/**
 * Create a new room.
 * POST ?action=shoutbox;sa=room_create
 */
function ShoutboxRoomCreate()
{
	global $smcFunc;

	isAllowedTo('shoutbox_moderate');
	checkSession('request');

	$room_name = isset($_POST['room_name']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['room_name'])) : '';
	if (empty($room_name))
		shoutbox_json_error('shoutbox_room_name_required');

	$room_desc = isset($_POST['room_desc']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['room_desc'])) : '';
	$is_private = !empty($_POST['is_private']) ? 1 : 0;
	$allowed_groups = '';
	if ($is_private && !empty($_POST['allowed_groups']) && is_array($_POST['allowed_groups']))
		$allowed_groups = implode(',', array_map('intval', $_POST['allowed_groups']));

	$sort_order = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;

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

	shoutbox_invalidate_room_cache();
	shoutbox_json_response(array('success' => true));
}

/**
 * Edit an existing room.
 * POST ?action=shoutbox;sa=room_edit
 */
function ShoutboxRoomEdit()
{
	global $smcFunc;

	isAllowedTo('shoutbox_moderate');
	checkSession('request');

	$id_room = isset($_POST['id_room']) ? (int) $_POST['id_room'] : 0;
	if ($id_room <= 0)
		shoutbox_json_error('shoutbox_invalid_room');

	$room_name = isset($_POST['room_name']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['room_name'])) : '';
	if (empty($room_name))
		shoutbox_json_error('shoutbox_room_name_required');

	$room_desc = isset($_POST['room_desc']) ? $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['room_desc'])) : '';
	$is_private = !empty($_POST['is_private']) ? 1 : 0;
	$allowed_groups = '';
	if ($is_private && !empty($_POST['allowed_groups']) && is_array($_POST['allowed_groups']))
		$allowed_groups = implode(',', array_map('intval', $_POST['allowed_groups']));

	$sort_order = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 0;

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

	shoutbox_invalidate_room_cache();
	shoutbox_json_response(array('success' => true));
}

/**
 * Delete a room.
 * POST ?action=shoutbox;sa=room_delete
 */
function ShoutboxRoomDelete()
{
	global $smcFunc;

	isAllowedTo('shoutbox_moderate');
	checkSession('request');

	$id_room = isset($_POST['id_room']) ? (int) $_POST['id_room'] : 0;
	if ($id_room <= 0)
		shoutbox_json_error('shoutbox_invalid_room');

	// Cannot delete the default room.
	$request = $smcFunc['db_query']('', '
		SELECT is_default
		FROM {db_prefix}shoutbox_rooms
		WHERE id_room = {int:id_room}
		LIMIT 1',
		array(
			'id_room' => $id_room,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		shoutbox_json_error('shoutbox_invalid_room');
	}

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($row['is_default']))
		shoutbox_json_error('shoutbox_cannot_delete_default_room');

	// Delete reactions for messages in this room.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_reactions
		WHERE id_msg IN (SELECT id_msg FROM {db_prefix}shoutbox_messages WHERE id_room = {int:id_room})',
		array(
			'id_room' => $id_room,
		)
	);

	// Delete all messages in this room.
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
	shoutbox_invalidate_cache($id_room);
	shoutbox_json_response(array('success' => true));
}

// =========================================================================
// Reaction Helpers
// =========================================================================

/**
 * Batch-load reactions for a set of message IDs.
 * Returns array(msg_id => array(type => array('count' => N, 'users' => [...], 'reacted' => bool)))
 */
function shoutbox_get_reactions_for_messages($msg_ids)
{
	global $smcFunc, $modSettings, $user_info;

	if (empty($modSettings['shoutbox_enable_reactions']) || empty($msg_ids))
		return array();

	$request = $smcFunc['db_query']('', '
		SELECT sr.id_msg, sr.id_member, sr.reaction_type,
			COALESCE(mem.real_name, {string:unknown}) AS member_name
		FROM {db_prefix}shoutbox_reactions AS sr
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sr.id_member)
		WHERE sr.id_msg IN ({array_int:msg_ids})
		ORDER BY sr.created_at ASC',
		array(
			'msg_ids' => $msg_ids,
			'unknown' => 'Unknown',
		)
	);

	$user_id = (int) $user_info['id'];
	$reactions = array();

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$msg_id = (int) $row['id_msg'];
		$type = $row['reaction_type'];

		if (!isset($reactions[$msg_id]))
			$reactions[$msg_id] = array();

		if (!isset($reactions[$msg_id][$type]))
			$reactions[$msg_id][$type] = array('count' => 0, 'users' => array(), 'reacted' => false);

		$reactions[$msg_id][$type]['count']++;
		$reactions[$msg_id][$type]['users'][] = $row['member_name'];

		if ((int) $row['id_member'] === $user_id)
			$reactions[$msg_id][$type]['reacted'] = true;
	}
	$smcFunc['db_free_result']($request);

	return $reactions;
}

// =========================================================================
// Helper Functions
// =========================================================================

/**
 * Invalidate cached shoutbox message data.
 * Accepts optional room ID to invalidate room-specific caches.
 */
function shoutbox_invalidate_cache($id_room = 0)
{
	// Legacy keys (for backward compat during upgrade).
	cache_put_data('shoutbox_initial_widget', null, 0);
	cache_put_data('shoutbox_initial_chat', null, 0);

	// Room-specific keys.
	if ($id_room > 0)
	{
		cache_put_data('shoutbox_initial_widget_r' . $id_room, null, 0);
		cache_put_data('shoutbox_initial_chat_r' . $id_room, null, 0);
	}
	else
	{
		// If no specific room, invalidate default room cache.
		$default_id = shoutbox_get_default_room_id();
		cache_put_data('shoutbox_initial_widget_r' . $default_id, null, 0);
		cache_put_data('shoutbox_initial_chat_r' . $default_id, null, 0);
	}
}

function shoutbox_json_response($data)
{
	// Clear any existing output buffers.
	while (ob_get_level() > 0)
		@ob_end_clean();

	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($data);
	obExit(false);
}

/**
 * Send a JSON error and exit.
 */
function shoutbox_json_error($error_key)
{
	global $txt;

	loadLanguage('Shoutbox');

	// Clear any existing output buffers.
	while (ob_get_level() > 0)
		@ob_end_clean();

	header('Content-Type: application/json; charset=UTF-8');
	header('HTTP/1.1 400 Bad Request');
	echo json_encode(array(
		'error' => true,
		'message' => !empty($txt[$error_key]) ? $txt[$error_key] : $error_key,
	));
	obExit(false);
}

/**
 * Parse BBCode for a shoutbox message.
 */
function shoutbox_parse_bbc($body)
{
	global $modSettings;

	$disabled = array();

	if (empty($modSettings['shoutbox_enable_bbcode']))
		$disabled = array('b' => true, 'i' => true, 'u' => true, 's' => true, 'url' => true, 'img' => true, 'code' => true, 'quote' => true);

	// Auto-embed image URLs not already in [img] tags (only when img BBCode is enabled).
	if (empty($disabled['img']))
	{
		$body = preg_replace(
			'/(?<!\[img\])(?<!=)(https?:\/\/[^\s\[\]<>"]+\.(?:gif|png|jpe?g|webp)(?:\?[^\s\[\]<>"]*)?)(?!\[\/img\])/i',
			'[img]$1[/img]',
			$body
		);
	}

	// Use SMF's parse_bbc.
	$parsed = parse_bbc($body, !empty($modSettings['shoutbox_enable_smileys']), '', $disabled);

	return $parsed;
}

/**
 * Format a message row for JSON output.
 */
function shoutbox_format_message($row, $reactions = array())
{
	global $scripturl, $modSettings, $settings;

	$avatar_url = '';
	if (!empty($modSettings['shoutbox_show_avatars']) && !empty($row['avatar']))
	{
		if (stripos($row['avatar'], 'http') === 0)
			$avatar_url = $row['avatar'];
		else
			$avatar_url = $settings['images_url'] . '/avatars/' . $row['avatar'];
	}

	return array(
		'id' => (int) $row['id_msg'],
		'memberId' => (int) $row['id_member'],
		'memberName' => $row['real_name'],
		'memberColor' => !empty($row['member_color']) ? $row['member_color'] : '',
		'body' => $row['parsed_body'],
		'isWhisper' => $row['is_whisper'] == 1,
		'isAdmin' => $row['is_whisper'] == 2,
		'whisperTo' => (int) $row['whisper_to'],
		'isAction' => !empty($row['is_action']),
		'createdAt' => (int) $row['created_at'],
		'editedBy' => (int) $row['edited_by'],
		'editedAt' => (int) $row['edited_at'],
		'avatar' => $avatar_url,
		'profileUrl' => $row['id_member'] > 0 ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
		'reactions' => !empty($reactions) ? $reactions : new stdClass(),
	);
}

/**
 * Check flood control.
 */
function shoutbox_flood_check()
{
	global $smcFunc, $modSettings, $user_info;

	if ($user_info['is_admin'] || allowedTo('shoutbox_moderate'))
		return;

	$flood_time = !empty($modSettings['shoutbox_flood_time']) ? (int) $modSettings['shoutbox_flood_time'] : 5;

	$request = $smcFunc['db_query']('', '
		SELECT MAX(created_at) AS last_shout
		FROM {db_prefix}shoutbox_messages
		WHERE id_member = {int:user_id}',
		array(
			'user_id' => (int) $user_info['id'],
		)
	);

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (!empty($row['last_shout']) && (time() - $row['last_shout']) < $flood_time)
		shoutbox_json_error('shoutbox_flood');
}

/**
 * Check if current user is banned from shoutbox.
 */
function shoutbox_check_ban()
{
	global $smcFunc, $user_info, $txt;

	$request = $smcFunc['db_query']('', '
		SELECT id_ban, reason, expires_at
		FROM {db_prefix}shoutbox_bans
		WHERE id_member = {int:user_id}
			AND (expires_at = 0 OR expires_at > {int:now})
		LIMIT 1',
		array(
			'user_id' => (int) $user_info['id'],
			'now' => time(),
		)
	);

	if ($smcFunc['db_num_rows']($request) > 0)
	{
		$ban = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		// If this is an AJAX request, return a JSON error.
		if (isset($_REQUEST['sa']) && $_REQUEST['sa'] !== 'chatroom' && $_REQUEST['sa'] !== 'history_page')
		{
			loadLanguage('Shoutbox');
			shoutbox_json_error('shoutbox_you_are_banned');
		}
	}
	else
	{
		$smcFunc['db_free_result']($request);
	}

	// Clean expired bans.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_bans
		WHERE expires_at > 0 AND expires_at < {int:now}',
		array(
			'now' => time(),
		)
	);
}

/**
 * Auto-prune old messages.
 */
function shoutbox_auto_prune()
{
	global $smcFunc, $modSettings;

	$days = !empty($modSettings['shoutbox_auto_prune_days']) ? (int) $modSettings['shoutbox_auto_prune_days'] : 0;

	if ($days <= 0)
		return;

	$cutoff = time() - ($days * 86400);

	// Delete reactions for old messages.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_reactions
		WHERE id_msg IN (SELECT id_msg FROM {db_prefix}shoutbox_messages WHERE created_at < {int:cutoff})',
		array(
			'cutoff' => $cutoff,
		)
	);

	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}shoutbox_messages
		WHERE created_at < {int:cutoff}',
		array(
			'cutoff' => $cutoff,
		)
	);
}

/**
 * Get list of online users (recently active).
 *
 * Uses cache-based presence tracking per room when SMF cache is available.
 * Falls back to SMF's log_online table when cache is unavailable.
 */
function shoutbox_get_online_users($id_room = 0)
{
	global $smcFunc, $scripturl, $user_info, $modSettings;

	// Cache-based presence requires SMF's cache to be enabled AND our cache not disabled.
	$cache_available = !empty($modSettings['cache_enable']) && empty($modSettings['shoutbox_disable_cache']);

	// Per-room presence tracking via SMF cache (only when cache actually works).
	if ($id_room > 0 && !$user_info['is_guest'] && $cache_available)
	{
		$presence_key = 'shoutbox_presence_r' . $id_room;
		$presence = cache_get_data($presence_key, 60);

		// Validate that cache is actually working: if we saved data before but get null,
		// cache is broken. Use a test key to detect this on first request.
		if ($presence === null)
		{
			// Try a cache round-trip test.
			cache_put_data('shoutbox_cache_test', 'ok', 60);
			$test = cache_get_data('shoutbox_cache_test', 60);

			if ($test !== 'ok')
			{
				// Cache is not functional - fall through to log_online.
				$cache_available = false;
			}
			else
			{
				$presence = array();
			}
		}

		if ($cache_available)
		{
			// Get current user's group color (reuse from cached presence if available).
			$my_color = '';
			if (isset($presence[(int) $user_info['id']]['color']))
			{
				$my_color = $presence[(int) $user_info['id']]['color'];
			}
			else
			{
				$req = $smcFunc['db_query']('', '
					SELECT COALESCE(mg.online_color, pg.online_color, {string:empty}) AS member_color
					FROM {db_prefix}members AS mem
						LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
						LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
					WHERE mem.id_member = {int:user_id}
					LIMIT 1',
					array(
						'user_id' => (int) $user_info['id'],
						'empty' => '',
					)
				);
				if ($row = $smcFunc['db_fetch_assoc']($req))
					$my_color = $row['member_color'];
				$smcFunc['db_free_result']($req);
			}

			// Update current user's presence.
			$presence[(int) $user_info['id']] = array(
				'id' => (int) $user_info['id'],
				'name' => $user_info['name'],
				'color' => $my_color,
				'time' => time(),
			);

			// Prune entries older than 30 seconds.
			$cutoff = time() - 30;
			foreach ($presence as $uid => $entry)
			{
				if ($entry['time'] < $cutoff)
					unset($presence[$uid]);
			}

			cache_put_data($presence_key, $presence, 60);

			// Build user list from presence data.
			$users = array();
			foreach ($presence as $entry)
			{
				$users[] = array(
					'id' => $entry['id'],
					'name' => $entry['name'],
					'color' => !empty($entry['color']) ? $entry['color'] : '',
					'profileUrl' => $scripturl . '?action=profile;u=' . $entry['id'],
				);
			}

			usort($users, function($a, $b) {
				return strcasecmp($a['name'], $b['name']);
			});

			return $users;
		}
	}

	// DB fallback: online users from SMF's log_online (always reliable).
	$users = $cache_available ? cache_get_data('shoutbox_online_users', 1) : null;

	if ($users !== null)
		return $users;

	$request = $smcFunc['db_query']('', '
		SELECT lo.id_member, mem.real_name,
			COALESCE(mg.online_color, pg.online_color, {string:empty}) AS member_color
		FROM {db_prefix}log_online AS lo
			INNER JOIN {db_prefix}members AS mem ON (mem.id_member = lo.id_member)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
			LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
		WHERE lo.id_member > 0
		ORDER BY mem.real_name ASC
		LIMIT 50',
		array(
			'empty' => '',
		)
	);

	$users = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$users[] = array(
			'id' => (int) $row['id_member'],
			'name' => $row['real_name'],
			'color' => !empty($row['member_color']) ? $row['member_color'] : '',
			'profileUrl' => $scripturl . '?action=profile;u=' . $row['id_member'],
		);
	}
	$smcFunc['db_free_result']($request);

	if ($cache_available)
		cache_put_data('shoutbox_online_users', $users, 1);

	return $users;
}

/**
 * Make an HTTP request (for GIF proxy).
 */
function shoutbox_http_request($url)
{
	// Try cURL first.
	if (function_exists('curl_init'))
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'SMF-Shoutbox/1.0');
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode >= 200 && $httpCode < 300)
			return $response;

		return false;
	}

	// Fall back to file_get_contents.
	$context = stream_context_create(array(
		'http' => array(
			'timeout' => 10,
			'user_agent' => 'SMF-Shoutbox/1.0',
		),
	));

	$response = @file_get_contents($url, false, $context);
	return $response !== false ? $response : false;
}
