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
* Subs-Arcade.php                                                              *
********************************************************************************/
/*	This file contains functions used by arcade

	loadArcadeSettings()
		-Loads the arcade settings in to $arcSettings array
		
	saveArcadeSettings()
		Saves the arcade settings to the database table arcade_settings
	
	NewGameOfDay()
		- Generates new game of day
	
	ArcadeGameInfo()
		- Returns the game information
		- Either by ID or internal_name
	
	UpdateGame()
		- Updates the game information
		
	BuildGameArray()
		- Builds array of all game info for easy of use
	
	BuildGameArrayAdmin()
		- Builds array of game info needed for admin use
		
	ArcadeFixPositions(i$id_game)
		- Fixes the score  positions of a game
		
	update_champ_cups($id_game)
		- Updates the gold,silver,bronze cups
		
	prepareCategories()
		- Gets list of the categories
		
	ArcadeSendPM()	
		- Sends a PM to ex champion
	
	ArcadeLastUpdate()
		- Checks if daily updates - gotd etc were last updated
		
	ArcadeCountGames()
		- Counts the number of 'enabled' games
		
	small_game_query($condition)
		_ returns less game info ArcadeGameInfo() - used for GOTD and Random Game
		
	getLastMessageID()
		- Returns the last message (forum topic) id 
		
	add_to_arcade_shoutbox()
		- Adds shouts to the database
		  
*/

if (!defined('SMF'))
	die('Hacking attempt...');

//loads the aracde settings
function loadArcadeSettings()
{
	global $arcSettings, $modSettings, $smcFunc;

	if (($arcSettings = cache_get_data('arcSettings', 90)) == null)
	{
		$request = $smcFunc['db_query']('', '
			SELECT variable, value
			FROM {db_prefix}arcade_settings',
			array(
			)
		);
		$arcSettings = array();
		if (!$request)
			db_fatal_error();
		while ($row = $smcFunc['db_fetch_row']($request))
			$arcSettings[$row[0]] = $row[1];
			
			
		$smcFunc['db_free_result']($request);
	
		
		if (!empty($modSettings['cache_enable']))
			cache_put_data('arcSettings', $arcSettings, 90);
	}
}

//saves the arcade settings
function saveArcadeSettings($changeArray, $update = false)
{
	global $smcFunc, $arcSettings;


	if (empty($changeArray) || !is_array($changeArray))
		return;

	// In some cases, this may be better and faster, but for large sets we don't want so many UPDATEs.
	if ($update)
	{
		foreach ($changeArray as $variable => $value)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_settings
				SET value = {' . ($value === false || $value === true ? 'raw' : 'string') . ':value}
				WHERE variable = {string:variable}',
				array(
					'value' => $value === true ? 'value + 1' : ($value === false ? 'value - 1' : $value),
					'variable' => $variable,
				)
			);
			$arcSettings[$variable] = $value === true ? $arcSettings[$variable] + 1 : ($value === false ? $arcSettings[$variable] - 1 : $value);

		}

		// Clean out the cache and make sure the cobwebs are gone too.
		cache_put_data('arcSettings', null, 90);

		return;
	}
	
		$replaceArray = array();
	foreach ($changeArray as $variable => $value)
	{ 
		// Don't bother if it's already like that ;).
		if (isset($arcSettings[$variable]) && $arcSettings[$variable] == $value)
			continue;
		// If the variable isn't set, but would only be set to nothing'ness, then don't bother setting it.
        elseif (!isset($arcSettings[$variable]) && empty($value))
		  continue;
        
        
        if ($value == null)
            $value = '';
    


		$replaceArray[] = array($variable, $value);

		$modSettings[$variable] = $value;
	}

	if (empty($replaceArray))
		return;
        


	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_settings',
		array('variable' => 'string-255', 'value' => 'string-65534'),
		$replaceArray,
		array('variable','value')
	);

	// Kill the cache - it needs redoing now, but we won't bother ourselves with that here.
	cache_put_data('arcSettings', null, 90);
}

// Returns a random game id for new game of day
function NewGameOfDay()
{
	global $smcFunc, $arcSettings;
    
    $arcSettings['arcadegotd'] = (int) $arcSettings['arcadegotd'];

	$result = $smcFunc['db_query']('', '
		SELECT id_game
		FROM {db_prefix}arcade_games
		WHERE id_game != {int:game}
		AND enabled = 1
		ORDER BY RAND() LIMIT 0,1',
		array(
		'game' => $arcSettings['arcadegotd'],
		'special' => 1,
		)
	);
	$row = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);
    
    if (empty($row[0]))
        $row[0] = 0;
	
	//clear game of the day cache, its a new game now
	if (file_exists($arcSettings['cacheDirectory'].'gotd.cache'))
    {
        unlink($arcSettings['cacheDirectory'].'gotd.cache');
    }

	return $row[0];
}


// Fetches game info
function ArcadeGameInfo($id_game , $internal_name = '', $admin = false)
{
	global $smcFunc, $user_info;	
		
	if (!empty($id_game) && empty($internal_name)) // By ID
		$sql = ' AND g.id_game = ' . (int) $id_game;
		
	elseif (!empty($internal_name)) // By internal_name
		$sql = ' AND g.internal_name = \'' . $internal_name . '\'';
		
	else
		return false; 
	
	if (!$admin)
		$enabled = 'g.enabled = 1';

	else 
		$enabled = '1';
	
	if (allowedTo('arcade_admin'))
		$user_info['query_see_game'] = '1';
	
	// Query with all needed data
    $result = $smcFunc['db_query']('', '
	SELECT
			g.id_game,g.internal_name,g.game_name,g.game_file,
			g.game_directory,g.description,g.help,
			g.thumbnail,g.id_category,g.enabled,g.member_groups, 
			g.score_type,g.game_rating,g.id_member_first,
			g.id_score_first,g.id_member_second,g.id_score_second,
			g.id_member_third,g.id_score_third,g.game_width,
			g.game_height,g.game_bg_colour AS bgcolor,
			g.topic_id,g.number_plays,c.category_name,f.id_favorite AS is_favorite,
			s1.score AS gold_score,IFNULL(s2.score, 0) AS silver_score,
			IFNULL(s3.score, 0) AS bronze_score,IFNULL(pb.id_best, 0) AS id_best, 
			IFNULL(pb.score, 0) AS best,IFNULL(pb.atbscore, 0) AS atbbest,			
			IFNULL(s1.start_time, 0) AS champion_time, IFNULL(m1.real_name, 0) AS real_name1,
			IFNULL(m2.real_name, 0) AS real_name2, IFNULL(m3.real_name, 0) AS real_name3
		FROM {db_prefix}arcade_games as g
    		LEFT JOIN {db_prefix}arcade_categories AS c ON (c.id_category = g.id_category)
    		LEFT JOIN {db_prefix}arcade_favorite AS f ON (f.id_game = g.id_game AND f.id_member = {int:id_mem})
    		LEFT JOIN {db_prefix}arcade_scores AS s1 ON (s1.id_score = g.id_score_first)
    		LEFT JOIN {db_prefix}arcade_scores AS s2 ON (s2.id_score = g.id_score_second)
    		LEFT JOIN {db_prefix}arcade_scores AS s3 ON (s3.id_score = g.id_score_third)
    		LEFT JOIN {db_prefix}arcade_personalbest AS pb ON (pb.id_game = g.id_game AND pb.id_member = {int:id_mem})						
    		LEFT JOIN {db_prefix}members AS m1 ON (m1.id_member = g.id_member_first)
    		LEFT JOIN {db_prefix}members AS m2 ON (m2.id_member = g.id_member_second)
    		LEFT JOIN {db_prefix}members AS m3 ON (m3.id_member = g.id_member_third)			
		WHERE '.$user_info['query_see_game'].' AND '.$enabled.' '.$sql.'
		LIMIT 0,1',
		array(
		'id_mem' => $user_info['id'],
		)
	);
    
	if ($smcFunc['db_num_rows']($result) == 0)
	   return false;
	
	$game = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	
	if (!$admin)
		return BuildGameArray($game);
	else
		return BuildGameArrayAdmin($game);
}

// Updates Game
function UpdateGame($id_game = null, $modifications)
{
	global $smcFunc, $scripturl, $txt, $user_info, $id_member;
	
	if (empty($id_game))
		fatal_error('arcade_game_update_error');
	if (!is_array($id_game))
		$id_game = array($id_game);
			
	// Following is numeric values
	$numeric = array('game_width', 'game_height', 'score_type', 'number_plays', 'number_rates', 'id_category', 'game_rating');
	// Valid values everyone can modify these
	$valid = array('id_member_first', 'id_score_first','id_member_second','id_score_second','id_member_third','id_score_third', 'number_plays', 'number_rates', 'game_rating');
	// Following may not be empty
	$non_empty = array('game_name', 'category', 'internal_name', 'game_file');
	
	// These can be modfied only by those wo can modify arcade. Just for extra protection
	if (allowedTo('arcade_admin'))
	{	
		$valid = array_merge($valid, array('game_directory', 'id_category', 'enabled', 'member_groups'));		
		
		// What we can update in single game mode
		if (count($id_game) == 1)
			$valid = array_merge($valid, array('game_name', 'description', 'game_file',
			'internal_name', 'game_width', 'game_height', 'game_bg_colour',
			'score_type', 'thumbnail', 'help'));
			
	}
		
	$changes = array();
	$errors = array();
	
	foreach ($modifications as $key => $value)
		if (in_array($key, $valid))
		{
			if (!empty($value) || !in_array($key, $non_empty))
			{
				if (in_array($key, $numeric))
				{
					// Hey is this really numeric?
					if (!is_numeric($value))
						$errors[$key] = 'arcade_must_be_numeric';
					$changes[] = "$key = $value";
				}
				else 
				{
					if (is_array($value))
						$value = implode(',', $value);
						
					$value = addslashes(stripcslashes($value));
					$changes[] = "$key = '$value'"; 
				}
			}
			else
				$errors[$key] = 'arcade_empty_value';
		}
		// This can't be modifed, 
		else 
			fatal_error($txt['arcade_game_update_error'] . ' (' . $key . ')');
			
	// Check some values
	if (isset($modifications['game_bg_colour']))
	{
		$modifications['game_bg_colour'] = trim($modifications['game_bg_colour']);
		
		if (strlen($modifications['game_bg_colour']) != 6 && $modifications['game_bg_colour'] != '')
			$errors['game_bg_colour'] = 'arcade_invalid_bgcolor';
	}
	
	if (count($errors) >= 1)
		return $errors;
	
	if (count($changes) == 0)
		fatal_lang_error('arcade_game_update_error');
	
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_games
		SET ' . implode(', ', $changes) . '
		WHERE id_game IN(' . implode(', ', $id_game) . ')',
		array(
				)
	);
	
	
	// No errors
	return array();
}

function BuildGameArray($game)
{
	// Build Game array for easier to use and update
	
	global $scripturl, $txt, $arcSettings, $user_info, $id_member, $smfChanges;
	
	if ($game['game_directory'] != '') // Is game installed in subdirectory
	{
		$gameurl = $arcSettings['gamesUrl'] . $game['game_directory'] . '/';	
	}
	else // It is in main directory
	{
		$gameurl = $arcSettings['gamesUrl'];
	}
	
	$game['description'] = parse_bbc($game['description']);
	$game['help'] = parse_bbc($game['help']);
	$game['maxScores'] = $arcSettings['arcadeMaxScores'];
	
	return array(
		'id' => $game['id_game'],
		'url' => array(
			'play' => $scripturl . '?action=arcade;sa=play;game=' . $game['id_game'],
			'pop' => $scripturl . '?action=arcade;sa=play;pop=1;game=' . $game['id_game'],
			'edit' => $scripturl . '?action=admin;area=managearcade;sa=listgames;do=edit;game=' . $game['id_game'],
			'highscore' => $scripturl . '?action=arcade;sa=highscore;game=' . $game['id_game'],
			'flash' => $gameurl . $game['game_file'],
			'favorite' => $game['is_favorite'] == 0 ? $scripturl . '?action=arcade;sa=favorite;game=' . $game['id_game'] : $scripturl . '?action=arcade;sa=favorite;remove;game=' . $game['id_game'],
		),  
		// Information needed for showing flash
		'flash' => array(
			'width' => $game['game_width'],
			'height' => $game['game_height'], 
			'backgroundColor' => $game['bgcolor']	 	
		),
		'category' => array(
			'id' => $game['id_category'],
			'name' => $game['category_name'],
			'link' => $scripturl . '?action=arcade;category=' . $game['id_category'],
		),
		'internal_name' => $game['internal_name'],
		'name' => $game['game_name'],
		'file' => $game['game_file'],
		'description' => $game['description'],
		'help' =>  $game['help'],		
		'rating' => $game['game_rating'],
		'topic_id' => $game['topic_id'],		 
		'rating2' => round($game['game_rating']),
		'number_plays' => $game['number_plays'],  
		'thumbnail' => !empty($game['thumbnail']) ? $gameurl . $game['thumbnail'] : '',	
		'isChampion' => $game['id_score_first'] > 0 ? true : false,
		'champion' => array(
			'member_id' => $game['id_member_first'],
			'score_id' => $game['id_score_first'],
			'memberLink' =>  $game['real_name1'] != '0' ? '<a href="' . $scripturl . '?action=profile;u=' . $game['id_member_first'] . ';sa=statPanel">' . $game['real_name1'] . '</a>' : $txt['arcade_guest'],
			'score' => round($game['gold_score'],3),
			'time' => $game['champion_time'],
		),
			'secondPlace' => array(
			'member_id' => $game['id_member_second'],
			'score_id' => $game['id_score_second'],
			'memberLink' =>  $game['real_name2'] != '0' ? '<a href="' . $scripturl . '?action=profile;u=' . $game['id_member_second'] . ';sa=statPanel">' . $game['real_name2'] . '</a>' : $txt['arcade_guest'],
			'score' => round($game['silver_score'],3),
		),
			'thirdPlace' => array(
			'member_id' => $game['id_member_third'],
			'score_id' => $game['id_score_third'],
			'memberLink' =>  $game['real_name3'] != '0' ? '<a href="' . $scripturl . '?action=profile;u=' . $game['id_member_third'] . ';sa=statPanel">' . $game['real_name3'] . '</a>' : $txt['arcade_guest'],
			'score' => round($game['bronze_score'],3),
		),
		'isPersonalBest' => $game['id_best'] > 0 ? true : false,
		'personalBest' => round($game['best'],3),
		'all_time_best' => round($game['atbbest'],3),
		'score_type' => $game['score_type'],
		'highscoreSupport' => $game['score_type'] != 2 ? true : false,
		'maxScores' => $game['maxScores'],		
		'directory' => $game['game_directory'],
		'isFavorite' => $game['is_favorite'] > 0 ? true : false,
	); 	
}

function BuildGameArrayAdmin($game)
{
	// Build Game array for easier to use and update
	
	global $scripturl, $txt, $arcSettings, $user_info, $id_member;
			
	return array(
		'id' => $game['id_game'],
		'url' => array(
			'play' => $scripturl . '?action=arcade;sa=play;game=' . $game['id_game'],
			'highscore' => $scripturl . '?action=arcade;sa=highscore;game=' . $game['id_game'],
			'edit' => $scripturl . '?action=admin;area=managearcade;sa=listgames;do=edit;game=' . $game['id_game'],
			'highscore_edit' => $scripturl . '?action=managehighscores;sa=edit;game=' . $game['id_game'],
			'delete' => $scripturl . '?action=admin;area=managearcade;sa=delete;game=' . $game['id_game'],
		),  
		// Information needed for showing flash
		'flash' => array(
			'width' => $game['game_width'],
			'height' => $game['game_height'], 
			'backgroundColor' => $game['bgcolor']	 	
		),
		'category' => array(
			'id' => $game['id_category'],
			'name' => $game['category_name'],
			'link' => $scripturl . '?action=arcade;category=' . $game['id_category'],
		),
		'internal_name' => $game['internal_name'],
		'name' => $game['game_name'],
		'file' => $game['game_file'],
		'description' => $game['description'],
		'help' =>  $game['help'],		
		'rating' => $game['game_rating'], 
		'thumbnail' => $game['thumbnail'],	
		'score_type' => $game['score_type'],
		'directory' => $game['game_directory'],
		'enabled' => $game['enabled'] == 1,
		'member_groups' => isset($game['member_groups']) ? $game['member_groups'] : '',
	); 	
}

function ArcadeFixPositions($id_game,$score_type)
{
	global $smcFunc;
/*	
	//get the score type
	$result = $smcFunc['db_query']('', '
		SELECT score_type
		FROM {db_prefix}arcade_games',
		array(
		'id_game' => $id_game,
		)
	);
	$row = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);
	
*/	
	$type = $score_type == 0 ? 'DESC' : 'ASC';
	
	//get the scores
	$result = $smcFunc['db_query']('', '
		SELECT id_score
		FROM {db_prefix}arcade_scores
		WHERE id_game = {int:id_game}
		ORDER BY score '.$type.', end_time ASC',
		array(
		'id_game' => $id_game,
		)
	);
	if ($smcFunc['db_num_rows']($result) > 0)
	{
		$position = 1;
		while ($scoreids = $smcFunc['db_fetch_assoc']($result))
		{
			//re-write the positions
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_scores
				SET
				position = {int:pos}
				WHERE id_score = {int:ids}',
				array(
				'pos' => $position,
				'ids' => $scoreids['id_score'],
				)
			);
	
			$position++;
		}
	}
	
	//update the gold-silver-bronze cups
	update_champ_cups($id_game);
	
}

// update the gold,silver,bronze cups
function update_champ_cups($id_game)
{
	global $arcSettings, $smcFunc;
	
		$cups_update = array('id_score_first' => 0, 'id_member_first' => 0, 'id_member_second' => 0,'id_score_second' => 0, 'id_member_third' => 0,'id_score_third' => 0 );
		$loop = 0;
		
		$result = $smcFunc['db_query']('', '
			SELECT id_score, id_member
			FROM {db_prefix}arcade_scores
			WHERE id_game = {int:id_game} ORDER BY position LIMIT 0,3',
		array(
			'id_game' => $id_game,
			)
		);
			
		while ($cups = $smcFunc['db_fetch_assoc']($result))
		{
			if ($loop == 0)
			{
				$cups_update['id_member_first'] = $cups['id_member'];
				$cups_update['id_score_first'] = $cups['id_score'];				
			}
		
			if ($loop == 1)
			{
					$cups_update['id_member_second'] = $cups['id_member'];
					$cups_update['id_score_second'] = $cups['id_score'];
			}
		
			if ($loop == 2)
			{
					$cups_update['id_member_third'] = $cups['id_member'];
					$cups_update['id_score_third'] = $cups['id_score'];
			}		
			$loop++;
		}

		$smcFunc['db_free_result']($result);
		updateGame($id_game,$cups_update);
		
}



function ArcadeSendPM($type, $to, $game, $extra = null)
{
	global $sourcedir, $txt, $user_info, $id_member;
	
	$id_member = $user_info['id'];
	
	if ($to == $id_member)
		return false;
/*	
	$query = "
		SELECT value
		FROM {db_prefix}themes
		WHERE id_member = $to AND variable = 'arcade_messages'";
		$row = Arcade_Get_Row($query);
	
	 

	if ($row['value'] == "0")
	{
		return false;
	}
*/
	if ($type == 'new_champion')
	{
		// Some beat your
		
		require_once($sourcedir . '/Subs-Post.php');
		
		$subject = sprintf($txt['arcade_pm_champion_beat_subject'], $game['name']);
		$message = $txt['arcade_pm_automatic_notification'] . ' ' . sprintf($txt['arcade_pm_champion_beat_mesage'], $user_info['name'], '[iurl=' . $game['url']['play'] . ']' . $game['name'] . '[/iurl]', $game['url']['play']);
		
		return sendpm(array('to' => array($to), 'bcc' => array()), addslashes($subject), addslashes($message));
	}
	
	return false;
}

//returns an array of Category info
function &prepareCategories()
{
	global $smcFunc;
	
	static $category = array(), $run = false;
	
	if ($run)
		return $category;
	
	$result = $smcFunc['db_query']('', '
		SELECT 
            id_category, category_name, member_groups, special, category_order, category_icon
		FROM {db_prefix}arcade_categories
		ORDER BY category_order',
		array()
		);					
		while ($cat = $smcFunc['db_fetch_assoc']($result))	
		{
		$category[ $cat['id_category'] ] = array(
			'id' => $cat['id_category'],
			'name' => $cat['category_name'],
			'default' => $cat['special'] == 1 ? true : false,
			'canRemove' => $cat['special'] == 1 ? false : true,
			'member_groups' => explode(',', $cat['member_groups']),
			'icon' => $cat['category_icon'],
			'order' => $cat['category_order'],
		);
	}
	$run = true;
	
	$smcFunc['db_free_result']($result);
	
	return $category;
}

// Check - change the game of the day if its a new day
function ArcadeLastUpdate()
{
	global $arcSettings;
		
	if (date('Y-m-d') != $arcSettings['arcade_last_update'])
	{ 
		$updates = array(
		'arcadegotd' => NewGameOfDay(),
		'arcade_last_update' => date('Y-m-d'),
	   );
	
	   saveArcadeSettings($updates);	
	}		
}

// Counts and sets the number of enabled games
function ArcadeCountGames()
{
	global $smcFunc, $arcSettings;
	
	$result = $smcFunc['db_query']('', '
				SELECT count(*) AS games
				FROM {db_prefix}arcade_games WHERE enabled = 1',
				array(
				)
			);
			list ($total_games) = $smcFunc['db_fetch_row']($result);
			$smcFunc['db_free_result']($result);

		$updates = array(
		'arcade_total_games' => $total_games,
		);
		
	saveArcadeSettings($updates);
	
}

// Query with all needed data for random game or game of the day, much more server friendly than the massive gameinfo query
function small_game_query($condition)
{
	global $scripturl, $smcFunc, $arcSettings, $txt, $user_info;
	
	$request = $smcFunc['db_query']('', '
		SELECT
		g.id_game, g.game_name, g.game_rating,
		g.game_directory,g.thumbnail,g.member_groups,
		IFNULL(score.id_score,0) AS id_score, IFNULL(score.score,0) AS champScore,IFNULL(mem.id_member,0) AS id_member,
		IFNULL(mem.real_name,0) AS real_name
		FROM {db_prefix}arcade_games AS g
		  LEFT JOIN {db_prefix}arcade_scores AS score ON (score.id_score = g.id_score_first)
		  LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = g.id_member_first)
		  LEFT JOIN {db_prefix}arcade_categories AS c ON (c.id_category = g.id_category)
		WHERE '.$user_info['query_see_game'].' AND enabled = {int:enabled} '. $condition,
		array(
		  'enabled' => 1,
		)
	);
	while ($game = $smcFunc['db_fetch_assoc']($request))
	{
		//sort the paths for the thumbnail
		$gameico = !$game['game_directory'] ?	$arcSettings['gamesUrl'].$game['thumbnail'] : $arcSettings['gamesUrl'].$game['game_directory']."/".$game['thumbnail'];

		//build and return an arry of whats needed
		$games[$game['game_name']] = array(
		'id' => $game['id_game'],
		'url' => array(
		'play' => $scripturl . '?action=arcade;sa=play;game=' . $game['id_game'],
		),
		'name' => $game['game_name'],
		'rating' => $game['game_rating'],
		'rating2' => round($game['game_rating']),
		'thumbnail' => $gameico,
		'isChampion' => $game['id_score'] > 0 ? true : false,
		'champion' => array(
		'member_id' => $game['id_member'],
		'memberLink' =>  $game['real_name'] != '' ? '<a href="' . $scripturl . '?action=profile;u=' . $game['id_member'] . ';sa=statPanel">' . $game['real_name'] . '</a>' : $txt['arcade_guest'],
		'score' => round($game['champScore'],3),
		),
		);
	}

	$smcFunc['db_free_result']($request);

	return $games;
}

//returns the last topic id
function getLastMessageID()
{
    global $smcFunc;
		
		$result = $smcFunc['db_query']('', '
			SELECT id_last_msg
			FROM {db_prefix}topics 
			ORDER BY id_last_msg DESC
			LIMIT 0 , 1',
				array(
				)
			);
			list ($messageid) = $smcFunc['db_fetch_row']($result);
			$smcFunc['db_free_result']($result);
		
	return $messageid;
}

function add_to_arcade_shoutbox($shout)
{
	global $user_info, $smcFunc, $arcSettings;
		
	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_shouts',
		array('id_member' => 'int','content' => 'string-255', 'time' => 'int'),
		array($user_info['id'],$shout,time()),
		array('id_shout')
	);
	
	if (file_exists($arcSettings['cacheDirectory'].'shout.cache'))
    {
        @unlink($arcSettings['cacheDirectory'].'shout.cache');
    }
	
}

/*
function hasArcadePass()
{
	global $db, $id_member;

	// Check for an arcade pass
	$result = $db->query("
		SELECT m.arcadePass
		FROM {db_prefix}members AS m
		WHERE m.id_member = {$id_member}
		");

	// check to see if the pass has expired
	$pass = mysql_fetch_assoc($result);
	mysql_free_result($result);
	if (time() < $pass["arcadePass"])
	{
		return 1;
	}
	
	// pass has expired, return false
	return 0;
}
*/

/*
// Return the Latest scores
function ArcadeLatestScores($count = 5, $start = 0)
{
	global $scripturl, $txt, $smfChanges;

	$query = "
	SELECT g.id_game, g.game_name, g.thumbnail, g.game_directory, score.score, score.position,
				 IFNULL(mem.id_member, 0) AS id_member, IFNULL(mem.{$smfChanges['real_name']}, '') AS real_name, score.end_time
	FROM ({db_prefix}arcade_scores AS score, {db_prefix}arcade_games AS game)
	LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = score.id_member)
	WHERE g.id_game = score.id_game
	ORDER BY end_time DESC
	LIMIT $start,$count";
	$request = Arcade_Get_Array($query);

	foreach($request as $score)
	{
		$latest_scores[] = array(
		'game_id' => $score['id_game'],
		'name' => $score['game_name'],
		'thumbnail' => $score['thumbnail'],
		'directory' => $score['game_directory'],
		'score' => round($score['score'],3),
		'id' => $score['id_member'],
		'member' => !empty($score['real_name']) ? $score['real_name'] : $txt['arcade_guest'],
		'memberLink' => $score['real_name'] != $txt['arcade_guest'] ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' . $score['real_name'] . '</a>' : $txt['arcade_guest'],
		'time' => timeformat($score['end_time']),
		);
	}
	return $latest_scores;
}
*/

function Arcade_DoToolBarStrip($button_strip, $direction)
{
	global $settings, $txt;

	if (!empty($settings['use_tabs']))
	{
		template_button_strip($button_strip, $direction);
	}
	else
	{
			foreach ($button_strip as $tab)
			{
				echo ' <a href="', $tab['url'], '">', $txt[$tab['text']], '</a>';

				if (empty($tab['is_last']))
					echo ' | ';
			}



	}

}
?>