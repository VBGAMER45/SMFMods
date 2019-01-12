<?php
/*
Referrals System
Version 3.0
http://www.smfhacks.com
*/

function template_ref_settings()
{
	global $context, $txt, $scripturl, $modSettings;

	echo '
	<form method="post" action="', $scripturl, '?action=refferals;sa=settings2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td width="50%" colspan="2"  align="center" class="catbg">
	    <b>', $txt['ref_admin'], '</b></td>
	  </tr>
	  
	  <tr class="windowbg2">
	  	<td>',$txt['ref_showreflink'],'</td>
	  	<td><input type="checkbox" name="ref_showreflink" ' . ($modSettings['ref_showreflink'] ? ' checked="checked" ' : '') . ' /></td>
	  </tr>
	  
	  <tr class="windowbg2">
	  	<td>',$txt['ref_showonpost'],'</td>
	  	<td><input type="checkbox" name="ref_showonpost"  ' . ($modSettings['ref_showonpost']  ? ' checked="checked" ' : '') . ' /></td>
	  </tr>
	  <tr class="windowbg2">
	  	<td>',$txt['ref_trackcookiehits'],'</td>
	  	<td><input type="checkbox" name="ref_trackcookiehits"  ' . ($modSettings['ref_trackcookiehits']  ? ' checked="checked" ' : '') . ' /></td>
	  </tr>	  
	  
	  <tr class="windowbg2">
	  	<td>',$txt['ref_cookietrackdays'],'</td>
	  	<td><input type="text" name="ref_cookietrackdays" size="4" value="' . $modSettings['ref_cookietrackdays'] . '" /></td>
	  </tr>
	  

	
	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	     <input type="hidden" name="sc" value="', $context['session_id'], '" />
	    <input type="submit" value="',$txt['ref_save_settings'],'" /></td>
	  </tr>
	  </table>
  	</form>';


}


function template_ref_memlist()
{
	global $txt, $scripturl, $context;
	
		echo '
<table cellspacing="0" cellpadding="10" border="0" align="center" width="90%" class="table_grid">
<tr class="catbg">
<td colspan="3">',  $txt['ref_txt_referredmembers'], '</td>
</tr>
		<tr class="catbg">';

				echo  '<td>', $txt['ref_txt_member_name'], '</td>';
				echo '<td>', $txt['ref_txt_member_group'], '</td>';
				echo '<td>', $txt['date_registered'], '</td>';
		
			echo '</tr>
		';
		
		$styleName = 'windowbg';
		
		foreach($context['ref_members'] as $row)
		{
			echo '<tr class="' . $styleName . '">';
			echo '<td><a href="' . $scripturl . '?action=profile;u='  . $row['ID_MEMBER'] . '"' . (!empty($row['onlineColor']) ? ' style="color: ' . $row['onlineColor'] . ';" ' :'' ) . '>', $row['real_name'] ,'</a></td>';
			echo '<td>', $row['groupName'] ,'</td>';
			echo '<td>', timeformat($row['dateRegistered']), '</td>';
			echo '</tr>';
			
			if ($styleName == 'windowbg')
				$styleName = 'windowbg2';
			else
				$styleName = 'windowbg';
				
		}	

		echo '</table>';
	
}


function template_refcopyright()
{
	global $txt, $scripturl, $context, $boardurl, $modSettings;
	
                    
    $modID = 38;
    
    $urlBoardurl = urlencode(base64_encode($boardurl));                
                    
    	echo '
	<form method="post" action="',$scripturl,'?action=refferals;sa=copyright;save=1">
<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td colspan="2">', $txt['ref_txt_copyrightremoval'], '</td>
		</tr>    
	<tr class="windowbg2">
		<td valign="top" align="right">',$txt['ref_txt_copyrightkey'],'</td>
		<td><input type="text" name="ref_copyrightkey" size="50" value="' . $modSettings['ref_copyrightkey'] . '" />
        <br />
        <a href="http://www.smfhacks.com/copyright_removal.php?mod=' . $modID .  '&board=' . $urlBoardurl . '" target="_blank">' . $txt['ref_txt_ordercopyright'] . '</a>
        </td>
	</tr>
    <tr class="windowbg2">
        <td colspan="2">' . $txt['ref_txt_copyremovalnote'] . '</td>
    </tr>
    
    
	<tr class="windowbg2">
		<td valign="top" colspan="2" align="center"><input type="submit" value="' . $txt['ref_save_settings'] . '" />
		</td>
		</tr>
	</table>
	</form>
    

    ';              
                    
}


?>