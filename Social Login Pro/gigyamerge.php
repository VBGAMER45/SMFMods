<?php
/*
Social Login Pro
Version 2.0
by:vbgamer45
http://www.smfhacks.com

License Information:
Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
*/
global $ssi_guest_access;
$ssi_guest_access = 1;
include 'SSI.php';

is_not_guest();


$secretKey2 = $modSettings['slp_secretkey']; 


$UID = $_REQUEST['UID'];
$zip = htmlspecialchars($_REQUEST['zip'],ENT_QUOTES);
$photoURL =htmlspecialchars($_REQUEST['photoURL'],ENT_QUOTES);
$nickname = $_REQUEST['nickname'];
$nickname = preg_replace('#([\%u]{2})([0-9a-zA-Z]{4})#i', '&#x$2', $nickname);
$nickname  = html_entity_decode($nickname , ENT_NOQUOTES,  'UTF-8');

$profileURL = htmlspecialchars($_REQUEST['profileURL'],ENT_QUOTES);
$birthMonth = (int) $_REQUEST['birthMonth'];
$loginProvider = htmlspecialchars($_REQUEST['loginProvider'],ENT_QUOTES);
$loginProviderUID = htmlspecialchars($_REQUEST['loginProviderUID'],ENT_QUOTES);
$country = htmlspecialchars($_REQUEST['country'],ENT_QUOTES);
$thumbnailURL = htmlspecialchars($_REQUEST['thumbnailURL'],ENT_QUOTES);
$lastName = $_REQUEST['lastName'];
$lastName = preg_replace('#([\%u]{2})([0-9a-zA-Z]{4})#i', '&#x$2', $lastName);

$lastName  = html_entity_decode($lastName , ENT_NOQUOTES,  'UTF-8');
$lastName = $func['htmlspecialchars']($lastName,ENT_QUOTES);

$signature = $_REQUEST['signature'];
$firstName = $_REQUEST['firstName'];
$firstName = preg_replace('#([\%u]{2})([0-9a-zA-Z]{4})#i', '&#x$2', $firstName);
$firstName  = html_entity_decode($firstName , ENT_NOQUOTES,  'UTF-8');

$firstName = $func['htmlspecialchars']($firstName,ENT_QUOTES);

$provider = htmlspecialchars($_REQUEST['provider'],ENT_QUOTES);
$gender = htmlspecialchars($_REQUEST['gender'],ENT_QUOTES);
$birthYear = (int) $_REQUEST['birthYear'];
$timestamp = $_REQUEST['timestamp'];
$UIDSig = $_REQUEST['UIDSig'];
$state = htmlspecialchars($_REQUEST['state'],ENT_QUOTES);
$email = htmlspecialchars($_REQUEST['email'],ENT_QUOTES);
$city = htmlspecialchars($_REQUEST['city'],ENT_QUOTES);
$birthDay = htmlspecialchars($_REQUEST['birthDay'],ENT_QUOTES);
$proxiedEmail = htmlspecialchars($_REQUEST['proxiedEmail'],ENT_QUOTES);


$avatarUrl = $thumbnailURL;
if (empty($avatarUrl))
	$avatarUrl = $photoURL;
	
if (empty($modSettings['slp_importavatar']))
	$avatarUrl = '';	


if (empty($UID))
	exit;
	
	// Check if UID already exists....
	$UID = htmlspecialchars($UID,ENT_QUOTES);
	
	$result = db_query("
	SELECT id_member, merged FROM
	{$db_prefix}social_logins WHERE uid = '$UID'", __FILE__, __LINE__);
	$row = mysql_fetch_assoc($result );

	// If exists log in user
	if (empty($row['id_member']))
	{
		$memberID = $ID_MEMBER;
		
		
			db_query("
			INSERT INTO {$db_prefix}social_logins
			(id_member,uid,nickname,email,photourl,profileurl,
			zip,state,city,country,firstname,lastname,
			birthmonth,birthday,birthyear, thumbnailurl,
			loginprovider,loginprovideruid,provider,proxiedemail, merged
				
			)
			VALUES ($memberID,'$UID','$nickname','$email','$photoURL','$profileURL',
			'$zip','$state','$city','$country','$firstName','$lastName',
			'$birthMonth','$birthDay','$birthYear','$thumbnailURL',
			'$loginProvider','$loginProviderUID','$provider','$proxiedEmail', 1  
			)
			", __FILE__, __LINE__);
			
		redirectexit('action=slp;sa=merge');
	}
	else 
	{
		redirectexit('action=slp;sa=merge');
	}
?>