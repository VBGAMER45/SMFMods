<?php
/*
Post Scheduler
Version 1.0
by:vbgamer45
http://www.smfhacks.com
Copyright 2012 http://www.samsonsoftware.com
*/
ini_set("display_errors",1);
// SSI needed to get SMF functions
require('SSI.php');



require_once($sourcedir . '/PostScheduler.php');

CheckPostScheduler();

die($txt['postscheduler_admin']);
?>