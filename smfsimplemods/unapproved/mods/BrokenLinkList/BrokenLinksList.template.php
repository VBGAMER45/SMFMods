<?php
/*---------------------------------------------------------------------------------
*	Broken Links List															  *
*	Version 1.1																	  *
*	Author: 4kstore																  *
*	Powered by www.smfsimple.com												  *
**********************************************************************************/

function template_ShowBll()
{
	global $context, $modSettings, $txt, $settings;
	echo'
	<div class="cat_bar">
		<h3 class="catbg">
			',!empty($modSettings['bll_titleset']) ? $modSettings['bll_titleset'] : $txt['bll_admin_menu_button'],'
		</h3>
	</div>
	<div id="mlist" class="tborder topic_table">
		<table class="table_grid" cellspacing="0" width="100%">
			<thead>
				<tr class="catbg">';
			foreach ($context['columns'] as $column)
			{
				if ($column['selected'])
					echo '
						<th scope="col">
							<a href="', $column['href'], '">', $column['label'], ' <img src="', $settings['images_url'], '/sort_', $context['sort_direction'], '.gif" alt="" /></a>
						</th>';
				elseif ($column['sortable'])
					echo '
						<th scope="col">
							', $column['link'], '
						</th>';
				else
					echo '
						<th scope="col">
							', $column['label'], '
						</th>';
			}

			echo'
					</tr>
				</thead>
			<tbody>';
			foreach($context['inforows'] as $infor)
			{
				echo '
				<tr>
					<td class="windowbg2" width="3%">
						', $infor['id_report'] ,'
					</td>
					<td class="windowbg">
						<strong>', $infor['subjecthref'] ,'</strong>
						<br />
						',$txt['bll_report_links_poster'],' ', $infor['posterhref'] ,'
						<br />
					</td>

					<td class="windowbg">
						', $infor['reported_name_href'] ,'
					</td>
					<td class="windowbg">
						', $infor['notes'] ,'
					</td>
					<td class="windowbg2" width="10%">
						', $infor['reported_time'] ,'
					</td>
					<td class="windowbg2" width="3%">
						', $infor['iconstatus'] ,'
					</td>';
				if(!empty($infor['canMod']))
					echo'
					<td class="windowbg2" width="3%">
						'. $infor['edit'] .' '. $infor['delete'] .'
					</td}';
				echo'
				</tr>';
			}
			echo'
			</tbody>
		</table>
		<div style="text-align: center;"><em><a href="https://www.smfsimple.com" target="_blank"><strong>Broken Link List 1.1  by 4kstore</strong></a></em></div>
	</div>';
}

function template_reportinglinks()
{
	global $context, $txt, $scripturl;
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				'.$txt['bll_admin_menu_button'].'
			</h3>
		</div>
		<form method="post" name="sectform" action="', $scripturl, '?action=brokenlinkslist;sa=report" accept-charset="', $context['character_set'], '">
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<label for="bll_report_links_notes_add">', $txt['bll_report_links_notes_add'], '</label>
						</dt>
						<dd>
							<input type="text" name="notes" size="50" maxlength="100" value=""/>
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="hidden" name="msg" value="', $context['bll_id_msg'] ,'"/>
						<input type="hidden" name="topic" value="', $context['bll_id_topic'] ,'"/>
						<input type="hidden" name="member" value="', $context['bll_id_member'] ,'"/>
						<input type="hidden" name="save" value="',$txt['bll_admin_save_menu_button'],'" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="',$txt['bll_admin_save_menu_button'],'" value="',$txt['bll_admin_save_menu_button'],'" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}
function template_bll_edit()
{
	global $context, $scripturl, $txt;
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['bll_report_links_moderate_edit'] ,'
			</h3>
		</div>
		<form method="post" name="sectform" action="', $scripturl, '?action=brokenlinkslist;sa=edit" accept-charset="', $context['character_set'], '">
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">';
					foreach($context['bllrows'] as $blledit)
					{
						echo'
						<dt>
							<span><label for="bll_report_links_notes">', $txt['bll_report_links_notes'], '</label></span>
						</dt>
						<dd>
							<input type="text" name="notes" size="70" maxlength="100" value="', $blledit['notes'] ,'"/>
						</dd>
						<dt>
							<span><label for="bll_report_links_status">', $txt['bll_report_links_status'], '</label></span>
						</dt>
						<dd>
							<select name="status" size="1" style="width: 88%;">
								<option value="0" ' ,($blledit['status'] == 0) ? 'selected="selected"' : '', '>'.$txt['bll_report_links_status_icon'].'</option>
								<option value="1" ' ,($blledit['status'] == 1) ? 'selected="selected"' : '', '>'.$txt['bll_report_links_status_icon1'].'</option>
								<option value="2" ' ,($blledit['status'] == 2) ? 'selected="selected"' : '', '>'.$txt['bll_report_links_status_icon2'].'</option>
							</select>
						</dd>';
					}
					echo'
					</dl>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="hidden" name="id" value="', $blledit['id_report'] ,'"/>
						<input type="hidden" name="save" value="',$txt['bll_admin_save_menu_button'],'" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="',$txt['bll_admin_save_menu_button'],'" value="',$txt['bll_admin_save_menu_button'],'" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}
//ADMIN SETTINGS!
function template_bll_settings()
{
	global $context, $scripturl, $txt, $modSettings;
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				'.$txt['bll_admin_menu_button'].' - '.$txt['bll_admin_settings'].'
			</h3>
		</div>
		<form method="post" action="', $scripturl, '?action=admin;area=brokenlinkslist" accept-charset="', $context['character_set'], '">
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<label for="bll_admin_enabled">', $txt['bll_admin_enabled'], '</label>
						</dt>
						<dd>
							<input type="checkbox" value="1" name="bll_enabled" ',!empty($modSettings['bll_enabled']) ? 'checked="checked"' : '',' />
						</dd>
					</dl>
					<hr class="hrcolor clear">
					<dl class="settings">
						<dt>
							<span><label for="bll_admin_titleset">', $txt['bll_admin_titleset'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['bll_titleset']) ? $modSettings['bll_titleset'] : '','" name="bll_titleset" />
						</dd>
						<dt>
							<span><label for="bll_admin_warning_link_time">', $txt['bll_admin_warning_link_time'], '<div class="smalltext">', $txt['bll_admin_warning_link_time2'], '</div></label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['bll_warning_link_time']) ? $modSettings['bll_warning_link_time'] : '','" name="bll_warning_link_time" />
						</dd>
						<dt>
							<span><label for="bll_admin_warning_link_color">', $txt['bll_admin_warning_link_color'], '<div class="smalltext">', $txt['bll_admin_warning_link_color2'], '</div></label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['bll_warning_link_color']) ? $modSettings['bll_warning_link_color'] : '','" name="bll_warning_link_color" />
						</dd>
						<dt>
							<span><label for="bll_admin_senderid">', $txt['bll_admin_senderid'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['bll_senderid']) ? $modSettings['bll_senderid'] : '','" name="bll_senderid" />
						</dd>
						<dt>
							<span><label for="bll_admin_pm_title">', $txt['bll_admin_pm_title'], '</label></span>
						</dt>
						<dd>
							<input type="text" size="50" value="',!empty($modSettings['bll_pm_title']) ? $modSettings['bll_pm_title'] : '','" name="bll_pm_title" />
						</dd>
						<dt>
							<span><label for="bll_pm_text">'.$txt['bll_pm_text'].'<br /><div class="smalltext">'.$txt['bll_pm_text2'].'</div><br />'.$txt['bll_admin_pm_vars'].'<br />'.$txt['bll_admin_pm_vars2'].'<br />'.$txt['bll_admin_pm_vars3'].'<br />'.$txt['bll_admin_pm_vars4'].'</label></span>
						</dt>
						<dd>
							<textarea rows="10" cols="70" name="bll_pm_text">',!empty($modSettings['bll_pm_text']) ? $modSettings['bll_pm_text'] : '','</textarea>
						</dd>
						<hr class="hrcolor clear" />
						<dt>
							<span><label for="bll_admin_reset_ok_status"><a onclick="return confirm(\''.$txt['bll_report_links_admin_delete_confirm'].'\');" href="', $scripturl, '?action=admin;area=brokenlinkslist;sa=deleteok">'.$txt['bll_admin_reset_ok_status'].'</a><br /><div style="color:red;" class="smalltext">'.$txt['bll_admin_reset_ok_status_warn'].'</div></label></span>
						</dt>
					</dl>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="submit" name="save" value="',$txt['bll_admin_save_menu_button'],'" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}