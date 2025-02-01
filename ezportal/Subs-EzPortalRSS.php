<?php
/*
EzPortal
by:vbgamer45
http://www.smfhacks.com
*/
if (!defined('SMF'))
	die('Hacking attempt...');

// Globals
$feedcount = 0;
$maxitemcount = 0;
$tag = '';
$tag_attrs = '';
$insideitem = false;
$depth = array();

function ezportal_startElement2($parser, $name, $attrs)
{
   global $depth;
   $depth[$parser]++;
}

function ezportal_endElement2($parser, $name)
{
   global $depth;
   $depth[$parser]--;
}

function ezportal_ShowRSSFeed($layoutID =0, $showBody = false, $numShow = 10,  $feedurl = '', $feedXML = '', $lastupdate = '',  $updatetime = 15, $newwindow = 0, $encoding ="ISO-8859-1", $reverseOrder = true)
{
	global $db_prefix, $context, $sourcedir, $feedcount, $func, $maxitemcount, $insideitem, $tag, $modSettings;


	// Check if a field expired
		$current_time = time();


		// If the feedbot time to next import has expired
		if ($current_time > $lastupdate)
		{
		
			$feeddata = ezportal_GetRSSData($feedurl);
	
			$nextupdatetime = time() +  (60 * $updatetime);
			
			db_query("UPDATE {$db_prefix}ezp_block_parameters_values as v, {$db_prefix}ezp_block_parameters AS p
			SET v.data = '$nextupdatetime'
			WHERE v.id_parameter = p.id_parameter AND p.parameter_name = 'lastupdate'  AND v.id_layout = $layoutID
			", __FILE__, __LINE__);
			
			
			$safeSQLData = addslashes($feeddata);
			
			$result = db_query("
			SELECT 
				COUNT(*) as total 
			FROM {$db_prefix}ezp_block_parameters_values as v, {$db_prefix}ezp_block_parameters AS p
			
			WHERE v.id_parameter = p.id_parameter AND p.parameter_name = 'lastupdate'  AND v.id_layout = $layoutID
			", __FILE__, __LINE__);
			$countRow = mysql_fetch_assoc($result);
			
			
			if ($countRow['total'] == 0)
			{
				
				// Find the parameter id
				$result = db_query("
				SELECT 
					p.id_parameter  
				FROM {$db_prefix}ezp_block_parameters AS p
				WHERE  p.parameter_name = 'lastupdate'
			", __FILE__, __LINE__);
				$paramRow = mysql_fetch_assoc($result);
				mysql_free_result($result);


				// Insert the record
				db_query("INSERT INTO {$db_prefix}ezp_block_parameters_values 
				(id_layout, id_parameter, data)
				VALUES ($layoutID," . $paramRow['id_parameter'] . ",'$nextupdatetime')", __FILE__, __LINE__);
				
			}

			db_query("UPDATE {$db_prefix}ezp_block_parameters_values as v, {$db_prefix}ezp_block_parameters AS p
			SET v.data = '$safeSQLData'
			WHERE v.id_parameter = p.id_parameter AND p.parameter_name  = 'rssdata'   AND v.id_layout = $layoutID
			", __FILE__, __LINE__);
			
			$result = db_query("
			SELECT 
				COUNT(*) as total 
			FROM {$db_prefix}ezp_block_parameters_values as v, {$db_prefix}ezp_block_parameters AS p
			
			WHERE v.id_parameter = p.id_parameter AND p.parameter_name = 'rssdata'  AND v.id_layout = $layoutID
			", __FILE__, __LINE__);
			$countRow = mysql_fetch_assoc($result);
			
			
			if ($countRow['total'] == 0)
			{
				
				// Find the parameter id
				$result = db_query("
				SELECT p.id_parameter  FROM {$db_prefix}ezp_block_parameters AS p
				WHERE  p.parameter_name = 'rssdata'
			", __FILE__, __LINE__);
				$paramRow = mysql_fetch_assoc($result);
				mysql_free_result($result);
				
				// Insert the record
				db_query("INSERT INTO {$db_prefix}ezp_block_parameters_values 
				(id_layout, id_parameter, data)
				VALUES ($layoutID," . $paramRow['id_parameter'] . ",'$safeSQLData')", __FILE__, __LINE__);
				
			}
			
			// lastupdate
			// rss data

			if ($feeddata != false)
			{
				// Update the Feed Data cache
				
				echo ezportal_ShowFeed($feeddata,$showBody, $numShow, $newwindow, $encoding, $reverseOrder);



			}  // End get feed data
			else 
			{
				echo ezportal_ShowFeed($feedXML,$showBody, $numShow, $newwindow, $encoding, $reverseOrder);
			}

			
			
			
			
		} // End expire check
		else 
		{

			// Show cache
			echo ezportal_ShowFeed($feedXML,$showBody, $numShow, $newwindow, $encoding, $reverseOrder);
		}

}

function ezportal_ShowFeed($feedData = '', $showBody = false, $numShow = 10, $newwindow = 0, $encoding ="ISO-8859-1", $reverseOrder = true)
{
	global $context, $feedcount, $maxitemcount, $insideitem, $tag, $modSettings, $tag_attrs;


		// Process the XML
		if (empty($encoding))
			$encoding = "ISO-8859-1";
		
					$xml_parser = xml_parser_create($encoding);
				//	xml_parser_set_option($xml_parser,XML_OPTION_TARGET_ENCODING, $encoding);
					
					$context['feeditems'] = array();
					$feedcount = 0;
					$maxitemcount = $numShow;
					$tag = '';
					$tag_attrs = '';
					
					$insideitem = false;
					$context['feeditems'][0] = array();
					$context['feeditems'][0][] = array();
					$context['feeditems'][0]['title'] = '';
					$context['feeditems'][0]['description'] = '';
					$context['feeditems'][0]['link'] = '';
					$context['feeditems'][0]['content'] = '';

					xml_set_element_handler($xml_parser, "ezportal_startElement1", "ezportal_endElement1");
					xml_set_character_data_handler($xml_parser, "ezportal_characterData1");

					if (!xml_parse($xml_parser, $feedData))
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
						
						$finalItems = array();
						foreach($context['feeditems'] as $feeditem)
						{
							if (empty($feeditem['link']) && empty($finalItem['description']) && empty($finalItem['title']))
							{
								
							}
							else
								$finalItems[] = $feeditem; 
							
						}
						
						$context['feeditems'] = $finalItems;
						

						// Loop though all the items
						if ($reverseOrder == true)
							$context['feeditems'] = array_reverse($context['feeditems']);

						for ($i = 0; $i <= ($numShow -1); $i++)
						{
							// Check feed Log
							// Generate the hash for the log
							if(!isset($context['feeditems'][$i]['title']) || !isset($context['feeditems'][$i]['description']))
								continue;


							$itemhash = md5($context['feeditems'][$i]['title'] . $context['feeditems'][$i]['description']);
							
							echo '<div style="float: left; margin-left: 0; margin-top: .5em;"><b><a href="' .  $context['feeditems'][$i]['link'] . '"'. ($newwindow == 1 ? ' target="_blank" ' : '') . '>' . $context['feeditems'][$i]['title'] . '</a></b><br /></div>';

							if ($showBody == true)
							{
								
								echo $context['feeditems'][$i]['description'];
								echo '<br /><br />';
							}

						}

					 } // End valid XML check
}


function ezportal_GetRSSData($url)
{
    try
    {
    	@ini_set('max_execution_time', '1500');
    	$url_array = parse_url($url);
    	
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
    	
			$sslhost = '';
			$port = 80;
			
			if ($url_array['scheme'] = 'https')
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
    		   
    		   $header = '';
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
    catch (Exception $e)
    {
        return false;
    }	

	// Failure return false
	return false;

}

function ezportal_startElement1($parser, $name, $attrs)
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

function ezportal_endElement1($parser, $name)
{
	global $insideitem, $tag, $feedcount, $context, $tag_attrs;

	if ($name == "ITEM"  || $name == "ENTRY")
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

function ezportal_characterData1($parser, $data)
 {
	global $insideitem, $tag,  $feedcount, $context, $maxitemcount, $tag_attrs;
	
	if (!isset($context['feeditems'][$feedcount]['title']))
	{
		$context['feeditems'][$feedcount] = array();
		$context['feeditems'][$feedcount][] = array();
		$context['feeditems'][$feedcount]['title'] = '';
		$context['feeditems'][$feedcount]['description'] = '';
		$context['feeditems'][$feedcount]['link'] = '';
	}

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
			$context['feeditems'][$feedcount]['description'] .= $data;

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
?>