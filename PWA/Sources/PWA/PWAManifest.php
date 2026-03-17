<?php

/**
 * Mobile-First PWA Shell — Dynamic Manifest Generator
 *
 * Serves a Web App Manifest populated from forum settings.
 *
 * @package smfmods:pwa-shell
 * @license MIT
 */

namespace PWA;

class PWAManifest
{
	/**
	 * Serve the manifest.json dynamically.
	 *
	 * Accessed via ?action=pwa-manifest
	 */
	public static function serve(): void
	{
		global $context, $boardurl, $modSettings, $mbname;

		$forumName = !empty($context['forum_name']) ? $context['forum_name'] : $mbname;
		$shortName = self::truncateShortName($forumName, 12);
		$themeColor = !empty($modSettings['pwa_accent_color']) ? $modSettings['pwa_accent_color'] : '#557EA0';

		$manifest = [
			'name'             => $forumName,
			'short_name'       => $shortName,
			'description'      => !empty($modSettings['meta_description']) ? $modSettings['meta_description'] : $forumName . ' — Community Forum',
			'start_url'        => $boardurl . '/index.php',
			'display'          => 'standalone',
			'orientation'      => 'portrait',
			'theme_color'      => $themeColor,
			'background_color' => '#ffffff',
			'scope'            => '/',
			'icons'            => [
				[
					'src'   => $boardurl . '/pwa-icons/icon-192.png',
					'sizes' => '192x192',
					'type'  => 'image/png',
				],
				[
					'src'   => $boardurl . '/pwa-icons/icon-512.png',
					'sizes' => '512x512',
					'type'  => 'image/png',
				],
				[
					'src'     => $boardurl . '/pwa-icons/icon-maskable-512.png',
					'sizes'   => '512x512',
					'type'    => 'image/png',
					'purpose' => 'maskable',
				],
			],
			'categories'       => ['social', 'news'],
		];

		header('Content-Type: application/manifest+json; charset=utf-8');
		header('Cache-Control: public, max-age=86400');

		echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		exit;
	}

	/**
	 * Truncate forum name to fit the short_name field.
	 */
	private static function truncateShortName(string $name, int $maxLen): string
	{
		if (mb_strlen($name) <= $maxLen)
			return $name;

		// Try to cut at a word boundary
		$short = mb_substr($name, 0, $maxLen);
		$lastSpace = mb_strrpos($short, ' ');

		if ($lastSpace !== false && $lastSpace > $maxLen / 2)
			return mb_substr($short, 0, $lastSpace);

		return $short;
	}
}
