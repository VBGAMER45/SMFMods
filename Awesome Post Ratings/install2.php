<?php
/*
Awesome Post Ratings
Version 1.0
by:vbgamer45
http://www.smfhacks.com

License Information:
Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
*/


ini_set("display_errors",1);
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

db_extend('packages');
db_extend('extra');


$smcFunc['db_create_table']('{db_prefix}awesome_ratetypes',
	array(
		array(
			'name' => 'id_ratetype',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
		),
		array(
			'name' => 'title',
			'type' => 'varchar',
			'size' => 255,
		),
		array(
			'name' => 'enabled',
			'type' => 'tinyint',
			'size' => 1,
			'default' => 1,
		),
		array(
			'name' => 'points',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
		),
		array(
			'name' => 'icon',
			'type' => 'varchar',
			'size' => 255,
		),
		array(
			'name' => 'roworder',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
		),
        array(
			'name' => 'firstpostonly',
			'type' => 'tinyint',
			'size' => 1,
			'default' => 0,
		),
        
	),
	array(
		array(
			'name' => 'id_ratetype',
			'type' => 'primary',
			'columns' => array('id_ratetype'),
		),
        
	),
	array(),
	'ignore');
    
/*
CREATE TABLE IF NOT EXISTS {db_prefix}awesome_ratetypes(
id_ratetype int(11) NOT NULL auto_increment,
title varchar(255),
enabled tinyint(1) default 1,
points int(11) default 0,
icon varchar(255),
roworder int(11) default 0,
firstpostonly tinyint(1) default 0,
PRIMARY KEY (id_ratetype))");

*/    
    
$smcFunc['db_create_table']('{db_prefix}awesome_ratelog',
	array(
		array(
			'name' => 'id_log',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
		),
		array(
			'name' => 'id_ratetype',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
		),
		array(
			'name' => 'id_topic',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
		),   
		array(
			'name' => 'id_msg',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
		),  
		array(
			'name' => 'id_member',
			'type' => 'int',
			'size' => 11,
			'default' => 0,
		),
		array(
			'name' => 'logdate',
			'type' => 'int',
			'size' => 10,
			'default' => 0,
		),  
        
         
        
	),
	array(
		array(
			'name' => 'id_log',
			'type' => 'primary',
			'columns' => array('id_log'),
		),
		array(
			'name' => 'id_ratetype',
			'type' => 'index',
			'columns' => array('id_ratetype')
		),
		array(
			'name' => 'id_topic',
			'type' => 'index',
			'columns' => array('id_topic')
		),
		array(
			'name' => 'id_msg',
			'type' => 'index',
			'columns' => array('id_msg')
		),
		array(
			'name' => 'id_member',
			'type' => 'index',
			'columns' => array('id_member')
		),
		array(
			'name' => 'logdate',
			'type' => 'index',
			'columns' => array('logdate')
		)
        
	),
	array(),
	'ignore');
    
    
    
/*
CREATE TABLE IF NOT EXISTS {db_prefix}awesome_ratelog(
id_log int(11) NOT NULL auto_increment,
id_ratetype int(11),
id_topic int(11) default 0,
id_msg int(11) default 0,
id_member int(11) default 0,
logdate int(10),
PRIMARY KEY (id_log),
KEY `id_ratetype` (`id_ratetype`),
KEY `id_topic` (`id_topic`),
KEY `id_msg` (`id_msg`),
KEY `id_member` (`id_member`),
KEY `logdate` (`logdate`)


)");


*/    



    
// Post types
// Awesome
// Like
// Agree
// Disagree
// Fail
// LOL
// Informative
// Thanks

// Do the Insert

    
?>