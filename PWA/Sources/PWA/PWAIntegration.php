<?php

/**
 * Mobile-First PWA Shell — Central Hook Dispatcher
 *
 * Registers custom actions and injects PWA assets into every page load.
 *
 * @package smfmods:pwa-shell
 * @license MIT
 */

namespace PWA;

class PWAIntegration
{
	/**
	 * Register custom actions for manifest, push API, and service worker serving.
	 *
	 * Hook: integrate_actions
	 */
	public static function hookActions(array &$actionArray): void
	{
		$actionArray['pwa-manifest'] = ['PWA/PWAManifest.php', 'PWA\\PWAManifest::serve#'];
		$actionArray['pwa-push']     = ['PWA/PWAPush.php', 'PWA\\PWAPush::endpoint#'];
		$actionArray['pwa-sw']       = ['PWA/PWAIntegration.php', 'PWA\\PWAIntegration::serveServiceWorker#'];
	}

	/**
	 * Inject PWA assets on every page load.
	 *
	 * Hook: integrate_load_theme
	 */
	public static function hookLoadTheme(): void
	{
		global $context, $scripturl, $modSettings, $settings, $user_info, $boardurl, $txt;

		// Scheduled task name/description for the admin panel
		$txt['scheduled_task_pwa_push_alerts'] = 'PWA Push Notifications';
		$txt['scheduled_task_desc_pwa_push_alerts'] = 'Checks for new alerts and sends push notifications to members with subscribed devices.';

		// Bail if the mod is disabled
		if (empty($modSettings['pwa_enabled']))
			return;

		// Determine if the full PWA shell should be active (mobile or user preference)
		$isMobile = !empty($context['browser']['is_mobile']);
		$userPref = !empty($user_info['id']) && !empty($context['member']['options']['pwa_enabled']);
		$isPWAActive = $isMobile || $userPref;

		// Always inject manifest link and SW registration for all users
		$context['html_headers'] = ($context['html_headers'] ?? '') .
			"\n\t" . '<link rel="manifest" href="' . $scripturl . '?action=pwa-manifest">' .
			"\n\t" . '<meta name="theme-color" content="' . (!empty($modSettings['pwa_accent_color']) ? $modSettings['pwa_accent_color'] : '#557EA0') . '">' .
			"\n\t" . '<meta name="apple-mobile-web-app-capable" content="yes">' .
			"\n\t" . '<meta name="apple-mobile-web-app-status-bar-style" content="default">' .
			"\n\t" . '<link rel="apple-touch-icon" href="' . $boardurl . '/pwa-icons/icon-192.png">';

		// Service worker registration — inline to guarantee output and inject server paths
		addInlineJavaScript('
(function(){
	if(!("serviceWorker" in navigator))return;
	var swDirect = ' . JavaScriptEscape($boardurl . '/sw.js') . ';
	var swAction = ' . JavaScriptEscape($scripturl . '?action=pwa-sw') . ';
	var swScope = ' . JavaScriptEscape($boardurl . '/') . ';
	navigator.serviceWorker.register(swDirect, {scope: swScope})
		.catch(function(){
			return navigator.serviceWorker.register(swAction, {scope: swScope});
		})
		.then(function(r){
			if(r) setInterval(function(){ r.update(); }, 3600000);
		})
		.catch(function(e){
			console.warn("[PWA] SW registration failed:", e);
		});
})();
', true);

		// Full PWA shell — only for mobile / opted-in users
		if ($isPWAActive)
		{
			loadCSSFile('pwa-shell.css', ['minimize' => true, 'order_pos' => 9000, 'default_theme' => true]);
			loadCSSFile('pwa-dark.css', ['minimize' => true, 'order_pos' => 9001, 'default_theme' => true]);
			loadJavaScriptFile('pwa-app.js', ['defer' => true, 'default_theme' => true]);
			loadJavaScriptFile('pwa-push.js', ['defer' => true, 'default_theme' => true]);

			// Add pwa-active class to body
			$context['html_headers'] .= "\n\t" . '<script>document.documentElement.classList.add("pwa-active-html");</script>';

			// JavaScript variables for client-side use
			addJavaScriptVar('smf_pwa_active', 'true');
			addJavaScriptVar('smf_pwa_push_key', !empty($modSettings['pwa_vapid_public']) ? JavaScriptEscape($modSettings['pwa_vapid_public']) : '\'\'');
			addJavaScriptVar('smf_pwa_push_enabled', !empty($modSettings['pwa_push_enabled']) ? '\'1\'' : '\'0\'');
			addJavaScriptVar('smf_pwa_dark_default', JavaScriptEscape(!empty($modSettings['pwa_dark_default']) ? $modSettings['pwa_dark_default'] : 'system'));
			addJavaScriptVar('smf_pwa_a2hs_delay', !empty($modSettings['pwa_a2hs_delay']) ? (string) $modSettings['pwa_a2hs_delay'] : '2');

			// Alert/PM counts for badge display
			$alertCount = !empty($context['user']['alerts']) ? (int) $context['user']['alerts'] : 0;
			$pmCount = !empty($context['user']['unread_messages']) ? (int) $context['user']['unread_messages'] : 0;
			addJavaScriptVar('smf_pwa_alert_count', (string) $alertCount);
			addJavaScriptVar('smf_pwa_pm_count', (string) $pmCount);

			// Session info for CSRF-protected AJAX calls
			if (!empty($context['session_var']))
			{
				addJavaScriptVar('smf_pwa_session_var', JavaScriptEscape($context['session_var']));
				addJavaScriptVar('smf_pwa_session_id', JavaScriptEscape($context['session_id']));
			}

			// User info
			addJavaScriptVar('smf_pwa_user_id', !empty($user_info['id']) ? (string) $user_info['id'] : '0');
		}
	}

	/**
	 * Serve the service worker file with correct headers.
	 *
	 * Accessed via ?action=pwa-sw
	 */
	public static function serveServiceWorker(): void
	{
		global $boarddir;

		$swPath = $boarddir . '/sw.js';

		if (!file_exists($swPath))
		{
			header('HTTP/1.1 404 Not Found');
			exit;
		}

		header('Content-Type: application/javascript');
		header('Service-Worker-Allowed: /');
		header('Cache-Control: no-cache, no-store, must-revalidate');

		echo file_get_contents($swPath);
		exit;
	}
}
