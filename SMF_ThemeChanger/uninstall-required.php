<?php
//Last modified: 2012/04/16

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	$ssi = true;
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

remove_integration_function('integrate_pre_include', '$sourcedir/cls.php');
remove_integration_function('integrate_modify_modifications', 'cls_int_modify_modifications');
remove_integration_function('integrate_admin_areas', 'cls_int_admin_area');

if (!empty($ssi))
	echo 'Database installation complete!';

?>