<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/

function template_ultimate_portal_frontpage()
{
	global $context, $ultimateportalSettings, $sourcedir;

	//print the front page
	/*
		This function is from Source/Subs-UltimatePortal-Init-Blocks.php
		first parameter is left column
		second parameter is right column
		and the last parameter is the center column
		the first and second parameter is "1" if the column is are not collapsed and "0" if the column print collapsed
	*/
	$left = !empty($ultimateportalSettings['ultimate_portal_enable_col_left']) ? 1 : 0;
	$right = !empty($ultimateportalSettings['ultimate_portal_enable_col_right']) ? 1 : 0;
	//Portal Above
	up_print_page_above($left, $right, '', 0, 1);
	//Portal Below
	up_print_page_below($right);	
}

?>