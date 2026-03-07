<?php

/**
 * Members Online Today - Template
 *
 * @package MembersOnlineToday
 * @author vbgamer45
 * @license BSD 3-Clause
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Info Center block for Members Online Today.
 * Called by the SMF info_center rendering loop.
 */
function template_ic_block_MembersOnlineToday()
{
	global $context, $txt, $modSettings;

	$time_range = isset($modSettings['mot_time_range']) ? $modSettings['mot_time_range'] : 'today';

	// Pick the correct heading based on configured time range.
	if ($time_range === '7days')
		$heading = $txt['mot_heading_7days'];
	elseif ($time_range === '24hours')
		$heading = $txt['mot_heading_24hours'];
	else
		$heading = $txt['mot_heading_today'];

	echo '
	<div class="sub_bar">
		<h4 class="subbg">
			<span class="main_icons people"></span> ', $heading, '
		</h4>
	</div>
	<p class="inline smalltext">';

	if (!empty($context['mot_can_view']))
	{
		$visible_count = $context['mot_total_count'] - $context['mot_hidden_count'];

		echo '
		', $txt['mot_total'], ': <strong>', $context['mot_total_count'], '</strong>';

		if (allowedTo('moderate_forum'))
		{
			echo ' (', $txt['mot_visible'], ': ', $visible_count, ', ', $txt['mot_hidden'], ': ', $context['mot_hidden_count'], ')';
		}

		if (!empty($context['mot_member_links']))
		{
			echo '<br>
		', implode(', ', $context['mot_member_links']);
		}
	}
	else
	{
		echo '
		', $txt['mot_total'], ': <strong>', $context['mot_total_count'], '</strong>';
	}

	echo '
	</p>';
}
