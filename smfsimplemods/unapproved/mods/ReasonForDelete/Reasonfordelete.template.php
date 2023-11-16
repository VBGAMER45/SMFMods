<?php
/**
 * Reason For Delete (SMF)
 *
 * @package SMF
 * @author 4kstore
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 *
 * @version 1.0
 */

function template_ShowRFD()
{
	global $user_info, $topic, $board, $sourcedir, $smcFunc, $context, $modSettings,$txt,$scripturl;
	checkSession('get');
	if (empty($modSettings['rfd_enabled']))
	redirectexit('action=removetopic2;topic='.$context['current_topic'].'.0;'.$context['session_var'].'='.$context['session_id'].'');

	echo'<span class="clear upperframe"><span></span></span>
		<div class="roundframe"><div class="innerframe">
		<div align="center">
		<form action="'.$scripturl.'?action=removetopic2;topic=' . $context['current_topic'] . '.0;' . $context['session_var'] . '=' . $context['session_id'].'" method="post">
	';
	foreach($context['inforows'] as $infor)
	{	
		echo'
		<strong>',$txt['rfd_delete_topic'],'</strong> '.$infor['subjecthref'].'<br />
		<strong>',$txt['rfd_PostedBy'],'</strong> '.$infor['posterhref'].'<br /><br />
		<select name="reason">
		<option value="0">'.$txt['rfd_no_info'] .'</option>
		<option value="" disabled="disabled">'.$txt['rfd_choose_info'] .'</option>';
		foreach($context['rfdrows'] as $rowsrfd) //loadrfdTable
		{
		echo'
		<option value="'.$rowsrfd['id'].'">'.$rowsrfd['title'].'</option>';
		}	
		echo'
		</select> <br /><br />
		<strong>',$txt['rfd_additional_info'],'</strong><br />
		<textarea name="infoadd" cols="80" rows="10" id="infoadd"></textarea><br />
		<input type="submit" name="save" value="',$txt['rfd_delete'],'" />
		<input type="hidden" name="author" value="'.$infor['id_member'].'" />	
		<input type="hidden" name="subject" value="'.$infor['subject'].'" />
		<input type="hidden" name="poster_name" value="'.$infor['poster_name'].'" />
		
		';
	}
	echo'
	<input type="hidden" name="save" value="ok" />	
		
		</form>
	</div>

	</div></div>
		<span class="lowerframe"><span></span></span>
	';
}


//RFD ADMIN SETTINGS!
function template_rfd_settings()
{
	global $context, $scripturl, $txt, $settings, $modSettings;
	
	echo'
	
	<form method="post" action="', $scripturl, '?action=admin;area=reasonfordelete" accept-charset="', $context['character_set'], '">												
														
		<table width="100%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
		<tr>
				<td width="100%" colspan="10" class="description" style="font-size:15px;font-weight:bold;text-align:center;">						
					'.$txt['rfd_settings'].'
				</td>
			</tr>
		<tr>
				<td align="left" width="50%" valign="top" class="windowbg2" style="border: 1px solid #ccc;">
				'.$txt['rfd_enabled'].'
			</td>
			<td align="center" width="50%" valign="top" class="windowbg2" style="border: 1px solid #ccc;">
				<input type="checkbox" value="1" name="rfd_enabled" ',!empty($modSettings['rfd_enabled']) ? 'checked="checked"' : '',' />
			</td>
			</tr>
			<tr>
				<td align="left" width="50%" valign="top" class="windowbg2" style="border: 1px solid #ccc;">
				'.$txt['rfd_titleset'].'
			</td>
			<td align="center" width="50%" valign="top" class="windowbg2" style="border: 1px solid #ccc;">
				<input type="text" size="50" value="',!empty($modSettings['rfd_titleset']) ? $modSettings['rfd_titleset'] : '','" name="rfd_titleset" />
			</td>
			</tr>
			<tr>
				<td align="left" width="50%" valign="top" class="windowbg2" style="border: 1px solid #ccc;">
				'.$txt['rfd_idusersend'].'
			</td>
			<td align="center" width="50%" valign="top" class="windowbg2" style="border: 1px solid #ccc;">
				<input type="text" size="50" value="',!empty($modSettings['rfd_senderid']) ? $modSettings['rfd_senderid'] : '','" name="rfd_senderid" />
			</td>
			</tr>			
			</table>
			<table width="100%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" colspan="10" class="description" style="font-size:15px;font-weight:bold;text-align:center;">						
					'.$txt['rfd_AddProfiles'].'
				</td>
			</tr>';			
	echo '		
			<tr>
				
				<td valign="top"  align="center" class="information">									
					', $txt['rfd_title'] ,'
				</td>
				<td valign="top" align="center" class="information">									
					', $txt['rfd_default_text'] ,'
				</td>
				<td valign="top" align="center" class="information">									
					', $txt['rfd_edit'] ,'
				</td>
				<td valign="top"  align="center" class="information">									
					', $txt['rfd_delete'] ,'
				</td>
			</tr><br />';

	
		foreach($context['rfdrows'] as $rfd)
		{	
			echo '		
			<tr>
							
				<td valign="top" align="left" class="windowbg2" style="border: 1px solid #ccc;">									
					<strong>', $rfd['title'] ,'</strong>
					<br />
					', $rfd['description'] ,'
					<br />
				</td>
				<td valign="top" align="center" class="windowbg2" style="border: 1px solid #ccc;">									
					', $rfd['default_text'] ,'
					
				</td>		
				<td valign="top" width="5%" align="center" class="windowbg2" style="border: 1px solid #ccc;">									
					', $rfd['edit'] ,'				
				</td>
				<td valign="top" width="5%" align="center" class="windowbg2" style="border: 1px solid #ccc;">									
					', $rfd['delete'] ,'
					
				</td>
			</tr>';

		}	

	echo '		
		</table>
		
		<table width="100%" cellspacing="0" cellpadding="0" align="center">	
			<tr>
				<td align="center" colspan="0" class="windowbg2">	
					<br /><input type="submit" name="save" value="',$txt['rfd_btn_save'],'" /><br /><br />
				</td>
			</tr>
		</table>
	</form>';

}

function template_rfd_add()
{
	global $context, $scripturl, $txt, $settings;

	echo'
	<form method="post" name="sectform" action="', $scripturl, '?action=admin;area=reasonfordelete;sa=add" accept-charset="', $context['character_set'], '">												
		<table width="90%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="description">						
					'.$txt['rfd_AddProfiles'].'
				</td>
			</tr>
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg2">									
					', $txt['rfd_title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="title" size="70" maxlength="100"/>
				</td>
			</tr>	
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg2">									
					', $txt['description'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="description" size="70" maxlength="150"/>
				</td>
			</tr>	
			
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg2">									
					', $txt['rfd_default_text'], '<br />
					<strong>',$txt['rfd_variables'],'</strong><br />
					',$txt['rfd_poster_name'],' (member_name)<br />
					',$txt['rfd_subject_topic'],' (subject_topic)
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<textarea name="default_text" cols="60" rows="15" id="reasonForDelete"></textarea>
				</td>
			</tr>
						
	
		</table>
		<table width="90%" align="center" cellspacing="1" cellpadding="5" border="0">			
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['rfd_btn_save'],'" value="',$txt['rfd_btn_save'],'" />
				</td>
			</tr>
		</table>
	</form>';
}

function template_rfd_edit()
{
	global $context, $scripturl, $txt, $settings;

	echo'
	<form method="post" name="sectform" action="', $scripturl, '?action=admin;area=reasonfordelete;sa=edit" accept-charset="', $context['character_set'], '">												
		<table width="90%" align="center" class="bordercolor" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="100%" align="left" colspan="2" class="description">	
					', $txt['rfd_EditProfiles'] ,'
				</td>
			</tr>';
	foreach($context['rfdrows'] as $rfd)		
	{
	echo '		
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg2">									
					', $txt['title'], '
				</td>
				<td width="50%" align="center" class="windowbg2">	
					<input type="hidden" name="id" value="', $rfd['id'] ,'"/>									
					<input type="text" name="title" size="70" maxlength="100" value="', $rfd['title'] ,'"/>
				</td>
			</tr>	
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg2">									
					', $txt['description'], '
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="description" size="70" maxlength="150" value="', $rfd['description'] ,'"/>
				</td>
			</tr>	
			<tr>
				<td valign="top" width="50%" align="left" class="windowbg2">									
					', $txt['rfd_default_text'], '<br />
					<strong>',$txt['rfd_variables'],'</strong><br />
					',$txt['rfd_poster_name'],' (member_name)<br />
					',$txt['rfd_subject_topic'],' (subject_topic)
					
				</td>
				<td width="50%" align="center" class="windowbg2">									
					<textarea name="default_text" cols="100" rows="10">', $rfd['default_text'] ,'</textarea>
				</td>
			</tr>';
	}
			echo'
		</table>
		<table width="90%" align="center" cellspacing="1" cellpadding="5" border="0">			
			<tr>
				<td align="center" colspan="2">	
					<input type="hidden" name="save" value="ok" />	
					<input type="hidden" name="sc" value="', $context['session_id'], '" />
					<input type="submit" name="',$txt['rfd_btn_save'],'" value="',$txt['rfd_btn_save'],'" />
				</td>
			</tr>
			
		</table>
	</form>';
	

}

//End Admin

?>