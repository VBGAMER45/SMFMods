<?php
/**********************************************************************************
 * Garage.php                                                                      *
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

function Garage()
{

    global $scripturl, $txt, $modSettings, $context, $settings;
    global $smcFunc, $smfgSettings, $sourcedir;

    // Check Permissions
    isAllowedTo('view_garage');

    // We need our functions!
    require_once('GarageFunctions.php');
    require_once($sourcedir . '/Subs-Menu.php');

    // Load settings
    loadSmfgConfig();

    $context['page_title'] = 'Garage';

    // This is gonna be needed...
    loadTemplate('Garage', 'garage');
    loadLanguage('Garage');

    // SMF 2.1: Load CSS/JS via API instead of template injection
    if (defined('SMF_VERSION') && version_compare(SMF_VERSION, '2.1', '>='))
    {
        // SMF 2.1 already includes jQuery 3.x, so don't load bundled jQuery 1.6.1
        loadJavascriptFile('jquery.preload.min.js', array('default_theme' => true));
        loadJavascriptFile('jquery.tipTip.minified.js', array('default_theme' => true));
        loadJavascriptFile('jquery.jeditable.mini.js', array('default_theme' => true));
        loadJavascriptFile('jquery-smfg.js', array('default_theme' => true));
        loadJavascriptFile('garage_functions.js', array('default_theme' => true));
        loadCSSFile('smfg_tips.css', array('default_theme' => true));
    }

    // Set our index includes
    $context['smfg_ajax'] = 0;
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    if ($smfgSettings['disable_garage'] && !$context['user']['is_admin']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_error', false);
    }

    $context['garage_sa'] = !empty($_GET['sa']) ? $_GET['sa'] : 'main';

    // $g_subActions array format:
    // 'subaction' => array('label', 'function', 'is_selected')

    // Build the subactions array for the garage menu

    $g_subActions = array(
        'main' => array(&$txt['smfg_main'], 'G_Main', $context['garage_sa'] == 'main')
    );
    if ($context['user']['is_logged']) {
        $g_subActions['user_garage'] = array(
            &$txt['smfg_my_vehicles'],
            'G_User_Garage',
            $context['garage_sa'] == 'user_garage' || $context['garage_sa'] == 'add_vehicle' || $context['garage_sa'] == 'edit_vehicle' || $context['garage_sa'] == 'add_modification' || $context['garage_sa'] == 'add_insurance' || $context['garage_sa'] == 'add_quartermile' || $context['garage_sa'] == 'add_dynorun' || $context['garage_sa'] == 'add_laptime' || $context['garage_sa'] == 'add_service' || $context['garage_sa'] == 'edit_modification' || $context['garage_sa'] == 'edit_insurance' || $context['garage_sa'] == 'edit_dynorun' || $context['garage_sa'] == 'edit_quartermile' || $context['garage_sa'] == 'edit_laptime' || $context['garage_sa'] == 'edit_service' || $context['garage_sa'] == 'edit_blog'
        );
    }
    if ($smfgSettings['enable_browse_menu']) {
        $g_subActions['browse'] = array(
            &$txt['smfg_browse'],
            'G_Browse',
            $context['garage_sa'] == 'browse' || $context['garage_sa'] == 'view_garage' || $context['garage_sa'] == 'view_vehicle' || $context['garage_sa'] == 'view_modification' || $context['garage_sa'] == 'view_dynorun' || $context['garage_sa'] == 'view_quartermile' || $context['garage_sa'] == 'view_laptime' || $context['garage_sa'] == 'view_track' || $context['garage_sa'] == 'modifications' || $context['garage_sa'] == 'mostmodified' || $context['garage_sa'] == 'mostviewed' || $context['garage_sa'] == 'latestservice' || $context['garage_sa'] == 'toprated' || $context['garage_sa'] == 'mostspent' || $context['garage_sa'] == 'latestblog' || $context['garage_sa'] == 'latestvideo'
        );
    }
    if ($smfgSettings['enable_search_menu']) {
        $g_subActions['g_search'] = array(
            &$txt['smfg_search'],
            'G_Search',
            $context['garage_sa'] == 'g_search' || $context['garage_sa'] == 'search_results'
        );
    }
    if ($smfgSettings['enable_insurance_review_menu'] && $smfgSettings['enable_insurance']) {
        $g_subActions['insurance'] = array(
            &$txt['smfg_insurance'],
            'G_Insurance',
            $context['garage_sa'] == 'insurance' || $context['garage_sa'] == 'insurance_review'
        );
    }
    if ($smfgSettings['enable_shop_review_menu']) {
        $g_subActions['shops'] = array(
            &$txt['smfg_shops'],
            'G_Shops',
            $context['garage_sa'] == 'shops' || $context['garage_sa'] == 'shop_review'
        );
    }
    if ($smfgSettings['enable_garage_review_menu']) {
        $g_subActions['garages'] = array(
            &$txt['smfg_garages'],
            'G_Garages',
            $context['garage_sa'] == 'garages' || $context['garage_sa'] == 'garage_review'
        );
    }
    if ($smfgSettings['enable_quartermile_menu'] && $smfgSettings['enable_quartermile']) {
        $g_subActions['quartermiles'] = array(
            &$txt['smfg_quartermiles'],
            'G_Quartermiles',
            $context['garage_sa'] == 'quartermiles'
        );
    }
    if ($smfgSettings['enable_dynorun_menu'] && $smfgSettings['enable_dynorun']) {
        $g_subActions['dynoruns'] = array(&$txt['smfg_dynoruns'], 'G_Dynoruns', $context['garage_sa'] == 'dynoruns');
    }
    if ($smfgSettings['enable_lap_menu'] && $smfgSettings['enable_laptimes']) {
        $g_subActions['laptimes'] = array(&$txt['smfg_laptimes'], 'G_Laptimes', $context['garage_sa'] == 'laptimes');
    }

    // Set up the sort links.
    $context['sort_links'] = array();
    foreach ($g_subActions as $act => $text) {
        $context['sort_links'][] = array(
            'label' => $text[0],
            'action' => $act,
            'selected' => $text[2],
        );
    }

    // Links that will not be needed in the menu.
    $g_subActions['add_vehicle'] = array('', 'G_Add_Vehicle');
    $g_subActions['insert_vehicle'] = array('', 'G_Insert_Vehicle');
    $g_subActions['view_garage'] = array('', 'G_View_Garage');
    $g_subActions['view_vehicle'] = array('', 'G_View_Vehicle');
    $g_subActions['edit_vehicle'] = array('', 'G_Edit_Vehicle');
    $g_subActions['update_vehicle'] = array('', 'G_Update_Vehicle');
    $g_subActions['delete_vehicle'] = array('', 'G_Delete_Vehicle');
    $g_subActions['insert_vehicle_images'] = array('', 'G_Insert_Vehicle_Images');
    $g_subActions['remove_vehicle_image'] = array('', 'G_Remove_Vehicle_Image');
    $g_subActions['insert_vehicle_video'] = array('', 'G_Insert_Vehicle_Video');
    $g_subActions['set_main_vehicle'] = array('', 'G_Set_Main_Vehicle');
    $g_subActions['set_hilite_image'] = array('', 'G_Set_Hilite_Image');
    $g_subActions['set_hilite_image_mod'] = array('', 'G_Set_Hilite_Image_Mod');
    $g_subActions['set_hilite_image_quartermile'] = array('', 'G_Set_Hilite_Image_Quartermile');
    $g_subActions['set_hilite_image_dynorun'] = array('', 'G_Set_Hilite_Image_Dynorun');
    $g_subActions['set_hilite_image_laptime'] = array('', 'G_Set_Hilite_Image_Laptime');
    $g_subActions['update_text'] = array('', 'G_Update_Text');
    $g_subActions['update_attach_desc'] = array('', 'G_Update_Attach_Desc');
    $g_subActions['update_video_desc'] = array('', 'G_Update_Video_Desc');
    $g_subActions['view_modification'] = array('', 'G_View_Modification');
    $g_subActions['add_modification'] = array('', 'G_Add_Modification');
    $g_subActions['insert_modification'] = array('', 'G_Insert_Modification');
    $g_subActions['edit_modification'] = array('', 'G_Edit_Modification');
    $g_subActions['update_modification'] = array('', 'G_Update_Modification');
    $g_subActions['delete_modification'] = array('', 'G_Delete_Modification');
    $g_subActions['insert_modification_images'] = array('', 'G_Insert_Modification_Images');
    $g_subActions['remove_modification_image'] = array('', 'G_Remove_Modification_Image');
    $g_subActions['insert_modification_video'] = array('', 'G_Insert_Modification_Video');
    $g_subActions['add_insurance'] = array('', 'G_Add_Insurance');
    $g_subActions['insert_insurance'] = array('', 'G_Insert_Insurance');
    $g_subActions['edit_insurance'] = array('', 'G_Edit_Insurance');
    $g_subActions['update_insurance'] = array('', 'G_Update_Insurance');
    $g_subActions['delete_insurance'] = array('', 'G_Delete_Insurance');
    $g_subActions['add_quartermile'] = array('', 'G_Add_Quartermile');
    $g_subActions['insert_quartermile'] = array('', 'G_Insert_Quartermile');
    $g_subActions['edit_quartermile'] = array('', 'G_Edit_Quartermile');
    $g_subActions['update_quartermile'] = array('', 'G_Update_Quartermile');
    $g_subActions['delete_quartermile'] = array('', 'G_Delete_Quartermile');
    $g_subActions['insert_quartermile_images'] = array('', 'G_Insert_Quartermile_Images');
    $g_subActions['remove_quartermile_image'] = array('', 'G_Remove_Quartermile_Image');
    $g_subActions['insert_quartermile_video'] = array('', 'G_Insert_Quartermile_Video');
    $g_subActions['view_quartermile'] = array('', 'G_View_Quartermile');
    $g_subActions['add_dynorun'] = array('', 'G_Add_Dynorun');
    $g_subActions['insert_dynorun'] = array('', 'G_Insert_Dynorun');
    $g_subActions['edit_dynorun'] = array('', 'G_Edit_Dynorun');
    $g_subActions['update_dynorun'] = array('', 'G_Update_Dynorun');
    $g_subActions['delete_dynorun'] = array('', 'G_Delete_Dynorun');
    $g_subActions['insert_dynorun_images'] = array('', 'G_Insert_Dynorun_Images');
    $g_subActions['remove_dynorun_image'] = array('', 'G_Remove_Dynorun_Image');
    $g_subActions['insert_dynorun_video'] = array('', 'G_Insert_Dynorun_Video');
    $g_subActions['view_dynorun'] = array('', 'G_View_Dynorun');
    $g_subActions['add_laptime'] = array('', 'G_Add_Laptime');
    $g_subActions['insert_laptime'] = array('', 'G_Insert_Laptime');
    $g_subActions['edit_laptime'] = array('', 'G_Edit_Laptime');
    $g_subActions['update_laptime'] = array('', 'G_Update_Laptime');
    $g_subActions['delete_laptime'] = array('', 'G_Delete_Laptime');
    $g_subActions['insert_laptime_images'] = array('', 'G_Insert_Laptime_Images');
    $g_subActions['remove_laptime_image'] = array('', 'G_Remove_Laptime_Image');
    $g_subActions['insert_laptime_video'] = array('', 'G_Insert_Laptime_Video');
    $g_subActions['remove_video'] = array('', 'G_Remove_Video');
    $g_subActions['view_laptime'] = array('', 'G_View_Laptime');
    $g_subActions['view_track'] = array('', 'G_View_Track');
    $g_subActions['add_service'] = array('', 'G_Add_Service');
    $g_subActions['insert_service'] = array('', 'G_Insert_Service');
    $g_subActions['edit_service'] = array('', 'G_Edit_Service');
    $g_subActions['update_service'] = array('', 'G_Update_Service');
    $g_subActions['delete_service'] = array('', 'G_Delete_Service');
    $g_subActions['insert_blog'] = array('', 'G_Insert_Blog');
    $g_subActions['edit_blog'] = array('', 'G_Edit_Blog');
    $g_subActions['update_blog'] = array('', 'G_Update_Blog');
    $g_subActions['delete_blog'] = array('', 'G_Delete_Blog');
    $g_subActions['insert_garage_comment'] = array('', 'G_Insert_Garage_Comment');
    $g_subActions['edit_garage_comment'] = array('', 'G_Edit_Garage_Comment');
    $g_subActions['update_garage_comment'] = array('', 'G_Update_Garage_Comment');
    $g_subActions['delete_garage_comment'] = array('', 'G_Delete_Garage_Comment');
    $g_subActions['insert_comment'] = array('', 'G_Insert_Comment');
    $g_subActions['edit_comment'] = array('', 'G_Edit_Comment');
    $g_subActions['update_comment'] = array('', 'G_Update_Comment');
    $g_subActions['delete_comment'] = array('', 'G_Delete_Comment');
    $g_subActions['insurance_review'] = array('', 'G_Insurance_Review');
    $g_subActions['shop_review'] = array('', 'G_Shop_Review');
    $g_subActions['garage_review'] = array('', 'G_Garage_Review');
    $g_subActions['mfg_review'] = array('', 'G_Manufacturer_Review');
    $g_subActions['dc_review'] = array('', 'G_Dynocenter_Review');
    $g_subActions['insert_rating'] = array('', 'G_Insert_Rating');
    $g_subActions['remove_rating'] = array('', 'G_Remove_Rating');
    $g_subActions['search_results'] = array('', 'G_Search_Results');
    $g_subActions['submit_make'] = array('', 'G_Submit_Make');
    $g_subActions['submit_make_insert'] = array('', 'G_Submit_Make_Insert');
    $g_subActions['submit_model'] = array('', 'G_Submit_Model');
    $g_subActions['submit_model_insert'] = array('', 'G_Submit_Model_Insert');
    $g_subActions['submit_track'] = array('', 'G_Submit_Track');
    $g_subActions['submit_track_insert'] = array('', 'G_Submit_Track_Insert');
    $g_subActions['submit_business'] = array('', 'G_Submit_Business');
    $g_subActions['submit_business_insert'] = array('', 'G_Submit_Business_Insert');
    $g_subActions['submit_product'] = array('', 'G_Submit_Product');
    $g_subActions['submit_product_insert'] = array('', 'G_Submit_Product_Insert');
    $g_subActions['copyright'] = array('', 'G_Copyright');
    $g_subActions['gcard'] = array('', 'G_Garage_Card');
    $g_subActions['get_gcard'] = array('', 'G_Get_Garage_Card');
    $g_subActions['video'] = array('', 'G_Video');
    $g_subActions['supported_video'] = array('', 'G_Supported_Video');
    $g_subActions['modifications'] = array('', 'G_Modifications');
    $g_subActions['mostmodified'] = array('', 'G_Most_Modified');
    $g_subActions['mostviewed'] = array('', 'G_Most_Viewed');
    $g_subActions['latestservice'] = array('', 'G_Latest_Service');
    $g_subActions['toprated'] = array('', 'G_Top_Rated');
    $g_subActions['mostspent'] = array('', 'G_Most_Spent');
    $g_subActions['latestblog'] = array('', 'G_Latest_Blog');
    $g_subActions['latestvideo'] = array('', 'G_Latest_Video');

    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage',
        'name' => &$txt['smfg_garage']
    );

    // Jump to the sub action.
    if (isset($g_subActions[$context['garage_sa']])) {
        $g_subActions[$context['garage_sa']][1]();
    } else {
        $g_subActions['main'][1]();
    }

    // SMF 2.1: Load conditional CSS/JS after sub-action sets context flags
    if (defined('SMF_VERSION') && version_compare(SMF_VERSION, '2.1', '>='))
    {
        if (!empty($smfgSettings['enable_lightbox']) && !empty($context['lightbox']))
        {
            loadCSSFile('shadowbox.css', array('default_theme' => true));
            loadJavascriptFile('shadowbox.js', array('default_theme' => true, 'subdir' => 'css'));
            addInlineJavascript('Shadowbox.init();', true);
        }
        if (!empty($context['form_validation']))
            loadJavascriptFile('gen_validatorv2.js', array('default_theme' => true));
        if (!empty($context['dynamicoptionlist']))
            loadJavascriptFile('dynamicoptionlist.js', array('default_theme' => true));
        if (!empty($context['smfg_ajax']))
        {
            loadCSSFile('jquery-ui-1.8.13.custom.css', array('default_theme' => true));
            loadJavascriptFile('jquery-ui-1.8.13.custom.min.js', array('default_theme' => true));
            loadJavascriptFile('smfg_ajax.js', array('default_theme' => true));
        }
    }

}

// Main Garage
function G_Main()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $smfgSettings, $modSettings, $context, $func, $smcFunc;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['date_format'] = $smfgSettings['dateformat'];

    // Check Permissions
    isAllowedTo('view_garage');

    // Get total number of vehicles
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_vehicles',
        array(// no values
        )
    );
    $context['total_vehicles'] = $smcFunc['db_num_rows']($request);
    $smcFunc['db_free_result']($request);
    $context['total_vehicles'] = number_format($context['total_vehicles'], 0, '.', ',');

    // Get total number of mods
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_modifications',
        array(// no values
        )
    );
    $context['total_mods'] = $smcFunc['db_num_rows']($request);
    $smcFunc['db_free_result']($request);
    $context['total_mods'] = number_format($context['total_mods'], 0, '.', ',');

    // Get total number of comments
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_guestbooks',
        array(// no values
        )
    );
    $context['total_comments'] = $smcFunc['db_num_rows']($request);
    $smcFunc['db_free_result']($request);
    $context['total_comments'] = number_format($context['total_comments'], 0, '.', ',');

    // Get total number of views
    $context['total_views'] = 0;
    $request = $smcFunc['db_query']('', '
        SELECT views
        FROM {db_prefix}garage_vehicles',
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context[$count]['views']) = $row;
        $context['total_views'] += $context[$count]['views'];
        $count++;
    }
    $smcFunc['db_free_result']($request);
    $context['total_views'] = number_format($context['total_views'], 0, '.', ',');

    if ($smfgSettings['enable_featured_vehicle'] != 0 && $context['total_vehicles'] != 0) {

        // Get the featured vehicle ID
        $context['featured_vehicle']['id'] = getFeaturedVehicle();

        if (isset($context['featured_vehicle']['id']) && !empty($context['featured_vehicle']['id'])) {
            $request = $smcFunc['db_query']('', '
                SELECT u.real_name, CONCAT_WS(" ", v.made_year, mk.make, md.model), v.user_id
                FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
                WHERE v.id = {int:fvid}
                    AND v.make_id = mk.id
                    AND v.model_id = md.id
                    AND v.user_id = u.id_member',
                array(
                    'fvid' => $context['featured_vehicle']['id'],
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
                WHERE vehicle_id = {int:fvid}
                    AND hilite = 1',
                array(
                    'fvid' => $context['featured_vehicle']['id'],
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
                    WHERE attach_id = {int:fv_image_id}',
                    array(
                        'fv_image_id' => $context['featured_vehicle']['image_id'],
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
                if (empty($context['featured_vehicle'][$count]['attach_desc'])) {
                    $context['featured_vehicle'][$count]['attach_desc'] = $txt['smfg_no_desc'];
                }

                // Check to see if the image is remote or not and build appropriate links
                if ($context['featured_vehicle']['is_remote'] == 1) {
                    $context['featured_vehicle']['attach_location'] = urldecode($context['featured_vehicle']['attach_file']);
                } else {
                    $context['featured_vehicle']['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['featured_vehicle']['attach_location'];
                }

                // If there is an image attached, link to it
                if (isset($context['featured_vehicle']['attach_location'])) {
                    $context['featured_vehicle']['image'] = "<a href=\"" . $context['featured_vehicle']['attach_location'] . "\" rel=\"shadowbox\" title=\"" . $context['featured_vehicle']['vehicle'] . " :: Owner: " . $context['featured_vehicle']['owner'] . "\" class=\"smfg_imageTitle\"><img src=\"" . $boardurl . "/" . $smfgSettings['upload_directory'] . 'cache/' . $context['featured_vehicle']['attach_thumb_location'] . "\" width=\"" . $context['featured_vehicle']['attach_thumb_width'] . "\" height=\"" . $context['featured_vehicle']['attach_thumb_height'] . "\" /></a><br />";
                }

                //No image for the featured vehicle? Check if featured image required, if so display "no image" place holder image.
            } else {
                if (!isset($context['featured_vehicle']['image_id']) && $smfgSettings['featured_vehicle_image_required']) {
                    list($fv_width, $fv_height) = getimagesize($settings['actual_images_url'] . "/garage_no_vehicle_thumb.png");
                    $context['featured_vehicle']['image'] = "<img src=\"" . $settings['actual_images_url'] . "/garage_no_vehicle_thumb.png\" width=\"" . $fv_width . "\" height=\"" . $fv_height . "\" /><br />";
                }
            }
        }
    }

    ###################################################
    ##       Begin building blocks if enabled        ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_newest_vehicle']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five newest vehicles
        $request = $smcFunc['db_query']('', '
            SELECT v.id, v.user_id, v.date_created, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
            WHERE v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND v.user_id = u.id_member
            ORDER BY v.date_created DESC
            LIMIT 0, {int:newest_vehicle_limit}',
            array(
                'newest_vehicle_limit' => $smfgSettings['newest_vehicle_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['newest_vehicles'][$count]['id'],
                $context['newest_vehicles'][$count]['user_id'],
                $context['newest_vehicles'][$count]['date_created'],
                $context['newest_vehicles'][$count]['vehicle'],
                $context['newest_vehicles'][$count]['memberName']) = $row;
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['newest_vehicles'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['newest_vehicles'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'newestVehicles\', \'newestVehiclesUpshrink\'); return false;"><img id="newestVehiclesUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=browse">' . $txt['smfg_newest_vehicles'] . '</a>';

        $context['blocks']['newest_vehicles'] .= '
                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['newest_vehicles'] .= '
                </td>
            </tr>';

        $context['blocks']['newest_vehicles'] .= '
            <tr>
                <td>';

        $context['blocks']['newest_vehicles'] .= '
                <div id="newestVehicles">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['newest_vehicles'][$count]['id'])) {
            $context['blocks']['newest_vehicles'] .= '
                                <tr>';
            $context['blocks']['newest_vehicles'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_created'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['newest_vehicles'][$count]['id'])) {
                $context['blocks']['newest_vehicles'] .= '
                                <tr class="tableRow">
                                    <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['newest_vehicles'][$count]['id'] . '">' . garage_title_clean($context['newest_vehicles'][$count]['vehicle']) . '</a></td>
                                    <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['newest_vehicles'][$count]['user_id'] . '">' . $context['newest_vehicles'][$count]['memberName'] . '</a></td>
                                    <td align="center" valign="middle" nowrap="nowrap">' . date($context['date_format'],
                        $context['newest_vehicles'][$count]['date_created']) . '</td>
                                </tr>';
                $count++;
            }
        } else {
            $context['blocks']['newest_vehicles'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_vehicles'] . '</td>
                                </tr>';
        }
        $context['blocks']['newest_vehicles'] .= '
                            </table>';


        $context['blocks']['newest_vehicles'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['newest_vehicles'] .= '    
                </td>
            </tr>
        </table>';
    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_newest_modification']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five newest modifications
        $request = $smcFunc['db_query']('', '
            SELECT m.id, p.title, m.vehicle_id, m.user_id, m.date_created, u.real_name
            FROM {db_prefix}garage_modifications AS m, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_products AS p, {db_prefix}garage_business AS b, {db_prefix}members AS u
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
                AND m.user_id = u.id_member
                ORDER BY m.date_created DESC
                LIMIT 0, {int:newest_modification_limit}',
            array(
                'newest_modification_limit' => $smfgSettings['newest_modification_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['newest_mods'][$count]['id'],
                $context['newest_mods'][$count]['modification'],
                $context['newest_mods'][$count]['vid'],
                $context['newest_mods'][$count]['user_id'],
                $context['newest_mods'][$count]['date_created'],
                $context['newest_mods'][$count]['memberName']) = $row;
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['newest_mods'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['newest_mods'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'newestMods\', \'newestModsUpshrink\'); return false;"><img id="newestModsUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=modifications">' . $txt['smfg_newest_mods'] . '</a>';

        $context['blocks']['newest_mods'] .= '
                                            
                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['newest_mods'] .= '
                </td>
            </tr>';

        $context['blocks']['newest_mods'] .= '
            <tr>
                <td>';

        $context['blocks']['newest_mods'] .= '
                <div id="newestMods">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['newest_mods'][$count]['id'])) {
            $context['blocks']['newest_mods'] .= '
                                <tr>';
            $context['blocks']['newest_mods'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_modification'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_created'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['newest_mods'][$count]['id'])) {
                $context['blocks']['newest_mods'] .= '
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['newest_mods'][$count]['vid'] . ';MID=' . $context['newest_mods'][$count]['id'] . '">' . garage_title_clean($context['newest_mods'][$count]['modification']) . '</a></td>
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['newest_mods'][$count]['user_id'] . '">' . $context['newest_mods'][$count]['memberName'] . '</a></td>
                                        <td align="center" valign="middle" nowrap="nowrap">' . date($context['date_format'],
                        $context['newest_mods'][$count]['date_created']) . '</td>
                                    </tr>';
                $count++;
            }
        } else {
            $context['blocks']['newest_mods'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_modifications_in_garage'] . '</td>
                                </tr>';
        }
        $context['blocks']['newest_mods'] .= '
                            </table>';


        $context['blocks']['newest_mods'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['newest_mods'] .= '    
                </td>
            </tr>
        </table>';
    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_most_modified']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five most modified vehicles
        $request = $smcFunc['db_query']('', '
            SELECT v.id, COUNT( m.id ) AS total_mods, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
            FROM {db_prefix}garage_vehicles AS v 
            LEFT OUTER JOIN {db_prefix}garage_modifications AS m ON v.id = m.vehicle_id, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_products AS p, {db_prefix}garage_business AS b, {db_prefix}members AS u
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
                AND v.user_id = u.id_member
                GROUP BY v.id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
                ORDER BY total_mods DESC
                LIMIT 0, {int:most_modified_limit}',
            array(
                'most_modified_limit' => $smfgSettings['most_modified_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['most_mods'][$count]['id'],
                $context['most_mods'][$count]['total_mods'],
                $context['most_mods'][$count]['user_id'],
                $context['most_mods'][$count]['vehicle'],
                $context['most_mods'][$count]['memberName']) = $row;
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['most_mods'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['most_mods'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'mostMods\', \'mostModsUpshrink\'); return false;"><img id="mostModsUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=mostmodified">' . $txt['smfg_most_modified'] . '</a>';

        $context['blocks']['most_mods'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['most_mods'] .= '
                </td>
            </tr>';

        $context['blocks']['most_mods'] .= '
            <tr>
                <td>';

        $context['blocks']['most_mods'] .= '
                <div id="mostMods">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['most_mods'][$count]['id'])) {
            $context['blocks']['most_mods'] .= '
                                <tr>';
            $context['blocks']['most_mods'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_mods'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['most_mods'][$count]['id'])) {
                $context['blocks']['most_mods'] .= '
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['most_mods'][$count]['id'] . '#modifications">' . garage_title_clean($context['most_mods'][$count]['vehicle']) . '</a></td>
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['most_mods'][$count]['user_id'] . '">' . $context['most_mods'][$count]['memberName'] . '</a></td>
                                        <td align="center" valign="middle" nowrap="nowrap">' . $context['most_mods'][$count]['total_mods'] . '</td>
                                    </tr>';
                $count++;
            }
        } else {
            $context['blocks']['most_mods'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_modifications_in_garage'] . '</td>
                                </tr>';
        }
        $context['blocks']['most_mods'] .= '
                            </table>';


        $context['blocks']['most_mods'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['most_mods'] .= '    
                </td>
            </tr>
        </table>';

    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_most_viewed']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five most viewed vehicles
        $request = $smcFunc['db_query']('', '
            SELECT v.id, v.user_id, v.views, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
            WHERE v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND v.user_id = u.id_member
                ORDER BY v.views DESC
                LIMIT 0, {int:most_viewed_limit}',
            array(
                'most_viewed_limit' => $smfgSettings['most_viewed_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['most_views'][$count]['id'],
                $context['most_views'][$count]['user_id'],
                $context['most_views'][$count]['views'],
                $context['most_views'][$count]['vehicle'],
                $context['most_views'][$count]['memberName']) = $row;
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['most_views'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['most_views'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'mostViews\', \'mostViewsUpshrink\'); return false;"><img id="mostViewsUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=mostviewed">' . $txt['smfg_most_viewed'] . '</a>';

        $context['blocks']['most_views'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['most_views'] .= '
                </td>
            </tr>';

        $context['blocks']['most_views'] .= '
            <tr>
                <td>';

        $context['blocks']['most_views'] .= '
                <div id="mostViews">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['most_views'][$count]['id'])) {
            $context['blocks']['most_views'] .= '
                                <tr>';
            $context['blocks']['most_views'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_views'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['most_views'][$count]['id'])) {
                $context['blocks']['most_views'] .= '
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['most_views'][$count]['id'] . '">' . garage_title_clean($context['most_views'][$count]['vehicle']) . '</a></td>
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['most_views'][$count]['user_id'] . '">' . $context['most_views'][$count]['memberName'] . '</a></td>
                                        <td align="center" valign="middle" nowrap="nowrap">' . $context['most_views'][$count]['views'] . '</td>
                                    </tr>';
                $count++;
            }
        } else {
            $context['blocks']['most_views'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_vehicles'] . '</td>
                                </tr>';
        }
        $context['blocks']['most_views'] .= '
                            </table>';


        $context['blocks']['most_views'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['most_views'] .= '    
                </td>
            </tr>
        </table>';

    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_top_quartermile']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five top quartermiles
        $request = $smcFunc['db_query']('', '
            SELECT q.id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name, q.vehicle_id, q.quart, q.quartmph
            FROM {db_prefix}garage_quartermiles AS q, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
            WHERE q.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND q.pending != "1"
                AND v.user_id = u.id_member
                ORDER BY q.quart ASC
                LIMIT 0, {int:top_quartermile_limit}',
            array(
                'top_quartermile_limit' => $smfgSettings['top_quartermile_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['top_qm'][$count]['id'],
                $context['top_qm'][$count]['user_id'],
                $context['top_qm'][$count]['vehicle'],
                $context['top_qm'][$count]['memberName'],
                $context['top_qm'][$count]['vid'],
                $context['top_qm'][$count]['quart'],
                $context['top_qm'][$count]['quartmph']) = $row;
            $context['top_qm'][$count]['qm_run'] = $context['top_qm'][$count]['quart'] . ' @ ' . $context['top_qm'][$count]['quartmph'] . ' MPH';
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['top_qm'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['top_qm'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'topQm\', \'topQmUpshrink\'); return false;"><img id="topQmUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=quartermiles">' . $txt['smfg_top_qmile'] . '</a>';

        $context['blocks']['top_qm'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['top_qm'] .= '
                </td>
            </tr>';

        $context['blocks']['top_qm'] .= '
            <tr>
                <td>';

        $context['blocks']['top_qm'] .= '
                <div id="topQm">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['top_qm'][$count]['id'])) {
            $context['blocks']['top_qm'] .= '
                                <tr>';
            $context['blocks']['top_qm'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_quartermile_fraction'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['top_qm'][$count]['id'])) {
                $context['blocks']['top_qm'] .= '
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['top_qm'][$count]['vid'] . '#quartermiles">' . garage_title_clean($context['top_qm'][$count]['vehicle']) . '</a></td>
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['top_qm'][$count]['user_id'] . '">' . $context['top_qm'][$count]['memberName'] . '</a></td>
                                        <td align="center" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_quartermile;VID=' . $context['top_qm'][$count]['vid'] . ';QID=' . $context['top_qm'][$count]['id'] . '">' . garage_title_clean($context['top_qm'][$count]['qm_run']) . '</a></td>
                                    </tr>';
                $count++;
            }
        } else {
            $context['blocks']['top_qm'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_quartermiles_in_garage'] . '</td>
                                </tr>';
        }
        $context['blocks']['top_qm'] .= '
                            </table>';


        $context['blocks']['top_qm'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['top_qm'] .= '    
                </td>
            </tr>
        </table>';

    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_top_rating']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        if ($smfgSettings['rating_system'] == 0) {
            $ratingfunc = "SUM";
        } else {
            if ($smfgSettings['rating_system'] == 1) {
                $ratingfunc = "AVG";
            }
        }

        // Get the five top rated vehicles
        $request = $smcFunc['db_query']('', '
            SELECT r.vehicle_id, ' . $ratingfunc . '( r.rating ) AS rating, COUNT( r.id ) * 10 AS poss_rating, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
            FROM {db_prefix}garage_ratings AS r, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
            WHERE r.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND v.user_id = u.id_member
                GROUP BY r.vehicle_id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
                ORDER BY rating DESC
                LIMIT 0, {int:top_rating_limit}',
            array(
                'top_rating_limit' => $smfgSettings['top_rating_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['top_rated'][$count]['id'],
                $context['top_rated'][$count]['rating'],
                $context['top_rated'][$count]['poss_rating'],
                $context['top_rated'][$count]['user_id'],
                $context['top_rated'][$count]['vehicle'],
                $context['top_rated'][$count]['memberName']) = $row;
            if ($context['top_rated'][$count]['rating'] > 0) {
                $context['top_rated'][$count]['rating'] = number_format($context['top_rated'][$count]['rating'], 2, '.',
                    ',');
            } else {
                $context['top_rated'][$count]['rating'] = 0;
            }
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['top_rated'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['top_rated'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'topRated\', \'topRatedUpshrink\'); return false;"><img id="topRatedUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=toprated">' . $txt['smfg_top_rated'] . '</a>';

        $context['blocks']['top_rated'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['top_rated'] .= '
                </td>
            </tr>';

        $context['blocks']['top_rated'] .= '
            <tr>
                <td>';

        $context['blocks']['top_rated'] .= '
                <div id="topRated">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['top_rated'][$count]['id'])) {
            $context['blocks']['top_rated'] .= '
                                <tr>';
            $context['blocks']['top_rated'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_rating'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['top_rated'][$count]['id'])) {
                $context['blocks']['top_rated'] .= '
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['top_rated'][$count]['id'] . '">' . garage_title_clean($context['top_rated'][$count]['vehicle']) . '</a></td>
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['top_rated'][$count]['user_id'] . '">' . $context['top_rated'][$count]['memberName'] . '</a></td>';
                if ($context['top_rated'][$count]['poss_rating']) {
                    if ($smfgSettings['rating_system'] == 0) {
                        $context['blocks']['top_rated'] .= '
                                        <td align="center" valign="middle" nowrap="nowrap">' . $context['top_rated'][$count]['rating'] . '/' . $context['top_rated'][$count]['poss_rating'] . '</td>';
                    } else {
                        if ($smfgSettings['rating_system'] == 1) {
                            $context['blocks']['top_rated'] .= '
                                        <td align="center" valign="middle" nowrap="nowrap">' . $context['top_rated'][$count]['rating'] . '/10</td>';
                        }
                    }
                } else {
                    $context['blocks']['top_rated'] .= '
                                        <td align="center" valign="middle" nowrap="nowrap">' . $txt['smfg_vehicle_not_rated'] . '</td>';
                }
                $context['blocks']['top_rated'] .= '
                                    </tr>';
                $count++;
            }
        } else {
            $context['blocks']['top_rated'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_vehicles_rated'] . '</td>
                                </tr>';
        }
        $context['blocks']['top_rated'] .= '
                            </table>';


        $context['blocks']['top_rated'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['top_rated'] .= '    
                </td>
            </tr>
        </table>';

    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_updated_vehicle']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five latest updated vehicles
        $request = $smcFunc['db_query']('', '
            SELECT v.id, v.user_id, v.date_updated, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
            WHERE v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND v.user_id = u.id_member
            ORDER BY v.date_updated DESC
            LIMIT 0, {int:updated_vehicle_limit}',
            array(
                'updated_vehicle_limit' => $smfgSettings['updated_vehicle_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['last_updated_veh'][$count]['id'],
                $context['last_updated_veh'][$count]['user_id'],
                $context['last_updated_veh'][$count]['date_updated'],
                $context['last_updated_veh'][$count]['vehicle'],
                $context['last_updated_veh'][$count]['memberName']) = $row;
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['last_updated_veh'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['last_updated_veh'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'updatedVehicles\', \'updatedVehiclesUpshrink\'); return false;"><img id="updatedVehiclesUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=browse;sort=updated">' . $txt['smfg_last_updated_vehicles'] . '</a>';

        $context['blocks']['last_updated_veh'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['last_updated_veh'] .= '
                </td>
            </tr>';

        $context['blocks']['last_updated_veh'] .= '
            <tr>
                <td>';

        $context['blocks']['last_updated_veh'] .= '
                <div id="updatedVehicles">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['last_updated_veh'][$count]['id'])) {
            $context['blocks']['last_updated_veh'] .= '
                                <tr>';
            $context['blocks']['last_updated_veh'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_updated'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['last_updated_veh'][$count]['id'])) {
                $context['blocks']['last_updated_veh'] .= '
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_updated_veh'][$count]['id'] . '">' . garage_title_clean($context['last_updated_veh'][$count]['vehicle']) . '</a></td>
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['last_updated_veh'][$count]['user_id'] . '">' . $context['last_updated_veh'][$count]['memberName'] . '</a></td>
                                        <td align="center" valign="middle" nowrap="nowrap">' . date($context['date_format'],
                        $context['last_updated_veh'][$count]['date_updated']) . '</td>
                                    </tr>';
                $count++;
            }
        } else {
            $context['blocks']['last_updated_veh'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_vehicles'] . '</td>
                                </tr>';
        }
        $context['blocks']['last_updated_veh'] .= '
                            </table>';


        $context['blocks']['last_updated_veh'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['last_updated_veh'] .= '    
                </td>
            </tr>
        </table>';

    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_updated_modification']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five latest updated mods
        $request = $smcFunc['db_query']('', '
            SELECT m.id, p.title, m.vehicle_id, m.user_id, m.date_updated, u.real_name
            FROM {db_prefix}garage_modifications AS m, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_products AS p, {db_prefix}garage_business AS b, {db_prefix}members AS u
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
                AND m.user_id = u.id_member
            ORDER BY m.date_updated DESC
            LIMIT 0, {int:updated_modification_limit}',
            array(
                'updated_modification_limit' => $smfgSettings['updated_modification_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['last_updated_mods'][$count]['id'],
                $context['last_updated_mods'][$count]['modification'],
                $context['last_updated_mods'][$count]['vid'],
                $context['last_updated_mods'][$count]['user_id'],
                $context['last_updated_mods'][$count]['date_updated'],
                $context['last_updated_mods'][$count]['memberName']) = $row;
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['last_updated_mods'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['last_updated_mods'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'updatedMods\', \'updatedModsUpshrink\'); return false;"><img id="updatedModsUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=modifications;sort=updated">' . $txt['smfg_last_updated_mods'] . '</a>';

        $context['blocks']['last_updated_mods'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['last_updated_mods'] .= '
                </td>
            </tr>';

        $context['blocks']['last_updated_mods'] .= '
            <tr>
                <td>';

        $context['blocks']['last_updated_mods'] .= '
                <div id="updatedMods">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['last_updated_mods'][$count]['id'])) {
            $context['blocks']['last_updated_mods'] .= '
                                <tr>';
            $context['blocks']['last_updated_mods'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_modification'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_updated'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['last_updated_mods'][$count]['id'])) {
                $context['blocks']['last_updated_mods'] .= '
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['last_updated_mods'][$count]['vid'] . ';MID=' . $context['last_updated_mods'][$count]['id'] . '">' . garage_title_clean($context['last_updated_mods'][$count]['modification']) . '</a></td>
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['last_updated_mods'][$count]['user_id'] . '">' . $context['last_updated_mods'][$count]['memberName'] . '</a></td>
                                        <td align="center" valign="middle" nowrap="nowrap">' . date($context['date_format'],
                        $context['last_updated_mods'][$count]['date_updated']) . '</td>
                                    </tr>';
                $count++;
            }
        } else {
            $context['blocks']['last_updated_mods'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_modifications_in_garage'] . '</td>
                                </tr>';
        }
        $context['blocks']['last_updated_mods'] .= '
                            </table>';


        $context['blocks']['last_updated_mods'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['last_updated_mods'] .= '    
                </td>
            </tr>
        </table>';
    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_most_spent']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // *************************************************************
        // WARNING: The query check is being disabled to allow for the following subselect.
        // It is imperative this is turned back on for security reasons.
        // *************************************************************
        $modSettings['disableQueryCheck'] = 1;
        // *************************************************************

        // Get the five vehicles with the most money spent
        $request = $smcFunc['db_query']('', '
            SELECT v.id, IFNULL(m.total_mods,0) + IFNULL(s.total_service,0) AS total_spent, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name, c.title AS currency
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
                    GROUP BY vehicle_id) AS s ON v.id = s.vehicle_id, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_currency AS c, {db_prefix}members AS u
                WHERE v.make_id = mk.id
                    AND v.model_id = md.id
                    AND mk.pending != "1"
                    AND md.pending != "1"
                    AND v.pending != "1"
                    AND v.user_id = u.id_member
                    AND v.currency = c.id
                    GROUP BY v.id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name, c.title, IFNULL(m.total_mods,0) + IFNULL(s.total_service,0)
                    ORDER BY total_spent DESC
                    LIMIT 0, {int:most_spent_limit}',
            array(
                'most_spent_limit' => $smfgSettings['most_spent_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['most_spent'][$count]['id'],
                $context['most_spent'][$count]['total_spent'],
                $context['most_spent'][$count]['user_id'],
                $context['most_spent'][$count]['vehicle'],
                $context['most_spent'][$count]['memberName'],
                $context['most_spent'][$count]['currency']) = $row;
            $context['most_spent'][$count]['total_spent'] = number_format($context['most_spent'][$count]['total_spent'],
                2, '.', ',');
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // *************************************************************
        // WARNING: The query check is being enabled, this MUST BE DONE!
        // *************************************************************
        $modSettings['disableQueryCheck'] = 0;
        // *************************************************************

        // Build the block
        $context['blocks']['most_spent'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['most_spent'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'mostSpent\', \'mostSpentUpshrink\'); return false;"><img id="mostSpentUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=mostspent">' . $txt['smfg_most_spent'] . '</a>';

        $context['blocks']['most_spent'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['most_spent'] .= '
                </td>
            </tr>';

        $context['blocks']['most_spent'] .= '
            <tr>
                <td>';

        $context['blocks']['most_spent'] .= '
                <div id="mostSpent">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['most_spent'][$count]['id'])) {
            $context['blocks']['most_spent'] .= '
                                <tr>';
            $context['blocks']['most_spent'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_total_spent'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['most_spent'][$count]['id'])) {
                $context['blocks']['most_spent'] .= '
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['most_spent'][$count]['id'] . '">' . garage_title_clean($context['most_spent'][$count]['vehicle']) . '</a></td>
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['most_spent'][$count]['user_id'] . '">' . $context['most_spent'][$count]['memberName'] . '</a></td>
                                        <td align="center" valign="middle" nowrap="nowrap">' . $context['most_spent'][$count]['total_spent'] . ' ' . $context['most_spent'][$count]['currency'] . '</td>
                                    </tr>';
                $count++;
            }
        } else {
            $context['blocks']['most_spent'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_vehicles'] . '</td>
                                </tr>';
        }
        $context['blocks']['most_spent'] .= '
                            </table>';


        $context['blocks']['most_spent'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['most_spent'] .= '    
                </td>
            </tr>
        </table>';
    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_last_commented']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five latest vehicles with comments
        $request = $smcFunc['db_query']('', '
            SELECT gb.id, gb.vehicle_id, gb.post_date, gb.author_id, u2.id_member, u2.real_name, CONCAT(CONCAT_WS(" ", v.made_year, mk.make, md.model)), u.real_name 
            FROM {db_prefix}garage_guestbooks AS gb, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u, {db_prefix}members AS u2
            WHERE gb.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND gb.pending != "1"
                AND gb.author_id = u.id_member
                AND v.user_id = u2.id_member
            ORDER BY post_date DESC
            LIMIT 0, {int:last_commented_limit}',
            array(
                'last_commented_limit' => $smfgSettings['last_commented_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['last_comments'][$count]['id'],
                $context['last_comments'][$count]['vid'],
                $context['last_comments'][$count]['post_date'],
                $context['last_comments'][$count]['author_id'],
                $context['last_comments'][$count]['owner_id'],
                $context['last_comments'][$count]['ownerName'],
                $context['last_comments'][$count]['vehicle'],
                $context['last_comments'][$count]['memberName']) = $row;
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['last_comments'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['last_comments'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'lastComments\', \'lastCommentsUpshrink\'); return false;"><img id="lastCommentsUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            ' . $txt['smfg_latest_comments'];

        $context['blocks']['last_comments'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['last_comments'] .= '
                </td>
            </tr>';

        $context['blocks']['last_comments'] .= '
            <tr>
                <td>';

        $context['blocks']['last_comments'] .= '
                <div id="lastComments">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['last_comments'][$count]['id'])) {
            $context['blocks']['last_comments'] .= '
                                <tr>';
            $context['blocks']['last_comments'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_author'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_posted_date'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['last_comments'][$count]['id'])) {
                $context['blocks']['last_comments'] .= '
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['last_comments'][$count]['owner_id'] . '">' . $context['last_comments'][$count]['ownerName'] . '</a>\'s&nbsp;<a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_comments'][$count]['vid'] . '#guestbook">' . garage_title_clean($context['last_comments'][$count]['vehicle']) . '</a></td>
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=profile;u=' . $context['last_comments'][$count]['author_id'] . '">' . $context['last_comments'][$count]['memberName'] . '</a></td>
                                        <td align="center" valign="middle" nowrap="nowrap">' . date($context['date_format'],
                        $context['last_comments'][$count]['post_date']) . '</td>
                                    </tr>';
                $count++;
            }
        } else {
            $context['blocks']['last_comments'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_comments_in_garage'] . '</td>
                                </tr>';
        }
        $context['blocks']['last_comments'] .= '
                            </table>';


        $context['blocks']['last_comments'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['last_comments'] .= '    
                </td>
            </tr>
        </table>';

    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_top_dynorun']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five top dyno runs
        $request = $smcFunc['db_query']('', '
            SELECT d.id, d.vehicle_id, d.bhp, d.bhp_unit, d.torque, d.torque_unit, d.nitrous, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
            FROM {db_prefix}garage_dynoruns AS d, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_business AS b, {db_prefix}members AS u
            WHERE d.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND d.dynocenter_id = b.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND d.pending != "1"
                AND b.pending != "1"
                AND v.user_id = u.id_member
            ORDER BY bhp DESC
            LIMIT 0, {int:top_dynorun_limit}',
            array(
                'top_dynorun_limit' => $smfgSettings['top_dynorun_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['top_dr'][$count]['id'],
                $context['top_dr'][$count]['vid'],
                $context['top_dr'][$count]['bhp'],
                $context['top_dr'][$count]['bhp_unit'],
                $context['top_dr'][$count]['torque'],
                $context['top_dr'][$count]['torque_unit'],
                $context['top_dr'][$count]['nitrous'],
                $context['top_dr'][$count]['user_id'],
                $context['top_dr'][$count]['vehicle'],
                $context['top_dr'][$count]['memberName']) = $row;
            $context['top_dr'][$count]['dynorun'] = $context['top_dr'][$count]['bhp'] . ' ' . $context['top_dr'][$count]['bhp_unit'] . ' / ' . $context['top_dr'][$count]['torque'] . ' ' . $context['top_dr'][$count]['torque_unit'] . ' / ' . $context['top_dr'][$count]['nitrous'];
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['top_dr'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['top_dr'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'topDyno\', \'topDynoUpshrink\'); return false;"><img id="topDynoUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=dynoruns">' . $txt['smfg_top_dynorun'] . '</a>';

        $context['blocks']['top_dr'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['top_dr'] .= '
                </td>
            </tr>';

        $context['blocks']['top_dr'] .= '
            <tr>
                <td>';

        $context['blocks']['top_dr'] .= '
                <div id="topDyno">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['top_dr'][$count]['id'])) {
            $context['blocks']['top_dr'] .= '
                                <tr>';
            $context['blocks']['top_dr'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_dyno_specs'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['top_dr'][$count]['id'])) {
                $context['blocks']['top_dr'] .= '
                                <tr class="tableRow">
                                    <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['top_dr'][$count]['vid'] . '#dynoruns">' . garage_title_clean($context['top_dr'][$count]['vehicle']) . '</a></td>
                                    <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['top_dr'][$count]['user_id'] . '">' . $context['top_dr'][$count]['memberName'] . '</a></td>
                                    <td align="center" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_dynorun;VID=' . $context['top_dr'][$count]['vid'] . ';DID=' . $context['top_dr'][$count]['id'] . '">' . garage_title_clean($context['top_dr'][$count]['dynorun']) . '</a></td>
                                </tr>';
                $count++;
            }
        } else {
            $context['blocks']['top_dr'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_dynoruns_in_garage'] . '</td>
                                </tr>';
        }
        $context['blocks']['top_dr'] .= '
                            </table>';


        $context['blocks']['top_dr'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['top_dr'] .= '    
                </td>
            </tr>
        </table>';

    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_top_lap']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five top laps
        // Check the POST variable for a numeric value and set the query string if there is a track selected
        if (!isset($_POST['track_select'])) {
            $_POST['track_select'] = "";
        }
        if (ctype_digit($_POST['track_select']) === true) {
            if (!empty($_POST['track_select'])) {
                $spec_track = "AND l.track_id = {int:track_select}";
            } else {
                $spec_track = "";
            }
        } else {
            $spec_track = "";
        }

        // Get the lap data
        $request = $smcFunc['db_query']('', '
            SELECT l.id, l.vehicle_id, l.track_id, t.title, CONCAT_WS(" ", v.made_year, mk.make, md.model ), CONCAT_WS(":", l.minute, l.second, l.millisecond ) AS time
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
                LIMIT 0, {int:top_lap_limit}',
            array(
                'top_lap_limit' => $smfgSettings['top_lap_limit'],
                'track_select' => $_POST['track_select'],
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
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['top_laps'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['top_laps'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'topLaps\', \'topLapsUpshrink\', \'topLaps2\'); return false;"><img id="topLapsUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=laptimes">' . $txt['smfg_top_laptimes'] . '</a>';

        $context['blocks']['top_laps'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['top_laps'] .= '
                </td>
            </tr>';

        $context['blocks']['top_laps'] .= '
            <tr>
                <td>';

        $context['blocks']['top_laps'] .= '
                <div id="topLaps">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['top_laps'][$count]['vid'])) {
            $context['blocks']['top_laps'] .= '
                                <tr>
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_track'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_laptime_specs'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['top_laps'][$count]['vid'])) {
                $context['blocks']['top_laps'] .= '
                                <tr class="tableRow">
                                    <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['top_laps'][$count]['vid'] . '#laps">' . garage_title_clean($context['top_laps'][$count]['vehicle']) . '</a></td>
                                    <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_track;TID=' . $context['top_laps'][$count]['tid'] . '">' . garage_title_clean($context['top_laps'][$count]['track']) . '</a></td>
                                    <td align="center" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_laptime;VID=' . $context['top_laps'][$count]['vid'] . ';LID=' . $context['top_laps'][$count]['lid'] . '">' . garage_title_clean($context['top_laps'][$count]['time']) . '</a></td>
                                </tr>';
                $count++;
            }
        } else {
            $context['blocks']['top_laps'] .= '
                                <tr>
                                    <td align="center" valign="middle">' . $txt['smfg_no_laps_on_track'] . '</td>
                                </tr>';
        }

        $context['blocks']['top_laps'] .= ' 
                            <tr>
                                <td colspan="3">                               
                                    <form action="' . $scripturl . '?action=garage;sa=main" name="trackSelect" method="post" style="padding:0; margin:0;">
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tr id="topLaps2">
                                            <td align="right"><hr /><b>' . $txt['smfg_select_track'] . ':</b>&nbsp;<select name="track_select" onchange="trackSelect.submit();"><option value="">--------</option>';
        $context['blocks']['top_laps'] .= track_select($_POST['track_select'], true);
        $context['blocks']['top_laps'] .= '</select></td>
                                        </tr>
                                    </table>
                                    </form>
                                </td>
                            </tr>';

        $context['blocks']['top_laps'] .= '
                            </table>';


        $context['blocks']['top_laps'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['top_laps'] .= '    
                </td>
            </tr>
        </table>';

    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_last_service']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five latest vehicles with service history
        $request = $smcFunc['db_query']('', '
            SELECT sh.id, sh.vehicle_id, sh.date_created, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
            FROM {db_prefix}garage_service_history AS sh, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
            WHERE sh.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND v.user_id = u.id_member
            ORDER BY sh.date_created DESC
            LIMIT 0, {int:latest_service_limit}',
            array(
                'latest_service_limit' => $smfgSettings['latest_service_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['last_service'][$count]['id'],
                $context['last_service'][$count]['vid'],
                $context['last_service'][$count]['date_created'],
                $context['last_service'][$count]['user_id'],
                $context['last_service'][$count]['vehicle'],
                $context['last_service'][$count]['memberName']) = $row;
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['last_service'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['last_service'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'lastService\', \'lastServiceUpshrink\'); return false;"><img id="lastServiceUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=latestservice">' . $txt['smfg_latest_service'] . '</a>';

        $context['blocks']['last_service'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['last_service'] .= '
                </td>
            </tr>';

        $context['blocks']['last_service'] .= '
            <tr>
                <td>';

        $context['blocks']['last_service'] .= '
                <div id="lastService">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['last_service'][$count]['id'])) {
            $context['blocks']['last_service'] .= '
                                <tr>';
            $context['blocks']['last_service'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_created'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['last_service'][$count]['id'])) {
                $context['blocks']['last_service'] .= '
                                <tr class="tableRow">
                                    <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_service'][$count]['vid'] . '#services">' . garage_title_clean($context['last_service'][$count]['vehicle']) . '</a></td>
                                    <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['last_service'][$count]['user_id'] . '">' . $context['last_service'][$count]['memberName'] . '</a></td>
                                    <td align="center" valign="middle" nowrap="nowrap">' . date($context['date_format'],
                        $context['last_service'][$count]['date_created']) . '</td>
                                </tr>';
                $count++;
            }
        } else {
            $context['blocks']['last_service'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_services_in_garage'] . '</td>
                                </tr>';
        }
        $context['blocks']['last_service'] .= '
                            </table>';


        $context['blocks']['last_service'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['last_service'] .= '    
                </td>
            </tr>
        </table>';

    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_last_blog']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five latest vehicles with blog entries
        $request = $smcFunc['db_query']('', '
            SELECT b.id, b.vehicle_id, b.blog_title, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name
            FROM {db_prefix}garage_blog AS b, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
            WHERE b.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND v.user_id = u.id_member
            ORDER BY b.post_date DESC
            LIMIT 0, {int:latest_blog_limit}',
            array(
                'latest_blog_limit' => $smfgSettings['latest_blog_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['last_blog'][$count]['id'],
                $context['last_blog'][$count]['vid'],
                $context['last_blog'][$count]['blog_title'],
                $context['last_blog'][$count]['user_id'],
                $context['last_blog'][$count]['vehicle'],
                $context['last_blog'][$count]['memberName']) = $row;
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['last_blog'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['last_blog'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'lastBlog\', \'lastBlogUpshrink\'); return false;"><img id="lastBlogUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=latestblog">' . $txt['smfg_latest_blog'] . '</a>';

        $context['blocks']['last_blog'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['last_blog'] .= '
                </td>
            </tr>';

        $context['blocks']['last_blog'] .= '
            <tr>
                <td>';

        $context['blocks']['last_blog'] .= '
                <div id="lastBlog">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['last_blog'][$count]['id'])) {
            $context['blocks']['last_blog'] .= '
                                <tr>';
            $context['blocks']['last_blog'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_blog'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['last_blog'][$count]['id'])) {
                $context['blocks']['last_blog'] .= '
                                <tr class="tableRow">
                                    <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_blog'][$count]['vid'] . '">' . garage_title_clean($context['last_blog'][$count]['vehicle']) . '</a></td>
                                    <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['last_blog'][$count]['user_id'] . '">' . $context['last_blog'][$count]['memberName'] . '</a></td>
                                    <td align="center" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_blog'][$count]['vid'] . '#blog">' . garage_title_clean($context['last_blog'][$count]['blog_title']) . '</a></td>
                                </tr>';
                $count++;
            }
        } else {
            $context['blocks']['last_blog'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_blogs_in_garage'] . '</td>
                                </tr>';
        }
        $context['blocks']['last_blog'] .= '
                            </table>';


        $context['blocks']['last_blog'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['last_blog'] .= '    
                </td>
            </tr>
        </table>';
    }

    ###################################################
    ##                Next Block                     ##
    ###################################################

    // Check if block is enabled
    if ($smfgSettings['enable_last_video']) {

        // Set or increase the blocks count
        if (isset($context['blocks']['total'])) {
            $context['blocks']['total']++;
        } else {
            $context['blocks']['total'] = 1;
        }

        // Get the five latest vehicles with video entries
        $request = $smcFunc['db_query']('', '
            SELECT b.id, b.vehicle_id, b.title, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), u.real_name, b2.type, b2.type_id
            FROM {db_prefix}garage_video AS b, {db_prefix}garage_video_gallery AS b2, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
            WHERE b.vehicle_id = v.id
                AND b2.video_id = b.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND mk.pending != "1"
                AND md.pending != "1"
                AND v.pending != "1"
                AND v.user_id = u.id_member
            ORDER BY b.id DESC
            LIMIT 0, {int:latest_video_limit}',
            array(
                'latest_video_limit' => $smfgSettings['latest_video_limit'],
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['last_video'][$count]['id'],
                $context['last_video'][$count]['vid'],
                $context['last_video'][$count]['video_title'],
                $context['last_video'][$count]['user_id'],
                $context['last_video'][$count]['vehicle'],
                $context['last_video'][$count]['memberName'],
                $context['last_video'][$count]['type'],
                $context['last_video'][$count]['tid']) = $row;
            $count++;
        }
        $smcFunc['db_free_result']($request);

        // Build the block
        $context['blocks']['last_video'] = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>
                    <table class="table_list" cellspacing="0" cellpadding="0">
                        <tbody class="header">
                            <tr>
                                <td>
                                    <div class="cat_bar">
                                        <h3 class="catbg">';

        $context['blocks']['last_video'] .= '
                                            <a href="#" class="collapse" onclick="shrinkSection(\'lastVideo\', \'lastVideoUpshrink\'); return false;"><img id="lastVideoUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>
                                            <a href="' . $scripturl . '?action=garage;sa=latestvideo">' . $txt['smfg_latest_video'] . '</a>';

        $context['blocks']['last_video'] .= '

                                        </h3>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>';


        $context['blocks']['last_video'] .= '
                </td>
            </tr>';

        $context['blocks']['last_video'] .= '
            <tr>
                <td>';

        $context['blocks']['last_video'] .= '
                <div id="lastVideo">
                    <span class="clear upperframe"><span></span></span>
                    <div class="roundframe"><div class="innerframe">

                            <table border="0" cellspacing="1" width="100%" style="padding: 0 3px;">';

        //table data here
        $count = 0;
        if (isset($context['last_video'][$count]['id'])) {
            $context['blocks']['last_video'] .= '
                                <tr>';
            $context['blocks']['last_video'] .= '
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_vehicle'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_owner'] . '
                                    </span></h4></div></td>  
                                    <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                                    ' . $txt['smfg_video'] . '
                                    </span></h4></div></td>
                                </tr>';
            while (isset($context['last_video'][$count]['id'])) {

                //Check the string length of video title and trim it
                $context['last_video'][$count]['video_title'] = smfg_trim($context['last_video'][$count]['video_title']);

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

                $context['blocks']['last_video'] .= '
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['last_video'][$count]['vid'] . '">' . garage_title_clean($context['last_video'][$count]['vehicle']) . '</a></td>
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['last_video'][$count]['user_id'] . '">' . $context['last_video'][$count]['memberName'] . '</a></td>
                                        <td align="center" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;' . $uri . '#videos">' . $context['last_video'][$count]['video_title'] . '</a></td>
                                    </tr>';
                $count++;
            }
        } else {
            $context['blocks']['last_video'] .= '
                                <tr>
                                    <td align="center">' . $txt['smfg_no_videos_in_garage'] . '</td>
                                </tr>';
        }
        $context['blocks']['last_video'] .= '
                            </table>';


        $context['blocks']['last_video'] .= '       
                        </div>
                    </div>
                    <span class="lowerframe"><span></span></span>
                </div>';

        $context['blocks']['last_video'] .= '    
                </td>
            </tr>
        </table>';

    }

    ###################################################
    ##                End Blocks                     ##
    ###################################################

    // Load the block positions
    $request = $smcFunc['db_query']('', '
        SELECT block
        FROM {db_prefix}garage_blocks
        WHERE enabled = 1
            ORDER BY position ASC',
        array(// no values
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['blocks'][$count]['block']) = $row;
        $blocks[$count] = $context['blocks'][$count]['block'];
        $count++;
    }
    $smcFunc['db_free_result']($request);

    // Set count to 1 for proper counting
    $count = 1;
    $context['blocks']['display'] = '';
    foreach ($blocks as $block) {
        // Build the display
        $context['blocks']['display'] .= $context['blocks'][$block];
        // 1 or 2 columns?
        if ($smfgSettings['index_columns'] == 2) {
            // Set total to even number
            if ($context['blocks']['total'] & 1) {
                $context['blocks']['total'] += 1;
            }
            // Get the median
            $context['blocks']['median'] = $context['blocks']['total'] / 2;
            // Begin the 2nd column...or not
            if ($count == $context['blocks']['median']) {
                $context['blocks']['display'] .= '
                </td>
                <td width="10" valign="top">&nbsp;</td>
                <td width="49%" valign="top">';
            } else {
                // Last block?  No need for a break
                if ($count != $context['blocks']['total']) {
                    $context['blocks']['display'] .= '<br />';
                }
            }
        } else {
            // Last block?  No need for a break
            if ($count != $context['blocks']['total']) {
                // ...and we still need a break
                $context['blocks']['display'] .= '<br />';
            }
        }
        $count++;
    }

}

// User Garage
function G_User_Garage()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'user_garage';
    $context['date_format'] = $smfgSettings['dateformat'];
    $context['page_title'] = $txt['smfg_my_vehicles'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=user_garage',
        'name' => &$txt['smfg_my_vehicles']
    );

    $context['pending_modules'] = 0;

    // Select User's Vehicle(s)
    $request = $smcFunc['db_query']('', '
        SELECT v.id, CONCAT_WS(" ", v.made_year, mk.make, md.model), v.views, v.date_created, v.date_updated, IF(mk.pending = "1" OR md.pending = "1", 1, 0) AS pending
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.user_id = {int:user_id}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'user_id' => $context['user']['id'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list ($context['user_vehicles'][$count]['veh_id'],
            $context['user_vehicles'][$count]['vehicle'],
            $context['user_vehicles'][$count]['views'],
            $context['user_vehicles'][$count]['date_created'],
            $context['user_vehicles'][$count]['date_updated'],
            $context['user_vehicles'][$count]['pending']) = $row;
        $context['user_vehicles'][$count]['views'] = number_format($context['user_vehicles'][$count]['views'], 0, '.',
            ',');

        if ($context['user_vehicles'][$count]['pending']) {
            $context['pending_modules'] = 1;
        }

        // Get the # of total mods
        $request2 = $smcFunc['db_query']('', '
            SELECT id
            FROM {db_prefix}garage_modifications
            WHERE vehicle_id = {int:vid}',
            array(
                'vid' => $context['user_vehicles'][$count]['veh_id'],
            )
        );
        $context['user_vehicles'][$count]['total_mods'] = $smcFunc['db_num_rows']($request2);
        $smcFunc['db_free_result']($request2);

        $count++;
    }
    $smcFunc['db_free_result']($request);

}

// Add Vehicle to User Garage
function G_Add_Vehicle()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 1;

    $context['sub_template'] = 'add_vehicle';
    $context['max_image_bytes'] = $smfgSettings['max_image_kbytes'] * 1024;
    $context['page_title'] = $txt['smfg_create_vehicle'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=add_vehicle',
        'name' => &$txt['smfg_create_vehicle']
    );

    // Check Permissions
    isAllowedTo('own_vehicle');

    // Session variable set?
    if (!isset($_SESSION['added_make'])) {
        $_SESSION['added_make'] = 0;
    }
    if (!isset($_SESSION['added_model'])) {
        $_SESSION['added_model'] = 0;
    }

    // Check user for vehicle quota
    checkQuota($context['user']['id']);

    // Set max_image_bytes for form value
    $smfgSettings['max_image_bytes'] = $smfgSettings['max_image_kbytes'] * 1024;

}

// Insert Vehicle
function G_Insert_Vehicle()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Check Permissions
    isAllowedTo('own_vehicle');

    // Check if approval is required
    if ($smfgSettings['enable_vehicle_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    $context['date_created'] = time();

    // Will this be their main vehicle?
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_vehicles
        WHERE user_id = {int:user_id}',
        array(
            'user_id' => $context['user']['id'],
        )
    );
    $results = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    if ($results > 0) {
        $main_vehicle = 0;
    } else {
        $main_vehicle = 1;
    }

    // Clean up the mess
    if (empty($_POST['engine_type'])) {
        $_POST['engine_type'] = 0;
    }
    if (!empty($_POST['color'])) {
        if (!strpos($_POST['color'], '/')) {
            $_POST['color'] = ucwords($_POST['color']);
        } else {
            $pieces = explode('/', $_POST['color']);
            $count = 0;
            foreach ($pieces AS $piece) {
                $pieced[$count] = ucwords($piece);
                $count++;
            }
            $_POST['color'] = implode('/', $pieced);
        }
    }

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_vehicle_images']) {

        // Check maximum file size from MAX_FILE_SIZE
        if ($_FILES['FILE_UPLOAD']['error'] == 2) {
            loadLanguage('Errors');
            fatal_lang_error('garage_filesize_error', false);
        }

        // If they provided an image to upload use it, otherwise use the remote image
        // handle_images($gallery, $source(0=local,1=remote), $file, $extra)
        if ($_FILES['FILE_UPLOAD']['error'] == 0) {

            // Check for maximum file size allowed...again, just in case they made it this far
            $max_fsize = $smfgSettings['max_image_kbytes'] * 1024;
            if ($_FILES['FILE_UPLOAD']['size'] > $max_fsize) {
                loadLanguage('Errors');
                fatal_lang_error('garage_filesize_error', false);
            }

            // Check for maximum image resolution
            $dimensions = getimagesize($_FILES['FILE_UPLOAD']['tmp_name']) or die('Could not obtain image dimensions.');
            if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                loadLanguage('Errors');
                fatal_lang_error('garage_resolution_error', false);
            }

            // If all the image restrictions were met, go ahead and insert the vehicle data
            $request = $smcFunc['db_insert']('insert',
                '{db_prefix}garage_vehicles',
                array(
                    'user_id' => 'int',
                    'made_year' => 'int',
                    'engine_type' => 'int',
                    'color' => 'string',
                    'mileage' => 'int',
                    'mileage_unit' => 'string',
                    'price' => 'string',
                    'currency' => 'int',
                    'comments' => 'string',
                    'date_created' => 'int',
                    'date_updated' => 'int',
                    'make_id' => 'int',
                    'model_id' => 'int',
                    'main_vehicle' => 'int',
                    'pending' => 'string',
                ),
                array(
                    $context['user']['id'],
                    $_POST['made_year'],
                    $_POST['engine_type'],
                    $_POST['color'],
                    $_POST['mileage'],
                    $_POST['mileage_units'],
                    $_POST['price'],
                    $_POST['currency'],
                    $_POST['comments'],
                    $context['date_created'],
                    $context['date_created'],
                    $_POST['make_id'],
                    $_POST['model_id'],
                    $main_vehicle,
                    $pending,
                ),
                array(// no data
                )
            );
            $context['vehicle_id'] = $smcFunc['db_insert_id']($request);

            // If they made it this far, go ahead and process the image
            handle_images("garage", 0, $_FILES['FILE_UPLOAD'], $_POST);

            // Insert table data for vehicles_gallery
            $request = $smcFunc['db_insert']('insert',
                '{db_prefix}garage_vehicles_gallery',
                array(
                    'vehicle_id' => 'int',
                    'image_id' => 'int',
                    'hilite' => 'int',
                ),
                array(
                    $context['vehicle_id'],
                    $context['image_id'],
                    1,
                ),
                array(// no data
                )
            );

        } // Check if a remote image was supplied...then use it
        else {
            if ($_POST['url_image'] !== 'http://' && $_POST['url_image'] !== 'https://') {

                // Check for maximum image resolution
                $dimensions = getimagesize_remote($_POST['url_image']) or die('Could not obtain remote image dimensions.');
                if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                    loadLanguage('Errors');
                    fatal_lang_error('garage_resolution_error', false);
                }

                // If all the image restrictions were met, go ahead and insert the vehicle data
                $request = $smcFunc['db_insert']('insert',
                    '{db_prefix}garage_vehicles',
                    array(
                        'user_id' => 'int',
                        'made_year' => 'int',
                        'engine_type' => 'int',
                        'color' => 'string',
                        'mileage' => 'int',
                        'mileage_unit' => 'string',
                        'price' => 'string',
                        'currency' => 'int',
                        'comments' => 'string',
                        'date_created' => 'int',
                        'date_updated' => 'int',
                        'make_id' => 'int',
                        'model_id' => 'int',
                        'main_vehicle' => 'int',
                        'pending' => 'string',
                    ),
                    array(
                        $context['user']['id'],
                        $_POST['made_year'],
                        $_POST['engine_type'],
                        $_POST['color'],
                        $_POST['mileage'],
                        $_POST['mileage_units'],
                        $_POST['price'],
                        $_POST['currency'],
                        $_POST['comments'],
                        $context['date_created'],
                        $context['date_created'],
                        $_POST['make_id'],
                        $_POST['model_id'],
                        $main_vehicle,
                        $pending,
                    ),
                    array(// no data
                    )
                );
                $context['vehicle_id'] = $smcFunc['db_insert_id']($request);

                // If they made it this far, go ahead and process the image
                handle_images("garage", 1, $_POST);

                // Insert table data for vehicles_gallery
                $request = $smcFunc['db_insert']('insert',
                    '{db_prefix}garage_vehicles_gallery',
                    array(
                        'vehicle_id' => 'int',
                        'image_id' => 'int',
                        'hilite' => 'int',
                    ),
                    array(
                        $context['vehicle_id'],
                        $context['image_id'],
                        1,
                    ),
                    array(// no data
                    )
                );

            }
        }

    }

    // If modification images are disabled or no image was provided, we still need to insert the data
    if (!$smfgSettings['enable_vehicle_images'] || $_FILES['FILE_UPLOAD']['error'] == 4 && ($_POST['url_image'] === 'http://' || $_POST['url_image'] === 'https://')) {

        // Insert vehicle
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_vehicles',
            array(
                'user_id' => 'int',
                'made_year' => 'int',
                'engine_type' => 'int',
                'color' => 'string',
                'mileage' => 'int',
                'mileage_unit' => 'string',
                'price' => 'string',
                'currency' => 'int',
                'comments' => 'string',
                'date_created' => 'int',
                'date_updated' => 'int',
                'make_id' => 'int',
                'model_id' => 'int',
                'main_vehicle' => 'int',
                'pending' => 'string',
            ),
            array(
                $context['user']['id'],
                $_POST['made_year'],
                $_POST['engine_type'],
                $_POST['color'],
                $_POST['mileage'],
                $_POST['mileage_units'],
                $_POST['price'],
                $_POST['currency'],
                $_POST['comments'],
                $context['date_created'],
                $context['date_created'],
                $_POST['make_id'],
                $_POST['model_id'],
                $main_vehicle,
                $pending,
            ),
            array(// no data
            )
        );
        $context['vehicle_id'] = $smcFunc['db_insert_id']($request);
    }

    // Perform video actions if images are enabled
    if ($smfgSettings['enable_vehicle_video'] && $_POST['video_url'] > 'http://') {

        // Check for video title
        if (empty($_POST['video_title'])) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_title_required', false);
        }

        // Check for video URL compatibility
        if (!displayVideo($_POST['video_url'], 4)) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_unsupported', false);
        }

        // Insert video
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video',
            array(
                'vehicle_id' => 'int',
                'url' => 'string',
                'title' => 'string',
                'video_desc' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['video_url'],
                $_POST['video_title'],
                $_POST['video_desc'],
            ),
            array(// no data
            )
        );
        $context['video_id'] = $smcFunc['db_insert_id']($request);

        // Insert table data for video_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video_gallery',
            array(
                'vehicle_id' => 'int',
                'video_id' => 'int',
                'type' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $context['video_id'],
                'vehicle',
            ),
            array(// no data
            )
        );

    }

    // Send out Notifications
    if ($smfgSettings['enable_vehicle_approval']) {
        sendGarageNotifications();
    }

    // And finally.....send them to their newly created vehicle
    // header( 'Location: '.$scripturl.'?action=garage;sa=view_own_vehicle;VID='.$context['vehicle_id']);
    $newurl = str_replace("{VID}", $context['vehicle_id'], $_POST['redirecturl']);
    header('Location: ' . $newurl);

}

// View User's Garage
function G_View_Garage()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'view_garage';
    $context['date_format'] = $smfgSettings['dateformat'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_garage;UID=' . $_GET['UID'],
        'name' => &$txt['smfg_view_garage']
    );

    // Make sure the user didn't access this page directly
    if (!isset($_GET['UID'])) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_direct_page_access', false);
    }

    // Get User's Real Name from id
    $request = $smcFunc['db_query']('', '
        SELECT u.real_name
        FROM {db_prefix}members AS u
        WHERE u.id_member = {int:uid}',
        array(
            'uid' => $_GET['UID'],
        )
    );
    list($context['user_vehicles']['memberName']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Now that we have their name, lets set the page title
    $context['page_title'] = $context['user_vehicles']['memberName'] . '\'s ' . $txt['smfg_garage'];

    // Select User's Vehicle(s)
    $request = $smcFunc['db_query']('', '
        SELECT v.id, CONCAT_WS(" ", v.made_year, mk.make, md.model), v.color, v.mileage, v.mileage_unit, v.views, v.date_created, v.date_updated, IF(mk.pending = "1" OR md.pending = "1", 1, 0) AS pending
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.user_id = {int:uid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'uid' => $_GET['UID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list ($context['user_vehicles'][$count]['veh_id'],
            $context['user_vehicles'][$count]['vehicle'],
            $context['user_vehicles'][$count]['color'],
            $context['user_vehicles'][$count]['mileage'],
            $context['user_vehicles'][$count]['mileage_unit'],
            $context['user_vehicles'][$count]['views'],
            $context['user_vehicles'][$count]['date_created'],
            $context['user_vehicles'][$count]['date_updated'],
            $context['user_vehicles'][$count]['pending']) = $row;
        $context['user_vehicles'][$count]['views'] = number_format($context['user_vehicles'][$count]['views'], 0, '.',
            ',');
        $context['user_vehicles'][$count]['mileage'] = number_format($context['user_vehicles'][$count]['mileage'], 0,
            '.', ',');

        // Get the # of total mods
        $request2 = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_modifications
        WHERE vehicle_id = {int:vid}',
            array(
                'vid' => $context['user_vehicles'][$count]['veh_id'],
            )
        );
        $context['user_vehicles'][$count]['total_mods'] = $smcFunc['db_num_rows']($request2);
        $smcFunc['db_free_result']($request2);

        // Get Rating sytem settings
        if ($smfgSettings['rating_system'] == 0) {
            $ratingfunc = "SUM";
        } else {
            if ($smfgSettings['rating_system'] == 1) {
                $ratingfunc = "AVG";
            }
        }

        // Get vehicle rating
        $request2 = $smcFunc['db_query']('', '
        SELECT ' . $ratingfunc . '( rating ), COUNT( id ) * 10
        FROM {db_prefix}garage_ratings
        WHERE vehicle_id = {int:vid}
        GROUP BY vehicle_id',
            array(
                'vid' => $context['user_vehicles'][$count]['veh_id'],
            )
        );
        list($context['user_vehicles'][$count]['rating'],
            $context['user_vehicles'][$count]['poss_rating']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        if ($context['user_vehicles'][$count]['rating'] > 0) {
            $context['user_vehicles'][$count]['rating'] = number_format($context['user_vehicles'][$count]['rating'], 2,
                '.', ',');
        } else {
            $context['user_vehicles'][$count]['rating'] = 0;
        }

        // Check if there is a hilite image
        $request2 = $smcFunc['db_query']('', '
        SELECT image_id
        FROM {db_prefix}garage_vehicles_gallery
        WHERE vehicle_id = {int:vid}
            AND hilite = 1',
            array(
                'vid' => $context['user_vehicles'][$count]['veh_id'],
            )
        );
        list($context['user_vehicles'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        // Select image data if there is any
        $context['user_vehicles'][$count]['image'] = "";
        if (isset($context['user_vehicles'][$count]['image_id'])) {

            $request2 = $smcFunc['db_query']('', '
        SELECT attach_location, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_desc, is_remote 
        FROM {db_prefix}garage_images 
        WHERE attach_id = {int:image_id}',
                array(
                    'image_id' => $context['user_vehicles'][$count]['image_id'],
                )
            );
            list($context['user_vehicles'][$count]['attach_location'],
                $context['user_vehicles'][$count]['attach_file'],
                $context['user_vehicles'][$count]['attach_thumb_location'],
                $context['user_vehicles'][$count]['attach_thumb_width'],
                $context['user_vehicles'][$count]['attach_thumb_height'],
                $context['user_vehicles'][$count]['attach_desc'],
                $context['user_vehicles'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);
            if (empty($context['user_vehicles'][$count]['attach_desc'])) {
                $context['user_vehicles'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }

            // Check to see if the image is remote or not and build appropriate links
            if ($context['user_vehicles'][$count]['is_remote'] == 1) {
                $context['user_vehicles'][$count]['attach_location'] = urldecode($context['user_vehicles'][$count]['attach_file']);
            } else {
                $context['user_vehicles'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['user_vehicles'][$count]['attach_location'];
            }

            // If there is an image attached, link to it
            if (isset($context['user_vehicles'][$count]['attach_location'])) {
                $context['user_vehicles'][$count]['image'] = "<a href=\"" . $context['user_vehicles'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . $context['user_vehicles'][$count]['vehicle'] . " :: Owner: " . $context['user_vehicles']['memberName'] . "\" class=\"smfg_imageTitle\"><img src=\"" . $boardurl . "/" . $smfgSettings['upload_directory'] . 'cache/' . $context['user_vehicles'][$count]['attach_thumb_location'] . "\" width=\"" . $context['user_vehicles'][$count]['attach_thumb_width'] . "\" height=\"" . $context['user_vehicles'][$count]['attach_thumb_height'] . "\" /></a><br />";
            }

            //No hilight image? Display "no image" place holder image.
        } else {
            if (!isset($context['user_vehicles'][$count]['image_id'])) {
                list($fv_width, $fv_height) = getimagesize($settings['actual_images_url'] . "/garage_no_vehicle_thumb.png");
                $context['user_vehicles'][$count]['image'] = "<img src=\"" . $settings['actual_images_url'] . "/garage_no_vehicle_thumb.png\" width=\"" . $fv_width . "\" height=\"" . $fv_height . "\" /><br />";
            }
        }

        $count++;
    }
    $smcFunc['db_free_result']($request);

    // User's Garage Comments PAGINATION: Get the total number of comments posts
    $context['comments'] = array();
    $request = $smcFunc['db_query']('', '
        SELECT count(*) 
        FROM {db_prefix}garage_comments
        WHERE user_id = {int:uid}
            AND pending != "1"',
        array(
            'uid' => $_GET['UID'],
        )
    );
    list($context['comments']['total']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Construct the page index
    $context['comments']['display'] = $smfgSettings['comments_per_page'];
    $context['comments']['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=view_garage' . (isset($_REQUEST['UID']) ? ';UID=' . $_REQUEST['UID'] : ''),
        $_REQUEST['start'], $context['comments']['total'], $context['comments']['display'], false);
    $context['comments']['start'] = $_REQUEST['start'] + 1;
    $context['comments']['end'] = min($_REQUEST['start'] + $context['comments']['display'],
        $context['comments']['total']);

    // Get comment data
    $request = $smcFunc['db_query']('', '
        SELECT u.real_name, u.date_registered, u.posts, c.id,  c.author_id, c.post_date, c.ip_address, c.post
        FROM {db_prefix}garage_comments AS c, {db_prefix}members AS u
        WHERE c.user_id = {int:uid}
            AND c.author_id = u.id_member
            AND c.pending != "1"
            ORDER BY c.post_date DESC
            LIMIT {int:start}, {int:end}',
        array(
            'uid' => $_GET['UID'],
            'start' => $_REQUEST['start'],
            'end' => $context['comments']['display'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['comments'][$count]['author'],
            $context['comments'][$count]['date_reg'],
            $context['comments'][$count]['posts'],
            $context['comments'][$count]['CID'],
            $context['comments'][$count]['author_id'],
            $context['comments'][$count]['post_date'],
            $context['comments'][$count]['author_ip'],
            $context['comments'][$count]['comment']) = $row;
        $context['comments'][$count]['comment'] = str_replace(array("\n", "{SEMICOLON}"), array("<br />", ";"),
            $context['comments'][$count]['comment']);
        $context['comments'][$count]['posts'] = number_format($context['comments'][$count]['posts']);
        // Is BBCode enabled?
        if ($smfgSettings['enable_guestbooks_bbcode']) {
            $context['comments'][$count]['comment'] = parse_bbc($context['comments'][$count]['comment']);
        }
        // Check if author has a vehicle
        $request2 = $smcFunc['db_query']('', '
           SELECT id
           FROM {db_prefix}garage_vehicles
           WHERE user_id = {int:author_id}
               AND main_vehicle = 1',
            array(
                'author_id' => $context['comments'][$count]['author_id'],
            )
        );
        list($context['comments'][$count]['author_VID']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);
        // If there is a vehicle, get its title
        if (isset($context['comments'][$count]['author_VID'])) {
            $request2 = $smcFunc['db_query']('', '
               SELECT v.made_year, mk.make, md.model
               FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
               WHERE user_id = {int:author_id}
                   AND v.main_vehicle = 1
                   AND v.make_id = mk.id
                   AND v.model_id = md.id',
                array(
                    'author_id' => $context['comments'][$count]['author_id'],
                )
            );
            list($context['comments'][$count]['made_year'],
                $context['comments'][$count]['make'],
                $context['comments'][$count]['model']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);
            $context['comments'][$count]['author_vehicle'] = $context['comments'][$count]['made_year'] . ' ' . $context['comments'][$count]['make'] . ' ' . $context['comments'][$count]['model'];
        } else {
            $context['comments'][$count]['author_vehicle'] = "";
        }
        $count++;
    }
}

// View Vehicle
function G_View_Vehicle()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'view_vehicle';
    $context['date_format'] = $smfgSettings['dateformat'];

    // Make sure the user didn't access this page directly
    if (!isset($_GET['VID'])) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_direct_page_access', false);
    }

    // Check Permissions
    isAllowedTo('view_vehicles');

    $veh_id = $_GET['VID'];
    // Check and make sure the vehicle is in the db
    $request = $smcFunc['db_query']('', '
        SELECT v.id
        FROM {db_prefix}garage_vehicles AS v
        WHERE v.id = {int:vid}
        LIMIT 1',
        array(
            'vid' => $veh_id,
        )
    );
    $matching_vid = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_vid <= 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_vehicle', false);
    }

    // Select Vehicle
    $context['user_vehicles'] = array();
    $request = $smcFunc['db_query']('', '
        SELECT v.id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), v.color, v.mileage, v.mileage_unit, v.price, v.currency, v.comments, v.views, v.date_created, v.date_updated, v.main_vehicle, v.pending, v.engine_type
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $veh_id,
        )
    );
    list ($context['user_vehicles']['id'],
        $context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle'],
        $context['user_vehicles']['color'],
        $context['user_vehicles']['mileage'],
        $context['user_vehicles']['mileage_unit'],
        $context['user_vehicles']['price'],
        $context['user_vehicles']['currency_id'],
        $context['user_vehicles']['comments'],
        $context['user_vehicles']['views'],
        $context['user_vehicles']['date_created'],
        $context['user_vehicles']['date_updated'],
        $context['user_vehicles']['main_vehicle'],
        $context['user_vehicles']['pending'],
        $context['user_vehicles']['engine_type']) = $smcFunc['db_fetch_row']($request);
    $context['user_vehicles']['price'] = number_format($context['user_vehicles']['price'], 2, '.', ',');
    $context['user_vehicles']['mileage'] = number_format($context['user_vehicles']['mileage'], 0, '.', ',');
    $smcFunc['db_free_result']($request);

    // Check if vehicle is pending, if they are the owner, or if they have permission to view pending items
    if ($context['user_vehicles']['pending'] == '1' && $context['user_vehicles']['user_id'] != $context['user']['id'] && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_vehicle_error', false);
    }

    // Does the owner want to view or edit?
    //session_start();

    if (isset($_GET['view_as_user']) && $_GET['view_as_user'] == 1) {
        $_SESSION['view_as_user'] = 1;
    } else {
        if (isset($_GET['view_as_user']) && $_GET['view_as_user'] == 0) {
            $_SESSION['view_as_user'] = 0;
        } else {
            if (!isset($_SESSION['view_as_user'])) {
                $_SESSION['view_as_user'] = "";
            }
        }
    }

    // Check if they are the owner
    if ($context['user_vehicles']['user_id'] == $context['user']['id'] && $_SESSION['view_as_user'] != 1) {
        $context['view_own_vehicle'] = 1;
    } else {
        if ($_SESSION['view_as_user'] == 1) {
            $context['view_own_vehicle'] = 0;
        } else {
            $context['view_own_vehicle'] = 0;
        }
    }

    // Get the engine type
    if (!empty($context['user_vehicles']['engine_type'])) {
        $request = $smcFunc['db_query']('', '
            SELECT title
            FROM {db_prefix}garage_engine_types
            WHERE id = {int:engine_type}',
            array(
                'engine_type' => $context['user_vehicles']['engine_type'],
            )
        );
        list($context['user_vehicles']['engine']) = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);
    } else {
        $context['user_vehicles']['engine'] = "";
    }

    // Get the currency
    $request = $smcFunc['db_query']('', '
        SELECT title
            FROM {db_prefix}garage_currency
            WHERE id = {int:currency_id}',
        array(
            'currency_id' => $context['user_vehicles']['currency_id'],
        )
    );
    list($context['user_vehicles']['currency']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Get the # of total mods
    $request = $smcFunc['db_query']('', '
        SELECT id
            FROM {db_prefix}garage_modifications
            WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $veh_id,
        )
    );
    $context['user_vehicles']['total_mods'] = $smcFunc['db_num_rows']($request);
    $smcFunc['db_free_result']($request);

    // *************************************************************
    // WARNING: The query check is being disabled to allow for the following subselect.
    // It is imperative this is turned back on for security reasons.
    // *************************************************************
    $modSettings['disableQueryCheck'] = 1;
    // *************************************************************

    // Time for total spent fun!
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
                GROUP BY vehicle_id) AS s ON v.id = s.vehicle_id
        WHERE v.id = {int:vid}
        GROUP BY v.id, IFNULL(m.total_mods,0) + IFNULL(s.total_service,0)
        ORDER BY total_spent DESC
        LIMIT 1',
        array(
            'vid' => $veh_id,
        )
    );
    list($context['vid'], $context['user_vehicles']['total_spent']) = $smcFunc['db_fetch_row']($request);
    $context['user_vehicles']['total_spent'] = number_format($context['user_vehicles']['total_spent'], 2, '.', ',');

    // *************************************************************
    // WARNING: The query check is being enabled, this MUST BE DONE!
    // *************************************************************
    $modSettings['disableQueryCheck'] = 0;
    // *************************************************************

    // Select image id
    $request = $smcFunc['db_query']('', '
        SELECT image_id, hilite 
        FROM {db_prefix}garage_vehicles_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $veh_id,
        )
    );
    $context['user_vehicles']['total_images'] = $smcFunc['db_num_rows']($request);
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['user_vehicles'][$count]['image_id'],
            $context['user_vehicles'][$count]['hilite']) = $row;

        // Select image data if there is any
        if (!empty($context['user_vehicles'][$count]['image_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT attach_location, attach_hits, attach_ext, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_is_image, attach_date, attach_filesize, attach_thumb_filesize, attach_desc, is_remote 
                    FROM {db_prefix}garage_images 
                    WHERE attach_id = {int:image_id}',
                array(
                    'image_id' => $context['user_vehicles'][$count]['image_id'],
                )
            );
            list($context['user_vehicles'][$count]['attach_location'],
                $context['user_vehicles'][$count]['attach_hits'],
                $context['user_vehicles'][$count]['attach_ext'],
                $context['user_vehicles'][$count]['attach_file'],
                $context['user_vehicles'][$count]['attach_thumb_location'],
                $context['user_vehicles'][$count]['attach_thumb_width'],
                $context['user_vehicles'][$count]['attach_thumb_height'],
                $context['user_vehicles'][$count]['attach_is_image'],
                $context['user_vehicles'][$count]['attach_date'],
                $context['user_vehicles'][$count]['attach_filesize'],
                $context['user_vehicles'][$count]['attach_thumb_filesize'],
                $context['user_vehicles'][$count]['attach_desc'],
                $context['user_vehicles'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);

            if (empty($context['user_vehicles'][$count]['attach_desc'])) {
                $context['user_vehicles'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }

            if ($context['user_vehicles'][$count]['hilite'] == 1) {
                if ($context['user_vehicles'][$count]['is_remote'] == 1) {
                    $context['hilite_image_location'] = urldecode($context['user_vehicles'][$count]['attach_file']);
                } else {
                    $context['hilite_image_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['user_vehicles'][$count]['attach_location'];
                }
                $context['hilite_thumb_location'] = $context['user_vehicles'][$count]['attach_thumb_location'];
                $context['hilite_thumb_width'] = $context['user_vehicles'][$count]['attach_thumb_width'];
                $context['hilite_thumb_height'] = $context['user_vehicles'][$count]['attach_thumb_height'];
                $context['hilite_desc'] = $context['user_vehicles'][$count]['attach_desc'];
            }
            if ($context['user_vehicles'][$count]['is_remote'] == 1) {
                $context['user_vehicles'][$count]['attach_location'] = urldecode($context['user_vehicles'][$count]['attach_file']);
            } else {
                $context['user_vehicles'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['user_vehicles'][$count]['attach_location'];
            }
            $count++;
        }
    }
    $smcFunc['db_free_result']($request);

    if (!isset($context['hilite_image_location'])) {
        $context['hilite_image_location'] = "";
    }
    if (!isset($context['hilite_thumb_location'])) {
        $context['hilite_thumb_location'] = "";
    }

    if ($smfgSettings['enable_vehicle_video']) {

        // Select video id
        $request = $smcFunc['db_query']('', '
            SELECT video_id
                FROM {db_prefix}garage_video_gallery
                WHERE vehicle_id = {int:vid}
                    AND type = "vehicle"',
            array(
                'vid' => $veh_id,
            )
        );
        $count = 0;
        while ($row = $smcFunc['db_fetch_row']($request)) {
            list($context['user_vehicles'][$count]['video_id']) = $row;

            // Select video data if there is any
            if (!empty($context['user_vehicles'][$count]['video_id'])) {
                $request2 = $smcFunc['db_query']('', '
                        SELECT url, title, video_desc
                        FROM {db_prefix}garage_video 
                        WHERE id = {int:video_id}',
                    array(
                        'video_id' => $context['user_vehicles'][$count]['video_id'],
                    )
                );
                list($context['user_vehicles'][$count]['video_url'],
                    $context['user_vehicles'][$count]['video_title'],
                    $context['user_vehicles'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                $smcFunc['db_free_result']($request2);
                if (empty($context['user_vehicles'][$count]['video_desc'])) {
                    $context['user_vehicles'][$count]['video_desc'] = $txt['smfg_no_desc'];
                }
                $context['user_vehicles'][$count]['video_thumb'] = displayVideo($context['user_vehicles'][$count]['video_url'],
                    2);
                $context['user_vehicles'][$count]['video_height'] = displayVideo($context['user_vehicles'][$count]['video_url'],
                    'height');
                $context['user_vehicles'][$count]['video_width'] = displayVideo($context['user_vehicles'][$count]['video_url'],
                    'width');
            }
            $count++;
        }

    }

    // Get Modification Data
    $context['mods'] = array();
    //$context['user_vehicles']['total_spent'] = 0;
    $request = $smcFunc['db_query']('', '
        SELECT m.id, p.title, b.title, c.title, m.price, m.install_price, m.product_rating, m.date_created, m.date_updated
        FROM {db_prefix}garage_modifications AS m, {db_prefix}garage_categories AS c, {db_prefix}garage_products AS p, {db_prefix}garage_business AS b
        WHERE m.vehicle_id = {int:vid}
            AND m.product_id = p.id
            AND p.business_id = b.id
            AND m.category_id = c.id
            AND m.pending != "1"
            AND p.pending != "1"
            AND b.pending != "1"
            ORDER BY m.date_created DESC',
        array(
            'vid' => $veh_id,
        )
    );
    $count = 0;
    //$context['mods']['total_spent'] = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['mods'][$count]['id'],
            $context['mods'][$count]['title'],
            $context['mods'][$count]['manufacturer'],
            $context['mods'][$count]['category'],
            $context['mods'][$count]['price'],
            $context['mods'][$count]['install_price'],
            $context['mods'][$count]['product_rating'],
            $context['mods'][$count]['date_created'],
            $context['mods'][$count]['date_updated']) = $row;
        $context['mods'][$count]['price'] = number_format($context['mods'][$count]['price'], 2, '.', ',');
        $context['mods'][$count]['install_price'] = number_format($context['mods'][$count]['install_price'], 2, '.',
            ',');

        // Add the total spent each loop
        //$context['mods']['total_spent'] += $context['mods'][$count]['price'] + $context['mods'][$count]['install_price'];

        // Check to see if there is an image attached
        $request2 = $smcFunc['db_query']('', '
            SELECT image_id
            FROM {db_prefix}garage_modifications_gallery
            WHERE modification_id = {int:mid}
                AND hilite = 1',
            array(
                'mid' => $context['mods'][$count]['id'],
            )
        );
        list($context['mods'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
        $context['mods'][$count]['image'] = "";
        // If there is not an image attached, dont go looking for extra info
        if (!empty($context['mods'][$count]['image_id'])) {
            $request3 = $smcFunc['db_query']('', '
                SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                FROM {db_prefix}garage_images
                WHERE attach_id = {int:image_id}',
                array(
                    'image_id' => $context['mods'][$count]['image_id'],
                )
            );
            list($context['mods'][$count]['attach_location'],
                $context['mods'][$count]['attach_ext'],
                $context['mods'][$count]['attach_file'],
                $context['mods'][$count]['attach_desc'],
                $context['mods'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request3);
            $smcFunc['db_free_result']($request3);

            if (empty($context['mods'][$count]['attach_desc'])) {
                $context['mods'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }

            // Check to see if the image is remote or not and build appropriate links
            if ($context['mods'][$count]['is_remote'] == 1) {
                $context['mods'][$count]['attach_location'] = urldecode($context['mods'][$count]['attach_file']);
            } else {
                $context['mods'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['mods'][$count]['attach_location'];
            }

            // If there is an image attached, link to it
            if (!empty($context['mods'][$count]['attach_desc'])) {
                $context['mods'][$count]['is_desc'] = "&lt;BR /&gt;" . $txt['smfg_description'] . ": " . $context['mods'][$count]['attach_desc'];
            } else {
                $context['mods'][$count]['is_desc'] = "";
            }
            if (isset($context['mods'][$count]['attach_location'])) {
                $context['mods'][$count]['image'] = "<a href=\"" . $context['mods'][$count]['attach_location'] . "\" rel=\"shadowbox[mods]\" title=\"" . garage_title_clean($context['mods'][$count]['title'] . ' :: ' . $context['mods'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
            }
        }
        $smcFunc['db_free_result']($request2);

        // Define spacer
        $context['mods'][$count]['spacer'] = '';

        if ($smfgSettings['enable_modification_video']) {

            // Check for video
            $request2 = $smcFunc['db_query']('', '
                    SELECT video_id
                    FROM {db_prefix}garage_video_gallery
                    WHERE vehicle_id = {int:vid}
                        AND type = "mod"
                        AND type_id = {int:mid}
                        ORDER BY video_id ASC
                        LIMIT 1',
                array(
                    'vid' => $veh_id,
                    'mid' => $context['mods'][$count]['id'],
                )
            );
            list($context['mods'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
            // Get video data if there is any
            $context['mods'][$count]['video'] = "";
            if (!empty($context['mods'][$count]['video_id'])) {
                $request3 = $smcFunc['db_query']('', '
                        SELECT url, title, video_desc
                        FROM {db_prefix}garage_video
                        WHERE id = {int:video_id}',
                    array(
                        'video_id' => $context['mods'][$count]['video_id'],
                    )
                );
                list($context['mods'][$count]['video_url'],
                    $context['mods'][$count]['video_title'],
                    $context['mods'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request3);
                $smcFunc['db_free_result']($request3);
                if (empty($context['mods'][$count]['video_desc'])) {
                    $context['mods'][$count]['video_desc'] = $txt['smfg_no_desc'];
                }
                $context['mods'][$count]['video_height'] = displayVideo($context['mods'][$count]['video_url'],
                    'height');
                $context['mods'][$count]['video_width'] = displayVideo($context['mods'][$count]['video_url'], 'width');
                $context['mods'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['mods'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['mods'][$count]['video_width'] . ";height=" . $context['mods'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['mods'][$count]['video_title'] . '</b> :: ' . $context['mods'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                if (!empty($context['mods'][$count]['image_id']) && !empty($context['mods'][$count]['video_id'])) {
                    $context['mods'][$count]['spacer'] = '&nbsp;';
                }
            }
            $smcFunc['db_free_result']($request2);

        }

        // Mods tooltip
        $context['mods'][$count]['mod_tooltip'] = garage_title_clean($context['mods'][$count]['title']) . " :: ";
        if (!empty($context['mods'][$count]['manufacturer'])) {
            $context['mods'][$count]['mod_tooltip'] .= "<b>" . $txt['smfg_manufacturer'] . ":</b> " . $context['mods'][$count]['manufacturer'];
        }
        if (!empty($context['mods'][$count]['category'])) {
            $context['mods'][$count]['mod_tooltip'] .= "<br /><b>" . $txt['smfg_category'] . ":</b> " . $context['mods'][$count]['category'];
        }

        $count++;
    }
    $smcFunc['db_free_result']($request);

    // Find the owner
    $request = $smcFunc['db_query']('', '
        SELECT real_name 
        FROM {db_prefix}members 
        WHERE id_member = {int:user_id}',
        array(
            'user_id' => $context['user_vehicles']['user_id'],
        )
    );
    list($context['user_vehicles']['memberName']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build the Page Title and Link Tree after the vehicle information has been obtained
    // if its their vehicle, build custom page title and breadcrumbs
    if ($context['user_vehicles']['user_id'] == $context['user']['id']) {

        // Your Vehicle
        $context['page_title'] = $txt['smfg_your'] . ' ' . $context['user_vehicles']['vehicle'];

        $context['linktree'][] = array(
            'url' => $scripturl . '?action=garage;sa=user_garage',
            'name' => $txt['smfg_my_vehicles']
        );

    } else { // not their vehicle

        // membername's vehicle
        $context['page_title'] = $context['user_vehicles']['memberName'] . '\'s ' . $context['user_vehicles']['vehicle'];

    }

    // show the vehicle name in the breadcrumbs, doesn't matter if its their vehicle or not
    // we only added 'my vehicles' if it was their vehicle so they could get back to their user garage
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'],
        'name' => $context['user_vehicles']['vehicle']
    );

    // Get Premiums if they exist
    $context['premiums'] = array();
    $request = $smcFunc['db_query']('', '
        SELECT p.id, p.business_id, p.premium, pt.title, p.comments, b.title, b.id
        FROM {db_prefix}garage_premiums AS p, {db_prefix}garage_business AS b, {db_prefix}garage_premium_types AS pt
        WHERE p.vehicle_id = {int:vid}
            AND p.business_id = b.id
            AND p.cover_type_id = pt.id',
        array(
            'vid' => $veh_id,
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['premiums'][$count]['id'],
            $context['premiums'][$count]['business_id'],
            $context['premiums'][$count]['premium'],
            $context['premiums'][$count]['cover_type'],
            $context['premiums'][$count]['comments'],
            $context['premiums'][$count]['insurer'],
            $context['premiums'][$count]['insurer_id']) = $row;
        $context['premiums'][$count]['premium'] = number_format($context['premiums'][$count]['premium'], 2, '.', ',');
        $count++;
    }

    // Get Dynoruns if they exist
    $context['dynoruns'] = array();
    $request = $smcFunc['db_query']('', '
        SELECT d.id,  d.bhp, d.bhp_unit, d.torque, d.torque_unit, d.boost, d.boost_unit, d.nitrous, d.peakpoint, b.title, b.id
        FROM {db_prefix}garage_dynoruns AS d, {db_prefix}garage_business AS b
        WHERE d.vehicle_id = {int:vid}
            AND d.dynocenter_id = b.id 
            AND d.pending != "1"
            AND b.pending != "1"
            ORDER BY d.date_created DESC',
        array(
            'vid' => $veh_id,
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['dynoruns'][$count]['id'],
            $context['dynoruns'][$count]['bhp'],
            $context['dynoruns'][$count]['bhp_unit'],
            $context['dynoruns'][$count]['torque'],
            $context['dynoruns'][$count]['torque_unit'],
            $context['dynoruns'][$count]['boost'],
            $context['dynoruns'][$count]['boost_unit'],
            $context['dynoruns'][$count]['nitrous'],
            $context['dynoruns'][$count]['peakpoint'],
            $context['dynoruns'][$count]['dynocenter'],
            $context['dynoruns'][$count]['dynocenter_id']) = $row;

        // Check to see if there is an image attached
        $request2 = $smcFunc['db_query']('', '
            SELECT image_id
            FROM {db_prefix}garage_dynoruns_gallery
            WHERE dynorun_id = {int:did}
                AND hilite = 1',
            array(
                'did' => $context['dynoruns'][$count]['id'],
            )
        );
        list($context['dynoruns'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
        $context['dynoruns'][$count]['image'] = "";
        // If there is not an image attached, dont go looking for extra info
        if (!empty($context['dynoruns'][$count]['image_id'])) {
            $request3 = $smcFunc['db_query']('', '
                SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                FROM {db_prefix}garage_images
                WHERE attach_id = {int:image_id}',
                array(
                    'image_id' => $context['dynoruns'][$count]['image_id'],
                )
            );
            list($context['dynoruns'][$count]['attach_location'],
                $context['dynoruns'][$count]['attach_ext'],
                $context['dynoruns'][$count]['attach_file'],
                $context['dynoruns'][$count]['attach_desc'],
                $context['dynoruns'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request3);
            $smcFunc['db_free_result']($request3);
            if (empty($context['dynoruns'][$count]['attach_desc'])) {
                $context['dynoruns'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }

            // Check to see if the image is remote or not and build appropriate links
            if ($context['dynoruns'][$count]['is_remote'] == 1) {
                $context['dynoruns'][$count]['attach_location'] = urldecode($context['dynoruns'][$count]['attach_file']);
            } else {
                $context['dynoruns'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['dynoruns'][$count]['attach_location'];
            }

            // If there is an image attached, link to it
            if (isset($context['dynoruns'][$count]['attach_location'])) {
                $context['dynoruns'][$count]['image'] = "<a href=\"" . $context['dynoruns'][$count]['attach_location'] . "\" rel=\"shadowbox[dynoruns]\" title=\"" . garage_title_clean($context['dynoruns'][$count]['bhp'] . ' ' . $context['dynoruns'][$count]['bhp_unit'] . ' :: ' . $context['dynoruns'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
            }
        }
        $smcFunc['db_free_result']($request2);

        // Define spacer
        $context['dynoruns'][$count]['spacer'] = '';

        if ($smfgSettings['enable_dynorun_video']) {

            // Check for video
            $request2 = $smcFunc['db_query']('', '
                SELECT video_id
                FROM {db_prefix}garage_video_gallery
                WHERE vehicle_id = {int:vid}
                    AND type = "dynorun"
                    AND type_id = {int:did}
                    ORDER BY video_id ASC
                    LIMIT 1',
                array(
                    'vid' => $veh_id,
                    'did' => $context['dynoruns'][$count]['id'],
                )
            );
            list($context['dynoruns'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
            // Get video data if there is any
            $context['dynoruns'][$count]['video'] = "";
            if (!empty($context['dynoruns'][$count]['video_id'])) {
                $request3 = $smcFunc['db_query']('', '
                    SELECT url, title, video_desc
                    FROM {db_prefix}garage_video
                    WHERE id = {int:video_id}',
                    array(
                        'video_id' => $context['dynoruns'][$count]['video_id'],
                    )
                );
                list($context['dynoruns'][$count]['video_url'],
                    $context['dynoruns'][$count]['video_title'],
                    $context['dynoruns'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request3);
                $smcFunc['db_free_result']($request3);
                if (empty($context['dynoruns'][$count]['video_desc'])) {
                    $context['dynoruns'][$count]['video_desc'] = $txt['smfg_no_desc'];
                }
                $context['dynoruns'][$count]['video_height'] = displayVideo($context['dynoruns'][$count]['video_url'],
                    'height');
                $context['dynoruns'][$count]['video_width'] = displayVideo($context['dynoruns'][$count]['video_url'],
                    'width');
                $context['dynoruns'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['dynoruns'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['dynoruns'][$count]['video_width'] . ";height=" . $context['dynoruns'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['dynoruns'][$count]['video_title'] . '</b> :: ' . $context['dynoruns'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                if (!empty($context['dynoruns'][$count]['image_id']) && !empty($context['dynoruns'][$count]['video_id'])) {
                    $context['dynoruns'][$count]['spacer'] = '&nbsp;';
                }
            }
            $smcFunc['db_free_result']($request2);

        }
        $count++;
    }
    $smcFunc['db_free_result']($request);

    // Get Quartermiles if they exist
    $context['qmiles'] = array();
    $request = $smcFunc['db_query']('', '
        SELECT q.id, q.rt, q.sixty, q.three, q.eighth, q.eighthmph, q.thou, q.quart, q.quartmph
        FROM {db_prefix}garage_quartermiles AS q
        WHERE q.vehicle_id = {int:vid}
            AND q.pending != "1"
            ORDER BY q.date_created DESC',
        array(
            'vid' => $_GET['VID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['qmiles'][$count]['id'],
            $context['qmiles'][$count]['rt'],
            $context['qmiles'][$count]['sixty'],
            $context['qmiles'][$count]['three'],
            $context['qmiles'][$count]['eighth'],
            $context['qmiles'][$count]['eighthmph'],
            $context['qmiles'][$count]['thou'],
            $context['qmiles'][$count]['quart'],
            $context['qmiles'][$count]['quartmph']) = $row;

        // Check to see if there is an image attached
        $request2 = $smcFunc['db_query']('', '
            SELECT image_id
            FROM {db_prefix}garage_quartermiles_gallery
            WHERE quartermile_id = {int:qid}
                AND hilite = 1',
            array(
                'qid' => $context['qmiles'][$count]['id'],
            )
        );
        list($context['qmiles'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
        $context['qmiles'][$count]['image'] = "";
        // If there is not an image attached, dont go looking for extra info
        if (!empty($context['qmiles'][$count]['image_id'])) {
            $request3 = $smcFunc['db_query']('', '
                SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                FROM {db_prefix}garage_images
                WHERE attach_id = {int:image_id}',
                array(
                    'image_id' => $context['qmiles'][$count]['image_id'],
                )
            );
            list($context['qmiles'][$count]['attach_location'],
                $context['qmiles'][$count]['attach_ext'],
                $context['qmiles'][$count]['attach_file'],
                $context['qmiles'][$count]['attach_desc'],
                $context['qmiles'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request3);
            $smcFunc['db_free_result']($request3);
            if (empty($context['qmiles'][$count]['attach_desc'])) {
                $context['qmiles'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }

            // Check to see if the image is remote or not and build appropriate links
            if ($context['qmiles'][$count]['is_remote'] == 1) {
                $context['qmiles'][$count]['attach_location'] = urldecode($context['qmiles'][$count]['attach_file']);
            } else {
                $context['qmiles'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['qmiles'][$count]['attach_location'];
            }

            // If there is an image attached, link to it
            if (isset($context['qmiles'][$count]['attach_location'])) {
                $context['qmiles'][$count]['image'] = "<a href=\"" . $context['qmiles'][$count]['attach_location'] . "\" rel=\"shadowbox[qmiles]\" title=\"" . garage_title_clean($context['qmiles'][$count]['quart'] . ' @ ' . $context['qmiles'][$count]['quartmph'] . ' :: ' . $context['qmiles'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
            }
        }
        $smcFunc['db_free_result']($request2);

        // Define spacer
        $context['qmiles'][$count]['spacer'] = '';

        if ($smfgSettings['enable_quartermile_video']) {

            // Check for video
            $request2 = $smcFunc['db_query']('', '
                SELECT video_id
                FROM {db_prefix}garage_video_gallery
                WHERE vehicle_id = {int:vid}
                    AND type = "qmile"
                    AND type_id = {int:qid}
                    ORDER BY video_id ASC
                    LIMIT 1',
                array(
                    'vid' => $veh_id,
                    'qid' => $context['qmiles'][$count]['id'],
                )
            );
            list($context['qmiles'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
            // Get video data if there is any
            $context['qmiles'][$count]['video'] = "";
            if (!empty($context['qmiles'][$count]['video_id'])) {
                $request3 = $smcFunc['db_query']('', '
                    SELECT url, title, video_desc
                    FROM {db_prefix}garage_video
                    WHERE id = {int:video_id}',
                    array(
                        'video_id' => $context['qmiles'][$count]['video_id'],
                    )
                );
                list($context['qmiles'][$count]['video_url'],
                    $context['qmiles'][$count]['video_title'],
                    $context['qmiles'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request3);
                $smcFunc['db_free_result']($request3);
                if (empty($context['qmiles'][$count]['video_desc'])) {
                    $context['qmiles'][$count]['video_desc'] = $txt['smfg_no_desc'];
                }
                $context['qmiles'][$count]['video_height'] = displayVideo($context['qmiles'][$count]['video_url'],
                    'height');
                $context['qmiles'][$count]['video_width'] = displayVideo($context['qmiles'][$count]['video_url'],
                    'width');
                $context['qmiles'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['qmiles'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['qmiles'][$count]['video_width'] . ";height=" . $context['qmiles'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['qmiles'][$count]['video_title'] . '</b> :: ' . $context['qmiles'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                if (!empty($context['qmiles'][$count]['image_id']) && !empty($context['qmiles'][$count]['video_id'])) {
                    $context['qmiles'][$count]['spacer'] = '&nbsp;';
                }
            }
            $smcFunc['db_free_result']($request2);

        }
        $count++;
    }
    $smcFunc['db_free_result']($request);

    // Get Laptimes if they exist
    $context['laps'] = array();
    $request = $smcFunc['db_query']('', '
        SELECT l.id, l.track_id, tc.title, lt.title, l.minute, l.second, l.millisecond, t.title
        FROM {db_prefix}garage_laps AS l, {db_prefix}garage_tracks AS t, {db_prefix}garage_track_conditions AS tc, {db_prefix}garage_lap_types AS lt 
        WHERE l.vehicle_id = {int:vid}
            AND l.track_id = t.id 
            AND l.condition_id = tc.id 
            AND l.type_id = lt.id
            AND l.pending != "1"
            AND t.pending != "1"',
        array(
            'vid' => $_GET['VID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['laps'][$count]['id'],
            $context['laps'][$count]['track_id'],
            $context['laps'][$count]['condition'],
            $context['laps'][$count]['type'],
            $context['laps'][$count]['minute'],
            $context['laps'][$count]['second'],
            $context['laps'][$count]['millisecond'],
            $context['laps'][$count]['track']) = $row;
        $context['laps'][$count]['time'] = $context['laps'][$count]['minute'] . ':' . $context['laps'][$count]['second'] . ':' . $context['laps'][$count]['millisecond'];

        // Check to see if there is an image attached
        $request2 = $smcFunc['db_query']('', '
            SELECT image_id
            FROM {db_prefix}garage_laps_gallery
            WHERE lap_id = {int:lid}
                AND hilite = 1',
            array(
                'lid' => $context['laps'][$count]['id'],
            )
        );
        list($context['laps'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
        $context['laps'][$count]['image'] = "";
        // If there is not an image attached, dont go looking for extra info
        if (!empty($context['laps'][$count]['image_id'])) {
            $request3 = $smcFunc['db_query']('', '
                SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                FROM {db_prefix}garage_images
                WHERE attach_id = {int:image_id}',
                array(
                    'image_id' => $context['laps'][$count]['image_id'],
                )
            );
            list($context['laps'][$count]['attach_location'],
                $context['laps'][$count]['attach_ext'],
                $context['laps'][$count]['attach_file'],
                $context['laps'][$count]['attach_desc'],
                $context['laps'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request3);
            $smcFunc['db_free_result']($request3);
            if (empty($context['laps'][$count]['attach_desc'])) {
                $context['laps'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }

            // Check to see if the image is remote or not and build appropriate links
            if ($context['laps'][$count]['is_remote'] == 1) {
                $context['laps'][$count]['attach_location'] = urldecode($context['laps'][$count]['attach_file']);
            } else {
                $context['laps'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['laps'][$count]['attach_location'];
            }

            // If there is an image attached, link to it
            if (isset($context['laps'][$count]['attach_location'])) {
                $context['laps'][$count]['image'] = "<a href=\"" . $context['laps'][$count]['attach_location'] . "\" rel=\"shadowbox[laps]\" title=\"" . garage_title_clean($context['laps'][$count]['time'] . ' @ ' . $context['laps'][$count]['track'] . ' :: ' . $context['laps'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
            }
        }
        $smcFunc['db_free_result']($request2);

        // Define spacer
        $context['laps'][$count]['spacer'] = '';

        if ($smfgSettings['enable_laptime_video']) {

            // Check for video
            $request2 = $smcFunc['db_query']('', '
                SELECT video_id
                FROM {db_prefix}garage_video_gallery
                WHERE vehicle_id = {int:vid}
                    AND type = "lap"
                    AND type_id = {int:lid}
                    ORDER BY video_id ASC
                    LIMIT 1',
                array(
                    'vid' => $veh_id,
                    'lid' => $context['laps'][$count]['id'],
                )
            );
            list($context['laps'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
            // Get video data if there is any
            $context['laps'][$count]['video'] = "";
            if (!empty($context['laps'][$count]['video_id'])) {
                $request3 = $smcFunc['db_query']('', '
                    SELECT url, title, video_desc
                    FROM {db_prefix}garage_video
                    WHERE id = {int:video_id}',
                    array(
                        'video_id' => $context['laps'][$count]['video_id'],
                    )
                );
                list($context['laps'][$count]['video_url'],
                    $context['laps'][$count]['video_title'],
                    $context['laps'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request3);
                $smcFunc['db_free_result']($request3);
                if (empty($context['laps'][$count]['video_desc'])) {
                    $context['laps'][$count]['video_desc'] = $txt['smfg_no_desc'];
                }
                $context['laps'][$count]['video_height'] = displayVideo($context['laps'][$count]['video_url'],
                    'height');
                $context['laps'][$count]['video_width'] = displayVideo($context['laps'][$count]['video_url'], 'width');
                $context['laps'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['laps'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['laps'][$count]['video_width'] . ";height=" . $context['laps'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['laps'][$count]['video_title'] . '</b> :: ' . $context['laps'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                if (!empty($context['laps'][$count]['image_id']) && !empty($context['laps'][$count]['video_id'])) {
                    $context['laps'][$count]['spacer'] = '&nbsp;';
                }
            }
            $smcFunc['db_free_result']($request2);

        }
        $count++;
    }
    $smcFunc['db_free_result']($request);

    // Get Services if they exist
    $context['services'] = array();
    $request = $smcFunc['db_query']('', '
        SELECT b.title, b.id, t.title, s.id, s.price, s.rating, s.mileage
        FROM {db_prefix}garage_business AS b, {db_prefix}garage_service_types AS t, {db_prefix}garage_service_history AS s
        WHERE s.vehicle_id = {int:vid}
            AND s.garage_id = b.id
            AND s.type_id = t.id
            AND b.pending != "1"',
        array(
            'vid' => $veh_id,
        )
    );
    $count = 0;
    //$context['services']['total_spent'] = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['services'][$count]['garage'],
            $context['services'][$count]['garage_id'],
            $context['services'][$count]['type'],
            $context['services'][$count]['id'],
            $context['services'][$count]['price'],
            $context['services'][$count]['rating'],
            $context['services'][$count]['mileage']) = $row;
        // Add the total spent each loop
        //$context['services']['total_spent'] += $context['services'][$count]['price'];
        $context['services'][$count]['price'] = number_format($context['services'][$count]['price'], 2, '.', ',');
        $context['services'][$count]['mileage'] = number_format($context['services'][$count]['mileage'], 0, '.', ',');
        $count++;
    }
    $smcFunc['db_free_result']($request);

    // BLOG PAGINATION: Get the total number of blog posts
    $context['blog'] = array();
    $request = $smcFunc['db_query']('', '
        SELECT count(*) 
        FROM {db_prefix}garage_blog
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $veh_id,
        )
    );
    list($context['blog']['total']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if (!isset($_GET['start'])) {
        $_GET['start'] = 0;
    }

    // Construct the page index
    $context['blog']['display'] = $smfgSettings['blogs_per_page'];
    $context['blog']['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=view_vehicle' . (isset($_REQUEST['VID']) ? ';VID=' . $_REQUEST['VID'] : ''),
        $_REQUEST['blog_start'], $context['blog']['total'], $context['blog']['display'], false);
    $context['blog']['start'] = $_REQUEST['blog_start'] + 1;
    $context['blog']['end'] = min($_REQUEST['blog_start'] + $context['blog']['display'], $context['blog']['total']);

    // Get any blog posts
    $request = $smcFunc['db_query']('', '
        SELECT id, blog_title, blog_text, post_date
        FROM {db_prefix}garage_blog
        WHERE vehicle_id = {int:vid}
        ORDER BY post_date DESC
        LIMIT {int:start}, {int:end}',
        array(
            'vid' => $_GET['VID'],
            'start' => $_REQUEST['blog_start'],
            'end' => $context['blog']['display'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['blog'][$count]['id'],
            $context['blog'][$count]['title'],
            $context['blog'][$count]['text'],
            $context['blog'][$count]['post_date']) = $row;
        $context['blog'][$count]['text'] = str_replace(array("\n", "{SEMICOLON}"), array("<br />", ";"),
            $context['blog'][$count]['text']);
        $context['blog'][$count]['title'] = str_replace("{SEMICOLON}", ";", $context['blog'][$count]['title']);
        // Is BBCode enabled?
        if ($smfgSettings['enable_blogs_bbcode']) {
            $context['blog'][$count]['text'] = parse_bbc($context['blog'][$count]['text']);
        }
        $count++;
    }
    $smcFunc['db_free_result']($request);

    // GUESTBOOK PAGINATION: Get the total number of guestbook posts
    $context['gb'] = array();
    $request = $smcFunc['db_query']('', '
        SELECT count(*) 
        FROM {db_prefix}garage_guestbooks
        WHERE vehicle_id = {int:vid}
            AND pending != "1"',
        array(
            'vid' => $veh_id,
        )
    );
    list($context['gb']['total']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Construct the page index
    $context['gb']['display'] = $smfgSettings['comments_per_page'];
    $context['gb']['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=view_vehicle' . (isset($_REQUEST['VID']) ? ';VID=' . $_REQUEST['VID'] : ''),
        $_REQUEST['gb_start'], $context['gb']['total'], $context['gb']['display'], false);
    $context['gb']['start'] = $_REQUEST['gb_start'] + 1;
    $context['gb']['end'] = min($_REQUEST['gb_start'] + $context['gb']['display'], $context['gb']['total']);

    // Get comment data
    $request = $smcFunc['db_query']('', '
        SELECT u.real_name, u.date_registered, u.posts, gb.id,  gb.author_id, gb.post_date, gb.ip_address, gb.post
        FROM {db_prefix}garage_guestbooks AS gb, {db_prefix}members AS u
        WHERE gb.vehicle_id = {int:vid}
            AND gb.author_id = u.id_member
            AND gb.pending != "1"
            ORDER BY gb.post_date DESC
            LIMIT {int:start}, {int:end}',
        array(
            'vid' => $_GET['VID'],
            'start' => $_REQUEST['gb_start'],
            'end' => $context['gb']['display'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['gb'][$count]['author'],
            $context['gb'][$count]['date_reg'],
            $context['gb'][$count]['posts'],
            $context['gb'][$count]['CID'],
            $context['gb'][$count]['author_id'],
            $context['gb'][$count]['post_date'],
            $context['gb'][$count]['author_ip'],
            $context['gb'][$count]['comment']) = $row;
        $context['gb'][$count]['comment'] = str_replace(array("\n", "{SEMICOLON}"), array("<br />", ";"),
            $context['gb'][$count]['comment']);
        $context['gb'][$count]['posts'] = number_format($context['gb'][$count]['posts']);
        // Is BBCode enabled?
        if ($smfgSettings['enable_guestbooks_bbcode']) {
            $context['gb'][$count]['comment'] = parse_bbc($context['gb'][$count]['comment']);
        }
        // Check if author has a vehicle
        $request2 = $smcFunc['db_query']('', '
           SELECT id
           FROM {db_prefix}garage_vehicles
           WHERE user_id = {int:author_id}
               AND main_vehicle = 1',
            array(
                'author_id' => $context['gb'][$count]['author_id'],
            )
        );
        list($context['gb'][$count]['author_VID']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);
        // If there is a vehicle, get its title
        if (isset($context['gb'][$count]['author_VID'])) {
            $request2 = $smcFunc['db_query']('', '
               SELECT v.made_year, mk.make, md.model
               FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
               WHERE user_id = {int:author_id}
                   AND v.main_vehicle = 1
                   AND v.make_id = mk.id
                   AND v.model_id = md.id',
                array(
                    'author_id' => $context['gb'][$count]['author_id'],
                )
            );
            list($context['gb'][$count]['made_year'],
                $context['gb'][$count]['make'],
                $context['gb'][$count]['model']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);
            $context['gb'][$count]['author_vehicle'] = $context['gb'][$count]['made_year'] . ' ' . $context['gb'][$count]['make'] . ' ' . $context['gb'][$count]['model'];
        } else {
            $context['gb'][$count]['author_vehicle'] = "";
        }

        $count++;
    }
    $smcFunc['db_free_result']($request);

    // Add the mod and service totals spent
    //if(!isset($context['mods']['total_spent'])) $context['mods']['total_spent'] = 0;
    //if(!isset($context['services']['total_spent'])) $context['services']['total_spent'] = 0;
    //$context['user_vehicles']['total_spent'] = $context['mods']['total_spent'] + $context['services']['total_spent'];
    //$context['user_vehicles']['total_spent'] = number_format($context['user_vehicles']['total_spent'], 2, '.', ',');

    if ($smfgSettings['rating_system'] == 0) {
        $ratingfunc = "SUM";
    } else {
        if ($smfgSettings['rating_system'] == 1) {
            $ratingfunc = "AVG";
        }
    }

    // Get vehicle rating
    $request = $smcFunc['db_query']('', '
        SELECT ' . $ratingfunc . '( rating ), COUNT( id ) * 10
        FROM {db_prefix}garage_ratings
        WHERE vehicle_id = {int:vid}
        GROUP BY vehicle_id',
        array(
            'vid' => $veh_id,
        )
    );
    list($context['user_vehicles']['rating'],
        $context['user_vehicles']['poss_rating']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($context['user_vehicles']['rating'] > 0) {
        $context['user_vehicles']['rating'] = number_format($context['user_vehicles']['rating'], 2, '.', ',');
    } else {
        $context['user_vehicles']['rating'] = 0;
    }

    // Check if the user has already rated this vehicle
    $request = $smcFunc['db_query']('', '
        SELECT id, rating, rate_date
        FROM {db_prefix}garage_ratings
        WHERE vehicle_id = {int:vid}
            AND user_id = {int:uid}',
        array(
            'vid' => $veh_id,
            'uid' => $context['user']['id'],
        )
    );
    list($context['user_vehicles']['rid'],
        $context['user_vehicles']['veh_rating'],
        $context['user_vehicles']['rate_date']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Get the user's VID to ensure they don't rate their own vehicle
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_vehicles
        WHERE user_id = {int:uid}',
        array(
            'uid' => $context['user']['id'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['user'][$count]['vid']) = $row;
        $count++;
    }
    $smcFunc['db_free_result']($request);

    // Increase the number of views by one...if they have not been here before that is :D
    $client_ip = $_SERVER["REMOTE_ADDR"];
    $request = $smcFunc['db_query']('', '
        SELECT *
        FROM {db_prefix}garage_views
        WHERE vid = {int:vid}
            AND ip = {string:client_ip}',
        array(
            'vid' => $context['user_vehicles']['id'],
            'client_ip' => $client_ip,
        )
    );
    $num_rows = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    if ($num_rows == 0) {
        // Insert record and increment
        $updated_views = $context['user_vehicles']['views'] + 1;
        $request = $smcFunc['db_query']('', '
            UPDATE {db_prefix}garage_vehicles
            SET views = {int:updated_views}
            WHERE id = {int:vid}',
            array(
                'updated_views' => $updated_views,
                'vid' => $veh_id,
            )
        );
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_views',
            array(
                'vid' => 'int',
                'ip' => 'string',
            ),
            array(
                $context['user_vehicles']['id'],
                $client_ip,
            ),
            array(// no data
            )
        );
    }

    // Set the views number format AFTER we add to the view count
    $context['user_vehicles']['views'] = number_format($context['user_vehicles']['views'], 0, '.', ',');

}

// Edit Vehicle
function G_Edit_Vehicle()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 1;

    $context['sub_template'] = 'edit_vehicle';
    $context['date_format'] = $smfgSettings['dateformat'];
    $context['max_image_bytes'] = $smfgSettings['max_image_kbytes'] * 1024;
    $veh_id = $_GET['VID'];

    // Validate Owner
    checkOwner($_GET['VID']);

    // Session variable set?
    if (!isset($_SESSION['added_make'])) {
        $_SESSION['added_make'] = 0;
    }
    if (!isset($_SESSION['added_model'])) {
        $_SESSION['added_model'] = 0;
    }

    // Select Vehicle
    $request = $smcFunc['db_query']('', '
        SELECT id, user_id, made_year, engine_type, color, mileage, mileage_unit, price, currency, comments, views, date_created, date_updated, make_id, model_id, main_vehicle
        FROM {db_prefix}garage_vehicles
        WHERE id = {int:vid}',
        array(
            'vid' => $veh_id,
        )
    );
    list ($context['user_vehicles']['id'],
        $context['user_vehicles']['user_id'],
        $context['user_vehicles']['made_year'],
        $context['user_vehicles']['engine_type'],
        $context['user_vehicles']['color'],
        $context['user_vehicles']['mileage'],
        $context['user_vehicles']['mileage_unit'],
        $context['user_vehicles']['price'],
        $context['user_vehicles']['currency'],
        $context['user_vehicles']['comments'],
        $context['user_vehicles']['views'],
        $context['user_vehicles']['date_created'],
        $context['user_vehicles']['date_updated'],
        $context['user_vehicles']['make_id'],
        $context['user_vehicles']['model_id'],
        $context['user_vehicles']['main_vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Select Make
    $request = $smcFunc['db_query']('', '
        SELECT make 
        FROM {db_prefix}garage_makes 
        WHERE id = {int:make_id}',
        array(
            'make_id' => $context['user_vehicles']['make_id'],
        )
    );
    list ($context['user_vehicles']['make']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Select Model
    $request = $smcFunc['db_query']('', '
        SELECT model 
        FROM {db_prefix}garage_models 
        WHERE id = {int:model_id}',
        array(
            'model_id' => $context['user_vehicles']['model_id'],
        )
    );
    list($context['user_vehicles']['model']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build the Page Title and Link Tree after the vehicle information has been obtained
    $context['page_title'] = $context['user_vehicles']['made_year'] . ' ' . $context['user_vehicles']['make'] . ' ' . $context['user_vehicles']['model'] . ' &gt; ' . $txt['smfg_edit_vehicle'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'],
        'name' => $context['user_vehicles']['made_year'] . ' ' . $context['user_vehicles']['make'] . ' ' . $context['user_vehicles']['model']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=edit_vehicle;VID=' . $context['user_vehicles']['id'],
        'name' => $txt['smfg_edit_vehicle']
    );

    // Select image id
    $request = $smcFunc['db_query']('', '
        SELECT image_id, hilite 
        FROM {db_prefix}garage_vehicles_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $veh_id,
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['user_vehicles'][$count]['image_id'],
            $context['user_vehicles'][$count]['hilite']) = $row;

        // Select image data if there is any
        if (!empty($context['user_vehicles'][$count]['image_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT attach_location, attach_hits, attach_ext, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_is_image, attach_date, attach_filesize, attach_thumb_filesize, attach_desc, is_remote
                FROM {db_prefix}garage_images 
                WHERE attach_id = {int:image_id}',
                array(
                    'image_id' => $context['user_vehicles'][$count]['image_id'],
                )
            );
            list($context['user_vehicles'][$count]['attach_location'],
                $context['user_vehicles'][$count]['attach_hits'],
                $context['user_vehicles'][$count]['attach_ext'],
                $context['user_vehicles'][$count]['attach_file'],
                $context['user_vehicles'][$count]['attach_thumb_location'],
                $context['user_vehicles'][$count]['attach_thumb_width'],
                $context['user_vehicles'][$count]['attach_thumb_height'],
                $context['user_vehicles'][$count]['attach_is_image'],
                $context['user_vehicles'][$count]['attach_date'],
                $context['user_vehicles'][$count]['attach_filesize'],
                $context['user_vehicles'][$count]['attach_thumb_filesize'],
                $context['user_vehicles'][$count]['attach_desc'],
                $context['user_vehicles'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);

            if (empty($context['user_vehicles'][$count]['attach_desc'])) {
                $context['user_vehicles'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }
            if ($context['user_vehicles'][$count]['hilite'] == 1) {
                if ($context['user_vehicles'][$count]['is_remote'] == 1) {
                    $context['hilite_image_location'] = urldecode($context['user_vehicles'][$count]['attach_file']);
                } else {
                    $context['hilite_image_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['user_vehicles'][$count]['attach_location'];
                }
                $context['hilite_thumb_location'] = $context['user_vehicles'][$count]['attach_thumb_location'];
                $context['hilite_thumb_width'] = $context['user_vehicles'][$count]['attach_thumb_width'];
                $context['hilite_thumb_height'] = $context['user_vehicles'][$count]['attach_thumb_height'];
                $context['hilite_desc'] = $context['user_vehicles'][$count]['attach_desc'];
            }
            if ($context['user_vehicles'][$count]['is_remote'] == 1) {
                $context['user_vehicles'][$count]['attach_location'] = urldecode($context['user_vehicles'][$count]['attach_file']);
            } else {
                $context['user_vehicles'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['user_vehicles'][$count]['attach_location'];
            }
            $count++;
        }
    }
    $smcFunc['db_free_result']($request);

    if ($context['user_vehicles']['mileage_unit'] == "Miles") {
        $context['miles'] = " selected=\"selected\"";
    } else {
        if ($context['user_vehicles']['mileage_unit'] == "Kilometers") {
            $context['kilometers'] = " selected=\"selected\"";
        }
    }

    if (!isset($context['miles'])) {
        $context['miles'] = "";
    }
    if (!isset($context['kilometers'])) {
        $context['kilometers'] = "";
    }

    // Select video id
    $request = $smcFunc['db_query']('', '
        SELECT video_id
        FROM {db_prefix}garage_video_gallery
        WHERE vehicle_id = {int:vid}
            AND type = "vehicle"',
        array(
            'vid' => $veh_id,
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['user_vehicles'][$count]['video_id']) = $row;

        // Select video data if there is any
        if (!empty($context['user_vehicles'][$count]['video_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT url, title, video_desc
                FROM {db_prefix}garage_video 
                WHERE id = {int:video_id}',
                array(
                    'video_id' => $context['user_vehicles'][$count]['video_id'],
                )
            );
            list($context['user_vehicles'][$count]['video_url'],
                $context['user_vehicles'][$count]['video_title'],
                $context['user_vehicles'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);
            if (empty($context['user_vehicles'][$count]['video_desc'])) {
                $context['user_vehicles'][$count]['video_desc'] = $txt['smfg_no_desc'];
            }
            $context['user_vehicles'][$count]['video_thumb'] = displayVideo($context['user_vehicles'][$count]['video_url'],
                2);
            $context['user_vehicles'][$count]['video_height'] = displayVideo($context['user_vehicles'][$count]['video_url'],
                'height');
            $context['user_vehicles'][$count]['video_width'] = displayVideo($context['user_vehicles'][$count]['video_url'],
                'width');
        }
        $count++;
    }

}

// Update Vehicle
function G_Update_Vehicle()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';
    $date_updated = time();

    // Validate Session
    checkSession();

    // Clean up the mess
    if (empty($_POST['engine_type'])) {
        $_POST['engine_type'] = 0;
    }
    if (!empty($_POST['color'])) {
        if (!strpos($_POST['color'], '/')) {
            $_POST['color'] = ucwords($_POST['color']);
        } else {
            $pieces = explode('/', $_POST['color']);
            $count = 0;
            foreach ($pieces AS $piece) {
                $pieced[$count] = ucwords($piece);
                $count++;
            }
            $_POST['color'] = implode('/', $pieced);
        }
    }

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_vehicles 
        SET made_year = {int:made_year}, engine_type = {int:engine_type}, color = {string:color}, mileage = {int:mileage}, mileage_unit = {string:mileage_unit}, price = {int:price}, currency = {int:currency}, comments = {string:comments}, date_updated = {int:date_updated}, make_id = {int:make_id}, model_id = {int:model_id}
        WHERE id = {int:vid}',
        array(
            'made_year' => $_POST['made_year'],
            'engine_type' => $_POST['engine_type'],
            'color' => $_POST['color'],
            'mileage' => $_POST['mileage'],
            'mileage_unit' => $_POST['mileage_unit'],
            'price' => $_POST['price'],
            'currency' => $_POST['currency'],
            'comments' => $_POST['comments'],
            'date_updated' => $date_updated,
            'make_id' => $_POST['make_id'],
            'model_id' => $_POST['model_id'],
            'vid' => $_POST['VID'],
        )
    );

    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Delete Vehicle
function G_Delete_Vehicle()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boarddir;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Set image directory
    $dir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $cachedir = $dir . 'cache/';

    // Get vehicle image IDs
    $request = $smcFunc['db_query']('', '
        SELECT image_id
        FROM {db_prefix}garage_vehicles_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );
    while ($row = $smcFunc['db_fetch_row']($request)) {
        $images['id'] = $row[0];
        // Get image filenames
        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_thumb_location
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            $images['filename'] = $row2[0];
            $images['thumb_filename'] = $row2[1];
            // Destroy the images
            unlink($dir . $images['filename']);
            unlink($cachedir . $images['filename']);
            unlink($cachedir . $images['thumb_filename']);
        }
        $smcFunc['db_free_result']($request2);
    }
    $smcFunc['db_free_result']($request);

    // Get modification image IDs
    $request = $smcFunc['db_query']('', '
        SELECT image_id
        FROM {db_prefix}garage_modifications_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );
    while ($row = $smcFunc['db_fetch_row']($request)) {
        $images['id'] = $row[0];
        // Get image filenames
        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_thumb_location
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            $images['filename'] = $row2[0];
            $images['thumb_filename'] = $row2[1];
            // Destroy the images
            unlink($dir . $images['filename']);
            unlink($cachedir . $images['filename']);
            unlink($cachedir . $images['thumb_filename']);
        }
        $smcFunc['db_free_result']($request2);
    }
    $smcFunc['db_free_result']($request);

    // Get quartermile image IDs
    $request = $smcFunc['db_query']('', '
        SELECT image_id
        FROM {db_prefix}garage_quartermiles_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );
    while ($row = $smcFunc['db_fetch_row']($request)) {
        $images['id'] = $row[0];
        // Get image filenames
        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_thumb_location
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            $images['filename'] = $row2[0];
            $images['thumb_filename'] = $row2[1];
            // Destroy the images
            unlink($dir . $images['filename']);
            unlink($cachedir . $images['filename']);
            unlink($cachedir . $images['thumb_filename']);
        }
        $smcFunc['db_free_result']($request2);
    }
    $smcFunc['db_free_result']($request);

    // Get dynorun image IDs
    $request = $smcFunc['db_query']('', '
        SELECT image_id
        FROM {db_prefix}garage_dynoruns_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );
    while ($row = $smcFunc['db_fetch_row']($request)) {
        $images['id'] = $row[0];
        // Get image filenames
        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_thumb_location
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            $images['filename'] = $row2[0];
            $images['thumb_filename'] = $row2[1];
            // Destroy the images
            unlink($dir . $images['filename']);
            unlink($cachedir . $images['filename']);
            unlink($cachedir . $images['thumb_filename']);
        }
        $smcFunc['db_free_result']($request2);
    }
    $smcFunc['db_free_result']($request);

    // Get lap image IDs
    $request = $smcFunc['db_query']('', '
        SELECT image_id
        FROM {db_prefix}garage_laps_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );
    while ($row = $smcFunc['db_fetch_row']($request)) {
        $images['id'] = $row[0];
        // Get image filenames
        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_thumb_location
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            $images['filename'] = $row2[0];
            $images['thumb_filename'] = $row2[1];
            // Destroy the images
            unlink($dir . $images['filename']);
            unlink($cachedir . $images['filename']);
            unlink($cachedir . $images['thumb_filename']);
        }
        $smcFunc['db_free_result']($request2);
    }
    $smcFunc['db_free_result']($request);

    // Delete rows from garage_images
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_images
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete rows from vehicles_gallery
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_vehicles_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete rows from modifications_gallery
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_modifications_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete modifications
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_modifications
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete rows from quartermiles_gallery
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_quartermiles_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete quartermiles
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_quartermiles
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete rows from dynrouns_gallery
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_dynoruns_gallery 
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete dynrouns
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_dynoruns
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete rows from laps_gallery
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_laps_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete laps
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_laps
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete blogs
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_blog
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete guestbooks
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_guestbooks
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete premiums
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_premiums
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete ratings
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_ratings
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete service_history
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_service_history
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // Delete the vehicle
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_vehicles
        WHERE id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    // ...and send them on their way
    if (isset($_GET['ug'])) {
        //header('Location: '.$scripturl.'?action=garage;sa=user_garage');
        $newurl = $_POST['redirecturl'];
        header('Location: ' . $newurl);
    } else {
        header('Location: ' . $_SESSION['old_url']);
    }

}

// Add Modification
function G_Add_Modification()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 1;

    $context['sub_template'] = 'add_modification';
    $context['max_image_bytes'] = $smfgSettings['max_image_kbytes'] * 1024;

    // Validate Owner
    checkOwner($_GET['VID']);

    // Session variable set?
    if (!isset($_SESSION['added_man'])) {
        $_SESSION['added_man'] = 0;
    }
    if (!isset($_SESSION['added_product'])) {
        $_SESSION['added_product'] = 0;
    }
    if (!isset($_SESSION['added_shop'])) {
        $_SESSION['added_shop'] = 0;
    }
    if (!isset($_SESSION['added_garage'])) {
        $_SESSION['added_garage'] = 0;
    }

    // Make sure this module is enabled
    if (!$smfgSettings['enable_modification']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build link tree
    $context['page_title'] = $txt['smfg_add_modification'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['page_title'] = $txt['smfg_add_modification'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=add_modification;VID=' . $_GET['VID'],
        'name' => &$txt['smfg_add_modification']
    );

}

// Insert Modification
function G_Insert_Modification()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_modification']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Check if approval is required
    if ($smfgSettings['enable_modification_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    $context['date_created'] = time();
    $context['vehicle_id'] = $_POST['VID'];

    // Get the owner ID
    $request = $smcFunc['db_query']('', '
        SELECT user_id
        FROM {db_prefix}garage_vehicles
        WHERE id = {int:vid}',
        array(
            'vid' => $context['vehicle_id'],
        )
    );
    list($context['owner_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Did they enter prices or leave em blank?
    if (empty($_POST['price'])) {
        $_POST['price'] = 0;
    }
    if (empty($_POST['install_price'])) {
        $_POST['install_price'] = 0;
    }

    if (empty($_POST['shop_id'])) {
        $_POST['shop_id'] = 0;
    }
    if (empty($_POST['installer_id'])) {
        $_POST['installer_id'] = 0;
    }

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_modification_images']) {

        // Check maximum file size from MAX_FILE_SIZE
        if ($_FILES['FILE_UPLOAD']['error'] == 2) {
            loadLanguage('Errors');
            fatal_lang_error('garage_filesize_error', false);
        }

        // If they provided an image to upload use it, otherwise use the remote image
        // handle_images($gallery, $source(0=local,1=remote), $file, $extra)
        if ($_FILES['FILE_UPLOAD']['error'] == 0) {

            // Check for maximum file size allowed...again, just in case they made it this far
            $max_fsize = $smfgSettings['max_image_kbytes'] * 1024;
            if ($_FILES['FILE_UPLOAD']['size'] > $max_fsize) {
                loadLanguage('Errors');
                fatal_lang_error('garage_filesize_error', false);
            }

            // Check for maximum image resolution
            $dimensions = getimagesize($_FILES['FILE_UPLOAD']['tmp_name']) or die('Could not obtain image dimensions.');
            if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                loadLanguage('Errors');
                fatal_lang_error('garage_resolution_error', false);
            }

            // If all the image restrictions were met, go ahead and insert the modification data
            $request = $smcFunc['db_insert']('insert',
                '{db_prefix}garage_modifications',
                array(
                    'vehicle_id' => 'int',
                    'user_id' => 'int',
                    'category_id' => 'int',
                    'manufacturer_id' => 'int',
                    'product_id' => 'int',
                    'price' => 'string',
                    'install_price' => 'string',
                    'product_rating' => 'int',
                    'purchase_rating' => 'int',
                    'install_rating' => 'int',
                    'shop_id' => 'int',
                    'installer_id' => 'int',
                    'comments' => 'string',
                    'install_comments' => 'string',
                    'date_created' => 'int',
                    'date_updated' => 'int',
                    'pending' => 'string',
                ),
                array(
                    $context['vehicle_id'],
                    $context['owner_id'],
                    $_POST['category_id'],
                    $_POST['manufacturer_id'],
                    $_POST['product_id'],
                    $_POST['price'],
                    $_POST['install_price'],
                    $_POST['product_rating'],
                    $_POST['purchase_rating'],
                    $_POST['install_rating'],
                    $_POST['shop_id'],
                    $_POST['installer_id'],
                    $_POST['comments'],
                    $_POST['install_comments'],
                    $context['date_created'],
                    $context['date_created'],
                    $pending,
                ),
                array(
                    'id'
                )
            );
            $context['mod_id'] = $smcFunc['db_insert_id']($request);

            // If they made it this far, go ahead and process the image
            handle_images("mod", 0, $_FILES['FILE_UPLOAD'], $_POST);

            // Insert table data for modifications_gallery
            $request = $smcFunc['db_insert']('insert',
                '{db_prefix}garage_modifications_gallery',
                array(
                    'vehicle_id' => 'int',
                    'modification_id' => 'int',
                    'image_id' => 'int',
                    'hilite' => 'int',
                ),
                array(
                    $context['vehicle_id'],
                    $context['mod_id'],
                    $context['image_id'],
                    1,
                ),
                array(
                    'vehicle_id',
                    'modification_id',
                    'image_id'
                )
            );

        } // Check if a remote image was supplied...then use it
        else {
            if ($_POST['url_image'] !== 'http://' && $_POST['url_image'] !== 'https://') {

                // Check for maximum image resolution
                $dimensions = getimagesize_remote($_POST['url_image']) or die('Could not obtain remote image dimensions.');
                if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                    loadLanguage('Errors');
                    fatal_lang_error('garage_resolution_error', false);
                }

                // If all the image restrictions were met, go ahead and insert the modification data
                $request = $smcFunc['db_insert']('insert',
                    '{db_prefix}garage_modifications',
                    array(
                        'vehicle_id' => 'int',
                        'user_id' => 'int',
                        'category_id' => 'int',
                        'manufacturer_id' => 'int',
                        'product_id' => 'int',
                        'price' => 'string',
                        'install_price' => 'string',
                        'product_rating' => 'int',
                        'purchase_rating' => 'int',
                        'install_rating' => 'int',
                        'shop_id' => 'int',
                        'installer_id' => 'int',
                        'comments' => 'string',
                        'install_comments' => 'string',
                        'date_created' => 'int',
                        'date_updated' => 'int',
                        'pending' => 'string',
                    ),
                    array(
                        $context['vehicle_id'],
                        $context['owner_id'],
                        $_POST['category_id'],
                        $_POST['manufacturer_id'],
                        $_POST['product_id'],
                        $_POST['price'],
                        $_POST['install_price'],
                        $_POST['product_rating'],
                        $_POST['purchase_rating'],
                        $_POST['install_rating'],
                        $_POST['shop_id'],
                        $_POST['installer_id'],
                        $_POST['comments'],
                        $_POST['install_comments'],
                        $context['date_created'],
                        $context['date_created'],
                        $pending,
                    ),
                    array(// no data
                    )
                );
                $context['mod_id'] = $smcFunc['db_insert_id']($request);

                // If they made it this far, go ahead and process the image
                handle_images("mod", 1, $_POST);

                // Insert table data for modifications_gallery
                $request = $smcFunc['db_insert']('insert',
                    '{db_prefix}garage_modifications_gallery',
                    array(
                        'vehicle_id' => 'int',
                        'modification_id' => 'int',
                        'image_id' => 'int',
                        'hilite' => 'int',
                    ),
                    array(
                        $context['vehicle_id'],
                        $context['mod_id'],
                        $context['image_id'],
                        1,
                    ),
                    array(// no data
                    )
                );

            }
        }

    }

    // If modification images are disabled or no image was provided, we still need to insert the data
    if ($smfgSettings['enable_modification_images'] != 1 || $_FILES['FILE_UPLOAD']['error'] == 4 && ($_POST['url_image'] === 'http://' || $_POST['url_image'] === 'https://')) {

        // Insert modification
        $request = $smcFunc['db_insert']('',
            '{db_prefix}garage_modifications',
            array(
                'vehicle_id' => 'int',
                'user_id' => 'int',
                'category_id' => 'int',
                'manufacturer_id' => 'int',
                'product_id' => 'int',
                'price' => 'string',
                'install_price' => 'string',
                'product_rating' => 'int',
                'purchase_rating' => 'int',
                'install_rating' => 'int',
                'shop_id' => 'int',
                'installer_id' => 'int',
                'comments' => 'string',
                'install_comments' => 'string',
                'date_created' => 'int',
                'date_updated' => 'int',
                'pending' => 'string',
            ),
            array(
                'vid' => $context['vehicle_id'],
                $context['owner_id'],
                $_POST['category_id'],
                $_POST['manufacturer_id'],
                $_POST['product_id'],
                $_POST['price'],
                $_POST['install_price'],
                $_POST['product_rating'],
                $_POST['purchase_rating'],
                $_POST['install_rating'],
                $_POST['shop_id'],
                $_POST['installer_id'],
                $_POST['comments'],
                $_POST['install_comments'],
                $context['date_created'],
                $context['date_created'],
                $pending,
            ),
            array(// no data
            )
        );
        $context['mod_id'] = $smcFunc['db_insert_id']($request);

    }

    // Perform video actions if enabled
    if ($smfgSettings['enable_modification_video'] && $_POST['video_url'] > 'http://') {

        // Check for video title
        if (empty($_POST['video_title'])) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_title_required', false);
        }

        // Check for video URL compatibility
        if (!displayVideo($_POST['video_url'], 4)) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_unsupported', false);
        }

        // Insert video
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video',
            array(
                'vehicle_id' => 'int',
                'url' => 'string',
                'title' => 'string',
                'video_desc' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['video_url'],
                $_POST['video_title'],
                $_POST['video_desc'],
            ),
            array(// no data
            )
        );
        $context['video_id'] = $smcFunc['db_insert_id']($request);

        // Insert table data for video_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video_gallery',
            array(
                'vehicle_id' => 'int',
                'video_id' => 'int',
                'type' => 'string',
                'type_id' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['video_id'],
                'mod',
                $context['mod_id'],
            ),
            array(// no data
            )
        );

    }

    // Send out Notifications
    if ($smfgSettings['enable_modification_approval']) {
        sendGarageNotifications();
    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=view_own_vehicle;VID='.$_POST['VID'].'#modifications' );
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Edit Modification
function G_Edit_Modification()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 1;

    $context['sub_template'] = 'edit_modification';
    $context['max_image_bytes'] = $smfgSettings['max_image_kbytes'] * 1024;

    // Validate Owner
    checkOwner($_GET['VID']);

    // Session variable set?
    if (!isset($_SESSION['added_man'])) {
        $_SESSION['added_man'] = 0;
    }
    if (!isset($_SESSION['added_product'])) {
        $_SESSION['added_product'] = 0;
    }
    if (!isset($_SESSION['added_shop'])) {
        $_SESSION['added_shop'] = 0;
    }
    if (!isset($_SESSION['added_garage'])) {
        $_SESSION['added_garage'] = 0;
    }

    // Make sure this module is enabled
    if (!$smfgSettings['enable_modification']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build the link tree
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['page_title'] = $context['user_vehicles']['vehicle'] . ' &gt; ' . $txt['smfg_edit_modification'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=edit_modification;VID=' . $_GET['VID'] . ';MID=' . $_GET['MID'] . '',
        'name' => &$txt['smfg_edit_modification']
    );

    $request = $smcFunc['db_query']('', '
        SELECT vehicle_id, category_id, manufacturer_id, product_id, price, install_price, product_rating, purchase_rating, install_rating, shop_id, installer_id, comments, install_comments
        FROM {db_prefix}garage_modifications
        WHERE id = {int:mid}',
        array(
            'mid' => $_GET['MID'],
        )
    );
    list($context['mods']['vehicle_id'],
        $context['mods']['category_id'],
        $context['mods']['manufacturer_id'],
        $context['mods']['product_id'],
        $context['mods']['price'],
        $context['mods']['install_price'],
        $context['mods']['product_rating'],
        $context['mods']['purchase_rating'],
        $context['mods']['install_rating'],
        $context['mods']['shop_id'],
        $context['mods']['installer_id'],
        $context['mods']['comments'],
        $context['mods']['install_comments']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($context['mods']['product_rating'] == 10) {
        $context['prod_rat_10'] = "selected=\"selected\"";
    } else {
        if ($context['mods']['product_rating'] == 9) {
            $context['prod_rat_9'] = "selected=\"selected\"";
        } else {
            if ($context['mods']['product_rating'] == 8) {
                $context['prod_rat_8'] = "selected=\"selected\"";
            } else {
                if ($context['mods']['product_rating'] == 7) {
                    $context['prod_rat_7'] = "selected=\"selected\"";
                } else {
                    if ($context['mods']['product_rating'] == 6) {
                        $context['prod_rat_6'] = "selected=\"selected\"";
                    } else {
                        if ($context['mods']['product_rating'] == 5) {
                            $context['prod_rat_5'] = "selected=\"selected\"";
                        } else {
                            if ($context['mods']['product_rating'] == 4) {
                                $context['prod_rat_4'] = "selected=\"selected\"";
                            } else {
                                if ($context['mods']['product_rating'] == 3) {
                                    $context['prod_rat_3'] = "selected=\"selected\"";
                                } else {
                                    if ($context['mods']['product_rating'] == 2) {
                                        $context['prod_rat_2'] = "selected=\"selected\"";
                                    } else {
                                        if ($context['mods']['product_rating'] == 1) {
                                            $context['prod_rat_1'] = "selected=\"selected\"";
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

    if (!isset($context['prod_rat_10'])) {
        $context['prod_rat_10'] = "";
    }
    if (!isset($context['prod_rat_9'])) {
        $context['prod_rat_9'] = "";
    }
    if (!isset($context['prod_rat_8'])) {
        $context['prod_rat_8'] = "";
    }
    if (!isset($context['prod_rat_7'])) {
        $context['prod_rat_7'] = "";
    }
    if (!isset($context['prod_rat_6'])) {
        $context['prod_rat_6'] = "";
    }
    if (!isset($context['prod_rat_5'])) {
        $context['prod_rat_5'] = "";
    }
    if (!isset($context['prod_rat_4'])) {
        $context['prod_rat_4'] = "";
    }
    if (!isset($context['prod_rat_3'])) {
        $context['prod_rat_3'] = "";
    }
    if (!isset($context['prod_rat_2'])) {
        $context['prod_rat_2'] = "";
    }
    if (!isset($context['prod_rat_1'])) {
        $context['prod_rat_1'] = "";
    }

    if ($context['mods']['purchase_rating'] == 10) {
        $context['purch_rat_10'] = "selected=\"selected\"";
    } else {
        if ($context['mods']['purchase_rating'] == 9) {
            $context['purch_rat_9'] = "selected=\"selected\"";
        } else {
            if ($context['mods']['purchase_rating'] == 8) {
                $context['purch_rat_8'] = "selected=\"selected\"";
            } else {
                if ($context['mods']['purchase_rating'] == 7) {
                    $context['purch_rat_7'] = "selected=\"selected\"";
                } else {
                    if ($context['mods']['purchase_rating'] == 6) {
                        $context['purch_rat_6'] = "selected=\"selected\"";
                    } else {
                        if ($context['mods']['purchase_rating'] == 5) {
                            $context['purch_rat_5'] = "selected=\"selected\"";
                        } else {
                            if ($context['mods']['purchase_rating'] == 4) {
                                $context['purch_rat_4'] = "selected=\"selected\"";
                            } else {
                                if ($context['mods']['purchase_rating'] == 3) {
                                    $context['purch_rat_3'] = "selected=\"selected\"";
                                } else {
                                    if ($context['mods']['purchase_rating'] == 2) {
                                        $context['purch_rat_2'] = "selected=\"selected\"";
                                    } else {
                                        if ($context['mods']['purchase_rating'] == 1) {
                                            $context['purch_rat_1'] = "selected=\"selected\"";
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

    if (!isset($context['purch_rat_10'])) {
        $context['purch_rat_10'] = "";
    }
    if (!isset($context['purch_rat_9'])) {
        $context['purch_rat_9'] = "";
    }
    if (!isset($context['purch_rat_8'])) {
        $context['purch_rat_8'] = "";
    }
    if (!isset($context['purch_rat_7'])) {
        $context['purch_rat_7'] = "";
    }
    if (!isset($context['purch_rat_6'])) {
        $context['purch_rat_6'] = "";
    }
    if (!isset($context['purch_rat_5'])) {
        $context['purch_rat_5'] = "";
    }
    if (!isset($context['purch_rat_4'])) {
        $context['purch_rat_4'] = "";
    }
    if (!isset($context['purch_rat_3'])) {
        $context['purch_rat_3'] = "";
    }
    if (!isset($context['purch_rat_2'])) {
        $context['purch_rat_2'] = "";
    }
    if (!isset($context['purch_rat_1'])) {
        $context['purch_rat_1'] = "";
    }

    if ($context['mods']['install_rating'] == 10) {
        $context['ins_rat_10'] = "selected=\"selected\"";
    } else {
        if ($context['mods']['install_rating'] == 9) {
            $context['ins_rat_9'] = "selected=\"selected\"";
        } else {
            if ($context['mods']['install_rating'] == 8) {
                $context['ins_rat_8'] = "selected=\"selected\"";
            } else {
                if ($context['mods']['install_rating'] == 7) {
                    $context['ins_rat_7'] = "selected=\"selected\"";
                } else {
                    if ($context['mods']['install_rating'] == 6) {
                        $context['ins_rat_6'] = "selected=\"selected\"";
                    } else {
                        if ($context['mods']['install_rating'] == 5) {
                            $context['ins_rat_5'] = "selected=\"selected\"";
                        } else {
                            if ($context['mods']['install_rating'] == 4) {
                                $context['ins_rat_4'] = "selected=\"selected\"";
                            } else {
                                if ($context['mods']['install_rating'] == 3) {
                                    $context['ins_rat_3'] = "selected=\"selected\"";
                                } else {
                                    if ($context['mods']['install_rating'] == 2) {
                                        $context['ins_rat_2'] = "selected=\"selected\"";
                                    } else {
                                        if ($context['mods']['install_rating'] == 1) {
                                            $context['ins_rat_1'] = "selected=\"selected\"";
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

    if (!isset($context['ins_rat_10'])) {
        $context['ins_rat_10'] = "";
    }
    if (!isset($context['ins_rat_9'])) {
        $context['ins_rat_9'] = "";
    }
    if (!isset($context['ins_rat_8'])) {
        $context['ins_rat_8'] = "";
    }
    if (!isset($context['ins_rat_7'])) {
        $context['ins_rat_7'] = "";
    }
    if (!isset($context['ins_rat_6'])) {
        $context['ins_rat_6'] = "";
    }
    if (!isset($context['ins_rat_5'])) {
        $context['ins_rat_5'] = "";
    }
    if (!isset($context['ins_rat_4'])) {
        $context['ins_rat_4'] = "";
    }
    if (!isset($context['ins_rat_3'])) {
        $context['ins_rat_3'] = "";
    }
    if (!isset($context['ins_rat_2'])) {
        $context['ins_rat_2'] = "";
    }
    if (!isset($context['ins_rat_1'])) {
        $context['ins_rat_1'] = "";
    }

    // Select image data
    $request = $smcFunc['db_query']('', '
        SELECT image_id, hilite
        FROM {db_prefix}garage_modifications_gallery
        WHERE modification_id = {int:mid}',
        array(
            'mid' => $_GET['MID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['mods'][$count]['image_id'],
            $context['mods'][$count]['hilite']) = $row;

        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_desc, is_remote
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:image_id}',
            array(
                'image_id' => $context['mods'][$count]['image_id'],
            )
        );
        list($context['mods'][$count]['attach_location'],
            $context['mods'][$count]['attach_file'],
            $context['mods'][$count]['attach_thumb_location'],
            $context['mods'][$count]['attach_thumb_width'],
            $context['mods'][$count]['attach_thumb_height'],
            $context['mods'][$count]['attach_desc'],
            $context['mods'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        $request2 = $smcFunc['db_query']('', '
            SELECT {db_prefix}garage_products.title
            FROM {db_prefix}garage_products, {db_prefix}garage_modifications
            WHERE {db_prefix}garage_modifications.product_id = {db_prefix}garage_products.id
                AND {db_prefix}garage_modifications.id = {int:mid}',
            array(
                'mid' => $_GET['MID'],
            )
        );
        list($context['mods'][$count]['title']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        if (empty($context['mods'][$count]['attach_desc'])) {
            $context['mods'][$count]['attach_desc'] = $txt['smfg_no_desc'];
        }

        // Check to see if the image is remote or not and build appropriate links
        if ($context['mods'][$count]['is_remote'] == 1) {
            $context['mods'][$count]['attach_location'] = urldecode($context['mods'][$count]['attach_file']);
        } else {
            $context['mods'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['mods'][$count]['attach_location'];
        }

        // If there is an image attached, link to it
        if (isset($context['mods'][$count]['attach_location'])) {
            $context['mods'][$count]['image'] = "<a href=\"" . $context['mods'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['mods'][$count]['title'] . ' :: ' . $context['mods'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $boardurl . "/" . $smfgSettings['upload_directory'] . 'cache/' . $context['mods'][$count]['attach_thumb_location'] . "\" width=\"" . $context['mods'][$count]['attach_thumb_width'] . "\" height=\"" . $context['mods'][$count]['attach_thumb_height'] . "\" alt=\"\" /></a>";
        }

        $count++;
    }
    $smcFunc['db_free_result']($request);

    // Select video id
    $request = $smcFunc['db_query']('', '
        SELECT video_id
        FROM {db_prefix}garage_video_gallery
        WHERE vehicle_id = {int:vid}
            AND type = "mod"
            AND type_id = {int:mid}',
        array(
            'vid' => $_GET['VID'],
            'mid' => $_GET['MID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['mods'][$count]['video_id']) = $row;

        // Select video data if there is any
        if (!empty($context['mods'][$count]['video_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT url, title, video_desc
                FROM {db_prefix}garage_video 
                WHERE id = {int:video_id}',
                array(
                    'video_id' => $context['mods'][$count]['video_id'],
                )
            );
            list($context['mods'][$count]['video_url'],
                $context['mods'][$count]['video_title'],
                $context['mods'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);
            if (empty($context['mods'][$count]['video_desc'])) {
                $context['mods'][$count]['video_desc'] = $txt['smfg_no_desc'];
            }
            $context['mods'][$count]['video_thumb'] = displayVideo($context['mods'][$count]['video_url'], 2);
            $context['mods'][$count]['video_height'] = displayVideo($context['mods'][$count]['video_url'], 'height');
            $context['mods'][$count]['video_width'] = displayVideo($context['mods'][$count]['video_url'], 'width');
        }
        $count++;
    }

}

// Update Modifications
function G_Update_Modification()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_modification']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    $date_updated = time();

    // Did they enter prices or leave em blank?
    if (empty($_POST['price'])) {
        $_POST['price'] = 0;
    }
    if (empty($_POST['install_price'])) {
        $_POST['install_price'] = 0;
    }

    if (empty($_POST['shop_id'])) {
        $_POST['shop_id'] = 0;
    }
    if (empty($_POST['installer_id'])) {
        $_POST['installer_id'] = 0;
    }

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_modifications
        SET category_id = {int:category_id}, manufacturer_id = {int:manufacturer_id}, product_id = {int:product_id}, price= {string:price}, install_price = {string:install_price}, product_rating = {int:product_rating}, purchase_rating = {int:purchase_rating}, install_rating = {int:install_rating}, shop_id = {int:shop_id}, installer_id = {int:installer_id}, comments = {string:comments}, install_comments = {string:install_comments}, date_updated = {int:date_updated}
        WHERE id = {int:mid}',
        array(
            'category_id' => $_POST['category_id'],
            'manufacturer_id' => $_POST['manufacturer_id'],
            'product_id' => $_POST['product_id'],
            'price' => $_POST['price'],
            'install_price' => $_POST['install_price'],
            'product_rating' => $_POST['product_rating'],
            'purchase_rating' => $_POST['purchase_rating'],
            'install_rating' => $_POST['install_rating'],
            'shop_id' => $_POST['shop_id'],
            'installer_id' => $_POST['installer_id'],
            'comments' => $_POST['comments'],
            'install_comments' => $_POST['install_comments'],
            'date_updated' => $date_updated,
            'mid' => $_POST['MID'],
        )
    );

    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Delete Modification
function G_Delete_Modification()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boarddir;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Set image directory
    $dir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $cachedir = $dir . 'cache/';

    // Get image IDs
    $request = $smcFunc['db_query']('', '
        SELECT image_id
        FROM {db_prefix}garage_modifications_gallery
        WHERE modification_id = {int:mid}',
        array(
            'mid' => $_GET['MID'],
        )
    );
    while ($row = $smcFunc['db_fetch_row']($request)) {
        $images['id'] = $row[0];
        // Get image filenames
        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_thumb_location
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            $images['filename'] = $row2[0];
            $images['thumb_filename'] = $row2[1];
            // Destroy the images
            unlink($dir . $images['filename']);
            unlink($cachedir . $images['filename']);
            unlink($cachedir . $images['thumb_filename']);
        }
        $smcFunc['db_free_result']($request2);

        // Delete row from garage_images
        $request2 = $smcFunc['db_query']('', '
            DELETE FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
    }
    $smcFunc['db_free_result']($request);

    // Delete rows from modifications_gallery
    $request2 = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_modifications_gallery
        WHERE modification_id = {int:mid}',
        array(
            'mid' => $_GET['MID'],
        )
    );

    // Delete the modification
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_modifications
        WHERE id = {int:mid}',
        array(
            'mid' => $_GET['MID'],
        )
    );

    // ...and send them on their way
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Add Insurance
function G_Add_Insurance()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'add_insurance';

    // Validate Owner
    checkOwner($_GET['VID']);

    // Session variable set?
    if (!isset($_SESSION['added_insurance'])) {
        $_SESSION['added_insurance'] = 0;
    }

    // Make sure this module is enabled
    if (!$smfgSettings['enable_insurance']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build link tree
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['page_title'] = $txt['smfg_add_insurance'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=add_insurance;VID=' . $_GET['VID'],
        'name' => &$txt['smfg_add_insurance']
    );

}

// Insert Insurance
function G_Insert_Insurance()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_insurance']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Insert the premium
    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_premiums',
        array(
            'vehicle_id' => 'int',
            'business_id' => 'int',
            'premium' => 'int',
            'cover_type_id' => 'int',
            'comments' => 'string',
        ),
        array(
            $_POST['VID'],
            $_POST['business_id'],
            $_POST['premium'],
            $_POST['cover_type'],
            $_POST['comments'],
        ),
        array(// no data
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=view_own_vehicle;VID='.$_POST['VID'].'#premiums');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);
}

// Edit Insurance
function G_Edit_Insurance()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'edit_insurance';
    $context['page_title'] = $txt['smfg_edit_insurance'];

    // Validate Owner
    checkOwner($_GET['VID']);

    // Session variable set?
    if (!isset($_SESSION['added_insurance'])) {
        $_SESSION['added_insurance'] = 0;
    }

    // Make sure this module is enabled
    if (!$smfgSettings['enable_insurance']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build the link tree
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=edit_insurance;VID=' . $_GET['VID'] . ';INS_ID=' . $_GET['INS_ID'],
        'name' => &$txt['smfg_edit_insurance']
    );

    $request = $smcFunc['db_query']('', '
        SELECT p.business_id, p.premium, p.cover_type_id, p.comments, b.title
        FROM {db_prefix}garage_premiums AS p, {db_prefix}garage_business AS b
        WHERE p.id = {int:ins_id}
            AND p.business_id = b.id',
        array(
            'ins_id' => $_GET['INS_ID'],
        )
    );
    list($context['premiums']['business_id'],
        $context['premiums']['premium'],
        $context['premiums']['cover_type_id'],
        $context['premiums']['comments'],
        $context['premiums']['title']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
}

// Update Insurance
function G_Update_Insurance()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_insurance']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_premiums
        SET business_id = {int:business_id}, premium = {int:premium}, cover_type_id = {int:cover_type}, comments = {string:comments}
        WHERE id = {int:ins_id}',
        array(
            'business_id' => $_POST['business_id'],
            'premium' => $_POST['premium'],
            'cover_type' => $_POST['cover_type'],
            'comments' => $_POST['comments'],
            'ins_id' => $_POST['INS_ID'],
        )
    );

    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);
}

// Delete Insurance
function G_Delete_Insurance()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Delete the premium
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_premiums
        WHERE id = {int:ins_id}',
        array(
            'ins_id' => $_GET['INS_ID'],
        )
    );

    // ...and send them on their way
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Add Quartermile
function G_Add_Quartermile()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'add_quartermile';
    $context['max_image_bytes'] = $smfgSettings['max_image_kbytes'] * 1024;

    // Validate Owner
    checkOwner($_GET['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_quartermile']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build link tree
    $context['page_title'] = $txt['smfg_add_quartermile'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=add_quartermile;VID=' . $_GET['VID'],
        'name' => &$txt['smfg_add_quartermile']
    );

    // Get available dynoruns for the vehicle
    $request = $smcFunc['db_query']('', '
        SELECT id, bhp, bhp_unit
        FROM {db_prefix}garage_dynoruns
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['dynoruns'][$count]['id'],
            $context['dynoruns'][$count]['bhp'],
            $context['dynoruns'][$count]['bhp_unit']) = $row;
        $count++;
    }
    $smcFunc['db_free_result']($request);

}

// Insert Quartermile
function G_Insert_Quartermile()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';
    $context['date_created'] = time();
    $context['vehicle_id'] = $_POST['VID'];

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_quartermile']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Check if approval is required
    if ($smfgSettings['enable_quartermile_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    if (empty($_POST['dynorun_id'])) {
        $_POST['dynorun_id'] = 0;
    }

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_quartermile_images']) {

        // Check maximum file size from MAX_FILE_SIZE
        if ($_FILES['FILE_UPLOAD']['error'] == 2) {
            loadLanguage('Errors');
            fatal_lang_error('garage_filesize_error', false);
        }

        // If they provided an image to upload use it, otherwise use the remote image
        // handle_images($gallery, $source(0=local,1=remote), $file, $extra)
        if ($_FILES['FILE_UPLOAD']['error'] == 0) {

            // Check for maximum file size allowed...again, just in case they made it this far
            $max_fsize = $smfgSettings['max_image_kbytes'] * 1024;
            if ($_FILES['FILE_UPLOAD']['size'] > $max_fsize) {
                loadLanguage('Errors');
                fatal_lang_error('garage_filesize_error', false);
            }

            // Check for maximum image resolution
            $dimensions = getimagesize($_FILES['FILE_UPLOAD']['tmp_name']) or die('Could not obtain image dimensions.');
            if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                loadLanguage('Errors');
                fatal_lang_error('garage_resolution_error', false);
            }

            // If all the image restrictions were met, go ahead and insert the qmile data
            $request = $smcFunc['db_insert']('insert',
                '{db_prefix}garage_quartermiles',
                array(
                    'vehicle_id' => 'int',
                    'rt' => 'string',
                    'sixty' => 'string',
                    'three' => 'string',
                    'eighth' => 'string',
                    'eighthmph' => 'string',
                    'thou' => 'string',
                    'quart' => 'string',
                    'quartmph' => 'string',
                    'dynorun_id' => 'int',
                    'date_created' => 'int',
                    'date_updated' => 'int',
                    'pending' => 'string',
                ),
                array(
                    $context['vehicle_id'],
                    $_POST['rt'],
                    $_POST['sixty'],
                    $_POST['three'],
                    $_POST['eighth'],
                    $_POST['eighthmph'],
                    $_POST['thou'],
                    $_POST['quart'],
                    $_POST['quartmph'],
                    $_POST['dynorun_id'],
                    $context['date_created'],
                    $context['date_created'],
                    $pending,
                ),
                array(// no data
                )
            );
            $context['qmile_id'] = $smcFunc['db_insert_id']($request);

            // If they made it this far, go ahead and process the image
            handle_images("qmile", 0, $_FILES['FILE_UPLOAD'], $_POST);

            // Insert table data for quartermiles_gallery
            $request = $smcFunc['db_insert']('insert',
                '{db_prefix}garage_quartermiles_gallery',
                array(
                    'vehicle_id' => 'int',
                    'quartermile_id' => 'int',
                    'image_id' => 'int',
                    'hilite' => 'int',
                ),
                array(
                    $context['vehicle_id'],
                    $context['qmile_id'],
                    $context['image_id'],
                    1,
                ),
                array(// no data
                )
            );

        } // Check if a remote image was supplied...then use it
        else {
            if ($_POST['url_image'] !== 'http://' && $_POST['url_image'] !== 'https://') {

                // Check for maximum image resolution
                $dimensions = getimagesize_remote($_POST['url_image']) or die('Could not obtain remote image dimensions.');
                if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                    loadLanguage('Errors');
                    fatal_lang_error('garage_resolution_error', false);
                }

                // If all the image restrictions were met, go ahead and insert the qmile data
                $request = $smcFunc['db_insert']('insert',
                    '{db_prefix}garage_quartermiles',
                    array(
                        'vehicle_id' => 'int',
                        'rt' => 'string',
                        'sixty' => 'string',
                        'three' => 'string',
                        'eighth' => 'string',
                        'eighthmph' => 'string',
                        'thou' => 'string',
                        'quart' => 'string',
                        'quartmph' => 'string',
                        'dynorun_id' => 'int',
                        'date_created' => 'int',
                        'date_updated' => 'int',
                        'pending' => 'string',
                    ),
                    array(
                        $context['vehicle_id'],
                        $_POST['rt'],
                        $_POST['sixty'],
                        $_POST['three'],
                        $_POST['eighth'],
                        $_POST['eighthmph'],
                        $_POST['thou'],
                        $_POST['quart'],
                        $_POST['quartmph'],
                        $_POST['dynorun_id'],
                        $context['date_created'],
                        $context['date_created'],
                        $pending,
                    ),
                    array(// no data
                    )
                );
                $context['qmile_id'] = $smcFunc['db_insert_id']($request);

                // If they made it this far, go ahead and process the image
                handle_images("qmile", 1, $_POST);

                // Insert table data for quartermiles_gallery
                $request = $smcFunc['db_insert']('insert',
                    '{db_prefix}garage_quartermiles_gallery',
                    array(
                        'vehicle_id' => 'int',
                        'quartermile_id' => 'int',
                        'image_id' => 'int',
                        'hilite' => 'int',
                    ),
                    array(
                        $context['vehicle_id'],
                        $context['qmile_id'],
                        $context['image_id'],
                        1,
                    ),
                    array(// no data
                    )
                );

            }
        }

    }

    // If modification images are disabled or no image was provided, we still need to insert the data
    if ($smfgSettings['enable_quartermile_images'] != 1 || $_FILES['FILE_UPLOAD']['error'] == 4 && ($_POST['url_image'] === 'http://' || $_POST['url_image'] === 'https://')) {

        if ($smfgSettings['enable_quartermile_image_required']) {
            loadLanguage('Errors');
            fatal_lang_error('garage_required_image_error', false);
        }

        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_quartermiles',
            array(
                'vehicle_id' => 'int',
                'rt' => 'string',
                'sixty' => 'string',
                'three' => 'string',
                'eighth' => 'string',
                'eighthmph' => 'string',
                'thou' => 'string',
                'quart' => 'string',
                'quartmph' => 'string',
                'dynorun_id' => 'int',
                'date_created' => 'int',
                'date_updated' => 'int',
                'pending' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['rt'],
                $_POST['sixty'],
                $_POST['three'],
                $_POST['eighth'],
                $_POST['eighthmph'],
                $_POST['thou'],
                $_POST['quart'],
                $_POST['quartmph'],
                $_POST['dynorun_id'],
                $context['date_created'],
                $context['date_created'],
                $pending,
            ),
            array(// no data
            )
        );
        $context['qmile_id'] = $smcFunc['db_insert_id']($request);
    }

    // Perform video actions if enabled
    if ($smfgSettings['enable_quartermile_video'] && $_POST['video_url'] > 'http://') {

        // Check for video title
        if (empty($_POST['video_title'])) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_title_required', false);
        }

        // Check for video URL compatibility
        if (!displayVideo($_POST['video_url'], 4)) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_unsupported', false);
        }

        // Insert video
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video',
            array(
                'vehicle_id' => 'int',
                'url' => 'string',
                'title' => 'string',
                'video_desc' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['video_url'],
                $_POST['video_title'],
                $_POST['video_desc'],
            ),
            array(// no data
            )
        );
        $context['video_id'] = $smcFunc['db_insert_id']($request);

        // Insert table data for video_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video_gallery',
            array(
                'vehicle_id' => 'int',
                'video_id' => 'int',
                'type' => 'string',
                'type_id' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['video_id'],
                'qmile',
                $context['qmile_id'],
            ),
            array(// no data
            )
        );

    }

    // Send out Notifications
    if ($smfgSettings['enable_quartermile_approval']) {
        sendGarageNotifications();
    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=view_own_vehicle;VID='.$context['vehicle_id'].'#quartermiles');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Edit Quartermile
function G_Edit_Quartermile()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'edit_quartermile';
    $context['max_image_bytes'] = $smfgSettings['max_image_kbytes'] * 1024;
    $context['page_title'] = $txt['smfg_edit_quartermile'];

    // Validate Owner
    checkOwner($_GET['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_quartermile']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build the link tree
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=edit_quartermile;VID=' . $_GET['VID'] . ';QID=' . $_GET['QID'],
        'name' => &$txt['smfg_edit_quartermile']
    );

    $request = $smcFunc['db_query']('', '
        SELECT rt, sixty, three, eighth, eighthmph, thou, quart, quartmph, dynorun_id
        FROM {db_prefix}garage_quartermiles
        WHERE id = {int:qid}',
        array(
            'qid' => $_GET['QID'],
        )
    );
    list($context['qmiles']['rt'],
        $context['qmiles']['sixty'],
        $context['qmiles']['three'],
        $context['qmiles']['eighth'],
        $context['qmiles']['eighthmph'],
        $context['qmiles']['thou'],
        $context['qmiles']['quart'],
        $context['qmiles']['quartmph'],
        $context['qmiles']['dynorun_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Select image data
    $request = $smcFunc['db_query']('', '
        SELECT image_id, hilite
        FROM {db_prefix}garage_quartermiles_gallery
        WHERE quartermile_id = {int:qid}',
        array(
            'qid' => $_GET['QID'],
        )
    );
    $count = 0;
    //$tempArray = array();
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['qmiles'][$count]['image_id'],
            $context['qmiles'][$count]['hilite']) = $row;
        /*list($tempArray['image_id'],
             $tempArray['hilite']) = $row;*/

        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_desc, is_remote
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:image_id}',
            array(
                'image_id' => $context['qmiles'][$count]['image_id'],
            )
        );
        //WHERE attach_id = ".$tempArray['image_id'],__FILE__,__LINE__);
        list($context['qmiles'][$count]['attach_location'],
            $context['qmiles'][$count]['attach_file'],
            $context['qmiles'][$count]['attach_thumb_location'],
            $context['qmiles'][$count]['attach_thumb_width'],
            $context['qmiles'][$count]['attach_thumb_height'],
            $context['qmiles'][$count]['attach_desc'],
            $context['qmiles'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
        /*list($tempArray['attach_location'],
             $tempArray['attach_file'],
             $tempArray['attach_thumb_location'],
             $tempArray['attach_thumb_width'],
             $tempArray['attach_thumb_height'],
             $tempArray['attach_desc'],
             $tempArray['is_remote']) = $smcFunc['db_fetch_row']($request2);*/
        $smcFunc['db_free_result']($request2);

        if (empty($context['qmiles'][$count]['attach_desc'])) {
            $context['qmiles'][$count]['attach_desc'] = $txt['smfg_no_desc'];
        }

        // Check to see if the image is remote or not and build appropriate links
        if ($context['qmiles'][$count]['is_remote'] == 1) {
            $context['qmiles'][$count]['attach_location'] = urldecode($context['qmiles'][$count]['attach_file']);
        } else {
            $context['qmiles'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['qmiles'][$count]['attach_location'];
        }

        // If there is an image attached, link to it
        if (isset($context['qmiles'][$count]['attach_location'])) {
            $context['qmiles'][$count]['image'] = "<a href=\"" . $context['qmiles'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['qmiles']['quart'] . ' @ ' . $context['qmiles']['quartmph'] . ' :: ' . $context['qmiles'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $boardurl . "/" . $smfgSettings['upload_directory'] . 'cache/' . $context['qmiles'][$count]['attach_thumb_location'] . "\" width=\"" . $context['qmiles'][$count]['attach_thumb_width'] . "\" height=\"" . $context['qmiles'][$count]['attach_thumb_height'] . "\" /></a>";
        }
        $count++;

        /*
        // Check to see if the image is remote or not and build appropriate links
        if($tempArray['is_remote'] == 1) $tempArray['attach_location'] = urldecode($tempArray['attach_file']);
            else $tempArray['attach_location'] = $boardurl.'/'.$smfgSettings['upload_directory'].'cache/'.$tempArray['attach_location'];

        // If there is an image attached, link to it
        if(isset($tempArray['attach_location'])) $tempArray['image'] = "<a href=\"".$tempArray['attach_location']."\" rel=\"shadowbox\" title=\"".garage_title_clean($tempArray['attach_desc'])."\"><img src=\"".$boardurl."/".$smfgSettings['upload_directory'].'cache/'.$tempArray['attach_thumb_location']."\" width=\"".$tempArray['attach_thumb_width']."\" height=\"".$tempArray['attach_thumb_height']."\" alt=\"\" /></a>";

        array_push($context['qmiles'], $tempArray);

        echo '<pre>';
        print_R($context['qmiles']);
        echo '</pre>';
        */
    }

    // Select video data
    $request = $smcFunc['db_query']('', '
        SELECT video_id
        FROM {db_prefix}garage_video_gallery
        WHERE vehicle_id = {int:vid}
            AND type = "qmile"
            AND type_id = {int:qid}',
        array(
            'vid' => $_GET['VID'],
            'qid' => $_GET['QID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['qmiles'][$count]['video_id']) = $row;

        // Select video data if there is any
        if (!empty($context['qmiles'][$count]['video_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT url, title, video_desc
                FROM {db_prefix}garage_video 
                WHERE id = {int:video_id}',
                array(
                    'video_id' => $context['qmiles'][$count]['video_id'],
                )
            );
            list($context['qmiles'][$count]['video_url'],
                $context['qmiles'][$count]['video_title'],
                $context['qmiles'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);
            if (empty($context['qmiles'][$count]['video_desc'])) {
                $context['qmiles'][$count]['video_desc'] = $txt['smfg_no_desc'];
            }
            $context['qmiles'][$count]['video_thumb'] = displayVideo($context['qmiles'][$count]['video_url'], 2);
            $context['qmiles'][$count]['video_height'] = displayVideo($context['qmiles'][$count]['video_url'],
                'height');
            $context['qmiles'][$count]['video_width'] = displayVideo($context['qmiles'][$count]['video_url'], 'width');
        }
        $count++;
    }

}

// Update Quartermile
function G_Update_Quartermile()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_quartermile']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    if (empty($_POST['dynorun_id'])) {
        $_POST['dynorun_id'] = 0;
    }

    $date_updated = time();

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_quartermiles
        SET rt = {string:rt}, sixty = {string:sixty}, three = {string:three}, eighth = {string:eighth}, eighthmph = {string:eighthmph}, thou = {string:thou}, quart = {string:quart}, quartmph = {string:quartmph}, dynorun_id = {int:dynorun_id}, date_updated = {int:date_updated}
        WHERE id = {int:qid}',
        array(
            'rt' => $_POST['rt'],
            'sixty' => $_POST['sixty'],
            'three' => $_POST['three'],
            'eighth' => $_POST['eighth'],
            'eighthmph' => $_POST['eighthmph'],
            'thou' => $_POST['thou'],
            'quart' => $_POST['quart'],
            'quartmph' => $_POST['quartmph'],
            'dynorun_id' => $_POST['dynorun_id'],
            'date_updated' => $date_updated,
            'qid' => $_POST['QID'],
        )
    );

    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Delete Quartermile
function G_Delete_Quartermile()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boarddir;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Set image directory
    $dir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $cachedir = $dir . 'cache/';

    // Get image IDs
    $request = $smcFunc['db_query']('', '
        SELECT image_id
        FROM {db_prefix}garage_quartermiles_gallery
        WHERE quartermile_id = {int:qid}',
        array(
            'qid' => $_GET['QID'],
        )
    );
    while ($row = $smcFunc['db_fetch_row']($request)) {
        $images['id'] = $row[0];
        // Get image filenames
        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_thumb_location
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            $images['filename'] = $row2[0];
            $images['thumb_filename'] = $row2[1];
            // Destroy the images
            unlink($dir . $images['filename']);
            unlink($cachedir . $images['filename']);
            unlink($cachedir . $images['thumb_filename']);
        }
        $smcFunc['db_free_result']($request2);

        // Delete row from garage_images
        $request2 = $smcFunc['db_query']('', '
            DELETE FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
    }
    $smcFunc['db_free_result']($request);

    // Delete rows from quartermiles_gallery
    $request2 = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_quartermiles_gallery
        WHERE quartermile_id = {int:qid}',
        array(
            'qid' => $_GET['QID'],
        )
    );

    // Delete the quartermile
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_quartermiles
        WHERE id = {int:qid}',
        array(
            'qid' => $_GET['QID'],
        )
    );

    // ...and send them on their way
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Add Dynorun
function G_Add_Dynorun()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'add_dynorun';
    $context['max_image_bytes'] = $smfgSettings['max_image_kbytes'] * 1024;

    // Validate Owner
    checkOwner($_GET['VID']);

    // Session variable set?
    if (!isset($_SESSION['added_dynocenter'])) {
        $_SESSION['added_dynocenter'] = 0;
    }

    // Make sure this module is enabled
    if (!$smfgSettings['enable_dynorun']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build link tree
    $context['page_title'] = $txt['smfg_add_dynorun'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=add_dynorun;VID=' . $_GET['VID'],
        'name' => &$txt['smfg_add_dynorun']
    );

}

// Insert Dynorun
function G_Insert_Dynorun()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';
    $context['date_created'] = time();
    $context['vehicle_id'] = $_POST['VID'];

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_dynorun']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Check if approval is required
    if ($smfgSettings['enable_dynorun_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_dynorun_images']) {

        // Check maximum file size from MAX_FILE_SIZE
        if ($_FILES['FILE_UPLOAD']['error'] == 2) {
            loadLanguage('Errors');
            fatal_lang_error('garage_filesize_error', false);
        }

        // If they provided an image to upload use it, otherwise use the remote image
        // handle_images($gallery, $source(0=local,1=remote), $file, $extra)
        if ($_FILES['FILE_UPLOAD']['error'] == 0) {

            // Check for maximum file size allowed...again, just in case they made it this far
            $max_fsize = $smfgSettings['max_image_kbytes'] * 1024;
            if ($_FILES['FILE_UPLOAD']['size'] > $max_fsize) {
                loadLanguage('Errors');
                fatal_lang_error('garage_filesize_error', false);
            }

            // Check for maximum image resolution
            $dimensions = getimagesize($_FILES['FILE_UPLOAD']['tmp_name']) or die('Could not obtain image dimensions.');
            if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                loadLanguage('Errors');
                fatal_lang_error('garage_resolution_error', false);
            }

            // If all the image restrictions were met, go ahead and insert the dynorun data
            $request = $smcFunc['db_insert']('insert',
                '{db_prefix}garage_dynoruns',
                array(
                    'vehicle_id' => 'int',
                    'dynocenter_id' => 'int',
                    'bhp' => 'string',
                    'bhp_unit' => 'string',
                    'torque' => 'string',
                    'torque_unit' => 'string',
                    'boost' => 'string',
                    'boost_unit' => 'string',
                    'nitrous' => 'string',
                    'peakpoint' => 'string',
                    'date_created' => 'int',
                    'date_updated' => 'int',
                    'pending' => 'string',
                ),
                array(
                    $context['vehicle_id'],
                    $_POST['dynocenter_id'],
                    $_POST['bhp'],
                    $_POST['bhp_unit'],
                    $_POST['torque'],
                    $_POST['torque_unit'],
                    $_POST['boost'],
                    $_POST['boost_unit'],
                    $_POST['nitrous'],
                    $_POST['peakpoint'],
                    $context['date_created'],
                    $context['date_created'],
                    $pending,
                ),
                array(// no data
                )
            );
            $context['dynorun_id'] = $smcFunc['db_insert_id']($request);

            // If they made it this far, go ahead and process the image
            handle_images("dynorun", 0, $_FILES['FILE_UPLOAD'], $_POST);

            // Insert table data for dynoruns_gallery
            $request = $smcFunc['db_insert']('insert',
                '{db_prefix}garage_dynoruns_gallery',
                array(
                    'vehicle_id' => 'int',
                    'dynorun_id' => 'int',
                    'image_id' => 'int',
                    'hilite' => 'int',
                ),
                array(
                    $context['vehicle_id'],
                    $context['dynorun_id'],
                    $context['image_id'],
                    1,
                )
            );

        } // Check if a remote image was supplied...then use it
        else {
            if ($_POST['url_image'] !== 'http://' && $_POST['url_image'] !== 'https://') {

                // Check for maximum image resolution
                $dimensions = getimagesize_remote($_POST['url_image']) or die('Could not obtain remote image dimensions.');
                if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                    loadLanguage('Errors');
                    fatal_lang_error('garage_resolution_error', false);
                }

                // If all the image restrictions were met, go ahead and insert the dynorun data
                $request = $smcFunc['db_insert']('insert',
                    '{db_prefix}garage_dynoruns',
                    array(
                        'vehicle_id' => 'int',
                        'dynocenter_id' => 'int',
                        'bhp' => 'string',
                        'bhp_unit' => 'string',
                        'torque' => 'string',
                        'torque_unit' => 'string',
                        'boost' => 'string',
                        'boost_unit' => 'string',
                        'nitrous' => 'string',
                        'peakpoint' => 'string',
                        'date_created' => 'int',
                        'date_updated' => 'int',
                        'pending' => 'string',
                    ),
                    array(
                        $context['vehicle_id'],
                        $_POST['dynocenter_id'],
                        $_POST['bhp'],
                        $_POST['bhp_unit'],
                        $_POST['torque'],
                        $_POST['torque_unit'],
                        $_POST['boost'],
                        $_POST['boost_unit'],
                        $_POST['nitrous'],
                        $_POST['peakpoint'],
                        $context['date_created'],
                        $context['date_created'],
                        $pending,
                    ),
                    array(// no data
                    )
                );
                $context['dynorun_id'] = $smcFunc['db_insert_id']($request);

                // If they made it this far, go ahead and process the image
                handle_images("dynorun", 1, $_POST);

                // Insert table data for dynoruns_gallery
                $request = $smcFunc['db_insert']('insert',
                    '{db_prefix}garage_dynoruns_gallery',
                    array(
                        'vehicle_id' => 'int',
                        'dynorun_id' => 'int',
                        'image_id' => 'int',
                        'hilite' => 'int',
                    ),
                    array(
                        $context['vehicle_id'],
                        $context['dynorun_id'],
                        $context['image_id'],
                        1,
                    ),
                    array(// no data
                    )
                );

            }
        }

    }

    // If dynorun images are disabled or no image was provided, we still need to insert the data
    if ($smfgSettings['enable_dynorun_images'] != 1 || $_FILES['FILE_UPLOAD']['error'] == 4 && ($_POST['url_image'] === 'http://' || $_POST['url_image'] === 'https://')) {

        // Check if images are required
        if ($smfgSettings['enable_dynorun_image_required']) {
            loadLanguage('Errors');
            fatal_lang_error('garage_required_image_error', false);
        }

        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_dynoruns',
            array(
                'vehicle_id' => 'int',
                'dynocenter_id' => 'int',
                'bhp' => 'string',
                'bhp_unit' => 'string',
                'torque' => 'string',
                'torque_unit' => 'string',
                'boost' => 'string',
                'boost_unit' => 'string',
                'nitrous' => 'string',
                'peakpoint' => 'string',
                'date_created' => 'int',
                'date_updated' => 'int',
                'pending' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['dynocenter_id'],
                $_POST['bhp'],
                $_POST['bhp_unit'],
                $_POST['torque'],
                $_POST['torque_unit'],
                $_POST['boost'],
                $_POST['boost_unit'],
                $_POST['nitrous'],
                $_POST['peakpoint'],
                $context['date_created'],
                $context['date_created'],
                $pending,
            ),
            array(// no data
            )
        );
        $context['dynorun_id'] = $smcFunc['db_insert_id']($request);
    }

    // Perform video actions if enabled
    if ($smfgSettings['enable_dynorun_video'] && $_POST['video_url'] > 'http://') {

        // Check for video title
        if (empty($_POST['video_title'])) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_title_required', false);
        }

        // Check for video URL compatibility
        if (!displayVideo($_POST['video_url'], 4)) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_unsupported', false);
        }

        // Insert video
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video',
            array(
                'vehicle_id' => 'int',
                'url' => 'string',
                'title' => 'string',
                'video_desc' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['video_url'],
                $_POST['video_title'],
                $_POST['video_desc'],
            ),
            array(// no data
            )
        );
        $context['video_id'] = $smcFunc['db_insert_id']($request);

        // Insert table data for video_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video_gallery',
            array(
                'vehicle_id' => 'int',
                'video_id' => 'int',
                'type' => 'string',
                'type_id' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['video_id'],
                'dynorun',
                $context['dynorun_id'],
            ),
            array(// no data
            )
        );

    }

    // Send out Notifications
    if ($smfgSettings['enable_dynorun_approval']) {
        sendGarageNotifications();
    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=view_own_vehicle;VID='.$context['vehicle_id'].'#dynoruns');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Edit Dynorun
function G_Edit_Dynorun()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'edit_dynorun';
    $context['max_image_bytes'] = $smfgSettings['max_image_kbytes'] * 1024;
    $context['page_title'] = $txt['smfg_edit_dynorun'];

    // Validate Owner
    checkOwner($_GET['VID']);

    // Session variable set?
    if (!isset($_SESSION['added_dynocenter'])) {
        $_SESSION['added_dynocenter'] = 0;
    }

    // Make sure this module is enabled
    if (!$smfgSettings['enable_dynorun']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build the link tree
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=edit_dynorun;VID=' . $_GET['VID'] . ';DID=' . $_GET['DID'],
        'name' => &$txt['smfg_edit_dynorun']
    );

    $request = $smcFunc['db_query']('', '
        SELECT dynocenter_id, bhp, bhp_unit, torque, torque_unit, boost, boost_unit, nitrous, peakpoint
        FROM {db_prefix}garage_dynoruns
        WHERE id = {int:did}',
        array(
            'did' => $_GET['DID'],
        )
    );
    list($context['dynoruns']['dynocenter_id'],
        $context['dynoruns']['bhp'],
        $context['dynoruns']['bhp_unit'],
        $context['dynoruns']['torque'],
        $context['dynoruns']['torque_unit'],
        $context['dynoruns']['boost'],
        $context['dynoruns']['boost_unit'],
        $context['dynoruns']['nitrous'],
        $context['dynoruns']['peakpoint']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($context['dynoruns']['bhp_unit'] == "wheel") {
        $context['bhp_wheel'] = "selected=\"selected\"";
    } else {
        if ($context['dynoruns']['bhp_unit'] == "hub") {
            $context['bhp_hub'] = "selected=\"selected\"";
        } else {
            if ($context['dynoruns']['bhp_unit'] == "flywheel") {
                $context['bhp_fly'] = "selected=\"selected\"";
            }
        }
    }

    if (!isset($context['bhp_wheel'])) {
        $context['bhp_wheel'] = "";
    }
    if (!isset($context['bhp_hub'])) {
        $context['bhp_hub'] = "";
    }
    if (!isset($context['bhp_fly'])) {
        $context['bhp_fly'] = "";
    }

    if ($context['dynoruns']['torque_unit'] == "wheel") {
        $context['torque_wheel'] = "selected=\"selected\"";
    } else {
        if ($context['dynoruns']['torque_unit'] == "hub") {
            $context['torque_hub'] = "selected=\"selected\"";
        } else {
            if ($context['dynoruns']['torque_unit'] == "flywheel") {
                $context['torque_fly'] = "selected=\"selected\"";
            }
        }
    }

    if (!isset($context['torque_wheel'])) {
        $context['torque_wheel'] = "";
    }
    if (!isset($context['torque_hub'])) {
        $context['torque_hub'] = "";
    }
    if (!isset($context['torque_fly'])) {
        $context['torque_fly'] = "";
    }

    if ($context['dynoruns']['boost_unit'] == "PSI") {
        $context['psi'] = "selected=\"selected\"";
    } else {
        if ($context['dynoruns']['boost_unit'] == "BAR") {
            $context['bar'] = "selected=\"selected\"";
        }
    }

    if (!isset($context['psi'])) {
        $context['psi'] = "";
    }
    if (!isset($context['bar'])) {
        $context['bar'] = "";
    }

    if ($context['dynoruns']['nitrous'] == "0") {
        $context['n0'] = "selected=\"selected\"";
    } else {
        if ($context['dynoruns']['nitrous'] == "25") {
            $context['n25'] = "selected=\"selected\"";
        } else {
            if ($context['dynoruns']['nitrous'] == "50") {
                $context['n50'] = "selected=\"selected\"";
            } else {
                if ($context['dynoruns']['nitrous'] == "75") {
                    $context['n75'] = "selected=\"selected\"";
                } else {
                    if ($context['dynoruns']['nitrous'] == "100") {
                        $context['n100'] = "selected=\"selected\"";
                    }
                }
            }
        }
    }

    if (!isset($context['n0'])) {
        $context['n0'] = "";
    }
    if (!isset($context['n25'])) {
        $context['n25'] = "";
    }
    if (!isset($context['n50'])) {
        $context['n50'] = "";
    }
    if (!isset($context['n75'])) {
        $context['n75'] = "";
    }
    if (!isset($context['n100'])) {
        $context['n100'] = "";
    }

    // Select image data
    $request = $smcFunc['db_query']('', '
        SELECT image_id, hilite
        FROM {db_prefix}garage_dynoruns_gallery
        WHERE dynorun_id = {int:did}',
        array(
            'did' => $_GET['DID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['dynoruns'][$count]['image_id'],
            $context['dynoruns'][$count]['hilite']) = $row;

        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_desc, is_remote
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:image_id}',
            array(
                'image_id' => $context['dynoruns'][$count]['image_id'],
            )
        );
        list($context['dynoruns'][$count]['attach_location'],
            $context['dynoruns'][$count]['attach_file'],
            $context['dynoruns'][$count]['attach_thumb_location'],
            $context['dynoruns'][$count]['attach_thumb_width'],
            $context['dynoruns'][$count]['attach_thumb_height'],
            $context['dynoruns'][$count]['attach_desc'],
            $context['dynoruns'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        if (empty($context['dynoruns'][$count]['attach_desc'])) {
            $context['dynoruns'][$count]['attach_desc'] = $txt['smfg_no_desc'];
        }

        // Check to see if the image is remote or not and build appropriate links
        if ($context['dynoruns'][$count]['is_remote'] == 1) {
            $context['dynoruns'][$count]['attach_location'] = urldecode($context['dynoruns'][$count]['attach_file']);
        } else {
            $context['dynoruns'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['dynoruns'][$count]['attach_location'];
        }

        // If there is an image attached, link to it
        if (isset($context['dynoruns'][$count]['attach_location'])) {
            $context['dynoruns'][$count]['image'] = "<a href=\"" . $context['dynoruns'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['dynoruns']['bhp'] . ' ' . $context['dynoruns']['bhp_unit'] . ' :: ' . $context['dynoruns'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $boardurl . "/" . $smfgSettings['upload_directory'] . 'cache/' . $context['dynoruns'][$count]['attach_thumb_location'] . "\" width=\"" . $context['dynoruns'][$count]['attach_thumb_width'] . "\" height=\"" . $context['dynoruns'][$count]['attach_thumb_height'] . "\" /></a>";
        }

        $count++;
    }

    // Select video data
    $request = $smcFunc['db_query']('', '
        SELECT video_id
        FROM {db_prefix}garage_video_gallery
        WHERE vehicle_id = {int:vid}
            AND type = "dynorun"
            AND type_id = {int:did}',
        array(
            'vid' => $_GET['VID'],
            'did' => $_GET['DID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['dynoruns'][$count]['video_id']) = $row;

        // Select video data if there is any
        if (!empty($context['dynoruns'][$count]['video_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT url, title, video_desc
                FROM {db_prefix}garage_video 
                WHERE id = {int:video_id}',
                array(
                    'video_id' => $context['dynoruns'][$count]['video_id'],
                )
            );
            list($context['dynoruns'][$count]['video_url'],
                $context['dynoruns'][$count]['video_title'],
                $context['dynoruns'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);
            if (empty($context['dynoruns'][$count]['video_desc'])) {
                $context['dynoruns'][$count]['video_desc'] = $txt['smfg_no_desc'];
            }
            $context['dynoruns'][$count]['video_thumb'] = displayVideo($context['dynoruns'][$count]['video_url'], 2);
            $context['dynoruns'][$count]['video_height'] = displayVideo($context['dynoruns'][$count]['video_url'],
                'height');
            $context['dynoruns'][$count]['video_width'] = displayVideo($context['dynoruns'][$count]['video_url'],
                'width');
        }
        $count++;
    }

}

// Update Dynorun
function G_Update_Dynorun()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';
    $date_updated = time();

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_dynorun']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_dynoruns
        SET dynocenter_id = {int:dynocenter_id}, bhp = {string:bhp}, bhp_unit = {string:bhp_unit}, torque = {string:torque}, torque_unit = {string:torque_unit}, boost = {string:boost}, boost_unit = {string:boost_unit}, nitrous = {string:nitrous}, peakpoint = {string:peakpoint}, date_updated = {int:date_updated}
        WHERE id = {int:did}',
        array(
            'dynocenter_id' => $_POST['dynocenter_id'],
            'bhp' => $_POST['bhp'],
            'bhp_unit' => $_POST['bhp_unit'],
            'torque' => $_POST['torque'],
            'torque_unit' => $_POST['torque_unit'],
            'boost' => $_POST['boost'],
            'boost_unit' => $_POST['boost_unit'],
            'nitrous' => $_POST['nitrous'],
            'peakpoint' => $_POST['peakpoint'],
            'date_updated' => $date_updated,
            'did' => $_POST['DID'],
        )
    );

    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Delete Dynorun
function G_Delete_Dynorun()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boarddir;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Set image directory
    $dir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $cachedir = $dir . 'cache/';

    // Get image IDs
    $request = $smcFunc['db_query']('', '
        SELECT image_id
        FROM {db_prefix}garage_dynoruns_gallery
        WHERE dynorun_id = {int:did}',
        array(
            'did' => $_GET['DID'],
        )
    );
    while ($row = $smcFunc['db_fetch_row']($request)) {
        $images['id'] = $row[0];
        // Get image filenames
        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_thumb_location
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            $images['filename'] = $row2[0];
            $images['thumb_filename'] = $row2[1];
            // Destroy the images
            unlink($dir . $images['filename']);
            unlink($cachedir . $images['filename']);
            unlink($cachedir . $images['thumb_filename']);
        }
        $smcFunc['db_free_result']($request2);

        // Delete row from garage_images
        $request2 = $smcFunc['db_query']('', '
            DELETE FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
    }
    $smcFunc['db_free_result']($request);

    // Delete rows from dynoruns_gallery
    $request2 = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_dynoruns_gallery
        WHERE dynorun_id = {int:did}',
        array(
            'did' => $_GET['DID'],
        )
    );

    // Delete the dynorun
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_dynoruns
        WHERE id = {int:did}',
        array(
            'did' => $_GET['DID'],
        )
    );

    // ...and send them on their way
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Add Laptime
function G_Add_Laptime()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'add_laptime';
    $context['max_image_bytes'] = $smfgSettings['max_image_kbytes'] * 1024;
    $context['page_title'] = $txt['smfg_add_laptime'];

    // Validate Owner
    checkOwner($_GET['VID']);

    // Session variable set?
    if (!isset($_SESSION['added_track'])) {
        $_SESSION['added_track'] = 0;
    }

    // Make sure this module is enabled
    if (!$smfgSettings['enable_laptimes']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=add_laptime;VID=' . $_GET['VID'],
        'name' => &$txt['smfg_add_laptime']
    );

}

// Insert Laptime
function G_Insert_Laptime()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';
    $context['vehicle_id'] = $_POST['VID'];
    $context['date_created'] = time();

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_laptimes']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Check if approval is required
    if ($smfgSettings['enable_lap_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_lap_images']) {

        // Check maximum file size from MAX_FILE_SIZE
        if ($_FILES['FILE_UPLOAD']['error'] == 2) {
            loadLanguage('Errors');
            fatal_lang_error('garage_filesize_error', false);
        }

        // If they provided an image to upload use it, otherwise use the remote image
        // handle_images($gallery, $source(0=local,1=remote), $file, $extra)
        if ($_FILES['FILE_UPLOAD']['error'] == 0) {

            // Check for maximum file size allowed...again, just in case they made it this far
            $max_fsize = $smfgSettings['max_image_kbytes'] * 1024;
            if ($_FILES['FILE_UPLOAD']['size'] > $max_fsize) {
                loadLanguage('Errors');
                fatal_lang_error('garage_filesize_error', false);
            }

            // Check for maximum image resolution
            $dimensions = getimagesize($_FILES['FILE_UPLOAD']['tmp_name']) or die('Could not obtain image dimensions.');
            if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                loadLanguage('Errors');
                fatal_lang_error('garage_resolution_error', false);
            }

            // If all the image restrictions were met, go ahead and insert the lap data
            $request = $smcFunc['db_insert']('insert',
                '{db_prefix}garage_laps',
                array(
                    'vehicle_id' => 'int',
                    'track_id' => 'int',
                    'condition_id' => 'int',
                    'type_id' => 'int',
                    'minute' => 'int',
                    'second' => 'int',
                    'millisecond' => 'int',
                    'pending' => 'string',
                ),
                array(
                    $context['vehicle_id'],
                    $_POST['track_id'],
                    $_POST['condition'],
                    $_POST['type'],
                    $_POST['minute'],
                    $_POST['second'],
                    $_POST['millisecond'],
                    $pending,
                ),
                array(// no data
                )
            );
            $context['lap_id'] = $smcFunc['db_insert_id']($request);

            // If they made it this far, go ahead and process the image
            handle_images("lap", 0, $_FILES['FILE_UPLOAD'], $_POST);

            // Insert table data for laps_gallery
            $request = $smcFunc['db_insert']('insert',
                '{db_prefix}garage_laps_gallery',
                array(
                    'vehicle_id' => 'int',
                    'lap_id' => 'int',
                    'image_id' => 'int',
                    'hilite' => 'int',
                ),
                array(
                    $context['vehicle_id'],
                    $context['lap_id'],
                    $context['image_id'],
                    1,
                ),
                array(// no data
                )
            );

        } // Check if a remote image was supplied...then use it
        else {
            if ($_POST['url_image'] !== 'http://' && $_POST['url_image'] !== 'https://') {

                // Check for maximum image resolution
                $dimensions = getimagesize_remote($_POST['url_image']) or die('Could not obtain remote image dimensions.');
                if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                    loadLanguage('Errors');
                    fatal_lang_error('garage_resolution_error', false);
                }

                // If all the image restrictions were met, go ahead and insert the lap data
                $request = $smcFunc['db_insert']('insert',
                    '{db_prefix}garage_laps',
                    array(
                        'vehicle_id' => 'int',
                        'track_id' => 'int',
                        'condition_id' => 'int',
                        'type_id' => 'int',
                        'minute' => 'int',
                        'second' => 'int',
                        'millisecond' => 'int',
                        'pending' => 'string',
                    ),
                    array(
                        $context['vehicle_id'],
                        $_POST['track_id'],
                        $_POST['condition'],
                        $_POST['type'],
                        $_POST['minute'],
                        $_POST['second'],
                        $_POST['millisecond'],
                        $pending,
                    ),
                    array(// no data
                    )
                );
                $context['lap_id'] = $smcFunc['db_insert_id']($request);

                // If they made it this far, go ahead and process the image
                handle_images("lap", 1, $_POST);

                // Insert table data for laps_gallery
                $request = $smcFunc['db_insert']('insert',
                    '{db_prefix}garage_laps_gallery',
                    array(
                        'vehicle_id' => 'int',
                        'lap_id' => 'int',
                        'image_id' => 'int',
                        'hilite' => 'int',
                    ),
                    array(
                        $context['vehicle_id'],
                        $context['lap_id'],
                        $context['image_id'],
                        1,
                    ),
                    array(// no data
                    )
                );

            }
        }

    }

    // If modification images are disabled or no image was provided, we still need to insert the data
    if ($smfgSettings['enable_lap_images'] != 1 || $_FILES['FILE_UPLOAD']['error'] == 4 && ($_POST['url_image'] === 'http://' || $_POST['url_image'] === 'https://')) {

        // Check if images are required
        if ($smfgSettings['enable_lap_image_required']) {
            loadLanguage('Errors');
            fatal_lang_error('garage_required_image_error', false);
        }

        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_laps',
            array(
                'vehicle_id' => 'int',
                'track_id' => 'int',
                'condition_id' => 'int',
                'type_id' => 'int',
                'minute' => 'int',
                'second' => 'int',
                'millisecond' => 'int',
                'pending' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['track_id'],
                $_POST['condition'],
                $_POST['type'],
                $_POST['minute'],
                $_POST['second'],
                $_POST['millisecond'],
                $pending,
            ),
            array(// no data
            )
        );
        $context['lap_id'] = $smcFunc['db_insert_id']($request);

    }

    // Perform video actions if enabled
    if ($smfgSettings['enable_laptime_video'] && $_POST['video_url'] > 'http://') {

        // Check for video title
        if (empty($_POST['video_title'])) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_title_required', false);
        }

        // Check for video URL compatibility
        if (!displayVideo($_POST['video_url'], 4)) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_unsupported', false);
        }

        // Insert video
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video',
            array(
                'vehicle_id' => 'int',
                'url' => 'string',
                'title' => 'string',
                'video_desc' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['video_url'],
                $_POST['video_title'],
                $_POST['video_desc'],
            ),
            array(// no data
            )
        );
        $context['video_id'] = $smcFunc['db_insert_id']($request);

        // Insert table data for video_galleryint
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video_gallery',
            array(
                'vehicle_id' => 'int',
                'video_id' => 'int',
                'type' => 'string',
                'type_id' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['video_id'],
                'lap',
                $context['lap_id'],
            ),
            array(// no data
            )
        );

    }

    // Send out Notifications
    if ($smfgSettings['enable_lap_approval']) {
        sendGarageNotifications();
    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=view_own_vehicle;VID='.$context['vehicle_id'].'#laps');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Edit Laptime
function G_Edit_Laptime()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;


    $context['sub_template'] = 'edit_laptime';
    $context['max_image_bytes'] = $smfgSettings['max_image_kbytes'] * 1024;
    $context['page_title'] = $txt['smfg_edit_laptime'];

    // Validate Owner
    checkOwner($_GET['VID']);

    // Session variable set?
    if (!isset($_SESSION['added_track'])) {
        $_SESSION['added_track'] = 0;
    }

    // Make sure this module is enabled
    if (!$smfgSettings['enable_laptimes']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build the link tree
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=edit_laptime;VID=' . $_GET['VID'] . ';LID=' . $_GET['LID'],
        'name' => &$txt['smfg_edit_laptime']
    );

    $request = $smcFunc['db_query']('', '
        SELECT track_id, condition_id, type_id, minute, second, millisecond
        FROM {db_prefix}garage_laps
        WHERE id = {int:lid}',
        array(
            'lid' => $_GET['LID'],
        )
    );
    list($context['laps']['track_id'],
        $context['laps']['condition_id'],
        $context['laps']['type_id'],
        $context['laps']['minute'],
        $context['laps']['second'],
        $context['laps']['millisecond']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Select image data
    $request = $smcFunc['db_query']('', '
        SELECT image_id, hilite
        FROM {db_prefix}garage_laps_gallery
        WHERE lap_id = {int:lid}',
        array(
            'lid' => $_GET['LID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['laps'][$count]['image_id'],
            $context['laps'][$count]['hilite']) = $row;

        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_desc, is_remote
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $context['laps'][$count]['image_id'],
            )
        );
        list($context['laps'][$count]['attach_location'],
            $context['laps'][$count]['attach_file'],
            $context['laps'][$count]['attach_thumb_location'],
            $context['laps'][$count]['attach_thumb_width'],
            $context['laps'][$count]['attach_thumb_height'],
            $context['laps'][$count]['attach_desc'],
            $context['laps'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        //get the track title for tooltip
        $request2 = $smcFunc['db_query']('', '
            SELECT {db_prefix}garage_tracks.title
            FROM {db_prefix}garage_tracks, {db_prefix}garage_laps
            WHERE {db_prefix}garage_laps.track_id = {db_prefix}garage_tracks.id
                AND {db_prefix}garage_laps.id = {int:lid}',
            array(
                'lid' => $_GET['LID'],
            )
        );
        list($context['laps'][$count]['title']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        if (empty($context['laps'][$count]['attach_desc'])) {
            $context['laps'][$count]['attach_desc'] = $txt['smfg_no_desc'];
        }

        // Check to see if the image is remote or not and build appropriate links
        if ($context['laps'][$count]['is_remote'] == 1) {
            $context['laps'][$count]['attach_location'] = urldecode($context['laps'][$count]['attach_file']);
        } else {
            $context['laps'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['laps'][$count]['attach_location'];
        }

        // If there is an image attached, link to it
        if (isset($context['laps'][$count]['attach_location'])) {
            $context['laps'][$count]['image'] = "<a href=\"" . $context['laps'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['laps']['minute'] . ':' . $context['laps']['second'] . ':' . $context['laps']['millisecond'] . ' @ ' . $context['laps'][$count]['title'] . ' :: ' . $context['laps'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $boardurl . "/" . $smfgSettings['upload_directory'] . 'cache/' . $context['laps'][$count]['attach_thumb_location'] . "\" width=\"" . $context['laps'][$count]['attach_thumb_width'] . "\" height=\"" . $context['laps'][$count]['attach_thumb_height'] . "\" alt=\"\" /></a>";
        }

        $count++;
    }

    // Select video data
    $request = $smcFunc['db_query']('', '
        SELECT video_id
        FROM {db_prefix}garage_video_gallery
        WHERE vehicle_id = {int:vid}
            AND type = "lap"
            AND type_id = {int:lid}',
        array(
            'vid' => $_GET['VID'],
            'lid' => $_GET['LID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['laps'][$count]['video_id']) = $row;

        // Select video data if there is any
        if (!empty($context['laps'][$count]['video_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT url, title, video_desc
                FROM {db_prefix}garage_video 
                WHERE id = {int:video_id}',
                array(
                    'video_id' => $context['laps'][$count]['video_id'],
                )
            );
            list($context['laps'][$count]['video_url'],
                $context['laps'][$count]['video_title'],
                $context['laps'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);
            if (empty($context['laps'][$count]['video_desc'])) {
                $context['laps'][$count]['video_desc'] = $txt['smfg_no_desc'];
            }
            $context['laps'][$count]['video_thumb'] = displayVideo($context['laps'][$count]['video_url'], 2);
            $context['laps'][$count]['video_height'] = displayVideo($context['laps'][$count]['video_url'], 'height');
            $context['laps'][$count]['video_width'] = displayVideo($context['laps'][$count]['video_url'], 'width');
        }
        $count++;
    }

}

// Update Laptime
function G_Update_Laptime()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_laptimes']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_laps
        SET track_id = {int:track_id}, condition_id = {int:condit}, type_id = {int:type}, minute = {int:minute}, second = {int:second}, millisecond = {int:millisecond}
        WHERE id = {int:lid}',
        array(
            'track_id' => $_POST['track_id'],
            'condition' => $_POST['condition'],
            'type' => $_POST['type'],
            'minute' => $_POST['minute'],
            'second' => $_POST['second'],
            'millisecond' => $_POST['millisecond'],
            'lid' => $_POST['LID'],
        )
    );

    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Delete Laptime
function G_Delete_Laptime()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boarddir;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Set image directory
    $dir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $cachedir = $dir . 'cache/';

    // Get image IDs
    $request = $smcFunc['db_query']('', '
        SELECT image_id
        FROM {db_prefix}garage_laps_gallery
        WHERE lap_id = {int:lid}',
        array(
            'lid' => $_GET['LID'],
        )
    );
    while ($row = $smcFunc['db_fetch_row']($request)) {
        $images['id'] = $row[0];
        // Get image filenames
        $request2 = $smcFunc['db_query']('', '
            SELECT attach_location, attach_thumb_location
            FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
        while ($row2 = $smcFunc['db_fetch_row']($request2)) {
            $images['filename'] = $row2[0];
            $images['thumb_filename'] = $row2[1];
            // Destroy the images
            unlink($dir . $images['filename']);
            unlink($cachedir . $images['filename']);
            unlink($cachedir . $images['thumb_filename']);
        }
        $smcFunc['db_free_result']($request2);

        // Delete row from garage_images
        $request2 = $smcFunc['db_query']('', '
            DELETE FROM {db_prefix}garage_images
            WHERE attach_id = {int:attach_id}',
            array(
                'attach_id' => $images['id'],
            )
        );
    }
    $smcFunc['db_free_result']($request);

    // Delete rows from laps_gallery
    $request2 = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_laps_gallery
        WHERE lap_id = {int:lid}',
        array(
            'lid' => $_GET['LID'],
        )
    );

    // Delete the laptime
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_laps
        WHERE id = {int:lid}',
        array(
            'lid' => $_GET['LID'],
        )
    );

    // ...and send them on their way
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Add Service
function G_Add_Service()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'add_service';

    // Validate Owner
    checkOwner($_GET['VID']);

    // Session variable set?
    if (!isset($_SESSION['added_garage'])) {
        $_SESSION['added_garage'] = 0;
    }

    // Make sure this module is enabled
    if (!$smfgSettings['enable_service']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    $context['page_title'] = $txt['smfg_add_service'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=add_service;VID=' . $_GET['VID'],
        'name' => &$txt['smfg_add_service']
    );

}

// Insert Service
function G_Insert_Service()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_service']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    $date_created = time();

    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_service_history',
        array(
            'vehicle_id' => 'int',
            'garage_id' => 'int',
            'type_id' => 'int',
            'price' => 'string',
            'rating' => 'int',
            'mileage' => 'int',
            'date_created' => 'int',
            'date_updated' => 'int',
        ),
        array(
            $_POST['VID'],
            $_POST['garage_id'],
            $_POST['type_id'],
            $_POST['price'],
            $_POST['rating'],
            $_POST['mileage'],
            $date_created,
            $date_created,
        ),
        array(// no data
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=view_own_vehicle;VID='.$_POST['VID'].'#services');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Edit Service
function G_Edit_Service()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'edit_service';
    $context['page_title'] = $txt['smfg_edit_service'];

    // Validate Owner
    checkOwner($_GET['VID']);

    // Session variable set?
    if (!isset($_SESSION['added_garage'])) {
        $_SESSION['added_garage'] = 0;
    }

    // Make sure this module is enabled
    if (!$smfgSettings['enable_service']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build the link tree
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=edit_service;VID=' . $_GET['VID'] . ';SID=' . $_GET['SID'],
        'name' => &$txt['smfg_edit_service']
    );

    // Get service data
    $request = $smcFunc['db_query']('', '
        SELECT id, garage_id, type_id, price, rating, mileage
        FROM {db_prefix}garage_service_history
        WHERE id = {int:sid}',
        array(
            'sid' => $_GET['SID'],
        )
    );
    list($context['services']['id'],
        $context['services']['garage_id'],
        $context['services']['type_id'],
        $context['services']['price'],
        $context['services']['rating'],
        $context['services']['mileage']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($context['services']['rating'] == 10) {
        $context['rat_10'] = "selected=\"selected\"";
    } else {
        if ($context['services']['rating'] == 9) {
            $context['rat_9'] = "selected=\"selected\"";
        } else {
            if ($context['services']['rating'] == 8) {
                $context['rat_8'] = "selected=\"selected\"";
            } else {
                if ($context['services']['rating'] == 7) {
                    $context['rat_7'] = "selected=\"selected\"";
                } else {
                    if ($context['services']['rating'] == 6) {
                        $context['rat_6'] = "selected=\"selected\"";
                    } else {
                        if ($context['services']['rating'] == 5) {
                            $context['rat_5'] = "selected=\"selected\"";
                        } else {
                            if ($context['services']['rating'] == 4) {
                                $context['rat_4'] = "selected=\"selected\"";
                            } else {
                                if ($context['services']['rating'] == 3) {
                                    $context['rat_3'] = "selected=\"selected\"";
                                } else {
                                    if ($context['services']['rating'] == 2) {
                                        $context['rat_2'] = "selected=\"selected\"";
                                    } else {
                                        if ($context['services']['rating'] == 1) {
                                            $context['rat_1'] = "selected=\"selected\"";
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

    if (!isset($context['rat_10'])) {
        $context['rat_10'] = "";
    }
    if (!isset($context['rat_9'])) {
        $context['rat_9'] = "";
    }
    if (!isset($context['rat_8'])) {
        $context['rat_8'] = "";
    }
    if (!isset($context['rat_7'])) {
        $context['rat_7'] = "";
    }
    if (!isset($context['rat_6'])) {
        $context['rat_6'] = "";
    }
    if (!isset($context['rat_5'])) {
        $context['rat_5'] = "";
    }
    if (!isset($context['rat_4'])) {
        $context['rat_4'] = "";
    }
    if (!isset($context['rat_3'])) {
        $context['rat_3'] = "";
    }
    if (!isset($context['rat_2'])) {
        $context['rat_2'] = "";
    }
    if (!isset($context['rat_1'])) {
        $context['rat_1'] = "";
    }

}

// Update Service
function G_Update_Service()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_service']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    $date_updated = time();

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_service_history
        SET garage_id = {int:garage_id}, type_id = {int:type_id}, price = {int:price}, rating = {int:rating}, mileage = {int:mileage}, date_updated = {int:date_updated}
        WHERE id = {int:sid}',
        array(
            'garage_id' => $_POST['garage_id'],
            'type_id' => $_POST['type_id'],
            'price' => $_POST['price'],
            'rating' => $_POST['rating'],
            'mileage' => $_POST['mileage'],
            'date_updated' => $date_updated,
            'sid' => $_POST['SID'],
        )
    );

    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Delete Service
function G_Delete_Service()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Delete the service
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_service_history
        WHERE id = {int:sid}',
        array(
            'sid' => $_GET['SID'],
        )
    );

    // ...and send them on their way
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Blog Post
function G_Insert_Blog()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_blogs']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    $date_created = time();
    $blog_text = str_replace(";", "{SEMICOLON}", $_POST['blog_text']);
    $blog_title = str_replace(";", "{SEMICOLON}", $_POST['blog_title']);

    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_blog',
        array(
            'vehicle_id' => 'int',
            'user_id' => 'int',
            'blog_title' => 'string',
            'blog_text' => 'string',
            'post_date' => 'int',
        ),
        array(
            $_POST['VID'],
            $_POST['user_id'],
            $blog_title,
            $blog_text,
            $date_created,
        ),
        array(// no data
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=view_own_vehicle;VID='.$_POST['VID'].'#blog');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Edit Blog
function G_Edit_Blog()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'edit_blog';
    $context['page_title'] = $txt['smfg_edit_blog'];

    // Validate Owner
    checkOwner($_GET['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_blogs']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build the link tree
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['vehicle']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=edit_blog;VID=' . $_GET['VID'] . ';BID=' . $_GET['BID'],
        'name' => &$txt['smfg_edit_blog']
    );

    // Get the blog post
    $request = $smcFunc['db_query']('', '
        SELECT blog_title, blog_text, post_date
        FROM {db_prefix}garage_blog
        WHERE id = {int:bid}',
        array(
            'bid' => $_GET['BID'],
        )
    );
    list($context['blog']['title'],
        $context['blog']['text'],
        $context['blog']['post_date']) = $smcFunc['db_fetch_row']($request);
    $context['blog']['text'] = str_replace("{SEMICOLON}", ";", $context['blog']['text']);
    $smcFunc['db_free_result']($request);

}

// Update Blog
function G_Update_Blog()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Validate Owner
    checkOwner($_POST['VID']);

    // Make sure this module is enabled
    if (!$smfgSettings['enable_blogs']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    $blog_text = str_replace(";", "{SEMICOLON}", $_POST['blog_text']);
    $blog_title = str_replace(";", "{SEMICOLON}", $_POST['blog_title']);

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_blog
        SET blog_title = {string:blog_title}, blog_text = {string:blog_text}
        WHERE id = {int:bid}',
        array(
            'blog_title' => $blog_title,
            'blog_text' => $blog_text,
            'bid' => $_POST['BID'],
        )
    );
    //header( 'Location: '.$scripturl.'?action=garage;sa=view_own_vehicle;VID='.$_POST['VID'].';#blog');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Delete Blog
function G_Delete_Blog()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Delete the blog post
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_blog
        WHERE id = {int:bid}',
        array(
            'bid' => $_GET['BID'],
        )
    );

    // ...and send them on their way
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Garage Comment
function G_Insert_Garage_Comment()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Check Permissions
    isAllowedTo('post_comments');

    // Make sure this module is enabled
    if (!$smfgSettings['enable_guestbooks']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    $date_created = time();
    $client_ip = $_SERVER["REMOTE_ADDR"];
    $comment = str_replace(";", "{SEMICOLON}", $_POST['post']);

    // Check if approval is required
    if ($smfgSettings['enable_guestbooks_comment_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_comments',
        array(
            'user_id' => 'int',
            'author_id' => 'int',
            'post_date' => 'int',
            'ip_address' => 'string',
            'pending' => 'string',
            'post' => 'string',
        ),
        array(
            $_POST['UID'],
            $_POST['user_id'],
            $date_created,
            $client_ip,
            $pending,
            $comment,
        ),
        array(// no data
        )
    );

    // Send out Notifications
    if ($smfgSettings['enable_guestbooks_comment_approval']) {
        sendGarageNotifications();
    }

    // Whos gb?
    $request = $smcFunc['db_query']('', '
        SELECT user_id
        FROM {db_prefix}garage_vehicles
        WHERE user_id = {int:uid}',
        array(
            'uid' => $_POST['UID'],
        )
    );
    list($ownerId) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if they want gb notifications
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_notifications_misc
        WHERE user_id = {int:user_id}
            AND gb_opt_out = 1',
        array(
            'user_id' => $ownerId,
        )
    );
    $numResults = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($numResults == 0) {
        // Get author name
        $request = $smcFunc['db_query']('', '
            SELECT real_name
            FROM {db_prefix}members
            WHERE id_member = {int:id_member}',
            array(
                'id_member' => $_POST['user_id'],
            )
        );
        list($authorName) = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);
        sendOtherNotifications($ownerId, str_replace('@AUTHOR@', $authorName, $txt['smfg_comment_notification']),
            str_replace(array('@AUTHOR@', '@UID@'), array($authorName, $_POST['UID']), $txt['smfg_comment_pm']));
    }

    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Edit Garage Comment
function G_Edit_Garage_Comment()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    // Check Permissions
    isAllowedTo('post_comments');

    $context['sub_template'] = 'edit_garage_comment';

    // Make sure this module is enabled
    if (!$smfgSettings['enable_guestbooks']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Make sure this is their comment, dont want people editting other peoples comments do we?
    // Well, only if they are allowed to...
    $request = $smcFunc['db_query']('', '
        SELECT author_id
        FROM {db_prefix}garage_comments
        WHERE id = {int:cid}',
        array(
            'cid' => $_GET['CID'],
        )
    );
    list($context['author_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($context['author_id'] != $context['user']['id'] && !allowedTo('edit_all_comments')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_edit_comment_error', false);
    }

    // Build the link tree
    $context['page_title'] = $txt['smfg_edit_comment'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=edit_garage_comment;UID=' . $_GET['UID'] . ';CID=' . $_GET['CID'],
        'name' => &$txt['smfg_edit_comment']
    );

    // Get the comment
    $request = $smcFunc['db_query']('', '
        SELECT post
        FROM {db_prefix}garage_comments
        WHERE id = {int:cid}',
        array(
            'cid' => $_GET['CID'],
        )
    );
    list($context['comments']['post']) = $smcFunc['db_fetch_row']($request);
    $context['comments']['post'] = str_replace("{SEMICOLON}", ";", $context['comments']['post']);
    $smcFunc['db_free_result']($request);

}

// Update Garage Comment
function G_Update_Garage_Comment()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Check Permissions
    isAllowedTo('post_comments');

    // Make sure this module is enabled
    if (!$smfgSettings['enable_guestbooks']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Make sure this is their comment, dont want people editting other peoples comments do we?
    // Well, only if they are allowed to...
    $request = $smcFunc['db_query']('', '
        SELECT author_id
        FROM {db_prefix}garage_comments
        WHERE id = {int:cid}',
        array(
            'cid' => $_POST['CID'],
        )
    );
    list($context['author_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($context['author_id'] != $context['user']['id'] && !allowedTo('edit_all_comments')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_edit_comment_error', false);
    }

    $comment = str_replace(";", "{SEMICOLON}", $_POST['post']);

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_comments
        SET post = {string:comment}
        WHERE id = {int:cid}',
        array(
            'comment' => $comment,
            'cid' => $_POST['CID'],
        )
    );
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Delete Garage Comment
function G_Delete_Garage_Comment()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Make sure this is their comment, dont want people deleting other peoples comments do we?
    $request = $smcFunc['db_query']('', '
        SELECT author_id
        FROM {db_prefix}garage_comments
        WHERE id = {int:cid}',
        array(
            'cid' => $_GET['CID'],
        )
    );
    list($context['author_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Well, only if they are allowed to...
    if ($context['author_id'] != $context['user']['id'] && !allowedTo('edit_all_comments')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_edit_comment_error', false);
    }

    // Delete the comment
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_comments
        WHERE id = {int:cid}',
        array(
            'cid' => $_GET['CID'],
        )
    );

    // ...and send them on their way
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Guestbook Comment
function G_Insert_Comment()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Check Permissions
    isAllowedTo('post_comments');

    // Make sure this module is enabled
    if (!$smfgSettings['enable_guestbooks']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    $date_created = time();
    $client_ip = $_SERVER["REMOTE_ADDR"];
    $comment = str_replace(";", "{SEMICOLON}", $_POST['post']);

    // Check if approval is required
    if ($smfgSettings['enable_guestbooks_comment_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_guestbooks',
        array(
            'vehicle_id' => 'int',
            'author_id' => 'int',
            'post_date' => 'int',
            'ip_address' => 'string',
            'pending' => 'string',
            'post' => 'string',
        ),
        array(
            $_POST['VID'],
            $_POST['user_id'],
            $date_created,
            $client_ip,
            $pending,
            $comment,
        ),
        array(// no data
        )
    );

    // Send out Notifications
    if ($smfgSettings['enable_guestbooks_comment_approval']) {
        sendGarageNotifications();
    }

    // Whos gb?
    $request = $smcFunc['db_query']('', '
        SELECT user_id
        FROM {db_prefix}garage_vehicles
        WHERE id = {int:vid}',
        array(
            'vid' => $_POST['VID'],
        )
    );
    list($ownerId) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if they want gb notifications
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_notifications_misc
        WHERE user_id = {int:user_id}
            AND gb_opt_out = 1',
        array(
            'user_id' => $ownerId,
        )
    );
    $numResults = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($numResults == 0) {
        // Get author name
        $request = $smcFunc['db_query']('', '
            SELECT real_name
            FROM {db_prefix}members
            WHERE id_member = {int:user_id}',
            array(
                'user_id' => $_POST['user_id'],
            )
        );
        list($authorName) = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);
        sendOtherNotifications($ownerId, str_replace('@AUTHOR@', $authorName, $txt['smfg_gb_notification']),
            str_replace(array('@AUTHOR@', '@VID@'), array($authorName, $_POST['VID']), $txt['smfg_gb_pm']));
    }

    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Edit Guestbook Comment
function G_Edit_Comment()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    // Check Permissions
    isAllowedTo('post_comments');

    $context['sub_template'] = 'edit_comment';

    // Make sure this module is enabled
    if (!$smfgSettings['enable_guestbooks']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get vehicle data for link tree
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['vehicle']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Make sure this is their comment, dont want people editing other peoples comments do we?
    // Well, only if they are allowed to...
    $request = $smcFunc['db_query']('', '
        SELECT author_id
        FROM {db_prefix}garage_guestbooks
        WHERE id = {int:cid}',
        array(
            'cid' => $_GET['CID'],
        )
    );
    list($context['author_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($context['author_id'] != $context['user']['id'] && !allowedTo('edit_all_comments')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_edit_comment_error', false);
    }

    // If this is their vehicle and they are editting a comment, the link tree should link to view_own_vehicle
    if ($context['user_vehicles']['user_id'] == $context['user']['id']) {

        // Build the link tree
        $context['linktree'][] = array(
            'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
            'name' => $context['user_vehicles']['vehicle']
        );

    } else {

        // Build the link tree
        $context['linktree'][] = array(
            'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
            'name' => $context['user_vehicles']['vehicle']
        );

    }

    $context['page_title'] = $txt['smfg_edit_comment'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=edit_comment;VID=' . $_GET['VID'] . ';CID=' . $_GET['CID'],
        'name' => &$txt['smfg_edit_comment']
    );

    // Get the comment
    $request = $smcFunc['db_query']('', '
        SELECT post
        FROM {db_prefix}garage_guestbooks
        WHERE id = {int:cid}',
        array(
            'cid' => $_GET['CID'],
        )
    );
    list($context['gb']['comment']) = $smcFunc['db_fetch_row']($request);
    $context['gb']['comment'] = str_replace("{SEMICOLON}", ";", $context['gb']['comment']);
    $smcFunc['db_free_result']($request);

}

// Update Guestbook Comment
function G_Update_Comment()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Check Permissions
    isAllowedTo('post_comments');

    // Make sure this module is enabled
    if (!$smfgSettings['enable_guestbooks']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Make sure this is their comment, dont want people editting other peoples comments do we?
    // Well, only if they are allowed to...
    $request = $smcFunc['db_query']('', '
        SELECT author_id
        FROM {db_prefix}garage_guestbooks
        WHERE id = {int:cid}',
        array(
            'cid' => $_POST['CID'],
        )
    );
    list($context['author_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($context['author_id'] != $context['user']['id'] && !allowedTo('edit_all_comments')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_edit_comment_error', false);
    }

    $comment = str_replace(";", "{SEMICOLON}", $_POST['post']);

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_guestbooks
        SET post = {string:comment}
        WHERE id = {int:cid}',
        array(
            'comment' => $comment,
            'cid' => $_POST['CID'],
        )
    );

    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Delete Guestbook Comment
function G_Delete_Comment()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Make sure this is their comment, dont want people deleting other peoples comments do we?
    $request = $smcFunc['db_query']('', '
        SELECT author_id
        FROM {db_prefix}garage_guestbooks
        WHERE id = {int:cid}',
        array(
            'cid' => $_GET['CID'],
        )
    );
    list($context['author_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Well, only if they are allowed to...
    if ($context['author_id'] != $context['user']['id'] && !allowedTo('edit_all_comments')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_edit_comment_error', false);
    }

    // Delete the comment
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_guestbooks
        WHERE id = {int:cid}',
        array(
            'cid' => $_GET['CID'],
        )
    );

    // ...and send them on their way
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// View Modification
function G_View_Modification()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'view_modification';
    $context['date_format'] = $smfgSettings['dateformat'];

    // Make sure this module is enabled
    if (!$smfgSettings['enable_modification']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Make sure the user didn't access this page directly
    if (!isset($_GET['VID']) || !isset($_GET['MID'])) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_direct_page_access', false);
    }

    // Check and make sure the vehicle is in the db
    $request = $smcFunc['db_query']('', '
        SELECT v.id
        FROM {db_prefix}garage_vehicles AS v
        WHERE v.id = {int:vid}
        LIMIT 1',
        array(
            'vid' => $_GET['VID'],
        )
    );
    $matching_vid = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_vid <= 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_vehicle', false);
    }

    // Check and make sure the modification is in the db
    $request = $smcFunc['db_query']('', '
        SELECT m.id
        FROM {db_prefix}garage_modifications AS m
        WHERE m.id = {int:mid}
        LIMIT 1',
        array(
            'mid' => $_GET['MID'],
        )
    );
    $matching_mid = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_mid <= 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_modification', false);
    }

    // Gather vehicle data
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model) as title, c.title, u.real_name, v.pending
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u, {db_prefix}garage_currency AS c
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id
            AND v.currency = c.id
            AND id_member = v.user_id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['title'],
        $context['user_vehicles']['currency'],
        $context['mods']['owner'],
        $context['user_vehicles']['pending']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if vehicle is pending, if they are the owner, or if they have permission to view pending items
    if ($context['user_vehicles']['pending'] == '1' && $context['user_vehicles']['user_id'] != $context['user']['id'] && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_vehicle_error', false);
    }

    $request = $smcFunc['db_query']('', '
        SELECT b.title, m.manufacturer_id, c.title, p.title
        FROM {db_prefix}garage_business AS b, {db_prefix}garage_categories AS c, {db_prefix}garage_modifications AS m, {db_prefix}garage_products AS p
        WHERE m.manufacturer_id = b.id
            AND m.category_id = c.id
            AND m.product_id = p.id
            AND m.id = {int:mid}',
        array(
            'mid' => $_GET['MID'],
        )
    );
    list($context['mods']['manufacturer'],
        $context['mods']['manufacturer_id'],
        $context['mods']['category'],
        $context['mods']['product']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Build Link tree after mod info is gathered
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['title']
    );
    $context['page_title'] = $context['user_vehicles']['title'] . ' &gt; ' . $context['mods']['product'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_modification;VID=' . $_GET['VID'] . ';MID=' . $_GET['MID'] . '',
        'name' => $context['mods']['product']
    );

    // Gather mod data
    $request = $smcFunc['db_query']('', '
        SELECT m.price, m.install_price, m.product_rating, m.purchase_rating, m.install_rating, m.shop_id, m.installer_id, m.comments, m.date_updated, IF(m.pending = "1" OR b.pending = "1",1,0) AS pending
        FROM {db_prefix}garage_modifications AS m, {db_prefix}garage_business AS b
        WHERE m.id = {int:mid}
            AND m.manufacturer_id = b.id',
        array(
            'mid' => $_GET['MID'],
        )
    );
    list($context['mods']['price'],
        $context['mods']['install_price'],
        $context['mods']['product_rating'],
        $context['mods']['purchase_rating'],
        $context['mods']['install_rating'],
        $context['mods']['shop_id'],
        $context['mods']['installer_id'],
        $context['mods']['comments'],
        $context['mods']['date_updated'],
        $context['mods']['pending']) = $smcFunc['db_fetch_row']($request);
    $context['mods']['price'] = number_format($context['mods']['price'], 2, '.', ',');
    $context['mods']['install_price'] = number_format($context['mods']['install_price'], 2, '.', ',');
    $smcFunc['db_free_result']($request);

    // Check if the mod is pending, if they are the owner, or if they have permission to view pending items
    if ($context['mods']['pending'] && $context['user_vehicles']['user_id'] != $context['user']['id'] && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_item_error', false);
    }

    // Shop ID?
    if (!empty($context['mods']['shop_id'])) {

        // Get shop name
        $request = $smcFunc['db_query']('', '
            SELECT title, pending
            FROM {db_prefix}garage_business
            WHERE id = {int:shop_id}',
            array(
                'shop_id' => $context['mods']['shop_id'],
            )
        );
        list($context['mods']['shop'],
            $context['mods']['shop_pending']) = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);

        // Check if the shop is pending, if they are the owner, or if they have permission to view pending items
        if ($context['mods']['shop_pending'] == '1' && $context['user_vehicles']['user_id'] != $context['user']['id'] && !allowedTo('manage_garage_pending')) {
            loadLanguage('Errors');
            fatal_lang_error('garage_pending_item_error', false);
        }
    }

    // Installer ID?
    if (!empty($context['mods']['installer_id'])) {

        // Get installer name
        $request = $smcFunc['db_query']('', '
            SELECT title, pending
            FROM {db_prefix}garage_business
            WHERE id = {int:installer_id}',
            array(
                'installer_id' => $context['mods']['installer_id'],
            )
        );
        list($context['mods']['installer'],
            $context['mods']['installer_pending']) = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);

        // Check if the installer is pending, if they are the owner, or if they have permission to view pending items
        if ($context['mods']['installer_pending'] == '1' && $context['user_vehicles']['user_id'] != $context['user']['id'] && !allowedTo('manage_garage_pending')) {
            loadLanguage('Errors');
            fatal_lang_error('garage_pending_item_error', false);
        }
    }

    // Select image id
    $request = $smcFunc['db_query']('', '
        SELECT image_id, hilite 
        FROM {db_prefix}garage_modifications_gallery
        WHERE modification_id = {int:mid}',
        array(
            'mid' => $_GET['MID'],
        )
    );
    $count = 0;
    $context['hilite_image_location'] = "";
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['mods'][$count]['image_id'],
            $context['mods'][$count]['hilite']) = $row;

        // Select image data if there is any
        if (!empty($context['mods'][$count]['image_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT attach_location, attach_hits, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_desc, is_remote
                FROM {db_prefix}garage_images 
                WHERE attach_id = {int:attach_id}',
                array(
                    'attach_id' => $context['mods'][$count]['image_id'],
                )
            );
            list($context['mods'][$count]['attach_location'],
                $context['mods'][$count]['attach_hits'],
                $context['mods'][$count]['attach_file'],
                $context['mods'][$count]['attach_thumb_location'],
                $context['mods'][$count]['attach_thumb_width'],
                $context['mods'][$count]['attach_thumb_height'],
                $context['mods'][$count]['attach_desc'],
                $context['mods'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);

            if (empty($context['mods'][$count]['attach_desc'])) {
                $context['mods'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }

            if ($context['mods'][$count]['hilite'] == 1) {
                if ($context['mods'][$count]['is_remote'] == 1) {
                    $context['hilite_image_location'] = urldecode($context['mods'][$count]['attach_location']);
                } else {
                    $context['hilite_image_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['mods'][$count]['attach_location'];
                }
                $context['hilite_thumb_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['mods'][$count]['attach_thumb_location'];
                $context['hilite_thumb_width'] = $context['mods'][$count]['attach_thumb_width'];
                $context['hilite_thumb_height'] = $context['mods'][$count]['attach_thumb_height'];
                $context['hilite_desc'] = $context['mods'][$count]['attach_desc'];
            }
            if ($context['mods'][$count]['is_remote'] == 1) {
                $context['mods'][$count]['attach_location'] = urldecode($context['mods'][$count]['attach_location']);
            } else {
                $context['mods'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['mods'][$count]['attach_location'];
            }
            $context['mods'][$count]['attach_thumb_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['mods'][$count]['attach_thumb_location'];
            $count++;
        }
    }
    $smcFunc['db_free_result']($request);

    // Select video id
    $request = $smcFunc['db_query']('', '
        SELECT video_id
        FROM {db_prefix}garage_video_gallery
        WHERE vehicle_id = {int:vid}
            AND type = "mod"
            AND type_id = {int:mid}',
        array(
            'vid' => $_GET['VID'],
            'mid' => $_GET['MID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['mods'][$count]['video_id']) = $row;

        // Select video data if there is any
        if (!empty($context['mods'][$count]['video_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT url, title, video_desc
                FROM {db_prefix}garage_video 
                WHERE id = {int:video_id}',
                array(
                    'video_id' => $context['mods'][$count]['video_id'],
                )
            );
            list($context['mods'][$count]['video_url'],
                $context['mods'][$count]['video_title'],
                $context['mods'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);

            if (empty($context['mods'][$count]['video_desc'])) {
                $context['mods'][$count]['video_desc'] = $txt['smfg_no_desc'];
            }

            $context['mods'][$count]['video_thumb'] = displayVideo($context['mods'][$count]['video_url'], 2);
            $context['mods'][$count]['video_height'] = displayVideo($context['mods'][$count]['video_url'], 'height');
            $context['mods'][$count]['video_width'] = displayVideo($context['mods'][$count]['video_url'], 'width');
        }
        $count++;
    }

}

// View Quartermile
function G_View_Quartermile()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'view_quartermile';
    $context['date_format'] = $smfgSettings['dateformat'];
    $context['page_title'] = $txt['smfg_view_quartermile'];

    // Check Permissions
    isAllowedTo('view_qms');

    // Make sure this module is enabled
    if (!$smfgSettings['enable_quartermile']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Make sure the user didn't access this page directly
    if (!isset($_GET['VID']) || !isset($_GET['QID'])) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_direct_page_access', false);
    }

    // Check and make sure the vehicle is in the db
    $request = $smcFunc['db_query']('', '
        SELECT v.id
        FROM {db_prefix}garage_vehicles AS v
        WHERE v.id = {int:vid}
        LIMIT 1',
        array(
            'vid' => $_GET['VID'],
        )
    );
    $matching_vid = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_vid <= 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_vehicle', false);
    }

    // Check and make sure the quartermile is in the db
    $request = $smcFunc['db_query']('', '
        SELECT q.id
        FROM {db_prefix}garage_quartermiles AS q
        WHERE q.id = {int:qid}
        LIMIT 1',
        array(
            'qid' => $_GET['QID'],
        )
    );
    $matching_qid = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_qid <= 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_quartermile', false);
    }

    // Gather vehicle data
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model) as title, v.pending, u.real_name
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id
            AND id_member = v.user_id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['title'],
        $context['user_vehicles']['pending'],
        $context['qmiles']['owner']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if vehicle is pending, if they are the owner, or if they have permission to view pending items
    if ($context['user_vehicles']['pending'] == '1' && $context['user_vehicles']['user_id'] != $context['user']['id'] && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_vehicle_error', false);
    }

    // Build Link tree
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['title']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_quartermile;VID=' . $_GET['VID'] . ';QID=' . $_GET['QID'],
        'name' => &$txt['smfg_view_quartermile']
    );

    // Gather qmile data
    $request = $smcFunc['db_query']('', '
        SELECT rt, sixty, three, eighth, eighthmph, thou, quart, quartmph, dynorun_id, date_created, date_updated, pending
        FROM {db_prefix}garage_quartermiles
        WHERE id = {int:qid}',
        array(
            'qid' => $_GET['QID'],
        )
    );
    list($context['qmiles']['rt'],
        $context['qmiles']['sixty'],
        $context['qmiles']['three'],
        $context['qmiles']['eighth'],
        $context['qmiles']['eighthmph'],
        $context['qmiles']['thou'],
        $context['qmiles']['quart'],
        $context['qmiles']['quartmph'],
        $context['qmiles']['dynorun_id'],
        $context['qmiles']['date_created'],
        $context['qmiles']['date_updated'],
        $context['qmiles']['pending']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if the qmile is pending, if they are the owner, or if they have permission to view pending items
    if ($context['qmiles']['pending'] == '1' && $context['user_vehicles']['user_id'] != $context['user']['id'] && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_item_error', false);
    }

    // Select image id
    $request = $smcFunc['db_query']('', '
        SELECT image_id, hilite 
        FROM {db_prefix}garage_quartermiles_gallery
        WHERE quartermile_id = {int:qid}',
        array(
            'qid' => $_GET['QID'],
        )
    );
    $count = 0;
    $context['hilite_image_location'] = "";
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['qmiles'][$count]['image_id'],
            $context['qmiles'][$count]['hilite']) = $row;

        // Select image data if there is any
        if (!empty($context['qmiles'][$count]['image_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT attach_location, attach_hits, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_desc, is_remote
                FROM {db_prefix}garage_images 
                WHERE attach_id = {int:image_id}',
                array(
                    'image_id' => $context['qmiles'][$count]['image_id'],
                )
            );
            list($context['qmiles'][$count]['attach_location'],
                $context['qmiles'][$count]['attach_hits'],
                $context['qmiles'][$count]['attach_file'],
                $context['qmiles'][$count]['attach_thumb_location'],
                $context['qmiles'][$count]['attach_thumb_width'],
                $context['qmiles'][$count]['attach_thumb_height'],
                $context['qmiles'][$count]['attach_desc'],
                $context['qmiles'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);

            if (empty($context['qmiles'][$count]['attach_desc'])) {
                $context['qmiles'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }

            if ($context['qmiles'][$count]['hilite'] == 1) {
                if ($context['qmiles'][$count]['is_remote'] == 1) {
                    $context['hilite_image_location'] = urldecode($context['qmiles'][$count]['attach_location']);
                } else {
                    $context['hilite_image_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['qmiles'][$count]['attach_location'];
                }
                $context['hilite_thumb_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['qmiles'][$count]['attach_thumb_location'];
                $context['hilite_thumb_width'] = $context['qmiles'][$count]['attach_thumb_width'];
                $context['hilite_thumb_height'] = $context['qmiles'][$count]['attach_thumb_height'];
                $context['hilite_desc'] = $context['qmiles'][$count]['attach_desc'];
            }
            if ($context['qmiles'][$count]['is_remote'] == 1) {
                $context['qmiles'][$count]['attach_location'] = urldecode($context['qmiles'][$count]['attach_location']);
            } else {
                $context['qmiles'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['qmiles'][$count]['attach_location'];
            }
            $context['qmiles'][$count]['attach_thumb_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['qmiles'][$count]['attach_thumb_location'];
        }
        $count++;
    }
    $smcFunc['db_free_result']($request);

    // Select video id
    $request = $smcFunc['db_query']('', '
        SELECT video_id
        FROM {db_prefix}garage_video_gallery
        WHERE vehicle_id = {int:vid}
            AND type = "qmile"
            AND type_id = {int:qid}',
        array(
            'vid' => $_GET['VID'],
            'qid' => $_GET['QID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['qmiles'][$count]['video_id']) = $row;

        // Select video data if there is any
        if (!empty($context['qmiles'][$count]['video_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT url, title, video_desc
                FROM {db_prefix}garage_video 
                WHERE id = {int:video_id}',
                array(
                    'video_id' => $context['qmiles'][$count]['video_id'],
                )
            );
            list($context['qmiles'][$count]['video_url'],
                $context['qmiles'][$count]['video_title'],
                $context['qmiles'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);

            if (empty($context['qmiles'][$count]['video_desc'])) {
                $context['qmiles'][$count]['video_desc'] = $txt['smfg_no_desc'];
            }

            $context['qmiles'][$count]['video_thumb'] = displayVideo($context['qmiles'][$count]['video_url'], 2);
            $context['qmiles'][$count]['video_height'] = displayVideo($context['qmiles'][$count]['video_url'],
                'height');
            $context['qmiles'][$count]['video_width'] = displayVideo($context['qmiles'][$count]['video_url'], 'width');
        }
        $count++;
    }

}

// View Dynorun
function G_View_Dynorun()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'view_dynorun';
    $context['date_format'] = $smfgSettings['dateformat'];
    $context['page_title'] = $txt['smfg_view_dynorun'];

    // Check Permissions
    isAllowedTo('view_dynos');

    // Make sure this module is enabled
    if (!$smfgSettings['enable_dynorun']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Make sure the user didn't access this page directly
    if (!isset($_GET['VID']) || !isset($_GET['DID'])) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_direct_page_access', false);
    }

    // Check and make sure the vehicle is in the db
    $request = $smcFunc['db_query']('', '
        SELECT v.id
        FROM {db_prefix}garage_vehicles AS v
        WHERE v.id = {int:vid}
        LIMIT 1',
        array(
            'vid' => $_GET['VID'],
        )
    );
    $matching_vid = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_vid <= 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_vehicle', false);
    }

    // Check and make sure the dynorun is in the db
    $request = $smcFunc['db_query']('', '
        SELECT d.id
        FROM {db_prefix}garage_dynoruns AS d
        WHERE d.id = {int:did}
        LIMIT 1',
        array(
            'did' => $_GET['DID'],
        )
    );
    $matching_did = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_did <= 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_dynorun', false);
    }

    // Gather vehicle data
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model) as title, v.pending, u.real_name
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id
            AND id_member = v.user_id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['title'],
        $context['user_vehicles']['pending'],
        $context['dynoruns']['owner']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if vehicle is pending, if they are the owner, or if they have permission to view pending items
    if ($context['user_vehicles']['pending'] == '1' && $context['user_vehicles']['user_id'] != $context['user']['id'] && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_vehicle_error', false);
    }

    // Build Link tree
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['title']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_dynorun;VID=' . $_GET['VID'] . ';DID=' . $_GET['DID'],
        'name' => &$txt['smfg_view_dynorun']
    );

    $request = $smcFunc['db_query']('', '
        SELECT d.dynocenter_id, d.bhp, d.bhp_unit, d.torque, d.torque_unit, d.boost, d.boost_unit, d.nitrous, d.peakpoint, d.date_created, d.date_updated, b.title, b.id, IF(d.pending = "1" OR b.pending = "1",1,0) AS pending
        FROM {db_prefix}garage_dynoruns AS d, {db_prefix}garage_business AS b
        WHERE d.id = {int:did}
            AND d.dynocenter_id = b.id',
        array(
            'did' => $_GET['DID'],
        )
    );
    list($context['dynoruns']['dynocenter_id'],
        $context['dynoruns']['bhp'],
        $context['dynoruns']['bhp_unit'],
        $context['dynoruns']['torque'],
        $context['dynoruns']['torque_unit'],
        $context['dynoruns']['boost'],
        $context['dynoruns']['boost_unit'],
        $context['dynoruns']['nitrous'],
        $context['dynoruns']['peakpoint'],
        $context['dynoruns']['date_created'],
        $context['dynoruns']['date_updated'],
        $context['dynoruns']['dynocenter'],
        $context['dynoruns']['dynocenter_id'],
        $context['dynoruns']['pending']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if the dynorun is pending, if they are the owner, or if they have permission to view pending items
    if ($context['dynoruns']['pending'] == '1' && $context['user_vehicles']['user_id'] != $context['user']['id'] && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_item_error', false);
    }

    // Select image id
    $request = $smcFunc['db_query']('', '
        SELECT image_id, hilite 
        FROM {db_prefix}garage_dynoruns_gallery
        WHERE dynorun_id = {int:did}',
        array(
            'did' => $_GET['DID'],
        )
    );
    $count = 0;
    $context['hilite_image_location'] = "";
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['dynoruns'][$count]['image_id'],
            $context['dynoruns'][$count]['hilite']) = $row;

        // Select image data if there is any
        if (!empty($context['dynoruns'][$count]['image_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT attach_location, attach_hits, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_desc, is_remote
                FROM {db_prefix}garage_images 
                WHERE attach_id = {int:attach_id}',
                array(
                    'attach_id' => $context['dynoruns'][$count]['image_id'],
                )
            );
            list($context['dynoruns'][$count]['attach_location'],
                $context['dynoruns'][$count]['attach_hits'],
                $context['dynoruns'][$count]['attach_file'],
                $context['dynoruns'][$count]['attach_thumb_location'],
                $context['dynoruns'][$count]['attach_thumb_width'],
                $context['dynoruns'][$count]['attach_thumb_height'],
                $context['dynoruns'][$count]['attach_desc'],
                $context['dynoruns'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);

            if (empty($context['dynoruns'][$count]['attach_desc'])) {
                $context['dynoruns'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }

            if ($context['dynoruns'][$count]['hilite'] == 1) {
                if ($context['dynoruns'][$count]['is_remote'] == 1) {
                    $context['hilite_image_location'] = urldecode($context['dynoruns'][$count]['attach_location']);
                } else {
                    $context['hilite_image_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['dynoruns'][$count]['attach_location'];
                }
                $context['hilite_thumb_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['dynoruns'][$count]['attach_thumb_location'];
                $context['hilite_thumb_width'] = $context['dynoruns'][$count]['attach_thumb_width'];
                $context['hilite_thumb_height'] = $context['dynoruns'][$count]['attach_thumb_height'];
                $context['hilite_desc'] = $context['dynoruns'][$count]['attach_desc'];
            }
            if ($context['dynoruns'][$count]['is_remote'] == 1) {
                $context['dynoruns'][$count]['attach_location'] = urldecode($context['dynoruns'][$count]['attach_location']);
            } else {
                $context['dynoruns'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['dynoruns'][$count]['attach_location'];
            }
            $context['dynoruns'][$count]['attach_thumb_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['dynoruns'][$count]['attach_thumb_location'];
            $count++;
        }
    }
    $smcFunc['db_free_result']($request);

    // Select video id
    $request = $smcFunc['db_query']('', '
        SELECT video_id
        FROM {db_prefix}garage_video_gallery
        WHERE vehicle_id = {int:vid}
            AND type = "dynorun"
            AND type_id = {int:did}',
        array(
            'vid' => $_GET['VID'],
            'did' => $_GET['DID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['dynoruns'][$count]['video_id']) = $row;

        // Select video data if there is any
        if (!empty($context['dynoruns'][$count]['video_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT url, title, video_desc
                FROM {db_prefix}garage_video 
                WHERE id = {int:video_id}',
                array(
                    'video_id' => $context['dynoruns'][$count]['video_id'],
                )
            );
            list($context['dynoruns'][$count]['video_url'],
                $context['dynoruns'][$count]['video_title'],
                $context['dynoruns'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);

            if (empty($context['dynoruns'][$count]['video_desc'])) {
                $context['dynoruns'][$count]['video_desc'] = $txt['smfg_no_desc'];
            }

            $context['dynoruns'][$count]['video_thumb'] = displayVideo($context['dynoruns'][$count]['video_url'], 2);
            $context['dynoruns'][$count]['video_height'] = displayVideo($context['dynoruns'][$count]['video_url'],
                'height');
            $context['dynoruns'][$count]['video_width'] = displayVideo($context['dynoruns'][$count]['video_url'],
                'width');
        }
        $count++;
    }

}

// View Laptime
function G_View_Laptime()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'view_laptime';
    $context['page_title'] = $txt['smfg_view_laptime'];

    // Check Permissions
    isAllowedTo('view_laps');

    // Make sure this module is enabled
    if (!$smfgSettings['enable_laptimes']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Make sure the user didn't access this page directly
    if (!isset($_GET['VID']) || !isset($_GET['LID'])) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_direct_page_access', false);
    }

    // Check and make sure the vehicle is in the db
    $request = $smcFunc['db_query']('', '
        SELECT v.id
        FROM {db_prefix}garage_vehicles AS v
        WHERE v.id = {int:vid}
        LIMIT 1',
        array(
            'vid' => $_GET['VID'],
        )
    );
    $matching_vid = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_vid <= 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_vehicle', false);
    }

    // Check and make sure the laptime is in the db
    $request = $smcFunc['db_query']('', '
        SELECT l.id
        FROM {db_prefix}garage_laps AS l
        WHERE l.id = {int:lid}
        LIMIT 1',
        array(
            'lid' => $_GET['LID'],
        )
    );
    $matching_lid = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_lid <= 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_laptime', false);
    }

    // Gather vehicle data
    $request = $smcFunc['db_query']('', '
        SELECT v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model) as title, v.pending, u.real_name
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
        WHERE v.id = {int:vid}
            AND v.make_id = mk.id
            AND v.model_id = md.id
            AND id_member = v.user_id',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['user_vehicles']['user_id'],
        $context['user_vehicles']['title'],
        $context['user_vehicles']['pending'],
        $context['laps']['owner']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if vehicle is pending, if they are the owner, or if they have permission to view pending items
    if ($context['user_vehicles']['pending'] == '1' && $context['user_vehicles']['user_id'] != $context['user']['id'] && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_vehicle_error', false);
    }

    // Build Link tree
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'],
        'name' => $context['user_vehicles']['title']
    );
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_laptime;VID=' . $_GET['VID'] . ';LID=' . $_GET['LID'],
        'name' => &$txt['smfg_view_laptime']
    );

    // Get Laptimes
    $request = $smcFunc['db_query']('', '
        SELECT l.track_id, tc.title, lt.title, l.minute, l.second, l.millisecond, t.title, IF(t.pending = "1" OR l.pending = "1",1,0) AS pending
        FROM {db_prefix}garage_laps AS l, {db_prefix}garage_tracks AS t, {db_prefix}garage_track_conditions AS tc, {db_prefix}garage_lap_types AS lt 
        WHERE l.id = {int:lid}
            AND l.vehicle_id = {int:vid}
            AND l.track_id = t.id 
            AND l.condition_id = tc.id 
            AND l.type_id = lt.id',
        array(
            'lid' => $_GET['LID'],
            'vid' => $_GET['VID'],
        )
    );
    list($context['laps']['track_id'],
        $context['laps']['condition'],
        $context['laps']['type'],
        $context['laps']['minute'],
        $context['laps']['second'],
        $context['laps']['millisecond'],
        $context['laps']['track'],
        $context['laps']['pending']) = $smcFunc['db_fetch_row']($request);
    $context['laps']['time'] = $context['laps']['minute'] . ':' . $context['laps']['second'] . ':' . $context['laps']['millisecond'];
    $smcFunc['db_free_result']($request);

    // Check if the lap is pending, if they are the owner, or if they have permission to view pending items
    if ($context['laps']['pending'] == '1' && $context['user_vehicles']['user_id'] != $context['user']['id'] && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_item_error', false);
    }

    // Select image id
    $request = $smcFunc['db_query']('', '
        SELECT image_id, hilite 
        FROM {db_prefix}garage_laps_gallery
        WHERE lap_id = {int:lid}',
        array(
            'lid' => $_GET['LID'],
        )
    );
    $count = 0;
    $context['hilite_image_location'] = "";
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['laps'][$count]['image_id'],
            $context['laps'][$count]['hilite']) = $row;

        // Select image data if there is any
        if (!empty($context['laps'][$count]['image_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT attach_location, attach_hits, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_desc, is_remote 
                FROM {db_prefix}garage_images 
                WHERE attach_id = {int:attach_id}',
                array(
                    'attach_id' => $context['laps'][$count]['image_id'],
                )
            );
            list($context['laps'][$count]['attach_location'],
                $context['laps'][$count]['attach_hits'],
                $context['laps'][$count]['attach_file'],
                $context['laps'][$count]['attach_thumb_location'],
                $context['laps'][$count]['attach_thumb_width'],
                $context['laps'][$count]['attach_thumb_height'],
                $context['laps'][$count]['attach_desc'],
                $context['laps'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);

            if (empty($context['laps'][$count]['attach_desc'])) {
                $context['laps'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }

            if ($context['laps'][$count]['hilite'] == 1) {
                if ($context['laps'][$count]['is_remote'] == 1) {
                    $context['hilite_image_location'] = urldecode($context['laps'][$count]['attach_location']);
                } else {
                    $context['hilite_image_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['laps'][$count]['attach_location'];
                }
                $context['hilite_thumb_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['laps'][$count]['attach_thumb_location'];
                $context['hilite_thumb_width'] = $context['laps'][$count]['attach_thumb_width'];
                $context['hilite_thumb_height'] = $context['laps'][$count]['attach_thumb_height'];
                $context['hilite_desc'] = $context['laps'][$count]['attach_desc'];
            }
            if ($context['laps'][$count]['is_remote'] == 1) {
                $context['laps'][$count]['attach_location'] = urldecode($context['laps'][$count]['attach_location']);
            } else {
                $context['laps'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['laps'][$count]['attach_location'];
            }
            $context['laps'][$count]['attach_thumb_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['laps'][$count]['attach_thumb_location'];
            $count++;
        }
    }
    $smcFunc['db_free_result']($request);

    // Select video id
    $request = $smcFunc['db_query']('', '
        SELECT video_id
        FROM {db_prefix}garage_video_gallery
        WHERE vehicle_id = {int:vid}
            AND type = "lap"
            AND type_id = {int:lid}',
        array(
            'lid' => $_GET['LID'],
            'vid' => $_GET['VID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['laps'][$count]['video_id']) = $row;

        // Select video data if there is any
        if (!empty($context['laps'][$count]['video_id'])) {
            $request2 = $smcFunc['db_query']('', '
                SELECT url, title, video_desc
                FROM {db_prefix}garage_video 
                WHERE id = {int:video_id}',
                array(
                    'video_id' => $context['laps'][$count]['video_id'],
                )
            );
            list($context['laps'][$count]['video_url'],
                $context['laps'][$count]['video_title'],
                $context['laps'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);

            if (empty($context['laps'][$count]['video_desc'])) {
                $context['laps'][$count]['video_desc'] = $txt['smfg_no_desc'];
            }

            $context['laps'][$count]['video_thumb'] = displayVideo($context['laps'][$count]['video_url'], 2);
            $context['laps'][$count]['video_height'] = displayVideo($context['laps'][$count]['video_url'], 'height');
            $context['laps'][$count]['video_width'] = displayVideo($context['laps'][$count]['video_url'], 'width');
        }
        $count++;
    }

}

// View Track
function G_View_Track()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'view_track';

    // Make sure this module is enabled
    if (!$smfgSettings['enable_laptimes']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Make sure the user didn't access this page directly
    if (!isset($_GET['TID'])) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_direct_page_access', false);
    }

    // Check and make sure the track is in the db
    $request = $smcFunc['db_query']('', '
        SELECT t.id
        FROM {db_prefix}garage_tracks AS t
        WHERE t.id = {int:tid}
        LIMIT 1',
        array(
            'tid' => $_GET['TID'],
        )
    );
    $matching_tid = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_tid <= 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_track', false);
    }

    // Gather Track related info
    $request = $smcFunc['db_query']('', '
        SELECT title, length, mileage_unit, pending
        FROM {db_prefix}garage_tracks
        WHERE id = {int:tid}',
        array(
            'tid' => $_GET['TID'],
        )
    );
    list($context['track']['title'],
        $context['track']['length'],
        $context['track']['mileage_unit'],
        $context['track']['pending']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if vehicle is pending, if they are the owner, or if they have permission to view pending items
    if ($context['track']['pending'] == '1' && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_item_error', false);
    }

    // Get everything else at once
    $request = $smcFunc['db_query']('', '
        SELECT l.id, l.vehicle_id, tc.title, lt.title, CONCAT_WS(":", l.minute, l.second, l.millisecond), v.user_id, u.real_name, CONCAT_WS(" ", v.made_year, mk.make, md.model)
        FROM {db_prefix}garage_laps AS l, {db_prefix}garage_tracks AS t, {db_prefix}garage_vehicles AS v, {db_prefix}members AS u, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_track_conditions AS tc, {db_prefix}garage_lap_types AS lt
        WHERE t.id = {int:tid}
            AND l.track_id = {int:tid}
            AND l.vehicle_id = v.id 
            AND v.user_id = u.id_member 
            AND v.make_id = mk.id 
            AND v.model_id = md.id 
            AND l.condition_id = tc.id
            AND l.type_id = lt.id
            AND v.pending != "1"
            AND mk.pending != "1"
            AND md.pending != "1"
            AND l.pending != "1"',
        array(
            'tid' => $_GET['TID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['laps'][$count]['id'],
            $context['laps'][$count]['vehicle_id'],
            $context['laps'][$count]['condition'],
            $context['laps'][$count]['type'],
            $context['laps'][$count]['time'],
            $context['laps'][$count]['user_id'],
            $context['laps'][$count]['memberName'],
            $context['laps'][$count]['vehicle']) = $row;

        // Select image id
        $request3 = $smcFunc['db_query']('', '
            SELECT image_id, hilite 
            FROM {db_prefix}garage_laps_gallery
            WHERE lap_id = {int:lid}
                AND hilite = 1',
            array(
                'lid' => $context['laps'][$count]['id'],
            )
        );
        list($context['laps'][$count]['image_id'],
            $context['laps'][$count]['hilite']) = $smcFunc['db_fetch_row']($request3);

        // Select image data if there is any
        $context['laps'][$count]['image'] = '';
        if (!empty($context['laps'][$count]['image_id'])) {
            $request2 = $smcFunc['db_query']('', '
                    SELECT attach_location, attach_hits, attach_file, attach_thumb_location, attach_thumb_width, attach_thumb_height, attach_desc, is_remote
                    FROM {db_prefix}garage_images 
                    WHERE attach_id = {int:attach_id}',
                array(
                    'attach_id' => $context['laps'][$count]['image_id'],
                )
            );
            list($context['laps'][$count]['attach_location'],
                $context['laps'][$count]['attach_hits'],
                $context['laps'][$count]['attach_file'],
                $context['laps'][$count]['attach_thumb_location'],
                $context['laps'][$count]['attach_thumb_width'],
                $context['laps'][$count]['attach_thumb_height'],
                $context['laps'][$count]['attach_desc'],
                $context['laps'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);
            if (empty($context['laps'][$count]['attach_desc'])) {
                $context['laps'][$count]['attach_desc'] = $txt['smfg_no_desc'];
            }

            if ($context['laps'][$count]['is_remote'] == 1) {
                $context['laps'][$count]['attach_location'] = urldecode($context['laps'][$count]['attach_file']);
            } else {
                $context['laps'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['laps'][$count]['attach_location'];
            }
            $context['laps'][$count]['attach_thumb_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['laps'][$count]['attach_thumb_location'];
            // If there is an image attached, link to it
            if (isset($context['laps'][$count]['attach_location'])) {
                $context['laps'][$count]['image'] = "<a href=\"" . $context['laps'][$count]['attach_location'] . "\" rel=\"shadowbox[laps]\" title=\"" . garage_title_clean($context['laps'][$count]['time'] . ' @ ' . $context['track']['title'] . ' :: ' . $context['laps'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
            }
        }

        // Define spacer
        $context['laps'][$count]['spacer'] = '';

        if ($smfgSettings['enable_laptime_video']) {
            // Check for videos
            $request2 = $smcFunc['db_query']('', '
                    SELECT video_id
                    FROM {db_prefix}garage_video_gallery
                    WHERE type_id = {int:lid}
                        AND type = "lap"
                        ORDER BY id ASC
                        LIMIT 1',
                array(
                    'lid' => $context['laps'][$count]['id'],
                )
            );
            list($context['laps'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);

            // If there is an video, lets find its attributes
            $context['laps'][$count]['video'] = "";
            if (isset($context['laps'][$count]['video_id'])) {
                $request2 = $smcFunc['db_query']('', '
                        SELECT url, title, video_desc
                        FROM {db_prefix}garage_video
                        WHERE id = {int:video_id}',
                    array(
                        'video_id' => $context['laps'][$count]['video_id'],
                    )
                );
                list($context['laps'][$count]['video_url'],
                    $context['laps'][$count]['video_title'],
                    $context['laps'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                $smcFunc['db_free_result']($request2);

                if (empty($context['laps'][$count]['video_desc'])) {
                    $context['laps'][$count]['video_desc'] = $txt['smfg_no_desc'];
                }

                $context['laps'][$count]['video_height'] = displayVideo($context['laps'][$count]['video_url'],
                    'height');
                $context['laps'][$count]['video_width'] = displayVideo($context['laps'][$count]['video_url'], 'width');
                if (!empty($context['laps'][$count]['image_id']) && !empty($context['laps'][$count]['video_id'])) {
                    $context['laps'][$count]['spacer'] = '&nbsp;';
                }

                // If there is an video attached, link to it
                if (isset($context['laps'][$count]['video_url'])) {
                    $context['laps'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['laps'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['laps'][$count]['video_width'] . ";height=" . $context['laps'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['laps'][$count]['video_title'] . '</b> :: ' . $context['laps'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                }
            }
        }
        $count++;
    }
    $smcFunc['db_free_result']($request);

    // Build the link tree
    $context['page_title'] = $txt['smfg_view_track'] . ' &gt; ' . $context['track']['title'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=view_track;TID=' . $_GET['TID'],
        'name' => &$context['track']['title']
    );


}

// Browse Garage
function G_Browse()
{
    browse_tables("vehicles");
}

// Search Garage
function G_Search()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 1;

    $context['sub_template'] = 'search';
    $context['page_title'] = $txt['smfg_search'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=search',
        'name' => &$txt['smfg_search']
    );

    // Check Permissions
    isAllowedTo('search_vehicles');

    // If they are here, they are ready for a new search
    // The previous session vars must be unset prior to a new search
    unset($_SESSION['smfg']);

}

// Search Results
function G_Search_Results()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 1;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'search_results';
    $context['date_format'] = $smfgSettings['dateformat'];

    // Check Permissions
    isAllowedTo('search_vehicles');

    // Start the search session
    if (session_id() == "") {
        session_start();
    }

    // store session data
    if (!empty($_POST['made_year']) && isset($_POST['made_year'])) {
        $_SESSION['smfg']['made_year'] = $_POST['made_year'];
    }
    if (!empty($_POST['make_id']) && isset($_POST['make_id'])) {
        $_SESSION['smfg']['make_id'] = $_POST['make_id'];
    }
    if (!empty($_POST['model_id']) && isset($_POST['model_id'])) {
        $_SESSION['smfg']['model_id'] = $_POST['model_id'];
    }
    if (!empty($_POST['category_id']) && isset($_POST['category_id'])) {
        $_SESSION['smfg']['category_id'] = $_POST['category_id'];
    }
    if (!empty($_POST['manufacturer_id']) && isset($_POST['manufacturer_id'])) {
        $_SESSION['smfg']['manufacturer_id'] = $_POST['manufacturer_id'];
    }
    if (!empty($_POST['product_id']) && isset($_POST['product_id'])) {
        $_SESSION['smfg']['product_id'] = $_POST['product_id'];
    }
    if (!empty($_POST['username']) && isset($_POST['username'])) {
        $_SESSION['smfg']['username'] = $_POST['username'];
    }
    if (!empty($_POST['search_logic']) && isset($_POST['search_logic'])) {
        $_SESSION['smfg']['search_logic'] = $_POST['search_logic'];
    }
    if (!empty($_POST['display_as']) && isset($_POST['display_as'])) {
        $_SESSION['smfg']['display_as'] = $_POST['display_as'];
    }
    if (!empty($_POST['search_year']) && isset($_POST['search_year'])) {
        $_SESSION['smfg']['search_year'] = $_POST['search_year'];
    }
    if (!empty($_POST['search_make']) && isset($_POST['search_make'])) {
        $_SESSION['smfg']['search_make'] = $_POST['search_make'];
    }
    if (!empty($_POST['search_model']) && isset($_POST['search_model'])) {
        $_SESSION['smfg']['search_model'] = $_POST['search_model'];
    }
    if (!empty($_POST['search_category']) && isset($_POST['search_category'])) {
        $_SESSION['smfg']['search_category'] = $_POST['search_category'];
    }
    if (!empty($_POST['search_manufacturer']) && isset($_POST['search_manufacturer'])) {
        $_SESSION['smfg']['search_manufacturer'] = $_POST['search_manufacturer'];
    }
    if (!empty($_POST['search_product']) && isset($_POST['search_product'])) {
        $_SESSION['smfg']['search_product'] = $_POST['search_product'];
    }
    if (!empty($_POST['search_username']) && isset($_POST['search_username'])) {
        $_SESSION['smfg']['search_username'] = $_POST['search_username'];
    }

    // Define anything we are not searching for
    if (empty($_SESSION['smfg']['made_year'])) {
        $_SESSION['smfg']['made_year'] = "";
    }
    if (empty($_SESSION['smfg']['make_id'])) {
        $_SESSION['smfg']['make_id'] = "";
    }
    if (empty($_SESSION['smfg']['model_id'])) {
        $_SESSION['smfg']['model_id'] = "";
    }
    if (empty($_SESSION['smfg']['category_id'])) {
        $_SESSION['smfg']['category_id'] = "";
    }
    if (empty($_SESSION['smfg']['manufacturer_id'])) {
        $_SESSION['smfg']['manufacturer_id'] = "";
    }
    if (empty($_SESSION['smfg']['product_id'])) {
        $_SESSION['smfg']['product_id'] = "";
    }
    if (empty($_SESSION['smfg']['username'])) {
        $_SESSION['smfg']['username'] = "";
    }
    if (empty($_SESSION['smfg']['search_logic'])) {
        $_SESSION['smfg']['search_logic'] = "";
    }
    if (empty($_SESSION['smfg']['display_as'])) {
        $_SESSION['smfg']['display_as'] = "";
    }
    if (empty($_SESSION['smfg']['search_year'])) {
        $_SESSION['smfg']['search_year'] = "";
    }
    if (empty($_SESSION['smfg']['search_make'])) {
        $_SESSION['smfg']['search_make'] = "";
    }
    if (empty($_SESSION['smfg']['search_model'])) {
        $_SESSION['smfg']['search_model'] = "";
    }
    if (empty($_SESSION['smfg']['search_category'])) {
        $_SESSION['smfg']['search_category'] = "";
    }
    if (empty($_SESSION['smfg']['search_manufacturer'])) {
        $_SESSION['smfg']['search_manufacturer'] = "";
    }
    if (empty($_SESSION['smfg']['search_product'])) {
        $_SESSION['smfg']['search_product'] = "";
    }
    if (empty($_SESSION['smfg']['search_username'])) {
        $_SESSION['smfg']['search_username'] = "";
    }

    // Make sure they submitted criteria
    if (empty($_SESSION['smfg']['search_year']) && empty($_SESSION['smfg']['search_make']) && empty($_SESSION['smfg']['search_model']) && empty($_SESSION['smfg']['search_category']) && empty($_SESSION['smfg']['search_manufacturer']) && empty($_SESSION['smfg']['search_product']) && empty($_SESSION['smfg']['search_username'])) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_search_criteria', true);
    }

    // Make sure they submitted criteria
    if (empty($_SESSION['smfg']['made_year']) && empty($_SESSION['smfg']['make_id']) && empty($_SESSION['smfg']['model_id']) && empty($_SESSION['smfg']['category_id']) && empty($_SESSION['smfg']['manufacturer_id']) && empty($_SESSION['smfg']['product_id']) && empty($_SESSION['smfg']['username'])) {
        loadLanguage('Errors');
        fatal_lang_error('garage_no_search_criteria', true);
    }

    // Set the first row class for alternating bg colors
    $context['bgclass'] = "windowbg2";

    // Display as - Vehicles
    if ($_SESSION['smfg']['display_as'] == 'vehicles') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = "DESC";
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = "ASC";
        } else {
            if ($_GET['order'] == "DESC") {
                $order = "DESC";
            }
        }

        // Create the default sort option links
        $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=year\">" . $txt['smfg_year'] . "</a>";
        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=make\">" . $txt['smfg_make'] . "</a>";
        $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=model\">" . $txt['smfg_model'] . "</a>";
        $context['sort']['byColor'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=color\">" . $txt['smfg_color'] . "</a>";
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=owner\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byViews'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=views\">" . $txt['smfg_views'] . "</a>";
        $context['sort']['byMods'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=mods\">" . $txt['smfg_mods'] . "</a>";
        $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=updated\">" . $txt['smfg_updated'] . "</a>";

        // Build sort option links with dynamic ordering
        $sort = "v.date_created";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "date_created") {
            $sort = "v.date_created";
        } else {
            if ($_GET['sort'] == "updated") {
                $sort = "v.date_updated";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=updated;order=ASC\">" . $txt['smfg_updated'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=updated;order=DESC\">" . $txt['smfg_updated'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "owner") {
                    $sort = "u.real_name";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=owner;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=owner;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "year") {
                        $sort = "v.made_year";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=year;order=ASC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=year;order=DESC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "make") {
                            $sort = "mk.make";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=make;order=ASC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=make;order=DESC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "model") {
                                $sort = "md.model";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                    $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=model;order=ASC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=model;order=DESC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                }
                            } else {
                                if ($_GET['sort'] == "color") {
                                    $sort = "v.color";
                                    // Set order options for each sort type
                                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                        $context['sort']['byColor'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=color;order=ASC\">" . $txt['smfg_color'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                    } else {
                                        $context['sort']['byColor'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=color;order=DESC\">" . $txt['smfg_color'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                    }
                                } else {
                                    if ($_GET['sort'] == "views") {
                                        $sort = "v.views";
                                        // Set order options for each sort type
                                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                            $context['sort']['byViews'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=views;order=ASC\">" . $txt['smfg_views'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                        } else {
                                            $context['sort']['byViews'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=views;order=DESC\">" . $txt['smfg_views'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                        }
                                    } else {
                                        if ($_GET['sort'] == "mods") {
                                            $sort = "total_mods";
                                            // Set order options for each sort type
                                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                                $context['sort']['byMods'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=mods;order=ASC\">" . $txt['smfg_mods'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                            } else {
                                                $context['sort']['byMods'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=mods;order=DESC\">" . $txt['smfg_mods'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
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

    // Display as - Modifications
    if ($_SESSION['smfg']['display_as'] == 'modifications') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = "DESC";
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = "ASC";
        } else {
            if ($_GET['order'] == "DESC") {
                $order = "DESC";
            }
        }

        // Create the default sort option links
        $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=year\">" . $txt['smfg_year'] . "</a>";
        $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=make\">" . $txt['smfg_make'] . "</a>";
        $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=model\">" . $txt['smfg_model'] . "</a>";
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=owner\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byMod'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=modification\">" . $txt['smfg_modification'] . "</a>";
        $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=updated\">" . $txt['smfg_updated'] . "</a>";

        // Build sort option links with dynamic ordering
        $sort = "m.date_created";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "date_created") {
            $sort = "m.date_created";
        } else {
            if ($_GET['sort'] == "updated") {
                $sort = "m.date_updated";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=updated;order=ASC\">" . $txt['smfg_updated'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byUpdated'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=updated;order=DESC\">" . $txt['smfg_updated'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "owner") {
                    $sort = "u.real_name";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=owner;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=owner;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "year") {
                        $sort = "v.made_year";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=year;order=ASC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byYear'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=year;order=DESC\">" . $txt['smfg_year'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "make") {
                            $sort = "mk.make";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=make;order=ASC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byMake'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=make;order=DESC\">" . $txt['smfg_make'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "model") {
                                $sort = "md.model";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                    $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=model;order=ASC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byModel'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=model;order=DESC\">" . $txt['smfg_model'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                }
                            } else {
                                if ($_GET['sort'] == "modification") {
                                    $sort = "p.title";
                                    // Set order options for each sort type
                                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                        $context['sort']['byMod'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=modification;order=ASC\">" . $txt['smfg_modification'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                    } else {
                                        $context['sort']['byMod'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=modification;order=DESC\">" . $txt['smfg_modification'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Premiums
    if ($_SESSION['smfg']['display_as'] == 'premiums') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = "ASC";
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = "ASC";
        } else {
            if ($_GET['order'] == "DESC") {
                $order = "DESC";
            }
        }

        // Create the default sort option links
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user\">" . $txt['smfg_owner'] . "</a>";
        $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle\">Vehicle</a>";
        $context['sort']['byPremium'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=premium\">Premium</a>";
        $context['sort']['byCoverType'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=ct\">Cover Type</a>";
        $context['sort']['byInsurer'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=insurer\">Insurer</a>";

        // Set of get rid of the default sort image and link if they choose another sort option
        if (!isset($_GET['sort'])) {
            $context['sort']['byPremium'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=premium;order=DESC\">Premium <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
        } else {
            $context['sort']['byPremium'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=premium\">Premium</a>";
        }

        // Build sort option links with dynamic ordering
        $sort = "price";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "user") {
            $sort = "real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user;order=DESC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user;order=ASC\">" . $txt['smfg_owner'] . " <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "vehicle") {
                $sort = "vehicle";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle;order=DESC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle;order=ASC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "premium") {
                    $sort = "price";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                        $context['sort']['byPremium'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=premium;order=DESC\">Premium <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byPremium'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=premium;order=ASC\">Premium <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "ct") {
                        $sort = "pt.title";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                            $context['sort']['byCoverType'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=ct;order=DESC\">Cover Type <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byCoverType'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=ct;order=ASC\">Cover Type <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "insurer") {
                            $sort = "b.title";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                $context['sort']['byInsurer'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=insurer;order=DESC\">Insurer <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byInsurer'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=insurer;order=ASC\">Insurer <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            }
                        }
                    }
                }
            }
        }

    }

    // Display as - Quartermiles
    if ($_SESSION['smfg']['display_as'] == 'quartermiles') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = "ASC";
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = "ASC";
        } else {
            if ($_GET['order'] == "DESC") {
                $order = "DESC";
            }
        }

        // Create the default sort option links
        $context['sort']['byUsername'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user\">Username</a>";
        $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle\">Vehicle</a>";
        $context['sort']['byRt'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=rt\">R/T</a>";
        $context['sort']['bySixty'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=sixty\">60 Foot</a>";
        $context['sort']['byThree'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=three\">330 Foot</a>";
        $context['sort']['byEighth'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=eighth\">1/8 Mile</a>";
        $context['sort']['byThou'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=thou\">1000 Foot</a>";
        $context['sort']['byQuart'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=quart\">1/4 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";

        // Set of get rid of the default sort image and link if they choose another sort option
        if (!isset($_GET['sort'])) {
            $context['sort']['byQuart'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=quart;order=DESC\">1/4 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
        } else {
            $context['sort']['byQuart'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=quart\">1/4 Mile</a>";
        }

        // Build sort option links with dynamic ordering
        $sort = "quart";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "user") {
            $sort = "real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                $context['sort']['byUsername'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user;order=DESC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byUsername'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user;order=ASC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "vehicle") {
                $sort = "vehicle";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle;order=DESC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle;order=ASC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "rt") {
                    $sort = "rt";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                        $context['sort']['byRt'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=rt;order=DESC\">R/T <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byRt'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=rt;order=ASC\">R/T <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "sixty") {
                        $sort = "sixty";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                            $context['sort']['bySixty'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=sixty;order=DESC\">60 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['bySixty'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=sixty;order=ASC\">60 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "three") {
                            $sort = "three";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                $context['sort']['byThree'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=three;order=DESC\">330 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byThree'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=three;order=ASC\">330 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "eighth") {
                                $sort = "eighth";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                    $context['sort']['byEighth'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=eighth;order=DESC\">1/8 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byEighth'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=eighth;order=ASC\">1/8 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                }
                            } else {
                                if ($_GET['sort'] == "thou") {
                                    $sort = "thou";
                                    // Set order options for each sort type
                                    if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                        $context['sort']['byThou'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=thou;order=DESC\">1000 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                    } else {
                                        $context['sort']['byThou'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=thou;order=ASC\">1000 Foot <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                    }
                                } else {
                                    if ($_GET['sort'] == "quart") {
                                        $sort = "quart";
                                        // Set order options for each sort type
                                        if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                            $context['sort']['byQuart'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=quart;order=DESC\">1/4 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                        } else {
                                            $context['sort']['byQuart'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=quart;order=ASC\">1/4 Mile <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
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

    // Display as - Dynoruns
    if ($_SESSION['smfg']['display_as'] == 'dynoruns') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = "DESC";
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = "ASC";
        } else {
            if ($_GET['order'] == "DESC") {
                $order = "DESC";
            }
        }

        // Create the default sort option links
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user\">Username</a>";
        $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle\">Vehicle</a>";
        $context['sort']['byDynocenter'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=dynocenter\">Dynocenter</a>";
        $context['sort']['byBhp'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=bhp\">BHP</a>";
        $context['sort']['byTorque'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=torque\">Torque</a>";
        $context['sort']['byBoost'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=boost\">Boost</a>";
        $context['sort']['byNitrous'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=nitrous\">Nitrous</a>";
        $context['sort']['byPeakpoint'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=peak\">Peakpoint</a>";

        // Set or get rid of the default sort image and link if they choose another sort option
        if (!isset($_GET['sort'])) {
            $context['sort']['byBhp'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=bhp;order=ASC\">BHP <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
        } else {
            $context['sort']['byBhp'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=bhp\">BHP</a>";
        }

        // Build sort option links with dynamic ordering
        $sort = "d.bhp";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "user") {
            $sort = "u.real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user;order=ASC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user;order=DESC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "vehicle") {
                $sort = "vehicle";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle;order=ASC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle;order=DESC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "dynocenter") {
                    $sort = "b.title";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                        $context['sort']['byDynocenter'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=dynocenter;order=ASC\">Dynocenter <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byDynocenter'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=dynocenter;order=DESC\">Dynocenter <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "bhp") {
                        $sort = "d.bhp";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                            $context['sort']['byBhp'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=bhp;order=ASC\">BHP <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byBhp'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=bhp;order=DESC\">BHP <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "torque") {
                            $sort = "d.torque";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                $context['sort']['byTorque'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=torque;order=ASC\">Torque <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byTorque'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=torque;order=DESC\">Torque <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "boost") {
                                $sort = "d.boost";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                    $context['sort']['byBoost'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=boost;order=ASC\">Boost <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byBoost'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=boost;order=DESC\">Boost <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                }
                            } else {
                                if ($_GET['sort'] == "nitrous") {
                                    $sort = "d.nitrous";
                                    // Set order options for each sort type
                                    if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                        $context['sort']['byNitrous'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=nitrous;order=ASC\">Nitrous <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                    } else {
                                        $context['sort']['byNitrous'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=nitrous;order=DESC\">Nitrous <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                    }
                                } else {
                                    if ($_GET['sort'] == "peak") {
                                        $sort = "d.peakpoint";
                                        // Set order options for each sort type
                                        if (empty($_GET['order']) || $_GET['order'] == "DESC") {
                                            $context['sort']['byPeakpoint'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=peak;order=ASC\">Peakpoint <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                        } else {
                                            $context['sort']['byPeakpoint'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=peak;order=DESC\">Peakpoint <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
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

    // Display as - Laps
    if ($_SESSION['smfg']['display_as'] == 'laps') {

        // Set the defaults and GET variables for sorting and order
        // Order options
        $order = "ASC";
        if (!isset($_GET['order'])) {
            $_GET['order'] = "";
        }
        if ($_GET['order'] == "ASC") {
            $order = "ASC";
        } else {
            if ($_GET['order'] == "DESC") {
                $order = "DESC";
            }
        }

        // Create the default sort option links
        $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user\">Username</a>";
        $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle\">Vehicle</a>";
        $context['sort']['byTrack'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=track\">Track</a>";
        $context['sort']['byCondition'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=condition\">Condition</a>";
        $context['sort']['byType'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=type\">Type</a>";
        $context['sort']['byTime'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=time\">Time (M:S:MS)</a>";

        // Set or Get rid of the default sort image and link if they choose another sort option
        if (!isset($_GET['sort'])) {
            $context['sort']['byTime'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=time;order=DESC\">Time (M:S:MS) <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
        } else {
            $context['sort']['byTime'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=time\">Time (M:S:MS)</a>";
        }

        // Build sort option links with dynamic ordering
        $sort = "t.title " . $order . ", l.minute " . $order . ", l.second " . $order . ", l.millisecond";
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "";
        }
        if ($_GET['sort'] == "user") {
            $sort = "u.real_name";
            // Set order options for each sort type
            if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user;order=DESC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
            } else {
                $context['sort']['byOwner'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=user;order=ASC\">Username <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
            }
        } else {
            if ($_GET['sort'] == "vehicle") {
                $sort = "vehicle";
                // Set order options for each sort type
                if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle;order=DESC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                } else {
                    $context['sort']['byVehicle'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=vehicle;order=ASC\">Vehicle <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                }
            } else {
                if ($_GET['sort'] == "track") {
                    $sort = "t.title";
                    // Set order options for each sort type
                    if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                        $context['sort']['byTrack'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=track;order=DESC\">Track <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                    } else {
                        $context['sort']['byTrack'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=track;order=ASC\">Track <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                    }
                } else {
                    if ($_GET['sort'] == "condition") {
                        $sort = "tc.title";
                        // Set order options for each sort type
                        if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                            $context['sort']['byCondition'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=condition;order=DESC\">Condition <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                        } else {
                            $context['sort']['byCondition'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=condition;order=ASC\">Condition <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                        }
                    } else {
                        if ($_GET['sort'] == "type") {
                            $sort = "lt.title";
                            // Set order options for each sort type
                            if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                $context['sort']['byType'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=type;order=DESC\">Type <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                            } else {
                                $context['sort']['byType'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=type;order=ASC\">Type <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                            }
                        } else {
                            if ($_GET['sort'] == "time") {
                                $sort = "time";
                                // Set order options for each sort type
                                if (empty($_GET['order']) || $_GET['order'] == "ASC") {
                                    $context['sort']['byTime'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=time;order=DESC\">Time (M:S:MS) <img src=\"" . $settings['actual_images_url'] . "/sort_down.gif\" alt=\"\" /></a>";
                                } else {
                                    $context['sort']['byTime'] = "<a href=\"" . $scripturl . "?action=garage;sa=search_results;sort=time;order=ASC\">Time (M:S:MS) <img src=\"" . $settings['actual_images_url'] . "/sort_up.gif\" alt=\"\" /></a>";
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    // Assign the tables and selects needed
    $search_selects = "";
    if ($_SESSION['smfg']['display_as'] == "vehicles") {

        $search_selects .= "v.id, v.made_year, mk.make, md.model, v.color, v.user_id, u.real_name, v.views, COUNT(m.id) as total_mods, v.date_updated";
        $search_tables = "{db_prefix}garage_vehicles AS v LEFT OUTER JOIN {db_prefix}garage_modifications AS m ON v.id = m.vehicle_id, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
        WHERE v.user_id = u.id_member AND v.make_id = mk.id AND v.model_id = md.id AND mk.pending != '1' AND md.pending != '1' AND v.pending != '1' AND (";

    } else {
        if ($_SESSION['smfg']['display_as'] == "modifications") {

            $search_selects .= "v.id, v.made_year, mk.make, md.model, v.user_id, u.real_name, m.id, p.title, m.date_updated";
            $search_tables = "{db_prefix}garage_modifications AS m, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_products AS p, {db_prefix}members AS u 
        WHERE v.user_id = u.id_member AND v.make_id = mk.id AND v.model_id = md.id AND m.product_id = p.id AND m.vehicle_id = v.id AND mk.pending != '1' AND md.pending != '1' AND v.pending != '1' AND m.pending != '1' AND (";

        } else {
            if ($_SESSION['smfg']['display_as'] == "premiums") {

                $search_selects .= 'v.id, CONCAT_WS(" ", v.made_year, mk.make, md.model) AS vehicle, v.user_id, u.real_name, p.premium AS price, b.id, b.title, pt.title';
                $search_tables = "{db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_premiums AS p, {db_prefix}garage_premium_types AS pt, {db_prefix}garage_business AS b, {db_prefix}members AS u
        WHERE u.id_member = v.user_id AND v.make_id = mk.id AND v.model_id = md.id AND pt.id = p.cover_type_id AND b.id = p.business_id AND p.vehicle_id = v.id AND mk.pending != '1' AND md.pending != '1' AND v.pending != '1' AND b.pending != '1' AND (";

            } else {
                if ($_SESSION['smfg']['display_as'] == "quartermiles") {

                    $search_selects .= 'q.id, v.id, CONCAT_WS(" ", v.made_year, mk.make, md.model) AS vehicle, v.user_id, u.real_name, q.rt, q.sixty, q.three, q.eighth, q.eighthmph, q.thou, q.quart, q.quartmph';
                    $search_tables = "{db_prefix}garage_quartermiles AS q, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u
        WHERE u.id_member = v.user_id AND v.make_id = mk.id AND v.model_id = md.id AND q.vehicle_id = v.id AND mk.pending != '1' AND md.pending != '1' AND v.pending != '1' AND (";

                } else {
                    if ($_SESSION['smfg']['display_as'] == "dynoruns") {

                        $search_selects .= 'v.id, d.id, CONCAT_WS(" ", v.made_year, mk.make, md.model) AS vehicle, v.user_id, u.real_name, d.bhp, d.bhp_unit, d.torque, d.torque_unit, d.boost, d.boost_unit, d.nitrous, d.peakpoint, b.title, b.id';
                        $search_tables = "{db_prefix}garage_dynoruns AS d LEFT OUTER JOIN {db_prefix}garage_business AS b ON b.id = d.dynocenter_id, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u 
        WHERE u.id_member = v.user_id AND v.make_id = mk.id AND v.model_id = md.id AND d.vehicle_id = v.id AND mk.pending != '1' AND md.pending != '1' AND v.pending != '1' AND b.pending != '1' AND (";

                    } else {
                        if ($_SESSION['smfg']['display_as'] == "laps") {

                            $search_selects .= 'v.id, CONCAT_WS(" ", v.made_year, mk.make, md.model) AS vehicle, v.user_id, u.real_name, l.track_id, t.title, tc.title, lt.title, l.id, CONCAT_WS(":",l.minute, l.second, l.millisecond) AS time';
                            $search_tables = "{db_prefix}garage_laps AS l, {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u, {db_prefix}garage_tracks AS t, {db_prefix}garage_track_conditions AS tc, {db_prefix}garage_lap_types AS lt
        WHERE u.id_member = v.user_id AND v.make_id = mk.id AND v.model_id = md.id AND l.vehicle_id = v.id AND l.track_id = t.id AND l.condition_id = tc.id AND l.type_id = lt.id AND mk.pending != '1' AND md.pending != '1' AND v.pending != '1' AND t.pending != '1' AND (";

                        }
                    }
                }
            }
        }
    }

    // Check what is being searched for and assign appropriate data
    $count = 0;
    $search_logic = "";
    if ($_SESSION['smfg']['search_year'] == 1) {

        $search_logic .= "v.made_year = " . $_SESSION['smfg']['made_year'];
        $count++;

    }
    if ($_SESSION['smfg']['search_make'] == 1) {

        if ($count > 0) {
            $separator = " " . $_SESSION['smfg']['search_logic'] . " ";
        } else {
            $separator = "";
        }
        $search_logic .= $separator . "v.make_id = " . $_SESSION['smfg']['make_id'];
        $count++;

    }
    if ($_SESSION['smfg']['search_model'] == 1) {

        if ($count > 0) {
            $separator = " " . $_SESSION['smfg']['search_logic'] . " ";
        } else {
            $separator = "";
        }
        $search_logic .= $separator . "v.model_id = " . $_SESSION['smfg']['model_id'];
        $count++;

    }
    if ($_SESSION['smfg']['search_category'] == 1) {

        if ($count > 0) {
            $separator = " " . $_SESSION['smfg']['search_logic'] . " ";
        } else {
            $separator = "";
        }
        $search_logic .= $separator . "m.category_id = " . $_SESSION['smfg']['category_id'];
        $count++;

    }
    if ($_SESSION['smfg']['search_manufacturer'] == 1) {

        if ($count > 0) {
            $separator = " " . $_SESSION['smfg']['search_logic'] . " ";
        } else {
            $separator = "";
        }
        $search_logic .= $separator . "m.manufacturer_id = " . $_SESSION['smfg']['manufacturer_id'];
        $count++;

    }
    if ($_SESSION['smfg']['search_product'] == 1) {

        if ($count > 0) {
            $separator = " " . $_SESSION['smfg']['search_logic'] . " ";
        } else {
            $separator = "";
        }
        $search_logic .= $separator . "m.product_id = " . $_SESSION['smfg']['product_id'];
        $count++;

    }
    if ($_SESSION['smfg']['search_username'] == 1) {

        if ($count > 0) {
            $separator = " " . $_SESSION['smfg']['search_logic'] . " ";
        } else {
            $separator = "";
        }
        $search_logic .= $separator . "u.real_name = '" . $_SESSION['smfg']['username'] . "'";
        $count++;

    }

    $search_logic .= " )";

    // Assign any group by statements after joins if needed
    if ($_SESSION['smfg']['display_as'] == "vehicles") {
        $group_by = "GROUP BY v.id, v.made_year, mk.make, md.model, v.color, v.user_id, u.real_name, v.views, v.date_updated";
    } else {
        $group_by = "";
    }

    // Get the total number of results for pagination
    $request = $smcFunc['db_query']('', '
        SELECT ' . $search_selects . '
        FROM ' . $search_tables . ' ' . $search_logic . ' ' . $group_by . '
        ORDER BY ' . $sort . ' ' . $order
    );

    $context['total'] = $smcFunc['db_num_rows']($request);

    // Set pagination variables
    $context['display'] = $smfgSettings['results_per_page'];
    $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=search_results' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : ''),
        $_REQUEST['start'], $context['total'], $context['display']);
    $context['start'] = $_REQUEST['start'] + 1;
    $context['end'] = min($_REQUEST['start'] + $context['display'], $context['total']);
    $context['page_title'] = $txt['smfg_viewing_results'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=search_results' . (isset($_REQUEST['sort']) ? ';sort=' . $_REQUEST['sort'] : '') . (isset($_REQUEST['order']) ? ';order=' . $_REQUEST['order'] : '') . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
        'name' => $txt['smfg_viewing_results'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
        'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_results'] . ')'
    );

    $request = $smcFunc['db_query']('', '
        SELECT ' . $search_selects . '
        FROM ' . $search_tables . ' ' . $search_logic . ' ' . $group_by . '
        ORDER BY ' . $sort . ' ' . $order . '
        LIMIT ' . $_REQUEST['start'] . ', ' . $context['display']
    );

    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {

        if ($_SESSION['smfg']['display_as'] == "vehicles") {

            list($context['search_results'][$count]['vid'],
                $context['search_results'][$count]['made_year'],
                $context['search_results'][$count]['make'],
                $context['search_results'][$count]['model'],
                $context['search_results'][$count]['color'],
                $context['search_results'][$count]['user_id'],
                $context['search_results'][$count]['memberName'],
                $context['search_results'][$count]['views'],
                $context['search_results'][$count]['total_mods'],
                $context['search_results'][$count]['date_updated']) = $row;
            $context['search_results'][$count]['views'] = number_format($context['search_results'][$count]['views'], 0,
                '.', ',');

            // Check for images
            $request2 = $smcFunc['db_query']('', '
                    SELECT image_id
                    FROM {db_prefix}garage_vehicles_gallery
                    WHERE vehicle_id = {int:vid}
                        AND hilite = 1',
                array(
                    'vid' => $context['search_results'][$count]['vid'],
                )
            );
            list($context['search_results'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
            $smcFunc['db_free_result']($request2);
            // If there is an image, lets find its attributes
            $context['search_results'][$count]['image'] = '';
            if (isset($context['search_results'][$count]['image_id'])) {
                $request2 = $smcFunc['db_query']('', '
                        SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                        FROM {db_prefix}garage_images
                        WHERE attach_id = {int:attach_id}',
                    array(
                        'attach_id' => $context['search_results'][$count]['image_id'],
                    )
                );
                list($context['search_results'][$count]['attach_location'],
                    $context['search_results'][$count]['attach_ext'],
                    $context['search_results'][$count]['attach_file'],
                    $context['search_results'][$count]['attach_desc'],
                    $context['search_results'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
                $smcFunc['db_free_result']($request2);
                if (empty($context['search_results'][$count]['attach_desc'])) {
                    $context['search_results'][$count]['attach_desc'] = $txt['smfg_no_desc'];
                }

                // Build appropriate links for remote images
                if (isset($context['search_results'][$count]['attach_location'])) {
                    if ($context['search_results'][$count]['is_remote'] == 1) {
                        $context['search_results'][$count]['attach_location'] = urldecode($context['search_results'][$count]['attach_location']);
                    } else {
                        $context['search_results'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['search_results'][$count]['attach_location'];
                    }
                }
                // If there is an image attached, link to it
                if (isset($context['search_results'][$count]['attach_location'])) {
                    $context['search_results'][$count]['image'] = "<a href=\"" . $context['search_results'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['search_results'][$count]['made_year'] . ' ' . $context['search_results'][$count]['make'] . ' ' . $context['search_results'][$count]['model'] . ' :: ' . $context['search_results'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
                }
            }

            // Define spacer
            $context['search_results'][$count]['spacer'] = '';

            if ($smfgSettings['enable_vehicle_video']) {

                // Check for videos
                $request2 = $smcFunc['db_query']('', '
                        SELECT video_id
                        FROM {db_prefix}garage_video_gallery
                        WHERE vehicle_id = {int:vid}
                            ORDER BY id ASC
                            LIMIT 1',
                    array(
                        'vid' => $context['search_results'][$count]['vid'],
                    )
                );
                list($context['search_results'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
                $smcFunc['db_free_result']($request2);

                // If there is an video, lets find its attributes
                $context['search_results'][$count]['video'] = "";
                if (isset($context['search_results'][$count]['video_id'])) {
                    $request2 = $smcFunc['db_query']('', '
                            SELECT url, title, video_desc
                            FROM {db_prefix}garage_video
                            WHERE id = {int:video_id}',
                        array(
                            'video_id' => $context['search_results'][$count]['video_id'],
                        )
                    );
                    list($context['search_results'][$count]['video_url'],
                        $context['search_results'][$count]['video_title'],
                        $context['search_results'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                    $smcFunc['db_free_result']($request2);
                    if (empty($context['search_results'][$count]['video_desc'])) {
                        $context['search_results'][$count]['video_desc'] = $txt['smfg_no_desc'];
                    }
                    $context['search_results'][$count]['video_height'] = displayVideo($context['search_results'][$count]['video_url'],
                        'height');
                    $context['search_results'][$count]['video_width'] = displayVideo($context['search_results'][$count]['video_url'],
                        'width');
                    if (!empty($context['search_results'][$count]['image_id']) && !empty($context['search_results'][$count]['video_id'])) {
                        $context['search_results'][$count]['spacer'] = '&nbsp;';
                    }

                    // If there is an video attached, link to it
                    if (isset($context['search_results'][$count]['video_url'])) {
                        $context['search_results'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['search_results'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['search_results'][$count]['video_width'] . ";height=" . $context['search_results'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['search_results'][$count]['video_title'] . '</b> :: ' . $context['search_results'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                    }
                }

            }

        } else {
            if ($_SESSION['smfg']['display_as'] == "modifications") {

                list($context['search_results'][$count]['vid'],
                    $context['search_results'][$count]['made_year'],
                    $context['search_results'][$count]['make'],
                    $context['search_results'][$count]['model'],
                    $context['search_results'][$count]['user_id'],
                    $context['search_results'][$count]['memberName'],
                    $context['search_results'][$count]['mid'],
                    $context['search_results'][$count]['modification'],
                    $context['search_results'][$count]['date_updated']) = $row;

                // Check for images
                $request2 = $smcFunc['db_query']('', '
                    SELECT image_id
                    FROM {db_prefix}garage_modifications_gallery
                    WHERE modification_id = {int:mid}',
                    array(
                        'mid' => $context['search_results'][$count]['mid'],
                    )
                );
                list($context['search_results'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
                $smcFunc['db_free_result']($request2);
                // If there is an image, lets find its attributes
                $context['search_results'][$count]['image'] = '';
                if (isset($context['search_results'][$count]['image_id'])) {
                    $request2 = $smcFunc['db_query']('', '
                        SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                        FROM {db_prefix}garage_images
                        WHERE attach_id = {int:attach_id}',
                        array(
                            'attach_id' => $context['search_results'][$count]['image_id'],
                        )
                    );
                    list($context['search_results'][$count]['attach_location'],
                        $context['search_results'][$count]['attach_ext'],
                        $context['search_results'][$count]['attach_file'],
                        $context['search_results'][$count]['attach_desc'],
                        $context['search_results'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
                    $smcFunc['db_free_result']($request2);
                    if (empty($context['search_results'][$count]['attach_desc'])) {
                        $context['search_results'][$count]['attach_desc'] = $txt['smfg_no_desc'];
                    }

                    // Build appropriate links for remote images
                    if (isset($context['search_results'][$count]['attach_location'])) {
                        if ($context['search_results'][$count]['is_remote'] == 1) {
                            $context['search_results'][$count]['attach_location'] = urldecode($context['search_results'][$count]['attach_location']);
                        } else {
                            $context['search_results'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['search_results'][$count]['attach_location'];
                        }
                    }
                    // If there is an image attached, link to it
                    if (isset($context['search_results'][$count]['attach_location'])) {
                        $context['search_results'][$count]['image'] = "<a href=\"" . $context['search_results'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['search_results'][$count]['modification'] . ' :: ' . $context['search_results'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
                    }
                }

                // Define spacer
                $context['search_results'][$count]['spacer'] = '';

                if ($smfgSettings['enable_modification_video']) {

                    // Check for videos
                    $request2 = $smcFunc['db_query']('', '
                        SELECT video_id
                        FROM {db_prefix}garage_video_gallery
                        WHERE vehicle_id = {int:vid}
                            AND type = "mod"
                            AND type_id = {int:mid}
                            ORDER BY id ASC
                            LIMIT 1',
                        array(
                            'vid' => $context['search_results'][$count]['vid'],
                            'mid' => $context['search_results'][$count]['mid'],
                        )
                    );
                    list($context['search_results'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
                    $smcFunc['db_free_result']($request2);

                    // If there is an video, lets find its attributes
                    $context['search_results'][$count]['video'] = "";
                    if (isset($context['search_results'][$count]['video_id'])) {
                        $request2 = $smcFunc['db_query']('', '
                            SELECT url, title, video_desc
                            FROM {db_prefix}garage_video
                            WHERE id = {int:video_id}',
                            array(
                                'video_id' => $context['search_results'][$count]['video_id'],
                            )
                        );
                        list($context['search_results'][$count]['video_url'],
                            $context['search_results'][$count]['video_title'],
                            $context['search_results'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                        $smcFunc['db_free_result']($request2);
                        if (empty($context['search_results'][$count]['video_desc'])) {
                            $context['search_results'][$count]['video_desc'] = $txt['smfg_no_desc'];
                        }
                        $context['search_results'][$count]['video_height'] = displayVideo($context['search_results'][$count]['video_url'],
                            'height');
                        $context['search_results'][$count]['video_width'] = displayVideo($context['search_results'][$count]['video_url'],
                            'width');
                        if (!empty($context['search_results'][$count]['image_id']) && !empty($context['search_results'][$count]['video_id'])) {
                            $context['search_results'][$count]['spacer'] = '&nbsp;';
                        }

                        // If there is an video attached, link to it
                        if (isset($context['search_results'][$count]['video_url'])) {
                            $context['search_results'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['search_results'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['search_results'][$count]['video_width'] . ";height=" . $context['search_results'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['search_results'][$count]['video_title'] . '</b> :: ' . $context['search_results'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                        }
                    }

                }

            } else {
                if ($_SESSION['smfg']['display_as'] == "premiums") {

                    list($context['search_results'][$count]['vid'],
                        $context['search_results'][$count]['vehicle'],
                        $context['search_results'][$count]['user_id'],
                        $context['search_results'][$count]['memberName'],
                        $context['search_results'][$count]['price'],
                        $context['search_results'][$count]['bid'],
                        $context['search_results'][$count]['insurer'],
                        $context['search_results'][$count]['cover_type']) = $row;

                } else {
                    if ($_SESSION['smfg']['display_as'] == "quartermiles") {

                        list($context['search_results'][$count]['qmid'],
                            $context['search_results'][$count]['vid'],
                            $context['search_results'][$count]['vehicle'],
                            $context['search_results'][$count]['user_id'],
                            $context['search_results'][$count]['memberName'],
                            $context['search_results'][$count]['rt'],
                            $context['search_results'][$count]['sixty'],
                            $context['search_results'][$count]['three'],
                            $context['search_results'][$count]['eighth'],
                            $context['search_results'][$count]['eighthmph'],
                            $context['search_results'][$count]['thou'],
                            $context['search_results'][$count]['quart'],
                            $context['search_results'][$count]['quartmph']) = $row;

                        // Check for images
                        $request2 = $smcFunc['db_query']('', '
                    SELECT image_id
                    FROM {db_prefix}garage_quartermiles_gallery
                    WHERE quartermile_id = {int:qmid}',
                            array(
                                'qmid' => $context['search_results'][$count]['qmid'],
                            )
                        );
                        list($context['search_results'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
                        $smcFunc['db_free_result']($request2);
                        // If there is an image, lets find its attributes
                        $context['search_results'][$count]['image'] = '';
                        if (isset($context['search_results'][$count]['image_id'])) {
                            $request2 = $smcFunc['db_query']('', '
                        SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                        FROM {db_prefix}garage_images
                        WHERE attach_id = {int:attach_id}',
                                array(
                                    'attach_id' => $context['search_results'][$count]['image_id'],
                                )
                            );
                            list($context['search_results'][$count]['attach_location'],
                                $context['search_results'][$count]['attach_ext'],
                                $context['search_results'][$count]['attach_file'],
                                $context['search_results'][$count]['attach_desc'],
                                $context['search_results'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
                            $smcFunc['db_free_result']($request2);
                            if (empty($context['search_results'][$count]['attach_desc'])) {
                                $context['search_results'][$count]['attach_desc'] = $txt['smfg_no_desc'];
                            }

                            // Build appropriate links for remote images
                            if (isset($context['search_results'][$count]['attach_location'])) {
                                if ($context['search_results'][$count]['is_remote'] == 1) {
                                    $context['search_results'][$count]['attach_location'] = urldecode($context['search_results'][$count]['attach_location']);
                                } else {
                                    $context['search_results'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['search_results'][$count]['attach_location'];
                                }
                            }
                            // If there is an image attached, link to it
                            if (isset($context['search_results'][$count]['attach_location'])) {
                                $context['search_results'][$count]['image'] = "<a href=\"" . $context['search_results'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['search_results'][$count]['quart'] . ' @ ' . $context['search_results'][$count]['quartmph'] . ' :: ' . $context['search_results'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
                            }
                        }

                        // Define spacer
                        $context['search_results'][$count]['spacer'] = '';

                        if ($smfgSettings['enable_quartermile_video']) {

                            // Check for videos
                            $request2 = $smcFunc['db_query']('', '
                        SELECT video_id
                        FROM {db_prefix}garage_video_gallery
                        WHERE vehicle_id = {int:vid}
                            AND type = "qmile"
                            AND type_id = {int:qmid}
                            ORDER BY id ASC
                            LIMIT 1',
                                array(
                                    'vid' => $context['search_results'][$count]['vid'],
                                    'qmid' => $context['search_results'][$count]['qmid'],
                                )
                            );
                            list($context['search_results'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
                            $smcFunc['db_free_result']($request2);

                            // If there is an video, lets find its attributes
                            $context['search_results'][$count]['video'] = "";
                            if (isset($context['search_results'][$count]['video_id'])) {
                                $request2 = $smcFunc['db_query']('', '
                        SELECT url, title, video_desc
                        FROM {db_prefix}garage_video
                        WHERE id = {int:video_id}',
                                    array(
                                        'video_id' => $context['search_results'][$count]['video_id'],
                                    )
                                );
                                list($context['search_results'][$count]['video_url'],
                                    $context['search_results'][$count]['video_title'],
                                    $context['search_results'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                                $smcFunc['db_free_result']($request2);
                                if (empty($context['search_results'][$count]['video_desc'])) {
                                    $context['search_results'][$count]['video_desc'] = $txt['smfg_no_desc'];
                                }
                                $context['search_results'][$count]['video_height'] = displayVideo($context['search_results'][$count]['video_url'],
                                    'height');
                                $context['search_results'][$count]['video_width'] = displayVideo($context['search_results'][$count]['video_url'],
                                    'width');
                                if (!empty($context['search_results'][$count]['image_id']) && !empty($context['search_results'][$count]['video_id'])) {
                                    $context['search_results'][$count]['spacer'] = '&nbsp;';
                                }

                                // If there is an video attached, link to it
                                if (isset($context['search_results'][$count]['video_url'])) {
                                    $context['search_results'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['search_results'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['search_results'][$count]['video_width'] . ";height=" . $context['search_results'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['search_results'][$count]['video_title'] . '</b> :: ' . $context['search_results'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                                }
                            }

                        }

                    } else {
                        if ($_SESSION['smfg']['display_as'] == "dynoruns") {

                            list($context['search_results'][$count]['vid'],
                                $context['search_results'][$count]['did'],
                                $context['search_results'][$count]['vehicle'],
                                $context['search_results'][$count]['user_id'],
                                $context['search_results'][$count]['memberName'],
                                $context['search_results'][$count]['bhp'],
                                $context['search_results'][$count]['bhp_unit'],
                                $context['search_results'][$count]['torque'],
                                $context['search_results'][$count]['torque_unit'],
                                $context['search_results'][$count]['boost'],
                                $context['search_results'][$count]['boost_unit'],
                                $context['search_results'][$count]['nitrous'],
                                $context['search_results'][$count]['peakpoint'],
                                $context['search_results'][$count]['dynocenter'],
                                $context['search_results'][$count]['dynocenter_id']) = $row;

                            // Check for images
                            $request2 = $smcFunc['db_query']('', '
                    SELECT image_id
                    FROM {db_prefix}garage_dynoruns_gallery
                    WHERE dynorun_id = {int:did}',
                                array(
                                    'did' => $context['search_results'][$count]['did'],
                                )
                            );
                            list($context['search_results'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
                            $smcFunc['db_free_result']($request2);
                            // If there is an image, lets find its attributes
                            $context['search_results'][$count]['image'] = '';
                            if (isset($context['search_results'][$count]['image_id'])) {
                                $request2 = $smcFunc['db_query']('', '
                        SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                        FROM {db_prefix}garage_images
                        WHERE attach_id = {int:attach_id}',
                                    array(
                                        'attach_id' => $context['search_results'][$count]['image_id'],
                                    )
                                );
                                list($context['search_results'][$count]['attach_location'],
                                    $context['search_results'][$count]['attach_ext'],
                                    $context['search_results'][$count]['attach_file'],
                                    $context['search_results'][$count]['attach_desc'],
                                    $context['search_results'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
                                $smcFunc['db_free_result']($request2);
                                if (empty($context['search_results'][$count]['attach_desc'])) {
                                    $context['search_results'][$count]['attach_desc'] = $txt['smfg_no_desc'];
                                }

                                // Build appropriate links for remote images
                                if (isset($context['search_results'][$count]['attach_location'])) {
                                    if ($context['search_results'][$count]['is_remote'] == 1) {
                                        $context['search_results'][$count]['attach_location'] = urldecode($context['search_results'][$count]['attach_location']);
                                    } else {
                                        $context['search_results'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['search_results'][$count]['attach_location'];
                                    }
                                }
                                // If there is an image attached, link to it
                                if (isset($context['search_results'][$count]['attach_location'])) {
                                    $context['search_results'][$count]['image'] = "<a href=\"" . $context['search_results'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['search_results'][$count]['bhp'] . ' ' . $context['search_results'][$count]['bhp_unit'] . ' :: ' . $context['search_results'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
                                }
                            }

                            // Define spacer
                            $context['search_results'][$count]['spacer'] = '';

                            if ($smfgSettings['enable_dynorun_video']) {

                                // Check for videos
                                $request2 = $smcFunc['db_query']('', '
                        SELECT video_id
                        FROM {db_prefix}garage_video_gallery
                        WHERE vehicle_id = {int:vid}
                            AND type = "dynorun"
                            AND type_id = {int:did}
                            ORDER BY id ASC
                            LIMIT 1',
                                    array(
                                        'vid' => $context['search_results'][$count]['vid'],
                                        'did' => $context['search_results'][$count]['did'],
                                    )
                                );
                                list($context['search_results'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
                                $smcFunc['db_free_result']($request2);

                                // If there is an video, lets find its attributes
                                $context['search_results'][$count]['video'] = "";
                                if (isset($context['search_results'][$count]['video_id'])) {
                                    $request2 = $smcFunc['db_query']('', '
                            SELECT url, title, video_desc
                            FROM {db_prefix}garage_video
                            WHERE id = {int:video_id}',
                                        array(
                                            'video_id' => $context['search_results'][$count]['video_id'],
                                        )
                                    );
                                    list($context['search_results'][$count]['video_url'],
                                        $context['search_results'][$count]['video_title'],
                                        $context['search_results'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                                    $smcFunc['db_free_result']($request2);
                                    if (empty($context['search_results'][$count]['video_desc'])) {
                                        $context['search_results'][$count]['video_desc'] = $txt['smfg_no_desc'];
                                    }
                                    $context['search_results'][$count]['video_height'] = displayVideo($context['search_results'][$count]['video_url'],
                                        'height');
                                    $context['search_results'][$count]['video_width'] = displayVideo($context['search_results'][$count]['video_url'],
                                        'width');
                                    if (!empty($context['search_results'][$count]['image_id']) && !empty($context['search_results'][$count]['video_id'])) {
                                        $context['search_results'][$count]['spacer'] = '&nbsp;';
                                    }

                                    // If there is an video attached, link to it
                                    if (isset($context['search_results'][$count]['video_url'])) {
                                        $context['search_results'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['search_results'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['search_results'][$count]['video_width'] . ";height=" . $context['search_results'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['search_results'][$count]['video_title'] . '</b> :: ' . $context['search_results'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                                    }
                                }

                            }

                        } else {
                            if ($_SESSION['smfg']['display_as'] == "laps") {

                                list($context['search_results'][$count]['vid'],
                                    $context['search_results'][$count]['vehicle'],
                                    $context['search_results'][$count]['user_id'],
                                    $context['search_results'][$count]['memberName'],
                                    $context['search_results'][$count]['tid'],
                                    $context['search_results'][$count]['track'],
                                    $context['search_results'][$count]['condition'],
                                    $context['search_results'][$count]['type'],
                                    $context['search_results'][$count]['lid'],
                                    $context['search_results'][$count]['time']) = $row;

                                // Check for images
                                $request2 = $smcFunc['db_query']('', '
                    SELECT image_id
                    FROM {db_prefix}garage_laps_gallery
                    WHERE lap_id = {int:lid}',
                                    array(
                                        'lid' => $context['search_results'][$count]['lid'],
                                    )
                                );
                                list($context['search_results'][$count]['image_id']) = $smcFunc['db_fetch_row']($request2);
                                $smcFunc['db_free_result']($request2);
                                // If there is an image, lets find its attributes
                                $context['search_results'][$count]['image'] = '';
                                if (isset($context['search_results'][$count]['image_id'])) {
                                    $request2 = $smcFunc['db_query']('', '
                        SELECT attach_location, attach_ext, attach_file, attach_desc, is_remote
                        FROM {db_prefix}garage_images
                        WHERE attach_id = {int:attach_id}',
                                        array(
                                            'attach_id' => $context['search_results'][$count]['image_id'],
                                        )
                                    );
                                    list($context['search_results'][$count]['attach_location'],
                                        $context['search_results'][$count]['attach_ext'],
                                        $context['search_results'][$count]['attach_file'],
                                        $context['search_results'][$count]['attach_desc'],
                                        $context['search_results'][$count]['is_remote']) = $smcFunc['db_fetch_row']($request2);
                                    $smcFunc['db_free_result']($request2);
                                    if (empty($context['search_results'][$count]['attach_desc'])) {
                                        $context['search_results'][$count]['attach_desc'] = $txt['smfg_no_desc'];
                                    }

                                    // Build appropriate links for remote images
                                    if (isset($context['search_results'][$count]['attach_location'])) {
                                        if ($context['search_results'][$count]['is_remote'] == 1) {
                                            $context['search_results'][$count]['attach_location'] = urldecode($context['search_results'][$count]['attach_location']);
                                        } else {
                                            $context['search_results'][$count]['attach_location'] = $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['search_results'][$count]['attach_location'];
                                        }
                                    }
                                    // If there is an image attached, link to it
                                    if (isset($context['search_results'][$count]['attach_location'])) {
                                        $context['search_results'][$count]['image'] = "<a href=\"" . $context['search_results'][$count]['attach_location'] . "\" rel=\"shadowbox\" title=\"" . garage_title_clean($context['search_results'][$count]['time'] . ' @ ' . $context['search_results'][$count]['track'] . ' :: ' . $context['search_results'][$count]['attach_desc']) . "\" class=\"smfg_imageTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_camera.gif\" width=\"16\" height=\"16\" /></a>";
                                    }
                                }

                                // Define spacer
                                $context['search_results'][$count]['spacer'] = '';

                                if ($smfgSettings['enable_laptime_video']) {

                                    // Check for videos
                                    $request2 = $smcFunc['db_query']('', '
                        SELECT video_id
                        FROM {db_prefix}garage_video_gallery
                        WHERE vehicle_id = {int:vid}
                            AND type = "lap"
                            AND type_id = {int:lid}
                            ORDER BY id ASC
                            LIMIT 1',
                                        array(
                                            'vid' => $context['search_results'][$count]['vid'],
                                            'lid' => $context['search_results'][$count]['lid'],
                                        )
                                    );
                                    list($context['search_results'][$count]['video_id']) = $smcFunc['db_fetch_row']($request2);
                                    $smcFunc['db_free_result']($request2);

                                    // If there is an video, lets find its attributes
                                    $context['search_results'][$count]['video'] = "";
                                    if (isset($context['search_results'][$count]['video_id'])) {
                                        $request2 = $smcFunc['db_query']('', '
                            SELECT url, title, video_desc
                            FROM {db_prefix}garage_video
                            WHERE id = {int:video_id}',
                                            array(
                                                'video_id' => $context['search_results'][$count]['video_id'],
                                            )
                                        );
                                        list($context['search_results'][$count]['video_url'],
                                            $context['search_results'][$count]['video_title'],
                                            $context['search_results'][$count]['video_desc']) = $smcFunc['db_fetch_row']($request2);
                                        $smcFunc['db_free_result']($request2);
                                        if (empty($context['search_results'][$count]['video_desc'])) {
                                            $context['search_results'][$count]['video_desc'] = $txt['smfg_no_desc'];
                                        }
                                        $context['search_results'][$count]['video_height'] = displayVideo($context['search_results'][$count]['video_url'],
                                            'height');
                                        $context['search_results'][$count]['video_width'] = displayVideo($context['search_results'][$count]['video_url'],
                                            'width');
                                        if (!empty($context['search_results'][$count]['image_id']) && !empty($context['search_results'][$count]['video_id'])) {
                                            $context['search_results'][$count]['spacer'] = '&nbsp;';
                                        }

                                        // If there is an video attached, link to it
                                        if (isset($context['search_results'][$count]['video_url'])) {
                                            $context['search_results'][$count]['video'] = "<a href=\"" . $scripturl . "?action=garage;sa=video;id=" . $context['search_results'][$count]['video_id'] . "\" rel=\"shadowbox;width=" . $context['search_results'][$count]['video_width'] . ";height=" . $context['search_results'][$count]['video_height'] . "\" title=\"" . garage_title_clean('<b>' . $context['search_results'][$count]['video_title'] . '</b> :: ' . $context['search_results'][$count]['video_desc']) . "\" class=\"smfg_videoTitle\"><img src=\"" . $settings['default_images_url'] . "/garage_video.gif\" width=\"16\" height=\"16\" /></a>";
                                        }
                                    }

                                }

                            }
                        }
                    }
                }
            }
        }

        $count++;
    }
    $smcFunc['db_free_result']($request);

}

// Insurance Reviews
function G_Insurance()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'insurance';
    $context['page_title'] = $txt['smfg_insurance_reviews'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=insurance',
        'name' => &$txt['smfg_insurance_reviews']
    );

    // Check Permissions
    isAllowedTo('browse_insurance');

    // Make sure this module is enabled
    if (!$smfgSettings['enable_insurance']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get the total number of vehicles
    $request = $smcFunc['db_query']('', '
        SELECT count(*) 
        FROM {db_prefix}garage_business
        WHERE insurance = 1
            AND pending != "1"',
        array(// no values
        )
    );
    list($context['total']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Construct the page index, title, and add to link tree
    $context['display'] = $smfgSettings['insurance_review_limit'];
    $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=insurance', $_REQUEST['start'],
        $context['total'], $context['display']);
    $context['start'] = $_REQUEST['start'] + 1;
    $context['end'] = min($_REQUEST['start'] + $context['display'], $context['total']);
    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_insurance_reviews'] . ' &gt; ' . $txt['smfg_viewing_insurance'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=insurance' . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ';start=0'),
        'name' => $txt['smfg_viewing_insurance'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
        'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_businesses'] . ')'
    );

    // Get the insurance companies, (bid = business id)
    $request = $smcFunc['db_query']('', '
        SELECT id, title, address, telephone, fax, website, email, opening_hours
        FROM {db_prefix}garage_business
        WHERE insurance = 1
            AND pending != "1"
            ORDER BY title ASC
            LIMIT {int:start}, {int:display}',
        array(
            'start' => $_REQUEST['start'],
            'display' => $context['display'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['insurance'][$count]['bid'],
            $context['insurance'][$count]['title'],
            $context['insurance'][$count]['address'],
            $context['insurance'][$count]['telephone'],
            $context['insurance'][$count]['fax'],
            $context['insurance'][$count]['website'],
            $context['insurance'][$count]['email'],
            $context['insurance'][$count]['opening_hours']) = $row;
        // Build 'mailto' link if there is an address
        if (!empty($context['insurance'][$count]['email'])) {
            $context['insurance'][$count]['email'] = "<a href=\"mailto:" . $context['insurance'][$count]['email'] . "\">" . $context['insurance'][$count]['email'] . "</a>";
        }
        // Get coverage types and premiums, (cid = coverage id)
        $request2 = $smcFunc['db_query']('', '
            SELECT pt.id, pt.title, MIN( p.premium ) AS min, AVG( p.premium ) AS avg, MAX( p.premium ) AS max
            FROM {db_prefix}garage_premium_types AS pt
            LEFT OUTER JOIN {db_prefix}garage_premiums AS p ON p.business_id = {int:bid}
                AND p.cover_type_id = pt.id
                GROUP BY pt.id, pt.title
                ORDER BY field_order ASC',
            array(
                'bid' => $context['insurance'][$count]['bid'],
            )
        );
        $count2 = 0;
        while ($row = $smcFunc['db_fetch_row']($request2)) {
            list($context['insurance'][$count][$count2]['cid'],
                $context['insurance'][$count][$count2]['title'],
                $context['insurance'][$count][$count2]['min'],
                $context['insurance'][$count][$count2]['avg'],
                $context['insurance'][$count][$count2]['max']) = $row;
            if (!empty($context['insurance'][$count][$count2]['min'])) {
                $context['insurance'][$count][$count2]['min'] = number_format($context['insurance'][$count][$count2]['min'],
                    2, '.', ',');
            }
            if (!empty($context['insurance'][$count][$count2]['avg'])) {
                $context['insurance'][$count][$count2]['avg'] = number_format($context['insurance'][$count][$count2]['avg'],
                    2, '.', ',');
            }
            if (!empty($context['insurance'][$count][$count2]['max'])) {
                $context['insurance'][$count][$count2]['max'] = number_format($context['insurance'][$count][$count2]['max'],
                    2, '.', ',');
            }
            $count2++;
        }
        $smcFunc['db_free_result']($request2);
        $count++;
    }
    $smcFunc['db_free_result']($request);
}

// Insurance Reviews for Individual Business
function G_Insurance_Review()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'insurance_review';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=insurance',
        'name' => &$txt['smfg_insurance_reviews']
    );

    // Check Permissions
    isAllowedTo('view_insurance');

    // Make sure this module is enabled
    if (!$smfgSettings['enable_insurance']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Set the first row class for alternating bg colors
    $context['bgclass'] = "windowbg2";

    // Get the insurance companies, (bid = business id)
    $request = $smcFunc['db_query']('', '
        SELECT id, title, address, telephone, fax, website, email, opening_hours, pending
        FROM {db_prefix}garage_business
        WHERE insurance = 1
            AND id = {int:bid}
        ORDER BY id DESC
        LIMIT 0, 10',
        array(
            'bid' => $_GET['BID'],
        )
    );
    list($context['insurance']['bid'],
        $context['insurance']['title'],
        $context['insurance']['address'],
        $context['insurance']['telephone'],
        $context['insurance']['fax'],
        $context['insurance']['website'],
        $context['insurance']['email'],
        $context['insurance']['opening_hours'],
        $context['insurance']['pending']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if business is pending or if they have permission to view pending items
    if ($context['insurance']['pending'] == '1' && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_item_error', false);
    }

    // Build 'mailto' link if there is an address
    if (!empty($context['insurance']['email'])) {
        $context['insurance']['email'] = "<a href=\"mailto:" . $context['insurance']['email'] . "\">" . $context['insurance']['email'] . "</a>";
    }

    // Add to linktree
    $context['page_title'] = $txt['smfg_insurance_reviews'] . ' &gt; ' . $context['insurance']['title'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=insurance_review;BID=' . $context['insurance']['bid'],
        'name' => $context['insurance']['title']
    );

    //print_r($context['insurance']);

    // Get coverage types and premiums, (cid = coverage id)
    $request = $smcFunc['db_query']('', '
        SELECT pt.id, pt.title, MIN( p.premium ) AS min, AVG( p.premium ) AS avg, MAX( p.premium ) AS max
        FROM {db_prefix}garage_premium_types AS pt
        LEFT OUTER JOIN {db_prefix}garage_premiums AS p ON p.business_id = {int:bid}
            AND p.cover_type_id = pt.id
            GROUP BY pt.id, pt.title
            ORDER BY field_order ASC',
        array(
            'bid' => $context['insurance']['bid'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['insurance'][$count]['cid'],
            $context['insurance'][$count]['title'],
            $context['insurance'][$count]['min'],
            $context['insurance'][$count]['avg'],
            $context['insurance'][$count]['max']) = $row;
        if (!empty($context['insurance'][$count]['min'])) {
            $context['insurance'][$count]['min'] = number_format($context['insurance'][$count]['min'], 2, '.', ',');
        }
        if (!empty($context['insurance'][$count]['avg'])) {
            $context['insurance'][$count]['avg'] = number_format($context['insurance'][$count]['avg'], 2, '.', ',');
        }
        if (!empty($context['insurance'][$count]['max'])) {
            $context['insurance'][$count]['max'] = number_format($context['insurance'][$count]['max'], 2, '.', ',');
        }
        $count++;
    }
    $smcFunc['db_free_result']($request);

    $request = $smcFunc['db_query']('', '
        SELECT u.real_name, v.user_id, v.id, CONCAT_WS(" ", v.made_year, mk.make, md.model), p.id, p.premium, pt.title
        FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_premiums AS p, {db_prefix}garage_premium_types AS pt, {db_prefix}members AS u
        WHERE p.business_id = {int:bid}
            AND p.vehicle_id = v.id
            AND v.user_id = u.id_member
            AND v.make_id = mk.id
            AND v.model_id = md.id
            AND p.cover_type_id = pt.id
            AND mk.pending != "1"
            AND md.pending != "1"
            AND v.pending != "1"
            ORDER BY p.premium ASC
            LIMIT 0, 5',
        array(
            'bid' => $context['insurance']['bid'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['insurance'][$count]['memberName'],
            $context['insurance'][$count]['user_id'],
            $context['insurance'][$count]['vid'],
            $context['insurance'][$count]['vehicle'],
            $context['insurance'][$count]['pid'],
            $context['insurance'][$count]['premium'],
            $context['insurance'][$count]['cover_type']) = $row;
        $context['insurance'][$count]['premium'] = number_format($context['insurance'][$count]['premium'], 2, '.',
            ',');;
        $count++;
    }
    $smcFunc['db_free_result']($request);

}

// Shop Reviews
function G_Shops()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'shops';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=shops',
        'name' => &$txt['smfg_shop_reviews']
    );

    // Check Permissions
    isAllowedTo('browse_shops');

    // Set the first row class for alternating bg colors
    $context['bgclass'] = "windowbg2";

    // Get the total number of vehicles
    $request = $smcFunc['db_query']('', '
        SELECT count(*) 
        FROM {db_prefix}garage_business
        WHERE retail = 1
            AND pending != "1"',
        array(// no values
        )
    );
    list($context['total']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Construct the page index, title, and add to link tree
    $context['display'] = $smfgSettings['shop_review_limit'];
    $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=shops', $_REQUEST['start'],
        $context['total'], $context['display']);
    $context['start'] = $_REQUEST['start'] + 1;
    $context['end'] = min($_REQUEST['start'] + $context['display'], $context['total']);
    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_shop_reviews'] . ' &gt; ' . $txt['smfg_viewing_shops'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=shops' . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ''),
        'name' => $txt['smfg_viewing_shops'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
        'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_businesses'] . ')'
    );

    // Get the shops, (bid = business id)
    $request = $smcFunc['db_query']('', '
        SELECT id, title, address, telephone, fax, website, email, opening_hours
        FROM {db_prefix}garage_business
        WHERE retail = 1
            AND pending != "1"
            ORDER BY title ASC
            LIMIT {int:start}, {int:display}',
        array(
            'start' => $_REQUEST['start'],
            'display' => $context['display'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['shops'][$count]['bid'],
            $context['shops'][$count]['title'],
            $context['shops'][$count]['address'],
            $context['shops'][$count]['telephone'],
            $context['shops'][$count]['fax'],
            $context['shops'][$count]['website'],
            $context['shops'][$count]['email'],
            $context['shops'][$count]['opening_hours']) = $row;
        // Build 'mailto' link if there is an address
        if (!empty($context['shops'][$count]['email'])) {
            $context['shops'][$count]['email'] = "<a href=\"mailto:" . $context['shops'][$count]['email'] . "\">" . $context['shops'][$count]['email'] . "</a>";
        }

        if ($smfgSettings['rating_system'] == 0) {
            $ratingfunc = "SUM";
        } else {
            if ($smfgSettings['rating_system'] == 1) {
                $ratingfunc = "AVG";
            }
        }

        // Get the total rating of the shop
        $request2 = $smcFunc['db_query']('', '
            SELECT ' . $ratingfunc . '( purchase_rating ), COUNT( id )
            FROM {db_prefix}garage_modifications
            WHERE shop_id = {int:bid}
                AND pending != "1"',
            array(
                'bid' => $context['shops'][$count]['bid'],
            )
        );
        list($context['shops'][$count]['total_rating'],
            $context['shops'][$count]['total_poss_rating']) = $smcFunc['db_fetch_row']($request2);
        if ($context['shops'][$count]['total_rating'] > 0) {
            $context['shops'][$count]['total_rating'] = number_format($context['shops'][$count]['total_rating'], 2, '.',
                ',');
        } else {
            $context['shops'][$count]['total_rating'] = 0;
        }
        $smcFunc['db_free_result']($request2);

        if ($context['shops'][$count]['total_poss_rating'] > 0) {
            $context['shops'][$count]['total_poss_rating'] = $context['shops'][$count]['total_poss_rating'] * 10;
        } else {
            $context['shops'][$count]['total_poss_rating'] = 0;
        }

        if ($context['shops'][$count]['total_poss_rating'] > 0) {
            $context['shops'][$count]['total_poss_rating'] = number_format($context['shops'][$count]['total_poss_rating'],
                2, '.', ',');
        }

        $request2 = $smcFunc['db_query']('', '
            SELECT u.real_name, v.id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), m.id, m.price, m.product_rating, m.purchase_rating, p.title
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_modifications AS m, {db_prefix}garage_products AS p, {db_prefix}members AS u
            WHERE m.shop_id = {int:bid}
                AND m.product_id = p.id
                AND m.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND v.user_id = u.id_member
                AND v.pending != "1"
                AND mk.pending != "1"
                AND md.pending != "1"
                AND m.pending != "1"
                ORDER BY m.date_created DESC
                LIMIT 0, 10',
            array(
                'bid' => $context['shops'][$count]['bid'],
            )
        );
        $count2 = 0;
        while ($row = $smcFunc['db_fetch_row']($request2)) {
            list($context['shops'][$count][$count2]['memberName'],
                $context['shops'][$count][$count2]['vid'],
                $context['shops'][$count][$count2]['user_id'],
                $context['shops'][$count][$count2]['vehicle'],
                $context['shops'][$count][$count2]['mid'],
                $context['shops'][$count][$count2]['price'],
                $context['shops'][$count][$count2]['product_rating'],
                $context['shops'][$count][$count2]['purchase_rating'],
                $context['shops'][$count][$count2]['mod_title']) = $row;
            $context['shops'][$count][$count2]['price'] = number_format($context['shops'][$count][$count2]['price'], 2,
                '.', ',');
            $count2++;
        }
        $smcFunc['db_free_result']($request2);
        $count++;
    }
    $smcFunc['db_free_result']($request);
}

// Shop Reviews for Individual Business
function G_Shop_Review()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'shop_review';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=shops',
        'name' => &$txt['smfg_shop_reviews']
    );

    // Check Permissions
    isAllowedTo('view_shops');

    // Set the first row class for alternating bg colors
    $context['bgclass'] = "windowbg2";

    // Get the shops, (bid = business id)
    $request = $smcFunc['db_query']('', '
        SELECT id, title, address, telephone, fax, website, email, opening_hours, pending
        FROM {db_prefix}garage_business
        WHERE retail = 1
            AND id = {int:bid}',
        array(
            'bid' => $_GET['BID'],
        )
    );
    list($context['shops']['bid'],
        $context['shops']['title'],
        $context['shops']['address'],
        $context['shops']['telephone'],
        $context['shops']['fax'],
        $context['shops']['website'],
        $context['shops']['email'],
        $context['shops']['opening_hours'],
        $context['shops']['pending']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if business is pending or if they have permission to view pending items
    if ($context['shops']['pending'] == '1' && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_item_error', false);
    }

    // Build 'mailto' link if there is an address
    if (!empty($context['shops']['email'])) {
        $context['shops']['email'] = "<a href=\"mailto:" . $context['shops']['email'] . "\">" . $context['shops']['email'] . "</a>";
    }
    // Add to linktree
    $context['page_title'] = $txt['smfg_shop_reviews'] . ' &gt; ' . $context['shops']['title'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=shop_review;BID=' . $context['shops']['bid'],
        'name' => $context['shops']['title']
    );

    if ($smfgSettings['rating_system'] == 0) {
        $ratingfunc = "SUM";
    } else {
        if ($smfgSettings['rating_system'] == 1) {
            $ratingfunc = "AVG";
        }
    }

    // Get the total rating of the shop
    $request = $smcFunc['db_query']('', '
            SELECT ' . $ratingfunc . '( purchase_rating ), COUNT( id )
            FROM {db_prefix}garage_modifications
            WHERE shop_id = {int:bid}
                AND pending != "1"',
        array(
            'bid' => $context['shops']['bid'],
        )
    );
    list($context['shops']['total_rating'],
        $context['shops']['total_poss_rating']) = $smcFunc['db_fetch_row']($request);
    if ($context['shops']['total_rating'] > 0) {
        $context['shops']['total_rating'] = number_format($context['shops']['total_rating'], 2, '.', ',');
    } else {
        $context['shops']['total_rating'] = 0;
    }
    $smcFunc['db_free_result']($request);

    if ($context['shops']['total_poss_rating'] > 0) {
        $context['shops']['total_poss_rating'] = $context['shops']['total_poss_rating'] * 10;
    } else {
        $context['shops']['total_poss_rating'] = 0;
    }

    if ($context['shops']['total_poss_rating'] > 0) {
        $context['shops']['total_poss_rating'] = number_format($context['shops']['total_poss_rating'], 2, '.', ',');
    }

    $request = $smcFunc['db_query']('', '
            SELECT u.real_name, v.id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), m.id, m.price, m.product_rating, m.purchase_rating, m.comments, p.title
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_modifications AS m, {db_prefix}garage_products AS p, {db_prefix}members AS u
            WHERE m.shop_id = {int:bid}
                AND m.product_id = p.id
                AND m.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND v.user_id = u.id_member
                AND v.pending != "1"
                AND mk.pending != "1"
                AND md.pending != "1"
                AND m.pending != "1"
                ORDER BY m.date_created DESC
                LIMIT 0, 10',
        array(
            'bid' => $context['shops']['bid'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['shops'][$count]['memberName'],
            $context['shops'][$count]['vid'],
            $context['shops'][$count]['user_id'],
            $context['shops'][$count]['vehicle'],
            $context['shops'][$count]['mid'],
            $context['shops'][$count]['price'],
            $context['shops'][$count]['product_rating'],
            $context['shops'][$count]['purchase_rating'],
            $context['shops'][$count]['comment'],
            $context['shops'][$count]['mod_title']) = $row;
        $context['shops'][$count]['price'] = number_format($context['shops'][$count]['price'], 2, '.', ',');
        $count++;
    }
    $smcFunc['db_free_result']($request);

}

// Garage Reviews
function G_Garages()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'garages';
    $context['page_title'] = $txt['smfg_garage_reviews'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=garages',
        'name' => &$txt['smfg_garage_reviews']
    );

    // Check Permissions
    isAllowedTo('browse_garages');

    // Set the first row class for alternating bg colors
    $context['bgclass'] = "windowbg2";

    // Get the total number of vehicles
    $request = $smcFunc['db_query']('', '
        SELECT count(*) 
        FROM {db_prefix}garage_business
        WHERE garage = 1
            AND pending != "1"',
        array(// no values
        )
    );
    list($context['total']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Construct the page index, title, and add to link tree
    $context['display'] = $smfgSettings['garage_review_limit'];
    $context['page_index'] = constructPageIndex($scripturl . '?action=garage;sa=garages', $_REQUEST['start'],
        $context['total'], $context['display']);
    $context['start'] = $_REQUEST['start'] + 1;
    $context['end'] = min($_REQUEST['start'] + $context['display'], $context['total']);
    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_garage_reviews'] . ' &gt; ' . $txt['smfg_viewing_garages'] . ' ' . $context['start'] . ' ' . $txt['smfg_to'] . ' ' . $context['end'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=garages' . (isset($_REQUEST['start']) ? ';start=' . $_REQUEST['start'] : ''),
        'name' => $txt['smfg_viewing_garages'] . ' ' . ($context['total'] == 0 ? $context['total'] : $context['start']) . ' ' . $txt['smfg_to'] . ' ' . $context['end'] . '',
        'extra_after' => ' (' . $txt['smfg_of'] . ' ' . $context['total'] . ' ' . $txt['smfg_total_businesses'] . ')'
    );

    // Get the garages, (bid = business id)
    $request = $smcFunc['db_query']('', '
        SELECT id, title, address, telephone, fax, website, email, opening_hours
        FROM {db_prefix}garage_business
        WHERE garage = 1
            AND pending != "1"
            ORDER BY title ASC
            LIMIT {int:start}, {int:display}',
        array(
            'start' => $_REQUEST['start'],
            'display' => $context['display'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['garages'][$count]['bid'],
            $context['garages'][$count]['title'],
            $context['garages'][$count]['address'],
            $context['garages'][$count]['telephone'],
            $context['garages'][$count]['fax'],
            $context['garages'][$count]['website'],
            $context['garages'][$count]['email'],
            $context['garages'][$count]['opening_hours']) = $row;
        // Build 'mailto' link if there is an address
        if (!empty($context['garages'][$count]['email'])) {
            $context['garages'][$count]['email'] = "<a href=\"mailto:" . $context['garages'][$count]['email'] . "\">" . $context['garages'][$count]['email'] . "</a>";
        }

        // Get the total rating of the garage from mod installations
        $request2 = $smcFunc['db_query']('', '
            SELECT SUM( install_rating ), COUNT( id )*10, COUNT( id )
            FROM {db_prefix}garage_modifications
            WHERE installer_id = {int:bid}
                AND pending != "1"
                GROUP BY installer_id',
            array(
                'bid' => $context['garages'][$count]['bid'],
            )
        );
        list($context['garages'][$count]['total_install_rating'],
            $context['garages'][$count]['total_install_poss_rating'],
            $context['garages'][$count]['total_install_ratings']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        // Get the total rating of the garage from service
        $request2 = $smcFunc['db_query']('', '
            SELECT SUM( rating ), COUNT( id )*10, COUNT( id )
            FROM {db_prefix}garage_service_history
            WHERE garage_id = {int:bid}
                GROUP BY garage_id',
            array(
                'bid' => $context['garages'][$count]['bid'],
            )
        );
        list($context['garages'][$count]['total_service_rating'],
            $context['garages'][$count]['total_service_poss_rating'],
            $context['garages'][$count]['total_service_ratings']) = $smcFunc['db_fetch_row']($request2);
        $smcFunc['db_free_result']($request2);

        // Total install and service ratings
        $context['garages'][$count]['total_ratings'] = $context['garages'][$count]['total_install_ratings'] + $context['garages'][$count]['total_service_ratings'];
        if ($smfgSettings['rating_system'] == 0) {
            $context['garages'][$count]['total_rating'] = $context['garages'][$count]['total_install_rating'] + $context['garages'][$count]['total_service_rating'];
            $context['garages'][$count]['total_poss_rating'] = $context['garages'][$count]['total_install_poss_rating'] + $context['garages'][$count]['total_service_poss_rating'];
        } else {
            if ($smfgSettings['rating_system'] == 1) {
                if ($context['garages'][$count]['total_ratings'] > 0) { // Never divide by zero
                    $context['garages'][$count]['total_rating'] = (($context['garages'][$count]['total_install_rating'] + $context['garages'][$count]['total_service_rating']) / $context['garages'][$count]['total_ratings']);
                    $context['garages'][$count]['total_poss_rating'] = (($context['garages'][$count]['total_install_poss_rating'] + $context['garages'][$count]['total_service_poss_rating']) / $context['garages'][$count]['total_ratings']);
                } else {
                    $context['garages'][$count]['total_rating'] = 0;
                    $context['garages'][$count]['total_poss_rating'] = 0;
                }
            }
        }
        if ($context['garages'][$count]['total_rating'] > 0) {
            $context['garages'][$count]['total_rating'] = number_format($context['garages'][$count]['total_rating'], 2,
                '.', ',');
        } else {
            $context['garages'][$count]['total_rating'] = 0;
        }
        if ($context['garages'][$count]['total_poss_rating'] > 0) {
            $context['garages'][$count]['total_poss_rating'] = number_format($context['garages'][$count]['total_poss_rating'],
                2, '.', ',');
        } else {
            $context['garages'][$count]['total_poss_rating'] = 0;
        }

        // Get mods that used this garage
        $request2 = $smcFunc['db_query']('', '
            SELECT u.real_name, v.id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), m.id, m.install_rating, p.title, m.comments
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_modifications AS m, {db_prefix}garage_products AS p, {db_prefix}members AS u
            WHERE m.installer_id = {int:bid}
                AND m.product_id = p.id
                AND m.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND v.user_id = u.id_member
                AND v.pending != "1"
                AND mk.pending != "1"
                AND md.pending != "1"
                AND m.pending != "1"
                ORDER BY m.date_created DESC
                LIMIT 0, 10',
            array(
                'bid' => $context['garages'][$count]['bid'],
            )
        );
        $count2 = 0;
        while ($row = $smcFunc['db_fetch_row']($request2)) {
            list($context['garages']['mods'][$count][$count2]['memberName'],
                $context['garages']['mods'][$count][$count2]['vid'],
                $context['garages']['mods'][$count][$count2]['user_id'],
                $context['garages']['mods'][$count][$count2]['vehicle'],
                $context['garages']['mods'][$count][$count2]['mid'],
                $context['garages']['mods'][$count][$count2]['install_rating'],
                $context['garages']['mods'][$count][$count2]['mod_title'],
                $context['garages']['mods'][$count][$count2]['comment']) = $row;
            $count2++;
        }
        $smcFunc['db_free_result']($request2);

        // Get services that used this garage
        $request2 = $smcFunc['db_query']('', '
        SELECT u.real_name, v.id AS vid, s.id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), s.rating, st.title
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u, {db_prefix}garage_service_history AS s, {db_prefix}garage_service_types AS st
            WHERE s.garage_id = {int:bid}
                AND s.garage_id = {int:bid}
                AND s.type_id = st.id
                AND s.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND v.user_id = u.id_member
                AND v.pending != "1"
                AND mk.pending != "1"
                AND md.pending != "1"
                ORDER BY s.date_created DESC
                LIMIT 0, 5',
            array(
                'bid' => $context['garages'][$count]['bid'],
            )
        );
        $count2 = 0;
        while ($row = $smcFunc['db_fetch_row']($request2)) {
            list($context['garages']['services'][$count][$count2]['memberName'],
                $context['garages']['services'][$count][$count2]['vid'],
                $context['garages']['services'][$count][$count2]['sid'],
                $context['garages']['services'][$count][$count2]['user_id'],
                $context['garages']['services'][$count][$count2]['vehicle'],
                $context['garages']['services'][$count][$count2]['rating'],
                $context['garages']['services'][$count][$count2]['service_type']) = $row;
            $count2++;
        }
        $smcFunc['db_free_result']($request2);
        $count++;
    }
    $smcFunc['db_free_result']($request);
}

// Garage Reviews
function G_Garage_Review()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'garage_review';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=garages',
        'name' => &$txt['smfg_garage_reviews']
    );

    // Check Permissions
    isAllowedTo('view_garages');

    // Set the first row class for alternating bg colors
    $context['bgclass'] = "windowbg2";

    // Get the garages, (bid = business id)
    $request = $smcFunc['db_query']('', '
        SELECT title, address, telephone, fax, website, email, opening_hours, pending
        FROM {db_prefix}garage_business
        WHERE garage = 1
            AND id = {int:bid}',
        array(
            'bid' => $_GET['BID'],
        )
    );
    list($context['garages']['title'],
        $context['garages']['address'],
        $context['garages']['telephone'],
        $context['garages']['fax'],
        $context['garages']['website'],
        $context['garages']['email'],
        $context['garages']['opening_hours'],
        $context['garages']['pending']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if business is pending or if they have permission to view pending items
    if ($context['garages']['pending'] == '1' && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_item_error', false);
    }

    // Build 'mailto' link if there is an address
    if (!empty($context['garages']['email'])) {
        $context['garages']['email'] = "<a href=\"mailto:" . $context['garages']['email'] . "\">" . $context['garages']['email'] . "</a>";
    }

    // Construct the page index, title, and add to link tree
    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_garage_reviews'] . ' &gt; ' . $context['garages']['title'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=garage_review;BID=' . $_GET['BID'],
        'name' => $context['garages']['title']
    );

    // Get the total rating of the garage from mod installations
    $request2 = $smcFunc['db_query']('', '
                SELECT SUM( install_rating ), COUNT( id )*10, COUNT( id )
                FROM {db_prefix}garage_modifications
                WHERE installer_id = {int:bid}
                    AND pending != "1"
                    GROUP BY installer_id',
        array(
            'bid' => $_GET['BID'],
        )
    );
    list($context['garages']['total_install_rating'],
        $context['garages']['total_install_poss_rating'],
        $context['garages']['total_install_ratings']) = $smcFunc['db_fetch_row']($request2);
    $smcFunc['db_free_result']($request2);

    // Get the total rating of the garage from service
    $request2 = $smcFunc['db_query']('', '
                SELECT SUM( rating ), COUNT( id )*10, COUNT( id )
                FROM {db_prefix}garage_service_history
                WHERE garage_id = {int:bid}
                    GROUP BY garage_id',
        array(
            'bid' => $_GET['BID'],
        )
    );
    list($context['garages']['total_service_rating'],
        $context['garages']['total_service_poss_rating'],
        $context['garages']['total_service_ratings']) = $smcFunc['db_fetch_row']($request2);
    $smcFunc['db_free_result']($request2);

    // Total install and service ratings
    $context['garages']['total_ratings'] = $context['garages']['total_install_ratings'] + $context['garages']['total_service_ratings'];
    if ($smfgSettings['rating_system'] == 0) {
        $context['garages']['total_rating'] = $context['garages']['total_install_rating'] + $context['garages']['total_service_rating'];
        $context['garages']['total_poss_rating'] = $context['garages']['total_install_poss_rating'] + $context['garages']['total_service_poss_rating'];
    } else {
        if ($smfgSettings['rating_system'] == 1) {
            if ($context['garages']['total_ratings'] > 0) { // Never divide by zero
                $context['garages']['total_rating'] = (($context['garages']['total_install_rating'] + $context['garages']['total_service_rating']) / $context['garages']['total_ratings']);
                $context['garages']['total_poss_rating'] = (($context['garages']['total_install_poss_rating'] + $context['garages']['total_service_poss_rating']) / $context['garages']['total_ratings']);
            } else {
                $context['garages']['total_rating'] = 0;
                $context['garages']['total_poss_rating'] = 0;
            }
        }
    }
    if ($context['garages']['total_rating'] > 0) {
        $context['garages']['total_rating'] = number_format($context['garages']['total_rating'], 2, '.', ',');
    } else {
        $context['garages']['total_rating'] = 0;
    }
    if ($context['garages']['total_poss_rating'] > 0) {
        $context['garages']['total_poss_rating'] = number_format($context['garages']['total_poss_rating'], 2, '.', ',');
    } else {
        $context['garages']['total_poss_rating'] = 0;
    }

    // Get mods that used this garage
    $request = $smcFunc['db_query']('', '
            SELECT u.real_name, v.id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), m.id, m.install_rating, p.title, m.install_comments
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_modifications AS m, {db_prefix}garage_products AS p, {db_prefix}members AS u
            WHERE m.installer_id = {int:bid}
                AND m.product_id = p.id
                AND m.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND v.user_id = u.id_member
                AND v.pending != "1"
                AND mk.pending != "1"
                AND md.pending != "1"
                AND m.pending != "1"
                ORDER BY m.id DESC
                LIMIT 0, 10',
        array(
            'bid' => $_GET['BID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['garages']['mods'][$count]['memberName'],
            $context['garages']['mods'][$count]['vid'],
            $context['garages']['mods'][$count]['user_id'],
            $context['garages']['mods'][$count]['vehicle'],
            $context['garages']['mods'][$count]['mid'],
            $context['garages']['mods'][$count]['install_rating'],
            $context['garages']['mods'][$count]['mod_title'],
            $context['garages']['mods'][$count]['comment']) = $row;
        $count++;
    }
    $context['garages']['mods']['total'] = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Get services that used this garage
    $request = $smcFunc['db_query']('', '
        SELECT u.real_name, v.id AS vid, s.id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), s.rating, st.title
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u, {db_prefix}garage_service_history AS s, {db_prefix}garage_service_types AS st
            WHERE s.garage_id = {int:bid}
                AND s.garage_id = {int:bid}
                AND s.type_id = st.id
                AND s.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND v.user_id = u.id_member
                AND v.pending != "1"
                AND mk.pending != "1"
                AND md.pending != "1"
                ORDER BY s.id DESC
                LIMIT 0, 5',
        array(
            'bid' => $_GET['BID'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['garages']['services'][$count]['memberName'],
            $context['garages']['services'][$count]['vid'],
            $context['garages']['services'][$count]['sid'],
            $context['garages']['services'][$count]['user_id'],
            $context['garages']['services'][$count]['vehicle'],
            $context['garages']['services'][$count]['rating'],
            $context['garages']['services'][$count]['service_type']) = $row;
        $count++;
    }
    $smcFunc['db_free_result']($request);
}

// Manufacturer Reviews for Individual Business
function G_Manufacturer_Review()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'manufacturer_review';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=mfg_review;BID=' . $_GET['BID'],
        'name' => &$txt['smfg_manufacturer_reviews']
    );

    // Check Permissions
    isAllowedTo('view_garage');

    // Set the first row class for alternating bg colors
    $context['bgclass'] = "windowbg2";

    // Get the mfgs, (bid = business id)
    $request = $smcFunc['db_query']('', '
        SELECT id, title, address, telephone, fax, website, email, opening_hours, pending
        FROM {db_prefix}garage_business
        WHERE product = 1
            AND id = {int:bid}',
        array(
            'bid' => $_GET['BID'],
        )
    );
    list($context['mfg']['bid'],
        $context['mfg']['title'],
        $context['mfg']['address'],
        $context['mfg']['telephone'],
        $context['mfg']['fax'],
        $context['mfg']['website'],
        $context['mfg']['email'],
        $context['mfg']['opening_hours'],
        $context['mfg']['pending']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if business is pending or if they have permission to view pending items
    if ($context['mfg']['pending'] == '1' && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_item_error', false);
    }

    // Build 'mailto' link if there is an address
    if (!empty($context['mfg']['email'])) {
        $context['shops']['email'] = "<a href=\"mailto:" . $context['shops']['email'] . "\">" . $context['mfg']['email'] . "</a>";
    }
    // Add to linktree
    $context['page_title'] = $txt['smfg_manufacturer_reviews'] . ' &gt; ' . $context['mfg']['title'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=mfg_review;BID=' . $context['mfg']['bid'],
        'name' => $context['mfg']['title']
    );

    if ($smfgSettings['rating_system'] == 0) {
        $ratingfunc = "SUM";
    } else {
        if ($smfgSettings['rating_system'] == 1) {
            $ratingfunc = "AVG";
        }
    }

    // Get the total rating of the shop
    $request = $smcFunc['db_query']('', '
            SELECT ' . $ratingfunc . '( product_rating ), COUNT( id )
            FROM {db_prefix}garage_modifications
            WHERE manufacturer_id = {int:bid}
                AND pending != "1"',
        array(
            'bid' => $context['mfg']['bid'],
        )
    );
    list($context['mfg']['total_rating'],
        $context['mfg']['total_poss_rating']) = $smcFunc['db_fetch_row']($request);
    if ($context['mfg']['total_rating'] > 0) {
        $context['mfg']['total_rating'] = number_format($context['mfg']['total_rating'], 2, '.', ',');
    } else {
        $context['mfg']['total_rating'] = 0;
    }
    $smcFunc['db_free_result']($request);

    if ($context['mfg']['total_poss_rating'] > 0) {
        $context['mfg']['total_poss_rating'] = $context['mfg']['total_poss_rating'] * 10;
    } else {
        $context['mfg']['total_poss_rating'] = 0;
    }

    if ($context['mfg']['total_poss_rating'] > 0) {
        $context['mfg']['total_poss_rating'] = number_format($context['mfg']['total_poss_rating'], 2, '.', ',');
    }

    $request = $smcFunc['db_query']('', '
            SELECT u.real_name, v.id, v.user_id, CONCAT_WS(" ", v.made_year, mk.make, md.model), m.id, m.price, m.product_rating, m.purchase_rating, m.comments, p.title
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}garage_modifications AS m, {db_prefix}garage_products AS p, {db_prefix}members AS u
            WHERE m.manufacturer_id = {int:bid}
                AND m.product_id = p.id
                AND m.vehicle_id = v.id
                AND v.make_id = mk.id
                AND v.model_id = md.id
                AND v.user_id = u.id_member
                AND v.pending != "1"
                AND mk.pending != "1"
                AND md.pending != "1"
                AND m.pending != "1"
                ORDER BY m.date_created DESC
                LIMIT 0, 10',
        array(
            'bid' => $context['mfg']['bid'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context['mfg'][$count]['memberName'],
            $context['mfg'][$count]['vid'],
            $context['mfg'][$count]['user_id'],
            $context['mfg'][$count]['vehicle'],
            $context['mfg'][$count]['mid'],
            $context['mfg'][$count]['price'],
            $context['mfg'][$count]['product_rating'],
            $context['mfg'][$count]['purchase_rating'],
            $context['mfg'][$count]['comment'],
            $context['mfg'][$count]['mod_title']) = $row;
        $context['mfg'][$count]['price'] = number_format($context['mfg'][$count]['price'], 2, '.', ',');
        $count++;
    }
    $smcFunc['db_free_result']($request);

}

// Dynocenter Reviews for Individual Business
function G_Dynocenter_Review()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'dynocenter_review';
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=dc_review;BID=' . $_GET['BID'],
        'name' => &$txt['smfg_dynocenter_reviews']
    );

    // Check Permissions
    isAllowedTo('view_garage');

    // Set the first row class for alternating bg colors
    $context['bgclass'] = "windowbg2";

    // Get the dynocenterss, (bid = business id)
    $request = $smcFunc['db_query']('', '
        SELECT id, title, address, telephone, fax, website, email, opening_hours, pending
        FROM {db_prefix}garage_business
        WHERE dynocenter = 1
            AND id = {int:bid}',
        array(
            'bid' => $_GET['BID'],
        )
    );
    list($context['dc']['bid'],
        $context['dc']['title'],
        $context['dc']['address'],
        $context['dc']['telephone'],
        $context['dc']['fax'],
        $context['dc']['website'],
        $context['dc']['email'],
        $context['dc']['opening_hours'],
        $context['dc']['pending']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Check if business is pending or if they have permission to view pending items
    if ($context['dc']['pending'] == '1' && !allowedTo('manage_garage_pending')) {
        loadLanguage('Errors');
        fatal_lang_error('garage_pending_item_error', false);
    }

    // Build 'mailto' link if there is an address
    if (!empty($context['dc']['email'])) {
        $context['dc']['email'] = "<a href=\"mailto:" . $context['dc']['email'] . "\">" . $context['dc']['email'] . "</a>";
    }
    // Add to linktree
    $context['page_title'] = $txt['smfg_dynocenter_reviews'] . ' &gt; ' . $context['dc']['title'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=dc_review;BID=' . $context['dc']['bid'],
        'name' => $context['dc']['title']
    );

}

// Quartermiles Table
function G_Quartermiles()
{
    browse_tables("quartermiles");
}

// Dyno Runs Table
function G_Dynoruns()
{
    browse_tables("dynoruns");
}

// Lap Times Table
function G_Laptimes()
{
    browse_tables("laps");
}

// Modifications Table
function G_Modifications()
{
    browse_tables("modifications");
}

// Most Modified Table
function G_Most_Modified()
{
    browse_tables("mostmodified");
}

// Most Viewed Table
function G_Most_Viewed()
{
    browse_tables("mostviewed");
}

// Latest Service Table
function G_Latest_Service()
{
    browse_tables("latestservice");
}

// Top Rated Table
function G_Top_Rated()
{
    browse_tables("toprated");
}

// Most Spent Table
function G_Most_Spent()
{
    browse_tables("mostspent");
}

// Latest Blog Table
function G_Latest_Blog()
{
    browse_tables("latestblog");
}

// Latest Video Table
function G_Latest_Video()
{
    browse_tables("latestvideo");
}

// Insert Vehicle Images
function G_Insert_Vehicle_Images()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    $context['vehicle_id'] = $_POST['VID'];
    $context['date_created'] = time();

    // Check gallery limits
    checkLimits($context['vehicle_id'], 'garage');

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_vehicle_images']) {

        // Check maximum file size from MAX_FILE_SIZE
        if ($_FILES['FILE_UPLOAD']['error'] == 2) {
            loadLanguage('Errors');
            fatal_lang_error('garage_filesize_error', false);
        }

        // If they provided an image to upload use it, otherwise use the remote image
        // handle_images($gallery, $source(0=local,1=remote), $file, $extra)
        if ($_FILES['FILE_UPLOAD']['error'] == 0) {

            // Check for maximum file size allowed...again, just in case they made it this far
            $max_fsize = $smfgSettings['max_image_kbytes'] * 1024;
            if ($_FILES['FILE_UPLOAD']['size'] > $max_fsize) {
                loadLanguage('Errors');
                fatal_lang_error('garage_filesize_error', false);
            }

            // Check for maximum image resolution
            $dimensions = getimagesize($_FILES['FILE_UPLOAD']['tmp_name']);
            if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                loadLanguage('Errors');
                fatal_lang_error('garage_resolution_error', false);
            }

            // If they made it this far, go ahead and process the image
            handle_images("garage", 0, $_FILES['FILE_UPLOAD'], $_POST);

        } // Check if a remote image was supplied...then use it
        else {
            if ($_POST['url_image'] !== 'cahttp://' && $_POST['url_image'] !== 'https://') {

                // Check for maximum image resolution
                $dimensions = getimagesize_remote($_POST['url_image']);
                if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                    loadLanguage('Errors');
                    fatal_lang_error('garage_resolution_error', false);
                }

                // If they made it this far, go ahead and process the image
                handle_images("garage", 1, $_POST);
            } // If they didn't supply an image here, well, send them back cuz we cant do anything
            else {

                loadLanguage('Errors');
                fatal_lang_error('garage_no_image_supplied', false);

            }
        }

    }

    // Is there already a hilite?
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_vehicles_gallery
        WHERE vehicle_id = {int:vid}',
        array(
            'vid' => $context['vehicle_id'],
        )
    );
    $results = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    if ($results > 0) {
        $hilite = 0;
    } else {
        $hilite = 1;
    }

    // Insert table date for vehicles_gallery
    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_vehicles_gallery',
        array(
            'vehicle_id' => 'int',
            'image_id' => 'int',
            'hilite' => 'int',
        ),
        array(
            $context['vehicle_id'],
            $context['image_id'],
            $hilite,
        ),
        array(// no data
        )
    );

    // Update vehicle's' "last updated" time
    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_vehicles 
        SET date_updated = {int:date_created}
        WHERE id = {int:vid}',
        array(
            'date_created' => $context['date_created'],
            'vid' => $context['vehicle_id'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_vehicle;VID='.$context['vehicle_id'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Remove Vehicle Image
function G_Remove_Vehicle_Image()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boarddir;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Get the file location
    $request = $smcFunc['db_query']('', '
        SELECT attach_location, attach_thumb_location 
        FROM {db_prefix}garage_images
        WHERE attach_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );
    list($context['attach_location'],
        $context['attach_thumb_location']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Remove the db entries
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_images
        WHERE attach_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_vehicles_gallery
        WHERE image_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    $dir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $cachedir = $dir . 'cache/';
    unlink($dir . $context['attach_location']);
    unlink($cachedir . $context['attach_location']);
    unlink($cachedir . $context['attach_thumb_location']);

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_vehicle;VID='.$_GET['VID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Vehicle Video
function G_Insert_Vehicle_Video()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    $context['vehicle_id'] = $_POST['VID'];
    $context['date_created'] = date('Y', time());

    // Check gallery limits
    checkLimits($context['vehicle_id'], 'garage', 0, 'video');

    // Perform video actions if images are enabled
    if ($smfgSettings['enable_vehicle_video']) {

        // Check for video URL compatibility
        if (!displayVideo($_POST['video_url'], 4)) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_unsupported', false);
        }

        // Insert video
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video',
            array(
                'vehicle_id' => 'int',
                'url' => 'string',
                'title' => 'string',
                'video_desc' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['video_url'],
                $_POST['video_title'],
                $_POST['video_desc'],
            ),
            array(// no data
            )
        );
        $context['video_id'] = $smcFunc['db_insert_id']($request);

        // Insert table data for video_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video_gallery',
            array(
                'vehicle_id' => 'int',
                'video_id' => 'int',
                'type' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $context['video_id'],
                'vehicle',
            ),
            array(// no data
            )
        );

        // Update vehicle's' "last updated" time
        $request = $smcFunc['db_query']('', '
            UPDATE {db_prefix}garage_vehicles 
            SET date_updated = {int:date_created}
            WHERE id = {int:vid}',
            array(
                'vid' => $context['vehicle_id'],
                'date_created' => $context['date_created'],
            )
        );

    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_vehicle;VID='.$context['vehicle_id'].'#video');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Modification Images
function G_Insert_Modification_Images()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';
    $context['vehicle_id'] = $_POST['VID'];
    $context['MID'] = $_POST['MID'];
    $context['date_created'] = time();

    // Make sure this module is enabled
    if ($smfgSettings['enable_modification'] != 1) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Validate Owner
    checkOwner($context['vehicle_id']);

    // Check gallery limits
    checkLimits($context['vehicle_id'], 'mod', $context['MID']);

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_modification_images']) {

        // Check maximum file size from MAX_FILE_SIZE
        if ($_FILES['FILE_UPLOAD']['error'] == 2) {
            loadLanguage('Errors');
            fatal_lang_error('garage_filesize_error', false);
        }

        // If they provided an image to upload use it, otherwise use the remote image
        // handle_images($gallery, $source(0=local,1=remote), $file, $extra)
        if ($_FILES['FILE_UPLOAD']['error'] == 0) {

            // Check for maximum file size allowed...again, just in case they made it this far
            $max_fsize = $smfgSettings['max_image_kbytes'] * 1024;
            if ($_FILES['FILE_UPLOAD']['size'] > $max_fsize) {
                loadLanguage('Errors');
                fatal_lang_error('garage_filesize_error', false);
            }

            // Check for maximum image resolution
            $dimensions = getimagesize($_FILES['FILE_UPLOAD']['tmp_name']) or die('Could not obtain image dimensions.');
            if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                loadLanguage('Errors');
                fatal_lang_error('garage_resolution_error', false);
            }

            // If they made it this far, go ahead and process the image
            handle_images("mod", 0, $_FILES['FILE_UPLOAD'], $_POST);

        } // Check if a remote image was supplied...then use it
        else {
            if ($_POST['url_image'] !== 'http://' && $_POST['url_image'] !== 'https://') {

                // Check for maximum image resolution
                $dimensions = getimagesize_remote($_POST['url_image']) or die('Could not obtain remote image dimensions.');
                if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                    loadLanguage('Errors');
                    fatal_lang_error('garage_resolution_error', false);
                }

                // If they made it this far, go ahead and process the image
                handle_images("mod", 1, $_POST);
            } // If they didn't supply an image here, well, send them back cuz we cant do anything
            else {

                loadLanguage('Errors');
                fatal_lang_error('garage_no_image_supplied', false);

            }
        }

    }

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_modification_images']) {

        // Is there already a hilite?
        $request = $smcFunc['db_query']('', '
            SELECT id
            FROM {db_prefix}garage_modifications_gallery
            WHERE modification_id = {int:mid}',
            array(
                'mid' => $context['MID'],
            )
        );
        $results = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);
        if ($results > 0) {
            $hilite = 0;
        } else {
            $hilite = 1;
        }

        // Insert table data for modifications_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_modifications_gallery',
            array(
                'vehicle_id' => 'int',
                'modification_id' => 'int',
                'image_id' => 'int',
                'hilite' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['MID'],
                $context['image_id'],
                $hilite,
            ),
            array(// no data
            )
        );

        // Update modification's' "last updated" time
        $request = $smcFunc['db_query']('', '
            UPDATE {db_prefix}garage_modifications 
            SET date_updated = {int:date_created}
            WHERE id = {int:mid}',
            array(
                'mid' => $context['MID'],
                'date_created' => $context['date_created'],
            )
        );

    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_modification;VID='.$context['vehicle_id'].';MID='.$context['MID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Remove Modification Image
function G_Remove_Modification_Image()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boarddir;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Make sure this module is enabled
    if ($smfgSettings['enable_modification'] != 1) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get the file location
    $request = $smcFunc['db_query']('', '
        SELECT attach_location, attach_thumb_location 
        FROM {db_prefix}garage_images
        WHERE attach_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );
    list($context['attach_location'],
        $context['attach_thumb_location']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Remove the db entries
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_images
        WHERE attach_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_modifications_gallery
        WHERE image_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    $dir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $cachedir = $dir . 'cache/';
    unlink($dir . $context['attach_location']);
    unlink($cachedir . $context['attach_location']);
    unlink($cachedir . $context['attach_thumb_location']);

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_modification;VID='.$_GET['VID'].';MID='.$_GET['MID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Modification Video
function G_Insert_Modification_Video()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    $context['vehicle_id'] = $_POST['VID'];
    $context['mod_id'] = $_POST['MID'];
    $context['date_created'] = time();

    // Validate Owner
    checkOwner($context['vehicle_id']);

    // Check gallery limits
    checkLimits($context['vehicle_id'], 'mod', $context['mod_id'], 'video');

    // Perform video actions if videos are enabled
    if ($smfgSettings['enable_modification_video']) {

        // Check for video URL compatibility
        if (!displayVideo($_POST['video_url'], 4)) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_unsupported', false);
        }

        // Insert video
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video',
            array(
                'vehicle_id' => 'int',
                'url' => 'string',
                'title' => 'string',
                'video_desc' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['video_url'],
                $_POST['video_title'],
                $_POST['video_desc'],
            ),
            array(// no data
            )
        );
        $context['video_id'] = $smcFunc['db_insert_id']($request);

        // Insert table data for video_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video_gallery',
            array(
                'vehicle_id' => 'int',
                'video_id' => 'int',
                'type' => 'string',
                'type_id' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['video_id'],
                'mod',
                $context['mod_id'],
            ),
            array(// no data
            )
        );

        // Update modification's' "last updated" time
        $request = $smcFunc['db_query']('', '
            UPDATE {db_prefix}garage_modifications 
            SET date_updated = {int:date_created}
            WHERE id = {int:mod_id}',
            array(
                'date_created' => $context['date_created'],
                'mod_id' => $context['mod_id'],
            )
        );

    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_vehicle;VID='.$context['vehicle_id'].'#video');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Quartermile Images
function G_Insert_Quartermile_Images()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';
    $context['vehicle_id'] = $_POST['VID'];
    $context['QID'] = $_POST['QID'];
    $context['date_created'] = time();

    // Make sure this module is enabled
    if ($smfgSettings['enable_quartermile'] != 1) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Validate Owner
    checkOwner($context['vehicle_id']);

    // Check gallery limits
    checkLimits($context['vehicle_id'], 'qmile', $context['QID']);

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_quartermile_images']) {

        // Check maximum file size from MAX_FILE_SIZE
        if ($_FILES['FILE_UPLOAD']['error'] == 2) {
            loadLanguage('Errors');
            fatal_lang_error('garage_filesize_error', false);
        }

        // If they provided an image to upload use it, otherwise use the remote image
        // handle_images($gallery, $source(0=local,1=remote), $file, $extra)
        if ($_FILES['FILE_UPLOAD']['error'] == 0) {

            // Check for maximum file size allowed...again, just in case they made it this far
            $max_fsize = $smfgSettings['max_image_kbytes'] * 1024;
            if ($_FILES['FILE_UPLOAD']['size'] > $max_fsize) {
                loadLanguage('Errors');
                fatal_lang_error('garage_filesize_error', false);
            }

            // Check for maximum image resolution
            $dimensions = getimagesize($_FILES['FILE_UPLOAD']['tmp_name']) or die('Could not obtain image dimensions.');
            if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                loadLanguage('Errors');
                fatal_lang_error('garage_resolution_error', false);
            }

            // If they made it this far, go ahead and process the image
            handle_images("qmile", 0, $_FILES['FILE_UPLOAD'], $_POST);

        } // Check if a remote image was supplied...then use it
        else {
            if ($_POST['url_image'] !== 'http://' && $_POST['url_image'] !== 'https://') {

                // Check for maximum image resolution
                $dimensions = getimagesize_remote($_POST['url_image']) or die('Could not obtain remote image dimensions.');
                if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                    loadLanguage('Errors');
                    fatal_lang_error('garage_resolution_error', false);
                }

                // If they made it this far, go ahead and process the image
                handle_images("qmile", 1, $_POST);
            } // If they didn't supply an image here, well, send them back cuz we cant do anything
            else {

                loadLanguage('Errors');
                fatal_lang_error('garage_no_image_supplied', false);

            }
        }

    }

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_quartermile_images']) {

        // Is there already a hilite?
        $request = $smcFunc['db_query']('', '
            SELECT id
            FROM {db_prefix}garage_quartermiles_gallery
            WHERE quartermile_id = {int:qid}',
            array(
                'qid' => $context['QID'],
            )
        );
        $results = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);
        if ($results > 0) {
            $hilite = 0;
        } else {
            $hilite = 1;
        }

        // Insert table data for quartermiles_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_quartermiles_gallery',
            array(
                'vehicle_id' => 'int',
                'quartermile_id' => 'int',
                'image_id' => 'int',
                'hilite' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['QID'],
                $context['image_id'],
                $hilite,
            ),
            array(// no data
            )
        );
    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_quartermile;VID='.$context['vehicle_id'].';QID='.$context['QID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Remove Quartermile Image
function G_Remove_Quartermile_Image()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boarddir;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Make sure this module is enabled
    if ($smfgSettings['enable_quartermile'] != 1) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get the file location
    $request = $smcFunc['db_query']('', '
        SELECT attach_location, attach_thumb_location 
        FROM {db_prefix}garage_images
        WHERE attach_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );
    list($context['attach_location'],
        $context['attach_thumb_location']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Remove the db entries
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_images
        WHERE attach_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_quartermiles_gallery
        WHERE image_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    $dir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $cachedir = $dir . 'cache/';
    unlink($dir . $context['attach_location']);
    unlink($cachedir . $context['attach_location']);
    unlink($cachedir . $context['attach_thumb_location']);

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_quartermile;VID='.$_GET['VID'].';QID='.$_GET['QID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Quartermile Video
function G_Insert_Quartermile_Video()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    $context['vehicle_id'] = $_POST['VID'];
    $context['qmile_id'] = $_POST['QID'];

    // Validate Owner
    checkOwner($context['vehicle_id']);

    // Check gallery limits
    checkLimits($context['vehicle_id'], 'qmile', $context['qmile_id'], 'video');

    // Perform video actions if images are enabled
    if ($smfgSettings['enable_quartermile_video']) {

        // Check for video URL compatibility
        if (!displayVideo($_POST['video_url'], 4)) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_unsupported', false);
        }

        // Insert video
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video',
            array(
                'vehicle_id' => 'int',
                'url' => 'string',
                'title' => 'string',
                'video_desc' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['video_url'],
                $_POST['video_title'],
                $_POST['video_desc'],
            ),
            array(// no data
            )
        );
        $context['video_id'] = $smcFunc['db_insert_id']($request);

        // Insert table data for video_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video_gallery',
            array(
                'vehicle_id' => 'int',
                'video_id' => 'int',
                'type' => 'string',
                'type_id' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['video_id'],
                'qmile',
                $context['qmile_id'],
            ),
            array(// no data
            )
        );

    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_vehicle;VID='.$context['vehicle_id'].'#video');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Dynorun Images
function G_Insert_Dynorun_Images()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';
    $context['vehicle_id'] = $_POST['VID'];
    $context['DID'] = $_POST['DID'];
    $context['date_created'] = time();

    // Make sure this module is enabled
    if ($smfgSettings['enable_dynorun'] != 1) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Validate Owner
    checkOwner($context['vehicle_id']);

    // Check gallery limits
    checkLimits($context['vehicle_id'], 'dynorun', $context['DID']);

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_dynorun_images']) {

        // Check maximum file size from MAX_FILE_SIZE
        if ($_FILES['FILE_UPLOAD']['error'] == 2) {
            loadLanguage('Errors');
            fatal_lang_error('garage_filesize_error', false);
        }

        // If they provided an image to upload use it, otherwise use the remote image
        // handle_images($gallery, $source(0=local,1=remote), $file, $extra)
        if ($_FILES['FILE_UPLOAD']['error'] == 0) {

            // Check for maximum file size allowed...again, just in case they made it this far
            $max_fsize = $smfgSettings['max_image_kbytes'] * 1024;
            if ($_FILES['FILE_UPLOAD']['size'] > $max_fsize) {
                loadLanguage('Errors');
                fatal_lang_error('garage_filesize_error', false);
            }

            // Check for maximum image resolution
            $dimensions = getimagesize($_FILES['FILE_UPLOAD']['tmp_name']);
            if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                loadLanguage('Errors');
                fatal_lang_error('garage_resolution_error', false);
            }

            // If they made it this far, go ahead and process the image
            handle_images("dynorun", 0, $_FILES['FILE_UPLOAD'], $_POST);

        } // Check if a remote image was supplied...then use it
        else {
            if ($_POST['url_image'] !== 'http://' && $_POST['url_image'] !== 'https://') {

                // Check for maximum image resolution
                $dimensions = getimagesize_remote($_POST['url_image']);
                if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                    loadLanguage('Errors');
                    fatal_lang_error('garage_resolution_error', false);
                }

                // If they made it this far, go ahead and process the image
                handle_images("dynorun", 1, $_POST);
            } // If they didn't supply an image here, well, send them back cuz we cant do anything
            else {

                loadLanguage('Errors');
                fatal_lang_error('garage_no_image_supplied', false);

            }
        }

    }

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_dynorun_images']) {

        // Is there already a hilite?
        $request = $smcFunc['db_query']('', '
            SELECT id
            FROM {db_prefix}garage_dynoruns_gallery
            WHERE dynorun_id = {int:did}',
            array(
                'did' => $context['DID'],
            )
        );
        $results = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);
        if ($results > 0) {
            $hilite = 0;
        } else {
            $hilite = 1;
        }

        // Insert table data for dynoruns_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_dynoruns_gallery',
            array(
                'vehicle_id' => 'int',
                'dynorun_id' => 'int',
                'image_id' => 'int',
                'hilite' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['DID'],
                $context['image_id'],
                $hilite,
            ),
            array(// no data
            )
        );

    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_dynorun;VID='.$context['vehicle_id'].';DID='.$context['DID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Remove Dynorun Image
function G_Remove_Dynorun_Image()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boarddir;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Make sure this module is enabled
    if ($smfgSettings['enable_dynorun'] != 1) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get the file location
    $request = $smcFunc['db_query']('', '
        SELECT attach_location, attach_thumb_location 
        FROM {db_prefix}garage_images
        WHERE attach_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );
    list($context['attach_location'],
        $context['attach_thumb_location']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Remove the db entries
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_images
        WHERE attach_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_dynoruns_gallery
        WHERE image_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    $dir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $cachedir = $dir . 'cache/';
    unlink($dir . $context['attach_location']);
    unlink($cachedir . $context['attach_location']);
    unlink($cachedir . $context['attach_thumb_location']);

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_dynorun;VID='.$_GET['VID'].';DID='.$_GET['DID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Dynorun Video
function G_Insert_Dynorun_Video()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    $context['vehicle_id'] = $_POST['VID'];
    $context['dynorun_id'] = $_POST['DID'];

    // Validate Owner
    checkOwner($context['vehicle_id']);

    // Check gallery limits
    checkLimits($context['vehicle_id'], 'dynorun', $context['dynorun_id'], 'video');

    // Perform video actions if images are enabled
    if ($smfgSettings['enable_dynorun_video']) {

        // Check for video URL compatibility
        if (!displayVideo($_POST['video_url'], 4)) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_unsupported', false);
        }

        // Insert video
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video',
            array(
                'vehicle_id' => 'int',
                'url' => 'string',
                'title' => 'string',
                'video_desc' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['video_url'],
                $_POST['video_title'],
                $_POST['video_desc'],
            ),
            array(// no data
            )
        );
        $context['video_id'] = $smcFunc['db_insert_id']($request);

        // Insert table data for video_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video_gallery',
            array(
                'vehicle_id' => 'int',
                'video_id' => 'int',
                'type' => 'string',
                'type_id' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['video_id'],
                'dynorun',
                $context['dynorun_id'],
            ),
            array(// no data
            )
        );

    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_vehicle;VID='.$context['vehicle_id'].'#video');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Laptime Images
function G_Insert_Laptime_Images()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';
    $context['vehicle_id'] = $_POST['VID'];
    $context['LID'] = $_POST['LID'];
    $context['date_created'] = time();

    // Make sure this module is enabled
    if ($smfgSettings['enable_laptimes'] != 1) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Validate Owner
    checkOwner($context['vehicle_id']);

    // Check gallery limits
    checkLimits($context['vehicle_id'], 'lap', $context['LID']);

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_lap_images']) {

        // Check maximum file size from MAX_FILE_SIZE
        if ($_FILES['FILE_UPLOAD']['error'] == 2) {
            loadLanguage('Errors');
            fatal_lang_error('garage_filesize_error', false);
        }

        // If they provided an image to upload use it, otherwise use the remote image
        // handle_images($gallery, $source(0=local,1=remote), $file, $extra)
        if ($_FILES['FILE_UPLOAD']['error'] == 0) {

            // Check for maximum file size allowed...again, just in case they made it this far
            $max_fsize = $smfgSettings['max_image_kbytes'] * 1024;
            if ($_FILES['FILE_UPLOAD']['size'] > $max_fsize) {
                loadLanguage('Errors');
                fatal_lang_error('garage_filesize_error', false);
            }

            // Check for maximum image resolution
            $dimensions = getimagesize($_FILES['FILE_UPLOAD']['tmp_name']);
            if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                loadLanguage('Errors');
                fatal_lang_error('garage_resolution_error', false);
            }

            // If they made it this far, go ahead and process the image
            handle_images("lap", 0, $_FILES['FILE_UPLOAD'], $_POST);

        } // Check if a remote image was supplied...then use it
        else {
            if ($_POST['url_image'] !== 'http://' && $_POST['url_image'] !== 'https://') {

                // Check for maximum image resolution
                $dimensions = getimagesize_remote($_POST['url_image']);
                if ($dimensions[0] > $smfgSettings['max_image_resolution'] | $dimensions[1] > $smfgSettings['max_image_resolution']) {
                    loadLanguage('Errors');
                    fatal_lang_error('garage_resolution_error', false);
                }

                // If they made it this far, go ahead and process the image
                handle_images("lap", 1, $_POST);
            } // If they didn't supply an image here, well, send them back cuz we cant do anything
            else {

                loadLanguage('Errors');
                fatal_lang_error('garage_no_image_supplied', false);

            }
        }

    }

    // Perform image actions if images are enabled
    if ($smfgSettings['enable_lap_images']) {

        // Is there already a hilite?
        $request = $smcFunc['db_query']('', '
            SELECT id
            FROM {db_prefix}garage_laps_gallery
            WHERE lap_id = {int:lid}',
            array(
                'lid' => $context['LID'],
            )
        );
        $results = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);
        if ($results > 0) {
            $hilite = 0;
        } else {
            $hilite = 1;
        }

        // Insert table data for laps_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_laps_gallery',
            array(
                'vehicle_id' => 'int',
                'lap_id' => 'int',
                'image_id' => 'int',
                'hilite' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['LID'],
                $context['image_id'],
                $hilite,
            ),
            array(// no data
            )
        );

    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_laptime;VID='.$context['vehicle_id'].';LID='.$context['LID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Remove Laptime Image
function G_Remove_Laptime_Image()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boarddir;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Make sure this module is enabled
    if ($smfgSettings['enable_laptimes'] != 1) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Get the file location
    $request = $smcFunc['db_query']('', '
        SELECT attach_location, attach_thumb_location 
        FROM {db_prefix}garage_images
        WHERE attach_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );
    list($context['attach_location'],
        $context['attach_thumb_location']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // Remove the db entries
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_images
        WHERE attach_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_laps_gallery
        WHERE image_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    $dir = $boarddir . '/' . $smfgSettings['upload_directory'];
    $cachedir = $dir . 'cache/';
    unlink($dir . $context['attach_location']);
    unlink($cachedir . $context['attach_location']);
    unlink($cachedir . $context['attach_thumb_location']);

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_laptime;VID='.$_GET['VID'].';LID='.$_GET['LID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Laptime Video
function G_Insert_Laptime_Video()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    $context['vehicle_id'] = $_POST['VID'];
    $context['lap_id'] = $_POST['LID'];

    // Validate Owner
    checkOwner($context['vehicle_id']);

    // Check gallery limits
    checkLimits($context['vehicle_id'], 'lap', $context['lap_id'], 'video');

    // Perform video actions if images are enabled
    if ($smfgSettings['enable_laptime_video']) {

        // Check for video URL compatibility
        if (!displayVideo($_POST['video_url'], 4)) {
            loadLanguage('Errors');
            fatal_lang_error('garage_video_unsupported', false);
        }

        // Insert video
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video',
            array(
                'vehicle_id' => 'int',
                'url' => 'string',
                'title' => 'string',
                'video_desc' => 'string',
            ),
            array(
                $context['vehicle_id'],
                $_POST['video_url'],
                $_POST['video_title'],
                $_POST['video_desc'],
            ),
            array(// no data
            )
        );
        $context['video_id'] = $smcFunc['db_insert_id']($request);

        // Insert table data for video_gallery
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_video_gallery',
            array(
                'vehicle_id' => 'int',
                'video_id' => 'int',
                'type' => 'string',
                'type_id' => 'int',
            ),
            array(
                $context['vehicle_id'],
                $context['video_id'],
                'lap',
                $context['lap_id'],
            ),
            array(// no data
            )
        );

    }

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_vehicle;VID='.$context['vehicle_id'].'#video');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Remove Video
function G_Remove_Video()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boarddir;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Remove the db entries
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_video
        WHERE id = {int:video_id}',
        array(
            'video_id' => $_GET['video_id'],
        )
    );

    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_video_gallery
        WHERE video_id = {int:video_id}',
        array(
            'video_id' => $_GET['video_id'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_vehicle;VID='.$_GET['VID'].'#video');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Set Main Vehicle
function G_Set_Main_Vehicle()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Check if there is already a main vehicle
    $request = $smcFunc['db_query']('', '
        SELECT id 
        FROM {db_prefix}garage_vehicles
        WHERE user_id = {int:user_id}
            AND main_vehicle = 1',
        array(
            'user_id' => $_GET['user_id'],
        )
    );
    list($context['main_vehicle']['VID']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // If there is, turn it off
    if (isset($context['main_vehicle']['VID']) && $context['main_vehicle']['VID'] > 0) {
        $request = $smcFunc['db_query']('', '
              UPDATE {db_prefix}garage_vehicles
              SET main_vehicle = 0 
              WHERE id = {int:vid}',
            array(
                'vid' => $context['main_vehicle']['VID'],
            )
        );
    }

    // If not, just set the current image to hilite
    $request = $smcFunc['db_query']('', '
          UPDATE {db_prefix}garage_vehicles
          SET main_vehicle = 1 
          WHERE id = {int:vid}',
        array(
            'vid' => $_GET['VID'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=view_own_vehicle;VID='.$_GET['VID']);
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Set Hilite Image for Vehicle
function G_Set_Hilite_Image()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Check if there is already a hilite
    $request = $smcFunc['db_query']('', '
          SELECT image_id 
          FROM {db_prefix}garage_vehicles_gallery 
          WHERE vehicle_id = {int:vid} 
            AND hilite = 1',
        array(
            'vid' => $_GET['VID'],
        )
    );
    list($context['hilite']['image_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // If there is, turn it off
    if (isset($context['hilite']['image_id']) && $context['hilite']['image_id'] > 0) {
        $request = $smcFunc['db_query']('', '
              UPDATE {db_prefix}garage_vehicles_gallery 
              SET hilite = 0 
              WHERE image_id = {int:image_id}',
            array(
                'image_id' => $context['hilite']['image_id'],
            )
        );
    }

    // If not, just set the current image to hilite
    $request = $smcFunc['db_query']('', '
          UPDATE {db_prefix}garage_vehicles_gallery 
          SET hilite = 1 
          WHERE image_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_vehicle;VID='.$_GET['VID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Set Hilite Image for Modification
function G_Set_Hilite_Image_Mod()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Check if there is already a hilite
    $request = $smcFunc['db_query']('', '
          SELECT image_id 
          FROM {db_prefix}garage_modifications_gallery 
          WHERE modification_id = {int:mid} 
            AND hilite = 1',
        array(
            'mid' => $_GET['MID'],
        )
    );
    list($context['hilite']['image_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // If there is, turn it off
    if (isset($context['hilite']['image_id']) && $context['hilite']['image_id'] > 0) {
        $request = $smcFunc['db_query']('', '
              UPDATE {db_prefix}garage_modifications_gallery 
              SET hilite = 0 
              WHERE image_id = {int:image_id}',
            array(
                'image_id' => $context['hilite']['image_id'],
            )
        );
    }

    // If not, just set the current image to hilite
    $request = $smcFunc['db_query']('', ' 
          UPDATE {db_prefix}garage_modifications_gallery 
          SET hilite = 1 
          WHERE image_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_modification;VID='.$_GET['VID'].';MID='.$_GET['MID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Set Hilite Image for Quartermiles
function G_Set_Hilite_Image_Quartermile()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Check if there is already a hilite
    $request = $smcFunc['db_query']('', '
          SELECT image_id 
          FROM {db_prefix}garage_quartermiles_gallery 
          WHERE quartermile_id = {int:qid} 
            AND hilite = 1',
        array(
            'qid' => $_GET['QID'],
        )
    );
    list($context['hilite']['image_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // If there is, turn it off
    if (isset($context['hilite']['image_id']) && $context['hilite']['image_id'] > 0) {
        $request = $smcFunc['db_query']('', '
              UPDATE {db_prefix}garage_quartermiles_gallery 
              SET hilite = 0 
              WHERE image_id = {int:image_id}',
            array(
                'image_id' => $context['hilite']['image_id'],
            )
        );
    }

    // If not, just set the current image to hilite
    $request = $smcFunc['db_query']('', '
          UPDATE {db_prefix}garage_quartermiles_gallery 
          SET hilite = 1 
          WHERE image_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_quartermile;VID='.$_GET['VID'].';QID='.$_GET['QID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Set Hilite Image for Dynoruns
function G_Set_Hilite_Image_Dynorun()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Check if there is already a hilite
    $request = $smcFunc['db_query']('', '
          SELECT image_id 
          FROM {db_prefix}garage_dynoruns_gallery 
          WHERE dynorun_id = {int:did} 
            AND hilite = 1',
        array(
            'did' => $_GET['DID'],
        )
    );
    list($context['hilite']['image_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // If there is, turn it off
    if (isset($context['hilite']['image_id']) && $context['hilite']['image_id'] > 0) {
        $request = $smcFunc['db_query']('', '
              UPDATE {db_prefix}garage_dynoruns_gallery 
              SET hilite = 0 
              WHERE image_id = {int:image_id}',
            array(
                'image_id' => $context['hilite']['image_id'],
            )
        );
    }

    // If not, just set the current image to hilite
    $request = $smcFunc['db_query']('', '
          UPDATE {db_prefix}garage_dynoruns_gallery 
          SET hilite = 1 
          WHERE image_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_dynorun;VID='.$_GET['VID'].';DID='.$_GET['DID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Set Hilite Image for Laptimes
function G_Set_Hilite_Image_Laptime()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession('get');

    // Validate Owner
    checkOwner($_GET['VID']);

    // Check if there is already a hilite
    $request = $smcFunc['db_query']('', '
          SELECT image_id 
          FROM {db_prefix}garage_laps_gallery 
          WHERE lap_id = {int:lid}
            AND hilite = 1',
        array(
            'lid' => $_GET['LID'],
        )
    );
    list($context['hilite']['image_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    // If there is, turn it off
    if (isset($context['hilite']['image_id']) && $context['hilite']['image_id'] > 0) {
        $request = $smcFunc['db_query']('', '
              UPDATE {db_prefix}garage_laps_gallery 
              SET hilite = 0 
              WHERE image_id = {int:image_id}',
            array(
                'image_id' => $context['hilite']['image_id'],
            )
        );
    }

    // If not, just set the current image to hilite
    $request = $smcFunc['db_query']('', '
          UPDATE {db_prefix}garage_laps_gallery 
          SET hilite = 1 
          WHERE image_id = {int:image_id}',
        array(
            'image_id' => $_GET['image_id'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=edit_laptime;VID='.$_GET['VID'].';LID='.$_GET['LID'].'#images');
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Update Image/Video Title/Descriptions
function G_Update_Text()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Check to see if they left the default no description message
    if ($_POST['value'] == $txt['smfg_no_desc']) {
        echo $txt['smfg_no_desc'];
        exit;
    }

    // Check for data
    if (isset($_POST) && !empty($_POST)) {

        // Figure out type of update
        if (strpos($_POST['id'], 'image') !== false) {
            $type = 'image_desc';
        } else {
            if (strpos($_POST['id'], 'video_title') !== false) {
                $type = 'video_title';
            } else {
                $type = 'video_desc';
            }
        }

        // Strip the ID and then return a result for ajax
        switch ($type) {
            case 'image_desc':
                $id = substr($_POST['id'], 5);
                $request = $smcFunc['db_query']('', '
                    UPDATE {db_prefix}garage_images
                    SET attach_desc = {string:content}
                    WHERE attach_id = {int:id}',
                    array(
                        'content' => $_POST['value'],
                        'id' => $id,
                    )
                );
                break;
            case 'video_title':
                $id = substr($_POST['id'], 11);
                $request = $smcFunc['db_query']('', '
                    UPDATE {db_prefix}garage_video
                    SET title = {string:content}
                    WHERE id = {int:id}',
                    array(
                        'content' => $_POST['value'],
                        'id' => $id,
                    )
                );
                break;
            case 'video_desc':
                $id = substr($_POST['id'], 5);
                $request = $smcFunc['db_query']('', '
                    UPDATE {db_prefix}garage_video
                    SET video_desc = {string:content}
                    WHERE id = {int:id}',
                    array(
                        'content' => $_POST['value'],
                        'id' => $id,
                    )
                );
                break;
        }

        // output the status
        if (!$request) {
            //echo '0';
            echo 'Update Failed.';
            exit;
        } else {
            //echo '1';
            echo $_POST['value'];
            exit;
        }
        $smcFunc['db_free_result']($request);

        // No data, update failed
    } else {
        //echo '0';
        echo 'Update Failed.';
        exit;
    }

}

// Update Image Descriptions
function G_Update_Attach_Desc()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    $objID = substr($_POST['id'], 5);

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_images
        SET attach_desc = {string:desc}
        WHERE attach_id = {int:image_id}',
        array(
            'desc' => $_POST['value'],
            'image_id' => $objID,
        )
    );
    if (!$request) {
        //echo '0';
        echo 'Update failed.';
        exit;
    } else {
        //echo '1';
        echo $_POST['value'];
        exit;
    }
    $smcFunc['db_free_result']($request);

}

// Update Video Descriptions
function G_Update_Video_Desc()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    $objID = substr($_POST['id'], 5);

    $request = $smcFunc['db_query']('', '
        UPDATE {db_prefix}garage_video
        SET video_desc = {string:desc}
        WHERE id = {int:video_id}',
        array(
            'desc' => $_POST['value'],
            'video_id' => $objID,
        )
    );
    if (!$request) {
        echo '0';
        exit;
    } else {
        echo '1';
        exit;
    }
    $smcFunc['db_free_result']($request);

}

// Insert Rating
function G_Insert_Rating()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate the session
    checkSession();

    $context['date_created'] = time();

    // Don't let them rate their own vehicle
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_vehicles
        WHERE user_id = {int:user_id}',
        array(
            'user_id' => $context['user']['id'],
        )
    );
    $count = 0;
    while ($row = $smcFunc['db_fetch_row']($request)) {
        list($context[$count]['vid']) = $row;
        if ($context[$count]['vid'] == $_POST['VID']) {
            loadLanguage('Errors');
            fatal_lang_error('garage_rating_error2', false);
        }
        $count++;
    }
    $smcFunc['db_free_result']($request);

    // Make sure this vehicle has not been rated by this user before
    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_ratings
        WHERE vehicle_id = {int:vid}
            AND user_id = {int:user_id}',
        array(
            'vid' => $_POST['VID'],
            'user_id' => $context['user']['id'],
        )
    );
    $results = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($results > 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_double_rating_error', false);
    }

    // If they made it this far, insert data into {db_prefix}garage_ratings
    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_ratings',
        array(
            'vehicle_id' => 'int',
            'rating' => 'int',
            'user_id' => 'int',
            'rate_date' => 'int',
        ),
        array(
            $_POST['VID'],
            $_POST['rating'],
            $_POST['user_id'],
            time(),
        ),
        array(// no data
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=view_vehicle;VID='.$_POST['VID']);
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// Insert Rating
function G_Remove_Rating()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func, $ext;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate session
    checkSession('get');

    // Ensure this is their comment to remove
    $request = $smcFunc['db_query']('', '
        SELECT user_id
        FROM {db_prefix}garage_ratings
        WHERE id = {int:rid}',
        array(
            'rid' => $_GET['RID'],
        )
    );
    list($context['user_id']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($context['user_id'] != $context['user']['id']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_rating_error', false);
    }

    // Remove the db entries
    $request = $smcFunc['db_query']('', '
        DELETE FROM {db_prefix}garage_ratings
        WHERE id = {int:rid}',
        array(
            'rid' => $_GET['RID'],
        )
    );

    //header( 'Location: '.$scripturl.'?action=garage;sa=view_vehicle;VID='.$_GET['VID']);
    $newurl = $_POST['redirecturl'];
    header('Location: ' . $newurl);

}

// User Submit a Make
function G_Submit_Make()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Make sure this module is enabled
    if (!$smfgSettings['enable_user_submit_make']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Build link tree
    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_submit_make'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=submit_make',
        'name' => 'Submit Make'
    );

    echo '
    <html>
    <head>
    <title>' . $txt['smfg_submit_make'] . '</title>';

    echo '
    <link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?rc3" />
    <link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/print.css?rc3" media="print" />
    <script language="JavaScript" src="', $settings['default_theme_url'], '/gen_validatorv2.js" type="text/javascript"></script>';

    echo '
    </head>
    <body style="background-image: none; background-color: #fff;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_submit_make'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=submit_make_insert" enctype="multipart/form-data" method="post" id="submit_make" name="submit_make" style="padding:0; margin:0;">
            <input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" />
            <table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_make'] . '</b>&nbsp;</td>
                    <td><input name="make" type="text" size="15" value="" /></td>
                </tr>
                <tr>
                    <td align="center" height="28" colspan="2"><input type="hidden" name="redirecturl" value="', $_SESSION['old_url'], '" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_submit_make'] . '" /></td>
                </tr>      
            </table>
            </form>
            <script language="JavaScript" type="text/javascript">
            var frmvalidator = new Validator("submit_make");
            frmvalidator.addValidation("make","req","' . $txt['smfg_val_enter_make'] . '");
            frmvalidator.addValidation("make","regexp=^[. A-Za-z0-9]{1,30}$","' . $txt['smfg_val_enter_valid_make'] . '");
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </body>
    </html>';

    exit;
}

// User Submit a Model
function G_Submit_Model()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Make sure this module is enabled
    if (!$smfgSettings['enable_user_submit_model']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Build link tree
    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_submit_model'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=submit_model',
        'name' => 'Submit Model'
    );

    echo '
    <html>
    <head>
    <title>' . $txt['smfg_submit_make'] . '</title>';

    echo '
    <link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?rc3" />
    <link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/print.css?rc3" media="print" />
    <script language="JavaScript" src="', $settings['default_theme_url'], '/gen_validatorv2.js" type="text/javascript"></script>';

    echo '
    </head>
    <body style="background-image: none; background-color: #fff;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_submit_model'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">

        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=submit_model_insert" enctype="multipart/form-data" method="post" id="submit_model" name="submit_model" style="padding:0; margin:0;">
            <input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" />
            <table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_make'] . '</b>&nbsp;</td>
                    <td><select id="make_id" name="make_id">
                                         <option value="">' . $txt['smfg_select_make1'] . '</option>
                                         <option value="">------</option>';
    // List Make Selections
    echo make_select();
    echo '</select>';
    echo '
                    </td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_submit_model'] . '</b>&nbsp;</td>
                    <td><input name="model" type="text" size="15" value="" /></td>
                </tr>
                <tr>
                    <td align="center" height="28" colspan="2"><input type="hidden" name="redirecturl" value="', $_SESSION['old_url'], '" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_submit_model'] . '" /></td>
                </tr>      
            </table>
            </form>
            <script language="JavaScript" type="text/javascript">
            var frmvalidator = new Validator("submit_model");
            frmvalidator.addValidation("make_id","req","' . $txt['smfg_val_select_make'] . '");
            frmvalidator.addValidation("make_id","dontselect=0","' . $txt['smfg_val_select_make'] . '");
            frmvalidator.addValidation("make_id","dontselect=1","' . $txt['smfg_val_select_make'] . '");
            frmvalidator.addValidation("model","req","' . $txt['smfg_val_enter_model'] . '");
            frmvalidator.addValidation("model","regexp=^[. A-Za-z0-9]{1,30}$","' . $txt['smfg_val_enter_valid_model'] . '");
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </body>
    </html>';

    exit;
}

// User Submit a Business
function G_Submit_Business()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Make sure this module is enabled
    if (!$smfgSettings['enable_user_submit_business']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Build link tree
    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_submit_business'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=submit_business',
        'name' => 'Submit Business'
    );

    // Get business type so that it's auto selected in the Type select
    $context['check_garage'] = "";
    $context['check_retail'] = "";
    $context['check_insurance'] = "";
    $context['check_dynocenter'] = "";
    $context['check_product'] = "";

    switch ($_GET['bustype']) {
        case "garage":
            $context['check_garage'] = " checked=\"checked\"";
            break;
        case "retail":
            $context['check_retail'] = " checked=\"checked\"";
            break;
        case "insurance":
            $context['check_insurance'] = " checked=\"checked\"";
            break;
        case "dynocenter":
            $context['check_dynocenter'] = " checked=\"checked\"";
            break;
        case "product":
            $context['check_product'] = " checked=\"checked\"";
            break;
    }

    echo '
    <html>
    <head>
    <title>' . $txt['smfg_submit_business'] . '</title>';

    echo '
    <link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?rc3" />
    <link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/print.css?rc3" media="print" />
    <script language="JavaScript" src="', $settings['default_theme_url'], '/gen_validatorv2.js" type="text/javascript"></script>';

    echo '
    </head>
    <body style="background-image: none; background-color: #fff;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_submit_business'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">

        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=submit_business_insert" enctype="multipart/form-data" method="post" id="submit_business" name="submit_business" style="padding:0; margin:0;">
            <input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" />
            <table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
                <tr>
                    <td align="right" valign="top">' . $txt['smfg_business_name'] . ':</td>
                        <td width="70%">
                            <input class="medium" type="text" id="title" name="title" value="" maxlength="60" />
                        </td>
                    </tr>
                    <tr>
                    <td align="right" valign="top">' . $txt['smfg_address'] . ':</td>
                        <td width="70%">
                            <textarea name="address" cols="40" rows="5"></textarea>
                        </td>
                    </tr>
                    <tr>
                    <td align="right" valign="top">' . $txt['smfg_telephone_no'] . ':</td>
                        <td width="70%">
                            <input class="medium" name="telephone" type="text" size="35" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td align="right" valign="top">' . $txt['smfg_fax'] . ':</td>
                        <td width="70%">
                            <input class="medium" name="fax" type="text" size="35" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td align="right" valign="top">' . $txt['smfg_website'] . ':</td>
                        <td width="70%">
                            <input class="medium" name="website" type="text" size="35" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td align="right" valign="top">' . $txt['smfg_email'] . ':</td>
                        <td width="70%">
                            <input class="medium" name="email" type="text" size="35" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td align="right" valign="top">' . $txt['smfg_opening_hours'] . ':</td>
                        <td width="70%">
                            <textarea name="opening_hours" cols="40" rows="3"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" valign="top">' . $txt['smfg_type'] . ':</td>
                        <td width="70%">
                            ' . $txt['smfg_garage'] . ':&nbsp;<input type="checkbox" name="type[0]" id="type" value="garage"' . $context['check_garage'] . ' /><br />
                            ' . $txt['smfg_shop'] . ':&nbsp;<input type="checkbox" name="type[1]" id="type" value="retail"' . $context['check_retail'] . ' /><br />
                            ' . $txt['smfg_insurance'] . ':&nbsp;<input type="checkbox" name="type[2]" id="type" value="insurance"' . $context['check_insurance'] . ' /><br />
                            ' . $txt['smfg_dynocenter'] . ':&nbsp;<input type="checkbox" name="type[3]" id="type" value="dynocenter"' . $context['check_dynocenter'] . ' /><br />
                            ' . $txt['smfg_manufacturer'] . ':&nbsp;<input type="checkbox" name="type[4]" id="type" value="product"' . $context['check_product'] . ' />
                        </td>
                    </tr>
                <tr>
                    <td align="center" height="28" colspan="2"><input type="hidden" name="redirecturl" value="', $_SESSION['old_url'], '" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_submit_business'] . '" /></td>
                </tr>      
            </table>
            </form>
            <script language="JavaScript" type="text/javascript"> 
            function DoCustomValidation()
                {
                  var frm = document.forms["submit_business"];
                  var myOption = 0;

                  for (var i = 0; i < frm.type.length; i++) {
                    if (frm.type[i].checked) {
                        myOption++;
                    }
                  }

                  if (myOption == 0) {
                    alert("' . $txt['smfg_val_select_business_type'] . '");
                    return false;
                  }
                }
            var frmvalidator = new Validator("submit_business");
            frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_business'] . '");
            frmvalidator.addValidation("email","email","' . $txt['smfg_val_enter_valid_email'] . '");
            frmvalidator.setAddnlValidationFunction("DoCustomValidation");
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </body>
    </html>';

    exit;
}

// User Submit a Product
function G_Submit_Product()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Make sure this module is enabled
    if (!$smfgSettings['enable_user_submit_product']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Build link tree
    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_submit_product'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=submit_product',
        'name' => 'Submit Product'
    );

    echo '
    <html>
    <head>
    <title>' . $txt['smfg_submit_product'] . '</title>';

    echo '
    <link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?rc3" />
    <link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/print.css?rc3" media="print" />
    <script language="JavaScript" src="', $settings['default_theme_url'], '/gen_validatorv2.js" type="text/javascript"></script>';

    echo '
    </head>
    <body style="background-image: none; background-color: #fff;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_submit_product'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">

        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=submit_product_insert" enctype="multipart/form-data" method="post" id="submit_product" name="submit_product" style="padding:0; margin:0;">
            <input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" />
            <table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_category'] . '</b>&nbsp;</td>
                    <td><select id="category_id" name="category_id">
                                         <option value="">' . $txt['smfg_select_category'] . '</option>
                                         <option value="">------</option>';
    // List Category Selections
    echo cat_select();
    echo '</select>';
    echo '
                    </td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_manufacturer'] . '</b>&nbsp;</td>
                    <td><select id="manufacturer_id" name="manufacturer_id">
                                         <option value="">' . $txt['smfg_select_manufacturer'] . '</option>
                                         <option value="">------</option>';
    // List Manufacturer Selections
    echo manufacturer_select();
    echo '</select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=product">' . $txt['smfg_here'] . '</a>';
    }
    echo '
                    </td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_product'] . '</b>&nbsp;</td>
                    <td><input name="product" type="text" size="15" value="" /></td>
                </tr>
                <tr>
                    <td align="center" height="28" colspan="2"><input type="hidden" name="redirecturl" value="', $_SESSION['old_url'], '" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_submit_product'] . '" /></td>
                </tr>      
            </table>
            </form>
            <script language="JavaScript" type="text/javascript">
            var frmvalidator = new Validator("submit_product");
        
            frmvalidator.addValidation("category_id","req","' . $txt['smfg_val_select_category'] . '");
            frmvalidator.addValidation("category_id","dontselect=0","' . $txt['smfg_val_select_category'] . '");
            frmvalidator.addValidation("category_id","dontselect=1","' . $txt['smfg_val_select_category'] . '");
            frmvalidator.addValidation("manufacturer_id","req","' . $txt['smfg_val_select_manufacturer'] . '");
            frmvalidator.addValidation("manufacturer_id","dontselect=0","' . $txt['smfg_val_select_manufacturer'] . '");
            frmvalidator.addValidation("manufacturer_id","dontselect=1","' . $txt['smfg_val_select_manufacturer'] . '");
            frmvalidator.addValidation("product","req","' . $txt['smfg_val_enter_product'] . '");
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </body>
    </html>';

    exit;
}

// User Submit a Track
function G_Submit_Track()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 1;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Make sure this module is enabled
    if (!$smfgSettings['enable_user_add_track']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Build link tree
    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_submit_track'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=submit_track',
        'name' => 'Submit Track'
    );

    echo '
    <html>
    <head>
    <title>' . $txt['smfg_submit_track'] . '</title>';

    echo '
    <link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?rc3" />
    <link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/print.css?rc3" media="print" />
    <script language="JavaScript" src="', $settings['default_theme_url'], '/gen_validatorv2.js" type="text/javascript"></script>';

    echo '
    </head>
    <body style="background-image: none; background-color: #fff;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_submit_track'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=submit_track_insert" enctype="multipart/form-data" method="post" id="submit_track" name="submit_track" style="padding:0; margin:0;">
            <input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" />
            <table width="100%" cellpadding="3" cellspacing="1" border="0" class="bordercolor">
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_track'] . '</b>&nbsp;</td>
                    <td><input name="track" type="text" size="15" value="" /></td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_length'] . '</b>&nbsp;</td>
                    <td><input class="medium" type="text" id="length" name="length" value="" maxlength="255" /></td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_mileage_unit'] . '</b>&nbsp;</td>
                    <td><select name="mileage_unit" id="mileage_unit">
                        <option value="">------</option>
                        <option value="Miles">' . $txt['smfg_miles'] . '</option>
                        <option value="Kilometers">' . $txt['smfg_kilometers'] . '</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="center" height="28" colspan="2"><input type="hidden" name="redirecturl" value="', $_SESSION['old_url'], '" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_submit_track'] . '" /></td>
                </tr>      
            </table>
            </form>
            <script language="JavaScript" type="text/javascript">
            var frmvalidator = new Validator("submit_track");
            frmvalidator.addValidation("track","req","' . $txt['smfg_val_enter_track'] . '");
            frmvalidator.addValidation("track","regexp=^[ A-Za-z0-9]{1,30}$","' . $txt['smfg_val_enter_valid_track'] . '");
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </body>
    </html>';

    exit;
}

// Insert submitted make
function G_Submit_Make_Insert()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Make sure this module is enabled
    if (!$smfgSettings['enable_user_submit_make']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Upper Case the make
    $_POST['make'] = ucwords($_POST['make']);

    // Check if the submitted make already exists
    $request = $smcFunc['db_query']('', '
        SELECT make
        FROM {db_prefix}garage_makes
        WHERE make = {string:make}',
        array(
            'make' => $_POST['make'],
        )
    );
    $matching_makes = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_makes > 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_submit_make_error', false);
    }

    // Check if submissions need approval
    if ($smfgSettings['enable_make_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    // Insert the make
    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_makes',
        array(
            'make' => 'string',
            'pending' => 'string',
        ),
        array(
            $_POST['make'],
            $pending,
        ),
        array(// no data
        )
    );

    // Set session variable to show 'success' message
    $_SESSION['added_make'] = 1;

    // Send out Notifications
    if ($smfgSettings['enable_make_approval']) {
        sendGarageNotifications();
    }

    // Send them back to where they were
//    $newurl = $_POST['redirecturl'];
//    header( 'Location: '.$newurl) ;
    echo '<script type="text/javascript">
    parent.location.reload();
    parent.Shadowbox.close();
    </script>';

}

// Insert submitted model
function G_Submit_Model_Insert()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Make sure this module is enabled
    if (!$smfgSettings['enable_user_submit_model']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Upper Case the model
    $_POST['model'] = ucwords($_POST['model']);

    // Check if the submitted model already exists
    $request = $smcFunc['db_query']('', '
        SELECT model
        FROM {db_prefix}garage_models
        WHERE make_id = {int:make_id}
            AND model = {string:model}',
        array(
            'make_id' => $_POST['make_id'],
            'model' => $_POST['model'],
        )
    );
    $matching_models = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_models > 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_submit_model_error', false);
    }

    // Check if submissions need approval
    if ($smfgSettings['enable_model_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    // Insert the model
    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_models',
        array(
            'make_id' => 'int',
            'model' => 'string',
            'pending' => 'string',
        ),
        array(
            $_POST['make_id'],
            $_POST['model'],
            $pending,
        ),
        array(// no data
        )
    );

    // Set session variable to show 'success' message
    $_SESSION['added_model'] = 1;

    // Send out Notifications
    if ($smfgSettings['enable_model_approval']) {
        sendGarageNotifications();
    }

//    $newurl = $_POST['redirecturl'];
//    header( 'Location: '.$newurl) ;
    echo '<script type="text/javascript">
    parent.location.reload();
    parent.Shadowbox.close();
    </script>';

}

// Insert submitted business
function G_Submit_Business_Insert()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Make sure this module is enabled
    if (!$smfgSettings['enable_user_submit_business']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Upper Case the business title
    $_POST['title'] = ucwords($_POST['title']);

    // Check if the submitted business already exist
    $request = $smcFunc['db_query']('', '
        SELECT title
        FROM {db_prefix}garage_business
        WHERE title = {string:title}',
        array(
            'title' => $_POST['title'],
        )
    );
    $matching_business = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    $typeval = ARRAY();
    foreach ($_POST['type'] as $type) {
        // Check if the submitted business already exist for that type
        $request = $smcFunc['db_query']('', '
            SELECT title
            FROM {db_prefix}garage_business
            WHERE title = {string:title}
                AND {raw:type} = 1',
            array(
                'title' => $_POST['title'],
                'type' => $type,
            )
        );
        $matching_type = $smcFunc['db_fetch_row']($request);
        $smcFunc['db_free_result']($request);

        if ($matching_type > 0) {
            ### This can't be fatal, but it would be nice to make a
            ### session var to show it's skipping this type cause it exists
            continue;
        }

        // Define these types
        $typeval['product'] = 0;
        $typeval['retail'] = 0;
        $typeval['garage'] = 0;
        $typeval['insurance'] = 0;
        $typeval['dynocenter'] = 0;

        // If it was a manufacturer, set the session variable for successful message
        switch ($type) {
            case "product":
                $_SESSION['added_man'] = 1;
                $typeval['product'] = 1;
                break;
            case "retail":
                $_SESSION['added_shop'] = 1;
                $typeval['retail'] = 1;
                break;
            case "garage":
                $_SESSION['added_garage'] = 1;
                $typeval['garage'] = 1;
                break;
            case "insurance":
                $_SESSION['added_insurance'] = 1;
                $typeval['insurance'] = 1;
                break;
            case "dynocenter":
                $_SESSION['added_dynocenter'] = 1;
                $typeval['dynocenter'] = 1;
                break;
        }
    }

    if ($matching_business > 0 && count($typeval) < 1) {
        loadLanguage('Errors');
        fatal_lang_error('garage_submit_business_error', false);
    }

    // Check for 'http://' or 'https://'
    if (!empty($_POST['website'])) {

        $http_pos = strpos($_POST['website'], 'http://');
        $https_pos = strpos($_POST['website'], 'https://');

        if ($http_pos === false && $https_pos === false) {
            // Append http:// to beginning of URL
            $http = array('http://', $_POST['website']);
            $_POST['website'] = join("", $http);
        }
    }

    // Check if submissions need approval
    if ($smfgSettings['enable_business_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    if ($matching_business > 0) {
        foreach ($typeval AS $type => $val) {
            $typestring .= "" . $type . " = " . $val . ", ";
        }
        // Update the business (Only add types and reset pending if needed)
        $request = $smcFunc['db_query']('', '
            UPDATE {db_prefix}garage_business
            SET {raw:typestring}pending = {string:pending}
            WHERE title = {string:title}',
            array(
                'typestring' => $typestring,
                'pending' => $pending,
                'title' => $_POST['title'],
            )
        );
    } else {
        // Insert the business
        $request = $smcFunc['db_insert']('insert',
            '{db_prefix}garage_business',
            array(
                'title' => 'string',
                'address' => 'string',
                'telephone' => 'string',
                'fax' => 'string',
                'website' => 'string',
                'email' => 'string',
                'opening_hours' => 'string',
                'product' => 'int',
                'retail' => 'int',
                'garage' => 'int',
                'insurance' => 'int',
                'dynocenter' => 'int',
                'pending' => 'string',
            ),
            array(
                $_POST['title'],
                $_POST['address'],
                $_POST['telephone'],
                $_POST['fax'],
                $_POST['website'],
                $_POST['email'],
                $_POST['opening_hours'],
                $typeval['product'],
                $typeval['retail'],
                $typeval['garage'],
                $typeval['insurance'],
                $typeval['dynocenter'],
                'pending' => $pending,
            ),
            array(// no data
            )
        );
    }

    // Send out Notifications
    if ($smfgSettings['enable_business_approval']) {
        sendGarageNotifications();
    }

//    $newurl = $_POST['redirecturl'];
//    header( 'Location: '.$newurl) ;
    echo '<script type="text/javascript">
    parent.location.reload();
    parent.Shadowbox.close();
    </script>';

}

// Insert submitted product
function G_Submit_Product_Insert()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Make sure this module is enabled
    if (!$smfgSettings['enable_user_submit_product']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Upper Case the product
    $_POST['product'] = ucwords($_POST['product']);

    // Check if the submitted product already exists
    $request = $smcFunc['db_query']('', '
        SELECT title
        FROM {db_prefix}garage_products
        WHERE business_id = {int:manufacturer_id}
            AND category_id = {int:category_id}
            AND title = {string:product}',
        array(
            'manufacturer_id' => $_POST['manufacturer_id'],
            'category_id' => $_POST['category_id'],
            'product' => $_POST['product'],
        )
    );
    $matching_products = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_products > 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_submit_product_error', false);
    }

    // Check if submissions need approval
    if ($smfgSettings['enable_product_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    // Insert the product
    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_products',
        array(
            'business_id' => 'int',
            'category_id' => 'int',
            'title' => 'string',
            'pending' => 'string',
        ),
        array(
            $_POST['manufacturer_id'],
            $_POST['category_id'],
            $_POST['product'],
            $pending,
        ),
        array(// no data
        )
    );

    // Set session variable to show 'success' message
    $_SESSION['added_product'] = 1;

    // Send out Notifications
    if ($smfgSettings['enable_product_approval']) {
        sendGarageNotifications();
    }

//    $newurl = $_POST['redirecturl'];
//    header( 'Location: '.$newurl) ;
    echo '<script type="text/javascript">
    parent.location.reload();
    parent.Shadowbox.close();
    </script>';

}

// Insert submitted track
function G_Submit_Track_Insert()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    $context['sub_template'] = 'blank';

    // Validate Session
    checkSession();

    // Make sure this module is enabled
    if (!$smfgSettings['enable_user_add_track']) {
        loadLanguage('Errors');
        fatal_lang_error('garage_disabled_module_error', false);
    }

    // Upper Case the track
    $_POST['track'] = ucwords($_POST['track']);

    // Check if the submitted track already exists
    $request = $smcFunc['db_query']('', '
        SELECT title
        FROM {db_prefix}garage_tracks
        WHERE title = {string:track}',
        array(
            'track' => $_POST['track'],
        )
    );
    $matching_tracks = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    if ($matching_tracks > 0) {
        loadLanguage('Errors');
        fatal_lang_error('garage_submit_track_error', false);
    }

    // Check if submissions need approval
    if ($smfgSettings['enable_track_approval']) {
        $pending = '1';
    } else {
        $pending = '0';
    }

    // Insert the make
    $request = $smcFunc['db_insert']('insert',
        '{db_prefix}garage_tracks',
        array(
            'title' => 'string',
            'length' => 'string',
            'mileage_unit' => 'string',
            'pending' => 'string',
        ),
        array(
            $_POST['track'],
            $_POST['length'],
            $_POST['mileage_unit'],
            $pending,
        ),
        array(// no data
        )
    );

    // Set session variable to show 'success' message
    $_SESSION['added_track'] = 1;

    // Send out Notifications
    if ($smfgSettings['enable_track_approval']) {
        sendGarageNotifications();
    }

//    $newurl = $_POST['redirecturl'];
//    header( 'Location: '.$newurl) ;
    echo '<script type="text/javascript">
    parent.location.reload();
    parent.Shadowbox.close();
    </script>';

}

// What is this thing for?  :D
function G_Garage_Card()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func, $boarddir;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    // Check Permissions
    //isAllowedTo('view_garage');

    $context['sub_template'] = 'blank';

    // Lets check if $_GET is empty before doing anything...
    if (isset($_GET['user']) && !empty($_GET['user'])) {
        // Get main veh.id from membername
        $user = $_GET['user'];
        $request = $smcFunc['db_query']('', '
            SELECT v.id
            FROM {db_prefix}garage_vehicles AS v, {db_prefix}members AS u
            WHERE u.member_name = {string:user}
                AND u.id_member = v.user_Id
                AND v.main_vehicle',
            array(
                'user' => $user,
            )
        );
        // check to see if the username submitted is valid
        $num_results = $smcFunc['db_num_rows']($request);
        if ($num_results == 0) {
            $img_bg = imageCreateFromPNG($boarddir . "/Themes/default/images/Garage_Card_01.png");
            $textcolor = ImageColorAllocate($img_bg, 000, 000, 000);
            ImageString($img_bg, 10, 120, 40, $txt['smfg_submit_valid_username'], $textcolor);
            Header("Content-type: image/png");
            Imagepng($img_bg);
            ImageDestroy($img_bg);
            exit;
        } // end check to see if username is vaild
        while ($row = $smcFunc['db_fetch_assoc']($request)) {
            $VID = $row['id'];
        }
        // Free resultset
        $smcFunc['db_free_result']($request);
    } else {
        $img_bg = imageCreateFromPNG($boarddir . "/Themes/default/images/Garage_Card_01.png");
        $textcolor = ImageColorAllocate($img_bg, 000, 000, 000);
        ImageString($img_bg, 10, 120, 40, $txt['smfg_submit_valid_username'], $textcolor);
        Header("Content-type: image/png");
        Imagepng($img_bg);
        ImageDestroy($img_bg);
        exit;
    }//end check if $_GET is empty

    // *************************************************************
    // WARNING: The query check is being disabled to allow for the following subselect.
    // It is imperative this is turned back on for security reasons.
    // *************************************************************
    $modSettings['disableQueryCheck'] = 1;
    // *************************************************************

    // Which rating type?
    if ($smfgSettings['rating_system'] == 0) {
        $ratingfunc = "SUM";
    } else {
        if ($smfgSettings['rating_system'] == 1) {
            $ratingfunc = "AVG";
        }
    }

    //get info for image
    $request = $smcFunc['db_query']('', '
        SELECT u.member_name, CONCAT_WS(" ", v.made_year, mk.make, md.model) as Vehicle, IFNULL(m.total_mods,0) + IFNULL(s.total_service,0) + IFNULL(v.price,0) AS total_spent, c.title AS currency, IFNULL(m1.total_num_mods, 0) AS total_num_mods, IFNULL(r.rating,0) AS rating, IFNULL(r.poss_rating,0) AS poss_rating, g.attach_location AS thumb, g.is_remote, g.attach_file
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
                            GROUP BY vehicle_id) AS s ON v.id = s.vehicle_id
        LEFT OUTER JOIN (
                            SELECT vehicle_id, COUNT(id) AS total_num_mods
                            FROM {db_prefix}garage_modifications AS m
                            GROUP BY vehicle_id) AS m1 ON v.id = m1.vehicle_id
        LEFT OUTER JOIN (
                            SELECT vehicle_id, {raw:ratingfunc}( rating ) AS rating, COUNT( id ) AS poss_rating
                            FROM {db_prefix}garage_ratings
                            GROUP BY vehicle_id) AS r ON v.id = r.vehicle_id
        LEFT OUTER JOIN (
                            SELECT vg.vehicle_id, i.attach_location, i.is_remote, i.attach_file
                            FROM {db_prefix}garage_vehicles_gallery AS vg, {db_prefix}garage_images AS i
                            WHERE vg.vehicle_id = i.vehicle_id
                                AND vg.image_id = i.attach_id
                                AND vg.hilite = 1) AS g ON v.id = g.vehicle_id, {db_prefix}garage_makes AS mk, {db_prefix}garage_models AS md, {db_prefix}members AS u, {db_prefix}garage_currency AS c
        WHERE v.id = {int:vid}
            AND v.user_id = u.id_member
            AND v.make_id = mk.id
            AND v.model_id = md.id
            AND v.currency = c.id
        GROUP BY v.id, u.member_name, CONCAT_WS(" ", v.made_year, mk.make, md.model), IFNULL(m.total_mods,0) + IFNULL(s.total_service,0) + IFNULL(v.price,0), c.title, IFNULL(m1.total_num_mods, 0), IFNULL(r.rating,0), IFNULL(r.poss_rating,0), g.attach_location, g.is_remote, g.attach_file',
        array(
            'ratingfunc' => $ratingfunc,
            'vid' => $VID,
        )
    );

    // *************************************************************
    // WARNING: The query check is being enabled, this MUST BE DONE!
    // *************************************************************
    $modSettings['disableQueryCheck'] = 0;
    // *************************************************************

    while ($g_card = $smcFunc['db_fetch_assoc']($request)) {
        $g_card['Vehicle'];
        $user_name = $g_card['member_name'];
        $vehicle_tmp = $g_card['Vehicle'];
        $maxlinelen = 22;
        if (strlen($vehicle_tmp) > $maxlinelen) {
            $vehicle_array = explode(' ', $vehicle_tmp);
            $vehicle = ARRAY();
            foreach ($vehicle_array AS $vehicle_word) {
                $linelen = 0;
                $lineno = 0;
                $vehicle[$lineno] = "";
                // Make sure the word fits at least or just give up now
                if (strlen($vehicle_word) < $maxlinelen) {
                    $linelen = $linelen + strlen($vehicle_word);
                    if ($linelen > $maxlinelen) {
                        $lineno++;
                        $linelen = strlen($vehicle_word);
                    }
                    $vehicle[$lineno] .= $vehicle_word . " ";
                }
            }
        } else {
            $vehicle = ARRAY();
            $vehicle[0] = $g_card['Vehicle'];
        }
        $total_spent = $g_card['total_spent'];
        $currency = $g_card['currency'];
        $total_num_mods = $g_card['total_num_mods'];
        $rating = $g_card['rating'];
        $poss_rating = $g_card['poss_rating'];
        $thumb = $g_card['thumb'];
        $is_remote = $g_card['is_remote'];
        $attach_file = $g_card['attach_file'];
    }
    // Free resultset
    $smcFunc['db_free_result']($request);

    // Let's do a little ratings work
    if ($smfgSettings['rating_system'] == 0) {
        $poss_rating = $poss_rating * 10;
    } else {
        if ($smfgSettings['rating_system'] == 1) {
            if ($poss_rating != 0) {
                $poss_rating = 10;
            }
        }
    }
    $rating = number_format($rating, 2, '.', ',');
    $poss_rating = number_format($poss_rating, 2, '.', ',');

    // No image uploaded? No problem, lets give them a filler!
    if (isset($thumb) && !empty($thumb)) {
        if ($is_remote) {
            $target = $boarddir . '/' . $smfgSettings['upload_directory'] . 'cache/' . $attach_file;
            // Check if it the image is still there
            if (url_validate($thumb)) {
                getRemoteImage($thumb, $target);
            } else {
                $veh_image_thumb = $boarddir . "/Themes/default/images/garage_no_vehicle_thumb.png";
            }
            $veh_image_thumb = $target;
        } else {
            $veh_image_thumb = $boarddir . "/" . $smfgSettings['upload_directory'] . $thumb;
        }
    } else {
        $veh_image_thumb = $boarddir . "/Themes/default/images/garage_no_vehicle_thumb.png";
    }

    // Image type?
    $image_type = get_image_type($veh_image_thumb);
    if ($image_type == "gif") {
        $overlay_image_source = imagecreatefromgif($veh_image_thumb);
    } else {
        if ($image_type == "jpg") {
            $overlay_image_source = imagecreatefromjpeg($veh_image_thumb);
        } else {
            if ($image_type == "png") {
                $overlay_image_source = imagecreatefrompng($veh_image_thumb);
            }
        }
    }

    list($thumb_width, $thumb_height) = getimagesize($veh_image_thumb);

    if (!imageistruecolor($overlay_image_source)) {
        $original_transparency = imagecolortransparent($overlay_image_source);
        // we have a transparent color
        if ($original_transparency >= 0) {
            // get the actual transparent color
            $rgb = imagecolorsforindex($overlay_image_source, $original_transparency);
            $original_transparency = ($rgb['red'] << 16) | ($rgb['green'] << 8) | $rgb['blue'];
            // change the transparent color to black, since transparent goes to black anyways (no way to remove transparency in GIF)
            imagecolortransparent($overlay_image_source, imagecolorallocate($overlay_image_source, 0, 0, 0));
        }
        // create truecolor image and transfer
        $truecolor = imagecreatetruecolor($thumb_width, $thumb_height);
        imagealphablending($overlay_image_source, false);
        imagesavealpha($overlay_image_source, true);
        imagecopy($truecolor, $overlay_image_source, 0, 0, 0, 0, $w, $h);
        imagedestroy($overlay_image_source);
        $overlay_image_source = $truecolor;
        // remake transparency (if there was transparency)
        if ($original_transparency >= 0) {
            imagealphablending($overlay_image_source, false);
            imagesavealpha($overlay_image_source, true);
            for ($x = 0; $x < $w; $x++) {
                for ($y = 0; $y < $h; $y++) {
                    if (imagecolorat($overlay_image_source, $x, $y) == $original_transparency) {
                        imagesetpixel($overlay_image_source, $x, $y, 127 << 24);
                    }
                }
            }
        }
    }

    // What style does the user want? If nothing is submited give them 1
    $style = $_GET['style'];
    if (isset($style) && $style == "1") {
        $img_bg = imageCreateFromPNG($boarddir . "/Themes/default/images/Garage_Card_01.png");
        $new_width = "213";
        $new_height = "133";
        $thumb_x_pos = 20;
        $thumb_y_pos = 8;
        $textcolorr = "000";
        $textcolorg = "000";
        $textcolorb = "000";
        $bgtextcolorr = "trans";
        $bgtextcolorg = "255";
        $bgtextcolorb = "255";
    } else {
        if (isset($style) && $style == "2") { //end style 1 start style 2
            $img_bg = imageCreateFromPNG($boarddir . "/Themes/default/images/Garage_Card_02.png");
            $new_width = "212";
            $new_height = "134";
            $thumb_x_pos = 7;
            $thumb_y_pos = 7;
            $textcolorr = "255";
            $textcolorg = "255";
            $textcolorb = "255";
            $bgtextcolorr = "255";
            $bgtextcolorg = "255";
            $bgtextcolorb = "255";
        } else {
            if (isset($style) && $style == "3") { //end style 2 start style 3
                $img_bg = imageCreateFromPNG($boarddir . "/Themes/default/images/Garage_Card_03.png");
                $new_width = "213";
                $new_height = "133";
                $thumb_x_pos = 9;
                $thumb_y_pos = 9;
                $textcolorr = "000";
                $textcolorg = "000";
                $textcolorb = "000";
                $bgtextcolorr = "trans";
                $bgtextcolorg = "255";
                $bgtextcolorb = "255";
            } else {
                if (isset($style) && $style == "4") { //end style 3 start style 4
                    $img_bg = imageCreateFromPNG($boarddir . "/Themes/default/images/Garage_Card_04.png");
                    $new_width = "213";
                    $new_height = "133";
                    $thumb_x_pos = 9;
                    $thumb_y_pos = 9;
                    $textcolorr = "000";
                    $textcolorg = "000";
                    $textcolorb = "000";
                    $bgtextcolorr = "trans";
                    $bgtextcolorg = "255";
                    $bgtextcolorb = "255";
                } else { //no style set? Lets give them style1
                    $img_bg = imageCreateFromPNG($boarddir . "/Themes/default/images/Garage_Card_01.png");
                    $new_width = "213";
                    $new_height = "133";
                    $thumb_x_pos = 20;
                    $thumb_y_pos = 8;
                    $textcolorr = "000";
                    $textcolorg = "000";
                    $textcolorb = "000";
                    $bgtextcolorr = "trans";
                    $bgtextcolorg = "255";
                    $bgtextcolorb = "255";
                }
            }
        }
    }

    // Get thumbnail W and H
    // Force the overlay image to be a set width, height (so it doesn't overflow the background)
    $overlay_image = imagecreatetruecolor($new_width, $new_height);
    if ($bgtextcolorr == "trans") {
        $trnprt_indx = "";
        // Get the original image's transparent color's RGB values
        $trnprt_color = imagecolorsforindex($overlay_image, $trnprt_indx);

        // Allocate the same color in the new image resource
        $trnprt_indx = imagecolorallocate($img_bg, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

        $bgcolor = imagecolortransparent($overlay_image, $trnprt_indx);
    } else {
        $bgcolor = ImageColorAllocate($overlay_image, $bgtextcolorr, $bgtextcolorg, $bgtextcolorb);
    }
    imagefill($overlay_image, 0, 0, $bgcolor);

    // Resize propotionately centered on a bg
    $orig_ratio = $thumb_width / $thumb_height;
    $new_ratio = $new_width / $new_height;
    if ($orig_ratio < $new_ratio) {
        $new_thumb_width = $new_height * $orig_ratio;
        $new_thumb_height = $new_height;
        $centerx = ABS($new_width - $new_thumb_width) / 2;
        $centery = 0;
        //die("Thumb: $thumb_width x $thumb_height (".$thumb_width / $thumb_height.")<br />New: $new_width x $new_height (".$new_width / $new_height.")<br />New Thumb: $new_thumb_width x $new_thumb_height (".$new_thumb_width / $new_thumb_height.")<br />");
    } else {
        if ($orig_ratio > $new_ratio) {
            $new_thumb_height = $new_width / $orig_ratio;
            $new_thumb_width = $new_width;
            $centerx = 0;
            $centery = ABS($new_height - $new_thumb_height) / 2;
            //die("Thumb: $thumb_width x $thumb_height (".$thumb_width / $thumb_height.")<br />New: $new_width x $new_height (".$new_width / $new_height.")<br />New Thumb: $new_thumb_width x $new_thumb_height (".$new_thumb_width / $new_thumb_height.")<br />");
        } else {
            $new_thumb_width = $new_width;
            $new_thumb_height = $new_height;
            $centerx = 0;
            $centery = 0;
        }
    }

    $thumb_image = imagecreatetruecolor($new_thumb_width, $new_thumb_height);
    imagecopyresized($thumb_image, $overlay_image_source, 0, 0, 0, 0, $new_thumb_width, $new_thumb_height, $thumb_width,
        $thumb_height);
    imagecopy($overlay_image, $thumb_image, $centerx, $centery, 0, 0, $new_thumb_width, $new_thumb_height);

    // Build card
    imagecopy($img_bg, $overlay_image, $thumb_x_pos, $thumb_y_pos, 0, 0, $new_width, $new_height);
    $textcolor = ImageColorAllocate($overlay_image, $textcolorr, $textcolorg, $textcolorb);
    $lineheight = 12;
    $liney = 9;
    ImageString($img_bg, 10, 300, $liney, $user_name . "'s", $textcolor);
    $liney = $liney + $lineheight;
    foreach ($vehicle AS $vehicleline) {
        ImageString($img_bg, 10, 270, $liney, $vehicleline, $textcolor);
        $liney = $liney + $lineheight;
    }
    ImageString($img_bg, 10, 270, $liney, $txt['smfg_total_mods'] . ": " . $total_num_mods, $textcolor);
    $liney = $liney + $lineheight;
    ImageString($img_bg, 10, 270, $liney, $txt['smfg_total_spent'] . ": " . $total_spent . " " . $currency, $textcolor);
    $liney = $liney + $lineheight;
    ImageString($img_bg, 10, 272, $liney, $txt['smfg_rating'] . ": " . $rating . "/" . $poss_rating, $textcolor);
    // Do the watermark thing
    if ($smfgSettings['gcard_watermark']) {
        $watermark = $boarddir . '/' . $smfgSettings['watermark_source'];
        list($wm_width, $wm_height) = getimagesize($watermark);
        $x_coor = (500 - $wm_width) - 3;
        $y_coor = (150 - $wm_height) - 3;
        // WM image type?
        $wm_image_type = get_image_type($watermark);
        if ($wm_image_type == "gif") {
            $watermark_source = imagecreatefromgif($watermark);
        } else {
            if ($wm_image_type == "jpg") {
                $watermark_source = imagecreatefromjpeg($watermark);
            } else {
                if ($wm_image_type == "png") {
                    $watermark_source = imagecreatefrompng($watermark);
                }
            }
        }
        // Since style 1 just had to be different, lets move it a tad over
        if (isset($style) && $style == "1") {
            imagecopy($img_bg, $watermark_source, ($x_coor) - 6, ($y_coor) - 6, 0, 0, $wm_width, $wm_height);
        } else {
            imagecopy($img_bg, $watermark_source, $x_coor, $y_coor, 0, 0, $wm_width, $wm_height);
        }
    }
    Header("Content-type: image/png");
    Imagepng($img_bg);
    ImageDestroy($img_bg);
    if ($is_remote) {
        unlink($target);
    }
    exit;

}

// Wha...again?  :D
function G_Get_Garage_Card()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    // Check Permissions
    isAllowedTo('view_garage');

    $context['sub_template'] = 'blank';

    $request = $smcFunc['db_query']('', '
        SELECT member_name
        FROM {db_prefix}members
        WHERE id_member = {int:user}',
        array(
            'user' => $_REQUEST['u'],
        )
    );
    $row = $smcFunc['db_fetch_assoc']($request);
    $username = $row['member_name'];
    $smcFunc['db_free_result']($request);

    $request = $smcFunc['db_query']('', '
        SELECT id
        FROM {db_prefix}garage_vehicles
        WHERE user_id = {int:user}
        AND main_vehicle = 1',
        array(
            'user' => $_REQUEST['u'],
        )
    );
    $row = $smcFunc['db_fetch_assoc']($request);
    $VID = $row['id'];
    $smcFunc['db_free_result']($request);

    echo '
    <html>
    <head>
    <title>' . $txt['smfg_g_sig_cards'] . '</title>';

    echo '
    <link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?rc3" />
    <link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/print.css?rc3" media="print" />';

    echo '
    <style type="text/css">
    * { font-size: 1em}
    </style>';

    echo '
    </head>
    <body style="background-image: none; background-color: #fff;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_g_sig_cards'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table border="0" cellspacing="1" cellpadding="4" align="center" class="bordercolor" style="width: 505px;">
        <tr>
            <td align="center" valign="middle">
            <img src="' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=1" height="150" width="500"/>
            <br />
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_1_bb" id="style_1_bb" type="text" size="58" readonly="readonly" value="[img]' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=1[/img]" onClick="this.select();" /></div>' . $txt['smfg_bbcode'] . ':</div>
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_1_bblink" id="style_1_bblink" type="text" size="58" readonly="readonly" value="[url=' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $VID . '][img]' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=1[/img][/url]" onClick="this.select();" /></div>' . $txt['smfg_bbcode_link'] . ':</div>
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_1_htmllink" id="style_1_htmllink" type="text" size="58" readonly="readonly" value="<a href=&quot;' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $VID . '&quot;><img src=&quot;' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=1&quot; /></a>" onClick="this.select();" /></div>' . $txt['smfg_html_link'] . ':</div>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle">
            <img src="' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=2" height="150" width="500"/>
            <br />
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_2_bb" id="style_2_bb" type="text" size="58" readonly="readonly" value="[img]' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=2[/img]" onClick="this.select();" /></div>' . $txt['smfg_bbcode'] . ':</div>
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_2_bblink" id="style_2_bblink" type="text" size="58" readonly="readonly" value="[url=' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $VID . '][img]' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=2[/img][/url]" onClick="this.select();" /></div>' . $txt['smfg_bbcode_link'] . ':</div>
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_2_htmllink" id="style_2_htmllink" type="text" size="58" readonly="readonly" value="<a href=&quot;' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $VID . '&quot;><img src=&quot;' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=2&quot; /></a>" onClick="this.select();" /></div>' . $txt['smfg_html_link'] . ':</div>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle">
            <img src="' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=3" height="150" width="500"/>
            <br />
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_3_bb" id="style_3_bb" type="text" size="58" readonly="readonly" value="[img]' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=3[/img]" onClick="this.select();" /></div>' . $txt['smfg_bbcode'] . ':</div>
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_3_bblink" id="style_3_bblink" type="text" size="58" readonly="readonly" value="[url=' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $VID . '][img]' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=3[/img][/url]" onClick="this.select();" /></div>' . $txt['smfg_bbcode_link'] . ':</div>
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_3_htmllink" id="style_3_htmllink" type="text" size="58" readonly="readonly" value="<a href=&quot;' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $VID . '&quot;><img src=&quot;' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=3&quot; /></a>" onClick="this.select();" /></div>' . $txt['smfg_html_link'] . ':</div>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle">
            <img src="' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=4" height="150" width="500"/>
            <br />
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_4_bb" id="style_4_bb" type="text" size="58" readonly="readonly" value="[img]' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=1[/img]" onClick="this.select();" /></div>' . $txt['smfg_bbcode'] . ':</div>
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_4_bblink" id="style_4_bblink" type="text" size="58" readonly="readonly" value="[url=' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $VID . '][img]' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=4[/img][/url]" onClick="this.select();" /></div>' . $txt['smfg_bbcode_link'] . ':</div>
            <div style="padding:2px; margin-top: 3px; text-align: left; width: 100%;"><div style="float:right"><input name="style_4_htmllink" id="style_4_htmllink" type="text" size="58" readonly="readonly" value="<a href=&quot;' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $VID . '&quot;><img src=&quot;' . $scripturl . '?action=garage;sa=gcard;user=' . $username . ';style=4&quot; /></a>" onClick="this.select();" /></div>' . $txt['smfg_html_link'] . ':</div>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </body>
    </html>';

    exit;

}

// Load Video  :D
function G_Video()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    // Check Permissions
    isAllowedTo('view_garage');

    $context['sub_template'] = 'blank';

    $request = $smcFunc['db_query']('', '
        SELECT url, title, video_desc
        FROM {db_prefix}garage_video
        WHERE id = {int:id}
        LIMIT 1',
        array(
            'id' => $_GET['id'],
        )
    );
    list($video_url, $video_title, $video_desc) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    $context['page_title'] = garage_title_clean($video_title);

    echo '
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
    <title>', $context['page_title'], '</title>
    </head>
    <body>';

    /*echo '
    <link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css?fin11" />
    <link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/print.css?fin11" media="print" />';*/

    /*echo '
    <table border="0" cellspacing="1" cellpadding="4" align="center" class="bordercolor">
        <tr>
            <td class="', empty($settings['use_tabs']) ? 'catbg' : 'titlebg', '" align="center" nowrap="nowrap">'.$video_title.'</td>
        </tr>
        <tr>
            <td class="windowbg" align="center" width="'.displayVideo($video_url, 'width').'" height="'.displayVideo($video_url, 'height').'" valign="middle">'.displayVideo($video_url, 1).'
            </td>
        </tr>
    </table>';*/
    //echo '<div style="text:align: center;">'.displayVideo($video_url, 1).'</div>';
    echo displayVideo($video_url, 1);

    echo '
    </body>
    </html>';
    exit;

}

// Supported Video Sites
function G_Supported_Video()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    // Check Permissions
    isAllowedTo('view_garage');

    $context['sub_template'] = 'blank';

    echo '
    <html>
    <head>
    <title>' . $txt['smfg_submit_make'] . '</title>';

    echo '
    <link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?rc3" />
    <link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/print.css?rc3" media="print" />
    <script language="JavaScript" src="', $settings['default_theme_url'], '/gen_validatorv2.js" type="text/javascript"></script>';

    echo '
    </head>
    <body style="background-image: none; background-color: #fff;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_supported_videos'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table border="0" cellspacing="1" cellpadding="4" align="center" width="100%">
        <tr>
            <td align="center" valign="middle"><a href="http://www.123video.nl/" target="new">123Video.nl</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.aniboom.com/" target="new">Aniboom</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://uncutvideo.aol.com/" target="new">AOL Uncut</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.atomfilms.com/" target="new">AtomFilms Uploads</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.biku.com/" target="new">Biku</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.brightcove.com/" target="new">BrightCove</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.cellfish.com/" target="new">CellFish</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.clipfish.de/" target="new">ClipFish.de</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.collegehumor.com/" target="new">CollegeHumor</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.dailymotion.com/" target="new">DailyMotion</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://dave.tv/" target="new">Dave.tv</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://dv.ouou.com" target="new">dv.ouou</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://sports.espn.go.com/broadband/video/" target="new">ESPN</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.gametrailers.com/" target="new">GameTrailers</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.gamevideos.com/" target="new">GameVideos</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.glumbert.com/" target="new">Glumbert</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.godtube.com/" target="new">GodTube</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://video.google.com/" target="new">Google Video</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.guba.com/" target="new">Guba</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.hulu.com/" target="new">Hulu</a> (US Only)
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.koreus.com/videos.php" target="new">Koreus</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://arianna.libero.it/hpvideo.html" target="new">Libero.it</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.liveleak.com/" target="new">LiveLeak</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.livevideo.com/" target="new">LiveVideo</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.megavideo.com/" target="new">Megavideo</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.metacafe.com/" target="new">MetaCafe</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://video.msn.com/" target="new">MSN Live/Soapbox Video</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://tv.mofile.com/" target="new">Mofile</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.mthai.com/" target="new">MThai</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://vids.myspace.com/" target="new">MySpaceTV</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.myvideo.de/" target="new">MyVideo.de</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.onsmash.com/" target="new">OnSmash</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.revver.com/" target="new">Revver</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://en.sevenload.com/" target="new">Sevenload</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.stage6.com/" target="new">Stage6</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.streetfire.net/" target="new">Streetfire.net</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.tudou.com/" target="new">Tudou</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.veoh.com/" target="new">Veoh</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.videotube.de/" target="new">Videotube.de</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.vidiac.com/" target="new">Vidiac</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.vidmax.com/" target="new">VidMax</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.vimeo.com/" target="new">Vimeo</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.vsocial.com/" target="new">VSocial</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://tv.yahoo.com/" target="new">Yahoo</a>/<a href="http://hk.yahoo.com/">Yahoo HK</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.youku.com" target="new">Youku</a>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle"><a href="http://www.youtube.com" target="new">YouTube/YouTube Playlists</a>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </body>
    </html>';

    exit;

}

// Copyright Information
function G_Copyright()
{
    global $txt, $scripturl, $smcFunc, $user_info, $settings, $boardurl;
    global $modSettings, $smfgSettings, $context, $func;

    // Set our index includes
    $context['lightbox'] = 0;
    $context['form_validation'] = 0;
    $context['dynamicoptionlist'] = 0;

    // Check Permissions
    isAllowedTo('view_garage');

    $context['sub_template'] = 'copyright';

    $context['page_title'] = $txt['smfg_garage'] . ' &gt; ' . $txt['smfg_copyright'];
    $context['linktree'][] = array(
        'url' => $scripturl . '?action=garage;sa=copyright',
        'name' => $txt['smfg_copyright']
    );

}
