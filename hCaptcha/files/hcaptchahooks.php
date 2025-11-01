<?php

/*
hCaptcha
by vbgamer45 https://www.smfhacks.com
*/

if (!defined('SMF'))
    die('Hacking attempt...');



function hcaptcha_integrate_spam_settings(&$config_vars)
{
    global $sourcedir, $modSettings, $txt;
    $config_vars[] =array('title', 'hcaptcha_configure');
    $config_vars[] =array('desc', 'hcaptcha_configure_desc', 'class' => 'windowbg');
    $config_vars[] = array('check', 'hcaptcha_enabled', 'subtext' => $txt['hcaptcha_enable_desc']);
    $config_vars[] =array('text', 'hcaptcha_public_key');
    $config_vars[] =array('text', 'hcaptcha_private_key');
    $config_vars[] =array('select', 'hcaptcha_theme', array('light' => $txt['hcaptcha_theme_light'],
                    'dark' => $txt['hcaptcha_theme_dark']));
}

function hcaptcha_integrate_create_control_verification_pre(&$verificationOptions, $do_test)
{
    global $modSettings, $context;
    $verificationOptions['can_hcaptcha'] = 0;

    if ($modSettings['hcaptcha_enabled'] == 1 && !empty($modSettings['hcaptcha_public_key'])  && !empty($modSettings['hcaptcha_private_key']))
    {
        $verificationOptions['can_recaptcha'] = 0;
        $verificationOptions['show_visual'] = 0;
        $verificationOptions['can_hcaptcha'] = 1;
        $context['controls']['verification'][$verificationOptions['id']]['can_recaptcha'] = 0;
        $context['controls']['verification'][$verificationOptions['id']]['show_visual'] = 0;
        $context['controls']['verification'][$verificationOptions['id']]['can_hcaptcha'] = 1;

    }
}

function hcaptcha_integrate_create_control_verification_test(&$thisVerification, &$verification_errors)
{
    global $modSettings, $sourcedir;
	$verification_errors = array();

    if ($thisVerification['can_hcaptcha'] == 1)
     {

         $thisVerification['show_visual'] = 0;
         // Verify the captcha
         if(isset($_REQUEST["h-captcha-response"]))
         {
             require_once($sourcedir . '/Subs-Package.php');
             $response = fetch_web_data('https://hcaptcha.com/siteverify?secret=' . $modSettings['hcaptcha_private_key'] . '&response=' . $_REQUEST["h-captcha-response"] . '&remoteip=' . $_SERVER["REMOTE_ADDR"]);
             $response = json_decode($response, true);

             if (true != $response["success"])
                 $verification_errors[] = 'hcaptcha_verification_code';

         }
         else
             $verification_errors[] = 'hcaptcha_verification_code';





     }
}



