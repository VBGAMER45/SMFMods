<?php
/*
Who Quoted Me
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

db_extend('packages');

 
// Set up default settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('whoquoted_enabled', '0')");

/*
id_log int(11) NOT NULL auto_increment,
id_member mediumint(8) unsigned NOT NULL default '0',
id_member_from mediumint(8) unsigned NOT NULL default '0',
id_topic mediumint(8) unsigned NOT NULL default '0',
id_msg int(8) unsigned NOT NULL default '0',
logdate int(10),
*/


  $columns = array(
	array(
		'name' => 'id_log',
		'type' => 'int',
		'size' => 11,
		'auto' => true,
	),
	array(
		'name' => 'id_member',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
	),
	array(
		'name' => 'id_member_from',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
	),
	array(
		'name' => 'id_topic',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
	),
	array(
		'name' => 'id_msg',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
	),
	array(
		'name' => 'logdate',
		'type' => 'int',
		'size' => 10,
		'unsigned' => true,
	),	
	

);

$indexes = array(
	array(
		'type' => 'primary',
		'columns' => array('id_log')
	),
	array(
		'columns' => array('id_member')
	),
	array(
		'columns' => array('id_member_from')
	),	
	array(
		'columns' => array('id_topic')
	),	
	array(
		'columns' => array('id_msg')
	),
	array(
		'columns' => array('logdate')
	),
	
);


$smcFunc['db_create_table']('{db_prefix}quoted_log', $columns, $indexes, array(), 'ignore');


/*
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}quoted_log 
(
id_log int(11) NOT NULL auto_increment,
id_member mediumint(8) unsigned NOT NULL default '0',
id_member_from mediumint(8) unsigned NOT NULL default '0',
id_topic mediumint(8) unsigned NOT NULL default '0',
id_msg int(8) unsigned NOT NULL default '0',
logdate int(10),
PRIMARY KEY  (id_log),
KEY (id_member),
KEY (id_member_from),
KEY (id_topic),
KEY (id_msg),
KEY (logdate)  

) Engine=MyISAM");


*/

?>