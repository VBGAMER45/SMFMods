<?php
/**********************************************************************************
 * GarageSettings.php                                                              *
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

if (!defined('SMF')) {
    die('Hacking attempt...');
}

// The controller; doesn't do anything, just delegates.
function GarageSettings()
{
    global $smfgSettings, $context, $txt, $scripturl, $db_prefix;

    if (isset($context['TPortal'])) {
        tp_hidebars();
    }

    // First, let's do a quick permissions check
    isAllowedTo('manage_garage_settings');

    // We need our functions!
    require_once('GarageFunctions.php');

    // Load settings
    loadSmfgConfig();

    // Administrative side bar, here we come!
    //adminIndex('garage_settings');

    // This is gonna be needed...
    loadTemplate('GarageSettings', 'garage');
    loadLanguage('Garage');

    // Set our index includes
    $context['smfg_ajax'] = 0;
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    // Format: 'sub-action' => array('function', 'permission')
    $subActions = array(
        'general' => array('ManageGarage', 'manage_garage_general'),
        'updategeneral' => array('UpdateGeneral', 'manage_garage_general'),
        'notify_add' => array('AddNotify', 'manage_garage_general'),
        'notify_delete' => array('DeleteNotify', 'manage_garage_general'),
        'menusettings' => array('MenuSettings', 'manage_garage_menu'),
        'updatemenu' => array('UpdateMenu', 'manage_garage_menu'),
        'indexsettings' => array('IndexSettings', 'manage_garage_index'),
        'updateindex' => array('UpdateIndex', 'manage_garage_index'),
        'block_move' => array('MoveBlock', 'manage_garage_index'),
        'block_disable' => array('DisableBlock', 'manage_garage_index'),
        'block_enable' => array('EnableBlock', 'manage_garage_index'),
        'imagesettings' => array('ImageSettings', 'manage_garage_images'),
        'updateimage' => array('UpdateImage', 'manage_garage_images'),
        'videosettings' => array('VideoSettings', 'manage_garage_videos'),
        'updatevideo' => array('UpdateVideo', 'manage_garage_videos'),
        'modulesettings' => array('ModuleSettings', 'manage_garage_modules'),
        'updatemodule' => array('UpdateModule', 'manage_garage_modules'),
    );

    // Default to a sub action they have permission to
    if (allowedTo('manage_garage_general')) {
        $_REQUEST['sa'] = !empty($_GET['sa']) ? $_GET['sa'] : 'general';
    } else {
        if (allowedTo('manage_garage_menu')) {
            $_REQUEST['sa'] = !empty($_GET['sa']) ? $_GET['sa'] : 'menusettings';
        } else {
            if (allowedTo('manage_garage_index')) {
                $_REQUEST['sa'] = !empty($_GET['sa']) ? $_GET['sa'] : 'indexsettings';
            } else {
                if (allowedTo('manage_garage_images')) {
                    $_REQUEST['sa'] = !empty($_GET['sa']) ? $_GET['sa'] : 'imagesettings';
                } else {
                    if (allowedTo('manage_garage_videos')) {
                        $_REQUEST['sa'] = !empty($_GET['sa']) ? $_GET['sa'] : 'videosettings';
                    } else {
                        if (allowedTo('manage_garage_modules')) {
                            $_REQUEST['sa'] = !empty($_GET['sa']) ? $_GET['sa'] : 'modulesettings';
                        }
                    }
                }
            }
        }
    }

    // Have you got the proper permissions?
    isAllowedTo($subActions[$_REQUEST['sa']][1]);

    // Tabs for browsing the different ban functions.
    $context[$context['admin_menu_name']]['tab_data'] = array(
        'title' => $txt['managegarage_settings'],
        'help' => 'garage_settings',
        'description' => $txt['settings_general'],
        'tabs' => array(
            'general' => array(
                'description' => $txt['settings_general'],
            ),
            'menusettings' => array(
                'description' => $txt['settings_menu'],
            ),
            'indexsettings' => array(
                'description' => $txt['settings_index'],
            ),
            'imagesettings' => array(
                'description' => $txt['settings_images'],
            ),
            'videosettings' => array(
                'description' => $txt['settings_videos'],
            ),
            'modulesettings' => array(
                'description' => $txt['settings_modules'],
            ),
        ),
    );

    $subActions[$_REQUEST['sa']][0]();
}

// General Garage Settings
function ManageGarage()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'garage_settings';
    $context['page_title'] = $txt['smfg_garage'] . ' ' . $txt['smfg_settings'];

    // Check Permissions
    isAllowedTo('manage_garage_general');

    // Check for config values and 'check' enabled options
    if ($smfgSettings['enable_vehicle_approval']) {
        $context['enable_vehicle_approval_check'] = 'checked="checked"';
    } else {
        $context['enable_vehicle_approval_check'] = "";
    }
    if ($smfgSettings['enable_user_submit_make']) {
        $context['user_submit_make_check'] = 'checked="checked"';
    } else {
        $context['user_submit_make_check'] = "";
    }
    if ($smfgSettings['enable_make_approval']) {
        $context['enable_make_approval_check'] = 'checked="checked"';
    } else {
        $context['enable_make_approval_check'] = "";
    }
    if ($smfgSettings['enable_user_submit_model']) {
        $context['user_submit_model_check'] = 'checked="checked"';
    } else {
        $context['user_submit_model_check'] = "";
    }
    if ($smfgSettings['enable_model_approval']) {
        $context['enable_model_approval_check'] = 'checked="checked"';
    } else {
        $context['enable_model_approval_check'] = "";
    }
    if ($smfgSettings['enable_user_submit_business']) {
        $context['enable_user_submit_business_check'] = 'checked="checked"';
    } else {
        $context['enable_user_submit_business_check'] = "";
    }
    if ($smfgSettings['enable_business_approval']) {
        $context['enable_business_approval_check'] = 'checked="checked"';
    } else {
        $context['enable_business_approval_check'] = "";
    }
    if ($smfgSettings['enable_user_submit_product']) {
        $context['enable_user_submit_product_check'] = 'checked="checked"';
    } else {
        $context['enable_user_submit_product_check'] = "";
    }
    if ($smfgSettings['enable_product_approval']) {
        $context['enable_product_approval_check'] = 'checked="checked"';
    } else {
        $context['enable_product_approval_check'] = "";
    }
    if ($smfgSettings['integrate_viewtopic']) {
        $context['integrate_viewtopic_check'] = 'checked="checked"';
    } else {
        $context['integrate_viewtopic_check'] = "";
    }
    if ($smfgSettings['integrate_profile']) {
        $context['integrate_profile_check'] = 'checked="checked"';
    } else {
        $context['integrate_profile_check'] = "";
    }
    if ($smfgSettings['enable_pm_pending_notify']) {
        $context['enable_pm_pending_notify_check'] = 'checked="checked"';
    } else {
        $context['enable_pm_pending_notify_check'] = "";
    }
    if ($smfgSettings['enable_email_pending_notify']) {
        $context['enable_email_pending_notify_check'] = 'checked="checked"';
    } else {
        $context['enable_email_pending_notify_check'] = "";
    }
    if ($smfgSettings['enable_pm_pending_notify_optout']) {
        $context['enable_pm_pending_notify_optout_check'] = 'checked="checked"';
    } else {
        $context['enable_pm_pending_notify_optout_check'] = "";
    }
    if ($smfgSettings['enable_email_pending_notify_optout']) {
        $context['enable_email_pending_notify_optout_check'] = 'checked="checked"';
    } else {
        $context['enable_email_pending_notify_optout_check'] = "";
    }
    if ($smfgSettings['disable_garage']) {
        $context['disable_garage_check'] = 'checked="checked"';
    } else {
        $context['disable_garage_check'] = "";
    }

    if ($smfgSettings['rating_system'] == 0) {
        $context['sum_rating_check'] = 'checked="checked"';
    } else {
        if ($smfgSettings['rating_system'] == 1) {
            $context['avg_rating_check'] = 'checked="checked"';
        }
    }

    if (!isset($context['sum_rating_check'])) {
        $context['sum_rating_check'] = "";
    }
    if (!isset($context['avg_rating_check'])) {
        $context['avg_rating_check'] = "";
    }

    // What date format is selected?
    if ($smfgSettings['dateformat'] == 'd M Y, H:i') {
        $context['one'] = ' selected="selected"';
    } else {
        if ($smfgSettings['dateformat'] == 'd M Y H:i') {
            $context['two'] = ' selected="selected"';
        } else {
            if ($smfgSettings['dateformat'] == 'M jS, \'y, H:i') {
                $context['three'] = ' selected="selected"';
            } else {
                if ($smfgSettings['dateformat'] == 'D M d, Y g:i a') {
                    $context['four'] = ' selected="selected"';
                } else {
                    if ($smfgSettings['dateformat'] == 'F jS, Y, g:i a') {
                        $context['five'] = ' selected="selected"';
                    } else {
                        if ($smfgSettings['dateformat'] == '|d M Y| H:i') {
                            $context['six'] = ' selected="selected"';
                        } else {
                            if ($smfgSettings['dateformat'] == '|F jS, Y| g:i a') {
                                $context['seven'] = ' selected="selected"';
                            } else {
                                $context['custom'] = ' selected="selected"';
                            }
                        }
                    }
                }
            }
        }
    }

    if (!isset($context['one'])) {
        $context['one'] = "";
    }
    if (!isset($context['two'])) {
        $context['two'] = "";
    }
    if (!isset($context['three'])) {
        $context['three'] = "";
    }
    if (!isset($context['four'])) {
        $context['four'] = "";
    }
    if (!isset($context['five'])) {
        $context['five'] = "";
    }
    if (!isset($context['six'])) {
        $context['six'] = "";
    }
    if (!isset($context['seven'])) {
        $context['seven'] = "";
    }
    if (!isset($context['custom'])) {
        $context['custom'] = "";
    }

    // Get all the members to be notified
    $request = $smcFunc['db_query']('', '
        SELECT n.id, u.real_name
        FROM {db_prefix}garage_notifications AS n, {db_prefix}members AS u
        WHERE n.user_id = u.id_member
            ORDER BY n.id',
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['notifications'][$count]['id'],
            $context['notifications'][$count]['user']) = $row;
        $count++;
    }
    $smcFunc['db_free_result'] ($request);

}

// Update General Settings
function UpdateGeneral()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Check Permissions
    isAllowedTo('manage_garage_general');

    // Validate the session
    checkSession();

    // Define all indices
    if (!isset($_POST['config']['enable_vehicle_approval'])) {
        $_POST['config']['enable_vehicle_approval'] = 0;
    }
    if (!isset($_POST['config']['enable_user_submit_make'])) {
        $_POST['config']['enable_user_submit_make'] = 0;
    }
    if (!isset($_POST['config']['enable_make_approval'])) {
        $_POST['config']['enable_make_approval'] = 0;
    }
    if (!isset($_POST['config']['enable_user_submit_model'])) {
        $_POST['config']['enable_user_submit_model'] = 0;
    }
    if (!isset($_POST['config']['enable_model_approval'])) {
        $_POST['config']['enable_model_approval'] = 0;
    }
    if (!isset($_POST['config']['enable_user_submit_business'])) {
        $_POST['config']['enable_user_submit_business'] = 0;
    }
    if (!isset($_POST['config']['enable_business_approval'])) {
        $_POST['config']['enable_business_approval'] = 0;
    }
    if (!isset($_POST['config']['enable_user_submit_product'])) {
        $_POST['config']['enable_user_submit_product'] = 0;
    }
    if (!isset($_POST['config']['enable_product_approval'])) {
        $_POST['config']['enable_product_approval'] = 0;
    }
    if (!isset($_POST['config']['enable_pm_pending_notify'])) {
        $_POST['config']['enable_pm_pending_notify'] = 0;
    }
    if (!isset($_POST['config']['enable_pm_pending_notify_optout'])) {
        $_POST['config']['enable_pm_pending_notify_optout'] = 0;
    }
    if (!isset($_POST['config']['enable_email_pending_notify'])) {
        $_POST['config']['enable_email_pending_notify'] = 0;
    }
    if (!isset($_POST['config']['enable_email_pending_notify_optout'])) {
        $_POST['config']['enable_email_pending_notify_optout'] = 0;
    }
    if (!isset($_POST['config']['integrate_viewtopic'])) {
        $_POST['config']['integrate_viewtopic'] = 0;
    }
    if (!isset($_POST['config']['integrate_profile'])) {
        $_POST['config']['integrate_profile'] = 0;
    }
    if (!isset($_POST['config']['disable_garage'])) {
        $_POST['config']['disable_garage'] = 0;
    }

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:cars_per_page} 
        WHERE config_name = "cars_per_page"',
        array(
            'cars_per_page' => $_POST['config']['cars_per_page'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:results_per_page} 
        WHERE config_name = "results_per_page"',
        array(
            'results_per_page' => $_POST['config']['results_per_page'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:blogs_per_page}
        WHERE config_name = "blogs_per_page"',
        array(
            'blogs_per_page' => $_POST['config']['blogs_per_page'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:insurance_review_limit}
        WHERE config_name = "insurance_review_limit"',
        array(
            'insurance_review_limit' => $_POST['config']['insurance_review_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:shop_review_limit}
        WHERE config_name = "shop_review_limit"',
        array(
            'shop_review_limit' => $_POST['config']['shop_review_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:garage_review_limit}
        WHERE config_name = "garage_review_limit"',
        array(
            'garage_review_limit' => $_POST['config']['garage_review_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:default_vehicle_quota}
        WHERE config_name = "default_vehicle_quota"',
        array(
            'default_vehicle_quota' => $_POST['config']['default_vehicle_quota'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_vehicle_approval}
        WHERE config_name = "enable_vehicle_approval"',
        array(
            'enable_vehicle_approval' => $_POST['config']['enable_vehicle_approval'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {string:pending_subject}
        WHERE config_name = "pending_subject"',
        array(
            'pending_subject' => $_POST['config']['pending_subject'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:comments_per_page}
        WHERE config_name = "comments_per_page"',
        array(
            'comments_per_page' => $_POST['config']['comments_per_page'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:year_start}
        WHERE config_name = "year_start"',
        array(
            'year_start' => $_POST['config']['year_start'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:year_end}
        WHERE config_name = "year_end"',
        array(
            'year_end' => $_POST['config']['year_end'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_user_submit_make}
        WHERE config_name = "enable_user_submit_make"',
        array(
            'enable_user_submit_make' => $_POST['config']['enable_user_submit_make'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_make_approval}
        WHERE config_name = "enable_make_approval"',
        array(
            'enable_make_approval' => $_POST['config']['enable_make_approval'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_user_submit_model}
        WHERE config_name = "enable_user_submit_model"',
        array(
            'enable_user_submit_model' => $_POST['config']['enable_user_submit_model'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_model_approval}
        WHERE config_name = "enable_model_approval"',
        array(
            'enable_model_approval' => $_POST['config']['enable_model_approval'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {string:dateformat}
        WHERE config_name = "dateformat"',
        array(
            'dateformat' => $_POST['config']['dateformat'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:integrate_viewtopic}
        WHERE config_name = "integrate_viewtopic"',
        array(
            'integrate_viewtopic' => $_POST['config']['integrate_viewtopic'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:integrate_profile}
        WHERE config_name = "integrate_profile"',
        array(
            'integrate_profile' => $_POST['config']['integrate_profile'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:pending_sender}
        WHERE config_name = "pending_sender"',
        array(
            'pending_sender' => $_POST['config']['pending_sender'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_pm_pending_notify}
        WHERE config_name = "enable_pm_pending_notify"',
        array(
            'enable_pm_pending_notify' => $_POST['config']['enable_pm_pending_notify'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_email_pending_notify}
        WHERE config_name = "enable_email_pending_notify"',
        array(
            'enable_email_pending_notify' => $_POST['config']['enable_email_pending_notify'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_pm_pending_notify_optout}
        WHERE config_name = "enable_pm_pending_notify_optout"',
        array(
            'enable_pm_pending_notify_optout' => $_POST['config']['enable_pm_pending_notify_optout'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_email_pending_notify_optout}
        WHERE config_name = "enable_email_pending_notify_optout"',
        array(
            'enable_email_pending_notify_optout' => $_POST['config']['enable_email_pending_notify_optout'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_user_submit_business}
        WHERE config_name = "enable_user_submit_business"',
        array(
            'enable_user_submit_business' => $_POST['config']['enable_user_submit_business'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_business_approval}
        WHERE config_name = "enable_business_approval"',
        array(
            'enable_business_approval' => $_POST['config']['enable_business_approval'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_user_submit_product}
        WHERE config_name = "enable_user_submit_product"',
        array(
            'enable_user_submit_product' => $_POST['config']['enable_user_submit_product'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_product_approval}
        WHERE config_name = "enable_product_approval"',
        array(
            'enable_product_approval' => $_POST['config']['enable_product_approval'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config
        SET config_value = {int:rating_system}
        WHERE config_name = "rating_system"',
        array(
            'rating_system' => $_POST['config']['rating_system'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config
        SET config_value = {int:disable_garage}
        WHERE config_name = "disable_garage"',
        array(
            'disable_garage' => $_POST['config']['disable_garage'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garagesettings');    
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Menu Settings
function MenuSettings()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'menu_settings';
    $context['page_title'] = $txt['smfg_garage'] . ' ' . $txt['smfg_settings'];

    // Check Permissions
    isAllowedTo('manage_garage_menu');

    // Check for config values and 'check' enabled options
    if ($smfgSettings['enable_index_menu']) {
        $context['enable_index_menu_check'] = 'checked="checked"';
    } else {
        $context['enable_index_menu_check'] = "";
    }
    if ($smfgSettings['enable_browse_menu']) {
        $context['enable_browse_menu_check'] = 'checked="checked"';
    } else {
        $context['enable_browse_menu_check'] = "";
    }
    if ($smfgSettings['enable_search_menu']) {
        $context['enable_search_menu_check'] = 'checked="checked"';
    } else {
        $context['enable_search_menu_check'] = "";
    }
    if ($smfgSettings['enable_insurance_review_menu']) {
        $context['enable_insurance_review_menu_check'] = 'checked="checked"';
    } else {
        $context['enable_insurance_review_menu_check'] = "";
    }
    if ($smfgSettings['enable_garage_review_menu']) {
        $context['enable_garage_review_menu_check'] = 'checked="checked"';
    } else {
        $context['enable_garage_review_menu_check'] = "";
    }
    if ($smfgSettings['enable_shop_review_menu']) {
        $context['enable_shop_review_menu_check'] = 'checked="checked"';
    } else {
        $context['enable_shop_review_menu_check'] = "";
    }
    if ($smfgSettings['enable_quartermile_menu']) {
        $context['enable_quartermile_menu_check'] = 'checked="checked"';
    } else {
        $context['enable_quartermile_menu_check'] = "";
    }
    if ($smfgSettings['enable_dynorun_menu']) {
        $context['enable_dynorun_menu_check'] = 'checked="checked"';
    } else {
        $context['enable_dynorun_menu_check'] = "";
    }
    if ($smfgSettings['enable_lap_menu']) {
        $context['enable_lap_menu_check'] = 'checked="checked"';
    } else {
        $context['enable_lap_menu_check'] = "";
    }
}

// Update Menu Settings
function UpdateMenu()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Check Permissions
    isAllowedTo('manage_garage_menu');

    // Validate the session
    checkSession();

    // Define all indices
    if (!isset($_POST['config']['enable_index_menu'])) {
        $_POST['config']['enable_index_menu'] = 0;
    }
    if (!isset($_POST['config']['enable_browse_menu'])) {
        $_POST['config']['enable_browse_menu'] = 0;
    }
    if (!isset($_POST['config']['enable_search_menu'])) {
        $_POST['config']['enable_search_menu'] = 0;
    }
    if (!isset($_POST['config']['enable_insurance_review_menu'])) {
        $_POST['config']['enable_insurance_review_menu'] = 0;
    }
    if (!isset($_POST['config']['enable_garage_review_menu'])) {
        $_POST['config']['enable_garage_review_menu'] = 0;
    }
    if (!isset($_POST['config']['enable_shop_review_menu'])) {
        $_POST['config']['enable_shop_review_menu'] = 0;
    }
    if (!isset($_POST['config']['enable_quartermile_menu'])) {
        $_POST['config']['enable_quartermile_menu'] = 0;
    }
    if (!isset($_POST['config']['enable_dynorun_menu'])) {
        $_POST['config']['enable_dynorun_menu'] = 0;
    }
    if (!isset($_POST['config']['enable_lap_menu'])) {
        $_POST['config']['enable_lap_menu'] = 0;
    }

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_index_menu}
        WHERE config_name = "enable_index_menu"',
        array(
            'enable_index_menu' => $_POST['config']['enable_index_menu'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_browse_menu}
        WHERE config_name = "enable_browse_menu"',
        array(
            'enable_browse_menu' => $_POST['config']['enable_browse_menu'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_search_menu} 
        WHERE config_name = "enable_search_menu"',
        array(
            'enable_search_menu' => $_POST['config']['enable_search_menu'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_insurance_review_menu}
        WHERE config_name = "enable_insurance_review_menu"',
        array(
            'enable_insurance_review_menu' => $_POST['config']['enable_insurance_review_menu'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_garage_review_menu}
        WHERE config_name = "enable_garage_review_menu"',
        array(
            'enable_garage_review_menu' => $_POST['config']['enable_garage_review_menu'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_shop_review_menu} 
        WHERE config_name = "enable_shop_review_menu"',
        array(
            'enable_shop_review_menu' => $_POST['config']['enable_shop_review_menu'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_quartermile_menu}
        WHERE config_name = "enable_quartermile_menu"',
        array(
            'enable_quartermile_menu' => $_POST['config']['enable_quartermile_menu'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_dynorun_menu}
        WHERE config_name = "enable_dynorun_menu"',
        array(
            'enable_dynorun_menu' => $_POST['config']['enable_dynorun_menu'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_lap_menu} 
        WHERE config_name = "enable_lap_menu"',
        array(
            'enable_lap_menu' => $_POST['config']['enable_lap_menu'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garagesettings;sa=menusettings');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Index Page Settings
function IndexSettings()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'index_settings';
    $context['page_title'] = $txt['smfg_garage'] . ' ' . $txt['smfg_settings'];

    // Check Permissions
    isAllowedTo('manage_garage_index');

    // Get block postions
    $request = $smcFunc['db_query']('', '
        SELECT id, input_title, title, position, enabled
        FROM {db_prefix}garage_blocks
            ORDER BY position ASC',
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['blocks'][$count]['id'],
            $context['blocks'][$count]['input_title'],
            $context['blocks'][$count]['title'],
            $context['blocks'][$count]['position'],
            $context['blocks'][$count]['enabled']) = $row;
        $count++;
    }
    $smcFunc['db_free_result'] ($request);

    // Set the total number of blocks
    $context['blocks']['total'] = $count;

    // Check for config values and 'check' enabled options
    if ($smfgSettings['enable_newest_vehicle']) {
        $context['enable_newest_vehicle_check'] = 'checked="checked"';
    } else {
        $context['enable_newest_vehicle_check'] = "";
    }
    if ($smfgSettings['enable_updated_vehicle']) {
        $context['enable_updated_vehicle_check'] = 'checked="checked"';
    } else {
        $context['enable_updated_vehicle_check'] = "";
    }
    if ($smfgSettings['enable_newest_modification']) {
        $context['enable_newest_modification_check'] = 'checked="checked"';
    } else {
        $context['enable_newest_modification_check'] = "";
    }
    if ($smfgSettings['enable_updated_modification']) {
        $context['enable_updated_modification_check'] = 'checked="checked"';
    } else {
        $context['enable_updated_modification_check'] = "";
    }
    if ($smfgSettings['enable_most_modified']) {
        $context['enable_most_modified_check'] = 'checked="checked"';
    } else {
        $context['enable_most_modified_check'] = "";
    }
    if ($smfgSettings['enable_most_spent']) {
        $context['enable_most_spent_check'] = 'checked="checked"';
    } else {
        $context['enable_most_spent_check'] = "";
    }
    if ($smfgSettings['enable_most_viewed']) {
        $context['enable_most_viewed_check'] = 'checked="checked"';
    } else {
        $context['enable_most_viewed_check'] = "";
    }
    if ($smfgSettings['enable_last_commented']) {
        $context['enable_last_commented_check'] = 'checked="checked"';
    } else {
        $context['enable_last_commented_check'] = "";
    }
    if ($smfgSettings['enable_top_dynorun']) {
        $context['enable_top_dynorun_check'] = 'checked="checked"';
    } else {
        $context['enable_top_dynorun_check'] = "";
    }
    if ($smfgSettings['enable_top_quartermile']) {
        $context['enable_top_quartermile_check'] = 'checked="checked"';
    } else {
        $context['enable_top_quartermile_check'] = "";
    }
    if ($smfgSettings['enable_top_rating']) {
        $context['enable_top_rating_check'] = 'checked="checked"';
    } else {
        $context['enable_top_rating_check'] = "";
    }
    if ($smfgSettings['enable_top_lap']) {
        $context['enable_top_lap_check'] = 'checked="checked"';
    } else {
        $context['enable_top_lap_check'] = "";
    }
    if ($smfgSettings['featured_vehicle_image_required']) {
        $context['featured_vehicle_image_required_check'] = 'checked="checked"';
    } else {
        $context['featured_vehicle_image_required_check'] = "";
    }

    if ($smfgSettings['enable_featured_vehicle'] == 0) {
        $context['disabled_check'] = 'checked="checked"';
    } else {
        if ($smfgSettings['enable_featured_vehicle'] == 1) {
            $context['from_id_check'] = 'checked="checked"';
        } else {
            if ($smfgSettings['enable_featured_vehicle'] == 2) {
                $context['from_block_check'] = 'checked="checked"';
            } else {
                if ($smfgSettings['enable_featured_vehicle'] == 3) {
                    $context['random_check'] = 'checked="checked"';
                }
            }
        }
    }

    if (!isset($context['disabled_check'])) {
        $context['disabled_check'] = "";
    }
    if (!isset($context['from_id_check'])) {
        $context['from_id_check'] = "";
    }
    if (!isset($context['from_block_check'])) {
        $context['from_block_check'] = "";
    }
    if (!isset($context['random_check'])) {
        $context['random_check'] = "";
    }

    if ($smfgSettings['index_columns'] == 1) {
        $context['one'] = ' selected="selected"';
    } else {
        if ($smfgSettings['index_columns'] == 2) {
            $context['two'] = ' selected="selected"';
        }
    }

    if (!isset($context['one'])) {
        $context['one'] = "";
    }
    if (!isset($context['two'])) {
        $context['two'] = "";
    }

    if ($smfgSettings['featured_vehicle_from_block'] == 0 || $smfgSettings['featured_vehicle_from_block'] === "") {
        $context['fb_none'] = ' selected="selected"';
    } else {
        if ($smfgSettings['featured_vehicle_from_block'] == 1) {
            $context['fb_one'] = ' selected="selected"';
        } else {
            if ($smfgSettings['featured_vehicle_from_block'] == 2) {
                $context['fb_two'] = ' selected="selected"';
            } else {
                if ($smfgSettings['featured_vehicle_from_block'] == 3) {
                    $context['fb_three'] = ' selected="selected"';
                } else {
                    if ($smfgSettings['featured_vehicle_from_block'] == 4) {
                        $context['fb_four'] = ' selected="selected"';
                    } else {
                        if ($smfgSettings['featured_vehicle_from_block'] == 5) {
                            $context['fb_five'] = ' selected="selected"';
                        } else {
                            if ($smfgSettings['featured_vehicle_from_block'] == 6) {
                                $context['fb_six'] = ' selected="selected"';
                            } else {
                                if ($smfgSettings['featured_vehicle_from_block'] == 7) {
                                    $context['fb_seven'] = ' selected="selected"';
                                } else {
                                    if ($smfgSettings['featured_vehicle_from_block'] == 8) {
                                        $context['fb_eight'] = ' selected="selected"';
                                    } else {
                                        if ($smfgSettings['featured_vehicle_from_block'] == 9) {
                                            $context['fb_nine'] = ' selected="selected"';
                                        } else {
                                            if ($smfgSettings['featured_vehicle_from_block'] == 10) {
                                                $context['fb_ten'] = ' selected="selected"';
                                            } else {
                                                if ($smfgSettings['featured_vehicle_from_block'] == 11) {
                                                    $context['fb_eleven'] = ' selected="selected"';
                                                } else {
                                                    if ($smfgSettings['featured_vehicle_from_block'] == 12) {
                                                        $context['fb_twelve'] = ' selected="selected"';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    if (!isset($context['fb_none'])) {
        $context['fb_none'] = "";
    }
    if (!isset($context['fb_one'])) {
        $context['fb_one'] = "";
    }
    if (!isset($context['fb_two'])) {
        $context['fb_two'] = "";
    }
    if (!isset($context['fb_three'])) {
        $context['fb_three'] = "";
    }
    if (!isset($context['fb_four'])) {
        $context['fb_four'] = "";
    }
    if (!isset($context['fb_five'])) {
        $context['fb_five'] = "";
    }
    if (!isset($context['fb_six'])) {
        $context['fb_six'] = "";
    }
    if (!isset($context['fb_seven'])) {
        $context['fb_seven'] = "";
    }
    if (!isset($context['fb_eight'])) {
        $context['fb_eight'] = "";
    }
    if (!isset($context['fb_nine'])) {
        $context['fb_nine'] = "";
    }
    if (!isset($context['fb_ten'])) {
        $context['fb_ten'] = "";
    }
    if (!isset($context['fb_eleven'])) {
        $context['fb_eleven'] = "";
    }
    if (!isset($context['fb_twelve'])) {
        $context['fb_twelve'] = "";
    }

    if ($smfgSettings['featured_vehicle_description_alignment'] == "left") {
        $context['left'] = ' selected="selected"';
    } else {
        if ($smfgSettings['featured_vehicle_description_alignment'] == "center") {
            $context['center'] = ' selected="selected"';
        } else {
            if ($smfgSettings['featured_vehicle_description_alignment'] == "right") {
                $context['right'] = ' selected="selected"';
            }
        }
    }

    if (!isset($context['left'])) {
        $context['left'] = "";
    }
    if (!isset($context['center'])) {
        $context['center'] = "";
    }
    if (!isset($context['right'])) {
        $context['right'] = "";
    }

}

// Update Index Page Settings
function UpdateIndex()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Check Permissions
    isAllowedTo('manage_garage_index');

    // Validate the session
    checkSession();

    // Define all indices
    if (!isset($_POST['config']['featured_vehicle_image_required'])) {
        $_POST['config']['featured_vehicle_image_required'] = 0;
    }
    if (empty($_POST['config']['featured_vehicle_id'])) {
        $_POST['config']['featured_vehicle_id'] = 0;
    }
    if (empty($_POST['config']['featured_vehicle_from_block'])) {
        $_POST['config']['featured_vehicle_from_block'] = 0;
    }

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:index_columns}
        WHERE config_name = "index_columns"',
        array(
            'index_columns' => $_POST['config']['index_columns'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_featured_vehicle} 
        WHERE config_name = "enable_featured_vehicle"',
        array(
            'enable_featured_vehicle' => $_POST['config']['enable_featured_vehicle'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:featured_vehicle_image_required}
        WHERE config_name = "featured_vehicle_image_required"',
        array(
            'featured_vehicle_image_required' => $_POST['config']['featured_vehicle_image_required'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:featured_vehicle_id}
        WHERE config_name = "featured_vehicle_id"',
        array(
            'featured_vehicle_id' => $_POST['config']['featured_vehicle_id'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:featured_vehicle_from_block}
        WHERE config_name = "featured_vehicle_from_block"',
        array(
            'featured_vehicle_from_block' => $_POST['config']['featured_vehicle_from_block'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {string:featured_vehicle_description}
        WHERE config_name = "featured_vehicle_description"',
        array(
            'featured_vehicle_description' => $_POST['config']['featured_vehicle_description'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {string:featured_vehicle_description_alignment}
        WHERE config_name = "featured_vehicle_description_alignment"',
        array(
            'featured_vehicle_description_alignment' => $_POST['config']['featured_vehicle_description_alignment'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:newest_vehicle_limit}
        WHERE config_name = "newest_vehicle_limit"',
        array(
            'newest_vehicle_limit' => $_POST['config']['newest_vehicle_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:updated_vehicle_limit}
        WHERE config_name = "updated_vehicle_limit"',
        array(
            'updated_vehicle_limit' => $_POST['config']['updated_vehicle_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:newest_modification_limit}
        WHERE config_name = "newest_modification_limit"',
        array(
            'newest_modification_limit' => $_POST['config']['newest_modification_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:updated_modification_limit}
        WHERE config_name = "updated_modification_limit"',
        array(
            'updated_modification_limit' => $_POST['config']['updated_modification_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:most_modified_limit}
        WHERE config_name = "most_modified_limit"',
        array(
            'most_modified_limit' => $_POST['config']['most_modified_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:most_spent_limit}
        WHERE config_name = "most_spent_limit"',
        array(
            'most_spent_limit' => $_POST['config']['most_spent_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:most_viewed_limit}
        WHERE config_name = "most_viewed_limit"',
        array(
            'most_viewed_limit' => $_POST['config']['most_viewed_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:last_commented_limit}
        WHERE config_name = "last_commented_limit"',
        array(
            'last_commented_limit' => $_POST['config']['last_commented_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:top_dynorun_limit}
        WHERE config_name = "top_dynorun_limit"',
        array(
            'top_dynorun_limit' => $_POST['config']['top_dynorun_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:top_quartermile_limit}
        WHERE config_name = "top_quartermile_limit"',
        array(
            'top_quartermile_limit' => $_POST['config']['top_quartermile_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:top_rating_limit}
        WHERE config_name = "top_rating_limit"',
        array(
            'top_rating_limit' => $_POST['config']['top_rating_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:top_lap_limit}
        WHERE config_name = "top_lap_limit"',
        array(
            'top_lap_limit' => $_POST['config']['top_lap_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:latest_service_limit}
        WHERE config_name = "latest_service_limit"',
        array(
            'latest_service_limit' => $_POST['config']['latest_service_limit'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:latest_blog_limit}
        WHERE config_name = "latest_blog_limit"',
        array(
            'latest_blog_limit' => $_POST['config']['latest_blog_limit'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garagesettings;sa=indexsettings');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Image Settings
function ImageSettings()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc, $boarddir, $settings;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'image_settings';
    $context['page_title'] = $txt['smfg_garage'] . ' ' . $txt['smfg_settings'];

    // Check Permissions
    isAllowedTo('manage_garage_images');

    // Check for config values and 'check' enabled options
    if ($smfgSettings['enable_images']) {
        $context['enable_images_check'] = 'checked="checked"';
    } else {
        $context['enable_images_check'] = "";
    }
    if ($smfgSettings['enable_vehicle_images']) {
        $context['enable_vehicle_images_check'] = 'checked="checked"';
    } else {
        $context['enable_vehicle_images_check'] = "";
    }
    if ($smfgSettings['enable_modification_images']) {
        $context['enable_modification_images_check'] = 'checked="checked"';
    } else {
        $context['enable_modification_images_check'] = "";
    }
    if ($smfgSettings['enable_quartermile_images']) {
        $context['enable_quartermile_images_check'] = 'checked="checked"';
    } else {
        $context['enable_quartermile_images_check'] = "";
    }
    if ($smfgSettings['enable_dynorun_images']) {
        $context['enable_dynorun_images_check'] = 'checked="checked"';
    } else {
        $context['enable_dynorun_images_check'] = "";
    }
    if ($smfgSettings['enable_lap_images']) {
        $context['enable_lap_images_check'] = 'checked="checked"';
    } else {
        $context['enable_lap_images_check'] = "";
    }
    if ($smfgSettings['enable_remote_images']) {
        $context['enable_remote_images_check'] = 'checked="checked"';
    } else {
        $context['enable_remote_images_check'] = "";
    }
    if ($smfgSettings['store_remote_images_locally']) {
        $context['store_remote_images_locally_check'] = 'checked="checked"';
    } else {
        $context['store_remote_images_locally_check'] = "";
    }
    if ($smfgSettings['enable_watermark']) {
        $context['enable_watermark_check'] = 'checked="checked"';
    } else {
        $context['enable_watermark_check'] = "";
    }
    if ($smfgSettings['gcard_watermark']) {
        $context['gcard_watermark_check'] = 'checked="checked"';
    } else {
        $context['gcard_watermark_check'] = "";
    }
    if ($smfgSettings['enable_lightbox']) {
        $context['enable_lightbox_check'] = 'checked="checked"';
    } else {
        $context['enable_lightbox_check'] = "";
    }

    $context['enable_watermark_thumb_off'] = "";
    $context['enable_watermark_thumb_on'] = "";
    $context['enable_watermark_thumb_onsized'] = "";
    if ($smfgSettings['enable_watermark_thumb'] == 1) {
        $context['enable_watermark_thumb_on'] = 'selected="selected"';
    } else {
        if ($smfgSettings['enable_watermark_thumb'] == 2) {
            $context['enable_watermark_thumb_onsized'] = 'selected="selected"';
        } else {
            $context['enable_watermark_thumb_off'] = 'selected="selected"';
        }
    }

    $context['watermark_position_0'] = "";
    $context['watermark_position_1'] = "";
    $context['watermark_position_2'] = "";
    $context['watermark_position_3'] = "";
    $context['watermark_position_4'] = "";
    $context['watermark_position_5'] = "";
    $context['watermark_position_6'] = "";
    $context['watermark_position_7'] = "";
    $context['watermark_position_8'] = "";
    if ($smfgSettings['watermark_position'] == 0) {
        $context['watermark_position_0'] = 'checked="checked"';
    } else {
        if ($smfgSettings['watermark_position'] == 1) {
            $context['watermark_position_1'] = 'checked="checked"';
        } else {
            if ($smfgSettings['watermark_position'] == 2) {
                $context['watermark_position_2'] = 'checked="checked"';
            } else {
                if ($smfgSettings['watermark_position'] == 3) {
                    $context['watermark_position_3'] = 'checked="checked"';
                } else {
                    if ($smfgSettings['watermark_position'] == 4) {
                        $context['watermark_position_4'] = 'checked="checked"';
                    } else {
                        if ($smfgSettings['watermark_position'] == 5) {
                            $context['watermark_position_5'] = 'checked="checked"';
                        } else {
                            if ($smfgSettings['watermark_position'] == 6) {
                                $context['watermark_position_6'] = 'checked="checked"';
                            } else {
                                if ($smfgSettings['watermark_position'] == 7) {
                                    $context['watermark_position_7'] = 'checked="checked"';
                                } else {
                                    if ($smfgSettings['watermark_position'] == 8) {
                                        $context['watermark_position_8'] = 'checked="checked"';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $context['image_processor_none'] = "";
    $context['image_processor_im'] = "";
    $context['image_processor_gd'] = "";
    if ($smfgSettings['image_processor'] == 1) {
        $context['image_processor_im'] = 'selected="selected"';
    } else {
        if ($smfgSettings['image_processor'] == 2) {
            $context['image_processor_gd'] = 'selected="selected"';
        } else {
            $context['image_processor_none'] = 'selected="selected"';
        }
    }

    // check if the uploads directory is writable
    $uploaddir = $smfgSettings['upload_directory'];
    $full_uploaddir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $cachedir = $smfgSettings['upload_directory'] . 'cache/';
    $full_cachedir = $boarddir . '/' . $smfgSettings['upload_directory'] . 'cache/';
    $context['write_output'] = '<table cellpadding="2" cellspacing="2">';
    // upload dir writable?
    if (!is_writable($full_uploaddir)) {
        $context['write_output'] .= '<tr><td valign="top"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="Bad" /></td><td style="background-color: #ff0000; color: #FFF; padding: 2px;">' . $uploaddir . ' is not writable.</td></tr>';
    } else {
        $context['write_output'] .= '<tr><td valign="top"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="Good" /></td><td style="background-color: #00cf49; color: #FFF; padding: 2px;">' . $uploaddir . ' is writable.</td></tr>';
    }
    // attempt to make the directory if it doesnt exist
    if (!is_dir($full_cachedir)) {
        if (mkdir($full_cachedir)) {
            $context['write_output'] .= '<tr><td valign="top"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="Good" /></td><td style="background-color: #00cf49; color: #FFF; padding: 2px;">' . $cachedir . ' created.</td></tr>';
        } else {
            $context['write_output'] .= '<tr><td valign="top"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="Bad" /></td><td style="background-color: #ff0000; color: #FFF; padding: 2px;">' . $cachedir . ' could not be created.</td></tr>';
        }
    } else {
        $context['write_output'] .= '<tr><td valign="top"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="Good" /></td><td style="background-color: #00cf49; color: #FFF; padding: 2px;">' . $cachedir . ' exists.</td></tr>';
    }
    // cache writable?
    if (!is_writable($full_cachedir)) {
        $context['write_output'] .= '<tr><td valign="top"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="Bad" /></td><td style="background-color: #ff0000; color: #FFF; padding: 2px;">' . $cachedir . ' is not writable.</td></tr>';
    } else {
        $context['write_output'] .= '<tr><td valign="top"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="Good" /></td><td style="background-color: #00cf49; color: #FFF; padding: 2px;">' . $cachedir . ' is writable.</td></tr>';
    }
    $context['write_output'] .= '</table>';

    // Check for ImageMagick directories
    $context['im_convert'] = 0;
    $context['im_composite'] = 0;
    $context['im_gd'] = 0;

    // See if exec is enabled on this server, need to test this...
    if (function_exists('exec')) {

        $output = array();
        $returnval = 1;
        exec($smfgSettings['im_convert'], $output, $returnval);
        if ($returnval == 0) {
            $context['im_convert'] = "OK";
            if (preg_match('/Version: ImageMagick ([0-9.]+)/i', $output[0], $matches)) {
                $context['im_convert'] = $matches[1];
            }
        }
        unset($output);
        unset($returnval);

        $output = array();
        $returnval = 1;
        exec($smfgSettings['im_composite'], $output, $returnval);
        if ($returnval == 0) {
            $context['im_composite'] = "OK";
            if (preg_match('/Version: ImageMagick ([0-9.]+)/i', $output[0], $matches)) {
                $context['im_composite'] = $matches[1];
            }
        }
        unset($output);
        unset($returnval);

        $context['exec_output'] = '<table cellpadding="2" cellspacing="2">';
        $context['exec_output'] .= '<tr><td valign="top"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="Good" /></td><td style="background-color: #00cf49; color: #FFF; padding: 2px;">PHP function exec() is available.</td></tr>';
        $context['exec_output'] .= '</table>';

    } else {

        // exec() is disabled        
        $context['exec_output'] = '<table cellpadding="2" cellspacing="2">';
        $context['exec_output'] .= '<tr><td valign="top"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="Bad" /></td><td style="background-color: #ff0000; color: #FFF; padding: 2px;">PHP function exec() is not available.  You cannot use ImageMagick as an image processor without this function.  Please switch your image processor to GD.</td></tr>';
        $context['exec_output'] .= '</table>';

        $context['exec_disabled'] = 1;

    }

    $gdinfo = gd_info();
    if ($gdinfo["GD Version"]) {
        $context['im_gd'] = $gdinfo["GD Version"];
    }
    unset($gdinfo);
}

// Update Image Settings
function UpdateImage()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Check Permissions
    isAllowedTo('manage_garage_images');

    // Validate the session
    checkSession();

    // Define all indices
    if (!isset($_POST['config']['enable_vehicle_images'])) {
        $_POST['config']['enable_vehicle_images'] = 0;
    }
    if (!isset($_POST['config']['enable_modification_images'])) {
        $_POST['config']['enable_modification_images'] = 0;
    }
    if (!isset($_POST['config']['enable_quartermile_images'])) {
        $_POST['config']['enable_quartermile_images'] = 0;
    }
    if (!isset($_POST['config']['enable_dynorun_images'])) {
        $_POST['config']['enable_dynorun_images'] = 0;
    }
    if (!isset($_POST['config']['enable_lap_images'])) {
        $_POST['config']['enable_lap_images'] = 0;
    }
    if (!isset($_POST['config']['enable_remote_images'])) {
        $_POST['config']['enable_remote_images'] = 0;
    }
    if (!isset($_POST['config']['store_remote_images_locally'])) {
        $_POST['config']['store_remote_images_locally'] = 0;
    }
    if (!isset($_POST['config']['enable_watermark'])) {
        $_POST['config']['enable_watermark'] = 0;
    }
    if (!isset($_POST['config']['gcard_watermark'])) {
        $_POST['config']['gcard_watermark'] = 0;
    }
    if (!isset($_POST['config']['enable_watermark_thumb'])) {
        $_POST['config']['enable_watermark_thumb'] = 0;
    }
    if (!isset($_POST['config']['enable_lightbox'])) {
        $_POST['config']['enable_lightbox'] = 0;
    }
    if (!isset($_POST['config']['watermark_position'])) {
        $_POST['config']['watermark_position'] = "9";
    }
    if (!isset($_POST['config']['watermark_opacity'])) {
        $_POST['config']['watermark_opacity'] = "100";
    }
    if (!isset($_POST['config']['image_processor'])) {
        $_POST['config']['image_processor'] = 0;
    }

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_vehicle_images}
        WHERE config_name = "enable_vehicle_images"',
        array(
            'enable_vehicle_images' => $_POST['config']['enable_vehicle_images'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_modification_images}
        WHERE config_name = "enable_modification_images"',
        array(
            'enable_modification_images' => $_POST['config']['enable_modification_images'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_quartermile_images}
        WHERE config_name = "enable_quartermile_images"',
        array(
            'enable_quartermile_images' => $_POST['config']['enable_quartermile_images'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_dynorun_images}
        WHERE config_name = "enable_dynorun_images"',
        array(
            'enable_dynorun_images' => $_POST['config']['enable_dynorun_images'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_lap_images}
        WHERE config_name = "enable_lap_images"',
        array(
            'enable_lap_images' => $_POST['config']['enable_lap_images'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_remote_images}
        WHERE config_name = "enable_remote_images"',
        array(
            'enable_remote_images' => $_POST['config']['enable_remote_images'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:remote_timeout}
        WHERE config_name = "remote_timeout"',
        array(
            'remote_timeout' => $_POST['config']['remote_timeout'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:store_remote_images_locally}
        WHERE config_name = "store_remote_images_locally"',
        array(
            'store_remote_images_locally' => $_POST['config']['store_remote_images_locally'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_lightbox}
        WHERE config_name = "enable_lightbox"',
        array(
            'enable_lightbox' => $_POST['config']['enable_lightbox'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:gallery_limit}
        WHERE config_name = "gallery_limit"',
        array(
            'gallery_limit' => $_POST['config']['gallery_limit'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:max_image_kbytes}
        WHERE config_name = "max_image_kbytes"',
        array(
            'max_image_kbytes' => $_POST['config']['max_image_kbytes'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:max_image_resolution}
        WHERE config_name = "max_image_resolution"',
        array(
            'max_image_resolution' => $_POST['config']['max_image_resolution'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:thumbnail_resolution}
        WHERE config_name = "thumbnail_resolution"',
        array(
            'thumbnail_resolution' => $_POST['config']['thumbnail_resolution'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {string:upload_directory}
        WHERE config_name = "upload_directory"',
        array(
            'upload_directory' => $_POST['config']['upload_directory'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_watermark}
        WHERE config_name = "enable_watermark"',
        array(
            'enable_watermark' => $_POST['config']['enable_watermark'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:gcard_watermark}
        WHERE config_name = "gcard_watermark"',
        array(
            'gcard_watermark' => $_POST['config']['gcard_watermark'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_watermark_thumb}
        WHERE config_name = "enable_watermark_thumb"',
        array(
            'enable_watermark_thumb' => $_POST['config']['enable_watermark_thumb'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:watermark_position}
        WHERE config_name = "watermark_position"',
        array(
            'watermark_position' => $_POST['config']['watermark_position'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:watermark_opacity}
        WHERE config_name = "watermark_opacity"',
        array(
            'watermark_opacity' => $_POST['config']['watermark_opacity'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:image_processor} 
        WHERE config_name = "image_processor"',
        array(
            'image_processor' => $_POST['config']['image_processor'],
        )
    );

    /*/ Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = "".$_POST['config']['watermark_type']."" 
        WHERE config_name = "watermark_type"',
        array(
            'value' => $_POST['config']['value'],
        )
    );*/

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {string:watermark_source}
        WHERE config_name = "watermark_source"',
        array(
            'watermark_source' => $_POST['config']['watermark_source'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {string:im_convert}
        WHERE config_name = "im_convert"',
        array(
            'im_convert' => $_POST['config']['im_convert'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {string:im_composite} 
        WHERE config_name = "im_composite"',
        array(
            'im_composite' => $_POST['config']['im_composite'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garagesettings;sa=imagesettings');
    if ($_POST['rebuild']) {
        $newurl = $_POST['redirecturl2'];
    } else {
        $newurl = $_POST['redirecturl'];
    }
    header('Location: ' . $newurl);

}

// Video Settings
function VideoSettings()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'video_settings';
    $context['page_title'] = $txt['smfg_garage'] . ' ' . $txt['smfg_settings'];

    // Check Permissions
    isAllowedTo('manage_garage_videos');

    // Check for config values and 'check' enabled options
    if ($smfgSettings['enable_vehicle_video']) {
        $context['enable_vehicle_video_check'] = 'checked="checked"';
    } else {
        $context['enable_vehicle_video_check'] = "";
    }
    if ($smfgSettings['enable_modification_video']) {
        $context['enable_modification_video_check'] = 'checked="checked"';
    } else {
        $context['enable_modification_video_check'] = "";
    }
    if ($smfgSettings['enable_quartermile_video']) {
        $context['enable_quartermile_video_check'] = 'checked="checked"';
    } else {
        $context['enable_quartermile_video_check'] = "";
    }
    if ($smfgSettings['enable_dynorun_video']) {
        $context['enable_dynorun_video_check'] = 'checked="checked"';
    } else {
        $context['enable_dynorun_video_check'] = "";
    }
    if ($smfgSettings['enable_laptime_video']) {
        $context['enable_laptime_video_check'] = 'checked="checked"';
    } else {
        $context['enable_laptime_video_check'] = "";
    }


}

// Update Video Settings
function UpdateVideo()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Check Permissions
    isAllowedTo('manage_garage_videos');

    // Validate the session
    checkSession();

    // Define all indices
    if (!isset($_POST['config']['enable_vehicle_video'])) {
        $_POST['config']['enable_vehicle_video'] = 0;
    }
    if (!isset($_POST['config']['enable_modification_video'])) {
        $_POST['config']['enable_modification_video'] = 0;
    }
    if (!isset($_POST['config']['enable_quartermile_video'])) {
        $_POST['config']['enable_quartermile_video'] = 0;
    }
    if (!isset($_POST['config']['enable_dynorun_video'])) {
        $_POST['config']['enable_dynorun_video'] = 0;
    }
    if (!isset($_POST['config']['enable_laptime_video'])) {
        $_POST['config']['enable_laptime_video'] = 0;
    }

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_vehicle_video}
        WHERE config_name = "enable_vehicle_video"',
        array(
            'enable_vehicle_video' => $_POST['config']['enable_vehicle_video'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_modification_video}
        WHERE config_name = "enable_modification_video"',
        array(
            'enable_modification_video' => $_POST['config']['enable_modification_video'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_quartermile_video} 
        WHERE config_name = "enable_quartermile_video"',
        array(
            'enable_quartermile_video' => $_POST['config']['enable_quartermile_video'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_dynorun_video}
        WHERE config_name = "enable_dynorun_video"',
        array(
            'enable_dynorun_video' => $_POST['config']['enable_dynorun_video'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_laptime_video}
        WHERE config_name = "enable_laptime_video"',
        array(
            'enable_laptime_video' => $_POST['config']['enable_laptime_video'],
        )
    );

    // Update Config Settings
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:gallery_limit_video}
        WHERE config_name = "gallery_limit_video"',
        array(
            'gallery_limit_video' => $_POST['config']['gallery_limit_video'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garagesettings;sa=imagesettings');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Module Settings
function ModuleSettings()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'module_settings';
    $context['page_title'] = $txt['smfg_garage'] . ' ' . $txt['smfg_settings'];

    // Check Permissions
    isAllowedTo('manage_garage_modules');

    // Check for config values and 'check' enabled options
    if ($smfgSettings['enable_modification']) {
        $context['enable_modification_check'] = 'checked="checked"';
    } else {
        $context['enable_modification_check'] = "";
    }
    if ($smfgSettings['enable_modification_approval']) {
        $context['enable_modification_approval_check'] = 'checked="checked"';
    } else {
        $context['enable_modification_approval_check'] = "";
    }
    if ($smfgSettings['enable_quartermile']) {
        $context['enable_quartermile_check'] = 'checked="checked"';
    } else {
        $context['enable_quartermile_check'] = "";
    }
    if ($smfgSettings['enable_quartermile_approval']) {
        $context['enable_quartermile_approval_check'] = 'checked="checked"';
    } else {
        $context['enable_quartermile_approval_check'] = "";
    }
    if ($smfgSettings['enable_quartermile_image_required']) {
        $context['enable_quartermile_image_required_check'] = 'checked="checked"';
    } else {
        $context['enable_quartermile_image_required_check'] = "";
    }
    if ($smfgSettings['enable_dynorun']) {
        $context['enable_dynorun_check'] = 'checked="checked"';
    } else {
        $context['enable_dynorun_check'] = "";
    }
    if ($smfgSettings['enable_dynorun_approval']) {
        $context['enable_dynorun_approval_check'] = 'checked="checked"';
    } else {
        $context['enable_dynorun_approval_check'] = "";
    }
    if ($smfgSettings['enable_dynorun_image_required']) {
        $context['enable_dynorun_image_required_check'] = 'checked="checked"';
    } else {
        $context['enable_dynorun_image_required_check'] = "";
    }
    if ($smfgSettings['enable_laptimes']) {
        $context['enable_laptimes_check'] = 'checked="checked"';
    } else {
        $context['enable_laptimes_check'] = "";
    }
    if ($smfgSettings['enable_user_add_track']) {
        $context['enable_user_add_track_check'] = 'checked="checked"';
    } else {
        $context['enable_user_add_track_check'] = "";
    }
    if ($smfgSettings['enable_lap_approval']) {
        $context['enable_lap_approval_check'] = 'checked="checked"';
    } else {
        $context['enable_lap_approval_check'] = "";
    }
    if ($smfgSettings['enable_lap_image_required']) {
        $context['enable_lap_image_required_check'] = 'checked="checked"';
    } else {
        $context['enable_lap_image_required_check'] = "";
    }
    if ($smfgSettings['enable_track_approval']) {
        $context['enable_track_approval_check'] = 'checked="checked"';
    } else {
        $context['enable_track_approval_check'] = "";
    }
    if ($smfgSettings['enable_insurance']) {
        $context['enable_insurance_check'] = 'checked="checked"';
    } else {
        $context['enable_insurance_check'] = "";
    }
    if ($smfgSettings['enable_guestbooks']) {
        $context['enable_guestbooks_check'] = 'checked="checked"';
    } else {
        $context['enable_guestbooks_check'] = "";
    }
    if ($smfgSettings['enable_guestbooks_bbcode']) {
        $context['enable_guestbooks_bbcode_check'] = 'checked="checked"';
    } else {
        $context['enable_guestbooks_bbcode_check'] = "";
    }
    if ($smfgSettings['enable_guestbooks_comment_approval']) {
        $context['enable_guestbooks_comment_approval_check'] = 'checked="checked"';
    } else {
        $context['enable_guestbooks_comment_approval_check'] = "";
    }
    if ($smfgSettings['enable_service']) {
        $context['enable_service_check'] = 'checked="checked"';
    } else {
        $context['enable_service_check'] = "";
    }
    if ($smfgSettings['enable_blogs']) {
        $context['enable_blogs_check'] = 'checked="checked"';
    } else {
        $context['enable_blogs_check'] = "";
    }
    if ($smfgSettings['enable_blogs_bbcode']) {
        $context['enable_blogs_bbcode_check'] = 'checked="checked"';
    } else {
        $context['enable_blogs_bbcode_check'] = "";
    }

}

// Update Module Settings
function UpdateModule()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $smfgSettings, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Check Permissions
    isAllowedTo('manage_garage_modules');

    // Validate the session
    checkSession();

    // Define all indices
    if (!isset($_POST['config']['enable_modification'])) {
        $_POST['config']['enable_modification'] = 0;
    }
    if (!isset($_POST['config']['enable_modification_approval'])) {
        $_POST['config']['enable_modification_approval'] = 0;
    }
    if (!isset($_POST['config']['enable_quartermile'])) {
        $_POST['config']['enable_quartermile'] = 0;
    }
    if (!isset($_POST['config']['enable_quartermile_approval'])) {
        $_POST['config']['enable_quartermile_approval'] = 0;
    }
    if (!isset($_POST['config']['enable_quartermile_image_required'])) {
        $_POST['config']['enable_quartermile_image_required'] = 0;
    }
    if (!isset($_POST['config']['enable_dynorun'])) {
        $_POST['config']['enable_dynorun'] = 0;
    }
    if (!isset($_POST['config']['enable_dynorun_approval'])) {
        $_POST['config']['enable_dynorun_approval'] = 0;
    }
    if (!isset($_POST['config']['enable_dynorun_image_required'])) {
        $_POST['config']['enable_dynorun_image_required'] = 0;
    }
    if (!isset($_POST['config']['enable_laptimes'])) {
        $_POST['config']['enable_laptimes'] = 0;
    }
    if (!isset($_POST['config']['enable_user_add_track'])) {
        $_POST['config']['enable_user_add_track'] = 0;
    }
    if (!isset($_POST['config']['enable_lap_approval'])) {
        $_POST['config']['enable_lap_approval'] = 0;
    }
    if (!isset($_POST['config']['enable_lap_image_required'])) {
        $_POST['config']['enable_lap_image_required'] = 0;
    }
    if (!isset($_POST['config']['enable_track_approval'])) {
        $_POST['config']['enable_track_approval'] = 0;
    }
    if (!isset($_POST['config']['enable_insurance'])) {
        $_POST['config']['enable_insurance'] = 0;
    }
    if (!isset($_POST['config']['enable_guestbooks'])) {
        $_POST['config']['enable_guestbooks'] = 0;
    }
    if (!isset($_POST['config']['enable_guestbooks_bbcode'])) {
        $_POST['config']['enable_guestbooks_bbcode'] = 0;
    }
    if (!isset($_POST['config']['enable_guestbooks_comment_approval'])) {
        $_POST['config']['enable_guestbooks_comment_approval'] = 0;
    }
    if (!isset($_POST['config']['enable_service'])) {
        $_POST['config']['enable_service'] = 0;
    }
    if (!isset($_POST['config']['enable_blogs'])) {
        $_POST['config']['enable_blogs'] = 0;
    }
    if (!isset($_POST['config']['enable_blogs_bbcode'])) {
        $_POST['config']['enable_blogs_bbcode'] = 0;
    }

    // Update Config Settings  
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_modification}
        WHERE config_name = "enable_modification"',
        array(
            'enable_modification' => $_POST['config']['enable_modification'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_modification_approval}
        WHERE config_name = "enable_modification_approval"',
        array(
            'enable_modification_approval' => $_POST['config']['enable_modification_approval'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_quartermile}
        WHERE config_name = "enable_quartermile"',
        array(
            'enable_quartermile' => $_POST['config']['enable_quartermile'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_quartermile_approval}
        WHERE config_name = "enable_quartermile_approval"',
        array(
            'enable_quartermile_approval' => $_POST['config']['enable_quartermile_approval'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_quartermile_image_required}
        WHERE config_name = "enable_quartermile_image_required"',
        array(
            'enable_quartermile_image_required' => $_POST['config']['enable_quartermile_image_required'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_dynorun}
        WHERE config_name = "enable_dynorun"',
        array(
            'enable_dynorun' => $_POST['config']['enable_dynorun'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_dynorun_approval}
        WHERE config_name = "enable_dynorun_approval"',
        array(
            'enable_dynorun_approval' => $_POST['config']['enable_dynorun_approval'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_dynorun_image_required}
        WHERE config_name = "enable_dynorun_image_required"',
        array(
            'enable_dynorun_image_required' => $_POST['config']['enable_dynorun_image_required'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_laptimes}
        WHERE config_name = "enable_laptimes"',
        array(
            'enable_laptimes' => $_POST['config']['enable_laptimes'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_user_add_track}
        WHERE config_name = "enable_user_add_track"',
        array(
            'enable_user_add_track' => $_POST['config']['enable_user_add_track'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_lap_approval}
        WHERE config_name = "enable_lap_approval"',
        array(
            'enable_lap_approval' => $_POST['config']['enable_lap_approval'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_lap_image_required}
        WHERE config_name = "enable_lap_image_required"',
        array(
            'enable_lap_image_required' => $_POST['config']['enable_lap_image_required'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_track_approval}
        WHERE config_name = "enable_track_approval"',
        array(
            'enable_track_approval' => $_POST['config']['enable_track_approval'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_insurance}
        WHERE config_name = "enable_insurance"',
        array(
            'enable_insurance' => $_POST['config']['enable_insurance'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_guestbooks}
        WHERE config_name = "enable_guestbooks"',
        array(
            'enable_guestbooks' => $_POST['config']['enable_guestbooks'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_guestbooks_bbcode}
        WHERE config_name = "enable_guestbooks_bbcode"',
        array(
            'enable_guestbooks_bbcode' => $_POST['config']['enable_guestbooks_bbcode'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_guestbooks_comment_approval}
        WHERE config_name = "enable_guestbooks_comment_approval"',
        array(
            'enable_guestbooks_comment_approval' => $_POST['config']['enable_guestbooks_comment_approval'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_service}
        WHERE config_name = "enable_service"',
        array(
            'enable_service' => $_POST['config']['enable_service'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_blogs}
        WHERE config_name = "enable_blogs"',
        array(
            'enable_blogs' => $_POST['config']['enable_blogs'],
        )
    );

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config 
        SET config_value = {int:enable_blogs_bbcode}
        WHERE config_name = "enable_blogs_bbcode"',
        array(
            'enable_blogs_bbcode' => $_POST['config']['enable_blogs_bbcode'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garagesettings;sa=modulesettings');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Modify block position
function MoveBlock()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    checkSession('get');

    // Check Permissions
    isAllowedTo('manage_garage_index');

    if ($_GET['direction'] == 'up') {

        // Find our target position
        $context['target_position'] = $_GET['position'] - 1;

    } else {
        if ($_GET['direction'] == 'down') {

            // Find our target position
            $context['target_position'] = $_GET['position'] + 1;

        }
    }

    // Get the target position's BID
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_blocks
        WHERE position = {int:position}',
        array(
            'position' => $context['target_position'],
        )
    );
    list($context['target_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result'] ($request);

    // First set our target to the current position
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_blocks
        SET position = {int:position}
        WHERE id = {int:id}',
        array(
            'position' => $_GET['position'],
            'id' => $context['target_id'],
        )
    );

    // Then set our current block to the target position
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_blocks
        SET position = {int:position}
        WHERE id = {int:id}',
        array(
            'position' => $context['target_position'],
            'id' => $_GET['BID'],
        )
    );

    header('Location: ' . $scripturl . '?action=admin;area=garagesettings;sa=indexsettings');

}

// Disable block
function DisableBlock()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    checkSession('get');

    // Check Permissions
    isAllowedTo('manage_garage_index');

    // Get block config title
    $request = $smcFunc['db_query']('', '
        SELECT config_title
        FROM {db_prefix}garage_blocks
        WHERE id = {int:id}',
        array(
            'id' => $_GET['BID'],
        )
    );
    list($context['blocks']['config_title']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result'] ($request);

    // Update 'blocks' table
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_blocks 
        SET enabled = 0
        WHERE id = {int:id}',
        array(
            'id' => $_GET['BID'],
        )
    );

    // Update config table
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config
        SET config_value = 0
        WHERE config_name = {string:config_name}',
        array(
            'config_name' => $context['blocks']['config_title'],
        )
    );

    header('Location: ' . $scripturl . '?action=admin;area=garagesettings;sa=indexsettings');

}

// Enable block
function EnableBlock()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    checkSession('get');

    // Check Permissions
    isAllowedTo('manage_garage_index');

    // Get block config title
    $request = $smcFunc['db_query']('', '
        SELECT config_title
        FROM {db_prefix}garage_blocks
        WHERE id = {int:id}',
        array(
            'id' => $_GET['BID'],
        )
    );
    list($context['blocks']['config_title']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result'] ($request);

    // Update 'blocks' table
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_blocks 
        SET enabled = 1
        WHERE id = {int:id}',
        array(
            'id' => $_GET['BID'],
        )
    );

    // Update config table
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_config
        SET config_value = 1
        WHERE config_name = {string:config_name}',
        array(
            'config_name' => $context['blocks']['config_title'],
        )
    );

    header('Location: ' . $scripturl . '?action=admin;area=garagesettings;sa=indexsettings');

}

// Add Member to Notification List
function AddNotify()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    checkSession();

    // Check Permissions
    isAllowedTo('manage_garage_general');

    // Get member info
    $request = $smcFunc['db_query']('', '
        SELECT id_member
        FROM {db_prefix}members
        WHERE member_name = {string:member_name}',
        array(
            'member_name' => $_POST['username'],
        )
    );
    $member = $smcFunc['db_num_rows']($request);
    if ($member <= 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_nonexistent_member_error', false);
    }
    $row = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result'] ($request);

    // Insert new member
    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_notifications',
        array(
            'user_id' => 'int',
        ),
        array(
            $row[0],
        ),
        array(// no data
        )
    );

    header('Location: ' . $scripturl . '?action=admin;area=garagesettings;sa=general');

}

// Remove Member from Notification List
function DeleteNotify()
{
    global $txt, $modSettings, $context, $db_prefix, $sourcedir, $user_info;
    global $func, $scripturl, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    checkSession('get');

    // Check Permissions
    isAllowedTo('manage_garage_general');

    // Delete member from list
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_notifications
        WHERE id = {int:id}',
        array(
            'id' => $_GET['ID'],
        )
    );

    header('Location: ' . $scripturl . '?action=admin;area=garagesettings;sa=general');

}
