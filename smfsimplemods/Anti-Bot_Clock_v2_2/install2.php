<?php
################################
##	.LORD.
##	Anti Bot: Captcha Clock
##	v2.2
################################

global $smcFunc;

db_extend('packages');

$smcFunc['db_insert']('ignore',
			'{db_prefix}settings',
			array('variable' => 'string','value' => 'string'),
			array(
				array ('abclock_s' ,'1'),
				array ('abclock_n', '5'),
				array ('abclock_r', '10'),
				array ('abclock_e', '0')
			),
			array()
		);

?>