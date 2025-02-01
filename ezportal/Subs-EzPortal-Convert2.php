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
	global $txt, $smcFunc;

	
	TruncateEzPortalTables();
	// Give them a final message saying the import went well
}

function EzPortalImportTinyPortal()
{
	global $txt, $smcFunc;
	
	TruncateEzPortalTables();
	// Give them a final message saying the import went well
}

function EzPortalImportPortalMX()
{
	global $txt, $smcFunc;
	
	TruncateEzPortalTables();
	// Give them a final message saying the import went well
}

function EzPortalImportDreamPortal()
{
	global $txt, $smcFunc;
	
	TruncateEzPortalTables();
	
	// Give them a final message saying the import went well
}

function EzPortalImportADKPortal()
{
	global $txt, $smcFunc;
	
	TruncateEzPortalTables();
	
	// Give them a final message saying the import went well
}

function EzPortalImportUltimatePortal()
{
	global $txt, $smcFunc;
	
	TruncateEzPortalTables();
	
	// Give them a final message saying the import went well
}

function TruncateEzPortalTables()
{
	global $smcFunc;
	
	// Truncate Layout
	$dbresult = $smcFunc['db_query']('', "
	TRUNCATE {db_prefix}ezp_block_layout
	");
	
	// Truncate saved parametters values
	$dbresult = $smcFunc['db_query']('', "
	TRUNCATE {db_prefix}ezp_block_parameters_values
	");
	
	// Truncate shoutbox
	$dbresult = $smcFunc['db_query']('', "
	TRUNCATE {db_prefix}ezp_shoutbox
	");
	
	// Menu
	$dbresult = $smcFunc['db_query']('', "
	TRUNCATE {db_prefix}ezp_menu
	");
	
	// RSS
	$dbresult = $smcFunc['db_query']('', "
	TRUNCATE {db_prefix}ezp_rss_cache
	");
	
}

?>