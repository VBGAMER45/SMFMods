<?php
/*---------------------------------------------------------------------------------
*	SMFSimple Rewards System											 		  *
*	Version 3.0																 	  *
*	Author: 4Kstore																  *
*	Copyright 2012												        		  *
*	Powered by www.smfsimple.com												  *
***********************************************************************************
**********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

function SsrsPointsSettings()
{
	global $context, $txt;

	if (!allowedTo('admin_forum'))
		isAllowedTo('admin_forum');

	//loadTemplate('SsrsAdmin');
	loadTemplate('SsrsPoints');

	//Load subactions for the ultimate portal preferences
	$subActions = array(
		'main' => 'ShowSettings',
		'permissions' => 'ShowPerms',
		'permissionsboard' => 'ShowPermsBoard',
	);
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['admin_ssrs_title'] . ' - ' . $txt['admin_ssrs_points_desc'],
		'description' => $txt['admin_ssrs_points_desc'],
		'tabs' => array(
			'main' => array(
				'description' => $txt['admin_ssrs_points_desc'],
			),
		),
	);
	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'main';
	$context['sub_action'] = $_REQUEST['sa'];
	$subActions[$_REQUEST['sa']]();
}

function ShowPerms($return_config = false)
{
	global $context, $sourcedir, $txt, $scripturl;

	require_once($sourcedir . '/ManageServer.php');
	isAllowedTo('manage_permissions');
	loadLanguage('Ssrs');

	$config_vars = array(
		array('title', 'admin_ssrs_permissions_points'),
		array('permissions', 'ssrs_give_points', 'subtext' => $txt['permissionname_ssrs_give_points']),
	);

	if ($return_config)
		return $config_vars;

	$context['page_title'] = $txt['admin_ssrs_points_desc'] .' - '. $txt['admin_ssrs_permissions'];
	$context['sub_template'] = 'show_settings';

	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=rewardPoints;sa=permissions');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=rewardPoints;save;sa=permissions';
	prepareDBSettingContext($config_vars);
}

function ShowPermsBoard()
{
	global $context, $txt, $smcFunc;

	//Save
	if (isset($_POST['save']))
	{
		checkSession('post');
		if (isset($_POST['boards']))
		{
			foreach ($_POST['boards'] as $i => $v)
				 if (!is_numeric($_POST['boards'][$i]))
				 	unset($_POST['boards'][$i]);

			$smcFunc['db_query']('', "
				UPDATE {db_prefix}boards
				SET ssrs_give_points_on = 1
				WHERE id_board IN ({array_int:board})",
				array(
					'board' => $_POST['boards'],
				)
			);

			$smcFunc['db_query']('', "
				UPDATE {db_prefix}boards
				SET ssrs_give_points_on = 0
				WHERE id_board NOT IN ({array_int:board})",
				array(
					'board' => $_POST['boards'],
				)
			);
		}

		redirectexit('action=admin;area=rewardPoints;sa=permissionsboard');
	}

	boardToSelect();
	$context['sub_template'] = 'ssrs_permissions_board';
	$context['page_title'] = $txt['admin_ssrs_points_desc'] .' - '. $txt['admin_ssrs_permissions_board'];
}

function ShowSettings()
{
	global $context, $txt;

	if (isset($_POST['save']))
	{
		//Only integers
		$_POST['ssrs_points_per_post'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_post']);
		$_POST['ssrs_points_per_reply'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_reply']);
		$_POST['ssrs_points_per_word'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_word']);
		$_POST['ssrs_points_bonus_words'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_bonus_words']);
		$_POST['ssrs_points_bonus_words_min'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_bonus_words_min']);
		$_POST['ssrs_points_per_registered'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_registered']);
		$_POST['ssrs_points_per_poll'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_poll']);
		$_POST['ssrs_points_per_vote_poll'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_vote_poll']);
		$_POST['ssrs_points_per_topic_views'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_topic_views']);
		$_POST['ssrs_points_per_topic_views_min'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_topic_views_min']);
		$_POST['ssrs_points_per_topic_replies'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_topic_replies']);
		$_POST['ssrs_points_per_topic_replies_min'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_topic_replies_min']);
		$_POST['ssrs_points_per_sticky'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_sticky']);
		$_POST['ssrs_points_per_warning'] = preg_replace('/[^0-9]/', '', $_POST['ssrs_points_per_warning']);

		//Settings change?...
		$ssrs_settings = array(
			'ssrs_points_enabled' => !empty($_POST['ssrs_points_enabled']) ? '1' : '0',
			'ssrs_points_guests' => !empty($_POST['ssrs_points_guests']) ? '1' : '0',
			'ssrs_points_show_givers_display' => !empty($_POST['ssrs_points_show_givers_display']) ? $_POST['ssrs_points_show_givers_display'] : '0',
			'ssrs_points_per_post' => !empty($_POST['ssrs_points_per_post']) ? $_POST['ssrs_points_per_post'] : '',
			'ssrs_points_per_reply' => !empty($_POST['ssrs_points_per_reply']) ? $_POST['ssrs_points_per_reply'] : '',
			'ssrs_points_per_word' => !empty($_POST['ssrs_points_per_word']) ? $_POST['ssrs_points_per_word'] : '',
			'ssrs_points_bonus_words' => !empty($_POST['ssrs_points_bonus_words']) ? $_POST['ssrs_points_bonus_words'] : '',
			'ssrs_points_bonus_words_min' => !empty($_POST['ssrs_points_bonus_words_min']) ? $_POST['ssrs_points_bonus_words_min'] : '',
			'ssrs_points_per_registered' => !empty($_POST['ssrs_points_per_registered']) ? $_POST['ssrs_points_per_registered'] : '',
			'ssrs_points_per_poll' => !empty($_POST['ssrs_points_per_poll']) ? $_POST['ssrs_points_per_poll'] : '',
			'ssrs_points_per_vote_poll' => !empty($_POST['ssrs_points_per_vote_poll']) ? $_POST['ssrs_points_per_vote_poll'] : '',
			'ssrs_points_per_topic_views' => !empty($_POST['ssrs_points_per_topic_views']) ? $_POST['ssrs_points_per_topic_views'] : '',
			'ssrs_points_per_topic_views_min' => !empty($_POST['ssrs_points_per_topic_views_min']) ? $_POST['ssrs_points_per_topic_views_min'] : '',
			'ssrs_points_per_topic_replies' => !empty($_POST['ssrs_points_per_topic_replies']) ? $_POST['ssrs_points_per_topic_replies'] : '',
			'ssrs_points_per_topic_replies_min' => !empty($_POST['ssrs_points_per_topic_replies_min']) ? $_POST['ssrs_points_per_topic_replies_min'] : '',
			'ssrs_points_per_sticky' => !empty($_POST['ssrs_points_per_sticky']) ? $_POST['ssrs_points_per_sticky'] : '',
			'ssrs_points_per_warning' => !empty($_POST['ssrs_points_per_warning']) ? $_POST['ssrs_points_per_warning'] : '',
			'ssrs_points_points_on_messageindex' => !empty($_POST['ssrs_points_points_on_messageindex']) ? '1' : '0',
		);
		updateSettings($ssrs_settings);
		redirectexit('action=admin;area=rewardPoints;sesc='.$context['session_id']);
	}

	$context['sub_template'] = 'ssrs_settings';
	$context['page_title'] = $txt['admin_ssrs_settings'];
}

function SendPoints() //Ajax call from Ssrs.js
{
	global $context, $smcFunc, $user_info, $modSettings, $txt, $settings;

	loadLanguage('Ssrs');
	loadTemplate('SsrsPoints');

	if ($_REQUEST['points'] < 0)
		fatal_lang_error('error_ssrs_no_negatives', false);

	$_REQUEST['points'] = preg_replace('/[^0-9]/', '', $_REQUEST['points']);
	$_REQUEST['to'] = preg_replace('/[^0-9]/', '', $_REQUEST['to']);
	$_REQUEST['topic'] = preg_replace('/[^0-9]/', '', $_REQUEST['topic']);

	$points = !empty($_REQUEST['points']) ? (int) $smcFunc['db_escape_string']($_REQUEST['points']) : '';
	$to = !empty($_REQUEST['to']) ? (int) $smcFunc['db_escape_string']($_REQUEST['to']) : '';
	$topic = !empty($_REQUEST['topic']) ? (int) $smcFunc['db_escape_string']($_REQUEST['topic']) : '';

	if (empty($points) || empty($to) || empty($topic))
		fatal_lang_error('error_ssrs_no_points', false);

	//Check topic_starter != user_id
	$sql = $smcFunc['db_query']('',"
		SELECT id_member_started
		FROM {db_prefix}topics
		WHERE id_topic = {int:topic}
		LIMIT 1",
		array(
			'topic' => $topic,
		)
	);
	list ($id_topic_starter) = $smcFunc['db_fetch_row']($sql);
	$smcFunc['db_free_result']($sql);

	if ($to != $id_topic_starter)
		fatal_lang_error('error_ssrs_no_points', false);

	ssrsGivePoints($to); //we need use "candSendPoints"

	$context['ssrs_points_day'] = !empty($context['ssrs_points_day']) ? $context['ssrs_points_day'] : 0;

	if ($points > $context['ssrs_points_day'])
		fatal_lang_error('error_ssrs_no_points', false);

	//Adding points to the user
	if ($context['canSendPoints'])
	{
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}members
			SET ssrs_points = ssrs_points + {int:points}
			WHERE id_member = {int:to}
			LIMIT 1",
			array(
				'points' => $points,
				'to' => $to,
			)
		);
		//remove points...
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}members
			SET ssrs_points_day = ssrs_points_day - {int:points}
			WHERE id_member = {int:user}
			LIMIT 1",
			array(
				'points' => $points,
				'user' => $user_info['id'],
			)
		);
		//Adding points to the glorius topic
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}topics
			SET ssrs_points = ssrs_points + {int:points}
			WHERE id_topic = {int:topic}
			LIMIT 1",
			array(
				'points' => $points,
				'topic' => $topic,
			)
		);
		//updating the log
		 $smcFunc['db_insert']('insert',
			'{db_prefix}ssrs_good_post',
			array(
				'id_member' => 'int', 'id_topic' => 'int', 'points' => 'int',
			),
			array(
				$user_info['id'], $topic, $points,
			),
			array()
		);

		if (!empty($modSettings['ssrs_points_show_givers_display']))
			$context['usersgives_ajax'] = ssrsGiveWhoGive($topic);

		//$context['give_points_display'] = ssrsGivePoints($to);
		$context['give_points_display'] = 	'<div class="ssrs_points_added">
												<img style="vertical-align: 5px;" src="'.$settings['default_theme_url'].'/images/SSRS_images/accept.png" alt="" /><span style="margin-left: 4px; vertical-align: 8px;"> '.$txt['ssrs_message_points_added_ok'].'</span>
											</div>';
		$context['give_points_display_total'] = (int) ssrsPostTotalPoints();
		$who_gives = $txt['ssrs_message_users_no_pointings'];

		if (!empty($context['usersgives_ajax']))
		{
			$who_gives = $txt['ssrs_message_users_pointings'];

			foreach ($context['usersgives_ajax'] as $info)
				$who_gives.= ($modSettings['ssrs_points_show_givers_display'] == 1) ? $info['href'] : $info['hrefAndPoints'];

		}

		$ssrsReturn = array(
			"message" => !empty($context['give_points_display']) ? $context['give_points_display'] : "",
			"total" => !empty($context['give_points_display_total']) ? $context['give_points_display_total'] : "",
			"whogives" =>  !empty($who_gives) ? $who_gives : "",
		);

		$ssrsReturn = json_encode($ssrsReturn);
		echo $ssrsReturn;

		obExit(false);
	}

	else
		fatal_lang_error('error_ssrs_no_points');
}

function ssrsGivePoints($id_starter) //Show points in the display.php
{
	global $context, $smcFunc, $user_info, $txt, $scripturl, $topic, $board_info, $settings;

	$context['canSendPoints'] = true;
	$id_start = (!empty($id_starter)) ? $id_starter : -1;

	$context['ssrs_give_points_on'] = $board_info['ssrs_give_points_on'];

	if (!$context['ssrs_give_points_on']) // In this board the mod is disabled
		$context['canSendPoints'] = false;

	if (!allowedTo('ssrs_give_points')) //The group cant vote
	{
		$context['canSendPoints'] = false;
		$text = '<div class="ssrs_no_points">
					<img style="vertical-align: -2px;" src="'.$settings['default_theme_url'].'/images/SSRS_images/guest.png" alt="" /> <span>'.$txt['error_ssrs_no_permissions'].'</span>
				</div>';
		return $text;
	}

	if ($user_info['is_guest']) // The guest cant vote
	{
		$text = '<div class="ssrs_no_points">
					<img style="vertical-align: -2px;" src="'.$settings['default_theme_url'].'/images/SSRS_images/guest.png" alt="" /> <span>'.$txt['ssrs_message_guest'].'</span>
				</div>';
		return $text;
	}

	//The author cant vote his own topic
	if ($id_start == $user_info['id'])
	{
		$context['canSendPoints'] = false;
		$text = '<div class="ssrs_no_vot_you_mismo_tema">
					<img style="vertical-align: -2px;" src="'.$settings['default_theme_url'].'/images/SSRS_images/mismo.png" alt="" /> <span>'.$txt['ssrs_no_vot_you_mismo_tema'].'</span>
				</div>';
		return $text;
	}

	//already vote this topic
	$sql = $smcFunc['db_query']('',"
		SELECT count(*)
		FROM {db_prefix}ssrs_good_post
		WHERE id_topic = {int:topic} AND id_member = {int:id}
		LIMIT 1",
		array(
			'id' => $user_info['id'],
			'topic' => $topic,
		)
	);
	list($something) = $smcFunc['db_fetch_row']($sql);
	$smcFunc['db_free_result']($sql);

	if (!empty($something))
	{
		$context['canSendPoints'] = false;
		$text = '<div class="ssrs_no_points">
					<img style="vertical-align: -2px;" src="'.$settings['default_theme_url'].'/images/SSRS_images/mismo.png" alt="" /> <span>'.$txt['no_vot_mismo_topic'].'</span>
				</div>';
		return  $text;
	}
	
	//Has points? how many?	
	$context['ssrs_points_day'] = $user_info['ssrs_points_day'];

	if (($context['ssrs_points_day'] > 0) && ($context['canSendPoints'] == true)) //Have Points Yeah!
	{
		$dataSsrs = array(
			"sourcedir" => $scripturl,
			"topic" => $topic,
			"id_start" => $id_start,
			"sesid" => $context['session_id'],
			"sesvar" => $context['session_var'],
		);
		$dataSsrs = $smcFunc['htmlspecialchars'](json_encode($dataSsrs));
		$cantidad = $context['ssrs_points_day'];
		$text = '<span class="ssrs_points_dar">'.$txt['ssrs_dar_point'].'</span>';
		$salto = 20;
		
		for ($i = 1; $i<=$cantidad; $i++)
		{
			$text.= '<span class="ssrs_point_style" onClick="ssrs_points('.$dataSsrs.',\''.$i.'\');"><span>'.$i.'</span></span>';

			if ($i==$salto)
			{
				$text.='<br /><br />';
				$salto = $salto + 20;
			}
		}
	}

	else
	{
		$text = '<div class="ssrs_no_points">
					<img style="vertical-align: -2px;" src="'.$settings['default_theme_url'].'/images/SSRS_images/time.png" alt="time" /> <span>'.$txt['no_point_today'].'</span>
				 </div>';
		$context['canSendPoints'] = false;
	}
	return $text;
}

function ssrsGiveWhoGive($id_topic) //Show who give point and how many
{
	global $context, $smcFunc, $scripturl, $topic;

	$context['usersgives'] = array();
	$usersgives = array();
	$sql = $smcFunc['db_query']('',"
		SELECT lg.id_topic, lg.id_member, lg.points, m.id_member, m.real_name, m.id_group, mg.id_group, mg.online_color
		FROM {db_prefix}ssrs_good_post AS lg
		INNER JOIN {db_prefix}members AS m ON (lg.id_member = m.id_member)
		LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN m.id_group = {int:reg_mem_group} THEN m.id_post_group ELSE m.id_group END)
		WHERE lg.id_topic = {int:topic}",
		array(
			'topic' => $id_topic,
			'reg_mem_group' => 0,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($sql))
	{
		$usersgives = &$context['usersgives'][];
		$usersgives['id_topic'] = $row['id_topic'];
		$usersgives['id_member'] = $row['id_member'];
		$usersgives['real_name'] = $row['real_name'];
		$usersgives['group_color'] = $row['online_color'];

		if (!empty($usersgives['group_color']))
		{
			$usersgives['href'] = '&#187; <a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color:'.$usersgives['group_color'].';">' . $row['real_name'] . '</a>';
			$usersgives['hrefAndPoints'] = '&#187; <a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" style="color:'.$usersgives['group_color'].';">' . $row['real_name'] . '</a> ('.$row['points'].' Pts)';
		}
		else
		{
			$usersgives['href'] = '&#187; <a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>';
			$usersgives['hrefAndPoints'] = '&#187; <a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a> ('.$row['points'].' Pts)';
		}
	 }

	$smcFunc['db_free_result']($sql);
	return $context['usersgives'];
}

function ssrsPostTotalPoints() //Total Points Topic!
{
	global $smcFunc, $topic;

	$totalPoints = 0;
	$sql = $smcFunc['db_query']('',"
		SELECT ssrs_points,id_topic
		FROM {db_prefix}topics
		WHERE id_topic = {int:topic}",
		array(
			'topic' => $topic,
		)
	);

	while($row = $smcFunc['db_fetch_assoc']($sql))
		$totalPoints = $totalPoints + $row['ssrs_points'];

	return $totalPoints;
}

function ssrsAddPointGeneric($totalPoints,$userTo) //IMPORTANT FUNCTION!
{
	global $smcFunc;

	$totalPoints = (!empty($totalPoints)) ? (int) $totalPoints : 0;
	$userTo = (!empty($userTo)) ? (int) $userTo : 0;

	if (!empty($userTo) && !empty($totalPoints))
	{
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}members
			SET ssrs_points = ssrs_points + {int:addPoints}
			WHERE id_member = {int:id}
			LIMIT 1",
			array(
				'addPoints' => $totalPoints,
				'id' => $userTo,
			)
		);
	}
}

function ssrsPerPost($newTopic, $ssrsPoll) //Post.php call him to adding points
{
	global $modSettings, $user_info;

	$pointPoll = (!empty($modSettings['ssrs_points_per_poll']) && ($ssrsPoll == true)) ? $modSettings['ssrs_points_per_poll'] : 0;
	$PointPost = (!empty($modSettings['ssrs_points_per_post'])) ? $modSettings['ssrs_points_per_post'] : 0;
	$PointReply = (!empty($modSettings['ssrs_points_per_reply'])) ? $modSettings['ssrs_points_per_reply'] : 0;
	$addPoints = !empty($newTopic) ? $PointPost + $pointPoll : $PointReply;

	if (!empty($addPoints))
		ssrsAddPointGeneric($addPoints,$user_info['id']);
}

function ssrsPointPerLenghtMsg($msg) //Points Per Word | Points Bonus \\
{
	global $modSettings, $user_info;

	if (!empty($msg))
	{
		$PointPerWord = (!empty($modSettings['ssrs_points_per_word'])) ? $modSettings['ssrs_points_per_word'] : 0;  // Points per Word
		$PointBonus = (!empty($modSettings['ssrs_points_bonus_words'])) ? $modSettings['ssrs_points_bonus_words'] : 0; //Points
		$PointBonusMinWords = (!empty($modSettings['ssrs_points_bonus_words_min'])) ? $modSettings['ssrs_points_bonus_words_min'] : 0; //Min Words

		if ($PointPerWord != 0 || $PointBonus != 0)
		{
			$message = $msg;
			$totalPoints = 0;
			$completemsg = preg_replace('[\[(.*?)\]]', ' ', $message); //BBC OUT!
			$completemsg = str_replace(array('<br />', "\r", "\n"), ' ', $completemsg); //NewLines OUT!
			$completemsg = preg_replace('/\s+/', ' ', $completemsg); //One Space!

			if ($PointPerWord != 0)
				$totalPoints += ($PointPerWord * str_word_count($completemsg)); //Get Points Per Word

			if (($PointBonusMinWords && $PointBonus) != 0)
			{
				if (str_word_count($completemsg) >= $PointBonusMinWords)
					$totalPoints += $PointBonus; //Get Points Bonus!
			}

			ssrsAddPointGeneric($totalPoints,$user_info['id']);
		}
	}
}

function ssrsTopTenStats() // Points top 10.
{
	global $context, $smcFunc, $scripturl;

	$members_result = $smcFunc['db_query']('', '
		SELECT id_member, real_name, ssrs_points
		FROM {db_prefix}members
		WHERE ssrs_points > {int:no_points}
		ORDER BY ssrs_points DESC
		LIMIT 10',
		array(
			'no_points' => 0,
		)
	);
	$context['top_ssrs_points'] = array(); //For Stats.php
	$max_num_ssrs_points = 1;

	while ($row_members = $smcFunc['db_fetch_assoc']($members_result))
	{
		$context['top_ssrs_points'][] = array(
			'name' => $row_members['real_name'],
			'id' => $row_members['id_member'],
			'ssrs_points' => $row_members['ssrs_points'],
			'href' => $scripturl . '?action=profile;u=' . $row_members['id_member'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row_members['id_member'] . '">' . $row_members['real_name'] . '</a>'
		);

		if ($max_num_ssrs_points < $row_members['ssrs_points'])
			$max_num_ssrs_points = $row_members['ssrs_points'];
	}

	$smcFunc['db_free_result']($members_result);
	$context['max_num_ssrs_points'] = $max_num_ssrs_points; //For Stats.php
}

function ssrsTopTenTopicPointsStats() // Topics Points top 10.
{
	global $context, $smcFunc, $scripturl;

	$topic_reply_result = $smcFunc['db_query']('', '
		SELECT m.subject, t.ssrs_points, t.id_topic
		FROM {db_prefix}topics AS t
		INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
		INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE {query_see_board}
		AND ssrs_points > {int:no_points}
		ORDER BY t.ssrs_points DESC
		LIMIT 10',
		array(
			'no_points' => 0,
		)
	);
	$context['top_topics_ssrs_points'] = array();
	$max_num_ssrs_points_topics = 1;

	while ($row_topic_reply = $smcFunc['db_fetch_assoc']($topic_reply_result))
	{
		censorText($row_topic_reply['subject']);
		$context['top_topics_ssrs_points'][] = array(
			'id' => $row_topic_reply['id_topic'],
			'subject' => $row_topic_reply['subject'],
			'ssrs_points' => $row_topic_reply['ssrs_points'],
			'href' => $scripturl . '?topic=' . $row_topic_reply['id_topic'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row_topic_reply['id_topic'] . '.0">' . $row_topic_reply['subject'] . '</a>'
		);

		if ($max_num_ssrs_points_topics < $row_topic_reply['ssrs_points'])
			$max_num_ssrs_points_topics = $row_topic_reply['ssrs_points'];
	}
	$smcFunc['db_free_result']($topic_reply_result);
	$context['max_num_ssrs_points_topics'] = $max_num_ssrs_points_topics; //For Stats.php

}

function boardToSelect()
{
	global $smcFunc, $context;

	if (isset($context['jump_to']))
		return;

	$request = $smcFunc['db_query']('', "
		SELECT c.name AS cat_name, c.id_cat, b.id_board, b.name AS board_name, b.child_level
		FROM {db_prefix}boards AS b
			LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
		WHERE {query_see_board}"
		);

	$context['jump_to'] = array();
	$this_cat = array('id' => -1);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if ($this_cat['id'] != $row['id_cat'])
		{
			$this_cat = &$context['jump_to'][];
			$this_cat['id'] = $row['id_cat'];
			$this_cat['name'] = $row['cat_name'];
			$this_cat['boards'] = array();
		}

		$this_cat['boards'][] = array(
			'id' => $row['id_board'],
			'name' => $row['board_name'],
			'child_level' => $row['child_level'],
			'is_current' => isset($context['current_board']) && $row['id_board'] == $context['current_board']
		);
	}
	$smcFunc['db_free_result']($request);

	$request = $smcFunc['db_query']('', '
		SELECT id_board, ssrs_give_points_on
		FROM {db_prefix}boards
		WHERE ssrs_give_points_on = {int:active}',
		array(
			'active' => 1,
		)
	);

	$context['ssrs_boards_enabled'] = array();
	while($row = $smcFunc['db_fetch_assoc']($request))
		$context['ssrs_boards_enabled'][] = $row['id_board'];

	$smcFunc['db_free_result']($request);
}