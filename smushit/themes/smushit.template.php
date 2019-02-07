<?php
/**
 * Smush.it for SMF
 *
 * @author spuds http://addons.elkarte.net/2015/05/Smushit/
 * @license MPL 1.1 http://mozilla.org/MPL/1.1/
 * Ported to SMF by vbgamer45 http://www.smfhacks.com
 *
 */

/**
 * Displays the list of attachments and current smushed status
 * Shows results of a smushit run on the selected files
 */
function template_attachment_smushit()
{
	global $context, $txt, $settings;

	if ($context['completed'])
	{
		echo '
	<div id="manage_attachments">
		<h3 class="category_header">', $txt['smushit_attachments_complete'], '</h3>
		<div class="content">
			<p>', $txt['smushit_attachments_complete_desc'], '</p>
			<table class="table_grid">
				<thead>
					<tr class="table_header">
						<th class="first_th"></th>
						<th>#</th>
						<th>', $txt['attachment_name'], '</th>
						<th class="last_th">', $txt['smushit_attachments'], '</th>
					</tr>
				</thead>
				<tbody>';

		// Loop through each result reporting the status
		$i = 1;
		$savings = 0;
		$alternate = true;

		if (isset($context['smushit_results']))
		{
			foreach ($context['smushit_results'] as $attach_id => $result)
			{
				$attach_id = str_replace('+', '', $attach_id, $count);
				list($filename, $result) = explode('|', $result, 2);
				echo '
					<tr>
						<td class="', $alternate ? 'windowbg2' : 'windowbg', '">' . (($count != 0) ? $txt['smushit_valid'] : $txt['smushit_invalid']) . '</td>
						<td class="', $alternate ? 'windowbg2' : 'windowbg', '">' . $i . '</td>
						<td class="', $alternate ? 'windowbg2' : 'windowbg', '">[' . $attach_id . '] ' . $filename . '</td>
						<td class="', $alternate ? 'windowbg2' : 'windowbg', '">' . $result . '</td>
					</tr>';
				$alternate = !$alternate;
				$i++;

				// Keep track of how great we are
				if ($count != 0 && preg_match('~.*\((\d*)\).*~', $result, $thissavings))
					$savings += $thissavings[1];
			}
		}

		// Show the total savings in some form the user will understand
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$savings = max($savings, 0);
		$pow = floor(($savings ? log($savings) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$savings /= pow(1024, $pow);

		echo '
				</tbody>
			</table>
			<br />
			<p><strong>' . $txt['smushit_attachments_savings'] . ' ' . round($savings, 2) . ' ' . $units[$pow] . '</strong></p>
		</div>
	</div>';
	}
}

/**
 * Maintainance section, injected in the layer via ->add
 */
function template_smushit_maintain_below()
{
	global $txt, $scripturl, $context;

	echo '
	<h3 class="category_header">', $txt['smushit_attachment_check'], '</h3>
	<div id="manage_boards" class="windowbg">
		<div class="content" style="margin-top: -10px">
			<form action="', $scripturl, '?action=admin;area=manageattachments;sa=smushit;', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="UTF-8">
				<p>', $txt['smushit_attachment_check_desc'], '</p>
				<br />
				', $txt['smushit_attachments_age'], ' <input type="text" name="smushitage" value="25" size="4" class="input_text" /> ', $txt['days_word'], '<br />
				<input type="submit" name="submit" value="', $txt['smushit_attachment_now'], '" class="right_submit" />
			</form>
		</div>
	</div>';
}