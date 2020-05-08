<?php
/*
Telegram Autopost
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

function template_telegram_settings()
{
	global $context, $txt, $scripturl, $boarddir, $modSettings;

	echo '
 <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['telegram_admin'] , '
        </h3>
  </div>
	<form method="post" name="frmsettings" id="frmsettings" action="', $scripturl, '?action=admin;area=telegram;sa=settings2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>
	    <td width="50%" colspan="2"  align="center" class="windowbg2"><strong>' . $txt['telegram_txt_settings'] . '</strong>
	    </td>
	    </tr>

		    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['telegram_enable_bot_auth_token'] .'</td>
	    	<td class="windowbg2"><input type="text" size="50" name="telegram_enable_bot_auth_token" value="' . $modSettings['telegram_enable_bot_auth_token'] . '" />
	    	<br /><span class="smalltext">' .$txt['telegram_enable_bot_auth_token_desc'] . '</span>
	    	</td>
	    </tr>    
	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['telegram_enable_chat_id'] .' </td>
	    	<td class="windowbg2"><input type="text" name="telegram_enable_chat_id" value="' . $modSettings['telegram_enable_chat_id'] . '" />
	    	<br /><span class="smalltext">' .$txt['telegram_enable_chat_id_desc'] . '</span>
	    	</td>
	    </tr>  	 
	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['telegram_dateformat'] .'</td>
	    	<td class="windowbg2"><input type="text" size="50" name="telegram_dateformat" value="' . $modSettings['telegram_dateformat'] . '" />
	    	<br /><span class="smalltext">' .$txt['telegram_dateformat_desc'] . '</span>
	    	</td>
	    </tr>
	    

		<tr class="windowbg2">
		<td valign="top" align="right">' . $txt['telegram_boardstopush'] . ' </td>
		<td valign="top">';
	$boardList = explode(",",$modSettings['telegram_boardstopush']);
	
	echo '<select name="telegram_boardstopush[]" multiple="multiple" size="5">';

						foreach ($context['telegram_boards'] as $key => $option)
						{
							if (!in_array($key,$boardList))
							 echo '<option value="' . $key . '">' . $option . '</option>';
							else 
								echo '<option selected="selected" value="' . $key . '">' . $option . '</option>';
							 
						}
					
					echo '</select>';
		
	echo '	
		<br />
			<span class="smalltext">' .$txt['telegram_boardstopush_desc'] . '</span>
			</td>
		</tr>
		 

	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['telegram_enable_push_registration'] .' </td>
	    	<td class="windowbg2"><input type="checkbox" name="telegram_enable_push_registration"' . (!empty($modSettings['telegram_enable_push_registration']) ? ' checked="checked"' : '') . ' />
	    	</td>
	    </tr>  
   
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['telegram_msg_reg'] .' </td>
	    	<td class="windowbg2"><textarea name="telegram_msg_reg" rows="5" cols="50">' . $modSettings['telegram_msg_reg'] . '</textarea>
	    	</td>
	    </tr>  
	    
	    
	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['telegram_enable_push_topic'] .' </td>
	    	<td class="windowbg2"><input type="checkbox" name="telegram_enable_push_topic"' . (!empty($modSettings['telegram_enable_push_topic']) ? ' checked="checked"' : '') . ' />
	    	</td>
	    </tr>  

 	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['telegram_msg_topic'] .' </td>
	    	<td class="windowbg2"><textarea name="telegram_msg_topic" rows="5" cols="50">' . $modSettings['telegram_msg_topic'] . '</textarea>
	    	</td>
	    </tr>   
	    

	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['telegram_enable_push_post'] .' </td>
	    	<td class="windowbg2"><input type="checkbox" name="telegram_enable_push_post"' . (!empty($modSettings['telegram_enable_push_post']) ? ' checked="checked"' : '') . ' />
	    	</td>
	    </tr>  

  
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['telegram_msg_post'] .' </td>
	    	<td class="windowbg2"><textarea name="telegram_msg_post" rows="5" cols="50">' . $modSettings['telegram_msg_post'] . '</textarea>
	    	</td>
	    </tr>   	    

	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	     <input type="hidden" name="sc" value="', $context['session_id'], '" />
	    <input type="submit" name="cmdsave" value="',$txt['telegram_txt_savesettings'],'" /></td>
	  </tr>

	  
	  </table>
  	</form>



  ';

}




?>