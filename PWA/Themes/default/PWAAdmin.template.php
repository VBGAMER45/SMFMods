<?php

/**
 * Mobile-First PWA Shell — Admin Template
 *
 * Provides the settings UI for the admin panel.
 *
 * @package smfmods:pwa-shell
 * @license MIT
 */

/**
 * Callback template for displaying VAPID key information.
 */
function template_callback_pwa_vapid_info()
{
	global $modSettings, $txt;

	$publicKey = !empty($modSettings['pwa_vapid_public']) ? $modSettings['pwa_vapid_public'] : '<em>Not generated</em>';

	echo '
		<dt>
			<strong>', $txt['pwa_vapid_public_key'], '</strong><br>
			<span class="smalltext">', $txt['pwa_vapid_generated'], '</span>
		</dt>
		<dd>
			<input type="text" value="', $publicKey, '" readonly
				style="width: 100%; max-width: 500px; font-family: monospace; font-size: 11px; padding: 6px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 3px;"
				onclick="this.select();" />
		</dd>';
}
