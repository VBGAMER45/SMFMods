<?php
/*
Social Login Pro
Version 2.0
by:vbgamer45
http://www.smfhacks.com

License Information:
Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
*/
global $ssi_guest_access;
$ssi_guest_access = 1;
include 'SSI.php';


$secretKey2 = $modSettings['slp_secretkey'];


$UID = $_REQUEST['UID'];
$zip = htmlspecialchars($_REQUEST['zip'],ENT_QUOTES);
$photoURL =htmlspecialchars($_REQUEST['photoURL'],ENT_QUOTES);
$nickname = $_REQUEST['nickname'];
$nickname = preg_replace('#([\%u]{2})([0-9a-zA-Z]{4})#i', '&#x$2', $nickname);
$nickname  = html_entity_decode($nickname , ENT_NOQUOTES,  'UTF-8');

$profileURL = htmlspecialchars($_REQUEST['profileURL'],ENT_QUOTES);
$birthMonth = (int) $_REQUEST['birthMonth'];
$loginProvider = htmlspecialchars($_REQUEST['loginProvider'],ENT_QUOTES);
$loginProviderUID = htmlspecialchars($_REQUEST['loginProviderUID'],ENT_QUOTES);
$country = htmlspecialchars($_REQUEST['country'],ENT_QUOTES);
$thumbnailURL = htmlspecialchars($_REQUEST['thumbnailURL'],ENT_QUOTES);
$lastName = $_REQUEST['lastName'];
$lastName = preg_replace('#([\%u]{2})([0-9a-zA-Z]{4})#i', '&#x$2', $lastName);

$lastName  = html_entity_decode($lastName , ENT_NOQUOTES,  'UTF-8');
$lastName = $func['htmlspecialchars']($lastName,ENT_QUOTES);

$signature = $_REQUEST['signature'];
$firstName = $_REQUEST['firstName'];
$firstName = preg_replace('#([\%u]{2})([0-9a-zA-Z]{4})#i', '&#x$2', $firstName);
$firstName  = html_entity_decode($firstName , ENT_NOQUOTES,  'UTF-8');

$firstName = $func['htmlspecialchars']($firstName,ENT_QUOTES);

$provider = htmlspecialchars($_REQUEST['provider'],ENT_QUOTES);
$gender = htmlspecialchars($_REQUEST['gender'],ENT_QUOTES);
$birthYear = (int) $_REQUEST['birthYear'];
$timestamp = $_REQUEST['timestamp'];
$UIDSig = $_REQUEST['UIDSig'];
$state = htmlspecialchars($_REQUEST['state'],ENT_QUOTES);
$email = htmlspecialchars($_REQUEST['email'],ENT_QUOTES);
$city = htmlspecialchars($_REQUEST['city'],ENT_QUOTES);
$birthDay = htmlspecialchars($_REQUEST['birthDay'],ENT_QUOTES);
$proxiedEmail = htmlspecialchars($_REQUEST['proxiedEmail'],ENT_QUOTES);


$avatarUrl = $thumbnailURL;
if (empty($avatarUrl))
	$avatarUrl = $photoURL;

if (empty($modSettings['slp_importavatar']))
	$avatarUrl = '';


if (empty($UID))
	exit;

//IF ($signature != calcHmacsha1Signature($secretKey2,$UID,$timestamp))
//	die("Invalid signature...:(  "  );
//else
{

	// Load cookie authentication stuff.
	require_once($sourcedir . '/Subs-Auth.php');

	// Check if UID already exists....
	$UID = htmlspecialchars($UID,ENT_QUOTES);
	$result = db_query("
	SELECT id_member, merged FROM
	{$db_prefix}social_logins WHERE uid = '$UID'", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($result );

	// If exists log in user
	if (!empty($row['id_member']))
	{
		$request = db_query("
			SELECT passwd, id_member, id_group, lngfile, is_activated, emailAddress, additionalGroups, memberName, passwordSalt

			FROM {$db_prefix}members
			WHERE id_member = " . $row['id_member'] . "
			LIMIT 1"

		, __FILE__, __LINE__);

	loadLanguage('Login');
	$context['page_title'] = $txt[34];
	global $user_settings ;
	$modSettings['cookieTime'] = 3153600;
	$user_settings = mysql_fetch_assoc($request);
	include_once($sourcedir.'/LogInOut.php');


	if ($row['merged'] == 0)
	{
		$encryptCookie = sha1($UID . $secretKey2);
		updateMemberData($row['id_member'] , array('passwd' => '\'' . $encryptCookie  . '\''));
		if (empty($user_settings['passwordSalt']))
		{
			 $salt = substr(md5(mt_rand()), 0, 4);

			updateMemberData($row['id_member'] , array('passwordSalt' => '\'' . $salt . '\''));
			$user_settings['passwordSalt'] = 	 $salt;
		}
	}
	else
	{
		$encryptCookie = $user_settings['passwd'];
	}


	$activation_status = $user_settings['is_activated'] > 10 ? $user_settings['is_activated'] - 10 : $user_settings['is_activated'];

	// Check if the account is activated - COPPA first...
	if ($activation_status == 5)
	{
		$context['login_error'] =  $txt['coppa_not_completed1'] . ' <a href="' . $scripturl . '?action=coppa;member=' . $user_settings['ID_MEMBER'] . '">' . $txt['coppa_not_completed2'] . '</a>';
		loadTemplate('Login');
		$context['sub_template'] = 'login';
		obExit();
	}
	// Awaiting approval still?
	elseif ($activation_status == 3)
	{
		$context['login_error'] =  $txt['still_awaiting_approval'];
		loadTemplate('Login');
		$context['sub_template'] = 'login';
		obExit();
	}
	// Awaiting deletion, changed their mind?
	elseif ($activation_status == 4)
	{
		// Display an error if we haven't decided to undelete.
		$context['login_error'] = $txt['awaiting_delete_account'];
		loadTemplate('Login');
		$context['sub_template'] = 'login';
		obExit();
	}
	// Standard activation?
	elseif ($activation_status != 1)
	{

		$context['login_error'] =  $txt['activate_not_completed1'] . ' <a href="' . $scripturl . '?action=activate;sa=resend;u=' . $user_settings['ID_MEMBER'] . '">' . $txt['activate_not_completed2'] . '</a>';
		loadTemplate('Login');
		$context['sub_template'] = 'login';
		obExit();
	}


	$modSettings['cookieTime'] = 3153600;
	setLoginCookie(60 * $modSettings['cookieTime'], $row['id_member'], sha1($encryptCookie  . $user_settings['passwordSalt']));

	$username = $user_settings['memberName'];
	$user_info['id'] = $row['id_member'];

// Reset the login threshold.
	if (isset($_SESSION['failed_login']))
		unset($_SESSION['failed_login']);

	$user_info['is_guest'] = false;
	$user_settings['additionalGroups'] = explode(',', $user_settings['additionalGroups']);
	$user_info['is_admin'] = $user_settings['id_group'] == 1 || in_array(1, $user_settings['additionalGroups']);

	// Are you banned?
	is_not_banned(true);



	// Don't stick the language or theme after this point.
	unset($_SESSION['language'], $_SESSION['id_theme']);

	// First login?
	$request = db_query("
		SELECT lastLogin
		FROM {$db_prefix}members
		WHERE id_member = " . $row['id_member'] . "
			AND lastLogin = 0"

	, __FILE__, __LINE__);
	if (mysql_num_rows($request) == 1)
		$_SESSION['first_login'] = true;
	else
		unset($_SESSION['first_login']);
	mysql_free_result($request);

	// You've logged in, haven't you?
	updateMemberData($row['id_member'], array('lastLogin' => time(), 'memberIP' =>  '\'' . $user_info['ip'] . '\'', 'memberIP2' => '\'' .  $_SERVER['BAN_CHECK_IP'] . '\''));

		// Get rid of the online entry for that old guest....
		db_query("
			DELETE FROM {$db_prefix}log_online
			WHERE session = 'ip" . $user_info['ip'] . "'"

		, __FILE__, __LINE__);

		$_SESSION['log_time'] = 0;
		$_SESSION['old_url'] = $boardurl;

		if (empty($maintenance))
			redirectexit('action=login2;sa=check;member=' . $row['id_member'], $context['server']['needs_login_fix']);
		else
			redirectexit('action=logout;' . $context['session_var'] . '=' . $context['session_id'], $context['server']['needs_login_fix']);


		exit;
	}
	else
	{
	// Does not exist register member


		// Check if duplicate username
		$memCheck = db_query("
		SELECT
			id_member
		FROM {$db_prefix}members
		WHERE realName = '$nickname' or memberName = '$nickname'", __FILE__, __LINE__);
		 if (mysql_num_rows($memCheck) != 0)
		 {
		 	// Find a new nickname
		 	$nickname .= "_" . ($modSettings['totalMembers'] + 1);
		 }

		// Check duplicate email
		$badEmail = 0;
		$memCheck = db_query("
		SELECT
			id_member FROM {$db_prefix}members
		WHERE emailAddress ='$email'", __FILE__, __LINE__);
		if (mysql_num_rows($memCheck) != 0)
		{
			$badEmail = 1;
			$email = '';
		}

		if (empty($email))
		{
			global $context;
			$context['page_title_html_safe'] = $txt['slp_txt_completeemail'];

			template_header();
			echo '
			<h1>',$txt['slp_txt_completeemail'],'</h1>
			<form method="post" action="' . $boardurl . '/gigya.php">
			<table>
				<tr>
					<td colspan="2">' . $txt['slp_txt_nickname'] . ' ' . $nickname . '</td>

				</tr>
';

			if ($badEmail == 1)
			echo '<tr>
				<td colspan="2"><font color="#FF0000">' . $txt['slp_err_alreadyregistered'] . ' ' . $email . '</td>
			</tr>';


				echo '

				<tr>
					<td>' . $txt['slp_enter_address'] . '</td>
					<td><input type="text" name="email" size="50" value="" /></td>
				</tr>
				<tr>
					<td colspan="2">';

			foreach ($_REQUEST as $key => $value)
			{
				if ($key == 'email')
					continue;

				echo '<input type="hidden" name="' . $key . '" value="' .$value . '" />';
			}

			echo '
					<input type="submit" value="' . $txt['slp_txt_complete_reg'] . '" /></td>
				</tr>
			</table>
			</form>';
			template_footer();

			exit;
		}


		$salt = substr(md5(mt_rand()), 0, 4);
		require_once($sourcedir . '/Subs-Members.php');
		$regOptions = array(
		'send_welcome_email' => !empty($modSettings['send_welcomeEmail']),
		'interface' => 'guest',
		'username' => preg_replace('/[^a-z0-9]/i', '', $nickname),
		'memberName' => preg_replace('/[^a-z0-9]/i', '', $nickname),
		'realName' => $nickname,
		'emailAddress' => $email,
		'require' => 'nothing',
		'email' => $email,
		'check_password_strength' => false,
		'passwd' => sha1($UID . $secretKey2),
		'password' => sha1($UID . $secretKey2),
		'password_check' => sha1($UID . $secretKey2),
		'passwordSalt' => $salt ,
		'auth_method' => 'password',
		'posts' => 0,
		'date_registered' => time(),
		'memberIP' =>  $user_info['ip'],
		'memberIP2' =>  $_SERVER['BAN_CHECK_IP'],
		'validation_code' => '',
		'real_name' => $nickname,
		'personal_text' => '',
		'pm_email_notify' => 1,
		'id_theme' => 0,
		'id_post_group' => 4,
		'lngfile' => '',
		'buddy_list' => '',
		'pm_ignore_list' => '',
		'message_labels' => '',
		'website_title' => '',
		'website_url' => '',
		'location' => '',
		'icq' => '',
		'aim' => '',
		'yim' => '',
		'msn' => '',
		'time_format' => '',
		'signature' => '',
		'avatar' => $avatarUrl,
		'usertitle' => '',
		'secret_question' => '',
		'secret_answer' => '',
		'additionalGroups' => '',
		'ignore_boards' => '',
		'smiley_set' => '',

		);
		$regOptions['register_vars'] = array('realName' => $nickname);

		// register member
		$memberID = registerMember($regOptions, true);


		//updateMemberData($memberID , array('passwordSalt' => $salt));
		updateMemberData($memberID , array('passwordSalt' => '\'' . $salt . '\'','avatar' => '\'' . $avatarUrl . '\''));

		if (empty($memberID))
		{
			fatal_error($txt['slp_err_reg']);
			exit;
		}

		$encryptPass = sha1($UID . $secretKey2);

		$nickname = addslashes($nickname);
		// Reset password
		db_query("
		UPDATE {$db_prefix}members
		SET passwd = '$encryptPass',realName = '$nickname'
		WHERE id_member = $memberID", __FILE__, __LINE__);

		// Insert record into dblog
		db_query("
			INSERT INTO {$db_prefix}social_logins
			(id_member,uid,nickname,email,photourl,profileurl,
			zip,state,city,country,firstname,lastname,
			birthmonth,birthday,birthyear, thumbnailurl,
			loginprovider,loginprovideruid,provider,proxiedemail

			)
			VALUES ($memberID,'$UID','$nickname','$email','$photoURL','$profileURL',
			'$zip','$state','$city','$country','$firstName','$lastName',
			'$birthMonth','$birthDay','$birthYear','$thumbnailURL',
			'$loginProvider','$loginProviderUID','$provider','$proxiedEmail'
			)
			", __FILE__, __LINE__);
	// Member registered now what
	$encyptCookie = sha1($UID . $secretKey2);
	$modSettings['cookieTime'] = 3153600;
	setLoginCookie(60 * $modSettings['cookieTime'], $memberID, sha1($encyptCookie . $salt));

	$username = $nickname;
	$user_info['id'] = $memberID;


		$request = db_query("
			SELECT passwd, id_member, id_group, lngfile, is_activated, emailAddress, additionalGroups, memberName, passwordSalt

			FROM {$db_prefix}members
			WHERE id_member = " . $memberID . "
			LIMIT 1"

		, __FILE__, __LINE__);


	global $user_settings ;
	$user_settings = mysql_fetch_assoc($request);


	$activation_status = $user_settings['is_activated'] > 10 ? $user_settings['is_activated'] - 10 : $user_settings['is_activated'];

	// Check if the account is activated - COPPA first...
	if ($activation_status == 5)
	{
		fatal_error($txt['coppa_not_completed1'] . ' <a href="' . $scripturl . '?action=coppa;member=' . $user_settings['ID_MEMBER'] . '">' . $txt['coppa_not_completed2'] . '</a>',false);
		return;
	}
	// Awaiting approval still?
	elseif ($activation_status == 3)
		fatal_lang_error('still_awaiting_approval');
	// Awaiting deletion, changed their mind?
	elseif ($activation_status == 4)
	{
		// Display an error if we haven't decided to undelete.
			fatal_error($txt['awaiting_delete_account'],false);

	}

	// Reset the login threshold.
	if (isset($_SESSION['failed_login']))
		unset($_SESSION['failed_login']);

	$user_info['is_guest'] = false;

	// Are you banned?
	is_not_banned(true);


	// Don't stick the language or theme after this point.
	unset($_SESSION['language'], $_SESSION['id_theme']);

	// First login?
	$request = db_query("
		SELECT lastLogin
		FROM {$db_prefix}members
		WHERE id_member = $memberID
			AND lastLogin = 0"

	, __FILE__, __LINE__);
	if (mysql_num_rows($request) == 1)
		$_SESSION['first_login'] = true;
	else
		unset($_SESSION['first_login']);
	mysql_free_result($request);

	// You've logged in, haven't you?
	updateMemberData($memberID, array('lastLogin' => time(), 'memberIP' => '\'' .  $user_info['ip'] . '\'', 'memberIP2' =>  '\'' .  $_SERVER['BAN_CHECK_IP'] . '\''));

		// Get rid of the online entry for that old guest....
		db_query("
			DELETE FROM {$db_prefix}log_online
			WHERE session = 'ip" . $user_info['ip'] . "'"

		, __FILE__, __LINE__);

		$_SESSION['log_time'] = 0;

		redirectexit('action=login2;sa=check;member=' . $memberID, $context['server']['needs_login_fix']);


	}
}

/*
function calcHmacsha1Signature($secretKey, $UID, $timestamp) {
     $base_string = $timestamp.'_'.$UID;
     return base64_encode(hash_hmac('sha1', $base_string, base64_decode($secretKey), true));
}
*/
 function calcHmacsha1Signature($secretkey, $UID, $timestamp) {
     return hmacsha1(base64_decode($secretkey), $timestamp.'_'.$UID);
 }
 function hmacsha1($key,$data) {
     $blocksize=64;
    $hashfunc='sha1';
     if (strlen($key)>$blocksize)
         $key=pack('H*', $hashfunc($key));
     $key=str_pad($key,$blocksize,chr(0x00));
     $ipad=str_repeat(chr(0x36),$blocksize);
     $opad=str_repeat(chr(0x5c),$blocksize);
     $hmac = pack('H*',$hashfunc(($key^$opad).pack('H*',$hashfunc(($key^$ipad).$data))));
    return base64_encode($hmac);
 }


 ?>