<?php
/*
Awesome Post Ratings
Version 1.0
by:vbgamer45
http://www.smfhacks.com

License Information:
Links to http://www.smfhacks.com must remain unless
branding free option is purchased.
*/

if (!defined('SMF'))
	die('Hacking attempt...');

// Hook Add Action
function awesomepost_actions(&$actionArray)
{
  global $sourcedir, $modSettings;
   
  $actionArray += array('awesome' => array('AwesomePostRatings2.php', 'AwesomeMain'));
  
}



?>