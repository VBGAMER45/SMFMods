<?php
/**********************************************************************************
* SMFShop item                                                                    *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.0 (Build 12)                              *
* $Date:: 2007-01-18 19:26:55 +1100 (Thu, 18 Jan 2007)                          $ *
* $Id:: StickyTopic.php 79 2007-01-18 08:26:55Z daniel15                        $ *
* Software by:                DanSoft Australia (http://www.dansoftaustralia.net/)*
* Copyright 2005-2007 by:     DanSoft Australia (http://www.dansoftaustralia.net/)*
* Support, News, Updates at:  http://www.dansoftaustralia.net/                    *
*                                                                                 *
* Forum software by:          Simple Machines (http://www.simplemachines.org)     *
* Copyright 2006-2007 by:     Simple Machines LLC (http://www.simplemachines.org) *
*           2001-2006 by:     Lewis Media (http://www.lewismedia.com)             *
***********************************************************************************
* This program is free software; you may redistribute it and/or modify it under   *
* the terms of the provided license as published by Simple Machines LLC.          *
*                                                                                 *
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
*                                                                                 *
* See the "license.txt" file for details of the Simple Machines license.          *
* The latest version of the license can always be found at                        *
* http://www.simplemachines.org.                                                  *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

class item_StickyTopic extends itemTemplate
{
 
    function getItemDetails()
	{
		$this->authorName = 'Daniel15';
		$this->authorWeb = 'http://www.dansoftaustralia.net/';
		$this->authorEmail = 'dansoft@dansoftaustralia.net';

          $this->name = 'Sticky Topic';
          $this->desc = 'Make any one of your topics a sticky!';
          $this->price = 400;

          $this->require_input = true;
          $this->can_use_item = true;
    }

    function getUseInput()
	{
		global $db_prefix, $context;
		
		$returnStr = '
			Please choose which topic you would like to sticky: <br />
			<select name="stickyTopic">';

		$result = db_query("
			SELECT t.ID_TOPIC, t.isSticky, m.subject
			FROM {$db_prefix}topics AS t, {$db_prefix}messages AS m
			WHERE t.ID_MEMBER_STARTED = {$context['user']['id']} AND m.ID_MSG = t.ID_FIRST_MSG", __FILE__, __LINE__);		
		while ($row = mysql_fetch_assoc($result))
		{
			if ($row['isSticky'] == 0)
			{
				$returnStr .= '<option value="' . $row['ID_TOPIC'] . '">' . $row['subject'] . '</option>';
			}
		}
		
		$returnStr .= '</select>';
        return $returnStr;
    }

    function onUse()
	{
		global $db_prefix, $context;
		
		if (!isset($_POST['stickyTopic'])) die('ERROR: No topic chosen!');
		$_POST['stickyTopic'] = (int) $_POST['stickyTopic'];
		
		$result = db_query("
			SELECT isSticky, ID_MEMBER_STARTED
			FROM {$db_prefix}topics
			WHERE ID_TOPIC = {$_POST['stickyTopic']}", __FILE__, __LINE__);
		$row = mysql_fetch_assoc($result);		
		
		if (mysql_num_rows($result) == 0)
			die('ERROR: That topic does not exist!');
		if ($row['ID_MEMBER_STARTED'] != $context['user']['id'])
			die ('ERROR: That isn\'t your topic!');
		
		db_query("
			UPDATE {$db_prefix}topics			 
			SET isSticky = 1
			WHERE ID_TOPIC = {$_POST['stickyTopic']}
			LIMIT 1", __FILE__, __LINE__);
							 
        return 'Made topic #' . $_POST['stickyTopic'] . ' a sticky!';
    }
}

?>
