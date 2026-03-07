# ActivityPub Protocol Research for SMF Integration

> Comprehensive technical reference compiled from W3C specifications, Mastodon implementation documentation, and community resources. This document serves as the architectural foundation for building ActivityPub federation into SMF (Simple Machines Forum).

---

## Table of Contents

1. [Protocol Overview](#1-protocol-overview)
2. [Actor Model](#2-actor-model)
3. [HTTP Endpoints](#3-http-endpoints)
4. [WebFinger Discovery](#4-webfinger-discovery)
5. [HTTP Signatures Authentication](#5-http-signatures-authentication)
6. [Content Types and JSON-LD](#6-content-types-and-json-ld)
7. [ActivityStreams Vocabulary](#7-activitystreams-vocabulary)
8. [Server-to-Server Federation Protocol](#8-server-to-server-federation-protocol)
9. [Client-to-Server Protocol](#9-client-to-server-protocol)
10. [Collections and Pagination](#10-collections-and-pagination)
11. [Object Identifiers and Addressing](#11-object-identifiers-and-addressing)
12. [Delivery Mechanisms](#12-delivery-mechanisms)
13. [Inbox Forwarding](#13-inbox-forwarding)
14. [Mastodon-Specific Extensions](#14-mastodon-specific-extensions)
15. [Security Considerations](#15-security-considerations)
16. [Minimum Viable Federation Requirements](#16-minimum-viable-federation-requirements)
17. [SMF Integration Architecture Notes](#17-smf-integration-architecture-notes)

---

## 1. Protocol Overview

ActivityPub is a W3C Recommendation (January 2018) that defines two protocols:

- **Server-to-Server (Federation):** Servers communicate by POSTing activities to actors' inbox endpoints. This enables decentralized social networking across different server implementations.
- **Client-to-Server:** Clients interact with servers by POSTing activities to the actor's outbox and reading from the inbox. (Less commonly implemented; most implementations focus on S2S federation.)

The protocol is built on top of:
- **ActivityStreams 2.0** (W3C Recommendation) - the vocabulary and data model
- **JSON-LD** - the serialization format
- **HTTP Signatures** (draft-cavage-http-signatures) - authentication between servers
- **WebFinger** (RFC 7033) - actor discovery

### Key Standards References

| Standard | URL | Purpose |
|----------|-----|---------|
| ActivityPub | https://www.w3.org/TR/activitypub/ | Core federation protocol |
| ActivityStreams 2.0 Core | https://www.w3.org/TR/activitystreams-core/ | Data model and vocabulary |
| ActivityStreams 2.0 Vocabulary | https://www.w3.org/TR/activitystreams-vocabulary/ | Activity and object types |
| WebFinger | RFC 7033 | Actor discovery |
| HTTP Signatures | draft-cavage-http-signatures | Server authentication |
| JSON-LD | https://www.w3.org/TR/json-ld/ | Serialization format |

---

## 2. Actor Model

### What is an Actor?

An actor is an entity that can perform activities. In the context of an SMF forum, actors would represent user profiles (and potentially the forum itself as a Service actor).

### Actor Types (ActivityStreams 2.0)

| Type | Description | SMF Use Case |
|------|-------------|--------------|
| `Person` | Individual human | Forum member profiles |
| `Service` | Automated system | The forum itself (for bot-like announcements) |
| `Application` | Software or service | The SMF instance |
| `Group` | Collection of entities | Forum boards/categories |
| `Organization` | Business/institutional entity | Could represent the forum organization |

### REQUIRED Actor Properties

Per Section 4.1 of the ActivityPub spec, all actors **MUST** have:

```json
{
  "@context": [
    "https://www.w3.org/ns/activitystreams",
    "https://w3id.org/security/v1"
  ],
  "id": "https://forum.example.com/activitypub/actor/42",
  "type": "Person",
  "inbox": "https://forum.example.com/activitypub/actor/42/inbox",
  "outbox": "https://forum.example.com/activitypub/actor/42/outbox"
}
```

- **`id`** - Globally unique HTTPS URI identifying this actor. MUST be dereferenceable (GETting it returns the actor document).
- **`type`** - One of the ActivityStreams actor types (Person, Service, etc.)
- **`inbox`** - URL to the actor's inbox (OrderedCollection). Accepts POST for federation, GET for reading received activities.
- **`outbox`** - URL to the actor's outbox (OrderedCollection). Accepts POST for client-to-server, GET for reading published activities.

### STRONGLY RECOMMENDED Actor Properties

```json
{
  "following": "https://forum.example.com/activitypub/actor/42/following",
  "followers": "https://forum.example.com/activitypub/actor/42/followers",
  "preferredUsername": "johndoe",
  "name": "John Doe",
  "summary": "<p>Forum member since 2020</p>",
  "url": "https://forum.example.com/index.php?action=profile;u=42",
  "icon": {
    "type": "Image",
    "mediaType": "image/png",
    "url": "https://forum.example.com/avatars/42.png"
  }
}
```

- **`following`** - Collection of actors this actor follows
- **`followers`** - Collection of actors following this actor (default delivery target)
- **`preferredUsername`** - Short username (used in `acct:` URI for WebFinger)
- **`name`** - Display name
- **`summary`** - Bio/description (HTML allowed)
- **`url`** - Link to human-readable profile page (if different from `id`)
- **`icon`** - Profile picture (Image object or URL)

### OPTIONAL Actor Properties

```json
{
  "liked": "https://forum.example.com/activitypub/actor/42/liked",
  "streams": [],
  "image": {
    "type": "Image",
    "url": "https://forum.example.com/headers/42.png"
  },
  "endpoints": {
    "sharedInbox": "https://forum.example.com/activitypub/inbox"
  },
  "publicKey": {
    "id": "https://forum.example.com/activitypub/actor/42#main-key",
    "owner": "https://forum.example.com/activitypub/actor/42",
    "publicKeyPem": "-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----\n"
  }
}
```

- **`liked`** - Collection of objects this actor has liked
- **`streams`** - Supplementary collections
- **`image`** - Header/banner image
- **`endpoints`** - Server-wide endpoints, including:
  - `sharedInbox` - Single inbox for efficient bulk delivery
  - `oauthAuthorizationEndpoint` - OAuth 2.0 authorization
  - `oauthTokenEndpoint` - OAuth 2.0 token endpoint
  - `proxyUrl` - Proxy for fetching remote resources
- **`publicKey`** - RSA public key for HTTP Signature verification (de facto required for federation)

### Complete Actor Example (SMF Context)

```json
{
  "@context": [
    "https://www.w3.org/ns/activitystreams",
    "https://w3id.org/security/v1",
    {
      "manuallyApprovesFollowers": "as:manuallyApprovesFollowers",
      "sensitive": "as:sensitive",
      "PropertyValue": "schema:PropertyValue",
      "value": "schema:value"
    }
  ],
  "id": "https://forum.example.com/activitypub/actor/42",
  "type": "Person",
  "preferredUsername": "johndoe",
  "name": "John Doe",
  "summary": "<p>Long-time forum member. I post about technology and gaming.</p>",
  "url": "https://forum.example.com/index.php?action=profile;u=42",
  "inbox": "https://forum.example.com/activitypub/actor/42/inbox",
  "outbox": "https://forum.example.com/activitypub/actor/42/outbox",
  "followers": "https://forum.example.com/activitypub/actor/42/followers",
  "following": "https://forum.example.com/activitypub/actor/42/following",
  "liked": "https://forum.example.com/activitypub/actor/42/liked",
  "manuallyApprovesFollowers": false,
  "publicKey": {
    "id": "https://forum.example.com/activitypub/actor/42#main-key",
    "owner": "https://forum.example.com/activitypub/actor/42",
    "publicKeyPem": "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A...\n-----END PUBLIC KEY-----\n"
  },
  "icon": {
    "type": "Image",
    "mediaType": "image/jpeg",
    "url": "https://forum.example.com/avatars/42.jpg"
  },
  "endpoints": {
    "sharedInbox": "https://forum.example.com/activitypub/inbox"
  },
  "attachment": [
    {
      "type": "PropertyValue",
      "name": "Forum Role",
      "value": "Global Moderator"
    }
  ]
}
```

---

## 3. HTTP Endpoints

### Summary of Required Endpoints

| Endpoint | Methods | Purpose |
|----------|---------|---------|
| `/.well-known/webfinger` | GET | Actor discovery |
| `/activitypub/actor/{id}` | GET | Actor profile (JSON-LD) |
| `/activitypub/actor/{id}/inbox` | GET, POST | Receive activities (POST for federation) |
| `/activitypub/actor/{id}/outbox` | GET, POST | Published activities (POST for C2S) |
| `/activitypub/actor/{id}/followers` | GET | Followers collection |
| `/activitypub/actor/{id}/following` | GET | Following collection |
| `/activitypub/actor/{id}/liked` | GET | Liked objects collection |
| `/activitypub/inbox` | POST | Shared inbox for bulk delivery |
| `/activitypub/object/{type}/{id}` | GET | Individual objects (posts, etc.) |
| `/activitypub/activity/{id}` | GET | Individual activities |

### Actor Endpoint (GET)

**Request:**
```http
GET /activitypub/actor/42 HTTP/1.1
Host: forum.example.com
Accept: application/activity+json
```

**Response:**
```http
HTTP/1.1 200 OK
Content-Type: application/activity+json

{
  "@context": [...],
  "id": "https://forum.example.com/activitypub/actor/42",
  "type": "Person",
  ...
}
```

**Requirements:**
- MUST return the actor document when GETted with appropriate Accept header
- MUST present ActivityStreams representation for `Accept: application/ld+json; profile="https://www.w3.org/ns/activitystreams"`
- SHOULD also present for `Accept: application/activity+json`
- MAY require authorization; SHOULD return 403 Forbidden for unauthorized, MAY return 404 to hide existence
- SHOULD use HTTP content negotiation (RFC 7231)

### Inbox Endpoint

#### GET (Read inbox)
```http
GET /activitypub/actor/42/inbox HTTP/1.1
Host: forum.example.com
Accept: application/activity+json
Authorization: Bearer <token>
```

- Returns OrderedCollection of received activities
- Server SHOULD filter based on requester's permissions
- Owner sees all; others see based on authentication/authorization

#### POST (Receive federated activity)
```http
POST /activitypub/actor/42/inbox HTTP/1.1
Host: forum.example.com
Content-Type: application/ld+json; profile="https://www.w3.org/ns/activitystreams"
Date: Sun, 01 Mar 2026 12:00:00 GMT
Digest: sha-256=abc123...
Signature: keyId="https://remote.example/actor#main-key",headers="(request-target) host date digest",signature="base64..."

{
  "@context": "https://www.w3.org/ns/activitystreams",
  "id": "https://remote.example/activities/123",
  "type": "Create",
  "actor": "https://remote.example/actor",
  "object": { ... }
}
```

**Requirements:**
- MUST accept POST for federation (federated servers)
- Non-federated servers SHOULD return 405 Method Not Allowed
- MUST de-duplicate activities by `id` (drop already-seen activities)
- Server MUST verify the HTTP Signature
- Server SHOULD verify the activity is authentic (e.g., dereference the `id` to confirm it exists at origin)

### Outbox Endpoint

#### GET (Read published activities)
```http
GET /activitypub/actor/42/outbox HTTP/1.1
Host: forum.example.com
Accept: application/activity+json
```

- Returns OrderedCollection of published activities
- Without authorization: return only Public posts
- With authorization: return based on permissions

#### POST (Client-to-Server submission)
```http
POST /activitypub/actor/42/outbox HTTP/1.1
Host: forum.example.com
Content-Type: application/ld+json; profile="https://www.w3.org/ns/activitystreams"
Authorization: Bearer <token>

{
  "@context": "https://www.w3.org/ns/activitystreams",
  "type": "Create",
  "object": { ... }
}
```

**Requirements:**
- MUST be authenticated
- MUST return 201 Created on success
- MUST include new `id` in Location header (unless transient)
- If an Activity is submitted with an `id`, server MUST ignore it and generate a new one
- MUST remove `bto`/`bcc` before delivery
- MUST add activity to outbox collection

### Object Endpoints (GET)

```http
GET /activitypub/object/note/12345 HTTP/1.1
Host: forum.example.com
Accept: application/activity+json
```

- Returns the JSON-LD representation of the object
- Objects MUST be dereferenceable by their `id`
- MAY require authorization
- Deleted objects: return 410 Gone (with Tombstone) or 404 Not Found

### Shared Inbox (POST)

```http
POST /activitypub/inbox HTTP/1.1
Host: forum.example.com
Content-Type: application/ld+json; profile="https://www.w3.org/ns/activitystreams"
```

- Single endpoint receiving activities for ALL actors on the server
- Efficiency optimization: remote servers send one copy instead of N copies for N followers
- Referenced in actor's `endpoints.sharedInbox`
- Same verification and processing requirements as individual inboxes

---

## 4. WebFinger Discovery

### Overview

WebFinger (RFC 7033) is used to discover an actor's ActivityPub profile URL from their username. When someone searches for `@johndoe@forum.example.com`, the remote server queries WebFinger to find the actor's profile.

### Request Format

```http
GET /.well-known/webfinger?resource=acct:johndoe@forum.example.com HTTP/1.1
Host: forum.example.com
Accept: application/jrd+json
```

**Query Parameters:**
- `resource` (REQUIRED): The URI being queried. For ActivityPub, this is `acct:username@domain`
- `rel` (OPTIONAL, repeatable): Filter response to specific link relation types

### Response Format

```http
HTTP/1.1 200 OK
Content-Type: application/jrd+json
Access-Control-Allow-Origin: *

{
  "subject": "acct:johndoe@forum.example.com",
  "aliases": [
    "https://forum.example.com/index.php?action=profile;u=42",
    "https://forum.example.com/activitypub/actor/42"
  ],
  "links": [
    {
      "rel": "self",
      "type": "application/activity+json",
      "href": "https://forum.example.com/activitypub/actor/42"
    },
    {
      "rel": "http://webfinger.net/rel/profile-page",
      "type": "text/html",
      "href": "https://forum.example.com/index.php?action=profile;u=42"
    }
  ]
}
```

### Critical Requirements

1. **HTTPS is mandatory** - WebFinger queries MUST use HTTPS
2. **CORS headers required** - Response MUST include `Access-Control-Allow-Origin: *`
3. **Content-Type** - Response MUST be `application/jrd+json`
4. **The `self` link is essential** - MUST have `rel: "self"` with `type: "application/activity+json"` pointing to the actor's ActivityPub profile URI

### Response Fields

| Field | Required | Description |
|-------|----------|-------------|
| `subject` | Yes | Canonical `acct:` URI for the actor |
| `aliases` | No | Alternative URIs (profile page, actor URI) |
| `links` | Yes | Array of link objects |
| `properties` | No | Key-value metadata |

### Link Object Fields

| Field | Required | Description |
|-------|----------|-------------|
| `rel` | Yes | Relationship type (e.g., `self`, `http://webfinger.net/rel/profile-page`) |
| `href` | No | Target URI |
| `type` | No | Media type of target resource |
| `titles` | No | Multilingual descriptions |
| `properties` | No | Additional metadata |

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success, JRD returned |
| 400 | Malformed resource parameter |
| 404 | Resource not found |

### Discovery Flow

1. User searches for `@johndoe@forum.example.com`
2. Remote server extracts domain `forum.example.com`
3. Remote server queries `https://forum.example.com/.well-known/webfinger?resource=acct:johndoe@forum.example.com`
4. Response includes `self` link pointing to `https://forum.example.com/activitypub/actor/42`
5. Remote server GETs that URL with `Accept: application/activity+json`
6. Receives the full actor document with inbox, outbox, keys, etc.

### Validation (Mastodon-specific)

Mastodon validates:
- Response contains a `subject` field
- At least one link has `rel="self"` with ActivityPub content type
- The `href` resolves to a valid ActivityPub actor
- The actor's `preferredUsername` and domain reconstruct the original `acct:` URI

---

## 5. HTTP Signatures Authentication

### Overview

HTTP Signatures provide cryptographic proof that a request was sent by the claimed actor. This is the de facto standard for server-to-server authentication in the fediverse, even though the ActivityPub spec itself does not mandate a specific authentication mechanism.

### Key Generation

Each actor needs an RSA key pair:
- **Algorithm:** RSA with minimum 2048-bit key
- **Format:** PEM-encoded
- **Storage:** Private key stored securely on server; public key published in actor document

### Publishing the Public Key

The public key is embedded in the actor document:

```json
{
  "publicKey": {
    "id": "https://forum.example.com/activitypub/actor/42#main-key",
    "owner": "https://forum.example.com/activitypub/actor/42",
    "publicKeyPem": "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvXc4vkECU2/CeuSo1wtn\nFoim94Ne1jBMYxTZ9wm2YTdJq1oiZKif06I2fOqDzY/4q/S9uccrE9Bkajv1dnkO\nVm31QjWlhVpSKynVxEWjVBO5Ienue8gND0xvHIuXf87o61poqjEoepvsQFElA5ym\novjljWGSA/jpj7ozygUZhCXtaS2W5AD5tnBQUpcO0lhItYPYTjnmzcc4y2NbJV8hz\n2s2G8qKv8fyimE23gY1XrPJg+cRF+g4PqFXujjlJ7MihD9oqtLGxbu7o1cifTn3x\nBfIdPythWu5b4cujNsB3m3awJjVmx+MHQ9SugkSIYXV0Ina77cTNS0M2PYiH1PFR\nTwIDAQAB\n-----END PUBLIC KEY-----\n"
  }
}
```

**Key Properties:**
- `id` - URI identifying the key (typically `actorId#main-key`)
- `owner` - URI of the actor who owns this key (MUST match the actor's `id`)
- `publicKeyPem` - PEM-encoded public key string

### Signing Outgoing Requests

#### For GET Requests (fetching remote resources)

**Step 1: Construct the request headers**
```http
GET /users/username/outbox HTTP/1.1
Host: mastodon.example
Date: Sun, 01 Mar 2026 12:00:00 GMT
Accept: application/activity+json
```

**Step 2: Build the signing string**

The signing string concatenates specified headers in order, joined by newlines:
```
(request-target): get /users/username/outbox
host: mastodon.example
date: Sun, 01 Mar 2026 12:00:00 GMT
```

**Step 3: Sign with RSA-SHA256**
1. Hash the signing string with SHA-256
2. Sign the hash with the actor's private key (RSASSA-PKCS1-v1_5)
3. Base64-encode the result

**Step 4: Add Signature header**
```http
Signature: keyId="https://forum.example.com/activitypub/actor/42#main-key",algorithm="rsa-sha256",headers="(request-target) host date",signature="base64EncodedSignature..."
```

#### For POST Requests (delivering activities)

POST requests additionally require a `Digest` header:

**Step 1: Calculate body digest**
```
Digest: SHA-256=base64(sha256(requestBody))
```

**Step 2: Construct headers**
```http
POST /users/username/inbox HTTP/1.1
Host: mastodon.example
Date: Sun, 01 Mar 2026 12:00:00 GMT
Digest: SHA-256=hcK0GZB1BM4R0eenYrj9clYBuyXs/lemt5iWRYmIX0A=
Content-Type: application/ld+json; profile="https://www.w3.org/ns/activitystreams"
```

**Step 3: Build signing string**
```
(request-target): post /users/username/inbox
host: mastodon.example
date: Sun, 01 Mar 2026 12:00:00 GMT
digest: SHA-256=hcK0GZB1BM4R0eenYrj9clYBuyXs/lemt5iWRYmIX0A=
```

**Step 4: Sign and add Signature header**
```http
Signature: keyId="https://forum.example.com/activitypub/actor/42#main-key",algorithm="rsa-sha256",headers="(request-target) host date digest",signature="base64EncodedSignature..."
```

### Signature Header Format

```
Signature: keyId="<actor-key-uri>",algorithm="rsa-sha256",headers="<space-separated-header-names>",signature="<base64-encoded-signature>"
```

| Parameter | Description |
|-----------|-------------|
| `keyId` | URI of the public key (from actor's `publicKey.id`) |
| `algorithm` | Always `rsa-sha256` for current implementations |
| `headers` | Space-separated list of headers included in signature |
| `signature` | Base64-encoded RSA-SHA256 signature |

### Verifying Incoming Signatures

**Verification Algorithm:**

1. Parse the `Signature` header to extract `keyId`, `headers`, and `signature`
2. Reconstruct the signing string using the headers listed in the `headers` parameter
3. Fetch the `keyId` URI to retrieve the actor's public key
4. Base64-decode the `signature` value
5. Verify the decoded signature against the reconstructed signing string using RSA-SHA256 with the fetched public key
6. For POST requests: verify the `Digest` header matches SHA-256 of the request body
7. Verify the `Date` header is within acceptable clock skew (typically 12 hours)

### Clock Skew Tolerance

- The `Date` header MUST be within the past 12 hours (Mastodon's tolerance)
- Some implementations may use tighter windows
- Recommended: accept requests with Date within +/- 30 seconds to 12 hours

### Headers to Sign

| Request Type | Recommended Headers |
|-------------|-------------------|
| GET | `(request-target) host date` |
| POST | `(request-target) host date digest content-type` |

### RFC 9421 (HTTP Message Signatures) - Newer Standard

Starting with Mastodon 4.5.0, the newer RFC 9421 standard is supported:

```http
Signature-Input: sig1=("@method" "@target-uri" "content-digest");created=1748341414;keyid="https://my.example.com/actor#main-key"
Signature: sig1=:base64signature:
```

Key differences:
- Two headers instead of one (`Signature-Input` and `Signature`)
- `created` parameter is mandatory (Unix timestamp)
- Uses `@method` and `@target-uri` derived components
- Uses `Content-Digest` (RFC 9530) instead of `Digest`

**For initial implementation, use draft-cavage-http-signatures as it has broader compatibility across the fediverse.**

---

## 6. Content Types and JSON-LD

### Required Content Types

| Content Type | Usage |
|-------------|-------|
| `application/ld+json; profile="https://www.w3.org/ns/activitystreams"` | Normative requirement for POST and Accept headers |
| `application/activity+json` | Servers SHOULD interpret as equivalent (widely used) |
| `application/jrd+json` | WebFinger responses |

### When to Use Which

- **Sending POST to inbox:** `Content-Type: application/ld+json; profile="https://www.w3.org/ns/activitystreams"`
- **Requesting actor/object:** `Accept: application/activity+json` (simpler, widely accepted)
- **Responding to requests:** `Content-Type: application/activity+json`
- **WebFinger:** `Content-Type: application/jrd+json`

### JSON-LD Context

All ActivityPub documents MUST include a `@context` property:

```json
{
  "@context": [
    "https://www.w3.org/ns/activitystreams",
    "https://w3id.org/security/v1"
  ]
}
```

**Multiple contexts** (array form) for extensions:

```json
{
  "@context": [
    "https://www.w3.org/ns/activitystreams",
    "https://w3id.org/security/v1",
    {
      "manuallyApprovesFollowers": "as:manuallyApprovesFollowers",
      "sensitive": "as:sensitive",
      "Hashtag": "as:Hashtag",
      "PropertyValue": "schema:PropertyValue",
      "value": "schema:value",
      "schema": "http://schema.org#"
    }
  ]
}
```

### JSON-LD Processing Rules

- Documents using `application/activity+json` without explicit `@context` referencing the normative definition: implementations MUST assume the normative ActivityStreams context applies
- Implementations MAY augment `@context` but MUST NOT override normative definitions
- JSON-LD algorithms silently ignore properties absent from `@context`
- UTF-8 encoding is mandatory
- Absent properties may be represented by `null` or omission (semantically equivalent)

### Compact URIs in @context

Prefixes expand via context definitions:
- `as:sensitive` expands to `https://www.w3.org/ns/activitystreams#sensitive`
- `schema:PropertyValue` expands to `http://schema.org#PropertyValue`

---

## 7. ActivityStreams Vocabulary

### Object Base Type

All ActivityStreams entities inherit from Object:

```json
{
  "id": "https://example.com/objects/123",
  "type": "Note",
  "name": "Title",
  "content": "<p>HTML content</p>",
  "summary": "Brief description or content warning",
  "published": "2026-03-01T12:00:00Z",
  "updated": "2026-03-01T13:00:00Z",
  "attributedTo": "https://example.com/actor/42",
  "inReplyTo": "https://remote.example/objects/456",
  "url": "https://example.com/posts/123",
  "mediaType": "text/html",
  "to": ["https://www.w3.org/ns/activitystreams#Public"],
  "cc": ["https://example.com/actor/42/followers"],
  "tag": [],
  "attachment": [],
  "replies": { "type": "Collection", "totalItems": 0 }
}
```

### Object Properties Reference

| Property | Type | Description |
|----------|------|-------------|
| `id` | IRI | Globally unique identifier (HTTPS URI) |
| `type` | string/array | Object type classification |
| `name` / `nameMap` | string / language map | Human-readable title |
| `content` / `contentMap` | string / language map | Full body content (may include HTML) |
| `summary` / `summaryMap` | string / language map | Brief description; used as content warning in Mastodon |
| `published` | RFC 3339 datetime | Creation timestamp |
| `updated` | RFC 3339 datetime | Last modification timestamp |
| `attributedTo` | IRI/object/array | Creator or source |
| `inReplyTo` | IRI/object | Parent object being replied to |
| `url` | IRI/Link/array | Web-accessible URL |
| `mediaType` | MIME string | Content format |
| `to` | IRI/object/array | Primary recipients |
| `cc` | IRI/object/array | Secondary recipients |
| `bcc` | IRI/object/array | Hidden recipients (removed before delivery) |
| `bto` | IRI/object/array | Blind hidden recipients (removed before delivery) |
| `audience` | IRI/object/array | Intended audience group |
| `tag` | IRI/object/array | Tags, mentions, hashtags |
| `attachment` | IRI/object/array | Attached media |
| `image` | Link/IRI/array | Visual representation |
| `icon` | Link/IRI/array | Small icon/avatar |
| `replies` | Collection | Response thread collection |
| `likes` | Collection | Like activities collection |
| `shares` | Collection | Announce activities collection |
| `context` | IRI/object | Related conceptual scope |
| `generator` | IRI/object | Creating application |
| `location` | Place/object | Physical/geographic location |
| `startTime` | RFC 3339 | Event start |
| `endTime` | RFC 3339 | Event end |
| `duration` | ISO 8601 | Temporal length |
| `sensitive` | boolean | Content sensitivity flag (Mastodon extension) |

### Object Types Relevant to SMF

| Type | Description | SMF Mapping |
|------|-------------|-------------|
| `Note` | Short text content | Forum posts/replies |
| `Article` | Long-form content | Topic opening posts |
| `Page` | Web page | Could represent topics |
| `Image` | Image resource | Attachments |
| `Video` | Video resource | Attachments |
| `Document` | Generic document | Attachments |
| `Question` | Poll | Forum polls |
| `Tombstone` | Deleted object placeholder | Deleted posts |

### Activity Base Type

Activities represent actions performed by actors:

```json
{
  "@context": "https://www.w3.org/ns/activitystreams",
  "id": "https://example.com/activities/789",
  "type": "Create",
  "actor": "https://example.com/actor/42",
  "object": { ... },
  "to": ["https://www.w3.org/ns/activitystreams#Public"],
  "cc": ["https://example.com/actor/42/followers"],
  "published": "2026-03-01T12:00:00Z"
}
```

### Activity Properties

| Property | Type | Description |
|----------|------|-------------|
| `actor` | IRI/object/array | Entity performing the action (REQUIRED) |
| `object` | IRI/object/array | Target/subject of the action |
| `target` | IRI/object | Destination container |
| `origin` | IRI/object | Source location |
| `result` | IRI/object | Outcome/product |
| `instrument` | IRI/object | Tool used |

### Activity Types - Complete Reference

#### Create

Creates a new object.

```json
{
  "type": "Create",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "object": {
    "type": "Note",
    "id": "https://forum.example.com/activitypub/object/note/123",
    "attributedTo": "https://forum.example.com/activitypub/actor/42",
    "content": "<p>This is a new forum post!</p>",
    "to": ["https://www.w3.org/ns/activitystreams#Public"],
    "cc": ["https://forum.example.com/activitypub/actor/42/followers"]
  },
  "to": ["https://www.w3.org/ns/activitystreams#Public"],
  "cc": ["https://forum.example.com/activitypub/actor/42/followers"]
}
```

**Side effects:**
- C2S: Actor copied to object's `attributedTo`; addressing copied between activity and object
- S2S: Minimal side effects; activity stored in recipient's inbox; server likely stores object locally

#### Update

Updates an existing object.

```json
{
  "type": "Update",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "object": {
    "type": "Note",
    "id": "https://forum.example.com/activitypub/object/note/123",
    "content": "<p>Updated content of the post</p>",
    "updated": "2026-03-01T13:00:00Z"
  }
}
```

**Side effects:**
- C2S: Partial update - supplied key-value pairs replace existing; `null` removes field
- S2S: Complete replacement - entire new object representation; server MUST verify Update and object have same origin (authorization check)

#### Delete

Deletes an existing object.

```json
{
  "type": "Delete",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "object": "https://forum.example.com/activitypub/object/note/123"
}
```

**Side effects:**
- Server MAY replace object with `Tombstone`
- Subsequent GET for deleted object: 410 Gone (with Tombstone) or 404 Not Found
- Remote servers SHOULD honor deletion but there is no enforcement mechanism

**Tombstone format:**
```json
{
  "type": "Tombstone",
  "id": "https://forum.example.com/activitypub/object/note/123",
  "formerType": "Note",
  "deleted": "2026-03-01T14:00:00Z"
}
```

#### Follow

Subscribes to another actor's activities.

```json
{
  "type": "Follow",
  "id": "https://forum.example.com/activitypub/activity/follow/456",
  "actor": "https://remote.example/actor",
  "object": "https://forum.example.com/activitypub/actor/42"
}
```

**Side effects:**
- Receiving server SHOULD generate Accept or Reject
- Accept may be automatic or require user approval (locked accounts)
- On Accept: follower added to target's Followers collection; target added to follower's Following collection
- On Reject: MUST NOT add to Followers collection

#### Accept

Accepts a previously received activity (typically Follow).

```json
{
  "type": "Accept",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "object": {
    "type": "Follow",
    "id": "https://remote.example/activities/follow/456",
    "actor": "https://remote.example/actor",
    "object": "https://forum.example.com/activitypub/actor/42"
  }
}
```

**Side effects:**
- If accepting a Follow: SHOULD add actor to receiver's Following collection

#### Reject

Rejects a previously received activity (typically Follow).

```json
{
  "type": "Reject",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "object": {
    "type": "Follow",
    "id": "https://remote.example/activities/follow/456",
    "actor": "https://remote.example/actor",
    "object": "https://forum.example.com/activitypub/actor/42"
  }
}
```

**Side effects:**
- If rejecting a Follow: MUST NOT add to Followers collection

#### Like

Indicates the actor likes an object (equivalent to SMF's "like" feature).

```json
{
  "type": "Like",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "object": "https://remote.example/posts/789"
}
```

**Side effects:**
- C2S: SHOULD add object to actor's `liked` collection
- S2S: SHOULD increment object's like count by adding to `likes` collection

#### Announce

Shares/boosts/reposts an object (no direct SMF equivalent, but could map to "sharing" a post).

```json
{
  "type": "Announce",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "object": "https://remote.example/posts/789",
  "to": ["https://www.w3.org/ns/activitystreams#Public"],
  "cc": ["https://forum.example.com/activitypub/actor/42/followers"]
}
```

**Side effects:**
- S2S: SHOULD increment object's share count by adding to `shares` collection

#### Undo

Undoes a previous activity.

```json
{
  "type": "Undo",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "object": {
    "type": "Like",
    "actor": "https://forum.example.com/activitypub/actor/42",
    "object": "https://remote.example/posts/789"
  }
}
```

**Requirements:**
- Undo and original activity MUST have the same `actor`
- Side effects of original activity reversed to the extent possible
- Do NOT use Undo for Create (use Delete instead) or Add (use Remove instead)
- Commonly used with: Follow, Like, Announce, Block

#### Block

Prevents another actor from interacting with the blocking actor's objects.

```json
{
  "type": "Block",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "object": "https://remote.example/actor"
}
```

**Side effects:**
- Server SHOULD prevent blocked user from interacting
- Servers SHOULD NOT deliver Block activities to the blocked actor

#### Add / Remove

Add or remove objects from collections.

```json
{
  "type": "Add",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "object": "https://forum.example.com/activitypub/object/note/123",
  "target": "https://forum.example.com/activitypub/actor/42/featured"
}
```

**Requirements:**
- MUST include `object` and `target` properties
- Server SHOULD perform the addition/removal unless target is not owned by server

#### Flag (Mastodon Extension)

Used for reporting content/actors to remote moderators.

```json
{
  "type": "Flag",
  "actor": "https://forum.example.com/activitypub/actor",
  "content": "Spam report",
  "object": [
    "https://remote.example/actor",
    "https://remote.example/posts/123"
  ]
}
```

#### Move (Mastodon Extension)

Used for account migration.

```json
{
  "type": "Move",
  "actor": "https://old.example/actor",
  "object": "https://old.example/actor",
  "target": "https://new.example/actor"
}
```

### Link Type

Links are references to resources, distinct from Objects:

```json
{
  "type": "Link",
  "href": "https://example.com/image.png",
  "mediaType": "image/png",
  "name": "An image",
  "width": 800,
  "height": 600
}
```

| Property | Type | Description |
|----------|------|-------------|
| `href` | IRI | Target resource URL (REQUIRED) |
| `mediaType` | MIME | Content type of target |
| `name` | string | Display label |
| `hreflang` | language tag | Target language |
| `rel` | string/array | Link relation type(s) |
| `height` | integer | Rendering height in pixels |
| `width` | integer | Rendering width in pixels |

---

## 8. Server-to-Server Federation Protocol

### Communication Flow

1. Actor on Server A creates content (or performs action)
2. Server A determines recipients from `to`, `cc`, `bcc`, `bto`, `audience`
3. Server A resolves collections to individual actor inboxes
4. Server A de-duplicates recipients
5. Server A excludes the activity's own actor
6. Server A POSTs the activity to each recipient's inbox (or shared inbox)
7. Server B receives the POST, verifies the signature, and processes the activity

### Required Server Behaviors

#### On Receiving Activities via Inbox POST:

1. **Verify HTTP Signature** - Fetch sender's public key, verify signature
2. **De-duplicate** - Check if activity `id` has been seen before; drop duplicates
3. **Verify authenticity** - Optionally dereference the activity's `id` to confirm it exists at origin
4. **Process activity** - Apply side effects based on activity type
5. **Consider inbox forwarding** - Forward if conditions are met (see Section 13)

#### Activity-Specific Processing:

| Activity | Required Server Behavior |
|----------|------------------------|
| Create | Store object locally; add to recipient's inbox |
| Update | Replace stored object (verify same origin as original) |
| Delete | Remove or tombstone stored object |
| Follow | Generate Accept or Reject; add to followers on Accept |
| Accept | If accepting Follow: add to Following collection |
| Reject | If rejecting Follow: do NOT add to Following |
| Like | Increment likes count on object |
| Announce | Increment shares count on object |
| Undo | Reverse side effects of referenced activity |
| Add | Add object to target collection |
| Remove | Remove object from target collection |

### Authorization Verification

For Update activities, the server MUST verify that the Update activity and the object being updated have the **same origin** (same domain). This prevents unauthorized modifications.

General verification principle: dereference the object's `id` to ensure it actually exists at the claimed origin and matches what was received.

---

## 9. Client-to-Server Protocol

### Overview

The C2S protocol allows clients to interact with the server by POSTing activities to the actor's outbox. This is less commonly implemented in the fediverse (most implementations use custom APIs), but understanding it informs the architecture.

### Submission Flow

1. Client authenticates to server
2. Client discovers outbox URL from actor profile
3. Client POSTs activity (or raw object) to outbox
4. Server processes, assigns IDs, and initiates delivery
5. Server returns 201 Created with Location header

### Key Differences from S2S

| Aspect | Client-to-Server | Server-to-Server |
|--------|-----------------|------------------|
| Endpoint | Outbox (POST) | Inbox (POST) |
| Authentication | User credentials (OAuth, etc.) | HTTP Signatures |
| ID assignment | Server generates new IDs | IDs come from remote server |
| Update semantics | Partial (key-value replacement) | Complete replacement |
| Object wrapping | Raw objects auto-wrapped in Create | Activities sent directly |

### Client Addressing Responsibilities

When creating activities, clients SHOULD:
1. Default to Followers Collection and/or Public Collection as targets
2. Examine objects referenced in `object`, `target`, `inReplyTo`, `tag`
3. Retrieve their `actor`/`attributedTo` and addressing
4. Add to `to` or `cc` of new activity
5. Provide UI for users to modify addressing

---

## 10. Collections and Pagination

### Collection Types

| Type | Description | Ordering |
|------|-------------|----------|
| `Collection` | Unordered set of items | No guaranteed order |
| `OrderedCollection` | Ordered set of items | Reverse chronological (newest first) |
| `CollectionPage` | Page within a Collection | Follows parent ordering |
| `OrderedCollectionPage` | Page within an OrderedCollection | Maintains order |

### OrderedCollection Structure

```json
{
  "@context": "https://www.w3.org/ns/activitystreams",
  "id": "https://forum.example.com/activitypub/actor/42/outbox",
  "type": "OrderedCollection",
  "totalItems": 150,
  "first": "https://forum.example.com/activitypub/actor/42/outbox?page=1",
  "last": "https://forum.example.com/activitypub/actor/42/outbox?page=8"
}
```

### OrderedCollectionPage Structure

```json
{
  "@context": "https://www.w3.org/ns/activitystreams",
  "id": "https://forum.example.com/activitypub/actor/42/outbox?page=1",
  "type": "OrderedCollectionPage",
  "partOf": "https://forum.example.com/activitypub/actor/42/outbox",
  "totalItems": 150,
  "next": "https://forum.example.com/activitypub/actor/42/outbox?page=2",
  "prev": null,
  "orderedItems": [
    {
      "type": "Create",
      "id": "https://forum.example.com/activitypub/activity/789",
      "actor": "https://forum.example.com/activitypub/actor/42",
      "object": { ... }
    }
  ]
}
```

### Collection Properties

| Property | Type | Description |
|----------|------|-------------|
| `totalItems` | non-negative integer | Total count of items |
| `first` | CollectionPage/Link | First page |
| `last` | CollectionPage/Link | Last page |
| `current` | CollectionPage/Link | Most recently updated page |
| `items` | array | Items (Collection) |
| `orderedItems` | array | Items (OrderedCollection) |

### CollectionPage Properties

| Property | Type | Description |
|----------|------|-------------|
| `partOf` | Collection/IRI | Parent collection |
| `next` | CollectionPage/Link | Next page |
| `prev` | CollectionPage/Link | Previous page |
| `startIndex` | non-negative integer | Position in parent (OrderedCollectionPage only) |

### Ordering Requirements

- OrderedCollections MUST be presented in **reverse chronological order** (newest first)
- Ordering is consistent but the spec does not mandate which property determines order
- Do NOT use frequently-changing properties like "last updated" for ordering

### Pagination Best Practices

- SHOULD limit collection page sizes to prevent DoS
- Typical page size: 20-50 items
- First request returns the collection overview (totalItems, first, last)
- Client follows `first`/`next` links to paginate

---

## 11. Object Identifiers and Addressing

### Object ID Requirements

All objects distributed via ActivityPub **MUST** have unique global identifiers unless intentionally transient (short-lived activities not meant for later lookup).

**Valid ID formats:**
1. HTTPS URIs with authority belonging to the originating server
2. `null` (implies anonymous object, part of parent context)

**Rules:**
- Public-facing content SHOULD use HTTPS URIs
- Objects MUST be dereferenceable (GETting the `id` returns the object)
- For C2S: objects posted without `id` get one assigned by the server
- For S2S: activities posted without `id` get one assigned by the server

### Addressing Model

Activities are addressed using these properties:

| Property | Visibility | Delivery |
|----------|-----------|----------|
| `to` | Public (visible to recipients) | Delivered to listed actors/collections |
| `cc` | Public (visible to recipients) | Delivered to listed actors/collections |
| `bto` | Hidden (removed before delivery) | Delivered to listed actors/collections |
| `bcc` | Hidden (removed before delivery) | Delivered to listed actors/collections |
| `audience` | Public | Delivered to listed actors/collections |

### Public Addressing

The special Public Collection: `https://www.w3.org/ns/activitystreams#Public`

Valid representations (all equivalent after JSON-LD compaction):
- `https://www.w3.org/ns/activitystreams#Public`
- `Public`
- `as:Public`

Activities addressed to Public are accessible without authentication.

**IMPORTANT:** Implementations MUST NOT deliver to this collection; it is not capable of receiving activities. It is purely an addressing marker.

### Visibility Levels (Mastodon Convention)

| Visibility | `to` | `cc` | Behavior |
|-----------|------|------|----------|
| Public | `as:Public` | followers collection | Visible to everyone, appears on timelines |
| Unlisted | followers collection | `as:Public` | Visible to everyone but not on public timelines |
| Followers-only | followers collection | (none) | Only visible to followers |
| Direct | specific actors | (none) | Only visible to mentioned actors |

---

## 12. Delivery Mechanisms

### Target Identification

1. Collect all URIs from `to`, `bto`, `cc`, `bcc`, and `audience`
2. If a URI is a Collection, dereference it to discover individual actors
3. Servers MUST limit collection indirection (MAY limit to one level)
4. De-duplicate the final recipient list
5. Exclude the activity's own actor from recipients
6. Remove `bto` and `bcc` from the activity before sending

### Delivery Process

For each unique recipient inbox:

1. Construct the HTTP POST request with the activity as body
2. Set `Content-Type: application/ld+json; profile="https://www.w3.org/ns/activitystreams"`
3. Add Date, Host, and Digest headers
4. Sign the request with HTTP Signatures
5. POST to the recipient's inbox URL

### Shared Inbox Optimization

When delivering to multiple followers on the same server:
- MAY consolidate into a single POST to the `sharedInbox` endpoint
- If activity is addressed to Public, MAY deliver to all known shared inboxes
- MUST still deliver individually to actors addressed via `to`/`bto`/`cc`/`bcc`/`audience` who lack a `sharedInbox`

### Asynchronous Delivery

- Delivery to third-party servers SHOULD be asynchronous
- SHOULD retry on network failure
- Use exponential backoff to avoid overloading servers
- Activities between same-origin actors MAY use internal mechanisms (no HTTP needed)

### Delivery Queue Best Practices

1. Queue activities for background processing
2. Use exponential backoff for failed deliveries
3. Track failed endpoints; stop retrying after extended failure (e.g., 6 months)
4. Batch deliveries to shared inboxes when possible
5. Implement rate limiting to avoid being flagged as spam

---

## 13. Inbox Forwarding

### When to Forward

Inbox forwarding occurs when **ALL** of the following conditions are true:

1. This is the **first time** the server has seen this activity
2. The values of `to`, `cc`, or `audience` contain a Collection owned by this server
3. The values of `inReplyTo`, `object`, `target`, or `tag` reference objects owned by this server

### How to Forward

1. Server SHOULD recurse through linked objects (with recursion limits) to find additional recipients
2. Forward the activity only to the addressees of the **original object** (do NOT add new recipients discovered through recursion)
3. POST the activity to the appropriate inboxes

### Purpose

Inbox forwarding solves the problem of replies to objects. When someone replies to a post, the reply is addressed to the post's author, but the author's followers may not know about the reply. The author's server forwards the reply to the appropriate audience.

---

## 14. Mastodon-Specific Extensions

Understanding Mastodon's extensions is critical because it is the dominant fediverse implementation.

### Custom Properties on Actors

| Property | Type | Description |
|----------|------|-------------|
| `manuallyApprovesFollowers` | boolean | Locked account (requires follow approval) |
| `discoverable` | boolean | Opt-in for profile directory |
| `indexable` | boolean | Allow full-text search indexing |
| `suspended` | boolean | Account is suspended |
| `memorial` | boolean | Memorial account flag |
| `featured` | IRI | Collection of pinned posts |
| `featuredTags` | IRI | Collection of featured hashtags |

### Custom Properties on Objects

| Property | Type | Description |
|----------|------|-------------|
| `sensitive` | boolean | Content needs a warning |
| `blurhash` | string | BlurHash preview for media |
| `focalPoint` | [float, float] | Image focal point coordinates |

### Tag Types

```json
{
  "tag": [
    { "type": "Mention", "name": "@user@example.com", "href": "https://example.com/users/user" },
    { "type": "Hashtag", "name": "#topic", "href": "https://example.com/tags/topic" },
    { "type": "Emoji", "name": ":custom:", "icon": { "type": "Image", "url": "https://example.com/emoji.png" } }
  ]
}
```

### Profile Metadata (PropertyValue)

```json
{
  "attachment": [
    {
      "type": "PropertyValue",
      "name": "Website",
      "value": "<a href=\"https://example.com\" rel=\"me\">example.com</a>"
    }
  ]
}
```

### Polls (Question type)

```json
{
  "type": "Question",
  "content": "What do you prefer?",
  "endTime": "2026-03-02T12:00:00Z",
  "votersCount": 42,
  "oneOf": [
    { "type": "Note", "name": "Option A", "replies": { "type": "Collection", "totalItems": 20 } },
    { "type": "Note", "name": "Option B", "replies": { "type": "Collection", "totalItems": 22 } }
  ]
}
```

- `oneOf` = single-choice poll
- `anyOf` = multiple-choice poll
- Votes sent as Create activities with Note objects where `name` matches option text

### HTML Sanitization (Mastodon)

Allowed HTML elements: `<p>`, `<span>`, `<br>`, `<a>`, `<del>`, `<pre>`, `<code>`, `<em>`, `<strong>`, `<b>`, `<i>`, `<u>`, `<ul>`, `<ol>`, `<li>`, `<blockquote>`

Allowed link protocols: `http`, `https`, `dat`, `dweb`, `ipfs`, `ipns`, `ssb`, `gopher`, `xmpp`, `magnet`, `gemini`

---

## 15. Security Considerations

### Content Verification

- Servers SHOULD verify new content is posted by the claiming actor
- Servers SHOULD verify the actor has permission to update claimed resources
- Federated servers SHOULD NOT trust non-origin servers without verification
- At minimum: dereference object's `id` to confirm it exists at origin
- Better: verify HTTP Signatures

### Content Sanitization

- **MUST** sanitize all HTML content before rendering in browser
- Prevent cross-site scripting (XSS) attacks
- Whitelist allowed HTML tags and attributes
- Strip JavaScript, event handlers, and dangerous attributes

### bto/bcc Handling

- Servers **MUST** remove `bto` and `bcc` properties before delivery
- SHOULD omit from display (only intended for original author's reference)

### Recursion Limits

- Set hard limits on recursion depth when resolving objects
- Prevents denial-of-service from infinitely recursive references
- Recommended: 1-3 levels maximum

### Rate Limiting

- Servers SHOULD rate-limit incoming POST requests
- Servers SHOULD rate-limit outgoing delivery
- Use exponential backoff for failed deliveries
- Prevents amplification attacks

### URI Safety

- Whitelist only safe URI schemes: `http`, `https`
- Never allow `file://` scheme in production
- Be cautious with localhost URIs (disable by default in production)
- Carefully validate URI schemes from external input

### Spam Prevention

- Filter incoming content from both local and remote users
- No specific spam mechanism defined in ActivityPub
- Implementations should build their own filtering

### Federation DoS Protection

- Implement rate limiting for incoming federation requests
- Be careful with activities involving side effects (Follow/Like/Announce can be used for amplification)
- Use exponential backoff strategy

---

## 16. Minimum Viable Federation Requirements

### What MUST Be Implemented

To achieve basic federation interoperability with Mastodon and other fediverse software:

#### 1. WebFinger Endpoint
- `GET /.well-known/webfinger?resource=acct:username@domain`
- Returns JRD with `self` link pointing to actor profile
- Content-Type: `application/jrd+json`
- CORS: `Access-Control-Allow-Origin: *`

#### 2. Actor Profiles
- Each federated user has an actor endpoint returning JSON-LD
- MUST include: `id`, `type`, `inbox`, `outbox`
- SHOULD include: `followers`, `following`, `preferredUsername`, `name`, `summary`, `icon`, `publicKey`
- `endpoints.sharedInbox` for efficient delivery

#### 3. Inbox (POST)
- Accept incoming activities from remote servers
- Verify HTTP Signatures on all incoming requests
- De-duplicate by activity `id`
- Process: Create, Follow, Accept, Reject, Undo, Delete, Like, Announce, Update

#### 4. Outbox (GET)
- Return OrderedCollection of public activities
- Support pagination

#### 5. HTTP Signatures
- Sign all outgoing requests (POST to remote inboxes, GET for remote resources)
- Verify all incoming POST requests
- RSA-SHA256 with 2048+ bit keys
- Include `(request-target)`, `host`, `date` headers in signature
- Include `digest` header for POST requests

#### 6. Object Endpoints
- Objects (posts/notes) must be dereferenceable by their `id`
- Return JSON-LD representation with appropriate Content-Type

#### 7. Activity Delivery
- POST activities to remote actor inboxes
- Resolve addressing (to, cc) to discover inboxes
- Use shared inbox when available
- Async delivery with retry and exponential backoff

#### 8. Follow Protocol
- Handle incoming Follow requests
- Generate Accept/Reject responses
- Maintain followers/following collections
- Handle Undo Follow (unfollow)

### What CAN Be Deferred

- Client-to-Server protocol (use SMF's native interface instead)
- Liked collection
- Add/Remove activities
- Inbox forwarding (complex, can add later)
- Move activity (account migration)
- Flag activity (reports)
- Polls (Question type)

---

## 17. SMF Integration Architecture Notes

### Mapping SMF Concepts to ActivityPub

| SMF Concept | ActivityPub Equivalent |
|-------------|----------------------|
| Member | Person actor |
| Forum (instance) | Service or Application actor |
| Board | Could be Group actor or Collection |
| Topic | Context (thread) |
| Post (first in topic) | Note or Article object |
| Reply | Note with `inReplyTo` |
| Edit post | Update activity |
| Delete post | Delete activity |
| Like | Like activity |
| Member profile | Actor document |
| Board subscription | Follow activity to board actor |
| PM | Direct message (Note with only actor in `to`) |
| Ban | Block activity |
| Report | Flag activity |

### URL Structure Recommendations

All ActivityPub URLs should be clean, permanent, and separate from SMF's query-string URLs:

```
/.well-known/webfinger                              -> WebFinger discovery
/activitypub/actor/{member_id}                      -> Actor profile
/activitypub/actor/{member_id}/inbox                -> Actor inbox
/activitypub/actor/{member_id}/outbox               -> Actor outbox
/activitypub/actor/{member_id}/followers             -> Followers collection
/activitypub/actor/{member_id}/following             -> Following collection
/activitypub/inbox                                   -> Shared inbox
/activitypub/object/note/{post_id}                   -> Individual post
/activitypub/object/article/{topic_id}               -> Topic (first post)
/activitypub/activity/{activity_id}                  -> Individual activity
```

### Database Requirements

New tables needed:

1. **activitypub_actors** - Local actor federation data (keys, preferences)
2. **activitypub_followers** - Remote follower relationships
3. **activitypub_following** - Remote accounts being followed
4. **activitypub_activities** - Activity log (sent and received)
5. **activitypub_objects** - Cache of remote objects
6. **activitypub_delivery_queue** - Async delivery queue
7. **activitypub_remote_actors** - Cached remote actor data
8. **activitypub_keys** - RSA key pairs for local actors

### Key Implementation Components

1. **WebFinger Handler** - Responds to discovery queries
2. **Actor Serializer** - Converts SMF members to ActivityPub actors
3. **Object Serializer** - Converts SMF posts to ActivityPub objects (Note/Article)
4. **Inbox Handler** - Processes incoming activities
5. **Outbox Handler** - Serves published activities
6. **HTTP Signature Module** - Signs outgoing requests and verifies incoming ones
7. **Delivery Queue** - Async background delivery of activities
8. **Activity Processor** - Handles side effects of each activity type
9. **Remote Actor Cache** - Fetches and caches remote actor data
10. **Content Sanitizer** - Sanitizes incoming HTML content

### PHP Libraries to Consider

While the activitypub.rocks resource did not list PHP-specific libraries, the implementation will need:

- **phpseclib** or OpenSSL extension - RSA key generation and signing
- **HTTP client** (cURL) - Making outgoing requests
- **JSON-LD processor** - (Optional) Full JSON-LD processing; can be simplified for ActivityPub-only use

---

## Appendix A: Complete Request/Response Examples

### WebFinger Request/Response

```http
GET /.well-known/webfinger?resource=acct:johndoe@forum.example.com HTTP/1.1
Host: forum.example.com
Accept: application/jrd+json
```

```http
HTTP/1.1 200 OK
Content-Type: application/jrd+json
Access-Control-Allow-Origin: *

{
  "subject": "acct:johndoe@forum.example.com",
  "aliases": [
    "https://forum.example.com/activitypub/actor/42"
  ],
  "links": [
    {
      "rel": "self",
      "type": "application/activity+json",
      "href": "https://forum.example.com/activitypub/actor/42"
    },
    {
      "rel": "http://webfinger.net/rel/profile-page",
      "type": "text/html",
      "href": "https://forum.example.com/index.php?action=profile;u=42"
    }
  ]
}
```

### Actor Fetch

```http
GET /activitypub/actor/42 HTTP/1.1
Host: forum.example.com
Accept: application/activity+json
Date: Sun, 01 Mar 2026 12:00:00 GMT
Signature: keyId="https://remote.example/actor#main-key",algorithm="rsa-sha256",headers="(request-target) host date",signature="..."
```

```http
HTTP/1.1 200 OK
Content-Type: application/activity+json

{
  "@context": [
    "https://www.w3.org/ns/activitystreams",
    "https://w3id.org/security/v1"
  ],
  "id": "https://forum.example.com/activitypub/actor/42",
  "type": "Person",
  "preferredUsername": "johndoe",
  "name": "John Doe",
  "summary": "<p>Forum member</p>",
  "inbox": "https://forum.example.com/activitypub/actor/42/inbox",
  "outbox": "https://forum.example.com/activitypub/actor/42/outbox",
  "followers": "https://forum.example.com/activitypub/actor/42/followers",
  "following": "https://forum.example.com/activitypub/actor/42/following",
  "url": "https://forum.example.com/index.php?action=profile;u=42",
  "publicKey": {
    "id": "https://forum.example.com/activitypub/actor/42#main-key",
    "owner": "https://forum.example.com/activitypub/actor/42",
    "publicKeyPem": "-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----\n"
  },
  "icon": {
    "type": "Image",
    "mediaType": "image/jpeg",
    "url": "https://forum.example.com/avatars/42.jpg"
  },
  "endpoints": {
    "sharedInbox": "https://forum.example.com/activitypub/inbox"
  }
}
```

### Incoming Create Activity (New Post)

```http
POST /activitypub/actor/42/inbox HTTP/1.1
Host: forum.example.com
Content-Type: application/ld+json; profile="https://www.w3.org/ns/activitystreams"
Date: Sun, 01 Mar 2026 12:00:00 GMT
Digest: SHA-256=abc123def456...
Signature: keyId="https://mastodon.social/users/alice#main-key",algorithm="rsa-sha256",headers="(request-target) host date digest",signature="..."

{
  "@context": "https://www.w3.org/ns/activitystreams",
  "id": "https://mastodon.social/users/alice/statuses/12345/activity",
  "type": "Create",
  "actor": "https://mastodon.social/users/alice",
  "published": "2026-03-01T12:00:00Z",
  "to": ["https://www.w3.org/ns/activitystreams#Public"],
  "cc": [
    "https://mastodon.social/users/alice/followers",
    "https://forum.example.com/activitypub/actor/42"
  ],
  "object": {
    "id": "https://mastodon.social/users/alice/statuses/12345",
    "type": "Note",
    "attributedTo": "https://mastodon.social/users/alice",
    "content": "<p>@johndoe Great discussion on the forum!</p>",
    "inReplyTo": "https://forum.example.com/activitypub/object/note/789",
    "published": "2026-03-01T12:00:00Z",
    "to": ["https://www.w3.org/ns/activitystreams#Public"],
    "cc": [
      "https://mastodon.social/users/alice/followers",
      "https://forum.example.com/activitypub/actor/42"
    ],
    "tag": [
      {
        "type": "Mention",
        "name": "@johndoe@forum.example.com",
        "href": "https://forum.example.com/activitypub/actor/42"
      }
    ]
  }
}
```

### Follow / Accept Flow

**Incoming Follow:**
```json
{
  "@context": "https://www.w3.org/ns/activitystreams",
  "id": "https://mastodon.social/activities/follow/999",
  "type": "Follow",
  "actor": "https://mastodon.social/users/alice",
  "object": "https://forum.example.com/activitypub/actor/42"
}
```

**Outgoing Accept:**
```json
{
  "@context": "https://www.w3.org/ns/activitystreams",
  "id": "https://forum.example.com/activitypub/activity/accept/888",
  "type": "Accept",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "object": {
    "id": "https://mastodon.social/activities/follow/999",
    "type": "Follow",
    "actor": "https://mastodon.social/users/alice",
    "object": "https://forum.example.com/activitypub/actor/42"
  }
}
```

### Outgoing Create Activity (Forum Post Published)

```json
{
  "@context": [
    "https://www.w3.org/ns/activitystreams",
    "https://w3id.org/security/v1"
  ],
  "id": "https://forum.example.com/activitypub/activity/create/1001",
  "type": "Create",
  "actor": "https://forum.example.com/activitypub/actor/42",
  "published": "2026-03-01T15:30:00Z",
  "to": ["https://www.w3.org/ns/activitystreams#Public"],
  "cc": ["https://forum.example.com/activitypub/actor/42/followers"],
  "object": {
    "id": "https://forum.example.com/activitypub/object/note/1234",
    "type": "Note",
    "attributedTo": "https://forum.example.com/activitypub/actor/42",
    "content": "<p>Just posted a new topic about ActivityPub integration with forums. Check it out!</p>",
    "url": "https://forum.example.com/index.php?topic=1234.0",
    "published": "2026-03-01T15:30:00Z",
    "to": ["https://www.w3.org/ns/activitystreams#Public"],
    "cc": ["https://forum.example.com/activitypub/actor/42/followers"],
    "sensitive": false,
    "tag": [
      {
        "type": "Hashtag",
        "name": "#activitypub",
        "href": "https://forum.example.com/activitypub/tags/activitypub"
      }
    ]
  }
}
```

---

## Appendix B: HTTP Signature Pseudocode

### Signing an Outgoing POST Request

```
function signRequest(actorKeyId, privateKey, method, path, host, body):
    // 1. Calculate body digest
    bodyDigest = base64(sha256(body))
    digestHeader = "SHA-256=" + bodyDigest

    // 2. Get current date in HTTP format
    dateHeader = formatHttpDate(now())  // e.g., "Sun, 01 Mar 2026 12:00:00 GMT"

    // 3. Build signing string
    signingString = "(request-target): " + lowercase(method) + " " + path + "\n"
                  + "host: " + host + "\n"
                  + "date: " + dateHeader + "\n"
                  + "digest: " + digestHeader

    // 4. Sign with RSA-SHA256
    signature = base64(rsaSha256Sign(privateKey, signingString))

    // 5. Construct Signature header
    signatureHeader = 'keyId="' + actorKeyId + '",'
                    + 'algorithm="rsa-sha256",'
                    + 'headers="(request-target) host date digest",'
                    + 'signature="' + signature + '"'

    // 6. Set headers
    headers = {
        "Host": host,
        "Date": dateHeader,
        "Digest": digestHeader,
        "Signature": signatureHeader,
        "Content-Type": "application/ld+json; profile=\"https://www.w3.org/ns/activitystreams\""
    }

    return headers
```

### Verifying an Incoming POST Request

```
function verifyRequest(request):
    // 1. Parse Signature header
    params = parseSignatureHeader(request.headers["Signature"])
    // params = { keyId, algorithm, headers, signature }

    // 2. Verify Date is within tolerance (12 hours)
    requestDate = parseHttpDate(request.headers["Date"])
    if (abs(now() - requestDate) > 12 * 3600):
        return REJECT("Date too old or too far in future")

    // 3. Verify Digest (for POST)
    expectedDigest = "SHA-256=" + base64(sha256(request.body))
    if (request.headers["Digest"] != expectedDigest):
        return REJECT("Digest mismatch")

    // 4. Reconstruct signing string from listed headers
    signingString = ""
    for headerName in params.headers.split(" "):
        if headerName == "(request-target)":
            signingString += "(request-target): " + lowercase(request.method) + " " + request.path
        else:
            signingString += headerName + ": " + request.headers[headerName]
        signingString += "\n"
    signingString = trimTrailingNewline(signingString)

    // 5. Fetch actor's public key
    actorDocument = httpGet(params.keyId)  // May need to strip fragment
    publicKeyPem = actorDocument.publicKey.publicKeyPem

    // 6. Verify signature
    decodedSignature = base64Decode(params.signature)
    isValid = rsaSha256Verify(publicKeyPem, signingString, decodedSignature)

    // 7. Verify actor matches activity
    activityActor = jsonDecode(request.body).actor
    keyOwner = actorDocument.publicKey.owner
    if (activityActor != keyOwner):
        return REJECT("Actor mismatch")

    return isValid ? ACCEPT : REJECT("Invalid signature")
```

---

## Appendix C: Conformance Requirements Summary

### For a Federated Server (S2S):

**MUST:**
- Support ActivityStreams 2.0 vocabulary
- Have `inbox` and `outbox` endpoints on actors
- Support OrderedCollections for inbox/outbox
- De-duplicate inbox activities by `id`
- Validate/verify received content
- Respect Content-Type and Accept headers
- Handle HTTP status codes: 201, 404, 405, 410
- Verify Update activities have same origin as object
- Remove `bto`/`bcc` before delivery
- Limit collection indirection during delivery
- De-duplicate recipient list
- Exclude activity's actor from recipients
- Not deliver to the Public collection

**SHOULD:**
- Have followers/following collections
- Support shared inbox delivery
- Implement rate limiting
- Sanitize content for display
- Verify HTTP Signatures
- Perform asynchronous delivery with retry
- Generate Accept/Reject for Follow activities
- Forward activities when inbox forwarding conditions are met
- Present OrderedCollections in reverse chronological order

**MAY:**
- Require authorization for resource access
- Return 404 instead of 403 to hide object existence
- Replace deleted objects with Tombstones
- Support additional ActivityStreams types and properties
