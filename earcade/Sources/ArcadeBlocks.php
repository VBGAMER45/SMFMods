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
ArcadeBlocks.php

This file contains functions for the top info blocks
********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');



function Arcade3champsBlock($no)
{
	global $scripturl, $txt, $settings;

	$top_player = ArcadeLatestChamps($no);
	$content = '<div align="center"><table width="100%" border="0" cellpadding="1"><tr><td colspan="2"><div align="center"><i><b>'.$no.' '.$txt['arcade_g_i_b_8'].'</b></i></div></td></tr>';
	if ($top_player != false)
	{
		if (is_array($top_player))
		foreach ($top_player as $row)
		{
			$content .= '<tr><td height="25"><div align="right"><img src="'.$settings['images_url'].'/arc_icons/cup_g.gif" alt="ico"/></div></td><td><div class="middletext"><div align="left"> - '.$row['member_link'].' '.$txt['is_champ_of'].' '.$row['game_link'].'</div></div></td></tr>';
		}
	}
	$content.='</table></div>';

	return $content;
}

function ArcadeGOTDBlock()
{
	global $context, $txt, $arcSettings, $settings;

	$condition = 'AND g.id_game = ' . (int) $arcSettings['arcadegotd'] .' LIMIT 0,1';
	$gamex = small_game_query($condition);
	$ratecode = '';

	$content ='<div align="center"><table width="100%" border="0" cellpadding="1"><tr><td><div align="center"><i><b>'.$txt['arcade_game_of_day'].'</b></i></div></td></tr>';

	if (is_array($gamex))
	foreach($gamex as $game)
	{
		$rating = $game['rating'];

		if ($rating > 0)
		{
			$ratecode = str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="s" />' , $rating);
			$ratecode .= str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="s" />' , 5 - $rating);
		}

		$content .='<tr><td><div align="center">';

		if ($game['thumbnail'] != '')
		{
			$content .='<br /><a href="' . $game['url']['play'] . '"><img src="' . $game['thumbnail'] . '" width="80" height="80" alt="ico" title="'.$txt['arcade_play'].' '.$game['name'].'"/></a><br /><br />';

		}

		$content .='<div class="middletext"><a href="'. $game['url']['play']. '">'. $game['name']. '</a></div></div></td></tr>';


		if ($rating > 0)
		$content .='<tr><td align="center">'. $ratecode. '</td></tr>';

		$content .='<tr><td align="center"><div class="middletext">';

		if ($game['isChampion'])
		$content .= '<strong>'. $txt['arcade_champion']. ':</strong> '. $game['champion']['memberLink']. ' - '. $game['champion']['score']. '</div>';

		else
		$content .= $txt['arcade_no_scores'];
	}
	$content .= '</div></td></tr></table></div>';
	return $content;
}

function ArcadeRandomGameBlock()
{
	global $context, $txt, $arcSettings, $settings;

	$condition = 'ORDER BY RAND() LIMIT 0,1';
	$gamex = small_game_query($condition);
	foreach($gamex as $game)
	{
	$ratecode = '';
	$rating = $game['rating'];

	if ($rating > 0)
	{
		$ratecode = str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star.gif" alt="*" />' , $rating);
		$ratecode .= str_repeat('<img src="' . $settings['images_url'] . '/arc_icons/star2.gif" alt="*" />' , 5 - $rating);
	}

	$content ='<div align="center"><table width="100%" border="0" cellpadding="1"><tr><td colspan="2"><div align="center"><i><b>'.$txt['arcade_random_game'].'</b></i></div></td></tr>';
	$content .='<tr><td align="center"><br /><a href="'.$game['url']['play'] .'"><img src="'.$game['thumbnail'].'" width="80" height="80" alt="ico" title="'.$txt['arcade_play'].' '.$game['name'].'"/></a><div class="middletext"><a href="'.$game['url']['play'].'"><br />'.$game['name'].'</a></div></td></tr>';

	//echo $content;
	if ($rating > 0)
		$content .='<tr><td align="center">'.$ratecode.'</td></tr>';

	$content .='<tr><td align="center"><div class="middletext">';

	if ($game['isChampion'])
		$content .='<strong>'.$txt['arcade_champion'].':</strong> '.$game['champion']['memberLink'].' - '.$game['champion']['score'].'</div>';

	else
		$content .=$txt['arcade_no_scores'];

	$content .='</div></td></tr></table></div>';

	return $content;
	}
}


function ArcadeInfoNewestGames($no)
{
	global $smcFunc, $scripturl, $txt, $arcSettings;

		$result = $smcFunc['db_query']('', '
		SELECT id_game, game_name, thumbnail, game_directory
		FROM {db_prefix}arcade_games
		WHERE enabled = 1
		ORDER BY id_game DESC
		LIMIT 0, {int:limit}',
		array(
		'limit' => $no,
		)
	);
	$content = '<div align="center"><table width="100%" border="0" cellpadding="3"><tr><td colspan="2"><div align="center"><i><b>'.$no.' '.$txt['arcade_LatestGames'].'</b></i></div></td></tr>';
	while ($popgame = $smcFunc['db_fetch_assoc']($result))
	{
	   $popgameico = !$popgame['game_directory'] ?	$arcSettings['gamesUrl'].$popgame['thumbnail'] : $arcSettings['gamesUrl'].$popgame['game_directory']."/".$popgame['thumbnail'];
	   $content .='<tr><td><div align="right"><a href="'.$scripturl.'?action=arcade;sa=play;game='.$popgame['id_game'].'"><img border="0" src="'.$popgameico. '" alt="ico" width="25" height="25" title="'.$txt['arcade_champions_play'].' '. $popgame['game_name'].'"/></a></div></td><td class="middletext"><div align="left"><a href="'.$scripturl.'?action=arcade;sa=play;game='.$popgame['id_game'].'">'.$popgame['game_name'].'</a></div></td></tr>';
	}

	$content .='</table></div>' ;

	return $content;
}

function ArcadeInfoLongestChamps($no)
{
	global $scripturl, $txt, $arcSettings;

	$mostgame = ArcadeStats_LongestChampions($no);

	$content = '<div align="center"><table width="100%" border="0" cellpadding="3"><tr><td colspan="3"><div align="center"><i><b>'.$no.' '.$txt['arcade_g_i_b_11'].'</b></i></div></td></tr>';
	if (is_array($mostgame))
		foreach($mostgame as $popgame)
		{
		   $popgameico = !$popgame['game_directory'] ?	$arcSettings['gamesUrl'].$popgame['thumbnail'] : $arcSettings['gamesUrl'].$popgame['game_directory']."/".$popgame['thumbnail'];
		   $content .='<tr><td width="25"><a href="'.$scripturl.'?action=arcade;sa=play;game='.$popgame['id'].'"><img border="0" src="'.$popgameico. '" alt="ico" width="25" height="25" title="'.$txt['arcade_champions_play'].' '. $popgame['game_name'].'"/></a></td><td class="middletext"><div align="left">'.$popgame['member_link'].' '.$txt['arcade_g_i_b_9'].' '.$popgame['game_name'].' '.$txt['arcade_g_i_b_5'].' '.$popgame['duration'].'</div></td></tr>';
		}

	$content .='</table></div>' ;

	return $content;
}

function ArcadeInfoMostPlayed($no)
{
	global $scripturl, $txt, $arcSettings;

	$mostgame = ArcadeStats_MostPlayed($no);

	$content = '<div align="center"><table width="100%" border="0" cellpadding="3"><tr><td colspan="3"><div align="center"><i><b>'.$no.' '.$txt['arcade_g_i_b_10'].'</b></i></div></td></tr>';
	if (is_array($mostgame))
		foreach($mostgame as $popgame)
		{
		   $popgameico = !$popgame['game_directory'] ?	$arcSettings['gamesUrl'].$popgame['thumbnail'] : $arcSettings['gamesUrl'].$popgame['game_directory']."/".$popgame['thumbnail'];
		   $content .='<tr><td width="25"><a href="'.$scripturl.'?action=arcade;sa=play;game='.$popgame['id'].'"><img border="0" src="'.$popgameico. '" alt="ico" width="25" height="25" title="'.$txt['arcade_champions_play'].' '. $popgame['name'].'"/></a></td><td class="middletext"><div align="left">'.$popgame['link'].' '.$txt['arcade_g_i_b_6'].' '.$popgame['plays'].' '.$txt['arcade_g_i_b_7'].'</div></td></tr>';
		}

	$content .='</table></div>' ;

	return $content;
}

function ArcadeInfoBestPlayers($no)
{
	global $scripturl, $txt, $settings;

	$top_player = ArcadeStats_BestPlayers($no);
	$i=0; //players position

	//array for icons
	$poz = array('/first.gif','/second.gif','/third.gif',);

	$content = '<div align="center"><table width="100%" border="0" cellpadding="1"><tr><td colspan="2"><div align="center"><i><b>'.$no.' '.$txt['arcade_b3pb_1'].'</b></i></div></td></tr>';

	if ($top_player != false)
	{
		foreach ($top_player as $row)
		{
			$content.= '<tr><td height="25"><div align="right"><img src="'.$settings['images_url'].'/arc_icons'.$poz[$i].'" alt="ico"/></div></td><td><div class="middletext"><div align="left"> - '.$row['link'].' '.$txt['arcade_b3pb_2'].' '.$row['champions'].' '.$txt['arcade_b3pb_3'].'</div></div></td></tr>';
			$i++;
			if ($i > 2)
			{
				$poz[$i]= '/star2.gif';
			}
		}
	}
	$content.='</table></div>';

	return $content;

}

function ArcadeInfoShouts()
{
	global $smcFunc, $scripturl, $settings, $txt, $sourcedir, $arcSettings;

	if (empty($arcSettings['arcade_show_shouts']))
		$arcSettings['arcade_show_shouts'] = 10;

			$result = $smcFunc['db_query']('', '
				SELECT
				s.id_shout, s.id_member,
				s.content, s.time, m.real_name
				FROM {db_prefix}arcade_shouts AS s
				LEFT JOIN {db_prefix}members AS m ON (m.id_member = s.id_member)
				ORDER BY id_shout DESC
				LIMIT 0, {int:limit}',
				array(
				'limit' => $arcSettings['arcade_show_shouts'],
				)
			);

			echo '
				<table cellpadding="0"  align="center" width="100%" cellspacing="0" style="table-layout: fixed;">
				<tr>
					<td align="center">
						<span><i><b>', $txt['arcade_shouts'], '</b></i></span>
					</td>
				</tr>
					<tr><td>
					<div class="smalltext" style="width: 99%; height: 250px; overflow: auto;">';
					while ($shout = $smcFunc['db_fetch_assoc']($result))
					{
					echo'
						<div style="margin: 4px;">
							<div style="border: dotted 1px; padding: 2px 4px 2px 4px;" class="windowbg2">';

							if (allowedTo('arcade_admin'))
								echo'<a href="'.$scripturl.'?action=arcade;sa=shout;del='.$shout['id_shout'].'"><img border="0" src="' . $settings['images_url'] . '/arc_icons/del1.png" alt="X"  title="'.$txt['arcade_shout_del'].'"/></a>&nbsp;';

								echo'<b>'.$shout['real_name'].'</b>
							</div>
							<div style="padding: 2px;">
								'.timeformat($shout['time']).'
							</div>
							<div style="padding: 4px;">
								'.wordwrap(parse_bbc(censorText($shout['content'])), 34, "\n", true).'
							</div>
					</div>';

		}
					echo'</div></td>
					</tr>
				</table>';




}

//////////////////////////////////////////////////////////////////////////////
//Below here - Functions required to generate the blocks and read/write cache
function ArcadeShout()
{
	global $smcFunc, $txt, $arcSettings, $user_info;

	if (isset($_REQUEST['del']))
	{
	   // Only allow admins to delete shouts

	   if (allowedTo('arcade_admin'))
	   {
			$id = (int)$_REQUEST['del'];

			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_shouts
				WHERE id_shout = {int:ids}',
				array(
					'ids' => $id,
				)
			);

			if ( file_exists($arcSettings['cacheDirectory'].'shout.cache'))
			{
				unlink($arcSettings['cacheDirectory'].'shout.cache');
			}
	  }
	}
	elseif (!$user_info['is_guest'])
	{
		$shout = $txt['arcade_shouted'].$smcFunc['htmlspecialchars']($_REQUEST['the_shout'], ENT_QUOTES);
		add_to_arcade_shoutbox($shout);
	}
	redirectexit('action=arcade');
}

function ArcadeLatestChamps($no)
{
	global $smcFunc, $scripturl, $txt;

	$result = $smcFunc['db_query']('', '
		SELECT g.id_game, g.game_name, g.thumbnail, g.game_directory, m.id_member, m.real_name, s.id_member
		FROM {db_prefix}arcade_games AS g
		LEFT JOIN {db_prefix}arcade_scores AS s ON ( g.id_score_first = s.id_score )
		LEFT JOIN {db_prefix}members AS m ON ( m.id_member = s.id_member )
		WHERE g.id_member_first > 0
		ORDER BY s.champion_from DESC
		LIMIT 0, {int:limit}',
		array(
		'limit' => $no,
		)
	);

	$top = array();
	while ($score = $smcFunc['db_fetch_assoc']($result))
	{
		$top[] = array(
			'id' => $score['id_game'],
			'game_name' => $score['game_name'],
			'thumbnail' => $score['thumbnail'],
			'game_directory' => $score['game_directory'],
			'game_link' => '<a href="' . $scripturl . '?action=arcade;sa=play;game=' . $score['id_game'] . '">' .  $score['game_name'] . '</a>',
			'real_name' => $score['real_name'],
			'member_link' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' .  $score['real_name'] . '</a>' : $txt['arcade_guest'],
		);
	}
		return $top;
}

function category_games()
{
	//gets the category names and counts the games in each category
	global $smcFunc;

	$no = 30;
	// Load Category information
 	$result = $smcFunc['db_query']('', '
		SELECT
		c.id_category,
		count(g.id_category) AS games,
		c.category_name,
		c.category_icon
		FROM {db_prefix}arcade_games g, {db_prefix}arcade_categories c
		WHERE g.id_category = c.id_category AND g.enabled = 1
 		GROUP BY g.id_category
 		ORDER BY c.category_order
 		LIMIT 0, {int:limit}',
		array(
		'limit' => $no,
		)
	);
	while ($cat = $smcFunc['db_fetch_assoc']($result))
	{
		$cats[$cat['id_category']] = $cat;
	}
	return $cats;
}

function writeCache($content, $filename)
{
	// Writes a cache file
	global $arcSettings;

	$fp = fopen($arcSettings['cacheDirectory'].$filename, 'w');
	fwrite($fp, $content);
	fclose($fp);
}

function readCache($filename, $expiry)
{
	//Reads a cache file
	global $arcSettings;

	if (file_exists($arcSettings['cacheDirectory'].$filename))
	{
	  if ((time() - $expiry) > filemtime($arcSettings['cacheDirectory'].$filename))
	  {
		return FALSE;
	  }
	  $cache = file($arcSettings['cacheDirectory'].$filename);
	  return implode('', $cache);
	}
	return FALSE;
}

function arcade_news_fader($board, $limit)
{
	global $smcFunc;

	$result = $smcFunc['db_query']('', '
		SELECT
		id_first_msg
		FROM {db_prefix}topics
		WHERE id_board = {int:board}
 		ORDER BY id_first_msg DESC
 		LIMIT 0, {int:limit}',
		array(
		'limit' => $limit,
		'board' => $board,
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		$posts[] = $row['id_first_msg'];
	}

	if (empty($posts))
		return array();

	$result = $smcFunc['db_query']('', '
		SELECT
		m.body,
		m.smileys_enabled,
		m.id_msg
		FROM {db_prefix}topics AS t, {db_prefix}messages AS m
		WHERE t.id_first_msg IN (' . implode(', ', $posts) . ')
 		AND m.id_msg = t.id_first_msg
		ORDER BY t.id_first_msg DESC
 		LIMIT 0, {int:limit}',
		array(
		'limit' => count($posts),
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{

		$find  = '<br';
		$pos = strpos($row['body'], $find);

		if ($pos !== false)
		{
			$row['body'] = substr($row['body'], 0, $pos);
		}

		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);
		censorText($row['body']);
		$return[] = array(
			'body' => $row['body'],
			'is_last' => false
		);
	}

	$return[count($return) - 1]['is_last'] = true;

	return $return;
}
?>