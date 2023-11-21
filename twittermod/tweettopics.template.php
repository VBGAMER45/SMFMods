<?php
/*
Tweet Topics/FB Post System
Version 2.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2010-2012 SMFHacks.com

############################################
License Information:
Tweet Topics System is NOT free software.
This software may not be redistributed.

Thelicense is good for a single instance / install on a website.
You are allowed only one active install for each license purchase.

#############################################
*/

function template_twitter()
{
	global $scripturl, $modSettings, $context, $txt, $boardurl;
	
	echo '
	<form method="post" action="',$scripturl,'?action=twitter;sa=twitter2">
	<table border="0" width="100%" cellspacing="1" cellpadding="5" class="bordercolor">
		<tr class="catbg">
		<td colspan="2">',$txt['twitter_admin'],'</td>
		</tr>	
<tr class="windowbg2">
			<td colspan="2">',$txt['twitter_step1'],'
			</td>
		</tr>
		
	<tr class="windowbg2">
	<td colspan="2">' . $txt['twitter_step2']  . (!empty($modSettings['oauth_token']) ? "<b>" . $txt['twitter_step2_part2'] . "</b> " : '') . $txt['twitter_signinwithtwitter'] . ' 
	<a href="',$scripturl,'?action=twitter;sa=twittersignin"><img src="' . $boardurl . '/images/lighter.png" alt="' . $txt['twitter_signinwithtwitter'] . '"/></a>
	</td>
	</tr>
	
	
	
	
	<tr class="windowbg2">
		<td>',$txt['consumer_key'],'</td>
		<td><input type="text" name="consumer_key" size="50" value="' . $modSettings['consumer_key'] . '" /></td>
	</tr>
	<tr class="windowbg2">
		<td>',$txt['consumer_secret'],'</td>
		<td><input type="text" name="consumer_secret" size="50" value="' . $modSettings['consumer_secret'] . '" /></td>
	</tr>
	

	
		';

		
	
	
	
echo '
		<tr class="windowbg2">
		<td valign="top">' . $txt['twitter_boardstotweet'] . '</td>
		<td valign="top">';
	$boardList = explode(",",$modSettings['twitterboards']);
	
	echo '<select name="twitterboards[]" multiple="multiple" size="5">';

						foreach ($context['twitter_boards'] as $key => $option)
						{
							if (!in_array($key,$boardList))
							 echo '<option value="' . $key . '">' . $option . '</option>';
							else 
								echo '<option selected="selected" value="' . $key . '">' . $option . '</option>';
							 
						}
					
					echo '</select>';
		
	echo '	
		<br />
			<span class="smalltext">' .$txt['twitter_selectmultipleboards'] . '</span>
			</td>
		</tr>
		
		
	
	<tr class="windowbg2">
			<td colspan="2"><b>',$txt['twitter_bitly_info'],'</b>
			</td>
		</tr>
	
	<tr class="windowbg2">
		<td>',$txt['bitly_username'],'</td>
		<td><input type="text" name="bitly_username" size="100" value="' . $modSettings['bitly_username'] . '" /></td>
	</tr>
	<tr class="windowbg2">
		<td>',$txt['bitly_apikey'],'</td>
		<td><input type="text" name="bitly_apikey" size="100" value="' . $modSettings['bitly_apikey'] . '" /></td>
	</tr>
		
		<tr class="windowbg2">
		<td valign="top" colspan="2" align="center"><input type="submit" value="' . $txt['twitter_save_settings'] . '" />
		</td>
		</tr>
	</table>
	</form>';
}


function template_facebook()
{
	global $scripturl, $modSettings, $context, $txt, $boardurl;
	
	echo '
	<form method="post" action="',$scripturl,'?action=twitter;sa=fbsettings2">
	<table border="0" width="100%" cellspacing="1" cellpadding="5" class="bordercolor">
		<tr class="catbg">
		<td colspan="2">',$txt['facebook_admin'],'</td>
		</tr>	
<tr class="windowbg2">
			<td colspan="2">',$txt['facebook_step1'],  $boardurl . '/facebookcallback.php','
			</td>
		</tr>
	<tr class="windowbg2">
		<td>',$txt['facebook_appid'],'</td>
		<td><input type="text" name="facebookappid" size="50" value="' . $modSettings['facebookappid'] . '" /></td>
	</tr>
	<tr class="windowbg2">
		<td>',$txt['facebook_appsecret'],'</td>
		<td><input type="text" name="facebookappsecret" size="50" value="' . $modSettings['facebookappsecret'] . '" /></td>
	</tr>';
	
	if (empty($modSettings['facebookappid']) || empty($txt['facebook_appsecret']))
	{
		echo '
	<tr class="windowbg2">
	<td colspan="2">' . $txt['facebook_step2']  . '<font color="#FF0000">' . $txt['facebook_err_step2'] . '</font>
	</td>
	</tr>';
	
			
	}
	else
	echo '
	<tr class="windowbg2">
	<td colspan="2">' . $txt['facebook_step2']  . (!empty($modSettings['facebookacesstoken']) ? $txt['facebook_step2_part2'] : '') . '<a href="https://www.facebook.com/dialog/oauth?client_id=' . $modSettings['facebookappid'] . '&redirect_uri=' . urlencode($boardurl . '/facebookcallback.php') .'&scope=manage_pages,offline_access,publish_stream">' .$txt['facebook_signin'] . '</a>
	</td>
	</tr>


		';


echo '
		<tr class="windowbg2">
		<td valign="top">' . $txt['facebook_boardstotweet'] . '</td>
		<td valign="top">';
	$boardList = explode(",",$modSettings['facebookboards']);
	
	echo '<select name="facebookboards[]" multiple="multiple" size="5">';

						foreach ($context['facebook_boards'] as $key => $option)
						{
							if (!in_array($key,$boardList))
							 echo '<option value="' . $key . '">' . $option . '</option>';
							else 
								echo '<option selected="selected" value="' . $key . '">' . $option . '</option>';
							 
						}
					
					echo '</select>';
		
	echo '	
		<br />
			<span class="smalltext">' .$txt['facebook_selectmultipleboards'] . '</span>
			</td>
		</tr>';
		
echo '
		<tr class="windowbg2">
		<td valign="top">' . $txt['facebook_yourprofile'] . '</td>
		<td valign="top">
		<select name="selectaccount">';

		foreach ($context['fbfanpages'] as $page)
		{
				if ($page['id'] != $modSettings['facebookfanpageid'])
					echo '<option value="' . $page['id']. '">' . $page['name'] . '</option>';
				else 
					echo '<option selected="selected" value="' . $page['id'] . '">' . $page['name'] . '</option>';

					 			
		}
		
echo '
		</select>
		</td>
		</tr>
		<tr class="windowbg2">
		<td valign="top" colspan="2" align="center"><input type="submit" value="' . $txt['facebook_save_settings'] . '" />
		</td>
		</tr>
	</table>
	</form>';
}
?>