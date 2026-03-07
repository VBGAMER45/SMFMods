<?php

/**
 * Members Online Today - Database Installer/Uninstaller
 *
 * @package MembersOnlineToday
 * @author vbgamer45
 * @license BSD 3-Clause
 */

if (!defined('SMF'))
	die('No direct access...');

global $context, $smcFunc, $modSettings;

if (!empty($context['uninstalling']))
{
	// Remove mod settings.
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}settings
		WHERE variable IN ({array_string:mot_settings})',
		array(
			'mot_settings' => array(
				'mot_sort_field',
				'mot_sort_direction',
				'mot_time_range',
				'mot_visibility',
			),
		)
	);

	// Flush the settings cache.
	updateSettings(array('settings_updated' => time()));
}
else
{
	// Install default settings (only if not already set).
	$defaults = array(
		'mot_sort_field' => 'last_login',
		'mot_sort_direction' => 'desc',
		'mot_time_range' => 'today',
		'mot_visibility' => 'members',
	);

	$new_settings = array();
	foreach ($defaults as $key => $value)
	{
		if (!isset($modSettings[$key]))
			$new_settings[$key] = $value;
	}

	if (!empty($new_settings))
		updateSettings($new_settings);
}
