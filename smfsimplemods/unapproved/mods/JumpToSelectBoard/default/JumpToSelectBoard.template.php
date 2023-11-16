<?php
function template_jtsb_tpl_above()
{
	global $context, $txt;

	if (!empty($context['selectBoards']))
	{
		echo '
		<div align="right">
			'.$txt['jump_to'].'
			<select name="post_board" id="post_board_select" onchange="changeUrl()">
				<option value="">---------------------</option>';

			foreach ($context['selectBoards'] as $board)
			echo '
				<option value="', $board['id'], '">', $board['cat']['name'], ' - ', $board['name'], '</option>';

			echo '
			</select>
		</div>';
	}
}

function template_jtsb_tpl_below()
{
}