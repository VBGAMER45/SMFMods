<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.4
*	Project manager: vicram10
*	Copyright 2011-2021
*	Powered by SMFSimple.com
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');
	
//Init Blocks
function up_init_blocks($position = "") {
	global $db_prefix, $context, $user_info, $ID_MEMBER;
	global $smcFunc, $ultimateportalSettings;

	//Ohhh.. UP it's the best.. only UP, can create this feature..
	if(!empty($ultimateportalSettings['up_reduce_site_overload']))
	{
		if((cache_get_data('up_bk', 1800)) === NULL)
		{
			$result = $smcFunc['db_query']('',"SELECT id, file, title, icon, position, progressive, active, personal, content, perms, bk_collapse, bk_no_title, bk_style
							FROM {db_prefix}ultimate_portal_blocks 
							WHERE active='checked' ".(!empty($position) ? "and position = '$position'" : "")."
							ORDER BY progressive");
		
			while( $row = $smcFunc['db_fetch_assoc']($result) ) 
			{
				$context['load_block_'.$row['position']] = 1;
				$context['blocks-init'][] = $row;
			}
			
			//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
			cache_put_data('up_bk', $context['blocks-init'], 1800);
			cache_put_data('load_block_right', $context['load_block_right'], 1800);
			cache_put_data('load_block_left', $context['load_block_left'], 1800);
			cache_put_data('load_block_center', $context['load_block_center'], 1800);

		}else{
			$blocks = cache_get_data('up_bk', 1800);
			$context['load_block_right'] = cache_get_data('load_block_right', 1800);
			$context['load_block_left'] = cache_get_data('load_block_left', 1800);
			$context['load_block_center'] = cache_get_data('load_block_center', 1800);			

			foreach($blocks as $bk => $value)
			{
				$context['blocks-init'][] = array(
					'id' => $value['id'],
					'file' => $value['file'],
					'title' => $value['title'],
					'icon' => $value['icon'],
					'position' => $value['position'],
					'progressive' => $value['progressive'],
					'active' => $value['active'],
					'personal' => $value['personal'],
					'content' => $value['content'],
					'perms' => $value['perms'],
					'bk_collapse' => $value['bk_collapse'],
					'bk_no_title' => $value['bk_no_title'],
					'bk_style' => $value['bk_style'],
				);
			}
		}
	}else{
		$result = $smcFunc['db_query']('',"SELECT id, file, title, icon, position, progressive, active, personal, content, perms, bk_collapse, bk_no_title, bk_style
						FROM {db_prefix}ultimate_portal_blocks 
						WHERE active='checked' ".(!empty($position) ? "and position = '$position'" : "")."
						ORDER BY progressive");
	
		while( $row = $smcFunc['db_fetch_assoc']($result) ) 
		{
			$context['load_block_'.$row['position']] = 1;
			$context['blocks-init'][] = $row;
		}		
	}
}

//Get Column
function up_get_column($position) {
	global $db_prefix, $sourcedir, $boarddir, $context, $user_info;
	global $ultimateportalSettings, $settings, $scripturl;
	global $options;

	//default position
	$position = isset($position) ? $position : "left";

	//Init Blocks
	$column = '';	

	if(!empty($context['load_block_'.$position]))
	{
		foreach($context['blocks-init'] as $row) {
			if ($row['position'] == $position ) {
				$id_block = $row['id'];
				$content = "";
				$perms = '';
				$perms = array();
				$title = $row['title'];
				$icon = $row['icon'];
				$active = $row['active'];
	
				if ($row['perms']) {
					$perms =  $row['perms'];
				}
				
				if(!$perms) {
					$perms = array();
				}
	
				$perms = !empty($perms) ? explode(',', $perms) : '';		
				$viewblock	= !empty($perms) ? false : true;		
				if ($viewblock === false)
				{
					foreach($user_info['groups'] as $group_id) 
						if(in_array($group_id, $perms)) {
							$viewblock = true;
						}
				}
		
				if ($active == "checked" && ($viewblock === true || $user_info['is_admin'])) {
					switch($row['personal']) {
						// HTML Block
						case '1':   if ($row['content'] != "") {
										$content = "".stripslashes($row['content'])."";
									}
									//Print
									$title = $user_info['is_admin'] ? '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .'">'. $title .'</a>' : $title;
									head_block($icon, $title, $id_block, $row['bk_collapse'], $row['bk_no_title'], $row['bk_style']);
									echo $content;
									footer_block(trim($row['bk_style']));
									break;
						 // PHP Block								
						case '2':   $title = $user_info['is_admin'] ? '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';type-php=created">'. $title .'</a>' : $title;
									head_block($icon, $title, $id_block, $row['bk_collapse'], $row['bk_no_title'], $row['bk_style']);
									require_once($boarddir."/up-php-blocks/".$row['file']);
									footer_block(trim($row['bk_style']));
									break;
						 // System Block			
						default:    $title = $user_info['is_admin'] ? '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';type-php=system">'. $title .'</a>' : $title;
									head_block($icon, $title, $id_block, $row['bk_collapse'], $row['bk_no_title'], $row['bk_style']);
									require_once($boarddir."/up-blocks/".$row['file']);
									footer_block(trim($row['bk_style']));
									break;
					}				
				}
			}
			unset($viewblock);
		}
	}
}

function head_block($icon, $title, $id_block = 0, $bk_collapse = 'on', $bk_no_title = '', $bk_style = 'on') {
	global $settings, $user_info, $options;
	global $ultimateportalSettings;
	
	$icon = '<img style="vertical-align: middle;" width="16" height="16" alt="'.$icon.'" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/'.$icon.$ultimateportalSettings['ultimate_portal_icons_extention'].'" />&nbsp;';
	
	//Now control if your theme, it's "Curve" or "Variation Curve"	
	echo '
	', (!empty($ultimateportalSettings['up_use_curve_variation']) ? '<span '. (!empty($bk_style) ? 'class="clear upperframe"' : '').'><span></span></span><div '. (!empty($bk_style) ? 'class="roundframe"' : '').'><div '. (!empty($bk_style) ? 'class="innerframe"' : '').'><div '. (!empty($bk_style) ? 'class="cat_bar"' : '') .'><h3 style="font-size:12px;" '. (!empty($bk_style) ? 'class="catbg"' : '') .'><span class="left"></span>' : '<table '. (!empty($bk_style) ? 'class="tborder"' : '') .' border="0" width="100%" cellpadding="5" cellspacing="1"><div '. (!empty($bk_style) ? 'class="catbg"' : '') .' width="100%" valign="middle" align="left">') ,'
		'.((!empty($ultimateportalSettings['ultimate_portal_enable_icons']) && empty($bk_no_title)) ? $icon : ''). (empty($bk_no_title) ? $title : '');
		if (!empty($id_block) && !empty($bk_collapse))
		{ 				
			echo '											
				<span onclick="collapseBlock('. $id_block .',\'image_collapse_'. $id_block .'\')" style="cursor: pointer;">
					'.  (($user_info['is_guest'] ? !empty($_COOKIE['up_bk_'.$id_block]) : !empty($options['up_bk_'.$id_block])) ? '<img id="image_collapse_'. $id_block .'" align="right" src="' . $settings['default_theme_url'] . '/images/ultimate-portal/expand.gif" alt="+" border="0" style="padding: 8px 0pt 0pt;"/>' : '<img id="image_collapse_'. $id_block .'" align="right" src="' . $settings['default_theme_url'] . '/images/ultimate-portal/collapse.gif" alt="-" border="0" style="padding: 8px 0pt 0pt;"/>') .'
				</span>';									
		}		
		echo '
	'. (!empty($ultimateportalSettings['up_use_curve_variation']) ? '</h3></div>' : '<div align="left" '. (!empty($bk_style) ? 'class="windowbg"' : '') .' width="100%">') .'
			<div id="up_bk_'. $id_block .'" '. (($user_info['is_guest'] ? !empty($_COOKIE['up_bk_'.$id_block]) : !empty($options['up_bk_'.$id_block])) ? ' style="display: none;"' : '') .'>';
	
}

function footer_block($bk_style = 'on') {
	global $settings, $user_info, $options;
	global $ultimateportalSettings;
	
	echo '
			</div>
		'. (!empty($ultimateportalSettings['up_use_curve_variation']) ? '</div></div><span'. (!empty($bk_style) ? ' class="lowerframe"' : '') .'><span></span></span>' : '</div>') .'';	
}

function up_print_page($left, $right, $content = '', $copyright = "", $icon = '', $title = '') 
{
	global $settings, $context, $txt,$options;
	global $ultimateportalSettings, $user_info;
	//Portal Above
	up_print_page_above($left, $right, $copyright, 0);
	head_block($icon, $title, 0);
	//Ok now print
	echo $content;
	footer_block();
	//Copyright?
	if (!empty($copyright))
	{
		echo '
					<div align="center">
						<strong>'. $copyright .'</strong>
					</div>';
	}
	//Portal Below	
	up_print_page_below($right);
}

function up_print_page_above($left, $right, $copyright = '', $call_forum = 0, $call_front_page = 0) {
	global $settings, $context, $txt,$options;
	global $ultimateportalSettings, $user_info;

	if (!empty($ultimateportalSettings['up_left_right_collapse'])) {

	if (!empty($call_forum))
	{
		echo	'
			<div align="center">';
			
		if (!empty($ultimateportalSettings['up_forum_enable_col_left']) && !empty($left))	
		{	
			echo 	'<img src="'.$settings['default_images_url'].'/ultimate-portal/icons/clickandbuyleft.png" alt="" />
				<span onclick="collapse(1,\'upleftcollapse\')" style="cursor: pointer;">
					<span id="upleftcollapse">	
					 '.  (($user_info['is_guest'] ? !empty($_COOKIE['up_left']) : !empty($options['up_left'])) ? $txt['ultport_unhide'] : $txt['ultport_hide']) .'
					</span>
					'. $txt['ultport_col_left'] .'
				</span>'; 
			
			echo (!empty($ultimateportalSettings['up_forum_enable_col_right'])  && !empty($right) && !empty($ultimateportalSettings['up_left_right_collapse'])) ? '|' : '';
		}	 			
		if (!empty($ultimateportalSettings['up_forum_enable_col_right']) && !empty($right) && !empty($ultimateportalSettings['up_left_right_collapse']))
			echo 	'
				<span onclick="collapse(2,\'uprightcollapse\')" style="cursor: pointer;">
					<span id="uprightcollapse">	
						'.  (($user_info['is_guest'] ? !empty($_COOKIE['up_right']) : !empty($options['up_right'])) ? $txt['ultport_unhide'] : $txt['ultport_hide']) .'
					</span>
					'. $txt['ultport_col_right'] .' <img src="'.$settings['default_images_url'].'/ultimate-portal/icons/clickandbuyright.png" alt="" />				
				</span>'; 
		
		echo	'		
			</div>';
	}else{
		
		echo	'
			<div align="center">';
			
		if (!empty($ultimateportalSettings['ultimate_portal_enable_col_left']) && !empty($left) && !empty($ultimateportalSettings['up_left_right_collapse']))	
		{	
			echo 	'<img src="'.$settings['default_images_url'].'/ultimate-portal/icons/clickandbuyleft.png" alt="" />
				<span onclick="collapse(1,\'upleftcollapse\')" style="cursor: pointer;">
					<span id="upleftcollapse">	
					 '.  (($user_info['is_guest'] ? !empty($_COOKIE['up_left']) : !empty($options['up_left'])) ? $txt['ultport_unhide'] : $txt['ultport_hide']) .'
					</span>
					'. $txt['ultport_col_left'] .'
				</span>'; 
			
			echo (!empty($ultimateportalSettings['ultimate_portal_enable_col_right'])  && !empty($right) && !empty($ultimateportalSettings['up_left_right_collapse'])) ? '|' : '';
		}	 			
		if (!empty($ultimateportalSettings['ultimate_portal_enable_col_right']) && !empty($right) && !empty($ultimateportalSettings['up_left_right_collapse']))
			echo '
				<span onclick="collapse(2,\'uprightcollapse\')" style="cursor: pointer;">
					<span id="uprightcollapse">	
						'.  (($user_info['is_guest'] ? !empty($_COOKIE['up_right']) : !empty($options['up_right'])) ? $txt['ultport_unhide'] : $txt['ultport_hide']) .'
					</span>
					'. $txt['ultport_col_right'] .'	<img src="'.$settings['default_images_url'].'/ultimate-portal/icons/clickandbuyright.png" alt="" />			
				</span>'; 
		
		echo '		
			</div>';
		
	}
	}
	
	//Global Announcements 
	if(!empty($ultimateportalSettings['up_news_global_announcement']))
	{
		//Now control if your theme, it's "Curve" or "Variation Curve"
			echo '
			'. (!empty($ultimateportalSettings['up_use_curve_variation']) ? '<table width="100%" cellpadding="5" cellspacing="1"><tr><td colspan="3" id="global_annoucements" align="left" valign="top" width="100%"><span class="clear upperframe"><span></span></span><div class="roundframe"><div class="innerframe"><div class="cat_bar"><h4 style="font-size:12px" class="catbg"><span class="left"></span>' : '<table class="tborder" width="100%" cellpadding="5" cellspacing="1"><tr><td class="catbg" id="global_annoucements" align="left" valign="top" width="100%">') .'
					<img style="vertical-align: middle;" alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/icons/info.png"/>&nbsp;'. $txt['ultport_announcement'] .'
					<span onclick="collapseBlock(\'announcement\',\'image_collapse_announcement\')" style="cursor: pointer;">
						'.  (($user_info['is_guest'] ? !empty($_COOKIE['up_bk_announcement']) : !empty($options['up_bk_announcement'])) ? '<img id="image_collapse_announcement" align="right" src="' . $settings['default_theme_url'] . '/images/ultimate-portal/expand.gif" alt="+" border="0" style="padding: 8px 0pt 0pt;"/>' : '<img id="image_collapse_announcement" align="right" src="' . $settings['default_theme_url'] . '/images/ultimate-portal/collapse.gif" alt="-" border="0" style="padding: 8px 0pt 0pt;"/>') .'
					</span>
		'. (!empty($ultimateportalSettings['up_use_curve_variation']) ? '</h4></div></div>' : '</td></tr><tr><td align="left" class="windowbg" width="100%">') .'
					<p class="smallpadding" id="up_bk_announcement" '. (($user_info['is_guest'] ? !empty($_COOKIE['up_bk_announcement']) : !empty($options['up_bk_announcement'])) ? ' style="display: none;"' : '') .'>
							'. parse_bbc($ultimateportalSettings['up_news_global_announcement']) .'
					</p>
		'. (!empty($ultimateportalSettings['up_use_curve_variation']) ? '</div><span class="lowerframe"><span></span></span>' : '') .'					
				</td>
			</tr>
			</table>';
	}else{
		echo '';
	}
	//End Announcements				

	//User Posts Module - Show Cover 
	if(!empty($ultimateportalSettings['user_posts_header_show']))
	{
		//Load Covers	
		UserPostCoverShow();
		//Now control if your theme, it's "Curve" or "Variation Curve"
		echo '
		'. (!empty($ultimateportalSettings['up_use_curve_variation']) ? '<table width="100%" cellpadding="5" cellspacing="1"><tr><td colspan="3" id="global_annoucements" align="left" valign="top" width="100%"><span class="clear upperframe"><span></span></span><div class="roundframe"><div class="innerframe"><div class="cat_bar"><h4 style="font-size:12px" class="catbg"><span class="left"></span>' : '<table class="tborder" width="100%" cellpadding="5" cellspacing="1"><tr><td class="catbg" align="left" valign="top" width="100%">') .'
				<img style="vertical-align: middle;" alt="" border="0" src="'.$settings['default_images_url'].'/ultimate-portal/main-links/user-posts.png"/>&nbsp;'. $txt['ultport_cover_show'] .'
				<span onclick="collapseBlock(\'user_posts_cover\',\'image_collapse_user_posts_cover\')" style="cursor: pointer;">
					'.  (($user_info['is_guest'] ? !empty($_COOKIE['up_bk_user_posts_cover']) : !empty($options['up_bk_user_posts_cover'])) ? '<img id="image_collapse_user_posts_cover" align="right" src="' . $settings['default_theme_url'] . '/images/ultimate-portal/expand.gif" alt="+" border="0" style="padding: 8px 0pt 0pt;"/>' : '<img id="image_collapse_user_posts_cover" align="right" src="' . $settings['default_theme_url'] . '/images/ultimate-portal/collapse.gif" alt="-" border="0" style="padding: 8px 0pt 0pt;"/>') .'
				</span>
		'. (!empty($ultimateportalSettings['up_use_curve_variation']) ? '</h4></div></div>' : '</td></tr><tr><td class="windowbg" id="global_user_posts" align="left" valign="top" width="100%">') .'
				<p class="smallpadding" id="up_bk_user_posts_cover" '. (($user_info['is_guest'] ? !empty($_COOKIE['up_bk_user_posts_cover']) : !empty($options['up_bk_user_posts_cover'])) ? ' style="display: none;"' : '') .'>
					'; 
			if (!empty($context['view_cover']))		
			{
				echo '
				<marquee scrollamount="6" scrolldelay="1" direction="left" onMouseOver="this.stop()" onMouseOut="this.start()" loop="true">';
					foreach($context['userpost_cover'] as $cover)
					{
						echo '<a href="'. $cover['link_topic'] .'">'. $cover['cover_img'] .'</a>&nbsp;';
					}
				echo '
				</marquee>';						
			}else{
				echo ''. $txt['ultport_cover_no_found'] .'';
			}		
	echo '	
				</p>
	'. (!empty($ultimateportalSettings['up_use_curve_variation']) ? '</div><span class="lowerframe"><span></span></span>' : '') .'
			</td>
		</tr>
		</table>';
	}else{
		echo '';			
	}
	//End User Post Module
	
	//Multiblock Header
	up_print_MultiBlock('header');
	
	echo '	
	<table id="up_bk_table_main" summary="" style="width:100%" cellpadding="3" cellspacing="0">
		<tr>';

		if(!empty($left) && !empty($context['load_block_left']))
		{
			echo '
			<td id="up_left" align="left" valign="top" width="'. ((!empty($ultimateportalSettings['ultimate_portal_width_col_left'])) ? $ultimateportalSettings['ultimate_portal_width_col_left'] : '15%') .'" '. ((empty($ultimateportalSettings['ultimate_portal_enable_col_left']) || ($user_info['is_guest'] ? !empty($_COOKIE['up_left']) : !empty($options['up_left']))) ? ' style="display: none;"' : '') .'>
				', up_get_column("left") ,'
			</td>';
		}
	
			echo '					
			<td align="left" valign="top" width="'. (!empty($ultimateportalSettings['ultimate_portal_width_col_center']) ? (empty($right) && empty($left) ? '100%' : $ultimateportalSettings['ultimate_portal_width_col_center']) : '70%') .'">';
			//Column Center			
			if(empty($call_forum) && !empty($call_front_page))
			{
				up_get_column("center");
			}
}

function up_print_page_below($right) {
	global $settings, $context, $txt,$options;
	global $ultimateportalSettings, $user_info;

			echo '	
			</td>';//CLose Column Center (Boards)
			
	//Column Right
	if(!empty($right) && !empty($context['load_block_right']))				
	{
		echo '
			<td id="up_right" align="left" valign="top" width="'. (!empty($ultimateportalSettings['ultimate_portal_width_col_right']) ? $ultimateportalSettings['ultimate_portal_width_col_right'] : '15%') .'" '. ((empty($ultimateportalSettings['ultimate_portal_enable_col_right']) || ($user_info['is_guest'] ? !empty($_COOKIE['up_right']) : !empty($options['up_right']))) ? ' style="display: none;"' : '') .'>
				', up_get_column("right") ,'
			</td>';
	}
	echo '
			</tr>
		</table>';			

	//Multiblock Footer
	up_print_MultiBlock('footer');
	
}

//Print Multiblock 
function up_print_MultiBlock($position) 
{
	global $settings, $context, $txt,$options, $scripturl;
	global $ultimateportalSettings, $user_info;

	if ($position == 'header')
	{
		if(!empty($ultimateportalSettings['up_reduce_site_overload']))
		{
			if((cache_get_data('mbk_header', 1800)) === NULL)
			{
				LoadBlocksHEADERPortal("and enable=1");
				//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
				cache_put_data('mbk_header', !empty($context['block-'.$position]), 1800);		
				cache_put_data('exists_multi'.$position, $context['exists_multi'.$position], 1800);		
			}else{
				$context['block-'.$position] = cache_get_data('mbk_header', 1800);
				$context['exists_multi'.$position] = cache_get_data('exists_multi'.$position, 1800);
			}
		}else{
			LoadBlocksHEADERPortal("and enable=1");
		}		
	}
	
	if ($position == 'footer')
	{
		if(!empty($ultimateportalSettings['up_reduce_site_overload']))
		{
			if((cache_get_data('mbk_footer', 1800)) === NULL)
			{
				LoadBlocksFOOTERPortal("and enable=1");
				//Ultimate Portal use SMF Cache data... UP it's the best, only "UP", can create this feature
				cache_put_data('mbk_footer', !empty($context['block-'.$position]), 1800);		
				cache_put_data('exists_multi'.$position, $context['exists_multi'.$position], 1800);		
			}else{
				$context['block-'.$position] = cache_get_data('mbk_footer', 1800);
				$context['exists_multi'.$position] = cache_get_data('exists_multi'.$position, 1800);
			}
		}else{
			LoadBlocksFOOTERPortal("and enable=1");
		}				
	}
	
	if(!empty($context['exists_multi'.$position]) && !empty($context['block-'.$position]))
	{
		echo '
		<table id="up_bk_table_main" style="width:100%" cellpadding="3" cellspacing="0">
		<tr>
		<td>';
		foreach($context['block-'.$position] as $multiblock)		
		{
			$title = $user_info['is_admin'] ? '<a href="'. $scripturl .'?action=admin;area=multiblock;sa=edit;id='. $multiblock['id'] .';'. $context['session_var'] .'='. $context['session_id'] .'">'. $multiblock['mbtitle'] .'</a>' : $multiblock['mbtitle'];
			head_block('up-multiblock', $title, '-'.$multiblock['id'], $multiblock['mbk_collapse'], $multiblock['mbk_title'], $multiblock['mbk_style']);
			
			switch($multiblock['design']) 
			{
				case '1-2': $column = 1;//per line
							break;
				case '2-1': $column = 2;//per line
							break;
				case '3-1': $column = 2;//per line
							break;
				default: 	$column = 1;//per line
							break;
			}
			
			//1 Row 2 Columns
			if ($multiblock['design']=='1-2')
			{
				$c1 = !empty($multiblock['vblocks']['c1']) ? count($multiblock['vblocks']['c1']) : 0;
				$c2 = !empty($multiblock['vblocks']['c2']) ? count($multiblock['vblocks']['c2']) : 0;
				
				echo '
				<table id="up_bk_table_main" style="width:100%" cellpadding="5" cellspacing="2">
					<tr>';		
						//Now View Column 1 
						if(!empty($multiblock['vblocks']['c1']))
						{
							echo '
							<td valign="top" align="left">';							
							foreach($multiblock['vblocks']['c1'] as $vblocks)
							{
								echo '
								',up_get_MBcolumn($vblocks['id']),'';
							}
							echo '
							</td>';
						}
						//Now View Column 2 
						if(!empty($multiblock['vblocks']['c2']))
						{
							echo '
							<td valign="top" align="left">';							
							foreach($multiblock['vblocks']['c2'] as $vblocks)
							{
								echo '
								',up_get_MBcolumn($vblocks['id']),'';
							}
							echo '
							</td>';
						}						
					echo '
					</tr>
				</table>';
				//End View Column 1
			}
			//End Design 1-2

			//2 Rows 1 Column
			if ($multiblock['design']=='2-1')
			{
				$r1 = !empty($multiblock['vblocks']['r1']) ? count($multiblock['vblocks']['r1']) : 0;
				$r2 = !empty($multiblock['vblocks']['r2']) ? count($multiblock['vblocks']['r2']) : 0;
				$context['width_1'] = ($r1 >= 1 && $r1<=2 ? '50%' : '33%');
				$context['width_2'] = ($r2 >= 1 && $r2<=2 ? '50%' : '33%');
				$i = 1; //flag
				
				//Now View Row 1 
				if(!empty($multiblock['vblocks']['r1']))
				{
					echo '
					<table id="up_bk_table_main" style="width:100%" cellpadding="5" cellspacing="2">
						<tr>
							<td colspan="3" valign="top" style="width:100%;" align="left">
								<table style="width:100%">
									<tr>';
									$alternate = true;
									foreach($multiblock['vblocks']['r1'] as $vblocks)
									{
										echo '
										<td valign="top" style="width:',$context['width_1'],';" align="left">
											',up_get_MBcolumn($vblocks['id']) ,'
										</td>';
										
										$i++;
										if($i==$column+1)
										{
											echo '
											</tr><tr>';
											$i=1;
										}
									}
						echo '
									</tr>
								</table>
							</td>	
						</tr>
					</table>';
				}
				//End View Row 1
				
				//Now View Row 2 
				if (!empty($multiblock['vblocks']['r2']))
				{
					$i = 1; //flag
					echo '
					<table id="up_bk_table_main" style="width:100%" cellpadding="5" cellspacing="2">
						<tr>
							<td colspan="3" valign="top" style="width:100%;" align="left">
								<table style="width:100%">
									<tr>';
									$alternate = true;
									foreach($multiblock['vblocks']['r2'] as $vblocks)
									{
										echo '
										<td valign="top" style="width:',$context['width_2'],';" align="left">
											',up_get_MBcolumn($vblocks['id']) ,'
										</td>';
										
										$i++;
										if($i==$column+1)
										{
											echo '
											</tr><tr>';
											$i=1;
										}
									}
						echo '
									</tr>
								</table>
							</td>	
						</tr>
					</table>';
				}
				//End View Row 2										
			}
			//End Design 2-1
			
			//3 Rows 1 Column
			if ($multiblock['design']=='3-1')
			{
				$r1 = !empty($multiblock['vblocks']['r1']) ? count($multiblock['vblocks']['r1']) : 0;
				$r2 = !empty($multiblock['vblocks']['r2']) ? count($multiblock['vblocks']['r2']) : 0;
				$r3 = !empty($multiblock['vblocks']['r3']) ? count($multiblock['vblocks']['r3']) : 0;
				$context['width_1'] = ($r1 >= 1 && $r1<=2 ? '50%' : '33%');
				$context['width_2'] = ($r2 >= 1 && $r2<=2 ? '50%' : '33%');
				$context['width_3'] = ($r3 >= 1 && $r3<=2 ? '50%' : '33%');
				$i = 1; //flag
				
				//Now View Row 1 
				if(!empty($multiblock['vblocks']['r1']))
				{
					echo '
					<table id="up_bk_table_main" style="width:100%" cellpadding="5" cellspacing="2">
						<tr>
							<td colspan="3" valign="top" style="width:100%;" align="left">
								<table style="width:100%">
									<tr>';
									$alternate = true;
									foreach($multiblock['vblocks']['r1'] as $vblocks)
									{
										echo '
										<td valign="top" style="width:',$context['width_1'],';" align="left">
											',up_get_MBcolumn($vblocks['id']) ,'
										</td>';
										
										$i++;
										if($i==$column+1)
										{
											echo '
											</tr><tr>';
											$i=1;
										}
									}
						echo '
									</tr>
								</table>
							</td>	
						</tr>
					</table>';
				}
				//End View Row 1
				
				//Now View Row 2 
				if (!empty($multiblock['vblocks']['r2']))
				{
					$i = 1; //flag
					echo '
					<table id="up_bk_table_main" style="width:100%" cellpadding="5" cellspacing="2">
						<tr>
							<td colspan="3" valign="top" style="width:100%;" align="left">
								<table style="width:100%">
									<tr>';
									$alternate = true;
									foreach($multiblock['vblocks']['r2'] as $vblocks)
									{
										echo '
										<td valign="top" style="width:',$context['width_2'],';" align="left">
											',up_get_MBcolumn($vblocks['id']) ,'
										</td>';
										
										$i++;
										if($i==$column+1)
										{
											echo '
											</tr><tr>';
											$i=1;
										}
									}
						echo '
									</tr>
								</table>
							</td>	
						</tr>
					</table>';
				}
				//End View Row 2					

				//Now View Row 3 
				if (!empty($multiblock['vblocks']['r3']))
				{
					$i = 1; //flag
					echo '
					<table id="up_bk_table_main" style="width:100%" cellpadding="5" cellspacing="2">
						<tr>
							<td colspan="3" valign="top" style="width:100%;" align="left">
								<table style="width:100%">
									<tr>';
									$alternate = true;
									foreach($multiblock['vblocks']['r3'] as $vblocks)
									{
										echo '
										<td valign="top" style="width:',$context['width_3'],';" align="left">
											',up_get_MBcolumn($vblocks['id']) ,'
										</td>';
										
										$i++;
										if($i==$column+1)
										{
											echo '
											</tr><tr>';
											$i=1;
										}
									}
						echo '
									</tr>
								</table>
							</td>	
						</tr>
					</table>';
				}
				//End View Row 3					
			}
			//End Design 3-1
			
			footer_block($multiblock['mbk_style']);
		}
		echo '
		</td>
		</tr>
		</table>';
	}
}

//Get Column
function up_get_MBcolumn($id) {
	global $db_prefix, $sourcedir, $boarddir, $context, $user_info;
	global $ultimateportalSettings, $settings, $scripturl;
	global $options;

	foreach($context['blocks-init'] as $row) 
	{
		if ($row['id'] == $id) {
			$id_block = $row['id'];
			$content = "";
			$perms = '';
			$perms = array();
			$title = $row['title'];
			$icon = $row['icon'];
			$active = $row['active'];

			if ($row['perms']) {
				$perms =  $row['perms'];
			}
			
			if(!$perms) {
				$perms = array();
			}

			$perms = !empty($perms) ? explode(',', $perms) : '';		
			$viewblock	= !empty($perms) ? false : true;		
			if ($viewblock === false)
			{
				foreach($user_info['groups'] as $group_id) 
					if(in_array($group_id, $perms)) {
						$viewblock = true;
					}
			}
	
			if ($active == "checked" && ($viewblock === true || $user_info['is_admin'])) {
				switch($row['personal']) {
					// HTML Block
					case '1':   if ($row['content'] != "") {
									$content = "".stripslashes($row['content'])."";
								}
								//Print
								$title = $user_info['is_admin'] ? '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .'">'. $title .'</a>' : $title;
								head_block($icon, $title, $id_block, $row['bk_collapse'], $row['bk_no_title'], $row['bk_style']);
								echo $content;
								footer_block(trim($row['bk_style']));
								break;
					 // PHP Block								
					case '2':   $title = $user_info['is_admin'] ? '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';type-php=created">'. $title .'</a>' : $title;
								head_block($icon, $title, $id_block, $row['bk_collapse'], $row['bk_no_title'], $row['bk_style']);
								require_once($boarddir."/up-php-blocks/".$row['file']);
								footer_block(trim($row['bk_style']));
								break;
					 // System Block			
					default:    $title = $user_info['is_admin'] ? '<a href="'. $scripturl .'?action=admin;area=ultimate_portal_blocks;sa=blocks-edit;id='. $row['id'] .';personal='. $row['personal'] .';type-php=system">'. $title .'</a>' : $title;
								head_block($icon, $title, $id_block, $row['bk_collapse'], $row['bk_no_title'], $row['bk_style']);
								require_once($boarddir."/up-blocks/".$row['file']);
								footer_block(trim($row['bk_style']));
								break;
				}				
			}
		}
		unset($viewblock);
	}
}

?>