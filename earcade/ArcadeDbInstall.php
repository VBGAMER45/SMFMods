<?php
/************************************************************************************
* E Arcade 3.0 (http://www.smfhacks.com)                                            *
* Copyright (C) 2014-2015  http://www.smfhacks.com                                       *
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
********************************************************************************                                                              *
*                                                                                   *
* This file contains functions and data for database installer                      *
*************************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

//some globals we need
global $db_prefix, $smfFunc, $smcFunc, $db_type, $db_name, $messages;

$arcade_version = '3.0';
$database_version= '12';


//start the database extras
db_extend('extra');
db_extend('packages');

//array for install messages
$messages = array();


//Arcade tables/columns array
$arcadeTables = array();
// Games table
$arcadeTables['arcade_games'] = array(
	'name' => 'arcade_games',
	'columns' => array(
		array(
			'name' => 'id_game',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'internal_name',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
		array(
			'name' => 'game_name',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
		array(
			'name' => 'game_file',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
		array(
			'name' => 'game_directory',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
		array(
			'name' => 'description',
			'type' => 'text',
			'null' => false
		),
		array(
			'name' => 'help',
			'type' => 'text',
			'null' => false
		),
		array(
			'name' => 'thumbnail',
			'type' => 'varchar',
			'size' => 255,
			'null' => true
		),
		array(
			'name' => 'id_category',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'enabled',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'member_groups',
			'type' => 'varchar',
			'size' => 50,
			'null' => false
		),
		array(
			'name' => 'score_type',
			'type' => 'tinyint',
			'null' => false
		),
		array(
			'name' => 'game_rating',
			'type' => 'tinyint',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'id_member_first',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'id_score_first',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'id_member_second',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'id_score_second',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'id_member_third',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'id_score_third',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'game_width',
			'type' => 'int',
			'default' => 400,
			'null' => false
		),
		array(
			'name' => 'game_height',
			'type' => 'int',
			'default' => 300,
			'null' => false
		),
		array(
			'name' => 'game_bg_colour',
			'type' => 'varchar',
			'size' => 6,
			'null' => false
		),
		array(
			'name' => 'topic_id',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'number_plays',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
		array(
			'name' => 'number_rates',
			'type' => 'int',
			'default' => 0,
			'null' => false
		),
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_game')
		),
		array(
			'name' => 'internal_name',
			'type' => 'unique',
			'columns' => array('internal_name')
		),
		array(
			'name' => 'game_file',
			'type' => 'unique',
			'columns' => array('game_file')
		),
	)
);

$arcadeTables['arcade_personalbest'] = array(
	'name' => 'arcade_personalbest',
	'columns' => array(
		array(
			'name' => 'id_best',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'id_game',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'id_member',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'score',
			'type' => 'float',
			'null' => false,
		),
		array(
			'name' => 'atbscore',
			'type' => 'float',
			'null' => false,
		),
		array(
			'name' => 'my_plays',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'playing_time',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'time_gained',
			'type' => 'int',
			'null' => false,
		)
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_best')
		),
		array(
			'name' => 'id_game',
			'type' => 'index',
			'columns' => array('id_game', 'id_member')
		)
	)
);

// Scores table
$arcadeTables['arcade_scores'] = array(
	'name' => 'arcade_scores',
	'columns' => array(
		array(
			'name' => 'id_score',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'id_game',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'id_member',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'score',
			'type' => 'float',
			'null' => false
		),
		array(
			'name' => 'start_time',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'end_time',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'champion_from',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'champion_to',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'game_duration',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'member_ip',
			'type' => 'varchar',
			'size' => 15,
			'null' => false
		),
		array(
			'name' => 'comment',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
		array(
			'name' => 'position',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'score_status',
			'type' => 'int',
			'null' => false
		),
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_score')
		),
		array(
			'name' => 'id_game',
			'type' => 'index',
			'columns' => array('id_game')
		),
	)
);

// Categories
$arcadeTables['arcade_categories'] = array(
	'name' => 'arcade_categories',
	'columns' => array(
		array(
			'name' => 'id_category',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'category_name',
			'type' => 'varchar',
			'size' => 20,
			'null' => false,
		),
		array(
			'name' => 'category_icon',
			'type' => 'varchar',
			'size' => 20,
			'null' => false,
		),
		array(
			'name' => 'category_order',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'special',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'member_groups',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_category')
		),
		array(
			'name' => 'category_name',
			'type' => 'unique',
			'columns' => array('category_name')
		)
	)
);

// Favorites
$arcadeTables['arcade_favorite'] = array(
	'name' => 'arcade_favorite',
	'columns' => array(
		array(
			'name' => 'id_favorite',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'id_member',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'id_game',
			'type' => 'int',
			'null' => false,
		)
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_favorite')
		),
		array(
			'name' => 'id_game',
			'type' => 'index',
			'columns' => array('id_game', 'id_member')
		)
	)
);

// Rates
$arcadeTables['arcade_rates'] = array(
	'name' => 'arcade_rates',
	'columns' => array(
		array(
			'name' => 'id_rating',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'id_member',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'id_game',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'rating',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'rate_time',
			'type' => 'int',
			'null' => false,
		)
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_rating')
		),
		array(
			'name' => 'id_game',
			'type' => 'index',
			'columns' => array('id_game')
		)
	)
);

// Settings
$arcadeTables['arcade_settings'] = array(
	'name' => 'arcade_settings',
	'columns' => array(
		array(
			'name' => 'variable',
			'type' => 'tinytext',
			'null' => false,
		),
		array(
			'name' => 'value',
			'type' => 'text',
			'null' => false,
		),
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('variable (30)')
		)
	)
);

$arcadeTables['arcade_v3temp'] = array(
	'name' => 'arcade_v3temp',
	'columns' => array(
		array(
			'name' => 'id',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'game',
			'type' => 'varchar',
			'size' => 50,
			'null' => false,
		),
		array(
			'name' => 'score',
			'type' => 'float',
			'null' => false
		),
		array(
			'name' => 'starttime',
			'type' => 'double',
			'null' => false
		),
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id')
		)
	)
);

$arcadeTables['arcade_shouts'] = array(
	'name' => 'arcade_shouts',
	'columns' => array(
		array(
			'name' => 'id_shout',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'id_member',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'content',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
		array(
			'name' => 'time',
			'type' => 'int',
			'null' => false
		),
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_shout')
		)
	)
);

$arcadeTables['arcade_tournament_rounds'] = array(
	'name' => 'arcade_tournament_rounds',
	'columns' => array(
		array(
			'name' => 'id_round',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'id_tour',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'round_number',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'id_game',
			'type' => 'int',
			'null' => false
		),
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_round')
		)
	)
);
// Favorites
$arcadeTables['arcade_tournament_players'] = array(
	'name' => 'arcade_tournament_players',
	'columns' => array(
		array(
			'name' => 'id_tour_player',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'id_member',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'id_tour',
			'type' => 'int',
			'null' => false,
		)
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_tour_player')
		),
		array(
			'name' => 'id_tour',
			'type' => 'index',
			'columns' => array('id_tour', 'id_member')
		)
	)
);

// Rates
$arcadeTables['arcade_tournament_scores'] = array(
	'name' => 'arcade_tournament_scores',
	'columns' => array(
		array(
			'name' => 'id_tour_score',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'id_member',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'id_game',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'id_tour',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'score',
			'type' => 'int',
			'null' => false,
		),
		array(
			'name' => 'time',
			'type' => 'int',
			'null' => false,
		),
				array(
			'name' => 'round_number',
			'type' => 'int',
			'null' => false,
		)
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_tour_score')
		),
		array(
			'name' => 'id_tour',
			'type' => 'index',
			'columns' => array('id_tour')
		)
	)
);

// tournament table
$arcadeTables['arcade_tournament'] = array(
	'name' => 'arcade_tournament',
	'columns' => array(
		array(
			'name' => 'id_tour',
			'type' => 'int',
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'id_member',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'name',
			'type' => 'varchar',
			'size' => 30,
			'null' => false
		),
		array(
			'name' => 'players',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'rounds',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'round_data',
			'type' => 'varchar',
			'size' => 100,
			'null' => false
		),
		array(
			'name' => 'password',
			'type' => 'varchar',
			'size' => 50,
			'null' => false
		),
		array(
			'name' => 'tour_start_time',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'active',
			'type' => 'int',
			'null' => false
		),
		array(
			'name' => 'results',
			'type' => 'varchar',
			'size' => 50,
			'null' => false
		),
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_tour')
		),
	)
);

//first check if were upgrading
$upgrade = false;

//we need the latest table names to compare
$tablenames = array();
foreach ($arcadeTables as $arcadeTable)
{
	$tablenames[]=$db_prefix.$arcadeTable['name'];
}

//if any old arcade tables exists there must have been an arcade
$tables = $smcFunc['db_list_tables']();
foreach ($tables as $table)
{
	if (in_array($table, $tablenames))
	{
		$messages[]='<br />Found '.$table.' table';
		$upgrade = true;
	}
}


//set this so we can send the db_prefix
$parameters = array(
	'no_prefix' => $db_prefix,
	);

//use the built in smf functions to create/check/update the tables
foreach ($arcadeTables as $arcadeTable)
{
	//$tablename = $arcadeTable['name'];
	$smcFunc['db_create_table']($db_prefix.$arcadeTable['name'], $arcadeTable['columns'], $arcadeTable['indexes'], $parameters, $if_exists = 'update_remove');
	if(!$upgrade)
	$messages[]='Create table '.$arcadeTable['name'].'<br />';
}


//if its not an upgrade then setup some default stuff
if($upgrade == false)
{
	arcade_runOnce($arcade_version,$database_version);
}
else
{
	//arcade default settings array
	$arcadeSettings = array(
	'arcade_show_shouts' => 10,
	'arcadeVersion' => $arcade_version,
	'arcadeDatabaseVersion' => $database_version,
	);

	foreach ($arcadeSettings as $variable => $value)
	{
		$replaceArray[] = array($variable, $value);
	}

	if (empty($replaceArray))
		return;

	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_settings',
		array('variable' => 'string-255', 'value' => 'string-65534'),
		$replaceArray,
		array('variable')
	);

}


//function to update/reset arcade settings
function arcade_runOnce($arcade_version,$database_version)
{
	global $boarddir, $boardurl, $smcFunc,$messages;

	//arcade default settings array
	$arcadeSettings = array(
	'gamesPerPage' => 25,
	'gamesPerPageAdmin' => 50,
	'scoresPerPage' => 50,
	'gamesDirectory' => $boarddir . '/Games/',
	'cacheDirectory' => $boarddir . '/cache/',
	'gamesUrl' => $boardurl . '/Games/',
	'arcadeEnabled' => true,
    'arcade_total_games' => 1,
    'arcade_last_update' => '',
	'arcadeCheckLevel' => 1,
	'arcadeDefaultCategory' => 1,
	'arcadegotd' => 0,
	'gameFrontPage' => 0,
	'arcadeNewsFader' => 0,
	'arcadeNewsNumber' => 5,
	'arcadeMaxScores' => 1,
	'arcadePermissionMode' => 0,
	'arcadePostPermission' => 0,
	'arcadePostsPlay' => 0,
	'arcadePostsPlayPerDay' => 0,
	'arcadePostsPlayDays'=> 0,
	'arcade_champions_in_post' => 3,
	'arcade_champion_sig' => 1,
	'arcade_champion_pp' => 1,
	'arcadePMsystem' => 1,
	'arcadePostTopic' => 0,
	'enable_post_comment' => 0,
	'enable_shout_score' => 1,
	'enable_shout_comment' => 1,
	'arcade_show_shouts' => 10,
	'enable_shout_box_comment' => 1,
	'enable_shout_box_members' => 1,
	'enable_shout_box_scores' => 1,
	'enable_shout_box_best' => 1,
	'enable_shout_box_champ' => 1,
	'enable_arcade_cache' => 1,
	'arcade_active_user' => 1,
	'arcadeVersion' => $arcade_version,
	'arcadeDatabaseVersion' => $database_version,
	);



	foreach ($arcadeSettings as $variable => $value)
	{
		$replaceArray[] = array($variable, $value);
	}

	if (empty($replaceArray))
		return;

	$smcFunc['db_insert']('replace',
		'{db_prefix}arcade_settings',
		array('variable' => 'string-255', 'value' => 'string-65534'),
		$replaceArray,
		array('variable')
	);
	//set/reset the default arcade settings

	$messages[]='<br />Set arcade settings to default settings<br />';


//set/reset the default permissions
	$permissions = array(
	'arcade_view' => array(-1, 0, 2), // Everyone
	'arcade_play' => array(-1, 0, 2), // Everyone
	'arcade_favorite' => array(0, 2), // Regular members
	'arcade_submit' => array(0, 2), // Regular members
	'arcade_admin' => array(), // Only admins will get this
	'arcade_comment_own' => array(0, 2), // Regular members
	'arcade_comment_any' => array(), // Only admins
	'arcade_rate' => array(0, 2), // Regular members
	'arcade_playtour' => array(0, 2),
	'arcade_createtour' => array(0, 2),
	);

	foreach ($permissions as $permission => $groups)
	{
		foreach ($groups as $ID_GROUP)
		{
			$replacePerm[] = array((int)$ID_GROUP, $permission, 1);
		}
	}

	if (empty($replaceArray))
		return;

				$smcFunc['db_insert']('replace',
			'{db_prefix}permissions',
			array('id_group' => 'int', 'permission' => 'string', 'add_deny' => 'int'),
			$replacePerm,
			array('id_group', 'permission')
		);

	$messages[]='<br />Set arcade permissions to default settings<br />';

		$smcFunc['db_insert']('replace',
			'{db_prefix}arcade_categories',
						array(
						'category_name' => 'string-20',
						'category_icon' => 'string-20',
						'category_order' => 'int',
						'special' => 'int',
						'member_groups' => 'string-255'),
						array('Default','arcade_cat6.gif',1,1,'-1,0,2'),
						array('id_category')
				);

		$smcFunc['db_insert']('replace',
			'{db_prefix}arcade_games',
						array(
						'internal_name' => 'string',
						'game_name' => 'string',
						'game_file' => 'string',
						'game_directory' => 'string',
						'description' => 'string',
						'help' => 'string',
						'thumbnail' => 'string',
						'id_category' => 'int',
						'enabled' => 'int',
						'member_groups' => 'string',
						'score_type' => 'int',
						'game_rating' => 'int',
						'id_member_first' => 'int',
						'id_score_first' => 'int',
						'id_member_second' => 'int',
						'id_score_second' => 'int',
						'id_member_third' => 'int',
						'id_score_third' => 'int',
						'game_width' => 'int',
						'game_height' => 'int',
						'game_bg_colour' => 'string',
						'topic_id' => 'int',
						'number_plays' => 'int',
						'number_rates' => 'int'),
						array('color_boxes', 'Color Boxes', 'color_boxes.swf', '', '', '', 'color_boxes.gif', 1, 1, '-1,0,2', 1, 0, 0, 0, 0, 0, 0, 0, 400, 300, '', 0, 0, 0),
						array('id_game')
				);



	$messages[]='<br />Add default category<br /><br />';



}



echo '
<div class="tborder" style="margin: 0px;">
<div class="titlebg" style="padding: 1ex;">
<b>E-Arcade ',$arcade_version,' Database Installer</b>
</div>
<div class="windowbg">';
echo'<br /><br />';
	if($upgrade)
	{
		$messages[]='<br />Database Upgraded<br />';
	}
	else
	{
		$messages[]='<br />Database Installed<br />';
	}
	foreach($messages as $message)
	{
		echo $message;
	}

echo'
	</div>
</div>';


?>