<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/
global $settings, $txt, $scripturl, $boardurl, $ultimateportalSettings;
global $boarddir, $user_info;

//Buttons
$txt['ultport_button_save'] = 'Save';
$txt['ultport_button_edit'] = 'Edit';
$txt['ultport_button_delete'] = 'Delete';
$txt['ultport_button_add'] = 'Add';
$txt['ultport_button_preview'] = 'Preview';
$txt['ultport_button_permission'] = 'Permissions';
$txt['ultport_button_select_all'] = 'Select all';
$txt['ultport_button_go_back'] = 'Return';

//Main CP
$txt['main_description'] = 'Welcome, <strong>'. $user_info['username'] .'</strong>!. This is your"<strong>Administration Center of Ultimate Portal</strong>". <br />
Here you can modify:
	<ul>
		<li>General Portal Settings</li>
		<li>Managing blocks</li>
		<li>Assign Permissions</li> 
		<li>Managing Modules</li>
		<li>Managing Languages Files</li>
		<li>etc..</li>
	</ul>	
If you have any problems, please check the <strong> website the online manual.</strong>. If that information is not for you, you can visit for help about your problem.';
$txt['main_blocks_title'] = 'Managing Blocks';
$txt['main_blocks_description'] = 'Area where you can administer the Portal blocks, change position, activity, assign permissions, delete, etc...';
$txt['main_user_posts_title'] = 'Post great Module';
$txt['main_user_posts_description'] = 'Ultimate Module Default Portal, you can see in a well presentable the posts great that make your users (especially for forums Share Download Links)';
$txt['main_news_title'] = 'News Module';
$txt['main_news_description'] = 'Ultimate Portal Module Default , to present Web News';
$txt['main_bnews_title'] = 'Forum News Module';
$txt['main_bnews_description'] = 'Ultimate Portal Module Default , to present the selected Forum Latest Topics';
$txt['main_download_title'] = 'Downloads Module';
$txt['main_download_description'] = 'Ultimate Portal Module Default , this module will have a complete manager Uploading or downloading files from the highly configurable Panel Module.';
$txt['main_ipage_title'] = 'Internal Pages Module';
$txt['main_ipage_description'] = 'Ultimate Portal Module Default , this module can manage the internal pages that will have the Ultimate Portal.';
$txt['main_affiliates_title'] = 'Affiliate Module';
$txt['main_affiliates_description'] = 'Ultimate Portal Module Default , this module can manage the Partners / Friends webs.';
$txt['main_about_title'] = 'About Us Module?';
$txt['main_about_description'] = 'Ultimate Portal Module Default , with this module you can display the equipment or Staff of your Web page, as well as Extra Information.';
$txt['main_faq_title'] = 'FAQ Module';
$txt['main_faq_description'] = 'Ultimate Portal Module Default , with this module you can build your own FAQ page for your Web.';
$txt['main_manual_title'] = 'On line Manual';
$txt['main_manual_description'] = 'With this manual, you will know how to handle the Ultimate Portal, detail each part of the Ultimate Portal is explained, so you can handle the Ultimate Portal of the best possible way.';
$txt['main_credits_title'] = 'Credits';
$txt['main_credits_description'] = '<strong><a href="http://www.smfsimple.com">SMFSimple</a></strong> want to thank everyone who helped make <strong>Ultimate Portal</strong> what it is today, giving form and directing our project, through the thick and thin. I wouldn\'t have been possible without you. This includes our users - Thanks for installing and using our software, giving us valuable information, bug reports, and opinions.
	<br /><br /><strong>Founding Father of Ultimate Portal and Project Manager:</strong> Victor "vicram10" Ramirez
	<br /><br /><strong>Staff:</strong> Vicram, Lean, 4kstore & Distante!
	<br /><br /><strong>Special Thanks to</strong> Nino_16, royalduke, Suki, Liam, Near, Frony & Maliante!
	<br /><br />
	<strong>Special Thanks for the icons used to: </strong> 
	<a href="http://www.famfamfam.com/lab/icons/silk/" target="_blank">FamFamFam</a> | <a href="http://dryicons.com" target="_blank">DryIcons</a> | <a href="http://iconfinder.com" target="_blank">Iconfinder</a>
	<br /><br />
	And to all those who have helped us and we have not appointed, <strong>Thank you very much</strong>';

//Confirmation
$txt['ultport_delete_confirmation'] = 'Are you sure you want to delete this?';

//Titles
$txt['ultport_admin_category_title'] = 'Ultimate Portal CP';
$txt['ultport_admin_title'] = 'Ultimate Portal - Admin Center';
$txt['ultport_preferences_title'] = 'Preferences';
$txt['ultport_blocks_title'] = 'Managing Modules';

//Preferences
$txt['ultport_admin_preferences_title'] = 'Admin Center';
$txt['ultport_admin_preferences_description'] = 'Area in which it may fully configure the Ultimate Portal';
$txt['ultport_admin_main_title'] = 'Information';

//Admin Gral Settings - Sections Titles
$txt['ultport_admin_gral_settings_sect_principal'] = 'Main';
$txt['ultport_admin_gral_settings_sect_view_portal'] = 'View the Portal';
$txt['ultport_admin_gral_settings_sect_view_forum'] = 'View the Forum';

//Admin Gral Settings
$txt['ultport_admin_gral_settings_title'] = 'General Settings';
$txt['ultport_admin_gral_settings_description'] = 'Area in which it is the general settings of the Ultimate Portal';
$txt['ultport_admin_gral_settings_portal_enable'] = '<span style="color:red"><strong>Activate Ultimate Portal?</strong></span>
												<br />
												Here you can activate / deactivate the portal at any time.';
$txt['ultport_admin_gral_settings_portal_title'] = '<strong>Name of Portal</strong>
												<br />
												This will establish the name that will have the portal regardless of who has the Forum Name';
$txt['ultport_admin_gral_settings_favicons'] = '<strong>Activate FavIcons?</strong>
												<br />
												Here you can enable / disable default favicons. <img alt="favicons" src="'.$settings['default_images_url'].'/ultimate-portal/favicon.png" />';
$txt['ultport_admin_gral_settings_use_curve'] = '<strong>Use a Theme based on the theme Curve (Curve Variation) or is the Theme Curve?</strong>
												<br/>
												This allows you to display the blocks correctly. This option is enabled by default.
												If not using the Curve theme or a theme based on Curve, do not select this option, thus improving the performance of the visualization of the blocks.';

//Admin Gral Settings - Section: View Portal
$txt['ultport_admin_gral_settings_height_col_left'] = '<strong>Left Column Width</strong>
												<br />
												This sets the width will be in the left column of the portal.
      <br />
												Percentage value.<strong>Example:</strong> 20%';
$txt['ultport_admin_gral_settings_height_col_center'] = '<strong>Central Column Width</strong>
												<br />
												This sets the width will be in the central column of the portal.  
<br />
												Percentage value. <strong>Example:</strong> 60%';
$txt['ultport_admin_gral_settings_height_col_right'] = '<strong>Right Column Width</strong>
												<br />
												This sets the width will be in the right column of the portal.
      <br />
												Percentage value. <strong>Example:</strong> 20%';
$txt['ultport_admin_gral_settings_enable_portal_col_left'] = '<strong>Turn left column?</strong>
												<br />
												Enabled by default. Viewing Portal';
$txt['ultport_admin_gral_settings_enable_portal_col_right'] = '<strong>Turn right column?</strong>
												<br />
												Enabled by default. Viewing Portal';
$txt['ultport_admin_gral_settings_enable_icons'] = '<strong>On the use of graphic icons?</strong>
												<br />
												Enabled by default, thus, blocks, main menu will show the icons for each one of them.<br />
												Disabling this option does not show the icons, only <strong>Title</strong>.';
$txt['ultport_admin_gral_settings_icons_extention'] = '<strong>Select extension of the graphical icons</strong>
												<br />
												This allows you to select the type of extension will have the icons of each block.<strong>Ejemplo</strong>: .jpg, .png, .gif, etc.';									
$txt['ultport_admin_gral_settings_enable_version'] = '<strong>Display the version of Portal in the Footer?</strong>
												<br />
												Enabled by default. Optional for all webmaster, thus nobody knows the Portal version you use.';

//Admin Gral Settings - Section: View Forum
$txt['ultport_admin_view_forum_enable_col_left'] = '<strong>On the left column to view the forum?</strong>
												<br />
												Enabled by default. This way you can see the column on the forum.<br />
												Disabling this option, don\'t charge the php script (not in the database generates queries) in this column';
$txt['ultport_admin_view_forum_enable_col_right'] = '<strong>On the right column to view the forum?</strong>
												<br />
												Enabled by default. This way you can see the column on the forum.<br />
												Disabling this option will not load the php script (not in the database generates queries) in this column.';
												
//Admin - Language - Maintenance 
$txt['ultport_admin_lang_maintenance_title'] = 'Language - Maintenance';
$txt['ultport_admin_lang_maintenance_admin'] = 'Admin of language';
$txt['lang_maintenance_duplicate_title'] = 'Language Duplication';
$txt['ultport_admin_lang_maintenance_edit_info'] = 'Language File Information';
$txt['ultport_admin_lang_maintenance_edit'] = 'Language File Editing';
$txt['ultport_admin_lang_maintenance_warning'] = 'Remember to clean
Cache Forum when editing a language file
												<strong>Admin---->Maintenance----->Routine------></strong> Clean Cache File. 
												<br/>&nbsp;&nbsp;It is the last option in this control panel.';

//Admin - Language - Maintenance - Section Admin Language 
$txt['ultport_admin_lang_maintenance_admin_edit_language'] = '<strong>Select Language</strong>
												<br />
												Select the file to edit';
$txt['ultport_admin_select_lang_duplicate'] = '<strong>Select Language</strong>
												<br />
												Select the language file you want to duplicate';
$txt['ultport_admin_lang_duplicate_new'] = '<strong>New File Name Language</strong>
												<br />
												Sets the file name will have the new language. No need to place the Extension ".php", 
												The same is placed automatically by Builder
												<br/><strong>Example:</strong> Modifications.english';

//Admin - Language - Maintenance - Edit Language
$txt['ultport_admin_edit_language_file'] = 'Language File';

//Admin - Perms Settings
$txt['ultport_admin_permissions_settings_title'] = 'Setting Permissions';
$txt['ultport_admin_permissions_settings_subtitle'] = 'Sets the permissions for this group';
$txt['ultport_admin_perms_groups'] = 'Select to edit the permissions group';

//Perms - Names
$txt['ultport_perms_user_posts_add'] = 'Can Add input in Post great Module?';
$txt['ultport_perms_user_posts_moderate'] = 'Can Moderate (Edit / Delete) Post Great Module?';
$txt['ultport_perms_news_add'] = 'Can add News?';
$txt['ultport_perms_news_moderate'] = 'Can Moderate (Edit / Delete) News Module?';
$txt['ultport_perms_download_add'] = 'Can add files to Download Module? ';
$txt['ultport_perms_download_moderate'] = 'Can Moderate (Edit / Delete / Approve / Disapprove / Add files, without needing to be Approved) Download Module?';
$txt['ultport_perms_ipage_add'] = 'Can add pages to Internal Pages Module?';
$txt['ultport_perms_ipage_moderate'] = 'Can Moderate (Edit / Delete) Internal Pages Module?';
$txt['ultport_perms_faq_add'] = 'Can Add Questions / Answers FAQ Module?';
$txt['ultport_perms_faq_moderate'] = 'Can Moderate (Edit / Delete) FAQ Module?';

//Admin - Portal Menu Settings
$txt['ultport_admin_portal_menu_title'] = 'Portal Menu';
$txt['ultport_admin_mainlinks_icon'] = 'Icon';
$txt['ultport_admin_mainlinks_title'] = 'Title';
$txt['ultport_admin_mainlinks_url'] = 'Web site';
$txt['ultport_admin_mainlinks_position'] = 'Position';
$txt['ultport_admin_mainlinks_edit'] = 'Edit';
$txt['ultport_admin_mainlinks_delete'] = 'Delete';
$txt['ultport_admin_mainlinks_active'] = 'Activate';
$txt['ultport_admin_mainlinks_top_menu'] = 'Add to the Top Menu?';
$txt['ultport_admin_portal_menu_add_title'] = 'Add New Link';	

//Admin - Portal Menu Settings - Edit
$txt['ultport_admin_portal_menu_edit_title'] = 'Edit Link';

//Admin - Portal Menu Settings - Edit

$txt['ultport_admin_portal_menu_delet_confirm'] = 'Are you sure you want to delete the link?';

//SEO
$txt['ultport_seo_title'] = 'SEO Management';
$txt['ultport_seo_description'] = 'SEO Management';
$txt['seo_robots_title'] = 'Configuration Robots.txt';
$txt['seo_config'] = 'General Settings';
$txt['seo_robots_txt'] = '<strong>Configuration Robots.txt</strong>
						<br/>Area where you can indicate how to index the content.';
$txt['seo_robots_added'] = 'Robots.txt';
$txt['seo_title_key_word'] = '<strong>Keywords (in the title of the Forum)</strong>
							<br />
							This way you can make it optimized for search engines, the words that identify your forum, achieving good results.
							The same appear in any forum, the words were in the title that appears in the Navigator.

							For better optimization separated by dashes "-"
							<br />
							<strong>Example:</strong> php - smf - simplemachines - portal - mysql';
$txt['seo_google_analytics'] = '<strong>Google Analytics Code</strong>
							<br />If you have Google Analytics code, just add it here.
							<br /><strong>Example:</strong> UA-00110011-1';							
$txt['seo_google_verification_code_title'] = 'Google Verification Code';
$txt['seo_google_verification_code'] = '<strong>Enter the name of html file to Verify your site on Google</strong>
							<br />This option allows you to create a "file.html" that google need for you to verify your site in Google\'s Webmaster Tools.
							<br />No need to place the HTML extension file name.';
$txt['seo_google_verification_code_error'] = 'Extension is not allowed. Just put the name.';							

//Blocks
$txt['ultport_blocks_title'] = 'Administration Block';
$txt['ultport_blocks_description'] = 'Area to establish the different positions that correspond to the blocks.';
$txt['ultport_blocks_left'] = 'Left';
$txt['ultport_blocks_center'] = 'Center';
$txt['ultport_blocks_right'] = 'Right';
$txt['ultport_blocks_enable'] = 'Activate';

//Blocks - Sect: Position
$txt['ultport_blocks_position_title'] = 'Block Position';

//Blocks - Sect: Titles
$txt['ultport_blocks_titles'] = 'Block Titles';
$txt['ultport_blocks_titles_description'] = 'Area where you can set the titles that will have each block of the Ultimate Portal';
$txt['ultport_blocks_titles_id'] = 'Id.';
$txt['ultport_blocks_titles_original_title'] = 'Current Title';
$txt['ultport_blocks_titles_custom_title'] = 'Custom Title';

//Blocks - Sect: Create Blocks
$txt['ultport_add_bk_title'] = 'Title Block';
$txt['ultport_add_bk_icon'] = '<strong>Block Icon</strong>
							<br />
							It will use the default icon, if not select anything. To place a custom icon, you must upload it to the folder
							<br /><strong>Theme/default/images/ultimate-portal/icons</strong> with extension<strong>'. $ultimateportalSettings['ultimate_portal_icons_extention'] .'</strong>.';
$txt['ultport_add_bk_collapse'] = '<strong>Block collapsible?</strong>
							<br />This way you can determine whether or not the block may collapse.';
$txt['ultport_add_bk_style'] = '<strong>Block with style?</strong>
							<br />This way you can determine whether or not the block will have style.';
$txt['ultport_add_bk_no_title'] = '<strong>Block without Title?</strong>
							<br />This way you can determine whether or not the block will have title.';							
$txt['ultport_create_blocks_titles'] = 'Create Block';
$txt['ultport_create_blocks_description'] = 'Zone Creation blocks.';
$txt['ultport_creat_bk_html_title'] = 'Create Block HTML';
$txt['ultport_creat_bk_php_title'] = 'Create Block PHP';
$txt['ultport_add_bk_html_titles'] = 'Building block HTML';
$txt['ultport_add_bk_php_titles'] = 'Building block PHP';
$txt['ultport_tmp_bk_php_hello'] = 'Hello';
$txt['ultport_tmp_bk_php_content'] = '
/*------------------------------------------------------
*	Ultimate Portal
*	Version 0.4
*	Project Manager: vicram10
*	Copyright 2011-2021
*	Powered by SMFSimple.com
--------------------------------------------------------
Got DB connection, all global variables
and all functions of the Portal and your availability Forum
*/
//NOT DELETE THIS PART
if (!defined(\'SMF\'))
	die(\'Hacking attempt...\');
//END IMPORTANT PART

global $user_info, $txt, $context;
$username = $user_info[\'username\'];
echo $txt[\'ultport_tmp_bk_php_hello\'] . \'�<strong>\'. $username . \'</strong>\';';

$txt['ultport_admin_bk_title'] = 'Blocks Admin';
$txt['ultport_admin_bk_description'] = 'Area where you can, edit your blocks, assign permissions, etc.';
$txt['ultport_admin_bk_custom'] = 'Personal Block List';
$txt['ultport_admin_bk_system'] = 'System Block List';
$txt['ultport_admin_bk_type'] = 'Type';
$txt['ultport_admin_bk_action'] = 'Actions';
$txt['ultport_admin_edit_bk_html'] = 'Editing HTML Block';
$txt['ultport_admin_edit_bk_php'] = 'Editing PHP Block';
$txt['ultport_admin_edit_perms'] = 'Block Edit Permissions';
$txt['ultport_admin_select_perms'] = '<strong>Select Groups</strong>
									<br />
									This way you can establish which are the groups that may see this block in particular';

//Multiblock CP
$txt['ultport_mb_title'] = 'Multiblock CP';
$txt['ultport_mb_main'] = 'Main';
$txt['ultport_mb_main_descrip'] = 'Control Panel multiblock created, you can edit, add, delete.';
$txt['ultport_mb_add'] = 'Add Multiblock';
$txt['ultport_mb_next'] = 'Next';
$txt['ultport_mb_title2'] = 'Multiblock Title';
$txt['ultport_mb_position'] = 'Multiblock Position | Header | Footer';
$txt['ultport_mb_blocks'] = 'Select the blocks that appear in the multiblock';
$txt['ultport_mb_design'] = 'Multiblock Design| 1 Row 2 Columns| 2 Rows 1 Column| 3 Rows 1 Column';
$txt['ultport_mb_enable'] = 'Enable Multiblock';
$txt['ultport_mbk_title'] = '<strong>MultiBlock without Title?</strong>
	<br />This way you can determine whether or not the MultiBlock will have title.';
$txt['ultport_mbk_collapse'] = '<strong>MultiBlock collapsible?</strong>
	<br />This way you can determine whether or not the MultiBlock may collapse.';
$txt['ultport_mbk_style'] = '<strong>MultiBlock with style?</strong>
	<br />This way you can determine whether or not the MultiBlock will have style.';
$txt['ultport_mb_step'] = 'Step';
$txt['ultport_mb_organization'] = '<strong>Organization</strong>
	<br />Organize your block to appear wherever you want';
$txt['ultport_mb_row'] = 'Row';
$txt['ultport_mb_column'] = 'Column';
$txt['ultport_mbk_position'] = 'Position in MultiBlock';
$txt['ultport_mb_edit'] = 'Edit Multiblock';
$txt['ultport_mb_multiheader'] = 'MultiBlock Header Position';
$txt['ultport_mb_footer'] = 'MultiBlock Footer Position';
$txt['ultport_mb_delete'] = 'Are you sure you want to delete the MultiBlock?';

//Admin Gral Settings - Section: Extra Config
$txt['ultport_exconfig_title'] = 'Extra Settings';
$txt['ultport_rso_title'] = '<strong>Reduce Site Overload</strong>
<br />
This way you can reduce the overhead (queries to the database is only 1 you see every 30 minutes) of the Ultimate Portal, using the method of SMF default Cache.
<br />
<strong>With this option enabled should empty the cache of your forum every time you change / add / delete blocks / modules, thus the changes take effect</strong>.';
$txt['ultport_collapse_left_right'] = '<strong>Enable Collapse Left/Right Blocks?</strong>
<br />
Enable by default. You can turn On or Off the possibility to collapse blocks left and right.';



//Tabs
$txt['ultport_admin_title2'] = 'Test';

//Errors
$txt['ultport_error_no_add_bk_title'] = 'Error, not a title added to his block.';
$txt['ultport_error_no_add_bk_fopen_error'] = "Can\'t open file ". $boarddir ."/up-php-blocks/tmp-bk.php. Verify that the file ". $boarddir ."/up-php-blocks/tmp-bk.php note CHMOD (0777)  and find out if your server supports PHP function  \"fopen\".";
$txt['ultport_error_fopen_error'] = "Can\'t open file / write. Verify that the file has CHMOD (0777)  and find out if your server supports PHP function  \"fopen\".";
$txt['ultport_error_no_add_bk_nofile'] = "Unable to open the file.Check the write permissions on the folder: Themes/default/language if you editing language file or ". $boarddir ."/up-php-blocks if you add or editing a php block.";
$txt['ultport_error_no_name'] = 'You must provide a Title, you can\'t leave blank.';
//Permissions to enter the admin panel Ultimate Portal
$txt['ultport_error_enter_admin'] = 'Sorry, not allowed to enter the Site Administration';

?>