<?php
/**************************************************************************
* IgnoreTopics.php														*
***************************************************************************/

// Manual Install?
// If the user is manually installing allow them to upload install.php 
// providing its in the same directory as SMF, allow the install to proceed, 
if(!defined('SMF'))
	include_once('SSI.php');

// Make sure we have access to install packages
if(!array_key_exists('db_add_column', $smcFunc))
	db_extend('packages');

// Globals
global $db_prefix;

// Columns for our table
$table_columns = array(
	array('name' => 'id_member', 'type' => 'mediumint', 'size' => 8, 'null' => false, 'default' => 0),
	array('name' => 'id_topic', 'type' => 'mediumint', 'size' => 8, 'null' => false, 'default' => 0),
	array('name' => 'date_ignored', 'type' => 'int', 'size' => 12, 'null' => false, 'default' => 0)
);

// Indexes for our table
$table_indexes = array(
	array('name' => 'topic', 'columns' => array('id_member', 'id_topic'), 'type' => 'unique')
);

// Create the table

$smcFunc['db_create_table']('{db_prefix}ignore_topics', $table_columns, $table_indexes);

// If we're using SSI, tell them we're done
if(SMF == 'SSI')
	echo 'Database changes are complete!';

?>