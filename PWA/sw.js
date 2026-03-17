/**
 * Mobile-First PWA Shell — Service Worker
 *
 * Caching strategies:
 * - Static assets: stale-while-revalidate
 * - HTML pages: network-first with offline fallback
 * - AJAX/API: network-only (never cached)
 *
 * @package smfmods:pwa-shell
 */

const CACHE_VERSION = 'pwa-shell-v1';

// Derive forum base path from this SW's location (works for both /sw.js and /forums/sw.js)
const BASE = new URL('./', self.location).pathname;

const OFFLINE_URL = BASE + 'offline.html';

const PRECACHE_URLS = [
	BASE + 'offline.html',
	BASE + 'pwa-icons/icon-192.png',
];

// File extensions considered static assets
const STATIC_EXTENSIONS = /\.(css|js|png|jpg|jpeg|gif|svg|woff2?|ttf|eot|ico)(\?|$)/i;

// Actions that should never be cached
const NOCACHE_ACTIONS = ['xmlhttp', 'pwa-push', 'jsoption', 'logout', 'login2', 'post2', 'deletemsg', 'quickmod'];

// ─── Install ───────────────────────────────────────────────────────────────────

self.addEventListener('install', (event) => {
	event.waitUntil(
		caches.open(CACHE_VERSION)
			.then((cache) => cache.addAll(PRECACHE_URLS))
			.then(() => self.skipWaiting())
	);
});

// ─── Activate ──────────────────────────────────────────────────────────────────

self.addEventListener('activate', (event) => {
	event.waitUntil(
		caches.keys()
			.then((keys) =>
				Promise.all(
					keys
						.filter((key) => key !== CACHE_VERSION)
						.map((key) => caches.delete(key))
				)
			)
			.then(() => self.clients.claim())
	);
});

// ─── Fetch ─────────────────────────────────────────────────────────────────────

self.addEventListener('fetch', (event) => {
	const url = new URL(event.request.url);

	// Only handle GET requests
	if (event.request.method !== 'GET')
		return;

	// Skip cross-origin requests
	if (url.origin !== self.location.origin)
		return;

	// Skip AJAX and state-changing actions
	const action = url.searchParams.get('action');
	if (action && NOCACHE_ACTIONS.includes(action))
		return;

	// Static assets → stale-while-revalidate
	if (isStaticAsset(url)) {
		event.respondWith(staleWhileRevalidate(event.request));
		return;
	}

	// HTML pages → network-first with offline fallback
	if (event.request.headers.get('accept')?.includes('text/html')) {
		event.respondWith(networkFirstWithFallback(event.request));
		return;
	}

	// Everything else → network-first (images in content, etc.)
	event.respondWith(networkFirst(event.request));
});

// ─── Push Notifications ────────────────────────────────────────────────────────

self.addEventListener('push', (event) => {
	let data = {};

	try {
		data = event.data?.json() ?? {};
	} catch (e) {
		data = { title: 'New notification', body: event.data?.text() || '' };
	}

	const options = {
		body: data.body || '',
		icon: BASE + 'pwa-icons/icon-192.png',
		badge: BASE + 'pwa-icons/icon-192.png',
		data: { url: data.url || BASE },
		tag: data.tag || 'smf-notification',
		renotify: true,
	};

	event.waitUntil(
		self.registration.showNotification(data.title || 'New notification', options)
	);
});

self.addEventListener('notificationclick', (event) => {
	event.notification.close();

	const targetUrl = event.notification.data?.url || BASE;

	event.waitUntil(
		clients.matchAll({ type: 'window', includeUncontrolled: true })
			.then((windowClients) => {
				// Focus existing tab if one matches
				for (const client of windowClients) {
					if (new URL(client.url).pathname === new URL(targetUrl, self.location.origin).pathname && 'focus' in client) {
						return client.focus();
					}
				}
				// Open new tab
				return clients.openWindow(targetUrl);
			})
	);
});

// ─── Caching Strategies ────────────────────────────────────────────────────────

/**
 * Stale-while-revalidate: serve from cache immediately, update cache in background.
 */
async function staleWhileRevalidate(request) {
	const cache = await caches.open(CACHE_VERSION);
	const cached = await cache.match(request);

	const fetchPromise = fetch(request)
		.then((response) => {
			if (response.ok) {
				cache.put(request, response.clone());
			}
			return response;
		})
		.catch(() => cached);

	return cached || fetchPromise;
}

/**
 * Network-first: try network, fall back to cache, then offline page.
 */
async function networkFirstWithFallback(request) {
	const cache = await caches.open(CACHE_VERSION);

	try {
		const response = await fetch(request);
		if (response.ok) {
			cache.put(request, response.clone());
		}
		return response;
	} catch (e) {
		const cached = await cache.match(request);
		if (cached)
			return cached;

		// Return offline fallback for HTML requests
		const offlinePage = await cache.match(OFFLINE_URL);
		return offlinePage || new Response('Offline', {
			status: 503,
			headers: { 'Content-Type': 'text/plain' },
		});
	}
}

/**
 * Network-first without offline fallback page (for non-HTML resources).
 */
async function networkFirst(request) {
	const cache = await caches.open(CACHE_VERSION);

	try {
		const response = await fetch(request);
		if (response.ok) {
			cache.put(request, response.clone());
		}
		return response;
	} catch (e) {
		const cached = await cache.match(request);
		return cached || new Response('', { status: 503 });
	}
}

// ─── Helpers ───────────────────────────────────────────────────────────────────

function isStaticAsset(url) {
	return STATIC_EXTENSIONS.test(url.pathname);
}
