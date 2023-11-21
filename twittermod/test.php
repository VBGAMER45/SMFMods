<?php
ini_set("display_errors",1);
include 'SSI.php';
require_once($sourcedir . '/tweettopics2.php');
TweetTopic("Retesting the software checking how it works",2535,17);

function TweetTopic3($subject = '', $topicID = 0, $boardId = 0)
{
	global $modSettings, $sourcedir, $scripturl;
		print_r($modSettings);
	if (empty($subject))
		return;
		
	if (empty($topicID))
		return;

	if (empty($modSettings['oauth_token']))	
		return;
		
	if (empty($modSettings['oauth_token_secret']))	
		return;		

		
	if (empty($modSettings['twitterboards']))	
		return;		
		
	if (empty($modSettings['consumer_key']))
		return;
		
	if (empty($modSettings['consumer_secret']))
		return;

	$bitLyApiKey = $modSettings['bitly_apikey'];
	$bitlyUsername = $modSettings['bitly_username'];
	
	$subject = stripslashes($subject);
	

	
			

	$boardList = explode(",",$modSettings['twitterboards']);
	
	if (!in_array($boardId,$boardList))
		return;
		
		
	$url = $scripturl . "?topic=" . $topicID;
		
	
	// Check if we need to do link shortening
	if (!empty($modSettings['bitly_apikey']) && !empty($modSettings['bitly_username']))
	{
		require_once($sourcedir . '/bitly.php');
	
		$bitly = new bitly($bitlyUsername, $bitLyApiKey);
		$url = $bitly->shorten($scripturl . "?topic=" . $topicID);
	}
			
	if (!empty($modSettings['oauth_token']) && !empty($modSettings['oauth_token_secret']))
	{
		echo 'Making connection';
		require_once($sourcedir . '/twitteroauth.php');
	
		/* Create a TwitterOauth object with consumer/user tokens. */
		$connection = new TwitterOAuth($modSettings['consumer_key'], $modSettings['consumer_secret'], $modSettings['oauth_token'], $modSettings['oauth_token_secret']);
	
		/* If method is set change API call made. Test is called by default. */
		$content = $connection->get('account/verify_credentials');
		#
		print_r($content);
		
		
	
		$connection->post('statuses/update', array('status' => $subject . " " . $url));
		
		print_r($connection);
		
		echo 'Done';
	
	}
}

?>