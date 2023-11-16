<?php
################################
##	SMFSIMPLE.com
##	Are You Human/Bot? Anti-Bot Registration Check
##	v3
################################

global $smcFunc;

db_extend('packages');

$smcFunc['db_insert']('ignore',
			'{db_prefix}settings',
			array('variable' => 'string','value' => 'string'),
			array(
				array ('are_you_human_s' ,'1'),
				array ('are_you_human_q', '0'),
				array ('are_you_human_a', '1')
			),
			array()
		);

?>