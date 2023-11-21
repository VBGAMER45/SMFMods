<?php
/*
Tweet Topics/FB Post System
Version 2.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2010-2012 SMFHacks.com

############################################
License Information:
Tweet Topics System is NOT free software.
This software may not be redistributed.

Thelicense is good for a single instance / install on a website.
You are allowed only one active install for each license purchase.

#############################################
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function TwitterMain()
{
	isAllowedTo('admin_forum');
	
	loadtemplate('tweettopics');

	// Actions
	$subActions = array(

		'twitter' => 'TwitterThread',
		'twitter2' => 'TwitterThread2',
		'twittersignin' => 'TwitterSignIn',
		
		'fbsettings' => 'FacebookSettings',
		'fbsettings2' => 'FacebookSettings2',
	);


	// Follow the sa or just go to  the main function
	@$sa = $_GET['sa'];
	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		TwitterThread();

	
}

function TwitterThread()
{
	global $context, $db_prefix, $txt;
	
	adminIndex('twitter_settings');
	
	$context['twitter_boards'] = array();
	$request = db_query("
				SELECT
					b.ID_BOARD, b.name AS bName, c.name AS cName
				FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c
				WHERE b.ID_CAT = c.ID_CAT ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
			$context['twitter_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	mysql_free_result($request);
	
	
	
	
	$context['page_title'] = $txt['twitter_admin'] ;
	$context['sub_template']  = 'twitter';

}

function TwitterThread2()
{

	$consumer_key = htmlspecialchars($_REQUEST['consumer_key'],ENT_QUOTES);
	$consumer_secret = htmlspecialchars($_REQUEST['consumer_secret'],ENT_QUOTES);
	$twitterBoards = implode(",",$_REQUEST['twitterboards']);
	$bitly_username = $_REQUEST['bitly_username'];
	$bitly_apikey = $_REQUEST['bitly_apikey'];

	updateSettings(
	array(
	'twitterboards' => $twitterBoards,
	'consumer_key' => $consumer_key,
	'consumer_secret' => $consumer_secret,
	'bitly_username' => $bitly_username,
	'bitly_apikey' => $bitly_apikey,
	
	)
	
	
	);
	
	redirectexit('action=twitter;sa=twitter');
}


function TwitterSignIn()
{
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
    die($txt['twitter_signon_error'] . " - " . $connection->http_code);
}

}

function TweetTopic($subject = '', $topicID = 0, $boardId = 0)
{
	global $modSettings, $sourcedir, $scripturl;
	
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
		require_once($sourcedir . '/twitteroauth.php');
	try
	{
		/* Create a TwitterOauth object with consumer/user tokens. */
		$connection = new TwitterOAuth($modSettings['consumer_key'], $modSettings['consumer_secret'], $modSettings['oauth_token'], $modSettings['oauth_token_secret']);
	
		/* If method is set change API call made. Test is called by default. */
		$content = $connection->get('account/verify_credentials');
		#
	
		$connection->post('statuses/update', array('status' => $subject . " " . $url));
	
	} 
	catch (Exception $e)
	{
	
	    echo $e->getMessage();
	    log_error("TweetPost:" .$e->getMessage());
	}

		/*
TwitterOAuth Object
(
    [http_code] => 403
    [http_header] => Array
        (
            [date] => Sat, 11 Feb 2012 15:16:20 GMT
            [status] => 403 Forbidden
            [x_ratelimit_limit] => 350
            [x_frame_options] => SAMEORIGIN
            [last_modified] => Sat, 11 Feb 2012 15:16:20 GMT
            [x_ratelimit_remaining] => 338
            [x_ratelimit_reset] => 1328975827

		
		*/
		
	
	}
}



function FBPostTopic($subject = '', $topicID = 0, $boardId = 0)
{
	global $modSettings, $sourcedir, $scripturl;
	
	if (empty($subject))
		return;
		
	if (empty($topicID))
		return;

	if (empty($modSettings['facebookacesstoken']))	
		return;
	
		
	if (empty($modSettings['facebookboards']))	
		return;		
		
	if (empty($modSettings['facebookappid']))
		return;
		
	if (empty($modSettings['facebookappsecret']))
		return;


	$subject = stripslashes($subject);
	
			

	$boardList = explode(",",$modSettings['facebookboards']);
	
	if (!in_array($boardId,$boardList))
		return;
		
		
	$url = $scripturl . "?topic=" . $topicID;
		

	require_once($sourcedir . '/facebook.php');
	
	$facebook = new Facebook(array(
	  "appId"  =>  $modSettings['facebookappid'],
	  "secret" => $modSettings['facebookappsecret'],
	  "cookie" => true,
	));
	
	$acess_token = $modSettings['facebookacesstoken'];
	if (!empty($modSettings['facebookfanpageid']))
		$acess_token = $modSettings['facebookfanpageacesstoken'];
		
		
	$post = array('access_token' => $acess_token, 'message' => $subject . " " . $url);
	
	try
	{
		$type = 'me';
		
		if (!empty($modSettings['facebookfanpageid']))
			$type = $modSettings['facebookfanpageid'];
		
		$res = $facebook->api('/'. $type . '/feed','POST',$post);
		//print_r($res);
		//log_error("FBPost lOG" . print_r($res,true));
	} 
	catch (Exception $e)
	{
	
	    echo $e->getMessage();
	    log_error("FBPost:" .$e->getMessage());
	}


	

}

function FacebookSettings()
{
	global $context, $db_prefix, $txt, $sourcedir, $modSettings;
	
	adminIndex('facebook_settings');
	
	$context['facebook_boards'] = array();
	$request = db_query("
				SELECT
					b.ID_BOARD, b.name AS bName, c.name AS cName
				FROM {$db_prefix}boards AS b, {$db_prefix}categories AS c
				WHERE b.ID_CAT = c.ID_CAT ORDER BY c.catOrder, b.boardOrder", __FILE__, __LINE__);
	while ($row = mysql_fetch_assoc($request))
			$context['facebook_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	mysql_free_result($request);
	
	
	$context['page_title'] = $txt['facebook_admin'] ;
	$context['sub_template']  = 'facebook';
	
	if (!empty($modSettings['facebookacesstoken']) && !empty($modSettings['facebookappid']) && !empty($modSettings['facebookappsecret']))
	{
	   try
       {
        
		require_once($sourcedir . '/facebook.php');
		$facebook = new Facebook(array(
		  "appId"  =>  $modSettings['facebookappid'],
		  "secret" => $modSettings['facebookappsecret'],
		  "cookie" => true,
		  'access_token'=>$modSettings['facebookacesstoken']
		));
		$facebook->setAccessToken($modSettings['facebookacesstoken']);
		
		
		
		//print_R($facebook);
	    $fan_pages = array();
	    $page = array('id' => '','access_token' => '', 'name' => $txt['facebook_yourprofile']);
	    $fan_pages[] = $page;
	    
	    $temp_pages = $facebook->api('/'.$facebook->getUser().'/accounts','GET',array('access_token'=>$modSettings['facebookacesstoken']));
	    if(count($temp_pages['data']) > 0)
	    {
	        foreach($temp_pages['data'] as $page)
	        {
	        	
	            if($page["category"] != "Application")
	            {
	                $fan_pages[] = $page;
	            }
	        }
	    }
	    
	    $context['fbfanpages'] = $fan_pages;
        
        } catch (Exception $e){
	
	    echo $e->getMessage();
	    log_error("FBSettings:" .$e->getMessage());
	   }
        
        
	}
	
	
}

function FacebookSettings2()
{
	global $sourcedir, $modSettings;
	
	$facebookappid = htmlspecialchars($_REQUEST['facebookappid'],ENT_QUOTES);
	$facebookappsecret = htmlspecialchars($_REQUEST['facebookappsecret'],ENT_QUOTES);
	$facebookboards = implode(",",$_REQUEST['facebookboards']);
	
	$selectaccount =  htmlspecialchars($_REQUEST['selectaccount'],ENT_QUOTES);
	
	updateSettings(
	array(
	'facebookboards' => $facebookboards,
	'facebookappid' => $facebookappid,
	'facebookappsecret' => $facebookappsecret,
	)
	
	
	);
	
	
	
	if (empty($selectaccount))
	{
		updateSettings(
		array(
		'facebookfanpageacesstoken' => '',
		'facebookfanpageid' => '',
		)
		
		
		);
		
	}
	else 
	{
		
		if (!empty($modSettings['facebookacesstoken']) && !empty($modSettings['facebookappid']) && !empty($modSettings['facebookappsecret']))
			{
			 
             try 
             {
             
				require_once($sourcedir . '/facebook.php');
				$facebook = new Facebook(array(
				  "appId"  =>  $modSettings['facebookappid'],
				  "secret" => $modSettings['facebookappsecret'],
				  "cookie" => true,
				  'access_token'=>$modSettings['facebookacesstoken']
				));
				$facebook->setAccessToken($modSettings['facebookacesstoken']);
				
				
				
				//print_R($facebook);
			    $fan_pages = array();
			    $page = array('id' => '','access_token' => '', 'name' => '');
			    $fan_pages[] = $page;
			    
			    $temp_pages = $facebook->api('/'.$facebook->getUser().'/accounts','GET',array('access_token'=>$modSettings['facebookacesstoken']));
			    //print_R($temp_pages);
			    //die("done $selectaccount");
			    if(count($temp_pages['data']) > 0)
			    {
			        foreach($temp_pages['data'] as $page)
			        {
			        	
			            if($page["id"] == $selectaccount)
			            {
							updateSettings(
							array(
							'facebookfanpageacesstoken' => $page["access_token"],
							'facebookfanpageid' => $page["id"],
							)
							
							
							);
							//print_r($page);
							//die("done");
			            }
			        }
			    }
                
                
            } catch (Exception $e){
	
        	    echo $e->getMessage();
        	    log_error("FBSettings2:" .$e->getMessage());
        	   }  
                
			    
			}	
				
				
		
	}



	
	redirectexit('action=twitter;sa=fbsettings');
	
}


?>