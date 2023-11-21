<?php
/*
Tweet Topics System
Version 1.0
by:vbgamer45

*/
global $ssi_guest_access;
$ssi_guest_access = 1;
require 'SSI.php';
global $sourcedir;
require_once($sourcedir . '/twitteroauth.php');

//print_R($_REQUEST);
if(!isset($_GET["error"]))
{

 if(isset($_GET["code"]))
 {
  $code = $_GET["code"];    
  $url = 'https://graph.facebook.com/oauth/access_token?client_id='.$modSettings['facebookappid'] . '&redirect_uri='.urlencode($boardurl . '/facebookcallback.php').'&client_secret='.$modSettings['facebookappsecret'].'&code='.$code;
 //ECHO  $url;

  $curl_handle=curl_init($url);
  curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
  $buffer = curl_exec($curl_handle);
  curl_close($curl_handle);    
  
 $buffer = trim($buffer);
// echo '##' . $buffer . '##';
  
  if (substr_count($buffer,'error') > 0)
  {
  	echo 'FB Error!:';
  	 echo htmlspecialchars($buffer,ENT_QUOTES);
  	 die(" ");
  }
  
  //echo htmlspecialchars($buffer,ENT_QUOTES);
  if(strpos($buffer, 'access_token=') === 0)
  {
  	$params = null;
    parse_str($buffer, $params);

  	
   //if you requested offline acces save this token to db 
   //for use later   
  // $token = str_replace('access_token=', '', $buffer);
  
   $token =  $params['access_token'];
   updateSettings(array('facebookacesstoken' =>  $token));
  // echo 'here';

  }
  else 
  {
  //	ECHO 'HERE3435';
  }
 }
}


ob_clean();
header('Location: ' . $scripturl .'?action=twitter;sa=fbsettings');
exit;

?>