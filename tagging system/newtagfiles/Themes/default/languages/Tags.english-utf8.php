<?php
/*
Tagging System
Version 2.4.1+stef
http://www.smfhacks.com
  by: vbgamer45 and stefann
  license: this modification is licensed under the Creative Commons BY-NC-SA 3.0 License

Included icons are from Silk Icons 1.3 available at http://www.famfamfam.com/lab/icons/silk/
  and are licensed under the Creative Commons Attribution 2.5 License
*/

//Tags text strings
$txt['smftags_tags'] = 'Tags';
$txt['smftags_tagtosuggest'] = 'Tag To Suggest:';
$txt['smftags_popular'] = 'Popular Tags';
$txt['smftags_latest'] = 'Latest Tagged Posts';
$txt['smftags_resultsfor'] = 'Results for ';

$txt['smftags_suggest'] = 'Suggest Tag';
$txt['smftags_addtag2'] = 'Add Tag';
$txt['smftags_tagtoadd'] = 'Tag to Add';
$txt['smftags_edittag'] = 'Edit Tag';
$txt['smftags_rmsuggest'] = 'Remove suggestions';
$txt['smftags_select'] = 'Select';
$txt['smftags_suggesttopic'] = 'Suggest Topic Tags';
$txt['smftags_viewall'] = 'View all';
$txt['smftags_viewall2'] = ' for a complete list of tags';

$txt['smftags_manage'] = 'Manage Tags';
$txt['smftags_manage_tags'] = 'Pending Tags';
$txt['smftags_manage_topictags'] = 'Pending Topic Tags';
$txt['smftags_manage_topic'] = 'Tagged topic';
$txt['smftags_manage_suggestedby'] = 'Suggested by';
$txt['smftags_taggable'] = 'Taggable';
$txt['smftags_untaggable'] = 'Untaggable';
$txt['smftags_renametag'] = 'Rename tag';
$txt['smftags_renametag_to'] = 'to';
$txt['smftags_all'] = 'All Tags';
$txt['smftags_noparent'] = 'no parent';
$txt['smftags_parents'] = 'Parent tag(s)';
$txt['smftags_count'] = 'Count';
$txt['smftags_roottag'] = 'root tag element';

$txt['smftags_act_merge'] = 'Merge';
$txt['smftags_desc_merge'] = 'Select tags to move tags to merge, moving all existing topic tags placed. All tags to be merged must be selected.';
$txt['smftags_merge2'] = 'Please select the tag you wish to merge the selected tags to';
$txt['smftags_act_move'] = 'Move';
$txt['smftags_desc_move'] = 'Select tags to move tags to a single parent. The new parent must also be selected, if applicable';
$txt['smftags_move2'] = 'Please select the parent tag you wish to move the selected tags to';
$txt['smftags_act_delete'] = 'Delete';
$txt['smftags_act_expand'] = 'Expand';
$txt['smftags_act_approve'] = 'Approve';
$txt['smftags_act_unapprove'] = 'Unapprove';
$txt['smftags_act_taggable'] = 'Set taggable';
$txt['smftags_act_untaggable'] = 'Set untaggable';
$txt['smftags_act_rename'] = 'Rename';
$txt['smftags_act_create'] = 'Create';
$txt['smftags_act_update'] = 'Update';
$txt['smftags_act_go'] = 'Go';
$txt['smftags_act_reset'] = 'Reset';

//Tags Admin Settings
$txt['smftags_set_taggable'] = 'Choose taggable boards by id (space delimited)';
$txt['smftags_set_mintaglength'] = 'Minimum Tag Length';
$txt['smftags_set_maxtaglength'] = 'Maximum Tag Length';
$txt['smftags_set_maxtags'] = 'Max number of tags per topic';

$txt['smftags_set_type'] = 'Choose what style tagging to use';
$txt['smftags_set_listtags'] = 'Tag list';
$txt['smftags_set_delimiter'] = 'Delimiter character for manual tagging';
$txt['smftags_set_listcols'] = 'Number of columns for tag list';
$txt['smftags_set_manualtags'] = 'Manual tagging';
$txt['smftags_set_display'] = 'Where to display tags';
$txt['smftags_set_display_top'] = 'Top of topic';
$txt['smftags_set_display_bottom'] = 'Bottom of topic';
$txt['smftags_set_display_messageindex'] = 'Message Index icons';

$txt['smftags_set_latest_limit'] = 'Latest tags to show at bottom of tags page';

$txt['smftags_set_cloud_sort'] = 'Sort tag cloud by';
$txt['smftags_set_cloud_sort_alpha'] = 'alphabetical order';
$txt['smftags_set_cloud_sort_count'] = 'tag count';
$txt['smftags_set_cloud_sort_random'] = 'random';



$txt['smftags_tagcloud_settings'] = 'Tag Cloud Settings';
$txt['smftags_set_cloud_tags_to_show'] = 'Number of tags to show in tag cloud';
$txt['smftags_set_cloud_tags_per_row'] = 'Number of tags to show per row';
$txt['smftags_set_cloud_max_font_size_precent'] = 'Max tag cloud font size in percent';
$txt['smftags_set_cloud_min_font_size_precent'] = 'Min tag cloud font size in percent';



$txt['smftags_err_notopic'] = 'No topic selected.';
$txt['smftags_err_notag'] = 'You need to enter a tag.';
$txt['smftags_err_nodirect'] = 'You cannot access this page directly.'; 
$txt['smftags_err_invalidtag'] = 'This tag no longer exists, or is invalid';
$txt['smftags_err_emptytag'] = 'This tag has not been used yet.'; 

$txt['smftags_err_mintag'] = 'The tag is smaller than the minimum tag length of ';
$txt['smftags_err_maxtag'] = 'The tag is greater than the maximum tag length of ';
$txt['smftags_err_toomaxtag'] = 'Tag limit per topic exceeded.';

$txt['smftags_err_alreadyexists'] = 'That tag for that topic already exists.';
$txt['smftags_err_delimiterused'] = 'The delimiter you have chosen is already used in %d tags. Either choose another delimiter, or rename these tags first.';

$txt['smftags_success_unapprove'] = 'Successfully unapproved %d tags'; 
$txt['smftags_success_untaggable'] = 'Successfully marked %d tags as untaggable'; 
$txt['smftags_success_taggable'] = 'Successfully marked %d tags as taggable'; 
$txt['smftags_success_delete'] = 'Successfully deleted %d tags, affecting %d rows'; 
$txt['smftags_success_move'] = 'Successfully moved %d tags'; 
$txt['smftags_success_merge'] = 'Successfully merged %d tags, affecting %d rows'; 

$txt['smftags_settings'] = 'Tags Settings';
$txt['smftags_pages'] = 'Pages: ';

$txt['smftags_savesettings'] = 'Save Settings';

///Results Display
$txt['smftags_subject'] = 'Subject';
$txt['smftags_startedby'] = 'Started by';
$txt['smftags_replies'] = 'Replies';
$txt['smftags_views'] = 'Views';
$txt['smftags_guest'] = 'Guest';
$txt['smftags_othertags'] = 'Other tags';

$txt['smftags_topictag'] = 'Tag';

//Manage Display
$txt['smftags_postedby'] = 'Posted by';
$txt['smftags_suggestedby'] = 'Suggested by';
$txt['smftags_tagssuggested'] = 'Tags suggested';
$txt['smftags_create'] = 'Create new tags';
$txt['smftags_parent'] = 'Parent tag';
$txt['smftags_taggable'] = 'Taggable';
$txt['smftags_approved'] = 'Approved';
?>