<?php
/*
Social Login Pro
Version 2.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2010-2012 SMFHacks.com

############################################
License Information:
Social Login Pro is NOT free software.
This software may not be redistributed.

The pro edition license is good for a single instance / install on a website.
You are allowed only one active install for each license purchase.

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}social_logins (
		  id mediumint(8) unsigned NOT NULL auto_increment,
		  id_member mediumint(8) unsigned NOT NULL default '0',
		  uid varchar(255),
		  nickname varchar(255),
		  email varchar(255),
		  photourl varchar(255),
		  profileurl varchar(255),
		  zip varchar(100),
		  state varchar(100),
		  city varchar(150),
		  country varchar(150),
		  
		  firstname varchar(100),
		  lastname varchar(100),
		  
		  birthmonth int(5),
		  birthday int(5),
		  birthyear int(5),
		  
		  thumbnailurl varchar(255),
		  loginprovider varchar(255),
		  loginprovideruid varchar(255),
		  provider varchar(255),
		  
		  proxiedemail varchar(255),
		  merged tinyint(1) default 0,
			
		  PRIMARY KEY (id),
		  KEY (uid)
		) ", __FILE__, __LINE__);

$dbresult = db_query("SHOW COLUMNS FROM {$db_prefix}social_logins", __FILE__, __LINE__);
$merged = 1;

while ($row = mysql_fetch_row($dbresult))
{
	if ($row[0] == 'merged')
		$merged = 0;	
}

if ($merged)
	db_query("ALTER TABLE {$db_prefix}social_logins ADD merged tinyint(1) default 0", __FILE__, __LINE__);




// Settings
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('slp_apikey', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('slp_secretkey', '')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('slp_buttonsStyle', 'default')", __FILE__, __LINE__);
db_query("REPLACE INTO {$db_prefix}settings VALUES ('slp_providers', 'facebook,twitter,yahoo,messenger,google,linkedin,myspace,aol,blogger,wordpress,typepad,livejournal,hyves,verisign,openid,netlog,bloglines,signon,orangefrance,mixi,livedoor,vkontakte,foursquare,paypal,digg')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('slp_enabledProviders', 'facebook,twitter,yahoo,messenger,google,linkedin,myspace,aol,blogger,wordpress')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('slp_loginheight', '70')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('slp_loginwidth', '450')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('slp_registerheight', '92')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('slp_registerwidth', '150')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('slp_disableregistration', '0')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('slp_allowaccountmerge', '1')", __FILE__, __LINE__);
db_query("INSERT IGNORE INTO {$db_prefix}settings VALUES ('slp_importavatar', '1')", __FILE__, __LINE__);

// Add Package Servers
db_query("DELETE FROM {$db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'", __FILE__, __LINE__);
db_query("REPLACE INTO {$db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')", __FILE__, __LINE__);

	
?>