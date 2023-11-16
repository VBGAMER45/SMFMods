<?php
/******************************************************************************
* SMF: Simple Machines Forum - Ad Management Mod                              *
*                                                                             *
* =========================================================================== *
* Software Version:           Ad mod: 3.1                                     *
* Software by:                smfhacks.com                                    *
* Copyright 2010-2013 by:     smfhacks.com                                    *
* Support site:               www.smfads.com                                  *
*******************************************************************************
* This mod is free software; you may not redistribute or provide a modified   *
* version to redistribute.  This mod is distributed in the hope that it is    *
* and will be useful, but WITHOUT ANY WARRANTIES; without even any implied    *
* warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            *
******************************************************************************/
global $helptxt;

//Ad Managment Admin Strings
$txt['ad_management'] = 'Ad Management';
$txt['ad_management_disc'] = 'Welcome to the admin panel for the Ad Management Mod.';
$txt['ad_management_main'] = 'Edit Ads';
$txt['ad_management_add'] = 'Add Ads';
$txt['ad_management_reports'] = 'Reports';
$txt['ad_management_settings'] = 'Settings';
$txt['ad_management_credits'] = 'Credits';
$txt['ad_manage_admin_name'] = 'Name';
$txt['ad_manage_admin_content'] = 'Content';
$txt['ad_manage_admin_modify'] = 'Modify';
$txt['ad_manage_admin_boards'] = 'Which boards should this ad display in';
$txt['ad_manage_admin_posts'] = 'Display ads within posts';
$txt['ad_manage_admin_category'] = 'Display ads after categories';
$txt['ad_manage_admin_type'] = 'Type';
$txt['ad_manage_admin_type_html'] = 'HTML';
$txt['ad_manage_admin_type_php'] = 'PHP';
$txt['ad_manage_save'] = 'Save';
$txt['ad_manage_delete'] = 'Delete';
$txt['ad_manage_add'] = 'Add';
$txt['ad_manage_admin_show_index'] = 'Display ads on every page right below the menu';
$txt['ad_manage_admin_show_board'] = 'Display ads on board index';
$txt['ad_manage_admin_show_threadindex'] = 'Display ads on message index';
$txt['ad_manage_admin_show_thread'] = 'Display ads on the post page';
$txt['ad_manage_admin_show_lastpost'] = 'Display ads after the last post';
$txt['ad_manage_admin_show_bottom'] = 'Display ads on the bottom of the page';
$txt['ad_manage_admin_show_welcome'] = 'Display ads in the welcome user area';
$txt['ad_manage_admin_show_topofpage'] = 'Display ads on every page on the top of the page';
$txt['ad_manage_admin_show_towerright'] = 'Display ads on every page as a tower on the right side';
$txt['ad_manage_admin_show_towerleft'] = 'Display ads on every page as a tower on the left side';
$txt['ad_manage_admin_show_underchildren'] = 'Display ads under child boards';


//Help Strings
$helptxt['ad_manage_help'] = 'Help Pages';
$helptxt['ad_manage_name'] = 'This is the name that you will give your ads. This will help identify your ads.';
$helptxt['ad_manage_content'] = 'This section is for placing your ad code. You get this code from the ad company itself.';
$helptxt['ad_manage_type'] = 'Two types of ad code are allowed. Either html(javascript) or PHP. You must choose the correct one.';
$helptxt['ad_manage_boards'] = 'This is for board specific ads. If you want your ads to display on board 1, you must put 1 in the input box.
If you want to display the ads on board 2, you put 2 in the input box. If you want to display ads on both board 1 and 2, you have to put 1,2 (the comma is a must).
If you wish to display ads on all boards, you leave the input box empty.';
$helptxt['ad_manage_posts'] = 'This is for display ads inbetween posts. If you want to display ads after the first post, you put 1 in the input box.
If you wish to display ads after the second post, you put 2 in the input box.
If you want to display ads inbetween both the first and the second post, you must put 1,2 (comma is a must) in the input box.
If you don\'t want to display any ads inbetween posts, leave the input box empty.';
$helptxt['ad_manage_category'] = 'This will display ads after categories.
If you want to display the ads after the second category, you put 2 in the input box. If you want to display ads between category 1 and 2, you have to put 1,2 (the comma is a must).
If you don\'t want to display any ads inbetween categories, you leave the input box empty.';
$helptxt['ad_manage_index'] = 'This is for displaying ads right under the menu page. These ads will display on everypage within your forum.';
$helptxt['ad_manage_board'] = 'This is for displaying ads only on the main board area. This section is typically the root of the forum. The ad location is right under the menu bar.';
$helptxt['ad_manage_threadindex'] = 'This is for displaying ads on the message index page. These pages are found within the boards, but are not the posts. The ad location is right under the menu bar.';
$helptxt['ad_manage_thread'] = 'This is for displaying ads on the message page itself. The ad location is right under the menu bar.';
$helptxt['ad_manage_lastpost'] = 'This is for displaying ads after the last post. It will display a page after the last post.';
$helptxt['ad_manage_topofpage'] = 'This is for displaying ads on the very top of your page. These ads will display on every page of your forum.';
$helptxt['ad_manage_welcome'] = 'This is for displaying ads in the welcome area of the user. These ads will display on every page of your forum.';
$helptxt['ad_manage_bottom'] = 'This is for displaying ads on the very bottom of the page. These ads will display on every page of your forum.';
$helptxt['ad_manage_towerleft'] = 'This is for display ads on the left side of the page. Also known as skyscraper, these ads will display on every page of your forum.';
$helptxt['ad_manage_towerright'] = 'This is for display ads on the right side of the page. Also known as skyscraper, these ads will display on every page of your forum.';
$helptxt['ad_manage_underchildren'] = 'This will display ads after child boards.';
$txt['119'] = 'Help';
$txt['1006'] = 'Close';

//Extra $txt's for Reports
$txt['ad_manage_admin_hits'] = 'Hits';
$txt['ad_manage_ad_position'] = 'Positions of ad';
$txt['ad_manage_adminreports_boards'] = 'Boards';
$txt['ad_manage_adminreports_posts'] = 'Posts';
$txt['ad_manage_adminreports_category'] = 'After Categories';
$txt['ad_manage_adminreports_boards_all'] = 'All';
$txt['ad_manage_show_index'] = 'Below menu';
$txt['ad_manage_show_board'] = 'Board index';
$txt['ad_manage_show_threadindex'] = 'Message index';
$txt['ad_manage_show_thread'] = 'Post';
$txt['ad_manage_show_lastpost'] = 'Last Post';
$txt['ad_manage_show_bottom'] = 'Bottom';
$txt['ad_manage_show_welcome'] = 'Welcome area';
$txt['ad_manage_show_topofpage'] = 'Top of page';
$txt['ad_manage_show_towerright'] = 'Tower right';
$txt['ad_manage_show_towerleft'] = 'Tower left';
$txt['ad_manage_show_underchildren'] = 'Under Children';

//Settings Page
$txt['ad_manage_displayAdsAdmin'] = 'Disable ads for admins';
$txt['ad_manage_updateReports'] = 'Disable Reports';
$txt['ad_manage_quickDisable'] = 'Disable all ads';
$txt['ad_manage_lookLikePosts'] = 'Ads within posts look like actual posts';


//Credit Page
$txt['ad_manage_show_credits'] = '
<p>Thank you for installing my Ad management mod. A lot of hard work has gone into programming this mod,
and I hope that it works well on your board.</p>
<p>
If you wish to have further support on my ad mod,
you can post a message in the topic associated with my mod,
or you can simply go to <a href="http://www.smfads.com" target="_blank">www.smfads.com</a> for more support.</p>
<p>
This mod is free software; you may not redistribute or provide a modified version to redistribute. This mod is distributed in the hope
that it is and will be useful, but WITHOUT ANY WARRANTIES; without even any
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

Enjoy!
</p>
<br />
<hr>
<h3>Special Credit</h3>
<b>Mod Testing</b>: bigguy<br />
<b>English Translation</b>: jerm (Jeremy)
<br />
<b>Ad Seller Pro</b><br />
Ad Selling System with PayPal support! Supports Banners/textlinks. Unlimited ad locations<br />
And more!<br />
<a href="http://www.smfhacks.com/ad-seller-pro.php">http://www.smfhacks.com/ad-seller-pro.php</a>

';
/*
Translators:
You may put yourself just above me to give yourself credit for translating my mod into your language.
I ask that you keep my english translation there though.
Format: <b>Your language Translation</b>: nickname (Real name if you want)<br />

Thanks for translating!
*/



//Errors
$txt['error_ads_missing_info'] = 'You are missing info that is needed when adding ads. Please fill out a proper name and content to complete the process.';
$txt['error_ads_file_missing'] = 'LoadAds.php is missing/corrupt from your sources directory. Please make sure that it uploaded properly. The Ad mod won\'t be able to function properly. If you are having issues, please visit <a href="http://www.smfads.com">www.smfads.com</a>';
$txt['error_ads_file_missing_title'] = 'Issue with the Ad Mod';
//Permission Strings
$txt['permissionname_ad_manageperm'] = 'Enable ads';
$txt['permissiongroup_simple_ad_manage'] = 'Ad Management';
$txt['permissiongroup_ad_manage'] = 'Ad Management';
//$txt['permissiongroup_ad_manage'] = 'Ad Management';
//$txt['permissionname_ad_manageperm'] = 'Enable ads';
$txt['permissionhelp_ad_manageperm'] = 'To enable ads to appear for this membergroup, this must be checked';

//Copyright - Do not change
$txt['ads_copyright'] = '<br /><div align="center"><a href="http://www.smfads.com">SMFADS v2.3</a></div>';


$txt['ads_txt_copyright'] = 'Copyright';
$txt['ads_txt_copyrightremoval'] = 'Copyright Removal';
$txt['ads_txt_copyrightkey'] = 'Copyright Key';
$txt['ads_txt_ordercopyright'] = 'Order Copyright Removal';


$txt['ads_txt_copyremovalnote'] = 'Copyright removal removes the copyright line "SMFAds for Free Forums"';

?>