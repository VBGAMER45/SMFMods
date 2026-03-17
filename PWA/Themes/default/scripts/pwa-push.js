/**
 * Mobile-First PWA Shell — Push Notification Client
 *
 * Handles push subscription management on the client side.
 * Requires smf_pwa_push_key and smf_pwa_push_enabled to be set.
 *
 * @package smfmods:pwa-shell
 */

(function () {
	'use strict';

	// Only run if push is enabled and user is logged in
	if (typeof smf_pwa_push_enabled === 'undefined' || smf_pwa_push_enabled !== '1')
		return;
	if (typeof smf_pwa_user_id === 'undefined' || smf_pwa_user_id === '0')
		return;
	if (!('serviceWorker' in navigator) || !('PushManager' in window))
		return;

	var scriptUrl = (typeof smf_scripturl !== 'undefined') ? smf_scripturl : '/index.php';

	/**
	 * Convert a base64url string to a Uint8Array (for applicationServerKey).
	 */
	function urlBase64ToUint8Array(base64String) {
		var padding = '='.repeat((4 - base64String.length % 4) % 4);
		var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
		var rawData = atob(base64);
		var outputArray = new Uint8Array(rawData.length);
		for (var i = 0; i < rawData.length; i++) {
			outputArray[i] = rawData.charCodeAt(i);
		}
		return outputArray;
	}

	/**
	 * Subscribe the user to push notifications.
	 */
	async function subscribePush() {
		try {
			var registration = await navigator.serviceWorker.ready;

			// Check current subscription
			var existing = await registration.pushManager.getSubscription();
			if (existing) {
				// Already subscribed, send to server in case it's not recorded
				await sendSubscriptionToServer(existing);
				return;
			}

			// Request permission
			var permission = await Notification.requestPermission();
			if (permission !== 'granted')
				return;

			// Subscribe
			var vapidKey = (typeof smf_pwa_push_key !== 'undefined') ? smf_pwa_push_key : '';
			if (!vapidKey)
				return;

			var subscription = await registration.pushManager.subscribe({
				userVisibleOnly: true,
				applicationServerKey: urlBase64ToUint8Array(vapidKey)
			});

			await sendSubscriptionToServer(subscription);
		} catch (err) {
			console.warn('[PWA Shell] Push subscription failed:', err.message);
		}
	}

	/**
	 * Send subscription data to the server.
	 */
	async function sendSubscriptionToServer(subscription) {
		var body = {
			subscription: subscription.toJSON()
		};

		// Add CSRF token
		if (typeof smf_pwa_session_var !== 'undefined' && typeof smf_pwa_session_id !== 'undefined') {
			body[smf_pwa_session_var] = smf_pwa_session_id;
		}

		var url = scriptUrl + '?action=pwa-push;sa=subscribe';

		// Append session to URL as well for SMF's checkSession
		if (typeof smf_pwa_session_var !== 'undefined' && typeof smf_pwa_session_id !== 'undefined') {
			url += ';' + smf_pwa_session_var + '=' + smf_pwa_session_id;
		}

		await fetch(url, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			credentials: 'same-origin',
			body: JSON.stringify(body)
		});
	}

	/**
	 * Unsubscribe from push notifications.
	 */
	async function unsubscribePush() {
		try {
			var registration = await navigator.serviceWorker.ready;
			var subscription = await registration.pushManager.getSubscription();

			if (!subscription)
				return;

			var endpoint = subscription.endpoint;
			await subscription.unsubscribe();

			var body = { endpoint: endpoint };

			if (typeof smf_pwa_session_var !== 'undefined' && typeof smf_pwa_session_id !== 'undefined') {
				body[smf_pwa_session_var] = smf_pwa_session_id;
			}

			var url = scriptUrl + '?action=pwa-push;sa=unsubscribe';
			if (typeof smf_pwa_session_var !== 'undefined' && typeof smf_pwa_session_id !== 'undefined') {
				url += ';' + smf_pwa_session_var + '=' + smf_pwa_session_id;
			}

			await fetch(url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				credentials: 'same-origin',
				body: JSON.stringify(body)
			});
		} catch (err) {
			console.warn('[PWA Shell] Push unsubscribe failed:', err.message);
		}
	}

	// Expose functions globally
	window.pwaPushSubscribe = subscribePush;
	window.pwaPushUnsubscribe = unsubscribePush;

	/**
	 * Detect if running as an installed PWA (standalone mode).
	 */
	function isStandalone() {
		return window.matchMedia('(display-mode: standalone)').matches ||
			window.navigator.standalone === true;
	}

	/**
	 * Detect iOS.
	 */
	function isIOS() {
		return /iP(hone|ad|od)/.test(navigator.userAgent) ||
			(navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
	}

	/**
	 * Show a non-intrusive banner asking the user to enable notifications.
	 * Only shown once per session inside the installed PWA.
	 */
	function showPushOptInBanner() {
		if (sessionStorage.getItem('pwa_push_banner_shown'))
			return;
		sessionStorage.setItem('pwa_push_banner_shown', '1');

		var banner = document.createElement('div');
		banner.style.cssText = 'position:fixed;bottom:72px;left:8px;right:8px;background:var(--pwa-bg-surface,#fff);border-radius:12px;padding:14px 16px;box-shadow:0 4px 20px rgba(0,0,0,.15);z-index:10000;display:flex;align-items:center;gap:12px;font-size:14px;animation:pwa-slide-up .3s ease-out';

		banner.innerHTML =
			'<div style="flex:1"><strong>Enable Notifications?</strong><br><span style="font-size:12px;color:var(--pwa-text-secondary,#666)">Get notified about replies, mentions, and likes.</span></div>' +
			'<button id="pwa-push-yes" style="padding:8px 16px;border-radius:8px;border:none;background:var(--pwa-accent,#557EA0);color:#fff;font-weight:600;font-size:13px;cursor:pointer">Enable</button>' +
			'<button id="pwa-push-no" style="padding:8px;border:none;background:transparent;color:var(--pwa-text-secondary,#666);font-size:18px;cursor:pointer">\u00D7</button>';

		document.body.appendChild(banner);

		document.getElementById('pwa-push-yes').addEventListener('click', function () {
			banner.remove();
			subscribePush();
		});

		document.getElementById('pwa-push-no').addEventListener('click', function () {
			banner.remove();
			localStorage.setItem('pwa_push_declined', '1');
		});
	}

	// ─── Initialization Logic ───────────────────────────────────────────

	document.addEventListener('DOMContentLoaded', function () {
		// Already granted — silently re-subscribe (ensures server has current subscription)
		if (Notification.permission === 'granted') {
			subscribePush();
			return;
		}

		// User previously denied via browser — nothing we can do
		if (Notification.permission === 'denied')
			return;

		// User declined our banner before — respect that
		if (localStorage.getItem('pwa_push_declined'))
			return;

		// Permission is 'default' (never asked) — decide when to ask:

		if (isIOS()) {
			// iOS: push ONLY works in installed standalone PWA mode.
			// Don't ask in Safari — it will just confuse users.
			if (!isStandalone())
				return;

			// In the installed PWA on iOS: show our opt-in banner
			showPushOptInBanner();
		} else {
			// Android/Desktop: show opt-in banner inside installed PWA,
			// or after a delay on the regular site
			if (isStandalone()) {
				showPushOptInBanner();
			} else {
				// Regular browser visit — show banner after a few page loads
				var pushVisits = parseInt(localStorage.getItem('pwa_push_visit_count') || '0', 10) + 1;
				localStorage.setItem('pwa_push_visit_count', pushVisits.toString());
				if (pushVisits >= 3) {
					showPushOptInBanner();
				}
			}
		}
	});

})();
