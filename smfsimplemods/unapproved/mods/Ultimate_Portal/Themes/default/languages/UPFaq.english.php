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
$txt['ultport_button_add'] = 'Publish';
$txt['ultport_button_view'] = 'Preview';
$txt['ultport_button_editing'] = 'Editing';
$txt['ultport_button_back'] = 'Back';

//Titles Modules
$txt['up_module_title'] = 'Module';
$txt['up_module_faq_title'] = 'Frequently Asked Questions';
$txt['up_faq_index'] = 'Index';
$txt['up_faq_content'] = 'Content';

//Text Normal
$txt['up_faq_add'] = 'Add Question and Answer';
$txt['up_faq_add_section'] = 'Add Section';
$txt['up_faq_edit'] = 'Edit Question and Answer';
$txt['up_faq_edit_section'] = 'Edit Section';

//Add New FAQ
$txt['up_faq_add_description'] = 'Form to add new Questions and Answers Module';
$txt['up_faq_edit_description'] = 'Form to edit the selected Question and Answers module';
$txt['up_faq_question'] = '<strong>Question</strong>
					<br/>The title that will appear as the question to perform.
					<br/><strong>Example:</strong> What is Ultimate Portal?';
$txt['up_faq_section'] = '<strong>Section which belongs</strong>
					<br/>This section may establish that you own this Question and Answer';
$txt['up_faq_answer'] = '<strong>Answer</strong>
					<br/>The answer to the question.';

//Add New Section
$txt['up_faq_section_description'] = 'Form to add / edit new sections to the module.';
$txt['up_faq_section_title'] = 'Title of the section.';

//Errors
$txt['ultport_error_no_active'] = 'Error, the module is not enabled';
$txt['ultport_error_no_faq_main'] = 'There haven\'t been any questions or answers added.';
$txt['ultport_error_no_perm'] = 'Error, you do not have the permissions to perform this action.';
$txt['ultport_error_no_add_title'] = 'Error, you have not added a title.';
$txt['ultport_error_no_add_section'] = 'Error, there have not been any sections created.';
$txt['ultport_error_no_edit'] = 'Error, failed to recieve the Question ID.';
$txt['ultport_error_no_delete'] = 'Error, this has not been deleted, failed to recieve the Question ID.';
$txt['ultport_error_no_empty'] = 'Error, you cannot leave any field empty.';
$txt['ultport_error_no_delete_section'] = 'Error, failed to delete the section, cannot find the Section ID.';

//Delete confirmation
$txt['ultport_delete_section_confirmation'] = 'Are you sure you want to delete this Section, including any FAQ that it has inside?';
$txt['ultport_delete_confirmation'] = 'Are you sure you want to delete this?';

?>