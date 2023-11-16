<?php
/*
Rough take on Twitter oEmbed for VeloRooms
by L'arri : voici.l.arriviste@gmail.com
27 October 2014

Packaged and modified by SMFHacks.com -vbgamer45
*/

// I used SMF items where possible for database interactions and required includes.
global $ssi_guest_access;
$ssi_guest_access = 1;
require(dirname(__FILE__) . '/SSI.php');

global $smcFunc;
$url = trim($_GET['url']);
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_NUMBER_INT);
$qv  = $_GET['id'];


if (!empty($qv)) 
{
	check_cache($qv,$url);
} 
else 
{
	echo '{"html":"<p style=\"color: #666; border: 1px dotted #666; padding: 5px; width: 490px;\">' . $txt['autotwitter_blankid'] . '</p>"}';
}

function check_cache($tweet,$url)
{
	global  $smcFunc;
	
	if (!is_numeric($tweet))
		return;
	
	$request = $smcFunc['db_query']('','SELECT 
		html from 
	{db_prefix}tweet_cache 
	where tweetid = {raw:tweet}', 
	array(
		'tweet' => $tweet
		)
	);
	
	if ($smcFunc['db_num_rows']($request) == 0) 
	{
		add_cache($tweet,$url);
	}
	else 
	{
		while ($row = $smcFunc['db_fetch_row']($request))
			echo '{"html" : "' . $row[0] .'"}';
	}		
	$smcFunc['db_free_result']($request);
	
}

function add_cache($tweet,$url)
{
	global $smcFunc, $txt, $sourcedir;

	$url = str_replace("https://x.com","https://twitter.com",$url);

	$twitterapi_url = "https://publish.twitter.com/oembed?url=" . $url;



	if (function_exists('curl_init'))
    {
        $curl = curl_init($twitterapi_url);
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);
    	$response = curl_exec($curl);
    	curl_close($curl);

    }
    else
    {
        require_once($sourcedir . '/Subs-Package.php');
        $response = fetch_web_data($twitterapi_url);

    }
    
	
	$json_content = json_decode($response, true);
	$json_content = preg_replace( "/\r|\n/", "", $json_content);
	if (empty($json_content['html']))
	{


			echo '{"html":"<p style=\"color: #666; border: 1px dotted #666; padding: 5px; width: 490px;\">' . $txt['autotwitter_tweeterror'] . '</p>"}';

		exit;
	}
	
	$html = $json_content['html'];
	if (!empty($html)) 
	{

		$smcFunc['db_query']('', '
			SET NAMES {string:db_character_set}',
			array(
				'db_character_set' => 'utf8mb4',
			)
		);

		$request = $smcFunc['db_insert']('',
				'{db_prefix}tweet_cache',
				array(
					'tweetid' => 'raw', 'html' => 'text'
				),
				array(
					$tweet, addslashes($html)
			 ),
				array('tweetid','html')
			);
		echo '{"html" : "' . addslashes($html) .'"}'; 
	}
	else 
	{
		echo '{"html":"<p style=\"color: #666; border: 1px dotted #666; padding: 5px; width: 490px;\">' . $txt['autotwitter_tweeterror'] . '</p>"}';
	}
	
}

?>