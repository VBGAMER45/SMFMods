<?php
/*
Post Scheduler
Version 1.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2012-2015 http://www.samsonsoftware.com
*/

function template_postmain()
{
	global $scripturl, $txt, $context, $modSettings, $boardurl;

	echo '
 	<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['postscheduler_postlist'], '
        </h3>
</div>   
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="windowbg2">
			<td>
				<form action="',$scripturl,'?action=postscheduler;sa=bulkactions" method="post">
				<table width="95%" cellspacing="0" align="center" cellpadding="4" class="tborder">
					<tr class="titlebg">
						<td></td>
						<td>', $txt['postscheduler_date'], '</td>
						<td>', $txt['postscheduler_subject'], '</td>
						<td>', $txt['postscheduler_postername'], '</td>
						<td>', $txt['postscheduler_options'], '</td>
					</tr>';
	
					$styleClass = 'windowbg';
	
					foreach ($context['schedule_posts'] as $key => $post)
					{
						echo '<tr class="' . $styleClass . '">
								<td><input type="checkbox" name="post[', $post['ID_POST'], ']" value="', $post['ID_POST'], '" /></td>
								<td>', timeformat($post['post_time']), '</td> 
								<td>', $post['subject'], '</td> 
								<td>', $post['postername'], '</td>
								<td><a href="', $scripturl, '?action=admin;area=postscheduler;sa=editpost;id=', $post['ID_POST'], '">', $txt['postscheduler_editpost'], '</a></td> 
							</tr>
						';
						
						if ($styleClass == 'windowbg')
							$styleClass = 'windowbg2';
						else 
							$styleClass = 'windowbg';
					}
	
	echo '
	<tr class="windowbg2">
		<td align="left" colspan="5">
			', $txt['postscheduler_pages'], $context['page_index'],
		'</td>
	</tr>
	<tr class="windowbg2">
			<td colspan="5">' .$txt['postscheduler_withselected']  . '<select name="bulk">
			<option value=""></option>
			<option value="delete">' .$txt['postscheduler_delete']  . '</option>
			</select> <input type="submit" value="' . $txt['postscheduler_go'] . '">
			</td>
		 </tr>	
	<tr class="windowbg2">
		<td colspan="5" align="center"><a href="', $scripturl, '?action=admin;area=postscheduler;sa=addpost">', $txt['postscheduler_addpost'], '</a></td>
	</tr>
	</table>
	</form>
	<br />
	', $txt['postscheduler_cronjoburl'], '&nbsp;<b>', $boardurl, '/cronpost.php</b><br />
				<br />
				<b>', $txt['postscheduler_settings'], '</b><br />
				<form method="post" action="', $scripturl, '?action=postscheduler;sa=saveset">
				<input type="checkbox" name="post_fakecron" ', ($modSettings['post_fakecron'] == 1 ? ' checked="checked" ' : ''), ' />', $txt['postscheduler_post_fakecron'], '<br />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<br />
				<input type="submit" value="', $txt['postscheduler_savesettings'], '" />
				</form>
			</td>
		</tr>
</table>';

	schedulercopyright();
}

function template_addpost()
{
	global $context, $txt, $scripturl, $settings;
	
echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['postscheduler_addpost'], '
        </h3>
</div>
			<form method="post" action="', $scripturl, '?action=postscheduler;sa=addpost2" name="frmfeed"  id="frmfeed" onsubmit="submitonce(this);">
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
	<tr class="windowbg2">
			 <td width="30%">', $txt['postscheduler_subject'], '</td>
			 <td><input type="text" name="subject" size="50" /></td>
	</tr>
	<tr class="windowbg2">
		<td width="30%">' .$txt['postscheduler_date'] .'</td>
		<td>
		<select name="month">
		<option value="0"></option>';
		for($i = 1; $i < 13; $i++)
			echo '<option value="' . $i . '">' . date("M",mktime(0,0,0,$i+1,0,0)) . '</option>';
	
	echo '
		</select>
		<select name="day">
		<option value="0"></option>
		';
		for($i = 1; $i < 32; $i++)
			echo '<option value="' . $i . '">' . $i . '</option>';
	
	echo '
	</select>
	<select name="year">
	<option value="0"></option>
		';
	for($i = date("Y"); $i < date("Y")  + 3; $i++)
		echo '<option value="' . $i . '">' . $i . '</option>';
	
	echo '
		</select>';
	
	echo '<select name="hour">';
	for($i = 1; $i < 13;$i++)
	{
		echo '<option value="' . $i . '">' . $i . '</option>';
	}
	echo '
		</select>';


	echo '<select name="minute">';
	for($i = 0; $i < 60;$i++)
	{
		echo '<option value="' . $i . '">' . str_pad($i, 2, "0", STR_PAD_LEFT) . '</option>';
	}
	
	echo '
		</select>
		<select name="ampm">
		   <option value="am">AM</option>
		   <option value="pm">PM</option>
		</select>
		</td>
	</tr>
	<tr class="windowbg2">
			<td width="30%">', $txt['postscheduler_topicid'], '</td><td><input type="text" name="topicid" size="10" value="0" /></td>
	</tr>
	<tr class="windowbg2">
		<td width="30%">', $txt['postscheduler_forum'], '</td>
		<td><select name="boardselect" id="boardselect">
  ';

	foreach ($context['schedule_boards'] as $key => $option)
		 echo '<option value="', $key, '">', $option, '</option>';

echo '</select></td></tr>
	<tr class="windowbg2">
		<td width="30%">', $txt['postscheduler_msg_icon'], '</td>
		<td><select name="msgicon" id="msgicon" onchange="ChangeIconPic(this.value)">
  ';

	foreach ($context['msg_icons'] as $key => $option)
		 echo '<option value="', $option['filename'], '">', $option['title'], '</option>';

	echo '</select> <img id="iconPick" src="" alter="" />

	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	// iconSelect
	var selectIcons = new Array();
	';

	foreach ($context['msg_icons'] as $key => $option)
	{
		echo ' selectIcons["', $option['filename'], '"] = "', ($settings[file_exists($settings['theme_dir'] . '/images/post/' . $option['filename'] . (!function_exists("set_tld_regex") ? '.gif' : '.png') ) ? 'actual_images_url' : 'default_images_url'] . '/post/' . $option['filename']  . (!function_exists("set_tld_regex") ? '.gif' : '.png')) . '";';
	}

echo '
	function ChangeIconPic(iconIndex)
	{
		document.frmfeed.iconPick.src = selectIcons[iconIndex];
	}
	
	ChangeIconPic("xx");
	
	// ]]></script>
		</td>
	</tr>
	<tr class="windowbg2">
		<td width="30%">', $txt['postscheduler_postername'], '</td><td><input type="text" name="postername" id="postername" /><a href="', $scripturl, '?action=findmember;input=postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/' . (!function_exists("set_tld_regex") ? 'assist.gif' : 'members.png') . '" alt="', $txt['find_members'], '" /></a> <a href="', $scripturl, '?action=findmember;input=postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a></td>
	</tr>
	<tr class="windowbg2">
		<td colspan="2" align="center">
		<table>
   ';

	if (!function_exists('getLanguages'))
	{
			
		// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
								<tr class="windowbg2">
		
									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'bbc'), '
									</td>
								</tr>';
		}
	
		// What about smileys?
		if (!empty($context['smileys']['postform']))
			echo '
								<tr class="windowbg2">
		
									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'smileys'), '
									</td>
								</tr>';
	
		// Show BBC buttons, smileys and textbox.
		echo '
								<tr class="windowbg2">
								
									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'message'), '
									</td>
								</tr>';
	}
	else 
	{
		echo '
								<tr class="windowbg2">
		<td colspan="2">';
			// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
					<div id="bbcBox_message"></div>';
		}
	
		// What about smileys?
		if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			echo '
					<div id="smileyBox_message"></div>';
	
		// Show BBC buttons, smileys and textbox.
		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		
		echo '</td></tr>';
	}
	
	
		echo '
					</table>
		</td>
	</tr>
	<tr class="windowbg2"><td width="30%" align="right"><input type="checkbox" name="topiclocked" /></td><td>', $txt['postscheduler_topiclocked'], '</td></tr>
	<tr class="windowbg2">
			<td colspan="2" align="center">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="submit" name="addpost" value="', $txt['postscheduler_addpost'],  '" />
		</td>
	</tr>
		</table>
		</form>
';

	schedulercopyright();
}

function template_editpost()
{
	global $context, $txt, $scripturl, $settings;
	
echo '
	<div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['postscheduler_editpost'], '
        </h3>
</div>


			<form method="post" action="', $scripturl, '?action=postscheduler;sa=editpost2" name="frmfeed"  id="frmfeed" onsubmit="submitonce(this);">
	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4">
	<tr class="windowbg2">
		  	<td width="30%">', $txt['postscheduler_subject'], '</td><td><input type="text" name="subject" size="50" value="' . $context['schedulepost']['subject'] . '" /></td>
	</tr>
	<tr class="windowbg2">
		<td width="30%">' .$txt['postscheduler_date'] .'</td>
		<td>
		';

	$day = date("j", $context['schedulepost']['post_time']);
	$month = date("n", $context['schedulepost']['post_time']);
	$year = date("Y", $context['schedulepost']['post_time']);

	$minute = date("i", $context['schedulepost']['post_time']);
	$ampm  = date("a", $context['schedulepost']['post_time']);

	$hour = date("g", $context['schedulepost']['post_time']);
	
	echo '<select name="month">
		<option value="0"></option>';
		for($i = 1; $i < 13; $i++)
			echo '<option value="' . $i . '" ' . ($i == $month ? ' selected="selected" ' : '') . '>' . date("M",mktime(0,0,0,$i+1,0,0)) . '</option>';
	
	echo '
		</select>
		<select name="day">
			<option value="0"></option>
		';
		for($i = 1; $i < 32; $i++)
			echo '<option value="' . $i . '" ' . ($i == $day ? ' selected="selected" ' : '') . '>' . $i . '</option>';
	
	echo '
		</select>
		<select name="year">
			<option value="0"></option>
		';
		for($i = date("Y"); $i < date("Y")  + 3; $i++)
			echo '<option value="' . $i . '" ' . ($i == $year ? ' selected="selected" ' : '') . '>' . $i . '</option>';
	
	echo '
		</select>';
	
	echo '<select name="hour">';
	for($i = 1; $i < 13;$i++)
	{
		echo '<option value="' . $i . '" ' . ($i == $hour ? ' selected="selected" ' : '') . '>' . $i . '</option>';
	}
	
	echo '
		</select>';

	echo '<select name="minute">';
	
	for($i = 0; $i < 60;$i++)
	{
		echo '<option value="' . $i . '" ' . ($i == $minute ? ' selected="selected" ' : '') . '>' . str_pad($i, 2, "0", STR_PAD_LEFT) . '</option>';
	}
	
	echo '
		</select>
		<select name="ampm">
		   <option value="am" ' . ('am' == $ampm ? ' selected="selected" ' : '') . '>AM</option>
		   <option value="pm" ' . ('pm' == $ampm ? ' selected="selected" ' : '') . '>PM</option>
		</select>
		</td>
	</tr>
	<tr class="windowbg2">
		<td width="30%">', $txt['postscheduler_topicid'], '</td><td><input type="text" name="topicid" size="10" value="' . $context['schedulepost']['ID_TOPIC'] . '" /></td></tr>
	<tr class="windowbg2">
		<td width="30%">', $txt['postscheduler_forum'], '</td>
		<td><select name="boardselect" id="boardselect">
  ';
	foreach ($context['schedule_boards'] as $key => $option)
		 echo '<option value="', $key, ' " ', ($key == $context['schedulepost']['ID_BOARD'] ? ' selected="selected" ' : ''), '>', $option, '</option>';

	echo '</select></td></tr>
	<tr class="windowbg2">
		<td width="30%">', $txt['postscheduler_msg_icon'], '</td><td>
			<select name="msgicon" id="msgicon"  onchange="ChangeIconPic(this.value)">
  ';
  
	foreach ($context['msg_icons'] as $key => $option)
		 echo '<option value="', $option['filename'], '"' . ($context['schedulepost']['msgicon'] == $option['filename'] ? ' selected="selected"' : '') . '>', $option['title'], '</option>';

	echo '</select>
<img id="iconPick" src="" alter="" />

	<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
	// iconSelect
	var selectIcons = new Array();
	';

	foreach ($context['msg_icons'] as $key => $option)
	{
		echo ' selectIcons["', $option['filename'], '"] = "', ($settings[file_exists($settings['theme_dir'] . '/images/post/' . $option['filename']  . (!function_exists("set_tld_regex") ? '.gif' : '.png')) ? 'actual_images_url' : 'default_images_url'] . '/post/' . $option['filename']  . (!function_exists("set_tld_regex") ? '.gif' : '.png')) . '";';
	}

	echo '
		function ChangeIconPic(iconIndex)
		{
			document.frmfeed.iconPick.src = selectIcons[iconIndex];
		}
		
		ChangeIconPic("' . $context['schedulepost']['msgicon'] . '");
		
		// ]]></script>
	</td>
	</tr>
	<tr class="windowbg2">
		<td width="30%">', $txt['postscheduler_postername'], '</td><td><input type="text" name="postername" id="postername" value="' . $context['schedulepost']['postername'] . '" /><a href="', $scripturl, '?action=findmember;input=postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/' . (!function_exists("set_tld_regex") ? 'assist.gif' : 'members.png') . '" alt="', $txt['find_members'], '" /></a> <a href="', $scripturl, '?action=findmember;input=postername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);">', $txt['find_members'], '</a>
		</td>
	</tr>
	<tr class="windowbg2">
		<td colspan="2" align="center">
		<table>
   ';

	if (!function_exists('getLanguages'))
	{
			
		// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
								<tr class="windowbg2">
		
									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'bbc'), '
									</td>
								</tr>';
		}
	
		// What about smileys?
		if (!empty($context['smileys']['postform']))
			echo '
								<tr class="windowbg2">
		
									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'smileys'), '
									</td>
								</tr>';
	
		// Show BBC buttons, smileys and textbox.
		echo '
								<tr class="windowbg2">
								
									<td colspan="2" align="center">
										', template_control_richedit($context['post_box_name'], 'message'), '
									</td>
								</tr>';
	}
	else 
	{
		echo '
								<tr class="windowbg2">
		<td colspan="2">';
			// Showing BBC?
		if ($context['show_bbc'])
		{
			echo '
					<div id="bbcBox_message"></div>';
		}
	
		// What about smileys?
		if (!empty($context['smileys']['postform']) || !empty($context['smileys']['popup']))
			echo '
					<div id="smileyBox_message"></div>';
	
		// Show BBC buttons, smileys and textbox.
		echo '
					', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');

		
		echo '</td></tr>';
	}
	
	
		echo '
					</table>
		</td>
	</tr>
	<tr class="windowbg2">
		<td width="30%" align="right"><input type="checkbox" name="topiclocked" ', ($context['schedulepost']['locked'] ? ' checked="checked" ' : ''), ' /></td><td>', $txt['postscheduler_topiclocked'], '</td></tr>
	<tr class="windowbg2">
		<td colspan="2" align="center">
			<input type="hidden" name="id" value="', $context['schedulepost']['ID_POST'], '" />
			<input type="submit" name="editpost" value="', $txt['postscheduler_editpost'],  '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</td>
	</tr>
	</table>
			</form>
		';

	schedulercopyright();
	
}

function schedulercopyright()
{
	// The Copyright is required to remain or contact me to purchase link removal.
	echo '<br /><div align="center"><a href="https://www.smfhacks.com" target="blank">Post Scheduler</a></div>';
}

?>