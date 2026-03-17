================================================================================
  Mobile-First PWA Shell for SMF 2.1
  Version 1.0.0
================================================================================

  Author:       vbgamer45 / SMF Hacks
  Website:      https://www.smfhacks.com
  Compatibility: SMF 2.1.x (tested on 2.1.6)
  License:      MIT
  Package ID:   smfmods:pwa-shell

================================================================================
  OVERVIEW
================================================================================

  Transforms your SMF forum into a modern Progressive Web App on mobile devices.
  Installs as a standard SMF package using only hooks - zero core file edits.
  The desktop experience is completely untouched.

  Mobile users get:
  - Bottom tab navigation (Feed, Boards, Alerts, Messages, Profile)
  - Sticky header with search and dark mode toggle
  - Pull-to-refresh gesture
  - Swipe-right from left edge to go back
  - Floating action button for new post/reply
  - "Add to Home Screen" install prompt (iOS and Android)
  - Dark mode with system preference detection
  - Push notifications for forum alerts
  - Offline fallback page when network is unavailable
  - Service worker caching for faster page loads

================================================================================
  REQUIREMENTS
================================================================================

  - SMF 2.1.x
  - PHP 7.4+ (PHP 8.x recommended)
  - OpenSSL PHP extension (required for push notification VAPID keys)
  - cURL PHP extension (required for sending push notifications)
  - HTTPS (required by browsers for service workers and push notifications)

================================================================================
  INSTALLATION
================================================================================

  1. Download the package zip file.
  2. Go to Admin > Packages > Install a New Package.
  3. Upload the zip file and click Install.
  4. You will be redirected to the PWA Settings page after install.
  5. Configure your preferences (see Settings section below).

  On install, the mod will:
  - Generate VAPID encryption keys for push notifications (via OpenSSL)
  - Create a database table for push subscriptions
  - Register a scheduled task for push notification delivery
  - Register three integration hooks (no core files modified)

================================================================================
  UNINSTALLATION
================================================================================

  1. Go to Admin > Packages > Installed Packages.
  2. Find "Mobile-First PWA Shell" and click Uninstall.

  This will cleanly remove:
  - All mod files (PHP sources, CSS, JS, icons, templates)
  - The push subscriptions database table
  - All mod settings (including VAPID keys)
  - The scheduled task
  - All registered hooks

================================================================================
  ADMIN SETTINGS
================================================================================

  Found at: Admin > Configuration > PWA Shell Settings

  General Settings:
  - Enable PWA Shell          Master on/off toggle for the mobile experience.
  - Enable Push Notifications Allow users to receive push notifications.

  Appearance:
  - Default Dark Mode         Light / Dark / Follow System.
                              Users can override with the toggle in the header.
  - Accent Color              Primary theme color used in the manifest, browser
                              toolbar, and UI accents. Default: #557EA0

  Behavior:
  - Install Prompt Delay      Number of page visits before showing the
                              "Add to Home Screen" banner. Default: 2
  - Offline Message           Text shown on the offline fallback page when the
                              user has no network connection.

  Push Notifications:
  - VAPID Contact Email       Email address used as the VAPID subject. Push
                              services (Google, Apple) may contact this address.
  - VAPID Public Key          Auto-generated on install. Display-only.
                              Do not change unless you want to invalidate all
                              existing push subscriptions.

================================================================================
  FEATURES IN DETAIL
================================================================================

  BOTTOM TAB NAVIGATION
  ---------------------
  A fixed bottom navigation bar with 5 tabs, injected via JavaScript so it
  works with any SMF theme. Tabs:

    Feed       (?action=unread)             Recent unread topics
    Boards     (board index)                Board listing
    Alerts     (?action=profile;area=showalerts)  Your alerts
    Messages   (?action=pm)                 Personal messages
    Profile    (?action=profile)            Your profile (or Login for guests)

  The active tab is highlighted based on the current page URL. Badge counts
  for unread alerts and PMs are shown on the Alerts and Messages tabs.

  STICKY HEADER
  -------------
  A compact header replaces SMF's default top section on mobile with:
  - Forum name / page title
  - Search button
  - Dark mode toggle (moon/sun icon)

  FLOATING ACTION BUTTON
  ----------------------
  A "+" button in the bottom-right corner. Context-aware behavior:
  - Viewing a topic: scrolls to and focuses the quick reply box
  - Viewing a board: opens "New Topic" for that board
  - Elsewhere: navigates to unread topics
  Only shown for logged-in users.

  DARK MODE
  ---------
  Full dark theme using CSS custom properties. Three modes:
  - Light: always light
  - Dark: always dark
  - System: follows the device's prefers-color-scheme setting

  The admin sets the default. Users toggle with the header button.
  Preference is saved to localStorage (works for guests too).
  Overrides all standard SMF CSS classes: windowbg, titlebg, catbg,
  roundframe, buttons, inputs, links, code blocks, quotes, etc.

  PULL TO REFRESH
  ---------------
  Pull down from the top of the page to refresh. Shows a spinner indicator.
  Haptic feedback when the pull threshold is crossed (on supported devices).

  SWIPE GESTURES
  --------------
  Swipe right from the left edge of the screen to go back (history.back).
  Mimics the native iOS/Android back gesture.

  SERVICE WORKER & OFFLINE
  ------------------------
  A service worker is registered for all users (mobile and desktop) to
  enable caching and offline support.

  Caching strategies:
  - Static assets (CSS, JS, images, fonts): Stale-while-revalidate.
    Served instantly from cache, updated in the background.
  - HTML pages: Network-first with cache fallback.
    Always tries the network. Falls back to cached version if offline.
  - AJAX/API calls: Network-only. Never cached to prevent stale data.

  When offline with no cached page available, a styled offline fallback page
  is shown with a "Try Again" button. Respects dark mode preference.

  The service worker is served via ?action=pwa-sw with a
  Service-Worker-Allowed: / header, allowing root scope even though SMF
  URLs are query-string based. It is also installed to the webroot as
  sw.js for direct access.

  ADD TO HOME SCREEN
  ------------------
  Prompts users to install the forum as an app on their device.

  Android/Chrome:
    Intercepts the browser's beforeinstallprompt event and shows a custom
    banner with an "Install" button after the configured number of visits.

  iOS/Safari:
    iOS does not support beforeinstallprompt. The banner instead shows
    manual instructions: "Tap [share icon] then Add to Home Screen"
    with the actual iOS share icon displayed inline.

  The banner is not shown if:
  - The app is already installed (standalone mode detected)
  - The user dismissed the banner previously
  - The visit count hasn't reached the configured delay

  WEB APP MANIFEST
  ----------------
  Served dynamically at ?action=pwa-manifest with values populated from
  your forum settings:
  - name / short_name: from forum name
  - description: from meta description setting
  - theme_color: from accent color setting
  - icons: 192px, 512px, and maskable 512px from /pwa-icons/
  - display: standalone
  - orientation: portrait

================================================================================
  PUSH NOTIFICATIONS
================================================================================

  OVERVIEW
  --------
  When enabled, users can subscribe to receive push notifications on their
  devices. Notifications are delivered for forum alerts such as replies,
  mentions, quotes, and likes.

  HOW IT WORKS
  ------------
  1. The user visits the forum and is prompted to enable notifications.
     - On Android: shown after 3 page visits, or immediately in the
       installed PWA.
     - On iOS: only shown inside the installed home screen app (push
       does not work in regular Safari). Requires iOS 16.4+.
  2. If the user allows, a push subscription is created via the browser's
     Push API and sent to the server.
  3. A scheduled task ("PWA Push Notifications") runs every 2 minutes.
  4. It queries the user_alerts table for new unread alerts belonging to
     members who have push subscriptions.
  5. Each alert is converted into a human-readable notification and sent
     to all of that member's subscribed devices via the Web Push protocol.

  SUPPORTED ALERT TYPES
  ---------------------
  The following SMF events generate push notifications:

    Event                Push Notification Title
    -------------------  --------------------------------
    @mention             "Mentioned by {name}"
    Quote                "Quoted by {name}"
    Like                 "{name} liked your post"
    Reply (watched)      "New reply from {name}"
    New topic (watched)  "New topic by {name}"
    Buddy request        "Buddy Request"
    Group approved       "Group Request Approved"
    Any other alert      "New Notification" (generic)

  Each notification includes the topic subject and a direct link to the
  relevant content. Tapping a notification opens the forum to that page.

  TECHNICAL DETAILS
  -----------------
  - Uses VAPID authentication (keys auto-generated on install via OpenSSL)
  - Payload encryption per RFC 8291 (Web Push Message Encryption)
  - ECDH key exchange + AES-128-GCM encryption
  - Pure PHP implementation using openssl extension - no Composer or
    external libraries required
  - Stale subscriptions (HTTP 410) are automatically cleaned up
  - Push delivery uses cURL
  - Alert tracking uses a high-water mark (last processed alert ID) to
    avoid duplicate notifications

  SCHEDULED TASK
  --------------
  Name:        PWA Push Notifications
  Location:    Admin > Scheduled Tasks
  Frequency:   Every 2 minutes (configurable in admin)
  Description: Checks for new alerts and sends push notifications to
               members with subscribed devices.

================================================================================
  FILE LIST
================================================================================

  Sources/PWA/
    PWAIntegration.php     Hook registration, action routing, CSS/JS injection
    PWAManifest.php        Dynamic manifest.json endpoint
    PWAPush.php            Push subscription API, Web Push sending, scheduled
                           task for alert-to-push delivery
    PWAAdmin.php           Admin settings panel

  Themes/default/css/
    pwa-shell.css          Mobile shell layout: bottom nav, header, FAB,
                           pull-to-refresh, A2HS banner, layout transforms
    pwa-dark.css           Dark mode CSS custom properties and SMF overrides

  Themes/default/scripts/
    pwa-app.js             Shell controller: bottom nav, gestures, dark mode,
                           A2HS prompt, pull-to-refresh
    pwa-sw-register.js     Service worker registration with update detection
    pwa-push.js            Push subscription client with iOS-aware opt-in

  Themes/default/
    PWAAdmin.template.php  Admin settings template (VAPID key display)

  pwa-icons/
    icon-192.png           PWA icon 192x192 (placeholder - replace with yours)
    icon-512.png           PWA icon 512x512 (placeholder - replace with yours)
    icon-maskable-512.png  Maskable PWA icon 512x512 (placeholder)
    icon-192.svg           SVG source for 192px icon
    icon-512.svg           SVG source for 512px icon
    icon-maskable-512.svg  SVG source for maskable icon

  Root files:
    sw.js                  Service worker (installed to webroot)
    offline.html           Offline fallback page

  Install/Uninstall:
    package-info.xml       SMF package manifest
    install.php            VAPID key generation and default settings
    install_db.php         Database table and scheduled task creation
    uninstall.php          Clean removal of all data and tasks

================================================================================
  HOOKS USED
================================================================================

  This mod uses only integration hooks. No core SMF files are modified.

  integrate_actions        Registers custom actions:
                           ?action=pwa-manifest  (dynamic manifest.json)
                           ?action=pwa-push      (push subscription API)
                           ?action=pwa-sw        (service worker serving)

  integrate_load_theme     Injects manifest link, meta tags, CSS/JS files,
                           and JavaScript variables on every page load.
                           Also sets scheduled task language strings.

  integrate_admin_areas    Registers the PWA Settings page under
                           Admin > Configuration.

================================================================================
  DATABASE CHANGES
================================================================================

  Tables created:
    {db_prefix}pwa_push_subscriptions
      id            INT AUTO_INCREMENT PRIMARY KEY
      id_member     INT (indexed)
      endpoint      TEXT
      p256dh        VARCHAR(255)
      auth          VARCHAR(255)
      created_at    INT

  Settings added to {db_prefix}settings:
    pwa_enabled, pwa_push_enabled, pwa_dark_default, pwa_accent_color,
    pwa_a2hs_delay, pwa_offline_msg, pwa_vapid_public, pwa_vapid_private,
    pwa_vapid_private_pem, pwa_vapid_email, pwa_push_last_alert_id

  Scheduled task added to {db_prefix}scheduled_tasks:
    pwa_push_alerts (every 2 minutes)

  All database changes are fully reversed on uninstall.

================================================================================
  BROWSER COMPATIBILITY
================================================================================

  Mobile (full PWA experience):
  - Chrome for Android 67+
  - Samsung Internet 9.2+
  - Safari iOS 16.4+ (push requires installed PWA)
  - Firefox for Android 100+
  - Edge for Android 67+

  Desktop (service worker caching only, no shell UI):
  - All modern browsers with service worker support

  Push notification support:
  - Chrome 50+, Edge 17+, Firefox 44+, Safari 16.4+ (installed PWA only)
  - NOT supported: Safari on macOS (as of 2025)

================================================================================
  CUSTOMIZING ICONS
================================================================================

  The default icons are placeholder images with a blue background and "F"
  letter. To replace them with your forum's branding:

  1. Create PNG images at these sizes:
     - 192x192 pixels (icon-192.png)
     - 512x512 pixels (icon-512.png)
     - 512x512 pixels with extra padding for safe zone (icon-maskable-512.png)

  2. Upload them to your forum's /pwa-icons/ directory, replacing the
     existing files.

  For the maskable icon, keep important content within the center 80% of
  the image. The outer 10% on each side may be cropped by the device.

  Tip: Use https://maskable.app to preview and test your maskable icon.

================================================================================
  TROUBLESHOOTING
================================================================================

  "VAPID key generation failed" on install:
    Your server's OpenSSL may not find its config file. The installer tries
    multiple fallback paths. Check that openssl.cnf exists and PHP's OpenSSL
    extension supports EC (P-256) keys. PHP 7.4+ with OpenSSL 1.1+ required.

  Service worker not registering:
    Ensure your forum is served over HTTPS. Service workers require a secure
    context. Check browser DevTools > Application > Service Workers for errors.

  Push notifications not arriving:
    - Check Admin > Scheduled Tasks: ensure "PWA Push Notifications" is
      enabled and running (last run time should be recent).
    - Verify the VAPID Contact Email is set in PWA Settings.
    - Check that the user has allowed notifications (browser permissions).
    - On iOS: push only works in the installed home screen app, not Safari.

  Bottom nav not showing:
    The mobile shell only activates on mobile viewports (max-width: 768px)
    and when the mod is enabled. Check Admin > PWA Shell Settings.

  Icons not displaying:
    Ensure the /pwa-icons/ directory exists in your forum root with the
    PNG files. Check file permissions (readable by web server).

  Dark mode not persisting:
    Dark mode preference is saved to localStorage. If the user clears
    browser data, the preference resets to the admin default.

================================================================================
  CHANGELOG
================================================================================

  1.0.0 - Initial Release
    - Bottom tab navigation with badge counts
    - Sticky header with search and dark mode toggle
    - Floating action button (context-aware)
    - Dark mode with system preference detection
    - Pull-to-refresh and swipe-back gestures
    - Service worker with offline fallback
    - Web Push notifications via scheduled task
    - Dynamic web app manifest
    - iOS "Add to Home Screen" instructions
    - Android install prompt interception
    - Admin settings panel
    - Clean install/uninstall via hooks only

================================================================================
