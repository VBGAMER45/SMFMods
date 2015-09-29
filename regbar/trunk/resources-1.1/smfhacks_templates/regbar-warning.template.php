<?php
/**
 * Regbar Warning (regbar-warning_)
 *
 * @file ./smfhacks_templates/regbar-warning.english.php
 * @author SMFHacks <http://www.smfhacks.com/>
 * @copyright SMFHacks.com Team, 2012
 *
 * @version 1.0.3
 */

function template_regbar_warning()
{
	global $scripturl, $settings, $txt, $mbname;
	echo '
		<div id="regbar_warning">
			<a href="', $scripturl, '?action=register" target="_self">
				<span class="floatleft" id="regbar_icon">
					<img src="', $settings['default_images_url'], '/smfhacks_images/regbar-warning.png" alt="" />
				</span>
				<span class="floatleft" id="regbar_message">
					', sprintf($txt['regbar_warning']['message'], '<strong>' . $mbname . '</strong>'), '
				</span>
				<br class="clear" />
			</a>
		</div>
	';
}