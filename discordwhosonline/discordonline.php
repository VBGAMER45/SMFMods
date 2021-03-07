<?php
/*******************************************************************************
 * Discord Who's Online for SMF
 *
 * Copyright (c) 2021 vbgamer45
 */


if (!defined('SMF'))
	die('No direct access...');

function discordonline_credits()
{
	global $context;
	$context['copyrights']['mods'][] = 'Discord Who\'s Online by vbgamer45 &copy; 2021';
}

function discordonline_mod_settings(&$config_vars)
{
	global $txt, $modSettings;
		loadLanguage('discordonline');

		if (empty($modSettings['discord_online_cache_minutes']))
			$modSettings['discord_online_cache_minutes'] = 3;

		if (isset($_REQUEST['save']))
			cache_put_data('discord_whos_online', null,  ($modSettings['discord_online_cache_minutes'] * 60));


		if (!empty($config_vars))
			$config_vars[] = '';

		$config_vars[] = array('title', 'discord_online_title');
		$config_vars[] = array('check', 'discord_online_enabled');
		$config_vars[] = array('text', 'discord_online_server_id', 'subtext' => $txt['discord_online_server_id_extra']);
		$config_vars[] = array('check', 'discord_online_show_avatars');
		$config_vars[] = array('int', 'discord_online_cache_minutes');
		$config_vars[] = array('text', 'discord_online_server_link');

}

// Portions  Copyright © 2020 StoryBB. All rights reserved.
function discordonline_boardlayout()
{
	global $sourcedir, $modSettings, $txt, $context;

	if (!function_exists('curl_init'))
		return false;


	if (empty($modSettings['discord_online_cache_minutes']))
		$modSettings['discord_online_cache_minutes'] = 3;


	if (empty($modSettings['discord_online_enabled']))
		return false;

	if (empty($modSettings['discord_online_server_id']))
		return false;

	loadLanguage('discordonline');
	// Cache the output
	if (($contents= cache_get_data('discord_whos_online', ($modSettings['discord_online_cache_minutes'] * 60))) == null)
    {
		// Include the file containing the curl_fetch_web_data class.
		require_once($sourcedir . '/Class-CurlFetchWeb.php');

		$fetch_data = new curl_fetch_web_data();
		$fetch_data->get_url_data('https://discord.com/api/guilds/' . $modSettings['discord_online_server_id'] . '/widget.json','');


		switch ($fetch_data->result('code'))
		{
			case 200:
				$contents = (string) $fetch_data->result('body');
				break;
			case 403:
				// Widget not configured.
				log_error($txt['discord_online_err_not_configured']);
				break;
			case 429:
				// Widget rate-limited.
				log_error($txt['discord_online_err_rate_limited']);
				break;
			default:
				// Something else went wrong.
				break;
		}


        cache_put_data('discord_whos_online', $contents,  ($modSettings['discord_online_cache_minutes'] * 60));

   	 }

		if (empty($contents))
		{
			return false;
		}

		$server_response = @json_decode($contents, true);

		$online = array();
		if (!empty($server_response['members']))
		foreach ($server_response['members'] as $member)
		{
			$online[] = [
				'name' => $member['username'],
				'avatar' => $member['avatar_url'],
			];
		}

		$context['info_center'][] = array(
				'tpl' => 'discord_whos_online',
				'txt' => $txt['discord_online_title'],
			);

		$context['discord_whos_online']['num_online'] =  $server_response['presence_count'];
		$context['discord_whos_online']['online'] = $online;

}

function  template_ic_block_discord_whos_online()
{
	global $context, $scripturl, $txt, $modSettings, $settings;

	// Check for SMF 2.1.x
	if (function_exists("set_tld_regex"))
	{
		echo '
			<div class="sub_bar">
				<h4 class="subbg">
					', '<span class="main_icons people"></span> ', $txt['discord_online_title'], (!empty($modSettings['discord_online_server_link']) ? ' <a href="' . $modSettings['discord_online_server_link'] . '" target=_blank">' . $txt['discord_online_visit_discord'] . '</a>' : ''), '
				</h4>
			</div>
			<p class="inline">
				<strong>', comma_format($context['discord_whos_online']['num_online']), ' ', $context['discord_whos_online']['num_online'] == 1 ? $txt['user'] : $txt['users'];
		echo '</strong><br>';

	}
	else
	{
		// SMF 2.0.x
		echo '
				<div class="title_barIC">
					<h4 class="titlebg">
						<span class="ie6_header floatleft">
							<img class="icon" src="', $settings['images_url'], '/icons/online.gif', '" alt="', $txt['online_users'], '" />', $txt['discord_online_title'], (!empty($modSettings['discord_online_server_link'])  ? ' <a href="' . $modSettings['discord_online_server_link'] . '" target=_blank">' . $txt['discord_online_visit_discord'] . '</a>' : ''), '
						</span>
					</h4>
				</div>
				<p class="inline">
					<strong>',  comma_format($context['discord_whos_online']['num_online']), ' ', $context['discord_whos_online']['num_online'] == 1 ? $txt['user'] : $txt['users'];
		echo '</strong><br>';
	}

	if (!empty($context['discord_whos_online']['online']))
	{
		$first = 0;
		foreach($context['discord_whos_online']['online'] as $row)
		{
			if (!empty($first))
				echo ',&nbsp;';

			if (!empty($modSettings['discord_online_show_avatars']))
			{
				echo '<img src="' .$row['avatar'] . '" style="max-height: 25px" alt="" />';
			}

			echo $row['name'];

			$first = 1;

		}


	}

	echo '
			</p>';
}
