<?php
// Version: 2.0 RC2; CopyTopic
// CopyTopic
// Mod by karlbenson
// Taken over by JBlaze

// Show an interface for selecting which board to move a post to.
function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<div id="copy_topic" class="lower_padding">
		<form action="', $scripturl, '?action=copytopic2;topic=', $context['current_topic'], '.0" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
			<h3 class="catbg"><span class="left"></span>
				', $txt['copytopic'], '
			</h3>
			<div class="windowbg centertext">
				<span class="topslice"><span></span></span>
				<div class="content">
					<div class="copy_topic">
						<dl class="settings">
							<dt>
								<strong>', $txt['copytopic_copyto'], ':</strong>
							</dt>
							<dd>
								<select name="toboard">';

	// Show dashes (-) before the board name if it's a child.
	foreach ($context['boards'] as $board)
		echo '
						<option value="', $board['id'], '"', $board['selected'] ? ' selected="selected"' : '', '>', $board['category'], ' ', str_repeat('-', 1 + $board['child_level']), ' ', $board['name'], '</option>';
	
	echo '
								</select>
							</dd>';
							
	echo '
						</dl>
					<div class="information"><span class="error">', $txt['copytopic_negativeseo'] ,'</span></div>
					<p><input type="submit" value="', $txt['copytopic'], '" onclick="return submitThisOnce(this);" accesskey="s" /></p>
				</div>
			</div>
			<span class="botslice"><span></span></span>
		</div>';

	if ($context['back_to_topic'])
		echo '
		<input type="hidden" name="goback" value="1" />';

	echo '
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
	</form>';
}

?>