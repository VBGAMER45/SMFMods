<?php
/*
Post Scheduler
Version 1.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2012 http://www.samsonsoftware.com
*/
ini_set("display_errors",1);
global $ssi_guest_access;
$ssi_guest_access = true;

// SSI needed to get SMF functions
require('SSI.php');


if (isset($smcFunc))
	require_once($sourcedir . '/PostScheduler2.php');
else
	require_once($sourcedir . '/PostScheduler.php');

CheckPostScheduler();

die($txt['postscheduler_admin']);
?>