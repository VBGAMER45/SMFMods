<?php
function template_fblikes_response()
{
	global $context, $txt;	
	$context['textReturn'] = !empty($context['textReturn']) ? $context['textReturn'] : $txt['fblike_all_bad'];
	echo $context['textReturn'];	
}