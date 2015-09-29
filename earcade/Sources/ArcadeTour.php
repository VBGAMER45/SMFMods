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
ArcadeTour.php
*****************************************************************************/
/*	This file contains functions for the tournament section

	ArcadeTour()
		- ???

	ArcadeTourJoin()
		- ???

	ArcadeTourPlay()
		- ???

	ArcadeTourDel()
		- ???

	ArcadeTourNew()
		- ???

	ArcadeTourShow()
		- ???

	checkTourPass($id,$pass)
		- ???

	countTourPlayers($id)
		- ???

	joinTournament($id,$players)
		- ???

	setTourStatus($id)
		- ???

	ArcadeGames()
		- is sent the number of rounds from ajax, builds and returns the game selection boxes back to ajax

*/

if (!defined('SMF'))
	die('Hacking attempt...');


function ArcadeTour()
{
 	global $scripturl, $txt, $arcSettings, $context, $user_info;

	$subActions = array(
		'show' => array('ArcadeTourShow', 'arcade_play'),
		'new' => array('ArcadeTourNew', 'arcade_play'),
		'join' => array('ArcadeTourJoin', 'arcade_play'),
		'play' => array('ArcadeTourPlay', 'arcade_play'),
		'del' => array('ArcadeTourDel', 'arcade_play'),
		'delplay' => array('ArcadeDelPlayer', 'arcade_play'),
	);

	// What user wants to do?
	$_REQUEST['ta'] = isset($_REQUEST['ta']) && isset($subActions[$_REQUEST['ta']]) ? $_REQUEST['ta'] : 'show';
	// Do we have reason to allow him/her to do it?
	isAllowedTo($subActions[$_REQUEST['ta']][1]);

	$context['page_title'] = $txt['arcade'].' - '.$txt['arcade_tour_tour'];

	$subActions[$_REQUEST['ta']][0]();

}

function ArcadeGames()
{
	global $smcFunc, $txt, $context;

	$rounds = $_REQUEST['rounds'];
	$i = 1;
	$contentl='';
	$contentr='';

	$result = $smcFunc['db_query']('', '
		SELECT id_game, game_name
		FROM {db_prefix}arcade_games
		WHERE enabled = 1
		ORDER BY game_name',
		array(
		)
	);
	while ($score = $smcFunc['db_fetch_assoc']($result))
	{
		$games_list[] = array(
			'id' => $score['id_game'],
			'name' => $score['game_name'],
		);
	}

	$content='<div class="maintour"><b>'.$txt['arcade_tour_select_games'].'</b></div>';
	$content.='<div class="lefty">';

	while($i <= $rounds)
	{
		$contentl.='<div class="left">'.$txt['arcade_tour_round'].' '.$i.':</div>';
		$contentr.='<div class="right"><select name="game[]">';
		foreach($games_list as $game)
		{
			$contentr.='<option value="'.$game['id'].'">'.$game['name'].'</option>';
		}
		$contentr.='</select></div>';
		$i++;
	}
	$content.=$contentl;
	$content.='</div><div class="righty">';
	$content.=$contentr;
	$content.='</div>';
	$content.='<div class="maintour"><input type="submit" value="'.$txt['arcade_tour_continue'].'" /></form></div>';

	$context['sub_template'] = 'xml';
	$context['arcade']['message'] = $content;
}

function ArcadeTourJoin()
{
	global $smcFunc, $scripturl, $txt, $arcSettings, $context, $user_info;

	isAllowedTo('arcade_playtour');

	//First get the tour info so we have everything we need
	$result = $smcFunc['db_query']('', '
		SELECT
			t.id_tour,
			t.id_member,
			t.name,
			t.rounds,
			t.tour_start_time,
			t.players,
			t.password,
			t.round_data,
			t.active,
			t.results,
			m.real_name AS creator
			FROM {db_prefix}arcade_tournament AS t
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = t.id_member)
			WHERE t.id_tour = {int:tour}',
			array(
			'tour' => $_REQUEST['id'],
			)
		);
	$tour = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

		//if someone wants to join add them now
	if(isset($_REQUEST['pass']))
	{
		if(checkTourPass($tour['id_tour'],$_REQUEST['pass']))
		{
			joinTournament($tour['id_tour'],$tour['players']);
		}
		else
		{
			$tour['passFailed'] = true;
		}
	}
	elseif(isset($_REQUEST['in']))
	{
		joinTournament($tour['id_tour'],$tour['players']);
	}

	//get the player info - new players will already be added
	$thePlayers = array();

	$result = $smcFunc['db_query']('', '
		SELECT 	p.id_member, m.real_name AS players
		FROM {db_prefix}arcade_tournament_players AS p
		LEFT JOIN {db_prefix}members AS m ON (m.id_member = p.id_member)
		WHERE p.id_tour ={int:tour2}',
			array(
			'tour2' => $_REQUEST['id'],
			)
		);

		if (!$result)
			fatal_lang_error('arcade_general_query_error');


		while ($p = $smcFunc['db_fetch_assoc']($result))
		{
			$thePlayers[$p['id_member']] = array(
			'players' => $p['players'],
			'total' => 0,
			);
		}

	//get the games for the rounds
	$result = $smcFunc['db_query']('', '
		SELECT 	r.id_tour, r.id_round, r.round_number, r.id_game, g.game_name, g.score_type
		FROM {db_prefix}arcade_tournament_rounds AS r
		LEFT JOIN {db_prefix}arcade_games AS g ON (g.id_game = r.id_game)
		WHERE id_tour = {int:tour3}
		ORDER BY g.id_game',
			array(
			'tour3' => $tour['id_tour'],
			)
		);
	while ($r = $smcFunc['db_fetch_assoc']($result))
	{
		$rounds[] = array(
			'id_round' => $r['id_round'],
			'id_tour' => $r['id_tour'],
			'round_number' => $r['round_number'],
			'id_game' => $r['id_game'],
			'game_name' => $r['game_name'],
			'score_type' => $r['score_type'],
		);
	}
	$smcFunc['db_free_result']($result);


	//get the scores for this tour
	$result = $smcFunc['db_query']('', '
		SELECT 	*
		FROM {db_prefix}arcade_tournament_scores
		WHERE id_tour = {int:tour4}
		ORDER BY id_game',
			array(
			'tour4' => $tour['id_tour'],
			)
		);
	while ($score1 = $smcFunc['db_fetch_assoc']($result))
	{
		$scores[] = array(
			'id_tour_score' => $score1['id_tour_score'],
			'id_member' => $score1['id_member'],
			'id_game' => $score1['id_game'],
			'id_tour' => $score1['id_tour'],
			'score' => $score1['score'],
			'time' => $score1['time'],
			'round_number' => $score1['round_number'],
		);
	}

	//if all the rounds/scores are in, set the tour status to complete
	$s = $smcFunc['db_num_rows']($result);
	$smcFunc['db_free_result']($result);
	if($s == $tour['rounds']*$tour['players'] && $tour['active']!=2)
	{
		setTourStatus($tour['id_tour']);
	}

	//if tour is complete work out the players points/positions
	if($tour['active']==2)
	{
		$i=1;
		foreach($rounds as $id => $g)
		{
			$thePoints = 1000;
			$sort = $g['score_type'] == 0 ? 'DESC' : 'ASC';

			$result = $smcFunc['db_query']('', '
				SELECT
				score,
				id_member
				FROM {db_prefix}arcade_tournament_scores
				WHERE id_game = {int:gid}
				AND id_tour = {int:tour5}
				AND round_number = {int:rid}
				ORDER BY score '.$sort.', time ASC',
					array(
					'tour5' => $tour['id_tour'],
					'gid' => $g['id_game'],
					'rid' => $i,
					)
				);

		if (!isset($result))
			fatal_lang_error('arcade_general_query_error');

		while ($pt = $smcFunc['db_fetch_assoc']($result))
		{
				$thePlayers[$pt['id_member']]['total'] = $thePlayers[$pt['id_member']]['total']+$thePoints;
				$thePoints = $thePoints - 100;
		}
		$smcFunc['db_free_result']($result);
		$i++;
		}

		//work out the winner
		$i=0;
		$winnerScore = 0;
		$winner[$i] = '';

		foreach($thePlayers as $key => $players)
		{
			//if its a higher score clear the array and add the winner to [0]
			if($players['total'] > $winnerScore)
			{
				foreach ($winner as $x => $value)
				{
				unset($winner[$x]);
				}
				$i=0;
				$winner[$i] = $players['players'];
				$winnerScore = $players['total'];
				$i++;
			}
			//if its an equal score we need to keep all players as its a draw
			elseif($players['total'] == $winnerScore)
			{
				$winner[$i] = $players['players'];
				$winnerScore = $players['total'];
				$i++;
			}
		}

		//add the winner/s to the db
		if($tour['results']==0)
		{
			$res = implode(",", $winner);
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_tournament
				SET
				results = {string:res}
				WHERE id_tour = {int:tid}',
				array(
				'tid' => $tour['id_tour'],
				'res' => $res,
				)
			);
		}
		$context['arcade']['tour']['winner'] = $winner;
	}


	$context['arcade']['tour']['players'] = $thePlayers;
	$context['arcade']['tour']['scores']	= isset($scores) ? $scores : 0;
	$context['arcade']['tour']['rounds']	= $rounds;
	$context['arcade']['tour']['tourdata'] = $tour;
	$context['arcade']['tour']['show'] = 3;
	$context['sub_template'] = 'arcade_tour_join';
}

function ArcadeTourPlay()
{
	global $sourcedir, $context;

	require_once($sourcedir . '/ArcadePlay.php');

	ArcadePlay($_REQUEST['tid'],$_REQUEST['gid'],$_REQUEST['rid']);

}

function ArcadeDelPlayer()
{
	global $smcFunc;

	if (isset($_REQUEST['u'])&&isset($_REQUEST['tid']))
	{
		$memid = (int) $_REQUEST['u'];
		$tourid = (int) $_REQUEST['tid'];

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_tournament_players
			WHERE id_tour = {int:idt}
			AND id_member = {int:idm}',
			array(
			'idt' => $tourid,
			'idm' => $memid,
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_tournament_scores
			WHERE id_tour = {int:idt}
			AND id_member = {int:idm}',
			array(
			'idt' => $tourid,
			'idm' => $memid,
			)
		);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_tournament
			SET
			active = 0
			WHERE id_tour = {int:idt}',
			array(
			'idt' => $tourid,
			)
		);

		if(isset($_REQUEST['lower']))
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_tournament
				SET
				players = players-1
				WHERE id_tour = {int:idt}',
				array(
				'idt' => $tourid,
				)
			);
		}

	}
	redirectexit('action=arcade;sa=tour;ta=join;id='.$tourid);
}

function ArcadeTourDel()
{
	global $smcFunc;

	if(isset($_REQUEST['idd']))
	{
		$id = (int) $_REQUEST['idd'];

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_tournament
			WHERE id_tour = {int:idt}',
			array(
			'idt' => $id,
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_tournament_players
			WHERE id_tour = {int:idt}',
			array(
			'idt' => $id,
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_tournament_scores
			WHERE id_tour = {int:idt}',
			array(
			'idt' => $id,
			)
		);

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_tournament_rounds
			WHERE id_tour = {int:idt}',
			array(
			'idt' => $id,
			)
		);
	}
	redirectexit('action=arcade;sa=tour');
}

function ArcadeTourShow()
{
	global $smcFunc, $scripturl, $txt, $arcSettings, $context, $user_info;

	$result = $smcFunc['db_query']('', '
		SELECT COUNT(id_tour)AS number
		FROM {db_prefix}arcade_tournament',
		array(
		)
	);
	$tours = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	if(!isset($_GET['start']))
	{
		$limit = "0,{$arcSettings['gamesPerPage']}";
	}
	else
	{
		$limit = "{$_GET['start']},{$arcSettings['gamesPerPage']}";
	}

	if(isset($_REQUEST['show']))
	{
			$show = ' = 2';
			$context['arcade']['tour']['show']=1;
	}
	else
	{
			$show = ' < 2';
			$context['arcade']['tour']['show']=2;
	}



	$context['arcade']['tour']['pageindex'] = constructPageIndex($scripturl.'?action=arcade;sa=tour',$_GET['start'],$tours['number'],$arcSettings['gamesPerPage']);

	$i = 0;

	 $result = $smcFunc['db_query']('', '
		SELECT
			t.id_tour,
			t.id_member,
			t.name,
			t.rounds,
			t.tour_start_time,
			t.players,
			t.password,
			t.round_data,
			t.active,
			t.results,
			m.real_name AS creator
			FROM {db_prefix}arcade_tournament AS t
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = t.id_member)
			WHERE t.active '.$show.'
			ORDER BY t.active, t.tour_start_time
			LIMIT '.$limit.'',
			array(
			)
		);

		if(isset($result))
		{
			while ($row = $smcFunc['db_fetch_assoc']($result))
			{
			$context['arcade']['tour']['list'][$i] = $row;
			$context['arcade']['tour']['list'][$i]['joined'] = $row['players'];
			if($row['active']==0)
			{
				$context['arcade']['tour']['list'][$i]['joined'] = countTourPlayers($row['id_tour']);
			}
			$i++;
			}
		}
		$smcFunc['db_free_result']($result);
	$context['sub_template'] = 'arcade_tour_show';
}

function ArcadeTourNew()
{
	global $smcFunc, $context, $user_info;

	isAllowedTo('arcade_createtour');

	if(isset($_REQUEST['step']) &&  $_REQUEST['step'] == 1)
	{
		$posts['name'] = $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES);
		$posts['rounds'] = (int)$_POST['rounds'];
		$posts['players'] = (int)$_POST['players'];
		$posts['pass'] = !empty($_POST['pass']) ? @md5($_POST['pass']) : '';
		$games = $_REQUEST['game'];

		$time = time();

		$smcFunc['db_insert']('',
			'{db_prefix}arcade_tournament',
				array(
				'id_member' => 'int',
				'players' => 'int',
				'tour_start_time' => 'int',
				'round_data' => 'string',
				'name' => 'string',
				'password' => 'string-50',
				'active' => 'int',
				'rounds' => 'int',
				'results' => 'string-50'),
				array($user_info['id'],$posts['players'],$time,0,$posts['name'],$posts['pass'],0,$posts['rounds'],0),
				array('id_tour')
		);

		$last = $smcFunc['db_insert_id']('{db_prefix}arcade_tournament', 'id_tour');

		$round=1;
		foreach($games as $game)
		{
			$smcFunc['db_insert']('',
				'{db_prefix}arcade_tournament_rounds',
					array(
					'id_tour' => 'int',
					'round_number' => 'int',
					'id_game' => 'int'),
					array($last,$round,$game),
					array('id_round')
			);

			$round++;
		}
		joinTournament($last,$posts['players']);

		redirectexit('action=arcade;sa=tour');
	}
	$context['arcade']['tour']['show'] = 4;
	$context['sub_template'] = 'arcade_tour_new';
}

function joinTournament($id,$players)
{

	global $smcFunc, $user_info;

	$smcFunc['db_insert']('',
		'{db_prefix}arcade_tournament_players',
			array(
			'id_tour' => 'int',
			'id_member' => 'int'),
			array($id,$user_info['id']),
			array('id_tour_player')
	);

	if($players == countTourPlayers($id))
	{
		setTourStatus($id);
	}

	return true;
}

function setTourStatus($id)
{
	global $smcFunc;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_tournament
		SET
		active=active+1
		WHERE id_tour = {int:idt}',
		array(
		'idt' => $id,
		)
	);

	return true;
}

function checkTourPass($id,$pass)
{
	global $smcFunc;

	$result = $smcFunc['db_query']('', '
		SELECT password
		FROM {db_prefix}arcade_tournament
		WHERE id_tour = {int:idt}',
		array(
		'idt' => $id,
		)
	);
	$row = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);

	if (@md5($pass) == $row[0])
	{
		return true;
	}
	return false;
}


function countTourPlayers($id)
{
	global $smcFunc;

	$result = $smcFunc['db_query']('', '
		SELECT COUNT(id_tour_player) AS players
		FROM {db_prefix}arcade_tournament_players
		WHERE id_tour = {int:idt}',
		array(
		'idt' => $id,
		)
	);
	$row = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);

	return $row[0];
}

?>