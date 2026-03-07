<?php
/**
 * ActivityPub Federation - Remote Actor/Object Fetching
 *
 * Fetches remote actors and objects using signed HTTP GET requests.
 * Includes caching via SMF's cache system.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Fetch a remote actor by URL.
 * Caches results for 1 hour.
 *
 * @param string $url The actor URL.
 * @param bool $force_refresh Bypass cache.
 * @return array|false Actor data or false.
 */
function activitypub_fetch_remote_actor($url, $force_refresh = false)
{
	if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL))
		return false;

	// Only allow https URLs (except in development).
	if (strpos($url, 'https://') !== 0 && strpos($url, 'http://localhost') !== 0 && strpos($url, 'http://127.') !== 0)
	{
		activitypub_log_error('Refusing to fetch non-HTTPS actor: ' . $url);
		return false;
	}

	// Check cache.
	$cache_key = 'ap_actor_' . md5($url);
	if (!$force_refresh)
	{
		$cached = cache_get_data($cache_key, 3600);
		if ($cached !== null)
			return $cached;
	}

	// Check our DB first.
	global $smcFunc, $sourcedir;

	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

	$existing = activitypub_get_actor_by_ap_id($url);
	if (!empty($existing) && !$force_refresh && (time() - $existing['last_fetched']) < 3600)
	{
		// Return from DB if recently fetched.
		$data = !empty($existing['raw_data']) ? json_decode($existing['raw_data'], true) : null;
		if (!empty($data))
		{
			cache_put_data($cache_key, $data, 3600);
			return $data;
		}
	}

	// Fetch remotely with a signed GET.
	$data = activitypub_signed_fetch($url);
	if (empty($data) || !isset($data['id']))
		return false;

	// Store/update in DB.
	activitypub_store_remote_actor($data);

	// Cache.
	cache_put_data($cache_key, $data, 3600);

	return $data;
}

/**
 * Fetch a remote object by URL.
 *
 * @param string $url The object URL.
 * @return array|false Object data or false.
 */
function activitypub_fetch_remote_object($url)
{
	if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL))
		return false;

	return activitypub_signed_fetch($url);
}

/**
 * Perform a signed HTTP GET to fetch an ActivityPub resource.
 * Uses a local board actor for signing (first available).
 *
 * @param string $url The URL to fetch.
 * @return array|false Decoded JSON data or false.
 */
function activitypub_signed_fetch($url)
{
	global $sourcedir, $smcFunc;

	require_once($sourcedir . '/Subs-ActivityPub-HttpSig.php');
	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

	// Get a local actor to sign with (use first available board actor).
	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}ap_actors
		WHERE is_local = {int:local}
			AND local_type = {string:type}
			AND enabled = {int:enabled}
		LIMIT 1',
		array(
			'local' => 1,
			'type' => 'board',
			'enabled' => 1,
		)
	);

	$signing_actor = null;
	if ($smcFunc['db_num_rows']($request) > 0)
	{
		$signing_actor = $smcFunc['db_fetch_assoc']($request);
		if (!empty($signing_actor['private_key_pem']))
			$signing_actor['private_key_pem_decrypted'] = activitypub_decrypt_private_key($signing_actor['private_key_pem']);
	}
	$smcFunc['db_free_result']($request);

	// Build headers.
	$headers = array(
		'Accept: application/activity+json, application/ld+json; profile="https://www.w3.org/ns/activitystreams"',
	);

	if (!empty($signing_actor))
	{
		$sig_headers = activitypub_sign_request($url, 'GET', $signing_actor);
		foreach ($sig_headers as $name => $value)
			$headers[] = $name . ': ' . $value;
	}

	$ch = curl_init($url);
	curl_setopt_array($ch, array(
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 15,
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 5,
		CURLOPT_USERAGENT => 'SMF-ActivityPub/' . ACTIVITYPUB_VERSION . ' (+' . activitypub_base_url() . ')',
	));

	$response = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	$error = curl_error($ch);
	curl_close($ch);

	if (!empty($error))
	{
		activitypub_log_error('Fetch failed for ' . $url . ': ' . $error);
		return false;
	}

	if ($http_code !== 200)
	{
		activitypub_log_error('Fetch returned HTTP ' . $http_code . ' for ' . $url);
		return false;
	}

	// Verify content type is JSON.
	if (strpos($content_type, 'json') === false && strpos($content_type, 'activity') === false)
	{
		activitypub_log_error('Unexpected content type for ' . $url . ': ' . $content_type);
		return false;
	}

	$data = json_decode($response, true);
	if (empty($data))
	{
		activitypub_log_error('Invalid JSON from ' . $url);
		return false;
	}

	return $data;
}
