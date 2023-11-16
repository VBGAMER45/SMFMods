<?php
/*----------------------------------------------------------------------------------/
*	My Mood                                             	                        *
*	Author: SSimple Team - 4KSTORE						 							*
*	Powered by www.smfsimple.com						   							*
************************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

function Mymood_admin_area(&$areas)
{
	global $txt;

	loadLanguage('Mymood');
	$areas['config']['areas']['modsettings']['subsections']['mymood'] = array($txt['mymood_admin']);
}

function Mymood_modify_modifications(&$sub_actions)
{
	$sub_actions['mymood'] = 'Mymood_settings';
}

function Mymood_settings(&$return_config = false)
{
	global $context, $txt, $scripturl;

	loadLanguage('Mymood');
	$context['page_title'] = $txt['mymood_admin'];
	$context['settings_title'] = $txt['mymood_admin'];
	$context['settings_message'] = $txt['mymood_admin_desc'];
	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=mymood';

	$config_vars = array(
		array('check', 'mymood_enabled'),
		array('int', 'mymood_limit_in_profile'),
		array('int', 'mymood_limit_chars', 'subtext' => $txt['mymood_limit_chars_desc']),
		array('check', 'mymood_allow_bbc'),
		array('check', 'mymood_allow_smileys'),
		array('check', 'mymood_show_on_boardindex'),
		array('select', 'mymood_boardindex_where',
			array(
				'mood_board_top' => $txt['mymood_boardindex_where_top'],
				'mood_board_bottom' => $txt['mymood_boardindex_where_bottom'],
			)
		),
		array('int', 'mymood_limit_in_board_total'),
		array('int', 'mymood_limit_in_board_view'),
		array('int', 'mymood_second_per_mood_board'),
		array('text', 'mymood_groups_excluded_board', 'subtext' => $txt['mymood_groups_excluded_board_desc']),
	);

	if ($return_config)
		return $config_vars;


	if (isset($_GET['save']))
	{
		if (!empty($_POST['mymood_groups_excluded_board']))
		{
			$tmp = explode(',', $_POST['mymood_groups_excluded_board']);
			$tmp = array_unique(array_map('intval', $tmp));
			$_POST['mymood_groups_excluded_board'] = implode(',', $tmp);
		}

		if (empty($_POST['mymood_limit_in_board_total']) || $_POST['mymood_limit_in_board_total'] < 0)
			$_POST['mymood_limit_in_board_total'] = 5;

		if (empty($_POST['mymood_limit_in_board_view']) || $_POST['mymood_limit_in_board_view'] < 0)
			$_POST['mymood_limit_in_board_view'] = 1;

		if (empty($_POST['mymood_second_per_mood_board']) || $_POST['mymood_second_per_mood_board'] < 0)
			$_POST['mymood_second_per_mood_board'] = 3;

		cache_put_data('my_mood_board', null); //we need kill data in cache	for update new values
		checkSession();
		saveDBSettings($config_vars);
		writeLog();
	}
	prepareDBSettingContext($config_vars);
}

function Mymood_load_theme()
{
	global $context, $settings, $modSettings, $txt;

	$action = !empty($context['current_action']) ? (string) $context['current_action'] : '';
	$area = !empty($_REQUEST['area']) ? (string)$_REQUEST['area'] : '';

	if (!empty($modSettings['mymood_enabled']) && !empty($modSettings['mymood_show_on_boardindex']) && empty($_GET['xml']) && empty($action) && empty($_GET['topic']) && empty($_GET['board']))
    {
		$time = $modSettings['mymood_second_per_mood_board'] * 1000; //we need this in miliseconds?
		$context['html_headers'] .= '
		<link rel="stylesheet" type="text/css" href="'.$settings['default_theme_url'].'/css/mymood.css" />		
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script type="text/javascript" src="'. $settings['default_theme_url']. '/scripts/jquery.vticker.js"></script>
		<script type="text/javascript">
		// <![CDATA[
		var mym = jQuery.noConflict();
		mym(function(){
			mym(\'.mood_ticker\').vTicker({
				speed: 800,
				pause: '.$time.',
				animation: \'fade\',
				mousePause: true,
				showItems: '.$modSettings['mymood_limit_in_board_view'].'
			});
		});
		// ]]>
        </script>';

		showMoods();
	}

	if (!empty($modSettings['mymood_enabled']) && ($area == "summary" || empty($area)) && $action == "profile" )
	{
		$context['html_headers'] .= '
		<link rel="stylesheet" type="text/css" href="'.$settings['default_theme_url'].'/css/mymood.css" />';

		if (!empty($modSettings['mymood_limit_chars']))
		{
			loadLanguage('Mymood');
			$context['html_headers'] .= '
			<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url']. '/scripts/jquery.vticker.js"></script>
			<script type="text/javascript">
			// <![CDATA[
				var mym = jQuery.noConflict();
				mym(document).ready(function(){
					mym(\'form[name*="my_mood_form"]\').append("<span class=\"char_left\">'.$txt['mymood_char_left'].'</span><span class=\"char_counter\" name=\"countdown\">'.$modSettings['mymood_limit_chars'].'</span>");
					mym(\'.editor\').csCharCounter({
						limit_text: '.$modSettings['mymood_limit_chars'].',
					});
				});
			// ]]>
			</script>';
		}
	}
}

function Mymood_Buffer($buffer)
{
	global $forum_copyright, $context, $sourcedir;
	
	require_once($sourcedir . '/QueryString.php');
	ob_sessrewrite($buffer);
	
	if(empty($context['deletforum']))
	{
		$context['deletforum'] = base64_decode('IHwgPGEgc3R5bGU9ImZvbnQtc2l6ZToxMHB4OyIgaHJlZj0iaHR0cDovL3d3dy5zbWZzaW1wbGUuY29tIiB0aXRsZT0iVG9kbyBwYXJhIHR1IGZvcm8gU01GIj5Nb2RzIGJ5IFNNRlNpbXBsZS5jb208L2E+');	
		$buffer = str_replace($forum_copyright, $forum_copyright.$context['deletforum'],$buffer);			
	}
	return $buffer;
}

function myMood($memID)
{
    global $sourcedir, $context, $txt, $smcFunc, $user_info, $scripturl, $modSettings;

	$memID = !empty($memID) ? (int)$memID : '';
	
	if (!empty($modSettings['mymood_enabled']) && !empty($memID))
	{
		loadLanguage('Mymood');

		if (!empty($_POST['save']))
		{
			$mood_content = !empty($_POST['my_mood']) ? (string) $smcFunc['htmlspecialchars']($_POST['my_mood']) : '';

			if (empty($mood_content))
				fatal_lang_error('mymood_error_no_content',false);

			if (!empty($modSettings['mymood_limit_chars']) && ($smcFunc['strlen']($mood_content) > $modSettings['mymood_limit_chars']))
				fatal_lang_error('mymood_error_limit_chars',false);

			$add_date = (int) time();

			$smcFunc['db_insert']('insert',
				'{db_prefix}my_mood',
				array(
					'id_member' => 'int', 'mood_content' => 'string', 'date' => 'int'
				),
				array(
					$user_info['id'], $mood_content, $add_date
				),
				array()
			);

			cache_put_data('my_mood_board', null); //we need kill data in cache
		}

		$context['post_box_name'] = 'my_mood';
		require_once($sourcedir . '/Subs-Editor.php');
		$editorOptions = array(
			'id' => $context['post_box_name'],
			'value' => '',
			'form' => 'my_mood_form',
			'labels' => array(
				'post_button' => $txt['mymood_publish'],
			),
			'height' => '80px',
			'preview_type' => 0,
		);
		create_control_richedit($editorOptions);

		//Checks for the last x moods		
		$context['last_moods'] = array();
		
		$limitmoods = !empty($modSettings['mymood_limit_in_profile']) ? 'LIMIT '.(int) $modSettings['mymood_limit_in_profile'].'' : '';
		$context['last_moods'] = array();
		$last_moods = array();
		$sql = $smcFunc['db_query']('',"
			SELECT mo.id_mood, mo.id_member, mo.mood_content, mo.date, m.id_member, m.real_name
			FROM {db_prefix}my_mood AS mo
			INNER JOIN {db_prefix}members AS m ON (mo.id_member = m.id_member)
			WHERE mo.id_member = {int:id_member}
			ORDER BY date DESC
			".$limitmoods."
			",
			array(
				'id_member' => $memID,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($sql))
		{
			$last_moods = &$context['last_moods'][];
			$last_moods['id_mood'] = $row['id_mood'];
			$last_moods['id_member'] = $row['id_member'];
			$last_moods['date'] = timeformat($row['date']);
			$last_moods['real_name'] = $row['real_name'];
			$last_moods['mood_delete'] = '<a onclick="return confirm(\''.$txt['mymood_delete_confirmation'].'\');" href="'.$scripturl.'?action=profile;area=summary;mood_delete_id='.$row['id_mood'].'">X</a>';
			$last_moods['mood_content'] = !empty($modSettings['mymood_allow_bbc']) ? parse_bbc($row['mood_content'], false) : $row['mood_content'];

			if (!empty($modSettings['mymood_allow_smileys']))
				parsesmileys($last_moods['mood_content']);
		}

		$smcFunc['db_free_result']($sql);
		
		//Check mood id for delete
		$mood_delete_id = !empty($_REQUEST['mood_delete_id']) ? (int)$_REQUEST['mood_delete_id'] : '';

		if (!empty($mood_delete_id))
		{
			$can_delete = true;
			if (!$user_info['is_admin'])
				$can_delete = checkDeletePerms($mood_delete_id); //no admin? then check if the same user

			if (!$can_delete)
			   fatal_lang_error('mymood_error_cant_delete',false); //cant delete

			$smcFunc['db_query']('',"
				DELETE FROM {db_prefix}my_mood
				WHERE id_mood = {int:id}
				LIMIT 1",
				array(
						'id' => $mood_delete_id,
				)
			);
			cache_put_data('my_mood_board', null);
			redirectexit('action=profile;area=summary');
		}
	}
}

function checkDeletePerms($mood_id)
{
    global $smcFunc, $user_info;

    $can_delete_mood = false;
    $mood_id = !empty($mood_id) ? (int)$mood_id : '';

    if (!empty($mood_id))
    {
       $sql = $smcFunc['db_query']('',"
            SELECT id_member
            FROM {db_prefix}my_mood
            WHERE id_mood = {int:mood_id}
            LIMIT 1",
            array(
                'mood_id' => $mood_id,
            )
        );
        list($id_member) = $smcFunc['db_fetch_row']($sql);
        $smcFunc['db_free_result']($sql);

        if ($id_member == $user_info['id'])
        {
            $can_delete_mood = true;
            return  $can_delete_mood;
        }
    }
}

function showMoods()
{
    global $smcFunc, $context, $modSettings, $settings, $scripturl;

	if (!empty($modSettings['mymood_enabled']) && !empty($modSettings['mymood_show_on_boardindex']) && !isset($_GET['xml']) && !isset($_GET['action']) && !isset($_GET['topic']) && !isset($_GET['board']))
	{
		loadLanguage('Mymood');
		loadTemplate('MyMood');
		$context['template_layers'][] = 'boardmood_tpl';

		if ((cache_get_data('my_mood_board', 1800)) === NULL)
		{
			$limitmoods = !empty($modSettings['mymood_limit_in_board_total']) ? (int) $modSettings['mymood_limit_in_board_total'] : 5;
			$condition = '';

			if (!empty($modSettings['mymood_groups_excluded_board']))
			{
				$exclude = $modSettings['mymood_groups_excluded_board'];
				$exclude_groups = explode(',',$exclude);
				$condition = 'WHERE (FIND_IN_SET({raw:member_group_excluded_implode}, m.additional_groups) = 0)
				AND m.id_group NOT IN ({array_int:member_groups_excluded})
				AND m.id_post_group NOT IN ({array_int:member_groups_excluded})';
			}

			$context['board_moods'] = array();
			$board_moods = array();
			$sql = $smcFunc['db_query']('',"
				SELECT mo.id_mood, mo.id_member, mo.mood_content, mo.date, m.id_member, m.real_name, m.avatar, m.additional_groups, m.id_group, m.id_post_group,
				IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type
				FROM {db_prefix}my_mood AS mo
				INNER JOIN {db_prefix}members AS m ON (mo.id_member = m.id_member)
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mo.id_member)
				".$condition."
				ORDER BY date DESC
				LIMIT ".$limitmoods."",
				array(
					'member_groups_excluded' => !empty($exclude_groups) ? $exclude_groups : '',
					'member_group_excluded_implode' => !empty($exclude_groups) ? implode(', m.additional_groups) = 0 AND FIND_IN_SET(', $exclude_groups) : '',
				)
			);

			while ($row = $smcFunc['db_fetch_assoc']($sql))
			{
				$board_moods = &$context['board_moods'][];
				$board_moods['id_mood'] = $row['id_mood'];
				$board_moods['id_member'] = $row['id_member'];
				$board_moods['date'] = timeformat($row['date']);
				$board_moods['real_name'] = $row['real_name'];
				$board_moods['avatar'] = $row['avatar'] == '' ? ($row['id_attach'] > 0 ? '<img style="width:50px;height:50px;" src="' . (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" />' : '<img style="width:50px;height:50px;" src="' . $settings['images_url'] . '/noavatar.png" alt="" />') : (stristr($row['avatar'], 'http://') ? '<img style="width:50px;height:50px;" src="' . $row['avatar'] . '" alt="" />' : '<img style="width:50px;height:50px;" src="' . $modSettings['avatar_url'] . '/' . $smcFunc['htmlspecialchars']($row['avatar']) . '" alt="" />');
				$board_moods['mood_content'] = !empty($modSettings['mymood_allow_bbc']) ? parse_bbc($row['mood_content'], false) : $row['mood_content'];

				if (!empty($modSettings['mymood_allow_smileys']))
					parsesmileys($board_moods['mood_content']);
			}
			$smcFunc['db_free_result']($sql);
			cache_put_data('my_mood_board', $context['board_moods'], 1800);
		}

		else
			$context['board_moods'] = cache_get_data('my_mood_board', 1800);
	}
}