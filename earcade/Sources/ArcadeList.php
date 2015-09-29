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
* ArcadeList.php                                                               *
********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file handles Arcade and loads required files.

	void ArcadeList()
		- ???

	void ArcadeRate()
		- ???

	void ArcadeFavorite()
		- ???

	void ArcadeSearch()
		- ???

	void ArcadeSearchXML()
		- ???

*/

function ArcadeList()
{
 	global $smcFunc, $scripturl, $txt, $arcSettings, $context, $user_info, $smfChanges, $sourcedir;

	//some vars we need to setup
	$gamesPerPage = isset($arcSettings['gamesPerPage']) ? $arcSettings['gamesPerPage'] : 25;
	$where = "enabled = 1 AND $user_info[query_see_game]";
	$search = false;

	if (isset($_REQUEST['sc']))
	{
		setcookie("SMFArcadeMod[page]",$_REQUEST['start'],0);
		$pageStart = (int)$_REQUEST['start'];
	}
	elseif (isset($_COOKIE['SMFArcadeMod']['page']))
	{
		$pageStart = (int)$_COOKIE['SMFArcadeMod']['page'];
	}
	else
	{
		$_COOKIE['SMFArcadeMod']['page']=0;
		$pageStart = 0;
	}

	if (isset($_REQUEST['category']))
	{
		$search = true;
		$category = (int) $_REQUEST['category'];
		$where .= " AND g.id_category = ".$category;
	}

	if (isset($_REQUEST['name']))
	{
		$search = true;
		$name = htmlspecialchars($_REQUEST['name'],ENT_QUOTES);
		$where .= " AND g.game_name LIKE '%$name%'";
	}

	if (isset($_REQUEST['sort'])&& $_REQUEST['sort']=='idr')
	{
		$gameCount = $gamesPerPage;
		$_REQUEST['sort'] = 'id';
	}

	if (isset($_REQUEST['favorites']))
	{
		$search = true;
		$favorite = true;
		$favorite_join = "JOIN";
	}
	else
	{
		$favorite_join = "LEFT JOIN";
		$favorite = false;
	}

	if (!isset($gameCount))
	{
		// How many games there are
		if (isset($favorite) && $favorite == true)
		{
			$result = $smcFunc['db_query']('', '
				SELECT count(*) AS gc
				FROM ({db_prefix}arcade_games AS g, {db_prefix}arcade_favorite AS f)
				LEFT JOIN {db_prefix}arcade_categories AS c ON (c.id_category = g.id_category)
				WHERE f.id_game = g.id_game
				AND f.id_member = {int:mem}
				AND '.$where.'',
				array(
				'mem' => $user_info['id'],
				)
			);
		}
		else
		{
			$result = $smcFunc['db_query']('', '
				SELECT count(*) AS gc
				FROM {db_prefix}arcade_games AS g
				LEFT JOIN {db_prefix}arcade_categories AS c ON (c.id_category = g.id_category)
				WHERE '.$where.'',
				array(
				)
			);

		}
		$row = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		$gameCount = $row[0];
	}

	$parts = array(
		$scripturl . '?action=arcade;sc=1'
	);

	// Sorting methods
	$sort_methods = array(
		'id' => 'g.id_game',
		'name' => 'g.game_name',
		'plays' => 'g.number_plays',
		'champs' => 'champion_time',
		'champion' => 'real_name1',
		'myscore' => 'IFNULL(pb.score, 0)',
		'category' => 'c.category_name, g.game_name',
		'rating' => 'g.game_rating',
		'favorite' => 'IF(f.id_favorite = null, 0, 1)'
	);

	if (isset($_REQUEST['sort']) && isset($sort_methods[$_REQUEST['sort']]))
		$parts[] = 'sort=' . $_REQUEST['sort'];
	if (isset($_REQUEST['desc']))
		$parts[] = 'desc';

	if ($search)
	{
		if (isset($category))
			$parts[] = 'category=' . $category;

		if (isset($name))
			$parts[] = 'name=' . urlencode($name);

		if (isset($favorite) && $favorite == true)
			$parts[] = 'favorites';
	}

	$context['arcade']['pageIndex'] = constructPageIndex( implode(';', $parts) , $pageStart, $gameCount , $gamesPerPage, false );

	// How user wants to sort games?
	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
	{
		$context['sort_by'] = 'name';
		$_REQUEST['sort'] = 'g.game_name';
	}
	else
	{
		$context['sort_by'] = $_REQUEST['sort'];
		$_REQUEST['sort'] = $sort_methods[$_REQUEST['sort']];

	}

	$ascending = !isset($_REQUEST['desc']);
	$sort_query = $_REQUEST['sort'].($ascending ? '' : ' DESC');

	$context['sort_direction'] = $ascending ? 'up' : 'down';

	$result = $smcFunc['db_query']('', '
		SELECT
			g.id_game, g.internal_name, g.game_name, g.game_file,
			g.game_directory, g.description, g.help,
			g.thumbnail, g.id_category, g.enabled,
			g.member_groups, g.score_type, g.game_rating,
			g.id_member_first, g.id_score_first, g.id_member_second,
			g.id_score_second, g.id_member_third, g.id_score_third,
			g.game_width, g.game_height, g.game_bg_colour AS bgcolor,
			g.topic_id,
			g.number_plays,
			c.category_name,
			IFNULL(f.id_favorite, 0) AS is_favorite,
			IFNULL(s1.score, 0) AS gold_score,
			IFNULL(s2.score, 0) AS silver_score,
			IFNULL(s3.score, 0) AS bronze_score,
			IFNULL(pb.id_best, 0) AS id_best,
			IFNULL(pb.score, 0) AS best,
			IFNULL(pb.atbscore, 0) AS atbbest,
			IFNULL(s1.start_time, 0) AS champion_time,
			IFNULL(m1.real_name, 0) AS real_name1,
			IFNULL(m2.real_name, 0) AS real_name2,
			IFNULL(m3.real_name, 0) AS real_name3
		FROM {db_prefix}arcade_games as g
		LEFT JOIN {db_prefix}arcade_categories AS c ON (c.id_category = g.id_category)
		'.$favorite_join.' {db_prefix}arcade_favorite AS f ON (f.id_game = g.id_game AND f.id_member = {int:id_mem})
		LEFT JOIN {db_prefix}arcade_scores AS s1 ON (s1.id_score = g.id_score_first)
		LEFT JOIN {db_prefix}arcade_scores AS s2 ON (s2.id_score = g.id_score_second)
		LEFT JOIN {db_prefix}arcade_scores AS s3 ON (s3.id_score = g.id_score_third)
		LEFT JOIN {db_prefix}arcade_personalbest AS pb ON (pb.id_game = g.id_game AND pb.id_member = {int:id_mem})
		LEFT JOIN {db_prefix}members AS m1 ON (m1.id_member = g.id_member_first)
		LEFT JOIN {db_prefix}members AS m2 ON (m2.id_member = g.id_member_second)
		LEFT JOIN {db_prefix}members AS m3 ON (m3.id_member = g.id_member_third)
		WHERE '.$where.'
		ORDER BY '.$sort_query.'
		LIMIT '.$pageStart.','.$gamesPerPage.'',
		array(
		'id_mem' => $user_info['id'],
		)
	);

	$context['arcade']['games'] = array();
	$context['arcade']['search'] = $search;


	while ($game = $smcFunc['db_fetch_assoc']($result))
	{
		if ($gameCount == 1 && $search && !$favorite) // Redirect to game if only one result in search and not favorites search
			redirectexit('action=arcade;sa=play;game=' . $game['id_game']);

		$context['arcade']['games'][] = BuildGameArray($game);
	}
	$smcFunc['db_free_result']($result);
    
    
    
    if (!$user_info['is_guest'] && $arcSettings['arcade_active_user']==1)
    {
	   require_once($sourcedir.'/ArcadeSigsStats_v2.php');
	   require_once($sourcedir.'/Who.php');
	   Who();
    }

	$context['sub_template'] = 'arcade_list';
	$context['page_title'] = $txt['arcade_game_list'];

}

function ArcadeRate()
{
	global $smcFunc, $txt, $arcSettings, $context, $user_info;

	$xml = isset($_REQUEST['xml']);
	$game = ArcadeGameInfo((int) $_REQUEST['game']); // Get game info

	if ($game === false)
		fatal_lang_error('arcade_game_not_found'); // Game was not found

	$rate = (int) $_REQUEST['rate'];
	if ($rate < 0 || $rate > 5)
		fatal_lang_error('arcade_rate_error');  // Don't allow invalid rates



	// To ensure there will be no doubles
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_rates
		WHERE id_member = {int:mem}
		AND id_game = {int:game}',
		array(
			'mem' => $user_info['id'],
			'game' => $game['id'],
		)
	);


	if ($rate > 0)
	{
		$smcFunc['db_insert']('',
			'{db_prefix}arcade_rates',
			array(
			'id_member' => 'int',
			'id_game' => 'int',
			'rating' => 'int',
			'rate_time' => 'int'),
			array($user_info['id'],$game['id'],$rate,time()),
			array('id_rating')
			);
	}

	// Update rating
		$result = $smcFunc['db_query']('', '
			SELECT SUM(rating) AS rating, COUNT(rating) AS rates
			FROM {db_prefix}arcade_rates
			WHERE id_game = {int:game}
			GROUP BY id_game',
			array(
			'game' => $game['id'],
			)
		);


	$row = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);
	$rate2 = $row[0] / $row[1];

	UpdateGame($game['id'], array('game_rating' => $rate2));

   	if (!$xml)
   	{
		// Go to reffering page (or highscore page)
		if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == '')
			redirectexit('action=arcade;sa=highscore;game=' . $game['id']);
		else
			redirectexit($_SERVER['HTTP_REFERER']);
	}
	else
	{
   		   $context['sub_template'] = 'xml';
   		   $context['arcade']['message'] = 'arcade_rating_saved';
   		   $context['arcade']['extra'] = '<rating>' . $rate2 . '</rating>';
	}
}

function ArcadeFavorite()
{
	global $smcFunc, $txt, $db, $arcSettings, $context, $user_info;

	$xml = isset($_REQUEST['xml']) ? true : false;

	if ($user_info['is_guest'])
		fatal_lang_error('arcade_not_for_guest');

   	$game = ArcadeGameInfo((int) $_REQUEST['game']);

	if ($game === false)
		fatal_lang_error('arcade_game_not_found'); // Game was not found

	// It's favorite so we can remove it
	if ($game['isFavorite'])
	{
		$remove = true;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_favorite
			WHERE id_member = {int:mem}
			AND id_game = {int:game}',
			array(
				'mem' => $user_info['id'],
				'game' => $game['id'],
			)
		);

	}
	// It's not favorite, let's add it
	else
	{
		$remove = false;

		$smcFunc['db_insert']('',
			'{db_prefix}arcade_favorite',
			array(
			'id_member' => 'int',
			'id_game' => 'int'),
			array($user_info['id'],$game['id']),
			array('id_favorite')
		);
	}

	if (!$xml)
	{
		if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == '')
			redirectexit('?action=arcade');
		else
			redirectexit($_SERVER['HTTP_REFERER']);
	}

	else
	{
		$state = $remove ? 0 : 1;
		$context['sub_template'] = 'xml';
   		$context['arcade']['message'] = $remove ? 'arcade_favorite_removed' : 'arcade_favorite_added';
   		$context['arcade']['extra'] = '<state>' . $state . '</state>';
	}
}

function ArcadeSearchXML()
{
	global $smcFunc, $scripturl, $txt, $db, $arcSettings, $context, $user_info;

	$limit = 5;

	$search = '%'.addslashes($_REQUEST['name']).'%';

	$result = $smcFunc['db_query']('', '
		SELECT count(*) AS games
		FROM {db_prefix}arcade_games as g
		LEFT JOIN {db_prefix}arcade_categories AS c ON (c.id_category = g.id_category)
		WHERE g.game_name LIKE {string:game} AND '.$user_info['query_see_game'].'
		ORDER BY game_name',
		array(
		'game' => $search,
		)
	);
	$row = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);
	$how_many = $row[0];

	$context['arcade']['search'] = array();
	$context['arcade']['search']['games'] = array();
	$context['arcade']['search']['more'] = $how_many > $limit ? 1 : 0;
	$context['arcade']['search']['more_url'] = $scripturl . '?action=arcade;name=' . urlencode($_REQUEST['name']);

	$result = $smcFunc['db_query']('', '
		SELECT g.id_game, g.game_name
		FROM {db_prefix}arcade_games as g
		LEFT JOIN {db_prefix}arcade_categories AS c ON (c.id_category = g.id_category)
		WHERE g.game_name LIKE {string:game} AND '.$user_info['query_see_game'].'
		ORDER BY game_name
		LIMIT 0, {int:limit}',
		array(
		'game' => $search,
		'limit' => $limit,
		)
	);

	while ($game = $smcFunc['db_fetch_assoc']($result))
	{
		$context['arcade']['search']['games'][] = array(
		'name' => $game['game_name'],
		'id' => $game['id_game'],
		'url' => $scripturl . '?action=arcade;game=' . $game['id_game']
		);
	}
	$smcFunc['db_free_result']($result);


	$context['sub_template'] = 'xml_list';
}

function ArcadeFrontPage()
{
 global $scripturl, $txt, $arcSettings, $context, $user_info;
	//$frontPage = array(0 => 'list', 1 => 'latest', 2 => 'random' );

	$switcher = $arcSettings['gameFrontPage']==1 ? rand(2,5) : $arcSettings['gameFrontPage'];

	switch ($switcher)
	{
		case 2:
		$condition = 'ORDER BY g.number_plays DESC LIMIT 0,4';
		$context['arcade']['frontPage']['games']= small_game_query($condition);
		$context['arcade']['frontPage']['pageName'] = $txt['arcade_most_played'];
		break;

		case 3:
		$condition = 'ORDER BY g.number_plays LIMIT 0,4';
		$context['arcade']['frontPage']['games'] = small_game_query($condition);
		$context['arcade']['frontPage']['pageName'] = $txt['arcade_LeastPlayed'];
		break;

		case 4:
		$condition = 'ORDER BY g.id_game DESC LIMIT 0,4';
		$context['arcade']['frontPage']['games'] = small_game_query($condition);
		$context['arcade']['frontPage']['pageName'] = $txt['arcade_LatestGames'];
		break;

		case 5:
		$condition = 'ORDER BY g.game_rating DESC LIMIT 0,4';
		$context['arcade']['frontPage']['games'] = small_game_query($condition);
		$context['arcade']['frontPage']['pageName'] = $txt['arcade_RatedGames'];
		break;

		default:
			$context['arcade']['frontPage']['pageName'] = 'Error';
		break;
	}

	$context['sub_template'] = 'arcade_front_page';
	$context['page_title'] = $txt['arcade_game_list'];


}
?>