<?php
/*
EzPortal
Version 5.6
by:vbgamer45
https://www.ezportal.com
Copyright 2010-2025 http://www.samsonsoftware.com
*/

function LoadEzPortalSettings()
{
	global $ezpSettings, $smcFunc, $modSettings, $boarddir, $boardurl;

	if (($ezpSettings = cache_get_data('ezpSettings', 90)) == null)
	{
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			variable, value
		FROM {db_prefix}ezp_settings");

		$ezpSettings = array();
		while ($row = $smcFunc['db_fetch_row']($dbresult))
			$ezpSettings[$row[0]] = $row[1];
		$smcFunc['db_free_result']($dbresult);

		// Be sure that the paths are setup
		if (empty($ezpSettings['ezp_url']))
			$ezpSettings['ezp_url'] = $boardurl . '/ezportal/';

		if (empty($ezpSettings['ezp_path']))
			$ezpSettings['ezp_path'] = $boarddir . '/ezportal/';

		// Check if cache is enabled
		if (!empty($modSettings['cache_enable']))
			cache_put_data('ezpSettings', $ezpSettings, 90);

	}
}

function MakeSEOUrl($data)
{
    $data = trim($data);
	$seourl = str_replace(" ","_",$data);
	$seourl = preg_replace('/[^a-z0-9_]/i', '', $seourl);
	$seourl= strtolower($seourl);

	return $seourl;
}

function SetupEditor()
{
	global $context, $ezpSettings;

		 $context['html_headers'] .= '<script type="text/javascript" src="' .$ezpSettings['ezp_url'] . 'tiny_mce/tiny_mce.js"></script>


<script type="text/javascript">
	tinyMCE.init({
		// General options
        mode : "specific_textareas",
        editor_selector : "myTextEditor",
        extended_valid_elements : ",input[*]",
		theme : "advanced",
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
convert_urls:true,
relative_urls:false,
remove_script_host:false,

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,pagebreak",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true
	});
</script>';

}

function UpdatePortalSettings($changeArray)
{
	global $smcFunc, $ezpSettings;

	if (empty($changeArray) || !is_array($changeArray))
		return;

	$replaceArray = array();
	foreach ($changeArray as $variable => $value)
	{
		if (isset($ezpSettings[$variable]) && $ezpSettings[$variable] == stripslashes($value))
			continue;
		elseif (!isset($ezpSettings[$variable]) && empty($value))
			continue;

		$replaceArray[] = "(SUBSTRING('$variable', 1, 255), SUBSTRING('$value', 1, 65534))";
		$ezpSettings[$variable] = stripslashes($value);
	}

	if (empty($replaceArray))
		return;

	$smcFunc['db_query']('', "
		REPLACE INTO {db_prefix}ezp_settings
			(variable, value)
		VALUES " . implode(',
			', $replaceArray));


	cache_put_data('ezpSettings', null, 90);
}

function EzPortalProcessBlockFile($filename, $iszip = true)
{
	global $sourcedir, $ezpSettings, $smcFunc, $modSettings;

	require_once($sourcedir . '/Class-Package.php');
	require_once($sourcedir . '/Subs-Package.php');

	// Check if the ezBlock file is coming via a website and fetch the ezBlock data
	if (strpos($filename, 'http://') !== false || strpos($filename, 'https://') !== false )
	{
		// If it is a compressed file read the file
		if ($iszip == true)
			$blockInfo = read_tgz_data(fetch_web_data($filename, '', true), '*/block-info.xml', true);
		else
			$blockInfo = fetch_web_data($filename, '', true);

	}
	else
	{

		// Check if the ezBlock does not exist already if so throw an error.
		if (!file_exists($ezpSettings['ezp_path'] . 'blocks/' . $filename))
			fatal_lang_error('ezp_err_no_ezblock_file_exists2',false);

		if ($iszip == false)
		{
			$blockInfo = file_get_contents($ezpSettings['ezp_path'] . 'blocks/' . $filename);
		}
		else if (is_file($ezpSettings['ezp_path'] . 'blocks/' . $filename))
			$blockInfo = read_tgz_file($ezpSettings['ezp_path'] . 'blocks/' . $filename, '*/block-info.xml', true);
		elseif (file_exists($ezpSettings['ezp_path'] . 'blocks/' . $filename . '/block-info.xml'))
			$blockInfo = file_get_contents($ezpSettings['ezp_path'] . 'blocks/' . $filename . '/block-info.xml');
		else
			fatal_lang_error('ezp_err_no_invalid_ezblockfile',false);
	}

	// Get the ezBlock into an easy to use XML Array
	$blockInfo = new xmlArray($blockInfo);



	// Check if the ezBlock header exists
	if (!$blockInfo->exists('block-info[0]'))
		fatal_lang_error('ezp_err_no_missing_ezblockheader',false);

	// Start at the ezBlockHeader
	$blockInfo = $blockInfo->path('block-info[0]');

	// Make the ezBlock into an array that we can actually use.
	$ezBlock = $blockInfo->to_array();

	/* Example Format
	[id] => vbgamer45:exampleblock
    [title] => Example Block
    [version] => 1.0
    [blocktype] => php
    [forumversion] => SMF 1.1.x
    [author] => vbgamer45
    [website] => http://www.ezportal.com
    [editable] => 1
    [can_cache] => 1
    [blockdata] => echo '<b>My Awesome EzPortal ezBlock<b> ' . $parameters[0];

	*/

	if (isset($ezBlock['title']))
		$title = $smcFunc['htmlspecialchars']($ezBlock['title'],ENT_QUOTES);
	else
		fatal_lang_error('ezp_err_no_block_title',false);

	if (isset($ezBlock['blocktype']))
		$blocktype = htmlspecialchars($ezBlock['blocktype'],ENT_QUOTES);
	else
		fatal_lang_error('ezp_err_no_block_type',false);

	if (isset($ezBlock['version']))
		$version = htmlspecialchars($ezBlock['version'],ENT_QUOTES);
	else
		$version = '';

	if (isset($ezBlock['forumversion']))
		$forumversion = htmlspecialchars($ezBlock['forumversion'],ENT_QUOTES);
	else
		$forumversion = '';

	if (isset($ezBlock['author']))
		$author = $smcFunc['htmlspecialchars']($ezBlock['author'],ENT_QUOTES);
	else
		$author = '';

	if (isset($ezBlock['website']))
		$website = $smcFunc['htmlspecialchars']($ezBlock['website'],ENT_QUOTES);
	else
		$website = '';

	if (isset($ezBlock['editable']))
		$editable = (int) $ezBlock['editable'];
	else
		$editable = 0;

	if (isset($ezBlock['can_cache']))
		$can_cache =  (int) $ezBlock['can_cache'];
	else
		$can_cache = 1;

	if (isset($ezBlock['blockdata']))
		$blockdata = $smcFunc['htmlspecialchars']($ezBlock['blockdata'],ENT_QUOTES);
	else
		$blockdata = '';

	// Insert the ezBlock into the database
	$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_blocks
		    (blocktitle, blocktype, blockversion, blockauthor, blockwebsite, can_cache, data_editable, forumversion, blockdata)
			VALUES ('$title', '$blocktype','$version','$author','$website',$can_cache,$editable,'$forumversion','$blockdata')");

	$blockID =  $smcFunc['db_insert_id']('{db_prefix}ezp_blocks', 'id_block');

	// Get all the parameters
	foreach($ezBlock as $key=> $parameter)
	{
		if (substr_count($key,"parameter") != 0)
		{
				$ptype  = 'string';
				$prequired = 0;
				$pdefault = '';
				$ptitle = '';
				$porder = 0;

				if (isset($parameter['type']))
					$ptype = htmlspecialchars($parameter['type'],ENT_QUOTES);

				if (isset($parameter['default']))
					$pdefault = $smcFunc['htmlspecialchars']($parameter['default'],ENT_QUOTES);

				if (isset($parameter['required']))
					$prequired = (int) $parameter['required'];

				if (isset($parameter['order']))
					$porder = (int) $parameter['order'];

				if (isset($parameter['title']))
					$ptitle = $smcFunc['htmlspecialchars']($parameter['title'],ENT_QUOTES);

				$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_block_parameters
			    (id_block, title, parameter_type, defaultvalue, required, parameter_name, id_order)
				VALUES ($blockID, '$ptitle', '$ptype','$pdefault', '$prequired', '$key', $porder)");

				$parameterID = $smcFunc['db_insert_id']('{db_prefix}ezp_block_parameters', 'id_parameter');

				// Insert custom select values
				if (isset($parameter['selectvalues']))
				{
					$selectValues = explode(",",$parameter['selectvalues']);
					$modSettings['disableQueryCheck'] = true;
					foreach($selectValues as $paramSelect)
					{
						$paramSelect = addslashes($paramSelect);
						$smcFunc['db_query']('', "INSERT INTO {db_prefix}ezp_paramaters_select
				    (id_block, id_parameter, selectvalue, selecttext)
					VALUES ($blockID, $parameterID, '$paramSelect','$paramSelect')");

					}
					$modSettings['disableQueryCheck'] = false;
				}

		}

	}

}


function EzBlockLoginBoxBlock($parameters = array(), $redirectUrl ='', $userbox = false, $startHtml = '', $endHtml = '')
{
	global $txt, $scripturl, $user_info, $sc, $context, $modSettings;
	global $boardurl;
	// Shows Login or logout box

		// Pass all the parematers
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'redirectUrl')
			$redirectUrl =  $myparam['data'];

	}

	echo $startHtml;

	if ($user_info['is_guest'])
	{

    	// Create a one time token.
        if ($context['ezportal21beta'] == true)
        {
    	createToken('login');
        }

		if ($redirectUrl != '')
			$_SESSION['login_url'] = $redirectUrl;
		
		if ($context['ezportal21beta'] == true)
			echo $txt['ezp_welcome_guest2'] . '<br />';
		else
			echo $txt['ezp_welcome_guest'] . '<br />';
		

		echo '
		<form action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '">
			<table border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td align="right"><label for="user">', $txt['ezp_built_login'], ':</label>&nbsp;</td>
					<td><input type="text" id="user" name="user" size="9" value="', $user_info['username'], '" /></td>
				</tr><tr>
					<td align="right"><label for="passwrd">', $txt['ezp_built_password'], ':</label>&nbsp;</td>
					<td><input type="password" name="passwrd" id="passwrd" size="9" /></td>
				</tr>';

			// Open ID
				if (!empty($modSettings['enableOpenID']))
			echo'
					<tr>
						<td colspan="2">
							<input type="text" name="openid_identifier" id="openid_url" size="25" class="input_text openid_login" />
						</td>
					</tr>';

				// Login for
				echo '<tr>
					<td align="right"><label for="passwrd">', $txt['ezp_txt_login_for'], ':</label>&nbsp;</td>
					<td><select name="cookielength">
						<option value="60">',$txt['ezp_txt_login_for_one_hour'],'</option>
						<option value="1440">',$txt['ezp_txt_login_for_day'],'</option>
						<option value="10080">',$txt['ezp_txt_login_for_week'],'</option>
						<option value="43200">',$txt['ezp_txt_login_for_month'],'</option>
						<option value="-1" selected="selected">',$txt['ezp_txt_login_for_forever'],'</option>
					</select></td>
				</tr>';

				echo '
				<tr>

					<td colspan="2">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">';

     				if ($context['ezportal21beta'] == true)
	        			{
				    	 echo '<input type="hidden" name="', $context['login_token_var'], '" value="', $context['login_token'], '">';

				        }



					echo '
                    <input type="submit" value="', $txt['ezp_built_login'], '" /></td>
				</tr>

';


	// One All Social Login
	if (!empty ($modSettings['oasl_api_key']) && !empty ($modSettings['oasl_enabled_providers']))
	{		
		// Extract the chosen providers.
		$providers = explode (',', trim ($modSettings['oasl_enabled_providers']));
		
		// Create Random integer to prevent id collision.
		$rand = mt_rand (99999, 9999999);

		
		echo '<tr>
					<td align="center" colspan="2">
			
				<div class="oneall_social_login_providers" id="oneall_social_login_providers_', $rand, '"></div>
				<script type="text/javascript">
					oneall.api.plugins.social_login.build("oneall_social_login_providers_', $rand, '", {
						"providers": [\'', implode ("', '", $providers), '\'], 
						"callback_uri": \'', $boardurl, '/index.php?action=oasl_callback;oasl_source=login\',
					});
				</script>
				<!-- OneAll.com / Social Login for SMF -->				
			</td></tr>';
	}




		echo '
			</table>


		</form>
		<span class="smalltext">
		<a href="', $scripturl, '?action=reminder">',$txt['ezp_loginbox_forgot'],'</a>
		</span>
		';
	}
	else
	{
		// Show basic member information

		// Avatar
		if (!empty($context['user']['avatar']))
			echo $context['user']['avatar']['image'], '<br />';

		/// Member Name
		echo $txt['hello_member_ndt'], ' <b>', $context['user']['name'] , '</b><br />';


		// Is the forum in maintenance mode?
		if ($context['in_maintenance'] && $context['user']['is_admin'])
			echo '
								<b>', $txt['ezp_maintain_mode'], '</b><br />';


		echo $txt['ezp_userbox_pm'] . ' <a href="', $scripturl, '?action=pm">' . $context['user']['messages'] . '</a> ' .  ($context['user']['unread_messages'] > 0 ? '[<strong>'. $context['user']['unread_messages'] . ' ' . $txt['ezp_userbox_pm_new'] .  '</strong>]' : '') . '<br />';


		if (!empty($context['unapproved_members']))
			echo '
								', $context['unapproved_members'] == 1 ? $txt['ezp_approve_thereis'] : $txt['ezp_approve_thereare'], ' <a href="', $scripturl, '?action=admin;area=viewmembers;sa=browse;type=approve">', $context['unapproved_members'] == 1 ? $txt['ezp_approve_member'] : $context['unapproved_members'] . ' ' . $txt['ezp_approve_members'], '</a> ', $txt['ezp_approve_members_waiting'], '<br />';

		echo '
					<a href="', $scripturl, '?action=unread">', $txt['ezp_userbox_unread_posts'], '</a> <br />
					<a href="', $scripturl, '?action=unreadreplies">', $txt['ezp_userbox_unread_replies'] , '</a><br />';



		if (isset($modSettings['gallery_max_filesize']))
		{
			// My Images Link
			echo '<a href="', $scripturl, '?action=gallery;sa=myimages;u=' . $user_info['id'] . '">', $txt['ezp_userbox_myimages'], '</a><br />';
		}
		if (isset($modSettings['down_set_files_per_page']))
		{
			// My Files Link
			echo '<a href="', $scripturl, '?action=downloads;sa=myfiles;u=' . $user_info['id'] . '">', $txt['ezp_userbox_myfiles'] , '</a><br />';
		}

		if (isset($modSettings['class_set_listings_per_page']))
		{
			// My Listings Link
			echo '<a href="', $scripturl, '?action=classifieds;sa=mylistings;u=' . $user_info['id'] . '">', $txt['ezp_userbox_mylistings'] , '</a><br />';
		}

		echo '<br />';

		// Show logout link
		if ($userbox  == false)
			echo '<a href="', $scripturl, '?action=logout;sesc=', $sc, '">', $txt['ezp_built_logout'], '</a>';

	}

	echo  $endHtml;

}

function EzBlockSearchBlock($parameters = array(), $defaultSearchValue = '', $startHtml = '', $endHtml = '')
{
	global $txt, $scripturl, $context;

	echo $startHtml;

	echo '
	<form action="',$scripturl,'?action=search2" method="post" accept-charset="', $context['character_set'], '">
	<input type="text" name="search" value="',$defaultSearchValue ,'" /><br />
	<input type="submit" name="submit" value="',$txt['ezp_built_search'],'" />
	<input type="hidden" name="advanced" value="0" />
	</form>
	<br />
	<a href="',$scripturl,'?action=search;advanced">',$txt['ezp_built_searchadvanced'],'</a>';

	echo $endHtml;


}

function EzBlockRecentPostsBlock($parameters = array(), $numPosts = 10, $exclude_boards = null, $format = 'vertical', $startHtml = '', $endHtml = '')
{

	global $context, $settings, $scripturl, $txt, $smcFunc;
	global $user_info, $modSettings, $ezpSettings;

	$numPosts = (int) $numPosts;
	$showColor = false;
	$ezblocklayoutid = 0;

	// Pass all the parematers
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'format')
			$format =  $myparam['data'];
		if ($myparam['parameter_name'] == 'numPosts')
			$numPosts = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'showcolor')
		{
			if ($myparam['data'] == 'true')
				$showColor = true;
			else
				$showColor = false;
		}

		if ($myparam['parameter_name'] == 'excludeboards')
		{
			$board = htmlspecialchars($myparam['data'],ENT_QUOTES);
			if (!empty($board))
				$exclude_boards = explode(",",$board);
		}
		
		
		if ($myparam['parameter_name'] == 'ezblocklayoutid')
				$ezblocklayoutid = (int) $myparam['data'];


	}

	echo $startHtml;

	if ($exclude_boards === null && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0)
		$exclude_boards = array($modSettings['recycle_board']);
	else
		$exclude_boards = empty($exclude_boards) ? array() : $exclude_boards;
		
		
	$posts = array();
		


	if (($posts = cache_get_data('ezprecentpost_block_' . $ezblocklayoutid . '_' . $modSettings['maxMsgID'] . '_' . $user_info['id'], 10)) == null)
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
			m.ID_MSG >= " . ($modSettings['maxMsgID'] - 2000 * min($numPosts, 5)) . " AND
		b.ID_BOARD = m.ID_BOARD" . (empty($exclude_boards) ? '' : "
			AND b.ID_BOARD NOT IN (" . implode(', ', $exclude_boards) . ")") . "
			AND $user_info[query_see_board]
		ORDER BY m.ID_MSG DESC
		LIMIT $numPosts");
	
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
		//	$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['ID_MSG']), array('<br />' => '&#10;')));
		//	if ($smcFunc['strlen']($row['body']) > 128)
			//	$row['body'] = $smcFunc['substr']($row['body'], 0, 128) . '...';
	
			// Censor it!
			censorText($row['subject']);
			//censorText($row['body']);
	
			// Build the array.
			$posts[] = array(
				'board' => array(
					'id' => $row['ID_BOARD'],
					'name' => $row['board_name'],
					'href' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
					'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['board_name'] . '</a>'
				),
				'topic' => $row['ID_TOPIC'],
				'poster' => array(
					'id' => $row['ID_MEMBER'],
					'name' => $row['poster_name'],
					'href' => empty($row['ID_MEMBER']) ? '' : $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
					'link' => empty($row['ID_MEMBER']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['poster_name'] . '</a>',
					'colorlink'  => empty($row['ID_MEMBER']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '"><font color="' . $row['online_color'] . '">' . $row['poster_name'] . '</font></a>',
				),
				'subject' => $row['subject'],
				'short_subject' => shorten_subject($row['subject'], 25),
				//'preview' => $row['body'],
				'time' => timeformat($row['poster_time']),
				'timestamp' => forum_time(true, $row['poster_time']),
				'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . ';topicseen#new',
				'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#msg' . $row['ID_MSG'] . '">' . $row['subject'] . '</a>',
				'new' => !empty($row['is_read']),
				'new_from' => $row['new_from'],
			);
		}
		$smcFunc['db_free_result']($request);
	

		// Check if cache is enabled
		if (!empty($modSettings['cache_enable']))
			cache_put_data('ezprecentpost_block_' . $ezblocklayoutid . '_' . $modSettings['maxMsgID'] . '_' . $user_info['id'], $posts, 10);

	}


	if  (empty($posts))
		return;

	echo '
		<table border="0">';


    $newImage = $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif';
    if ($context['ezportal21beta'] == true)
    {
        $newImage = $ezpSettings['ezp_url'] . 'new.gif';
    }

	if ($format == 'vertical')
	{
		foreach ($posts as $post)
		{


			echo '
				<tr>
					<td valign="top">
						<span class="smalltext">
						<a href="', $post['href'], '"><b>', $post['subject'], '</b></a>
						', $txt['ezp_built_by'], ' ', ($showColor == true ? $post['poster']['colorlink'] : $post['poster']['link']), '
						', $post['new'] ? '' : '<a href="' . $scripturl . '?topic=' . $post['topic'] . '.msg' . $post['new_from'] . ';topicseen#new"><img src="' . $newImage . '" alt="' . $txt['ezp_built_new'] . '" border="0" /></a>', '
						<br />', $post['time'], '
						</span>
						<hr />
					</td>

				</tr>';
		}
	}
	else
	{
		foreach ($posts as $post)
			echo '
				<tr>
					<td align="right" valign="top">
						[', $post['board']['link'], ']
					</td>
					<td valign="top">
						<a href="', $post['href'], '">', $post['subject'], '</a>
						', $txt['ezp_built_by'], ' ', ($showColor == true ? $post['poster']['colorlink'] : $post['poster']['link']), '
						', $post['new'] ? '' : '<a href="' . $scripturl . '?topic=' . $post['topic'] . '.msg' . $post['new_from'] . ';topicseen#new"><img src="' . $newImage . '" alt="' . $txt['ezp_built_new'] . '" border="0" /></a>', '
					</td>
					<td align="right" nowrap="nowrap">
						', $post['time'], '
					</td>
				</tr>';
	}
	echo '
		</table>';



	// Footer
	echo $endHtml;



}

function EzBlockRecentTopicsBlock($parameters = array(), $numTopics = 10, $exclude_boards = null, $format = 'vertical', $startHtml = '', $endHtml = '')
{
	global $context, $settings, $scripturl, $txt, $smcFunc;
	global $user_info, $modSettings, $ezpSettings;

	$numTopics = (int) $numTopics;
	$showColor = false;
	$ezblocklayoutid = 0;

	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'format')
			$format =  $myparam['data'];
		if ($myparam['parameter_name'] == 'numTopics')
			$numTopics = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'showcolor')
		{
			if ($myparam['data'] == 'true')
				$showColor = true;
			else
				$showColor = false;
		}

		if ($myparam['parameter_name'] == 'excludeboards')
		{
			$board = htmlspecialchars($myparam['data'],ENT_QUOTES);
			if (!empty($board))
				$exclude_boards = explode(",",$board);
		}
		
		if ($myparam['parameter_name'] == 'ezblocklayoutid')
			$ezblocklayoutid = (int) $myparam['data'];
		
	}

	echo $startHtml;


	if ($exclude_boards === null && !empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0)
		$exclude_boards = array($modSettings['recycle_board']);
	else
		$exclude_boards = empty($exclude_boards) ? array() : $exclude_boards;

	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';
		
	$posts = array();
		
	if (($posts = cache_get_data('ezprecenttopics_block_' . $ezblocklayoutid . '_' . $modSettings['maxMsgID'] . '_' . $user_info['id'], 10)) == null)
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
			m.ID_MSG >= " . ($modSettings['maxMsgID'] - 2000 * min($numTopics, 5)) . " AND
		t.ID_LAST_MSG = m.ID_MSG
			AND b.ID_BOARD = t.ID_BOARD" . (empty($exclude_boards) ? '' : "
			AND b.ID_BOARD NOT IN (" . implode(', ', $exclude_boards) . ")") . "
			AND $user_info[query_see_board]
			AND ms.ID_MSG = t.ID_FIRST_MSG
		ORDER BY t.ID_LAST_MSG DESC
		LIMIT $numTopics");
	
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		//$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['ID_MSG']), array('<br />' => '&#10;')));
	//	if ($smcFunc['strlen']($row['body']) > 128)
		//	$row['body'] = $smcFunc['substr']($row['body'], 0, 128) . '...';

		// Censor the subject.
		censorText($row['subject']);
		//censorText($row['body']);

		if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.'  .($context['ezportal21beta'] == true ? 'png' : 'gif')) ? 'images_url' : 'default_images_url';

		// Build the array.
		$posts[] = array(
			'board' => array(
				'id' => $row['ID_BOARD'],
				'name' => $row['board_name'],
				'href' => $scripturl . '?board=' . $row['ID_BOARD'] . '.0',
				'link' => '<a href="' . $scripturl . '?board=' . $row['ID_BOARD'] . '.0">' . $row['board_name'] . '</a>'
			),
			'topic' => $row['ID_TOPIC'],
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['poster_name'],
				'href' => empty($row['ID_MEMBER']) ? '' : $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => empty($row['ID_MEMBER']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['poster_name'] . '</a>',
				'colorlink'  => empty($row['ID_MEMBER']) ? $row['poster_name'] : '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '"><font color="' . $row['online_color'] . '">' . $row['poster_name'] . '</font></a>',
			),
			'subject' => $row['subject'],
			'short_subject' => shorten_subject($row['subject'], 25),
			//'preview' => $row['body'],
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . ';topicseen#new',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.msg' . $row['ID_MSG'] . '#new">' . $row['subject'] . '</a>',
			'new' => !empty($row['is_read']),
			'new_from' => $row['new_from'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.'  .($context['ezportal21beta'] == true ? 'png' : 'gif') . '" align="middle" alt="' . $row['icon'] . '" border="0" />',
		);
	}
	$smcFunc['db_free_result']($request);
	
		// Check if cache is enabled
		if (!empty($modSettings['cache_enable']))
			cache_put_data('ezprecenttopics_block_' . $ezblocklayoutid . '_' . $modSettings['maxMsgID']. '_' . $user_info['id'], $posts, 10);

	}	
	


	// Just return it.
	if  (empty($posts))
		return;

	echo '
		<table border="0">';


    $newImage = $settings['images_url'] . '/' . $context['user']['language'] . '/new.gif';
    if ($context['ezportal21beta'] == true)
    {
        $newImage = $ezpSettings['ezp_url'] . 'new.gif';
    }

	if ($format == 'vertical')
	{
		foreach ($posts as $post)
		{


			echo '
				<tr>
					<td valign="top">
						<span class="smalltext">
						<a href="', $post['href'], '"><b>', $post['subject'], '</b></a>
						', $txt['ezp_built_by'], ' ', ($showColor == true ? $post['poster']['colorlink'] : $post['poster']['link']), '
						', $post['new'] ? '' : '<a href="' . $scripturl . '?topic=' . $post['topic'] . '.msg' . $post['new_from'] . ';topicseen#new"><img src="' . $newImage . '" alt="' . $txt['ezp_built_new'] . '" border="0" /></a>', '
						<br />', $post['time'], '
						</span>
						<hr />
					</td>

				</tr>';
		}
	}
	else
	{
		foreach ($posts as $post)
			echo '
				<tr>
					<td align="right" valign="top">
						[', $post['board']['link'], ']
					</td>
					<td valign="top">
						<a href="', $post['href'], '">', $post['subject'], '</a>
						', $txt['ezp_built_by'], ' ', ($showColor == true ? $post['poster']['colorlink'] : $post['poster']['link']), '
						', $post['new'] ? '' : '<a href="' . $scripturl . '?topic=' . $post['topic'] . '.msg' . $post['new_from'] . ';topicseen#new"><img src="' . $newImage . '" alt="' . $txt['ezp_built_new'] . '" border="0" /></a>', '
					</td>
					<td align="right" nowrap="nowrap">
						', $post['time'], '
					</td>
				</tr>';
	}
	echo '
		</table>';


	echo $endHtml;
}


function EzBlockRandomNewsBlock($parameters = array())
{
	global $context;

	echo $context['random_news_line'];


}

function EzBlockThemeSelect($parameters = array(), $startHtml = '', $endHtml = '', $showPreview = 'Yes')
{
	global $txt, $smcFunc, $settings, $modSettings, $context, $modSettings, $user_info, $scripturl;

	echo $startHtml;


	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'showpreview')
			$showPreview = $myparam['data'];
	}

	// Generate theme list

	// Guests
	if ($user_info['is_guest'])
		$context['current_theme'] = $modSettings['theme_guests'];
	else
		$context['current_theme'] = $user_info['theme'];


	$context['current_theme'] = $settings['theme_id'];

	$context['available_themes'] = array();
	if (!empty($modSettings['knownThemes']))
	{
		$knownThemes = implode("', '", explode(',', $modSettings['knownThemes']));


		$ezblockThemeSelectCache = array();
		if (($ezblockThemeSelectCache = cache_get_data('ezblockThemeSelect', 90)) == null)
		{
			$request = $smcFunc['db_query']('', "
			SELECT ID_THEME, variable, value
			FROM {db_prefix}themes
			WHERE variable IN ('name', 'theme_url', 'theme_dir', 'images_url')" . (empty($modSettings['theme_default']) && !allowedTo('admin_forum') ? "
				AND ID_THEME IN ('$knownThemes')
				AND ID_THEME != 1" : '') . "
				AND ID_THEME != 0
			LIMIT " . count(explode(',', $modSettings['knownThemes'])) * 8);
			while ($row = $smcFunc['db_fetch_assoc']($request))
			{
				$ezblockThemeSelectCache[] = $row;
			}
			$smcFunc['db_free_result']($request);

			cache_put_data('ezblockThemeSelect', $ezblockThemeSelectCache, 90);

		}

		foreach($ezblockThemeSelectCache as $row)
		{
			if (!isset($context['available_themes'][$row['ID_THEME']]))
			{
				$context['available_themes'][$row['ID_THEME']] = array(
					'id' => $row['ID_THEME'],
					'selected' => ($context['current_theme'] == $row['ID_THEME']) ? 1 : 0,
				);
			}
			$context['available_themes'][$row['ID_THEME']][$row['variable']] = $row['value'];

		}

	}

echo '
	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	// Theme Select ezBlock
	var selectThemes = new Array();
	';

	foreach($context['available_themes'] as $c => $myTheme)
		echo 'selectThemes[',$myTheme['id'],'] = "' . $myTheme['images_url'] .  '/thumbnail.'  .($context['ezportal21beta'] == true ? 'png' : 'gif') . '";' . "\n";

	echo  '
	function ChangeThemePic(themeIndex)
	{
		document.frmPick.themePick.src = selectThemes[themeIndex];
	}
	// ]]></script>';

	// Generate the form
	echo '<form name="frmPick" id="frmPick" method="get" action="',$scripturl,'">';

	// Get selected theme
	if ($showPreview == 'Yes')
	{
		foreach($context['available_themes'] as $myTheme)
		{

			if ($myTheme['selected'] == 1)
			echo '
		<img id="themePick" src="' . $myTheme['images_url'] .  '/thumbnail.'  .($context['ezportal21beta'] == true ? 'png' : 'gif') . '" alt="*" />';

		}
	}
	if ($showPreview == 'Yes')
		echo '<br /><select name="theme" onchange="ChangeThemePic(this.value)">';
	else
		echo '<br /><select name="theme" onchange="ChangeThemePic(this.value)">';

	foreach($context['available_themes'] as $myTheme)
		echo '<option ',($myTheme['selected'] == 1 ? ' selected="selected" ' : '') ,' value="',$myTheme['id'],'">',$myTheme['name'],'</option>';


	echo '
	</select><br />
	<input type="submit" value="',$txt['ezp_built_changetheme'],'" />
	</form>';


	echo $endHtml;


}

function EzBlockUserInfo($parameters = array(), $startHtml = '', $endHtml = '')
{
	global $txt, $user_info, $smcFunc;

	echo $startHtml;

	if ($user_info['is_guest'])
	{
		EzBlockLoginBox('',true);
	}
	else
	{
		// Show the User Box
	}

	echo $endHtml;

}

function EzBlockWhoIsOnline($parameters = array(), $format = 'vertical', $startHtml = '', $endHtml = '')
{
	global $txt, $smcFunc, $user_info, $scripturl, $modSettings;
	
	$ezblocklayoutid = 0;

	// Pass all the parematers
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'format')
			$format =  $myparam['data'];
			
		if ($myparam['parameter_name'] == 'ezblocklayoutid')
			$ezblocklayoutid = (int) $myparam['data'];

	}


	echo $startHtml;

	$loggedInUsers = array();
	$totalGuests = 0;
	$totalHiddenMembers = 0;
	$totalBuddies = 0;
	$show_buddies = !empty($user_info['buddies']);

	$totalLoggedInMembers = 0;
	$totalLoggedInALL = 0;
	$can_moderate = allowedTo('moderate_forum');

	$data = array();
	if (($data = cache_get_data('ezwhos_block_' . $ezblocklayoutid, 10)) == null)
	{	
	
	$result = $smcFunc['db_query']('', "
		SELECT
			lo.ID_MEMBER, lo.log_time, mem.real_name, mem.member_name, mem.show_online,
			mg.online_color, mg.ID_GROUP
		FROM {db_prefix}log_online AS lo
		LEFT JOIN {db_prefix}members AS mem ON (mem.ID_MEMBER = lo.ID_MEMBER)
		LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))");


		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			$data[] = $row;
		}	
		
		$smcFunc['db_free_result']($result);
	
		// Check if cache is enabled
		if (!empty($modSettings['cache_enable']))
			cache_put_data('ezwhos_block_' . $ezblocklayoutid, $data, 10);

	}	

	
	if (!empty($data))
	foreach($data as $row)
	{
		if (!isset($row['real_name']))
			$totalGuests++;
		elseif (!empty($row['show_online']) || $can_moderate)
		{
			// Some basic color coding...
			if (!empty($row['online_color']))
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '" style="color: ' . $row['online_color'] . ';">' . $row['real_name'] . '</a>';
			else
				$link = '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">' . $row['real_name'] . '</a>';

			// Bold any buddies.
			if ($show_buddies && in_array($row['ID_MEMBER'], $user_info['buddies']))
			{
				$totalBuddies++;
				$link = '<b>' . $link . '</b>';
			}

			$loggedInUsers[$row['log_time'] . $row['member_name']] = array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['real_name'],
				'group' => $row['ID_GROUP'],
				'href' => $scripturl . '?action=profile;u=' . $row['ID_MEMBER'],
				'link' => $link,
				'hidden' => empty($row['show_online']),
				'is_last' => false,
			);
		}
		else
			$totalHiddenMembers++;
	}
	

	if (!empty($loggedInUsers))
	{
		krsort($loggedInUsers);
		$userlist = array_keys($loggedInUsers);
		$loggedInUsers[$userlist[count($userlist) - 1]]['is_last'] = true;
	}
	$totalLoggedInMembers = count($loggedInUsers) + $totalHiddenMembers;
	$totalLoggedInALL = $totalLoggedInMembers + $totalGuests;

	echo '
		', $totalGuests, ' ', $totalGuests == 1 ? $txt['ezp_guest'] : $txt['ezp_guests'], ', ', $totalLoggedInMembers, ' ', $totalLoggedInMembers == 1 ? $txt['ezp_user'] : $txt['ezp_users'];

	// Hidden users, or buddies?
	if ($totalHiddenMembers > 0 || $show_buddies)
		echo '
			(' . ($show_buddies ? ($totalBuddies . ' ' . ($totalBuddies == 1 ? $txt['ezp_buddy'] : $txt['ezp_buddies'])) : '') . ($show_buddies && $totalHiddenMembers ? ', ' : '') . (!$totalHiddenMembers ? '' : $totalHiddenMembers . ' ' . $txt['ezp_hidden']) . ')';

	echo '<br />';
	foreach ($loggedInUsers as $user)
	{
		echo $user['hidden'] ? '<i>' . $user['link'] . '</i>' : $user['link'], $user['is_last'] ? '' : ', ';

		if ($format == 'vertical')
		echo '<br />';
	}

	echo $endHtml;


}


function EzBlockPollBlock($parameters = array(), $pollTopicID = 0, $startHtml = '', $endHtml = '')
{
	global $smcFunc, $txt, $settings, $boardurl, $sc, $user_info;
	global $context;

	$t =  time();
	$pollOption = " AND (p.expire_time = 0 OR p.expire_time < $t) ORDER BY RAND() ";
	$board = 0;
	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'pollTopicID')
		{
			$pollTopicID = (int) $myparam['data'];
			if (!empty($pollTopicID))
				$pollOption = " AND t.ID_TOPIC = $pollTopicID";
		}

		if ($myparam['parameter_name'] == 'board')
			$board = htmlspecialchars($myparam['data'],ENT_QUOTES);

	}

	if (!empty($board))
	{
		$pollOption = " AND b.ID_BOARD IN($board) " . $pollOption;
	}


	$topic = (int) $pollTopicID;

	// Html Header
	echo $startHtml;


	$boardsAllowed = boardsAllowedTo('poll_view');

	if (empty($boardsAllowed))
		return array();



	$request = $smcFunc['db_query']('', "
		SELECT
			p.id_poll, p.question, p.guest_vote, t.ID_TOPIC, p.voting_locked, p.hide_results, p.expire_time, p.max_votes, b.id_board 
		FROM ({db_prefix}topics AS t, {db_prefix}polls AS p, {db_prefix}boards AS b)
		WHERE p.ID_POLL = t.ID_POLL 
			AND b.ID_BOARD = t.ID_BOARD
			AND $user_info[query_see_board]" . (!in_array(0, $boardsAllowed) ? "
			AND b.ID_BOARD IN (" . implode(', ', $boardsAllowed) . ")" : '') . " $pollOption
		LIMIT 1");

	// Either this topic has no poll, or the user cannot view it.
	if ($smcFunc['db_num_rows']($request) == 0)
		return array();

	$row = $smcFunc['db_fetch_assoc']($request);
	$pollinfo = $row;
	$topic = (int) $pollinfo['ID_TOPIC'];
	$smcFunc['db_free_result']($request);

	// Check if they can vote.
	$already_voted = false;
	if (!empty($row['expire_time']) && $row['expire_time'] < time())
		$allow_vote = false;
	elseif ($user_info['is_guest'])
	{
		// There's a difference between "allowed to vote" and "already voted"...
		$allow_vote = $row['guest_vote'];

		// Did you already vote?
		if (isset($_COOKIE['guest_poll_vote']) && in_array($row['id_poll'], explode(',', $_COOKIE['guest_poll_vote'])))
		{
			$already_voted = true;
		}
	}
	elseif (!empty($row['voting_locked']) || !allowedTo('poll_vote', $row['id_board']))
		$allow_vote = false;
	else
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}log_polls
			WHERE id_poll = {int:current_poll}
				AND id_member = {int:current_member}
			LIMIT 1',
			array(
				'current_member' => $user_info['id'],
				'current_poll' => $row['id_poll'],
			)
		);
		$allow_vote = $smcFunc['db_num_rows']($request) == 0;
		$already_voted = $allow_vote;
		$smcFunc['db_free_result']($request);
	}

	// Can they view?
	$is_expired = !empty($row['expire_time']) && $row['expire_time'] < time();
	$allow_view_results = allowedTo('moderate_board') || $row['hide_results'] == 0 || ($row['hide_results'] == 1 && $already_voted) || $is_expired;

	$request = $smcFunc['db_query']('', "
		SELECT COUNT(DISTINCT ID_MEMBER)
		FROM {db_prefix}log_polls
		WHERE ID_POLL = $row[id_poll]");
	list ($total) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$request = $smcFunc['db_query']('', "
		SELECT ID_CHOICE, label, votes
		FROM {db_prefix}poll_choices
		WHERE ID_POLL = $row[id_poll]");
	$options = array();
	$total_votes = 0;
	while ($rowChoice = $smcFunc['db_fetch_assoc']($request))
	{
		censorText($rowChoice['label']);

		$options[$rowChoice['ID_CHOICE']] = array($rowChoice['label'], $rowChoice['votes']);
		$total_votes += $rowChoice['votes'];
	}
	$smcFunc['db_free_result']($request);

	$return = array(
		'id' => $row['id_poll'],
		'image' => empty($pollinfo['voting_locked']) ? 'poll' : 'locked_poll',
		'question' => $row['question'],
		'total_votes' => $total,
		'is_locked' => !empty($pollinfo['voting_locked']),
		'allow_vote' => $allow_vote,
		'allow_view_results' => $allow_view_results,
		'topic' => $topic
	);

	// Calculate the percentages and bar lengths...
	$divisor = $total_votes == 0 ? 1 : $total_votes;
	foreach ($options as $i => $option)
	{
		$bar = floor(($option[1] * 100) / $divisor);
		$barWide = $bar == 0 ? 1 : floor(($bar * 5) / 3);
		$return['options'][$i] = array(
			'id' => 'options-' . $i,
			'percent' => $bar,
			'votes' => $option[1],
			'bar' => '<span style="white-space: nowrap;"><img src="' . $settings['images_url'] . '/poll_' . ($context['right_to_left'] ? 'right' : 'left') . '.gif" alt="" /><img src="' . $settings['images_url'] . '/poll_middle.gif" width="' . $barWide . '" height="12" alt="-" /><img src="' . $settings['images_url'] . '/poll_' . ($context['right_to_left'] ? 'left' : 'right') . '.gif" alt="" /></span>',
			'option' => parse_bbc($option[0]),
			'vote_button' => '<input type="' . ($row['max_votes'] > 1 ? 'checkbox' : 'radio') . '" name="options[]" id="options-' . $i . '" value="' . $i . '" class="input_' . ($row['max_votes'] > 1 ? 'check' : 'radio') . '" />'
		);
	}

	$return['allowed_warning'] = $row['max_votes'] > 1 ? sprintf($txt['poll_options6'], min(count($options), $row['max_votes'])) : '';


	if ($context['ezportal21beta'] == false)
	{
		if ($return['allow_vote'])
		{
			echo '
				<form class="ssi_poll" action="', $boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="', $context['character_set'], '">
					<strong>', $return['question'], '</strong><br />
					', !empty($return['allowed_warning']) ? $return['allowed_warning'] . '<br />' : '';

			foreach ($return['options'] as $option)
				echo '
					<label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label><br />';

			echo '
					<input type="submit" value="', $txt['ezp_built_submitvote'], '" class="button_submit" />
					<input type="hidden" name="poll" value="', $return['id'], '" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>';
		} else
		{
			echo '
				<div class="ssi_poll">
					<strong>', $return['question'], '</strong>
					<dl>';

			foreach ($return['options'] as $option)
			{
				echo '
						<dt>', $option['option'], '</dt>
						<dd>';

				if ($return['allow_view_results'])
				{
					echo '
							<div class="ssi_poll_bar" style="border: 1px solid #666; height: 1em">
								<div class="ssi_poll_bar_fill" style="background: #ccf; height: 1em; width: ', $option['percent'], '%;">
								</div>
							</div>
							', $option['votes'], ' (', $option['percent'], '%)';
				}

				echo '
						</dd>';
			}

			echo '
					</dl>', ($return['allow_view_results'] ? '
					<strong>' . $txt['ezp_built_totalvoters'] . ': ' . $return['total_votes'] . '</strong>' : ''), '
				</div>';
		}
	}
	else
	{
		// show 2.1
		if ($return['allow_vote'])
		{
			echo '
				<form class="ssi_poll" action="', $boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="', $context['character_set'], '">
					<strong>', $return['question'], '</strong><br>
					', !empty($return['allowed_warning']) ? $return['allowed_warning'] . '<br>' : '';

			foreach ($return['options'] as $option)
				echo '
					<label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label><br>';

			echo '
					<input type="submit" value="', $txt['poll_vote'], '" class="button">
					<input type="hidden" name="poll" value="', $return['id'], '">
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				</form>';
		}
		else
		{
			echo '
				<div class="ssi_poll">
					<strong>', $return['question'], '</strong>
					<dl>';

			foreach ($return['options'] as $option)
			{
				echo '
						<dt>', $option['option'], '</dt>
						<dd>';

				if ($return['allow_view_results'])
				{
					echo '
							<div class="ssi_poll_bar" style="border: 1px solid #666; height: 1em">
								<div class="ssi_poll_bar_fill" style="background: #ccf; height: 1em; width: ', $option['percent'], '%;">
								</div>
							</div>
							', $option['votes'], ' (', $option['percent'], '%)';
				}

				echo '
						</dd>';
			}

			echo '
					</dl>', ($return['allow_view_results'] ? '
					<strong>' . $txt['poll_total_voters'] . ': ' . $return['total_votes'] . '</strong>' : ''), '
				</div>';
		}

	}

	// Html Footer
	echo $endHtml;
}

function EzBlockSMFArticlesEzBlock($parameters = array(), $rows = 4, $articles = 4, $category = 0, $type = 'recent', $startHtml = '', $endHtml = '')
{
	global $txt, $smcFunc, $modSettings, $scripturl;

	$rows  = (int) $rows;
	$articles = (int)  $articles;
	$category = (int)  $category;


	if (!isset($modSettings['smfarticles_setarticlesperpage']))
	{
		echo $txt['ezp_articles_block_noinstall'];

		return;
	}

	// Pass all the parematers
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'type')
			$type =  $myparam['data'];
		if ($myparam['parameter_name'] == 'rows')
			$rows = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'articles')
			$articles = (int) $myparam['data'];

	}



	// Check if articles system is installed

	// Load the language files
	if (loadlanguage('Articles') == false)
		loadLanguage('Articles','english');

	// Html Header
	echo $startHtml;


	$maxrowlevel = $rows;
	echo '<table cellspacing="0" cellpadding="5" border="0" align="center" width="100%">
					';
			//Check what type it is
			$query = ' ';
			switch($type)
			{
				case 'recent':
					$query = "SELECT a.ID_ARTICLE, a.title, a.date, a.rating, a.totalratings, m.real_name, a.ID_MEMBER, a.description, a.views, a.commenttotal
					FROM {db_prefix}articles AS a
					LEFT JOIN {db_prefix}members AS m  ON (a.ID_MEMBER = m.ID_MEMBER)
					WHERE  a.approved = 1  ORDER BY a.ID_ARTICLE DESC LIMIT $articles";
				break;

				case 'viewed':
					$query = "SELECT a.ID_ARTICLE, a.title, a.date, a.rating, a.totalratings, m.real_name, a.ID_MEMBER, a.description, a.views, a.commenttotal
					FROM {db_prefix}articles AS a
					LEFT JOIN {db_prefix}members AS m  ON (a.ID_MEMBER = m.ID_MEMBER)
					WHERE a.approved = 1  ORDER BY a.views DESC LIMIT $articles";
				break;

				case 'mostcomments':
					$query = "SELECT a.ID_ARTICLE, a.title, a.date, a.rating, a.totalratings, m.real_name, a.ID_MEMBER, a.description, a.views, a.commenttotal
					FROM {db_prefix}articles AS a
					LEFT JOIN {db_prefix}members AS m  ON (a.ID_MEMBER = m.ID_MEMBER)
					WHERE a.approved = 1  ORDER BY p.commenttotal DESC LIMIT $articles";
				break;

				case 'toprated':
					$query = "SELECT a.ID_ARTICLE, a.title, a.date, a.rating, a.totalratings, m.real_name,
					a.ID_MEMBER, a.description, a.views, a.commenttotal,
					(a.rating / a.totalratings ) AS ratingaverage
					FROM {db_prefix}articles AS a
					LEFT JOIN {db_prefix}members AS m  ON (a.ID_MEMBER = m.ID_MEMBER)
					WHERE a.approved = 1  ORDER BY ratingaverage DESC LIMIT $articles ";
				break;
			}
			// Execute the SQL query
			$dbresult = $smcFunc['db_query']('', $query);
			$rowlevel = 0;
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			if ($rowlevel == 0)
				echo '<tr class="windowbg2">';

			echo '<td align="center"><a href="' , $scripturl , '?action=articles;sa=view;article=', $row['ID_ARTICLE'], '">', $row['title'], '</a><br />';
			echo '<span class="smalltext">';


				if (!empty($modSettings['smfarticles_disp_rating']))
				{
					echo $txt['smfarticles_crating'],' ' , EzGetStarsByPrecent(($row['totalratings'] != 0) ? ($row['rating'] / ($row['totalratings']* 5) * 100) : 0),'<br />';
				}


				if (!empty($modSettings['smfarticles_disp_totalcomment']))
					echo  $txt['smfarticles_txt_comments'], ' ', $row['commenttotal'], '<br />';


				if (!empty($modSettings['smfarticles_disp_views']))
					echo $txt['smfarticles_cviews'], ' ', $row['views'], '<br />';

				// Check if it was a guest article
				if (!empty($modSettings['smfarticles_disp_membername']))
					if ($row['real_name'] != '')
						echo $txt['ezp_gallery_text_by'],' <a href="', $scripturl, '?action=profile;u=', $row['ID_MEMBER'], '">', $row['real_name'], '</a><br />';
					else
						echo $txt['ezp_gallery_text_by'],' ', $txt['smfarticles_txtguest'], '<br />';

				if (!empty($modSettings['smfarticles_disp_date']))
					echo  $txt['smfarticles_cdate'],' ', timeformat($row['date']), '<br />';




			echo '</span></td>';


			if ($rowlevel < ($maxrowlevel-1))
				$rowlevel++;
			else
			{
				echo '</tr>';
				$rowlevel = 0;
			}
		}
		if ($rowlevel !=0)
		{
			echo '</tr>';
		}

	echo '
	      </table><br />';


	// Html Footer
	echo $endHtml;

}

function EzBlockDownloadsBlock($parameters = array(), $rows = 4, $type = 'recent', $files = 4, $category = 0, $startHtml = '', $endHtml = '')
{
	global $txt, $smcFunc, $scripturl,  $modSettings, $scripturl, $boardurl;

	$rows = (int) $rows;
	$files = (int) $files;
	$category = (int) $category;

	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'type')
			$type =  $myparam['data'];
		if ($myparam['parameter_name'] == 'rows')
			$rows = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'files')
			$files = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'category')
			$category = (int) $myparam['data'];

	}



	// Check if downloads system is installed
	if (!isset($modSettings['down_set_files_per_page']))
	{
		echo $txt['ezp_downloads_block_noinstall'];
		return;
	}

	// Html Header
	echo $startHtml;

	$isPro = false;

	if(isset($modSettings['down_set_t_image']))
		$isPro = true;


	// Load the language files
	if (loadlanguage('Downloads') == false)
		loadLanguage('Downloads','english');


	if (empty($modSettings['down_url']))
		$modSettings['down_url'] = $boardurl . '/downloads/';

	$maxrowlevel = $rows;
	echo '<table cellspacing="0" cellpadding="5" border="0" align="center" width="100%">
					';
			//Check what type it is
			$selectPro = '';
			$selectProJoin = '';
			if ($isPro == true)
			{
				$selectPro = " f.ID_PICTURE, f.thumbfilename, ";
				$selectProJoin =  " LEFT JOIN {db_prefix}down_file_pic AS f ON (f.ID_PICTURE = p.ID_PICTURE) ";
			}

			$query = ' ';
			switch($type)
			{

				case 'random':
					$query = "SELECT p.ID_FILE, p.commenttotal, $selectPro p.totalratings, p.rating, p.filesize, p.views, p.title, p.ID_MEMBER, m.real_name, p.date, p.description, p.totaldownloads
					FROM {db_prefix}down_file as p
					LEFT JOIN {db_prefix}members AS m  ON (m.ID_MEMBER = p.ID_MEMBER)
					$selectProJoin
					WHERE  p.approved = 1 ORDER BY RAND() DESC LIMIT $files";
				break;

				case 'recent':
					$query = "SELECT p.ID_FILE, p.commenttotal, $selectPro p.totalratings, p.rating, p.filesize, p.views, p.title, p.ID_MEMBER, m.real_name, p.date, p.description, p.totaldownloads
					FROM {db_prefix}down_file as p
					LEFT JOIN {db_prefix}members AS m  ON (m.ID_MEMBER = p.ID_MEMBER)
					$selectProJoin
					WHERE  p.approved = 1 ORDER BY p.ID_FILE DESC LIMIT $files";
				break;

				case 'viewed':
					$query = "SELECT p.ID_FILE, p.commenttotal, $selectPro p.totalratings, p.rating, p.filesize, p.views, p.title, p.ID_MEMBER, m.real_name, p.date, p.description, p.totaldownloads
					FROM {db_prefix}down_file as p
					LEFT JOIN {db_prefix}members AS m  ON (m.ID_MEMBER = p.ID_MEMBER)
					$selectProJoin
					WHERE p.approved = 1 ORDER BY  p.views DESC LIMIT $files";
				break;

				case 'mostcomments':
					$query = "SELECT p.ID_FILE, p.commenttotal, $selectPro p.totalratings, p.rating, p.filesize, p.views, p.title, p.ID_MEMBER, m.real_name, p.date, p.description, p.totaldownloads
					FROM {db_prefix}down_file as p
					LEFT JOIN {db_prefix}members AS m  ON (m.ID_MEMBER = p.ID_MEMBER)
					$selectProJoin
					WHERE p.approved = 1 ORDER BY p.commenttotal DESC LIMIT $files";
				break;

				case 'toprated':
					$query = "SELECT p.ID_FILE,  (p.rating / p.totalratings ) AS ratingaverage, p.commenttotal, $selectPro p.totalratings, p.rating, p.filesize, p.views, p.title, p.ID_MEMBER, m.real_name, p.date, p.description, p.totaldownloads
					FROM {db_prefix}down_file as p
					LEFT JOIN {db_prefix}members AS m  ON (m.ID_MEMBER = p.ID_MEMBER)
					$selectProJoin
					WHERE p.approved = 1 ORDER BY ratingaverage DESC LIMIT $files ";
				break;

				case 'downloads':
					$query = "SELECT p.ID_FILE, p.commenttotal, $selectPro p.totalratings, p.rating, p.filesize, p.views, p.title, p.ID_MEMBER, m.real_name, p.date, p.description, p.totaldownloads
					FROM {db_prefix}down_file as p
					LEFT JOIN {db_prefix}members AS m  ON (m.ID_MEMBER = p.ID_MEMBER)
					$selectProJoin
					WHERE p.approved = 1 ORDER BY  p.totaldownloads DESC LIMIT $files";
				break;
			}
			// Execute the SQL query
			$dbresult = $smcFunc['db_query']('', $query);
			$rowlevel = 0;
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			if ($rowlevel == 0)
				echo '<tr class="windowbg2">';

			echo '<td align="center"><a href="' . $scripturl . '?action=downloads;sa=view;id=' . $row['ID_FILE'] . '">',$row['title'],'</a><br />';

			if ($isPro == true && $modSettings['down_set_t_image'] && !empty($row['thumbfilename']))
				echo '<a href="' . $scripturl . '?action=downloads;sa=view;id=' . $row['ID_FILE'] . '"><img src="',$modSettings['down_url'],$row['thumbfilename'],'" alt="" /></a><br />';


			echo '<span class="smalltext">';
			if (!empty($modSettings['down_set_t_rating']))
				echo $txt['downloads_form_rating'] . EzGetStarsByPrecent(($row['totalratings'] != 0) ? ($row['rating'] / ($row['totalratings']* 5) * 100) : 0) . '<br />';

			if (!empty($modSettings['down_set_t_downloads']))
				echo $txt['downloads_text_downloads'] . $row['totaldownloads'] . '<br />';
			if (!empty($modSettings['down_set_t_views']))
				echo $txt['downloads_text_views'] . $row['views'] . '<br />';
			if (!empty($modSettings['down_set_t_filesize']))
				echo $txt['downloads_text_filesize'] . round($row['filesize'] / 1024, 2) . 'KB<br />';
			if (!empty($modSettings['down_set_t_date']))
				echo $txt['downloads_text_date'] . timeformat($row['date']) . '<br />';
			if (!empty($modSettings['down_set_t_comment']))
				echo $txt['downloads_text_comments'] . ' (<a href="' . $scripturl . '?action=downloads;sa=view;id=' . $row['ID_FILE'] . '">' . $row['commenttotal'] . '</a>)<br />';
			if (!empty($modSettings['down_set_t_username']))
			{
				if ($row['real_name'] != '')
					echo $txt['downloads_text_by'] . ' <a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">'  . $row['real_name'] . '</a><br />';
				else
					echo $txt['downloads_text_by'] . ' ' . $txt['downloads_guest'] . '<br />';
			}
			echo '</span></td>';


			if ($rowlevel < ($maxrowlevel-1))
				$rowlevel++;
			else
			{
				echo '</tr>';
				$rowlevel = 0;
			}
		}
		if ($rowlevel !=0)
		{
			echo '</tr>';
		}

	echo '
	      </table><br />';

	// Html Footer
	echo $endHtml;

}

function EzBlockGalleryBlock($parameters = array(), $rows = 4, $images = 4, $category = 0, $type = 'recent', $startHtml = '', $endHtml = '')
{
	global $txt, $smcFunc, $user_info, $context, $modSettings, $scripturl, $boardurl;


	$galleryPro = false;
	if (!$context['user']['is_guest'])
		$groupsdata = implode(',',$user_info['groups']);
	else
		$groupsdata = -1;

	if (isset($modSettings['gallery_set_count_child']))
		$galleryPro = true;

	$mediatype = 'both';

	// Pass all the parematers
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'type')
			$type =  $myparam['data'];
		if ($myparam['parameter_name'] == 'rows')
			$rows = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'images')
			$images = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'category')
			$category = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'mediatype')
			$mediatype =  $myparam['data'];

	}


	// Check if gallery is installed
	if (!isset($modSettings['gallery_max_filesize']))
	{
		echo $txt['ezp_gallery_block_noinstall'];
		return;
	}
	else
	{
		// Html Header
		echo $startHtml;
		$maxrowlevel = $rows;

		$mediaSQL = '';
		if ($mediatype == 'onlyaudiovideos')
			$mediaSQL = ' AND p.type > 0';
		if ($mediatype == 'onlyphotos')
			$mediaSQL = ' AND p.type = 0';


		if ($galleryPro == false)
		$dbresult = $smcFunc['db_query']('', "
		SELECT
			p.ID_PICTURE, p.commenttotal, p.filesize, p.views, p.thumbfilename, p.title,
			p.ID_MEMBER, m.real_name, p.date
		FROM {db_prefix}gallery_pic as p
		LEFT JOIN {db_prefix}members AS m ON (p.ID_MEMBER = m.ID_MEMBER)
		WHERE p.approved = 1 " . ($category != 0 ? ' AND p.ID_CAT = ' . $category : '' ) . "
			ORDER BY p.ID_PICTURE DESC LIMIT $images");
		else
			$dbresult = $smcFunc['db_query']('', "
		SELECT
			p.ID_PICTURE, p.commenttotal, p.filesize, p.views, p.thumbfilename, p.title,
			p.ID_MEMBER, m.real_name, p.date
		FROM {db_prefix}gallery_pic as p
		LEFT JOIN {db_prefix}members AS m ON (p.ID_MEMBER = m.ID_MEMBER)
		LEFT JOIN {db_prefix}gallery_usersettings AS s ON (s.ID_MEMBER = m.ID_MEMBER)
		LEFT JOIN {db_prefix}gallery_catperm AS c ON (c.ID_GROUP IN ($groupsdata) AND c.ID_CAT = p.ID_CAT)
		WHERE ((s.private =0 OR s.private IS NULL ) AND (s.password = '' OR s.password IS NULL )  AND p.USER_ID_CAT !=0 AND p.approved =1) OR (p.approved =1 AND p.USER_ID_CAT =0 AND (c.view IS NULL OR c.view =1))
					 " . ($category != 0 ? ' AND p.ID_CAT = ' . $category : '' ) . " $mediaSQL
			GROUP by p.ID_PICTURE, p.ID_PICTURE, p.commenttotal, p.filesize, p.views, p.thumbfilename, p.title,p.ID_MEMBER, m.real_name, p.date ORDER BY p.ID_PICTURE DESC LIMIT $images");


		$gallery_recent = array();
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			$gallery_recent[] = array(
				'ID_PICTURE' => $row['ID_PICTURE'],
				'title' => $row['title'],
				'thumbfilename' =>  $row['thumbfilename'],
				'views' => $row['views'],
				'filesize' => round($row['filesize'] / 1024, 2) . 'kb',
				'date' => timeformat($row['date']),
				'commenttotal' => $row['commenttotal'],
				'commentlink' => ' (<a href="' . $scripturl . '?action=gallery;sa=view;id=' . $row['ID_PICTURE'] . '">' . $row['commenttotal'] . '</a>)<br />',
				'profilelink' => ' <a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '">'  . $row['real_name'] . '</a><br />',
			);

		}
		$smcFunc['db_free_result']($dbresult);


			//Check if the gallery url has been set if not use the default
			if (empty($modSettings['gallery_url']))
				$modSettings['gallery_url'] = $boardurl . '/gallery/';

			echo '<table cellspacing="0" cellpadding="5" border="0" align="center" width="100%">
				';
			$rowlevel = 0;

			foreach ($gallery_recent as $picture)
			{
				if ($rowlevel == 0)
					echo '<tr>';

				echo '<td align="center"><a href="' . $scripturl . '?action=gallery;sa=view;' . (empty($modSettings['gallery_thumb_width']) ? 'pic=' : 'id=')  . $picture['ID_PICTURE'] . '"><img alt="" src="' . $modSettings['gallery_url'] . $picture['thumbfilename']  . '" /></a><br />
				<span class="smalltext">' . $txt['gallery_text_views'] . $picture['views'] . '<br />';
				echo $txt['ezp_gallery_text_comments'] . $picture['commentlink'];
				echo $txt['ezp_gallery_text_by'] . $picture['profilelink'];
				echo '</span></td>';

				if($rowlevel < ($maxrowlevel-1))
					$rowlevel++;
				else
				{
					echo '</tr>';
					$rowlevel = 0;
				}
			}
			if($rowlevel !=0)
				echo '</tr>';

			echo '</table>';

		// Html Footer
		echo $endHtml;
	}
}


function EzBlockGalleryRandomImage($parameters = array(), $category = 0, $numimages = 1, $startHtml = '', $endHtml = '')
{
	global $txt, $smcFunc, $context, $user_info, $modSettings, $scripturl, $boardurl;


	$galleryPro = false;
	if (!$context['user']['is_guest'])
		$groupsdata = implode(',',$user_info['groups']);
	else
		$groupsdata = -1;

	if (isset($modSettings['gallery_set_count_child']))
		$galleryPro = true;

	echo $startHtml;

	// Set the category
	$category = (int) $category;

	$numimages = (int)  $numimages;
	$rows = 4;
	$mediatype = 'both';

	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'category')
			$category = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'images')
			$numimages = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'rows')
			$rows = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'mediatype')
			$mediatype =  $myparam['data'];

	}

	// Check if gallery is installed
	if (!isset($modSettings['gallery_max_filesize']))
	{
		echo $txt['ezp_gallery_block_noinstall'];
	}
	else
	{
		if (empty($modSettings['gallery_url']))
			$modSettings['gallery_url'] = $boardurl . '/gallery/';

		// Load the Gallery language files
		if (loadlanguage('Gallery') == false)
			loadLanguage('Gallery','english');

		$mediaSQL = '';
		if ($mediatype == 'onlyaudiovideos')
			$mediaSQL = ' AND p.type > 0';
		if ($mediatype == 'onlyphotos')
			$mediaSQL = ' AND p.type = 0';

		$rowlevel = 0;
		$maxrowlevel = $rows;
		if ($galleryPro == false)
		$request = $smcFunc['db_query']('', "
		SELECT
			thumbfilename,ID_PICTURE,filename
		FROM {db_prefix}gallery_pic
		WHERE " . ($category == 0 ? '' : ' ID_CAT = ' . $category . ' AND ') . " approved = 1 ORDER BY RAND() LIMIT $numimages");
		else
		$request = $smcFunc['db_query']('', "
		SELECT
			p.thumbfilename, p.ID_PICTURE, p.filename
		FROM {db_prefix}gallery_pic as p
		LEFT JOIN {db_prefix}members AS m ON (p.ID_MEMBER = m.ID_MEMBER)
		LEFT JOIN {db_prefix}gallery_usersettings AS s ON (s.ID_MEMBER = m.ID_MEMBER)
		LEFT JOIN {db_prefix}gallery_catperm AS c ON (c.ID_GROUP IN ($groupsdata) AND c.ID_CAT = p.ID_CAT)
		WHERE ((s.private =0 OR s.private IS NULL ) AND (s.password = '' OR s.password IS NULL )  AND p.USER_ID_CAT !=0 AND p.approved =1) OR (p.approved =1 AND p.USER_ID_CAT =0 AND (c.view IS NULL OR c.view =1))
					 " . ($category != 0 ? ' AND p.ID_CAT = ' . $category : '' ) . " $mediaSQL 
			GROUP by p.ID_PICTURE, p.thumbfilename, p.ID_PICTURE, p.filename ORDER BY RAND() LIMIT " . $numimages);

		echo '<table align="center">
		';

		while($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ($rowlevel == 0)
					echo '<tr>';


			echo'<td align="center"><a href="', $scripturl, '?action=gallery;sa=view;', (empty($modSettings['gallery_thumb_width']) ? 'pic=' : 'id='), $row['ID_PICTURE'], '"><img src="',  $modSettings['gallery_url'] . $row['thumbfilename'] ,'" alt="" /></a></td>';

			if($rowlevel < ($maxrowlevel-1))
					$rowlevel++;
				else
				{
					echo '</tr>';
					$rowlevel = 0;
				}
		}

		if($rowlevel !=0)
				echo '</tr>';

		$smcFunc['db_free_result']($request);
		echo '
		</table>';
	}


	echo $endHtml;

}

function EzBlockLinksBlock($parameters = array(), $rows = 1, $links = 10, $category = 0, $type = 'recent', $startHtml = '', $endHtml = '')
{
	global $txt, $smcFunc, $scripturl, $modSettings;

	$rows = (int) $rows;
	$links = (int) $links;
	$category = (int)  $category;

	if (!isset($modSettings['smflinks_setlinksperpage']))
	{
		echo $txt['ezp_links_block_noinstall'];
		return;

	}


	// Pass all the parematers
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'links')
			$links = (int) $myparam['data'];


	}


	// Check if links is installed
	 $dbresult = $smcFunc['db_query']('', "
		SELECT
			l.ID_LINK,l.title,l.date, l.pagerank, l.alexa, l.rating, m.real_name, l.ID_MEMBER, l.description,l.hits
		FROM {db_prefix}links AS l
		LEFT JOIN {db_prefix}members AS m  ON (l.ID_MEMBER = m.ID_MEMBER)
		WHERE l.approved = 1 ORDER BY l.ID_LINK DESC
		LIMIT $links");
	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		echo '<a href="' . $scripturl . '?action=links;sa=visit;id=' . $row['ID_LINK'] . '" target="blank">' . $row['title'] . '</a><br />';
	}
	$smcFunc['db_free_result']($dbresult);



	// Html Header
	echo $startHtml;

	// Html Footer
	echo $endHtml;

}

function EzBlockStoreBlock($parameters = array(), $rows = 4, $products = 4, $type = 'recent', $category = 0, $startHtml = '', $endHtml = '')
{
	global  $smcFunc, $scripturl, $txt, $modSettings, $boardurl, $context;

	$rows = (int) $rows;

	// Check if store is installed
	if (!isset($modSettings['store_set_items_per_page']))
	{
		echo $txt['ezp_store_block_noinstall'] ;
		return;
	}

	// Pass all the parematers
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'type')
			$type =  $myparam['data'];
		if ($myparam['parameter_name'] == 'rows')
			$rows = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'products')
			$products = (int) $myparam['data'];

	}


	if (empty($modSettings['store_url']))
		$modSettings['store_url'] = $boardurl . '/store/';

	// Html Header
	echo $startHtml;



	// Load the language files
	if (loadlanguage('Store') == false)
		loadLanguage('Store', 'english');

	$g_manage = allowedTo('smfstore_manage');

	$maxrowlevel = $rows ;
	echo '<table cellspacing="0" cellpadding="5" border="0" align="center" width="100%" >
				';
			// Check what type it is
			$query = ' ';
			switch($type)
			{
				case 'recent':
					$query = "SELECT i.ID_ITEM, i.commenttotal, i.totalratings, i.rating,
					i.primaryID_PICTURE, i.productname, p.thumbfilename, i.price,i.currency,
					i.qtyinstock, i.needsstock, i.date, i.views
					FROM {db_prefix}store_item as i LEFT JOIN {db_prefix}store_item_pic as p ON (i.primaryID_PICTURE = p.ID_PICTURE)
					 WHERE  i.hideproduct = 0 ORDER BY i.ID_ITEM DESC LIMIT $products";
				break;

				case 'viewed':
					$query = "SELECT i.ID_ITEM, i.commenttotal, i.totalratings, i.rating,
					i.primaryID_PICTURE, i.productname, p.thumbfilename, i.price,i.currency,
					i.qtyinstock, i.needsstock, i.date, i.views
					FROM {db_prefix}store_item as i LEFT JOIN {db_prefix}store_item_pic as p ON (i.primaryID_PICTURE = p.ID_PICTURE)
					 WHERE  i.hideproduct = 0  ORDER BY  i.views DESC LIMIT $products";
				break;

				case 'mostcomments':
					$query = "SELECT i.ID_ITEM, i.commenttotal, i.totalratings, i.rating,
					i.primaryID_PICTURE, i.productname, p.thumbfilename, i.price,i.currency,
					i.qtyinstock, i.needsstock, i.date, i.views
					FROM {db_prefix}store_item as i LEFT JOIN {db_prefix}store_item_pic as p ON (i.primaryID_PICTURE = p.ID_PICTURE)
					 WHERE  i.hideproduct = 0  ORDER BY i.commenttotal DESC LIMIT $products";
				break;

				case 'toprated':
					$query = "SELECT i.ID_ITEM, i.commenttotal, i.totalratings, i.rating,
					(i.rating / i.totalratings ) AS ratingaverage,
					i.primaryID_PICTURE, i.productname, p.thumbfilename, i.price,i.currency,
					i.qtyinstock, i.needsstock, i.date, i.views
					FROM {db_prefix}store_item as i LEFT JOIN {db_prefix}store_item_pic as p ON (i.primaryID_PICTURE = p.ID_PICTURE)
					 WHERE  i.hideproduct = 0  ORDER BY ratingaverage DESC LIMIT $products";
				break;

				case 'random':
					$query = "SELECT i.ID_ITEM, i.commenttotal, i.totalratings, i.rating,
					i.primaryID_PICTURE, i.productname, p.thumbfilename, i.price,i.currency,
					i.qtyinstock, i.needsstock, i.date, i.views
					FROM {db_prefix}store_item as i LEFT JOIN {db_prefix}store_item_pic as p ON (i.primaryID_PICTURE = p.ID_PICTURE)
					 WHERE  i.hideproduct = 0 ORDER BY RAND() DESC LIMIT $products";
				break;

				case 'mostpurchased':

				break;


			}
			// Execute the SQL query
			$dbresult = $smcFunc['db_query']('', $query);
			$rowlevel = 0;
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			if($rowlevel == 0)
				echo '<tr class="windowbg2">';

			echo '<td align="center"><a href="', $scripturl, '?action=store;sa=view;id=', $row['ID_ITEM'], '">', $row['productname'], '</a><br />';

			if (!empty($row['primaryID_PICTURE']))
				echo '<a href="', $scripturl, '?action=store;sa=view;id=', $row['ID_ITEM'], '"><img src="', $modSettings['store_url'], $row['thumbfilename'], '" alt="" /></a><br />';


			echo '<span class="smalltext">';


			if (!empty($modSettings['store_set_t_price']))
			{

				echo $txt['store_text_price'] . Ezformatprice($row['price'],$row['currency']) . '<br />';

			}

			if (!empty($modSettings['store_set_t_stock']))
			{
				if ($row['needsstock'])
				{
					if ($row['qtyinstock'] > 0)
						echo $txt['store_text_instock'] . '<br />';
					else
						echo $txt['store_text_outofstock'] . '<br />';
				}
			}

			if (!empty($modSettings['store_set_t_date']))
				echo $txt['store_text_date'] . timeformat($row['date']) . '<br />';

			if (!empty($modSettings['store_set_t_rating']))
				echo $txt['store_form_rating'] . EzGetStarsByPrecent(($row['totalratings'] != 0) ? ($row['rating'] / ($row['totalratings']* 5) * 100) : 0) . '<br />';


			if (!empty($modSettings['store_set_t_comment']))
				echo $txt['store_text_comments'] . ' (<a href="' . $scripturl . '?action=store;sa=view;id=' . $row['ID_ITEM'] . '">' . $row['commenttotal'] . '</a>)<br />';
			if ($g_manage)
			{

				echo '&nbsp;<a href="' . $scripturl . '?action=store;sa=edititem;id=' . $row['ID_ITEM'] . '">' . $txt['store_text_edit'] . '</a>';
				echo '&nbsp;<a href="' . $scripturl . '?action=store;sa=deleteitem;id=' . $row['ID_ITEM'] . '">' . $txt['store_text_delete'] . '</a>';
			}

			echo '</span></td>';


			if($rowlevel < ($maxrowlevel-1))
				$rowlevel++;
			else
			{
				echo '</tr>';
				$rowlevel = 0;
			}
		}
		if($rowlevel !=0)
		{
			echo '</tr>';
		}

	echo '
	      </table><br />';
	// Free the Mysql Resoruces
	$smcFunc['db_free_result']($dbresult);



	// Html Footer
	echo $endHtml;

}

function EzBlockClassifiedsBlock($parameters = array(), $rows = 4, $listings = 4, $type = 'recent', $category = 0,  $format = 'vertical',  $startHtml = '', $endHtml = '')
{
	global  $smcFunc, $scripturl, $txt, $modSettings, $boardurl, $user_info, $context, $sourcedir, $boarddir;

	$rows = (int) $rows;
	$listings = (int) $listings;
	$category  = (int) $category;

	// Check if classifieds is installed
	if (!isset($modSettings['class_set_listings_per_page']))
	{
		echo $txt['ezp_classifieds_block_noinstall'];
		return;
	}



	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'type')
			$type =  $myparam['data'];
		if ($myparam['parameter_name'] == 'rows')
			$rows = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'listings')
			$listings = (int) $myparam['data'];

	}


	// Html Header
	echo $startHtml;


	// Load the language files
	if (loadlanguage('classifieds') == false)
		loadLanguage('classifieds', 'english');

	$g_manage = allowedTo('smfclassifieds_manage');

	$groupsdata = implode(',',$user_info['groups']);

	if (empty($modSettings['class_url']))
		$modSettings['class_url'] = $boardurl . '/classifieds/';

	if (empty($modSettings['class_path']))
		$modSettings['class_path'] = $boarddir . '/classifieds/';

	$maxrowlevel = $rows;
	echo '<table cellspacing="0" cellpadding="5" border="0" align="center" width="100%">

				';
			// Check what type it is
			$query = ' ';
			switch($type)
			{
				case 'recent':
					$query = "SELECT i.ID_LISTING, i.commenttotal,
					i.primaryID_PICTURE, i.title, p.thumbfilename, p.remotefilename, i.currentbid,i.currency,
					i.datelisted, i.views, i.is_auction, i.totalbids, i.ID_CAT, c.noprice, i.expiredate
					FROM {db_prefix}class_listing as i
					LEFT JOIN {db_prefix}class_listing_pic as p ON (i.primaryID_PICTURE = p.ID_PICTURE)
					LEFT JOIN {db_prefix}class_cat as c ON (i.ID_CAT = c.ID_CAT)
					LEFT JOIN {db_prefix}class_catperm AS r ON (r.ID_GROUP IN ($groupsdata) AND r.ID_CAT = i.ID_CAT)
					WHERE i.removed = 0 AND i.approved = 1  AND (r.view IS NULL OR r.view =1) GROUP BY i.ID_LISTING, i.commenttotal,
					i.primaryID_PICTURE, i.title, p.thumbfilename, p.remotefilename, i.currentbid,i.currency,
					i.datelisted, i.views, i.is_auction, i.totalbids, i.ID_CAT, c.noprice, i.expiredate ORDER BY i.ID_LISTING DESC LIMIT $listings";
				break;

				case 'viewed':
					$query = "SELECT i.ID_LISTING, i.commenttotal,
					i.primaryID_PICTURE, i.title, p.thumbfilename, p.remotefilename, i.currentbid,i.currency,
					i.datelisted, i.views, i.is_auction, i.totalbids, i.ID_CAT, c.noprice, i.expiredate
					FROM {db_prefix}class_listing as i
					LEFT JOIN {db_prefix}class_listing_pic as p ON (i.primaryID_PICTURE = p.ID_PICTURE)
					 LEFT JOIN {db_prefix}class_cat as c ON (i.ID_CAT = c.ID_CAT)
					 LEFT JOIN {db_prefix}class_catperm AS r ON (r.ID_GROUP IN ($groupsdata) AND r.ID_CAT = i.ID_CAT)
					WHERE i.removed = 0 AND i.approved = 1  AND (r.view IS NULL OR r.view =1) GROUP BY i.ID_LISTING, i.commenttotal,
					i.primaryID_PICTURE, i.title, p.thumbfilename, p.remotefilename, i.currentbid,i.currency,
					i.datelisted, i.views, i.is_auction, i.totalbids, i.ID_CAT, c.noprice, i.expiredate ORDER BY  i.views DESC LIMIT $listings";
				break;

  				case 'random':
					$query = "SELECT i.ID_LISTING, i.commenttotal,
					i.primaryID_PICTURE, i.title, p.thumbfilename, p.remotefilename, i.currentbid,i.currency,
					i.datelisted, i.views, i.is_auction, i.totalbids, i.ID_CAT, c.noprice, i.expiredate
					FROM {db_prefix}class_listing as i
					LEFT JOIN {db_prefix}class_listing_pic as p ON (i.primaryID_PICTURE = p.ID_PICTURE)
					 LEFT JOIN {db_prefix}class_cat as c ON (i.ID_CAT = c.ID_CAT)
					 LEFT JOIN {db_prefix}class_catperm AS r ON (r.ID_GROUP IN ($groupsdata) AND r.ID_CAT = i.ID_CAT)
					WHERE i.removed = 0 AND i.approved = 1  AND (r.view IS NULL OR r.view =1) GROUP BY i.ID_LISTING, i.commenttotal,
					i.primaryID_PICTURE, i.title, p.thumbfilename, p.remotefilename, i.currentbid,i.currency,
					i.datelisted, i.views, i.is_auction, i.totalbids, i.ID_CAT, c.noprice, i.expiredate ORDER BY  RAND() DESC LIMIT $listings";
				break;


				case 'mostcomments':
					$query = "SELECT i.ID_LISTING, i.commenttotal,
					i.primaryID_PICTURE, i.title, p.thumbfilename, p.remotefilename, i.currentbid,i.currency,
					i.datelisted, i.views, i.is_auction, i.totalbids, i.ID_CAT, c.noprice, i.expiredate
					FROM {db_prefix}class_listing as i
					LEFT JOIN {db_prefix}class_listing_pic as p ON (i.primaryID_PICTURE = p.ID_PICTURE)
					LEFT JOIN {db_prefix}class_cat as c ON (i.ID_CAT = c.ID_CAT)
					LEFT JOIN {db_prefix}class_catperm AS r ON (r.ID_GROUP IN ($groupsdata) AND r.ID_CAT = i.ID_CAT)
					WHERE i.removed = 0 AND i.approved = 1  AND (r.view IS NULL OR r.view =1) GROUP BY i.ID_LISTING, i.commenttotal,
					i.primaryID_PICTURE, i.title, p.thumbfilename, p.remotefilename, i.currentbid,i.currency,
					i.datelisted, i.views, i.is_auction, i.totalbids, i.ID_CAT, c.noprice, i.expiredate ORDER BY i.commenttotal DESC LIMIT $listings";
				break;

				case 'featured':
					$query = "SELECT i.ID_LISTING, i.commenttotal,
					i.primaryID_PICTURE, i.title, p.thumbfilename, p.remotefilename, i.currentbid,i.currency,
					i.datelisted, i.views, i.is_auction, i.totalbids, i.ID_CAT, c.noprice, i.expiredate
					FROM {db_prefix}class_listing as i
					LEFT JOIN {db_prefix}class_listing_pic as p ON (i.primaryID_PICTURE = p.ID_PICTURE)
					LEFT JOIN {db_prefix}class_cat as c ON (i.ID_CAT = c.ID_CAT)
					LEFT JOIN {db_prefix}class_catperm AS r ON (r.ID_GROUP IN ($groupsdata) AND r.ID_CAT = i.ID_CAT)
					WHERE i.removed = 0 AND i.approved = 1 AND i.featuredlisting = 1  AND (r.view IS NULL OR r.view =1) GROUP BY i.ID_LISTING, i.commenttotal,
					i.primaryID_PICTURE, i.title, p.thumbfilename, p.remotefilename, i.currentbid,i.currency,
					i.datelisted, i.views, i.is_auction, i.totalbids, i.ID_CAT, c.noprice, i.expiredate ORDER BY i.ID_LISTING DESC LIMIT $listings";
				break;


			}
			// Execute the SQL query
			$dbresult = $smcFunc['db_query']('', $query);
			$rowlevel = 0;
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{
			if ($rowlevel == 0)
				echo '<tr class="windowbg2">';

			echo '<td align="center"><a href="', $scripturl, '?action=classifieds;sa=view;id=', $row['ID_LISTING'], '">', $row['title'], '</a><br />';

			if (!empty($row['primaryID_PICTURE']) && $modSettings['class_catlist_showimage'])
			{

				if (empty($row['remotefilename']))
					echo '<a href="', $scripturl, '?action=classifieds;sa=view;id=', $row['ID_LISTING'], '"><img src="', $modSettings['class_url'], $row['thumbfilename'], '" alt="" /></a><br />';
				else
					echo '<a href="', $scripturl, '?action=classifieds;sa=view;id=', $row['ID_LISTING'], '"><img src="', $row['remotefilename'], '" alt="" /></a><br />';

			}

			echo '<span class="smalltext">';


			if (!empty($modSettings['class_catlist_currentprice']) && $row['noprice'] == 0)
				echo $txt['class_text_price'] . Ezformatprice($row['currentbid'],$row['currency']) . '<br />';


			if (!empty($modSettings['class_catlist_listingdate']))
            {
                if (!empty($modSettings['class_set_date_format_mdy']))
                    echo $txt['class_text_date'] .  date($modSettings['class_set_date_format_mdy'],$row['datelisted']), ' ', date($modSettings['class_set_date_format_hia'],$row['datelisted']) . '<br />';
                else
                    echo $txt['class_text_date'] . date("m/d/Y",$row['datelisted']) . ' ' . date("h:i a",$row['datelisted'])  . '<br />';

            }




			if ($modSettings['class_catlist_timeleft'])
			{
				echo  $txt['class_txt_time_left'] ;

				echo   ($row['expiredate'] == 0 ? $txt['class_expire_never'] :   ezblockclass_cattimeleft( date("Y",$row['expiredate']), date("m",$row['expiredate']), date("d",$row['expiredate']), date("H",$row['expiredate']), date("i",$row['expiredate']),date("s",$row['expiredate'])) ) , '<br />';

			}



			if (!empty($modSettings['class_catlist_numofbids']) && $row['noprice'] == 0 && $row['is_auction'] == 1)
				echo $txt['class_text_totalbids'] . ' ' . $row['totalbids'] . '<br />';


			if (!empty($modSettings['class_catlist_comments']))
				echo $txt['class_text_comments'] . ' (<a href="' . $scripturl . '?action=classifieds;sa=view;id=' . $row['ID_LISTING'] . '">' . $row['commenttotal'] . '</a>)<br />';

			if ($g_manage)
			{
				if ($row['is_auction'] == 0)
					echo '&nbsp;<a href="' . $scripturl . '?action=classifieds;sa=editlisting;id=' . $row['ID_LISTING'] . '">' . $txt['class_text_edit'] . '</a>';
				else
					echo '&nbsp;<a href="' . $scripturl . '?action=classifieds;sa=editauction;id=' . $row['ID_LISTING'] . '">' . $txt['class_text_edit'] . '</a>';


				echo '&nbsp;<a href="' . $scripturl . '?action=classifieds;sa=deletelisting;id=' . $row['ID_LISTING'] . '">' . $txt['class_text_delete'] . '</a>';

			}

			echo '</span></td>';


			if($rowlevel < ($maxrowlevel-1))
				$rowlevel++;
			else
			{
				echo '</tr>';
				$rowlevel = 0;
			}
		}
		if($rowlevel !=0)
		{
			echo '</tr>';
		}

	echo '
	      </table><br />';
	// Free the Mysql Resoruces
	$smcFunc['db_free_result']($dbresult);

		// Html Footer
		echo $endHtml;

}
function EzBlockRecentAttachmentsBlock($parameters = array(), $numToShow = 10)
{
	$numToShow = (int) $numToShow;
}
function EzBlockSMFArcadeBlock($parameters = array(), $type = 'random', $count = 1)
{
	global $settings, $modSettings, $sourcedir, $context, $user_info, $txt, $smcFunc, $scripturl, $boarddir;

	if (file_exists($sourcedir . '/Subs-Arcade.php'))
		$arcadePath = $sourcedir . '/Subs-Arcade.php';

	if (file_exists($boarddir . '/ArcadeSources/Subs-Arcade.php'))
		$arcadePath = $boarddir . '/ArcadeSources/Subs-Arcade.php';

	// Check if arcade is installed
	if (!isset($modSettings['arcadeVersion'])  || !file_exists($arcadePath))
	{
		echo $txt['ezp_arcade_block_noinstall'];
		return;
	}


	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'type')
			$type =  $myparam['data'];
		if ($myparam['parameter_name'] == 'count')
			$count = (int) $myparam['data'];
	}


	$user_info['query_see_game'] = '1';

	if ($type == 'random')
	{

		require_once($arcadePath);

		$context['arcade']['can_favorite'] = !empty($modSettings['arcadeEnableFavorites']) && !$user_info['is_guest'];

		echo '<div align="center">';
			$game = getGameInfo('random');

			$rating = $game['rating'];

			if ($rating > 0)
			{

                if ($context['ezportal21beta'] == false)
                {
    				$ratecode = str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" />' , $rating);
    				$ratecode .= str_repeat('<img src="' . $settings['images_url'] . '/star2.gif" alt="*" />' , 5 - $rating);
                }
                else
                {
				    $ratecode = str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" />' , $rating);
				    $ratecode .= str_repeat('<img src="' . $settings['images_url'] . '/membericons/iconadmin.png" alt="*" />' , 5 - $rating);
                }


			}

			echo '

						', $game['thumbnail'] != '' ? '<div><a href="' . $game['url']['play'] . '"><img src="' . $game['thumbnail'] . '" alt="" /></a></div>' : '', '
						<div><a href="', $game['url']['play'], '">', $game['name'], '</a></div>
					';

			if ($rating > 0)
				echo  $ratecode, '<br />';
		echo '</div>';


	}
	else if ($type == 'latestscores')
	{
		require_once($arcadePath);

		$scores = ArcadeLatestScores($count);


		if (!empty($scores))
		{
			foreach ($scores as $score)
				echo '
							<li class="clearfix">
								', sprintf($txt['ezp_arcade_latest_score_item'], $scripturl . '?action=arcade;sa=play;game=' . $score['game_id'], $score['name'], $score['score'], $score['memberLink']), '
								<div class="floatright">',  $score['time'], '</div>
							</li>';

		}
		else
			echo $txt['ezp_arcade_no_scores'];

	}
}
function EzBlockRecentWebPagesBlock($parameters = array(), $numToShow = 10)
{
	global $txt, $smcFunc, $scripturl, $ezpSettings, $boardurl;

	$numToShow = (int) $numToShow;

	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'numToShow')
			$numToShow = (int) $myparam['data'];
	}

	$dbresult = $smcFunc['db_query']('', "
	SELECT
		id_page, date, title, views
	FROM {db_prefix}ezp_page
	ORDER BY id_page DESC
	LIMIT $numToShow");
	

	while ($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		
		$pageurl = $scripturl . '?action=ezportal;sa=page;p='  . $row['id_page'];
			
		if (!empty($ezpSettings['ezp_pages_seourls']))
				$pageurl = $boardurl . '/pages/' . MakeSEOUrl($row['title']) . '-' . $row['id_page'];	
		
		echo '<a href="',$pageurl, '">',$row['title'],'</a><br />';

	}
	$smcFunc['db_free_result']($dbresult);



}

function EzBlockBoardNewsBlock($parameters = array(),$board = null, $length = null, $limit = null, $start = null)
{
	global $scripturl, $smcFunc, $txt, $settings, $modSettings, $context, $boardurl;

	$showlike = false;
	$ezblocklayoutid = 0;
	$showgoogleplus = false;

    if (empty($board))
        $board = 0;

	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'board')
			$board = htmlspecialchars($myparam['data'],ENT_QUOTES);
		if ($myparam['parameter_name'] == 'limit')
			$limit = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'length')
			$length = (int) $myparam['data'];


		if ($myparam['parameter_name'] == 'showlike')
		{
			if ($myparam['data'] == 'true')
				$showlike = true;
			else
				$showlike = false;
		}




		if ($myparam['parameter_name'] == 'ezblocklayoutid')
			$ezblocklayoutid = (int) $myparam['data'];

	}



	loadLanguage('Stats');

	// Must be integers....
	if ($limit === null)
		$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
	else
		$limit = (int) $limit;

	if ($start === null)
		$start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
	else
		$start = (int) $start;

	if ($length === null)
		$length = isset($_GET['length']) ? (int) $_GET['length'] : 0;
	else
		$length = (int) $length;

	$limit = max(0, $limit);
	$start = max(0, $start);


	// Load the message icons - the usual suspects.
	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';
	$posts = array();
	if (($posts  = cache_get_data('ezportal_boardnews_' . $modSettings['totalMessages'] . '_' . $ezblocklayoutid, 60)) == null)
	{
		// Find the post ids.
		$request = $smcFunc['db_query']('', "
			SELECT ID_FIRST_MSG
			FROM {db_prefix}topics
			WHERE ID_BOARD IN($board)
			ORDER BY ID_FIRST_MSG DESC
			LIMIT $start, $limit");

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$posts[] = $row['ID_FIRST_MSG'];
		$smcFunc['db_free_result']($request);
		cache_put_data('ezportal_boardnews_' . $modSettings['totalMessages'] . '_' . $ezblocklayoutid, $posts, 60);
	}

	if (empty($posts))
		return array();

	// Find the posts.
	$request = $smcFunc['db_query']('', "
		SELECT
			m.icon, m.subject, m.body, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time,
			t.num_replies, t.ID_TOPIC, m.ID_MEMBER, m.smileys_enabled, m.ID_MSG, t.locked, mg.online_color
		FROM ({db_prefix}topics AS t, {db_prefix}messages AS m)
			LEFT JOIN {db_prefix}members AS mem ON (mem.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(mem.ID_GROUP = 0, mem.ID_POST_GROUP, mem.ID_GROUP))
		WHERE t.ID_FIRST_MSG IN (" . implode(', ', $posts) . ")
			AND m.ID_MSG = t.ID_FIRST_MSG
		ORDER BY t.ID_FIRST_MSG DESC
		LIMIT " . count($posts));
	$return = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// If we want to limit the length of the post.
		if (!empty($length) && $smcFunc['strlen']($row['body']) > $length)
		{
			$row['body'] = $smcFunc['substr']($row['body'], 0, $length);

			// The first space or line break. (<br />, etc.)
			$cutoff = max(strrpos($row['body'], ' '), strrpos($row['body'], '<'));

			if ($cutoff !== false)
				$row['body'] = $smcFunc['substr']($row['body'], 0, $cutoff);
			$row['body'] .= '...';
		}

		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['ID_MSG']);

		// Check that this message icon is there...
		if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.'  .($context['ezportal21beta'] == true ? 'png' : 'gif')) ? 'images_url' : 'default_images_url';

		censorText($row['subject']);
		censorText($row['body']);



		$return[] = array(
			'id' => $row['ID_TOPIC'],
			'message_id' => $row['ID_MSG'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.'  .($context['ezportal21beta'] == true ? 'png' : 'gif') . '" align="middle" alt="' . $row['icon'] . '" border="0" />',
			'subject' => $row['subject'],
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'body' => $row['body'],
			'href' => $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['ID_TOPIC'] . '.0">' . $row['num_replies'] . ' ' . ($row['num_replies'] == 1 ? $txt['ezp_built_news_1'] : $txt['ezp_built_news_2']) . '</a>',
			'replies' => $row['num_replies'],
			'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'],
			'comment_link' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'] . '">' . $txt['ezp_built_news_3'] . '</a>',
			'new_comment' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['ID_TOPIC'] . '.' . $row['num_replies'] . '">' . $txt['ezp_built_news_3'] . '</a>',
			'poster' => array(
				'id' => $row['ID_MEMBER'],
				'name' => $row['poster_name'],
				'href' => !empty($row['ID_MEMBER']) ? $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] : '',
				'link' => !empty($row['ID_MEMBER']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['ID_MEMBER'] . '" style="color: ' . $row['online_color'] . ';">' . $row['poster_name'] . '</a>' : $row['poster_name']
			),
			'locked' => !empty($row['locked']),
			'is_last' => false
		);
	}
	$smcFunc['db_free_result']($request);

	if (empty($return))
		return;

	$return[count($return) - 1]['is_last'] = true;




	foreach ($return as $news)
	{
		echo '
			<div>
				<a href="', $news['href'], '">', $news['icon'], '</a> <a href="', $news['href'], '"><b>', $news['subject'], '</b></a>
				<div class="smaller">', $news['time'], ' ', $txt['ezp_built_by'], ' ', $news['poster']['link'], '</div>

				<div class="post" style="padding: 2ex 0;">', $news['body'], '</div>

				', $news['link'], $news['locked'] ? '' : ' | ' . $news['comment_link'];

				if ($showlike == true || $showgoogleplus == true)
					echo '<br />';

				if ($showlike == true)
					echo '<iframe src="https://www.facebook.com/plugins/like.php?href=' . urlencode($news['href']) . '&amp;layout=button_count&amp;show_faces=true&amp;width=450&amp;action=like&amp;font&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:21px;" allowTransparency="true" title="' . $txt['ezp_txt_fb_like']  . '"></iframe>
';



				echo  '
			</div>';

		if (!$news['is_last'])
			echo '
			<hr style="margin: 2ex 0;" width="100%" />';
	}

}

function EzBlockShoutBoxBlock($parameters = array(), $numberofShouts = 10, $iframe = false)
{
	global $txt, $smcFunc, $ezpSettings, $scripturl, $context, $user_info, $settings, $modSettings;

	// Check if shoutbox is enabled
	if ($ezpSettings['ezp_shoutbox_enable'] == 0)
	{
		echo $txt['ezp_shoutbox_error_disabled'];
		return;

	}

	$context['save_embed_disable'] = 1;

	// Pass all the parematers
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'numberofShouts')
			$numberofShouts = (int) $myparam['data'];
	}


	$adminShoutBox = allowedTo('admin_forum');


	$useIframe = empty($ezpSettings['ezp_shoutbox_refreshseconds']) ? false : true;

	if ($context['browser']['is_ie6'] == true || $context['browser']['is_gecko'] == true || strpos($_SERVER['HTTP_USER_AGENT'], 'WebKit') !== false)
	{
		$iframe = false;
		$useIframe = false;
	}


	// Show latest shouts
	if ($iframe == false)
	{
	echo '<table style="width: 100%;table-layout: fixed;">
	<tr>
	<td>
	';
	}

	if ($useIframe == true && $iframe == false)
		echo '<iframe src="' . $scripturl . '?action=ezportal;sa=shoutframe;num=' . $numberofShouts	. '" frameborder="0" width="100%" height="100%">';


		if ($useIframe == true && $iframe == false)
	{

	}
	else
	{
		echo '<div style="overflow: auto;">';

		$ezBlockshout =array();

		if (($ezBlockshout = cache_get_data('ezBlockshout', 60)) == null)
		{

			$dbresult = $smcFunc['db_query']('', "
			SELECT
				s.shout, s.date, s.id_shout, s.id_member, m.real_name,mg.online_color, mg.ID_GROUP
			FROM {db_prefix}ezp_shoutbox AS s
			LEFT JOIN {db_prefix}members AS m ON (s.ID_MEMBER = m.ID_MEMBER)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(m.ID_GROUP = 0, m.ID_POST_GROUP, m.ID_GROUP))
			ORDER BY s.id_shout DESC LIMIT $numberofShouts");
			while ($row = $smcFunc['db_fetch_assoc']($dbresult))
			{
				$ezBlockshout[] = $row;
			}
			$smcFunc['db_free_result']($dbresult);


			cache_put_data('ezBlockshout', $ezBlockshout, 60);

		}


		if (!empty($ezBlockshout))
		foreach($ezBlockshout as $row)
		{
			// Censor the shout
			censorText($row['shout']);

			if ($ezpSettings['ezp_shoutbox_showdate'])
				echo timeformat($row['date']) . ' ';

			echo '<a href="',$scripturl,'?action=profile;u=',$row['id_member'],'" style="color: ' . $row['online_color'] . ';">',$row['real_name'],'</a>';

			if (empty($ezpSettings['ezp_shoutbox_hidesays']))
				echo $txt['ezp_shoutbox_says'];
			else
				echo ": ";

			echo parse_bbc($row['shout']);

			if ($adminShoutBox && empty($ezpSettings['ezp_shoutbox_hidedelete']))
				echo '<br /><a href="',$scripturl,'?action=ezportal;sa=removeshout;shout=',$row['id_shout'],'" style="color: #FF0000">[X]</a>';

			echo '<br /><hr>';

		}



	}

	$context['save_embed_disable'] = 0;

	if ($useIframe == true && $iframe == false)
	{
		echo '</iframe>';

	}
	if ($iframe == true)
	{
		echo '</div></body></html>';
		return;
	}

    $context['save_embed_disable'] = 1;

	$_SESSION['shoutbox_url'] = $_SERVER['REQUEST_URI'];

	if ($ezpSettings['ezp_shoutbox_archivehistory'] == 1)
		echo '<span class="smalltext"><a href="',$scripturl,'?action=ezportal;sa=shouthistory">',$txt['ezp_txt_viewshouthistory'],'</a></span><br />';

	// Show the shoutbox form
	if (!$user_info['is_guest'])
	{
	echo '<hr />
		<form action="', $scripturl, '?action=ezportal;sa=addshout" method="post"  id="ezpshoutform" accept-charset="', $context['character_set'], '">';

		if ($ezpSettings['ezp_shoutbox_showbbc'])
		{
			$context['bbc_tags2'] = array();
			$context['bbc_tags2'][] = array(
				'bold' => array('code' => 'b', 'before' => '[b]', 'after' => '[/b]', 'description' => $txt['ezp_bbc_bold'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/'),
				'italicize' => array('code' => 'i', 'before' => '[i]', 'after' => '[/i]', 'description' => $txt['ezp_bbc_italic'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/'),
				'underline' => array('code' => 'u', 'before' => '[u]', 'after' => '[/u]', 'description' => $txt['ezp_bbc_underline'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/'),
				'strike' => array('code' => 's', 'before' => '[s]', 'after' => '[/s]', 'description' => $txt['ezp_bbc_strike'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/'),
				array(),
				'img' => array('code' => 'img', 'before' => '[img]', 'after' => '[/img]', 'description' => $txt['ezp_bbc_img'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/'),
				'url' => array('code' => 'url', 'before' => '[url]', 'after' => '[/url]', 'description' => $txt['ezp_bbc_link'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/'),
				'move' => array('code' => 'move', 'before' => '[move]', 'after' => '[/move]', 'description' => $txt['ezp_bbc_move'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/'),
			);
			$context['bbc_tags2'][] = array(

				'left' => array('code' => 'left', 'before' => '[left]', 'after' => '[/left]', 'description' => $txt['ezp_bbc_left'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/' ),
				'center' => array('code' => 'center', 'before' => '[center]', 'after' => '[/center]', 'description' => $txt['ezp_bbc_center'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/'),
				'right' => array('code' => 'right', 'before' => '[right]', 'after' => '[/right]', 'description' => $txt['ezp_bbc_right'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/'),

				array(),
				'size' => array('code' => 'size', 'before' => '[size=10pt]', 'after' => '[/size]', 'description' => $txt['ezp_bbc_size'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/'),
				'face' => array('code' => 'font', 'before' => '[font=Verdana]', 'after' => '[/font]', 'description' => $txt['ezp_bbc_face'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/' ),
				array(),
				'hr' => array('code' => 'hr', 'before' => '[hr]', 'description' => $txt['ezp_bbc_hr'], 'imagebase' =>  $ezpSettings['ezp_url'] . 'icons/' ),

			);

			foreach ($context['bbc_tags2'] as $i => $row)
			{
				foreach ($row as $image => $tag)
				{
					// Is this tag disabled?
					if (!empty($tag['code']) && !empty($context['disabled_tags'][$tag['code']]))
						continue;

					if (isset($tag['before']))
                    {
                        if (isset($tag['imagebase']))
						  echo '<a href="javascript:' . (isset($tag['after']) ? 'surround' : 'replace') . 'Text(\'' . $tag['before'] . '\'' . (isset($tag['after']) ? ', \'' . $tag['after'] . '\'' : '') . ', document.forms.ezpshoutform.shout);"><img src="' . $tag['imagebase']  . $image . '.gif" align="bottom" width="23" height="22" alt="' . $tag['description'] . '" border="0" /></a>';
                        else
                            echo '<a href="javascript:' . (isset($tag['after']) ? 'surround' : 'replace') . 'Text(\'' . $tag['before'] . '\'' . (isset($tag['after']) ? ', \'' . $tag['after'] . '\'' : '') . ', document.forms.ezpshoutform.shout);"><img src="' . $settings['images_url'] . '/bbc/' . $image . '.gif" align="bottom" width="23" height="22" alt="' . $tag['description'] . '" border="0" /></a>';

				    }

                }

				if ($i != count($context['bbc_tags2']) - 1)
					echo '<br />';
			}
		}


	if ($ezpSettings['ezp_shoutbox_showsmilies'])
	{
		echo '<br />';

		if (function_exists("set_tld_regex"))
        {

            $context['shout_smileys'] = array(
                'smileys' => array(
                    array('code' => ':)', 'filename' => 'smiley.png'),
                    array('code' => ';)', 'filename' => 'wink.png'),
                    array('code' => ':D', 'filename' => 'cheesy.png'),
                    array('code' => ';D', 'filename' => 'grin.png'),
                    array('code' => '>:(', 'filename' => 'angry.png'),
                    array('code' => ':(', 'filename' => 'sad.png'),
                    array('code' => ':o', 'filename' => 'shocked.png'),
                    array('code' => '8)', 'filename' => 'cool.png'),
                    array('code' => '???', 'filename' => 'huh.png'),
                    array('code' => '::)', 'filename' => 'rolleyes.png'),
                    array('code' => ':P', 'filename' => 'tongue.png'),
                    array('code' => ':-[', 'filename' => 'embarrassed.png'),
                    array('code' => ':-X', 'filename' => 'lipsrsealed.png'),
                    array('code' => ':-\\', 'filename' => 'undecided.png'),
                    array('code' => ':-*', 'filename' => 'kiss.png'),
                    array('code' => ':\'(', 'filename' => 'cry.png')
                ),

            );
        }
		else
        {

            $context['shout_smileys'] = array(
                'smileys' => array(
                    array('code' => ':)', 'filename' => 'smiley.gif'),
                    array('code' => ';)', 'filename' => 'wink.gif'),
                    array('code' => ':D', 'filename' => 'cheesy.gif'),
                    array('code' => ';D', 'filename' => 'grin.gif'),
                    array('code' => '>:(', 'filename' => 'angry.gif'),
                    array('code' => ':(', 'filename' => 'sad.gif'),
                    array('code' => ':o', 'filename' => 'shocked.gif'),
                    array('code' => '8)', 'filename' => 'cool.gif'),
                    array('code' => '???', 'filename' => 'huh.gif'),
                    array('code' => '::)', 'filename' => 'rolleyes.gif'),
                    array('code' => ':P', 'filename' => 'tongue.gif'),
                    array('code' => ':-[', 'filename' => 'embarrassed.gif'),
                    array('code' => ':-X', 'filename' => 'lipsrsealed.gif'),
                    array('code' => ':-\\', 'filename' => 'undecided.gif'),
                    array('code' => ':-*', 'filename' => 'kiss.gif'),
                    array('code' => ':\'(', 'filename' => 'cry.gif')
                ),

            );
        }




		$settings['smileys_url'] = $modSettings['smileys_url'] . '/' . $user_info['smiley_set'];
	// Show each row of smileys ;).
		foreach ($context['shout_smileys'] as $smiley_row)
		{

			foreach (@$smiley_row as $smiley)
			{
				echo '
				<a href="javascript:replaceText(\' ', addslashes($smiley['code']), '\', document.forms.ezpshoutform.shout);"><img src="', $settings['smileys_url'], '/', $smiley['filename'], '" align="bottom" alt="" title="" border="0" /></a>';
			}

		}

	}

	echo '
			<textarea name="shout"  rows="3" cols="30" style="width: 96%"></textarea>
			<br />
			<input type="submit" value="', $txt['ezp_shoutbox_add_shout'], '" />

		</form>';
	}

	echo '</div>
	</td>
	</tr>
	</table>';

	$context['save_embed_disable'] = 0;


}


function EzBlockTagCloudBlock($parameters = array())
{
	global $txt, $smcFunc, $modSettings, $scripturl, $context, $user_info;

	// Check if Tag cloud is enabled

	if (!isset($modSettings['smftags_set_cloud_tags_to_show']))
	{
		echo $txt['ezp_smftags_block_noinstall'];
		return;

	}

	echo '<div align="center">';

	// Pass all the parameters
	// Show the latest Tags
		// Tag cloud from http://www.prism-perfect.net/archive/php-tag-cloud-tutorial/
		$result = $smcFunc['db_query']('', "
		SELECT
			t.tag AS tag, l.ID_TAG, COUNT(l.ID_TAG) AS quantity
		 FROM {db_prefix}tags as t, {db_prefix}tags_log as l WHERE t.ID_TAG = l.ID_TAG
		  GROUP BY l.ID_TAG,t.tag AS tag 
		  ORDER BY l.ID DESC LIMIT " .  $modSettings['smftags_set_cloud_tags_to_show']);

		// here we loop through the results and put them into a simple array:
		// $tag['thing1'] = 12;
		// $tag['thing2'] = 25;
		// etc. so we can use all the nifty array functions
		// to calculate the font-size of each tag
		$tags = array();

		$tags2 = array();

		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
		    $tags[$row['tag']] = $row['quantity'];
		    $tags2[$row['tag']] = $row['ID_TAG'];
		}

		if (count($tags2) > 0)
		{
			// change these font sizes if you will
			$max_size = $modSettings['smftags_set_cloud_max_font_size_precent']; // max font size in %
			$min_size = $modSettings['smftags_set_cloud_min_font_size_precent']; // min font size in %

			// get the largest and smallest array values
			$max_qty = max(array_values($tags));
			$min_qty = min(array_values($tags));

			// find the range of values
			$spread = $max_qty - $min_qty;
			if (0 == $spread)
			 { // we don't want to divide by zero
			    $spread = 1;
			}

			// determine the font-size increment
			// this is the increase per tag quantity (times used)
			$step = ($max_size - $min_size)/($spread);

			// loop through our tag array
			$context['poptags'] = '';
			$row_count = 0;
			foreach ($tags as $key => $value)
			{
				$row_count++;
			    // calculate CSS font-size
			    // find the $value in excess of $min_qty
			    // multiply by the font-size increment ($size)
			    // and add the $min_size set above
			    $size = $min_size + (($value - $min_qty) * $step);
			    // uncomment if you want sizes in whole %:
			    // $size = ceil($size);

			    // you'll need to put the link destination in place of the #
			    // (assuming your tag links to some sort of details page)
			    $context['poptags'] .= '<a href="' . $scripturl . '?action=tags;tagid=' . $tags2[$key] . '" style="font-size: '.$size.'%"';
			    // perhaps adjust this title attribute for the things that are tagged
			   $context['poptags'] .= ' title="'.$value.' things tagged with '.$key.'"';
			   $context['poptags'] .= '>'.$key.'</a> ';
			   if ($row_count > ($modSettings['smftags_set_cloud_tags_per_row']-1))
			   {
			   	$context['poptags'] .= '<br />';
			   	$row_count =0;
			   }
			    // notice the space at the end of the link
			}
		}

		if (isset($context['poptags']))
  			echo $context['poptags'];


	echo '</div>';
}

function EzGetStarsByPrecent($percent)
{
	global $settings, $txt, $context;

    if ($context['ezportal21beta'] == false)
    {
    	if ($percent == 0)
    		return $txt['ezp_no_rating'];
    	else if ($percent <= 20)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 1);
    	else if ($percent <= 40)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 2);
    	else if ($percent <= 60)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 3);
    	else if ($percent <= 80)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 4);
    	else if ($percent <= 100)
    		return str_repeat('<img src="' . $settings['images_url'] . '/star.gif" alt="*" border="0" />', 5);
    }
    else
    {
    	if ($percent == 0)
    		return $txt['ezp_no_rating'];
    	else if ($percent <= 20)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 1);
    	else if ($percent <= 40)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 2);
    	else if ($percent <= 60)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 3);
    	else if ($percent <= 80)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 4);
    	else if ($percent <= 100)
    		return str_repeat('<img src="' . $settings['images_url'] . '/membericons/icon.png" alt="*" border="0" />', 5);
    }


}

function Ezformatprice($price,$currency)
{
		if ($currency == 'USD')
			return  '$' .  number_format($price, 2, '.', '');
		else if ($currency == 'GBP')
			return  '&#163;' .  number_format($price, 2, '.', '');
		elseif ($currency == 'CAD')
			return  '$' .  number_format($price, 2, '.', '');
		elseif ($currency == 'AUD')
			return  '$' .  number_format($price, 2, '.', '');
 		elseif ($currency == 'ZAR')
			return  'R ' .  number_format($price, 2, '-', '');
		else
			return $price . ' ' . $currency;

}

//--------------------------
// author: Louai Munajim
// website: www.elouai.com
//
// Note:
// Unix timestamp limitations
// Date range is from
// the year 1970 to 2038
//--------------------------
function ezblockclass_cattimeleft($year, $month, $day, $hour, $minute, $seconds)
{
	global $txt;
  // make a unix timestamp for the given date
  $the_countdown_date = mktime($hour, $minute, $seconds, $month, $day, $year);

  // get current unix timestamp
  $today = forum_time(false);


  $difference = $the_countdown_date - $today;
  if ($difference < 0) $difference = 0;

  $days_left = floor($difference/60/60/24);
  $hours_left = floor(($difference - $days_left*60*60*24)/60/60);
  $minutes_left = floor(($difference - $days_left*60*60*24 - $hours_left*60*60)/60);
  $seconds_left = floor(($difference - $days_left*60*60*24 - $hours_left*60*60 - $minutes_left*60));



  if ($days_left == 0 && $hours_left == 0 &&  $minutes_left < 5  && ($minutes_left != 0 && $seconds_left  !=0))
  {
  	echo '<font color="#FF0000">';
  }

  echo '<span class="smalltext">';

  if ($days_left > 0)
  {
  	echo $days_left . 'd ' . $hours_left  . 'h ' . $minutes_left . 'm';
  }
  else
  {
  	if ($hours_left > 0)
  		echo $hours_left  . 'h ' . $minutes_left . 'm';
  	else
  	{

  		if ($minutes_left > 0)
  			echo $minutes_left . 'm ' . $seconds_left . 's';
  		else
  		{
  			if ($seconds_left > 0)
  				echo  $seconds_left . 's';

  		}


  	}

  }


  echo '</span>';

  if ($days_left == 0 && $hours_left == 0 &&  $minutes_left < 5  && ($minutes_left != 0 && $seconds_left  !=0))
  {
  	echo '</font>';
  }

}

function EzBlockRecentMembersBlock($parameters = array(), $numShow = 10, $showAvatar = false,  $showColor = false, $format = 'vertical', $startHtml = '', $endHtml = '')
{
	global $smcFunc, $scripturl, $memberContext, $txt;

	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'numShow')
			$numShow = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'format')
			$format = $myparam['data'];

		if ($myparam['parameter_name'] == 'showcolor')
		{
			if ($myparam['data'] == 'true')
				$showColor = true;
			else
				$showColor = false;
		}

		if ($myparam['parameter_name'] == 'showavatar')
		{
			if ($myparam['data'] == 'true')
				$showAvatar = true;
			else
				$showAvatar = false;
		}

	}

	// Get list of most recent activated members
	$dbresult = $smcFunc['db_query']('', "
		SELECT
			m.ID_MEMBER, m.real_name, mg.online_color, mg.ID_GROUP
		FROM {db_prefix}members AS m
		LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(m.ID_GROUP = 0, m.ID_POST_GROUP, m.ID_GROUP))
		WHERE m.is_activated = 1
		ORDER BY m.ID_MEMBER DESC LIMIT $numShow");
	echo '<table width="100%">';


	if ($format != 'vertical')
		echo '<tr>';

	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		if ($format == 'vertical')
			echo '<tr>';

			echo '<td>
					<table>
					<tr><td>';

					if ($showAvatar == true)
					{
						// Show avatar if it exists
						$memCommID = $row['ID_MEMBER'];
			            loadMemberData($memCommID);
						loadMemberContext($memCommID);


						if (!empty($memberContext[$memCommID]['avatar']['image']))
							echo '
								', $memberContext[$memCommID]['avatar']['image'], '';


						// Make two columns
						echo '</td><td>';
					}

					// Show member name
					if ($showColor == true)
					{
						echo '<a href="',$scripturl,'?action=profile;u=',$row['ID_MEMBER'],'"><font color="',$row['online_color'],'">',$row['real_name'],'</font></a>';
					}
					else
					{
						echo '<a href="',$scripturl,'?action=profile;u=',$row['ID_MEMBER'],'">',$row['real_name'],'</a>';
					}


			echo '</td></tr>
				</table>
			</td>';

		if ($format == 'vertical')
			echo '</tr>';
	}

	if ($format != 'vertical')
		echo '</tr>';

	echo '</table>';

	$smcFunc['db_free_result']($dbresult);

}

function EzBlockTopPosterBlock($parameters = array(), $numShow = 10, $showAvatar = false, $showColor = false, $format = 'vertical', $startHtml = '', $endHtml = '')
{
	global $smcFunc, $scripturl, $txt, $memberContext;


	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'numShow')
			$numShow = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'format')
			$format = $myparam['data'];

		if ($myparam['parameter_name'] == 'showcolor')
		{
			if ($myparam['data'] == 'true')
				$showColor = true;
			else
				$showColor = false;
		}

		if ($myparam['parameter_name'] == 'showavatar')
		{
			if ($myparam['data'] == 'true')
				$showAvatar = true;
			else
				$showAvatar = false;
		}

	}


	// Get top Posters
	$dbresult = $smcFunc['db_query']('', "
		SELECT
			m.ID_MEMBER, m.real_name, mg.online_color, mg.ID_GROUP, m.posts
		FROM {db_prefix}members AS m
		LEFT JOIN {db_prefix}membergroups AS mg ON (mg.ID_GROUP = IF(m.ID_GROUP = 0, m.ID_POST_GROUP, m.ID_GROUP))

		ORDER BY m.posts DESC LIMIT $numShow");
	echo '<table width="100%">';


	if ($format != 'vertical')
		echo '<tr>';

	while($row = $smcFunc['db_fetch_assoc']($dbresult))
	{
		if ($format == 'vertical')
			echo '<tr>';

			echo '<td>
					<table>
					<tr><td valign="top">';

					if ($showAvatar == true)
					{
						// Show avatar if it exists
						$memCommID = $row['ID_MEMBER'];
			            loadMemberData($memCommID);
						loadMemberContext($memCommID);


						if (!empty($memberContext[$memCommID]['avatar']['image']))
							echo '
								', $memberContext[$memCommID]['avatar']['image'], '';


						// Make two columns
						echo '</td><td>';
					}

					// Show member name
					if ($showColor == true)
					{
						echo '<a href="',$scripturl,'?action=profile;u=',$row['ID_MEMBER'],'"><font color="',$row['online_color'],'">',$row['real_name'],'</font></a>';
					}
					else
					{
						echo '<a href="',$scripturl,'?action=profile;u=',$row['ID_MEMBER'],'">',$row['real_name'],'</a>';
					}

					// Show Posts number
					echo '<br />', $txt['ezp_txt_posts'], $row['posts'];


			echo '</td></tr>
				</table>
			</td>';

		if ($format == 'vertical')
			echo '</tr>';
	}

	if ($format != 'vertical')
		echo '</tr>';

	echo '</table>';

	$smcFunc['db_free_result']($dbresult);

}

function EzBlockParseBBCBlock($parameters = array(), $bbcText = '', $startHtml = '', $endHtml = '')
{
	global $ezpSettings;


	// Parse the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'bbctext')
			$bbcText = $myparam['data'];


	}




	echo parse_bbc($bbcText);

}

function EzBlockRSSBlock($parameters = array(), $showBody = false, $numShow = 10, $feedurl = '', $feedData = '', $lastupdate = '',  $updatetime = 15, $format = 'vertical', $newwindow = 0, $reverseorder = 1, $encoding = 'ISO-8859-1', $startHtml = '', $endHtml = '')
{
	global $ezpSettings, $sourcedir;

	include_once($sourcedir . '/Subs-EzPortalRSS2.php');

	$ezblocklayoutid = 0;

	// Parse the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'numShow')
			$numShow = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'feedurl')
			$feedurl = $myparam['data'];

		if ($myparam['parameter_name'] == 'updatetime')
			$updatetime = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'ezblocklayoutid')
			$ezblocklayoutid = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'showBody')
		{
			if ($myparam['data'] == 'true')
				$showBody = true;
			else
				$showBody = false;

		}

		if ($myparam['parameter_name'] == 'rssdata')
			$feedData  =  $myparam['data'];

		if ($myparam['parameter_name'] == 'lastupdate')
			$lastupdate  = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'newwindow')
			$newwindow = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'reverseorder')
			$reverseorder = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'encoding')
			$encoding = $myparam['data'];

	}

	// Show Feed
	ezportal_ShowRSSFeed($ezblocklayoutid,$showBody,$numShow,$feedurl,$feedData, $lastupdate, $updatetime, $newwindow,$encoding,$reverseorder);


}

function EzBlockMenuBlock($parameters = array(), $format = 'vertical', $startHtml = '', $endHtml = '')
{
	global $ezpSettings, $smcFunc, $user_info;

	$ezblocklayoutid = 0;

	// Parse the parameters
	foreach($parameters as $myparam)
	{

		if ($myparam['parameter_name'] == 'ezblocklayoutid')
			$ezblocklayoutid = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'format')
			$format =  $myparam['data'];
	}
	
	$ezblockMenuCache = array();
	if (($ezblockMenuCache = cache_get_data('ezBlockMenu_' . $ezblocklayoutid, 90)) == null)
	{
		$dbresult = $smcFunc['db_query']('', "
			SELECT
				title, linkurl, permissions, newwindow
			FROM {db_prefix}ezp_menu
			WHERE enabled = 1 AND id_layout = $ezblocklayoutid
			ORDER BY id_order ASC");
		while($row = $smcFunc['db_fetch_assoc']($dbresult))
		{

			$ezblockMenuCache[] = $row;
		}
		$smcFunc['db_free_result']($dbresult);

		cache_put_data('ezBlockMenu_' . $ezblocklayoutid, $ezblockMenuCache, 90);

	}

	if (!empty($ezblockMenuCache))
	{
		echo '<div style="float: left; margin-left: -.9em; margin-top: -1.2em;"><ul>';
		

	foreach($ezblockMenuCache as $row)
	{
		// Display the items
			$target = '';
			$menuPermissions = explode(',',$row['permissions']);

			$canViewMenu = count(array_intersect($user_info['groups'], $menuPermissions)) == 0 ? false : true;
			if ($canViewMenu ==  false)
				continue;


			if ($row['newwindow'] == 1)
				$target = ' target="_blank"';

			echo '<li><a href="' . $row['linkurl'] . '" ' . $target . '>' . $row['title'] . '</a></li>';



	//	if ($format != 'vertical')
		//	echo '<br class="clear" />';

	}
	
		echo '</ul></div>';
	}
	echo '<br class="clear" />';
}

function EzBlockStatsBox($parameters = array(), $startHtml = '', $endHtml = '')
{
	global $txt, $smcFunc;
	global $context, $boarddir, $settings, $options, $scripturl, $user_info, $modSettings, $ezpSettings;



	$ezblocklayoutid = 0;

	// Parse the parameters
	foreach($parameters as $myparam)
	{

		if ($myparam['parameter_name'] == 'ezblocklayoutid')
			$ezblocklayoutid = (int) $myparam['data'];
	}

	// Html Header
	echo $startHtml;

	require_once($boarddir.'/SSI.php');

    $bullet = ' ';

    $membersImage = $settings['images_url'].'/icons/members.gif';
    $infoImage = $settings['images_url'].'/icons/info.gif';
    $onlineImage = $settings['images_url'].'/icons/online.gif';

    if ($context['ezportal21beta'] == true)
    {
        $membersImage = $ezpSettings['ezp_url'] . 'icons/members.gif';
        $infoImage = $ezpSettings['ezp_url'] . 'icons/info.gif';
        $onlineImage = $ezpSettings['ezp_url'] . 'icons/online.gif';
    }


	echo'
	<div style="font-family: verdana, arial, sans-serif;">';

	   // Members stats
          echo '
                 <img src="'. $membersImage . '" style="margin: 0;" align="bottom" alt="" />
                 <a href="'.$scripturl.'?action=mlist"><b>',$txt['ezp_stats_members'],'</b></a>
                 <br />'.$bullet,$txt['ezp_stats_total_members'],': ' , isset($modSettings['memberCount']) ? $modSettings['memberCount'] : $modSettings['totalMembers'] , '
                 <br />'.$bullet,$txt['ezp_stats_latest'],': <a href="', $scripturl, '?action=profile;u=', $modSettings['latestMember'], '"><b>', $modSettings['latestRealName'], '</b></a>';


			$today = strtotime("24 hours ago");
			date('j') == 1 ? $thismonth = $today : $thismonth = strtotime(date('F') . ' 1');
			date('l') == 'Sunday' ? $thisweek = $today : $thisweek = strtotime('last Sunday');
			date('M') == 'January' ? $thisyear = $thismonth : $thisyear = strtotime('January 1');
			
			
	if (($row = cache_get_data('ezpstats_block_' . $ezblocklayoutid, 300)) == null)
	{	
			$query = $smcFunc['db_query']('', "
			SELECT
					COUNT(date_registered > $thisyear OR NULL) as year,
					COUNT(date_registered > $thismonth OR NULL) as month,
					COUNT(date_registered > $thisweek OR NULL) as week,
					COUNT(date_registered > $today OR NULL) as today
			FROM {db_prefix}members
			WHERE is_activated = 1");
			$row = $smcFunc['db_fetch_assoc']($query);
			$smcFunc['db_free_result']($query);
			
		// Check if cache is enabled
		if (!empty($modSettings['cache_enable']))
			cache_put_data('ezpstats_block_' . $ezblocklayoutid, $row, 300);

	}		
			

			echo '<br />',$bullet,$txt['ezp_stats_new_this_month'], $row['month'],'<br />';
			echo $bullet,$txt['ezp_stats_new_this_week'], $row['week'],'<br />';
			echo $bullet,$txt['ezp_stats_new_today'], $row['today'];

	   // more stats
            echo '
                 <hr /><img src="' . $infoImage. '" style="margin: 0;" align="bottom" alt="" />
                 <a href="'.$scripturl.'?action=stats"><b>',$txt['ezp_stats_stats'],'</b></a>
                 <br />'.$bullet,$txt['ezp_stats_total_posts'],': '.$modSettings['totalMessages']. '
                                  <br />'.$bullet,$txt['ezp_stats_total_topics'],': '.$modSettings['totalTopics']. '
                                  <br />'.$bullet,$txt['ezp_stats_most_online_today'],': '.$modSettings['mostOnlineToday'].'
                                  <br />'.$bullet,$txt['ezp_stats_most_online_ever'],': '.$modSettings['mostOnline'].'<br />
					  ('.timeformat($modSettings['mostDate']).')
				  ';

	   // Add online users
           echo '<hr /><img src="'. $onlineImage . '" style="margin: 0;" align="bottom" alt="" />
                 <a href="'.$scripturl.'?action=who"><b>',$txt['ezp_stats_users_online'],'</b></a><br />';

            $online = ssi_whosOnline('array');
           echo $bullet,$txt['ezp_stats_members'],': '.$online['num_users'];
           echo '<br />'.$bullet,$txt['ezp_stats_users_guests'],': '.$online['guests'];
           echo '<br />'.$bullet,$txt['ezp_stats_users_total'],': '.$online['total_users'].'<hr />
			<div style="width: 100%; ' , $online['num_users']> 14 ? 'height: 23ex;overflow: auto;' : '' ,'">';

      foreach($online['users'] as $user)
      {
		echo ' ' , $user['hidden'] ? '<i>' . $user['link'] . '</i>' : $user['link'];
		echo '<br />';
      }
        echo '</div>';

    echo '</div>';

	// Html Footer
	echo $endHtml;

}

function EzBlockAdSellerPro($parameters = array(), $locationID = 0, $startHtml = '', $endHtml = '')
{
	global $txt, $modSettings;

	// Parse the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'locationid')
			$locationID = (int) $myparam['data'];

	}

	if (empty($locationID))
		return;


	if (!isset($modSettings['seller_show_advetise']))
	{
		echo $txt['ezp_adsellerpro_block_noinstall'];
		return;

	}


	global $sourcedir;
	include_once $sourcedir . "/adseller2.php";

	$adSellerAdData =  ShowAdLocation($locationID);

	// Check if any ads where found
	if ($adSellerAdData != false)
	{
		// Display the advertising code
		echo $adSellerAdData;
	}

}

function EzBlockTopBoards($parameters = array(), $startHtml = '', $endHtml = '')
{
	global $txt, $boarddir;

	// Html Header
	echo $startHtml;

	require_once($boarddir.'/SSI.php');

	ssi_topBoards();

	echo $endHtml;

}


function EzBlockBirthDaysBlock($parameters = array(), $startHtml = '', $endHtml = '')
{
	global $txt, $boarddir, $context, $scripturl, $modSettings, $user_info;

	// Html Header
	echo $startHtml;

	if (empty($user_info['time_offset']))
		$user_info['time_offset'] = 0;

	$eventOptions = array(
		'include_birthdays' => true,
		'num_days_shown' => empty($modSettings['cal_days_for_index']) || $modSettings['cal_days_for_index'] < 1 ? 1 : $modSettings['cal_days_for_index'],
	);

	$return = cache_quick_get('calendar_index_offset_' . ($user_info['time_offset'] + $modSettings['time_offset']), 'Subs-Calendar.php', 'cache_getRecentEvents', array($eventOptions));


	foreach ($return['calendar_birthdays'] as $member)
		echo '
			<a href="', $scripturl, '?action=profile;u=', $member['id'], '">' . $member['name'] . (isset($member['age']) ? ' (' . $member['age'] . ')' : '') . '</a>' . (!$member['is_last'] ? ', ' : '');


	echo $endHtml;
}


function EzBlockTopTopicsBlock($parameters = array(), $type  = 'replies', $numTopics = 10, $startHtml = '', $endHtml = '')
{
	global $txt, $boarddir;

	$numTopics = (int) $numTopics;

	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'type')
			$type =  $myparam['data'];
		if ($myparam['parameter_name'] == 'numTopics')
			$numTopics = (int) $myparam['data'];

	}


	// Html Header
	echo $startHtml;

	require_once($boarddir.'/SSI.php');

	if ($type == 'replies')
		ssi_topTopicsReplies($numTopics);

	if ($type == 'views')
		ssi_topTopicsViews($numTopics);

	echo $endHtml;
}

function EzBlockTwitterTweets($parameters = array(), $numTweets = 5, $startHtml = '', $endHtml = '')
{
	global $txt, $boardurl;

	$numTweets = (int) $numTweets;
	$twitterusername = '';

	// Pass all the parematers
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'twitterusername')
			$twitterusername =  $myparam['data'];

		if ($myparam['parameter_name'] == 'numTweets')
			$numTweets = (int) $myparam['data'];

	}


	echo '
	<a class="twitter-timeline" href="https://twitter.com/' . $twitterusername . '"  data-tweet-limit="'  . $numTweets .'">Tweets by ' . $twitterusername . '</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

';
}

function EzBlockFacebookComments($parameters = array(), $startHtml = '', $endHtml = '')
{
	global $boardurl;

	$numPosts = 5;
	$applicationid = '';
	$commentwidth = 500;

	// Pass all the parematers
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'applicationid')
			$applicationid =  $myparam['data'];

		if ($myparam['parameter_name'] == 'numPosts')
			$numPosts = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'commentwidth')
			$commentwidth = (int) $myparam['data'];
	}


	echo '<div id="fb-root"></div><script src="https://connect.facebook.net/en_US/all.js#appId=' . $applicationid . '&amp;xfbml=1"></script><fb:comments href="' . $boardurl . '" num_posts="' . $numPosts . '" width="' . $commentwidth . '"></fb:comments>
';
}


function EzBlockCalendar($parameters = array(), $showBirthdays = 1, $showEvents = 1, $showHolidays = 1, $showPrevNext = 1, $startHtml = '', $endHtml = '')
{
	global $txt, $sourcedir, $scripturl, $options, $smcFunc, $context;

	$size = 'small';
	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'showBirthdays')
			$showBirthdays = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'showEvents')
			$showEvents = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'showHolidays')
			$showHolidays = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'showPrevNext')
			$showPrevNext = (int) $myparam['data'];
		if ($myparam['parameter_name'] == 'size')
			$size = htmlspecialchars($myparam['data'],ENT_QUOTES);

	}


	// Html Header
	echo $startHtml;


	require_once($sourcedir . '/Subs-Calendar.php');

	$today = getTodayInfo();

	$curPage = array(
		'day' => $today['day'],
		'month' => $today['month'],
		'year' => $today['year']
	);


	$calendar_data['current_day'] = $curPage['day'];
	$calendar_data['current_month'] = $curPage['month'];
	$calendar_data['current_year'] = $curPage['year'];


	$calendarOptions = array(
		'start_day' => !empty($options['calendar_start_day']) ? $options['calendar_start_day'] : 0,
		'show_week_num' => false,
		'show_events' => $showEvents,
		'show_birthdays' => $showBirthdays,
		'show_holidays' => $showHolidays,
		'short_day_titles' => false,
		'show_next_prev' => $showPrevNext,
		'size' => $size,

	);

	if ($context['ezportal21beta'] == false)
	{
		$calendar_data = getCalendarGrid($curPage['month'], $curPage['year'], $calendarOptions);
	}
	else
	{

		$month = date("n");
		$day = date("j");
		$year = date("Y");

		$start_object = checkdate($month, $day, $year) === true ? date_create(implode('-', array($year, $month, $day))) : date_create(implode('-', array($today['year'], $today['month'], $today['day'])));
		$calendar_data = getCalendarGrid(date_format($start_object, 'Y-m-d'), $calendarOptions);
	}

	if (!isset($calendar_data['size']))
		 $calendar_data['size'] = $size;

	if (!isset($calendar_data['current_month']))
	{
		$calendar_data['current_day'] = $curPage['day'];
		$calendar_data['current_month'] = $curPage['month'];
		$calendar_data['current_year'] = $curPage['year'];
	}

	$colspan = !empty($calendar_data['show_week_links']) ? 8 : 7;

	if (empty($calendar_data['disable_title']))
	{
		echo '
			<span class="catbg centertext" style="font-size: ', $calendar_data['size'] == 'large' ? 'large' : 'small', ';">';

		if (empty($calendar_data['previous_calendar']['disabled']) && $calendar_data['show_next_prev'])
			echo '
					<span class="floatleft"><a href="', $calendar_data['previous_calendar']['href'], '">&#171;</a></span>';

		if (empty($calendar_data['next_calendar']['disabled']) && $calendar_data['show_next_prev'])
			echo '
					<span class="floatright"><a href="', $calendar_data['next_calendar']['href'], '">&#187;</a></span>';

		if ($calendar_data['show_next_prev'])
			echo '
					', $txt['months_titles'][$calendar_data['current_month']], ' ', $calendar_data['current_year'];
		else
			echo '
					<a href="', $scripturl, '?action=calendar;year=', $calendar_data['current_year'], ';month=', $calendar_data['current_month'], '">', $txt['months_titles'][$calendar_data['current_month']], ' ', $calendar_data['current_year'], '</a>';

		echo '
				</span>';
	}

	echo '
				<table cellspacing="1" class="calendar_table" style="overflow: hidden;">';

	if (empty($calendar_data['disable_day_titles']))
	{
		echo '
					<tr class="titlebg2">';

		if (!empty($calendar_data['show_week_links']))
			echo '
						<th>&nbsp;</th>';

		foreach ($calendar_data['week_days'] as $day)
		{

			echo '
						<th class="days" scope="col" ', $calendar_data['size'] == 'small' ? 'style="font-size: x-small;"' : '', '>', !empty($calendar_data['short_day_titles']) ? ($smcFunc['substr']($txt['days'][$day], 0, 1)) : (empty($txt['days'][$day]) ? $txt['days'][0] : $txt['days'][$day]), '</th>';
		}
		echo '
					</tr>';
	}

	foreach ($calendar_data['weeks'] as $week)
	{
		echo '
					<tr>';

		if (!empty($calendar_data['show_week_links']))
			echo '
						<td class="windowbg2 weeks">
							<a href="', $scripturl, '?action=calendar;viewweek;year=', $calendar_data['current_year'], ';month=', $calendar_data['current_month'], ';day=', $week['days'][0]['day'], '">&#187;</a>
						</td>';

		foreach ($week['days'] as $day)
		{
			echo '
						<td style="height: ', $calendar_data['size'] == 'small' ? '20' : '100', 'px; padding: 2px;', $calendar_data['size'] == 'small' ? 'font-size: x-small;' : '', '" class="', $day['is_today'] ? 'calendar_today' : 'windowbg', ' days">';

			if (!empty($day['day']))
			{

				if (!empty($modSettings['cal_daysaslink']) && $context['can_post'])
					echo '
							<a href="', $scripturl, '?action=calendar;sa=post;month=', $calendar_data['current_month'], ';year=', $calendar_data['current_year'], ';day=', $day['day'], ';', $context['session_var'], '=', $context['session_id'], '">', $day['day'], '</a>';
				else
					echo '
							', $day['day'];

				if ($day['is_first_day'] && $calendar_data['size'] != 'small')
					echo '<span class="smalltext"> - <a href="', $scripturl, '?action=calendar;viewweek;year=', $calendar_data['current_year'], ';month=', $calendar_data['current_month'], ';day=', $day['day'], '">', $txt['calendar_week'], ' ', $week['number'], '</a></span>';


				if (!empty($day['holidays']))
					echo '
							<div class="smalltext holiday">', $txt['calendar_prompt'], ' ', implode(', ', $day['holidays']), '</div>';


				if (!empty($day['birthdays']))
				{
					echo '
							<div class="smalltext">
								<span class="birthday">', $txt['birthdays'], '</span>';

					$use_js_hide = empty($context['show_all_birthdays']) && count($day['birthdays']) > 15;
					$count = 0;
					foreach ($day['birthdays'] as $member)
					{
						echo '
									<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>', $member['is_last'] || ($count == 10 && $use_js_hide)? '' : ', ';

						if ($count == 10 && $use_js_hide)
							echo '<span class="hidelink" id="bdhidelink_', $day['day'], '">...<br /><a href="', $scripturl, '?action=calendar;month=', $calendar_data['current_month'], ';year=', $calendar_data['current_year'], ';showbd" onclick="document.getElementById(\'bdhide_', $day['day'], '\').style.display = \'\'; document.getElementById(\'bdhidelink_', $day['day'], '\').style.display = \'none\'; return false;">(', sprintf($txt['calendar_click_all'], count($day['birthdays'])), ')</a></span><span id="bdhide_', $day['day'], '" style="display: none;">, ';

						$count++;
					}
					if ($use_js_hide)
						echo '
								</span>';

					echo '
							</div>';
				}

				if (!empty($day['events']))
				{
					echo '
							<div class="smalltext">
								<span class="event">', $txt['events'], '</span>';

					foreach ($day['events'] as $event)
					{

						echo '
								', $event['link'], $event['is_last'] ? '' : ', ';
					}

					echo '
							</div>';
				}
			}

			echo '
						</td>';
		}

		echo '
					</tr>';
	}

	echo '
				</table>';


	echo $endHtml;
}


function EzBlockBlueSkyFeed($parameters = array(),$startHtml = '', $endHtml = '')
{

	$numposts = (int) 10;
	$blueskyusername = '';
	$darkmode  = 'false';
	$loadmore  = 'false';
	$linkimage  = 'false';
	$disablestyles = 'false';

	// Pass all the parameters
	foreach($parameters as $myparam)
	{
		if ($myparam['parameter_name'] == 'blueskyusername')
			$blueskyusername =  $myparam['data'];

		if ($myparam['parameter_name'] == 'numposts')
			$numposts = (int) $myparam['data'];

		if ($myparam['parameter_name'] == 'darkmode')
			$darkmode =  $myparam['data'];

		if ($myparam['parameter_name'] == 'loadmore')
			$loadmore =  $myparam['data'];

		if ($myparam['parameter_name'] == 'linkimage')
			$linkimage =  $myparam['data'];

		if ($myparam['parameter_name'] == 'disablestyles')
			$disablestyles = $myparam['data'];

	}


	echo '
	  <bsky-embed
    username="' .$blueskyusername . '"
    mode="' . ($darkmode == 'false' ? '' : 'dark')  .'"
    limit="' . $numposts . '"
    link-target="_blank"
    link-image="' . $linkimage  .'"
    load-more="' . $loadmore .'"
    disable-styles="' . $disablestyles .'">
  </bsky-embed>
  <script type="module" src="https://cdn.jsdelivr.net/npm/bsky-embed/dist/bsky-embed.es.js" async></script>

';
}

?>