<?php
// Version: 0.1 Beta; Blog

// Stuff displayed on top of the blog...
function template_blog_above()
{
	
}

// ... and stuff displayed below it.
function template_blog_below()
{
	// Please do not remove the copyright. If you really must, please contact me
	// Removing the copyright without
	// permission is illegal!
	echo '
		<div style="text-align: center" class="smalltext">
			Powered by <a href="http://www.smfhacks.com" title="A (very) simple blogging system for SMF">SMFBlog</a> by <a href="http://www.createaforum.com" title="Forum Hosting">CreateAForum.com</a>
		</div>';
}

// The blog index.
function template_index()
{
	global $context, $scripturl, $txt;
	
	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 1.5ex;">
			<tr><td class="titlebg">', $txt['blog'], ' - ', $txt['blog_blogs'], '</td></tr>
			<tr valign="top" class="windowbg2">
				<td style="padding-bottom: 2ex;">';
	
	// Loop through all blog boards.
	foreach ($context['blog_boards'] as $board)
		echo '
					<div class="blog_listing">
						<a class="blog_link" href="', $scripturl, '?action=blog;sa=view_blog;name=', $board['alias'], '">', $board['name'], '</a>
						<blockquote class="blog_desc">', $board['description'], '</blockquote>
					</div>';
		
	echo '
				</td>
			</tr>
		</table>';
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
		<div class="windowbg2 blog_post blog_post_', $alternating, '">
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
		<div class="windowbg2 blog_post">
			<div class="blog_post_heading">', $context['blog_post']['icon'], ' ', $context['blog_post']['subject'], '</div>
			<div class="smaller">', $context['blog_post']['time'], ' ', $txt['smfblog_by'], ' ', $context['blog_post']['poster']['link'], '</div>
			
			<div class="blog_post_body">', $context['blog_post']['body'], '</div><br /><br />
		</div>
		
		<h2 class="blog_heading">', $txt['blog_write_comment'], ':</h2>';
	// Not logged in? No commenting for you!
	// !!! Check the permissions!!
	if (!$context['user']['is_logged'])
		echo '
		',$txt['blog_error_login'];
	else
		echo '
		<form action="', $scripturl, '?action=post2" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify" onsubmit="submitonce(this);">
			<input type="hidden" name="topic" value="' . $context['blog_post']['id'] . '" />
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
			<input type="hidden" name="blog_post" value="', $context['blog_post']['id'], '" />', (!empty($context['blog_name']) ? '
			<input type="hidden" name="blog_name" value="' . htmlentities($context['blog_name']) . '" />' : ''), '
			
			<table border="0" width="100%" align="center" cellspacing="1" cellpadding="3"> <!--class="bordercolor"-->
				<tr>
					<td align="right" style="font-weight: bold;" id="caption_subject">
						', $txt['smfblog_subject'], ':
					</td>
					<td>
						<input type="text" name="subject" value="', $txt['smfblog_re'], $context['blog_post']['subject'], '" tabindex="', $context['tabindex']++, '" size="80" maxlength="80" />
					</td>
				</tr>
				<tr>
					<td  colspan="2" align="center">
						<table>
					   ';
					
					 	if (!function_exists('getLanguages'))
						{
							// Showing BBC?
							if ($context['show_bbc'])
							{
								echo '
													<tr class="windowbg2">
						
														<td colspan="2" align="center">
															', template_control_richedit($context['post_box_name'], 'bbc'), '
														</td>
													</tr>';
							}
						
							// What about smileys?
							if (!empty($context['smileys']['postform']))
								echo '
													<tr class="windowbg2">
						
														<td colspan="2" align="center">
															', template_control_richedit($context['post_box_name'], 'smileys'), '
														</td>
													</tr>';
						
							// Show BBC buttons, smileys and textbox.
							echo '
													<tr class="windowbg2">
						
														<td colspan="2" align="center">
															', template_control_richedit($context['post_box_name'], 'message'), '
														</td>
													</tr>';
						}
						else 
						{
							echo '
													<tr class="windowbg2">
							<td>';
								// Showing BBC?
							if ($context['show_bbc'])
							{
								echo '
										<div id="bbcBox_message"></div>';
							}
						
							// What about smileys?
							if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
								echo '
										<div id="smileyBox_message"></div>';
						
							// Show BBC buttons, smileys and textbox.
							echo '
										', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
					
							
							echo '</td></tr>';
						}
					
			
			echo '
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" colspan="2">
						<span class="smalltext"><br />', $txt['smfblog_shortcuts'], '</span><br />
						<input type="submit" name="post" value="', $txt['smfblog_save'], '" tabindex="', $context['tabindex']++, '" onclick="return submitThisOnce(this);" accesskey="s" />
						<input type="submit" name="preview" value="', $txt['smfblog_preview'], '" tabindex="', $context['tabindex']++, '" onclick="return event.ctrlKey || previewPost();" accesskey="p" />
					</td>
				</tr>
				<tr>
					<td colspan="2"></td>
				</tr>
			</table>
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
		<div id="msg', $post['id'], '" class="windowbg2 blog_reply">
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
