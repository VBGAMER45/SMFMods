<?php
/**********************************************************************************
* SMFShop item                                                                    *
***********************************************************************************
* SMFShop: Shop MOD for Simple Machines Forum                                     *
* =============================================================================== *
* Software Version:           SMFShop 3.1 (Build 14)                              *
* Software by:                DanSoft Australia (http://www.dansoftaustralia.net/)*
* Copyright 2009 by:          vbgamer45 (http://www.smfhacks.com)                 *
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
***********************************************************************************/

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
		global $smcFunc, $context;
		
		$returnStr = '
			Please choose which topic you would like to sticky: <br />
			<select name="stickyTopic">';

		$result = $smcFunc['db_query']('', "
			SELECT t.id_topic, t.is_sticky, m.subject
			FROM {db_prefix}topics AS t, {db_prefix}messages AS m
			WHERE t.id_member_started = {int:member} AND m.id_msg = t.id_first_msg",
			array(
				'member' => $context['user']['id'],
			));
		
		while ($row = $smcFunc['db_fetch_assoc']($result))
		{
			if ($row['is_sticky'] == 0)
			{
				$returnStr .= '<option value="' . $row['id_topic'] . '">' . $row['subject'] . '</option>';
			}
		}
		
		$returnStr .= '</select>';
        return $returnStr;
    }

    function onUse()
	{
		global $context, $smcFunc;
		
		if (!isset($_POST['stickyTopic'])) die('ERROR: No topic chosen!');
		$_POST['stickyTopic'] = (int) $_POST['stickyTopic'];
		
		$result = $smcFunc['db_query']('', "
			SELECT is_sticky, id_member_startted
			FROM {db_prefix}topics
			WHERE id_topic = {int:id_topic}",
			array(
				'id_topic' => $_POST['stickyTopic'],
			));
		$row = $smcFunc['db_fetch_assoc']($result);		
		
		if ($smcFunc['db_num_rows']($result) == 0)
			die('ERROR: That topic does not exist!');
		if ($row['id_member_started'] != $context['user']['id'])
			die ('ERROR: That isn\'t your topic!');
		
		$smcFunc['db_query']('', "
			UPDATE {db_prefix}topics			 
			SET is_sticky = 1
			WHERE id_topic = {int:id_topic}
			LIMIT 1",
			array(
				'id_topic' => $_POST['stickyTopic'],
			));
							 
        return 'Made topic #' . $_POST['stickyTopic'] . ' a sticky!';
    }
}

?>
