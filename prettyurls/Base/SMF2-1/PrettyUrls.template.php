<?php
/*
Pretty Urls
*/


//	Pretty URLs chrome
function template_pretty_chrome_above()
{
	global $context;

	echo '
<div id="chrome">
	<div id="chrome_main">';

	if (isset($context['pretty']['chrome']['admin']))
	{
		//	Any notices?
		if (isset($context['pretty']['chrome']['notice']))
			echo '
		<div class="infobox">', $context['pretty']['chrome']['notice'], '</div>';
	}
}

function template_pretty_chrome_below()
{
	echo '
	</div>
</div>';
}

//	Mini template for successful mod installs
function template_pretty_install()
{
	global $scripturl, $txt;

	echo '
		<p>', $txt['pretty_install_success'], '</p>
		<p><a href="', $scripturl, '?action=admin;area=pretty">', $txt['pretty_install_continue'], '</a></p>';
}

//	It should be easy and fun to manage this mod
function template_pretty_settings()
{
	global $context, $scripturl, $txt, $modSettings, $prettyurlsVersion;

	if (!isset($modSettings['pretty_bufferusecache']))
		$modSettings['pretty_bufferusecache'] = 0;

	echo '
 <div class="cat_bar">
		<h3 class="catbg">
        ' . $txt['pretty_chrome_menu_settings'] .'
        </h3>
  </div>

	<table border="0" cellpadding="0" cellspacing="0" width="100%">


	<tr>
	    <td width="50%" colspan="2"  class="windowbg2">

		<form action="', $scripturl, '?action=admin;area=pretty;sa=settings;save" method="post" accept-charset="', $context['character_set'], '">
			<fieldset>
				<legend>', $txt['pretty_core_settings'], '</legend>
				<label for="pretty_enable">', $txt['pretty_enable'], '</label>
				<input type="hidden" name="pretty_enable" value="0" />
				<input type="checkbox" name="pretty_enable" id="pretty_enable"', ($context['pretty']['settings']['enable'] ? ' checked="checked"' : ''), ' />

				<br />
				<label for="pretty_root_url">', $txt['pretty_root_url'], '</label>
				<input type="text" name="pretty_root_url" id="pretty_root_url" value="', (isset($modSettings['pretty_root_url']) ? $modSettings['pretty_root_url'] : ''), '" size="50" />


				<br />
				<label for="pretty_skipactions">', $txt['pretty_skipactions'], '</label>
				<input type="text" name="pretty_skipactions" id="pretty_skipactions" value="', (isset($modSettings['pretty_skipactions']) ? $modSettings['pretty_skipactions'] : ''), '" size="50" />
				<br />
				<span class="smalltext">',$txt['pretty_skipactions_note'],'</span><br />
				<label for="pretty_bufferusecache">', $txt['pretty_bufferusecache'], '</label>
				<input type="checkbox" name="pretty_bufferusecache" id="pretty_bufferusecache"', ($modSettings['pretty_bufferusecache'] ? ' checked="checked"' : ''), ' />
		
			</fieldset>
			<fieldset>
				<legend>', $txt['pretty_filters'], '</legend>';

	//	Display the filters
	foreach ($context['pretty']['filters'] as $id => $filter)
		echo '
				<div>
					<input type="checkbox" name="pretty_filter_', $id, '" id="pretty_filter_', $id, '"', ($filter['enabled'] ? ' checked="checked"' : ''), ' />
					<label for="pretty_filter_', $id, '">', $filter['title'], '</label>
					<p>', $filter['description'], '</p>
				</div>';

	echo '
			</fieldset>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="submit" value="', $txt['pretty_save'], '" />

		</form>

		</td>
		</tr>
		</table>

		';



}

// Show a short list of rewritten test URLs
function template_pretty_test_rewrites()
{
	global $context, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=pretty;sa=test;save" method="post">
			<fieldset>', $context['pretty']['chrome']['linklist'], '</fieldset>
			<fieldset>
				<input type="submit" value="', $txt['pretty_enable'], '" />
			</fieldset>
		</form>';
}

//	Forum out of whack?
function template_pretty_maintenance()
{
	global $context, $scripturl, $txt;

	echo ' <div class="cat_bar">
		<h3 class="catbg">
        ' . $txt['pretty_chrome_menu_maintenance'].'
        </h3>
  </div>
  	<table border="0" cellpadding="0" cellspacing="0" width="100%">

	<tr>
	    <td width="50%" colspan="2"  class="windowbg2">
  ';


	if (isset($context['pretty']['maintenance_tasks']))
	{
		echo '
		<ul>';
		foreach ($context['pretty']['maintenance_tasks'] as $task)
			echo '
			<li>', $task, '</li>';
		echo '
		</ul>';
	}
	else
		echo '
		<p><a href="', $scripturl, '?action=admin;area=pretty;sa=maintenance;run">', $txt['pretty_run_maintenance'], '</a></p>';

    echo '</table>';

}

//	To make it easier to edit that nasty filters array
function template_pretty_filters()
{
	global $context, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=pretty;sa=filters;save" method="post" accept-charset="', $context['character_set'], '">
			<textarea id="pretty_json_filters" name="pretty_json_filters" rows="20">', $context['pretty']['json_filters'], '</textarea>
			<input type="submit" value="', $txt['pretty_save'], '" />
		</form>';
}

function template_pretty_nginx()
{
	global $txt, $context;

	echo '
<div class="cat_bar">
		<h3 class="catbg">
        ' . $txt['pretty_chrome_menu_nginx'].'
        </h3>
  </div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
	    <td width="50%" colspan="2"  class="windowbg2">
		<p>', $txt['pretty_nginix_note'],  '</p>
		<p><textarea rows="20" cols="150">' . $context['pretty_nginx_rules'] . '</textarea></p>
		</td>
		</tr>
		</table>
		';
}

?>