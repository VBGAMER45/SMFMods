<?php
/*
Zapier for SMF
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/
global $ssi_guest_access;
$ssi_guest_access = 1;
require(dirname(__FILE__) . '/SSI.php');


$hash = $_REQUEST['hash'];
// Check if hash matches
if ($modSettings['zapier_hash'] != $hash)
{
    die("Error: Invalid Hash");
}

if (!isset($_REQUEST['action']))
    die("No action passed");

$action = $_REQUEST['action'];

if (function_exists("set_tld_regex"))
    $context['isSMF21'] = true;
else
    $context['isSMF21'] = false;



if ($action == 'getmembers')
{
    $numShow = 100;
    if (isset($_REQUEST['limit']))
        $numShow = (int) $_REQUEST['limit'];

    if ($numShow > 1000)
        $numShow = 1000;

    	// Get list of most recent activated members
	$dbresult = $smcFunc['db_query']('', "
		SELECT
			m.ID_MEMBER as id, m.real_name, m.email_address, m.date_registered, m.last_login, mg.online_color, mg.ID_GROUP, m.member_ip, m.is_activated  
		FROM {db_prefix}members AS m
		LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(m.ID_GROUP = 0, m.ID_POST_GROUP, m.ID_GROUP))
		WHERE m.is_activated = 1
		ORDER BY m.ID_MEMBER DESC LIMIT $numShow");

	$data = array();
	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{

	    if (!empty($row['last_login']))
	        $row['last_login'] = date("F j, Y, g:i a",$row['last_login']);

	    $row['date_registered'] = date("F j, Y, g:i a",$row['date_registered']);

	    $data[] = $row;
	}

	echo safe_json_encode($data);

    exit;
}

if ($action == 'getposts')
{

    $numShow = 100;
    if (isset($_REQUEST['limit']))
        $numShow = (int) $_REQUEST['limit'];

    if ($numShow > 1000)
        $numShow = 1000;


    $boardFilter = '';
    if (isset($_REQUEST['showboard']))
    {
       if (substr_count($_REQUEST['showboard'],',') == 0)
        {
            $boardNum = (int) $_REQUEST['showboard'];
            $boardFilter = ' AND m.ID_BOARD = ' . $boardNum;

        }
        else
        {

                $boardsToInclude = array();
                $tmp = explode(",",$_REQUEST['showboard']);
                foreach($tmp as $bitem)
                {
                    $bitem = trim($bitem);
                     $boardNum = (int) $bitem;
                    if (is_numeric($bitem))
                        $boardsToInclude[] = $boardNum;
                }

                if (!empty($boardsToInclude))
                    $boardFilter = ' AND m.ID_BOARD IN(' . implode(', ', $boardsToInclude) . ') ';




        }

    }


	if ($exclude_boards === null && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0)
		$exclude_boards = array($modSettings['recycle_board']);
	else
		$exclude_boards = empty($exclude_boards) ? array() : $exclude_boards;


	$posts = array();


	if (($posts = cache_get_data('zapier_recentpost', 10)) == null)
	{

	// Find all the posts.  Newer ones will have higher IDs.
	$request = $smcFunc['db_query']('', "
		SELECT
			m.poster_time, m.subject, m.ID_TOPIC, m.ID_MEMBER, m.ID_MSG, m.ID_BOARD, b.name AS board_name, mg.online_color, mg.ID_GROUP,
			IFNULL(mem.real_name, m.poster_name) AS poster_name, " . ($user_info['is_guest'] ? '1 AS is_read, 0 AS new_from' : '
			IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, 0)) >= m.ID_MSG_MODIFIED AS is_read,
			IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, -1)) + 1 AS new_from') . ", LEFT(m.body, 384) AS body, m.smileys_enabled
		FROM ({db_prefix}messages AS m, {db_prefix}boards AS b)
			LEFT JOIN {db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))

			" . (!$user_info['is_guest'] ? "
			LEFT JOIN {db_prefix}log_topics AS lt ON (lt.ID_TOPIC = m.ID_TOPIC AND lt.ID_MEMBER = " . $user_info['id'] . ")
			LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = m.ID_BOARD AND lmr.ID_MEMBER = " . $user_info['id'] . ")" : '') . "
		WHERE
			m.ID_MSG >= " . ($modSettings['maxMsgID'] - 2000 * min($numShow, 5)) . " AND
		b.ID_BOARD = m.ID_BOARD" . (empty($exclude_boards) ? '' : "
			AND b.ID_BOARD NOT IN (" . implode(', ', $exclude_boards) . ")") . "
			$boardFilter 
		ORDER BY m.ID_MSG DESC
		LIMIT $numShow");

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['ID_MSG']), array('<br />' => '&#10;')));


			// Censor it!
			censorText($row['subject']);
			censorText($row['body']);

			// Build the array.
			$posts[] = array(
				'id' => $row['ID_MSG'],
                'topic' => $row['ID_TOPIC'],
				'subject' => $row['subject'],
				'short_subject' => shorten_subject($row['subject'], 25),
				'body' => $row['body'],
				'time' => date("F j, Y, g:i a",$row['poster_time']),
				'timestamp' => forum_time(true, $row['poster_time']),
				'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . ';topicseen#new',
				'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'] . '">' . $row['subject'] . '</a>',
				'new' => !empty($row['is_read']),
				'new_from' => $row['new_from'],
                'board'  => $row['ID_BOARD'],
                'boardname' => $row['board_name'],
                'posterid' => $row['ID_MEMBER'],
                'postername' => $row['poster_name'],

			);
		}
		$smcFunc['db_free_result']($request);


		// Check if cache is enabled
		if (!empty($modSettings['cache_enable']))
			cache_put_data('zapier_recentpost', $posts, 10);

	}

	echo safe_json_encode($posts);

    exit;



}

if ($action == 'gettopics')
{

    $numShow = 100;
    if (isset($_REQUEST['limit']))
        $numShow = (int) $_REQUEST['limit'];

    $boardFilter = '';
    if (isset($_REQUEST['showboard']))
    {
       if (substr_count($_REQUEST['showboard'],',') == 0)
        {
            $boardNum = (int) $_REQUEST['showboard'];
            $boardFilter = ' AND t.ID_BOARD = ' . $boardNum;

        }
        else
        {

                $boardsToInclude = array();
                $tmp = explode(",",$_REQUEST['showboard']);
                foreach($tmp as $bitem)
                {
                    $bitem = trim($bitem);
                     $boardNum = (int) $bitem;
                    if (is_numeric($bitem))
                        $boardsToInclude[] = $boardNum;
                }

                if (!empty($boardsToInclude))
                    $boardFilter = ' AND t.ID_BOARD IN(' . implode(', ', $boardsToInclude) . ') ';




        }

    }


    if ($numShow > 1000)
        $numShow = 1000;

	if ($exclude_boards === null && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0)
		$exclude_boards = array($modSettings['recycle_board']);
	else
		$exclude_boards = empty($exclude_boards) ? array() : $exclude_boards;

	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	$posts = array();

	if (($posts = cache_get_data('zapier_topics_block', 10)) == null)
	{
	// Find all the posts in distinct topics.  Newer ones will have higher IDs.
	$request = $smcFunc['db_query']('', "
		SELECT
			m.poster_time, ms.subject, m.ID_TOPIC, m.ID_MEMBER, m.ID_MSG, b.ID_BOARD, b.name AS board_name, mg.online_color, mg.ID_GROUP,
			IFNULL(mem.real_name, m.poster_name) AS poster_name, " . ($user_info['is_guest'] ? '1 AS is_read, 0 AS new_from' : '
			IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, 0)) >= m.ID_MSG_MODIFIED AS is_read,
			IFNULL(lt.ID_MSG, IFNULL(lmr.ID_MSG, -1)) + 1 AS new_from') . ", LEFT(m.body, 384) AS body, m.smileys_enabled, m.icon
		FROM ({db_prefix}messages AS m, {db_prefix}topics AS t, {db_prefix}boards AS b, {db_prefix}messages AS ms)
			LEFT JOIN {db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
			" . (!$user_info['is_guest'] ? "
			LEFT JOIN {db_prefix}log_topics AS lt ON (lt.ID_TOPIC = t.ID_TOPIC AND lt.ID_MEMBER = " . $user_info['id'] . ")
			LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.ID_BOARD = b.ID_BOARD AND lmr.ID_MEMBER = " . $user_info['id'] . ")" : '') . "
		WHERE
			m.ID_MSG >= " . ($modSettings['maxMsgID'] - 2000 * min( $numShow , 5)) . " AND
		t.ID_LAST_MSG = m.ID_MSG
			AND b.ID_BOARD = t.ID_BOARD" . (empty($exclude_boards) ? '' : "
			AND b.ID_BOARD NOT IN (" . implode(', ', $exclude_boards) . ")") . "
				AND ms.ID_MSG = t.ID_FIRST_MSG $boardFilter
		ORDER BY t.ID_LAST_MSG DESC
		LIMIT  $numShow");

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
	  //  print_r($row);
		$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['ID_MSG']), array('<br />' => '&#10;')));
		//if ($smcFunc['strlen']($row['body']) > 128)
		//	$row['body'] = $smcFunc['substr']($row['body'], 0, 128) . '...';

		// Censor the subject.
		censorText($row['subject']);
		censorText($row['body']);

	//	if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
			//$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.'  .($context['isSMF21'] == true ? 'png' : 'gif')) ? 'images_url' : 'default_images_url';

		// Build the array.
		$posts[] = array(

			'id' => $row['ID_TOPIC'],
			'subject' => $row['subject'],
			'short_subject' => shorten_subject($row['subject'], 25),
			'body' => $row['body'],
			'time' => date("F j, Y, g:i a",$row['poster_time']),
            'timestamp' => forum_time(true, $row['poster_time']),
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . ';topicseen#new',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#new">' . $row['subject'] . '</a>',
			'new' => !empty($row['is_read']),
			'new_from' => $row['new_from'],
		    'board'  => $row['ID_BOARD'],
            'boardname' => $row['board_name'],
            'posterid' => $row['ID_MEMBER'],
	        'postername' => $row['poster_name'],

            );
	}
	$smcFunc['db_free_result']($request);

		// Check if cache is enabled
		if (!empty($modSettings['cache_enable']))
			cache_put_data('zapier_topics_block', $posts, 10);

	}


//	echo print_r($posts);

	echo safe_json_encode($posts);

    exit;

}

if ($action == 'updatemember')
{
	$group = 0;

	$memberID = (int) $_REQUEST['id'];

	// update the membergroup if set
	if (!empty($group))
					$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET id_group = ' . $group . '
			WHERE ID_MEMBER = ' . $memberID);

}


if ($action == 'registermember')
{

    $errors = array();
    $email = '';
    $username = '';
    $displayname = '';
    $password = '';
    $send_welcome_email = 0;
	$group = 0;


    if (isset($_REQUEST['email']))
        $email = trim($_REQUEST['email']);

    if (isset($_REQUEST['password']))
        $password = trim($_REQUEST['password']);


    if (isset($_REQUEST['username']))
        $username = trim($_REQUEST['username']);


    if (isset($_REQUEST['displayname']))
        $displayname = trim($_REQUEST['displayname']);


    if (empty($displayname))
        $displayname = $username;

    if (empty($password))
    {
        $send_welcome_email = 1;
        $password = uniqid("pw");
    }

    if (empty($username))
        $errors[] = 'A username is required';

    if (empty($email))
        $errors[] = 'An email address is required';

    if (isset($_REQUEST['group']))
        $group = (int) $_REQUEST['group'];



    if (!empty($errors))
    {

        zapier_showerror($errors);
        exit;
    }
    else
    {


		$regOptions = array(
			'username' => $username,
			'email' => $email,
			'password' => $password,
			'password_check' => $password,
			'check_reserved_name' => true,
			'check_password_strength' => false,
			'check_email_ban' => false,
			'send_welcome_email' => $send_welcome_email,
			'require' => isset($_POST['emailActivate']) ? 'activation' : 'nothing',
		);

		if (empty($_POST['requireAgreement']) && empty($modSettings['force_gdpr']))
			$regOptions['theme_vars']['agreement_accepted'] = time();

		if (empty($_POST['requirePolicyAgreement']) && empty($modSettings['force_gdpr']))
			$regOptions['theme_vars']['policy_accepted'] = time();

        $regOptions['extra_register_vars']['real_name'] = $displayname;
        // GDPR Helper
	//	if (empty($_POST['requireAgreement']) && empty($modSettings['gpdr_policydate']))
		   // $regOptions['theme_vars']['gpdr_policydate'] = time();


		require_once($sourcedir . '/Subs-Members.php');
		$memberID = registerMember($regOptions,true);
		if (!is_array($memberID))
		{

			// update the membergroup if set
				if (!empty($group))
					$smcFunc['db_query']('', '
			UPDATE {db_prefix}members
			SET id_group = ' . $group . '
			WHERE ID_MEMBER = ' . $memberID
	);

            $response = array();
            $response['result'] = 'success';
            $response['memberid'] = $memberID;

            // Show password in response if we had to generate it.
            if (empty($_REQUEST['password']))
                $response['password'] = $password;

            echo safe_json_encode($response);

            exit;
        }
        else
        {
            zapier_showerror($memberID);
            exit;
        }

    }


}

// Creates post or topic
if ($action == 'createpost')
{

    $subject = $_REQUEST['subject'];
    $message = $_REQUEST['message'];
    $boardID =  (int) $_REQUEST['boardid'];
    $memberID = (int) $_REQUEST['memberid'];
    $topicID = (int) $_REQUEST['topicid'];

    $errors = array();

    if (empty($boardID))
        $errors[] = 'A board id is required';


    if (empty($subject))
        $errors[] = 'A subject is required';

    if (empty($message))
        $errors[] = 'A message is required';

     if (empty($errors))
     {

         // Create the post
         require_once($sourcedir . '/Subs-Post.php');
         $msgOptions = array(
             'id' => 0,
             'subject' => $subject,
             'body' => $message,
             'icon' => 'xx',
             'smileys_enabled' => 1,
             'attachments' => array(),
         );
         $topicOptions = array(
             'id' => $topicID,
             'board' => $boardID,
             'poll' => null,
             'lock_mode' => 0,
             'sticky_mode' => null,
             'mark_as_read' => true,
         );
         $posterOptions = array(
             'id' => $memberID,
             'update_post_count' => (empty($memberID) ? 0 : 1),
         );


         preparsecode($msgOptions['body']);
         createPost($msgOptions, $topicOptions, $posterOptions);

         // Out put data

         $topicid = $topicOptions['id'];

         $msgID = $msgOptions['id'];


            $response = array();
            $response['result'] = 'success';
            $response['topicid'] =  $topicid;
            $response['messageid'] =   $msgID;

            echo safe_json_encode($response);


     }
     else
     {
            zapier_showerror($errors);
            exit;
     }
}



function safe_json_encode($value){
    if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
        $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        $encoded = json_encode($value);
    }
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return $encoded;
        case JSON_ERROR_DEPTH:
            return 'Maximum stack depth exceeded'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_STATE_MISMATCH:
            return 'Underflow or the modes mismatch'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_CTRL_CHAR:
            return 'Unexpected control character found';
        case JSON_ERROR_SYNTAX:
            return 'Syntax error, malformed JSON'; // or trigger_error() or throw new Exception()
        case JSON_ERROR_UTF8:
            $clean = utf8ize($value);
            return safe_json_encode($clean);
        default:
            return 'Unknown error'; // or trigger_error() or throw new Exception()
    }
}


function utf8ize($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } else if (is_string ($mixed)) {
        return utf8_encode($mixed);
    }
    return $mixed;
}


function zapier_showerror($errors = array())
{
        $response = array();
        $response['result'] = 'error';
        $response['errors'] = $errors;
        echo safe_json_encode($response);
}



?>