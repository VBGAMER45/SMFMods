<?php
/*
RSS Feed Poster
Version 4.2
by:vbgamer45
https://www.smfhacks.com
*/
if (!defined('SMF'))
	die('Hacking attempt...');

// Globals
$feedcount = 0;
$maxitemcount = 0;
$tag_attrs = '';
$tag = '';
$insideitem = false;
$depth = array();


function verify_rss_url($url)
{
	global $txt, $modSettings, $depth;

	// Rss Data storage
	$finalrss = '';
	$failed = true;

	if ($modSettings['rss_feedmethod'] == 'All' || $modSettings['rss_feedmethod'] == 'fopen')
	{
		$fp2 = @fopen($url, "r");
		if ($fp2)
		{
			$failed = false;
			$contents = '';
			while (!feof($fp2))
			{
			  $contents .= fread($fp2, 8192);
			}

			fclose($fp2);

			$finalrss = $contents;
		}
	}


	// Use Fsockopen
	if ($modSettings['rss_feedmethod'] == 'All' || $modSettings['rss_feedmethod'] == 'fsockopen')
	{


		if($failed == true)
		{
			
			
			
			$url_array = parse_url($url);
			
			$sslhost = '';
			$port = 80;
			
			if ($url_array['scheme'] = 'https')
			{
				$sslhost = 'ssl://';
				$port = 443;
			}
			

			

			$fp = @fsockopen($sslhost . $url_array['host'], $port, $errno, $errstr, 30);
			if (!$fp)
			{

			}
			else
			{
				$failed = false;

			   $out = "GET " . $url_array['path'] . (@$url_array['query'] != '' ? '?' . $url_array['query'] : '') . "  HTTP/1.1\r\n";
			   $out .= "Host: " . $url_array['host'] . "\r\n";
			   $out .= "Connection: Close\r\n\r\n";

			   fwrite($fp, $out);

			   $rssdata = '';
		   $header  = '';

		   // Remove stupid headers.
		   do
			{
				$header .= fgets ($fp, 128 );

		 	 } while ( strpos($header, "\r\n\r\n" ) === false );

			   while (!feof($fp))
			   {
			       $rssdata .= fgets($fp, 128);
			   }
			   fclose($fp);

			   // Get rid of the stupid header information! Wish the function did it for me.
			   @$finalrss = $rssdata;

			}
		}
	}

	// Use cURL
	if ($modSettings['rss_feedmethod'] == 'All' || $modSettings['rss_feedmethod'] == 'curl')
	{
		if($failed == true)
		{
			if(function_exists("curl_init"))
			{
				$failed = false;
				// Last but not least try cUrl
				$ch = curl_init();

				// set URL and other appropriate options
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

				// grab URL, and return output
				$output = curl_exec($ch);

				// close curl resource, and free up system resources
				curl_close($ch);
				return $output;
			}
		}
	}



	// XML Parser functions to verify the XML Feed
	if($failed == false)
	{
		$depth = array();

		$xml_parser = xml_parser_create("UTF-8");
		xml_set_element_handler($xml_parser, "startElement2", "endElement2");
		xml_set_character_data_handler($xml_parser, "characterData1");

		   if (!xml_parse($xml_parser, $finalrss)) {
		      fatal_error(sprintf($txt['feedposter_err_xmlerror'],
		                   xml_error_string(xml_get_error_code($xml_parser)),
		                   xml_get_current_line_number($xml_parser)), false);
		   }

		xml_parser_free($xml_parser);
	}
	else
	{
		// We were not able to download the feed :(
		fatal_error($txt['feedposter_err_nodownload'], false);
	}


}


function startElement2($parser, $name, $attrs)
{
   global $depth;
   if (isset($depth[$parser]))
   	@$depth[$parser]++;
}

function endElement2($parser, $name)
{
   global $depth;
   if (isset($depth[$parser]))
   	@$depth[$parser]--;
}

function UpdateRSSFeedBots()
{
	global $db_prefix, $txt, $context, $sourcedir, $tag_attrs, $feedcount, $func, $maxitemcount, $insideitem, $tag, $modSettings;


	// Load the language files
	if (loadlanguage('FeedPoster') == false)
		loadLanguage('FeedPoster', 'english');

	// First get all the enabled bots
	$context['feeds'] = array();
	$request = db_query("
			SELECT
				ID_FEED, ID_BOARD, feedurl, title, postername, updatetime, enabled, html,
				ID_MEMBER, locked, articlelink, topicprefix, numbertoimport, importevery,
				msgicon, footer, id_topic
			FROM {$db_prefix}feedbot
			WHERE enabled = 1 AND json = 0", __FILE__, __LINE__);

	while ($row = mysql_fetch_assoc($request))
	{

		$request2 = db_query("
			SELECT
				countPosts
			FROM {$db_prefix}boards
			WHERE ID_BOARD = ". $row['ID_BOARD'], __FILE__, __LINE__);
		$row2 = mysql_fetch_assoc($request2);
		$row['count_posts'] = $row2['countPosts'];


		$context['feeds'][] = $row;
	}

	mysql_free_result($request);

	// For the createPost function
	require_once($sourcedir . '/Subs-Post.php');



	// Check if a field expired
	foreach ($context['feeds'] as $key => $feed)
	{

		$current_time = time();


		// If the feedbot time to next import has expired
		if ($current_time > $feed['updatetime'])
		{

			$feeddata = GetRSSData($feed['feedurl']);



			if ($feeddata != false)
			{

				// Process the XML
					$xml_parser = xml_parser_create("UTF-8"); // xml_parser_create("ISO-8859-1");
                    
					$context['feeditems'] = array();
					$feedcount = 0;
					$maxitemcount = $feed['numbertoimport'];
					$tag = '';
					$tag_attrs = '';
					$insideitem = false;
					$context['feeditems'][0] = array();
					$context['feeditems'][0][] = array();
					$context['feeditems'][0]['title'] = '';
					$context['feeditems'][0]['description'] = '';
					$context['feeditems'][0]['link'] = '';
					$context['feeditems'][0]['content'] = '';


					xml_set_element_handler($xml_parser, "startElement1", "endElement1");
					xml_set_character_data_handler($xml_parser, "characterData1");

					if (!xml_parse($xml_parser, $feeddata))
					 {
						// Error reading xml data

					     xml_parser_free($xml_parser);


					 }
					else
					{
					   	// Data must be valid lets extra some information from it
					   	// RSS Feeds are a list of items that might contain title, description, and link


					   	// Free the xml parser memory
						xml_parser_free($xml_parser);

						$context['feeditems'] = array_reverse($context['feeditems']);

						// Loop though all the items

						$myfeedcount = 0;


						for ($i = 0; $i <= ($feedcount); $i++)
						{

							if ($myfeedcount >= $maxitemcount)
							{
								continue;
							}
							
							
							if (empty($modSettings['rss_usedescription']) && !empty($context['feeditems'][$i]['content']))
							{
								$context['feeditems'][$i]['description'] = $context['feeditems'][$i]['content'];
							}
							
							// Check feed Log
							// Generate the hash for the log
							if(!isset($context['feeditems'][$i]['title']) || !isset($context['feeditems'][$i]['description']))
								continue;

							if(empty($context['feeditems'][$i]['title']) && empty($context['feeditems'][$i]['description']))
								continue;


							$itemhash = md5($context['feeditems'][$i]['title'] . $context['feeditems'][$i]['description']);
							$request = db_query("
							SELECT
								feedtime
							FROM {$db_prefix}feedbot_log
							WHERE feedhash = '$itemhash'", __FILE__, __LINE__);

							mysql_free_result($request);

							// If no has has found that means no duplicate entry
							if (db_affected_rows() == 0)
							{
								//echo "subject: " . $context['feeditems'][$i]['title'];
								// Create the Post
								$msg_title = $func['htmlspecialchars']($context['feeditems'][$i]['title'], ENT_QUOTES);
								//echo "after subject: " . $msg_title;
								$msg_title = $func['htmltrim']($msg_title);


								$msg_body = '';

								if ($feed['html'])
								{
									//echo "body: " . $context['feeditems'][$i]['description'];
									$msg_body =  $func['htmlspecialchars']($context['feeditems'][$i]['description'], ENT_QUOTES);
									$msg_body = $func['htmltrim']($msg_body);


                                    if ($modSettings['rss_embedimages'])
                                    {
                                        $m= preg_match_all('!http://[a-z0-9\-\.\/]+\.(?:jpe?g|png|gif)!Ui' , $msg_body , $matches);

                                        if ($m) {
                                            $links=$matches[0];
                                            for ($j=0;$j<$m;$j++) {
                                                $msg_body=str_replace($links[$j],'[img]'.$links[$j].'[/img]',$msg_body);
                                            }
                                        }
                                        
                                        $m= preg_match_all('!https://[a-z0-9\-\.\/]+\.(?:jpe?g|png|gif)!Ui' , $msg_body , $matches);

                                        if ($m) {
                                            $links=$matches[0];
                                            for ($j=0;$j<$m;$j++) {
                                                $msg_body=str_replace($links[$j],'[img]'.$links[$j].'[/img]',$msg_body);
                                            }
                                        }                             
                                        
                                        
                                    }


									preparsecode($msg_body);
									$msg_body = '[html]' . $msg_body . '[/html]';

									$msg_body  .=  $func['htmlspecialchars']("\n\n" . $txt['feedposter_source'] . "[url=" . $context['feeditems'][$i]['link'] . "]" . $msg_title ."[/url]", ENT_QUOTES);

									if (!empty($feed['footer']))
										$msg_body .=  $func['htmlspecialchars']("\n\n" . $feed['footer'], ENT_QUOTES);


								}
								else
								{
									$msg_body =  $func['htmlspecialchars']($context['feeditems'][$i]['description'], ENT_QUOTES);
									$msg_body = $func['htmltrim']($msg_body);


                                    if ($modSettings['rss_embedimages'])
                                    {
                                        $m= preg_match_all('!http://[a-z0-9\-\.\/]+\.(?:jpe?g|png|gif)!Ui' , $msg_body , $matches);

                                        if ($m) {
                                            $links=$matches[0];
                                            for ($j=0;$j<$m;$j++) {
                                                $msg_body=str_replace($links[$j],'[img]'.$links[$j].'[/img]',$msg_body);
                                            }
                                        }
                                        
                                        $m= preg_match_all('!https://[a-z0-9\-\.\/]+\.(?:jpe?g|png|gif)!Ui' , $msg_body , $matches);

                                        if ($m) {
                                            $links=$matches[0];
                                            for ($j=0;$j<$m;$j++) {
                                                $msg_body=str_replace($links[$j],'[img]'.$links[$j].'[/img]',$msg_body);
                                            }
                                        }    
                                        
                                        
                                    }


									$msg_body  .=  $func['htmlspecialchars']("\n\n" . $txt['feedposter_source'] . "[url=" . $context['feeditems'][$i]['link'] . "]" . $msg_title ."[/url]", ENT_QUOTES);
									if (!empty($feed['footer']))
										$msg_body .=  $func['htmlspecialchars']("\n\n" . $feed['footer'], ENT_QUOTES);
								}


								$msg_title = addslashes($msg_title);
								$msg_body = addslashes($msg_body);


								$updatePostCount = (($feed['ID_MEMBER'] == 0) ? 0 : 1);
								if ($feed['count_posts'] == 0)
									$updatePostCount = 0;


								$msgOptions = array(
									'id' => 0,
									'subject' => $feed['topicprefix'] . $msg_title,
									'body' => '[b]' . $msg_title . "[/b]\n\n" . $msg_body,
									'icon' => $feed['msgicon'],
									'smileys_enabled' => 1,
									'attachments' => array(),
								);
								$topicOptions = array(
									'id' => $feed['id_topic'],
									'board' => $feed['ID_BOARD'],
									'poll' => null,
									'lock_mode' => $feed['locked'],
									'sticky_mode' => null,
									'mark_as_read' => false,
								);
								$posterOptions = array(
									'id' => $feed['ID_MEMBER'],
									'name' => $feed['postername'],
									'email' => '',
									'ip' => '127.0.0.1',
									'update_post_count' => $updatePostCount,
								);

								createPost($msgOptions, $topicOptions, $posterOptions);

								$topicID = 0;
								if (isset($topicOptions['id']))
									$topicID = $topicOptions['id'];

								$msgID = 0;
								if (isset($msgOptions['id']))
									$msgID = $msgOptions['id'];

								// Add Feed Log
								$fid = $feed['ID_FEED'];
								$ftime = time();

								db_query("
								INSERT INTO {$db_prefix}feedbot_log
									(ID_FEED, feedhash, feedtime, ID_TOPIC,ID_MSG)
								VALUES
									($fid,'$itemhash',$ftime,$topicID,$msgID)", __FILE__, __LINE__);

								$myfeedcount++;

								db_query("
								UPDATE {$db_prefix}feedbot
								SET total_posts = total_posts + 1
								WHERE ID_FEED = $fid
								", __FILE__, __LINE__);


							}
						}

					 } // End valid XML check



			}  // End get feed data

			// Set the RSS Feed Update time
			$updatetime = time() +  (60 * $feed['importevery']);

			db_query("
			UPDATE {$db_prefix}feedbot
			SET
				updatetime = '$updatetime'

			WHERE ID_FEED = " . $feed['ID_FEED'], __FILE__, __LINE__);


		} // End expire check


	} // End for each feed

}

function GetRSSData($url)
{
	global $modSettings;
	$url_array = parse_url($url);


	if ($modSettings['rss_feedmethod'] == 'All' || $modSettings['rss_feedmethod'] == 'fopen')
	{
		$fp2 = @fopen($url, "r");
		if ($fp2)
		{
			$contents = '';
			while (!feof($fp2))
			{
			  $contents .= fread($fp2, 8192);
			}

			fclose($fp2);

			return $contents;
		}
	}

	if ($modSettings['rss_feedmethod'] == 'All' || $modSettings['rss_feedmethod'] == 'fsockopen')
	{
	
			$sslhost = '';
			$port = 80;
			
			if ($url_array['scheme'] == 'https')
			{
				$sslhost = 'ssl://';
				$port = 443;
			}
	

		$fp = fsockopen($sslhost . $url_array['host'], $port, $errno, $errstr, 30);
		if (!$fp)
		{

		}
		else
		{


		   $out = "GET " . $url_array['path'] . (@$url_array['query'] != '' ? '?' . $url_array['query'] : '') . "  HTTP/1.1\r\n";
		   $out .= "Host: " . $url_array['host'] . "\r\n";
		   $out .= "Connection: Close\r\n\r\n";

		   fwrite($fp, $out);

		   $rssdata = '';

		   $header  = '';

		   // Remove stupid headers.
		   do
		{
			$header .= fgets ($fp, 128 );

		  } while ( strpos($header, "\r\n\r\n" ) === false );


		   while (!feof($fp))
		   {
		       $rssdata .= fgets($fp, 128);
		   }
		   fclose($fp);


		   $finalrss = $rssdata;

		   return  $finalrss;
		}
	}

	if ($modSettings['rss_feedmethod'] == 'All' || $modSettings['rss_feedmethod'] == 'curl')
	{
		if(function_exists("curl_init"))
		{
			// Last but not least try cUrl
			$ch = curl_init();

			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			// grab URL, and return output
			$output = curl_exec($ch);

			// close curl resource, and free up system resources
			curl_close($ch);

			return $output;
		}
	}


	// Failure return false
	return false;

}

function startElement1($parser, $name, $attrs)
 {
	global $insideitem, $tag, $tag_attrs;
	if ($insideitem)
	{
		$tag = $name;
		$tag_attrs =  $attrs;
	}
	elseif ($name == "ITEM"  || $name == "ENTRY")
	{
		$insideitem = true;
	}
}

function endElement1($parser, $name)
{
	global $insideitem, $tag, $feedcount, $context, $tag_attrs;

	if ($name == "ITEM" || $name == "ENTRY")
	{
		$feedcount++;
		$context['feeditems'][$feedcount] = array();
		$context['feeditems'][$feedcount][] = array();
		$context['feeditems'][$feedcount]['title'] = '';
		$context['feeditems'][$feedcount]['description'] = '';
		$context['feeditems'][$feedcount]['content'] = '';
		$context['feeditems'][$feedcount]['link'] = '';
		$tag_attrs = '';
		$insideitem = false;
	}
}

function characterData1($parser, $data)
 {
	global $insideitem, $tag,  $feedcount, $context, $maxitemcount, $tag_attrs, $modSettings;

	if ($insideitem)
 	{
		switch ($tag)
		{
			case "TITLE":
				$context['feeditems'][$feedcount]['title'] .= $data;
			break;

			case "DESCRIPTION":
				$context['feeditems'][$feedcount]['description'] .= $data;

			break;

			case "SUMMARY":
			$context['feeditems'][$feedcount]['description'] .= $data;

			break;
			case "CONTENT":
			if (empty($modSettings['rss_usedescription']))
				$context['feeditems'][$feedcount]['content'] .= $data;

			break;
			
			case "CONTENT:ENCODED":
			
			if (empty($modSettings['rss_usedescription']))
				$context['feeditems'][$feedcount]['content'] .= $data;
			break;

			case "LINK":
				$data = trim($data);
				$context['feeditems'][$feedcount]['link'] .= $data;
				IF (empty($data) && isset($tag_attrs['HREF']))
					$context['feeditems'][$feedcount]['link'] .= $tag_attrs['HREF'];
                IF (empty($data) && isset($tag_attrs['href']))
					$context['feeditems'][$feedcount]['link'] .= $tag_attrs['href'];


			break;
		}
	}
}

// disguises the curl using fake headers and a fake user agent.
function disguise_curl($url)
{
  if(!function_exists("curl_init"))
    return false;
    

  $curl = curl_init();

  // Setup headers - the same headers from Firefox version 2.0.0.6
  // below was split up because the line was too long.
  $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
  $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
  $header[] = "Cache-Control: max-age=0";
  $header[] = "Connection: keep-alive";
  $header[] = "Keep-Alive: 300";
  $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
  $header[] = "Accept-Language: en-us,en;q=0.5";
  $header[] = "Pragma: "; // browsers keep this blank.

  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla');
  curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
  curl_setopt($curl, CURLOPT_REFERER, '');
  curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
  curl_setopt($curl, CURLOPT_AUTOREFERER, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_TIMEOUT, 10);
  curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);

  $html = curl_exec($curl); // execute the curl command
  curl_close($curl); // close the connection

  return $html; // and finally, return $html
}


function UpdateJSONFeedBots()
{
	global $db_prefix, $txt, $context, $sourcedir, $tag_attrs, $feedcount, $func, $maxitemcount, $insideitem, $tag, $modSettings;


	// Load the language files
	if (loadlanguage('FeedPoster') == false)
		loadLanguage('FeedPoster', 'english');

	// First get all the enabled bots
	$context['feeds'] = array();
	$request = db_query("
			SELECT
				ID_FEED, ID_BOARD, feedurl, title, postername, updatetime, enabled, html,
				ID_MEMBER, locked, articlelink, topicprefix, numbertoimport, importevery,
				msgicon, footer, id_topic
			FROM {$db_prefix}feedbot
			WHERE enabled = 1 AND json = 1", __FILE__, __LINE__);

	while ($row = mysql_fetch_assoc($request))
	{

		$request2 = db_query("
			SELECT
				countPosts
			FROM {$db_prefix}boards
			WHERE ID_BOARD = ". $row['ID_BOARD'], __FILE__, __LINE__);
		$row2 = mysql_fetch_assoc($request2);
		$row['count_posts'] = $row2['countPosts'];


		$context['feeds'][] = $row;
	}

	mysql_free_result($request);

	// For the createPost function
	require_once($sourcedir . '/Subs-Post.php');



	// Check if a field expired
	foreach ($context['feeds'] as $key => $feed)
	{

		$current_time = time();


		// If the feedbot time to next import has expired
		if ($current_time > $feed['updatetime'])
		{

			$feeddata = disguise_curl($feed['feedurl']);

            $json_feed_object = json_decode($feeddata);
            $feedcount = 0;
            $context['feeditems'] = array();
            if (!empty($json_feed_object->entries))
            foreach ( $json_feed_object->entries as $entry )
            {
               // echo "<h2>{$entry->title}</h2>";
               // $published = date("g:i A F j, Y", strtotime($entry->published));
               // echo "<small>{$published}</small>";
                //echo "<p>{$entry->content}</p>";

                 $context['feeditems'][$feedcount]['title'] = (string) $entry->title;
			     $context['feeditems'][$feedcount]['description'] = (string) $entry->content;
			     $context['feeditems'][$feedcount]['link'] = (string) $entry->alternate;

                  $feedcount++;

            }



			if (!empty($feeddata))
			{

				// Process the XML


					$maxitemcount = $feed['numbertoimport'];

					   	// Data must be valid lets extra some information from it

						$context['feeditems'] = array_reverse($context['feeditems']);

						// Loop though all the items

						$myfeedcount = 0;


						for ($i = 0; $i < ($feedcount); $i++)
						{

							if ($myfeedcount >= $maxitemcount)
							{
								continue;
							}
							// Check feed Log
							// Generate the hash for the log
							if(!isset($context['feeditems'][$i]['title']) || !isset($context['feeditems'][$i]['description']))
								continue;

							if(empty($context['feeditems'][$i]['title']) && empty($context['feeditems'][$i]['description']))
								continue;


							$itemhash = md5($context['feeditems'][$i]['title'] . $context['feeditems'][$i]['description']);
							$request = db_query("
							SELECT
								feedtime
							FROM {$db_prefix}feedbot_log
							WHERE feedhash = '$itemhash'", __FILE__, __LINE__);

							mysql_free_result($request);

							// If no has has found that means no duplicate entry
							if (db_affected_rows() == 0)
							{
								//echo "subject: " . $context['feeditems'][$i]['title'];
								// Create the Post
								$msg_title = $func['htmlspecialchars']($context['feeditems'][$i]['title'], ENT_QUOTES);
								//echo "after subject: " . $msg_title;
								$msg_title = $func['htmltrim']($msg_title);


								$msg_body = '';

								if ($feed['html'])
								{
									//echo "body: " . $context['feeditems'][$i]['description'];
									$msg_body =  $func['htmlspecialchars']($context['feeditems'][$i]['description'], ENT_QUOTES);
									$msg_body = $func['htmltrim']($msg_body);
									preparsecode($msg_body);
									$msg_body = '[html]' . $msg_body . '[/html]';

									$msg_body  .=  $func['htmlspecialchars']("\n\n" . $txt['feedposter_source'] . "[url=" . $context['feeditems'][$i]['link'] . "]" . $msg_title ."[/url]", ENT_QUOTES);

									if (!empty($feed['footer']))
										$msg_body .=  $func['htmlspecialchars']("\n\n" . $feed['footer'], ENT_QUOTES);


								}
								else
								{
									$msg_body =  $func['htmlspecialchars']($context['feeditems'][$i]['description'], ENT_QUOTES);
									$msg_body = $func['htmltrim']($msg_body);


									$msg_body  .=  $func['htmlspecialchars']("\n\n" . $txt['feedposter_source'] . "[url=" . $context['feeditems'][$i]['link'] . "]" . $msg_title ."[/url]", ENT_QUOTES);
									if (!empty($feed['footer']))
										$msg_body .=  $func['htmlspecialchars']("\n\n" . $feed['footer'], ENT_QUOTES);
								}


								$msg_title = addslashes($msg_title);
								$msg_body = addslashes($msg_body);


								$updatePostCount = (($feed['ID_MEMBER'] == 0) ? 0 : 1);
								if ($feed['count_posts'] == 0)
									$updatePostCount = 0;


								$msgOptions = array(
									'id' => 0,
									'subject' => $feed['topicprefix'] . $msg_title,
									'body' => '[b]' . $msg_title . "[/b]\n\n" . $msg_body,
									'icon' => $feed['msgicon'],
									'smileys_enabled' => 1,
									'attachments' => array(),
								);
								$topicOptions = array(
									'id' => $row['id_topic'],
									'board' => $feed['ID_BOARD'],
									'poll' => null,
									'lock_mode' => $feed['locked'],
									'sticky_mode' => null,
									'mark_as_read' => false,
								);
								$posterOptions = array(
									'id' => $feed['ID_MEMBER'],
									'name' => $feed['postername'],
									'email' => '',
									'ip' => '127.0.0.1',
									'update_post_count' => $updatePostCount,
								);

								createPost($msgOptions, $topicOptions, $posterOptions);

								$topicID = 0;
								if (isset($topicOptions['id']))
									$topicID = $topicOptions['id'];

								$msgID = 0;
								if (isset($msgOptions['id']))
									$msgID = $msgOptions['id'];

								// Add Feed Log
								$fid = $feed['ID_FEED'];
								$ftime = time();

								db_query("
								INSERT INTO {$db_prefix}feedbot_log
									(ID_FEED, feedhash, feedtime, ID_TOPIC,ID_MSG)
								VALUES
									($fid,'$itemhash',$ftime,$topicID,$msgID)", __FILE__, __LINE__);

								$myfeedcount++;

								db_query("
								UPDATE {$db_prefix}feedbot
								SET total_posts = total_posts + 1
								WHERE ID_FEED = $fid
								", __FILE__, __LINE__);


							}
						}





			}  // End get feed data

			// Set the RSS Feed Update time
			$updatetime = time() +  (60 * $feed['importevery']);

			db_query("
			UPDATE {$db_prefix}feedbot
			SET
				updatetime = '$updatetime'

			WHERE ID_FEED = " . $feed['ID_FEED'], __FILE__, __LINE__);


		} // End expire check


	} // End for each feed

}
?>