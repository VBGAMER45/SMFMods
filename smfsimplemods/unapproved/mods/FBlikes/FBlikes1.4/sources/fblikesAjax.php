<?php
if (file_exists('../SSI.php') && !defined('SMF'))
	require_once('../SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

function fblikesMain()
{
	global $txt, $smcFunc, $user_info, $context;
	
	loadLanguage('Fblike');
	loadTemplate('Fblike');
	checkSession('post');	
	$topic = (!empty($_POST['topic'])) ? (int) $smcFunc['db_escape_string']($_POST['topic']) : '';
	$textReturn = '';
	
	if(!empty($topic))
	{	
		$smcFunc['db_insert']('',
		'{db_prefix}fblikes',
			array(
				'id_topic' => 'int', 'id_member' => 'int', 'member_ip' => 'string',
			),
			array(
				$topic, (!empty($user_info['id'])) ? $user_info['id'] : 0, (!empty($user_info['ip'])) ? $user_info['ip'] : '0'
			),
			array('id_topic')
		);
		//ok?
		$textReturn = $txt['fblike_all_ok'];
	}

	$context['textReturn'] = $textReturn;
	$context['template_layers'] = array();
	$context['sub_template'] = 'fblikes_response';
}