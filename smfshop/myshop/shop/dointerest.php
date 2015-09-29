<?php
/**********************************************\
| SMFSHOP (Shop MOD for Simple Machines Forum) |
|         (c) 2005 DanSoft Australia           |
|      http://www.dansoftaustralia.com/        |
\**********************************************/

//File: dointerest.php
//      The file to add interest to member's bank

// Not running via a cronjob?
if (isset($_SERVER["HTTP_HOST"]))
	// Show an error
	die('Sorry, this script is designed to be used as a cronjob, and cannot be accessed directly!');

// Require SMF's SSI functions
// If this does not work, change the path to be an absolute one (ie.
// include('/home/username/public_html/forum/SSI.php');
include('../../SSI.php');

$interest_rate = $modSettings['shopInterest'] / 100;
// Actually apply the interest
db_query("
	UPDATE {$db_prefix}members
	SET moneyBank = moneyBank + (moneyBank * {$interest_rate})", __FILE__, __LINE__);

echo 'Interest added at ' . date('d/m/Y h:i:s A');
?>
