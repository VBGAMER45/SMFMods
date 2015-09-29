<?php
/**
 * Admin Notepad (an_)
 *
 * @file ./smfhacks_templates/admin-notepad.template.php
 * @author SMFHacks <http://www.smfhacks.com/>
 * @copyright SMFHacks.com Team, 2012
 *
 * @version 2.0.2
 */

function template_admin_notepad()
{
	global $txt, $scripturl, $modSettings;
	if (isset($_GET['notes_saved']))
		echo '<div id="admin_notepad_success">', $txt['admin_notepad']['success'], '</div>';

	echo '
		<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor" id="admin_notepad_cont">
			<tr>
				<td class="catbg">', $txt['admin_notepad']['title'], '</td>
			</tr>
			<tr>
				<td class="windowbg" valign="top" style="padding: 10px;">
					<span id="admin_notepad_desc">', $txt['admin_notepad']['desc'], '</span>
					<form action="', $scripturl, '?action=admin" method="post">
						<textarea id="admin_notes" name="admin_notes" style="width: 50%; max-height: 75px; height: 75px; display: block;">', !empty($modSettings['admin_notes']) ? $modSettings['admin_notes'] : '', '</textarea>
						<input type="submit" value="', $txt['admin_notepad']['save'], '" id="admin_notepad_submit" />
					</form>	
				</td>
			</tr>
		</table>
	';
}