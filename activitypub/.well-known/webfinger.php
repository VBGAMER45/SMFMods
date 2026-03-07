<?php
/**
 * ActivityPub Federation - Standalone WebFinger Handler
 *
 * This file handles /.well-known/webfinger requests by bootstrapping
 * SMF via SSI.php and delegating to the WebFinger handler.
 *
 * Place this file in your forum's well-known/ directory, or configure
 * your web server to rewrite /.well-known/webfinger to this file.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

// Handle CORS preflight immediately.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
{
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, OPTIONS');
	header('Access-Control-Allow-Headers: Accept');
	header('HTTP/1.1 204 No Content');
	exit;
}

// Only respond to GET.
if ($_SERVER['REQUEST_METHOD'] !== 'GET')
{
	header('HTTP/1.1 405 Method Not Allowed');
	exit;
}

// Resource parameter is required.
if (!isset($_GET['resource']) || empty($_GET['resource']))
{
	header('HTTP/1.1 400 Bad Request');
	header('Content-Type: application/json');
	echo json_encode(array('error' => 'Missing resource parameter'));
	exit;
}

// Bootstrap SMF via SSI.php.
// Adjust this path to point to your forum's SSI.php.
$ssi_path = dirname(__DIR__) . '/SSI.php';

// Try common locations if the default doesn't work.
if (!file_exists($ssi_path))
{
	// Try one more level up (if well-known/ is inside the forum root).
	$ssi_path = dirname(dirname(__DIR__)) . '/SSI.php';
}

if (!file_exists($ssi_path))
{
	header('HTTP/1.1 500 Internal Server Error');
	header('Content-Type: application/json');
	echo json_encode(array('error' => 'SSI.php not found. Check webfinger.php configuration.'));
	exit;
}

// Prevent SSI from outputting the page template.
$ssi_guest_access = true;
require_once($ssi_path);

global $sourcedir, $modSettings;

// Check if ActivityPub is enabled.
if (empty($modSettings['activitypub_enabled']))
{
	header('HTTP/1.1 404 Not Found');
	header('Content-Type: application/json');
	echo json_encode(array('error' => 'ActivityPub is not enabled'));
	exit;
}

// Load required files.
require_once($sourcedir . '/Subs-ActivityPub.php');
require_once($sourcedir . '/ActivityPub-WebFinger.php');

// Parse the resource and respond.
$resource = $_GET['resource'];
$actor_data = activitypub_parse_webfinger_resource($resource);

if (empty($actor_data))
{
	header('HTTP/1.1 404 Not Found');
	header('Content-Type: application/json');
	echo json_encode(array('error' => 'Resource not found'));
	exit;
}

// Build JRD response.
$response = array(
	'subject' => 'acct:' . $actor_data['username'] . '@' . activitypub_domain(),
	'aliases' => array(
		$actor_data['ap_id'],
		$actor_data['url'],
	),
	'links' => array(
		array(
			'rel' => 'self',
			'type' => 'application/activity+json',
			'href' => $actor_data['ap_id'],
		),
		array(
			'rel' => 'http://webfinger.net/rel/profile-page',
			'type' => 'text/html',
			'href' => $actor_data['url'],
		),
	),
);

header('Content-Type: application/jrd+json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
