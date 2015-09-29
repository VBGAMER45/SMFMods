<?php
/************************************************************************************
* E Arcade 3.0 (http://www.smfhacks.com)											*
* Copyright (C) 2014  http://www.smfhacks.com									   *
* Copyright (C) 2007  Eric Lawson (http://www.ericsworld.eu)						*
* based on the original SMFArcade mod by Nico - http://www.smfarcade.info/		  *																		   *
*************************************************************************************
* This program is free software; you can redistribute it and/or modify		 *
* it under the terms of the GNU General Public License as published by		 *
* the Free Software Foundation; either version 2 of the License, or			*
* (at your option) any later version.										  *
*																			  *
* This program is distributed in the hope that it will be useful,			  *
* but WITHOUT ANY WARRANTY; without even the implied warranty of			   *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the				*
* GNU General Public License for more details.								 *
*																			  *
* You should have received a copy of the GNU General Public License			*
* along with this program; if not, write to the Free Software				  *
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA *
********************************************************************************
* Arcade.template.php															   *
*																				   *
* Main Template file for arcade													 *
*************************************************************************************/

function template_arcade_above()
{
	global $settings, $context, $txt, $arcSettings, $scripturl, $db_count;

	$context['arcade']['queries_temp'] = $db_count;

		echo '<a name="top"></a>';

	// Show the start of the tab section.
	   $tab='<td nowrap="nowrap" style="cursor: pointer; font-size: 11px; padding: 6px 10px 6px 10px;  border: solid 1px #ADADAD;border-top: 0px; border-bottom:0px; border-left:0px" align="center" onmouseover="this.style.backgroundPosition=\'0 -5px\'" onmouseout="this.style.backgroundPosition=\'0 0px\'">';
	   $tab2='<td nowrap="nowrap" style="cursor: pointer; padding: 6px 6px 6px 6px;  border-top: 0px; border-bottom:0px;" align="center" onmouseover="this.style.backgroundPosition=\'0 -5px\'" onmouseout="this.style.backgroundPosition=\'0 0px\'">';


		$context['arcade']['buttons_set']['arcade'] =  array(
								'text' => 'arcade',
								'url' => $scripturl . '?action=arcade',
								'lang' => true,
						);

		$context['arcade']['buttons_set']['stats'] =  array(
								'text' => 'arcade_stats',
								'url' => $scripturl . '?action=arcade;sa=stats',
								'lang' => true,
						);

		$context['arcade']['buttons_set']['tour'] =  array(
								'text' => 'arcade_tour_tour',
								'url' => $scripturl . '?action=arcade;sa=tour',
								'lang' => true,
						);


		if ($context['arcade']['can_admin'])
		{
			$context['arcade']['buttons_set']['arcadeadmin'] =  array(
								'text' => 'arcade_admin',
								'url' => $scripturl . '?action=admin;area=managearcade',
								'lang' => true,
						);
	   }

	echo '
<div id="moderationbuttons" class="margintop">
	', Arcade_DoToolBarStrip($context['arcade']['buttons_set'], 'bottom'), '
</div>';


	echo '
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr class="catbg">';

	echo '<td width="100%" style=" border: solid 1px #ADADAD; border-bottom:0px; border-right:0px;border-top: 0px; border-left:0px" align="center">&nbsp;</td>
		</tr>
	</table>';
	//end of the tab table
}

// Game list
function template_arcade_list()
{
	global $sourcedir, $scripturl, $txt, $boardurl, $context, $settings, $arcSettings, $user_info;

	template_top_blocks();

	echo'

	<table class="bordercolor" border="0" width="100%" cellspacing="1" cellpadding="5">
		<tr class="titlebg">
			<td colspan="5" class="smalltext" style="padding: 8px; " valign="middle">', $txt['pages'], ': ', $context['arcade']['pageIndex'], '   &nbsp;&nbsp;<a href="#bot"><b>', $txt['go_down'], '</b></a></td>
		</tr>';

	// Is there games?
	if (count($context['arcade']['games']) > 0)
	{
		echo '
		<tr>
			<td class="catbg3"></td>
			<td class="catbg3">', $txt['arcade_game_name'], '</td>
			<td class="catbg3" style="width: 5%; text-align: center;">', $txt['arcade_plays'], '</td>
			<td nowrap="nowrap" class="catbg3" style="width: 5%; text-align: center;">', $txt['arcade_personal_best'],'</td>
			<td class="catbg3" style="width: 5%; text-align: center;">', $txt['arcade_champion'],'</td>
		</tr>';

		// Loop thought all games in page
		foreach ($context['arcade']['games'] as $game)
		{
			// Print out game information
			echo '
			<tr>
				<td class="windowbg2" style="width: 70px;" align="center">', $game['thumbnail'] != '' ? '
					<a href="' . $game['url']['play'] . '"><img width="70" height="70" src="' . $game['thumbnail'] . '" alt="'.$game['name'].'" title="'.$txt['arcade_champions_play'].' '.$game['name'].'"/></a>' : '', '
				</td>

				<td class="windowbg">
					<div style="float: left">
					<div><a href="', $game['url']['play'], '">', $game['name'], '</a></div>
					<div class="smalltext"><a href="javascript:popup(\''.$game['url']['pop'].'\',\''.$game['flash']['width'].'\',\''.$game['flash']['height'].'\')" >',$txt['arcade_popup'],'</a></div>';
					// Is there description?
					if (!empty($game['description']))
					echo '
					<div class="smalltext">', $game['description'], '</div>';


					if ($game['highscoreSupport']) // Does this game support highscores?
					echo '
					<div class="smalltext"><a href="' . $game['url']['highscore'] . '">' . $txt['arcade_viewscore'] . '</a></div>';

					if (!empty($game['topic_id']) && $arcSettings['arcadePostTopic']!=0)
					echo '
					<div class="smalltext"><a href="', $scripturl, '?topic=', $game['topic_id'], '">', $txt['arcade_topic_talk'],'</a></div></div>';

					echo '
					</div><div style="float: right; text-align: right;" class="smalltext">';
					// Rating

					if ($game['rating2'] > 0)
					echo '
					<div>',
					str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" />' , $game['rating2']),
					str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="" />' , 5 - $game['rating2']), '</div>';

					// Category

					if ($game['category']['name'] != '')
					echo '
					<a href="', $game['category']['link'], '">', $game['category']['name'], '</a><br />';

					if ($user_info['is_guest']) echo '';
					elseif (in_array(1, $user_info['groups'])) echo '<a href="', $game['url']['edit'], '">',$txt['arcade_edit'],'</a><br />';
					else echo '';

					// Favorite link (if can favorite)
					if ($context['arcade']['can_favorite'])
					echo '
					<a href="', $game['url']['favorite'], '" onclick="arcade_favorite(', $game['id'] , '); return false;">
					', !$game['isFavorite'] ?
					'<img id="favgame' . $game['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite.gif" alt="' . $txt['arcade_add_favorites'] . '" title="' . $txt['arcade_add_favorites'] . '"/>' :
					'<img id="favgame' . $game['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite2.gif" alt="' . $txt['arcade_remove_favorite'] .'" title="' . $txt['arcade_remove_favorite'] . '" />', '</a>';

					echo '
					</div>
				</td>

				<td class="windowbg2" style="width: 5%; text-align: center;">',$game['number_plays'],'</td>';

			// Show personal best and champion only if game doest support highscores
			if ($game['highscoreSupport'] && $game['isChampion'])
			{
				echo '
				<td class="windowbg2" style="width: 5%; text-align: center;">';

					if ($game['personalBest']>0 && $user_info['id']==$game['champion']['member_id'])
					{
						echo'<img src="'. $settings['images_url']. '/arc_icons/cup_g.gif" border="0" alt="cup_g" title="'.$txt['arcade_you_are_first'].' '.$game['name'].'"/><br />';
					}
					elseif ($game['personalBest']>0 && $user_info['id']==$game['secondPlace']['member_id'])
					{
						echo'<img src="'. $settings['images_url']. '/arc_icons/cup_s.gif" border="0" alt="cup_s" title="'.$txt['arcade_you_are_second'].' '.$game['name'].'" /><br />';
					}
					elseif ($game['personalBest']>0 && $user_info['id']==$game['thirdPlace']['member_id'])
					{
						echo'<img src="'. $settings['images_url']. '/arc_icons/cup_b.gif" border="0" alt="cup_b" title="'.$txt['arcade_you_are_third'].' '.$game['name'].'"/><br />';
					}

					echo $game['isPersonalBest'] ? $game['personalBest'] :  $txt['arcade_no_scores'];
					echo'
				</td>

				<td class="windowbg2" style="width: 15%; text-align: center;">
					<table width="100%">
						<tr>
							<td style="width: 10%; text-align: left;"><img src="'. $settings['images_url']. '/arc_icons/cup_g.gif" border="0" alt="gold" title="'.$txt['arcade_first'].'"/></td>
							<td style=" text-align: center;">', $game['champion']['memberLink'], ' </td>
							<td style="width: 15%; text-align: right;">', $game['champion']['score'], '</td>
						</tr>';
						if ($game['secondPlace']['score'] > 0)
						{
							echo'
							<tr>
								<td style="width: 10%; text-align: left;"><img src="'. $settings['images_url']. '/arc_icons/cup_s.gif" border="0" alt="silver" title="'.$txt['arcade_second'].'"/></td>
								<td>', $game['secondPlace']['memberLink'], ' </td>
								<td style="width: 15%; text-align: right;">', $game['secondPlace']['score'], '</td>
							</tr>';
						}
						if ($game['thirdPlace']['score'] > 0)
						{
						echo'
						<tr>
							<td style="width: 10%; text-align: left;"><img src="'. $settings['images_url']. '/arc_icons/cup_b.gif" border="0" alt="bronze" title="'.$txt['arcade_third'].'"/></td>
							<td>', $game['thirdPlace']['memberLink'], ' </td>
							<td style="width: 15%; text-align: right;">', $game['thirdPlace']['score'], '</td>
						</tr>';
						}
						echo'
					</table>
				</td>';
			}
			elseif (!$game['highscoreSupport'])
			{
				echo '
				<td class="windowbg2" colspan="2" style="text-align: center; width: 30%;">', $txt['arcade_no_highscore'], '</td>';
			}
			else
			{
				echo '
				<td class="windowbg2" colspan="2" style="text-align: center; width: 30%;">', $txt['arcade_no_scores'], '</td>';
			}

			echo '
		</tr>';
		}
	}
	else
	{
		// There is no games installed / found.
		echo '
		<tr>
		<td class="catbg3"><b>', $txt['arcade_no_games'], '</b></td>
		</tr>';
	}

	echo '
	</table>

	<table class="bordercolor" border="0" width="100%" cellspacing="1" cellpadding="5">
		<tr class="titlebg">
			<td colspan="4"  class="smalltext" style="padding:8px;" valign="middle">', $txt['pages'], ': ', $context['arcade']['pageIndex'], '   &nbsp;&nbsp;<a href="#top"><b>', $txt['go_up'], '</b></a></td>
		</tr>
	</table>';
	if (!$user_info['is_guest'] && $arcSettings['arcade_active_user']==1)
	{
		$context['arcade']['who'] = true;
		echo'
		<table class="bordercolor" border="0" width="100%" cellspacing="1" cellpadding="5">
		<tr>
		<td class="catbg" align="center" colspan="0">',$txt['who_arcade_active'],'</td>
		</tr>
		<tr>
				<td class="windowbg2" valign="bottom">
		';
		$i = 0;

	   foreach ($context['members'] as $member)
		 {
			if ((stristr($member['action'],"arcade"))&&stristr($member['action'],"20"))
			{
				if ($i != 0)
				{
					echo ' | ';
				}
				echo '
				 ', $member['action'], '
					<span', $member['is_hidden'] ? ' style="font-style: italic;"' : '', '>','
					<a href="#" onclick="window.open(\'',$scripturl,'?action=arcade;sa=pro_stats;ta=',$member['id'],'\',\'PopupWindow\',\'height=300,width=700,scrollbars=1,resizable=1\');return false;" title="' . $member['time'] . ' - ' . $member['ip'] . '"' . (empty($member['color']) ? '' : ' style="color: ' . $member['color'] . '"') . '>' . $member['name'] . '</a>', '</span>&nbsp;

				';

				$i++;
			}
		}
		echo '</td></tr></table>';
		$context['arcade']['who'] = false;
	}
}

function template_arcade_front_page()
{
	global $scripturl, $txt, $context, $settings;
	template_top_blocks();
	echo '
	<div class="bordercolor">
	<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">
		<tr class="catbg">
			<td colspan="4">',$context['arcade']['frontPage']['pageName'],'</td>
		</tr>
		<tr class="windowbg">';
		foreach($context['arcade']['frontPage']['games'] as $game)
		{
			$ratecode = '';
			$rating = $game['rating'];

			if ($rating > 0)
			{
				$ratecode = str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" />' , $rating);
				$ratecode .= str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" />' , 5 - $rating);
			}
			echo'
			<td width="25%"><div align="center">
				<table width="100%" border="0" cellpadding="1">
					<tr>
						<td colspan="2"><div align="center"><i><b>',$game['name'],'</b></i></div></td>
					</tr>
					<tr>
						<td align="center">
						', $game['thumbnail'] != '' ? '<a href="' . $game['url']['play'] . '"><img src="' . $game['thumbnail'] . '" width="60" height="60" alt="ico" title="'.$txt['arcade_play'].' '.$game['name'].'"/></a>' : '', '
						<div class="smalltext"><a href="', $game['url']['play'], '">', $game['name'], '</a>
						</div>
						</td>
					</tr>';
					if ($rating > 0)
					echo '
					<tr>
						<td align="center">', $ratecode, '</td>
					</tr>';

					echo '
					<tr>
						<td align="center"><div class="smalltext">';
						if ($game['isChampion'])
						echo '

						<strong>', $txt['arcade_champion'], ':</strong> ', $game['champion']['memberLink'], ' - ', $game['champion']['score'], '
						</div>';

						else
						echo $txt['arcade_no_scores'];

						echo '
						</div>
						</td>
					</tr>
				</table>
			</div></td>';
		}
		echo'
		</tr>
	</table>
	</div>';

}

function template_arcade_tour_show()
{
	global $scripturl, $txt, $context, $settings,  $user_info;

	template_top_blocks();
	arcade_tour_buttons();

	echo '
	<div class="bordercolor">
		<table border="0" width="100%" cellspacing="1" cellpadding="4">

			<tr class="titlebg">
				<td colspan="6" class="smalltext" style="padding: 8px; " valign="middle">', $txt['arcade_tour_tours'], ': ', $context['arcade']['tour']['pageindex'], '   &nbsp;&nbsp;<a href="#bot"><b>', $txt['go_down'], '</b></a></td>
			</tr>

			<tr class="titlebg">
				<td>&nbsp;</td>
				<td>',$txt['arcade_game_name'],'</td>
				<td>',$txt['arcade_tour_players'],'</td>
				<td>',$txt['arcade_tour_starter'],'</td>
				<td>',$txt['arcade_time'],'</td>
				<td>',$txt['arcade_tour_status'],'</td>
			</tr>';

			$i = 0;
			$a[0] = 'windowbg';
			$a[1] = 'windowbg2';
			if (isset($context['arcade']['tour']['list']))
			{
				foreach ($context['arcade']['tour']['list'] as $tour)
				{
					$password = $tour['password'] != "" ? '<i>'.$txt['arcade_tour_pass'].'</i>' : '';

					echo '<tr class="',$a[$i % 2],'">';
					if ($tour['id_member'] == $user_info['id'] || allowedTo('admin_arcade'))
					{
						echo '<td><a href="',$scripturl,'?action=arcade;sa=tour;ta=del;idd=',$tour['id_tour'],'"><img src="' . $settings['images_url'] . '/arc_icons/del.png" alt="*" /></a></td>';
					}
					else
					{
						echo '<td></td>';
					}

					echo '
					<td><a href="',$scripturl,'?action=arcade;sa=tour;ta=join;id=',$tour['id_tour'],'">',$tour['name'],'</a> ',$password,'</td>
					<td>',$tour['joined'],'/',$tour['players'],'</td>
					<td><a href="'.$scripturl.'?action=profile;u='.$tour['id_member'].'">',$tour['creator'],'</a></td>
					<td>',timeformat($tour['tour_start_time']),'</td>';

					if ($tour['active']==1)
					{
						echo '<td>' . $txt['arcade_running'] . '</td>';
					}
					elseif ($tour['active']==2)
					{
						echo '<td>' . $txt['arcade_ended'] . '</td>';
					}
					else
					{
						echo '<td><a href="',$scripturl,'?action=arcade;sa=tour;ta=join;id=',$tour['id_tour'],'">',$txt['arcade_tour_join'],'</a></td>';
					}

					echo '</tr>';
					$i++;
				}
			}
			else
			{
				echo '<tr class="',$a[$i % 2],'"><td colspan="6">',$txt['arcade_tour_no_tour'],'</td></tr>';
			}
			echo '
			<tr class="titlebg">
				<td colspan="6" class="smalltext" style="padding: 8px;" valign="middle">', $txt['arcade_tour_tours'], ': ', $context['arcade']['tour']['pageindex'], '   &nbsp;&nbsp;<a href="#top"><b>', $txt['go_up'], '</b></a></td>
			</tr>
		</table>
	</div>';

}

function template_arcade_tour_join()
{
	global $scripturl, $txt, $context, $settings,  $user_info;

	template_top_blocks();
	arcade_tour_buttons();

	$tours = &$context['arcade']['tour']['tourdata'];

	$i =1;
	$a[0] = 'windowbg';
	$a[1] = 'windowbg2';

	echo'
	<table border="0" width="100%" cellspacing="1" cellpadding="0" class="bordercolor">
		<tr class="titlebg">
			<td colspan="2" height="25">'.$txt['arcade_tour_tour'],' - ',$tours['name'],'</td>
		</tr>
		<tr class="windowbg">
			<td colspan="2" height="25" align="center" ><b><i>',$txt['arcade_tour_info'],'</b></i></td>
		</tr>
		<tr valign="top">
			<td width="35%">
				<table border="0" width="100%" cellspacing="1" cellpadding="5" class="bordercolor">
					<tr>
						<td class="windowbg">'.$txt['arcade_tour_tour'].'</td><td class="windowbg2">'.$tours['name'].'</td>
					</tr>
					<tr>
						<td class="windowbg">'.$txt['arcade_tour_started'].'</td><td class="windowbg2">'.$tours['creator'].'</td>
					</tr>
					<tr >
						<td class="windowbg">'.$txt['arcade_tour_players'].'</td><td class="windowbg2">'.$tours['players'].'</td>
					</tr>
					<tr>
						<td class="windowbg">'.$txt['arcade_tour_rounds'].'</td><td class="windowbg2">'.$tours['rounds'].'</td>
					</tr>
					<tr>
						<td class="windowbg">'.$txt['arcade_time'].'</td><td class="windowbg2">',timeformat($tours['tour_start_time']),'</td>
					</tr>
				</table>
			</td>
			<td align="center" class="windowbg2">';

	$joinedPlayers = &$context['arcade']['tour']['players'];
	$joined[] = 0;
	$i = 1;
	foreach($joinedPlayers as $key => $players)
	{
		$joined[] = $key;
	}


	if (isset($tours['passFailed']))
	{
		echo '<br /><font color="#FF0000" size="+1">',$txt['arcade_tour_wrong_pass'],'</font><br />';
	}

	//if your not in the list of joined members - show the join button
	if (!in_array($user_info['id'],$joined) && $tours['active'] < 1)
	{
		echo '<br /><form action="',$scripturl,'?action=arcade;sa=tour;ta=join;id=',$tours['id_tour'],';in=1" method="post">';
		if ($tours['password'] != "")
		{
			echo '',$txt['arcade_tour_pass'],': <input type="password" name="pass" /><br />';
		}
		echo '<input type="submit" value="',$txt['arcade_tour_join'],'" />
		</form>';
	}
	else
	{
		if (in_array($user_info['id'],$joined) && $tours['active']!=2)
		{
			echo '<br /><font size="+1">',$txt['arcade_tour_joined'],'</font><br /><br />';
		}
		elseif ($tours['active']==2)
		{
			echo '<br /><font size="+1">',$txt['arcade_tour_ended'],'</font><br /><br />';
			if (count($context['arcade']['tour']['winner'])==1)
			{
				echo'<img src="'. $settings['images_url']. '/arc_icons/cup_g.gif" border="0" alt="cup_g" title="'.$txt['arcade_you_are_first'].'"/>';
				echo '<font color="#FF0000" size="+1">&nbsp;' . $txt['arcade_txt_WINNER'] . '&nbsp;<img src="'. $settings['images_url']. '/arc_icons/cup_g.gif" border="0" alt="cup_g" title="'.$txt['arcade_you_are_first'].'"/><br /><br />',$context['arcade']['tour']['winner'][0],'</font><br />';

			}
			else
			{
				echo '<font color="#FF0000" size="+1">' . $txt['arcade_txt_itsadraw'] . '</font><br /><br />';
				foreach($context['arcade']['tour']['winner'] as  $winners)
				{
					echo '<font color="#FF0000">&nbsp;',$winners,'&nbsp;';
				}
				echo'</font><br />';
			}
		}
		else
		{
			echo '<font size="+1">',$txt['arcade_tour_cant_join'],'</font><br /><br />';
		}
	}

	echo '</td>
	</tr>';
	$i = 1;

	echo '<tr valign="top"><td colspan="2" class="windowbg2">
	<table border="0" cellspacing="0" cellpadding="5" class="bordercolor" width="100%">
	<tr>
	   <td class="windowbg" align="center" ><b><i>',$txt['arcade_tour_heading2'],'</b></i></td>
	</tr>
	</table>

	<table border="0" cellspacing="1" cellpadding="5" class="bordercolor">
		<tr>';

	echo '<td class="windowbg2"></td>';
	//add each player to the row
	foreach($joinedPlayers as $key => $players)
	{
		echo '<td class="windowbg" align="center"><a href="'.$scripturl.'?action=profile;u='.$key.'">'.$players['players'].'</a>';
		if (allowedTo('admin_arcade')&& $tours['active']!=2)
		{
			echo'<br /><a href="'.$scripturl.'?action=arcade;sa=tour;ta=delplay;tid='.$tours['id_tour'].';u='.$key.'"><img border="0" src="',$settings['images_url'],'/arc_icons/del2.png" alt="ico" width="10" height="10" title="'.$txt['arcade_tour_remove1'].'"/></a>&nbsp;';
			echo'<a href="'.$scripturl.'?action=arcade;sa=tour;ta=delplay;tid='.$tours['id_tour'].';u='.$key.';lower=1"><img border="0" src="',$settings['images_url'],'/arc_icons/del1.png" alt="ico" width="10" height="10" title="'.$txt['arcade_tour_remove2'].'"/></a></td>';
		}
	}
	echo '</tr>';

	foreach($context['arcade']['tour']['rounds'] as $key => $r)
	{
		echo '<tr>';
		echo '<td class="windowbg"><b><i>',$txt['arcade_tour_round'],' ',$i,' - ',$r['game_name'],'</b></i></td>';
		foreach($joinedPlayers as $id => $arr1)
		{
			$match = 0;
			if ($match == 0 && is_array($context['arcade']['tour']['scores']))
			{
				foreach($context['arcade']['tour']['scores'] as $k => $score)
				{
					if ($score['id_game'] == $r['id_game'] && $score['id_member'] == $id  &&  $score['round_number'] == $i)
					{
						echo'<td class="windowbg2" align="center">',$score['score'],'</td>';
						$match = 1;
					}
				}
			}

			if ($match == 0)
			{
				if ($user_info['id'] == $id)
				{
					echo'<td class="windowbg2" align="center"><a href="'.$scripturl.'?action=arcade;sa=tour;ta=play;tid='.$tours['id_tour'].';gid='.$r['id_game'].';rid=',$i,'">',$txt['arcade_tour_wait'],'</a></td>';
				}
				else
				{
					echo'<td class="windowbg2" align="center">',$txt['arcade_tour_wait'],'</td>';
				}
			}
		}
		echo '</tr>';
		$i++;
	}
	echo'
			<tr>';
	if ($tours['active']==2)
	{
		echo '<td class="windowbg" align="right"><b><i>' . $txt['arcade_txt_results'] . '</b></i></td>';
		foreach($joinedPlayers as $key => $players)
		{
			echo '<td class="windowbg2" align="center">'.$players['total'].'</td>';
		}
		echo '</tr>';
	}
	echo '</table>
			</td>
		</tr>
	</table>';
}

function template_arcade_tour_new()
{
	global $scripturl, $txt;
	//template_top_blocks();
	arcade_tour_buttons();

	//max players and max rounds
	$maxr = 10;
	$maxp = 10;

	//some styles so the divs look in line
	echo '<style type="text/css">
	<!--
	.maintour {
		width: 400px;
		text-align: center;
	}
	.lefty {
		text-align: right;
		float: left;
		width: 200px;
	}
	.righty {
	text-align: left;
		float: right;
		width: 200px;
	}
	.left {
		line-height: 26px;
	}
	.right {
		line-height: 25px;
		padding: 3px;
	}

	-->
	</style>

	<table border="0" width="100%" cellspacing="1" cellpadding="5" class="bordercolor">
		<tr class="catbg">
			<td colspan="2">'.$txt['arcade_tour_new_tour'].'</td>
		</tr>
		<tr class="windowbg">
			<td>
			<form name="tour" action="',$scripturl,'?action=arcade;sa=tour;ta=new;step=2" method="post">
			<input type="hidden" name="step" value="1" />
			<div class="maintour">
			  <div class="lefty">
				<div class="left">',$txt['arcade_game_name'],': </div>
				<div class="left">',$txt['arcade_tour_password'],': </div>
				<div class="left">',$txt['arcade_tour_many_players'],': </div>
				<div class="left">',$txt['arcade_tour_many_rounds'],':</div>
			  </div>
			  <div class="righty">
				<div class="right">
				  <input type="text" name="name" />
				</div>
				<div class="right">
				  <input type="password" name="pass" />
				</div>
				<div class="right">
				  <select name="players">';
								$i = 2;
								while($i <= $maxp)
								{
									echo '<option value="',$i,'">',$i,'</option>';
									$i++;
								}

								echo '</select>
				</div>
				<div class="right">
				  <select name="rounds" id="rounds" onchange="arcade_tour_games(rounds.value);">';
								$i = 0;
								while($i <= $maxr)
								{
									echo '<option value="',$i,'">',$i,'</option>';
									$i++;
								}

								echo '</select>
				  </div>
			  </div>
			</div>
			<div class="maintour" id="tourgames"></form></div>
			</td>
		</tr>
	</table>';
}


function arcade_tour_buttons()
{
	global $settings, $context, $txt, $arcSettings, $scripturl;
		//echo '<a name="top"></a>';

	// Show the start of the tab section.
	   $tab='<td nowrap="nowrap" style="cursor: pointer; font-size: 11px; padding: 6px 10px 6px 10px;  border: solid 1px #ADADAD;border-top: 0px; border-bottom:0px; border-left:0px" align="center" onmouseover="this.style.backgroundPosition=\'0 -5px\'" onmouseout="this.style.backgroundPosition=\'0 0px\'">';
	   $tab2='<td nowrap="nowrap" style="cursor: pointer; padding: 6px 6px 6px 6px;  border-top: 0px; border-bottom:0px;" align="center" onmouseover="this.style.backgroundPosition=\'0 -5px\'" onmouseout="this.style.backgroundPosition=\'0 0px\'">';


		if ($context['arcade']['tour']['show']!=0)
			$context['arcadetour']['buttons_set']['newtour'] =  array(
								'text' => 'arcade_tour_new_tour',
								'url' => $scripturl . '?action=arcade;sa=tour;ta=new',
								'lang' => true,
						);


		if ($context['arcade']['tour']['show']!=2)
			$context['arcadetour']['buttons_set']['activetour'] =  array(
								'text' => 'arcade_tour_show_active',
								'url' => $scripturl . '?action=arcade;sa=tour',
								'lang' => true,
						);


		if ($context['arcade']['tour']['show']!=1)
			   $context['arcadetour']['buttons_set']['finishedtour'] =  array(
								'text' => 'arcade_tour_show_finished',
								'url' => $scripturl . '?action=arcade;sa=tour;show=1',
								'lang' => true,
						);


	echo '
<div id="moderationbuttons2" class="margintop">
	', Arcade_DoToolBarStrip($context['arcadetour']['buttons_set'], 'bottom'), '
</div>';

	echo '
	<table cellpadding="0" cellspacing="0" border="0" width ="100%">
		<tr class="catbg">';

	echo '<td width="100%" style=" border: solid 1px #ADADAD; border-bottom:0px; border-right:0px;border-top: 0px; border-left:0px" align="center">&nbsp;</td>
		</tr>
	</table>';
	//end of the tab table
}

// Play screen
function template_arcade_game_play()
{
	global $scripturl, $txt, $context, $settings;

	echo '
	<div class="tborder">
		<table class="bordercolor" border="0" cellpadding="4" cellspacing="0" width="100%">
			<tr class="catbg">
				<td>', $context['arcade']['game']['name'], '</td>
			</tr>
			<tr class="windowbg">
				<td>
					<div style="text-align: center;">
					', $context['arcade']['game']['html'], '
					', !$context['arcade']['can_submit'] ? '<br /><b>' . $txt['arcade_cannot_save'] . '</b>' : '', '
					<br />', $context['arcade']['game']['help'], '
					</div>
				</td>
			</tr>';
			if ($context['arcade']['game']['isChampion'])
			{
				echo'
				<tr class="windowbg">
					<td>
						<div align = "center">
							<strong>', $txt['arcade_champion'], ':</strong> ', $context['arcade']['game']['champion']['memberLink'], ' - ', $context['arcade']['game']['champion']['score'], '&nbsp;&nbsp;&nbsp;&nbsp;';

							if ($context['arcade']['game']['isPersonalBest'])
							{
								echo '<strong>', $txt['arcade_personal_best'], ':</strong> ', $context['arcade']['game']['personalBest'];
							}
							echo'
							</div>
					</td>
				</tr>';
			}
			echo'
		</table>
	</div>';

}

// Highscore
function template_arcade_game_highscore()
{
	global $scripturl, $txt, $context, $settings,$arcSettings;

	$game = &$context['arcade']['game'];

	echo '<div >
	<table class="bordercolor" border="0" cellpadding="4" cellspacing="1" width="100%">';

	if (isset($context['arcade']['new_score'])) // Was score submitted
	{
		$score = &$context['arcade']['new_score'];
		$ratecode = '';
		$rating = $context['arcade']['game']['rating'];
		if ($context['arcade']['can_rate'])
		{
			// Can rate

			for ($i = 1; $i <= 5; $i++)
			{
				if ($i <= $rating)
				$ratecode .= '<a href="' . $scripturl . '?action=arcade;sa=rate;game=' . $context['arcade']['game']['id'] . ';rate=' . $i . ';sesc=' . $context['session_id'] . '" onclick="arcade_rate(' . $i . ', ' . $context['arcade']['game']['id'] . '); return false;"><img id="imgrate' . $i . '" src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" /></a>';

				else
				$ratecode .= '<a href="' . $scripturl . '?action=arcade;sa=rate;game=' . $context['arcade']['game']['id'] . ';rate=' . $i . ';sesc=' . $context['session_id'] . '" onclick="arcade_rate(' . $i . ', ' . $context['arcade']['game']['id'] . '); return false;"><img id="imgrate' . $i . '" src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" /></a>';
			}
		}
		else
		{
			// Can't rate
			$ratecode = str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" />' , $rating);
			$ratecode .= str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" />' , 5 - $rating);
		}
		echo '
		<tr class="titlebg">
				<td colspan="5">', $txt['arcade_submit_score'],' ',$game['name'], '</td>
		</tr>
		<tr class="windowbg">
			<td colspan="3" style="text-align: center;">
				<table align="center">
					<tr>
						<td align="center">
						', $context['arcade']['game']['thumbnail'] != '' ? '<div><a href="' .$scripturl . '?action=arcade;sa=play;game=' . $context['arcade']['game']['id'] . '"><img src="' . $context['arcade']['game']['thumbnail'] . '" alt="icon" title="'.$txt['arcade_play'].' '.$game['name'].'"/></a></div>' : '', '
						</td>
					</tr>
					<tr>
						<td align="center">',$txt['arcade_rate_game'],' ',$game['name'],' ', $ratecode, '</td>
					</tr>';
					// Favorite link (if can favorite)
					if ($context['arcade']['can_favorite'])
					{
						echo '
						<tr>
							<td align="center">
							<a href="', $context['arcade']['game']['url']['favorite'], '" onclick="arcade_favorite(', $context['arcade']['game']['id'], '); return false;">', !$context['arcade']['game']['isFavorite'] ?  ''.$txt['arcade_add_favorites'].' <img id="favgame' . $context['arcade']['game']['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite.gif" alt="' . $txt['arcade_add_favorites'] . '" />' : '' . $txt['arcade_remove_favorite'] .' <img id="favgame' . $context['arcade']['game']['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite2.gif" alt="' . $txt['arcade_remove_favorite'] .'" />', '</a>
							</td>
						</tr>';
					}
					echo'
					<tr>
						<td align="center"><a href="' .$scripturl . '?action=arcade;sa=play;game=' . $context['arcade']['game']['id'] . '">',$txt['arcade_play_again'],'</a></td>
					</tr>
					<tr>
						<td align="center"><a href="javascript:popup(\''.$game['url']['pop'].'\',\''.$game['flash']['width'].'\',\''.$game['flash']['height'].'\')" >' . $txt['arcade_popup'] . '</a></td>
					</tr
					<tr>
						<td align="center"><a href="' .$scripturl . '?action=arcade">',$txt['arcade_play_other'],'</a></td>
					</tr>
				</table>
			</td>
			<td colspan="2" style="text-align: center;">';
				if ($context['arcade']['game']['isChampion'])
				echo '
				<div>
				<strong>', $txt['arcade_champion'], ':</strong> ', $context['arcade']['game']['champion']['memberLink'], ' - ', $context['arcade']['game']['champion']['score'], '&nbsp;&nbsp;&nbsp;&nbsp;';

				if ($context['arcade']['game']['isPersonalBest'])
				echo '
				<strong>', $txt['arcade_personal_best'], ':</strong> ', $context['arcade']['game']['personalBest'], '
				</div>';


				if (!$score['saved'])
					// No permission to save
					echo '<br />
					<div><strong>', $txt['arcade_txt_your'], $txt['arcade_score'], ':</strong> ', $score['score'], '<br /><br />
					', $txt[$score['error']], '<br /> </div>';

				else
				{
					echo '<br />
					<div><strong>', $txt['arcade_txt_your'], $txt['arcade_score'], ':</strong> ', $score['score'], '<br /><br />
					', $txt['arcade_score_saved'], '<br /> </div>';


					if ($score['is_new_champion'])
						echo '
						<div>', $txt['arcade_you_are_now_champion'], '</div>';

					elseif ($score['is_personal_best'])
						echo '
						<div>', $txt['arcade_this_is_your_best'], '</div>';

					if ($score['can_comment'])
						echo '
						<div id="edit', $score['id'], '">
							<form action="', $scripturl, '?action=arcade;sa=comment;game=', $game['id'], ';score=',  $score['id'], '" onsubmit="arcadeCommentEdit(', $score['id'], ', ', $game['id'], ', 1); return false;" method="post">
								<input type="text" id="c', $score['id'], '" name="comment" style="width: 95%;" />
								<input type="submit" value="', $txt['arcade_save'], '" />
							</form>
						</div>';
				}

				if ($arcSettings['arcadePostTopic']!=0)
				{
					echo '<div><br /><a href="', $scripturl, '?topic=', $game['topic_id'], '">', $txt['arcade_topic_talk2'],' ',$game['name'], ' here</a></div>';
				}

				echo '</td>
		</tr>';
	}

	if (count($context['arcade']['scores']) > 0) // There must be more than zero scores or we will skip them :)
	{
		if (!isset($context['arcade']['new_score'])) // Was score submitted
		{
		$ratecode = '';
		$rating = $context['arcade']['game']['rating'];
		if ($context['arcade']['can_rate'])
		{
			// Can rate

			for ($i = 1; $i <= 5; $i++)
			{
				if ($i <= $rating)
				$ratecode .= '<a href="' . $scripturl . '?action=arcade;sa=rate;game=' . $context['arcade']['game']['id'] . ';rate=' . $i . ';sesc=' . $context['session_id'] . '" onclick="arcade_rate(' . $i . ', ' . $context['arcade']['game']['id'] . '); return false;"><img id="imgrate' . $i . '" src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" /></a>';

				else
				$ratecode .= '<a href="' . $scripturl . '?action=arcade;sa=rate;game=' . $context['arcade']['game']['id'] . ';rate=' . $i . ';sesc=' . $context['session_id'] . '" onclick="arcade_rate(' . $i . ', ' . $context['arcade']['game']['id'] . '); return false;"><img id="imgrate' . $i . '" src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" /></a>';
			}
		}
		else
		{
			// Can't rate
			$ratecode = str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" />' , $rating);
			$ratecode .= str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" />' , 5 - $rating);
		}

		echo '
		<tr class="windowbg">
			<td align="center" colspan="5">
					<table align="center">
						<tr>
							<td align="center">
							', $context['arcade']['game']['thumbnail'] != '' ? '<div><a href="' .$scripturl . '?action=arcade;sa=play;game=' . $context['arcade']['game']['id'] . '"><img src="' . $context['arcade']['game']['thumbnail'] . '" alt="icon" title="'.$txt['arcade_play'].' '.$game['name'].'"/></a></div>' : '', '
							</td>
						</tr>
						<tr>
							<td align="center">',$txt['arcade_rate_game'],' ',$game['name'],' ', $ratecode, '</td>
						</tr>';
						// Favorite link (if can favorite)
						if ($context['arcade']['can_favorite'])
						{
						echo '
						<tr>
							<td align="center">
							<a href="', $context['arcade']['game']['url']['favorite'], '" onclick="arcade_favorite(', $context['arcade']['game']['id'], '); return false;">', !$context['arcade']['game']['isFavorite'] ?  ''.$txt['arcade_add_favorites'].' <img id="favgame' . $context['arcade']['game']['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite.gif" alt="' . $txt['arcade_add_favorites'] . '" />' : '' . $txt['arcade_remove_favorite'] .' <img id="favgame' . $context['arcade']['game']['id'] . '" src="' . $settings['images_url'] . '/arc_icons/favorite2.gif" alt="' . $txt['arcade_remove_favorite'] .'" />', '</a>
							</td>
						</tr>';
						}
						echo'
						<tr>
							<td align="center"><a href="' .$scripturl . '?action=arcade;sa=play;game=' . $context['arcade']['game']['id'] . '">',$txt['arcade_play'],' ',$game['name'],'</a></td>
						</tr>
						<tr>
							<td align="center"><a href="javascript:popup(\''.$game['url']['pop'].'\',\''.$game['flash']['width'].'\',\''.$game['flash']['height'].'\')" >',$txt['arcade_popup'],'</a></td>
						</tr>
					</table>
				</td>
			</tr>';
		}
			echo'
			<tr class="titlebg">
				<td colspan="5" height="25px" class="smalltext">', $txt['arcade_highscores'], ' ', isset($context['arcade']['pageIndex']) ? ' ' . $context['arcade']['pageIndex'] : '' ,'</td>
			</tr>
			<tr class="catbg3">
				<td width="50px">', $txt['arcade_position'], '</td>
				<td width="150px">', $txt['arcade_member'], '</td>
				<td width="50px">', $txt['arcade_score'], '</td>
				<td width="250px">', $txt['arcade_time'], '</td>
				<td>', $txt['arcade_comment'], '</td>
			</tr>';

		$button['edit'] = create_button('modify.gif', 'arcade_edit', '', 'title="' . $txt['arcade_edit'] . '"');
		$button['delete'] = create_button('delete.gif', 'arcade_delete_score', '', 'title="' . $txt['arcade_delete_score'] . '"');

		foreach ($context['arcade']['scores'] as $score)
		{
			echo '
			<tr class="', $score['own'] ? 'windowbg3' : 'windowbg', '"', $score['highlight'] ? ' style="font-weight: bold;"' : '', '>
				<td class="windowbg2" align="center">', $score['position'], '</td>
				<td>', $score['memberLink'], '</td>
				<td  class="windowbg2">', $score['score'], '</td>
				<td width="300" align="center">', $score['time'], '</td>
				<td class="windowbg2">
					<div id="comment', $score['id'], '" style="float: left; ', $score['edit'] && $score['can_edit'] ? 'display: none;' : '', '">', $score['comment'], '</div>';
						if ($score['can_edit']) // Can edit
						{
							echo '
							<div id="edit', $score['id'], '" style="float: left; ', $score['edit'] ? '' : 'display: none;', ' width: 90%;">
								<form action="', $scripturl, '?action=arcade;sa=comment;game=', $game['id'], '" method="post" name="score_edit', $score['id'], '" onsubmit="arcadeCommentEdit(', $score['id'], ', ', $game['id'], '); return false;">
									<input type="hidden" name="score" value="', $score['id'], '" />
									<input type="text" name="comment" id="c', $score['id'], '" value="', $score['raw_comment'], '" style="width: 95%;" />
								</form>
							</div>';
						}
						// Buttons
						if ($score['can_edit'] || $context['arcade']['show_editor'])
						{
							echo '<div style="float: right">';

							// Edit
							if ($score['can_edit'])
								echo '<a onclick="arcadeCommentEdit(', $score['id'], ', ', $game['id'], ', 0); return false;" href="', $scripturl, '?action=arcade;sa=highscore;game=', $game['id'], ';edit;score=', $score['id'], '">', $button['edit'], '</a>';

							// Delete
							if ($context['arcade']['show_editor'])
								echo '<a onclick="return confirm(\'', $txt['arcade_really_delete'], '\');" href="', $scripturl, '?action=arcade;sa=highscore;game=', $game['id'], ';delete;score=', $score['id'], ';sesc=', $context['session_id'], '">', $button['delete'], '</a>';

							echo '</div>';
						}

			echo '</td>
				</tr>';
		}
		echo '
				<tr class="catbg3">
					<td>', $txt['arcade_position'], '</td>
					<td>', $txt['arcade_member'], '</td>
					<td>', $txt['arcade_score'], '</td>
					<td>', $txt['arcade_time'], '</td>
					<td>', $txt['arcade_comment'], '</td>
				</tr>';
		}
		else
		{
			// No one has played this game
			echo '
			<tr class="windowbg">
				<td align="center" class="catbg3"><b>', $txt['arcade_no_scores'], '</b></td>
			</tr>';
		}
	echo '<tr class="titlebg">
					<td colspan="5" class="smalltext" height="25px">', $txt['arcade_highscores'], ' ', isset($context['arcade']['pageIndex']) ? ' ' . $context['arcade']['pageIndex'] : '' ,'</td>
				</tr>
			</table>
		</div>';
}

function template_arcade_statistics()
{
	global $scripturl, $txt, $context, $settings, $arcSettings;

	echo '
	<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
		<tr class="titlebg">
				<td align="center" colspan="4">', $txt['arcade_stats'], '</td>
		</tr>
		<tr class="windowbg">
			<td colspan="4">',sprintf($txt['arcade_game_we_have_games'], $arcSettings['arcade_total_games']),'<br />
			',$txt['arcade_champions_tgp'],' ',$context['arcade']['statistics']['total'],'</td>
		</tr>
		<tr>
			<td class="catbg" colspan="2"><b>', $txt['arcade_most_played'], '</b></td>
			<td class="catbg" colspan="2"><b>', $txt['arcade_most_active'], '</b></td>
		</tr>
		<tr>
				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/arc_icons/gold.gif" alt="" /></td>
				<td class="windowbg2" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';

	// Most played games
	if ($context['arcade']['statistics']['play'] != false)
	{
		foreach ($context['arcade']['statistics']['play'] as $game)
			echo '
							<tr>
								<td width="60%" valign="top">', $game['link'], '</td>
								<td width="20%" align="left" valign="top">', $game['plays'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $game['precent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
								<td width="20%" align="right" valign="top">', $game['plays'], '</td>
							</tr>';
	}

	echo '
					</table>
				</td>

				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/arc_icons/gold.gif" alt="" /></td>
				<td class="windowbg2" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';

	// Most active in arcade
	if ($context['arcade']['statistics']['active'] != false)
	{
		foreach ($context['arcade']['statistics']['active'] as $game)
			echo '
							<tr>
								<td width="60%" valign="top">', $game['link'], '</td>
								<td width="20%" align="left" valign="top">', $game['scores'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $game['precent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
								<td width="20%" align="right" valign="top">', $game['scores'], '</td>
							</tr>';
	}
	echo '
					</table>
				</td>
		</tr>

		<tr>
			<td class="catbg" colspan="2"><b>', $txt['arcade_best_games'], '</b></td>
			<td class="catbg" colspan="2"><b>', $txt['arcade_best_players'], '</b></td>
		</tr>

		<tr>
				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/arc_icons/gold.gif" alt="" /></td>
				<td class="windowbg2" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';

	// Top rated games
	if ($context['arcade']['statistics']['rating'] != false)
	{
		foreach ($context['arcade']['statistics']['rating'] as $game)
			echo '
							<tr>
								<td width="60%" valign="top">', $game['link'], '</td>
								<td width="20%" align="left" valign="top">', $game['rating'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $game['precent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
								<td width="20%" align="right" valign="top">', $game['rating'], '</td>
							</tr>';
	}

	echo '
					</table>
				</td>

				<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/arc_icons/gold.gif" alt="" /></td>
				<td class="windowbg2" valign="top">
					<table border="0" cellpadding="1" cellspacing="0" width="100%">';

	// Best players by champions
	if ($context['arcade']['statistics']['champions'] != false)
	{
		foreach ($context['arcade']['statistics']['champions'] as $game)
			echo '
							<tr>
								<td width="60%" valign="top">', $game['link'], '</td>
								<td width="20%" align="left" valign="top">', $game['champions'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $game['precent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
								<td width="20%" align="right" valign="top">', $game['champions'], '</td>
							</tr>';
	}

	echo '
					</table>
				</td>
		</tr>

		<tr>
			<td class="catbg" colspan="4"><b>', $txt['arcade_longest_champions'], '</b></td>
		</tr>
		<tr>
			<td class="windowbg" width="20" valign="middle" align="center"><img src="', $settings['images_url'], '/arc_icons/gold.gif" alt="" /></td>
			<td class="windowbg2" valign="top" colspan="3">
				<table border="0" cellpadding="1" cellspacing="0" width="100%">';

	// Top rated games
	if ($context['arcade']['statistics']['longest'] != false)
	{
		foreach ($context['arcade']['statistics']['longest'] as $game)
			echo '
						<tr>
							<td width="40%" valign="top">', $game['member_link'], ' (', $game['game_link'], ')</td>
							<td width="20%" align="left" valign="top">', $game['duration'] > 0 ? '<img src="' . $settings['images_url'] . '/bar_stats.png" width="' . $game['precent'] . '" height="15" alt="" />' : '&nbsp;', '</td>
							<td width="40%" align="right" valign="top">', $game['current'] ? '<b>' . $game['duration'] . '</b>' : $game['duration'], '</td>
						</tr>';
	}

	echo '
				</table>
			</td>
		</tr>
	</table>';
}

function template_arcade_below()
{
	global $arcade_version, $arcSettings, $context, $db_count;
	$m_time = explode(" ",microtime());
	$loadend = $m_time[0] + $m_time[1];

	$loadtotal = ($loadend - $context['loadstart']);

	$context['arcade']['queries_temp'] = $db_count - $context['arcade']['queries_temp'];
	$aracde_queries = $context['arcade']['queries']+$context['arcade']['queries_temp'];
	// Print out copyright and version. Removing copyright is not allowed by license
	echo '
	<a name="bot"></a>
	<div style="text-align: center;">
		<span  class="smalltext"><small><em>Generated Arcade page in ', round($loadtotal,3) ,' seconds with ',$aracde_queries,' queries.</em></small> <br />
		Powered by <a href="http://www.smfhacks.com/" target="_blank">E-Arcade ', $arcSettings['arcadeVersion'],'</a> </span>
	</div>';

}

// XML templates

function template_xml() // General XML template
{
	global $context, $txt;

	$extra = isset($context['arcade']['extra']) ? $context['arcade']['extra'] : '';

	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
	<smf>
		<txt><![CDATA[', isset($txt[$context['arcade']['message']]) ? $txt[$context['arcade']['message']] : $context['arcade']['message'], ']]></txt>
		', $extra, '
	</smf>';
}

function template_xml_list()
{
	global $context, $txt;


	echo '<', '?xml version="1.0" encoding="', $context['character_set'], '"?', '>
	<smf>';

	foreach ($context['arcade']['search']['games'] as $game)
		echo '
		<game>
			<id>', $game['id'], '</id>
			<name><![CDATA[', $game['name'], ']]></name>
			<url><![CDATA[', $game['url'], ']]></url>
		</game>';

	echo '
		<more>
			<is>', $context['arcade']['search']['more'], '</is>
			<url>', $context['arcade']['search']['more_url'], '</url>
		</more>
	</smf>';
}

function template_poparc_stats()
{
	global $context, $settings ,$txt;

echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
<title>' . $txt['arcade_champions_stats'] . '</title>
<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style', $context['theme_variant'], '.css?b4" />
<style type="text/css">
<!--
body {
	padding: 0px 0px 0px 0px;
}
-->
</style>
</head>
<body>
<table width="100%"  border="0" cellpadding="0" cellspacing="0" >
  <tr class="titlebg">
	<td colspan="6" height="26"><img src="'. $settings['images_url']. '/icons/profile_sm.gif" alt="" align="top" />&nbsp;' . $txt['arcade_champions_stats'] . '</td>
  </tr>
	<tr>
	<td class="windowbg" width="20" rowspan="2" valign="middle" style="padding: 5px 5px 5px 5px;"><img src="'. $settings['images_url']. '/stats_info.gif" width="20" height="20" alt="" /></td>
	<td class="windowbg2" width="20%" height="30" align="center" style="padding: 5px 5px 5px 5px;"><img src="'. $settings['images_url']. '/arc_icons/cup_g.gif" border="0" alt="cup" />&nbsp;' . $txt['arcade_champions_th'] . '<br />'.$context['arcade']['champ_stats']['gold'].'</td>
	<td class="windowbg2" width="20%" height="30" align="center" style="padding: 5px 5px 5px 5px;"><img src="'. $settings['images_url']. '/arc_icons/cup_s.gif" border="0" alt="cup" />&nbsp;' . $txt['arcade_champions_th'] . '<br />'.$context['arcade']['champ_stats']['silver'].'</td>
	<td class="windowbg2" width="20%" height="30" align="center" style="padding: 5px 5px 5px 5px;"><img src="'. $settings['images_url']. '/arc_icons/cup_b.gif" border="0" alt="cup" />&nbsp;' . $txt['arcade_champions_th'] . '<br />'.$context['arcade']['champ_stats']['bronze'].'</td>
	<td class="windowbg2" width="20%" height="30" align="center" style="padding: 5px 5px 5px 5px;"><img src="'. $settings['images_url']. '/arc_icons/play.gif" width="14" height="14" alt="play" />&nbsp;' . $txt['arcade_champions_tgp'] . '<br />'.$context['arcade']['champ_stats']['total_plays'].'</td>
	<td class="windowbg2" width="20%" height="30" align="center" style="padding: 5px 5px 5px 5px;"><img src="'. $settings['images_url']. '/arc_icons/tick.gif" width="14" height="14" alt="time" />&nbsp;' . $txt['arcade_champions_tsp'] . '<br />'.$context['arcade']['champ_stats']['timeplayed'].'</td>
  </tr>
  <tr>
	<td valign="top"colspan="5"><table width="100%" border="0" cellpadding="5" cellspacing="1">
		<tr class="titlebg">
		<td height="23" width="10"></td>
		  <td height="23">'.$txt['arcade_game'].'</td>
		  <td>'.$txt['arcade_score'].'</td>
		  <td>'.$txt['arcade_plays'].'</td>
		  <td>'.$txt['arcade_champions_cf'].'</td>
		</tr>
		';
							foreach($context['arcade']['champ_pro_gold']as $tmpx)
								{
									echo '
							<tr class="windowbg2" cellspacing="1">
								 <td height="30">
									<img src="'. $settings['images_url']. '/arc_icons/cup_g.gif" border="0" alt="cup" /></td>
								<td height="30">
									<a href="'.$tmpx['linkurl'].'"><img border="0" src="'. $tmpx['thumbnail']. '" alt="test" width="20" height="20"></a>&nbsp;'.$tmpx['game_name'].'&nbsp;
								</td>
								<td>'.round($tmpx['score'],2).'</td>
								<td>'.$tmpx['my_plays'].'</td>
								<td>'.$tmpx['time'].'</td>
							</tr>';

								}
									foreach($context['arcade']['champ_pro_silver']as $tmpx)
								{
									echo '
							<tr class="windowbg2" cellspacing="1">
								 <td height="30">
									<img src="'. $settings['images_url']. '/arc_icons/cup_s.gif" border="0" alt="cup" /></td>
								<td height="30">
									<a href="'.$tmpx['linkurl'].'"><img border="0" src="'. $tmpx['thumbnail']. '" alt="test" width="20" height="20"></a>&nbsp;'.$tmpx['game_name'].'&nbsp;
								</td>
								<td>'.round($tmpx['score'],2).'</td>
								<td>'.$tmpx['my_plays'].'</td>
								<td>'.$tmpx['time'].'</td>
							</tr>';

								}
								foreach($context['arcade']['champ_pro_bronze']as $tmpx)
								{
									echo '
							<tr class="windowbg2" cellspacing="1">
								 <td height="30">
									<img src="'. $settings['images_url']. '/arc_icons/cup_b.gif" border="0" alt="cup" /></td>
								<td height="30">
									<a href="'.$tmpx['linkurl'].'"><img border="0" src="'. $tmpx['thumbnail']. '" alt="test" width="20" height="20"></a>&nbsp;'.$tmpx['game_name'].'&nbsp;
								</td>
								<td>'.round($tmpx['score'],2).'</td>
								<td>'.$tmpx['my_plays'].'</td>
								<td>'.$tmpx['time'].'</td>
							</tr>';

								}
								echo'
	  </table></td>
  </tr>
</table><br />
<div align="center"><a href="javascript:self.close();">'. $txt['arcade_close']. '</a></div>
</body>
</html>';
}


function template_top_blocks()
{
	global $settings, $context, $txt, $arcSettings, $scripturl,$user_info;

	//some user defined vars....
	//number of categries to show accross the search row
	$per_line = 4; //min of 2
	//cat icon width and height---
	$cat_width = 20;
	$cat_height = 20;
	//remember they wont change until arcade cache has been cleared!!


	echo '<table class="bordercolor"  width="100%" cellspacing="1" cellpadding="5">';
				//start warning notice
			 	if (isset($context['arcade']['notice']))
			 	{
			 		echo'<tr class="windowbg2">
						<td colspan="3" align="center" style="color: red;">' . $context['arcade']['notice'] . '</td>
					</tr>';
				}
				//end warning notice
				//start arcade news
					if ($arcSettings['arcadeNewsFader']!=0)
					{
						if ($arcSettings['enable_arcade_cache']==1)
						{
						//CACHE NEWS FADER
						ob_start();
							if (!$cacheFader = readCache('newsFader.cache', 300))
							{
								echo'
								<tr>';
									ArcadeInfoFader();
									echo'
							</tr>';

							$cacheFader = ob_get_contents();
							 	ob_clean();
							 	writeCache($cacheFader,'newsFader.cache');
							 	ob_end_clean();
							}
 							echo $cacheFader;
 						}
 						else
 						{
 							echo'
								<tr>';
									ArcadeInfoFader();
									echo'
							</tr>';
 						}

				  }
				  //END CACHE NEWS FADER

				  echo'<tr>
					<td class="windowbg2" valign="top" width="275">';
						//Right Block
						ArcadeInfoPanelBlock();
						echo'
					</td>
					<td class="windowbg" valign="top" >';
						//top Centre Block
				  	ArcadeUserBlock();
				  	echo'
				  </td>
				  <td class="windowbg2" valign="top" width="275">';
					  if ($context['arcade']['can_admin'] || $arcSettings['enable_arcade_cache']==0 )
					  {
					  	ArcadeInfoShouts();
					  }
					  else
					  {
					  	ob_start();
								if (!$cacheShout = readCache('shout.cache', 86400))
								{

										ArcadeInfoShouts();

								$cacheShout = ob_get_contents();
								 	ob_clean();
								 	writeCache($cacheShout,'shout.cache');
								 	ob_end_clean();
								}
	 							echo $cacheShout;
					  }
				  	echo'
				  </td>
					</tr>';
					$curr = 1;
					$pcent= 100 / $per_line;
					echo'
					<tr class="windowbg">
						<td colspan="3">
							<table width="100%" border="0" cellspacing="0">
								<tr><td class="windowbg2" valign="top">
										<table width="100%" border="0" cellpadding="3" cellspacing="0">
											<tr>
												<td align="center" colspan="3"><span><i><b>', $txt['arcade_Gamecategory'], '</b></i></span></td>
											</tr>';
												ArcadeQuickSearchBlock();
											echo'
											<tr>
												<td align="center" colspan="3"><hr /></td>
											</tr>
										</table>
									<table width="100%" border="0" cellpadding="3" cellspacing="0">';
									//START CACHE - get the cats stuff from cache if its upto date or create new
									if ($arcSettings['enable_arcade_cache']==1)
									{
										ob_start();
										if (!$cacheCats = readCache('cats.cache', 604800))
										{
										$cats = category_games();
										echo'
												<tr>
													<td  width="',$pcent,'%" align="left" valign="middle">&nbsp;&nbsp;<a href="',$scripturl,'?action=arcade;sort=idr;desc=DESC"><img border="0" src="',$settings['images_url'],'/arc_icons/cat_new.gif" alt="ico" width="',$cat_width,'" height="',$cat_height,'" title="Show Latest"/> ' , $txt['arcade_LatestGames'] , ' (',$arcSettings['gamesPerPage'],')</a></td>';
													foreach( $cats as $id => $tmp)
													{
														if ($curr == $per_line)
														{
															echo '</tr><tr valign="middle">';
															$curr=0;
														}
														echo'<td  width="',$pcent,'%" align="left" >&nbsp;&nbsp;<a href="',$scripturl,'?action=arcade;category=',$tmp['id_category'],'"><img border="0" src="',$settings['images_url'],'/arc_icons/',$tmp['category_icon'],'" alt="ico" width="',$cat_width,'" height="',$cat_height,'" title="Show ',$tmp['category_name'],'"/> ',$tmp['category_name'],' (',$tmp['games'],')</a></td>';
														$curr++;
														//$gif++;
													}echo'
												</tr>
											</table>
										</td>';
									 	$cacheCats = ob_get_contents();
										ob_clean();
										writeCache($cacheCats,'cats.cache');
										ob_end_clean();
									  }
								  	echo $cacheCats;
								  }
								  else
								  {
								  		$cats = category_games();
											echo'
												<tr>
													<td  width="',$pcent,'%" align="left" valign="middle">&nbsp;&nbsp;<a href="',$scripturl,'?action=arcade;sort=idr;desc=DESC"><img border="0" src="',$settings['images_url'],'/arc_icons/cat_new.gif" alt="ico" width="',$cat_width,'" height="',$cat_height,'" title="Show Latest"/> Latest (',$arcSettings['gamesPerPage'],')</a></td>';
													foreach( $cats as $id => $tmp)
													{
														if ($curr == $per_line)
														{
															echo '</tr><tr valign="middle">';
															$curr=0;
														}
														echo'<td  width="',$pcent,'%" align="left" >&nbsp;&nbsp;<a href="',$scripturl,'?action=arcade;category=',$tmp['id_category'],'"><img border="0" src="',$settings['images_url'],'/arc_icons/',$tmp['category_icon'],'" alt="ico" width="',$cat_width,'" height="',$cat_height,'" title="Show ',$tmp['category_name'],'"/> ',$tmp['category_name'],' (',$tmp['games'],')</a></td>';
														$curr++;
														//$gif++;
													}echo'
												</tr>
											</table>
										</td>';

								  }
									//END CATEGORY CACHE
									echo'
								</tr>
							</table>
						</td>
					</tr>
				</table>
	';
}

function ArcadeQuickSearchBlock()
{
	global $txt, $context, $scripturl;

	echo '
	<tr>
		<td align="right">
			<form action="', $scripturl, '?action=arcade" onsubmit="ArcadeQuickSearch(); return false;" method="post">
				<input id="quick_name" type="text" value="" name="name[', rand(0, 1000), ']" onkeyup="ArcadeQuickSearch();" />
			</form>
		</td>
		<td align="center" width="275">
			<div id="quick_div" class="smalltext">', $txt['arcade_quick_search'], '</div>
		</td>
		<td align="left">
			<script type="text/javascript">
			function go()
			{
			location = document.cmform.gowhere.value;
			}
			</script>
			<form name="cmform" action="">
				<select id="gowhere" onchange="go()">
					<option>',$txt['arcade_list_games'],'</option>
					<option value="',$scripturl,'?action=arcade;sort=name;">',$txt['arcade_nameAZ'],'</option>
					<option value="',$scripturl,'?action=arcade;sort=name;desc=DESC">',$txt['arcade_nameZA'],'</option>
					<option value="',$scripturl,'?action=arcade;sort=id;desc=DESC">',$txt['arcade_LatestList'],'</option>
					<option value="',$scripturl,'?action=arcade;sort=plays;desc=DESC">',$txt['arcade_g_i_b_3'],'</option>
					<option value="',$scripturl,'?action=arcade;sort=champs;desc=DESC">',$txt['arcade_g_i_b_8'],'</option>
					<option value="',$scripturl,'?action=arcade;sort=plays">',$txt['arcade_LeastPlayed'],'</option>
					<option value="',$scripturl,'?action=arcade;sort=category">',$txt['arcade_category'],'</option>
					<option value="',$scripturl,'?action=arcade;sort=rating;desc=DESC">',$txt['arcade_rating_sort'],'</option>
					<option value="',$scripturl,'?action=arcade;sort=myscore;desc=DESC">',$txt['arcade_personal_best'],'</option>
					<option value="',$scripturl,'?action=arcade;sort=champion">',$txt['arcade_champion'],'</option>
				</select>
			</form>
		</td>
	</tr>';
}

function ArcadeUserBlock()
{
	global $context, $txt, $user_info, $scripturl, $settings, $arcSettings;

	echo'
	<div align="center">
	<table width="100%" border="0" cellpadding="1">
		<tr>
			<td colspan="2"><div align="center"><span><i><b>',$txt['arcade_u_b_1'],' ',$context['user']['name'],'</b></i></span></div></td>
		</tr>
		<tr>
			<td height="155px"><div align="center"> ';
				if (isset($context['user']['avatar']['image']))
				{
					echo  $context['user']['avatar']['image'];
				}
				else
				{
					echo' <img border="0" src="',$settings['images_url'],'/icons/online.gif" alt="ico" width="50" height="50" title="Default Avatar"/>';
				}
				echo'<br />';
				echo'<br /><div class="smalltext"><a href="',$scripturl,'?action=arcade;favorites"><img border="0" src="',$settings['images_url'],'/arc_icons/arcade_cat1.gif" alt="ico" width="15" height="15" title="Show favs"/> ',$txt['arcade_u_b_2'],'
				<img border="0" src="',$settings['images_url'],'/arc_icons/arcade_cat1.gif" alt="ico" width="15" height="15" title="Show favs"/></a>
				</div></div>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="smalltext"><div align="center">',sprintf($txt['arcade_game_we_have_games'], $arcSettings['arcade_total_games']),'</div></td>
		</tr>';
		if ((!$user_info['is_guest'] && $arcSettings['enable_shout_box_members']==1)||$context['arcade']['can_admin'])
		{
		echo'
		<tr>
		 <td>
	   <form accept-charset="', $context['character_set'], '" class="smalltext" style="padding: 0; margin: 0; margin-top: 5px; text-align: center;" name="arcade_shout" action="'.$scripturl.'?action=arcade;sa=shout" method="post">
				<textarea class="smalltext" rows="5" cols="50" name="the_shout" style="width: 80%;margin-top: 1ex; height: 25px;" wrap="auto"></textarea><br />
		 <input style="margin-top: 4px;" class="smalltext" type="submit" name="shout" value="'.$txt['arcade_shout'].'" />
	   </form>
	  </td>
	 </tr>';
	}
	echo'
	</table>
	</div>';
}



function ArcadeInfoPanelBlock()
{
	global $context, $txt, $arcSettings, $scripturl;

$no = 5;
	echo'
	<table width="100%" border="0" cellpadding="1">
		<tr>
			<td colspan="2"><div align="center"><span><i><b>',$txt['arcade_info'],'</b></i></span></div></td>
		</tr>
		<tr>
			<td><span class="middletext">';

			echo'
	<script type="text/javascript">
		var pausecontent=new Array()
		';
	if ($arcSettings['enable_arcade_cache']==1)
	{
		ob_start();
		if (!$cache3Best = readCache('arcadeinfopanel.cache', 86400))
		{
			echo 'pausecontent[0]="',addslashes(ArcadeInfoBestPlayers($no)),'";
			';
			echo 'pausecontent[1]="',addslashes(ArcadeInfoNewestGames($no)),'";
			';
			echo 'pausecontent[2]="',addslashes(Arcade3champsBlock($no)),'";
			';
			echo 'pausecontent[3]="',addslashes(ArcadeInfoMostPlayed($no)),'";
			';
			echo 'pausecontent[4]="',addslashes(ArcadeInfoLongestChamps($no)),'";
			';
			$cache3Best = ob_get_contents();
			ob_clean();
			writeCache($cache3Best,'arcadeinfopanel.cache');
			ob_end_clean();
		}
		echo $cache3Best;

		ob_start();
		if (!$cacheGotd = readCache('gotd.cache', 86400))
		{
			echo 'pausecontent[5]="',addslashes(ArcadeGOTDBlock()),'";
			';
			$cacheGotd = ob_get_contents();
			ob_clean();
			writeCache($cacheGotd,'gotd.cache');
			ob_end_clean();
		}
		echo $cacheGotd;
	}
	else
	{
		echo 'pausecontent[0]="',addslashes(ArcadeInfoBestPlayers($no)),'";
			';
			echo 'pausecontent[1]="',addslashes(ArcadeInfoNewestGames($no)),'";
			';
			echo 'pausecontent[2]="',addslashes(Arcade3champsBlock($no)),'";
			';
			echo 'pausecontent[3]="',addslashes(ArcadeInfoMostPlayed($no)),'";
			';
			echo 'pausecontent[4]="',addslashes(ArcadeInfoLongestChamps($no)),'";
			';
			echo 'pausecontent[5]="',addslashes(ArcadeGOTDBlock()),'";
			';
	}
	echo 'pausecontent[6]="',addslashes(ArcadeRandomGameBlock()),'";
	';

	echo'</script>
	<style type="text/css">
	#pescroller1{
	height: 220px;
	border: 0px solid black;
	padding: 5px;
	}

	.someclass{ //class to apply to your escroller(s) if desired
	}

	</style>
	<script type="text/javascript">

	/***********************************************
	* Pausing up-down escroller- © Dynamic Drive (www.dynamicdrive.com)
	* This notice MUST stay intact for legal use
	* Visit http://www.dynamicdrive.com/ for this script and 100s more.
	***********************************************/

	function pauseescroller(content, divId, divClass, delay){
	this.content=content //message array content
	this.tickerid=divId //ID of ticker div to display information
	this.delay=delay //Delay between msg change, in miliseconds.
	this.mouseoverBol=0 //Boolean to indicate whether mouse is currently over escroller (and pause it if it is)
	this.hiddendivpointer=1 //index of message array for hidden div
	document.write(\'<div id="\'+divId+\'" class="\'+divClass+\'" style="position: relative; overflow: hidden"><div class="innerDiv" style="position: absolute; width: 100%" id="\'+divId+\'1">\'+content[0]+\'</div><div class="innerDiv" style="position: absolute; width: 100%; visibility: hidden" id="\'+divId+\'2">\'+content[1]+\'</div></div>\')
	var escrollerinstance=this
	if (window.addEventListener) //run onload in DOM2 browsers
	window.addEventListener("load", function(){escrollerinstance.initialize()}, false)
	else if (window.attachEvent) //run onload in IE5.5+
	window.attachEvent("onload", function(){escrollerinstance.initialize()})
	else if (document.getElementById) //if legacy DOM browsers, just start escroller after 0.5 sec
	setTimeout(function(){escrollerinstance.initialize()}, 500)
	}
	// -------------------------------------------------------------------
	// initialize()- Initialize escroller method.
	// -Get div objects, set initial positions, start up down animation
	// -------------------------------------------------------------------

	pauseescroller.prototype.initialize=function(){
	this.tickerdiv=document.getElementById(this.tickerid)
	this.visiblediv=document.getElementById(this.tickerid+"1")
	this.hiddendiv=document.getElementById(this.tickerid+"2")
	this.visibledivtop=parseInt(pauseescroller.getCSSpadding(this.tickerdiv))

	this.visiblediv.style.width=this.hiddendiv.style.width=this.tickerdiv.offsetWidth-(this.visibledivtop*2)+"px"
	this.getinline(this.visiblediv, this.hiddendiv)
	this.hiddendiv.style.visibility="visible"
	var escrollerinstance=this
	document.getElementById(this.tickerid).onmouseover=function(){escrollerinstance.mouseoverBol=1}
	document.getElementById(this.tickerid).onmouseout=function(){escrollerinstance.mouseoverBol=0}
	if (window.attachEvent) //Clean up loose references in IE
	window.attachEvent("onunload", function(){escrollerinstance.tickerdiv.onmouseover=escrollerinstance.tickerdiv.onmouseout=null})
	setTimeout(function(){escrollerinstance.animateup()}, this.delay)
	}

	// -------------------------------------------------------------------
	// animateup()- Move the two inner divs of the escroller up and in sync
	// -------------------------------------------------------------------

	pauseescroller.prototype.animateup=function(){
	var escrollerinstance=this
	if (parseInt(this.hiddendiv.style.top)>(this.visibledivtop+5)){
	this.visiblediv.style.top=parseInt(this.visiblediv.style.top)-5+"px"
	this.hiddendiv.style.top=parseInt(this.hiddendiv.style.top)-5+"px"
	setTimeout(function(){escrollerinstance.animateup()}, 50)
	}
	else{
	this.getinline(this.hiddendiv, this.visiblediv)
	this.swapdivs()
	setTimeout(function(){escrollerinstance.setmessage()}, this.delay)
	}
	}

	// -------------------------------------------------------------------
	// swapdivs()- Swap between which is the visible and which is the hidden div
	// -------------------------------------------------------------------

	pauseescroller.prototype.swapdivs=function(){
	var tempcontainer=this.visiblediv
	this.visiblediv=this.hiddendiv
	this.hiddendiv=tempcontainer
	}

	pauseescroller.prototype.getinline=function(div1, div2){
	div1.style.top=this.visibledivtop+"px"
	div2.style.top=Math.max(div1.parentNode.offsetHeight, div1.offsetHeight)+"px"
	}

	// -------------------------------------------------------------------
	// setmessage()- Populate the hidden div with the next message before it\'s visible
	// -------------------------------------------------------------------

	pauseescroller.prototype.setmessage=function(){
	var escrollerinstance=this
	if (this.mouseoverBol==1) //if mouse is currently over scoller, do nothing (pause it)
	setTimeout(function(){escrollerinstance.setmessage()}, 100)
	else{
	var i=this.hiddendivpointer
	var ceiling=this.content.length
	this.hiddendivpointer=(i+1>ceiling-1)? 0 : i+1
	this.hiddendiv.innerHTML=this.content[this.hiddendivpointer]
	this.animateup()
	}
	}

	pauseescroller.getCSSpadding=function(tickerobj){ //get CSS padding value, if any
	if (tickerobj.currentStyle)
	return tickerobj.currentStyle["paddingTop"]
	else if (window.getComputedStyle) //if DOM2
	return window.getComputedStyle(tickerobj, "").getPropertyValue("padding-top")
	else
	return 0
	}

	</script>
	<script type="text/javascript">

	//new pauseescroller(name_of_message_array, CSS_ID, CSS_classname, pause_in_miliseconds)
	new pauseescroller(pausecontent, "pescroller1", "someclass", 2000)
	document.write("<br />")
	</script>
	</span>
	</td>
	</tr>
	</table>
	';

}

function ArcadeInfoFader()
{
	global $context, $txt, $arcSettings, $scripturl;

	$a_news = arcade_news_fader($arcSettings['arcadeNewsFader'], $arcSettings['arcadeNewsNumber']);
	$i=0;

	echo'
	<div align="center">

	<td height="50" class="windowbg2" colspan = "3"><span>
	<script type="text/javascript"><!-- // --><![CDATA[

	/***********************************************
	* Fading Scroller- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
	* This notice MUST stay intact for legal use
	* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
	***********************************************/

	var delay = 5000; //set delay between message change (in miliseconds)
	var maxsteps=30; // number of steps to take to change from start color to endcolor
	var stepdelay=40; // time in miliseconds of a single step
	//**Note: maxsteps*stepdelay will be total time in miliseconds of fading effect
	var startcolor= new Array(255,255,255); // start color (red, green, blue)
	var endcolor=new Array(0,0,0); // end color (red, green, blue)

	var fcontent=new Array();
	begintag=\'<div align="center"style=" padding: 5px;">\'; //set opening tag, such as font declarations
	';
	foreach($a_news as $news_out)
	{
			echo 'fcontent[',$i,']="',addslashes($news_out['body']),'";
			';
		$i++;
	}
	echo '
	closetag=\'</div>\';

	var fwidth=\'100%\'; //set scroller width
	var fheight=\'30px\'; //set scroller height
	var ie4=document.all&&!document.getElementById;
	var DOM2=document.getElementById;
	var index=0;

	function changecontent(){
	if (index>=fcontent.length)
	index=0
	if (DOM2){
	document.getElementById("fscroller").innerHTML=begintag+fcontent[index]+closetag
	setTimeout("changecontent()", delay);
	}
	else if (ie4)
	document.all.fscroller.innerHTML=begintag+fcontent[index]+closetag;
	index++
	}

	if (ie4||DOM2)
	document.write(\'<div id="fscroller" style="border:0px solid black;width:\'+fwidth+\';height:\'+fheight+\'"></div>\');

	if (window.addEventListener)
	window.addEventListener("load", changecontent, false)
	else if (window.attachEvent)
	window.attachEvent("onload", changecontent)
	else if (document.getElementById)
	window.onload=changecontent
	// ]]></script>
	</span>
	</td>

	</div>';
}

?>