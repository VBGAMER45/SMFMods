<?php
/************************************************************************************
* E Arcade 3.0 (http://www.smfhacks.com)                                            *
* Copyright (C) 2014  http://www.smfhacks.com                                       *
* Copyright (C) 2007  Eric Lawson (http://www.ericsworld.eu)                        *
* based on the original SMFArcade mod by Nico - http://www.smfarcade.info/          *                                                                           *
*************************************************************************************
* This program is free software; you can redistribute it and/or modify         *
* it under the terms of the GNU General Public License as published by         *
* the Free Software Foundation; either version 2 of the License, or            *
* (at your option) any later version.                                          *
*                                                                              *
* This program is distributed in the hope that it will be useful,              *
* but WITHOUT ANY WARRANTY; without even the implied warranty of               *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
* GNU General Public License for more details.                                 *
*                                                                              *
* You should have received a copy of the GNU General Public License            *
* along with this program; if not, write to the Free Software                  *
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA *
********************************************************************************
ManageArcade.template.php

Template file for arcade administration
*****************************************************************************/

function template_arcadeadmin_category()
{
    // Category editor

    global $scripturl, $txt, $context, $settings;

        echo '
    <form name="category" action="', $scripturl, '?action=admin;area=managearcade;sa=savecats" method="post">
        <script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
        var swap = [];

        function swapCategory(id)
        {
            var id2 = id + "_div";
            var id3 = id + "_img";

            if (swap[id2] == undefined)
                swap[id2] = false;

            if (swap[id2] == false)
                document.getElementById(id2).style.display = "";
            else
                document.getElementById(id2).style.display = "none";

            swap[id2] = !swap[id2];
            document.getElementById(id3).src = smf_images_url + (swap[id2] ? "/upshrink.png" : "/upshrink2.png");

            return swap[id2];
        }

        function addNewCategory()
        {
            setOuterHTML(document.getElementById("newcategory"), \'<br /><input type="text" name="new[]" size="50" /><span id="newcategory"></span>\');

            return true;
        }

        // ]]></script>
        <div class="cat_bar">
                <h3 class="catbg">
                ', $txt['arcade_categories'], '
                </h3>
        </div>
        <table border="0" cellspacing="0" cellpadding="4" width="100%" class="tborder" align="center">
        ';

        foreach ($context['arcade']['category'] as $category)
        {
            echo '
            <tr class="windowbg2">
                <th width="10%" align="right" valign="top" style="margin-top: 5px;">
                    <a id="cat', $category['id'], '_link" href="#" onclick="swapCategory(\'cat', $category['id'], '\'); return false;">
                        <img id="cat', $category['id'], '_img" src="', $settings['images_url'], '/upshrink2.png" alt="+" />
                    </a>
                </th>
                <td valign="top">
                    <input type="text" name="category[', $category['id'], '][name]" value="', $category['name'], '" size="40" />
                    ' . $txt['arcade_txt_icon'] . ' <input type="text" name="category[', $category['id'], '][icon]" value="', $category['icon'], '" size="20" />
                    ' . $txt['arcade_txt_order'] .' <input type="text" name="category[', $category['id'], '][order]" value="', $category['order'], '" size="10" />
                    <input id="cat', $category['id'], '" type="checkbox" name="category[', $category['id'], '][delete]" value="', $category['id'], '" style="check" /><label for="cat', $category['id'], '">', $txt['arcade_delete'], '</label>
                    <div id="cat', $category['id'], '_div" style="display: none;">
                        <div style="float: left; width: 50%;">
                            <fieldset>
                                <legend>', $txt['arcade_category_permission_allowed'], '</legend>';

            foreach($context['groups'] as $group)
                    echo '
                                <input id="group', $group['id'], '" type="checkbox" class="check" name="category[', $category['id'], '][member_groups][]" value="', $group['id'], '" ', in_array($group['id'], $category['member_groups']) ? 'checked="checked"' : '' , '/> <span', $group['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['groups_post_group'] . '"' : '' ,'><label for="group', $group['id'], '">', $group['name'], '</label></span><br />';

            echo '
                                <input onclick="invertAll(this, this.form, \'category[', $category['id'], '][member_groups][]\');" type="checkbox" style="check" /> <i>' . $txt['arcade_checkall'] . '</i>
                            </fieldset>
                        </div>

                        <div style="float: left; width: 50%;">
                            <fieldset>
                                <legend>', $txt['arcade_settings'], '</legend>
                                <input id="default" name="category[', $category['id'], '][default]" value="1" type="checkbox" style="check" /> <lablel for="default">', $txt['arcade_make_default'], '</label><br />
                            </fieldset>
                        </div>
                    </div>
                </td>
            </tr>';
        }

        echo '
            <tr class="windowbg2">
                <th width="10%" align="right" valign="top">', $txt['arcade_new'] ,'</th>
                <td>
                    <input type="text" name="new[]" size="50" />
                    <span id="newcategory"></span> (<a href="#" onclick="addNewCategory(); return false;">', $txt['arcade_more'], '</a>)
                </td>
            </tr>
            <tr class="windowbg2">
                <td align="right" colspan="3">
                    <input type="submit" name="save_settings" value="', $txt['arcade_save_category'], '" />
                </td>
            </tr>
        </table>

        <input type="hidden" name="sc" value="', $context['session_id'], '" />
    </form>';
}

function template_arcadeadmin_maintenance()
{
    global $scripturl, $txt, $arcSettings, $context, $settings;

        echo '
        <div class="cat_bar">
                <h3 class="catbg">
                ', $txt['arcade_maintenance'], '
                </h3>
        </div>
    <table border="0" cellspacing="0" cellpadding="4" align="center" width="100%" class="tborder">
            <tr class="windowbg2">
                <td width="75%" align="left">',$txt['arcade_maintenance_cache'],'</td>
                <td align="left">
                    <a href="', $scripturl, '?action=admin;area=managearcade;sa=clear">',$txt['arcade_cache_clear'],'</a>
                </td>
            </tr>
			<tr class="windowbg2">
               <td colspan="2">
                   <hr />
                </td>
            </tr>
            <tr class="windowbg2">
                <td width="75%" align="left">',$txt['arcade_maintenance_topics'],'</td>
                <td align="left">
                    <a href="', $scripturl, '?action=admin;area=managearcade;sa=settopics">',$txt['arcade_set_topics'],'</a>
                </td>
            </tr>
			<tr class="windowbg2">
               <td colspan="2">
                   <hr />
                </td>
            </tr>
			<tr class="windowbg2">
                <td width="75%" align="left">',$txt['arcade_maintenance_fix_scores'],'</td>
                <td align="left">
                    <a href="', $scripturl, '?action=admin;area=managearcade;sa=fix">',$txt['arcade_fix_scores'],'</a>
                </td>
            </tr>
			<tr class="windowbg2">
               <td colspan="2">
                   <hr />
                </td>
            </tr>
			<tr class="windowbg2">
               <td colspan="2">
                   <hr />
                </td>
            </tr>
        </table>

       ';
}

function template_arcadeadmin_settings()
{
    global $scripturl, $txt, $arcSettings, $context, $settings;

    // Settings page

        echo '
    <form action="', $scripturl, '?action=admin;area=managearcade;sa=save" method="post">

           <div class="cat_bar">
                <h3 class="catbg">
                ', $txt['arcade_settings'], '
                </h3>
            </div>
        <table border="0" cellspacing="0" cellpadding="4" align="center" width="100%" class="tborder">
             <tr class="windowbg2">
                <td colspan="2" align="center" class="warn_moderate">
									<i><b>',$txt['arcade_general_settings'],'</b></i>
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="enabled">', $txt['arcade_enabled'], '</label></th>
                <td>
                    <input type="checkbox" name="enabled" id="enabled" value="1" style="check" ', $arcSettings['arcadeEnabled'] ? 'checked="checked"' : '' ,'/><br />
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="gameFrontPageBox">', $txt['arcade_gameFrontPage'], '</label></th>
                <td>
                    <select name="gameFrontPage" id="gameFrontPageBox">
                        <option value="0" ', $arcSettings['gameFrontPage'] == 0 ? 'selected="selected"' : '', '>', $txt['arcade_gameFrontPage1'] ,'</option>
                        <option value="1" ', $arcSettings['gameFrontPage'] == 1 ? 'selected="selected"' : '', '>', $txt['arcade_gameFrontPage2'] ,'</option>
                        <option value="2" ', $arcSettings['gameFrontPage'] == 2 ? 'selected="selected"' : '', '>', $txt['arcade_gameFrontPage3'] ,'</option>
                        <option value="3" ', $arcSettings['gameFrontPage'] == 3 ? 'selected="selected"' : '', '>', $txt['arcade_gameFrontPage4'] ,'</option>
                        <option value="4" ', $arcSettings['gameFrontPage'] == 4 ? 'selected="selected"' : '', '>', $txt['arcade_gameFrontPage5'] ,'</option>
                        <option value="5" ', $arcSettings['gameFrontPage'] == 5 ? 'selected="selected"' : '', '>', $txt['arcade_gameFrontPage6'] ,'</option>
                    </select>
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="gamesPerPage">', $txt['arcade_games_page'] ,'</label></th>
                <td>
                    <input type="text" name="gamesPerPage" id="gamesPerPage" value="', $arcSettings['gamesPerPage'], '" /><br />
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="gamesPerPageAdmin">', $txt['arcade_games_page_admin'] ,'</label></th>
                <td>
                    <input type="text" name="gamesPerPageAdmin" id="gamesPerPageAdmin" value="', $arcSettings['gamesPerPageAdmin'], '" /><br />
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="scoresPerPage">', $txt['arcade_scores_page'], '</label></th>
                <td>
                    <input type="text" name="scoresPerPage" id="scoresPerPage" value="', $arcSettings['scoresPerPage'], '" /><br />
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcadeCheckLevel">', $txt['arcade_check_level'], '</label></th>
                <td>
                    <select name="arcadeCheckLevel" id="arcadeCheckLevel">
                        <option value="0" ', $arcSettings['arcadeCheckLevel'] == 0 ? 'selected="selected"' : '', '>', $txt['arcade_check_level0'] ,'</option>
                        <option value="1" ', $arcSettings['arcadeCheckLevel'] == 1 ? 'selected="selected"' : '', '>', $txt['arcade_check_level1'] ,'</option>
                        <option value="2" ', $arcSettings['arcadeCheckLevel'] == 2 ? 'selected="selected"' : '', '>', $txt['arcade_check_level2'] ,'</option>
                    </select>
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="gamesDirectory">', $txt['arcade_games_directory'] ,'</label></th>
                <td>
                    <input type="text" name="gamesDirectory" id="gamesDirectory" value="', $arcSettings['gamesDirectory'], '" style="width: 95%;" /><br />
                </td>
            </tr>
             <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_enable_cache">', $txt['arcade_enable_cache'], '</label></th>
                <td>
                    <input type="checkbox" name="enable_arcade_cache" id="arcade_enable_cache" value="1" style="check" ', $arcSettings['enable_arcade_cache'] ? 'checked="checked"' : '' ,'/><br />
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="cacheDirectory">', $txt['arcade_cache_directory'] ,'</label></th>
                <td>
                    <input type="text" name="cacheDirectory" id="cacheDirectory" value="', $arcSettings['cacheDirectory'], '" style="width: 95%;" /><br />
                </td>
            </tr>

            <tr class="windowbg2">
                <th width="50%" align="right"><label for="gamesUrl">', $txt['arcade_games_url'], '</label></th>
                <td>
                    <input type="text" name="gamesUrl" id="gamesUrl" value="', $arcSettings['gamesUrl'], '" style="width: 95%;" /><br />
                </td>
            </tr>

            <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcadeMaxScores">', $txt['arcade_max_scores'], '</label></th>
                <td>
                    <input type="text" name="arcadeMaxScores" id="arcadeMaxScores" value="', $arcSettings['arcadeMaxScores'], '" /><br />
                    ', $txt['arcade_max_scores_help'], '
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_enable_pm">', $txt['arcade_enable_pm'], '</label></th>
                <td>
                    <input type="checkbox" name="arcadePMsystem" id="arcade_enable_pm" value="1" style="check" ', $arcSettings['arcadePMsystem'] ? 'checked="checked"' : '' ,'/><br />
                </td>
            </tr>
             <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_active_user">', $txt['arcade_active_user'], '</label></th>
                <td>
                    <input type="checkbox" name="arcade_active_user" id="arcade_active_user" value="1" style="check" ', $arcSettings['arcade_active_user'] ? 'checked="checked"' : '' ,'/><br />
                </td>
            </tr>
             <tr class="windowbg2">
                <td colspan="2" align="center" class="warn_moderate">
									<i><b>',$txt['arcade_permissions'],'</b></i>
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_permission_mode">', $txt['arcade_permission_mode'], '</label></th>
                <td>
                    <select name="arcadePermissionMode" id="arcade_permission_mode">
                        <option value="0" ', $arcSettings['arcadePermissionMode'] == 0 ? 'selected="selected"' : '', '>', $txt['arcade_permission_mode_none'] ,'</option>
                        <option value="1" ', $arcSettings['arcadePermissionMode'] == 1 ? 'selected="selected"' : '', '>', $txt['arcade_permission_mode_category'] ,'</option>
                        <option value="2" ', $arcSettings['arcadePermissionMode'] == 2 ? 'selected="selected"' : '', '>', $txt['arcade_permission_mode_game'] ,'</option>
                        <option value="3" ', $arcSettings['arcadePermissionMode'] == 3 ? 'selected="selected"' : '', '>', $txt['arcade_permission_mode_and_both'] ,'</option>
                        <option value="4" ', $arcSettings['arcadePermissionMode'] == 4 ? 'selected="selected"' : '', '>', $txt['arcade_permission_mode_or_both'] ,'</option>
                    </select>
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_enable_post">', $txt['arcade_enable_post'], '</label></th>
                <td>
                    <input type="checkbox" name="arcadePostPermission" id="arcade_enable_post" value="1" style="check" ', $arcSettings['arcadePostPermission'] ? 'checked="checked"' : '' ,'/><br />
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_posts_cumulative">', $txt['arcade_posts_cumulative'] ,'</label></th>
                <td>
                    <input type="text" name="arcadePostsPlay" id="arcade_posts_cumulative" value="', $arcSettings['arcadePostsPlay'], '"  /><br />
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_posts_perday">', $txt['arcade_posts_perday'], '</label></th>
                <td>
                    <input type="text" name="arcadePostsPlayPerDay" id="arcade_posts_perday" value="', $arcSettings['arcadePostsPlayPerDay'], '" /><br />
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_posts_perday_days">', $txt['arcade_posts_perday_days'], '</label></th>
                <td>
                    <input type="text" name="arcadePostsPlayDays" id="arcade_posts_perday_days" value="', $arcSettings['arcadePostsPlayDays'], '"  /><br />
                </td>
            </tr>
             <tr class="windowbg2">
                <td colspan="2" align="center" class="warn_moderate">
									<i><b>',$txt['arcade_show_champ_icons'],'</b></i>
                </td>
            </tr>
                        <tr class="windowbg2">
                            <th width="50%" align="right"><label for="arcade_posts_perday">',$txt['arcade_champions_in_post'],':</td>
                            <td><input type="text" name="arcade_champions_in_post" value="', $arcSettings['arcade_champions_in_post'], '" /></td>
                        </tr>
                        <tr class="windowbg2">
                            <th width="50%" align="right"><label for="arcade_champions_sigs">',$txt['arcade_champions_sigs'],':</td>
                            <td>
                            ',$txt['arcade_champions_off'],'<input type="radio" name="arcade_champion_sig" value="2"';if ($arcSettings['arcade_champion_sig'] == 2){echo" checked";} echo ' />
                            ',$txt['arcade_champions_icons'],'<input type="radio" name="arcade_champion_sig" value="1"';if ($arcSettings['arcade_champion_sig'] == 1){echo" checked";} echo ' />
                            ',$txt['arcade_champions_cups'],'<input type="radio" name="arcade_champion_sig" value="0"';if ($arcSettings['arcade_champion_sig'] == 0){echo" checked";} echo ' />
                            </td>
                        </tr>
                        <tr class="windowbg2">
                            <th width="50%" align="right"><label for="arcade_champions_pp">',$txt['arcade_champions_pp'],':</td>
                            <td>
                            ',$txt['arcade_champions_off'],'<input type="radio" name="arcade_champion_pp" value="2"';if ($arcSettings['arcade_champion_pp'] == 2){echo" checked";} echo ' />
                            ',$txt['arcade_champions_icons'],'<input type="radio" name="arcade_champion_pp" value="1"';if ($arcSettings['arcade_champion_pp'] == 1){echo" checked";} echo ' />
                            ',$txt['arcade_champions_cups'],'<input type="radio" name="arcade_champion_pp" value="0"';if ($arcSettings['arcade_champion_pp'] == 0){echo" checked";} echo ' />
                            </td>
                        </tr>
               <tr class="windowbg2">
                <td colspan="2" align="center" class="warn_moderate">
									<i><b>',$txt['arcade_arcade_topics'],'</b></i>
                </td>
            </tr>


              <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_post_topic">', $txt['arcade_post_topic'], '</label></th>
                <td>
                    <select name="arcadePostTopic" id="arcade_post_topic">
                        <option value="0" ', $arcSettings['arcadePostTopic'] == 0 ? 'selected="selected"' : '', '>' . $txt['arcade_txt_off'] . '</option>';
                        foreach($context['arcade_boards'] as $boards)
                        {
                            echo'<option value="',$boards['id_board'],'" ', $arcSettings['arcadePostTopic'] == $boards['id_board'] ? 'selected="selected"' : '', '>', $boards['name'] ,'</option>';
                        }
                    echo'</select>
                </td>
            </tr>
                    <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_post_comment">', $txt['arcade_enable_post_comment'], '</label></th>
                <td>
                    <input type="checkbox" name="enable_post_comment" id="arcade_post_comment" value="1" style="check" ', $arcSettings['enable_post_comment'] ? 'checked="checked"' : '' ,'/><br />
                </td>
            </tr>
          <tr class="windowbg2">
                <td colspan="2" align="center" class="warn_moderate">
									<i><b>',$txt['arcade_arcade_news'],'</b></i>
                </td>
            </tr>

                        <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_news_fader">', $txt['arcade_news_fader'], '</label></th>
                <td>
                    <select name="arcadeNewsFader" id="arcade_news_fader">
                        <option value="0" ', $arcSettings['arcadeNewsFader'] == 0 ? 'selected="selected"' : '', '>' . $txt['arcade_txt_off'] . '</option>';
                        foreach($context['arcade_boards'] as $boards)
                        {
                            echo'<option value="',$boards['id_board'],'" ', $arcSettings['arcadeNewsFader'] == $boards['id_board'] ? 'selected="selected"' : '', '>', $boards['name'] ,'</option>';
                        }
                    echo'</select>
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_news_fader_topics">',$txt['arcade_news_fader_topics'],':</td>
                    <td><input type="text" name="arcadeNewsNumber" value="', $arcSettings['arcadeNewsNumber'], '" /></td>
            </tr>
 					 <tr class="windowbg2">
                <td colspan="2" align="center" class="warn_moderate">
									<i><b>',$txt['arcade_shout_box'],'</b></i>
                </td>
            </tr>
             <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_shout_box_members">', $txt['arcade_shout_box_members'], '</label></th>
                <td>
                    <input type="checkbox" name="enable_shout_box_members" id="arcade_shout_box_members" value="1" style="check" ', $arcSettings['enable_shout_box_members'] ? 'checked="checked"' : '' ,'/><br />
                </td>
            </tr>
             <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_shout_box_scores">', $txt['arcade_shout_box_scores'], '</label></th>
                <td>
                    <input type="checkbox" name="enable_shout_box_scores" id="arcade_shout_box_scores" value="1" style="check" ', $arcSettings['enable_shout_box_scores'] ? 'checked="checked"' : '' ,'/><br />
                </td>
            </tr>
             <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_shout_box_best">', $txt['arcade_shout_box_best'], '</label></th>
                <td>
                    <input type="checkbox" name="enable_shout_box_best" id="arcade_shout_box_best" value="1" style="check" ', $arcSettings['enable_shout_box_best'] ? 'checked="checked"' : '' ,'/><br />
                </td>
            </tr>
             <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_shout_box_champ">', $txt['arcade_shout_box_champ'], '</label></th>
                <td>
                    <input type="checkbox" name="enable_shout_box_champ" id="arcade_shout_box_champ" value="1" style="check" ', $arcSettings['enable_shout_box_champ'] ? 'checked="checked"' : '' ,'/><br />
                </td>
            </tr>
             <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_shout_box_comment">', $txt['arcade_shout_box_comment'], '</label></th>
                <td>
                    <input type="checkbox" name="enable_shout_box_comment" id="arcade_shout_box_comment" value="1" style="check" ', $arcSettings['enable_shout_box_comment'] ? 'checked="checked"' : '' ,'/><br />
                </td>
            </tr>
            <tr class="windowbg2">
                <th width="50%" align="right"><label for="arcade_shout_box_show">',$txt['arcade_shout_box_show'],':</td>
                    <td><input type="text" name="arcade_show_shouts" value="', $arcSettings['arcade_show_shouts'], '" /></td>
            </tr>

            <tr class="windowbg2">
                <td  colspan="2" align="center">
                    <input type="submit" name="save_settings" value="', $txt['arcade_save_settings'], '" /><br /><br />
                </td>
            </tr>

        </table>

        <input type="hidden" name="sc" value="', $context['session_id'], '" />
    </form>';
}

function template_arcadeadmin_info()
{
    global $scripturl, $txt, $arcSettings, $context, $settings, $arcade_version;

    echo '
       <div class="cat_bar">
                <h3 class="catbg">
                ',$txt['arcade_status'], '
                </h3>
            </div>
        <table class="bordercolor" border="0" cellpadding="5" cellspacing="1" width="100%">
            <tr class="windowbg2">
            <td valign="bottom" align="center">
            <img src="' . $settings['images_url'] . '/arc_icons/arc.gif" alt="*" /><br />';

            echo parse_bbc('[b][size=7][color=green]E[/color][color=yellow]-[/color][color=red]A[/color][color=pink]R[/color][color=orange]C[/color][color=purple]A[/color][color=blue]D[/color][color=limeGreen]E[/color][/size][/b]');
            echo'
            <br />
                <div class="smalltext" style="text-align: center;">
                Powered by <a href="http://www.smfhacks.com" target="_blank">E-Arcade ', $arcSettings['arcadeVersion'],'</a> based on: SMF Arcade ', $arcade_version, ' &copy; Niko Pahajoki 2004-2007</div>
            </td>
            <td valign="top" width="30%">
                <table class="bordercolor" border="0" cellpadding="5" cellspacing="1" width="100%">
                    <tr class="windowbg2"><td>', $txt['arcade_ins_version'], '</td><td>', $arcSettings['arcadeVersion'], '</td></tr>
                    <tr class="windowbg2"><td>', $txt['arcade_latest_version'], '</td><td><span id="latest_version">???</span></td></tr>
                    <tr class="windowbg2"><td>', $txt['arcade_db_ins_version'], '</td><td>', $arcSettings['arcadeDatabaseVersion'], '</td></tr>
                    <tr class="windowbg2"><td>', $txt['arcade_db_req_version'], '</td><td><span>12</span></td></tr>
                </table>
            </td>
            </tr>
        </table>

    <div style="float: left; width: 100%;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr><td valign="top">
            <div class="cat_bar">
                <h3 class="catbg">
                ', $txt['arcade_latest_news'], '
                </h3>
            </div>
            <table class="bordercolor" border="0" cellpadding="5" cellspacing="1" width="100%">
                <tr><td class="windowbg2" style="padding: 0pt; height: 18ex;" valign="top"><div id="news" style="overflow: auto; height: 18ex; padding-right: 1ex;">' . $txt['arcade_forlatestnews'] . '</div></td></tr>
            </table>
        </td></tr>
        </table>
    </div>

    <div style="clear: both;"></div><br />';

    // Shows news and latest version
    echo '
    <script language="JavaScript" type="text/javascript" src="http://www.smfhacks.com/versions/arcade.js"></script>
    <script language="JavaScript" type="text/javascript">
            function setArcadeNews()
            {
                if (typeof(window.arcadeNews) == "undefined" || typeof(window.arcadeNews.length) == "undefined")
                    return;

                var str = "<div style=\"margin: 4px; font-size: 0.85em;\">";

                for (var i = 0; i < window.arcadeNews.length; i++)
                {
                    str += "\n  <div style=\"padding-bottom: 2px;\">" + window.arcadeNews[i].subject + " on " + window.arcadeNews[i].time + "</div>";
                    str += "\n  <div style=\"padding-left: 2ex; margin-bottom: 1.5ex; border-top: 1px dashed;\">"
                    str += "\n      " + window.arcadeNews[i].message;
                    str += "\n  </div>";
                }

                setInnerHTML(document.getElementById("news"), str + "</div>");
            }
            function setArcadeVersion()
            {
                if (typeof(window.arcadeVersion) == "undefined")
                    return;

                setInnerHTML(document.getElementById("latest_version"), window.arcadeVersion);
            }
        
            // Override on the onload function
            window.onload = function ()
            {
                setArcadeVersion();
                setArcadeNews();
            }

    </script>
    ';
}

function template_arcadeadmin_editor()
{
	global $context, $txt, $scripturl, $settings;

	echo '
	<form action="', $scripturl, '?action=admin;area=managearcade;sa=listgames;do=gamesave" method="post">
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
			<tr>
				<td>
                   <div class="cat_bar">
                        <h3 class="catbg">
                        ', $txt['arcade_edit_game'], ' - ', $context['arcade']['game']['name'], '
                        </h3>
                    </div>
					<table border="0" cellspacing="0" cellpadding="4" width="100%">
				        ';
						foreach ($context['arcade']['config_array'] as $item)
						{
							echo'<tr class="windowbg2">';
							if (is_array($item))
							{
								echo '<td align="right" valign="middle"', ($item['disabled'] ? ' style="color: #777777;"' : isset($context['arcade']['config_errors'][$item['name']]) ? ' style="color: red;"' : ''), '><label for="', $item['name'], '">', $item['label'], ($item['type'] == 'password' ? '<br /><i>' . $txt['admin_confirm_password'] . '</i>' : ''), '</label></td>';
								if ($item['help'])
								{
									echo '<td class="windowbg2" align="right" valign="middle" width="16"><a href="', $scripturl, '?action=helpadmin;help=', $item['help'], '" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" align="top" /></a></td>';
								}
								else
								{
									echo '<td class="windowbg2" align="right" width="0"> = </td>';
								}
								echo '<td class="windowbg2" width="50%">';
								// Select
								if ($item['type'] == 'select')
								{
									echo '<select name="data[', $item['name'], ']" id="', $item['name'], '">';
									foreach ($item['data'] as $option)
									{
										echo '<option value="', $option['value'], '"', $option['value'] == $item['value'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
									}
									echo '</select>';
								}
								// Text area
								elseif ($item['type'] == 'large_text')
								    echo '<textarea name="data[', $item['name'], ']" id="', $item['name'], '" rows="', $item['rows'], '" cols="', $item['cols'], '">', $item['value'], '</textarea>';
								// Checkbox
								elseif ($item['type'] == 'checkbox')
								    echo '<input type="checkbox"', ($item['disabled'] ? ' disabled="disabled"' : ''), ' name="data[', $item['name'], ']" id="', $item['name'], '" value="1"', ($item['checked'] ? ' checked="checked"' : ''), ' class="check" />';

								elseif ($item['type'] == 'permission')
								{
									echo '<fieldset>';
									foreach($context['groups'] as $group)
									{
										echo '<input id="group', $group['id'], '" type="checkbox" class="check" name="data[member_groups][]" value="', $group['id'], '"', $group['checked'] ? ' checked="checked"' : '' , ' class="check" /> <span', $group['is_post_group'] ? ' style="border-bottom: 1px dotted;" title="' . $txt['groups_post_group'] . '"' : '' ,'><label for="group', $group['id'], '">', $group['name'], '</label></span><br />';
									}
									echo'<input onclick="invertAll(this, this.form, \'data[member_groups][]\');" type="checkbox" style="check" /> <i>'  . $txt['arcade_checkall'] . '</i>
									</fieldset>';
								}
								// Textbox
								else
								echo '<input type="text"', ($item['disabled'] ? ' disabled="disabled"' : ''), ' name="data[', $item['name'], ']" id="', $item['name'], '" value="', $item['value'], '"', ($item['size'] ? ' size="' . $item['size'] . '"' : ''), ' />';

								echo '</td>';
							}
							else
							{
								if ($item == '')
								echo'<td colspan="3" class="windowbg2"><hr /></td>';
								else
								echo'<td colspan="3" align="center" class="warn_moderate"><b><i>', $item, '</i></b></td>';
							}

							echo '</tr>';
						}

						echo '
						<tr class="windowbg2">
							<td align="right" colspan="3">
								<input type="submit" name="save_game" value="', $txt['arcade_save'], '" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	<input type="hidden" name="game" value="', $context['arcade']['game']['id'], '" />
	<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>';
}

function template_games_list()
{
    global $context, $txt, $scripturl, $sc, $settings, $arcSettings;

   $category = prepareCategories();

    echo '
    <script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	 	function QactionChange()
		{
			document.getElementById(\'qcategory\').style.display = \'none\';
			document.getElementById(\'qset\').style.display = \'none\';

			if (document.getElementById(\'qaction\').value == \'change\')
			{
				document.getElementById(\'qcategory\').style.display = \'\';
				document.getElementById(\'qset\').style.display = \'\';
			}
			else if (document.getElementById(\'qaction\').value == \'clear_scores\')
			{
				document.getElementById(\'qset\').style.display = \'\';
			}
			else if (document.getElementById(\'qaction\').value == \'clear_scores2\')
			{
				document.getElementById(\'qset\').style.display = \'\';
			}
			else if (document.getElementById(\'qaction\').value == \'del_games\')
			{
				document.getElementById(\'qset\').style.display = \'\';
			}
			else if (document.getElementById(\'qaction\').value == \'fix_scores\')
			{
				document.getElementById(\'qset\').style.display = \'\';
			}
			else
			{

			}
		}
     // ]]></script>
    <a name="top">
    <form action="', $scripturl, '?action=admin;area=managearcade;sa=quick" method="post" onsubmit="return confirm(\'', addslashes($txt['arcade_are_you_sure']), '\');">
    <div >
    <table class="bordercolor" border="0" cellpadding="3" cellspacing="1" width="100%">
        <tr class="titlebg">
            <td class="smalltext" colspan="4">', $txt['pages'], ': ', $context['arcade']['pageIndex'], ' &nbsp;&nbsp;<a href="#bot"><b>', $txt['go_down'], '</b></a></td>
        </tr>';
        if (count($context['arcade']['games']) > 0)
        {
            echo '
            <tr class="windowbg">
                <td><input type="checkbox" class="check" name="selectall" onclick="invertAll(this, this.form, \'games[]\');" /> </td>
                <td><b>', $txt['arcade_install_all'], '</b></td>
                <td></td>
            </tr>';
            foreach ($context['arcade']['games'] as $game)
            {
            	$thumb = !$game['directory'] ?	$arcSettings['gamesUrl'].$game['thumbnail'] : $arcSettings['gamesUrl'].$game['directory']."/".$game['thumbnail'];

                echo '
                <tr class="windowbg2">
                    <td width="2" align="center"><input type="checkbox" name="games[]" value="', $game['id'], '" class="check" /></td>
                    <td>
                    <a href="' . $game['url']['edit'] . '"><img width="20" height="20" src="' . $thumb . '" alt="'.$game['name'].'" title="'.$game['name'].'"/></a>
                    <a href="', $game['url']['edit'], '">', $game['name'], '</a></td>
                    <td width="25%">
                    <a href="', $scripturl,'?action=admin;area=managearcade;sa=listgames;category=', $game['category']['id'], '"><img src="' . $settings['images_url'] . '/arc_icons/'.$category[$game['category']['id']]['icon'].'" width="20" height="20" alt="', $game['category']['name'], '" title="'.$txt['arcade_category'].' '.$game['category']['name'].'"/></a>
                    <a href="', $game['url']['edit'], '"><img src="' . $settings['images_url'] . '/arc_icons/modify.gif" alt="*" title="'.$txt['arcade_edit_game'].'"/></a>
                    <a href="', $game['url']['delete'], ';sesc=', $sc, '" onclick="return confirm(\'', addslashes($txt['arcade_delete_game']), '\');"><img src="' . $settings['images_url'] . '/arc_icons/delete.gif" alt="*" title="'.$txt['arcade_delete'].'"/></a>';
                    if (!$game['enabled'])
                    {
                    	echo '&nbsp;<img src="' . $settings['images_url'] . '/warn.gif" alt="*" title="'.$txt['arcade_disabled_game'].'"/>';
                    }
                    echo'
                    </td>
                </tr>';
            }


            echo '
            <tr class="windowbg" align="right">
                <td colspan="3">
                    <select id="qaction" name="qaction" onchange="QactionChange();">
                    <option>-------</option>
					<option value="gotd">',$txt['arcade_change_gotd'],'</option>
					<option value="clear_scores">', $txt['arcade_delete_all_scores'], '</option>
					<option value="clear_scores2">', $txt['arcade_delete_all_scores2'], '</option>
                    <option value="del_games">', $txt['arcade_uninstall'], '</option>
					<option value="change">', $txt['arcade_change_category'], '</option>
					<option value="fix_scores">', $txt['arcade_fix_scores'], '</option>
                    </select>

                    <select id="qcategory" name="qcategory" style="display: none;">';
                    foreach ($category as $cat)
                    {
                        echo '
                        <option value="', $cat['id'], '">', $cat['name'], '</option>';
                    }
                    echo '
                    </select>
                    <select id="qset" name="qset">
                    <option value="0">', $txt['arcade_selected'], '</option>
                    <option value="1">', $txt['arcade_all'], '</option>
                    </select>
                    <input type="submit" value="', $txt['arcade_submit'], '" />
                </td>
            </tr>';
        }
        else
        {
            // There are no games.
            echo '
            <tr>
                <td class="catbg3"><b>', $txt['arcade_no_games_installed'], '</b></td>
            </tr>';
        }

    echo '
            <tr class="titlebg">
                <td class="smalltext" colspan="3">', $txt['pages'], ': ', $context['arcade']['pageIndex'], ' &nbsp;&nbsp;<a href="#top"><b>', $txt['go_up'], '</b></a></td>
            </tr>
        </table>
        </div>
        <input type="hidden" name="sc" value="', $context['session_id'], '" />
    </form>
    <a name="bot">';
}

function template_files_list()
{
	global $context, $txt, $scripturl;


	// Header for File listing
	echo '
	<div class="bordercolor">
	<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
	<form action="', $scripturl, '?action=admin;area=managearcade;sa=files;do=stageone" method="post" id="files">';

		echo '
		<tr>
			<td colspan="3" class="titlebg"><span class="smalltext" align="right"><a href="#bot"><b>', $txt['go_down'], '</b></a></span></td>
		</tr>
		<tr class="windowbg">
			<td width="10"><input type="checkbox" class="check" name="selectall" onclick="invertAll(this, this.form, \'directory[]\');" /></td>
			<td colspan="2"><b>', $txt['arcade_install_all'], ' ', $txt['arcade_directories'], '</b></td>
		</tr>';
		// Directories
	if (count($context['arcade']['directories']))
	{

		foreach ($context['arcade']['directories'] as $directory)
		{
			echo '
			<tr class="windowbg2">
				<td align="center"><input type="checkbox" class="check" name="directory[]" value="', $directory['name'], '" /></td>
				<td colspan="2"><a href="', $directory['url']['view'], '">', $directory['name'], '</a></td>
			</tr>';
		}
	}
	// Files
	if (count($context['arcade']['files']))
	{
		echo '

		<tr class="windowbg">
			<td width="10"><input type="checkbox" class="check" name="selectall" onclick="invertAll(this, this.form, \'file[]\');" /></td>
			<td colspan="2"><b>', $txt['arcade_install_all'], ' ', $txt['arcade_files'], '</b></td>
		</tr>';

		foreach ($context['arcade']['files'] as $game)
		echo '
		<tr class="windowbg2">
			<td align="center"><input type="checkbox" class="check" name="file[]" value="', $game['path'], '" /></td>
			<td colspan="2"><a href="', $game['url']['install'], '">', $game['name'], '</a> (', $game['file'], ')</td>
		</tr>';
	}

	// Submit
	echo '
	<tr class="windowbg2">
		<td align="right" colspan="3">
		<input type="submit" name="install_games" value="', $txt['arcade_install_selected'], '" />
		</td>
	</tr>
	</table>
	<input type="hidden" name="sc" value="', $context['session_id'], '" />
	</form>

	<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
	<form action="', $scripturl, '?action=admin;area=managearcade;sa=upload" method="post" id="upload" enctype="multipart/form-data">
	<tr class="windowbg">
		<td ><b>', $txt['arcade_upload'], '</b>
		<input type="file" name="package" /> (<i>', $txt['arcade_supported_filetypes'], '</i>)
		<input type="submit" name="install_games" value="', $txt['arcade_upload'], '" />
		</td>
	</tr>
	<tr>
		<td colspan="3" class="titlebg"><span class="smalltext" align="right"><span class="smalltext"><a href="#top"><b>', $txt['go_up'], '</b></a></span></span></td>
	</tr>
	</table>
	</div>
	</form>';

}

function template_games_install()
{
    global $context, $txt, $scripturl;

    // Header for Installer
    echo '
        <form action="', $scripturl, '?action=admin;area=managearcade;sa=files;do=stagetwo" method="post" id="games">
            <div class="tborder">
                <table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
                    <tr>
                        <td colspan="2" class="catbg3"><b>', $txt['arcade_install_general'], '</b></td>
                    </tr>
                    <tr class="windowbg2">
                        <td width="100"><label for="def_enabled">', $txt['arcade_install_disabled'], '</label>:</td>
                        <td><input id="def_enabled" type="checkbox" name="defaults[enabled]" value="0" /></td>
                    </tr>
                    <tr class="windowbg2">
                        <td width="100"><label for="def_category">', $txt['arcade_category'], '</label>:</td>
                        <td><select id="def_enabled" name="defaults[id_category]">';

    foreach ($context['arcade']['categories'] as $category)
        echo '
                                <option value="', $category['id'], '">', $category['name'], '</option>';

    echo '

                            </select></td>
                    </tr>';

    foreach ($context['arcade']['install'] as $game)
    {
        echo '
                    <tr>
                        <td colspan="2" class="catbg3"><b>', $game['game_name'], ' (', $game['file'], ')</b></td>
                    </tr>
                    <tr class="windowbg2">
                        <td><label for="', $game['internal_name'], 'name">', $txt['arcade_game_name'], '</label>:</td>
                        <td><input id="', $game['internal_name'], 'name" type="text" name="game[', $game['internal_name'], '][game_name]" value="', $game['game_name'], '" /></td>
                    </tr>
                    <tr class="windowbg2">
                        <td><label for="', $game['internal_name'], 'internal_name">', $txt['arcade_internal_name'], '</label>:</td>
                        <td><input id="', $game['internal_name'], 'internal_name" type="text" name="game[', $game['internal_name'], '][internal_name]" value="', $game['internal_name'], '" />
                        </td>
                    </tr>';
    }

    // Submit
    echo '
                    <tr class="windowbg2">
                        <td align="right" colspan="3">
                            <input type="submit" name="install_games" value="', $txt['arcade_install_game'], '" />
                        </td>
                    </tr>';

    // Bottom
    echo '
                </table>
            </div>
            <input type="hidden" name="sc" value="', $context['session_id'], '" />
        </form>

';
}

function template_games_install_complete()
{
    global $context, $txt, $scripturl;

    echo '
        <div class="tborder">
            <table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
                <tr>
                    <td colspan="2" class="catbg3"><b>', $txt['arcade_game_install_complete'], '</b></td>
                </tr>
                <tr class="windowbg2">
                    <td>';

    foreach ($context['arcade']['messages'] as $message)
        echo '
            ', $message, '<br />';

    echo '
                    </td>
                </tr>
            </table>
        </div>';

}

function template_auto_files()
{
    global $txt, $scripturl, $settings, $context, $arcSettings;

    if ($context['arcade']['sub_action'] == "massinstall")
    {
        echo '
            <div class="cat_bar">
                    <h3 class="catbg">
                    ', $txt['arcade_admin_auto_install'], '
                    </h3>
            </div>

        <table border="0" width="100%" cellspacing="1" cellpadding="5" class="bordercolor">
            <tr class="windowbg2">
                <td colspan="2" class="windowbg2" align="left" valign="middle" width="16"><br />',$txt['arcade_description_help'],' <a href="', $scripturl, '?action=helpadmin;help=arcade_admin_auto_install_text" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" border="0" align="top" /></a><br /><br /></td>
            </tr>';
           if (isset($context['arcade']['toinstall']))
            {

            	echo '<tr class="windowbg2">
                <td colspan="2" class="windowbg2" align="left" valign="middle" width="16">
                <b>',$txt['arcade_admin_ai_waiting'],'</b><br />';
            	foreach($context['arcade']['toinstall'] as $key => $file)
            	{
            		echo $file.'<br />';
            	}

            	echo'</td>
            </tr>';
            }

            echo'
            <tr class="windowbg">
                <td>
                    <b>', $txt['arcade_upload'], '</b>
                </td>
                <td>
                    <form action="', $scripturl, '?action=admin;area=managearcade;sa=upload" method="post" id="upload" enctype="multipart/form-data">
                    <input type="file" name="package" /> (<i>', $txt['arcade_supported_filetypes_tar'], '</i>)
                    <input type="submit" name="install_games" value="', $txt['arcade_upload'], '" />
                    </form>
                </td>
            </tr>
            <tr class="windowbg">
                <td>
                    <b>',$txt['arcade_category'],':</b>
                </td>
                <td>
                    <form name="whatta" action="',$scripturl,'?action=admin;area=managearcade;sa=autofiles;sub=massi1" method="post">
                    <select size="1" name="category">';
                    $cats = prepareCategories();
                    foreach($cats as $i => $temp)
                    {
                        echo '<option value=',$temp['id'],'>',$temp['name'],'</option>';
                    }
                    echo '</select>&nbsp;<input type="submit" value="',$txt['arcade_title_install_games'],'" name="massinstall" />
                    </form>
                </td>
            </tr>
            <tr class="titlebg">
                <td colspan="2">&nbsp;</td>
            </tr>
        </table>';
    }
    else
    {
        echo '<table border="0" width="100%" cellspacing="1" cellpadding="5" class="bordercolor">
                <tr class="catbg">
                    <td>',$txt['arcade_admin_auto_install_done'],'</td>
                </tr>

                <tr class="windowbg2">
                    <td>';

            foreach ($context['arcade']['installed_games'] as $key => $value)
            {
                    echo $value,'<br />';
            }
            echo'
            </td>
        </tr>
                    <tr class="catbg">
                    <td>',$txt['arcade_admin_auto_install_fail'],'</td>
                </tr>

                <tr class="windowbg2">
                    <td>';

            foreach ($context['arcade']['failed_games'] as $key1 => $value1)
            {
                    echo $value1,'<br />';
            }
            echo'
            </td>
        </tr>
    </table><br />';

    }

}

?>