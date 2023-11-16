<?php
/*
SMF Links
Version 2.5.3
by:vbgamer45
http://www.smfhacks.com
*/
global $scripturl;

//Links.template.php text strings
$txt['smflinks_indextitle'] = 'Links';
$txt['smflinks_ctitle'] = 'Title';
$txt['smflinks_description'] = 'Description';
$txt['smflinks_url'] = 'Url';
$txt['smflinks_category'] = 'Category';
$txt['smflinks_parentcategory'] =	'Parent Category'; 
$txt['smflinks_text_catnone'] =	'(None)'; 
$txt['smflinks_totallinks'] = 'Total Links';
$txt['smflinks_image'] = 'Image';
$txt['smflinks_submittedby'] = 'Submitted By:';
$txt['smflinks_options'] = 'Options';
$txt['smflinks_txtedit'] = '[Edit]';
$txt['smflinks_txtdel'] = '[Delete]';
$txt['smflinks_txtup'] = '[Up]';
$txt['smflinks_txtdown'] = '[Down]';
$txt['smflinks_txtunapprove'] = '[Un Approve]';
$txt['smflinks_txtapprove'] = '[Approve]';
$txt['smflinks_groupname'] = 'Member Group Name';
$txt['smflinks_txt_perm'] = '[Permissions]';

$txt['smflinks_txtguest'] = 'Guest';

$txt['smflinks_addsubcat'] = 'Add Sub Category';
$txt['smflinks_returnindex'] = 'Return to Links Index';
$txt['smflinks_linkssettings'] = 'Links Settings';
$txt['smflinks_linksconfig'] = 'Links Configuration';
$txt['smflinks_linkssettings_des'] = 'Sets the settings for the links system';
$txt['smflinks_managecats'] =  'Manage Categories';
$txt['smflinks_anagecats_des'] = 'Allows you create groups for links';
$txt['smflinks_linkspanel'] = 'Links Directory Panel';
$txt['smflinks_approvepanel'] = 'Approve Links Panel';
$txt['smflinks_catpermlist'] = 'Category Permission List';

$txt['smflinks_text_catperm'] = 'Category Permission';
$txt['smflinks_text_addperm'] = 'Add Permission';
$txt['smflinks_categories'] = 'Categories:';
$txt['smflinks_rating'] = 'Rating: ';
$txt['smflinks_hits'] = 'Hits: ';
$txt['smflinks_submittedby'] =  'Submitted by:';
$txt['smflinks_date'] = 'Added on ';

//Category listing strings
$txt['smflinks_crating'] = 'Rating';
$txt['smflinks_chits'] = 'Hits';
$txt['smflinks_cusername'] =  'Member Name';
$txt['smflinks_cdate'] = 'Date';
$txt['smflinks_alexa'] = 'Alexa';
$txt['smflinks_pagerank'] = 'Pagerank';

$txt['smflinks_topfivevisited'] = 'Top Five Visited Links';
$txt['smflinks_topfiverated'] = 'Top Five Rated Links';


$txt['smflinks_stats'] =  'Stats';

$txt['smflinks_thereare'] =  'There are ';

$txt['smflinks_catand'] = ' categories and ';
$txt['smflinks_linkssystem'] = ' links in the links system.';


$txt['smflinks_waitingapproval'] =  ' links waiting for approval.';


$txt['smflinks_warndel2'] = 'Warning this WILL DELETE this category and ALL Links that category contains...';
$txt['smflinks_warndel'] = 'Warning this WILL DELETE this link and you can not get it back...';
$txt['smflinks_nofirstcat'] = 'You need create a category first!';

$txt['smflinks_nolinks'] = 'There are no links in this category.';
$txt['smflinks_nolinks2'] = 'No links in ';

$txt['smflinks_pages'] = 'Pages: ';
$txt['smflinks_returnindex'] = 'Return to Links Index';
$txt['smflinks_bbcallowed'] = 'BBC Codes allowed for the description.';

$txt['smflinks_setlinksperpage'] = 'Links per page';

$txt['smflinks_setshowtoprate'] = 'Show Toprated Index';
$txt['smflinks_setshowmostvisited'] = 'Show Most Visited Index';
$txt['smflinks_setshowstats'] = 'Show Stats Index';
$txt['smflinks_set_count_child'] = 'Counts child categories link totals. <br />(Uses more queries)';
$txt['smflinks_setallowbbc'] = 'Allow BBC for links description';
$txt['smflinks_setgetpr'] = 'Get Pagerank information';
$txt['smflinks_setgetalexa'] = 'Get Alexa information';

//Link Display Settings
$txt['smflinks_linkdisplay'] = 'Link Display Settings';
$txt['smflinks_disp_description'] = 'Show Description';
$txt['smflinks_disp_hits'] = 'Show Hits';
$txt['smflinks_disp_rating'] = 'Show Rating';
$txt['smflinks_disp_membername'] = 'Show Member Name';
$txt['smflinks_disp_date'] = 'Show Date';
$txt['smflinks_disp_alexa'] = 'Show Alexa';
$txt['smflinks_disp_pagerank'] = 'Show Pagerank';

$txt['smflinks_settings_save'] = 'Save Settings';

//Links.php text strings
$txt['smflinks_title'] = ' - Links';
$txt['smflinks_addcat'] = 'Add Category';
$txt['smflinks_editcat'] = 'Edit Category';
$txt['smflinks_deltcat'] = 'Delete Category';
$txt['smflinks_addlink'] = 'Add Link';
$txt['smflinks_editlink'] = 'Edit Link';
$txt['smflinks_dellink'] = 'Delete Link';
$txt['smflinks_approvelinks'] = 'Approve Links';
$txt['smflinks_settings'] = 'Settings';



$txt['smflinks_nocattitle'] = 'You need to enter a category title';
$txt['smflinks_nolinktitle'] = 'You need to enter a link title';
$txt['smflinks_nolinkurl'] = 'You need to enter a link url';
$txt['smflinks_nolinkselected'] = 'No link selected';
$txt['smflinks_nocatselected'] = 'No category selected';
$txt['smflinks_nocatabove'] = 'There is no category above the current one.';
$txt['smflinks_nocatbelow'] = 'There is no category below the current one.';
$txt['smflinks_linkneedsapproval'] = 'Note: Your link needs to be approved before it is visible.';
$txt['smflinks_alreadyrated'] = 'You already rated this link';

$txt['smflinks_linkexists'] = 'The link already exists in category %c with link title of %l';


$txt['smflinks_perm_no_view'] = 'You are not allowed to view this category.';
$txt['smflinks_perm_no_add'] = 'You are not allowed to add a link in this category.';
$txt['smflinks_perm_no_edit'] = 'You are not allowed to edit that link in this category.';
$txt['smflinks_perm_no_delete'] = 'You are not allowed to delete that link in this category.';
$txt['smflinks_perm_no_ratelink'] = 'You are not allowed to rate that link in this category.';


$txt['smflinks_perm_link_no_edit'] = 'You are not allowed to edit this link.';
$txt['smflinks_perm_link_no_delete'] = 'You are not allowed to delete this link.';


$txt['smflinks_perm_view'] = 'View';
$txt['smflinks_perm_add'] = 'Add Link';
$txt['smflinks_perm_edit'] = 'Edit own Link';
$txt['smflinks_perm_delete'] = 'Delete own Link';


$txt['smflinks_permerr_permexist'] = 'A permission already exists for this group and category please delete it first.';

$txt['smflinks_perm_allowed'] = 'Allowed';
$txt['smflinks_perm_denied'] = 'Denied';

$txt['smflinks_sub_cats'] = 'Sub Categories: ';

$txt['smflinks_err_linkmuststart'] = 'Link must start with either http:// or https://';


// Begin SMF Links Text Strings
$txt['smflinks_menu'] = 'Links';
$txt['smflinks_admin'] = 'Links Configuration';
$txt['smflinks_linkssettings'] = 'Links Settings';
$txt['smflinks_managecats'] =  'Manage Categories';
$txt['smflinks_catpermlist'] = 'Category Permission List';
$txt['smflinks_approvelinks'] = 'Approve Links';


$txt['permissiongroup_simple_smflinks'] = 'SMF Links';
$txt['permissiongroup_smflinks'] = 'SMF Links';
$txt['permissionname_view_smflinks'] = 'View Links Page';
$txt['permissionhelp_view_smflinks'] = 'Sets if the user can view the links page.';
$txt['cannot_view_smflinks'] = 'You can not view the links page.';
$txt['permissionname_add_links'] = 'Add Links';
$txt['permissionhelp_add_links'] = 'If the user is allowed to submit links';
$txt['cannot_add_links'] = 'You can not add links.';
$txt['permissionname_edit_links'] = 'Edit Links';
$txt['permissionhelp_edit_links'] = 'If the user is allowed to edit links';
$txt['cannot_edit_links'] = 'You can not edit links.';
$txt['permissionname_delete_links'] = 'Delete Links';
$txt['permissionhelp_delete_links'] = 'If the user is allowed to delete links';
$txt['cannot_delete_links'] = 'You can not delete links.';
$txt['permissionname_approve_links'] = 'Approve Links';
$txt['permissionhelp_approve_links'] = 'If the user is allowed to approve links';
$txt['cannot_approve_links'] = 'You can not approve links.';
$txt['permissionname_links_auto_approve'] = 'Links Auto Approved';
$txt['permissionhelp_links_auto_approve'] = 'If the users links are auto approved when submitted.';
$txt['permissionname_rate_links'] = 'Rate Links';
$txt['permissionhelp_rate_links'] = 'If the user is allowed to rate links';
$txt['cannot_rate_links'] = 'You are not allowed to rate links.';
$txt['permissionname_links_manage_cat'] = 'Manage Categories';
$txt['permissionhelp_links_manage_cat'] = 'User can add/remove/edit/reorder categories';
$txt['cannot_links_manage_cat'] = 'You are not allowed to manage categories.';

$txt['permissionname_delete_links_own'] = 'Own Link';
$txt['permissionname_delete_links_any'] = 'Any Link';
$txt['permissionname_edit_links_own'] = 'Own Link';
$txt['permissionname_edit_links_any'] = 'Any Link';
//END SMF Links Strings


$txt['whoall_links'] = 'Viewing the <a href="' . $scripturl . '?action=links">' . $txt['smflinks_menu'] . '</a>';