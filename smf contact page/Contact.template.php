<?php
/*
Contact Page
Version 1.1
by:vbgamer45
http://www.smfhacks.com
*/
function template_main()
{
	global $scripturl, $txt, $context, $modSettings;

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
	

<form method="post" action="', $scripturl, '?action=contact;sa=save">
<table border="0" cellpadding="0" cellspacing="0" width="60%" align="center" class="tborder" accept-charset="', $context['character_set'], '">
  <tr>
    <td width="50%" colspan="2" align="center" class="catbg">
    <b>',$txt['smfcontact_contact'],'</b></td>
  </tr>
  <tr>
    <td width="28%"  class="windowbg2"><span class="gen"><b>',$txt['smfcontact_name'],'</b></span></td>
    <td width="72%"  class="windowbg2"><input type="text" name="from" size="64" /></td>
  </tr>
  <tr>
    <td width="28%"  class="windowbg2"><span class="gen"><b>',$txt['smfcontact_subject'],'</b></span></td>
    <td width="72%"  class="windowbg2"><input type="text" name="subject" size="64" /></td>
  </tr>
  <tr>
    <td width="28%"  valign="top" class="windowbg2"><span class="gen"><b>',$txt['smfcontact_body'],'</b></span></td>
    <td width="72%"  class="windowbg2"><textarea rows="6" name="message" cols="54"></textarea></td>
  </tr>';
  
 	if ($context['visual_verification'])
	{
		
				if (isset($modSettings['recaptcha_enabled']) && $modSettings['recaptcha_enabled'])
			{
				
	

											echo ' <tr>
						<td colspan="2" align="top" class="windowbg2">
						
						          <div class="g-recaptcha" data-sitekey="', $modSettings['recaptcha_public_key'], '"></div>
								            <script type="text/javascript"
								                    src="https://www.google.com/recaptcha/api.js?hl=en">
								            </script></td></tr>
								';


	
				
				
				/*
				echo '<tr>
						<td colspan="2" align="top" class="windowbg2">
				<script type="text/javascript">
				var RecaptchaOptions = {
				   theme : \'', empty($modSettings['recaptcha_theme']) ? 'clean' : $modSettings['recaptcha_theme'] , '\',
				};
				</script>
				<script type="text/javascript"
					src="https://api.recaptcha.net/challenge?k=', $modSettings['recaptcha_public_key'], '">
				 </script>

				 <noscript>
					<iframe src="https://api.recaptcha.net/noscript?k=', $modSettings['recaptcha_public_key'], '"
						height="300" width="500" frameborder="0"></iframe><br />
					<textarea name="recaptcha_challenge_field" rows="3" cols="40">
					</textarea>
					<input type="hidden" name="recaptcha_response_field"
						value="manual_challenge" />
				 </noscript>
				 </td></tr>';
				 
				 */
			}
			else 
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
	}
	
echo '
  <tr>
    <td width="28%" class="windowbg2"><span class="gen"><b>',$txt['smfcontact_emailaddress'],'</b></span></td>
    <td width="72%" class="windowbg2"><input type="text" name="email" size="64" /></td>
  </tr>
  <tr>
    <td width="28%" colspan="2"  align="center" class="windowbg2">
    <input type="submit" value="',$txt['smfcontact_sendemail'],'" name="submit" /></td>

  </tr>
</table>
</form>
';

	// Copyright required unless copyright removal ordered at smfhacks.com
	echo '<br /><div align="center"><a href="https://www.smfhacks.com" target="blank">Contact Page</a> by <a href="http://www.createaforum.com" title="Forum Hosting">CreateAForum.com</a></div>';
}

function template_send()
{
	global $scripturl, $txt;
echo '
<div>
	<table border="0" width="80%" cellspacing="0" align="center" cellpadding="4" class="tborder">
		<tr class="titlebg">
			<td>',$txt['smfcontact_messagesent'],'</td>
		</tr>

		<tr class="windowbg">
			<td style="padding: 3ex;">
				',$txt['smfcontact_messagesent_click'],'<a href="', $scripturl, '">',$txt['smfcontact_messagesent_return'] ,'
			</td>
		</tr>
	</table>
</div>';
}
?>