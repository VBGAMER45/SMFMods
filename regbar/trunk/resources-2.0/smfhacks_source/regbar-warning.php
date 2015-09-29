<?php
    /**
     * Regbar Warning (regbar-warning_)
     *
     * @file ./smfhacks_source/regbar-warning.php
     * @author SMFHacks <http://www.smfhacks.com/>
     * @copyright SMFHacks.com Team, 2013
     *
     * @version 1.0.5
     */
     
    if (!defined('SMF'))
            die('Hacking attempt...');
     
    function RegbarWarning()
    {
            global $context, $settings;
            loadLanguage('smfhacks_languages/regbar-warning');
            loadTemplate('smfhacks_templates/regbar-warning');
            $context['html_headers'] .= "\n" . '
           <link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/smfhacks_css/regbar-warning.css" />
       ';
            $context['insert_after_template'] .= template_regbar_warning();
    }
