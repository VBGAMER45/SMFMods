<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

//Show the Ultimate Portal - Module FAQ
function template_main()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = '';//Only Inicialized

	$content .= "
	    <script type=\"text/javascript\">
			function makesurelink(condition) {
				if (condition=='section')
				{
					if (confirm('". $txt['ultport_delete_section_confirmation'] ."')) {
						return true;
					} else {
						return false;
					}
				}
				if (condition=='faq')
				{
					if (confirm('". $txt['ultport_delete_confirmation'] ."')) {
						return true;
					} else {
						return false;
					}
				}				
			}
	    </script>";

	if (!empty($ultimateportalSettings['faq_title']) && !empty($ultimateportalSettings['faq_small_description']))
	{
		$content .= '
		<dl id="DownBrowsing">
			<dt>
				<img src="'. $settings['default_theme_url'] .'/images/ultimate-portal/help.png" alt="'. $txt['up_module_faq_title'] .'" style="vertical-align:middle;" />&nbsp;<a href="'. $scripturl .'?action=faq">'. $ultimateportalSettings['faq_title'] . '</a>
			</dt>
			<dd>
				<ul>
					<li>'. $ultimateportalSettings['faq_small_description'] . '</li>
				</ul>	
			</dd>
		</dl>'; 
	}

	// Create the button set...
	$normal_buttons = array(
		'add_faq' => array('condition' => !empty($user_info['up-modules-permissions']['faq_add']), 'text' => 'up_faq_add', 'lang' => true, 'url' => $scripturl . '?action=faq;sa=add;sesc=' . $context['session_id'].'', 'active' => true),
		'add_section' => array('condition' => !empty($user_info['up-modules-permissions']['faq_add']), 'text' => 'up_faq_add_section', 'lang' => true, 'url' => $scripturl . '?action=faq;sa=add-section;sesc=' . $context['session_id'].''),		
	);

	$content .= '
		<div class="UPpagesection">
			'. up_template_button_strip($normal_buttons, 'right') .'
		</div>';

	if (!empty($context['view_faq_main']))
	{
		$content .= '
		<div class="windowbg">
			<div style="font-size:16px;padding:5px" align="center" class="catbg">
				<strong>'. $txt['up_faq_index'] .'</strong>
			</div><br />';
		//Index Content Section	
		$i_section = 1; 
		$i_faq = 1;
		foreach($context['faq_main'] as $section)
		{
			$content .= '
			<a href="'. $scripturl .'?action=faq#view_'. $i_section .'"><strong>'. $i_section .'.&nbsp;'. $section['section'] .'</strong></a>
			<br />';
			foreach($section['question'] as $faq)
			{
				$content .= '
				<a href="'. $scripturl .'?action=faq#view_'. $i_section .'.'. $i_faq .'"><strong>'. $i_section .'.'. $i_faq .'&nbsp;'. $faq['question'] .'</strong></a>
				<br />';
				++$i_faq;
			}//End second for each
			++$i_section;
			$i_faq = 1; //inicialized again
			$content .= '<br/>';
		}//End principal for each
		//Content
		$content .= '<br/>
		<div style="font-size:16px;padding:5px" align="center" class="catbg">
			<strong>'. $txt['up_faq_content'] .'</strong>
		</div><br />';		
		$i_section = 1; 
		$i_faq = 1;
		foreach($context['faq_main'] as $section)
		{
			$content .= '
			<a name="view_'. $i_section .'" id="view_'. $i_section .'"></a><br />
			<strong>'. $i_section .'.&nbsp;'. $section['section'] .'</strong>&nbsp;
			'. ((!empty($user_info['up-modules-permissions']['faq_moderate']) || $user_info['is_admin']) ? $section['edit'] .' | '. $section['delete'] : '') .'';
			foreach($section['question'] as $faq)
			{
				$content .= '
				<br/><a name="view_'. $i_section .'.'. $i_faq .'" id="view_'. $i_section .'.'. $i_faq .'"></a><br />
				<strong>'. $i_section .'.'. $i_faq .'&nbsp;'. $faq['question'] .'</strong>&nbsp;
				'. ((!empty($user_info['up-modules-permissions']['faq_moderate']) || $user_info['is_admin']) ? $faq['edit'] .' | '. $faq['delete'] : '') .'
				<br />
				'. $faq['answer'] .'';
				++$i_faq;
			}//End second for each
			++$i_section;
			$i_faq = 1; //inicialized again
			$content .= '<br/>';
		}//End principal for each

		$content .= '	
		</div>';
	}else{
		$content .= '
		<div class="UPdescription">
			'. $txt['ultport_error_no_faq_main'] .'
		</div>';	
	}
	//The Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_faq_title'] .'</a> &copy; 2010 - 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a><br />';

	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page(1, 1, $content, $copyright, 'faq', $txt['up_module_title'] . ' - ' . $txt['up_module_faq_title']);
	
}

function template_add_faq()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = '';//Only Inicialized
	
	// Create the button set...
	$normal_buttons = array(
		'back' => array('condition' => !empty($user_info['up-modules-permissions']['faq_add']), 'text' => 'ultport_button_back', 'lang' => true, 'url' => $scripturl . '?action=faq', 'active' => true),
	);

	$content .= '
	<dl id="DownBrowsing">
		<dt>
			<img src="'. $settings['default_theme_url'] .'/images/ultimate-portal/add.png" alt="'. $txt['up_faq_add'] .'" style="vertical-align:middle;">&nbsp;<strong>'. $txt['up_faq_add'] . '</strong>
		</dt>
		<dd>
			<ul>
				<li>'. $txt['up_faq_add_description'] . '</li>
			</ul>	
		</dd>
	</dl>'; 

	$content .= '
	<div class="UPpagesection">
		'. up_template_button_strip($normal_buttons, 'top') .'
	</div>';

	$content .=	'
	<form name="faqform" method="post" action="'. $scripturl .'?action=faq;sa=add" accept-charset="'. $context['character_set'] .'">												
		<table width="100%" align="center" class="windowbg UPdescription" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="50%" align="left" class="windowbg">									
					'. $txt['up_faq_question'] .'
				</td>			
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="question" size="50" maxlength="250"/>
				</td>			
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					'. $txt['up_faq_section'] .'
				</td>			
				<td width="50%" align="center" class="windowbg2">
					<select name="section" size="1">';
					foreach ($context['faq_section'] as $section)
					{
						$content .= '
						<option value="'. $section['id_section'] .'">'. $section['section'] .'</option>';
					}
	$content .= '	</select>						
				</td>			
			</tr>
			<tr>
				<td class="windowbg" colspan="2" align="left" width="20%">
					'. $txt['up_faq_answer'] .'
				</td>
			</tr>
			<tr>
				<td colspan="2" class="windowbg2" align="left" width="100%">
					<div id="'. $context['bbcBox_container'] .'"></div>			
					<div id="'. $context['smileyBox_container'] .'"></div>											
					'. up_template_control_richedit($context['post_box_name'],$context['smileyBox_container'],$context['bbcBox_container']) .'
				</td>
			</tr>			
		</table>
		<table width="100%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td colspan="2" align="left">	
					<input type="hidden" name="save" value="ok" />
					<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
					<input type="submit" name="'.$txt['ultport_button_add'].'" value="'.$txt['ultport_button_add'].'" />
				</td>
			</tr>
		</table>
	</form>';

	//The Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_faq_title'] .'</a> &copy; 2010 - 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a><br />';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page(1, 1, $content, $copyright, 'faq', $txt['up_module_title'] . ' - ' . $txt['up_module_faq_title']);
	
}

function template_add_section()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = '';//Only Inicialized
	
	// Create the button set...
	$normal_buttons = array(
		'back' => array('condition' => !empty($user_info['up-modules-permissions']['faq_add']), 'text' => 'ultport_button_back', 'lang' => true, 'url' => $scripturl . '?action=faq', 'active' => true),
	);

	$content .= '
	<dl id="DownBrowsing">
		<dt>
			<img src="'. $settings['default_theme_url'] .'/images/ultimate-portal/add.png" alt="'. $txt['up_faq_add_section'] .'" style="vertical-align:middle;">&nbsp;<a href="'. $scripturl .'?action=faq">'. $txt['up_faq_add_section'] . '</a>
		</dt>
		<dd>
			<ul>
				<li>'. $txt['up_faq_section_description'] . '</li>
			</ul>	
		</dd>
	</dl>'; 

	$content .= '
	<div class="UPpagesection">
		'. up_template_button_strip($normal_buttons, 'top') .'
	</div>';

	$content .=	'
	<form method="post" action="'. $scripturl .'?action=faq;sa=add-section" accept-charset="'. $context['character_set'] .'">												
		<table width="100%" align="center" class="windowbg UPdescription" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="50%" align="left" class="windowbg">									
					<strong>'. $txt['up_faq_section_title'] .'</strong>
				</td>			
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="title" size="85" maxlength="150"/>
				</td>			
			</tr>
		</table>
		<table width="100%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td colspan="2" align="left">	
					<input type="hidden" name="save" value="ok" />
					<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
					<input type="submit" name="'.$txt['ultport_button_add'].'" value="'.$txt['ultport_button_add'].'" />
				</td>
			</tr>
		</table>
	</form>';

	//The Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_faq_title'] .'</a> &copy; 2010 - 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a><br />';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page(1, 1, $content, $copyright, 'faq', $txt['up_module_title'] . ' - ' . $txt['up_module_faq_title']);
	
}

function template_edit_faq()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = '';//Only Inicialized
	
	// Create the button set...
	$normal_buttons = array(
		'back' => array('condition' => !empty($user_info['up-modules-permissions']['faq_moderate']), 'text' => 'ultport_button_back', 'lang' => true, 'url' => $scripturl . '?action=faq', 'active' => true),
	);

	$content .= '
	<dl id="DownBrowsing">
		<dt>
			<img src="'. $settings['default_theme_url'] .'/images/ultimate-portal/add.png" alt="'. $txt['up_faq_edit'] .'" style="vertical-align:middle;">&nbsp;<a href="'. $scripturl .'?action=faq">'. $txt['up_faq_edit'] . '</a>
		</dt>
		<dd>
			<ul>
				<li>'. $txt['up_faq_edit_description'] . '</li>
			</ul>	
		</dd>
	</dl>'; 

	$content .= '
	<div class="UPpagesection">
		'. up_template_button_strip($normal_buttons, 'top') .'
	</div>';

	$content .=	'
	<form name="faqform" method="post" action="'. $scripturl .'?action=faq;sa=edit-faq" accept-charset="'. $context['character_set'] .'">												
		<table width="100%" align="center" class="windowbg UPdescription" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="50%" align="left" class="windowbg">									
					'. $txt['up_faq_question'] .'
				</td>			
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="question" size="50" value="'. $context['question'] .'" maxlength="250"/>
				</td>			
			</tr>
			<tr>
				<td width="50%" align="left" class="windowbg">									
					'. $txt['up_faq_section'] .'
				</td>			
				<td width="50%" align="center" class="windowbg2">
					<select name="section" size="1">';
					foreach ($context['faq_section'] as $section)
					{
						$content .= '
						<option '. ($section['id_section'] == $context['id_section'] ? 'selected="selected"' : '') .'value="'. $section['id_section'] .'">'. $section['section'] .'</option>';
					}
	$content .= '	</select>						
				</td>			
			</tr>
			<tr>
				<td class="windowbg" colspan="2" align="left" width="20%">
					'. $txt['up_faq_answer'] .'
				</td>
			</tr>
			<tr>
				<td colspan="2" class="windowbg2" align="left" width="100%">
					<div id="'. $context['bbcBox_container'] .'"></div>			
					<div id="'. $context['smileyBox_container'] .'"></div>											
					'. up_template_control_richedit($context['post_box_name'],$context['smileyBox_container'],$context['bbcBox_container']) .'
				</td>
			</tr>			
		</table>
		<table width="100%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td colspan="2" align="left">	
					<input type="hidden" name="save" value="ok" />						
					<input type="hidden" name="id" value="'. $context['id'] .'" />
					<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
					<input type="submit" name="'.$txt['ultport_button_edit'].'" value="'.$txt['ultport_button_edit'].'" />
				</td>
			</tr>
		</table>
	</form>';

	//The Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_faq_title'] .'</a> &copy; 2010 - 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a><br />';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page(1, 1, $content, $copyright, 'faq', $txt['up_module_title'] . ' - ' . $txt['up_module_faq_title']);
	
}

function template_edit_section()
{
	global $context, $scripturl, $txt, $settings;
	global $ultimateportalSettings, $user_info;

	$content = '';//Only Inicialized
	
	// Create the button set...
	$normal_buttons = array(
		'back' => array('condition' => !empty($user_info['up-modules-permissions']['faq_moderate']), 'text' => 'ultport_button_back', 'lang' => true, 'url' => $scripturl . '?action=faq', 'active' => true),
	);

	$content .= '
	<dl id="DownBrowsing">
		<dt>
			<img src="'. $settings['default_theme_url'] .'/images/ultimate-portal/add.png" alt="'. $txt['up_faq_edit_section'] .'" style="vertical-align:middle;">&nbsp;<a href="'. $scripturl .'?action=faq">'. $txt['up_faq_edit_section'] . '</a>
		</dt>
		<dd>
			<ul>
				<li>'. $txt['up_faq_section_description'] . '</li>
			</ul>	
		</dd>
	</dl>'; 

	$content .= '
	<div class="UPpagesection">
		'. up_template_button_strip($normal_buttons, 'top') .'
	</div>';

	$content .=	'
	<form method="post" action="'. $scripturl .'?action=faq;sa=edit-section" accept-charset="'. $context['character_set'] .'">												
		<table width="100%" align="center" class="windowbg UPdescription" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td width="50%" align="left" class="windowbg">									
					<strong>'. $txt['up_faq_section_title'] .'</strong>
				</td>			
				<td width="50%" align="center" class="windowbg2">									
					<input type="text" name="title" size="85" value="'. $context['section'] .'" maxlength="150"/>
				</td>			
			</tr>
		</table>
		<table width="100%" align="center" cellspacing="1" cellpadding="5" border="0">
			<tr>
				<td colspan="2" align="left">	
					<input type="hidden" name="save" value="ok" />
					<input type="hidden" name="id_section" value="'. $context['id_section'] .'" />
					<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
					<input type="submit" name="'.$txt['ultport_button_edit'].'" value="'.$txt['ultport_button_edit'].'" />
				</td>
			</tr>
		</table>
	</form>';

	//The Module Copyright - PLEASE NOT REMOVE
	$copyright = '<a href="http://www.smfsimple.com">Ultimate Portal - '. $txt['up_module_title'] . ' '. $txt['up_module_faq_title'] .'</a> &copy; 2010 - 2011 <a href="http://www.smfsimple.com">SMFSimple.com</a><br />';
	
	//Now print the module 
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/	
	up_print_page(1, 1, $content, $copyright, 'faq', $txt['up_module_title'] . ' - ' . $txt['up_module_faq_title']);
	
}

?>