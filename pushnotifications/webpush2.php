<?php
/*
Push Notifications
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

function WebPushMain()
{
	// Only admins can access Settings
	isAllowedTo('admin_forum');

	// Load the language files
	if (loadlanguage('webpush') == false)
		loadLanguage('webpush','english');

	// Load template
	loadtemplate('webpush');

	// Sub Action Array
	$subActions = array(
		'settings' => 'WebPushSettings',
		'settings2' => 'WebPushSettings2',
	);

	if (isset($_REQUEST['sa']))
		$sa = $_GET['sa'];
	else
		$sa = '';

	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		WebPushSettings();
}

function WebPushSettings()
{
	global $txt, $context, $smcFunc;
	
	// Set template
	$context['sub_template'] = 'webpush_settings';

	// Set page title
	$context['page_title'] = $txt['webpush_admin'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['webpush_admin'],
			'description' => '',
			'tabs' => array(
				'settings' => array(
					'description' => $txt['webpush_txt_settings_desc'],
				),


			),
		);

}

function WebPushSettings2()
{
	global $smcFunc;

	// Security Check
	checkSession('post');

	// Settings
	$onesignal_enabled = isset($_REQUEST['onesignal_enabled']) ? 1 : 0;	
	$onesignal_appid = $smcFunc['htmlspecialchars']($_REQUEST['onesignal_appid'],ENT_QUOTES);
	$onesignal_authkey = $smcFunc['htmlspecialchars']($_REQUEST['onesignal_authkey'],ENT_QUOTES);

	webpush_checkmanifest();

		updateSettings(
	array(
	'onesignal_enabled' => $onesignal_enabled,
	'onesignal_appid' => $onesignal_appid,
	'onesignal_authkey' => $onesignal_authkey,
     
	));
	
	

	// Redirect to the admin area
	redirectexit('action=admin;area=webpush;sa=settings');
}

function webpush_send($memberID, $fromMEMID = 0, $fromDisplayName = '',  $action = '', $url = '', $extraData = null)
{
	global $txt, $modSettings, $mbname, $smcFunc;
	
	if (empty($modSettings['onesignal_appid']) || empty($modSettings['onesignal_authkey']) || empty($modSettings['onesignal_enabled']))
		return;
		
		
	if (empty($memberID))
		return;
	
	if (!function_exists("curl_init"))
		return;
		

	// Load the language files
	if (loadlanguage('webpush') == false)
		loadLanguage('webpush','english');	
	
	$content_response = '';
	$tags = array();

	// Filters
	$tags[] = array("key" => "uid","relation" => "=","value" => $memberID);
	

	switch ($action) 
	{
		case "reply": // Forum Reply Message
			$heading_en = $fromDisplayName . $txt['webpush_act_replied'];
			$content_response = $extraData['title'];
			break;
					
		case "rated": // Awesome Post Ratings Mod
			$heading_en	 = $fromDisplayName . $txt['webpush_act_rated'] . $extraData['ratingtitle'];
					
			$msgID = (int) $extraData['msgid'];
			$dbresult = $smcFunc['db_query']('', "SELECT subject FROM {db_prefix}messages WHERE id_msg = {int:id_msg}",
			array(
				'id_msg' => $msgID
				)
			);
			$row = $smcFunc['db_fetch_assoc']($dbresult);
			$smcFunc['db_free_result']($dbresult);
	
			$content_response = $row['subject'];
			break;
					
		case "quote": // Who Quoted Mod
			$heading_en = $fromDisplayName . $txt['webpush_act_quoted'];
			$content_response = $extraData['title'];
			break;
					
		case "tag": // Mention Mod
			$heading_en = $fromDisplayName . $txt['webpush_act_tagged'] . $extraData['title'];
			$content_response = $extraData['message'];
			break;
	}


		
		$content = array("en" => $content_response);
		
		// No heading set set it to the forum message title
		if (!isset($heading_en))
			$heading_en = $mbname;
	
	
		$headings = array("en" => $heading_en); // First line of web push notification
		
		$fields = array(
			'app_id' 	=> $modSettings['onesignal_appid'],
			'tags' 		=> $tags,
			'contents' 	=> $content,
			'url' 		=> $url,
			'headings' 	=> $headings
		);
		
		$fields = json_encode($fields);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: Basic ' . $modSettings['onesignal_authkey']
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_ENCODING,  ''); //  it's enable gzip compression
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); // IPV4 only
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		
		$response = curl_exec($ch);
		curl_close($ch);
		
		
		$return["allresponses"] = $response;
		$return = json_encode($return);
		
	


}


function webpush_checkmanifest()
{
	global $boarddir, $mbname, $txt;
	
	if (file_exists($boarddir . "/manifest.json"))
	{
		$checkContents = file_get_contents($boarddir . "/manifest.json");
		
	
		if (substr_count($checkContents,"gcm_sender_id") == 0)
		{
$requires = '
  "gcm_sender_id_comment": "For OneSignal Web Push Notifications, Do Not Change ID",
  "gcm_sender_id": "482941778795",
';
	
			fatal_error($txt['webpush_manifest1'] . '<br />' . $requires);

			
		}
		
	}
	else
	{
		// create the file
$data = '{
  "short_name": "' . $mbname. '",
  "name": "' . $mbname. '",
  "gcm_sender_id_comment": "For OneSignal Web Push Notifications, Do Not Change ID",
  "gcm_sender_id": "482941778795"
}';
	

	
		file_put_contents($boarddir . "/manifest.json",$data);
		if (!file_exists($boarddir . "/manifest.json"))
		{	
			
			fatal_error($txt['webpush_manifest2'] . '<br />' . $data);
			
		}
		
	}	

}

?>