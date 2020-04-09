<?php
/**
 *  hCaptcha for SMF by vbgamer45
 *  @license   https://choosealicense.com/licenses/bsd-3-clause/ BSD-3-Clause
 *
 *  Based on reCAPTCHA for SMF
 * @author    Michael Johnson <youngmug@animeneko.net>
 * @copyright 2007-2018 Michael Johnson
 * @license   https://choosealicense.com/licenses/bsd-3-clause/ BSD-3-Clause
 */

function load_hcaptcha()
{
    global $context;

    $context['html_headers'] .= '<script src="https://hcaptcha.com/1/api.js" async defer></script>';
    loadLanguage('hcaptcha');
    loadTemplate(false, 'hcaptcha');
}
