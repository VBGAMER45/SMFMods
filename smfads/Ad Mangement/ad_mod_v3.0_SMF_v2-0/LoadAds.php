<?php
/******************************************************************************
* SMF: Simple Machines Forum - Ad Management Mod                              *
*                                                                             *
* =========================================================================== *
* Software Version:           Ad mod: 3.3                                     *
* Software by:                smfhacks.com                                    *
* Copyright 2010-2017 by:     smfhacks.com                                    *
* Support site:               www.smfads.com                                  *
*******************************************************************************
* This mod is free software; you may not redistribute or provide a modified   *
* version to redistribute.  This mod is distributed in the hope that it is    *
* and will be useful, but WITHOUT ANY WARRANTIES; without even any implied    *
* warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            *
******************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');
	
function loadads()
{
	global $smcFunc;	
	global $adverts, $context;
	
	// Skip attachments
	if (strpos($_SERVER['REQUEST_URL'], 'action=dlattach') === true)
		return;
	
	if (!isset($context['template_layers']))
		return;
	
	loadTemplate('Ads');
	loadLanguage('Ads');
	
	if (isset($context['template_layers']))
		$layerHolder = $context['template_layers'];
	$context['template_layers'] = array();
	if (isset($layerHolder ))
	foreach($layerHolder as $layer)
	{
		$context['template_layers'][] = $layer;
		if ($layer == 'html')
			$context['template_layers'][] = 'adsheaders';
		else if ($layer == 'body')
			$context['template_layers'][] = 'adsindex';
	}
	
	
		
	$results = $smcFunc['db_query']('', "
		SELECT *
		FROM {db_prefix}ads");
	while ($row = $smcFunc['db_fetch_assoc']($results))
	{
		$adverts[] = array(
			'id' => $row['ADS_ID'],
			'name' => $row['NAME'],
			'content' => $row['CONTENT'],
			'boards' => $row['BOARDS'],
			'posts' => $row['POSTS'],
			'category' => $row['CATEGORY'],
			'hits' => $row['HITS'],
			'type' => $row['TYPE'],
			'show_index' => $row['show_index'],
			'show_board' => $row['show_board'],
			'show_threadindex' => $row['show_threadindex'],
			'show_lastpost' => $row['show_lastpost'],
			'show_thread' => $row['show_thread'],
			'show_bottom' => $row['show_bottom'],
			'show_welcome' => $row['show_welcome'],
			'show_topofpage' => $row['show_topofpage'],
			'show_towerright' => $row['show_towerright'],
			'show_towerleft' => $row['show_towerleft'],
			'show_underchildren' => $row['show_underchildren'],
		);
	}
	
	$smcFunc['db_free_result']($results);
	$adverts = stripslashes__recursive($adverts);
		
			
}	

function show_threadindexAds()
{
	return showAds('show_threadindex');
}

function show_boardAds()
{
	return showAds('show_board');
}

function show_threadAds()
{
	return showAds('show_thread');
}

function show_bottomAds()
{
	return showAds('show_bottom');
}

function show_indexAds()
{
	return showAds('show_index');
}

function show_towerleftAds()
{
	return showAds('show_towerleft');
}

function show_towerrightAds()
{
	return showAds('show_towerright');	
}

function show_topofpageAds()
{
	return showAds('show_topofpage');
}

function show_welcomeAds()
{
	return showAds('show_welcome');
}

function show_lastpostAds()
{
	return showAds('show_lastpost');
}

function show_underchildren()
{
	return showAds('show_underchildren');
}


function show_posts($postcount)
{
	global $board, $adverts, $modSettings;
	
	//Quickly check the settings to display ads for Admins
	if(!empty($modSettings['ads_displayAdsAdmin']) && $modSettings['ads_displayAdsAdmin'] == 1 && allowedTo('admin'))
		return ;
		
	//Quickly check if all ads should be disabled
	if(!empty($modSettings['ads_quickDisable']) && $modSettings['ads_quickDisable'] == 1)
		return ;
	
	//Only want to go in here if there are ads!
	if(!empty($adverts) && allowedTo('ad_manageperm'))
	{
		$displayAds = array();
		$displayBoardAds = array();
		//Find which ads should display in this area.		
		for ($i=0;$i<count($adverts);$i++)
		{
			if(($adverts[$i]['posts']!="" && in_array($postcount, explode(',', $adverts[$i]['posts']))) && ($adverts[$i]['boards']!="" && in_array($board, explode(',', $adverts[$i]['boards']))))
				$displayBoardAds[] = $adverts[$i];
			elseif($adverts[$i]['posts']!="" && in_array($postcount, explode(',', $adverts[$i]['posts'])) && $adverts[$i]['boards']=="")
				$displayAds[] = $adverts[$i];
		}
				//Did we find any ads?
		if(!empty($displayAds) || !empty($displayBoardAds))
		{

			//If the board specific ads array is empty, then display normal ads
			if(empty($displayBoardAds))
			{			
				$adtemp = array_rand($displayAds);
				
				//Update the hit counter for this ad
				if(empty($modSettings['ads_updateReports']))
					updateAddHits($displayAds[$adtemp]['id']);
				//Return all information about the post
				return $displayAds[$adtemp];

			}
			//else this is a board specific ads, therefor only display ads for that certain board
			else
			{		
				$adtemp = array_rand($displayBoardAds);
				
				//Update the hit counter for this ad
				if(empty($modSettings['ads_updateReports']))
					updateAddHits($displayBoardAds[$adtemp]['id']);
				//Return all information about the post
				return $displayBoardAds[$adtemp];
			}			
		}
	}		
		
}

function showAds($type)
{
	global $board, $adverts, $modSettings;

	// For security reasons, we don't want to display ads if the action is "admin" or "admod"
	if(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'admod' || $_REQUEST['action'] == 'admin' || $_REQUEST['action'] == 'login' || $_REQUEST['action'] == 'login2' || $_REQUEST['action'] == 'register' || $_REQUEST['action'] == 'register2' || $_REQUEST['action'] == 'reminder'))
		return ;
	
	//Quickly check the settings to display ads for Admins
	if(!empty($modSettings['ads_displayAdsAdmin']) && $modSettings['ads_displayAdsAdmin'] == 1 && allowedTo('admin'))
		return ;
		
	// Quickly check if all ads should be disabled
	if(!empty($modSettings['ads_quickDisable']) && $modSettings['ads_quickDisable'] == 1)
		return ;
		
	//Only want to go in here if there are ads!
	if(!empty($adverts) && allowedTo('ad_manageperm'))
	{
		$displayAds = array();
		$displayBoardAds = array();
		//Find which ads should display in this area.		
		for ($i=0;$i<count($adverts);$i++)
		{
			//This is where all the board specific ads go
			if($adverts[$i][$type]==1 && $adverts[$i]['boards']!="" && in_array($board, explode(',', $adverts[$i]['boards'])))
				$displayBoardAds[] = $adverts[$i];
			//This is where the non board specific ads go
			elseif($adverts[$i][$type]==1 && $adverts[$i]['boards']=="")
				$displayAds[] = $adverts[$i];

		}	

		//Did we find any ads?
		if(!empty($displayAds) || !empty($displayBoardAds))
		{

			//If the board specific ads array is empty, then display normal ads
			if(empty($displayBoardAds))
			{			
				$adtemp = array_rand($displayAds);
				//Only update if we have reports enabled
				if(empty($modSettings['ads_updateReports']))
					updateAddHits($displayAds[$adtemp]['id']);
				//Return all information about the post
				return $displayAds[$adtemp];
			}
			//else this is a board specific ads, therefor only display ads for that certain board
			else
			{		
				$adtemp = array_rand($displayBoardAds);
				//Only update if we have reports enabled
				if(empty($modSettings['ads_updateReports']))
					updateAddHits($displayBoardAds[$adtemp]['id']);
				//Return all information about the post
				return $displayBoardAds[$adtemp];
			}
			
		}
	}
}

function show_category($cat)
{
	global $board, $adverts, $modSettings;
	
	//Quickly check the settings to display ads for Admins
	if(!empty($modSettings['ads_displayAdsAdmin']) && $modSettings['ads_displayAdsAdmin'] == 1 && allowedTo('admin'))
		return ;
		
	//Quickly check if all ads should be disabled
	if(!empty($modSettings['ads_quickDisable']) && $modSettings['ads_quickDisable'] == 1)
		return ;
	
	//Only want to go in here if there are ads!
	if(!empty($adverts) && allowedTo('ad_manageperm'))
	{
		$displayAds = array();
		//Find which ads should display in this area.		
		for ($i=0;$i<count($adverts);$i++)
		{
			if(in_array($cat, explode(',', $adverts[$i]['category'])))
				$displayAds[] = $adverts[$i];
		}
				//Did we find any ads?
		if(!empty($displayAds))
		{		
				$adtemp = array_rand($displayAds);
				
				//Update the hit counter for this ad
				if(empty($modSettings['ads_updateReports']))
					updateAddHits($displayAds[$adtemp]['id']);
				//Return all information about the post
				return $displayAds[$adtemp];
						
		}
	}
}

function updateAddHits($id)
{
	global $smcFunc;
	$smcFunc['db_query']('',"UPDATE {db_prefix}ads SET hits = hits+1 WHERE ADS_ID = $id");	
	
}	
	
?>