<?php
/*

Rough take on Twitter oEmbed for VeloRooms
by L'arri : voici.l.arriviste@gmail.com
27 October 2014

Packaged and modified by SMFHacks.com - vbgamer45

*/

ini_set("display_errors",1);
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

db_extend('packages');
db_extend('extra');


$smcFunc['db_create_table']('{db_prefix}tweet_cache',
	array(
		array(
			'name' => 'id',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
		),
		array(
			'name' => 'tweetid',
			'type' => 'bigint',
			'size' => 30,
			'default' => 0,
		),

		array(
			'name' => 'html',
			'type' => 'text',
		),	
	),
	array(
		array(
			'name' => 'id',
			'type' => 'primary',
			'columns' => array('id'),
		),
		array(
			'name' => 'tweetid',
			'type' => 'index',
			'columns' => array('tweetid')
		)
        
	),
	array(),
	'ignore');

// Handle Emoji's
$smcFunc['db_query']('','ALTER TABLE {db_prefix}tweet_cache CHANGE html html text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

?>
