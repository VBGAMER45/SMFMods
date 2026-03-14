<?php
/**
 * Army System - Event Feed
 *
 * Display a paginated feed of recent army events (attacks, level-ups,
 * revives, transfers, donations). Event text placeholders are resolved
 * to member profile links using a batch-loaded name cache.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Event feed page with pagination.
 *
 * Displays all army events ordered by most recent first, 25 per page.
 * For each event, template placeholders are replaced:
 *   <% FROM %>     => linked member name using event_from ID
 *   <% TO %>       => linked member name using event_to ID
 *   <% MONEYNAME %> => the configured currency name
 *
 * Member names are batch-loaded to avoid N+1 queries: all unique member
 * IDs referenced in the current page of events are collected and resolved
 * in a single query.
 *
 * Event types (from the original system):
 *   1 = Attack result
 *   2 = Level up
 *   3 = Revive
 *   4 = Item transfer
 *   6 = Money transfer / donation
 *
 * @return void
 */
function ArmyEvents()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_view');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	$settings = $modSettings['army'];

	// Load the template
	loadTemplate('ArmySystem-Events');

	$currency_name = $settings['currency_name'] ?? ($txt['army_currency'] ?? 'Gold');

	// Count total events
	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}army_events',
		array()
	);

	list($total_events) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);
	$total_events = (int) $total_events;

	// Pagination
	$per_page = 25;
	$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

	if ($start < 0)
		$start = 0;

	$context['page_index'] = constructPageIndex(
		$scripturl . '?action=army;sa=events',
		$start,
		$total_events,
		$per_page
	);
	$context['start'] = $start;

	// Query events for the current page
	$events = array();
	$member_ids = array();

	$request = $smcFunc['db_query']('', '
		SELECT ae.id, ae.event_time, ae.event_from, ae.event_to,
			ae.event_type, ae.event_text
		FROM {db_prefix}army_events AS ae
		ORDER BY ae.event_time DESC
		LIMIT {int:start}, {int:per_page}',
		array(
			'start' => $start,
			'per_page' => $per_page,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$events[] = $row;

		// Collect unique member IDs for batch name lookup
		$from_id = (int) $row['event_from'];
		$to_id = (int) $row['event_to'];

		if ($from_id > 0)
			$member_ids[$from_id] = true;

		if ($to_id > 0)
			$member_ids[$to_id] = true;
	}

	$smcFunc['db_free_result']($request);

	// Batch-load member names to avoid N+1 queries
	$member_names = array();

	if (!empty($member_ids))
	{
		$id_list = array_keys($member_ids);

		$request = $smcFunc['db_query']('', '
			SELECT id_member, real_name
			FROM {db_prefix}members
			WHERE id_member IN ({array_int:ids})',
			array(
				'ids' => $id_list,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$member_names[(int) $row['id_member']] = $row['real_name'];

		$smcFunc['db_free_result']($request);
	}

	// Process events: resolve placeholders
	$context['army_events'] = array();
	$unknown_name = $txt['army_unknown_player'] ?? 'Unknown';

	foreach ($events as $event)
	{
		$from_id = (int) $event['event_from'];
		$to_id = (int) $event['event_to'];

		// Resolve member names with profile links
		$from_name = isset($member_names[$from_id]) ? $member_names[$from_id] : $unknown_name;
		$to_name = isset($member_names[$to_id]) ? $member_names[$to_id] : $unknown_name;

		// Build linked names for the event text
		$from_link = ($from_id > 0)
			? '<a href="' . $scripturl . '?action=army;sa=profile;u=' . $from_id . '">' . $from_name . '</a>'
			: $from_name;

		$to_link = ($to_id > 0)
			? '<a href="' . $scripturl . '?action=army;sa=profile;u=' . $to_id . '">' . $to_name . '</a>'
			: $to_name;

		// Replace placeholders in event text
		$event_text = $event['event_text'];
		$event_text = str_replace('<% FROM %>', $from_link, $event_text);
		$event_text = str_replace('<% TO %>', $to_link, $event_text);
		$event_text = str_replace('<% MONEYNAME %>', $currency_name, $event_text);

		// Event type labels and CSS classes for the template
		$type = (int) $event['event_type'];
		$type_labels = array(
			1 => $txt['army_event_attack'] ?? 'Attack',
			2 => $txt['army_event_levelup'] ?? 'Level Up',
			3 => $txt['army_event_revive'] ?? 'Revive',
			4 => $txt['army_event_transfer'] ?? 'Transfer',
			6 => $txt['army_event_donation'] ?? 'Donation',
		);

		$type_classes = array(
			1 => 'army_event_attack',
			2 => 'army_event_levelup',
			3 => 'army_event_revive',
			4 => 'army_event_transfer',
			6 => 'army_event_donation',
		);

		$context['army_events'][] = array(
			'id' => (int) $event['id'],
			'time' => timeformat((int) $event['event_time']),
			'time_raw' => (int) $event['event_time'],
			'type' => $type,
			'type_label' => $type_labels[$type] ?? ($txt['army_event_other'] ?? 'Event'),
			'type_class' => $type_classes[$type] ?? 'army_event_other',
			'text' => $event_text,
			'from_id' => $from_id,
			'from_name' => $from_name,
			'to_id' => $to_id,
			'to_name' => $to_name,
		);
	}

	$context['army_events_total'] = $total_events;
	$context['army_currency'] = $currency_name;

	$context['sub_template'] = 'army_events';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_events_title'] ?? 'Events');
}
