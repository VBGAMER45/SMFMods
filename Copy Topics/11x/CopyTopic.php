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
	global $db_prefix, $ID_MEMBER, $boards, $language, $user_info, $func;

	if (empty($topic))
		fatal_lang_error(1);

	// Permission check!
	isAllowedTo('copy');

	loadTemplate('CopyTopic');

	// Get a list of boards this moderator can move to.
	$request = db_query("
		SELECT b.ID_BOARD, b.name, b.childLevel, c.name AS catName
		FROM {$db_prefix}boards AS b
			LEFT JOIN {$db_prefix}categories AS c ON (c.ID_CAT = b.ID_CAT)
		WHERE $user_info[query_see_board]", __FILE__, __LINE__);
	$context['boards'] = array();
	while ($row = mysql_fetch_assoc($request))
		$context['boards'][] = array(
			'id' => $row['ID_BOARD'],
			'name' => $row['name'],
			'category' => $row['catName'],
			'child_level' => $row['childLevel'],
			'selected' => !empty($_SESSION['copy_to_topic']) && $_SESSION['copy_to_topic'] == $row['ID_BOARD']
		);
	mysql_free_result($request);

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
	global $db_prefix, $ID_MEMBER, $boards, $language, $user_info, $func;

	// Make sure this form hasn't been submitted before.
	checkSubmitOnce('check');

	// Permission check!
	isAllowedTo('copy');
	// Check Session
	checkSession();
	
	// The destination board must be numeric.
	$_POST['toboard'] = (int) $_POST['toboard'];
	if(empty($_POST['toboard']))
		fatal_lang_error('smf232');

	// Destination board exists
	$request = db_query("
		SELECT countPosts
		FROM {$db_prefix}boards
		WHERE ID_BOARD = $_POST[toboard]
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf232');
		
	list($countPosts) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Strangely 0 = true, 1 = false
	$countPosts = empty($countPosts) ? 1 : 0 ;	

	// Can the user see that board
	$request = db_query("
		SELECT count(*)
		FROM {$db_prefix}boards as b
		WHERE b.ID_BOARD = $_POST[toboard]
			AND $user_info[query_see_board]
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('copytopic_notallowed');

	// Remember this for later.
	$_SESSION['copy_to_topic'] = $_POST['toboard'];
	$topic = (int) $topic;
	
	// THE ACTUAL COPYING FUNCTION
	CopyTopics($topic, $_POST['toboard'], $countPosts);
	
	// Log that they copied this topic.
	if (!allowedTo('copy'))
		logAction('copy', array('topic' => $topic, 'board_from' => $board, 'board_to' => $_POST['toboard']));

	// Why not go back to the original board in case they want to keep moving?
	if (!isset($_REQUEST['goback']))
		redirectexit('board=' . $board . '.0');
	else
		redirectexit('topic=' . $topic . '.0');
}

function CopyTopics($original_topic_id, $board_id, $countPosts)
{
	global $db_prefix, $txt, $scripturl, $sourcedir, $modSettings, $context, $user_info;
	
	// Try to buy some time...
	@set_time_limit(0);
	
	// Check Topic Exists and get some info for now and later
	$request = db_query("
		SELECT ID_BOARD, ID_POLL, numReplies
		FROM {$db_prefix}topics
		WHERE ID_TOPIC = $original_topic_id
		LIMIT 1
		", __FILE__, __LINE__);
	
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf263');
		
	list($original_board_id, $original_poll_id, $numPosts) = mysql_fetch_row($request);
	// Its topic so it has 1 more reply that it states
	$numPosts++;
	mysql_free_result($request);

	// --- Copy Topic Entry ---

	// The Columns for the table with our new topic and board ids.
	// ID_TOPIC is not listed because it is Auto-Incremented
	// ID_BOARD is set to our destination board
	// ID_FIRST_MSG and ID_LAST_MSG are set to 0 for now(to prevent key errors if copying into same forum)
	$insert = "ID_BOARD, ID_FIRST_MSG, ID_LAST_MSG, ID_MEMBER_STARTED, ID_MEMBER_UPDATED, isSticky, ID_POLL, numReplies, numViews, locked";
	$select = "'$board_id', 0, 0, ID_MEMBER_STARTED, ID_MEMBER_UPDATED, isSticky, ID_POLL, numReplies, numViews, locked";
	
	// Query to Copy the Topic
	db_query("
		INSERT INTO {$db_prefix}topics (". $insert .")
		SELECT ". $select ."
		FROM {$db_prefix}topics
		WHERE ID_TOPIC = '".$original_topic_id."'
		LIMIT 1
	", __FILE__, __LINE__);
	
	$topic_id = db_insert_id();
	
	// Tidy up
	unset($insert, $select);	
	
	// --- Copy Messages Entries ---

	// The Columns for the table with our new topic and board ids.
	// ID_MSG is not listed because it is Auto-Incremented
	// ID_MSG_MODIFIED is made to the old msg id temporarily.
	$insert = "ID_TOPIC, ID_BOARD, ID_MSG_MODIFIED, ID_MEMBER, posterTime, subject, posterName, posterEmail, posterIP, modifiedName, body, icon";
	$select = "'$topic_id', '$board_id', ID_MSG, ID_MEMBER, posterTime, subject, posterName, posterEmail, posterIP, modifiedName, body, icon";
	
	// Query to Copy EVERY post in the topic (Potentially could be a beast of a query)
	db_query("
		INSERT INTO {$db_prefix}messages (". $insert .")
		SELECT ". $select ."
		FROM {$db_prefix}messages
		WHERE ID_TOPIC = ".$original_topic_id."
		ORDER BY ID_MSG ASC
	", __FILE__, __LINE__);
	
	// Tidy up
	unset($insert,$select);
	
	// --- Log Search Subject Cache (for searching) ---
	// Standard
	// The Columns for the table with our new topic id instead	
	$insert = "word, ID_TOPIC";
	$select = "word, '$topic_id'";
	
	// Query to Copy EVERY matching row
	db_query("
		INSERT INTO {$db_prefix}log_search_subjects (". $insert .")
		SELECT ". $select ."
		FROM {$db_prefix}log_search_subjects
		WHERE ID_TOPIC = ".$original_topic_id."
	", __FILE__, __LINE__);
	
	// Tidy up
	unset($insert,$select);
	
	// Custom Search Index?
	if (!empty($modSettings['search_custom_index_config']))
	{
		// The Columns for the table with our new msg id instead	
		$insert = "ID_WORD, ID_MSG";
		$select = "w.ID_WORD, m.ID_MSG";
	
		// Query to Copy EVERY matching row
		db_query("
			INSERT INTO {$db_prefix}log_search_words (". $insert .")
			SELECT ". $select ."
			FROM {$db_prefix}log_search_words as w
				LEFT JOIN {$db_prefix}messages as m ON (w.ID_MSG = m.ID_MSG_MODIFIED)
			WHERE m.ID_TOPIC = ".$topic_id."
		", __FILE__, __LINE__);
		
		// Tidy up
		unset($insert,$select);
	}
	
	/* Disabled v1.2 - Causing duplicate key issues
	// --- Log_Search_Results ---
	// Include this new topic in existing search results
	// The Columns for the table with our new msg id and topic id instead	
	$insert = "ID_SEARCH, ID_TOPIC, ID_MSG, relevance, num_matches";
	$select = "r.ID_SEARCH, '$topic_id', m.ID_MSG, r.relevance, r.num_matches";
	
	// Query to Copy EVERY matching row
	db_query("
		INSERT INTO {$db_prefix}log_search_results (". $insert .")
		SELECT ". $select ."
		FROM {$db_prefix}log_search_results as r
			LEFT JOIN {$db_prefix}messages as m ON (r.ID_MSG = m.ID_MSG_MODIFIED)
		WHERE m.ID_TOPIC = ".$topic_id."
	", __FILE__, __LINE__);
	
	// Tidy up
	unset($insert,$select);
	*/
	
	// --- Copy Attachments ---
	
	// * Only those less than 1mb in size, larger files may crash your server
	
	$doattachments = 1;
	// First make sure the attachment dir is writable.
	if (!is_writable($modSettings['attachmentUploadDir']))
	{
		// Try to fix it.
		@chmod($modSettings['attachmentUploadDir'], 0777);

		// Guess that didn't work :/?
		if (!is_writable($modSettings['attachmentUploadDir']))
			$doattachments = 0;
	}
	
	// Check how we are on directory filesize?
	if($doattachments && !empty($modSettings['attachmentDirSizeLimit']))
		$doattachments = attachmentDirectorySizeCheck();
	
	// Right try to go ahead with the attachments.
	if(!empty($doattachments))
	{
		// The Columns for the table
		// ID_ATTACH is not listed because it is Auto-Incremented
		// Matching the ID_MSG_MODIFIED and switching it for the new ID_MSG
		$insert = "ID_THUMB, ID_MSG, ID_MEMBER, attachmentType, filename, size, downloads, width, height";
		$select = "a.ID_ATTACH, m.ID_MSG, a.ID_MEMBER, a.attachmentType, a.filename, a.size, a.downloads, a.width, a.height";
		
		db_query("
			INSERT INTO {$db_prefix}attachments (". $insert .")
			SELECT ". $select ."
			FROM {$db_prefix}attachments as a
				LEFT JOIN {$db_prefix}messages as m ON (a.ID_MSG = m.ID_MSG_MODIFIED)
			WHERE m.ID_TOPIC = ".$topic_id."
				AND size < 1048600
			ORDER BY ID_ATTACH ASC
		", __FILE__, __LINE__);
		
		// Tidy up
		unset($insert,$select);
		
		// Grab and store the new vs old attachment ids in a array (we temporarily stored the old ID in the ID_THUMB column)
		$request = db_query("
			SELECT a.ID_ATTACH, a.ID_THUMB
			FROM {$db_prefix}attachments as a
				LEFT JOIN {$db_prefix}messages as m ON (a.ID_MSG = m.ID_MSG)
			WHERE m.ID_TOPIC = ".$topic_id."
			ORDER BY ID_ATTACH ASC
		", __FILE__, __LINE__);
		
		// Did it copy any attachment entries in the db?
		if(mysql_num_rows($request) != 0)
		{
			$ids = array();
			while($row = mysql_fetch_assoc($request))
				$ids[$row['ID_ATTACH']] = $row['ID_THUMB'];
			// Tidy up
			mysql_free_result($request);
			unset($row);
		
			// Re-Associate Thumbs with our new attachment and thumb ids
			db_query("
				UPDATE {$db_prefix}attachments as a
					LEFT JOIN {$db_prefix}messages as m ON (a.ID_MSG = m.ID_MSG)
					LEFT JOIN {$db_prefix}attachments as a2 ON (a.ID_THUMB = a2.ID_ATTACH)
					LEFT JOIN {$db_prefix}attachments as a3 ON (a2.ID_THUMB = a3.ID_THUMB AND m.ID_MSG = a3.ID_MSG)
				SET a.ID_THUMB = a3.ID_ATTACH
				WHERE m.ID_TOPIC = ".$topic_id."
					AND a2.ID_ATTACH != 0
			", __FILE__, __LINE__);
		
			// Grab all the information about the attachments (including thumbs) attached to this topic
			$request = db_query("
				SELECT 	a.ID_ATTACH as file_id, a.filename as filename, a.size as filesize,
						a.ID_THUMB as thumb_id, a2.filename as thumbname, a2.size as thumbsize
				FROM {$db_prefix}attachments as a
					LEFT JOIN {$db_prefix}messages as m ON (a.ID_MSG = m.ID_MSG)
					LEFT JOIN {$db_prefix}attachments as a2 ON (a.ID_THUMB = a2.ID_ATTACH)
				WHERE m.ID_TOPIC = ".$topic_id."
					AND	a.attachmentType != 3
				ORDER BY a.ID_ATTACH ASC
			", __FILE__, __LINE__);
		
			// Attachments found
			if(mysql_num_rows($request) != 0)
			{
				$attachments = array();
				// For each attachment, we will try to copy the file
				while($row = mysql_fetch_assoc($request))
				{
					$row['original_id'] = $ids[$row['file_id']];
					$row['original_thumb_id'] = empty($row['thumb_id']) ? 0 : $ids[$row['thumb_id']] ;
					$attachments[] = $row;
				}
				// Tidy up
				mysql_free_result($request);
				unset($ids, $row);
				
				foreach($attachments as $row)
				{
					// Is there enough space for the copied attachment + thumb
					if(empty($modSettings['attachmentDirSizeLimit']) || 
						($doattachments + (int) $row['filesize'] + (int) $row['thumbsize'] < $modSettings['attachmentDirSizeLimit'] * 1024))
					{
						// Copy the attachment
						if($filename = copyAttachment($row))
						{
							// Successly copied the attachment
							// Update the attachment db entry with the new filename (only if new filename was generated);
							db_query("
								UPDATE {$db_prefix}attachments
								SET filename = '$filename'
								WHERE ID_ATTACH = ". (int) $row['file_id']."
							", __FILE__, __LINE__);
							
							// Increase the size
							$doattachments = $doattachments + (int) $row['filesize'] + (int) $row['thumbsize'];
							
							// If theres a thumb, rename that aswell
							if(!empty($row['thumb_id']))
								db_query("
									UPDATE {$db_prefix}attachments
									SET filename = '".$filename."_thumb'
									WHERE ID_ATTACH = ". (int) $row['thumb_id']."
								", __FILE__, __LINE__);
						}
						else
							// Copying the attachment failed, so delete the db entries
							db_query("
								DELETE FROM {$db_prefix}attachments
								WHERE ID_ATTACH = ". (int) $row['file_id']."
									OR ID_ATTACH = ". (int) $row['thumb_id']."
							", __FILE__, __LINE__);
					}
					else
						// Ran out of space or error, so delete the db entries
						db_query("
							DELETE FROM {$db_prefix}attachments
							WHERE ID_ATTACH = ". (int) $row['file_id']."
								OR ID_ATTACH = ". (int) $row['thumb_id']."
						", __FILE__, __LINE__);

					// Tidy up
					unset($row);
				}
				// Tidy up
				unset($attachments, $ids, $row);
			}
		}
	}
	
	// --- Fix some stats and logs ---
	
	// Fix ID_MSG_MODIFIED to the New ID_MSG
	db_query("
		UPDATE {$db_prefix}messages
		SET ID_MSG_MODIFIED = ID_MSG
		WHERE ID_TOPIC = ".$topic_id."
	", __FILE__, __LINE__);
	
	// Grab First & Last Message Id
	$request = db_query("
		SELECT max(ID_MSG) as last, min(ID_MSG) as first
		FROM {$db_prefix}messages
		WHERE ID_TOPIC = ".$topic_id."
	", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($request);
	
	// Update the topic info with that info
	db_query("
		UPDATE {$db_prefix}topics
		SET ID_FIRST_MSG = ".$row['first'].", ID_LAST_MSG = ".$row['last']."
		WHERE ID_TOPIC = ".$topic_id."
	", __FILE__, __LINE__);

	// Update log topics (for this user only)
	db_query("
		REPLACE
		INTO {$db_prefix}log_topics
			(ID_TOPIC, ID_MEMBER, ID_MSG)
		VALUES (".$topic_id.", ".$context['user']['id'].", ".$row['last'].")
		", __FILE__, __LINE__);

	// Update log boards (for this user only)
	db_query("
		REPLACE
		INTO {$db_prefix}log_boards
			(ID_BOARD, ID_MEMBER, ID_MSG)
		VALUES (".$board_id.", ".$context['user']['id'].", ".$row['last'].")
		", __FILE__, __LINE__);
		
	require_once($sourcedir . '/Subs-Post.php');
	updateLastMessages($board_id, $row['last']);
	
	// --- Fix Post Counts ---
	
	// Posts in the board we're copying the topic to count, so we need to get the figures for each
	if($countPosts)
	{
		// Increase the stats for the board
		db_query("
			UPDATE {$db_prefix}boards
			SET numPosts = numPosts + ".$numPosts.", numTopics = numTopics + 1
			WHERE ID_BOARD = ".$board_id."
		", __FILE__, __LINE__);
	
		// How many posts have been made by each user in the copied topic?
		$request = db_query("
			SELECT count(*) as increase, ID_MEMBER
			FROM {$db_prefix}messages
			WHERE ID_TOPIC = ".$topic_id."
				AND ID_MEMBER > 0
			GROUP BY ID_MEMBER
		", __FILE__, __LINE__);
		// Any members to update (non-guests);
		if(mysql_num_rows($request) != 0)
		{
			$members = $increase = array();
			// Prepare the information in arrays for easy update
			while($row = mysql_fetch_assoc($request))
			{
				$increase[$row['ID_MEMBER']] = $row['increase'];
				// Store the member ids, as we will need to Update PostGroups
				if(!in_array($row['ID_MEMBER'], $members))
					$members[] = $row['ID_MEMBER'];
			}
			
			// Update each users postcount accordingly.  Could add significant number of queries for large topics.
			foreach($increase as $a => $b)
			{
				db_query("
					UPDATE {$db_prefix}members
					SET posts = posts + ".$b."
					WHERE ID_MEMBER = ".$a."
				", __FILE__, __LINE__);
			}
			unset($increase, $a, $b);
			
			// Update PostGroups for member who's postcounts have been altered.
			updateStats('postgroups', 'ID_MEMBER IN (' . implode(', ', $members) . ')');
		}
	}
	
	$_SESSION['last_read_topic'] = $original_topic_id;
	
	// --- Copy Poll ---
	
	// Is it a poll?
	if($original_poll_id > 0)
	{
		// The Columns for the table
		// ID_POLL is not listed because it is Auto-Incremented
		// The rest are the same
		$select = $insert = "question, votingLocked, maxVotes, expireTime, hideResults, changeVote, ID_MEMBER, posterName";

		// Copy the poll
		db_query("
			INSERT INTO {$db_prefix}polls (". $insert .")
			SELECT ". $select ."
			FROM {$db_prefix}polls
			WHERE ID_POLL = '".$original_poll_id."'
		", __FILE__, __LINE__);

		// Save the new poll id
		$poll_id = db_insert_id();

		// Update the topic info with the poll id
		db_query("
			UPDATE {$db_prefix}topics
			SET ID_POLL = ".$poll_id."
			WHERE ID_TOPIC = ".$topic_id."
		", __FILE__, __LINE__);
	
		// Tidy up
		unset($insert,$select);
	
		// --- Copy Poll Choices ---
		
		// The Columns for the table
		// ID_POLL is set to our new poll id
		$insert = "ID_POLL, ID_CHOICE, label, votes";
		$select = "'$poll_id', ID_CHOICE, label, votes";
		
		// Query to Select & Copy ALL the poll choices in one query.
		db_query("
			INSERT INTO {$db_prefix}poll_choices (". $insert .")
			SELECT ". $select ."
			FROM {$db_prefix}poll_choices
			WHERE ID_POLL = ".$original_poll_id."
			ORDER BY ID_CHOICE ASC
		", __FILE__, __LINE__);
		
		// Tidy up
		unset($insert,$select);
		
		// --- Copy Log Polls ---

		// The Columns for the table
		// ID_POLL is set to our new poll id
		$insert = "ID_POLL, ID_MEMBER, ID_CHOICE";
		$select = "'$poll_id', ID_MEMBER, ID_CHOICE";
		
		// Query to Select & Copy the log polls in one query.  (depending on the no. of voters, it could be heavy)
		db_query("
			INSERT INTO {$db_prefix}log_polls(". $insert .")
			SELECT ". $select ."
			FROM {$db_prefix}log_polls
			WHERE ID_POLL = ".$original_poll_id."
		", __FILE__, __LINE__);
		
		// Tidy up
		unset($insert,$select);
	}
	
	// --- Calender Events ---
	
	// The Columns for the table
	// ID_EVENT is not listed as its auto-incremented
	// ID_TOPIC and ID_BOARD are set to our new poll id
		$insert = "startDate, endDate, ID_BOARD, ID_TOPIC, title, ID_MEMBER";
		$select = "startDate, endDate, '$board_id', '$topic_id', title, ID_MEMBER";
	
	// Query to Copy each calendar entry related to this topic
	db_query("
		INSERT INTO {$db_prefix}calendar (". $insert .")
		SELECT ". $select ."
		FROM {$db_prefix}calendar
		WHERE ID_TOPIC = ".$original_topic_id."
		ORDER BY ID_EVENT ASC
	", __FILE__, __LINE__);
	
	updateStats('topic');
	updateStats('message');
	updateStats('calendar');

}

// Sets up for copying multiple topics
function CopyMultipleTopics()
{
	global $board, $db_prefix, $sourcedir, $ID_MEMBER, $user_info, $modSettings;

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
	if (empty($_REQUEST['move_to']))
		$_REQUEST['move_to'] = $board;
		
	$_REQUEST['move_to'] = (int) $_REQUEST['move_to'];

	// Permission check!
	isAllowedTo('copy');
	// Check Session
	checkSession();

	// Destination board exists
	$request = db_query("
		SELECT countPosts
		FROM {$db_prefix}boards
		WHERE ID_BOARD = $_REQUEST[move_to]
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('smf232');
		
	list($countPosts) = mysql_fetch_row($request);
	mysql_free_result($request);

	// Strangely 0 = true, 1 = false
	$countPosts = empty($countPosts) ? 1 : 0 ;	

	// Can the user see that board
	$request = db_query("
		SELECT count(*)
		FROM {$db_prefix}boards
		WHERE ID_BOARD = $_REQUEST[move_to]
			AND $user_info[query_see_board]
		LIMIT 1", __FILE__, __LINE__);
	if (mysql_num_rows($request) == 0)
		fatal_lang_error('copytopic_notallowed');

	// Remember this for later.
	$_SESSION['copy_to_topic'] = $_REQUEST['move_to'];
	
	foreach($topics as $topic)
	{
		// THE ACTUAL COPYING FUNCTION
		CopyTopics($topic, $_REQUEST['move_to'], $countPosts);
	
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
function attachmentDirectorySizeCheck()
{
	global $modSettings;
	
	// Are there any limits on the size of the attachment directory
	if (!empty($modSettings['attachmentDirSizeLimit']))
	{
		// Make sure the directory isn't full.
		$dirSize = 0;
		$dir = @opendir($modSettings['attachmentUploadDir']);
		
		// Read each file
		while ($file = readdir($dir))
		{
			if (substr($file, 0, -1) == '.')
				continue;

			if (preg_match('~^post_tmp_\d+_\d+$~', $file) != 0)
			{
				// Temp file is more than 5 hours old!
				if (filemtime($modSettings['attachmentUploadDir'] . '/' . $file) < time() - 18000)
					@unlink($modSettings['attachmentUploadDir'] . '/' . $file);
				continue;
			}

			$dirSize += filesize($modSettings['attachmentUploadDir'] . '/' . $file);
		}
		closedir($dir);

		// Too big! 
		if ($dirSize > $modSettings['attachmentDirSizeLimit'] * 1024)
			return 0;
	}
	// Else we are ok to go ahead, pass back the current size
	return $dirSize ;
}

function CopyAttachment($row)
{
	global $modSettings;
	
	// Original attachment link
	$row['original_file_link'] = getAttachmentFilename($row['filename'], $row['original_id']);
	
	// New Attachment
	// Ay caramba!, we can't have two files with exact same name, so we need a new filename
	if(empty($modSettings['attachmentEncryptFilenames']))
	{
		$row['filename'] = GenerateUniqueFilename($modSettings['attachmentUploadDir'].'/', $row['filename']);
		// Couldn't generate a new filename so return
		if(empty($row['filename']))
			return 0;
		else
			// Destination attachment link
			$row['file_link'] = $modSettings['attachmentUploadDir'].'/'.$row['filename'];
	}
	else
		// Destination attachment link
		$row['file_link'] = $modSettings['attachmentUploadDir'].'/'.getAttachmentFilename($row['filename'], $row['file_id'], true);
		
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
			$row['original_thumb_link'] = getAttachmentFilename($row['thumbname'], $row['original_thumb_id']);
			
			if(empty($modSettings['attachmentEncryptFilenames']))
			{
				$row['thumbname'] = $row['filename'].'_thumb';
				$row['thumb_link'] = $modSettings['attachmentUploadDir'].'/'.$row['thumbname'];
			}
			else
				$row['thumb_link'] = $modSettings['attachmentUploadDir'].'/'.getAttachmentFilename($row['thumbname'], $row['thumb_id'], true);
				
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