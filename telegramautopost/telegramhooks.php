<?php
/*
Telegram Autopost
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

if (!defined('SMF'))
	die('Hacking attempt...');



function telegram_admin_areas(&$admin_areas)
{
   global $txt, $modSettings, $scripturl, $sc;
   

    telegram_array_insert($admin_areas, 'layout',
	        array(
                        'telegram' => array(
			'title' => $txt['telegram_admin'],
			'permission' => array('admin_forum'),
			'areas' => array(
				'telegram' => array(
					'label' => $txt['telegram_admin'],
					'file' => 'telegram2.php',
					'function' => 'telegramMain',
					'custom_url' => $scripturl . '?action=admin;area=telegram;sa=settings;sesc=' . $sc,
					'icon' =>  (function_exists("set_tld_regex") ? 'telegram.png' : 'server.gif'),
					'permission' => array('admin_forum'),
					'subsections' => array(
						'settings' => array($txt['telegram_admin']),					),
				),
	
			),
		),
                
	        )
        );
		



}



function telegram_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
{
	$position = array_search($key, array_keys($input), $strict);
	
	// Key not found -> insert as last
	if ($position === false)
	{
		$input = array_merge($input, $insert);
		return;
	}
	
	if ($where === 'after')
		$position += 1;

	// Insert as first
	if ($position === 0)
		$input = array_merge($insert, $input);
	else
		$input = array_merge(
			array_slice($input, 0, $position),
			$insert,
			array_slice($input, $position)
		);
}


function telegram_integrate_create_topic($msgOptions, $topicOptions, $posterOptions)
{
    if (!empty( $topicOptions['id']))
    {
        // telegram web hooks
		global $sourcedir;
		require_once($sourcedir . '/telegram2.php');
		telegram_send_topic($msgOptions['id']);
    }
}

function telegram_integrate_after_create_post($msgOptions, $topicOptions, $posterOptions, $message_columns, $message_parameters)
{
    global $modSettings;

        // don't do alert if topic notifications are enabled for new topics only
     if (empty($topicOptions['id']) && $modSettings['telegram_enable_push_topic'] == 1)
     {
         return;
     }

		// telegram web hooks
		global $sourcedir, $context;
		require_once($sourcedir . '/telegram2.php');
		telegram_send_post($msgOptions['id']);

}

function telegram_integrate_register_after($regOptions, $memberID)
{
		// telegram web hooks
		global $sourcedir;
		require_once($sourcedir . '/telegram2.php');
		telegram_send_new_member_registration($memberID);
}
?>