<?php
/*
Contact Page
Version 3.2
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function Contact()
{
	global $context, $mbname, $webmaster_email, $txt, $sourcedir, $modSettings, $scripturl, $func;

	// Check if the current user can send a message
	isAllowedTo('view_contact');

	if (isset($_GET['sa']))
	{

		if ($_GET['sa'] == 'save')
		{
			
		// Check whether the visual verification code was entered correctly.
		if(isset($modSettings['recaptcha_enabled']) &&!empty($modSettings['recaptcha_enabled']) && ($modSettings['recaptcha_enabled'] == 1 && !empty($modSettings['recaptcha_public_key']) && !empty($modSettings['recaptcha_private_key'])))
		{
			if(!empty($_POST["recaptcha_response_field"]) && !empty($_POST["recaptcha_challenge_field"])) //Check the input if this exists, if it doesn't, then the user didn't fill it out.
			{
				require_once("$sourcedir/recaptchalib.php");

				$resp = recaptcha_check_answer($modSettings['recaptcha_private_key'], $_SERVER['REMOTE_ADDR'], $_REQUEST['recaptcha_challenge_field'], $_REQUEST['recaptcha_response_field']);

				if (!$resp->is_valid)
					fatal_lang_error('error_wrong_verification_code', false);
			}
			else
				fatal_lang_error('error_wrong_verification_code', false);
		}
		else if ((empty($modSettings['disable_visual_verification']) || $modSettings['disable_visual_verification'] != 1) && (empty($_REQUEST['visual_verification_code']) || strtoupper($_REQUEST['visual_verification_code']) !== $_SESSION['visual_verification_code']))
		{
			$_SESSION['visual_errors'] = isset($_SESSION['visual_errors']) ? $_SESSION['visual_errors'] + 1 : 1;
			if ($_SESSION['visual_errors'] > 3 && isset($_SESSION['visual_verification_code']))
				unset($_SESSION['visual_verification_code']);
	
			fatal_lang_error('visual_verification_failed', false);
		}
		elseif (isset($_SESSION['visual_errors']))
			unset($_SESSION['visual_errors']);
			
			$from = $_POST['from'];
			if ($from == '')
				fatal_error($txt['smfcontact_errname'], false);
			$subject = $_POST['subject'];
			if ($subject == '')
				fatal_error($txt['smfcontact_errsubject'], false);
			$message = $_POST['message'];
			if ($message == '')
				fatal_error($txt['smfcontact_errmessage'], false);
			$email = trim($_POST['email']);
			if ($email == '')
				fatal_error($txt['smfcontact_erremail'], false);


			if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
				fatal_error($txt['smfcontact_errbademail'], false);

			$subject = $func['htmlspecialchars']($subject, ENT_QUOTES);
			$message = $func['htmlspecialchars']($message, ENT_QUOTES);
			$from = $func['htmlspecialchars']($from, ENT_QUOTES);
			$email = $func['htmlspecialchars']($email, ENT_QUOTES);

$m = $txt['smfcontact_form'] . $mbname . " \n";
$m .= $txt['smfcontact_formname'] . $from . "\n";
$m .= $txt['smfcontact_formemail'] . $email . "\n";
$m .= $txt['smfcontact_ip'] . $_SERVER['REMOTE_ADDR'] . "\n";
$m .= $txt['smfcontact_formmessage'];
$m .= $message;
$m .= "\n";


	// For send mail function
	require_once($sourcedir . '/Subs-Post.php');

		// Send email to webmaster
		if (empty($modSettings['smfcontactpage_email']))
			sendmail($webmaster_email, $subject, $m,$email);
		else 
			sendmail($modSettings['smfcontactpage_email'], $subject, $m,$email);
			
		// Show template that mail was sent
		loadtemplate('Contact');

		// Load the main contact template
		$context['sub_template']  = 'send';

		// Set the page title
		$context['page_title'] = $mbname . $txt['smfcontact_titlesent'];

		}
	}
	else
	{

		// Load the main Contact template
		loadtemplate('Contact');
		
		// Language strings
		loadLanguage('Login');
		// Load the main Contact template
		$context['sub_template']  = 'main';

		// Set the page title
		$context['page_title'] = $mbname . ' - ' . $txt['smfcontact_contact'];
		
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
?>