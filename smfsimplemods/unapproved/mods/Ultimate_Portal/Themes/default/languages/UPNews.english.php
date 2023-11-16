<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/
global $settings, $txt, $scripturl, $boardurl, $mbname;

//Buttons
$txt['ultport_button_save'] = 'Save';
$txt['ultport_button_edit'] = 'Edit';
$txt['ultport_button_delete'] = 'Delete';
$txt['ultport_button_add_new'] = 'Add News';
$txt['ultport_button_view'] = 'View';

//Titles Modules
$txt['up_module_title'] = 'Module';
$txt['up_module_news_title'] = 'News';
$txt['up_module_news_edit'] = 'Edit News';
$txt['up_module_news_add'] = 'Add News';

//Edit NEWS
$txt['ultport_edit_news_title'] = 'News Title';
$txt['ultport_edit_news_section'] = '<strong>Select Section</strong>
<br />This selects the section that this news entry will be in.';

//Main
$txt['up_module_category_name'] = 'Category';
$txt['up_module_last_news'] = 'Latest News';
$txt['up_module_news_date'] = 'Date';

//Show New
$txt['up_module_news_added_portal_for'] = 'Added by [MEMBER], [DATE]';
$txt['up_module_news_updated_for'] = 'Updated by [UPDATED_MEMBER], [UPDATED_DATE]';

//Errors
$txt['ultport_error_no_active_news'] = 'Error, the news module is not enabled.';
$txt['ultport_error_no_add_news_title'] = 'Error, you have not inserted a news title.';
$txt['ultport_error_no_delete_news'] = 'Error, cannot delete the news, cannot find the news ID.';
$txt['ultport_error_no_groups_delete'] = 'Error, you do not have the required permissions to delete news, ask the Administrator <em>'. $mbname .'</em>.';
$txt['ultport_error_no_perms_groups'] = 'Error, you do not have the required permissions to perform this action, please ask your Administrator <em>'. $mbname .'</em>.';

//Delete confirmation
$txt['ultport_delete_news_confirmation'] = 'Are you sure that you want to delete this news?';

?>