<?php
/**
 * Global Headers and Footers (global-hf_)
 *
 * @file ./smfhacks_source/global-hf.php
 * @author SMFHacks <http://www.smfhacks.com/>
 * @copyright SMFHacks.com Team, 2012
 *
 * @version 2.0.1
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function GlobalHF()
{
	global $context, $settings, $txt, $global_hf;
	define('GlobalHF_VERSION', '<a href="http://custom.simplemachines.org/mods/index.php?mod=351" target="_blank">Global Headers and Footers 2.0.1</a>');
	define('GlobalHF_COPYRIGHT', 'Copyright <a href="http://www.smfhacks.com/" target="_blank">SMFHacks.com</a> 2012');
	$context['insert_after_template'] .= '<div class="centertext smalltext">' . GlobalHF_VERSION . ' | ' . GlobalHF_COPYRIGHT . '</div>';
	loadLanguage('smfhacks_languages/global-hf');
	loadTemplate('smfhacks_templates/global-hf', array('smfhacks_css/global-hf'));
	GlobalHFCheckSaving();
	$context['page_title'] = $txt['global_hf']['title'];
	$context['sub_template'] = 'global_hf_admin';
}

function retrieveGlobalHFContent($placement)
{
	global $context, $boarddir, $sourcedir, $global_hf, $modSettings;
	if (!isset($_GET['xml']) && (!isset($_GET['action']) || $_GET['action'] != 'dlattach'))
	{
		$global_hf = array(
			'head' => un_htmlspecialchars(file_get_contents($boarddir . '/smfhacks_resources/global-hf-head.txt')),
			'header' => un_htmlspecialchars(file_get_contents($boarddir . '/smfhacks_resources/global-hf-header.txt')),
			'footer' => un_htmlspecialchars(file_get_contents($boarddir . '/smfhacks_resources/global-hf-footer.txt'))
		);
		if ($placement != 'load')
		{
			if (!empty($modSettings['global_header_bbc']))
				$global_hf['parsed']['header'] = parse_bbc($global_hf['header']);
			if (!empty($modSettings['global_footer_bbc']))
				$global_hf['parsed']['footer'] = parse_bbc($global_hf['footer']);
			loadTemplate('smfhacks_templates/global-hf');
			loadSubTemplate('global_hf' . $placement, true);
		}
		elseif (!empty($global_hf['head']))
			$context['html_headers'] .= "\n" . $global_hf['head'];
	}
}

function GlobalHFCheckSaving()
{
	if (isset($_POST['global_head']))
	{
		checkSession();
		global $smcFunc, $boarddir;
		$file_fields = array('global_head', 'global_header', 'global_footer');
		foreach ($file_fields as $key => $value)
		{
			if (isset($_POST[$value]))
			{
				$_POST[$value] = $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST[$value], ENT_QUOTES));
				file_put_contents($boarddir . '/smfhacks_resources/global-hf-' . str_replace('global_', '', $value) . '.txt', $_POST[$value]);
			}
		}
		$bbc_fields = array('global_header_bbc', 'global_footer_bbc');
		foreach ($bbc_fields as $key => $value)
		{
			if (isset($_POST[$value]))
			{
				$_POST[$value] = (int) $_POST[$value];
				$final_arr[$value] = $_POST[$value];
			}
		}
		updateSettings($final_arr);
		redirectexit('action=admin;area=globalhf;success=saved');
	}
}