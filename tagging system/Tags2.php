<?php
/*
Tagging System
Version 4.2
by:vbgamer45
https://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function TagsMain()
{
	// Load the main Tags template
	if (version_compare(SMF_VERSION, '2.1', '>='))
		loadtemplate('Tags2.1');
	else
		loadtemplate('Tags2');

	// Load the language files
	if (loadlanguage('Tags') == false)
		loadLanguage('Tags','english');

	// Tags actions
	$subActions = array(
		'suggest' => 'SuggestTag',
		'suggest2' => 'SuggestTag2',
		'addtag' => 'AddTag',
		'addtag2' => 'AddTag2',
		'deletetag' => 'DeleteTag',
		'admin' => 'TagsSettings',
		'admin2' => 'TagsSettings2',
		'cleanup' => 'TagCleanUp',
		'addtagajax' => 'AddTagAjax',
		'deletetagajax' => 'DeleteTagAjax',
		'suggesttagajax' => 'SuggestTagAjax',
	);

	// Follow the sa or just go to main links index.
	if (isset($_GET['sa']) && !empty($subActions[$_GET['sa']]))
		$subActions[$_GET['sa']]();
	else
		ViewTags();
}

function ViewTags()
{
	global $context, $txt, $mbname, $scripturl, $user_info, $smcFunc, $modSettings;

	// Views that tag results and popular tags
	if (isset($_REQUEST['tagid']))
	{
		// Show the tag results for that tag
		$id = (int) $_REQUEST['tagid'];

		// Find Tag Name
		$dbresult = $smcFunc['db_query']('', '
			SELECT tag
			FROM {db_prefix}tags
			WHERE id_tag = {int:id_tag}
			LIMIT 1',
			array(
				'id_tag' => $id,
			)
		);
		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$smcFunc['db_free_result']($dbresult);

		if (empty($row['tag']))
			fatal_error($txt['smftags_err_notag'], false);

		$context['tag_search'] = $smcFunc['htmlspecialchars']($row['tag']);
		$context['page_title'] = $mbname . ' - ' . $txt['smftags_resultsfor'] . $context['tag_search'];
		$context['start'] = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

		$dbresult = $smcFunc['db_query']('', '
			SELECT COUNT(*) AS total
			FROM {db_prefix}tags_log AS l
				INNER JOIN {db_prefix}topics AS t ON (l.id_topic = t.id_topic)
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
				INNER JOIN {db_prefix}messages AS m ON (t.id_first_msg = m.id_msg)
			WHERE l.id_tag = {int:id_tag}
				AND t.approved = {int:approved}
				AND {query_see_board}',
			array(
				'id_tag' => $id,
				'approved' => 1,
			)
		);
		$totalRow = $smcFunc['db_fetch_assoc']($dbresult);
		$numofrows = $totalRow['total'];
		$smcFunc['db_free_result']($dbresult);

		// Find Results
		$dbresult = $smcFunc['db_query']('', '
			SELECT t.num_replies, t.num_views, m.id_member, m.poster_name, m.subject,
				m.id_topic, m.poster_time, t.id_board
			FROM {db_prefix}tags_log AS l
				INNER JOIN {db_prefix}topics AS t ON (l.id_topic = t.id_topic)
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
				INNER JOIN {db_prefix}messages AS m ON (t.id_first_msg = m.id_msg)
			WHERE l.id_tag = {int:id_tag}
				AND t.approved = {int:approved}
				AND {query_see_board}
			ORDER BY m.id_msg DESC
			LIMIT {int:start}, 25',
			array(
				'id_tag' => $id,
				'approved' => 1,
				'start' => $context['start'],
			)
		);

		$context['tags_topics'] = array();
		while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$row['subject'] = $smcFunc['htmlspecialchars']($row['subject']);
			$row['poster_name'] = $smcFunc['htmlspecialchars']($row['poster_name']);
			$context['tags_topics'][] = $row;
		}
		$smcFunc['db_free_result']($dbresult);

		$context['sub_template'] = 'results';

		$context['page_index'] = constructPageIndex($scripturl . '?action=tags;tagid=' . $id, $_REQUEST['start'], $numofrows, 25);
	}
	else
	{
		$context['page_title'] = $mbname . ' - ' . $txt['smftags_popular'];

		$cloud_limit = isset($modSettings['smftags_set_cloud_tags_to_show']) ? (int) $modSettings['smftags_set_cloud_tags_to_show'] : 50;

		$result = $smcFunc['db_query']('', '
			SELECT t.tag, l.id_tag, COUNT(l.id_tag) AS quantity
			FROM {db_prefix}tags AS t
				INNER JOIN {db_prefix}tags_log AS l ON (t.id_tag = l.id_tag)
			GROUP BY l.id_tag, t.tag
			ORDER BY COUNT(l.id_tag) DESC
			LIMIT {int:limit}',
			array(
				'limit' => $cloud_limit,
			)
		);

		$tags = array();
		$tags2 = array();

		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$tags[$row['tag']] = $row['quantity'];
			$tags2[$row['tag']] = $row['id_tag'];
		}
		$smcFunc['db_free_result']($result);

		// Shuffle to randomize display order
		if (!empty($tags))
		{
			$keys = array_keys($tags);
			shuffle($keys);
			$shuffled = array();
			$shuffled2 = array();
			foreach ($keys as $key)
			{
				$shuffled[$key] = $tags[$key];
				$shuffled2[$key] = $tags2[$key];
			}
			$tags = $shuffled;
			$tags2 = $shuffled2;
		}

		if (count($tags2) > 0)
		{
			$max_size = isset($modSettings['smftags_set_cloud_max_font_size_precent']) ? (int) $modSettings['smftags_set_cloud_max_font_size_precent'] : 250;
			$min_size = isset($modSettings['smftags_set_cloud_min_font_size_precent']) ? (int) $modSettings['smftags_set_cloud_min_font_size_precent'] : 100;

			$max_qty = max(array_values($tags));
			$min_qty = min(array_values($tags));

			$spread = $max_qty - $min_qty;
			if ($spread == 0)
				$spread = 1;

			$step = ($max_size - $min_size) / $spread;

			$context['poptags'] = '';
			foreach ($tags as $key => $value)
			{
				$size = $min_size + (($value - $min_qty) * $step);
				$escaped_key = $smcFunc['htmlspecialchars']($key);

				$context['poptags'] .= '<a href="' . $scripturl . '?action=tags;tagid=' . $tags2[$key] . '" style="font-size: ' . $size . '%"';
				$context['poptags'] .= ' title="' . $value . ' topics tagged &quot;' . $escaped_key . '&quot;"';
				$context['poptags'] .= '>' . $escaped_key . '</a> ';
			}
		}

		// Find latest tagged posts
		$dbresult = $smcFunc['db_query']('', '
			SELECT DISTINCT l.id_topic, t.num_replies, t.num_views, m.id_member,
				m.poster_name, m.subject, m.id_topic, m.poster_time,
				t.id_board, g.tag, g.id_tag, l.id
			FROM {db_prefix}tags_log AS l
				INNER JOIN {db_prefix}topics AS t ON (l.id_topic = t.id_topic)
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
				INNER JOIN {db_prefix}messages AS m ON (t.id_first_msg = m.id_msg)
				LEFT JOIN {db_prefix}tags AS g ON (l.id_tag = g.id_tag)
			WHERE t.approved = {int:approved}
				AND {query_see_board}
			ORDER BY l.id DESC
			LIMIT 20',
			array(
				'approved' => 1,
			)
		);

		$context['tags_topics'] = array();
		while ($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$row['tag'] = $smcFunc['htmlspecialchars']($row['tag']);
			$row['subject'] = $smcFunc['htmlspecialchars']($row['subject']);
			$row['poster_name'] = $smcFunc['htmlspecialchars']($row['poster_name']);
			$context['tags_topics'][] = $row;
		}
		$smcFunc['db_free_result']($dbresult);
	}

	$context['linktree'][] = array(
		'url' => $scripturl . '?action=tags',
		'name' => $txt['smftags_menu']
	);
}

function AddTag()
{
	global $context, $txt, $mbname, $user_info, $smcFunc;

	isAllowedTo('smftags_add');

	$topic = (int) $_REQUEST['topic'];

	if (empty($topic))
		fatal_error($txt['smftags_err_notopic'], false);

	// Check permission
	$a_manage = allowedTo('smftags_manage');

	$dbresult = $smcFunc['db_query']('', '
		SELECT m.id_member
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (t.id_first_msg = m.id_msg)
		WHERE t.id_topic = {int:topic}
		LIMIT 1',
		array(
			'topic' => $topic,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if (empty($row) || ($user_info['id'] != $row['id_member'] && $a_manage == false))
		fatal_error($txt['smftags_err_permaddtags'], false);

	$context['tags_topic'] = $topic;
	$context['sub_template'] = 'addtag';
	$context['page_title'] = $mbname . ' - ' . $txt['smftags_addtag2'];
}

function AddTag2()
{
	global $txt, $modSettings, $smcFunc, $user_info;

	isAllowedTo('smftags_add');
	checkSession();

	$topic = (int) $_REQUEST['topic'];

	if (empty($topic))
		fatal_error($txt['smftags_err_notopic'], false);

	// Check Permission
	$a_manage = allowedTo('smftags_manage');

	$dbresult = $smcFunc['db_query']('', '
		SELECT m.id_member
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (t.id_first_msg = m.id_msg)
		WHERE t.id_topic = {int:topic}
		LIMIT 1',
		array(
			'topic' => $topic,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if (empty($row) || ($user_info['id'] != $row['id_member'] && $a_manage == false))
		fatal_error($txt['smftags_err_permaddtags'], false);

	// Get how many tags there have been for the topic
	$dbresult = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS total
		FROM {db_prefix}tags_log
		WHERE id_topic = {int:topic}',
		array(
			'topic' => $topic,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$totaltags = $row['total'];
	$smcFunc['db_free_result']($dbresult);

	if ($totaltags >= $modSettings['smftags_set_maxtags'])
		fatal_error($txt['smftags_err_toomaxtag'], false);

	// Process tags - store raw, escape on output
	$raw_input = $smcFunc['strtolower'](trim($_REQUEST['tag']));

	if (empty($raw_input))
		fatal_error($txt['smftags_err_notag'], false);

	$tags = explode(',', $raw_input);

	foreach ($tags as $tag)
	{
		$tag = trim($tag);

		if (empty($tag))
			continue;

		// Check min tag length
		if ($smcFunc['strlen']($tag) < $modSettings['smftags_set_mintaglength'])
			continue;

		// Check max tag length
		if ($smcFunc['strlen']($tag) > $modSettings['smftags_set_maxtaglength'])
			continue;

		// Check if tag already exists
		$dbresult = $smcFunc['db_query']('', '
			SELECT id_tag
			FROM {db_prefix}tags
			WHERE tag = {string:tag}
			LIMIT 1',
			array(
				'tag' => $tag,
			)
		);

		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$smcFunc['db_free_result']($dbresult);

		if (empty($row))
		{
			// Insert into Tags table
			$smcFunc['db_insert']('insert',
				'{db_prefix}tags',
				array('tag' => 'string', 'approved' => 'int'),
				array($tag, 1),
				array('id_tag')
			);
			$ID_TAG = $smcFunc['db_insert_id']('{db_prefix}tags', 'id_tag');

			// Insert into Tags log
			$smcFunc['db_insert']('insert',
				'{db_prefix}tags_log',
				array('id_tag' => 'int', 'id_topic' => 'int', 'id_member' => 'int'),
				array($ID_TAG, $topic, $user_info['id']),
				array('id')
			);
		}
		else
		{
			$ID_TAG = $row['id_tag'];

			// Check if this tag is already assigned to this topic
			$dbresult2 = $smcFunc['db_query']('', '
				SELECT id
				FROM {db_prefix}tags_log
				WHERE id_tag = {int:id_tag}
					AND id_topic = {int:topic}',
				array(
					'id_tag' => $ID_TAG,
					'topic' => $topic,
				)
			);

			$existing = $smcFunc['db_fetch_assoc']($dbresult2);
			$smcFunc['db_free_result']($dbresult2);

			if (!empty($existing))
				continue;

			// Insert into Tags log
			$smcFunc['db_insert']('insert',
				'{db_prefix}tags_log',
				array('id_tag' => 'int', 'id_topic' => 'int', 'id_member' => 'int'),
				array($ID_TAG, $topic, $user_info['id']),
				array('id')
			);
		}
	}

	// Redirect back to the topic
	redirectexit('topic=' . $topic);
}

function DeleteTag()
{
	global $txt, $smcFunc, $user_info;

	isAllowedTo('smftags_del');
	checkSession('get');

	$id = (int) $_REQUEST['tagid'];

	// Check permission
	$a_manage = allowedTo('smftags_manage');

	$dbresult = $smcFunc['db_query']('', '
		SELECT id_member, id_topic, id_tag
		FROM {db_prefix}tags_log
		WHERE id = {int:id}
		LIMIT 1',
		array(
			'id' => $id,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if (empty($row))
		fatal_error($txt['smftags_err_notag'], false);

	if ($row['id_member'] != $user_info['id'] && $a_manage == false)
		fatal_error($txt['smftags_err_deletetag'], false);

	// Delete the tag for the topic
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}tags_log
		WHERE id = {int:id}
		LIMIT 1',
		array(
			'id' => $id,
		)
	);

	// Tag Cleanup
	TagCleanUp($row['id_tag']);

	// Redirect back to the topic
	redirectexit('topic=' . $row['id_topic']);
}

function TagsSettings()
{
	global $context, $txt, $mbname;

	// Check permission
	isAllowedTo('smftags_manage');

	$context['sub_template'] = 'admin_settings';
	$context['page_title'] = $mbname . ' - ' . $txt['smftags_settings'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['smftags_admin'],
		'description' => '',
		'tabs' => array(
			'admin' => array(
				'description' => '',
			),
		),
	);
}

function TagsSettings2()
{
	global $smcFunc;

	// Check permission
	isAllowedTo('smftags_manage');
	checkSession();

	// Get the settings
	$smftags_set_mintaglength = (int) $_REQUEST['smftags_set_mintaglength'];
	$smftags_set_maxtaglength = (int) $_REQUEST['smftags_set_maxtaglength'];
	$smftags_set_maxtags = (int) $_REQUEST['smftags_set_maxtags'];

	$smftags_set_cloud_tags_per_row = (int) $_REQUEST['smftags_set_cloud_tags_per_row'];
	$smftags_set_cloud_tags_to_show = (int) $_REQUEST['smftags_set_cloud_tags_to_show'];
	$smftags_set_cloud_max_font_size_precent = (int) $_REQUEST['smftags_set_cloud_max_font_size_precent'];
	$smftags_set_cloud_min_font_size_precent = (int) $_REQUEST['smftags_set_cloud_min_font_size_precent'];

	$smftags_set_msgindex = isset($_REQUEST['smftags_set_msgindex']) ? 1 : 0;
	$smftags_set_msgindex_max_show = (int) $_REQUEST['smftags_set_msgindex_max_show'];
	$smftags_set_use_css_tags = isset($_REQUEST['smftags_set_use_css_tags']) ? 1 : 0;

	// Validate CSS color inputs
	$smftags_set_css_tag_background_color = isset($_REQUEST['smftags_set_css_tag_background_color']) && preg_match('/^#[0-9a-fA-F]{3,6}$/', $_REQUEST['smftags_set_css_tag_background_color'])
		? $_REQUEST['smftags_set_css_tag_background_color'] : '#71a0b7';
	$smftags_set_css_tag_font_color = isset($_REQUEST['smftags_set_css_tag_font_color']) && preg_match('/^#[0-9a-fA-F]{3,6}$/', $_REQUEST['smftags_set_css_tag_font_color'])
		? $_REQUEST['smftags_set_css_tag_font_color'] : '#ffffff';

	// Save the setting information
	updateSettings(
		array(
			'smftags_set_maxtags' => $smftags_set_maxtags,
			'smftags_set_mintaglength' => $smftags_set_mintaglength,
			'smftags_set_maxtaglength' => $smftags_set_maxtaglength,
			'smftags_set_cloud_tags_per_row' => $smftags_set_cloud_tags_per_row,
			'smftags_set_cloud_tags_to_show' => $smftags_set_cloud_tags_to_show,
			'smftags_set_cloud_max_font_size_precent' => $smftags_set_cloud_max_font_size_precent,
			'smftags_set_cloud_min_font_size_precent' => $smftags_set_cloud_min_font_size_precent,
			'smftags_set_msgindex' => $smftags_set_msgindex,
			'smftags_set_msgindex_max_show' => $smftags_set_msgindex_max_show,
			'smftags_set_use_css_tags' => $smftags_set_use_css_tags,
			'smftags_set_css_tag_background_color' => $smftags_set_css_tag_background_color,
			'smftags_set_css_tag_font_color' => $smftags_set_css_tag_font_color,
		)
	);

	// Redirect to the admin section
	redirectexit('action=admin;area=tags;sa=admin');
}

function TagCleanUp($ID_TAG)
{
	global $smcFunc;

	$ID_TAG = (int) $ID_TAG;

	// Check if tag still has any log entries
	$dbresult = $smcFunc['db_query']('', '
		SELECT id
		FROM {db_prefix}tags_log
		WHERE id_tag = {int:id_tag}
		LIMIT 1',
		array(
			'id_tag' => $ID_TAG,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if (empty($row))
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}tags
			WHERE id_tag = {int:id_tag}',
			array(
				'id_tag' => $ID_TAG,
			)
		);
	}
}

function SuggestTag()
{
	global $context, $txt, $mbname;

	// Check permission
	isAllowedTo('smftags_suggest');

	$context['sub_template'] = 'suggest';
	$context['page_title'] = $mbname . ' - ' . $txt['smftags_suggest'];
}

function SuggestTag2()
{
	global $txt, $modSettings, $smcFunc, $user_info;

	// Check permission
	isAllowedTo('smftags_suggest');
	checkSession();

	$raw_input = $smcFunc['strtolower'](trim($_REQUEST['tag']));

	if (empty($raw_input))
		fatal_error($txt['smftags_err_notag'], false);

	// Check min/max tag length
	if ($smcFunc['strlen']($raw_input) < $modSettings['smftags_set_mintaglength'])
		fatal_error($txt['smftags_err_mintag'] . $modSettings['smftags_set_mintaglength'], false);

	if ($smcFunc['strlen']($raw_input) > $modSettings['smftags_set_maxtaglength'])
		fatal_error($txt['smftags_err_maxtag'] . $modSettings['smftags_set_maxtaglength'], false);

	// Check if tag already exists
	$dbresult = $smcFunc['db_query']('', '
		SELECT id_tag
		FROM {db_prefix}tags
		WHERE tag = {string:tag}
		LIMIT 1',
		array(
			'tag' => $raw_input,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if (!empty($row))
		fatal_error($txt['smftags_err_alreadyexists'], false);

	// Insert as unapproved
	$smcFunc['db_insert']('insert',
		'{db_prefix}tags',
		array('tag' => 'string', 'approved' => 'int'),
		array($raw_input, 0),
		array('id_tag')
	);

	// Redirect to tags page
	redirectexit('action=tags');
}

function AddTagAjax()
{
	global $txt, $modSettings, $smcFunc, $user_info;

	// Clean any existing output buffers
	while (ob_get_level())
		ob_end_clean();

	header('Content-Type: application/json');

	// Check permission (non-fatal)
	if (!allowedTo('smftags_add'))
	{
		echo json_encode(array('success' => false, 'error' => $txt['cannot_smftags_add']));
		obExit(false);
	}

	// Check session (non-fatal)
	$session_error = checkSession('post', '', false);
	if ($session_error !== '')
	{
		echo json_encode(array('success' => false, 'error' => $txt['smftags_err_ajax']));
		obExit(false);
	}

	$topic = (int) $_REQUEST['topic'];

	if (empty($topic))
	{
		echo json_encode(array('success' => false, 'error' => $txt['smftags_err_notopic']));
		obExit(false);
	}

	// Check permission - topic owner or manage
	$a_manage = allowedTo('smftags_manage');

	$dbresult = $smcFunc['db_query']('', '
		SELECT m.id_member
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (t.id_first_msg = m.id_msg)
		WHERE t.id_topic = {int:topic}
		LIMIT 1',
		array(
			'topic' => $topic,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if (empty($row) || ($user_info['id'] != $row['id_member'] && $a_manage == false))
	{
		echo json_encode(array('success' => false, 'error' => $txt['smftags_err_permaddtags']));
		obExit(false);
	}

	// Get how many tags there have been for the topic
	$dbresult = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS total
		FROM {db_prefix}tags_log
		WHERE id_topic = {int:topic}',
		array(
			'topic' => $topic,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$totaltags = $row['total'];
	$smcFunc['db_free_result']($dbresult);

	if ($totaltags >= $modSettings['smftags_set_maxtags'])
	{
		echo json_encode(array('success' => false, 'error' => $txt['smftags_err_toomaxtag']));
		obExit(false);
	}

	// Process tags
	$raw_input = $smcFunc['strtolower'](trim($_REQUEST['tag']));

	if (empty($raw_input))
	{
		echo json_encode(array('success' => false, 'error' => $txt['smftags_err_notag']));
		obExit(false);
	}

	$tags = explode(',', $raw_input);
	$added_tags = array();

	foreach ($tags as $tag)
	{
		$tag = trim($tag);

		if (empty($tag))
			continue;

		// Check min tag length
		if ($smcFunc['strlen']($tag) < $modSettings['smftags_set_mintaglength'])
			continue;

		// Check max tag length
		if ($smcFunc['strlen']($tag) > $modSettings['smftags_set_maxtaglength'])
			continue;

		// Check if tag already exists
		$dbresult = $smcFunc['db_query']('', '
			SELECT id_tag
			FROM {db_prefix}tags
			WHERE tag = {string:tag}
			LIMIT 1',
			array(
				'tag' => $tag,
			)
		);

		$row = $smcFunc['db_fetch_assoc']($dbresult);
		$smcFunc['db_free_result']($dbresult);

		if (empty($row))
		{
			// Insert into Tags table
			$smcFunc['db_insert']('insert',
				'{db_prefix}tags',
				array('tag' => 'string', 'approved' => 'int'),
				array($tag, 1),
				array('id_tag')
			);
			$ID_TAG = $smcFunc['db_insert_id']('{db_prefix}tags', 'id_tag');

			// Insert into Tags log
			$smcFunc['db_insert']('insert',
				'{db_prefix}tags_log',
				array('id_tag' => 'int', 'id_topic' => 'int', 'id_member' => 'int'),
				array($ID_TAG, $topic, $user_info['id']),
				array('id')
			);
			$log_id = $smcFunc['db_insert_id']('{db_prefix}tags_log', 'id');

			$added_tags[] = array(
				'id' => $log_id,
				'id_tag' => $ID_TAG,
				'tag' => $smcFunc['htmlspecialchars']($tag),
			);
		}
		else
		{
			$ID_TAG = $row['id_tag'];

			// Check if this tag is already assigned to this topic
			$dbresult2 = $smcFunc['db_query']('', '
				SELECT id
				FROM {db_prefix}tags_log
				WHERE id_tag = {int:id_tag}
					AND id_topic = {int:topic}',
				array(
					'id_tag' => $ID_TAG,
					'topic' => $topic,
				)
			);

			$existing = $smcFunc['db_fetch_assoc']($dbresult2);
			$smcFunc['db_free_result']($dbresult2);

			if (!empty($existing))
				continue;

			// Insert into Tags log
			$smcFunc['db_insert']('insert',
				'{db_prefix}tags_log',
				array('id_tag' => 'int', 'id_topic' => 'int', 'id_member' => 'int'),
				array($ID_TAG, $topic, $user_info['id']),
				array('id')
			);
			$log_id = $smcFunc['db_insert_id']('{db_prefix}tags_log', 'id');

			$added_tags[] = array(
				'id' => $log_id,
				'id_tag' => $ID_TAG,
				'tag' => $smcFunc['htmlspecialchars']($tag),
			);
		}
	}

	echo json_encode(array('success' => true, 'tags' => $added_tags));
	obExit(false);
}

function DeleteTagAjax()
{
	global $txt, $smcFunc, $user_info;

	// Clean any existing output buffers
	while (ob_get_level())
		ob_end_clean();

	header('Content-Type: application/json');

	// Check permission (non-fatal)
	if (!allowedTo('smftags_del'))
	{
		echo json_encode(array('success' => false, 'error' => $txt['cannot_smftags_del']));
		obExit(false);
	}

	// Check session (non-fatal)
	$session_error = checkSession('post', '', false);
	if ($session_error !== '')
	{
		echo json_encode(array('success' => false, 'error' => $txt['smftags_err_ajax']));
		obExit(false);
	}

	$id = (int) $_REQUEST['tagid'];

	if (empty($id))
	{
		echo json_encode(array('success' => false, 'error' => $txt['smftags_err_notag']));
		obExit(false);
	}

	// Check permission - tag creator or manage
	$a_manage = allowedTo('smftags_manage');

	$dbresult = $smcFunc['db_query']('', '
		SELECT id_member, id_topic, id_tag
		FROM {db_prefix}tags_log
		WHERE id = {int:id}
		LIMIT 1',
		array(
			'id' => $id,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	if (empty($row))
	{
		echo json_encode(array('success' => false, 'error' => $txt['smftags_err_notag']));
		obExit(false);
	}

	if ($row['id_member'] != $user_info['id'] && $a_manage == false)
	{
		echo json_encode(array('success' => false, 'error' => $txt['smftags_err_deletetag']));
		obExit(false);
	}

	// Delete the tag for the topic
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}tags_log
		WHERE id = {int:id}
		LIMIT 1',
		array(
			'id' => $id,
		)
	);

	// Tag Cleanup
	TagCleanUp($row['id_tag']);

	echo json_encode(array('success' => true));
	obExit(false);
}

function SuggestTagAjax()
{
	global $txt, $smcFunc;

	// Clean any existing output buffers
	while (ob_get_level())
		ob_end_clean();

	header('Content-Type: application/json');

	// Check permission (non-fatal)
	if (!allowedTo('smftags_add'))
	{
		echo json_encode(array('success' => false, 'error' => $txt['cannot_smftags_add']));
		obExit(false);
	}

	$search = isset($_REQUEST['search']) ? trim($smcFunc['strtolower']($_REQUEST['search'])) : '';

	if (empty($search))
	{
		echo json_encode(array('success' => true, 'tags' => array()));
		obExit(false);
	}

	// Escape SQL wildcards and append %
	$search = strtr($search, array('%' => '\%', '_' => '\_'));
	$search .= '%';

	$dbresult = $smcFunc['db_query']('', '
		SELECT id_tag, tag
		FROM {db_prefix}tags
		WHERE tag LIKE {string:search}
			AND approved = {int:approved}
		ORDER BY tag
		LIMIT 10',
		array(
			'search' => $search,
			'approved' => 1,
		)
	);

	$tags = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$tags[] = array(
			'id_tag' => $row['id_tag'],
			'tag' => $smcFunc['htmlspecialchars']($row['tag']),
		);
	}
	$smcFunc['db_free_result']($dbresult);

	echo json_encode(array('success' => true, 'tags' => $tags));
	obExit(false);
}

function LoadTagsCSS()
{
	global $context, $modSettings;

	if (empty($modSettings['smftags_set_use_css_tags']))
		return;

	// Validate colors at runtime
	$bg_color = isset($modSettings['smftags_set_css_tag_background_color']) && preg_match('/^#[0-9a-fA-F]{3,6}$/', $modSettings['smftags_set_css_tag_background_color'])
		? $modSettings['smftags_set_css_tag_background_color'] : '#71a0b7';
	$font_color = isset($modSettings['smftags_set_css_tag_font_color']) && preg_match('/^#[0-9a-fA-F]{3,6}$/', $modSettings['smftags_set_css_tag_font_color'])
		? $modSettings['smftags_set_css_tag_font_color'] : '#ffffff';

	$context['html_headers'] .= '
<style>
.smftags_bar {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
  padding: 8px 16px;
  border-top: 1px solid rgba(0,0,0,0.07);
  background: rgba(0,0,0,0.015);
}

.smftags_label {
  color: #888;
  font-size: 0.8em;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-right: 2px;
}

.tags {
  list-style: none;
  margin: 0;
  padding: 0;
  display: inline-flex;
  flex-wrap: wrap;
  gap: 5px;
  align-items: center;
}

.tags li {
  display: inline-flex;
  align-items: center;
}

.tag_pill {
  display: inline-flex;
  align-items: center;
  background: ' . $bg_color . '18;
  border: 1px solid ' . $bg_color . '40;
  border-radius: 4px;
  padding: 0;
  transition: border-color 0.15s;
}

.tag_pill:hover {
  border-color: ' . $bg_color . '99;
}

.tag {
  color: ' . $bg_color . ';
  display: inline-block;
  font-size: 0.8em;
  line-height: 1;
  padding: 4px 8px;
  text-decoration: none;
  transition: background-color 0.15s, color 0.15s;
  border-radius: 4px;
}

.tag:link, .tag:visited {
  color: ' . $bg_color . ';
}

.tag:hover {
  background: ' . $bg_color . ';
  color: ' . $font_color . ';
  text-decoration: none;
}

.tag_delete {
  color: #bbb;
  text-decoration: none;
  font-size: 0.75em;
  line-height: 1;
  padding: 4px 5px 4px 0;
  border-left: 1px solid ' . $bg_color . '30;
  margin-left: -1px;
  transition: color 0.15s;
}

.tag_delete:hover {
  color: #c00;
}

.tag_add {
  display: inline-flex;
  align-items: center;
  gap: 2px;
  color: #999;
  font-size: 0.8em;
  line-height: 1;
  padding: 4px 8px;
  text-decoration: none;
  border: 1px dashed #ccc;
  border-radius: 4px;
  transition: color 0.15s, border-color 0.15s, background-color 0.15s;
}

.tag_add:hover {
  color: ' . $bg_color . ';
  border-color: ' . $bg_color . '80;
  background: ' . $bg_color . '0d;
}

#smftags_add_form {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

#smftags_add_input {
  font-size: 0.8em;
  padding: 3px 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  outline: none;
  width: 150px;
}

#smftags_add_input:focus {
  border-color: ' . $bg_color . ';
}

#smftags_add_error {
  font-size: 0.8em;
  color: #c00;
  margin-left: 4px;
}

.smftags_autocomplete {
  position: absolute;
  z-index: 1000;
  background: #fff;
  border: 1px solid #ccc;
  border-top: none;
  border-radius: 0 0 4px 4px;
  max-height: 200px;
  overflow-y: auto;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.smftags_autocomplete_item {
  padding: 6px 10px;
  cursor: pointer;
  font-size: 0.85em;
}

.smftags_autocomplete_item:hover,
.smftags_autocomplete_item.active {
  background: ' . $bg_color . '18;
  color: ' . $bg_color . ';
}

.smftags_editor_pills {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  margin-bottom: 6px;
}

.smftags_input_wrapper {
  position: relative;
  display: inline-block;
}

.smftags_post_input {
  font-size: 0.9em;
  padding: 3px 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  outline: none;
  width: 250px;
}

.smftags_post_input:focus {
  border-color: ' . $bg_color . ';
}

.smftags_post_add_btn {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  font-size: 0.85em;
  color: ' . $bg_color . ';
  border: 1px solid ' . $bg_color . '60;
  border-radius: 4px;
  background: ' . $bg_color . '0d;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.15s, border-color 0.15s;
}

.smftags_post_add_btn:hover {
  background: ' . $bg_color . '20;
  border-color: ' . $bg_color . ';
}

.smftags_post_error {
  font-size: 0.8em;
  color: #c00;
  margin-left: 6px;
  display: none;
}
</style>';
}

?>
