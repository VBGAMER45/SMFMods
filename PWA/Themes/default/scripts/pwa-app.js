/**
 * Mobile-First PWA Shell — Shell Controller
 *
 * Manages bottom navigation, pull-to-refresh, swipe gestures,
 * Add-to-Home-Screen prompt, and dark mode toggle.
 *
 * Only runs when smf_pwa_active is set (mobile or opted-in users).
 *
 * @package smfmods:pwa-shell
 */

(function () {
	'use strict';

	// Only initialize if PWA shell is active
	if (typeof smf_pwa_active === 'undefined')
		return;

	// ─── SVG Icons ──────────────────────────────────────────────────────────

	var SVG_NS = 'xmlns="http://www.w3.org/2000/svg"';

	var ICONS = {
		home: '<svg ' + SVG_NS + ' viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
		grid: '<svg ' + SVG_NS + ' viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
		bell: '<svg ' + SVG_NS + ' viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
		mail: '<svg ' + SVG_NS + ' viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
		user: '<svg ' + SVG_NS + ' viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
		plus: '<svg ' + SVG_NS + ' viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
		search: '<svg ' + SVG_NS + ' viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
		moon: '<svg ' + SVG_NS + ' viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>',
		sun: '<svg ' + SVG_NS + ' viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>',
		arrow: '<svg ' + SVG_NS + ' viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>',
		share: '<svg ' + SVG_NS + ' viewBox="0 0 24 24"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>'
	};

	// ─── PWAShell Class ─────────────────────────────────────────────────────

	function PWAShell() {
		this.deferredPrompt = null;
		this.init();
	}

	PWAShell.prototype.init = function () {
		var self = this;
		document.addEventListener('DOMContentLoaded', function () {
			document.body.classList.add('pwa-active');
			self.initHeader();
			self.initBottomNav();
			self.initFAB();
			self.initPullToRefresh();
			self.initSwipeGestures();
			self.initA2HSPrompt();
			self.initDarkMode();
		});
	};

	// ─── Sticky Header ──────────────────────────────────────────────────────

	PWAShell.prototype.initHeader = function () {
		var header = document.createElement('div');
		header.className = 'pwa-header';

		// Forum title
		var title = document.createElement('span');
		title.className = 'pwa-header-title';
		title.textContent = this.getPageTitle();

		// Action buttons
		var actions = document.createElement('div');
		actions.className = 'pwa-header-actions';

		// Search button
		var searchBtn = document.createElement('button');
		searchBtn.className = 'pwa-header-btn';
		searchBtn.innerHTML = ICONS.search;
		searchBtn.setAttribute('aria-label', 'Search');
		searchBtn.addEventListener('click', function () {
			window.location.href = smf_scripturl + '?action=search';
		});

		// Dark mode toggle
		var darkBtn = document.createElement('button');
		darkBtn.className = 'pwa-header-btn pwa-dark-toggle';
		darkBtn.innerHTML = document.documentElement.classList.contains('pwa-dark') ? ICONS.sun : ICONS.moon;
		darkBtn.setAttribute('aria-label', 'Toggle dark mode');
		darkBtn.addEventListener('click', function () {
			toggleDarkMode();
			darkBtn.innerHTML = document.documentElement.classList.contains('pwa-dark') ? ICONS.sun : ICONS.moon;
		});

		actions.appendChild(searchBtn);
		actions.appendChild(darkBtn);

		header.appendChild(title);
		header.appendChild(actions);

		// Insert at top of body
		var topSection = document.getElementById('top_section');
		if (topSection) {
			topSection.parentNode.insertBefore(header, topSection);
		} else {
			document.body.insertBefore(header, document.body.firstChild);
		}
	};

	PWAShell.prototype.getPageTitle = function () {
		// Try to get the forum name from SMF context
		var forumName = document.querySelector('#top_section .inner_wrap a');
		if (forumName)
			return forumName.textContent.trim();

		// Fallback to document title (remove " - Board Name" suffix)
		var parts = document.title.split(' - ');
		return parts.length > 1 ? parts[parts.length - 1] : parts[0];
	};

	// ─── Bottom Navigation ──────────────────────────────────────────────────

	PWAShell.prototype.initBottomNav = function () {
		var scriptUrl = (typeof smf_scripturl !== 'undefined') ? smf_scripturl : '/index.php';
		var isGuest = (typeof smf_pwa_user_id !== 'undefined') ? smf_pwa_user_id === '0' || smf_pwa_user_id === 0 : true;

		var alertCount = (typeof smf_pwa_alert_count !== 'undefined') ? parseInt(smf_pwa_alert_count, 10) : 0;
		var pmCount = (typeof smf_pwa_pm_count !== 'undefined') ? parseInt(smf_pwa_pm_count, 10) : 0;

		var tabs = [
			{ id: 'feed', label: 'Feed', icon: ICONS.home, url: scriptUrl + '?action=unread', actions: ['unread', 'unreadreplies'] },
			{ id: 'boards', label: 'Boards', icon: ICONS.grid, url: scriptUrl, actions: [null, 'forum'] },
			{ id: 'alerts', label: 'Alerts', icon: ICONS.bell, url: scriptUrl + '?action=profile;area=showalerts', actions: ['alerts'], badge: alertCount },
			{ id: 'messages', label: 'Messages', icon: ICONS.mail, url: scriptUrl + '?action=pm', actions: ['pm'], badge: pmCount },
			{ id: 'profile', label: 'Profile', icon: ICONS.user, url: isGuest ? scriptUrl + '?action=login' : scriptUrl + '?action=profile', actions: ['profile', 'login'] }
		];

		var nav = document.createElement('nav');
		nav.className = 'pwa-bottom-nav';
		nav.setAttribute('role', 'navigation');
		nav.setAttribute('aria-label', 'Main navigation');

		var currentAction = this.getCurrentAction();

		for (var i = 0; i < tabs.length; i++) {
			var tab = tabs[i];
			var link = document.createElement('a');
			link.href = tab.url;
			link.setAttribute('aria-label', tab.label);

			// Determine if this tab is active
			var isActive = tab.actions.indexOf(currentAction) !== -1;
			if (isActive)
				link.className = 'active';

			// Icon container
			var iconWrap = document.createElement('span');
			iconWrap.className = 'pwa-nav-icon';
			iconWrap.innerHTML = tab.icon;

			// Label
			var label = document.createElement('span');
			label.className = 'pwa-nav-label';
			label.textContent = tab.label;

			link.appendChild(iconWrap);
			link.appendChild(label);

			// Badge
			if (tab.badge && tab.badge > 0) {
				var badge = document.createElement('span');
				badge.className = 'pwa-badge';
				badge.setAttribute('data-count', tab.badge);
				badge.textContent = tab.badge > 99 ? '99+' : tab.badge;
				link.appendChild(badge);
			}

			// Haptic feedback on tap
			link.addEventListener('touchstart', function () {
				if (navigator.vibrate) navigator.vibrate(10);
			}, { passive: true });

			nav.appendChild(link);
		}

		document.body.appendChild(nav);
	};

	PWAShell.prototype.getCurrentAction = function () {
		var params = new URLSearchParams(window.location.search);
		var action = params.get('action');

		// Board index (no action) = boards tab
		if (!action) {
			// Check if we're on the board index
			var path = window.location.pathname;
			if (path.match(/\/index\.php\/?$/) || path === '/' || !params.has('topic') && !params.has('board'))
				return null; // boards tab
		}

		return action;
	};

	// ─── Floating Action Button ─────────────────────────────────────────────

	PWAShell.prototype.initFAB = function () {
		var scriptUrl = (typeof smf_scripturl !== 'undefined') ? smf_scripturl : '/index.php';
		var isGuest = (typeof smf_pwa_user_id !== 'undefined') ? (smf_pwa_user_id === '0' || smf_pwa_user_id === 0) : true;

		if (isGuest) return;

		var fab = document.createElement('button');
		fab.className = 'pwa-fab';
		fab.innerHTML = ICONS.plus;
		fab.setAttribute('aria-label', 'New post');

		fab.addEventListener('click', function () {
			// If we're viewing a topic, reply to it
			var params = new URLSearchParams(window.location.search);
			var topic = params.get('topic');

			if (topic) {
				// Scroll to quick reply if available
				var qr = document.getElementById('quickReplyOptions');
				if (qr) {
					qr.scrollIntoView({ behavior: 'smooth' });
					var textarea = qr.querySelector('textarea');
					if (textarea) textarea.focus();
					return;
				}
			}

			// If on a board, create new topic
			var board = params.get('board');
			if (board) {
				var boardNum = board.split('.')[0];
				window.location.href = scriptUrl + '?action=post;board=' + boardNum + '.0';
				return;
			}

			// Default: go to unread
			window.location.href = scriptUrl + '?action=unread';
		});

		fab.addEventListener('touchstart', function () {
			if (navigator.vibrate) navigator.vibrate(10);
		}, { passive: true });

		document.body.appendChild(fab);
	};

	// ─── Pull to Refresh ────────────────────────────────────────────────────

	PWAShell.prototype.initPullToRefresh = function () {
		var indicator = document.createElement('div');
		indicator.className = 'pwa-ptr-indicator';
		indicator.innerHTML = '<div class="pwa-ptr-spinner"></div>';
		document.body.appendChild(indicator);

		var startY = 0;
		var pulling = false;
		var threshold = 80;

		document.addEventListener('touchstart', function (e) {
			if (window.scrollY === 0 && e.touches.length === 1) {
				startY = e.touches[0].clientY;
				pulling = true;
			}
		}, { passive: true });

		document.addEventListener('touchmove', function (e) {
			if (!pulling) return;

			var deltaY = e.touches[0].clientY - startY;
			if (deltaY < 0) {
				pulling = false;
				return;
			}

			if (deltaY > 10 && window.scrollY === 0) {
				var progress = Math.min(deltaY / threshold, 1);
				indicator.classList.add('pulling');
				indicator.style.transform = 'translateX(-50%) translateY(' + (progress * 60 - 36) + 'px)';
				indicator.querySelector('.pwa-ptr-spinner').style.transform = 'rotate(' + (progress * 360) + 'deg)';
			}
		}, { passive: true });

		document.addEventListener('touchend', function () {
			if (!pulling) return;
			pulling = false;

			var isPulledEnough = indicator.classList.contains('pulling');
			var rect = indicator.getBoundingClientRect();

			if (isPulledEnough && rect.top > 20) {
				// Trigger refresh
				indicator.classList.remove('pulling');
				indicator.classList.add('refreshing');
				indicator.style.transform = 'translateX(-50%) translateY(16px)';

				if (navigator.vibrate) navigator.vibrate(15);

				setTimeout(function () {
					window.location.reload();
				}, 300);
			} else {
				// Reset
				indicator.classList.remove('pulling');
				indicator.style.transform = 'translateX(-50%) translateY(-100%)';
			}
		}, { passive: true });
	};

	// ─── Swipe Gestures ─────────────────────────────────────────────────────

	PWAShell.prototype.initSwipeGestures = function () {
		var startX = 0;
		var startY = 0;
		var tracking = false;
		var edgeThreshold = 30; // px from left edge
		var swipeThreshold = 80;

		document.addEventListener('touchstart', function (e) {
			if (e.touches.length !== 1) return;

			startX = e.touches[0].clientX;
			startY = e.touches[0].clientY;

			// Only track swipes starting from the left edge
			tracking = startX < edgeThreshold;
		}, { passive: true });

		document.addEventListener('touchend', function (e) {
			if (!tracking) return;
			tracking = false;

			var endX = e.changedTouches[0].clientX;
			var endY = e.changedTouches[0].clientY;

			var deltaX = endX - startX;
			var deltaY = Math.abs(endY - startY);

			// Swipe right from left edge = go back
			if (deltaX > swipeThreshold && deltaY < deltaX * 0.5) {
				if (navigator.vibrate) navigator.vibrate(10);
				history.back();
			}
		}, { passive: true });
	};

	// ─── Add to Home Screen ─────────────────────────────────────────────────

	PWAShell.prototype.isIOS = function () {
		return /iP(hone|ad|od)/.test(navigator.userAgent) ||
			(navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
	};

	PWAShell.prototype.isStandalone = function () {
		return window.matchMedia('(display-mode: standalone)').matches ||
			window.navigator.standalone === true;
	};

	PWAShell.prototype.initA2HSPrompt = function () {
		var self = this;

		// Already installed — skip
		if (this.isStandalone())
			return;

		// Check if dismissed
		if (localStorage.getItem('pwa_a2hs_dismissed'))
			return;

		// Check visit count
		var delay = (typeof smf_pwa_a2hs_delay !== 'undefined') ? parseInt(smf_pwa_a2hs_delay, 10) : 2;
		var visits = parseInt(localStorage.getItem('pwa_visit_count') || '0', 10) + 1;
		localStorage.setItem('pwa_visit_count', visits.toString());

		if (visits < delay)
			return;

		if (this.isIOS()) {
			// iOS: show manual instructions (no beforeinstallprompt support)
			this.showIOSA2HSBanner();
		} else {
			// Android/Desktop: capture the native install prompt
			window.addEventListener('beforeinstallprompt', function (e) {
				e.preventDefault();
				self.deferredPrompt = e;
				self.showA2HSBanner();
			});
		}
	};

	PWAShell.prototype.showIOSA2HSBanner = function () {
		var banner = document.createElement('div');
		banner.className = 'pwa-a2hs-banner';

		var icon = document.createElement('img');
		icon.className = 'pwa-a2hs-icon';
		icon.src = '/pwa-icons/icon-192.png';
		icon.alt = '';

		var text = document.createElement('div');
		text.className = 'pwa-a2hs-text';

		var title = document.createElement('div');
		title.className = 'pwa-a2hs-title';
		title.textContent = 'Install App';

		var desc = document.createElement('div');
		desc.className = 'pwa-a2hs-desc';
		// Share icon (box with arrow) instruction
		desc.innerHTML = 'Tap <span class="pwa-a2hs-share-icon">' + ICONS.share + '</span> then <strong>"Add to Home Screen"</strong>';

		text.appendChild(title);
		text.appendChild(desc);

		var dismissBtn = document.createElement('button');
		dismissBtn.className = 'pwa-a2hs-dismiss';
		dismissBtn.textContent = '\u00D7';
		dismissBtn.setAttribute('aria-label', 'Dismiss');
		dismissBtn.addEventListener('click', function () {
			localStorage.setItem('pwa_a2hs_dismissed', '1');
			banner.remove();
		});

		banner.appendChild(icon);
		banner.appendChild(text);
		banner.appendChild(dismissBtn);

		document.body.appendChild(banner);
	};

	PWAShell.prototype.showA2HSBanner = function () {
		var self = this;

		var banner = document.createElement('div');
		banner.className = 'pwa-a2hs-banner';

		var icon = document.createElement('img');
		icon.className = 'pwa-a2hs-icon';
		icon.src = '/pwa-icons/icon-192.png';
		icon.alt = '';

		var text = document.createElement('div');
		text.className = 'pwa-a2hs-text';

		var title = document.createElement('div');
		title.className = 'pwa-a2hs-title';
		title.textContent = 'Install App';

		var desc = document.createElement('div');
		desc.className = 'pwa-a2hs-desc';
		desc.textContent = 'Add to your home screen for quick access';

		text.appendChild(title);
		text.appendChild(desc);

		var installBtn = document.createElement('button');
		installBtn.className = 'pwa-a2hs-install';
		installBtn.textContent = 'Install';
		installBtn.addEventListener('click', function () {
			if (self.deferredPrompt) {
				self.deferredPrompt.prompt();
				self.deferredPrompt.userChoice.then(function (result) {
					self.deferredPrompt = null;
					banner.remove();
				});
			}
		});

		var dismissBtn = document.createElement('button');
		dismissBtn.className = 'pwa-a2hs-dismiss';
		dismissBtn.textContent = '\u00D7';
		dismissBtn.setAttribute('aria-label', 'Dismiss');
		dismissBtn.addEventListener('click', function () {
			localStorage.setItem('pwa_a2hs_dismissed', '1');
			banner.remove();
		});

		banner.appendChild(icon);
		banner.appendChild(text);
		banner.appendChild(installBtn);
		banner.appendChild(dismissBtn);

		document.body.appendChild(banner);
	};

	// ─── Dark Mode ──────────────────────────────────────────────────────────

	PWAShell.prototype.initDarkMode = function () {
		var savedPref = localStorage.getItem('pwa_dark_mode');
		var defaultPref = (typeof smf_pwa_dark_default !== 'undefined') ? smf_pwa_dark_default : 'system';

		if (savedPref === 'dark') {
			document.documentElement.classList.add('pwa-dark');
			document.documentElement.classList.remove('pwa-dark-auto');
		} else if (savedPref === 'light') {
			document.documentElement.classList.remove('pwa-dark');
			document.documentElement.classList.remove('pwa-dark-auto');
		} else {
			// System default
			if (defaultPref === 'dark') {
				document.documentElement.classList.add('pwa-dark');
			} else if (defaultPref === 'system') {
				document.documentElement.classList.add('pwa-dark-auto');
				// Also check media query for system theme
				if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
					document.documentElement.classList.add('pwa-dark');
				}
			}
		}

		// Listen for system theme changes
		if (window.matchMedia) {
			window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
				if (!localStorage.getItem('pwa_dark_mode')) {
					if (e.matches) {
						document.documentElement.classList.add('pwa-dark');
					} else {
						document.documentElement.classList.remove('pwa-dark');
					}
				}
			});
		}
	};

	// ─── Dark Mode Toggle (global function) ─────────────────────────────────

	function toggleDarkMode() {
		var html = document.documentElement;
		var isDark = html.classList.contains('pwa-dark');

		if (isDark) {
			html.classList.remove('pwa-dark');
			html.classList.remove('pwa-dark-auto');
			localStorage.setItem('pwa_dark_mode', 'light');
		} else {
			html.classList.add('pwa-dark');
			html.classList.remove('pwa-dark-auto');
			localStorage.setItem('pwa_dark_mode', 'dark');
		}

		// Persist to server for logged-in users via SMF's jsoption
		if (typeof smf_pwa_user_id !== 'undefined' && smf_pwa_user_id !== '0' && typeof smf_scripturl !== 'undefined') {
			var darkValue = html.classList.contains('pwa-dark') ? '1' : '0';
			var url = smf_scripturl + '?action=jsoption;var=pwa_dark_mode;val=' + darkValue;

			if (typeof smf_pwa_session_var !== 'undefined' && typeof smf_pwa_session_id !== 'undefined') {
				url += ';' + smf_pwa_session_var + '=' + smf_pwa_session_id;
			}

			fetch(url, { credentials: 'same-origin' }).catch(function () {});
		}

		if (navigator.vibrate) navigator.vibrate(10);
	}

	// Expose globally for the header button
	window.toggleDarkMode = toggleDarkMode;

	// ─── Initialize ─────────────────────────────────────────────────────────

	new PWAShell();

})();
