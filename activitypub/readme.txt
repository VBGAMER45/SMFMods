ActivityPub Federation for SMF 2.1
====================================
By: vbgamer45
https://www.smfhacks.com

This mod enables your SMF forum to federate with the fediverse (Mastodon,
Lemmy, etc.) using the ActivityPub protocol. Forum boards become Group actors
that remote users can follow and interact with.

Requirements:
- SMF 2.1.x
- PHP 7.4+ with openssl, curl, and json extensions
- HTTPS (required for federation)

Installation:
1. Upload the package via Admin > Package Manager > Upload Package
2. Install the package
3. Configure at Admin > Configuration > ActivityPub Federation

WebFinger Setup (Required for Discovery):
Your web server must route /.well-known/webfinger requests to the included
handler. Add one of these rewrite rules:

Apache (.htaccess in your forum root):
  RewriteEngine On
  RewriteRule ^\.well-known/webfinger$ well-known/webfinger.php [L,QSA]

Nginx (in your server block):
  location = /.well-known/webfinger {
      rewrite ^ /well-known/webfinger.php last;
  }

Alternatively, copy well-known/webfinger.php into your site's actual
.well-known/ directory and adjust the SSI.php path inside it.

Features:
- Boards as Group actors (FEP-1b12 standard)
- Bidirectional federation: posts flow out, replies flow in
- Per-board federation control
- Domain block/silence list
- HTTP Signature authentication
- Background delivery with exponential retry
- Admin dashboard with delivery status


