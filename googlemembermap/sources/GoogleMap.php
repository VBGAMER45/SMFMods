<?php

/**
 * @package "Google Member Map" Mod for Simple Machines Forum (SMF) V2.0
 * @author Spuds
 * @copyright (c) 2011-2013 Spuds
 * @license This Source Code is subject to the terms of the Mozilla Public License
 * version 1.1 (the "License"). You can obtain a copy of the License at
 * http://mozilla.org/MPL/1.1/.
 *
 * @version 3.0
 *
 */

// Are we calling this directly, umm lets just say no
if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * gmm_main()
 *
 * Traffic cop, checks permissions
 * calls the template which in turn calls this to request the xml file or js file to template inclusion
 *
 * @return
 */
function gmm_main()
{
	global $context, $txt, $smcFunc;

	// Are we allowed to view the map?
	isAllowedTo('googleMap_view');
	loadLanguage('GoogleMap');

	// Build the XML data?
	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] === '.xml')
		return gmm_build_XML();

	// create the pins (urls) for use
	gmm_buildpins();

	// Build the JS data?
	if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == '.js')
		return gmm_build_JS();

	// load up our template and style sheet
	loadTemplate('GoogleMap', 'GoogleMap');

	// Lets find number of members that have placed their map pin for the template
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) as TOTAL
		FROM {db_prefix}members
		WHERE latitude <> false AND longitude <> false',
		array(
		)
	);
	$totalSet = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// show the map
	$context['total_pins'] = $totalSet[0];
	$context['sub_template'] = 'map';
	$context['page_title'] = $txt['googleMap'];
}

/**
 * gmm_build_JS()
 *
 * creates the javascript file based on the admin settings
 * called from the map template file via map sa = .js
 *
 * @return
 */
function gmm_build_JS()
{
	global $context, $scripturl, $txt, $modSettings;

	// Clean the buffer so we only return JS back to the template
	ob_end_clean();
	if (!empty($modSettings['enableCompressedOutput']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	header('Content-Type: application/javascript');

	// Start up the session URL fixer.
	ob_start('ob_sessrewrite');

	// Our push pins as defined from gmm_buildpins
	$npin = $modSettings['npin'];
	$cpin = $modSettings['cpin'];
	$mpin = $modSettings['mpin'];
	$fpin = $modSettings['fpin'];

	// Push Pin shadows as well?
	$mshd = (!empty($modSettings['googleMap_PinShadow'])) ? $mshd = '_withshadow' : $mshd = '';
	$cshd = (!empty($modSettings['googleMap_ClusterShadow'])) ? $cshd = '_withshadow' : $cshd = '';

	// Validate the specified pin size is not to small
	$m_iconsize = (isset($modSettings['googleMap_PinSize']) && $modSettings['googleMap_PinSize'] > 19) ? $modSettings['googleMap_PinSize'] : 20;
	$c_iconsize = (isset($modSettings['googleMap_ClusterSize']) && $modSettings['googleMap_ClusterSize'] > 19) ? $modSettings['googleMap_ClusterSize'] : 20;

	// scaling factors based on these W/H ratios to maintain aspect ratio and overall size
	// such that a mixed shadown./no sprite push pin appear the same size
	$m_iconscaled_w = !empty($mshd) ? $m_iconsize * 1.08 : $m_iconsize * .62;
	$m_iconscaled_h = $m_iconsize;

	$c_iconscaled_w = !is_int($cpin) ? (!empty($cshd) ? $c_iconsize * 1.08 : $c_iconsize * .62) : $c_iconsize;
	$c_iconscaled_h = $c_iconsize;

	// Set all those anchor points based on the scaled icon size, icon at pin mid bottom
	$m_iconanchor_w = (!empty($mshd)) ? $m_iconscaled_w / 3.0 : $m_iconscaled_w / 2.0;
	$m_iconanchor_h = $m_iconscaled_h;

	// Pin count
	$context['total_pins'] = isset($_REQUEST['count']) ? (int) $_REQUEST['count'] : 0;

	// lets start making some javascript
	echo '// globals
var xhr = false;

// arrays to hold copies of the markers and html used by the sidebar
var gmarkers = [],
	htmls = [],
	sidebar_html = "";

// map, cluster and info bubble
var map = null,
	mc = null,
	infowindow = null;

// icon locations
var codebase = "https://raw.githubusercontent.com/googlemaps/v3-utility-library/master/markerclustererplus",
	chartbase = "https://chart.apis.google.com/chart";

// our normal pin to show on the map
var npic = {
	url: chartbase + "' . $npin . '",
	size: null,
	origin: null,
	anchor: new google.maps.Point(' . $m_iconanchor_w . ', ' . $m_iconanchor_h . '),
	scaledSize: new google.maps.Size(' . $m_iconscaled_w . ', ' . $m_iconscaled_h . ')
};';

// Gender pins as well?
	if (!empty($modSettings['googleMap_PinGender']))
		echo '
// The Gender Pins
var fpic = {
	url: chartbase + "' . $fpin . '",
	size: null,
	origin: null,
	anchor: new google.maps.Point(' . $m_iconanchor_w . ', ' . $m_iconanchor_h . '),
	scaledSize: new google.maps.Size(' . $m_iconscaled_w . ', ' . $m_iconscaled_h . ')
};

var mpic = {
	url: chartbase + "' . $mpin . '",
	size: null,
	origin: null,
	anchor: new google.maps.Point(' . $m_iconanchor_w . ', ' . $m_iconanchor_h . '),
	scaledSize: new google.maps.Size(' . $m_iconscaled_w . ', ' . $m_iconscaled_h . ')
};';

// Cluster Pin Styles
	if (!empty($modSettings['googleMap_EnableClusterer']))
		echo '

// various cluster pin styles
var styles = [[
	{url: chartbase + "' . $cpin . '", width: ' . $c_iconscaled_w . ', height: ' . $c_iconscaled_h . '},
	{url: chartbase + "' . $cpin . '", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.3 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.2 : 1) . '},
	{url: chartbase + "' . $cpin . '", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.6 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.4 : 1) . '},
	{url: chartbase + "' . $cpin . '", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.9 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.6 : 1) . '},
	{url: chartbase + "' . $cpin . '", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 2.1 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.8 : 1) . '}
],[
	{url: codebase + "/images/m1.png", width: ' . $c_iconscaled_w . ', height: ' . $c_iconscaled_h . '},
	{url: codebase + "/images/m2.png", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.2 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.2 : 1) . '},
	{url: codebase + "/images/m3.png", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.4 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.4 : 1) . '},
	{url: codebase + "/images/m4.png", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.6 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.6 : 1) . '},
	{url: codebase + "/images/m5.png", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.8 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.8 : 1) . '}
],[
	{url: codebase + "/images/people35.png", width: ' . $c_iconscaled_w . ', height: ' . $c_iconscaled_h . '},
	{url: codebase + "/images/people45.png", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.4 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.4 : 1) . '},
	{url: codebase + "/images/people55.png", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.8 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.8 : 1) . '}
],[
	{url: codebase + "/images/conv30.png", width: ' . $c_iconscaled_w . ', height: ' . $c_iconscaled_h . '},
	{url: codebase + "/images/conv40.png", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.4 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.4 : 1) . '},
	{url: codebase + "/images/conv50.png", width: ' . $c_iconscaled_w * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.8 : 1) . ', height: ' . $c_iconscaled_h * (!empty($modSettings['googleMap_ScalableCluster']) ? 1.8 : 1) . '}
]];

// who does not like a good old fashioned cluster, cause thats what we have here
var style = ' . (is_int($cpin) ? $cpin : 0) . ';
var mcOptions = {
		gridSize: ' . (!empty($modSettings['googleMap_GridSize']) ? $modSettings['googleMap_GridSize'] : 2) . ',
		maxZoom: 6,
		averageCenter: true,
		zoomOnClick: false,
		minimumClusterSize: ' . (!empty($modSettings['googleMap_MinMarkerPerCluster']) ? $modSettings['googleMap_MinMarkerPerCluster'] : 60) . ',
		title: "' . $txt['googleMap_GroupOfPins'] . '",
		styles: styles[style],
	};';

	echo '

// functions to read xml data
function makeRequest(url) {
	if (window.XMLHttpRequest) {
		xhr = new XMLHttpRequest();
	} else {
		if (window.ActiveXObject) {
			try {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) { }
		}
	}

	if (xhr) {
		xhr.onreadystatechange = showContents;
		xhr.open("GET", url, true);
		xhr.send(null);
	} else {
		document.write("' . $txt['googleMap_xmlerror'] . '");
	}
}

function showContents() {
	var xmldoc = \'\';

	if (xhr.readyState === 4)
	{
		// Run on server (200) or local machine (0)
		if (xhr.status === 200 || xhr.status === 0) {
			xmldoc = xhr.responseXML;
			makeMarkers(xmldoc);
		} else {
			document.write("' . $txt['googleMap_error'] . ' - " + xhr.status);
		}
	}
}

// Create the map and load our data
function initialize() {
	// create the map
	var latlng = new google.maps.LatLng(' . (!empty($modSettings['googleMap_DefaultLat']) ? $modSettings['googleMap_DefaultLat'] : 0) . ', ' . (!empty($modSettings['googleMap_DefaultLong']) ? $modSettings['googleMap_DefaultLong'] : 0) . ');
	var options = {
		zoom: ' . $modSettings['googleMap_DefaultZoom'] . ',
		center: latlng,
		scrollwheel: false,
		mapTypeId: google.maps.MapTypeId.' . $modSettings['googleMap_Type'] . ',
		mapTypeControlOptions: {
			style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
		},
		zoomControl: true,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.' . $modSettings['googleMap_NavType'] . '
		}
	};

	map = new google.maps.Map(document.getElementById("map"), options);

	// load the members
	makeRequest("' . $scripturl . '?action=googlemap;sa=.xml");

	// Our own initial state button since its gone walkies in the v3 api
	var reset = document.getElementById("googleMapReset");
	reset.style.filter = "alpha(opacity=0)";
	reset.style.mozOpacity = "0";
	reset.style.opacity = "0";
}

// Read the output of the marker xml
function makeMarkers(xmldoc) {
	var markers = xmldoc.documentElement.getElementsByTagName("marker"),
		point = null,
		html = null,
		label = null,
		marker = null;

	for (var i = 0; i < markers.length; ++i) {
		point = new google.maps.LatLng(parseFloat(markers[i].getAttribute("lat")), parseFloat(markers[i].getAttribute("lng")));
		html = markers[i].childNodes[0].nodeValue;
		label = markers[i].getAttribute("label");';

	if (!empty($modSettings['googleMap_PinGender']))
		echo '
		if (parseInt(markers[i].getAttribute("gender")) === 0)
			marker = createMarker(point, npic, label, html, i);

		if (parseInt(markers[i].getAttribute("gender")) === 1)
			marker = createMarker(point, mpic, label, html, i);

		if (parseInt(markers[i].getAttribute("gender")) === 2)
			marker = createMarker(point, fpic, label, html, i);
	}';
	else
		echo '
		marker = createMarker(point, npic, label, html, i);
	}';

	// clustering enabled and we have enough pins?
	if (!empty($modSettings['googleMap_EnableClusterer']) && ($context['total_pins'] > (!empty($modSettings['googleMap_MinMarkertoCluster']) ? $modSettings['googleMap_MinMarkertoCluster'] : 0)))
		echo '
	// send the markers array to the cluster script
	mc = new MarkerClusterer(map, gmarkers, mcOptions);

	google.maps.event.addListener(mc, "clusterclick", function(cluster) {
		if (infowindow)
			infowindow.close();

		var clusterMarkers = cluster.getMarkers();
		map.setCenter(cluster.getCenter());

		// build the info window content
		var content = "<div style=\"text-align:left\">",
			numtoshow = Math.min(cluster.getSize(),', $modSettings['googleMap_MaxLinesCluster'], ');

		for (var i = 0; i < numtoshow; ++i) {
			content = content + "<img src=\"" + clusterMarkers[i].icon.url + "\" width=\"12\" height=\"12\" />   " + clusterMarkers[i].title + "<br />";
		}

		if (cluster.getSize() > numtoshow)
			content = content + "<br />', $txt['googleMap_Plus'], ' [" + (cluster.getSize() - numtoshow) + "] ', $txt['googleMap_Otherpins'], '";
		content = content + "</div>";

		infowindow = new google.maps.InfoWindow;
		myLatlng = new google.maps.LatLng(cluster.getCenter().lat(), cluster.getCenter().lng());
		infowindow.setPosition(myLatlng);
		infowindow.setContent(content);
		infowindow.open(map);
	});';

	echo '
	// put the assembled sidebar_html contents into the sidebar div
	document.getElementById("googleSidebar").innerHTML = sidebar_html;

}

// Create a marker and set up the event window
function createMarker(point, pic, name, html, i) {
	// map marker
	var marker = new google.maps.Marker({
		position: point,
		map: map,
		icon: pic,
		clickable: true,
		title: name.replace(/\[b\](.*)\[\/b\]/gi, "$1")
	});

	// listen for a marker click
	google.maps.event.addListener(marker, "click", function() {
		if (infowindow)
			infowindow.close();
		infowindow = new google.maps.InfoWindow({content: html, maxWidth:240});
		infowindow.open(map, marker);
	});

	// save the info used to populate the sidebar
	gmarkers.push(marker);
	htmls.push(html);
	name = name.replace(/\[b\](.*)\[\/b\]/gi, "<strong>$1</strong>");

	// add a line to the sidebar html';
	if ($modSettings['googleMap_Sidebar'] !== 'none')
		echo '
	sidebar_html += \'<a href="javascript:finduser(\' + i + \')">\' + name + \'</a><br /> \';
}

// Picks up the sidebar click and opens the corresponding info window
function finduser(i) {
	if (infowindow)
		infowindow.close();

	var marker = gmarkers[i]["position"];
	infowindow = new google.maps.InfoWindow({content: htmls[i], maxWidth:240});
	infowindow.setPosition(marker);
	infowindow.open(map);
}

// Resets the map to the inital zoom/center values
function resetMap() {
	// close any info windows we may have opened
	if (infowindow)
		infowindow.close();

	map.setCenter(new google.maps.LatLng(' . (!empty($modSettings['googleMap_DefaultLat']) ? $modSettings['googleMap_DefaultLat'] : 0) . ', ' . (!empty($modSettings['googleMap_DefaultLong']) ? $modSettings['googleMap_DefaultLong'] : 0) . '));
    map.setZoom(' . $modSettings['googleMap_DefaultZoom'] . ');
    // map.setMapTypeId(google.maps.MapTypeId.' . $modSettings['googleMap_Type'] . ');
}

google.maps.event.addDomListener(window, "load", initialize);';

	obExit(false);
}

/**
 * gmm_build_XML()
 *
 * creates the xml data for use on the map
 * pin info window content
 * map sidebar layout
 *
 * @return
 */
function gmm_build_XML()
{
	global $smcFunc, $context, $settings, $options, $scripturl, $txt, $modSettings, $user_info, $memberContext;

	// Make sure the buffer is empty so we return clean XML to the template
	ob_end_clean();

	// PMXsef grabs xml output and starts another gz buffer, users could add an ignore action in the PMX cp, but that assumes ...
	if (!empty($modSettings['enableCompressedOutput']) && empty($context['pmx']['pmxsef_enable']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	// Start up the session URL fixer.
	ob_start('ob_sessrewrite');

	// XML Header
	header('Content-Type: application/xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

	// Lets find number of members have set their map
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) as TOTAL
		FROM {db_prefix}members
		WHERE latitude <> false AND longitude <> false',
		array(
		)
	);
	$totalSet = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Can we show all these pins or is a limit set?
	if ($totalSet[0] >= $modSettings['googleMap_PinNumber'] && $modSettings['googleMap_PinNumber'] != 0)
	{
		// More pins then we can show so load the data up at random to the number set in the admin panel
		$query = 'SELECT id_member
		FROM {db_prefix}members
		WHERE latitude <> false AND longitude <> false
		ORDER BY RAND()
		LIMIT 0, {int:max_pins_to_show}';
	}
	else
	{
		// Showing them all, load everyone ... with recently moved as first in the list
		$query = 'SELECT id_member, real_name, IF(pindate > {int:last_week}, pindate, 0) AS pindate
		FROM {db_prefix}members
		WHERE latitude <> false AND longitude <> false
		ORDER BY pindate DESC, real_name ASC';
	}

	// with the SQL request defined, lets make the query
	$last_week = time() - (7 * 24 * 60 * 60);
	$request = $smcFunc['db_query']('',
		$query,
		array(
			'last_week' => $last_week,
			'max_pins_to_show' => isset($modSettings['googleMap_PinNumber']) ? $modSettings['googleMap_PinNumber'] : 0,
		)
	);

	// Load the pins
	$temp = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$temp[] = $row['id_member'];
	$smcFunc['db_free_result']($request);

	// Load all of the data for these 'pined' members
	loadMemberData($temp);
	foreach ($temp as $v)
		loadMemberContext($v);
	unset($temp);

	// Begin the XML output
	echo '<?xml version="1.0" encoding="', $context['character_set'], '"?' . '>
	<markers>';
	if (isset($memberContext))
	{
		// to prevent the avatar being outside the popup window we need to set a max div height, since smf does not have
		// the avatar height available, google will misrender the div until it gets the image in cache you see
		$div_height = max(isset($modSettings['avatar_max_height_external']) ? $modSettings['avatar_max_height_external'] : 0, isset($modSettings['avatar_max_height_upload']) ? $modSettings['avatar_max_height_upload'] : 0);

		// for every member with a pin, build the info bubble ...
		foreach ($memberContext as $marker)
		{
			$datablurb = '';

			// guest don't get to see this ....
			if (!$user_info['is_guest'])
			{
				$datablurb = '
		<div class="googleMap">
			<h4>
				<a href="' . $marker['online']['href'] . '">
					<img src="' . $marker['online']['image_href'] . '" alt="' . $marker['online']['text'] . '" /></a>
				<a href="' . $marker['href'] . '">' . $marker['name'] . '</a>
			</h4>';

				// avatar?
				if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($marker['avatar']['image']))
					$datablurb .= '
				<div class="floatright" style="height:' . $div_height . 'px">' . $marker['avatar']['image'] . '<br /></div>';

				// user info section
				$datablurb .= '
			<div class="floatleft">
				<ul class="reset">';

				// Show the member's primary group (like 'Administrator') if they have one.
				if (!empty($marker['group']))
					$datablurb .= '
					<li class="membergroup">' . $marker['group'] . '</li>';

				// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
				if ((empty($settings['hide_post_group']) || $marker['group'] == '') && $marker['post_group'] != '')
					$datablurb .= '
					<li class="postgroup">' . $marker['post_group'] . '</li>';

				// groups stars
				$datablurb .= '
					<li class="stars">' . $marker['group_stars'] . '</li>';

				// show the title, if they have one
				if (!empty($marker['title']) && !$user_info['is_guest'])
					$datablurb .= '
					<li class="title">' . $marker['title'] . '</li>';

				// Show the profile, website, email address, and personal message buttons.
				if ($settings['show_profile_buttons'])
				{
					$datablurb .= '
					<li>';

					// messaging icons
					$datablurb .= '
						<ul>
							<li>' . $marker['icq']['link'] . '</li>
							<li>' . $marker['msn']['link'] . '</li>
							<li>' . $marker['aim']['link'] . '</li>
							<li>' . $marker['yim']['link'] . '</li>
						</ul>
						<ul>';

					// Don't show an icon if they haven't specified a website.
					if ($marker['website']['url'] != '' && !isset($context['disabled_fields']['website']))
						$datablurb .= '
							<li>
								<a href="' . $marker['website']['url'] . '" title="' . $marker['website']['title'] . '" target="_blank" class="new_win">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" height="16" alt="' . $marker['website']['title'] . '" border="0" />' : $txt['www']) . '</a>
							</li>';

					// Don't show the email address if they want it hidden.
					if (in_array($marker['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
						$datablurb .= '
							<li>
								<a href="' . $scripturl . '?action=emailuser;sa=email;uid=' . $marker['id'] . '">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" height="16" title="' . $txt['email'] . '" />' : $txt['email']) . '</a>
							</li>';

					// Show the PM tag
					$datablurb .= '
							<li>
								<a href="' . $scripturl . '?action=pm;sa=send;u=' . $marker['id'] . '">';
					$datablurb .= $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($marker['online']['is_online'] ? 'on' : 'off') . '.gif" height="16" border="0" />' : ($marker['online']['is_online'] ? $txt['pm_online'] : $txt['pm_offline']);
					$datablurb .= '</a>
							</li>
						</ul>
					</li>';
				}

				$datablurb .= '
				</ul>
			</div>';

				// Show their personal text?
				if (!empty($settings['show_blurb']) && $marker['blurb'] != '')
					$datablurb .= '
			<br class="clear" />' . $marker['blurb'];

				$datablurb .= '
		</div>';
			}

			// Let's bring it all together...
			$markers = '<marker lat="' . round($marker['googleMap']['latitude'], 8) . '" lng="' . round($marker['googleMap']['longitude'], 8) . '" ';

			if ($marker['gender']['name'] == $txt['male'])
				$markers .= 'gender="1"';
			elseif ($marker['gender']['name'] == $txt['female'])
				$markers .= 'gender="2"';
			else
				$markers .= 'gender="0"';

			if (!empty($modSettings['googleMap_BoldMember']) && $marker['googleMap']['pindate'] >= $last_week)
				$markers .= ' label="[b]' . $marker['name'] . '[/b]"><![CDATA[' . $datablurb . ']]></marker>';
			else
				$markers .= ' label="' . $marker['name'] . '"><![CDATA[' . $datablurb . ']]></marker>';

			echo $markers;
		}
	}
	echo '
	</markers>';

	// Ok we should be done with output, dump it to the template
	obExit(false);
}

/**
 * ShowKML()
 *
 * @return
 */
function gmm_show_KML()
{
	global $smcFunc, $settings, $options, $context, $scripturl, $txt, $modSettings, $user_info, $mbname, $themeUser, $memberContext;

	// Are we allowed to view the map?
	loadLanguage('GoogleMap');
	isAllowedTo('googleMap_view');

	// If it's not enabled, die.
	if (empty($modSettings['googleMap_KMLoutput_enable']))
		obExit(false);

	// Start off empty, we want a clean stream
	ob_end_clean();

	// PMXsef grabs xml output and starts another gz buffer, users could add an ignore action in the PMX cp, but that assumes ...
	if (!empty($modSettings['enableCompressedOutput']) && empty($context['pmx']['pmxsef_enable']))
		@ob_start('ob_gzhandler');
	else
		ob_start();

	// Start up the session URL fixer.
	ob_start('ob_sessrewrite');

	// It will be a file called ourforumname.kml
	header('Content-type: application/keyhole;');
	header('Content-Disposition: attachment; filename="' . $mbname . '.kml"');

	// Load all the data up, no need to limit an output file to the 'world'
	$request = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}members
		WHERE latitude <> false AND longitude <> false',
		array(
		)
	);

	// load the member data into $memberContext
	$temp = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$temp[] = $row['id_member'];

	loadMemberData($temp);
	foreach ($temp as $v)
		loadMemberContext($v);

	$smcFunc['db_free_result']($request);

	// Start building the output
	echo '<?xml version="1.0" encoding="', $context['character_set'], '"?' . '>
	<kml xmlns="http://www.opengis.net/kml/2.2"
	 xmlns:gx="http://www.google.com/kml/ext/2.2">
	<Folder>
		<name>' . $mbname . '</name>
		<open>1</open>';

	// create the pushpin styles ... just color really, all with a 80% transparancy
	echo '
	<Style id="member">
		<IconStyle>
			<color>CF', gmm_validate_color('googleMap_PinBackground', '66FF66'), '</color>
			<scale>1.0</scale>
		</IconStyle>
		<BalloonStyle>
		  <text><![CDATA[
		  <font face="verdana">$[description]</font>
		  <br clear="all"/>
		  $[geDirections]
		  ]]></text>
		</BalloonStyle>
	</Style>
	<Style id="cluster">
		<IconStyle>
			<color>CF', gmm_validate_color('googleMap_ClusterBackground', '66FF66'), '</color>
			<scale>1.0</scale>
		</IconStyle>
		<BalloonStyle>
		  <text><![CDATA[
		  <font face="verdana">$[description]</font>
		  <br clear="all"/>
		  $[geDirections]
		  ]]></text>
		</BalloonStyle>
	</Style>
	<Style id="female">
		<IconStyle>
			<color>CFFF0099</color>
			<scale>1.0</scale>
		</IconStyle>
		<BalloonStyle>
		  <text><![CDATA[
		  <font face="verdana">$[description]</font>
		  <br clear="all"/>
		  $[geDirections]
		  ]]></text>
		</BalloonStyle>
	</Style>
	<Style id="male">
		<IconStyle>
			<color>CF0066FF</color>
			<scale>1.0</scale>
		</IconStyle>
		<BalloonStyle>
		  <text><![CDATA[
		  <font face="verdana">$[description]</font>
		  <br clear="all"/>
		  $[geDirections]
		  ]]></text>
		</BalloonStyle>
	</Style>';

	if (isset($memberContext))
	{
		// Assuming we have data to work with...
		foreach ($memberContext as $marker)
		{
			// to prevent the avatar being outside the popup window we need to set a max div height, since smf does not have
			// the avatar height availalbe, google will misrender the div until it gets the image in cache you see
			$div_height = max(isset($modSettings['avatar_max_height_external']) ? $modSettings['avatar_max_height_external'] : 0, isset($modSettings['avatar_max_height_upload']) ? $modSettings['avatar_max_height_upload'] : 0);

			echo '
	<Placemark id="' . $marker['name'] . '">
		<description>
			<![CDATA[
				<div style="width:240px">
					<h4>
						<a href="' . $marker['online']['href'] . '">
							<img src="' . $marker['online']['image_href'] . '" alt="' . $marker['online']['text'] . '" /></a>
						<a href="' . $marker['href'] . '">' . $marker['name'] . '</a>
					</h4>';

			// avatar?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($marker['avatar']['image']))
				echo '
						<div style="float:right;height:' . $div_height . 'px">'
				. $marker['avatar']['image'] . '<br />
						</div>';

			// user info section
			echo '
					<div style="float:left;">
						<ul style="padding:0;margin:0;list-style:none;">';

			// Show the member's primary group (like 'Administrator') if they have one.
			if (!empty($marker['group']))
				echo '
							<li>' . $marker['group'] . '</li>';

			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $marker['group'] == '') && $marker['post_group'] != '')
				echo '
							<li>' . $marker['post_group'] . '</li>';

			// groups stars
			echo '
							<li>' . $marker['group_stars'] . '</li>';

			// show the title, if they have one
			if (!empty($marker['title']) && !$user_info['is_guest'])
				echo '
							<li>' . $marker['title'] . '</li>';

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
				echo '
							<li>';

				// messaging icons
				echo '
								<ul style="padding:0;margin:0;list-style:none;">
									<li>' . $marker['icq']['link'] . '</li>
									<li>' . $marker['msn']['link'] . '</li>
									<li>' . $marker['aim']['link'] . '</li>
									<li>' . $marker['yim']['link'] . '</li>
								</ul>
								<ul style="padding:0;margin:0;list-style:none;">';

				// Don't show an icon if they haven't specified a website.
				if ($marker['website']['url'] != '' && !isset($context['disabled_fields']['website']))
					echo '
									<li>
										<a href="' . $marker['website']['url'] . '" title="' . $marker['website']['title'] . '" target="_blank" class="new_win">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" height="16" alt="' . $marker['website']['title'] . '" border="0" />' : $txt['www']) . '</a>
									</li>';

				// Don't show the email address if they want it hidden.
				if (in_array($marker['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					echo '
									<li>
										<a href="' . $scripturl . '?action=emailuser;sa=email;uid=' . $marker['id'] . '">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" height="16" title="' . $txt['email'] . '" />' : $txt['email']) . '</a>
									</li>';

				// Show the PM tag
				echo '
									<li>
										<a href="' . $scripturl . '?action=pm;sa=send;u=' . $marker['id'] . '">';
				echo $settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/im_' . ($marker['online']['is_online'] ? 'on' : 'off') . '.gif" height="16" border="0" />' : ($marker['online']['is_online'] ? $txt['pm_online'] : $txt['pm_offline']);
				echo '					</a>
									</li>
								</ul>
							</li>';
			}

			echo '
						</ul>
					</div>
				</div>
			]]>
		</description>
		<name>' . $marker['name'] . '</name>
		<LookAt>
			<longitude>' . round($marker['googleMap']['longitude'], 8) . '</longitude>
			<latitude>' . round($marker['googleMap']['latitude'], 8) . '</latitude>
			<range>15000</range>
		</LookAt>';

			// pin color
			if (!empty($modSettings['googleMap_PinGender']))
			{
				if ($marker['gender']['name'] == 'Male')
					echo '
		<styleUrl>#male</styleUrl>';
				elseif ($marker['gender']['name'] == 'Female')
					echo '
		<styleUrl>#female</styleUrl>';
				else
					echo '
		<styleUrl>#member</styleUrl>';
			}
			else
				echo '
		<styleUrl>#member</styleUrl>';

			echo '
		<Point>
			<extrude>1</extrude>
			<altitudeMode>clampToGround</altitudeMode>
			<coordinates>' . round($marker['googleMap']['longitude'], 8) . ',' . round($marker['googleMap']['latitude'], 8) . ',0</coordinates>
		</Point>
	</Placemark>';
		}
	}
	echo '
	</Folder>
</kml>';

	// Ok done, should send everything now..
	obExit(false);
}

/**
 * gmm_buildpins()
 *
 * Does the majority of work in determining how the map pin should look based on admin settings
 *
 * @return
 */
function gmm_buildpins()
{
	global $modSettings;

	// lets work out all those options
	$modSettings['googleMap_ClusterBackground'] = gmm_validate_color('googleMap_ClusterBackground', 'FF66FF');
	$modSettings['googleMap_PinBackground'] = gmm_validate_color('googleMap_PinBackground', '66FF66');
	$modSettings['googleMap_ClusterForeground'] = gmm_validate_color('googleMap_ClusterForeground', '202020');
	$modSettings['googleMap_PinForeground'] = gmm_validate_color('googleMap_PinForeground', '202020');

	// what kind of pins have been chosen
	$mpin = gmm_validate_pin('googleMap_PinStyle', 'd_map_pin_icon');
	$cpin = gmm_validate_pin('googleMap_ClusterStyle', 'd_map_pin_icon');

	// shall we add in shadows
	$mshd = (isset($modSettings['googleMap_PinShadow']) && $modSettings['googleMap_PinShadow']) ? $mshd = '_withshadow' : $mshd = '';
	$cshd = (isset($modSettings['googleMap_ClusterShadow']) && $modSettings['googleMap_ClusterShadow']) ? $cshd = '_withshadow' : $cshd = '';

	// set the member style, icon or text
	if ($mpin == 'd_map_pin_icon')
		$mchld = ((isset($modSettings['googleMap_PinIcon']) && trim($modSettings['googleMap_PinIcon']) != '') ? $modSettings['googleMap_PinIcon'] : 'info');
	elseif ($mpin == 'd_map_pin_letter')
		$mchld = (isset($modSettings['googleMap_PinText']) && trim($modSettings['googleMap_PinText']) != '') ? $modSettings['googleMap_PinText'] : '';
	else
	{
		$mpin = 'd_map_pin_letter';
		$mchld = '';
	}

	// cluster pins style, icon, text or image
	if ($cpin == 'd_map_pin_icon')
		$cchld = ((isset($modSettings['googleMap_ClusterIcon']) && trim($modSettings['googleMap_ClusterIcon']) != '') ? $modSettings['googleMap_ClusterIcon'] : 'info');
	elseif ($cpin == 'd_map_pin_letter')
		$cchld = (isset($modSettings['googleMap_ClusterText']) && trim($modSettings['googleMap_ClusterText']) != '') ? $modSettings['googleMap_ClusterText'] : '';
	elseif (is_int($cpin))
		$cchld = '';
	else
	{
		$cpin = 'd_map_pin_letter';
		$cchld = '';
	}

	// and now for the colors
	$mchld .= '|' . $modSettings['googleMap_PinBackground'] . '|' . $modSettings['googleMap_PinForeground'];
	$cchld .= '|' . $modSettings['googleMap_ClusterBackground'] . '|' . $modSettings['googleMap_ClusterForeground'];

	// Build those pins
	$modSettings['npin'] = '?chst=' . $mpin . $mshd . '&chld=' . $mchld;
	$modSettings['cpin'] = is_int($cpin) ? $cpin : '?chst=' . $cpin . $cshd . '&chld=' . $cchld;

	// the gender pins follow the member pin format ....
	if ($mpin == 'd_map_pin_icon')
	{
		$modSettings['fpin'] = '?chst=d_map_pin_icon' . $mshd . '&chld=WCfemale|FF0099';
		$modSettings['mpin'] = '?chst=d_map_pin_icon' . $mshd . '&chld=WCmale|0066FF';
	}
	else
	{
		$modSettings['fpin'] = '?chst=d_map_pin_letter' . $mshd . '&chld=|FF0099|' . $modSettings['googleMap_PinForeground'];
		$modSettings['mpin'] = '?chst=d_map_pin_letter' . $mshd . '&chld=|0066FF|' . $modSettings['googleMap_PinForeground'];
	}
	return;
}

/**
 * gmm_validate_color()
 *
 * Makes sure we have a 6digit hex for the color definitions or sets a default value
 *
 * @param mixed $color
 * @param mixed $default
 * @return
 */
function gmm_validate_color($color, $default)
{
	global $modSettings;

	// no leading #'s please
	if (substr($modSettings[$color], 0, 1) == '#')
		$modSettings[$color] = substr($modSettings[$color], 1);

	// is it a hex
	if (!preg_match('~^[a-f0-9]{6}$~i', $modSettings[$color]))
		$modSettings[$color] = $default;

	return strtoupper($modSettings[$color]);
}

/**
 * gmm_validate_pin()
 *
 * outputs the correct goggle chart pin type based on selection
 *
 * @return
 */
function gmm_validate_pin($area, $default)
{
	global $modSettings;

	// return the type of pin requested
	if (isset($modSettings[$area]))
	{
		switch ($modSettings[$area])
		{
			case 'googleMap_plainpin':
				$pin = 'd_map_pin';
				break;
			case 'googleMap_textpin':
				$pin = 'd_map_pin_letter';
				break;
			case 'googleMap_iconpin':
				$pin = 'd_map_pin_icon';
				break;
			case 'googleMap_zonepin':
				$pin = 1;
				break;
			case 'googleMap_peepspin':
				$pin = 2;
				break;
			case 'googleMap_talkpin':
				$pin = 3;
				break;
			default:
				$pin = 'd_map_pin_icon';
		}
	}
	else
		$pin = $default;

	return $pin;
}