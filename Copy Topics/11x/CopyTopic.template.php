<?php
// CopyTopic
// Mod by karlbenson
// Taken over by JBlaze

if (!defined('SMF'))
	die('Hacking attempt...');

// Show an interface for selecting which board to move a post to.
function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<form action="', $scripturl, '?action=copytopic2;topic=', $context['current_topic'], '.0" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
		<table border="0" width="400" cellspacing="0" cellpadding="4" align="center" class="tborder">
			<tr class="titlebg">
				<td>', $txt['copytopic'], '</td>
			</tr><tr>
				<td class="windowbg" valign="middle" align="center" style="padding-bottom: 1ex; padding-top: 2ex;">
					<b>', $txt['copytopic_copyto'], ':</b> <select name="toboard">';

	// Show dashes (-) before the board name if it's a child.
	foreach ($context['boards'] as $board)
		echo '
						<option value="', $board['id'], '"', $board['selected'] ? ' selected="selected"' : '', '>', $board['category'], ' ', str_repeat('-', 1 + $board['child_level']), ' ', $board['name'], '</option>';

	echo '
					</select><br />
					<br />', $txt['copytopic_negativeseo'] ,'<br />
					<input type="submit" value="', $txt['copytopic'], '" onclick="return submitThisOnce(this);" accesskey="s" />
				</td>
			</tr>
		</table>';

	if ($context['back_to_topic'])
		echo '
		<input type="hidden" name="goback" value="1" />';

	echo '
		<input type="hidden" name="sc" value="', $context['session_id'], '" />
		<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />
	</form>';
}

?>