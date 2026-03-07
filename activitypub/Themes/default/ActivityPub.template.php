<?php
/**
 * ActivityPub Federation - Templates
 *
 * Admin panel and user profile templates.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

/**
 * Admin Settings page template.
 */
function template_activitypub_admin_settings()
{
	global $context, $txt, $scripturl;

	$s = $context['ap_settings'];

	echo '
	<div id="admincenter">
		<form action="', $scripturl, '?action=admin;area=activitypub;sa=settings" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['activitypub_settings'], '</h3>
			</div>
			<div class="windowbg">
				<dl class="settings">
					<dt>
						<label for="activitypub_enabled">', $txt['activitypub_enabled'], '</label>
						<br><span class="smalltext">', $txt['activitypub_enabled_desc'], '</span>
					</dt>
					<dd>
						<input type="checkbox" name="activitypub_enabled" id="activitypub_enabled" value="1"', $s['enabled'] ? ' checked' : '', '>
					</dd>

					<dt>
						<label for="activitypub_auto_accept_follows">', $txt['activitypub_auto_accept_follows'], '</label>
						<br><span class="smalltext">', $txt['activitypub_auto_accept_follows_desc'], '</span>
					</dt>
					<dd>
						<input type="checkbox" name="activitypub_auto_accept_follows" id="activitypub_auto_accept_follows" value="1"', $s['auto_accept_follows'] ? ' checked' : '', '>
					</dd>

					<dt>
						<label for="activitypub_user_actors_enabled">', $txt['activitypub_user_actors_enabled'], '</label>
						<br><span class="smalltext">', $txt['activitypub_user_actors_enabled_desc'], '</span>
					</dt>
					<dd>
						<input type="checkbox" name="activitypub_user_actors_enabled" id="activitypub_user_actors_enabled" value="1"', $s['user_actors_enabled'] ? ' checked' : '', '>
					</dd>

					<dt>
						<label for="activitypub_user_opt_in">', $txt['activitypub_user_opt_in'], '</label>
						<br><span class="smalltext">', $txt['activitypub_user_opt_in_desc'], '</span>
					</dt>
					<dd>
						<input type="checkbox" name="activitypub_user_opt_in" id="activitypub_user_opt_in" value="1"', $s['user_opt_in'] ? ' checked' : '', '>
					</dd>

					<dt>
						<label for="activitypub_content_mode">', $txt['activitypub_content_mode'], '</label>
						<br><span class="smalltext">', $txt['activitypub_content_mode_desc'], '</span>
					</dt>
					<dd>
						<select name="activitypub_content_mode" id="activitypub_content_mode">
							<option value="note"', $s['content_mode'] === 'note' ? ' selected' : '', '>', $txt['activitypub_content_mode_note'], '</option>
							<option value="article"', $s['content_mode'] === 'article' ? ' selected' : '', '>', $txt['activitypub_content_mode_article'], '</option>
						</select>
					</dd>

					<dt>
						<label for="activitypub_max_delivery_attempts">', $txt['activitypub_max_delivery_attempts'], '</label>
						<br><span class="smalltext">', $txt['activitypub_max_delivery_attempts_desc'], '</span>
					</dt>
					<dd>
						<input type="number" name="activitypub_max_delivery_attempts" id="activitypub_max_delivery_attempts" value="', $s['max_delivery_attempts'], '" min="1" max="20" size="5">
					</dd>

					<dt>
						<label for="activitypub_delivery_batch_size">', $txt['activitypub_delivery_batch_size'], '</label>
						<br><span class="smalltext">', $txt['activitypub_delivery_batch_size_desc'], '</span>
					</dt>
					<dd>
						<input type="number" name="activitypub_delivery_batch_size" id="activitypub_delivery_batch_size" value="', $s['delivery_batch_size'], '" min="10" max="200" size="5">
					</dd>

					<dt>
						<label for="activitypub_rate_limit_inbox">', $txt['activitypub_rate_limit_inbox'], '</label>
						<br><span class="smalltext">', $txt['activitypub_rate_limit_inbox_desc'], '</span>
					</dt>
					<dd>
						<input type="number" name="activitypub_rate_limit_inbox" id="activitypub_rate_limit_inbox" value="', $s['rate_limit_inbox'], '" min="10" max="10000" size="5">
					</dd>
				</dl>

				<input type="submit" name="save" value="', $txt['save'], '" class="button">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			</div>
		</form>
	</div>';
}

/**
 * Board Federation settings template.
 */
function template_activitypub_admin_boards()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">
		<form action="', $scripturl, '?action=admin;area=activitypub;sa=boards" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['activitypub_board_settings'], '</h3>
			</div>
			<p class="information">', $txt['activitypub_board_settings_desc'], '</p>
			<table class="table_grid ap-board-table">
				<thead>
					<tr class="title_bar">
						<th>', $txt['activitypub_board_enabled'], '</th>
						<th>Board</th>
						<th>Category</th>
						<th>', $txt['activitypub_board_handle'], '</th>
						<th>', $txt['activitypub_board_followers'], '</th>
					</tr>
				</thead>
				<tbody>';

	if (empty($context['ap_boards']))
	{
		echo '
					<tr class="windowbg">
						<td colspan="5">No boards found.</td>
					</tr>';
	}
	else
	{
		foreach ($context['ap_boards'] as $board)
		{
			echo '
					<tr class="windowbg">
						<td>';

			if ($board['is_public'])
			{
				echo '<input type="hidden" name="board_enabled[', $board['id'], ']" value="0">
							<input type="checkbox" name="board_enabled[', $board['id'], ']" value="1"', $board['enabled'] ? ' checked' : '', '>';
			}
			else
			{
				echo '<span class="smalltext">', $txt['activitypub_board_private'], '</span>';
			}

			echo '</td>
						<td>', htmlspecialchars($board['name']), '</td>
						<td>', htmlspecialchars($board['category']), '</td>
						<td>';

			if ($board['is_public'])
				echo '<code>@', htmlspecialchars($board['handle']), '</code>';
			else
				echo '-';

			echo '</td>
						<td>', $board['followers'], '</td>
					</tr>';
		}
	}

	echo '
				</tbody>
			</table>
			<div class="windowbg">
				<input type="submit" name="save" value="', $txt['save'], '" class="button">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			</div>
		</form>
	</div>';
}

/**
 * Domain Blocks template.
 */
function template_activitypub_admin_blocks()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['activitypub_blocks'], '</h3>
		</div>';

	// Add block form.
	echo '
		<form action="', $scripturl, '?action=admin;area=activitypub;sa=blocks" method="post" accept-charset="', $context['character_set'], '">
			<div class="windowbg">
				<dl class="settings">
					<dt><label for="block_domain">Domain</label></dt>
					<dd><input type="text" name="block_domain" id="block_domain" size="40" placeholder="example.com"></dd>
					<dt><label for="block_type">', $txt['activitypub_block_type'], '</label></dt>
					<dd>
						<select name="block_type" id="block_type">
							<option value="block">', $txt['activitypub_block_type_block'], '</option>
							<option value="silence">', $txt['activitypub_block_type_silence'], '</option>
						</select>
					</dd>
					<dt><label for="block_reason">', $txt['activitypub_block_reason'], '</label></dt>
					<dd><input type="text" name="block_reason" id="block_reason" size="60"></dd>
				</dl>
				<input type="submit" name="add_block" value="', $txt['activitypub_block_add'], '" class="button">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			</div>
		</form>';

	// Current blocks list.
	echo '
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>Domain</th>
					<th>Type</th>
					<th>', $txt['activitypub_block_reason'], '</th>
					<th>Added by</th>
					<th>Date</th>
					<th></th>
				</tr>
			</thead>
			<tbody>';

	if (empty($context['ap_blocks']))
	{
		echo '
				<tr class="windowbg"><td colspan="6">', $txt['activitypub_block_none'], '</td></tr>';
	}
	else
	{
		foreach ($context['ap_blocks'] as $block)
		{
			echo '
				<tr class="windowbg">
					<td><strong>', htmlspecialchars($block['domain']), '</strong></td>
					<td>', htmlspecialchars($block['block_type']), '</td>
					<td>', htmlspecialchars($block['reason']), '</td>
					<td>', htmlspecialchars($block['created_by_name'] ?? 'Unknown'), '</td>
					<td>', !empty($block['created_at']) ? date('Y-m-d', $block['created_at']) : '-', '</td>
					<td>
						<form action="', $scripturl, '?action=admin;area=activitypub;sa=blocks" method="post" style="display:inline">
							<input type="hidden" name="remove_id" value="', $block['id_block'], '">
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
							<input type="submit" name="remove_block" value="', $txt['activitypub_block_remove'], '" class="button" onclick="return confirm(\'Remove this block?\');">
						</form>
					</td>
				</tr>';
		}
	}

	echo '
			</tbody>
		</table>
	</div>';
}

/**
 * Status Dashboard template.
 */
function template_activitypub_admin_status()
{
	global $context, $txt;

	$s = $context['ap_status'];

	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['activitypub_status_title'], '</h3>
		</div>

		<div class="windowbg">
			<div class="ap-status-grid">
				<div class="ap-status-card">
					<h4>Federation</h4>
					<p class="ap-status-value">', $s['enabled'] ? $txt['activitypub_status_enabled'] : $txt['activitypub_status_disabled'], '</p>
					<p class="smalltext">Domain: <code>', htmlspecialchars($s['domain']), '</code></p>
				</div>
				<div class="ap-status-card">
					<h4>', $txt['activitypub_status_federated_boards'], '</h4>
					<p class="ap-status-value ap-big-number">', (int) $s['federated_boards'], '</p>
				</div>
				<div class="ap-status-card">
					<h4>', $txt['activitypub_status_total_followers'], '</h4>
					<p class="ap-status-value ap-big-number">', (int) $s['total_followers'], '</p>
				</div>
			</div>
		</div>

		<div class="cat_bar">
			<h3 class="catbg">', $txt['activitypub_status_queue_stats'], '</h3>
		</div>
		<div class="windowbg">
			<div class="ap-status-grid">
				<div class="ap-status-card">
					<h4>', $txt['activitypub_status_queued'], '</h4>
					<p class="ap-status-value ap-big-number">', $s['queue']['queued'] + ($s['queue']['processing'] ?? 0), '</p>
				</div>
				<div class="ap-status-card">
					<h4>', $txt['activitypub_status_delivered'], '</h4>
					<p class="ap-status-value ap-big-number ap-color-success">', $s['queue']['delivered'], '</p>
				</div>
				<div class="ap-status-card">
					<h4>', $txt['activitypub_status_failed'], '</h4>
					<p class="ap-status-value ap-big-number ap-color-warning">', $s['queue']['failed'], '</p>
				</div>
				<div class="ap-status-card">
					<h4>', $txt['activitypub_status_abandoned'], '</h4>
					<p class="ap-status-value ap-big-number ap-color-error">', $s['queue']['abandoned'], '</p>
				</div>
			</div>
		</div>

		<div class="cat_bar">
			<h3 class="catbg">', $txt['activitypub_status_recent_activities'], '</h3>
		</div>';

	if (empty($s['recent_activities']))
	{
		echo '
		<div class="windowbg">', $txt['activitypub_status_no_activities'], '</div>';
	}
	else
	{
		echo '
		<table class="table_grid">
			<thead>
				<tr class="title_bar">
					<th>Type</th>
					<th>Direction</th>
					<th>Status</th>
					<th>Actor</th>
					<th>Object</th>
					<th>Time</th>
				</tr>
			</thead>
			<tbody>';

		foreach ($s['recent_activities'] as $act)
		{
			$status_class = '';
			if ($act['status'] === 'completed')
				$status_class = 'ap-color-success';
			elseif ($act['status'] === 'failed')
				$status_class = 'ap-color-error';

			echo '
				<tr class="windowbg">
					<td><strong>', htmlspecialchars($act['type']), '</strong></td>
					<td>', $act['direction'] === 'inbound' ? $txt['activitypub_activity_inbound'] : $txt['activitypub_activity_outbound'], '</td>
					<td class="', $status_class, '">', htmlspecialchars($act['status']), '</td>
					<td>', htmlspecialchars($act['actor_name']), '</td>
					<td>', htmlspecialchars($act['object_type']), '</td>
					<td>', !empty($act['created_at']) ? date('Y-m-d H:i', $act['created_at']) : '-', '</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}

	echo '
	</div>';
}

/**
 * User Profile ActivityPub template.
 */
function template_activitypub_profile()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['activitypub_profile'], '</h3>
	</div>
	<p class="information">', $txt['activitypub_profile_desc'], '</p>

	<form action="', $scripturl, '?action=profile;area=activitypub;u=', $context['member']['id'], '" method="post" accept-charset="', $context['character_set'], '">
		<div class="windowbg">
			<dl class="settings">
				<dt>
					<label for="ap_user_enabled">', $txt['activitypub_profile_enabled'], '</label>
				</dt>
				<dd>
					<input type="checkbox" name="ap_user_enabled" id="ap_user_enabled" value="1"', $context['ap_user_enabled'] ? ' checked' : '', '>
				</dd>';

	if (!empty($context['ap_user_handle']))
	{
		echo '
				<dt>', $txt['activitypub_profile_handle'], '</dt>
				<dd><code>@', htmlspecialchars($context['ap_user_handle']), '</code></dd>';
	}

	echo '
			</dl>
			<input type="submit" name="ap_save" value="', $txt['activitypub_profile_save'], '" class="button">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</div>
	</form>';
}
