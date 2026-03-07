<?php
/**********************************************************************************
 * GarageManagement.template.php                                                   *
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

// Form for managing businesses
function template_manage_business()
{
    global $context, $settings, $options, $scripturl, $txt;

    echo '
    <hr size="1" width="100%" class="hrcolor" style="margin-top: 0px;" />';

    echo '
    <div id="garage_management_submenus">
        <ul class="dropmenu">
            <li>
                <a class="active firstlevel" id="tab000" href="' . $scripturl . '?action=admin;area=garagemanagement#garages" onclick="change_tab(\'000\');"><span class="firstlevel">' . $txt['smfg_garages'] . '</span></a>
            </li>
            <li>
                <a class="firstlevel" id="tab001" href="' . $scripturl . '?action=admin;area=garagemanagement#shops" onclick="change_tab(\'001\');"><span class="firstlevel">' . $txt['smfg_shops'] . '</span></a>
            </li>
            <li>
                <a class="firstlevel" id="tab002" href="' . $scripturl . '?action=admin;area=garagemanagement#insurance" onclick="change_tab(\'002\');"><span class="firstlevel">' . $txt['smfg_insurance'] . '</span></a>
            </li>
            <li>
                <a class="firstlevel" id="tab003" href="' . $scripturl . '?action=admin;area=garagemanagement#dynocenters" onclick="change_tab(\'003\');"><span class="firstlevel">' . $txt['smfg_dynocenters'] . '</span></a>
            </li>
            <li>
                <a class="firstlevel" id="tab004" href="' . $scripturl . '?action=admin;area=garagemanagement#manufacturers" onclick="change_tab(\'004\');"><span class="firstlevel">' . $txt['smfg_manufacturers'] . '</span></a>
            </li>
        </ul>
    </div>';

    echo '
    <br /><br />';

    echo '
    <div class="garage_panel" id="options000" style="display: none;">';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_garages'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    if (isset($context['garages'][$count]['id'])) {
        while (isset($context['garages'][$count]['id'])) {
            echo '
                            <tr class="tableRow">
                                <td align="left" valign="middle" nowrap="nowrap">' . $context['garages'][$count]['title'] . '</td>
                                <td align="right" width="75%" valign="middle">';

            if ($context['garages'][$count]['pending'] == '1') {
                echo '
                                    <img src="', $settings['default_images_url'], '/icon_garage_disapprove_disabled.gif" alt="' . $txt['smfg_disapprove'] . '" title="' . $txt['smfg_disapprove'] . '" />
                                    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_approve;BID=' . $context['garages'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_garage_' . $context['garages'][$count]['id'] . '" id="approve_garage_' . $context['garages'][$count]['id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#garages" />
                                        <a href="#" onClick="document.approve_garage_' . $context['garages'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                    </form>';
            } else {
                echo '
                                    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_disable;BID=' . $context['garages'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="disable_garage_' . $context['garages'][$count]['id'] . '" id="disable_garage_' . $context['garages'][$count]['id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#garages" />
                                        <a href="#" onClick="document.disable_garage_' . $context['garages'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_disapprove.gif" alt="' . $txt['smfg_disable'] . '" title="' . $txt['smfg_disable'] . '" /></a>
                                    </form>
                                    <img src="', $settings['default_images_url'], '/icon_garage_approve_disabled.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" />';
            }

            echo '
                                <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_edit;BID=' . $context['garages'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=garage_delete;BID=' . $context['garages'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_garage_' . $context['garages'][$count]['id'] . '" id="remove_garage_' . $context['garages'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#garages" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_garage'] . '\')) { document.remove_garage_' . $context['garages'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="', $settings['default_images_url'], '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                                </td>
                            </tr>';
            $count++;
        }
    } else {
        echo '
                    <tr>
                        <td align="center" valign="middle">' . $txt['smfg_no_garages'] . '</td>
                    </tr>';
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_add;type=garage" id="add_garage" name="add_garage" method="post" style="padding:0; margin:0;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="garage" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addgarage" type="submit" value="' . $txt['smfg_create_business'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("add_garage");
                 var frm = document.forms["add_garage"];
                 
                 frmvalidator.addValidation("garage","req","' . $txt['smfg_val_enter_business'] . '");
                </script>
                </td>
            </tr>
        </table>
        </form>
        </div>';

    echo '
        <div class="garage_panel" id="options001" style="display: none;">';

    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_shops'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td class="windowbg">
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    if (isset($context['shops'][$count]['id'])) {
        while (isset($context['shops'][$count]['id'])) {
            echo '
                            <tr class="tableRow">
                                <td align="left" valign="middle" nowrap="nowrap">' . $context['shops'][$count]['title'] . '</td>
                                <td align="right" width="75%" valign="middle">';

            if ($context['shops'][$count]['pending'] == '1') {
                echo '
                                    <img src="', $settings['default_images_url'], '/icon_garage_disapprove_disabled.gif" alt="' . $txt['smfg_disapprove'] . '" title="' . $txt['smfg_disapprove'] . '" />
                                    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_approve;BID=' . $context['shops'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_shop_' . $context['shops'][$count]['id'] . '" id="approve_shop_' . $context['shops'][$count]['id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#shops" />
                                        <a href="#" onClick="document.approve_shop_' . $context['shops'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                    </form>';
            } else {
                echo '
                                    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_disable;BID=' . $context['shops'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="disable_shop_' . $context['shops'][$count]['id'] . '" id="disable_shop_' . $context['shops'][$count]['id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#shops" />
                                        <a href="#" onClick="document.disable_shop_' . $context['shops'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_disapprove.gif" alt="' . $txt['smfg_disable'] . '" title="' . $txt['smfg_disable'] . '" /></a>
                                    </form>
                                    <img src="', $settings['default_images_url'], '/icon_garage_approve_disabled.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" />';
            }

            echo '
                                <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_edit;BID=' . $context['shops'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=shop_delete;BID=' . $context['shops'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_shop_' . $context['shops'][$count]['id'] . '" id="remove_shop_' . $context['shops'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#shops" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_shop'] . '\')) { document.remove_shop_' . $context['shops'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="', $settings['default_images_url'], '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                                </td>
                            </tr>';
            $count++;
        }
    } else {
        echo '
                    <tr>
                        <td align="center" valign="middle">' . $txt['smfg_no_shops'] . '</td>
                    </tr>';
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_add;type=retail" id="add_shop" name="add_shop" method="post" style="padding:0; margin:0;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="shop" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addshop" type="submit" value="' . $txt['smfg_create_business'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("add_shop");
                 var frm = document.forms["add_shop"];
                 
                 frmvalidator.addValidation("shop","req","' . $txt['smfg_val_enter_business'] . '");
                </script>
                </td>
            </tr>
        </table>
        </form>
        </div>';

    echo '
        <div class="garage_panel" id="options002" style="display: none;">';

    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_insurance'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    if (isset($context['insurance'][$count]['id'])) {
        while (isset($context['insurance'][$count]['id'])) {
            echo '
                            <tr class="tableRow">
                                <td align="left" valign="middle" nowrap="nowrap">' . $context['insurance'][$count]['title'] . '</td>
                                <td align="right" width="75%" valign="middle">';

            if ($context['insurance'][$count]['pending'] == '1') {
                echo '
                                    <img src="', $settings['default_images_url'], '/icon_garage_disapprove_disabled.gif" alt="' . $txt['smfg_disapprove'] . '" title="' . $txt['smfg_disapprove'] . '" />
                                    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_approve;BID=' . $context['insurance'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_insurance_' . $context['insurance'][$count]['id'] . '" id="approve_insurance_' . $context['insurance'][$count]['id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#insurance" />
                                        <a href="#" onClick="document.approve_insurance_' . $context['insurance'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                    </form>';
            } else {
                echo '
                                    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_disable;BID=' . $context['insurance'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="disable_insurance_' . $context['insurance'][$count]['id'] . '" id="disable_insurance_' . $context['insurance'][$count]['id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#insurance" />
                                        <a href="#" onClick="document.disable_insurance_' . $context['insurance'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_disapprove.gif" alt="' . $txt['smfg_disable'] . '" title="' . $txt['smfg_disable'] . '" /></a>
                                    </form>
                                    <img src="', $settings['default_images_url'], '/icon_garage_approve_disabled.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" />';
            }

            echo '
                                <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_edit;BID=' . $context['insurance'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=insurance_delete;BID=' . $context['insurance'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_insurance_' . $context['insurance'][$count]['id'] . '" id="remove_insurance_' . $context['insurance'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#insurance" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_insurance'] . '\')) { document.remove_insurance_' . $context['insurance'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="', $settings['default_images_url'], '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                                </td>
                            </tr>';
            $count++;
        }
    } else {
        echo '
                    <tr>
                        <td align="center" valign="middle">' . $txt['smfg_no_insurance'] . '</td>
                    </tr>';
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_add;type=insurance" id="add_insurance" name="add_insurance" method="post" style="padding:0; margin:0;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="insurance" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addinsurance" type="submit" value="' . $txt['smfg_create_business'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("add_insurance");
                 var frm = document.forms["add_insurance"];
                 
                 frmvalidator.addValidation("insurance","req","' . $txt['smfg_val_enter_business'] . '");
                </script>
                </td>
            </tr>
        </table>
        </form>
        </div>';

    echo '
        <div class="garage_panel" id="options003" style="display: none;">';

    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_dynocenters'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    if (isset($context['dynocenters'][$count]['id'])) {
        while (isset($context['dynocenters'][$count]['id'])) {
            echo '
                            <tr class="tableRow">
                                <td align="left" valign="middle" nowrap="nowrap">' . $context['dynocenters'][$count]['title'] . '</td>
                                <td align="right" width="75%" valign="middle">';

            if ($context['dynocenters'][$count]['pending'] == '1') {
                echo '
                                    <img src="', $settings['default_images_url'], '/icon_garage_disapprove_disabled.gif" alt="' . $txt['smfg_disapprove'] . '" title="' . $txt['smfg_disapprove'] . '" />
                                    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_approve;BID=' . $context['dynocenters'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_dynocenter_' . $context['dynocenters'][$count]['id'] . '" id="approve_dynocenter_' . $context['dynocenters'][$count]['id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#dynocenters" />
                                        <a href="#" onClick="document.approve_dynocenter_' . $context['dynocenters'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                    </form>';
            } else {
                echo '
                                    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_disable;BID=' . $context['dynocenters'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="disable_dynocenter_' . $context['dynocenters'][$count]['id'] . '" id="disable_dynocenter_' . $context['dynocenters'][$count]['id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#dynocenters" />
                                        <a href="#" onClick="document.disable_dynocenter_' . $context['dynocenters'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_disapprove.gif" alt="' . $txt['smfg_disable'] . '" title="' . $txt['smfg_disable'] . '" /></a>
                                    </form>
                                    <img src="', $settings['default_images_url'], '/icon_garage_approve_disabled.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" />';
            }

            echo '
                                <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_edit;BID=' . $context['dynocenters'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=dynocenter_delete;BID=' . $context['dynocenters'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_dynocenter_' . $context['dynocenters'][$count]['id'] . '" id="remove_dynocenter_' . $context['dynocenters'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#dynocenters" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_dynocenter'] . '\')) { document.remove_dynocenter_' . $context['dynocenters'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="', $settings['default_images_url'], '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                                </td>
                            </tr>';
            $count++;
        }
    } else {
        echo '
                    <tr>
                        <td align="center" valign="middle">' . $txt['smfg_no_dynocenters'] . '</td>
                    </tr>';
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_add;type=dynocenter" id="add_dynocenter" name="add_dynocenter" method="post" style="padding:0; margin:0;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="dynocenter" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="adddynocenter" type="submit" value="' . $txt['smfg_create_business'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("add_dynocenter");
                 var frm = document.forms["add_dynocenter"];
                 
                 frmvalidator.addValidation("dynocenter","req","' . $txt['smfg_val_enter_business'] . '");
                </script>
                </td>
            </tr>
        </table>
        </form>
        </div>';

    echo '
        <div class="garage_panel" id="options004" style="display: none;">';

    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_manufacturers'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td class="windowbg">
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    if (isset($context['manufacturers'][$count]['id'])) {
        while (isset($context['manufacturers'][$count]['id'])) {
            echo '
                            <tr class="tableRow">
                                <td align="left" valign="middle" nowrap="nowrap">' . $context['manufacturers'][$count]['title'] . '</td>
                                <td align="right" width="75%" valign="middle">';

            if ($context['manufacturers'][$count]['pending'] == '1') {
                echo '
                                    <img src="', $settings['default_images_url'], '/icon_garage_disapprove_disabled.gif" alt="' . $txt['smfg_disapprove'] . '" title="' . $txt['smfg_disapprove'] . '" />
                                    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_approve;BID=' . $context['manufacturers'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_manufacturer_' . $context['manufacturers'][$count]['id'] . '" id="approve_manufacturer_' . $context['manufacturers'][$count]['id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#manufacturers" />
                                        <a href="#" onClick="document.approve_manufacturer_' . $context['manufacturers'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                    </form>';
            } else {
                echo '
                                    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_disable;BID=' . $context['manufacturers'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="disable_manufacturer_' . $context['manufacturers'][$count]['id'] . '" id="disable_manufacturer_' . $context['manufacturers'][$count]['id'] . '" style="display: inline;">
                                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#manufacturers" />
                                        <a href="#" onClick="document.disable_manufacturer_' . $context['manufacturers'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_disapprove.gif" alt="' . $txt['smfg_disable'] . '" title="' . $txt['smfg_disable'] . '" /></a>
                                    </form>
                                    <img src="', $settings['default_images_url'], '/icon_garage_approve_disabled.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" />';
            }

            echo '
                                <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_edit;BID=' . $context['manufacturers'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=manufacturer_delete;BID=' . $context['manufacturers'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_manufacturer_' . $context['manufacturers'][$count]['id'] . '" id="remove_manufacturer_' . $context['manufacturers'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement#manufacturers" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_manufacturer'] . '\')) { document.remove_manufacturer_' . $context['manufacturers'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="', $settings['default_images_url'], '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                                </td>
                            </tr>';
            $count++;
        }
    } else {
        echo '
                    <tr>
                        <td align="center" valign="middle">' . $txt['smfg_no_manufacturers'] . '</td>
                    </tr>';
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_add;type=product" id="add_manufacturer" name="add_manufacturer" method="post" style="padding:0; margin:0;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="manufacturer" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addmanufacturer" type="submit" value="' . $txt['smfg_create_business'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("add_manufacturer");
                 var frm = document.forms["add_manufacturer"];
                 
                 frmvalidator.addValidation("manufacturer","req","' . $txt['smfg_val_enter_business'] . '");
                </script>
                </td>
            </tr>
        </table>
        </form>
        </div>
            
    <script type="text/javascript">
<!--
    var lowest_tab = \'000\';
    var active_id = \'000\';
    if (document.location.hash == "")
    {
        change_tab(lowest_tab);
    }
    else if (document.location.hash == "#garages")
    {
        change_tab(\'000\');
    }
    else if (document.location.hash == "#shops")
    {
        change_tab(\'001\');
    }
    else if (document.location.hash == "#insurance")
    {
        change_tab(\'002\');
    }
    else if (document.location.hash == "#dynocenters")
    {
        change_tab(\'003\');
    }
    else if (document.location.hash == "#manufacturers")
    {
        change_tab(\'004\');
    }

//-->

</script>';
}

// Form for adding businesses
function template_add_business()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_create_business'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_insert" id="add_business" name="add_business" method="post" accept-charset="ISO-8859-1" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_business_name'] . ':</td>
                <td width="50%">
                    <input class="medium" type="text" id="title" name="title" value="' . $context['business_name'] . '" maxlength="60" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_address'] . ':</td>
                <td width="50%">
                    <textarea name="address" cols="60" rows="5"></textarea>
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_telephone_no'] . ':</td>
                <td width="50%">
                    <input class="medium" name="telephone" type="text" size="35" value="" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_fax'] . ':</td>
                <td width="50%">
                    <input class="medium" name="fax" type="text" size="35" value="" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_website'] . ':</td>
                <td width="50%">
                    <input class="medium" name="website" type="text" size="35" value="" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_email'] . ':</td>
                <td width="50%">
                    <input class="medium" name="email" type="text" size="35" value="" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_opening_hours'] . ':</td>
                <td width="50%">
                    <textarea name="opening_hours" cols="60" rows="3"></textarea>
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_type'] . ':</td>
                <td width="50%">
                    ' . $txt['smfg_garage'] . ':&nbsp;<input type="checkbox" name="type[0]" id="type0" value="garage"' . $context['check_garage'] . ' /><br />
                    ' . $txt['smfg_shop'] . ':&nbsp;<input type="checkbox" name="type[1]" id="type1" value="retail"' . $context['check_retail'] . ' /><br />
                    ' . $txt['smfg_insurance'] . ':&nbsp;<input type="checkbox" name="type[2]" id="type2" value="insurance"' . $context['check_insurance'] . ' /><br />
                    ' . $txt['smfg_dynocenter'] . ':&nbsp;<input type="checkbox" name="type[3]" id="type3" value="dynocenter"' . $context['check_dynocenter'] . ' /><br />
                    ' . $txt['smfg_manufacturer'] . ':&nbsp;<input type="checkbox" name="type[4]" id="type4" value="product"' . $context['check_product'] . ' />
                </td>
            </tr>
        </table>                
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
        </div>
    </form>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <script type="text/javascript">
    function DoCustomValidation()
                {
                  var frm = document.forms["add_business"];
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
            
    var frmvalidator = new Validator("add_business");
    frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_business'] . '");
    frmvalidator.addValidation("email","email","' . $txt['smfg_val_enter_valid_email'] . '");
    frmvalidator.setAddnlValidationFunction("DoCustomValidation");
    </script>';
}

// Form for editing businesses
function template_edit_business()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_business'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_update" id="edit_business" name="edit_business" method="post" accept-charset="ISO-8859-1" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_business_name'] . ':</td>
                <td width="50%">
                    <input class="medium" type="text" id="title" name="title" value="' . $context['business']['title'] . '" maxlength="60" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_address'] . ':</td>
                <td width="50%">
                    <textarea name="address" cols="60" rows="5">' . $context['business']['address'] . '</textarea>
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_telephone_no'] . ':</td>
                <td width="50%">
                    <input class="medium" name="telephone" type="text" size="35" value="' . $context['business']['telephone'] . '" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_fax'] . ':</td>
                <td width="50%">
                    <input class="medium" name="fax" type="text" size="35" value="' . $context['business']['fax'] . '" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_website'] . ':</td>
                <td width="50%">
                    <input class="medium" name="website" type="text" size="35" value="' . $context['business']['website'] . '" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_email'] . ':</td>
                <td width="50%">
                    <input class="medium" name="email" type="text" size="35" value="' . $context['business']['email'] . '" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_opening_hours'] . ':</td>
                <td width="50%">
                    <textarea name="opening_hours" cols="60" rows="3">' . $context['business']['opening_hours'] . '</textarea>
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_type'] . ':</td>
                <td width="50%">
                    ' . $txt['smfg_garage'] . ':&nbsp;<input type="checkbox" name="type[0]" id="type" value="garage"' . $context['business']['check']['garage'] . '/><br />
                    ' . $txt['smfg_shop'] . ':&nbsp;<input type="checkbox" name="type[1]" id="type" value="retail"' . $context['business']['check']['retail'] . '/><br />
                    ' . $txt['smfg_insurance'] . ':&nbsp;<input type="checkbox" name="type[2]" id="type" value="insurance"' . $context['business']['check']['insurance'] . '/><br />
                    ' . $txt['smfg_dynocenter'] . ':&nbsp;<input type="checkbox" name="type[3]" id="type" value="dynocenter"' . $context['business']['check']['dynocenter'] . '/><br />
                    ' . $txt['smfg_manufacturer'] . ':&nbsp;<input type="checkbox" name="type[4]" id="type" value="product"' . $context['business']['check']['product'] . '/>
                </td>
            </tr>
        </table>    
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            <input type="hidden" name="bid" value="' . $context['business']['bid'] . '" />
        </div>
    </form>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <script type="text/javascript">
    function DoCustomValidation()
                {
                  var frm = document.forms["edit_business"];
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
            
    var frmvalidator = new Validator("edit_business");
    frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_business'] . '");
    frmvalidator.addValidation("email","email","' . $txt['smfg_val_enter_valid_email'] . '");
    frmvalidator.setAddnlValidationFunction("DoCustomValidation");
    </script>';
}

// Form for managing modification categoires
function template_manage_categories()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_categories'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
    <table border="0" cellspacing="0" cellpadding="4" width="100%">
        <tr>
            <td>
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    while (isset($context['categories'][$count]['id'])) {
        echo '
                    <tr class="tableRow">
                        <td align="left" valign="middle" nowrap="nowrap">' . $context['categories'][$count]['title'] . '</td>
                        <td align="right" width="75%" valign="middle">';

        if ($context['categories'][$count]['field_order'] == 1) {
            echo '
                            <img src="', $settings['default_images_url'], '/icon_up_disabled.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" />
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=category_move;direction=down;CID=' . $context['categories'][$count]['id'] . ';order=' . $context['categories'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_cat_down_' . $context['categories'][$count]['id'] . '" id="move_cat_down_' . $context['categories'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=categories" />
                                <a href="#" onClick="document.move_cat_down_' . $context['categories'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                            </form>';
        } else {
            if ($context['categories'][$count]['field_order'] == $context['categories']['total']) {
                echo '
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=category_move;direction=up;CID=' . $context['categories'][$count]['id'] . ';order=' . $context['categories'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_cat_up_' . $context['categories'][$count]['id'] . '" id="move_cat_up_' . $context['categories'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=categories" />
                                <a href="#" onClick="document.move_cat_up_' . $context['categories'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                            </form>
                            <img src="', $settings['default_images_url'], '/icon_down_disabled.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" />';
            } else {
                echo '
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=category_move;direction=up;CID=' . $context['categories'][$count]['id'] . ';order=' . $context['categories'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_cat_up_' . $context['categories'][$count]['id'] . '" id="move_cat_up_' . $context['categories'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=categories" />
                                <a href="#" onClick="document.move_cat_up_' . $context['categories'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                            </form>
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=category_move;direction=down;CID=' . $context['categories'][$count]['id'] . ';order=' . $context['categories'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_cat_down_' . $context['categories'][$count]['id'] . '" id="move_cat_down_' . $context['categories'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=categories" />
                                <a href="#" onClick="document.move_cat_down_' . $context['categories'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                            </form>';
            }
        }

        echo '
                        <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=category_edit;CID=' . $context['categories'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=category_delete;CID=' . $context['categories'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_category_' . $context['categories'][$count]['id'] . '" id="remove_category_' . $context['categories'][$count]['id'] . '" style="display: inline;">
                            <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=categories" />
                            <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_category'] . '\')) { document.remove_category_' . $context['categories'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="', $settings['default_images_url'], '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                        </form>';

        echo '
                        </td>
                    </tr>';
        $count++;
    }
    echo '
            </table>
            </td>
        </tr>
    </table>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=category_add" id="category_add" name="category_add" method="post" style="padding:0; margin:0;">
    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=categories" />
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td align="right">
            <input type="text" name="category" value="" maxlength="255" />
            <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
            <input name="addcategory" type="submit" value="' . $txt['smfg_create_category'] . '" />
            <script type="text/javascript">
             var frmvalidator = new Validator("category_add");
             var frm = document.forms["category_add"];
             
             frmvalidator.addValidation("category","req","' . $txt['smfg_val_enter_category'] . '");
            </script>
            </td>
        </tr>
    </table>
    </form>';
}

// Form for editting modification categoires
function template_edit_categories()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_category'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=category_update" id="edit_category" name="edit_category" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=categories" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_category'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $context['categories']['title'] . '" maxlength="255" />
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            <input type="hidden" name="cid" value="' . $context['categories']['cid'] . '" />
        </div>
        </form>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <script type="text/javascript">
    var frmvalidator = new Validator("edit_category");
    
    frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_category'] . '");
    </script>';
}

// Form for managing makes and models
function template_manage_makes_models()
{
    global $context, $settings, $options, $scripturl, $txt;

    echo '
    <script type="text/javascript">
    var active_model_id = \'000\';
    </script>';

    echo '
    <hr size="1" width="100%" class="hrcolor" style="margin-top: 0px;" />';

    echo '
    <div id="garage_management_submenus">
        <ul class="dropmenu">
            <li>
                <a class="active firstlevel" id="tab000" href="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels#makes" onclick="change_tab(\'000\');"><span class="firstlevel">' . $txt['smfg_makes'] . '</span></a>
            </li>
            <li>
                <a class="firstlevel" id="tab001" href="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels#models" onclick="change_tab(\'001\');"><span class="firstlevel">' . $txt['smfg_models'] . '</span></a>
            </li>
        </ul>
    </div>';

    echo '
    <br /><br />';

    // Makes
    echo '
    <div class="garage_panel" id="options000" style="display: none;">';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_makes'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_create_make'] . ':</td>
                <td width="70%">
                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=make_add" id="make_add" name="make_add" method="post" style="padding:0; margin:0;">
                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels" />
                <input type="text" name="make" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addmake" type="submit" value="' . $txt['smfg_create_make'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("make_add");
                 var frm = document.forms["make_add"];
                 
                 frmvalidator.addValidation("make","req","' . $txt['smfg_val_enter_make'] . '");
                </script>
                </form>
                </td>
            </tr>
            <tr>
                    <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
            </tr>
            <tr>
                <td colspan="3">
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    while (isset($context['makes'][$count]['id'])) {
        echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">' . $context['makes'][$count]['title'] . '</td>
                            <td align="right" width="75%" valign="middle">';

        if ($context['makes'][$count]['pending'] == '1') {
            echo '
                                <img src="', $settings['default_images_url'], '/icon_garage_disapprove_disabled.gif" alt="' . $txt['smfg_disapprove'] . '" title="' . $txt['smfg_disapprove'] . '" />
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=make_approve;mkid=' . $context['makes'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_make_' . $context['makes'][$count]['id'] . '" id="approve_make_' . $context['makes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels" />
                                    <a href="#" onClick="document.approve_make_' . $context['makes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';
        } else {
            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=make_disable;mkid=' . $context['makes'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="disable_make_' . $context['makes'][$count]['id'] . '" id="disable_make_' . $context['makes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels" />
                                    <a href="#" onClick="document.disable_make_' . $context['makes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_garage_disapprove.gif" alt="' . $txt['smfg_disable'] . '" title="' . $txt['smfg_disable'] . '" /></a>
                                </form>
                                <img src="', $settings['default_images_url'], '/icon_garage_approve_disabled.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" />';
        }

        echo '
                            <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=make_edit;mkid=' . $context['makes'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=make_delete;mkid=' . $context['makes'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_make_' . $context['makes'][$count]['id'] . '" id="remove_make_' . $context['makes'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels" />
                                <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_make'] . '\')) { document.remove_make_' . $context['makes'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="', $settings['default_images_url'], '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                            </form>';

        echo '
                            </td>
                        </tr>';
        $count++;
    }
    echo '
                </table>
                </td>
            </tr>';
    echo '
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=make_add" id="make_add2" name="make_add2" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels" />
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="make" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addmake" type="submit" value="' . $txt['smfg_create_make'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("make_add2");
                 var frm = document.forms["make_add2"];
                 
                 frmvalidator.addValidation("make","req","' . $txt['smfg_val_enter_make'] . '");
                </script>
                </td>
            </tr>
        </table>
        </form>
        </div>';

    // Models
    echo '
        <div class="garage_panel" id="options001" style="display: none;">';

    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_models'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
                <table border="0" cellspacing="0" cellpadding="4" width="100%">
                    <tr>
                        <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=create_model" onclick="return reqWin(this.href);" class="help"><img src="', $settings['actual_images_url'], '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                        <td valign="top">' . $txt['smfg_create_modify_model'] . ':</td>
                        <td width="70%">
                        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=model_add" id="model_add" name="model_add" method="post" style="padding:0; margin:0;">
                        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels#models" />
                        <select name="make_id" id="make_id" onChange="change_select(this.value, \'makes\')">
                        <option value="000">------</option>';
    // List Make Selections
    echo make_select();
    echo '
                        </select>
                        <input type="text" name="model" value="" maxlength="255" />
                        <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                        <input name="addmodel" type="submit" value="' . $txt['smfg_create_model'] . '" />
                        <script type="text/javascript">
                         var frmvalidator = new Validator("model_add");
                         var frm = document.forms["model_add"];
                         
                         frmvalidator.addValidation("make_id","dontselect=0","' . $txt['smfg_val_select_make'] . '");
                         frmvalidator.addValidation("model","req","' . $txt['smfg_val_enter_model'] . '");
                        </script>
                        </form>
                        </td>
                    </tr>
                    <tr>
                            <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                    </tr>';

    echo '
                    <tr>
                            <td colspan="3">
                            <div class="models_panel" id="makes000" style="display: block;">' . $txt['smfg_select_make_dropdown'] . '</div>
                            ';
    // List ALL the makes, each with their own div for some AJAX action!
    echo model_divs();
    echo '
                            </td>
                    </tr>';
    echo '
                </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        </div>';

    echo '
        <script type="text/javascript">
        <!--
            var lowest_tab = \'000\';
            var active_id = \'000\';
            if (document.location.hash == "")
            {
                change_tab(lowest_tab);
            }
            else if (document.location.hash == "#makes")
            {
                change_tab(\'000\');
            }
            else if (document.location.hash == "#models")
            {
                change_tab(\'001\');
            }

        //-->

        </script>';
}

// Form for editting makes
function template_edit_make()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_make'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=make_update" id="edit_make" name="edit_make" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_make'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $context['makes']['title'] . '" maxlength="255" />
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />            
            <input type="hidden" name="mkid" value="' . $context['makes']['mkid'] . '" />
        </div>
        </form>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <script type="text/javascript">
    var frmvalidator = new Validator("edit_make");
    
    frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_make'] . '");
    frmvalidator.addValidation("title","regexp=^[. A-Za-z0-9]{1,30}$","' . $txt['smfg_val_enter_valid_make'] . '");
    </script>';
}

// Form for editting models
function template_edit_model()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_model'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=model_update" id="edit_model" name="edit_model" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=makesmodels#models" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_make'] . ':</td>
                <td width="50%">
                <select name="make_id" id="make_id">
                <option value="">------</option>';
    // List Make Selections
    echo make_select($context['models']['mkid']);
    echo '
                </select>
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_model'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $context['models']['title'] . '" maxlength="255" />
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            <input type="hidden" name="mdid" value="' . $context['models']['mdid'] . '" />
        </div>
        </form>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <script type="text/javascript">
    var frmvalidator = new Validator("edit_model");
    frmvalidator.addValidation("make_id","req","' . $txt['smfg_val_select_make'] . '");
    frmvalidator.addValidation("make_id","dontselect=0","' . $txt['smfg_val_select_make'] . '");
    frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_model'] . '");
    frmvalidator.addValidation("title","regexp=^[. A-Za-z0-9]{1,30}$","' . $txt['smfg_val_enter_valid_model'] . '");
    </script>';
}

// Form for managing makes and models
function template_manage_products()
{
    global $context, $settings, $options, $scripturl, $txt;

    echo '
    <script type="text/javascript">
    var active_model_id = \'000\';
    </script>';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_products'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    // Products
    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=create_product" onclick="return reqWin(this.href);" class="help"><img src="', $settings['actual_images_url'], '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                <td valign="top">' . $txt['smfg_create_modify_product'] . ':</td>
                <td width="70%">
                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=product_add" id="product_add" name="product_add" method="post" style="padding:0; margin:0;">
                <select name="manufacturer_id" id="manufacturer_id" onChange="change_select(this.value, \'man\')">
                <option value="000">------</option>';
    // List Manufacturer Selections
    echo manufacturer_select();
    echo '
                </select>
                <input type="text" name="product" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addproduct" type="submit" value="' . $txt['smfg_create_product'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("product_add");
                 var frm = document.forms["product_add"];
                 
                 frmvalidator.addValidation("manufacturer_id","dontselect=0","' . $txt['smfg_val_select_manufacturer'] . '");
                 frmvalidator.addValidation("product","req","' . $txt['smfg_val_enter_product'] . '");
                </script>
                </form>
                </td>
            </tr>
            <tr>
                    <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
            </tr>';

    echo '
            <tr>
                    <td colspan="3">
                    <div class="products_panel" id="man000" style="display: block;">' . $txt['smfg_select_manufacturer_dropdown'] . '</div>
                    ';
    // List ALL the products, each with their own div for some AJAX action!
    echo product_divs();
    echo '
                    </td>
            </tr>';

    echo '
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';
}

// Form for editting products
function template_edit_product()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_product'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=product_update" id="edit_product" name="edit_product" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=products" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_category'] . ':</td>
                <td width="50%">
                <select name="cid" id="cid">
                <option value="">------</option>';
    // List Cat Selections
    echo cat_select($context['products']['cid']);
    echo '
                </select>
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_manufacturer'] . ':</td>
                <td width="50%">
                <select name="bid" id="bid">
                <option value="">------</option>';
    // List Manufacturer Selections
    echo manufacturer_select($context['products']['bid']);
    echo '
                </select>
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_product'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $context['products']['title'] . '" maxlength="255" />
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            <input type="hidden" name="pid" value="' . $_GET['pid'] . '" />
        </div>
        </form>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <script type="text/javascript">
        var frmvalidator = new Validator("edit_product");
        
        frmvalidator.addValidation("cid","req","' . $txt['smfg_val_select_category'] . '");
        frmvalidator.addValidation("cid","dontselect=0","' . $txt['smfg_val_select_category'] . '");
        frmvalidator.addValidation("bid","req","' . $txt['smfg_val_select_manufacturer'] . '");
        frmvalidator.addValidation("bid","dontselect=0","' . $txt['smfg_val_select_manufacturer'] . '");
        frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_product'] . '");
        </script>';
}

// Form for addin products
function template_add_product()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_create_product'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=product_insert" id="product_add" name="product_add" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=products" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_product'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $_POST['product'] . '" maxlength="255" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_manufacturer'] . ':</td>
                <td width="50%">
                <select name="bid" id="bid">
                <option value="">------</option>';
    // List Manufacturer Selections
    echo manufacturer_select($_POST['manufacturer_id']);
    echo '
                </select>
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_category'] . ':</td>
                <td width="50%">
                <select name="cid" id="cid">
                <option value="">------</option>';
    // List Cat Selections
    echo cat_select();
    echo '
                </select>
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
        </div>
        </form>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <script type="text/javascript">
        var frmvalidator = new Validator("product_add");
        
        frmvalidator.addValidation("cid","req","' . $txt['smfg_val_select_category'] . '");
        frmvalidator.addValidation("cid","dontselect=0","' . $txt['smfg_val_select_category'] . '");
        frmvalidator.addValidation("bid","req","' . $txt['smfg_val_select_manufacturer'] . '");
        frmvalidator.addValidation("bid","dontselect=0","' . $txt['smfg_val_select_manufacturer'] . '");
        frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_product'] . '");
        </script>';
}

// Form for managing tracks
function template_manage_tracks()
{
    global $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_tracks'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
    <table border="0" cellspacing="0" cellpadding="4" width="100%">
        <tr>
            <td>
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    if (isset($context['tracks'][$count]['id'])) {
        while (isset($context['tracks'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_track;TID=' . $context['tracks'][$count]['id'] . '">' . $context['tracks'][$count]['title'] . '</a></td>
                            <td align="right" width="75%" valign="middle">';

            if ($context['tracks'][$count]['pending'] == '1') {
                echo '
                                <img src="', $settings['default_images_url'], '/icon_garage_disapprove_disabled.gif" alt="' . $txt['smfg_disapprove'] . '" title="' . $txt['smfg_disapprove'] . '" />
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=track_approve;TID=' . $context['tracks'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_track_' . $context['tracks'][$count]['id'] . '" id="approve_track_' . $context['tracks'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                    <a href="#" onClick="document.approve_track_' . $context['tracks'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';
            } else {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=track_disable;TID=' . $context['tracks'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="disable_track_' . $context['tracks'][$count]['id'] . '" id="disable_track_' . $context['tracks'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                    <a href="#" onClick="document.disable_track_' . $context['tracks'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_disapprove.gif" alt="' . $txt['smfg_disable'] . '" title="' . $txt['smfg_disable'] . '" /></a>
                                </form>
                                <img src="', $settings['default_images_url'], '/icon_garage_approve_disabled.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" />';
            }

            echo '
                            <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=track_edit;TID=' . $context['tracks'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=track_delete;TID=' . $context['tracks'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_track_' . $context['tracks'][$count]['id'] . '" id="remove_track_' . $context['tracks'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_track'] . '\')) { document.remove_track_' . $context['tracks'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                            </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
    } else {
        echo '
                <tr>
                    <td align="center" valign="middle">' . $txt['smfg_no_tracks'] . '</td>
                </tr>';
    }
    echo '
            </table>
            </td>
        </tr>
    </table>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=track_add" id="track_add" name="track_add" method="post" style="padding:0; margin:0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td align="right">
            <input type="text" name="track" value="" maxlength="255" />
            <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
            <input name="addtrack" type="submit" value="' . $txt['smfg_create_track'] . '" />
            <script type="text/javascript">
             var frmvalidator = new Validator("track_add");
             var frm = document.forms["track_add"];
                     
             frmvalidator.addValidation("track","req","' . $txt['smfg_val_enter_product'] . '");
            </script>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
    </table>
    </form>';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_track_conditions'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    while (isset($context['conditions'][$count]['id'])) {
        echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">' . $context['conditions'][$count]['title'] . '</td>
                            <td align="right" width="75%" valign="middle">';

        if ($context['conditions'][$count]['field_order'] == 1) {
            echo '
                                <img src="', $settings['default_images_url'], '/icon_up_disabled.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" />
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=tc_move;direction=down;TCID=' . $context['conditions'][$count]['id'] . ';order=' . $context['conditions'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_tc_down_' . $context['conditions'][$count]['id'] . '" id="move_tc_down_' . $context['conditions'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                    <a href="#" onClick="document.move_tc_down_' . $context['conditions'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
        } else {
            if ($context['conditions'][$count]['field_order'] == $context['conditions']['total']) {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=tc_move;direction=up;TCID=' . $context['conditions'][$count]['id'] . ';order=' . $context['conditions'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_tc_up_' . $context['conditions'][$count]['id'] . '" id="move_tc_up_' . $context['conditions'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                    <a href="#" onClick="document.move_tc_up_' . $context['conditions'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <img src="', $settings['default_images_url'], '/icon_down_disabled.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" />';
            } else {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=tc_move;direction=up;TCID=' . $context['conditions'][$count]['id'] . ';order=' . $context['conditions'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_tc_up_' . $context['conditions'][$count]['id'] . '" id="move_tc_up_' . $context['conditions'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                    <a href="#" onClick="document.move_tc_up_' . $context['conditions'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=tc_move;direction=down;TCID=' . $context['conditions'][$count]['id'] . ';order=' . $context['conditions'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_tc_down_' . $context['conditions'][$count]['id'] . '" id="move_tc_down_' . $context['conditions'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                    <a href="#" onClick="document.move_tc_down_' . $context['conditions'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
            }
        }

        echo '
                            <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=tc_edit;TCID=' . $context['conditions'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=tc_delete;TCID=' . $context['conditions'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_tc_' . $context['conditions'][$count]['id'] . '" id="remove_tc_' . $context['conditions'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_track_condition'] . '\')) { document.remove_tc_' . $context['conditions'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                            </form>';

        echo '
                            </td>
                        </tr>';
        $count++;
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=tc_add" id="condition_add" name="condition_add" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="tc" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addcondition" type="submit" value="' . $txt['smfg_create_track_condition'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("condition_add");
                 var frm = document.forms["condition_add"];
                         
                 frmvalidator.addValidation("tc","req","' . $txt['smfg_val_enter_track_condition'] . '");
                </script>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
        </form>';

    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_lap_types'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    while (isset($context['laptypes'][$count]['id'])) {
        echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">' . $context['laptypes'][$count]['title'] . '</td>
                            <td align="right" width="75%" valign="middle">';

        if ($context['laptypes'][$count]['field_order'] == 1) {
            echo '
                                <img src="', $settings['default_images_url'], '/icon_up_disabled.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" />
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=lt_move;direction=down;LTID=' . $context['laptypes'][$count]['id'] . ';order=' . $context['laptypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_lt_down_' . $context['laptypes'][$count]['id'] . '" id="move_lt_down_' . $context['laptypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                    <a href="#" onClick="document.move_lt_down_' . $context['laptypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
        } else {
            if ($context['laptypes'][$count]['field_order'] == $context['laptypes']['total']) {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=lt_move;direction=up;LTID=' . $context['laptypes'][$count]['id'] . ';order=' . $context['laptypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_lt_up_' . $context['laptypes'][$count]['id'] . '" id="move_lt_up_' . $context['laptypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                    <a href="#" onClick="document.move_lt_up_' . $context['laptypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <img src="', $settings['default_images_url'], '/icon_down_disabled.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" />';
            } else {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=lt_move;direction=up;LTID=' . $context['laptypes'][$count]['id'] . ';order=' . $context['laptypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_lt_up_' . $context['laptypes'][$count]['id'] . '" id="move_lt_up_' . $context['laptypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                    <a href="#" onClick="document.move_lt_up_' . $context['laptypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=lt_move;direction=down;LTID=' . $context['laptypes'][$count]['id'] . ';order=' . $context['laptypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_lt_down_' . $context['laptypes'][$count]['id'] . '" id="move_lt_down_' . $context['laptypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                    <a href="#" onClick="document.move_lt_down_' . $context['laptypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
            }
        }

        echo '
                            <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=lt_edit;LTID=' . $context['laptypes'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=lt_delete;LTID=' . $context['laptypes'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_lt_' . $context['laptypes'][$count]['id'] . '" id="remove_lt_' . $context['laptypes'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
                                <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_lap_type'] . '\')) { document.remove_lt_' . $context['laptypes'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                            </form>';

        echo '
                            </td>
                        </tr>';
        $count++;
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=lt_add" id="laptype_add" name="laptype_add" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="lt" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addlaptype" type="submit" value="' . $txt['smfg_create_lap_type'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("laptype_add");
                 var frm = document.forms["laptype_add"];
                         
                 frmvalidator.addValidation("lt","req","' . $txt['smfg_val_enter_lap_type'] . '");
                </script>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
        </form>';
}

// Form for creating tracks
function template_add_track()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_create_track'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=track_insert" id="track_add" name="track_add" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_track'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $_POST['track'] . '" maxlength="255" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_length'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="length" name="length" value="" maxlength="255" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_mileage_unit'] . ':</td>
                <td width="50%">
                <select name="mileage_unit" id="mileage_unit">
                <option value="">------</option>
                <option value="Miles">' . $txt['smfg_miles'] . '</option>
                <option value="Kilometers">' . $txt['smfg_kilometers'] . '</option>
                </select>
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
        </div>
        </form>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <script type="text/javascript">
        var frmvalidator = new Validator("track_add");
        frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_track'] . '");
        frmvalidator.addValidation("title","regexp=^[ A-Za-z0-9]{1,30}$","' . $txt['smfg_val_enter_valid_track'] . '");
        </script>';
}

// Form for editting tracks
function template_edit_track()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_track'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=track_update" id="edit_track" name="edit_track" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_track'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $context['tracks']['title'] . '" maxlength="255" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_length'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="length" name="length" value="' . $context['tracks']['length'] . '" maxlength="255" />
                </td>
            </tr>
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_mileage_unit'] . ':</td>
                <td width="50%">
                <select name="mileage_unit" id="mileage_unit">
                <option value="">------</option>
                <option value="Miles"' . $context['miles'] . '>' . $txt['smfg_miles'] . '</option>
                <option value="Kilometers"' . $context['kilometers'] . '>' . $txt['smfg_kilometers'] . '</option>
                </select>
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            <input type="hidden" name="tid" value="' . $_GET['TID'] . '" />
        </div>
        </form>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <script type="text/javascript">
        var frmvalidator = new Validator("edit_track");
        frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_track'] . '");
        frmvalidator.addValidation("title","regexp=^[ A-Za-z0-9]{1,30}$","' . $txt['smfg_val_enter_valid_track'] . '");
        </script>';
}

// Form for editting track conditions
function template_edit_track_condition()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_track_condition'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=tc_update" id="edit_track_condition" name="edit_track_condition" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_track_condition'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $context['tc']['title'] . '" maxlength="255" />
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            <input type="hidden" name="tcid" value="' . $_GET['TCID'] . '" />
        </div>
        </form>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <script type="text/javascript">
        var frmvalidator = new Validator("edit_track_condition");
        frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_track_condition'] . '");
        </script>';
}

// Form for editting lap types
function template_edit_lap_type()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_lap_type'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=lt_update" id="edit_lap_type" name="edit_lap_type"method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tracks" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_lap_type'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $context['lt']['title'] . '" maxlength="255" />
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            <input type="hidden" name="ltid" value="' . $_GET['LTID'] . '" />
        </div>
        </form>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <script type="text/javascript">
        var frmvalidator = new Validator("edit_lap_type");
        frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_lap_type'] . '");
        </script>';
}

// Form for managing all other garage content
function template_other()
{
    global $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_premium_types'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    while (isset($context['premiumtypes'][$count]['id'])) {
        echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">' . $context['premiumtypes'][$count]['title'] . '</td>
                            <td align="right" width="75%" valign="middle">';

        if ($context['premiumtypes'][$count]['field_order'] == 1) {
            echo '
                                <img src="', $settings['default_images_url'], '/icon_up_disabled.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" />
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=pt_move;direction=down;PTID=' . $context['premiumtypes'][$count]['id'] . ';order=' . $context['premiumtypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_pt_down_' . $context['premiumtypes'][$count]['id'] . '" id="move_pt_down_' . $context['premiumtypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_pt_down_' . $context['premiumtypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
        } else {
            if ($context['premiumtypes'][$count]['field_order'] == $context['premiumtypes']['total']) {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=pt_move;direction=up;PTID=' . $context['premiumtypes'][$count]['id'] . ';order=' . $context['premiumtypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_pt_up_' . $context['premiumtypes'][$count]['id'] . '" id="move_pt_up_' . $context['premiumtypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_pt_up_' . $context['premiumtypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <img src="', $settings['default_images_url'], '/icon_down_disabled.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" />';
            } else {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=pt_move;direction=up;PTID=' . $context['premiumtypes'][$count]['id'] . ';order=' . $context['premiumtypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_pt_up_' . $context['premiumtypes'][$count]['id'] . '" id="move_pt_up_' . $context['premiumtypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_pt_up_' . $context['premiumtypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=pt_move;direction=down;PTID=' . $context['premiumtypes'][$count]['id'] . ';order=' . $context['premiumtypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_pt_down_' . $context['premiumtypes'][$count]['id'] . '" id="move_pt_down_' . $context['premiumtypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_pt_down_' . $context['premiumtypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
            }
        }

        echo '
                            <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=pt_edit;PTID=' . $context['premiumtypes'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=pt_delete;PTID=' . $context['premiumtypes'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_pt_' . $context['premiumtypes'][$count]['id'] . '" id="remove_pt_' . $context['premiumtypes'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_premium_type'] . '\')) { document.remove_pt_' . $context['premiumtypes'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                            </form>';

        echo '
                            </td>
                        </tr>';
        $count++;
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=pt_add" id="pt_add" name="pt_add" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="pt" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addpremiumtype" type="submit" value="' . $txt['smfg_create_premium_type'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("pt_add");
                 var frm = document.forms["pt_add"];
                         
                 frmvalidator.addValidation("pt","req","' . $txt['smfg_val_enter_premium_type'] . '");
                </script>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
        </form>';

    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_engine_types'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    while (isset($context['enginetypes'][$count]['id'])) {
        echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">' . $context['enginetypes'][$count]['title'] . '</td>
                            <td align="right" width="75%" valign="middle">';

        if ($context['enginetypes'][$count]['field_order'] == 1) {
            echo '
                                <img src="', $settings['default_images_url'], '/icon_up_disabled.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" />
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=et_move;direction=down;ETID=' . $context['enginetypes'][$count]['id'] . ';order=' . $context['enginetypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_et_down_' . $context['enginetypes'][$count]['id'] . '" id="move_et_down_' . $context['enginetypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_et_down_' . $context['enginetypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
        } else {
            if ($context['enginetypes'][$count]['field_order'] == $context['enginetypes']['total']) {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=et_move;direction=up;ETID=' . $context['enginetypes'][$count]['id'] . ';order=' . $context['enginetypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_et_up_' . $context['enginetypes'][$count]['id'] . '" id="move_et_up_' . $context['enginetypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_et_up_' . $context['enginetypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <img src="', $settings['default_images_url'], '/icon_down_disabled.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" />';
            } else {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=et_move;direction=up;ETID=' . $context['enginetypes'][$count]['id'] . ';order=' . $context['enginetypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_et_up_' . $context['enginetypes'][$count]['id'] . '" id="move_et_up_' . $context['enginetypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_et_up_' . $context['enginetypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=et_move;direction=down;ETID=' . $context['enginetypes'][$count]['id'] . ';order=' . $context['enginetypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_et_down_' . $context['enginetypes'][$count]['id'] . '" id="move_et_down_' . $context['enginetypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_et_down_' . $context['enginetypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
            }
        }

        echo '
                            <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=et_edit;ETID=' . $context['enginetypes'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=et_delete;ETID=' . $context['enginetypes'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_et_' . $context['enginetypes'][$count]['id'] . '" id="remove_et_' . $context['enginetypes'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_engine_type'] . '\')) { document.remove_et_' . $context['enginetypes'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                            </form>';

        echo '
                            </td>
                        </tr>';
        $count++;
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=et_add" id="et_add" name="et_add" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="et" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addenginetype" type="submit" value="' . $txt['smfg_create_engine_type'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("et_add");
                 var frm = document.forms["et_add"];
                         
                 frmvalidator.addValidation("et","req","' . $txt['smfg_val_enter_engine_type'] . '");
                </script>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
        </form>';

    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_service_types'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    while (isset($context['servicetypes'][$count]['id'])) {
        echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">' . $context['servicetypes'][$count]['title'] . '</td>
                            <td align="right" width="75%" valign="middle">';

        if ($context['servicetypes'][$count]['field_order'] == 1) {
            echo '
                                <img src="', $settings['default_images_url'], '/icon_up_disabled.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" />
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=st_move;direction=down;STID=' . $context['servicetypes'][$count]['id'] . ';order=' . $context['servicetypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_st_down_' . $context['servicetypes'][$count]['id'] . '" id="move_st_down_' . $context['servicetypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_st_down_' . $context['servicetypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
        } else {
            if ($context['servicetypes'][$count]['field_order'] == $context['servicetypes']['total']) {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=st_move;direction=up;STID=' . $context['servicetypes'][$count]['id'] . ';order=' . $context['servicetypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_st_up_' . $context['servicetypes'][$count]['id'] . '" id="move_st_up_' . $context['servicetypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_st_up_' . $context['servicetypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <img src="', $settings['default_images_url'], '/icon_down_disabled.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" />';
            } else {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=st_move;direction=up;STID=' . $context['servicetypes'][$count]['id'] . ';order=' . $context['servicetypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_st_up_' . $context['servicetypes'][$count]['id'] . '" id="move_st_up_' . $context['servicetypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_st_up_' . $context['servicetypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=st_move;direction=down;STID=' . $context['servicetypes'][$count]['id'] . ';order=' . $context['servicetypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_st_down_' . $context['servicetypes'][$count]['id'] . '" id="move_st_down_' . $context['servicetypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_st_down_' . $context['servicetypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
            }
        }

        echo '
                            <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=st_edit;STID=' . $context['servicetypes'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=st_delete;STID=' . $context['servicetypes'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_st_' . $context['servicetypes'][$count]['id'] . '" id="remove_st_' . $context['servicetypes'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_service_type'] . '\')) { document.remove_st_' . $context['servicetypes'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                            </form>';

        echo '
                            </td>
                        </tr>';
        $count++;
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=st_add" id="st_add" name="st_add" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="st" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addservicetype" type="submit" value="' . $txt['smfg_create_service_type'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("st_add");
                 var frm = document.forms["st_add"];
                         
                 frmvalidator.addValidation("st","req","' . $txt['smfg_val_enter_service_type'] . '");
                </script>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
        </form>';

    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_currency_types'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    $count = 0;
    while (isset($context['currencytypes'][$count]['id'])) {
        echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">' . $context['currencytypes'][$count]['title'] . '</td>
                            <td align="right" width="75%" valign="middle">';

        if ($context['currencytypes'][$count]['field_order'] == 1) {
            echo '
                                <img src="', $settings['default_images_url'], '/icon_up_disabled.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" />
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=ct_move;direction=down;CTID=' . $context['currencytypes'][$count]['id'] . ';order=' . $context['currencytypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_ct_down_' . $context['currencytypes'][$count]['id'] . '" id="move_ct_down_' . $context['currencytypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_ct_down_' . $context['currencytypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
        } else {
            if ($context['currencytypes'][$count]['field_order'] == $context['currencytypes']['total']) {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=ct_move;direction=up;CTID=' . $context['currencytypes'][$count]['id'] . ';order=' . $context['currencytypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_ct_up_' . $context['currencytypes'][$count]['id'] . '" id="move_ct_up_' . $context['currencytypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_ct_up_' . $context['currencytypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <img src="', $settings['default_images_url'], '/icon_down_disabled.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" />';
            } else {
                echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=ct_move;direction=up;CTID=' . $context['currencytypes'][$count]['id'] . ';order=' . $context['currencytypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_ct_up_' . $context['currencytypes'][$count]['id'] . '" id="move_ct_up_' . $context['currencytypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_ct_up_' . $context['currencytypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" title="' . $txt['smfg_move_up'] . '" /></a>
                                </form>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=ct_move;direction=down;CTID=' . $context['currencytypes'][$count]['id'] . ';order=' . $context['currencytypes'][$count]['field_order'] . ';sesc=' . $context['session_id'] . '" method="post" name="move_ct_down_' . $context['currencytypes'][$count]['id'] . '" id="move_ct_down_' . $context['currencytypes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                    <a href="#" onClick="document.move_ct_down_' . $context['currencytypes'][$count]['id'] . '.submit(); return false;"><img src="', $settings['default_images_url'], '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" title="' . $txt['smfg_move_down'] . '" /></a>
                                </form>';
            }
        }

        echo '
                            <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=ct_edit;CTID=' . $context['currencytypes'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                            <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=ct_delete;CTID=' . $context['currencytypes'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_ct_' . $context['currencytypes'][$count]['id'] . '" id="remove_ct_' . $context['currencytypes'][$count]['id'] . '" style="display: inline;">
                                <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
                                <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_currency_type'] . '\')) { document.remove_ct_' . $context['currencytypes'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                            </form>';

        echo '
                            </td>
                        </tr>';
        $count++;
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=ct_add" id="ct_add" name="ct_add" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td align="right">
                <input type="text" name="ct" value="" maxlength="255" />
                <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                <input name="addcurrencytype" type="submit" value="' . $txt['smfg_create_currency_type'] . '" />
                <script type="text/javascript">
                 var frmvalidator = new Validator("ct_add");
                 var frm = document.forms["ct_add"];
                         
                 frmvalidator.addValidation("ct","req","' . $txt['smfg_val_enter_currency_type'] . '");
                </script>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
        </form>';
}

// Form for editting premium types
function template_edit_premium_type()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_premium_type'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=pt_update" id="edit_premium_type" name="edit_premium_type" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_premium_type'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $context['pt']['title'] . '" maxlength="255" />
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            <input type="hidden" name="ptid" value="' . $_GET['PTID'] . '" />
        </div>
        </form>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <script type="text/javascript">
        var frmvalidator = new Validator("edit_premium_type");
        frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_premium_type'] . '");
        </script>';
}

// Form for editting engine types
function template_edit_engine_type()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_engine_type'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=et_update" id="edit_engine_type" name="edit_engine_type" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_engine_type'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $context['et']['title'] . '" maxlength="255" />
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            <input type="hidden" name="etid" value="' . $_GET['ETID'] . '" />
        </div>
        </form>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <script type="text/javascript">
    var frmvalidator = new Validator("edit_engine_type");
    frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_engine_type'] . '");
    </script>';
}

// Form for editting service types
function template_edit_service_type()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_service_type'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=st_update" id="edit_service_type" name="edit_service_type" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_service_type'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $context['st']['title'] . '" maxlength="255" />
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            <input type="hidden" name="stid" value="' . $_GET['STID'] . '" />
        </div>
        </form>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <script type="text/javascript">
        var frmvalidator = new Validator("edit_service_type");
        frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_service_type'] . '");
        </script>';
}

// Form for editting currency types
function template_edit_currency_type()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_edit_currency_type'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=ct_update" id="edit_currency_type" name="edit_currency_type" method="post" style="padding:0; margin:0;">
        <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=other" />
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td valign="top" width="16"></td>
                <td valign="top">' . $txt['smfg_currency_type'] . ':</td>
                <td width="50%">
                <input class="medium" type="text" id="title" name="title" value="' . $context['ct']['title'] . '" maxlength="255" />
                </td>
            </tr>
        </table>
        <div class="righttext">
            <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
            <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            <input type="hidden" name="ctid" value="' . $_GET['CTID'] . '" />
        </div>
        </form>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <script type="text/javascript">
        var frmvalidator = new Validator("edit_currency_type");
        frmvalidator.addValidation("title","req","' . $txt['smfg_val_enter_currency_type'] . '");
        </script>';
}

// Tools to optimize database
function template_tools()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_image_directory_size'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
    <table border="0" cellspacing="0" cellpadding="4" width="100%">
        <tr>
            <td>
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    echo '
                    <tr>
                        <td align="left" valign="middle">
                        <b>' . $txt['smfg_upload_directory'] . ':</b>&nbsp;' . $smfgSettings['upload_directory'] . '<br />
                        <b>' . $txt['smfg_total_size'] . ':</b>&nbsp;' . sizeFormat($context['dir_details']['size']) . '<br />
                        <b>' . $txt['smfg_number_files'] . ':</b>&nbsp;' . $context['dir_details']['count'] . '<br />
                        <br />
                        <b>' . $txt['smfg_upload_cache_directory'] . ':</b>&nbsp;' . $smfgSettings['upload_directory'] . 'cache/<br />
                        <b>' . $txt['smfg_total_size'] . ':</b>&nbsp;' . sizeFormat($context['cachedir_details']['size']) . '<br />
                        <b>' . $txt['smfg_number_files'] . ':</b>&nbsp;' . $context['cachedir_details']['count'] . '<br />
                        </td>
                    </tr>';
    echo '
            </table>
            </td>
        </tr>
    </table>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <br />';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_optimize'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
    <table border="0" cellspacing="0" cellpadding="4" width="100%">
        <tr>
            <td>
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    echo '
                    <tr>
                        <td align="center" valign="middle">' . $txt['smfg_tools_description'] . '
                        </td>
                    </tr>';
    echo '
            </table>
            </td>
        </tr>
    </table>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <br />';

    echo '
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr>
        <td align="center">
        <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=orphan_results" id="tools_submit" name="tools_submit" method="post" style="padding:0; margin:0;">
        <input type="submit" value="' . $txt['smfg_begin_optimization'] . '" />
        <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
        </form>
        </td>
    </tr>
    </table>
    <br />';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_regen_cache'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
    <table border="0" cellspacing="0" cellpadding="4" width="100%">
        <tr>
            <td>
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    echo '
                    <tr>
                        <td align="center" valign="middle">
                        ' . $txt['smfg_regen_description'] . '
                        </td>
                    </tr>';
    echo '
            </table>
            </td>
        </tr>
    </table>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <br />';

    echo '
    <table width="80%" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td align="center">            
            <div id="rebuild_parent" style="width: 60%; border: 1px dashed #000; text-align: center; margin-left: auto; margin-right: auto; padding: 10px;" class="windowbg2">
                <div id="rebuild_images_status"></div>
                <div id="progressbar" style="margin: 5px;"></div>
            </div>
            </td>
        </tr>
    </table>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr>
        <td align="center">
        <div id="rebuild_form_div">
        <form method="get" action="#" id="smfg_rebuild_images_form">
        <input name="Start" id="rebuild_form_submit" value="' . $txt['smfg_regen_images'] . '" type="submit" />
        <input type="hidden" name="sc" value="' . $context['session_id'] . '" />
        </form>
        </div>
        </td>
    </tr>
    </table>';
}

// Tools to find/repair orphan files
function template_orphan_results()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt, $orphans, $regen, $entries, $boardurl;
    global $totalcount, $missingcount, $regencount, $missing;

    echo '
        <script type="text/javascript">
        function checkUncheckAll(theElement) 
        {
            var theForm = theElement.form, z = 0;
            for(z=0; z<theForm.length;z++){
              if(theForm[z].type == \'checkbox\' && theForm[z].name != \'checkall\'){
                theForm[z].checked = theElement.checked;
              }
            }
        } 
        </script>';

    echo '
    <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=optimize" id="optimizedatabase" name="optimizedatabase" method="post" style="padding:0; margin:0;">';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
    <table border="0" cellspacing="0" cellpadding="4" width="100%">
        <tr>
            <td>
            <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    echo '
                    <tr>
                        <td align="left" valign="middle">
                        ' . $txt['smfg_orphan_results_description'] . '
                        </td>
                    </tr>';
    echo '
            </table>
            </td>
        </tr>
    </table>';

    echo '            
        </div>
        <span class="botslice"><span></span></span>
    </div>';

    echo '
    <br />';

    /*  // I think this was used on an older version of the image regen

        // If regen ran, then show results
        if($_POST['regencache']) {

            echo '
            <div class="title_bar">
                <h3 class="titlebg">', $txt['missing_files'], '</h3>
            </div>';

            echo '
            <div class="windowbg">
                <span class="topslice"><span></span></span>
                    <div class="content">';

            echo '
            <table border="0" cellspacing="0" cellpadding="4" width="100%">
                <tr>
                    <td>
                    <table border="0" cellpadding="3" cellspacing="1" width="100%">';
                        echo '
                            <tr>
                                <td align="center" valign="middle">
                                '.$txt['smfg_regen_description'].'
                                </td>
                            </tr>';
                    echo '
                    </table>
                    </td>
                </tr>
            </table>';

            echo '
                </div>
                <span class="botslice"><span></span></span>
            </div>';

            echo '
            <br />';

            echo '
            <table width="80%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
                <tr>
                    <td align="center">
                    <table border="0" cellspacing="0" cellpadding="4" width="100%">
                        <tr class="titlebg">
                            <td colspan="3">'.$txt['smfg_regenerated_files'].'</td>
                        </tr>
                        <tr>
                            <td class="windowbg" align="center">
                            <table border="0" cellpadding="3" cellspacing="1" width="100%" class="bordercolor">
                                            <tr class="windowbg">
                                                <td align="left" valign="middle" class="windowbg2">'.str_replace('@TOTAL@', $totalcount, str_replace('@REGEN@', $regencount, str_replace('@MISSING@', $missingcount, $txt['smfg_regenerated_results']))).'</td>
                                            </tr>
                            </table>
                            </td>
                        </tr>
                    </table>
                    </td>
                </tr>
            </table>
            <br />';

            if($missingcount > 0) {
                echo '
                <table width="80%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
                    <tr>
                        <td align="center">
                        <table border="0" cellspacing="0" cellpadding="4" width="100%">
                            <tr class="titlebg">
                                <td colspan="3">'.$txt['missing_files'].'</td>
                            </tr>
                            <tr>
                                <td class="windowbg" align="center">
                                <table border="0" cellpadding="3" cellspacing="1" width="100%" class="bordercolor">';
                                foreach($missing AS $lost) {
                                                echo '
                                                <tr class="windowbg">
                                                    <td align="left" valign="middle" class="windowbg2"><input type="checkbox" name="missing_files[]" value="'.$lost.'" />&nbsp;<a href="', (strpos($lost, 'http') === FALSE) ? $boardurl.'/'.$smfgSettings['upload_directory'].$lost : $lost ,'" target="_blank">', (strpos($lost, 'http') === FALSE) ? $lost : shorten_subject($lost, 50) ,'</a></td>
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
            <br /> ';
            }
        }

        */

    // Show ye' orphans
    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_orphaned_files'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    if (!empty($orphans)) {
        $count = 0;
        foreach ($orphans AS $orphan) {
            $file = explode('/', $orphan);
            echo '
                                <tr>
                                    <td align="left" valign="middle"><input type="checkbox" name="orphaned_files[]" value="' . $orphan . '" />&nbsp;<a href="' . $boardurl . '/' . $smfgSettings['upload_directory'] . $file[count($file) - 1] . '" rel="shadowbox">' . $file[count($file) - 1] . '</a></td>
                                </tr>';
            $count++;
        }
    } else {
        echo '
                        <tr>
                            <td align="center" valign="middle">' . $txt['smfg_no_orphans'] . '</td>
                        </tr>';
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <br />';

    // ...and the entries
    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_db_entries'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    if (!empty($entries)) {
        $count = 0;
        foreach ($entries AS $entry) {
            echo '
                                <tr>
                                    <td align="left" valign="middle"><input type="checkbox" name="db_entries[]" value="' . $entry . '" />&nbsp;' . $entry . '</td>
                                </tr>';
            $count++;
        }
    } else {
        echo '
                        <tr>
                            <td align="center" valign="middle">' . $txt['smfg_no_entries'] . '</td>
                        </tr>';
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <br />';

    // ...and the regens
    echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_db_regen'], '</h3>
        </div>';

    echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

    echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
    if (!empty($regen)) {
        $count = 0;
        foreach ($regen AS $gen) {
            echo '
                                <tr>
                                    <td align="left" valign="middle"><input type="checkbox" name="db_regen[]" value="' . $gen . '" />&nbsp;<a href="' . $boardurl . '/' . $smfgSettings['upload_directory'] . $gen . '" rel="shadowbox">' . $gen . '</a></td>
                                </tr>';
            $count++;
        }
    } else {
        echo '
                        <tr>
                            <td align="center" valign="middle">' . $txt['smfg_no_regen'] . '</td>
                        </tr>';
    }
    echo '
                </table>
                </td>
            </tr>
        </table>';

    echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
        <br />';

    // Do we have anything to optimize?
    if (!empty($orphans) || !empty($entries) || !empty($regen) || !empty($missing)) {

        echo '
                <br />
                <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                <tr>
                    <td align="center">
                    <div style="float:left">&nbsp;&nbsp;<input type="checkbox" name="checkall" id="checkall" onclick="checkUncheckAll(this);" />&nbsp;' . $txt['smfg_select_all'] . '</div><input type="submit" value="' . $txt['smfg_delete_entries'] . '" /><input type="hidden" name="sc" value="' . $context['session_id'] . '" />
                    </td>
                </tr>
                </table>
                </form>';

    }

    /*if(isset($context['smfg_debug']) && !empty($context['smfg_debug'])) {
            echo '<br /><table width="80%" border="0" cellspacing="0" cellpadding="0" align="center">
                    <tr>
                        <td align="center">
                        <textarea name= "debug" cols="70" rows="5">'.str_replace('<br />', "\n", $context['smfg_debug']).'</textarea>
                        </td>
                    </tr>
                    </table>';
        }*/

    // DEBUG OUTPUT
    if (isset($context['smfg_debug']) && !empty($context['smfg_debug'])) {
        echo '
            <br />
            <table width="80%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
                <tr>
                    <td align="center">
                    <table border="0" cellspacing="0" cellpadding="4" width="100%">
                        <tr class="titlebg">
                            <td colspan="3">' . $txt['smfg_debug'] . '</td>
                        </tr>
                        <tr>
                            <td class="windowbg" align="center">                      
                            <table border="0" cellpadding="3" cellspacing="1" width="100%" class="bordercolor">
                            <tr class="windowbg">
                                <td align="left" valign="middle" class="windowbg2"><textarea name= "debug" cols="68" rows="5">' . str_replace('<br />',
                "\n", $context['smfg_debug']) . '</textarea></td>
                            </tr>
                            </table>
                            </td>
                        </tr>
                    </table>
                    </td>
                </tr>
            </table>';
    }
}

// All pending or disabled items will be shown here
function template_pending()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    // Vehicles
    $count = 0;
    if (isset($context['vehicles'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_vehicles_caps'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        while (isset($context['vehicles'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=profile;u=' . $context['vehicles'][$count]['owner_id'] . '">' . $context['vehicles'][$count]['owner'] . '</a>\'s <a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['vehicles'][$count]['id'] . '">' . $context['vehicles'][$count]['vehicle'] . '</a></td>
                            <td align="right" width="75%" valign="middle">';

            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=vehicle_approve;VID=' . $context['vehicles'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_vehicle_' . $context['vehicles'][$count]['id'] . '" id="approve_vehicle_' . $context['vehicles'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="document.approve_vehicle_' . $context['vehicles'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';

            echo '
                                <a href="' . $scripturl . '?action=garage;sa=edit_vehicle;VID=' . $context['vehicles'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=delete_vehicle;VID=' . $context['vehicles'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_vehicle_' . $context['vehicles'][$count]['id'] . '" id="remove_vehicle_' . $context['vehicles'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_vehicle'] . '\')) { document.remove_vehicle_' . $context['vehicles'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
        echo '
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

        echo '
        <br />';
    }

    // Modifications
    $count = 0;
    if (isset($context['mods'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_modifications'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        while (isset($context['mods'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_modification;VID=' . $context['mods'][$count]['vid'] . ';MID=' . $context['mods'][$count]['id'] . '">' . $context['mods'][$count]['modification'] . '</a> on <a href="' . $scripturl . '?action=profile;u=' . $context['mods'][$count]['owner_id'] . '">' . $context['mods'][$count]['owner'] . '</a>\'s <a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['mods'][$count]['vid'] . '">' . $context['mods'][$count]['vehicle'] . '</a></td>
                            <td align="right" width="75%" valign="middle">';

            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=modification_approve;MID=' . $context['mods'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_mod_' . $context['mods'][$count]['id'] . '" id="approve_mod_' . $context['mods'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="document.approve_mod_' . $context['mods'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';

            echo '
                                <a href="' . $scripturl . '?action=garage;sa=edit_modification;VID=' . $context['mods'][$count]['vid'] . ';MID=' . $context['mods'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=delete_modification;VID=' . $context['mods'][$count]['vid'] . ';MID=' . $context['mods'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_mod_' . $context['mods'][$count]['id'] . '" id="remove_mod_' . $context['mods'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_modification'] . '\')) { document.remove_mod_' . $context['vehicles'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
        echo '
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

        echo '
        <br />';
    }

    // Makes
    $count = 0;
    if (isset($context['makes'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_makes'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        while (isset($context['makes'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">' . $context['makes'][$count]['make'] . '</td>
                            <td align="right" width="75%" valign="middle">';

            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=make_approve;mkid=' . $context['makes'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_make_' . $context['makes'][$count]['id'] . '" id="approve_make_' . $context['makes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="document.approve_make_' . $context['makes'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';

            echo '
                                <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=make_edit;mkid=' . $context['makes'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=make_delete;mkid=' . $context['makes'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_make_' . $context['makes'][$count]['id'] . '" id="remove_make_' . $context['makes'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_make'] . '\')) { document.remove_make_' . $context['makes'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
        echo '
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

        echo '
        <br />';
    }

    // Models
    $count = 0;
    if (isset($context['models'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_models'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        while (isset($context['models'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">' . $context['models'][$count]['make'] . ' ' . $context['models'][$count]['model'] . '</td>
                            <td align="right" width="75%" valign="middle">';

            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=model_approve;mdid=' . $context['models'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_model_' . $context['models'][$count]['id'] . '" id="approve_model_' . $context['models'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="document.approve_model_' . $context['models'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';

            echo '
                                <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=model_edit;mdid=' . $context['models'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=model_delete;mdid=' . $context['models'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_model_' . $context['models'][$count]['id'] . '" id="remove_model_' . $context['models'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_model'] . '\')) { document.remove_model_' . $context['models'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
        echo '
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

        echo '
        <br />';
    }

    // Quartermiles
    $count = 0;
    if (isset($context['qmiles'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_quartermiles'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        while (isset($context['qmiles'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_quartermile;VID=' . $context['qmiles'][$count]['vid'] . ';QID=' . $context['qmiles'][$count]['id'] . '">Quartermile</a> for <a href="' . $scripturl . '?action=profile;u=' . $context['qmiles'][$count]['owner_id'] . '">' . $context['qmiles'][$count]['owner'] . '</a>\'s <a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['qmiles'][$count]['vid'] . '">' . $context['qmiles'][$count]['vehicle'] . '</a></td>
                            <td align="right" width="75%" valign="middle">';

            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=quartermile_approve;QID=' . $context['qmiles'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_qmile_' . $context['qmiles'][$count]['id'] . '" id="approve_qmile_' . $context['qmiles'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="document.approve_qmile_' . $context['qmiles'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';

            echo '
                                <a href="' . $scripturl . '?action=garage;sa=edit_quartermile;VID=' . $context['qmiles'][$count]['vid'] . ';QID=' . $context['qmiles'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=delete_quartermile;VID=' . $context['qmiles'][$count]['vid'] . ';QID=' . $context['qmiles'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_qmile_' . $context['qmiles'][$count]['id'] . '" id="remove_qmile_' . $context['qmiles'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_quartermile'] . '\')) { document.remove_qmile_' . $context['qmiles'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
        echo '
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

        echo '
        <br />';
    }

    // Dynoruns
    $count = 0;
    if (isset($context['dynoruns'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_dynoruns'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        while (isset($context['dynoruns'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_dynorun;VID=' . $context['dynoruns'][$count]['vid'] . ';DID=' . $context['dynoruns'][$count]['id'] . '">Dynorun</a> for <a href="' . $scripturl . '?action=profile;u=' . $context['dynoruns'][$count]['owner_id'] . '">' . $context['dynoruns'][$count]['owner'] . '</a>\'s <a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['dynoruns'][$count]['vid'] . '">' . $context['dynoruns'][$count]['vehicle'] . '</a></td>
                            <td align="right" width="75%" valign="middle">';

            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=dynorun_approve;DID=' . $context['dynoruns'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_dynorun_' . $context['dynoruns'][$count]['id'] . '" id="approve_dynorun_' . $context['dynoruns'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="document.approve_dynorun_' . $context['dynoruns'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';

            echo '
                                <a href="' . $scripturl . '?action=garage;sa=edit_dynorun;VID=' . $context['dynoruns'][$count]['vid'] . ';DID=' . $context['dynoruns'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=delete_dynorun;VID=' . $context['dynoruns'][$count]['vid'] . ';DID=' . $context['dynoruns'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_dynorun_' . $context['dynoruns'][$count]['id'] . '" id="remove_dynorun_' . $context['dynoruns'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_dynorun'] . '\')) { document.remove_dynorun_' . $context['dynoruns'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
        echo '
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

        echo '
        <br />';
    }

    // Laptimes
    $count = 0;
    if (isset($context['laps'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_laptimes'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        while (isset($context['laps'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_laptime;VID=' . $context['laps'][$count]['vid'] . ';LID=' . $context['laps'][$count]['id'] . '">Laptime</a> for <a href="' . $scripturl . '?action=profile;u=' . $context['laps'][$count]['owner_id'] . '">' . $context['laps'][$count]['owner'] . '</a>\'s <a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['laps'][$count]['vid'] . '">' . $context['laps'][$count]['vehicle'] . '</a></td>
                            <td align="right" width="75%" valign="middle">';

            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=laptime_approve;LID=' . $context['laps'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_laptime_' . $context['laps'][$count]['id'] . '" id="approve_laptime_' . $context['laps'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="document.approve_laptime_' . $context['laps'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';

            echo '
                                <a href="' . $scripturl . '?action=garage;sa=edit_laptime;VID=' . $context['laps'][$count]['vid'] . ';LID=' . $context['laps'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=delete_laptime;VID=' . $context['laps'][$count]['vid'] . ';LID=' . $context['laps'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_laptime_' . $context['laps'][$count]['id'] . '" id="remove_laptime_' . $context['laps'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_laptime'] . '\')) { document.remove_laptime_' . $context['laps'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
        echo '
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

        echo '
        <br />';
    }

    // Tracks
    $count = 0;
    if (isset($context['tracks'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_tracks'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        while (isset($context['tracks'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap"><a href="' . $scripturl . '?action=garage;sa=view_track;TID=' . $context['tracks'][$count]['id'] . '">' . $context['tracks'][$count]['track'] . '</a></td>
                            <td align="right" width="75%" valign="middle">';

            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=track_approve;TID=' . $context['tracks'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_track_' . $context['tracks'][$count]['id'] . '" id="approve_track_' . $context['tracks'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="document.approve_track_' . $context['tracks'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';

            echo '
                                <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=track_edit;TID=' . $context['tracks'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=track_delete;TID=' . $context['tracks'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_track_' . $context['tracks'][$count]['id'] . '" id="remove_track_' . $context['tracks'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_track'] . '\')) { document.remove_track_' . $context['tracks'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
        echo '
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

        echo '
        <br />';
    }

    // Business
    $count = 0;
    if (isset($context['business'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_businesses'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        while (isset($context['business'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">' . $context['business'][$count]['business'] . ' (' . $context['business'][$count]['type'] . ')</td>
                            <td align="right" width="75%" valign="middle">';

            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_approve;BID=' . $context['business'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_business_' . $context['business'][$count]['id'] . '" id="approve_business_' . $context['business'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="document.approve_business_' . $context['business'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';

            echo '
                                <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=business_edit;BID=' . $context['business'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=' . $context['business'][$count]['lowertype'] . '_delete;BID=' . $context['business'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_business_' . $context['business'][$count]['id'] . '" id="remove_business_' . $context['business'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_business'] . '\')) { document.remove_business_' . $context['business'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
        echo '
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

        echo '
        <br />';
    }

    // Products
    $count = 0;
    if (isset($context['products'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_products'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        while (isset($context['products'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">' . $context['products'][$count]['manufacturer'] . ' ' . $context['products'][$count]['product'] . ' (' . $context['products'][$count]['category'] . ')</td>
                            <td align="right" width="75%" valign="middle">';

            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=product_approve;pid=' . $context['products'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_product_' . $context['products'][$count]['id'] . '" id="approve_product_' . $context['products'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="document.approve_product_' . $context['products'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';

            echo '
                                <a href="' . $scripturl . '?action=admin;area=garagemanagement;sa=product_edit;pid=' . $context['products'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=product_delete;pid=' . $context['products'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_product_' . $context['products'][$count]['id'] . '" id="remove_product_' . $context['products'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_product'] . '\')) { document.remove_product_' . $context['products'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
        echo '
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

        echo '
        <br />';
    }

    // Guestbook Comments
    $count = 0;
    if (isset($context['comments'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_comments'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">';
        while (isset($context['comments'][$count]['id'])) {
            echo '
                        <tr class="tableRow">
                            <td align="left" valign="middle" nowrap="nowrap">Comment by <a href="' . $scripturl . '?action=profile;u=' . $context['comments'][$count]['author_id'] . '">' . $context['comments'][$count]['author'] . '</a> on <a href="' . $scripturl . '?action=garage;sa=view_vehicle;VID=' . $context['comments'][$count]['vid'] . '">' . $context['comments'][$count]['vehicle'] . '</a></td>
                            <td align="right" width="75%" valign="middle">';

            echo '
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=comment_approve;CID=' . $context['comments'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="approve_comment_' . $context['comments'][$count]['id'] . '" id="approve_comment_' . $context['comments'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="document.approve_comment_' . $context['comments'][$count]['id'] . '.submit(); return false;"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="' . $txt['smfg_approve'] . '" title="' . $txt['smfg_approve'] . '" /></a>
                                </form>';

            echo '
                                <a href="' . $scripturl . '?action=garage;sa=edit_comment;VID=' . $context['comments'][$count]['id'] . ';CID=' . $context['comments'][$count]['id'] . '"><img src="', $settings['default_images_url'], '/icon_edit.gif" alt="' . $txt['smfg_edit'] . '" title="' . $txt['smfg_edit'] . '" /></a>
                                <form action="' . $scripturl . '?action=admin;area=garagemanagement;sa=delete_comment;VID=' . $context['comments'][$count]['id'] . ';CID=' . $context['comments'][$count]['id'] . ';sesc=' . $context['session_id'] . '" method="post" name="remove_comment_' . $context['comments'][$count]['id'] . '" id="remove_comment_' . $context['comments'][$count]['id'] . '" style="display: inline;">
                                    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=pending" />
                                    <a href="#" onClick="if (confirm(\'' . $txt['smfg_delete_comment'] . '\')) { document.remove_comment_' . $context['comments'][$count]['id'] . '.submit(); } else { return false; } return false;"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                                </form>';

            echo '
                            </td>
                        </tr>';
            $count++;
        }
        echo '
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

        echo '
        <br />';
    }

    // And if there are no pending items
    $count = 0;
    if (!isset($context['vehicles'][$count]['id']) && !isset($context['mods'][$count]['id']) && !isset($context['makes'][$count]['id']) && !isset($context['models'][$count]['id']) && !isset($context['qmiles'][$count]['id']) && !isset($context['dynoruns'][$count]['id']) && !isset($context['laps'][$count]['id']) && !isset($context['tracks'][$count]['id']) && !isset($context['business'][$count]['id']) && !isset($context['products'][$count]['id']) && !isset($context['comments'][$count]['id'])) {

        echo '
        <div class="title_bar">
            <h3 class="titlebg">', $txt['smfg_pending_items'], '</h3>
        </div>';

        echo '            
        <div class="windowbg">
            <span class="topslice"><span></span></span>
                <div class="content">';

        echo '
        <table border="0" cellspacing="0" cellpadding="4" width="100%">
            <tr>
                <td>
                <table border="0" cellpadding="3" cellspacing="1" width="100%">
                    <tr>
                        <td align="center" valign="middle">' . $txt['smfg_no_pending'] . '</td>
                    </tr>
                </table>
                </td>
            </tr>
        </table>';

        echo '            
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    }
}

// This is a blank template for all forwarding actions
function template_blank()
{
    // Nothing to see here folks
}
