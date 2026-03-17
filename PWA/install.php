<?php

/**
 * Mobile-First PWA Shell — Installation Script
 *
 * Generates VAPID keys and sets default mod settings.
 *
 * @package smfmods:pwa-shell
 * @license MIT
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Generate VAPID key pair for Web Push using openssl.
 *
 * Returns an array with 'public' and 'private' keys in
 * uncompressed base64url format suitable for Web Push.
 */
function pwa_generate_vapid_keys(): array
{
	// Drain any stale openssl errors
	while (openssl_error_string() !== false);

	$key = pwa_generate_ec_key();

	if ($key === false)
	{
		$errors = [];
		while (($e = openssl_error_string()) !== false)
			$errors[] = $e;

		fatal_error(
			'PWA Shell: Failed to generate VAPID keys.<br><br>' .
			'OpenSSL errors: ' . (!empty($errors) ? implode('; ', $errors) : 'none reported') . '<br>' .
			'PHP version: ' . PHP_VERSION . '<br>' .
			'OpenSSL version: ' . OPENSSL_VERSION_TEXT,
			false
		);
	}

	$details = openssl_pkey_get_details($key);

	// Extract the raw x and y coordinates (each 32 bytes)
	$x = str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT);
	$y = str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);
	$d = str_pad($details['ec']['d'], 32, "\0", STR_PAD_LEFT);

	// Public key: 0x04 || x || y (uncompressed point, 65 bytes)
	$publicKey = "\x04" . $x . $y;

	// Encode to base64url (no padding)
	$publicKeyB64  = rtrim(strtr(base64_encode($publicKey), '+/', '-_'), '=');
	$privateKeyB64 = rtrim(strtr(base64_encode($d), '+/', '-_'), '=');

	// Export PEM for server-side VAPID JWT signing
	openssl_pkey_export($key, $privatePem, null, pwa_openssl_config());

	return [
		'public'      => $publicKeyB64,
		'private'     => $privateKeyB64,
		'private_pem' => $privatePem,
	];
}

/**
 * Try multiple approaches to generate an EC P-256 key.
 *
 * Windows OpenSSL often fails without an explicit config path,
 * so we try several fallbacks.
 *
 * @return OpenSSLAsymmetricKey|resource|false
 */
function pwa_generate_ec_key()
{
	$params = [
		'curve_name'       => 'prime256v1',
		'private_key_type' => OPENSSL_KEYTYPE_EC,
	];

	// Attempt 1: default config
	$key = @openssl_pkey_new($params);
	if ($key !== false)
		return $key;

	// Attempt 2: explicit config from PHP's own openssl.cnf
	$config = pwa_openssl_config();
	if (!empty($config['config']))
	{
		$key = @openssl_pkey_new($params + $config);
		if ($key !== false)
			return $key;
	}

	// Attempt 3: write a minimal openssl.cnf and use it
	$key = pwa_generate_ec_key_with_temp_config($params);
	if ($key !== false)
		return $key;

	return false;
}

/**
 * Build an openssl config array pointing to an existing openssl.cnf.
 */
function pwa_openssl_config(): array
{
	// Check common locations on Windows
	$candidates = [
		// The OPENSSL_CONF env var
		getenv('OPENSSL_CONF'),
		// PHP's bundled openssl.cnf (typical Windows PHP paths)
		PHP_BINARY ? dirname(PHP_BINARY) . '/extras/ssl/openssl.cnf' : null,
		PHP_BINARY ? dirname(PHP_BINARY) . '/extras/openssl.cnf' : null,
		// Common Windows install paths
		'C:/Program Files/Common Files/SSL/openssl.cnf',
		'C:/Program Files (x86)/Common Files/SSL/openssl.cnf',
		// XAMPP / WampServer / Laragon
		'C:/xampp/apache/conf/openssl.cnf',
		'C:/wamp64/bin/apache/apache2.4.54/conf/openssl.cnf',
	];

	foreach ($candidates as $path)
	{
		if (!empty($path) && file_exists($path))
			return ['config' => $path];
	}

	return [];
}

/**
 * Last-resort: create a temporary minimal openssl.cnf and generate the key.
 *
 * @return OpenSSLAsymmetricKey|resource|false
 */
function pwa_generate_ec_key_with_temp_config(array $params)
{
	$tmpDir = sys_get_temp_dir();
	$tmpConf = $tmpDir . '/pwa_openssl_' . getmypid() . '.cnf';

	$confContent = <<<'CNF'
# Minimal OpenSSL config for EC key generation
HOME = .
openssl_conf = openssl_init

[openssl_init]
providers = provider_sect

[provider_sect]
default = default_sect

[default_sect]
activate = 1

[req]
distinguished_name = req_distinguished_name

[req_distinguished_name]
CNF;

	if (@file_put_contents($tmpConf, $confContent) === false)
		return false;

	$params['config'] = $tmpConf;
	$key = @openssl_pkey_new($params);

	@unlink($tmpConf);

	return $key;
}

// Only generate VAPID keys if they don't already exist (re-install safe)
global $modSettings;

if (empty($modSettings['pwa_vapid_public']))
{
	$vapid = pwa_generate_vapid_keys();

	updateSettings([
		'pwa_vapid_public'      => $vapid['public'],
		'pwa_vapid_private'     => $vapid['private'],
		'pwa_vapid_private_pem' => $vapid['private_pem'],
	]);
}

// Set default settings (preserve existing values on re-install)
$defaults = [
	'pwa_enabled'       => '1',
	'pwa_push_enabled'  => '1',
	'pwa_dark_default'  => 'system',   // light | dark | system
	'pwa_accent_color'  => '#557EA0',
	'pwa_a2hs_delay'    => '2',        // Show install prompt after N visits
	'pwa_offline_msg'   => 'You appear to be offline. Please check your connection and try again.',
];

$toUpdate = [];
foreach ($defaults as $key => $value)
{
	if (!isset($modSettings[$key]))
		$toUpdate[$key] = $value;
}

if (!empty($toUpdate))
	updateSettings($toUpdate);
