<?php
/*
Block Proxy VPN On Registration
Version 1.0
by:vbgamer45
https://www.smfhacks.com

*/

if (!defined('SMF'))
	die('Hacking attempt...');


function blockvpnproxy_register_check(&$regOptions, &$reg_errors)
{
	global $txt;
	
	if (is_UserProxyVPN() == true)
	{
		$reg_errors[] = array('lang', 'blockvbn_signuperror');
	}

}

function is_UserProxyVPN($log = false) 
{
	// Cloudflare don't use this mod then!
	if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
		return false;

	$headers = array('CLIENT_IP', 'USERAGENT_VIA', 'HTTP_PC_REMOTE_ADDR', 'XPROXY_CONNECTION', 'HTTP_XPROXY_CONNECTION', 'FORWARDED','FORWARDED_FOR','FORWARDED_FOR_IP','VIA','X_FORWARDED','X_FORWARDED_FOR','HTTP_CLIENT_IP','HTTP_FORWARDED','HTTP_FORWARDED_FOR','HTTP_FORWARDED_FOR_IP','HTTP_PROXY_CONNECTION','HTTP_VIA','HTTP_X_FORWARDED','HTTP_X_FORWARDED_FOR');

	foreach ($headers as $header)
	{
		if (isset($_SERVER[$header])) 
		{
			
			if ($log == true)
				log_error($header, 'general');
			
			return true;
		}
	}
	
	return false;
	
	
}

function IsTorExitPoint()
{
	if (gethostbyname(ReverseIPOctets($_SERVER['REMOTE_ADDR']).".".$_SERVER['SERVER_PORT'].".".ReverseIPOctets($_SERVER['SERVER_ADDR']).".ip-port.exitlist.torproject.org")=="127.0.0.2")
		return true;
 	else
		return false;

}
function ReverseIPOctets($inputip)
{
	$ipoc = explode(".",$inputip);
	return $ipoc[3].".".$ipoc[2].".".$ipoc[1].".".$ipoc[0];
}




?>