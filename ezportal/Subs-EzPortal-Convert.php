<?php
/*
EzPortal
Version 0.4.0
by:vbgamer45
http://www.ezportal.com
Copyright 2010 http://www.samsonsoftware.com
*/

function EzPortalImportSimplePortal()
{
	global $txt, $db_prefix;

	TruncateEzPortalTables();
	// Give them a final message saying the import went well
}

function EzPortalImportTinyPortal()
{
	global $txt, $db_prefix;
	
	TruncateEzPortalTables();
	// Give them a final message saying the import went well
}

function EzPortalImportPortalMX()
{
	global $txt, $db_prefix;
	
	TruncateEzPortalTables();
	// Give them a final message saying the import went well
}

function EzPortalImportDreamPortal()
{
	global $txt, $db_prefix;
	
	TruncateEzPortalTables();
	// Give them a final message saying the import went well
}

function EzPortalImportADKPortal()
{
	global $txt, $db_prefix;
	
	TruncateEzPortalTables();
	// Give them a final message saying the import went well
}

function EzPortalImportUltimatePortal()
{
	global $txt, $db_prefix;
	
	TruncateEzPortalTables();
	// Give them a final message saying the import went well
}

function TruncateEzPortalTables()
{
	global $db_prefix;
	
	// Truncate Layout
	$dbresult = db_query("
	TRUNCATE {$db_prefix}ezp_block_layout
	", __FILE__, __LINE__);
	
	// Truncate saved parametters values
	$dbresult = db_query("
	TRUNCATE {$db_prefix}ezp_block_parameters_values
	", __FILE__, __LINE__);
	
	// Truncate shoutbox
	$dbresult = db_query("
	TRUNCATE {$db_prefix}ezp_shoutbox
	", __FILE__, __LINE__);
	
	// Menu
	$dbresult = db_query("
	TRUNCATE {$db_prefix}ezp_menu
	", __FILE__, __LINE__);
	
	// RSS
	$dbresult = db_query("
	TRUNCATE {$db_prefix}ezp_rss_cache
	", __FILE__, __LINE__);
}

?>