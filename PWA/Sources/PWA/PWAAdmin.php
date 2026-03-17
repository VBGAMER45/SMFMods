<?php

/**
 * Mobile-First PWA Shell — Admin Settings Panel
 *
 * Registers and handles the admin configuration area.
 *
 * @package smfmods:pwa-shell
 * @license MIT
 */

namespace PWA;

class PWAAdmin
{
	/**
	 * Register PWA admin area.
	 *
	 * Hook: integrate_admin_areas
	 */
	public static function hookAdminAreas(array &$admin_areas): void
	{
		global $txt;

		$txt['pwa_admin_title'] = 'PWA Shell Settings';
		$txt['pwa_admin_desc']  = 'Configure the Mobile-First PWA Shell for your forum.';

		$admin_areas['config']['areas']['pwa_settings'] = [
			'label'       => $txt['pwa_admin_title'],
			'file'        => 'PWA/PWAAdmin.php',
			'function'    => 'PWA\\PWAAdmin::settingsPage#',
			'icon'        => 'modifications',
			'subsections' => [
				'general' => [$txt['pwa_admin_title']],
			],
		];
	}

	/**
	 * Display and process the settings page.
	 */
	public static function settingsPage(bool $return_config = false): void
	{
		global $context, $txt, $scripturl, $modSettings;

		// Define language strings
		self::loadLanguage();

		$config_vars = [
			['title', 'pwa_settings_general'],

			['check', 'pwa_enabled'],
			['check', 'pwa_push_enabled'],

			'',

			['title', 'pwa_settings_appearance'],

			['select', 'pwa_dark_default', [
				'system' => $txt['pwa_dark_system'],
				'light'  => $txt['pwa_dark_light'],
				'dark'   => $txt['pwa_dark_dark'],
			]],
			['color', 'pwa_accent_color'],

			'',

			['title', 'pwa_settings_behavior'],

			['int', 'pwa_a2hs_delay', 'size' => 3, 'subtext' => $txt['pwa_a2hs_delay_sub']],
			['large_text', 'pwa_offline_msg', 'size' => 4],

			'',

			['title', 'pwa_settings_push'],

			['text', 'pwa_vapid_email', 'subtext' => $txt['pwa_vapid_email_sub']],
			['callback', 'pwa_vapid_info'],
		];

		if ($return_config)
			return;

		$context['page_title'] = $txt['pwa_admin_title'];
		$context['sub_template'] = 'show_settings';
		$context['post_url'] = $scripturl . '?action=admin;area=pwa_settings;save';

		// Load the template for the VAPID info callback
		loadTemplate('PWAAdmin');

		global $sourcedir;
		require_once($sourcedir . '/ManageServer.php');

		// Saving?
		if (isset($_GET['save']))
		{
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=pwa_settings');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * Load inline language strings for the admin panel.
	 */
	private static function loadLanguage(): void
	{
		global $txt;

		$txt['pwa_settings_general']    = 'General Settings';
		$txt['pwa_enabled']             = 'Enable PWA Shell';
		$txt['pwa_enabled_sub']         = 'Master toggle for the mobile PWA experience.';
		$txt['pwa_push_enabled']        = 'Enable Push Notifications';
		$txt['pwa_push_enabled_sub']    = 'Allow users to receive push notifications.';

		$txt['pwa_settings_appearance'] = 'Appearance';
		$txt['pwa_dark_default']        = 'Default Dark Mode';
		$txt['pwa_dark_system']         = 'Follow System';
		$txt['pwa_dark_light']          = 'Light';
		$txt['pwa_dark_dark']           = 'Dark';
		$txt['pwa_accent_color']        = 'Accent Color';
		$txt['pwa_accent_color_sub']    = 'Primary theme color used in the manifest and UI accents.';

		$txt['pwa_settings_behavior']   = 'Behavior';
		$txt['pwa_a2hs_delay']          = 'Install Prompt Delay';
		$txt['pwa_a2hs_delay_sub']      = 'Number of visits before showing the &quot;Add to Home Screen&quot; prompt.';
		$txt['pwa_offline_msg']         = 'Offline Message';
		$txt['pwa_offline_msg_sub']     = 'Message shown on the offline fallback page.';

		$txt['pwa_settings_push']       = 'Push Notifications';
		$txt['pwa_vapid_email']         = 'VAPID Contact Email';
		$txt['pwa_vapid_email_sub']     = 'Email used as the VAPID subject. Push services may contact this address.';
		$txt['pwa_vapid_public_key']    = 'VAPID Public Key';
		$txt['pwa_vapid_generated']     = 'Auto-generated on install. Do not change unless you want to invalidate all existing subscriptions.';
	}
}
