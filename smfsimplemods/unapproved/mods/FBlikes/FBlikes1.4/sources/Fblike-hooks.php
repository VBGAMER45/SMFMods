<?php
/*---------------------------------------------------------------------------------
*	Facebook Likes Hide															  *
*	Author: SSimple Team - 4KSTORE										          *
*	Powered by www.smfsimple.com												  *
***********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

//---Hooks Part....
function Fblike_actions(&$actionArray) //Action!
{
	$actionArray['fblike'] = array('fblikesAjax.php', 'fblikesMain');
}
function Fblike_load_theme()
{
	global $modSettings, $context, $settings, $sourcedir, $scripturl;
	
	$topic_id = !empty($_REQUEST['topic']) ? (int) $_REQUEST['topic'] : '';	
	if(!empty($modSettings['fblike_enable']) && $topic_id != '')
	{	
		//Checking some values..
		$timeleft = !empty($modSettings['fblike_count']) ? (int) $modSettings['fblike_count'] : '';			
		$dataFb = array(
			"sourcedir" => $scripturl, 
			"topic" => $topic_id, 
			"sesid" => $context['session_id'],  
			"sesvar" => $context['session_var'],
			"tiemporesta" => $timeleft,			
		);
		$dataFb = json_encode($dataFb);	
		$context['html_headers'] .= '
		<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url']. '/css/fblike.css" />
		<script type="text/javascript" src="'. $settings['default_theme_url']. '/scripts/fblikes.js"></script>
		
		<script type="text/javascript">
			 if (typeof jQuery == \'undefined\')
			 { 
				document.write("<script src=\https://code.jquery.com/jquery-1.7.2.js\><\/script>");
			 }		
		</script>
		<script type="text/javascript">
			window.fbAsyncInit = function() {
			FB.init({
				status     : true, // check login status
				cookie     : true, // enable cookies to allow the server to access the session
				oauth      : true, // enable OAuth 2.0
				xfbml      : true,  // parse XFBML
			});
			
			FB.Event.subscribe("edge.create", function(href, widget) {
				fblikejs('.$dataFb.'); //call ti fblikes.js for ajax part...
			});			 
				FB.XFBML.parse();			   
			};	 
			// Load the SDK Asynchronously
			(function(d){
			   var js, id = \'facebook-jssdk\'; if (d.getElementById(id)) {return;}
			   js = d.createElement(\'script\'); js.id = id; js.async = true;
			   js.src = \'//connect.facebook.net/'.$modSettings['fblike_button_lang'].'/all.js\';
			   d.getElementsByTagName(\'head\')[0].appendChild(js);
			}(document));
		</script>';
	}	
}

function Fblike_add_code(&$codes) //Add the function
{

	global $modSettings;
	if(!empty($modSettings['fblike_enable']))
	{
		$codes[] = array(
			'tag' => 'fblike',
			'type' => 'unparsed_content',
			'content' => '$1',
			'validate' => function (&$tag, &$data, $disabled)
			{
				$data = fblike($data);
			},
		);
	}
}

function Fblike_add_button(&$buttons)
{
	global $txt, $modSettings;
	$topic_id = !empty($_REQUEST['topic']) ? (int)$_REQUEST['topic'] : '';
	$board_id = !empty($_REQUEST['board']) ? (int)$_REQUEST['board'] : '';
	if(!empty($modSettings['fblike_enable']) && (!empty($board_id)) || (!empty($topic_id)))
	{
		loadLanguage('Fblike');	
		$buttons[count($buttons) - 1][] = array(
			'image' => 'fblike',
			'code' => 'fblike',
			'before' => '[fblike]',
			'after' => '[/fblike]',
			'description' => $txt['fblike_bbc']
		);
	}
}

function Fblike_Admin(&$areas)
{
	global $txt;
	loadLanguage('Fblike');
	$areas['config']['areas']['modsettings']['subsections']['Fblike'] = array($txt['fblike_admin_title']);
}

function Fblike_Admin_Settings(&$sub_actions)
{
	global $context;
	$sub_actions['Fblike'] = 'Fblike_Settings';
}

function Fblike_Settings(&$return_config = false)
{
	global $context, $txt, $scripturl;
	//$modSettings['fblike_enable']
	$context['page_title'] = $txt['fblike_admin_title'];
	$context['settings_title'] = $txt['fblike_admin_title'];
	$context['settings_message'] = $txt['fblike_admin_title_desc'];
	$config_vars = array(
		array('check', 'fblike_enable'),
		array('check', 'fblike_guest_can'),
		array('check', 'fblike_message_unhide'),
		array('int', 'fblike_count', 'postinput' => $txt['fblike_count_seconds'], 3),
		array('select', 'fblike_button_lang', array('en_US' => 'English (US)', 'af_ZA' => 'Afrikaans', 'sq_AL' => 'Albanian', 'ar_AR' => 'Arabic', 'hy_AM' => 'Armenian', 'az_AZ' => 'Azerbaijani', 'eu_ES' => 'Basque', 'be_BY' => 'Belarusian', 'bg_BG' => 'Bulgarian', 'bn_IN' => 'Bengali', 'bs_BA' => 'Bosnian', 'ca_ES' => 'Catalan', 'hr_HR' => 'Croatian', 'cs_CZ' => 'Czech', 'da_DK' => 'Danish', 'de_DE' => 'German', 'el_GR' => 'Greek', 'en_GB' => 'English (UK)', 'en_PI' => 'English (Pirate)', 'en_UD' => 'English (Upside Down)', 'eo_EO' => 'Esperanto', 'es_ES' => 'Spanish (Spain)', 'es_LA' => 'Spanish', 'et_EE' => 'Estonian', 'fa_IR' => 'Persian', 'fb_LT' => 'Leet Speak', 'fi_FI' => 'Finnish', 'fo_FO' => 'Faroese', 'fr_CA' => 'French (Canada)', 'fr_FR' => 'French (France)', 'fy_NL' => 'Frisian', 'ga_IE' => 'Irish', 'gl_ES' => 'Galician', 'he_IL' => 'Hebrew', 'hi_IN' => 'Hindi', 'hu_HU' => 'Hungarian', 'id_ID' => 'Indonesian', 'is_IS' => 'Icelandic', 'it_IT' => 'Italian', 'ja_JP' => 'Japanese', 'ka_GE' => 'Georgian', 'km_KH' => 'Khmer', 'ko_KR' => 'Korean', 'ku_TR' => 'Kurdish', 'la_VA' => 'Latin', 'lt_LT' => 'Lithuanian', 'lv_LV' => 'Latvian', 'mk_MK' => 'Macedonian', 'ml_IN' => 'Malayalam', 'ms_MY' => 'Malay', 'nb_NO' => 'Norwegian (bokmal)', 'ne_NP' => 'Nepali', 'nl_NL' => 'Dutch', 'nn_NO' => 'Norwegian (nynorsk)', 'pa_IN' => 'Punjabi', 'pl_PL' => 'Polish', 'ps_AF' => 'Pashto', 'pt_BR' => 'Portuguese (Brazil)', 'pt_PT' => 'Portuguese (Portugal)', 'ro_RO' => 'Romanian', 'ru_RU' => 'Russian', 'sk_SK' => 'Slovak', 'sl_SI' => 'Slovenian', 'sr_RS' => 'Serbian', 'sv_SE' => 'Swedish', 'sw_KE' => 'Swahili', 'ta_IN' => 'Tamil', 'te_IN' => 'Telugu', 'th_TH' => 'Thai', 'tl_PH' => 'Filipino', 'tr_TR' => 'Turkish', 'uk_UA' => 'Ukrainian', 'cy_GB' => 'Welsh', 'vi_VN' => 'Vietnamese', 'zh_CN' => 'Simplified Chinese (China)', 'zh_HK' => 'Traditional Chinese (Hong Kong)', 'zh_TW' => 'Traditional Chinese (Taiwan)',)),
	);
	if ($return_config)
		return $config_vars;
		
	if (isset($_GET['save'])) {
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=modsettings;sa=Fblike');
	}
	
	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=Fblike';
	prepareDBSettingContext($config_vars);
}

function fblike($contenidosecreto)
{
    global $smcFunc, $settings, $scripturl, $context, $txt, $user_info, $boardurl, $modSettings;
  	loadLanguage('Fblike');

	if(empty($modSettings['fblike_enable'])) //Mod is disabled... return the content
	return parse_bbc($contenidosecreto);

	if(empty($modSettings['fblike_guest_can']) && $user_info['is_guest']) //Show message to guests if the mod is disabled to guests
	{
		$fbml = '<div class="fblike" id="fblike">
					'.$txt['fblike_guest_txt'].'
				</div>';
		return $fbml;
	}
	$topic_id = !empty($_REQUEST['topic']) ? (int)$_REQUEST['topic'] : '';
	$board_id = !empty($_REQUEST['board']) ? (int)$_REQUEST['board'] : '';

	if ((!empty($contenidosecreto)) && (!empty($board_id)) || (!empty($topic_id))) //We are in a topic and the bbc have something to us
	{

		if(!empty($context['topic_starter_id']) && ($user_info['id'] == $context['topic_starter_id']))  //Show the content for topic starter user
		{
			$fbml = parse_bbc($contenidosecreto);
			return $fbml;
		}
		if(!empty($modSettings['fblike_message_unhide']) && !$user_info['is_guest']) //show if the message has already replied
		{
			$request = $smcFunc['db_query']('', '
				SELECT id_topic, id_member
				FROM {db_prefix}messages
				WHERE id_topic = {int:id_topic}
				AND id_member = {int:id_member}
				LIMIT 1',
				array(
				  'id_topic' => $context['current_topic'],
				  'id_member' => $user_info['id'],
				)
			);
			if ($smcFunc['db_num_rows']($request) == 1) //Ya dio las gracias puede ver el contenido.
			{
				$fbml = parse_bbc($contenidosecreto);
				return $fbml;
			}
		}
		if($user_info['is_guest'])
		$condicion = "WHERE id_topic = {int:id_topic} AND member_ip = {string:member_ip}";
		else
		$condicion = "WHERE id_topic = {int:id_topic} AND id_member = {int:id_member}";
		$request = $smcFunc['db_query']('', '
			SELECT id_topic,id_member,member_ip
			FROM {db_prefix}fblikes
			'.$condicion.'
			LIMIT 1',
			array(
				  'id_topic' => $context['current_topic'],
				  'id_member' => $user_info['id'],
				  'member_ip' => $user_info['ip'],
			)
		);
		if ($smcFunc['db_num_rows']($request) == 1) //Ya dio las gracias puede ver el contenido.
        {
			$fbml = parse_bbc($contenidosecreto);
			return $fbml;
		}
		$urlfbk = $scripturl."?topic=".$context['current_topic']; //Url del tema.
		$fbml = '
				<div class="fblike" id="fblike">
					'.$txt['fblike_hidden_txt'].'<br />
					<fb:like href="'.$urlfbk.'" send="false" width="450" show_faces="false"></fb:like>
				</div>';
	}
	if(!empty($fbml))
		return $fbml;
}