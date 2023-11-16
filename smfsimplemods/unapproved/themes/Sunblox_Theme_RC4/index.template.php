<?php
/*--------------------------------------
*	Smfsimple.com
*   Theme: SunBlox Theme
*   Version Smf: 2.0
*	Author: Lean
*	Copyright 2011-2021
*	Desarrollado para www.smfsimple.com
***************************************/

/*	This template is, perhaps, the most important template in the theme. It
	contains the main template layer that displays the header and footer of
	the forum, namely with main_above and main_below. It also contains the
	menu sub template, which appropriately displays the menu; the init sub
	template, which is there to set the theme up; (init can be missing.) and
	the linktree sub template, which sorts out the link tree.

	The init sub template should load any data and set any hardcoded options.

	The main_above sub template is what is shown above the main content, and
	should contain anything that should be shown up there.

	The main_below sub template, conversely, is shown after the main content.
	It should probably contain the copyright statement and some other things.

	The linktree sub template should display the link tree, using the data
	in the $context['linktree'] variable.

	The menu sub template should display all the relevant buttons the user
	wants and or needs.

	For more information on the templating system, please see the site at:
	http://www.simplemachines.org/
*/

// Initialize the template... mainly little settings.
function template_init()
{
	global $context, $settings, $options, $txt;

	$settings['use_default_images'] = 'never';
	$settings['doctype'] = 'xhtml';
	$settings['theme_version'] = '2.0';
	$settings['use_tabs'] = true;
	$settings['use_buttons'] = true;
	$settings['separate_sticky_lock'] = true;
	$settings['strict_doctype'] = false;
	$settings['message_index_preview'] = false;
	$settings['require_theme_strings'] = true;
}

// The main sub template above the content.
function template_html_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', $context['right_to_left'] ? ' dir="rtl"' : '', '>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
	<meta name="description" content="', $context['page_title_html_safe'], '" />', !empty($context['meta_keywords']) ? '
	<meta name="keywords" content="' . $context['meta_keywords'] . '" />' : '', '
	<title>', $context['page_title_html_safe'], '</title>';

	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex" />';

	if (!empty($context['canonical_url']))
		echo '
	<link rel="canonical" href="', $context['canonical_url'], '" />';

	echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?fin2" />
	';

	echo '
	<link rel="help" href="', $scripturl, '?action=help" />
	<link rel="search" href="', $scripturl, '?action=search" />
	<link rel="contents" href="', $scripturl, '" />';

	if (!empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']))
		echo '
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['rss'], '" href="', $scripturl, '?type=rss;action=.xml" />';

	if (!empty($context['current_topic']))
		echo '
	<link rel="prev" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=prev" />
	<link rel="next" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=next" />';

	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="', $scripturl, '?board=', $context['current_board'], '.0" />';

	foreach (array('ie7', 'ie6', 'webkit') as $cssfix)
		if ($context['browser']['is_' . $cssfix])
			echo '
	<link rel="stylesheet" type="text/css" href="', $settings['default_theme_url'], '/css/', $cssfix, '.css" />';

	if ($context['right_to_left'])
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/rtl.css" />';

	echo '
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?fin2"></script>
	<script type="text/javascript" src="', $settings['theme_url'], '/scripts/theme.js?fin2"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var smf_theme_url = "', $settings['theme_url'], '";
		var smf_default_theme_url = "', $settings['default_theme_url'], '";
		var smf_images_url = "', $settings['images_url'], '";
		var smf_scripturl = "', $scripturl, '";
		var smf_iso_case_folding = ', $context['server']['iso_case_folding'] ? 'true' : 'false', ';
		var smf_charset = "', $context['character_set'], '";', $context['show_pm_popup'] ? '
		var fPmPopup = function ()
		{
			if (confirm("' . $txt['show_personal_messages'] . '"))
				window.open(smf_prepareScriptUrl(smf_scripturl) + "action=pm");
		}
		addLoadEvent(fPmPopup);' : '', '
		var ajax_notification_text = "', $txt['ajax_in_progress'], '";
		var ajax_notification_cancel_text = "', $txt['modify_cancel'], '";
	// ]]></script>';
	
	echo'<style type="text/css">
	      .wrapper{ ';
		  if ($settings['forum_width'])
		  echo 'width: ', $settings['forum_width'] ,';';
		  else echo 'width: 990px;';
	      echo 'margin: 0 auto;
	        padding: 0;}
		.logo a {';
		  if (!empty($settings['active_publi']))
	      	echo 'width: 300px;';
		  else 
		  	echo'width: 100%;';
		  
	     echo'height: 133px;
	     display: block;
	     float: left;
	     margin-top: 15px;}	
		 </style>';
	echo $context['html_headers'];

	echo '
</head>
<body>';
}

function template_body_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	
	<div id="header">
		<div class="wrapper">
		<div id="toolbar_left">
				<div id="toolbar_right">
					<div id="toolbar">';
					// Show the menu here, according to the menu sub template.
						template_menu();
					echo'</div>
				</div>
			</div>';
			if (!empty($settings['active_publi']))
			 echo'<div class="logo">';
			else
				echo '<div class="logo" align="center">';

			echo'<a href="', $scripturl, '">', empty($context['header_logo_url_html_safe']) ? '<span><img src="'. $settings['images_url']. '/custom/logo.png" alt="' . $context['forum_name'] . '" /></span>' : '<img src="' . $context['header_logo_url_html_safe'] . '" alt="' . $context['forum_name'] . '" />', '</a>
			</div>
		</div>
	</div>
	<div id="sub_header" align="center">';

	     if (!empty($settings['active_publi']))
		  {
		  	echo' <div class="wrapper" style="padding-top:10px;color:#000;font-weight:bold;">
		', $txt['sunpubli_ad'] ,':<br />
		', parse_bbc($settings['sunpubli']),'
		 </div>';

		  }
	echo'</div>
	<div id="topbar" align="center">
		<div class="wrapper">
			<div class="toplog"><br />
				', $txt['hello_member'], ' ', $context['user']['name'],'!! ';
				if ($context['allow_pm'])
					echo '
						<img src="', $settings['images_url'], '/selected.gif" alt="" /> <a class="dot" href="', $scripturl, '?action=pm">', $txt['messages_total'], ' ', $context['user']['messages'], ' (', $context['user']['unread_messages'] , ' ', $txt['messages_new'], ')</a>';
						
					echo '	
						<img src="', $settings['images_url'], '/selected.gif" alt="" /> <a class="dot" href="', $scripturl, '?action=unread">', $txt['view_unread'], '</a>
							<img src="', $settings['images_url'], '/selected.gif" alt="" /> <a class="dot" href="', $scripturl, '?action=unreadreplies">', $txt['view_replies'], '</a> 
						 <img src="', $settings['images_url'], '/selected.gif" alt="" /> ', $txt['forum_stats'] ,' -
				', $context['common_stats']['total_posts'], ' ', $txt['posts'], ' - ', $context['common_stats']['total_topics'], ' 
				', $txt['topics'], ' - ', $context['common_stats']['total_members'], ' ', $txt['members'], '
			</div>
		</div> 
	</div>
	<div id="main_body">
		<div class="wrapper">
			<div class="fwt">
				<div class="fwtr">
					<div class="fwtm"></div>
				</div>
			</div>
			<div class="fwl">
				<div class="fwr">
					<div class="fwm">';
					// Show the navigation tree.
					theme_linktree();
					
}

function template_body_below()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

				echo '
					</div>
				</div>
			</div>
			<div class="fwb">
				<div class="fwbr">
					<div class="fwbm"></div>
				</div>
			</div>
		</div>
	</div>';
	// Show the "Powered by" and "Valid" logos, as well as the copyright. Remember, the copyright must be somewhere!
	echo '
		<div id="footer" align="center">
			<div class="wrapper">
				<div id="footer_copy">
					',theme_copyright(),' | 
					<strong><a href="https://www.smfsimple.com" title="Themes para smf">Sunblox Theme</a> </strong>
                    <br /><hr />
					', parse_bbc(@$settings['sunbloxcopy']) ,'
				</div>
				<div class="clr"></div>';
				// Show the load time?
			if ($context['show_load_time'])
			echo '
				<div class="loadtime smalltext">
					', $txt['page_created'], $context['load_time'], $txt['seconds_with'], $context['load_queries'], $txt['queries'], '
				</div>';
			echo '
			</div>
		</div>';
}

function template_html_below()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
</body></html>';
}

// Show a linktree. This is that thing that shows "My Community | General Category | General Discussion"..
function theme_linktree($force_show = false)
{
	global $context, $settings, $options, $shown_linktree;

	if (empty($context['linktree']) || (!empty($context['dont_default_linktree']) && !$force_show))
		return;

	echo '
	<div class="navigate_section">
		<ul>';

	foreach ($context['linktree'] as $link_num => $tree)
	{
		echo '
			<li', ($link_num == count($context['linktree']) - 1) ? ' class="last"' : '', '>';

		if (isset($tree['extra_before']))
			echo $tree['extra_before'];

		echo $settings['linktree_link'] && isset($tree['url']) ? '
				<a href="' . $tree['url'] . '"><span>' . $tree['name'] . '</span></a>' : '<span>' . $tree['name'] . '</span>';
				
		if (isset($tree['extra_after']))
			echo $tree['extra_after'];

		if ($link_num != count($context['linktree']) - 1)
			echo ' &#187;';

		echo '
			</li>';
	}
	echo '
		</ul>
	</div>';

	$shown_linktree = true;
}

// Show the menu up top. Something like [home] [help] [profile] [logout]...
function template_menu()
{
	global $context, $settings, $options, $scripturl, $txt;

	echo '
			<ul id="menutop">';

	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
				<li id="button_', $act, '">
					<a class="', $button['active_button'] ? 'active ' : '', 'firstlevel" href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', '>
						<span class="', isset($button['is_last']) ? 'last ' : '', 'firstlevel">', $button['title'], '</span>
					</a>';
		if (!empty($button['sub_buttons']))
		{
			echo '
					<ul>';

			foreach ($button['sub_buttons'] as $childbutton)
			{
				echo '
						<li>
							<a href="', $childbutton['href'], '"', isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', '>
								<span', isset($childbutton['is_last']) ? ' class="subchild"' : '', '>', $childbutton['title'], !empty($childbutton['sub_buttons']) ? '...' : '', '</span>
							</a>';

				if (!empty($childbutton['sub_buttons']))
				{
					echo '
							<ul>';

					foreach ($childbutton['sub_buttons'] as $grandchildbutton)
						echo '
								<li>
									<a', $grandchildbutton['active_button'] ? ' class="active"' : '', ' href="', $grandchildbutton['href'], '"', isset($grandchildbutton['target']) ? ' target="' . $grandchildbutton['target'] . '"' : '', '>
										<span', isset($grandchildbutton['is_last']) ? ' class="last"' : '', '>', $grandchildbutton['title'], '</span>
									</a>
								</li>';

					echo '
						</ul>';
				}

				echo '
						</li>';
			}
			echo '
					</ul>';
		}
		echo '
				</li>';
	}

	echo '
			</ul>';
}

// Generate a strip of buttons.
function template_button_strip($button_strip, $direction = 'top', $strip_options = array())
{
	global $settings, $context, $txt, $scripturl;

	if (!is_array($strip_options))
		$strip_options = array();

	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		if (!isset($value['test']) || !empty($context[$value['test']]))
			$buttons[] = '
				<li><a' . (isset($value['id']) ? ' id="button_strip_' . $value['id'] . '"' : '') . ' class="button_strip_' . $key . '' . (isset($value['active']) ? ' active' : '') . '" href="' . $value['url'] . '"' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '><span>' . $txt[$value['text']] . '</span></a></li>';
	}

	if (empty($buttons))
		return;

	$buttons[count($buttons) - 1] = str_replace('<span>', '<span class="last">', $buttons[count($buttons) - 1]);

	echo '
		<div class="buttonlist', !empty($direction) ? ' align_' . $direction : '', '"', (empty($buttons) ? ' style="display: none;"' : ''), (!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"': ''), '>
			<ul>',
				implode('', $buttons), '
			</ul>
		</div>';
}

?>