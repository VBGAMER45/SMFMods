<?php

if (!defined('SMF'))
	die('Hacking attempt...');

global $smcFunc;

db_extend('packages');

$smcFunc['db_add_column']("smf_members",
                array(
                     'name' => 'formspring', 'type' => 'varchar(50)', 'null' => false,
                ),
                array(),
                'do_nothing',
                ''
      );

?>