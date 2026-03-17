<?php
/******************************************************************************
 * MagicLlama.php - Magic Llama Mod 2.0 for SMF 2.1
 *
 * A gamification mod that randomly releases virtual llamas on forum pages.
 * Members click floating llama images to catch them, earning or losing points.
 *
 * Original concept by Aquilo (2004) for SMF RC1.
 * Rewritten for SMF 2.1 hook architecture.
 ******************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Hook: integrate_actions
 * Register the magicllama catch action and llamalog admin action.
 */
function magic_llama_actions(&$actionArray)
{
	$actionArray['magicllama'] = array('MagicLlama.php', 'magic_llama_catch');
	$actionArray['llamalog'] = array('MagicLlama.php', 'magic_llama_log');
}

/**
 * Hook: integrate_load_theme
 * Runs on every page load. Loads language/CSS, attempts llama release,
 * and adds the template layer for floating llama display.
 */
function magic_llama_load_theme()
{
	global $context, $modSettings, $user_info, $settings;

	// Load our language strings.
	loadLanguage('MagicLlama');

	// Load our CSS.
	loadCSSFile('magicllama.css', array('default_theme' => true, 'minimize' => true));

	// Attempt to release a llama for this page load.
	magic_llama_release();

	// If a llama was released, add our template layer so it renders on the page.
	if (!empty($context['magic_llama']))
	{
		loadTemplate('MagicLlama');
		$context['template_layers'][] = 'magic_llama';
	}
}

/**
 * Hook: integrate_general_mod_settings
 * Adds all Magic Llama settings to Admin > Configuration > Modification Settings.
 */
function magic_llama_settings(&$config_vars)
{
	global $txt;

	$config_vars[] = array('title', 'magic_llama_settings_title');
	$config_vars[] = array('check', 'magic_llama_enabled', 'subtext' => $txt['magic_llama_enabled_desc']);
	$config_vars[] = array('check', 'magic_llama_show_stats', 'subtext' => $txt['magic_llama_show_stats_desc']);
	$config_vars[] = array('check', 'magic_llama_allow_hide', 'subtext' => $txt['magic_llama_allow_hide_desc']);
	$config_vars[] = array('check', 'magic_llama_show_in_posts', 'subtext' => $txt['magic_llama_show_in_posts_desc']);
	$config_vars[] = '';
	$config_vars[] = array('int', 'magic_llama_chances', 'subtext' => $txt['magic_llama_chances_desc']);
	$config_vars[] = array('text', 'magic_llama_image', 'subtext' => $txt['magic_llama_image_desc']);
	$config_vars[] = array('int', 'magic_llama_width', 'subtext' => $txt['magic_llama_width_desc']);
	$config_vars[] = array('int', 'magic_llama_height', 'subtext' => $txt['magic_llama_height_desc']);
	$config_vars[] = array('int', 'magic_llama_speed', 'subtext' => $txt['magic_llama_speed_desc']);
	$config_vars[] = '';
	$config_vars[] = array('text', 'magic_llama_type1_name');
	$config_vars[] = array('int', 'magic_llama_type1_min');
	$config_vars[] = array('int', 'magic_llama_type1_max');
	$config_vars[] = array('large_text', 'magic_llama_type1_msg', 'subtext' => $txt['magic_llama_msg_placeholders']);
	$config_vars[] = '';
	$config_vars[] = array('text', 'magic_llama_type2_name');
	$config_vars[] = array('int', 'magic_llama_type2_min');
	$config_vars[] = array('int', 'magic_llama_type2_max');
	$config_vars[] = array('large_text', 'magic_llama_type2_msg', 'subtext' => $txt['magic_llama_msg_placeholders']);
	$config_vars[] = '';
	$config_vars[] = array('large_text', 'magic_llama_late_msg');
}

/**
 * Hook: integrate_admin_areas
 * Adds "Llama Log" link to the admin maintenance area.
 */
function magic_llama_admin_areas(&$admin_areas)
{
	global $txt;

	loadLanguage('MagicLlama');

	$admin_areas['maintenance']['areas']['llamalog'] = array(
		'label' => $txt['magic_llama_log_title'],
		'file' => 'MagicLlama.php',
		'function' => 'magic_llama_log',
		'icon' => 'maintain',
		'permission' => array('admin_forum'),
	);
}

/**
 * Hook: integrate_profile_areas
 * Adds a "Llama Stats" section to user profiles.
 */
function magic_llama_profile_areas(&$profile_areas)
{
	global $txt, $modSettings;

	if (empty($modSettings['magic_llama_enabled']) || empty($modSettings['magic_llama_show_stats']))
		return;

	loadLanguage('MagicLlama');

	$profile_areas['info']['areas']['llamastats'] = array(
		'label' => $txt['magic_llama_profile_title'],
		'file' => 'MagicLlama.php',
		'function' => 'magic_llama_profile',
		'icon' => 'maintain',
		'permission' => array(
			'own' => 'is_not_guest',
			'any' => 'is_not_guest',
		),
	);
}

/**
 * Hook: integrate_load_member_data
 * JOINs the magic_llama_members table when member data is loaded.
 */
function magic_llama_member_data(&$select_columns, &$select_tables, &$set)
{
	$select_columns .= ', IFNULL(mlm.good_llamas, 0) AS good_llamas, IFNULL(mlm.good_points, 0) AS good_points, IFNULL(mlm.bad_llamas, 0) AS bad_llamas, IFNULL(mlm.bad_points, 0) AS bad_points, IFNULL(mlm.hide_llama, 0) AS hide_llama';
	$select_tables .= ' LEFT JOIN {db_prefix}magic_llama_members AS mlm ON (mlm.id_member = mem.id_member)';
}

/**
 * Hook: integrate_member_context
 * Populates $memberContext with llama stats for display in posts/profiles.
 */
function magic_llama_member_context(&$result, $user, $display_custom_fields)
{
	global $memberContext, $user_profile, $modSettings;

	if (empty($modSettings['magic_llama_enabled']))
		return;

	$id = $result['id_member'] ?? $user;

	$memberContext[$id]['magic_llama'] = array(
		'good_llamas' => $user_profile[$id]['good_llamas'] ?? 0,
		'good_points' => $user_profile[$id]['good_points'] ?? 0,
		'bad_llamas' => $user_profile[$id]['bad_llamas'] ?? 0,
		'bad_points' => $user_profile[$id]['bad_points'] ?? 0,
		'hide_llama' => $user_profile[$id]['hide_llama'] ?? 0,
	);
}

/**
 * Hook: integrate_prepare_display_context
 * Injects llama points into the poster info sidebar as a custom field.
 * Controlled by the magic_llama_show_in_posts setting.
 */
function magic_llama_display_context(&$output, &$message, $counter)
{
	global $modSettings, $txt;

	if (empty($modSettings['magic_llama_enabled']) || empty($modSettings['magic_llama_show_in_posts']))
		return;

	// The member context data was already populated by magic_llama_member_context.
	$llama = $output['member']['magic_llama'] ?? null;
	if ($llama === null)
		return;

	$type1 = !empty($modSettings['magic_llama_type1_name']) ? $modSettings['magic_llama_type1_name'] : 'Golden Llama';
	$type2 = !empty($modSettings['magic_llama_type2_name']) ? $modSettings['magic_llama_type2_name'] : 'Evil Llama';

	$net_points = $llama['good_points'] - $llama['bad_points'];
	$total_caught = $llama['good_llamas'] + $llama['bad_llamas'];

	// Build compact HTML for the poster info area.
	$value = '<span class="magic_llama_poster_stats">'
		. $txt['magic_llama_posts_label'] . ' '
		. '<span class="magic_llama_net' . ($net_points >= 0 ? ' positive' : ' negative') . '">'
		. ($net_points >= 0 ? '+' : '') . $net_points
		. '</span>'
		. ' (' . $total_caught . ' ' . $txt['magic_llama_profile_caught'] . ')'
		. '</span>';

	// Inject as a custom field so it renders in the poster sidebar automatically.
	$output['custom_fields'][] = array(
		'title' => $txt['magic_llama_posts_label'],
		'col_name' => 'magic_llama_points',
		'value' => $value,
		'placement' => 0,
	);
}

/**
 * Core: Release a llama on the current page load.
 * Called from the theme hook on every page.
 */
function magic_llama_release()
{
	global $context, $smcFunc, $modSettings, $user_info, $settings, $scripturl;

	// Don't release if disabled, guest, or during AJAX/API requests.
	if (empty($modSettings['magic_llama_enabled']))
		return;

	if ($user_info['is_guest'])
		return;

	// Check if user has hidden llamas.
	if (!empty($modSettings['magic_llama_allow_hide']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT hide_llama
			FROM {db_prefix}magic_llama_members
			WHERE id_member = {int:id_member}',
			array(
				'id_member' => $user_info['id'],
			)
		);
		if ($smcFunc['db_num_rows']($request) > 0)
		{
			$row = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);

			if (!empty($row['hide_llama']))
				return;
		}
		else
			$smcFunc['db_free_result']($request);
	}

	// Roll the dice - check chances.
	$chances = !empty($modSettings['magic_llama_chances']) ? (int) $modSettings['magic_llama_chances'] : 5;
	$needle = mt_rand(1, 100);
	$found = false;

	for ($i = 0; $i < $chances; $i++)
	{
		if ($needle == mt_rand(1, 100))
		{
			$found = true;
			break;
		}
	}

	if (!$found)
		return;

	// Pick llama type: 1 = good, 2 = evil.
	$llama_type = mt_rand(1, 2);

	// Determine point value.
	$min = !empty($modSettings['magic_llama_type' . $llama_type . '_min']) ? (int) $modSettings['magic_llama_type' . $llama_type . '_min'] : 1;
	$max = !empty($modSettings['magic_llama_type' . $llama_type . '_max']) ? (int) $modSettings['magic_llama_type' . $llama_type . '_max'] : ($llama_type == 1 ? 10 : 5);
	$points = mt_rand($min, max($min, $max));

	// Generate unique hash.
	$llama_hash = md5(mt_rand() . microtime() . $user_info['id']);

	// Insert into database.
	$smcFunc['db_insert']('insert',
		'{db_prefix}magic_llama',
		array(
			'llama_type' => 'int',
			'points' => 'int',
			'llama_hash' => 'string',
			'id_member' => 'int',
			'released_at' => 'int',
			'caught_at' => 'int',
		),
		array(
			$llama_type,
			$points,
			$llama_hash,
			0,
			time(),
			0,
		),
		array('id_llama')
	);

	// Increment freed counter.
	updateSettings(array('magic_llama_freed' => (isset($modSettings['magic_llama_freed']) ? (int) $modSettings['magic_llama_freed'] : 0) + 1));

	// Determine llama image dimensions.
	$image = !empty($modSettings['magic_llama_image']) ? $modSettings['magic_llama_image'] : 'golden_llama2.gif';
	$width = !empty($modSettings['magic_llama_width']) ? (int) $modSettings['magic_llama_width'] : 0;
	$height = !empty($modSettings['magic_llama_height']) ? (int) $modSettings['magic_llama_height'] : 0;

	// Auto-detect image dimensions if not set.
	if ($width == 0 || $height == 0)
	{
		$image_path = $settings['default_theme_dir'] . '/images/' . $image;
		if (file_exists($image_path))
		{
			$size = @getimagesize($image_path);
			if ($size !== false)
			{
				$width = $width > 0 ? $width : $size[0];
				$height = $height > 0 ? $height : $size[1];
			}
		}

		// Fallback defaults.
		if ($width == 0) $width = 64;
		if ($height == 0) $height = 64;
	}

	// Animation speed (lower = faster movement interval in ms).
	$speed = !empty($modSettings['magic_llama_speed']) ? (int) $modSettings['magic_llama_speed'] : 3;

	// Set context for the template layer.
	$context['magic_llama'] = array(
		'hash' => $llama_hash,
		'image_url' => $settings['default_theme_url'] . '/images/' . $image,
		'catch_url' => $scripturl . '?action=magicllama;llama=' . $llama_hash . ';' . $context['session_var'] . '=' . $context['session_id'],
		'width' => $width,
		'height' => $height,
		'speed' => $speed,
		'start_x' => mt_rand(50, 600),
		'start_y' => mt_rand(50, 400),
	);
}

/**
 * Action: ?action=magicllama
 * Handles catching a llama (supports both AJAX and regular requests).
 */
function magic_llama_catch()
{
	global $context, $smcFunc, $modSettings, $user_info, $txt, $scripturl;

	// Must be logged in.
	is_not_guest();

	// CSRF check.
	checkSession('get');

	// Load language.
	loadLanguage('MagicLlama');

	// Get the llama hash from the request.
	$llama_hash = isset($_REQUEST['llama']) ? $_REQUEST['llama'] : '';

	if (empty($llama_hash) || strlen($llama_hash) !== 32)
		fatal_lang_error('magic_llama_bad_id', false);

	// Look up this llama.
	$request = $smcFunc['db_query']('', '
		SELECT id_llama, llama_type, points, id_member, llama_hash
		FROM {db_prefix}magic_llama
		WHERE llama_hash = {string:hash}
		LIMIT 1',
		array(
			'hash' => $llama_hash,
		)
	);

	if ($smcFunc['db_num_rows']($request) == 0)
	{
		$smcFunc['db_free_result']($request);
		fatal_lang_error('magic_llama_bad_id', false);
	}

	$llama = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Already caught?
	if (!empty($llama['id_member']))
	{
		$context['magic_llama_result'] = array(
			'caught' => false,
			'title' => $txt['magic_llama_too_late_title'],
			'message' => !empty($modSettings['magic_llama_late_msg']) ? $modSettings['magic_llama_late_msg'] : $txt['magic_llama_too_late_default'],
		);
	}
	else
	{
		// Catch the llama! Update the record.
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}magic_llama
			SET id_member = {int:id_member}, caught_at = {int:caught_at}
			WHERE id_llama = {int:id_llama}
				AND id_member = 0',
			array(
				'id_member' => $user_info['id'],
				'caught_at' => time(),
				'id_llama' => $llama['id_llama'],
			)
		);

		// Check if we actually got it (race condition protection).
		if ($smcFunc['db_affected_rows']() == 0)
		{
			$context['magic_llama_result'] = array(
				'caught' => false,
				'title' => $txt['magic_llama_too_late_title'],
				'message' => !empty($modSettings['magic_llama_late_msg']) ? $modSettings['magic_llama_late_msg'] : $txt['magic_llama_too_late_default'],
			);
		}
		else
		{
			// Update or insert member stats.
			$good_field = ($llama['llama_type'] == 1) ? 'good' : 'bad';

			// Check if member row exists.
			$request = $smcFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}magic_llama_members
				WHERE id_member = {int:id_member}',
				array(
					'id_member' => $user_info['id'],
				)
			);

			if ($smcFunc['db_num_rows']($request) > 0)
			{
				$smcFunc['db_free_result']($request);

				// Update existing stats.
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}magic_llama_members
					SET ' . $good_field . '_llamas = ' . $good_field . '_llamas + 1,
						' . $good_field . '_points = ' . $good_field . '_points + {int:points}
					WHERE id_member = {int:id_member}',
					array(
						'points' => $llama['points'],
						'id_member' => $user_info['id'],
					)
				);
			}
			else
			{
				$smcFunc['db_free_result']($request);

				// Insert new member row.
				$smcFunc['db_insert']('insert',
					'{db_prefix}magic_llama_members',
					array(
						'id_member' => 'int',
						'good_llamas' => 'int',
						'good_points' => 'int',
						'bad_llamas' => 'int',
						'bad_points' => 'int',
						'hide_llama' => 'int',
					),
					array(
						$user_info['id'],
						$llama['llama_type'] == 1 ? 1 : 0,
						$llama['llama_type'] == 1 ? $llama['points'] : 0,
						$llama['llama_type'] == 2 ? 1 : 0,
						$llama['llama_type'] == 2 ? $llama['points'] : 0,
						0,
					),
					array('id_member')
				);
			}

			// Build the catch message.
			$type_name = !empty($modSettings['magic_llama_type' . $llama['llama_type'] . '_name']) ? $modSettings['magic_llama_type' . $llama['llama_type'] . '_name'] : ($llama['llama_type'] == 1 ? 'Golden Llama' : 'Evil Llama');
			$msg_template = !empty($modSettings['magic_llama_type' . $llama['llama_type'] . '_msg']) ? $modSettings['magic_llama_type' . $llama['llama_type'] . '_msg'] : '%N caught a %K worth %P points!';

			$display_points = ($llama['llama_type'] == 1) ? '+' . $llama['points'] : '-' . $llama['points'];

			$message = str_replace(
				array('%N', '%K', '%P'),
				array($user_info['name'], $type_name, $display_points),
				$msg_template
			);

			$context['magic_llama_result'] = array(
				'caught' => true,
				'title' => sprintf($txt['magic_llama_caught_title'], $type_name),
				'message' => $message,
				'type' => $llama['llama_type'],
				'points' => $llama['points'],
			);
		}
	}

	// Check if this is an AJAX request.
	if (isset($_REQUEST['xml']))
	{
		$context['sub_template'] = 'magic_llama_catch_xml';
		$context['template_layers'] = array();
		loadTemplate('MagicLlama');
		return;
	}

	// Regular page display.
	$context['page_title'] = $context['magic_llama_result']['title'];
	$context['sub_template'] = 'magic_llama_catch';
	$context['template_layers'] = array();
	loadTemplate('MagicLlama');

	// Use the generic wrapper.
	$context['template_layers'][] = 'html';
	$context['template_layers'][] = 'body';
}

/**
 * Action: ?action=llamalog
 * Admin view of all released/caught llamas with maintenance options.
 */
function magic_llama_log()
{
	global $context, $smcFunc, $modSettings, $txt, $scripturl, $user_info;

	// Admin only.
	isAllowedTo('admin_forum');

	// Load language and template.
	loadLanguage('MagicLlama');
	loadTemplate('MagicLlama');

	// Handle sub-actions.
	$sa = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : '';

	if ($sa === 'removeuncaught')
	{
		checkSession('get');

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}magic_llama
			WHERE id_member = 0',
			array()
		);

		redirectexit('action=llamalog');
	}
	elseif ($sa === 'removeall')
	{
		checkSession('get');

		$smcFunc['db_query']('', '
			TRUNCATE {db_prefix}magic_llama',
			array()
		);

		// Reset freed counter.
		updateSettings(array('magic_llama_freed' => 0));

		redirectexit('action=llamalog');
	}

	// Fetch all llamas with catcher info.
	$context['magic_llama_log'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT ml.id_llama, ml.llama_type, ml.points, ml.llama_hash,
			ml.id_member, ml.released_at, ml.caught_at,
			IFNULL(mem.real_name, {string:empty}) AS member_name
		FROM {db_prefix}magic_llama AS ml
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = ml.id_member AND ml.id_member > 0)
		ORDER BY ml.id_llama DESC',
		array(
			'empty' => '',
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$type_name = !empty($modSettings['magic_llama_type' . $row['llama_type'] . '_name']) ? $modSettings['magic_llama_type' . $row['llama_type'] . '_name'] : ($row['llama_type'] == 1 ? 'Good' : 'Evil');

		$context['magic_llama_log'][] = array(
			'id' => $row['id_llama'],
			'type' => $row['llama_type'],
			'type_name' => $type_name,
			'points' => $row['points'],
			'member_id' => $row['id_member'],
			'member_name' => $row['member_name'],
			'released' => timeformat($row['released_at']),
			'caught' => !empty($row['caught_at']) ? timeformat($row['caught_at']) : '',
		);
	}
	$smcFunc['db_free_result']($request);

	// Stats summary.
	$context['magic_llama_total_freed'] = !empty($modSettings['magic_llama_freed']) ? $modSettings['magic_llama_freed'] : 0;

	// Build maintenance URLs with session token.
	$context['magic_llama_remove_uncaught_url'] = $scripturl . '?action=llamalog;sa=removeuncaught;' . $context['session_var'] . '=' . $context['session_id'];
	$context['magic_llama_remove_all_url'] = $scripturl . '?action=llamalog;sa=removeall;' . $context['session_var'] . '=' . $context['session_id'];

	$context['page_title'] = $txt['magic_llama_log_title'];
	$context['sub_template'] = 'magic_llama_log';
}

/**
 * Profile area: Display llama stats and hide preference for a member.
 */
function magic_llama_profile($memID)
{
	global $context, $smcFunc, $modSettings, $txt, $user_info, $scripturl;

	loadLanguage('MagicLlama');
	loadTemplate('MagicLlama');

	// Load this member's llama stats.
	$request = $smcFunc['db_query']('', '
		SELECT good_llamas, good_points, bad_llamas, bad_points, hide_llama
		FROM {db_prefix}magic_llama_members
		WHERE id_member = {int:id_member}',
		array(
			'id_member' => $memID,
		)
	);

	if ($smcFunc['db_num_rows']($request) > 0)
	{
		$stats = $smcFunc['db_fetch_assoc']($request);
	}
	else
	{
		$stats = array(
			'good_llamas' => 0,
			'good_points' => 0,
			'bad_llamas' => 0,
			'bad_points' => 0,
			'hide_llama' => 0,
		);
	}
	$smcFunc['db_free_result']($request);

	// Handle save of hide preference.
	if (isset($_POST['save_llama_prefs']) && $memID == $user_info['id'])
	{
		checkSession();

		$hide = !empty($_POST['hide_llama']) ? 1 : 0;

		$smcFunc['db_insert']('replace',
			'{db_prefix}magic_llama_members',
			array(
				'id_member' => 'int',
				'good_llamas' => 'int',
				'good_points' => 'int',
				'bad_llamas' => 'int',
				'bad_points' => 'int',
				'hide_llama' => 'int',
			),
			array(
				$memID,
				$stats['good_llamas'],
				$stats['good_points'],
				$stats['bad_llamas'],
				$stats['bad_points'],
				$hide,
			),
			array('id_member')
		);

		$stats['hide_llama'] = $hide;
		redirectexit('action=profile;area=llamastats;u=' . $memID);
	}

	$context['magic_llama_stats'] = $stats;
	$context['magic_llama_stats']['net_points'] = $stats['good_points'] - $stats['bad_points'];
	$context['magic_llama_stats']['total_llamas'] = $stats['good_llamas'] + $stats['bad_llamas'];
	$context['magic_llama_is_own'] = ($memID == $user_info['id']);
	$context['magic_llama_allow_hide'] = !empty($modSettings['magic_llama_allow_hide']);

	$context['page_title'] = $txt['magic_llama_profile_title'];
	$context['sub_template'] = 'magic_llama_profile';
}
