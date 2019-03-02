<?php
/*
SMF Trader System
Version 1.6
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function tradermain()
{
    if (function_exists("set_tld_regex") )
	    loadtemplate('Trader2.1');
    else
        loadtemplate('Trader2');

	// Load the language files
	if (loadlanguage('Trader') == false)
		loadLanguage('Trader','english');

	// Trader actions
	$subActions = array(
		'main' => 'TraderMainView',
		'report' => 'Report',
		'report2' => 'Report2',
		'submit' => 'Submit',
		'detail' => 'ViewDetail',
		'delete' => 'Delete',
		'delete2' => 'Delete2',
		'submit2' => 'Submit2',
		'admin' => 'AdminSettings',
		'admin2' => 'AdminSettings2',
		'approve' => 'ApproveRating',
		'bulkactions' => 'BulkActions',
	);

	@$sa = $_GET['sa'];

	// Follow the sa or just go to administration.
	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		$subActions['main']();
}

function TraderMainView()
{
	global $context, $txt, $smcFunc, $scripturl, $modSettings;

	$context['sub_template']  = 'main_trader';

	@$memid = (int) $_REQUEST['id'];

	if (empty($memid))
		fatal_error($txt['smftrader_nomemberselected'], false);

	$request = $smcFunc['db_query']('', "
	SELECT
		real_name FROM {db_prefix}members
	WHERE ID_MEMBER = $memid LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($request);

	$mcount = $smcFunc['db_affected_rows']();

	if ($mcount != 1)
		fatal_error($txt['smftrader_nomemberselected'], false);

	$context['traderid'] = $memid;
	$context['tradername'] = $row['real_name'];


	$smcFunc['db_query']('', "
	SELECT
		feedbackid
	FROM {db_prefix}feedback
	WHERE approved = 1 AND ID_MEMBER =" . $context['traderid']);
	$context['tradecount'] = $smcFunc['db_affected_rows']();

	$result = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total,salevalue
	FROM {db_prefix}feedback
	
	WHERE approved = 1 AND ID_MEMBER = " . $context['traderid'] . " GROUP BY salevalue" );
	$context['neturalcount'] = 0;
	$context['pcount'] = 0;
	$context['ncount'] = 0;
	while($row = $smcFunc['db_fetch_assoc']($result))
	{
		if ($row['salevalue'] == 0)
		{
			$context['pcount'] = $row['total'];
		}
		else if ($row['salevalue'] == 2)
		{
			$context['ncount'] = $row['total'];
		}
		else if ($row['salevalue'] == 1)
		{
			$context['neturalcount'] = $row['total'];
		}

	}
	$smcFunc['db_free_result']($result);

	$context['tradecount_nonetural'] = ($context['pcount'] +  $context['ncount']);

	// Get the view type
	@$view = (int) $_GET['view'];
	if (empty($view))
			$view = 0;


			$queryextra = '';
			switch($view)
			{
				case 0:
				$queryextra = '';
				break;
				case 1:
				$queryextra = ' AND f.saletype = 1';

				break;
				case 2:
				$queryextra = ' AND f.saletype = 0';
				break;
				case 3:
				$queryextra = ' AND f.saletype = 2';

				break;
				default:
				fatal_error($txt['smftrader_invalidview'], false);
				break;
			}


	$context['start'] = (int) $_REQUEST['start'];

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total
	FROM ({db_prefix}feedback AS f)
	LEFT JOIN {db_prefix}members AS m ON (f.FeedBackMEMBER_ID = m.ID_MEMBER) 
	WHERE f.ID_MEMBER = " . $context['traderid'] . "   AND f.approved = 1 $queryextra ");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);

	$leftJoinClassifieds = '';
	$selectClassifiedsSQL = '';

	if (IsClassifiedsInstalled() == true)
	{
		$selectClassifiedsSQL =', l.ID_LISTING, l.title ';
		$leftJoinClassifieds = ' LEFT JOIN {db_prefix}class_listing as l ON (l.ID_LISTING = f.ID_LISTING) ';
	}

	$dbresult = $smcFunc['db_query']('', "
	SELECT
			f.saletype, f.feedbackid, f.FeedBackMEMBER_ID, f.topicurl,
			f.comment_short, f.salevalue, f.saledate, m.real_name  $selectClassifiedsSQL
	FROM ({db_prefix}feedback AS f)
	LEFT JOIN {db_prefix}members AS m ON (f.FeedBackMEMBER_ID = m.ID_MEMBER) 
	$leftJoinClassifieds
	WHERE f.ID_MEMBER = " . $context['traderid'] . "  AND f.approved = 1 $queryextra ORDER BY f.feedbackid DESC LIMIT $context[start]," . $modSettings['trader_feedbackperpage']);
	$context['trader_feedback'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['trader_feedback'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);

	$context['page_index'] = constructPageIndex($scripturl . '?action=trader&id=' . $context['traderid'] . ';view=' . $view , $_REQUEST['start'], $total, $modSettings['trader_feedbackperpage']);

	// Set the page title
	$context['page_title'] = $txt['smftrader_feedbacktitle'] . ' - ' . $row['real_name'];


}

function Submit()
{
	global $context, $txt, $smcFunc, $user_info;

	is_not_guest();

	// Check if they are allowed to submit feedback
	isAllowedTo('smftrader_feedback');


	$context['sub_template']  = 'submit';

	@$memid = (int) $_GET['id'];

	if (empty($memid))
		fatal_error($txt['smftrader_nomemberselected'],false);

	$request = $smcFunc['db_query']('', "
	SELECT
		real_name FROM {db_prefix}members
	WHERE ID_MEMBER = $memid LIMIT 1");
	$row = $smcFunc['db_fetch_assoc']($request);

	$mcount = $smcFunc['db_affected_rows']();

	if ($mcount != 1)
		fatal_error($txt['smftrader_nomemberselected'],false);

	if ($user_info['id'] == $memid)
		fatal_error($txt['smftrader_errfeedbackself'] ,false);

	$context['traderid'] = $memid;
	$context['tradername'] = $row['real_name'];
	$context['page_title'] = $txt['smftrader_submittitle'] . ' - ' . $row['real_name'];

	if (IsClassifiedsInstalled() == true)
	{


		// Gets any listed that is completed with bids
		// WHERE the LIST OWNER = CURRENT VIEWER AND BIDDER = PROFILEID
		$context['class_listings_trader'] = array();

		$request = $smcFunc['db_query']('', "
		SELECT
			l.title, l.ID_LISTING, m.real_name, m.ID_MEMBER, f.feedbackid, f.FeedBackMEMBER_ID  
		FROM ({db_prefix}class_listing as l, {db_prefix}class_bids  as b)
			LEFT JOIN {db_prefix}members as m ON (m.ID_MEMBER = b.ID_MEMBER)
			LEFT JOIN {db_prefix}feedback as f ON (l.ID_LISTING = f.ID_LISTING AND  f.FeedBackMEMBER_ID   = " . $user_info['id'] . ") 
		WHERE b.ID_LISTING = l.ID_LISTING AND b.bid_accepted = 1 AND l.listingstatus = 2 AND l.ID_MEMBER = " . $user_info['id'] . " AND b.ID_MEMBER = $memid ");
		
		

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			

				
			if (empty($row['feedbackid']))
				$context['class_listings_trader'][] = $row;
	
		}
		$smcFunc['db_free_result']($request);

		// Gets any listed that is completed with bids
		// WHERE the LIST OWNER =  PROFILEID  AND BIDDER = CURRENT VIEWER
		
		
	
		$request = $smcFunc['db_query']('', "
		SELECT
			l.title, l.ID_LISTING, m.real_name, m.ID_MEMBER, f.feedbackid, f.FeedBackMEMBER_ID  
		FROM ({db_prefix}class_listing as l, {db_prefix}class_bids  as b)
			LEFT JOIN {db_prefix}members as m ON (m.ID_MEMBER = l.ID_MEMBER) 
			LEFT JOIN {db_prefix}feedback as f ON (l.ID_LISTING = f.ID_LISTING AND f.FeedBackMEMBER_ID  = " . $user_info['id'] . ") 
		WHERE b.ID_LISTING = l.ID_LISTING AND b.bid_accepted = 1 AND l.listingstatus = 2 AND l.ID_MEMBER =  $memid AND b.ID_MEMBER = " . $user_info['id']);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			

			
			if (empty($row['feedbackid']))
				$context['class_listings_trader'][] = $row;
		}
		$smcFunc['db_free_result']($request);


	}


}

function Submit2()
{
	global $smcFunc, $txt, $modSettings, $user_info;

	is_not_guest();

	// Check if they are allowed to submit feedback
	isAllowedTo('smftrader_feedback');

	// Get the trader id
	$traderid = (int) $_REQUEST['id'];


	if ($user_info['id'] == $traderid)
		fatal_error($txt['smftrader_errfeedbackself'] ,false);

	// Check if comment posted
	$shortcomment = $smcFunc['htmlspecialchars'](substr($_REQUEST['shortcomment'], 0, 100),ENT_QUOTES);

	if ($shortcomment == '')
		fatal_error($txt['smftrader_errshortcoment'],false);

	$ID_LISTING = 0;
	$topicurl = $smcFunc['htmlspecialchars']($_REQUEST['topicurl'],ENT_QUOTES);
	$salevalue = (int) $_REQUEST['salevalue'];
	$saletype = (int) $_REQUEST['saletype'];
	$longcomment = $smcFunc['htmlspecialchars']($_REQUEST['longcomment'],ENT_QUOTES);
	switch($saletype)
	{
		case 0:
		break;

		case 1:
		break;

		case 2:

		break;

		default:
		fatal_error($txt['smftrader_errsaletype'],false);
		break;
	}
	switch($salevalue)
	{
		case 0:
		break;

		case 1:
		break;

		case 2:
		break;

		default:
		fatal_error($txt['smftrader_errsalevalue'],false);
		break;
	}

	// Get the date
	$tradedate = time();



	// Get the approval
	if ($modSettings['trader_approval'] == 1)
	{
		$approval = (allowedTo('smftrader_autorating') ? 1 : 0);
	}
	else
		$approval = 1;

	// Admin's always approved
	if (allowedTo('admin_forum'))
		$approval = 1;

	if (IsClassifiedsInstalled()== true)
	{
		if (isset($_REQUEST['listingid']))
		{
			$listID = (int) $_REQUEST['listingid'];
			$listStatus = CheckIfInClassifieds($listID, $traderid);

			if ($listStatus == true)
				$ID_LISTING  = $listID;
				
				
				

			// Make sure you can't rate a listing twice.
			if (!empty( $ID_LISTING))
			{
				$request = $smcFunc['db_query']('', "
				SELECT
					COUNT(*) as total 
				FROM {db_prefix}feedback 
				WHERE ID_LISTING = $ID_LISTING AND FeedBackMEMBER_ID = " . $user_info['id']);
				$classrow = $smcFunc['db_fetch_assoc']($request);
				
				if ($classrow['total'] > 0)
					fatal_error($txt['smftrader_err_no_classifieds_listing'],false);
					
			}


		}
	}

	// Check Classifieds if you must have listing setting
	if (isset($modSettings['class_set_trader_feedback']))
	{
		if ($modSettings['class_set_trader_feedback'] == 1 && empty($ID_LISTING))
			fatal_error($txt['smftrader_err_classifieds_listing'],false);
	}


	// Finally Insert it into the db
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}feedback
			(ID_MEMBER, comment_short, comment_long, topicurl, saletype, salevalue,
			 saledate, FeedBackMEMBER_ID, approved, ID_LISTING)
		VALUES ($traderid, '$shortcomment', '$longcomment', '$topicurl',$saletype,
		 $salevalue, $tradedate, " . $user_info['id'] . ",$approval,$ID_LISTING)");

	$id = $smcFunc['db_insert_id']('{db_prefix}feedback', 'feedbackid');

	if ($approval == 1)
	{

		SendTraderPMByID($id);

		redirectexit('action=trader&id=' . $traderid);
	}
	else
		fatal_error($txt['smftrader_form_notapproved'], false);

}

function Report()
{
	global $context, $txt;

	is_not_guest();

	$context['sub_template']  = 'report';

	@$feedid = (int) $_GET['feedid'];
	if (empty($feedid))
		fatal_error($txt['smftrader_errnofeedselected'], false);

	$context['feedid'] = $feedid;


	$context['page_title'] = $txt['smftrader_reporttitle'];
}

function Report2()
{
	global $smcFunc, $webmaster_email, $sourcedir, $scripturl, $txt, $sourcedir, $modSettings;

	include $sourcedir . '/Subs-Post.php';

	is_not_guest();

	@$comment = $smcFunc['htmlspecialchars']($_REQUEST['comment'],ENT_QUOTES);

	if ($comment == '')
		fatal_error($txt['smftrader_errnocomment'], false);

	// Add link to trader to comment field.
	@$feedid = (int) $_REQUEST['feedid'];

	$result = "
	SELECT f.saletype, f.feedbackid, f.ID_MEMBER, f.FeedBackMEMBER_ID, f.comment_short,
	f.topicurl, f.comment_long, f.salevalue, f.saledate, m.real_name
	FROM {db_prefix}feedback AS f,{db_prefix}members AS m
	WHERE f.feedbackid = $feedid AND f.FeedBackMEMBER_ID = m.ID_MEMBER";

	$dbresult = $smcFunc['db_query']('', $result);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);
	$comment .= "\n"  . $txt['smftrader_commentmadeby'] . '[url=' . $scripturl . '?action=profile;u=' . $row['FeedBackMEMBER_ID'] . ']' . $row['real_name'] . '[/url]';
	$comment .= "\n\n" . '[url=' . $scripturl . '?action=trader&id=' . $row['ID_MEMBER'] . ']' . $txt['smftrader_viewtrader'] . '[/url]';
	$comment .= "\n\n" . $txt['smftrader_title'];


	require_once($sourcedir . '/Subs-Post.php');
	$dbresult = $smcFunc['db_query']('', "
	SELECT
		m.ID_MEMBER
	FROM {db_prefix}membergroups AS g,{db_prefix}members AS m
	WHERE m.ID_GROUP = g.ID_GROUP AND m.ID_GROUP IN (" . $modSettings['trader_membergroupspm'] . ")");
	while($row2 = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$pm_register_recipients = array(
			'to' => array($row2['ID_MEMBER']),
			'bcc' => array(),
		);


		sendpm($pm_register_recipients,$txt['smftrader_title'] . ' ' . $txt['smftrader_badfeedback'],$comment);
	}
	$smcFunc['db_free_result']($dbresult);

	//if (!sendmail($webmaster_email, $txt['smftrader_title'] . ' ' . $txt['smftrader_badfeedback'],$comment))
	//	fatal_error($txt['smftrader_failedreport'],false);

	redirectexit('action=trader&id=' . $row['ID_MEMBER'] );
}

function ViewDetail()
{
	global $context, $smcFunc, $txt;


	$context['sub_template']  = 'detail';

	@$feedid = (int) $_REQUEST['feedid'];
	if (empty($feedid))
		fatal_error($txt['smftrader_errnofeedselected'], false);

	$context['page_title'] = $txt['smftrader_title'] . ' - ' . $txt['smftrader_detailedfeedback'] ;
	$context['feedid'] = $feedid;



	$leftJoinClassifieds = '';
	$selectClassifiedsSQL = '';

	if (IsClassifiedsInstalled() == true)
	{
		$selectClassifiedsSQL =', l.ID_LISTING, l.title ';
		$leftJoinClassifieds = ' LEFT JOIN {db_prefix}class_listing as l ON (l.ID_LISTING = f.ID_LISTING) ';
	}


	$result = "
	SELECT
	f.saletype, f.feedbackid, f.ID_MEMBER, f.FeedBackMEMBER_ID, f.topicurl,
	f.comment_long, f.salevalue, f.saledate, m.real_name $selectClassifiedsSQL
	FROM ({db_prefix}feedback AS f,{db_prefix}members AS m)
	$leftJoinClassifieds 
	WHERE f.feedbackid = " . $context['feedid'] . " AND f.FeedBackMEMBER_ID = m.ID_MEMBER";

	$dbresult = $smcFunc['db_query']('', $result);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	

	
	$smcFunc['db_free_result']($dbresult);

	$context['trading_detail'] = $row;

}

function Delete()
{
	global $context, $smcFunc, $txt;

	// Check if they are allowed to delete feedback
	isAllowedTo('smftrader_deletefeed');

	@$feedid = (int) $_REQUEST['feedid'];
	if (empty($feedid))
		fatal_error($txt['smftrader_errnofeedselected'], false);

	$context['feedid'] = $feedid;

	$context['sub_template']  = 'delete';
	$context['page_title'] = $txt['smftrader_title'] . ' - ' . $txt['smftrader_deletefeedback'];

	$result = "SELECT f.saletype, f.feedbackid, f.ID_MEMBER, f.FeedBackMEMBER_ID,
	f.comment_short,  f.topicurl, f.comment_long, f.salevalue, f.saledate, m.real_name
	FROM {db_prefix}feedback AS f,{db_prefix}members AS m
	WHERE f.feedbackid = " . $context['feedid'] . " AND f.FeedBackMEMBER_ID = m.ID_MEMBER";

	$dbresult = $smcFunc['db_query']('', $result);
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$smcFunc['db_free_result']($dbresult);

	$context['trader_feedback'] = $row;


}

function Delete2()
{
	global $txt;

	// Check if they are allowed to delete feedback
	isAllowedTo('smftrader_deletefeed');

	@$feedid = (int) $_REQUEST['feedid'];
	if (empty($feedid))
		fatal_error($txt['smftrader_errnofeedselected'],false);

	@$redirectid = (int) $_REQUEST['redirect'];
	if (empty($redirectid))
		fatal_error($txt['smftrader_notrader'], false);

	DeleteByID($feedid);

	redirectexit('action=trader&id=' . $redirectid);
}

function AdminSettings()
{
	global $context, $txt,  $mbname, $smcFunc, $scripturl;

	isAllowedTo('admin_forum');

	@$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['smftrader_admin'],
			'description' => '',
			'tabs' => array(
				'admin' => array(
					'description' => $txt['smftrader_settings_admin_desc'],
				),


			),
		);



	$context['page_title'] = $mbname . ' - ' . $txt['smftrader_title'] . ' - ' . $txt['smftrader_text_settings'];
	$context['sub_template']  = 'settings';


	$context['start'] = (int) $_REQUEST['start'];


	$dbresult = $smcFunc['db_query']('', "
	SELECT
		COUNT(*) AS total
	FROM {db_prefix}feedback
	WHERE approved = 0");
	$row = $smcFunc['db_fetch_assoc']($dbresult);
	$total = $row['total'];
	$smcFunc['db_free_result']($dbresult);

	$dbresult = $smcFunc['db_query']('', "
	SELECT
	f.saletype, f.feedbackid, f.FeedBackMEMBER_ID,  f.topicurl, f.comment_short,
	f.salevalue, f.saledate, m.real_name, f.ID_MEMBER, u.real_name mainName
	FROM ({db_prefix}feedback AS f)
	LEFT JOIN {db_prefix}members AS m ON (f.FeedBackMEMBER_ID = m.ID_MEMBER)
	LEFT JOIN {db_prefix}members AS u ON (f.ID_MEMBER= u.ID_MEMBER)
	WHERE f.approved = 0 LIMIT $context[start],10");

	$context['trader_appoval'] = array();

	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		$context['trader_appoval'][] = $row;
	}
	$smcFunc['db_free_result']($dbresult);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=trader;sa=admin' , $_REQUEST['start'], $total, 10);

}

function AdminSettings2()
{
	isAllowedTo('admin_forum');

	$trader_approval =  isset($_REQUEST['trader_approval']) ? 1 : 0;
	$trader_use_pos_neg = isset($_REQUEST['trader_use_pos_neg']) ? 1 : 0;
	$trader_feedbackperpage = (int) $_REQUEST['trader_feedbackperpage'];

	updateSettings(
	array(
	'trader_approval' => $trader_approval,
	'trader_feedbackperpage' => $trader_feedbackperpage,
	'trader_use_pos_neg' => $trader_use_pos_neg,

	));

	redirectexit('action=admin;area=trader;sa=admin');
}

function ApproveRating()
{

	isAllowedTo('admin_forum');

	$id = (int) $_REQUEST['id'];


	ApproveByID($id);

	redirectexit('action=admin;area=trader;sa=admin');
}

function SendTraderPMByID($id)
{
	global $smcFunc, $sourcedir, $txt, $scripturl;

	$request = $smcFunc['db_query']('', "
		SELECT
			 m.real_name, f.comment_short, f.ID_MEMBER
		FROM
			({db_prefix}feedback AS f, {db_prefix}members as m)
		WHERE f.FeedBackMEMBER_ID = m.ID_MEMBER AND f.feedbackid  = $id LIMIT 1");


		$row = $smcFunc['db_fetch_assoc']($request);

		$smcFunc['db_free_result']($request);


		$pm_register_recipients = array(
			'to' => array($row['ID_MEMBER']),
			'bcc' => array(),
		);

	require_once($sourcedir . '/Subs-Post.php');

	sendpm($pm_register_recipients, $txt['smftrader_newrating'], $row['comment_short'] .  "\n\n" . $txt['smftrader_commentmadeby'] . $row['real_name'] . "\n" . "{$scripturl}?action=profile");

}

function BulkActions()
{
	isAllowedTo('admin_forum');

	if (isset($_REQUEST['ratings']))
	{

		$baction = $_REQUEST['doaction'];

		foreach ($_REQUEST['ratings'] as $value)
		{

			if ($baction == 'approve')
				ApproveByID($value);
			if ($baction == 'delete')
				DeleteByID($value);

		}
	}

	// Redirect to approval list
	redirectexit('action=admin;area=trader;sa=admin');
}

function DeleteByID($id)
{
	global $smcFunc;

	// Delete the comment
	$smcFunc['db_query']('', "
	DELETE FROM {db_prefix}feedback
	WHERE feedbackid = $id");
}

function ApproveByID($id)
{
	global $smcFunc;

	$smcFunc['db_query']('', "UPDATE {db_prefix}feedback
	SET approved = 1
	WHERE feedbackid = $id LIMIT 1");

	SendTraderPMByID($id);

	SendCommenterPMByID($id);
}


function SendCommenterPMByID($id)
{
	global $smcFunc, $sourcedir, $txt;

	$request = $smcFunc['db_query']('', "
		SELECT
			 m.real_name, f.comment_short, f.ID_MEMBER,f.FeedBackMEMBER_ID,
			 u.real_name MainName, m.time_offset
		FROM
			({db_prefix}feedback AS f)

		LEFT JOIN {db_prefix}members as m ON (f.FeedBackMEMBER_ID = m.ID_MEMBER)
		LEFT JOIN {db_prefix}members as u ON (f.ID_MEMBER = u.ID_MEMBER)
		WHERE  f.feedbackid  = $id LIMIT 1");


		$row = $smcFunc['db_fetch_assoc']($request);

		$smcFunc['db_free_result']($request);


		$pm_register_recipients = array(
			'to' => array($row['FeedBackMEMBER_ID']),
			'bcc' => array(),
		);

	require_once($sourcedir . '/Subs-Post.php');

	$finaltime = timeformat(forum_time(false));
	$finaltime = strip_tags($finaltime);

	sendpm($pm_register_recipients, sprintf($txt['smftrader_commenter_subject'],$row['MainName']),
	sprintf($txt['smftrader_commenter_body'],$row['MainName'],$finaltime));

}

function GetTraderInformation($memberID)
{
	global $smcFunc, $context;

	$result = $smcFunc['db_query']('', "
		SELECT
			COUNT(*) AS total,salevalue
		FROM {db_prefix}feedback
		WHERE approved = 1 AND ID_MEMBER = " . $memberID . " GROUP BY salevalue");
		$context['trader_mem_data'] = array();
		while($row = $smcFunc['db_fetch_assoc']($result))
		{
			$context['trader_mem_data'][] = $row;
		}
		$smcFunc['db_free_result']($result);
}

function GetTraderCount($memberID)
{
	global $smcFunc, $context;
	$smcFunc['db_query']('', "
	SELECT
		feedbackid
	FROM {db_prefix}feedback
	WHERE approved = 1 AND ID_MEMBER =" . $memberID);
	$tradecount = $smcFunc['db_affected_rows']();

	$context['trader_trade_count'] = $tradecount;

	return $tradecount;
}

function IsClassifiedsInstalled()
{
	global $modSettings;

	if (isset($modSettings['class_set_listings_per_page']))
		return true;
	else
		return false;

}

function CheckIfInClassifieds($ID_LISTING, $memid)
{
	global $smcFunc, $user_info;

	if (IsClassifiedsInstalled() == true)
	{

		// Gets any listed that is completed with bids
		// WHERE the LIST OWNER = CURRENT VIEWER AND BIDDER = PROFILEID
		$context['class_listings_trader'] = array();

		$request = $smcFunc['db_query']('', "
		SELECT
			l.title, l.ID_LISTING, m.real_name, m.ID_MEMBER
		FROM ({db_prefix}class_listing as l, {db_prefix}class_bids  as b)
			LEFT JOIN {db_prefix}members as m ON (m.ID_MEMBER = b.ID_MEMBER)
		WHERE b.ID_LISTING = l.ID_LISTING AND b.bid_accepted = 1 AND l.listingstatus = 2 AND l.ID_MEMBER = " . $user_info['id'] . " AND b.ID_MEMBER = $memid ");
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['class_listings_trader'][] = $row;
		}
		$smcFunc['db_free_result']($request);

		// Gets any listed that is completed with bids
		// WHERE the LIST OWNER =  PROFILEID  AND BIDDER = CURRENT VIEWER
		$request = $smcFunc['db_query']('', "
		SELECT
			l.title, l.ID_LISTING, m.real_name, m.ID_MEMBER
		FROM ({db_prefix}class_listing as l, {db_prefix}class_bids  as b)
			LEFT JOIN {db_prefix}members as m ON (m.ID_MEMBER = l.ID_MEMBER)
		WHERE b.ID_LISTING = l.ID_LISTING AND b.bid_accepted = 1 AND l.listingstatus = 2 AND l.ID_MEMBER =  $memid AND b.ID_MEMBER = " . $user_info['id']);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['class_listings_trader'][] = $row;
		}
		$smcFunc['db_free_result']($request);


		$found = false;
		foreach($context['class_listings_trader'] as $listing)
		{
			if ($listing['ID_LISTING'] == $ID_LISTING)
				$found = true;

		}


		if ($found == true)
			return true;
		else
			return false;


	}
	else
		return false;
}

?>