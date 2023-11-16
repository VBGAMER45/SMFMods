<?php

function template_fsb_tpl_above()
{
	global $context, $modSettings;

	if ((!empty($modSettings['allforum_fixed_enable']) || !empty($modSettings['display_fixed_enable']) || !empty($modSettings['display_fixed_enable'])) && !empty($context['social_bookmarks']))
	{
		$top = 5;
		foreach ($context['social_bookmarks'] as $act => $button)
		{
			if($button['active'])
			{
				echo '
	            <a style="',$modSettings['sb_zone_float'],':5px; top: ', $top ,'px;" class="', $modSettings['sb_zone_float'] == 'left' ? 'buttonfixed_left' : 'buttonfixed', '" id="'.$button['id'].'" title="', $button['title'] ,'" ', (!empty($button['target']) ? 'target="_blank"' : '') ,' href="', $button['href'] ,'">', $button['text'] ,'</a>';
				$top = $top + 50;
			}
		}
	}
}

function template_fsb_tpl_below()
{
}