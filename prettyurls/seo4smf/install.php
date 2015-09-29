<?php

/*******************************************************************************
        ATTENTION: If you are trying to install this manually, you should try
        the package manager. If that does not work, you can run this file by
        accessing it by url. Please ensure it is in the same location as your
        forum's SSI.php file.
*******************************************************************************/

//      Pretty URLs - seo4smf v0.8

//      If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
        require_once(dirname(__FILE__) . '/SSI.php');
        $standalone = true;
        $txt = array('package_installed_done' => '');
}
//      Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
        die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s SSI.php.');

//      Start the list
$output = '<ul>';

require_once($sourcedir . '/Subs-PrettyUrls.php');

//      Update the pretty_filters setting
$prettyFilters = unserialize($modSettings['pretty_filters']);
$prettyFilters['seo4smf'] = array(
        'id' => 'seo4smf',
        'enabled' => 1,
        'rewrite' => array(
                'priority' => 5,
                'rule' => 'RewriteRule ^(.*)-b([0-9]*)\.([0-9]*)/;(.*) index.php?board=$2.$3;$4 [L]
RewriteRule ^(.*)-b([0-9]*)\.([0-9]*)/$ index.php?board=$2.$3 [L]
RewriteRule ^(.*)-b([0-9]*)\.([0-9])$ index.php?board=$2.$3 [L]
RewriteRule ^(.*)-t([0-9]*)\.([0-9]*)\.html;((\?:from|msg|new)[0-9]*);(.*)$ index.php?topic=$2.$4;$6 [L]
RewriteRule ^(.*)-t([0-9]*)\.([0-9]*)\.html;((\?:from|msg|new)[0-9]*) index.php?topic=$2.$4 [L]
RewriteRule ^(.*)-t([0-9]*)\.([0-9]*)\.html;(.*)$ index.php?topic=$2.$3;$4 [L]
RewriteRule ^(.*)-t([0-9]*)\.([0-9]*)\.html$ index.php?topic=$2.$3 [L]',
        ),
);
updateSettings(array('pretty_filters' => addslashes(serialize($prettyFilters))));
$output .= '<li>Adding the seo4smf rewrite rules</li>';

//      Update everything now
pretty_update_filters();
$output .= '<li>Processing the installed filters</li></ul>';

//      Output the list of database changes
$txt['package_installed_done'] = $output . $txt['package_installed_done'];
if (isset($standalone))
{
        echo '<title>Installing Pretty URLs - SEO4SMF Redirection Patch 0.8</title>
<h1>Installing Pretty URLs - SEO4SMF Redirection Patch 0.8</h1>
<h2>Database changes</h2>
', $txt['package_installed_done'];
}

?>