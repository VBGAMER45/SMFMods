<?php
/*
ListUnsubscribePost
Version 1.0
by:vbgamer45
https://www.smfhacks.com

License Information:
Links to https://www.smfhacks.com must remain unless
branding free option is purchased.
*/

// Connect to SMF
global $ssi_guest_access, $db_type;
$ssi_guest_access = 1;
require 'SSI.php';

// Check the request type
if (isset($_REQUEST['act']))
{
	switch ($_REQUEST['act'])
	{
		case 'unsub':
			ProcessListUnsubscribe();
		break;

	}

}


function ProcessListUnsubscribe()
{
    global $txt, $smcFunc, $context;

	IF (isset( $_POST['id']))
	{
   		$id = (int) $_POST['id'];

		$hash = $_REQUEST['hash'];

		// Check if the email exists
		$result = $smcFunc['db_query']('', "
			SELECT
				email
			FROM {db_prefix}listsubscribe
			WHERE id = $id
			");
		$row = $smcFunc['db_fetch_assoc']($result);

		$email = $row['email'];
		$hash2 = smf_email_hash($email);

		$email = addslashes($email);


		if ($hash2 === $hash)
		{
			$smcFunc['db_query']('', "UPDATE {db_prefix}listsubscribe SET unsubscribed = 1 
			WHERE email = '$email' LIMIT 1");
		}

	}

   	$context['page_title'] = $txt['listunsub_emailunsubscribed'];
   	$context['sub_template'] = 'listunsubscribed';
   	obExit();


}

function template_listunsubscribed()
{
	global $txt;
	echo '<h1>' . $txt['listunsub_emailunsubscribed'] . '</h1>';
}

function smf_email_hash($email)
{
	return sprintf('%u', crc32(strtolower($email))) . strlen($email);
}

?>