<?php
/*
User Email System
Version 1.2
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function UserEmailMain()
{ 
	global $context, $mbname, $scripturl, $webmaster_email, $modSettings, $ID_MEMBER, $txt, $db_prefix, $sourcedir, $user_info;

	// Check if the current user can send emails
	isAllowedTo('send_useremail');

	if (isset($_GET['sa']))
	{

		if ($_GET['sa'] == 'save')
		{

		// Check whether the visual verification code was entered correctly.
		if ((empty($modSettings['disable_visual_verification']) || $modSettings['disable_visual_verification'] != 1) && (empty($_REQUEST['visual_verification_code']) || strtoupper($_REQUEST['visual_verification_code']) !== $_SESSION['visual_verification_code']))
		{
			$_SESSION['visual_errors'] = isset($_SESSION['visual_errors']) ? $_SESSION['visual_errors'] + 1 : 1;
			if ($_SESSION['visual_errors'] > 3 && isset($_SESSION['visual_verification_code']))
				unset($_SESSION['visual_verification_code']);
	
			fatal_lang_error('visual_verification_failed', false);
		}
		elseif (isset($_SESSION['visual_errors']))
			unset($_SESSION['visual_errors']);			
			
			@$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);
			if ($subject == '')
				fatal_error($txt['user_email_errsubject'], false);
			@$message = htmlspecialchars($_POST['message'], ENT_QUOTES);
			if ($message == '')
				fatal_error($txt['user_email_errmessage'], false);


			@$userid = (int) $_POST['userid'];
			if (empty($userid))
				fatal_error($txt['user_email_errnouser'], false);

	$request = db_query("
	SELECT 
		realName, hideEmail, emailAddress 
	FROM {$db_prefix}members 
	WHERE ID_MEMBER = $userid LIMIT 1", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($request);
	
	if($row['hideEmail'] == 1 && !allowedTo('admin_forum'))
				fatal_error($txt['user_email_errnousersend'], false);

	$rec = $row['realName'];
	$rec_email = $row['emailAddress'];

	mysql_free_result($request);
	
	// Show the Guest email form field
	if (!$user_info['is_guest'])
	{
		$request2 = db_query("
		SELECT 
			realName, emailAddress 
		FROM {$db_prefix}members 
		WHERE ID_MEMBER = $ID_MEMBER LIMIT 1", __FILE__, __LINE__);
		$row2 = mysql_fetch_assoc($request2);
		$sec_name = $row2['realName'];
		$sec_email = $row2['emailAddress'];
		mysql_free_result($request2);
	}
	else 
	{
			@$guestemail = htmlspecialchars($_POST['guestemail'], ENT_QUOTES);
			if ($guestemail == '')
				fatal_error($txt['user_email_errnoemail'],false);
				
			if (!is_valid_email($guestemail))
			{
				fatal_error($txt['user_email_err_invalidemail'],false);
			}
		
		$sec_name = $txt['user_email_guest'];
		
		$sec_email = $guestemail;
	}

$m = $txt['user_email_hello'] . $rec . $txt['user_email_emailsentby'] . $sec_name . $txt['user_email_viaaccount'] . $mbname
. $txt['user_email_ifmsgspam'] .  $webmaster_email
 . " \n" . $txt['user_email_msgsentas'] . "\n";
$m .=  strip_tags($message);
		// For send mail function
		require_once($sourcedir . '/Subs-Post.php');

		// Send email to member
		sendmail($rec_email, $subject, $m, $sec_email);

		// Check if it should send the sender a copy of email
		@$sendcopy = $_POST['sendcopy'];
		if ($sendcopy == 'ON')
		{
			sendmail($sec_email, $subject, $m);
		}

		// Show template that mail was sent
		loadtemplate('User_Email');

		// Load the main User Email template
		$context['sub_template']  = 'send';

		// Set the page title
		$context['page_title'] = $mbname . $txt['user_email_emailsent'];

		}
	}
	else
	{
		$u = (int) $_GET['u'];

		$request = db_query("
		SELECT 
			realName,hideEmail 
		FROM {$db_prefix}members 
		WHERE ID_MEMBER = $u LIMIT 1", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($request);
		$context['user_email_name'] = $row['realName'];
		$context['user_email_id'] = $u;
		
		if($row['hideEmail'] == 1 && !allowedTo('admin_forum'))
				fatal_error($txt['user_email_errnousersend'], false);

		// Load the main User Email template
		loadtemplate('User_Email');
		
		// Language strings
		loadLanguage('Login');

		// Load the main User Email template
		$context['sub_template']  = 'main';

		// Set the page title
		$context['page_title'] = $mbname . ' - ' . $txt['user_email_title'];
		
		// Generate a visual verification code to make sure the user is no bot.
		$context['visual_verification'] = empty($modSettings['disable_visual_verification']) || $modSettings['disable_visual_verification'] != 1;
		if ($context['visual_verification'])
		{
			$context['use_graphic_library'] = in_array('gd', get_loaded_extensions());
			$context['verificiation_image_href'] = $scripturl . '?action=verificationcode;rand=' . md5(rand());
	
			// Only generate a new code if one hasn't been set yet
			if (!isset($_SESSION['visual_verification_code']))
			{
				// Skip I, J, L, O and Q.
				$character_range = array_merge(range('A', 'H'), array('K', 'M', 'N', 'P'), range('R', 'Z'));
	
				// Generate a new code.
				$_SESSION['visual_verification_code'] = '';
				for ($i = 0; $i < 5; $i++)
					$_SESSION['visual_verification_code'] .= $character_range[array_rand($character_range)];
			}
		}
	}
}
function is_valid_email($email) 
{
	return preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s]+\.+[a-z]{2,6}))$#si', $email);
}
?>