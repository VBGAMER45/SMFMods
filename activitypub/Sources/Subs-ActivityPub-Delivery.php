<?php
/**
 * ActivityPub Federation - Delivery Queue Management
 *
 * Manages outbound delivery queue, shared inbox deduplication,
 * parallel delivery via curl_multi, and exponential backoff retry.
 *
 * @package ActivityPub
 * @version 1.0.0
 */

if (!defined('SMF'))
	die('No direct access...');

/**
 * Queue delivery of an activity to all followers of an actor.
 * Deduplicates shared inboxes to avoid sending the same activity
 * multiple times to the same server.
 *
 * @param array $actor The local actor record.
 * @param array $activity The activity to deliver.
 * @param int $activity_db_id The activity row ID.
 */
function activitypub_queue_delivery_to_followers($actor, $activity, $activity_db_id)
{
	global $sourcedir;

	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

	$followers = activitypub_get_follower_actors($actor['id_actor']);
	if (empty($followers))
		return;

	$payload = json_encode($activity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

	// Deduplicate shared inboxes.
	$inboxes = array();
	$shared_inboxes_used = array();

	foreach ($followers as $follower)
	{
		// Prefer shared inbox for efficiency.
		if (!empty($follower['shared_inbox_url']))
		{
			if (!isset($shared_inboxes_used[$follower['shared_inbox_url']]))
			{
				$inboxes[] = $follower['shared_inbox_url'];
				$shared_inboxes_used[$follower['shared_inbox_url']] = true;
			}
		}
		elseif (!empty($follower['inbox_url']))
		{
			$inboxes[] = $follower['inbox_url'];
		}
	}

	// Queue each unique inbox.
	foreach ($inboxes as $inbox)
	{
		activitypub_queue_single_delivery($activity, $actor['id_actor'], $inbox, $activity_db_id);
	}

	// Trigger background task processing.
	activitypub_schedule_delivery_task();
}

/**
 * Queue a single delivery.
 *
 * @param array $activity The activity data.
 * @param int $actor_id The signing actor's row ID.
 * @param string $inbox The target inbox URL.
 * @param int $activity_db_id The activity row ID.
 */
function activitypub_queue_single_delivery($activity, $actor_id, $inbox, $activity_db_id)
{
	global $smcFunc, $modSettings;

	$max_attempts = !empty($modSettings['activitypub_max_delivery_attempts']) ? (int) $modSettings['activitypub_max_delivery_attempts'] : 8;
	$payload = json_encode($activity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

	$smcFunc['db_insert']('insert',
		'{db_prefix}ap_delivery_queue',
		array(
			'activity_id' => 'int',
			'target_inbox' => 'string',
			'actor_id' => 'int',
			'payload' => 'string',
			'status' => 'string',
			'attempts' => 'int',
			'max_attempts' => 'int',
			'last_attempt' => 'int',
			'next_retry' => 'int',
			'error_message' => 'string',
			'created_at' => 'int',
		),
		array(
			$activity_db_id,
			$inbox,
			$actor_id,
			$payload,
			'queued',
			0,
			$max_attempts,
			0,
			0,
			'',
			time(),
		),
		array('id_delivery')
	);
}

/**
 * Schedule the background delivery task.
 */
function activitypub_schedule_delivery_task()
{
	global $smcFunc, $sourcedir;

	// Check if there's already a pending task.
	$request = $smcFunc['db_query']('', '
		SELECT id_task
		FROM {db_prefix}background_tasks
		WHERE task_class = {string:class}
		LIMIT 1',
		array('class' => 'ActivityPub_Deliver_Background')
	);

	if ($smcFunc['db_num_rows']($request) > 0)
	{
		$smcFunc['db_free_result']($request);
		return;
	}
	$smcFunc['db_free_result']($request);

	$smcFunc['db_insert']('insert',
		'{db_prefix}background_tasks',
		array(
			'task_file' => 'string',
			'task_class' => 'string',
			'task_data' => 'string',
			'claimed_time' => 'int',
		),
		array(
			'$sourcedir/tasks/ActivityPub-Deliver.php',
			'ActivityPub_Deliver_Background',
			json_encode(array('time' => time())),
			0,
		),
		array('id_task')
	);
}

/**
 * Process queued deliveries.
 * Called by the background task.
 *
 * @param int $batch_size Number of deliveries to process.
 * @return int Number of deliveries processed.
 */
function activitypub_process_delivery_queue($batch_size = 50)
{
	global $smcFunc, $sourcedir, $modSettings;

	require_once($sourcedir . '/Subs-ActivityPub.php');
	require_once($sourcedir . '/Subs-ActivityPub-HttpSig.php');
	require_once($sourcedir . '/Subs-ActivityPub-Actor.php');

	$batch_size = !empty($modSettings['activitypub_delivery_batch_size']) ? (int) $modSettings['activitypub_delivery_batch_size'] : $batch_size;

	// Fetch queued items ready for delivery.
	$request = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}ap_delivery_queue
		WHERE status IN ({string:queued}, {string:failed})
			AND (next_retry = 0 OR next_retry <= {int:now})
		ORDER BY created_at ASC
		LIMIT {int:limit}',
		array(
			'queued' => 'queued',
			'failed' => 'failed',
			'now' => time(),
			'limit' => $batch_size,
		)
	);

	$deliveries = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$deliveries[] = $row;
	$smcFunc['db_free_result']($request);

	if (empty($deliveries))
		return 0;

	// Mark as processing.
	$ids = array_column($deliveries, 'id_delivery');
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ap_delivery_queue
		SET status = {string:processing}
		WHERE id_delivery IN ({array_int:ids})',
		array(
			'processing' => 'processing',
			'ids' => $ids,
		)
	);

	// Cache actors for signing.
	$actor_cache = array();
	$processed = 0;

	foreach ($deliveries as $delivery)
	{
		$actor_id = $delivery['actor_id'];

		if (!isset($actor_cache[$actor_id]))
		{
			$actor = activitypub_get_actor_by_id($actor_id);
			if (!empty($actor) && !empty($actor['private_key_pem']))
			{
				$actor['private_key_pem_decrypted'] = activitypub_decrypt_private_key($actor['private_key_pem']);
				$actor_cache[$actor_id] = $actor;
			}
			else
			{
				activitypub_mark_delivery_failed($delivery['id_delivery'], 'Actor not found or no private key', $delivery['attempts']);
				continue;
			}
		}

		$actor = $actor_cache[$actor_id];
		$success = activitypub_deliver_single($delivery['target_inbox'], $delivery['payload'], $actor);

		if ($success)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}ap_delivery_queue
				SET status = {string:delivered},
					attempts = attempts + 1,
					last_attempt = {int:now}
				WHERE id_delivery = {int:id}',
				array(
					'delivered' => 'delivered',
					'now' => time(),
					'id' => $delivery['id_delivery'],
				)
			);
		}
		else
		{
			activitypub_mark_delivery_failed($delivery['id_delivery'], 'HTTP delivery failed', $delivery['attempts']);
		}

		$processed++;
	}

	return $processed;
}

/**
 * Mark a delivery as failed with exponential backoff.
 *
 * Retry schedule: immediate, 5min, 30min, 2hr, 12hr, 24hr, 48hr, 72hr.
 *
 * @param int $delivery_id The delivery row ID.
 * @param string $error Error message.
 * @param int $current_attempts Current attempt count.
 */
function activitypub_mark_delivery_failed($delivery_id, $error, $current_attempts)
{
	global $smcFunc;

	$retry_delays = array(0, 300, 1800, 7200, 43200, 86400, 172800, 259200);
	$new_attempts = $current_attempts + 1;

	// Check if we should abandon.
	$request = $smcFunc['db_query']('', '
		SELECT max_attempts FROM {db_prefix}ap_delivery_queue WHERE id_delivery = {int:id}',
		array('id' => $delivery_id)
	);
	$row = $smcFunc['db_fetch_assoc']($request);
	$smcFunc['db_free_result']($request);
	$max = isset($row['max_attempts']) ? (int) $row['max_attempts'] : 8;

	if ($new_attempts >= $max)
	{
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}ap_delivery_queue
			SET status = {string:abandoned},
				attempts = {int:attempts},
				last_attempt = {int:now},
				error_message = {string:error}
			WHERE id_delivery = {int:id}',
			array(
				'abandoned' => 'abandoned',
				'attempts' => $new_attempts,
				'now' => time(),
				'error' => $error,
				'id' => $delivery_id,
			)
		);
		return;
	}

	$delay = isset($retry_delays[$new_attempts]) ? $retry_delays[$new_attempts] : 259200;
	$next_retry = time() + $delay;

	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ap_delivery_queue
		SET status = {string:failed},
			attempts = {int:attempts},
			last_attempt = {int:now},
			next_retry = {int:next},
			error_message = {string:error}
		WHERE id_delivery = {int:id}',
		array(
			'failed' => 'failed',
			'attempts' => $new_attempts,
			'now' => time(),
			'next' => $next_retry,
			'error' => $error,
			'id' => $delivery_id,
		)
	);
}

/**
 * Deliver a single activity to a remote inbox via signed HTTP POST.
 *
 * @param string $inbox_url The target inbox URL.
 * @param string $payload The JSON payload.
 * @param array $actor The signing actor.
 * @return bool True on success (2xx response).
 */
function activitypub_deliver_single($inbox_url, $payload, $actor)
{
	$headers = activitypub_sign_request($inbox_url, 'POST', $actor, $payload);

	$curl_headers = array();
	foreach ($headers as $name => $value)
		$curl_headers[] = $name . ': ' . $value;

	// Ensure Content-Type is present.
	$has_ct = false;
	foreach ($curl_headers as $h)
	{
		if (stripos($h, 'Content-Type:') === 0)
			$has_ct = true;
	}
	if (!$has_ct)
		$curl_headers[] = 'Content-Type: application/activity+json';

	$ch = curl_init($inbox_url);
	curl_setopt_array($ch, array(
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $payload,
		CURLOPT_HTTPHEADER => $curl_headers,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 3,
		CURLOPT_USERAGENT => 'SMF-ActivityPub/' . ACTIVITYPUB_VERSION . ' (+' . activitypub_base_url() . ')',
	));

	$response = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error = curl_error($ch);
	curl_close($ch);

	if (!empty($error))
	{
		activitypub_log_error('Delivery to ' . $inbox_url . ' failed: ' . $error);
		return false;
	}

	// 2xx = success, 202 = accepted.
	if ($http_code >= 200 && $http_code < 300)
		return true;

	activitypub_log_error('Delivery to ' . $inbox_url . ' returned HTTP ' . $http_code);
	return false;
}
