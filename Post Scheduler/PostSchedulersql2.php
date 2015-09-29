<?php
/*
Post Scheduler
Version 1.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2012-2014 http://www.samsonsoftware.com
*/
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
  
db_extend('packages');
db_extend('extra');


$smcFunc['db_create_table']('{db_prefix}postscheduler',
	array(
		array(
			'name' => 'ID_POST',
			'type' => 'int',
			'size' => 11,
			'auto' => true,
		),
		array(
			'name' => 'ID_BOARD',
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
			'name' => 'subject',
			'type' => 'varchar',
			'size' => 255,
		),
		array(
			'name' => 'body',
			'type' => 'text',
		),	
		array(
			'name' => 'postername',
			'type' => 'varchar',
			'size' => 255,
		),
		array(
			'name' => 'ID_MEMBER',
			'type' => 'int',
			'size' => 11,
		),
		array(
			'name' => 'post_time',
			'type' => 'int',
			'size' => 10,
		),     
		array(
			'name' => 'msgicon',
			'type' => 'varchar',
			'size' => 50,
            'default' => 'xx',
		),
		array(
			'name' => 'locked',
			'type' => 'tinyint',
			'size' => 1,
			'default' => 0,
		),
		array(
			'name' => 'hasposted',
			'type' => 'tinyint',
			'size' => 1,
			'default' => 0,
		),     
        
	),
	array(
		array(
			'name' => 'ID_POST',
			'type' => 'primary',
			'columns' => array('ID_POST'),
		),
		array(
			'name' => 'post_time',
			'type' => 'index',
			'columns' => array('post_time')
		)
        
	),
	array(),
	'ignore');

 
 
 /* Orginal for reference
 
 "CREATE TABLE IF NOT EXISTS {db_prefix}postscheduler
(ID_POST mediumint(8) NOT NULL auto_increment,
ID_BOARD smallint(5) unsigned NOT NULL default '0',
id_topic mediumint(8) unsigned NOT NULL default '0',
subject varchar(255),
body text NOT NULL,
postername tinytext,
ID_MEMBER mediumint(8) unsigned,
post_time int(10),
msgicon varchar(50) default 'xx',
locked tinyint(1) NOT NULL default '0',
hasposted tinyint(1) NOT NULL default '0',
PRIMARY KEY  (ID_POST),
KEY (post_time)
*/


// Fake cron setting default false
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('post_fakecron', '0')");



// Add the scheduled task
$dbresult = $smcFunc['db_query']('', "
SELECT 
	COUNT(*) as total 
FROM {db_prefix}scheduled_tasks
WHERE task = 'update_scheduleposts'");
$row = $smcFunc['db_fetch_assoc']($dbresult);
if ($row['total'] == 0)
{
    /* Reference
    "INSERT INTO {db_prefix}scheduled_tasks
	   (time_offset, time_regularity, time_unit, disabled, task)
	VALUES ('0', '1', 'h', '0', 'update_scheduleposts')"
    */
    
    $smcFunc['db_insert']('',
					'{db_prefix}scheduled_tasks',
					array('time_offset' => 'int', 'time_regularity' => 'int', 'time_unit' => 'string','disabled' => 'int','update_scheduleposts' => 'string'),
					array(0,1,'h',0,'update_scheduleposts'),
					array('id_task')
				);
    

}

?>