<?php

// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

	
elseif( !defined('SMF') )
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $forum_version;

$bool = (!empty($smcFunc) ? true : false);

if($bool)
	install_smf2('{db_prefix}');
if(!$bool)
{
	$request = db_query("
		DESCRIBE {$db_prefix}members skype", __FILE__, __LINE__);
	if(mysql_num_rows($request) == 0)
	{

		
		db_query("
			ALTER TABLE {$db_prefix}members ADD COLUMN
			skype VARCHAR(50) NOT NULL default ''", __FILE__, __LINE__);
	}
	if(SMF == 'SSI')
   		echo '<b>The Skype setting has been added~.</b>';
}
function install_smf2($table_prefix)
{
	global $smcFunc;
	
	$request = $smcFunc['db_query']('', '
		DESCRIBE {db_prefix}members skype',
		array()
	);
	
	if($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		
		$table = $table_prefix . 'members';
		$skype_column = array(
			'name' => 'skype',
			'type' => 'varchar',
			'size' => 50,
		);
	
		db_extend('packages');
	
		$smcFunc['db_add_column']($table, $skype_column);
	}
	if(SMF == 'SSI')
   		echo '<b>The Skype setting has been added~.</b>';
}
?>