<?php
/**********************************************************************************
* blogHooks.php                                                                   *
***********************************************************************************
* SMFBlog: Hook callbacks for SMF 2.1                                            *
* =============================================================================== *
* Software Version:           SMFBlog 3.0                                         *
* Updated by:                 vbgamer45 (http://www.smfhacks.com)                 *
* Copyright 2010-2026 by:     vbgamer45 (http://www.smfhacks.com)                *
***********************************************************************************
*********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * integrate_actions - Register the blog action.
 * Replaces the file edit to index.php.
 */
function blog_actions(&$actionArray)
{
	$actionArray['blog'] = array('Blog.php', 'Blog');
}

/**
 * integrate_pre_boardindex - Add is_blog to the board index SELECT.
 * Replaces the file edit to Subs-BoardIndex.php.
 */
function blog_pre_boardindex(&$board_index_selects, &$board_index_parameters)
{
	$board_index_selects[] = 'b.is_blog';
}

/**
 * integrate_boardindex_board - Hide blog boards from the board index.
 * Uses the is_blog column added by integrate_pre_boardindex.
 */
function blog_boardindex_board(&$this_category, $row_board)
{
	global $modSettings;

	if (!empty($modSettings['blog_hide_boards']) && !empty($row_board['is_blog']))
		unset($this_category[$row_board['id_board']]);
}

/**
 * integrate_modify_features - Add blog settings tab to admin Features area.
 * Replaces file edits to Admin.php and ManageSettings.php.
 */
function blog_modify_features(&$subActions)
{
	global $context, $txt;

	loadLanguage('Modifications');

	// Add the blog sub-action.
	$subActions['blog'] = 'ModifyBlogSettings';

	// Add the blog tab.
	$context['admin_tabs']['blog'] = array($txt['blog']);
}

/**
 * integrate_post2_end - Redirect to blog post after posting a comment.
 * Replaces the file edit to Post.php.
 */
function blog_post2_end()
{
	// Did we get here from a blog post?
	if (!empty($_POST['blog_post']))
	{
		// Do we have a blog name?
		if (!empty($_POST['blog_name']))
			// Redirect using the blog name at the end.
			redirectexit('action=blog;sa=view_post;id=' . (int) $_POST['blog_post'] . ';blog_name=' . strtolower(preg_replace('/[^A-Za-z0-9\-_]/', '', $_POST['blog_name'])) . '#new');
		// Otherwise, just the ID.
		else
			redirectexit('action=blog;sa=view_post;id=' . (int) $_POST['blog_post'] . '#new');
	}
}

/**
 * integrate_redirect - Rewrite blog URLs in redirects.
 * Replaces the file edit to Subs.php for URL rewriting in redirects.
 */
function blog_redirect(&$setLocation)
{
	global $boardurl, $modSettings, $scripturl;

	// Is URL rewriting enabled?
	if (!empty($modSettings['blog_enable_rewrite']))
	{
		// The blog itself (using a name)
		$setLocation = preg_replace('/^' . preg_quote($scripturl, '/') . '\?action=blog;sa=view_blog;name=([A-Za-z0-9\-_]+)/', $boardurl . '/blog/$1/', $setLocation);
		// A post in the blog (with its alias)
		$setLocation = preg_replace('/^' . preg_quote($scripturl, '/') . '\?action=blog;sa=view_post;id=([0-9]+);blog_name=([A-Za-z0-9\-_]+)/', $boardurl . '/blog/$2/post-$1.html', $setLocation);
		// The main blog page
		$setLocation = preg_replace('/^' . preg_quote($scripturl, '/') . '\?action=blog$/', $boardurl . '/blog/', $setLocation);
	}
}

/**
 * integrate_buffer - Rewrite blog URLs in page output buffer.
 * Replaces the file edit to QueryString.php.
 */
function blog_buffer_rewrite($buffer)
{
	global $boardurl, $modSettings, $scripturl;

	// Is URL rewriting enabled?
	if (!empty($modSettings['blog_enable_rewrite']))
	{
		// The main blog page
		$buffer = preg_replace('/"' . preg_quote($scripturl, '/') . '\?action=blog"/', '"' . $boardurl . '/blog/"', $buffer);
		// The blog itself
		$buffer = preg_replace('/"' . preg_quote($scripturl, '/') . '\?action=blog;sa=view_blog;name=([A-Za-z0-9\-_]+)"/', '"' . $boardurl . '/blog/$1/"', $buffer);
		// A specific page in the blog
		$buffer = preg_replace('/"' . preg_quote($scripturl, '/') . '\?action=blog;sa=view_blog;name=([A-Za-z0-9\-_]+);start=([0-9]+)"/', '"' . $boardurl . '/blog/$1/$2.html"', $buffer);
		// A post in the blog (with its alias)
		$buffer = preg_replace('/"' . preg_quote($scripturl, '/') . '\?action=blog;sa=view_post;id=([0-9]+);blog_name=([A-Za-z0-9\-_]+)"/', '"' . $boardurl . '/blog/$2/post-$1.html"', $buffer);
	}

	return $buffer;
}

/**
 * integrate_menu_buttons - Add blog button to main navigation.
 * Replaces the file edit to Subs.php for the menu button.
 */
function blog_menu_buttons(&$buttons)
{
	global $scripturl, $txt;

	loadLanguage('Modifications');

	// Build the blog button.
	$blog_button = array(
		'title' => $txt['blog'],
		'href' => $scripturl . '?action=blog',
		'show' => true,
		'icon' => 'modify_button',
		'sub_buttons' => array(),
	);

	// Insert after 'mlist' if it exists, otherwise append.
	$new_buttons = array();
	$inserted = false;
	foreach ($buttons as $key => $button)
	{
		$new_buttons[$key] = $button;
		if ($key === 'mlist' && !$inserted)
		{
			$new_buttons['blog'] = $blog_button;
			$inserted = true;
		}
	}

	if (!$inserted)
		$new_buttons['blog'] = $blog_button;

	$buttons = $new_buttons;
}

/**
 * integrate_modify_board - Handle is_blog and blog_alias when saving a board.
 * Replaces file edits to ManageBoards.php and Subs-Boards.php.
 *
 * Note: There is no hook between EditBoard2() building $boardOptions and calling
 * modifyBoard(), so we read $_POST['is_blog'] directly here.
 */
function blog_modify_board($id, $boardOptions, &$boardUpdates, &$boardUpdateParameters)
{
	global $smcFunc;

	// Read is_blog from POST since no hook exists to inject it into $boardOptions.
	$is_blog = isset($_POST['is_blog']);

	$boardUpdates[] = 'is_blog = {int:is_blog}';
	$boardUpdateParameters['is_blog'] = $is_blog ? 1 : 0;

	// If it's a blog, generate an alias from the board name.
	$board_name = isset($boardOptions['board_name']) ? $boardOptions['board_name'] : '';
	if ($is_blog && !empty($board_name))
	{
		// Remove any non-alphanumeric characters.
		$alias = strtolower(preg_replace('/[^A-Za-z0-9 ]/', '', $board_name));
		// Replace spaces with dashes.
		$alias = str_replace(' ', '-', $alias);

		// Is this alias already in use?
		$result = $smcFunc['db_query']('', '
			SELECT COUNT(id_board)
			FROM {db_prefix}boards
			WHERE blog_alias = {string:alias}
				AND id_board != {int:board_id}',
			array(
				'alias' => $alias,
				'board_id' => $id,
			)
		);
		list($temp_count) = $smcFunc['db_fetch_row']($result);
		$smcFunc['db_free_result']($result);

		// It is? Add the board ID to make it unique.
		if ($temp_count > 0)
			$alias .= '_' . $id;

		$boardUpdates[] = 'blog_alias = {string:blog_alias}';
		$boardUpdateParameters['blog_alias'] = $alias;
	}
}

/**
 * integrate_create_board - Set default is_blog value for new boards.
 * Replaces the file edit to ManageBoards.php for new board defaults.
 */
function blog_create_board(&$boardOptions, &$board_columns, &$board_parameters)
{
	if (!isset($boardOptions['is_blog']))
		$boardOptions['is_blog'] = 0;

	$board_columns[] = 'is_blog';
	$board_parameters[] = $boardOptions['is_blog'] ? 1 : 0;
}

/**
 * integrate_pre_boardtree - Add is_blog and blog_alias to board tree SELECT.
 * Replaces the file edit to Subs-Boards.php for the board tree query.
 */
function blog_pre_boardtree(&$boardColumns, &$boardParameters)
{
	$boardColumns[] = 'b.is_blog';
	$boardColumns[] = 'b.blog_alias';
}

/**
 * integrate_boardtree_board - Populate is_blog in board data from query results.
 * Replaces the file edit to Subs-Boards.php for populating board data.
 */
function blog_boardtree_board($row)
{
	global $boards;

	if (isset($row['is_blog']))
		$boards[$row['id_board']]['is_blog'] = $row['is_blog'];
	if (isset($row['blog_alias']))
		$boards[$row['id_board']]['blog_alias'] = $row['blog_alias'];
}

/**
 * integrate_edit_board - Ensure is_blog is set in context for the board edit template.
 */
function blog_edit_board()
{
	global $context;

	if (!isset($context['board']['is_blog']))
		$context['board']['is_blog'] = 0;
}

/**
 * ModifyBlogSettings - Admin settings page for the blog.
 * Previously injected into ManageSettings.php via file edit.
 */
function ModifyBlogSettings($return_config = false)
{
	global $txt, $scripturl, $context, $settings, $modSettings;

	loadLanguage('Modifications');

	$config_vars = array(
		array('check', 'blog_enable'),
		array('check', 'blog_enable_rewrite'),
		array('check', 'blog_hide_boards'),
		'',
		array('int', 'blog_posts_perpage'),
		array('int', 'blog_comments_perpage'),
	);

	if ($return_config)
		return $config_vars;

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);

		writeLog();
		redirectexit('action=admin;area=featuresettings;sa=blog');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=featuresettings;save;sa=blog';
	$context['settings_title'] = $txt['blog_settings'];

	prepareDBSettingContext($config_vars);
}

?>
