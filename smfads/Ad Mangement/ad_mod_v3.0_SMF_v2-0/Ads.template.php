<?php
/******************************************************************************
* SMF: Simple Machines Forum - Ad Management Mod                              *
*                                                                             *
* =========================================================================== *
* Software Version:           Ad mod: 3.2                                     *
* Software by:                smfhacks.com                                    *
* Copyright 2010-2014 by:     smfhacks.com                                    *
* Support site:               www.smfads.com                                  *
*******************************************************************************
* This mod is free software; you may not redistribute or provide a modified   *
* version to redistribute.  This mod is distributed in the hope that it is    *
* and will be useful, but WITHOUT ANY WARRANTIES; without even any implied    *
* warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            *
******************************************************************************/

function template_adminmain()
{
	global $txt, $adverts, $scripturl;
	global $adsSettings;

	$adsFunctions = array('show_topofpageAds', 'show_welcomeAds', 'show_indexAds', 'show_towerleftAds', 'show_towerrightAds', 'show_bottomAds', 'show_boardAds', 'show_threadAds', 'show_posts', 'show_threadindexAds', 'show_category', 'show_underchildren');
	foreach ($adsFunctions as $i => $adsFunction)
	{
		if (function_exists($adsFunction))
			unset($adsFunctions[$i]);
	}

	if(!empty($adsFunctions))
	echo '
		<div style="margin: 2ex; padding: 2ex; border: 2px dashed #cc3344; color: black; background-color: #ffe4e9;">
			<div style="float: left; width: 2ex; font-size: 2em; color: red;">!!</div>
			<b style="text-decoration: underline;">', $txt['error_ads_file_missing_title'] ,'</b><br />
				<div style="padding-left: 6ex;">
					<b>', $txt['error_ads_file_missing'] ,'</b>!<br />

				</div>
		</div><div>';

	echo '
		<div class="cat_bar">
				<h3 class="catbg">', $txt['ad_management_main'] ,'</h3>
            </div>

		<table class="tborder" align="center"  border="0" width="80%" cellspacing="1" cellpadding="4">

			<tr class="catbg3">
				<td width="70%">', $txt['ad_manage_admin_name'] ,'</td>
				<td align="center">', $txt['ad_manage_admin_modify'] ,'</td>
			</tr>';
			if(!empty($adverts))
				for ($i=0;$i<count($adverts);$i++)
				{
					echo '
						<tr class="windowbg2">
							<td>', $adverts[$i]['name'] ,'</td>
							<td class="windowbg2" align="center"><a href="' . $scripturl . '?action=admin;area=ads;sa=edit;ad=' . $adverts[$i]['id'] . '">' . $txt['ad_manage_admin_modify'] . '</a></td>
						</tr>';
				}

	echo '</table></div>';

	echo $txt['ads_copyright'];

}

function template_addAds()
{
		global $txt, $scripturl, $settings;

	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['ad_management_add'] ,'</h3>
            </div>
		<div class="tborder">
		<form action="', $scripturl, '?action=admin;area=ads;sa=add" method="post">
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">

			<tr class="windowbg2">
				<td width="35%" align="center"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_name" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_name'] ,'</td>
				<td width="65%"><input type="text" name="name" /></td>
			</tr>
			<tr class="windowbg2">
				<td align="center"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_content" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_content'] ,'</td>
				<td><textarea cols="70" rows="8" name="content"></textarea></td>
			</tr>
			<tr class="windowbg2">
				<td colspan="2">
					<table border="0" width="80%" cellspacing="1" cellpadding="4">
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_boards" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_boards'] ,'</td>
							<td><input type="text" name="boards" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_posts" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_posts'] ,'</td>
							<td><input type="text" name="posts" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_category" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_category'] ,'</td>
							<td><input type="text" name="category" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_type" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_type'] ,'</td>
							<td>
								<select name="type">
									<option value="0">', $txt['ad_manage_admin_type_html'] ,'</option>
									<option value="1">', $txt['ad_manage_admin_type_php'] ,'</option>
								</select></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_index" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_index'] ,'</td>
							<td><input type="checkbox" name="show_index" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_board" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_board'] ,'</td>
							<td><input type="checkbox" name="show_board" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_threadindex" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_threadindex'] ,'</td>
							<td><input type="checkbox" name="show_threadindex" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_thread" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_thread'] ,'</td>
							<td><input type="checkbox" name="show_thread" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_lastpost" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_lastpost'] ,'</td>
							<td><input type="checkbox" name="show_lastpost" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_topofpage" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_topofpage'] ,'</td>
							<td><input type="checkbox" name="show_topofpage" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_welcome" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_welcome'] ,'</td>
							<td><input type="checkbox" name="show_welcome" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_bottom" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_bottom'] ,'</td>
							<td><input type="checkbox" name="show_bottom" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_towerleft" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_towerleft'] ,'</td>
							<td><input type="checkbox" name="show_towerleft" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_towerright" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_towerright'] ,'</td>
							<td><input type="checkbox" name="show_towerright" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_underchildren" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_underchildren'] ,'</td>
							<td><input type="checkbox" name="show_underchildren" /></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="windowbg2">
					<td colspan="2" align="right" style="padding-top: 1ex;">
						<input type="submit" name="add" value="', $txt['ad_manage_add'], '" />
					</td>
			</tr>
		</table></form></div>';

		echo $txt['ads_copyright'];
}

function template_creditsAds()
{
	global $txt;

		echo '
		<div>
			<div class="cat_bar">
				<h3 class="catbg">', $txt['ad_management_credits'],'</h3>
            </div>
			<table class="tborder" align="center"  border="0" width="100%" cellspacing="1" cellpadding="4">

				<tr class="windowbg2">
					<td>', $txt['ad_manage_show_credits'] ,'</td>
				</tr>
			</table>
						<br />
			<b>Ad Seller Pro</b><br />
Ad Selling System with PayPal support! Supports Banners/textlinks. Unlimited ad locations<br />
And more!<br />
<a href="https://www.smfhacks.com/ad-seller-pro.php">https://www.smfhacks.com/ad-seller-pro.php</a>
<br />
<b>Has the Ad Management Mod helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@smfforfree.com">
	<input type="hidden" name="item_name" value="Ad Management Mod">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<br />
<table>
	<tr>

<td>
<a href="http://www.viglink.com/?vgref=11246">Viglink</a>
<br />
Make money off links that users add on your forum posts! For any merchant that is part of Viglink you get a portion of the sale.

</td>
	</tr>
</table>
		</div>';

		echo $txt['ads_copyright'];

}

function template_editAds()
{
		global $txt, $scripturl, $settings, $adverts, $advertsEdit;

	echo '
		<div class="cat_bar">
				<h3 class="catbg">', $txt['ad_management_main'] ,' - ', $advertsEdit['name'] ,'</h3>
            </div>
		<div class="tborder">
		<form action="', $scripturl, '?action=admin;area=ads;sa=edit;ad=', $advertsEdit['id'], '" method="post">
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="windowbg2">
				<td width="35%" align="center"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_name" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_name'] ,'</td>
				<td width="65%"><input type="text" name="name" value="', $advertsEdit['name'] ,'" /></td>
			</tr>
			<tr class="windowbg2">
				<td align="center"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_content" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_content'] ,'</td>
				<td><textarea cols="70" rows="8" name="content">', $advertsEdit['content'] ,'</textarea></td>
			</tr>
			<tr class="windowbg2">
				<td colspan="2">
					<table border="0" width="80%" cellspacing="1" cellpadding="4">
						<tr class="windowbg2">
							<td align="left" width="55%"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_boards" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_boards'] ,'</td>
							<td><input type="text" name="boards" value="', $advertsEdit['boards'] ,'" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_posts" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_posts'] ,'</td>
							<td><input type="text" name="posts" value="', $advertsEdit['posts'] ,'" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_category" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_category'] ,'</td>
							<td><input type="text" name="category" value="', $advertsEdit['category'] ,'" /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_type" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_type'] ,'</td>
							<td>
								<select name="type">
									<option value="0" ', $advertsEdit['type'] == 0 ? 'selected=selected' : '' ,'>', $txt['ad_manage_admin_type_html'] ,'</option>
									<option value="1" ', $advertsEdit['type'] == 1 ? 'selected=selected' : '' ,'>', $txt['ad_manage_admin_type_php'] ,'</option>
								</select></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_index" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_index'] ,'</td>
							<td><input type="checkbox" name="show_index" ', $advertsEdit['show_index'] == 1 ? 'checked=checked' : '' ,' /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_board" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_board'] ,'</td>
							<td><input type="checkbox" name="show_board" ', $advertsEdit['show_board'] == 1 ? 'checked=checked' : '' ,' /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_threadindex" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_threadindex'] ,'</td>
							<td><input type="checkbox" name="show_threadindex" ', $advertsEdit['show_threadindex'] == 1 ? 'checked=checked' : '' ,' /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_thread" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_thread'] ,'</td>
							<td><input type="checkbox" name="show_thread" ', $advertsEdit['show_thread'] == 1 ? 'checked=checked' : '' ,' /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_lastpost" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_lastpost'] ,'</td>
							<td><input type="checkbox" name="show_lastpost" ', $advertsEdit['show_lastpost'] == 1 ? 'checked=checked' : '' ,' /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_topofpage" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_topofpage'] ,'</td>
							<td><input type="checkbox" name="show_topofpage" ', $advertsEdit['show_topofpage'] == 1 ? 'checked=checked' : '' ,' /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_welcome" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_welcome'] ,'</td>
							<td><input type="checkbox" name="show_welcome" ', $advertsEdit['show_welcome'] == 1 ? 'checked=checked' : '' ,' /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_bottom" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_bottom'] ,'</td>
							<td><input type="checkbox" name="show_bottom" ', $advertsEdit['show_bottom'] == 1 ? 'checked=checked' : '' ,' /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_towerleft" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_towerleft'] ,'</td>
							<td><input type="checkbox" name="show_towerleft" ', $advertsEdit['show_towerleft'] == 1 ? 'checked=checked' : '' ,' /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_towerright" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_towerright'] ,'</td>
							<td><input type="checkbox" name="show_towerright" ', $advertsEdit['show_towerright'] == 1 ? 'checked=checked' : '' ,' /></td>
						</tr>
						<tr class="windowbg2">
							<td align="left"><a href="', $scripturl, '?action=admin;area=ads;sa=help;help=ad_manage_underchildren" onclick="return reqWin(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.gif" alt="', $txt['help'], '" align="top" /></a> ', $txt['ad_manage_admin_show_underchildren'] ,'</td>
							<td><input type="checkbox" name="show_underchildren" ', $advertsEdit['show_underchildren'] == 1 ? 'checked=checked' : '' ,' /></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="windowbg2">
					<td colspan="2" align="right" style="padding-top: 1ex;">
						<input type="submit" name="save" value="', $txt['ad_manage_save'], '" />
						<input type="submit" name="delete" value="', $txt['ad_manage_delete'], '" />
					</td>
			</tr>
		</table></form></div>';

		echo $txt['ads_copyright'];

}

function template_reportsAds()
{
		global $txt, $adverts;

	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['ad_management_reports'],'</h3>
            </div>';


				if(!empty($adverts))
					for ($i=0;$i < count($adverts) ; $i++)
						echo '
							<p><div class="bordercolor">
							<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
								<tr class="titlebg">
									<td width="50%" align="center">', $txt['ad_manage_admin_name'] ,'</td>
									<td width="50%" align="center">', $txt['ad_manage_admin_hits'] ,'</td>
								</tr>
								<tr class="windowbg2">
									<td>', $adverts[$i]['name'] ,'</td>
									<td align="center">', $adverts[$i]['hits'] ,'</td>
								</tr>
								<tr class="windowbg2">
									<td colspan="3">
										<div align="left">
										<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
											<tr class="catbg3">
												<td>', $txt['ad_manage_show_index'] ,'</td>
												<td>', $txt['ad_manage_show_board'] ,'</td>
												<td>', $txt['ad_manage_show_threadindex'] ,'</td>
												<td>', $txt['ad_manage_show_thread'] ,'</td>
												<td>', $txt['ad_manage_show_lastpost'] ,'</td>
												<td>', $txt['ad_manage_show_bottom'] ,'</td>
												<td>', $txt['ad_manage_show_welcome'] ,'</td>
												<td>', $txt['ad_manage_show_topofpage'] ,'</td>
												<td>', $txt['ad_manage_show_towerright'] ,'</td>
												<td>', $txt['ad_manage_show_towerleft'] ,'</td>
												<td>', $txt['ad_manage_show_underchildren'] ,'</td>
												<td>', $txt['ad_manage_adminreports_boards'] ,'</td>
												<td>', $txt['ad_manage_adminreports_posts'] ,'</td>
												<td>', $txt['ad_manage_adminreports_category'] ,'</td>
											</tr>
											<tr class="windowbg2">
												<td>', empty($adverts[$i]['show_index']) ? '-' : '&#8730' ,'</td>
												<td>', empty($adverts[$i]['show_board']) ? '-' : '&#8730' ,'</td>
												<td>', empty($adverts[$i]['show_threadindex']) ? '-' : '&#8730' ,'</td>
												<td>', empty($adverts[$i]['show_thread']) ? '-' : '&#8730' ,'</td>
												<td>', empty($adverts[$i]['show_lastpost']) ? '-' : '&#8730' ,'</td>
												<td>', empty($adverts[$i]['show_bottom']) ? '-' : '&#8730' ,'</td>
												<td>', empty($adverts[$i]['show_welcome']) ? '-' : '&#8730' ,'</td>
												<td>', empty($adverts[$i]['show_topofpage']) ? '-' : '&#8730' ,'</td>
												<td>', empty($adverts[$i]['show_towerright']) ? '-' : '&#8730' ,'</td>
												<td>', empty($adverts[$i]['show_towerleft']) ? '-' : '&#8730' ,'</td>
												<td>', empty($adverts[$i]['show_underchildren']) ? '-' : '&#8730' ,'</td>
												<td>', empty($adverts[$i]['boards']) ? $txt['ad_manage_adminreports_boards_all'] : $adverts[$i]['boards'] ,'</td>
												<td>', empty($adverts[$i]['posts']) ? '-' : $adverts[$i]['posts'] ,'</td>
												<td>', empty($adverts[$i]['category']) ? '-' : $adverts[$i]['category'] ,'</td>

											</tr>
										</table>
										</div>
									</td>
								</tr>
							</table>


							</div></p>';

		echo '
		<hr />
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
<tr class="windowbg2">
					<td colspan="2">
						<br />
			<b>Ad Seller Pro</b><br />
Ad Selling System with PayPal support! Supports Banners/textlinks. Unlimited ad locations<br />
And more!<br />
<a href="https://www.smfhacks.com/ad-seller-pro.php">https://www.smfhacks.com/ad-seller-pro.php</a>
<br />
<b>Has the Ad Management Mod helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@smfforfree.com">
	<input type="hidden" name="item_name" value="Ad Management Mod">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<br />
<table>
	<tr>
	<td><a href="https://www.revenuehits.com/lps/pubref/?ref=@RH@dK6epyQmAa_IjkEbC7aurILIRLfbhe5D" target="_blank"><img src="https://revenuehits.com/publishers/media/img/v2/125x125_v2.png" border="0"></a>


	</td>
	<td>
<a href="http://www.viglink.com/?vgref=11246">Viglink</a>
<br />
Make money off links that users add on your forum posts! For any merchant that is part of Viglink you get a portion of the sale.

</td>
	</tr>
</table>
</td>

	<td>



	</td>
	</tr>
</table>

						';


	echo $txt['ads_copyright'];

}

function template_fatal_ads_error()
{
	global $txt;

	echo '
		<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
			<tr class="titlebg">
				<td>', $txt['ad_management_error'] ,'</td></tr></table>';

}

function template_helpAds()
{
	global $context, $settings, $options, $txt;

	// Since this is a popup of its own we need to start the html, etc.
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
		<title>', $context['page_title'], '</title>
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/style.css" />
		<style type="text/css">';

	// Internet Explorer 4/5 and Opera 6 just don't do font sizes properly. (they are bigger...)
	if ($context['browser']['needs_size_fix'])
		echo '
			@import(', $settings['default_theme_url'], '/fonts-compat.css);';

	// Just show the help text and a "close window" link.
	echo '
		</style>
	</head>
	<body style="margin: 1ex;">
		', $context['help_text'], '<br />
		<br />
		<div align="center"><a href="javascript:self.close();">', $txt[1006], '</a></div>
	</body>
</html>';

}

function template_settingsAds()
{
	global $txt, $adsSettings, $scripturl, $modSettings;


	echo '
		<div>
		    <form action="', $scripturl, '?action=admin;area=ads;sa=settings" method="post">

			<div class="cat_bar">
				<h3 class="catbg">', $txt['ad_management_settings'],'</h3>
            </div>

			<table class="tborder" align="center"  border="0" width="100%" cellspacing="1" cellpadding="4">
					<tr class="windowbg2">
					<td width="60%">', $txt['ad_manage_displayAdsAdmin'] ,'</td>
					<td><input type="checkbox" name="ads_displayAdsAdmin" ', $modSettings['ads_displayAdsAdmin'] == 1 ? 'checked=checked' : '' ,' /></td>
				</tr>
				<tr class="windowbg2">
					<td width="60%">', $txt['ad_manage_updateReports'] ,'</td>
					<td><input type="checkbox" name="ads_updateReports" ', $modSettings['ads_updateReports'] == 1 ? 'checked=checked' : '' ,' /></td>
				</tr>
				<tr class="windowbg2">
					<td width="60%">', $txt['ad_manage_quickDisable'] ,'</td>
					<td><input type="checkbox" name="ads_quickDisable" ', $modSettings['ads_quickDisable'] == 1 ? 'checked=checked' : '' ,' /></td>
				</tr>
				<tr class="windowbg2">
					<td width="60%">', $txt['ad_manage_lookLikePosts'] ,'</td>
					<td><input type="checkbox" name="ads_lookLikePosts" ', $modSettings['ads_lookLikePosts'] == 1 ? 'checked=checked' : '' ,' /></td>
				</tr>
				<tr class="windowbg2">
					<td colspan="2" align="center"><input type="submit" name="save" value="', $txt['ad_manage_save'], '" /></td>
				</tr>
</table>
	</form>
<table class="tborder" align="center"  border="0" width="100%" cellspacing="1" cellpadding="4">
		<tr class="windowbg2">
					<td colspan="2">
						<br />
			<b>Ad Seller Pro</b><br />
Ad Selling System with PayPal support! Supports Banners/textlinks. Unlimited ad locations<br />
And more!<br />
<a href="https://www.smfhacks.com/ad-seller-pro.php">https://www.smfhacks.com/ad-seller-pro.php</a>
<br />
<b>Has the Ad Management Mod helped you?</b> Then support the developers:<br />
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="sales@smfforfree.com">
	<input type="hidden" name="item_name" value="Ad Management Mod">
	<input type="hidden" name="no_shipping" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="USD">
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="bn" value="PP-DonationsBF">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="Make payments with PayPal - it is fast, free and secure!" />
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<br />
<table>
	<tr>

	<td>
<a href="http://www.viglink.com/?vgref=11246">Viglink</a>
<br />
Make money off links that users add on your forum posts! For any merchant that is part of Viglink you get a portion of the sale.

</td>
	</tr>
</table>
</td>
</tr>
</table>

		</div>';

		echo $txt['ads_copyright'];

}

function template_adsindex_above()
{


		//Below the menu ads
	if (function_exists("show_indexAds"))
	{
		$ads = show_indexAds();
		if(!empty($ads))
			if($ads['type']==0)
				echo $ads['content'];
			else
				eval($ads['content']);
		unset($ads);
	}

		if (function_exists("show_towerleftAds") && function_exists("show_towerrightAds"))
	{
		//Tower left Ads
		$ads = show_towerleftAds();
		if(!empty($ads))
			echo '<table><tr><td valign="top">', $ads['type']==0 ? $ads['content'] : eval($ads['content']) ,'</td><td width="100%" valign="top">';

		unset($ads);
		//Tower Right Ads
		$ads = show_towerrightAds();
		if(!empty($ads))
			echo '<table><tr><td width="100%" valign="top">';
		unset($ads);
	}
}


function template_adsindex_below()
{
		//Close table for towerright ads
	if (function_exists("show_towerrightAds") && function_exists("show_towerleftAds") && function_exists("show_bottomAds"))
	{
		$ads = show_towerrightAds();
		if(!empty($ads))
			echo '</td><td valign="top">', $ads['type']==0 ? $ads['content'] : eval($ads['content']) ,'</td></tr></table>';

		unset($ads);
		//Close table for towerleft ads
		$ads = show_towerleftAds();
		if(!empty($ads))
			echo '</td></tr></table>';
		unset($ads);

		//Show ads on the bottom of the page
		$ads = show_bottomAds();
		if(!empty($ads))
			if($ads['type']==0)
				echo $ads['content'];
			else
				eval($ads['content']);
		unset($ads);
	}
}

function template_adsheaders_above()
{
    // Display ads on the top of the page
	if (function_exists("show_topofpageAds"))
	{
		$ads = show_topofpageAds();
		if(!empty($ads))
			if($ads['type']==0)
				echo $ads['content'];
			else
				eval($ads['content']);
		unset($ads);
	}

}

function template_adsheaders_below()
{

}

function template_ads_copyright()
{
	global $txt, $scripturl, $context, $boardurl, $modSettings;


    $modID = 35;

    $urlBoardurl = urlencode(base64_encode($boardurl));

    	echo '
		<div class="cat_bar">
				<h3 class="catbg">', $txt['ads_txt_copyrightremoval'],'</h3>
            </div>
		<form method="post" action="',$scripturl,'?action=admin;area=ads;sa=copyright;save=1">
<table border="0" width="100%" cellspacing="0" align="center" cellpadding="4" class="tborder">
	<tr class="windowbg2">
		<td valign="top" align="right">',$txt['ads_txt_copyrightkey'],'</td>
		<td><input type="text" name="ads_copyrightkey" size="50" value="' . $modSettings['ads_copyrightkey'] . '" />
        <br />
        <a href="https://www.smfhacks.com/copyright_removal.php?mod=' . $modID .  '&board=' . $urlBoardurl . '" target="_blank">' . $txt['ads_txt_ordercopyright'] . '</a>
        </td>
	</tr>
    <tr class="windowbg2">
        <td colspan="2">' . $txt['ads_txt_copyremovalnote'] . '</td>
    </tr>


	<tr class="windowbg2">
		<td valign="top" colspan="2" align="center"><input type="submit" value="' . $txt['ad_manage_save'] . '" />
		</td>
		</tr>
	</table>
	</form>


    ';

}




?>