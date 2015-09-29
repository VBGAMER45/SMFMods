<?php
/**
 * Regbar Warning (regbar-warning_)
 *
 * @file ./smfhacks_source/regbar-warning.php
 * @author SMFHacks <http://www.smfhacks.com/>
 * @copyright SMFHacks.com Team, 2012
 *
 * @version 1.0.3
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function RegbarWarning()
{
	global $context, $settings;
	loadLanguage('smfhacks_languages/regbar-warning');
	loadTemplate('smfhacks_templates/regbar-warning');
	$context['html_headers'] .= "\n" . '<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/smfhacks_css/regbar-warning.css" />';
	loadSubTemplate('regbar_warning');
}