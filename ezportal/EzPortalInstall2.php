<?php
/*
EzPortal
Version 5.5
by:vbgamer45
https://www.ezportal.com
Copyright 2010-2022 http://www.samsonsoftware.com
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
  require_once(dirname(__FILE__) . '/SSI.php');
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
  die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

$UTFData = '';

$modSettings['disableQueryCheck'] = 1;

$result = $smcFunc['db_query']('', "
   SELECT
   	value
   FROM {db_prefix}settings
   WHERE variable = 'global_character_set' AND value = 'UTF-8'");

// If UTF8 found in the settings make the table UTF8!
if ($smcFunc['db_num_rows']($result) > 0)
	$UTFData = ' CHARACTER SET utf8';

// EzPortal Settings Table
 $smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_settings (
  variable tinytext NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (variable(30))
) Engine=MyISAM $UTFData");


$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}ezp_settings
	(variable, value)
VALUES
('ezp_allowstats', '1'),
('ezp_portal_enable', '1'),
('ezp_portal_homepage_title', ''),
('ezp_shoutbox_enable','1'),
('ezp_shoutbox_showdate','1'),
('ezp_shoutbox_archivehistory','1'),
('ezp_shoutbox_showbbcselect','0'),
('ezp_hide_edit_delete','0'),
('ezp_shoutbox_hidesays','0'),
('ezp_shoutbox_hidedelete','0'),
('ezp_shoutbox_history_number','25'),
('ezp_shoutbox_showbbc','0'),
('ezp_shoutbox_showsmilies','0'),
('ezp_shoutbox_refreshseconds','0'),
('ezp_disable_tinymce_html','0'),
('ezp_copyrightkey', ''),
('ezp_disableblocksinadmin', '1'),
('ezp_disablemobiledevices', '1'),
('ezp_pages_seourls', '0')
");

$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ezp_copyrightkey', '')");
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}settings VALUES ('ezp_responsivemode', '0')");



$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_visible_actions (
  action tinytext NOT NULL,
  title text NOT NULL,
  is_mod tinyint default 0,
  PRIMARY KEY (action(10))
) Engine=MyISAM $UTFData");


$smcFunc['db_query']('', "DELETE FROM {db_prefix}ezp_visible_actions  WHERE title = 'Calender'");

$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}ezp_visible_actions
	(action, title,is_mod)
VALUES
('help', 'Help',0),
('search', 'Search',0),
('profile', 'Profile',0),
('pm', 'My Messages',0),
('mlist', 'Members',0),
('unread', 'Unread Posts',0),
('unreadreplies', 'Unread Replies',0),
('login', 'Login',0),
('post', 'Posting Page',0),
('register', 'Register',0),
('calendar', 'Calendar',0),
('stats', 'Stats',0),
('who', 'Who is Online',0),
('admin', 'Admin',0),
('gallery', '<a href=\"https://www.smfhacks.com/smf-gallery.php\" target=\"blank\">SMF Gallery</a>',1),
('arcade', 'SMF Arcade',1),
('links', 'SMF Links',1),
('downloads', '<a href=\"https://www.smfhacks.com/download-system-pro.php\" target=\"blank\">Downloads System</a>',1),
('classifieds', '<a href=\"https://www.smfhacks.com/smf-classifieds.php\" target=\"blank\">SMF Classifieds</a>',1),
('store', '<a href=\"https://www.smfhacks.com/smf-store.php\" target=\"blank\">SMF Store</a>',1)
");



// EzPortal Page table
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_page(
id_page int(11) NOT NULL auto_increment,
date int(10) unsigned NOT NULL default '0',
title tinytext,
description text,
content longtext,
metatags longtext,
views int(10) NOT NULL default '0',
permissions text,
is_html tinyint(1) default 0,
showinmenu tinyint(1) default 0,
menutitle tinytext,
icon tinytext,
bbc tinyint(1) default 0,
PRIMARY KEY  (id_page)) Engine=MyISAM $UTFData");

$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}ezp_page");
$metatags =  1;
$showinmenu = 1;
$menutitle = 1;
$icon = 1;
$bbc = 1;


while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if ($row[0] == 'metatags')
		$metatags = 0;
		
	if ($row[0] == 'showinmenu')
		$showinmenu = 0;
		
	if ($row[0] == 'menutitle')
		$menutitle = 0;
		
	if ($row[0] == 'icon')
		$icon = 0;
		
	if ($row[0] == 'bbc')
		$bbc = 0;
}
$smcFunc['db_free_result']($dbresult);

if ($metatags)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_page ADD metatags longtext");

if ($showinmenu)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_page ADD showinmenu tinyint(1) default 0");

if ($menutitle)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_page ADD menutitle tinytext");


if ($icon)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_page ADD icon tinytext");

if ($bbc)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_page ADD bbc tinyint(1) default 0");

	
// Blocks Table
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_blocks(
id_block int(11) NOT NULL auto_increment,
blocktype tinytext,
blocktitle tinytext,
blockdescription text,
blockdata longtext,

blockauthor tinytext,
blockwebsite tinytext,
blockversion tinytext,
forumversion tinytext,

can_cache tinyint(1) default 0,
data_editable tinyint(1) default 0,
no_delete tinyint(1) default 0,

PRIMARY KEY  (id_block)) Engine=MyISAM $UTFData");
$firstInstall = false;
$dbresult = $smcFunc['db_query']('', "SELECT COUNT(*) AS total FROM {db_prefix}ezp_blocks");
$totalRow = $smcFunc['db_fetch_assoc']($dbresult);

if ($totalRow['total']  < 2)
{
	// Insert Default Blocks
	// Types BUILTIN, userblock(HTML,PHP)
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_blocks
					(blocktitle, blocktype,blockversion,blockauthor,blockwebsite,can_cache,data_editable,no_delete)
				VALUES ('HTML ezBlock', 'HTML','1.0','EzPortal','https://www.ezportal.com',1,1,1)");

	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_blocks
					(blocktitle, blocktype,blockversion,blockauthor,blockwebsite,can_cache,data_editable,no_delete)
				VALUES ('PHP ezBlock', 'PHP','1.0','EzPortal','https://www.ezportal.com',0,1,1)");
	$firstInstall = true;
}




// Block Parameters
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_block_parameters(
id_parameter int(11) NOT NULL auto_increment,
id_block int(11),
title tinytext,
parameter_name tinytext,
parameter_type tinytext,
defaultvalue text,
required tinyint(1) default 0,
id_order int(11) default 0,
PRIMARY KEY  (id_parameter)) Engine=MyISAM $UTFData");


$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}ezp_block_parameters");
$id_order =  1;
while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if ($row[0] == 'id_order')
		$id_order = 0;

}
$smcFunc['db_free_result']($dbresult);

if ($id_order)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_block_parameters ADD id_order int(11) default 0");


// Block Layout
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_block_layout(
id_layout int(11) NOT NULL auto_increment,
id_column int(11),
id_block int(11),
id_order int(11),

customtitle tinytext,
permissions tinytext,
can_collapse tinyint(1) default 0,
active tinyint(1) default 0,
visibileactions text,
visibileboards text,
visibileareascustom text,
blockmanagers tinytext,
blockdata longtext,
id_icon int(11) default 0,
visibilepages text,
hidetitlebar  tinyint(1) default 0,
hidemobile tinyint(1) default 0,
showonlymobile tinyint(1) default 0,
block_header_class tinytext,
block_body_class tinytext,
PRIMARY KEY  (id_layout)) Engine=MyISAM $UTFData");

$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}ezp_block_layout");
$id_icon =  1;
$visibilepages = 1;
$hidetitlebar = 1;
$hidemobile = 1;
$showonlymobile = 1;
$block_header_class  = 1;
$block_body_class  = 1;
while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if ($row[0] == 'id_icon')
		$id_icon = 0;
	if($row[0] == 'visibilepages')
		$visibilepages = 0;

	if($row[0] == 'hidetitlebar')
		$hidetitlebar = 0;
		
	if($row[0] == 'hidemobile')
		$hidemobile = 0;
		
	if($row[0] == 'showonlymobile')
		$showonlymobile = 0;	
		
	if($row[0] == 'block_header_class')
		$block_header_class = 0;

	if($row[0] == 'block_body_class')
		$block_body_class = 0;
}
$smcFunc['db_free_result']($dbresult);

if ($id_icon)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_block_layout ADD id_icon int(11) default 0");

if ($visibilepages)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_block_layout ADD visibilepages text");

if ($hidetitlebar)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_block_layout ADD hidetitlebar  tinyint(1) default 0");

if ($hidemobile)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_block_layout ADD hidemobile tinyint(1) default 0");

if ($showonlymobile)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_block_layout ADD showonlymobile tinyint(1) default 0");

if ($block_header_class)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_block_layout ADD block_header_class tinytext");

if ($block_body_class)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_block_layout ADD block_body_class tinytext");

// Layout Columns
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_columns(
id_column int(11) NOT NULL auto_increment,
column_title tinytext,
can_collapse tinyint(1) default 0,
active tinyint(1) default 0,
column_width int(5),
column_percent int(5) default 0,
column_order int(11),
visibileactions text,
visibileboards text,
visibileareascustom text,
visibilepages text,
sticky tinyint(1) default 0,    
PRIMARY KEY  (id_column)) Engine=MyISAM $UTFData");


$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}ezp_columns");
$column_percent =  1;
$sticky = 1;
while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if ($row[0] == 'column_percent')
		$column_percent = 0;
	if($row[0] == 'visibilepages')
		$visibilepages = 0;
	if($row[0] == 'sticky')
		$sticky = 0;
}
$smcFunc['db_free_result']($dbresult);

if ($column_percent)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_columns ADD column_percent int(5) default 0");

if ($visibilepages)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_columns ADD visibilepages text");

if ($sticky)
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_columns ADD sticky tinyint(1) default 0");


$dbresult = $smcFunc['db_query']('', "SELECT COUNT(*) AS total FROM {db_prefix}ezp_columns");
$totalRow = $smcFunc['db_fetch_assoc']($dbresult);

if ($totalRow['total']  < 3)
{
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_columns
					(column_title, can_collapse, active, column_width, column_order,column_percent)
				VALUES ('Left', 1,1,'150',1,15)");

	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_columns
					(column_title, can_collapse, active, column_width, column_order,column_percent)
				VALUES ('Center', 1,1,'600',2,68)");

	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_columns
					(column_title, can_collapse, active, column_width, column_order,column_percent)
				VALUES ('Right', 1,1,'150',3,15)");
}


if ($totalRow['total']  < 5)
{
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_columns
					(column_title, can_collapse, active, column_width, column_order,column_percent)
				VALUES ('Top', 1,0,'9',4,100)");

	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_columns
					(column_title, can_collapse, active, column_width, column_order,column_percent)
				VALUES ('Bottom', 1,0,'0',5,100)");

}


// Block Values
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_block_parameters_values(
id_value int(11) NOT NULL auto_increment,
id_parameter int(11),
id_layout int(11),
data text,
PRIMARY KEY  (id_value)) Engine=MyISAM $UTFData");


// Add Package Servers
$smcFunc['db_query']('', "DELETE FROM {db_prefix}package_servers WHERE url = 'http://www.smfhacks.com'");
$smcFunc['db_query']('', "REPLACE INTO {db_prefix}package_servers (name,url) VALUES ('SMFHacks.com Modification Site', 'http://www.smfhacks.com')");


// Shoutbox
// Block Values
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_shoutbox(
id_shout int(11) NOT NULL auto_increment,
id_member mediumint(8) unsigned NOT NULL default '0',
date int(10) unsigned NOT NULL default '0',
shout text,
ipaddress tinytext,
PRIMARY KEY  (id_shout)) Engine=MyISAM $UTFData");

// For storing select paramter values
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_paramaters_select(
id_select int(11) NOT NULL auto_increment,
id_block int(11),
selectvalue tinytext,
selecttext tinytext,
PRIMARY KEY (id_select)) Engine=MyISAM $UTFData");




// Upgrade the paratemeter select table
$dbresult = $smcFunc['db_query']('', "SHOW COLUMNS FROM {db_prefix}ezp_paramaters_select");
$id_parameter =  1;
while ($row = $smcFunc['db_fetch_row']($dbresult))
{
	if($row[0] == 'id_parameter')
		$id_parameter =0;

}
$smcFunc['db_free_result']($dbresult);

if ($id_parameter)
{
	$smcFunc['db_query']('', "ALTER TABLE {db_prefix}ezp_paramaters_select ADD id_parameter int(11) NOT NULL default '0'");

	// Fix all the old parameters
	$result = $smcFunc['db_query']('', "
	SELECT
		id_block, id_parameter
	FROM {db_prefix}ezp_block_parameters
	WHERE parameter_type = 'select'");
	while($row = $smcFunc['db_fetch_assoc']($result))
	{
		$smcFunc['db_query']('', "
		UPDATE {db_prefix}ezp_paramaters_select
	    SET id_parameter = " . $row['id_parameter'] . ' WHERE id_block = ' . $row['id_block'] );
	}
	$smcFunc['db_free_result']($result);

}

$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_icons (
  id_icon int(11) NOT NULL auto_increment,
  icon tinytext,
  PRIMARY KEY (id_icon)
) Engine=MyISAM $UTFData");

$result = $smcFunc['db_query']('', "SELECT COUNT(*) AS icontotal FROM {db_prefix}ezp_icons
  ");
$iconRowCount =$smcFunc['db_fetch_assoc']($result);

if ($iconRowCount['icontotal'] == 0)
$smcFunc['db_query']('', "INSERT IGNORE INTO {db_prefix}ezp_icons
	(icon)
VALUES
('application.png'),
('award_star_gold_1.png'),
('brick.png'),
('book.png'),
('bug.png'),
('cake.png'),
('calculator.png'),
('calendar.png'),
('camera.png'),
('cart.png'),
('chart_bar.png'),
('comments.png'),
('computer.png'),
('drink.png'),
('email.png'),
('feed.png'),
('female.png'),
('film.png'),
('help.png'),
('ipod.png'),
('information.png'),
('male.png'),
('money.png'),
('music.png'),
('new.png'),
('phone.png'),
('photo.png'),
('rss.png'),
('sitemap.png'),
('sport_8ball.png'),
('sport_basketball.png'),
('sport_football.png'),
('sport_golf.png'),
('sport_raquet.png'),
('sport_soccer.png'),
('sport_tennis.png'),
('star.png'),
('television.png'),
('user.png'),
('user_female.png'),
('vcard.png'),
('webcam.png'),
('world.png')
");


// Menu ezBlock
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_menu
(
id_menu int(11) NOT NULL auto_increment,
id_layout int(11),
id_order int(11),
title tinytext,
linkurl tinytext,
permissions tinytext,
enabled tinyint(1) default 1,
newwindow tinyint(1) default 0,
PRIMARY KEY  (id_menu)) Engine=MyISAM $UTFData");


// RSS Feed Table
$smcFunc['db_query']('', "CREATE TABLE IF NOT EXISTS {db_prefix}ezp_rss_cache
(
id_rss int(11) NOT NULL auto_increment,
id_layout int(11),
lastupdated int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (id_rss)) Engine=MyISAM $UTFData");


// Insert each User Block if it does not exist
InsertUserEzBlocks();

UpdateEzBlocks();

if ($firstInstall == true)
{


$result = $smcFunc['db_query']('', "SELECT id_block FROM {db_prefix}ezp_blocks WHERE blocktitle = 'UserBox'");
$blockIDRow = $smcFunc['db_fetch_assoc']($result);

$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_layout (id_layout, id_column, id_block, id_order, customtitle, permissions, can_collapse, active, visibileactions, visibileboards, visibileareascustom, blockmanagers, blockdata, id_icon, visibilepages, hidetitlebar) VALUES (1, 1, " . $blockIDRow['id_block'] . ", 1, 'User', '-1,0,1,2', 1, 1, '', '', '', '', 'EzBlockLoginBoxBlock', 0, '', 0)");

$result = $smcFunc['db_query']('', "SELECT id_block FROM {db_prefix}ezp_blocks WHERE blocktitle = 'Stats ezBlock'");
$blockIDRow = $smcFunc['db_fetch_assoc']($result);
$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_layout (id_layout, id_column, id_block, id_order, customtitle, permissions, can_collapse, active, visibileactions, visibileboards, visibileareascustom, blockmanagers, blockdata, id_icon, visibilepages, hidetitlebar) VALUES (2, 1, " . $blockIDRow['id_block'] . ", 2, 'Stats ezBlock', '-1,0,1,2', 1, 1, '', '', '', '', 'EzBlockStatsBox', 0, '', 0)");
$result = $smcFunc['db_query']('', "SELECT id_block FROM {db_prefix}ezp_blocks WHERE blocktitle = 'Recent Topics ezBlock'");
$blockIDRow = $smcFunc['db_fetch_assoc']($result);
$recentBlockID = $blockIDRow['id_block'];
$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_layout (id_layout, id_column, id_block, id_order, customtitle, permissions, can_collapse, active, visibileactions, visibileboards, visibileareascustom, blockmanagers, blockdata, id_icon, visibilepages, hidetitlebar) VALUES (3, 3, " . $blockIDRow['id_block'] . ", 1, 'Recent Topics', '-1,0,1,2', 1, 1, '', '', '', '', 'EzBlockRecentTopicsBlock', 0, '', 0)");
$result = $smcFunc['db_query']('', "SELECT id_block FROM {db_prefix}ezp_blocks WHERE blocktitle = 'ParseBBC ezBlock'");
$blockIDRow = $smcFunc['db_fetch_assoc']($result);
$parsebbcBlockID = $blockIDRow['id_block'];
$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_layout (id_layout, id_column, id_block, id_order, customtitle, permissions, can_collapse, active, visibileactions, visibileboards, visibileareascustom, blockmanagers, blockdata, id_icon, visibilepages, hidetitlebar) VALUES (4, 2, " . $blockIDRow['id_block'] . ", 1, 'ParseBBC ezBlock', '-1,0,1,2', 1, 1, '', '', 'portal', '', 'EzBlockParseBBCBlock', 0, '', 0)");



// Parameters
$result = $smcFunc['db_query']('', "SELECT id_parameter FROM {db_prefix}ezp_block_parameters WHERE id_block = $recentBlockID AND parameter_name = 'numTopics'");
$paramIDRow = $smcFunc['db_fetch_assoc']($result);
$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_parameters_values (id_value, id_parameter, id_layout, `data`) VALUES (1,  " . $paramIDRow['id_parameter'] . ", 3, '10')");


$result = $smcFunc['db_query']('', "SELECT id_parameter FROM {db_prefix}ezp_block_parameters WHERE id_block = $recentBlockID AND parameter_name = 'format'");
$paramIDRow = $smcFunc['db_fetch_assoc']($result);
$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_parameters_values (id_value, id_parameter, id_layout, `data`) VALUES (2,  " . $paramIDRow['id_parameter'] . ", 3, 'vertical')");


$result = $smcFunc['db_query']('', "SELECT id_parameter FROM {db_prefix}ezp_block_parameters WHERE id_block = $recentBlockID AND parameter_name = 'showcolor'");
$paramIDRow = $smcFunc['db_fetch_assoc']($result);

$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_parameters_values (id_value, id_parameter, id_layout, `data`) VALUES (3,  " . $paramIDRow['id_parameter'] . ", 3, 'true')");


$result = $smcFunc['db_query']('', "SELECT id_parameter FROM {db_prefix}ezp_block_parameters WHERE id_block = $parsebbcBlockID  AND parameter_name = 'bbctext'");
$paramIDRow = $smcFunc['db_fetch_assoc']($result);

$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_parameters_values (id_value, id_parameter, id_layout, `data`) VALUES (4,  " . $paramIDRow['id_parameter'] . ", 4, 'Welcome to your new install of ezPortal!

You can control which blocks are shown in the ezBlockManager.

Forum refers to your main board index.
Portal is the main homepage of your site. This block for instance is only shown on the portal page.


If you have any questions or need help post in our support forums at [url]https://www.ezportal.com[/url]')");
global $ezpSettings;
$ezpSettings['ezp_portal_enable'] =1;



updateSettings(array('settings_updated' => time()));
}


if (empty($modSettings['ezportal_menucount']))
	updateSettings(array('ezportal_menucount' => 0));

// Ping EzPortal about new ezportal setup
global $boardurl;
// Connect to EzPortal.com
$fp = @fsockopen("www.ezportal.com", 80, $errno, $errstr);

// Check if we have a valid connection to ezportal.com
if ($fp)
{
	// Setup the request
	$data = "GET /stats/collector.php?site=" . base64_encode($boardurl) . " HTTP/1.1\r\n";
	$data .= "Host: www.ezportal.com\r\n";
	$data .= "Connection: Close\r\n\r\n";
	fwrite($fp, $data);

	$result = '';
	while (!feof($fp))
		$result .= fgets($fp, 128);

	// Close the connection
	fclose($fp);
}

function InsertUserEzBlocks()
{
	global $smcFunc;

// Check if user functions exist
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		blocktype,blockdata
	FROM {db_prefix}ezp_blocks
	WHERE  blocktype = 'builtin'
	");
	$builtinArray = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		$builtinArray[] = $row['blockdata'];
	$smcFunc['db_free_result']($dbresult);

	$ezBlocksInstall = array();

	// Board News
	$paramArray = array();
	$paramArray[] = array('name' => 'board','title' => 'Select Board for News','defaultvalue' => 0,'parameter_type' => 'multiboardselect', 'required' => 1);
	$paramArray[] = array('name' => 'limit','title' => 'Number of News items to Show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);

	$ezBlocksInstall[] = array('title' => 'Board News ezBlock','description'  => '', 'blockdata' => 'EzBlockBoardNewsBlock', 'version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// User Box
	$paramArray = array();
	$ezBlocksInstall[] = array('title' => 'UserBox','description' => '','blockdata' => 'EzBlockLoginBoxBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);
	$paramArray[] = array('name' => 'redirectUrl','title' => 'Login Redirect url:','defaultvalue' => '','parameter_type' => 'string', 'required' => 0);
	// Poll
	$paramArray = array();
	$paramArray[] = array('name' => 'pollTopicID','title' => 'Poll Topic ID','defaultvalue' => 0,'parameter_type' => 'int', 'required' => 0);
	$paramArray[] = array('name' => 'board','title' => 'Boards for Random Poll','defaultvalue' => 0,'parameter_type' => 'multiboardselect', 'required' => 0);
	$ezBlocksInstall[] = array('title' => 'Poll ezBlock','description' => '','blockdata' => 'EzBlockPollBlock','version' => '1.1','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// Recent Posts
	$paramArray = array();
	$paramArray[] = array('name' => 'numPosts','title' => 'Number of Posts to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'format','title' => 'Format','defaultvalue' => 'vertical','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'vertical,horizontal');
	$paramArray[] = array('name' => 'showcolor','title' => 'Show Member Link Color','defaultvalue' => 'false','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'true,false');
	$paramArray[] = array('name' => 'excludeboards','title' => 'Exclude Boards','defaultvalue' => 0,'parameter_type' => 'multiboardselect', 'required' => 0);
	$ezBlocksInstall[] = array('title' => 'Recent Posts ezBlock','description' => '','blockdata' => 'EzBlockRecentPostsBlock','version' => '1.2','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// Recent Topics
	$paramArray = array();
	$paramArray[] = array('name' => 'numTopics','title' => 'Number of Topics to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'format','title' => 'Format','defaultvalue' => 'vertical','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'vertical,horizontal');
	$paramArray[] = array('name' => 'showcolor','title' => 'Show Member Link Color','defaultvalue' => 'false','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'true,false');
	$paramArray[] = array('name' => 'excludeboards','title' => 'Exclude Boards','defaultvalue' => 0,'parameter_type' => 'multiboardselect', 'required' => 0);
	$ezBlocksInstall[] = array('title' => 'Recent Topics ezBlock','description' => '','blockdata' => 'EzBlockRecentTopicsBlock','version' => '1.2','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// Recent Webpages
	$paramArray = array();
	$paramArray[] = array('name' => 'numToShow','title' => 'Number of WebPages to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
	$ezBlocksInstall[] = array('title' => 'Recent Webpages ezBlock','description' => '','blockdata' => 'EzBlockRecentWebPagesBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);
	// Search
	$paramArray = array();
	$ezBlocksInstall[] = array('title' => 'Search ezBlock','description' => '','blockdata' => 'EzBlockSearchBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);
	// Theme Select
	$paramArray = array();
	$ezBlocksInstall[] = array('title' => 'Theme Select ezBlock','description' => '','blockdata' => 'EzBlockThemeSelect','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// Who's Online
	$paramArray = array();
	$ezBlocksInstall[] = array('title' => 'Members Online','description' => '','blockdata' => 'EzBlockWhoIsOnline','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// ShoutBox
	$paramArray = array();
	$paramArray[] = array('name' => 'numberofShouts','title' => 'Number of Shouts to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
	$ezBlocksInstall[] = array('title' => 'Shoutbox','description' => '','blockdata' => 'EzBlockShoutBoxBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// SMF Arcade
	$paramArray = array();
	$paramArray[] = array('name' => 'type','title' => 'Arcade Block Type','defaultvalue' => 'random','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'random,latestscores');
	$paramArray[] = array('name' => 'count','title' => 'Number of scores to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
	$ezBlocksInstall[] = array('title' => 'SMF Arcade ezBlock','description' => '','blockdata' => 'EzBlockSMFArcadeBlock','version' => '1.1','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);
	// SMF Articles
	$paramArray = array();
	$paramArray[] = array('name' => 'articles','title' => 'Number of Articles to show','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'rows','title' => 'Number of items per row','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'type','title' => 'Type','defaultvalue' => 'recent','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'recent,viewed,mostcomments,toprated');
	$ezBlocksInstall[] = array('title' => 'SMF Articles ezBlock','description' => '','blockdata' => 'EzBlockSMFArticlesEzBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// SMF Gallery Block
	$paramArray = array();
	$paramArray[] = array('name' => 'images','title' => 'Number of Images to show','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'rows','title' => 'Number of items per row','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'category','title' => 'Gallery Category ID to display','defaultvalue' => 0,'parameter_type' => 'int', 'required' => 0);

	$paramArray[] = array('name' => 'mediatype','title' => 'Media Type','defaultvalue' => 'both','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'both,onlyphotos,onlyaudiovideos');

	$ezBlocksInstall[] = array('title' => 'SMF Gallery ezBlock','description' => '','blockdata' => 'EzBlockGalleryBlock','version' => '1.2','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);
	// SMF Gallery Random Image
	$paramArray = array();
	$paramArray[] = array('name' => 'images','title' => 'Number of Images to show','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'rows','title' => 'Number of items per row','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	//$paramArray[] = array('name' => 'type','title' => 'Type','defaultvalue' => 'random','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'recent,viewed,mostcomments,toprated');
	$paramArray[] = array('name' => 'mediatype','title' => 'Media Type','defaultvalue' => 'both','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'both,onlyphotos,onlyaudiovideos');

	$ezBlocksInstall[] = array('title' => 'SMF Gallery Random Image','description' => '','blockdata' => 'EzBlockGalleryRandomImage','version' => '1.1','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// SMF Links
	$paramArray = array();
	$paramArray[] = array('name' => 'links','title' => 'Number of Links to show','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$ezBlocksInstall[] = array('title' => 'SMF Links ezBlock','description' => '','blockdata' => 'EzBlockLinksBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// SMF Classifieds
	$paramArray = array();
	$paramArray[] = array('name' => 'listings','title' => 'Number of Listings to show','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'rows','title' => 'Number of items per row','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'type','title' => 'Type','defaultvalue' => 'recent','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'recent,viewed,mostcomments,featured,random');

	$ezBlocksInstall[] = array('title' => 'SMF Classifieds ezBlock','description'  => 'ezBlock for SMF Classifieds','blockdata' => 'EzBlockClassifiedsBlock','version' => '1.1','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// Download System
	$paramArray = array();
	$paramArray[] = array('name' => 'files','title' => 'Number of Files to show','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'rows','title' => 'Number of items per row','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'type','title' => 'Type','defaultvalue' => 'recent','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'random,recent,viewed,mostcomments,toprated,downloads');

	$ezBlocksInstall[] = array('title' => 'Download System ezBlock','description' => '','blockdata' => 'EzBlockDownloadsBlock','version' => '1.2','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// SMF Store EzBlock
	$paramArray = array();
	$paramArray[] = array('name' => 'products','title' => 'Number of Products to show','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'rows','title' => 'Number of items per row','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'type','title' => 'Type','defaultvalue' => 'recent','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'recent,viewed,mostcomments,toprated,random');

	$ezBlocksInstall[] = array('title' => 'SMF Store ezBlock','description' => '','blockdata' => 'EzBlockStoreBlock','version' => '1.1','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// Tagging System
	$paramArray = array();
	$ezBlocksInstall[] = array('title' => 'Tag Cloud','description' => '','blockdata' => 'EzBlockTagCloudBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);


	// Recent Member's ezBlock
	$paramArray = array();

	$paramArray[] = array('name' => 'numShow','title' => 'Number of members to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'format','title' => 'Format','defaultvalue' => 'vertical','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'vertical,horizontal');
	$paramArray[] = array('name' => 'showcolor','title' => 'Show Member Link Color' ,'defaultvalue' => 'false','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'true,false');
	$paramArray[] = array('name' => 'showavatar','title' => 'Show Member Avatar' ,'defaultvalue' => 'false','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'true,false');

	$ezBlocksInstall[] = array('title' => 'Recent Members ezBlock','description' => '','blockdata' => 'EzBlockRecentMembersBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// Top Posters ezBlock
	$paramArray = array();

	$paramArray[] = array('name' => 'numShow','title' => 'Number of posters to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'format','title' => 'Format','defaultvalue' => 'vertical','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'vertical,horizontal');
	$paramArray[] = array('name' => 'showcolor','title' => 'Show Member Link Color' ,'defaultvalue' => 'false','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'true,false');
	$paramArray[] = array('name' => 'showavatar','title' => 'Show Member Avatar' ,'defaultvalue' => 'false','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'true,false');

	$ezBlocksInstall[] = array('title' => 'Top Posters ezBlock','description' => '','blockdata' => 'EzBlockTopPosterBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);


	// Menu ezBlock
	$paramArray = array();
	$paramArray[] = array('name' => 'format','title' => 'Format','defaultvalue' => 'vertical','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'vertical,horizontal');
	$ezBlocksInstall[] = array('title' => 'Menu ezBlock','description' => '','blockdata' => 'EzBlockMenuBlock','version' => '1.1','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// RSS ezBlock
	$paramArray = array();
	$paramArray[] = array('name' => 'feedurl','title' => 'Feed Url','defaultvalue' => '','parameter_type' => 'string', 'required' => 1);
	$paramArray[] = array('name' => 'numShow','title' => 'Number of Items to show','defaultvalue' => 5,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'showBody','title' => 'ShowBody','defaultvalue' => 'false','parameter_type' => 'select', 'required' => 0, 'selectvalues' => 'true,false');
	$paramArray[] = array('name' => 'updatetime','title' => 'Number of minutes to recheck feed','defaultvalue' => 15,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'lastupdate','title' => 'Last Update Time','defaultvalue' => 15,'parameter_type' => 'hidden', 'required' => 0);
	$paramArray[] = array('name' => 'rssdata','title' => 'RSS Data','defaultvalue' => 0,'parameter_type' => 'hidden', 'required' => 0);
	$paramArray[] = array('name' => 'newwindow','title' => 'Open in new window','defaultvalue' => 0,'parameter_type' => 'checkbox', 'required' => 0);

	$ezBlocksInstall[] = array('title' => 'RSS ezBlock','description' => '','blockdata' => 'EzBlockRSSBlock','version' => '1.1','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// Calender ezBlock
	$paramArray = array();
//$ezBlocksInstall[] = array('title' => 'Calender ezBlock','description' => '','blockdata' => 'EzBlockCalenderBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);

	// ParseBBC ezBlock
	$paramArray = array();
	$paramArray[] = array('name' => 'bbctext','title' => 'BBC Text','defaultvalue' => '','parameter_type' => 'bbc', 'required' => 1);
	$ezBlocksInstall[] = array('title' => 'ParseBBC ezBlock','description' => '','blockdata' => 'EzBlockParseBBCBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'data_editable' => 0, 'parameters' => $paramArray);


	// Stats EzBlock
	$paramArray = array();
	$ezBlocksInstall[] = array('title' => 'Stats ezBlock','description' => '','blockdata' => 'EzBlockStatsBox','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'data_editable' => 0, 'parameters' => $paramArray);

	// Ad Seller Pro EzBlock
	$paramArray = array();
	$paramArray[] = array('name' => 'locationid','title' => 'Location ID:','defaultvalue' => 0,'parameter_type' => 'int', 'required' => 1);
	$ezBlocksInstall[] = array('title' => 'Ad Seller Pro ezBlock','description' => '','blockdata' => 'EzBlockAdSellerPro','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'data_editable' => 0, 'parameters' => $paramArray);


	// Top Topics
	$paramArray = array();
	$paramArray[] = array('name' => 'numTopics','title' => 'Number of Topics to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'type','title' => 'Type','defaultvalue' => 'vertical','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'replies,views');
	$ezBlocksInstall[] = array('title' => 'Top Topics','description' => '','blockdata' => 'EzBlockTopTopicsBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'data_editable' => 0, 'parameters' => $paramArray);

	// Top Boards
	$paramArray = array();
	$ezBlocksInstall[] = array('title' => 'Top Boards','description' => '','blockdata' => 'EzBlockTopBoards','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'data_editable' => 0, 'parameters' => $paramArray);

	// Todays Birthdays
	$paramArray = array();
	$ezBlocksInstall[] = array('title' => 'Todays Birthdays','description' => '','blockdata' => 'EzBlockBirthDaysBlock','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'data_editable' => 0, 'parameters' => $paramArray);


	// Twitter Tweets
    $paramArray = array();
	$paramArray[] = array('name' => 'twitterusername','title' => 'Twitter Username','defaultvalue' => '','parameter_type' => 'string', 'required' => 1);
	$paramArray[] = array('name' => 'numTweets','title' => 'Number of Tweets to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
	$ezBlocksInstall[] = array('title' => 'Twitter Tweets','description' => '','blockdata' => 'EzBlockTwitterTweets','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'data_editable' => 0, 'parameters' => $paramArray);



	// Facebook Comments
	$paramArray = array();
	$paramArray[] = array('name' => 'applicationid','title' => 'Facebook Aplication ID','defaultvalue' => '','parameter_type' => 'string', 'required' => 1);
	$paramArray[] = array('name' => 'numPosts','title' => 'Number of Comments to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'commentwidth','title' => 'Comment Box Width','defaultvalue' => 500,'parameter_type' => 'int', 'required' => 1);
	$ezBlocksInstall[] = array('title' => 'Facebook Comments','description' => '','blockdata' => 'EzBlockFacebookComments','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'data_editable' => 0, 'parameters' => $paramArray);




	// Calendar
	$paramArray = array();
	$paramArray[] = array('name' => 'showBirthdays','title' => 'Show Birthdays','defaultvalue' => 0,'parameter_type' => 'checkbox', 'required' => 0);
    $paramArray[] = array('name' => 'showEvents','title' => 'Show Events','defaultvalue' => 0,'parameter_type' => 'checkbox', 'required' => 0);
    $paramArray[] = array('name' => 'showHolidays','title' => 'Show Holidays','defaultvalue' => 0,'parameter_type' => 'checkbox', 'required' => 0);
    $paramArray[] = array('name' => 'showPrevNext','title' => 'Show Previous/Next','defaultvalue' => 0,'parameter_type' => 'checkbox', 'required' => 0);
	$paramArray[] = array('name' => 'size','title' => 'Size' ,'defaultvalue' => 'small','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'small,large');

	$ezBlocksInstall[] = array('title' => 'Calendar ezBlock','description' => '','blockdata' => 'EzBlockCalendar','version' => '1.1','blockauthor' => 'EzPortal','blockwebsite'=>'http://www.ezportal.com','no_delete' => 1, 'parameters' => $paramArray);


	// Bluesky Feeds
    $paramArray = array();
	$paramArray[] = array('name' => 'blueskyusername','title' => 'Bluesky Username','defaultvalue' => '','parameter_type' => 'string', 'required' => 1);
	$paramArray[] = array('name' => 'numposts','title' => 'Number of Posts to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
	$paramArray[] = array('name' => 'darkmode','title' => 'Dark Mode','defaultvalue' => 'false','parameter_type' => 'select', 'required' => 0, 'selectvalues' => 'false,true');
	$paramArray[] = array('name' => 'loadmore','title' => 'Load More','defaultvalue' => 'false','parameter_type' => 'select', 'required' => 0, 'selectvalues' => 'false,true');
	$paramArray[] = array('name' => 'linkimage','title' => 'Link Image','defaultvalue' => 'false','parameter_type' => 'select', 'required' => 0, 'selectvalues' => 'false,true');
	$paramArray[] = array('name' => 'disablestyles','title' => 'Disable Styles','defaultvalue' => 'false','parameter_type' => 'select', 'required' => 0, 'selectvalues' => 'false,true');

	$ezBlocksInstall[] = array('title' => 'Bluesky Feed','description' => '','blockdata' => 'EzBlockBlueSkyFeed','version' => '1.0','blockauthor' => 'EzPortal','blockwebsite'=>'https://www.ezportal.com','no_delete' => 1, 'data_editable' => 0, 'parameters' => $paramArray);


	foreach($ezBlocksInstall as $installBlock)
	{
		// Check if ezBlock already exists if so skip it
		if (in_array($installBlock['blockdata'],$builtinArray))
			continue;

		$data_editable = 0;
		if (isset($installBlock['data_editable']))
			$data_editable = (int) $installBlock['data_editable'];


		$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_blocks
					(blocktitle, blockdata, blocktype,blockversion,blockauthor,blockwebsite,can_cache,data_editable,no_delete)
				VALUES ('" . $installBlock['title']. "','" . $installBlock['blockdata']. "', 'builtin','" . $installBlock['version']. "','" . $installBlock['blockauthor']. "','" . $installBlock['blockwebsite']. "',0," . $data_editable  . "," . $installBlock['no_delete']. ")");

		// EzBlock ID
		$blockID =  $smcFunc['db_insert_id']('{db_prefix}ezp_blocks', 'id_block');

		foreach($installBlock['parameters'] as $myparam)
		{
			$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_parameters
					(id_block, title, parameter_name, parameter_type,defaultvalue,required)
				VALUES ($blockID,'" . $myparam['title']. "','" . $myparam['name']. "',
				'" . $myparam['parameter_type']. "','" . $myparam['defaultvalue']. "',
				'" . $myparam['required']. "'
				)");

			$paramID =  $smcFunc['db_insert_id']('{db_prefix}ezp_block_parameters', 'id_parameter');

			if (!empty($myparam['selectvalues']))
			{
				$selectvalues = explode(",",$myparam['selectvalues']);
				foreach ($selectvalues as $sv)
				{
					// Insert select value
			$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_paramaters_select
					(id_parameter, id_block, selecttext, selectvalue)
				VALUES ($paramID,$blockID,'" . $sv. "','" . $sv. "')");

				}

			}

		}


	}
}



function UpdateEzBlocks()
{
	global $smcFunc;

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		blocktype, blockdata, blockversion, blocktitle, id_block
	FROM {db_prefix}ezp_blocks
	WHERE  blocktype = 'builtin'
	");

	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{

		// Update RSS Feed ezBlock
		if ($row['blocktitle'] == 'RSS ezBlock' && $row['blockversion'] == '1.0')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'newwindow','title' => 'Open in new window','defaultvalue' => 0,'parameter_type' => 'checkbox', 'required' => 0);
			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}

		if ($row['blocktitle'] == 'Calendar ezBlock' && $row['blockversion'] == '1.0')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'size','title' => 'Size' ,'defaultvalue' => 'small','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'small,large');

			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}

		
		
		if ($row['blocktitle'] == 'RSS ezBlock' && $row['blockversion'] == '1.1')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'reverseorder','title' => 'Reverse Feed Order','defaultvalue' => 1,'parameter_type' => 'checkbox', 'required' => 0);
			AddParameter($row['id_block'],$paramArray);
			
			$paramArray = array();

			$paramArray[] = array('name' => 'encoding','title' => 'Format','defaultvalue' => 'ISO-8859-1','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'ISO-8859-1,UTF-8');
			AddParameter($row['id_block'],$paramArray);
			
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.2'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.2';	
		}

		// Update Member's Online ezBlock
		if ($row['blocktitle'] == 'Members Online' && $row['blockversion'] == '1.0')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'format','title' => 'Format','defaultvalue' => 'vertical','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'vertical,horizontal');
			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}

		// Update Poll ezBlock
		if ($row['blocktitle'] == 'Poll ezBlock' && $row['blockversion'] == '1.0')
		{
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_block_parameters SET required = 0
			WHERE title = 'Poll Topic ID' AND  id_block = " . $row['id_block']);
			$paramArray = array();
			$paramArray[] = array('name' => 'board','title' => 'Boards for Random Poll','defaultvalue' => 0,'parameter_type' => 'multiboardselect', 'required' => 0);
			AddParameter($row['id_block'],$paramArray);

			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';

		}

		// Update BoardNews ezBlock
		if ($row['blocktitle'] == 'Board News ezBlock' && $row['blockversion'] == '1.0')
		{
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_block_parameters SET parameter_type = 'multiboardselect'
			WHERE parameter_type = 'boardselect' AND title = 'Select Board for News' AND  id_block = " . $row['id_block']);

			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';

		}


		if ($row['blocktitle'] == 'Theme Select ezBlock' && $row['blockversion'] == '1.0')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'showpreview','title' => 'Show Preview Image','defaultvalue' => 'Yes','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'Yes,No');
			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';

		}

		if ($row['blocktitle'] == 'Board News ezBlock' && $row['blockversion'] == '1.1')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'length','title' => 'Number of characters to show per entry','defaultvalue' => 500,'parameter_type' => 'int', 'required' => 0);
			AddParameter($row['id_block'],$paramArray);

			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.2'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.2';
		}

		if ($row['blocktitle'] == 'Board News ezBlock' && $row['blockversion'] == '1.2')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'showlike', 'title' =>  'Show Facebook Like Button','defaultvalue' => 'false','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'true,false');

			AddParameter($row['id_block'],$paramArray);

			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.3'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.3';
		}

		if ($row['blocktitle'] == 'Board News ezBlock' && $row['blockversion'] == '1.3')
		{


			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.4'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.4';
		}


		if ($row['blocktitle'] == 'Recent Posts ezBlock' && $row['blockversion'] == '1.0')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'showcolor','title' =>  'Show Member Link Color','defaultvalue' => 'false','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'true,false');
			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}

		if ($row['blocktitle'] == 'Recent Topics ezBlock' && $row['blockversion'] == '1.0')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'showcolor', 'title' =>  'Show Member Link Color','defaultvalue' => 'false','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'true,false');
			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}
		
		if ($row['blocktitle'] == 'SMF Arcade ezBlock' && $row['blockversion'] == '1.0')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'count','title' => 'Number of scores to show','defaultvalue' => 10,'parameter_type' => 'int', 'required' => 1);
			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}
		
		
		if ($row['blocktitle'] == 'Recent Posts ezBlock' && $row['blockversion'] == '1.1')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'excludeboards','title' => 'Exclude Boards','defaultvalue' => 0,'parameter_type' => 'multiboardselect', 'required' => 0);

			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.2'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.2';
		}

		if ($row['blocktitle'] == 'SMF Gallery ezBlock' && $row['blockversion'] == '1.0')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'images','title' => 'Number of Images to show','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
			AddParameter($row['id_block'],$paramArray);
			$paramArray = array();
	        $paramArray[] = array('name' => 'rows','title' => 'Number of items per row','defaultvalue' => 4,'parameter_type' => 'int', 'required' => 1);
	        AddParameter($row['id_block'],$paramArray);
	        $paramArray = array();
	        $paramArray[] = array('name' => 'category','title' => 'Gallery Category ID to display','defaultvalue' => 0,'parameter_type' => 'int', 'required' => 0);
			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}

		if ($row['blocktitle'] == 'SMF Gallery ezBlock' && $row['blockversion'] == '1.1')
		{
			$paramArray = array();
			$paramArray[] = array('name' => 'mediatype','title' => 'Media Type','defaultvalue' => 'both','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'both,onlyphotos,onlyaudiovideos');
			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.2'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.2';
		}

		if ($row['blocktitle'] == 'SMF Gallery Random Image' && $row['blockversion'] == '1.0')
		{
			$paramArray = array();
			$paramArray[] = array('name' => 'mediatype','title' => 'Media Type','defaultvalue' => 'both','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'both,onlyphotos,onlyaudiovideos');

			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}



		if ($row['blocktitle'] == 'Recent Topics ezBlock' && $row['blockversion'] == '1.1')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'excludeboards','title' => 'Exclude Boards','defaultvalue' => 0,'parameter_type' => 'multiboardselect', 'required' => 0);

			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.2'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.2';
		}



		if ($row['blocktitle'] == 'Menu ezBlock' && $row['blockversion'] == '1.0')
		{
			$paramArray = array();

			$paramArray[] = array('name' => 'format','title' => 'Format','defaultvalue' => 'vertical','parameter_type' => 'select', 'required' => 1, 'selectvalues' => 'vertical,horizontal');
			AddParameter($row['id_block'],$paramArray);
			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}

		// Update Downloads ezBlock
		if ($row['blocktitle'] == 'Download System ezBlock' && $row['blockversion'] == '1.0')
		{
			$dbresult2 = $smcFunc['db_query']('', "
			SELECT
				id_parameter
				FROM {db_prefix}ezp_block_parameters
			WHERE id_block = " . $row['id_block'] . " AND  parameter_name = 'type' LIMIT 1
				");
			$paramRow = $smcFunc['db_fetch_assoc']($dbresult2);
			$smcFunc['db_free_result']($dbresult2);

			$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_paramaters_select
					(id_parameter, id_block, selecttext, selectvalue)
				VALUES (" . $paramRow['id_parameter'] . ", " . $row['id_block'] . ",'downloads','downloads')");



			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}

		// Update Downloads ezBlock
		if ($row['blocktitle'] == 'Download System ezBlock' && $row['blockversion'] == '1.1')
		{
			$dbresult2 = $smcFunc['db_query']('', "
			SELECT
				id_parameter
				FROM {db_prefix}ezp_block_parameters
			WHERE id_block = " . $row['id_block'] . " AND  parameter_name = 'type' LIMIT 1
				");
			$paramRow = $smcFunc['db_fetch_assoc']($dbresult2);
			$smcFunc['db_free_result']($dbresult2);

			$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_paramaters_select
					(id_parameter, id_block, selecttext, selectvalue)
				VALUES (" . $paramRow['id_parameter'] . ", " . $row['id_block'] . ",'random','random')");



			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.2'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.2';
		}

		// Update SMF Classifieds ezBlock
		if ($row['blocktitle'] == 'SMF Classifieds ezBlock' && $row['blockversion'] == '1.0')
		{
			$dbresult2 = $smcFunc['db_query']('', "
			SELECT
				id_parameter
				FROM {db_prefix}ezp_block_parameters
			WHERE id_block = " . $row['id_block'] . " AND  parameter_name = 'type' LIMIT 1
				");
			$paramRow = $smcFunc['db_fetch_assoc']($dbresult2);
			$smcFunc['db_free_result']($dbresult2);

			$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_paramaters_select
					(id_parameter, id_block, selecttext, selectvalue)
				VALUES (" . $paramRow['id_parameter'] . ", " . $row['id_block'] . ",'random','random')");



			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}



		// Update SMF Store ezBlock
		if ($row['blocktitle'] == 'SMF Store ezBlock' && $row['blockversion'] == '1.0')
		{
			$dbresult2 = $smcFunc['db_query']('', "
			SELECT
				id_parameter
				FROM {db_prefix}ezp_block_parameters
			WHERE id_block = " . $row['id_block'] . " AND  parameter_name = 'type' LIMIT 1
				");
			$paramRow = $smcFunc['db_fetch_assoc']($dbresult2);
			$smcFunc['db_free_result']($dbresult2);

			$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_paramaters_select
					(id_parameter, id_block, selecttext, selectvalue)
				VALUES (" . $paramRow['id_parameter'] . ", " . $row['id_block'] . ",'random','random')");


			$smcFunc['db_query']('', "UPDATE {db_prefix}ezp_blocks SET blockversion = '1.1'
			WHERE id_block = " . $row['id_block']);
			$row['blockversion'] = '1.1';
		}




	}

	$smcFunc['db_free_result']($dbresult);

}


function AddParameter($blockID,$installBlock)
{
	global $smcFunc;

	foreach($installBlock as $myparam)
		{
			$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_parameters
					(id_block, title, parameter_name, parameter_type,defaultvalue,required)
				VALUES ($blockID,'" . $myparam['title']. "','" . $myparam['name']. "',
				'" . $myparam['parameter_type']. "','" . $myparam['defaultvalue']. "',
				'" . $myparam['required']. "'
				)");

			$paramID =  $smcFunc['db_insert_id']('{db_prefix}ezp_block_parameters', 'id_parameter');


			if (!empty($myparam['selectvalues']))
			{
				$selectvalues = explode(",",$myparam['selectvalues']);
				foreach ($selectvalues as $sv)
				{
					// Insert select value
			$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_paramaters_select
					(id_parameter, id_block, selecttext, selectvalue)
				VALUES ($paramID, $blockID,'" . $sv. "','" . $sv. "')");

				}

			}
		}
}

?>