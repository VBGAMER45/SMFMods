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

        // Added per request rather than in package-info.xml: the settings table key only covers the first 30 characters,
        // which this hook name shares with integrate_create_control_verification_pre, so both can't be stored
        if ($do_test)
            add_integration_function('integrate_create_control_verification_test', 'hcaptcha_integrate_create_control_verification_test', false);
    }
}

function hcaptcha_integrate_create_control_verification_test($thisVerification, &$verification_errors)
{
    global $modSettings, $sourcedir, $user_info;

    if (empty($thisVerification['can_hcaptcha']))
        return;

    if (empty($_REQUEST['h-captcha-response']) || !is_string($_REQUEST['h-captcha-response']))
    {
        $verification_errors[] = 'hcaptcha_verification_code';
        return;
    }

    // siteverify only accepts a POST body. Pass '&' explicitly since SMF sets arg_separator.output to &amp;
    require_once($sourcedir . '/Subs-Package.php');
    $raw = fetch_web_data('https://api.hcaptcha.com/siteverify', http_build_query(array(
        'secret' => $modSettings['hcaptcha_private_key'],
        'response' => $_REQUEST['h-captcha-response'],
        'remoteip' => $user_info['ip'],
        'sitekey' => $modSettings['hcaptcha_public_key'],
    ), '', '&'));
    $response = json_decode((string) $raw, true);

    if (!empty($response['success']))
        return;

    $verification_errors[] = 'hcaptcha_verification_code';

    // Log connection and setup problems, but not ordinary bad or expired captchas
    $user_errors = array('invalid-input-response', 'expired-input-response', 'already-seen-response', 'invalid-or-already-seen-response');
    $error_codes = isset($response['error-codes']) ? (array) $response['error-codes'] : array();

    if ($raw === false)
        log_error('hCaptcha: no reply from api.hcaptcha.com', 'general');
    elseif (empty($error_codes) || array_diff($error_codes, $user_errors))
        log_error('hCaptcha: verification rejected (' . (empty($error_codes) ? 'unexpected reply' : implode(', ', $error_codes)) . ')', 'general');
}



