<?php
/*
Contact Page
Version 3.2
by:vbgamer45
https://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function Contact()
{
	global $context, $mbname, $webmaster_email, $txt, $sourcedir, $user_info, $modSettings, $scripturl, $smcFunc;

	// Check if the current user can send a message
	isAllowedTo('view_contact');

	if (isset($_REQUEST['sa']))
	{

		if ($_REQUEST['sa'] == 'save')
		{
		
			if ($context['user']['is_guest'] == true)
			{	
				
				
		if(isset($modSettings['recaptcha_enabled']) &&!empty($modSettings['recaptcha_enabled']) && ($modSettings['recaptcha_enabled'] == 1 && !empty($modSettings['recaptcha_public_key']) && !empty($modSettings['recaptcha_private_key'])))
		{
			$loadRECAPTCHA = 0;
			if (file_exists("$sourcedir/recaptchalib.php"))
			{
				require_once("$sourcedir/recaptchalib.php");
				$loadRECAPTCHA = 1;
			}
			
			if (file_exists("$sourcedir/recaptcha/recaptcha-for-smf.php"))
			{
			
			
			 $recaptcha = new \ReCaptcha\ReCaptcha($modSettings['recaptcha_private_key']);
			
			
	            // Was there a reCAPTCHA response?
	            if(isset($_REQUEST["g-recaptcha-response"]))
	            {
	                $resp = $recaptcha->verify($_REQUEST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);
	
	                if (!$resp->isSuccess())
	                    fatal_lang_error('error_wrong_verification_code', false);
	            }
	            else
	                fatal_lang_error('error_wrong_verification_code', false);
				
			
			
			}
			else if (class_exists('ReCaptcha') && $loadRECAPTCHA == 1) 
			{
					$reCaptcha = new ReCaptcha($modSettings['recaptcha_private_key']);
		
					// Was there a reCAPTCHA response?
					if(isset($_POST["g-recaptcha-response"]))
					{
						$resp = $reCaptcha->verifyResponse( $_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"] );
		
						if (!$resp->success)
							fatal_lang_error('error_wrong_verification_code', false);
					}
					else
						fatal_lang_error('error_wrong_verification_code', false);
			
			}
			else
			{

				// Use non class program
			
				if(!empty($_POST["recaptcha_response_field"]) && !empty($_POST["recaptcha_challenge_field"])) //Check the input if this exists, if it doesn't, then the user didn't fill it out.
				{
			
	
					$resp = recaptcha_check_answer($modSettings['recaptcha_private_key'], $_SERVER['REMOTE_ADDR'], $_REQUEST['recaptcha_challenge_field'], $_REQUEST['recaptcha_response_field']);
	
					if (!$resp->is_valid)
						fatal_lang_error('error_wrong_verification_code', false);
				}
				else
					fatal_lang_error('error_wrong_verification_code', false);
					
				
			}	
				
		}
		else if (!empty($modSettings['reg_verification']))
				{
					
					// no Repcachea
					
					require_once($sourcedir . '/Subs-Editor.php');
					$verificationOptions = array(
						'id' => 'post',
					);
					$context['visual_verification'] = create_control_verification($verificationOptions, true);
		
					if (is_array($context['visual_verification']))
					{
						loadLanguage('Errors');
						foreach ($context['visual_verification'] as $error)
							fatal_error($txt['error_' . $error],false);
					}
				}
			}
			
			$from = trim($_POST['from']);
			if ($from == '')
				fatal_error($txt['smfcontact_errname'], false);
			$subject = trim($_POST['subject']);
			if ($subject == '')
				fatal_error($txt['smfcontact_errsubject'], false);
			$message = $_POST['message'];
			if ($message == '')
				fatal_error($txt['smfcontact_errmessage'], false);
			$email = trim($_POST['email']);
			if ($email == '')
				fatal_error($txt['smfcontact_erremail'], false);

			$subject = $smcFunc['htmlspecialchars']($subject, ENT_QUOTES);
			$message = $smcFunc['htmlspecialchars']($message, ENT_QUOTES);
			$from = $smcFunc['htmlspecialchars']($from, ENT_QUOTES);
			$email = $smcFunc['htmlspecialchars']($email, ENT_QUOTES);

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
			sendmail($webmaster_email, $subject, $m,$email,null,'contact');
		else 
			sendmail($modSettings['smfcontactpage_email'], $subject, $m,$email,null,'contact');
			
		// Show template that mail was sent
		loadtemplate('Contact2');

		// Load the main contact template
		$context['sub_template']  = 'send';

		// Set the page title
		$context['page_title'] = $mbname . $txt['smfcontact_titlesent'];

		}
	}
	else
	{

		// Load the main Contact template
		loadtemplate('Contact2');
		
		// Language strings
		loadLanguage('Login');
		// Load the main Contact template
		$context['sub_template']  = 'main';

		// Set the page title
		$context['page_title'] = $mbname . ' - ' . $txt['smfcontact_contact'];
		
			// Do we need to show the visual verification image?
	$context['require_verification'] = (!empty($modSettings['reg_verification']) && $context['user']['is_guest'] == true);
	if ($context['require_verification'])
	{
		require_once($sourcedir . '/Subs-Editor.php');
		$verificationOptions = array(
			'id' => 'post',
		);
		$context['require_verification'] = create_control_verification($verificationOptions);
		$context['visual_verification_id'] = $verificationOptions['id'];
	}
	}
}
?>