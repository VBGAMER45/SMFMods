<?php
/*
Telegram Autopost
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

function telegramMain()
{
	// Only admins can access Settings
	isAllowedTo('admin_forum');

	// Load the language files
	if (loadlanguage('telegram') == false)
		loadLanguage('telegram','english');

	// Load template
	loadtemplate('telegram2');

	// Sub Action Array
	$subActions = array(
		'settings' => 'telegramSettings',
		'settings2' => 'telegramSettings2',
	);

	if (isset($_REQUEST['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';

	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		telegramSettings();
}

function telegramSettings()
{
	global $txt, $context, $smcFunc;
	
	$context['telegram_boards'] = array();
	$request = $smcFunc['db_query']('', "
				SELECT
					b.ID_BOARD, b.name AS bName, c.name AS cName
				FROM {db_prefix}boards AS b, {db_prefix}categories AS c
				WHERE b.ID_CAT = c.ID_CAT ORDER BY c.cat_order, b.board_order");
	while ($row = $smcFunc['db_fetch_assoc']($request))
			$context['telegram_boards'][$row['ID_BOARD']] = $row['cName'] . ' - ' . $row['bName'];
	$smcFunc['db_free_result']($request);
	

	// Set template
	$context['sub_template'] = 'telegram_settings';

	// Set page title
	$context['page_title'] = $txt['telegram_admin'];


	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['telegram_admin'],
			'description' => '',
			'tabs' => array(
				'settings' => array(
					'description' => $txt['telegram_txt_settings_desc'],
				),


			),
		);

}

function telegramSettings2()
{
	global $smcFunc;

	// Security Check
	checkSession('post');

	$telegram_enable_bot_auth_token = $smcFunc['htmlspecialchars']($_REQUEST['telegram_enable_bot_auth_token'],ENT_QUOTES);
	$telegram_enable_chat_id = $smcFunc['htmlspecialchars']($_REQUEST['telegram_enable_chat_id'],ENT_QUOTES);

	// Settings
	$telegram_enable_push_registration = isset($_REQUEST['telegram_enable_push_registration']) ? 1 : 0;
	$telegram_enable_push_topic = isset($_REQUEST['telegram_enable_push_topic']) ? 1 : 0;
	$telegram_enable_push_post = isset($_REQUEST['telegram_enable_push_post']) ? 1 : 0;
	
	$telegram_boardstopush = implode(",",$_REQUEST['telegram_boardstopush']);
	
	$telegram_dateformat = $smcFunc['htmlspecialchars']($_REQUEST['telegram_dateformat'],ENT_QUOTES);
	$telegram_msg_reg = $smcFunc['htmlspecialchars']($_REQUEST['telegram_msg_reg'],ENT_QUOTES);
	$telegram_msg_topic = $smcFunc['htmlspecialchars']($_REQUEST['telegram_msg_topic'],ENT_QUOTES);
	$telegram_msg_post = $smcFunc['htmlspecialchars']($_REQUEST['telegram_msg_post'],ENT_QUOTES);
	


		updateSettings(
	array(
	 'telegram_enable_bot_auth_token' => $telegram_enable_bot_auth_token,
     'telegram_enable_chat_id' => $telegram_enable_chat_id,

	'telegram_enable_push_registration' => $telegram_enable_push_registration,
	'telegram_enable_push_topic' => $telegram_enable_push_topic,
	'telegram_enable_push_post' => $telegram_enable_push_post,
	

	'telegram_boardstopush' => $telegram_boardstopush,
	
	'telegram_dateformat' => $telegram_dateformat,
	'telegram_msg_reg' => $telegram_msg_reg,
	'telegram_msg_topic' => $telegram_msg_topic,
	'telegram_msg_post' => $telegram_msg_post,
	

     
	));

	// Redirect to the admin area
	redirectexit('action=admin;area=telegram;sa=settings');
}

function telegram_sendCURL($message)
{
     global $modSettings;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,  'https://api.telegram.org/bot'.$modSettings['telegram_enable_bot_auth_token'].'/sendMessage?chat_id=' .$modSettings['telegram_enable_chat_id'] . "&text=". urlencode($message['text']));
            curl_setopt($ch, CURLOPT_POST, true);
           // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
          //  curl_setopt($ch, CURLOPT_POSTFIELDS,  http_build_query($message));
            $result = curl_exec($ch);
            // Check for errors and display the error message
            if ($errno = curl_errno($ch))
            {
                $error_message = curl_strerror($errno);
                log_error("cURL error ({$errno}):\n {$error_message}");
            }
            $json_result = json_decode($result, true);
            if (($httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE)) != 204)
            {
                if ($httpcode  != 200)
                    log_error($httpcode . ':' . $result);
            }

            if ($json_result === NULL)
            {
                log_error('JSON '.json_last_error_msg());
                return NULL;
            }
            if ($json_result['ok'] !== TRUE)
            {
                log_error($json_result['description']);
                return NULL;
            }


            curl_close($ch);
            return $result;
}

function telegram_sendSocket($message)
{
    global $modSettings, $sourcedir;


	require_once($sourcedir . '/Subs-Package.php');

	// Get the html and parse it for the openid variable which will tell us where to go.
	$webdata = fetch_web_data('https://api.telegram.org/bot' . $modSettings['telegram_enable_bot_auth_token'] . '/sendMessage?chat_id=' . $modSettings['telegram_enable_chat_id'] . "&text=" . urlencode($message['text']));

	if (empty($webdata))
	{


		$parsed = parse_url('https://api.telegram.org/bot' . $modSettings['telegram_enable_bot_auth_token'] . '/sendMessage?chat_id=' . $modSettings['telegram_enable_chat_id'] . "&text=" . urlencode($message['text']));
		$result = '';
		if ($f = fsockopen((($parsed['scheme'] == 'https') ? 'ssl://' : '') . $parsed['host'], (($parsed['scheme'] == 'https') ? 443 : 80), $errno, $errmsg, 30))
		{
			$out = "POST " . $parsed['path'] . " HTTP/1.0\r\n";
			$out .= "Host: " . $parsed['host'] . "\r\n";
			//     $out .= "Content-Length: " . strlen($message['text']) . "\r\n\r\n";
			fwrite($f, $out);
			while (!feof($f))
			{
				$result .= fread($f, 4096);
			}
			fclose($f);
		}
		return $result;


	}
}

function telegram_send($message)
{
    global $modSettings;

    if (empty($modSettings['telegram_enable_chat_id']))
            return;

    if (empty($modSettings['telegram_enable_bot_auth_token']))
            return;


            $push = array(
			'chat_id' => $modSettings['telegram_enable_chat_id'],
			'disable_web_page_preview' => 'true',
			'parse_mode' => 'HTML',
			'text' => $message,
		);


           if (!function_exists('curl_init')) 
                telegram_sendSocket($push);
           else
		   		telegram_sendCURL($push);
           
}

function telegram_send_topic($messageid)
{
	global $modSettings, $smcFunc, $scripturl, $txt;
	
	if (empty($modSettings['telegram_enable_push_topic']))
		return;
		
	if (empty($messageid))
		return;	
			
	$message = $modSettings['telegram_msg_topic'];
	
	$t = time();
	$message = str_replace("(date)",date($modSettings['telegram_dateformat'],$t),$message);
	
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
		if (loadlanguage('telegram') == false)
			loadLanguage('telegram','english');
			
		$username = $txt['telegram_guest'];
	}	
		
		
		
	$message = str_replace("(username)",$username,$message);
	$message = str_replace("(title)",$row['subject'],$message);
	$message = str_replace("(board)",$row['name'],$message);
	$message = str_replace("(url)",$scripturl . '?topic=' . $row['id_topic'] . '.msg' . $messageid . '#msg' . $messageid,$message);
	$message = str_replace("(can_url)",$scripturl . '?topic=' . $row['id_topic'] . '.0',$message); // this one allows canonical url...
	$message = html_entity_decode($message, ENT_QUOTES | ENT_XML1, 'UTF-8');
	
	// Check if this is in a board that is allowed to post
	$boardlist = explode(",",$modSettings['telegram_boardstopush']);
	
	if (!in_array($row['id_board'],$boardlist))
		return;	


	telegram_send($message);
	
}

function telegram_send_post($messageid)
{
	global $modSettings, $smcFunc, $scripturl, $txt;
	
	if (empty($modSettings['telegram_enable_push_post']))
		return;
		
		
	if (empty($messageid))
		return;	
		
	$message = $modSettings['telegram_msg_post'];
	
	$t = time();
	$message = str_replace("(date)",date($modSettings['telegram_dateformat'],$t),$message);
	
	
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
		if (loadlanguage('telegram') == false)
			loadLanguage('telegram','english');
			
		$username = $txt['telegram_guest'];
	}	
		
	$message = str_replace("(username)",$username,$message);
	$message = str_replace("(title)",$row['subject'],$message);
	$message = str_replace("(board)",$row['name'],$message);
	$message = str_replace("(url)",$scripturl . '?topic=' . $row['id_topic'] . '.msg' . $messageid . '#msg' . $messageid,$message);
	$message = str_replace("(can_url)",$scripturl . '?topic=' . $row['id_topic'] . '.0',$message); // this one allows canonical url...
	$message = html_entity_decode($message, ENT_QUOTES | ENT_XML1, 'UTF-8');

	// Check if this is in a board that is allowed to post
	$boardlist = explode(",",$modSettings['telegram_boardstopush']);
	
	if (!in_array($row['id_board'],$boardlist))
		return;	

	telegram_send($message);
	
	
}

function telegram_send_new_member_registration($memberID)
{
	global $modSettings, $smcFunc;
	
	if (empty($modSettings['telegram_enable_push_registration']))
		return;


	if (empty($memberID))
		return;		
		
	$message = $modSettings['telegram_msg_reg'];
	
	$t = time();
	$message = str_replace("(date)",date($modSettings['telegram_dateformat'],$t),$message);
	
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


	
	telegram_send($message);
	
}

?>