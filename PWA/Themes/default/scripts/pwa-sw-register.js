/**
 * Mobile-First PWA Shell — Service Worker Registration
 *
 * Loaded on all pages (mobile and desktop) to enable caching.
 * Derives the correct SW path from smf_scripturl so it works
 * whether the forum is at the domain root or in a subdirectory.
 *
 * NOTE: Primary registration is now handled inline from PWAIntegration.php.
 * This file is kept as a standalone fallback if loaded via loadJavaScriptFile.
 *
 * @package smfmods:pwa-shell
 */

(function () {
	'use strict';

	if (!('serviceWorker' in navigator))
		return;

	// Derive forum base from smf_scripturl (e.g., '/forums/' from '/forums/index.php')
	var base = (typeof smf_scripturl !== 'undefined')
		? smf_scripturl.replace(/index\.php.*$/, '')
		: '/';

	var rootSwUrl = base + 'sw.js';
	var actionSwUrl = (typeof smf_scripturl !== 'undefined')
		? smf_scripturl + '?action=pwa-sw'
		: '/index.php?action=pwa-sw';

	// Try direct sw.js first (no extra header needed when scope matches directory)
	navigator.serviceWorker.register(rootSwUrl, { scope: base })
		.then(onRegistered)
		.catch(function () {
			// Fallback to action route
			navigator.serviceWorker.register(actionSwUrl, { scope: base })
				.then(onRegistered)
				.catch(function (err) {
					console.warn('[PWA Shell] Service worker registration failed:', err.message);
				});
		});

	function onRegistered(registration) {
		// Check for updates periodically (every 60 minutes)
		setInterval(function () {
			registration.update();
		}, 60 * 60 * 1000);

		// Listen for new service worker
		registration.addEventListener('updatefound', function () {
			var newWorker = registration.installing;
			if (!newWorker)
				return;

			newWorker.addEventListener('statechange', function () {
				if (newWorker.state === 'activated' && navigator.serviceWorker.controller) {
					showUpdateNotice();
				}
			});
		});
	}

	function showUpdateNotice() {
		if (typeof smf_pwa_active === 'undefined')
			return;

		var notice = document.createElement('div');
		notice.className = 'pwa-update-notice';
		notice.innerHTML = '<span>A new version is available.</span> <button onclick="location.reload()">Refresh</button>';
		notice.style.cssText = 'position:fixed;bottom:64px;left:50%;transform:translateX(-50%);background:#333;color:#fff;padding:10px 20px;border-radius:8px;z-index:10000;font-size:13px;display:flex;align-items:center;gap:12px;box-shadow:0 4px 12px rgba(0,0,0,.3)';

		var btn = notice.querySelector('button');
		btn.style.cssText = 'background:#557EA0;color:#fff;border:none;padding:6px 14px;border-radius:4px;cursor:pointer;font-size:13px';

		document.body.appendChild(notice);

		setTimeout(function () {
			if (notice.parentNode)
				notice.parentNode.removeChild(notice);
		}, 10000);
	}
})();
