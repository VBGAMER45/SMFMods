<?php
/**
 * ActivityPub Federation - HTTP Signature Signing & Verification
 *
 * Implements HTTP Signatures (draft-cavage-http-signatures-12) using RSA-SHA256.
 * Used for all outbound POST deliveries and verified on all inbox POSTs.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Sign an HTTP request with the given actor's private key.
 *
 * @param string $url Target URL.
 * @param string $method HTTP method (GET/POST).
 * @param array $actor Actor record with private_key_pem_decrypted.
 * @param string $body Request body (for POST).
 * @return array Headers to include in the request.
 */
function activitypub_sign_request($url, $method, $actor, $body = '')
{
	$parsed = parse_url($url);
	$host = $parsed['host'];
	$path = isset($parsed['path']) ? $parsed['path'] : '/';
	if (isset($parsed['query']))
		$path .= '?' . $parsed['query'];

	$date = gmdate('D, d M Y H:i:s \G\M\T');
	$method = strtolower($method);

	$headers = array(
		'Host' => $host,
		'Date' => $date,
	);

	$signed_headers = array('(request-target)', 'host', 'date');
	$signing_string_parts = array(
		'(request-target): ' . $method . ' ' . $path,
		'host: ' . $host,
		'date: ' . $date,
	);

	// For POST, include digest.
	if ($method === 'post' && $body !== '')
	{
		$digest = 'SHA-256=' . base64_encode(hash('sha256', $body, true));
		$headers['Digest'] = $digest;
		$headers['Content-Type'] = 'application/activity+json';

		$signed_headers[] = 'digest';
		$signing_string_parts[] = 'digest: ' . $digest;
	}

	// For GET with Accept header.
	if ($method === 'get')
	{
		$headers['Accept'] = 'application/activity+json, application/ld+json; profile="https://www.w3.org/ns/activitystreams"';
	}

	$signing_string = implode("\n", $signing_string_parts);

	// Sign with RSA-SHA256.
	$private_key = $actor['private_key_pem_decrypted'];
	if (empty($private_key))
	{
		activitypub_log_error('No private key available for signing (actor: ' . $actor['ap_id'] . ')');
		return $headers;
	}

	$key_resource = openssl_pkey_get_private($private_key);
	if ($key_resource === false)
	{
		activitypub_log_error('Failed to load private key for signing: ' . openssl_error_string());
		return $headers;
	}

	$signature = '';
	$result = openssl_sign($signing_string, $signature, $key_resource, OPENSSL_ALGO_SHA256);

	if (!$result)
	{
		activitypub_log_error('Failed to create signature: ' . openssl_error_string());
		return $headers;
	}

	$signature_b64 = base64_encode($signature);
	$key_id = $actor['ap_id'] . '#main-key';

	$headers['Signature'] = sprintf(
		'keyId="%s",algorithm="rsa-sha256",headers="%s",signature="%s"',
		$key_id,
		implode(' ', $signed_headers),
		$signature_b64
	);

	return $headers;
}

/**
 * Verify an HTTP Signature on an incoming request.
 *
 * @return array|false The verified actor data on success, false on failure.
 */
function activitypub_verify_http_signature()
{
	global $sourcedir;

	$sig_header = isset($_SERVER['HTTP_SIGNATURE']) ? $_SERVER['HTTP_SIGNATURE'] : '';
	if (empty($sig_header))
		return false;

	// Parse the Signature header.
	$sig_params = activitypub_parse_signature_header($sig_header);
	if (empty($sig_params) || empty($sig_params['keyId']) || empty($sig_params['signature']))
		return false;

	// Fetch the actor's public key.
	require_once($sourcedir . '/Subs-ActivityPub-Fetch.php');
	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

	$key_id = $sig_params['keyId'];

	// keyId is usually actorUrl#main-key - extract the actor URL.
	$actor_url = $key_id;
	$hash_pos = strpos($key_id, '#');
	if ($hash_pos !== false)
		$actor_url = substr($key_id, 0, $hash_pos);

	// Fetch the actor.
	$actor_data = activitypub_fetch_remote_actor($actor_url);
	if (empty($actor_data))
	{
		activitypub_log_error('Could not fetch actor for signature verification: ' . $actor_url);
		return false;
	}

	// Get the public key.
	$public_key_pem = '';
	if (isset($actor_data['publicKey']['publicKeyPem']))
		$public_key_pem = $actor_data['publicKey']['publicKeyPem'];

	if (empty($public_key_pem))
	{
		activitypub_log_error('No public key found for actor: ' . $actor_url);
		return false;
	}

	// Rebuild the signing string.
	$headers_list = isset($sig_params['headers']) ? explode(' ', $sig_params['headers']) : array('date');
	$signing_parts = array();

	foreach ($headers_list as $header_name)
	{
		$header_name = strtolower(trim($header_name));

		if ($header_name === '(request-target)')
		{
			$method = strtolower($_SERVER['REQUEST_METHOD']);
			$path = $_SERVER['REQUEST_URI'];
			$signing_parts[] = '(request-target): ' . $method . ' ' . $path;
		}
		elseif ($header_name === 'host')
		{
			$signing_parts[] = 'host: ' . $_SERVER['HTTP_HOST'];
		}
		elseif ($header_name === 'date')
		{
			$date = isset($_SERVER['HTTP_DATE']) ? $_SERVER['HTTP_DATE'] : '';
			if (empty($date))
				return false;

			// Check date is within reasonable range (±12 hours).
			$date_time = strtotime($date);
			if (abs(time() - $date_time) > 43200)
			{
				activitypub_log_error('Signature date too old/new: ' . $date);
				return false;
			}

			$signing_parts[] = 'date: ' . $date;
		}
		elseif ($header_name === 'digest')
		{
			$digest = isset($_SERVER['HTTP_DIGEST']) ? $_SERVER['HTTP_DIGEST'] : '';
			if (empty($digest))
				return false;

			$signing_parts[] = 'digest: ' . $digest;
		}
		elseif ($header_name === 'content-type')
		{
			$ct = isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : ($_SERVER['CONTENT_TYPE'] ?? '');
			$signing_parts[] = 'content-type: ' . $ct;
		}
		else
		{
			// Generic header lookup.
			$server_key = 'HTTP_' . strtoupper(str_replace('-', '_', $header_name));
			if (isset($_SERVER[$server_key]))
				$signing_parts[] = $header_name . ': ' . $_SERVER[$server_key];
			else
				return false;
		}
	}

	$signing_string = implode("\n", $signing_parts);
	$signature = base64_decode($sig_params['signature']);

	// Verify the signature.
	$key_resource = openssl_pkey_get_public($public_key_pem);
	if ($key_resource === false)
	{
		activitypub_log_error('Failed to load public key: ' . openssl_error_string());
		return false;
	}

	$algorithm = isset($sig_params['algorithm']) ? $sig_params['algorithm'] : 'rsa-sha256';
	$algo_map = array(
		'rsa-sha256' => OPENSSL_ALGO_SHA256,
		'rsa-sha512' => OPENSSL_ALGO_SHA512,
	);
	$openssl_algo = isset($algo_map[$algorithm]) ? $algo_map[$algorithm] : OPENSSL_ALGO_SHA256;

	$result = openssl_verify($signing_string, $signature, $key_resource, $openssl_algo);

	if ($result !== 1)
	{
		activitypub_log_error('Signature verification failed for: ' . $actor_url);
		return false;
	}

	return $actor_data;
}

/**
 * Verify the Digest header matches the request body.
 *
 * @param string $body The request body.
 * @return bool True if valid or no digest present, false if mismatch.
 */
function activitypub_verify_digest($body)
{
	$digest_header = isset($_SERVER['HTTP_DIGEST']) ? $_SERVER['HTTP_DIGEST'] : '';
	if (empty($digest_header))
		return true; // No digest to verify.

	// Parse "SHA-256=base64hash"
	if (strpos($digest_header, 'SHA-256=') !== 0 && strpos($digest_header, 'sha-256=') !== 0)
	{
		activitypub_log_error('Unsupported digest algorithm: ' . $digest_header);
		return false;
	}

	$expected_hash = substr($digest_header, 8);
	$actual_hash = base64_encode(hash('sha256', $body, true));

	return hash_equals($expected_hash, $actual_hash);
}

/**
 * Parse the Signature header into its components.
 *
 * @param string $header The Signature header value.
 * @return array Parsed components (keyId, algorithm, headers, signature).
 */
function activitypub_parse_signature_header($header)
{
	$params = array();

	// Match key="value" pairs.
	preg_match_all('/(\w+)="([^"]*)"/', $header, $matches, PREG_SET_ORDER);

	foreach ($matches as $match)
	{
		$params[$match[1]] = $match[2];
	}

	return $params;
}
