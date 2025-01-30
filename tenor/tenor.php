<?php
/*******************************************************************************
 * Tenor for SMF
 *
 * Copyright (c) 2024 vbgamer45
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 ******************************************************************************/


if (!defined('SMF'))
	die('No direct access...');

function tenor_bbc_buttons(&$bbc_tags, &$editor_tag_map)
{
	global $context, $editortxt, $modSettings;

	if (empty($modSettings['tenorapikey']))
			return;

	loadLanguage('tenor');

	$tenorData = array(array(
		'image' => 'tenor10',
		'code' => 'scetenor',
		'description' => $editortxt['scetenor'],

	));

	$firstData = array();
	$secondData = array();
	$count = 0;
	$lastCount = 0;
	foreach($context['bbc_tags'][count($context['bbc_tags']) - 1]  as $key => &$item)
	{
		$count++;
		if (isset($item['code']) && $item['code'] == 'image')
		{
			$firstData = array_slice($context['bbc_tags'][count($context['bbc_tags']) - 1],0,$count);
			$lastCount = $count;
		}

	}

	$secondData = array_slice($context['bbc_tags'][count($context['bbc_tags']) - 1],$lastCount,($count - $lastCount));
	$context['bbc_tags'][count($context['bbc_tags']) - 1] = array_merge($firstData,$tenorData,$secondData);

	loadJavaScriptFile('scetenor.js', array(), 'smf_scetenor');


	addInlineJavaScript('
		window.tenorkey = "' . $modSettings['tenorapikey'] . '";');
}
function tenor_credits()
{
	global $context;
	$context['copyrights']['mods'][] = 'Tenor for SMF by vbgamer45 &copy; 2024';
}
function tenor_sceditor(&$sce_options)
{

	global $modSettings;

	if (empty($modSettings['tenorapikey']))
		return;

	if (!empty($sce_options['plugins']))
		$sce_options['plugins'] .= ',';

	$sce_options['plugins'] .= 'scetenor';

}
function tenor_mod_settings(&$config_vars)
{
	global $txt;
		loadLanguage('tenor');

		if (!empty($config_vars))
			$config_vars[] = '';

		$config_vars[] = array('text', 'tenorapikey', 'subtext' => $txt['tenorapikey_extra']);
}