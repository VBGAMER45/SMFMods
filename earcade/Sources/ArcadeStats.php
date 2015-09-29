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
 ArcadeStats.php
****************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	void ArcadeStats()
		- ...
		
	array ArcadeStats_BestPlayers([count = 10])
		- ...

	array ArcadeStats_LongestChampions([count = 10], [time])
		- ...
		
	array ArcadeStats_MostActive([count = 10], [time])
		- ...
		
	array ArcadeStats_MostPlayed([count = 10], [time])
		- ...
		
	array ArcadeStats_Rating([count = 10])
		- ...
		
	string format_time_ago(timestamp)
		- Formats time to xxx ago

*/

function ArcadeStats()
{
	global $txt, $context;
	
	$context['sub_template'] = 'arcade_statistics';
	$context['page_title'] = $txt['arcade_stats_title'];
	
	// Load data using functions
	$context['arcade']['statistics']['play'] = ArcadeStats_MostPlayed();
	$context['arcade']['statistics']['active'] = ArcadeStats_MostActive(); 
	$context['arcade']['statistics']['rating'] = ArcadeStats_Rating(); 
	$context['arcade']['statistics']['champions'] = ArcadeStats_BestPlayers(); 
	$context['arcade']['statistics']['longest'] = ArcadeStats_LongestChampions();
	$context['arcade']['statistics']['total'] = ArcadeStats_total_plays();
	
}

function ArcadeStats_total_plays()
{
	// Returns most playd games
	global $smcFunc;

	$result = $smcFunc['db_query']('', '
	SELECT SUM(number_plays) AS total_plays 
	FROM {db_prefix}arcade_games 
	WHERE enabled = 1',
		array(
		)
	);
	$row = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);
	
	return $row[0];
}

function ArcadeStats_MostPlayed($count = 10)
{
	// Returns most playd games
	global $smcFunc, $scripturl;
	
	$top = array();
	$max = -1;
	
	$result = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.game_rating, game.thumbnail, game.game_directory, game.number_plays
		FROM {db_prefix}arcade_games AS game
		WHERE game.number_plays > 0
		AND game.enabled = 1
		ORDER BY game.number_plays DESC
		LIMIT 0,{int:limit}',
			array(
			'limit' => $count,
			)
		);
	
	while ($score = $smcFunc['db_fetch_assoc']($result))
	{
		if ($max == -1)
			$max = $score['number_plays'];
		if ($max == 0)
			return false; // No one has played games yet0
		
		$top[] = array(
			'id' => $score['id_game'],
			'name' => $score['game_name'],
			'thumbnail' => $score['thumbnail'],
			'game_directory' => $score['game_directory'],
			'link' => '<a href="' . $scripturl . '?action=arcade;sa=play;game=' . $score['id_game'] . '">' .  $score['game_name'] . '</a>',
			'rating' => $score['game_rating'],
			'plays' => $score['number_plays'],
			'precent' => ($score['number_plays'] / $max) * 100,
		);	
	}
	
	if (count($top) == 0)
		return false;
	elseif ($count > 1)
		return $top;
	else
		return $top[0];
}

function ArcadeStats_Rating($count = 10)
{
	global $smcFunc, $scripturl;
	
	$top = array();
	$max = -1;
	
	$result = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.game_rating, game.number_plays
		FROM {db_prefix}arcade_games AS game
		WHERE game.game_rating > 0
		AND game.enabled = 1
		ORDER BY game.game_rating DESC
		LIMIT 0,{int:limit}',
			array(
			'limit' => $count,
			)
		);
	
	while ($score = $smcFunc['db_fetch_assoc']($result))
	{
		if ($max == -1)
			$max = $score['game_rating'];
		
		$top[] = array(
			'id' => $score['id_game'],
			'name' => $score['game_name'],
			'link' => '<a href="' . $scripturl . '?action=arcade;sa=play;game=' . $score['id_game'] . '">' .  $score['game_name'] . '</a>',
			'rating' => $score['game_rating'],
			'plays' => $score['number_plays'],
			'precent' => ($score['game_rating'] / $max) * 100,
		);	
	}
	
	if (count($top) == 0)
		return false;
	elseif ($count > 1)
		return $top;
	else
		return $top[0];
}

function ArcadeStats_BestPlayers($count = 10)
{
	// Returns best players by count of champions
	global $smcFunc, $scripturl, $txt;
		
	$top = array();
	$max = -1;
	
		$result = $smcFunc['db_query']('', '
		SELECT count(*) AS champions, 
		mem.id_member,
		mem.real_name	 
		FROM {db_prefix}arcade_games AS game
		LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = game.id_member_first)
		WHERE id_score_first > 0
		GROUP BY game.id_member_first
		ORDER BY champions DESC 
		LIMIT 0,{int:limit}',
			array(
			'limit' => $count,
			)
		);
	
	while ($score = $smcFunc['db_fetch_assoc']($result))
	{
		if ($max == -1)
			$max = $score['champions'];
		
		$top[] = array(
			'name' => $score['real_name'],
			'link' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' .  $score['real_name'] . '</a>' : $txt['arcade_guest'],
			'champions' => $score['champions'],
			'precent' => ($score['champions'] / $max) * 100,
		);	
	}
	
	if (count($top) == 0)
		return false;
	elseif ($count > 1)
		return $top;
	else
		return $top[0];
}

function ArcadeStats_MostActive($count = 10, $time = -1)
{
	// Returns most active players
	global $smcFunc, $scripturl, $txt;
	
	$top = array();
	$max = -1;
	
	$result = $smcFunc['db_query']('', '
		SELECT count(*) AS scores, 
		mem.id_member,
		mem.real_name	 
		FROM {db_prefix}arcade_scores AS score
		LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = score.id_member)
		GROUP BY score.id_member
		ORDER BY scores DESC 
		LIMIT 0,{int:limit}',
			array(
			'limit' => $count,
			)
		);
	
	while ($score = $smcFunc['db_fetch_assoc']($result))
	{
		if ($max == -1)
			$max = $score['scores'];
		
		$top[] = array(
			'name' => $score['real_name'],
			'link' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' .  $score['real_name'] . '</a>' : $txt['arcade_guest'],
			'scores' => $score['scores'],
			'precent' => ($score['scores'] / $max) * 100,
		);	
	}
	
	if (count($top) == 0 || $count == 1)
		return false;
	elseif ($count > 1)
		return $top;
	else
		return $top[0];
}

function ArcadeStats_LongestChampions($count = 20, $time = - 1)
{
	global $smcFunc, $scripturl, $txt;

	$top = array();
	$max = -1;
	
	$result = $smcFunc['db_query']('', '
		SELECT game.id_game, game.game_name, game.thumbnail, game.game_directory,
			IF(champion_from > 0, (IF(champion_to = 0, UNIX_TIMESTAMP(), champion_to) - champion_from), 0) AS championDuration,
			mem.id_member, mem.real_name, IF(champion_to = 0, 1, 0) AS current		
		FROM ({db_prefix}arcade_scores AS score, {db_prefix}arcade_games AS game)
		LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = score.id_member)
		WHERE game.enabled = 1
		AND game.id_game = score.id_game
		HAVING championDuration > 0
		ORDER BY IF(champion_from > 0, (IF(champion_to = 0, UNIX_TIMESTAMP(), champion_to) - champion_from), 0) DESC
		LIMIT 0,{int:limit}',
			array(
			'limit' => $count,
			)
		);
	
	while ($score = $smcFunc['db_fetch_assoc']($result))
	{
		if ($max == -1)
			$max = $score['championDuration'];
		
		$top[] = array(
			'id' => $score['id_game'],
			'game_name' => $score['game_name'],
			'thumbnail' => $score['thumbnail'],
			'game_directory' => $score['game_directory'],
			'game_link' => '<a href="' . $scripturl . '?action=arcade;sa=play;game=' . $score['id_game'] . '">' .  $score['game_name'] . '</a>',
			'real_name' => $score['real_name'],
			'member_link' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' .  $score['real_name'] . '</a>' : $txt['arcade_guest'],
			'duration' => format_time_ago($score['championDuration']),
			'precent' => ($score['championDuration'] / $max) * 100,
			'current' => $score['current'] == 1,
		);	
	}
	
	if (count($top) == 0)
		return false;
	elseif ($count > 1)
		return $top;
	else
		return $top[0];
}

function format_time_ago($timestamp)
{
	global $txt;
	// Returns formated string
	
	$yksikot = array(
		array(604800, $txt['arcade_weeks']), // Seconds in week
		array(86400, $txt['arcade_days']), // Seconds in day
		array(3600, $txt['arcade_hours']), // Seconds in hour
		array(60, $txt['arcade_mins']), // Seconds in minute
	);
	
	if ($timestamp < 60)
		return $txt['arcade_under_minute_ago'];

	$text = '';

	foreach( $yksikot as $t )
	{
		$tassa = floor($timestamp / $t[0]);

		if ( $tassa > 1 )
		{
			$text .= $tassa . ' '. $t[1];
			$text .= ' ';

			$timestamp = $timestamp - ($t[0] * $tassa);
		}
	}
		
	return trim($text);

}
?>