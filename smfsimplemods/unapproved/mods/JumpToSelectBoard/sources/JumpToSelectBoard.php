<?php
/*---------------------------------------
*@Jump To Select Board V1.1				*
*@Author: SSimple Team - 4KSTORE		*
*@Powered by www.smfsimple.com			*
*@agustintari@hotmail.com				*
****************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

function jtsb_mod_load_theme()
{
	global $context, $scripturl;

	selectBoard_JumpTo_cached();

	if (!empty($context['selectBoards']))
	{
		$context['html_headers'] .= '
		<script type="text/javascript">
			function changeUrl()
			{
				var redirect;
				redirect = "'.$scripturl.'?board=";
				redirect += document.getElementById(\'post_board_select\').value;
				document.location.href = redirect;
			}
		</script>';
		loadTemplate('JumpToSelectBoard');
		$context['template_layers'][] = 'jtsb_tpl';
	}
}

function selectBoard_JumpTo_cached()
{
	global $smcFunc, $context, $user_info;

	if(!empty($user_info['query_see_board']) && (cache_get_data('jtsb_mod', 900) === NULL))
	{
		$request = $smcFunc['db_query']('', "
			SELECT c.name AS catName, c.id_cat, b.id_board, b.name AS boardName, b.child_level
			FROM {db_prefix}boards AS b
			LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
			WHERE {query_see_board} AND b.redirect = {string:blank_redirect}",
			array(
					'blank_redirect' => '',
				)
			);


		$context['selectBoards'] = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['selectBoards'][] = array(
				'id' => $row['id_board'],
				'name' => $row['boardName'],
				'childLevel' => $row['child_level'],
				'cat' => array(
					'id' => $row['id_cat'],
					'name' => $row['catName']
				)
			);
		}

		cache_put_data('jtsb_mod', $context['selectBoards'], 900);

		$smcFunc['db_free_result']($request);
	}

	else
		$context['selectBoards'] = cache_get_data('jtsb_mod', 900);
}

function jtsb_mod_Buffer($buffer)
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