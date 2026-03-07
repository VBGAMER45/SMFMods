Ultimate Shoutbox & Chatroom v1.1.0
====================================
A feature-rich, AJAX-powered shoutbox and chatroom for SMF 2.1.

Features:
- Real-time shoutbox widget on every page (configurable placement)
- Full-page chatroom with online users sidebar
- Multi-room support with public/private rooms and group-based access
- Whisper (private) messages via /whisper or /w commands
- Admin messages visible to moderators only via /admin or /a commands
- Message reactions with 5 icon types (like, dislike, star, heart, award)
- GIF picker with Tenor and Giphy support (server-side proxy)
- @mention autocomplete (searches online users + SMF member DB)
- Slash command autocomplete (/whisper, /prune, /clean, /mute, etc.)
- Inline message editing and deletion
- Moderation tools: ban/mute/unmute users, prune/clean messages
- /me action messages
- Sound notifications with tab-away title flash and unread counter
- Searchable message history with date filtering and CSV/text export
- Auto-prune old messages (configurable retention period)
- Flood protection for non-moderators
- Per-room online presence tracking via cache
- BBCode and smiley parsing support
- Fully responsive design with dark mode support
- 100% hook-based installation (zero core file edits)
- 5 configurable permissions (view, post, moderate, whisper, gif)
- Guest read-only access (optional)
- Adaptive polling with exponential backoff on errors
- SMF cache integration for reduced database load

Installation:
1. Go to Admin > Packages > Install a New Package
2. Upload the mod package or install from directory
3. Set permissions under Admin > Permissions > Shoutbox
4. Configure settings under Admin > Configuration > Shoutbox Settings

For GIF support:
- Tenor: Get a free API key from Google Cloud Console
  (APIs & Services > Enable "Tenor API" > Create Credentials)
- Giphy: Get a free API key from developers.giphy.com

Notification Sound:
- A WAV notification chime is included.
- For smaller file sizes, optionally provide notification.mp3
  and/or notification.ogg in the shoutbox-sounds directory.
  The player will prefer MP3 > OGG > WAV automatically.

Credits & Licenses:
- Icons: FamFamFam Silk icon set by Mark James
  http://www.famfamfam.com/lab/icons/silk/
  Licensed under
  Creative Commons Attribution-ShareAlike 4.0 International
  https://creativecommons.org/licenses/by-sa/4.0/
- Notification sound generated procedurally (public domain)
- This modification is provided as-is with no warranty.

Requirements:
- SMF 2.1.x
- PHP 7.0+
- MySQL 5.6+ / MariaDB 10.0+
