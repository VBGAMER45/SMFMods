<?php
/*
Push Notifications
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

function template_webpush_settings()
{
	global $context, $txt, $scripturl, $boarddir, $modSettings;

	echo '
 <div class="cat_bar">
		<h3 class="catbg centertext">
        ', $txt['webpush_admin'] , '
        </h3>
  </div>
	<form method="post" name="frmsettings" id="frmsettings" action="', $scripturl, '?action=admin;area=webpush;sa=settings2" accept-charset="', $context['character_set'], '">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">

	    
	    <tr>
	    	<td class="windowbg2" colspan="2" valign="top">' .  $txt['webpush_setup'] .' </td>
	   
	    </tr>  

	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['onesignal_enabled'] .' </td>
	    	<td class="windowbg2"><input type="checkbox" name="onesignal_enabled"' . (!empty($modSettings['onesignal_enabled']) ? ' checked="checked"' : '') . ' />
	    	</td>
	    </tr>  
		 
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['onesignal_appid'] .' </td>
	    	<td class="windowbg2"><input type="text" size="75" name="onesignal_appid" value="' . $modSettings['onesignal_appid'] . '" />
	    	</td>
	    </tr>
	    
	    <tr>
	    	<td class="windowbg2" align="right" valign="top">' . $txt['onesignal_authkey'] .' </td>
	    	<td class="windowbg2"><input type="text" size="75" name="onesignal_authkey" value="' . $modSettings['onesignal_authkey'] . '" />
	    	</td>
	    </tr>
	    
   	    

	  <tr>
	    <td colspan="2" class="windowbg2" align="center">
	     <input type="hidden" name="sc" value="', $context['session_id'], '" />
	    <input type="submit" name="cmdsave" value="',$txt['webpush_txt_savesettings'],'" /></td>
	  </tr>

	  
	  </table>
  	</form>



  ';

}




?>