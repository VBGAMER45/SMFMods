<?php
/**********************************************************************************
 * GarageSettings.template.php                                                     *
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

// Form for editing general garage settings
function template_garage_settings()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div id="general_settings">';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_general_settings'], '</h3>
    </div>';

    echo '
    <form action="' . $scripturl . '?action=admin;area=garagesettings;sa=updategeneral" id="garage_settings" name="garage_settings" method="post" accept-charset="ISO-8859-1">
    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagesettings;sa=general" />';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
            <table border="0" cellspacing="0" cellpadding="4" width="100%">
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_disable_garage'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="disable_garage" name="config[disable_garage]" value="1" ' . $context['disable_garage_check'] . ' class="check" />
                    </td>
                </tr>
                
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>

                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=year_range" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_year_end_beginning'] . ':</td>
                    <td width="50%">
                        <input type="text" name="config[year_start]" size="4" maxlength="4" value="' . $smfgSettings['year_start'] . '" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=year_offset" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_year_range_end_offset'] . ':</td>
                    <td width="50%">
                        <input type="text" name="config[year_end]" size="2" maxlength="2" value="' . $smfgSettings['year_end'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=vehicle_quota" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_vehicle_quota'] . ':</td>
                    <td width="50%">
                        <input type="text" id="default_vehicle_quota" name="config[default_vehicle_quota]" value="' . $smfgSettings['default_vehicle_quota'] . '" maxlength="2" size="2" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_vehicle_approval'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_vehicle_approval" name="config[enable_vehicle_approval]" value="1" ' . $context['enable_vehicle_approval_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_date_format'] . ':</td>
                    <td width="50%">
                        <input type="text" name="config[dateformat]" id="dateformat" value="' . $smfgSettings['dateformat'] . '" size="10" maxlength="30" /><br />
                        <select name="dateoptions" id="dateoptions" onchange="if (this.value == \'custom\') { document.getElementById(\'dateformat\').value = \'' . $smfgSettings['dateformat'] . '\'; } else { document.getElementById(\'dateformat\').value = this.value; }">
                        <option value="d M Y, H:i"' . $context['one'] . '>12 Jun 2007, 15:04</option>
                        <option value="d M Y H:i"' . $context['two'] . '>12 Jun 2007 15:04</option>
                        <option value="M jS, \'y, H:i"' . $context['three'] . '>Jun 12th, \'07, 15:04</option>
                        <option value="D M d, Y g:i a"' . $context['four'] . '>Tue Jun 12, 2007 3:04 pm</option>
                        <option value="F jS, Y, g:i a"' . $context['five'] . '>June 12th, 2007, 3:04 pm</option>
                        <option value="|d M Y| H:i"' . $context['six'] . '>|12 Jun 2007| 15:04</option>
                        <option value="|F jS, Y| g:i a"' . $context['seven'] . '>|June 12th, 2007| 3:04 pm</option>
                        <option value="custom"' . $context['custom'] . '>' . $txt['smfg_custom'] . '</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=vehicles_per_page" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_vehicles_per_page'] . ':</td>
                    <td width="50%">
                        <input type="text" name="config[cars_per_page]" size="3" maxlength="3" value="' . $smfgSettings['cars_per_page'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_search_results_per_page'] . ':</td>
                    <td width="50%">
                        <input type="text" name="config[results_per_page]" size="3" maxlength="3" value="' . $smfgSettings['results_per_page'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_insurance_per_page'] . ':</td>
                    <td width="50%">
                        <input type="text" name="config[insurance_review_limit]" size="3" maxlength="3" value="' . $smfgSettings['insurance_review_limit'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_shops_per_page'] . ':</td>
                    <td width="50%">
                        <input type="text" name="config[shop_review_limit]" size="3" maxlength="3" value="' . $smfgSettings['shop_review_limit'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_garages_per_page'] . ':</td>
                    <td width="50%">
                        <input type="text" name="config[garage_review_limit]" size="3" maxlength="3" value="' . $smfgSettings['garage_review_limit'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_blog_posts_per_page'] . ':</td>
                    <td width="50%">
                        <input type="text" name="config[blogs_per_page]" size="3" maxlength="3" value="' . $smfgSettings['blogs_per_page'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_comments_per_page'] . ':</td>
                    <td width="50%">
                        <input type="text" name="config[comments_per_page]" size="3" maxlength="3" value="' . $smfgSettings['comments_per_page'] . '" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>

                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=user_make_submission" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_user_make_submission'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_user_submit_make" name="config[enable_user_submit_make]" value="1" ' . $context['user_submit_make_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_make_approval'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_make_approval" name="config[enable_make_approval]" value="1" ' . $context['enable_make_approval_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=user_model_submission" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_user_model_submission'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_user_submit_model" name="config[enable_user_submit_model]" value="1" ' . $context['user_submit_model_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_model_approval'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_model_approval" name="config[enable_model_approval]" value="1" ' . $context['enable_model_approval_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=user_business_submission" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_user_business_submission'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_user_submit_business" name="config[enable_user_submit_business]" value="1" ' . $context['enable_user_submit_business_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_business_approval'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_business_approval" name="config[enable_business_approval]" value="1" ' . $context['enable_business_approval_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=user_product_submission" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_user_product_submission'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_user_submit_product" name="config[enable_user_submit_product]" value="1" ' . $context['enable_user_submit_product_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_product_approval'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_product_approval" name="config[enable_product_approval]" value="1" ' . $context['enable_product_approval_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>

                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=pending_pm" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_pending_pm_notification'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_pm_pending_notify" name="config[enable_pm_pending_notify]" value="1" ' . $context['enable_pm_pending_notify_check'] . ' class="check" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=pm_opt_out" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_pending_pm_notification_optout'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_pm_pending_notify_optout" name="config[enable_pm_pending_notify_optout]" value="1" ' . $context['enable_pm_pending_notify_optout_check'] . ' class="check" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=pending_email" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_pending_email_notification'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_email_pending_notify" name="config[enable_email_pending_notify]" value="1" ' . $context['enable_email_pending_notify_check'] . ' class="check" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=email_opt_out" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_pending_email_notification_optout'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_email_pending_notify_optout" name="config[enable_email_pending_notify_optout]" value="1" ' . $context['enable_email_pending_notify_optout_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=pending_subject" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_pending_notification_subject'] . ':</td>
                    <td width="50%">
                        <input id="pending_subject" type="text" size="40" maxlength="100" name="config[pending_subject]" value="' . $smfgSettings['pending_subject'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=pending_sender_id" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_pending_pm_sender_id'] . ':</td>
                    <td width="50%">
                        <input id="pending_sender" type="text" size="3" maxlength="7" name="config[pending_sender]" value="' . $smfgSettings['pending_sender'] . '" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=integrate_viewtopic" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_integrate_viewtopic'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="integrate_viewtopic" name="config[integrate_viewtopic]" value="1" ' . $context['integrate_viewtopic_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=integrate_profile" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_integrate_profile'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="integrate_profile" name="config[integrate_profile]" value="1" ' . $context['integrate_profile_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=rating_system" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_rating_system'] . ':</td>
                    <td width="50%">
                        <input type="radio" name="config[rating_system]" id="rating_system" value="0" ' . $context['sum_rating_check'] . ' class="radio" /> ' . $txt['smfg_sum_rating_system'] . '<input type="radio" name="config[rating_system]" value="1" ' . $context['avg_rating_check'] . ' class="radio" /> ' . $txt['smfg_avg_rating_system'] . '
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
            </table>
                
            <div class="righttext">
                <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
                <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            </div>
            
                </div>
                <span class="botslice"><span></span></span>
            </div>
        </form>';

    echo '      
        
    <script type="text/javascript">
    var frmvalidator = new Validator("garage_settings");
            
    frmvalidator.addValidation("config[year_start]","req","' . $txt['smfg_val_enter_year_range'] . '");
    frmvalidator.addValidation("config[year_start]","minlen=4","' . $txt['smfg_val_year_range_restriction1'] . '");
    frmvalidator.addValidation("config[year_start]","maxlen=4","' . $txt['smfg_val_year_range_restriction1'] . '");
    frmvalidator.addValidation("config[year_start]","numeric","' . $txt['smfg_val_year_range_restriction2'] . '");
            
    frmvalidator.addValidation("config[year_end]","req","' . $txt['smfg_val_enter_year_offset'] . '");
    frmvalidator.addValidation("config[year_end]","minlen=1","' . $txt['smfg_val_year_offset_restriction'] . '");
    frmvalidator.addValidation("config[year_end]","maxlen=2","' . $txt['smfg_val_year_offset_restriction1'] . '");
    frmvalidator.addValidation("config[year_end]","numeric","' . $txt['smfg_val_year_offset_restriction2'] . '");
            
    frmvalidator.addValidation("config[default_vehicle_quota]","req","' . $txt['smfg_val_enter_vehicle_quota'] . '");
    frmvalidator.addValidation("config[default_vehicle_quota]","minlen=1","' . $txt['smfg_val_vehicle_quota_restriction'] . '");
    frmvalidator.addValidation("config[default_vehicle_quota]","maxlen=2","' . $txt['smfg_val_vehicle_quota_restriction1'] . '");
    frmvalidator.addValidation("config[default_vehicle_quota]","numeric","' . $txt['smfg_val_vehicle_quota_restriction2'] . '");
            
    frmvalidator.addValidation("config[cars_per_page]","req","' . $txt['smfg_val_enter_vehicles_per_page'] . '");
    frmvalidator.addValidation("config[cars_per_page]","minlen=1","' . $txt['smfg_val_vehicles_per_page_restriction'] . '");
    frmvalidator.addValidation("config[cars_per_page]","maxlen=2","' . $txt['smfg_val_vehicles_per_page_restriction1'] . '");
    frmvalidator.addValidation("config[cars_per_page]","numeric","' . $txt['smfg_val_vehicles_per_page_restriction2'] . '");
            
    frmvalidator.addValidation("config[results_per_page]","req","' . $txt['smfg_val_enter_results_per_page'] . '");
    frmvalidator.addValidation("config[results_per_page]","minlen=1","' . $txt['smfg_val_results_per_page_restriction'] . '");
    frmvalidator.addValidation("config[results_per_page]","maxlen=2","' . $txt['smfg_val_results_per_page_restriction1'] . '");
    frmvalidator.addValidation("config[results_per_page]","numeric","' . $txt['smfg_val_results_per_page_restriction2'] . '");
            
    frmvalidator.addValidation("config[insurance_review_limit]","req","' . $txt['smfg_val_enter_insurance_per_page'] . '");
    frmvalidator.addValidation("config[insurance_review_limit]","minlen=1","' . $txt['smfg_val_insurance_per_page_restriction'] . '");
    frmvalidator.addValidation("config[insurance_review_limit]","maxlen=2","' . $txt['smfg_val_insurance_per_page_restriction1'] . '");
    frmvalidator.addValidation("config[insurance_review_limit]","numeric","' . $txt['smfg_val_insurance_per_page_restriction2'] . '");
            
    frmvalidator.addValidation("config[shop_review_limit]","req","' . $txt['smfg_val_enter_shops_per_page'] . '");
    frmvalidator.addValidation("config[shop_review_limit]","minlen=1","' . $txt['smfg_val_shops_per_page_restriction'] . '");
    frmvalidator.addValidation("config[shop_review_limit]","maxlen=2","' . $txt['smfg_val_shops_per_page_restriction1'] . '");
    frmvalidator.addValidation("config[shop_review_limit]","numeric","' . $txt['smfg_val_shops_per_page_restriction2'] . '");
            
    frmvalidator.addValidation("config[garage_review_limit]","req","' . $txt['smfg_val_enter_garages_per_page'] . '");
    frmvalidator.addValidation("config[garage_review_limit]","minlen=1","' . $txt['smfg_val_garages_per_page_restriction'] . '");
    frmvalidator.addValidation("config[garage_review_limit]","maxlen=2","' . $txt['smfg_val_garages_per_page_restriction1'] . '");
    frmvalidator.addValidation("config[garage_review_limit]","numeric","' . $txt['smfg_val_garages_per_page_restriction2'] . '");
            
    frmvalidator.addValidation("config[blogs_per_page]","req","' . $txt['smfg_val_enter_blogs_per_page'] . '");
    frmvalidator.addValidation("config[blogs_per_page]","minlen=1","' . $txt['smfg_val_blogs_per_page_restriction'] . '");
    frmvalidator.addValidation("config[blogs_per_page]","maxlen=2","' . $txt['smfg_val_blogs_per_page_restriction1'] . '");
    frmvalidator.addValidation("config[blogs_per_page]","numeric","' . $txt['smfg_val_blogs_per_page_restriction2'] . '");
            
    frmvalidator.addValidation("config[comments_per_page]","req","' . $txt['smfg_val_enter_comments_per_page'] . '");
    frmvalidator.addValidation("config[comments_per_page]","minlen=1","' . $txt['smfg_val_comments_per_page_restriction'] . '");
    frmvalidator.addValidation("config[comments_per_page]","maxlen=2","' . $txt['smfg_val_comments_per_page_restriction1'] . '");
    frmvalidator.addValidation("config[comments_per_page]","numeric","' . $txt['smfg_val_comments_per_page_restriction2'] . '");
            
    frmvalidator.addValidation("config[pending_subject]","req","' . $txt['smfg_val_enter_notification_subject'] . '");
            
    frmvalidator.addValidation("config[pending_sender]","req","' . $txt['smfg_val_enter_sender_id'] . '");
    frmvalidator.addValidation("config[pending_sender]","minlen=1","' . $txt['smfg_val_sender_id_restriction1'] . '");
    frmvalidator.addValidation("config[pending_sender]","maxlen=7","' . $txt['smfg_val_sender_id_restriction1'] . '");
    frmvalidator.addValidation("config[pending_sender]","numeric","' . $txt['smfg_val_sender_id_restriction2'] . '");
    </script>';

    echo '
    <br />';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_notification_list'], '</h3>
    </div>';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '    
    <form action="' . $scripturl . '?action=admin;area=garagesettings;sa=notify_add" id="notify_members" name="notify_members" method="post" accept-charset="ISO-8859-1">
    <table border="0" cellspacing="0" cellpadding="4" width="100%">
        <tr>
            <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=members_notify" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
            <td valign="top">' . $txt['smfg_members_to_notify'] . ':</td>
            <td width="50%">
            
                <table width="30%" border="0" cellspacing="1" cellpadding="2">';
    $count = 0;
    while (isset($context['notifications'][$count]['id'])) {
        echo '
                    <tr>
                        <td align="left" nowrap="nowrap">' . $context['notifications'][$count]['user'] . '</td>
                        <td width="15%" align="center" nowrap="nowrap"><a href="' . $scripturl . '?action=admin;area=garagesettings;sa=notify_delete;ID=' . $context['notifications'][$count]['id'] . ';sesc=', $context['session_id'], '"><img src="' . $settings['default_images_url'] . '/icon_delete.gif" alt="' . $txt['smfg_delete'] . '" title="' . $txt['smfg_delete'] . '" /></a>
                        </td>
                    </tr>';
        $count++;
    }
    echo '
                </table>
                
            </td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td>            
            <input name="username" id="username" type="text" size="35" value="" tabindex="', $context['tabindex']++, '" />&nbsp;<a href="', $scripturl, '?action=findmember;input=username;quote=0;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" alt="', $txt['find_members'], '" /></a> <a href="', $scripturl, '?action=findmember;input=username;quote=0;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><span class="smalltext">', $txt['find_members'], '</span></a>
            </td>
        </tr>
        <tr>
            <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
        </tr>
        <tr>
            <td colspan="3">
            <div class="righttext">
                <input type="submit" value="', $txt['smfg_add_to_notification_list'], '" class="button_submit" />
                <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            </div>
            </td>
        </tr>
        </table>
        </form>
        <script type="text/javascript">
        var frmvalidator = new Validator("notify_members");                    
        frmvalidator.addValidation("username","req","' . $txt['smfg_val_enter_username_to_notify'] . '");
        </script>';

    echo '
            </div>
            <span class="botslice"><span></span></span>
        </div>';

    echo '
    </div>';
}

// Form for editing garage menu settings
function template_menu_settings()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div id="index_settings">';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_menu_settings'], '</h3>
    </div>';

    echo '
    <form action="' . $scripturl . '?action=admin;area=garagesettings;sa=updatemenu" method="post" accept-charset="ISO-8859-1">
    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagesettings;sa=menusettings" />';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
            <table border="0" cellspacing="0" cellpadding="4" width="100%">

                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_index_menu'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_index_menu" name="config[enable_index_menu]" value="1" ' . $context['enable_index_menu_check'] . ' class="check" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_browse_menu'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_browse_menu" name="config[enable_browse_menu]" value="1" ' . $context['enable_browse_menu_check'] . ' class="check" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_search_menu'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_search_menu" name="config[enable_search_menu]" value="1" ' . $context['enable_search_menu_check'] . ' class="check" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_insurance_review_menu'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_insurance_review_menu" name="config[enable_insurance_review_menu]" value="1" ' . $context['enable_insurance_review_menu_check'] . ' class="check" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_garage_review_menu'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_garage_review_menu" name="config[enable_garage_review_menu]" value="1" ' . $context['enable_garage_review_menu_check'] . ' class="check" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_shop_review_menu'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_shop_review_menu" name="config[enable_shop_review_menu]" value="1" ' . $context['enable_shop_review_menu_check'] . ' class="check" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_quartermile_menu'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_quartermile_menu" name="config[enable_quartermile_menu]" value="1" ' . $context['enable_quartermile_menu_check'] . ' class="check" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_dynorun_menu'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_dynorun_menu" name="config[enable_dynorun_menu]" value="1" ' . $context['enable_dynorun_menu_check'] . ' class="check" />
                    </td>
                </tr>

                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_laptime_menu'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_lap_menu" name="config[enable_lap_menu]" value="1" ' . $context['enable_lap_menu_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
            </table>
                          
            <div class="righttext">
                <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
                <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            </div>
        
            </div>
            <span class="botslice"><span></span></span>
        </div>
    </form>';

    echo '
    </div>';
}

// Form for editing garage index page settings
function template_index_settings()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div id="index_settings">';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_index_settings'], '</h3>
    </div>';

    echo '
    <form action="' . $scripturl . '?action=admin;area=garagesettings;sa=updateindex" id="index_settings" name="index_settings" method="post" accept-charset="ISO-8859-1">
    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagesettings;sa=indexsettings" />';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
            <table border="0" cellspacing="0" cellpadding="4" width="100%">
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=featured_vehicle" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_enable_featured_vehicle'] . ':</td>
                    <td width="50%">
                        <input type="radio" name="config[enable_featured_vehicle]" id="enable_featured_vehicle" value="0" ' . $context['disabled_check'] . ' class="radio" /> ' . $txt['smfg_disabled'] . '<input type="radio" name="config[enable_featured_vehicle]" value="1" ' . $context['from_id_check'] . ' class="radio" /> ' . $txt['smfg_from_vehicle_id'] . '<input type="radio" name="config[enable_featured_vehicle]" value="2" ' . $context['from_block_check'] . ' class="radio" /> ' . $txt['smfg_from_block'] . '<input type="radio" name="config[enable_featured_vehicle]" value="3" ' . $context['random_check'] . ' class="radio" /> ' . $txt['smfg_random'] . '
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=featured_vehicle_image_required" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_featured_vehicle_image_required'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="featured_vehicle_image_required" name="config[featured_vehicle_image_required]" value="1" ' . $context['featured_vehicle_image_required_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=featured_vehicle_id" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_featured_vehicle_id'] . ':</td>
                    <td width="50%">
                        <input id="featured_vehicle_id" type="text" size="3" maxlength="4" name="config[featured_vehicle_id]" value="' . $smfgSettings['featured_vehicle_id'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=featured_vehicle_block" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_featured_vehicle_from_block'] . ':</td>
                    <td width="50%">
                        <select name="config[featured_vehicle_from_block]" id="featured_vehicle_from_block">
                        <option value=""' . $context['fb_none'] . '>--------</option>
                        <option value="1"' . $context['fb_one'] . '>' . $txt['smfg_newest_vehicles'] . '</option>
                        <option value="2"' . $context['fb_two'] . '>' . $txt['smfg_last_updated_vehicles'] . '</option>
                        <option value="3"' . $context['fb_three'] . '>' . $txt['smfg_newest_mods'] . '</option>
                        <option value="4"' . $context['fb_four'] . '>' . $txt['smfg_last_updated_mods'] . '</option>
                        <option value="5"' . $context['fb_five'] . '>' . $txt['smfg_most_modified'] . '</option>
                        <option value="6"' . $context['fb_six'] . '>' . $txt['smfg_most_spent'] . '</option>
                        <option value="7"' . $context['fb_seven'] . '>' . $txt['smfg_most_viewed'] . '</option>
                        <option value="8"' . $context['fb_eight'] . '>' . $txt['smfg_latest_comments'] . '</option>
                        <option value="9"' . $context['fb_nine'] . '>' . $txt['smfg_top_qmile'] . '</option>
                        <option value="10"' . $context['fb_ten'] . '>' . $txt['smfg_top_dynorun'] . '</option>
                        <option value="11"' . $context['fb_eleven'] . '>' . $txt['smfg_top_rated'] . '</option>
                        <option value="12"' . $context['fb_twelve'] . '>' . $txt['smfg_top_laptimes'] . '</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=featured_vehicle_description" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_featured_vehicle_description'] . ':</td>
                    <td width="50%">
                        <input id="featured_vehicle_description" type="text" size="40" maxlength="100" name="config[featured_vehicle_description]" value="' . $smfgSettings['featured_vehicle_description'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_featured_vehicle_description_alignment'] . ':</td>
                    <td width="50%">
                        <select name="config[featured_vehicle_description_alignment]" id="featured_vehicle_description_alignment">
                        <option value="left"' . $context['left'] . '>' . $txt['smfg_left'] . '</option>
                        <option value="center"' . $context['center'] . '>' . $txt['smfg_center'] . '</option>
                        <option value="right"' . $context['right'] . '>' . $txt['smfg_right'] . '</option>
                        </select>
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=columns_index" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_columns_on_index'] . ':</td>
                    <td width="50%">
                        <select name="config[index_columns]" id="index_columns"><option value="1"' . $context['one'] . '>1</option><option value="2"' . $context['two'] . '>2</option></select>
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=black_management" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_block_management'] . ':</td>
                    <td>
                        <table width="100%" border="0" cellspacing="1" cellpadding="2" align="center">';
    $count = 0;
    while (isset($context['blocks'][$count]['id'])) {
        echo '
                            <tr class="tableRow">
                                <td align="left" nowrap="nowrap">' . $context['blocks'][$count]['position'] . '. ' . $context['blocks'][$count]['title'] . '</td>
                                <td align="center" nowrap="nowrap">';

        if ($context['blocks'][$count]['position'] == 1) {
            echo '
                                        <img src="' . $settings['default_images_url'] . '/icon_up_disabled.gif" alt="' . $txt['smfg_move_up'] . '" />
                                        <a href="' . $scripturl . '?action=admin;area=garagesettings;sa=block_move;direction=down;BID=' . $context['blocks'][$count]['id'] . ';position=' . $context['blocks'][$count]['position'] . ';sesc=' . $context['session_id'] . '" title="' . $txt['smfg_move_down'] . '"><img src="' . $settings['default_images_url'] . '/icon_down.gif" alt="' . $txt['smfg_move_down'] . '" /></a>';
        } else {
            if ($context['blocks'][$count]['position'] == $context['blocks']['total']) {
                echo '
                                        <a href="' . $scripturl . '?action=admin;area=garagesettings;sa=block_move;direction=up;BID=' . $context['blocks'][$count]['id'] . ';position=' . $context['blocks'][$count]['position'] . ';sesc=' . $context['session_id'] . '" title="' . $txt['smfg_move_up'] . '"><img src="' . $settings['default_images_url'] . '/icon_up.gif" alt="' . $txt['smfg_move_up'] . '" /></a>
                                        <img src="' . $settings['default_images_url'] . '/icon_down_disabled.gif" alt="' . $txt['smfg_move_down'] . '" />';
            } else {
                echo '
                                        <a href="' . $scripturl . '?action=admin;area=garagesettings;sa=block_move;direction=up;BID=' . $context['blocks'][$count]['id'] . ';position=' . $context['blocks'][$count]['position'] . ';sesc=' . $context['session_id'] . '" title="' . $txt['smfg_move_up'] . '"><img src="' . $settings['default_images_url'] . '/icon_up.gif" alt="' . $txt['smfg_move_down'] . '" /></a>
                                        <a href="' . $scripturl . '?action=admin;area=garagesettings;sa=block_move;direction=down;BID=' . $context['blocks'][$count]['id'] . ';position=' . $context['blocks'][$count]['position'] . ';sesc=' . $context['session_id'] . '" title="' . $txt['smfg_move_down'] . '"><img src="' . $settings['default_images_url'] . '/icon_down.gif" alt="' . $txt['smfg_move_up'] . '" /></a>';
            }
        }

        if ($context['blocks'][$count]['enabled'] == 0) {
            echo '
                                        <img src="' . $settings['default_images_url'] . '/icon_garage_disapprove_disabled.gif" alt="' . $txt['smfg_disable'] . '" title="' . $txt['smfg_disable'] . '" />
                                        <a href="' . $scripturl . '?action=admin;area=garagesettings;sa=block_enable;BID=' . $context['blocks'][$count]['id'] . ';sesc=' . $context['session_id'] . '"><img src="' . $settings['default_images_url'] . '/icon_garage_approve.gif" alt="Enable" title="Enable" /></a>';
        } else {
            echo '
                                        <a href="' . $scripturl . '?action=admin;area=garagesettings;sa=block_disable;BID=' . $context['blocks'][$count]['id'] . ';sesc=' . $context['session_id'] . '"><img src="' . $settings['default_images_url'] . '/icon_garage_disapprove.gif" alt="' . $txt['smfg_disable'] . '" title="' . $txt['smfg_disable'] . '" /></a>
                                        <img src="' . $settings['default_images_url'] . '/icon_garage_approve_disabled.gif" alt="Enable" title="Enable" />';
        }

        echo '&nbsp;<input id="' . $context['blocks'][$count]['input_title'] . '" type="text" size="2" maxlength="4" name="config[' . $context['blocks'][$count]['input_title'] . ']" value="' . $smfgSettings[$context['blocks'][$count]['input_title']] . '" />';

        echo '
                                </td>
                            </tr>';
        $count++;
    }
    echo '
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
            </table>
                          
            <div class="righttext">
                <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
                <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            </div>
        
            </div>
            <span class="botslice"><span></span></span>
        </div>
    </form>';

    echo '
    </div>';

    echo '
    <script type="text/javascript">
    var frmvalidator = new Validator("index_settings");
                                
    frmvalidator.addValidation("config[featured_vehicle_id]","numeric","' . $txt['smfg_val_featured_vehicle_restriction'] . '");
    
    frmvalidator.addValidation("config[newest_vehicle_limit]","req","' . $txt['smfg_val_enter_newest_vehicle_limit'] . '");
    frmvalidator.addValidation("config[newest_vehicle_limit]","numeric","' . $txt['smfg_val_newest_vehicle_limit_restriction'] . '");
    
    frmvalidator.addValidation("config[newest_modification_limit]","req","' . $txt['smfg_val_enter_newest_modification_limit'] . '");
    frmvalidator.addValidation("config[newest_modification_limit]","numeric","' . $txt['smfg_val_newest_modification_limit_restriction'] . '");
    
    frmvalidator.addValidation("config[most_modified_limit]","req","' . $txt['smfg_val_enter_most_modified_limit'] . '");
    frmvalidator.addValidation("config[most_modified_limit]","numeric","' . $txt['smfg_val_most_modified_limit_restriction'] . '");
    
    frmvalidator.addValidation("config[most_viewed_limit]","req","' . $txt['smfg_val_enter_most_viewed_limit'] . '");
    frmvalidator.addValidation("config[most_viewed_limit]","numeric","' . $txt['smfg_val_most_viewed_limit_restriction'] . '");
    
    frmvalidator.addValidation("config[top_quartermile_limit]","req","' . $txt['smfg_val_enter_top_quartermile_limit'] . '");
    frmvalidator.addValidation("config[top_quartermile_limit]","numeric","' . $txt['smfg_val_top_quartermile_restriction'] . '");
    
    frmvalidator.addValidation("config[top_rating_limit]","req","' . $txt['smfg_val_enter_top_rating_limit'] . '");
    frmvalidator.addValidation("config[top_rating_limit]","numeric","' . $txt['smfg_val_top_rating_limit_restriction'] . '");
    
    frmvalidator.addValidation("config[updated_vehicle_limit]","req","' . $txt['smfg_val_enter_updated_vehicle_limit'] . '");
    frmvalidator.addValidation("config[updated_vehicle_limit]","numeric","' . $txt['smfg_val_updated_vehicle_limit_restriction'] . '");
    
    frmvalidator.addValidation("config[updated_modification_limit]","req","' . $txt['smfg_val_enter_updated_modification_limit'] . '");
    frmvalidator.addValidation("config[updated_modification_limit]","numeric","' . $txt['smfg_val_updated_modification_limit_restriction'] . '");
    
    frmvalidator.addValidation("config[last_commented_limit]","req","' . $txt['smfg_val_enter_last_commented_limit'] . '");
    frmvalidator.addValidation("config[last_commented_limit]","numeric","' . $txt['smfg_val_last_commented_limit_restriction'] . '");
    
    frmvalidator.addValidation("config[most_spent_limit]","req","' . $txt['smfg_val_enter_most_spent_limit'] . '");
    frmvalidator.addValidation("config[most_spent_limit]","numeric","' . $txt['smfg_val_most_spent_limit_restriction'] . '");
    
    frmvalidator.addValidation("config[top_dynorun_limit]","req","' . $txt['smfg_val_enter_top_dynorun_limit'] . '");
    frmvalidator.addValidation("config[top_dynorun_limit]","numeric","' . $txt['smfg_val_top_dynorun_limit_restriction'] . '");
    
    frmvalidator.addValidation("config[top_lap_limit]","req","' . $txt['smfg_val_enter_top_lap_limit'] . '");
    frmvalidator.addValidation("config[top_lap_limit]","numeric","' . $txt['smfg_val_top_lap_limit_restriction'] . '");
    
    </script>';
}

// Form for editing garage image settings
function template_image_settings()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div id="image_settings">';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_image_settings'], '</h3>
    </div>';

    echo '
    <form action="' . $scripturl . '?action=admin;area=garagesettings;sa=updateimage" id="image_settings" name="image_settings" method="post" accept-charset="ISO-8859-1">
    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagesettings;sa=imagesettings" />
    <input type="hidden" name="redirecturl2" value="' . $scripturl . '?action=admin;area=garagemanagement;sa=tools;regen=1" />
    <input type="hidden" name="rebuild" value="0" />';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
            <table border="0" cellspacing="0" cellpadding="4" width="100%">
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_vehicle_images'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_vehicle_images" name="config[enable_vehicle_images]" value="1" ' . $context['enable_vehicle_images_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_modification_images'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_modification_images" name="config[enable_modification_images]" value="1" ' . $context['enable_modification_images_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_quartermile_images'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_quartermile_images" name="config[enable_quartermile_images]" value="1" ' . $context['enable_quartermile_images_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_dynorun_images'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_dynorun_images" name="config[enable_dynorun_images]" value="1" ' . $context['enable_dynorun_images_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_lap_images'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_lap_images" name="config[enable_lap_images]" value="1" ' . $context['enable_lap_images_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_remote_images'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_remote_images" name="config[enable_remote_images]" value="1" ' . $context['enable_remote_images_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=store_remote_locally" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_store_remote_images_locally'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="store_remote_images_locally" name="config[store_remote_images_locally]" value="1" ' . $context['store_remote_images_locally_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_remote_image_timeout'] . ':</td>
                    <td width="50%">
                        <input id="remote_timeout" type="text" size="3" maxlength="4" name="config[remote_timeout]" value="' . $smfgSettings['remote_timeout'] . '" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=enable_lightbox" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_enable_lightbox'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_lightbox" name="config[enable_lightbox]" value="1" ' . $context['enable_lightbox_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=gallery_limit" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_gallery_limit'] . ':</td>
                    <td width="50%">
                        <input id="gallery_limit" type="text" size="3" maxlength="4" name="config[gallery_limit]" value="' . $smfgSettings['gallery_limit'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_maximum_fsize'] . ':</td>
                    <td width="50%">
                        <input id="max_image_kbytes" type="text" size="3" maxlength="4" name="config[max_image_kbytes]" value="' . $smfgSettings['max_image_kbytes'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_maximum_resolution'] . ':</td>
                    <td width="50%">
                        <input id="max_image_resolution" type="text" size="3" maxlength="4" name="config[max_image_resolution]" value="' . $smfgSettings['max_image_resolution'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_thumbnail_resolution'] . ':</td>
                    <td width="50%">
                        <input id="thumbnail_resolution" type="text" size="3" maxlength="4" name="config[thumbnail_resolution]" value="' . $smfgSettings['thumbnail_resolution'] . '" onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=upload_directory" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_upload_directory'] . ':</td>
                    <td width="50%">
                        <input id="upload_directory" type="text" size="20" name="config[upload_directory]" value="' . $smfgSettings['upload_directory'] . '" onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                    </td>
                </tr>
                    <td valign="top" width="16"></td>
                    <td valign="top"></td>
                    <td valign="top">
                        ' . $context['write_output'] . '
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_watermark'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_watermark" name="config[enable_watermark]" value="1" ' . $context['enable_watermark_check'] . ' class="check" onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_gcard_watermark'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="gcard_watermark" name="config[gcard_watermark]" value="1" ' . $context['gcard_watermark_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_watermark_thumb'] . ':</td>
                    <td width="50%">
                        <select name="config[enable_watermark_thumb]" onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }">
                            <option value="0" ' . $context['enable_watermark_thumb_off'] . '>' . $txt['smfg_off'] . '</option>
                            <option value="1" ' . $context['enable_watermark_thumb_on'] . '>' . $txt['smfg_onorig'] . '</option>
                            <option value="2" ' . $context['enable_watermark_thumb_onsized'] . '>' . $txt['smfg_onsized'] . '</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_watermark_opacity'] . ':</td>
                    <td width="50%">
                        <input id="watermark_opacity" type="text" size="3" maxlength="3" name="config[watermark_opacity]" value="' . $smfgSettings['watermark_opacity'] . '" onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_watermark_position'] . ':</td>
                    <td width="50%">
                        <table border="0" cellspacing="0" cellpadding="0" width="1" height="1">
                            <tr>
                                <td>
                                    <input type="radio" name="config[watermark_position]" value="0" ' . $context['watermark_position_0'] . ' onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                                </td>
                                <td>
                                    &#151;
                                </td>
                                <td>
                                    <input type="radio" name="config[watermark_position]" value="1" ' . $context['watermark_position_1'] . ' onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                                </td>
                                <td>
                                    &#151;
                                </td>
                                <td>
                                    <input type="radio" name="config[watermark_position]" value="2" ' . $context['watermark_position_2'] . ' onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: center;">
                                    &#124;
                                </td>
                                <td>
                                </td>
                                <td style="text-align: center;">
                                    &#124;
                                </td>
                                <td>
                                </td>
                                <td style="text-align: center;">
                                    &#124;
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="radio" name="config[watermark_position]" value="3" ' . $context['watermark_position_3'] . ' onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                                </td>
                                <td>
                                    &#151;
                                </td>
                                <td>
                                    <input type="radio" name="config[watermark_position]" value="4" ' . $context['watermark_position_4'] . ' onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                                </td>
                                <td>
                                    &#151;
                                </td>
                                <td>
                                    <input type="radio" name="config[watermark_position]" value="5" ' . $context['watermark_position_5'] . ' onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: center;">
                                    &#124;
                                </td>
                                <td>
                                </td>
                                <td style="text-align: center;">
                                    &#124;
                                </td>
                                <td>
                                </td>
                                <td style="text-align: center;">
                                    &#124;
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="radio" name="config[watermark_position]" value="6" ' . $context['watermark_position_6'] . ' onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                                </td>
                                <td>
                                    &#151;
                                </td>
                                <td>
                                    <input type="radio" name="config[watermark_position]" value="7" ' . $context['watermark_position_7'] . ' onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                                </td>
                                <td>
                                    &#151;
                                </td>
                                <td>
                                    <input type="radio" name="config[watermark_position]" value="8" ' . $context['watermark_position_8'] . ' onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>';
    /* We are going to leave this out for now, it may be added later
       Also don't use hardcoded field names :) "Watermark Type"
    echo '
    <tr>
        <td valign="top" width="16"><a href="'.$scripturl.'?action=helpadmin;help=" onclick="return reqWin(this.href);" class="help"><img src="'. $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
        <td valign="top">Watermark Type:</td>
        <td width="50%">
            <select name="config[watermark_type]" id="watermark_type" onChange="if(confirm(\''.$txt['smfg_rebuild_required'].'\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }">
            <option value="permanent"'.$context['perm'].'>Permanent</option>
            <option value="non_permanent"'.$context['nonperm'].'>Non Permanent</option>
            </select>
        </td>
    </tr>';
    */
    echo '
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=watermark_source" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_watermark_sourcefile'] . ':</td>
                    <td width="50%">
                        <input id="watermark_source" type="text" size="30" maxlength="60" name="config[watermark_source]" value="' . $smfgSettings['watermark_source'] . '" onChange="if(confirm(\'' . $txt['smfg_rebuild_required'] . '\')) { setforminputvalue(\'image_settings\', \'rebuild\', \'1\'); } else { return false; }" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top"></td>
                    <td width="50%">
                        ' . $context['exec_output'] . '
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16">
';
    if (!$context['im_convert']) {
        echo '<img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="" title="" />';
    }

    echo '
                    </td>
                    <td valign="top">' . $txt['smfg_im_convert'] . ':</td>
                    <td width="50%">
';
    if (!$context['im_convert']) {
        if (!empty($context['exec_disabled'])) {
            echo $txt['smfg_exec_disabled_warning'];
        } else {
            echo $txt['smfg_im_convert_warning'];
        }
    } else {
        echo $context['im_convert'];
    }

    echo '
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16">
';
    if (!$context['im_composite']) {
        echo '<img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="" title="" />';
    }

    echo '
                    </td>
                    <td valign="top">' . $txt['smfg_im_composite'] . ':</td>
                    <td width="50%">
';
    if (!$context['im_composite']) {
        if (!empty($context['exec_disabled'])) {
            echo $txt['smfg_exec_disabled_warning'];
        } else {
            echo $txt['smfg_im_composite_warning'];
        }
    } else {
        echo $context['im_composite'];
    }

    echo '
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16">
';
    if (!$context['im_gd']) {
        echo '<img src="' . $settings['default_images_url'] . '/garage_delete.gif" alt="" title="" />';
    }

    echo '
                    </td>
                    <td valign="top">' . $txt['smfg_im_gd'] . ':</td>
                    <td width="50%">
';
    if (!$context['im_gd']) {
        echo $txt['smfg_im_gd_warning'];
    } else {
        echo $context['im_gd'];
    }

    echo '
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_image_processor_select'] . ':</td>
                    <td width="50%">
                        <select name="config[image_processor]">
                            <option value="0" ' . $context['image_processor_none'] . '>' . $txt['smfg_image_processor_none'] . '</option>
                            <option value="1" ' . $context['image_processor_im'] . '>' . $txt['smfg_image_processor_im'] . '</option>
                            <option value="2" ' . $context['image_processor_gd'] . '>' . $txt['smfg_image_processor_gd'] . '</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=IM_convert" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_IM_convert_dir'] . ':</td>
                    <td width="50%">
                        <input id="im_convert" type="text" size="20" maxlength="60" name="config[im_convert]" value="' . $smfgSettings['im_convert'] . '" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=IM_composite" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_IM_composite_dir'] . ':</td>
                    <td width="50%">
                        <input id="im_composite" type="text" size="20" maxlength="60" name="config[im_composite]" value="' . $smfgSettings['im_composite'] . '" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
            </table>
                          
            <div class="righttext">
                <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
                <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            </div>
        
            </div>
            <span class="botslice"><span></span></span>
        </div>
    </form>';

    echo '
    </div>';

    echo '
    <script type="text/javascript">
    var frmvalidator = new Validator("image_settings");
                                
    frmvalidator.addValidation("config[remote_timeout]","req","' . $txt['smfg_val_enter_remote_timout'] . '");
    frmvalidator.addValidation("config[remote_timeout]","numeric","' . $txt['smfg_val_remote_timeout_restriction'] . '");
                                
    frmvalidator.addValidation("config[gallery_limit]","req","' . $txt['smfg_val_enter_gallery_limit'] . '");
    frmvalidator.addValidation("config[gallery_limit]","numeric","' . $txt['smfg_val_gallery_limit_restriction'] . '");
                                
    frmvalidator.addValidation("config[max_image_kbytes]","req","' . $txt['smfg_val_enter_max_image_kbytes'] . '");
    frmvalidator.addValidation("config[max_image_kbytes]","numeric","' . $txt['smfg_val_max_image_kbytes_restriction'] . '");
                                
    frmvalidator.addValidation("config[max_image_resolution]","req","' . $txt['smfg_val_enter_max_image_resolution'] . '");
    frmvalidator.addValidation("config[max_image_resolution]","numeric","' . $txt['smfg_val_max_image_resolution_restriction'] . '");
                                
    frmvalidator.addValidation("config[thumbnail_resolution]","req","' . $txt['smfg_val_enter_thumbnail_resolution'] . '");
    frmvalidator.addValidation("config[thumbnail_resolution]","numeric","' . $txt['smfg_val_thumbnail_resolution_restriction'] . '");
                                
    frmvalidator.addValidation("config[upload_directory]","req","' . $txt['smfg_val_enter_upload_directory'] . '");
    frmvalidator.addValidation("config[watermark_source]","req","' . $txt['smfg_val_enter_watermark_source'] . '");
    frmvalidator.addValidation("config[im_convert]","req","' . $txt['smfg_val_enter_im_convert'] . '");
    frmvalidator.addValidation("config[im_composite]","req","' . $txt['smfg_val_enter_im_composite'] . '");
    
    </script>';
}

// Form for editing garage video settings
function template_video_settings()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div id="video_settings">';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_video_settings'], '</h3>
    </div>';

    echo '
    <form action="' . $scripturl . '?action=admin;area=garagesettings;sa=updatevideo" id="video_settings" name="video_settings" method="post" accept-charset="ISO-8859-1">
    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagesettings;sa=videosettings" />';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
            <table border="0" cellspacing="0" cellpadding="4" width="100%">

                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_vehicle_videos'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_vehicle_video" name="config[enable_vehicle_video]" value="1" ' . $context['enable_vehicle_video_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_modification_videos'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_modification_video" name="config[enable_modification_video]" value="1" ' . $context['enable_modification_video_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_quartermile_videos'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_quartermile_video" name="config[enable_quartermile_video]" value="1" ' . $context['enable_quartermile_video_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_dynorun_videos'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_dynorun_video" name="config[enable_dynorun_video]" value="1" ' . $context['enable_dynorun_video_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_lap_videos'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_laptime_video" name="config[enable_laptime_video]" value="1" ' . $context['enable_laptime_video_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"><a href="' . $scripturl . '?action=helpadmin;help=gallery_limit_video" onclick="return reqWin(this.href);" class="help"><img src="' . $settings['actual_images_url'] . '/helptopics.gif" alt="Help" border="0" align="top" /></a></td>
                    <td valign="top">' . $txt['smfg_gallery_limit'] . ':</td>
                    <td width="50%">
                        <input id="gallery_limit_video" type="text" size="3" maxlength="4" name="config[gallery_limit_video]" value="' . $smfgSettings['gallery_limit_video'] . '" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
            </table>
                          
            <div class="righttext">
                <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
                <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            </div>
        
            </div>
            <span class="botslice"><span></span></span>
        </div>
    </form>';

    echo '
    </div>';

    echo '
    <script type="text/javascript">
    var frmvalidator = new Validator("video_settings");
                                
    frmvalidator.addValidation("config[gallery_limit_video]","req","' . $txt['smfg_val_enter_gallery_limit'] . '");
    frmvalidator.addValidation("config[gallery_limit_video]","numeric","' . $txt['smfg_val_gallery_limit_restriction'] . '");
    
    </script>';
}

// Form for editing garage module settings
function template_module_settings()
{
    global $smfgSettings, $context, $settings, $options, $scripturl, $txt;

    echo '
    <div id="image_settings">';

    echo '
    <div class="title_bar">
        <h3 class="titlebg">', $txt['smfg_module_settings'], '</h3>
    </div>';

    echo '
    <form action="' . $scripturl . '?action=admin;area=garagesettings;sa=updatemodule" method="post" accept-charset="ISO-8859-1">
    <input type="hidden" name="redirecturl" value="' . $scripturl . '?action=admin;area=garagesettings;sa=modulesettings" />';

    echo '            
    <div class="windowbg">
        <span class="topslice"><span></span></span>
            <div class="content">';

    echo '
            <table border="0" cellspacing="0" cellpadding="4" width="100%">
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_modifications'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_modification" name="config[enable_modification]" value="1" ' . $context['enable_modification_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_modification_approval'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_modification_approval" name="config[enable_modification_approval]" value="1" ' . $context['enable_modification_approval_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_quartermiles'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_quartermile" name="config[enable_quartermile]" value="1" ' . $context['enable_quartermile_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_quartermile_approval'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_quartermile_approval" name="config[enable_quartermile_approval]" value="1" ' . $context['enable_quartermile_approval_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_quartermile_image_required'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_quartermile_image_required" name="config[enable_quartermile_image_required]" value="1" ' . $context['enable_quartermile_image_required_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_dynoruns'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_dynorun" name="config[enable_dynorun]" value="1" ' . $context['enable_dynorun_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_dynorun_approval'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_dynorun_approval" name="config[enable_dynorun_approval]" value="1" ' . $context['enable_dynorun_approval_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_dynorun_image_required'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_dynorun_image_required" name="config[enable_dynorun_image_required]" value="1" ' . $context['enable_dynorun_image_required_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_laptimes'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_laptimes" name="config[enable_laptimes]" value="1" ' . $context['enable_laptimes_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_lap_approval'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_lap_approval" name="config[enable_lap_approval]" value="1" ' . $context['enable_lap_approval_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_lap_image_required'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_lap_image_required" name="config[enable_lap_image_required]" value="1" ' . $context['enable_lap_image_required_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_user_track_submission'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_user_add_track" name="config[enable_user_add_track]" value="1" ' . $context['enable_user_add_track_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_track_approval'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_track_approval" name="config[enable_track_approval]" value="1" ' . $context['enable_track_approval_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_insurance'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_insurance" name="config[enable_insurance]" value="1" ' . $context['enable_insurance_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_service_history'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_service" name="config[enable_service]" value="1" ' . $context['enable_service_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_blogs'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_blogs" name="config[enable_blogs]" value="1" ' . $context['enable_blogs_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_blog_bbcode'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_blogs_bbcode" name="config[enable_blogs_bbcode]" value="1" ' . $context['enable_blogs_bbcode_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                        <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_guestbooks'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_guestbooks" name="config[enable_guestbooks]" value="1" ' . $context['enable_guestbooks_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_guestbook_bbcode'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_guestbooks_bbcode" name="config[enable_guestbooks_bbcode]" value="1" ' . $context['enable_guestbooks_bbcode_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="16"></td>
                    <td valign="top">' . $txt['smfg_enable_comment_approval'] . ':</td>
                    <td width="50%">
                        <input type="checkbox" id="enable_guestbooks_comment_approval" name="config[enable_guestbooks_comment_approval]" value="1" ' . $context['enable_guestbooks_comment_approval_check'] . ' class="check" />
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr size="1" width="100%" class="hrcolor" /></td>
                </tr>
            </table>
                          
            <div class="righttext">
                <input type="submit" value="', $txt['smfg_save'], '" class="button_submit" />
                <input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
            </div>
        
            </div>
            <span class="botslice"><span></span></span>
        </div>
    </form>';

    echo '
    </div>';
}

// This is a blank template for all forwarding actions
function template_blank()
{
    // Nothing to see here folks
}
