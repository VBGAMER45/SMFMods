<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

global $settings, $txt, $scripturl, $boardurl, $mbname;
global $ultimateportalSettings;

//Buttons
$txt['ultport_button_save'] = 'Save';
$txt['ultport_button_edit'] = 'Edit';
$txt['ultport_button_delete'] = 'Delete';
$txt['ultport_button_add'] = 'Add File';
$txt['ultport_button_view'] = 'Visualize';
$txt['ultport_button_editing'] = 'Editing';

//Titles Modules
$txt['down_module_title'] = 'Module';
$txt['down_module_title2'] = 'Download';
$txt['down_module_search_page_title'] = 'Search Result';
$txt['down_module_section_title'] = 'Sections';
$txt['down_module_new_file_title'] = 'Send new File';
$txt['down_module_edit_file_title'] = 'File Editing';
$txt['down_module_upload_file_title'] = 'Associated Files';
$txt['down_module_more_popular_title'] = 'Popular Downloads';
$txt['down_module_last_files_title'] = 'Latest Files';
$txt['down_module_stats_title'] = 'View Stats';
$txt['down_module_unapproved_title'] = 'Unapproved Files';

//New Form
$txt['down_module_warning'] = '<strong>Note:</strong> Your submission will not appear instantly - you must wait until the Administrator approves your file before it appears in the Downloads Module.';
$txt['down_module_file_name'] = 'File Name';
$txt['down_module_file_description'] = 'Description';
$txt['down_module_image_description'] = 'Screenshots';
$txt['down_module_file_small_description'] = 'Brief Description
								<br />Be used in the search results page.
								<br />Max characters 100 
								<br />BBC and HTML not allowed.';
$txt['down_module_file_section'] = 'Section';								
$txt['down_module_file_upload'] = '<strong>Upload File</strong>
								<br/>You can upload images';
$txt['down_module_file_upload_other'] = 'Add more';							
$txt['down_module_file_upload_max_size'] = 'Maximum file size allowed [SIZE] KB
								<br/>Image Extension: gif,jpeg,jpg
								<br/>File Extension: '. $ultimateportalSettings['download_extension_file'];
//Section 
$txt['down_module_search'] = 'Search';
$txt['down_module_actions'] = 'Operations';
$txt['down_module_new_file'] = 'Add new File';
$txt['down_attach'] = 'Attachments Files';
$txt['smf130'] = 'Select those you want to delete';

//Search form
$txt['down_search'] = 'You Searched';
$txt['down_author'] = 'Uploaded by';
$txt['down_date_created'] = 'Submitted On';
$txt['down_date_updated'] = 'Last Updated';
$txt['down_total_downloads'] = 'Total Downloads';

//Specific File 
$txt['down_file_title_downloads'] = 'Downloads';
$txt['down_file_title_section'] = 'Section';
$txt['down_file_uploaded_user'] = 'View uploads by this user';
$txt['down_file_no_attachment'] = 'No files for download.';
$txt['down_file_warning_no_approved'] = 'Warning: This file has not been approved.';
$txt['down_file_approved'] = 'Approve File';
$txt['down_file_post_link'] = 'Link to Download';
$txt['down_file_topic_link'] = 'Go to the topic of this file';

//Stats
$txt['down_top_uploader'] = 'Top 5: Users with more Uploaded Files';
$txt['down_top_sections'] = 'Top 5: Sections with more activity';

//Profile
$txt['down_profile_title'] = 'Profile of ';
$txt['down_profile_total_files'] = 'Total Files uploaded by this user: ';

//Errors or Confirmation txt
$txt['ultport_error_no_active'] = 'Error, this Module is not active';
$txt['ultport_delete_confirmation'] = 'Are you sure you want to delete the file?';
$txt['down_error_no_title'] = 'Error, the title it\'s empty.';
$txt['down_error_no_description'] = 'Error, the description it\'s empty.';
$txt['down_error_no_small_description'] = 'Error, the brief description it\'s empty.';
$txt['ultport_error_no_perms_groups'] = 'Error, does not have the required permissions to perform this action, contact with the Administrator of <em>'. $mbname .'</em>.';
$txt['down_error_no_section'] = 'Error, not created Sections.';
$txt['down_error_no_action'] = 'Error, unauthorized action.';
$txt['down_error_no_found'] = 'Error, your request not found.';
$txt['down_error_max_size'] = 'Your file is too large. The maximum size allowed for attachments is %d KB.';
$txt['down_error_canot_upload_file'] = 'It can not be uploaded. The extensions permitted are: ';
$txt['down_error_no_files_section'] = 'No files in this Section';

?>