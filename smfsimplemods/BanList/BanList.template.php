<?php
// Version: 1.1; BanList

if (!defined('SMF'))
	die('Hacking attempt...');

function template_main()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
		<table border="0" align="center" cellspacing="1" cellpadding="4" class="bordercolor" width="100%">
			<tr class="catbg3">
				<td colspan="8"><b>', $txt[139], ':</b> ', $context['page_index'], '</td>
			</tr><tr class="titlebg">';
	foreach ($context['columns'] as $column)
	{
		if ($column['selected'])
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					<a href="', $column['href'], '">', $column['label'], '&nbsp;<img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="" /></a>
				</th>';
		elseif ($column['sortable'])
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					', $column['link'], '
				</th>';
		else
			echo '
				<th', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>
					', $column['label'], '
				</th>';
	}
	echo '
			</tr>';

	while ($ban = $context['get_ban']())
	{
		echo '
			<tr>
				<td align="left" valign="top" class="windowbg">', $ban['name'], '</td>
				<td align="left" valign="top" class="windowbg2">', $ban['reason'], '</td>
				<td align="left" valign="top" class="windowbg2">', $ban['added'], '</td>
				<td align="left" valign="top" class="windowbg">', $ban['expires'], '</td>
			</tr>';
	}
	echo '
			<tr class="catbg3">
				<td colspan="8" align="left">
					<div style="float: left;">
						<b>', $txt[139], ':</b> ', $context['page_index'], '
					</div>
				</td>
			</tr>
		</table>';
}

?>