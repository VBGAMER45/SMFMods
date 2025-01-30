<?php
/*
Auto Bluesky Embed
By: vbgamer45
https://www.smfhacks.com
*/

// I used SMF items where possible for database interactions and required includes.
global $ssi_guest_access;
$ssi_guest_access = 1;
require(dirname(__FILE__) . '/SSI.php');

global $smcFunc;
$url = trim($_GET['url']);
$qv  = $_GET['id'];


if (!empty($qv)) 
{
	check_cache($qv,$url);
} 
else 
{
	echo '{"html":"<p style=\"color: #666; border: 1px dotted #666; padding: 5px; width: 490px;\">' . $txt['autobluesky_blankid'] . '</p>"}';
}

function check_cache($blueskypost,$url)
{
	global  $smcFunc;
	

	$request = $smcFunc['db_query']('','SELECT 
		html from 
	{db_prefix}bluesky_cache 
	where postid = {string:postid}',
	array(
		'postid' => $blueskypost
		)
	);
	
	if ($smcFunc['db_num_rows']($request) == 0) 
	{
		add_cache($blueskypost,$url);
	}
	else 
	{
		while ($row = $smcFunc['db_fetch_row']($request))
			echo '{"html" : "' . $row[0] .'"}';
	}		
	$smcFunc['db_free_result']($request);
	
}

function add_cache($blueskypost,$url)
{
	global $smcFunc, $txt, $sourcedir;

	if (substr_count($url, "bsky.app") == 0)
		return;

	$api_url = "https://embed.bsky.app/oembed?url=" . $url;

	if (function_exists('curl_init'))
    {
        $curl = curl_init($api_url);
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);
    	$response = curl_exec($curl);
    	curl_close($curl);

    }
    else
    {
        require_once($sourcedir . '/Subs-Package.php');
        $response = fetch_web_data($api_url);

    }
    
	
	$json_content = json_decode($response, true);
	$json_content = preg_replace( "/\r|\n/", "", $json_content);
	if (empty($json_content['html']))
	{


			echo '{"html":"<p style=\"color: #666; border: 1px dotted #666; padding: 5px; width: 490px;\">' . $txt['autobluesky_error'] . '</p>"}';

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

		$smcFunc['db_insert']('',
				'{db_prefix}bluesky_cache',
				array(
					'postid' => 'text', 'html' => 'text'
				),
				array(
					$blueskypost, addslashes($html)
			 ),
				array('postid','html')
			);
		echo '{"html" : "' . addslashes($html) .'"}'; 
	}
	else 
	{
		echo '{"html":"<p style=\"color: #666; border: 1px dotted #666; padding: 5px; width: 490px;\">' . $txt['autobluesky_error'] . '</p>"}';
	}
	
}

?>