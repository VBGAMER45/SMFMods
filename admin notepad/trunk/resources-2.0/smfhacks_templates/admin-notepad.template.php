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
		echo '<div id="profile_success" class="admin_notepad_success" style="margin-top: 8px;">', $txt['admin_notepad']['success'], '</div>';

	echo '
		<div id="admin_notepad_cont">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['admin_notepad']['title'], '</h3>
			</div>
			<div class="windowbg">
				<span class="topslice"><span></span></span>
				<div class="content">
					<span id="admin_notepad_desc">', $txt['admin_notepad']['desc'], '</span>
					<form action="', $scripturl, '?action=admin" method="post">
						<textarea id="admin_notes" name="admin_notes" style="width: 50%; max-height: 75px; height: 75px; display: block;">', !empty($modSettings['admin_notes']) ? $modSettings['admin_notes'] : '', '</textarea>
						<input type="submit" value="', $txt['admin_notepad']['save'], '" class="button_submit" id="admin_notepad_submit" />
					</form>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</div>
	';
}