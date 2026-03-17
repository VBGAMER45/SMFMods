<?php

/**
 * Mobile-First PWA Shell — Web Push Notification System
 *
 * Handles push subscription management and notification sending.
 * Uses pure PHP openssl for VAPID signing — no external dependencies.
 *
 * @package smfmods:pwa-shell
 * @license MIT
 */

namespace PWA;

class PWAPush
{
	/**
	 * Route push API requests.
	 *
	 * Accessed via ?action=pwa-push;sa=subscribe|unsubscribe|test
	 */
	public static function endpoint(): void
	{
		global $user_info, $modSettings;

		// Push must be enabled
		if (empty($modSettings['pwa_push_enabled']))
		{
			self::jsonResponse(['error' => 'Push notifications are disabled'], 403);
			return;
		}

		// All push endpoints require login
		if (empty($user_info['id']) || $user_info['is_guest'])
		{
			self::jsonResponse(['error' => 'Authentication required'], 401);
			return;
		}

		$sa = isset($_GET['sa']) ? $_GET['sa'] : '';

		switch ($sa)
		{
			case 'subscribe':
				self::subscribe();
				break;
			case 'unsubscribe':
				self::unsubscribe();
				break;
			case 'test':
				self::sendTest();
				break;
			default:
				self::jsonResponse(['error' => 'Unknown sub-action'], 400);
		}
	}

	/**
	 * Store a push subscription for the current user.
	 */
	private static function subscribe(): void
	{
		global $smcFunc, $user_info, $context;

		// Verify session
		checkSession('request');

		$input = json_decode(file_get_contents('php://input'), true);

		if (empty($input['subscription']['endpoint']) || empty($input['subscription']['keys']['p256dh']) || empty($input['subscription']['keys']['auth']))
		{
			self::jsonResponse(['error' => 'Invalid subscription data'], 400);
			return;
		}

		$endpoint = $input['subscription']['endpoint'];
		$p256dh   = $input['subscription']['keys']['p256dh'];
		$auth     = $input['subscription']['keys']['auth'];

		// Remove any existing subscription with the same endpoint for this user
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}pwa_push_subscriptions
			WHERE id_member = {int:member}
				AND endpoint = {string:endpoint}',
			[
				'member'   => $user_info['id'],
				'endpoint' => $endpoint,
			]
		);

		// Insert new subscription
		$smcFunc['db_insert']('insert',
			'{db_prefix}pwa_push_subscriptions',
			[
				'id_member'  => 'int',
				'endpoint'   => 'string',
				'p256dh'     => 'string',
				'auth'       => 'string',
				'created_at' => 'int',
			],
			[
				$user_info['id'],
				$endpoint,
				$p256dh,
				$auth,
				time(),
			],
			['id']
		);

		self::jsonResponse(['success' => true]);
	}

	/**
	 * Remove a push subscription.
	 */
	private static function unsubscribe(): void
	{
		global $smcFunc, $user_info;

		checkSession('request');

		$input = json_decode(file_get_contents('php://input'), true);

		if (empty($input['endpoint']))
		{
			self::jsonResponse(['error' => 'Endpoint required'], 400);
			return;
		}

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}pwa_push_subscriptions
			WHERE id_member = {int:member}
				AND endpoint = {string:endpoint}',
			[
				'member'   => $user_info['id'],
				'endpoint' => $input['endpoint'],
			]
		);

		self::jsonResponse(['success' => true]);
	}

	/**
	 * Send a test push notification (admin only).
	 */
	private static function sendTest(): void
	{
		global $user_info;

		checkSession('request');

		if (!$user_info['is_admin'])
		{
			self::jsonResponse(['error' => 'Admin access required'], 403);
			return;
		}

		$result = self::sendToMember($user_info['id'], [
			'title' => 'PWA Shell Test',
			'body'  => 'Push notifications are working!',
			'url'   => '/',
			'tag'   => 'pwa-test',
		]);

		self::jsonResponse(['success' => true, 'sent' => $result]);
	}

	// ─── Scheduled Task: Alert-to-Push Bridge ───────────────────────────────

	/**
	 * Scheduled task that polls for new unread alerts and sends push notifications.
	 *
	 * Runs every 2 minutes via SMF's scheduled task system.
	 * Tracks the last-processed alert ID in $modSettings to avoid duplicates.
	 *
	 * Called as: PWA\PWAPush::scheduledPushAlerts#
	 *
	 * @return bool Always returns true
	 */
	public static function scheduledPushAlerts(): bool
	{
		global $smcFunc, $modSettings, $scripturl;

		if (empty($modSettings['pwa_push_enabled']))
			return true;

		$lastAlertId = !empty($modSettings['pwa_push_last_alert_id']) ? (int) $modSettings['pwa_push_last_alert_id'] : 0;

		// Fetch new unread alerts since last check, grouped by member.
		// Only get alerts for members who have push subscriptions.
		$result = $smcFunc['db_query']('', '
			SELECT a.id_alert, a.id_member, a.id_member_started, a.member_name,
				a.content_type, a.content_id, a.content_action, a.extra
			FROM {db_prefix}user_alerts AS a
				INNER JOIN {db_prefix}pwa_push_subscriptions AS ps ON (a.id_member = ps.id_member)
			WHERE a.id_alert > {int:last_id}
				AND a.is_read = 0
			GROUP BY a.id_alert
			ORDER BY a.id_alert ASC
			LIMIT 50',
			[
				'last_id' => $lastAlertId,
			]
		);

		$maxAlertId = $lastAlertId;
		$alerts = [];

		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$alerts[] = $row;
			if ((int) $row['id_alert'] > $maxAlertId)
				$maxAlertId = (int) $row['id_alert'];
		}
		$smcFunc['db_free_result']($result);

		// Nothing new
		if (empty($alerts))
			return true;

		// Process each alert into a push notification
		foreach ($alerts as $alert)
		{
			$push = self::alertToPayload($alert, $scripturl);

			if ($push !== false)
				self::sendToMember((int) $alert['id_member'], $push);
		}

		// Update the watermark
		updateSettings(['pwa_push_last_alert_id' => $maxAlertId]);

		return true;
	}

	/**
	 * Convert an SMF alert row into a Web Push notification payload.
	 *
	 * @param array  $alert     Alert row from user_alerts table
	 * @param string $scripturl SMF script URL
	 * @return array|false      Push payload or false to skip
	 */
	private static function alertToPayload(array $alert, string $scripturl)
	{
		$extra = !empty($alert['extra']) ? @json_decode($alert['extra'], true) : [];
		$who = $alert['member_name'];
		$type = $alert['content_type'];
		$action = $alert['content_action'];
		$contentId = (int) $alert['content_id'];

		// Build human-readable notification based on type + action
		switch ($type . '_' . $action)
		{
			// ── Topic/Post Alerts ──────────────────────────────────

			case 'msg_mention':
				return [
					'title' => 'Mentioned by ' . $who,
					'body'  => !empty($extra['content_subject']) ? $extra['content_subject'] : 'You were mentioned in a post.',
					'url'   => !empty($extra['content_link']) ? $extra['content_link'] : $scripturl . '?msg=' . $contentId,
					'tag'   => 'mention-' . $contentId,
				];

			case 'msg_quote':
				return [
					'title' => 'Quoted by ' . $who,
					'body'  => !empty($extra['content_subject']) ? $extra['content_subject'] : 'Your post was quoted.',
					'url'   => !empty($extra['content_link']) ? $extra['content_link'] : $scripturl . '?msg=' . $contentId,
					'tag'   => 'quote-' . $contentId,
				];

			case 'msg_like':
				return [
					'title' => $who . ' liked your post',
					'body'  => !empty($extra['content_subject']) ? $extra['content_subject'] : 'Someone liked your post.',
					'url'   => $scripturl . '?msg=' . $contentId,
					'tag'   => 'like-' . $contentId,
				];

			case 'topic_reply':
				return [
					'title' => 'New reply from ' . $who,
					'body'  => !empty($extra['content_subject']) ? $extra['content_subject'] : 'New reply in a topic you follow.',
					'url'   => !empty($extra['content_link']) ? $extra['content_link'] : $scripturl . '?topic=' . (!empty($extra['topic']) ? $extra['topic'] : $contentId) . '.new;topicseen#new',
					'tag'   => 'reply-' . $contentId,
				];

			case 'board_topic':
				return [
					'title' => 'New topic by ' . $who,
					'body'  => !empty($extra['content_subject']) ? $extra['content_subject'] : 'New topic in a board you watch.',
					'url'   => !empty($extra['content_link']) ? $extra['content_link'] : $scripturl . '?topic=' . $contentId . '.0',
					'tag'   => 'topic-' . $contentId,
				];

			// ── Personal Messages (future-proofing, PMs don't create alerts in 2.1) ──

			case 'pm_new':
			case 'pm_reply':
				return [
					'title' => 'Message from ' . $who,
					'body'  => !empty($extra['pm_subject']) ? $extra['pm_subject'] : 'You received a new personal message.',
					'url'   => $scripturl . '?action=pm',
					'tag'   => 'pm-' . $contentId,
				];

			// ── Group / Moderation ─────────────────────────────────

			case 'groupr_approved':
				return [
					'title' => 'Group Request Approved',
					'body'  => !empty($extra['group_name']) ? 'You were added to ' . $extra['group_name'] . '.' : 'Your group request was approved.',
					'url'   => $scripturl . '?action=profile;area=groupmembership',
					'tag'   => 'group-' . $contentId,
				];

			case 'member_buddy_request':
				return [
					'title' => 'Buddy Request',
					'body'  => $who . ' added you as a buddy.',
					'url'   => $scripturl . '?action=profile;u=' . (int) $alert['id_member_started'],
					'tag'   => 'buddy-' . $alert['id_member_started'],
				];

			// ── Catch-all for unknown alert types ──────────────────

			default:
				// Still send a generic notification so users know something happened
				return [
					'title' => 'New Notification',
					'body'  => $who . ' — ' . str_replace('_', ' ', $action),
					'url'   => $scripturl . '?action=profile;area=showalerts',
					'tag'   => 'alert-' . $alert['id_alert'],
				];
		}
	}

	/**
	 * Send a push notification to all subscriptions for a given member.
	 *
	 * @param int   $memberId  Target member ID
	 * @param array $payload   Notification data [title, body, url, tag]
	 * @return int  Number of notifications successfully sent
	 */
	public static function sendToMember(int $memberId, array $payload): int
	{
		global $smcFunc, $modSettings;

		if (empty($modSettings['pwa_push_enabled']) || empty($modSettings['pwa_vapid_private_pem']))
			return 0;

		// Get all subscriptions for this member
		$result = $smcFunc['db_query']('', '
			SELECT id, endpoint, p256dh, auth
			FROM {db_prefix}pwa_push_subscriptions
			WHERE id_member = {int:member}',
			[
				'member' => $memberId,
			]
		);

		$sent = 0;
		$stale = [];

		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$success = self::sendPush(
				$row['endpoint'],
				$row['p256dh'],
				$row['auth'],
				$payload
			);

			if ($success === true)
				$sent++;
			elseif ($success === 'gone')
				$stale[] = $row['id'];
		}
		$smcFunc['db_free_result']($result);

		// Clean up stale subscriptions (410 Gone)
		if (!empty($stale))
		{
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}pwa_push_subscriptions
				WHERE id IN ({array_int:ids})',
				[
					'ids' => $stale,
				]
			);
		}

		return $sent;
	}

	/**
	 * Send a single Web Push notification.
	 *
	 * Implements the Web Push protocol with VAPID authentication.
	 * Uses PHP openssl for all cryptographic operations.
	 *
	 * @return true|string  true on success, 'gone' if subscription expired, 'error' otherwise
	 */
	private static function sendPush(string $endpoint, string $p256dh, string $auth, array $payload)
	{
		global $modSettings, $boardurl;

		$payloadJson = json_encode($payload);

		// Build VAPID JWT
		$vapidEmail = !empty($modSettings['pwa_vapid_email']) ? $modSettings['pwa_vapid_email'] : 'webmaster@' . parse_url($boardurl, PHP_URL_HOST);
		$audience = parse_url($endpoint, PHP_URL_SCHEME) . '://' . parse_url($endpoint, PHP_URL_HOST);

		$jwt = self::createVapidJwt($audience, $vapidEmail, $modSettings['pwa_vapid_private_pem']);
		if ($jwt === false)
			return 'error';

		// Encrypt payload using Web Push encryption (RFC 8291)
		$encrypted = self::encryptPayload($payloadJson, $p256dh, $auth);
		if ($encrypted === false)
			return 'error';

		// Build the HTTP request
		$headers = [
			'Content-Type: application/octet-stream',
			'Content-Encoding: aes128gcm',
			'Content-Length: ' . strlen($encrypted['ciphertext']),
			'TTL: 86400',
			'Authorization: vapid t=' . $jwt . ', k=' . $modSettings['pwa_vapid_public'],
		];

		$ch = curl_init($endpoint);
		curl_setopt_array($ch, [
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $encrypted['ciphertext'],
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 30,
		]);

		curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode >= 200 && $httpCode < 300)
			return true;

		if ($httpCode === 410 || $httpCode === 404)
			return 'gone';

		return 'error';
	}

	/**
	 * Create a VAPID JWT token.
	 *
	 * @return string|false  The signed JWT or false on failure
	 */
	private static function createVapidJwt(string $audience, string $subject, string $privatePem)
	{
		$header = self::base64url(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));

		$payload = self::base64url(json_encode([
			'aud' => $audience,
			'exp' => time() + 86400,
			'sub' => 'mailto:' . $subject,
		]));

		$signingInput = $header . '.' . $payload;

		$key = openssl_pkey_get_private($privatePem);
		if ($key === false)
			return false;

		$success = openssl_sign($signingInput, $derSignature, $key, OPENSSL_ALGO_SHA256);
		if (!$success)
			return false;

		// Convert DER signature to raw r||s (64 bytes)
		$rawSignature = self::derToRaw($derSignature);
		if ($rawSignature === false)
			return false;

		return $signingInput . '.' . self::base64url($rawSignature);
	}

	/**
	 * Encrypt payload per RFC 8291 (Web Push Message Encryption).
	 *
	 * @return array|false  ['ciphertext' => ...] or false on failure
	 */
	private static function encryptPayload(string $payload, string $userPublicKeyB64, string $userAuthB64)
	{
		$userPublicKey = self::base64urlDecode($userPublicKeyB64);
		$userAuth      = self::base64urlDecode($userAuthB64);

		if (strlen($userPublicKey) !== 65 || strlen($userAuth) !== 16)
			return false;

		// Generate local ECDH key pair (with Windows openssl.cnf fallbacks)
		$localKey = self::generateEcKey();
		if ($localKey === false)
			return false;

		$localDetails = openssl_pkey_get_details($localKey);
		$localX = str_pad($localDetails['ec']['x'], 32, "\0", STR_PAD_LEFT);
		$localY = str_pad($localDetails['ec']['y'], 32, "\0", STR_PAD_LEFT);
		$localPublicKey = "\x04" . $localX . $localY;

		// Derive shared secret via ECDH
		// We need to create a PEM for the user's public key to use with openssl
		$sharedSecret = self::deriveSharedSecret($localKey, $userPublicKey);
		if ($sharedSecret === false)
			return false;

		// Key derivation per RFC 8291
		// IKM = ECDH(local_private, user_public)
		// PRK = HKDF-Extract(auth_secret, IKM)
		$prkKey = hash_hmac('sha256', $sharedSecret, $userAuth, true);

		// Derive content encryption key
		$context = "WebPush: info\x00" . $userPublicKey . $localPublicKey;
		$ikm = self::hkdfExpand($prkKey, $context, 32);

		// Generate salt (16 bytes)
		$salt = random_bytes(16);

		// Derive final key and nonce
		$prk = hash_hmac('sha256', $ikm, $salt, true);
		$cek = self::hkdfExpand($prk, "Content-Encoding: aes128gcm\x00", 16);
		$nonce = self::hkdfExpand($prk, "Content-Encoding: nonce\x00", 12);

		// Pad payload (add 0x02 delimiter + optional padding)
		$paddedPayload = $payload . "\x02";

		// Encrypt with AES-128-GCM
		$tag = '';
		$encrypted = openssl_encrypt(
			$paddedPayload,
			'aes-128-gcm',
			$cek,
			OPENSSL_RAW_DATA,
			$nonce,
			$tag,
			'',
			16
		);

		if ($encrypted === false)
			return false;

		// Build aes128gcm header: salt(16) || rs(4) || idlen(1) || keyid(65)
		$rs = pack('N', 4096);
		$header = $salt . $rs . chr(65) . $localPublicKey;

		return [
			'ciphertext' => $header . $encrypted . $tag,
		];
	}

	/**
	 * Derive ECDH shared secret between local private key and remote public key.
	 *
	 * @return string|false  The shared secret or false on failure
	 */
	private static function deriveSharedSecret($localKey, string $remotePublicKeyRaw)
	{
		// Build a PEM-encoded EC public key for the remote party
		// This requires wrapping the raw key in ASN.1/DER structure

		// EC public key DER prefix for P-256 (uncompressed point)
		$derPrefix = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200');
		$derKey = $derPrefix . $remotePublicKeyRaw;
		$pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($derKey), 64, "\n") . "-----END PUBLIC KEY-----\n";

		$remoteKey = openssl_pkey_get_public($pem);
		if ($remoteKey === false)
			return false;

		// Use openssl_pkey_derive if available (PHP 7.3+)
		if (function_exists('openssl_pkey_derive'))
		{
			$shared = openssl_pkey_derive($remoteKey, $localKey, 256);
			return $shared !== false ? $shared : false;
		}

		// Fallback: this shouldn't happen on modern PHP but guard against it
		return false;
	}

	/**
	 * HKDF-Expand (RFC 5869) — simplified single-block extraction.
	 */
	private static function hkdfExpand(string $prk, string $info, int $length): string
	{
		$t = hash_hmac('sha256', $info . "\x01", $prk, true);
		return substr($t, 0, $length);
	}

	/**
	 * Convert DER-encoded ECDSA signature to raw r||s format (64 bytes).
	 */
	private static function derToRaw(string $der)
	{
		// DER: 0x30 [len] 0x02 [rlen] [r] 0x02 [slen] [s]
		$pos = 0;
		if (ord($der[$pos++]) !== 0x30)
			return false;

		$pos++; // skip total length

		if (ord($der[$pos++]) !== 0x02)
			return false;

		$rLen = ord($der[$pos++]);
		$r = substr($der, $pos, $rLen);
		$pos += $rLen;

		if (ord($der[$pos++]) !== 0x02)
			return false;

		$sLen = ord($der[$pos++]);
		$s = substr($der, $pos, $sLen);

		// Strip leading zeros and pad to 32 bytes
		$r = ltrim($r, "\x00");
		$s = ltrim($s, "\x00");

		return str_pad($r, 32, "\x00", STR_PAD_LEFT) . str_pad($s, 32, "\x00", STR_PAD_LEFT);
	}

	/**
	 * Generate an EC P-256 key, trying multiple openssl.cnf paths on Windows.
	 *
	 * @return \OpenSSLAsymmetricKey|resource|false
	 */
	private static function generateEcKey()
	{
		$params = [
			'curve_name'       => 'prime256v1',
			'private_key_type' => OPENSSL_KEYTYPE_EC,
		];

		// Attempt 1: default config
		$key = @openssl_pkey_new($params);
		if ($key !== false)
			return $key;

		// Attempt 2: try known config file locations
		foreach (self::opensslConfigCandidates() as $path)
		{
			if (!empty($path) && file_exists($path))
			{
				$key = @openssl_pkey_new($params + ['config' => $path]);
				if ($key !== false)
					return $key;
			}
		}

		// Attempt 3: write a minimal temp config
		$tmpConf = sys_get_temp_dir() . '/pwa_openssl_' . getmypid() . '.cnf';
		@file_put_contents($tmpConf, "HOME = .\nopenssl_conf = openssl_init\n[openssl_init]\nproviders = provider_sect\n[provider_sect]\ndefault = default_sect\n[default_sect]\nactivate = 1\n[req]\ndistinguished_name = req_distinguished_name\n[req_distinguished_name]\n");
		$key = @openssl_pkey_new($params + ['config' => $tmpConf]);
		@unlink($tmpConf);

		return $key !== false ? $key : false;
	}

	/**
	 * Return candidate openssl.cnf paths (Windows-aware).
	 */
	private static function opensslConfigCandidates(): array
	{
		return array_filter([
			getenv('OPENSSL_CONF'),
			PHP_BINARY ? dirname(PHP_BINARY) . '/extras/ssl/openssl.cnf' : null,
			PHP_BINARY ? dirname(PHP_BINARY) . '/extras/openssl.cnf' : null,
			'C:/Program Files/Common Files/SSL/openssl.cnf',
			'C:/Program Files (x86)/Common Files/SSL/openssl.cnf',
		]);
	}

	/**
	 * Base64url encode (no padding).
	 */
	private static function base64url(string $data): string
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	/**
	 * Base64url decode.
	 */
	private static function base64urlDecode(string $data): string
	{
		return base64_decode(strtr($data, '-_', '+/'));
	}

	/**
	 * Send a JSON response and exit.
	 */
	private static function jsonResponse(array $data, int $status = 200): void
	{
		if ($status !== 200)
			http_response_code($status);

		header('Content-Type: application/json');
		echo json_encode($data);
		exit;
	}
}
