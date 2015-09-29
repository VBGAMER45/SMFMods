<?php
/*
Global Headers and Footers
Version 2.0
by:vbgamer45
http://www.smfhacks.com
*/
function template_main()
{
	global $boarddir, $scripturl, $context, $txt;

	$headercontents = '';
	if (file_exists($boarddir . '/smfheader.txt'))
		$headercontents = file_get_contents($boarddir . '/smfheader.txt');


	$footercontents = '';
	if (file_exists($boarddir . '/smffooter.txt'))
		$footercontents = file_get_contents($boarddir . '/smffooter.txt');

	// Begin the template work
	echo '<div class="tborder">
	<form method="post" action="'. $scripturl .'?action=globalhf;sa=save">';
	echo '<table border="0" cellspacing="0" cellpadding="4" width="100%">
					<tr class="titlebg">
						<td colspan="3">', $txt['globalhf_title'],'</td>
					</tr>
					<tr>
						<td class="windowbg2">
							',$txt['globalhf_globalheaders'],'<br />
							<textarea rows="12" name="headers" cols="77">', $headercontents, '</textarea>
						</td>
					</tr>
					</tr>
						<td class="windowbg2">
							',$txt['globalhf_globalfooters'],'<br />
							<textarea rows="12" name="footers" cols="77">', $footercontents, '</textarea>
						</td>
					</tr>
					</tr>
						<td class="windowbg2" align="center">
							<input type="hidden" name="sc" value="', $context['session_id'], '" />
							<input type="submit" value="',$txt['globalhf_saveglobal'],'" name="cmdSubmit" />
		  				</td>
		  			</tr>
				<tr class="windowbg2"><td><b>Has Global Headers and Footers helped you?</b> Then support the developers:<br />
				    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="business" value="sales@visualbasiczone.com">
					<input type="hidden" name="item_name" value="Global Headers Footers">
					<input type="hidden" name="no_shipping" value="1">
					<input type="hidden" name="no_note" value="1">
					<input type="hidden" name="currency_code" value="USD">
					<input type="hidden" name="tax" value="0">
					<input type="hidden" name="bn" value="PP-DonationsBF">
					<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
					<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
				</td></tr>
		  			
		  </table>
		  </from>';
	echo '</div>';

}
?>