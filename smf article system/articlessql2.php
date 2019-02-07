<?php
// SMFHacks.com
// Table SQL


if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');



// Create category SQL
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}articles_cat
(ID_CAT mediumint(8) NOT NULL auto_increment,
title text NOT NULL,
seotitle tinytext,
description tinytext,
roworder mediumint(8) unsigned NOT NULL default '0',
ID_PARENT mediumint(8) NOT NULL default '0',
imageurl tinytext,
filename tinytext,
total int(11) NOT NULL default '0',
PRIMARY KEY  (ID_CAT)) Engine=MyISAM");

// Create Articles Table
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}articles
(ID_ARTICLE int(11) NOT NULL auto_increment,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
ID_CAT mediumint(8) NOT NULL default '0',
title tinytext NOT NULL,
seotitle tinytext,
description longtext,
views int(11) NOT NULL default '0',
approved tinyint(4) NOT NULL default '0',
rating int(11) NOT NULL default '0',
totalratings int(10) NOT NULL default '0',
commenttotal int(10) NOT NULL default '0',
sendemail tinyint(4) NOT NULL default '0',
date int(10) unsigned NOT NULL default '0',
modifiedTime int(10) unsigned NOT NULL default '0',
removed tinyint(4) NOT NULL default '0',
PRIMARY KEY  (ID_ARTICLE)) Engine=MyISAM");

$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}articles_page
(ID_PAGE int(11) NOT NULL auto_increment,
ID_ARTICLE int(11)  NOT NULL default '0',
roworder mediumint(8) unsigned NOT NULL default '0',
sectiontitle tinytext,
pagetext longtext,

PRIMARY KEY (ID_PAGE)) Engine=MyISAM");


$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}articles_rating
(ID int(11) NOT NULL auto_increment,
ID_ARTICLE int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
value tinyint(4) NOT NULL,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (ID)) Engine=MyISAM");


$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}articles_catperm
(ID mediumint(8) NOT NULL auto_increment,
ID_GROUP mediumint(8) NOT NULL default '0',
ID_CAT mediumint(8) NOT NULL default '0',
view tinyint(4) NOT NULL default '0',
addarticle tinyint(4) NOT NULL default '0',
editarticle tinyint(4) NOT NULL default '0',
delarticle tinyint(4) NOT NULL default '0',
ratearticle tinyint(4) NOT NULL default '0',
addcomment tinyint(4) NOT NULL default '0',
editcomment tinyint(4) NOT NULL default '0',
report tinyint(4) NOT NULL default '0',
PRIMARY KEY  (ID)) Engine=MyISAM");

$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}articles_comment(
ID_COMMENT int(11) NOT NULL auto_increment,
ID_ARTICLE int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
approved tinyint(4) NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
modified_ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
lastmodified int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (ID_COMMENT)) Engine=MyISAM");

$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}articles_creport
(ID int(11) NOT NULL auto_increment,
ID_ARTICLE int(11) NOT NULL,
ID_COMMENT int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
comment text,
date int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (ID)) Engine=MyISAM");

// Insert the settings
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings
	(variable, value)
VALUES
('smfarticles_setarticlesperpage', '10'),
('smfarticles_countsubcats', '1'),
('smfarticles_enableratings', '1'),
('smfarticles_enablecomments', '1'),
('smfarticles_disp_views', '1'),
('smfarticles_disp_rating', '1'),
('smfarticles_disp_membername', '1'),
('smfarticles_disp_date', '1'),
('smfarticles_disp_totalcomment','1'),

('smfarticles_view_description', '1'),
('smfarticles_view_views', '1'),
('smfarticles_view_rating', '1'),
('smfarticles_view_membername', '1'),
('smfarticles_view_date', '1'),
('smfarticles_allow_attached_images', '0'),
('smfarticles_images_view_article', '1'),
('smfarticles_max_num_attached', '5'),
('smfarticles_max_filesize', '5242880'),
('smfarticles_sharingicons', '1'),
('smfarticles_showrss', '1')
");

$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}articles_attachments
(ID_FILE int(11) NOT NULL auto_increment,
ID_ARTICLE int(11) NOT NULL,
ID_MEMBER mediumint(8) unsigned NOT NULL default '0',
type tinyint(4) unsigned NOT NULL default '0',
thumbnail tinytext,
filename tinytext,
date int(10) unsigned NOT NULL default '0',
filesize int(11),
views int(11),
PRIMARY KEY  (ID_FILE)) Engine=MyISAM");

// Add Package Servers
$smcFunc['db_query']('', "DELETE FROM {db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'");
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')");


// Permissions array
$permissions = array(
	'view_articles' => array(-1, 0, 2), // ALL
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