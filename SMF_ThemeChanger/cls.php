<?php
//Last modified: 2012/04/16
if (!defined('SMF'))
	die('Hacking attempt...');


function cls_int_admin_area(&$admin_areas)
{
	global $txt;

	loadLanguage('cls');
	$admin_areas['config']['areas']['modsettings']['subsections']['cls'] = array($txt['cls_title']);
}

function cls_int_modify_modifications(&$sub_actions)
{
	$sub_actions['cls'] = 'CLS_ChangeThemeSettings';
}

// ClickSafe Admin
function CLS_ChangeThemeSettings($return_config = false)
{
	global $txt, $scripturl, $context;

$config_vars = array();
$config_vars[] = array('check', 'change_theme_check_top');    
$config_vars[] = array('check', 'change_theme_check_bot'); 
    
	if ($return_config)
		return $config_vars;
		
	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=cls';
		
	if (isset($_GET['save']))
	{
		checkSession();		
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=modsettings;sa=cls');		
	}
		prepareDBSettingContext($config_vars);	
} 

// ClickSafe Read database
function cls_read_db()
{
  global $context, $settings, $txt, $modSettings, $smcFunc;
	$request = $smcFunc['db_query']('', '
		SELECT id_theme, variable, value
		FROM {db_prefix}themes
		WHERE variable IN ({string:name}, {string:theme_dir}, {string:theme_url}, {string:images_url})
			AND id_member = {int:no_member}
      AND id_theme IN ({array_string:known_themes})',
		array(
			'no_member' => 0,
			'name' => 'name',
			'theme_dir' => 'theme_dir',
			'theme_url' => 'theme_url',
			'images_url' => 'images_url',
      'known_themes' => explode(',', $modSettings['knownThemes']),
		)
	);
	$context['cls_themes'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (!isset($context['cls_themes'][$row['id_theme']]))
			$context['cls_themes'][$row['id_theme']] = array(
				'id' => $row['id_theme'],
			);
		$context['cls_themes'][$row['id_theme']][$row['variable']] = $row['value'];
	}
	$smcFunc['db_free_result']($request);
}

// ClickSafe Top selector
function cls_change_theme_top()
{
  global $context, $settings, $txt, $modSettings, $smcFunc;

//PrintPage does not need a theme selector ...
If(isset($_GET['action'])) 
if($_GET['action']=="printpage"){return;} 
  
// ClickSafe Read the Templates Database  
cls_read_db();
// ClickSafe Execute the javascript
cls_java();
// ClickSafe Load the language
loadLanguage('cls');
// Right position for selector (example: 100[total screen] - 90[width forum] / 2 = 5 ) 

    if(!empty($modSettings['change_theme_check_top']) || !empty($modSettings['change_theme_check_bot'])){
         //If Top is on/off
         if($modSettings['change_theme_check_top']=='1')
             {
              echo '
               <!-- START ClickSafe SMF Change Theme TOP -->                     
              				<div style="margin: auto; text-align:right; width:'.$settings['forum_width'].'; font-size:12px;">
              					<select id="clicksafe_changer_top" onchange="cls_switch_theme_top();">
                        <optgroup label="', $txt['cls-head'], '">';
              							foreach ($context['cls_themes'] as $theme)
              							{
              								echo '<option value="', $theme['id'], '"', (($settings['theme_id'] == $theme['id']) ? ' selected="selected"' : ''), '>', $theme['name'], '</option>';
              							}
              					echo '
                        </optgroup>
              					</select> 
              				</div>
                <!-- END ClickSafe SMF Change Theme TOP -->
                  ';             
             }
          
        // Top is set to OFF    
        elseif($modSettings['change_theme_check_top']=='0'){}    
         //If both values are empty (normaly by installing the first time), show the top
          else{
              echo '
               <!-- START ClickSafe SMF Change Theme TOP -->                      
              				<div style="margin: auto; text-align:right; width:'.$settings['forum_width'].'; font-size:12px;">
              					<select id="clicksafe_changer_top" onchange="cls_switch_theme_top();">
                        <optgroup label="', $txt['cls-head'], '">';
              							foreach ($context['cls_themes'] as $theme)
              							{
              								echo '<option value="', $theme['id'], '"', (($settings['theme_id'] == $theme['id']) ? ' selected="selected"' : ''), '>', $theme['name'], '</option>';
              							}
              					echo '
                        </optgroup>
              					</select> 
              				</div>
                <!-- END ClickSafe SMF Change Theme TOP -->
                  ';      
          } 
          }  
}

// ClickSafe Bottom selector
function cls_change_theme_bot()
{
  global $context, $settings, $txt, $modSettings;
  
//PrintPage does not need a theme selector ...
If(isset($_GET['action'])) 
if($_GET['action']=="printpage"){return;} 
 
// ClickSafe Load the language  
loadLanguage('cls');

    if(!empty($modSettings['change_theme_check_bot'])){
         //If Top is on/off
         if($modSettings['change_theme_check_bot']=='1')
             {
              // START Display the SMF Theme Changer results  
              echo '
               <!-- START ClickSafe SMF Change Theme BOTTOM -->
              				<div style="text-align:right; margin: -10px 0 0 0; font-size:12px;">
              					<select id="clicksafe_changer_bot" onchange="cls_switch_theme_bot();">
                        <optgroup label="', $txt['cls-head'], '">';
              							foreach ($context['cls_themes'] as $theme)
              							{
              								echo '<option value="', $theme['id'], '"', (($settings['theme_id'] == $theme['id']) ? ' selected="selected"' : ''), '>', $theme['name'], '</option>';
              							}
              					echo '
                        </optgroup>
              					</select> 
              				</div>
                <!-- END ClickSafe SMF Change Theme BOTTOM -->
                  ';             
             }
          }  
}

// ClickSafe JavaScript to change the style
function cls_java()
  {
  global $settings;
    echo '
        <script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/cls.tc.js"></script>';
  }
?>