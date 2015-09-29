<?php
// CopyTopic
// Mod by karlbenson
// Taken over by JBlaze

if (!defined('SMF'))
	die('Hacking attempt...');

// CopyTopic
function CopyTopic()
{
	global $txt, $board, $topic, $scripturl, $sourcedir, $modSettings, $context;
	global $boards, $language, $user_info, $smcFunc;

	if (empty($topic))
		fatal_lang_error(1);

	// Permission check!
	isAllowedTo('copy');

	loadTemplate('CopyTopic');

	// Get a list of boards this moderator can move to.
	$request = $smcFunc['db_query']('', '
		SELECT b.id_board, b.name, b.child_level, c.name AS cat_name
		FROM {db_prefix}boards AS b
			LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		WHERE {query_see_board}',
		array()
	);
	
	$context['boards'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['boards'][] = array(
			'id' => $row['id_board'],
			'name' => $row['name'],
			'category' => $row['cat_name'],
			'child_level' => $row['child_level'],
			'selected' => !empty($_SESSION['copy_to_topic']) && $_SESSION['copy_to_topic'] == $row['id_board']
		);
	$smcFunc['db_free_result']($request);

	if (empty($context['boards']))
		fatal_lang_error('copytopic_noboards', false);

	$context['page_title'] = $txt['copytopic'];

	$context['back_to_topic'] = isset($_REQUEST['goback']);

	// Register this form and get a sequence number in $context.
	checkSubmitOnce('register');
}

// Execute the move.
function CopyTopic2()
{
	global $txt, $board, $topic, $scripturl, $sourcedir, $modSettings, $context;
	global $boards, $language, $user_info, $smcFunc;

	// Make sure this form hasn't been submitted before.
	checkSubmitOnce('check');

	// Permission check!
	isAllowedTo('copy');
	// Check Session
	checkSession();
	
	// The destination board must be numeric.
	$_POST['toboard'] = (int) $_POST['toboard'];
	if(empty($_POST['toboard']))
		fatal_lang_error('no_board');

	// Destination board exists
	$request = $smcFunc['db_query']('', '
		SELECT count_posts
		FROM {db_prefix}boards
		WHERE id_board = {int:toboard}
		LIMIT 1',
		array(
			'toboard' => $_POST['toboard'],
		)
	);
	
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_board');
		
	list($count_posts) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Strangely 0 = true, 1 = false
	$count_posts = empty($count_posts) ? 1 : 0 ;	

	// Can the user see that board
	$request = $smcFunc['db_query']('', '
		SELECT count(*)
		FROM {db_prefix}boards as b
		WHERE b.id_board = {int:toboard}
			AND {query_see_board}
		LIMIT 1',
		array(
			'toboard' => $_POST['toboard'],
		)
	);
	
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('copytopic_notallowed');

	// Remember this for later.
	$_SESSION['copy_to_topic'] = $_POST['toboard'];
	$topic = (int) $topic;
	
	// THE ACTUAL COPYING FUNCTION
	CopyTopics($topic, $_POST['toboard'], $count_posts);
	
	// Log that they copied this topic.
	if (!allowedTo('copy'))
		logAction('copy', array('topic' => $topic, 'board_from' => $board, 'board_to' => $_POST['toboard']));

	// Why not go back to the original board in case they want to keep moving?
	if (!isset($_REQUEST['goback']))
		redirectexit('board=' . $board . '.0');
	else
		redirectexit('topic=' . $topic . '.0');
}

function CopyTopics($original_topic_id, $board_id, $count_posts)
{
	global $txt, $scripturl, $sourcedir, $modSettings, $context, $user_info, $smcFunc;
	
	// Try to buy some time...
	@set_time_limit(0);
	
	// Check Topic Exists and get some info for now and later
	$request = $smcFunc['db_query']('', '
		SELECT id_board, id_poll, num_replies
		FROM {db_prefix}topics
		WHERE id_topic = {int:original_topic_id}
		LIMIT 1',
		array(
			'original_topic_id' => $original_topic_id,
		)
	);
	
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_topic_id');
		
	list($original_board_id, $original_poll_id, $num_posts) = $smcFunc['db_fetch_row']($request);
	// Its topic so it has 1 more reply that it states
	$num_posts++;
	$smcFunc['db_free_result']($request);

	// --- Copy Topic Entry ---

	// Query to Copy the Topic
	// The Columns for the table with our new topic and board ids.
	// id_topic is not listed because it is Auto-Incremented
	// id_board is set to our destination board
	// id_first_msg and id_last_msg are set to 0 for now(to prevent key errors if copying into same forum)
	$smcFunc['db_query']('', '
		INSERT INTO {db_prefix}topics (id_board, id_first_msg, id_last_msg, id_member_started, id_member_updated, is_sticky, id_poll, num_replies, num_views, locked)
		SELECT {int:board_id}, 0, 0, id_member_started, id_member_updated, is_sticky, id_poll, num_replies, num_views, locked
		FROM {db_prefix}topics
		WHERE id_topic = {int:original_topic_id}
		LIMIT 1',
		array(
			'original_topic_id' => $original_topic_id,
			'board_id' => $board_id,
		)
	);
	
	$topic_id = $smcFunc['db_insert_id']('{db_prefix}topics', 'id_topic');

	
	// --- Copy Messages Entries ---

	// Query to Copy EVERY post in the topic (Potentially could be a beast of a query)
	// The Columns for the table with our new topic and board ids.
	// id_msg is not listed because it is Auto-Incremented
	// id_msg_modified is made to the old msg id temporarily.
	$smcFunc['db_query']('', '
		INSERT INTO {db_prefix}messages (id_topic, id_board, id_msg_modified, id_member, poster_time, subject, poster_name, poster_email, poster_ip, modified_name, body, icon, approved)
		SELECT {int:topic_id}, {int:board_id}, id_msg, id_member, poster_time, subject, poster_name, poster_email, poster_ip, modified_name, body, icon, approved
		FROM {db_prefix}messages
		WHERE id_topic = {int:original_topic_id}
		ORDER BY id_msg ASC',
		array(
			'original_topic_id' => $original_topic_id,
			'topic_id' => $topic_id,
			'board_id' => $board_id,
		)
	);
	
	// --- Log Search Subject Cache (for searching) ---
	// Standard

	// Query to Copy EVERY matching row
	// The Columns for the table with our new topic id instead	
	$smcFunc['db_query']('', '
		INSERT INTO {db_prefix}log_search_subjects (word, id_topic)
		SELECT word, {int:topic_id}
		FROM {db_prefix}log_search_subjects
		WHERE id_topic = {int:original_topic_id}',
		array(
			'original_topic_id' => $original_topic_id,
			'topic_id' => $topic_id,
		)
	);
	
	// Custom Search Index?
	if (!empty($modSettings['search_custom_index_config']))
	{
		// Query to Copy EVERY matching row
		// The Columns for the table with our new msg id instead
		$smcFunc['db_query']('', '
			INSERT INTO {db_prefix}log_search_words (id_word, id_msg)
			SELECT w.id_word, m.id_msg
			FROM {db_prefix}log_search_words as w
				LEFT JOIN {db_prefix}messages as m ON (w.id_msg = m.id_msg_modified)
			WHERE m.id_topic = {int:topic_id}',
			array(
				'topic_id' => $topic_id,
			)
		);
	}
	
	/* Disabled v1.2 - Causing duplicate key issues
	// --- Log_Search_Results ---

	// Query to Copy EVERY matching row
	// Include this new topic in existing search results
	// The Columns for the table with our new msg id and topic id instead
	$smcFunc['db_query']('', '
		INSERT INTO {db_prefix}log_search_results (id_search, id_topic, id_msg, relevance, num_matches)
		SELECT r.id_search, {int:topic_id}, m.id_msg, r.relevance, r.num_matches
		FROM {db_prefix}log_search_results as r
			LEFT JOIN {db_prefix}messages as m ON (r.id_msg = m.id_msg_modified)
		WHERE m.id_topic = {int:topic_id}',
		array(
			'topic_id' => $topic_id,
		)
	);
	*/
	
	// --- Copy Attachments ---
	
	// * Only those less than 1mb in size, larger files may crash your server
	
	// We need to know where this thing is going.
	if (!empty($modSettings['currentAttachmentUploadDir']))
	{
		if (!is_array($modSettings['attachmentUploadDir']))
			$modSettings['attachmentUploadDir'] = unserialize($modSettings['attachmentUploadDir']);

		// Just use the current path for temp files.
		$attach_dir = $modSettings['attachmentUploadDir'][$modSettings['currentAttachmentUploadDir']];
		$id_folder = $modSettings['currentAttachmentUploadDir'];
	}
	else
	{
		$attach_dir = $modSettings['attachmentUploadDir'];
		$id_folder = 1;
	}
	
	$doattachments = 1;
	// First make sure the attachment dir is writable.
	if (!is_writable($attach_dir))
	{
		// Try to fix it.
		@chmod($attach_dir, 0777);

		// Guess that didn't work :/?
		if (!is_writable($attach_dir))
			$doattachments = 0;
	}
	
	// Check how we are on directory filesize?
	if($doattachments && !empty($modSettings['attachmentDirSizeLimit']))
		$doattachments = attachmentDirectorySizeCheck($attach_dir);
	
	// Right try to go ahead with the attachments.
	if(!empty($doattachments))
	{
		// id_attach is not listed because it is Auto-Incremented
		// Matching the id_msg_modified and switching it for the new id_msg
		// id_folder is new in 2.0 (for multiple attachment directory support), will be changed later once its been copied
		$smcFunc['db_query']('', '
			INSERT INTO {db_prefix}attachments (fileext, mime_type, approved, id_folder, id_thumb, id_msg, id_member, attachment_type, filename, size, downloads, width, height)
			SELECT fileext, a.mime_type, a.approved, a.id_folder, a.id_attach, m.id_msg, a.id_member, a.attachment_type, a.filename, a.size, a.downloads, a.width, a.height
			FROM {db_prefix}attachments as a
				LEFT JOIN {db_prefix}messages as m ON (a.id_msg = m.id_msg_modified)
			WHERE m.id_topic = {int:topic_id}
				AND size < 1048600
			ORDER BY id_attach ASC',
			array(
				'topic_id' => $topic_id,
			)
		);
		
		// Grab and store the new vs old attachment ids in a array (we temporarily stored the old ID in the id_thumb column)
		$request = $smcFunc['db_query']('', '
			SELECT a.id_attach, a.id_thumb
			FROM {db_prefix}attachments as a
				LEFT JOIN {db_prefix}messages as m ON (a.id_msg = m.id_msg)
			WHERE m.id_topic = {int:topic_id}
			ORDER BY id_attach ASC',
			array(
				'topic_id' => $topic_id,
			)
		);
		
		// Did it copy any attachment entries in the db?
		if($smcFunc['db_num_rows']($request) != 0)
		{
			$ids = array();
			while($row = $smcFunc['db_fetch_assoc']($request))
				$ids[$row['id_attach']] = $row['id_thumb'];
			// Tidy up
			$smcFunc['db_free_result']($request);
			unset($row);
		
			// Re-Associate Thumbs with our new attachment and thumb ids
			// Grab all the information about the attachments so we can re-associate them to the correct
			$request = $smcFunc['db_query']('', '
				SELECT a.id_attach as attach_id, IFNULL(a3.id_attach, 0) as thumb_id
				FROM {db_prefix}attachments as a
					LEFT JOIN {db_prefix}messages as m ON (a.id_msg = m.id_msg)
					LEFT JOIN {db_prefix}attachments as a2 ON (a.id_thumb = a2.id_attach)
					LEFT JOIN {db_prefix}attachments as a3 ON (a2.id_thumb = a3.id_thumb AND m.id_msg = a3.id_msg)
				WHERE m.id_topic = {int:topic_id}
					AND a2.id_attach != 0
				',
				array(
					'topic_id' => $topic_id,
				)
			);
			$change = array();
			while($row = $smcFunc['db_fetch_assoc']($request))
				$change[$row['attach_id']] = $row['thumb_id'];

				foreach($change as $a => $b)
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}attachments
					SET id_thumb = {int:thumb_id}
					WHERE id_attach = {int:attach_id}
						AND id_attach != 0',
					array(
						'attach_id' => $a,
						'thumb_id' => (int) $b,
					)
				);
			}
			unset($a, $b, $change, $row);
			
			// Grab all the information about the attachments (including thumbs) attached to this topic
			$request = $smcFunc['db_query']('', '
				SELECT 	a.id_attach as file_id, a.filename as filename, a.size as filesize,
						a.id_thumb as thumb_id, a2.filename as thumbname, a2.size as thumbsize,
						a.id_folder as folder, a2.id_folder as thumbfolder
				FROM {db_prefix}attachments as a
					LEFT JOIN {db_prefix}messages as m ON (a.id_msg = m.id_msg)
					LEFT JOIN {db_prefix}attachments as a2 ON (a.id_thumb = a2.id_attach)
				WHERE m.id_topic = {int:topic_id}
					AND	a.attachment_type != 3
				ORDER BY a.id_attach ASC',
				array(
					'topic_id' => $topic_id,
				)
			);
		
			// Attachments found
			if($smcFunc['db_num_rows']($request) != 0)
			{
				$attachments = array();
				// For each attachment, we will try to copy the file
				while($row = $smcFunc['db_fetch_assoc']($request))
				{
					$row['original_id'] = $ids[$row['file_id']];
					$row['original_thumb_id'] = empty($row['thumb_id']) ? 0 : $ids[$row['thumb_id']] ;
					$attachments[] = $row;
				}
				// Tidy up
				$smcFunc['db_free_result']($request);
				unset($ids, $row);
				
				foreach($attachments as $row)
				{
					// Is there enough space for the copied attachment + thumb
					if(empty($modSettings['attachmentDirSizeLimit']) || 
						($doattachments + (int) $row['filesize'] + (int) $row['thumbsize'] < $modSettings['attachmentDirSizeLimit'] * 1024))
					{
						// Copy the attachment
						if($filename = copyAttachment($row, $attach_dir))
						{
							// Successly copied the attachment
							// Update the attachment db entry with the new filename (only if new filename was generated);
							$smcFunc['db_query']('', '
								UPDATE {db_prefix}attachments
								SET filename = {string:filename}, id_folder = {int:id_folder}
								WHERE id_attach = {int:file_id}
								',
								array(
									'file_id' => $row['file_id'],
									'filename' => $filename,
									'id_folder' => $id_folder,
								)
							);
							
							// Increase the size
							$doattachments = $doattachments + (int) $row['filesize'] + (int) $row['thumbsize'];
							
							// If theres a thumb, rename that aswell
							if(!empty($row['thumb_id']))
							{
								$smcFunc['db_query']('', '
									UPDATE {db_prefix}attachments
									SET filename = {string:thumbname}, id_folder = {int:id_folder}
									WHERE id_attach = {int:thumb_id}
									',
									array(
										'thumb_id' => $row['thumb_id'],
										'thumbname' => $filename.'_thumb',
										'id_folder' => $id_folder,
									)
								);
							}
						}
						else
							// Copying the attachment failed, so delete the db entries
							$smcFunc['db_query']('', '
								DELETE FROM {db_prefix}attachments
								WHERE id_attach = {int:file_id}
									OR id_attach = {int:thumb_id}
								',
								array(
									'file_id' => $row['file_id'],
									'thumb_id' => $row['thumb_id'],
								)
							);
					}
					else
						// Ran out of space or error, so delete the db entries
						$smcFunc['db_query']('', '
							DELETE FROM {db_prefix}attachments
							WHERE id_attach = {int:file_id}
								OR id_attach = {int:thumb_id}
							',
							array(
								'file_id' => $row['file_id'],
								'thumb_id' => $row['thumb_id'],
							)
						);

					// Tidy up
					unset($row);
				}
				// Tidy up
				unset($attachments, $ids, $row);
			}
		}
	}
	
	//  Fix Attachment Icon, if icon is set to clip and no attachments were copied.
	$request = $smcFunc['db_query']('', '
		SELECT count(a.id_attach) as attachments, m.icon, m.id_msg
		FROM {db_prefix}messages as m
			LEFT JOIN {db_prefix}attachments as a ON (m.id_msg = a.id_msg)
		WHERE m.id_topic = {int:topic_id}
		GROUP BY a.id_attach
		',
		array(
			'topic_id' => $topic_id,
		)
	);
	$fix = array();
	while($row = $smcFunc['db_fetch_assoc']($request))
	{
		if($row['attachments'] == 0 && $row['icon'] == 'clip')
			$fix[] = $row['id_msg'];
	}
	if(!empty($fix))
	{
		// So change it back to default
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}messages
			SET icon = {string:default_icon}
			WHERE id_topic = {int:topic_id}
				AND id_msg IN ({array_int:id_msgs})
				AND icon = {string:clip}
			',
			array(
				'topic_id' => $topic_id,
				'id_msgs' => $fix,
				'default_icon' => 'xx',
				'clip' => 'clip',
			)
		);	
	}
	// Tidy up
	unset($fix, $row);
	
	// --- Fix some stats and logs ---
	
	// Fix id_msg_modified to the New id_msg
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}messages
		SET id_msg_modified = id_msg
		WHERE id_topic = {int:topic_id}',
		array(
			'topic_id' => $topic_id,
		)
	);
	
	// Grab First & Last Message Id
	$request = $smcFunc['db_query']('', '
		SELECT max(id_msg) as last, min(id_msg) as first
		FROM {db_prefix}messages
		WHERE id_topic = {int:topic_id}',
		array(
			'topic_id' => $topic_id,
		)
	);
	$row = $smcFunc['db_fetch_assoc']($request);
	
	// Update the topic info with that info
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}topics
		SET id_first_msg = {int:first}, id_last_msg = {int:last}
		WHERE id_topic = {int:topic_id}',
		array(
			'topic_id' => $topic_id,
			'first' => $row['first'],
			'last' => $row['last'],
		)
	);

	// Update log topics (for this user only)
	$smcFunc['db_query']('', '
		REPLACE
		INTO {db_prefix}log_topics
			(id_topic, id_member, id_msg)
		VALUES ({int:topic_id}, {int:user_id}, {int:last})
		',
		array(
			'topic_id' => $topic_id,
			'last' => $row['last'],
			'user_id' => $context['user']['id'],
		)
	);

	// Update log boards (for this user only)
	$smcFunc['db_query']('', '
		REPLACE
		INTO {db_prefix}log_boards
			(id_board, id_member, id_msg)
		VALUES ({int:board_id}, {int:user_id}, {int:last})
		',
		array(
			'board_id' => $board_id,
			'last' => $row['last'],
			'user_id' => $context['user']['id'],
		)
	);
		
	require_once($sourcedir . '/Subs-Post.php');
	updateLastMessages($board_id, $row['last']);
	
	// --- Fix Post Counts ---
	
	// Posts in the board we're copying the topic to count, so we need to get the figures for each
	if($count_posts)
	{
		// Increase the stats for the board
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}boards
			SET num_posts = num_posts + {int:num_posts}, num_topics = num_topics + 1
			WHERE id_board = {int:board_id}
			',
			array(
				'board_id' => $board_id,
				'num_posts' => $num_posts,
			)
		);
	
		// How many posts have been made by each user in the copied topic?
		$request = $smcFunc['db_query']('', '
			SELECT count(*) as increase, id_member
			FROM {db_prefix}messages
			WHERE id_topic = {int:topic_id}
				AND id_member > 0
			GROUP BY id_member
			',
			array(
				'topic_id' => $topic_id,
			)
		);
		
		// Any members to update (non-guests);
		if($smcFunc['db_num_rows']($request) != 0)
		{
			$members = $increase = array();
			// Prepare the information in arrays for easy update
			while($row = $smcFunc['db_fetch_assoc']($request))
			{
				$increase[$row['id_member']] = $row['increase'];
				// Store the member ids, as we will need to Update PostGroups
				if(!in_array($row['id_member'], $members))
					$members[] = $row['id_member'];
			}
			
			// Update each users postcount accordingly.  Could add significant number of queries for large topics.
			foreach($increase as $a => $b)
			{
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}members
					SET posts = posts + {int:posts}
					WHERE id_member = {int:id_member}
					',
					array(
						'id_member' => $a,
						'posts' => $b,
					)
				);
			}
			unset($increase, $a, $b);
			
			// Update PostGroups for member who's postcounts have been altered.
			updateStats('postgroups', $members);
		}
	}
	
	$_SESSION['last_read_topic'] = $original_topic_id;
	
	// --- Copy Poll ---
	
	// Is it a poll?
	if($original_poll_id > 0)
	{
		// Copy the poll
		// id_poll is not listed because it is Auto-Incremented
		// The rest are the same
		$smcFunc['db_query']('', '
			INSERT INTO {db_prefix}polls (question, voting_locked, max_votes, expire_time, hide_results, change_vote, id_member, poster_name)
			SELECT question, voting_locked, max_votes, expire_time, hide_results, change_vote, id_member, poster_name
			FROM {db_prefix}polls
			WHERE id_poll = {int:original_poll_id}
			',
			array(
				'original_poll_id' => $original_poll_id,
			)
		);

		// Save the new poll id
		$poll_id = $smcFunc['db_insert_id']('{db_prefix}polls', 'id_poll');

		// Update the topic info with the poll id
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}topics
			SET id_poll = {int:poll_id}
			WHERE id_topic = {int:topic_id}
			',
			array(
				'poll_id' => $poll_id,
				'topic_id' => $topic_id,
			)
		);
	
		// --- Copy Poll Choices ---

		// Query to Select & Copy ALL the poll choices in one query.
		// id_poll is set to our new poll id
		$smcFunc['db_query']('', '
			INSERT INTO {db_prefix}poll_choices (id_poll, id_choice, label, votes)
			SELECT {int:poll_id}, id_choice, label, votes
			FROM {db_prefix}poll_choices
			WHERE id_poll = {int:original_poll_id}
			ORDER BY id_choice ASC
			',
			array(
				'poll_id' => $poll_id,
				'original_poll_id' => $original_poll_id,
			)
		);
		
		// --- Copy Log Polls ---

		// Query to Select & Copy the log polls in one query.  (depending on the no. of voters, it could be heavy)
		// id_poll is set to our new poll id
		$smcFunc['db_query']('', '
			INSERT INTO {db_prefix}log_polls(id_poll, id_member, id_choice)
			SELECT {int:poll_id}, id_member, id_choice
			FROM {db_prefix}log_polls
			WHERE id_poll = {int:original_poll_id}
			',
			array(
				'poll_id' => $poll_id,
				'original_poll_id' => $original_poll_id,
			)
		);
	}
	
	// --- Calender Events ---
	
	// Query to Copy each calendar entry related to this topic
	// id_event is not listed as its auto-incremented
	// id_topic and id_board are set to our new poll id
	$smcFunc['db_query']('', '
		INSERT INTO {db_prefix}calendar (start_date, end_date, id_board, id_topic, title, id_member)
		SELECT start_date, end_date, {int:board_id}, {int:topic_id}, title, id_member
		FROM {db_prefix}calendar
		WHERE id_topic = {int:original_topic_id}
		ORDER BY id_event ASC
		',
		array(
			'topic_id' => $topic_id,
			'board_id' => $board_id,
			'original_topic_id' => $original_topic_id,
		)
	);
	
	updateStats('topic');
	updateStats('message');
	updateSettings(array(
		'calendar_updated' => time(),
	));

}

// Sets up for copying multiple topics
function CopyMultipleTopics()
{
	global $board, $sourcedir, $user_info, $modSettings, $smcFunc;

	// Empty array?
	if (empty($_REQUEST['topics']))
		return;
	
	// If copying multiple topics, we want them in the exact same order.
	$_REQUEST['topics'] = array_reverse($_REQUEST['topics']);
	
	$topics = array();
	foreach ($_REQUEST['topics'] as $topic)
	{
		$topic = (int) $topic;
		if(!empty($topic))
			$topics[] = $topic;
	}
	unset($topic);
	
	// Destination board empty or equal to 0?
	// Uses move_to because hijacking the move to dropdown
	if (empty($_REQUEST['move_to']))
		$_REQUEST['move_to'] = $board;
		
	$_REQUEST['move_to'] = (int) $_REQUEST['move_to'];

	// Permission check!
	isAllowedTo('copy');
	// Check Session
	checkSession();

	// Destination board exists
	$request = $smcFunc['db_query']('', '
		SELECT count_posts
		FROM {db_prefix}boards
		WHERE id_board = {int:move_to}
		LIMIT 1
		',
		array(
			'move_to' => $_REQUEST['move_to'],
		)
	);
	
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('no_board');
		
	list($count_posts) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Strangely 0 = true, 1 = false
	$count_posts = empty($count_posts) ? 1 : 0 ;	

	// Can the user see that board
	$request = $smcFunc['db_query']('', '
		SELECT count(*)
		FROM {db_prefix}boards
		WHERE id_board = {int:move_to}
			AND {query_see_board}
		LIMIT 1
		',
		array(
			'move_to' => $_REQUEST['move_to'],
		)
	);
	
	if ($smcFunc['db_num_rows']($request) == 0)
		fatal_lang_error('copytopic_notallowed');

	// Remember this for later.
	$_SESSION['copy_to_topic'] = $_REQUEST['move_to'];
	
	foreach($topics as $topic)
	{
		// THE ACTUAL COPYING FUNCTION
		CopyTopics($topic, $_REQUEST['move_to'], $count_posts);
	
		// Log that they copied this topic.
		if (allowedTo('copy'))
			logAction('copy', array('topic' => $topic, 'board_from' => $board, 'board_to' => $_REQUEST['move_to']));
	}
	
	// Why not go back to the original board in case they want to keep moving?
	if (!isset($_REQUEST['goback']))
		redirectexit('board=' . $board . '.0');
	else
		redirectexit();
	
}

// Modified from smf code to check whether we have enough space to store the attachments
function attachmentDirectorySizeCheck($attach_dir)
{
	global $modSettings;
	
	// Are there any limits on the size of the attachment directory
	if (!empty($modSettings['attachmentDirSizeLimit']))
	{
		// Make sure the directory isn't full.
		$dirSize = 0;
		$dir = @opendir($attach_dir);
		
		// Read each file
		while ($file = readdir($dir))
		{
			if (substr($file, 0, -1) == '.')
				continue;

			$dirSize += filesize($attach_dir . '/' . $file);
		}
		closedir($dir);

		// Too big! 
		if ($dirSize > $modSettings['attachmentDirSizeLimit'] * 1024)
			return 0;
	}
	// Else we are ok to go ahead, pass back the current size
	return $dirSize ;
}

function CopyAttachment($row, $attach_dir)
{
	global $modSettings;
	
	// Original attachment link
	$row['original_file_link'] = getAttachmentFilename($row['filename'], $row['original_id'], $row['folder']);
	
	// New Attachment
	// Ay caramba!, we can't have two files with exact same name, so we need a new filename
	if(empty($modSettings['attachmentEncryptFilenames']))
	{
		$row['filename'] = GenerateUniqueFilename($attach_dir.'/', $row['filename']);
		// Couldn't generate a new filename so return
		if(empty($row['filename']))
			return 0;
		else
			// Destination attachment link
			$row['file_link'] = $attach_dir.'/'.$row['filename'];
	}
	else
		// Destination attachment link
		$row['file_link'] = $attach_dir.'/'. getAttachmentFilename($row['filename'], $row['file_id'], $attach_dir, true);
		
	// Now copy the file
	if(copy($row['original_file_link'], $row['file_link']) !== false)
	{
		// Attempt to chmod it.
		@chmod($row['file_link'], 0644);
	
		//Success
		// But what about the thumbnail
		if(!empty($row['thumb_id']))
		{
			// Original attachment link
			$row['original_thumb_link'] = getAttachmentFilename($row['thumbname'], $row['original_thumb_id'], $row['thumbfolder']);
			
			if(empty($modSettings['attachmentEncryptFilenames']))
			{
				$row['thumbname'] = $row['filename'].'_thumb';
				$row['thumb_link'] = $attach_dir.'/'.$row['thumbname'];
			}
			else
				$row['thumb_link'] = $attach_dir.'/'.getAttachmentFilename($row['thumbname'], $row['thumb_id'], $attach_dir, true);
				
			// If it fails to copy the thumb, send back 0
			if(copy($row['original_thumb_link'], $row['thumb_link']) !== false)
				// Attempt to chmod it.
				@chmod($row['thumb_link'], 0644);
			else
				return 0;
		}
		//send back the new filename to store in the db
		return $row['filename'];
	}
	// If haven't exited, we must have failed
	return 0;
}

// If NOT encrypting filenames, we need a unique filename
function GenerateUniqueFilename($folder, $originalfilename)
{
	global $context;
	
	if(preg_match('~(.*?)([0-9]{1,2}|)(\.(?:[^\.]{1,3}\.[^\.]{1,3}|[^\.]{1,5}))$~i'.($context['utf8'] ? 'u' : ''), $originalfilename, $parts))
	{
		// Split to name, $no & extension (if there isn't a number at the end, set it to 0 by casting as int)
		list($name, $no, $ext) = array($parts[1], (int) $parts[2], $parts[3]);

		// Too long filename, go in 2 characters (to make space for a number)
		if(strlen($name) > 60 )
			$name = substr($name, 0, strlen($name) - 2);

		// What if the filename is just a number eg 1234.gif
		if(empty($name) && !empty($no))
			// So we will try to generate a filename starting at 1235.gif
			$startat = $no;
		else
			// So we start trying to do
			$startat = 1;
			
		// Try to generate a unique filename in 50 tries
		for($i=$startat; $i<=($startat+50); $i++)
		{
			// If we've found a unique filename, then break the for loop
			if(file_exists($folder.$name.$i.$ext) === false)
				return $name.$i.$ext;
		}
	}

	// Still not exited? Try another way of generating a unique filename
	// If we couldn't parse the filename at all, then we will use the entire filename as the extension (and prepend it)
	if(empty($name) && empty($no) && empty($ext))
	{	
		$name = '';
		$ext = $originalfilename;
	}
	
	for($s=1; $s <= 20; $s++)
	{
		$i = rand(127,997531);
		// If we've found a unique filename, then break the for loop
		if(!file_exists($folder.$name.$i.$ext))
			return $name.$i.$ext;
	}
	// If not exited by now, failed to generate unique filename
	return 0;
}

?>