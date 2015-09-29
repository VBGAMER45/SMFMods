

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
        $txt = array('package_uninstall_done' => '');
}
//      Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
        die('<b>Error:</b> Cannot uninstall - please verify you put this in the same place as SMF\'s SSI.php.');

//      Start the list
$output = '<ul>';

require_once($sourcedir . '/Subs-PrettyUrls.php');

//      Update the pretty_filters setting
$prettyFilters = unserialize($modSettings['pretty_filters']);
unset($prettyFilters['seo4smf']);
updateSettings(array('pretty_filters' => addslashes(serialize($prettyFilters))));
output .= '<li>Removing the seo4smf rewrite rules</li>';

//      Update everything now
pretty_update_filters();
$output .= '<li>Processing the installed filters</li></ul>';

//      Output the list of database changes
$txt['package_uninstall_done'] = $output . $txt['package_uninstall_done'];
if (isset($standalone))
{
        echo '<title>Uninstalling Pretty URLs - SEO4SMF Redirection Patch 0.8</title>
<h1>Uninstalling Pretty URLs - SEO4SMF Redirection Patch 0.8</h1>
<h2>Database changes</h2>
', $txt['package_uninstall_done'];
}

?>