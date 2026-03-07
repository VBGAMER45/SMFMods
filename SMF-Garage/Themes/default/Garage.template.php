<?php
/**********************************************************************************
 * Garage.template.php                                                             *
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

function template_main()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;

    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    // Show a link to collapse it.
    echo '
                        <a href="#" class="collapse" onclick="shrinkSection(\'mainGarage\', \'mainGarageUpshrink\'); return false;"><img id="mainGarageUpshrink" src="' . $settings['actual_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>';

    echo '
                        ', $txt['smfg_welcome'], '
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '
    <div id="mainGarage">';

    echo '
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table>
        <tr>';

    if ($smfgSettings['enable_featured_vehicle'] != 0) {
        echo '
        <td width="33%" valign="top">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" class="tborder">
            <tr>
                <td>';

        echo '                
                <div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ', $smfgSettings['featured_vehicle_description'], '
                </span></h4></div>';

        echo '
                </td>
            </tr>
            
            <tr>
                <td>
                <table border="0" cellpadding="0" cellspacing="3" width="100%">';
        if (isset($context['featured_vehicle']['id']) && !empty($context['featured_vehicle']['id'])) {
            echo '
                    <tr>
                        <td width="100%" valign="top" align="center">
                        ' . $context['featured_vehicle']['image'] . '<a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['featured_vehicle']['id'] . '">' . garage_title_clean($context['featured_vehicle']['vehicle']) . '</a><br />' . $txt['smfg_owner'] . ':&nbsp;<a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['featured_vehicle']['user_id'] . '">' . $context['featured_vehicle']['owner'] . '</a></td>
                    </tr>';
        } else {
            echo '
                    <tr>
                        <td width="100%" valign="top" align="center">' . $txt['smfg_no_vid'] . '</td>
                    </tr>';
        }
        echo '
                </table>
                </td>
            </tr>
        </table>
        </td>';
    }

    echo '
        <td>
        <div style="padding-left: 20px;">
            ' . $txt['smfg_garage_brief'] . '
            <br /><br />
            ' . $txt['smfg_total_vehicles_caps'] . ':&nbsp;<b>' . $context['total_vehicles'] . '</b>
            <br />
            ' . $txt['smfg_total_mods'] . ':&nbsp;<b>' . $context['total_mods'] . '</b>
            <br />
            ' . $txt['smfg_total_comments'] . ':&nbsp;<b>' . $context['total_comments'] . '</b>
            <br />
            ' . $txt['smfg_total_views'] . ':&nbsp;<b>' . $context['total_views'] . '</b>
            </div>
        </td>
    </tr>
    </table>
        
    </div></div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </div>';

    echo '
    <br />';

    echo '
<table border="0" cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td width="49%" valign="top">';

    echo $context['blocks']['display'];

    echo '            </td>
        </tr>
</table>
';

    echo smfg_footer();

}

function template_user_garage()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;

    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo '
                            <a href="#" class="collapse" onclick="shrinkSection(\'vehicleManagement\', \'vehicleManagementUpshrink\'); return false;"><img id="vehicleManagementUpshrink" src="' . $settings['default_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>';

    echo $txt['smfg_vehicle_management'];

    echo '
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '
    <div id="vehicleManagement">';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <div class="content">
        <ul id="quick_tasks" class="flow_hidden" style="padding: 0;">';
    echo '
            <li style="width: 100%; height: 40px;">
                <a href="' . $scripturl . '?action=garage;sa=add_vehicle"><img src="' . $settings['default_images_url'] . '/garage_add_vehicle.png" alt="" class="home_image png_fix" /></a>
                <h5><a href="' . $scripturl . '?action=garage;sa=add_vehicle">' . $txt['smfg_create_vehicle'] . '</a></h5>
                <span class="task">' . sprintf($txt['smfg_add_vehicle_desc'], $smfgSettings['default_vehicle_quota']) . '</span>
            </li>
        </ul>
    </div>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </div>';

    echo '
    <br />';

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $txt['smfg_my_vehicles'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
<form action="' . $scripturl . '?action=garage;sa=browse" method="post" style="padding:0; margin:0;">
<table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';
    if ($context['pending_modules']) {
        echo '
    <tr>
        <td align="left" valign="middle"><b>' . $txt['smfg_vehicle_pending_alert'] . '</b></td>
    </tr>';
    }
    echo '
<tr>';
    echo '
<td>
    <table border="0" cellpadding="3" cellspacing="1" width="100%">';

    // Check if there are vehicles in user's garage.
    if (!empty($context['user_vehicles'])) {
        echo '
    <tr>
        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
        ' . $txt['smfg_vehicle'] . '
        </span></h4></div></td> 
        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
        ' . $txt['smfg_mods'] . '
        </span></h4></div></td> 
        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
        ' . $txt['smfg_views'] . '
        </span></h4></div></td> 
        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
        ' . $txt['smfg_created'] . '
        </span></h4></div></td> 
        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
        ' . $txt['smfg_updated'] . '
        </span></h4></div></td> 
    </tr>';

        // Loop through each vehicle.
        foreach ($context['user_vehicles'] as $vehicle) {
            echo ' 
    <tr class="tableRow', ($vehicle['pending']) ? ' windowbg_pending' : '', '">
        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $vehicle['veh_id'] . '">' . garage_title_clean($vehicle['vehicle']) . '</a></td>
        <td align="center">' . $vehicle['total_mods'] . '</td>
        <td align="center">' . $vehicle['views'] . '</td>
        <td align="center">' . date($context['date_format'], $vehicle['date_created']) . '</td>
        <td align="center">' . date($context['date_format'], $vehicle['date_updated']) . '</td>
    </tr>';

        }

    } // No Vehicles?
    else {

        echo '
    <tr>
    <td align="center">' . $txt['smfg_no_vehicles_in_ug'] . '</td>
    </tr>';

    }

    echo '
    </table>
</td>
</tr>
</table>
</form>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_view_garage()
{
    global $context, $settings, $options, $txt, $scripturl, $db_prefix, $smfgSettings, $boardurl, $user_profile;

    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $context['user_vehicles']['memberName'] . '\'s ' . $txt['smfg_garage'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    //show the vehicle(s) in this garage
    $count = 0;
    while (isset($context['user_vehicles'][$count]['veh_id'])) {

        echo '
            <table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
            <tr>
            <td width="33%" valign="middle">
            <table width="100%" border="0" cellspacing="0" cellpadding="4" align="center" class="tborder">
                <tr>   
                    <td align="center" nowrap="nowrap">
                    <div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    <a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles'][$count]['veh_id'] . '">' . garage_title_clean($context['user_vehicles'][$count]['vehicle']) . '</a>
                    </span></h4></div>
                    </td>
                </tr>
                <tr>
                    <td>
                    <table border="0" cellpadding="0" cellspacing="3" width="100%">
                        <tr>
                            <td width="100%" valign="top" align="center">' . $context['user_vehicles'][$count]['image'] . '</td>
                        </tr>
                    </table>
                    </td>
                </tr>
            </table>
            </td>
            <td width="77%" valign="middle">
            <table width="350" border="0" cellspacing="1" cellpadding="3" style="margin-left: 25px;">
            <tbody>
                <tr>
                    <td><b>' . $txt['smfg_vehicle'] . ':</b></td>
                    <td><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles'][$count]['veh_id'] . '">' . garage_title_clean($context['user_vehicles'][$count]['vehicle']) . '</a></td>
                </tr>
                <tr>
                    <td><b>' . $txt['smfg_date_created'] . ':</b></td>
                    <td>' . date($context['date_format'], $context['user_vehicles'][$count]['date_created']) . '</td>
                </tr>';
        if (!empty($context['user_vehicles'][$count]['color'])) {
            echo '
                    <tr>
                        <td><b>' . $txt['smfg_color'] . ':</b></td>
                        <td>' . $context['user_vehicles'][$count]['color'] . '</td>
                    </tr>';
        }
        echo '
                <tr>
                    <td><b>' . $txt['smfg_mileage'] . ':</b></td>
                    <td>' . $context['user_vehicles'][$count]['mileage'] . ' ' . $context['user_vehicles'][$count]['mileage_unit'] . '</td>
                </tr>
                <tr>
                    <td><b>' . $txt['smfg_total_mods'] . ':</b></td>
                    <td>' . $context['user_vehicles'][$count]['total_mods'] . '</td>
                </tr>
                <tr>
                    <td><b>' . $txt['smfg_total_views'] . ':</b></td>
                    <td>' . $context['user_vehicles'][$count]['views'] . '</td>
                </tr>
                <tr>
                    <td><b>' . $txt['smfg_vehicle_rating'] . ':</b></td>
                    <td>';
        if ($context['user_vehicles'][$count]['poss_rating']) {
            if ($smfgSettings['rating_system'] == 0) {
                echo $context['user_vehicles'][$count]['rating'] . '/' . $context['user_vehicles'][$count]['poss_rating'];
            } else {
                if ($smfgSettings['rating_system'] == 1) {
                    echo $context['user_vehicles'][$count]['rating'] . '/10 (' . $txt['smfg_rated'] . ' ' . ($context['user_vehicles'][$count]['poss_rating'] / 10) . ' ' . $txt['smfg_times'] . ')';
                }
            }
        } else {
            echo $txt['smfg_vehicle_not_rated'];
        }
        echo '</td>
                </tr>                                                
            </tbody>
            </table>
            </td>
            </tr>
            </table>';

        if ($count < (count($context['user_vehicles']) - 2)) {
            echo '
                <hr />';
        }

        $count++;
    }

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    <br />';

    echo '      
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo (defined('SMF_VERSION') && version_compare(SMF_VERSION, '2.1', '>=') ? '' : $txt['pages'] . ': ') . $context['comments']['page_index'];

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
                <table width="100%" cellspacing="1" cellpadding="3" border="0">';
    $count = 0;
    $forms = '';
    if (isset($context['comments'][$count]['comment'])) {
        echo '
                        <tr>
                            <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                            ' . $txt['smfg_author'] . '
                            </span></h4></div></td>
                            <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                            ' . $txt['smfg_message'] . '
                            </span></h4></div></td>
                        </tr>';
        while (isset($context['comments'][$count]['comment'])) {
            loadMemberData(ARRAY($context['comments'][$count]['author_id']));
            $avatarimg = "";
            if ($user_profile[$context['comments'][$count]['author_id']]['id_attach']) {
                $avatarimg = '<img src="' . $scripturl . '?action=dlattach;attach=' . $user_profile[$context['comments'][$count]['author_id']]['id_attach'] . ';type=avatar" alt="" class="avatar" border="0" /><br />';
            }
            echo '
                            <tr>
                                <td width="150" align="center" valign="middle">
                                ' . $avatarimg . '<b><a href="' . $scripturl . '?action=profile;u=' . $context['comments'][$count]['author_id'] . '">' . $context['comments'][$count]['author'] . '</a></b>
                                <table cellspacing="4" align="center" width="150">
                                    <tr>
                                        <td class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['comments'][$count]['author_VID'] . '">' . garage_title_clean($context['comments'][$count]['author_vehicle']) . '</a></td>
                                    </tr>
                                </table>
                                <table cellspacing="4" border="0">
                                    <tr>
                                        <td nowrap="nowrap"><span class="smalltext"><b>' . $txt['smfg_joined'] . ':</b>&nbsp;' . date($context['date_format'],
                    $context['comments'][$count]['date_reg']) . '<br /><b>' . $txt['smfg_posts'] . ':</b>&nbsp;' . $context['comments'][$count]['posts'] . '</span>
                                        </td>
                                    </tr>
                                </table>
                                </td>
                                <td valign="top">
                                <table width="100%" cellspacing="0">
                                    <tr>
                                        <td class="smalltext" width="100%"><div style="float:right"><b>' . $txt['smfg_posted'] . ':</b>&nbsp;' . date($context['date_format'],
                    $context['comments'][$count]['post_date']) . '&nbsp;</div>
                                        </td>
                                    </tr>
                                </table>
                                <table width="100%" cellspacing="5">
                                    <tr>
                                        <td>
                                        <div class="postbody">' . $context['comments'][$count]['comment'] . '</div>
                                        <br clear="all" /><br />
                                        <table width="100%" cellspacing="0">
                                            <tr valign="middle">
                                                <td class="smalltext" align="right">';
            if ($context['user']['is_admin']) {
                echo '<img src="' . $settings['actual_images_url'] . '/ip.gif" alt="IP" title="IP" />&nbsp;<a href="' . $scripturl . '?action=trackip;searchip=' . $context['comments'][$count]['author_ip'] . '">' . $context['comments'][$count]['author_ip'] . '</a>&nbsp;<a href="' . $scripturl . '?action=helpadmin;help=see_admin_ip" onclick="return reqWin(this.href);" class="help">(?)</a>';
            }
            echo '
                                                </td>
                                            </tr>
                                        </table>
                                        </td>
                                    </tr>
                                </table>
                                </td>
                            </tr>
                            <tr>
                                <td nowrap="nowrap">&nbsp;</td>
                                <td><div class="smalltext" style="float: left;">&nbsp;&nbsp;</div><div class="gensmall" style="float:right">';
            // If the reader is the author, let them edit it
            if ($context['user']['id'] == $context['comments'][$count]['author_id'] | allowedTo('edit_all_comments')) {
                echo '<a href="' . $scripturl . '?action=garage;sa=edit_garage_comment;UID=' . $_GET['UID'] . ';CID=' . $context['comments'][$count]['CID'] . '"><img src="' . $settings['default_images_url'] . '/garage_edit.gif" alt="Edit" title="Edit" /></a>&nbsp;<a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_comment'] . '\')) { document.delete_comment_' . $context['comments'][$count]['CID'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="Delete" title="Delete" /></a>';
                $forms .= '
                                    <form action="' . $scripturl . '?action=garage;sa=delete_garage_comment;UID=' . $_GET['UID'] . ';CID=' . $context['comments'][$count]['CID'] . ';sesc=' . $context['session_id'] . '" method="post" name="delete_comment_' . $context['comments'][$count]['CID'] . '" id="delete_comment_' . $context['comments'][$count]['CID'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $_GET['UID'] . '" />
                                    </form>';
            }
            echo '</div></td>
                            </tr>
                            <tr>
                                <td colspan="2">';

            if ($count < ($context['comments']['total'] - 1)) {
                echo '
                                    <hr />';
            }

            echo '
                                </td>
                            </tr>';
            $count++;
        }
    } else {
        echo '
                       <tr>
                            <td colspan="2" align="center">' . $txt['smfg_no_comments'] . '<br /></td>
                       </tr>';
    }

    echo '
            </table>
        </td>
    </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    <br />';

    if ($context['user']['is_logged'] && allowedTo('post_comments')) {

        echo '   
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_add_comment'];

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
        <form action="' . $scripturl . '?action=garage;sa=insert_garage_comment" method="post" name="add_comment" id="add_comment" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $_GET['UID'] . '" />                  
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
                
        <tr>
            <td align="right" width="20%"><span style="">', $smfgSettings['enable_guestbooks_bbcode'] ? $txt['smfg_bbc_supported'] : $txt['smfg_bbc_disabled'], '<br /><br />' . $txt['smfg_html_supported'] . '</span></td>
            <td><textarea name="post" cols="70" rows="7" style="width: 98%"></textarea></td>
        </tr>
        <tr>
            <td align="center" height="28" colspan="5"><input type="hidden" value="' . $_GET['UID'] . '" name="UID" /><input type="hidden" value="' . $context['user']['id'] . '" name="user_id" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_post_comment'] . '" /></td>
        </tr>';

    }

    echo '
    </table>
    </form>
    ' . $forms . '';
    if ($context['user']['is_logged'] && allowedTo('post_comments')) {

        echo ' 
        <script type="text/javascript">
        var frmvalidator = new Validator("add_comment");
        frmvalidator.addValidation("post","req","Please enter a comment.");
        frmvalidator.addValidation("post","maxlen=2500","Max length for comments is 2500 characters.");
        </script>';

    }

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();
}

function template_view_vehicle()
{
    global $context, $settings, $options, $txt, $scripturl, $db_prefix, $smfgSettings, $boardurl, $user_profile;

    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    // show the vehicle management pane, if this is their vehicle, or they have perms
    if ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo '
                                <a href="#" class="collapse" onclick="shrinkSection(\'vehicleManagement\', \'vehicleManagementUpshrink\'); return false;"><img id="vehicleManagementUpshrink" src="' . $settings['default_images_url'] . '/collapse.gif" alt="*" title="Shrink or expand the table." align="bottom" style="margin: 0 1ex;" /></a>';

        echo $txt['smfg_vehicle_management'];

        echo '        
                        </h3>
                    </div>
                </td>
                </tr>
            </tbody>
        </table>';

        echo '
        <div id="vehicleManagement">';

        echo '    
        <span class="clear upperframe"><span></span></span>
        <div class="roundframe"><div class="innerframe">';

        // if they are the owner show them edit buttons
        if ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {

            echo '
            <div class="content">
                <ul id="quick_tasks" class="flow_hidden" style="padding: 0;">';

            // only let them set the main vehicle if this is NOT their main vehicle
            if (!$context['user_vehicles']['main_vehicle']) {
                echo '
                    <li>
                        <form action="' . $scripturl . '?action=garage;sa=set_main_vehicle;VID=' . $context['user_vehicles']['id'] . ';user_id=' . $context['user_vehicles']['user_id'] . ';sesc=', $context['session_id'], '" id="set_main_vehicle_' . $context['user_vehicles']['id'] . '" enctype="multipart/form-data" method="post" name="set_main_vehicle_' . $context['user_vehicles']['id'] . '" style="padding:0; margin:0; display:inline;"><input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'] . '" />
                            <a href="#" onClick="document.set_main_vehicle_' . $context['user_vehicles']['id'] . '.submit(); return false;">
                            <img src="' . $settings['default_images_url'] . '/garage_set_main_vehicle.png" alt="" class="home_image png_fix" /></a>
                        </form>
                        <h5><a href="#" onClick="document.set_main_vehicle_' . $context['user_vehicles']['id'] . '.submit(); return false;">' . $txt['smfg_set_main_vehicle'] . '</a></h5>
                        <span class="task">' . $txt['smfg_set_main_desc'] . '</span>
                    </li>';
            }

            echo '
                    <li>
                        <a href="' . $scripturl . '?action=garage;sa=edit_vehicle;VID=' . $context['user_vehicles']['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_edit_vehicle.png" alt="" class="home_image png_fix" /></a>
                        <h5><a href="' . $scripturl . '?action=garage;sa=edit_vehicle;VID=' . $context['user_vehicles']['id'] . '">' . $txt['smfg_edit_vehicle'] . '</a></h5>
                        <span class="task">' . $txt['smfg_edit_vehicle_desc'] . '</span>
                    </li>
                    <li>
                        <form action="' . $scripturl . '?action=garage;sa=delete_vehicle;VID=' . $context['user_vehicles']['id'] . ';ug=1;sesc=' . $context['session_id'] . '" method="post" name="remove_vehicle" id="remove_vehicle" style="display: inline;">
                            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=user_garage" />
                            <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_vehicle'] . '\')) { document.remove_vehicle.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/garage_delete_vehicle.png" alt="" class="home_image png_fix" /></a>
                        </form>
                        <h5><a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_vehicle'] . '\')) { document.remove_vehicle.submit(); } else { return false; } return false;">' . $txt['smfg_delete_vehicle_title'] . '</a></h5>
                        <span class="task">' . $txt['smfg_delete_vehicle_desc'] . '</span>
                    </li>
                    <li>
                        <a href="' . $scripturl . '?action=garage;sa=add_modification;VID=' . $context['user_vehicles']['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_add_mod.png" alt="" class="home_image png_fix" /></a>
                        <h5><a href="' . $scripturl . '?action=garage;sa=add_modification;VID=' . $context['user_vehicles']['id'] . '">' . $txt['smfg_add_modification'] . '</a></h5>
                        <span class="task">' . $txt['smfg_add_mod_desc'] . '</span>
                    </li>
                    <li>
                        <a href="' . $scripturl . '?action=garage;sa=add_insurance;VID=' . $context['user_vehicles']['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_add_insurance.png" alt="" class="home_image png_fix" /></a>
                        <h5><a href="' . $scripturl . '?action=garage;sa=add_insurance;VID=' . $context['user_vehicles']['id'] . '">' . $txt['smfg_add_insurance'] . '</a></h5>
                        <span class="task">' . $txt['smfg_add_ins_desc'] . '</span>
                    </li>
                    <li>
                        <a href="' . $scripturl . '?action=garage;sa=add_quartermile;VID=' . $context['user_vehicles']['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_add_quartermile.png" alt="" class="home_image png_fix" /></a>
                        <h5><a href="' . $scripturl . '?action=garage;sa=add_quartermile;VID=' . $context['user_vehicles']['id'] . '">' . $txt['smfg_add_quartermile'] . '</a></h5>
                        <span class="task">' . $txt['smfg_add_qmile_desc'] . '</span>
                    </li>
                    <li>
                        <a href="' . $scripturl . '?action=garage;sa=add_dynorun;VID=' . $context['user_vehicles']['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_add_dynorun.png" alt="" class="home_image png_fix" /></a>
                        <h5><a href="' . $scripturl . '?action=garage;sa=add_dynorun;VID=' . $context['user_vehicles']['id'] . '">' . $txt['smfg_add_dynorun'] . '</a></h5>
                        <span class="task">' . $txt['smfg_add_dyno_desc'] . '</span>
                    </li>
                    <li>
                        <a href="' . $scripturl . '?action=garage;sa=add_laptime;VID=' . $context['user_vehicles']['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_add_laptime.png" alt="" class="home_image png_fix" /></a>
                        <h5><a href="' . $scripturl . '?action=garage;sa=add_laptime;VID=' . $context['user_vehicles']['id'] . '">' . $txt['smfg_add_laptime'] . '</a></h5>
                        <span class="task">' . $txt['smfg_add_lap_desc'] . '</span>
                    </li>
                    <li>
                        <a href="' . $scripturl . '?action=garage;sa=add_service;VID=' . $context['user_vehicles']['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_add_service.png" alt="" class="home_image png_fix" /></a>
                        <h5><a href="' . $scripturl . '?action=garage;sa=add_service;VID=' . $context['user_vehicles']['id'] . '">' . $txt['smfg_add_service'] . '</a></h5>
                        <span class="task">' . $txt['smfg_add_service_desc'] . '</span>
                    </li>
                </ul>
            </div>';

        }

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '
        </div>';

        echo '
        <br />';

    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $context['user_vehicles']['vehicle'];

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
    <table border="0" cellpadding="3" cellspacing="1" width="100%">';

    echo '
    <tr>
        <td align="center" valign="top" width="475">
            <table border="0" width="100%">';

    echo '
            <tr>
                <td align="left">
                    <div id="mainImageContainer">';

    // show a placeholder if they dont have an image
    if (!empty($context['hilite_image_location'])) {
        echo '
                        <a href="' . $context['hilite_image_location'] . '" rel="shadowbox" title="' . garage_title_clean($context['user_vehicles']['vehicle'] . ' :: ' . $context['hilite_desc']) . '" class="smfg_imageTitle"><img src="' . $context['hilite_image_location'] . '" /></a>';
    } else {
        echo '
                        <img src="' . $settings['default_images_url'] . '/garage_no_vehicle.png" />';
    }

    echo '
                    </div>
                </td>
            </tr>';

    /*
            if(!empty($context['hilite_thumb_location'])) {
                echo '
                <tr>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="center" style="padding-bottom: 15px;"><a href="'.$context['hilite_image_location'].'" rel="shadowbox" title="'.garage_title_clean($context['user_vehicles']['vehicle'].' :: '.$context['hilite_desc']).'" class="smfg_imageTitle"><img src="'.$boardurl.'/'.$smfgSettings['upload_directory'].'cache/'.$context['hilite_thumb_location'].'" width="'.$context['hilite_thumb_width'].'" height="'.$context['hilite_thumb_height'].'" /></a></td>
                </tr>';
            }
            */

    if (!empty($context['user_vehicles']['comments'])) {

        echo '
                <tr>
                    <td align="left" style="padding-top: 15px;">' . $context['user_vehicles']['comments'] . '</td>
                </tr>';
    }

    echo '
            </table>';

    echo '
        </td>
        <td valign="top" align="left">
            <table border="0" cellspacing="1" cellpadding="3" width="100%" style="max-width: 400px;">
            <tr>
                <td colspan="2"><div class="title_bar" style="text-align: left;"><h4 class="titlebg"><span class="ie6_header">
                ', $txt['smfg_vehicle_details'], '
                </span></h4></div></td>
            </tr>
            <tr class="tableRow">
                <td align="left" width="165"><b>' . $txt['smfg_owner'] . '</b></td>
                <td align="left"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['user_vehicles']['user_id'] . '"><b>' . $context['user_vehicles']['memberName'] . '</b></a></td>
            </tr>
            <tr class="tableRow">
                <td align="left" width="165"><b>' . $txt['smfg_vehicle'] . '</b></td>
                <td align="left">' . $context['user_vehicles']['vehicle'] . '</td>
            </tr>';
    if (!empty($context['user_vehicles']['color'])) {
        echo '
                <tr class="tableRow">
                    <td align="left" width="165"><b>' . $txt['smfg_color'] . '</b></td>
                    <td align="left">' . $context['user_vehicles']['color'] . '</td>
                </tr>';
    }
    if (!empty($context['user_vehicles']['engine'])) {
        echo '
                <tr class="tableRow">
                    <td align="left" width="165"><b>' . $txt['smfg_engine_type'] . '</b></td>
                    <td align="left">' . $context['user_vehicles']['engine'] . '</td>
                </tr>';
    }
    echo '
            <tr class="tableRow">
                <td align="left" width="165"><b>' . $txt['smfg_updated'] . '</b></td>
                <td align="left">' . date($context['date_format'], $context['user_vehicles']['date_updated']) . '</td>
            </tr>
            <tr class="tableRow">
                <td align="left" width="165"><b>' . $txt['smfg_mileage'] . '</b></td>
                <td align="left">' . $context['user_vehicles']['mileage'] . ' ' . $context['user_vehicles']['mileage_unit'] . '</td>
            </tr>
            <tr class="tableRow">
                <td align="left" width="165"><b>' . $txt['smfg_price'] . '</b></td>
                <td align="left">' . $context['user_vehicles']['price'] . ' ' . $context['user_vehicles']['currency'] . '</td>
            </tr>
            <tr class="tableRow">
                <td align="left" width="165"><b>' . $txt['smfg_total_mods'] . '</b></td>
                <td align="left">' . $context['user_vehicles']['total_mods'] . '</td>
            </tr>
            <tr class="tableRow">
                <td align="left" width="165"><b>' . $txt['smfg_total_spent'] . '</b></td>
                <td align="left">' . $context['user_vehicles']['total_spent'] . ' ' . $context['user_vehicles']['currency'] . '</td>
            </tr>
            <tr class="tableRow">
                <td align="left" width="165"><b>' . $txt['smfg_total_views'] . '</b></td>
                <td align="left">' . $context['user_vehicles']['views'] . '</td>
            </tr>
            <tr class="tableRow">
                <td align="left" width="165"><b>' . $txt['smfg_vehicle_rating'] . '</b></td>';
    // Show the vehicle rating if there is one
    if ($context['user_vehicles']['poss_rating']) {
        if ($smfgSettings['rating_system'] == 0) {
            echo '<td align="left">' . $context['user_vehicles']['rating'] . '/' . $context['user_vehicles']['poss_rating'] . '</td>';
        } else {
            if ($smfgSettings['rating_system'] == 1) {
                echo '<td align="left">' . $context['user_vehicles']['rating'] . '/10 (' . $txt['smfg_rated'] . ' ' . ($context['user_vehicles']['poss_rating'] / 10) . ' ' . $txt['smfg_times'] . ')</td>';
            }
        }
    } else {
        echo '
                <td align="left">' . $txt['smfg_vehicle_not_rated'] . '</td>';
    }
    echo '
            </tr>';
    // If they are logged in, let the user rate it
    if ($context['user']['is_logged'] && $context['view_own_vehicle'] != 1) {
        echo '
                <tr class="tableRow">
                    <td align="left" valign="top">
                    <b>Please Rate</b></td>';
        // If they already rated it, dont let them rate it again
        if (isset($context['user_vehicles']['veh_rating'])) {
            echo '<td>' . $txt['smfg_rated'] . ' ' . $context['user_vehicles']['veh_rating'] . ' ' . $txt['smfg_on'] . ' ' . date("n/j/Y",
                    $context['user_vehicles']['rate_date']) . '<br />
                        <form action="' . $scripturl . '?action=garage;sa=remove_rating;VID=' . $_GET['VID'] . ';RID=' . $context['user_vehicles']['rid'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_rating_' . $context['user_vehicles']['rid'] . '" id="remove_rating_' . $context['user_vehicles']['rid'] . '" style="display: inline;">
                            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'] . '" />
                            <a href="#" onClick="document.remove_rating_' . $context['user_vehicles']['rid'] . '.submit(); return false;">(' . $txt['smfg_remove_rating'] . ')</a>
                        </form>';
        } else {
            echo '
                        <td>
                        <form action="' . $scripturl . '?action=garage;sa=insert_rating" id="add_rating" enctype="multipart/form-data" method="post" name="add_rating" style="padding:0; margin:0;">
                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '" />
                        <select id="rating" name="rating">
                        <option value="">' . $txt['smfg_select_rating'] . '</option>
                        <option value="">------</option>
                        <option value="10">10 ' . $txt['smfg_best'] . '</option>
                        <option value="9">9</option>
                        <option value="8">8</option>
                        <option value="7">7</option>
                        <option value="6">6</option>
                        <option value="5">5</option>
                        <option value="4">4</option>
                        <option value="3">3</option>
                        <option value="2">2</option>
                        <option value="1">1 ' . $txt['smfg_worst'] . '</option>
                        </select>
                        <input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $context['user']['id'] . '" name="user_id" /><input name="Rate" type="submit" value="' . $txt['smfg_rate'] . '" /><input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                        </form>
                        <script type="text/javascript">
                         var frmvalidator = new Validator("add_rating");
                         frmvalidator.addValidation("rating","req","' . $txt['smfg_val_select_rating'] . '");
                         frmvalidator.addValidation("rating","dontselect=0","' . $txt['smfg_val_select_rating'] . '");
                         frmvalidator.addValidation("rating","dontselect=1","' . $txt['smfg_val_select_rating'] . '");
                        </script>
                        </td>';
        }
        echo '
                </tr>';
    }
    echo '
            </table>
        </td>
    </tr>';

    echo '
    </table>
    </td>
</tr>
</table>';

    echo '  
</div>
</div>
<span class="lowerframe"><span></span></span>';

    echo '
<table cellpadding="0" cellspacing="0" border="0" style="margin: 7px 0 5px 10px" id="tab_table">
    <tr id="tab_row">
        <td>                
            <ul class="dropmenu">
                <li id="button_g_images">
                    <a class="firstlevel" href="#images "onclick="change_tab(\'000\');" id="tab000">
                        <span class="firstlevel">' . $txt['smfg_images'] . '</span>
                    </a>
                </li>';
    $count = 0;
    if (isset($context['user_vehicles'][$count]['video_id']) && $smfgSettings['enable_vehicle_video']) {
        echo '
                <li id="button_g_videos">
                    <a class="firstlevel" href="#videos "onclick="change_tab(\'001\');" id="tab001">
                        <span class="firstlevel">' . $txt['smfg_videos'] . '</span>
                    </a>
                </li>';
    }
    if (isset($context['mods'][$count]['id']) && $smfgSettings['enable_modification']) {
        echo '
                <li id="button_g_mods">
                    <a class="firstlevel" href="#modifications "onclick="change_tab(\'002\');" id="tab002">
                        <span class="firstlevel">' . $txt['smfg_modifications'] . '</span>
                    </a>
                </li>';
    }
    if (isset($context['qmiles'][$count]['id']) && $smfgSettings['enable_quartermile']) {
        echo '
                <li id="button_g_qmiles">
                    <a class="firstlevel" href="#quartermiles "onclick="change_tab(\'003\');" id="tab003">
                        <span class="firstlevel">' . $txt['smfg_qmile_runs'] . '</span>
                    </a>
                </li>';
    }
    if (isset($context['dynoruns'][$count]['id']) && $smfgSettings['enable_dynorun']) {
        echo '
                <li id="button_g_dynoruns">
                    <a class="firstlevel" href="#dynoruns "onclick="change_tab(\'004\');" id="tab004">
                        <span class="firstlevel">' . $txt['smfg_dynoruns'] . '</span>
                    </a>
                </li>';
    }
    if (isset($context['laps'][$count]['id']) && $smfgSettings['enable_laptimes']) {
        echo '
                <li id="button_g_laps">
                    <a class="firstlevel" href="#laps "onclick="change_tab(\'005\');" id="tab005">
                        <span class="firstlevel">' . $txt['smfg_laps'] . '</span>
                    </a>
                </li>';
    }
    if (isset($context['premiums'][$count]['id']) && $smfgSettings['enable_insurance']) {
        echo '
                <li id="button_g_premiums">
                    <a class="firstlevel" href="#premiums "onclick="change_tab(\'006\');" id="tab006">
                        <span class="firstlevel">' . $txt['smfg_premiums'] . '</span>
                    </a>
                </li>';
    }
    if (isset($context['services'][$count]['id']) && $smfgSettings['enable_service']) {
        echo '
                <li id="button_g_services">
                    <a class="firstlevel" href="#services "onclick="change_tab(\'007\');" id="tab007">
                        <span class="firstlevel">' . $txt['smfg_services'] . '</span>
                    </a>
                </li>';
    }
    if (isset($context['blog'][$count]['id']) && $smfgSettings['enable_blogs'] || $context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {
        echo '
                <li id="button_g_blog">
                    <a class="firstlevel" href="#blog "onclick="change_tab(\'008\');" id="tab008">
                        <span class="firstlevel">' . $txt['smfg_blog'] . '</span>
                    </a>
                </li>';
    }
    if ($smfgSettings['enable_guestbooks']) {
        echo '
                <li id="button_g_guestbook">
                    <a class="firstlevel" href="#guestbook "onclick="change_tab(\'009\');" id="tab009">
                        <span class="firstlevel">' . $txt['smfg_guestbook'] . '</span>
                    </a>
                </li>';
    }
    echo '
            </ul>';

    echo '
        </td>

    </tr>
</table>';

    // images panel
    echo '        
    <div class="garage_panel" id="options000" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_images'];

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
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">        
            <tr>
                <td>';


    $count = 0;
    if (isset($context['user_vehicles'][$count]['image_id'])) {
        echo '
                <tr>
                    <td valign="middle">';
        while (isset($context['user_vehicles'][$count]['image_id'])) {
            echo '
                        <a href="' . $context['user_vehicles'][$count]['attach_location'] . '" title="' . garage_title_clean($context['user_vehicles']['vehicle'] . ' :: ' . $context['user_vehicles'][$count]['attach_desc']) . '" class="smfg_imageTitle imageGallery"><img src="' . $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['user_vehicles'][$count]['attach_thumb_location'] . '" width="' . $context['user_vehicles'][$count]['attach_thumb_width'] . '" height="' . $context['user_vehicles'][$count]['attach_thumb_height'] . '" /></a>';
            $count++;
        }
        echo '
                    </td>
                </tr>';
    } else {
        echo '
                <tr>
                    <td align="center">' . $txt['smfg_no_images'] . '</td>
                </tr>';
    }
    echo '
        </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    $count = 0;
    if (isset($context['user_vehicles'][$count]['image_id'])) {
        echo '
        <script type="text/javascript">
        $.preload( ';
        while (isset($context['user_vehicles'][$count]['image_id'])) {
            echo "
                '" . $context['user_vehicles'][$count]['attach_location'] . "'";
            if ($count < ($context['user_vehicles']['total_images'] - 1)) {
                echo ",";
            }
            $count++;
        }
        echo '
        );
        </script>';
    }

    echo '
    </div>';

    // videos panel
    echo '
    <div class="garage_panel" id="options001" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_videos'];

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
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">        
            <tr>
                <td>';

    $count = 0;
    if (isset($context['user_vehicles'][$count]['video_id'])) {
        echo '
            <tr>
                <td valign="middle">';
        while (isset($context['user_vehicles'][$count]['video_id'])) {
            echo '
                    <a href="' . $scripturl . '?action=garage;sa=video;id=' . $context['user_vehicles'][$count]['video_id'] . '" rel="shadowbox[video];width=' . $context['user_vehicles'][$count]['video_width'] . ';height=' . $context['user_vehicles'][$count]['video_height'] . ';" title="' . garage_title_clean('<b>' . $context['user_vehicles'][$count]['video_title'] . '</b> :: ' . $context['user_vehicles'][$count]['video_desc']) . '" class="smfg_videoTitle" ><img src="' . $context['user_vehicles'][$count]['video_thumb'] . '" /></a>';
            $count++;
        }
        echo '
                </td>
            </tr>';
    } else {
        echo '
            <tr>
                <td align="center">' . $txt['smfg_no_videos'] . '</td>
            </tr>';
    }
    echo '  
        </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '  
    </div>
    <div class="garage_panel" id="options002" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_modifications'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';
    $count = 0;
    if (isset($context['mods'][$count]['id'])) {
        echo '
            <tr>
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                &nbsp;
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_modification'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_rating'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_price'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_install_cost'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_created'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_updated'] . '
                </span></h4></div></td> 
                ', ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) ? '<td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">&nbsp;</span></h4></div></td>' : '', '
            </tr>
            ';

        // Loop through each modification.
        while (isset($context['mods'][$count]['id'])) {
            echo '<tr class="tableRow">
                <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['mods'][$count]['image'] . $context['mods'][$count]['spacer'] . $context['mods'][$count]['video'] . '</td>
                <td><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['user_vehicles']['id'] . ';MID=' . $context['mods'][$count]['id'] . '" title="' . $context['mods'][$count]['mod_tooltip'] . '" class="smfg_videoTitle">' . garage_title_clean($context['mods'][$count]['manufacturer'] . ' ' . $context['mods'][$count]['title']) . '</a></td>
                <td align="center">' . $context['mods'][$count]['product_rating'] . '</td>
                <td align="center">' . $context['mods'][$count]['price'] . '</td>
                <td align="center">' . $context['mods'][$count]['install_price'] . '</td>
                <td nowrap="nowrap">' . date($context['date_format'], $context['mods'][$count]['date_created']) . '</td>
                <td nowrap="nowrap">' . date($context['date_format'],
                    $context['mods'][$count]['date_updated']) . '</td>';

            if ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {
                echo '
                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=edit_modification;VID=' . $context['user_vehicles']['id'] . ';MID=' . $context['mods'][$count]['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                <form action="' . $scripturl . '?action=garage;sa=delete_modification;VID=' . $context['user_vehicles']['id'] . ';MID=' . $context['mods'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_modification_' . $context['mods'][$count]['id'] . '" id="remove_modification_' . $context['mods'][$count]['id'] . '" style="display: inline;">
                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'] . '" />
                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_modification'] . '\')) { document.remove_modification_' . $context['mods'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                </form></td>';
            }

            echo '
               </tr>
            ';
            $count++;
        }
    } else {
        echo '
                <tr>
                    <td align="center">' . $txt['smfg_no_modifications'] . '</td>
                </tr>';
    }

    echo '     
        </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '  
    </div>
        <div class="garage_panel" id="options003" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_qmile_runs'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    $count = 0;
    if (isset($context['qmiles'][$count]['id'])) {
        echo '
            <tr>                
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                &nbsp;
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_rt'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_sixty'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_three_thiry'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_eighth'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_eighth_mph'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_thou'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_quart'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_quart_mph'] . '
                </span></h4></div></td> 
                ', ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) ? '<td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">&nbsp;</span></h4></div></td>' : '', '
            </tr>
            ';

        // Loop through each quartermile
        while (isset($context['qmiles'][$count]['id'])) {
            echo '<tr class="tableRow">
                <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['qmiles'][$count]['image'] . $context['qmiles'][$count]['spacer'] . $context['qmiles'][$count]['video'] . '</td>
                <td align="center">' . $context['qmiles'][$count]['rt'] . '</td>
                <td align="center">' . $context['qmiles'][$count]['sixty'] . '</td>
                <td align="center">' . $context['qmiles'][$count]['three'] . '</td>
                <td align="center">' . $context['qmiles'][$count]['eighth'] . '</td>
                <td align="center">' . $context['qmiles'][$count]['eighthmph'] . ' MPH</td>
                <td align="center">' . $context['qmiles'][$count]['thou'] . '</td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_quartermile;VID=' . $_GET['VID'] . ';QID=' . $context['qmiles'][$count]['id'] . '">' . garage_title_clean($context['qmiles'][$count]['quart']) . '</a></td>
                <td align="center">' . $context['qmiles'][$count]['quartmph'] . ' MPH</td>';

            if ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {
                echo '
                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=edit_quartermile;VID=' . $context['user_vehicles']['id'] . ';QID=' . $context['qmiles'][$count]['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                <form action="' . $scripturl . '?action=garage;sa=delete_quartermile;VID=' . $context['user_vehicles']['id'] . ';QID=' . $context['qmiles'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_quartermile_' . $context['qmiles'][$count]['id'] . '" id="remove_quartermile_' . $context['qmiles'][$count]['id'] . '" style="display: inline;">
                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'] . '" />
                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_quartermile'] . '\')) { document.remove_quartermile_' . $context['qmiles'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                </form></td>';
            }

            echo '
            </tr>
            ';
            $count++;
        }
    } else {
        echo '
                <tr>
                    <td align="center">' . $txt['smfg_no_quartermiles'] . '</td>
                </tr>';
    }
    echo '      
        </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '  
    </div>
        <div class="garage_panel" id="options004" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_dynoruns'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    $count = 0;
    if (isset($context['dynoruns'][$count]['id'])) {
        echo '
            <tr>
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                &nbsp;
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_dynocenter'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_bhp'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_torque'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_boost'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_nitrous'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_peakpoint'] . '
                </span></h4></div></td> 
                ', ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) ? '<td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">&nbsp;</span></h4></div></td>' : '', '
            </tr>
            ';

        // Loop through each dynorun
        while (isset($context['dynoruns'][$count]['id'])) {
            echo '<tr class="tableRow">
                <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['dynoruns'][$count]['image'] . $context['dynoruns'][$count]['spacer'] . $context['dynoruns'][$count]['video'] . '</td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=dc_review;BID=' . $context['dynoruns'][$count]['dynocenter_id'] . '">' . garage_title_clean($context['dynoruns'][$count]['dynocenter']) . '</a></td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_dynorun;VID=' . $context['user_vehicles']['id'] . ';DID=' . $context['dynoruns'][$count]['id'] . '">' . garage_title_clean($context['dynoruns'][$count]['bhp']) . '</a></td>
                <td align="center">' . $context['dynoruns'][$count]['torque'] . '</td>
                <td align="center">' . $context['dynoruns'][$count]['boost'] . '</td>
                <td align="center">' . $context['dynoruns'][$count]['nitrous'] . ' ' . $txt['smfg_shot'] . '</td>
                <td align="center">' . $context['dynoruns'][$count]['peakpoint'] . ' ' . $txt['smfg_rpm'] . '</td>';

            if ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {
                echo '
                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=edit_dynorun;VID=' . $context['user_vehicles']['id'] . ';DID=' . $context['dynoruns'][$count]['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                <form action="' . $scripturl . '?action=garage;sa=delete_dynorun;VID=' . $context['user_vehicles']['id'] . ';DID=' . $context['dynoruns'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_dynorun_' . $context['dynoruns'][$count]['id'] . '" id="remove_dynorun_' . $context['dynoruns'][$count]['id'] . '" style="display: inline;">
                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'] . '" />
                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_dynorun'] . '\')) { document.remove_dynorun_' . $context['dynoruns'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                </form></td>';
            }

            echo '
            </tr>
            ';
            $count++;
        }
    } else {
        echo '
                <tr>
                    <td align="center">' . $txt['smfg_no_dynoruns'] . '</td>
                </tr>';
    }
    echo '      
        </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '  
    </div>
    <div class="garage_panel" id="options005" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_laps'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    $count = 0;
    if (isset($context['laps'][$count]['id'])) {
        echo '
            <tr>
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                &nbsp;
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_track'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_condition'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_type'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_laptime_specs'] . '
                </span></h4></div></td>                 
                ', ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) ? '<td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">&nbsp;</span></h4></div></td>' : '', '
            </tr>
            ';

        // Loop through each lap
        while (isset($context['laps'][$count]['id'])) {
            echo '<tr class="tableRow">
                <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['laps'][$count]['image'] . $context['laps'][$count]['spacer'] . $context['laps'][$count]['video'] . '</td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_track;TID=' . $context['laps'][$count]['track_id'] . '">' . garage_title_clean($context['laps'][$count]['track']) . '</a></td>
                <td align="center">' . $context['laps'][$count]['condition'] . '</td>
                <td align="center">' . $context['laps'][$count]['type'] . '</td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_laptime;VID=' . $context['user_vehicles']['id'] . ';LID=' . $context['laps'][$count]['id'] . '">' . garage_title_clean($context['laps'][$count]['time']) . '</a></td>';

            if ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {
                echo '
                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=edit_laptime;VID=' . $context['user_vehicles']['id'] . ';LID=' . $context['laps'][$count]['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                <form action="' . $scripturl . '?action=garage;sa=delete_laptime;VID=' . $context['user_vehicles']['id'] . ';LID=' . $context['laps'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_laptime_' . $context['laps'][$count]['id'] . '" id="remove_laptime_' . $context['laps'][$count]['id'] . '" style="display: inline;">
                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'] . '" />
                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_laptime'] . '\')) { document.remove_laptime_' . $context['laps'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                </form></td>';
            }

            echo '
            </tr>
            ';
            $count++;
        }
    } else {
        echo '
                <tr>
                    <td align="center">' . $txt['smfg_no_laps'] . '</td>
                </tr>';
    }
    echo '      
        </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '  
    </div>
        <div class="garage_panel" id="options006" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_premiums'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    $count = 0;
    if (isset($context['premiums'][$count]['id'])) {
        echo '
            <tr>
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_insurer'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_premium'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_cover_type'] . '
                </span></h4></div></td> 
                ', ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) ? '<td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">&nbsp;</span></h4></div></td>' : '', '
            </tr>
            ';

        // Loop through each premium
        while (isset($context['premiums'][$count]['id'])) {
            echo '<tr class="tableRow">
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=insurance_review;BID=' . $context['premiums'][$count]['insurer_id'] . '">' . garage_title_clean($context['premiums'][$count]['insurer']) . '</a></td>
                <td align="center">' . $context['premiums'][$count]['premium'] . '</td>
                <td align="center">' . $context['premiums'][$count]['cover_type'] . '</td>';

            if ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {
                echo '
                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=edit_insurance;VID=' . $context['user_vehicles']['id'] . ';INS_ID=' . $context['premiums'][$count]['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                <form action="' . $scripturl . '?action=garage;sa=delete_insurance;VID=' . $context['user_vehicles']['id'] . ';INS_ID=' . $context['premiums'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_insurance_' . $context['premiums'][$count]['id'] . '" id="remove_insurance_' . $context['premiums'][$count]['id'] . '" style="display: inline;">
                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'] . '" />
                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_premium'] . '\')) { document.remove_insurance_' . $context['premiums'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                </form></td>';
            }

            echo '
            </tr>
            ';
            $count++;
        }
    } else {
        echo '
                <tr>
                    <td align="center">' . $txt['smfg_no_premiums'] . '</td>
                </tr>';
    }
    echo '     
        </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '  
    </div>
        <div class="garage_panel" id="options007" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_services'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    $count = 0;
    if (isset($context['services'][$count]['id'])) {
        echo '
            <tr>
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_garage'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_type'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_cost'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_rating'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $txt['smfg_mileage'] . '
                </span></h4></div></td> 
                ', ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) ? '<td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">&nbsp;</span></h4></div></td>' : '', '
            </tr>
            ';

        // Loop through each service
        while (isset($context['services'][$count]['id'])) {
            echo '<tr class="tableRow">
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=garage_review;BID=' . $context['services'][$count]['garage_id'] . '">' . garage_title_clean($context['services'][$count]['garage']) . '</a></td>
                <td align="center">' . $context['services'][$count]['type'] . '</td>
                <td align="center">' . $context['services'][$count]['price'] . '</td>
                <td align="center">' . $context['services'][$count]['rating'] . '</td>
                <td align="center">' . $context['services'][$count]['mileage'] . '</td>';

            if ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {
                echo '
                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=edit_service;VID=' . $context['user_vehicles']['id'] . ';SID=' . $context['services'][$count]['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                <form action="' . $scripturl . '?action=garage;sa=delete_service;VID=' . $context['user_vehicles']['id'] . ';SID=' . $context['services'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_service_' . $context['services'][$count]['id'] . '" id="remove_service_' . $context['services'][$count]['id'] . '" style="display: inline;">
                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'] . '" />
                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_service'] . '\')) { document.remove_service_' . $context['services'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                </form></td>';
            }

            echo '
              </tr>
            ';
            $count++;
        }
    } else {
        echo '
                <tr>
                    <td align="center">' . $txt['smfg_no_services'] . '</td>
                </tr>';
    }
    echo '   
        </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '  
    </div>
    <div class="garage_panel" id="options008" style="display: none;">';

    if ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {
        echo '
        <form action="' . $scripturl . '?action=garage;sa=insert_blog" method="post" name="add_blog" id="add_blog" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '#blog" />';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_add_blog_entry'];

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
                <td align="right" width="20%">' . $txt['smfg_blog_title'] . '</td>
                <td colspan="1"><input name="blog_title" type="text" size="60" value="" /></td>
            </tr>
            <tr>
                <td align="right" width="20%">', $smfgSettings['enable_blogs_bbcode'] ? $txt['smfg_bbc_supported'] : $txt['smfg_bbc_disabled'], '<br /><br />' . $txt['smfg_html_supported'] . '</td>
                <td><textarea name="blog_text" cols="70" rows="7" style="width: 98%"></textarea></td>
            </tr>
            <tr>
                <td align="center" height="28" colspan="5"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $context['user']['id'] . '" name="user_id" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_add_blog_entry'] . '"/></td>
            </tr>
        </table>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '
        </form>';

        echo '
        <br />';

    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo (defined('SMF_VERSION') && version_compare(SMF_VERSION, '2.1', '>=') ? '' : $txt['pages'] . ': ') . $context['blog']['page_index'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    $count = 0;
    if (isset($context['blog'][$count]['id'])) {
        while (isset($context['blog'][$count]['id'])) {
            echo '
                <tr>
                    <td colspan="2">', ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) ? '' : '';
            echo '                
                            <div class="title_bar" style="text-align: left;"><h4 class="titlebg"><span class="ie6_header">
                            ', $context['blog'][$count]['title'], '
                            </span></h4></div>';

            if ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {
                echo '
                                <div style="float:right; margin-top:2px;"><a href="' . $scripturl . '?action=garage;sa=edit_blog;VID=' . $context['user_vehicles']['id'] . ';BID=' . $context['blog'][$count]['id'] . '"><img src="' . $settings['default_images_url'] . '/garage_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=garage;sa=delete_blog;VID=' . $context['user_vehicles']['id'] . ';BID=' . $context['blog'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_blog_' . $context['blog'][$count]['id'] . '" id="remove_blog_' . $context['blog'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'] . '#blog" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_blog'] . '\')) { document.remove_blog_' . $context['blog'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>
                                </div>';
            }

            echo '
                    <span class="smalltext">' . $txt['smfg_posted'] . ': ' . date($context['date_format'],
                    $context['blog'][$count]['post_date']) . '</span>
                    <hr />' . $context['blog'][$count]['text'] . '<hr /></td>
                </tr>';
            $count++;
        }
    } else {
        echo '
            <tr>
                <td colspan="2" align="center">' . $txt['smfg_no_blogs'] . '<br /></td>
            </tr>';
    }

    echo '
        </table>';

    if ($context['user_vehicles']['user_id'] == $context['user']['id'] || allowedTo('edit_all_vehicles')) {
        echo '
        <script type="text/javascript">
        var frmvalidator = new Validator("add_blog");
        
        frmvalidator.addValidation("blog_title","req","' . $txt['smfg_val_enter_blog_title'] . '");
        frmvalidator.addValidation("blog_text","req","' . $txt['smfg_val_enter_blog_text'] . '");
        frmvalidator.addValidation("blog_text","maxlen=5000","' . $txt['smfg_val_blog_restrictions'] . '");
        </script>';
    }

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </div>';

    echo ' 
    <div class="garage_panel" id="options009" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo (defined('SMF_VERSION') && version_compare(SMF_VERSION, '2.1', '>=') ? '' : $txt['pages'] . ': ') . $context['gb']['page_index'];

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
            <table width="100%" cellspacing="1" cellpadding="3" border="0">';
    $count = 0;
    $forms = '';
    if (isset($context['gb'][$count]['comment'])) {
        echo '
                    <tr>
                        <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $txt['smfg_author'] . '
                        </span></h4></div></td>
                        <td><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $txt['smfg_message'] . '
                        </span></h4></div></td>
                    </tr>';
        while (isset($context['gb'][$count]['comment'])) {
            loadMemberData(ARRAY($context['gb'][$count]['author_id']));
            $avatarimg = "";
            if ($user_profile[$context['gb'][$count]['author_id']]['id_attach']) {
                $avatarimg = '<img src="' . $scripturl . '?action=dlattach;attach=' . $user_profile[$context['gb'][$count]['author_id']]['id_attach'] . ';type=avatar" alt="" class="avatar" border="0" /><br />';
            }
            echo '
                        <tr>
                            <td width="150" align="center" valign="middle">
                            ' . $avatarimg . '<b><a href="' . $scripturl . '?action=profile;u=' . $context['gb'][$count]['author_id'] . '">' . $context['gb'][$count]['author'] . '</a></b>
                            <table cellspacing="4" align="center" width="150">
                                <tr>
                                    <td class="smalltext"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['gb'][$count]['author_VID'] . '">' . garage_title_clean($context['gb'][$count]['author_vehicle']) . '</a></td>
                                </tr>
                            </table>
                            <table cellspacing="4" border="0">
                                <tr>
                                    <td nowrap="nowrap"><span class="smalltext"><b>' . $txt['smfg_joined'] . ':</b>&nbsp;' . date($context['date_format'],
                    $context['gb'][$count]['date_reg']) . '<br /><b>' . $txt['smfg_posts'] . ':</b>&nbsp;' . $context['gb'][$count]['posts'] . '</span>
                                    </td>
                                </tr>
                            </table>
                            </td>
                            <td valign="top">
                            <table width="100%" cellspacing="0">
                                <tr>
                                    <td class="smalltext" width="100%"><div style="float:right"><b>' . $txt['smfg_posted'] . ':</b>&nbsp;' . date($context['date_format'],
                    $context['gb'][$count]['post_date']) . '&nbsp;</div>
                                    </td>
                                </tr>
                            </table>
                            <table width="100%" cellspacing="5">
                                <tr>
                                    <td>
                                    <div class="postbody">' . $context['gb'][$count]['comment'] . '</div>
                                    <br clear="all" /><br />
                                    <table width="100%" cellspacing="0">
                                        <tr valign="middle">
                                            <td class="smalltext" align="right">';
            if ($context['user']['is_admin']) {
                echo '<img src="' . $settings['actual_images_url'] . '/ip.gif" alt="IP" title="IP" />&nbsp;<a href="' . $scripturl . '?action=trackip;searchip=' . $context['gb'][$count]['author_ip'] . '">' . $context['gb'][$count]['author_ip'] . '</a>&nbsp;<a href="' . $scripturl . '?action=helpadmin;help=see_admin_ip" onclick="return reqWin(this.href);" class="help">(?)</a>';
            }
            echo '
                                            </td>
                                        </tr>
                                    </table>
                                    </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        <tr>
                            <td nowrap="nowrap">&nbsp;</td>
                            <td><div class="smalltext" style="float: left;">&nbsp;&nbsp;</div><div class="gensmall" style="float:right">';
            // If the reader is the author, let them edit it
            if ($context['user']['id'] == $context['gb'][$count]['author_id'] | allowedTo('edit_all_comments')) {
                echo '<a href="' . $scripturl . '?action=garage;sa=edit_comment;VID=' . $context['user_vehicles']['id'] . ';CID=' . $context['gb'][$count]['CID'] . '"><img src="' . $settings['default_images_url'] . '/garage_edit.gif" alt="Edit" title="Edit" /></a>&nbsp;<a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_comment'] . '\')) { document.delete_comment_' . $context['gb'][$count]['CID'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="Delete" title="Delete" /></a>';
                $forms .= '
                                <form action="' . $scripturl . '?action=garage;sa=delete_comment;VID=' . $context['user_vehicles']['id'] . ';CID=' . $context['gb'][$count]['CID'] . ';sesc=' . $context['session_id'] . '" method="post" name="delete_comment_' . $context['gb'][$count]['CID'] . '" id="delete_comment_' . $context['gb'][$count]['CID'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'] . '#guestbook" />
                                </form>';
            }
            echo '</div></td>
                        </tr>
                        <tr>
                            <td colspan="2">';

            if ($count < ($context['gb']['total'] - 1)) {
                echo '
                                <hr />';
            }

            echo '
                            </td>
                        </tr>';
            $count++;
        }
    } else {
        echo '
                   <tr>
                        <td colspan="2" align="center">' . $txt['smfg_no_comments'] . '<br /></td>
                   </tr>';
    }

    echo '
            </table>';

    echo ' 
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '<br />';

    if ($context['user']['is_logged'] && allowedTo('post_comments')) {

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_add_comment'];

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
        <form action="' . $scripturl . '?action=garage;sa=insert_comment" method="post" name="add_comment" id="add_comment" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['user_vehicles']['id'] . '#guestbook" />
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

        echo '
            <tr>
                <td align="right" width="20%">', $smfgSettings['enable_guestbooks_bbcode'] ? $txt['smfg_bbc_supported'] : $txt['smfg_bbc_disabled'], '<br /><br />' . $txt['smfg_html_supported'] . '</td>
                <td><textarea name="post" cols="70" rows="7" style="width: 98%"></textarea></td>
            </tr>
            <tr>
                <td align="center" height="28" colspan="5"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $context['user']['id'] . '" name="user_id" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_post_comment'] . '" /></td>
            </tr>';

        echo '
            </table>
            </form>';

        echo $forms;

        echo ' 
            <script type="text/javascript">
            var frmvalidator = new Validator("add_comment");
            frmvalidator.addValidation("post","req","Please enter a comment.");
            frmvalidator.addValidation("post","maxlen=2500","Max length for comments is 2500 characters.");
            </script>';

        echo ' 
            </div>
            </div>
            <span class="lowerframe"><span></span></span>';

    }

    echo '   
</div>

<script type="text/javascript">
<!--
    var lowest_tab = \'000\';
    var active_id = \'000\';
    if (document.location.hash == "")
    {
        change_tab(lowest_tab);
    }
    else if (document.location.hash == "#images")
    {
        change_tab(\'000\');
    }
    else if (document.location.hash == "#videos")
    {
        change_tab(\'001\');
    }
    else if (document.location.hash == "#modifications")
    {
        change_tab(\'002\');
    }
    else if (document.location.hash == "#quartermiles")
    {
        change_tab(\'003\');
    }
    else if (document.location.hash == "#dynoruns")
    {
        change_tab(\'004\');
    }
    else if (document.location.hash == "#laps")
    {
        change_tab(\'005\');
    }
    else if (document.location.hash == "#premiums")
    {
        change_tab(\'006\');
    }
    else if (document.location.hash == "#services")
    {
        change_tab(\'007\');
    }
    else if (document.location.hash == "#blog")
    {
        change_tab(\'008\');
    }
    else if (document.location.hash == "#guestbook")
    {
        change_tab(\'009\');
    }

//-->

</script>';

    echo smfg_footer();

}

function template_add_vehicle()
{
    global $context, $settings, $options, $txt, $scripturl, $db_prefix, $smfgSettings;

    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    // List Model Options
    echo model_options('add_vehicle');

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_create_vehicle'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '
    <form action="' . $scripturl . '?action=garage;sa=insert_vehicle" id="add_vehicle" enctype="multipart/form-data" method="post" name="add_vehicle" style="padding:0; margin:0;">
    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID={VID}" />';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
<table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Make?
    if ($_SESSION['added_make']) {
        echo '
        <tr>
            <td class="windowbg_pending" colspan="2" align="center" valign="middle">' . $txt['smfg_make_added'] . '</td>
        </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_make']);
    }

    // Model?
    if ($_SESSION['added_model']) {
        echo '
        <tr>
            <td class="windowbg_pending" colspan="2" align="center" valign="middle">' . $txt['smfg_model_added'] . '</td>
        </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_model']);
    }

    echo '
    <tr>
        <td width="20%" align="right"><b>' . $txt['smfg_year'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
        <td><select id="made_year" name="made_year">
        ';
// List the Year Range Options
    echo year_options($smfgSettings['year_start'], $smfgSettings['year_end']);

    echo '</select>
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_make'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
        <td ><select id="make_id" name="make_id">
                             <option value="">' . $txt['smfg_select_make1'] . '</option>
                             <option value="">------</option>';
    // List Make Selections
    echo make_select();
    echo '</select>';
    if ($smfgSettings['enable_user_submit_make']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_make" rel="shadowbox;width=500;height=165" title="Garage :: Submit Make">' . $txt['smfg_here'] . '</a>';
    }
    echo '                             
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_model'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
        <td><select id="model_id" name="model_id">
                             <script type="text/javascript">dol.printOptions("model_id")</script>
                             </select>';
    if ($smfgSettings['enable_user_submit_model']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_model" rel="shadowbox;width=620;height=200" title="Garage :: Submit Model">' . $txt['smfg_here'] . '</a>';
    }
    echo '
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_engine_type'] . '</b></td>
        <td><select id="engine_type" name="engine_type">
                             <option value="">' . $txt['smfg_select_engine_type'] . '</option>
                             <option value="">------</option>';
    echo engine_type_select();
    echo '
                             </select>
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_color'] . '</b></td>
        <td><input name="color" type="text" size="20" value="" /></td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_mileage'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
        <td><input name="mileage" type="text" size="15" value="" />&nbsp;
                             <select id="mileage_units" name="mileage_units">
                             <option value="">' . $txt['smfg_select_mileage_type'] . '</option>
                             <option value="">------</option>
                             <option value="Miles" >' . $txt['smfg_miles'] . '</option>
                             <option value="Kilometers" >' . $txt['smfg_kilometers'] . '</option>
                             </select>
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_purchased_price'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
        <td><input name="price" type="text" size="10" value="" />&nbsp;' . $txt['smfg_currency'] . ':&nbsp;
                             <select id="currency" name="currency">
                             <option value="">' . $txt['smfg_select_currency'] . '</option>
                             <option value="">------</option>';
    echo currency_select();
    echo '
                             </select>
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
        <td><textarea name="comments" cols="60" rows="5"></textarea></td>
    </tr>';

    echo '
    </table>';

    // Show the input for images if it is enabled
    if ($smfgSettings['enable_vehicle_images']) {

        echo '
        <br />';

        echo '
        <table width="100%">
            <tr>
                <td>
                    <div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_image_attachments'] . '
                    </span></h4></div>
                </td>
            </tr>
        </table>';

        echo '
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
        <tr>
            <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_attach_image'] . '.<br />' . $txt['smfg_max_filesize'] . ': ' . $smfgSettings['max_image_kbytes'] . ' ' . $txt['smfg_kbytes'] . '<br />' . $txt['smfg_max_resolution'] . ': ' . $smfgSettings['max_image_resolution'] . 'x' . $smfgSettings['max_image_resolution'] . '</b></td>
            <td><input type="hidden" name="MAX_FILE_SIZE" value="' . $context['max_image_bytes'] . '" /><input type="file" size="30" name="FILE_UPLOAD" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,.jpg,.jpeg,.png,.gif,.webp,.bmp"/></td>
        </tr>';

        // Show the input for remote images if it is enabled
        if ($smfgSettings['enable_remote_images']) {
            echo '    
            <tr>
                <td width="32%" align="right"><b>' . $txt['smfg_enter_remote_url'] . '</b></td>
                <td><input name="url_image" type="text" size="40" maxlength="255" value="https://" /></td>
            </tr>';
        }

        echo '    
        <tr>
            <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
            <td><textarea name="attach_desc" cols="60" rows="3"></textarea></td>
        </tr>
        </table>';

    }

    // Show the input for videos if it is enabled
    if ($smfgSettings['enable_vehicle_video']) {

        echo '
        <br />';

        echo '
        <table width="100%">
            <tr>
                <td>
                    <div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_hosted_videos'] . '
                    </span></h4></div>
                </td>
            </tr>
        </table>';


        echo '
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">        
        <tr>
            <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_title'] . '</b></td>
            <td><input type="text"  size="40" maxlength="75" value="" name="video_title"/></td>
        </tr>
        <tr>
            <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_url'] . '</b></td>
            <td><input type="text"  size="40" maxlength="255" value="https://" name="video_url"/>&nbsp;<span class="smalltext"><a href="' . $scripturl . '?action=garage;sa=supported_video" rel="shadowbox;width=260;height=400" title="' . $txt['smfg_video_instructions'] . '">Supported Sites</a></span></td>
        </tr>';

        echo '    
        <tr>
            <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
            <td><textarea name="video_desc" cols="60" rows="3"></textarea></td>
        </tr>
        </table>';

    }

    echo '    
    <table width="100%">
    <tr>
        <td align="center"><input type="hidden" value="" name="VID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="' . $txt['smfg_create_vehicle'] . '" type="submit" value="' . $txt['smfg_create_vehicle'] . '" /></td>
    </tr>
    </table>
    </form>
<script type="text/javascript">
 var frmvalidator = new Validator("add_vehicle");
 var frm = document.forms["add_vehicle"];
 
 frmvalidator.addValidation("made_year","req","' . $txt['smfg_val_select_year'] . '");
 frmvalidator.addValidation("made_year","dontselect=0","' . $txt['smfg_val_select_year'] . '");
 
 frmvalidator.addValidation("make_id","req","' . $txt['smfg_val_select_make'] . '");
 frmvalidator.addValidation("make_id","dontselect=0","' . $txt['smfg_val_select_make'] . '");
 frmvalidator.addValidation("make_id","dontselect=1","' . $txt['smfg_val_select_make'] . '");
 
 frmvalidator.addValidation("model_id","req","' . $txt['smfg_val_select_model'] . '");
 
 frmvalidator.addValidation("color","regexp=^[ /A-Za-z1-9]{1,20}$","' . $txt['smfg_val_color_requirements'] . '");
 
 frmvalidator.addValidation("mileage","req","' . $txt['smfg_val_enter_mileage'] . '");
 frmvalidator.addValidation("mileage","numeric","' . $txt['smfg_val_mileage_numeric'] . '");
 
 frmvalidator.addValidation("mileage_units","req","' . $txt['smfg_val_select_mileage_unit'] . '");
 frmvalidator.addValidation("mileage_units","dontselect=0","' . $txt['smfg_val_select_mileage_unit'] . '");
  
 frmvalidator.addValidation("price","req","' . $txt['smfg_val_enter_purchased_price'] . '");
 frmvalidator.addValidation("price","regexp=^[.0-9]{1,10}$","' . $txt['smfg_val_purchased_price_numeric_vehicle'] . '");

 frmvalidator.addValidation("currency","req","' . $txt['smfg_val_select_currency'] . '");
 frmvalidator.addValidation("currency","dontselect=0","' . $txt['smfg_val_select_currency'] . '");
  
 frmvalidator.addValidation("comments","maxlen=500","' . $txt['smfg_val_description_length'] . '");';
    if ($smfgSettings['enable_vehicle_images']) {
        echo '
        frmvalidator.addValidation("attach_desc","maxlen=150","' . $txt['smfg_val_image_description_length'] . '");';
    }
    if ($smfgSettings['enable_vehicle_video']) {
        echo '
        frmvalidator.addValidation("video_desc","maxlen=150","' . $txt['smfg_val_video_description_length'] . '");';
    }
    echo '
</script>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_edit_vehicle()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings, $boardurl;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_edit_vehicle'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '
    <table cellpadding="0" cellspacing="0" border="0" style="margin: 7px 0 5px 10px" id="tab_table">
        <tr id="tab_row">
            <td>               
            <ul class="dropmenu">
                <li id="button_g_vehicle">
                    <a class="firstlevel" href="#vehicle" onclick="change_tab(\'000\');" id="tab000">
                        <span class="firstlevel">' . $txt['smfg_vehicle'] . '</span>
                    </a>
                </li>';

    if ($smfgSettings['enable_vehicle_images']) {

        echo '
                    <li id="button_g_images">
                        <a class="firstlevel" href="#images" onclick="change_tab(\'001\');" id="tab001">
                            <span class="firstlevel">' . $txt['smfg_images'] . '</span>
                        </a>
                    </li>';

    }

    if ($smfgSettings['enable_vehicle_video']) {

        echo '
                    <li id="button_g_videos">
                        <a class="firstlevel" href="#videos" onclick="change_tab(\'002\');" id="tab002">
                            <span class="firstlevel">' . $txt['smfg_videos'] . '</span>
                        </a>
                    </li>';

    }

    echo '
            </ul>
            </td>
        </tr>
    </table>';

    // Begin dynamic js divs
    echo '        
        <div class="garage_panel" id="options000" style="display: none;">';

    echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $txt['smfg_vehicle'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Make?
    if ($_SESSION['added_make']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_make_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_make']);
    }

    // Model?
    if ($_SESSION['added_model']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_model_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_model']);
    }

    echo '
            <tr>
                <td>';

    // List Model Options
    echo model_options("update_vehicle");
    echo '<script type="text/javascript">dol.forField("model_id").setValues(' . $context['user_vehicles']['model_id'] . ');</script>';

    echo '

    <form action="' . $scripturl . '?action=garage;sa=update_vehicle" id="update_vehicle" enctype="multipart/form-data" method="post" name="update_vehicle" style="padding:0; margin:0;">
    <table border="0" cellpadding="3" cellspacing="1" width="100%">';

    echo '
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_year'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
        <td><select id="made_year" name="made_year">
        ';
    // List the Year Range Options
    echo year_options($smfgSettings['year_start'], $smfgSettings['year_end'], $context['user_vehicles']['made_year']);

    echo '</select>
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_make'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
        <td><select id="make_id" name="make_id">';
    // List Make Selections
    echo make_select($context['user_vehicles']['make_id']);
    echo '</select>';
    if ($smfgSettings['enable_user_submit_make']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_make" rel="shadowbox;width=620;height=165" title="Garage :: Submit Make">' . $txt['smfg_here'] . '</a>';
    }
    echo '
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_model'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
        <td><select id="model_id" name="model_id">
                             <script type="text/javascript">dol.printOptions("model_id")</script>
                             </select>';
    if ($smfgSettings['enable_user_submit_model']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_model" rel="shadowbox;width=620;height=200" title="Garage :: Submit Model">' . $txt['smfg_here'] . '</a>';
    }
    echo '                             
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_engine_type'] . '</b></td>
        <td><select id="engine_type" name="engine_type">
        <option value="">' . $txt['smfg_select_engine_type'] . '</option>
        <option value="">------</option>
        ';
    echo engine_type_select($context['user_vehicles']['engine_type']);
    echo '
        </select>
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_color'] . '</b></td>
        <td><input name="color" type="text" size="20" value="' . $context['user_vehicles']['color'] . '" /></td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_mileage'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
        <td><input name="mileage" type="text" size="15" value="' . $context['user_vehicles']['mileage'] . '" />&nbsp;
                             <select id="mileage_unit" name="mileage_unit">
                             <option value="">' . $txt['smfg_select_mileage_type'] . '</option>
                             <option value="">------</option>
                             <option value="Miles"' . $context['miles'] . '>' . $txt['smfg_miles'] . '</option>
                             <option value="Kilometers"' . $context['kilometers'] . '>' . $txt['smfg_kilometers'] . '</option>
        </select>
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_purchased_price'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
        <td>
        <input name="price" type="text" size="10" value="' . $context['user_vehicles']['price'] . '" />&nbsp;' . $txt['smfg_currency'] . ':&nbsp;
        <select id="currency" name="currency">
        <option value="">' . $txt['smfg_select_currency'] . '</option>
        <option value="">------</option>
        ';
    echo currency_select($context['user_vehicles']['currency']);
    echo '
        </select>
        </td>
    </tr>
    
    <tr>
        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
        <td><textarea name="comments" cols="60" rows="5">' . $context['user_vehicles']['comments'] . '</textarea></td>
    </tr>
    
    <tr>
        <td colspan="2" align="center" height="28">
            <input type="hidden" value="' . $context['user_vehicles']['id'] . '" name="VID" />
            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_vehicle;VID=' . $_GET['VID'] . '#vehicle" />
            <input type="hidden" name="sc" value="', $context['session_id'], '" />
            <input name="edit_vehicle" type="submit" value="' . $txt['smfg_update_vehicle'] . '" />
        </td>
    </tr>
</table>
</form>';
    echo '
                </td>
            </tr>    
        </table>
        <script type="text/javascript">
         var frmvalidator = new Validator("update_vehicle");
         var frm = document.forms["update_vehicle"];
         
         frmvalidator.addValidation("made_year","req","' . $txt['smfg_val_select_year'] . '");
         frmvalidator.addValidation("made_year","dontselect=0","' . $txt['smfg_val_select_year'] . '");
         
         frmvalidator.addValidation("make_id","req","' . $txt['smfg_val_select_make'] . '");
         
         frmvalidator.addValidation("model_id","req","' . $txt['smfg_val_select_model'] . '");
         
         frmvalidator.addValidation("color","regexp=^[ /A-Za-z1-9]{1,20}$","' . $txt['smfg_val_color_requirements'] . '");
         
         frmvalidator.addValidation("mileage","req","' . $txt['smfg_val_enter_mileage'] . '");
         frmvalidator.addValidation("mileage","numeric","' . $txt['smfg_val_mileage_numeric'] . '");
         
         frmvalidator.addValidation("mileage_unit","req","' . $txt['smfg_val_select_mileage_unit'] . '");
         frmvalidator.addValidation("mileage_unit","dontselect=0","' . $txt['smfg_val_select_mileage_unit'] . '");
          
         frmvalidator.addValidation("price","req","' . $txt['smfg_val_enter_purchased_price'] . '");
         frmvalidator.addValidation("price","regexp=^[.0-9]{1,10}$","' . $txt['smfg_val_purchased_price_numeric_vehicle'] . '");

         frmvalidator.addValidation("currency","req","' . $txt['smfg_val_select_currency'] . '");
         frmvalidator.addValidation("currency","dontselect=0","' . $txt['smfg_val_select_currency'] . '");
          
         frmvalidator.addValidation("comments","maxlen=500","' . $txt['smfg_val_description_length'] . '");
         
        </script>';

    echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

    echo '
        </div>';

    // Show the input for images if it is enabled
    if ($smfgSettings['enable_vehicle_images']) {
        echo '
            <div class="garage_panel" id="options001" style="display: none;">';

        echo '
            <table class="table_list" cellspacing="0" cellpadding="0">
                <tbody class="header">
                    <tr>
                        <td>
                            <div class="cat_bar">
                                <h3 class="catbg">';

        echo $txt['smfg_images'];

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
                                        
                    <form action="' . $scripturl . '?action=garage;sa=insert_vehicle_images" id="update_images" enctype="multipart/form-data" method="post" name="update_images" style="padding:0; margin:0;">         
                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_vehicle;VID=' . $_GET['VID'] . '#images" />
                    <table border="0" cellpadding="3" cellspacing="1" width="100%">  
                        <tr>
                            <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_attach_image'] . '.<br />' . $txt['smfg_max_filesize'] . ': ' . $smfgSettings['max_image_kbytes'] . ' ' . $txt['smfg_kbytes'] . '<br />' . $txt['smfg_max_resolution'] . ': ' . $smfgSettings['max_image_resolution'] . 'x' . $smfgSettings['max_image_resolution'] . '</b></td>
                            <td><input type="hidden" name="MAX_FILE_SIZE" value="' . $context['max_image_bytes'] . '" /><input type="file" size="30" name="FILE_UPLOAD" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,.jpg,.jpeg,.png,.gif,.webp,.bmp"/></td>
                        </tr>';

        // Show the input for remote images if it is enabled
        if ($smfgSettings['enable_remote_images']) {
            echo '    
                        <tr>
                            <td width="32%" align="right"><b>' . $txt['smfg_enter_remote_url'] . '</b></td>
                            <td><input name="url_image" type="text" size="40" maxlength="255" value="https://" /></td>
                        </tr>';
        }

        echo '    
                        <tr>
                            <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                            <td><textarea name="attach_desc" cols="60" rows="3"></textarea></td>
                        </tr>    
                        <tr>
                            <td colspan="2" align="center" height="28"><input type="hidden" value="' . $context['user_vehicles']['id'] . '" name="VID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="insert_vehicle_images" type="submit" value="' . $txt['smfg_add_new_image'] . '" /></td>
                        </tr>
                    </table>
                    </form>
                    <table border="0" cellpadding="3" cellspacing="1" width="100%">  
                        <tr>
                            <td colspan="2">
                            <table border="0" cellpadding="3" cellspacing="1" width="100%">
                                <tr>
                                    <td width="100%" align="center" colspan="3">' . $txt['smfg_edit_in_place_instructions'] . '<div id="updateStatus"></div></td>
                                </tr>
                                <tr>
                                    <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                    ' . $txt['smfg_image'] . '
                                    </span></h4></div></td>
                                    <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                    ' . $txt['smfg_description'] . '
                                    </span></h4></div></td>
                                    <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                    ' . $txt['smfg_manage'] . '
                                    </span></h4></div></td>
                                </tr>';

        $count = 0;
        // If there is an image, show em
        if (isset($context['user_vehicles'][$count]['image_id'])) {
            // and keep showing em
            while (isset($context['user_vehicles'][$count]['image_id'])) {
                echo '                            
                                        <tr class="tableRow">
                                            <td align="center" valign="middle"><a href="' . $context['user_vehicles'][$count]['attach_location'] . '" rel="shadowbox" title="' . garage_title_clean($context['user_vehicles']['made_year'] . ' ' . $context['user_vehicles']['make'] . ' ' . $context['user_vehicles']['model'] . ' :: ' . $context['user_vehicles'][$count]['attach_desc']) . '" class="smfg_imageTitle"><img src="' . $boardurl . '/' . $smfgSettings['upload_directory'] . 'cache/' . $context['user_vehicles'][$count]['attach_thumb_location'] . '" width="' . $context['user_vehicles'][$count]['attach_thumb_width'] . '" height="' . $context['user_vehicles'][$count]['attach_thumb_height'] . '" alt=""/></a></td>
                                            <td align="center" valign="middle">
                                            <div id="image' . $context['user_vehicles'][$count]['image_id'] . '" class="editin">';
                echo $context['user_vehicles'][$count]['attach_desc'];
                echo '</div></td>
                                            <td align="center" valign="middle">';
                if ($context['user_vehicles'][$count]['hilite'] != 1) {
                    echo '
                                                <form action="' . $scripturl . '?action=garage;sa=set_hilite_image;VID=' . $context['user_vehicles']['id'] . ';image_id=' . $context['user_vehicles'][$count]['image_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="set_vehicle_hilite_' . $context['user_vehicles'][$count]['image_id'] . '" id="set_vehicle_hilite_' . $context['user_vehicles'][$count]['image_id'] . '" style="display: inline;">
                                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_vehicle;VID=' . $_GET['VID'] . '#images" />
                                                    <a href="#" onClick="document.set_vehicle_hilite_' . $context['user_vehicles'][$count]['image_id'] . '.submit(); return false;">' . $txt['smfg_set_hilite_image'] . '</a>
                                                </form>
                                                <br /><br />';
                } else {
                    echo
                        $txt['smfg_hilite_image'] . '<br /><br />';
                }
                echo '
                                            <form action="' . $scripturl . '?action=garage;sa=remove_vehicle_image;VID=' . $context['user_vehicles']['id'] . ';image_id=' . $context['user_vehicles'][$count]['image_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_vehicle_image_' . $context['user_vehicles'][$count]['image_id'] . '" id="remove_vehicle_image_' . $context['user_vehicles'][$count]['image_id'] . '" style="display: inline;">
                                            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_vehicle;VID=' . $_GET['VID'] . '#images" />
                                            <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_image'] . '\')) { document.remove_vehicle_image_' . $context['user_vehicles'][$count]['image_id'] . '.submit(); } else { return false; } return false;">' . $txt['smfg_remove_image'] . '</a>
                                            </form>
                                            </td>
                                        </tr>';
                $count++;
            }
        } else {
            echo '
                                    <tr>
                                        <td colspan="3" align="center" valign="middle">' . $txt['smfg_no_images'] . '</td>
                                    </tr>';
        }
        echo '
                                </table> 
                            </td>
                        </tr>
                    </table>  
                    </td>
                </tr>
            </table>
            <script type="text/javascript">
             var frmvalidator = new Validator("update_images");
              
             frmvalidator.addValidation("attach_desc","maxlen=150","' . $txt['smfg_val_image_description_length'] . '");  
             
            </script>';

        echo '  
            </div>
            </div>
            <span class="lowerframe"><span></span></span>';

        echo '
            </div>';
    }

    // Show the input for videos if it is enabled
    if ($smfgSettings['enable_vehicle_video']) {

        echo '
            <div class="garage_panel" id="options002" style="display: none;">';

        echo '
            <table class="table_list" cellspacing="0" cellpadding="0">
                <tbody class="header">
                    <tr>
                        <td>
                            <div class="cat_bar">
                                <h3 class="catbg">';

        echo $txt['smfg_videos'];

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
                                        
                    <form action="' . $scripturl . '?action=garage;sa=insert_vehicle_video" id="update_video" enctype="multipart/form-data" method="post" name="update_video" style="padding:0; margin:0;">         
                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_vehicle;VID=' . $_GET['VID'] . '#video" />
                    <table border="0" cellpadding="3" cellspacing="1" width="100%">  
                        <tr>
                            <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_title'] . '</b></td>
                            <td><input type="text"  size="40" maxlength="75" value="" name="video_title"/></td>
                        </tr>
                        <tr>
                            <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_url'] . '</b></td>
                            <td><input type="text"  size="40" maxlength="255" value="https://" name="video_url"/>&nbsp;<span class="smalltext"><a href="' . $scripturl . '?action=garage;sa=supported_video" rel="shadowbox;width=260;height=400" title="' . $txt['smfg_video_instructions'] . '">Supported Sites</a></span></td>
                        </tr>';

        echo '    
                        <tr>
                            <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                            <td><textarea name="video_desc" cols="60" rows="3"></textarea></td>
                        </tr>   
                        <tr>
                            <td colspan="2" align="center" height="28"><input type="hidden" value="' . $context['user_vehicles']['id'] . '" name="VID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="insert_vehicle_video" type="submit" value="' . $txt['smfg_add_new_video'] . '" /></td>
                        </tr>
                    </table>
                    </form>
                    <table border="0" cellpadding="3" cellspacing="1" width="100%">
                        <tr>
                            <td colspan="2">
                            <table border="0" cellpadding="3" cellspacing="1" width="100%">
                                <tr>
                                    <td width="100%" align="center" colspan="3">' . $txt['smfg_edit_in_place_instructions'] . '<div id="updateStatus2"></div></td>
                                </tr>
                                <tr>
                                    <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                    ' . $txt['smfg_video'] . '
                                    </span></h4></div></td>
                                    <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                    ' . $txt['smfg_title_description'] . '
                                    </span></h4></div></td>
                                    <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                    ' . $txt['smfg_manage'] . '
                                    </span></h4></div></td>
                                </tr>';

        $count = 0;
        // If there is an video, show em
        if (isset($context['user_vehicles'][$count]['video_id'])) {
            // and keep showing em
            while (isset($context['user_vehicles'][$count]['video_id'])) {
                echo '                            
                                        <tr class="tableRow">
                                            <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=video;id=' . $context['user_vehicles'][$count]['video_id'] . '" rel="shadowbox;width=' . $context['user_vehicles'][$count]['video_width'] . ';height=' . $context['user_vehicles'][$count]['video_height'] . ';" title="' . garage_title_clean('<b>' . $context['user_vehicles'][$count]['video_title'] . '</b> :: ' . $context['user_vehicles'][$count]['video_desc']) . '" class="smfg_videoTitle"><img src="' . $context['user_vehicles'][$count]['video_thumb'] . '" /></a></td>
                                            <td align="center" valign="middle">
                                            <div id="video_title' . $context['user_vehicles'][$count]['video_id'] . '" class="editin" style="font-weight: bold;">';
                // If there is no title, let them add one
                if (!empty($context['user_vehicles'][$count]['video_title'])) {
                    echo $context['user_vehicles'][$count]['video_title'];
                }
                echo '</div>
                                            <br />
                                            <div id="video' . $context['user_vehicles'][$count]['video_id'] . '" class="editin">';
                // If there is no desc, let them add one
                if (!empty($context['user_vehicles'][$count]['video_desc'])) {
                    echo $context['user_vehicles'][$count]['video_desc'];
                }
                echo '</div></td>
                                            <td align="center" valign="middle">';
                echo '
                                            <form action="' . $scripturl . '?action=garage;sa=remove_video;VID=' . $context['user_vehicles']['id'] . ';video_id=' . $context['user_vehicles'][$count]['video_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_vehicle_video_' . $context['user_vehicles'][$count]['video_id'] . '" id="remove_vehicle_video_' . $context['user_vehicles'][$count]['video_id'] . '" style="display: inline;">
                                            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_vehicle;VID=' . $_GET['VID'] . '#video" />
                                            <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_video'] . '\')) { document.remove_vehicle_video_' . $context['user_vehicles'][$count]['video_id'] . '.submit(); } else { return false; } return false;">' . $txt['smfg_remove_video'] . '</a>
                                            </form>
                                            </td>
                                        </tr>';
                $count++;
            }
        } else {
            echo '
                                    <tr>
                                        <td colspan="3" align="center" valign="middle">' . $txt['smfg_no_videos'] . '</td>
                                    </tr>';
        }
        echo '
                                </table> 
                            </td>
                        </tr>
                    </table>  
                    </td>
                </tr>
            </table>
            <script type="text/javascript">
             var frmvalidator = new Validator("update_video");
              
             frmvalidator.addValidation("video_desc","maxlen=150","' . $txt['smfg_val_video_description_length'] . '");  
             frmvalidator.addValidation("video_title","req","' . $txt['smfg_val_enter_title'] . '");
             
            </script>';

        echo '  
            </div>
            </div>
            <span class="lowerframe"><span></span></span>';

        echo '
            </div>';
    }

    echo '

<script type="text/javascript">
<!--
    var lowest_tab = \'000\';
    var active_id = \'000\';
    if (document.location.hash == "")
    {
        change_tab(lowest_tab);
    }
    else if (document.location.hash == "#vehicle")
    {
        change_tab(\'000\');
    }
    else if (document.location.hash == "#images")
    {
        change_tab(\'001\');
    }
    else if (document.location.hash == "#videos")
    {
        change_tab(\'002\');
    }

//-->

</script>';

    echo smfg_footer();

}

function template_add_modification()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    // List the product options
    echo product_options("add_modification");

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_add_modification'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '
    <form action="' . $scripturl . '?action=garage;sa=insert_modification" id="add_modification" enctype="multipart/form-data" method="post" name="add_modification" style="padding:0; margin:0;">
    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '#modifications" />';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Submitted items?
    if (!$_SESSION['added_man'] && !$_SESSION['added_product'] && !$_SESSION['added_shop'] && !$_SESSION['added_garage']) {
        echo '
            <tr>
                <td align="center" valign="middle" colspan="2">' . $txt['smfg_add_mod_info'] . '</td>
            </tr>';
    }

    // Manufactuer?
    if ($_SESSION['added_man']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle" colspan="2">' . $txt['smfg_manufacturer_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_man']);
    }

    // Product?
    if ($_SESSION['added_product']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle" colspan="2">' . $txt['smfg_product_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_product']);
    }

    // Shop?
    if ($_SESSION['added_shop']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle" colspan="2">' . $txt['smfg_shop_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_shop']);
    }

    // Garage?
    if ($_SESSION['added_garage']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle" colspan="2">' . $txt['smfg_garage_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_garage']);
    }

    echo '
        <tr>
            <td width="30%" align="right"><b>' . $txt['smfg_category'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
            <td>
            <select id="category_id" name="category_id">
            <option value="">' . $txt['smfg_select_category'] . '</option>
            <option value="">------</option>';
    // List Mod Category Selections
    echo cat_select();
    echo '
            </select>
            &nbsp;
            </td>
        </tr>
        <tr>
            <td width="30%" align="right"><b>' . $txt['smfg_manufacturer'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
            <td>
            <select id="manufacturer_id" name="manufacturer_id">
            <script type="text/javascript">dol2.printOptions("manufacturer_id")</script>
            </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=product" rel="shadowbox;width=620;height=560" title="Garage :: Submit Business">' . $txt['smfg_here'] . '</a>';
    }
    echo '
            </td>
        </tr>
        <tr>
            <td width="30%" align="right"><b>' . $txt['smfg_product'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
            <td>
            <select id="product_id" name="product_id">
            <script type="text/javascript">dol2.printOptions("product_id")</script>
            </select>';
    if ($smfgSettings['enable_user_submit_product']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_product" rel="shadowbox;width=620;height=200" title="Garage :: Submit Product">' . $txt['smfg_here'] . '</a>';
    }
    echo '&nbsp;&nbsp;&nbsp;
            <b>' . $txt['smfg_rating'] . '</b>&nbsp;
            <select id="product_rating" name="product_rating">
            <option value="">' . $txt['smfg_select_rating'] . '</option>
            <option value="">------</option>
            <option value="10" >10 ' . $txt['smfg_best'] . '</option>
            <option value="9" >9</option>
            <option value="8" >8</option>
            <option value="7" >7</option>
            <option value="6" >6</option>
            <option value="5" >5</option>
            <option value="4" >4</option>
            <option value="3" >3</option>
            <option value="2" >2</option>
            <option value="1" >1 ' . $txt['smfg_worst'] . '</option>
            </select>
            </td>
        </tr>
        <tr>
            <td width="30%" align="right"><b>' . $txt['smfg_purchased_from'] . '</b></td>
            <td>
            <select id="shop_id" name="shop_id">
            <option value="">' . $txt['smfg_select_shop'] . '</option>
            <option value="">------</option>';
    // List Shop Options
    echo shop_select();
    echo '
            </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=retail" rel="shadowbox;width=620;height=560" title="Garage :: Submit Retail">' . $txt['smfg_here'] . '</a>';
    }
    echo '
            </td>
        </tr>
        <tr>
            <td width="30%" align="right"><b>' . $txt['smfg_purchased_price'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
            <td>
            <input name="price" type="text" size="10" value="" />&nbsp;<b>' . $txt['smfg_purchase_rating'] . '</b>&nbsp;
            <select id="purchase_rating" name="purchase_rating">
            <option value="">' . $txt['smfg_select_rating'] . '</option>
            <option value="">------</option>
            <option value="10" >10 ' . $txt['smfg_cheapest'] . '</option>
            <option value="9" >9</option>
            <option value="8" >8</option>
            <option value="7" >7</option>
            <option value="6" >6</option>
            <option value="5" >5</option>
            <option value="4" >4</option>
            <option value="3" >3</option>
            <option value="2" >2</option>
            <option value="1" >1 ' . $txt['smfg_most_expensive'] . '</option>
            </select>
            </td>
        </tr>    
        <tr>
            <td width="30%" align="right"><b>' . $txt['smfg_installed_by'] . '</b></td>
            <td>
            <select id="installer_id" name="installer_id">
            <option value="">' . $txt['smfg_select_garage'] . '</option>
            <option value="">------</option>';
    // List Installer/Garage Options
    echo install_select();
    echo '
            </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=garage" rel="shadowbox;width=620;height=560" title="Garage :: Submit Garage">' . $txt['smfg_here'] . '</a>';
    }
    echo '
        </tr>
        <tr>
            <td width="30%" align="right"><b>' . $txt['smfg_installation_price'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
            <td>
            <input name="install_price" type="text" size="10" value="" />&nbsp;<b>' . $txt['smfg_installation_rating'] . '</b>&nbsp;
            <select id="install_rating" name="install_rating">
            <option value="">' . $txt['smfg_select_rating'] . '</option>
            <option value="">------</option>
            <option value="10" >10 ' . $txt['smfg_cheapest'] . '</option>
            <option value="9" >9</option>
            <option value="8" >8</option>
            <option value="7" >7</option>
            <option value="6" >6</option>
            <option value="5" >5</option>
            <option value="4" >4</option>
            <option value="3" >3</option>
            <option value="2" >2</option>
            <option value="1" >1 ' . $txt['smfg_most_expensive'] . '</option>
            </select>
            </td>
        </tr>
        <tr>
            <td width="30%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
            <td><textarea name="comments" cols="60" rows="4"></textarea></td>
        </tr>
        <tr>
            <td width="30%" align="right"><b>' . $txt['smfg_install_comments'] . '</b><br/>' . $txt['smfg_only_show_in'] . '</td>

            <td><textarea name="install_comments" cols="60" rows="4"></textarea></td>
        </tr>';

    // Show the input for remote images if it is enabled
    if ($smfgSettings['enable_modification_images']) {
        echo '
        <tr>
            <td colspan="2"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_image_attachments'] . '
            </span></h4></div></td>
        </tr>
        <tr>
            <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_attach_image'] . '<br />' . $txt['smfg_max_filesize'] . ': ' . $smfgSettings['max_image_kbytes'] . ' ' . $txt['smfg_kbytes'] . '<br />' . $txt['smfg_max_resolution'] . ': ' . $smfgSettings['max_image_resolution'] . 'x' . $smfgSettings['max_image_resolution'] . '</b></td>
            <td><input type="hidden" name="MAX_FILE_SIZE" value="' . $context['max_image_bytes'] . '" /><input type="file" size="30" name="FILE_UPLOAD" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,.jpg,.jpeg,.png,.gif,.webp,.bmp"/></td>
        </tr>';

        // Show the input for remote images if it is enabled
        if ($smfgSettings['enable_remote_images']) {
            echo '    
        <tr>
            <td width="32%" align="right"><b>' . $txt['smfg_enter_remote_url'] . '</b></td>
            <td><input name="url_image" type="text" size="40" maxlength="255" value="https://" /></td>
        </tr>';
        }

        echo '    
        <tr>
            <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
            <td><textarea name="attach_desc" cols="60" rows="3"></textarea></td>
        </tr>';
    }
    // Show the input for videos if it is enabled
    if ($smfgSettings['enable_modification_video']) {
        echo '
        <tr>
            <td colspan="2"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_hosted_videos'] . '
            </span></h4></div></td>
        </tr>
        
        <tr>
            <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_title'] . '</b></td>
            <td><input type="text"  size="40" maxlength="75" value="" name="video_title"/></td>
        </tr>
        <tr>
            <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_url'] . '</b></td>
            <td ><input type="text"  size="40" maxlength="255" value="https://" name="video_url"/>&nbsp;<span class="smalltext"><a href="' . $scripturl . '?action=garage;sa=supported_video" rel="shadowbox;width=260;height=400" title="' . $txt['smfg_video_instructions'] . '">Supported Sites</a></span></td>
        </tr>';

        echo '    
        <tr>
            <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
            <td><textarea name="video_desc" cols="60" rows="3"></textarea></td>
        </tr>';

    }
    echo '
        <tr>
            <td align="center" height="28" colspan="2"><input type="hidden" value="' . $_GET['VID'] . '" name="VID"/><input type="hidden" value="" name="MID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="modification" type="submit" value="' . $txt['smfg_add_modification'] . '" /></td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </form>';

    echo '
    <script type="text/javascript">
     var frmvalidator = new Validator("add_modification");
     var frm = document.forms["add_modification"];
     
     frmvalidator.addValidation("category_id","req","' . $txt['smfg_val_select_category'] . '");
     frmvalidator.addValidation("category_id","dontselect=0","' . $txt['smfg_val_select_category'] . '");
     frmvalidator.addValidation("category_id","dontselect=1","' . $txt['smfg_val_select_category'] . '");
     
     frmvalidator.addValidation("manufacturer_id","req","' . $txt['smfg_val_select_manufacturer'] . '");
     frmvalidator.addValidation("product_id","req","' . $txt['smfg_val_select_product'] . '");                 
     frmvalidator.addValidation("product_rating","req","' . $txt['smfg_val_select_product_rating'] . '");  
                    
     frmvalidator.addValidation("price","req","' . $txt['smfg_val_enter_purchased_price'] . '");
     frmvalidator.addValidation("price","regexp=^[.0-9]{1,8}$","' . $txt['smfg_val_purchased_price_numeric'] . '");
     
     frmvalidator.addValidation("purchase_rating","req","' . $txt['smfg_val_select_purchase_rating'] . '");
     
     frmvalidator.addValidation("install_price","req","' . $txt['smfg_val_enter_install_price'] . '");
     frmvalidator.addValidation("install_price","regexp=^[.0-9]{1,8}$","' . $txt['smfg_val_install_price_numeric'] . '");
     
     frmvalidator.addValidation("install_rating","req","' . $txt['smfg_val_select_install_rating'] . '");
     
     frmvalidator.addValidation("comments","maxlen=500","' . $txt['smfg_val_description_length'] . '");
     frmvalidator.addValidation("install_comments","maxlen=500","' . $txt['smfg_val_install_comments_length'] . '");';
    if ($smfgSettings['enable_vehicle_images']) {
        echo '
            frmvalidator.addValidation("attach_desc","maxlen=150","' . $txt['smfg_val_image_description_length'] . '");';
    }
    if ($smfgSettings['enable_vehicle_video']) {
        echo '
            frmvalidator.addValidation("video_desc","maxlen=150","' . $txt['smfg_val_video_description_length'] . '");';
    }
    echo '
    </script>';

    echo smfg_footer();

}

function template_edit_modification()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_edit_modification'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '
    <table cellpadding="0" cellspacing="0" border="0" style="margin: 7px 0 5px 10px" id="tab_table">
        <tr id="tab_row">
            <td>               
            <ul class="dropmenu">
                <li id="button_g_vehicle">
                    <a class="firstlevel" href="#modification" onclick="change_tab(\'000\');" id="tab000">
                        <span class="firstlevel">' . $txt['smfg_modification'] . '</span>
                    </a>
                </li>';

    if ($smfgSettings['enable_vehicle_images']) {

        echo '
                    <li id="button_g_images">
                        <a class="firstlevel" href="#images" onclick="change_tab(\'001\');" id="tab001">
                            <span class="firstlevel">' . $txt['smfg_images'] . '</span>
                        </a>
                    </li>';

    }

    if ($smfgSettings['enable_vehicle_video']) {

        echo '
                    <li id="button_g_videos">
                        <a class="firstlevel" href="#videos" onclick="change_tab(\'002\');" id="tab002">
                            <span class="firstlevel">' . $txt['smfg_videos'] . '</span>
                        </a>
                    </li>';

    }

    echo '
            </ul>
            </td>
        </tr>
    </table>';

    // Begin dynamic js divs
    // List Prod Options
    echo product_options("edit_modification");

    echo '
    <script type="text/javascript">dol2.forField("manufacturer_id").setValues("' . $context['mods']['manufacturer_id'] . '");</script>';

    echo '
    <script type="text/javascript">dol2.forField("product_id").setValues("' . $context['mods']['product_id'] . '");</script>';

    echo '                
    <div class="garage_panel" id="options000" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_modification'];

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
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Submitted items?
    if (!$_SESSION['added_man'] && !$_SESSION['added_product'] && !$_SESSION['added_shop'] && !$_SESSION['added_garage']) {
        echo '
        <tr>
            <td align="center" valign="middle">' . $txt['smfg_add_mod_info'] . '<hr /></td>
        </tr>';
    }

    // Manufactuer?
    if ($_SESSION['added_man']) {
        echo '
        <tr>
            <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_manufacturer_added'] . '</td>
        </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_man']);
    }

    // Product?
    if ($_SESSION['added_product']) {
        echo '
        <tr>
            <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_product_added'] . '</td>
        </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_product']);
    }

    // Shop?
    if ($_SESSION['added_shop']) {
        echo '
        <tr>
            <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_shop_added'] . '</td>
        </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_shop']);
    }

    // Garage?
    if ($_SESSION['added_garage']) {
        echo '
        <tr>
            <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_garage_added'] . '</td>
        </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_garage']);
    }

    echo '
        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=update_modification;VID=' . $_GET['VID'] . ';MID=' . $_GET['MID'] . '" id="edit_modification" enctype="multipart/form-data" method="post" name="edit_modification" style="padding:0; margin:0;">
            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . ';#modifications" />
            <table width="100%" cellpadding="3" cellspacing="1" border="0">
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_category'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                    <td colspan="2">
                    <select id="category_id" name="category_id">
                    <option value="">' . $txt['smfg_select_category'] . '</option>
                    <option value="">------</option>';
    // List Mod Category Selections
    echo cat_select($context['mods']['category_id']);
    echo '
                    </select>
                    &nbsp;
                    </td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_manufacturer'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                    <td colspan="2">
                    <select id="manufacturer_id" name="manufacturer_id">
                    </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=product" rel="shadowbox;width=620;height=560" title="Garage :: Submit Product">' . $txt['smfg_here'] . '</a>';
    }
    echo '
                    </td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_product'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                    <td colspan="2">
                    <select id="product_id" name="product_id">
                    </select>
                    ';
    if ($smfgSettings['enable_user_submit_product']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_product" rel="shadowbox;width=620;height=200" title="Garage :: Submit Product">' . $txt['smfg_here'] . '</a>';
    }
    echo '&nbsp;&nbsp;&nbsp;
                    <b>' . $txt['smfg_rating'] . '</b>&nbsp;';
    echo '
                    <select id="product_rating" name="product_rating">
                    <option value="">' . $txt['smfg_select_rating'] . '</option>
                    <option value="">------</option>
                    <option value="10" ' . $context['prod_rat_10'] . '>10 ' . $txt['smfg_best'] . '</option>
                    <option value="9" ' . $context['prod_rat_9'] . '>9</option>
                    <option value="8" ' . $context['prod_rat_8'] . '>8</option>
                    <option value="7" ' . $context['prod_rat_7'] . '>7</option>
                    <option value="6" ' . $context['prod_rat_6'] . '>6</option>
                    <option value="5" ' . $context['prod_rat_5'] . '>5</option>
                    <option value="4" ' . $context['prod_rat_4'] . '>4</option>
                    <option value="3" ' . $context['prod_rat_3'] . '>3</option>
                    <option value="2" ' . $context['prod_rat_2'] . '>2</option>
                    <option value="1" ' . $context['prod_rat_1'] . '>1 ' . $txt['smfg_worst'] . '</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_purchased_from'] . '</b></td>
                    <td colspan="2">
                    <select id="shop_id" name="shop_id">
                    <option value="">' . $txt['smfg_select_shop'] . '</option>
                    <option value="">------</option>';
    // List Shop Options
    echo shop_select($context['mods']['shop_id']);
    echo '
                    </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=retail" rel="shadowbox;width=620;height=560" title="Garage :: Submit Retail">' . $txt['smfg_here'] . '</a>';
    }
    echo '
                    </td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_purchased_price'] . '</b></td>
                    <td colspan="2"><input name="price" type="text" size="10" value="' . $context['mods']['price'] . '" />&nbsp;
                    <b>' . $txt['smfg_purchase_rating'] . '</b>&nbsp;';
    echo '
                    <select id="purchase_rating" name="purchase_rating">
                    <option value="">' . $txt['smfg_select_rating'] . '</option>
                    <option value="">------</option>
                    <option value="10" ' . $context['purch_rat_10'] . '>10 ' . $txt['smfg_cheapest'] . '</option>
                    <option value="9" ' . $context['purch_rat_9'] . '>9</option>
                    <option value="8" ' . $context['purch_rat_8'] . '>8</option>
                    <option value="7" ' . $context['purch_rat_7'] . '>7</option>
                    <option value="6" ' . $context['purch_rat_6'] . '>6</option>
                    <option value="5" ' . $context['purch_rat_5'] . '>5</option>
                    <option value="4" ' . $context['purch_rat_4'] . '>4</option>
                    <option value="3" ' . $context['purch_rat_3'] . '>3</option>
                    <option value="2" ' . $context['purch_rat_2'] . '>2</option>
                    <option value="1" ' . $context['purch_rat_1'] . '>1 ' . $txt['smfg_most_expensive'] . '</option>
                    </select></td>
                </tr>    
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_installed_by'] . '</b></td>
                    <td colspan="2">
                    <select id="installer_id" name="installer_id">
                    <option value="">' . $txt['smfg_select_garage'] . '</option>
                    <option value="">------</option>';
    // List Installer/Garage Options
    echo install_select($context['mods']['installer_id']);
    echo '
                    </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=garage" rel="shadowbox;width=620;height=560" title="Garage :: Submit Garage">' . $txt['smfg_here'] . '</a>';
    }
    echo '</td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_installation_price'] . '</b></td>
                    <td colspan="2"><input name="install_price" type="text" size="10" value="' . $context['mods']['install_price'] . '" />&nbsp;
                    <b>' . $txt['smfg_installation_rating'] . '</b>&nbsp;';
    echo '
                    <select id="install_rating" name="install_rating">
                    <option value="">' . $txt['smfg_select_rating'] . '</option>
                    <option value="">------</option>
                    <option value="10" ' . $context['ins_rat_10'] . '>10 ' . $txt['smfg_cheapest'] . '</option>
                    <option value="9" ' . $context['ins_rat_9'] . '>9</option>
                    <option value="8" ' . $context['ins_rat_8'] . '>8</option>
                    <option value="7" ' . $context['ins_rat_7'] . '>7</option>
                    <option value="6" ' . $context['ins_rat_6'] . '>6</option>
                    <option value="5" ' . $context['ins_rat_5'] . '>5</option>
                    <option value="4" ' . $context['ins_rat_4'] . '>4</option>
                    <option value="3" ' . $context['ins_rat_3'] . '>3</option>
                    <option value="2" ' . $context['ins_rat_2'] . '>2</option>
                    <option value="1" ' . $context['ins_rat_1'] . '>1 ' . $txt['smfg_most_expensive'] . '</option>
                    </select></td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                    <td colspan="2"><textarea name="comments" cols="60" rows="4">' . $context['mods']['comments'] . '</textarea></td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_install_comments'] . '</b><br/>' . $txt['smfg_only_show_in'] . '</td>

                    <td colspan="2"><textarea name="install_comments" cols="60" rows="4">' . $context['mods']['install_comments'] . '</textarea></td>
                </tr>
                <tr>
                    <td align="center" height="28" colspan="3"><input type="hidden" value="' . $_GET['VID'] . '" name="VID"/><input type="hidden" value="' . $_GET['MID'] . '" name="MID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" /><input name="modification" type="submit" value="' . $txt['smfg_update_modification'] . '" /></td>
                </tr>
            </table>
            </form>
            <script type="text/javascript">
            var frmvalidator = new Validator("edit_modification");
            var frm = document.forms["edit_modification"];
            
            frmvalidator.addValidation("category_id","req","' . $txt['smfg_val_select_category'] . '");
            frmvalidator.addValidation("category_id","dontselect=0","' . $txt['smfg_val_select_category'] . '");
            frmvalidator.addValidation("category_id","dontselect=1","' . $txt['smfg_val_select_category'] . '");
                     
            frmvalidator.addValidation("manufacturer_id","req","' . $txt['smfg_val_select_manufacturer'] . '");
            frmvalidator.addValidation("product_id","req","' . $txt['smfg_val_select_product'] . '");                 
            frmvalidator.addValidation("product_rating","req","' . $txt['smfg_val_select_product_rating'] . '");  
                                    
            frmvalidator.addValidation("price","req","' . $txt['smfg_val_enter_purchased_price'] . '");
            frmvalidator.addValidation("price","regexp=^[.0-9]{1,8}$","' . $txt['smfg_val_purchased_price_numeric'] . '");
                     
            frmvalidator.addValidation("purchase_rating","req","' . $txt['smfg_val_select_purchase_rating'] . '");
                     
            frmvalidator.addValidation("install_price","req","' . $txt['smfg_val_enter_install_price'] . '");
            frmvalidator.addValidation("install_price","regexp=^[.0-9]{1,8}$","' . $txt['smfg_val_install_price_numeric'] . '");
                     
            frmvalidator.addValidation("install_rating","req","' . $txt['smfg_val_select_install_rating'] . '");
                     
            frmvalidator.addValidation("comments","maxlen=500","' . $txt['smfg_val_description_length'] . '");
            frmvalidator.addValidation("install_comments","maxlen=500","' . $txt['smfg_val_install_comments_length'] . '");
            </script>';
    echo '
            </td>
        </tr>    
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </div>';

    // Show the input for remote images if it is enabled
    if ($smfgSettings['enable_modification_images']) {
        echo '
        <div class="garage_panel" id="options001" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_images'];

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
                
                <form action="' . $scripturl . '?action=garage;sa=insert_modification_images" id="update_images" enctype="multipart/form-data" method="post" name="update_images" style="padding:0; margin:0;">
                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_modification;VID=' . $_GET['VID'] . ';MID=' . $_GET['MID'] . '#images" />
                <table border="0" cellpadding="3" cellspacing="1" width="100%">           
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_attach_image'] . '.<br />' . $txt['smfg_max_filesize'] . ': ' . $smfgSettings['max_image_kbytes'] . ' ' . $txt['smfg_kbytes'] . '<br />' . $txt['smfg_max_resolution'] . ': ' . $smfgSettings['max_image_resolution'] . 'x' . $smfgSettings['max_image_resolution'] . '</b></td>
                        <td><input type="hidden" name="MAX_FILE_SIZE" value="' . $context['max_image_bytes'] . '" /><input type="file" size="30" name="FILE_UPLOAD" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,.jpg,.jpeg,.png,.gif,.webp,.bmp"/></td>
                    </tr>';

        // Show the input for remote images if it is enabled
        if ($smfgSettings['enable_remote_images']) {
            echo '    
                        <tr>
                            <td width="32%" align="right"><b>' . $txt['smfg_enter_remote_url'] . '</b></td>
                            <td><input name="url_image" type="text" size="40" maxlength="255" value="https://" /></td>
                        </tr>';
        }

        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="attach_desc" cols="60" rows="3"></textarea></td>
                    </tr>    
                    <tr>
                        <td colspan="2" align="center" height="28"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['MID'] . '" name="MID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="insert_modification_images" type="submit" value="' . $txt['smfg_add_new_image'] . '" /></td>
                    </tr>
                </table>
                </form>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">
                    <tr>
                        <td colspan="2">
                        <table border="0" cellpadding="3" cellspacing="1" width="100%">
                            <tr>
                                <td width="100%" align="center" colspan="3">' . $txt['smfg_edit_in_place_instructions'] . '<div id="updateStatus"></div></td>
                            </tr>
                            <tr>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_image'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_description'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_manage'] . '
                                </span></h4></div></td>
                            </tr>';
        $count = 0;
        // If there is an image, show em
        if (isset($context['mods'][$count]['image_id'])) {
            // and keep showing em
            while (isset($context['mods'][$count]['image_id'])) {
                echo '                            
                                    <tr class="tableRow">
                                        <td align="center" valign="middle">' . $context['mods'][$count]['image'] . '</td>
                                        <td align="center" valign="middle">
                                        <div id="image' . $context['mods'][$count]['image_id'] . '" class="editin">';
                // If there is no desc, let them add one
                if (!empty($context['mods'][$count]['attach_desc'])) {
                    echo $context['mods'][$count]['attach_desc'];
                }
                echo '</div></td>
                                        <td align="center" valign="middle">';
                if ($context['mods'][$count]['hilite'] != 1) {
                    echo '
                                            <form action="' . $scripturl . '?action=garage;sa=set_hilite_image_mod;VID=' . $_GET['VID'] . ';MID=' . $_GET['MID'] . ';image_id=' . $context['mods'][$count]['image_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="set_mod_hilite_' . $context['mods'][$count]['image_id'] . '" id="set_mod_hilite_' . $context['mods'][$count]['image_id'] . '" style="display: inline;">
                                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_modification;VID=' . $_GET['VID'] . ';MID=' . $_GET['MID'] . '#images" />
                                                <a href="#" onClick="document.set_mod_hilite_' . $context['mods'][$count]['image_id'] . '.submit(); return false;">' . $txt['smfg_set_hilite_image'] . '</a>
                                            </form>
                                            <br /><br />';
                } else {
                    echo
                        $txt['smfg_hilite_image'] . '<br /><br />';
                }
                echo '
                                        <form action="' . $scripturl . '?action=garage;sa=remove_modification_image;VID=' . $_GET['VID'] . ';MID=' . $_GET['MID'] . ';image_id=' . $context['mods'][$count]['image_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_modification_image_' . $context['mods'][$count]['image_id'] . '" id="remove_modification_image_' . $context['mods'][$count]['image_id'] . '" style="display: inline;">
                                            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_modification;VID=' . $_GET['VID'] . ';MID=' . $_GET['MID'] . '#images" />
                                            <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_image'] . '\')) { document.remove_modification_image_' . $context['mods'][$count]['image_id'] . '.submit(); } else { return false; } return false;">' . $txt['smfg_remove_image'] . '</a>
                                        </form>
                                        </td>
                                    </tr>';
                $count++;
            }
        } else {
            echo '
                                <tr>
                                    <td colspan="3" align="center" valign="middle">' . $txt['smfg_no_modification_images'] . '</td>
                                </tr>';
        }
        echo '
                            </table> 
                        </td>
                    </tr>
                </table>          
                </td>
            </tr>
        </table>
        <script type="text/javascript">
         var frmvalidator = new Validator("update_images");
        
         frmvalidator.addValidation("attach_desc","maxlen=150","' . $txt['smfg_val_image_description_length'] . '");         
        </script>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '
        </div>';
    }

    // Show the input for videos if it is enabled
    if ($smfgSettings['enable_modification_video']) {
        echo '
        <div class="garage_panel" id="options002" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_video'];

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
                                    
                <form action="' . $scripturl . '?action=garage;sa=insert_modification_video" id="update_video" enctype="multipart/form-data" method="post" name="update_video" style="padding:0; margin:0;">         
                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_modification;VID=' . $_GET['VID'] . ';MID=' . $_GET['MID'] . '#video" />
                <table border="0" cellpadding="3" cellspacing="1" width="100%">  
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_title'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="75" value="" name="video_title"/></td>
                    </tr>
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_url'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="255" value="https://" name="video_url"/>&nbsp;<span class="smalltext"><a href="' . $scripturl . '?action=garage;sa=supported_video" rel="shadowbox;width=260;height=400" title="' . $txt['smfg_video_instructions'] . '">Supported Sites</a></span></td>
                    </tr>';

        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="video_desc" cols="60" rows="3"></textarea></td>
                    </tr>   
                    <tr>
                        <td colspan="2" align="center" height="28"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['MID'] . '" name="MID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="insert_modification_video" type="submit" value="' . $txt['smfg_add_new_video'] . '" /></td>
                    </tr>
                </table>
                </form>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">  
                    <tr>
                        <td colspan="2">
                        <table border="0" cellpadding="3" cellspacing="1" width="100%">
                            <tr>
                                <td width="100%" align="center" colspan="3">' . $txt['smfg_edit_in_place_instructions'] . '<div id="updateStatus2"></div></td>
                            </tr>
                            <tr>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_video'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_title_description'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_manage'] . '
                                </span></h4></div></td>
                            </tr>';

        $count = 0;
        // If there is an video, show em
        if (isset($context['mods'][$count]['video_id'])) {
            // and keep showing em
            while (isset($context['mods'][$count]['video_id'])) {
                echo '                            
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=video;id=' . $context['mods'][$count]['video_id'] . '" rel="shadowbox;width=' . $context['mods'][$count]['video_width'] . ';height=' . $context['mods'][$count]['video_height'] . '" title="' . garage_title_clean('<b>' . $context['mods'][$count]['video_title'] . '</b> :: ' . $context['mods'][$count]['video_desc']) . '" class="smfg_videoTitle"><img src="' . $context['mods'][$count]['video_thumb'] . '" /></a></td>
                                        <td align="center" valign="middle">
                                        <div id="video_title' . $context['mods'][$count]['video_id'] . '" class="editin" style="font-weight: bold;">';
                // If there is no title, let them add one
                if (!empty($context['mods'][$count]['video_title'])) {
                    echo $context['mods'][$count]['video_title'];
                }
                echo '</div>
                                        <br />
                                        <div id="video' . $context['mods'][$count]['video_id'] . '" class="editin">';
                // If there is no desc, let them add one
                if (!empty($context['mods'][$count]['video_desc'])) {
                    echo $context['mods'][$count]['video_desc'];
                }
                echo '</div></td>
                                        <td align="center" valign="middle">';
                echo '
                                        <form action="' . $scripturl . '?action=garage;sa=remove_video;VID=' . $_GET['VID'] . ';video_id=' . $context['mods'][$count]['video_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_modification_video_' . $context['mods'][$count]['video_id'] . '" id="remove_modification_video_' . $context['mods'][$count]['video_id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_modification;VID=' . $_GET['VID'] . ';MID=' . $_GET['MID'] . '#video" />
                                        <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_video'] . '\')) { document.remove_modification_video_' . $context['mods'][$count]['video_id'] . '.submit(); } else { return false; } return false;">' . $txt['smfg_remove_video'] . '</a>
                                        </form>
                                        </td>
                                    </tr>';
                $count++;
            }
        } else {
            echo '
                                <tr>
                                    <td colspan="3" align="center" valign="middle">' . $txt['smfg_no_mod_videos'] . '</td>
                                </tr>';
        }
        echo '
                            </table> 
                        </td>
                    </tr>
                </table>  
                </td>
            </tr>
        </table>
        <script type="text/javascript">
         var frmvalidator = new Validator("update_video");
          
         frmvalidator.addValidation("video_desc","maxlen=150","' . $txt['smfg_val_video_description_length'] . '");
         frmvalidator.addValidation("video_title","req","' . $txt['smfg_val_enter_title'] . '");
         
        </script>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '
        </div>';
    }

    echo '

<script type="text/javascript">
<!--
    var lowest_tab = \'000\';
    var active_id = \'000\';
    if (document.location.hash == "")
    {
        change_tab(lowest_tab);
    }
    else if (document.location.hash == "#modification")
    {
        change_tab(\'000\');
    }
    else if (document.location.hash == "#images")
    {
        change_tab(\'001\');
    }
    else if (document.location.hash == "#videos")
    {
        change_tab(\'002\');
    }

//-->

</script>';

    echo smfg_footer();

}

function template_view_modification()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings, $boardurl;

    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $context['mods']['product'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table border="0" cellpadding="3" cellspacing="1" width="100%">

    <tr>
        <td align="center" valign="top">';

    // Pending?
    if ($context['mods']['pending'] == '1') {
        echo '
            <table class="tborder" width="90%">
                <tr>
                    <td>
                    <table border="0">
                        <tr>
                            <td align="center" valign="middle" width="40"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="" title="" /></td>
                            <td align="center" valign="middle">' . $txt['smfg_pending_item'] . '</td>
                        </tr>
                    </table>
                    </td>
                </tr>
            </table><br />';
    }

    echo '
            <table border="0" width="70%">
                <tr>
                    <td align="left"><b>' . $txt['smfg_owner'] . '</b></td>
                </tr>
                <tr>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['user_vehicles']['user_id'] . '"><b>' . $context['mods']['owner'] . '</b></a></td>
                </tr>
                <tr>
                    <td align="left"><b>' . $txt['smfg_hilite_image'] . '</b></td>
                </tr>
                <tr>
                    <td align="center">', (!empty($context['hilite_image_location'])) ? '<a href="' . $context['hilite_image_location'] . '" rel="shadowbox" title="' . $context['mods']['product'] . ' :: ' . garage_title_clean($context['hilite_desc']) . '" class="smfg_imageTitle"><img src="' . $context['hilite_thumb_location'] . '" width="' . $context['hilite_thumb_width'] . '" height="' . $context['hilite_thumb_height'] . '" /></a>' : '', '</td>
                </tr>
                <tr>
                    <td align="left"><b>' . $txt['smfg_comments'] . '</b></td>
                </tr>
                <tr>
                    <td align="center">' . $context['mods']['comments'] . '</td>
                </tr>
            </table>
        </td>
        <td width="30%" valign="middle" align="center">
            <table border="0" cellspacing="1" cellpadding="3">
            <tr>
                <td align="left"><b>' . $txt['smfg_manufacturer'] . '</b></td>
                <td align="left"><a href="' . $scripturl . '?action=garage;sa=mfg_review;BID=' . $context['mods']['manufacturer_id'] . '">' . garage_title_clean($context['mods']['manufacturer']) . '</a><td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_modification'] . '</b></td>
                <td align="left">' . $context['mods']['product'] . '<td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_category'] . '</b></td>
                <td align="left">' . $context['mods']['category'] . '<td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_vehicle'] . '</b></td>
                <td align="left"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '">' . garage_title_clean($context['user_vehicles']['title']) . '</a><td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_updated'] . '</b></td>
                <td align="left">' . date($context['date_format'], $context['mods']['date_updated']) . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_purchased_from'] . '</b></td>
                <td align="left">', (!empty($context['mods']['shop'])) ? '<a href="' . $scripturl . '?action=garage;sa=shop_review;BID=' . $context['mods']['shop_id'] . '">' . garage_title_clean($context['mods']['shop']) . '</a>' : '', '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_purchased_price'] . '</b></td>
                <td align="left">' . $context['mods']['price'] . ' ' . $context['user_vehicles']['currency'] . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_product_rating'] . '</b></td>
                <td align="left">' . $context['mods']['product_rating'] . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_installed_by'] . '</b></td>
                <td align="left">', (!empty($context['mods']['installer'])) ? '<a href="' . $scripturl . '?action=garage;sa=garage_review;BID=' . $context['mods']['installer_id'] . '">' . garage_title_clean($context['mods']['installer']) . '</a>' : '', '<td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_installation_price'] . '</b></td>
                <td align="left">' . $context['mods']['install_price'] . ' ' . $context['user_vehicles']['currency'] . '<td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_installation_rating'] . '</b></td>
                <td align="left">' . $context['mods']['install_rating'] . '<td>
            </tr>
            </table>
        </td>
    </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    if ($smfgSettings['enable_modification_images'] || $smfgSettings['enable_modification_video']) {

        echo '
        <table cellpadding="0" cellspacing="0" border="0" style="margin: 7px 0 5px 10px" id="tab_table">
            <tr id="tab_row">
                <td>               
                <ul class="dropmenu">
                    <li id="button_g_images">
                        <a class="firstlevel" href="#images "onclick="change_tab(\'000\');" id="tab000">
                            <span class="firstlevel">' . $txt['smfg_images'] . '</span>
                        </a>
                    </li>';

        if (isset($context['mods'][$count]['video_id']) && $smfgSettings['enable_modification_video']) {

            echo '
                        <li id="button_g_videos">
                            <a class="firstlevel" href="#videos "onclick="change_tab(\'001\');" id="tab001">
                                <span class="firstlevel">' . $txt['smfg_videos'] . '</span>
                            </a>
                        </li>';

        }

        echo '
                </ul>
                </td>
            </tr>
        </table>';

        // Begin dynamic js divs
        echo '        
        <div class="garage_panel" id="options000" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_images'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';
        $count = 0;
        if (isset($context['mods'][$count]['image_id'])) {
            echo '
            <tr>
                <td valign="middle">';
            while (isset($context['mods'][$count]['image_id'])) {
                echo '
                <a href="' . $context['mods'][$count]['attach_location'] . '" rel="shadowbox[mods]" title="' . garage_title_clean($context['mods']['product'] . ' :: ' . $context['mods'][$count]['attach_desc']) . '" class="smfg_imageTitle"><img src="' . $context['mods'][$count]['attach_thumb_location'] . '" width="' . $context['mods'][$count]['attach_thumb_width'] . '" height="' . $context['mods'][$count]['attach_thumb_height'] . '" /></a>';
                $count++;
            }
            echo '
                </td>
            </tr>';
        } else {
            echo '
            <tr>
                <td align="center">' . $txt['smfg_no_modification_images'] . '</td>
            </tr>';
        }
        echo '
        </table>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '   
        </div>';

        echo '
        <div class="garage_panel" id="options001" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_videos'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';
        $count = 0;
        if (isset($context['mods'][$count]['video_id'])) {
            echo '
            <tr>
                <td valign="middle">';
            while (isset($context['mods'][$count]['video_id'])) {
                echo '
                    <a href="' . $scripturl . '?action=garage;sa=video;id=' . $context['mods'][$count]['video_id'] . '" rel="shadowbox[video];width=' . $context['mods'][$count]['video_width'] . ';height=' . $context['mods'][$count]['video_height'] . ';" title="' . garage_title_clean('<b>' . $context['mods'][$count]['video_title'] . '</b> :: ' . $context['mods'][$count]['video_desc']) . '" class="smfg_videoTitle"><img src="' . $context['mods'][$count]['video_thumb'] . '" /></a>';
                $count++;
            }
            echo '
                </td>
            </tr>';
        } else {
            echo '
            <tr>
                <td align="center">' . $txt['smfg_no_videos'] . '</td>
            </tr>';
        }
        echo '  
        </table> ';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '    
        </div>

        <script type="text/javascript">
        <!--
            var lowest_tab = \'000\';
            var active_id = \'000\';
            if (document.location.hash == "")
            {
                change_tab(lowest_tab);
            }
            else if (document.location.hash == "#images")
            {
                change_tab(\'000\');
            }
            else if (document.location.hash == "#videos")
            {
                change_tab(\'001\');
            }

        //-->

        </script>';

    }

    echo smfg_footer();

}

function template_add_insurance()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_add_insurance'];

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
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Submitted insurance agency?
    if ($_SESSION['added_insurance']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_insurance_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a dyno is added
        unset($_SESSION['added_insurance']);
    }

    echo '
        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=insert_insurance" enctype="multipart/form-data" method="post" id="add_insurance" name="add_insurance" style="padding:0; margin:0;">
            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '#premiums" />
            <table width="100%" cellpadding="3" cellspacing="1" border="0">
                <tr>
                    <td width="35%" align="right"><b>' . $txt['smfg_insurer'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                    <td>
                    <select id="business_id" name="business_id">
                    <option value="">' . $txt['smfg_select_insurer'] . '</option>
                    <option value="">------</option>
                    ';
    echo insurer_select();
    echo '
                    </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=insurance" rel="shadowbox;width=620;height=560" title="Garage :: Submit Insurance">' . $txt['smfg_here'] . '</a>';
    }
    echo '
                    </td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_cost_of_premium'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                    <td><input name="premium" type="text" size="15" value="" /></td>
                </tr>
                <tr>
                    <td width="35%" align="right"><b>' . $txt['smfg_cover_type'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                    <td>
                    <select id="cover_type" name="cover_type">
                    <option value="">' . $txt['smfg_select_cover_type'] . '</option>
                    <option value="">------</option>';
    echo premium_type_select();
    echo '
                    </select>
                    </td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_comments'] . '</b></td>
                    <td><textarea name="comments" cols="60" rows="5"></textarea></td>
                </tr>
                <tr>
                    <td align="center" height="28" colspan="2"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="" name="INS_ID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_add_insurance'] . '" /></td>
                </tr>      
            </table>
            </form>
            <script type="text/javascript">
            var frmvalidator = new Validator("add_insurance");
            
            frmvalidator.addValidation("business_id","req","' . $txt['smfg_val_select_insurance_company'] . '");
            frmvalidator.addValidation("business_id","dontselect=0","' . $txt['smfg_val_select_insurance_company'] . '");
            frmvalidator.addValidation("business_id","dontselect=1","' . $txt['smfg_val_select_insurance_company'] . '");
            
            frmvalidator.addValidation("premium","req","' . $txt['smfg_val_enter_premium'] . '");
            frmvalidator.addValidation("premium","regexp=^[0-9]{1,10}$","' . $txt['smfg_val_premium_numeric'] . '");
            
            frmvalidator.addValidation("cover_type","req","' . $txt['smfg_val_select_coverage'] . '");
            frmvalidator.addValidation("cover_type","dontselect=0","' . $txt['smfg_val_select_coverage'] . '");
            frmvalidator.addValidation("cover_type","dontselect=1","' . $txt['smfg_val_select_coverage'] . '");
            frmvalidator.addValidation("comments","maxlen=500","' . $txt['smfg_val_comment_length'] . '");
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_edit_insurance()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_edit_insurance'];

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
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Submitted insurance agency?
    if ($_SESSION['added_insurance']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_insurance_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a dyno is added
        unset($_SESSION['added_insurance']);
    }

    echo '
        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=update_insurance" enctype="multipart/form-data" method="post" name="edit_insurance" style="padding:0; margin:0;">
            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . ';#premiums" />
            <table width="100%" cellpadding="3" cellspacing="1" border="0">
                <tr>
                    <td width="35%" align="right"><b>' . $txt['smfg_insurer'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                    <td>
                    <select id="business_id" name="business_id">
                    <option value="">' . $txt['smfg_select_insurer'] . '</option>
                    <option value="">------</option>
                    ';
    echo insurer_select($context['premiums']['business_id']);
    echo '
                    </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=insurance" rel="shadowbox;width=620;height=560" title="Garage :: Submit Insurance">' . $txt['smfg_here'] . '</a>';
    }
    echo '
                    </td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_cost_of_premium'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                    <td><input name="premium" type="text" size="15" value="' . $context['premiums']['premium'] . '" /></td>
                </tr>
                <tr>
                    <td width="35%" align="right"><b>' . $txt['smfg_cover_type'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                    <td>
                    <select id="cover_type" name="cover_type">
                    <option value="">' . $txt['smfg_select_cover_type'] . '</option>
                    <option value="">------</option>
                    ';
    echo premium_type_select($context['premiums']['cover_type_id']);
    echo '
                    </select>
                    </td>
                </tr>
                <tr>
                    <td width="30%" align="right"><b>' . $txt['smfg_comments'] . '</b></td>
                    <td><textarea name="comments" cols="60" rows="5">' . $context['premiums']['comments'] . '</textarea></td>
                </tr>
                <tr>
                    <td align="center" height="28" colspan="2"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['INS_ID'] . '" name="INS_ID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" /><input name="submit" type="submit" value="' . $txt['smfg_update_insurance'] . '" /></td>
                </tr>      
            </table>
            </form>
            <script type="text/javascript">
            var frmvalidator = new Validator("edit_insurance");
            
            frmvalidator.addValidation("business_id","req","' . $txt['smfg_val_select_insurance_company'] . '");
            frmvalidator.addValidation("business_id","dontselect=0","' . $txt['smfg_val_select_insurance_company'] . '");
            frmvalidator.addValidation("business_id","dontselect=1","' . $txt['smfg_val_select_insurance_company'] . '");
            
            frmvalidator.addValidation("premium","req","' . $txt['smfg_val_enter_premium'] . '");
            frmvalidator.addValidation("premium","regexp=^[0-9]{1,10}$","' . $txt['smfg_val_premium_numeric'] . '");
            
            frmvalidator.addValidation("cover_type","req","' . $txt['smfg_val_select_coverage'] . '");
            frmvalidator.addValidation("cover_type","dontselect=0","' . $txt['smfg_val_select_coverage'] . '");
            frmvalidator.addValidation("cover_type","dontselect=1","' . $txt['smfg_val_select_coverage'] . '");
            frmvalidator.addValidation("comments","maxlen=500","' . $txt['smfg_val_comment_length'] . '");
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_add_quartermile()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_add_quartermile'];

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
            <form action="' . $scripturl . '?action=garage;sa=insert_quartermile" enctype="multipart/form-data" method="post" name="add_quartermile" id="add_quartermile" style="padding:0; margin:0;">
                 <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '#quartermiles" />
                <table width="100%" cellpadding="3" cellspacing="1" border="0">
                   <tr>
                        <td width="35%" align="right"><b>' . $txt['smfg_reaction_time'] . '</b><br />' . $txt['smfg_enter_reaction'] . '</td>
                        <td><input name="rt" type="text" size="15" value="" /></td>
                   </tr>
                   <tr>
                        <td width="35%" align="right"><b>' . $txt['smfg_sixty_foot_time'] . '</b><br />' . $txt['smfg_enter_sixty'] . '</td>
                        <td><input name="sixty" type="text" size="15" value="" /></td>
                   </tr>
                   <tr>
                        <td width="35%" align="right"><b>' . $txt['smfg_three_foot_time'] . '</b><br />' . $txt['smfg_enter_three'] . '</td>
                        <td><input name="three" type="text" size="15" value="" /></td>
                   </tr>
                   <tr>
                        <td width="35%" align="right"><b>' . $txt['smfg_eighth_time'] . '</b><br />' . $txt['smfg_enter_eighth'] . '</td>
                        <td><input name="eighth" type="text" size="15" value="" /></td>
                   </tr>
                   <tr>
                        <td width="35%" align="right"><b>' . $txt['smfg_eighth_speed'] . '</b><br />' . $txt['smfg_enter_eighth_speed'] . '</td>
                        <td><input name="eighthmph" type="text" size="15" value="" /></td>
                   </tr>
                   <tr>
                        <td width="35%" align="right"><b>' . $txt['smfg_thou_time'] . '</b><br />' . $txt['smfg_enter_thou_time'] . '</td>
                        <td><input name="thou" type="text" size="15" value="" /></td>
                    </tr>
                    <tr>
                        <td width="35%" align="right"><b>' . $txt['smfg_quart_time'] . '</b><br />' . $txt['smfg_enter_quart_time'] . '</td>
                        <td><input name="quart" type="text" size="15" value="" />&nbsp;' . $txt['smfg_required'] . '</td>
                    </tr>
                    <tr>
                        <td width="35%" align="right"><b>' . $txt['smfg_quart_speed'] . '</b><br />' . $txt['smfg_enter_quart_speed'] . '</td>
                        <td><input name="quartmph" type="text" size="15" value=""/>&nbsp;' . $txt['smfg_required'] . '</td>
                    </tr>
                    <tr>
                        <td width="35%" align="right"><b>' . $txt['smfg_link_to_rr'] . '</b></td>
                        <td>
                        <select id="dynorun_id" name="dynorun_id">
                        <option value="">' . $txt['smfg_select_dynorun'] . '</option>
                        <option value="">------</option>';
    echo dynoqm_select($_GET['VID']);
    echo '
                        </select>
                        </td>
                    </tr>';

    // Show the input for remote images if it is enabled
    if ($smfgSettings['enable_quartermile_images']) {
        echo '
                    <tr>
                        <td colspan="2"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_image_attachments'] . '
                        </span></h4></div></td>
                    </tr>                    
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_attach_image'] . '.<br />' . $txt['smfg_max_filesize'] . ': ' . $smfgSettings['max_image_kbytes'] . ' ' . $txt['smfg_kbytes'] . '<br />' . $txt['smfg_max_resolution'] . ': ' . $smfgSettings['max_image_resolution'] . 'x' . $smfgSettings['max_image_resolution'] . '</b></td>
                        <td><input type="hidden" name="MAX_FILE_SIZE" value="' . $context['max_image_bytes'] . '" /><input type="file" size="30" name="FILE_UPLOAD" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,.jpg,.jpeg,.png,.gif,.webp,.bmp"/></td>
                    </tr>';

        // Show the input for remote images if it is enabled
        if ($smfgSettings['enable_remote_images']) {
            echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_enter_remote_url'] . '</b></td>
                        <td><input name="url_image" type="text" size="40" maxlength="255" value="https://" /></td>
                    </tr>';
        }
        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="attach_desc" cols="60" rows="3"></textarea></td>
                    </tr>';
    }
    // Show the input for videos if it is enabled
    if ($smfgSettings['enable_quartermile_video']) {
        echo '
                    <tr>
                        <td colspan="2"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_hosted_videos'] . '
                        </span></h4></div></td>
                    </tr>
                    
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_title'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="75" value="" name="video_title"/></td>
                    </tr>
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_url'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="255" value="https://" name="video_url"/>&nbsp;<span class="smalltext"><a href="' . $scripturl . '?action=garage;sa=supported_video" rel="shadowbox;width=260;height=400" title="' . $txt['smfg_video_instructions'] . '">Supported Sites</a></span></td>
                    </tr>';

        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="video_desc" cols="60" rows="3"></textarea></td>
                    </tr>';

    }
    echo '    
                    <tr>
                        <td align="center" height="28" colspan="2"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_add_quartermile'] . '" /></td>
                    </tr>
                </table>
            </form>
            <script type="text/javascript">
            var frmvalidator = new Validator("add_quartermile");
            var frm = document.forms["add_quartermile"];
            
            frmvalidator.addValidation("rt","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_rt_restrictions'] . '");
            frmvalidator.addValidation("sixty","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_sixty_restrictions'] . '");
            frmvalidator.addValidation("three","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_three_restrictions'] . '");
            
            frmvalidator.addValidation("eighth","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_eighth_restrictions'] . '");
            frmvalidator.addValidation("eighthmph","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_eighth_mph_restrictions'] . '");
            
            frmvalidator.addValidation("thou","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_thou_restrictions'] . '");
            
            frmvalidator.addValidation("quart","req","' . $txt['smfg_val_enter_quart'] . '");
            frmvalidator.addValidation("quart","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_quart_restrictions'] . '");
            
            frmvalidator.addValidation("quartmph","req","' . $txt['smfg_val_enter_quart_mph'] . '");    
            frmvalidator.addValidation("quartmph","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_quart_mph_restrictions'] . '");';
    if ($smfgSettings['enable_vehicle_images']) {
        echo '
                    frmvalidator.addValidation("attach_desc","maxlen=150","' . $txt['smfg_val_image_description_length'] . '");';
    }
    if ($smfgSettings['enable_vehicle_video']) {
        echo '
                    frmvalidator.addValidation("video_desc","maxlen=150","' . $txt['smfg_val_video_description_length'] . '");';
    }
    echo '
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_edit_quartermile()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_edit_quartermile'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '
    <table cellpadding="0" cellspacing="0" border="0" style="margin: 7px 0 5px 10px" id="tab_table">
        <tr id="tab_row">
            <td>               
            <ul class="dropmenu">
                <li id="button_g_quartermile">
                    <a class="firstlevel" href="#quartermile" onclick="change_tab(\'000\');" id="tab000">
                        <span class="firstlevel">' . $txt['smfg_quartermile'] . '</span>
                    </a>
                </li>';

    if ($smfgSettings['enable_vehicle_images']) {

        echo '
                    <li id="button_g_images">
                        <a class="firstlevel" href="#images" onclick="change_tab(\'001\');" id="tab001">
                            <span class="firstlevel">' . $txt['smfg_images'] . '</span>
                        </a>
                    </li>';

    }

    if ($smfgSettings['enable_vehicle_video']) {

        echo '
                    <li id="button_g_videos">
                        <a class="firstlevel" href="#videos" onclick="change_tab(\'002\');" id="tab002">
                            <span class="firstlevel">' . $txt['smfg_videos'] . '</span>
                        </a>
                    </li>';

    }

    echo '
            </ul>
            </td>
        </tr>
    </table>';

    // Begin dynamic js divs
    echo '                
    <div class="garage_panel" id="options000" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_quartermile'];

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
    <form action="' . $scripturl . '?action=garage;sa=update_quartermile" enctype="multipart/form-data" method="post" name="edit_quartermile" id="edit_quartermile" style="padding:0; margin:0;">
    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . ';#quartermiles" />
    <table width="100%" cellpadding="3" cellspacing="1" border="0">
       <tr>
            <td width="40%" align="right"><b>' . $txt['smfg_reaction_time'] . '</b><br />' . $txt['smfg_enter_reaction'] . '</td>
            <td><input name="rt" type="text" size="15" value="' . $context['qmiles']['rt'] . '" /></td>
       </tr>
       <tr>
            <td width="40%" align="right"><b>' . $txt['smfg_sixty_foot_time'] . '</b><br />' . $txt['smfg_enter_sixty'] . '</td>
            <td><input name="sixty" type="text" size="15" value="' . $context['qmiles']['sixty'] . '" /></td>
       </tr>
       <tr>
            <td width="40%" align="right"><b>' . $txt['smfg_three_foot_time'] . '</b><br />' . $txt['smfg_enter_three'] . '</td>
            <td><input name="three" type="text" size="15" value="' . $context['qmiles']['three'] . '" /></td>
       </tr>
       <tr>
            <td width="40%" align="right"><b>' . $txt['smfg_eighth_time'] . '</b><br />' . $txt['smfg_enter_eighth'] . '</td>
            <td><input name="eighth" type="text" size="15" value="' . $context['qmiles']['eighth'] . '" /></td>
       </tr>
       <tr>
            <td width="40%" align="right"><b>' . $txt['smfg_eighth_speed'] . '</b><br />' . $txt['smfg_enter_eighth_speed'] . '</td>
            <td><input name="eighthmph" type="text" size="15" value="' . $context['qmiles']['eighthmph'] . '" /></td>
       </tr>
       <tr>
            <td width="40%" align="right"><b>' . $txt['smfg_thou_time'] . '</b><br />' . $txt['smfg_enter_thou_time'] . '</td>
            <td><input name="thou" type="text" size="15" value="' . $context['qmiles']['thou'] . '" /></td>
        </tr>
        <tr>
            <td width="40%" align="right"><b>' . $txt['smfg_quart_time'] . '</b><br />' . $txt['smfg_enter_quart_time'] . '</td>
            <td><input name="quart" type="text" size="15" value="' . $context['qmiles']['quart'] . '" /> ' . $txt['smfg_required'] . '</td>
        </tr>
        <tr>
            <td width="40%" align="right"><b>' . $txt['smfg_quart_speed'] . '</b><br />' . $txt['smfg_enter_quart_speed'] . '</td>
            <td><input name="quartmph" type="text" size="15" value="' . $context['qmiles']['quartmph'] . '"/> </td>
        </tr>
        <tr>
            <td width="40%" align="right"><b>' . $txt['smfg_link_to_rr'] . '</b></td>
            <td>
            <select id="dynorun_id" name="dynorun_id">
            <option value="">' . $txt['smfg_select_dynorun'] . '</option>
            <option value="">------</option>';
    echo dynoqm_select($_GET['VID'], $context['qmiles']['dynorun_id']);
    echo '
            </select>
            </td>
        </tr>                                 
        <tr>
            <td align="center" height="28" colspan="2"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['QID'] . '" name="QID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" /><input name="submit" type="submit" value="' . $txt['smfg_update_time'] . '" /></td>
        </tr>
    </table>
    </form>
    <script type="text/javascript">
    var frmvalidator = new Validator("edit_quartermile");
    
    frmvalidator.addValidation("rt","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_rt_restrictions'] . '");
    frmvalidator.addValidation("sixty","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_sixty_restrictions'] . '");
    frmvalidator.addValidation("three","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_three_restrictions'] . '");
    
    frmvalidator.addValidation("eighth","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_eighth_restrictions'] . '");
    frmvalidator.addValidation("eighthmph","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_eighth_mph_restrictions'] . '");
    
    frmvalidator.addValidation("thou","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_thou_restrictions'] . '");
    
    frmvalidator.addValidation("quart","req","' . $txt['smfg_val_enter_quart'] . '");
    frmvalidator.addValidation("quart","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_quart_restrictions'] . '");
    
    frmvalidator.addValidation("quartmph","req","' . $txt['smfg_val_enter_quart_mph'] . '");    
    frmvalidator.addValidation("quartmph","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_quart_mph_restrictions'] . '");
    </script>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </div>';
    // Show the input for images if it is enabled
    if ($smfgSettings['enable_quartermile_images']) {
        echo '
        <div class="garage_panel" id="options001" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_images'];

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
        <form action="' . $scripturl . '?action=garage;sa=insert_quartermile_images" id="update_images" enctype="multipart/form-data" method="post" name="update_images" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_quartermile;VID=' . $_GET['VID'] . ';QID=' . $_GET['QID'] . '#images" />
        <table border="0" cellpadding="3" cellspacing="1" width="100%">           
            <tr>
                <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_attach_image'] . '.<br />' . $txt['smfg_max_filesize'] . ': ' . $smfgSettings['max_image_kbytes'] . ' ' . $txt['smfg_kbytes'] . '<br />' . $txt['smfg_max_resolution'] . ': ' . $smfgSettings['max_image_resolution'] . 'x' . $smfgSettings['max_image_resolution'] . '</b></td>
                <td><input type="hidden" name="MAX_FILE_SIZE" value="' . $context['max_image_bytes'] . '" /><input type="file" size="30" name="FILE_UPLOAD" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,.jpg,.jpeg,.png,.gif,.webp,.bmp"/></td>
            </tr>';

        // Show the input for remote images if it is enabled
        if ($smfgSettings['enable_remote_images']) {
            echo '    
                <tr>
                    <td width="32%" align="right"><b>' . $txt['smfg_enter_remote_url'] . '</b></td>
                    <td><input name="url_image" type="text" size="40" maxlength="255" value="https://" /></td>
                </tr>';
        }

        echo '    
            <tr>
                <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                <td><textarea name="attach_desc" cols="60" rows="3"></textarea></td>
            </tr>    
            <tr>
                <td colspan="2" align="center" height="28"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['QID'] . '" name="QID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="insert_modification_images" type="submit" value="' . $txt['smfg_add_new_image'] . '" /></td>
            </tr>
        </table>
        </form>
        <table border="0" cellpadding="3" cellspacing="1" width="100%">
            <tr>
                <td colspan="2">
                <table border="0" cellpadding="3" cellspacing="1" width="100%">
                    <tr>
                        <td width="100%" align="center" colspan="3">' . $txt['smfg_edit_in_place_instructions'] . '.<div id="updateStatus"></div></td>
                    </tr>
                    <tr>
                        <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_image'] . '
                        </span></h4></div></td>
                        <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_description'] . '
                        </span></h4></div></td>
                        <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_manage'] . '
                        </span></h4></div></td>
                    </tr>';
        $count = 0;
        // If there is an image, show em
        if (isset($context['qmiles'][$count]['image_id'])) {
            // and keep showing em
            while (isset($context['qmiles'][$count]['image_id'])) {
                echo '                            
                            <tr class="tableRow">
                                <td align="center" valign="middle">' . $context['qmiles'][$count]['image'] . '</td>
                                <td align="center" valign="middle">
                                <div id="image' . $context['qmiles'][$count]['image_id'] . '" class="editin">';
                // If there is no desc, let them add one
                if (!empty($context['qmiles'][$count]['attach_desc'])) {
                    echo $context['qmiles'][$count]['attach_desc'];
                }
                echo '</div></td>
                                <td align="center" valign="middle">';
                if ($context['qmiles'][$count]['hilite'] != 1) {
                    echo '
                                        <form action="' . $scripturl . '?action=garage;sa=set_hilite_image_quartermile;VID=' . $_GET['VID'] . ';QID=' . $_GET['QID'] . ';image_id=' . $context['qmiles'][$count]['image_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="set_qmile_hilite_' . $context['qmiles'][$count]['image_id'] . '" id="set_qmile_hilite_' . $context['qmiles'][$count]['image_id'] . '" style="display: inline;">
                                            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_quartermile;VID=' . $_GET['VID'] . ';QID=' . $_GET['QID'] . '#images" />
                                            <a href="#" onClick="document.set_qmile_hilite_' . $context['qmiles'][$count]['image_id'] . '.submit(); return false;">' . $txt['smfg_set_hilite_image'] . '</a>
                                        </form>
                                        <br /><br />';
                } else {
                    echo
                        $txt['smfg_hilite_image'] . '<br /><br />';
                }
                echo '
                                <form action="' . $scripturl . '?action=garage;sa=remove_quartermile_image;VID=' . $_GET['VID'] . ';QID=' . $_GET['QID'] . ';image_id=' . $context['qmiles'][$count]['image_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_quartermile_image_' . $context['qmiles'][$count]['image_id'] . '" id="remove_quartermile_image_' . $context['qmiles'][$count]['image_id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_quartermile;VID=' . $_GET['VID'] . ';QID=' . $_GET['QID'] . '#images" />
                                <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_image'] . '\')) { document.remove_quartermile_image_' . $context['qmiles'][$count]['image_id'] . '.submit(); } else { return false; } return false;">' . $txt['smfg_remove_image'] . '</a>
                                </form>
                                </td>
                            </tr>';
                $count++;
            }
        } else {
            echo '
                        <tr>
                            <td colspan="3" align="center" valign="middle">' . $txt['smfg_no_quartermile_images'] . '</td>
                        </tr>';
        }
        echo '
                    </table> 
                </td>
            </tr>
        </table>  
        <script type="text/javascript">
         var frmvalidator = new Validator("update_images");
            
         frmvalidator.addValidation("attach_desc","maxlen=150","' . $txt['smfg_val_image_description_length'] . '");
         
        </script>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '
        </div>';
    }

    // Show the input for videos if it is enabled
    if ($smfgSettings['enable_quartermile_video']) {
        echo '
        <div class="garage_panel" id="options002" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_video'];

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
                <form action="' . $scripturl . '?action=garage;sa=insert_quartermile_video" id="update_video" enctype="multipart/form-data" method="post" name="update_video" style="padding:0; margin:0;">         
                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_quartermile;VID=' . $_GET['VID'] . ';QID=' . $_GET['QID'] . '#video" />
                <table border="0" cellpadding="3" cellspacing="1" width="100%">  
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_title'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="75" value="" name="video_title"/></td>
                    </tr>
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_url'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="255" value="https://" name="video_url"/>&nbsp;<span class="smalltext"><a href="' . $scripturl . '?action=garage;sa=supported_video" rel="shadowbox;width=260;height=400" title="' . $txt['smfg_video_instructions'] . '">Supported Sites</a></span></td>
                    </tr>';

        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="video_desc" cols="60" rows="3"></textarea></td>
                    </tr>   
                    <tr>
                        <td colspan="2" align="center" height="28"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['QID'] . '" name="QID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="insert_quartermile_video" type="submit" value="' . $txt['smfg_add_new_video'] . '" /></td>
                    </tr>
                </table>
                </form>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">  
                    <tr>
                        <td colspan="2">
                        <table border="0" cellpadding="3" cellspacing="1" width="100%">
                            <tr>
                                <td width="100%" align="center" colspan="3">' . $txt['smfg_edit_in_place_instructions'] . '<div id="updateStatus2"></div></td>
                            </tr>
                            <tr>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_video'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_title_description'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_manage'] . '
                                </span></h4></div></td>
                            </tr>';

        $count = 0;
        // If there is an video, show em
        if (isset($context['qmiles'][$count]['video_id'])) {
            // and keep showing em
            while (isset($context['qmiles'][$count]['video_id'])) {
                echo '                            
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=video;id=' . $context['qmiles'][$count]['video_id'] . '" rel="shadowbox;width=' . $context['qmiles'][$count]['video_width'] . ';height=' . $context['qmiles'][$count]['video_height'] . '" title="' . garage_title_clean('<b>' . $context['qmiles'][$count]['video_title'] . '</b> :: ' . $context['qmiles'][$count]['video_desc']) . '" class="smfg_videoTitle"><img src="' . $context['qmiles'][$count]['video_thumb'] . '" /></a></td>
                                        <td align="center" valign="middle">
                                        <div id="video_title' . $context['qmiles'][$count]['video_id'] . '" class="editin" style="font-weight: bold;">';
                // If there is no title, let them add one
                if (!empty($context['qmiles'][$count]['video_title'])) {
                    echo $context['qmiles'][$count]['video_title'];
                }
                echo '</div>
                                        <br />
                                        <div id="video' . $context['qmiles'][$count]['video_id'] . '" class="editin">';
                // If there is no desc, let them add one
                if (!empty($context['qmiles'][$count]['video_desc'])) {
                    echo $context['qmiles'][$count]['video_desc'];
                }
                echo '</div></td>
                                        <td align="center" valign="middle">';
                echo '
                                        <form action="' . $scripturl . '?action=garage;sa=remove_video;VID=' . $_GET['VID'] . ';video_id=' . $context['qmiles'][$count]['video_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_quartermile_video_' . $context['qmiles'][$count]['video_id'] . '" id="remove_quartermile_video_' . $context['qmiles'][$count]['video_id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_quartermile;VID=' . $_GET['VID'] . ';QID=' . $_GET['QID'] . '#video" />
                                        <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_video'] . '\')) { document.remove_quartermile_video_' . $context['qmiles'][$count]['video_id'] . '.submit(); } else { return false; } return false;">' . $txt['smfg_remove_video'] . '</a>
                                        </form>
                                        </td>
                                    </tr>';
                $count++;
            }
        } else {
            echo '
                                <tr>
                                    <td colspan="3" align="center" valign="middle">' . $txt['smfg_no_quartermile_videos'] . '</td>
                                </tr>';
        }
        echo '
                            </table> 
                        </td>
                    </tr>
                </table>  
        <script type="text/javascript">
         var frmvalidator = new Validator("update_video");
          
         frmvalidator.addValidation("video_desc","maxlen=150","' . $txt['smfg_val_video_description_length'] . '");
         frmvalidator.addValidation("video_title","req","' . $txt['smfg_val_enter_title'] . '");
         
        </script>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '
        </div>';
    }

    echo '
    <script type="text/javascript">
    <!--
        var lowest_tab = \'000\';
        var active_id = \'000\';
        if (document.location.hash == "")
        {
            change_tab(lowest_tab);
        }
        else if (document.location.hash == "#quartermile")
        {
            change_tab(\'000\');
        }
        else if (document.location.hash == "#images")
        {
            change_tab(\'001\');
        }
        else if (document.location.hash == "#videos")
        {
            change_tab(\'002\');
        }

    //-->

    </script>';

    echo smfg_footer();

}

function template_view_quartermile()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings, $boardurl;

    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $txt['smfg_view_quartermile'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    <tr>
        <td align="center" valign="top">';

    // Pending?
    if ($context['qmiles']['pending'] == '1') {

        echo '
                <table class="tborder" width="90%">
                    <tr>
                        <td>
                        <table border="0">
                            <tr>
                                <td align="center" valign="middle" width="40"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="" title="" /></td>
                                <td align="center" valign="middle">' . $txt['smfg_pending_item'] . '</td>
                            </tr>
                        </table>
                        </td>
                    </tr>
                </table><br />';

    }

    echo '
            
            <table border="0" width="70%">
                <tr>
                    <td align="left"><b>' . $txt['smfg_owner'] . '</b></td>
                </tr>
                <tr>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['user_vehicles']['user_id'] . '"><b>' . $context['qmiles']['owner'] . '</b></a></td>
                </tr>
                <tr>
                    <td align="left"><b>' . $txt['smfg_hilite_image'] . '</b></td>
                </tr>
                <tr>
                    <td align="center">', (!empty($context['hilite_image_location'])) ? '<a href="' . $context['hilite_image_location'] . '" rel="shadowbox" title="' . $context['qmiles']['quart'] . ' @ ' . $context['qmiles']['quartmph'] . ' :: ' . garage_title_clean($context['hilite_desc']) . '" class="smfg_imageTitle"><img src="' . $context['hilite_thumb_location'] . '" width="' . $context['hilite_thumb_width'] . '" height="' . $context['hilite_thumb_height'] . '" /></a>' : '', '</td>
                </tr>
            </table>
        </td>
        <td width="30%" valign="middle" align="center">
            <table border="0" cellspacing="1" cellpadding="3">
            <tr>
                <td align="left"><b>' . $txt['smfg_vehicle'] . '</b></td>
                <td align="left"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '">' . garage_title_clean($context['user_vehicles']['title']) . '</a><td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_rt'] . '</b></td>
                <td align="left">' . $context['qmiles']['rt'] . '<td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_sixty'] . '</b></td>
                <td align="left">' . $context['qmiles']['sixty'] . '<td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_three_thiry'] . '</b></td>
                <td align="left">' . $context['qmiles']['three'] . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_eighth'] . '</b></td>
                <td align="left">' . $context['qmiles']['eighth'] . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_eighth_mph'] . '</b></td>
                <td align="left">' . $context['qmiles']['eighthmph'] . ' MPH</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_thou'] . '</b></td>
                <td align="left">' . $context['qmiles']['thou'] . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_quart'] . '</b></td>
                <td align="left">' . $context['qmiles']['quart'] . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_quart_mph'] . '</b></td>
                <td align="left">' . $context['qmiles']['quartmph'] . ' MPH</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_date_created'] . '</b></td>
                <td align="left">' . date($context['date_format'], $context['qmiles']['date_created']) . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_date_updated'] . '</b></td>
                <td align="left">' . date($context['date_format'], $context['qmiles']['date_updated']) . '</td>
            </tr>
            </table>
        </td>
    </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    if ($smfgSettings['enable_quartermile_images'] || $smfgSettings['enable_quartermile_video']) {

        echo '
        <table cellpadding="0" cellspacing="0" border="0" style="margin: 7px 0 5px 10px" id="tab_table">
            <tr id="tab_row">
                <td>               
                <ul class="dropmenu">
                    <li id="button_g_images">
                        <a class="firstlevel" href="#images "onclick="change_tab(\'000\');" id="tab000">
                            <span class="firstlevel">' . $txt['smfg_images'] . '</span>
                        </a>
                    </li>';

        if (isset($context['qmiles'][$count]['video_id']) && $smfgSettings['enable_modification_video']) {

            echo '
                        <li id="button_g_videos">
                            <a class="firstlevel" href="#videos "onclick="change_tab(\'001\');" id="tab001">
                                <span class="firstlevel">' . $txt['smfg_videos'] . '</span>
                            </a>
                        </li>';

        }

        echo '
                </ul>
                </td>
            </tr>
        </table>';

        // Begin dynamic js divs
        echo '        
        <div class="garage_panel" id="options000" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_images'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';
        $count = 0;

        if (isset($context['qmiles'][$count]['image_id'])) {
            echo '
            <tr>
                <td valign="middle">';
            while (isset($context['qmiles'][$count]['image_id'])) {
                echo '
                <a href="' . $context['qmiles'][$count]['attach_location'] . '" rel="shadowbox[qmiles]" title="' . garage_title_clean($context['qmiles']['quart'] . ' @ ' . $context['qmiles']['quartmph'] . ' :: ' . $context['qmiles'][$count]['attach_desc']) . '" class="smfg_imageTitle"><img src="' . $context['qmiles'][$count]['attach_thumb_location'] . '" width="' . $context['qmiles'][$count]['attach_thumb_width'] . '" height="' . $context['qmiles'][$count]['attach_thumb_height'] . '" /></a>';
                $count++;
            }
            echo '
                </td>
            </tr>';
        } else {
            echo '
            <tr>
                <td align="center">' . $txt['smfg_no_quartermile_images'] . '</td>
            </tr>';
        }
        echo '
        </table>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '  
        </div>';

        echo '
        <div class="garage_panel" id="options001" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_videos'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';
        $count = 0;
        if (isset($context['qmiles'][$count]['video_id'])) {
            echo '
            <tr>
                <td valign="middle">';
            while (isset($context['qmiles'][$count]['video_id'])) {
                echo '
                <a href="' . $scripturl . '?action=garage;sa=video;id=' . $context['qmiles'][$count]['video_id'] . '" rel="shadowbox[video];width=' . $context['qmiles'][$count]['video_width'] . ';height=' . $context['qmiles'][$count]['video_height'] . ';" title="' . garage_title_clean('<b>' . $context['qmiles'][$count]['video_title'] . '</b> :: ' . $context['qmiles'][$count]['video_desc']) . '" class="smfg_videoTitle"><img src="' . $context['qmiles'][$count]['video_thumb'] . '" /></a>';
                $count++;
            }
            echo '
                </td>
            </tr>';
        } else {
            echo '
            <tr>
                <td align="center">' . $txt['smfg_no_quartermile_videos'] . '</td>
            </tr>';
        }
        echo '  
        </table>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '     
        </div>

        <script type="text/javascript">
        <!--
            var lowest_tab = \'000\';
            var active_id = \'000\';
            if (document.location.hash == "")
            {
                change_tab(lowest_tab);
            }
            else if (document.location.hash == "#images")
            {
                change_tab(\'000\');
            }
            else if (document.location.hash == "#videos")
            {
                change_tab(\'001\');
            }

        //-->

        </script>';

    }

    echo smfg_footer();

}

function template_add_dynorun()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_add_dynorun'];

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
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Submitted dynocenter?
    if ($_SESSION['added_dynocenter']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_dynocenter_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a dyno is added
        unset($_SESSION['added_dynocenter']);
    }

    echo '
        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=insert_dynorun" enctype="multipart/form-data" method="post" name="add_dynorun" id="add_dynorun" style="padding:0; margin:0;">
                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '#dynoruns" />

                <table width="100%" cellpadding="3" cellspacing="1" border="0">
                    <tr>
                        <td align="right" width="35%"><b>' . $txt['smfg_dynocenter'] . '</b></td>
                        <td>
                        <select id="dynocenter_id" name="dynocenter_id">
                        <option value="">' . $txt['smfg_select_dynocenter'] . '</option>
                        <option value="">------</option>';
    echo dynocenter_select();
    echo '
                        </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=dynocenter" rel="shadowbox;width=620;height=560" title="Garage :: Submit Dynocenter">' . $txt['smfg_here'] . '</a>';
    }
    echo '
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="35%"><b>' . $txt['smfg_bhp'] . '</b><br />' . $txt['smfg_enter_bhp'] . '</td>
                        <td>
                        <input name="bhp" type="text" size="15" value ="" />&nbsp;
                        <select id="bhp_unit" name="bhp_unit">
                        <option value="">' . $txt['smfg_please_select'] . '</option>
                        <option value="">------</option>
                        <option value="wheel" >' . $txt['smfg_wheel'] . '</option>
                        <option value="hub" >' . $txt['smfg_hub'] . '</option>
                        <option value="flywheel" >' . $txt['smfg_flywheel'] . '</option>
                        </select>&nbsp;' . $txt['smfg_required'] . '
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="35%"><b>' . $txt['smfg_torque'] . '</b><br />' . $txt['smfg_enter_torque'] . '</td>
                        <td>
                        <input name="torque" type="text" size="15" value ="" />&nbsp;
                        <select id="torque_unit" name="torque_unit">
                        <option value="">' . $txt['smfg_please_select'] . '</option>
                        <option value="">------</option>
                        <option value="wheel" >' . $txt['smfg_wheel'] . '</option>
                        <option value="hub" >' . $txt['smfg_hub'] . '</option>
                        <option value="flywheel" >' . $txt['smfg_flywheel'] . '</option>
                        </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="35%"><b>' . $txt['smfg_boost'] . '</b><br />' . $txt['smfg_enter_boost'] . '</td>
                        <td>
                        <input name="boost" type="text" size="15" value ="" />&nbsp;
                        <select id="boost_unit" name="boost_unit">
                        <option value="">' . $txt['smfg_please_select'] . '</option>
                        <option value="">------</option>
                        <option value="PSI" >' . $txt['smfg_psi'] . '</option>
                        <option value="BAR" >' . $txt['smfg_bar'] . '</option>
                        </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="35%"><b>' . $txt['smfg_nitrous'] . '</b><br />' . $txt['smfg_enter_nitrous'] . '</td>
                        <td>
                        <select id="nitrous" name="nitrous">
                        <option value="">' . $txt['smfg_please_select'] . '</option>
                        <option value="">------</option>
                        <option value="0" >' . $txt['smfg_no_nos'] . '</option>
                        <option value="25" >25 ' . $txt['smfg_bhp'] . ' ' . $txt['smfg_shot'] . '</option>
                        <option value="50" >50 ' . $txt['smfg_bhp'] . ' ' . $txt['smfg_shot'] . '</option>
                        <option value="75" >75 ' . $txt['smfg_bhp'] . ' ' . $txt['smfg_shot'] . '</option>
                        <option value="100" >100 ' . $txt['smfg_bhp'] . ' ' . $txt['smfg_shot'] . '</option>
                        </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="35%"><b>' . $txt['smfg_peakpoint'] . '</b><br />' . $txt['smfg_rpm_at_peak'] . '</td>

                        <td><input name="peakpoint" type="text" size="15" value ="" /></td>
                    </tr>';

    // Show the input for images if it is enabled
    if ($smfgSettings['enable_dynorun_images']) {
        echo '
                    <tr>
                        <td colspan="2"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_image_attachments'] . '
                        </span></h4></div></td>
                    </tr>
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_attach_image'] . '.<br />' . $txt['smfg_max_filesize'] . ': ' . $smfgSettings['max_image_kbytes'] . ' ' . $txt['smfg_kbytes'] . '<br />' . $txt['smfg_max_resolution'] . ': ' . $smfgSettings['max_image_resolution'] . 'x' . $smfgSettings['max_image_resolution'] . '</b></td>
                        <td><input type="hidden" name="MAX_FILE_SIZE" value="' . $context['max_image_bytes'] . '" /><input type="file" size="30" name="FILE_UPLOAD" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,.jpg,.jpeg,.png,.gif,.webp,.bmp"/></td>
                    </tr>';

        // Show the input for remote images if it is enabled
        if ($smfgSettings['enable_remote_images']) {
            echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_enter_remote_url'] . '</b></td>
                        <td><input name="url_image" type="text" size="40" maxlength="255" value="https://" /></td>
                    </tr>';
        }
        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="attach_desc" cols="60" rows="3"></textarea></td>
                    </tr>';
    }
    // Show the input for videos if it is enabled
    if ($smfgSettings['enable_dynorun_video']) {
        echo '
                    <tr>
                        <td colspan="2"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_hosted_videos'] . '
                        </span></h4></div></td>
                    </tr>
                    
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_title'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="75" value="" name="video_title"/></td>
                    </tr>
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_url'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="255" value="https://" name="video_url"/>&nbsp;<span class="smalltext"><a href="' . $scripturl . '?action=garage;sa=supported_video" rel="shadowbox;width=260;height=400" title="' . $txt['smfg_video_instructions'] . '">Supported Sites</a></span></td>
                    </tr>';

        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="video_desc" cols="60" rows="3"></textarea></td>
                    </tr>';

    }
    echo '
                    <tr>
                        <td align="center" height="28" colspan="2"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="" name="DID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_add_dynorun'] . '" /></td>
                    </tr>
                </table>
                </form>
                <script type="text/javascript">
                var frmvalidator = new Validator("add_dynorun");
                var frm = document.forms["add_dynorun"];
                
                frmvalidator.addValidation("dynocenter_id","req","' . $txt['smfg_val_select_dynocenter'] . '");
                frmvalidator.addValidation("dynocenter_id","dontselect=0","' . $txt['smfg_val_select_dynocenter'] . '");
                frmvalidator.addValidation("dynocenter_id","dontselect=1","' . $txt['smfg_val_select_dynocenter'] . '");
                
                frmvalidator.addValidation("bhp","req","' . $txt['smfg_val_enter_bhp'] . '");
                frmvalidator.addValidation("bhp","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_bhp_restrictions'] . '");
                frmvalidator.addValidation("bhp_unit","req","' . $txt['smfg_val_select_bhp_unit'] . '");
                
                frmvalidator.addValidation("torque","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_torque_restrictions'] . '");
                frmvalidator.addValidation("boost","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_boost_restrictions'] . '");
                    
                frmvalidator.addValidation("peakpoint","regexp=^[.0-9]{1,9}$","' . $txt['smfg_val_peakpoint_restrictions'] . '");';
    if ($smfgSettings['enable_vehicle_images']) {
        echo '
                        frmvalidator.addValidation("attach_desc","maxlen=150","' . $txt['smfg_val_image_description_length'] . '");';
    }
    if ($smfgSettings['enable_vehicle_video']) {
        echo '
                        frmvalidator.addValidation("video_desc","maxlen=150","' . $txt['smfg_val_video_description_length'] . '");';
    }
    echo '
                </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_edit_dynorun()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_edit_dynorun'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '
    <table cellpadding="0" cellspacing="0" border="0" style="margin: 7px 0 5px 10px" id="tab_table">
        <tr id="tab_row">
            <td>               
            <ul class="dropmenu">
                <li id="button_g_dynorun">
                    <a class="firstlevel" href="#dynorun" onclick="change_tab(\'000\');" id="tab000">
                        <span class="firstlevel">' . $txt['smfg_dynorun'] . '</span>
                    </a>
                </li>';

    if ($smfgSettings['enable_vehicle_images']) {

        echo '
                    <li id="button_g_images">
                        <a class="firstlevel" href="#images" onclick="change_tab(\'001\');" id="tab001">
                            <span class="firstlevel">' . $txt['smfg_images'] . '</span>
                        </a>
                    </li>';

    }

    if ($smfgSettings['enable_vehicle_video']) {

        echo '
                    <li id="button_g_videos">
                        <a class="firstlevel" href="#videos" onclick="change_tab(\'002\');" id="tab002">
                            <span class="firstlevel">' . $txt['smfg_videos'] . '</span>
                        </a>
                    </li>';

    }

    echo '
            </ul>
            </td>
        </tr>
    </table>';

    // Begin dynamic js divs
    echo '                     
    <div class="garage_panel" id="options000" style="display: none;">';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_dynorun'];

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
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Submitted dynocenter?
    if ($_SESSION['added_dynocenter']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_dynocenter_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a dyno is added
        unset($_SESSION['added_dynocenter']);
    }

    echo '
        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=update_dynorun" enctype="multipart/form-data" method="post" name="edit_dynorun" id="edit_dynorun" style="padding:0; margin:0;">
            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . ';#dynoruns" />
            <table width="100%" cellpadding="3" cellspacing="1" border="0">
                <tr>
                    <td align="right" width="40%"><b>' . $txt['smfg_dynocenter'] . '</b></td>
                    <td>
                    <select id="dynocenter_id" name="dynocenter_id">
                    <option value="">' . $txt['smfg_select_dynocenter'] . '</option>
                    <option value="">------</option>';
    echo dynocenter_select($context['dynoruns']['dynocenter_id']);
    echo '
                    </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=dynocenter" rel="shadowbox;width=620;height=560" title="Garage :: Submit Dynocenter">' . $txt['smfg_here'] . '</a>';
    }
    echo '
                    </td>
                </tr>
                <tr>
                    <td align="right" width="40%"><b>' . $txt['smfg_bhp'] . '</b><br />' . $txt['smfg_enter_bhp'] . '</td>
                    <td>';
    echo '
                    <input name="bhp" type="text" size="15" value ="' . $context['dynoruns']['bhp'] . '" />&nbsp;
                    <select id="bhp_unit" name="bhp_unit">
                    <option value="">' . $txt['smfg_please_select'] . '</option>
                    <option value="">------</option>
                    <option value="wheel" ' . $context['bhp_wheel'] . '>' . $txt['smfg_wheel'] . '</option>
                    <option value="hub" ' . $context['bhp_hub'] . '>' . $txt['smfg_hub'] . '</option>
                    <option value="flywheel" ' . $context['bhp_fly'] . '>' . $txt['smfg_flywheel'] . '</option>
                    </select>&nbsp;' . $txt['smfg_required'] . '
                    </td>
                </tr>
                <tr>
                    <td align="right" width="40%"><b>' . $txt['smfg_torque'] . '</b><br />' . $txt['smfg_enter_torque'] . '</td>
                    <td>';
    echo '
                    <input name="torque" type="text" size="15" value ="' . $context['dynoruns']['torque'] . '" />&nbsp;
                    <select id="torque_unit" name="torque_unit">
                    <option value="">' . $txt['smfg_please_select'] . '</option>
                    <option value="">------</option>
                    <option value="wheel" ' . $context['torque_wheel'] . '>' . $txt['smfg_wheel'] . '</option>
                    <option value="hub" ' . $context['torque_hub'] . '>' . $txt['smfg_hub'] . '</option>
                    <option value="flywheel" ' . $context['torque_fly'] . '>' . $txt['smfg_flywheel'] . '</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td align="right" width="40%"><b>' . $txt['smfg_boost'] . '</b><br />' . $txt['smfg_enter_boost'] . '</td>
                    <td>';
    echo '
                    <input name="boost" type="text" size="15" value ="' . $context['dynoruns']['boost'] . '" />&nbsp;
                    <select id="boost_unit" name="boost_unit">
                    <option value="">' . $txt['smfg_please_select'] . '</option>
                    <option value="">------</option>
                    <option value="PSI" ' . $context['psi'] . '>' . $txt['smfg_psi'] . '</option>
                    <option value="BAR" ' . $context['bar'] . '>' . $txt['smfg_bar'] . '</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td align="right" width="40%"><b>' . $txt['smfg_nitrous'] . '</b><br />' . $txt['smfg_enter_nitrous'] . '</td>
                    <td>';
    echo '
                    <select id="nitrous" name="nitrous">
                    <option value="">' . $txt['smfg_please_select'] . '</option>
                    <option value="">------</option>
                    <option value="0" ' . $context['n0'] . '>' . $txt['smfg_no_nos'] . '</option>
                    <option value="25" ' . $context['n25'] . '>25 ' . $txt['smfg_bhp'] . ' ' . $txt['smfg_shot'] . '</option>
                    <option value="50" ' . $context['n50'] . '>50 ' . $txt['smfg_bhp'] . ' ' . $txt['smfg_shot'] . '</option>
                    <option value="75" ' . $context['n75'] . '>75 ' . $txt['smfg_bhp'] . ' ' . $txt['smfg_shot'] . '</option>
                    <option value="100" ' . $context['n100'] . '>100 ' . $txt['smfg_bhp'] . ' ' . $txt['smfg_shot'] . '</option>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td align="right" width="40%"><b>' . $txt['smfg_peakpoint'] . '</b><br />' . $txt['smfg_rpm_at_peak'] . '</td>

                    <td><input name="peakpoint" type="text" size="15" value ="' . $context['dynoruns']['peakpoint'] . '" /></td>
                </tr>               
                <tr>
                    <td align="center" height="28" colspan="2"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['DID'] . '" name="DID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" /><input name="submit" type="submit" value="' . $txt['smfg_update_dynorun'] . '" /></td>
                </tr>
            </table>
            </form>
            <script type="text/javascript">
            var frmvalidator = new Validator("edit_dynorun");
            var frm = document.forms["edit_dynorun"];
            
            frmvalidator.addValidation("dynocenter_id","req","' . $txt['smfg_val_select_dynocenter'] . '");
            frmvalidator.addValidation("dynocenter_id","dontselect=0","' . $txt['smfg_val_select_dynocenter'] . '");
            frmvalidator.addValidation("dynocenter_id","dontselect=1","' . $txt['smfg_val_select_dynocenter'] . '");
            
            frmvalidator.addValidation("bhp","req","' . $txt['smfg_val_enter_bhp'] . '");
            frmvalidator.addValidation("bhp","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_bhp_restrictions'] . '");
            frmvalidator.addValidation("bhp_unit","req","' . $txt['smfg_val_select_bhp_unit'] . '");
            
            frmvalidator.addValidation("torque","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_torque_restrictions'] . '");
            frmvalidator.addValidation("boost","regexp=^[.0-9]{1,7}$","' . $txt['smfg_val_boost_restrictions'] . '");
                
            frmvalidator.addValidation("peakpoint","regexp=^[.0-9]{1,9}$","' . $txt['smfg_val_peakpoint_restrictions'] . '");
            </script>';
    echo '
            </td>
        </tr>    
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    </div>';
    // Show the input for images if it is enabled
    if ($smfgSettings['enable_dynorun_images']) {
        echo '
        <div class="garage_panel" id="options001" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_images'];

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
                
                <form action="' . $scripturl . '?action=garage;sa=insert_dynorun_images" id="update_images" enctype="multipart/form-data" method="post" name="update_images" style="padding:0; margin:0;">
                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_dynorun;VID=' . $_GET['VID'] . ';DID=' . $_GET['DID'] . '#images" />
                <table border="0" cellpadding="3" cellspacing="1" width="100%">           
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_attach_image'] . '.<br />' . $txt['smfg_max_filesize'] . ': ' . $smfgSettings['max_image_kbytes'] . ' ' . $txt['smfg_kbytes'] . '<br />' . $txt['smfg_max_resolution'] . ': ' . $smfgSettings['max_image_resolution'] . 'x' . $smfgSettings['max_image_resolution'] . '</b></td>
                        <td><input type="hidden" name="MAX_FILE_SIZE" value="' . $context['max_image_bytes'] . '" /><input type="file" size="30" name="FILE_UPLOAD" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,.jpg,.jpeg,.png,.gif,.webp,.bmp"/></td>
                    </tr>';

        // Show the input for remote images if it is enabled
        if ($smfgSettings['enable_remote_images']) {
            echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_enter_remote_url'] . '</b></td>
                        <td><input name="url_image" type="text" size="40" maxlength="255" value="https://" /></td>
                    </tr>';
        }

        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="attach_desc" cols="60" rows="3"></textarea></td>
                    </tr>    
                    <tr>
                        <td colspan="2" align="center" height="28"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['DID'] . '" name="DID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="insert_dynorun_images" type="submit" value="' . $txt['smfg_add_new_image'] . '" /></td>
                    </tr>
                </table>
                </form>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">
                    <tr>
                        <td colspan="2">
                        <table border="0" cellpadding="3" cellspacing="1" width="100%">
                            <tr>
                                <td width="100%" align="center" colspan="3">' . $txt['smfg_edit_in_place_instructions'] . '.<div id="updateStatus"></div></td>
                            </tr>
                            <tr>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_image'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_description'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_manage'] . '
                                </span></h4></div></td>
                            </tr>';
        $count = 0;
        // If there is an image, show em
        if (isset($context['dynoruns'][$count]['image_id'])) {
            // and keep showing em
            while (isset($context['dynoruns'][$count]['image_id'])) {
                echo '                            
                                    <tr class="tableRow">
                                        <td align="center" valign="middle">' . $context['dynoruns'][$count]['image'] . '</td>
                                        <td align="center" valign="middle">
                                            <div id="image' . $context['dynoruns'][$count]['image_id'] . '" class="editin">';
                // If there is no desc, let them add one
                if (!empty($context['dynoruns'][$count]['attach_desc'])) {
                    echo $context['dynoruns'][$count]['attach_desc'];
                }
                echo '</div></td>
                                        <td align="center" valign="middle">';
                if ($context['dynoruns'][$count]['hilite'] != 1) {
                    echo '
                                            <form action="' . $scripturl . '?action=garage;sa=set_hilite_image_dynorun;VID=' . $_GET['VID'] . ';DID=' . $_GET['DID'] . ';image_id=' . $context['dynoruns'][$count]['image_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="set_dynorun_hilite_' . $context['dynoruns'][$count]['image_id'] . '" id="set_dynorun_hilite_' . $context['dynoruns'][$count]['image_id'] . '" style="display: inline;">
                                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_dynorun;VID=' . $_GET['VID'] . ';DID=' . $_GET['DID'] . '#images" />
                                                <a href="#" onClick="document.set_dynorun_hilite_' . $context['dynoruns'][$count]['image_id'] . '.submit(); return false;">' . $txt['smfg_set_hilite_image'] . '</a>
                                            </form>
                                            <br /><br />';
                } else {
                    echo
                        $txt['smfg_hilite_image'] . '<br /><br />';
                }
                echo '
                                        <form action="' . $scripturl . '?action=garage;sa=remove_dynorun_image;VID=' . $_GET['VID'] . ';DID=' . $_GET['DID'] . ';image_id=' . $context['dynoruns'][$count]['image_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_dynorun_image_' . $context['dynoruns'][$count]['image_id'] . '" id="remove_dynorun_image_' . $context['dynoruns'][$count]['image_id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_dynorun;VID=' . $_GET['VID'] . ';DID=' . $_GET['DID'] . '#images" />
                                        <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_image'] . '\')) { document.remove_dynorun_image_' . $context['dynoruns'][$count]['image_id'] . '.submit(); } else { return false; } return false;">' . $txt['smfg_remove_image'] . '</a>
                                        </form>
                                        </td>
                                    </tr>';
                $count++;
            }
        } else {
            echo '
                                <tr>
                                    <td colspan="3" align="center" valign="middle">' . $txt['smfg_no_dynorun_image'] . '</td>
                                </tr>';
        }
        echo '
                            </table> 
                        </td>
                    </tr>
                </table> 
                <script type="text/javascript">
                 var frmvalidator = new Validator("update_images");
                    
                    frmvalidator.addValidation("attach_desc","maxlen=150","' . $txt['smfg_val_image_description_length'] . '");
                 
                </script>            
                </td>
            </tr>
        </table>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '
        </div>';
    }
    // Show the input for videos if it is enabled
    if ($smfgSettings['enable_dynorun_video']) {
        echo '
        <div class="garage_panel" id="options002" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_video'];

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
                                    
                <form action="' . $scripturl . '?action=garage;sa=insert_dynorun_video" id="update_video" enctype="multipart/form-data" method="post" name="update_video" style="padding:0; margin:0;">         
                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_dynorun;VID=' . $_GET['VID'] . ';DID=' . $_GET['DID'] . '#video" />
                <table border="0" cellpadding="3" cellspacing="1" width="100%">  
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_title'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="75" value="" name="video_title"/></td>
                    </tr>
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_url'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="255" value="https://" name="video_url"/>&nbsp;<span class="smalltext"><a href="' . $scripturl . '?action=garage;sa=supported_video" rel="shadowbox;width=260;height=400" title="' . $txt['smfg_video_instructions'] . '">Supported Sites</a></span></td>
                    </tr>';

        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="video_desc" cols="60" rows="3"></textarea></td>
                    </tr>   
                    <tr>
                        <td colspan="2" align="center" height="28"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['DID'] . '" name="DID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="insert_dynorun_video" type="submit" value="' . $txt['smfg_add_new_video'] . '" /></td>
                    </tr>
                </table>
                </form>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">  
                    <tr>
                        <td colspan="2">
                        <table border="0" cellpadding="3" cellspacing="1" width="100%">
                            <tr>
                                <td width="100%" align="center" colspan="3">' . $txt['smfg_edit_in_place_instructions'] . '<div id="updateStatus2"></div></td>
                            </tr>
                            <tr>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_video'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_title_description'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_manage'] . '
                                </span></h4></div></td>
                            </tr>';

        $count = 0;
        // If there is an video, show em
        if (isset($context['dynoruns'][$count]['video_id'])) {
            // and keep showing em
            while (isset($context['dynoruns'][$count]['video_id'])) {
                echo '                            
                                    <tr class="tableRow">
                                        <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=video;id=' . $context['dynoruns'][$count]['video_id'] . '" rel="shadowbox;width=' . $context['dynoruns'][$count]['video_width'] . ';height=' . $context['dynoruns'][$count]['video_height'] . '" title="' . garage_title_clean('<b>' . $context['dynoruns'][$count]['video_title'] . '</b> :: ' . $context['dynoruns'][$count]['video_desc']) . '" class="smfg_videoTitle"><img src="' . $context['dynoruns'][$count]['video_thumb'] . '" /></a></td>
                                        <td align="center" valign="middle">
                                        <div id="video_title' . $context['dynoruns'][$count]['video_id'] . '" class="editin" style="font-weight: bold;">';
                // If there is no title, let them add one
                if (!empty($context['dynoruns'][$count]['video_title'])) {
                    echo $context['dynoruns'][$count]['video_title'];
                }
                echo '</div>
                                        <br />
                                        <div id="video' . $context['dynoruns'][$count]['video_id'] . '" class="editin">';
                // If there is no desc, let them add one
                if (!empty($context['dynoruns'][$count]['video_desc'])) {
                    echo $context['dynoruns'][$count]['video_desc'];
                }
                echo '</div></td>
                                        <td align="center" valign="middle">';
                echo '
                                        <form action="' . $scripturl . '?action=garage;sa=remove_video;VID=' . $_GET['VID'] . ';video_id=' . $context['dynoruns'][$count]['video_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_dynorun_video_' . $context['dynoruns'][$count]['video_id'] . '" id="remove_dynorun_video_' . $context['dynoruns'][$count]['video_id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_dynorun;VID=' . $_GET['VID'] . ';DID=' . $_GET['DID'] . '#video" />
                                        <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_video'] . '\')) { document.remove_dynorun_video_' . $context['dynoruns'][$count]['video_id'] . '.submit(); } else { return false; } return false;">' . $txt['smfg_remove_video'] . '</a>
                                        </form>
                                        </td>
                                    </tr>';
                $count++;
            }
        } else {
            echo '
                                <tr>
                                    <td colspan="3" align="center" valign="middle">' . $txt['smfg_no_dynorun_videos'] . '</td>
                                </tr>';
        }
        echo '
                            </table> 
                        </td>
                    </tr>
                </table>  
                </td>
            </tr>
        </table>
        <script type="text/javascript">
         var frmvalidator = new Validator("update_video");
          
         frmvalidator.addValidation("video_desc","maxlen=150","' . $txt['smfg_val_video_description_length'] . '");
         frmvalidator.addValidation("video_title","req","' . $txt['smfg_val_enter_title'] . '");
         
        </script>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '
        </div>';
    }

    echo '
    <script type="text/javascript">
    <!--
        var lowest_tab = \'000\';
        var active_id = \'000\';
        if (document.location.hash == "")
        {
            change_tab(lowest_tab);
        }
        else if (document.location.hash == "#dynorun")
        {
            change_tab(\'000\');
        }
        else if (document.location.hash == "#images")
        {
            change_tab(\'001\');
        }
        else if (document.location.hash == "#videos")
        {
            change_tab(\'002\');
        }

    //-->

    </script>';

    echo smfg_footer();

}

function template_view_dynorun()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings, $boardurl;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_view_dynorun'];

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
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    <tr>
        <td align="center" valign="top">';

    // Pending?
    if ($context['dynoruns']['pending'] == '1') {
        echo '
            <table class="tborder" width="90%">
                <tr>
                    <td>
                    <table border="0">
                        <tr>
                            <td align="center" valign="middle" width="40"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="" title="" /></td>
                            <td align="center" valign="middle">' . $txt['smfg_pending_item'] . '</td>
                        </tr>
                    </table>
                    </td>
                </tr>
            </table><br />';
    }

    echo '
            
            <table border="0" width="70%">
                <tr>
                    <td align="left"><b>' . $txt['smfg_owner'] . '</b></td>
                </tr>
                <tr>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['user_vehicles']['user_id'] . '"><b>' . $context['dynoruns']['owner'] . '</b></a></td>
                </tr>
                <tr>
                    <td align="left"><b>' . $txt['smfg_hilite_image'] . '</b></td>
                </tr>
                <tr>
                    <td align="center">', (!empty($context['hilite_image_location'])) ? '<a href="' . $context['hilite_image_location'] . '" rel="shadowbox" title="' . $context['dynoruns']['bhp'] . ' ' . $context['dynoruns']['bhp_unit'] . ' :: ' . garage_title_clean($context['hilite_desc']) . '" class="smfg_imageTitle"><img src="' . $context['hilite_thumb_location'] . '" width="' . $context['hilite_thumb_width'] . '" height="' . $context['hilite_thumb_height'] . '" /></a>' : '', '</td>
                </tr>
            </table>
        </td>
        <td width="30%" valign="middle" align="center">
            <table border="0" cellspacing="1" cellpadding="3">
            <tr>
                <td align="left"><b>' . $txt['smfg_vehicle'] . '</b></td>
                <td align="left"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '">' . garage_title_clean($context['user_vehicles']['title']) . '</a><td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_dynocenter'] . '</b></td>
                <td align="left"><a href="' . $scripturl . '?action=garage;sa=dc_review;BID=' . $context['dynoruns']['dynocenter_id'] . '">' . garage_title_clean($context['dynoruns']['dynocenter']) . '</a><td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_bhp'] . '</b></td>
                <td align="left">' . $context['dynoruns']['bhp'] . ' ' . $context['dynoruns']['bhp_unit'] . '<td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_torque'] . '</b></td>
                <td align="left">' . $context['dynoruns']['torque'] . ' ' . $context['dynoruns']['torque_unit'] . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_boost'] . '</b></td>
                <td align="left">' . $context['dynoruns']['boost'] . ' ' . $context['dynoruns']['boost_unit'] . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_nitrous'] . '</b></td>
                <td align="left">' . $context['dynoruns']['nitrous'] . ' ' . $txt['smfg_shot'] . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_peakpoint'] . '</b></td>
                <td align="left">' . $context['dynoruns']['peakpoint'] . ' ' . $txt['smfg_rpm'] . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_date_created'] . '</b></td>
                <td align="left">' . date($context['date_format'], $context['dynoruns']['date_created']) . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_date_updated'] . '</b></td>
                <td align="left">' . date($context['date_format'], $context['dynoruns']['date_updated']) . '</td>
            </tr>
            </table>
        </td>
    </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    if ($smfgSettings['enable_dynorun_images'] || $smfgSettings['enable_dynorun_video']) {

        echo '
        <table cellpadding="0" cellspacing="0" border="0" style="margin: 7px 0 5px 10px" id="tab_table">
            <tr id="tab_row">
                <td>               
                <ul class="dropmenu">
                    <li id="button_g_images">
                        <a class="firstlevel" href="#images "onclick="change_tab(\'000\');" id="tab000">
                            <span class="firstlevel">' . $txt['smfg_images'] . '</span>
                        </a>
                    </li>';

        if (isset($context['dynoruns'][$count]['video_id']) && $smfgSettings['enable_modification_video']) {

            echo '
                        <li id="button_g_videos">
                            <a class="firstlevel" href="#videos "onclick="change_tab(\'001\');" id="tab001">
                                <span class="firstlevel">' . $txt['smfg_videos'] . '</span>
                            </a>
                        </li>';

        }

        echo '
                </ul>
                </td>
            </tr>
        </table>';

        // Begin dynamic js divs
        echo '        
        <div class="garage_panel" id="options000" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_images'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

        $count = 0;

        if (isset($context['dynoruns'][$count]['image_id'])) {
            echo '
            <tr>
                <td valign="middle">';
            while (isset($context['dynoruns'][$count]['image_id'])) {
                echo '
                <a href="' . $context['dynoruns'][$count]['attach_location'] . '" rel="shadowbox[dynoruns]" title="' . garage_title_clean($context['dynoruns']['bhp'] . ' ' . $context['dynoruns']['bhp_unit'] . ' :: ' . $context['dynoruns'][$count]['attach_desc']) . '" class="smfg_imageTitle"><img src="' . $context['dynoruns'][$count]['attach_thumb_location'] . '" width="' . $context['dynoruns'][$count]['attach_thumb_width'] . '" height="' . $context['dynoruns'][$count]['attach_thumb_height'] . '" /></a>';
                $count++;
            }
            echo '
                </td>
            </tr>';
        } else {
            echo '
            <tr>
                <td align="center">' . $txt['smfg_no_dynorun_images'] . '</td>
            </tr>';
        }
        echo '
        </table>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '   
        </div>
        <div class="garage_panel" id="options001" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_videos'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

        $count = 0;

        if (isset($context['dynoruns'][$count]['video_id'])) {
            echo '
            <tr>
                <td valign="middle">';
            while (isset($context['dynoruns'][$count]['video_id'])) {
                echo '
                <a href="' . $scripturl . '?action=garage;sa=video;id=' . $context['dynoruns'][$count]['video_id'] . '" rel="shadowbox[video];width=' . $context['dynoruns'][$count]['video_width'] . ';height=' . $context['dynoruns'][$count]['video_height'] . ';" title="' . garage_title_clean('<b>' . $context['dynoruns'][$count]['video_title'] . '</b> :: ' . $context['dynoruns'][$count]['video_desc']) . '" class="smfg_videoTitle"><img src="' . $context['dynoruns'][$count]['video_thumb'] . '" /></a>';
                $count++;
            }
            echo '
                </td>
            </tr>';
        } else {
            echo '
            <tr>
                <td align="center">' . $txt['smfg_no_dynorun_videos'] . '</td>
            </tr>';
        }
        echo '  
        </table>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '     
        </div>

        <script type="text/javascript">
        <!--
            var lowest_tab = \'000\';
            var active_id = \'000\';
            if (document.location.hash == "")
            {
                change_tab(lowest_tab);
            }
            else if (document.location.hash == "#images")
            {
                change_tab(\'000\');
            }
            else if (document.location.hash == "#videos")
            {
                change_tab(\'001\');
            }

        //-->

        </script>';

    }

    echo smfg_footer();

}

function template_add_laptime()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_add_laptime'];

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
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Submitted track?
    if ($_SESSION['added_track']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_track_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a dyno is added
        unset($_SESSION['added_track']);
    }

    echo '
        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=insert_laptime" enctype="multipart/form-data" method="post" name="add_lap" id="add_lap" style="padding:0; margin:0;">
                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '#laps" />
                <table width="100%" cellpadding="3" cellspacing="1" border="0">
                    <tr>
                        <td align="right" width="30%"><b>' . $txt['smfg_track'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td>
                        <select id="track_id" name="track_id" >
                        <option value="">' . $txt['smfg_select_track'] . '</option>
                        <option value="">------</option>';
    echo track_select();
    echo '
                        </select>';
    if ($smfgSettings['enable_user_add_track']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_track" rel="shadowbox;width=620;height=200" title="Garage :: Submit Track">' . $txt['smfg_here'] . '</a>';
    }
    echo '
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="30%"><b>' . $txt['smfg_track'] . ' ' . $txt['smfg_condition'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td>
                        <select id="condition" name="condition" >
                        <option value="">' . $txt['smfg_select_condition'] . '</option>
                        <option value="">------</option>';
    echo track_condition_select();
    echo '
                        </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="30%"><b>' . $txt['smfg_lap_type'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td>
                        <select id="type" name="type" >
                        <option value="">' . $txt['smfg_select_type'] . '</option>
                        <option value="">------</option>';
    echo lap_type_select();
    echo '
                        </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="35%"><b>' . $txt['smfg_laptime'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td><b>M</b><input name="minute" type="text" size="2" maxlength="2" value="" />&nbsp;<b>S</b><input name="second" type="text" size="2" maxlength="2" value="" />&nbsp;<b>MS</b><input name="millisecond" type="text" size="2" maxlength="3" value="" /></td>
                    </tr>';

    // Show the input for images if it is enabled
    if ($smfgSettings['enable_lap_images']) {
        echo '
                    <tr>
                        <td colspan="2"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_image_attachments'] . '
                        </span></h4></div></td>
                    </tr>
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_attach_image'] . '.<br />' . $txt['smfg_max_filesize'] . ': ' . $smfgSettings['max_image_kbytes'] . ' ' . $txt['smfg_kbytes'] . '<br />' . $txt['smfg_max_resolution'] . ': ' . $smfgSettings['max_image_resolution'] . 'x' . $smfgSettings['max_image_resolution'] . '</b></td>
                        <td><input type="hidden" name="MAX_FILE_SIZE" value="' . $context['max_image_bytes'] . '" /><input type="file" size="30" name="FILE_UPLOAD" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,.jpg,.jpeg,.png,.gif,.webp,.bmp"/></td>
                    </tr>';

        // Show the input for remote images if it is enabled
        if ($smfgSettings['enable_remote_images']) {
            echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_enter_remote_url'] . '</b></td>
                        <td><input name="url_image" type="text" size="40" maxlength="255" value="https://" /></td>
                    </tr>';
        }
        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="attach_desc" cols="60" rows="3"></textarea></td>
                    </tr>';
    }
    // Show the input for videos if it is enabled
    if ($smfgSettings['enable_laptime_video']) {
        echo '
                    <tr>
                        <td colspan="2"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_hosted_videos'] . '
                        </span></h4></div></td>
                    </tr>
                    
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_title'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="75" value="" name="video_title"/></td>
                    </tr>
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_url'] . '</b></td>
                        <td><input type="text"  size="40" maxlength="255" value="https://" name="video_url"/>&nbsp;<span class="smalltext"><a href="' . $scripturl . '?action=garage;sa=supported_video" rel="shadowbox;width=260;height=400" title="' . $txt['smfg_video_instructions'] . '">Supported Sites</a></span></td>
                    </tr>';

        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="video_desc" cols="60" rows="3"></textarea></td>
                    </tr>';

    }
    echo '
                    <tr>
                        <td align="center" height="28" colspan="2"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="add_laptime" type="submit" value="' . $txt['smfg_add_laptime'] . '" /></td>
                    </tr>
                </table>
            </form>
            <script type="text/javascript">
            var frmvalidator = new Validator("add_lap");
            var frm = document.forms["add_lap"];
            
            frmvalidator.addValidation("track_id","req","' . $txt['smfg_val_select_track'] . '");
            frmvalidator.addValidation("condition","req","' . $txt['smfg_val_select_track_condition'] . '");
            frmvalidator.addValidation("type","req","' . $txt['smfg_val_select_lap_type'] . '");
            
            frmvalidator.addValidation("minute","req","' . $txt['smfg_val_enter_minute'] . '");
            frmvalidator.addValidation("minute","num","' . $txt['smfg_val_time_restrictions'] . '");
            frmvalidator.addValidation("minute","minlen=1","' . $txt['smfg_val_minute_restriction1'] . '");
            frmvalidator.addValidation("minute","maxlen=2","' . $txt['smfg_val_minute_restriction2'] . '");
            
            frmvalidator.addValidation("second","req","' . $txt['smfg_val_enter_second'] . '");
            frmvalidator.addValidation("second","num","' . $txt['smfg_val_time_restrictions'] . '");
            frmvalidator.addValidation("second","maxlen=2","' . $txt['smfg_val_second_restriction'] . '");
            frmvalidator.addValidation("second","minlen=2","' . $txt['smfg_val_second_restriction'] . '");
            
            frmvalidator.addValidation("millisecond","req","' . $txt['smfg_val_enter_millisecond'] . '");
            frmvalidator.addValidation("millisecond","num","' . $txt['smfg_val_time_restrictions'] . '");
            frmvalidator.addValidation("millisecond","maxlen=2","' . $txt['smfg_val_millisecond_restriction'] . '");
            frmvalidator.addValidation("millisecond","minlen=2","' . $txt['smfg_val_millisecond_restriction'] . '");';
    if ($smfgSettings['enable_vehicle_images']) {
        echo '
                    frmvalidator.addValidation("attach_desc","maxlen=150","' . $txt['smfg_val_image_description_length'] . '");';
    }
    if ($smfgSettings['enable_vehicle_video']) {
        echo '
                    frmvalidator.addValidation("video_desc","maxlen=150","' . $txt['smfg_val_video_description_length'] . '");';
    }
    echo '
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_edit_laptime()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_edit_laptime'];

    echo '        
                        </h3>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>';

    echo '
    <table cellpadding="0" cellspacing="0" border="0" style="margin: 7px 0 5px 10px" id="tab_table">
        <tr id="tab_row">
            <td>               
            <ul class="dropmenu">
                <li id="button_g_laptime">
                    <a class="firstlevel" href="#laptime" onclick="change_tab(\'000\');" id="tab000">
                        <span class="firstlevel">' . $txt['smfg_laptime'] . '</span>
                    </a>
                </li>';

    if ($smfgSettings['enable_vehicle_images']) {

        echo '
                    <li id="button_g_images">
                        <a class="firstlevel" href="#images" onclick="change_tab(\'001\');" id="tab001">
                            <span class="firstlevel">' . $txt['smfg_images'] . '</span>
                        </a>
                    </li>';

    }

    if ($smfgSettings['enable_vehicle_video']) {

        echo '
                    <li id="button_g_videos">
                        <a class="firstlevel" href="#videos" onclick="change_tab(\'002\');" id="tab002">
                            <span class="firstlevel">' . $txt['smfg_videos'] . '</span>
                        </a>
                    </li>';

    }

    echo '
            </ul>
            </td>
        </tr>
    </table>';

    // Begin dynamic js divs
    echo '                     
        <div class="garage_panel" id="options000" style="display: none;">';

    echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $txt['smfg_laptime'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Submitted track?
    if ($_SESSION['added_track']) {
        echo '
                <tr>
                    <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_track_added'] . '</td>
                </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a dyno is added
        unset($_SESSION['added_track']);
    }

    echo '
            <tr>
                <td>
                <form action="' . $scripturl . '?action=garage;sa=update_laptime" enctype="multipart/form-data" method="post" name="edit_lap" id="edit_lap" style="padding:0; margin:0;">
                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . ';#laps" />
                <table width="100%" cellpadding="3" cellspacing="1" border="0">
                    <tr>
                        <td align="right" width="40%"><b>' . $txt['smfg_track'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td>
                        <select id="track_id" name="track_id" >
                        <option value="">' . $txt['smfg_select_track'] . '</option>
                        <option value="">------</option>';
    echo track_select($context['laps']['track_id']);
    echo '
                        </select>';
    if ($smfgSettings['enable_user_add_track']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_track" rel="shadowbox;width=620;height=200" title="Garage :: Submit Track">' . $txt['smfg_here'] . '</a>';
    }
    echo '
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="40%"><b>' . $txt['smfg_track'] . ' ' . $txt['smfg_condition'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td>
                        <select id="condition" name="condition" >
                        <option value="">' . $txt['smfg_select_condition'] . '</option>
                        <option value="">------</option>';
    echo track_condition_select($context['laps']['condition_id']);
    echo '
                        </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="40%"><b>' . $txt['smfg_lap_type'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td>
                        <select id="type" name="type" >
                        <option value="">' . $txt['smfg_select_type'] . '</option>
                        <option value="">------</option>';
    echo lap_type_select($context['laps']['type_id']);
    echo '
                        </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="40%"><b>' . $txt['smfg_laptime'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td><b>M</b><input name="minute" type="text" size="2" maxlength="2" value="' . $context['laps']['minute'] . '" />&nbsp;<b>S</b><input name="second" type="text" size="2" maxlength="2" value="' . $context['laps']['second'] . '" />&nbsp;<b>MS</b><input name="millisecond" type="text" size="2" maxlength="3" value="' . $context['laps']['millisecond'] . '" /></td>
                    </tr>
                    <tr>
                        <td align="center" height="28" colspan="2"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['LID'] . '" name="LID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" /><input name="add_laptime" type="submit" value="' . $txt['smfg_update_laptime'] . '" /></td>
                    </tr>
                </table>
            </form>
                <script type="text/javascript">
                var frmvalidator = new Validator("edit_lap");
                var frm = document.forms["edit_lap"];
                
                frmvalidator.addValidation("track_id","req","' . $txt['smfg_val_select_track'] . '");
                frmvalidator.addValidation("condition","req","' . $txt['smfg_val_select_track_condition'] . '");
                frmvalidator.addValidation("type","req","' . $txt['smfg_val_select_lap_type'] . '");
                
                frmvalidator.addValidation("minute","req","' . $txt['smfg_val_enter_minute'] . '");
                frmvalidator.addValidation("minute","num","' . $txt['smfg_val_time_restrictions'] . '");
                frmvalidator.addValidation("minute","minlen=1","' . $txt['smfg_val_minute_restriction1'] . '");
                frmvalidator.addValidation("minute","maxlen=2","' . $txt['smfg_val_minute_restriction2'] . '");
                
                frmvalidator.addValidation("second","req","' . $txt['smfg_val_enter_second'] . '");
                frmvalidator.addValidation("second","num","' . $txt['smfg_val_time_restrictions'] . '");
                frmvalidator.addValidation("second","maxlen=2","' . $txt['smfg_val_second_restriction'] . '");
                frmvalidator.addValidation("second","minlen=2","' . $txt['smfg_val_second_restriction'] . '");
                
                frmvalidator.addValidation("millisecond","req","' . $txt['smfg_val_enter_millisecond'] . '");
                frmvalidator.addValidation("millisecond","num","' . $txt['smfg_val_time_restrictions'] . '");
                frmvalidator.addValidation("millisecond","maxlen=2","' . $txt['smfg_val_millisecond_restriction'] . '");
                frmvalidator.addValidation("millisecond","minlen=2","' . $txt['smfg_val_millisecond_restriction'] . '");
                </script>';
    echo '
                </td>
            </tr>    
        </table>';

    echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

    echo '
        </div>';

    // Show the input for images if it is enabled
    if ($smfgSettings['enable_lap_images']) {
        echo '
        <div class="garage_panel" id="options001" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_images'];

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
                
                <form action="' . $scripturl . '?action=garage;sa=insert_laptime_images" id="update_images" enctype="multipart/form-data" method="post" name="update_images" style="padding:0; margin:0;">
                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_laptime;VID=' . $_GET['VID'] . ';LID=' . $_GET['LID'] . '#images" />
                <table border="0" cellpadding="3" cellspacing="1" width="100%">           
                    <tr>
                        <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_attach_image'] . '.<br />' . $txt['smfg_max_filesize'] . ': ' . $smfgSettings['max_image_kbytes'] . ' ' . $txt['smfg_kbytes'] . '<br />' . $txt['smfg_max_resolution'] . ': ' . $smfgSettings['max_image_resolution'] . 'x' . $smfgSettings['max_image_resolution'] . '</b></td>
                        <td><input type="hidden" name="MAX_FILE_SIZE" value="' . $context['max_image_bytes'] . '" /><input type="file" size="30" name="FILE_UPLOAD" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,.jpg,.jpeg,.png,.gif,.webp,.bmp"/></td>
                    </tr>';

        // Show the input for remote images if it is enabled
        if ($smfgSettings['enable_remote_images']) {
            echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_enter_remote_url'] . '</b></td>
                        <td><input name="url_image" type="text" size="40" maxlength="255" value="https://" /></td>
                    </tr>';
        }

        echo '    
                    <tr>
                        <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                        <td><textarea name="attach_desc" cols="60" rows="3"></textarea></td>
                    </tr>    
                    <tr>
                        <td colspan="2" align="center" height="28"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['LID'] . '" name="LID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="insert_laptime_images" type="submit" value="' . $txt['smfg_add_new_image'] . '" /></td>
                    </tr>
                </table>
                </form>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">
                    <tr>
                        <td colspan="2">
                        <table border="0" cellpadding="3" cellspacing="1" width="100%">
                            <tr>
                                <td width="100%" align="center" colspan="3">' . $txt['smfg_edit_in_place_instructions'] . '.<div id="updateStatus"></div></td>
                            </tr>
                            <tr>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_image'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_manage'] . '
                                </span></h4></div></td>
                                <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                ' . $txt['smfg_manage'] . '
                                </span></h4></div></td>
                            </tr>';
        $count = 0;
        // If there is an image, show em
        if (isset($context['laps'][$count]['image_id'])) {
            // and keep showing em
            while (isset($context['laps'][$count]['image_id'])) {
                echo '                            
                                    <tr class="tableRow">
                                        <td align="center" valign="middle">' . $context['laps'][$count]['image'] . '</td>
                                        <td align="center" valign="middle">
                                            <div id="image' . $context['laps'][$count]['image_id'] . '" class="editin">';
                // If there is no desc, let them add one
                if (!empty($context['laps'][$count]['attach_desc'])) {
                    echo $context['laps'][$count]['attach_desc'];
                }
                echo '</div></td>
                                        <td align="center" valign="middle">';
                if ($context['laps'][$count]['hilite'] != 1) {
                    echo '
                                            <form action="' . $scripturl . '?action=garage;sa=set_hilite_image_laptime;VID=' . $_GET['VID'] . ';LID=' . $_GET['LID'] . ';image_id=' . $context['laps'][$count]['image_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="set_laptime_hilite_' . $context['laps'][$count]['image_id'] . '" id="set_laptime_hilite_' . $context['laps'][$count]['image_id'] . '" style="display: inline;">
                                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_laptime;VID=' . $_GET['VID'] . ';LID=' . $_GET['LID'] . '#images" />
                                                <a href="#" onClick="document.set_laptime_hilite_' . $context['laps'][$count]['image_id'] . '.submit(); return false;">' . $txt['smfg_set_hilite_image'] . '</a>
                                            </form>
                                            <br /><br />';
                } else {
                    echo
                        $txt['smfg_hilite_image'] . '<br /><br />';
                }
                echo '
                                        <form action="' . $scripturl . '?action=garage;sa=remove_laptime_image;VID=' . $_GET['VID'] . ';LID=' . $_GET['LID'] . ';image_id=' . $context['laps'][$count]['image_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_laptime_image_' . $context['laps'][$count]['image_id'] . '" id="remove_laptime_image_' . $context['laps'][$count]['image_id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_laptime;VID=' . $_GET['VID'] . ';LID=' . $_GET['LID'] . '#images" />
                                        <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_image'] . '\')) { document.remove_laptime_image_' . $context['laps'][$count]['image_id'] . '.submit(); } else { return false; } return false;">' . $txt['smfg_remove_image'] . '</a>
                                        </form>
                                        </td>
                                    </tr>';
                $count++;
            }
        } else {
            echo '
                            <tr>
                                <td colspan="3" align="center" valign="middle">' . $txt['smfg_no_lap_images'] . '</td>
                            </tr>';
        }
        echo '
                            </table> 
                        </td>
                    </tr>
                </table>  
                <script type="text/javascript">
                 var frmvalidator = new Validator("update_images");
                
                    frmvalidator.addValidation("attach_desc","maxlen=150","' . $txt['smfg_val_image_description_length'] . '");
                 
                </script>           
                </td>
            </tr>
        </table>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '
        </div>';
    }
    // Show the input for videos if it is enabled
    if ($smfgSettings['enable_laptime_video']) {
        echo '
            <div class="garage_panel" id="options002" style="display: none;">';

        echo '
            <table class="table_list" cellspacing="0" cellpadding="0">
                <tbody class="header">
                    <tr>
                        <td>
                            <div class="cat_bar">
                                <h3 class="catbg">';

        echo $txt['smfg_video'];

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
                                        
                    <form action="' . $scripturl . '?action=garage;sa=insert_laptime_video" id="update_video" enctype="multipart/form-data" method="post" name="update_video" style="padding:0; margin:0;">         
                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_laptime;VID=' . $_GET['VID'] . ';LID=' . $_GET['LID'] . '#video" />
                    <table border="0" cellpadding="3" cellspacing="1" width="100%">  
                        <tr>
                            <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_title'] . '</b></td>
                            <td><input type="text"  size="40" maxlength="75" value="" name="video_title"/></td>
                        </tr>
                        <tr>
                            <td width="32%" align="right" nowrap="nowrap"><b>' . $txt['smfg_video_url'] . '</b></td>
                            <td><input type="text"  size="40" maxlength="255" value="https://" name="video_url"/>&nbsp;<span class="smalltext"><a href="' . $scripturl . '?action=garage;sa=supported_video" rel="shadowbox;width=260;height=400" title="' . $txt['smfg_video_instructions'] . '">Supported Sites</a></span></td>
                        </tr>';

        echo '    
                        <tr>
                            <td width="32%" align="right"><b>' . $txt['smfg_description'] . '</b></td>
                            <td><textarea name="video_desc" cols="60" rows="3"></textarea></td>
                        </tr>   
                        <tr>
                            <td colspan="2" align="center" height="28"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['LID'] . '" name="LID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="insert_laptime_video" type="submit" value="' . $txt['smfg_add_new_video'] . '" /></td>
                        </tr>
                    </table>
                    </form>
                    <table border="0" cellpadding="3" cellspacing="1" width="100%">  
                        <tr>
                            <td colspan="2">
                            <table border="0" cellpadding="3" cellspacing="1" width="100%">
                                <tr>
                                    <td width="100%" align="center" colspan="3">' . $txt['smfg_edit_in_place_instructions'] . '<div id="updateStatus2"></div></td>
                                </tr>
                                <tr>
                                    <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                    ' . $txt['smfg_video'] . '
                                    </span></h4></div></td>
                                    <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                    ' . $txt['smfg_title_description'] . '
                                    </span></h4></div></td>
                                    <td width="33%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                                    ' . $txt['smfg_manage'] . '
                                    </span></h4></div></td>
                                </tr>';

        $count = 0;
        // If there is an video, show em
        if (isset($context['laps'][$count]['video_id'])) {
            // and keep showing em
            while (isset($context['laps'][$count]['video_id'])) {
                echo '                            
                                        <tr class="tableRow">
                                            <td align="center" valign="middle"><a href="' . $scripturl . '?action=garage;sa=video;id=' . $context['laps'][$count]['video_id'] . '" rel="shadowbox;width=' . $context['laps'][$count]['video_width'] . ';height=' . $context['laps'][$count]['video_height'] . '" title="' . garage_title_clean('<b>' . $context['laps'][$count]['video_title'] . '</b> :: ' . $context['laps'][$count]['video_desc']) . '" class="smfg_videoTitle"><img src="' . $context['laps'][$count]['video_thumb'] . '" /></a></td>
                                            <td align="center" valign="middle">
                                            <div id="video_title' . $context['laps'][$count]['video_id'] . '" class="editin" style="font-weight: bold;">';
                // If there is no title, let them add one
                if (!empty($context['laps'][$count]['video_title'])) {
                    echo $context['laps'][$count]['video_title'];
                }
                echo '</div>
                                            <br />
                                            <div id="video' . $context['laps'][$count]['video_id'] . '" class="editin">';
                // If there is no desc, let them add one
                if (!empty($context['laps'][$count]['video_desc'])) {
                    echo $context['laps'][$count]['video_desc'];
                }
                echo '</div></td>
                                            <td align="center" valign="middle">';
                echo '
                                            <form action="' . $scripturl . '?action=garage;sa=remove_video;VID=' . $_GET['VID'] . ';video_id=' . $context['laps'][$count]['video_id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_laptime_video_' . $context['laps'][$count]['video_id'] . '" id="remove_laptime_video_' . $context['laps'][$count]['video_id'] . '" style="display: inline;">
                                            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=edit_laptime;VID=' . $_GET['VID'] . ';LID=' . $_GET['LID'] . '#video" />
                                            <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_video'] . '\')) { document.remove_laptime_video_' . $context['laps'][$count]['video_id'] . '.submit(); } else { return false; } return false;">' . $txt['smfg_remove_video'] . '</a>
                                            </form>
                                            </td>
                                        </tr>';
                $count++;
            }
        } else {
            echo '
                                    <tr>
                                        <td colspan="3" align="center" valign="middle">' . $txt['smfg_no_lap_videos'] . '</td>
                                    </tr>';
        }
        echo '
                                </table> 
                            </td>
                        </tr>
                    </table>  
                    </td>
                </tr>
            </table>
            <script type="text/javascript">
             var frmvalidator = new Validator("update_video");
              
             frmvalidator.addValidation("video_desc","maxlen=150","' . $txt['smfg_val_video_description_length'] . '");
             frmvalidator.addValidation("video_title","req","' . $txt['smfg_val_enter_title'] . '");
             
            </script>';

        echo '  
            </div>
            </div>
            <span class="lowerframe"><span></span></span>';

        echo '
            </div>';
    }

    echo '
        <script type="text/javascript">
        <!--
            var lowest_tab = \'000\';
            var active_id = \'000\';
            if (document.location.hash == "")
            {
                change_tab(lowest_tab);
            }
            else if (document.location.hash == "#laptime")
            {
                change_tab(\'000\');
            }
            else if (document.location.hash == "#images")
            {
                change_tab(\'001\');
            }
            else if (document.location.hash == "#videos")
            {
                change_tab(\'002\');
            }

        //-->

        </script>';

    echo smfg_footer();

}

function template_view_laptime()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings, $boardurl;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_view_laptime'];

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
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
    <tr>
        <td align="center" valign="top">';

    // Pending?
    if ($context['laps']['pending'] == '1') {
        echo '
            <table class="tborder" width="90%">
                <tr>
                    <td>
                    <table border="0">
                        <tr>
                            <td align="center" valign="middle" width="40"><img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="" title="" /></td>
                            <td align="center" valign="middle">' . $txt['smfg_pending_item'] . '</td>
                        </tr>
                    </table>
                    </td>
                </tr>
            </table><br />';
    }

    echo '
            
            <table border="0" width="70%">
                <tr>
                    <td align="left"><b>' . $txt['smfg_owner'] . '</b></td>
                </tr>
                <tr>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['user_vehicles']['user_id'] . '"><b>' . $context['laps']['owner'] . '</b></a></td>
                </tr>
                <tr>
                    <td align="left"><b>' . $txt['smfg_hilite_image'] . '</b></td>
                </tr>
                <tr>
                    <td align="center">', (!empty($context['hilite_image_location'])) ? '<a href="' . $context['hilite_image_location'] . '" rel="shadowbox" title="' . $context['laps']['time'] . ' @ ' . $context['laps']['track'] . ' :: ' . garage_title_clean($context['hilite_desc']) . '" class="smfg_imageTitle"><img src="' . $context['hilite_thumb_location'] . '" width="' . $context['hilite_thumb_width'] . '" height="' . $context['hilite_thumb_height'] . '" /></a>' : '', '</td>
                </tr>
            </table>
        </td>
        <td width="30%" valign="middle" align="center">
            <table border="0" cellspacing="1" cellpadding="3">
            <tr>
                <td align="left"><b>' . $txt['smfg_vehicle'] . '</b></td>
                <td align="left"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '">' . garage_title_clean($context['user_vehicles']['title']) . '</a><td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_track'] . '</b></td>
                <td align="left"><a href="' . $scripturl . '?action=garage;sa=view_track;TID=' . $context['laps']['track_id'] . '">' . garage_title_clean($context['laps']['track']) . '</a><td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_track'] . ' ' . $txt['smfg_condition'] . '</b></td>
                <td align="left">' . $context['laps']['condition'] . '<td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_lap_type'] . '</b></td>
                <td align="left">' . $context['laps']['type'] . '</td>
            </tr>
            <tr>
                <td align="left"><b>' . $txt['smfg_laptime_specs'] . '</b></td>
                <td align="left">' . $context['laps']['time'] . '</td>
            </tr>
            </table>
        </td>
    </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    if ($smfgSettings['enable_lap_images'] || $smfgSettings['enable_laptime_video']) {

        echo '
        <table cellpadding="0" cellspacing="0" border="0" style="margin: 7px 0 5px 10px" id="tab_table">
            <tr id="tab_row">
                <td>               
                <ul class="dropmenu">
                    <li id="button_g_images">
                        <a class="firstlevel" href="#images "onclick="change_tab(\'000\');" id="tab000">
                            <span class="firstlevel">' . $txt['smfg_images'] . '</span>
                        </a>
                    </li>';

        if (isset($context['laps'][$count]['video_id']) && $smfgSettings['enable_modification_video']) {

            echo '
                        <li id="button_g_videos">
                            <a class="firstlevel" href="#videos "onclick="change_tab(\'001\');" id="tab001">
                                <span class="firstlevel">' . $txt['smfg_videos'] . '</span>
                            </a>
                        </li>';

        }

        echo '
                </ul>
                </td>
            </tr>
        </table>';

        // Begin dynamic js divs
        echo '        
        <div class="garage_panel" id="options000" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_images'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

        $count = 0;

        if (isset($context['laps'][$count]['image_id'])) {
            echo '
            <tr>
                <td valign="middle">';
            while (isset($context['laps'][$count]['image_id'])) {
                echo '
                    <a href="' . $context['laps'][$count]['attach_location'] . '" rel="shadowbox[laps]" title="' . garage_title_clean($context['laps']['time'] . ' @ ' . $context['laps']['track'] . ' :: ' . $context['laps'][$count]['attach_desc']) . '" class="smfg_imageTitle"><img src="' . $context['laps'][$count]['attach_thumb_location'] . '" width="' . $context['laps'][$count]['attach_thumb_width'] . '" height="' . $context['laps'][$count]['attach_thumb_height'] . '" /></a>';
                $count++;
            }
            echo '
                </td>
            </tr>';
        } else {
            echo '
            <tr>
                <td align="center">' . $txt['smfg_no_lap_images'] . '</td>
            </tr>';
        }
        echo '
        </table> ';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '    
        </div>
        <div class="garage_panel" id="options001" style="display: none;">';

        echo '
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

        echo $txt['smfg_videos'];

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
        <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

        $count = 0;

        if (isset($context['laps'][$count]['video_id'])) {
            echo '
            <tr>
                <td valign="middle">';
            while (isset($context['laps'][$count]['video_id'])) {
                echo '
                    <a href="' . $scripturl . '?action=garage;sa=video;id=' . $context['laps'][$count]['video_id'] . '" rel="shadowbox[video];width=' . $context['laps'][$count]['video_width'] . ';height=' . $context['laps'][$count]['video_height'] . ';" title="' . garage_title_clean('<b>' . $context['laps'][$count]['video_title'] . '</b> :: ' . $context['laps'][$count]['video_desc']) . '" class="smfg_videoTitle"><img src="' . $context['laps'][$count]['video_thumb'] . '" /></a>';
                $count++;
            }
            echo '
                </td>
            </tr>';
        } else {
            echo '
            <tr>
                <td align="center">' . $txt['smfg_no_lap_videos'] . '</td>
            </tr>';
        }
        echo '  
        </table>';

        echo '  
        </div>
        </div>
        <span class="lowerframe"><span></span></span>';

        echo '    
        </div>

        <script type="text/javascript">
        <!--
            var lowest_tab = \'000\';
            var active_id = \'000\';
            if (document.location.hash == "")
            {
                change_tab(lowest_tab);
            }
            else if (document.location.hash == "#images")
            {
                change_tab(\'000\');
            }
            else if (document.location.hash == "#videos")
            {
                change_tab(\'001\');
            }

        //-->

        </script>';

    }

    echo smfg_footer();

}

function template_view_track()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_view_track'];

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
    <table width="100%" border="0" cellspacing="1" cellpadding="4"> 
        <tr class="tableRow">
            <td align="left" width="20%"><b>' . $txt['smfg_track'] . '</b></td>
            <td align="left" width="80%">' . $context['track']['title'] . '</td>
        </tr>
        <tr class="tableRow">
            <td align="left" width="20%"><b>' . $txt['smfg_length'] . '</b></td>
            <td align="left" width="80%"">' . $context['track']['length'] . ' ' . $context['track']['mileage_unit'] . '</td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo '
    <br />';

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_posted_laptimes'];

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
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    $count = 0;

    if (isset($context['laps'][$count]['id'])) {
        echo '
        <tr>
            <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            &nbsp;
            </span></h4></div></td>
            <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_owner'] . '
            </span></h4></div></td>
            <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_vehicle'] . '
            </span></h4></div></td>
            <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_condition'] . '
            </span></h4></div></td>
            <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_type'] . '
            </span></h4></div></td>
            <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_laptime_specs'] . '
            </span></h4></div></td>
        </tr>';
        while (isset($context['laps'][$count]['id'])) {
            echo '
            <tr class="tableRow">         
                <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['laps'][$count]['image'] . $context['laps'][$count]['spacer'] . $context['laps'][$count]['video'] . '</td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['laps'][$count]['user_id'] . '">' . $context['laps'][$count]['memberName'] . '</a></td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['laps'][$count]['vehicle_id'] . '">' . garage_title_clean($context['laps'][$count]['vehicle']) . '</a></td>
                <td align="center">' . $context['laps'][$count]['condition'] . '</td>
                <td align="center">' . $context['laps'][$count]['type'] . '</td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_laptime;VID=' . $context['laps'][$count]['vehicle_id'] . ';LID=' . $context['laps'][$count]['id'] . '">' . garage_title_clean($context['laps'][$count]['time']) . '</a></td>
            </tr>';
            $count++;
        }
    } else {
        echo '
        <tr>         
            <td align="center">' . $txt['smfg_no_laps_on_track'] . '</td>
        </tr>';
    }
    echo '
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_add_service()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_add_service'];

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
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Garage?
    if ($_SESSION['added_garage']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_garage_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_garage']);
    }

    echo '
        <tr>
            <td>
             <form action="' . $scripturl . '?action=garage;sa=insert_service;VID=' . $_GET['VID'] . '" enctype="multipart/form-data" method="post" name="add_service" id="add_service" style="padding:0; margin:0;">
                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . '#services" />
                <table width="100%" cellpadding="3" cellspacing="1" border="0">
                    <tr>
                        <td align="right" width="30%"><b>' . $txt['smfg_serviced_by'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                          <td colspan="2">
                          <select id="garage_id" name="garage_id">
                          <option value="">' . $txt['smfg_select_garage'] . '</option>
                          <option value="">------</option>';
    echo install_select();
    echo '
                          </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=garage" rel="shadowbox;width=620;height=560" title="Garage :: Submit Garage">' . $txt['smfg_here'] . '</a>';
    }
    echo '
                        </td>

                    </tr>
                    <tr>
                        <td align="right" width="30%"><b>' . $txt['smfg_service_type'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                          <td colspan="2">
                          <select id="type_id" name="type_id">
                          <option value="">' . $txt['smfg_select_service_type'] . '</option>
                          <option value="">------</option>';
    echo service_type_select();
    echo '
                          </select>
                          </td>
                    </tr>
                    <tr>

                        <td align="right" width="30%"><b>' . $txt['smfg_service_price'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td colspan="2"><input name="price" type="text" size="10" value="" /></td>
                    </tr>
                    <tr>
                        <td align="right" width="30%"><b>' . $txt['smfg_service_rating'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td colspan="2">
                        <select id="rating" name="rating">
                        <option value="">' . $txt['smfg_select_rating'] . '</option>
                        <option value="">------</option>
                        <option value="10" >10 ' . $txt['smfg_best'] . '</option>
                        <option value="9" >9</option>
                        <option value="8" >8</option>
                        <option value="7" >7</option>
                        <option value="6" >6</option>
                        <option value="5" >5</option>
                        <option value="4" >4</option>
                        <option value="3" >3</option>
                        <option value="2" >2</option>
                        <option value="1" >1 ' . $txt['smfg_worst'] . '</option>
                        </select>
                        </td>

                    </tr>
                    <tr>
                        <td align="right" width="30%"><b>' . $txt['smfg_vehicle_mileage'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td colspan="2"><input name="mileage" type="text" size="10" value="" /></td>
                    </tr>
                    <tr>
                        <td align="center" height="28" colspan="3"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="modification" type="submit" value="' . $txt['smfg_add_service'] . '" /></td>
                    </tr>
                </table>
                </form>
                <script type="text/javascript">
                var frmvalidator = new Validator("add_service");
                var frm = document.forms["add_service"];
                
                frmvalidator.addValidation("garage_id","req","' . $txt['smfg_val_select_garage'] . '");
                frmvalidator.addValidation("garage_id","dontselect=0","' . $txt['smfg_val_select_garage'] . '");
                frmvalidator.addValidation("garage_id","dontselect=1","' . $txt['smfg_val_select_garage'] . '");
                
                frmvalidator.addValidation("type_id","req","' . $txt['smfg_val_select_service_type'] . '");
                frmvalidator.addValidation("type_id","dontselect=0","' . $txt['smfg_val_select_service_type'] . '");
                frmvalidator.addValidation("type_id","dontselect=1","' . $txt['smfg_val_select_service_type'] . '");
                
                frmvalidator.addValidation("price","req","' . $txt['smfg_val_enter_service_price'] . '");
                frmvalidator.addValidation("price","regexp=^[.0-9]{1,10}$","' . $txt['smfg_val_service_price_restriction'] . '");
                
                frmvalidator.addValidation("rating","req","' . $txt['smfg_val_select_service_rating'] . '");
                frmvalidator.addValidation("rating","dontselect=0","' . $txt['smfg_val_select_service_rating'] . '");
                frmvalidator.addValidation("rating","dontselect=1","' . $txt['smfg_val_select_service_rating'] . '");
                
                frmvalidator.addValidation("mileage","req","' . $txt['smfg_val_enter_mileage'] . '");
                frmvalidator.addValidation("mileage","num","' . $txt['smfg_val_mileage_restriction'] . '");
                </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_edit_service()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_edit_service'];

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
    <table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">';

    // Garage?
    if ($_SESSION['added_garage']) {
        echo '
            <tr>
                <td class="windowbg_pending" align="center" valign="middle">' . $txt['smfg_garage_added'] . '</td>
            </tr>';
        // Have to unset the session after we check for it so it doesn't show 'Successful' everytime a mod is added
        unset($_SESSION['added_garage']);
    }

    echo '
        <tr>
            <td>
            <form action="' . $scripturl . '?action=garage;sa=update_service" enctype="multipart/form-data" method="post" name="edit_service" id="edit_service" style="padding:0; margin:0;">
            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . ';#services" />
                <table width="100%" cellpadding="3" cellspacing="1" border="0">
                    <tr>
                        <td align="right" width="40%"><b>' . $txt['smfg_serviced_by'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                          <td colspan="2">
                          <select id="garage_id" name="garage_id">
                          <option value="">' . $txt['smfg_select_garage'] . '</option>
                          <option value="">------</option>';
    echo install_select($context['services']['garage_id']);
    echo '
                          </select>';
    if ($smfgSettings['enable_user_submit_business']) {
        echo '&nbsp;' . $txt['smfg_not_listed'] . ' <a href="' . $scripturl . '?action=garage;sa=submit_business;bustype=garage" rel="shadowbox;width=620;height=560" title="Garage :: Submit Garage">' . $txt['smfg_here'] . '</a>';
    }
    echo '
                          </td>

                    </tr>
                    <tr>
                        <td align="right" width="40%"><b>' . $txt['smfg_service_type'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                          <td colspan="2">
                          <select id="type_id" name="type_id">
                          <option value="">' . $txt['smfg_select_service_type'] . '</option>
                          <option value="">------</option>';
    echo service_type_select($context['services']['type_id']);
    echo '
                          </select>
                          </td>
                    </tr>
                    <tr>

                        <td align="right" width="40%"><b>' . $txt['smfg_service_price'] . '</b></td>
                        <td colspan="2"><input name="price" type="text" size="10" value="' . $context['services']['price'] . '" /></td>
                    </tr>
                    <tr>
                        <td align="right" width="40%"><b>' . $txt['smfg_service_rating'] . '</b></td>
                        <td colspan="2">';
    echo '
                        <select id="rating" name="rating">   
                        <option value="">' . $txt['smfg_select_rating'] . '</option>
                        <option value="">------</option>
                        <option value="10" ' . $context['rat_10'] . '>10 ' . $txt['smfg_best'] . '</option>
                        <option value="9" ' . $context['rat_9'] . '>9</option>
                        <option value="8" ' . $context['rat_8'] . '>8</option>
                        <option value="7" ' . $context['rat_7'] . '>7</option>
                        <option value="6" ' . $context['rat_6'] . '>6</option>
                        <option value="5" ' . $context['rat_5'] . '>5</option>
                        <option value="4" ' . $context['rat_4'] . '>4</option>
                        <option value="3" ' . $context['rat_3'] . '>3</option>
                        <option value="2" ' . $context['rat_2'] . '>2</option>
                        <option value="1" ' . $context['rat_1'] . '>1 ' . $txt['smfg_worst'] . '</option>
                        </select>
                        </td>

                    </tr>
                    <tr>
                        <td align="right" width="40%"><b>' . $txt['smfg_vehicle_mileage'] . '</b>&nbsp;' . $txt['smfg_required'] . '</td>
                        <td colspan="2"><input name="mileage" type="text" size="10" value="' . $context['services']['mileage'] . '" /></td>
                    </tr>
                    <tr>
                        <td align="center" height="28" colspan="3"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['SID'] . '" name="SID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" /><input name="service_update" type="submit" value="' . $txt['smfg_update_service'] . '" /></td>
                    </tr>
                </table>
                </form>
                <script type="text/javascript">
                var frmvalidator = new Validator("edit_service");
                var frm = document.forms["edit_service"];
                
                frmvalidator.addValidation("garage_id","req","' . $txt['smfg_val_select_garage'] . '");
                frmvalidator.addValidation("garage_id","dontselect=0","' . $txt['smfg_val_select_garage'] . '");
                frmvalidator.addValidation("garage_id","dontselect=1","' . $txt['smfg_val_select_garage'] . '");
                
                frmvalidator.addValidation("type_id","req","' . $txt['smfg_val_select_service_type'] . '");
                frmvalidator.addValidation("type_id","dontselect=0","' . $txt['smfg_val_select_service_type'] . '");
                frmvalidator.addValidation("type_id","dontselect=1","' . $txt['smfg_val_select_service_type'] . '");
                
                frmvalidator.addValidation("price","req","' . $txt['smfg_val_enter_service_price'] . '");
                frmvalidator.addValidation("price","regexp=^[.0-9]{1,10}$","' . $txt['smfg_val_service_price_restriction'] . '");
                
                frmvalidator.addValidation("rating","req","' . $txt['smfg_val_select_service_rating'] . '");
                frmvalidator.addValidation("rating","dontselect=0","' . $txt['smfg_val_select_service_rating'] . '");
                frmvalidator.addValidation("rating","dontselect=1","' . $txt['smfg_val_select_service_rating'] . '");
                
                frmvalidator.addValidation("mileage","req","' . $txt['smfg_val_enter_mileage'] . '");
                frmvalidator.addValidation("mileage","num","' . $txt['smfg_val_mileage_restriction'] . '");
                </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_edit_blog()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_edit_blog'];

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
            <form action="' . $scripturl . '?action=garage;sa=update_blog" method="post" name="edit_blog" id="edit_blog" style="padding:0; margin:0;">
            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $_GET['VID'] . ';#blog" />
            <table width="100%" cellspacing="1" cellpadding="3" border="0">
                <tr>
                    <td align="right" width="20%">' . $txt['smfg_blog_title'] . '</td>
                    <td colspan="1"><input name="blog_title" type="text" size="60" value="' . $context['blog']['title'] . '" /></td>
                </tr>
                <tr>
                    <td align="right" width="20%">' . $txt['smfg_blog_entry'] . '<br /><br />', $smfgSettings['enable_blogs_bbcode'] ? $txt['smfg_bbc_supported'] : $txt['smfg_bbc_disabled'], '<br />' . $txt['smfg_html_supported'] . '</td>
                    <td colspan="1"><textarea name="blog_text" cols="70" rows="6" style="width: 98%">' . $context['blog']['text'] . '</textarea></td>
                </tr>
                <tr>
                    <td align="center" height="28" colspan="5"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['BID'] . '" name="BID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input name="submit" type="submit" value="' . $txt['smfg_update_blog_entry'] . '"/></td>
                </tr>
            </table>
            </form>
            <script type="text/javascript">
            var frmvalidator = new Validator("edit_blog");
                
            frmvalidator.addValidation("blog_title","req","' . $txt['smfg_val_enter_blog_title'] . '");
            frmvalidator.addValidation("blog_text","req","' . $txt['smfg_val_enter_blog_text'] . '");
            frmvalidator.addValidation("blog_text","maxlen=5000","' . $txt['smfg_val_blog_restrictions'] . '");
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_edit_garage_comment()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_edit_comment'];

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
            <form action="' . $scripturl . '?action=garage;sa=update_garage_comment" method="post" name="edit_comment" id="edit_comment" style="padding:0; margin:0;">
            <table width="100%" cellspacing="1" cellpadding="3" border="0">
                <tr>
                    <td align="right" width="20%">' . $txt['smfg_comment'] . '<br /><br />', $smfgSettings['enable_guestbooks_bbcode'] ? $txt['smfg_bbc_supported'] : $txt['smfg_bbc_disabled'], '<br />' . $txt['smfg_html_supported'] . '</td>
                    <td colspan="4"><textarea name="post" cols="70" rows="7" style="width: 98%">' . $context['comments']['post'] . '</textarea></td>
                </tr>
                <tr>
                    <td align="center" height="28" colspan="5"><input type="hidden" value="' . $_GET['UID'] . '" name="UID" /><input type="hidden" value="' . $_GET['CID'] . '" name="CID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" /><input name="submit" type="submit" value="' . $txt['smfg_update_comment'] . '" /></td>
                </tr>
            </table>
            </form>
            <script type="text/javascript">
            var frmvalidator = new Validator("edit_comment");
                
            frmvalidator.addValidation("post","req","' . $txt['smfg_val_enter_comment'] . '");
            frmvalidator.addValidation("post","maxlen=2500","' . $txt['smfg_val_comment_restriction'] . '");
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_edit_comment()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <table class="table_list" cellspacing="0" cellpadding="0">
        <tbody class="header">
            <tr>
                <td>
                    <div class="cat_bar">
                        <h3 class="catbg">';

    echo $txt['smfg_edit_comment'];

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
            <form action="' . $scripturl . '?action=garage;sa=update_comment" method="post" name="edit_comment" id="edit_comment" style="padding:0; margin:0;">
            <table width="100%" cellspacing="1" cellpadding="3" border="0">
                <tr>
                    <td align="right" width="20%">' . $txt['smfg_comment'] . '<br /><br />', $smfgSettings['enable_guestbooks_bbcode'] ? $txt['smfg_bbc_supported'] : $txt['smfg_bbc_disabled'], '<br />' . $txt['smfg_html_supported'] . '</td>
                    <td colspan="4"><textarea name="post" cols="70" rows="7" style="width: 98%">' . $context['gb']['comment'] . '</textarea></td>
                </tr>
                <tr>
                    <td align="center" height="28" colspan="5"><input type="hidden" value="' . $_GET['VID'] . '" name="VID" /><input type="hidden" value="' . $_GET['CID'] . '" name="CID" /><input type="hidden" name="sc" value="', $context['session_id'], '" /><input type="hidden" name="redirecturl" value="' . $_SESSION['old_url'] . '" /><input name="submit" type="submit" value="' . $txt['smfg_update_comment'] . '" /></td>
                </tr>
            </table>
            </form>
            <script type="text/javascript">
            var frmvalidator = new Validator("edit_comment");
                
            frmvalidator.addValidation("post","req","' . $txt['smfg_val_enter_comment'] . '");
            frmvalidator.addValidation("post","maxlen=2500","' . $txt['smfg_val_comment_restriction'] . '");
            </script>
            </td>
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_search()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings, $boardurl;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    // List Model Options
    echo model_options('search_garage') . "\n\n";

    // List the product options
    echo product_options('search_garage');

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $txt['smfg_search'] . ' ' . $txt['smfg_garage'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
<form action="' . $scripturl . '?action=garage;sa=search_results" id="search_garage" enctype="multipart/form-data"  name="search_garage" method="post" style="padding:0; margin:0;">
<table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
    <tr>
        <td>
        <table border="0" cellpadding="3" cellspacing="1" width="100%">
            <tr class="tableRow">
                <td align="center"><input type="checkbox" name="search_year" value="1" /></td>
                <td width="25%"><b>' . $txt['smfg_vehicle'] . '&nbsp;' . $txt['smfg_year'] . '</b></td>
                <td>
                <select id="made_year" name="made_year">';
    echo year_options($smfgSettings['year_start'], $smfgSettings['year_end']);
    echo '
                </select></td>
            </tr>
            <tr class="tableRow">
                <td align="center"><input type="checkbox" name="search_make" value="1" /></td>
                <td width="25%"><b>' . $txt['smfg_vehicle'] . '&nbsp;' . $txt['smfg_make'] . '</b></td>
                <td>
                <select id="make_id" name="make_id">
                <option value="">' . $txt['smfg_select_make1'] . '</option>
                <option value="">------</option>';
    // List Make Selections
    echo make_select();
    echo '
                </select></td>
            </tr>
            <tr class="tableRow">
                <td align="center"><input type="checkbox" name="search_model" value="1" /></td>
                <td width="25%"><b>' . $txt['smfg_vehicle'] . '&nbsp;' . $txt['smfg_model'] . '</b></td>
                <td>
                <select id="model_id" name="model_id">
                <script type="text/javascript">dol.printOptions("model_id")</script>
                </select></td>
            </tr>
            <tr class="tableRow">
                <td align="center"><input type="checkbox" name="search_category" value="1" /></td>
                <td width="25%"><b>' . $txt['smfg_modification'] . '&nbsp;' . $txt['smfg_category'] . '</b></td>
                <td>
                <select id="category_id" name="category_id">
                <option value="">' . $txt['smfg_select_category'] . '</option>
                <option value="">------</option>';
    // List Mod Category Selections
    echo cat_select();
    echo '</select></td>
            </tr>
            <tr class="tableRow">
                <td align="center"><input type="checkbox" name="search_manufacturer" value="1" /></td>
                <td width="25%"><b>' . $txt['smfg_modification'] . '&nbsp;' . $txt['smfg_manufacturer'] . '</b></td>
                <td>
                <select id="manufacturer_id" name="manufacturer_id">
                <script type="text/javascript">dol.printOptions("manufacturer_id")</script>
                </select></td>
            </tr>
            <tr class="tableRow">
                <td align="center"><input type="checkbox" name="search_product" value="1" /></td>
                <td width="25%"><b>' . $txt['smfg_modification'] . '&nbsp;' . $txt['smfg_product'] . '</b></td>
                <td>
                <select id="product_id" name="product_id">
                <script type="text/javascript">dol.printOptions("product_id")</script>
                </select></td>
            </tr>
            <tr class="tableRow">
                <td align="center"><input type="checkbox" name="search_username" value="1" /></td>
                <td width="25%"><b>' . $txt['smfg_member_name'] . '</b></td>
                <td><input name="username" id="username" type="text" size="35" value="" tabindex="', $context['tabindex']++, '" />&nbsp;<a href="', $scripturl, '?action=findmember;input=username;quote=0;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a> <a href="', $scripturl, '?action=findmember;input=username;quote=0;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><span class="smalltext">', $txt['find_members'], '</span></a>
            </tr>
            <tr class="tableRow">
                <td>&nbsp;</td>
                <td width="25%"><b>' . $txt['smfg_search_logic'] . '</b></td>
                <td><label for="search_any">' . $txt['smfg_match_any'] . '<input type="radio" name="search_logic" id="search_any" value="OR" checked="checked" /></label>&nbsp;&nbsp;<label for="search_all">' . $txt['smfg_match_all'] . '<input type="radio" name="search_logic" id="search_all" value="AND" /></label></td>
            </tr>
            <tr class="tableRow">
                <td>&nbsp;</td>
                <td width="25%"><b>' . $txt['smfg_display_results_as'] . '</b></td>
                <td>
                <label for="vehicles">' . $txt['smfg_vehicles_caps'] . '<input type="radio" class="radio" name="display_as" id="vehicles" value="vehicles" checked /></label>&nbsp;&nbsp;';
    if ($smfgSettings['enable_modification']) {
        echo '
                    <label for="modifications">' . $txt['smfg_modifications'] . '<input name="display_as" type="radio" class="radio" id="modifications" value="modifications" /></label>&nbsp;&nbsp;';
    }
    if ($smfgSettings['enable_insurance']) {
        echo '
                    <label for="premiums">' . $txt['smfg_premiums'] . '<input name="display_as" type="radio" class="radio" id="premiums" value="premiums" /></label>&nbsp;&nbsp;';
    }
    if ($smfgSettings['enable_quartermile']) {
        echo '
                    <label for="quartermiles">&frac14; miles<input type="radio" class="radio" name="display_as" id="quartermiles" value="quartermiles" /></label>&nbsp;&nbsp;';
    }
    if ($smfgSettings['enable_dynorun']) {
        echo '
                    <label for="dynoruns">' . $txt['smfg_dynoruns'] . '<input type="radio" class="radio" name="display_as" id="dynoruns" value="dynoruns" /></label>&nbsp;&nbsp;';
    }
    if ($smfgSettings['enable_laptimes']) {
        echo '
                    <label for="laps">' . $txt['smfg_laps'] . '<input type="radio" class="radio" name="display_as" id="laps" value="laps" /></label>';
    }
    echo '</td>
            </tr>
        </table>';

    echo '</td>
</tr>
<tr>
    <td align="center" height="28"><input name="submit" type="submit" value="' . $txt['smfg_search'] . '" /></td>
</tr>
</table>
</form>';

    echo '  
</div>
</div>
<span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_search_results()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo '<span style="float: right;"><b>[&nbsp;' . $context['total'] . '&nbsp;results&nbsp;]&nbsp;</b></span>';

    echo (defined('SMF_VERSION') && version_compare(SMF_VERSION, '2.1', '>=') ? '' : $txt['pages'] . ': ') . $context['page_index'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
<table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
    <tr>
        <td>';

    // Display as - Vehicles
    if ($_SESSION['smfg']['display_as'] == "vehicles") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['search_results'][$count]['vid'])) {
            echo '
                <tr>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    &nbsp;
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byYear'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byMake'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byModel'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byColor'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byViews'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byMods'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byUpdated'] . '
                    </span></h4></div></td>
                </tr>';
            while (isset($context['search_results'][$count]['vid'])) {

                echo '
                    <tr class="tableRow">
                        <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['search_results'][$count]['image'] . $context['search_results'][$count]['spacer'] . $context['search_results'][$count]['video'] . '</td>
                        <td align="center">' . $context['search_results'][$count]['made_year'] . '</td>
                        <td align="center">' . $context['search_results'][$count]['make'] . '</td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['search_results'][$count]['vid'] . '">' . garage_title_clean($context['search_results'][$count]['model']) . '</a></td>
                        <td align="center">' . $context['search_results'][$count]['color'] . '</td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['search_results'][$count]['user_id'] . '">' . $context['search_results'][$count]['memberName'] . '</a></td>
                        <td align="center">' . $context['search_results'][$count]['views'] . '</td>
                        <td align="center">' . $context['search_results'][$count]['total_mods'] . '</td>
                        <td align="center">' . date($context['date_format'],
                        $context['search_results'][$count]['date_updated']) . '</td>
                    </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                    <td colspan="9" align="center">' . $txt['smfg_no_vehicle_results'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }

    // Display as - Modifications
    if ($_SESSION['smfg']['display_as'] == "modifications") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['search_results'][$count]['vid'])) {
            echo '
                <tr>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    &nbsp;
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byYear'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byMake'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byModel'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byMod'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byUpdated'] . '
                    </span></h4></div></td>
                </tr>';
            while (isset($context['search_results'][$count]['vid'])) {

                echo '
                    <tr class="tableRow">
                        <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['search_results'][$count]['image'] . $context['search_results'][$count]['spacer'] . $context['search_results'][$count]['video'] . '</td>
                        <td align="center">' . $context['search_results'][$count]['made_year'] . '</td>
                        <td align="center">' . $context['search_results'][$count]['make'] . '</td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['search_results'][$count]['vid'] . '">' . garage_title_clean($context['search_results'][$count]['model']) . '</a></td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['search_results'][$count]['user_id'] . '">' . $context['search_results'][$count]['memberName'] . '</a></td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['search_results'][$count]['vid'] . ';MID=' . $context['search_results'][$count]['mid'] . '">' . garage_title_clean($context['search_results'][$count]['modification']) . '</td>
                        <td align="center">' . date($context['date_format'],
                        $context['search_results'][$count]['date_updated']) . '</td>
                    </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                    <td colspan="9" align="center">' . $txt['smfg_no_modification_results'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }


    // Display as - Premiums
    if ($_SESSION['smfg']['display_as'] == "premiums") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['search_results'][$count]['vid'])) {
            echo '
                <tr>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byVehicle'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byPremium'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byCoverType'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byInsurer'] . '
                    </span></h4></div></td>
                </tr>';
            while (isset($context['search_results'][$count]['vid'])) {

                echo '
                    <tr class="tableRow">
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['search_results'][$count]['vid'] . '">' . garage_title_clean($context['search_results'][$count]['vehicle']) . '</a></td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['search_results'][$count]['user_id'] . '">' . $context['search_results'][$count]['memberName'] . '</a></td>
                        <td align="center">' . $context['search_results'][$count]['price'] . '</td>
                        <td align="center">' . $context['search_results'][$count]['cover_type'] . '</td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=insurance_review;BID=' . $context['search_results'][$count]['bid'] . '">' . garage_title_clean($context['search_results'][$count]['insurer']) . '</a></td>
                    </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                    <td colspan="9" align="center">' . $txt['smfg_no_premium_results'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }


    // Display as - Quartermiles
    if ($_SESSION['smfg']['display_as'] == "quartermiles") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['search_results'][$count]['qmid'])) {
            echo '
                    <tr>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        &nbsp;
                        </span></h4></div></td>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $context['sort']['byUsername'] . '
                        </span></h4></div></td>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $context['sort']['byVehicle'] . '
                        </span></h4></div></td>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $context['sort']['byRt'] . '
                        </span></h4></div></td>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $context['sort']['bySixty'] . '
                        </span></h4></div></td>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $context['sort']['byThree'] . '
                        </span></h4></div></td>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $context['sort']['byEighth'] . '
                        </span></h4></div></td>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $context['sort']['byThou'] . '
                        </span></h4></div></td>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $context['sort']['byQuart'] . '
                        </span></h4></div></td>
                    </tr>';
            while (isset($context['search_results'][$count]['qmid'])) {

                echo '
                        <tr class="tableRow">
                            <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['search_results'][$count]['image'] . $context['search_results'][$count]['spacer'] . $context['search_results'][$count]['video'] . '</td>
                            <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['search_results'][$count]['user_id'] . '">' . $context['search_results'][$count]['memberName'] . '</a></td>
                            <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['search_results'][$count]['vid'] . '">' . garage_title_clean($context['search_results'][$count]['vehicle']) . '</a></td>
                            <td align="center">' . $context['search_results'][$count]['rt'] . '</td>
                            <td align="center">' . $context['search_results'][$count]['sixty'] . '</td>
                            <td align="center">' . $context['search_results'][$count]['three'] . '</td>
                            <td align="center">' . $context['search_results'][$count]['eighth'] . '&nbsp;&#64;&nbsp;' . $context['search_results'][$count]['eighthmph'] . '</td>
                            <td align="center">' . $context['search_results'][$count]['thou'] . '</td>
                            <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_quartermile;VID=' . $context['search_results'][$count]['vid'] . ';QID=' . $context['search_results'][$count]['qmid'] . '">' . garage_title_clean($context['search_results'][$count]['quart']) . '&nbsp;&#64;&nbsp;' . garage_title_clean($context['search_results'][$count]['quartmph']) . '</a></td>
                        </tr>';
                $count++;
            }
        } else {
            echo '
                    <tr>
                        <td colspan="9" align="center">' . $txt['smfg_no_quartermile_results'] . '</td>
                    </tr>';
        }
        echo '
            </table>';
    }


    // Display as - Dynoruns
    if ($_SESSION['smfg']['display_as'] == "dynoruns") {
        echo '
        <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['search_results'][$count]['vid'])) {
            echo '
                <tr> 
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    &nbsp;
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byVehicle'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byDynocenter'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byBhp'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byTorque'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byBoost'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byNitrous'] . '
                    </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $context['sort']['byPeakpoint'] . '
                    </span></h4></div></td>
                </tr>';
            while (isset($context['search_results'][$count]['vid'])) {

                echo '
                <tr class="tableRow">
                    <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['search_results'][$count]['image'] . $context['search_results'][$count]['spacer'] . $context['search_results'][$count]['video'] . '</td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['search_results'][$count]['user_id'] . '">' . $context['search_results'][$count]['memberName'] . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['search_results'][$count]['vid'] . '" >' . garage_title_clean($context['search_results'][$count]['vehicle']) . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=dc_review;BID=' . $context['search_results'][$count]['dynocenter_id'] . '">' . garage_title_clean($context['search_results'][$count]['dynocenter']) . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_dynorun;VID=' . $context['search_results'][$count]['vid'] . ';DID=' . $context['search_results'][$count]['did'] . '">' . garage_title_clean($context['search_results'][$count]['bhp']) . ' ' . garage_title_clean($context['search_results'][$count]['bhp_unit']) . '</a></td>
                    <td align="center">' . $context['search_results'][$count]['torque'] . ' ' . $context['search_results'][$count]['torque_unit'] . '</td>
                    <td align="center">' . $context['search_results'][$count]['boost'] . ' ' . $context['search_results'][$count]['boost_unit'] . '</td>
                    <td align="center">' . $context['search_results'][$count]['nitrous'] . ' Shot</td>
                    <td align="center">' . $context['search_results'][$count]['peakpoint'] . ' RPM</td>
                </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                    <td colspan="9" align="center">' . $txt['smfg_no_dynorun_results'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }


    // Display as - Laps
    if ($_SESSION['smfg']['display_as'] == "laps") {
        echo '
        <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['search_results'][$count]['lid'])) {
            echo '
            <tr>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                &nbsp;
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $context['sort']['byOwner'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $context['sort']['byVehicle'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $context['sort']['byTrack'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $context['sort']['byCondition'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $context['sort']['byType'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $context['sort']['byTime'] . '
                </span></h4></div></td>
            </tr>';
            while (isset($context['search_results'][$count]['lid'])) {

                echo '
                <tr class="tableRow">
                    <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['search_results'][$count]['image'] . '&nbsp;' . $context['search_results'][$count]['video'] . '</td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['search_results'][$count]['user_id'] . '">' . $context['search_results'][$count]['memberName'] . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['search_results'][$count]['vid'] . '">' . garage_title_clean($context['search_results'][$count]['vehicle']) . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_track;TID=' . $context['search_results'][$count]['tid'] . '">' . garage_title_clean($context['search_results'][$count]['track']) . '</a></td>
                    <td align="center">' . $context['search_results'][$count]['condition'] . '</td>
                    <td align="center">' . $context['search_results'][$count]['type'] . '</td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_laptime;VID=' . $context['search_results'][$count]['vid'] . ';LID=' . $context['search_results'][$count]['lid'] . '">' . garage_title_clean($context['search_results'][$count]['time']) . '</a></td>
                </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                    <td colspan="9" align="center">' . $txt['smfg_no_lap_results'] . '</td>
                </tr>';
        }
        echo '
        </table>';
    }

    echo '</td>
    </tr>
</table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_insurance()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo '<span style="float: right;"><b>[&nbsp;' . $context['total'] . '&nbsp;' . $txt['smfg_businesses_lower'] . '&nbsp;]&nbsp;</b></span>';

    echo (defined('SMF_VERSION') && version_compare(SMF_VERSION, '2.1', '>=') ? '' : $txt['pages'] . ': ') . $context['page_index'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
<table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
    <tr>
        <td>
        <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    // Loop through each insurance company
    $count = 0;
    if (isset($context['insurance'][$count]['bid'])) {
        while (isset($context['insurance'][$count]['bid'])) {
            echo '
                   <tr>
                   <td colspan="8">
                        <div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        <a href="' . $scripturl . '?action=garage;sa=insurance_review;BID=' . $context['insurance'][$count]['bid'] . '">' . garage_title_clean($context['insurance'][$count]['title']) . '</a>
                        </span></h4></div>
                   </td>
                </tr>
                <tr>
                      <td height="25" colspan="8" >
                    <b>' . $txt['smfg_business_name'] . ': </b><a href="' . $scripturl . '?action=garage;sa=insurance_review;BID=' . $context['insurance'][$count]['bid'] . '">' . garage_title_clean($context['insurance'][$count]['title']) . '</a>
                    &nbsp;&nbsp;<span class="smalltext">' . $txt['smfg_click_for_detail'] . '</span>
                     <br /><b>' . $txt['smfg_address'] . ': </b>' . $context['insurance'][$count]['address'] . '
                    <br /><b>' . $txt['smfg_telephone_no'] . ': </b>' . $context['insurance'][$count]['telephone'] . '
                    <br /><b>' . $txt['smfg_fax'] . ': </b>' . $context['insurance'][$count]['fax'] . '
                    <br /><b>' . $txt['smfg_website'] . ': </b>', (!empty($context['insurance'][$count]['website'])) ? '<a href="' . $context['insurance'][$count]['website'] . '" target="_blank">' . $context['insurance'][$count]['website'] . '</a>' : '', '
                    <br /><b>' . $txt['smfg_email'] . ': </b>' . $context['insurance'][$count]['email'] . '
                    <br /><b>' . $txt['smfg_opening_hours'] . ': </b>' . $context['insurance'][$count]['opening_hours'] . '
                    </td>
                </tr>
                   <tr>
                   <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                       ' . $txt['smfg_cover_type'] . '
                   </span></h4></div></td>
                   <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                       ' . $txt['smfg_lowest_premium'] . '
                   </span></h4></div></td>
                   <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                       ' . $txt['smfg_average_premium'] . '
                   </span></h4></div></td>
                   <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                       ' . $txt['smfg_highest_premium'] . '
                   </span></h4></div></td>
                </tr>';
            // Loop through all the coverage types and premiums
            $count2 = 0;
            while (isset($context['insurance'][$count][$count2]['cid'])) {
                echo '
                    <tr class="tableRow">
                        <td nowrap="nowrap">' . $context['insurance'][$count][$count2]['title'] . '</td>
                        <td align="center">' . $context['insurance'][$count][$count2]['min'] . '</td>
                        <td align="center">' . $context['insurance'][$count][$count2]['avg'] . '</td>
                        <td align="center">' . $context['insurance'][$count][$count2]['max'] . '</td>
                       </tr>';
                $count2++;
            }
            echo '
                <tr>
                    <td colspan="4">';

            if ($count < ($context['total'] - 1)) {
                echo '
                                <hr />';
            }

            echo '</td>
                </tr>';
            $count++;
        }
    } else {
        echo '
                    <tr>
                        <td align="center">' . $txt['smfg_no_insurance'] . '</td>
                    </tr>';
    }
    echo '
          </table>
        </td>
    </tr>
</table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_insurance_review()
{
    global $context, $settings, $options, $txt, $scripturl;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $context['insurance']['title'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table border="0" cellpadding="3" cellspacing="1" width="100%">
        <tr>
            <td height="25" colspan="8" >
            <b>' . $txt['smfg_business_name'] . ': </b>' . $context['insurance']['title'] . '
            <br /><b>' . $txt['smfg_address'] . ': </b>' . $context['insurance']['address'] . '
            <br /><b>' . $txt['smfg_telephone_no'] . ': </b>' . $context['insurance']['telephone'] . '
            <br /><b>' . $txt['smfg_fax'] . ': </b>' . $context['insurance']['fax'] . '
            <br /><b>' . $txt['smfg_website'] . ': </b>', (!empty($context['insurance']['website'])) ? '<a href="' . $context['insurance']['website'] . '" target="_blank">' . $context['insurance']['website'] . '</a>' : '', '
            <br /><b>' . $txt['smfg_email'] . ': </b>' . $context['insurance']['email'] . '
            <br /><b>' . $txt['smfg_opening_hours'] . ': </b>' . $context['insurance']['opening_hours'] . '
            </td>
        </tr>
        <tr>
            <td width="30%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_cover_type'] . '
            </span></h4></div></td>
            <td width="25%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_lowest_premium'] . '
            </span></h4></div></td>
            <td width="25%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_average_premium'] . '
            </span></h4></div></td>
            <td width="25%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_highest_premium'] . '
            </span></h4></div></td>
        </tr>';
    // Loop through all the coverage types and premiums
    $count = 0;
    while (isset($context['insurance'][$count]['cid'])) {
        echo '
            <tr class="tableRow">
                <td nowrap="nowrap">' . $context['insurance'][$count]['title'] . '</td>
                <td align="center">' . $context['insurance'][$count]['min'] . '</td>
                <td align="center">' . $context['insurance'][$count]['avg'] . '</td>
                <td align="center">' . $context['insurance'][$count]['max'] . '</td>
            </tr>';
        $count++;
    }
    echo '
        <tr>
            <td colspan="4"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
            ' . $txt['smfg_latest_customers'] . '
            </span></h4></div></td>
        </tr>';
    $count = 0;
    if (isset($context['insurance'][$count]['pid'])) {
        echo '
            <tr>
                <td width="30%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_owner'] . '
                </span></h4></div></td>
                <td width="25%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_vehicle'] . '
                </span></h4></div></td>
                <td width="25%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_premium'] . '
                </span></h4></div></td>
                <td width="25%"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_cover_type'] . '
                </span></h4></div></td>
            </tr>';
        while (isset($context['insurance'][$count]['pid'])) {

            echo '
            <tr class="tableRow">
                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['insurance'][$count]['user_id'] . '">' . $context['insurance'][$count]['memberName'] . '</a></td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['insurance'][$count]['vid'] . '">' . garage_title_clean($context['insurance'][$count]['vehicle']) . '</a></td>
                <td align="center">' . $context['insurance'][$count]['premium'] . '</td>
                <td align="center">' . $context['insurance'][$count]['cover_type'] . '</td>
            </tr>';
            $count++;
        }
    } else {
        echo '
        <tr>
            <td colspan="4" align="center">' . $txt['smfg_no_customers'] . '</td>
        </tr>';
    }
    echo '
      </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_shops()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo '<span style="float: right;"><b>[&nbsp;' . $context['total'] . '&nbsp;' . $txt['smfg_businesses_lower'] . '&nbsp;]&nbsp;</b></span>';

    echo (defined('SMF_VERSION') && version_compare(SMF_VERSION, '2.1', '>=') ? '' : $txt['pages'] . ': ') . $context['page_index'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '

<table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
    <tr>
        <td>
        <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    if (isset($context['shops'][$count]['bid'])) {
        while (isset($context['shops'][$count]['bid'])) {

            echo '
                <tr>
                      <td colspan="8">
                        <div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        <a href="' . $scripturl . '?action=garage;sa=shop_review;BID=' . $context['shops'][$count]['bid'] . '">' . garage_title_clean($context['shops'][$count]['title']) . '</a>
                        </span></h4></div></td>
                </tr>
                <tr>
                      <td height="25" colspan="8" ><b>' . $txt['smfg_business_name'] . ': </b><a href="' . $scripturl . '?action=garage;sa=shop_review;BID=' . $context['shops'][$count]['bid'] . '">' . garage_title_clean($context['shops'][$count]['title']) . '</a>
                    &nbsp;&nbsp;<span class="smalltext">' . $txt['smfg_click_for_detail'] . '</span>
                     <br /><b>' . $txt['smfg_address'] . ': </b>' . $context['shops'][$count]['address'] . '
                    <br /><b>' . $txt['smfg_telephone_no'] . ': </b>' . $context['shops'][$count]['telephone'] . '
                    <br /><b>' . $txt['smfg_fax'] . ':</b>' . $context['shops'][$count]['fax'] . '
                    <br /><b>' . $txt['smfg_website'] . ': </b>', (!empty($context['shops'][$count]['website'])) ? '<a href="' . $context['shops'][$count]['website'] . '" target="_blank">' . $context['shops'][$count]['website'] . '</a>' : '', '
                    <br /><b>' . $txt['smfg_email'] . ': </b>' . $context['shops'][$count]['email'] . '
                    <br /><b>' . $txt['smfg_opening_hours'] . ': </b>' . $context['shops'][$count]['opening_hours'];
            if ($context['shops'][$count]['total_poss_rating']) {
                if ($smfgSettings['rating_system'] == 0) {
                    echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $context['shops'][$count]['total_rating'] . '/' . $context['shops'][$count]['total_poss_rating'] . '</td>';
                } else {
                    if ($smfgSettings['rating_system'] == 1) {
                        echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $context['shops'][$count]['total_rating'] . '/10 (' . $txt['smfg_rated'] . ' ' . ($context['shops'][$count]['total_poss_rating'] / 10) . ' ' . $txt['smfg_times'] . ')</td>';
                    }
                }
            } else {
                echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $txt['smfg_not_rated'] . '</td>';
            }
            echo '                                
                </tr>
                 <tr>
                    <td colspan="6">
                        <div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_latest_customers'] . '
                    </span></h4></div></td>
                </tr>';
            $count2 = 0;
            if (isset($context['shops'][$count][$count2]['vid'])) {
                echo '
                <tr>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_owner'] . '
                        </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_vehicle'] . '
                        </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_modification'] . '
                        </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_purchase_rating'] . '
                        </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_product_rating'] . '
                        </span></h4></div></td>
                    <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        ' . $txt['smfg_price'] . '
                        </span></h4></div></td>
                </tr>';
                while (isset($context['shops'][$count][$count2]['vid'])) {

                    echo '
                    <tr class="tableRow">
                        <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['shops'][$count][$count2]['user_id'] . '">' . $context['shops'][$count][$count2]['memberName'] . '</a></td>
                        <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['shops'][$count][$count2]['vid'] . '">' . garage_title_clean($context['shops'][$count][$count2]['vehicle']) . '</a></td>
                        <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['shops'][$count][$count2]['vid'] . ';MID=' . $context['shops'][$count][$count2]['mid'] . '">' . garage_title_clean($context['shops'][$count][$count2]['mod_title']) . '</a></td>
                        <td align="center" nowrap="nowrap">' . $context['shops'][$count][$count2]['purchase_rating'] . '</td>
                        <td align="center" nowrap="nowrap">' . $context['shops'][$count][$count2]['product_rating'] . '</td>
                        <td align="center" nowrap="nowrap">' . $context['shops'][$count][$count2]['price'] . '</td>
                    </tr>';
                    $count2++;
                }
            } else {
                echo '
                    <tr>
                        <td colspan="6" align="center">' . $txt['smfg_no_customers'] . '</td>
                    </tr>
                    ';
            }
            echo '
                <tr>
                    <td colspan="6">';

            if ($count < ($context['total'] - 1)) {
                echo '
                                <hr />';
            }

            echo '</td>
                </tr>';
            $count++;
        }
    } else {
        echo '
                    <tr>
                        <td align="center">' . $txt['smfg_no_shops'] . '</td>
                    </tr>';
    }
    echo '
        </table>
        </td>
    </tr>
</table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_shop_review()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $context['shops']['title'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table border="0" cellpadding="3" cellspacing="1" width="100%">';

    $count = 0;

    echo '
        <tr>
            <td height="25" colspan="8" ><b>' . $txt['smfg_business_name'] . ': </b>' . $context['shops']['title'] . '
            <br /><b>' . $txt['smfg_address'] . ': </b>' . $context['shops']['address'] . '
            <br /><b>' . $txt['smfg_telephone_no'] . ': </b>' . $context['shops']['telephone'] . '
            <br /><b>' . $txt['smfg_fax'] . ': </b>' . $context['shops']['fax'] . '
            <br /><b>' . $txt['smfg_website'] . ': </b>', (!empty($context['shops']['website'])) ? '<a href="' . $context['shops']['website'] . '" target="_blank">' . $context['shops']['website'] . '</a>' : '', '
            <br /><b>' . $txt['smfg_email'] . ': </b>' . $context['shops']['email'] . '
            <br /><b>' . $txt['smfg_opening_hours'] . ': </b>' . $context['shops']['opening_hours'];
    if ($context['shops']['total_poss_rating']) {
        if ($smfgSettings['rating_system'] == 0) {
            echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $context['shops']['total_rating'] . '/' . $context['shops']['total_poss_rating'] . '</td>';
        } else {
            if ($smfgSettings['rating_system'] == 1) {
                echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $context['shops']['total_rating'] . '/10 (' . $txt['smfg_rated'] . ' ' . ($context['shops']['total_poss_rating'] / 10) . ' ' . $txt['smfg_times'] . ')</td>';
            }
        }
    } else {
        echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $txt['smfg_not_rated'] . '</td>';
    }
    echo '
        </tr>
        <tr>
            <td colspan="6"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_latest_customers'] . '
            </span></h4></div></td>
        </tr>';
    if (isset($context['shops'][$count]['vid'])) {
        echo '
            <tr>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_owner'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_vehicle'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_modification'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_purchase_rating'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_product_rating'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_price'] . '
                </span></h4></div></td>
            </tr>';
        while (isset($context['shops'][$count]['vid'])) {

            echo '
                <tr class="tableRow">
                    <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['shops'][$count]['user_id'] . '">' . $context['shops'][$count]['memberName'] . '</a></td>
                    <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['shops'][$count]['vid'] . '">' . garage_title_clean($context['shops'][$count]['vehicle']) . '</a></td>
                    <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['shops'][$count]['vid'] . ';MID=' . $context['shops'][$count]['mid'] . '">' . garage_title_clean($context['shops'][$count]['mod_title']) . '</a></td>
                    <td align="center" nowrap="nowrap">' . $context['shops'][$count]['purchase_rating'] . '</td>
                    <td align="center" nowrap="nowrap">' . $context['shops'][$count]['product_rating'] . '</td>
                    <td align="center" nowrap="nowrap">' . $context['shops'][$count]['price'] . '</td>
                </tr>';
            $count++;
        }
    } else {
        echo '
            <tr>
                <td colspan="6" align="center">' . $txt['smfg_no_customers'] . '</td>
            </tr>';
    }

    echo '
        </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_garages()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo '<span style="float: right;"><b>[&nbsp;' . $context['total'] . '&nbsp;' . $txt['smfg_businesses_lower'] . '&nbsp;]&nbsp;</b></span>';

    echo (defined('SMF_VERSION') && version_compare(SMF_VERSION, '2.1', '>=') ? '' : $txt['pages'] . ': ') . $context['page_index'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
<table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
    <tr>
    <tr>
        <td>
        <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    if (isset($context['garages'][$count]['bid'])) {
        while (isset($context['garages'][$count]['bid'])) {

            echo '
                <tr>
                    <td colspan="8" align="center">
                        <div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                        <a href="' . $scripturl . '?action=garage;sa=garage_review;BID=' . $context['garages'][$count]['bid'] . '">' . garage_title_clean($context['garages'][$count]['title']) . '</a>
                        </span></h4></div>                    
                    </td>
                </tr>        
                <tr>
                      <td height="25" colspan="8" >
                    <b>' . $txt['smfg_business_name'] . ': </b><a href="' . $scripturl . '?action=garage;sa=garage_review;BID=' . $context['garages'][$count]['bid'] . '">' . garage_title_clean($context['garages'][$count]['title']) . '</a>
                    &nbsp;&nbsp;<span class="smalltext">' . $txt['smfg_click_for_detail'] . '</span>
                     <br /><b>' . $txt['smfg_address'] . ': </b>' . $context['garages'][$count]['address'] . '
                    <br /><b>' . $txt['smfg_telephone_no'] . ': </b>' . $context['garages'][$count]['telephone'] . '
                    <br /><b>' . $txt['smfg_fax'] . ': </b>' . $context['garages'][$count]['fax'] . '
                    <br /><b>' . $txt['smfg_website'] . ': </b>', (!empty($context['garages'][$count]['website'])) ? '<a href="' . $context['garages'][$count]['website'] . '" target="_blank">' . $context['garages'][$count]['website'] . '</a>' : '', '
                    <br /><b>' . $txt['smfg_email'] . ': </b>' . $context['garages'][$count]['email'] . '
                    <br /><b>' . $txt['smfg_opening_hours'] . ': </b>' . $context['garages'][$count]['opening_hours'];
            if ($context['garages'][$count]['total_poss_rating']) {
                if ($smfgSettings['rating_system'] == 0) {
                    echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $context['garages'][$count]['total_rating'] . '/' . $context['garages'][$count]['total_poss_rating'] . '</td>';
                } else {
                    if ($smfgSettings['rating_system'] == 1) {
                        echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $context['garages'][$count]['total_rating'] . '/10 (' . $txt['smfg_rated'] . ' ' . ($context['garages'][$count]['total_poss_rating'] / 10) . ' ' . $txt['smfg_times'] . ')</td>';
                    }
                }
            } else {
                echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $txt['smfg_not_rated'] . '</td>';
            }
            echo '
                 </tr>
                 <tr>
                    <td colspan="4"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_latest_customers'] . '
                    </span></h4></div></td>
                </tr>';
            $count2 = 0;
            $count3 = 0;
            if (isset($context['garages']['mods'][$count][$count2]['vid']) || isset($context['garages']['services'][$count][$count2]['vid'])) {
                echo '
                    <tr>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                            ' . $txt['smfg_owner'] . '
                        </span></h4></div></td>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                            ' . $txt['smfg_vehicle'] . '
                        </span></h4></div></td>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                            ' . $txt['smfg_modification'] . '&nbsp;/&nbsp;' . $txt['smfg_services'] . '
                        </span></h4></div></td>
                        <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                            ' . $txt['smfg_rating'] . '
                        </span></h4></div></td>
                    </tr>';
                if (isset($context['garages']['mods'][$count][$count2]['vid'])) {
                    while (isset($context['garages']['mods'][$count][$count2]['vid'])) {

                        echo '
                            <tr class="tableRow">
                                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['garages']['mods'][$count][$count2]['user_id'] . '">' . $context['garages']['mods'][$count][$count2]['memberName'] . '</a></td>
                                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['garages']['mods'][$count][$count2]['vid'] . '">' . garage_title_clean($context['garages']['mods'][$count][$count2]['vehicle']) . '</a></td>
                                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['garages']['mods'][$count][$count2]['vid'] . ';MID=' . $context['garages']['mods'][$count][$count2]['mid'] . '">' . garage_title_clean($context['garages']['mods'][$count][$count2]['mod_title']) . '</a></td>
                                <td align="center" nowrap="nowrap">' . $context['garages']['mods'][$count][$count2]['install_rating'] . '</td>
                            </tr>';
                        $count2++;
                    }
                }
                if (isset($context['garages']['services'][$count][$count3]['vid'])) {
                    while (isset($context['garages']['services'][$count][$count3]['vid'])) {

                        echo '
                            <tr class="tableRow">
                                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['garages']['services'][$count][$count3]['user_id'] . '">' . $context['garages']['services'][$count][$count3]['memberName'] . '</a></td>
                                <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['garages']['services'][$count][$count3]['vid'] . '">' . garage_title_clean($context['garages']['services'][$count][$count3]['vehicle']) . '</a></td>
                                <td align="center" nowrap="nowrap">' . $context['garages']['services'][$count][$count3]['service_type'] . '</td>
                                <td align="center" nowrap="nowrap">' . $context['garages']['services'][$count][$count3]['rating'] . '</td>
                            </tr>';
                        $count3++;
                    }
                }
            } else {
                echo '
                    <tr>
                        <td colspan="6" align="center">' . $txt['smfg_no_customers'] . '</td>
                    </tr>';
            }
            echo '
                <tr>
                    <td colspan="4">';

            if ($count < ($context['total'] - 1)) {
                echo '
                                <hr />';
            }

            echo '</td>
                </tr>';
            $count++;
        }
    } else {
        echo '
                    <tr>
                        <td align="center">' . $txt['smfg_no_garages'] . '</td>
                    </tr>';
    }
    echo '
        </table>
        </td>
    </tr>
</table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_garage_review()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $context['garages']['title'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
        <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;

    echo '     
            <tr>
                <td height="25" colspan="8" >
                <b>' . $txt['smfg_business_name'] . ': </b>' . $context['garages']['title'] . '
                <br /><b>' . $txt['smfg_address'] . ': </b>' . $context['garages']['address'] . '
                <br /><b>' . $txt['smfg_telephone_no'] . ': </b>' . $context['garages']['telephone'] . '
                <br /><b>' . $txt['smfg_fax'] . ':</b>' . $context['garages']['fax'] . '
                <br /><b>' . $txt['smfg_website'] . ': </b>', (!empty($context['garages']['website'])) ? '<a href="' . $context['garages']['website'] . '" target="_blank">' . $context['garages']['website'] . '</a>' : '', '
                <br /><b>' . $txt['smfg_email'] . ': </b>' . $context['garages']['email'] . '
                <br /><b>' . $txt['smfg_opening_hours'] . ': </b>' . $context['garages']['opening_hours'];
    if ($context['garages']['total_poss_rating']) {
        if ($smfgSettings['rating_system'] == 0) {
            echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $context['garages']['total_rating'] . '/' . $context['garages']['total_poss_rating'] . '</td>';
        } else {
            if ($smfgSettings['rating_system'] == 1) {
                echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $context['garages']['total_rating'] . '/10 (' . $txt['smfg_rated'] . ' ' . ($context['garages']['total_poss_rating'] / 10) . ' ' . $txt['smfg_times'] . ')</td>';
            }
        }
    } else {
        echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $txt['smfg_not_rated'] . '</td>';
    }
    echo '
            </tr>
            <tr>
                <td align="center" colspan="6"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_latest_customers'] . '
                </span></h4></div></td>
            </tr>';
    $count2 = 0;
    if (isset($context['garages']['mods'][$count]['vid']) || isset($context['garages']['services'][$count]['vid'])) {
        echo '
                <tr>
                    <td align="center"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_owner'] . '
                    </span></h4></div></td>
                    <td align="center"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_vehicle'] . '
                    </span></h4></div></td>
                    <td align="center"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_modification'] . '&nbsp;/&nbsp;' . $txt['smfg_services'] . '
                    </span></h4></div></td>
                    <td align="center"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_rating'] . '
                    </span></h4></div></td>
                </tr>';
        if (isset($context['garages']['mods'][$count]['vid'])) {
            while (isset($context['garages']['mods'][$count]['vid'])) {

                echo '
                        <tr class="tableRow">
                            <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['garages']['mods'][$count]['user_id'] . '">' . $context['garages']['mods'][$count]['memberName'] . '</a></td>
                            <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['garages']['mods'][$count]['vid'] . '">' . garage_title_clean($context['garages']['mods'][$count]['vehicle']) . '</a></td>
                            <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['garages']['mods'][$count]['vid'] . ';MID=' . $context['garages']['mods'][$count]['mid'] . '">' . garage_title_clean($context['garages']['mods'][$count]['mod_title']) . '</a></td>
                            <td align="center" nowrap="nowrap">' . $context['garages']['mods'][$count]['install_rating'] . '</td>
                        </tr>';
                $count++;
            }
        }
        if (isset($context['garages']['services'][$count2]['vid'])) {
            while (isset($context['garages']['services'][$count2]['vid'])) {

                echo '
                        <tr class="tableRow">
                            <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['garages']['services'][$count2]['user_id'] . '">' . $context['garages']['services'][$count2]['memberName'] . '</a></td>
                            <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['garages']['services'][$count2]['vid'] . '">' . garage_title_clean($context['garages']['services'][$count2]['vehicle']) . '</a></td>
                            <td align="center" nowrap="nowrap">' . $context['garages']['services'][$count2]['service_type'] . '</td>
                            <td align="center" nowrap="nowrap">' . $context['garages']['services'][$count2]['rating'] . '</td>
                        </tr>';
                $count2++;
            }
        }
    } else {
        echo '
                <tr>
                    <td colspan="6" align="center">' . $txt['smfg_no_customers'] . '</td>
                </tr>
                ';
    }

    $count = 0;
    if (!empty($context['garages']['mods'][$count]['comment'])) {
        echo '
                <tr>
                    <td align="center" colspan="6"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                    ' . $txt['smfg_comments'] . '
                    </span></h4></div></td>
                </tr>
                <tr>
                    <td colspan="6" align="left">';
        // Loop through the num of results and only show the ones with a comment
        while ($count < $context['garages']['mods']['total']) {
            if ($context['garages']['mods'][$count]['comment'] != "") {
                $context['garages']['mods'][$count]['comment_str'] = '<a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['garages']['mods'][$count]['user_id'] . '">' . $context['garages']['mods'][$count]['memberName'] . '</a> -&gt; <a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['garages']['mods'][$count]['vid'] . ';MID=' . $context['garages']['mods'][$count]['mid'] . '">' . garage_title_clean($context['garages']['mods'][$count]['mod_title']) . '</a> -&gt; ' . $context['garages']['mods'][$count]['comment'] . '<br />';
                echo '<span class="smalltext">' . $context['garages']['mods'][$count]['comment_str'] . '</span>';
            }
            $count++;
        }
        echo '</td>
                </tr>';
    }
    echo '
        </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_manufacturer_review()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $context['mfg']['title'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
        <table border="0" cellpadding="3" cellspacing="1" width="100%">';

    $count = 0;

    echo '
            <tr>
                <td height="25" colspan="8" ><b>' . $txt['smfg_business_name'] . ': </b>' . $context['mfg']['title'] . '
                <br /><b>' . $txt['smfg_address'] . ': </b>' . $context['mfg']['address'] . '
                <br /><b>' . $txt['smfg_telephone_no'] . ': </b>' . $context['mfg']['telephone'] . '
                <br /><b>' . $txt['smfg_fax'] . ':</b>' . $context['mfg']['fax'] . '
                <br /><b>' . $txt['smfg_website'] . ': </b>', (!empty($context['mfg']['website'])) ? '<a href="' . $context['mfg']['website'] . '" target="_blank">' . $context['mfg']['website'] . '</a>' : '', '
                <br /><b>' . $txt['smfg_email'] . ': </b>' . $context['mfg']['email'] . '
                <br /><b>' . $txt['smfg_opening_hours'] . ': </b>' . $context['mfg']['opening_hours'];
    if ($context['mfg']['total_poss_rating']) {
        if ($smfgSettings['rating_system'] == 0) {
            echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $context['mfg']['total_rating'] . '/' . $context['mfg']['total_poss_rating'] . '</td>';
        } else {
            if ($smfgSettings['rating_system'] == 1) {
                echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $context['mfg']['total_rating'] . '/10 (' . $txt['smfg_rated'] . ' ' . ($context['mfg']['total_poss_rating'] / 10) . ' ' . $txt['smfg_times'] . '</td>';
            }
        }
    } else {
        echo '<br /><b>' . $txt['smfg_rating'] . ': </b>' . $txt['smfg_not_rated'] . '</td>';
    }
    echo '
            </tr>
            <tr>
                <td colspan="6"><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_latest_customers'] . '
                </span></h4></div></td>
            </tr>';
    if (isset($context['mfg'][$count]['vid'])) {
        echo '
            <tr>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_owner'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_vehicle'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_modification'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_purchase_rating'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_product_rating'] . '
                </span></h4></div></td>
                <td><div class="title_bar" style="text-align: center;"><h4 class="titlebg"><span class="ie6_header">
                ' . $txt['smfg_price'] . '
                </span></h4></div></td>
            </tr>';
        while (isset($context['mfg'][$count]['vid'])) {

            echo '
                <tr class="tableRow">
                    <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['mfg'][$count]['user_id'] . '">' . $context['mfg'][$count]['memberName'] . '</a></td>
                    <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['mfg'][$count]['vid'] . '">' . garage_title_clean($context['mfg'][$count]['vehicle']) . '</a></td>
                    <td align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['mfg'][$count]['vid'] . ';MID=' . $context['mfg'][$count]['mid'] . '">' . garage_title_clean($context['mfg'][$count]['mod_title']) . '</a></td>
                    <td align="center" nowrap="nowrap">' . $context['mfg'][$count]['purchase_rating'] . '</td>
                    <td align="center" nowrap="nowrap">' . $context['mfg'][$count]['product_rating'] . '</td>
                    <td align="center" nowrap="nowrap">' . $context['mfg'][$count]['price'] . '</td>
                </tr>';
            $count++;
        }
    } else {
        echo '
                <tr>
                    <td colspan="6" align="center">' . $txt['smfg_no_customers'] . '</td>
                </tr>';
    }

    echo '
            </table>
        </td>
    </tr>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_dynocenter_review()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $context['dc']['title'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';

    echo '
    <table border="0" cellpadding="3" cellspacing="1" width="100%">';

    $count = 0;

    echo '
        <tr>
            <td height="25" colspan="8" ><b>' . $txt['smfg_business_name'] . ': </b>' . $context['dc']['title'] . '
            <br /><b>' . $txt['smfg_address'] . ': </b>' . $context['dc']['address'] . '
            <br /><b>' . $txt['smfg_telephone_no'] . ': </b>' . $context['dc']['telephone'] . '
            <br /><b>' . $txt['smfg_fax'] . ':</b>' . $context['dc']['fax'] . '
            <br /><b>' . $txt['smfg_website'] . ': </b>', (!empty($context['dc']['website'])) ? '<a href="' . $context['dc']['website'] . '" target="_blank">' . $context['dc']['website'] . '</a>' : '', '
            <br /><b>' . $txt['smfg_email'] . ': </b>' . $context['dc']['email'] . '
            <br /><b>' . $txt['smfg_opening_hours'] . ': </b>' . $context['dc']['opening_hours'] . '
        </tr>
    </table>';

    echo '  
    </div>
    </div>
    <span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_browse_tables()
{
    global $context, $settings, $options, $txt, $scripturl, $smfgSettings;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }


    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo '<span style="float: right;"><b>[&nbsp;' . $context['total'] . '&nbsp;' . $context['browse_type_total'] . '&nbsp;]&nbsp;</b></span>';

    echo (defined('SMF_VERSION') && version_compare(SMF_VERSION, '2.1', '>=') ? '' : $txt['pages'] . ': ') . $context['page_index'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '
<span class="clear upperframe"><span></span></span>
<div class="roundframe"><div class="innerframe">';

    // Display as - Vehicles
    if ($context['browse_type'] == "vehicles") {
        echo '
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['vid'])) {
            echo '
                    <tr>
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        &nbsp;
                        </span></h4></div></td> 
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byYear'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byMake'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byModel'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byColor'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byOwner'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byViews'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byMods'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byUpdated'] . '
                        </span></h4></div></td>   
                    </tr>';
            while (isset($context['browse_tables'][$count]['vid'])) {

                echo '
                        <tr class="tableRow">
                        <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['browse_tables'][$count]['image'] . $context['browse_tables'][$count]['spacer'] . $context['browse_tables'][$count]['video'] . '</td>
                        <td align="center">' . $context['browse_tables'][$count]['made_year'] . '</td>
                        <td align="center">' . $context['browse_tables'][$count]['make'] . '</td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '">' . garage_title_clean($context['browse_tables'][$count]['model']) . '</a></td>
                        <td align="center">' . $context['browse_tables'][$count]['color'] . '</td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>
                        <td align="center">' . $context['browse_tables'][$count]['views'] . '</td>
                        <td align="center">' . $context['browse_tables'][$count]['total_mods'] . '</td>
                        <td align="center">' . date($context['date_format'],
                        $context['browse_tables'][$count]['date_updated']) . '</td>
                        </tr>';
                $count++;
            }
        } else {
            echo '
                    <tr>
                    <td colspan="9" align="center">' . $txt['smfg_no_vehicle_results'] . '</td>
                    </tr>';
        }
        echo '
                </table>';
    }

    // Display as - Modifications
    if ($context['browse_type'] == "modifications") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['vid'])) {
            echo '
                <tr>
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    &nbsp;
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byYear'] . '
                    </span></h4></div></td>   
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byMake'] . '
                    </span></h4></div></td>   
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byModel'] . '
                    </span></h4></div></td>   
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td>   
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byMod'] . '
                    </span></h4></div></td>   
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byUpdated'] . '
                    </span></h4></div></td>   
                </tr>';
            while (isset($context['browse_tables'][$count]['vid'])) {

                echo '
                <tr class="tableRow">
                <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['browse_tables'][$count]['image'] . $context['browse_tables'][$count]['spacer'] . $context['browse_tables'][$count]['video'] . '</td>
                <td align="center">' . $context['browse_tables'][$count]['made_year'] . '</td>
                <td align="center">' . $context['browse_tables'][$count]['make'] . '</td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '">' . garage_title_clean($context['browse_tables'][$count]['model']) . '</a></td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['browse_tables'][$count]['vid'] . ';MID=' . $context['browse_tables'][$count]['mid'] . '" title="' . $context['browse_tables'][$count]['mod_tooltip'] . '" class="smfg_videoTitle">' . garage_title_clean($context['browse_tables'][$count]['modification']) . '</td>
                <td align="center">' . date($context['date_format'],
                        $context['browse_tables'][$count]['date_updated']) . '</td>
                </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                <td colspan="9" align="center">' . $txt['smfg_no_modification_results'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }

    // Display as - Quartermiles
    if ($context['browse_type'] == "quartermiles") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['qmid'])) {
            echo '
                    <tr>
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        &nbsp;
                        </span></h4></div></td>  
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byUsername'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byVehicle'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byRt'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['bySixty'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byThree'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byEighth'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byThou'] . '
                        </span></h4></div></td>   
                        <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                        ' . $context['sort']['byQuart'] . '
                        </span></h4></div></td>   
                    </tr>';
            while (isset($context['browse_tables'][$count]['qmid'])) {

                echo '
                        <tr class="tableRow">
                            <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['browse_tables'][$count]['image'] . $context['browse_tables'][$count]['spacer'] . $context['browse_tables'][$count]['video'] . '</td>
                            <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>
                            <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '">' . garage_title_clean($context['browse_tables'][$count]['vehicle']) . '</a></td>
                            <td align="center">' . $context['browse_tables'][$count]['rt'] . '</td>
                            <td align="center">' . $context['browse_tables'][$count]['sixty'] . '</td>
                            <td align="center">' . $context['browse_tables'][$count]['three'] . '</td>
                            <td align="center">' . $context['browse_tables'][$count]['eighth'] . '&nbsp;&#64;&nbsp;' . $context['browse_tables'][$count]['eighthmph'] . '</td>
                            <td align="center">' . $context['browse_tables'][$count]['thou'] . '</td>
                            <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_quartermile;VID=' . $context['browse_tables'][$count]['vid'] . ';QID=' . $context['browse_tables'][$count]['qmid'] . '">' . garage_title_clean($context['browse_tables'][$count]['quart']) . '&nbsp;&#64;&nbsp;' . garage_title_clean($context['browse_tables'][$count]['quartmph']) . '</a></td>
                        </tr>';
                $count++;
            }
        } else {
            echo '
                    <tr>
                        <td colspan="9" align="center">' . $txt['smfg_no_quartermile_results'] . '</td>
                    </tr>';
        }
        echo '
            </table>';
    }


    // Display as - Dynoruns
    if ($context['browse_type'] == "dynoruns") {
        echo '
        <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['vid'])) {
            echo '
                <tr> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    &nbsp;
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td>  
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byVehicle'] . '
                    </span></h4></div></td>  
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byDynocenter'] . '
                    </span></h4></div></td>  
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byBhp'] . '
                    </span></h4></div></td>  
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byTorque'] . '
                    </span></h4></div></td>  
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byBoost'] . '
                    </span></h4></div></td>  
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byNitrous'] . '
                    </span></h4></div></td>  
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byPeakpoint'] . '
                    </span></h4></div></td>  
                </tr>';
            while (isset($context['browse_tables'][$count]['vid'])) {

                echo '
                    <tr class="tableRow">
                        <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['browse_tables'][$count]['image'] . $context['browse_tables'][$count]['spacer'] . $context['browse_tables'][$count]['video'] . '</td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '" >' . garage_title_clean($context['browse_tables'][$count]['vehicle']) . '</a></td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=dc_review;BID=' . $context['browse_tables'][$count]['dynocenter_id'] . '">' . garage_title_clean($context['browse_tables'][$count]['dynocenter']) . '</a></td>
                        <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_dynorun;VID=' . $context['browse_tables'][$count]['vid'] . ';DID=' . $context['browse_tables'][$count]['did'] . '">' . garage_title_clean($context['browse_tables'][$count]['bhp']) . ' ' . garage_title_clean($context['browse_tables'][$count]['bhp_unit']) . '</a></td>
                        <td align="center">' . $context['browse_tables'][$count]['torque'] . ' ' . $context['browse_tables'][$count]['torque_unit'] . '</td>
                        <td align="center">' . $context['browse_tables'][$count]['boost'] . ' ' . $context['browse_tables'][$count]['boost_unit'] . '</td>
                        <td align="center">' . $context['browse_tables'][$count]['nitrous'] . ' Shot</td>
                        <td align="center">' . $context['browse_tables'][$count]['peakpoint'] . ' RPM</td>
                    </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                    <td colspan="9" align="center">' . $txt['smfg_no_dynorun_results'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }


    // Display as - Laps
    if ($context['browse_type'] == "laps") {
        echo '
        <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['lid'])) {
            echo '
            <tr>
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                &nbsp;
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $context['sort']['byOwner'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $context['sort']['byVehicle'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $context['sort']['byTrack'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $context['sort']['byCondition'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $context['sort']['byType'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $context['sort']['byTime'] . '
                </span></h4></div></td> 
            </tr>';
            while (isset($context['browse_tables'][$count]['lid'])) {

                echo '
                <tr class="tableRow">
                    <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['browse_tables'][$count]['image'] . '&nbsp;' . $context['browse_tables'][$count]['video'] . '</td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '">' . garage_title_clean($context['browse_tables'][$count]['vehicle']) . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_track;TID=' . $context['browse_tables'][$count]['tid'] . '">' . garage_title_clean($context['browse_tables'][$count]['track']) . '</a></td>
                    <td align="center">' . $context['browse_tables'][$count]['condition'] . '</td>
                    <td align="center">' . $context['browse_tables'][$count]['type'] . '</td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_laptime;VID=' . $context['browse_tables'][$count]['vid'] . ';LID=' . $context['browse_tables'][$count]['lid'] . '">' . garage_title_clean($context['browse_tables'][$count]['time']) . '</a></td>
                </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                    <td colspan="9" align="center">' . $txt['smfg_no_lap_results'] . '</td>
                </tr>';
        }
        echo '
        </table>';
    }

    // Display as - Most Modified
    if ($context['browse_type'] == "mostmodified") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['vid'])) {
            echo '
                <tr>
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byYear'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byMake'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byModel'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byMods'] . '
                    </span></h4></div></td> 
                </tr>';
            while (isset($context['browse_tables'][$count]['vid'])) {

                echo '
                <tr class="tableRow">
                <td align="center">' . $context['browse_tables'][$count]['made_year'] . '</td>
                <td align="center">' . $context['browse_tables'][$count]['make'] . '</td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '">' . garage_title_clean($context['browse_tables'][$count]['model']) . '</a></td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>
                <td align="center">' . $context['browse_tables'][$count]['total_mods'] . '</td>
                </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                <td colspan="9" align="center">' . $txt['smfg_no_vehicle_results'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }

    // Display as - Most Viewed
    if ($context['browse_type'] == "mostviewed") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['vid'])) {
            echo '
                <tr>
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byYear'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byMake'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byModel'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byViews'] . '
                    </span></h4></div></td> 
                </tr>';
            while (isset($context['browse_tables'][$count]['vid'])) {

                echo '
                <tr class="tableRow">
                <td align="center">' . $context['browse_tables'][$count]['made_year'] . '</td>
                <td align="center">' . $context['browse_tables'][$count]['make'] . '</td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '">' . garage_title_clean($context['browse_tables'][$count]['model']) . '</a></td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>
                <td align="center">' . $context['browse_tables'][$count]['views'] . '</td>
                </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                <td colspan="9" align="center">' . $txt['smfg_no_vehicle_results'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }

    // Display as - Latest Service
    if ($context['browse_type'] == "latestservice") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['vid'])) {
            echo '
                <tr>
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byYear'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byMake'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byModel'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byType'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byCreated'] . '
                    </span></h4></div></td> 
                </tr>';
            while (isset($context['browse_tables'][$count]['vid'])) {

                echo '
                <tr class="tableRow">
                <td align="center">' . $context['browse_tables'][$count]['made_year'] . '</td>
                <td align="center">' . $context['browse_tables'][$count]['make'] . '</td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '">' . garage_title_clean($context['browse_tables'][$count]['model']) . '</a></td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>
                <td align="center">' . $context['browse_tables'][$count]['type'] . '</td>
                <td align="center">' . date($context['date_format'], $context['browse_tables'][$count]['created']) . '</td>
                </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                <td colspan="9" align="center">' . $txt['smfg_no_vehicle_results'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }

    // Display as - Top Rated
    if ($context['browse_type'] == "toprated") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['vid'])) {
            echo '
                <tr>
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byYear'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byMake'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byModel'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byRating'] . '
                    </span></h4></div></td> 
                </tr>';
            while (isset($context['browse_tables'][$count]['vid'])) {

                echo '
                <tr class="tableRow">
                <td align="center">' . $context['browse_tables'][$count]['made_year'] . '</td>
                <td align="center">' . $context['browse_tables'][$count]['make'] . '</td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '">' . garage_title_clean($context['browse_tables'][$count]['model']) . '</a></td>
                <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>';
                if ($context['browse_tables'][$count]['poss_rating']) {
                    if ($smfgSettings['rating_system'] == 0) {
                        echo '
                        <td align="center" valign="middle" nowrap="nowrap">' . $context['browse_tables'][$count]['rating'] . '/' . $context['browse_tables'][$count]['poss_rating'] . '</td>';
                    } else {
                        if ($smfgSettings['rating_system'] == 1) {
                            echo '
                        <td align="center" valign="middle" nowrap="nowrap">' . $context['browse_tables'][$count]['rating'] . '/10</td>';
                        }
                    }
                } else {
                    echo '
                        <td align="center" valign="middle" nowrap="nowrap">' . $txt['smfg_vehicle_not_rated'] . '</td>';
                }
                echo '</tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                    <td colspan="9" align="center">' . $txt['smfg_no_vehicle_results'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }

    // Display as - Most Spent
    if ($context['browse_type'] == "mostspent") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['vid'])) {
            echo '
                <tr>
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byYear'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byMake'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byModel'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byTotalSpent'] . '
                    </span></h4></div></td> 
                </tr>';
            while (isset($context['browse_tables'][$count]['vid'])) {

                echo '
                <tr class="tableRow">
                    <td align="center">' . $context['browse_tables'][$count]['made_year'] . '</td>
                    <td align="center">' . $context['browse_tables'][$count]['make'] . '</td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '">' . garage_title_clean($context['browse_tables'][$count]['model']) . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>
                    <td align="center">' . $context['browse_tables'][$count]['total_spent'] . ' ' . $context['browse_tables'][$count]['currency'] . '</td>
                </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                <td colspan="9" align="center">' . $txt['smfg_no_vehicle_results'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }

    // Display as - Latest Blog
    if ($context['browse_type'] == "latestblog") {
        echo '
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['vid'])) {
            echo '
                <tr>
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byYear'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byMake'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byModel'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byOwner'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byTitle'] . '
                    </span></h4></div></td> 
                    <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                    ' . $context['sort']['byPosted'] . '
                    </span></h4></div></td> 
                </tr>';
            while (isset($context['browse_tables'][$count]['vid'])) {

                echo '
                    <tr class="tableRow">
                    <td align="center">' . $context['browse_tables'][$count]['made_year'] . '</td>
                    <td align="center">' . $context['browse_tables'][$count]['make'] . '</td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '">' . garage_title_clean($context['browse_tables'][$count]['model']) . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '#blog">' . $context['browse_tables'][$count]['blog_title'] . '</a></td>
                    <td align="center">' . date($context['date_format'],
                        $context['browse_tables'][$count]['posted_date']) . '</td>
                    </tr>';
                $count++;

            }
        } else {
            echo '
                <tr>
                <td colspan="9" align="center">' . $txt['smfg_no_blogs_in_garage'] . '</td>
                </tr>';
        }
        echo '
            </table>';
    }

    // Display as - Latest Video
    if ($context['browse_type'] == "latestvideo") {
        echo '
        <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        $count = 0;
        if (isset($context['browse_tables'][$count]['id'])) {
            echo '
            <tr>
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                &nbsp;
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $context['sort']['byYear'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $context['sort']['byMake'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $context['sort']['byModel'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $context['sort']['byTitle'] . '
                </span></h4></div></td> 
                <td align="center" nowrap="nowrap"><div class="title_bar"><h4 class="titlebg"><span class="ie6_header floatleft">
                ' . $context['sort']['byTitle'] . '
                </span></h4></div></td> 
            </tr>';
            while (isset($context['browse_tables'][$count]['id'])) {


                switch ($context['browse_tables'][$count]['video_type']) {
                    case 'vehicle':
                        $uri = 'sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'];
                        break;
                    case 'mod':
                        $uri = 'sa=view_modification;VID=' . $context['browse_tables'][$count]['vid'] . ';MID=' . $context['browse_tables'][$count]['tid'];
                        break;
                    case 'dynorun':
                        $uri = 'sa=view_dynorun;VID=' . $context['browse_tables'][$count]['vid'] . ';DID=' . $context['browse_tables'][$count]['tid'];
                        break;
                    case 'qmile':
                        $uri = 'sa=view_quartermile;VID=' . $context['browse_tables'][$count]['vid'] . ';QID=' . $context['browse_tables'][$count]['tid'];
                        break;
                    case 'lap':
                        $uri = 'sa=view_laptime;VID=' . $context['browse_tables'][$count]['vid'] . ';LID=' . $context['browse_tables'][$count]['tid'];
                        break;
                }

                echo '
                <tr class="tableRow">
                    <td align="center" style="width: 25px;  white-space: nowrap;">' . $context['browse_tables'][$count]['video'] . '</td>
                    <td align="center">' . $context['browse_tables'][$count]['made_year'] . '</td>
                    <td align="center">' . $context['browse_tables'][$count]['make'] . '</td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['browse_tables'][$count]['vid'] . '">' . garage_title_clean($context['browse_tables'][$count]['model']) . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;sa=view_garage;UID=' . $context['browse_tables'][$count]['user_id'] . '">' . $context['browse_tables'][$count]['memberName'] . '</a></td>
                    <td align="center"><a href="' . $scripturl . '?action=garage;' . $uri . '#videos">' . $context['browse_tables'][$count]['video_title'] . '</a></td>
                </tr>';
                $count++;
            }
        } else {
            echo '
                <tr>
                    <td colspan="9" align="center">' . $txt['smfg_no_videos_in_garage'] . '</td>
                </tr>';
        }
        echo '
        </table>';
    }

    echo '  
    </div>
</div>
<span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

function template_copyright()
{
    global $context, $settings, $options, $txt, $scripturl;


    // Display links to navigate garage.
    if (!empty($smfgSettings['enable_index_menu'])) {
        build_submenu($context['sort_links']);
    }

    echo '
    <div id="boardindex_table">
        <table class="table_list" cellspacing="0" cellpadding="0">
            <tbody class="header">
                <tr>
                    <td>
                        <div class="cat_bar">
                            <h3 class="catbg">';

    echo $txt['smfg_copyright'];

    echo '        
                            </h3>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>';

    echo '    
    <span class="clear upperframe"><span></span></span>
    <div class="roundframe"><div class="innerframe">';


    echo '
<form action="' . $scripturl . '?action=garage;sa=browse" method="post" style="padding:0; margin:0;">
<table width="100%" border="0" cellspacing="1" cellpadding="4" align="center">
<tr>
<td>
    <table border="0" cellpadding="3" cellspacing="1" width="100%">';


    echo '
    <tr>
    <td align="center"><pre>' . $txt['smfg_gnu_license'] . '</pre></td>
    </tr>';

    echo '
    </table>
</td>
</tr>
</table>
</form>';

    echo '  
    </div>
</div>
<span class="lowerframe"><span></span></span>';

    echo smfg_footer();

}

// This is a blank template for all forwarding actions
function template_blank()
{
    // Nothing to see here folks
}
