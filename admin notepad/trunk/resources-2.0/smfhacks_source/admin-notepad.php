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

if (!defined('SMF'))
	die('Hacking attempt...');

function AdminNotepad()
{
	loadLanguage('smfhacks_languages/admin-notepad');
	loadTemplate('smfhacks_templates/admin-notepad', array('smfhacks_css/admin-notepad'));
	if (allowedTo('admin_forum'))
	{
		global $modSettings, $smcFunc;
		if (isset($_POST['admin_notes']))
		{
			if (!empty($modSettings['admin_notes']) && $_POST['admin_notes'] == $modSettings['admin_notes'])
				return;
			elseif (empty($modSettings['admin_notes']) && empty($_POST['admin_notes']))
				return;
			else
			{
				updateSettings(array(
					'admin_notes' => $smcFunc['htmlspecialchars']($_POST['admin_notes'], ENT_QUOTES)
				));
				redirectexit('action=admin;notes_saved');
			}
		}
	}
}