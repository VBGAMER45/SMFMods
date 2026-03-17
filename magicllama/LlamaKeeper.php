<?php
/******************************************************************************
* LlamaKeeper.php       2004                                                  *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 1.0 RC1 	                              *
* Software by:                Simple Machines (http://www.simplemachines.org) *
* Copyright 2001-2004 by:     Lewis Media (http://www.lewismedia.com)         *
* Support, News, Updates at:  http://www.simplemachines.org                   *
*******************************************************************************
* This program is free software; you may redistribute it and/or modify it     *
* under the terms of the provided license as published by Lewis Media.        *
*                                                                             *
* This program is distributed in the hope that it is and will be useful,      *
* but WITHOUT ANY WARRANTIES; without even any implied warranty of            *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                        *
*                                                                             *
* See the "license.txt" file for details of the Simple Machines license.      *
* The latest version can always be found at http://www.simplemachines.org.    *
******************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

function Llamalog()
{
	global $db_prefix, $context, $_GET;

	isAllowedTo('admin_forum');

	loadTemplate('MagicLlama');

	adminIndex('view_Llama_log');

	$sa = !empty($_GET['sa']) ? $_GET['sa'] : '';
	$llamasa = array(
			'RemoveULlamas' => 'removeLlamas1',
			'RemoveALlamas' => 'removeLlamas2'
		);
	if (isset($llamasa[$sa]))
	{
		$exacuteLlamas = $llamasa[$sa];
		$exacuteLlamas();
	}
		
	// get all llama's and all the members, if any that Caught one!
	$request = db_query("
			SELECT li.*, m.memberName
			FROM {$db_prefix}llama_info AS li
				LEFT JOIN {$db_prefix}members AS m ON (m.ID_MEMBER=li.member)", __FILE__, __LINE__);

	while ($logs = mysql_fetch_assoc($request))
		$context['llamaAdmin'][] = $logs;

	mysql_free_result($request);

	if (!isset($context['page_title']))
		$context['page_title'] = 'Llama Keeper 2004';
}

function removeLlamas1()
{
	global $db_prefix;

	isAllowedTo('admin_forum');

	// remove unCaught Llama's from llama_info
	$request = db_query("
			DELETE FROM {$db_prefix}llama_info
			WHERE member IS NULL AND Caught IS NULL", __FILE__, __LINE__);
}

function removeLlamas2()
{
	global $db_prefix;

	isAllowedTo('admin_forum');

	// remove unCaught Llama's from llama_info
	$request = db_query("
			DELETE FROM {$db_prefix}llama_info", __FILE__, __LINE__);
}
?>