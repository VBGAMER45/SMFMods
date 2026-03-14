<?php
// Version: 3.0; Blog (SMF 2.1)

// Stuff displayed on top of the blog...
function template_blog_above()
{

}

// ... and stuff displayed below it.
function template_blog_below()
{
	echo '
		<div style="text-align: center" class="smalltext">
			Powered by <a href="https://www.smfhacks.com" title="A (very) simple blogging system for SMF">SMFBlog</a> by <a href="https://www.createaforum.com" title="Forum Hosting">CreateAForum.com</a>
		</div>';
}

// The blog index.
function template_index()
{
	global $context, $scripturl, $txt;

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['blog'], ' - ', $txt['blog_blogs'], '</h3>
		</div>
		<div class="windowbg">';

	// Loop through all blog boards.
	foreach ($context['blog_boards'] as $board)
		echo '
				<div class="blog_listing">
					<a class="blog_link" href="', $scripturl, '?action=blog;sa=view_blog;name=', $board['alias'], '">', $board['name'], '</a>
					<blockquote class="blog_desc">', $board['description'], '</blockquote>
				</div>';

	echo '
		</div>';
}

// Viewing a blog itself.
function template_view()
{
	global $context, $modSettings, $scripturl, $txt;

	echo '
		<h2>', $context['blog']['name'], '</h2>
		', $context['blog']['pageindex'];

	$alternating = 1;
	// Loop through each post.
	foreach ($context['blog']['posts'] as $post)
	{
		// Output this post.
		echo '
		<div class="windowbg blog_post blog_post_', $alternating, '">
			<a class="blog_post_heading" href="', $scripturl, '?action=blog;sa=view_post&id=', $post['id'], (!empty($modSettings['blog_enable_rewrite']) ? ';blog_name=' . $context['blog']['alias'] : ''), '">', $post['icon'], ' ', $post['subject'], '</a>
			<div class="smaller">', $post['time'], ' ', $txt['smfblog_by'], ' ', $post['poster']['link'], '</div>

			<div class="blog_post_body" style="padding: 2ex 0;">', $post['body'], '</div>
		</div>';

		$alternating = ($alternating == 1) ? 2 : 1;
	}

	// Page numbers at the bottom
	echo '
		', $context['blog']['pageindex'];

}

// Viewing a blog post.
function template_view_post()
{
	global $context, $scripturl, $txt;

	echo '
		<h2 class="blog_heading">', $context['blog_post']['subject'], '</h2>';

	// Output the blog post itself.
	echo '
		<div class="windowbg blog_post">
			<div class="blog_post_heading">', $context['blog_post']['icon'], ' ', $context['blog_post']['subject'], '</div>
			<div class="smaller">', $context['blog_post']['time'], ' ', $txt['smfblog_by'], ' ', $context['blog_post']['poster']['link'], '</div>

			<div class="blog_post_body">', $context['blog_post']['body'], '</div><br><br>
		</div>

		<h2 class="blog_heading">', $txt['blog_write_comment'], ':</h2>';
	// Not logged in? No commenting for you!
	if (!$context['user']['is_logged'])
		echo '
		', $txt['blog_error_login'];
	else
		echo '
		<form action="', $scripturl, '?action=post2" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify" onsubmit="submitonce(this);">
			<input type="hidden" name="topic" value="', $context['blog_post']['id'], '">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
			<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">
			<input type="hidden" name="blog_post" value="', $context['blog_post']['id'], '">', (!empty($context['blog_name']) ? '
			<input type="hidden" name="blog_name" value="' . htmlentities($context['blog_name']) . '">' : ''), '

			<div class="roundframe">
				<div>
					<dl id="post_header">
						<dt>
							', $txt['smfblog_subject'], ':
						</dt>
						<dd>
							<input type="text" name="subject" value="', $txt['smfblog_re'], $context['blog_post']['subject'], '" tabindex="', $context['tabindex']++, '" size="80" maxlength="80">
						</dd>
					</dl>
				</div>
				<div id="post_area">
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '
				</div>
				<div id="post_confirm_buttons" class="submitbutton">
					', template_control_richedit_buttons($context['post_box_name']), '
				</div>
			</div>
		</form>';

	echo '
		<a name="comments"></a>
		<h2 class="blog_heading">', $txt['blog_comments'], ':</h2>';

	// No replies? Too bad! :P.
	if ($context['blog_post']['reply_count'] == 0)
		echo '
		', $txt['blog_no_comments'];
	else
	{
		// Page numbers, please!
		echo '
		', $context['blog_post']['pageindex'];

		// Now, go through all replies...
		foreach ($context['blog_post']['replies'] as $post)
		{
			echo '
		<a name="msg', $post['id'], '"></a>', ($post['is_last'] ? '<a name="new"></a>' : ''), '
		<div id="msg', $post['id'], '" class="windowbg blog_reply">
			<div class="blog_reply_heading">', $post['icon'], ' ', $post['subject'], ' (', $txt['ssiTopic_reply'], ' ', $post['number'], ') </div>
			<div class="smaller">', $post['time'], ' ', $txt['smfblog_by'], ' ', $post['poster']['link'], '</div>

			<div class="blog_reply_body">', $post['body'], '</div>
		</div>';
		}

		// Again, some page numbers on the bottom this time!
		echo '
		', $context['blog_post']['pageindex'];
	}
}
?>
