<?php
/*---------------------------------------------------------------------------------
*	SMFSIMPLE BBCUserInfo													 	  *
*	Author: SSimple Team - 4KSTORE										          *
*	Powered by www.smfsimple.com												  *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

function User_info_actions(&$actionArray) //Action!
{
	$actionArray['userinfo'] = array('Userinfo.php', 'UserInfoMain');
}

function User_info_Admin(&$areas)
{
	global $txt;

	loadLanguage('Userinfo');
	$areas['config']['areas']['modsettings']['subsections']['Userinfo'] = array($txt['uic_admin_title']);
}

function User_info_Admin_Settings(&$sub_actions)
{
	global $context;

	$sub_actions['Userinfo'] = 'User_info_Settings';
}

function User_info_Settings(&$return_config = false)
{
	global $context, $txt, $scripturl;

	loadLanguage('Userinfo');
	$context['page_title'] = $txt['uic_admin_title'];
	$context['settings_title'] = $txt['uic_admin_title'];
	$context['settings_message'] = $txt['uic_admin_title_desc'];
	$config_vars = array(
		array('check', 'uic_enable'),
		array('check', 'uic_guest_can'),
		//array('int', 'uic_count', 'postinput' => $txt['uic_count_seconds'], 3),
		array('select', 'uic_style', array(
			'qtip-blue' => $txt['uic_style_n1'],
			'qtip-light' => $txt['uic_style_n2'],
			'qtip-dark' => $txt['uic_style_n3'],
			'qtip-red' => $txt['uic_style_n4'],
			'qtip-green' => $txt['uic_style_n5'],
			'qtip-plain' => $txt['uic_style_n6'],
			'qtip-bootstrap' => 'Bootstrap',
			'qtip-tipsy' => 'Tipsy',
			'qtip-youtube' => 'Youtube',
			'qtip-jtools' => 'Jtools',
			'qtip-cluetip' => 'Cluetip',
			'qtip-tipped' => 'Tipped',
		)),
		array('check', 'uic_act_group_image'),
		array('check', 'uic_act_personal_text'),
		array('check', 'uic_act_contact_icons'),
	);


	if ($return_config)
		return $config_vars;

	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=modsettings;sa=Userinfo');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=Userinfo';
	prepareDBSettingContext($config_vars);
}

function User_info_add_code(&$codes)
{
	global $modSettings, $smcFunc;

	if (!empty($modSettings['uic_enable']))
	{
		$codes[] = array(
			'tag' => 'user',
			'type' => 'unparsed_content',
			'content' => '$1',
			'validate' => function (&$tag, &$data, $disabled) use ($smcFunc)
			{
				$data = usersInform($smcFunc['htmltrim']($data));
			}
			,
		);
	}
}

function User_info_add_button(&$buttons)
{
	global $txt, $modSettings;

	if (!empty($modSettings['uic_enable']))
	{
		loadLanguage('Userinfo');
		$buttons[count($buttons) - 1][] = array(
			'image' => 'userInfo',
			'code' => 'userInfo',
			'before' => '[user]',
			'after' => '[/user]',
			'description' => $txt['des_userInfo']
		);
	}
}

function User_info_load_theme()
{
	global $context, $settings, $txt, $modSettings;

	if (!empty($modSettings['uic_enable']))
	{
		loadLanguage('Userinfo');
		$context['html_headers'] .= '
		<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url']. '/css/jquery.qtip.css" />
		<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url']. '/css/user_info.css" />
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
		<script type="text/javascript" src="'. $settings['default_theme_url']. '/scripts/jquery.qtip.min.js"></script>';

		$modSettings['uic_style'] = !empty($modSettings['uic_style']) ? $modSettings['uic_style'] : 'blue';
		$context['html_headers'] .= "

		<script type=\"text/javascript\">
		var ui = jQuery.noConflict();
		ui(document).ready(function()
		{

			var loading = '".$txt['uic_loading']."'; //The script don't work if this is empty..
			var uic_style = '".$modSettings['uic_style']."';

			ui('a.user_info[rel]').each(function()
			{
				var username = this.id.replace('ui_','');
				// We make use of the .each() loop to gain access to each element via the \"this\" keyword...
				ui(this).qtip(
				{
					content: {
						// Set the text to an image HTML string with the correct src URL to the loading image you want to use

						text: loading,
						ajax: {
							url: ui(this).attr('rel'), // Use the rel attribute of each element for the url to load
							type: 'POST',
							
							data: 'user='+username
						},
						title: {
							text: ui(this).text(), // Give the tooltip a title using each elements text
							button: true
						}
					},
					position: {
						at: 'bottom center', // Position the tooltip above the link
						my: 'top center',
						viewport: ui(window), // Keep the tooltip on-screen at all times
						effect: false // Disable positioning animation
					},
					show: {
						event: 'click',
						solo: true // Only show one tooltip at a time
					},
					hide: 'unfocus',
					style: {
						classes: uic_style+' qtip-rounded'
					}
				})
			})

			// Make sure it doesn't follow the link when we click it
			.click(function(event) { event.preventDefault(); });
		});
		</script>";
	}
}

function usersInform($username)
{
	global $scripturl, $modSettings, $user_info;

	$username = !empty($username) ? $username : '';	
	$usernamevar = !empty($username) ? urlencode($username) : '';
	$message = $username;

	if (!empty($modSettings['uic_enable']) && (!$user_info['is_guest'] || $user_info['is_guest'] && !empty($modSettings['uic_guest_can'])))
		$message ='<a href="#" class="user_info" id="ui_'.$username.'" rel="'.$scripturl.'?action=userinfo;user='.$usernamevar.'">@'.$username.'</a>';

	return $message;
}

function UserInfoMain()
{
	global $smcFunc, $settings, $scripturl, $memberContext, $txt, $context;

	$username = !empty($_POST['user']) ? urldecode ($smcFunc['htmlspecialchars']($_POST['user'], ENT_QUOTES)) : '';

	if (empty($username))
		return $context['uic'];

	loadTemplate('Userinfo');
	loadLanguage('Userinfo');

	$context['uic'] = array();
	$context['template_layers'] = array();
	$context['sub_template'] = 'userinfo_response';

    $request = $smcFunc['db_query']('', "
		SELECT id_member
		FROM {db_prefix}members
		WHERE real_name = {string:user}
		LIMIT 1",
		array(
			'user' => $smcFunc['htmltrim']($username),
		)
	);

    list ($member_id) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if (empty($member_id))
       return $context['uic'];

	loadMemberData($member_id);
	loadMemberContext($member_id);

	$avatar = '<img src="'.$settings['theme_url'].'/images/sinAvatar.png" alt="" />';

	if (!empty($memberContext[$member_id]['avatar']['href']))
		$avatar = '<img src="'. $memberContext[$member_id]['avatar']['href'] .'" alt="" />';

	$user_age = '';

	if ($memberContext[$member_id]['birth_date'] != '0000-00-00')
	{
		list($year,$month,$day) = explode("-",$memberContext[$member_id]['birth_date']);
		$user_age = date("Y") - $year;
		$month_diff = date("m") - $month;
		$day_diff = date("d") - $day;

		if ($month_diff < 0)
			$user_age--;

		elseif ($month_diff == 0 && $day_diff < 0)
			$user_age--;
	}

	$user_age = !empty($user_age) ? $user_age : '-';

	$color = !empty($memberContext[$member_id]['group_color']) ? $memberContext[$member_id]['group_color'] : '';
	$group = !empty($memberContext[$member_id]['group']) ? $memberContext[$member_id]['group'] : $memberContext[$member_id]['post_group'];
	$personal_text = !empty($memberContext[$member_id]['blurb']) ? $memberContext[$member_id]['blurb'] : '&nbsp;';
	$location = !empty($memberContext[$member_id]['location']) ? $memberContext[$member_id]['location'] : '-';
	$web = !empty($memberContext[$member_id]['website']['url']) ? '<a href="'.$memberContext[$member_id]['website']['url'].'" title="'.$memberContext[$member_id]['website']['title'].'" target="_blank"><img src="'.$settings['theme_url'].'/images/www_sm.gif" alt="" /></a>' : '';
	$usernamehref = !empty($memberContext[$member_id]['href']) ? $memberContext[$member_id]['href'] : '';
	$groupstar = !empty($memberContext[$member_id]['group_stars']) ? $memberContext[$member_id]['group_stars'] : '';
	$gender = !empty($memberContext[$member_id]['gender']['image']) ? $memberContext[$member_id]['gender']['image'] : '-';
	$real_posts = !empty($memberContext[$member_id]['real_posts']) ? $memberContext[$member_id]['real_posts'] : '0';
	$icq = !empty($memberContext[$member_id]['icq']['link']) ? $memberContext[$member_id]['icq']['link'] : '';
	$aim = !empty($memberContext[$member_id]['aim']['link']) ? $memberContext[$member_id]['aim']['link'] : '';
	$yim = !empty($memberContext[$member_id]['yim']['link']) ? $memberContext[$member_id]['yim']['link'] : '';
	$msn = !empty($memberContext[$member_id]['msn']['link']) ? $memberContext[$member_id]['msn']['link'] : '';
	$profile_link = !empty($memberContext[$member_id]['msn']['href']) ? '<a href="'.$memberContext[$member_id]['href'].'" target="_blank" title="'.$txt["view_profile"].'"><img src="'.$settings['theme_url'].'/images/icons/profile_sm.gif" alt="" /></a> ': '';
	$pm_link = '<a href="'.$scripturl.'?action=pm;sa=send;u='.$memberContext[$member_id]['id'].'" target= "_blank" title="'.$txt['personal_messages'].'"><img src="'.$settings["theme_url"].'/images/im_on.gif" alt="" /></a>';

	//Mod Add Social Media Icons To Profiles: http://custom.simplemachines.org/mods/index.php?mod=3304
	$facebook = !empty($memberContext[$member_id]['facebook']['link']) ? $memberContext[$member_id]['facebook']['link'] : '';
	$twitter = !empty($memberContext[$member_id]['twitter']['link']) ? $memberContext[$member_id]['twitter']['link'] : '';
	$googleplus = !empty($memberContext[$member_id]['googleplus']['link']) ? $memberContext[$member_id]['googleplus']['link'] : '';
	$youtube = !empty($memberContext[$member_id]['youtube']['link']) ? $memberContext[$member_id]['youtube']['link'] : '';

	$context['uic'] = array(
		'name' =>  '<a style="color:'.$color.';" href="'.$usernamehref.'" title="' . $txt['profile_of'] . ' ' . $username . '">' . $username . '</a>',
		'avatar' => $avatar,
		'color' => $color,
		'group' => $group,
		'personal_text' => $personal_text,
		'location' => $location,
		'web' => $web,
		'age' => $user_age,
		'groupstar' => $groupstar,
		'gender' => $gender,
		'real_posts' => $real_posts,
		'icq' => $icq,
		'aim' => $aim,
		'yim' => $yim,
		'msn' => $msn,
		'profile_image' => $profile_link,
		'pm_link' => $pm_link,
		'facebook' => $facebook,
		'twitter' => $twitter,
		'googleplus' => $googleplus,
		'youtube' => $youtube,
	);
}

function User_info_Buffer($buffer)
{
	global $forum_copyright, $context, $sourcedir;
	
	require_once($sourcedir . '/QueryString.php');
	ob_sessrewrite($buffer);
	
	if(empty($context['deletforum']))
	{
		$context['deletforum'] = base64_decode('IHwgPGEgc3R5bGU9ImZvbnQtc2l6ZToxMHB4OyIgaHJlZj0iaHR0cDovL3d3dy5zbWZzaW1wbGUuY29tIiB0aXRsZT0iVG9kbyBwYXJhIHR1IGZvcm8gU01GIj5Nb2RzIGJ5IFNNRlNpbXBsZS5jb208L2E+');	
		$buffer = str_replace($forum_copyright, $forum_copyright.$context['deletforum'],$buffer);			
	}
	return $buffer;
}