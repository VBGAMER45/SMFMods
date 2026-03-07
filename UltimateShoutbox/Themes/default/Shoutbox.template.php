<?php
/**
 * Ultimate Shoutbox & Chatroom - Templates
 *
 * @package Shoutbox
 * @version 1.1.0
 * By: vbgamer45
 * https://www.smfhacks.com
 */

/**
 * Widget layer - wraps around main content.
 * "above" renders the shoutbox widget before content,
 * "below" renders it after content.
 */
function template_shoutbox_widget_above()
{
	global $context, $modSettings;

	$placement = !empty($modSettings['shoutbox_placement']) ? $modSettings['shoutbox_placement'] : 'top';

	if ($placement === 'top')
		template_shoutbox_widget_html();
}

function template_shoutbox_widget_below()
{
	global $context, $modSettings;

	$placement = !empty($modSettings['shoutbox_placement']) ? $modSettings['shoutbox_placement'] : 'top';

	if ($placement === 'bottom')
		template_shoutbox_widget_html();
}

/**
 * The actual shoutbox widget HTML.
 */
function template_shoutbox_widget_html()
{
	global $context, $txt, $scripturl, $modSettings, $settings;

	$config = $context['shoutbox_config'];

	echo '
	<div id="shoutbox_widget" class="shoutbox-widget roundframe">
		<div class="shoutbox-header">
			<h3 class="shoutbox-title">
				<img src="', $settings['images_url'], '/shoutbox/comments.png" alt="" class="shoutbox-icon" />
				', $txt['shoutbox_title'], '
			</h3>
			<div class="shoutbox-header-actions">';

	if (empty($config['chatroomDisabled']))
		echo '
				<a href="', $scripturl, '?action=shoutbox;sa=history_page" class="shoutbox-btn-link" title="', $txt['shoutbox_view_history'], '">&#128337;</a>
				<a href="', $scripturl, '?action=shoutbox" class="shoutbox-btn-link" title="', $txt['shoutbox_open_chatroom'], '">&#8599;</a>';

	if (!empty($config['enableSounds']))
		echo '
				<button type="button" id="shoutbox_sound_toggle" class="shoutbox-btn-icon" title="', $txt['shoutbox_sound_on'], '">&#128264;</button>';

	echo '
				<button type="button" id="shoutbox_toggle" class="shoutbox-btn-icon" title="', $txt['shoutbox_collapse'], '">&#9660;</button>
			</div>
		</div>

		<div id="shoutbox_body" class="shoutbox-body">
			<div id="shoutbox_messages" class="shoutbox-messages">
				<div class="shoutbox-loading"></div>
			</div>';

	if ($config['canPost'] && !$config['isGuest'])
	{
		if (!empty($config['showBBCToolbar']))
			echo '
			<div id="shoutbox_bbc_toolbar" class="shoutbox-bbc-toolbar">
				<button type="button" class="shoutbox-bbc-btn" data-before="[b]" data-after="[/b]" title="', $txt['shoutbox_bbc_bold'], '"><strong>B</strong></button>
				<button type="button" class="shoutbox-bbc-btn" data-before="[i]" data-after="[/i]" title="', $txt['shoutbox_bbc_italic'], '"><em>I</em></button>
				<button type="button" class="shoutbox-bbc-btn" data-before="[u]" data-after="[/u]" title="', $txt['shoutbox_bbc_underline'], '"><u>U</u></button>
				<button type="button" class="shoutbox-bbc-btn" data-before="[s]" data-after="[/s]" title="', $txt['shoutbox_bbc_strike'], '"><s>S</s></button>
				<button type="button" class="shoutbox-bbc-btn" data-before="[url]" data-after="[/url]" title="', $txt['shoutbox_bbc_url'], '">URL</button>
				<button type="button" class="shoutbox-bbc-btn" data-before="[code]" data-after="[/code]" title="', $txt['shoutbox_bbc_code'], '">&lt;/&gt;</button>
			</div>';

		echo '
			<div class="shoutbox-input-area">
				<div class="shoutbox-input-wrapper">
					<textarea id="shoutbox_input" class="shoutbox-input" placeholder="', $txt['shoutbox_type_message'], '" maxlength="', $config['maxLength'], '" rows="1"></textarea>
					<span id="shoutbox_char_count" class="shoutbox-char-count"></span>
				</div>
				<div class="shoutbox-input-buttons">';

		if (!empty($config['showSmileyPicker']))
			echo '
					<button type="button" id="shoutbox_smiley_btn" class="shoutbox-btn" title="', $txt['shoutbox_smileys'], '">&#9786;</button>';

		if ($config['canGif'] && $config['gifProvider'] !== 'none')
			echo '
					<button type="button" id="shoutbox_gif_btn" class="shoutbox-btn" title="', $txt['shoutbox_gif'], '">GIF</button>';

		echo '
					<button type="button" id="shoutbox_send_btn" class="shoutbox-btn shoutbox-btn-primary" title="', $txt['shoutbox_send'], '">&#10148;</button>
				</div>
			</div>';
	}
	elseif ($config['isGuest'])
	{
		echo '
			<div class="shoutbox-guest-notice">', $txt['shoutbox_guest_notice'], '</div>';
	}

	echo '
		</div>

		<div id="shoutbox_gif_picker" class="shoutbox-gif-picker" style="display:none;">
			<div class="gif-picker-header">
				<input type="text" id="shoutbox_gif_search" class="gif-search-input" placeholder="', $txt['shoutbox_gif_search'], '" autocomplete="off" />
				<button type="button" id="shoutbox_gif_close" class="gif-close-btn">&times;</button>
			</div>
			<div id="shoutbox_gif_results" class="gif-results"></div>
			<div id="shoutbox_gif_loading" class="gif-loading" style="display:none;">', $txt['shoutbox_loading'], '</div>
			<div class="gif-attribution">',
				(!empty($modSettings['shoutbox_gif_provider']) && $modSettings['shoutbox_gif_provider'] === 'giphy')
					? $txt['shoutbox_gif_powered_by_giphy']
					: ((!empty($modSettings['shoutbox_gif_provider']) && $modSettings['shoutbox_gif_provider'] === 'klipy')
						? $txt['shoutbox_gif_powered_by_klipy']
						: $txt['shoutbox_gif_powered_by_tenor']),
			'</div>
		</div>

		<div id="shoutbox_smiley_picker" class="shoutbox-smiley-picker" style="display:none;"></div>
		<div id="shoutbox_mention_dropdown" class="shoutbox-mention-dropdown" style="display:none;"></div>
		<div id="shoutbox_context_menu" class="shoutbox-context-menu" style="display:none;"></div>
	</div>';
}

/**
 * Full-page chatroom template.
 */
function template_shoutbox_chatroom()
{
	global $context, $txt, $scripturl, $modSettings, $settings;

	$config = $context['shoutbox_config'];

	echo '
	<div id="shoutbox_chatroom" class="shoutbox-chatroom">
		<div class="shoutbox-chatroom-header">
			<h2 class="shoutbox-chatroom-title">
				<img src="', $settings['images_url'], '/shoutbox/comments.png" alt="" class="shoutbox-icon" />
				', $txt['shoutbox_chatroom_title'], '
			</h2>
			<div class="shoutbox-chatroom-actions">
				<a href="', $scripturl, '?action=shoutbox;sa=history_page" class="button">', $txt['shoutbox_view_history'], '</a>';

	if (!empty($config['enableSounds']))
		echo '
				<button type="button" id="shoutbox_sound_toggle" class="button" title="', $txt['shoutbox_sound_on'], '">&#128264; ', $txt['shoutbox_sound_on'], '</button>';

	if ($config['canModerate'])
	{
		echo '
				<button type="button" id="shoutbox_prune_btn" class="button">', $txt['shoutbox_prune'], '</button>
				<button type="button" id="shoutbox_clean_btn" class="button">', $txt['shoutbox_clean'], '</button>';
	}

	echo '
			</div>
		</div>';

	// Room tabs (only when more than 1 room).
	if (!empty($context['shoutbox_rooms']) && count($context['shoutbox_rooms']) > 1)
	{
		echo '
		<div id="shoutbox_room_tabs" class="shoutbox-room-tabs">';

		foreach ($context['shoutbox_rooms'] as $room)
		{
			echo '
			<button type="button" class="shoutbox-room-tab', !empty($room['is_default']) ? ' active' : '', '" data-room-id="', $room['id'], '" title="', !empty($room['desc']) ? $room['desc'] : $room['name'], '">',
				!empty($room['is_private']) ? '<span class="shoutbox-room-lock">&#128274;</span> ' : '',
				$room['name'],
			'</button>';
		}

		echo '
		</div>';
	}

	echo '
		<div class="shoutbox-chatroom-layout">
			<div class="shoutbox-chatroom-main">
				<div id="shoutbox_messages" class="shoutbox-messages shoutbox-messages-chatroom">
					<div class="shoutbox-loading"></div>
				</div>';

	if ($config['canPost'] && !$config['isGuest'])
	{
		if (!empty($config['showBBCToolbar']))
			echo '
				<div id="shoutbox_bbc_toolbar" class="shoutbox-bbc-toolbar">
					<button type="button" class="shoutbox-bbc-btn" data-before="[b]" data-after="[/b]" title="', $txt['shoutbox_bbc_bold'], '"><strong>B</strong></button>
					<button type="button" class="shoutbox-bbc-btn" data-before="[i]" data-after="[/i]" title="', $txt['shoutbox_bbc_italic'], '"><em>I</em></button>
					<button type="button" class="shoutbox-bbc-btn" data-before="[u]" data-after="[/u]" title="', $txt['shoutbox_bbc_underline'], '"><u>U</u></button>
					<button type="button" class="shoutbox-bbc-btn" data-before="[s]" data-after="[/s]" title="', $txt['shoutbox_bbc_strike'], '"><s>S</s></button>
					<button type="button" class="shoutbox-bbc-btn" data-before="[url]" data-after="[/url]" title="', $txt['shoutbox_bbc_url'], '">URL</button>
					<button type="button" class="shoutbox-bbc-btn" data-before="[code]" data-after="[/code]" title="', $txt['shoutbox_bbc_code'], '">&lt;/&gt;</button>
				</div>';

		echo '
				<div class="shoutbox-input-area shoutbox-input-area-chatroom">
					<div class="shoutbox-input-wrapper">
						<textarea id="shoutbox_input" class="shoutbox-input" placeholder="', $txt['shoutbox_type_message'], '" maxlength="', $config['maxLength'], '" rows="1"></textarea>
						<span id="shoutbox_char_count" class="shoutbox-char-count"></span>
					</div>
					<div class="shoutbox-input-buttons">';

		if (!empty($config['showSmileyPicker']))
			echo '
						<button type="button" id="shoutbox_smiley_btn" class="shoutbox-btn" title="', $txt['shoutbox_smileys'], '">&#9786;</button>';

		if ($config['canGif'] && $config['gifProvider'] !== 'none')
			echo '
						<button type="button" id="shoutbox_gif_btn" class="shoutbox-btn" title="', $txt['shoutbox_gif'], '">GIF</button>';

		echo '
						<button type="button" id="shoutbox_send_btn" class="shoutbox-btn shoutbox-btn-primary">', $txt['shoutbox_send'], '</button>
					</div>
				</div>';
	}
	elseif ($config['isGuest'])
	{
		echo '
				<div class="shoutbox-guest-notice">', $txt['shoutbox_guest_notice'], '</div>';
	}

	echo '
			</div>

			<div class="shoutbox-chatroom-sidebar">
				<div class="shoutbox-sidebar-section">
					<h4>', $txt['shoutbox_whos_chatting'], '</h4>
					<div id="shoutbox_online_users" class="shoutbox-online-users">
						<div class="shoutbox-loading"></div>
					</div>
				</div>
			</div>
		</div>

		<div id="shoutbox_gif_picker" class="shoutbox-gif-picker" style="display:none;">
			<div class="gif-picker-header">
				<input type="text" id="shoutbox_gif_search" class="gif-search-input" placeholder="', $txt['shoutbox_gif_search'], '" autocomplete="off" />
				<button type="button" id="shoutbox_gif_close" class="gif-close-btn">&times;</button>
			</div>
			<div id="shoutbox_gif_results" class="gif-results"></div>
			<div id="shoutbox_gif_loading" class="gif-loading" style="display:none;">', $txt['shoutbox_loading'], '</div>
			<div class="gif-attribution">',
				(!empty($modSettings['shoutbox_gif_provider']) && $modSettings['shoutbox_gif_provider'] === 'giphy')
					? $txt['shoutbox_gif_powered_by_giphy']
					: ((!empty($modSettings['shoutbox_gif_provider']) && $modSettings['shoutbox_gif_provider'] === 'klipy')
						? $txt['shoutbox_gif_powered_by_klipy']
						: $txt['shoutbox_gif_powered_by_tenor']),
			'</div>
		</div>

		<div id="shoutbox_smiley_picker" class="shoutbox-smiley-picker" style="display:none;"></div>
		<div id="shoutbox_mention_dropdown" class="shoutbox-mention-dropdown" style="display:none;"></div>
		<div id="shoutbox_context_menu" class="shoutbox-context-menu" style="display:none;"></div>
	</div>';
}

/**
 * History/archive page template.
 */
function template_shoutbox_history()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="shoutbox_history" class="shoutbox-history">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['shoutbox_history_title'], '</h3>
		</div>

		<div class="roundframe">
			<form id="shoutbox_history_form" class="shoutbox-history-filters" action="', $scripturl, '?action=shoutbox;sa=history_page" method="get">
				<input type="hidden" name="action" value="shoutbox" />
				<input type="hidden" name="sa" value="history_page" />

				<div class="shoutbox-filter-row">
					<div class="shoutbox-filter-group">
						<label for="shoutbox_search_input">', $txt['shoutbox_search'], '</label>
						<input type="text" id="shoutbox_search_input" name="search" value="', !empty($context['shoutbox_search']) ? $context['shoutbox_search'] : '', '" placeholder="', $txt['shoutbox_search_placeholder'], '" />
					</div>';

	// Room filter dropdown (only when multiple rooms exist).
	if (!empty($context['shoutbox_rooms']) && count($context['shoutbox_rooms']) > 1)
	{
		echo '
					<div class="shoutbox-filter-group">
						<label for="shoutbox_room_filter">', $txt['shoutbox_room'], '</label>
						<select id="shoutbox_room_filter" name="room_id">
							<option value="0">', $txt['shoutbox_all_rooms'], '</option>';

		foreach ($context['shoutbox_rooms'] as $room)
		{
			echo '
							<option value="', $room['id'], '"', (!empty($context['shoutbox_room_filter']) && $context['shoutbox_room_filter'] == $room['id']) ? ' selected' : '', '>', $room['name'], '</option>';
		}

		echo '
						</select>
					</div>';
	}

	echo '
					<div class="shoutbox-filter-group">
						<label for="shoutbox_date_from">', $txt['shoutbox_date_from'], '</label>
						<input type="date" id="shoutbox_date_from" name="date_from" value="', !empty($context['shoutbox_date_from']) ? $context['shoutbox_date_from'] : '', '" />
					</div>

					<div class="shoutbox-filter-group">
						<label for="shoutbox_date_to">', $txt['shoutbox_date_to'], '</label>
						<input type="date" id="shoutbox_date_to" name="date_to" value="', !empty($context['shoutbox_date_to']) ? $context['shoutbox_date_to'] : '', '" />
					</div>

					<div class="shoutbox-filter-actions">
						<button type="submit" class="button">', $txt['shoutbox_filter'], '</button>
						<a href="', $scripturl, '?action=shoutbox;sa=history_page" class="button">', $txt['shoutbox_reset'], '</a>
					</div>
				</div>
			</form>';

	if (allowedTo('shoutbox_moderate'))
	{
		echo '
			<div class="shoutbox-export-actions">
				<a href="', $scripturl, '?action=shoutbox;sa=export;format=csv',
					!empty($context['shoutbox_search']) ? ';search=' . urlencode($context['shoutbox_search']) : '',
					!empty($context['shoutbox_date_from']) ? ';date_from=' . urlencode($context['shoutbox_date_from']) : '',
					!empty($context['shoutbox_date_to']) ? ';date_to=' . urlencode($context['shoutbox_date_to']) : '',
					!empty($context['shoutbox_room_filter']) ? ';room_id=' . (int) $context['shoutbox_room_filter'] : '',
				'" class="button">', $txt['shoutbox_export_csv'], '</a>
				<a href="', $scripturl, '?action=shoutbox;sa=export;format=text',
					!empty($context['shoutbox_search']) ? ';search=' . urlencode($context['shoutbox_search']) : '',
					!empty($context['shoutbox_date_from']) ? ';date_from=' . urlencode($context['shoutbox_date_from']) : '',
					!empty($context['shoutbox_date_to']) ? ';date_to=' . urlencode($context['shoutbox_date_to']) : '',
					!empty($context['shoutbox_room_filter']) ? ';room_id=' . (int) $context['shoutbox_room_filter'] : '',
				'" class="button">', $txt['shoutbox_export_text'], '</a>
			</div>';
	}

	echo '
		</div>

		<div id="shoutbox_history_messages" class="shoutbox-history-messages roundframe">';

	if (!empty($context['shoutbox_messages']))
	{
		foreach ($context['shoutbox_messages'] as $message)
		{
			echo '
			<div class="shoutbox-history-message', !empty($message['isWhisper']) ? ' shoutbox-whisper' : '', !empty($message['isAction']) ? ' shoutbox-action' : '', '">
				<div class="shoutbox-history-meta">
					<span class="shoutbox-history-time">', $message['time_formatted'], '</span>
					<a href="', $message['profileUrl'], '" class="shoutbox-history-author"', !empty($message['memberColor']) ? ' style="color: ' . $message['memberColor'] . '"' : '', '>', $message['memberName'], '</a>
					', !empty($message['isWhisper']) ? '<span class="shoutbox-whisper-badge">' . $txt['shoutbox_whisper_label'] . '</span>' : '', '
				</div>
				<div class="shoutbox-history-body">', $message['body'], '</div>
			</div>';
		}
	}
	else
	{
		echo '
			<div class="shoutbox-no-results">', $txt['shoutbox_no_results'], '</div>';
	}

	echo '
		</div>';

	// Pagination.
	if (!empty($context['shoutbox_page_index']))
	{
		echo '
		<div class="pagesection">
			<div class="pagelinks">', $context['shoutbox_page_index'], '</div>
		</div>';
	}

	echo '
	</div>';
}

/**
 * Admin settings page template.
 */
function template_shoutbox_admin()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">
		<form id="shoutbox_admin_form" action="', $scripturl, '?action=admin;area=shoutbox;sa=settings;save" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['shoutbox_admin_title'], '</h3>
			</div>
		

			<div class="windowbg">
				<dl class="settings">';

	foreach ($context['config_vars'] as $config_var)
	{
		if (is_array($config_var))
		{
			echo '
					<dt>
						<label for="', $config_var['name'], '">', $config_var['label'], '</label>';

			if (!empty($config_var['subtext']))
				echo '
						<br /><span class="smalltext">', $config_var['subtext'], '</span>';

			echo '
					</dt>
					<dd>';

			switch ($config_var['type'])
			{
				case 'check':
					echo '
						<input type="checkbox" name="', $config_var['name'], '" id="', $config_var['name'], '"', !empty($config_var['value']) ? ' checked' : '', ' />';
					break;

				case 'text':
				case 'password':
					echo '
						<input type="', $config_var['type'] === 'password' ? 'password' : 'text', '" name="', $config_var['name'], '" id="', $config_var['name'], '" value="', $config_var['value'], '" size="', !empty($config_var['size']) ? $config_var['size'] : 30, '" />';
					break;

				case 'int':
					echo '
						<input type="number" name="', $config_var['name'], '" id="', $config_var['name'], '" value="', $config_var['value'], '" min="0" size="8" />';
					break;

				case 'select':
					echo '
						<select name="', $config_var['name'], '" id="', $config_var['name'], '">';

					foreach ($config_var['data'] as $option)
						echo '
							<option value="', $option[0], '"', $option[0] == $config_var['value'] ? ' selected' : '', '>', $option[1], '</option>';

					echo '
						</select>';
					break;
			}

			echo '
					</dd>';
		}
		else
		{
			// Section title.
			echo '
				</dl>
				<hr />
				<dl class="settings">
					<dt colspan="2"><strong>', $config_var, '</strong></dt>';
		}
	}

	echo '
				</dl>
			</div>

			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="submit" name="save" value="', $txt['save'], '" class="button" />
		</form>
	</div>';
}

/**
 * Admin rooms management template.
 */
function template_shoutbox_admin_rooms()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">';

	// Error message if any.
	if (!empty($context['shoutbox_error']))
	{
		echo '
		<div class="errorbox">', $context['shoutbox_error'], '</div>';
	}

	// Room create/edit form.
	$editing = !empty($context['shoutbox_edit_room']);
	$room = $editing ? $context['shoutbox_edit_room'] : null;
	$allowed_ids = $editing && !empty($room['allowed_groups']) ? array_map('intval', explode(',', $room['allowed_groups'])) : array();

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $editing ? $txt['shoutbox_edit_room'] : $txt['shoutbox_create_room'], '</h3>
		</div>
		<div class="windowbg">
			<form method="post" action="', $scripturl, '?action=admin;area=shoutbox;sa=rooms', $editing ? ';edit=' . (int) $room['id_room'] : '', '">
				<dl class="settings">
					<dt><label for="room_name">', $txt['shoutbox_room_name'], '</label></dt>
					<dd><input type="text" name="room_name" id="room_name" value="', $editing ? $room['room_name'] : '', '" size="40" maxlength="80" /></dd>

					<dt><label for="room_desc">', $txt['shoutbox_room_desc_label'], '</label></dt>
					<dd><input type="text" name="room_desc" id="room_desc" value="', $editing ? $room['room_desc'] : '', '" size="60" maxlength="255" /></dd>

					<dt><label for="sort_order">', $txt['shoutbox_room_order'], '</label></dt>
					<dd><input type="number" name="sort_order" id="sort_order" value="', $editing ? (int) $room['sort_order'] : '0', '" min="0" size="5" /></dd>

					<dt><label for="is_private">', $txt['shoutbox_room_private'], '</label></dt>
					<dd><input type="checkbox" name="is_private" id="is_private" value="1"', ($editing && !empty($room['is_private'])) ? ' checked' : '', ' /></dd>

					<dt><label>', $txt['shoutbox_room_allowed_groups'], '</label><br /><span class="smalltext">', $txt['shoutbox_room_allowed_groups_desc'], '</span></dt>
					<dd>';

	if (!empty($context['shoutbox_membergroups']))
	{
		foreach ($context['shoutbox_membergroups'] as $group)
		{
			echo '
						<label><input type="checkbox" name="allowed_groups[]" value="', (int) $group['id_group'], '"',
							in_array((int) $group['id_group'], $allowed_ids) ? ' checked' : '',
						' /> ', $group['group_name'], '</label><br />';
		}
	}

	echo '
					</dd>
				</dl>';

	if ($editing)
		echo '
				<input type="hidden" name="id_room" value="', (int) $room['id_room'], '" />';

	echo '
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="submit" name="save_room" value="', $txt['save'], '" class="button" />
				<a href="', $scripturl, '?action=admin;area=shoutbox;sa=rooms" class="button">', $txt['shoutbox_reset'], '</a>
			</form>
		</div>';

	// Room list table.
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['shoutbox_admin_rooms'], '</h3>
		</div>
		<p class="description">', $txt['shoutbox_admin_rooms_desc'], '</p>
		<table class="table_grid" style="width:100%">
			<thead>
				<tr class="title_bar">
					<th>', $txt['shoutbox_room_name'], '</th>
					<th>', $txt['shoutbox_room_desc_label'], '</th>
					<th>', $txt['shoutbox_room_type'], '</th>
					<th>', $txt['shoutbox_room_messages'], '</th>
					<th>', $txt['shoutbox_room_order'], '</th>
					<th>', $txt['shoutbox_actions'], '</th>
				</tr>
			</thead>
			<tbody>';

	if (!empty($context['shoutbox_rooms']))
	{
		foreach ($context['shoutbox_rooms'] as $room)
		{
			echo '
				<tr class="windowbg">
					<td>
						<strong>', $room['room_name'], '</strong>',
						!empty($room['is_default']) ? ' <span class="smalltext">(' . $txt['shoutbox_default'] . ')</span>' : '',
					'</td>
					<td>', !empty($room['room_desc']) ? $room['room_desc'] : '-', '</td>
					<td>', !empty($room['is_private']) ? $txt['shoutbox_private'] : $txt['shoutbox_public'], '</td>
					<td>', (int) $room['msg_count'], '</td>
					<td>', (int) $room['sort_order'], '</td>
					<td>
						<a href="', $scripturl, '?action=admin;area=shoutbox;sa=rooms;edit=', (int) $room['id_room'], '" class="button">', $txt['shoutbox_edit'], '</a>';

			// Cannot delete the default room.
			if (empty($room['is_default']))
			{
				echo '
						<form method="post" action="', $scripturl, '?action=admin;area=shoutbox;sa=rooms" style="display:inline;" onsubmit="return confirm(\'', addslashes($txt['shoutbox_room_delete_confirm']), '\');">
							<input type="hidden" name="id_room" value="', (int) $room['id_room'], '" />
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="submit" name="delete_room" value="', $txt['shoutbox_delete'], '" class="button" />
						</form>';
			}

			echo '
					</td>
				</tr>';
		}
	}
	else
	{
		echo '
				<tr class="windowbg">
					<td colspan="6" style="text-align:center;">', $txt['shoutbox_no_rooms'], '</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>
	</div>';
}
