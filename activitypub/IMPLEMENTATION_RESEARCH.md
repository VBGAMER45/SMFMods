# ActivityPub Implementation Research for SMF Mod

> Survey of existing ActivityPub implementations in PHP and forum software, with architectural patterns, lessons learned, and recommendations for the SMF ActivityPub mod.

---

## Table of Contents

1. [PHP Libraries for ActivityPub](#1-php-libraries-for-activitypub)
2. [WordPress ActivityPub Plugin (PHP, Most Mature)](#2-wordpress-activitypub-plugin)
3. [Discourse ActivityPub Plugin (Forum, Ruby)](#3-discourse-activitypub-plugin)
4. [NodeBB v4 ActivityPub (Forum, Node.js)](#4-nodebb-v4-activitypub)
5. [Lemmy ActivityPub (Forum/Link Aggregator, Rust)](#5-lemmy-activitypub)
6. [phpBB and SMF Prior Art](#6-phpbb-and-smf-prior-art)
7. [FEP-1b12 Group Federation Standard](#7-fep-1b12-group-federation-standard)
8. [Conversational Contexts and Threading](#8-conversational-contexts-and-threading)
9. [Mastodon Compatibility Requirements](#9-mastodon-compatibility-requirements)
10. [HTTP Signatures in PHP](#10-http-signatures-in-php)
11. [WebFinger Implementation](#11-webfinger-implementation)
12. [Database Schema Patterns](#12-database-schema-patterns)
13. [Common Gotchas and Pitfalls](#13-common-gotchas-and-pitfalls)
14. [Architectural Recommendations for SMF](#14-architectural-recommendations-for-smf)

---

## 1. PHP Libraries for ActivityPub

### 1.1 ActivityPhp (landrok/activitypub)

- **Packagist:** `landrok/activitypub` (50,000+ installs)
- **GitHub:** https://github.com/landrok/activitypub
- **Latest:** v0.8.1 (October 2025)
- **PHP Requirement:** ^8.0
- **License:** MIT

**Dependencies:**
- `guzzlehttp/guzzle` - HTTP client
- `monolog/monolog` - Logging
- `symfony/cache` and `symfony/cache-contracts` - Caching
- `phpseclib/phpseclib: ^3.0.7` - Cryptographic operations (HTTP Signatures)

**Key Features:**
- Type-safe ActivityStreams 2.0 object creation and manipulation
- Server component with inbox/outbox handling
- Automatic HTTP Signature verification on inbox POSTs
- Dialect system for extensions (e.g., Mastodon, PeerTube ontologies)
- WebFinger client for actor discovery

**Usage Pattern:**
```php
use ActivityPhp\Server;
use ActivityPhp\Type;

// Create server instance
$server = new Server([
    'instance' => ['host' => 'mysite.com', 'port' => 443],
    'logger'   => ['driver' => 'monolog'],
    'cache'    => ['enabled' => true],
]);

// Create a Note object
$note = Type::create('Note', [
    'content'      => '<p>Hello Fediverse!</p>',
    'attributedTo' => 'https://mysite.com/users/alice',
    'to'           => ['https://www.w3.org/ns/activitystreams#Public'],
]);

// Fetch remote outbox
$outbox = $server->outbox('user@remote.example');
```

**Assessment for SMF:** Good for type creation and validation, but the server component makes assumptions about routing. Best used selectively for type creation and HTTP signature handling rather than as a full server framework. SMF has its own routing system.

### 1.2 ActivityPub-PHP (pterotype/activitypub-php)

- **Packagist:** `pterotype/activitypub-php`
- **GitHub:** https://github.com/pterotype-project/activitypub-php
- **Updated:** December 2025
- **PHP Requirement:** >=5.5
- **License:** MIT

**Dependencies:**
- `phpseclib/phpseclib: ^2.0` - HTTP Signatures
- `doctrine/dbal` - Database abstraction (works with MySQL, PostgreSQL, SQLite)
- `symfony/http-foundation` - HTTP request/response handling

**Key Features:**
- Embeds a full ActivityPub server into any PHP project
- Works with any SQL database via Doctrine
- Configurable table name prefix (ideal for SMF integration)
- Handles both C2S and S2S protocols
- Signs outgoing requests, verifies incoming signatures
- JSON-LD context management with custom field hooks
- Authentication callback system for integrating with existing user systems

**Usage Pattern:**
```php
use ActivityPub\ActivityPub;
use ActivityPub\Config\ActivityPubConfig;

$config = ActivityPubConfig::createBuilder()
    ->setAuthFunction(function() {
        // Hook into SMF's authentication
        global $user_info;
        return $user_info['is_guest'] ? false : $user_info['id'];
    })
    ->setDbConnectionParams([
        'driver'   => 'pdo_mysql',
        'user'     => $db_user,
        'password' => $db_passwd,
        'dbname'   => $db_name,
    ])
    ->setDbPrefix('smf_activitypub_')
    ->build();

$activitypub = new ActivityPub($config);

// Handle incoming AP request
if (in_array($_SERVER['HTTP_ACCEPT'], [
    'application/ld+json',
    'application/activity+json'
])) {
    $response = $activitypub->handle();
    $response->send();
}
```

**Assessment for SMF:** Most promising library for integration. The configurable database prefix, authentication callbacks, and framework-agnostic design make it suitable for SMF. However, it pulls in Doctrine which is a heavy dependency. Consider whether using it selectively or building a lighter custom solution is better.

### 1.3 RikudouSage/ActivityPub

- **GitHub:** https://github.com/RikudouSage/ActivityPub
- **Focus:** Strongly typed, validated ActivityPub objects
- **Approach:** Emphasizes type safety and developer experience

**Assessment for SMF:** Useful reference for type validation patterns, but less established than the above two.

### 1.4 dansup/php-activitypub

- **Packagist:** `dansup/php-activitypub`
- **PHP Requirement:** ~5.6|~7.0
- **Note:** Created by the Pixelfed developer. Lightweight but less maintained.

### 1.5 Recommendation for SMF

**Primary recommendation: Custom implementation borrowing patterns from these libraries.** Rationale:

1. SMF does not use Composer by default, and adding heavy dependency trees (Guzzle, Doctrine, Symfony) is not aligned with SMF mod conventions.
2. SMF has its own database abstraction layer (`$smcFunc['db_query']`), HTTP handling, and caching.
3. The core ActivityPub operations (JSON-LD serialization, HTTP signatures, type validation) can be implemented with PHP's built-in `openssl_*` functions and `json_encode/decode`.
4. Reference the libraries above for correct implementation patterns, especially ActivityPhp's type system and pterotype's database schema approach.

---

## 2. WordPress ActivityPub Plugin

- **GitHub:** https://github.com/Automattic/wordpress-activitypub
- **Maintained by:** Automattic (acquired March 2023)
- **License:** MIT
- **PHP:** 85.8% of codebase
- **Commits:** 2,730+, 142+ releases (very active)
- **Latest:** v7.9.1

### 2.1 Architecture Overview

**Actor Model:**
- WordPress users are the default ActivityPub actors (Person type)
- A "Blog Actor" represents the site itself (Application type)
- Blog-wide profile: `@example.com@example.com`
- Individual author profiles: `@jane@example.com`, `@bob@example.com`

**Transformer Pattern:**
- `includes/transformer/class-post.php` - Converts WP posts to AP objects
- Each WordPress Post-Format maps to an AP Object Type
- Base Transformer handles `WP_Term` objects
- Extensible: developers can create custom transformers for custom post types

**Key Classes:**
- Activity Handler - Processes incoming activities
- Inbox Handler - Global inbox with persistence layer
- Followers management with dedicated admin table UI
- Scheduler integration for async delivery

**Content Flow:**
```
WP Post Created
  -> Hook fires
  -> Transformer converts to AP Note/Article
  -> Create activity wrapped
  -> HTTP Signature applied
  -> Delivered to followers' inboxes (async via WP cron)
```

### 2.2 Database Approach

The WordPress plugin currently stores followers and following data using WordPress's built-in custom post types and metadata tables. A proposed (but rejected) schema suggested dedicated tables:

**Proposed tables (not implemented, but informative):**
- `ap_actors` - Federated profiles distinct from WP users
- `ap_activities` - Activity log (Create, Like, Follow, etc.)
- `ap_objects` - Central repository for local/remote objects
- `ap_actor_relationships` - Maps WP author IDs to AP actor IDs
- `ap_media_relationships` - Media URL routing per actor
- `ap_terms` / `ap_term_taxonomy` - Federated hashtags/mentions/emoji

**Why rejected:** WordPress.com compatibility constraints prevent separate database tables. They use WP's existing `wp_posts`, `wp_postmeta`, `wp_comments`, and custom post types instead.

**Lesson for SMF:** SMF does not have WordPress.com compatibility constraints. Dedicated tables are cleaner and more performant for a forum. Follow the proposed schema patterns.

### 2.3 Key Lessons

1. **Actor = User mapping works well** for blog-style content
2. **Blog/Site actor** is useful for site-wide federation (announcements, etc.)
3. **Transformer pattern** is powerful for mapping platform-specific content to AP objects
4. **Async delivery** is essential (WP uses wp-cron; SMF would need scheduled tasks)
5. **Fallback handling:** When Update fails for missing posts, fall back to Create handling
6. **Site Health checks** detect broken federation (scheduled events missing, security plugins blocking REST API)

---

## 3. Discourse ActivityPub Plugin

- **GitHub:** https://github.com/discourse/discourse-activity-pub
- **Language:** Ruby (Discourse is Rails-based)
- **Status:** Active development

### 3.1 Architecture Overview

**Actor Mapping:**
- Categories and Tags become `Group` actors
- Users become `Person` actors
- Each actor has a unique `ap_id` (HTTPS URL), RSA keypair, inbox URL, outbox URL
- Three actor types: Person, Group, Application (server instance)

**Content Mapping:**
- Posts become `Note` or `Article` objects
- Topics become `OrderedCollection` (in full_topic mode)
- Collections contain ordered post objects

**Data Models (4 core models):**
```
DiscourseActivityPubActor       -> Categories/Tags/Users
DiscourseActivityPubActivity    -> Create/Update/Delete/Like/Follow/Accept
DiscourseActivityPubObject      -> Note/Article/Tombstone (linked to Posts)
DiscourseActivityPubCollection  -> OrderedCollection (linked to Topics)
```

**Polymorphic Associations:**
```
Actor (polymorphic: Category/Tag/User)
  |-- has_many Activities
  |-- has_many Followers (through Follow records)
  |-- public_key, private_key (RSA keypair)

Activity (Create/Update/Delete/Like/Follow/Accept)
  |-- belongs_to Actor
  |-- belongs_to Object (polymorphic)
  |-- ap_type, visibility

Object (Note/Article/Tombstone)
  |-- belongs_to Model (polymorphic: Post)
  |-- belongs_to Collection
  |-- attributed_to (creator Actor)
  |-- reply_to (parent Object)
  |-- content (HTML)

Collection (OrderedCollection)
  |-- belongs_to Model (polymorphic: Topic)
  |-- ordered_items (array of Object ids)
```

### 3.2 Federation Modes

**First Post Mode:**
- Only the opening post of a topic is federated
- Attributed to the Category/Tag actor
- Subsequent replies remain local
- Best for announcements and news feeds
- Simpler to implement

**Full Topic Mode:**
- All posts in a topic are federated
- Posts are attributed to individual user actors
- Topic becomes an OrderedCollection containing all post objects
- Enables bidirectional discussion (remote replies become local posts)
- More complex but more powerful

**Lesson for SMF:** Both modes are valuable. Start with "First Post" mode for Phase 1 (simpler), add "Full Topic" mode in Phase 2. SMF boards map naturally to Discourse categories.

### 3.3 Activity Processing Pipeline

```
1. Event triggers (post create/edit/delete)
2. Activity creation wraps content in Create/Update/Delete
3. Signature generation signs with actor's private key
4. Delivery scheduling queues with configurable delay
5. Remote inbox delivery POSTs signed JSON-LD to followers
```

Delete activities bypass the delay and deliver immediately.

### 3.4 Inbound Security Pipeline

```
1. Rate limiting (per configured minute thresholds)
2. Signature parsing (extracts keyId, algorithm, headers)
3. Timestamp validation (12-hour window with 1-hour skew)
4. Digest verification (SHA-256 body integrity)
5. RSA verification (validate signature against public key)
6. Domain filtering (allowed/blocked origin lists)
```

**Lesson for SMF:** This is a comprehensive security pipeline. Implement all of these steps.

### 3.5 Addressing and Visibility

- Public posts: `https://www.w3.org/ns/activitystreams#Public` in `to`
- Private posts: omit public addressing, use `cc` for audience
- Followers receive via inbox subscriptions

---

## 4. NodeBB v4 ActivityPub

- **Software:** NodeBB (Node.js forum software)
- **Version:** v4.0+ (core feature, not a plugin)
- **Status:** Production, actively maintained
- **Documentation:** https://docs.nodebb.org/activitypub/

### 4.1 Architecture

**Actor Mapping:**
- Users are `Person` actors
- Categories are `Group` actors (per FEP-1b12)
- Each category gets a unique handle (slugified name, e.g., `category-about-cats`)
- Categories can follow other Group actors for cross-forum sync

**Content Mapping:**
- Topics contain posts
- The `audience` property is set to the category (second-order parent)
- The `context` property is set to the topic (first-order parent)
- This deviates from FEP-1b12's expectation that audience is one order higher than the post

**NodeBB's hierarchy:**
```
Category (Group actor, audience)
  |-- Topic (context, OrderedCollection)
        |-- Post (Note object, with inReplyTo)
```

**Key Design Decisions:**
- Federation is a core feature, not a plugin (required deep changes to core)
- Global toggle in admin panel (ACP > Settings > Federation)
- Upgrading from v3.x has federation disabled by default
- New forums federate automatically

### 4.2 Category Federation

- Categories federate content outward via `Announce` activities
- Categories maintain follow relationships with remote actors/users
- Categories can follow other Group actors
- Remote users can post directly to a category by addressing its handle
- No Announce federation for the "Uncategorized" pseudo-category

### 4.3 Remote Content Handling

- "Uncategorized" pseudo-category acts as catch-all for remote content
- Content pruning logic runs only on the Uncategorized category
- Remote content can be moved to proper categories by moderators

### 4.4 Lessons for SMF

1. **Boards as Group actors** is the right approach (NodeBB validates this)
2. **audience = board, context = topic** is the correct property mapping
3. **Category handles** (slugified board names) enable direct addressing
4. **Uncategorized bucket** for incoming remote content is practical
5. **Admin toggle** for federation is essential
6. **Content pruning** for remote content prevents database bloat

---

## 5. Lemmy ActivityPub

- **Software:** Lemmy (Rust-based link aggregator/forum)
- **Status:** Mature implementation, widely deployed

### 5.1 Architecture

**Actor Mapping:**
- Communities are `Group` actors (primary actor type)
- User accounts are `Person` actors
- Communities are discoverable via WebFinger: `!activitypub@lemmy.example`

**Content Mapping:**
- Top-level posts use `Page` objects (not Note)
- Comments use `Note` objects
- This distinction between posts and comments is unique to Lemmy

**The Announcement/Relay Pattern (critical for forums):**
```
1. User creates a post/comment
2. User's instance sends Create activity to Community inbox
3. Community actor wraps it in an Announce activity
4. Community sends Announce to ALL followers
5. All following instances receive and process the content
```

This means the Community/Group actor acts as a relay/hub:
```
User@instance-A  --Create-->  Community@instance-B  --Announce-->  All Followers
```

### 5.2 Activity Distribution

When a Community receives any of these activities, it Announces them to all followers:
- `Create` (new post/comment)
- `Update` (edited post/comment)
- `Like` / `Dislike` (votes)
- `Remove` / `Delete`
- `Undo` (undo vote, etc.)

### 5.3 Lessons for SMF

1. **Group-as-relay pattern** is the standard for forum federation
2. **Boards should Announce** all activities to their followers
3. **Separate object types** for topics vs replies is valid (Page vs Note)
4. **Votes are transparent** across the federation (different from Reddit)
5. **Following a board** means subscribing to all its content

---

## 6. phpBB and SMF Prior Art

### 6.1 phpBB

No ActivityPub implementation exists for phpBB. Web searches found no plugins, mods, or even discussion threads about adding federation to phpBB. This represents a gap in the PHP forum ecosystem.

### 6.2 SMF

No ActivityPub implementation exists for SMF either. Searches of the Simple Machines community, modification repository, and GitHub found zero results for ActivityPub, fediverse, or federation-related mods.

**This means the SMF ActivityPub mod would be the first ActivityPub implementation for either of the two major PHP forum platforms.** This is a significant opportunity.

### 6.3 Other PHP Forum Software

No ActivityPub implementations were found for:
- MyBB
- Vanilla Forums (self-hosted PHP version)
- FluxBB
- PunBB

The only forum software with production ActivityPub support are:
- **NodeBB** (Node.js) - core feature since v4
- **Discourse** (Ruby) - official plugin
- **Lemmy** (Rust) - core feature (though Lemmy is more link-aggregator than forum)

---

## 7. FEP-1b12 Group Federation Standard

- **Status:** FINAL (February 9, 2023)
- **Source:** https://codeberg.org/fediverse/fep/src/branch/main/fep/1b12/fep-1b12.md
- **Purpose:** Defines a common subset of ActivityPub for mutually compatible forum federation

### 7.1 Core Concepts

**Group Actor:**
- Each Group actor represents a single forum/board
- Type: `Group`
- Has standard actor properties (inbox, outbox, followers, etc.)

**The `audience` Property:**
- Used to indicate that a given object belongs to a group
- Part of ActivityStreams vocabulary (not an extension)
- Won't cause problems for implementations that don't understand it
- Example: `"audience": "https://forum.example/boards/general"`

**Content Flow:**
```
1. User sends Create(Note) to Group inbox
   - Note includes "audience": group-id
2. Group validates and accepts
3. Group wraps in Announce(Create(Note))
4. Group delivers Announce to all followers
```

### 7.2 Platform Implementations

| Platform | Group Actor | Announce Pattern | audience Property |
|----------|------------|-----------------|-------------------|
| Lemmy | Community (Group) | Yes, all activities | Yes |
| Friendica | Forum (Group) | Yes | Yes |
| NodeBB | Category (Group) | Yes | Yes (second-order) |
| Discourse | Category (Group) | Implicit | Not yet |
| Mastodon | N/A (Person only) | N/A | Ignores safely |

### 7.3 Implications for SMF

- **SMF Boards MUST be Group actors** to comply with FEP-1b12
- **SMF Board actors MUST use the Announce pattern** to relay content
- **All federated objects SHOULD include the `audience` property** pointing to the board
- **Topics use `context`** to group posts within a topic
- **Posts use `inReplyTo`** to establish parent-child relationships

---

## 8. Conversational Contexts and Threading

### 8.1 Current State (In Progress)

The W3C Social CG is working on standardizing conversational contexts, but the specification is not yet complete.

**Hierarchy:**
```
Audience  = Board/Category/Community (Group actor)
Context   = Topic/Thread/Conversation (OrderedCollection)
Object    = Post/Reply/Comment (Note)
```

**Properties used:**
- `audience` - Points to the Group actor (board)
- `context` - Points to the topic/conversation (OrderedCollection or URL)
- `inReplyTo` - Points to the parent post (for threading)
- `replies` - Collection of child posts (not broadly implemented)

### 8.2 FEP-7888 (Draft)

Proposes using `context` to refer to an object with an associated ordered collection. This allows:
- Fetching all posts within a thread
- Thread metadata (title, audience, moderator/owner)
- Conversational backfill (retrieving entire conversations)

### 8.3 Practical Implementation for SMF

**Topic as OrderedCollection:**
```json
{
  "@context": "https://www.w3.org/ns/activitystreams",
  "id": "https://forum.example/topic/123",
  "type": "OrderedCollection",
  "attributedTo": "https://forum.example/users/alice",
  "audience": "https://forum.example/boards/general",
  "name": "Topic Title Here",
  "totalItems": 5,
  "orderedItems": [
    "https://forum.example/post/456",
    "https://forum.example/post/457",
    "https://forum.example/post/458"
  ]
}
```

**Post as Note within a topic:**
```json
{
  "@context": "https://www.w3.org/ns/activitystreams",
  "id": "https://forum.example/post/457",
  "type": "Note",
  "attributedTo": "https://forum.example/users/bob",
  "audience": "https://forum.example/boards/general",
  "context": "https://forum.example/topic/123",
  "inReplyTo": "https://forum.example/post/456",
  "content": "<p>This is a reply in the topic.</p>",
  "published": "2025-01-15T10:30:00Z",
  "to": ["https://www.w3.org/ns/activitystreams#Public"],
  "cc": ["https://forum.example/boards/general/followers"]
}
```

### 8.4 Backfill Strategy

When encountering a new post:
1. Check `context` for an OrderedCollection URL
2. If available, fetch the collection to get all posts in the thread (FEP-f228)
3. If not, follow `inReplyTo` chain upward to find the root post
4. Walk the reply tree downward via `replies` collections

---

## 9. Mastodon Compatibility Requirements

Mastodon is the most widely deployed ActivityPub server. Compatibility with Mastodon is essential.

### 9.1 Supported Activity Types

**For Statuses:**
- `Create` - New post (transforms to status)
- `Delete` - Remove status
- `Like` - Favorite
- `Announce` - Boost/reblog
- `Update` - Edit post / refresh poll
- `Undo` - Reverse Like/Announce
- `Flag` - Moderation report

**For Profiles:**
- `Follow` / `Accept` / `Reject` - Follow management
- `Add` / `Remove` - Pin/unpin posts
- `Update` - Refresh account
- `Delete` - Remove account
- `Block` - Block user
- `Move` - Migrate followers

### 9.2 Required Actor Properties

```json
{
  "@context": [
    "https://www.w3.org/ns/activitystreams",
    "https://w3id.org/security/v1"
  ],
  "id": "https://forum.example/users/alice",
  "type": "Person",
  "preferredUsername": "alice",
  "name": "Alice",
  "summary": "<p>Bio here</p>",
  "inbox": "https://forum.example/users/alice/inbox",
  "outbox": "https://forum.example/users/alice/outbox",
  "followers": "https://forum.example/users/alice/followers",
  "following": "https://forum.example/users/alice/following",
  "publicKey": {
    "id": "https://forum.example/users/alice#main-key",
    "owner": "https://forum.example/users/alice",
    "publicKeyPem": "-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----"
  },
  "icon": {
    "type": "Image",
    "mediaType": "image/png",
    "url": "https://forum.example/avatars/alice.png"
  },
  "manuallyApprovesFollowers": false,
  "discoverable": true,
  "url": "https://forum.example/users/alice"
}
```

**Critical:** Mastodon requires an `inbox` even if you don't plan to receive messages. Inbox-less actors are not recognized.

### 9.3 Object Type Handling

| Object Type | Mastodon Behavior |
|------------|-------------------|
| `Note` | Full support, becomes a status |
| `Question` | Becomes a poll |
| `Article` | Fallback: uses content/name as text, appends URL |
| `Page` | Fallback: same as Article |
| `Image` | Fallback: content + url |
| `Audio` | Fallback: content + url |
| `Video` | Fallback: content + url |
| `Event` | Fallback: content + url |

**Recommendation:** Use `Note` as the primary object type for maximum compatibility. Use `Article` for topic first-posts if they are long-form. Mastodon will display both, but Note gets full rendering.

### 9.4 Content Sanitization

Mastodon strips most HTML. Allowed elements (as of v4.2):
```
<p>, <span>, <br>, <a>, <del>, <pre>, <code>,
<em>, <strong>, <b>, <i>, <u>,
<ul>, <ol>, <li>, <blockquote>
```

Headings (`<h1>`-`<h6>`) are converted to `<strong>` wrapped in `<p>`.

Preserved CSS classes: `h-*`, `p-*`, `u-*`, `dt-*`, `e-*`, `mention`, `hashtag`, `ellipsis`, `invisible`.

**Lesson for SMF:** Forum posts often contain rich BBCode formatting. The transformer must convert SMF BBCode to HTML that survives Mastodon's sanitizer. Test with tables, images, code blocks, quotes.

### 9.5 Visibility via Addressing

```
Public:    "to": ["as:Public"]
Unlisted:  "cc": ["as:Public"]  (not in "to")
Private:   followers in to/cc, no as:Public
Direct:    only mentioned actors in to/cc
```

**Mention tags are required for notifications:**
```json
{
  "tag": [{
    "type": "Mention",
    "href": "https://mastodon.social/users/someone",
    "name": "@someone@mastodon.social"
  }]
}
```

### 9.6 Mastodon Non-Standard Extensions

**Namespace:** `toot:` (Mastodon-specific)
- `Emoji` - Custom emoji
- `blurhash` - Image preview hashes
- `discoverable` - Profile discovery opt-in
- `featured` - Pinned posts collection
- `featuredTags` - Hashtag collections
- `focalPoint` - Image crop coordinates
- `indexable` - Search indexing opt-in
- `sensitive` - Content warning flag

**Profile fields** use `attachment` with `PropertyValue` (schema.org):
```json
{
  "attachment": [{
    "type": "PropertyValue",
    "name": "Website",
    "value": "<a href=\"https://example.com\">example.com</a>"
  }]
}
```

### 9.7 Group Actor Compatibility with Mastodon

Mastodon does not natively support Group actors, but it handles them gracefully:
- Group actors appear as regular accounts
- Announce activities from Groups appear as boosts
- Following a Group works like following a Person
- The `audience` property is safely ignored

This means SMF boards will appear as "accounts" on Mastodon that "boost" every topic posted in them. This is the standard behavior for Lemmy communities viewed from Mastodon.

---

## 10. HTTP Signatures in PHP

### 10.1 Overview

HTTP Signatures are the de facto authentication mechanism for ActivityPub server-to-server communication. Mastodon requires them for all POST requests to inboxes and optionally for all GET requests.

**Specification:** Draft RFC 9421 (draft 12, October 2019) -- note this is a DRAFT version, not the final RFC.

### 10.2 Signing Process (Outgoing Requests)

```php
// 1. Generate the digest of the request body
$body = json_encode($activity);
$digest = 'SHA-256=' . base64_encode(hash('sha256', $body, true));

// 2. Build the signature string
$date = gmdate('D, d M Y H:i:s \G\M\T');
$target = 'post /users/alice/inbox';
$host = 'remote.example';

$signatureString = implode("\n", [
    "(request-target): {$target}",
    "host: {$host}",
    "date: {$date}",
    "digest: {$digest}",
    "content-type: application/activity+json",
]);

// 3. Sign with RSA-SHA256
$privateKey = openssl_get_privatekey($pemString);
openssl_sign($signatureString, $signature, $privateKey, OPENSSL_ALGO_SHA256);
$signatureB64 = base64_encode($signature);

// 4. Build the Signature header
$keyId = 'https://forum.example/users/alice#main-key';
$headers = '(request-target) host date digest content-type';
$signatureHeader = sprintf(
    'keyId="%s",algorithm="rsa-sha256",headers="%s",signature="%s"',
    $keyId, $headers, $signatureB64
);

// 5. Send the request with all headers
// Headers: Host, Date, Digest, Content-Type, Signature, Accept
```

### 10.3 Verification Process (Incoming Requests)

```php
// 1. Parse the Signature header
$sigHeader = $_SERVER['HTTP_SIGNATURE'];
// Parse keyId, algorithm, headers, signature from header

// 2. Fetch the actor's public key via keyId URL
$actor = fetchJson($keyId);
$publicKeyPem = $actor['publicKey']['publicKeyPem'];

// 3. Validate keyId matches actor
if ($actor['publicKey']['id'] !== $keyId) {
    throw new Exception('Key mismatch');
}

// 4. Reconstruct the signature string from actual request headers
// Map header names to $_SERVER values

// 5. Verify the signature
$publicKey = openssl_get_publickey($publicKeyPem);
$valid = openssl_verify(
    $reconstructedString,
    base64_decode($signature),
    $publicKey,
    OPENSSL_ALGO_SHA256
);

// 6. Verify the digest
$expectedDigest = 'SHA-256=' . base64_encode(
    hash('sha256', file_get_contents('php://input'), true)
);
if ($expectedDigest !== $_SERVER['HTTP_DIGEST']) {
    throw new Exception('Digest mismatch');
}

// 7. Validate timestamp (within 12 hours)
$requestDate = strtotime($_SERVER['HTTP_DATE']);
if (abs(time() - $requestDate) > 43200) {
    throw new Exception('Request too old');
}
```

### 10.4 Key Management

- Generate RSA 2048-bit keypair per actor
- Store private key encrypted in database
- Expose public key in actor JSON document
- Key ID format: `{actor_url}#main-key`

```php
// Generate keypair
$config = [
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];
$resource = openssl_pkey_new($config);
openssl_pkey_export($resource, $privateKey);
$publicKey = openssl_pkey_get_details($resource)['key'];
```

### 10.5 PHP Libraries for HTTP Signatures

| Library | PHP Version | Notes |
|---------|------------|-------|
| `phpseclib/phpseclib ^3.0` | 8.1+ | Used by ActivityPhp |
| `phpseclib/phpseclib ^2.0` | 5.6+ | Used by pterotype |
| PHP built-in `openssl_*` | 7.0+ | No external dependency |
| `liamdennehy/http-signatures-php` | 7.4+ | Dedicated HTTP sig library |

**Recommendation for SMF:** Use PHP's built-in `openssl_*` functions. SMF requires PHP 8.0+ for v3.0, and OpenSSL is available on virtually all PHP installations. This avoids adding external dependencies.

---

## 11. WebFinger Implementation

### 11.1 Endpoint

**URL:** `/.well-known/webfinger`
**Method:** GET
**Query Parameter:** `resource=acct:username@domain`

### 11.2 Response Format

```json
{
  "subject": "acct:alice@forum.example",
  "aliases": [
    "https://forum.example/users/alice",
    "https://forum.example/@alice"
  ],
  "links": [
    {
      "rel": "self",
      "type": "application/activity+json",
      "href": "https://forum.example/users/alice"
    },
    {
      "rel": "http://webfinger.net/rel/profile-page",
      "type": "text/html",
      "href": "https://forum.example/?action=profile;u=123"
    }
  ]
}
```

### 11.3 For Board Actors (Group)

```json
{
  "subject": "acct:general-discussion@forum.example",
  "links": [
    {
      "rel": "self",
      "type": "application/activity+json",
      "href": "https://forum.example/boards/general-discussion"
    }
  ]
}
```

### 11.4 Implementation in SMF

WebFinger requires handling `/.well-known/webfinger` at the web root. Options:

1. **Apache/Nginx rewrite rule** to route to SMF handler
2. **Physical file** at `/.well-known/webfinger/index.php` that includes SMF
3. **SMF action handler** that checks if the URL matches

**Recommended approach:** Use a rewrite rule to route `/.well-known/webfinger` to `index.php?action=webfinger`, then handle it as a normal SMF action.

```apache
# .htaccess
RewriteRule ^\.well-known/webfinger$ index.php?action=webfinger [L,QSA]
```

### 11.5 Mastodon-Specific Requirements

- `subject` must match the requested `resource`
- `href` must resolve to a valid ActivityPub actor with matching `preferredUsername` and domain
- Mastodon uses WebFinger for all mention resolution; it is required for interoperability
- If the actor's domain differs from the request domain, Mastodon performs a secondary WebFinger lookup

---

## 12. Database Schema Patterns

### 12.1 Common Tables Across Implementations

Based on WordPress (proposed), Discourse, Lemmy, NodeBB, and the PHP/MySQL implementation guide:

**Actors Table:**
```sql
CREATE TABLE {prefix}ap_actors (
    id_actor        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ap_id           VARCHAR(512) NOT NULL,       -- Full ActivityPub URL
    type            VARCHAR(20) NOT NULL,         -- Person, Group, Application
    preferred_username VARCHAR(255) NOT NULL,
    name            VARCHAR(255) DEFAULT '',
    summary         TEXT,
    inbox_url       VARCHAR(512) NOT NULL,
    outbox_url      VARCHAR(512) DEFAULT '',
    shared_inbox_url VARCHAR(512) DEFAULT '',
    followers_url   VARCHAR(512) DEFAULT '',
    following_url   VARCHAR(512) DEFAULT '',
    public_key_pem  TEXT,
    private_key_pem TEXT,                         -- NULL for remote actors
    icon_url        VARCHAR(512) DEFAULT '',
    is_local        TINYINT(1) NOT NULL DEFAULT 0,
    local_user_id   INT DEFAULT NULL,             -- FK to smf_members
    local_board_id  INT DEFAULT NULL,             -- FK to smf_boards (for Group)
    last_fetched    INT UNSIGNED DEFAULT 0,       -- Unix timestamp
    created_at      INT UNSIGNED NOT NULL,
    updated_at      INT UNSIGNED NOT NULL,
    full_data       MEDIUMTEXT,                   -- Full actor JSON cache
    UNIQUE KEY idx_ap_id (ap_id(255)),
    KEY idx_local_user (local_user_id),
    KEY idx_local_board (local_board_id),
    KEY idx_preferred_username (preferred_username(100))
);
```

**Activities Table:**
```sql
CREATE TABLE {prefix}ap_activities (
    id_activity     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ap_id           VARCHAR(512) NOT NULL,
    type            VARCHAR(50) NOT NULL,         -- Create, Announce, Follow, Like, etc.
    actor_id        INT UNSIGNED NOT NULL,        -- FK to ap_actors
    object_ap_id    VARCHAR(512) DEFAULT '',      -- AP ID of the object
    object_type     VARCHAR(50) DEFAULT '',
    target_ap_id    VARCHAR(512) DEFAULT '',
    raw_data        MEDIUMTEXT,                   -- Full activity JSON
    status          VARCHAR(20) DEFAULT 'pending',-- pending, delivered, failed
    direction       VARCHAR(10) NOT NULL,         -- inbound, outbound
    created_at      INT UNSIGNED NOT NULL,
    processed_at    INT UNSIGNED DEFAULT 0,
    UNIQUE KEY idx_ap_id (ap_id(255)),
    KEY idx_actor (actor_id),
    KEY idx_status (status),
    KEY idx_direction (direction)
);
```

**Objects Table:**
```sql
CREATE TABLE {prefix}ap_objects (
    id_object       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ap_id           VARCHAR(512) NOT NULL,
    type            VARCHAR(50) NOT NULL,         -- Note, Article, Page, etc.
    attributed_to   INT UNSIGNED DEFAULT NULL,    -- FK to ap_actors
    in_reply_to     VARCHAR(512) DEFAULT '',
    context_url     VARCHAR(512) DEFAULT '',      -- Topic URL
    audience_url    VARCHAR(512) DEFAULT '',      -- Board URL
    content         MEDIUMTEXT,
    summary         VARCHAR(512) DEFAULT '',      -- Content warning
    url             VARCHAR(512) DEFAULT '',
    published       INT UNSIGNED DEFAULT 0,
    updated         INT UNSIGNED DEFAULT 0,
    is_local        TINYINT(1) NOT NULL DEFAULT 0,
    local_msg_id    INT DEFAULT NULL,             -- FK to smf_messages
    local_topic_id  INT DEFAULT NULL,             -- FK to smf_topics
    raw_data        MEDIUMTEXT,                   -- Full object JSON cache
    UNIQUE KEY idx_ap_id (ap_id(255)),
    KEY idx_local_msg (local_msg_id),
    KEY idx_local_topic (local_topic_id),
    KEY idx_context (context_url(255))
);
```

**Followers Table:**
```sql
CREATE TABLE {prefix}ap_followers (
    id_follow       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id        INT UNSIGNED NOT NULL,        -- The local actor being followed
    follower_id     INT UNSIGNED NOT NULL,        -- The remote actor following
    status          VARCHAR(20) DEFAULT 'pending',-- pending, accepted, rejected
    created_at      INT UNSIGNED NOT NULL,
    accepted_at     INT UNSIGNED DEFAULT 0,
    UNIQUE KEY idx_pair (actor_id, follower_id),
    KEY idx_actor (actor_id),
    KEY idx_follower (follower_id)
);
```

**Delivery Queue Table:**
```sql
CREATE TABLE {prefix}ap_delivery_queue (
    id_delivery     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity_id     INT UNSIGNED NOT NULL,        -- FK to ap_activities
    target_inbox    VARCHAR(512) NOT NULL,
    status          VARCHAR(20) DEFAULT 'queued', -- queued, delivered, failed, retrying
    attempts        TINYINT UNSIGNED DEFAULT 0,
    last_attempt    INT UNSIGNED DEFAULT 0,
    next_retry      INT UNSIGNED DEFAULT 0,
    error_message   VARCHAR(512) DEFAULT '',
    created_at      INT UNSIGNED NOT NULL,
    KEY idx_status (status),
    KEY idx_next_retry (next_retry)
);
```

### 12.2 Schema Notes

- Use `INT UNSIGNED` for timestamps (Unix epoch) to match SMF conventions
- Use `VARCHAR(512)` for URLs (AP IDs can be long)
- Cache full JSON as `MEDIUMTEXT` to avoid repeated remote fetches
- `is_local` flag distinguishes local content from federated content
- Foreign keys to SMF tables (`smf_members`, `smf_boards`, `smf_messages`, `smf_topics`) map AP entities to local entities

---

## 13. Common Gotchas and Pitfalls

### 13.1 Specification Gaps

- **Much behavior is undefined.** The AP spec is intentionally flexible, which means implementations diverge on edge cases.
- **No built-in authentication.** HTTP Signatures are a community convention, not part of the spec.
- **Private messaging is incompatible** across implementations (ChatMessage vs Note-without-public-audience).
- **Account migration** is not standardized; only basic follower migration exists.

### 13.2 Mastodon-Specific Gotchas

1. **Mastodon silently rejects Update activities** if the timestamp hasn't changed. Always update the `updated` field.
2. **Mastodon requires Mention tags** for notifications. Including an actor in `to`/`cc` is not enough.
3. **Article objects** are displayed as links, not full content. Use Note for maximum compatibility.
4. **Mastodon strips most HTML.** Test your BBCode-to-HTML conversion against Mastodon's sanitizer.
5. **Mastodon validates Date headers** within a 12-hour window. Server clock sync is critical.
6. **The Digest header** must be included in HTTP Signatures for POST requests.
7. **Mastodon's HTTP Signature spec** is a draft, not the final RFC. Use draft-12 behavior.

### 13.3 General Implementation Pitfalls

1. **JSON-LD complexity:** Treat AP as JSON with a `@context` field. Full JSON-LD processing is unnecessary and rarely done.
2. **Infinite loops:** AP allows liking a Like, creating circular references. Implement depth limits.
3. **Missing replies:** Remote instances may not have all replies. Implement backfill strategies.
4. **Stale statistics:** Federated like/boost counts become outdated. Consider periodic refresh.
5. **DoS amplification:** Poorly optimized delivery can overwhelm remote servers. Use shared inboxes, rate limiting, and exponential backoff.
6. **Delete propagation:** Delete requests may be ignored by remote servers. Don't rely on deletion across the federation.
7. **Content spoofing:** Always validate that the `attributedTo` actor matches the HTTP Signature actor.
8. **Replay attacks:** Validate timestamps and keep a short cache of processed activity IDs to prevent replays.
9. **Large payloads:** ActivityPub has no size limits. Implement maximum payload size checks.
10. **URL validation:** Verify that all URLs use HTTPS (except localhost in development).

### 13.4 PHP-Specific Issues

1. **OpenSSL availability:** Verify `openssl_*` functions are available (they usually are, but can be disabled).
2. **Key storage:** Never store private keys in plaintext. Encrypt at rest.
3. **Timezone handling:** Always use UTC for all AP timestamps.
4. **Character encoding:** AP uses UTF-8. Ensure database columns use `utf8mb4` charset.
5. **cURL timeout:** Set reasonable timeouts (30 seconds) for outbound requests.
6. **Background processing:** PHP is request-driven; delivery should be queued and processed by cron/scheduled tasks, not inline.

---

## 14. Architectural Recommendations for SMF

### 14.1 Actor Strategy

Based on all research, the recommended actor model for SMF is:

| SMF Entity | AP Actor Type | Handle Format | Notes |
|-----------|--------------|---------------|-------|
| Member | Person | `@username@domain` | Optional opt-in per user |
| Board | Group | `@board-slug@domain` | Admin-enabled per board |
| Forum | Application | `@domain@domain` | Single site-wide actor |

### 14.2 Content Mapping

| SMF Entity | AP Object | Properties |
|-----------|-----------|-----------|
| Topic (first post) | Note or Article | context=topic-url, audience=board-url |
| Reply | Note | context=topic-url, audience=board-url, inReplyTo=parent-post-url |
| Topic (collection) | OrderedCollection | Contains all posts in topic |
| Board (outbox) | OrderedCollection | Contains recent topics/activities |

### 14.3 Federation Modes (per board)

1. **Off** - No federation (default)
2. **First Post Only** - Only topic starters federate (simpler, announcement-style)
3. **Full Topic** - All posts federate (full discussion, bidirectional)

### 14.4 Implementation Priority

**Phase 1: Outbound Read-Only**
1. WebFinger endpoint
2. Actor endpoints for boards (Group) and users (Person)
3. Outbox for boards (recent topics)
4. HTTP Signature signing for outgoing requests
5. Topic-to-Note transformer (BBCode -> HTML)
6. Follower management (Accept/Reject follows)
7. Deliver Create activities to followers when new topics are posted
8. Board Announce relay pattern

**Phase 2: Inbound Processing**
1. Inbox endpoint with HTTP Signature verification
2. Process Follow/Unfollow activities
3. Process Create activities (remote replies to local topics)
4. Process Like activities (optional, map to SMF likes)
5. Process Announce activities (boosts)
6. Process Delete/Undo activities
7. Rate limiting and domain allow/block lists

**Phase 3: Full Bidirectional**
1. Full Topic mode (all replies federated)
2. User opt-in for Person actors
3. User-to-user following
4. Remote topic display in SMF
5. Conversational backfill
6. Admin dashboard for federation management

### 14.5 No External Dependencies Approach

To keep the mod lightweight and compatible with standard SMF hosting:

```
/Sources/ActivityPub/
    ActivityPub.php          - Main integration hooks
    Actor.php                - Actor management (create, fetch, serialize)
    Activity.php             - Activity creation and processing
    Object.php               - AP Object types (Note, Article, etc.)
    HttpSignature.php        - HTTP Signature signing and verification
    WebFinger.php            - WebFinger endpoint handler
    Inbox.php                - Inbox processing pipeline
    Outbox.php               - Outbox/delivery management
    Transformer.php          - BBCode <-> HTML <-> AP Object conversion
    DeliveryQueue.php        - Background delivery with retry logic
    JsonLD.php               - Minimal JSON-LD context handling
    FederationAdmin.php      - Admin panel integration
```

All cryptographic operations use PHP's built-in `openssl_*` functions.
All HTTP operations use `curl_*` functions (already available in SMF environments).
All database operations use SMF's `$smcFunc['db_query']` abstraction.

### 14.6 Shared Inbox Optimization

When delivering to followers, deduplicate by shared inbox:

```php
// Instead of sending to each follower's inbox individually:
// POST https://mastodon.social/users/alice/inbox
// POST https://mastodon.social/users/bob/inbox
// POST https://mastodon.social/users/charlie/inbox

// Send once to the shared inbox:
// POST https://mastodon.social/inbox
```

This is critical for performance when a board has many followers from the same instance.

### 14.7 Testing Strategy

1. **ActivityPub.Academy** - Lemmy instance for testing federation
2. **Mastodon test instance** - Set up a local Mastodon for testing
3. **activitypub-testing** tools from the W3C
4. Use Firefox/Chrome to inspect raw JSON-LD responses
5. Test WebFinger with: `curl https://forum.example/.well-known/webfinger?resource=acct:test@forum.example`

---

## Sources

### PHP Libraries
- [ActivityPhp (landrok/activitypub)](https://github.com/landrok/activitypub)
- [ActivityPub-PHP (pterotype/activitypub-php)](https://github.com/pterotype-project/activitypub-php)
- [RikudouSage/ActivityPub](https://github.com/RikudouSage/ActivityPub)
- [dansup/php-activitypub](https://packagist.org/packages/dansup/php-activitypub)
- [liamdennehy/http-signatures-php](https://packagist.org/packages/liamdennehy/http-signatures-php)

### WordPress ActivityPub
- [WordPress ActivityPub Plugin](https://wordpress.org/plugins/activitypub/)
- [GitHub: Automattic/wordpress-activitypub](https://github.com/Automattic/wordpress-activitypub)
- [Database Schema Proposal (Issue #2130)](https://github.com/Automattic/wordpress-activitypub/issues/2130)
- [Actor Management Discussion](https://github.com/Automattic/wordpress-activitypub/discussions/547)
- [ActivityPub for WordPress Blog](https://activitypub.blog/)

### Discourse ActivityPub
- [GitHub: discourse/discourse-activity-pub](https://github.com/discourse/discourse-activity-pub)
- [DeepWiki: Core Concepts](https://deepwiki.com/discourse/discourse-activity-pub/3-core-concepts)
- [SocialHub: Adding Federation to Discourse](https://socialhub.activitypub.rocks/t/adding-federation-support-to-discourse/2966)

### NodeBB ActivityPub
- [NodeBB ActivityPub Documentation](https://docs.nodebb.org/activitypub/)
- [NodeBB FEP-1b12 Implementation](https://docs.nodebb.org/activitypub/fep/1b12/)
- [NodeBB v4.0.0 Release](https://community.nodebb.org/topic/18545/nodebb-v4.0.0-federate-good-times-come-on/86)

### Lemmy
- [Understanding ActivityPub Part 2: Lemmy](https://seb.jambor.dev/posts/understanding-activitypub-part-2-lemmy/)
- [Lemmy Federation Overview](https://join-lemmy.org/docs/contributors/05-federation.html)

### Mastodon Documentation
- [Mastodon ActivityPub Spec](https://docs.joinmastodon.org/spec/activitypub/)
- [Mastodon Security Spec](https://docs.joinmastodon.org/spec/security/)
- [Mastodon WebFinger Spec](https://docs.joinmastodon.org/spec/webfinger/)
- [How to Implement a Basic ActivityPub Server](https://blog.joinmastodon.org/2018/06/how-to-implement-a-basic-activitypub-server/)

### HTTP Signatures
- [HTTP Signatures in PHP (Imran Nazar)](https://imrannazar.com/articles/http-signatures-in-php)
- [ActivityPub Event Handling in PHP/MySQL (Imran Nazar)](https://imrannazar.com/articles/activitypub-events)
- [SWICG: ActivityPub HTTP Signatures](https://swicg.github.io/activitypub-http-signature/)

### FEPs and Standards
- [FEP-1b12: Group Federation](https://codeberg.org/fediverse/fep/src/branch/main/fep/1b12/fep-1b12.md)
- [Conversational Contexts in ActivityPub](https://swicg.github.io/forums/)
- [SocialHub: Guide for New Implementers](https://socialhub.activitypub.rocks/t/guide-for-new-activitypub-implementers/479)
- [Fediverse Enhancement Proposals](https://codeberg.org/fediverse/fep)

### Gotchas and Challenges
- [ActivityPub: The Good, Bad, and Ugly](https://chrastecky.dev/technology/activity-pub-the-good-the-bad-and-the-ugly)
- [Delightful Fediverse Development](https://codeberg.org/fediverse/delightful-activitypub-development)
