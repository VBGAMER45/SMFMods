<?php
/*
SMF Gallery Lite Edition
Version 5.5
by:vbgamer45
http://www.smfhacks.com
Copyright 2008-2014 SMFHacks.com

############################################
License Information:
SMF Gallery is NOT free software.
This software may not be redistributed.

Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/
//Install the Database tables for SMF Gallery

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');




// Picture Table
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}gallery_pic(
ID_PICTURE int(11) NOT NULL auto_increment,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
date int(10) unsigned NOT NULL default '0',
title VARCHAR(100) NOT NULL,
description text,
 views int(10) NOT NULL default '0',
 filesize int(10) NOT NULL default '0',
 height int(10) NOT NULL default '0',
 width int(10) NOT NULL default '0',
 filename tinytext,
 thumbfilename tinytext,
 commenttotal int(10) NOT NULL default '0',
 ID_CAT int(10) NOT NULL default '0',
 approved tinyint(4) NOT NULL default '0',
 allowcomments tinyint(4) NOT NULL default '0',
 keywords VARCHAR(100),
 USER_ID_CAT int(10) NOT NULL default '0',
 totalratings int(10) NOT NULL default '0',
rating int(10) NOT NULL default '0',
type tinyint(4) NOT NULL default '0',
mediumfilename tinytext,
videofile tinytext,
PRIMARY KEY  (ID_PICTURE))", __FILE__, __LINE__);


$dbresult = db_query("SHOW COLUMNS FROM {$db_prefix}gallery_pic", __FILE__, __LINE__);
$totalratings = 1;
$rating = 1;
$type = 1;
$USER_ID_CAT = 1;
$mediumfilename = 1;
$videofile = 1;
$allowratings = 1;

while ($row = mysql_fetch_row($dbresult))
{
	if($row[0] == 'totalratings')
		$totalratings = 0;
	if($row[0] == 'rating')
		$rating = 0;
	if($row[0] == 'type')
		$type = 0;
	if($row[0] == 'USER_ID_CAT')
		$USER_ID_CAT = 0;

	if($row[0] == 'mediumfilename')
		$mediumfilename = 0;
	if($row[0] == 'videofile')
		$videofile = 0;

	if($row[0] == 'allowratings')
		$allowratings = 0;	
        
		
		
}
mysql_free_result($dbresult);

if($USER_ID_CAT)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD USER_ID_CAT int(10) NOT NULL default '0'", __FILE__, __LINE__);

if($totalratings)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD totalratings int(10) NOT NULL default '0'", __FILE__, __LINE__);


if($rating)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD rating int(10) NOT NULL default '0'", __FILE__, __LINE__);

if($type)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD type tinyint(4) NOT NULL default '0'", __FILE__, __LINE__);

if($mediumfilename)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD mediumfilename tinytext", __FILE__, __LINE__);
	
if($videofile)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD videofile tinytext", __FILE__, __LINE__);

if($allowratings)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD allowratings tinyint(4) NOT NULL default '1'", __FILE__, __LINE__);





// Picture comments
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}gallery_comment(
ID_COMMENT int(11) NOT NULL auto_increment,
ID_PICTURE int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
approved tinyint(4) NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (ID_COMMENT))", __FILE__, __LINE__);

// Gallery Category
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}gallery_cat
(ID_CAT mediumint(8) NOT NULL auto_increment,
title VARCHAR(100) NOT NULL,
description text,
roworder mediumint(8) unsigned NOT NULL default '0',
image VARCHAR(255) NOT NULL,
ID_PARENT smallint(5) unsigned NOT NULL default '0',
redirect tinyint(4) NOT NULL default '0',
PRIMARY KEY  (ID_CAT))", __FILE__, __LINE__);



// Gallery Category
$dbresult = db_query("SHOW COLUMNS FROM {$db_prefix}gallery_cat", __FILE__, __LINE__);
$ID_PARENT = 1;
$redirect = 1;

while ($row = mysql_fetch_row($dbresult))
{
	if($row[0] == 'ID_PARENT')
		$ID_PARENT = 0;
        
	if($row[0] == 'redirect')
		$redirect = 0;

}
mysql_free_result($dbresult);

if($ID_PARENT)
	db_query("ALTER TABLE {$db_prefix}gallery_cat ADD ID_PARENT smallint(5) unsigned NOT NULL default '0'", __FILE__, __LINE__);
    
 if($redirect)
	db_query("ALTER TABLE {$db_prefix}gallery_cat ADD redirect tinyint(4) NOT NULL default '0'", __FILE__, __LINE__);
       


// Gallery Reported Images
db_query("CREATE TABLE IF NOT EXISTS {$db_prefix}gallery_report
(ID int(11) NOT NULL auto_increment,
ID_PICTURE int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (ID))", __FILE__, __LINE__);


// Insert the settings
db_query("INSERT IGNORE INTO {$db_prefix}settings
	(variable, value)
VALUES
('gallery_max_height', '2500'),
('gallery_max_width', '2500'),
('gallery_max_filesize', '5000000'),
('gallery_who_viewing', '0'),
('gallery_commentchoice', '0'),
('gallery_set_images_per_page', '20'),
('gallery_set_images_per_row','4'),
('gallery_thumb_width', '120'),
('gallery_thumb_height', '78'),
('gallery_shop_commentadd', '0'),
('gallery_shop_picadd', '0'),
('gallery_set_showcode_bbc_image', '0'),
('gallery_set_showcode_directlink', '0'),
('gallery_set_showcode_htmllink', '0'),
('gallery_copyrightkey', ''),
('gallery_make_medium', '1'),
('gallery_medium_width', '600'),
('gallery_medium_height', '600'),
('gallery_avea_imported','0')
", __FILE__, __LINE__);


// Add indexes gallery pic table

$dbresult = db_query("SHOW INDEX FROM  {$db_prefix}gallery_pic", __FILE__, __LINE__);

$indexUSER_ID_CAT = 1;
$indexID_CAT = 1;
$indexID_MEMBER = 1;
$indexRating = 1;
$indexViews = 1;
$indexcommenttotal = 1;
$indextotalratings = 1;

while ($row = mysql_fetch_assoc($dbresult))
{
	if ($row['Column_name'] == 'ID_CAT')
		$indexID_CAT = 0;
	elseif ($row['Column_name'] == 'USER_ID_CAT')
		$indexUSER_ID_CAT = 0;	
	elseif ($row['Column_name'] == 'ID_MEMBER')
		$indexID_MEMBER = 0;	
	elseif ($row['Column_name'] == 'rating')
		$indexRating = 0;	
	elseif ($row['Column_name'] == 'views')
		$indexViews  = 0;	
	elseif ($row['Column_name'] == 'commenttotal')
		$indexcommenttotal = 0;	
	elseif ($row['Column_name'] == 'totalratings')
		$indextotalratings = 0;		
		
		
}

if ($indexID_CAT)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD INDEX (ID_CAT)", __FILE__, __LINE__);

if ($indexUSER_ID_CAT)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD INDEX (USER_ID_CAT)", __FILE__, __LINE__);

if ($indexID_MEMBER)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD INDEX (ID_MEMBER)", __FILE__, __LINE__);

if ($indexRating)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD INDEX (rating)", __FILE__, __LINE__);

if ($indexViews)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD INDEX (views)", __FILE__, __LINE__);

if ($indexcommenttotal)
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD INDEX (commenttotal)", __FILE__, __LINE__);

if ($indextotalratings)	
	db_query("ALTER TABLE {$db_prefix}gallery_pic ADD INDEX (totalratings)", __FILE__, __LINE__);	
	
	


// Add Package Servers
db_query("DELETE FROM {$db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'", __FILE__, __LINE__);
db_query("REPLACE INTO {$db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')", __FILE__, __LINE__);

// If pretty Urls exist
if (file_exists($sourcedir . '/Subs-PrettyUrls.php'))
{
	// The following is based on Pretty Url's Extra's Mod by Dannii
	
	require_once($sourcedir . '/Subs-PrettyUrls.php');
	
	//	Add these filters
	$prettyFilters = unserialize($modSettings['pretty_filters']);
	
	//	Pretty URLs for SMF Gallery Lite
	$prettyFilters['smf-gallery-lite'] = array(
		'description' => 'Rewrite SMF Gallery URLs',
		'enabled' => 0,
		'filter' => array(
			'priority' => 30,
			'callback' => 'pretty_smf_gallery_lite_filter',
		),
		'rewrite' => array(
			'priority' => 30,
			'rule' => array(
				'RewriteRule ^gallery/index\.php$ index.php?action=gallery [L,QSA]',
				'RewriteRule ^gallery/category/([^/]+)/?$ ./index.php?action=gallery;cat=$1 [L,QSA]',
				'RewriteRule ^gallery/picture/([^/]+)/?$ ./index.php?action=gallery;sa=view;id=$1 [L,QSA]',
			),
		),
		'title' => 'SMF Gallery Lite',
	);
	
	updateSettings(array('pretty_filters' => isset($smcFunc) ? serialize($prettyFilters) : addslashes(serialize($prettyFilters))));
	
	//	Update everything now
	if (function_exists('pretty_update_filters'))
		pretty_update_filters();
}

// Permissions array
$permissions = array(
	'smfgallery_view' => array(-1, 0, 2), // ALL
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