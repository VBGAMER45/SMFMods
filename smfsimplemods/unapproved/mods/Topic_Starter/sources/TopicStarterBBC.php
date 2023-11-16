<?php
/*-----------------------------------
*	Topic Starter BBC 1.1			*
*	Author: SSimple Team - 4KSTORE	*
*	Powered by www.smfsimple.com	*
************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

function tsbbc_add_code(&$codes)
{		
	global $context;
	$codes[] = array(
		'tag' => 'starter',
		'type' => 'closed',
		'content' => !empty($context['topic_starter_id']) ? searchStarterLink($context['topic_starter_id']) : '',	
	);
}

function searchStarterLink($topic_starter)
{
	global $context, $memberContext;

	if (!empty($topic_starter))	
	{
		loadMemberContext($context['topic_starter_id']);
		return $memberContext[$context['topic_starter_id']]['link'];
	}
}

function tsbbc_Buffer($buffer)
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