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
Arcade.php
********************************************************************************/
/*	This file handles the Arcade and loads required files.

	void Arcade()
		- Initialzes Arcade
		- Loads langauge files
		- Loads Template
		- Loads Arcade

	void ArcadeLoad([mode = normal])
		- Initializes Arcade

	string PermissionQuery()
		- Returns permission query to use in WHERE
*/

//define( 'DEBUG', TRUE );
if (!defined('SMF'))
	die('Hacking attempt...');

function Arcade()
{
	global $arcSettings, $scripturl, $txt, $context, $sourcedir, $user_info, $modSettings, $db_count;

	//start the load timer
	$ltime = explode(" ",microtime());
	$ltime = $ltime[0] + $ltime[1];
	$context['loadstart'] = $ltime;
	$arcade_db_count = $db_count;

	require_once($sourcedir . '/Subs-Arcade.php');

	loadArcadeSettings();
	ArcadeLastUpdate();

	// Is Arcade enabled?
	if (empty($arcSettings['arcadeEnabled']))
		fatal_lang_error('arcade_disabled');

	// Do we have permission?
	isAllowedTo('arcade_view');

	// Information for actions (file, function, [permission])
	$subActions = array(
		// ArcadeList.php
		'rate' => array('ArcadeList.php', 'ArcadeRate', 'arcade_rate'),
		'list' => array('ArcadeList.php', 'ArcadeList'),
		'frontPage' => array('ArcadeList.php', 'ArcadeFrontPage'),
		'favorite' => array('ArcadeList.php', 'ArcadeFavorite', 'arcade_favorite'),
		'search' => array('ArcadeList.php', 'ArcadeSearchXML'),
		'shout' => array('ArcadeBlocks.php', 'ArcadeShout'),
		// ArcadePlay.php
		'play' => array('ArcadePlay.php', 'ArcadePlay', 'arcade_play'),
		'submit' => array('ArcadePlay.php', 'ArcadeSubmit'),
		'highscore' => array('ArcadePlay.php', 'ArcadeHighscore'),
		'comment' => array('ArcadePlay.php', 'ArcadeComment'),
		// ArcadeStats.php
		'stats' => array('ArcadeStats.php', 'ArcadeStats'),
		// IBP Arcade
		'ibpverify' => array('ArcadePlay.php', 'ArcadeVerifyIBP'),
		'ibpsubmit' => array('ArcadePlay.php', 'ArcadeSubmitIBP'),
		'pro_stats' => array('ArcadeSigsStats_v2.php', 'Arcade_pro_stats'),
		// v3 Arcade
		'v3verify' => array('ArcadePlay.php', 'ArcadeVerifyV3'),
		'tour' => array('ArcadeTour.php', 'ArcadeTour'),
		'tour_games' => array('ArcadeTour.php', 'ArcadeGames'),
	);


	// Fix for broken games which doesn't send sa/do=submit
	if (isset($_POST['game']) && isset($_POST['score']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'submit';

	// Short urls like index.php?game=1 or index.php/game,1.html
	elseif (isset($_REQUEST['game']) && is_numeric($_REQUEST['game']) && !isset($_REQUEST['sa']))
		$_REQUEST['sa'] = 'play';

	$setFrontPage = $arcSettings['gameFrontPage']==0 ? 'list' : 'frontPage';

	if (isset($_REQUEST['sort']) || isset($_REQUEST['category'])||isset($_REQUEST['favorites']))
	{
		$_REQUEST['sa'] = 'list';
	}
	else
	{
		$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : $setFrontPage;
	}

	if (!in_array($_REQUEST['sa'], array('highscore', 'comment')) && isset($_SESSION['arcade']['highscore']))
   		unset($_SESSION['arcade']['highscore']);

   	// Load Arcade
   	ArcadeLoad('normal');

   	// Check permission if needed
  	if (isset($actions[$_REQUEST['sa']][2]))
   		isAllowedTo($subActions[$_REQUEST['sa']][2]);

	require_once($sourcedir . '/' . $subActions[$_REQUEST['sa']][0]);
	$subActions[$_REQUEST['sa']][1]();

	$context['arcade']['queries'] = $db_count - $arcade_db_count;
}



function ArcadeLoad($mode = 'normal', $index = '')
{
	global $smcFunc, $scripturl, $txt, $arcSettings, $modSettings, $context, $settings, $sourcedir, $user_info, $user_profile, $boarddir;

	$context['arcade'] = array();

	loadLanguage('Arcade');

	$user_info['query_see_game'] = PermissionQuery();

	require_once($sourcedir . '/ArcadeStats.php');
	require_once($sourcedir . '/ArcadeBlocks.php');

	// Arcade javascript
	$context['html_headers'] .= '
	<script language="JavaScript" type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/arcade.js"></script>';

	// Normal mode
	if ($mode == 'normal')
	{
		loadTemplate('Arcade');

		// Title
		$context['page_title'] = $txt['arcade'];

		// Add Arcade to link tree
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=arcade',
			'name' => $txt['arcade'],
		);

		// What I can do?
		$context['arcade']['can_play'] = allowedTo('arcade_play');
		$context['arcade']['can_favorite'] = allowedTo('arcade_favorite');
		$context['arcade']['can_rate'] = allowedTo('arcade_rate');
		$context['arcade']['can_submit'] = allowedTo('arcade_submit');
		$context['arcade']['can_comment_own'] = allowedTo('arcade_comment_own');
		$context['arcade']['can_comment_any'] = allowedTo('arcade_comment_any');
		$context['arcade']['can_admin'] = allowedTo('arcade_admin');

		// Or can I (do I have enought posts etc.)
		if (!empty($arcSettings['arcadePostPermission']))
		{
			if (!$user_info['is_guest'])
			{
				loadMemberData($user_info['id'], false, 'minimal');

				//total posts
				$post = $user_profile[$user_info['id']]['posts'];

				//post per number of days
				$from = time() - ($arcSettings['arcadePostsPlayDays']* 86400);

				$result = $smcFunc['db_query']('', '
					SELECT COUNT(*) AS posts
					FROM {db_prefix}messages AS m
					LEFT JOIN {db_prefix}boards AS b ON  (m.id_board = b.id_board)
					WHERE b.count_posts != 1
					AND m.id_member = {int:mem}
					AND m.poster_time >= {int:ptime}',
					array(
					'mem' => $user_info['id'],
					'ptime' => $from,
					)
				);
				$row = $smcFunc['db_fetch_row']($result);
				$smcFunc['db_free_result']($result);
				$postPerDay = $row[0];

			}
			// Guest cannot have posts
			else
			{
				$post = 0;
				$postPerDay = 0;
			}

			if (!empty($arcSettings['arcadePostsPlay']))
				$post2 = $post >= $arcSettings['arcadePostsPlay'];
			else
				$post2 = true;

			if (!empty($arcSettings['arcadePostsPlayPerDay']))
				$postPerDay2 = $postPerDay >= $arcSettings['arcadePostsPlayPerDay'];
			else
				$postPerDay2 = true;

			if ($user_info['is_guest'])
			{
				if (!empty($arcSettings['arcadePostsPlay']) && !empty($arcSettings['arcadePostsPlayPerDay']))
					$context['arcade']['notice'] = sprintf($txt['arcade_notice_post_both'], $arcSettings['arcadePostsPlay'], $arcSettings['arcadePostsPlayPerDay']);

				elseif (!empty($arcSettings['arcadePostsPlay']))
					$context['arcade']['notice'] = sprintf($txt['arcade_notice_post_cumulative'], $arcSettings['arcadePostsPlay']);

				elseif (!empty($arcSettings['arcadePostsPlayPerDay']))
					$context['arcade']['notice'] = sprintf($txt['arcade_notice_post_perday'], $arcSettings['arcadePostsPlayPerDay'],$arcSettings['arcadePostsPlayDays']);
			}
			else
			{
				$context['arcade']['can_play'] = $post2 && $postPerDay2;

				// Should we display notice, and what kind of notice?
				if (!$post2 || !$postPerDay2)
				{
					$p1 = !$post2 ?	'<span style="color: red">' . $arcSettings['arcadePostsPlay'] . '</span>' : $arcSettings['arcadePostsPlay'];
					$p2 = !$postPerDay2 ? '<span style="color: red">' . $arcSettings['arcadePostsPlayPerDay'] . '</span>' :  $arcSettings['arcadePostsPlayPerDay'];

					if (!empty($arcSettings['arcadePostsPlay']) && !empty($arcSettings['arcadePostsPlayPerDay']))
						$context['arcade']['notice'] = sprintf($txt['arcade_notice_post_both'], $p1, $p2);

					elseif (!empty($arcSettings['arcadePostsPlay']))
						$context['arcade']['notice'] = sprintf($txt['arcade_notice_post_cumulative'], $p1);

					elseif (!empty($arcSettings['arcadePostsPlayPerDay']))
						$context['arcade']['notice'] = sprintf($txt['arcade_notice_post_perday'], $p2,$arcSettings['arcadePostsPlayDays']);
				}
			}
		}

/*
	//Added for shop mod
	if (isset($modSettings['shopEnableArcadePass']) && $modSettings['shopEnableArcadePass'] > 0)
	{
		$context['arcade']['can_play'] = hasArcadePass();
		if ($context['arcade']['can_play']==0)
		{
			$context['arcade']['notice'] = $txt['arcade_pass_required'];
		}
	}
	//end shop mod
*/
		if (!isset($_REQUEST['xml']))
		{
			$context['template_layers'][] = 'Arcade';
		}
	}

	// Admin mode
	elseif ($mode == 'admin')
	{
		loadTemplate('ArcadeAdmin');
		loadLanguage('ArcadeAdmin');

		if (file_exists($boarddir . '/manual_install.php') && @!unlink($boarddir . '/manual_install.php'))
			fatal_lang_error('arcade_arcade_installer_not_removed');

		if (!empty($index))
			adminIndex($index);

		$context['template_layers'][] = 'ArcadeAdmin';
		$context['page_title'] = $txt['arcade_admin_title'];
	}
}

function PermissionQuery()
{
	global $scripturl, $txt, $arcSettings, $context, $settings, $sourcedir, $user_info;

	// Build permission query
	if (!isset($arcSettings['arcadePermissionMode']))
		$arcSettings['arcadePermissionMode'] = 1;

	if ($arcSettings['arcadePermissionMode'] >= 2)
	{
		// Can see game?
		if ($user_info['is_guest'])
			$see_game = 'FIND_IN_SET(-1, g.member_groups)';

		// Administrators can see all games.
		elseif ($user_info['is_admin'])
			$see_game = '1';
		// Registered user.... just the groups in $user_info['groups'].
		else
			$see_game = '(FIND_IN_SET(' . implode(', g.member_groups) OR FIND_IN_SET(', $user_info['groups']) . ', g.member_groups))';
	}

	if ($arcSettings['arcadePermissionMode'] == 1 || $arcSettings['arcadePermissionMode'] >= 3)
	{
		// Can see category?
		if ($user_info['is_guest'])
			$see_category = 'FIND_IN_SET(-1, c.member_groups)';

		// Administrators can see all games.
		elseif ($user_info['is_admin'])
			$see_category = '1';
		// Registered user.... just the groups in $user_info['groups'].
		else
			$see_category = '(FIND_IN_SET(' . implode(', c.member_groups) OR FIND_IN_SET(', $user_info['groups']) . ', c.member_groups))';
	}

	// Build final query
	if ($arcSettings['arcadePermissionMode'] == 0) // No game/category permissions used
		return '1';

	elseif ($arcSettings['arcadePermissionMode'] == 1) // Only category used
		return $see_category;

	elseif ($arcSettings['arcadePermissionMode'] == 2) // Only category used
		return $see_game;

	elseif ($arcSettings['arcadePermissionMode'] == 3) // Required to have permssion to game and category
		return "($see_category AND $see_game)";

	elseif ($arcSettings['arcadePermissionMode'] == 4) // Required to have permssion to game OR category
		return "($see_category OR $see_game)";

	else // Default
		return $see_category;
}

?>