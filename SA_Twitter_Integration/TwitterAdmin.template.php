<?php
/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://www.sa-mods.info
 *
 * The Initial Developer of the Original Code is
 * wayne Mankertz.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 * ***** END LICENSE BLOCK ***** */
function template_main() {

}
function template_twit_about() {

global $txt,$boardurl, $context;
$context['is_smf2'] = true;
          echo'<table cellpadding="3" cellspacing="1" border="0" width="100%" class="windowbg" height="135">
							<tr>
								<td colspan="2" class="titlebg">'.$txt['tw_sinfo'].'</td>
							</tr>
							<tr>
								<td class="windowbg2">'.$context['saphpver'].'</td>
								<td class="windowbg2">'.$context['safe_mode_go'].'</td>
							</tr>
							<tr>
								<td class="windowbg2">'.$context['sayesno'].'</td>
								<td class="windowbg2">'.$context['sayesnojson'].'</td>
							</tr>
						</table>';
echo '
		<table cellpadding="6" cellspacing="1" border="0" width="100%" class="bordercolor"', $context['is_smf2'] ? ' style="margin-top: 1ex"' : '', '>
			<tr class="titlebg">
				<td>'.$txt['tw_credit'].'<span class="smalltext" style="font-weight: normal"> ~ '.$txt['tw_credit1'].'</span></td>
			</tr>';

	foreach ($context['tw_credits'] as $credit)
		echo '
			<tr class="windowbg2">
				<td>', $credit['name'], !empty($credit['nickname']) ? ' a.k.a. ' . $credit['nickname'] : '', !empty($credit['site']) ? ' - <a href="' . $credit['site'] . '">' . $credit['site'] . '</a>' : '', ' - ', $credit['position'], '</td>
			</tr>';

	echo '
		</table>';
		
		echo'<table cellpadding="6" cellspacing="1" border="0" width="100%" class="bordercolor" style="margin-top: 8px">
			<tr class="titlebg">
				<td>'.$txt['tw_credit2'].'</td>
			</tr>
			<tr class="windowbg2">
				<td class="smalltext">';

	foreach ($context['tw_thanks'] as $credit)
		echo '
					', isset($credit['site']) ? '<a href="' . $credit['site'] . '">' . $credit['name'] . '</a>' : $credit['name'], ' - ', $credit['position'], '<br />';

	echo '
				</td>
			</tr>
			</table>';
			
			echo'<table cellpadding="6" cellspacing="1" border="0" width="100%" class="bordercolor" style="margin-top: 8px">
			<tr class="windowbg2">
				<td class="smalltext">
				
				<p style="margin: 0; padding: 1ex 0 1ex 0;">If you like my modification packages, please donate 
                to support their continued development. <br />Any amount will be greatly appreciated. Thank you!<br />
							    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	            <input type="hidden" name="cmd" value="_xclick" />
	            <input type="hidden" name="business" value="sales@visualbasiczone.com" />
	            <input type="hidden" name="item_name" value="SMF Modifications" />
	            <input type="hidden" name="no_shipping" value="1" />
	            <input type="hidden" name="no_note" value="1" />
	            <input type="hidden" name="currency_code" value="USD" />
	            <input type="hidden" name="tax" value="0" />
	            <input type="hidden" name="bn" value="PP-DonationsBF" />
	            <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
	            <img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
				</form>
</p>
				
				</td>
			</tr>
			</table>';

}
function template_twit_boards(){
global $context, $txt;
echo '
	<form action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
							<h3 class="catbg">
				<span>'.$txt['twittaboard1'].'</span>
			</h3>
						</div>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
			<tr><td>
				<table border="0" cellspacing="0" cellpadding="2" width="100%">';     
	$number = 0;
	$current_catagory = 0;
	foreach ($context['twit_boards'] as $board_id)
		if (!empty($board_id['is_cat'])){
			echo '
					<tr class="catbg">
						<td><label for="cat_', $current_catagory = $board_id['id_cat'], '">', $board_id['cat_name'], '</label></td>
						<td width="10">	<label for="cat_', $current_catagory = $board_id['id_cat'], '">'.$txt['tw_entweet'].'</label></td>
						<td width="10">	<label for="cat_', $current_catagory = $board_id['id_cat'], '">'.$txt['tw_enpub5'].'</label></td>
						
					</tr>';
					}
		else{
			echo '
					<tr class="windowbg', $number++ % 2 ? '2' : '','">
						<td style="padding-left: 20px"><label for="board_', $board_id['id_board'], '">', $board_id['board_name'], '</label>
					</td>
					
						<td width="10">
							<input class="_board_of_cat_', $current_catagory, '" id="board_', $board_id['id_board'], '" type="checkbox" name="boardstweet[]" value="', $board_id['id_board'], '" ', $board_id['tweet_enable'] ? 'checked="checked"' : '', ' />
						</td>
						<td width="10">
							<input class="_board_of_cat_', $current_catagory, '" id="board_', $board_id['id_board'], '" type="checkbox" name="boardspub[]" value="', $board_id['id_board'], '" ', $board_id['tweet_pubenable'] ? 'checked="checked"' : '', ' />
						</td>
					</tr>';
	
	}
	echo '
				</table>
			</td></tr>
			<tr><td align="center" style="padding-top: 5px">
				<input type="submit" value="'.$txt['twittaboard2'].'" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
			</td></tr>
		</table>
	</form>';
	
	
}
function template_twitlog() {

template_show_list('twit_list');

}
?>