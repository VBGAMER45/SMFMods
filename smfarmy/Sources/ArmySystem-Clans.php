<?php
/**
 * Army System - Clan System
 *
 * Create, join, manage, leave clans. Leaders can invite, approve/deny
 * requests, remove members, edit clan details, transfer leadership, and
 * disband. Members can join open clans, request to join invite-only clans,
 * accept/decline invitations, and leave.
 *
 * @package ArmySystem
 * @version 1.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Clan listing page with optional clan creation.
 *
 * GET: Displays all clans with member counts. Players with army_play
 * permission who have chosen a race can create a new clan via the form.
 *
 * POST (create_clan): Creates a new clan with the current player as
 * leader. Validates clan name (non-empty, max 255 chars) and inserts
 * the clan and the creator as the first member.
 *
 * @return void
 */
function ArmyClans()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_view');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	// Load the template
	loadTemplate('ArmySystem-Clans');

	// Determine if the current user can create a clan
	$can_create = false;
	$member = false;
	$current_clan_id = 0;

	if (!$user_info['is_guest'] && allowedTo('army_play'))
	{
		$member = army_load_member($user_info['id']);

		if ($member !== false && !empty($member['race_id']) && army_is_active($member))
		{
			// Check if they are already in a clan
			$request = $smcFunc['db_query']('', '
				SELECT clan_id
				FROM {db_prefix}army_clan_members
				WHERE id_member = {int:id_member}
				LIMIT 1',
				array(
					'id_member' => $user_info['id'],
				)
			);

			$row = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);

			if ($row !== null)
				$current_clan_id = (int) $row['clan_id'];
			else
				$can_create = true;
		}
	}

	// -------------------------------------------------------
	// POST handler: create a new clan
	// -------------------------------------------------------
	if (isset($_POST['create_clan']) && $can_create)
	{
		checkSession();

		$clan_name = isset($_POST['clan_name']) ? trim($smcFunc['htmlspecialchars']($_POST['clan_name'])) : '';
		$clan_desc = isset($_POST['clan_description']) ? trim($smcFunc['htmlspecialchars']($_POST['clan_description'])) : '';
		$by_invite = !empty($_POST['c_by_invite']) ? 1 : 0;
		$by_join = !empty($_POST['c_by_join']) ? 1 : 0;

		// Validate clan name
		if ($clan_name === '' || strlen($clan_name) > 255)
			fatal_lang_error('army_clan_invalid_name', false);

		// Check for duplicate clan name
		$request = $smcFunc['db_query']('', '
			SELECT c_id
			FROM {db_prefix}army_clans
			WHERE c_name = {string:name}
			LIMIT 1',
			array(
				'name' => $clan_name,
			)
		);

		$exists = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		if ($exists !== null)
			fatal_lang_error('army_clan_name_taken', false);

		// Insert the new clan
		$smcFunc['db_insert']('insert',
			'{db_prefix}army_clans',
			array(
				'c_name' => 'string',
				'c_description' => 'string',
				'c_leader' => 'int',
				'c_started' => 'int',
				'c_by_invite' => 'int',
				'c_by_join' => 'int',
				'c_notes' => 'string',
			),
			array(
				$clan_name,
				$clan_desc,
				$user_info['id'],
				time(),
				$by_invite,
				$by_join,
				'',
			),
			array('c_id')
		);

		// Get the new clan ID
		$new_clan_id = $smcFunc['db_insert_id']('{db_prefix}army_clans', 'c_id');

		// Add the creator as the first member
		$smcFunc['db_insert']('insert',
			'{db_prefix}army_clan_members',
			array(
				'clan_id' => 'int',
				'id_member' => 'int',
				'time_joined' => 'int',
			),
			array(
				$new_clan_id,
				$user_info['id'],
				time(),
			),
			array('id_member')
		);

		// Redirect to the new clan page
		redirectexit('action=army;sa=clan;id=' . $new_clan_id);
		return;
	}

	// -------------------------------------------------------
	// GET: Display all clans
	// -------------------------------------------------------

	$context['army_clans'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT c.c_id, c.c_name, c.c_description, c.c_leader, c.c_started,
			c.c_by_invite, c.c_by_join,
			COUNT(cm.id_member) AS member_count,
			mem.real_name AS leader_name
		FROM {db_prefix}army_clans AS c
			LEFT JOIN {db_prefix}army_clan_members AS cm ON (cm.clan_id = c.c_id)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = c.c_leader)
		GROUP BY c.c_id, c.c_name, c.c_description, c.c_leader, c.c_started,
			c.c_by_invite, c.c_by_join, mem.real_name
		ORDER BY member_count DESC, c.c_name ASC',
		array()
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['army_clans'][] = array(
			'id' => (int) $row['c_id'],
			'name' => $row['c_name'],
			'description' => $row['c_description'],
			'leader_id' => (int) $row['c_leader'],
			'leader_name' => $row['leader_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
			'started' => timeformat((int) $row['c_started']),
			'started_raw' => (int) $row['c_started'],
			'by_invite' => (int) $row['c_by_invite'],
			'by_join' => (int) $row['c_by_join'],
			'member_count' => (int) $row['member_count'],
			'url' => $scripturl . '?action=army;sa=clan;id=' . (int) $row['c_id'],
		);
	}

	$smcFunc['db_free_result']($request);

	// Context for the template
	$context['army_can_create'] = $can_create;
	$context['army_current_clan_id'] = $current_clan_id;

	// Session tokens for the create form
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	$context['sub_template'] = 'army_clans';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . ($txt['army_clans_title'] ?? 'Clans');
}

/**
 * Clan detail view with full management actions.
 *
 * GET: Displays clan info, member roster, and pending requests/invitations.
 * Available actions depend on the user's relationship to the clan.
 *
 * Leader actions (POST with checkSession):
 *   invite   - Invite a member by ID (inserts into pending with status=1)
 *   approve  - Approve a join request (moves from pending to members)
 *   deny     - Deny a join request (deletes pending row)
 *   remove   - Remove a member (not self)
 *   edit     - Update clan description, notes, invite/join flags
 *   transfer - Transfer leadership to another member
 *   disband  - Delete the clan, all members, and all pending rows
 *
 * Member actions (POST with checkSession):
 *   join           - Join open clan or request to join invite-only clan
 *   leave          - Leave the clan (leader cannot leave)
 *   accept_invite  - Accept a pending invitation (status=1)
 *   decline_invite - Decline a pending invitation
 *
 * @return void
 */
function ArmyClanView()
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc, $sourcedir;

	isAllowedTo('army_view');

	// Load shared helpers and settings
	require_once($sourcedir . '/ArmySystem-Subs.php');

	if (!isset($modSettings['army']))
		army_load_settings();

	// Load the template
	loadTemplate('ArmySystem-Clans');

	// Get the clan ID
	$clan_id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

	if ($clan_id <= 0)
	{
		redirectexit('action=army;sa=clans');
		return;
	}

	// Load the clan
	$request = $smcFunc['db_query']('', '
		SELECT c.c_id, c.c_name, c.c_description, c.c_leader, c.c_started,
			c.c_by_invite, c.c_by_join, c.c_notes,
			mem.real_name AS leader_name
		FROM {db_prefix}army_clans AS c
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = c.c_leader)
		WHERE c.c_id = {int:clan_id}
		LIMIT 1',
		array(
			'clan_id' => $clan_id,
		)
	);

	$clan = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	if ($clan === null)
		fatal_lang_error('army_clan_not_found', false);

	// Determine user's relationship to this clan
	$is_leader = (!$user_info['is_guest'] && (int) $clan['c_leader'] === $user_info['id']);
	$is_member = false;
	$has_pending = false;
	$pending_status = -1;

	if (!$user_info['is_guest'])
	{
		// Check if user is a member
		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}army_clan_members
			WHERE clan_id = {int:clan_id}
				AND id_member = {int:id_member}
			LIMIT 1',
			array(
				'clan_id' => $clan_id,
				'id_member' => $user_info['id'],
			)
		);

		$mem_row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		$is_member = ($mem_row !== null);

		// Check if user has a pending request or invitation
		if (!$is_member)
		{
			$request = $smcFunc['db_query']('', '
				SELECT status
				FROM {db_prefix}army_clan_pending
				WHERE clan_id = {int:clan_id}
					AND id_member = {int:id_member}
				LIMIT 1',
				array(
					'clan_id' => $clan_id,
					'id_member' => $user_info['id'],
				)
			);

			$pend_row = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);

			if ($pend_row !== null)
			{
				$has_pending = true;
				$pending_status = (int) $pend_row['status']; // 0 = requested, 1 = invited
			}
		}
	}

	// -------------------------------------------------------
	// POST handlers
	// -------------------------------------------------------
	if (!$user_info['is_guest'] && isset($_POST['clan_action']))
	{
		checkSession();

		$action = $_POST['clan_action'];

		// --- Leader actions ---
		if ($is_leader)
		{
			switch ($action)
			{
				case 'invite':
					$target = (int) ($_POST['invite_member'] ?? 0);

					if ($target <= 0)
						fatal_lang_error('army_clan_invalid_member', false);

					// Validate target exists, has race, is not already a member or pending
					$target_member = army_load_member($target);

					if ($target_member === false || empty($target_member['race_id']))
						fatal_lang_error('army_clan_invalid_member', false);

					// Check not already a member
					$request = $smcFunc['db_query']('', '
						SELECT id_member
						FROM {db_prefix}army_clan_members
						WHERE id_member = {int:target}
						LIMIT 1',
						array(
							'target' => $target,
						)
					);

					$already_member = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					if ($already_member !== null)
						fatal_lang_error('army_clan_already_member', false);

					// Delete any existing pending row for this member/clan, then insert fresh
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}army_clan_pending
						WHERE clan_id = {int:clan_id}
							AND id_member = {int:target}',
						array(
							'clan_id' => $clan_id,
							'target' => $target,
						)
					);

					$smcFunc['db_insert']('insert',
						'{db_prefix}army_clan_pending',
						array(
							'status' => 'int',
							'clan_id' => 'int',
							'id_member' => 'int',
							'time_pending' => 'int',
						),
						array(
							1, // status 1 = invited by leader
							$clan_id,
							$target,
							time(),
						),
						array('clan_id', 'id_member')
					);

					redirectexit('action=army;sa=clan;id=' . $clan_id . ';invited=1');
					return;

				case 'approve':
					$target = (int) ($_POST['approve_member'] ?? 0);

					if ($target <= 0)
						fatal_lang_error('army_clan_invalid_member', false);

					// Verify pending row exists with status=0 (join request)
					$request = $smcFunc['db_query']('', '
						SELECT id_member
						FROM {db_prefix}army_clan_pending
						WHERE clan_id = {int:clan_id}
							AND id_member = {int:target}
							AND status = {int:requested}
						LIMIT 1',
						array(
							'clan_id' => $clan_id,
							'target' => $target,
							'requested' => 0,
						)
					);

					$prow = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					if ($prow === null)
						fatal_lang_error('army_clan_no_pending', false);

					// Remove from pending
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}army_clan_pending
						WHERE clan_id = {int:clan_id}
							AND id_member = {int:target}',
						array(
							'clan_id' => $clan_id,
							'target' => $target,
						)
					);

					// Remove from any other clan they might have joined since requesting
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}army_clan_members
						WHERE id_member = {int:target}',
						array(
							'target' => $target,
						)
					);

					// Add to clan members
					$smcFunc['db_insert']('insert',
						'{db_prefix}army_clan_members',
						array(
							'clan_id' => 'int',
							'id_member' => 'int',
							'time_joined' => 'int',
						),
						array(
							$clan_id,
							$target,
							time(),
						),
						array('id_member')
					);

					redirectexit('action=army;sa=clan;id=' . $clan_id . ';approved=1');
					return;

				case 'deny':
					$target = (int) ($_POST['deny_member'] ?? 0);

					if ($target <= 0)
						fatal_lang_error('army_clan_invalid_member', false);

					// Delete the pending row
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}army_clan_pending
						WHERE clan_id = {int:clan_id}
							AND id_member = {int:target}',
						array(
							'clan_id' => $clan_id,
							'target' => $target,
						)
					);

					redirectexit('action=army;sa=clan;id=' . $clan_id . ';denied=1');
					return;

				case 'remove':
					$target = (int) ($_POST['remove_member'] ?? 0);

					if ($target <= 0)
						fatal_lang_error('army_clan_invalid_member', false);

					// Cannot remove yourself (leader)
					if ($target === $user_info['id'])
						fatal_lang_error('army_clan_cannot_remove_self', false);

					// Delete from clan members
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}army_clan_members
						WHERE clan_id = {int:clan_id}
							AND id_member = {int:target}',
						array(
							'clan_id' => $clan_id,
							'target' => $target,
						)
					);

					redirectexit('action=army;sa=clan;id=' . $clan_id . ';removed=1');
					return;

				case 'edit':
					$new_desc = isset($_POST['clan_description']) ? trim($smcFunc['htmlspecialchars']($_POST['clan_description'])) : '';
					$new_notes = isset($_POST['clan_notes']) ? trim($smcFunc['htmlspecialchars']($_POST['clan_notes'])) : '';
					$new_invite = !empty($_POST['c_by_invite']) ? 1 : 0;
					$new_join = !empty($_POST['c_by_join']) ? 1 : 0;

					$smcFunc['db_query']('', '
						UPDATE {db_prefix}army_clans
						SET c_description = {string:desc},
							c_notes = {string:notes},
							c_by_invite = {int:invite},
							c_by_join = {int:join}
						WHERE c_id = {int:clan_id}',
						array(
							'desc' => $new_desc,
							'notes' => $new_notes,
							'invite' => $new_invite,
							'join' => $new_join,
							'clan_id' => $clan_id,
						)
					);

					redirectexit('action=army;sa=clan;id=' . $clan_id . ';edited=1');
					return;

				case 'transfer':
					$target = (int) ($_POST['new_leader'] ?? 0);

					if ($target <= 0)
						fatal_lang_error('army_clan_invalid_member', false);

					// Cannot transfer to yourself
					if ($target === $user_info['id'])
						fatal_lang_error('army_clan_transfer_self', false);

					// Verify target is a member of this clan
					$request = $smcFunc['db_query']('', '
						SELECT id_member
						FROM {db_prefix}army_clan_members
						WHERE clan_id = {int:clan_id}
							AND id_member = {int:target}
						LIMIT 1',
						array(
							'clan_id' => $clan_id,
							'target' => $target,
						)
					);

					$trow = $smcFunc['db_fetch_assoc']($request);
					$smcFunc['db_free_result']($request);

					if ($trow === null)
						fatal_lang_error('army_clan_not_a_member', false);

					// Update clan leader
					$smcFunc['db_query']('', '
						UPDATE {db_prefix}army_clans
						SET c_leader = {int:new_leader}
						WHERE c_id = {int:clan_id}',
						array(
							'new_leader' => $target,
							'clan_id' => $clan_id,
						)
					);

					redirectexit('action=army;sa=clan;id=' . $clan_id . ';transferred=1');
					return;

				case 'disband':
					// Delete all pending rows for this clan
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}army_clan_pending
						WHERE clan_id = {int:clan_id}',
						array(
							'clan_id' => $clan_id,
						)
					);

					// Delete all members
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}army_clan_members
						WHERE clan_id = {int:clan_id}',
						array(
							'clan_id' => $clan_id,
						)
					);

					// Delete the clan itself
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}army_clans
						WHERE c_id = {int:clan_id}',
						array(
							'clan_id' => $clan_id,
						)
					);

					redirectexit('action=army;sa=clans;disbanded=1');
					return;
			}
		}

		// --- Member actions (non-leader) ---
		switch ($action)
		{
			case 'join':
				// Must have permission and a race
				if (!allowedTo('army_play'))
					fatal_lang_error('army_no_permission', false);

				$member = army_load_member($user_info['id']);

				if ($member === false || empty($member['race_id']))
					fatal_lang_error('army_no_race', false);

				if (army_check_vacation($member))
					fatal_lang_error('army_on_vacation', false);

				// Must not already be in a clan
				$request = $smcFunc['db_query']('', '
					SELECT id_member
					FROM {db_prefix}army_clan_members
					WHERE id_member = {int:id_member}
					LIMIT 1',
					array(
						'id_member' => $user_info['id'],
					)
				);

				$existing = $smcFunc['db_fetch_assoc']($request);
				$smcFunc['db_free_result']($request);

				if ($existing !== null)
					fatal_lang_error('army_clan_already_in_clan', false);

				// Open join?
				if (!empty($clan['c_by_join']))
				{
					// Direct join: remove any pending rows first
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}army_clan_pending
						WHERE clan_id = {int:clan_id}
							AND id_member = {int:id_member}',
						array(
							'clan_id' => $clan_id,
							'id_member' => $user_info['id'],
						)
					);

					// Insert as member
					$smcFunc['db_insert']('insert',
						'{db_prefix}army_clan_members',
						array(
							'clan_id' => 'int',
							'id_member' => 'int',
							'time_joined' => 'int',
						),
						array(
							$clan_id,
							$user_info['id'],
							time(),
						),
						array('id_member')
					);

					redirectexit('action=army;sa=clan;id=' . $clan_id . ';joined=1');
					return;
				}
				else
				{
					// Invite-only or closed: submit a join request
					// Delete any existing pending row, then insert
					$smcFunc['db_query']('', '
						DELETE FROM {db_prefix}army_clan_pending
						WHERE clan_id = {int:clan_id}
							AND id_member = {int:id_member}',
						array(
							'clan_id' => $clan_id,
							'id_member' => $user_info['id'],
						)
					);

					$smcFunc['db_insert']('insert',
						'{db_prefix}army_clan_pending',
						array(
							'status' => 'int',
							'clan_id' => 'int',
							'id_member' => 'int',
							'time_pending' => 'int',
						),
						array(
							0, // status 0 = member requested to join
							$clan_id,
							$user_info['id'],
							time(),
						),
						array('clan_id', 'id_member')
					);

					redirectexit('action=army;sa=clan;id=' . $clan_id . ';requested=1');
					return;
				}

			case 'leave':
				if (!$is_member)
					fatal_lang_error('army_clan_not_a_member', false);

				// Leader cannot leave (must transfer or disband)
				if ($is_leader)
					fatal_lang_error('army_clan_leader_cannot_leave', false);

				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}army_clan_members
					WHERE clan_id = {int:clan_id}
						AND id_member = {int:id_member}',
					array(
						'clan_id' => $clan_id,
						'id_member' => $user_info['id'],
					)
				);

				redirectexit('action=army;sa=clans;left=1');
				return;

			case 'accept_invite':
				// Must have a pending invitation (status=1)
				if (!$has_pending || $pending_status !== 1)
					fatal_lang_error('army_clan_no_invite', false);

				// Must not already be in a clan
				$request = $smcFunc['db_query']('', '
					SELECT id_member
					FROM {db_prefix}army_clan_members
					WHERE id_member = {int:id_member}
					LIMIT 1',
					array(
						'id_member' => $user_info['id'],
					)
				);

				$existing = $smcFunc['db_fetch_assoc']($request);
				$smcFunc['db_free_result']($request);

				if ($existing !== null)
					fatal_lang_error('army_clan_already_in_clan', false);

				// Remove from pending
				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}army_clan_pending
					WHERE clan_id = {int:clan_id}
						AND id_member = {int:id_member}',
					array(
						'clan_id' => $clan_id,
						'id_member' => $user_info['id'],
					)
				);

				// Add as member
				$smcFunc['db_insert']('insert',
					'{db_prefix}army_clan_members',
					array(
						'clan_id' => 'int',
						'id_member' => 'int',
						'time_joined' => 'int',
					),
					array(
						$clan_id,
						$user_info['id'],
						time(),
					),
					array('id_member')
				);

				redirectexit('action=army;sa=clan;id=' . $clan_id . ';joined=1');
				return;

			case 'decline_invite':
				// Must have a pending invitation (status=1)
				if (!$has_pending || $pending_status !== 1)
					fatal_lang_error('army_clan_no_invite', false);

				$smcFunc['db_query']('', '
					DELETE FROM {db_prefix}army_clan_pending
					WHERE clan_id = {int:clan_id}
						AND id_member = {int:id_member}',
					array(
						'clan_id' => $clan_id,
						'id_member' => $user_info['id'],
					)
				);

				redirectexit('action=army;sa=clan;id=' . $clan_id . ';declined=1');
				return;
		}
	}

	// -------------------------------------------------------
	// GET: Display the clan detail view
	// -------------------------------------------------------

	// Reload clan data after potential POST changes
	$request = $smcFunc['db_query']('', '
		SELECT c.c_id, c.c_name, c.c_description, c.c_leader, c.c_started,
			c.c_by_invite, c.c_by_join, c.c_notes,
			mem.real_name AS leader_name
		FROM {db_prefix}army_clans AS c
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = c.c_leader)
		WHERE c.c_id = {int:clan_id}
		LIMIT 1',
		array(
			'clan_id' => $clan_id,
		)
	);

	$clan = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);

	// Clan may have been disbanded
	if ($clan === null)
	{
		redirectexit('action=army;sa=clans');
		return;
	}

	// Recompute user relationship (may have changed from POST)
	$is_leader = (!$user_info['is_guest'] && (int) $clan['c_leader'] === $user_info['id']);
	$is_member = false;
	$has_pending = false;
	$pending_status = -1;

	if (!$user_info['is_guest'])
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member
			FROM {db_prefix}army_clan_members
			WHERE clan_id = {int:clan_id}
				AND id_member = {int:id_member}
			LIMIT 1',
			array(
				'clan_id' => $clan_id,
				'id_member' => $user_info['id'],
			)
		);

		$mem_row = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		$is_member = ($mem_row !== null);

		if (!$is_member)
		{
			$request = $smcFunc['db_query']('', '
				SELECT status
				FROM {db_prefix}army_clan_pending
				WHERE clan_id = {int:clan_id}
					AND id_member = {int:id_member}
				LIMIT 1',
				array(
					'clan_id' => $clan_id,
					'id_member' => $user_info['id'],
				)
			);

			$pend_row = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);

			if ($pend_row !== null)
			{
				$has_pending = true;
				$pending_status = (int) $pend_row['status'];
			}
		}
	}

	// Load clan members
	$context['army_clan_members'] = array();

	$request = $smcFunc['db_query']('', '
		SELECT cm.id_member, cm.time_joined,
			mem.real_name,
			am.army_size, am.race_id, am.rank_level,
			ar.name AS race_name
		FROM {db_prefix}army_clan_members AS cm
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = cm.id_member)
			LEFT JOIN {db_prefix}army_members AS am ON (am.id_member = cm.id_member)
			LEFT JOIN {db_prefix}army_races AS ar ON (ar.race_id = am.race_id)
		WHERE cm.clan_id = {int:clan_id}
		ORDER BY cm.time_joined ASC',
		array(
			'clan_id' => $clan_id,
		)
	);

	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['army_clan_members'][] = array(
			'id' => (int) $row['id_member'],
			'name' => $row['real_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
			'time_joined' => timeformat((int) $row['time_joined']),
			'time_joined_raw' => (int) $row['time_joined'],
			'army_size' => army_format_number($row['army_size'] ?? 0),
			'army_size_raw' => (int) ($row['army_size'] ?? 0),
			'race_name' => $row['race_name'] ?? '',
			'rank_level' => (int) ($row['rank_level'] ?? 0),
			'is_leader' => ((int) $row['id_member'] === (int) $clan['c_leader']),
			'profile_url' => $scripturl . '?action=army;sa=profile;u=' . (int) $row['id_member'],
		);
	}

	$smcFunc['db_free_result']($request);

	// Load pending requests/invitations (visible to leader)
	$context['army_clan_pending'] = array();

	if ($is_leader)
	{
		$request = $smcFunc['db_query']('', '
			SELECT cp.status, cp.id_member, cp.time_pending,
				mem.real_name
			FROM {db_prefix}army_clan_pending AS cp
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = cp.id_member)
			WHERE cp.clan_id = {int:clan_id}
			ORDER BY cp.time_pending ASC',
			array(
				'clan_id' => $clan_id,
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			$context['army_clan_pending'][] = array(
				'id' => (int) $row['id_member'],
				'name' => $row['real_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
				'status' => (int) $row['status'], // 0 = requested, 1 = invited
				'status_label' => ((int) $row['status'] === 1)
					? ($txt['army_clan_invited'] ?? 'Invited')
					: ($txt['army_clan_requested'] ?? 'Requested'),
				'time_pending' => timeformat((int) $row['time_pending']),
				'time_pending_raw' => (int) $row['time_pending'],
				'profile_url' => $scripturl . '?action=army;sa=profile;u=' . (int) $row['id_member'],
			);
		}

		$smcFunc['db_free_result']($request);
	}

	// Determine if the user can join this clan
	$can_join = false;

	if (!$user_info['is_guest'] && !$is_member && !$has_pending && allowedTo('army_play'))
	{
		$member = army_load_member($user_info['id']);

		if ($member !== false && !empty($member['race_id']) && army_is_active($member))
		{
			// Must not already be in another clan
			$request = $smcFunc['db_query']('', '
				SELECT id_member
				FROM {db_prefix}army_clan_members
				WHERE id_member = {int:id_member}
				LIMIT 1',
				array(
					'id_member' => $user_info['id'],
				)
			);

			$in_clan = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);

			$can_join = ($in_clan === null);
		}
	}

	// Build clan context
	$context['army_clan'] = array(
		'id' => (int) $clan['c_id'],
		'name' => $clan['c_name'],
		'description' => $clan['c_description'],
		'leader_id' => (int) $clan['c_leader'],
		'leader_name' => $clan['leader_name'] ?? ($txt['army_unknown_player'] ?? 'Unknown'),
		'started' => timeformat((int) $clan['c_started']),
		'started_raw' => (int) $clan['c_started'],
		'by_invite' => (int) $clan['c_by_invite'],
		'by_join' => (int) $clan['c_by_join'],
		'notes' => $clan['c_notes'],
		'member_count' => count($context['army_clan_members']),
	);

	$context['army_is_leader'] = $is_leader;
	$context['army_is_member'] = $is_member;
	$context['army_has_pending'] = $has_pending;
	$context['army_pending_status'] = $pending_status; // 0 = user requested, 1 = user was invited
	$context['army_can_join'] = $can_join;

	// Success flags from redirects
	$context['army_clan_invited'] = isset($_REQUEST['invited']) && $_REQUEST['invited'] == 1;
	$context['army_clan_approved'] = isset($_REQUEST['approved']) && $_REQUEST['approved'] == 1;
	$context['army_clan_denied'] = isset($_REQUEST['denied']) && $_REQUEST['denied'] == 1;
	$context['army_clan_removed'] = isset($_REQUEST['removed']) && $_REQUEST['removed'] == 1;
	$context['army_clan_edited'] = isset($_REQUEST['edited']) && $_REQUEST['edited'] == 1;
	$context['army_clan_transferred'] = isset($_REQUEST['transferred']) && $_REQUEST['transferred'] == 1;
	$context['army_clan_joined'] = isset($_REQUEST['joined']) && $_REQUEST['joined'] == 1;
	$context['army_clan_requested_join'] = isset($_REQUEST['requested']) && $_REQUEST['requested'] == 1;
	$context['army_clan_declined'] = isset($_REQUEST['declined']) && $_REQUEST['declined'] == 1;

	// Session tokens for the forms
	$context['army_session_var'] = $context['session_var'];
	$context['army_session_id'] = $context['session_id'];

	// Add to linktree
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=army;sa=clan;id=' . $clan_id,
		'name' => $clan['c_name'],
	);

	$context['sub_template'] = 'army_clan_view';
	$context['page_title'] = ($modSettings['army']['name'] ?? 'Army System') . ' - ' . $clan['c_name'];
}
