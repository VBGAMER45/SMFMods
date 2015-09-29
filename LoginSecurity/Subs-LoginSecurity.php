<?php
/*
Login Security
Version 1.0
by:vbgamer45
http://www.smfhacks.com
*/
if (!defined('SMF'))
	die('Hacking attempt...');

function CheckAllowedIP($memberID)
{
	global $txt, $db_prefix, $modSettings, $scripturl, $user_info;
	
	// Check if we have IP Security turned on
	if (empty($modSettings['ls_allow_ip_security']))
		return true;
	
	// Get user's ip
	$ip = $user_info['ip'];
	
	// Check if IP is allowed
	$dbresult = db_query("
	SELECT 
		allowedips 
	FROM {$db_prefix}login_security
	WHERE id_member = " . $memberID, __FILE__, __LINE__);
	// We are not going to do anything since they don't have any settings defined
	if (mysql_num_rows($dbresult) == 0)
		return true;
		
	$ipRow = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	
	// If no IP's in the list do nothing
	if (empty($ipRow['allowedips']))
		return true;
		
	// IP's where found make a list
	$ipArray = explode(",",$ipRow['allowedips']);
	
	if (in_array($ip, $ipArray) == true)
		return true;
	else
	{
		// Maybe they get a bypass link or not???
		if (CheckForSecureLoginLink($memberID) == false)
		{
			// IP not found give them a big error message!
			$loginInText = str_replace("%link", $scripturl . '?action=login2;sa=securelink;mem=' . $memberID, $txt['ls_invalid_ip']);
			
			// Log error needed because we are including html link!!!
			log_error($loginInText);
			// Display error
			fatal_error($loginInText,false);
		
		}
	}
	
}

function AddLoginFailure($memberID)
{
	global $db_prefix, $sourcedir, $modSettings, $txt, $user_info;
	
	// Get user's ip
	$ip = $user_info['ip'];
	
	// Get the time of failure
	$t = time();
	
	// Log the failure
	db_query("
	INSERT INTO {$db_prefix}login_security_log 
    (ID_MEMBER, date, ip) VALUES ($memberID,'$t','$ip')", __FILE__, __LINE__);
	
	// Lock the account if too many tries
	SetupLoginSecurityTable($memberID);
	
	$lockedTime = 0;
	$lockCheckTime = time() - ($modSettings['ls_allowed_login_attempts_mins'] * 60);
	
	$dbresult = db_query("
	SELECT
		COUNT(*) AS total 
	From {$db_prefix}login_security_log 
	WHERE ID_MEMBER = $memberID AND date >= $lockCheckTime", __FILE__, __LINE__);
	
	$totalRow = mysql_fetch_assoc($dbresult);
	
	// Check if we need to lock the account
	if ($totalRow['total'] > $modSettings['ls_allowed_login_attempts'])
	{
		$lockedTime =  time() + ($modSettings['ls_login_retry_minutes'] * 60);
	}
	
	mysql_free_result($dbresult);
	
	// Send the email alert to account owner if it is enabled
	if ($modSettings['ls_send_mail_failed_login'] == 1)
	{		
		
		// Try to find other members on the forum who had the same ip....
		$IPmemberList = '';
		$dbresult = db_query("
		SELECT
			ID_MEMBER, realName
		FROM {$db_prefix}members 
		WHERE memberIP = '$ip' OR memberIP2 = '$ip'", __FILE__, __LINE__);
		while($memRow = mysql_fetch_assoc($dbresult))
		{
			$IPmemberList .= $memRow['realName'] . "\n";
		}
		mysql_free_result($dbresult);
		
		
		require_once($sourcedir . '/Subs-Post.php');
		
		// Lookup the email address
		$dbresult = db_query("
		SELECT
			emailAddress, realName
		FROM {$db_prefix}members 
		WHERE ID_MEMBER = $memberID", __FILE__, __LINE__);
		$emailRow = mysql_fetch_assoc($dbresult);
		mysql_free_result($dbresult);
		
		// Include any IP's that match other forum members....
		$memberMatches = '';
		if (!empty($IPmemberList))
		{
			$memberMatches = $txt['ls_matched_members'] . "\n" . $IPmemberList;	
		}
		
		$msgBody = $txt['ls_failed_email_body'];
		$msgBody = str_replace("%name",$emailRow['realName'],$msgBody);
		$msgBody = str_replace("%membermatches",$memberMatches ,$msgBody);
		$msgBody = str_replace("%ip",$ip,$msgBody);
		
		sendmail($emailRow['emailAddress'], $txt['ls_failed_email_subject'], $msgBody);
		
	}
	
	UpdateLastFailedTime($memberID,$t, $lockedTime);
	
}

function CheckIfAccountIsLocked($memberID)
{
	global $txt, $db_prefix, $scripturl;
	
	$dbresult = db_query("
	SELECT 
		lockedaccountuntiltime
	FROM {$db_prefix}login_security
	WHERE ID_MEMBER = " . $memberID, __FILE__, __LINE__);
	// We are not going to do anything since they don't have any settings defined
	if (mysql_num_rows($dbresult) == 0)
		return false;
		
	$lockedRow = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	
	$t = time();
	
	if ($lockedRow['lockedaccountuntiltime'] > $t)
	{
		// Check if a valid security override link is generated
		if (CheckForSecureLoginLink($memberID) == false)
		{
			$loginInText = str_replace("%link", $scripturl . '?action=login2;sa=securelink;mem=' . $memberID, $txt['ls_account_locked']);
			$loginInText = str_replace("%min", timeformat($lockedRow['lockedaccountuntiltime']), $loginInText);
			
			// Log error needed because we are including html link!!!
			log_error($loginInText);
			// Display error
			fatal_error($loginInText,false);
			
		}
		
	}
	else 
	{
		return false;
	}
	
}

function GenerateSecureLoginHash($memberID)
{
	global $db_prefix, $modSettings, $sourcedir;
	
	SetupLoginSecurityTable($memberID);
	
	require_once($sourcedir . '/Subs-Members.php');
	
	$newHash = generateValidationCode();
	$newHash_sha1 = sha1($newHash);
	
	// Setup the next experiation
	$nextexpire = time() + ($modSettings['ls_securehash_expire_minutes'] * 60);
	
	db_query("
	UPDATE {$db_prefix}login_security 
	SET secureloginhash = '$newHash_sha1', secureloginhashexpiretime = $nextexpire
	WHERE ID_MEMBER = " . $memberID, __FILE__, __LINE__);
	
	return $newHash_sha1;
	
}

function UpdateLastFailedTime($memberID, $lastfailedlogintime, $lockedaccountuntiltime)
{
	global $db_prefix;
	
	// Check if there is an entry setup
	SetupLoginSecurityTable($memberID);
	 
	db_query("
	UPDATE {$db_prefix}login_security 
	SET lastfailedlogintime = '$lastfailedlogintime', lockedaccountuntiltime = '$lockedaccountuntiltime' 
	WHERE ID_MEMBER = " . $memberID, __FILE__, __LINE__);
}

function SetupLoginSecurityTable($memberID)
{
	global $db_prefix;
	
	$dbresult = db_query("
		SELECT 
			ID_MEMBER
		FROM {$db_prefix}login_security
		WHERE ID_MEMBER = " . $memberID, __FILE__, __LINE__);
	
	if (mysql_num_rows($dbresult) == 0)
	{
		// Insert the database entry
		db_query("
		INSERT INTO {$db_prefix}login_security 
		(ID_MEMBER) VALUES ($memberID)", __FILE__, __LINE__);
		
	}
	
	mysql_free_result($dbresult);

}

function ClearSecureLoginLink($memberID)
{
	global $db_prefix;
	
	db_query("
	UPDATE {$db_prefix}login_security 
	SET secureloginhash = '', secureloginhashexpiretime = 0
	WHERE ID_MEMBER = " . $memberID, __FILE__, __LINE__);

}

function CheckForSecureLoginLink($memberID)
{
	global $db_prefix;
	
	if (isset($_SESSION['secureloginhash']))
	{
		// Sanatize the session just in case for trouble makers!!!
		$hashCheck = htmlspecialchars($_SESSION['secureloginhash'],ENT_QUOTES);
		
		// Lets search for a hash in the database hopefully one exists
		$dbresult = db_query("
		SELECT 
			secureloginhashexpiretime, secureloginhash
		FROM {$db_prefix}login_security
		WHERE ID_MEMBER = " . $memberID . " AND secureloginhash = '$hashCheck'", __FILE__, __LINE__);

		// No secure link found for this account
		if (mysql_num_rows($dbresult) == 0)
			return false;
			
		$t = time();
		
		$secureRow = mysql_fetch_assoc($dbresult);
		mysql_free_result($dbresult);
		
		// Check if the hash expired
		if ($t > $secureRow['secureloginhashexpiretime'])
		{
			// Expired bye bye old hash
			ClearSecureLoginLink($memberID);
			
			return false;
		}
		else 
			return true;
				
	}
	else 
		return false;
}

function SendSecureLink()
{
	global $db_prefix, $sourcedir, $modSettings, $scripturl, $txt, $user_info;
	
	$memberID = (int) $_REQUEST['mem'];
	
	$ip = $user_info['ip'];
	
	require_once($sourcedir . '/Subs-Post.php');
		
	// Lookup the email address
	$dbresult = db_query("
		SELECT
			emailAddress, realName
		FROM {$db_prefix}members 
		WHERE ID_MEMBER = $memberID", __FILE__, __LINE__);

	
	// Check if the member exists
	if (mysql_num_rows($dbresult) == 0)
	{
		// Return to the boardurl
		redirectexit();
	}
	
	$emailRow = mysql_fetch_assoc($dbresult);
	mysql_free_result($dbresult);
	
	// Generate the login hash
	$loginHash = GenerateSecureLoginHash($memberID);
		
	$msgBody = $txt['ls_secure_email_body'];
	
	$msgBody = str_replace("%name",$emailRow['realName'],$msgBody);
	$msgBody = str_replace("%link",$scripturl . '?action=login;securelogin=' . $loginHash ,$msgBody);
	$msgBody = str_replace("%ip",$ip,$msgBody);
	$msgBody = str_replace("%min",$modSettings['ls_securehash_expire_minutes'],$msgBody);
	
	sendmail($emailRow['emailAddress'], $txt['ls_secure_email_subject'] , $msgBody );
	
	
	// Return to the boardurl
	redirectexit();
	
}

function UpdateAllowedIPs($memberID, $allowedIPS)
{
	global $db_prefix;
	
	// Check if there is an entry setup
	SetupLoginSecurityTable($memberID);
	
	$validIPs = array();
	$allowedIPS = trim($allowedIPS);

	foreach (explode(",",$allowedIPS)  as $ip)
		if (preg_match('~^((([1]?\d)?\d|2[0-4]\d|25[0-5])\.){3}(([1]?\d)?\d|2[0-4]\d|25[0-5])$~', trim($ip)) !== 0)
			$validIPs[] = $ip;
	 
	db_query("
	UPDATE {$db_prefix}login_security 
	SET allowedips = '" . implode(',', $validIPs) . "' 
	WHERE ID_MEMBER = " . $memberID, __FILE__, __LINE__);
}
	
?>