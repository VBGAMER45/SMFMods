<?php
/*
SMF Gallery Lite Edition
Version 9.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2008-2025 SMFHacks.com

############################################
License Information:
SMF Gallery is NOT free software.
This software may not be redistributed.

Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
#############################################
*/
//Install the Database tables for SMF Gallery

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


//Picture Table
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_pic(
id_picture int(11) NOT NULL auto_increment,
id_member mediumint(8) unsigned NOT NULL default '0',
date int(10) unsigned NOT NULL default '0',
title varchar(100) NOT NULL,
description text,
 views int(10) NOT NULL default '0',
 filesize int(10) NOT NULL default '0',
 height int(10) NOT NULL default '0',
 width int(10) NOT NULL default '0',
 filename tinytext,
 thumbfilename tinytext,
 commenttotal int(10) NOT NULL default '0',
 id_cat int(10) NOT NULL default '0',
 approved tinyint(4) NOT NULL default '0',
 allowcomments tinyint(4) NOT NULL default '0',
 keywords varchar(100),
 totalratings int(10) NOT NULL default '0',
rating int(10) NOT NULL default '0',
type tinyint(4) NOT NULL default '0',
user_id_cat int(10) NOT NULL default '0',
mediumfilename tinytext,
videofile tinytext,
orginalfilename varchar(255),
PRIMARY KEY  (id_picture))");


$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}gallery_pic");
$totalratings = 1;
$rating = 1;
$type = 1;
$user_id_cat = 1;
$mediumfilename = 1;
$videofile = 1;
$allowratings = 1;
$orginalfilename = 1;


while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	$row[0] = strtolower($row[0]);
	if($row[0] == 'totalratings')
		$totalratings = 0;
	if($row[0] == 'rating')
		$rating = 0;
	if($row[0] == 'type')
		$type = 0;

	if($row[0] == 'user_id_cat')
		$user_id_cat = 0;

	if($row[0] == 'mediumfilename')
		$mediumfilename = 0;
	if($row[0] == 'videofile')
		$videofile = 0;
	
	if($row[0] == 'allowratings')
		$allowratings = 0;	

	if($row[0] == 'orginalfilename')
		$orginalfilename = 0;


}

$smcFunc['db_free_result']($dbresult);

if($user_id_cat)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD user_id_cat int(10) NOT NULL default '0'");


if($totalratings)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD totalratings int(10) NOT NULL default '0'");


if($rating)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD rating int(10) NOT NULL default '0'");

if($type)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD type tinyint(4) NOT NULL default '0'");


if($mediumfilename)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD mediumfilename tinytext");
if($videofile)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD videofile tinytext");


if($allowratings)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD allowratings tinyint(4) NOT NULL default '1'");

if($orginalfilename)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD orginalfilename varchar(255)");




//Picture comments
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_comment(
id_comment int(11) NOT NULL auto_increment,
id_picture int(11) NOT NULL,
id_member mediumint(8) unsigned NOT NULL default '0',
approved tinyint(4) NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (id_comment))");

//Gallery Category
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_cat
(id_cat mediumint(8) NOT NULL auto_increment,
title varchar(100) NOT NULL,
description text,
roworder mediumint(8) unsigned NOT NULL default '0',
image varchar(255) NOT NULL,
id_parent smallint(5) unsigned NOT NULL default '0',
redirect tinyint(4) NOT NULL default '0',
PRIMARY KEY  (id_cat))");



// Gallery Category
$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}gallery_cat");
$id_parent = 1;
$redirect = 1;

while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	$row[0] = strtolower($row[0]);
	if($row[0] == 'id_parent')
		$id_parent = 0;
        
 	if($row[0] == 'redirect')
		$redirect = 0;      


}

$smcFunc['db_free_result']($dbresult);

if($id_parent)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_cat ADD id_parent smallint(5) unsigned NOT NULL default '0'");


if($redirect)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_cat ADD redirect tinyint(4) NOT NULL default '0'");


		


//Gallery Reported Images
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}gallery_report
(id int(11) NOT NULL auto_increment,
id_picture int(11) NOT NULL,
id_member mediumint(8) unsigned NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (id))");





// Insert the settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
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
");

// Indexes
$dbresult = $smcFunc['db_query']('', "SHOW INDEX FROM  {db_prefix}gallery_pic");

$indexUSER_ID_CAT = 1;
$indexID_CAT = 1;
$indexID_MEMBER = 1;
$indexRating = 1;
$indexViews = 1;
$indexcommenttotal = 1;
$indextotalratings = 1;

while ($row = $smcFunc['db_fetch_assoc']($dbresult))
{
	if (strtoupper($row['Column_name']) == 'ID_CAT')
		$indexID_CAT = 0;
	if (strtoupper($row['Column_name']) == 'USER_ID_CAT')
		$indexUSER_ID_CAT = 0;	
	if (strtoupper($row['Column_name']) == 'ID_MEMBER')
		$indexID_MEMBER = 0;	
	if ($row['Column_name'] == 'rating')
		$indexRating = 0;	
	if ($row['Column_name'] == 'views')
		$indexViews  = 0;	
	if ($row['Column_name'] == 'commenttotal')
		$indexcommenttotal = 0;	
	if ($row['Column_name'] == 'totalratings')
		$indextotalratings = 0;	
		
		
}

if ($indexID_CAT)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (ID_CAT)");

if ($indexUSER_ID_CAT)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (USER_ID_CAT)");

if ($indexID_MEMBER)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (ID_MEMBER)");
	
if ($indexRating)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (rating)");

if ($indexViews)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (views)");

if ($indexcommenttotal)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (commenttotal)");

if ($indextotalratings)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}gallery_pic ADD INDEX (totalratings)");

	





// Add Package Servers
$smcFunc['db_query']('', "DELETE FROM {db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'");
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')");






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
	global $smcFunc;

	$perm = array();

	foreach ($permissions as $permission => $default)
	{
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}permissions
			WHERE permission = {string:permission}',
			array(
				'permission' => $permission
			)
		);

		list ($num) = $smcFunc['db_fetch_row']($result);

		if ($num == 0)
		{
			foreach ($default as $grp)
				$perm[] = array($grp, $permission);
		}
	}

	if (empty($perm))
		return;

	$smcFunc['db_insert']('insert',
		'{db_prefix}permissions',
		array(
			'id_group' => 'int',
			'permission' => 'string'
		),
		$perm,
		array()
	);
}

?>