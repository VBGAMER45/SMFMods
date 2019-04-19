<?php

/**
 *
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

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * imb_googlemap()
 *
 * Menu Button hook, integrate_menu_buttons, called from subs.php
 * used to add top menu buttons
 *
 * @param mixed[] $buttons
 */
function imb_googlemap(&$buttons)
{
	global $txt, $scripturl, $modSettings;

	loadlanguage('GoogleMap');

	// where do we want to place this new button
	$insert_after = empty($modSettings['googleMap_ButtonLocation']) ? 'calendar' : $modSettings['googleMap_ButtonLocation'];
	$counter = 0;

	// find the location in the buttons array
	foreach ($buttons as $area => $dummy)
	{
		if (++$counter && $area == $insert_after)
			break;
	}

	// Define the new menu item(s)
	$new_menu = array(
		'googlemap' => array(
			'title' => $txt['googleMap'],
			'href' => $scripturl . '?action=googlemap',
			'show' => !empty($modSettings['googleMap_Enable']) && allowedTo('googleMap_view'),
			'icon' =>  (function_exists("set_tld_regex") ? 'googlemap.png' : 'server.gif'),
			'sub_buttons' => array(),
		)
	);

	// Insert the new items in the existing array with array-a-matic ...it slices, it dices, it puts it back together
	$buttons = array_merge(array_slice($buttons, 0, $counter), array_merge($new_menu, array_slice($buttons, $counter)));
}

/**
 * ilp_googlemap()
 *
 * Permissions hook, integrate_load_permissions, called from ManagePermissions.php
 * used to add new permisssions
 *
 * @param mixed[] $permissionGroups
 * @param mixed[] $permissionList
 * @param mixed[] $leftPermissionGroups
 * @param mixed[] $hiddenPermissions
 * @param mixed[] $relabelPermissions
 */
function ilp_googlemap(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$permissionList['membergroup']['googleMap_view'] = array(false, 'general', 'view_basic_info');
	$permissionList['membergroup']['googleMap_place'] = array(false, 'general', 'view_basic_info');
}

/**
 * ia_googlemap()
 *
 * Actions hook, integrate_actions, called from index.php
 * used to add new actions to the system
 *
 * @param mixed[] $actionArray
 */
function ia_googlemap(&$actionArray)
{
	$actionArray = array_merge($actionArray, array(
		'googlemap' => array('GoogleMap.php', 'gmm_main'),
		'.kml' => array('GoogleMap.php', 'gmm_show_KML'))
	);
}

/**
 * iaa_googlemap()
 *
 * Admin Hook, integrate_admin_areas, called from Admin.php
 * used to add/modify admin menu areas
 *
 * @param mixed[] $admin_areas
 */
function iaa_googlemap(&$admin_areas)
{
	global $txt;

	loadlanguage('GoogleMap');
	$admin_areas['config']['areas']['modsettings']['subsections']['googlemap'] = array($txt['googleMap']);
}

/**
 * imm_googlemap()
 *
 * Modifications hook, integrate_modify_modifications, called from ManageSettings.php
 * used to add new menu screens areas.
 *
 * @param mixed[] $sub_actions
 */
function imm_googlemap(&$sub_actions)
{
	$sub_actions['googlemap'] = 'ModifyGoogleMapSettings';
}

/**
 * ModifyGoogleMapSettings()
 */
function ModifyGoogleMapSettings()
{
	global $txt, $scripturl, $context, $settings, $sc;

	loadlanguage('GoogleMap');
	$context[$context['admin_menu_name']]['tab_data']['tabs']['googlemap']['description'] = $txt['googleMap_desc'];
	$config_vars = array(
		// Map - On or off?
		array('check', 'googleMap_Enable', 'postinput' => $txt['googleMap_license']),
		array('text', 'googleMap_Key', 'subtext' => $txt['googleMap_Key_info']),
		// Default Location/Zoom/Map Controls/etc
		array('title', 'googleMap_MapSettings'),
		array('select', 'googleMap_ButtonLocation', array(
				'home' => $txt['home'],
				'help' => $txt['help'],
				'search' => $txt['search'],
				'login' => $txt['login'],
				'register' => $txt['register'],
				'calendar' => $txt['calendar'],
				'profile' => $txt['profile'],
				'pm' => $txt['pm_short'])
		),
		array('float', 'googleMap_DefaultLat', 10, 'postinput' => $txt['googleMap_DefaultLat_info']),
		array('float', 'googleMap_DefaultLong', 10, 'postinput' => $txt['googleMap_DefaultLong_info']),
		array('int', 'googleMap_DefaultZoom', 'subtext' => $txt['googleMap_DefaultZoom_Info']),
		array('select', 'googleMap_Type', array(
				'ROADMAP' => $txt['googleMap_roadmap'],
				'SATELLITE' => $txt['googleMap_satellite'],
				'HYBRID' => $txt['googleMap_hybrid'])
		),
		array('select', 'googleMap_NavType', array(
				'LARGE' => $txt['googleMap_largemapcontrol3d'],
				'SMALL' => $txt['googleMap_smallzoomcontrol3d'],
				'DEFAULT' => $txt['googleMap_defaultzoomcontrol'])
		),
		array('check', 'googleMap_EnableLegend'),
		array('check', 'googleMap_KMLoutput_enable', 'subtext' => $txt['googleMap_KMLoutput_enable_info']),
		array('int', 'googleMap_PinNumber', 'subtext' => $txt['googleMap_PinNumber_info']),
		array('select', 'googleMap_Sidebar', array(
				'none' => $txt['googleMap_nosidebar'],
				'right' => $txt['googleMap_rightsidebar'],
				'left' => $txt['googleMap_leftsidebar'])
		),
		array('check', 'googleMap_BoldMember'),
		// Member Pin Style
		array('title', 'googleMap_MemeberpinSettings'),
		array('check', 'googleMap_PinGender'),
		array('text', 'googleMap_PinBackground', 6),
		array('text', 'googleMap_PinForeground', 6),
		array('select', 'googleMap_PinStyle', array(
				'googleMap_plainpin' => $txt['googleMap_plainpin'],
				'googleMap_textpin' => $txt['googleMap_textpin'],
				'googleMap_iconpin' => $txt['googleMap_iconpin'])
		),
		array('check', 'googleMap_PinShadow'),
		array('int', 'googleMap_PinSize', 2),
		array('text', 'googleMap_PinText', 10, 'postinput' => $txt['googleMap_PinText_info']),
		array('select', 'googleMap_PinIcon',
			array(
				'academy' => $txt['academy'],
				'activities' => $txt['activities'],
				'airport' => $txt['airport'],
				'amusement' => $txt['amusement'],
				'aquarium' => $txt['aquarium'],
				'art-gallery' => $txt['art-gallery'],
				'atm' => $txt['atm'],
				'baby' => $txt['baby'],
				'bank-dollar' => $txt['bank-dollar'],
				'bank-euro' => $txt['bank-euro'],
				'bank-intl' => $txt['bank-intl'],
				'bank-pound' => $txt['bank-pound'],
				'bank-yen' => $txt['bank-yen'],
				'bar' => $txt['bar'],
				'barber' => $txt['barber'],
				'beach' => $txt['beach'],
				'beer' => $txt['beer'],
				'bicycle' => $txt['bicycle'],
				'books' => $txt['books'],
				'bowling' => $txt['bowling'],
				'bus' => $txt['bus'],
				'cafe' => $txt['cafe'],
				'camping' => $txt['camping'],
				'car-dealer' => $txt['car-dealer'],
				'car-rental' => $txt['car-rental'],
				'car-repair' => $txt['car-repair'],
				'casino' => $txt['casino'],
				'caution' => $txt['caution'],
				'cemetery-grave' => $txt['cemetery-grave'],
				'cemetery-tomb' => $txt['cemetery-tomb'],
				'cinema' => $txt['cinema'],
				'civic-building' => $txt['civic-building'],
				'computer' => $txt['computer'],
				'corporate' => $txt['corporate'],
				'fire' => $txt['fire'],
				'flag' => $txt['flag'],
				'floral' => $txt['floral'],
				'helicopter' => $txt['helicopter'],
				'home' => $txt['home1'],
				'info' => $txt['info'],
				'landslide' => $txt['landslide'],
				'legal' => $txt['legal'],
				'location' => $txt['location1'],
				'locomotive' => $txt['locomotive'],
				'medical' => $txt['medical'],
				'mobile' => $txt['mobile'],
				'motorcycle' => $txt['motorcycle'],
				'music' => $txt['music'],
				'parking' => $txt['parking'],
				'pet' => $txt['pet'],
				'petrol' => $txt['petrol'],
				'phone' => $txt['phone'],
				'picnic' => $txt['picnic'],
				'postal' => $txt['postal'],
				'repair' => $txt['repair'],
				'restaurant' => $txt['restaurant'],
				'sail' => $txt['sail'],
				'school' => $txt['school'],
				'scissors' => $txt['scissors'],
				'ship' => $txt['ship'],
				'shoppingbag' => $txt['shoppingbag'],
				'shoppingcart' => $txt['shoppingcart'],
				'ski' => $txt['ski'],
				'snack' => $txt['snack'],
				'snow' => $txt['snow'],
				'sport' => $txt['sport'],
				'star' => $txt['star'],
				'swim' => $txt['swim'],
				'taxi' => $txt['taxi'],
				'train' => $txt['train'],
				'truck' => $txt['truck'],
				'wc-female' => $txt['wc-female'],
				'wc-male' => $txt['wc-male'],
				'wc' => $txt['wc'],
				'wheelchair' => $txt['wheelchair'],
			), 'postinput' => $txt['googleMap_PinIcon_info']
		),
		// Clustering Options
		array('title', 'googleMap_ClusterpinSettings'),
		array('check', 'googleMap_EnableClusterer', 'subtext' => $txt['googleMap_EnableClusterer_info']),
		array('int', 'googleMap_MinMarkerPerCluster'),
		array('int', 'googleMap_MinMarkertoCluster'),
		array('int', 'googleMap_GridSize'),
		array('check', 'googleMap_ScalableCluster', 'subtext' => $txt['googleMap_ScalableCluster_info']),
		// Clustering Style
		array('title', 'googleMap_ClusterpinStyle'),
		array('text', 'googleMap_ClusterBackground', 6),
		array('text', 'googleMap_ClusterForeground', 6),
		array('select', 'googleMap_ClusterStyle', array(
				'googleMap_plainpin' => $txt['googleMap_plainpin'],
				'googleMap_textpin' => $txt['googleMap_textpin'],
				'googleMap_iconpin' => $txt['googleMap_iconpin'],
				'googleMap_zonepin' => $txt['googleMap_zonepin'],
				'googleMap_peepspin' => $txt['googleMap_peepspin'],
				'googleMap_talkpin' => $txt['googleMap_talkpin'])
		),
		array('check', 'googleMap_ClusterShadow'),
		array('int', 'googleMap_ClusterSize', '2'),
		array('text', 'googleMap_ClusterText', 'postinput' => $txt['googleMap_PinText_info']),
		array('select', 'googleMap_ClusterIcon',
			array(
				'academy' => $txt['academy'],
				'activities' => $txt['activities'],
				'airport' => $txt['airport'],
				'amusement' => $txt['amusement'],
				'aquarium' => $txt['aquarium'],
				'art-gallery' => $txt['art-gallery'],
				'atm' => $txt['atm'],
				'baby' => $txt['baby'],
				'bank-dollar' => $txt['bank-dollar'],
				'bank-euro' => $txt['bank-euro'],
				'bank-intl' => $txt['bank-intl'],
				'bank-pound' => $txt['bank-pound'],
				'bank-yen' => $txt['bank-yen'],
				'bar' => $txt['bar'],
				'barber' => $txt['barber'],
				'beach' => $txt['beach'],
				'beer' => $txt['beer'],
				'bicycle' => $txt['bicycle'],
				'books' => $txt['books'],
				'bowling' => $txt['bowling'],
				'bus' => $txt['bus'],
				'cafe' => $txt['cafe'],
				'camping' => $txt['camping'],
				'car-dealer' => $txt['car-dealer'],
				'car-rental' => $txt['car-rental'],
				'car-repair' => $txt['car-repair'],
				'casino' => $txt['casino'],
				'caution' => $txt['caution'],
				'cemetery-grave' => $txt['cemetery-grave'],
				'cemetery-tomb' => $txt['cemetery-tomb'],
				'cinema' => $txt['cinema'],
				'civic-building' => $txt['civic-building'],
				'computer' => $txt['computer'],
				'corporate' => $txt['corporate'],
				'fire' => $txt['fire'],
				'flag' => $txt['flag'],
				'floral' => $txt['floral'],
				'helicopter' => $txt['helicopter'],
				'home' => $txt['home1'],
				'info' => $txt['info'],
				'landslide' => $txt['landslide'],
				'legal' => $txt['legal'],
				'location' => $txt['location1'],
				'locomotive' => $txt['locomotive'],
				'medical' => $txt['medical'],
				'mobile' => $txt['mobile'],
				'motorcycle' => $txt['motorcycle'],
				'music' => $txt['music'],
				'parking' => $txt['parking'],
				'pet' => $txt['pet'],
				'petrol' => $txt['petrol'],
				'phone' => $txt['phone'],
				'picnic' => $txt['picnic'],
				'postal' => $txt['postal'],
				'repair' => $txt['repair'],
				'restaurant' => $txt['restaurant'],
				'sail' => $txt['sail'],
				'school' => $txt['school'],
				'scissors' => $txt['scissors'],
				'ship' => $txt['ship'],
				'shoppingbag' => $txt['shoppingbag'],
				'shoppingcart' => $txt['shoppingcart'],
				'ski' => $txt['ski'],
				'snack' => $txt['snack'],
				'snow' => $txt['snow'],
				'sport' => $txt['sport'],
				'star' => $txt['star'],
				'swim' => $txt['swim'],
				'taxi' => $txt['taxi'],
				'train' => $txt['train'],
				'truck' => $txt['truck'],
				'wc-female' => $txt['wc-female'],
				'wc-male' => $txt['wc-male'],
				'wc' => $txt['wc'],
				'wheelchair' => $txt['wheelchair'],
			),
			'postinput' => $txt['googleMap_PinIcon_info']
		),
	);

	// Saving?
	if (isset($_GET['save']))
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=modsettings;sa=googlemap');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=googlemap';
	$context['settings_title'] = $txt['googleMap'];
	$context['settings_insert_below'] = '
	<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/jscolor/jscolor.js"></script>
	<script type="text/javascript">
		var myPicker1 = new jscolor.color(document.getElementById(\'googleMap_PinBackground\'), {});
		myPicker1.fromString(document.getElementById(\'googleMap_PinBackground\').value);

		var myPicker2 = new jscolor.color(document.getElementById(\'googleMap_PinForeground\'), {});
		myPicker2.fromString(document.getElementById(\'googleMap_PinForeground\').value);

		var myPicker3 = new jscolor.color(document.getElementById(\'googleMap_ClusterBackground\'), {});
		myPicker3.fromString(document.getElementById(\'googleMap_ClusterBackground\').value);

		var myPicker4 = new jscolor.color(document.getElementById(\'googleMap_ClusterForeground\'), {});
		myPicker4.fromString(document.getElementById(\'googleMap_ClusterForeground\').value);
	</script>';

	prepareDBSettingContext($config_vars);
}