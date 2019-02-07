<?php
/*
Who Quoted Me
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/
function template_whoquoted_settings()
{
	global $context, $txt, $scripturl, $boarddir, $modSettings;

	echo '
 <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['whoquoted_admin'] , '
        </h3>
  </div>
	<form method="post" name="frmsettings" id="frmsettings" action="', $scripturl, '?action=admin;area=whoquoted;sa=settings2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr class="windowbg2"><td colspan="2" align="center"><input type="checkbox" name="whoquoted_enabled" ',($modSettings['whoquoted_enabled'] ? ' checked="checked" ' : ''), ' />', $txt['whoquoted_enabled'] . '</td></tr>
	    <tr>
	    	<td class="windowbg2" colspan="2" align="center"><strong><a href="', $scripturl, '?action=admin;area=whoquoted;sa=rebuild">' .  $txt['who_quoted_click_redbuild'] .'</a></strong><br /><br /></td>
	    </tr>     	    
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	     <input type="hidden" name="sc" value="', $context['session_id'], '" />
	    <input type="submit" name="cmdsave" value="',$txt['whoquoted_txt_savesettings'],'" /></td>
	  </tr>
	  </table>
  	</form>
  ';
  
	WhoQuotedCopyright();
}

function template_rebuild_quotelog()
{
	global $scripturl, $context, $txt;

	if (empty($context['continue_countdown']))
		$context['continue_countdown'] = 3;

	if (empty($context['continue_get_data']))
		$context['continue_get_data'] ='';

	if (empty($context['continue_post_data']))
		$context['continue_post_data'] ='';


	echo '<b>' . $txt['whoquoted_txt_rebuild']. '</b><br />';

		if (!empty($context['continue_percent']))
		echo '
					<div style="padding-left: 20%; padding-right: 20%; margin-top: 1ex;">
						<div style="font-size: 8pt; height: 12pt; border: 1px solid black; background-color: white; padding: 1px; position: relative;">
							<div style="padding-top: ', $context['browser']['is_webkit'] || $context['browser']['is_konqueror'] ? '2pt' : '1pt', '; width: 100%; z-index: 2; color: black; position: absolute; text-align: center; font-weight: bold;">', $context['continue_percent'], '%</div>
							<div style="width: ', $context['continue_percent'], '%; height: 12pt; z-index: 1; background-color: red;">&nbsp;</div>
						</div>
					</div>';

	echo '<form action="' . $scripturl . '?action=admin;area=whoquoted;sa=rebuild;' , $context['continue_get_data'], '" method="post" accept-charset="', $context['character_set'], '" style="margin: 0;" name="autoSubmit" id="autoSubmit">
				<div style="margin: 1ex; text-align: right;"><input type="submit" name="cont" value="',  $txt['whoquoted_txt_continue'], '" class="button_submit" /></div>
				', $context['continue_post_data'], '

			</form>

			<script type="text/javascript"><!-- // --><![CDATA[
		var countdown = ', $context['continue_countdown'], ';
		doAutoSubmit();

		function doAutoSubmit()
		{
			if (countdown == 0)
				document.forms.autoSubmit.submit();
			else if (countdown == -1)
				return;

			document.forms.autoSubmit.cont.value = "',$txt['whoquoted_txt_continue'] , ' (" + countdown + ")";
			countdown--;

			setTimeout("doAutoSubmit();", 1000);
		}
	// ]]></script>';


}

function template_whoquoted_display()
{
	global $context, $txt, $scripturl, $modSettings;
	
	echo '
 <div class="cat_bar">
		<h3 class="catbg centertext">
		', $txt['whoquoted_txt_me'] , '
		</h3>
  </div>

	<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>', $txt['whoquoted_txt_quoted_by'], '</td>
			<td>', $txt['subject'], '</td>
			<td>', $txt['whoquoted_txt_date'], '</td>
		</tr>';
	
	$styleClass = 'windowbg';
	foreach($context['who_quoted_msgs'] as $row)
	{
		echo '<tr class="' . $styleClass . '">';
		echo '<td><a href="' . $scripturl . '?action=profile&u=' . $row['id_member_from'] . '">' . $row['real_name'] . '</td>';
		echo '<td><a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.msg' . $row['id_msg'] . '#msg' . $row['id_msg'] . '">' . $row['subject'] . '</a></td>';
		echo '<td>' . timeformat($row['logdate']) . '</td>';
		echo '</tr>';
		
		if ($styleClass == 'windowbg')
			$styleClass = 'windowbg2';
		else 
			$styleClass = 'windowbg';
	}
	
	
	echo '<tr class="titlebg">
					<td align="left" colspan="3">
					' . $txt['whoquoted_pages'];
	
		echo $context['page_index'];
	
	echo '</td>
	</tr>
	</table>';
	
	
	WhoQuotedCopyright();
}

function WhoQuotedCopyright()
{
    // To remove use the copyright removal http://www.smfhacks.com/copyright_removal.php
	echo '<div align="center">Powered by <a href="https://www.smfhacks.com">Who Quoted Me</a></div>';
}
?>