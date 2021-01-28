<?php
/*
Discord Web Hooks
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

function template_discord_settings()
{
	global $context, $txt, $scripturl, $boarddir, $modSettings;

	echo '
 <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['discord_admin'] , '
        </h3>
  </div>
	<form method="post" name="frmsettings" id="frmsettings" action="', $scripturl, '?action=admin;area=discord;sa=settings2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	    <td width="50%" colspan="2"  align="center" class="windowbg2"><strong>' . $txt['discord_txt_settings'] . '</strong>
	    </td>
	    </tr>
	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_dateformat'] .'</td>
	    	<td class="windowbg2"><input type="text" size="50" name="discord_dateformat" value="' . $modSettings['discord_dateformat'] . '" />
	    	<br /><span class="smalltext">' .$txt['discord_dateformat_desc'] . '</span>
	    	</td>
	    </tr>
	    

		<tr class="windowbg2">
		<td valign="top">' . $txt['discord_boardstopush'] . ' </td>
		<td valign="top">';
	$boardList = explode(",",$modSettings['discord_boardstopush']);
	
	echo '<select name="discord_boardstopush[]" multiple="multiple" size="5">';

						foreach ($context['discord_boards'] as $key => $option)
						{
							if (!in_array($key,$boardList))
							 echo '<option value="' . $key . '">' . $option . '</option>';
							else 
								echo '<option selected="selected" value="' . $key . '">' . $option . '</option>';
							 
						}
					
					echo '</select>';
		
	echo '	
		<br />
			<span class="smalltext">' .$txt['discord_boardstopush_desc'] . '</span>
			</td>
		</tr>
		 
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_webhook_url'] .' </td>
	    	<td class="windowbg2"><input type="text" size="75" name="discord_webhook_url" value="' . $modSettings['discord_webhook_url'] . '" />
	    	</td>
	    </tr>
	    
	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_enable_push_registration'] .' </td>
	    	<td class="windowbg2"><input type="checkbox" name="discord_enable_push_registration"' . (!empty($modSettings['discord_enable_push_registration']) ? ' checked="checked"' : '') . ' />
	    	</td>
	    </tr>  
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_botname_reg'] .' </td>
	    	<td class="windowbg2"><input type="text" name="discord_botname_reg" value="' . $modSettings['discord_botname_reg'] . '" />
	    	</td>
	    </tr>  	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_msg_reg'] .' </td>
	    	<td class="windowbg2"><textarea name="discord_msg_reg" rows="5" cols="50">' . $modSettings['discord_msg_reg'] . '</textarea>
	    	</td>
	    </tr>  
	    
	    
	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_enable_push_topic'] .' </td>
	    	<td class="windowbg2"><input type="checkbox" name="discord_enable_push_topic"' . (!empty($modSettings['discord_enable_push_topic']) ? ' checked="checked"' : '') . ' />
	    	</td>
	    </tr>  
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_webhook_topic_url'] .' </td>
	    	<td class="windowbg2"><input type="text" size="50" name="discord_webhook_topic_url" value="' . $modSettings['discord_webhook_topic_url'] . '" />
	    	<br /><span class="smalltext">' .$txt['discord_webhook_extra'] . '</span>
	    	</td>
	    </tr>
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_botname_topic'] .' </td>
	    	<td class="windowbg2"><input type="text" name="discord_botname_topic" value="' . $modSettings['discord_botname_topic'] . '" />
	    	</td>
	    </tr>  	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_msg_topic'] .' </td>
	    	<td class="windowbg2"><textarea name="discord_msg_topic" rows="5" cols="50">' . $modSettings['discord_msg_topic'] . '</textarea>
	    	</td>
	    </tr>   
	    

	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_enable_push_post'] .' </td>
	    	<td class="windowbg2"><input type="checkbox" name="discord_enable_push_post"' . (!empty($modSettings['discord_enable_push_post']) ? ' checked="checked"' : '') . ' />
	    	</td>
	    </tr>  
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_webhook_post_url'] .' </td>
	    	<td class="windowbg2"><input type="text" size="50" name="discord_webhook_post_url" value="' . $modSettings['discord_webhook_post_url'] . '" />
	    	<br /><span class="smalltext">' .$txt['discord_webhook_extra'] . '</span>
	    	</td>
	    </tr>
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_botname_post'] .' </td>
	    	<td class="windowbg2"><input type="text" name="discord_botname_post" value="' . $modSettings['discord_botname_post'] . '" />
	    	</td>
	    </tr>  	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_msg_post'] .' </td>
	    	<td class="windowbg2"><textarea name="discord_msg_post" rows="5" cols="50">' . $modSettings['discord_msg_post'] . '</textarea>
	    	</td>
	    </tr>   	    

	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	     <input type="hidden" name="sc" value="', $context['session_id'], '" />
	    <input type="submit" name="cmdsave" value="',$txt['discord_txt_savesettings'],'" /></td>
	  </tr>

	  
	  </table>
  	</form>
<br><br>
 <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['discord_board_level'] , '
        </h3>
  </div>
  	<table border="0" cellpadding="0" cellspacing="0" width="100%">';


	if (!empty($context['discord_hooks']))
	{
		foreach($context['discord_hooks'] as $boardhook)
		{
			echo '<tr>
				<td align="center">' . $boardhook['bName'] . '</td>
				<td align="center">';

			if ($boardhook['push_type'] == 1)
				echo $txt['discord_hook_post'];

			if ($boardhook['push_type'] == 2)
				echo $txt['discord_hook_topic'];

			if ($boardhook['push_type'] == 3)
				echo $txt['discord_hook_both'];

		echo '</td>
				<td align="center">' . $boardhook['url'] . '</td>
				<td align="center"><a href="', $scripturl, '?action=admin;area=discord;sa=deletehook;id=' . $boardhook['id'] . '">' . $txt['discord_delete'] . '</a></td>
				</tr>';
		}
	}

	echo '
  	<tr>
  	<td colspan="4" align="center"><a href="', $scripturl, '?action=admin;area=discord;sa=addhook">' . $txt['discord_add_board_level'] . '</a></td>
	</tr>
  	</table>
  ';


}


function template_discord_add_hook()
{
	global $context, $txt, $scripturl, $boarddir, $modSettings;

	echo '
 <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['discord_admin'], '
        </h3>
  </div>
	<form method="post" name="frmsettings" id="frmsettings" action="', $scripturl, '?action=admin;area=discord;sa=addhook2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">

		<tr class="windowbg2">
		<td valign="top">' . $txt['discord_hook_board'] . ' </td>
		<td valign="top">';

	echo '<select name="boardhook" size="5">';

	foreach ($context['discord_boards'] as $key => $option)
	{

			echo '<option value="' . $key . '">' . $option . '</option>';


	}

	echo '</select>';

	echo '	

			</td>
		</tr>
		 
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_hook_url']  . ' </td>
	    	<td class="windowbg2"><input type="text" size="75" name="url" value="" />
	    	</td>
	    </tr>
	    
	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['discord_hook_type'] . ' </td>
	    	<td class="windowbg2">
	    	<select name="pushtype">
	    	<option value="1">' . $txt['discord_hook_post'] . '</option>
	    	<option value="2">' . $txt['discord_hook_topic'] . '</option>
	    	<option value="3">' . $txt['discord_hook_both'] . '</option>
			</select>
	    	</td>
	    </tr>  


	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	     <input type="hidden" name="sc" value="', $context['session_id'], '" />
	    <input type="submit" name="cmdsave" value="', $txt['discord_add_board_level'] , '" /></td>
	  </tr>

	  
	  </table>
  	</form>';

}

?>