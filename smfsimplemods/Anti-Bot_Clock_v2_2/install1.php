<?php
################################
##	.LORD.
##	Anti Bot: Captcha Clock
##	v2.2
################################

global $db_prefix;

db_query("INSERT IGNORE INTO
			{$db_prefix}settings
			(variable, value)
			VALUES	('abclock_s', '1'),
					('abclock_n', '5'),
					('abclock_r', '10'),
					('abclock_e', '0')"
			, __FILE__, __LINE__
		);
?>