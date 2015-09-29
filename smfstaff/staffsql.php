<?php
//SMFHacks.com
//Table SQL

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');



// Create the Staff Table
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}staff
(ID_GROUP smallint(5) unsigned NOT NULL,
roworder mediumint(8) unsigned NOT NULL default '0',
PRIMARY KEY  (ID_GROUP))", __FILE__, __LINE__);



// Insert the settings
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smfstaff_showavatar', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smfstaff_showlastactive', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smfstaff_showdateregistered', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smfstaff_showcontactinfo', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('smfstaff_showlocalmods', '1')", __FILE__, __LINE__);



// Permissions array
$permissions = array(
	'view_stafflist' => array(-1, 0, 2), // ALL
);

addPermissions($permissions);

function addPermissions($permissions)
{
	global $db_prefix;

	foreach ($permissions as $permission => $default)
	{
		$result = db_query("
			SELECT COUNT(*)
			FROM {$db_prefix}permissions
			WHERE permission = '$permission'", __FILE__, __LINE__);

		list ($num) = mysql_free_result($result);

		if ($num == 0)
		{
			foreach ($default as $ID_GROUP)
			{
				db_query("
				INSERT IGNORE INTO {$db_prefix}permissions
					(id_group, permission)
				VALUES ('$ID_GROUP', '$permission')", __FILE__, __LINE__);
						
			}
	
		}
	}

		

}

?>