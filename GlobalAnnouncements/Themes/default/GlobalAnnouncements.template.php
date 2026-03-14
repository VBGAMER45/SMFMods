<?php

/**
 * Global Announcements - Template functions
 *
 * @package GlobalAnnouncements
 * @license MIT
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * View a single announcement with comments.
 */
function template_globalannouncements_view()
{
	global $context, $txt, $scripturl;

	$announcement = $context['announcement'];

	echo '
	<div class="global_announcement_view">
		<div class="cat_bar">
			<h3 class="catbg">', $announcement['title'], '</h3>
		</div>
		<div class="windowbg">
			<div class="global_announcement_view_meta">
				', $txt['globalannouncements_posted_by'], ' ', $announcement['author']['link'], '
				', $txt['globalannouncements_posted_on'], ' ', $announcement['created_at'];

	if (!empty($announcement['updated_at']))
		echo ' | ', $txt['globalannouncements_last_updated'], ': ', $announcement['updated_at'];

	echo '
			</div>
			<div class="global_announcement_view_body">
				', $announcement['body'], '
			</div>
			<div class="global_announcement_view_stats">
				', $txt['globalannouncements_views'], ': ', $announcement['views'], '
				 | ', $txt['globalannouncements_comments'], ': ', $announcement['num_comments'], '
			</div>
		</div>
	</div>';

	// Comments section.
	if ($announcement['allow_comments'] || !empty($context['comments']))
	{
		echo '
	<div class="global_announcement_comments">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['globalannouncements_comments_header'], '</h3>
		</div>';

		if (empty($context['comments']))
		{
			echo '
		<div class="windowbg">
			', $txt['globalannouncements_no_comments'], '
		</div>';
		}
		else
		{
			foreach ($context['comments'] as $comment)
			{
				echo '
		<div class="windowbg global_announcement_comment">
			<div class="global_announcement_comment_header">
				', $txt['globalannouncements_comment_by'], ' ', $comment['author']['link'], ' - ', $comment['created_at'], '
			</div>
			<div class="global_announcement_comment_body">
				', $comment['body'], '
			</div>';

				if (!empty($comment['modified_by']) && !empty($comment['updated_at']))
					echo '
			<div class="global_announcement_comment_modified">
				', sprintf($txt['globalannouncements_modified_by'], $comment['modified_by'], $comment['updated_at']), '
			</div>';

				if ($comment['can_edit'] || $comment['can_delete'])
				{
					echo '
			<div class="global_announcement_comment_actions">';

					if ($comment['can_edit'])
						echo '
				<a href="', $scripturl, '?action=globalannouncements;sa=editcomment;cid=', $comment['id'], '">', $txt['globalannouncements_edit_comment'], '</a>';

					if ($comment['can_edit'] && $comment['can_delete'])
						echo ' | ';

					if ($comment['can_delete'])
						echo '
				<a href="', $scripturl, '?action=globalannouncements;sa=deletecomment;cid=', $comment['id'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['globalannouncements_confirm_delete_comment'], '\');">', $txt['globalannouncements_delete_comment'], '</a>';

					echo '
			</div>';
				}

				echo '
		</div>';
			}
		}

		// Pagination.
		if (!empty($context['page_index']))
			echo '
		<div class="pagesection">
			<div class="pagelinks">', $context['page_index'], '</div>
		</div>';

		// Comment form.
		if (!empty($context['can_comment']))
		{
			echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['globalannouncements_add_comment'], '</h3>
		</div>
		<div class="windowbg">
			<form action="', $scripturl, '?action=globalannouncements;sa=comment;aid=', $context['announcement']['id'], '" method="post" accept-charset="', $context['character_set'], '">
				<div>', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '</div>
				<div style="margin-top: 8px;">
					<input type="submit" value="', $txt['globalannouncements_post_comment'], '" class="button">
				</div>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			</form>
		</div>';
		}

		echo '
	</div>';
	}
}

/**
 * Edit comment form.
 */
function template_globalannouncements_edit_comment()
{
	global $context, $txt, $scripturl;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['globalannouncements_edit_comment'], '</h3>
	</div>
	<div class="windowbg">
		<form action="', $scripturl, '?action=globalannouncements;sa=editcomment;cid=', $context['comment']['id_comment'], '" method="post" accept-charset="', $context['character_set'], '">
			<div>', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '</div>
			<div style="margin-top: 8px;">
				<input type="submit" name="save" value="', $txt['globalannouncements_save_comment'], '" class="button">
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</form>
	</div>';
}

/**
 * Admin add/edit form.
 */
function template_globalannouncements_admin_edit()
{
	global $context, $txt, $scripturl;

	$announcement = $context['announcement'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $announcement['id'] > 0 ? $txt['globalannouncements_edit_title'] : $txt['globalannouncements_add_title'], '</h3>
	</div>
	<div class="windowbg">
		<form action="', $scripturl, '?action=admin;area=globalannouncements;sa=save', $announcement['id'] > 0 ? ';aid=' . $announcement['id'] : '', '" method="post" accept-charset="', $context['character_set'], '">
			<dl class="settings">
				<dt>
					<strong>', $txt['globalannouncements_field_title'], ':</strong>
				</dt>
				<dd>
					<input type="text" name="title" value="', $announcement['title'], '" size="60" class="input_text">
				</dd>

				<dt>
					<strong>', $txt['globalannouncements_field_body'], ':</strong>
				</dt>
				<dd>
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '
				</dd>

				<dt>
					<strong>', $txt['globalannouncements_field_enabled'], ':</strong>
				</dt>
				<dd>
					<input type="checkbox" name="enabled" value="1"', $announcement['enabled'] ? ' checked' : '', '>
				</dd>

				<dt>
					<strong>', $txt['globalannouncements_field_allow_comments'], ':</strong>
				</dt>
				<dd>
					<input type="checkbox" name="allow_comments" value="1"', $announcement['allow_comments'] ? ' checked' : '', '>
				</dd>

				<dt>
					<strong>', $txt['globalannouncements_field_sort_order'], ':</strong>
					<br><span class="smalltext">', $txt['globalannouncements_field_sort_order_desc'], '</span>
				</dt>
				<dd>
					<input type="number" name="sort_order" value="', $announcement['sort_order'], '" size="5" class="input_text">
				</dd>

				<dt>
					<strong>', $txt['globalannouncements_field_boards'], ':</strong>
					<br><span class="smalltext">', $txt['globalannouncements_field_boards_desc'], '</span>
				</dt>
				<dd>
					<label><input type="checkbox" onclick="var cb = this.closest(\'dd\').querySelectorAll(\'input[name=\\\'boards[]\\\']\'); for (var i = 0; i < cb.length; i++) cb[i].checked = this.checked;"> <em>', $txt['check_all'], '</em></label>
					<br>';

	$current_cat = '';
	foreach ($context['boards_list'] as $board)
	{
		if ($board['cat_name'] !== $current_cat)
		{
			if (!empty($current_cat))
				echo '<br>';
			echo '<strong>', $board['cat_name'], '</strong><br>';
			$current_cat = $board['cat_name'];
		}

		echo '
					<label><input type="checkbox" name="boards[]" value="', $board['id_board'], '"', in_array($board['id_board'], $announcement['boards']) ? ' checked' : '', '> ', $board['name'], '</label><br>';
	}

	echo '
				</dd>

				<dt>
					<strong>', $txt['globalannouncements_field_groups'], ':</strong>
					<br><span class="smalltext">', $txt['globalannouncements_field_groups_desc'], '</span>
				</dt>
				<dd>
					<label><input type="checkbox" onclick="var cb = this.closest(\'dd\').querySelectorAll(\'input[name=\\\'groups[]\\\']\'); for (var i = 0; i < cb.length; i++) cb[i].checked = this.checked;"> <em>', $txt['check_all'], '</em></label>
					<br>';

	foreach ($context['groups_list'] as $group)
	{
		echo '
					<label><input type="checkbox" name="groups[]" value="', $group['id_group'], '"', in_array($group['id_group'], $announcement['groups']) ? ' checked' : '', '> ', $group['group_name'], '</label><br>';
	}

	echo '
				</dd>
			</dl>

			<input type="submit" value="', $txt['globalannouncements_save'], '" class="button">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</form>
	</div>';
}

/**
 * Admin view log.
 */
function template_globalannouncements_admin_log()
{
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', sprintf($txt['globalannouncements_log_for'], $context['announcement_title']), '</h3>
	</div>';

	if (empty($context['log_entries']))
	{
		echo '
	<div class="windowbg">
		', $txt['globalannouncements_log_no_entries'], '
	</div>';
	}
	else
	{
		echo '
	<table class="table_grid" style="width: 100%;">
		<thead>
			<tr class="title_bar">
				<th>', $txt['globalannouncements_log_member'], '</th>
				<th>', $txt['globalannouncements_log_viewed_at'], '</th>
			</tr>
		</thead>
		<tbody>';

		foreach ($context['log_entries'] as $entry)
		{
			echo '
			<tr class="windowbg">
				<td>', $entry['member']['link'], '</td>
				<td>', $entry['viewed_at'], '</td>
			</tr>';
		}

		echo '
		</tbody>
	</table>';

		if (!empty($context['page_index']))
			echo '
	<div class="pagesection">
		<div class="pagelinks">', $context['page_index'], '</div>
	</div>';
	}
}

/**
 * Convert announcement to topic form.
 */
function template_globalannouncements_make_topic()
{
	global $context, $txt, $scripturl;

	$announcement = $context['announcement'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['globalannouncements_make_topic_title'], '</h3>
	</div>
	<div class="windowbg">
		<form action="', $scripturl, '?action=admin;area=globalannouncements;sa=maketopic;aid=', $announcement['id'], '" method="post" accept-charset="', $context['character_set'], '">
			<p><strong>', $announcement['title'], '</strong></p>
			<dl class="settings">
				<dt>
					<strong>', $txt['globalannouncements_make_topic_board'], ':</strong>
				</dt>
				<dd>
					<select name="board_id">';

	foreach ($context['boards_list'] as $board)
		echo '
						<option value="', $board['id_board'], '">', $board['cat_name'], ' - ', $board['name'], '</option>';

	echo '
					</select>
				</dd>

				<dt>
					<strong>', $txt['globalannouncements_make_topic_sticky'], ':</strong>
				</dt>
				<dd>
					<input type="checkbox" name="sticky" value="1">
				</dd>

				<dt>
					<strong>', $txt['globalannouncements_make_topic_locked'], ':</strong>
				</dt>
				<dd>
					<input type="checkbox" name="locked" value="1">
				</dd>';

	if ($announcement['num_comments'] > 0)
	{
		echo '
				<dt>
					<strong>', $txt['globalannouncements_make_topic_comments'], ':</strong>
				</dt>
				<dd>
					<input type="checkbox" name="convert_comments" value="1" checked>
					(', $announcement['num_comments'], ' ', $txt['globalannouncements_comments'], ')
				</dd>';
	}

	echo '
			</dl>

			<input type="submit" name="convert" value="', $txt['globalannouncements_convert'], '" class="button">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</form>
	</div>';
}

/**
 * Convert topic to announcement form.
 */
function template_globalannouncements_from_topic()
{
	global $context, $txt, $scripturl;

	$topic = $context['topic_info'];

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['globalannouncements_from_topic_title'], '</h3>
	</div>
	<div class="windowbg">
		<form action="', $scripturl, '?action=globalannouncements;sa=fromtopic;topic=', $topic['id'], '" method="post" accept-charset="', $context['character_set'], '">
			<p><strong>', $topic['subject'], '</strong></p>
			<dl class="settings">';

	if ($topic['num_replies'] > 0)
	{
		echo '
				<dt>
					<strong>', $txt['globalannouncements_from_topic_replies'], ':</strong>
				</dt>
				<dd>
					<input type="checkbox" name="convert_replies" value="1" checked>
					(', $topic['num_replies'], ' ', $txt['globalannouncements_comments'], ')
				</dd>';
	}

	echo '
			</dl>

			<input type="submit" name="convert" value="', $txt['globalannouncements_convert'], '" class="button">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
		</form>
	</div>';
}

/**
 * Render announcement bar on message index (above topics).
 */
function template_globalannouncements_index_bar()
{
	global $context, $txt, $modSettings, $settings;

	if (empty($context['global_announcements']))
		return;

	echo '
	<div class="global_announcement_bar">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['globalannouncements_title'], '</h3>
		</div>';

	foreach ($context['global_announcements'] as $announcement)
	{
		echo '
		<div class="windowbg">
			<div class="board_icon">
				<img src="', $settings['images_url'], '/post/exclamation.png" alt="">
			</div>
			<div class="info">
				<div>
					<div class="message_index_title">
						<span><a href="', $announcement['href'], '">', $announcement['title'], '</a></span>
					</div>
					<p class="floatleft">
						', $txt['globalannouncements_posted_by'], ' <a href="', $announcement['author']['href'], '">', $announcement['author']['name'], '</a>
					</p>
				</div>
			</div>
			<div class="board_stats centertext">
				<p>
					', $txt['globalannouncements_comments'], ': ', $announcement['comments'], '<br>
					', $txt['globalannouncements_views'], ': ', $announcement['views'], '
				</p>
			</div>
			<div class="lastpost">
				<p>', timeformat($announcement['created_at']), '</p>
			</div>
		</div>';
	}

	echo '
	</div>';

	if (!empty($modSettings['globalannouncements_sticky_bar']))
		echo '
	<div class="global_announcement_separator"></div>';
}