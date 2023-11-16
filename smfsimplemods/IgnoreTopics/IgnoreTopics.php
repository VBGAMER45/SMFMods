<?php

if (!defined('SMF'))
	die('Hacking attempt...');

// Called via UnreadReplies/UnreadTopics
// Passed array of topics is added to users ignored
// Then back to location
function IgnoreMultipleTopics()
{
	global $board, $sourcedir, $user_info, $modSettings, $smcFunc;

	// For when we have to go back, or afterwards
	$redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : (isset($_SESSION['old_url']) ? $_SESSION['old_url'] : '');
	
	// Guests shouldn't have got this far
	is_not_guest();

	// Register this form and get a sequence number in $context.
	checkSubmitOnce('register');
	
	// Empty array? OR not allowed to ignore topics
	if (empty($_REQUEST['topics']) || !allowedTo('ignore_topics'))
		return redirectexit($redirect_url);

	$topics = array();
	foreach ($_REQUEST['topics'] as $topic)
	{
		$topic = (int) $topic;
		if(!empty($topic))
			$topics[] = $topic;
	}
	unset($_REQUEST['topics'], $topic);
	
	// Check the topics exist
	$request = $smcFunc['db_query']('', '
		SELECT id_topic
		FROM {db_prefix}topics
		WHERE id_topic IN ({array_int:topics})
		',
		array(
			'topics' => $topics,
		)
	);
	unset($topics);
	
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('topics_not_found');
	
	// Store the information in an array ready to be sent to the db, along with username, and the date_ignored
	$topicsArray = array();
	while($row = $smcFunc['db_fetch_assoc']($request))
		$topicsArray[] = array($user_info['id'], $row['id_topic'], time());
	
	// Free the result
	$smcFunc['db_free_result']($request);

	// Now add all ignored topics - replace, just in case one
	$smcFunc['db_insert']('replace',
		'{db_prefix}ignore_topics',
		array('id_member' => 'int', 'id_topic' => 'int', 'date_ignored' => 'int'),
			$topicsArray,
		array('id_member', 'id_topic', 'date_ignored')
	);
	
	// Tidy up
	unset($topicsArray);
	
	// Integrity Check
	// Check all existing ignore topics for references to topics which no longer exist and delete them.
	$smcFunc['db_query']('', '
		DELETE it
		FROM {db_prefix}ignore_topics AS it
			LEFT JOIN {db_prefix}topics as t ON (it.id_topic = t.id_topic)
		WHERE IFNULL(t.id_topic, 0) = 0',
		array()
	);
	
	// If we set a limit (admin > boards > settings) and we've NOT set permissions to allow this user unlimited
	if(!empty($modSettings['limit_ignore_topics']) && !allowedTo('unlimited_ignore_topics'))
	{
		// How many ignored topics?
		$request = $smcFunc['db_query']('', '
			SELECT count(*)
			FROM {db_prefix}ignore_topics
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => $user_info['id'],
			)
		);
		list($my_ignored) = $smcFunc['db_fetch_row']($request);
		
		// Free the result
		$smcFunc['db_free_result']($request);
		
		// Force integer
		$my_ignored = (int) $my_ignored;
		
		// Exceeding the limit? We may need to remove the oldest ones to bring back us under the limit.
		if($my_ignored > $modSettings['limit_ignore_topics'])
		{
			// By how many?
			$too_many = $my_ignored - $modSettings['limit_ignore_topics'];
			
			// Don't want to overload things if a user was unlimited on an uber forum, and now is limited.
			// Break up this query upto 500's
			for($i=0;$i<=100;$i++)
			{
				// Grab upto oldest 500
				$request = $smcFunc['db_query']('', '
					SELECT id_topic
					FROM {db_prefix}ignore_topics
					WHERE id_member = {int:id_member}
					ORDER BY date_ignored ASC
					LIMIT {int:limit}
					',
					array(
						'id_member' => $user_info['id'],
						'limit' => $too_many >= 500 ? 500 : $too_many,
					)
				);
				$topicsArray = array();
				while(list($id_topic) = $smcFunc['db_fetch_row']($request))
					$topicsArray[] = (int) $id_topic ;

				// Free the result
				$smcFunc['db_free_result']($request);
				
				// Delete all the oldest ones we
				$smcFunc['db_query']('', '
					DELETE
					FROM {db_prefix}ignore_topics
					WHERE id_member = {int:id_member}
						AND id_topic '. ( count($topicsArray) == 1 ? '= {int:topic}' : 'IN ({array_int:topics})'),
					array(
						'id_member' => $user_info['id'],
						'topics' => $topicsArray,
						'topic' => $topicsArray[0],
					)
				);
				
				// Reduce the amount left to do
				$too_many = $too_many - count($topicsArray);
				
				// Tidy up
				unset($topicsArray);
				
				// Finished before 100 steps
				if($too_many <= 0)
					break;
				
				// Free the result
				$smcFunc['db_free_result']($request);
			}
		}
	}
	
	// Now return to back to unread/unreadreplies
	return redirectexit($redirect_url);
}
	
function list_getTopicIgnoredCount($memID)
{
	global $smcFunc, $user_info;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}ignore_topics AS it
			INNER JOIN {db_prefix}topics AS t ON (t.id_topic = it.id_topic)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE it.id_member = {int:selected_member}
			AND {query_see_board}
			AND IFNULL(it.id_topic, 0) != {int:zero}
			AND t.approved = {int:is_approved}',
		array(
			'selected_member' => $memID,
			'is_approved' => 1,
			'zero' => 0,
		)
	);
	list ($totalIgnored) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $totalIgnored;
}

function list_getTopicIgnored($start, $items_per_page, $sort, $memID)
{
	global $smcFunc, $txt, $scripturl, $user_info;

	// All the Ignored Topics
	$request = $smcFunc['db_query']('', '
		SELECT
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1 AS new_from, b.id_board, b.name,
			t.id_topic, ms.subject, ms.id_member, IFNULL(mem.real_name, ms.poster_name) AS real_name_col,
			ml.id_msg_modified, ml.poster_time, ml.id_member AS id_member_updated,
			IFNULL(mem2.real_name, ml.poster_name) AS last_real_name, it.date_ignored
		FROM {db_prefix}ignore_topics AS it
			INNER JOIN {db_prefix}topics AS t ON (t.id_topic = it.id_topic AND t.approved = {int:is_approved})
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board AND {query_see_board})
			INNER JOIN {db_prefix}messages AS ms ON (ms.id_msg = t.id_first_msg)
			INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = ms.id_member)
			LEFT JOIN {db_prefix}members AS mem2 ON (mem2.id_member = ml.id_member)
			LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
			LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = b.id_board AND lmr.id_member = {int:current_member})
		WHERE it.id_member = {int:selected_member}
			AND IFNULL(it.id_topic, 0) != {int:zero}
		ORDER BY ' . $sort . '
		LIMIT ' . $start . ', ' . $items_per_page,
		array(
			'current_member' => $user_info['id'],
			'is_approved' => 1,
			'selected_member' => $memID,
			'zero' => 0,
		)
	);
	$ignored_topics = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		censorText($row['subject']);

		$ignored_topics[] = array(
			'id' => $row['id_topic'],
			'poster_link' => empty($row['id_member']) ? $row['real_name_col'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name_col'] . '</a>',
			'poster_updated_link' => empty($row['id_member_updated']) ? $row['last_real_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member_updated'] . '">' . $row['last_real_name'] . '</a>',
			'subject' => $row['subject'],
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['subject'] . '</a>',
			'new' => $row['new_from'] <= $row['id_msg_modified'],
			'new_from' => $row['new_from'],
			'updated' => timeformat($row['poster_time']),
			'new_href' => $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['new_from'] . '#new',
			'new_link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['new_from'] . '#new">' . $row['subject'] . '</a>',
			'board_link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>',
			'date_ignored' => timeformat($row['date_ignored']),
		);
	}
	$smcFunc['db_free_result']($request);

	return $ignored_topics;
}

function ignoretopics($memID)
{
	global $txt, $user_info, $context, $modSettings, $smcFunc, $cur_profile, $sourcedir, $scripturl, $settings;

	 if(!allowedTo('ignore_topics'))
		fatal_lang_error('no_access');
	
	// Gonna want this for the list.
	require_once($sourcedir . '/Profile-Modify.php');
	require_once($sourcedir . '/Subs-List.php');

	// Is there an updated message to show?
	if (isset($_GET['updated']))
		$context['profile_updated'] = $txt['ignored_topics_updated'];
	
	// Empty all
	if(isset($_REQUEST['empty']))
	{
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}ignore_topics
			WHERE id_member = {int:current_member}
			',
			array(
				'current_member' => $memID,
			)
		);
		
		// Inform of success
		$context['profile_updated'] = $txt['unignored_all_topics'];
	}
	
	// Now do the topic notifications.
	$listOptions = array(
		'id' => 'topic_ignore_list',
		'title' => '&nbsp;<img src="' . $settings['images_url'] . '/buttons/ignore.gif" alt="" align="top" />&nbsp;' . $txt['ignore_topics'],
		'width' => '100%',
		'items_per_page' => $modSettings['defaultMaxMessages'],
		'no_items_label' => $txt['ignore_topics_none'],
		'no_items_align' => 'center',
		'base_href' => $scripturl . '?action=profile;area=ignoretopics;u=' . $memID,
		'default_sort_col' => 'date_ignored',
		'get_items' => array(
			'function' => 'list_getTopicIgnored',
			'params' => array(
				$memID,
			),
		),
		'get_count' => array(
			'function' => 'list_getTopicIgnoredCount',
			'params' => array(
				$memID,
			),
		),
		'columns' => array(
			'subject' => array(
				'header' => array(
					'value' => $txt['subject'],
				),
				'data' => array(
					'function' => function($topic) use ($settings, $txt)
					{

						$link = $topic['link'];

						if ($topic['new'])
							$link .= ' <a href="' . $topic['new_href'] . '"><img src="' . $settings['lang_images_url'] . '/new.gif" alt="' . $txt['new'] . '" /></a>';

						$link .= '<br /><span class="smalltext"><i>' . $txt['in'] . ' ' . $topic['board_link'] . '</i></span>';

						return $link;
					}
				),
				'sort' => array(
					'default' => 'ms.subject',
					'reverse' => 'ms.subject DESC',
				),
			),
			'started_by' => array(
				'header' => array(
					'value' => $txt['started_by'],
				),
				'data' => array(
					'db' => 'poster_link',
				),
				'sort' => array(
					'default' => 'real_name_col',
					'reverse' => 'real_name_col DESC',
				),
			),
			'date_ignored' => array(
				'header' => array(
					'value' => $txt['date_ignored'],
				),
				'data' => array(
					'db' => 'date_ignored',
				),
				'sort' => array(
					'default' => 'it.date_ignored DESC',
					'reverse' => 'it.date_ignored',
				),
			),
			'delete' => array(
				'header' => array(
					'value' => '<input type="checkbox" class="check" onclick="invertAll(this, this.form);" />',
					'style' => 'width: 4%;',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="ignore_topics[]" value="%1$d" class="check" />',
						'params' => array(
							'id' => false,
						),
					),
					'style' => 'text-align: center;',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=profile;area=ignoretopics;u='.$memID.';save',
			'include_sort' => true,
			'include_start' => true,
			'hidden_fields' => array(
				'u' => $memID,
				'sa' => $context['menu_item_selected'],
				'sc' => $context['session_id'],
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'bottom_of_list',
				'value' => '<input type="submit" name="edit_ignore_topics" value="' . $txt['unignore_topics'] . '" />',
				'class' => 'windowbg',
				'align' => 'right',
			),
		),
	);

	// Create the notification list.
	createList($listOptions);

	loadThemeOptions($memID);
}

function profile_unignore_topics($memID)
{
	global $smcFunc;
	
	// No Topics to unignore?
	if (isset($_POST['sa']) && $_POST['sa'] == 'ignoretopics' && empty($_POST['ignore_topics']))
		return;
	
	// Not allowed, so shouldn't have got this far
	if(!allowedTo('ignore_topics'))
		return;
	
	// Force an array
	if (!is_array($_POST['ignore_topics']))
		$_POST['ignore_topics'] = array ( $_POST['ignore_topics'] );

	// Force integer, and don't retain any which are empty or zero or negative
	$arr = array();
	foreach ($_POST['ignore_topics'] as $k => $d )
	{
		$d = (int) $d;
		if (!empty($d) && $d > 0)
			$arr[$k] = $d;
	}
	unset($_POST['ignore_topics'], $k, $d);
	
	// No topics to to update, return
	if(empty($arr))
		return;
		
	// We're not saving, but actually deleting
	// So do that here
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}ignore_topics
		WHERE id_member = {int:current_member}
			AND id_topic IN ({array_int:topics})
		',
		array(
			'topics' => $arr,
			'current_member' => $memID,
		)
	);
	unset($arr);
}

function admin_ignore_topics()
{
	global $smcFunc, $scripturl, $txt;
	
	if(isset($_GET['emptyignored']))
	{
		$smcFunc['db_query']('truncate_table', '
			TRUNCATE {db_prefix}ignore_topics',
			array(
			)
		);
	
		// Now redirect
		redirectexit('action=admin;area=manageboards;sa=settings');
	}
	
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*), COUNT(DISTINCT id_topic), COUNT(DISTINCT id_member)
		FROM {db_prefix}ignore_topics',
		array(
		)
	);

	list($ignored_total, $ignored_unique, $ignored_members) = $smcFunc['db_fetch_row']($request);
	$txt['allow_ignore_topics'] = sprintf($txt['allow_ignore_topics'], (int) $ignored_unique, (int) $ignored_total, (int) $ignored_members,
		$scripturl . '?action=admin;area=manageboards;sa=settings;emptyignored');

}
?>