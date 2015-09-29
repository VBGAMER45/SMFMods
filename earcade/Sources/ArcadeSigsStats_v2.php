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
ArcadeSigsStats_v2.php
*****************************************************************************
This file contains the functions needed for:
Game Icons or Cups in members sigs
Game Icons or Cups in members post profile
Arcade Stats to the members profile
A popup of the members Arcade Stats
*****************************************************************************

arcade_champs_post_profile()
		-outputs the cups or icons in the members post profile

arcade_champs_sig()
		-outputs the cups or icons in the members signature

build_champ_sigs()
		-gets and formats the champ data for each game

build_champ_profile()
		-gets and formats the data for members acrade stats and the arcade popup window

template_poparc_stats()
		-the output for the popup

Arcade_pro_stats()
		-sets up the popup window

hhmmss()
		-turns seconds in to readable time

/********************************************************************************/

//Make sure we have language
LoadLanguage('Arcade');
global $sourcedir;
require_once($sourcedir . '/Subs-Arcade.php');
loadArcadeSettings();

function arcade_champs_post_profile($message)
{
	global $context, $settings, $scripturl,$sourcedir,$arcSettings, $txt, $user_info;
	if ($user_info['id']!=0)
	{
	$icon_type=$arcSettings['arcade_champion_pp'];

	if (!$message['member']['is_guest'])
	{
		echo'&nbsp;<a href="#" onclick="window.open(\'',$scripturl,'?action=arcade;sa=pro_stats;ta=',$message['member']['id'],'\',\'PopupWindow\',\'height=300,width=700,scrollbars=1\');return false;"><img src="'. $settings['images_url']. '/arc_icons/arc.gif" width="18" height="18" border="0" alt="Stats" title="' . $txt['arcade_champions_stats'] . '" ></a>';
		if ($icon_type !=2)
		{
			if (@is_array($context['arcade']['championsGold'][ $message['member']['id'] ]))
			{
				$k=0;
				$j=0;
				echo $icon_type==1 ? "<br /><br />" . $txt['arcade_champions_cho'].'<br />'  :"<br /><br />". $txt['arcade_champions_tro'].'<br />';
				foreach($context['arcade']['championsGold'][ $message['member']['id'] ] as $tmp)
				{
					if ($icon_type==0)
					{
						echo '<a href="',$tmp['url'],'"><img src="'. $settings['images_url']. '/arc_icons/gold.gif" border="0" alt="cup" title="' . $txt['arcade_champions_play'] .' ', $tmp['game_name'],'"></a>&nbsp;';
						$j++;
						$k++;

						if ($j==6)// change the 6 to how many cups to display across each row
						{
							echo'<br />';
							$j=0;
						}

						if ($k==$arcSettings['arcade_champions_in_post'])
						{
							echo'<br /><a href="#" onclick="window.open(\'',$scripturl,'?action=arcade;sa=pro_stats;ta=',$message['member']['id'],'\',\'PopupWindow\',\'height=300,width=700,scrollbars=1\');return false;">More>></a>';
							break;
						}
					}
					elseif ($icon_type==1)
					{
						if ($tmp['thumbnail'] != '')
						{
							echo '<a href="',$tmp['url'],'"><img border="0" src="', $tmp['thumbnail'], '" alt="" width="20" height="20" title="', $txt['arcade_champions_play'],' ', $tmp['game_name'],'"></a>&nbsp;';
							$j++;
							$k++;

							if ($j==4)// change the 4 to how many icons to display across each row
							{
								echo'<br />';
								$j=0;
							}
						}
						if ($k==$arcSettings['arcade_champions_in_post'])
						{
							echo'<br /><a href="#" onclick="window.open(\'',$scripturl,'?action=arcade;sa=pro_stats;ta=',$message['member']['id'],'\',\'PopupWindow\',\'height=300,width=700,scrollbars=1\');return false;">More>></a>';
							break;
						}
					}
				}
			}
		}
	}
}
	return;
}

function arcade_champs_sig($message)
{

	global $context, $settings, $options, $arcSettings, $txt, $user_info;
	$icon_type=$arcSettings['arcade_champion_sig'];

	//if cups or icons are on, its not a guest post or its not a guest viewing we do some stuff..
	if (($icon_type!=2)&&(!$message['member']['is_guest'])&& ($user_info['id']!=0))
	{
		echo '<div style="overflow: auto; width: 100%; padding-bottom: 3px;" class="signature">';
		//if we are showing cups..we need gold, silver and bronze
		if ($icon_type==0)//cups
		{

			$cups = array();
			$k=0;
			if (@is_array($context['arcade']['championsGold'][ $message['member']['id'] ]))
			{
				foreach($context['arcade']['championsGold'][ $message['member']['id'] ] as $tmp)
				{
					$cups[]='<a href="'.$tmp['url'].'"><img src="'.$settings['images_url'].'/arc_icons/cup_g.gif" border="0" alt="cup" title="'.$txt['arcade_champions_play'].' '.$tmp['game_name'].'"></a>&nbsp;';
					$k++;
				}
			}
			if (@is_array($context['arcade']['championsSilver'][ $message['member']['id'] ]) && $k < $arcSettings['arcade_champions_in_post'])
			{
				foreach($context['arcade']['championsSilver'][ $message['member']['id'] ] as $tmp)
				{
					$cups[]='<a href="'.$tmp['url'].'"><img src="'.$settings['images_url'].'/arc_icons/cup_s.gif" border="0" alt="cup" width="10" height="14" title="'.$txt['arcade_champions_play'].' '.$tmp['game_name'].'"></a>&nbsp;';
					$k++;
				}
			}
			if (@is_array($context['arcade']['championsBronze'][ $message['member']['id'] ])&& $k < $arcSettings['arcade_champions_in_post'])
			{
				foreach($context['arcade']['championsBronze'][ $message['member']['id'] ] as $tmp)
				{
					$cups[]='<a href="'.$tmp['url'].'"><img src="'.$settings['images_url'].'/arc_icons/cup_b.gif" border="0" alt="cup" width="10" height="14" title="'.$txt['arcade_champions_play'].' '.$tmp['game_name'].'"></a>&nbsp;';
					$k++;
				}
			}

			//output the cups up until the number allowed to show
			$k=0;
			foreach($cups as $cup)
			{
				echo $cup;
				$k++;
				if ($k == $arcSettings['arcade_champions_in_post'])break;
			}
		}
		//it must be icons then...
		else
		{
			//were only going to show icons for wins...no silvers, bronzes
			if (@is_array($context['arcade']['championsGold'][ $message['member']['id'] ]))
			{
				$k=0;
				foreach($context['arcade']['championsGold'][ $message['member']['id'] ] as $tmp)
				{

						if ($tmp['thumbnail'] != '')
						{
							echo '<a href="',$tmp['url'],'"><img border="0" src="', $tmp['thumbnail'], '" alt="" width="20" height="20" title="', $txt['arcade_champions_play'],' ', $tmp['game_name'],'"></a>&nbsp;';
						}
						else
						{
							echo '', $tmp['name'], '&nbsp;';
						}
						$k++;
						if ($k==$arcSettings['arcade_champions_in_post'])break;

				}
			}
	}
		echo'</div>';
		return;
	}
}

function build_champ_sigs($posters)
{
	global $smcFunc, $context, $arcSettings, $scripturl;

	if (($arcSettings['arcade_champion_pp']!=2)||($arcSettings['arcade_champion_sig']!=2))
	{
		$arcade_champg = array();
		$arcade_champs = array();
		$arcade_champb = array();
		$who = '';

		foreach($posters as $p)
		{

			if ($arcSettings['arcade_champion_sig']==0)
			{
			$who.= $p.' IN (id_member_first,id_member_second,id_member_third) OR ';
			}
			else
			{
				$who.= 'id_member_first = '.$p.' OR ';
			}
		}

		$result = $smcFunc['db_query']('', '
		SELECT
		id_game,
		internal_name,
		game_name,
		thumbnail,
		game_directory,
		id_member_first,
		id_member_second,
		id_member_third
		FROM {db_prefix}arcade_games
		WHERE enabled = 1 AND '.$who.' 1',
		array(
		)
	);

		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
				$arcade_champg[$row['id_member_first']][$row['internal_name']] = $row;
				$arcade_champg[$row['id_member_first']][$row['internal_name']]['url'] = $scripturl . '?action=arcade;sa=play;game=' . $row['id_game'];
				$arcade_champg[$row['id_member_first']][$row['internal_name']]['thumbnail']  = !$row['game_directory'] ?	$arcSettings['gamesUrl'].$row['thumbnail'] : $arcSettings['gamesUrl'].$row['game_directory']."/".$row['thumbnail'];
				//if were showing cups in the sig we need the silver and bronze
				if ($arcSettings['arcade_champion_sig']==0)
				{
				$arcade_champs[$row['id_member_second']][$row['internal_name']] = $row;
				$arcade_champs[$row['id_member_second']][$row['internal_name']]['url'] = $scripturl . '?action=arcade;sa=play;game=' . $row['id_game'];
				$arcade_champs[$row['id_member_second']][$row['internal_name']]['thumbnail']  = !$row['game_directory'] ?	$arcSettings['gamesUrl'].$row['thumbnail'] : $arcSettings['gamesUrl'].$row['game_directory']."/".$row['thumbnail'];

				$arcade_champb[$row['id_member_third']][$row['internal_name']] = $row;
				$arcade_champb[$row['id_member_third']][$row['internal_name']]['url'] = $scripturl . '?action=arcade;sa=play;game=' . $row['id_game'];
				$arcade_champb[$row['id_member_third']][$row['internal_name']]['thumbnail']  = !$row['game_directory'] ?	$arcSettings['gamesUrl'].$row['thumbnail'] : $arcSettings['gamesUrl'].$row['game_directory']."/".$row['thumbnail'];
				}
		}
		$context['arcade']['championsGold'] = $arcade_champg;
		$context['arcade']['championsSilver'] = $arcade_champs;
		$context['arcade']['championsBronze'] = $arcade_champb;
	}
}

function build_champ_profile($memID)
{
	global $smcFunc, $context, $scripturl, $arcSettings;

	//setup some stuff we need..so its all zero if there is nothing to show
	$arcade_champg = array();
	$arcade_champs = array();
	$arcade_champb = array();
	$stats = array();
	$stats['total_plays']=0;
	$stats['timeplayed']=0;
	$stats['gold']=0;
	$stats['silver']=0;
	$stats['bronze']=0;

			$result = $smcFunc['db_query']('', '
		SELECT
		g.id_game, g.internal_name,
		g.game_name,g.thumbnail, g.game_directory,
		g.id_member_first, g.id_member_second, g.id_member_third,
		b.score, b.my_plays, b.playing_time, b.time_gained
		FROM {db_prefix}arcade_personalbest AS b
		LEFT JOIN  {db_prefix}arcade_games AS g ON (b.id_game = g.id_game)
		WHERE b.id_member = {int:mem}',
		array(
		'mem' => $memID,
		)
	);
	if ($result)
	{

	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$stats['total_plays'] += $row['my_plays'];
		$stats['timeplayed'] +=$row['playing_time'];

		if ($row['id_member_first']==$memID)
		{
			$arcade_champg[$row['internal_name']] = $row;
			$arcade_champg[$row['internal_name']]['linkurl'] = $scripturl . '?action=arcade;sa=play;game=' . $row['id_game'];
			$arcade_champg[$row['internal_name']]['time'] = timeformat($row['time_gained']);
			$arcade_champg[$row['internal_name']]['thumbnail']  = !$row['game_directory'] ?	$arcSettings['gamesUrl'].$row['thumbnail'] : $arcSettings['gamesUrl'].$row['game_directory']."/".$row['thumbnail'];

			$stats['gold']++;
		}
		elseif ($row['id_member_second']==$memID)
		{
			$arcade_champs[$row['internal_name']] = $row;
			$arcade_champs[$row['internal_name']]['linkurl'] = $scripturl . '?action=arcade;sa=play;game=' . $row['id_game'];
			$arcade_champs[$row['internal_name']]['time'] = timeformat($row['time_gained']);
			$arcade_champs[$row['internal_name']]['thumbnail']  = !$row['game_directory'] ?	$arcSettings['gamesUrl'].$row['thumbnail'] : $arcSettings['gamesUrl'].$row['game_directory']."/".$row['thumbnail'];

			$stats['silver']++;
		}
		elseif ($row['id_member_third']==$memID)
		{
			$arcade_champb[$row['internal_name']] = $row;
			$arcade_champb[$row['internal_name']]['linkurl'] = $scripturl . '?action=arcade;sa=play;game=' . $row['id_game'];
			$arcade_champb[$row['internal_name']]['time'] = timeformat($row['time_gained']);
			$arcade_champb[$row['internal_name']]['thumbnail']  = !$row['game_directory'] ?	$arcSettings['gamesUrl'].$row['thumbnail'] : $arcSettings['gamesUrl'].$row['game_directory']."/".$row['thumbnail'];

			$stats['bronze']++;
		}

	}
}
	//format the time
	$stats['timeplayed'] = hhmmss($stats['timeplayed']);

	$context['arcade']['champ_pro_gold'] = $arcade_champg;
	$context['arcade']['champ_pro_silver'] = $arcade_champs;
	$context['arcade']['champ_pro_bronze'] = $arcade_champb;
	$context['arcade']['champ_stats']= $stats;
}


function Arcade_pro_stats()
{
	global $context;

	build_champ_profile($_GET['ta']);
	$context['template_layers'] = array();

    loadTemplate('Arcade');

	$context['sub_template'] = 'poparc_stats';
}

function hhmmss($length)
{
	$hrs = floor($length / 3600);
	$min = $length - $hrs * 3600;
	$min = floor($min / 60);
	$sec = $length - $hrs * 3600 - $min * 60;
	return	str_pad($hrs,2,'0',STR_PAD_LEFT) . 'h:' .
		str_pad($min,2,'0',STR_PAD_LEFT) . 'm:' .
		str_pad($sec,2,'0',STR_PAD_LEFT) . 's';
}

?>