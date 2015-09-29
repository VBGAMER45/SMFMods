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
ManageArcade.php
*****************************************************************************/
/*This file handles Arcade Admin and loads required files.

*/

if (!defined('SMF'))
	die('Hacking attempt...');


function ArcadeSettings()
{
	global $scripturl, $txt, $context, $sourcedir, $modSettings;

	require_once($sourcedir . '/Subs-Arcade.php');

	loadLanguage('ArcadeAdmin');
	loadArcadeSettings();
	loadTemplate('ManageArcade');

	$subActions = array(
		'show' => array('ArcadeSettingsShow', 'arcade_admin'),
		'edit' => array('ArcadeSettingsEdit', 'arcade_admin'),
		'save' => array('ArcadeSettingsSave', 'arcade_admin'),
		'editcats' => array('ArcadeCategoryEdit', 'arcade_admin'),
		'savecats' => array('ArcadeCategorySave', 'arcade_admin'),
		'quick' => array('GamesQuickEdit', 'arcade_admin'),
		'listgames' => array('GamesAdminList', 'arcade_admin'),
		'files' => array('GamesAdminFiles', 'arcade_admin'),
		'autofiles' => array('GamesAutoFiles', 'arcade_admin'),
		'delete' => array('GamesDelete', 'arcade_admin'),
		'upload' => array('GamesUpload', 'arcade_admin'),
		'clear' => array('GamesCacheClear', 'arcade_admin'),
		'highscore' => array('HighscoreEditor', 'arcade_admin'),
		'highscore2' => array('HighscoreEditor2', 'arcade_admin'),
		'settopics' => array('GamesTopicUpdater', 'arcade_admin'),
		'arc_maintenance' => array('Arcade_Maintenance', 'arcade_admin'),
		'fix' => array('Arcade_Fix_Scores', 'arcade_admin'),
		'delshout' => array('arcade_del_shouts', 'arcade_admin'),
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'show';
	isAllowedTo($subActions[$_REQUEST['sa']][1]);


	// Tabs for browsing the different functions.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['arcade_admin'],
		'help' => '',
		'description' => $txt['arcade_description1'],
		'tabs' => array(
			'show' => array(
				'description' => $txt['arcade_description1'],
				'href' => $scripturl . '?action=admin;area=managearcade;sa=show',

			),
			'edit' => array(
				'description' => $txt['arcade_description2'],
				'href' => $scripturl . '?action=admin;area=managearcade;sa=edit',

			),
			'editcats' => array(
				'description' => $txt['arcade_description3'],
				'href' => $scripturl . '?action=admin;area=managearcade;sa=editcats',

			),
			'listgames' => array(
				'description' => $txt['arcade_description4'],
				'href' => $scripturl . '?action=admin;area=managearcade;sa=listgames',

			),
			'files' => array(
				'description' => $txt['arcade_description5'],
				'href' => $scripturl . '?action=admin;area=managearcade;sa=files',

			),
			'autofiles' => array(
				'description' => $txt['arcade_description6'],
				'href' => $scripturl . '?action=admin;area=managearcade;sa=autofiles',

			),
			'arc_maintenance' => array(
				'description' => $txt['arcade_description7'],
				'href' => $scripturl . '?action=admin;area=managearcade;sa=arc_maintenance',

			),
		),
	);

	$subActions[$_REQUEST['sa']][0]();
}

function Arcade_Fix_Scores()
{
	global  $smcFunc;

	$result = $smcFunc['db_query']('', '
		SELECT id_game, score_type
		FROM {db_prefix}arcade_games',
		array(
		)
	);
	while ($game = $smcFunc['db_fetch_assoc']($result))
	{
		ArcadeFixPositions($game['id_game'], $game['score_type']);
	}

	redirectexit('action=admin;area=managearcade;sa=arc_maintenance');

}

function Arcade_Maintenance()
{
	global $context, $txt, $smcFunc;

	$context['page_title'] = $txt['arcade_admin'];
	$context['sub_template'] = 'arcadeadmin_maintenance';
}

function ArcadeSettingsShow()
{
	global $context, $txt;
	$context['page_title'] = $txt['arcade_admin'];
	$context['sub_template'] = 'arcadeadmin_info';
}

function ArcadeSettingsEdit()
{
	global $context, $txt, $smcFunc;

		//We need the forum boards
		$result = $smcFunc['db_query']('', '
			SELECT id_board, name
			FROM {db_prefix}boards',
			array(
			)
		);

		if (!$result)
			fatal_lang_error('arcade_general_query_error');

		while ($boards = $smcFunc['db_fetch_assoc']($result))
		{
			$b[]=$boards;
		}

	$context['arcade_boards']=$b;
	$context['page_title'] = $txt['arcade_settings'];
	$context['sub_template'] = 'arcadeadmin_settings';
}

function ArcadeSettingsSave()
{
	// Validate session
	checkSession('post');

// This is array for updateSettings
	$settings = array(
		'arcadeEnabled' => isset($_REQUEST['enabled']) ? true : false,
		'gamesPerPage' => (int) $_REQUEST['gamesPerPage'],
		'gamesPerPageAdmin' => (int) $_REQUEST['gamesPerPageAdmin'],
		'scoresPerPage' => (int) $_REQUEST['scoresPerPage'],
		'arcadeCheckLevel' => (int) $_REQUEST['arcadeCheckLevel'],
		'gamesDirectory' => $_REQUEST['gamesDirectory'],
		'cacheDirectory' => $_REQUEST['cacheDirectory'],
		'gamesUrl' => $_REQUEST['gamesUrl'],
		'gameFrontPage' => (int) $_REQUEST['gameFrontPage'],
		'arcadeNewsFader' => (int) $_REQUEST['arcadeNewsFader'],
		'arcadeNewsNumber' => (int) $_REQUEST['arcadeNewsNumber'],
		'arcadePermissionMode' => (int) $_REQUEST['arcadePermissionMode'],
		'arcadeMaxScores' => (int) $_REQUEST['arcadeMaxScores'],
		'arcadePostPermission' => isset($_REQUEST['arcadePostPermission']) ? true : false,
		'arcadePostsPlay' => (int) $_REQUEST['arcadePostsPlay'],
		'arcadePostsPlayPerDay' => (int) $_REQUEST['arcadePostsPlayPerDay'],
		'arcadePostsPlayDays' => (int) $_REQUEST['arcadePostsPlayDays'],
		'arcade_champions_in_post' => (int) $_REQUEST['arcade_champions_in_post'],
		'arcade_champion_sig' => (int) $_REQUEST['arcade_champion_sig'],
		'arcade_champion_pp' => (int) $_REQUEST['arcade_champion_pp'],
		'arcadePMsystem' => isset($_REQUEST['arcadePMsystem'])? true : false,
		'arcadePostTopic' => (int) $_REQUEST['arcadePostTopic'],
		'enable_post_comment' => isset($_REQUEST['enable_post_comment'])? true : false,
		'enable_shout_box_comment' => isset($_REQUEST['enable_shout_box_comment'])? true : false,
		'enable_shout_box_members' => isset($_REQUEST['enable_shout_box_members'])? true : false,
		'enable_shout_box_scores' => isset($_REQUEST['enable_shout_box_scores'])? true : false,
		'enable_shout_box_best' => isset($_REQUEST['enable_shout_box_best'])? true : false,
		'enable_shout_box_champ' => isset($_REQUEST['enable_shout_box_champ'])? true : false,
		'arcade_show_shouts' => (int) $_REQUEST['arcade_show_shouts'],
		'arcade_active_user' => isset($_REQUEST['arcade_active_user'])? true : false,
		'enable_arcade_cache' => isset($_REQUEST['enable_arcade_cache'])? true : false,

	);

	saveArcadeSettings($settings);

	redirectexit('action=admin;area=managearcade;sa=edit');
}

function ArcadeCategoryEdit()
{
	global $context, $txt;

	preparemember_groups();

	$context['arcade']['category'] = prepareCategories();
	$context['page_title'] = $txt['arcade_categories'];
	$context['sub_template'] = 'arcadeadmin_category';

}

function ArcadeCategorySave()
{
	global $smcFunc, $arcSettings, $context, $sourcedir;

	checkSession('post', '',true);

	$categories = prepareCategories();

	// Create new categories
	if (isset($_REQUEST['new']) && is_array($_REQUEST['new']))
	{
		foreach ($_REQUEST['new'] as $new)
		{
			$new = trim($new);

			if (!empty($new))
			{
				$smcFunc['db_insert']('',
					'{db_prefix}arcade_categories',
						array(
						'category_name' => 'string-20',
						'category_icon' => 'string-20',
						'category_order' => 'int',
						'special' => 'int',
						'member_groups' => 'string-255'),
						array($new,'',100,0,'-1,0,2'),
						array('id_tour')
				);
			}
		}
	}

	// Handle modifying old ones
	if (isset($_REQUEST['category']) && is_array($_REQUEST['category']))
	{
		foreach ($_REQUEST['category'] as $id => $category)
		{
			// Handle name changes
			$name = trim($category['name']);
			$icon = trim($category['icon']);
			$order = trim($category['order']);

			if ($category['name'] == '' && !isset($category['delete']))
			continue;

			if (isset($category['delete']) && $categories[$id]['canRemove'])
			{
				$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}arcade_categories
				WHERE id_category = {int:idc}',
				array(
				'idc' => $id,
				)
				);

				$smcFunc['db_query']('', '
				UPDATE {db_prefix}arcade_games
				SET
				id_category = {int:default}
				WHERE id_category = {int:idc}',
				array(
				'default' => $arcSettings['arcadeDefaultCategory'],
				'idc' => $id,
				)
				);
				continue;

			}
			elseif (isset($category['delete']))
			{
				fatal_lang_error('arcade_unable_to_remove', false, array($name));
			}

			$groups = implode(',', $category['member_groups']);
			$special = '';

			if (isset($category['default']) && !$categories[$id]['default'])
			{
				$special = ', special = 1';
				$change['arcadeDefaultCategory'] = $id;
				saveArcadeSettings($change);

				// To make sure thre won't be double defaults
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_categories
					SET special = 0
					WHERE special = 1',
					array(
					)
				);

			}

			$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_categories
			SET
			category_name = {string:cn},
			category_icon = {string:ci},
			category_order = {int:co},
			member_groups = {string:mg}'.$special.'
			WHERE id_category = {int:idc}',
			array(
			'cn' => $name,
			'ci' => $icon,
			'co' => $order,
			'mg' => $groups,
			'idc' => $id,
			)
			);
		}
	}
	if(file_exists($arcSettings['cacheDirectory'].'cats.cache')){unlink($arcSettings['cacheDirectory'].'cats.cache');}
	redirectexit('action=admin;area=managearcade;sa=editcats');
}

function arcade_del_shouts()
{
	global $smcFunc;

	$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_shouts',
			array(
			)
		);

		redirectexit('action=admin;area=managearcade;sa=arc_maintenance');
}

function GamesDelete($game = 0)
{
	global $smcFunc;

	$game = $game == 0 ? (int)$_REQUEST['game'] : $game;

	//delete the game
	$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_games
			WHERE id_game = {int:idg}',
			array(
			'idg' => $game,
			)
		);

	//delete all its scores
	$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_scores
			WHERE id_game = {int:idg}',
			array(
			'idg' => $game,
			)
		);

	//delete personal bests
	$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_personalbest
			WHERE id_game = {int:idg}',
			array(
			'idg' => $game,
			)
		);

	//delete its ratings
	$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_rates
			WHERE id_game = {int:idg}',
			array(
			'idg' => $game,
			)
		);

	//delete from favs
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_favorite
		WHERE id_game = {int:idg}',
		array(
		'idg' => $game,
		)
	);

	//delete from tour scores
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_tournament_scores
		WHERE id_game = {int:idg}',
		array(
		'idg' => $game,
		)
	);

	//del from tour rounds
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_tournament_rounds
		WHERE id_game = {int:idg}',
		array(
		'idg' => $game,
		)
	);

	if(isset($_REQUEST['game']))
	{
		//recount the games
		ArcadeCountGames();
		redirectexit('action=admin;area=managearcade;sa=listgames');
	}

}

function ScoresDelete($game = 0, $del_pb = 0)
{
	global $smcFunc;

	//delete the game
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}arcade_scores
		WHERE id_game = {int:ids}',
		array(
		'ids' => $game,
		)
	);

	if($del_pb == 0)
	{
		//reset personal best
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_personalbest
			SET
			score = {int:score},
			time_gained = {int:time}
			WHERE id_game = {int:game}',
			array(
			'score' => 0,
			'time' => 0,
			'game' => $game,
			)
		);
	}
	else
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}arcade_personalbest
			WHERE id_game = {int:idg}',
			array(
			'idg' => $game,
			)
		);
	}

}

function GamesQuickEdit()
{
	global $scripturl, $txt, $smcFunc, $context, $arcSettings ;

	checkSession('post');

	switch ($_REQUEST['qaction'])
	{

		case 'change':
		{
			$games = implode(',', $_POST['games']);
			$category = (int) $_POST['qcategory'];
			if($_REQUEST['qset'] == 0)
			{
				$games = implode(',', $_POST['games']);
				$category = (int) $_POST['qcategory'];

				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_games
					SET
					id_category = {int:idc}
					WHERE id_game IN('.$games.')',
					array(
					'idc' => $category,
					)
				);
			}
			else
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}arcade_games
					SET
					id_category = {int:idc}',
					array(
					'idc' => $category,
					)
				);
			}
		}
		break;

		case 'gotd':
		{
			if ($_REQUEST['qset'] == 0)
			{
				foreach ($_POST['games'] as $key => $value)
				{
					$value = (int) $value;
					$updates = array(
					'arcadegotd' => $value,
					);
				}
				saveArcadeSettings($updates);
				GamesCacheClear();
			}
		}
		break;

		case 'clear_scores':
		{
			if ($_REQUEST['qset'] == 0)
			{
				foreach ($_POST['games'] as $key => $game)
				{
					ScoresDelete($game, 0);
					ArcadeFixPositions($game);
				}
			}
			else
			{
				$result = $smcFunc['db_query']('', '
					SELECT id_game
					FROM {db_prefix}arcade_games',
					array(
					)
				);
				while ($game = $smcFunc['db_fetch_assoc']($result))
				{
					ScoresDelete($game['id_game'], 0);
					update_champ_cups($game['id_game']);
				}
			}
		}
		break;

		case 'clear_scores2':
		{
			if ($_REQUEST['qset'] == 0)
			{
				foreach ($_POST['games'] as $key => $game)
				{
					ScoresDelete($game, 1);
					ArcadeFixPositions($game);
				}
			}
			else
			{
				$result = $smcFunc['db_query']('', '
					SELECT id_game
					FROM {db_prefix}arcade_games',
					array(
					)
				);
				while ($game = $smcFunc['db_fetch_assoc']($result))
				{
					ScoresDelete($game['id_game'], 1);
					update_champ_cups($game['id_game']);
				}
			}
		}
		break;

		case 'del_games':
		{
			if ($_REQUEST['qset'] == 0)
			{
				foreach ($_REQUEST['games'] as $key => $game)
				{
					GamesDelete($game);
					ArcadeCountGames();
				}
			}
			else
			{
				$result = $smcFunc['db_query']('', '
					SELECT id_game
					FROM {db_prefix}arcade_games',
					array(
					)
				);
				while ($game = $smcFunc['db_fetch_row']($result))
				{
					GamesDelete($game[0]);
					ArcadeCountGames();
				}
			}
		}
		break;

		case 'fix_scores':
		{
			if ($_REQUEST['qset'] == 0)
			{
				foreach ($_REQUEST['games'] as $key => $game)
				{
					$result = $smcFunc['db_query']('', '
						SELECT id_game, score_type
						FROM {db_prefix}arcade_games
						WHERE id_game = {int:idg}',
						array(
						'idg' => $game,
						)
					);
					while ($game = $smcFunc['db_fetch_assoc']($result))
					{
						ArcadeFixPositions($game['id_game'], $game['score_type']);
					}
				}
			}
			else
			{
				$result = $smcFunc['db_query']('', '
					SELECT id_game, score_type
					FROM {db_prefix}arcade_games',
					array(
					)
				);
				while ($game = $smcFunc['db_fetch_assoc']($result))
				{
					ArcadeFixPositions($game['id_game'], $game['score_type']);
				}
			}
		}
		break;

		default:
		{
			redirectexit('action=admin;area=managearcade;sa=listgames');
		}
		break;
	}

	redirectexit('action=admin;area=managearcade;sa=listgames');
}

function prepareEditor($game)
{
	global $scripturl, $txt, $context;

	$context['arcade']['category'] = prepareCategories();
	$category = array();
	foreach ($context['arcade']['category'] as $cat)
		$category[] = array(
			'value' => $cat['id'],
			'name' => $cat['name'],
		);

	preparemember_groups(isset($_POST['data']['member_groups']) ? $_POST['data']['member_groups'] : explode(',', $game['member_groups']));

	$_POST['data']['enabled'] = isset($_POST['data']['enabled']) ? true : isset($_POST['data']['internal_name']) ? false : $game['enabled'];

	$context['arcade']['config_array'] = array(
		$txt['arcade_basic_settings'],
			array(
				'name' => 'internal_name',
				'type' => 'text',
				'help' => '',
				'label' => $txt['arcade_internal_name'],
				'value' => isset($_POST['data']['internal_name']) ? $_POST['data']['internal_name'] : $game['internal_name'],
				'disabled' => false,
				'size' => 30,
			),
			array(
				'name' => 'game_name',
				'type' => 'text',
				'help' => '',
				'label' => $txt['arcade_game_name'],
				'value' => isset($_POST['data']['game_name']) ? $_POST['data']['game_name'] : $game['name'],
				'disabled' => false,
				'size' => 30,
			),
			array(
				'name' => 'game_file',
				'type' => 'text',
				'help' => '',
				'label' => $txt['arcade_file_name'],
				'value' => isset($_POST['data']['game_file']) ? $_POST['data']['game_file'] : $game['file'],
				'disabled' => false,
				'size' => 30,
			),
			array(
				'name' => 'game_directory',
				'type' => 'text',
				'help' => '',
				'label' => $txt['arcade_directory'],
				'value' => isset($_POST['data']['game_directory']) ? $_POST['data']['game_directory'] : $game['directory'],
				'disabled' => false,
				'size' => 30,
			),
			array(
				'name' => 'score_type',
				'type' => 'select',
				'help' => '',
				'label' => $txt['arcade_score_type'],
				'value' => isset($_POST['data']['score_type']) ? $_POST['data']['score_type'] : $game['score_type'],
				'data' => array(
					array('value' => 0, 'name' => $txt['arcade_score_normal']),
					array('value' => 1, 'name' => $txt['arcade_score_reverse']),
					array('value' => 2, 'name' => $txt['arcade_score_none']),
				),
				'disabled' => false,
				'size' => 30,
			),
			array(
				'name' => 'id_category',
				'type' => 'select',
				'help' => '',
				'label' => $txt['arcade_category'],
				'value' => isset($_POST['data']['id_category']) ? $_POST['data']['id_category'] : $game['category']['id'],
				'data' => &$category,
				'disabled' => false,
			),
			array(
				'name' => 'enabled',
				'type' => 'checkbox',
				'help' => '',
				'label' => $txt['arcade_game_enabled'],
				'checked' => isset($_POST['data']['enabled']) ? $_POST['data']['enabled'] : $game['enabled'],
				'disabled' => false,
				'size' => 30,
			),
			// Permission (this is special one)
			array(
				'name' => 'member_groups',
				'label' => $txt['arcade_membergroups'],
				'type' => 'permission',
				'help' => 'arcade_member_groups_help',
				'disabled' => false,

			),
		$txt['arcade_thumbnails'],
			array(
				'name' => 'thumbnail',
				'type' => 'text',
				'help' => '',
				'label' => $txt['arcade_thumbnail'],
				'value' => isset($_POST['data']['thumbnail']) ? $_POST['data']['thumbnail'] : $game['thumbnail'],
				'disabled' => false,
				'size' => 30,
			),
		$txt['arcade_description_help'],
			array(
				'name' => 'description',
				'type' => 'large_text',
				'help' => '',
				'label' => $txt['arcade_description'],
				'value' => isset($_POST['data']['description']) ? stripcslashes($_POST['data']['description']) : $game['description'],
				'disabled' => false,
				'rows' => 3,
				'cols' => 30,
			),
			array(
				'name' => 'help',
				'type' => 'large_text',
				'help' => '',
				'label' => $txt['arcade_help'],
				'value' =>  isset($_POST['data']['help']) ? $_POST['data']['help'] : $game['help'],
				'disabled' => false,
				'rows' => 3,
				'cols' => 30,
			),
		$txt['arcade_flash_only'],
			array(
				'name' => 'game_width',
				'type' => 'text',
				'help' => '',
				'label' => $txt['arcade_width'],
				'value' => isset($_POST['data']['game_width']) ? $_POST['data']['game_width'] : $game['flash']['width'],
				'disabled' => false,
				'size' => 30,
			),
			array(
				'name' => 'game_height',
				'type' => 'text',
				'help' => '',
				'label' => $txt['arcade_height'],
				'value' => isset($_POST['data']['game_height']) ? $_POST['data']['game_height'] : $game['flash']['height'],
				'disabled' => false,
				'size' => 30,
			),
			array(
				'name' => 'game_bg_colour',
				'type' => 'text',
				'help' => '',
				'label' => $txt['arcade_bgcolor'],
				'value' => isset($_POST['data']['game_bg_colour']) ? $_POST['data']['game_bg_colour'] : $game['flash']['backgroundColor'],
				'disabled' => false,
				'size' => 30,
			),
	);
}

function GamesAdminEditor()
{
	global $scripturl, $txt, $context;

	if (!isset($_REQUEST['game']))
		fatal_error('arcade_no_game');

	$game = ArcadeGameInfo((int) $_REQUEST['game'], null, true);
	if ($game === false)
		fatal_error('arcade_no_game');

	$context['arcade']['config_errors'] = array();
	prepareEditor($game);

	// Title for page
	$context['page_title'] = $txt['arcade_title_manage_games'] . ' - ' . $game['name'];


	$context['sub_template'] = 'arcadeadmin_editor';

	// Game data
	$context['arcade']['game'] = &$game;
}

function GamesAdminEditor2()
{
	global $scripturl, $txt, $context;

	if (!isset($_POST['data']['enabled']))
		$_POST['data']['enabled'] = 0;

	if (!isset($_REQUEST['game']))
		fatal_error('arcade_no_game');

	$game = ArcadeGameInfo((int) $_REQUEST['game'], null, true);
	if ($game === false)
		fatal_error('arcade_no_game');

	$errors = UpdateGame((int) $_REQUEST['game'], $_POST['data']);

	// There were no errors no need to display this again
	if (count($errors) === 0)
	{
		if (!isset($_SESSION['arcade_page']))
			redirectexit('action=admin;area=managearcade;sa=listgames');
		else
			redirectexit('action=admin;area=managearcade;sa=listgames;start=' . $_SESSION['arcade_page']);
	}

	$context['arcade']['config_errors'] = &$errors;
	prepareEditor($game);

	// Title for page
	$context['page_title'] = $txt['arcade_title_manage_games'] . ' - ' . $game['name'];
	// Template layers for editor
	//$context['template_layers'][] = 'editor';
	$context['sub_template'] = 'arcadeadmin_editor';
	// Game data
	$context['arcade']['game'] = &$game;
}

function GamesAdminList()
{
	global $smcFunc,$scripturl, $txt, $arcSettings, $context, $sourcedir;

	if(isset($_REQUEST['do'])&& $_REQUEST['do']=='edit')
	{
		GamesAdminEditor();
	}
	elseif(isset($_REQUEST['do'])&& $_REQUEST['do']=='gamesave')
	{
		GamesAdminEditor2();
	}
	else
	{
		$where = '1';
		$search = false;
		$context['arcade']['games'] = array();
		$gamesPerPage = !empty($arcSettings['gamesPerPageAdmin']) ? $arcSettings['gamesPerPageAdmin'] : 25;

		//How many games we showing...
		$gameCount = $arcSettings['arcade_total_games'];

		if (isset($_REQUEST['category']))
		{
				$result = $smcFunc['db_query']('', '
					SELECT count(*) as gc
					FROM {db_prefix}arcade_games
					WHERE id_category = {int:catid}',
						array(
						'catid' => $_REQUEST['category'],
						)
				);
				$row = $smcFunc['db_fetch_row']($result);
				$smcFunc['db_free_result']($result);
				$gameCount = $row[0];
				$search = true;
				$where = " g.id_category = ".(int) $_REQUEST['category'];
		}


		//setup the url..
		$parts = array(
			$scripturl . '?action=admin;area=managearcade;sa=listgames'
		);

		if ($search)
		{
			if (isset($category))
				$parts[] = 'category=' . $category;

		}

		//the page index..
		$context['arcade']['pageIndex'] = constructPageIndex( implode(';', $parts) , $_REQUEST['start'], $gameCount , $gamesPerPage, false );

		$_SESSION['arcade_page'] = $_REQUEST['start'];

		//the games per page
		$result = $smcFunc['db_query']('', '
			SELECT g.id_game, g.game_name, g.description, g.game_rating, g.number_plays, g.game_file,
				g.game_directory, g.internal_name, g.game_width, g.game_height,
				g.game_bg_colour AS bgcolor, g.score_type, g.thumbnail,
			 	g.help, g.enabled, c.id_category, c.category_name
			FROM {db_prefix}arcade_games AS g
			LEFT JOIN {db_prefix}arcade_categories AS c ON (c.id_category = g.id_category)
			WHERE '.$where.'
			ORDER BY g.game_name
			LIMIT {int:limit1}, {int:limit2}',
				array(
				'limit1' => $_REQUEST['start'],
				'limit2' => $gamesPerPage,
				)
		);
		while ($game = $smcFunc['db_fetch_assoc']($result))
		{
			$context['arcade']['games'][] = BuildGameArrayAdmin($game);
		}
		$smcFunc['db_free_result']($result);

		// Title for page
		$context['page_title'] = $txt['arcade_title_manage_games'];

		// Subtemplate
		$context['sub_template'] = 'games_list';
	}
}

function GamesAdminFiles()
{
	global $scripturl, $txt, $smcFunc, $arcSettings, $context, $sourcedir;

	isset($_REQUEST['do']) ? $go = $_REQUEST['do'] : $go = '';

	switch ($go)
	{
		case 'stageone':
			GamesAdminInstall();
		break;

		case 'stagetwo':
			GamesAdminInstall2();
		break;

		default:

	if (!isset($_REQUEST['directory'])) // In main directory
	{
		$directory = $arcSettings['gamesDirectory'];
		$base_dir = '';
	}
	else
	{
		$directory = $arcSettings['gamesDirectory'] . $_REQUEST['directory'] . '/';
		$base_dir = $_REQUEST['directory'] . '/';
	}

	$context['arcade']['files'] = array();
	$context['arcade']['directories'] = array();
	$installed = array();

	$result = $smcFunc['db_query']('', '
		SELECT game_file
		FROM {db_prefix}arcade_games',
			array(
			)
	);
	while ($file = $smcFunc['db_fetch_assoc']($result))
	{
		$installed[$file['game_file']] = true;
	}
	$smcFunc['db_free_result']($result);

	if ($handle = opendir($directory))
	{
		while (($file = readdir($handle)) !== false)
		{
			if ($file == '.' || $file == '..' || isset($installed[$file]))
				continue;

			if (is_dir($directory . $file))
			{
				$context['arcade']['directories'][] = array(
					'name' => $file,
					'writable' => is_writable($directory . $file),
					'path' => $directory . $file,
					'url' => array(
						'view' => $scripturl . '?action=admin;area=managearcade;sa=files;directory=' . $base_dir . $file,
					),
				);
			}
			else
			{
				$dot = strrpos($file, '.');

				if ($dot !== false)
					$internal_name = substr($file, 0, $dot);
				else
					continue;

				if (substr($file, -3) == 'swf')
					$type = 'flash';

				elseif (substr($file, -3) == 'tar' || substr($file, -6) == 'tar.gz' || substr($file, -3) == 'zip')
					$type = 'compress';

				else
					continue;

				$file_url = !empty($base_dir) ? $base_dir . $file : $file;
				$context['arcade']['files'][] = array(
					'name' => ucwords(strtr($internal_name, array('_' => ' '))),
					'file' => $file,
					'writable' => is_writable($directory . $file),
					'path' => $base_dir . $file,
					'type' => $type,
					'url' => array(
						'install' => $scripturl . '?action=admin;area=managearcade;sa=files;do=stageone;file=' . $file_url,
					),
				);
			}
		}
	}

	// Title for page
	$context['page_title'] = $txt['arcade_title_install_games'];
	// Subtemplate
	$context['sub_template'] = 'files_list';
		break;
	}


}

function GamesAdminInstall()
{
	global $scripturl, $txt, $smcFunc, $arcSettings, $context, $sourcedir, $boarddir;

	// Make list of files to be installed
	if (isset($_REQUEST['file']) || isset($_REQUEST['directory']))
	{
		// We don't want to install games more than once
		$installed = array();

		$result = $smcFunc['db_query']('', '
			SELECT game_file
			FROM {db_prefix}arcade_games',
				array(
				)
		);
		while ($file = $smcFunc['db_fetch_assoc']($result))
		{
			$installed[$file['game_file']] = true;
		}
		$smcFunc['db_free_result']($result);

		// We need write permission to games directory
		if (!is_writable($arcSettings['gamesDirectory']))
		fatal_lang_error('arcade_not_writable', false, array($arcSettings['gamesDirectory']));

		// We will need this to extract files
		require_once($sourcedir . '/Subs-Package.php');

		// One file
		if (isset($_REQUEST['file']) && !is_array($_REQUEST['file']))
		$files = array($_REQUEST['file']);
		// Many files
		elseif (isset($_REQUEST['file']) && is_array($_REQUEST['file']))
		$files = &$_REQUEST['file'];
		// No files at all
		else
		$files = array();

		// Remove invalid games
		foreach ($files as $key => $value)
		if (!file_exists($arcSettings['gamesDirectory'] . '/' . $value))
		unset($files[$key]);

		// Files from directories
		if (isset($_REQUEST['directory']) && is_array($_REQUEST['directory']))
		{
			chdir($arcSettings['gamesDirectory']);

			foreach ($_REQUEST['directory'] as $directory)
			$files = array_merge($files, glob($directory . '/*.swf'));

		}

		if (count($files) == 0)
		fatal_lang_error('arcade_no_files_selected');

		$games = array();

		// Let's make array with basic settings
		foreach ($files as $file)
		{
			$path = $file;
			$file = basename($file);
			$dot = strrpos($file, '.');

			if ($dot !== false)
			$internal_name = substr($file, 0, $dot);
			else
			fatal_lang_error('arcade_invalid_file', false, array($path));

			if (substr($file, -3) == 'swf')
			{

				// Is it already installed?
				if (isset($installed[$file]))
				continue;

				$name = str_replace('_', ' ', $internal_name);
				$games[$internal_name] = array(
				'internal_name' => $internal_name,
				'name' => ucwords($name),
				'path' => $path,
				'file' => $file,
				'type' => substr($file, -3),
				);

			}
			elseif (substr($file, -3) == 'zip')
			{

				$data = file_get_contents($arcSettings['gamesDirectory'] . $path);

				if ($data === false)
				continue; // Unable to read file :(

				$destination = $arcSettings['gamesDirectory']. $internal_name . '/';
				$ret = read_zip_data($data, $destination);
				unset($data);
				unlink($arcSettings['gamesDirectory'].$file);

				$folder = $internal_name;

				if ($ret == false)
				continue;

				// Files that were extracted
				foreach ($ret as $file_pack)
				{
					$basename = basename($file_pack['filename']);

					// Is it already installed?
					if (isset($installed[ $basename ]))
					continue;

					$path = $folder . '/' . $basename;
					$dot = strpos($file_pack['filename'], '.');

					if ($dot !== false)
					$internal_name = substr($basename, 0, $dot);
					else
					fatal_lang_error('arcade_invalid_file', false, array($path));

					if (substr($basename, -3) == 'swf')
					{
						$name = str_replace('_', ' ', $internal_name);
						$games[$internal_name] = array(
						'internal_name' => $internal_name,
						'name' => ucwords($name),
						'path' => $path,
						'file' => $basename,
						'type' => substr($file_pack['filename'], -3),
						);
					}

				}
			}
		}

		// There were packages with errors or no games...
		if (count($games) == 0)
		fatal_lang_error('arcade_no_files_selected');


		// Session will store data
		$_SESSION['install'] = array(
		'to_check' => $games,
		'games' => array(),
		'done' => 0,
		'total' => count($files),
		'begin' => time(),
		'end' => 0,
		);

		redirectexit('action=admin;area=managearcade;sa=files;do=stageone');

	}
	// Do we install in progress?
	elseif (isset($_SESSION['install']))
	{
		//use getid...it works!!
		require_once($boarddir . '/getid3/getid3.php');

		$install = &$_SESSION['install'];
		$context['arcade']['install'] = array();

		// Games that needs to be checked
		foreach ($install['to_check'] as $key => $file)
		{
			//we need meta-data such as width and height
			$swf = new getID3;
			$swf->analyze($arcSettings['gamesDirectory'] . $file['path']);

			$file['width'] = $swf->info['video']['resolution_x'];
			$file['height'] = $swf->info['video']['resolution_y'];
			$file['bgcolor'] = $swf->info['swf']['bgcolor'];


			// Change working directory and try to find thumbails
			$directory = substr($file['path'], 0, strrpos($file['path'], '/'));
			chdir($arcSettings['gamesDirectory']  . $directory);
			$thumbnail = glob($file['internal_name'] . '.{png,gif,jpg}', GLOB_BRACE);
			if(!$thumbnail)
			{
				$thumbnail = glob($file['internal_name'] . '1.{png,gif,jpg}', GLOB_BRACE);
			}

			// Build array
			$context['arcade']['install'][] = array(
			'game_name' => $file['name'],
			'internal_name' => $file['internal_name'],
			'directory' => $directory,
			'file' => $file['file'],
			'thumbnail' => isset($thumbnail[0]) ? $thumbnail[0] : '',
			'score_type' => 0,
			'width' => isset($file['width']) ? $file['width'] : '400',
			'height' => isset($file['height']) ? $file['height'] : '300',
			'bgcolor' => $file['bgcolor'],
			);
		}

		$_SESSION['install2'] = $context['arcade']['install'];
		$context['arcade']['categories'] = prepareCategories();

		// Template
		$context['page_title'] = $txt['arcade_title_install_games'];
		$context['sub_template'] = 'games_install';
	}
	else
	   fatal_lang_error('arcade_no_files_selected');

}

function GamesAdminInstall2()
{
	global $scripturl, $txt, $arcSettings, $context;

	checkSession('post');

	if (!isset($_SESSION['install2']) || !isset($_POST['defaults']) || !isset($_POST['game']) || !is_array($_POST['defaults']) || !is_array($_POST['game']))
		fatal_lang_error('arcade_no_files_selected');


	$messages = array();
	foreach ($_SESSION['install2'] as $game2)
		$messages = array_merge($messages, createGame(array_merge($_POST['defaults'], array_merge($game2, $_POST['game'][$game2['internal_name']]))));

	$context['arcade']['messages'] = &$messages;
	unset($_SESSION['install']);

	ArcadeCountGames();
	if(file_exists($arcSettings['cacheDirectory'].'cats.cache')){unlink($arcSettings['cacheDirectory'].'cats.cache');}

	// Template
	$context['page_title'] = $txt['arcade_title_install_games'];
	$context['sub_template'] = 'games_install_complete';
}

function GamesUpload()
{
	global $scripturl, $txt, $arcSettings, $context, $boarddir;

	// We need this
	loadLanguage('Packages');

	// Was upload successful?
	if (!isset($_FILES['package']['name']) || $_FILES['package']['name'] == '' || !is_uploaded_file($_FILES['package']['tmp_name']) || (@ini_get('open_basedir') == '' && !file_exists($_FILES['package']['tmp_name'])))
		//fatal_lang_error('package_upload_error');

	// Remove dangerous / non websafe characters
	$_FILES['package']['name'] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $_FILES['package']['name']);

	$file_type = strtolower(substr($_FILES['package']['name'], -4));

	if (($file_type != '.tar') && ($file_type != '.zip'))
		fatal_error($txt['arcade_upload_filetypes'], false);

	// Get filename
	$filename = basename($_FILES['package']['name']);

	// Where it will be saved?
	if ($file_type == '.zip')
	{
		$destination = $arcSettings['gamesDirectory'] . $filename;
	}
	else
	{
		$destination = $boarddir . '/tempGames/' . $filename;
	}
	@chmod($destination, 0777);
	// It is already here
	if (file_exists($destination))
		fatal_lang_error('package_upload_error_exists');

	// Move and make writable
	move_uploaded_file($_FILES['package']['tmp_name'], $destination);
	@chmod($destination, 0777);

	if ($file_type == '.zip')
	{
		redirectexit('action=admin;area=managearcade;sa=files;file='. $filename);
	}
	else
	{
		redirectexit('action=admin;area=managearcade;sa=autofiles');
	}
}

function createGame($game)
{
	global $smcFunc,$scripturl, $txt, $arcSettings, $context, $sourcedir, $boarddir;

	$errors = array();

	// member_groups
	if (isset($game['member_groups']) && is_array($game['member_groups']))
		$game['member_groups'] =	implode(',', $game['member_groups']);

	// Defaults
	$default = array(
		'id_category' => $arcSettings['arcadeDefaultCategory'],
		'width' => 100,
		'height' => 70,
		'description' => '',
		'help' => '',
		'directory' => '',
		'score_type' => 0,
		'bgcolor' => '',
		'topic_id' => '0',
		'thumbnail' => '',
		'member_groups' => '-1,0,2',
		'enabled' => 1,
	);

	// We convert these to MySQL field names
	$convert = array(
		'width' => 'game_width',
		'height' => 'game_height',
		'file' => 'game_file',
		'directory' => 'game_directory',
		'bgcolor' => 'game_bg_colour',
	);

	// Add missing values which have default values
	foreach ($default as $key => $value)
		if (!isset($game[$key]))
			$game[$key] = $value;

	// Convert
	foreach ($convert as $from => $to)
		if (isset($game[$from]))
		{
			$game[$to] = $game[$from];
			unset($game[$from]);
		}

	// Types
	$numeric = array('game_width', 'game_height', 'score_type',  'id_category');

	// What is required always and may not be empty
	$required = array('internal_name', 'game_name', 'game_file');

	// Check regured values
	foreach ($required as $key)
		if (!isset($game[$key]))
			$errors[] = sprintf($txt['arcade_field_error'], $game['file'], $key, $txt['arcade_filed_numeric']);

	// Check numeric values
	foreach ($numeric as $key)
		if (!is_numeric($game[$key]))
			$errors[] = sprintf($txt['arcade_field_error'], $game['game_name'], $key, $txt['arcade_filed_numeric']);

	// We have error :(
	if (count($errors) > 0)
		return $errors;

	// Build Query
	$values = array();

	foreach ($game as $key => $value)
		if (in_array($key, $numeric))
			$values[] = $key . ' = ' . $value;
		else
			$values[] = $key . ' = "' . addslashes($value) . '"';

	 $smcFunc['db_query']('', '
		INSERT INTO {db_prefix}arcade_games
		SET '. implode(', ', $values).'',
			array(
			)
		);

	$gameid = $smcFunc['db_insert_id']('{db_prefix}arcade_games', 'id_game');

	if($arcSettings['arcadePostTopic']!=0)
	{
		$topic_ID = addArcadeTopic($game['game_name'],$gameid);
		$smcFunc['db_query']('', '
		UPDATE {db_prefix}arcade_games
		SET
		topic_id = {int:idt}
		WHERE id_game = {int:idg}',
		array(
		'idt' => $topic_ID,
		'idg' => $gameid,
		)
		);
	}

	return array(sprintf($txt['arcade_game_installed'], '<a href="' . $scripturl . '?game=' . $gameid . '">' . $game['game_name'] . '</a>'));;
}

function GamesAutoFiles()
{
	global $context, $txt, $boarddir;

	if(!isset($_GET['sub']))
	{
		$path = $boarddir.'/tempGames/';

		$handle = opendir($path);
		while (false !== ($file = readdir($handle)))
		{
			$type = (substr($file, -3, 4));

			if ($file != "." && $file != ".." && ($type=='tar'||$type=='php'))
			{
				$context['arcade']['toinstall'][] = $file;
			}
		}
		closedir($handle);

		$context['arcade']['sub_action'] = "massinstall";
	}
	elseif($_GET['sub'] == "massi1")
	{
		$context['arcade']['sub_action'] = "massinstall2";
		$cat=$_POST['category'];
		autoinstaller($cat);
	}

	// Title for page
	$context['page_title'] = $txt['arcade_auto_game'];
	// Subtemplate
	$context['sub_template'] = 'auto_files';

}

function autoinstaller($cat)
{
	global $smcFunc, $boarddir, $context, $arcSettings, $sourcedir, $txt;

	require_once($sourcedir . '/ArcadeTar.php');

	$path = $boarddir.'/tempGames/';

  //Check tempGames for tar files and extract them
	$handle = opendir($path);
	while (false !== ($file = readdir($handle)))
	{
		$type = (substr($file, -3, 4));
		if ($file != "." && $file != ".." && $type=='tar')
		{
			//extract the files from the tar
			$tar = new Archive_Tar($path.$file);
			$tar->extract($path);
			unset($tar);
			//delete the tar, its not need now
			unlink($path.$file);
		}

	}
	closedir($handle);


	//check and move any gamedata folders to /arcade/gamedata/
	$handle = opendir($path.'/gamedata/');
	while (false !== ($file = readdir($handle)))
	{
		if ($file != "." && $file != "..")
		{
			rename($path.'gamedata/'.$file,$boarddir.'/arcade/gamedata/'.$file);
		}
	}
	closedir($handle);


	//get list of .php game install files from tempGames
	$i=0;
	$the_array = Array();
	$handle = opendir($path);
	while (false !== ($file = readdir($handle)))
	{
		$type = (substr($file, -3, 4));

		if ($file != "." && $file != ".." && $type=='php')
		{
			$the_array[$i] = $file;
			$i++;
		}
	}
	closedir($handle);

	$context['arcade']['installed_games'] = array();
	$context['arcade']['failed_games'] = array();

	if($i == 0)
	{
		$context['arcade']['failed_games'][] = $txt['arcade_nophpgames'];
		return;
	}

	//install the list
	foreach ($the_array as $key => $value)
	{
		include($path.$value);

		$file=$config['gname'].'.swf';
		$gif=$config['gname'];
	//	echo $config['gname'];

		$fail=0;

			//check the swf file is there
			if (file_exists($path.$file))
			{
				//check for xxx.gif and move it if its there
				if(file_exists($path.$gif.'.gif'))
				{
					//try to copy xxx.gif to Games folder
					if(rename($path.$gif.'.gif',$arcSettings['gamesDirectory'].$gif.'.gif')==true)
					{
						$fail=0;
						$thumb=$gif.'.gif';
					}
					else
					{
						$fail=1;
					}

				}
				//check for xxx1.gif for ipb games and move it if its there
				elseif(file_exists($path.$gif.'1.gif'))
				{
					//try to copy and rename xxx1.gif to games folder
					if(rename($path.$gif.'1.gif',$arcSettings['gamesDirectory'].$gif.'.gif')==true)
					{
						$fail=0;
						$thumb=$gif.'.gif';
					}
					else
					{
						$fail=1;
					}
				}

				//if fail is still 0 then a gif has been moved
				if($fail==0)
				{
				//try to copy swf to games folder
					if(rename($path.$file,$arcSettings['gamesDirectory'].$file)==true)
					{
						$fail=0;
					}
					else
					{
						$fail=1;
					}
				}

				//if everything copied ok add the game to the database and to the installed list
				if($fail==0)
				{
					$topic_ID = 0;

					$smcFunc['db_insert']('',
					'{db_prefix}arcade_games',
						array(
						'internal_name' => 'string-255',
						'game_name' => 'string-255',
						'game_file' => 'string-255',
						'game_directory' => 'string-255',
						'description' => 'string',
						'help' => 'string',
						'thumbnail' => 'string-255',
						'id_category' => 'int',
						'enabled' => 'int',
						'member_groups' => 'string-50',
						'score_type' => 'int',
						'game_width' => 'int',
						'game_height' => 'int',
						'game_bg_colour' => 'string-6',
						'topic_id' => 'int'),
						array($config['gname'],addslashes($config['gtitle']),$file,'',addslashes($config['gwords']),'',$thumb,$cat,1,'-1,0,2',0,$config['gwidth'],$config['gheight'],'000000',$topic_ID),
						array('id_game')
					);

					if($arcSettings['arcadePostTopic']!=0)
					{
						$gameid = $smcFunc['db_insert_id']('{db_prefix}arcade_games', 'id_game');
						$topic_ID = addArcadeTopic($config['gtitle'],$gameid);
						$smcFunc['db_query']('', '
							UPDATE {db_prefix}arcade_games
							SET
							topic_id = {int:idt}
							WHERE id_game = {int:idg}',
							array(
							'idt' => $topic_ID,
							'idg' => $gameid,
							)
							);
					}

					$context['arcade']['installed_games'][] = $config['gtitle'];

					//delete any unwanted gifs
					if(file_exists($path.$gif.'.gif'))
                    {
                            unlink($path.$gif.'.gif');
                    }
					if(file_exists($path.$gif.'1.gif'))
                    {
                        unlink($path.$gif.'1.gif');
                    }
					if(file_exists($path.$gif.'2.gif'))
                    {
                        unlink($path.$gif.'2.gif');
                    }
					//delete the games php file
					unlink($path.$value);
				}

			}
			else
			{
				$fail=1;
			}
		}

		//if something went wrong, add to the failed list
		if($fail==1)
		{
			$context['arcade']['failed_games'][] = $config['gtitle'];
		}


	ArcadeCountGames();
	GamesCacheClear();

}

function addArcadeTopic($topicName,$gameid)
{
        global $txt, $user_info, $arcSettings, $scripturl;
        global $sourcedir;

        require_once($sourcedir . '/Subs-Post.php');

        $gamename = $topicName;
        $topicTalk = $txt['arcade_topic_talk2'].'<a href="'. $scripturl. '?action=arcade;sa=play;game='.$gameid.'">'.$topicName.'</a>';

        $msgOptions = array(
                'id' => 0,
                'subject' => $gamename,
                'body' => $topicTalk,
                'icon' => "xx",
                'smileys_enabled' => true,
                'attachments' => array(),
        );
        $topicOptions = array(
                'id' => 0,
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



function GamesTopicUpdater()
{
	global $txt, $smcFunc, $user_info, $arcSettings,$scripturl;

	$result = $smcFunc['db_query']('', '
		SELECT id_game, game_name
		FROM {db_prefix}arcade_games',
		array(
		)
	);
	while ($games = $smcFunc['db_fetch_assoc']($result))
	{
		$topicid = addArcadeTopic($games['game_name'],$games['id_game']);

		$smcFunc['db_query']('', '
			UPDATE {db_prefix}arcade_games
			SET
			topic_id = {int:idt}
			WHERE id_game = {int:idg}',
				array(
				'idt' => $topicid,
				'idg' => $games['id_game'],
				)
		);
	}

	redirectexit('action=admin;area=managearcade;sa=arc_maintenance');
}

function GamesCacheClear()
{
	global $arcSettings;

	if(file_exists($arcSettings['cacheDirectory'].'cats.cache')){unlink($arcSettings['cacheDirectory'].'cats.cache');}
	//if(file_exists($arcSettings['cacheDirectory'].'gameinfo.cache')){unlink($arcSettings['cacheDirectory'].'gameinfo.cache');}
	if(file_exists($arcSettings['cacheDirectory'].'gotd.cache')){unlink($arcSettings['cacheDirectory'].'gotd.cache');}
	//if(file_exists($arcSettings['cacheDirectory'].'latescore.cache')){unlink($arcSettings['cacheDirectory'].'latescore.cache');}
	if(file_exists($arcSettings['cacheDirectory'].'arcadeinfopanel.cache')){unlink($arcSettings['cacheDirectory'].'arcadeinfopanel.cache');}
	if(file_exists($arcSettings['cacheDirectory'].'newsFader.cache')){unlink($arcSettings['cacheDirectory'].'newsFader.cache');}

	if (isset($_REQUEST['sa'])&& $_REQUEST['sa']=='clear')
	{
		redirectexit('action=admin;area=managearcade;sa=arc_maintenance');
	}
	elseif (isset($_REQUEST['sa'])&& $_REQUEST['sa']=='autofiles')
	{
		//redirectexit('action=admin;area=managearcade;sa=autofiles');
		return;
	}
	else
	{
		redirectexit('action=admin;area=managearcade;sa=listgames');
	}
}

function preparemember_groups($ryhmat = null)
{
	global $smcFunc, $modSettings, $context, $txt;

	if ($ryhmat == null)
		$ryhmat = array();

	// Load member_groups.
	$context['groups'][-1] = array(
		'id' => -1,
		'name' => $txt['membergroups_guests'],
		'checked' => in_array(-1, $ryhmat),
		'is_post_group' => false,
	);

	$context['groups'][0] = array(
		'id' => 0,
		'name' => $txt['membergroups_members'],
		'checked' => in_array(0, $ryhmat),
		'is_post_group' => false,
	);


		$result = $smcFunc['db_query']('', '
		SELECT group_name, id_group, min_posts
		FROM {db_prefix}membergroups
		WHERE id_group > 3 OR id_group = 2
		ORDER BY min_posts, id_group != 2, group_name',
		array(
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($result))
		{
		$context['groups'][(int) $row['id_group']] = array(
			'id' => $row['id_group'],
			'name' => trim($row['group_name']),
			'checked' => in_array($row['id_group'], $ryhmat),
			'is_post_group' => $row['min_posts'] != -1,
	);

}

}
?>