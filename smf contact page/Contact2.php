<?php
/*
Contact Page
Version 6.0
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

		// Load the main Contact template
		loadtemplate('Contact2');

		// Language strings
		loadLanguage('Login');

		// Load the main Contact template
		$context['sub_template']  = 'main';

	if (isset($_REQUEST['sa']))
	{
		checkSession();

		if ($_REQUEST['sa'] == 'save') {

            if ($context['user']['is_guest'] == true) {


                if (!empty($modSettings['hcaptcha_enabled']) && ($modSettings['hcaptcha_enabled'] == 1 && !empty($modSettings['hcaptcha_public_key']) && !empty($modSettings['hcaptcha_private_key']))) {

                    // Verify the captcha
                    if (isset($_REQUEST["h-captcha-response"])) {
                        require_once($sourcedir . '/Subs-Package.php');
                        $response = fetch_web_data('https://hcaptcha.com/siteverify?secret=' . $modSettings['hcaptcha_private_key'] . '&response=' . $_REQUEST["h-captcha-response"] . '&remoteip=' . $_SERVER["REMOTE_ADDR"]);
                        $response = json_decode($response, true);

                        if (true != $response["success"])
                            $verification_errors[] = 'wrong_verification_code';

                    } else
                        $verification_errors[] = 'need_verification_code';


                } else if (isset($modSettings['recaptcha_enabled']) && !empty($modSettings['recaptcha_enabled']) && ($modSettings['recaptcha_enabled'] == 1 && !empty($modSettings['recaptcha_public_key']) && !empty($modSettings['recaptcha_private_key']))) {
                    $loadRECAPTCHA = 0;
                    if (file_exists("$sourcedir/recaptchalib.php")) {
                        require_once("$sourcedir/recaptchalib.php");
                        $loadRECAPTCHA = 1;
                    }

                    if (file_exists("$sourcedir/recaptcha/recaptcha-for-smf.php")) {


                        $recaptcha = new \ReCaptcha\ReCaptcha($modSettings['recaptcha_private_key']);


                        // Was there a reCAPTCHA response?
                        if (isset($_REQUEST["g-recaptcha-response"])) {
                            $resp = $recaptcha->verify($_REQUEST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);

                            if (!$resp->isSuccess())
                                fatal_lang_error('error_wrong_verification_code', false);
                        } else
                            fatal_lang_error('error_wrong_verification_code', false);


                    } else if (class_exists('ReCaptcha') && $loadRECAPTCHA == 1) {
                        $reCaptcha = new ReCaptcha($modSettings['recaptcha_private_key']);

                        // Was there a reCAPTCHA response?
                        if (isset($_POST["g-recaptcha-response"])) {
                            $resp = $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"]);

                            if (!$resp->success)
                                fatal_lang_error('error_wrong_verification_code', false);
                        } else
                            fatal_lang_error('error_wrong_verification_code', false);

                    } else if ($loadRECAPTCHA == 1) {

                        // Use non class program

                        if (!empty($_POST["recaptcha_response_field"]) && !empty($_POST["recaptcha_challenge_field"])) //Check the input if this exists, if it doesn't, then the user didn't fill it out.
                        {


                            $resp = recaptcha_check_answer($modSettings['recaptcha_private_key'], $_SERVER['REMOTE_ADDR'], $_REQUEST['recaptcha_challenge_field'], $_REQUEST['recaptcha_response_field']);

                            if (!$resp->is_valid)
                                fatal_lang_error('error_wrong_verification_code', false);
                        } else
                            fatal_lang_error('error_wrong_verification_code', false);


                    }
					else if ($loadRECAPTCHA == 0)
						$modSettings['recaptcha_enabled'] = 0;

                } else if (!empty($modSettings['reg_verification'])) {

                    // no Repcachea

                    require_once($sourcedir . '/Subs-Editor.php');
                    $verificationOptions = array(
                        'id' => 'post',
                    );
                    $context['visual_verification'] = create_control_verification($verificationOptions, true);

                    if (is_array($context['visual_verification'])) {
                        loadLanguage('Errors');
                        foreach ($context['visual_verification'] as $error)
                            fatal_error($txt['error_' . $error], false);
                    }
                }
            }

			if (isset($_POST['from']))
            	$from = trim($_POST['from']);
			else
				$from  = '';

            if ($from == '')
                fatal_error($txt['smfcontact_errname'], false);

			if (isset($_POST['subject']))
            	$subject = trim($_POST['subject']);
			else
				$subject = '';

            if ($subject == '')
                fatal_error($txt['smfcontact_errsubject'], false);

			if (isset($_POST['message']))
            	$message = $_POST['message'];
			else
				$message = '';

            if ($message == '')
                fatal_error($txt['smfcontact_errmessage'], false);

			if (isset($_REQUEST['email']))
            	$email = trim($_POST['email']);
			else
				$email = '';

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
                sendmail($webmaster_email, $subject, $m, $email, null, 'contact');
            else
                sendmail($modSettings['smfcontactpage_email'], $subject, $m, $email, null, 'contact');


            //  Check if we are posting to a board
            if (!empty($modSettings['smfcontactpage_board']))
            {

                $m = $txt['smfcontact_formname'] . $from . "\n";
                $m .= $txt['smfcontact_formemail'] . $email . "\n";
                $m .= $txt['smfcontact_ip'] . $_SERVER['REMOTE_ADDR'] . "\n";
                $m .= $txt['smfcontact_formmessage'];
                $m .= $message;
                $m .= "\n";


                $msgOptions = array(
                    'id' => 0,
                    'subject' => $subject,
                    'body' => '[b]' . $subject . "[/b]\n\n$m",
                    'icon' => 'xx',
                    'smileys_enabled' => 1,
                    'attachments' => array(),
                );
                $topicOptions = array(
                    'id' => 0,
                    'board' =>  $modSettings['smfcontactpage_board'],
                    'poll' => null,
                    'lock_mode' => 0,
                    'sticky_mode' => null,
                    'mark_as_read' => true,
                );
                $posterOptions = array(
                    'id' => $user_info['id'],
                    'update_post_count' => !$user_info['is_guest'],
                );
                // Fix height & width of posted image in message
                preparsecode($msgOptions['body']);


                createPost($msgOptions, $topicOptions, $posterOptions);


                require_once($sourcedir . '/Post.php');


                if (function_exists("notifyMembersBoard")) {
                    $notifyData = array(
                        'body' => $msgOptions['body'],
                        'subject' => $msgOptions['subject'],
                        'name' => $user_info['name'],
                        'poster' => $user_info['id'],
                        'msg' => $msgOptions['id'],
                        'board' => $modSettings['smfcontactpage_board'],
                        'topic' => $topicOptions['id'],
                    );
                    notifyMembersBoard($notifyData);
                } else {
                    // for 2.1
                    $smcFunc['db_insert']('',
                        '{db_prefix}background_tasks',
                        array('task_file' => 'string', 'task_class' => 'string', 'task_data' => 'string', 'claimed_time' => 'int'),
                        array('$sourcedir/tasks/CreatePost-Notify.php', 'CreatePost_Notify_Background', $smcFunc['json_encode'](array(
                            'msgOptions' => $msgOptions,
                            'topicOptions' => $topicOptions,
                            'posterOptions' => $posterOptions,
                            'type' => $topicOptions['id'] ? 'reply' : 'topic',
                        )), 0),
                        array('id_task')
                    );


                }

            }

			
		// Load the main contact template
		$context['sub_template']  = 'send';

		// Set the page title
		$context['page_title'] = $mbname . $txt['smfcontact_titlesent'];

		}
	}
	else
	{




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