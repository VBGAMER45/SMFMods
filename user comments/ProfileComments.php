<?php
/*
Profile Comments
Version 1.4
by:vbgamer45
http://www.smfhacks.com
*/

if (!defined('SMF'))
	die('Hacking attempt...');

function CommentsMain()
{

	loadtemplate('ProfileComments');
	loadlanguage('Post');
	
	// Load the language files
	if (loadlanguage('ProfileComments') == false)
		loadLanguage('ProfileComments','english');

	// Profile Comments actions
	$subActions = array(
		'view' => 'ProfileCommentsview',
		'admin' => 'CommentsAdmin',
		'add' => 'ProfileCommentsAdd',
		'add2' => 'ProfileCommentsAdd2',
		'edit' => 'ProfileCommentsEdit',
		'edit2' => 'ProfileCommentsEdit2',
		'delete' => 'ProfileCommentsDelete',
		'approve' => 'ProfileCommentsApproveComment',
	);

	$sa = $_REQUEST['sa'];

	// Follow the sa or just go to administration.
	if (!empty($subActions[$sa]))
		$subActions[$sa]();
	else
		ProfileCommentsview();
}
function ProfileCommentsview()
{
	die(base64_decode('UG93ZXJlZCBieSBQcm9maWxlIENvbW1lbnRzIG1hZGUgYnkgdmJnYW1lcjQ1IGh0dHA6Ly93d3cuc21maGFja3MuY29t'));
}

function ProfileCommentsAdd()
{
	global $context, $mbname, $modSettings, $sourcedir, $txt;
	// Guests can't do this stuff
	is_not_guest();
	isAllowedTo('pcomments_add');

	$context['sub_template']  = 'commentsadd';

	// Set the page title
	$context['page_title'] = $mbname . $txt['pcomments_add1'];

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');	
	$context['post_box_name'] = 'comment';
	$context['post_form'] = 'cprofile';

}

function ProfileCommentsAdd2()
{
	global $db_prefix, $ID_MEMBER, $txt, $sourcedir, $scripturl;
	// Guests can't do this stuff
	is_not_guest();
	
	isAllowedTo('pcomments_add');

	@$subject = htmlspecialchars($_POST['subject'],ENT_QUOTES);
	@$comment = htmlspecialchars($_POST['comment'],ENT_QUOTES);
	@$userid = (int) $_POST['userid'];
	
	// Uncomment if you want the subject required
	//if ($subject == '')
	//	fatal_error($txt['pcomments_err_subject'],false);

	if ($comment == '')
		fatal_error($txt['pcomments_err_comment'],false);

	if (empty($userid))
		fatal_error($txt['pcomments_err_noprofile'],false);

	$commentdate = time();
	
	// Check if you have automatic approval
	$approved = (allowedTo('pcomments_autocomment') ? 1 : 0);

	db_query("INSERT INTO {$db_prefix}profile_comments
			(ID_MEMBER, comment, subject, date, COMMENT_MEMBER_ID,approved)
		VALUES ($ID_MEMBER,'$comment','$subject', $commentdate,$userid,$approved)", __FILE__, __LINE__);

	
	// Send PM to the user to let them know someone left a comment on their page
	if ($userid != $ID_MEMBER && $approved == 1)
	{
		
		// Lookup the user name's
		$dbresult = db_query("
		SELECT 
			realName 
		FROM {$db_prefix}members
		WHERE ID_MEMBER = $ID_MEMBER", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
		mysql_free_result($dbresult);
		
		$pm_recipients = array(
						'to' => array($userid),
						'bcc' => array(),
					);
				
		require_once($sourcedir . '/Subs-Post.php');
				
	
		$notify_body = $txt['pcomments_notify_pm_body'] . $scripturl . '?action=profile;u=' . $userid;
		$notify_body = str_replace("%poster",$row['realName'],$notify_body);
		
		$pm_from = array(
					'id' => $ID_MEMBER,
					'username' => '',
					'name' => '',
				);
				
		sendpm($pm_recipients,$txt['pcomments_notify_pm_subject'] , $notify_body,false,$pm_from);
	}
	

	// Redirect back to the profile
	redirectexit('action=profile;u=' . $userid);

}

function ProfileCommentsEdit()
{
	global $context, $mbname, $ID_MEMBER, $modSettings, $sourcedir, $txt;
	// Guests can't do this stuff
	is_not_guest();


	$context['sub_template']  = 'commentsedit';

	//Set the page title
	$context['page_title'] = $mbname . $txt['pcomments_edit1'];

	// Check if spellchecking is both enabled and actually working.
	$context['show_spellchecking'] = !empty($modSettings['enableSpellChecking']) && function_exists('pspell_new');

	/// Used for the editor
	require_once($sourcedir . '/Subs-Post.php');	
	$context['post_box_name'] = 'comment';
	$context['post_form'] = 'cprofile';
}

function ProfileCommentsEdit2()
{
	global $db_prefix, $ID_MEMBER, $txt;
	// Guests can't do this stuff
	is_not_guest();
	
	@$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);
	@$comment = htmlspecialchars($_POST['comment'], ENT_QUOTES);
	@$id = (int) $_POST['commentid'];
	
	// Uncomment if you want the subject required
	//if ($subject == '')
	//	fatal_error($txt['pcomments_err_subject'],false);
	
	if ($comment == '')
		fatal_error($txt['pcomments_err_comment'],false);

	if (empty($id))
		fatal_error($txt['pcomments_err_nocom']);

	// Check if you are allowed to edit the comment
	$dbresult = db_query("
	SELECT 
		p.ID_COMMENT, p.ID_MEMBER, p.COMMENT_MEMBER_ID 
	FROM {$db_prefix}profile_comments as p 
	WHERE p.ID_COMMENT = $id", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);

	if (allowedTo('pcomments_edit_any') || (allowedTo('pcomments_edit_own') && $row['ID_MEMBER'] == $ID_MEMBER))
	{
	
		// Check if you have automatic approval
		$approved = (allowedTo('pcomments_autocomment') ? 1 : 0);
		
		// Update the Comment
		db_query("UPDATE {$db_prefix}profile_comments
			SET subject = '$subject', comment = '$comment', approved = $approved 
		 WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);


		// Redirect back to profile
		redirectexit('action=profile;u=' . $row['COMMENT_MEMBER_ID']);

	}
	else
	{
		fatal_error($txt['pcomments_noedit'],false);
	}


}

function ProfileCommentsDelete()
{
	global $db_prefix, $ID_MEMBER, $txt;
	// Guests can't do this stuff
	is_not_guest();
	// Get the comment id
	$id = (int) @$_REQUEST['id'];

	if (empty($id))
		fatal_error($txt['pcomments_err_nocom']);

	// Check if you are allowed to delete the comment
	$dbresult = db_query("
	SELECT 
		p.ID_COMMENT, p.ID_MEMBER, p.COMMENT_MEMBER_ID 
	FROM {$db_prefix}profile_comments as p 
	WHERE p.ID_COMMENT = $id", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);

	if (allowedTo('pcomments_delete_any') || allowedTo('pcomments_delete_own') && $row['ID_MEMBER'] == $ID_MEMBER)
	{
		db_query("DELETE FROM {$db_prefix}profile_comments 
		WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);

		// Redirect back to profile
		redirectexit('action=profile;u=' . $row['COMMENT_MEMBER_ID']);
	}
	else
	{
		fatal_error($txt['pcomments_nodel'],false);
	}
}

function CommentsAdmin()
{
	global $db_prefix, $mbname, $txt, $context;
	
	isAllowedTo('admin_forum');

	adminIndex('comment_settings');

    
		$context['start'] = (int) $_REQUEST['start'];
	
		$dbresult = db_query("
		SELECT 
			COUNT(*) AS total 
		FROM {$db_prefix}profile_comments 
		WHERE  approved = 0", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
		$total =  $row['total'];
        
        $context['pcomments_count'] = $total;
	
		mysql_free_result($dbresult);
        
        $context['pcomments_list'] = array();
        
			$dbresult = db_query("
			SELECT 
				p.ID_COMMENT, p.ID_MEMBER, p.comment, p.subject, p.date, m.realName, p.COMMENT_MEMBER_ID, m2.realName ProfileName 
			FROM ({$db_prefix}profile_comments as p)  
			LEFT JOIN {$db_prefix}members AS m ON (p.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {$db_prefix}members AS m2 ON (p.COMMENT_MEMBER_ID = m2.ID_MEMBER)
			WHERE p.approved = 0 ORDER BY p.ID_COMMENT DESC  LIMIT $context[start],10", __FILE__, __LINE__);
			while($row = mysql_fetch_assoc($dbresult))
			{
				$context['pcomments_list'][] = $row;
				
			}
			mysql_free_result($dbresult);
        
        
        
	$context['sub_template']  = 'commentsadmin';

	// Set the page title
	$context['page_title'] = $mbname . $txt['pcomments_admin'];
}

function ProfileCommentsApproveComment()
{
	global $db_prefix, $sourcedir, $scripturl, $txt;
	
	isAllowedTo('admin_forum');
	
	// Get the comment id
	$id = (int) $_REQUEST['id'];
	
	db_query("UPDATE {$db_prefix}profile_comments
			SET approved = 1 WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);

		$result = db_query("
		SELECT 
			COMMENT_MEMBER_ID, ID_MEMBER 
		FROM {$db_prefix}profile_comments  
		WHERE ID_COMMENT = $id LIMIT 1", __FILE__, __LINE__);	
		$commentRow = mysql_fetch_assoc($result);
		mysql_free_result($result);
		
	// Lookup the user name's
		$dbresult = db_query("
		SELECT 
			realName 
		FROM {$db_prefix}members
		WHERE ID_MEMBER = " . $commentRow['ID_MEMBER'], __FILE__, __LINE__);
		$row = mysql_fetch_assoc($dbresult);
		mysql_free_result($dbresult);
		
		$pm_recipients = array(
						'to' => array($commentRow['COMMENT_MEMBER_ID']),
						'bcc' => array(),
					);
				
		require_once($sourcedir . '/Subs-Post.php');
				
	
		$notify_body = $txt['pcomments_notify_pm_body'] . $scripturl . '?action=profile;u=' . $commentRow['COMMENT_MEMBER_ID'];
		
		$notify_body = str_replace("%poster",$row['realName'],$notify_body);
		
		$pm_from = array(
					'id' =>  $commentRow['ID_MEMBER'],
					'username' => '',
					'name' => '',
				);
				
		sendpm($pm_recipients,$txt['pcomments_notify_pm_subject'] , $notify_body,false,$pm_from);
	
	redirectexit('action=comment;sa=admin');

}

function ShowUserBox($memCommID, $onlineColor = '')
{
	global $memberContext, $settings, $modSettings, $txt, $context, $scripturl, $options, $downloadSettings;

	
	echo '
	<b>', $memberContext[$memCommID]['link'], '</b>
							<div class="smalltext">';

		// Show the member's custom title, if they have one.
		if (isset($memberContext[$memCommID]['title']) && $memberContext[$memCommID]['title'] != '')
			echo '
								', $memberContext[$memCommID]['title'], '<br />';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (isset($memberContext[$memCommID]['group']) && $memberContext[$memCommID]['group'] != '')
			echo '
								', $memberContext[$memCommID]['group'], '<br />';

		// Don't show these things for guests.
		if (!$memberContext[$memCommID]['is_guest'])
		{
			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $memberContext[$memCommID]['group'] == '') && $memberContext[$memCommID]['post_group'] != '')
				echo '
								', $memberContext[$memCommID]['post_group'], '<br />';
			echo '
								', $memberContext[$memCommID]['group_stars'], '<br />';

			// Is karma display enabled?  Total or +/-?
			if ($modSettings['karmaMode'] == '1')
				echo '
								<br />
								', $modSettings['karmaLabel'], ' ', $memberContext[$memCommID]['karma']['good'] - $memberContext[$memCommID]['karma']['bad'], '<br />';
			elseif ($modSettings['karmaMode'] == '2')
				echo '
								<br />
								', $modSettings['karmaLabel'], ' +', $memberContext[$memCommID]['karma']['good'], '/-', $memberContext[$memCommID]['karma']['bad'], '<br />';

			// Is this user allowed to modify this member's karma?
			if ($memberContext[$memCommID]['karma']['allow'])
				echo '
								<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $memberContext[$memCommID]['id'], ';sesc=', $context['session_id'], '">', $modSettings['karmaApplaudLabel'], '</a>
								<a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $memberContext[$memCommID]['id'],  ';sesc=', $context['session_id'], '">', $modSettings['karmaSmiteLabel'], '</a><br />';

			// Show online and offline buttons?
			if (!empty($modSettings['onlineEnable']) && !$memberContext[$memCommID]['is_guest'])
				echo '
								', $context['can_send_pm'] ? '<a href="' . $memberContext[$memCommID]['online']['href'] . '" title="' . $memberContext[$memCommID]['online']['label'] . '">' : '', $settings['use_image_buttons'] ? '<img src="' . $memberContext[$memCommID]['online']['image_href'] . '" alt="' . $memberContext[$memCommID]['online']['text'] . '" border="0" style="margin-top: 2px;" />' : $memberContext[$memCommID]['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $memberContext[$memCommID]['online']['text'] . '</span>' : '', '<br /><br />';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $memberContext[$memCommID]['gender']['image'] != '')
				echo '
								', $txt['pcomments_txt_gender'], ': ', $memberContext[$memCommID]['gender']['image'], '<br />';

			// Show how many posts they have made.
			echo '
								', $txt['pcomments_txt_posts'], ': ', $memberContext[$memCommID]['posts'], '<br />
								<br />';

			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($memberContext[$memCommID]['avatar']['image']))
				echo '
								<div style="overflow: hidden; width: 100%;">', $memberContext[$memCommID]['avatar']['image'], '</div><br />';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $memberContext[$memCommID]['blurb'] != '')
				echo '
								', $memberContext[$memCommID]['blurb'], '<br />
								<br />';

			// This shows the popular messaging icons.
			echo '
								', $memberContext[$memCommID]['icq']['link'], '
								', $memberContext[$memCommID]['msn']['link'], '
								', $memberContext[$memCommID]['aim']['link'], '
								', $memberContext[$memCommID]['yim']['link'], '<br />';

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
	
					echo '
								<a href="', $memberContext[$memCommID]['href'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/icons/profile_sm.gif" alt="' . $txt['pcomments_txt_view_profile'] . '" title="' . $txt['pcomments_txt_view_profile'] . '" border="0" />' : $txt['pcomments_txt_view_profile']), '</a>';

				// Don't show an icon if they haven't specified a website.
				if ($memberContext[$memCommID]['website']['url'] != '')
					echo '
								<a href="', $memberContext[$memCommID]['website']['url'], '" title="' . $memberContext[$memCommID]['website']['title'] . '" target="_blank">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $txt['pcomments_txt_www'] . '" border="0" />' : $txt['pcomments_txt_www']), '</a>';

				// Don't show the email address if they want it hidden.
				if (empty($memberContext[$memCommID]['hide_email']))
					echo '
								<a href="mailto:', $memberContext[$memCommID]['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['pcomments_txt_profile_email'] . '" title="' . $txt['pcomments_txt_profile_email'] . '" border="0" />' : $txt['pcomments_txt_profile_email']), '</a>';

				// Since we know this person isn't a guest, you *can* message them.
				if ($context['can_send_pm'])
					echo '
								<a href="', $scripturl, '?action=pm;sa=send;u=', $memberContext[$memCommID]['id'], '" title="', $memberContext[$memCommID]['online']['label'], '">', $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($memberContext[$memCommID]['online']['is_online'] ? 'on' : 'off') . '.gif" alt="' . $memberContext[$memCommID]['online']['label'] . '" border="0" />' : $memberContext[$memCommID]['online']['label'], '</a>';
			}
		}
		// Otherwise, show the guest's email.
		elseif (empty($memberContext[$memCommID]['hide_email']))
			echo '
								<br />
								<br />
								<a href="mailto:', $memberContext[$memCommID]['email'], '">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['pcomments_txt_profile_email'] . '" title="' . $txt['pcomments_txt_profile_email'] . '" border="0" />' : $txt['pcomments_txt_profile_email']), '</a>';

		// Done with the information about the poster... on to the post itself.
		echo '
							</div>';
}

?>