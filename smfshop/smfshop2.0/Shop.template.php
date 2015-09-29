<?php
/**********************************************************************************
* Shop.template.php                                                               *
* Template file for SMFShop                                                       *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.1 (Build 14)                              *
* $Date:: 2009-11-14 19:26:55 +1100 (Thu, 14 Nov 2009)                          $ *
* $Id:: Shop.english.php 79 2009-11-14 08:26:55Z daniel15                       $ *
* Software by:                DanSoft Australia (http://www.dansoftaustralia.net/)*
* Copyright 2009-2013 by:          vbgamer45 (http://www.smfhacks.com)            *
* Copyright 2005-2007 by:     DanSoft Australia (http://www.dansoftaustralia.net/)*
* Support, News, Updates at:  http://www.dansoftaustralia.net/                    *
*                                                                                 *
* Forum software by:          Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version of the license can always be found at                        *
* http://www.simplemachines.org.                                                  *
**********************************************************************************/

// The header. Shown at the 'top' of every shop page.
// I use 'top' loosely, as it's actually the menu on the left
function template_shop_above()
{
	global $txt, $context, $modSettings, $scripturl;
	// Doing nothing? So be it :P
	if (!isset($context['shop_do']))
		$context['shop_do'] = '';
	
	// TODO: Simplify the code (for the links) below?		
	echo '
		<table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding-top: 1ex;"><tr>
			<td width="180" valign="top" style="width: 26ex; padding-right: 10px; padding-bottom: 10px;">
				<table width="100%" cellpadding="4" cellspacing="1" border="0" class="bordercolor">
					<tr>
						<td class="catbg">', $txt['shop'], '</td>
					</tr>
					<tr class="windowbg2">
						<td class="smalltext" style="line-height: 1.3; padding-bottom: 3ex;">
							', ($context['shop_do'] == 'main' ? '<b>' : ''), '<a href="', $scripturl, '?action=shop">', $txt['shop'], ' Home</a>', ($context['shop_do'] == 'main' ? '</b>' : ''), '<br />';
	// Allowed to buy stuff?
	if (allowedTo('shop_buy'))
		echo '
							', ($context['shop_do'] == 'buy' ? '<b>' : '') . '<a href="' . $scripturl . '?action=shop;do=buy">' . $txt['shop_buy'] . '</a>' . ($context['shop_do'] == 'buy' ? '</b>' : '') . '<br />';
	// The inventory - Everyone can access this!
	echo '
							', ($context['shop_do'] == 'inv' ? '<b>' : '') . '<a href="' . $scripturl . '?action=shop;do=inv">' . $txt['shop_yourinv'] . '</a>' . ($context['shop_do'] == 'inv' ? '</b>' : ''), '<br />';
	// Allowed to send money to other people
	if (allowedTo('shop_sendmoney'))
		echo '
							', ($context['shop_do'] == 'sendmoney' ? '<b>' : '') . '<a href="' . $scripturl . '?action=shop;do=sendmoney">' . $txt['shop_send_money'] . '</a>' . ($context['shop_do'] == 'sendmoney' ? '</b>' : '') . '<br />';
	
	// Allowed to send items to other people?
	if (allowedTo('shop_senditems'))
		echo '
							', ($context['shop_do'] == 'senditems' ? '<b>' : ''), '<a href="' . $scripturl . '?action=shop;do=senditems">' . $txt['shop_send_item'] . '</a>' . ($context['shop_do'] == 'senditems' ? '</b>' : ''), '<br />';
	
	// Allowed to view inventory of others?
	if (allowedTo('shop_invother'))
		echo '
							', ($context['shop_do'] == 'invother' ? '<b>' : ''), '<a href="' . $scripturl . '?action=shop;do=invother">' . $txt['shop_invother'] . '</a>' . ($context['shop_do'] == 'invother' ? '</b>' : ''), '<br />';
	// Allowed to access the bank?
	if (allowedTo('shop_bank'))
		echo '
							', ($context['shop_do'] == 'bank' ? '<b>' : '') . ($modSettings['shopBankEnabled']) ? '<a href="' . $scripturl . '?action=shop;do=bank">' . $txt['shop_bank'] . '</a><br />' : '' . ($context['shop_do'] == 'bank' ? '</b>' : '');
	
	// Allowed to access the trade centre?
	if (allowedTo('shop_trade'))
		echo '
							', ($context['shop_do'] == 'trade' ? '<b>' : '') . ($modSettings['shopTradeEnabled']) ? '<a href="' . $scripturl . '?action=shop;do=trade">' . $txt['shop_trade'] . '</a><br />' : '', ($context['shop_do'] == 'trade' ? '</b>' : '');
							
	echo '
						</td>
					</tr>
				</table>
			</td>
			<td valign="top">';

}

// Likewise, this is the footer at the bottom of each page
function template_shop_below()
{
	global $sourcedir;
	//Get the SMFShop version information (file only loaded if not loaded previously)
	require_once($sourcedir . '/shop/shopVersion.php');
	
	echo '
			</td>
		</tr>
	</table>
	<p align="right">
		Powered by <a href="http://www.smfshop.com/">SMFShop</a>  by <a href="http://www.createaforum.com" title="Forum Hosting">CreateAForum.com</a><br />';
	

	
	echo '
	</p>';
}


// The main shop page (ie. the index)
function template_main()
{
	global $txt, $context, $modSettings, $scripturl, $settings;
	
	echo '
			<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor">
				<tr class="titlebg">
					<td align="center" colspan="2" class="largetext headerpadding">', $txt['shop'], '</td>
				</tr><tr>
					<td class="windowbg" valign="top" style="padding: 7px;">
						<b>', $txt['shop_welcome'], '</b>
						<div style="font-size: 0.85em; padding-top: 1ex;">
							', sprintf($txt['shop_welcome_full'], formatMoney($modSettings['shopPointsPerTopic']), formatMoney($modSettings['shopPointsPerPost']));
						
	// Are we using any bonuses at all?
	if ($modSettings['shopPointsPerWord'] != 0 || $modSettings['shopPointsPerChar'] != 0)
	{
		echo $txt['shop_welcome_full2'];
		// Are they capped?
		if ($modSettings['shopPointsLimit'] != 0)
			printf($txt['shop_welcome_full3'], formatMoney($modSettings['shopPointsLimit']));
	}
		
	echo '
							<br /><br />
							', sprintf($txt['shop_currently_have1'], formatMoney($context['user']['money'])), ($modSettings['shopBankEnabled'] ? sprintf($txt['shop_currently_have2'], formatMoney($context['user']['moneyBank'])) : ''), '
							<br /><br />';
	// Any bonuses? Show the heading
	if ($modSettings['shopPointsPerWord'] != 0 || $modSettings['shopPointsPerChar'] != 0)
		echo '
							<strong>', $txt['shop_bonuses'], '</strong><br />';
	// Are we using the points per word bonus?
	if ($modSettings['shopPointsPerWord'] != 0)
		echo '
							', formatMoney($modSettings['shopPointsPerWord']), ' ', $txt['shop_per_word2'], '<br />';
						
	// Are we using the points per char bonus?
	if ($modSettings['shopPointsPerChar'] != 0)
		echo '
							', formatMoney($modSettings['shopPointsPerChar']), ' ', $txt['shop_per_char2'], '<br />';
	
	echo '
							<br />
						</div>
					</td>
				</tr>
			</table>
			<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 1.5ex;"><tr>
				<td valign="top">
					<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor">
						<tr><td class="catbg">', $txt['shop_richest_pocket'], '</td></tr>
						<tr>
							<td class="windowbg2" valign="top" style="height: 18ex;">
								';
	// Richest people (in pocket)
	foreach ($context['shop_richest'] as $row)
		echo '
								<strong><a href="', $scripturl, '?action=profile;u=', $row['ID_MEMBER'], '">', $row['realName'], '</a></strong> - ', formatMoney($row['money']), '<br />';

	echo '					
								<br />
								<a href="', $scripturl, '?action=shop;do=viewall">', $txt['shop_view_all'], '</a>
							</td>
						</tr>
					</table>
				</td>';

	// If the bank's enabled
	if ($modSettings['shopBankEnabled']) {
		echo '
				<td style="width: 1ex;">&nbsp;</td>
				<td valign="top" style="width: 50%;">
					<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor" id="supportVersionsTable">
						<tr>
							<td class="catbg">', $txt['shop_richest_bank'], '</td>
						</tr><tr>
							<td class="windowbg2" valign="top" style="height: 18ex;">
						';
		// Richest people (in bank)
		foreach ($context['shop_richestBank'] as $row)
			echo '
								<strong><a href="', $scripturl, '?action=profile;u=', $row['ID_MEMBER'], '">', $row['realName'], '</a></strong> - ', formatMoney($row['moneyBank']), '<br />
	';
		echo '
								<br />
								 <a href="', $scripturl, '?action=shop;do=viewallBank">', $txt['shop_view_all'], '</a>
							</td>
						</tr>';
	}

	// Close the table
	echo '
					</table>
				</td>
			</tr>
		</table>';
}

// Inventory page.
// Used in various places - The shop 'Buy Stuff' page, your inventory page, etc.
function template_inventory()
{
	global $txt, $context, $modSettings, $scripturl, $boardurl;
	
	// If we need the sort box _or_ categories box (or both)
	if ((isset($context['shop_inv']['sort']) && $context['shop_inv']['sort'] == true) || (isset($context['shop_inv']['categories']))) 
	{
		echo '
				<div class="tborder catbg" style="padding: 4px; border-bottom: none">
					
					<form action="', $scripturl, '" method="get">
						<input type="hidden" name="action" value="shop" />
						<input type="hidden" name="do" value="', $_GET['do'], '" />';
			
		// The categories box?
		if (isset($context['shop_inv']['categories']))
		{
			echo '
						<div style="float: left">
							<b>', $txt['shop_category'], ': </b>
							<select name="cat">
								<option value="-1"', ($context['shop_inv']['category'] == -1 ? ' selected="selected"' : ''), '>', $txt['shop_cat_all'], '</option>
								<option value="0"', ($context['shop_inv']['category'] == 0 ? ' selected="selected"' : ''), '>', $txt['shop_cat_no'], '</option>';
				foreach ($context['shop_inv']['categories'] as $category)
					echo '
								<option value="', $category['id'], '"', ($context['shop_inv']['category'] == $category['id'] ? ' selected="selected"' : ''), '>', $category['name'], ' (', $category['count'], ' ', ($category['count'] == 1 ? $txt['shop_item'] : $txt['shop_items']), ')</option>';
				
				echo '
							</select>
							<input type="submit" value="Go" />
						</div>';
		}
			
		// The sort box?
		if (isset($context['shop_inv']['sort']) && $context['shop_inv']['sort'] == true)
			echo '
						<div style="float: right">
								<b>', $txt['shop_sort'], ':</b>
								<select name="sort">
									<option value="0"', ($context['shop_inv']['sort_type'] == $txt['shop_name'] ? ' selected="selected"' : ''), '>', $txt['shop_name'], '</option>
									<option value="1"', ($context['shop_inv']['sort_type'] == $txt['shop_price'] ? ' selected="selected"' : ''), '>', $txt['shop_price'], '</option>
									<option value="2"', ($context['shop_inv']['sort_type'] == $txt['shop_description'] ? ' selected="selected"' : ''), '>', $txt['shop_description'], '</option>
									<option value="3"', ($context['shop_inv']['sort_type'] == $txt['shop_stock'] ? ' selected="selected"' : ''), '>', $txt['shop_stock'], '</option>
								</select>
								
								<select name="sortDir">
									<option value="0"', ($context['shop_inv']['sort_dir'] == $txt['shop_asc'] ? ' selected="selected"' : ''), '>', $txt['shop_asc'], '</option>
									<option value="1"', ($context['shop_inv']['sort_dir'] == $txt['shop_desc'] ? ' selected="selected"' : ''), '>', $txt['shop_desc'], '</option>
								</select>
								
								<input type="submit" value="Go" />
							
						</div>';
		echo '
					</form><br clear="all" />
				</div>';
	}
	
	echo '
				<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor">
					<tr valign="top" class="titlebg">
						<td style="padding-bottom: 2ex;" width="32">
							<b>', $txt['shop_image'], '</b>
						</td>
						<td>
							<b>', $txt['shop_name'], '</b>
						</td>
						<td>
							<b>', $txt['shop_description'], '</b>
						</td>';
	// Now, do we need the last column?
	if (isset($context['shop_inv']['last_col_header']))
		// If so, we need an extra <td> in the header
		echo '
						<td width="18%">
							<b>', $context['shop_inv']['last_col_header'], '</b>
						</td>
					</tr>';

	$alternating = "windowbg2";
	
	// Loop through all the items
	foreach ($context['shop_inv']['items'] as $item)
	{
		echo '
					<tr valign="top" class="', $alternating, '">
						<td>
							<img border="0" width="', $modSettings['shopImageWidth'], '" height="', $modSettings['shopImageHeight'], '" src="', $boardurl, '/Sources/shop/item_images/', $item['image'], '" alt="Item Image" />
						</td>
						<td style="padding-bottom: 2ex;" width="20%">
							', $item['name'], '
						</td>
						<td>
							', $item['desc'], '
						</td>';
		// Now, for the last column! What type of column do we need?
		// Are we buying something?
		if ($context['shop_inv']['last_col_type'] == 'buy')
		{
			echo '
						<td width="18%">
							', $txt['shop_price'], ': ', $item['price'], '<br />
							', $txt['shop_stock'], ': ', $item['stock'], '<br />';
						
			// If the item is out of stock
			if ($item['stock'] == 0)
				echo '
							<b>', $txt['shop_soldout'], '</b>';
			// If the user doesn't have enough money
			elseif ($context['user']['money'] < $item['price'])
				echo '
							<b>', sprintf($txt['shop_need'], formatMoney($item['price'] - $context['user']['money'])), '</b>';
			// If they have enough money, and can buy it
			else
				echo '
							<a href="', $scripturl, '?action=shop;do=buy2&id=', $item['id'], '">', $txt['shop_buynow'], '</a>';

			// Add the 'Who Owns This' link
			echo '
							<br /><a href="', $scripturl, '?action=shop;do=owners&id=', $item['id'], '">', $txt['shop_owners'], '</a>
						</td>';
		}
		// Otherwise, if we're viewing your inventory
		elseif ($context['shop_inv']['last_col_type'] == 'inv')
		{
			echo '
						<td width="18%">
							Paid: ', $item['amtpaid'], '<br />';

			// The order of the if statements here is important!
			// If the item is in the trade centre
			if ($item['trading'] == 1)
			{
				// Tell them that they can't use it, and give the option to stop trading
				echo '
							<i>', sprintf($txt['shop_trading'], formatMoney($item['tradecost'])), '</i><br /><a href="', $scripturl, '?action=shop;do=trade_stop&id=', $item['id'], '">', $txt['shop_stoptrade'], '</a>';
			}
			else
			{
				// If the item is unusable
				if ($item['can_use_item'] == false)
					echo '
							<b>', $txt['shop_unusable'], '</b>';
				// If no input is needed for the item - Go straight to inv3
				elseif ($item['input_needed'] == false)
					echo '
							<a href="', $scripturl, '?action=shop;do=inv3&id=', $item['id'], '">', $txt['shop_use'], '</a>';
				// If the item requires input - Go to inv2
				else
					echo '
							<a href="', $scripturl, '?action=shop;do=inv2&id=', $item['id'], '">', $txt['shop_use'], '</a>';
				
				// If the trade centre is enabled...
				if ($modSettings['shopTradeEnabled'])
					//... show the 'Trade Item' link
					echo '
							<br /><a href="', $scripturl, '?action=shop;do=trade_sell&id=', $item['id'], '">', $txt['shop_tradeitem'], '</a>';
			}

		}
		// Close this row
		echo '
					</tr>';
		// Change the alternating row background
		$alternating = ($alternating == 'windowbg') ? 'windowbg2' : 'windowbg';

	}

	// If this listing should have paging at the bottom
	if (isset($context['shop_inv']['pages']['current']) && isset($context['shop_inv']['pages']['total']))
	{
		echo '
				<tr>
					<td colspan="4" align="right" class="catbg">Pages: ';
		
		//If current page != 1, show '< Back' link
		if ($context['shop_inv']['pages']['current'] != 1)
		{
			$prevPage = $context['shop_inv']['pages']['current'] - 1;
			echo '
						<a href="', $context['shop_inv']['pages']['link'], ';page=', $prevPage, '">', $txt['shop_back'], '</a> ';
		}
		else
			echo '
						', $txt['shop_back'], '';
		
		// Show links to all pages
		for ($x = 1; $x <= $context['shop_inv']['pages']['total']; $x++)
		{
			// If this number is the current page, don't make number a link
			if ($x == $context['shop_inv']['pages']['current'])
				echo '
						<b>', $x, '</b>';
			else
				echo '
						<a href="', $context['shop_inv']['pages']['link'], ';page=', $x, '">', $x, '</a> ';			
		}
		
		// If current page != last page, show 'Next >' link
		if ($context['shop_inv']['pages']['current'] != $context['shop_inv']['pages']['total'])
		{
			$nextPage = $context['shop_inv']['pages']['current'] + 1;
			echo '
						<a href="', $context['shop_inv']['pages']['link'], ';page=', $nextPage, '">', $txt['shop_next2'], '</a> ';
		}
		else
			echo '
						', $txt['shop_next2'], ' ';
	
		echo '
					</td>
				</tr>';
	}
	
	// Close the table
	echo '
			</table>';
}

// Probably the simplest template. This one simply shows a message to the user.
// Used almost everywhere :)
// TODO: Change some of the SMFShop functions which use this so that they have their own function? This one is overused.
// TODO: Rename $contxet['shop_buy_message'] to something else? It's used for more things than just a Buy message
function template_message()
{
	global $context;

	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 1.5ex;">
			<tr valign="top" class="windowbg2">
				<td style="padding-bottom: 2ex;" width="20%">
					', $context['shop_buy_message'], '
				</td>
			</tr>
		</table>';

}

// Send money to someone page
function template_sendMoney()
{
	global $txt, $context, $modSettings, $scripturl, $settings;
	
	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 1.5ex;">
			<tr class="titlebg">
				<td align="center" class="largetext headerpadding">
					', $txt['shop_send_money'], '
				</td>
			</tr>
			<tr valign="top" class="windowbg2">
				<td style="padding-bottom: 2ex;" width="20%">
					', $txt['shop_send_money_message'], '
					<form action="', $scripturl, '?action=shop;do=sendmoney2" method="post">
						<table>
							<tr>
								<td align="right"><label for="membername">', $txt['shop_member_name'], ':</label></td>
								<td><input type="text" name="membername" id="membername" size="25" value="', $context['shopSendMoneyMember'], '" />
								<a href="', $scripturl, '?action=findmember;input=membername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" border="0" alt="', $txt['find_members'], '" /> Find Members</a></td>
							</tr><tr>
								<td align="right"><label for="amount">', $txt['shop_amount_to_send'], ':</label></td>
								<td>', $modSettings['shopCurrencyPrefix'], '<input type="text" name="amount" id="amount" size="8" />', $modSettings['shopCurrencySuffix'], '</td>
							</tr><tr>
								<td align="right" valign="top"><label for="message">', $txt['shop_send_message_to_give'], ':</label></td>
								<td><textarea name="message" id="message" cols="50" rows="5" style="width: 100%"></textarea></td>
							</tr>
						</table>
						<input type="submit" value="', $txt['shop_send_money'], '" />
					</form>
				</td>
			</tr>
		</table>';
}

// Main bank page
function template_bank()
{
	global $txt, $context, $modSettings, $scripturl, $settings;
	
	echo '
		<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td align="center" colspan="2" class="largetext headerpadding">', $txt['shop'], ' ', $txt['shop_bank'], '</td>
			</tr><tr>
				<td class="windowbg" valign="top" style="padding: 7px;">
					<b>', $txt['shop_bank_welcome'], '</b>
					<div style="font-size: 0.85em; padding-top: 1ex;">', sprintf($txt['shop_bank_welcome_full'], $modSettings['shopInterest']), '<br /><br />
						', ($modSettings['shopMinDeposit'] != 0 ? $txt['shop_bank_minDeposit'] . ': ' . formatMoney($modSettings['shopMinDeposit']) . '<br />' : ''), '
						', ($modSettings['shopMinWithdraw'] != 0 ? $txt['shop_bank_minWithdraw'] . ': ' . formatMoney($modSettings['shopMinWithdraw']) . '<br />' : ''), '
						', ($modSettings['shopFeeDeposit'] != 0 ? $txt['shop_bank_fee_deposit'] . ': ' . formatMoney($modSettings['shopFeeDeposit']) . '<br />' : ''), '
						', ($modSettings['shopFeeWithdraw'] != 0 ? $txt['shop_bank_fee_withdraw'] . ': ' . formatMoney($modSettings['shopFeeWithdraw']) . '<br />' : ''), '
						
					</div>
					 
				</td>
			</tr>
		</table>
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 1.5ex;">
			<tr valign="top" class="windowbg2">
				<td style="padding-bottom: 2ex;" width="20%">
					', sprintf($txt['shop_current_bank'], formatMoney($context['user']['money']), formatMoney($context['user']['moneyBank'])), '<br /><br />
					<form action="', $scripturl, '?action=shop;do=bank2" method="post">
						  <input type="radio" name="type" value="deposit" id="deposit" checked="checked" /><label for="deposit">', $txt['shop_bank_deposit'], '</label>
						  <input type="radio" name="type" value="withdraw" id="withdraw" /><label for="withdraw">', $txt['shop_bank_withdraw'], '</label><br />
						  <label>', $txt['shop_amount'], ': ', $modSettings['shopCurrencyPrefix'], ' <input type="text" name="amount" size="5" /> ', $modSettings['shopCurrencySuffix'], '</label><br />
						  <input type="submit" value="Go!" />
					</form>
				</td>
			</tr>
		</table>';
}

// Viewing someone else's inventory
function template_otherInventory()
{
	global $txt, $context, $scripturl, $settings, $modSettings, $boardurl;

	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 1.5ex;">';
	// Step 1: Enter the name
	if ($_GET['do'] == 'invother')
	{
		echo '
			<tr class="titlebg">
				<td align="center" class="largetext headerpadding">', $txt['shop_invother'], '</td>
			</tr>
			<tr valign="top" class="windowbg2">
				<td style="padding-bottom: 2ex;" width="20%">
					', $txt['shop_invother_message'], '<br />
					<form action="', $scripturl, '?action=shop;do=invother2" method="post">
						', $txt['shop_member_name'], ':
				  
						<input type="text" name="member" id="membername" size="25" />
						<a href="', $scripturl, '?action=findmember;input=member;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" border="0" alt="', $txt['find_members'], '" /> Find Members</a><br />
						<input type="submit" value="', $txt['shop_invother'], '" />
					</form>
				</td>
			</tr>';
	}
	// Actually viewing it now!
	elseif ($_GET['do'] == 'invother2')
	{
		// Loop through all the members
		foreach ($context['shop_invother'] as $member)
		{
			// Heading for this member
			echo '
				<tr class="catbg">
					<td colspan="3" align="center" class="largetext">', sprintf($txt['shop_viewing_inv2'], $member['name']), '</td>
				</tr>
				<tr valign="top"  class="titlebg">
					<td style="padding-bottom: 2ex;" width="32">
						<b>', $txt['shop_image'], '</b>
					</td>
					<td>
						<b>', $txt['shop_name'], '</b>
					</td>
					<td>
						<b>', $txt['shop_description'], '</b>
					</td>
				</tr>';
			
			$alternating = "windowbg2";
			// Now, go through their items
			foreach ($member['items'] as $item)
			{
				echo '
				<tr valign="top" class="', $alternating, '">
					<td>
						<img border="0" width="', $modSettings['shopImageWidth'], '" height="', $modSettings['shopImageHeight'], '" src="', $boardurl, '/Sources/shop/item_images/', $item['image'], '" alt="Item Image" />
					</td>
					<td style="padding-bottom: 2ex;" width="20%">
						', $item['name'], '
					</td>
					<td>
						', $item['desc'], '
					</td>
				</tr>';
				$alternating = ($alternating == "windowbg") ? 'windowbg2' : 'windowbg';
			}
		}
	}
	
	echo '
			</tr>
		</table>';
}

// The trade centre
function template_userTrade()
{
	global $txt, $context, $modSettings, $scripturl, $boardurl;
	
	echo '
		<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor">
			<tr class="titlebg">
				<td align="center" colspan="2" class="largetext headerpadding">', $txt['shop'], ' ', $txt['shop_trade'], '</td>
			</tr><tr>
				<td class="windowbg" valign="top" style="padding: 7px;">
					<b>', $txt['shop_trade_welcome'], '</b>
					<div style="font-size: 0.85em; padding-top: 1ex;">', $txt['shop_trade_welcome_full'], '</div>
				</td>
			</tr>
		</table>
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 1.5ex;">
			<tr valign="top" class="windowbg2">
				<td style="padding-bottom: 2ex;" width="20%">
					', $txt['shop_trade_list'], '<br /><br />
					<table width="100%" cellpadding="5" cellspacing="1" border="0" class="bordercolor">
						<tr valign="top" class="titlebg">
							<td style="padding-bottom: 2ex;" width="32">
								<b>', $txt['shop_image'], '</b>
							</td>
							<td>
								<b>', $txt['shop_name'], '</b>
							</td>
							<td>
								<b>', $txt['shop_description'], '</b>
							</td>
							<td width="18%">
								<b>', $txt['shop_price'], '</b>
							</td>
						</tr>
						';
						
	$alternating = 'windowbg';
	// Loop through all items
	foreach ($context['shop_trade_items'] as $row)
	{

		echo '
						<tr valign="top" class="', $alternating, '">
							<td>
								<img border="0" width="', $modSettings['shopImageWidth'], '" height="', $modSettings['shopImageHeight'], '" src="', $boardurl, '/Sources/shop/item_images/', $row['image'], '" alt="Item Image" />
							</td>
							<td style="padding-bottom: 2ex;" width="20%">
								', $row['name'], '
							</td>
							<td>
								', $row['desc'], '<br />
								<i>', sprintf($txt['shop_trade_saleby'], $row['realName']), '</i>
							</td>
							<td width="18%">
								', $txt['shop_price'], ': ', $row['tradecost'], '<br />';
								
		// If the user has enough money to buy it
		if ($context['user']['money'] >= $row['tradecost'])
			// Show the 'Buy now' link
			echo '
								<a href="', $scripturl, '?action=shop;do=trade_buy&id=', $row['id'], '">', $txt['shop_buynow'], '</a>';
		// Otherwise
		else
			// They can't afford it - Tell them to start saving their money :)
			echo '
								<b>', sprintf($txt['shop_need'], formatMoney($row['tradecost'] - $context['user']['money'])), '</b>';
		
		echo '
							</td>
						</tr>';

		$alternating = ($alternating == 'windowbg') ? 'windowbg2' : 'windowbg';
	}
						
	echo '
					</table>
		
					<br />', $txt['shop_wanna_trade'], '
				</td>
			</tr>
		</table>';
}

// Trade an item in the trade centre
function template_tradeItem()
{
	global $txt, $context, $scripturl;
	
	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 1.5ex;">
			<tr class="titlebg">
				<td align="center" class="largetext headerpadding">', $txt['shop_tradeitem'], '</td>
			</tr>
			<tr valign="top" class="windowbg2">
				<td style="padding-bottom: 2ex;" width="20%">
				  <form action="', $scripturl, '?action=shop;do=trade_sell2" method="post">
					<input type="hidden" name="id" value="', $_GET['id'], '">
					', $txt['shop_price'], ': <input type="text" name="sellfor" value="', $context['shop_paid'], '" />
					<input type="submit" value="', $txt['shop_next'], '" /><br /><br />
					', $txt['shop_trade_message'], '
				</td>
			</tr>
		</table>';
}

// Send an item to somewhere
function template_sendItems()
{
	global $txt, $context, $scripturl, $settings;
	
	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 1.5ex;">
			<tr class="titlebg">
				<td align="center" class="largetext headerpadding">', $txt['shop_send_item'], '</td>
			</tr>
			<tr valign="top" class="windowbg2">
				<td style="padding-bottom: 2ex;" width="20%">
					', $txt['shop_send_items_message'], '
					<form action="', $scripturl, '?action=shop;do=senditems2" method="post">
						<table>
							<tr>
								<td align="right"><label for="membername">', $txt['shop_member_name'], ':</label></td>
								<td>
									<input type="text" name="membername" id="membername" size="25" />
									<a href="', $scripturl, '?action=findmember;input=membername;quote=1;sesc=', $context['session_id'], '" onclick="return reqWin(this.href, 350, 400);"><img src="', $settings['images_url'], '/icons/assist.gif" border="0" alt="', $txt['find_members'], '" /> Find Members</a>
								</td>
							</tr><tr>
								<td align="right"><label for="giftid">', $txt['shop_item_to_send'], ':</label></td>
								<td>
									<select name="giftid" id="giftid">';
						
	foreach ($context['shop_send_items'] as $row) 
		echo '
										<option value="', $row['id'], '">', $row['name'], '</option>';
						
	echo '
									</select>
								</td>
							</tr><tr>
								<td valign="top" align="right"><label for="message">', $txt['shop_send_message_to_give'], ':</label></td>
								<td><textarea name="message" id="message" cols="50" rows="5" style="width: 100%"></textarea></td>
							</tr>
						</table>
						<input type="submit" value="', $txt['shop_senditem'], '" />
					</form>
				</td>
			</tr>
		</table>';
}

// List of members money in pocket, or bank
function template_viewAllMembers()
{
	global $context;
	
	echo '
		<table width="100%" cellpadding="5" cellspacing="0" border="0" class="tborder" style="margin-top: 1.5ex;">
			<tr valign="top" class="windowbg2">
				<td style="padding-bottom: 2ex;" width="20%">';
	foreach ($context['shop_members'] as $row)
		echo '
					<b>', $row['realName'], '</b> - ', formatMoney($row['money']), '<br />';
	echo '
				</td>
			</tr>
		</table>';
}

?>
