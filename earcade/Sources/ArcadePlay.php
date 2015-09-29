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
ArcadePlay.php
********************************************************************************/
/*
	ArcadePlay()
		-sets up the game sessions and loads the game info ready to play

	ArcadeComment()
		- inserts or edits comments

	template_arcade_popup_play()
		- Template for a popup game

	arcade_post_comment()
		- posts comments to forum topics

	ArcadeHighscore()
		- sorts scores ready for display

	CheatingCheck(&allowFail, &checkPassed)
		- Does basic cheating check

	ArcadeSubmit()
		- Handles score submits and cheat checks

	ClearSession()
		- Deletes a game $SESSION

	ArcadeVerifyIBP
		- Verifies scores for IBP V32 games

	ArcadeVerifyv3
		- Verifies scores for VBulitin V3 games

	ArcadeSaveScore(id_game, score, start_time, end_time, score_status, [reverse = false])
		- Saves score into database, handling everything needed

	ArcadeUpdatePositions()
		-Used by save score to current positions

	add_to_arcade_shoutbox()
		- Adds shouts to the database

	microtime_floar()
		- Returns microtime - needed for timing v3 games
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function ArcadePlay($tourid=0,$tourGame=0,$round_no=0)
{
 	global $smcFunc,$scripturl, $txt,$settings, $context, $arcSettings, $modSettings;


	//Added for shop mod
	if (isset($modSettings['shopCostPerPlay'])&& $modSettings['shopCostPerPlay'] > 0)
	{
		$credit = checkShopCredits();

		if ($credit < $modSettings['shopCostPerPlay'])
		{
			//cant play
			fatal_lang_error('cannot_arcade_play_credit');
		}
	}
	// end shop mod

 	if (!$context['arcade']['can_play'])
 		fatal_lang_error('cannot_arcade_play');

	if (isset($_REQUEST['random']) && !isset($_REQUEST['game']) && !$xml) // Get random game if it is requested and there isn't game set
		$id = 0;

	elseif (!empty($_REQUEST['game']))
		$id = (int) $_REQUEST['game'];

	elseif ($tourGame !=0)
	{
		$id = $tourGame;
	}

	else
		fatal_lang_error('arcade_game_not_found');

	// Fetch gameinfo from database (function defined in Arcademain.php)
	if (!$game = ArcadeGameInfo($id))
		fatal_lang_error('arcade_game_not_found');

	//kill of any previous play session
	unset($_SESSION['arcade']);

	//starttime
	$gstarttime = microtime_float();

	//setup the temp score
	$smcFunc['db_insert']('',
		'{db_prefix}arcade_v3temp',
		array(
			'game' => 'string-50',
			'score' => 'int',
			'starttime' => 'float'),
		array($game['internal_name'],0,$gstarttime),
		array('id')
	);

	$x = $smcFunc['db_insert_id']('{db_prefix}arcade_v3temp', 'id');

	//setup new play session
	$_SESSION['arcade']['ibp']['gamename'] = $game['internal_name'];
	$_SESSION['arcade']['play'][$game['internal_name']] = array(
		'game' => $game['internal_name'],
		'id' => $game['id'],
		'starttime' => $gstarttime,
		'db_id' => $x,
	);

	// Increase plays by one
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_games
		SET
		number_plays = number_plays + 1
		WHERE id_game = {int:game}',
		array(
			'game' => $game['id'],
		)
	);

	$context['arcade']['game'] = &$game;

	$gamecode = '<script type="text/javascript" src="'.$settings['default_theme_url'].'/scripts/swfobject.js"></script>
		<div id="game">'.$txt['arcade_no_flash'].'
		</div>
			<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
				var so = new SWFObject("'.$game['url']['flash'].'", "' . $game['file'] . '", "' . $game['flash']['width'] . '", "' . $game['flash']['height'] . '", "7");
				so.addParam("menu", "false");
				so.write("game");
				// ]]></script>';

	if (isset($_REQUEST['pop']) && $_REQUEST['pop']==1)
	{
		$_SESSION['arcade']['pop'] = 1;
		$context['arcade']['popup']= $gamecode.'<div align="center"><a href="javascript:self.close();">'. $txt['arcade_close']. '</a></div>';
		$context['template_layers'] = array();
		$context['sub_template'] = 'arcade_popup_play';
	}
	else
	{
		if ($tourGame !=0)
		{
			$_SESSION['arcade']['play']['tour'] = $tourid;
			$_SESSION['arcade']['play']['round'] = $round_no;
			$_SESSION['arcade']['play'][$game['internal_name']]['tour'] = $tourGame;
			$_SESSION['arcade']['play'][$game['internal_name']]['tour_round'] = $round_no;
		}
		$context['arcade']['game']['html'] = $gamecode;
		$context['sub_template'] = 'arcade_game_play';
	}

	$context['page_title'] = $txt['arcade_game_play'].' '.$game['name'];
}



function ArcadeComment()
{
	global $smcFunc, $scripturl, $user_info, $txt, $context, $arcSettings;

	$xml = isset($_REQUEST['xml']);

	if ($context['arcade']['can_comment_any'] || $context['arcade']['can_comment_own'])
		$any = $context['arcade']['can_comment_any'];

	else
	{
		// Normal mode
		if (!$xml)
		{
			fatal_lang_error('cannot_arcade_comment_own');
		}
		else // Ajax
		{
			$context['sub_template'] = 'xml';
			$context['arcade']['message'] = 'cannot_arcade_comment_own';
			return false;
		}
	}

	if ($any)
		$where = '1';
	else
		$where = "id_member = ".$user_info['id'];

	$comment = strip_tags($_REQUEST['comment']);
	$score = (int) $_REQUEST['score'];

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_scores
		SET
		comment = {string:com}
		WHERE id_score = {int:ids} AND '.$where.'',
		array(
			'com' => $comment,
			'ids' => $score,
			)
		);

	//IF post the comment to the game topic or shoutbox is turned on in arcade admin
	if ((isset($arcSettings['enable_post_comment'])&& $arcSettings['enable_post_comment'] == 1 && $comment !='')||(isset($arcSettings['enable_shout_comment'])&& $arcSettings['enable_shout_box_comment'] == 1 && $comment !='' ))
	{
		//find the games topic id using the score id - seems thats all we have to go on here
		$result = $smcFunc['db_query']('', '
			SELECT  g.id_game, g.game_name, g.topic_id
			FROM {db_prefix}arcade_games AS g, {db_prefix}arcade_scores AS s
			WHERE s.id_score = {int:ids} AND s.id_game = g.id_game',
			array(
			'ids' => $score,
			)
		);
		$gamesid = $smcFunc['db_fetch_assoc']($result);
		$smcFunc['db_free_result']($result);

		if ($arcSettings['enable_post_comment'] == 1)
		{
	 		arcade_post_comment($txt['arcade_comment'],$gamesid['id_game'], $gamesid['topic_id'], $comment);
		}

		if ($arcSettings['enable_shout_box_comment'] == 1)
		{
			$shout = $gamesid['game_name'].' '.$txt['arcade_comment'].' - '.$comment;
			add_to_arcade_shoutbox($shout);
		}
	}

	$_SESSION['arcade']['highscore']['saved'] = true;

	if ($xml)
	{
		$context['sub_template'] = 'xml';
		$context['arcade']['message'] = parse_bbc(stripcslashes($comment));
	}
	else
		redirectexit('action=arcade;sa=highscore;game=' . (int) $_REQUEST['game']);

}

function template_arcade_popup_play()
{
	global $context, $settings;

echo'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
<title>',$context['page_title'],'</title>
<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />
<style type="text/css">
<!--
body {
	padding: 0px 0px 0px 0px;
}
-->
</style>
</head>
<body>'.$context['arcade']['popup'].'
</body>
</html>';
return;
}


function arcade_post_comment($topicName,$gameid, $topicid, $comment)
{
	global $sourcedir, $user_info, $arcSettings ;

	require_once($sourcedir . '/Subs-Post.php');

	$gamename = addslashes($topicName);
	$topicTalk = addslashes($comment);

	$msgOptions = array(
		'id' => 0,
		'subject' => $gamename,
		'body' => $topicTalk,
		'icon' => "xx",
		'smileys_enabled' => true,
		'attachments' => array(),
		);
	$topicOptions = array(
		'id' => $topicid,
		'board' => $arcSettings['arcadePostTopic'],
		'poll' => null,
		'lock_mode' => null,
		'sticky_mode' => null,
		'mark_as_read' => true,
		);
	$posterOptions = array(
		'id' => $user_info['id'],
		'name' => "arcade",
		'email' => "arcade@here",
		);

	createPost($msgOptions, $topicOptions, $posterOptions);

	if (isset($topicOptions['id']))
	$topicid = $topicOptions['id'];

	return $topicid;
}

function ArcadeHighscore()
{
	global $smcFunc, $scripturl, $txt, $arcSettings, $context, $user_info;

	// Is game set
	if (!isset($_REQUEST['game']))
		fatal_lang_error('arcade_game_not_found');

	// Get game info
	$game = ArcadeGameInfo((int) $_REQUEST['game']);

	if ($game === false || !$game['highscoreSupport'])
		fatal_lang_error('arcade_game_not_found'); // Game was not found


	// Do we show remove score functions?
	$context['arcade']['show_editor'] = $context['arcade']['can_admin'];

	// Do we have scores to delete?
	if ($context['arcade']['can_admin'] && isset($_REQUEST['delete']))
	{
		checkSession('request');

		$_REQUEST['score'] = (int) $_REQUEST['score'];

		$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_scores
		WHERE id_score = {int:ids}',
		array(
			'ids' => $_REQUEST['score'],
			)
		);

		ArcadeFixPositions($game['id']);
	}

	//$context['template_layers'][] = 'ArcadeGame';
	$context['sub_template'] = 'arcade_game_highscore';
	$context['page_title'] = $txt['arcade_view_highscore'];

	// We don't play :)
	$context['arcade']['play'] = false;

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=arcade;sa=play;game=' . $game['id'],
		'name' => $game['name'],
	);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=arcade;sa=highscore;game=' . $game['id'],
		'name' => $txt['arcade_viewscore'],
	);

	$scoresPerPage = isset($arcSettings['scoresPerPage']) ? $arcSettings['scoresPerPage'] : 50;

	// Did we just play
	if (isset($_SESSION['arcade']['highscore']))
	{
		//if it was a popup kill it off
		if (isset($_SESSION['arcade']['pop'])&& $_SESSION['arcade']['pop']==1)
		{
			$context['arcade']['popup']='
					<script type="text/javascript">
					opener.location.href="'.$scripturl.'?action=arcade;sa=highscore;game='.$game['id'].'";
					self.close();
					</script>';
			$context['sub_template'] = 'arcade_popup_play';
			$_SESSION['arcade']['pop']=0;
			return;
		}

		if ($_SESSION['arcade']['highscore']['gameid'] == $game['id'])
		{
			// For highlight
			$newScore = $_SESSION['arcade']['highscore']['saved'];
			$newScore_id = $_SESSION['arcade']['highscore']['id'];

			$context['arcade']['new_score'] = array(
				'id' => $_SESSION['arcade']['highscore']['id'],
				'saved' => $_SESSION['arcade']['highscore']['saved'],
				'error' => isset($_SESSION['arcade']['highscore']['error']) ? $_SESSION['arcade']['highscore']['error'] : '',
				'score' => round($_SESSION['arcade']['highscore']['score'],3),
				'position' => $_SESSION['arcade']['highscore']['position'],
				'can_comment' => $context['arcade']['can_comment_own'] || $context['arcade']['can_comment_any'],
				'is_new_champion' => isset($_SESSION['arcade']['highscore']['champion']) ? $_SESSION['arcade']['highscore']['champion'] : false,
				'is_personal_best' => isset($_SESSION['arcade']['highscore']['best']) ? $_SESSION['arcade']['highscore']['best'] : false,
			);


			if (!isset($_GET['start']) && !isset($_POST['start']))
				$_REQUEST['start'] = $_SESSION['arcade']['highscore']['start'];
		}
		else
		{
			$newScore = false;
			unset($_SESSION['arcade']['highscore']);
		}
	}
	else
		$newScore = false;

	// How many scores there are
	$result = $smcFunc['db_query']('', '
		SELECT count(*) AS sc
		FROM {db_prefix}arcade_scores
		WHERE id_game = {int:game}',
		array(
			'game' => $game['id'],
			)
		);
	$scoreCount = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);


	$score_sort = $game['score_type'] == 0 ? 'DESC' : 'ASC';


	// Actual query

		// Get position
	$request = $smcFunc['db_query']('', '
		SELECT
		scores.id_score, scores.score, scores.end_time, scores.game_duration,
		scores.comment,scores.position,scores.score_status,
		mem.id_member, mem.real_name
		FROM {db_prefix}arcade_scores AS scores
		LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = scores.id_member)
		WHERE scores.id_game = {int:game}
		ORDER BY position
		LIMIT {int:start},{int:spp}',
		array(
			'game' => $game['id'],
			'start' => $_REQUEST['start'],
			'spp' => $scoresPerPage,
			)
		);

	$context['arcade']['scores'] = array();
	$context['arcade']['game']	= $game;

	while ($score = $smcFunc['db_fetch_assoc']($request))
	{
		censorText($score['comment']);
		$own = $user_info['id'] == $score['id_member'];

		$context['arcade']['scores'][] = array(
			'id' => $score['id_score'],
			'own' => $own,
			'memberLink' => !empty($score['real_name']) ? '<a href="' . $scripturl . '?action=profile;u=' . $score['id_member'] . '">' . $score['real_name'] . '</a>' : $txt['arcade_guest'],
			'score' => round($score['score'],3),
			'time' => timeformat($score['end_time']),
			'duration' => $score['game_duration'],
			'position' => $score['position'],
			'score_status' => $score['score_status'],
			'comment' => parse_bbc(!empty($score['comment']) ? $score['comment'] : $txt['arcade_no_comment']),
			'raw_comment' => $score['comment'],
			'highlight' => $newScore ? $score['id_score'] == $newScore_id : false,
			'can_edit' =>  $newScore ? false : $own ? ($context['arcade']['can_comment_own'] || $context['arcade']['can_comment_any']) : $context['arcade']['can_comment_any'],
			'edit' => isset($_REQUEST['edit']) && isset($_REQUEST['score']) && (int) $_REQUEST['score'] == $score['id_score'],
		);
	}

	 $smcFunc['db_free_result']($request);

	// Free results since we don't want to use too much memory


	$context['arcade']['pageIndex'] = constructPageIndex( '?action=arcade;sa=highscore;game=' . $game['id'], $_REQUEST['start'], $scoreCount[0] , $scoresPerPage, false );

}

function CheatingCheck(&$allowFail, &$checkPassed)
{
	global $scripturl, $arcSettings;

	// Default check level is 1
	if (!isset($arcSettings['arcadeCheckLevel']))
		$arcSettings['arcadeCheckLevel'] = 1;

	if (!empty($_SERVER['HTTP_REFERER']))
		$referer = parse_url($_SERVER['HTTP_REFERER']);

	$real = parse_url($scripturl);

	// Level 1 Check
	// Checks also HTTP_REFERER if it not is empty
	if ($arcSettings['arcadeCheckLevel'] == 1)
	{
		if (isset($referer) && $real['host'] == $referer['host'] && $real['scheme'] == $referer['scheme'])
			$checkPassed = true;
		elseif (isset($referer))
			$checkPassed = false;
		else
			$checkPassed = true;
	}
	// Level 2 Check
	// Doesn't allow HTTP_REFERER to be empty
	elseif ($arcSettings['arcadeCheckLevel'] == 2)
	{
		if (isset($referer) && $real['host'] == $referer['host'] && $real['scheme'] == $referer['scheme'])
			$checkPassed = true;
		else
			$checkPassed = false;
	}
	// Level 0 check
	else
	{
		$checkPassed = true;
		$allowFail = true;
	}

	if ($allowFail || $checkPassed)
		return true;
	else
		return false;
}

function ArcadeSubmit()
{
	global $smcFunc, $scripturl, $txt, $arcSettings, $context;

	// if you cant save...we do nothing theres no point!!!
	if (allowedTo('arcade_submit'))
	{
		//what type of game is it?
		//normal ipb game
		if (isset($_REQUEST['gametype']) && $_REQUEST['gametype'] == 2)
		{
			$theGame = $_POST['gname'];
			$theScore = isset($_POST['gscore']) && is_numeric($_POST['gscore']) ? (float) $_POST['gscore'] : '';
		}
		//ipb v3 or v3.2
		elseif (isset($_REQUEST['gametype']) && $_REQUEST['gametype'] == 3)
		{
			$theGame = isset($_POST['gname']) ? $_POST['gname'] : $_SESSION['arcade']['ibp']['gamename'];
			$theScore = isset($_POST['gscore']) && is_numeric($_POST['gscore']) ? (float) $_POST['gscore'] : '';
			$time_taken = microtime_float() - $_SESSION['arcade']['ibp']['verify'][2];
			if ($time_taken < 0 || $time_taken > 7)
			{
				unset($_SESSION['arcade']['play']);
				fatal_lang_error('arcade_submit_ibp_error_time');
			}

			if ($_POST['enscore'] != ($theScore * $_SESSION['arcade']['ibp']['verify'][0] ^ $_SESSION['arcade']['ibp']['verify'][1]))
			{
				unset($_SESSION['arcade']['play']);
				fatal_lang_error('arcade_submit_ibp_error_check');
			}
		}
		//v3 vbuilitin game
		elseif (isset($_REQUEST['gametype']) && $_REQUEST['gametype'] == 4)
		{
			$result = $smcFunc['db_query']('', '
				SELECT game , score
				FROM {db_prefix}arcade_v3temp
				WHERE id = {int:game}',
				array(
				'game' => $_POST['id'],
				)
			);
			$tempGame = $smcFunc['db_fetch_assoc']($result);
			$smcFunc['db_free_result']($result);
			if (!isset($tempGame))
			{
				fatal_lang_error('arcade_submit_v3_error');
			}
			$theGame = $tempGame['game'];
			$theScore = $tempGame['score'];
		}
		//smf game
		else
		{
			$theGame = isset($_POST['game']) ? $_POST['game'] : '';
			$theScore = isset($_POST['score']) && is_numeric($_POST['score']) ? (float) $_POST['score'] : '';
		}

		//we should have a game and a score so lets do some checks...
		//if no game or no score or no session were gone...
		if (!isset($theGame) ||!isset($theScore) ||!isset($_SESSION['arcade']['play'][$theGame]))
		{
			unset($_SESSION['arcade']['play']);
			fatal_lang_error('arcade_submit_error_empty');
		}
		else
		{
			//do the cheat check now..
			// Preset these
			$checkPassed = false;
			$allowFail = false;

			if (!CheatingCheck($allowFail, $checkPassed))
			{
				ClearSession($game);
				fatal_lang_error('arcade_submit_error_check_failed');
			}

			//does the posted game match the session game name?
			if ($theGame != $_SESSION['arcade']['play'][ $theGame ]['game'])
			{
				// No..were gone..
				unset($_SESSION['arcade']['play']);
				fatal_lang_error('arcade_game_no_match');
			}

			//we have the game name so lets check it exists and get its info..
			$game = ArcadeGameInfo(0, $theGame);
			if ($game === false)
			{
				// No..were gone..
				unset($_SESSION['arcade']['play']);
				fatal_lang_error('arcade_game_not_found');
			}

			//so far so good..a game that matches, a score, a valid session and a header
			$session_info = &$_SESSION['arcade']['play'][ $theGame ];


			//..so lets check if the session game matches the temp game in the db...
			$result = $smcFunc['db_query']('', '
				SELECT game , score , starttime
				FROM {db_prefix}arcade_v3temp
				WHERE id = {int:game}',
				array(
				'game' => $session_info['db_id'],
				)
			);
			$tempGame = $smcFunc['db_fetch_assoc']($result);
			$smcFunc['db_free_result']($result);
			if (!isset($tempGame))
			{
				fatal_lang_error('arcade_submit_v3_error');
			}

			//..yip so lets check the session info matches the temp game info in the db...
			if ((string)$session_info['starttime'] != $tempGame['starttime'] || $session_info['game'] != $tempGame['game'])
			{
				fatal_lang_error('arcade_submit_error1');
			}

			//if we got this far we have a valid game, a score, and a session so we can go ahead and save...
			$start_time = round($tempGame['starttime']);
			$end_time = time();


			if (isset($_SESSION['arcade']['play']['tour']))
			{
				$save = ArcadeSaveScore($game, $theScore, $start_time, $end_time, $checkPassed, $_SESSION['arcade']['play']['tour'],$_SESSION['arcade']['play']['round']);
				$tour = $_SESSION['arcade']['play']['tour'];
				ClearSession();
				redirectexit('action=arcade;sa=tour;ta=join;id='.$tour);
			}
			else
			{
				$save = ArcadeSaveScore($game, $theScore, $start_time, $end_time, $checkPassed);
				ClearSession();

				// Saving failed
				if ($save === false || $save['id_score'] === false)
				$_SESSION['arcade']['highscore'] = array(
				'id' => false,
				'game' => $game['internal_name'],
				'score' => $theScore,
				'gameid' => $game['id'],
				'position' => 0,
				'start' => 0,
				'saved' => false,
				'error' => isset($save['error']) ? $save['error'] : 'arcade_no_permission'
				);

				// Save succesful
				else
				$_SESSION['arcade']['highscore'] = array(
				'id' => $save['id_score'],
				'game' => $game['internal_name'],
				'score' => $theScore,
				'gameid' => $game['id'],
				'position' => $save['position'],
				'start' => $save['start'],
				'champion' => $save['new_champion'],
				'best' => $save['ownbest'],
				'saved' => true,
				);


				// Go to scores list
				redirectexit('action=arcade;sa=highscore;game=' . $game['id']);
			}
		}
	}
	else
	{
		//cant save
		fatal_lang_error('arcade_no_permission');
	}
}

function ClearSession()
{
	unset($_SESSION['arcade']['play']);
	unset($_SESSION['arcade']['ibp']);
}

function ArcadeVerifyIBP()
{
	global $modSettings;

	$randomchar = rand(1, 200);
	$randomchar2 = rand(1, 200);

	$_SESSION['arcade']['ibp']['verify'] = array($randomchar, $randomchar2, microtime_float());

	// We output flash vars no need for anything that might output something before or after this
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	echo '&randchar=', $randomchar, '&randchar2=', $randomchar2, '&savescore=1&blah=OK';

	obExit(false);
}

function ArcadeVerifyV3()
{
	global $smcFunc, $modSettings;

	if ($_POST['sessdo'] == 'sessionstart')
	{
		//$gstarttime = microtime_float();
		$time =  microtime_float();
		$gamerand = rand(1,10);

		$smcFunc['db_insert']('',
			'{db_prefix}arcade_v3temp',
			array(
				'game' => 'string-50',
				'score' => 'float',
				'starttime' => 'float'),
			array($_POST['gamename'],1,$time),
			array('id')
		);

		$lastid = $smcFunc['db_insert_id']('{db_prefix}arcade_v3temp', 'id');


		// update the session microtime
		$_SESSION['arcade']['play'][$_POST['gamename']]['starttime'] = $time;
		$_SESSION['arcade']['play'][$_POST['gamename']]['db_id'] = $lastid;

		ob_end_clean();
		if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
		else
		ob_start();

		echo "&connStatus=1&initbar=$gamerand&gametime=" . $time . "&lastid=$lastid&result=OK";

		obExit(false);

	}

	if ($_POST['sessdo'] == 'permrequest')
	{

		$note = $_POST['note'];
		$id = $_POST['id'];
		$gametime = $_POST['gametime'];
		$score = $_POST['score'];
		$fakekey = $_POST['fakekey'];


		$ceilscore = ceil($score);
		$noteid = $note/($fakekey * $ceilscore);

		if ($noteid != $id)
		{

			ob_end_clean();
			if (!empty($modSettings['enableCompressedOutput']))
			@ob_start('ob_gzhandler');
			else
			ob_start();

			echo "&validate=0";

			obExit(false);
		}

		// Gets accurate timestamp
		$microone = time();

		// Don't ask.
		if ($score==-1)
		{
			$score = 0;
		}
		$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_v3temp
				SET
				score = {float:score}
				WHERE id = {int:game} AND starttime = {float:gtime}',
				array(
					'score' => $score,
					'game' => $id,
					'gtime' => $gametime,
				)
			);

		ob_end_clean();
		if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
		else
		ob_start();

		echo "&validate=1&microone=$microone&result=OK";

		obExit(false);

	}

}

function ArcadeSaveScore($game, $score, $start_time, $end_time, $score_status, $tour = 0, $round = 0)
{
	global $txt, $smcFunc, $user_info, $arcSettings, $modSettings;

	//dump the cache file were away to change something..probably!!
	if (file_exists($arcSettings['cacheDirectory'].'arcadeinfopanel.cache'))
    {
        unlink($arcSettings['cacheDirectory'].'arcadeinfopanel.cache');
    }

	//delete temp scores for unfinished games older than 4 hours - keep the database free of carp!!
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_v3temp
		WHERE starttime < {float:dt}',
		array(
			'dt' => time() - 14400,
		)
	);

	//sort out some vars we need..
	$id_member = $user_info['id'];
	$id_game = $game['id'];
	$duration = $end_time - $start_time;
	$ip = $user_info['ip'];
	$ownbest = false;
	$reverse = $game['score_type'] == 0 ? '>=' : '<=';
	$score_status = $score_status ? 0 : 1;

	//if its a tournament we'll save the score there as well.
	if ($tour!=0)
	{
		$smcFunc['db_insert']('',
			'{db_prefix}arcade_tournament_scores',
			array(
			'id_member' => 'int',
			'id_tour' => 'int',
			'id_game' => 'int',
			'score' => 'float',
			'time' => 'int',
			'round_number' => 'int'),
			array($id_member,$tour,$id_game,$score,$end_time,$round),
			array('id_tour_score')
			);
	}

	// Get position
	$result = $smcFunc['db_query']('', '
		SELECT count(*) AS position
		FROM {db_prefix}arcade_scores
		WHERE score '.$reverse.' {float:score} AND id_game = {int:game}',
		array(
			'game' => $id_game,
			'score' => $score,
			)
		);
	$row = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);
	$newPosition = $row[0] + 1;

	// If theres max socres limit  then let's check whatever to save or not to save
	if ($game['maxScores'] >= 1)
	{
		//do we have a previous score?
		$result = $smcFunc['db_query']('', '
			SELECT count(*) AS count
			FROM {db_prefix}arcade_scores
			WHERE id_game = {int:game} AND id_member = {int:member}',
			array(
				'game' => $id_game,
				'member' => $id_member,
				)
			);
			list ($my_count) = $smcFunc['db_fetch_row']($result);
			$smcFunc['db_free_result']($result);


		if ($my_count < $game['maxScores'])
		{
			$save = true;
		}
		else
		{
			// Clear out worst scores to maintain maximum number of scores
			// so if it has been changed it will update now

			$to_remove = $my_count - $game['maxScores'] + 1;

			$result = $smcFunc['db_query']('', '
			SELECT id_score, position, score
			FROM {db_prefix}arcade_scores
			WHERE id_game = {int:game} AND id_member = {int:member}',
			array(
				'game' => $id_game,
				'member' => $id_member,
				)
			);
			$remove_id = array();
			while ($myscore = $smcFunc['db_fetch_assoc']($result))
			{
			if (($myscore['position'] > $newPosition && $to_remove > 0) ||
					($myscore['position'] == $newPosition && $to_remove > 0 &&
					(($score > $myscore['score'] && $game['score_type']==0) ||
					($score < $myscore['score'] && $game['score_type']==1))) || $to_remove > 1)
			{
				$remove_id[] = $myscore['id_score'];
				$to_remove--;
			}
			}


			$removed = count($remove_id);

			// Were there scores to be removed?
			if ($removed > 0)
			{
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}arcade_scores
					WHERE id_score IN(' . implode(',', $remove_id) . ')',
					array(
					)
				);
			}

			// Can we add more scores?
			if (($my_count - $removed) < $game['maxScores'])
			{
				$save = true;
			}
			else
			{
				//if we cant save because of too many scores, still update plays and duration
				$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_personalbest
				SET
				my_plays = my_plays+1,
				playing_time = playing_time+ {int:dur}
				WHERE id_game = {int:game} AND id_member = {int:member}',
				array(
					'dur' => $duration,
					'game' => $id_game,
					'member' => $id_member,
				)
			);

				$save = false;
				$error = 'arcade_scores_limit';

				//echo sprintf($txt['arcade_latest_score'],$score);
				$shout = $txt['arcade_shout_scored'].$score.$txt['arcade_shout_on'].'<a href="'.$game['url']['play'].'">'. $game['name'].'</a>';

			}
		}
	}
	else
	{
		$save = true;
	}

	// Can't save, sorry!
	if (!$save)
	{
		if ($arcSettings['enable_shout_box_scores'] == 1)
		{
			add_to_arcade_shoutbox($shout);
		}

		return array(
			'id_score' => false,
			'error' => $error
		);
	}

	if ($newPosition == 1)
	{
		$champion_from = $end_time;
	}
	else
	{
		$champion_from = 0;
	}


	//Time to insert the score if we are going to.
	$smcFunc['db_insert']('',
		'{db_prefix}arcade_scores',
		array(
			'id_game' => 'int',
			'id_member' => 'int',
			'game_duration' => 'int',
			'member_ip' => 'string-15',
			'comment' => 'string-255',
			'position' => 'int',
			'score' => 'float',
			'start_time' => 'int',
			'end_time' => 'int',
			'champion_from' => 'int',
			'champion_to' => 'int',
			'score_status' => 'int'),
		array($id_game,$id_member,$duration,$ip,'',$newPosition,$score,$start_time,$end_time,$champion_from, 0,$score_status),
		array('id_score')
	);

	$id_score = $smcFunc['db_insert_id']('{db_prefix}arcade_scores', 'id_score');
	$ownbest = false;

	ArcadeUpdatePositions($id_game,$id_score,$newPosition);


// Update personal best and number of plays
	// Get position
	$result = $smcFunc['db_query']('', '
		SELECT score, atbscore
		FROM {db_prefix}arcade_personalbest
		WHERE id_game = {int:game} AND id_member = {int:member}',
			array(
				'game' => $id_game,
				'member' => $id_member,
			)
		);
	$request = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);
	if (!$request)
	{
		// No Personal best? Then we just insert this score
	$smcFunc['db_insert']('',
		'{db_prefix}arcade_personalbest',
		array(
			'id_game' => 'int',
			'id_member' => 'int',
			'score' => 'float',
			'atbscore' => 'float',
			'my_plays' => 'int',
			'playing_time' => 'int',
			'time_gained' => 'int'),
		array($id_game,$id_member,$score,$score,'1',$duration,$end_time),
		array('id_best')
	);

		$shout = $txt['arcade_shout_pb'].'<a href="'.$game['url']['play'].'">'. $game['name'].'</a> '.$txt['arcade_b3pb_2'].' '.$score;

		$ownbest = true;
	}
	else
	{
		// Update score if its better :>
		if ($game['score_type'] == 0 && $request['score'] < $score)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_personalbest
				SET
				score = {float:score},
				my_plays = my_plays+1,
				playing_time = playing_time+{int:dur},
				time_gained = {int:time}
				WHERE id_game = {int:game} AND id_member = {int:member}',
				array(
					'score' => $score,
					'dur' => $duration,
					'time' => $end_time,
					'game' => $id_game,
					'member' => $id_member,
				)
			);

			if ($request['atbscore'] < $score)
			{
				$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_personalbest
				SET
				atbscore = {float:score}
				WHERE id_game = {int:game} AND id_member = {int:member}',
				array(
					'score' => $score,
					'game' => $id_game,
					'member' => $id_member,
				)
				);
			}

			$shout = $txt['arcade_shout_pb'].'<a href="'.$game['url']['play'].'">'. $game['name'].'</a> '.$txt['arcade_b3pb_2'].' '.$score;

			$ownbest = true;
		}
		elseif ($game['score_type']==1 && $request['score'] > $score)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_personalbest
				SET
				score = {float:score},
				my_plays = my_plays+1,
				playing_time = playing_time+{int:dur},
				time_gained = {float:time}
				WHERE id_game = {int:game} AND id_member = {int:member}',
				array(
					'score' => $score,
					'dur' => $duration,
					'time' => $end_time,
					'game' => $id_game,
					'member' => $id_member,
				)
			);

			if ($request['atbscore'] > $score)
			{
				$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_personalbest
				SET
				atbscore = {float:score}
				WHERE id_game = {int:game} AND id_member = {int:member}',
				array(
					'score' => $score,
					'game' => $id_game,
					'member' => $id_member,
				)
				);
			}

			$shout = $txt['arcade_shout_pb'].'<a href="'.$game['url']['play'].'">'. $game['name'].'</a> '.$txt['arcade_b3pb_2'].' '.$score;
			$ownbest = true;
		}
		//if its not better - only update plays and duration
		else
		{
				$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_personalbest
				SET
				my_plays = my_plays+1,
				playing_time = playing_time+{int:dur}
				WHERE id_game = {int:game} AND id_member = {int:member}',
				array(
					'dur' => $duration,
					'game' => $id_game,
					'member' => $id_member,
				)
			);

			$shout = $txt['arcade_shout_scored'].$score.$txt['arcade_shout_on'].'<a href="'.$game['url']['play'].'">'. $game['name'].'</a>';

		}
	}
	//now we shout if were going to..
	if ($arcSettings['enable_shout_box_best'] == 1 && $newPosition > 3)
	{
			add_to_arcade_shoutbox($shout);
	}
// end Update personal best and number of plays

//if first, second or third update shop if installed and call champs/cups update
	if ($newPosition == 1)
	{
		// Arcade & Shop mod - if shop mod installed then update the points
		if (isset($modSettings['shopPointsPerHighscore']) && $modSettings['shopPointsPerHighscore'] > 0)
		{
			$points = $modSettings['shopPointsPerHighscore'];
			// Give the user their points
			$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET money = money + {int:points}
			WHERE id_member = {int:id_member}
			LIMIT 1',
				array(
					'points' => $points,
					'id_member' => $id_member,
				)
			);
		}
		// Update the cups


		//If there was a champ - update their champTo time and send a PM if its turned on
		if ($game['isChampion'])
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_scores
				SET
				champion_to = {int:endtime}
				WHERE id_score = {int:game}',
				array(
					'endtime' => $end_time,
					'game' => $game['champion']['score_id'],
				)
			);

			if (isset($arcSettings['arcadePMsystem'])&& $arcSettings['arcadePMsystem']==1)
			{
				arcadesendPM('new_champion', $game['champion']['member_id'], $game, array('score' => $score,'time' => time()));
			}
		}
		update_champ_cups($id_game);

		$shout = $txt['arcade_champions_cho'].' <a href="'.$game['url']['play'].'">'. $game['name'].'</a> '.$txt['arcade_b3pb_2'].' '.$score;
	}

	if ($newPosition == 2)
	{

		//if shop mod installed then give half points for silver
		if (isset($modSettings['shopPointsPerHighscore']) && $modSettings['shopPointsPerHighscore'] > 0)
		{
			$points = $modSettings['shopPointsPerHighscore'] / 2;
			// Give the user their points
			$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET money = money + {int:points}
			WHERE id_member = {int:id_member}
			LIMIT 1',
				array(
					'points' => $points,
					'id_member' => $id_member,
				)
			);
		}
		// Update the cups
		update_champ_cups($id_game);

		$shout = $txt['arcade_second'].$txt['arcade_shout_on'].'<a href="'.$game['url']['play'].'">'. $game['name'].'</a> with '.$score;

	}

	if ($newPosition == 3)
	{

		//if shop mod installed then give quarter points for bronze
		if (isset($modSettings['shopPointsPerHighscore']) && $modSettings['shopPointsPerHighscore'] > 0)
		{
			$points = $modSettings['shopPointsPerHighscore']/4;
			// Give the user their points
			$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET money = money + {int:points}
			WHERE id_member = {int:id_member}
			LIMIT 1',
				array(
					'points' => $points,
					'id_member' => $id_member,
				)
			);
		}
		// Update the cups
		update_champ_cups($id_game);

		$shout = $txt['arcade_third'].$txt['arcade_shout_on'].'<a href="'.$game['url']['play'].'">'. $game['name'].'</a> with '.$score;
	}
//end if first, second or third update shop if installed and call cups update

	//now we shout if were going to..
	if ($arcSettings['enable_shout_box_champ'] == 1)
	{
			add_to_arcade_shoutbox($shout);
	}

	// Where should we start?
	$scoresPerPage = isset($arcSettings['scoresPerPage']) ? $arcSettings['scoresPerPage'] : 50;
	$start = (floor($newPosition / $scoresPerPage)) * $scoresPerPage;

	// Return array with id_score and later maybe something more
	return array(
		'id_score' => $id_score,
		'position' => $newPosition,
		'new_champion' => $newPosition == 1 ? true : false,
		'start' => $start,
		'ownbest' => $ownbest,
	);
}
function ArcadeUpdatePositions($id_game,$id_score,$newPosition)
{
	global $smcFunc;

			$result = $smcFunc['db_query']('', '
			SELECT id_score
			FROM {db_prefix}arcade_scores
			WHERE id_game = {int:id_game}
			AND position >= {int:newpos}
			AND id_score != {int:ids}
			ORDER BY position ASC',
			array(
				'id_game' => $id_game,
				'newpos' => $newPosition,
				'ids' => $id_score,
				)
			);
		if ($smcFunc['db_num_rows']($result) > 0)
		{
			$new_pos = $newPosition + 1;
			while ($scoreids = $smcFunc['db_fetch_assoc']($result))
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_scores
					SET
					position = {int:pos}
					WHERE id_score = {int:sids}',
						array(
							'pos' => $new_pos,
							'sids' => $scoreids['id_score']
						)
				);

				$new_pos++;
			}
		}
	return;
}

// Returns micro time as a float
function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return (float) $usec + (float) $sec;
}

function checkShopCredits()
{
	// function added for shop mod to check members credit
	global $scripturl, $txt, $id_member, $smcFunc;

	$result = $smcFunc['db_query']('', '
			SELECT money
			FROM {db_prefix}members
			WHERE  id_member = {int:member}',
			array(
				'member' => $id_member,
				)
			);

	$cash = $smcFunc['db_fetch_assoc']($result);

	return $cash['money'];
}

?>