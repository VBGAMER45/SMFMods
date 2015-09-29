<?php
/*
User Email System
Version 1.2
by:vbgamer45
http://www.smfhacks.com
*/
function template_main()
{
	global $scripturl, $context, $user_info, $txt;
	
	
	echo '
<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
';

	if ($context['visual_verification'])
	{
		echo '
	function refreshImages()
	{
		// Make sure we are using a new rand code.
		var new_url = new String("', $context['verificiation_image_href'], '");
		new_url = new_url.substr(0, new_url.indexOf("rand=") + 5);

		// Quick and dirty way of converting decimal to hex
		var hexstr = "0123456789abcdef";
		for(var i=0; i < 32; i++)
			new_url = new_url + hexstr.substr(Math.floor(Math.random() * 16), 1);';

		if ($context['use_graphic_library'])
			echo '
		document.getElementById("verificiation_image").src = new_url;';
		else
			echo '
		document.getElementById("verificiation_image_1").src = new_url + ";letter=1";
		document.getElementById("verificiation_image_2").src = new_url + ";letter=2";
		document.getElementById("verificiation_image_3").src = new_url + ";letter=3";
		document.getElementById("verificiation_image_4").src = new_url + ";letter=4";
		document.getElementById("verificiation_image_5").src = new_url + ";letter=5";';
		echo '
	}';
	}

	echo '
// ]]></script>

<form method="POST" action="' . $scripturl . '?action=useremail&sa=save">
<table border="0" cellpadding="0" cellspacing="0"  bordercolor="#FFFFFF" class="tborder" align="center" width="60%" height="129">
  <tr>
    <td width="50%" colspan="2" height="19" align="center" class="catbg">
    <b>',$txt['user_email_sendtitle'],'</b></td>
  </tr>
  <tr>
    <td width="28%" height="19" class="windowbg2"><span class="gen"><b>',$txt['user_email_recipient'],'</b></span></td>
    <td width="72%" height="19" class="windowbg2">' . $context['user_email_name'] . '<input type="hidden" name="userid" value="' . $context['user_email_id'] . '" /></td>
  </tr>';
	
	// Show the Guest email form field
	if ($user_info['is_guest'])
	{
		
	echo '
	  <tr>
	    <td width="28%" height="19" class="windowbg2"><span class="gen"><b>',$txt['user_email_youremail'],'</b></span></td>
	    <td width="72%" height="19" class="windowbg2"><input type="text" name="guestemail" value="" /></td>
	  </tr>';
		
	}
echo '
  <tr>
    <td width="28%" height="22" class="windowbg2"><span class="gen"><b>',$txt['user_email_subject'],'</b></span></td>
    <td width="72%" height="22" class="windowbg2"><input type="text" name="subject" size="64" /></td>
  </tr>
  <tr>
    <td width="28%" height="19" valign="top" class="windowbg2"><span class="gen"><b>',$txt['user_email_body'],'</b></span></td>
    <td width="72%" height="19" class="windowbg2"><textarea rows="6" name="message" cols="54"></textarea></td>
  </tr>
  <tr>
    <td width="28%" height="19" class="windowbg2"><span class="gen"><b>',$txt['user_email_options'],'</b></span></td>
    <td width="72%" height="19" class="windowbg2">
    <input type="checkbox" name="sendcopy" value="ON" checked /><b><span class="gen">',$txt['user_email_sendcopy'],'</span></b></td>
  </tr>';

 	if ($context['visual_verification'])
	{
		echo '
					<tr>
						<td width="40%" align="top" class="windowbg2">
							<b>', $txt['visual_verification_label'], ':</b>
							<div class="smalltext">', $txt['visual_verification_description'], '</div>
						</td>
						<td class="windowbg2">';
		if ($context['use_graphic_library'])
			echo '
							<img src="', $context['verificiation_image_href'], '" alt="', $txt['visual_verification_description'], '" id="verificiation_image" /><br />';
		else
			echo '
							<img src="', $context['verificiation_image_href'], ';letter=1" alt="', $txt['visual_verification_description'], '" id="verificiation_image_1" />
							<img src="', $context['verificiation_image_href'], ';letter=2" alt="', $txt['visual_verification_description'], '" id="verificiation_image_2" />
							<img src="', $context['verificiation_image_href'], ';letter=3" alt="', $txt['visual_verification_description'], '" id="verificiation_image_3" />
							<img src="', $context['verificiation_image_href'], ';letter=4" alt="', $txt['visual_verification_description'], '" id="verificiation_image_4" />
							<img src="', $context['verificiation_image_href'], ';letter=5" alt="', $txt['visual_verification_description'], '" id="verificiation_image_5" />';
		echo '
							<input type="text" name="visual_verification_code" size="30" tabindex="', $context['tabindex']++, '" />

						</td>
					</tr>';
	}
	
 
echo '
  <tr>
    <td width="28%" colspan="2" height="26" align="center" class="windowbg2">
    <input type="submit" value="',$txt['user_email_sendemail'],'" name="submit" /></td>

  </tr>
</table>
</form>
';

	// Copyright link must remain or contact me at http://www.smfhacks.com to purchase copyright removal.
	echo '<br /><div align="center"><a href="http://www.smfhacks.com" target="blank">User Email System</a></div>';
}
function template_send()
{
	global $scripturl, $txt;
echo '
<div>
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>',$txt['user_email_emailsent'],'</td>
		</tr>

		<tr class="windowbg">
			<td style="padding: 3ex;">
				',$txt['user_email_emailreturn'],'
			</td>
		</tr>
	</table>
</div>';


	// Copyright link must remain or contact me at http://www.smfhacks.com to purchase copyright removal.
	echo '<br /><div align="center"><a href="http://www.smfhacks.com" target="blank">User Email System</a></div>';

}
?>