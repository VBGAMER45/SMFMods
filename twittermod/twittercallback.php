<?php
/*
Tweet Topics System
Version 1.0
by:vbgamer45

*/
global $ssi_guest_access;
$ssi_guest_access = 1;
require 'SSI.php';
global $sourcedir;
require_once($sourcedir . '/twitteroauth.php');
if (!isset($_REQUEST['action']))
{
	/* If the oauth_token is old redirect to the connect page. */
	if (isset($_REQUEST['oauth_token']) && $modSettings['oauth_token'] !== $_REQUEST['oauth_token'])
	{
	  die("No Twitter information passed");
	}

	/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
	$connection = new TwitterOAuth($modSettings['consumer_key'], $modSettings['consumer_secret'], $modSettings['oauth_token'], $modSettings['oauth_token_secret']);

	/* Request access tokens from twitter */
	$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);


	/* If HTTP response is 200 continue otherwise send to connect page to retry */
	if (200 == $connection->http_code)
	{
	  /* The user has been verified and the access tokens can be saved for future use */
	   updateSettings(array('oauth_token' => $access_token['oauth_token'], 'oauth_token_secret' => $access_token['oauth_token_secret']));



	  $modSettings['status'] = 'verified';
	  header('Location: ' . $scripturl .'?action=twitter');
	} else
	 {

		updateSettings(array('oauth_token' => '', 'oauth_token_secret' => ''));

	  die("Erorr occured please retry");
	}

}
else
{

	// Get Twitter sign stuff


		global $sourcedir, $boardurl, $txt, $modSettings;
		require_once($sourcedir . '/twitteroauth.php');

		/* Build TwitterOAuth object with client credentials. */
		$connection = new TwitterOAuth($modSettings['consumer_key'], $modSettings['consumer_secret']);

		/* Get temporary credentials. */
		$request_token = $connection->getRequestToken($boardurl . '/twittercallback.php');

		/* Save temporary credentials to session. */
		$token = $request_token['oauth_token'];

	/* If last connection failed don't display authorization link. */
	switch ($connection->http_code) {
	  case 200:
	    /* Build authorize URL and redirect user to Twitter. */
	    updateSettings(array('oauth_token' => $token, 'oauth_token_secret' => $request_token['oauth_token_secret']));

	    $url = $connection->getAuthorizeURL($token);
	    header('Location: ' . $url);
	    exit;
	    break;
	  default:
	  	  updateSettings(array('oauth_token' => '', 'oauth_token_secret' => ''));

	    /* Show notification if something went wrong. */
	    die($txt['twitter_signon_error']);
	}
}

?>