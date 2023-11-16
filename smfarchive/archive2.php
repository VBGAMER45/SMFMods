<?php
/*
	SMF Archive Version: 3.0
	https://www.smfhacks.com
	By:vbgamer45

	Install: Just upload archive2.php to your forum's main directory

	License: The footer links must remain. If you want to remove them you need to order copyright removal
	************************
	archive2.php - Generates a search engine friendly version of the forum.
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

// Max topics to show per page in a forum
$maxtopics = $modSettings['defaultMaxTopics'];
// Max posts to show per page in a topic
$maxposts = $modSettings['defaultMaxMessages'];

// Get the board ID
if (isset($_GET['board']))
	$board = (int) $_GET['board'];

//Get the topic ID
if (isset($_GET['topic']))
	$topic = (int) $_GET['topic'];

$smcFunc['db_select_db']($db_name, $db_connection);

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
	global $boardurl, $maxtopics, $mbname, $user_info, $smcFunc, $txt;

	$boardid = addslashes($boardid);
	$start = (int) $_REQUEST['start'];


	$request = $smcFunc['db_query']('', "
	SELECT
		b.name, b.num_topics
	FROM {db_prefix}boards AS b
	WHERE b.ID_BOARD = $boardid AND $user_info[query_see_board]");
	$row = $smcFunc['db_fetch_assoc']($request);

	if ($smcFunc['db_num_rows']($request) == 0)
		die($txt['topic_gone']);
	$smcFunc['db_free_result']($request);

	$request = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) as total
	FROM {db_prefix}topics AS b
	WHERE b.ID_BOARD = $boardid ");
	$rowTotal = $smcFunc['db_fetch_assoc']($request);


	archive_header($row['name'],$boardurl . '/index.php?board=' . $boardid . '.' . $start);
	// Show board Menu Parent List
	echo '<div id="linktree"><a href="' . $boardurl . '/archive2.php">' . $mbname . '</a></div>';


	// Show Pages List

	$totalpages = ceil($rowTotal['total'] / $maxtopics);
	if($totalpages < 1)
		$totalpages = 1;


	echo '<div id="pages">' .  $txt['pages'] . ': ';
	for($i=1; $i <= $totalpages; $i++)
	{
		if ($i != 1)
			echo ',&nbsp;';

			echo '<a href="' . $boardurl . '/archive2.php?board=' . $boardid . '.' . (($i-1) * $maxtopics) . '">' . $i . '</a>';

	}
	echo '</div>';

	echo '<div id="forum">';
	$request2 = $smcFunc['db_query']('', "
	SELECT
		m.subject, t.ID_TOPIC, t.num_replies
	FROM {db_prefix}messages AS m, {db_prefix}topics AS t
	WHERE m.ID_BOARD = $boardid AND m.ID_MSG = t.ID_FIRST_MSG AND t.approved = 1
	ORDER BY t.ID_LAST_MSG DESC
	LIMIT $start,$maxtopics");
	$i = 0;
	while($row2 = $smcFunc['db_fetch_assoc']($request2))
	{
		$i++;
		echo  $i . '.&nbsp;<a href="' . $boardurl . '/archive2.php?topic=' . $row2['ID_TOPIC'] . '.0">' . $row2['subject'] . '</a> (' . $row2['num_replies'] . ' replies)<br />';

	}
	echo '</div>';
	archive_footer();

}

function archive_topic($topicid)
{
	global $boardurl, $smcFunc, $maxposts, $user_info, $mbname, $txt;

	$topicid = addslashes($topicid);

	$start = (int) $_REQUEST['start'];

	$request = $smcFunc['db_query']('', "
	SELECT
		m.subject, t.num_replies, b.name, b.ID_BOARD, m.ID_BOARD
	FROM ({db_prefix}messages AS m, {db_prefix}topics AS t,
	{db_prefix}boards AS b)
	WHERE b.ID_BOARD = m.ID_BOARD AND t.ID_TOPIC = $topicid AND t.approved = 1 AND m.ID_MSG = t.ID_FIRST_MSG AND $user_info[query_see_board]");
	$row = $smcFunc['db_fetch_assoc']($request);
	if ($smcFunc['db_num_rows']($request) == 0)
		die($txt['topic_gone']);


	archive_header($row['subject'],$boardurl . '/index.php?topic=' . $topicid . '.' . $start);

	echo '<div id="linktree"><a href="' . $boardurl . '/archive2.php">' . $mbname . '</a>&nbsp;<a href="' . $boardurl . '/archive2.php?board=' . $row['ID_BOARD'] . '.0">' . $row['name'] . '</a></div>';


	// Show Pages List
	$totalpages = floor($row['num_replies'] / $maxposts) + 1;
	if ($totalpages < 1)
		$totalpages = 1;


	echo '<div id="pages">' . $txt['pages'] . ': ';
	for($i=1; $i <= $totalpages; $i++)
	{
		if ($i != 1)
			echo ',&nbsp;';

			echo '<a href="' . $boardurl . '/archive2.php?topic=' . $topicid . '.' . (($i-1) * $maxposts) . '">' . $i . '</a>';
	}
	echo '</div>';

	// Get all posts in a topic
	$request2 = $smcFunc['db_query']('', "
	SELECT
		m.subject, m.poster_name, m.body, m.poster_time
		FROM {db_prefix}messages AS m
		LEFT JOIN {db_prefix}boards AS b ON(b.ID_BOARD = m.ID_BOARD)
		WHERE m.ID_TOPIC = $topicid AND m.approved = 1 AND $user_info[query_see_board] ORDER BY m.ID_MSG ASC LIMIT $start,$maxposts");

	echo '<div id="topic">';
	while($row2 = $smcFunc['db_fetch_assoc']($request2))
	{
		echo $row2['subject'] . ' By: ' . $row2['poster_name'] . ' Date: ' . timeformat($row2['poster_time']) . '<br />';
		
		echo parse_bbc($row2['body']);
		

		echo '<hr />';
	}

	echo '</div>';


	echo '<div id="pages">' . $txt['pages'] . ': ';
	for($i=1; $i <= $totalpages; $i++)
	{
		if ($i != 1)
			echo ',&nbsp;';

			echo '<a href="' . $boardurl . '/archive2.php?topic=' . $topicid . '.' . (($i-1) * $maxposts) . '">' . $i . '</a>';
	}
	echo '</div>';

	archive_footer();

}

function archive_main()
{
	global $mbname, $boardurl, $smcFunc, $user_info, $modSettings;

	archive_header($mbname,$boardurl);

	// Show cats
	echo '<div id="main"><ul>';

	$request1 = $smcFunc['db_query']('', "
	SELECT
		c.ID_CAT, c.cat_order, c.name
	FROM {db_prefix}categories AS c
	ORDER BY c.cat_order ASC");
	while ($row1 = $smcFunc['db_fetch_assoc']($request1))
	{

		$catid = $row1['ID_CAT'];

		$request2 = $smcFunc['db_query']('', "
			SELECT
				b.name, b.num_posts, b.ID_BOARD, b.ID_CAT, b.child_level, b.ID_PARENT, b.board_order
			FROM {db_prefix}boards AS b
				LEFT JOIN {db_prefix}log_boards AS lb ON (lb.ID_BOARD = b.ID_BOARD AND lb.ID_MEMBER = " . $user_info['id'] . ")
			WHERE $user_info[query_see_board]" . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? "
				AND b.ID_BOARD != " . (int) $modSettings['recycle_board'] : '') . " AND $catid = b.ID_CAT
			");

		$b_count = $smcFunc['db_affected_rows']();
		if ($b_count !=0)
		{
			echo '<li><b>' . $row1['name'] . '</b></li>';
			// List the forums and subforums

			echo '<ul>';
			while ($row2 = $smcFunc['db_fetch_assoc']($request2))
			{
				echo '<li><a href="' . $boardurl . '/archive2.php?board=' . $row2['ID_BOARD'] . '.0">' . $row2['name'] . '</a> (' . $row2['num_posts'] . ' posts)</li>';
			}
			echo '</ul>';
		}
		$smcFunc['db_free_result']($request2);

	}
	$smcFunc['db_free_result']($request1);

	echo '</ul></div>';

	archive_footer();
}

function archive_header($title, $url)
{
	global $boardurl, $txt;
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
		<div id="menu" align="center"><a href="' . $boardurl . '/index.php?action=help">' . $txt['help'] . '</a>&nbsp;<a href="' . $boardurl . '/index.php?action=search">' . $txt['search']  . '</a>
		</div>
	</div>';

}

function archive_footer()
{
// Link back to SMF Hacks must remain.
// http://www.smfhacks.com/copyright_removal.php
echo '<br /><div align="center" id="footer"><!--Copyright for SMFHacks must stay-->SMF Archive by&nbsp;<a href="https://www.createaforum.com">Free Forum Hosting</a><!--EndCopyright for SMFHacks must stay--></div>
	</body></html>';
}
?>