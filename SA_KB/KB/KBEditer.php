<?php
/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://www.sa-mods.info
 *
 * The Initial Developer of the Original Code is
 * wayne Mankertz.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 * ***** END LICENSE BLOCK ***** */
if (!defined('SMF'))
	die('Hacking attempt...');

function KB_showediter($value,$bid)
{
	global $modSettings, $context, $sourcedir;

	require_once($sourcedir . '/Subs-Editor.php');

	$modSettings['disable_wysiwyg'] = !empty($modSettings['disable_wysiwyg']) || empty($modSettings['enableBBC']);
	
	$editorOptions = array(
		'id' => $bid,
		'value' => $value,
		'width' => '90%',
	);
    
   create_control_richedit($editorOptions);
   $context['post_box_name'] = $editorOptions['id'];
}

function KB_showediterpreview($subj,$mess,$sub)
{
	global $context, $smcFunc, $txt;

	$context['edit']['current'] = 0;

   $context['preview_subject'] = $smcFunc['htmlspecialchars']($subj, ENT_QUOTES);
   $context['preview_message'] = $smcFunc['htmlspecialchars']($mess, ENT_QUOTES);

   $context['preview_message'] = KB_parseTags($context['preview_message'], 0, true);

   $context['preview_message'] = !empty($context['preview_message']) ? $context['preview_message'] : '';
   $context['preview_subject'] = !empty($context['preview_subject']) ? $context['preview_subject'] : '';
   
   $context['page_title'] = $txt['preview'] . ' - ' . $context['preview_subject'];

   kbPreview($sub);
}

function kbPreview($sub)
{
	global $context, $smcFunc, $sourcedir;

	require_once($sourcedir . '/Subs-Editor.php');

   $context['title'] = isset($_REQUEST['title']) ? $smcFunc['htmlspecialchars']($_REQUEST['title']) : '';
   $context['body'] = isset($_REQUEST['description']) ? str_replace(array('  '), array('&nbsp; '), $smcFunc['htmlspecialchars']($_REQUEST['description'])) : '';

    $editorOptions = array(
	  'id' => 'description',
	  'value' => !empty($context['body']) ? $context['body'] : '',
	  'width' => '90%',
    );

   create_control_richedit($editorOptions);

   $context['post_box_name'] = $editorOptions['id'];
   $context['sub_template'] = $sub;
}
?>	