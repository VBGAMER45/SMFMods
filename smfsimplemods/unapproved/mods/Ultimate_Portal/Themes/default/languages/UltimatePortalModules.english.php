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
$txt['ultport_button_select_all'] = 'Select All';

//Default Text
$txt['ultport_no_rows'] = 'Although not added any rows.';
$txt['ultport_no_rows_title'] = 'Information.';

//Titles Modules
$txt['ultport_admin_module_title'] = 'Ultimate Portal Modules';
$txt['ultport_admin_module_title2'] = 'Module';
$txt['ultport_enablemodules_title'] = 'Enable/Disable Modules';
$txt['ultport_admin_user_posts_title'] = 'Greats Posts';
$txt['ultport_admin_news_title'] = 'News';
$txt['ultport_admin_board_news_title'] = 'Forum News';
$txt['ultport_admin_announcements_title'] = 'Global Announcement';

//Modules Description
$txt['ultport_admin_user_posts_descrip'] = 'Area will be doing the General Configuration Module Greats Posts';
$txt['ultport_admin_board_news_descrip'] = 'Area will be doing the General Module Configuration Forum News';
$txt['ultport_admin_news_descrip'] = 'Area will be doing the General Configuration Module News';
$txt['ultport_admin_news_section_descrip'] = 'Area you can manage the news section of the module, you can: Add, delete, edit, etc...';
$txt['ultport_admin_news_descrip2'] = 'Area where you can manage the news added to the News Module, you can: Add, delete, edit, etc...';
$txt['ultport_admin_announcements_descrip'] = 'Area where you can Manage Global Announcement that appears in the Portal';

//Section Great Posts
$txt['ultport_admin_user_posts_main'] = 'General Configuration';
$txt['ultport_admin_up_enable'] = 'Enable Greats Posts Module';
$txt['ultport_admin_up_limit'] = '<strong>Number of posts per page?</strong>
							<br />
							Here you set the number of posts per page to display within the block (home page).

							Default 10.';					
$txt['ultport_admin_up_fields'] = '<strong>Select the fields you will have the Form Module</strong>
							<br />
							Here provide that want fields to be loaded at the time of Add a new post, they will introduce mandatory.
							They will also be the fields that will be displayed in the module.
							<br />
							TITLE fields and link to the topic are default functions cann\t be disabled.';					
$txt['ultport_admin_up_cover_save_host'] = '<strong>Cover art the contribution will be saved on the host?</strong>
							<br />
							In this way you will have the possibility of all the covers of the posts are stored on your own hosting.
							<br />
							If you object to being stored in the hosting, uncheck this option.';							
$txt['ultport_admin_up_internal_page_presentation'] = '<strong>Present Contribution in an internal page?</strong>
							<br />
							Here you will establish whether the contribution will be displayed on an internal page (click the button to give<strong>Visualize</strong>), selected this option, input will be displayed on
							a special internal page for each contribution, if you choose this option click the button to give<strong>Visualize</strong>, 
							go directly to the link or links placed on the Contribution';					
$txt['ultport_admin_up_presentation'] = '<strong>Present Form of Contribution Cover art?</strong>
							<br />You have 2 (two) options: Normal, Advanced.
							<ul>
								<li><strong>Normal:</strong> Show the original image link, remain unchanged.</li>
								<li><strong>Advanzed:</strong> Show a changed image, thus the presentation of the cover of Contribution
									be improved. See the example for how the end result will be the cover art to display.

									This option is only active if the option is enabled add <strong>Description to topic</strong>.
									<br />By choosing this option you can write your own Watermark (Watermark) for its covers.
								</li>
							</ul>';
$txt['ultport_admin_up_normal'] = 'Normal';
$txt['ultport_admin_up_advanced'] = 'Advanced';
$txt['ultport_admin_up_cover_watermark'] = '<strong>Write to visualize Watermark</strong>
							<br />This may establish whether the image will have a text as a watermark (watermark), that identifies the image as the site itself.
							<br />Only if the image is stored in the hosting, the watermark is chosen, will be written by its cover, if the picture is saved in the hosting,
only simulate a watermark effect, without that letter by its cover.
							<br />Leave the field empty if you don\t want the covers with Watermark.
							<br /><strong>Example:</strong> <strong><a href="http://www.smfsimple.com/img/ultimateportal/user-posts/watermark-example.jpg" target="_blank">Sample Image</a></strong>.';
$txt['ultport_admin_up_header_show'] = 'Viewing posts in the Header';							
$txt['ultport_admin_up_social_bookmarks'] = '<strong>Allow Input Share (Social Bookmarks)</strong>
							<br />The same will be displayed both in the block module, and within the module\s internal website.';							

//User Posts Fields 							
$txt['ultport_admin_up_field_title'] = 'Title';
$txt['ultport_admin_up_field_cover'] = 'Cover';
$txt['ultport_admin_up_field_description'] = 'Description';
$txt['ultport_admin_up_field_link_topic'] = 'Link to Topic';
$txt['ultport_admin_up_field_topic_author'] = 'Topic Autor';
$txt['ultport_admin_up_field_member_use_module'] = 'Show who add the Contribution in Module?';
$txt['ultport_admin_up_field_member_updated_module'] = 'Show who updates the Contribution in Module?';
$txt['ultport_admin_up_field_type_posts'] = 'Add <strong> Selector posts <em> Type </ em> </ strong> the module?';
$txt['ultport_admin_up_field_add_language'] = 'Add <em> <strong> Selector Languages </ em> </ strong> the module?';
$txt['ultport_admin_up_extra_field_title'] = 'Extras Fields Manager';
$txt['ultport_admin_up_extra_field_description'] = 'Area where you can add, modify, delete, <strong> fields Contribution Type </ strong> and <strong> Language Selector </ strong> Module <strong> Members posts </ strong>';
$txt['ultport_admin_extra_field_id'] = 'Id';
$txt['ultport_admin_extra_field_icon'] = 'Icon';
$txt['ultport_admin_extra_field_title'] = 'Description';
$txt['ultport_admin_add_extra_field_icon'] = '<strong>Icon</strong>
										<br />This allows you to set the icon or image that corresponds to the option chosen by the user. This Field is required.
	
				Icon size 32 x 32';
$txt['ultport_admin_add_extra_field_title'] = '<strong>Description</strong>
										<br />The description that will take the field for the type of field you are adding.';
$txt['ultport_admin_extra_field_selectfield'] = '<strong>Select Field rightful</strong>
										<br />If the Contribution Type Selector or Selector Language';
$txt['ultport_admin_extra_field_type'] = 'Contribution Type Selector';
$txt['ultport_admin_extra_field_lang'] = 'Language Selector';
$txt['ultport_admin_extra_field_action'] = 'Actions';
$txt['ultport_no_activate_extra_field'] = 'Still haven\'t selected the option to add <strong> Type of Contribution </ strong> or <strong> Language </ strong> from the general configuration of mod.';

//Perms Title Redirect
$txt['user_posts_perms'] = 'Permissions Management';

//Section News
$txt['ultport_admin_news_main'] = 'General Configuration';
$txt['ultport_admin_news_main_title'] = 'Preferences';
$txt['ultport_admin_news_section_title'] = 'Sections Manager';
$txt['ultport_admin_admin_news_title'] = 'News Manager';
$txt['ultport_admin_news_enable'] = 'Activate News Module?';
$txt['ultport_admin_news_limit'] = '<strong>Limit Display Per Page</strong>
							<br />
							Here you set the number of news pages, to display within the block (home page, by default 10 News Page)';
					
$txt['ultport_global_annoucements'] = '<strong>Global Announcement</strong> 
							<br/>Appear in the Portal (Header), in the main block, unless you want to activate it, just leave it empty.';
$txt['ultport_admin_news_sect_id'] = 'Id';
$txt['ultport_admin_news_sect_icon'] = 'Icon';
$txt['ultport_admin_news_sect_title'] = 'Title';
$txt['ultport_admin_news_sect_position'] = 'Position';
$txt['ultport_admin_news_sect_action'] = 'Actions';
$txt['ultport_admin_add_sect_title'] = 'Adding New Section';
$txt['ultport_admin_edit_sect_title'] = 'Edit Section';
$txt['ultport_admin_news_add_sect_icon'] = '<strong>Icon</strong>
										<br />You can set the icon will be in the news section. <img style="float:right" alt="'.$txt['ultport_admin_add_sect_title'].'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/news-icon.png" width="50" height="50" />
										<br />Leave empty if you wish to use the icon by default, automatically resizes the image has <br /> (35 x 35)';
$txt['ultport_admin_news_add_sect_title'] = '<strong>Title</strong>
										<br />This will add the title will be in the News section.';
$txt['ultport_admin_news_add_sect_position'] = '<strong>Position</strong>
										<br />Sets the position within the module that will take the section to be created. By default brings the last position.';
$txt['ultport_admin_add_news_title'] = 'News Title';
$txt['ultport_admin_add_news_sect_title'] = 'Section Title';
$txt['ultport_admin_add_news_title2'] = 'Adding News';
$txt['ultport_admin_add_news_section'] = '<strong>Select Section</strong>
										<br />In this way, determine the section to which own the news.';
$txt['ultport_admin_edit_news_title'] = 'News Editing';										

//Section Board News
$txt['ultport_admin_board_news_main'] = 'General Configuration';
$txt['ultport_admin_bn_main_title'] = 'Preferences';
$txt['ultport_admin_bn_limit'] = '<strong>Display Limit</strong>

							<br />
							Here you set the number of news to display within the block
							<br />(Homepage)';

$txt['ultport_admin_bn_lenght'] = '<strong>Maximum number of characters</strong>
							<br />
							This sets the maximum number of characters that will have every news displayed. To leave empty not to set limits.';
$txt['ultport_admin_bn_view'] = '<strong>Select Forums</strong>
							<br />
							Select the forums you want to appear in the block.
							<br />
							To select multiple forums press CTRL + Click on the Forums';
$txt['ultport_admin_bn_select_all'] = 'All Forums';

//Section Download Module - Titles | Tabs
$txt['up_download_title'] = 'Downloads';
$txt['up_down_settings_tab'] = 'General Settings';
$txt['up_down_section_tab'] = 'Sections Manager';

//Section Download Module - Description
$txt['up_down_settings_descrip'] = 'Configuration Area General Downloads Module';
$txt['up_down_section_description'] = 'Area to Add, Delete, Edit, which will have sections Downloads Module';

//Section Download Module - Gral Settings
$txt['up_download_enable'] = 'Enable Module Downloads?';
$txt['up_down_file_limit_page'] = 'Limit Files to display per page (default 10)';
$txt['up_down_file_max_size'] = 'Maximum upload file size in kilobytes (0 = No Limit). 
								<br />Default = 2048 (2 Mb)';
$txt['up_down_extension_file'] = '<strong>Extension Allowed</strong>
								<br/>Sets the extensions allowed to upload the files, you put them with commas.

								<br/><strong>Defaults:</strong> zip, tar.gz';	
							
$txt['up_down_enable_approved_file'] = '<strong>Select whether the module uploads will be approved or not, for viewing.</strong>

								<br /><span style="color:red">It can only be approved by the Admins
</span>';
$txt['up_down_no_approved_file'] = 'It doesn\'t require approval of the Administrator';
$txt['up_down_yes_approved_file'] = 'If require approval of the Administrator';
$txt['up_down_board_post_file_disabled'] = 'Deactivate';
$txt['up_down_enable_send_pm_approved'] = 'Send an PM, the user who was approved for Uploaded File?';
$txt['up_down_pm_id_member'] = '<strong>User ID that will appear as Issuer of PM</strong>
							<br />If not set the user ID, which will send the MP who approved it.';
$txt['up_down_pm_subject'] = '<strong>Subject or Theme PM</strong>
							<br />You can use the variable {FILENAME}, in this way may also set up in case the name of the file Approved.
							<br /><br /><strong>Example:</strong> The file {FILENAME} has been approved.';							
$txt['up_down_pm_body'] = '<strong>Message Body</strong>
							<br />BBCode accepted, HTML NOT
							<br/>You can use the variable <strong> {FILENAME} </ strong> to the body of the message appears
File link was Approved.
							<br/><strong>Example:</strong> 
							<br/>Congratulations your file [b]{FILENAME}[/b] has been approved by our staff <strong><em>'. $mbname .'</em></strong>';

//Section Download Module - Sections Settings
$txt['up_down_sect_title'] = 'Title';
$txt['up_down_sect_icon'] = 'Icon';
$txt['up_down_sect_perms'] = 'Groups Allowed to view section';
$txt['up_down_sect_board'] = 'Section Forum.';
$txt['up_down_sect_no_board'] = 'Off';
$txt['up_down_sect_total_files'] = 'Number of Files Section';
$txt['up_down_sect_no_rows'] = 'Not yet added sections.';

//Add section
$txt['up_down_manage_sect_title'] = '<strong>Section Title
</strong>';
$txt['up_down_manage_sect_icon'] = '<strong>Icon</strong>
								<br />The image will represent the Section to display.
								<br />Automatic resizing to 30x30.
								<br />Leave empty to use the default image';
$txt['up_down_manage_sect_perms'] = '<strong>Groups Allowed to view section</strong>
								<br />
								This may privatize sections to display only what a particular group.';
$txt['up_down_board_post_file'] = '<strong>Select the forum where the creation of a post with the uploaded file</strong>
								<br />This may be published (a look that was approved) automatically uploaded file to the module, the Forum selected.
								<br />If you don\t want to create any topic of that section just Disable it, and this section shall not create any posts.';								
$txt['up_down_manage_sect_description'] = '<strong>Section Description</strong>
								<br />A brief description for the section created. Only accepts BBCode, HTML NOT';

//Internal Page Module
$txt['ipage_title'] = 'Internal Pages';
$txt['ipage_settings_title'] = 'Settings';
$txt['ipage_settings_description'] = 'General Configuration Module internal pages.';
$txt['ipage_enable'] = 'Enable Module Internal Pages?';
$txt['ipage_limit'] = 'Quantity of internal pages to display per page.';					
$txt['ipage_active_columns'] = '<strong>Turn Left Column | Right</strong>
					<br/>This way you can set if you want to be displayed in columns (Right | Left)
to enter the Home Module Internal Pages';

$txt['ipage_social_bookmarks'] = 'Allow Input Share (Social Bookmarks).';

//Section Affiliates
$txt['ultport_admin_affiliates_title'] = 'Affiliates';
$txt['ultport_admin_affiliates_main'] = 'General Configuration';
$txt['ultport_admin_affiliates_descrip'] = 'Area will be doing the General Module Configuration Partners Forum';
$txt['ultport_admin_aff_main_title'] = 'Configuration';
$txt['ultport_admin_aff_description'] = 'Area where you can add, modify and delete affiliates of your site';
$txt['ultport_admin_aff_limit'] = '<strong>Number of Banner to Show</strong>';
$txt['ultport_admin_aff_limit_error'] = '<strong>You exceeded the number of affiliates, change the limit or remove any affiliate if you want to add more</strong>';
$txt['ultport_admin_aff_admin_title'] = 'Affiliate Admin';
$txt['ultport_admin_aff_direction'] = '<strong>Direction of movement toward:</strong>';
$txt['ultport_admin_aff_direction_up'] = 'Up';
$txt['ultport_admin_aff_direction_down'] = 'Down';
$txt['ultport_admin_aff_direction_noMove'] = 'No Movement';
$txt['ultport_admin_aff_target'] = '<strong>Destiny:</strong>';
$txt['ultport_admin_aff_target_self'] = 'Same Window';
$txt['ultport_admin_aff_target_blank'] = 'New Window';
$txt['ultport_admin_aff_scrollDelay'] = '<strong>ScrollDelay</strong>
							<br />
							Defines the speed of movement of the images. The lower numbers, greater speed
							<br />';
$txt['ultport_admin_aff_add'] = 'Add minibanner';
$txt['ultport_error_no_add_affiliates_web'] = 'Error, haven\t set the name of the web.';
$txt['ultport_admin_add_title'] = 'Add Affiliates';
$txt['ultport_admin_add_aff_title'] = 'Site Name';
$txt['ultport_admin_add_aff_url'] = '<strong>Web URL</strong>';
$txt['ultport_admin_add_aff_minibanner'] = 'Minibanner';
$txt['ultport_admin_add_aff_urlbanner'] = '<strong>Address minibanner Image</strong>';
$txt['ultport_admin_add_aff_cant'] = 'Number';
$txt['ultport_admin_add_aff_id'] = 'ID';
$txt['ultport_admin_add_aff_actions'] = 'Actions';
$txt['ultport_admin_add_aff_alt'] = '<strong>Alternative Text</strong>
							<br />
							Brief description of the page
							<br />';

//Section About Us Module - Titles | Tabs
$txt['up_about_title'] = 'About Us';
$txt['up_about_settings_tab'] = 'General Settings';
$txt['up_about_enable'] = 'Enable Module About Us';
$txt['up_about_show_nick'] = '<strong>Show User Nick</strong>
							<br/>Enabled by default, with no possibility of change.';
$txt['up_about_show_group'] = '<strong>Show the group the user belongs</strong>
							<br/>Enabled by default, with no possibility of change.';
$txt['up_about_show_date_registered'] = '<strong>Show Date User registration</strong>
							<br/>Enabled by default.';									
$txt['up_about_show_mail'] = '<strong>Show User Email</strong>
							<br/>Enabled by default.';									
$txt['up_about_show_pm'] = '<strong>Show PM icon in order to send a Private Message</strong>
							<br/>Enabled by default.';												
$txt['up_about_extrainfo_title'] = '<strong>Title of the extra information to add</strong>
							<br/>In this way you will have the ability to add a title to additional information that will be displayed.';																										
$txt['up_about_extra_info'] = '<strong>Add Extra Information to Page</strong>
							<br/>In this way you will have the opportunity to add some extra information to the page of Module <strong> About Us </ strong>.';																									
$txt['up_about_group_view'] = '<strong>Select the Show Groups</strong>
							<br/>In this way you will have the ability to view only those groups that want to appear in the Module </ strong>.';																										

//Section About Us Module - Description

$txt['up_aboutus_settings_descrip'] = 'General Settings Area Module <strong> About Us </ strong>';

//Section FAQ Module - Titles | Tabs
$txt['up_faq_title'] = 'FAQ';
$txt['up_faq_config'] = 'General Settings';
$txt['up_faq_description'] = 'General Area Module configuration FAQs';
$txt['up_faq_enable'] = 'Enable FAQ Module?';
$txt['up_faq_title_page'] = 'Title will be in the Home Module
							<br/>Doesn\'t accept BBCode';
$txt['up_faq_small_description'] = 'Brief Description of what is being presented in the module <strong> Page FAQ </ strong>
							<br/>Doesn\'t accept BBCode';
$txt['up_faq_perms'] = 'Set Permissions Add, Edit, Delete';

//Errors
$txt['ultport_error_no_perm'] = 'Error, Doesn\'t have the privileges to perform this action.';
$txt['ultport_error_no_add_title'] = 'Error, heven\'t the title.';
$txt['ultport_error_no_add_icon'] = 'Error, haven\'t set the Icon.';
$txt['ultport_error_no_delete'] = 'Error, haven\'t been eliminated, failed to receive the ID.';
$txt['ultport_error_no_add_news_section_title'] = 'Error, haven\'t set the title of Section.';
$txt['ultport_error_no_delete_section'] = 'Error, Failed to delete the section doesn\'t designate the ID of the section to be removed.';
$txt['ultport_error_no_add_news_title'] = 'Error, haven\'t set the title of the news.';
$txt['ultport_error_no_delete_news'] = 'Error, Failed to delete the news, don\'t designate the ID of the Notice to be eliminated.';

//Delete confirmation
$txt['ultport_delete_section_confirmation'] = 'Are you sure you want to delete, eliminating Section, also eliminates the news that it has.';
$txt['ultport_delete_download_section_confirmation'] = 'Are you sure you want to delete, eliminating Section, also removes the files that it has.';
$txt['ultport_delete_news_confirmation'] = 'Are you sure you want to delete the news?';
$txt['ultport_delete_confirmation'] = 'Are you sure you want to delete?';

?>