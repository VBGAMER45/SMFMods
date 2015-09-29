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
function template_facehook() {
global $txt,$context;
echo'<div class="information">'.$txt['fb_hookinfo'].'
<a href="http://developers.facebook.com/docs/plugins/">http://developers.facebook.com/docs/plugins/</a>
</div>';
echo'<span class="upperframe"><span></span></span>
			<div class="roundframe">
			<div class="cat_bar">
				<h3 class="catbg">'.$txt['fb_hook'].'</h3>
			</div>
			<p>'.$context['hooks'].'</p>
			</div>
			<span class="lowerframe"><span></span></span>';
}


function template_faceab() {
global $txt,$boardurl, $context;
$context['is_smf2'] = true;
          echo'<table cellpadding="3" cellspacing="1" border="0" width="100%" class="windowbg" height="135">
							<tr>
								<td colspan="2" class="titlebg">'.$txt['fb_sinfo'].'</td>
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
				<td>'.$txt['fb_credit'].'<span class="smalltext" style="font-weight: normal"> ~ '.$txt['fb_credit1'].'</span></td>
			</tr>';

	foreach ($context['facebook_credits'] as $credit)
		echo '
			<tr class="windowbg2">
				<td>', $credit['name'], !empty($credit['nickname']) ? ' a.k.a. ' . $credit['nickname'] : '', !empty($credit['site']) ? ' - <a href="' . $credit['site'] . '">' . $credit['site'] . '</a>' : '', ' - ', $credit['position'], '</td>
			</tr>';

	echo '
		</table>';
		
		echo'<table cellpadding="6" cellspacing="1" border="0" width="100%" class="bordercolor" style="margin-top: 8px">
			<tr class="titlebg">
				<td>'.$txt['fb_credit2'].'</td>
			</tr>
			<tr class="windowbg2">
				<td class="smalltext">';

	foreach ($context['facebook_thanks'] as $credit)
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
	            <input type="hidden" name="item_name" value="Mod Donations" />
	            <input type="hidden" name="no_shipping" value="1" />
	            <input type="hidden" name="no_note" value="1" />
	            <input type="hidden" name="currency_code" value="USD" />
	            <input type="hidden" name="tax" value="0" />
	            <input type="hidden" name="bn" value="PP-DonationsBF" />
	            <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
	            <img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
				</form></p>
				
				</td>
			</tr>
			</table>';
}
function template_face_boards(){
global $context, $txt;
echo'<script type="text/javascript">//<![CDATA[
window.fbpublish_init = true;
		
			if (typeof(document.getElementsByClassName) != \'function\')
				document.getElementsByClassName = function(clsName)
				{
						var retVal = new Array();
						var elements = document.getElementsByTagName(\'input\');
						for(var i = 0;i < elements.length;i++){
								if(elements[i].className.indexOf(\' \') >= 0){
										var classes = elements[i].className.split(" ");
										for(var j = 0;j < classes.length;j++){
												if(classes[j] == clsName)
														retVal.push(elements[i]);
										}
								}
								else if(elements[i].className == clsName)
										retVal.push(elements[i]);
						}
						return retVal;
				}
			
			function checkChildBoards(cat_id)
			{
				if (window.fbpublish_init) return;
				var cat_box = document.getElementById(\'cat_\' + cat_id);
				var board_boxes = document.getElementsByClassName(\'_board_of_cat_\' + cat_id);
				if (cat_box && board_boxes && board_boxes.length)
					for ( var i in board_boxes)
						board_boxes[i].checked = cat_box.checked
			}
			
			function checkCurrentStates1()
			{
				var cat_boxes = document.getElementsByClassName(\'_cat\');
				if (cat_boxes && cat_boxes.length)
					for (var i in cat_boxes)
					{
						if (!cat_boxes[i].id) continue;
						var board_boxes = document.getElementsByClassName(\'_board_of_cat_\' + cat_boxes[i].id.split(\'_\')[1]);
						var checked_cnt = 0;
						for (var j in board_boxes)
							if (board_boxes[j].checked) checked_cnt++;
						if (checked_cnt < board_boxes.length) cat_boxes[i].checked=false;
						else cat_boxes[i].checked=true;
					}					
			}
			//]]></script>';
echo '
	<form action="', $context['post_url'], '" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
							<h3 class="catbg">
				<span>'.$txt['fb_board'].'</span>
			</h3>
						</div>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tborder" align="center">
			<tr><td>
				<table border="0" cellspacing="0" cellpadding="2" width="100%">';     
	$number = 0;
	$current_catagory = 0;
	foreach ($context['face_boards'] as $board_id)
		if (!empty($board_id['is_cat'])){
			echo '
					<tr class="catbg">
						<td><label for="cat_', $current_catagory = $board_id['id_cat'], '">', $board_id['cat_name'], '</label></td>
						<td width="10">	<label for="cat_', $current_catagory = $board_id['id_cat'], '">'.$txt['fb_apub'].'</label></td>
						<td width="10">	<label for="cat_', $current_catagory = $board_id['id_cat'], '">'.$txt['fb_alike'].'</label></td>
						<td width="10">	<label for="cat_', $current_catagory = $board_id['id_cat'], '">'.$txt['fb_acom'].'</label></td>
						<td width="10" style="padding: 2px">
							<input class="_cat" id="cat_', $board_id['id_cat'], '" type="checkbox" onclick="checkChildBoards(', $board_id['id_cat'], ');" />
						</td>
					</tr>';
					}
		else{
			echo '
					<tr class="windowbg', $number++ % 2 ? '2' : '','">
						<td style="padding-left: 20px"><label for="board_', $board_id['id_board'], '">', $board_id['board_name'], '</label>
					
					</td>
					<td width="10">						
							<input class="_board_of_cat_', $current_catagory, '" id="board_', $board_id['id_board'], '" type="checkbox" name="boardspub[]" value="', $board_id['id_board'], '" ', $board_id['pub_enable'] ? 'checked="checked"' : '', ' />
						</td>
						<td width="10">
							<input class="_board_of_cat_', $current_catagory, '" id="board_', $board_id['id_board'], '" type="checkbox" name="boardslike[]" value="', $board_id['id_board'], '" ', $board_id['like_enable'] ? 'checked="checked"' : '', ' />
						</td>
						<td width="10">						
							<input class="_board_of_cat_', $current_catagory, '" id="board_', $board_id['id_board'], '" type="checkbox" name="boardscom[]" value="', $board_id['id_board'], '" ', $board_id['com_enable'] ? 'checked="checked"' : '', ' />
						</td>
						<td width="10">						
							
						</td>
					</tr>';
	
	}
	echo '
				</table>
			</td></tr>
			<tr><td align="center" style="padding-top: 5px">
				<input type="submit" value="save" />
				<input type="hidden" name="sc" value="', $context['session_id'], '" />
			</td></tr>
		</table>
	</form>';
	echo'<script type="text/javascript">//<![CDATA[
	checkCurrentStates1();
	window.fbpublish_init = false;
	//]]></script>';
	
	
}
function template_facelog() {
global $txt, $scripturl, $context;
template_show_list('fb_list');
}
?>