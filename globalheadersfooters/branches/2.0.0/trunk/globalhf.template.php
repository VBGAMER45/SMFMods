<?php
/*
Global Headers and Footers
Version 1.0
by:vbgamer45
http://www.smfhacks.com
*/
function template_main()
{
	global $boarddir, $scripturl, $context;

	$headercontents = '';
	if(file_exists($boarddir . '/smfheader.txt'))
	{
		$headercontents = file_get_contents($boarddir . '/smfheader.txt');
	}

	$footercontents = '';
	if(file_exists($boarddir . '/smffooter.txt'))
	{
		$footercontents = file_get_contents($boarddir . '/smffooter.txt');
	}

	//Begin the template work

	echo '<div class="tborder">
	<form method="POST" action="'. $scripturl .'?action=globalhf;sa=save">';
	echo '<table border="0" cellspacing="0" cellpadding="4" width="100%">
					<tr class="titlebg">
						<td colspan="3">Global Headers Footers</td>
					</tr>
					<tr>
						<td class="windowbg2">
							Global Headers:<br />
							<textarea rows="12" name="headers" cols="77">' . $headercontents . '</textarea>
						</td>
					</tr>
					</tr>
						<td class="windowbg2">
							Global Footers:<br />
							<textarea rows="12" name="footers" cols="77">' . $footercontents . '</textarea>
						</td>
					</tr>
					</tr>
						<td class="windowbg2" align="center">
							<input type="hidden" name="sc" value="', $context['session_id'], '" />
							<input type="submit" value="Save Headers Footers" name="cmdSubmit" />
		  				</td>
		  			</tr>
		  </table>
		  </from>';
	echo '</div>';

}
?>