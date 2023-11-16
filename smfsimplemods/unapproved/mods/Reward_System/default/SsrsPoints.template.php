<?php
/*---------------------------------------------------------------------------------
*	SMFSimple Rewards System											 		  *
*	Version 3.0																	  *
*	Author: 4kstore																  *
*	Copyright 2012														          *
*	Powered by www.smfsimple.com												  *
***********************************************************************************
**********************************************************************************/


//RFD ADMIN SETTINGS!
function template_ssrs_settings()
{
	global $context, $scripturl, $txt, $settings, $modSettings;

	echo'
	<div id="ssrs_cont">
		<form method="post" action="', $scripturl, '?action=admin;area=rewardPoints" accept-charset="', $context['character_set'], '">
			<table width="100%" cellspacing="1" cellpadding="5" border="0">
				<tr>
					<td width="100%" align="center" colspan="10">
						<span class="ssrs_title">'.$txt['admin_ssrs_settings'].'</span>
						<br /><span class="ssrs_title2">'.$txt['ssrs_settings_rpp'].'</span>
						<hr class="ssrs_hr" />
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_enabled'].'
					</td>
					<td width="50%" valign="top">
						<input type="checkbox" value="1" name="ssrs_points_enabled" ',!empty($modSettings['ssrs_points_enabled']) ? 'checked="checked"' : '',' />
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_point_guest'].'
					</td>
					<td  width="50%" valign="top">
						<input type="checkbox" value="1" name="ssrs_points_guests" ',!empty($modSettings['ssrs_points_guests']) ? 'checked="checked"' : '',' />
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_show_givers_display'].'
					</td>
					<td width="50%" valign="top">
						<select name="ssrs_points_show_givers_display" size="1" style="width: 88%;">
							<option value="0" ' ,($modSettings['ssrs_points_show_givers_display'] == 0) ? 'selected="selected"' : '', '>'.$txt['admin_ssrs_points_show_givers_display_no'].'</option>
							<option value="1" ' ,($modSettings['ssrs_points_show_givers_display'] == 1) ? 'selected="selected"' : '', '>'.$txt['admin_ssrs_points_show_givers_display_only_names'].'</option>
							<option value="2" ' ,($modSettings['ssrs_points_show_givers_display'] == 2) ? 'selected="selected"' : '', '>'.$txt['admin_ssrs_points_show_givers_display_name_points'].'</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['ssrs_points_points_on_messageindex'].'
					</td>
					<td width="50%" valign="top">
						<input type="checkbox" value="1" name="ssrs_points_points_on_messageindex" ',!empty($modSettings['ssrs_points_points_on_messageindex']) ? 'checked="checked"' : '',' />
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_per_post'].'
					</td>
					<td width="50%" valign="top">
						<input type="text" size="20" value="',!empty($modSettings['ssrs_points_per_post']) ? $modSettings['ssrs_points_per_post'] : '','" name="ssrs_points_per_post" />
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_per_reply'].'
					</td>
					<td width="50%" valign="top">
						<input type="text" size="20" value="',!empty($modSettings['ssrs_points_per_reply']) ? $modSettings['ssrs_points_per_reply'] : '','" name="ssrs_points_per_reply" />
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_per_word'].'
					</td>
					<td width="50%" valign="top">
						<input type="text" size="20" value="',!empty($modSettings['ssrs_points_per_word']) ? $modSettings['ssrs_points_per_word'] : '','" name="ssrs_points_per_word" />
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_bonus_words'].'
					</td>
					<td style="color:#EAAA15;" width="50%" valign="top">
						'.$txt['modifications_ssrs_points'].' <input type="text" size="5" value="',!empty($modSettings['ssrs_points_bonus_words']) ? $modSettings['ssrs_points_bonus_words'] : '','" name="ssrs_points_bonus_words" />
						'.$txt['admin_ssrs_points_bonus_words_min'].' <input type="text" size="5" value="',!empty($modSettings['ssrs_points_bonus_words_min']) ? $modSettings['ssrs_points_bonus_words_min'] : '','" name="ssrs_points_bonus_words_min" />

					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_per_registered'].'
					</td>
					<td width="50%" valign="top">
						<input type="text" size="20" value="',!empty($modSettings['ssrs_points_per_registered']) ? $modSettings['ssrs_points_per_registered'] : '','" name="ssrs_points_per_registered" />
					</td>
				</tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_per_poll'].'
					</td>
					<td width="50%" valign="top">
						<input type="text" size="20" value="',!empty($modSettings['ssrs_points_per_poll']) ? $modSettings['ssrs_points_per_poll'] : '','" name="ssrs_points_per_poll" />
					</td>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_per_vote_poll'].'
					</td>
					<td width="50%" valign="top">
						<input type="text" size="20" value="',!empty($modSettings['ssrs_points_per_vote_poll']) ? $modSettings['ssrs_points_per_vote_poll'] : '','" name="ssrs_points_per_vote_poll" />
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_per_topic_views'].'
					</td>
					<td style="color:#EAAA15;" width="50%" valign="top">
						'.$txt['modifications_ssrs_points'].' <input type="text" size="5" value="',!empty($modSettings['ssrs_points_per_topic_views']) ? $modSettings['ssrs_points_per_topic_views'] : '','" name="ssrs_points_per_topic_views" />
						'.$txt['views'].' <input type="text" size="5" value="',!empty($modSettings['ssrs_points_per_topic_views_min']) ? $modSettings['ssrs_points_per_topic_views_min'] : '','" name="ssrs_points_per_topic_views_min" />
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_per_topic_replies'].'
					</td>
					<td style="color:#EAAA15;" width="50%" valign="top">
						'.$txt['modifications_ssrs_points'].' <input type="text" size="5" value="',!empty($modSettings['ssrs_points_per_topic_replies']) ? $modSettings['ssrs_points_per_topic_replies'] : '','" name="ssrs_points_per_topic_replies" />
						'.$txt['replies'].' <input type="text" size="5" value="',!empty($modSettings['ssrs_points_per_topic_replies_min']) ? $modSettings['ssrs_points_per_topic_replies_min'] : '','" name="ssrs_points_per_topic_replies_min" />
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_per_sticky'].'
					</td>
					<td width="50%" valign="top">
						<input type="text" size="20" value="',!empty($modSettings['ssrs_points_per_sticky']) ? $modSettings['ssrs_points_per_sticky'] : '','" name="ssrs_points_per_sticky" />
					</td>
				</tr>
				<tr>
					<td class="ssrs_letter_option" align="left" width="50%" valign="top">
						'.$txt['admin_ssrs_points_per_warning'].'
					</td>
					<td width="50%" valign="top">
						<input type="text" size="20" value="',!empty($modSettings['ssrs_points_per_warning']) ? $modSettings['ssrs_points_per_warning'] : '','" name="ssrs_points_per_warning" />
					</td>
				</tr>
			</table>
			<hr class="ssrs_hr" />
			<div class="ssrs_red_box">
				<span class="ssrs_red_box_title">',$txt['ssrs_pasos_box_red'],'</span><br />
				<ol class="ssrs_pasos_box_red">
					<li>',$txt['ssrs_paso1_box_red'],'</li>
					<li>',$txt['ssrs_paso2_box_red'],'</li>
					<li>',$txt['ssrs_paso3_box_red'],'</li>
					<li>',$txt['ssrs_paso6_box_red'],'</li>
					<li>',$txt['ssrs_paso4_box_red'],'</li>
					<li>',$txt['ssrs_paso5_box_red'],'</li>
				</ol>
				<div align="center">',$txt['ssrs_manual_faqs'],'</div>
			</div>
			<table width="100%" cellspacing="0" cellpadding="0" >
				<tr>
					<td colspan="0" align="center">
						<br /><input type="submit" name="save" value="',$txt['admin_ssrs_save'],'" /><br />
					</td>
				</tr>
			</table>
		</form>
		<hr class="ssrs_hr" />
		<div align="center">
			<a href="http://www.smfsimple.com">
			SMFSimple Rewards System Mod By SMFSimple.com</a>
		</div>
	</div>';

}

function template_ssrs_permissions_board()
{
	global $context, $scripturl, $txt, $settings,  $ultimateportalSettings;
	echo'
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				'.$txt['admin_ssrs_permissions_board'].'
			</h3>
		</div>
		<form method="post" action="', $scripturl, '?action=admin;area=rewardPoints;sa=permissionsboard" accept-charset="', $context['character_set'], '">
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>
							<span><label for="admin_ssrs_permissions_board_enable">', $txt['admin_ssrs_permissions_board_enable'], '</label></span>
						</dt>
						<dd>
							<select name="boards[]" size="10" multiple="multiple" style="width: 88%;">';
							$id_boards = !empty($context['ssrs_boards_enabled']) ? $context['ssrs_boards_enabled'] : 0 ;
							foreach ($context['jump_to'] as $category)
							{
								echo '
								<option disabled="disabled">----------------------------------------------------</option>
								<option disabled="disabled">', $category['name'], '</option>
								<option disabled="disabled">----------------------------------------------------</option>';
								foreach ($category['boards'] as $board)
									echo '
									<option value="' ,$board['id'], '" ' ,isset($id_boards) ? (in_array($board['id'], $id_boards) ? 'selected="selected"' : '') : '', '> ' . str_repeat('&nbsp;&nbsp;&nbsp; ', $board['child_level']) . '|--- ' . $board['name'] . '</option>';
							}
							echo '
							</select>
						</dd>
					</dl>
					<hr class="hrcolor clear" />
					<div class="righttext">
						<input type="hidden" name="save" value="ok" />
						<input type="hidden" name="sc" value="', $context['session_id'], '" />
						<input type="submit" name="',$txt['save'],'" value="',$txt['save'],'" />
					</div>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</form>
	</div>';
}