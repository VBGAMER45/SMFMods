<?php
/**********************************************************************************
* Blog.php                                                                        *
***********************************************************************************
* SMFBlog: A (very) simple Blog system for Simple Machines Forum                  *
* =============================================================================== *
* Software Version:           SMFBlog 2.0       
* Updated by:                 vbgamer45 (http://www.smfhacks.com)                 *
* Updated by:                 Runic (http://www.smfservices.org)                  *
* Original Mod by:            Daniel15 (http://www.dansoftaustralia.net/)         *
* Copyright 2010 by:           vbgamer45 (http://www.smfhacks.com)                *
* Copyright 2009-2010 by:     Runic (http://www.smfservices.org)                  *
* Copyright 2007-2009 by:     Daniel15 (http://www.dansoftaustralia.net/)         *
***********************************************************************************
*********************************************************************************/
ini_set("display_errors",1);
if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file is for SMFBlog, to view the blog itself.

	void Blog()
		- The main controlling function.
		- just passes control to the correct function
		
	void BlogIndex()
		- shows a list of all the blog boards.
		- if there's only one blog board, redirect straight to it
		
	void BlogView()
		- view a blog itself
		- grabs a list of posts in this blog, via ssi_boardNews()
		
	void BlogViewPost()
		- view a blog post
		- grabs the post and comments via the "SSI Topic and Replies" mod
*/


function Blog()
{
	global $boarddir, $context, $mbname, $smcFunc, $modSettings, $scripturl, $settings, $txt;
	
	// Some version stuff
	$context['blog_version'] = array(
		'version' => '2.0',
		'build' => '1',
		'revision' => '$Revision: 1 $',
		'date' => '$Date: 2010-01-30 18:54:44 +0 $',
	);
	
	// Is the blog disabled?
	if (empty($modSettings['blog_enable']))
		// Sorry bud, nothing we can do about this...
		fatal_lang_error('blog_error_disabled');
	
	// Link tree....
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=blog',
		'name' => $txt['blog']
	);

	// We need some SSI functions.
	require_once($boarddir . '/SSI.php');
	// Load our template.
	loadTemplate('Blog');
	// Use the blog layer
	$context['template_layers'][] = 'blog';
	// Add our stylesheet.
	$context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/blog.css" />';
	// A default page title.
	$context['page_title'] = $context['forum_name'] . ' ' . $txt['blog'];
	
	// Some actions we can do.
	$actions = array(
		'index' => 'BlogIndex',
		'view_blog' => 'BlogView',
		'view_post' => 'BlogViewPost',
	);

	// Get the current action.
	$action = (isset($_GET['sa'])) ? $_GET['sa'] : 'index';

	// Check if the action exist, otherwise go to the index.
	if (isset($actions[$action]))
		$actions[$action]();
	else
		BlogIndex();
}

function BlogIndex()
{
	global $boardurl, $context, $modSettings, $smcFunc, $scripturl, $txt;

	// !!! Todo!
	// Check the user's permissions.
	//isAllowedTo('blog_??');
	
	$context['blog_boards'] = array();

	// Get all the blog boards.
	$result = $smcFunc['db_query']('', '
		SELECT 
			id_board, name, description, blog_alias
		FROM {db_prefix}boards
		WHERE is_blog = {int:is_a_blog}
		ORDER BY name ASC',
		array(
			'is_a_blog' => 1,
		)
	);

	// Loop through each board...
	while ($row = $smcFunc['db_fetch_assoc']($result))
	{
		// ...Add this board to the list
		$context['blog_boards'][] = array(
			'id' => $row['id_board'],
			'name' => $row['name'],
			'description' => $row['description'],
			'alias' => $row['blog_alias'],
		);
	}
	$smcFunc['db_free_result']($result);
	
	// Only one board? Let's go straight there
	if (count($context['blog_boards']) == 1)
		redirectexit('action=blog;sa=view_blog;name=' . $context['blog_boards'][0]['alias']);
	
	// Set the page title
	$context['page_title'] .= ' &mdash; ' . $txt['blog_blogs'];

	// Let's go to the index, please :)
	$context['sub_template'] = 'index';
	
}

function BlogView()
{
	global $context, $scripturl, $txt, $smcFunc, $modSettings;
	
	// Do we have an alias?
	if (!empty($_GET['name']))
	{
		// Make sure the alias only has valid characters in it.
		$name = strtolower(preg_replace('/[^A-Za-z0-9\-_]/', '', $_GET['name']));

		// Let's try to get information on this blog.
		$result = $smcFunc['db_query']('', '
			SELECT 
				id_board, name, description, is_blog, blog_alias
			FROM {db_prefix}boards
			WHERE blog_alias = {string:name}',
			array(
				'name' => $name,
			)
		);	
	}
	// Otherwise, do we have a numeric board index?
	elseif (!empty($_GET['id']))
	{
		// Make sure ID is numeric.
		 $id = (int) $_GET['id'];
		
		// Get some information on this blog board.
		$result = $smcFunc['db_query']('', '
			SELECT 
				id_board, name, description, is_blog, blog_alias
			FROM {db_prefix}boards
			WHERE id_board = {int:id}',
			array(
				'id' => $id,
			)
		);
	}
	// Are you lost? Go back to the beginning.
	else
		redirectexit('action=blog');
	
	// Doesn't exist?
	if ($smcFunc['db_num_rows']($result) == 0)
		fatal_lang_error('blog_error_not_exist');
		
	$row = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);
	

	
	// Make sure it's a blog.
	if ($row['is_blog'] == false)
		fatal_lang_error('blog_error_not_blog');
		
	// Link tree....
	// Are we using an alias?
	if (!empty($_GET['name']))
	{
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=blog;sa=view_blog;name=' . $_GET['name'],
			'name' => $row['name']
		);
	}
	// Otherwise, it's an ID?
	elseif (!empty($_GET['id']))
	{
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=blog;sa=view_blog;id=' . $_GET['id'],
			'name' => $row['name']
		);
	}
	
	// Set some data that we use.
	$context['blog'] = array(
		'name' => $row['name'],
		'description' => $row['description'],
		'alias' => $row['blog_alias'],
		'posts' => array(),
	);
		
	// Get the number of posts in this board.
	$result = $smcFunc['db_query']('', '
		SELECT 
			COUNT(id_topic)
		FROM {db_prefix}topics
		WHERE id_board = {int:id_of_board}',
		array(
			'id_of_board' => (int) $row['id_board'],
		)
	);

	list ($post_count) = $smcFunc['db_fetch_row']($result);
	$smcFunc['db_free_result']($result);
	
	
	
	// Construct a page index.
	$context['blog']['pageindex'] = constructPageIndex($scripturl . '?action=blog;sa=view_blog;name=' . $row['blog_alias'] . ';start=%d', $_REQUEST['start'], $post_count, $modSettings['blog_posts_perpage'], true);

	// Get all the recent posts.
	$context['blog']['posts'] = ssi_boardNews($row['id_board'], $modSettings['blog_posts_perpage'], null, null, array());
		
	// Use the "view" template.
	$context['sub_template'] = 'view';
}

function BlogViewPost()
{
	global $boarddir, $context, $smcFunc, $modSettings, $scripturl, $sourcedir, $txt;
	
	// No ID? Redirect back to the main blog page.
	if (empty($_GET['id']))
		redirectexit('action=blog');
	// Make sure it's numeric.
	$_GET['id'] = (int) $_GET['id'];
	
	// We need the postbox functions.
	require_once($sourcedir . '/Subs-Post.php');
	require_once($sourcedir . '/Subs-Editor.php');
	// Now create the editor.
	$editorOptions = array(
		'id' => 'message',
		'value' => '',
		'width' => '90%',
		'form' => 'postmodify',
		'labels' => array(
			'post_button' => '',
		),
	);
	create_control_richedit($editorOptions);
	$context['post_box_name'] = $editorOptions['id'];
	
	// Register the comment form in the session variables.
	checkSubmitOnce('register');
	
	// Make sure we have a posts per page value. If not, use a default.
	if (empty($modSettings['blog_comments_perpage']))
		$modSettings['blog_comments_perpage'] = 10;
	// Grab the post and its replies.
	$context['blog_post'] = BlogTopic($_GET['id'], $modSettings['blog_comments_perpage'], null, 'array');
	// Construct a page index
	// !!! ssi_topic() should be fixed! :P
	$context['blog_post']['pageindex'] = constructPageIndex($scripturl . '?action=blog;sa=view_post;id=' . $_GET['id'] . (!empty($modSettings['blog_enable_rewrite']) ? ';blog_name=' . $_GET['blog_name'] : '') . ';start=%d#comments', $_REQUEST['start'], $context['blog_post']['reply_count'], $modSettings['blog_comments_perpage'], true);
	
	// If the blog name is passed...
	if (!empty($_GET['blog_name']))
		$context['blog_name'] = $_GET['blog_name'];

	// Use the "view_post" template.
	$context['sub_template'] = 'view_post';
}


// SSI Topic and Replies
function BlogTopic($topic = null, $num_replies = null, $start = null, $output_method = 'echo')
{
	global $scripturl, $smcFunc, $txt, $settings, $modSettings, $context;
	global $memberContext;

	loadLanguage('Stats');

	// Topic variable set?
	if ($topic === null && isset($_REQUEST['ssi_topic']))
		$topic = (int) $_REQUEST['ssi_topic'];
	// Well, what else are we going to do?
	else
		$topic = (int) $topic;
	
	// Number of replies per page.
	$num_replies = isset($_GET['num_replies']) ? (int) $_GET['num_replies'] : 10;
 
	// Reply to start at.
	$start = $start === null ? isset($_GET['start']) ? (int) $_GET['start'] : 0 : (int) $start;

	$num_replies = max(0, $num_replies);
	$start = max(0, $start);

	// Get some information about the topic. (ie. the first post)
	// !!! This should check logged in user's permissions, rather than checking if guests are allowed.
	$result = $smcFunc['db_query']('', '
		SELECT
			t.id_topic, t.id_board, t.id_first_msg, t.id_last_msg, t.id_member_started, t.num_replies, t.locked, m.id_member,m.icon, m.subject, 
			m.poster_time, m.body, m.smileys_enabled, m.id_msg, IFNULL(mem.real_name, m.poster_name) AS poster_name, b.name AS board_name
		FROM ({db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m
			INNER JOIN {db_prefix}boards AS b)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE t.id_topic = {int:current_topic}
			AND m.id_msg = t.id_first_msg
			AND b.id_board = t.id_board
			AND FIND_IN_SET({int:full_group}, b.member_groups)',
		array(
			'full_group' => -1,
			'current_topic' => $topic,
		)
	);

	// No results? That's not good!
	if ($smcFunc['db_num_rows']($result) == 0)
		fatal_lang_error('ssiTopic_notfound');
	
	// Get the topic info.
	$row = $smcFunc['db_fetch_assoc']($result);
	$smcFunc['db_free_result']($result);

	// Censor it.
	censorText($row['body']);
	censorText($row['subject']);

	// Parse BBC in the message
	$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

	// Start our array of information.
	$return = array(
		'id' => $row['id_topic'],
		'id_msg' => $row['id_msg'],
		'icon' => '<img src="' .  $settings['images_url'] . '/post/' . $row['icon'] . '.gif" align="middle" alt="' . $row['icon'] . '" border="0" />',
		'subject' => $row['subject'],
		'body' => $row['body'],
		'time' => timeformat($row['poster_time']),
		'timestamp' => forum_time(true, $row['poster_time']),
		'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
		'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['num_replies'] . ' ' . ($row['num_replies'] == 1 ? $txt['ssi_comment'] : $txt['ssi_comments']) . '</a>',
		'reply_count' => $row['num_replies'],
		'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'],
		'comment_link' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'] . '">' . $txt['ssi_write_comment'] . '</a>',
		'locked' => !empty($row['locked']),
		'poster' => array(
			'id' => $row['id_member'],
			'name' => $row['poster_name'],
			'href' => !empty($row['id_member']) ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
			'link' => !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>' : $row['poster_name'],
		),
		// !!! Better way to do this?
		'page_index' => constructPageIndex('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?start=%d', $start, $row['num_replies'], $num_replies, true),
		'replies' => array(),
		
	);

	// Get each post and poster in this topic.
	$result = $smcFunc['db_query']('', '
		SELECT m.id_msg, m.id_member
		FROM {db_prefix}messages AS m
			INNER JOIN {db_prefix}topics AS t
		WHERE m.id_topic = {int:current_topic}
			AND t.id_topic = {int:current_topic}
			AND m.id_msg != t.id_first_msg
		ORDER BY m.id_msg DESC
		LIMIT {int:start}, {int:num_replies}',
		array(
			'start' => $start,
			'num_replies' => $num_replies,
			'current_topic' => $topic,
		)
	);
		
	// Were there any replies?
	if ($smcFunc['db_num_rows']($result) != 0)
	{	
		// The arrays we will use later on.
		$messages = array();
		$posters = array();
		
		// Loop through each post.
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			// If it wasn't a guest, add them to the posters array.
			if (!empty($row['id_member']))
				$posters[] = $row['id_member'];
				
			// Add this message to the messages array.
			$messages[] = $row['id_msg'];
		}
		$smcFunc['db_free_result']($result);
		$posters = array_unique($posters);

		// Load the member data of all the members that posted in this topic
		loadMemberData($posters);

		// Now, let's get all the replies. (posts)
		$result = $smcFunc['db_query']('', '
			SELECT
				m.id_msg, m.poster_time, m.id_member, m.subject,
				IFNULL(mem.real_name, m.poster_name) AS poster_name,
				m.poster_ip, m.smileys_enabled, m.modified_time,
				m.modified_name, m.body, m.icon
			FROM {db_prefix}messages AS m
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
			WHERE id_msg IN ({array_int:post})
			ORDER BY m.id_msg ASC',
			array(
				'post' => $messages,
			)
		);

		$counter = $start;
		// Loop through each reply
		// !!! This will probably use way too much memory for large topics!!
		// !!! Callbacks instead?
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			// Try loading the member's data. 
			// If it couldn't load, or the user was a guest, use some failsafe values.
			if (!loadMemberContext($row['id_member']))
			{
				// Notice this information isn't used anywhere else....
				$memberContext[$row['id_member']]['name'] = $row['poster_name'];
				$memberContext[$row['id_member']]['id'] = 0;
				$memberContext[$row['id_member']]['group'] = $txt['guest'];
				$memberContext[$row['id_member']]['link'] = $row['poster_name'];
				$memberContext[$row['id_member']]['email'] = $row['posterEmail'];
				$memberContext[$row['id_member']]['hide_email'] = $row['posterEmail'] == '' || (!empty($modSettings['guest_hideContacts']) && $user_info['is_guest']);
				$memberContext[$row['id_member']]['is_guest'] = true;
			}
			else
			{
				$memberContext[$row['id_member']]['can_view_profile'] = allowedTo('profile_view_any') || ($row['id_member'] == $id_member && allowedTo('profile_view_own'));
				$memberContext[$row['id_member']]['is_topic_starter'] = $row['id_member'] == $return['poster']['id'];
			}
			$memberContext[$row['id_member']]['ip'] = $row['poster_ip'];

			// Censor it.
			censorText($row['body']);
			censorText($row['subject']);

			// Parse BBC in the message.
			$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

			// Add this to our messages array.
			$return['replies'][] = array(
				'number' => $counter + 1,
				'alternate' => $counter % 2,
				'id' => $row['id_msg'],
				'href' => $scripturl . '?topic=' . $topic . '.msg' . $row['id_msg'] . '#msg' . $row['id_msg'],
				'link' => '<a href="' . $scripturl . '?topic=' . $topic . '.msg' . $row['id_msg'] . '#msg' . $row['id_msg'] . '">' . $row['subject'] . '</a>',
				'poster' => &$memberContext[$row['id_member']],
				'icon' => '<img src="' .  $settings['images_url'] . '/post/' . $row['icon'] . '.gif" align="middle" alt="' . $row['icon'] . '" border="0" />',
				'subject' => $row['subject'],
				'time' => timeformat($row['poster_time']),
				'timestamp' => forum_time(true, $row['poster_time']),
				'modified' => array(
					'time' => timeformat($row['modified_time']),
					'timestamp' => forum_time(true, $row['modified_time']),
					'name' => $row['modified_name']
				),
				'body' => $row['body'],
				'new' => empty($row['isRead']),
				'first_new' => isset($context['start_from']) && $context['start_from'] == $counter,
				'can_modify' => allowedTo('modify_any') || (allowedTo('modify_replies') && $context['user']['started']) || (allowedTo('modify_own') && $row['id_member'] == $context['user']['id'] && (empty($modSettings['edit_disable_time']) || $row['poster_time'] + $modSettings['edit_disable_time'] * 60 > time())),
				'can_remove' => allowedTo('delete_any') || (allowedTo('delete_replies') && $context['user']['started']) || (allowedTo('delete_own') && $row['id_member'] == $context['user']['id'] && (empty($modSettings['edit_disable_time']) || $row['poster_time'] + $modSettings['edit_disable_time'] * 60 > time())),
				'can_see_ip' => allowedTo('moderate_forum') || ($row['id_member'] == $context['user']['id'] && !empty($context['user']['id'])),
				'is_last' => false,
			);

			$counter++;
		}
		$num_rows = $smcFunc['db_num_rows']($result);
		$smcFunc['db_free_result']($result);

		// The last post.
		$return['replies'][count($return['replies']) - 1]['is_last'] = true;
	}

	// If we're not echoing, return this information.
	if ($output_method != 'echo')
		return $return;
	
	// OK, if we're here, we need to echo the data.
	// Output the first post.
	echo '
			<div>
				<a href="', $return['href'], '">', $return['icon'], ' <b>', $return['subject'], '</b></a>
				<div class="smaller">', $return['time'], ' ', $txt['by'], ' ', $return['poster']['link'], '</div>
				<div class="post" style="padding: 2ex 0;">', $return['body'], '</div>
				', $return['locked'] ? '' : $return['comment_link'], '<br /><br />
			</div>
			
			<h2>', $txt['ssiTopic_replies'], ':</h2>
			<p>', $txt['pages'], ': ', $return['page_index'], '</p>';

	// Loop through each post.
	foreach ($return['replies'] as $post)
	{
		echo '
			<div>
				<a href="', $post['href'], '">', $post['icon'], ' <b>', $post['subject'], '</b></a>
				<div class="smaller">', $txt['reply'], ' ', $post['number'], ': ', $post['time'], ' ', $txt['by'], ' ', $post['poster']['link'], '</div>
				<div class="post" style="padding: 2ex 0;">', $post['body'], '</div>
			</div>';

		// The last post? Let's put the page numbers.
		if ($post['is_last'])
			echo '
			', $txt['pages'], ': ', $return['page_index'];
		else
			echo '
			<hr style="margin: 2ex 0;" width="100%" />';
			
	}
}

?>