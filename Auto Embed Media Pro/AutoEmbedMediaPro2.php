<?php
/*
Simple Audio Video Embedder
Version 6.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/
global $modSettings;
$modSettings['mediapro_disablesig'] = 0;

function MediaProMain()
{
	global $mediaProVersion;

	// Only admins can access MediaPro Settings
	isAllowedTo('admin_forum');

	// Hold Current Version
	$mediaProVersion = '6.0.3';

	// Load the language files
	if (loadlanguage('AutoEmbedMediaPro') == false)
		loadLanguage('AutoEmbedMediaPro','english');

	// Load template
	loadtemplate('AutoEmbedMediaPro2');

	// Sub Action Array
	$subActions = array(
		'settings' => 'MediaProSettings',
		'settings2' => 'MediaProSettings2',
		'mega' => 'MegaThanks',
		'copyright' => 'MediaProCopyright',
	);

	if (isset($_REQUEST['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';

	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		MediaProSettings();
}

function MediaProCopyright()
{
    global $context, $mbname, $txt;
	isAllowedTo('admin_forum');

    if (isset($_REQUEST['save']))
    {

        $mediapro_copyrightkey = addslashes($_REQUEST['mediapro_copyrightkey']);

        updateSettings(
    	array(
    	'mediapro_copyrightkey' => $mediapro_copyrightkey,
    	)

    	);
    }

    $context[$context['admin_menu_name']]['tab_data'] = array(
			'title' =>  $txt['mediapro_admin'],
			'description' => '',
			'tabs' => array(
				'settings' => array(
					'description' => $txt['mediapro_settings'],
				),
                'copyright' => array(
					'description' => $txt['mediapro_txt_copyrightremoval'],
				),
			),
		);



	$context['page_title'] = $mbname . ' - '  . $txt['mediapro_txt_copyrightremoval'];

	$context['sub_template']  = 'mediapro_copyright';
}


function MediaProSettings()
{
	global $txt, $context, $smcFunc;

	// Query all the sites
	$context['mediapro_sites'] = array();

	$result = $smcFunc['db_query']('', "
	SELECT
		id, title, website, enabled
	FROM {db_prefix}mediapro_sites
	ORDER BY title ASC
	");
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$context['mediapro_sites'][] = $row;
	}


	// Set template
	$context['sub_template'] = 'mediapro_settings';

	// Set page title
	$context['page_title'] = $txt['mediapro_admin'];


	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' =>  $txt['mediapro_admin'],
			'description' => '',
			'tabs' => array(
				'settings' => array(
					'description' => $txt['mediapro_settings2'],
				),
				'copyright' => array(
					'description' =>$txt['mediapro_txt_copyrightremoval'],

				),



			),
		);


}

function MediaProSettings2()
{
	global $smcFunc;

	// Security Check
	checkSession('post');

	// Disable all sites
	$smcFunc['db_query']('', "
	UPDATE {db_prefix}mediapro_sites SET enabled = 0
	");

	// Check for enabled sites
	if (isset($_REQUEST['site']))
	{
		$sites = $_REQUEST['site'];
		$siteArray = array();
		foreach($sites as $site  => $key)
		{
			$site = (int) $site;
			$siteArray[] = $site;
		}

		if (count($siteArray) != 0)
		{
			$smcFunc['db_query']('', "UPDATE {db_prefix}mediapro_sites SET enabled = 1 WHERE id IN(" . implode(',',$siteArray) .")");
		}

	}


	// Write the cache
	MediaProWriteCache();

	// Settings
	$mediapro_default_height = (int) $_REQUEST['mediapro_default_height'];
	$mediapro_default_width = (int) $_REQUEST['mediapro_default_width'];
    $mediapro_disablesig = isset($_REQUEST['mediapro_disablesig']) ? 1 : 0;
    $mediapro_disablemobile = isset($_REQUEST['mediapro_disablemobile']) ? 1 : 0;
	$mediapro_usecustomdiv = isset($_REQUEST['mediapro_usecustomdiv']) ? 1 :0;
	$mediapro_divclassname = htmlspecialchars($_REQUEST['mediapro_divclassname'],ENT_QUOTES);

	$mediapro_max_embeds = (int) $_REQUEST['mediapro_max_embeds'];
	$mediapro_showlink = isset($_REQUEST['mediapro_showlink']) ? 1 : 0;

		updateSettings(
	array(
	'mediapro_default_height' => $mediapro_default_height,
	'mediapro_default_width' => $mediapro_default_width,
    'mediapro_disablesig' => $mediapro_disablesig,
    'mediapro_disablemobile' => $mediapro_disablemobile,
    'mediapro_usecustomdiv' => $mediapro_usecustomdiv,
    'mediapro_divclassname' => $mediapro_divclassname,
	'mediapro_max_embeds' => $mediapro_max_embeds,
	'mediapro_showlink' => $mediapro_showlink,
	));

	// Redirect to the admin area
	redirectexit('action=admin;area=mediapro;sa=settings');
}

function MediaProProcess($message)
{
	global $boarddir, $modSettings, $context, $user_info, $boardurl;
	static $playerCount = 0;

 	if (isset($context['save_embed_disable']) && $context['save_embed_disable'] == 1)
		return $message;

	// Don't process if a robot
	if (!empty($user_info['possibly_robot']))
		return $message;

	// If it is short don't do anything
	if (strlen($message) < 7)
		return $message;

    if (isset($_REQUEST['action']))
    {
        if ($_REQUEST['action'] == 'post' || $_REQUEST['action'] == 'post2')
            return $message;
    }

    // Max embed settings
	if (!empty($modSettings['mediapro_max_embeds']))
	{
		 if ($playerCount >= $modSettings['mediapro_max_embeds'])
		 	return $message;
	}

    // Check disable mobile
    if (!empty($modSettings['mediapro_disablemobile']))
    {
        if (MediaProisMobileDevice() == true)
            return $message;
    }


	// Load the cache file
	if (file_exists($boarddir . "/cache/mediaprocache.php"))
	{
		global $mediaProCache;
		require_once($boarddir . "/cache/mediaprocache.php");


		$mediaProItems =  unserialize($mediaProCache);


	}
	else
		$mediaProItems = MediaProWriteCache();


	$parsed_url = parse_url($boardurl);



	// Loop though main array of enabled sites to process
	if (count($mediaProItems) > 0)
	foreach($mediaProItems as $mediaSite)
	{

		if (!empty($modSettings['mediapro_default_width']))
			$movie_width = $modSettings['mediapro_default_width'];
		else
			$movie_width  = $mediaSite['width'];

		if (!empty($modSettings['mediapro_default_height']))
			$movie_height = $modSettings['mediapro_default_height'];
		else
			$movie_height = $mediaSite['height'];

			if (!empty($modSettings['mediapro_usecustomdiv']))
			{
				$mediaSite['embedcode'] = '<div class="' . $modSettings['mediapro_divclassname'] . '">' . $mediaSite['embedcode'];

				$mediaSite['embedcode'] .= '</div>';

			}

			$mediaSite['embedcode'] = str_replace('#playercount#', $playerCount, $mediaSite['embedcode']);
			$mediaSite['embedcode'] = str_replace('#parent#', $parsed_url['host'], $mediaSite['embedcode']);

			$mediaSite['embedcode'] = str_replace('width="480"','width="' . $movie_width  .'"', $mediaSite['embedcode']);
			$mediaSite['embedcode'] = str_replace('width:480','width="' . $movie_width  .'px', $mediaSite['embedcode']);
			$mediaSite['embedcode'] = str_replace('width=480','width=' . $movie_width , $mediaSite['embedcode']);
			$mediaSite['embedcode'] = str_replace('data-width="480"','data-width="' . $movie_width  .'"', $mediaSite['embedcode']);


			 $mediaSite['embedcode'] = str_replace('height="600"','height="' . $movie_height .'"', $mediaSite['embedcode']);
			 $mediaSite['embedcode'] = str_replace('height:600','height:' . $movie_height.'px', $mediaSite['embedcode']);
			 $mediaSite['embedcode'] = str_replace('height=600','height=' . $movie_height, $mediaSite['embedcode']);
			 $mediaSite['embedcode'] = str_replace('data-height="640"','data-height="' . $movie_height .'"', $mediaSite['embedcode']);
			 $mediaSite['embedcode'] = str_replace('data-height="600"','data-height="' . $movie_height .'"', $mediaSite['embedcode']);


			if (!empty($modSettings['mediapro_showlink']))
				$mediaSite['embedcode'] .= '<br />#MYLINKMEDIA#';


		$medialinks = explode("ZSPLITMZ",$mediaSite['regexmatch']);

		foreach($medialinks as $medialink)
		{


			/// Old replace call
//			$message = preg_replace('#<a href="' . $medialink . '"[^>]*>([^<]+)</a>#i', $mediaSite['embedcode'], $message,-1,$count);

			$message = preg_replace_callback('#<a href="' . $medialink . '"[^>]*>([^<]+)</a>#i', function( $matches ) use ( $mediaSite, &$playerCount)
			{
				$mediaSite['embedcode'] = str_replace("#MYLINKMEDIA#",$matches[0],$mediaSite['embedcode']);

				for ($m = 1;$m < count($matches);$m++)
				{
					$mediaSite['embedcode'] = str_replace('$' . $m,$matches[$m],$mediaSite['embedcode']);
				}

				$playerCount++;

				return $mediaSite['embedcode'];


            }

            , $message,-1);


		}


        // 2.0
		// $message = preg_replace('#<a href="' . $mediaSite['regexmatch'] . '"(.*?)</a>#i', $mediaSite['embedcode'], $message);
	}

	// Return the updated message content
	return $message;
}

function MediaProMatches($matches)
{
	print_r($matches);


	return $matches;

}

function MediaProWriteCache()
{
	global $smcFunc, $boarddir;

	$mediaProItems = array();

	// Get list of sites that are enabled
	$result = $smcFunc['db_query']('', "
	SELECT
		id, title, website, regexmatch,
		embedcode, height,  width
	FROM {db_prefix}mediapro_sites
	WHERE enabled = 1");
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$mediaProItems[] = $row;
	}

	// Data to write
	$data = '<?php
$mediaProCache = \'' . serialize($mediaProItems)  . '\';
?>';

	// Write the cache to the file
	$fp = fopen($boarddir . "/cache/mediaprocache.php", 'w');
	if ($fp)
	{
		fwrite($fp, $data);
	}

	fclose($fp);


	// Return the items in the array
	return $mediaProItems;

}

function MegaThanks()
{
	global $context, $txt;

	// Set template
	$context['sub_template'] = 'mega';

	// Set page title
	$context['page_title'] = $txt['mediapro_megauploadtometoday'];
}

function MediaProisMobileDevice()
{
	$user_agents = array(
		array('iPhone', 'iphone'),
		array('iPod', 'ipod'),
		array('iPad', 'ipad'),
		array('PocketIE', 'iemobile'),
		array('Opera Mini', isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ?  'operamini' : 'operamini'),
		array('Opera Mobile', 'Opera Mobi'),
		array('Android', 'android'),
		array('Symbian', 'symbian'),
		array('BlackBerry', 'blackberry'),
		array('BlackBerry Storm', 'blackberry05'),
		array('Palm', 'palm'),
		array('Web OS', 'webos'),
	);

	foreach ($user_agents as $ua)
	{
			$string = (string) $ua[1];

			if (!empty($string))
			if ((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), $string)))
				return true;
	}

        return false;

}

?>