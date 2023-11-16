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
$txt['ultport_button_add'] = 'Add';
$txt['ultport_button_view'] = 'View';
$txt['ultport_read_more'] = 'Read More';

//Titles Modules
$txt['up_module_title'] = 'Module';
$txt['up_module_ipage_title'] = 'Internal Page';
$txt['up_ipage_add_html'] = 'Add HTML Page';
$txt['up_ipage_add_bbc'] = 'Add BBC Page';
$txt['up_ipage_date_created'] = 'Date Created:';
$txt['up_ipage_member'] = 'Added By:';
$txt['up_ipage_date_updated'] = 'Last Modified:';
$txt['up_ipage_member_updated'] = 'Modified By:';
//Please NOT REMOVE THE VARIABLE "IPAGE_URL"
$txt['up_ipage_disabled_any_ipage'] = 'There are some pages that are disabled, to view and enable either, click <strong><a href="IPAGE_URL">HERE</a></strong>';
$txt['up_ipage_disabled_any_ipage_title'] = 'Disable Internal Pages';

//Add Form
$txt['up_ipage_add_title'] = 'Add Internal Page';
$txt['up_ipage_edit_title'] = 'Edit Internal Page';
$txt['up_ipage_title'] = 'Title';
$txt['ipage_column_left'] = '<strong>Enable the left column for this internal page?</strong>
						<br/>So the left column will be displayed when this page is loaded.';
$txt['ipage_column_right'] = '<strong>Enable the right column for this internal page?</strong>
						<br/>So, the right column will be displayed when this page is loaded.';
$txt['up_ipage_content'] = 'Content';						
$txt['up_ipage_perms'] = 'Who can view this internal page?';
$txt['up_ipage_active'] = 'Enable this internal page';
$txt['up_ipage_sticky'] = 'Sticky this internal page';
$txt['membergroups_guests'] = 'Guests';
$txt['membergroups_members'] = 'Regular Members';

//Errors
$txt['ultport_error_no_active'] = 'Error, this module is not enabled.';
$txt['ultport_error_no_add_ipage_title'] = 'Error, you have not added a page title.';
$txt['ultport_error_no_delete_ippage'] = 'Error, could not be deleted, cannot find the ID of the internal page.';
$txt['ultport_error_no_perms_groups'] = 'Error, you do not have the required permissions to perform this action. Please contact the Administrator <em>'. $mbname .'</em>.';
$txt['ultport_error_no_view'] = 'Error, cannot see this internal page. It might not be active, or you do not have permissions to view it.';
$txt['ultport_error_no_action'] = 'Error, action not allowed.';

//Delete confirmation
$txt['ultport_delete_confirmation'] = 'Are you sure you want to delete this?';

?>