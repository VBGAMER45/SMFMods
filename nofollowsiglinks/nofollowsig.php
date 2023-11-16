<?php
/*
Nofollow Signature Links
Version 1.0
by:vbgamer45
https://www.smfhacks.com

*/

if (!defined('SMF'))
	die('Hacking attempt...');


function nofollowsig_member_context(&$user)
{
	$user['signature'] = nofollow($user['signature']);

}

function nofollow($html, $skip = null) {
    return preg_replace_callback(
        "#(<a[^>]+?)>#is", function ($mach) use ($skip) {
        	
        	
        	if (strpos($mach[0], 'nofollow') === false)
        	{
        		$mach[0] = str_replace('rel="noopener"','rel="nofollow noopener"',$mach[0]);
        	}
        	
        	
        	return $mach[0];
        },
        $html
    );
}


?>