<?php
/*
	SMF Archive Version: 1.1
	http://www.smfhacks.com
	By:vbgamer45

	Install: Just upload archive.php to your forum's main directory

	License: The footer links must remain. If you want to remove them you need to talk to me.
	************************
	archive.php - Generates a search engine friendly version of the forum.
	************************
	Function list
	void archive_board($boardid) - shows a board's topics passed is the ID_BOARD
	void archive_topic($topicid) - shows a topic's post passed is the ID_TOPIC
	void archive_main() - shows the board index of the archive
	//Template functions
	void archive_header($title, $url) - shows the header html information for the template. Takes a title of the page and the url to the full version
	void archive_footer() - shows the footer html for the archive. Links must remain!

*/
include 'SSI.php';

$board = 0;
$topic = 0;

//Max topics to show per page in a forum
$maxtopics = 20;
//Max posts to show per page in a topic
$maxposts = 15;

//Get the board ID
@$board = (int) $_GET['board'];

//Get the topic ID
@$topic = (int) $_GET['topic'];

if (empty($board) && empty($topic))
{
	archive_main();
	exit();
}
if (!empty($board))
{
	archive_board($board);
	exit();
}
if (!empty($topic))
{
	archive_topic($topic);
	exit();
}

function archive_board($boardid)
{
	global $boardurl, $db_prefix, $maxtopics, $mbname, $user_info;

	$boardid = addslashes($boardid);
	$start = (int) $_REQUEST['start'];


	$request = db_query("
	SELECT
		b.name, b.numTopics
	FROM {$db_prefix}boards AS b
	WHERE b.ID_BOARD = $boardid  AND $user_info[query_see_board]", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($request);

	if (mysql_num_rows($request) == 0)
		die('The topic or board you are looking for appears to be either missing or off limits to you');
	mysql_free_result($request);


	archive_header($row['name'],$boardurl . '/index.php?board=' . $boardid . '.' . $start);
	//Show board Menu Parent List
	echo '<div id="linktree"><a href="' . $boardurl . '/archive.php">' . $mbname . '</a></div>';


	//Show Pages List
	$totalpages = (int) $row['numTopics'] / $maxtopics;
	if($totalpages < 1)
		$totalpages = 1;

	echo '<div id="pages">Pages: ';
	for($i=1; $i <= $totalpages; $i++)
	{
		if($i != $totalpages)
			echo '<a href="' . $boardurl . '/archive.php?board=' . $boardid . '.' . (($i-1) * $maxtopics) . '">' . $i . '</a>,&nbsp;';
		else
			echo '<a href="' . $boardurl . '/archive.php?board=' . $boardid . '.' . (($i-1) * $maxtopics) . '">' . $i . '</a>';
	}
	echo '</div>';

	echo '<div id="forum">';
	$request2 = db_query("
	SELECT
		m.subject, t.ID_TOPIC, t.numReplies
	FROM {$db_prefix}messages AS m, {$db_prefix}topics AS t
	WHERE m.ID_BOARD = $boardid AND m.ID_MSG = t.ID_FIRST_MSG
	ORDER BY t.ID_LAST_MSG DESC
	LIMIT $start,$maxtopics", __FILE__, __LINE__);
	$i = 0;
	while($row2 = mysql_fetch_assoc($request2))
	{
		$i++;
		echo  $i . '.&nbsp;<a href="' . $boardurl . '/archive.php?topic=' . $row2['ID_TOPIC'] . '.0">' . $row2['subject'] . '</a> (' . $row2['numReplies'] . ' replies)<br />';

	}
	echo '</div>';
	archive_footer();

}

function archive_topic($topicid)
{
	global $boardurl, $db_prefix, $maxposts, $user_info, $mbname;


	$topicid = addslashes($topicid);

	$start = (int) $_REQUEST['start'];

	$request = db_query("
	SELECT
		m.subject, t.numReplies, b.name, b.ID_BOARD, m.ID_BOARD
	FROM ({$db_prefix}messages AS m, {$db_prefix}topics AS t,
	{$db_prefix}boards AS b)
	WHERE b.ID_BOARD = m.ID_BOARD AND t.ID_TOPIC = $topicid AND m.ID_MSG = t.ID_FIRST_MSG AND $user_info[query_see_board]", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($request);
	if (mysql_num_rows($request) == 0)
		die('The topic or board you are looking for appears to be either missing or off limits to you');


	archive_header($row['subject'],$boardurl . '/index.php?topic=' . $topicid . '.' . $start);

	echo '<div id="linktree"><a href="' . $boardurl . '/archive.php">' . $mbname . '</a>&nbsp;<a href="' . $boardurl . '/archive.php?board=' . $row['ID_BOARD'] . '.0">' . $row['name'] . '</a></div>';


	// Show Pages List
	$totalpages = floor($row['numReplies'] / $maxposts) + 1;
	if ($totalpages < 1)
		$totalpages = 1;


	echo '<div id="pages">Pages: ';
	for($i=1; $i <= $totalpages; $i++)
	{
		if($i != $totalpages)
			echo '<a href="' . $boardurl . '/archive.php?topic=' . $topicid . '.' . (($i-1) * $maxposts) . '">' . $i . '</a>,&nbsp;';
		else
			echo '<a href="' . $boardurl . '/archive.php?topic=' . $topicid . '.' . (($i-1) * $maxposts) . '">' . $i . '</a>';
	}
	echo '</div>';

	// Get all posts in a topic
	$request2 = db_query("
	SELECT
		m.subject, m.posterName, m.body, m.posterTime
		FROM {$db_prefix}messages AS m
		LEFT JOIN {$db_prefix}boards AS b ON(b.ID_BOARD = m.ID_BOARD)
		WHERE m.ID_TOPIC = $topicid AND $user_info[query_see_board] ORDER BY m.ID_MSG ASC LIMIT $start,$maxposts", __FILE__, __LINE__);

	echo '<div id="topic">';
	while($row2 = mysql_fetch_assoc($request2))
	{
		echo $row2['subject'] . ' By: ' . $row2['posterName'] . ' Date: ' . timeformat($row2['posterTime']) . '<br />';
		if (function_exists('parse_bbc'))
			echo parse_bbc($row2['body']);
		else
			echo doUBBC($row2['body']);

		echo '<hr />';
	}

	echo '</div>';

	archive_footer();

}
function archive_main()
{
	global $mbname,$boardurl, $db_prefix, $ID_MEMBER, $user_info, $modSettings;

	archive_header($mbname,$boardurl);


	// Show cats
	echo '<div id="main"><ul>';

	$request1 = db_query("
	SELECT
		c.ID_CAT, c.catOrder, c.name
	FROM {$db_prefix}categories AS c
	ORDER BY c.catOrder ASC", __FILE__, __LINE__);
	while ($row1 = mysql_fetch_assoc($request1))
	{


		$catid = $row1['ID_CAT'];

		$request2 = db_query("
			SELECT
				b.name, b.numPosts, b.ID_BOARD, b.ID_CAT, b.childLevel, b.ID_PARENT, b.boardOrder
			FROM {$db_prefix}boards AS b
				LEFT JOIN {$db_prefix}log_boards AS lb ON (lb.ID_BOARD = b.ID_BOARD AND lb.ID_MEMBER = $ID_MEMBER)
			WHERE $user_info[query_see_board]" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
				AND b.ID_BOARD != " . (int) $modSettings['recycle_board'] : '') . " AND $catid = b.ID_CAT
			", __FILE__, __LINE__);

		$b_count = db_affected_rows();
		if ($b_count !=0)
		{
			echo '<li><b>' . $row1['name'] . '</b></li>';
			// List the forums and subforums

			echo '<ul>';
			while ($row2 = mysql_fetch_assoc($request2))
			{
				echo '<li><a href="' . $boardurl . '/archive.php?board=' . $row2['ID_BOARD'] . '.0">' . $row2['name'] . '</a> (' . $row2['numPosts'] . ' posts)</li>';
			}
			echo '</ul>';
		}
		mysql_free_result($request2);

	}
	mysql_free_result($request1);

	echo '</ul></div>';

	archive_footer();
}

function archive_header($title, $url)
{
	global $boardurl;
 echo '
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 <html>
 <head>
 	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<meta name="description" content="' . $title . '" />
 	<title>' . $title . '</title>
 	<link rel="stylesheet" type="text/css" href="archive.css" />
 </head>
 <body>
 	<div id="header">
		<div id="fullver">Full Version: <a href="' . $url . '">' . $title . '</a>
		</div>
		<div id="menu" align="center"><a href="' . $boardurl . '/index.php?action=help">Help</a>&nbsp;<a href="' . $boardurl . '/index.php?action=search">Search</a>&nbsp;<a href="' . $boardurl . '/index.php?action=mlist">Member List</a>
		</div>
	</div>';

}

function archive_footer()
{
// Link back to SMF Hacks must remain.
// http://www.smfhacks.com/copyright_removal.php
echo '<br /><div align="center" id="footer"><!--Copyright for SMFHacks must stay-->SMF Archive Funded by SMF For Free&nbsp;<a href="https://www.smfforfree.com">Free Forum Hosting</a><br /><a href="https://www.smfhacks.com" target="blank">SMF Hacks</a><!--EndCopyright for SMFHacks must stay--></div>
	</body></html>';
}
?>