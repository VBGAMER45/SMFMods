<?php
/*
SMF Archive
Version 3.0
by:vbgamer45
https://www.smfhacks.com
Copyright 2008-2023 http://www.samsonsoftware.com
*/
if (!defined('SMF'))
	die('Hacking attempt...');

function archive_buffer($buffer)
{
    global $modSettings, $txt, $board, $topic, $boardurl;

	if(isset($_REQUEST['xml']))
	   return $buffer;

		// Load the language files
		if (loadlanguage('Archive') == false)
			loadLanguage('Archive','english');

		$link = $boardurl . '/archive2.php';

		if (!empty($board))
			$link .= '?board=' . $board;
		else if (!empty($topic))
			$link .= '?topic=' . $topic;

		 if (function_exists("set_tld_regex"))
		 {
			 // 2.1
			 $buffer  = str_replace(' | <a href="#top_section">','| <a href="' . $link . '">' . $txt['txt_archive'] . '</a> | <a href="#top_section">',$buffer);

		 }
		 else
		 {
			 // SMF 2.0
			  $buffer  = str_replace(', <a href="https://www.simplemachines.org" title="Simple Machines" target="_blank" class="new_win">Simple Machines</a>',', <a href="https://www.simplemachines.org" title="Simple Machines" target="_blank" class="new_win">Simple Machines</a><br /><a href="' . $link . '">' . $txt['txt_archive'] . '</a><br />',$buffer);
		 }

	return $buffer;
}

?>