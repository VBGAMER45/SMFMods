<?php
/*
Discord Web Hooks
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

function DiscordMain()
{
	// Only admins can access Settings
	isAllowedTo('admin_forum');

	// Load the language files
	if (loadlanguage('discord') == false)
		loadLanguage('discord','english');

	// Load template
	loadtemplate('discord2');

	// Sub Action Array
	$subActions = array(
		'settings' => 'DiscordSettings',
		'settings2' => 'DiscordSettings2',
		'addhook' => 'DiscordAddHook',
		'addhook2' => 'DiscordAddHook2',
		'deletehook' => 'DiscordDeleteHook',
	);

	if (isset($_REQUEST['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';

	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		DiscordSettings();
}

function DiscordSettings()
{
	global $txt, $context, $smcFunc;
	
	$context['discord_boards'] = array();
	$request = $smcFunc['db_query']('', "
				SELECT
					b.ID_BOARD, b.name AS bName, c.name AS cName
				FROM {db_prefix}boards AS b, {db_prefix}categories AS c
				WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
	while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['discord_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	$smcFunc['db_free_result']($request);


	$context['discord_hooks'] = array();
	$request = $smcFunc['db_query']('', "
				SELECT
					b.ID_BOARD, b.name AS bName, h.push_type, h.url, h.id
				FROM {db_prefix}discord_hook_boards as h, {db_prefix}boards AS b WHERE b.id_board = h.id_board");
	while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['discord_hooks'][] = $row;
	$smcFunc['db_free_result']($request);




	// Set template
	$context['sub_template'] = 'discord_settings';

	// Set page title
	$context['page_title'] = $txt['discord_admin'];


	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['discord_admin'],
			'description' => '',
			'tabs' => array(
				'settings' => array(
					'description' => $txt['discord_txt_settings_desc'],
				),


			),
		);

}

function DiscordSettings2()
{
	global $smcFunc;

	// Security Check
	checkSession('post');

	// Settings
	$discord_enable_push_registration = isset($_REQUEST['discord_enable_push_registration']) ? 1 : 0;
	$discord_enable_push_topic = isset($_REQUEST['discord_enable_push_topic']) ? 1 : 0;
	$discord_enable_push_post = isset($_REQUEST['discord_enable_push_post']) ? 1 : 0;
	
	$discord_webhook_url = $smcFunc['htmlspecialchars']($_REQUEST['discord_webhook_url'],ENT_QUOTES);
	$discord_webhook_topic_url = $smcFunc['htmlspecialchars']($_REQUEST['discord_webhook_topic_url'],ENT_QUOTES);
	$discord_webhook_post_url = $smcFunc['htmlspecialchars']($_REQUEST['discord_webhook_post_url'],ENT_QUOTES);

	if (!empty($_REQUEST['discord_boardstopush']))
	$discord_boardstopush = implode(",",$_REQUEST['discord_boardstopush']);
	else
		$discord_boardstopush = '';
	
	$discord_dateformat = $smcFunc['htmlspecialchars']($_REQUEST['discord_dateformat'],ENT_QUOTES);
	$discord_msg_reg = $smcFunc['htmlspecialchars']($_REQUEST['discord_msg_reg'],ENT_QUOTES);
	$discord_msg_topic = $smcFunc['htmlspecialchars']($_REQUEST['discord_msg_topic'],ENT_QUOTES);
	$discord_msg_post = $smcFunc['htmlspecialchars']($_REQUEST['discord_msg_post'],ENT_QUOTES);
	
	$discord_botname_reg = $smcFunc['htmlspecialchars']($_REQUEST['discord_botname_reg'],ENT_QUOTES);
	$discord_botname_topic = $smcFunc['htmlspecialchars']($_REQUEST['discord_botname_topic'],ENT_QUOTES);
	$discord_botname_post = $smcFunc['htmlspecialchars']($_REQUEST['discord_botname_post'],ENT_QUOTES);

		updateSettings(
	array(
	'discord_enable_push_registration' => $discord_enable_push_registration,
	'discord_enable_push_topic' => $discord_enable_push_topic,
	'discord_enable_push_post' => $discord_enable_push_post,
	
	'discord_webhook_url' => $discord_webhook_url,
	'discord_webhook_topic_url' => $discord_webhook_topic_url, 
	'discord_webhook_post_url' => $discord_webhook_post_url,
	
	'discord_boardstopush' => $discord_boardstopush,
	
	'discord_dateformat' => $discord_dateformat,
	'discord_msg_reg' => $discord_msg_reg,
	'discord_msg_topic' => $discord_msg_topic,
	'discord_msg_post' => $discord_msg_post,
	
	'discord_botname_reg' => $discord_botname_reg,
	'discord_botname_topic' => $discord_botname_topic,
	'discord_botname_post' => $discord_botname_post,
     
	));

	// Redirect to the admin area
	redirectexit('action=admin;area=discord;sa=settings');
}

function discord_sendCURL($message, $webhook) 
{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $webhook);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
            $result = curl_exec($ch);
            // Check for errors and display the error message
            if ($errno = curl_errno($ch)) {
                $error_message = curl_strerror($errno);
                log_error("cURL error ({$errno}):\n {$error_message}");
            }
            $json_result = json_decode($result, true);
            if (($httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE)) != 204) {
                log_error($httpcode . ':' . $result);
            }
            curl_close($ch);
            return $result;
}

function discord_sendSocket($message, $webhook) 
{
	$parsed = parse_url($webhook);
            $result = '';
            if ($f = fsockopen((($parsed['scheme'] == 'https') ? 'ssl://' : '') . $parsed['host'], (($parsed['scheme'] == 'https') ? 443 : 80), $errno, $errmsg, 30)) {
                $out = "POST " . $parsed['path'] . " HTTP/1.0\r\n";
                $out .= "Host: " . $parsed['host'] . "\r\n";
                $out .= "Content-Type: application/json\r\n";
                $out .= "Content-Length: " . strlen($message) . "\r\n\r\n";
                $out .= $message;
                fwrite($f, $out);
                while (!feof($f)) {
                    $result .= fread($f, 4096);
                }
                fclose($f);
            }
            return $result;
}

function discord_send($endpoint, $username, $message, $avatar = null, $embeds = null, $tts = false) 
{
	global $modSettings, $scripturl, $sourcedir;
		// Prettify any URLs
	if (!empty($modSettings['pretty_enable_filters']))
	{
		require_once($sourcedir . '/PrettyUrls-Filters.php');
		$context['pretty']['search_patterns'][] = '`(\s)(' . $scripturl . '[^#\s]*)`';
		$context['pretty']['replace_patterns'][] = '`(\s)(' . $scripturl . '[^\s]*)`';
		$message = pretty_rewrite_buffer($message);
	}
	
	
            $push = json_encode(array(
                'username' => $username,
                'avatar_url' => $avatar,
                'content' => $message,
                'embeds' => $embeds,
                'tts' => $tts,
            ), JSON_NUMERIC_CHECK);
            
           if (!function_exists('curl_init')) 
                discord_sendSocket($push, $endpoint);
           else
		   		discord_sendCURL($push, $endpoint);
           
}

function discord_send_topic($messageid)
{
	global $modSettings, $smcFunc, $scripturl, $txt;
	
	if (empty($modSettings['discord_enable_push_topic']))
		return;
		
	if (empty($messageid))
		return;	
			
	$message = $modSettings['discord_msg_topic'];	
	
	$t = time();
	$message = str_replace("(date)",date($modSettings['discord_dateformat'],$t),$message);
	
	$request = $smcFunc['db_query']('', "
				SELECT
					mem.real_name, m.subject, b.name, m.id_topic, m.id_board 
				FROM {db_prefix}messages as m
				LEFT JOIN {db_prefix}members as mem ON (m.id_member = mem.id_member) 
				LEFT JOIN {db_prefix}boards as b ON (b.id_board = m.id_board) 
				WHERE m.id_msg = $messageid");
	$row = $smcFunc['db_fetch_assoc']($request);
	$username = $row['real_name'];
	
	if (empty($row['real_name']))
	{
		// Load the language files
		if (loadlanguage('discord') == false)
			loadLanguage('discord','english');
			
		$username = $txt['discord_guest'];
	}	
		
		
		
	$message = str_replace("(username)",$username,$message);
	$message = str_replace("(title)",$row['subject'],$message);
	$message = str_replace("(board)",$row['name'],$message);
	$message = str_replace("(url)",$scripturl . '?topic=' . $row['id_topic'] . '.msg' . $messageid . '#msg' . $messageid,$message);
	$message = str_replace("(can_url)",$scripturl . '?topic=' . $row['id_topic'] . '.0',$message); // this one allows canonical url...
	$message = html_entity_decode($message, ENT_QUOTES | ENT_XML1, 'UTF-8');
	
	// Check if this is in a board that is allowed to post
	$boardlist = explode(",",$modSettings['discord_boardstopush']);
	
	if (!in_array($row['id_board'],$boardlist))
		return;	
	
	
	if (!empty($modSettings['discord_botname_topic']))
		$username = $modSettings['discord_botname_topic'];
	
	
	$endpoint = $modSettings['discord_webhook_url'];
	
	if (!empty($modSettings['discord_webhook_topic_url']))
		$endpoint = $modSettings['discord_webhook_topic_url'];


	// 1 send post 2 topic 3 both
	$request = $smcFunc['db_query']('', "
				SELECT
					id_board, url
				FROM {db_prefix}discord_hook_boards WHERE push_type in (2,3) AND id_board = " . $row['id_board']);
	$row = $smcFunc['db_fetch_assoc']($request);
	if (!empty($row['url']))
		$endpoint = $row['url'];

	if (empty($endpoint))
		return;


	discord_send($endpoint,$username,$message);
	
}

function discord_send_post($messageid)
{
	global $modSettings, $smcFunc, $scripturl, $txt;
	
	if (empty($modSettings['discord_enable_push_post']))
		return;
		
		
	if (empty($messageid))
		return;	
		
	$message = $modSettings['discord_msg_post'];
	
	$t = time();
	$message = str_replace("(date)",date($modSettings['discord_dateformat'],$t),$message);	
	
	
	$request = $smcFunc['db_query']('', "
				SELECT
					mem.real_name, m.subject, b.name, m.id_topic, m.id_board   
				FROM {db_prefix}messages as m
				LEFT JOIN {db_prefix}members as mem ON (m.id_member = mem.id_member) 
				LEFT JOIN {db_prefix}boards as b ON (b.id_board = m.id_board) 
				WHERE m.id_msg = $messageid");
	$row = $smcFunc['db_fetch_assoc']($request);
	$username = $row['real_name'];
	
	if (empty($row['real_name']))
	{
		// Load the language files
		if (loadlanguage('discord') == false)
			loadLanguage('discord','english');
			
		$username = $txt['discord_guest'];
	}	
		
	$message = str_replace("(username)",$username,$message);
	$message = str_replace("(title)",$row['subject'],$message);
	$message = str_replace("(board)",$row['name'],$message);
	$message = str_replace("(url)",$scripturl . '?topic=' . $row['id_topic'] . '.msg' . $messageid . '#msg' . $messageid,$message);
	$message = str_replace("(can_url)",$scripturl . '?topic=' . $row['id_topic'] . '.0',$message); // this one allows canonical url...
	$message = html_entity_decode($message, ENT_QUOTES | ENT_XML1, 'UTF-8');

	// Check if this is in a board that is allowed to post
	$boardlist = explode(",",$modSettings['discord_boardstopush']);
	
	if (!in_array($row['id_board'],$boardlist))
		return;	
	
	
	if (!empty($modSettings['discord_botname_post']))
		$username = $modSettings['discord_botname_post'];
	
	
	$endpoint = $modSettings['discord_webhook_url'];
	
	if (!empty($modSettings['discord_webhook_post_url']))
		$endpoint = $modSettings['discord_webhook_post_url'];


	// 1 send post 2 topic 3 both
	$request = $smcFunc['db_query']('', "
				SELECT
					id_board, url
				FROM {db_prefix}discord_hook_boards WHERE push_type in (1,3) AND id_board = " . $row['id_board']);
	$row = $smcFunc['db_fetch_assoc']($request);
	if (!empty($row['url']))
		$endpoint = $row['url'];

	
	if (empty($endpoint))
		return;
		
	discord_send($endpoint,$username,$message);
	
	
}

function discord_send_new_member_registration($memberID)
{
	global $modSettings, $smcFunc;
	
	if (empty($modSettings['discord_enable_push_registration']))
		return;

	if (empty($modSettings['discord_webhook_url']))
		return;

	if (empty($memberID))
		return;		
		
	$message = $modSettings['discord_msg_reg'];
	
	$t = time();
	$message = str_replace("(date)",date($modSettings['discord_dateformat'],$t),$message);
	
	// lookup member id
	$request = $smcFunc['db_query']('', "
				SELECT
					real_name
				FROM {db_prefix}members
				WHERE id_member = $memberID");
	$row = $smcFunc['db_fetch_assoc']($request);
	$username = $row['real_name'];
	
	
	$message = str_replace("(username)",$username,$message);
	$message = html_entity_decode($message, ENT_QUOTES | ENT_XML1, 'UTF-8');

	if (!empty($modSettings['discord_botname_reg']))
		$username = $modSettings['discord_botname_reg'];
	
	discord_send($modSettings['discord_webhook_url'],$username,$message);
	
}

function DiscordAddHook()
{
	global $txt, $context,  $smcFunc;

	$context['discord_boards'] = array();
	$request = $smcFunc['db_query']('', "
				SELECT
					b.ID_BOARD, b.name AS bName, c.name AS cName
				FROM {db_prefix}boards AS b, {db_prefix}categories AS c
				WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
	while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['discord_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	$smcFunc['db_free_result']($request);


	// Set template
	$context['sub_template'] = 'discord_add_hook';

	// Set page title
	$context['page_title'] = $txt['discord_add_board_level'];
}

function DiscordAddHook2()
{
	global  $smcFunc;

	$pushtype = (int) $_REQUEST['pushtype'];
	$boardhook = (int) $_REQUEST['boardhook'];

	$url =  $smcFunc['htmlspecialchars']($_REQUEST['url'],ENT_QUOTES);

	if (!empty($url) && !empty($boardhook))
	{

		$smcFunc['db_query']('', "INSERT INTO {db_prefix}discord_hook_boards (id_board, url, push_type)
VALUES ('$boardhook','$url','$pushtype') ");
	}

	// Redirect to the admin area
	redirectexit('action=admin;area=discord;sa=settings');

}

function DiscordDeleteHook()
{
	global  $smcFunc;

	$id = (int) $_REQUEST['id'];

	$smcFunc['db_query']('', "DELETE FROM {db_prefix}discord_hook_boards WHERE id = $id");

	// Redirect to the admin area
	redirectexit('action=admin;area=discord;sa=settings');

}


?>