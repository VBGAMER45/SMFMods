<?php
/**********************************************************************************
 * SSI_Garage.php                                                                  *
 ***********************************************************************************
 * SMF Garage: Simple Machines Forum Garage (MOD)                                  *
 * =============================================================================== *
 * Software Version:           SMF Garage 3.0.0                                    *
 * Install for:                2.0.9-2.0.99, 2.1.0-2.1.99                         *
 * Original Developer:         RRasco (http://www.smfgarage.com)                   *
 * Copyright 2026 by:          vbgamer45 (https://www.smfhacks.com)               *
 * Copyright 2015 by:          Bruno Alves (margarett.pt@gmail.com                 *
 * Copyright 2007-2011 by:     SMF Garage (http://www.smfgarage.com)               *
 *                             RRasco (rrasco@smfgarage.com)                       *
 * phpBB Garage by:            Esmond Poynton (esmond.poynton@gmail.com)           *
 ***********************************************************************************
 * See the "SMF_Garage_License.txt" file for details.                              *
 *              http://www.opensource.org/licenses/BSD-3-Clause                    *
 **********************************************************************************/


// Don't do anything if SMF is already loaded.
if (defined('SMF')) {
    return true;
}

define('SMF', 'SSI_Garage');

// We're going to want a few globals... these are all set later.
global $time_start, $maintenance, $msubject, $mmessage, $mbname, $language;
global $boardurl, $boarddir, $sourcedir, $webmaster_email, $cookiename;
global $db_server, $db_name, $db_user, $db_prefix, $db_persist, $db_error_send, $db_last_error;
global $db_connection, $modSettings, $context, $sc, $user_info, $topic, $board, $txt;
global $smcFunc, $ssi_db_user, $scripturl, $ssi_db_passwd, $db_passwd, $cachedir;
global $smfgSettings;

// Remember the current configuration so it can be set back.
$ssi_magic_quotes_runtime = function_exists('get_magic_quotes_gpc') && get_magic_quotes_runtime();
if (function_exists('set_magic_quotes_runtime')) {
    @set_magic_quotes_runtime(0);
}
$time_start = microtime();

// Just being safe...
foreach (array('db_character_set', 'cachedir') as $variable) {
    if (isset($GLOBALS[$variable])) {
        unset($GLOBALS[$variable]);
    }
}

// Get the forum's settings for database and file paths.
require_once(dirname(__FILE__) . '/Settings.php');

// Make absolutely sure the cache directory is defined.
if ((empty($cachedir) || !file_exists($cachedir)) && file_exists($boarddir . '/cache')) {
    $cachedir = $boarddir . '/cache';
}

$ssi_error_reporting = error_reporting(defined('E_STRICT') ? E_ALL | E_STRICT : E_ALL);
/* Set this to one of three values depending on what you want to happen in the case of a fatal error.
    false:  Default, will just load the error sub template and die - not putting any theme layers around it.
    true:   Will load the error sub template AND put the SMF layers around it (Not useful if on total custom pages).
    string: Name of a callback function to call in the event of an error to allow you to define your own methods. Will die after function returns.
*/
$ssi_on_error_method = false;

// Don't do john didley if the forum's been shut down competely.
if ($maintenance == 2 && (!isset($ssi_maintenance_off) || $ssi_maintenance_off !== true)) {
    die($mmessage);
}

// Fix for using the current directory as a path.
if (substr($sourcedir, 0, 1) == '.' && substr($sourcedir, 1, 1) != '.') {
    $sourcedir = dirname(__FILE__) . substr($sourcedir, 1);
}

// Load the important includes.
require_once($sourcedir . '/QueryString.php');
require_once($sourcedir . '/Subs.php');
require_once($sourcedir . '/Errors.php');
require_once($sourcedir . '/Load.php');
require_once($sourcedir . '/Security.php');

// Now the garage includes.
require_once($sourcedir . '/GarageFunctions.php');

// Using an pre-PHP 5.1 version?
if (@version_compare(PHP_VERSION, '5.1') == -1) {
    require_once($sourcedir . '/Subs-Compat.php');
}

// Create a variable to store some SMF specific functions in.
$smcFunc = array();

// Initate the database connection and define some database functions to use.
loadDatabase();

// Load installed 'Mods' settings.
reloadSettings();
// Clean the request variables.
cleanRequest();

// Seed the random generator?
if (empty($modSettings['rand_seed']) || mt_rand(1, 250) == 69) {
    smf_seed_generator();
}

// Check on any hacking attempts.
if (isset($_REQUEST['GLOBALS']) || isset($_COOKIE['GLOBALS'])) {
    die('Hacking attempt...');
} elseif (isset($_REQUEST['ssi_theme']) && (int)$_REQUEST['ssi_theme'] == (int)$ssi_theme) {
    die('Hacking attempt...');
} elseif (isset($_COOKIE['ssi_theme']) && (int)$_COOKIE['ssi_theme'] == (int)$ssi_theme) {
    die('Hacking attempt...');
} elseif (isset($_REQUEST['ssi_layers'], $ssi_layers) && (@get_magic_quotes_gpc() ? stripslashes($_REQUEST['ssi_layers']) : $_REQUEST['ssi_layers']) == $ssi_layers) {
    die('Hacking attempt...');
}
if (isset($_REQUEST['context'])) {
    die('Hacking attempt...');
}

// Make sure wireless is always off.
define('WIRELESS', false);

// Gzip output? (because it must be boolean and true, this can't be hacked.)
if (isset($ssi_gzip) && $ssi_gzip === true && @ini_get('zlib.output_compression') != '1' && @ini_get('output_handler') != 'ob_gzhandler' && @version_compare(PHP_VERSION,
        '4.2.0') != -1
) {
    ob_start('ob_gzhandler');
} else {
    $modSettings['enableCompressedOutput'] = '0';
}

// Primarily, this is to fix the URLs...
//ob_start('ob_sessrewrite');

// Start the session... known to scramble SSI includes in cases...
if (!headers_sent()) {
    loadSession();
} else {
    if (isset($_COOKIE[session_name()]) || isset($_REQUEST[session_name()])) {
        // Make a stab at it, but ignore the E_WARNINGs generated because we can't send headers.
        $temp = error_reporting(error_reporting() & !E_WARNING);
        loadSession();
        error_reporting($temp);
    }

    if (!isset($_SESSION['session_value'])) {
        $_SESSION['session_var'] = substr(md5(mt_rand() . session_id() . mt_rand()), 0, rand(7, 12));
        $_SESSION['session_value'] = md5(session_id() . mt_rand());
    }
    $sc = $_SESSION['session_value'];
}

// Get rid of $board and $topic... do stuff loadBoard would do.
unset($board, $topic);
$user_info['is_mod'] = false;
$context['user']['is_mod'] = &$user_info['is_mod'];
$context['linktree'] = array();

// Load the user and their cookie, as well as their settings.
loadUserSettings();

// Load the current user's permissions....
loadPermissions();

// Load the current or SSI theme. (just use $ssi_theme = id_theme;)
loadTheme(isset($ssi_theme) ? (int)$ssi_theme : 0);

// Take care of any banning that needs to be done.
if (isset($_REQUEST['ssi_ban']) || (isset($ssi_ban) && $ssi_ban === true)) {
    is_not_banned();
}

// Do we allow guests in here?
if (empty($ssi_guest_access) && empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && basename($_SERVER['PHP_SELF']) != 'SSI.php') {
    require_once($sourcedir . '/Subs-Auth.php');
    KickGuest();
    obExit(null, true);
}

// Load the stuff like the menu bar, etc.
if (isset($ssi_layers)) {
    $context['template_layers'] = $ssi_layers;
    template_header();
} else {
    setupThemeContext();
}

// Make sure they didn't muss around with the settings... but only if it's not cli.
if (isset($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['is_cli']) && session_id() == '') {
    trigger_error($txt['ssi_session_broken'], E_USER_NOTICE);
}

// Without visiting the forum this session variable might not be set on submit.
if (!isset($_SESSION['USER_AGENT']) && (!isset($_GET['ssi_function']) || $_GET['ssi_function'] !== 'pollVote')) {
    $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
}

// Call a function passed by GET.
if (isset($_GET['ssi_function']) && function_exists('ssi_' . $_GET['ssi_function']) && (!empty($modSettings['allow_guestAccess']) || !$user_info['is_guest'])) {
    call_user_func('ssi_' . $_GET['ssi_function']);
    exit;
}
if (isset($_GET['ssi_function'])) {
    exit;
} // You shouldn't just access SSI.php directly by URL!!
elseif (basename($_SERVER['PHP_SELF']) == 'SSI.php') {
    die(sprintf($txt['ssi_not_direct'], $user_info['is_admin'] ? '\'' . addslashes(__FILE__) . '\'' : '\'SSI.php\''));
}

error_reporting($ssi_error_reporting);
if (function_exists('set_magic_quotes_runtime')) {
    @set_magic_quotes_runtime($ssi_magic_quotes_runtime);
}

return true;

// SSI functions below here.....

// CSS and JS includes
function ssi_smfg_includes($output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $boardurl, $settings, $smcFunc;

    $output = '
    <!-- SMF Styles -->
    <link rel="stylesheet" href="' . $settings['default_theme_url'] . '/style.css" type="text/css" /> 
    
    <!-- jQuery Includes -->
    <script type="text/javascript" src="' . $settings['default_theme_url'] . '/jquery-1.6.1.min.js"></script>       
    
    <!-- Lightbox Includes -->
    <link rel="stylesheet" href="' . $settings['default_theme_url'] . '/shadowbox.css" type="text/css" media="screen" />
    <script type="text/javascript" src="' . $settings['default_theme_url'] . '/shadowbox.js"></script> 
    <script type="text/javascript">
    $(document).ready(function(){      
        Shadowbox.init();
    });        
    </script>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'return') {
            return $output;
        }
    }
}

// **ONLY CSS includes
function ssi_smfg_css_includes($output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $boardurl, $settings, $smcFunc;

    $output = '
    <!-- SMF Styles -->
    <link rel="stylesheet" href="' . $settings['default_theme_url'] . '/style.css" type="text/css" />';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'return') {
            return $output;
        }
    }
}

// **ONLY JS includes
function ssi_smfg_js_includes($output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $boardurl, $settings, $smcFunc;

    $output = '
    <!-- jQuery Includes -->
    <script type="text/javascript" src="' . $settings['default_theme_url'] . '/jquery-1.6.1.min.js"></script> 
    
    <!-- Lightbox Includes -->
    <link rel="stylesheet" href="' . $settings['default_theme_url'] . '/shadowbox.css" type="text/css" media="screen" />
    <script type="text/javascript" src="' . $settings['default_theme_url'] . '/shadowbox.js"></script> 
    <script type="text/javascript">
    $(document).ready(function(){      
        Shadowbox.init();
    });        
    </script>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'return') {
            return $output;
        }
    }
}

// Show the featured vehicle
function ssi_smfg_featuredVehicle($width = 200, $description = 1, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $boardurl, $settings, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    // Get the featured vehicle ID
    $context['featured_vehicle']['id'] = getFeaturedVehicle();

    if (isset($context['featured_vehicle']['id']) && !empty($context['featured_vehicle']['id'])) {
        $request = $smcFunc['db_query']('', '
            SELECT u.real_name, CONCAT_WS(" ", v.made_year, mk.make, md.model), v.user_id
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
            WHERE v.id = {int:id}
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND v.user_id = u.id_member',
            array(
                'id' => $context['featured_vehicle']['id'],
            )
        );
        list($context['featured_vehicle']['owner'],
            $context['featured_vehicle']['vehicle'],
            $context['featured_vehicle']['user_id']) = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);

        // Check if there is a hilite image
        $request = $smcFunc['db_query']('', '
            SELECT image_id
            FROM {db_prefix}garage_vehicles_gallery
            WHERE vehicle_id = {int:id}
                AND hilite = 1',
            array(
                'id' => $context['featured_vehicle']['id'],
            )
        );
        list($context['featured_vehicle']['image_id']) = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);

        // Select image data if there is any
        $context['featured_vehicle']['image'] = "";
        if (isset($context['featured_vehicle']['image_id'])) {

            $request = $smcFunc['db_query']('', '
                SELECT attach_location, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_desc, is_remote 
                FROM {db_prefix}garage_images 
                WHERE attach_id = {int:image_id}',
                array(
                    'image_id' => $context['featured_vehicle']['image_id'],
                )
            );
            list($context['featured_vehicle']['attach_location'],
                $context['featured_vehicle']['attach_file'],
                $context['featured_vehicle']['attach_thumb_location'],
                $context['featured_vehicle']['attach_thumb_width'],
                $context['featured_vehicle']['attach_thumb_height'],
                $context['featured_vehicle']['attach_desc'],
                $context['featured_vehicle']['is_remote']) = $smcFunc['db_fetch_row']($request);
            $smcFunc['db_free_result']($request);

            // Check to see if the image is remote or not and build appropriate links
            if ($context['featured_vehicle']['is_remote'] == 1) {
                $context['featured_vehicle']['attach_location'] = urldecode($context['featured_vehicle']['attach_file']);
            } else {
                $context['featured_vehicle']['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['featured_vehicle']['attach_location'];
            }

            // If there is an image attached, link to it
            if (isset($context['featured_vehicle']['attach_location'])) {
                $context['featured_vehicle']['image'] = "<a href=\"" . $context['featured_vehicle']['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['featured_vehicle']['vehicle']) . "\"><img src=\"" . $boardurl . "/" . $smfgSettings['upload_directory'] . 'cache/' . $context['featured_vehicle']['attach_thumb_location'] . "\" width=\"" . $context['featured_vehicle']['attach_thumb_width'] . "\" height=\"" . $context['featured_vehicle']['attach_thumb_height'] . "\" alt=\"Featured Vehicle\" /></a><br />";
            }

        }
    }

    $output = '';

    // Generate output    
    $output .= '
        <div style="width: ' . $width . 'px; white-space: nowrap;">
            <table border="0" cellspacing="0" cellpadding="0" align="center" class="tborder">
                <tr>
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $smfgSettings['featured_vehicle_description'] . '
                    </span></h4></div></td> 
                </tr>
                
                <tr>
                    <td>
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">';
    if (isset($context['featured_vehicle']['id']) && !empty($context['featured_vehicle']['id'])) {
        $output .= '
                            <tr>
                                <td width="100%" valign="top" align="center">
                                ' . $context['featured_vehicle']['image'] . '<a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['featured_vehicle']['id'] . '">' . garage_title_clean($context['featured_vehicle']['vehicle']) . '</a><br />' . $txt['smfg_owner'] . ':&nbsp;<a href="' . $scripturl . '?action=profile;u=' . $context['featured_vehicle']['user_id'] . '">' . $context['featured_vehicle']['owner'] . '</a></td>
                            </tr>';
    } else {
        $output .= '
                            <tr>
                                <td width="100%" valign="top" align="center">' . $txt['smfg_no_vid'] . '</td>
                            </tr>';
    }
    $output .= '
                        </table>
                    </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                    </td>
                </tr>
            </table>';

    $output .= '
        </div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['featured_vehicle'];
        }
    }
}

// Show the SMF Garage stats
function ssi_smfg_garageStats($style = 0, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $settings, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    // Get total number of vehicles
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_vehicles',
        array(// no values
        )
    );
    $context['garageStats']['total_vehicles'] = $smcFunc['db_num_rows']($request);
    $smcFunc['db_free_result']($request);
    $context['total_vehicles'] = number_format($context['garageStats']['total_vehicles'], 0, '.', ',');

    // Get total number of mods
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_modifications',
        array(// no values
        )
    );
    $context['garageStats']['total_mods'] = $smcFunc['db_num_rows']($request);
    $smcFunc['db_free_result']($request);
    $context['garageStats']['total_mods'] = number_format($context['garageStats']['total_mods'], 0, '.', ',');

    // Get total number of comments
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_guestbooks',
        array(// no values
        )
    );
    $context['garageStats']['total_comments'] = $smcFunc['db_num_rows']($request);
    $smcFunc['db_free_result']($request);
    $context['garageStats']['total_comments'] = number_format($context['garageStats']['total_comments'], 0, '.', ',');

    // Get total number of views
    $context['garageStats']['total_views'] = 0;
    $request = $smcFunc['db_query']('', '
        SELECT views
        FROM {db_prefix}garage_vehicles',
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context[$count]['views']) = $row;
        $context['garageStats']['total_views'] += $context[$count]['views'];
        $count++;
    }
    $smcFunc['db_free_result']($request);
    $context['garageStats']['total_views'] = number_format($context['garageStats']['total_views'], 0, '.', ',');

    $output = '';

    if ($style == 0) {
        $output .= '
    <table border="0" cellspacing="0" cellpadding="4">
        <tr>
            <td>' . $txt['smfg_total_vehicles_caps'] . ':&nbsp;<b>' . $context['garageStats']['total_vehicles'] . '</b>
            <br />
            ' . $txt['smfg_total_mods'] . ':&nbsp;<b>' . $context['garageStats']['total_mods'] . '</b>
            <br />
            ' . $txt['smfg_total_comments'] . ':&nbsp;<b>' . $context['garageStats']['total_comments'] . '</b>
            <br />
            ' . $txt['smfg_total_views'] . ':&nbsp;<b>' . $context['garageStats']['total_views'] . '</b>
            </td>
        </tr>
    </table>';
    } else {
        if ($style == 1) {
            $bullet = '<img src="' . $settings['images_url'] . '/TPdivider.gif" alt="" border="0" style="margin:0 2px 0 0;" />';
            $bullet2 = '<img src="' . $settings['images_url'] . '/TPdivider2.gif" alt="" border="0" style="margin:0 2px 0 0;" />';
            $output .= '
            <div class="smalltext" style="font-family: verdana, arial, sans-serif;">
            ' . $bullet . $txt['smfg_total_vehicles_caps'] . ': ' . $context['garageStats']['total_vehicles'] . '<br />
            ' . $bullet . $txt['smfg_total_mods'] . ': ' . $context['garageStats']['total_mods'] . '<br />
            ' . $bullet . $txt['smfg_total_comments'] . ': ' . $context['garageStats']['total_comments'] . '<br />
            ' . $bullet . $txt['smfg_total_views'] . ': ' . $context['garageStats']['total_views'] . '<br />
            </div>';
        }
    }

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['garageStats'];
        }
    }
}

// Show the newest vehicles
function ssi_smfg_newestVehicles($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_new_vehicles'] . '</h3>';
    }

    // Get the five newest vehicles
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT v.id
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
            WHERE v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
            ORDER BY v.date_created DESC
            LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['newest_vehicles'][$count]['id']) = $row;
        // Now collect data for each vehicle
        $request2 = $smcFunc['db_query']('', '
                    SELECT v.user_id, v.date_created, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
                    FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
                    WHERE v.id = {int:id}
                        AND v.user_id = u.id_member
                        AND v.make_id = mk.id
                        AND v.model_id = md.id',
            array(
                'id' => $context['newest_vehicles'][$count]['id'],
            )
        );
        list($context['newest_vehicles'][$count]['user_id'],
            $context['newest_vehicles'][$count]['date_created'],
            $context['newest_vehicles'][$count]['vehicle'],
            $context['newest_vehicles'][$count]['memberName']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        $output .= '
                    <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['newest_vehicles'][$count]['id'] . '">' . $context['newest_vehicles'][$count]['vehicle'] . '</a></div>
                    <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['newest_vehicles'][$count]['user_id'] . '">' . $context['newest_vehicles'][$count]['memberName'] . '</a></b></div>
                    <div class="smalltext">' . $txt['smfg_created'] . ': <b>' . date('M d, Y',
                $context['newest_vehicles'][$count]['date_created']) . '</b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['newest_vehicles'];
        }
    }
}

// Show the last updated vehicles
function ssi_smfg_lastUpdatedVehicles($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_up_vehicles'] . '</h3>';
    }

    // Get the five latest updated vehicles
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT v.id
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
            WHERE v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
            ORDER BY v.date_updated DESC
            LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['last_updated_veh'][$count]['id']) = $row;
        // Now collect data for each vehicle
        $request2 = $smcFunc['db_query']('', '
                    SELECT v.user_id, v.date_updated, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
                    FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
                    WHERE v.id = {int:id}
                        AND v.user_id = u.id_member
                        AND v.make_id = mk.id
                        AND v.model_id = md.id',
            array(
                'id' => $context['last_updated_veh'][$count]['id'],
            )
        );
        list($context['last_updated_veh'][$count]['user_id'],
            $context['last_updated_veh'][$count]['date_updated'],
            $context['last_updated_veh'][$count]['vehicle'],
            $context['last_updated_veh'][$count]['memberName']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        $output .= '
                    <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_updated_veh'][$count]['id'] . '">' . $context['last_updated_veh'][$count]['vehicle'] . '</a></div>
                    <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['last_updated_veh'][$count]['user_id'] . '">' . $context['last_updated_veh'][$count]['memberName'] . '</a></b></div>
                    <div class="smalltext">' . $txt['smfg_updated'] . ': <b>' . date('M d, Y',
                $context['last_updated_veh'][$count]['date_updated']) . '</b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['last_updated_veh'];
        }
    }
}

// Show the newest mods
function ssi_smfg_newestMods($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_new_mods'] . '</h3>';
    }

    // Get the five newest modifications
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
        SELECT m.id
        FROM {db_prefix}garage_modifications AS m, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_products AS p, {db_prefix}garage_business AS b
        WHERE m.vehicle_id = v.id
            AND v.make_id = mk.id
            AND v.model_id = md.id
            AND m.product_id = p.id
            AND p.business_id = b.id
            AND mk.pending != "1"
            AND md.pending != "1"
            AND m.pending != "1"
            AND v.pending != "1"
            AND p.pending != "1"
            AND b.pending != "1"
            ORDER BY m.date_created DESC
            LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['newest_mods'][$count]['id']) = $row;
        // Now collect data for each mod
        $request2 = $smcFunc['db_query']('', '
                SELECT p.title, m.vehicle_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), m.user_id, m.date_created, u.real_name
                FROM {db_prefix}garage_products AS p, {db_prefix}garage_modifications AS m, {db_prefix}members AS u, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE m.id = {int:id}
                    AND m.product_id = p.id
                    AND m.user_id = u.id_member
                    AND m.vehicle_id = v.id
                    AND v.make_id = mk.id
                    AND v.model_id = md.id',
            array(
                'id' => $context['newest_mods'][$count]['id'],
            )
        );
        list($context['newest_mods'][$count]['modification'],
            $context['newest_mods'][$count]['vid'],
            $context['newest_mods'][$count]['vehicle'],
            $context['newest_mods'][$count]['user_id'],
            $context['newest_mods'][$count]['date_created'],
            $context['newest_mods'][$count]['memberName']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['newest_mods'][$count]['vid'] . ';MID=' . $context['newest_mods'][$count]['id'] . '">' . $context['newest_mods'][$count]['modification'] . '</a></div>
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['newest_mods'][$count]['vid'] . '#modifications">' . $context['newest_mods'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['newest_mods'][$count]['user_id'] . '">' . $context['newest_mods'][$count]['memberName'] . '</a></b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['newest_mods'];
        }
    }
}

// Show the last updated mods
function ssi_smfg_lastUpdatedMods($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_up_mods'] . '</h3>';
    }

    // Get the five latest updated mods
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT m.id
            FROM {db_prefix}garage_modifications AS m, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_products AS p, {db_prefix}garage_business AS b
            WHERE m.vehicle_id = v.id
                AND m.product_id = p.id
                AND p.business_id = b.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND m.pending != "1"
                AND p.pending != "1"
                AND b.pending != "1"
            ORDER BY m.date_updated DESC
            LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['last_updated_mods'][$count]['id']) = $row;
        // Now collect data for each mod
        $request2 = $smcFunc['db_query']('', '
                    SELECT p.title, CONCAT_WS(" ", v.made_year, mk.make, md.model), m.vehicle_id, m.user_id, m.date_updated, u.real_name
                    FROM {db_prefix}garage_products AS p, {db_prefix}garage_modifications AS m, {db_prefix}members AS u, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                    WHERE m.id = {int:id}
                        AND m.product_id = p.id
                        AND m.user_id = u.id_member
                        AND m.vehicle_id = v.id
                        AND v.make_id = mk.id
                        AND v.model_id = md.id',
            array(
                'id' => $context['last_updated_mods'][$count]['id'],
            )
        );
        list($context['last_updated_mods'][$count]['modification'],
            $context['last_updated_mods'][$count]['vehicle'],
            $context['last_updated_mods'][$count]['vid'],
            $context['last_updated_mods'][$count]['user_id'],
            $context['last_updated_mods'][$count]['date_updated'],
            $context['last_updated_mods'][$count]['memberName']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['last_updated_mods'][$count]['id'] . ';MID=' . $context['last_updated_mods'][$count]['id'] . '">' . $context['last_updated_mods'][$count]['modification'] . '</a></div>
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_updated_mods'][$count]['vid'] . '#modifications">' . $context['last_updated_mods'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['last_updated_mods'][$count]['user_id'] . '">' . $context['last_updated_mods'][$count]['memberName'] . '</a></b></div>
                <div class="smalltext">' . $txt['smfg_updated'] . ': <b>' . date('M d, Y',
                $context['last_updated_mods'][$count]['date_updated']) . '</b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['last_updated_mods'];
        }
    }
}

// Show the most viewed
function ssi_smfg_mostViews($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_most_views'] . '</h3>';
    }

    // Get the five most viewed vehicles
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT v.id
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
            WHERE v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                ORDER BY v.views DESC
                LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['most_views'][$count]['id']) = $row;
        // Now collect data for each vehicle
        $request2 = $smcFunc['db_query']('', '
                    SELECT v.user_id, v.views, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
                    FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
                    WHERE v.id = {int:id}
                        AND v.user_id = u.id_member
                        AND v.make_id = mk.id
                        AND v.model_id = md.id',
            array(
                'id' => $context['most_views'][$count]['id'],
            )
        );
        list($context['most_views'][$count]['user_id'],
            $context['most_views'][$count]['views'],
            $context['most_views'][$count]['vehicle'],
            $context['most_views'][$count]['memberName']) = $smcFunc['db_fetch_row']($request2);
        $context['most_views'][$count]['views'] = number_format($context['most_views'][$count]['views'], 0, '.', ',');
        $smcFunc['db_free_result']($request2);

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['most_views'][$count]['id'] . '">' . $context['most_views'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['most_views'][$count]['user_id'] . '">' . $context['most_views'][$count]['memberName'] . '</a></b></div>
                <div class="smalltext">' . $txt['smfg_views'] . ': <b>' . $context['most_views'][$count]['views'] . '</b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['most_views'];
        }
    }
}

// Show the most spent
function ssi_smfg_mostSpent($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $modSettings, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_most_spent2'] . '</h3>';
    }

    // *************************************************************
    // WARNING: The query check is being disabled to allow for the following subselect.
    // It is imperative this is turned back on for security reasons.
    // *************************************************************
    $modSettings['disableQueryCheck'] = 1;
    // *************************************************************

    // Get the five vehicles with the most money spent
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT v.id, IFNULL(m.total_mods,0) + IFNULL(s.total_service,0) AS total_spent
                FROM {db_prefix}garage_vehicles AS v
                LEFT OUTER JOIN (
                    SELECT vehicle_id, SUM(price) + SUM(install_price) AS total_mods
                    FROM {db_prefix}garage_modifications AS m1, {db_prefix}garage_business AS b, {db_prefix}garage_products AS p
                    WHERE m1.manufacturer_id = b.id
                        AND m1.product_id = p.id
                        AND b.pending != "1"
                        AND m1.pending != "1"
                        AND p.pending != "1"
                    GROUP BY vehicle_id) AS m ON v.id = m.vehicle_id
                LEFT OUTER JOIN (
                    SELECT vehicle_id, SUM(price) AS total_service
                    FROM {db_prefix}garage_service_history AS s1, {db_prefix}garage_business AS b1
                    WHERE s1.garage_id = b1.id
                        AND b1.pending != "1"
                    GROUP BY vehicle_id) AS s ON v.id = s.vehicle_id, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
                WHERE v.make_id = mk.id
                    AND v.model_id = md.id
                    AND mk.pending != "1"
                    AND md.pending != "1"
                    AND v.pending != "1"
                    GROUP BY v.id, IFNULL(m.total_mods,0) + IFNULL(s.total_service,0)
                    ORDER BY total_spent DESC
                    LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['most_spent'][$count]['id'],
            $context['most_spent'][$count]['total_spent']) = $row;
        $context['most_spent'][$count]['total_spent'] = number_format($context['most_spent'][$count]['total_spent'], 2,
            '.', ',');
        // Now collect data for each vehicle
        $request2 = $smcFunc['db_query']('', '
                    SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name, c.title AS currency
                    FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_currency AS c, {db_prefix}members AS u
                    WHERE v.id = {int:id}
                        AND v.user_id = u.id_member
                        AND v.make_id = mk.id
                        AND v.model_id = md.id
                        AND v.currency = c.id',
            array(
                'id' => $context['most_spent'][$count]['id'],
            )
        );
        list($context['most_spent'][$count]['user_id'],
            $context['most_spent'][$count]['vehicle'],
            $context['most_spent'][$count]['memberName'],
            $context['most_spent'][$count]['currency']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['most_spent'][$count]['id'] . '">' . $context['most_spent'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['most_spent'][$count]['user_id'] . '">' . $context['most_spent'][$count]['memberName'] . '</a></b></div>
                <div class="smalltext">' . $txt['smfg_total'] . ': <b>' . $context['most_spent'][$count]['total_spent'] . ' ' . $context['most_spent'][$count]['currency'] . '</b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    // *************************************************************
    // WARNING: The query check is being enabled, this MUST BE DONE!
    // *************************************************************
    $modSettings['disableQueryCheck'] = 0;
    // *************************************************************

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['most_spent'];
        }
    }
}

// Show the most modified
function ssi_smfg_mostModified($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_most_modified2'] . '</h3>';
    }

    // Get the five most modified vehicles
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT v.id, COUNT( m.id ) AS total_mods
            FROM {db_prefix}garage_vehicles AS v 
            LEFT OUTER JOIN {db_prefix}garage_modifications AS m ON v.id = m.vehicle_id, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_products AS p, {db_prefix}garage_business AS b
            WHERE v.make_id = mk.id
                AND v.model_id = md.id
                AND m.product_id = p.id
                AND p.business_id = b.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND m.pending != "1"
                AND p.pending != "1"
                AND b.pending != "1"
                GROUP BY v.id
                ORDER BY total_mods DESC
                LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['most_mods'][$count]['id'],
            $context['most_mods'][$count]['total_mods']) = $row;
        // Now collect data for each vehicle
        $request2 = $smcFunc['db_query']('', '
                    SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
                    FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
                    WHERE v.id = {int:id}
                        AND v.user_id = u.id_member
                        AND v.make_id = mk.id
                        AND v.model_id = md.id',
            array(
                'id' => $context['most_mods'][$count]['id'],
            )
        );
        list($context['most_mods'][$count]['user_id'],
            $context['most_mods'][$count]['vehicle'],
            $context['most_mods'][$count]['memberName']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['most_mods'][$count]['id'] . '#modifications">' . $context['most_mods'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['most_mods'][$count]['user_id'] . '">' . $context['most_mods'][$count]['memberName'] . '</a></b></div>
                <div class="smalltext">' . $txt['smfg_total_mods'] . ': <b>' . $context['most_mods'][$count]['total_mods'] . '</b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['most_mods'];
        }
    }
}

// Show the top quartermile
function ssi_smfg_topQmile($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_top_quartermiles'] . '</h3>';
    }

    // Get the five top quartermiles
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT q.id
            FROM {db_prefix}garage_quartermiles AS q, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
            WHERE q.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND q.pending != "1"
                ORDER BY q.quart ASC
                LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['top_qm'][$count]['id']) = $row;
        // Now collect data for each vehicle
        $request2 = $smcFunc['db_query']('', '
                    SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name, q.vehicle_id, q.quart, q.quartmph
                    FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u, {db_prefix}garage_quartermiles AS q
                    WHERE q.id = {int:id}
                        AND v.id = q.vehicle_id
                        AND v.user_id = u.id_member
                        AND v.make_id = mk.id
                        AND v.model_id = md.id',
            array(
                'id' => $context['top_qm'][$count]['id'],
            )
        );
        list($context['top_qm'][$count]['user_id'],
            $context['top_qm'][$count]['vehicle'],
            $context['top_qm'][$count]['memberName'],
            $context['top_qm'][$count]['vid'],
            $context['top_qm'][$count]['quart'],
            $context['top_qm'][$count]['quartmph']) = $smcFunc['db_fetch_row']($request2);
        $context['top_qm'][$count]['qm_run'] = $context['top_qm'][$count]['quart'] . ' @ ' . $context['top_qm'][$count]['quartmph'] . ' MPH';
        $smcFunc['db_free_result']($request2);

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_quartermile;VID=' . $context['top_qm'][$count]['vid'] . ';QID=' . $context['top_qm'][$count]['id'] . '">' . $context['top_qm'][$count]['qm_run'] . '</a></div>
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['top_qm'][$count]['vid'] . '#quartermiles">' . $context['top_qm'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['top_qm'][$count]['user_id'] . '">' . $context['top_qm'][$count]['memberName'] . '</a></b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['top_qm'];
        }
    }
}

// Show the top dynorun
function ssi_smfg_topDyno($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_top_dynrons'] . '</h3>';
    }

    // Get the five top dyno runs
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT d.id
            FROM {db_prefix}garage_dynoruns AS d, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_business AS b
            WHERE d.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND d.dynocenter_id = b.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND d.pending != "1"
                AND b.pending != "1"
            ORDER BY bhp DESC
            LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['top_dr'][$count]['id']) = $row;
        // Now collect date for each dynorun
        $request2 = $smcFunc['db_query']('', '
                    SELECT d.vehicle_id, d.bhp, d.bhp_unit, d.torque, d.torque_unit, d.nitrous, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
                    FROM {db_prefix}garage_dynoruns AS d, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
                    WHERE d.id = {int:id}
                        AND d.vehicle_id = v.id
                        AND v.make_id = mk.id
                        AND v.model_id = md.id
                        AND v.user_id = u.id_member',
            array(
                'id' => $context['top_dr'][$count]['id'],
            )
        );
        list($context['top_dr'][$count]['vid'],
            $context['top_dr'][$count]['bhp'],
            $context['top_dr'][$count]['bhp_unit'],
            $context['top_dr'][$count]['torque'],
            $context['top_dr'][$count]['torque_unit'],
            $context['top_dr'][$count]['nitrous'],
            $context['top_dr'][$count]['user_id'],
            $context['top_dr'][$count]['vehicle'],
            $context['top_dr'][$count]['memberName']) = $smcFunc['db_fetch_row']($request2);
        $context['top_dr'][$count]['dynorun'] = $context['top_dr'][$count]['bhp'] . ' ' . $context['top_dr'][$count]['bhp_unit'] . ' / ' . $context['top_dr'][$count]['torque'] . ' ' . $context['top_dr'][$count]['torque_unit'] . ' / ' . $context['top_dr'][$count]['nitrous'];
        $smcFunc['db_free_result']($request2);

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_dynorun;VID=' . $context['top_dr'][$count]['vid'] . ';DID=' . $context['top_dr'][$count]['id'] . '">' . $context['top_dr'][$count]['dynorun'] . '</a></div>
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['top_dr'][$count]['vid'] . '#dynoruns">' . $context['top_dr'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['top_dr'][$count]['user_id'] . '">' . $context['top_dr'][$count]['memberName'] . '</a></b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['top_dr'];
        }
    }
}

// Show the top laptimes
function ssi_smfg_topLap($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_top_laptimes'] . '</h3>';
    }

    // Get the five top laps
    // Check the POST variable for a numeric value and set the query string if there is a track selected
    if (!isset($_POST['track_select'])) {
        $_POST['track_select'] = "";
    }
    if (ctype_digit($_POST['track_select']) === true) {
        if (!empty($_POST['track_select'])) {
            $spec_track = "AND l.track_id = " . $_POST['track_select'];
        } else {
            $spec_track = "";
        }
    } else {
        $spec_track = "";
    }

    // Get the lap data
    $request = $smcFunc['db_query']('', '
            SELECT l.id, l.vehicle_id, l.track_id, t.title, CONCAT_WS(" ", v.made_year, mk.make, md.model ), CONCAT_WS( ":", l.minute, l.second, l.millisecond ) AS time
            FROM {db_prefix}garage_laps AS l, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_tracks AS t
            WHERE l.vehicle_id = v.id
                AND l.track_id = t.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND l.pending != "1"
                AND t.pending != "1"
                AND v.pending != "1"
                ' . $spec_track . '
                ORDER BY time ASC
                LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['top_laps'][$count]['lid'],
            $context['top_laps'][$count]['vid'],
            $context['top_laps'][$count]['tid'],
            $context['top_laps'][$count]['track'],
            $context['top_laps'][$count]['vehicle'],
            $context['top_laps'][$count]['time']) = $row;

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['top_laps'][$count]['vid'] . '#laps">' . $context['top_laps'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_time'] . ': <a href="' . $scripturl . '?action=garage;sa=view_laptime;VID=' . $context['top_laps'][$count]['vid'] . ';LID=' . $context['top_laps'][$count]['lid'] . '">' . garage_title_clean($context['top_laps'][$count]['time']) . '</a></div>
                <div class="smalltext">' . $txt['smfg_track'] . ': <a href="' . $scripturl . '?action=garage;sa=view_track;TID=' . $context['top_laps'][$count]['tid'] . '">' . $context['top_laps'][$count]['track'] . '</a></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    $output .= '
                <form action="' . $_SERVER['PHP_SELF'] . '" name="trackSelect" method="post" style="padding:0; margin:0;">
                    <table border="0" cellpadding="0" cellspacing="0">
                        <tr id="topLaps2">
                            <td><span class="smalltext"><b>' . $txt['smfg_select_track'] . ':</b>&nbsp;<select name="track_select" onchange="trackSelect.submit();"><option value="">--------</option>';
    $output .= track_select($_POST['track_select'], true);
    $output .= '</select></span></td>
                        </tr>
                    </table>
                </form>';

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['top_laps'];
        }
    }
}

// Show the top rated
function ssi_smfg_topRated($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_top_rated2'] . '</h3>';
    }

    // Get the five top rated vehicles
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT r.vehicle_id, ' . $ratingfunc . '( r.rating ) AS rating, COUNT( r.id ) * 10 AS poss_rating
            FROM {db_prefix}garage_ratings AS r, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
            WHERE r.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                GROUP BY vehicle_id
                ORDER BY rating DESC
                LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['top_rated'][$count]['id'],
            $context['top_rated'][$count]['rating'],
            $context['top_rated'][$count]['poss_rating']) = $row;
        if ($context['top_rated'][$count]['rating'] > 0) {
            $context['top_rated'][$count]['rating'] = number_format($context['top_rated'][$count]['rating'], 2, '.',
                ',');
        } else {
            $context['top_rated'][$count]['rating'] = 0;
        }
        // Now collect data for each vehicle
        $request2 = $smcFunc['db_query']('', '
                    SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
                    FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
                    WHERE v.id = {int:id}
                        AND v.user_id = u.id_member
                        AND v.make_id = mk.id
                        AND v.model_id = md.id',
            array(
                'id' => $context['top_rated'][$count]['id'],
            )
        );
        list($context['top_rated'][$count]['user_id'],
            $context['top_rated'][$count]['vehicle'],
            $context['top_rated'][$count]['memberName']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['top_rated'][$count]['id'] . '">' . $context['top_rated'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['top_rated'][$count]['user_id'] . '">' . $context['top_rated'][$count]['memberName'] . '</a></b></div>
                <div class="smalltext">' . $txt['smfg_rating'] . ': <b>' . $context['top_rated'][$count]['rating'] . '</b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['top_rated'];
        }
    }
}

// Show the last blogs
function ssi_smfg_lastBlog($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_recent_blogs'] . '</h3>';
    }

    // Get the five latest vehicles with blog entries
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT b.id 
            FROM {db_prefix}garage_blog AS b, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
            WHERE b.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
            ORDER BY b.post_date DESC
            LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['last_blog'][$count]['id']) = $row;
        // Now collect data for each blog
        $request2 = $smcFunc['db_query']('', '
                    SELECT b.vehicle_id, b.blog_title, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
                    FROM {db_prefix}garage_blog AS b, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
                    WHERE b.id = {int:id}
                        AND b.vehicle_id = v.id
                        AND v.make_id = mk.id
                        AND v.model_id = md.id
                        AND v.user_id = u.id_member',
            array(
                'id' => $context['last_blog'][$count]['id'],
            )
        );
        list($context['last_blog'][$count]['vid'],
            $context['last_blog'][$count]['blog_title'],
            $context['last_blog'][$count]['user_id'],
            $context['last_blog'][$count]['vehicle'],
            $context['last_blog'][$count]['memberName']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        //Check the string length of blog title and trim it
        $blog_title_length = strlen($context['last_blog'][$count]['blog_title']);
        if ($blog_title_length >= 20) {
            $context['last_blog'][$count]['blog_title'] = substr($context['last_blog'][$count]['blog_title'], 0, 20);
            $context['last_blog'][$count]['blog_title'] .= "...";
        }

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_blog'][$count]['vid'] . '#blog">' . $context['last_blog'][$count]['blog_title'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_by'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['last_blog'][$count]['user_id'] . '">' . $context['last_blog'][$count]['memberName'] . '</a></b></div>
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_blog'][$count]['vid'] . '">' . $context['last_blog'][$count]['vehicle'] . '</a></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['last_blog'];
        }
    }
}

// Show the last services
function ssi_smfg_lastService($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_recently_serviced'] . '</h3>';
    }

    // Get the five latest vehicles with service history
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT sh.id 
            FROM {db_prefix}garage_service_history AS sh, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
            WHERE sh.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
            ORDER BY sh.date_created DESC
            LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['last_service'][$count]['id']) = $row;
        // Now collect data for each service
        $request2 = $smcFunc['db_query']('', '
                    SELECT sh.vehicle_id, sh.date_created, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
                    FROM {db_prefix}garage_service_history AS sh, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
                    WHERE sh.id = {int:id}
                        AND sh.vehicle_id = v.id
                        AND v.make_id = mk.id
                        AND v.model_id = md.id
                        AND v.user_id = u.id_member',
            array(
                'id' => $context['last_service'][$count]['id'],
            )
        );
        list($context['last_service'][$count]['vid'],
            $context['last_service'][$count]['date_created'],
            $context['last_service'][$count]['user_id'],
            $context['last_service'][$count]['vehicle'],
            $context['last_service'][$count]['memberName']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_service'][$count]['vid'] . '#services">' . $context['last_service'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['last_service'][$count]['user_id'] . '">' . $context['last_service'][$count]['memberName'] . '</a></b></div>
                <div class="smalltext">' . $txt['smfg_serviced'] . ': <b>' . date('M d, Y',
                $context['last_service'][$count]['date_created']) . '</b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['last_service'];
        }
    }
}

// Show the last comments
function ssi_smfg_lastComment($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_recent_comments'] . '</h3>';
    }

    // Get the five latest vehicles with comments
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT gb.id 
            FROM {db_prefix}garage_guestbooks AS gb, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
            WHERE gb.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND gb.pending != "1"
            ORDER BY post_date DESC
            LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['last_comments'][$count]['id']) = $row;
        // Now collect data for each comment
        $request2 = $smcFunc['db_query']('', '
                    SELECT gb.vehicle_id, gb.post_date, gb.author_id, CONCAT(u2.real_name, "\'s ", CONCAT_WS(" ", v.made_year, mk.make, md.model)), u.real_name
                    FROM {db_prefix}garage_guestbooks AS gb, {db_prefix}members AS u2, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
                    WHERE gb.id = {int:id}
                        AND gb.vehicle_id = v.id
                        AND v.user_id = u2.id_member
                        AND v.make_id = mk.id
                        AND v.model_id = md.id
                        AND gb.author_id = u.id_member',
            array(
                'id' => $context['last_comments'][$count]['id'],
            )
        );
        list($context['last_comments'][$count]['vid'],
            $context['last_comments'][$count]['post_date'],
            $context['last_comments'][$count]['author_id'],
            $context['last_comments'][$count]['vehicle'],
            $context['last_comments'][$count]['memberName']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_comments'][$count]['vid'] . '#guestbook">' . $context['last_comments'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_author'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['last_comments'][$count]['author_id'] . '">' . $context['last_comments'][$count]['memberName'] . '</a></b></div>
                <div class="smalltext">' . $txt['smfg_posted'] . ': <b>' . date('M d, Y',
                $context['last_comments'][$count]['post_date']) . '</b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['last_comments'];
        }
    }
}

// Show the last videos
function ssi_smfg_lastVideo($title = 1, $limit = 5, $output_method = 'echo')
{
    global $smfgSettings, $txt, $scripturl, $db_prefix, $smcFunc;

    // Load garage settings
    loadSmfgConfig();
    loadLanguage('Garage');

    $output = '';

    $output .= '
            <div style="width: 100%;">';
    if ($title) {
        $output .= '<div style="margin-bottom: 2px;">
                <h3 style="margin: 0; font-size: 1em; padding: 5px;">' . $txt['smfg_recent_videos'] . '</h3>';
    }

    // Get the five latest vehicles with video entries
    // Get the VIDs first
    $request = $smcFunc['db_query']('', '
            SELECT b.id 
            FROM {db_prefix}garage_video AS b, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
            WHERE b.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
            ORDER BY b.id DESC
            LIMIT 0, ' . $limit,
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['last_video'][$count]['id']) = $row;
        // Now collect data for each video
        $request2 = $smcFunc['db_query']('', '
                    SELECT b.vehicle_id, b.title, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name, b2.type, b2.type_id
                    FROM {db_prefix}garage_video AS b, {db_prefix}garage_video_gallery AS b2, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
                    WHERE b.id = {int:id}
                        AND v.id = b.vehicle_id
                        AND b2.video_id = {int:id2}
                        AND v.make_id = mk.id
                        AND v.model_id = md.id
                        AND v.user_id = u.id_member',
            array(
                'id' => $context['last_video'][$count]['id'],
                'id2' => $context['last_video'][$count]['id'],
            )
        );
        list($context['last_video'][$count]['vid'],
            $context['last_video'][$count]['video_title'],
            $context['last_video'][$count]['user_id'],
            $context['last_video'][$count]['vehicle'],
            $context['last_video'][$count]['memberName'],
            $context['last_video'][$count]['type'],
            $context['last_video'][$count]['tid']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        //Check the string length of video title and trim it
        $video_title_length = strlen($context['last_video'][$count]['video_title']);
        if ($video_title_length >= 20) {
            $context['last_video'][$count]['video_title'] = substr($context['last_video'][$count]['video_title'], 0,
                20);
            $context['last_video'][$count]['video_title'] .= "...";
        }

        switch ($context['last_video'][$count]['type']) {
            case 'vehicle':
                $uri = 'sa=view_vehicle;VID=' . $context['last_video'][$count]['vid'];
                break;
            case 'mod':
                $uri = 'sa=view_modification;VID=' . $context['last_video'][$count]['vid'] . ';MID=' . $context['last_video'][$count]['tid'];
                break;
            case 'dynorun':
                $uri = 'sa=view_dynorun;VID=' . $context['last_video'][$count]['vid'] . ';DID=' . $context['last_video'][$count]['tid'];
                break;
            case 'qmile':
                $uri = 'sa=view_quartermile;VID=' . $context['last_video'][$count]['vid'] . ';QID=' . $context['last_video'][$count]['tid'];
                break;
            case 'lap':
                $uri = 'sa=view_laptime;VID=' . $context['last_video'][$count]['vid'] . ';LID=' . $context['last_video'][$count]['tid'];
                break;
        }

        $output .= '
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;' . $uri . '#videos">' . $context['last_video'][$count]['video_title'] . '</a></div>
                <div class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_video'][$count]['vid'] . '">' . $context['last_video'][$count]['vehicle'] . '</a></div>
                <div class="smalltext">' . $txt['smfg_owner'] . ': <b><a href="' . $scripturl . '?action=profile;u=' . $context['last_video'][$count]['user_id'] . '">' . $context['last_video'][$count]['memberName'] . '</a></b></div>';

        $count++;

        if ($count < $limit) {
            $output .= '<hr />';
        }
    }
    $smcFunc['db_free_result']($request);

    if ($title) {
        $output .= '</div>';
    }
    $output .= '</div>';

    if ($output_method == 'echo') {
        echo $output;
    } else {
        if ($output_method == 'array') {
            return $context['last_video'];
        }
    }
}
