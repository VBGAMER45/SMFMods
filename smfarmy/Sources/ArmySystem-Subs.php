<?php
/**
 * Army System - Shared Helper Functions
 *
 * Provides data loading, caching, calculations, and utility functions
 * used across all Army System modules.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Load all Army System settings from the database.
 *
 * Retrieves settings from {db_prefix}army_settings where core='armysystem',
 * caches them for one hour, and stores the result in $modSettings['army']
 * for global access.
 *
 * @return array Associative array of setting => current_value
 */
function army_load_settings()
{
	global $smcFunc, $modSettings;

	// Try cache first
	$settings = cache_get_data('army_settings', 3600);

	if ($settings !== null)
	{
		$modSettings['army'] = $settings;
		return $settings;
	}

	$settings = array();

	$request = $smcFunc['db_query']('', '
		SELECT setting, current_value
		FROM {db_prefix}army_settings
		WHERE core = {string:core}',
		array(
			'core' => 'armysystem',
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$settings[$row['setting']] = $row['current_value'];

	$smcFunc['db_free_result']($request);

	// Store in cache and make globally accessible
	cache_put_data('army_settings', $settings, 3600);
	$modSettings['army'] = $settings;

	return $settings;
}

/**
 * Load a single member's army data along with their race information.
 *
 * Performs a LEFT JOIN against army_races so race bonus data is included
 * even if the member has not yet selected a race (race fields will be null).
 *
 * @param int $id_member The member ID to load
 * @return array|false The member row with race data, or false if not found
 */
function army_load_member($id_member)
{
	global $smcFunc;

	$id_member = (int) $id_member;

	if ($id_member <= 0)
		return false;

	$request = $smcFunc['db_query']('', '
		SELECT
			am.*,
			ar.name AS race_name,
			ar.bonus_income, ar.bonus_discount, ar.bonus_casualties,
			ar.bonus_attack, ar.bonus_defence, ar.bonus_spy, ar.bonus_sentry,
			ar.default_icon, ar.train_atk_icon, ar.train_def_icon,
			ar.merc_icon, ar.merc_atk_icon, ar.merc_def_icon,
			ar.spy_icon, ar.sentry_icon
		FROM {db_prefix}army_members AS am
			LEFT JOIN {db_prefix}army_races AS ar ON (ar.race_id = am.race_id)
		WHERE am.id_member = {int:id_member}
		LIMIT 1',
		array(
			'id_member' => $id_member,
		)
	);

	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if (empty($row))
		return false;

	return $row;
}

/**
 * Load all items of a given type from the items table.
 *
 * Results are cached per type and ordered by the 'number' field ascending.
 *
 * Type codes: 'a' (attack weapons), 'd' (defense armor), 'b' (ships),
 * 'q' (spy tools), 'e' (sentry tools), 'f' (fort levels), 's' (siege levels),
 * 'up' (unit production), 'sl' (spy levels), 'ta'/'td'/'tu'/'ts'/'tw' (training),
 * 'ma'/'md'/'mu' (mercenaries).
 *
 * @param string $type The item type code
 * @return array Array of item rows, keyed by 'number'
 */
function army_load_items($type)
{
	global $smcFunc;

	$cache_key = 'army_items_' . $type;
	$items = cache_get_data($cache_key, 3600);

	if ($items !== null)
		return $items;

	$items = array();

	$request = $smcFunc['db_query']('', '
		SELECT id, type, number, name, value, price, letter, icon, repair
		FROM {db_prefix}army_items
		WHERE type = {string:type}
		ORDER BY number ASC',
		array(
			'type' => $type,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$items[$row['number']] = $row;

	$smcFunc['db_free_result']($request);

	cache_put_data($cache_key, $items, 3600);

	return $items;
}

/**
 * Load all races from the database.
 *
 * Results are cached and keyed by race_id.
 *
 * @return array Array of race rows, keyed by race_id
 */
function army_load_races()
{
	global $smcFunc;

	$races = cache_get_data('army_races', 3600);

	if ($races !== null)
		return $races;

	$races = array();

	$request = $smcFunc['db_query']('', '
		SELECT race_id, name, bonus_income, bonus_discount, bonus_casualties,
			bonus_attack, bonus_defence, bonus_spy, bonus_sentry,
			default_icon, train_atk_icon, train_def_icon,
			merc_icon, merc_atk_icon, merc_def_icon,
			spy_icon, sentry_icon
		FROM {db_prefix}army_races
		ORDER BY race_id ASC',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$races[$row['race_id']] = $row;

	$smcFunc['db_free_result']($request);

	cache_put_data('army_races', $races, 3600);

	return $races;
}

/**
 * Load a member's full inventory.
 *
 * Returns all inventory rows for the given member, keyed by a composite
 * key of "i_section.i_number" (e.g., "a.1" for the first attack weapon,
 * "d.3" for the third defense armor).
 *
 * @param int $id_member The member ID whose inventory to load
 * @return array Inventory rows keyed by "section.number"
 */
function army_load_inventory($id_member)
{
	global $smcFunc;

	$id_member = (int) $id_member;
	$inventory = array();

	if ($id_member <= 0)
		return $inventory;

	$request = $smcFunc['db_query']('', '
		SELECT i_id, i_section, i_number, i_member, i_quantity, i_strength, i_spy_sabbed
		FROM {db_prefix}army_inventory
		WHERE i_member = {int:member}',
		array(
			'member' => $id_member,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$inventory[$row['i_section'] . '.' . $row['i_number']] = $row;

	$smcFunc['db_free_result']($request);

	return $inventory;
}

/**
 * Ensure a member has a row in the army_members table.
 *
 * If the member does not yet exist, inserts a new row with default values
 * drawn from the Army System settings (reset_army, reset_turn, reset_money).
 * All starting soldiers are placed into the untrained pool.
 *
 * Uses db_insert with 'ignore' mode so existing rows are not overwritten.
 *
 * @param int $id_member The member ID to ensure exists
 * @return void
 */
function army_ensure_member($id_member)
{
	global $smcFunc, $modSettings;

	$id_member = (int) $id_member;

	if ($id_member <= 0)
		return;

	// Load settings if not already available
	if (!isset($modSettings['army']))
		army_load_settings();

	$settings = $modSettings['army'];

	$army_size = isset($settings['reset_army']) ? (int) $settings['reset_army'] : 10;
	$attack_turns = isset($settings['reset_turn']) ? (int) $settings['reset_turn'] : 25;
	$army_points = isset($settings['reset_money']) ? (int) $settings['reset_money'] : 50000;

	$smcFunc['db_insert']('ignore',
		'{db_prefix}army_members',
		array(
			'id_member' => 'int',
			'race_id' => 'int',
			'army_points' => 'int',
			'army_size' => 'int',
			'soldiers_attack' => 'int',
			'soldiers_defense' => 'int',
			'soldiers_spy' => 'int',
			'soldiers_sentry' => 'int',
			'soldiers_untrained' => 'int',
			'mercs_attack' => 'int',
			'mercs_defense' => 'int',
			'mercs_untrained' => 'int',
			'fort_level' => 'int',
			'siege_level' => 'int',
			'unit_prod_level' => 'int',
			'spy_skill_level' => 'int',
			'attack_turns' => 'int',
			'total_attacks' => 'int',
			'total_defends' => 'int',
			'rank_level' => 'int',
			'last_active' => 'int',
			'vacation_start' => 'int',
			'vacation_end' => 'int',
			'is_active' => 'int',
		),
		array(
			$id_member,
			0,              // race_id: not yet chosen
			$army_points,
			$army_size,
			0,              // soldiers_attack
			0,              // soldiers_defense
			0,              // soldiers_spy
			0,              // soldiers_sentry
			$army_size,     // soldiers_untrained: all start untrained
			0,              // mercs_attack
			0,              // mercs_defense
			0,              // mercs_untrained
			1,              // fort_level: starts at 1 (Camp)
			1,              // siege_level: starts at 1 (None)
			0,              // unit_prod_level
			0,              // spy_skill_level
			$attack_turns,
			0,              // total_attacks
			0,              // total_defends
			0,              // rank_level
			time(),         // last_active
			0,              // vacation_start
			0,              // vacation_end
			1,              // is_active
		),
		array('id_member')
	);
}

/**
 * Calculate total naval power for a member.
 *
 * Naval power is the sum of all ship strengths (i_section='b') multiplied
 * by their quantities. Ships contribute to BOTH attack and defense (50/50 split).
 *
 * @param array $member    Member data row (from army_load_member)
 * @param array $inventory Inventory data (from army_load_inventory)
 * @return float Total naval power (before split)
 */
function army_calculate_naval_power($member, $inventory)
{
	$base = 0.0;

	foreach ($inventory as $key => $item)
	{
		if ($item['i_section'] === 'b' && $item['i_quantity'] > 0)
			$base += (float) $item['i_quantity'] * (float) $item['i_strength'];
	}

	return $base;
}

/**
 * Calculate total offensive (attack) power for a member.
 *
 * Attack power is the sum of all equipped attack weapon strengths multiplied
 * by their quantities, plus mercenary attack strength, plus half of naval
 * power (ships contribute 50% to offense). The race attack bonus is then
 * applied as a percentage modifier.
 *
 * Note: Fort/siege interactions are handled in the battle calculator
 * (ArmySystem-Attack.php), not here. This returns the base attack power.
 *
 * @param array $member  Member data row (from army_load_member)
 * @param array $inventory Inventory data (from army_load_inventory)
 * @return float Total attack power after race bonus
 */
function army_calculate_attack_power($member, $inventory)
{
	$base = 0.0;

	// Sum all attack weapon contributions
	foreach ($inventory as $key => $item)
	{
		if ($item['i_section'] === 'a' && $item['i_quantity'] > 0)
			$base += (float) $item['i_quantity'] * (float) $item['i_strength'];
	}

	// Add mercenary attack contribution (base strength of 10 per merc)
	$merc_strength = 10;
	$base += (int) $member['mercs_attack'] * $merc_strength;

	// Add half of naval power (ships contribute 50% to offense)
	$naval_power = army_calculate_naval_power($member, $inventory);
	$base += $naval_power / 2;

	// Apply race bonus
	$bonus = isset($member['bonus_attack']) ? (int) $member['bonus_attack'] : 0;

	return army_apply_race_bonus($base, $bonus);
}

/**
 * Calculate total defensive power for a member.
 *
 * Defense power is the sum of all equipped defense armor strengths multiplied
 * by their quantities, plus mercenary defense strength, plus half of naval
 * power (ships contribute 50% to defense). The race defence bonus is then
 * applied as a percentage modifier.
 *
 * Note: Fortification bonuses are applied during battle calculation,
 * not in this base power computation.
 *
 * @param array $member  Member data row (from army_load_member)
 * @param array $inventory Inventory data (from army_load_inventory)
 * @return float Total defense power after race bonus
 */
function army_calculate_defense_power($member, $inventory)
{
	$base = 0.0;

	// Sum all defense armor contributions
	foreach ($inventory as $key => $item)
	{
		if ($item['i_section'] === 'd' && $item['i_quantity'] > 0)
			$base += (float) $item['i_quantity'] * (float) $item['i_strength'];
	}

	// Add mercenary defense contribution (base strength of 10 per merc)
	$merc_strength = 10;
	$base += (int) $member['mercs_defense'] * $merc_strength;

	// Add half of naval power (ships contribute 50% to defense)
	$naval_power = army_calculate_naval_power($member, $inventory);
	$base += $naval_power / 2;

	// Apply race bonus
	$bonus = isset($member['bonus_defence']) ? (int) $member['bonus_defence'] : 0;

	return army_apply_race_bonus($base, $bonus);
}

/**
 * Calculate total spy (espionage) power for a member.
 *
 * Spy power is based on the number of trained spy soldiers plus the
 * cumulative strength of all spy tools in the inventory. The race spy
 * bonus is applied as a percentage modifier.
 *
 * Formula:
 *   base = soldiers_spy + SUM(spy_tool_quantity * spy_tool_strength) for i_section='q'
 *   result = army_apply_race_bonus(base, bonus_spy)
 *
 * @param array $member  Member data row (from army_load_member)
 * @param array $inventory Inventory data (from army_load_inventory)
 * @return float Total spy power after race bonus
 */
function army_calculate_spy_power($member, $inventory)
{
	$base = (float) (int) $member['soldiers_spy'];

	// Sum all spy tool contributions
	foreach ($inventory as $key => $item)
	{
		if ($item['i_section'] === 'q' && $item['i_quantity'] > 0)
			$base += (float) $item['i_quantity'] * (float) $item['i_strength'];
	}

	// Apply race bonus
	$bonus = isset($member['bonus_spy']) ? (int) $member['bonus_spy'] : 0;

	return army_apply_race_bonus($base, $bonus);
}

/**
 * Calculate total sentry (counter-espionage) power for a member.
 *
 * Sentry power is based on the number of trained sentry soldiers plus the
 * cumulative strength of all sentry tools in the inventory. The race sentry
 * bonus is applied as a percentage modifier.
 *
 * Formula:
 *   base = soldiers_sentry + SUM(sentry_tool_quantity * sentry_tool_strength) for i_section='e'
 *   result = army_apply_race_bonus(base, bonus_sentry)
 *
 * @param array $member  Member data row (from army_load_member)
 * @param array $inventory Inventory data (from army_load_inventory)
 * @return float Total sentry power after race bonus
 */
function army_calculate_sentry_power($member, $inventory)
{
	$base = (float) (int) $member['soldiers_sentry'];

	// Sum all sentry tool contributions
	foreach ($inventory as $key => $item)
	{
		if ($item['i_section'] === 'e' && $item['i_quantity'] > 0)
			$base += (float) $item['i_quantity'] * (float) $item['i_strength'];
	}

	// Apply race bonus
	$bonus = isset($member['bonus_sentry']) ? (int) $member['bonus_sentry'] : 0;

	return army_apply_race_bonus($base, $bonus);
}

/**
 * Apply a race bonus percentage to a base value.
 *
 * The bonus is expressed as a whole-number percentage (e.g., 40 means +40%,
 * -10 means -10%). The result is clamped to a minimum of 0 to prevent
 * negative power values.
 *
 * @param float $base_value    The base numeric value
 * @param int   $bonus_percent The bonus as a percentage (positive or negative)
 * @return float The adjusted value, minimum 0
 */
function army_apply_race_bonus($base_value, $bonus_percent)
{
	$result = (float) $base_value * (1 + (int) $bonus_percent / 100);

	return max(0.0, $result);
}

/**
 * Format a number for display with thousands separators.
 *
 * @param int|float|string $number The number to format
 * @return string The formatted number string (e.g., "1,234,567")
 */
function army_format_number($number)
{
	return number_format((int) $number);
}

/**
 * Log an event to the army_events table.
 *
 * Event types (from the original system):
 *   1 = Attack result
 *   2 = Level up
 *   3 = Revive
 *   4 = Item transfer
 *   6 = Money transfer / donation
 *
 * Event text can use placeholders like <% FROM %>, <% TO %>, <% MONEYNAME %>
 * which are resolved at display time.
 *
 * @param int    $from The member ID who triggered the event
 * @param int    $to   The member ID who is the target (0 if none)
 * @param int    $type The event type code
 * @param string $text The event description text
 * @return void
 */
function army_log_event($from, $to, $type, $text)
{
	global $smcFunc;

	$smcFunc['db_insert']('insert',
		'{db_prefix}army_events',
		array(
			'event_time' => 'int',
			'event_from' => 'int',
			'event_to' => 'int',
			'event_type' => 'int',
			'event_text' => 'string',
		),
		array(
			time(),
			(int) $from,
			(int) $to,
			(int) $type,
			$text,
		),
		array('id')
	);
}

/**
 * Check for IP clone / multi-account abuse.
 *
 * Looks up the current user's IP address in the army_ip_tracking table to
 * find other member IDs sharing the same IP. Updates or inserts the
 * tracking record for the current member.
 *
 * Uses $user_info['ip'] (SMF's sanitized IP) which supports both IPv4 and
 * IPv6 addresses (the column is VARCHAR(45) to accommodate IPv6).
 *
 * @param int $id_member The current member's ID
 * @return array List of other member IDs sharing the same IP address
 */
function army_check_ip_clone($id_member)
{
	global $smcFunc, $user_info;

	$id_member = (int) $id_member;

	if ($id_member <= 0)
		return array();

	$ip = $user_info['ip'];
	$clone_ids = array();

	// Find other members using this IP
	$request = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}army_ip_tracking
		WHERE ip = {string:ip}
			AND id_member != {int:id_member}',
		array(
			'ip' => $ip,
			'id_member' => $id_member,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$clone_ids[] = (int) $row['id_member'];

	$smcFunc['db_free_result']($request);

	// Check if this member already has a tracking row for this IP
	$request = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}army_ip_tracking
		WHERE ip = {string:ip}
			AND id_member = {int:id_member}
		LIMIT 1',
		array(
			'ip' => $ip,
			'id_member' => $id_member,
		)
	);

	$exists = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if ($exists)
	{
		// Update existing tracking record
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}army_ip_tracking
			SET last_time = {int:time}
			WHERE ip = {string:ip}
				AND id_member = {int:id_member}',
			array(
				'time' => time(),
				'ip' => $ip,
				'id_member' => $id_member,
			)
		);
	}
	else
	{
		// Insert new tracking record
		$smcFunc['db_insert']('insert',
			'{db_prefix}army_ip_tracking',
			array(
				'ip' => 'string',
				'id_member' => 'int',
				'last_time' => 'int',
			),
			array(
				$ip,
				$id_member,
				time(),
			),
			array()
		);
	}

	return $clone_ids;
}

/**
 * Check if a member is currently on vacation.
 *
 * Vacation mode protects the member from attacks and prevents them from
 * performing most actions for the duration.
 *
 * @param array $member_data Member data row (must contain 'vacation_end')
 * @return bool True if the member is on vacation, false otherwise
 */
function army_check_vacation($member_data)
{
	if (empty($member_data['vacation_end']))
		return false;

	return (int) $member_data['vacation_end'] > time();
}

/**
 * Check if a member is currently active and able to participate.
 *
 * A member is considered active when their is_active flag is set to 1
 * AND they are not currently on vacation.
 *
 * @param array $member_data Member data row (must contain 'is_active' and 'vacation_end')
 * @return bool True if the member is active and not on vacation
 */
function army_is_active($member_data)
{
	return (int) $member_data['is_active'] === 1 && !army_check_vacation($member_data);
}

/**
 * Invalidate (remove) a specific cache entry.
 *
 * Call this after modifying data that has been cached (e.g., after updating
 * settings, items, or races) to ensure fresh data is loaded on the next request.
 *
 * Common cache keys:
 *   'army_settings'       - System settings
 *   'army_races'          - Race definitions
 *   'army_items_{type}'   - Items by type (e.g., 'army_items_a')
 *
 * @param string $key The cache key to invalidate
 * @return void
 */
function army_invalidate_cache($key)
{
	cache_put_data($key, null, 0);
}

/**
 * Determine a member's rank level based on their army size.
 *
 * Ranks range from 1 (smallest armies) to 10 (largest armies).
 * The rank thresholds are:
 *   Rank 1:  0 - 50
 *   Rank 2:  51 - 200
 *   Rank 3:  201 - 500
 *   Rank 4:  501 - 1,000
 *   Rank 5:  1,001 - 2,500
 *   Rank 6:  2,501 - 5,000
 *   Rank 7:  5,001 - 10,000
 *   Rank 8:  10,001 - 25,000
 *   Rank 9:  25,001 - 50,000
 *   Rank 10: 50,001+
 *
 * @param array $member_data Member data row (must contain 'army_size')
 * @return int The rank level (1-10)
 */
function army_get_rank($member_data)
{
	$size = (int) $member_data['army_size'];

	if ($size > 50000)
		return 10;
	if ($size > 25000)
		return 9;
	if ($size > 10000)
		return 8;
	if ($size > 5000)
		return 7;
	if ($size > 2500)
		return 6;
	if ($size > 1000)
		return 5;
	if ($size > 500)
		return 4;
	if ($size > 200)
		return 3;
	if ($size > 50)
		return 2;

	return 1;
}
