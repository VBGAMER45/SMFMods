<?php
/**********************************************************************************
* Shop.english.php                                                                *
* Language file for SMFShop                                                       *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.1 (Build 14)                              *
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
***********************************************************************************/

global $modSettings, $scripturl;

/*
 * I've done my best to keep this list in alphabetical order, although there might
 * be errors somewhere. If you want to translate the shop into another language, make a copy
 * of this file, and call it 'Shop.[language].php
 *
 * If there's a '%s' anywhere in this file, it means that that is a changeable parameter. For
 * example, it could be an amount of credits.
 */

 // The odd one out :P
$txt['cannot_shop_admin'] = 'Sorry, you aren\'t allowed to access the shop administration! Only admins can do that!';

$txt['shop'] = 'Shop';
$txt['shop_action'] = 'Action';
$txt['shop_add'] = 'Add';
$txt['shop_add_another'] = 'Add another item';
$txt['shop_add_item'] = 'Add An Item';
$txt['shop_add_item_message'] = 'To add a new item to your shop, please use the form below. Choose the item you wish to add to your Shop, and then click "Next >>":';
$txt['shop_add_item_message2'] = '<i>You are adding the item \'%s\' to your shop. For support with this item, please email the author: %s &lt;<a href="mailto:%3$s?subject=%1$s item">%3$s</a>&gt;, or visit their website at <a href="%4$s">%4$s</a></i>';
$txt['shop_added_cat'] = 'Added new category!';
$txt['shop_added_item'] = 'Added item ID';
$txt['shop_admin'] = 'Shop Administration';
$txt['shop_admin_cat'] = 'Category Management';
$txt['shop_admin_general'] = 'General Settings';
$txt['shop_admin_inventory'] = 'Members\' Inventory';
$txt['shop_admin_items'] = 'Item Administration';
$txt['shop_admin_items_add'] = 'Add Items';
$txt['shop_admin_items_edit'] = 'Edit/Delete Items';
$txt['shop_admin_items_addedit'] = 'Add/Edit/Delete Items';
$txt['shop_admin_restock'] = 'Restock Items';
$txt['shop_admin_usergroup'] = 'Membergroup Functions';
$txt['shop_amount'] = 'Amount';
$txt['shop_amount_to_send'] = 'Amount to send';
$txt['shop_asc'] = 'Ascending';
$txt['shop_back'] = '&lt; Back';
$txt['shop_back2admin'] = 'Back to Shop Administration';
$txt['shop_back2bank'] = 'Back to the Bank';
$txt['shop_back2inv'] = 'Back to your Inventory';
$txt['shop_bank'] = 'Bank';
$txt['shop_bank_deposit'] = 'Deposit';
$txt['shop_bank_disableMin'] = 'To allow the user to deposit or withdraw any amount, please enter \'0\' as the minimum';
$txt['shop_bank_fee_deposit'] = 'Deposit fee';
$txt['shop_bank_fee_withdraw'] = 'Withdrawal fee';
$txt['shop_bank_interest'] = 'Bank Interest';
$txt['shop_bank_minDeposit'] = 'Minimum Deposit';
$txt['shop_bank_minWithdraw'] = 'Minimum Withdrawal';
$txt['shop_bank_welcome'] = 'Welcome to the Shop Bank!';
$txt['shop_bank_welcome_full'] = 'Welcome to the Shop Bank! Here, you can safely store your credits and earn interest for them! All money stored in the bank gains interest at a rate of %s%% per day';
$txt['shop_bank_withdraw'] = 'Withdraw';
$txt['shop_bank_withdrawal'] = 'Withdrawal';
$txt['shop_bonuses'] = 'Bonuses';
$txt['shop_bonus_zero'] = 'Setting a bonus to 0 means that it will not be used.';
$txt['shop_bonus_info'] = 'Bonuses are extra credit given to a user depending on the number of words and/or characters in his/her post. The bonuses will be given in addition to the standard settings (as specified above). If the bonuses make the number of points given exceed the limit per post, it will be set to that limit.<br />';
$txt['shop_bought_for'] = 'Bought for %s'; //%s = the price, eg. Bought for $100
$txt['shop_bought_item'] = 'You successfully bought a \'%s\'. To use it, click on the "Your Inventory" link on the left.';
$txt['shop_buy'] = 'Buy Stuff';
$txt['shop_buynow'] = 'Buy Now!';
$txt['shop_cannot_open_items'] = 'ERROR: Cannot open Sources/shop/items dir!';
$txt['shop_cannot_open_images'] = 'ERROR: Cannot open Sources/shop/item_images dir!';
$txt['shop_cat_all'] = '[All]';
$txt['shop_cat_no'] = '[Uncategorised]';
$txt['shop_category'] = 'Category';
$txt['shop_categories'] = 'Categories';
$txt['shop_changed_money'] = 'Changed user ID %s\'s money in pocket to %s and money in bank to %s!';
$txt['shop_count_points'] = 'Count Shop Points';
$txt['shop_count_points_msg'] = 'Members will receive Shop credits for posting in this forum.';
$txt['shop_create_cat'] = 'Create Category';
$txt['shop_currency_prefix'] = 'Currency Prefix';
$txt['shop_currency_suffix'] = 'Currency Suffix';
$txt['shop_currently_have1'] = 'You currently have %s in your pocket';
$txt['shop_currently_have2'] = ' and %s in the bank';
$txt['shop_current_bank'] = 'You currently have %s in your pocket and %s in the bank. Would you like to deposit or withdraw money?';
$txt['shop_database_version'] = 'Database Version';
$txt['shop_delete'] = 'delete';
$txt['shop_deleted'] = 'Deleted item!';
$txt['shop_delete_after_use'] = 'Delete item from user\'s inventory after use?';
$txt['shop_deleted_item'] = 'Deleted Inventory item #%s';
$txt['shop_deleted_cat'] = 'Deleted category #%s';
$txt['shop_deposit'] = 'Deposit processed! You now have %s in the bank and %s in your pocket.';
$txt['shop_deposit_small'] = 'ERROR: You must deposit at least %s!';
//Don't get confused here. shop_description means Description, and shop_desc means Descending
$txt['shop_description'] = 'Description';
$txt['shop_desc'] = 'Descending';
$txt['shop_dont_have_much'] = 'ERROR: You don\'t have that much money!';
$txt['shop_dont_have_much2'] = 'ERROR: You don\'t have that much money in the Bank!';
$txt['shop_edit'] = 'edit';
$txt['shop_edit_inventory'] = 'Edit A Member\'s Inventory';
$txt['shop_edit_item'] = 'Edit Item';
$txt['shop_edit_member'] = 'Editing member id %s (%s)\'s Inventory:';
$txt['shop_edit_member_inventory'] = 'Please type in the member you wish to edit the inventory of, and click "Next--&gt;":';
$txt['shop_edit_message'] = 'Below is a listing of all the items in your Shop. To edit an item, press the "Edit" link beside it. To delete any items, tick the box next to the item, and then press the "Delete" button at the bottom of the page.';
$txt['shop_editing_item'] = 'Editing item ID';
$txt['shop_enter_cat_name'] = 'ERROR: Please enter a category name!';
$txt['shop_give_negative'] = 'Nice try, but how can you GIVE a negative amount...? You were trying to steal, WEREN\'T YOU? ;)';
//Please keep the link to DanSoft Australia here...
global $modSettings;
$txt['shop_guest_message'] = 'Sorry, guests can\'t access the shop!<br />Please register to view this forum\'s shop<br /><br />Powered by SMFShop <br />&copy; 2009-2013 <a href="http://www.smfhacks.com">SMFHacks</a> &copy; 2005, 2006, 2007 <a href="http://www.dansoftaustralia.net/">DanSoft Australia</a><br /><br />';
$txt['shop_im_sendmoney_subject'] = '%s sent to you by %s';
$txt['shop_im_sendmoney_message'] = "%s has sent you %s. If they left a message explaining why, it will be below:\r\n\r\n %s\r\nHave a good day,\r\n --Forum Management";
$txt['shop_im_senditem_subject'] = 'You have been sent an item!';
$txt['shop_im_senditem_message'] = "%s has sent you a %s. If they left a message explaining why, it will be below:\r\n\r\n %s\r\nHave a good day,\r\n --Forum Management";
$txt['shop_im_trade_subject'] = 'Your trade of %s item';
$txt['shop_im_trade_message'] = "Congratulations! [url=' . $scripturl . '?action=profile;u=%s]%s[/url] has purchased your %s item from you for %s.\r\n\r\nHave a good day,\r\n --Forum Management";
$txt['shop_image'] = 'Image';
$txt['shop_image_width'] = 'Image width';
$txt['shop_image_height'] = 'Image height';
$txt['shop_input'] = 'This item requires further input. Please complete the fields below, and then press "Use Item"';
$txt['shop_interest'] = 'Interest';
$txt['shop_invalid_send_amount'] = 'ERROR: Please enter a valid amount to send!';
$txt['shop_inventory'] = 'Inventory';
$txt['shop_invother_message'] = 'Please enter the names of the members you wish to view the Inventory of, and then press "View Other Members\' Inventory"';
$txt['shop_item'] = 'item';
$txt['shop_items'] = 'items';
$txt['shop_item_configure'] = 'Please configure this item below:';
$txt['shop_item_delete_error'] = 'ERROR: Please choose something to delete!';
$txt['shop_item_error'] = 'ERROR: Could not create instance of \'%s\' item!<br />';
$txt['shop_item_notice'] = 'Images are stored in Sources/shop/item_images/. Feel free to add more images!';
$txt['shop_item_to_send'] = 'Item to send';
$txt['shop_itemsperpage'] = 'Items shown per page';
$txt['shop_invother'] = 'View Other Members\' Inventory';
$txt['shop_membergroup'] = 'Membergroup';
$txt['shop_member_name'] = 'Member Name';
$txt['shop_member_no_exist'] = 'ERROR: The member you typed (\'%s\') doesn\'t exist!';
$txt['shop_members_no_exist'] = 'ERROR: The following members did not exist: %s';
$txt['shop_membergroup_desc'] = 'From here, you can add or subtract money from people, depending on which main membergroup they are a part of. Please fill in all of the fields, and then press the "Next--&gt;" button.';
$txt['shop_member_id'] = 'Member ID';
$txt['shop_money_in_bank'] = 'Money in Bank';
$txt['shop_money_in_pocket'] = 'Money in Pocket';
$txt['shop_name'] = 'Name';
$txt['shop_name_desc_match'] = 'Make sure to change the Name and Description above to reflect the values below.';
$txt['shop_need'] = 'You need %s';
$txt['shop_new_cat'] = 'Create a new Category';
$txt['shop_next'] = 'Next --&gt;';
$txt['shop_next2'] = 'Next &gt;';
$txt['shop_not_enough_money'] = 'ERROR: You don\'t have enough money to buy this item (you need %s more)';
$txt['shop_noway'] = 'NO, go back!';
$txt['shop_no_negative'] = 'ERROR: You can\'t Withdraw or Deposit a negative value';
$txt['shop_no_sale'] = 'ERROR: That item is not up for trade!';
$txt['shop_owners'] = 'Who owns this item?';
$txt['shop_per_char'] = 'Per character';
$txt['shop_per_char2'] = 'per character';
$txt['shop_per_new_post'] = 'Per new post';
$txt['shop_per_new_post2'] = 'per new post';
$txt['shop_per_new_topic'] = 'Per new topic';
$txt['shop_per_new_topic2'] = 'per new topic';
$txt['shop_per_post_limit'] = 'Limit per post';
$txt['shop_per_word'] = 'Per word';
$txt['shop_per_word2'] = 'per word';
$txt['shop_please_delete'] = 'Yes, please delete it';
$txt['shop_pocket'] = 'pocket';
$txt['shop_post_limit_zero'] = 'Setting the Limit per Post to 0 means that no limit will be applied.';
$txt['shop_pre-suf_confuse'] = 'Some people get confused over the above settings. Basically, Prefix is what is shown <u>before</u> the number, and suffix is what is shown <u>after</u> the number. Eg. Prefix of \'$\' and suffix of \' credits\' would mean \'$100 credits\'';
$txt['shop_price'] = 'Price';
$txt['shop_reg_bonus'] = 'Amount to get on registration';
$txt['shop_restock_lessthan'] = 'Restock all items with less than';
$txt['shop_restock_amount'] = 'Amount to add to stock';
$txt['shop_richest_bank'] = '10 Richest Members (Bank)';
$txt['shop_richest_pocket'] = '10 Richest Members (Pocket)';
$txt['shop_saved'] = 'Saved Changes!';
$txt['shop_save_changes'] = 'Save Changes';
$txt['shop_send_item'] = 'Send an Item to Someone';
$txt['shop_senditem'] = 'Send item';
$txt['shop_send_items_message'] = 'You can use this to give an item to someone, without them having to pay for the item themselves. If you wish to sell an item, please use the trade center. Note that any items that you have in the trade center won\'t be listed here.';
$txt['shop_send_message_to_give'] = 'Message to give member';
$txt['shop_send_money'] = 'Send Money to Someone';
$txt['shop_send_money_message'] = 'When you send money to someone, they will receive a Personal Message saying that you sent them some money. If you want to attach a message to the Personal Message, please type it in the "Message to give member" box below.';
$txt['shop_settings_general'] = 'General Settings';
$txt['shop_settings_currency'] = 'Currency Settings';
$txt['shop_soldout'] = 'Sold Out!';
$txt['shop_soldout_full'] = 'ERROR: This item is sold out!';
$txt['shop_sort'] = 'Sort by';
$txt['shop_stock'] = 'Stock';
$txt['shop_stoptrade'] = 'Stop Trading';
$txt['shop_subtract'] = 'Subtract';
$txt['shop_successfull_send'] = '%s successfully sent to %s';
$txt['shop_sure_delete'] = 'Are you sure you want to delete the following items:';
$txt['shop_sure_delete_cat'] = 'Are you sure you want to delete this category? All the items in it will be deleted!';
$txt['shop_trade'] = 'Trade Center';
$txt['shop_trade_bought_item'] = 'You successfully bought a "%s" from %s. To use it, click on the "Your Inventory" link on the left.';
$txt['shop_trade_cancelled'] = 'Your item is no longer listed in the Trade Center.';
$txt['shop_trade_enable'] = 'Trade Center Enabled';
$txt['shop_trade_list'] = 'Below is a list of all the items that are currently up for trade.';
$txt['shop_trade_message'] = 'Please type in the amount you wish to trade this item for. Once you click "Next--&gt;", your item will be listed in the Trade Center, and other members will be able to buy this item from you. As soon as a member buys the item, you\'ll receive an email.<br /><br /><b>NOTE: </b>If your item is listed in the Trade Center, you <b>won\'t</b> be able to use it!';
$txt['shop_trade_negative'] = 'ERROR: You can\'t trade an item for a negative amount!';
$txt['shop_trade_success'] = 'Your request was successful. Your item is now listed in the Trade Center, and other members can buy your item from you.';
$txt['shop_trade_welcome'] = 'Welcome to the Shop Trade Center!';
$txt['shop_trade_welcome_full'] = 'Welcome to the Shop Trade Center! This is a place where you can buy items from different members, and sell items that you currently have.';
$txt['shop_tradeitem'] = 'Trade Item';
$txt['shop_trade_saleby'] = 'For sale by %s';
$txt['shop_trading'] = 'Trading for %s';
$txt['shop_transfer_success'] = 'Item successfully transferred!';
$txt['shop_unable_connect'] = 'Unable to connect to SMFHacks website to check for new version!';
$txt['shop_unusable'] = 'Unusable!';
$txt['shop_use'] = 'Use Item';
$txt['shop_use_others_item'] = 'What are you doing? That ISN\'T your item!!! Stop trying to steal, SMFShop is smarter than that ;)';
$txt['shop_use_others_item2'] = 'What are you doing? That ISN\'T your item!!! And you even went to inv3!! Well, I thought you might try that... Stop trying to steal, SMFShop is smarter than that ;)';
$txt['shop_users_own_item'] = '%s users own the item \'%s\':';
$txt['shop_version_info_header'] = 'Version Information';
$txt['shop_version_number'] = 'Version Number';
$txt['shop_version_reldate'] = 'Release Date';
$txt['shop_view_all'] = 'View All Members';
$txt['shop_viewing_inv'] = 'Viewing Inventory of member ID %s (%s)';
$txt['shop_viewing_inv2'] = '%s\'s Inventory';
$txt['shop_wanna_trade'] = 'Want to trade your items? Just click on "Your Inventory" on the left-hand side of the screen, and the click on the "Trade Item" link next to the item name. The item will appear here, and users will be able to buy the item off you. You will receive a notification via PM once someone buys your item.';
$txt['shop_welcome'] = 'Welcome to the Shop!';
$txt['shop_welcome_full'] = 'Welcome to the Shop! Here, you can "buy" stuff with the credits you get from posting on the forum! For each post you make on the forum, you\'ll earn %s per new topic and %s per new post';
$txt['shop_welcome_full2'] = ', plus any bonuses ';
$txt['shop_welcome_full3'] = ' (capped at a maximum of %s)';
$txt['shop_withdraw'] = 'Withdrawal processed! You now have %s in your pocket and %s in the bank.';
$txt['shop_withdraw_small'] = 'ERROR: You must withdraw at least %s!';
$txt['shop_view_all2'] = 'View All Members (Bank)';
$txt['shop_yourinv'] = 'Your Inventory';
$txt['shop_bonuses_enabled'] = 'Enable Shop Bonuses';
$txt['shop_bonuses_enabled_msg'] = 'Shop Bonuses will be take effect in this board';
$txt['shop_credits'] = 'Shop Credits';
$txt['shop_credits_msg'] = 'If custom values are set for these two settings, they will override the settings set on the SMFShop administration page. Set these to "0" to use the default values (currently ' . $modSettings['shopCurrencyPrefix'] . $modSettings['shopPointsPerTopic'] . $modSettings['shopCurrencySuffix'] . ' per topic, and ' . $modSettings['shopCurrencyPrefix'] . $modSettings['shopPointsPerPost'] . $modSettings['shopCurrencySuffix'] . ' per post)';
?>
